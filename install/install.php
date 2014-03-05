<?php 
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.net
 *
 * */

error_reporting(0);
@set_time_limit(0);
define("ROOTDIR", dirname(__FILE__) . "/../");
$latestversion = "5.2.15";
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n\t<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n<title>WHMCS Install/Upgrade Process</title>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n<script type=\"text/javascript\" src=\"../includes/jscript/jquery.js\"></script>\n<script>\nfunction showloading() {\n    \$(\"#submitbtn\").attr(\"disabled\",\"disabled\");\n    \$(\".loading\").fadeIn();\n}\n</script>\n<style>\nbody {\n    background-color: #efefef;\n    margin: 25px;\n}\na {\n    color: #0000ff;\n}\nbody,td {\n    font-family: Tahoma;\n    font-size: 12px;\n}\ninput {\n    font-family: Tahoma;\n    font-size: 16px;\n    padding: 2px 10px;\n}\nh1 {\n    font-size: 18px;\n    font-family: Arial;\n    color: #294A87;\n    padding-bottom: 10px;\n    border-bottom: 1px dashed #ccc;\n    margin-bottom: 30px;\n}\nh2 {\n    font-size: 16px;\n    font-family: Arial;\n    color: #000;\n}\n.wrapper {\n    margin: 0 auto;\n    background-color: #fff;\n    width: 740px;\n    padding: 10px 30px 30px 30px;\n    -moz-border-radius: 10px;\n    -webkit-border-radius: 10px;\n    -o-border-radius: 10px;\n    border-radius: 10px;\n}\n.version {\n    float: right;\n    margin: 30px 10px;\n    padding: 10px 20px;\n    background-color: #294A87;\n    color: #fff;\n    font-family: Verdana;\n    font-size: 40px;\n    -moz-border-radius: 10px;\n    -webkit-border-radius: 10px;\n    -o-border-radius: 10px;\n    border-radius: 10px;\n}\n.errorbox {\n\tmargin: 15px auto 0 auto;\n\tpadding: 10px;\n    width: 90%;\n\tborder: 1px solid #A89824;\n    font-size: 14px;\n\tbackground-color: #EEE7B0;\n\ttext-align: left;\n\tcolor: #706518;\n}\n.loading {\n    display: none;\n    margin: 0 auto;\n    padding: 20px;\n    width: 400px;\n    font-size: 18px;\n    text-align: center;\n}\n</style>\n</head>\n<body>\n\n<div class=\"wrapper\">\n\n<div class=\"version\">V";
echo substr($latestversion, 0, 0 - 2);
echo "</div>\n\n<div style=\"margin:30px;\"><a href=\"http://www.whmcs.com/\" target=\"_blank\"><img src=\"http://api.mtimer.net/whmcs/images/logo.png\" alt=\"WHMCS - The Complete Client Management, Billing & Support Solution\" border=\"0\" /></a></div>\n\n<br />\n\n";
$step = $_REQUEST["step"];
$type = $_REQUEST["type"];
$version = $_REQUEST["version"];
$failed = false;
$error = $firstname = $lastname = $username = $email = $password = $confirmpassword = "";
if( $step == "5" ) 
{
    if( !$error && !trim($_REQUEST["firstname"]) ) 
    {
        $error = "You must enter a first name";
    }

    if( !$error && !trim($_REQUEST["email"]) ) 
    {
        $error = "You must enter an email address";
    }

    if( !$error && !trim($_REQUEST["username"]) ) 
    {
        $error = "You must enter a username";
    }

    if( !$error && !trim($_REQUEST["password"]) ) 
    {
        $error = "You must enter a password";
    }

    if( !$error && trim($_REQUEST["password"]) != trim($_REQUEST["confirmpassword"]) ) 
    {
        $error = "The two passwords you entered did not match. Please try again";
    }

    if( $error ) 
    {
        $step = "4";
        $failed = true;
        $firstname = $_REQUEST["firstname"];
        $lastname = $_REQUEST["lastname"];
        $username = $_REQUEST["username"];
        $email = $_REQUEST["email"];
        $password = $_REQUEST["password"];
        $confirmpassword = $_REQUEST["confirmpassword"];
    }

}

