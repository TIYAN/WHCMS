<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

function getShortLastReplyTime($lastreply) {
	$datetime = strtotime("now");
	$date2 = strtotime("" . $lastreply);
	$holdtotsec = $datetime - $date2;
	$holdtotmin = ($datetime - $date2) / 60;
	$holdtothr = ($datetime - $date2) / 3600;
	$holdtotday = intval(($datetime - $date2) / 86400);

	if (0 < $holdtotday) {
		$str .= $holdtotday . "d ";
	}

	$holdhr = intval($holdtothr - $holdtotday * 24);
	$str .= $holdhr . "h ";
	$holdmr = intval($holdtotmin - ($holdhr * 60 + $holdtotday * 1440));
	$str .= $holdmr . "m";
	return $str;
}

function getLastReplyTime($lastreply, $from = "now") {
	$datetime = strtotime($from);
	$date2 = strtotime($lastreply);
	$holdtotsec = $datetime - $date2;
	$holdtotmin = ($datetime - $date2) / 60;
	$holdtothr = ($datetime - $date2) / 3600;
	$holdtotday = intval(($datetime - $date2) / 86400);

	if (0 < $holdtotday) {
		$str .= $holdtotday . " Days ";
	}

	$holdhr = intval($holdtothr - $holdtotday * 24);
	$str .= $holdhr . " Hours ";
	$holdmr = intval($holdtotmin - ($holdhr * 60 + $holdtotday * 1440));
	$str .= $holdmr . " Minutes ";
	$holdsr = intval($holdtotsec - ($holdhr * 3600 + $holdmr * 60 + 86400 * $holdtotday));
	$str .= $holdsr . " Seconds";
	$str .= " Ago";
	return $str;
}

function getStatusColour($tstatus) {
	global $_LANG;
	static $ticketcolors = array();

	if (!array_key_exists($tstatus, $ticketcolors)) {
		$ticketcolors[$tstatus] = $color = get_query_val("tblticketstatuses", "color", array("title" => $tstatus));
	}
	else {
		$color = $ticketcolors[$tstatus];
	}

	$langstatus = preg_replace("/[^a-z]/i", "", strtolower($tstatus));

	if ($_LANG["supportticketsstatus" . $langstatus]) {
		$tstatus = $_LANG["supportticketsstatus" . $langstatus];
	}

	$statuslabel = "";

	if ($color) {
		$statuslabel .= "<span style=\"color:" . $color . "\">";
	}

	$statuslabel .= $tstatus;

	if ($color) {
		$statuslabel .= "</span>";
	}

	return $statuslabel;
}

function ticketAutoHyperlinks($message) {
	return autoHyperLink($message);
}

function AddNote($tid, $message) {
	if (!function_exists("getAdminName")) {
		require ROOTDIR . "/includes/adminfunctions.php";
	}

	$adminname = getAdminName();
	insert_query("tblticketnotes", array("ticketid" => $tid, "date" => "now()", "admin" => $adminname, "message" => nl2br($message)));
	addTicketLog($tid, "Ticket Note Added");
	run_hook("TicketAddNote", array("ticketid" => $tid, "message" => $message, "adminid" => $_SESSION['adminid']));
}

function AdminRead($tid) {
	$result = select_query("tbltickets", "adminunread", array("id" => $tid));
	$data = mysql_fetch_assoc($result);
	$adminread = $data['adminunread'];
	$adminreadarray = ($adminread ? explode(",", $adminread) : array());

	if (!in_array($_SESSION['adminid'], $adminreadarray)) {
		$adminreadarray[] = $_SESSION['adminid'];
		update_query("tbltickets", array("adminunread" => implode(",", $adminreadarray)), array("id" => $tid));
	}

}

function ClientRead($tid) {
	update_query("tbltickets", array("clientunread" => ""), array("id" => $tid));
}

function addTicketLog($tid, $action) {
	if (isset($_SESSION['adminid'])) {
		if (!function_exists("getAdminName")) {
			require ROOTDIR . "/includes/adminfunctions.php";
		}

		$action .= " (by " . getAdminName() . ")";
	}

	insert_query("tblticketlog", array("date" => "now()", "tid" => $tid, "action" => $action));
}

function AddtoLog($tid, $action) {
	addTicketLog($tid, $action);
}

function getDepartmentName($deptid) {
	$result = select_query("tblticketdepartments", "name", array("id" => $deptid));
	$data = mysql_fetch_array($result);
	$department = $data['name'];
	return $department;
}

