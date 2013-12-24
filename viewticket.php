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

define("CLIENTAREA", true);
require "init.php";
require "includes/ticketfunctions.php";
require "includes/clientfunctions.php";
require "includes/customfieldfunctions.php";
$tid = $whmcs->get_req_var("tid");
$c = preg_replace("/[^A-Za-z0-9]/", "", $c);
$clientname = $clientemail = "";
$pagetitle = $_LANG['supportticketsviewticket'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"clientarea.php\">" . $_LANG['clientareatitle'] . "</a> > <a href=\"supporttickets.php\">" . $_LANG['supportticketspagetitle'] . ("</a> > <a href=\"viewticket.php?tid=" . $tid . "&amp;c=" . $c . "\">") . $_LANG['supportticketsviewticket'] . "</a>";
$pageicon = "images/supporttickets_big.gif";
$templatefile = "viewticket";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);
checkContactPermission("tickets");
$usingsupportmodule = false;

if ($CONFIG['SupportModule']) {
	if (!isValidforPath($CONFIG['SupportModule'])) {
		exit("Invalid Support Module");
	}

	$supportmodulepath = "modules/support/" . $CONFIG['SupportModule'] . "/viewticket.php";

	if (file_exists($supportmodulepath)) {
		$usingsupportmodule = true;
		$templatefile = "";
		require $supportmodulepath;
		outputClientArea($templatefile);
		exit();
	}
}

$result = select_query("tbltickets", "", array("tid" => $tid, "c" => $c));
$data = mysql_fetch_array($result);
$id = $data['id'];
$tid = $data['tid'];
$c = $data['c'];
$userid = $data['userid'];

