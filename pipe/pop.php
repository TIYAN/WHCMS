<?php 
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 **/

require(dirname(__FILE__) . "/../init.php");
require(dirname(__FILE__) . "/../includes/adminfunctions.php");
require(dirname(__FILE__) . "/../includes/ticketfunctions.php");
if( !function_exists("imap_open") ) 
{
    exit( "IMAP needs to be compiled into PHP for this to function" );
}

$type = array( "text", "multipart", "message", "application", "audio", "image", "video", "other" );
$encoding = array( "7bit", "8bit", "binary", "base64", "quoted-printable", "other" );
echo "<b>POP Import Log</b><br>Date: " . date("d/m/Y H:i:s") . "<hr>";
$query = "SELECT * FROM tblticketdepartments WHERE host!='' AND port!='' AND login!='' ORDER BY `order` ASC";
$result = full_query($query);
while( $data = mysql_fetch_array($result) ) 
{
    $host = $data["host"];
    $port = $data["port"];
    $login = $data["login"];
    $password = decrypt($data["password"]);
    echo "" . "Host: " . $host . "<br>Email: " . $login . "<br>";
    $imaplasterror = $connectsuccess = "";
    if( $port == "995" ) 
    {
        $mbox = imap_open("{" . $host . ":" . $port . "/pop3/ssl/novalidate-cert}INBOX", $login, $password);
        if( $mbox == false ) 
        {
            $imaplasterror = imap_last_error();
        }
        else
        {
            $connectsuccess = true;
        }

    }
    else
    {
        $mbox = imap_open("{" . $host . ":" . $port . "/pop3/notls}INBOX", $login, $password);
        if( $mbox == false ) 
        {
            if( !$imaplasterror ) 
            {
                $imaplasterror = imap_last_error();
            }

        }
        else
        {
            $connectsuccess = true;
        }

        if( !$connectsuccess ) 
        {
            $mbox = imap_open("{" . $host . ":" . $port . "/pop3/novalidate-cert}INBOX", $login, $password);
            if( $mbox == false ) 
            {
                if( !$imaplasterror ) 
                {
                    $imaplasterror = imap_last_error();
                }

            }
            else
            {
                $connectsuccess = true;
            }

        }

    }

    if( !$connectsuccess ) 
    {
        echo "" . "An Error Occurred: " . $imaplasterror . "<hr>";
    }
    else
    {
        $headers = imap_headers($mbox);
        $emailcount = count($headers);
        echo "" . "Email Count: " . $emailcount . "<hr>";
        if( $emailcount ) 
        {
            $msgno = 1;
            while( $msgno <= $emailcount ) 
            {
                $sections = $attachments = "";
                $header_info = getheaders($mbox, $msgno);
                $structure = imap_fetchstructure($mbox, $msgno);
                if( 1 < sizeof($structure->parts) ) 
                {
                    $sections = parse($structure);
                    $attachments = get_attachments($sections);
                }

                $msgBody = get_part($mbox, $msgno, "TEXT/PLAIN");
                if( !$msgBody ) 
                {
                    $msgBody = get_part($mbox, $msgno, "TEXT/HTML");
                    $msgBody = strip_tags($msgBody);
                }

                if( !$msgBody ) 
                {
                    $msgBody = "No message found.";
                }

                $msgBody = str_replace("&nbsp;", " ", $msgBody);
                $attachmentslist = "";
                if( is_array($attachments) ) 
                {
                    foreach( $attachments as $attachment ) 
                    {
                        $pid = $attachment["pid"];
                        $encoding = $attachment["encoding"];
                        $filename = $attachment["name"] ? $attachment["name"] : $attachment["filename"];
                        if( checkTicketAttachmentExtension($filename) ) 
                        {
                            $filenameparts = explode(".", $filename);
                            $extension = end($filenameparts);
                            $filename = implode(array_slice($filenameparts, 0, 0 - 1));
                            $filename = preg_replace("/[^a-zA-Z0-9-_ ]/", "", $filename);
                            mt_srand(time());
                            $rand = mt_rand(100000, 999999);
                            $attachmentfilename = $rand . "_" . $filename . "." . $extension;
                            $attachmentslist .= $attachmentfilename . "|";
                            $attachmentdata = imap_fetchbody($mbox, $msgno, $pid);
                            if( $encoding == "base64" ) 
                            {
                                $attachmentdata = imap_base64($attachmentdata);
                            }

                            $fp = fopen($attachments_dir . $attachmentfilename, "w");
                            fwrite($fp, $attachmentdata);
                            fclose($fp);
                        }
                        else
                        {
                            $msgBody .= "" . "\n" . "\nAttachment " . $filename . " blocked - file type not allowed.";
                        }

                        $cou++;
                    }
                }

                $attachmentslist = substr($attachmentslist, 0, 0 - 1);
                $fromemail = $header_info["fromAddr"];
                if( $header_info["replyTo"] ) 
                {
                    $fromemail = $header_info["replyTo"];
                }

                $header_info["subject"] = str_replace("{", "[", $header_info["subject"]);
                $header_info["subject"] = str_replace("}", "]", $header_info["subject"]);
                processPipedTicket($header_info["to"] . "" . "," . $login, $header_info["fromName"], $fromemail, $header_info["subject"], $msgBody, $attachmentslist);
                $sections = $attachments = $header_info = $fromemail = $attachmentslist = $attachmentdata = $attachmentfilename = "";
                imap_delete($mbox, $msgno);
                $msgno += 1;
            }
        }

        imap_expunge($mbox);
        imap_close($mbox);
    }

}