function openNewTicket($userid, $contactid, $deptid, $tickettitle, $message, $urgency, $attachedfile = "", $from = "", $relatedservice = "", $ccemail = "", $noemail = "", $admin = "") {
	global $CONFIG;

	$result = select_query("tblticketdepartments", "", array("id" => $deptid));
	$data = mysql_fetch_array($result);
	$deptid = $data['id'];
	$noautoresponder = $data['noautoresponder'];

	if (!$deptid) {
		exit("Department Not Found. Exiting.");
	}

	$ccemail = trim($ccemail);

	if ($userid) {
		$name = $email = "";

		if (0 < $contactid) {
			$data = get_query_vals("tblcontacts", "firstname,lastname,email", array("id" => $contactid, "userid" => $userid));
			$ccemail .= ($ccemail ? "," . $data['email'] : $data['email']);
		}
		else {
			$data = get_query_vals("tblclients", "firstname,lastname,email", array("id" => $userid));
		}


		if ($admin) {
			$message = str_replace("[NAME]", $data['firstname'] . " " . $data['lastname'], $message);
			$message = str_replace("[FIRSTNAME]", $data['firstname'], $message);
			$message = str_replace("[EMAIL]", $data['email'], $message);
		}

		$clientname = $data['firstname'] . " " . $data['lastname'];
	}
	else {
		if ($admin) {
			$message = str_replace("[NAME]", $from['name'], $message);
			$message = str_replace("[FIRSTNAME]", current(explode(" ", $from['name'])), $message);
			$message = str_replace("[EMAIL]", $from['email'], $message);
		}

		$clientname = $from['name'];
	}

	$length = 8;
	$seeds = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$c = null;
	$seeds_count = strlen($seeds) - 1;
	$i = 0;

	while ($i < $length) {
		$c .= $seeds[rand(0, $seeds_count)];
		++$i;
	}

	$tid = genTicketMask();

	if (!in_array($urgency, array("High", "Medium", "Low"))) {
		$urgency = "Medium";
	}

	$table = "tbltickets";
	$array = array("tid" => $tid, "userid" => $userid, "contactid" => $contactid, "did" => $deptid, "date" => "now()", "title" => $tickettitle, "message" => $message, "urgency" => $urgency, "status" => "Open", "attachment" => $attachedfile, "lastreply" => "now()", "name" => $from['name'], "email" => $from['email'], "c" => $c, "clientunread" => "1", "adminunread" => "", "service" => $relatedservice, "cc" => $ccemail);

	if ($admin) {
		$array['admin'] = getAdminName();
	}

	$id = insert_query($table, $array);
	$tid = genTicketMask($id);
	update_query("tbltickets", array("tid" => $tid), array("id" => $id));

	if (!$noemail) {
		if ($admin) {
			sendMessage("Support Ticket Opened by Admin", $id);
		}
		else {
			if (!$noautoresponder) {
				sendMessage("Support Ticket Opened", $id);
			}
		}
	}

	$deptname = getDepartmentName($deptid);

	if (!$noemail) {
		sendAdminMessage("Support Ticket Created", array("ticket_id" => $id, "ticket_tid" => $tid, "client_id" => $userid, "client_name" => $clientname, "ticket_department" => $deptname, "ticket_subject" => $tickettitle, "ticket_priority" => $urgency, "ticket_message" => ticketMessageFormat($message)), "support", $deptid, "", true);
	}


	if ($admin) {
		addTicketLog($id, "New Support Ticket Opened");
	}
	else {
		addTicketLog($id, "New Support Ticket Opened");
	}

	run_hook("TicketOpen" . ($admin ? "Admin" : ""), array("ticketid" => $id, "userid" => $userid, "deptid" => $deptid, "deptname" => $deptname, "subject" => $tickettitle, "message" => $message, "priority" => $urgency));
	return array("ID" => $id, "TID" => $tid, "C" => $c, "Subject" => $tickettitle);
}

function AddReply($ticketid, $userid, $contactid, $message, $admin, $attachfile = "", $from = "", $status = "", $noemail = "", $api = false) {
	global $CONFIG;

	if ($admin) {
		$data = get_query_vals("tbltickets", "userid,contactid,name,email", array("id" => $ticketid));

		if (0 < $data['userid']) {
			if (0 < $data['contactid']) {
				$data = get_query_vals("tblcontacts", "firstname,lastname,email", array("id" => $data['contactid'], "userid" => $data['userid']));
			}
			else {
				$data = get_query_vals("tblclients", "firstname,lastname,email", array("id" => $data['userid']));
			}

			$message = str_replace("[NAME]", $data['firstname'] . " " . $data['lastname'], $message);
			$message = str_replace("[FIRSTNAME]", $data['firstname'], $message);
			$message = str_replace("[EMAIL]", $data['email'], $message);
		}
		else {
			$message = str_replace("[NAME]", $data['name'], $message);
			$message = str_replace("[FIRSTNAME]", current(explode(" ", $data['name'])), $message);
			$message = str_replace("[EMAIL]", $data['email'], $message);
		}


		if (!function_exists("getAdminName")) {
			require ROOTDIR . "/includes/adminfunctions.php";
		}

		$adminname = ($api ? $admin : getAdminName());
	}

	$table = "tblticketreplies";
	$array = array("tid" => $ticketid, "userid" => $userid, "contactid" => $contactid, "name" => $from['name'], "email" => $from['email'], "date" => "now()", "message" => $message, "admin" => $adminname, "attachment" => $attachfile);
	$ticketreplyid = insert_query($table, $array);
	$result = select_query("tbltickets", "tid,did,title,urgency,flag", array("id" => $ticketid));
	$data = mysql_fetch_array($result);
	$tid = $data['tid'];
	$deptid = $data['did'];
	$tickettitle = $data['title'];
	$urgency = $data['urgency'];
	$flagadmin = $data['flag'];

	if ($userid) {
		$result = select_query("tblclients", "firstname,lastname", array("id" => $userid));
		$data = mysql_fetch_array($result);
		$clientname = $data['firstname'] . " " . $data['lastname'];
	}
	else {
		$clientname = $from['name'];
	}

	$deptname = getDepartmentName($deptid);

	if ($admin) {
		if ($status == "") {
			$status = "Answered";
		}

		$updateqry = array("status" => $status, "clientunread" => "1", "lastreply" => "now()");

		if ($CONFIG['TicketLastReplyUpdateClientOnly']) {
			unset($updateqry['lastreply']);
		}

		update_query("tbltickets", $updateqry, array("id" => $ticketid));
		addTicketLog($ticketid, "New Ticket Response");

		if (!$noemail) {
			sendMessage("Support Ticket Reply", $ticketid, $ticketreplyid);
		}

		run_hook("TicketAdminReply", array("ticketid" => $ticketid, "replyid" => $ticketreplyid, "deptid" => $deptid, "deptname" => $deptname, "subject" => $tickettitle, "message" => $message, "priority" => $urgency, "admin" => $adminname, "status" => $status));
		return null;
	}

	$status = "Customer-Reply";
	update_query("tbltickets", array("status" => "Customer-Reply", "clientunread" => "1", "adminunread" => "", "lastreply" => "now()"), array("id" => $ticketid));
	addTicketLog($ticketid, "New Ticket Response made by User");

	if ($flagadmin) {
		sendAdminMessage("Support Ticket Response", array("ticket_id" => $ticketid, "ticket_tid" => $tid, "client_id" => $userid, "client_name" => $clientname, "ticket_department" => $deptname, "ticket_subject" => $tickettitle, "ticket_priority" => $urgency, "ticket_message" => ticketMessageFormat($message)), "support", "", $flagadmin);
	}
	else {
		if (!$noemail) {
			sendAdminMessage("Support Ticket Response", array("ticket_id" => $ticketid, "ticket_tid" => $tid, "client_id" => $userid, "client_name" => $clientname, "ticket_department" => $deptname, "ticket_subject" => $tickettitle, "ticket_priority" => $urgency, "ticket_message" => ticketMessageFormat($message)), "support", $deptid, "", true);
		}
	}

	run_hook("TicketUserReply", array("ticketid" => $ticketid, "replyid" => $ticketreplyid, "userid" => $userid, "deptid" => $deptid, "deptname" => $deptname, "subject" => $tickettitle, "message" => $message, "priority" => $urgency, "status" => $status));
}