if( $step == "" ) 
{
    echo "\n<h1>End User License Agreement</h1>\n<p>Please review the license terms before installing/upgrading WHMCS.  By installing, copying, or otherwise using the software, you are agreeing to be bound by the terms of the EULA.</p><form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\" style=\"margin-top:10px;margin-bottom:5px;\"><input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\"><input type=\"hidden\" name=\"hosted_button_id\" value=\"N3T56B5LHAGBS\"><input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif\" border=\"0\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\" style=\"margin-bottom:-5px;\"><p style=\"display:inline;margin-left:10px;\"> & get updates lifetime via email :) It doesn't cost much ~</p></form>\n<p align=\"center\"><textarea style=\"width:700px;font-family:Tahoma;font-size:10px;color:#666666\" rows=\"25\" readonly>\nWHMCS Software License Agreement\n\nPlease read this End-User License Agreement (the \"EULA\")\n\nIMPORTANT --- READ CAREFULLY. By installing, copying, or otherwise using the Software, you are agreeing to be bound by the terms of this EULA, including the WARRANTY DISCLAIMERS, LIMITATIONS OF LIABILITY, and TERMINATION PROVISIONS. If you do not agree to the terms of this EULA do not install or use the Software.\n\nLICENSE TERMS\n\n1. The Software is supplied by WHMCS Limited and is licensed, not sold, under the terms of this EULA and WHMCS reserves all rights not expressly granted to you. WHMCS Limited retains the ownership of the Software.\n\n2. Software License:\n\na. WHMCS Limited grants you a license to use one copy of the Software. You may not modify or disable any licensing or control features of the Software.\n\nb. This Software is licensed to operate on only one domain.\n\nc. The Software is licensed only to you. You may not rent, lease, sub-license, sell, assign, pledge, transfer or otherwise dispose of the Software, on a temporary or permanent basis, without the prior written consent of WHMCS Limited. (For the avoidance of doubt, this licence is only granted to one person/company and if more than one person/company wishes to use the Software, each company must purchase a separate license).\n\n3. License Restrictions:\n\na. By accepting this EULA you are agreeing not to reverse engineer, decompile, or disassemble the Software Application, except and only to the extent that such activity is expressly permitted by applicable law notwithstanding this limitation.\n\nb. You are the exclusive licensee of the Software and sharing any source code of the Software with any individual or entity is a violation of copyright laws and international treaties and cause for license termination.\n\nc. Modifying any portion of the Software source code or asking any individual or entity to modify the Software source code other than WHMCS Limited is a violation of copyright laws and international treaties and cause for license termination.\n\nd. If you upgrade the Software to a higher version of the Software, this EULA is terminated and your rights shall be limited to the EULA associated with the higher version.\n\n4. Proprietary Rights: All title and copyrights in and to the Software (including, without limitation, any images, photographs, animations, video, audio, music, text, and \"applets\" incorporated into the Software Application), the accompanying media and printed materials, and any copies of the Software are owned by WHMCS Limited. The Software is protected by copyright laws and international treaty provisions. Therefore, you must treat the Software like any other copyrighted material, subject to the provisions of this EULA.\n\n5. Termination Rights:\n\na. Without prejudice to any other rights, WHMCS Limited may terminate this EULA if you fail to comply with the terms and conditions of this EULA. In such event, you must destroy all copies of the Software and all of its component parts, and WHMCS Limited may suspend or deactivate your use of the Software with or without notice.\n\nb. WHMCS Limited may terminate this EULA if you become subject to an administration order; a receiver or administrative receiver or similar is appointed over, or an encumbrancer takes possession of, any of your property or assets; you enter into an arrangement or composition with your creditors, you cease or threaten to cease to carry on business, you become insolvent or bankrupt, or cease to be able to pay your debts as they fall due;\n\nc. WHMCS Limited may terminate this EULA if you fail to pay your monthly fee to WHMCS and WHMCS files a final written notice on you and the outstanding sums due under this EULA still remain unpaid 5 days after the service of the notice.\n\n6. Export Control: You may not export or re-export the Software or any copy or adaptation of the Software in violation of any applicable laws or regulations.\n\n7. WHMCS Limited does not warrant that the operation of WHMCS Software will be uninterrupted or error free. WHMCS Software may contain third-party functions or may have been subject to incidental use.\n\n8. LIMIT of Liability:\n\na. WHMCS Limited is not responsible for problems resulting from improper or inadequate maintenance or configuration; software or interface routines or functions NOT developed by WHMCS; unauthorized specifications for the Software; improper site preparation or maintenance; Beta Software; encryption mechanisms or routines.\n\nb. Good data processing procedure dictates that any program be thoroughly tested with non-critical data before relying on it. You must assume the entire risk of using the Software. IN NO EVENT WILL WHMCS LIMITED OR ITS SUPPLIERS BE LIABLE FOR DIRECT, SPECIAL, INCIDENTAL, CONSEQUENTIAL (INCLUDING LOST PROFIT OR LOST SAVINGS) OR OTHER DAMAGE WHETHER BASED IN CONTRACT, TORT, OR OTHERWISE EVEN IF A WHMCS REPRESENTATIVE HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES, OR FOR ANY CLAIM BY ANY THIRD PARTY.\n\n9. If the above clause 8 is deemed to be unenforceable by a court or other body of competent jurisdiction and WHMCS Limited is found to be liable to you for direct losses under this EULA, then WHMCS Limited’s liability shall be limited to £20,000 for any one event or a series of connected events.\n\n10. Submissions: Should you decide to transmit to WHMCS Limited by any means or by any media any information (including, without limitation, ideas, concepts, or techniques for new or improved services and products), whether as information, feedback, data, questions, comments, suggestions, or the like, you agree such submissions are unrestricted and shall be deemed non-confidential and you automatically grant WHMCS Limited and its assigns a non-exclusive, royalty-free, worldwide, perpetual, irrevocable license, with the right to sublicense, to use, copy, transmit, distribute, create derivative works of, display, and perform the same.\n\n11. Distribution and Backups:\n\na. DISTRIBUTION OF THE REGISTERED VERSION OF THE Software IS STRICTLY PROHIBITED and is a violation of copyright laws and international treaties punishable by severe criminal and civil penalties.\n\nb. You may make copies of the Registered Version of the Software for backup purposes only. All backup copies must be an exact copy of the original Software.\n\n12. Refunds Policy: Refunds are only issued for software failure. Refunds are not issued for server failure/issues, lack of features or if your server does not meet the Software Requirements. Refunds are determined on individual circumstances and only issued once our technical staff determine that WHMCS has a fault causing it to not run on your server. Installation charges are not refundable under any circumstances. Refunds are not available after 1 month from purchase date.\n\n13. If any provision of this EULA shall be prohibited by law or adjudged by a court to be unlawful, void or unenforceable such provision shall to the extent required be severed from this EULA and rendered ineffective as far as possible without modifying the remaining provisions of this EULA and shall not in any way affect any other circumstances of or the validity or enforcement of this EULA.\n\n14. WHMCS Limited undertakes to comply with the provisions of the Data Protection Act 1998 and any related legislation in so far as the same relates to the provision of the Software and related support services by WHMCS Limited to you.\n\nYour name, address and other personal information as well as any personal data you supply to WHMCS Limited in order for WHMCS to provide the Software and related services related to you will be stored by WHMCS Limited on its computer system and may be made available to WHMCS staff and related third parties as required to allow the provision of support and any related services to be completed. Any third party that receives personal data from WHMCS Limited is under an obligation to process such personal data in line with the Data Protection Act 1998.\n\n15. Each party irrevocably agrees that the courts of the country of England shall have exclusive jurisdiction to resolve any controversy or claim of whatever nature arising out of or in relation to this EULA and the place of performance of this EULA shall be England and that the laws of that country shall govern such controversy or claim.\n</textarea></p>\n\n<p align=center><input type=\"submit\" value=\"I AGREE\" class=\"button\" onClick=\"window.location='install.php?step=2'\"> <input type=\"button\" value=\"I DISAGREE\" class=\"button\" onClick=\"window.location='install.php'\">\n\n";
}
else
{
    if( $step == "2" ) 
    {
        include("../configuration.php");
        if( function_exists("mysql_connect") ) 
        {
            $link = mysql_connect($db_host, $db_username, $db_password);
            mysql_select_db($db_name);
            $query = "SELECT * FROM tblconfiguration WHERE setting='Version'";
            $result = mysql_query($query);
            while( $data = @mysql_fetch_array($result) ) 
            {
                $setting = $data["setting"];
                $value = $data["value"];
                $CONFIG[$setting] = $value;
            }
        }

        if( $CONFIG["Version"] ) 
        {
            echo "\n<h1>Upgrade to V";
            echo $latestversion;
            echo "</h1>\n\n";
            $upgradeversion = str_replace(".", "", $CONFIG["Version"]);
            if( $CONFIG["Version"] == $latestversion ) 
            {
                echo "<p style=\"font-size: 16px;\">You are already running the latest version of WHMCS and so cannot upgrade.</p>\n";
            }
            else
            {
                if( $upgradeversion < 320 ) 
                {
                    echo "<p style=\"font-size: 16px;\">The version of WHMCS you are running is too old to be upgraded automatically.</p>\n<p style=\"font-size: 16px;\">You will need to purchase our professional upgrade service @ <a href=\"http://www.whmcs.com/upgradeservice.php\">www.whmcs.com/upgradeservice.php</a> to have it manually updated.</p>\n";
                }
                else
                {
                    $previous_installed_version_to_display = $CONFIG["Version"];
                    if( $previous_installed_version_to_display == "5.3.0" ) 
                    {
                        $previous_installed_version_to_display = "5.2.6 build 3";
                    }

                    echo "<p align=\"center\" style=\"font-size:18px;\">Your Current Version is V";
                    echo $previous_installed_version_to_display;
                    echo "</p>\n<div style=\"border: 1px dashed #cc0000;\tfont-weight: bold;\tbackground-color: #FBEEEB;\ttext-align: center; padding: 10px;\tcolor: #cc0000;font-size:16px;\">Backup your database before continuing...</div>\n<form method=\"post\" action=\"install.php\" onsubmit=\"showloading()\">\n<input type=\"hidden\" name=\"step\" value=\"upgrade\" />\n<input type=\"hidden\" name=\"version\" value=\"";
                    echo $upgradeversion;
                    echo "\" />\n";
                    if( $upgradeversion < 400 ) 
                    {
                        echo "<p align=\"center\"><input type=\"checkbox\" name=\"nomd5\" /> Do not use MD5 client password encryption</p>";
                    }

                    echo "<p align=\"center\"><label><input type=\"checkbox\" name=\"confirmbackup\" /> I confirm I have backed up my database</label></p>\n<p align=\"center\"><input type=\"submit\" value=\"Perform Upgrade &raquo;\" class=\"button\" id=\"submitbtn\" /></p>\n<div class=\"loading\">Updating Database... Please Wait...<br /><img src=\"../images/loading.gif\" /></div>\n</form>\n";
                }

            }

        }
        else
        {
            echo "\n<h1>System Requirements Checks</h1>\n<div style=\"font-size: 16px;\">\n&raquo; PHP Version .......... ";
            if( "5.0.0" <= phpversion() ) 
            {
                echo "<font color=#99cc00><B>Passed</B></font>";
            }
            else
            {
                echo "<font color=#cc0000><B>Failed</B></font><div class=\"errorbox\">Your PHP version needs to be upgraded to at least V5.0 for WHMCS</div>";
                $error = "1";
            }

            echo "<br>\n&raquo; MySQL .......... ";
            if( function_exists("mysql_connect") ) 
            {
                echo "<font color=#99cc00><B>Passed</B></font>";
            }
            else
            {
                echo "<font color=#cc0000><B>Failed</B></font><div class=\"errorbox\">MySQL support doesn't appear to be available in this PHP installation. You will need to install MySQL before you can continue.</div>";
                $error = "1";
            }

            echo "<br>\n&raquo; CURL .......... ";
            if( function_exists("curl_init") ) 
            {
                echo "<font color=#99cc00><B>Passed</B></font>";
            }
            else
            {
                echo "<font color=#cc0000><B>Failed</B></font><div class=\"errorbox\">CURL is required in order for WHMCS to function so you will need to recompile PHP with the CURL libraries included before you can continue.</div>";
                $error = "1";
            }

            echo "</div>\n<br />\n<h1>Permissions Checks</h1>\n<div style=\"font-size: 16px;\">\n&raquo; Configuration File .......... ";
            if( is_writable("../configuration.php") ) 
            {
                echo "<font color=#99cc00><B>Passed</B></font>";
            }
            else
            {
                echo "<font color=#cc0000><B>Failed</B></font>";
                if( !is_file("../configuration.php") ) 
                {
                    echo "<div class=\"errorbox\">The file \"configuration.php\" cannot be found. Please create an empty file in the folder above the install directory with writeable permissions to continue.</div>";
                }
                else
                {
                    echo "<div class=\"errorbox\">You must apply writeable permissions (CHMOD 755 or 777) to the \"/configuration.php\" before you can continue</div>";
                }

                $error = "1";
            }

            echo "<br>\n&raquo; Attachments Folder .......... ";
            if( is_writable("../attachments/") ) 
            {
                echo "<font color=#99cc00><B>Passed</B></font>";
            }
            else
            {
                echo "<font color=#cc0000><B>Failed</B></font>";
                if( !is_dir("../attachments") ) 
                {
                    echo "<div class=\"errorbox\">You must create a directory named \"attachments\" before you can continue</div>";
                }
                else
                {
                    echo "<div class=\"errorbox\">You must apply writeable permissions (CHMOD 755 or 777) to the \"attachments\" directory before you can continue</div>";
                }

                $error = "1";
            }

            echo "<br>\n&raquo; Downloads Folder .......... ";
            if( is_writable("../downloads/") ) 
            {
                echo "<font color=#99cc00><B>Passed</B></font>";
            }
            else
            {
                echo "<font color=#cc0000><B>Failed</B></font>";
                if( !is_dir("../downloads") ) 
                {
                    echo "<div class=\"errorbox\">You must create a directory named \"downloads\" before you can continue</div>";
                }
                else
                {
                    echo "<div class=\"errorbox\">You must apply writeable permissions (CHMOD 755 or 777) to the \"downloads\" directory before you can continue</div>";
                }

                $error = "1";
            }

            echo "<br>\n&raquo; Templates Cache Folder .......... ";
            if( is_writable("../templates_c/") ) 
            {
                echo "<font color=#99cc00><B>Passed</B></font>";
            }
            else
            {
                echo "<font color=#cc0000><B>Failed</B></font>";
                if( !is_dir("../templates_c") ) 
                {
                    echo "<div class=\"errorbox\">You must create a directory named \"templates_c\" before you can continue</div>";
                }
                else
                {
                    echo "<div class=\"errorbox\">You must apply writeable permissions (CHMOD 755 or 777) to the \"templates_c\" directory before you can continue</div>";
                }

                $error = "1";
            }

            echo "</div>\n<br />\n";
            if( $error == "1" ) 
            {
                echo "<p align=\"center\" style=\"font-size:16px;color:#cc0000;\"><b>Pre-Installation Checks Failure</b><br />Please correct the problems listed above and then recheck the requirements to continue...</p>\n<p align=\"center\"><input type=\"button\" value=\"Recheck Requirements\" onClick=\"location.reload(true);\"></p>\n";
            }
            else
            {
                echo "<p align=\"center\" style=\"font-size:16px;color:#7BA400;\"><b>Pre-Installation Checks Success</b><br />Everything seems to be ok so please continue below to begin the installation...</p>\n<form method=\"post\" action=\"install.php?step=3\">\n<p align=\"center\"><input type=\"submit\" value=\"Continue &raquo;\" class=\"button\" /></p>\n</form>\n";
            }

            echo "\n";
        }

    }
    else
    {
        if( $step == "3" ) 
        {
            echo "\n<form method=\"post\" action=\"install.php?step=4\" onsubmit=\"showloading()\">\n\n<h1>Database Connection Details</h1>\n<p>You must now create a MySQL database for WHMCS to use inside your hosting control panel.  Once created, and a user assigned, enter the connection details below.</p>\n<table>\n<tr><td width=120>Database Host</td><td><input type=\"text\" name=\"dbhost\" size=\"20\" value=\"localhost\"></td></tr>\n<tr><td>Database Username</td><td><input type=\"text\" name=\"dbusername\" size=\"20\" value=\"\"></td></tr>\n<tr><td>Database Password</td><td><input type=\"text\" name=\"dbpassword\" size=\"20\" value=\"\"></td></tr>\n<tr><td>Database Name</td><td><input type=\"text\" name=\"dbname\" size=\"20\" value=\"\"></td></tr>\n</table>\n<p align=\"center\"><input type=\"submit\" value=\"Continue &raquo;\" class=\"button\" id=\"submitbtn\" /></p>\n<div class=\"loading\">Initialising Database... Please Wait...<br /><img src=\"../images/loading.gif\" /></div>\n</form>\n\n";
        }
        else
        {
            if( $step == "4" ) 
            {
                if( !$failed ) 
                {
//                    $licensekey = trim($_REQUEST["licensekey"]);
                    $licensekey = 'Owned-f9f530be1f13fa8342ff';
                    $dbhost = trim($_REQUEST["dbhost"]);
                    $dbusername = trim($_REQUEST["dbusername"]);
                    $dbpassword = trim($_REQUEST["dbpassword"]);
                    $dbname = trim($_REQUEST["dbname"]);
                    if( !$licensekey ) 
                    {
                        echo "You did not enter your license key.  You must go back and correct this.";
                        exit();
                    }

                    $length = 64;
                    $seeds = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                    $str = null;
                    $seeds_count = strlen($seeds) - 1;
                    for( $i = 0; $i < $length; $i++ ) 
                    {
                        $str .= $seeds[rand(0, $seeds_count)];
                    }
                    $output = "<?php\n    \$license = '" . $licensekey . "';\n    \$db_host = '" . $dbhost . "';\n    \$db_username = '" . $dbusername . "';\n    \$db_password = '" . $dbpassword . "';\n    \$db_name = '" . $dbname . "';\n    \$cc_encryption_hash = '" . $str . "';\n    \$templates_compiledir = 'templates_c/';\n    \$mysql_charset = 'utf8';\n?>";
                    $fp = fopen("../configuration.php", "w");
                    if( fwrite($fp, $output) !== FALSE ) 
                    {
                        fclose($fp);
                    }
                    else
                    {
                        header("Content-Type: text/x-delimtext; name=\"configuration.php\"");
                        header("Content-disposition: attachment; filename=configuration.php");
                        echo $output;
                    }

                    include("../configuration.php");
                    $link = mysql_connect($db_host, $db_username, $db_password);
                    mysql_select_db($db_name) or exit( "Could not connect to the database - check the database connection details you entered and go back and correct them if necessary" );
                    mysql_query("SET NAMES 'utf8'");
                    mysql_import_file("install.sql");
                    mysql_import_file("emailtemplates.sql");
                }
                else
                {
                    echo "<div class=\"errorbox\">" . $error . "</div>";
                }

                echo "\n<h1>Setup Administrator Account</h1>\n<form method=\"post\" action=\"install.php?step=5\" onsubmit=\"showloading()\">\n<p>You now need to setup your administrator account.</p>\n<table>\n<tr><td width=120>First Name:</td><td><input type=\"text\" name=\"firstname\" size=\"30\" value=\"";
                echo $firstname;
                echo "\"></td></tr>\n<tr><td>Last Name:</td><td><input type=\"text\" name=\"lastname\" size=\"30\" value=\"";
                echo $lastname;
                echo "\"></td></tr>\n<tr><td>Email:</td><td><input type=\"text\" name=\"email\" size=\"50\" value=\"";
                echo $email;
                echo "\"></td></tr>\n<tr><td>Username:</td><td><input type=\"text\" name=\"username\" size=\"20\" value=\"";
                echo $username;
                echo "\"></td></tr>\n<tr><td>Password:</td><td><input type=\"password\" name=\"password\" size=\"20\" value=\"";
                echo $password;
                echo "\"></td></tr>\n<tr><td>Confirm Password:</td><td><input type=\"password\" name=\"confirmpassword\" size=\"20\" value=\"";
                echo $confirmpassword;
                echo "\"></td></tr>\n</table>\n<p align=\"center\"><input type=\"submit\" value=\"Complete Setup &raquo;\" class=\"button\" id=\"submitbtn\" /></p>\n<div class=\"loading\">Setting Up System for First Use... Please Wait...<br /><img src=\"../images/loading.gif\" /></div>\n</form>\n\n";
            }
            else
            {
                if( $step == "5" ) 
                {
                    include("../configuration.php");
                    $link = mysql_connect($db_host, $db_username, $db_password);
                    mysql_select_db($db_name) or exit( "Could not connect to the database - check the database connection details you entered and go back and correct them if necessary" );
                    mysql_query("SET NAMES 'utf8'");
                    mysql_query("INSERT INTO `tbladmins` ( `username` , `password` , `firstname` , `lastname` , `email` , `userlevel` , `signature` , `notes` , `supportdepts` ) VALUES ('" . $_REQUEST["username"] . "', '" . md5($_REQUEST["password"]) . "', '" . $_REQUEST["firstname"] . "', '" . $_REQUEST["lastname"] . "', '" . $_REQUEST["email"] . "', '3', '', 'Welcome to WHMCS 5.2.15 Full Decoded && Nulled By Mtimer!  Please ensure you have setup the cron job in cPanel to automate tasks', ',')");
                    echo "<h1>Installation Complete</h1>";
                    v321Upgrade();
                    v322Upgrade();
                    v323Upgrade();
                    v330Upgrade();
                    v340Upgrade();
                    v341Upgrade();
                    v350Upgrade();
                    v351Upgrade();
                    v360Upgrade();
                    v361Upgrade();
                    v362Upgrade();
                    v370Upgrade();
                    v371Upgrade();
                    v372Upgrade();
                    v380Upgrade();
                    v381Upgrade();
                    v382Upgrade();
                    v400Upgrade();
                    v401Upgrade();
                    v410Upgrade();
                    v411Upgrade();
                    v412Upgrade();
                    v420Upgrade();
                    v421Upgrade();
                    v430Upgrade();
                    v431Upgrade();
                    v440Upgrade();
                    v441Upgrade();
                    v442Upgrade();
                    v450Upgrade();
                    v451Upgrade();
                    v452Upgrade();
                    v500Upgrade();
                    v501Upgrade();
                    v502Upgrade();
                    v503Upgrade();
                    v510Upgrade();
                    v511Upgrade();
                    v512Upgrade();
                    v520Upgrade();
                    v521Upgrade();
                    v522Upgrade();
                    v523Upgrade();
                    v524Upgrade();
                    v525Upgrade();
                    v526Upgrade();
                    v527Upgrade();
                    v528Upgrade();
                    v529Upgrade();
                    v5210Upgrade();
                    v5211Upgrade();
                    v5212Upgrade();
                    v5213Upgrade();
                    v5214Upgrade();
                    v5215Upgrade();
                    echo "\n<p>Here's what you should do next:</p>\n\n<p><b>1. Delete the Install Folder</b></p>\n<p>You should now delete the <b><i>install</i></b> directory from your web server.</p>\n\n<p><b>2. Secure the Writeable Directories</b></p>\n<p>It is advisable to move the attachments, downloads & templates_c directories (which need to be writeable for WHMCS to function) outside of the publically accessible folder tree.  Instructions for how to do this can be found in our documentation @ <a href=\"http://docs.whmcs.com/Further_Security_Steps\" target=\"_blank\">Further Security Steps</a></p>\n\n<p><b>3. Setup the Daily Cron Job</b></p>\n<p>You should setup a cron job in your control panel to run using the following command once per day:<br>\n<div align=\"center\"><input type=\"text\" value=\"php -q ";
                    $pos = strrpos($_SERVER["SCRIPT_FILENAME"], "/");
                    $filename = substr($_SERVER["SCRIPT_FILENAME"], 0, $pos);
                    $pos = strrpos($filename, "/");
                    $filename = substr($filename, 0, $pos);
                    echo $filename;
                    echo "/admin/cron.php\" style=\"width:90%;\" readonly=\"true\"></div></p>\n\n<p><b>4. Configure WHMCS</b></p>\n<p>Now it's time to configure your WHMCS installation.<br /><br />We have lots of <b>helpful resources & guides</b> available to assist you in setting up & using your new WHMCS system in our comprehensive online documentation located @ <a href=\"http://docs.whmcs.com/\" target=\"_blank\">http://docs.whmcs.com/</a> (you can access the docs at any time by going to Help > Documentation or using the handy Help shortcuts available from most setup pages within the admin area)</p>\n\n<br />\n\n<p align=\"center\" style=\"font-size:16px;\"><a href=\"../admin/\">Click here to go to the admin area now &raquo;</a></p>\n\n<br />\n\n<h2>Thank you for choosing WHMCS!</h2>\n<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\" style=\"margin-top:10px;margin-bottom:5px;\"><input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\"><input type=\"hidden\" name=\"hosted_button_id\" value=\"N3T56B5LHAGBS\"><input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif\" border=\"0\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\" style=\"margin-bottom:-5px;\"><p style=\"display:inline;margin-left:10px;\">to get updates lifetime via email :) Don't miss it~</p></form>\n\n";
                }
                else
                {
                    if( $step == "upgrade" ) 
                    {
                        if( !$_REQUEST["confirmbackup"] ) 
                        {
                            echo "<h1>Did you backup?</h1><p>You must confirm you have backed up your database before upgrading. Please go back and try again.";
                        }
                        else
                        {
                            $customadminpath = "admin";
                            include("../configuration.php");
                            $link = mysql_connect($db_host, $db_username, $db_password);
                            mysql_select_db($db_name) or exit( "Could not connect to the database" );
                            if( $mysql_charset ) 
                            {
                                mysql_query("SET NAMES '" . mysql_real_escape_string($mysql_charset) . "'");
                            }

                            $maj_min_version = substr($version, 0, 2);
                            $revision_version = substr($version, 2);
                            echo "<h1>Upgrade Complete</h1>";
                            if( $version <= 320 ) 
                            {
                                v321Upgrade();
                            }

                            if( $version <= 321 ) 
                            {
                                v322Upgrade();
                            }

                            if( $version <= 322 ) 
                            {
                                v323Upgrade();
                            }

                            if( $version <= 323 ) 
                            {
                                v330Upgrade();
                            }

                            if( $version <= 330 ) 
                            {
                                v340Upgrade();
                            }

                            if( $version <= 340 ) 
                            {
                                v341Upgrade();
                            }

                            if( $version <= 341 ) 
                            {
                                v350Upgrade();
                            }

                            if( $version <= 350 ) 
                            {
                                v351Upgrade();
                            }

                            if( $version <= 351 ) 
                            {
                                v360Upgrade();
                            }

                            if( $version <= 360 ) 
                            {
                                v361Upgrade();
                            }

                            if( $version <= 361 ) 
                            {
                                v362Upgrade();
                            }

                            if( $version <= 362 ) 
                            {
                                v370Upgrade();
                            }

                            if( $version <= 370 ) 
                            {
                                v371Upgrade();
                            }

                            if( $version <= 371 ) 
                            {
                                v372Upgrade();
                            }

                            if( $version <= 372 ) 
                            {
                                v380Upgrade();
                            }

                            if( $version <= 380 ) 
                            {
                                v381Upgrade();
                            }

                            if( $version <= 381 ) 
                            {
                                v382Upgrade();
                            }

                            if( $version <= 383 ) 
                            {
                                v400Upgrade();
                            }

                            if( $version <= 400 ) 
                            {
                                v401Upgrade();
                            }

                            if( $version <= 402 ) 
                            {
                                v410Upgrade();
                            }

                            if( $version <= 410 ) 
                            {
                                v411Upgrade();
                            }

                            if( $version <= 411 ) 
                            {
                                v412Upgrade();
                            }

                            if( $version <= 412 ) 
                            {
                                v420Upgrade();
                            }

                            if( $version <= 420 ) 
                            {
                                v421Upgrade();
                            }

                            if( $version <= 421 ) 
                            {
                                v430Upgrade();
                            }

                            if( $version <= 430 ) 
                            {
                                v431Upgrade();
                            }

                            if( $version <= 431 ) 
                            {
                                v440Upgrade();
                            }

                            if( $version <= 440 ) 
                            {
                                v441Upgrade();
                            }

                            if( $version <= 441 ) 
                            {
                                v442Upgrade();
                            }

                            if( $version <= 442 ) 
                            {
                                v450Upgrade();
                            }

                            if( $version <= 450 ) 
                            {
                                v451Upgrade();
                            }

                            if( $version <= 451 ) 
                            {
                                v452Upgrade();
                            }

                            if( $version <= 459 ) 
                            {
                                v500Upgrade();
                            }

                            if( $maj_min_version < 50 ) 
                            {
                                v501Upgrade();
                                v502Upgrade();
                                v503Upgrade();
                                v510Upgrade();
                            }
                            else
                            {
                                if( $maj_min_version == 50 ) 
                                {
                                    if( $revision_version < 1 ) 
                                    {
                                        v501Upgrade();
                                    }

                                    if( $revision_version < 2 ) 
                                    {
                                        v502Upgrade();
                                    }

                                    if( $revision_version < 3 ) 
                                    {
                                        v503Upgrade();
                                    }

                                    v510Upgrade();
                                }

                            }

                            if( $maj_min_version < 51 ) 
                            {
                                v511Upgrade();
                                v512Upgrade();
                                v520Upgrade();
                            }
                            else
                            {
                                if( $maj_min_version == 51 ) 
                                {
                                    if( $revision_version < 1 ) 
                                    {
                                        v511Upgrade();
                                    }

                                    if( $revision_version < 2 ) 
                                    {
                                        v512Upgrade();
                                    }

                                    v520Upgrade();
                                }

                            }

                            if( $maj_min_version < 52 ) 
                            {
                                v521Upgrade();
                                v522Upgrade();
                                v523Upgrade();
                                v524Upgrade();
                                v525Upgrade();
                                v526Upgrade();
                                v530prereleasedowngradesqlfor52($maj_min_version, $revision_version);
                                v527Upgrade();
                                v528Upgrade();
                                v529Upgrade();
                                v5210Upgrade();
                                v5211Upgrade();
                                v5212Upgrade();
                                v5213Upgrade();
                                v5214Upgrade();
                                v5215Upgrade();
                            }
                            else
                            {
                                if( $maj_min_version == 52 ) 
                                {
                                    if( $revision_version < 1 ) 
                                    {
                                        v521Upgrade();
                                    }

                                    if( $revision_version < 2 ) 
                                    {
                                        v522Upgrade();
                                    }

                                    if( $revision_version < 3 ) 
                                    {
                                        v523Upgrade();
                                    }

                                    if( $revision_version < 4 ) 
                                    {
                                        v524Upgrade();
                                    }

                                    if( $revision_version < 5 ) 
                                    {
                                        v525Upgrade();
                                    }

                                    if( $revision_version < 6 ) 
                                    {
                                        v526Upgrade();
                                    }

                                    if( $revision_version < 7 ) 
                                    {
                                        v530prereleasedowngradesqlfor52($maj_min_version, $revision_version);
                                        v527Upgrade();
                                    }

                                    if ($revision_version < 8) {
                                        v528Upgrade();
                                    }

                                    if ($revision_version < 9) {
                                        v529Upgrade();
                                    }

                                    if ($revision_version < 10) {
                                        v5210Upgrade();
                                    }

                                    if ($revision_version < 11) {
                                        v5211Upgrade();
                                    }

                                    if ($revision_version < 12) {
                                        v5212Upgrade();
                                    }

                                    if ($revision_version < 13) {
                                        v5213Upgrade();
                                    }

                                    if ($revision_version < 14) {
                                        v5214Upgrade();
                                    }

                                    if ($revision_version < 15) {
                                        v5215Upgrade();
                                    }

                                }

                            }

                            if( $maj_min_version == 53 ) 
                            {
                                v530prereleasedowngradesqlfor52($maj_min_version, $revision_version);
                                v527Upgrade();
                                v528Upgrade();
                                v529Upgrade();
                                v5210Upgrade();
                                v5211Upgrade();
                                v5212Upgrade();
                                v5213Upgrade();
                                v5214Upgrade();
                                v5215Upgrade();
                            }

                            echo "\n<p>You should now delete the install folder from your web server.</p>\n\n<p align=\"center\" style=\"font-size:16px;\"><a href=\"../";
                            echo $customadminpath;
                            echo "/\">Click here to go to the admin area now &raquo;</a></p>\n\n<h2>Thank you for choosing WHMCS!</h2>\n\n";
                        }

                    }

                }

            }

        }

    }

}