function get_mime_type($structure)
{
    $primary_mime_type = array( "TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER" );
    if( $structure->subtype ) 
    {
        return $primary_mime_type[(int) $structure->type] . "/" . $structure->subtype;
    }

    return "TEXT/PLAIN";
}

function get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false)
{
    global $CONFIG;
    global $disable_iconv;
    if( !$structure ) 
    {
        $structure = imap_fetchstructure($stream, $msg_number);
    }

    if( $structure ) 
    {
        $charset = "";
        foreach( $structure->parameters as $param ) 
        {
            if( $param->attribute == "CHARSET" ) 
            {
                $charset = $param->value;
                if( $charset == "UTF-8" ) 
                {
                    $charset = "";
                }

            }

        }
        if( $mime_type == get_mime_type($structure) ) 
        {
            if( !$part_number ) 
            {
                $part_number = "1";
            }

            $text = imap_fetchbody($stream, $msg_number, $part_number);
            if( $structure->encoding == 3 ) 
            {
                $text = imap_base64($text);
            }
            else
            {
                if( $structure->encoding == 4 ) 
                {
                    $text = imap_qprint($text);
                }

            }

            if( $charset && function_exists("iconv") && !$disable_iconv ) 
            {
                $text = iconv($charset, $CONFIG["Charset"], $text);
            }

            if( $charset && !$GLOBALS["mailcharset"] ) 
            {
                $GLOBALS["mailcharset"] = $charset;
            }

            return $text;
        }

        if( $structure->type == 1 ) 
        {
            while( list($index, $sub_structure) = each($structure->parts) ) 
            {
                if( $part_number ) 
                {
                    $prefix = $part_number . ".";
                }

                $data = get_part($stream, $msg_number, $mime_type, $sub_structure, $prefix . $index + 1);
                if( $data ) 
                {
                    return $data;
                }

            }
        }

    }

    return false;
}

function parse($structure)
{
    global $type;
    global $encoding;
    $ret = array(  );
    $parts = $structure->parts;
    for( $x = 0; $x < sizeof($parts); $x++ ) 
    {
        $ret[$x]["pid"] = $x + 1;
        $thisPart = $parts[$x];
        if( $thisPart->type == "" ) 
        {
            $thisPart->type = 0;
        }

        $ret[$x]["type"] = $type[$thisPart->type] . "/" . strtolower($thisPart->subtype);
        if( $thisPart->encoding == "" ) 
        {
            $thisPart->encoding = 0;
        }

        $ret[$x]["encoding"] = $encoding[$thisPart->encoding];
        $ret[$x]["size"] = strtolower($thisPart->bytes);
        $ret[$x]["disposition"] = strtolower($thisPart->disposition);
        if( $thisPart->parameters ) 
        {
            foreach( $thisPart->parameters as $p ) 
            {
                $ret[$x][strtolower($p->attribute)] = $p->value;
            }
        }

        if( $thisPart->dparameters ) 
        {
            foreach( $thisPart->dparameters as $p ) 
            {
                $ret[$x][strtolower($p->attribute)] = $p->value;
            }
        }

    }
    return $ret;
}