function processPipedTicket($to, $name, $email, $subject, $message, $attachment) {
	global $whmcs;
	global $CONFIG;
	global $supportticketpipe;
	global $pipenonregisteredreplyonly;

	$supportticketpipe = true;
	$decodestring = $subject . "##||-MESSAGESPLIT-||##" . $message;
	$decodestring = pipeDecodeString($decodestring);
	$decodestring = explode("##||-MESSAGESPLIT-||##", $decodestring);
	$subject = $decodestring[0];
	$message = $decodestring[1];
	$raw_message = $message;
	$result = select_query("tblticketspamfilters", "", "");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$type = $data['type'];
		$content = $data['content'];

		if ($type == "sender") {
			if (strtolower($content) == strtolower($email)) {
				$mailstatus = "Blocked Sender";
			}
		}


		if ($type == "subject") {
			if (strpos("x" . strtolower($subject), strtolower($content))) {
				$mailstatus = "Blocked Subject";
			}
		}


		if ($type == "phrase") {
			if (strpos("x" . strtolower($message), strtolower($content))) {
				$mailstatus = "Blocked Phrase";
			}
		}
	}

	run_hook("TicketPiping", array());

	if (!$mailstatus) {
		$pos = strpos($subject, "[Ticket ID: ");

		if ($pos === false) {
		}
		else {
			$tid = substr($subject, $pos + 12);
			$tid = substr($tid, 0, strpos($tid, "]"));
			$result = select_query("tbltickets", "", array("tid" => $tid));
			$data = mysql_fetch_array($result);
			$tid = $data['id'];
		}

		$to = trim($to);
		$toemails = explode(",", $to);
		$deptid = "";
		foreach ($toemails as $toemail) {

			if (!$deptid) {
				$result = select_query("tblticketdepartments", "", array("email" => trim(strtolower($toemail))));
				$data = mysql_fetch_array($result);
				$deptid = $data['id'];
				$to = $data['email'];
				$deptclientsonly = $data['clientsonly'];
				$deptpiperepliesonly = $data['piperepliesonly'];
				continue;
			}
		}


		if (!$deptid) {
			$result = select_query("tblticketdepartments", "", array("hidden" => ""), "order", "ASC", "1");
			$data = mysql_fetch_array($result);
			$deptid = $data['id'];
			$to = $data['email'];
			$deptclientsonly = $data['clientsonly'];
			$deptpiperepliesonly = $data['piperepliesonly'];
		}


		if (!$deptid) {
			$mailstatus = "Department Not Found";
		}
		else {
			if ($to == $email) {
				$mailstatus = "Blocked Potential Email Loop";
			}
			else {
				$messagebackup = $message;
				$result = select_query("tblticketbreaklines", "", "", "id", "ASC");

				while ($data = mysql_fetch_array($result)) {
					$breakpos = strpos($message, $data['breakline']);

					if ($breakpos) {
						$message = substr($message, 0, $breakpos);
					}
				}


				if (!$message) {
					$message = $messagebackup;
				}

				$message = trim($message);
				$result = select_query("tbladmins", "id", array("email" => $email));
				$data = mysql_fetch_array($result);
				$adminid = $data['id'];

				if ($adminid) {
					if ($tid) {
						$_SESSION['adminid'] = $adminid;
						AddReply($tid, "", "", $message, true, $attachment);
						$_SESSION['adminid'] = "";
						$mailstatus = "Ticket Reply Imported Successfully";
					}
					else {
						$mailstatus = "Ticket ID Not Found";
					}
				}
				else {
					$result = select_query("tblclients", "id", array("email" => $email));
					$data = mysql_fetch_array($result);
					$userid = $data['id'];

					if (!$userid) {
						$result = select_query("tblcontacts", "id,userid", array("email" => $email));
						$data = mysql_fetch_array($result);
						$userid = $data['userid'];
						$contactid = $data['id'];

						if ($userid) {
							$ccemail = $email;
						}
					}


					if ($deptclientsonly == "on" && !$userid) {
						$mailstatus = "Unregistered Email Address";
						$result = select_query("tblticketdepartments", "", array("id" => $deptid));
						$data = mysql_fetch_array($result);
						$noautoresponder = $data['noautoresponder'];

						if (!$noautoresponder) {
							sendMessage("Bounce Message", "", array($name, $email));
						}
					}
					else {
						if ($userid == "") {
							$from['name'] = $name;
							$from['email'] = $email;
						}

						$filterdate = date("YmdHis", mktime(date("H"), date("i") - 15, date("s"), date("m"), date("d"), date("Y")));
						$query = "SELECT count(*) FROM tbltickets WHERE date>'" . $filterdate . "' AND (email='" . mysql_real_escape_string($email) . "'";

						if ($userid) {
							$query .= " OR userid=" . (int)$userid;
						}

						$query .= ")";
						$result = full_query($query);
						$data = mysql_fetch_array($result);
						$numtickets = $data[0];

						if (10 < $numtickets) {
							$mailstatus = "Exceeded Limit of 10 Tickets within 15 Minutes";
						}
						else {
							run_hook("TransliterateTicketText", array("subject" => $subject, "message" => $message));

							if ($tid) {
								AddReply($tid, $userid, $contactid, htmlspecialchars_array($message), "", $attachment, htmlspecialchars_array($from));
								$mailstatus = "Ticket Reply Imported Successfully";
							}
							else {
								if ($pipenonregisteredreplyonly && !$userid) {
									$mailstatus = "Blocked Ticket Opening from Unregistered User";
								}
								else {
									if ($deptpiperepliesonly) {
										$mailstatus = "Only Replies Allowed by Email";
									}
									else {
										openNewTicket(htmlspecialchars_array($userid), htmlspecialchars_array($contactid), htmlspecialchars_array($deptid), htmlspecialchars_array($subject), htmlspecialchars_array($message), "Medium", $attachment, htmlspecialchars_array($from), "", htmlspecialchars_array($ccemail));
										$mailstatus = "Ticket Imported Successfully";
									}
								}
							}
						}
					}
				}
			}
		}
	}
	else {
		if ($attachment) {
			global $attachments_dir;

			$attachment = explode("|", $attachment);
			foreach ($attachment as $file) {
				unlink($attachments_dir . $file);
			}
		}
	}


	if ($mailstatus == "") {
		$mailstatus = "Ticket Import Failed";
	}

	$table = "tblticketmaillog";
	$array = "";
	$array = array("date" => "now()", "to" => $to, "name" => $name, "email" => $email, "subject" => $subject, "message" => $message, "status" => $mailstatus);
	insert_query($table, htmlspecialchars_array($array));
}