echo "\n<br />\n<br />\n<br />\n\n<div align=\"center\">Copyright &copy; WHMCS 2005-";
echo date("Y");
echo " NULLED BY MTIMER<br /><a href=\"http://www.whmcs.com/\" target=\"_blank\">www.whmcs.com</a></div>\n\n</div>\n\n</body>\n</html>\n";


function mysql_import_file($filename)
{
    $querycount = 0;
    $queryerrors = "";
    $lines = file($filename);
    if( !$lines ) 
    {
        $errmsg = "cannot open file " . $filename;
        return false;
    }

    $scriptfile = false;
    foreach( $lines as $line ) 
    {
        $line = trim($line);
        if( substr($line, 0, 2) != "--" ) 
        {
            $scriptfile .= " " . $line;
        }

    }
    $queries = explode(";", $scriptfile);
    foreach( $queries as $query ) 
    {
        $query = trim($query);
        $querycount++;
        if( $query == "" ) 
        {
            continue;
        }

        if( !mysql_query($query) ) 
        {
            $queryerrors .= "Line " . $querycount . " - " . mysql_error() . "<br>";
        }

    }
    if( $queryerrors ) 
    {
        echo "<b>Errors Occurred</b><br><br>Please open a ticket with the debug information below for support<br><br>File: " . $filename . "<br>" . $queryerrors;
    }

    return true;
}

