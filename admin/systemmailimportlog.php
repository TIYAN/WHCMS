<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View Ticket Mail Import Log");
$aInt->title = $aInt->lang("system", "mailimportlog");
$aInt->sidebar = "utilities";
$aInt->icon = "logs";
$aInt->requiredFiles(array("ticketfunctions"));

if ($display) {
	$aInt->title = $aInt->lang("system", "viewimportmessage");
	$result = select_query("tblticketmaillog", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	$id = $data['id'];
	$date = $data['date'];
	$to = $data['to'];
	$name = $data['name'];
	$email = $data['email'];
	$subject = $data['subject'];
	$message = $data['message'];
	$status = $data['status'];

	if ($status == "Ticket Imported Successfully") {
		$status = "<font color=#669900>" . $aInt->lang("system", "ticketimportsuccess") . "</font>";
	}


	if ($status == "Ticket Reply Imported Successfully") {
		$status = "<font color=#669900>" . $aInt->lang("system", "ticketreplyimportsuccess") . "</font>";
	}


	if ($status == "Blocked Potential Email Loop") {
		$status = $aInt->lang("system", "ticketimportblockloop");
	}


	if ($status == "Department Not Found") {
		$status = $aInt->lang("system", "ticketimportdeptnotfound");
	}


	if ($status == "Ticket ID Not Found") {
		$status = $aInt->lang("system", "ticketimporttidnotfound");
	}


	if ($status == "Unregistered Email Address") {
		$status = $aInt->lang("system", "ticketimportunregistered");
	}


	if ($status == "Exceeded Limit of 10 Tickets within 15 Minutes") {
		$status = $aInt->lang("system", "ticketimportexceededlimit");
	}


	if ($status == "Blocked Ticket Opening from Unregistered User") {
		$status = $aInt->lang("system", "ticketimportunregisteredopen");
	}


	if ($status == "Only Replies Allowed by Email") {
		$status = $aInt->lang("system", "ticketimportrepliesonly");
	}


	if ($action == "import") {
		check_token("WHMCS.admin.default");
		$tid = $userid = $adminid = 0;
		$from = $admin = "";
		$result = select_query("tblclients", "id", array("email" => $email));
		$data = mysql_fetch_array($result);
		$userid = $data['id'];

		if (!$userid) {
			$from = array("name" => $name, "email" => $email);
		}

		$pos = strpos($subject, "[Ticket ID: ");

		if ($pos === false) {
			$result = select_query("tblticketdepartments", "id", array("email" => $email));
			$data = mysql_fetch_array($result);
			$deptid = $data['id'];

			if (!$deptid) {
				$result = select_query("tblticketdepartments", "id", "", "order", "ASC");
				$data = mysql_fetch_array($result);
				$deptid = $data['id'];
			}

			openNewTicket($userid, "", $deptid, $subject, $message, "Medium", "", $from);
			$status = "Ticket Imported Successfully";
		}
		else {
			$tid = substr($subject, $pos + 12, 6);
			$result = select_query("tbltickets", "", array("tid" => $tid));
			$data = mysql_fetch_array($result);
			$tid = $data['id'];
			$result = select_query("tbladmins", "id", array("email" => $email));
			$data = mysql_fetch_array($result);
			$adminid = $data['id'];

			if ($adminid) {
				$userid = 0;
				$from = "";
				$admin = getAdminName($adminid);
			}

			AddReply($tid, $userid, "", $message, $admin, "", $from);
			$status = "Ticket Reply Imported Successfully";
		}

		update_query("tblticketmaillog", array("status" => $status), array("id" => $id));
		redir("display=true&id=" . $id);
	}

	$content = "<p><b>" . $aInt->lang("emails", "to") . ":</b> " . $to . "<br>
<b>" . $aInt->lang("emails", "from") . ":</b> " . $name . " &laquo;" . $email . "&raquo;<br>
<b>" . $aInt->lang("emails", "subject") . ":</b> " . $subject . "<br>
<b>" . $aInt->lang("fields", "status") . ":</b> " . $status;

	if ($status != "Ticket Imported Successfully" && $status != "Ticket Reply Imported Successfully") {
		$content .= " <input type=\"button\" value=\"" . $aInt->lang("system", "ignoreimport") . "\" onclick=\"window.location='" . $_SERVER['PHP_SELF'] . "?display=true&id=" . $id . generate_token("link") . "&action=import'\" />";
	}

	$content .= "</p>
<p>" . nl2br($message) . "</p>
<p align=\"center\"><a href=\"#\" onClick=\"window.close();return false\">" . $aInt->lang("addons", "closewindow") . "</a></p>";
	$aInt->content = $content;
	$aInt->displayPopUp();
	exit();
}

ob_start();
$aInt->sortableTableInit("date");
$query = "SELECT COUNT(id) as cnt FROM tblticketmaillog ORDER BY id DESC";
$numresults = full_query($query);
$data = mysql_fetch_assoc($numresults);
$numrows = $data['cnt'];
$query = "SELECT * FROM tblticketmaillog ORDER BY id DESC LIMIT " . (int)$page * $limit . "," . (int)$limit;
$result = full_query($query);

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$date = $data['date'];
	$to = $data['to'];
	$name = $data['name'];
	$email = $data['email'];
	$subject = $data['subject'];
	$status = $data['status'];

	if ($status == "Ticket Imported Successfully") {
		$status = "<font color=#669900>" . $aInt->lang("system", "ticketimportsuccess") . "</font>";
	}


	if ($status == "Ticket Reply Imported Successfully") {
		$status = "<font color=#669900>" . $aInt->lang("system", "ticketreplyimportsuccess") . "</font>";
	}


	if ($status == "Blocked Potential Email Loop") {
		$status = $aInt->lang("system", "ticketimportblockloop");
	}


	if ($status == "Department Not Found") {
		$status = $aInt->lang("system", "ticketimportdeptnotfound");
	}


	if ($status == "Ticket ID Not Found") {
		$status = $aInt->lang("system", "ticketimporttidnotfound");
	}


	if ($status == "Unregistered Email Address") {
		$status = $aInt->lang("system", "ticketimportunregistered");
	}


	if ($status == "Exceeded Limit of 10 Tickets within 15 Minutes") {
		$status = $aInt->lang("system", "ticketimportexceededlimit");
	}


	if ($status == "Blocked Ticket Opening from Unregistered User") {
		$status = $aInt->lang("system", "ticketimportunregisteredopen");
	}


	if ($status == "Only Replies Allowed by Email") {
		$status = $aInt->lang("system", "ticketimportrepliesonly");
	}

	$subject = (75 < strlen($subject) ? substr($subject, 0, 75) . "..." : $subject);
	$tabledata[] = array(fromMySQLDate($date, true), $to, "<a href=\"#\" onClick=\"window.open('" . $_SERVER['PHP_SELF'] . ("?display=true&id=" . $id . "','','width=650,height=400,scrollbars=yes');return false\">" . $subject . "</a><br>") . $aInt->lang("emails", "from") . (": " . $name . " &laquo;" . $email . "&raquo;"), $status);
}

echo $aInt->sortableTable(array($aInt->lang("fields", "date"), $aInt->lang("emails", "to"), $aInt->lang("emails", "subject"), $aInt->lang("fields", "status")), $tabledata);
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>