function uploadTicketAttachments($admin = false) {
	global $attachments_dir;

	$attachments = "";

	if ($_FILES['attachments']) {
		foreach ($_FILES['attachments']['name'] as $num => $filename) {
			$filename = trim($filename);

			if ($filename) {
				$filename = preg_replace("/[^a-zA-Z0-9-_. ]/", "", $filename);
				$validextension = checkTicketAttachmentExtension($filename);

				if ($validextension || $admin) {
					mt_srand(time());
					$rand = mt_rand(100000, 999999);
					$newfilename = $rand . "_" . $filename;

					while (file_exists($attachments_dir . $newfilename)) {
						mt_srand(time());
						$rand = mt_rand(100000, 999999);
						$newfilename = $rand . "_" . $filename;
					}

					move_uploaded_file($_FILES['attachments']['tmp_name'][$num], $attachments_dir . $newfilename);
					$attachments .= $newfilename . "|";
					continue;
				}

				continue;
			}
		}

		$attachments = substr($attachments, 0, 0 - 1);
	}

	return $attachments;
}

function checkTicketAttachmentExtension($file_name) {
	global $CONFIG;

	$ext_array = $CONFIG['TicketAllowedFileTypes'];
	$ext_array = explode(",", $ext_array);
	$tmp = explode(".", $file_name);
	$extension = strtolower(end($tmp));
	$extension = "." . $extension;
	$bannedparts = array(".php", ".cgi", ".pl", "htaccess");
	foreach ($bannedparts as $bannedpart) {
		$pos = strpos($file_name, $bannedpart);

		if ($pos !== false) {
			return false;
		}
	}

	foreach ($ext_array as $value) {

		if (trim($value) == $extension) {
			return true;
		}
	}

}

function pipeDecodeString($string) {

	if (($pos = strpos($string, "=?")) === false) {
		return $string;
	}

	$newresult = NULL;

	while (!($pos === false)) {
		$newresult .= substr($string, 0, $pos);
		$string = substr($string, $pos + 2, strlen($string));
		$intpos = strpos($string, "?");
		$charset = substr($string, 0, $intpos);
		$enctype = strtolower(substr($string, $intpos + 1, 1));
		$string = substr($string, $intpos + 3, strlen($string));
		$endpos = strpos($string, "?=");
		$mystring = substr($string, 0, $endpos);
		$string = substr($string, $endpos + 2, strlen($string));

		if ($enctype == "q") {
			$mystring = quoted_printable_decode(str_replace("_", " ", $mystring));
		}
		else {
			if ($enctype == "b") {
				$mystring = base64_decode($mystring);
			}
		}

		$newresult .= $mystring;
		$pos = strpos($string, "=?");
	}

	$result = $newresult . $string;
	return $result;
}