function v321Upgrade()
{
    mysql_import_file("upgrade321.sql");
}

function v322Upgrade()
{
    mysql_import_file("upgrade322.sql");
}

function v323Upgrade()
{
    mysql_import_file("upgrade323.sql");
}

function v330Upgrade()
{
    mysql_import_file("upgrade330.sql");
    include("../configuration.php");
    $query = "SELECT id,AES_DECRYPT(cardnum,'" . $cc_encryption_hash . "') as cardnum,AES_DECRYPT(expdate,'" . $cc_encryption_hash . "') as expdate,AES_DECRYPT(issuenumber,'" . $cc_encryption_hash . "') as issuenumber,AES_DECRYPT(startdate,'" . $cc_encryption_hash . "') as startdate FROM tblclients";
    $result = mysql_query($query);
    while( $row = mysql_fetch_array($result) ) 
    {
        $id = $row["id"];
        $cardnum = $row["cardnum"];
        $cardexp = $row["expdate"];
        $cardissuenum = $row["issuenumber"];
        $cardstart = $row["startdate"];
        $query2 = "UPDATE tblclients SET cardnum=AES_ENCRYPT('" . $cardnum . "','54X6zoYZZnS35o6m5gEwGmYC6" . $cc_encryption_hash . "'),expdate=AES_ENCRYPT('" . $cardexp . "','54X6zoYZZnS35o6m5gEwGmYC6" . $cc_encryption_hash . "'),startdate=AES_ENCRYPT('" . $cardstart . "','54X6zoYZZnS35o6m5gEwGmYC6" . $cc_encryption_hash . "'),issuenumber=AES_ENCRYPT('" . $cardissuenum . "','54X6zoYZZnS35o6m5gEwGmYC6" . $cc_encryption_hash . "') WHERE id='" . $id . "'";
        mysql_query($query2);
    }
}

function v340Upgrade()
{
    mysql_import_file("upgrade340.sql");
    mysql_query("UPDATE tblhosting SET nextinvoicedate = nextduedate");
    mysql_query("UPDATE tbldomains SET nextinvoicedate = nextduedate");
    mysql_query("UPDATE tblhostingaddons SET nextinvoicedate = nextduedate");
}

function v341Upgrade()
{
    mysql_import_file("upgrade341.sql");
}

function v350Upgrade()
{
    $query = "ALTER TABLE tblupgrades ADD `orderid` INT( 1 ) NOT NULL AFTER `id`";
    mysql_query($query);
    $query = "SELECT * FROM tblorders WHERE upgradeids!=''";
    $result = mysql_query($query);
    while( $data = mysql_fetch_array($result) ) 
    {
        $orderid = $data["id"];
        $upgradeids = $data["upgradeids"];
        $upgradeids = explode(",", $upgradeids);
        foreach( $upgradeids as $upgradeid ) 
        {
            if( $upgradeid ) 
            {
                $query2 = "UPDATE tblupgrades SET orderid='" . $orderid . "' WHERE id='" . $upgradeid . "'";
                mysql_query($query2);
            }

        }
    }
    mysql_import_file("upgrade350.sql");
}

function v351Upgrade()
{
    mysql_import_file("upgrade351.sql");
}

function v360Upgrade()
{
    mysql_import_file("upgrade360.sql");
    $query = "SELECT COUNT(*) FROM tblpaymentgateways WHERE gateway='paypal'";
    $result = mysql_query($query);
    $data = mysql_fetch_array($result);
    $paypalenabled = $data[0];
    if( $paypalenabled ) 
    {
        $query = "INSERT INTO `tblpaymentgateways` (`id`, `gateway`, `type`, `setting`, `value`, `name`, `size`, `notes`, `description`, `order`) VALUES('', 'paypal', 'yesno', 'forceonetime', '', 'Force One Time Payments', 0, '', 'Tick this box to never show the subscription payment button', 0)";
        mysql_query($query);
    }

}

function v361Upgrade()
{
    mysql_import_file("upgrade361.sql");
    include_once("../includes/functions.php");
    $query = "SELECT id,value FROM tblregistrars";
    $result = mysql_query($query);
    while( $row = mysql_fetch_array($result) ) 
    {
        $id = $row["id"];
        $value = $row["value"];
        $value = encrypt($value);
        $query2 = "UPDATE tblregistrars SET value='" . $value . "' WHERE id='" . $id . "'";
        mysql_query($query2);
    }
}