if (!$id) {
	$smarty->assign("error", true);
}
else {
	if (($CONFIG['RequireLoginforClientTickets'] && $userid) && (!isset($_SESSION['uid']) || $userid != $_SESSION['uid'])) {
		$goto = "viewticket";
		require "login.php";
	}


	if ($whmcs->get_req_var("feedback") && $whmcs->get_config("TicketFeedback")) {
		$templatefile = "ticketfeedback";
		$smartyvalues['id'] = $id;
		$smartyvalues['tid'] = $tid;
		$smartyvalues['c'] = $c;
		$status = $data['status'];
		$closedcheck = get_query_val("tblticketstatuses", "id", array("title" => $status, "showactive" => "0"));
		$smartyvalues['stillopen'] = (!$closedcheck ? true : false);
		$feedbackcheck = get_query_val("tblticketfeedback", "id", array("ticketid" => $id));
		$smartyvalues['feedbackdone'] = $feedbackcheck;
		$date = $data['date'];
		$smartyvalues['opened'] = date("l, jS F Y H:ia", strtotime($date));
		$lastreply = get_query_val("tblticketreplies", "date", array("tid" => $id), "id", "DESC");

		if (!$lastreply) {
			$lastreply = $date;
		}

		$smartyvalues['lastreply'] = date("l, jS F Y H:ia", strtotime($lastreply));
		$duration = getLastReplyTime($date, $lastreply);
		$smartyvalues['duration'] = $duration;
		$ratings = array();
		$i = 1;

		while ($i <= 10) {
			$ratings[] = $i;
			++$i;
		}

		$smartyvalues['ratings'] = $ratings;
		$staffinvolved = array();
		$result = select_query("tblticketreplies", "DISTINCT admin", array("tid" => $id));

		while ($data = mysql_fetch_array($result)) {
			if (trim($data[0])) {
				$staffinvolved[get_query_val("tbladmins", "id", "CONCAT(firstname,' ',lastname)='" . db_escape_string($data[0]) . "'")] = $data[0];
			}
		}

		$smartyvalues['staffinvolved'] = $staffinvolved;
		$smartyvalues['rate'] = $whmcs->get_req_var("rate");
		$smartyvalues['comments'] = $whmcs->get_req_var("comments");
		$errormessage = "";

		if ($whmcs->get_req_var("validate")) {
			check_token();
			foreach ($staffinvolved as $staffid => $staffname) {

				if (!$whmcs->get_req_var("rate", $staffid)) {
					$errormessage .= "<li>Please supply at least a rating for " . $staffname . " (comments are optional)</li>";
					continue;
				}
			}

			$smartyvalues['errormessage'] = $errormessage;

			if (!$errormessage) {
				foreach ($staffinvolved as $staffid => $staffname) {
					insert_query("tblticketfeedback", array("ticketid" => $id, "adminid" => $staffid, "rating" => $whmcs->get_req_var("rate", $staffid), "comments" => $whmcs->get_req_var("comments", $staffid), "datetime" => "now()", "ip" => $whmcs->get_user_ip()));
				}


				if (trim($whmcs->get_req_var("comments", "generic"))) {
					insert_query("tblticketfeedback", array("ticketid" => $id, "adminid" => "0", "rating" => "0", "comments" => $whmcs->get_req_var("comments", "generic"), "datetime" => "now()", "ip" => $whmcs->get_user_ip()));
				}

				$smartyvalues['success'] = true;
			}
		}

		outputClientArea($templatefile);
		exit();
	}


	if ($closeticket) {
		closeTicket($id);
		redir("tid=" . $tid . "&c=" . $c);
	}


	if ($rating) {
		$rating = explode("_", $rating);
		$replyid = $rating[0];
		$ratingscore = $rating[1];
		$replyid = substr($replyid, 4);
		update_query("tblticketreplies", array("rating" => $ratingscore), array("id" => $replyid));
		redir("tid=" . $tid . "&c=" . $c);
	}

	$errormessage = "";

	if ($postreply) {
		check_token();

		if (!$_SESSION['uid']) {
			if (!$replyname) {
				$errormessage .= "<li>" . $_LANG['supportticketserrornoname'];
			}


			if (!$replyemail) {
				$errormessage .= "<li>" . $_LANG['supportticketserrornoemail'];
			}
			else {
				if (!preg_match('/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/', $replyemail)) {
					$errormessage .= "<li>" . $_LANG['clientareaerroremailinvalid'];
				}
			}
		}


		if (!$replymessage) {
			$errormessage .= "<li>" . $_LANG['supportticketserrornomessage'];
		}


		if ($_FILES['attachments']) {
			foreach ($_FILES['attachments']['name'] as $num => $filename) {
				$filename = trim($filename);

				if ($filename) {
					$filenameparts = explode(".", $filename);
					$extension = end($filenameparts);
					$filename = implode(array_slice($filenameparts, 0, 0 - 1));
					$filename = preg_replace("/[^a-zA-Z0-9-_ ]/", "", $filename);
					$filename .= "." . $extension;
					$validextension = checkTicketAttachmentExtension($filename);

					if (!$validextension) {
						$errormessage .= "<li>" . $_LANG['supportticketsfilenotallowed'];
						continue;
					}

					continue;
				}
			}
		}


		if (!$errormessage) {
			$attachments = uploadTicketAttachments();
			$from = array("name" => $replyname, "email" => $replyemail);
			AddReply($id, $_SESSION['uid'], $_SESSION['cid'], $replymessage, "", $attachments, $from);
			redir("tid=" . $tid . "&c=" . $c);
		}
	}

	$id = $data['id'];
	$userid = $data['userid'];
	$contactid = $data['contactid'];
	$deptid = $data['did'];
	$date = $data['date'];
	$subject = $data['title'];
	$message = $data['message'];
	$status = $data['status'];
	$attachment = $data['attachment'];
	$urgency = $data['urgency'];
	$name = $data['name'];
	$email = $data['email'];
	$lastreply = $data['lastreply'];
	$admin = $data['admin'];
	$date = fromMySQLDate($date, 1, 1);
	$lastreply = fromMySQLDate($lastreply, 1, 1);
	$message = ticketMessageFormat($message);

	if ($status != "Closed") {
		$showclosebutton = true;
	}

	$status = getStatusColour($status);
	$urgency = $_LANG["supportticketsticketurgency" . strtolower($urgency)];
	$customfields = getCustomFields("support", $deptid, $id, "", "", "", true);
	ClientRead($id);

	if ($admin) {
		$user = "<strong>" . $admin . "</strong><br />" . $_LANG['supportticketsstaff'];
	}
	else {
		if (0 < $userid) {
			$clientsdata = get_query_vals("tblclients", "firstname,lastname,email", array("id" => $userid));
			$clientname = $clientsdata['firstname'] . " " . $clientsdata['lastname'];
			$clientemail = $clientsdata['email'];
			$user = "<strong>" . $clientname . "</strong><br />" . $_LANG['supportticketsclient'];

			if (0 < $contactid) {
				$contactdata = get_query_vals("tblcontacts", "firstname,lastname,email", array("id" => $contactid, "userid" => $userid));
				$clientname = $contactdata['firstname'] . " " . $contactdata['lastname'];
				$clientemail = $contactdata['email'];
				$user = "<strong>" . $clientname . "</strong><br />" . $_LANG['supportticketscontact'];
			}
		}
		else {
			$clientname = $name;
			$clientemail = $email;
			$user = "<strong>" . $clientname . "</strong><br />" . $clientemail;
		}
	}

	$department = getDepartmentName($deptid);
	$attachments = array();

	if ($attachment) {
		$attachment = explode("|", $attachment);
		foreach ($attachment as $filename) {
			$filename = substr($filename, 7);
			$attachments[] = $filename;
		}
	}

	$smarty->assign("id", $id);
	$smarty->assign("c", $c);
	$smarty->assign("tid", $tid);
	$smarty->assign("date", $date);
	$smarty->assign("department", $department);
	$smarty->assign("subject", $subject);
	$smarty->assign("message", $message);
	$smarty->assign("status", $status);
	$smarty->assign("urgency", $urgency);
	$smarty->assign("attachments", $attachments);
	$smarty->assign("user", $user);
	$smarty->assign("contact", $contact);
	$smarty->assign("lastreply", $lastreply);
	$smarty->assign("showclosebutton", $showclosebutton);
	$smarty->assign("customfields", $customfields);
	$smarty->assign("ratingenabled", $CONFIG['TicketRatingEnabled']);
	$replies = $ascreplies = array();
	$ascreplies[] = array("id" => "", "userid" => $userid, "contactid" => $contactid, "name" => ($admin ? $admin : $clientname), "email" => ($admin ? "" : $clientemail), "admin" => ($admin ? true : false), "user" => $user, "admin" => $admin, "date" => $date, "message" => $message, "attachments" => $attachments, "rating" => $rating);
	$result = select_query("tblticketreplies", "", array("tid" => $id), "date", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$ids = $data['id'];
		$userid = $data['userid'];
		$contactid = $data['contactid'];
		$admin = $data['admin'];
		$name = $data['name'];
		$email = $data['email'];
		$date = $data['date'];
		$message = $data['message'];
		$attachment = $data['attachment'];
		$rating = $data['rating'];
		$date = fromMySQLDate($date, 1, 1);
		$message = ticketMessageFormat($message);

		if ($admin) {
			$user = "<strong>" . $admin . "</strong><br />" . $_LANG['supportticketsstaff'];
		}
		else {
			if (0 < $userid) {
				$clientsdata = get_query_vals("tblclients", "firstname,lastname,email", array("id" => $userid));
				$clientname = $clientsdata['firstname'] . " " . $clientsdata['lastname'];
				$clientemail = $clientsdata['email'];
				$user = "<strong>" . $clientname . "</strong><br />" . $_LANG['supportticketsclient'];

				if (0 < $contactid) {
					$contactdata = get_query_vals("tblcontacts", "firstname,lastname,email", array("id" => $contactid, "userid" => $userid));
					$clientname = $contactdata['firstname'] . " " . $contactdata['lastname'];
					$clientemail = $contactdata['email'];
					$user = "<strong>" . $clientname . "</strong><br />" . $_LANG['supportticketscontact'];
				}
			}
			else {
				$clientname = $name;
				$clientemail = $email;
				$user = "<strong>" . $clientname . "</strong><br />" . $clientemail;
			}
		}

		$attachments = array();

		if ($attachment) {
			$attachment = explode("|", $attachment);
			foreach ($attachment as $filename) {
				$filename = substr($filename, 7);
				$attachments[] = $filename;
			}
		}

		$replies[] = $ascreplies[] = array("id" => $ids, "userid" => $userid, "contactid" => $contactid, "name" => ($admin ? $admin : $clientname), "email" => ($admin ? "" : $clientemail), "admin" => ($admin ? true : false), "user" => $user, "date" => $date, "message" => $message, "attachments" => $attachments, "rating" => $rating);
	}

	$smarty->assign("replies", $replies);
	$smarty->assign("ascreplies", $ascreplies);
	krsort($ascreplies);
	$smarty->assign("descreplies", $ascreplies);
	$ratings = array();
	$counter = 1;

	while ($counter <= 5) {
		$ratings[] = $counter;
		++$counter;
	}

	$smarty->assign("ratings", $ratings);

	if ($_SESSION['uid']) {
		$clientname = $clientsdetails['firstname'] . " " . $clientsdetails['lastname'];
		$clientemail = $clientsdetails['email'];

		if ($_SESSION['cid']) {
			$contactdata = get_query_vals("tblcontacts", "firstname,lastname,email", array("id" => $_SESSION['cid'], "userid" => $_SESSION['uid']));
			$clientname = $contactdata['firstname'] . " " . $contactdata['lastname'];
			$clientemail = $contactdata['email'];
		}
	}


	if (!$replyname) {
		$replyname = $clientname;
	}


	if (!$replyemail) {
		$replyemail = $clientemail;
	}

	$smarty->assign("errormessage", $errormessage);
	$smarty->assign("clientname", $clientname);
	$smarty->assign("email", $clientemail);
	$smarty->assign("replyname", $replyname);
	$smarty->assign("replyemail", $replyemail);
	$smarty->assign("replymessage", $replymessage);
	$smarty->assign("allowedfiletypes", $CONFIG['TicketAllowedFileTypes']);
}

outputClientArea($templatefile);
?>