function closeInactiveTickets() {
	global $whmcs;
	global $cron;

	if (0 < $whmcs->get_config("CloseInactiveTickets")) {
		$departmentresponders = array();
		$result = select_query("tblticketdepartments", "id,noautoresponder", "");

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$noautoresponder = $data['noautoresponder'];
			$departmentresponders[$id] = $noautoresponder;
		}

		$closetitles = array();
		$result = select_query("tblticketstatuses", "title", array("autoclose" => "1"));

		while ($data = mysql_fetch_array($result)) {
			$closetitles[] = $data[0];
		}

		$ticketclosedate = date("Y-m-d H:i:s", mktime(date("H") - $whmcs->get_config("CloseInactiveTickets"), date("i"), date("s"), date("m"), date("d"), date("Y")));
		$i = 0;
		$query = "SELECT id,did,title FROM tbltickets WHERE status IN (" . db_build_in_array($closetitles) . (") AND lastreply<='" . $ticketclosedate . "'");
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$did = $data['did'];
			$subject = $data['title'];
			closeTicket($id);

			if (!$departmentresponders[$did] && !$whmcs->get_config("TicketFeedback")) {
				sendMessage("Support Ticket Auto Close Notification", $id);
			}


			if (is_object($cron)) {
				$cron->logActivityDebug("Closed Ticket '" . $subject . "'");
			}

			++$i;
		}


		if (is_object($cron)) {
			$cron->logActivity("Processed " . $i . " Ticket Closures", true);
			$cron->emailLog($i . " Tickets Closed for Inactivity");
		}
	}

}

function deleteTicket($ticketid, $replyid = "") {
	global $attachments_dir;

	$where = array("tid" => $ticketid);

	if ($replyid) {
		$where = array("id" => $replyid);
	}

	$result = select_query("tblticketreplies", "", $where);

	while ($data = mysql_fetch_array($result)) {
		$attachment = $data['attachment'];

		if ($attachment) {
			$attachment = explode("|", $attachment);
			foreach ($attachment as $file) {
				unlink($attachments_dir . $file);
			}
		}
	}


	if (!$replyid) {
		$result = select_query("tbltickets", "", array("id" => $ticketid));
		$data = mysql_fetch_array($result);
		$attachment = $data['attachment'];

		if ($attachment) {
			$attachment = explode("|", $attachment);
			foreach ($attachment as $file) {
				unlink($attachments_dir . $file);
			}
		}

		delete_query("tblticketreplies", array("tid" => $ticketid));
		delete_query("tbltickets", array("id" => $ticketid));
		logActivity("Deleted Ticket - Ticket ID: " . $ticketid);
		return null;
	}

	delete_query("tblticketreplies", array("id" => $replyid));
	addTicketLog($ticketid, "Deleted Ticket Reply (ID: " . $replyid . ")");
	logActivity("Deleted Ticket Reply - ID: " . $replyid);
}

function genTicketMask(&$id = "") {
	global $CONFIG;

	$lowercase = "abcdefghijklmnopqrstuvwxyz";
	$uppercase = "ABCDEFGHIJKLMNOPQRSTUVYWXYZ";
	$ticketmaskstr = "";
	$ticketmask = trim($CONFIG['TicketMask']);

	if (!$ticketmask) {
		$ticketmask = "%n%n%n%n%n%n";
	}

	$masklen = strlen($ticketmask);
	$i = 0;

	while ($i < $masklen) {
		$maskval = $ticketmask[$i];

		if ($maskval == "%") {
			++$i;
			$maskval .= $ticketmask[$i];

			if ($maskval == "%A") {
				$ticketmaskstr .= $uppercase[rand(0, 25)];
			}
			else {
				if ($maskval == "%a") {
					$ticketmaskstr .= $lowercase[rand(0, 25)];
				}
				else {
					if ($maskval == "%n") {
						$ticketmaskstr .= (strlen($ticketmaskstr) ? rand(0, 9) : rand(1, 9));
					}
					else {
						if ($maskval == "%y") {
							$ticketmaskstr .= date("Y");
						}
						else {
							if ($maskval == "%m") {
								$ticketmaskstr .= date("m");
							}
							else {
								if ($maskval == "%d") {
									$ticketmaskstr .= date("d");
								}
								else {
									if ($maskval == "%i") {
										$ticketmaskstr .= $id;
									}
								}
							}
						}
					}
				}
			}
		}
		else {
			$ticketmaskstr .= $maskval;
		}

		++$i;
	}

	$tid = get_query_val("tbltickets", "id", array("tid" => $ticketmaskstr));

	if ($tid) {
		$ticketmaskstr = genTicketMask($id);
	}

	return $ticketmaskstr;
}

function ticketMessageFormat($message) {
	$message = strip_tags($message);
	$message = preg_replace("/\[div=\"(.*?)\"\]/", "<div class=\"\">", $message);
	$replacetags = array("b" => "strong", "i" => "em", "u" => "ul", "div" => "div");
	foreach ($replacetags as $k => $v) {
		$message = str_replace("[" . $k . "]", "<" . $k . ">", $message);
		$message = str_replace("[/" . $k . "]", "</" . $k . ">", $message);
	}

	$message = nl2br($message);
	$message = ticketAutoHyperlinks($message);
	return $message;
}