function v362Upgrade()
{
    mysql_import_file("upgrade362.sql");
    mysql_query("ALTER TABLE `tblaffiliateswithdrawals` CHANGE `id` `id` INT( 10 ) NOT NULL AUTO_INCREMENT , CHANGE `affiliateid` `affiliateid` INT( 10 ) NOT NULL");
    mysql_query("CREATE INDEX affiliateid ON tblaffiliateswithdrawals (affiliateid)");
    $query = "SELECT * FROM tbladmins";
    $result = mysql_query($query);
    while( $data = mysql_fetch_array($result) ) 
    {
        $adminid = $data["id"];
        $supportdepts = $data["supportdepts"];
        $supportdepts = explode(",", $supportdepts);
        $newsupportdepts = ",";
        foreach( $supportdepts as $supportdept ) 
        {
            if( $supportdept ) 
            {
                $newsupportdepts .= ltrim($supportdept, 0) . ",";
            }

        }
        $query2 = "UPDATE tbladmins SET supportdepts='" . $newsupportdepts . "' WHERE id='" . $adminid . "'";
        mysql_query($query2);
    }
}

function v370UpgradeX($string)
{
    $key = "5a8ej8WndK\$3#9Ua425!hg741KknN";
    $result = "";
    $string = base64_decode($string);
    for( $i = 0; $i < strlen($string); $i++ ) 
    {
        $char = substr($string, $i, 1);
        $keychar = substr($key, $i % strlen($key) - 1, 1);
        $char = chr(ord($char) - ord($keychar));
        $result .= $char;
    }
    unset($key);
    return $result;
}