function get_attachments($arr)
{
    unset($ret);
    for( $x = 0; $x < sizeof($arr); $x++ ) 
    {
        if( $arr[$x]["filename"] || $arr[$x]["name"] ) 
        {
            $ret[] = $arr[$x];
        }

    }
    return $ret;
}

function char_replace($bad_char, $good_char, $str)
{
    if( is_array($bad_char) ) 
    {
        for( $i = 0; $i < sizeof($bad_char); $i++ ) 
        {
            $str = str_replace($bad_char[$i], $good_char, $str);
        }
    }
    else
    {
        $str = str_replace($bad_char, $good_char, $str);
    }

    return $str;
}

function getHeaders($mbox, $msgno)
{
    global $CONFIG;
    global $disable_iconv;
    if( $headers = @imap_headerinfo($mbox, $msgno) ) 
    {
        $header_info["msgID"] = $headers->message_id;
        if( $headersFrom = $headers->from ) 
        {
            $header_info["fromAddr"] = $headersFrom[0]->mailbox . "@" . $headersFrom[0]->host;
            if( $headersFrom[0]->personal ) 
            {
                $fromName = $headersFrom[0]->personal;
            }
            else
            {
                $fromName = $headersFrom[0]->mailbox . "@" . $headersFrom[0]->host;
            }

            $elements = imap_mime_header_decode($fromName);
            $fromName = $elements[0]->text;
            $charset = $elements[0]->charset;
            if( $charset && function_exists("iconv") && !$disable_iconv && $charset != "default" ) 
            {
                $fromName = iconv($charset, $CONFIG["Charset"], $fromName);
            }

            $fromName = str_replace(array( "<", ">", "\"", "'" ), "", $fromName);
            $header_info["fromName"] = $fromName;
        }

        unset($to);
        if( $headersTo = $headers->to ) 
        {
            if( 1 < sizeof($headersTo) ) 
            {
                $toMailbox = $headersTo[0]->mailbox . "@" . $headersTo[0]->host;
                if( !strstr($toMailbox, "UNEXPECTED_DATA") ) 
                {
                    $to .= $toMailbox;
                }

                for( $i = 1; $i < sizeof($headersTo); $i++ ) 
                {
                    $toMailbox = $headersTo[$i]->mailbox . "@" . $headersTo[$i]->host;
                    if( !strstr($toMailbox, "UNEXPECTED_DATA") ) 
                    {
                        $to .= ", " . $toMailbox;
                    }

                }
                $header_info["to"] = $to;
            }
            else
            {
                $header_info["to"] = $headersTo[0]->mailbox . "@" . $headersTo[0]->host;
            }

        }
        else
        {
            $header_info["to"] = "&nbsp;";
        }

        unset($cc);
        if( $headersCc = $headers->cc ) 
        {
            if( 1 < sizeof($headersCc) ) 
            {
                for( $i = 0; $i < sizeof($headersCc) - 1; $i++ ) 
                {
                    $ccMailbox = $headersCc[$i]->mailbox . "@" . $headersCc[$i]->host;
                    $cc .= $ccMailbox . ", ";
                }
                $ccMailbox = $headersCc[sizeof($headersCc) - 1]->mailbox . "@" . $headersCc[sizeof($headersCc) - 1]->host;
                $cc .= $ccMailbox;
                $header_info["cc"] = $cc;
            }
            else
            {
                $header_info["cc"] = $headersCc[0]->mailbox . "@" . $headersCc[0]->host;
            }

        }

        if( $headers->Date ) 
        {
            $header_info["date"] = htmlspecialchars($headers->Date);
        }
        else
        {
            $header_info["date"] = "&nbsp;";
        }

        if( $headers->subject ) 
        {
            $subject = $headers->subject;
            $elements = imap_mime_header_decode($subject);
            $subject = $elements[0]->text;
            $charset = $elements[0]->charset;
            if( $charset && function_exists("iconv") && !$disable_iconv && $charset != "default" ) 
            {
                $subject = iconv($charset, $CONFIG["Charset"], $subject);
            }

            $header_info["subject"] = $subject;
        }
        else
        {
            $header_info["subject"] = "No Subject";
        }

        $headersReplyTo = $headers->reply_to;
        if( is_array($headersReplyTo) ) 
        {
            $header_info["replyTo"] = $headersReplyTo[0]->mailbox . "@" . $headersReplyTo[0]->host;
        }

    }
    else
    {
        $header_info = false;
    }

    return $header_info;
}


?>