function getKBAutoSuggestions($text) {
	$kbarticles = array();
	$hookret = run_hook("SubmitTicketAnswerSuggestions", array("text" => $text));

	if (count($hookret)) {
		foreach ($hookret as $hookdat) {
			foreach ($hookdat as $arrdata) {
				$kbarticles[] = $arrdata;
			}
		}
	}
	else {
		$ignorewords = array("able", "about", "above", "according", "accordingly", "across", "actually", "after", "afterwards", "again", "against", "ain't", "allow", "allows", "almost", "alone", "along", "already", "also", "although", "always", "among", "amongst", "another", "anybody", "anyhow", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate", "aren't", "around", "aside", "asking", "associated", "available", "away", "awfully", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "behind", "being", "believe", "below", "beside", "besides", "best", "better", "between", "beyond", "both", "brief", "c'mon", "came", "can't", "cannot", "cant", "cause", "causes", "certain", "certainly", "changes", "clearly", "come", "comes", "concerning", "consequently", "consider", "considering", "contain", "containing", "contains", "corresponding", "could", "couldn't", "course", "currently", "definitely", "described", "despite", "didn't", "different", "does", "doesn't", "doing", "don't", "done", "down", "downwards", "during", "each", "eight", "either", "else", "elsewhere", "enough", "entirely", "especially", "even", "ever", "every", "everybody", "everyone", "everything", "everywhere", "exactly", "example", "except", "fifth", "first", "five", "followed", "following", "follows", "former", "formerly", "forth", "four", "from", "further", "furthermore", "gets", "getting", "given", "gives", "goes", "going", "gone", "gotten", "greetings", "hadn't", "happens", "hardly", "hasn't", "have", "haven't", "having", "he's", "hello", "help", "hence", "here", "here's", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "himself", "hither", "hopefully", "howbeit", "however", "i'll", "i've", "ignored", "immediate", "inasmuch", "indeed", "indicate", "indicated", "indicates", "inner", "insofar", "instead", "into", "inward", "isn't", "it'd", "it'll", "it's", "itself", "just", "keep", "keeps", "kept", "know", "known", "knows", "last", "lately", "later", "latter", "latterly", "least", "less", "lest", "let's", "like", "liked", "likely", "little", "look", "looking", "looks", "mainly", "many", "maybe", "mean", "meanwhile", "merely", "might", "more", "moreover", "most", "mostly", "much", "must", "myself", "name", "namely", "near", "nearly", "necessary", "need", "needs", "neither", "never", "nevertheless", "next", "nine", "nobody", "none", "noone", "normally", "nothing", "novel", "nowhere", "obviously", "often", "okay", "once", "ones", "only", "onto", "other", "others", "otherwise", "ought", "ours", "ourselves", "outside", "over", "overall", "particular", "particularly", "perhaps", "placed", "please", "plus", "possible", "presumably", "probably", "provides", "quite", "rather", "really", "reasonably", "regarding", "regardless", "regards", "relatively", "respectively", "right", "said", "same", "saying", "says", "second", "secondly", "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sensible", "sent", "serious", "seriously", "seven", "several", "shall", "should", "shouldn't", "since", "some", "somebody", "somehow", "someone", "something", "sometime", "sometimes", "somewhat", "somewhere", "soon", "sorry", "specified", "specify", "specifying", "still", "such", "sure", "take", "taken", "tell", "tends", "than", "thank", "thanks", "thanx", "that", "that's", "thats", "their", "theirs", "them", "themselves", "then", "thence", "there", "there's", "thereafter", "thereby", "therefore", "therein", "theres", "thereupon", "these", "they", "they'd", "they'll", "they're", "they've", "think", "third", "this", "thorough", "thoroughly", "those", "though", "three", "through", "throughout", "thru", "thus", "together", "took", "toward", "towards", "tried", "tries", "truly", "trying", "twice", "under", "unfortunately", "unless", "unlikely", "until", "unto", "upon", "used", "useful", "uses", "using", "usually", "value", "various", "very", "want", "wants", "wasn't", "we'd", "we'll", "we're", "we've", "welcome", "well", "went", "were", "weren't", "what", "what's", "whatever", "when", "whence", "whenever", "where", "where's", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who's", "whoever", "whole", "whom", "whose", "will", "willing", "wish", "with", "within", "without", "won't", "wonder", "would", "wouldn't", "you'd", "you'll", "you're", "you've", "your", "yours", "yourself", "yourselves", "zero");
		$text = str_replace("\r\n", " ", $text);

		$textparts = explode(" ", strtolower($text));
		$validword = 0;
		foreach ($textparts as $k => $v) {

			if ((in_array($v, $ignorewords) || strlen($textparts[$k]) <= 3) || 100 <= $validword) {
				unset($textparts[$k]);
				continue;
			}

			++$validword;
		}

		$kbarticles = getKBAutoSuggestionsQuery("title", $textparts, "5");

		if (count($kbarticles) < 5) {
			$numleft = 5 - count($kbarticles);
			$kbarticles = array_merge($kbarticles, getKBAutoSuggestionsQuery("article", $textparts, $numleft, $kbarticles));
		}
	}

	return $kbarticles;
}

function getKBAutoSuggestionsQuery($field, $textparts, $limit, $existingkbarticles = "") {
	$kbarticles = array();
	$where = "";
	foreach ($textparts as $textpart) {
		$where .= "" . $field . " LIKE '%" . db_escape_string($textpart) . "%' OR ";
	}

	$where = (!$where ? "id!=''" : substr($where, 0, 0 - 4));

	if (is_array($existingkbarticles)) {
		$existingkbids = array();
		foreach ($existingkbarticles as $v) {
			$existingkbids[] = (int)$v['id'];
		}

		$where = "(" . $where . ")";

		if (0 < count($existingkbids)) {
			$where .= " AND id NOT IN (" . implode(",", $existingkbids) . ")";
		}
	}

	$result = full_query("SELECT id,parentid FROM tblknowledgebase WHERE " . $where . " ORDER BY useful DESC LIMIT 0," . (int)$limit);

	while ($data = mysql_fetch_array($result)) {
		$articleid = $data['id'];
		$parentid = $data['parentid'];

		if ($parentid) {
			$articleid = $parentid;
		}

		$result2 = full_query("SELECT tblknowledgebaselinks.categoryid FROM tblknowledgebase INNER JOIN tblknowledgebaselinks ON tblknowledgebase.id=tblknowledgebaselinks.articleid INNER JOIN tblknowledgebasecats ON tblknowledgebasecats.id=tblknowledgebaselinks.categoryid WHERE (tblknowledgebase.id=" . (int)$articleid . " OR tblknowledgebase.parentid=" . (int)$articleid . ") AND tblknowledgebasecats.hidden=''");
		$data = mysql_fetch_array($result2);
		$categoryid = $data['categoryid'];

		if ($categoryid) {
			$result2 = full_query("SELECT * FROM tblknowledgebase WHERE (id=" . (int)$articleid . " OR parentid=" . (int)$articleid . ") AND (language='" . db_escape_string($_SESSION['Language']) . "' OR language='') ORDER BY language DESC");
			$data = mysql_fetch_array($result2);
			$title = $data['title'];
			$article = $data['article'];
			$views = $data['views'];
			$kbarticles[] = array("id" => $articleid, "category" => $categoryid, "title" => $title, "article" => ticketsummary($article), "text" => $article);
		}
	}

	return $kbarticles;
}