function v370Upgrade()
{
    mysql_import_file("upgrade370.sql");
    include_once("../includes/functions.php");
    $query = "SELECT id,password FROM tblclients";
    $result = mysql_query($query);
    while( $row = mysql_fetch_array($result) ) 
    {
        $id = $row[0];
        $value = $row[1];
        $value = v370UpgradeX($value);
        $value = encrypt($value);
        $query2 = "UPDATE tblclients SET password='" . $value . "' WHERE id='" . $id . "'";
        mysql_query($query2);
    }
    $query = "SELECT id,password FROM tblhosting";
    $result = mysql_query($query);
    while( $row = mysql_fetch_array($result) ) 
    {
        $id = $row[0];
        $value = $row[1];
        $value = v370UpgradeX($value);
        $value = encrypt($value);
        $query2 = "UPDATE tblhosting SET password='" . $value . "' WHERE id='" . $id . "'";
        mysql_query($query2);
    }
    $query = "SELECT id,value FROM tblregistrars";
    $result = mysql_query($query);
    while( $row = mysql_fetch_array($result) ) 
    {
        $id = $row[0];
        $value = $row[1];
        $value = v370UpgradeX($value);
        $value = encrypt($value);
        $query2 = "UPDATE tblregistrars SET value='" . $value . "' WHERE id='" . $id . "'";
        mysql_query($query2);
    }
    $query = "SELECT id,password FROM tblservers";
    $result = mysql_query($query);
    while( $row = mysql_fetch_array($result) ) 
    {
        $id = $row[0];
        $value = $row[1];
        $value = v370UpgradeX($value);
        $value = encrypt($value);
        $query2 = "UPDATE tblservers SET password='" . $value . "' WHERE id='" . $id . "'";
        mysql_query($query2);
    }
    $general_email_merge_fields = array(  );
    $general_email_merge_fields["CustomerID"] = "client_id";
    $general_email_merge_fields["CustomerName"] = "client_name";
    $general_email_merge_fields["CustomerFirstName"] = "client_first_name";
    $general_email_merge_fields["CustomerLastName"] = "client_last_name";
    $general_email_merge_fields["CompanyName"] = "client_company_name";
    $general_email_merge_fields["CustomerEmail"] = "client_email";
    $general_email_merge_fields["Address1"] = "client_address1";
    $general_email_merge_fields["Address2"] = "client_address2";
    $general_email_merge_fields["City"] = "client_city";
    $general_email_merge_fields["State"] = "client_state";
    $general_email_merge_fields["Postcode"] = "client_postcode";
    $general_email_merge_fields["Country"] = "client_country";
    $general_email_merge_fields["PhoneNumber"] = "client_phonenumber";
    $general_email_merge_fields["MAPassword"] = "client_password";
    $general_email_merge_fields["CAPassword"] = "client_password";
    $general_email_merge_fields["CreditBalance"] = "client_credit";
    $general_email_merge_fields["CCType"] = "client_cc_type";
    $general_email_merge_fields["CCLastFour"] = "client_cc_number";
    $general_email_merge_fields["CCExpiryDate"] = "client_cc_expiry";
    $general_email_merge_fields["SystemCompanyName"] = "company_name";
    $general_email_merge_fields["ClientAreaLink"] = "whmcs_url";
    $general_email_merge_fields["Signature"] = "signature";
    $general_email_merge_fields["http://smartftp.com"] = "http://www.filezilla-project.org/";
    $general_email_merge_fields["smart ftp"] = "FileZilla";
    $email_merge_fields = array(  );
    $email_merge_fields["InvoiceID"] = "invoice_id";
    $email_merge_fields["InvoiceNo"] = "invoice_num";
    $email_merge_fields["InvoiceNum"] = "invoice_num";
    $email_merge_fields["InvoiceDate"] = "invoice_date_created";
    $email_merge_fields["DueDate"] = "invoice_date_due";
    $email_merge_fields["DatePaid"] = "invoice_date_paid";
    $email_merge_fields["Description"] = "invoice_html_contents";
    $email_merge_fields["SubTotal"] = "invoice_subtotal";
    $email_merge_fields["Credit"] = "invoice_credit";
    $email_merge_fields["Tax"] = "invoice_tax";
    $email_merge_fields["TaxRate"] = "invoice_tax_rate";
    $email_merge_fields["Total"] = "invoice_total";
    $email_merge_fields["AmountDue"] = "invoice_total";
    $email_merge_fields["AmountPaid"] = "invoice_amount_paid";
    $email_merge_fields["Balance"] = "invoice_balance";
    $email_merge_fields["LastPaymentAmount"] = "invoice_last_payment_amount";
    $email_merge_fields["Status"] = "invoice_status";
    $email_merge_fields["TransactionID"] = "invoice_last_payment_transid";
    $email_merge_fields["PayButton"] = "invoice_payment_link";
    $email_merge_fields["PaymentMethod"] = "invoice_payment_method";
    $email_merge_fields["InvoiceLink"] = "invoice_link";
    $email_merge_fields["PreviousBalance"] = "invoice_previous_balance";
    $email_merge_fields["AllDueInvoices"] = "invoice_all_due_total";
    $email_merge_fields["TotalBalanceDue"] = "invoice_total_balance_due";
    $query = "SELECT * FROM tblemailtemplates WHERE type='invoice'";
    $result = mysql_query($query);
    while( $data = mysql_fetch_array($result) ) 
    {
        $email_id = $data["id"];
        $email_subject = $data["subject"];
        $email_message = $data["message"];
        foreach( $email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        foreach( $general_email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        $query = "UPDATE tblemailtemplates SET subject='" . mysql_real_escape_string($email_subject) . "',message='" . mysql_real_escape_string($email_message) . "' WHERE id='" . $email_id . "'";
        mysql_query($query);
    }
    $email_merge_fields = array(  );
    $email_merge_fields["OrderID"] = "domain_order_id";
    $email_merge_fields["RegDate"] = "domain_reg_date";
    $email_merge_fields["Status"] = "domain_status";
    $email_merge_fields["Domain"] = "domain_name";
    $email_merge_fields["Amount"] = "domain_first_payment_amount";
    $email_merge_fields["FirstPaymentAmount"] = "domain_first_payment_amount";
    $email_merge_fields["RecurringAmount"] = "domain_recurring_amount";
    $email_merge_fields["Registrar"] = "domain_registrar";
    $email_merge_fields["RegPeriod"] = "domain_reg_period";
    $email_merge_fields["ExpiryDate"] = "domain_expiry_date";
    $email_merge_fields["NextDueDate"] = "domain_next_due_date";
    $email_merge_fields["DaysUntilExpiry"] = "domain_days_until_expiry";
    $query = "SELECT * FROM tblemailtemplates WHERE type='domain'";
    $result = mysql_query($query);
    while( $data = mysql_fetch_array($result) ) 
    {
        $email_id = $data["id"];
        $email_subject = $data["subject"];
        $email_message = $data["message"];
        foreach( $email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        foreach( $general_email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        $query = "UPDATE tblemailtemplates SET subject='" . mysql_real_escape_string($email_subject) . "',message='" . mysql_real_escape_string($email_message) . "' WHERE id='" . $email_id . "'";
        mysql_query($query);
    }
    $email_merge_fields = array(  );
    $email_merge_fields["Name"] = "client_name";
    $email_merge_fields["TicketID"] = "ticket_id";
    $email_merge_fields["Department"] = "ticket_department";
    $email_merge_fields["DateOpened"] = "ticket_date_opened";
    $email_merge_fields["Subject"] = "ticket_subject";
    $email_merge_fields["Message"] = "ticket_message";
    $email_merge_fields["Status"] = "ticket_status";
    $email_merge_fields["Priority"] = "ticket_priority";
    $email_merge_fields["TicketURL"] = "ticket_url";
    $email_merge_fields["TicketLink"] = "ticket_link";
    $email_merge_fields["AutoCloseTime"] = "ticket_auto_close_time";
    $query = "SELECT * FROM tblemailtemplates WHERE type='support'";
    $result = mysql_query($query);
    while( $data = mysql_fetch_array($result) ) 
    {
        $email_id = $data["id"];
        $email_subject = $data["subject"];
        $email_message = $data["message"];
        foreach( $email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        foreach( $general_email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        $query = "UPDATE tblemailtemplates SET subject='" . mysql_real_escape_string($email_subject) . "',message='" . mysql_real_escape_string($email_message) . "' WHERE id='" . $email_id . "'";
        mysql_query($query);
    }
    $email_merge_fields = array(  );
    $email_merge_fields["OrderID"] = "service_order_id";
    $email_merge_fields["ProductID"] = "service_id";
    $email_merge_fields["RegDate"] = "service_reg_date";
    $email_merge_fields["Domain"] = "service_domain";
    $email_merge_fields["domain"] = "service_domain";
    $email_merge_fields["ServerName"] = "service_server_name";
    $email_merge_fields["ServerIP"] = "service_server_ip";
    $email_merge_fields["serverip"] = "service_server_ip";
    $email_merge_fields["DedicatedIP"] = "service_dedicated_ip";
    $email_merge_fields["AssignedIPs"] = "service_assigned_ips";
    $email_merge_fields["Nameserver1"] = "service_ns1";
    $email_merge_fields["Nameserver2"] = "service_ns2";
    $email_merge_fields["Nameserver3"] = "service_ns3";
    $email_merge_fields["Nameserver4"] = "service_ns4";
    $email_merge_fields["Nameserver1IP"] = "service_ns1_ip";
    $email_merge_fields["Nameserver2IP"] = "service_ns2_ip";
    $email_merge_fields["Nameserver3IP"] = "service_ns3_ip";
    $email_merge_fields["Nameserver4IP"] = "service_ns4_ip";
    $email_merge_fields["Product"] = "service_product_name";
    $email_merge_fields["Package"] = "service_product_name";
    $email_merge_fields["ConfigOptions"] = "service_config_options_html";
    $email_merge_fields["PaymentMethod"] = "service_payment_method";
    $email_merge_fields["Amount"] = "service_recurring_amount";
    $email_merge_fields["FirstPaymentAmount"] = "service_first_payment_amount";
    $email_merge_fields["RecurringAmount"] = "service_recurring_amount";
    $email_merge_fields["BillingCycle"] = "service_billing_cycle";
    $email_merge_fields["NextDueDate"] = "service_next_due_date";
    $email_merge_fields["Status"] = "service_status";
    $email_merge_fields["Username"] = "service_username";
    $email_merge_fields["Password"] = "service_password";
    $email_merge_fields["CpanelUsername"] = "service_username";
    $email_merge_fields["CpanelPassword"] = "service_password";
    $email_merge_fields["RootUsername"] = "service_username";
    $email_merge_fields["RootPassword"] = "service_password";
    $email_merge_fields["OrderNumber"] = "order_number";
    $email_merge_fields["OrderDetails"] = "order_details";
    $email_merge_fields["SSLConfigurationLink"] = "ssl_configuration_link";
    $query = "SELECT * FROM tblemailtemplates WHERE type='product'";
    $result = mysql_query($query);
    while( $data = mysql_fetch_array($result) ) 
    {
        $email_id = $data["id"];
        $email_subject = $data["subject"];
        $email_message = $data["message"];
        foreach( $email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        foreach( $general_email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        $query = "UPDATE tblemailtemplates SET subject='" . mysql_real_escape_string($email_subject) . "',message='" . mysql_real_escape_string($email_message) . "' WHERE id='" . $email_id . "'";
        mysql_query($query);
    }
    $email_merge_fields = array(  );
    $email_merge_fields["TotalVisitors"] = "affiliate_total_visits";
    $email_merge_fields["CurrentBalance"] = "affiliate_balance";
    $email_merge_fields["AmountWithdrawn"] = "affiliate_withdrawn";
    $email_merge_fields["ReferralsTable"] = "affiliate_referrals_table";
    $email_merge_fields["ReferralLink"] = "affiliate_referral_url";
    $query = "SELECT * FROM tblemailtemplates WHERE type='affiliate'";
    $result = mysql_query($query);
    while( $data = mysql_fetch_array($result) ) 
    {
        $email_id = $data["id"];
        $email_subject = $data["subject"];
        $email_message = $data["message"];
        foreach( $email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        foreach( $general_email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        $query = "UPDATE tblemailtemplates SET subject='" . mysql_real_escape_string($email_subject) . "',message='" . mysql_real_escape_string($email_message) . "' WHERE id='" . $email_id . "'";
        mysql_query($query);
    }
    $query = "SELECT * FROM tblemailtemplates WHERE type='general'";
    $result = mysql_query($query);
    while( $data = mysql_fetch_array($result) ) 
    {
        $email_id = $data["id"];
        $email_subject = $data["subject"];
        $email_message = $data["message"];
        foreach( $general_email_merge_fields as $old_email_merge_fields => $new_email_merge_fields ) 
        {
            $email_subject = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_subject);
            $email_message = str_replace("[" . $old_email_merge_fields . "]", "{\$" . $new_email_merge_fields . "}", $email_message);
        }
        $query = "UPDATE tblemailtemplates SET subject='" . mysql_real_escape_string($email_subject) . "',message='" . mysql_real_escape_string($email_message) . "' WHERE id='" . $email_id . "'";
        mysql_query($query);
    }
}

function v371Upgrade()
{
    mysql_import_file("upgrade371.sql");
}

function v372Upgrade()
{
    mysql_import_file("upgrade372.sql");
}

function v380Upgrade()
{
    $query = "ALTER TABLE `tblcustomfields` DROP `num` ;";
    mysql_query($query);
    mysql_query("INSERT INTO `tblconfiguration` (`setting`, `value`) VALUES ('EmailCSS', 'body,td { font-family: verdana; font-size: 11px; font-weight: normal; }\na { color: #0000ff; }')");
    mysql_import_file("upgrade380.sql");
    $query = "SELECT DISTINCT gid FROM tblproductconfigoptions";
    $result = mysql_query($query);
    while( $data = mysql_fetch_array($result) ) 
    {
        $productconfigoptionspid = $data["gid"];
        $query = "INSERT INTO tblproductconfiggroups (id,name,description) VALUES ('" . $productconfigoptionspid . "','Default Options','For product ID " . $productconfigoptionspid . " - created by upgrade script')";
        mysql_query($query);
        $query = "INSERT INTO tblproductconfiglinks (gid,pid) VALUES ('" . $productconfigoptionspid . "','" . $productconfigoptionspid . "')";
        mysql_query($query);
    }
}

function v381Upgrade()
{
    mysql_import_file("upgrade381.sql");
}

function v382Upgrade()
{
    mysql_import_file("upgrade382.sql");
}

function V4generateClientPW($plain, $salt = "")
{
    if( !$salt ) 
    {
        $seeds = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ#!%()#!%()#!%()";
        $seeds_count = strlen($seeds) - 1;
        for( $i = 0; $i < 5; $i++ ) 
        {
            $salt .= $seeds[rand(0, $seeds_count)];
        }
    }

    $pw = md5($salt . html_entity_decode($plain)) . ":" . $salt;
    return $pw;
}

function v400Upgrade()
{
    global $license;
    include_once("../includes/functions.php");
    if( !$_REQUEST["nomd5"] ) 
    {
        $query = "SELECT id, password FROM tblclients";
        $result = mysql_query($query);
        while( $data = mysql_fetch_assoc($result) ) 
        {
            $password = decrypt($data["password"]);
            $password = v4generateclientpw($password);
            $id = $data["id"];
            $upd_query = "UPDATE tblclients SET password = '" . $password . "' WHERE id = " . $id . ";";
            mysql_query($upd_query);
        }
        $query = "INSERT into tblconfiguration VALUES ('NOMD5', '');";
        mysql_query($query);
    }
    else
    {
        $query = "INSERT into tblconfiguration VALUES ('NOMD5', 'on');";
        mysql_query($query);
    }

    mysql_import_file("upgrade400.sql");
    $query = "SELECT id, category FROM tblknowledgebase";
    $result = mysql_query($query);
    while( $data = mysql_fetch_assoc($result) ) 
    {
        $id = $data["id"];
        $category = $data["category"];
        $query = "INSERT INTO tblknowledgebaselinks (categoryid,articleid) VALUES ('" . $category . "','" . $id . "')";
        mysql_query($query);
    }
    mysql_query("ALTER TABLE `tblknowledgebase` DROP `category`");
    $existingcurrency = array(  );
    $query = "SELECT * FROM tblconfiguration WHERE setting LIKE 'Currency%'";
    $result = mysql_query($query);
    while( $data = mysql_fetch_assoc($result) ) 
    {
        $existingcurrency[$data["setting"]] = $data["value"];
    }
    $query = "TRUNCATE tblcurrencies";
    mysql_query($query);
    $query = "INSERT INTO `tblcurrencies` (`id`, `code`, `prefix`, `suffix`, `format`, `rate`, `default`) VALUES\n(1, '" . $existingcurrency["Currency"] . "', '" . $existingcurrency["CurrencySymbol"] . "', ' " . $existingcurrency["Currency"] . "', 1, 1.00000, 1)";
    mysql_query($query);
    $query = "DELETE FROM tblconfiguration WHERE setting='Currency' OR setting='CurrencySymbol'";
    mysql_query($query);
    $query = "SELECT * FROM tblproducts WHERE paytype!='free' ORDER BY id ASC";
    $result = mysql_query($query);
    while( $data = mysql_fetch_assoc($result) ) 
    {
        $id = $data["id"];
        $paytype = $data["paytype"];
        $msetupfee = $data["msetupfee"];
        $qsetupfee = $data["qsetupfee"];
        $ssetupfee = $data["ssetupfee"];
        $asetupfee = $data["asetupfee"];
        $bsetupfee = $data["bsetupfee"];
        $monthly = $data["monthly"];
        $quarterly = $data["quarterly"];
        $semiannual = $data["semiannual"];
        $annual = $data["annual"];
        $biennial = $data["biennial"];
        if( $paytype == "recurring" ) 
        {
            if( $monthly <= 0 ) 
            {
                $monthly = "-1";
            }

            if( $quarterly <= 0 ) 
            {
                $quarterly = "-1";
            }

            if( $semiannual <= 0 ) 
            {
                $semiannual = "-1";
            }

            if( $annual <= 0 ) 
            {
                $annual = "-1";
            }

            if( $biennial <= 0 ) 
            {
                $biennial = "-1";
            }

        }

        $query = "INSERT INTO tblpricing (type,currency,relid,msetupfee,qsetupfee,ssetupfee,asetupfee,bsetupfee,monthly,quarterly,semiannually,annually,biennially) VALUES ('product','1','" . $id . "','" . $msetupfee . "','" . $qsetupfee . "','" . $ssetupfee . "','" . $asetupfee . "','" . $bsetupfee . "','" . $monthly . "','" . $quarterly . "','" . $semiannual . "','" . $annual . "','" . $biennial . "')";
        mysql_query($query);
    }
    $query = "SELECT * FROM tblproductconfigoptionssub ORDER BY id ASC";
    $result = mysql_query($query);
    while( $data = mysql_fetch_assoc($result) ) 
    {
        $id = $data["id"];
        $setup = $data["setup"];
        $monthly = $data["monthly"];
        $quarterly = $data["quarterly"];
        $semiannual = $data["semiannual"];
        $annual = $data["annual"];
        $biennial = $data["biennial"];
        $query = "INSERT INTO tblpricing (type,currency,relid,msetupfee,qsetupfee,ssetupfee,asetupfee,bsetupfee,monthly,quarterly,semiannually,annually,biennially) VALUES ('configoptions','1','" . $id . "','" . $setup . "','" . $setup . "','" . $setup . "','" . $setup . "','" . $setup . "','" . $monthly . "','" . $quarterly . "','" . $semiannual . "','" . $annual . "','" . $biennial . "')";
        mysql_query($query);
    }
    $query = "SELECT * FROM tbladdons ORDER BY id ASC";
    $result = mysql_query($query);
    while( $data = mysql_fetch_assoc($result) ) 
    {
        $id = $data["id"];
        $setupfee = $data["setupfee"];
        $recurring = $data["recurring"];
        $query = "INSERT INTO tblpricing (type,currency,relid,msetupfee,qsetupfee,ssetupfee,asetupfee,bsetupfee,monthly,quarterly,semiannually,annually,biennially) VALUES ('addon','1','" . $id . "','" . $setupfee . "','0','0','0','0','" . $recurring . "','0','0','0','0')";
        mysql_query($query);
    }
    $domainpricing = array(  );
    $query = "SELECT * FROM tbldomainpricing ORDER BY id ASC";
    $result = mysql_query($query);
    while( $data = mysql_fetch_assoc($result) ) 
    {
        $extension = $data["extension"];
        $regperiod = $data["registrationperiod"];
        if( $data["register"] != "0.00" && $data["transfer"] <= 0 ) 
        {
            $data["transfer"] = "-1";
        }

        if( $data["register"] != "0.00" && $data["renew"] <= 0 ) 
        {
            $data["renew"] = "-1";
        }

        $domainpricing[$extension][$regperiod]["register"] = $data["register"];
        $domainpricing[$extension][$regperiod]["transfer"] = $data["transfer"];
        $domainpricing[$extension][$regperiod]["renew"] = $data["renew"];
    }
    $query = "SELECT DISTINCT extension FROM tbldomainpricing";
    $result = mysql_query($query);
    while( $data = mysql_fetch_assoc($result) ) 
    {
        $extension = $data["extension"];
        $query = "SELECT id FROM tbldomainpricing WHERE extension='" . $extension . "' ORDER BY registrationperiod ASC";
        $result2 = mysql_query($query);
        $data = mysql_fetch_assoc($result2);
        $id = $data["id"];
        $query = "DELETE FROM tbldomainpricing WHERE extension='" . $extension . "' AND id!='" . $id . "'";
        mysql_query($query);
    }
    $query = "SELECT * FROM tbldomainpricing ORDER BY id ASC";
    $result = mysql_query($query);
    while( $data = mysql_fetch_assoc($result) ) 
    {
        $id = $data["id"];
        $extension = $data["extension"];
        $inserttype = "register";
        $query = "INSERT INTO tblpricing (type,currency,relid,msetupfee,qsetupfee,ssetupfee,asetupfee,bsetupfee,monthly,quarterly,semiannually,annually,biennially) VALUES ('domain" . $inserttype . "','1','" . $id . "','" . $domainpricing[$extension][1][$inserttype] . "','" . $domainpricing[$extension][2][$inserttype] . "','" . $domainpricing[$extension][3][$inserttype] . "','" . $domainpricing[$extension][4][$inserttype] . "','" . $domainpricing[$extension][5][$inserttype] . "','" . $domainpricing[$extension][6][$inserttype] . "','" . $domainpricing[$extension][7][$inserttype] . "','" . $domainpricing[$extension][8][$inserttype] . "','" . $domainpricing[$extension][9][$inserttype] . "','" . $domainpricing[$extension][10][$inserttype] . "')";
        mysql_query($query);
        $inserttype = "transfer";
        $query = "INSERT INTO tblpricing (type,currency,relid,msetupfee,qsetupfee,ssetupfee,asetupfee,bsetupfee,monthly,quarterly,semiannually,annually,biennially) VALUES ('domain" . $inserttype . "','1','" . $id . "','" . $domainpricing[$extension][1][$inserttype] . "','" . $domainpricing[$extension][2][$inserttype] . "','" . $domainpricing[$extension][3][$inserttype] . "','" . $domainpricing[$extension][4][$inserttype] . "','" . $domainpricing[$extension][5][$inserttype] . "','" . $domainpricing[$extension][6][$inserttype] . "','" . $domainpricing[$extension][7][$inserttype] . "','" . $domainpricing[$extension][8][$inserttype] . "','" . $domainpricing[$extension][9][$inserttype] . "','" . $domainpricing[$extension][10][$inserttype] . "')";
        mysql_query($query);
        $inserttype = "renew";
        $query = "INSERT INTO tblpricing (type,currency,relid,msetupfee,qsetupfee,ssetupfee,asetupfee,bsetupfee,monthly,quarterly,semiannually,annually,biennially) VALUES ('domain" . $inserttype . "','1','" . $id . "','" . $domainpricing[$extension][1][$inserttype] . "','" . $domainpricing[$extension][2][$inserttype] . "','" . $domainpricing[$extension][3][$inserttype] . "','" . $domainpricing[$extension][4][$inserttype] . "','" . $domainpricing[$extension][5][$inserttype] . "','" . $domainpricing[$extension][6][$inserttype] . "','" . $domainpricing[$extension][7][$inserttype] . "','" . $domainpricing[$extension][8][$inserttype] . "','" . $domainpricing[$extension][9][$inserttype] . "','" . $domainpricing[$extension][10][$inserttype] . "')";
        mysql_query($query);
    }
    mysql_query("ALTER TABLE `tblproducts` DROP `msetupfee`,DROP `qsetupfee`,DROP `ssetupfee`,DROP `asetupfee`,DROP `bsetupfee`,DROP `monthly`,DROP `quarterly`,DROP `semiannual`,DROP `annual`,DROP `biennial`");
    mysql_query("ALTER TABLE `tbldomainpricing`  DROP `registrationperiod`,  DROP `register`,  DROP `transfer`,  DROP `renew`");
    mysql_query("ALTER TABLE `tblproductconfigoptionssub` DROP `setup`,DROP `monthly`,DROP `quarterly`,DROP `semiannual`,DROP `annual`,DROP `biennial`");
    mysql_query("ALTER TABLE `tbladdons`  DROP `recurring`,  DROP `setupfee`");
    mysql_query("ALTER TABLE `mod_licensing` ADD `lastaccess` DATE NOT NULL");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://www.whmcs.com/license/v4upgrade.php?licensekey=" . $license);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
}

function v401Upgrade()
{
    mysql_import_file("upgrade401.sql");
}

function v410Upgrade()
{
    mysql_import_file("upgrade410.sql");
    include("../configuration.php");
    $query = "SELECT id,AES_DECRYPT(cardnum,'54X6zoYZZnS35o6m5gEwGmYC6" . $cc_encryption_hash . "') as cardnum,AES_DECRYPT(expdate,'54X6zoYZZnS35o6m5gEwGmYC6" . $cc_encryption_hash . "') as expdate,AES_DECRYPT(issuenumber,'54X6zoYZZnS35o6m5gEwGmYC6" . $cc_encryption_hash . "') as issuenumber,AES_DECRYPT(startdate,'54X6zoYZZnS35o6m5gEwGmYC6" . $cc_encryption_hash . "') as startdate FROM tblclients WHERE cardnum!=''";
    $result = mysql_query($query);
    while( $row = mysql_fetch_array($result) ) 
    {
        $userid = $row["id"];
        $cardnum = $row["cardnum"];
        $cardexp = $row["expdate"];
        $cardissuenum = $row["issuenumber"];
        $cardstart = $row["startdate"];
        $cardlastfour = substr($cardnum, 0 - 4);
        $cchash = md5($cc_encryption_hash . $userid);
        $query2 = "UPDATE tblclients SET cardlastfour='" . $cardlastfour . "',cardnum=AES_ENCRYPT('" . $cardnum . "','" . $cchash . "'),expdate=AES_ENCRYPT('" . $cardexp . "','" . $cchash . "'),startdate=AES_ENCRYPT('" . $cardstart . "','" . $cchash . "'),issuenumber=AES_ENCRYPT('" . $cardissuenum . "','" . $cchash . "') WHERE id='" . $userid . "'";
        mysql_query($query2);
    }
}

function v411Upgrade()
{
    mysql_import_file("upgrade411.sql");
}

function v412Upgrade()
{
    mysql_import_file("upgrade412.sql");
}

function v420Upgrade()
{
    mysql_import_file("upgrade420.sql");
}

function v421Upgrade()
{
    mysql_import_file("upgrade421.sql");
}

function v430Upgrade()
{
    mysql_import_file("upgrade430.sql");
    $query = "UPDATE tblconfiguration SET value='ssl' where setting = 'SMTPSSL' and value='on';";
    mysql_query($query);
}

function v431Upgrade()
{
    mysql_import_file("upgrade431.sql");
    $query = "UPDATE tblconfiguration SET value='cart' where setting = 'OrderFormTemplate' and value='singlepage';";
    mysql_query($query);
}

function v440Upgrade()
{
    mysql_import_file("upgrade440.sql");
}

function v441Upgrade()
{
    mysql_import_file("upgrade441.sql");
}

function v442Upgrade()
{
    mysql_import_file("upgrade442.sql");
    $query = "INSERT INTO tblconfiguration (setting,value) VALUES ('CCDoNotRemoveOnExpiry','')";
    mysql_query($query);
}

function v450Upgrade()
{
    $query = "UPDATE tblemailtemplates SET name='Hosting Account Welcome Email' WHERE name='Hosting Account Welcome Email (cPanel)'";
    mysql_query($query);
    $query = "UPDATE tblemailtemplates SET custom='1' WHERE name='Hosting Account Welcome Email (DirectAdmin)'";
    mysql_query($query);
    $query = "UPDATE tblemailtemplates SET custom='1' WHERE name='Hosting Account Welcome Email (Plesk)'";
    mysql_query($query);
    mysql_import_file("upgrade450.sql");
}

function v451Upgrade()
{
    mysql_import_file("upgrade451.sql");
}

function v452Upgrade()
{
    mysql_query("ALTER TABLE `tblsslorders` CHANGE `status` `status` TEXT NOT NULL");
    mysql_query("UPDATE `tblsslorders` SET status='Awaiting Configuration' WHERE status='Incomplete'");
    mysql_query("ALTER TABLE `tblsslorders` ADD `configdata` TEXT NOT NULL AFTER `certtype`");
    mysql_import_file("upgrade452.sql");
}

function v500Upgrade()
{
    mysql_import_file("upgrade500.sql");
    mysql_query("INSERT INTO `tblconfiguration` (`setting`, `value`) VALUES('EmailGlobalHeader', '&lt;p&gt;&lt;a href=&quot;{\$company_domain}&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;{\$company_logo_url}&quot; alt=&quot;{\$company_name}&quot; border=&quot;0&quot; /&gt;&lt;/a&gt;&lt;/p&gt;')");
    mysql_query("INSERT INTO `tblconfiguration` (`setting`, `value`) VALUES('EmailGlobalFooter', '')");
}

function v501Upgrade()
{
    mysql_import_file("upgrade501.sql");
    mysql_query("UPDATE tbladmins SET template='blend' WHERE template='simple'");
    mysql_query("ALTER TABLE `tblclients`  ADD `bankname` TEXT NOT NULL AFTER `issuenumber`,  ADD `banktype` TEXT NOT NULL AFTER `bankname`,  ADD `bankcode` BLOB NOT NULL AFTER `banktype`,  ADD `bankacct` BLOB NOT NULL AFTER `bankcode`");
}

function v502Upgrade()
{
    mysql_import_file("upgrade502.sql");
}

function v503Upgrade()
{
    mysql_import_file("upgrade503.sql");
    mysql_query("UPDATE tblconfiguration SET value='' WHERE setting='License'");
    mysql_query("UPDATE tbladminroles SET widgets = CONCAT(widgets,',supporttickets_overview')");
}

function v510Upgrade()
{
    mysql_import_file("upgrade510.sql");
    mysql_query("UPDATE tblpaymentgateways SET value='CC' WHERE gateway='worldpayfuturepay' AND setting='type'");
    $result = mysql_query("SELECT id FROM tblcustomfields WHERE type='client' AND fieldname='FuturePay ID'");
    $data = mysql_fetch_array($result);
    $futurepayfid = $data[0];
    if( $futurepayfid ) 
    {
        $result = mysql_query("SELECT relid,value FROM tblcustomfieldsvalues WHERE fieldid=" . $futurepayfid);
        while( $data = mysql_fetch_array($result) ) 
        {
            $userid = $data[0];
            $fpid = $data[1];
            mysql_query("UPDATE tblclients SET gatewayid='" . $fpid . "' WHERE id=" . $userid . " AND gatewayid=''");
            mysql_query("DELETE FROM tblcustomfieldsvalues WHERE fieldid=" . $futurepayfid . " AND relid=" . $userid);
        }
        mysql_query("DELETE FROM tblcustomfields WHERE id=" . $futurepayfid);
    }

    mysql_query("ALTER TABLE  `tblcalendar` ADD  `start` INT( 10 ) NOT NULL AFTER  `desc` , ADD  `end` INT( 10 ) NOT NULL AFTER  `start`, ADD  `allday` INT( 1 ) NOT NULL AFTER  `end`, ADD  `recurid` INT( 10 ) NOT NULL AFTER  `adminid`");
    $result = mysql_query("SELECT * FROM tblcalendar");
    while( $data = mysql_fetch_array($result) ) 
    {
        $id = $data["id"];
        $day = $data["day"];
        $month = $data["month"];
        $year = $data["year"];
        $startt1 = $data["startt1"];
        $startt2 = $data["startt2"];
        $endt1 = $data["endt1"];
        $endt2 = $data["endt2"];
        $start = mktime($startt1, $startt2, 0, $month, $day, $year);
        $end = $endt1 && $endt2 ? mktime($endt1, $endt2, 0, $month, $day, $year) : "0";
        mysql_query("UPDATE tblcalendar SET start='" . $start . "',end='" . $end . "' WHERE id=" . $id);
    }
    mysql_query("ALTER TABLE `tblcalendar` DROP `day`,DROP `month`,DROP `year`,DROP `startt1`,DROP `startt2`,DROP `endt1`,DROP `endt2`");
}

function v511Upgrade()
{
    mysql_import_file("upgrade511.sql");
    mysql_query("ALTER TABLE  `tblcalendar` ADD  `start` INT( 10 ) NOT NULL AFTER  `desc` , ADD  `end` INT( 10 ) NOT NULL AFTER  `start`, ADD  `allday` INT( 1 ) NOT NULL AFTER  `end`, ADD  `recurid` INT( 10 ) NOT NULL AFTER  `adminid`");
    $result = mysql_query("SELECT * FROM tblcalendar");
    while( $data = mysql_fetch_array($result) ) 
    {
        $id = $data["id"];
        $day = $data["day"];
        $month = $data["month"];
        $year = $data["year"];
        $startt1 = $data["startt1"];
        $startt2 = $data["startt2"];
        $endt1 = $data["endt1"];
        $endt2 = $data["endt2"];
        $start = mktime($startt1, $startt2, 0, $month, $day, $year);
        $end = $endt1 && $endt2 ? mktime($endt1, $endt2, 0, $month, $day, $year) : "0";
        mysql_query("UPDATE tblcalendar SET start='" . $start . "',end='" . $end . "' WHERE id=" . $id);
    }
    mysql_query("ALTER TABLE `tblcalendar` DROP `day`,DROP `month`,DROP `year`,DROP `startt1`,DROP `startt2`,DROP `endt1`,DROP `endt2`");
    mysql_query("ALTER TABLE  `tblpromotions` ADD `lifetimepromo` INT(1) NOT NULL AFTER `uses`");
    mysql_query("ALTER TABLE  `tblquotes` ADD  `datesent` DATE NOT NULL , ADD  `dateaccepted` DATE NOT NULL");
    mysql_query("UPDATE tbladminroles SET widgets = CONCAT(widgets,',calendar')");
    mysql_query("UPDATE tbladmins SET  `homewidgets`='getting_started:true,orders_overview:true,supporttickets_overview:true,my_notes:true,client_activity:true,open_invoices:true,activity_log:true|income_overview:true,system_overview:true,whmcs_news:true,sysinfo:true,admin_activity:true,todo_list:true,network_status:true,income_forecast:true|' WHERE id=1");
}

function v512Upgrade()
{
    mysql_import_file("upgrade512.sql");
    mysql_query("ALTER TABLE `tblnotes` CHANGE `important` `sticky` INT( 1 ) NOT NULL");
}

function v520Upgrade()
{
    mysql_import_file("upgrade520.sql");
    include_once("../includes/functions.php");
    $newips = array(  );
    $query = "SELECT value FROM tblconfiguration WHERE setting='APIAllowedIPs'";
    $result = mysql_query($query);
    $data = mysql_fetch_array($result);
    $apiips = $data["value"];
    $apiips = explode("\n", $apiips);
    foreach( $apiips as $ip ) 
    {
        $newips[] = array( "ip" => trim($ip), "note" => "" );
    }
    $query = "UPDATE tblconfiguration SET value='" . mysql_real_escape_string(serialize($newips)) . "' WHERE setting='APIAllowedIPs'";
    mysql_query($query);
    $query = "SELECT value FROM tblconfiguration WHERE setting='SystemURL'";
    $result = mysql_query($query);
    $data = mysql_fetch_array($result);
    $sysurl = $data["value"];
    if( $sysurl == "http://www.yourdomain.com/whmcs/" ) 
    {   
        if($_SERVER["SERVER_NAME"] == "localhost") {
            $sysurl = "http://" . $_SERVER["SERVER_ADDR"] .($_SERVER["SERVER_PORT"]==80 ? '':':'.$_SERVER["SERVER_PORT"]). $_SERVER["REQUEST_URI"];
        }else $sysurl = "http://" . $_SERVER["SERVER_NAME"] .($_SERVER["SERVER_PORT"]==80 ? '':':'.$_SERVER["SERVER_PORT"]). $_SERVER["REQUEST_URI"];
        $sysurl = str_replace("?step=5", "", $sysurl);
        $sysurl = str_replace("install/install.php", "", $sysurl);
        $query = "UPDATE tblconfiguration SET value='" . mysql_real_escape_string($sysurl) . "' WHERE setting='SystemURL'";
        mysql_query($query);
    }

    $query = "SELECT id,password FROM tblticketdepartments";
    $result = mysql_query($query);
    while( $row = mysql_fetch_array($result) ) 
    {
        $id = $row["id"];
        $value = encrypt($row["password"]);
        $query2 = "UPDATE tblticketdepartments SET password='" . $value . "' WHERE id='" . $id . "'";
        mysql_query($query2);
    }
    $query = "SELECT value FROM tblconfiguration WHERE setting='FTPBackupPassword'";
    $result = mysql_query($query);
    $data = mysql_fetch_array($result);
    $ftppass = encrypt($data["value"]);
    $query = "UPDATE tblconfiguration SET value='" . $ftppass . "' WHERE setting='FTPBackupPassword'";
    mysql_query($query);
    $query = "SELECT value FROM tblconfiguration WHERE setting='SMTPPassword'";
    $result = mysql_query($query);
    $data = mysql_fetch_array($result);
    $smtppass = encrypt($data["value"]);
    $query = "UPDATE tblconfiguration SET value='" . $smtppass . "' WHERE setting='SMTPPassword'";
    mysql_query($query);
}

function v521Upgrade()
{
    mysql_import_file("upgrade521.sql");
}

function v522Upgrade()
{
    mysql_import_file("upgrade522.sql");
}

function v523Upgrade()
{
    mysql_import_file("upgrade523.sql");
}

function v524Upgrade()
{
    mysql_import_file("upgrade524.sql");
}

function v525Upgrade()
{
    $query = "UPDATE tblconfiguration SET value='5.2.5' WHERE setting='Version'";
    mysql_query($query);
}

function v526Upgrade()
{
    $query = "UPDATE tblconfiguration SET value='5.2.6' WHERE setting='Version'";
    mysql_query($query);
}

function v527Upgrade()
{
    $query = "UPDATE tblconfiguration SET value='5.2.7' WHERE setting='Version'";
    mysql_query($query);
}

function v528Upgrade() {
    $query = "UPDATE tblconfiguration SET value='5.2.8' WHERE setting='Version'";
    mysql_query($query);
}

function v529Upgrade() {
    $query = "UPDATE tblconfiguration SET value='5.2.9' WHERE setting='Version'";
    mysql_query($query);
}

function v5210Upgrade() {
    $query = "UPDATE tblconfiguration SET value='5.2.10' WHERE setting='Version'";
    mysql_query($query);
}

function v5211Upgrade() {
    $query = "UPDATE tblconfiguration SET value='5.2.11' WHERE setting='Version'";
    mysql_query($query);
}

function v5212Upgrade() {
    $query = "UPDATE tblconfiguration SET value='5.2.12' WHERE setting='Version'";
    mysql_query($query);
}

function v5213Upgrade() {
    $query = "UPDATE tblconfiguration SET value='5.2.13' WHERE setting='Version'";
    mysql_query($query);
}

function v5214Upgrade() {
    $query = "UPDATE tblconfiguration SET value='5.2.14' WHERE setting='Version'";
    mysql_query($query);
}

function v5215Upgrade() {
    $query = "UPDATE tblconfiguration SET value='5.2.15' WHERE setting='Version'";
    mysql_query($query);
}

function v530PreReleaseDowngradeSqlFor52($maj_min_version, $revision_version)
{
    if( previousVersionMayContainPreRelease53Sql($maj_min_version, $revision_version) ) 
    {
        $file = realpath(dirname(__FILE__)) . "/53PreReleaseSqlFix52.txt";
        if( file_exists($file) ) 
        {
            $stmts = file($file);
            foreach( $stmts as $query ) 
            {
                mysql_query(trim($query));
            }
        }

    }

}

function previousVersionMayContainPreRelease53Sql($maj_min_version, $revision_version)
{
    $response = false;
    if( $maj_min_version == 53 || $maj_min_version < 52 || $maj_min_version == 52 && $revision_version < 7 ) 
    {
        $response = true;
    }

    return $response;
}


?>