function ticketsummary($text, $length = 100) {
	$tail = "...";
	$text = strip_tags($text);
	$txtl = strlen($text);

	if ($length < $txtl) {
		$i = 0;

		while ($text[$length - $i] != " ") {
			if ($i == $length) {
				return substr($text, 0, $length) . $tail;
			}

			++$i;
		}

		$text = substr($text, 0, $length - $i + 1) . $tail;
	}

	return $text;
}

function getTicketContacts($userid) {
	$contacts = "";
	$result = select_query("tblcontacts", "", array("userid" => $userid, "email" => array("sqltype" => "NEQ", "value" => "")));

	while ($data = mysql_fetch_array($result)) {
		$contacts .= "<option value=\"" . $data['id'] . "\"";

		if (isset($_POST['contactid']) && $_POST['contactid'] == $data['id']) {
			$contacts .= " selected";
		}

		$contacts .= ">" . $data['firstname'] . " " . $data['lastname'] . " - " . $data['email'] . "</option>";
	}


	if ($contacts) {
		return "<select name=\"contactid\"><option value=\"0\">None</option>" . $contacts . "</select>";
	}

}

function getTicketAttachmentsInfo($ticketid, $replyid, $attachment) {
	$attachments = array();

	if ($attachment) {
		$attachment = explode("|", $attachment);
		foreach ($attachment as $num => $file) {
			$file = substr($file, 7);

			if ($replyid) {
				$attachments[] = array("filename" => $file, "dllink" => "dl.php?type=ar&id=" . $replyid . "&i=" . $num, "deletelink" => "" . $PHP_SELF . "?action=viewticket&id=" . $ticketid . "&removeattachment=true&type=r&idsd=" . $replyid . "&filecount=" . $num);
				continue;
			}

			$attachments[] = array("filename" => $file, "dllink" => "dl.php?type=a&id=" . $ticketid . "&i=" . $num, "deletelink" => "" . $PHP_SELF . "?action=viewticket&id=" . $ticketid . "&removeattachment=true&idsd=" . $ticketid . "&filecount=" . $num);
		}
	}

	return $attachments;
}

function getAdminDepartmentAssignments() {
	static $DepartmentIDs = array();

	if (count($DepartmentIDs)) {
		return $DepartmentIDs;
	}

	$result = select_query("tbladmins", "supportdepts", array("id" => $_SESSION['adminid']));
	$data = mysql_fetch_array($result);
	$supportdepts = $data['supportdepts'];
	$supportdepts = explode(",", $supportdepts);
	foreach ($supportdepts as $k => $v) {

		if (!$v) {
			unset($supportdepts[$k]);
			continue;
		}
	}

	$DepartmentIDs = $supportdepts;
	return $supportdepts;
}

function getDepartments() {
	$departmentsarray = array();
	$result = select_query("tblticketdepartments", "id,name", "");
	$departmentsarray = array();

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$name = $data['name'];
		$departmentsarray[$id] = $name;
	}

	return $departmentsarray;
}

function buildAdminTicketListArray($result) {
	global $departmentsarray;
	global $tabledata;
	global $aInt;
	global $tickets;


	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$ticketnumber = $data['tid'];
		$did = $data['did'];
		$puserid = $data['userid'];
		$name = $data['name'];
		$email = $data['email'];
		$date = $data['date'];
		$title = $data['title'];
		$message = $data['message'];
		$tstatus = $data['status'];
		$priority = $data['urgency'];
		$rawlastactivity = $data['lastreply'];
		$flag = $data['flag'];
		$firstname = $data['firstname'];
		$lastname = $data['lastname'];
		$companyname = $data['companyname'];
		$groupid = $data['groupid'];
		$adminread = $data['adminunread'];
		$adminread = explode(",", $adminread);
		$tickets->addTagCloudID($id);

		if (!in_array($_SESSION['adminid'], $adminread)) {
			$unread = 1;
		}
		else {
			$unread = 0;
		}

		$alttitle = "";
		$title = trim($title);

		if (!$title) {
			$title = "&nbsp;- " . $aInt->lang("emails", "nosubject") . " -&nbsp;";
		}


		if (80 < strlen($title)) {
			$alttitle = $title;
			$title = substr($title, 0, 80) . "...";
		}


		if ($alttitle) {
			$alttitle .= "\r\n";
		}

		$alttitle .= trim(ticketsummary($message, 250));
		$flaggedto = "";

		if ($flag == $_SESSION['adminid']) {
			$showflag = "user";
		}
		else {
			if ($flag == 0) {
				$showflag = "none";
			}
			else {
				$showflag = "other";
				$flaggedto = getAdminName($flag);
			}
		}

		$department = $departmentsarray[$did];

		if ($flaggedto) {
			$department .= " (" . $flaggedto . ")";
		}

		$date = fromMySQLDate($date, "time");
		$lastactivity = fromMySQLDate($rawlastactivity, "time");
		$tstatus = getStatusColour($tstatus);
		$lastreply = getShortLastReplyTime($rawlastactivity);
		$title = "#" . $ticketnumber . " - " . $title;

		if ($unread) {
			$title = "<strong>" . $title . "</strong>";
		}

		$clientinfo = ($puserid != "0" ? $aInt->outputClientLink($puserid, $firstname, $lastname, $companyname, $groupid) : $name);
		$ticketlink = ("<a href=\"?action=viewticket&id=" . $id . "\"") . ($alttitle ? " title=\"" . $alttitle . "\"" : "") . "" . $ainject . ">";
		$tabledata[] = array("<input type=\"checkbox\" name=\"selectedtickets[]\" value=\"" . $id . "\" class=\"checkall\">", "<img src=\"images/" . strtolower($priority) . ("priority.gif\" width=\"16\" height=\"16\" alt=\"" . $priority . "\" class=\"absmiddle\" />"), $department, "<div style=\"text-align:left;\">" . $ticketlink . $title . "</a></div>", $clientinfo, $tstatus, $lastreply);
	}

}

function validateAdminTicketAccess($ticketid) {
	$data = get_query_vals("tbltickets", "id,did,flag", array("id" => $ticketid));
	$id = $data['id'];
	$deptid = $data['did'];
	$flag = $data['flag'];

	if (!$id) {
		return "invalidid";
	}


	if (!in_array($deptid, getAdminDepartmentAssignments()) && !checkPermission("Access All Tickets Directly", true)) {
		return "deptblocked";
	}


	if ((($flag && $flag != $_SESSION['adminid']) && !checkPermission("View Flagged Tickets", true)) && !checkPermission("Access All Tickets Directly", true)) {
		return "flagged";
	}

	return false;
}

function genPredefinedRepliesList($cat, $predefq = "") {
	global $aInt;

	$catscontent = "";
	$repliescontent = "";

	if (!$predefq) {
		if (!$cat) {
			$cat = 0;
		}

		$result = select_query("tblticketpredefinedcats", "", array("parentid" => $cat), "name", "ASC");
		$i = 0;

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$name = $data['name'];
			$catscontent .= "<td width=\"33%\"><img src=\"../images/folder.gif\" align=\"absmiddle\"> <a href=\"#\" onclick=\"selectpredefcat('" . $id . "');return false\">" . $name . "</a></td>";
			++$i;

			if ($i % 3 == 0) {
				$catscontent .= "</tr><tr>";
				$i = 0;
			}
		}
	}

	$where = ($predefq ? array("name" => array("sqltype" => "LIKE", "value" => $predefq)) : array("catid" => $cat));
	$result = select_query("tblticketpredefinedreplies", "", $where, "name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$name = $data['name'];
		$reply = strip_tags($data['reply']);
		$shortreply = substr($reply, 0, 100) . "...";
		$shortreply = str_replace(chr(10), " ", $shortreply);
		$shortreply = str_replace(chr(13), " ", $shortreply);
		$repliescontent .= " &nbsp; <img src=\"../images/article.gif\" align=\"absmiddle\"> <a href=\"#\" onclick=\"selectpredefreply('" . $id . "');return false\">" . $name . "</a> - " . $shortreply . "<br>";
	}

	$content = "";

	if ($catscontent) {
		$content .= "<strong>" . $aInt->lang("support", "categories") . "</strong><br><br><table width=\"95%\"><tr>" . $catscontent . "</tr></table><br>";
	}


	if ($repliescontent) {
		if ($predefq) {
			$content .= "<strong>" . $aInt->lang("global", "searchresults") . "</strong><br><br>" . $repliescontent;
		}
		else {
			$content .= "<strong>" . $aInt->lang("support", "replies") . "</strong><br><br>" . $repliescontent;
		}
	}


	if (!$content) {
		if ($predefq) {
			$content .= "<strong>" . $aInt->lang("global", "searchresults") . "</strong><br><br>" . $aInt->lang("global", "nomatchesfound") . "<br>";
		}
		else {
			$content .= "<span style=\"line-height:22px;\">" . $aInt->lang("support", "catempty") . "</span><br>";
		}
	}

	$result = select_query("tblticketpredefinedcats", "parentid", array("id" => $cat));
	$data = mysql_fetch_array($result);

	if (0 < $cat || $predefq) {
		$content .= "<br /><a href=\"#\" onclick=\"selectpredefcat('0');return false\"><img src=\"images/icons/navrotate.png\" align=\"top\" /> " . $aInt->lang("support", "toplevel") . "</a>";
	}


	if (0 < $cat) {
		$content .= " &nbsp;<a href=\"#\" onclick=\"selectpredefcat('" . $data[0] . "');return false\"><img src=\"images/icons/navback.png\" align=\"top\" /> " . $aInt->lang("support", "uponelevel") . "</a>";
	}

	return $content;
}

function closeTicket($id) {
	global $whmcs;

	$status = get_query_val("tbltickets", "status", array("id" => $id));

	if ($status == "Closed") {
		return false;
	}


	if (defined("CLIENTAREA")) {
		addTicketLog($id, "Closed by Client");
	}
	else {
		if (defined("ADMINAREA")) {
			addTicketLog($id, "Status changed to Closed");
		}
		else {
			addTicketLog($id, "Ticket Auto Closed For Inactivity");
		}
	}

	update_query("tbltickets", array("status" => "Closed"), array("id" => $id));

	if ($whmcs->get_config("TicketFeedback")) {
		$feedbackcheck = get_query_val("tblticketfeedback", "id", array("ticketid" => $id));

		if (!$feedbackcheck) {
			sendMessage("Support Ticket Feedback Request", $id);
		}
	}

	run_hook("TicketClose", array("ticketid" => $id));
	return true;
}

?>