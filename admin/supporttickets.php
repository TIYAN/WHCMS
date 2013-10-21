<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$action = $whmcs->get_req_var("action");

if ($action == "viewticket") {
	$reqperm = "View Support Ticket";
}
else {
	if ($action == "openticket" || $action == "open") {
		$reqperm = "Open New Ticket";
	}
	else {
		$reqperm = "List Support Tickets";
	}
}


if (!$action) {
	$aInt = new WHMCS_Admin($reqperm, false);
}
else {
	$aInt = new WHMCS_Admin($reqperm);
}


if ($action == "open" || $action == "openticket") {
	$icon = "ticketsopen";
	$title = $aInt->lang("support", "opennewticket");
}
else {
	$icon = "tickets";
	$title = $aInt->lang("support", "supporttickets");
}

$aInt->title = $title;
$aInt->sidebar = "support";
$aInt->icon = $icon;
$aInt->helplink = "Support Tickets";
$aInt->requiredFiles(array("ticketfunctions", "modulefunctions", "customfieldfunctions"));
$filt = new WHMCS_Filter("tickets");
$smartyvalues = array();

if ($whmcs->get_req_var("ticketid")) {
	$action = "search";
}


if ($action == "gettags") {
	$array = array();
	$result = select_query("tbltickettags", "DISTINCT tag", "tag LIKE '" . db_escape_string($q) . "%'", "tag", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$array[] = $data[0];
	}

	echo json_encode($array);
	exit();
}


if ($action == "savetags") {
	$access = validateAdminTicketAccess($id);

	if ($access) {
		exit();
	}

	$tags = json_decode(html_entity_decode($tags), true);
	foreach ($tags as $k => $tag) {
		$tags[$k] = strip_tags($tag);
	}

	$existingtags = array();
	$result = select_query("tbltickettags", "tag", array("ticketid" => $id));

	while ($data = mysql_fetch_assoc($result)) {
		$existingtags[] = $data['tag'];
	}

	foreach ($existingtags as $tag) {

		if (trim($tag)) {
			if (!in_array($tag, $tags)) {
				delete_query("tbltickettags", array("ticketid" => $id, "tag" => $tag));
				addTicketLog($id, "Deleted Tag " . $tag);
				continue;
			}

			continue;
		}
	}

	foreach ($tags as $tag) {

		if (trim($tag)) {
			if (!in_array($tag, $existingtags)) {
				insert_query("tbltickettags", array("ticketid" => $id, "tag" => $tag));
				addTicketLog($id, "Added Tag " . $tag);
				continue;
			}

			continue;
		}
	}

	exit();
}


if ($action == "checkstatus") {
	$access = validateAdminTicketAccess($id);

	if ($access) {
		exit();
	}

	$result = select_query("tbltickets", "status", array("id" => $id));
	$data = mysql_fetch_assoc($result);
	$status = $data['status'];

	if ($status == $ticketstatus) {
		echo "true";
	}
	else {
		echo "false";
	}

	exit();
}


if ($action == "split") {
	if (empty($rids)) {
		header("Location:supporttickets.php?action=viewticket&id=" . $id . "");
		exit();
	}

	$access = validateAdminTicketAccess($id);

	if ($access) {
		exit();
	}

	$rids = db_escape_numarray($rids);
	$rids = implode(", ", $rids);
	$noemail = (!$splitnotifyclient ? TRUE : FALSE);
	$result = select_query("tbltickets", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	$newTicketUserid = $data['userid'];
	$newTicketContactid = $data['contactid'];
	$newTicketdepartmentid = $data['did'];
	$newTicketName = $data['name'];
	$newTicketEmail = $data['email'];
	$newTicketAttachment = $data['attachment'];
	$newTicketUrgency = $data['urgency'];
	$newTicketCC = $data['cc'];
	$newTicketService = $data['service'];
	$newTicketTitle = $data['title'];
	$result = select_query("tblticketreplies", "id,message", "`id` IN (" . $rids . ")", "date", "ASC", "0,1");
	$data = mysql_fetch_array($result);
	$messageEarliestID = $data['id'];
	$messageEarliest = $data['message'];
	$messageAdmin = $data['admin'];
	$subject = (trim($splitsubject) ? $splitsubject : $newTicketTitle);
	$deptid = (trim($splitdeptid) ? $splitdeptid : $newTicketdepartmentid);
	$priority = (trim($splitpriority) ? $splitpriority : $newTicketUrgency);
	$newOpenedTicketResults = openNewTicket($newTicketUserid, $newTicketContactid, $deptid, $subject, $messageEarliest, $priority, $newTicketAttachment, array("name" => $newTicketName, "email" => $newTicketEmail), $newTicketService, $newTicketCC, $noemail, $messageAdmin);
	$ticketid = $newOpenedTicketResults['ID'];
	delete_query("tblticketreplies", array("id" => $messageEarliestID));
	update_query("tblticketreplies", array("tid" => $ticketid), "`id` IN (" . $rids . ")");
	header("Location: supporttickets.php?action=viewticket&id=" . $ticketid);
	exit();
}


if ($action == "getmsg") {
	$msg = "";
	$id = substr($ref, 1);

	if (substr($ref, 0, 1) == "t") {
		$access = validateAdminTicketAccess($id);

		if ($access) {
			exit();
		}

		$msg = get_query_val("tbltickets", "message", array("id" => $id));
	}
	else {
		if (substr($ref, 0, 1) == "r") {
			$data = get_query_vals("tblticketreplies", "tid,message", array("id" => $id));
			$id = $data['tid'];
			$msg = $data['message'];
			$access = validateAdminTicketAccess($id);

			if ($access) {
				exit();
			}
		}
	}

	echo html_entity_decode($msg);
	exit();
}


if ($action == "getticketlog") {
	$access = validateAdminTicketAccess($id);

	if ($access) {
		exit();
	}

	$totaltickets = get_query_val("tblticketlog", "COUNT(id)", array("tid" => $id));
	$qlimit = 10;
	$offset = (int)$offset;

	if ($offset < 0) {
		$offset = 0;
	}

	$endnum = $offset + $qlimit;
	echo "<div style=\"padding:0 0 5px 0;text-align:left;\">Showing <strong>" . ($offset + 1) . "</strong> to <strong>" . ($totaltickets < $endnum ? $totaltickets : $endnum) . "</strong> of <strong>" . $totaltickets . " total</strong></div>";
	$aInt->sortableTableInit("nopagination");
	$result = select_query("tblticketlog", "", array("tid" => $id), "date", "DESC", "" . $offset . "," . $qlimit);

	while ($data = mysql_fetch_array($result)) {
		$tabledata[] = array(fromMySQLDate($data['date'], 1), "<div style=\"text-align:left;\">" . $data['action'] . "</div>");
	}

	echo $aInt->sortableTable(array($aInt->lang("fields", "date"), $aInt->lang("permissions", "action")), $tabledata);
	echo "<table width=\"80%\" align=\"center\"><tr><td style=\"text-align:left;\">";

	if (0 < $offset) {
		echo "<a href=\"#\" onclick=\"loadTab(" . $target . ",'ticketlog'," . ($offset - $qlimit) . ");return false\">";
	}

	echo "&laquo; Previous</a></td><td style=\"text-align:right;\">";

	if ($endnum < $totaltickets) {
		echo "<a href=\"#\" onclick=\"loadTab(" . $target . ",'ticketlog'," . $endnum . ");return false\">";
	}

	echo "Next &raquo;</a></td></tr></table>";
	exit();
}


if ($action == "getclientlog") {
	checkPermission("View Activity Log");
	$totaltickets = get_query_val("tblactivitylog", "COUNT(id)", array("userid" => $userid));
	$patterns = $replacements = array();
	$patterns[] = "/User ID: (.*?) /";
	$patterns[] = "/Service ID: (.*?) /";
	$patterns[] = "/Domain ID: (.*?) /";
	$patterns[] = "/Invoice ID: (.*?) /";
	$patterns[] = "/Quote ID: (.*?) /";
	$patterns[] = "/Order ID: (.*?) /";
	$patterns[] = "/Transaction ID: (.*?) /";
	$replacements[] = "<a href=\"clientssummary.php?userid=$1\">User ID: $1</a> ";
	$replacements[] = "<a href=\"clientsservices.php?id=$1\">Service ID: $1</a> ";
	$replacements[] = "<a href=\"clientsdomains.php?id=$1\">Domain ID: $1</a> ";
	$replacements[] = "<a href=\"invoices.php?action=edit&id=$1\">Invoice ID: $1</a> ";
	$replacements[] = "<a href=\"quotes.php?action=manage&id=$1\">Quote ID: $1</a> ";
	$replacements[] = "<a href=\"orders.php?action=view&id=$1\">Order ID: $1</a> ";
	$replacements[] = "<a href=\"transactions.php?action=edit&id=$1\">Transaction ID: $1</a> ";
	$qlimit = 10;
	$offset = (int)$offset;

	if ($offset < 0) {
		$offset = 0;
	}

	$endnum = $offset + $qlimit;
	echo "<div style=\"padding:0 0 5px 0;text-align:left;\">Showing <strong>" . ($offset + 1) . "</strong> to <strong>" . ($totaltickets < $endnum ? $totaltickets : $endnum) . "</strong> of <strong>" . $totaltickets . " total</strong></div>";
	$aInt->sortableTableInit("nopagination");
	$result = select_query("tblactivitylog", "", array("userid" => $userid), "date", "DESC", "" . $offset . "," . $qlimit);

	while ($data = mysql_fetch_array($result)) {
		$description = $data['description'];
		$description .= " ";
		$description = preg_replace($patterns, $replacements, $description);
		$tabledata[] = array(fromMySQLDate($data['date'], 1), "<div style=\"text-align:left;\">" . $description . "</div>", $data['user'], $data['ipaddr']);
	}

	echo $aInt->sortableTable(array($aInt->lang("fields", "date"), $aInt->lang("permissions", "action"), $aInt->lang("support", "user"), $aInt->lang("fields", "ipaddress")), $tabledata);
	echo "<table width=\"80%\" align=\"center\"><tr><td style=\"text-align:left;\">";

	if (0 < $offset) {
		echo "<a href=\"#\" onclick=\"loadTab(" . $target . ",'clientlog'," . ($offset - $qlimit) . ");return false\">";
	}

	echo "&laquo; Previous</a></td><td style=\"text-align:right;\">";

	if ($endnum < $totaltickets) {
		echo "<a href=\"#\" onclick=\"loadTab(" . $target . ",'clientlog'," . $endnum . ");return false\">";
	}

	echo "Next &raquo;</a></td></tr></table>";
	exit();
}


if ($action == "gettickets") {
	$departmentsarray = getDepartments();

	if ($userid) {
		$where = array("userid" => $userid);
	}
	else {
		$where = array("email" => get_query_val("tbltickets", "email", array("id" => $id)));
	}

	$totaltickets = get_query_val("tbltickets", "COUNT(id)", $where);
	$qlimit = 5;
	$offset = (int)$offset;

	if ($offset < 0) {
		$offset = 0;
	}

	$endnum = $offset + $qlimit;
	echo "<div style=\"padding:0 0 5px 0;text-align:left;\">Showing <strong>" . ($offset + 1) . "</strong> to <strong>" . ($totaltickets < $endnum ? $totaltickets : $endnum) . "</strong> of <strong>" . $totaltickets . " total</strong></div>";
	$aInt->sortableTableInit("nopagination");
	$result = select_query("tbltickets", "", $where, "lastreply", "DESC", "" . $offset . "," . $qlimit);

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
		$adminread = $data['adminunread'];
		$adminread = explode(",", $adminread);

		if (!in_array($_SESSION['adminid'], $adminread)) {
			$unread = 8054;
		}
		else {
			$unread = 0;
		}


		if (!trim($title)) {
			$title = "(" . $aInt->lang("emails", "nosubject") . ")";
		}

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
		$flagstyle = ($showflag == "user" ? "<span class=\"ticketflag\">" : "");
		$title = "#" . $ticketnumber . " - " . $title;

		if ($unread || $showflag == "user") {
			$title = "<strong>" . $title . "</strong>";
		}

		$ticketlink = ("<a href=\"" . $PHP_SELF . "?action=viewticket&id=" . $id . "\"") . $ainject . ">";
		$tabledata[] = array("<img src=\"images/" . strtolower($priority) . ("priority.gif\" width=\"16\" height=\"16\" alt=\"" . $priority . "\" class=\"absmiddle\" />"), $flagstyle . $date, $flagstyle . $department, "<div style=\"text-align:left;\">" . $flagstyle . $ticketlink . $title . "</a></div>", $flagstyle . $tstatus, $flagstyle . $lastreply);
	}

	echo $aInt->sortableTable(array("", $aInt->lang("support", "datesubmitted"), $aInt->lang("support", "department"), $aInt->lang("fields", "subject"), $aInt->lang("fields", "status"), $aInt->lang("support", "lastreply")), $tabledata);
	echo "<table width=\"80%\" align=\"center\"><tr><td style=\"text-align:left;\">";

	if (0 < $offset) {
		echo "<a href=\"#\" onclick=\"loadTab(" . $target . ",'tickets'," . ($offset - $qlimit) . ");return false\">";
	}

	echo "&laquo; Previous</a></td><td style=\"text-align:right;\">";

	if ($endnum < $totaltickets) {
		echo "<a href=\"#\" onclick=\"loadTab(" . $target . ",'tickets'," . $endnum . ");return false\">";
	}

	echo "Next &raquo;</a></td></tr></table>";
	exit();
}


if ($action == "getallservices") {
	$pauserid = (int)$userid;
	$currency = getCurrency($pauserid);
	$service = get_query_val("tbltickets", "service", array("id" => $id));
	$output = array();
	$result = select_query("tblhosting", "tblhosting.*,tblproducts.name", array("userid" => $pauserid), "domainstatus` ASC,`id", "DESC", "", "tblproducts ON tblproducts.id=tblhosting.packageid");

	while ($data = mysql_fetch_array($result)) {
		$service_id = $data['id'];
		$service_name = $data['name'];
		$service_domain = $data['domain'];
		$service_firstpaymentamount = $data['firstpaymentamount'];
		$service_recurringamount = $data['amount'];
		$service_billingcycle = $data['billingcycle'];
		$service_regdate = $data['regdate'];
		$service_regdate = fromMySQLDate($service_regdate);
		$service_nextduedate = $data['nextduedate'];
		$service_nextduedate = ($service_nextduedate == "0000-00-00" ? "-" : fromMySQLDate($service_nextduedate));

		if ($service_recurringamount <= 0) {
			$service_amount = $service_firstpaymentamount;
		}
		else {
			$service_amount = $service_recurringamount;
		}

		$service_amount = formatCurrency($service_amount);
		$selected = ((substr($service, 0, 1) == "S" && substr($service, 1) == $service_id) ? true : false);
		$service_name = "<a href=\"clientshosting.php?userid=" . $pauserid . "&id=" . $service_id . "\" target=\"_blank\">" . $service_name . "</a> - <a href=\"http://" . $service_domain . "/\" target=\"_blank\">" . $service_domain . "</a>";
		$output[] = "<tr" . ($selected ? " class=\"rowhighlight\"" : "") . "><td>" . $service_name . "</td><td>" . $service_amount . "</td><td>" . $service_billingcycle . "</td><td>" . $service_regdate . "</td><td>" . $service_nextduedate . "</td><td>" . $data['domainstatus'] . "</td></tr>";
	}

	$predefinedaddons = array();
	$result = select_query("tbladdons", "", "");

	while ($data = mysql_fetch_array($result)) {
		$addon_id = $data['id'];
		$addon_name = $data['name'];
		$predefinedaddons[$addon_id] = $addon_name;
	}

	$result = select_query("tblhostingaddons", "tblhostingaddons.*,tblhostingaddons.id AS addonid,tblhostingaddons.addonid AS addonid2,tblhostingaddons.name AS addonname,tblhosting.id AS hostingid,tblhosting.domain,tblproducts.name", array("tblhosting.userid" => $pauserid), "status` ASC,`tblhosting`.`id", "DESC", "", "tblhosting ON tblhosting.id=tblhostingaddons.hostingid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid");

	while ($data = mysql_fetch_array($result)) {
		$service_id = $data['id'];
		$hostingid = $data['hostingid'];
		$service_addonid = $data['addonid2'];
		$service_name = $data['name'];
		$service_addon = $data['addonname'];
		$service_domain = $data['domain'];
		$service_recurringamount = $data['recurring'];
		$service_billingcycle = $data['billingcycle'];
		$service_regdate = $data['regdate'];
		$service_regdate = fromMySQLDate($service_regdate);
		$service_nextduedate = $data['nextduedate'];
		$service_nextduedate = ($service_nextduedate == "0000-00-00" ? "-" : fromMySQLDate($service_nextduedate));

		if ($service_recurringamount <= 0) {
			$service_amount = $service_firstpaymentamount;
		}
		else {
			$service_amount = $service_recurringamount;
		}


		if (!$service_addon) {
			$service_addon = $predefinedaddons[$service_addonid];
		}

		$service_amount = formatCurrency($service_recurringamount);
		$selected = ((substr($service, 0, 1) == "A" && substr($service, 1) == $service_id) ? true : false);
		$service_name = $aInt->lang("orders", "addon") . (" - " . $service_addon . "<br /><a href=\"clientshosting.php?userid=" . $pauserid . "&id=" . $hostingid . "\" target=\"_blank\">" . $service_name . "</a> - <a href=\"http://" . $service_domain . "/\" target=\"_blank\">" . $service_domain . "</a>");
		$output[] = "<tr" . ($selected ? " class=\"rowhighlight\"" : "") . "><td>" . $service_name . "</td><td>" . $service_amount . "</td><td>" . $service_billingcycle . "</td><td>" . $service_regdate . "</td><td>" . $service_nextduedate . "</td><td>" . $data['status'] . "</td></tr>";
	}

	$result = select_query("tbldomains", "", array("userid" => $pauserid), "status` ASC,`id", "DESC");

	while ($data = mysql_fetch_array($result)) {
		$service_id = $data['id'];
		$service_domain = $data['domain'];
		$service_firstpaymentamount = $data['firstpaymentamount'];
		$service_recurringamount = $data['recurringamount'];
		$service_registrationperiod = $data['registrationperiod'] . " Year(s)";
		$service_regdate = $data['registrationdate'];
		$service_regdate = fromMySQLDate($service_regdate);
		$service_nextduedate = $data['nextduedate'];
		$service_nextduedate = ($service_nextduedate == "0000-00-00" ? "-" : fromMySQLDate($service_nextduedate));

		if ($service_recurringamount <= 0) {
			$service_amount = $service_firstpaymentamount;
		}
		else {
			$service_amount = $service_recurringamount;
		}

		$service_amount = formatCurrency($service_amount);
		$selected = ((substr($service, 0, 1) == "D" && substr($service, 1) == $service_id) ? true : false);
		$service_name = "<a href=\"clientsdomains.php?userid=" . $pauserid . "&id=" . $service_id . "\" target=\"_blank\">" . $aInt->lang("fields", "domain") . ("</a> - <a href=\"http://" . $service_domain . "/\" target=\"_blank\">" . $service_domain . "</a>");
		$output[] = "<tr" . ($selected ? " class=\"rowhighlight\"" : "") . "><td>" . $service_name . "</td><td>" . $service_amount . "</td><td>" . $service_registrationperiod . "</td><td>" . $service_regdate . "</td><td>" . $service_nextduedate . "</td><td>" . $data['status'] . "</td></tr>";
	}

	$i = 0;

	while ($i <= 9) {
		unset($output[$i]);
		++$i;
	}

	echo implode($output);
	exit();
}


if ($action == "updatereply") {
	if (substr($ref, 0, 1) == "t") {
		update_query("tbltickets", array("message" => $text), array("id" => substr($ref, 1)));
	}
	else {
		if (substr($ref, 0, 1) == "r") {
			update_query("tblticketreplies", array("message" => $text), array("id" => substr($ref, 1)));
		}
		else {
			if ($id && is_numeric($id)) {
				update_query("tblticketreplies", array("message" => $text), array("id" => $id));
			}
		}
	}

	$text = nl2br($text);
	$text = ticketAutoHyperlinks($text);
	echo $text;
	exit();
}


if ($action == "makingreply") {
	$access = validateAdminTicketAccess($id);

	if ($access) {
		exit();
	}

	$result = select_query("tbltickets", "replyingadmin,replyingtime", array("id" => $id, "replyingadmin" => array("sqltype" => ">", "value" => "0")));

	if (mysql_num_rows($result)) {
		$data = mysql_fetch_assoc($result);
		$replyingadmin = $data['replyingadmin'];
		$replyingtime = $data['replyingtime'];
		$replyingtime = fromMySQLDate($replyingtime, "time");

		if ($replyingadmin != $_SESSION['adminid']) {
			$result = select_query("tbladmins", "", array("id" => $replyingadmin));
			$data = mysql_fetch_array($result);
			$replyingadmin = ucfirst($data['username']);
			echo "<div class=\"errorbox\">" . $replyingadmin . " " . $aInt->lang("support", "viewedandstarted") . (" @ " . $replyingtime . "</div>");
		}
	}
	else {
		update_query("tbltickets", array("replyingadmin" => $_SESSION['adminid'], "replyingtime" => "now()"), array("id" => $id));
	}

	exit();
}


if ($action == "endreply") {
	$access = validateAdminTicketAccess($id);

	if ($access) {
		exit();
	}

	update_query("tbltickets", array("replyingadmin" => ""), array("id" => $id));
	exit();
}


if ($action == "changestatus") {
	$access = validateAdminTicketAccess($id);

	if ($access) {
		exit();
	}


	if ($status == "Closed") {
		closeTicket($id);
	}
	else {
		addTicketLog($id, "Status changed to " . $status);
		update_query("tbltickets", array("status" => $status), array("id" => $id));
		run_hook("TicketStatusChange", array("adminid" => $_SESSION['adminid'], "status" => $status, "ticketid" => $id));
	}

	exit();
}


if ($action == "changeflag") {
	$access = validateAdminTicketAccess($id);

	if ($access) {
		exit();
	}

	addTicketLog($id, "Flagged to " . getAdminName($flag));
	update_query("tbltickets", array("flag" => $flag), array("id" => $id));

	if ($flag != 0 && $flag != $_SESSION['adminid']) {
		echo "1";
	}

	exit();
}


if ($action == "loadpredefinedreplies") {
	echo genPredefinedRepliesList($cat, $predefq);
	exit();
}


if ($action == "getpredefinedreply") {
	$result = select_query("tblticketpredefinedreplies", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	$reply = html_entity_decode($data['reply']);
	echo $reply;
	exit();
}


if ($action == "getquotedtext") {
	$replytext = "";

	if ($id) {
		$access = validateAdminTicketAccess($id);

		if ($access) {
			exit();
		}

		$result = select_query("tbltickets", "message", array("id" => $id));
		$data = mysql_fetch_array($result);
		$replytext = $data['message'];
	}
	else {
		if ($ids) {
			$result = select_query("tblticketreplies", "tid,message", array("id" => $ids));
			$data = mysql_fetch_array($result);
			$id = $data['tid'];
			$access = validateAdminTicketAccess($id);

			if ($access) {
				exit();
			}

			$replytext = $data['message'];
		}
	}

	$replytext = wordwrap(html_entity_decode(strip_tags($replytext)), 80);
	$replytext = explode("\r\n", $replytext);

	foreach ($replytext as $line) {
		echo "> " . $line . "\r\n";
	}

	exit();
}


if ($action == "getcontacts") {
	echo getTicketContacts($userid);
	exit();
}


if (!$action) {
	if ($sub == "deleteticket") {
		checkPermission("Delete Ticket");
		deleteTicket($id);
		header("Location: supporttickets.php");
		exit();
	}


	if ($sub == "multipleaction") {
		check_token("WHMCS.admin.default");

		if ($close) {
			foreach ($selectedtickets as $id) {
				closeTicket($id);
			}
		}


		if ($delete) {
			checkPermission("Delete Ticket");
			foreach ($selectedtickets as $id) {
				deleteTicket($id);
			}
		}


		if ($blockdelete) {
			checkPermission("Delete Ticket");
			foreach ($selectedtickets as $id) {
				$result = select_query("tbltickets", "userid,email", array("id" => $id));
				$data = mysql_fetch_array($result);
				$userid = $data['userid'];
				$email = $data['email'];

				if ($userid) {
					$result = select_query("tblclients", "email", array("id" => $userid));
					$data = mysql_fetch_array($result);
					$email = $data['email'];
				}

				$result = select_query("tblticketspamfilters", "COUNT(*)", array("type" => "Sender", "content" => $email));
				$data = mysql_fetch_array($result);
				$blockedalready = $data[0];

				if (!$blockedalready) {
					insert_query("tblticketspamfilters", array("type" => "Sender", "content" => $email));
				}

				deleteTicket($id);
			}
		}


		if ($merge) {
			sort($selectedtickets);
			$mastertid = $selectedtickets[0];
			$adminname = getAdminName();
			addTicketLog($mastertid, "Merged Tickets " . implode(",", $selectedtickets));
			$adminname = "";
			$result = select_query("tbltickets", "title,userid", array("id" => $mastertid));
			$data = mysql_fetch_array($result);
			$userid = $data['userid'];
			getUsersLang($userid);
			$merge = $_LANG['ticketmerge'];

			if (!$merge) {
				$merge = "MERGED";
			}

			$subject = (strpos($data[0], (" [" . $merge . "]")) === FALSE ? $data[0] . (" [" . $merge . "]") : $data[0]);
			update_query("tbltickets", array("title" => $subject), array("id" => $mastertid));
			foreach ($selectedtickets as $id) {
				update_query("tblticketnotes", array("ticketid" => $mastertid), array("ticketid" => $id));
				update_query("tblticketreplies", array("tid" => $mastertid), array("tid" => $id));

				if ($id != $mastertid) {
					$result = select_query("tbltickets", "", array("id" => $id));
					$data = mysql_fetch_array($result);
					$userid = $data['userid'];
					$name = $data['name'];
					$email = $data['email'];
					$date = $data['date'];
					$message = $data['message'];
					$admin = $data['admin'];
					$attachment = $data['attachment'];
					insert_query("tblticketreplies", array("tid" => $mastertid, "userid" => $userid, "name" => $name, "email" => $email, "date" => $date, "message" => $message, "admin" => $admin, "attachment" => $attachment));
					delete_query("tbltickets", array("id" => $id));
					continue;
				}
			}
		}

		$filt->redir();
	}
}
else {
	if ($action == "mergeticket") {
		$result = select_query("tbltickets", "id", array("tid" => $mergetid));
		$data = mysql_fetch_array($result);
		$mergeid = $data['id'];

		if (!$mergeid) {
			exit($aInt->lang("support", "mergeidnotfound"));
		}


		if ($mergeid == $id) {
			exit($aInt->lang("support", "mergeticketequal"));
		}

		$mastertid = $id;

		if ($mergeid < $mastertid) {
			$mastertid = $mergeid;
			$mergeid = $id;
		}

		$adminname = getAdminName();
		addTicketLog($mastertid, "Merged Ticket " . $mergeid);
		$adminname = "";
		$result = select_query("tbltickets", "title,userid", array("id" => $mastertid));
		$data = mysql_fetch_array($result);
		$userid = $data['userid'];
		getUsersLang($userid);
		$merge = $_LANG['ticketmerge'];

		if (!$merge) {
			$merge = "MERGED";
		}

		$subject = (strpos($data[0], (" [" . $merge . "]")) === FALSE ? $data[0] . (" [" . $merge . "]") : $data[0]);
		update_query("tbltickets", array("title" => $subject), array("id" => $mastertid));
		update_query("tblticketnotes", array("ticketid" => $mastertid), array("ticketid" => $mergeid));
		update_query("tblticketreplies", array("tid" => $mastertid), array("tid" => $mergeid));
		$result = select_query("tbltickets", "", array("id" => $mergeid));
		$data = mysql_fetch_array($result);
		$userid = $data['userid'];
		$name = $data['name'];
		$email = $data['email'];
		$date = $data['date'];
		$message = $data['message'];
		$admin = $data['admin'];
		$attachment = $data['attachment'];
		insert_query("tblticketreplies", array("tid" => $mastertid, "userid" => $userid, "name" => $name, "email" => $email, "date" => $date, "message" => $message, "admin" => $admin, "attachment" => $attachment));
		delete_query("tbltickets", array("id" => $mergeid));
		header("Location: supporttickets.php?action=viewticket&id=" . $mastertid);
		exit();
	}
	else {
		if ($action == "openticket") {
			check_token("WHMCS.admin.default");
			$errormessage = "";

			if (!trim($message)) {
				$errormessage = $aInt->lang("support", "ticketmessageerror");
			}


			if (!trim($subject)) {
				$errormessage = $aInt->lang("support", "ticketsubjecterror");
			}


			if (!$client) {
				if (!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9+_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $email)) {
					$errormessage = $aInt->lang("support", "ticketemailvalidationerror");
				}


				if (!$email) {
					$errormessage = $aInt->lang("support", "ticketemailerror");
				}


				if (!$name) {
					$errormessage = $aInt->lang("support", "ticketnameerror");
				}
			}


			if (!$errormessage) {
				$attachments = uploadTicketAttachments(true);
				$client = (int)str_replace("UserID:", "", $client);
				$ticketdata = openNewTicket($client, $contactid, $deptid, $subject, $message, $priority, $attachments, array("name" => $name, "email" => $email), $relatedservice, $ccemail, ($sendemail ? false : true), true);
				$id = $ticketdata['ID'];
				header("Location: supporttickets.php?action=viewticket&id=" . $id);
				exit();
			}
			else {
				$action = "open";
			}
		}
		else {
			if ($action == "viewticket") {
				if ($postreply || $postaction) {
					check_token("WHMCS.admin.default");

					if ($postaction == "note") {
						AddNote($id, $message);
					}
					else {
						$attachments = uploadTicketAttachments(true);

						if ($postaction == "close") {
							$newstatus = "Closed";
						}
						else {
							if (substr($postaction, 0, 9) == "setstatus") {
								$result = select_query("tblticketstatuses", "title", array("id" => substr($postaction, 9)));
								$data = mysql_fetch_array($result);
								$newstatus = $data[0];
							}
							else {
								if ($postaction == "onhold") {
									$newstatus = "On Hold";
								}
								else {
									if ($postaction == "inprogress") {
										$newstatus = "In Progress";
									}
									else {
										$newstatus = "Answered";
									}
								}
							}
						}

						AddReply($id, "", "", $message, true, $attachments, "", $newstatus);
						run_hook("TicketStatusChange", array("adminid" => $_SESSION['adminid'], "status" => $newstatus, "ticketid" => $id));

						if ($billingdescription && $billingdescription != $aInt->lang("support", "toinvoicedes")) {
							checkPermission("Create Invoice");
							$result = select_query("tbltickets", "", array("id" => $id));
							$data = mysql_fetch_array($result);
							$userid = $data['userid'];
							$contactid = $data['contactid'];
							$invoicenow = false;

							if ($billingaction == "3") {
								$invoicenow = true;
								$billingaction = "1";
							}

							$billingamount = preg_replace("/[^0-9.]/", "", $billingamount);
							insert_query("tblbillableitems", array("userid" => $userid, "description" => $billingdescription, "amount" => $billingamount, "recur" => 0, "recurcycle" => 0, "recurfor" => 0, "invoiceaction" => $billingaction, "duedate" => "now()"));

							if ($invoicenow) {
								require ROOTDIR . "/includes/clientfunctions.php";
								require ROOTDIR . "/includes/processinvoices.php";
								require ROOTDIR . "/includes/invoicefunctions.php";
								createInvoices($userid);
							}
						}
					}

					update_query("tbltickets", array("replyingadmin" => "", "replyingtime" => ""), array("id" => $id));

					if ($postaction == "close") {
						closeTicket($id);
						$filt->redir();
					}
					else {
						if ($postaction == "return") {
							$filt->redir();
						}
						else {
							if ($postaction == "onhold") {
								update_query("tbltickets", array("status" => "On Hold"), array("id" => $id));
								run_hook("TicketStatusChange", array("adminid" => $_SESSION['adminid'], "status" => "On Hold", "ticketid" => $id));
							}
							else {
								if ($postaction == "inprogress") {
									update_query("tbltickets", array("status" => "In Progress"), array("id" => $id));
									run_hook("TicketStatusChange", array("adminid" => $_SESSION['adminid'], "status" => "In Progress", "ticketid" => $id));
								}
							}
						}
					}

					header("Location: supporttickets.php?action=viewticket&id=" . $id);
					exit();
				}


				if ($deptid) {
					check_token("WHMCS.admin.default");
					$adminname = getAdminName();
					$result = select_query("tbltickets", "", array("id" => $id));
					$data = mysql_fetch_array($result);
					$orig_userid = $data['userid'];
					$orig_contactid = $data['contactid'];
					$orig_deptid = $data['did'];
					$orig_status = $data['status'];
					$orig_priority = $data['urgency'];
					$orig_flag = $data['flag'];
					$orig_cc = $data['cc'];

					if ($orig_userid != $userid) {
						addTicketLog($id, "Ticket Assigned to User ID " . $userid);
					}


					if ($orig_deptid != $deptid) {
						$ticket = new WHMCS_Tickets();
						$ticket->setID($id);
						$ticket->changeDept($deptid);
					}


					if ($orig_status != $status) {
						if ($status == "Closed") {
							closeTicket($id);
						}
						else {
							addTicketLog($id, "Status changed to " . $status);
						}
					}


					if ($orig_priority != $priority) {
						addTicketLog($id, "Priority changed to " . $priority);
					}


					if ($orig_cc != $cc) {
						addTicketLog($id, "Modified CC Recipients");
					}


					if ($orig_flag != $flagto) {
						$ticket = new WHMCS_Tickets();
						$ticket->setID($id);
						$ticket->setFlagTo($flagto);
					}

					$table = "tbltickets";
					$array = array("status" => $status, "urgency" => $priority, "title" => $subject, "userid" => $userid, "cc" => $cc);
					$where = array("id" => $id);
					update_query($table, $array, $where);

					if ($orig_status != "Closed" && $status == "Closed") {
						run_hook("TicketClose", array("ticketid" => $id));
					}


					if ($mergetid) {
						header("Location: supporttickets.php?action=mergeticket&id=" . $id . "&mergetid=" . $mergetid);
						exit();
					}

					header("Location: supporttickets.php?action=viewticket&id=" . $id);
					exit();
				}


				if ($removeattachment) {
					if ($type == "r") {
						$result = select_query("tblticketreplies", "", array("id" => $idsd));
						$data = mysql_fetch_array($result);
						$attachment = $data['attachment'];

						if (strpos($attachment, "|") !== FALSE) {
							$attachment = explode("|", $attachment);
							$count = 0;
							foreach ($attachment as $file) {

								if ($count != $filecount) {
									$keepfile .= $file . "|";
								}
								else {
									$filetoremove = $file;
								}

								++$count;
							}

							$keepfile = substr($keepfile, 0, 0 - 1);
							unlink($attachments_dir . $filetoremove);
							update_query("tblticketreplies", array("attachment" => $keepfile), array("id" => $idsd));
						}
						else {
							unlink($attachments_dir . $attachment);
							update_query("tblticketreplies", array("attachment" => ""), array("id" => $idsd));
						}
					}
					else {
						$result = select_query("tbltickets", "", array("id" => $idsd));
						$data = mysql_fetch_array($result);
						$attachment = $data['attachment'];

						if (strpos($attachment, "|") !== FALSE) {
							$attachment = explode("|", $attachment);
							$count = 0;
							foreach ($attachment as $file) {

								if ($count != $filecount) {
									$keepfile .= $file . "|";
								}
								else {
									$filetoremove = $file;
								}

								++$count;
							}

							$keepfile = substr($keepfile, 0, 0 - 1);
							unlink($attachments_dir . $filetoremove);
							update_query("tbltickets", array("attachment" => $keepfile), array("id" => $idsd));
						}
						else {
							unlink($attachments_dir . $attachment);
							update_query("tbltickets", array("attachment" => ""), array("id" => $idsd));
						}
					}

					header("Location: supporttickets.php?action=viewticket&id=" . $id);
					exit();
				}


				if ($sub == "del") {
					checkPermission("Delete Ticket");
					deleteTicket($id, $idsd);
					header("Location: supporttickets.php?action=viewticket&id=" . $id);
					exit();
				}


				if ($sub == "delnote") {
					delete_query("tblticketnotes", array("id" => $idsd));
					addTicketLog($id, "Deleted Ticket Note ID " . $idsd);
					header("Location: supporttickets.php?action=viewticket&id=" . $id);
					exit();
				}


				if ($blocksender) {
					$result = select_query("tbltickets", "userid,email", array("id" => $id));
					$data = get_query_vals("tbltickets", "userid,email", array("id" => $id));
					$userid = $data['userid'];
					$email = $data['email'];

					if ($userid) {
						$email = get_query_val("tblclients", "email", array("id" => $userid));
					}

					$blockedalready = get_query_val("tblticketspamfilters", "COUNT(*)", array("type" => "Sender", "content" => $email));

					if ($blockedalready) {
						infoBox($aInt->lang("support", "spamupdatefailed"), $aInt->lang("support", "spamupdatefailedinfo"));
					}
					else {
						insert_query("tblticketspamfilters", array("type" => "Sender", "content" => $email));
						infoBox($aInt->lang("support", "spamupdatesuccess"), $aInt->lang("support", "spamupdatesuccessinfo"));
					}
				}
			}
		}
	}
}


if ($autorefresh) {
	if ($autorefresh == "Never") {
		wDelCookie("AutoRefresh");
	}
	else {
		wSetCookie("AutoRefresh", $autorefresh, time() + 90 * 24 * 60 * 60);
	}

	header("Location: supporttickets.php");
}


if ($action == "viewticket") {
	$result = select_query("tbltickets", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	$replyingadmin = $data['replyingadmin'];

	if (!$replyingadmin) {
		$adminheaderbodyjs = "onunload=\"endMakingReply();\"";
	}
}

$supportdepts = getAdminDepartmentAssignments();
ob_start();
$smartyvalues['ticketfilterdata'] = array("view" => $filt->getFromSession("view"), "deptid" => $filt->getFromSession("deptid"), "subject" => $filt->getFromSession("subject"), "email" => $filt->getFromSession("email"));

if (!$action) {
	$smartyvalues['inticketlist'] = true;

	if (!count($supportdepts)) {
		$aInt->gracefulExit($aInt->lang("permissions", "accessdenied") . " - " . $aInt->lang("support", "noticketdepts"));
	}

	$tickets = new WHMCS_Tickets();

	if ($_COOKIE['WHMCSAutoRefresh'] && !$action) {
		$refreshtime = intval($_COOKIE['WHMCSAutoRefresh']) * 60;

		if ($refreshtime && !$disable_auto_ticket_refresh) {
			echo "<meta http-equiv=\"refresh\" content=\"" . $refreshtime . "\">";
		}
	}

	echo $aInt->Tabs(array($aInt->lang("global", "searchfilter"), $aInt->lang("support", "autorefresh")), true);
	$filterops = array("view", "deptid", "client", "subject", "email", "tag");
	$filt->setAllowedVars($filterops);
	$view = $filt->get("view");
	$deptid = $filt->get("deptid");
	$client = $filt->get("client");
	$subject = $filt->get("subject");
	$email = $filt->get("email");
	$tag = $filt->get("tag");
	$filt->store();
	$smartyvalues['ticketfilterdata'] = array("view" => $view, "deptid" => $deptid, "subject" => $subject, "email" => $email);
	echo "
<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form action=\"";
	echo $PHP_SELF;
	echo "\" method=\"post\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
	echo $aInt->lang("fields", "status");
	echo "</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"view\">
<option value=\"any\"";

	if ($view == "any") {
		echo " selected";
	}

	echo ">";
	echo $aInt->lang("global", "any");
	echo "</option>
<option value=\"\"";

	if ($view == "") {
		echo " selected";
	}

	echo ">";
	echo $aInt->lang("support", "awaitingreply");
	echo "</option>
<option value=\"flagged\"";

	if ($view == "flagged") {
		echo " selected";
	}

	echo ">";
	echo $aInt->lang("support", "flagged");
	echo "</option>
<option value=\"active\"";

	if ($view == "active") {
		echo " selected";
	}

	echo ">";
	echo $aInt->lang("support", "allactive");
	echo "</option>
";
	$result = select_query("tblticketstatuses", "", "", "sortorder", "ASC");

	while ($data = mysql_fetch_array($result)) {
		echo "<option";

		if ($view == $data['title']) {
			echo " selected";
		}

		echo ">" . $data['title'] . "</option>";
	}

	echo "</select></td><td width=\"15%\" class=\"fieldlabel\">";
	echo $aInt->lang("fields", "client");
	echo "</td><td class=\"fieldarea\">";

	if ($CONFIG['DisableClientDropdown']) {
		echo "<input type=\"text\" name=\"client\" value=\"" . $client . "\" size=\"10\" />";
	}
	else {
		echo $aInt->clientsDropDown($client, "", "client", true);
	}

	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("support", "department");
	echo "</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"deptid\"><option value=\"\">";
	echo $aInt->lang("global", "any");
	echo "</option>";
	$result = select_query("tblticketdepartments", "", "", "order", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$name = $data['name'];

		if (in_array($id, $supportdepts)) {
			echo "<option value=\"" . $id . "\"";

			if ($id == $deptid) {
				echo " selected";
			}

			echo ">" . $name . "</option>";
		}
	}

	echo "</select></td><td class=\"fieldlabel\">";
	echo $aInt->lang("support", "ticketid");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ticketid\" size=\"15\" /></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("support", "subjectmessage");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"subject\" size=\"40\" value=\"";
	echo $subject;
	echo "\" /></td><td class=\"fieldlabel\">";
	echo $aInt->lang("fields", "email");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"email\" size=\"40\" value=\"";
	echo $email;
	echo "\" /></td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"10\" width=\"1\"><br>
<DIV ALIGN=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("global", "searchfilter");
	echo "\" class=\"button\"></DIV>

</form>

  </div>
</div>
<div id=\"tab1box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form action=\"";
	echo $PHP_SELF;
	echo "\" method=\"post\">
<div align=\"center\">";
	echo $aInt->lang("support", "autorefreshevery");
	echo " ";
	echo "<s";
	echo "elect name=\"autorefresh\"><option>Never</option><option value=\"1\"";

	if ($_COOKIE['WHMCSAutoRefresh'] == 1) {
		echo "selected";
	}

	echo ">1 ";
	echo $aInt->lang("support", "minute");
	echo "</option><option value=\"2\"";

	if ($_COOKIE['WHMCSAutoRefresh'] == 2) {
		echo "selected";
	}

	echo ">2 ";
	echo $aInt->lang("support", "minutes");
	echo "</option><option value=\"5\"";

	if ($_COOKIE['WHMCSAutoRefresh'] == 5) {
		echo "selected";
	}

	echo ">5 ";
	echo $aInt->lang("support", "minutes");
	echo "</option><option value=\"10\"";

	if ($_COOKIE['WHMCSAutoRefresh'] == 10) {
		echo "selected";
	}

	echo ">10 ";
	echo $aInt->lang("support", "minutes");
	echo "</option><option value=\"15\"";

	if ($_COOKIE['WHMCSAutoRefresh'] == 15) {
		echo "selected";
	}

	echo ">15 ";
	echo $aInt->lang("support", "minutes");
	echo "</option></select> <input type=\"submit\" value=\"";
	echo $aInt->lang("support", "setautorefresh");
	echo "\" class=\"button\" /></div>
</form>

  </div>
</div>
<div id=\"tab2box\" class=\"tabbox\">
  <div id=\"tab_content\">

  </div>
</div>

<br />

";
	$departmentsarray = getDepartments();
	$tag = $whmcs->get_req_var("tag");

	if ($tag) {
		echo "<h2>Filtering Tickets for Tag <strong>\"" . $tag . "\"</strong></h2>";
	}

	$tagjoin = ($tag ? " INNER JOIN tbltickettags ON tbltickettags.ticketid=tbltickets.id" : "");
	$query = " FROM tbltickets LEFT JOIN tblclients ON tblclients.id=tbltickets.userid" . $tagjoin . " WHERE ";
	$filters = $statusfilter = array();

	if ($view == "") {
		$result = select_query("tblticketstatuses", "title", array("showawaiting" => "1"));

		while ($data = mysql_fetch_array($result)) {
			$statusfilter[] = $data[0];
		}

		$filters[] = "tbltickets.status IN (" . db_build_in_array($statusfilter) . ")";
	}
	else {
		if ($view == "any") {
		}
		else {
			if ($view == "active") {
				$result = select_query("tblticketstatuses", "title", array("showactive" => "1"));

				while ($data = mysql_fetch_array($result)) {
					$statusfilter[] = $data[0];
				}

				$filters[] = "tbltickets.status IN (" . db_build_in_array($statusfilter) . ")";
			}
			else {
				if ($view == "flagged") {
					$result = select_query("tblticketstatuses", "title", array("showactive" => "1"));

					while ($data = mysql_fetch_array($result)) {
						$statusfilter[] = $data[0];
					}

					$filters[] = "tbltickets.status IN (" . db_build_in_array($statusfilter) . ") AND flag=" . (int)$_SESSION['adminid'];
				}
				else {
					$filters[] = "tbltickets.status='" . db_escape_string($view) . "'";
				}
			}
		}
	}

	$deptfilter = false;

	if ((($client || $subject) || $email) || $clientname) {
	}
	else {
		if (!checkPermission("View Flagged Tickets", true)) {
			$filters[] = "(flag=" . (int)$_SESSION['adminid'] . " OR flag=0)";
		}

		$deptfilter = true;
	}


	if ($client) {
		$filters[] = "tbltickets.userid='" . db_escape_string($client) . "'";
	}


	if ($deptid) {
		$filters[] = "tbltickets.did='" . db_escape_string($deptid) . "'";
	}


	if ($subject) {
		$filters[] = "(tbltickets.title LIKE '%" . db_escape_string($subject) . "%' OR tbltickets.message LIKE '%" . db_escape_string($subject) . "%')";
	}


	if ($email) {
		$filters[] = "(tbltickets.email LIKE '%" . db_escape_string($email) . "%' OR tblclients.email LIKE '%" . db_escape_string($email) . "%' OR tbltickets.name LIKE '%" . db_escape_string($email) . "%')";
	}


	if ($clientname) {
		$filters[] = "(tbltickets.name LIKE '%" . db_escape_string($clientname) . "%' OR concat(tblclients.firstname,' ',tblclients.lastname) LIKE '%" . db_escape_string($clientname) . "%')";
	}


	if ($tag) {
		$filters[] = "tbltickettags.tag='" . db_escape_string($tag) . "'";
	}

	releaseSession();
	$query .= implode(" AND ", array_merge($filters, array("tbltickets.flag=" . (int)$_SESSION['adminid']))) . " ORDER BY tbltickets.lastreply DESC";
	$numresultsquery = "SELECT COUNT(tbltickets.id)" . $query;
	$result = full_query($numresultsquery);
	$data = mysql_fetch_array($result);
	$numrows = $data[0];
	$aInt->sortableTableInit("nopagination");
	$query = "SELECT tbltickets.*,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblclients.groupid" . $query . " LIMIT " . (int)$page * $limit . "," . (int)$limit;
	$result = full_query($query);
	buildAdminTicketListArray($result);
	$tableformurl = "?view=" . $view . "&sub=multipleaction";
	$tableformbuttons = "<input onclick=\"return confirm('" . $aInt->lang("support", "massmergeconfirm", "1") . "');\" type=\"submit\" value=\"" . $aInt->lang("clientsummary", "merge") . "\" name=\"merge\" class=\"btn-small\" /> <input onclick=\"return confirm('" . $aInt->lang("support", "masscloseconfirm", "1") . "');\" type=\"submit\" value=\"" . $aInt->lang("global", "close") . "\" name=\"close\" class=\"btn-small\" /> <input onclick=\"return confirm('" . $aInt->lang("support", "massdeleteconfirm", "1") . "');\" type=\"submit\" value=\"" . $aInt->lang("global", "delete") . "\" name=\"delete\" class=\"btn-small\" /> <input onclick=\"return confirm('" . $aInt->lang("support", "massblockdeleteconfirm", "1") . "');\" type=\"submit\" value=\"" . $aInt->lang("support", "blockanddelete") . "\" name=\"blockdelete\" class=\"btn-small\" />";

	if (count($tabledata)) {
		echo "<h2>" . $aInt->lang("support", "assignedtickets") . "</h2><p>" . sprintf($aInt->lang("support", "numticketsassigned"), count($tabledata)) . "</p>" . $aInt->sortableTable(array("checkall", "", $aInt->lang("support", "department"), $aInt->lang("fields", "subject"), $aInt->lang("support", "submitter"), $aInt->lang("fields", "status"), $aInt->lang("support", "lastreply")), $tabledata, $tableformurl, $tableformbuttons) . "<br /><h2>" . $aInt->lang("support", "unassignedtickets") . "</h2>";
	}

	$aInt->sortableTableInit("lastreply", "ASC", array("view", "client", "deptid", "subject", "email", "clientname"));
	$tabledata = array();
	$query = " FROM tbltickets LEFT JOIN tblclients ON tblclients.id=tbltickets.userid" . $tagjoin . " WHERE ";
	$filters[] = "tbltickets.flag!=" . (int)$_SESSION['adminid'];

	if ($deptfilter) {
		$filters[] = "did IN (" . db_build_in_array(getAdminDepartmentAssignments()) . ")";
	}

	$query .= implode(" AND ", $filters) . (" ORDER BY tbltickets." . $orderby . " " . $order);
	$numresultsquery = "SELECT COUNT(tbltickets.id)" . $query;
	$result = full_query($numresultsquery);
	$data = mysql_fetch_array($result);
	$numrows = $data[0];
	$query = "SELECT tbltickets.*,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblclients.groupid" . $query . " LIMIT " . (int)$page * $limit . "," . (int)$limit;
	$result = full_query($query);
	buildAdminTicketListArray($result);
	echo $aInt->sortableTable(array("checkall", "", $aInt->lang("support", "department"), array("title", $aInt->lang("fields", "subject")), $aInt->lang("support", "submitter"), array("status", $aInt->lang("fields", "status")), array("lastreply", $aInt->lang("support", "lastreply"))), $tabledata, $tableformurl, $tableformbuttons, true);
	$smartyvalues['tagcloud'] = $tickets->buildTagCloud();
}


if ($action == "search") {
	$where = "tid='" . db_escape_string($ticketid) . "' AND did IN (" . implode(",", db_escape_numarray(getAdminDepartmentAssignments())) . ")";
	$result = select_query("tbltickets", "", $where);
	$data = mysql_fetch_array($result);
	$id = $data['id'];

	if (!$id) {
		echo "<p>" . $aInt->lang("support", "ticketnotfound") . "  <a href=\"javascript:history.go(-1)\">" . $aInt->lang("support", "pleasetryagain") . "</a>.</p>";
	}
	else {
		$action = "viewticket";
	}
}


if ($action == "viewticket") {
	releaseSession();
	$aInt->template = "viewticket";
	$smartyvalues['inticket'] = true;
	$ticket = new WHMCS_Tickets();
	$ticket->setID($id);
	$data = $ticket->getData();
	$id = $data['id'];
	$tid = $data['tid'];
	$deptid = $data['did'];
	$pauserid = $data['userid'];
	$pacontactid = $data['contactid'];
	$name = $data['name'];
	$email = $data['email'];
	$cc = $data['cc'];
	$date = $data['date'];
	$title = $data['title'];
	$message = $data['message'];
	$tstatus = $data['status'];
	$admin = $data['admin'];
	$attachment = $data['attachment'];
	$urgency = $data['urgency'];
	$lastreply = $data['lastreply'];
	$flag = $data['flag'];
	$replyingadmin = $data['replyingadmin'];
	$replyingtime = $data['replyingtime'];
	$service = $data['service'];
	$replyingtime = fromMySQLDate($replyingtime, "time");
	$access = validateAdminTicketAccess($id);

	if ($access == "invalidid") {
		$aInt->gracefulExit($aInt->lang("support", "ticketnotfound"));
	}


	if ($access == "deptblocked") {
		$aInt->gracefulExit($aInt->lang("support", "deptnoaccess"));
	}


	if ($access == "flagged") {
		$aInt->gracefulExit($aInt->lang("support", "flagnoaccess") . ": " . getAdminName($flag));
	}


	if ($access) {
		exit();
	}


	if ($updateticket == "deptid") {
		$ticket->changeDept($value);
		exit();
	}


	if ($updateticket == "flagto") {
		$ticket->setFlagTo($value);
		exit();
	}


	if ($updateticket == "priority") {
		if (!in_array($value, array("High", "Medium", "Low"))) {
			exit();
		}

		update_query("tbltickets", array("urgency" => $value), array("id" => (int)$id));
		addTicketLog($id, "Priority changed to " . $value);
		exit();
	}


	if ($sub == "savecustomfields") {
		$customfields = getCustomFields("support", $deptid, $id, true);
		foreach ($customfields as $v) {
			$k = $v['id'];
			$customfieldsarray[$k] = $customfield[$k];
		}

		saveCustomFields($id, $customfieldsarray);
		$adminname = getAdminName();
		addTicketLog($id, "Custom Field Values Modified by " . $adminname);
	}

	AdminRead($id);

	if ($replyingadmin && $replyingadmin != $_SESSION['adminid']) {
		$result = select_query("tbladmins", "", array("id" => $replyingadmin));
		$data = mysql_fetch_array($result);
		$replyingadmin = ucfirst($data['username']);
		$smartyvalues['replyingadmin'] = array("name" => $replyingadmin, "time" => $replyingtime);
	}

	$clientname = $contactname = $clientgroupcolour = "";

	if ($pauserid) {
		$clientname = strip_tags($aInt->outputClientLink($pauserid));
	}


	if ($pacontactid) {
		$contactname = strip_tags($aInt->outputClientLink(array($pauserid, $pacontactid)));
	}

	$staffinvolved = array();
	$result = select_query("tblticketreplies", "DISTINCT admin", array("tid" => $id));

	while ($data = mysql_fetch_array($result)) {
		if (trim($data[0])) {
			$staffinvolved[] = $data[0];
		}
	}

	$addons_html = run_hook("AdminAreaViewTicketPage", array("ticketid" => $id));
	$smartyvalues['addons_html'] = $addons_html;
	$department = getDepartmentName($deptid);

	if (!$lastreply) {
		$lastreply = $date;
	}

	$date = fromMySQLDate($date, true);
	$outstatus = getStatusColour($tstatus);
	$aInt->Tabs();
	$tags = array();
	$result = select_query("tbltickettags", "tag", array("ticketid" => $id), "tag", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$tags[] = $data['tag'];
	}

	$smartyvalues['tags'] = $tags;
	$tags = json_encode($tags);
	$jsheadoutput = "<script type=\"text/javascript\">
var ticketid = '" . $id . "';
var userid = '" . $pauserid . "';
var ticketTags = " . $tags . ";
var langdelreplysure = \"" . $_ADMINLANG['support']['delreplysure'] . "\";
var langdelticketsure = \"" . $_ADMINLANG['support']['delticketsure'] . "\";
var langdelnotesure = \"" . $_ADMINLANG['support']['delnotesure'] . "\";
var langloading = \"" . $_ADMINLANG['global']['loading'] . "\";
var langstatuschanged = \"" . $_ADMINLANG['support']['statuschanged'] . "\";
var langstillsubmit = \"" . $_ADMINLANG['support']['stillsubmit'] . "\";
</script>
<script type=\"text/javascript\" src=\"../includes/jscript/admintickets.js\"></script>";
	$aInt->addHeadOutput($jsheadoutput);
	$smartyvalues['infobox'] = $infobox;
	$smartyvalues['ticketid'] = $id;
	$smartyvalues['deptid'] = $deptid;
	$smartyvalues['tid'] = $tid;
	$smartyvalues['subject'] = $title;
	$smartyvalues['status'] = $tstatus;
	$smartyvalues['userid'] = $pauserid;
	$smartyvalues['contactid'] = $pacontactid;
	$smartyvalues['clientname'] = $clientname;
	$smartyvalues['contactname'] = $contactname;
	$smartyvalues['clientgroupcolour'] = $clientgroupcolour;
	$smartyvalues['lastreply'] = getLastReplyTime($lastreply);
	$smartyvalues['priority'] = $urgency;
	$smartyvalues['flag'] = $flag;
	$smartyvalues['cc'] = $cc;
	$smartyvalues['staffinvolved'] = $staffinvolved;
	$smartyvalues['deleteperm'] = checkPermission("Delete Ticket", true);
	$result = select_query("tbladmins", "firstname,lastname,signature", array("id" => $_SESSION['adminid']));
	$data = mysql_fetch_array($result);
	$signature = $data['signature'];
	$smartyvalues['signature'] = $signature;
	$smartyvalues['predefinedreplies'] = genPredefinedRepliesList(0);
	$smartyvalues['clientnotes'] = array();
	$result = select_query("tblnotes", "tblnotes.*,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE tbladmins.id=tblnotes.adminid) AS adminuser", array("userid" => $pauserid, "sticky" => "1"), "modified", "DESC");

	while ($data = mysql_fetch_assoc($result)) {
		$data['created'] = fromMySQLDate($data['created'], 1);
		$data['modified'] = fromMySQLDate($data['modified'], 1);
		$data['note'] = autoHyperLink(nl2br($data['note']));
		$smartyvalues['clientnotes'][] = $data;
	}

	$notes = array();
	$result = select_query("tblticketnotes", "", array("ticketid" => $id), "date", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$notes[] = array("id" => $data['id'], "admin" => $data['admin'], "date" => fromMySQLDate($data['date'], true), "message" => ticketAutoHyperlinks($data['message']));
	}

	$smartyvalues['notes'] = $notes;
	$smartyvalues['numnotes'] = count($notes);
	$customfields = getCustomFields("support", $deptid, $id, true);
	$smartyvalues['customfields'] = $customfields;
	$smartyvalues['numcustomfields'] = count($customfields);
	$departmentshtml = "";
	$departments = array();
	$result = select_query("tblticketdepartments", "", "", "order", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$departments[] = array("id" => $data['id'], "name" => $data['name']);
		$departmentshtml .= "<option value=\"" . $data['id'] . "\"" . ($data['id'] == $deptid ? " selected" : "") . ">" . $data['name'] . "</option>";
	}

	$smartyvalues['departments'] = $departments;
	$staff = array();
	$result = select_query("tbladmins", "id,firstname,lastname,supportdepts", "disabled=0 OR id='" . (int)$flag . "'", "firstname` ASC,`lastname", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$staff[] = array("id" => $data['id'], "name" => $data['firstname'] . " " . $data['lastname']);
	}

	$smartyvalues['staff'] = $staff;
	$statuses = array();
	$result = select_query("tblticketstatuses", "", "", "sortorder", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$statuses[] = array("title" => $data['title'], "color" => $data['color'], "id" => $data['id']);
	}

	$smartyvalues['statuses'] = $statuses;

	if ($service) {
		switch (substr($service, 0, 1)) {
		case "S": {
				$result = select_query("tblhosting", "tblhosting.id,tblhosting.userid,tblhosting.regdate,tblhosting.domain,tblhosting.domainstatus,tblhosting.nextduedate,tblhosting.billingcycle,tblproducts.name,tblhosting.username,tblhosting.password,tblproducts.servertype", array("tblhosting.id" => substr($service, 1)), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");
				$data = mysql_fetch_array($result);
				$service_id = $data['id'];
				$service_userid = $data['userid'];
				$service_name = $data['name'];
				$service_domain = $data['domain'];
				$service_status = $data['domainstatus'];
				$service_regdate = $data['regdate'];
				$service_nextduedate = $data['nextduedate'];
				$service_username = $data['username'];
				$service_password = decrypt($data['password']);
				$service_servertype = $data['servertype'];

				if ($service_servertype) {
					if (!isValidforPath($service_servertype)) {
						exit("Invalid Server Module Name");
					}

					include "../modules/servers/" . $service_servertype . "/" . $service_servertype . ".php";

					if (function_exists($service_servertype . "_LoginLink")) {
						ob_start();
						ServerLoginLink($service_id);
						$service_loginlink = ob_get_contents();
						ob_end_clean();
					}
				}

				$smartyvalues['relatedproduct'] = array("id" => $service_id, "name" => $service_name, "regdate" => fromMySQLDate($service_regdate), "domain" => $service_domain, "nextduedate" => fromMySQLDate($service_nextduedate), "username" => $service_username, "password" => $service_password, "loginlink" => $service_loginlink, "status" => $service_status);
				break;
			}

		case "D": {
				$result = select_query("tbldomains", "", array("id" => substr($service, 1)));
				$data = mysql_fetch_array($result);
				$service_id = $data['id'];
				$service_userid = $data['userid'];
				$service_type = $data['type'];
				$service_domain = $data['domain'];
				$service_status = $data['status'];
				$service_nextduedate = $data['nextduedate'];
				$service_regperiod = $data['registrationperiod'];
				$service_registrar = $data['registrar'];
				$smartyvalues['relateddomain'] = array("id" => $service_id, "domain" => $service_domain, "nextduedate" => fromMySQLDate($service_nextduedate), "registrar" => ucfirst($service_registrar), "regperiod" => $service_regperiod, "ordertype" => $service_type, "status" => $service_status);
			}
		}
	}


	if ($pauserid && checkPermission("List Services", true)) {
		$currency = getCurrency($pauserid);
		$smartyvalues['relatedservices'] = array();
		$totalitems = get_query_val("tblhosting", "COUNT(id)", array("userid" => $pauserid)) + get_query_val("tblhostingaddons", "COUNT(tblhostingaddons.id)", array("tblhosting.userid" => $pauserid), "", "", "", "tblhosting ON tblhosting.id=tblhostingaddons.hostingid") + get_query_val("tbldomains", "COUNT(id)", array("userid" => $pauserid));
		$lefttoselect = 10;
		$result = select_query("tblhosting", "tblhosting.*,tblproducts.name", array("userid" => $pauserid), "domainstatus` ASC,`id", "DESC", "0," . $lefttoselect, "tblproducts ON tblproducts.id=tblhosting.packageid");

		while ($data = mysql_fetch_array($result)) {
			$service_id = $data['id'];
			$service_name = $data['name'];
			$service_domain = $data['domain'];
			$service_firstpaymentamount = $data['firstpaymentamount'];
			$service_recurringamount = $data['amount'];
			$service_billingcycle = $data['billingcycle'];
			$service_signupdate = $data['regdate'];
			$service_nextduedate = $data['nextduedate'];
			$service_status = $data['domainstatus'];
			$service_signupdate = fromMySQLDate($service_signupdate);

			if ($service_nextduedate == "0000-00-00") {
				$service_nextduedate = "-";
			}
			else {
				$service_nextduedate = fromMySQLDate($service_nextduedate);
			}


			if ($service_recurringamount <= 0) {
				$service_amount = $service_firstpaymentamount;
			}
			else {
				$service_amount = $service_recurringamount;
			}

			$service_amount = formatCurrency($service_amount);
			$selected = ((substr($service, 0, 1) == "S" && substr($service, 1) == $service_id) ? true : false);
			$smartyvalues['relatedservices'][] = array("id" => $service_id, "type" => "product", "name" => "<a href=\"clientsservices.php?userid=" . $pauserid . "&id=" . $service_id . "\" target=\"_blank\">" . $service_name . "</a> - <a href=\"http://" . $service_domain . "/\" target=\"_blank\">" . $service_domain . "</a>", "product" => $service_name, "domain" => $service_domain, "amount" => $service_amount, "billingcycle" => $service_billingcycle, "regdate" => $service_signupdate, "nextduedate" => $service_nextduedate, "status" => $service_status, "selected" => $selected);
		}

		$predefinedaddons = array();
		$result = select_query("tbladdons", "", "");

		while ($data = mysql_fetch_array($result)) {
			$addon_id = $data['id'];
			$addon_name = $data['name'];
			$predefinedaddons[$addon_id] = $addon_name;
		}

		$lefttoselect = 10 - count($smartyvalues['relatedservices']);

		if (0 < $lefttoselect) {
			$result = select_query("tblhostingaddons", "tblhostingaddons.*,tblhostingaddons.id AS addonid,tblhostingaddons.addonid AS addonid2,tblhostingaddons.name AS addonname,tblhosting.id AS hostingid,tblhosting.domain,tblproducts.name", array("tblhosting.userid" => $pauserid), "status` ASC,`tblhosting`.`id", "DESC", "0," . $lefttoselect, "tblhosting ON tblhosting.id=tblhostingaddons.hostingid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid");

			while ($data = mysql_fetch_array($result)) {
				$service_id = $data['id'];
				$hostingid = $data['hostingid'];
				$service_addonid = $data['addonid2'];
				$service_name = $data['name'];
				$service_addon = $data['addonname'];
				$service_domain = $data['domain'];
				$service_recurringamount = $data['recurring'];
				$service_billingcycle = $data['billingcycle'];
				$service_signupdate = $data['regdate'];
				$service_nextduedate = $data['nextduedate'];
				$service_status = $data['status'];

				if (!$service_addon) {
					$service_addon = $predefinedaddons[$service_addonid];
				}

				$service_signupdate = fromMySQLDate($service_signupdate);

				if ($service_nextduedate == "0000-00-00") {
					$service_nextduedate = "-";
				}
				else {
					$service_nextduedate = fromMySQLDate($service_nextduedate);
				}

				$service_amount = formatCurrency($service_recurringamount);
				$selected = ((substr($service, 0, 1) == "A" && substr($service, 1) == $service_id) ? true : false);
				$smartyvalues['relatedservices'][] = array("id" => $service_id, "type" => "addon", "serviceid" => $hostingid, "name" => $aInt->lang("orders", "addon") . (" - " . $service_addon . "<br /><a href=\"clientsservices.php?userid=" . $pauserid . "&id=" . $hostingid . "&aid=" . $service_id . "\" target=\"_blank\">" . $service_name . "</a> - <a href=\"http://" . $service_domain . "/\" target=\"_blank\">" . $service_domain . "</a>"), "product" => $service_addon, "domain" => $service_domain, "amount" => $service_amount, "billingcycle" => $service_billingcycle, "regdate" => $service_signupdate, "nextduedate" => $service_nextduedate, "status" => $service_status, "selected" => $selected);
			}
		}

		$lefttoselect = 10 - count($smartyvalues['relatedservices']);

		if (0 < $lefttoselect) {
			$result = select_query("tbldomains", "", array("userid" => $pauserid), "status` ASC,`id", "DESC", "0," . $lefttoselect);

			while ($data = mysql_fetch_array($result)) {
				$service_id = $data['id'];
				$service_domain = $data['domain'];
				$service_firstpaymentamount = $data['firstpaymentamount'];
				$service_recurringamount = $data['recurringamount'];
				$service_registrationperiod = $data['registrationperiod'] . " Year(s)";
				$service_signupdate = $data['registrationdate'];
				$service_nextduedate = $data['nextduedate'];
				$service_status = $data['status'];
				$service_signupdate = fromMySQLDate($service_signupdate);

				if ($service_nextduedate == "0000-00-00") {
					$service_nextduedate = "-";
				}
				else {
					$service_nextduedate = fromMySQLDate($service_nextduedate);
				}


				if ($service_recurringamount <= 0) {
					$service_amount = $service_firstpaymentamount;
				}
				else {
					$service_amount = $service_recurringamount;
				}

				$service_amount = formatCurrency($service_amount);
				$selected = ((substr($service, 0, 1) == "D" && substr($service, 1) == $service_id) ? true : false);
				$smartyvalues['relatedservices'][] = array("id" => $service_id, "type" => "domain", "name" => "<a href=\"clientsdomains.php?userid=" . $pauserid . "&id=" . $service_id . "\" target=\"_blank\">" . $aInt->lang("fields", "domain") . ("</a> - <a href=\"http://" . $service_domain . "/\" target=\"_blank\">" . $service_domain . "</a>"), "product" => $aInt->lang("fields", "domain"), "domain" => $service_domain, "amount" => $service_amount, "billingcycle" => $service_registrationperiod, "regdate" => $service_signupdate, "nextduedate" => $service_nextduedate, "status" => $service_status, "selected" => $selected);
			}
		}


		if (count($smartyvalues['relatedservices']) < $totalitems) {
			$smartyvalues['relatedservicesexpand'] = true;
		}
	}

	$jscode = "function insertKBLink(url) {
    $(\"#replymessage\").addToReply(url);
}";
	$aInt->jscode = $jscode;
	$jquerycode = "(function() {
    var fieldSelection = {
	    addToReply: function() {
		    var e = this.jquery ? this[0] : this;
		    var text = arguments[0] || '';
		    return (
			    ('selectionStart' in e && function() {
                    if (e.value==\"\\n\\n" . str_replace("\r\n", "\n", $signature) . "\") {
                        e.selectionStart=0;
                        e.selectionEnd=0;
                    }
                    e.value = e.value.substr(0, e.selectionStart) + text + e.value.substr(e.selectionEnd, e.value.length);
                    e.focus();
					return this;
				}) ||
				(document.selection && function() {
					e.focus();
					document.selection.createRange().text = text;
					return this;
				}) ||
				function() {
					e.value += text;
					return this;
				}
			)();
		}
	};
	jQuery.each(fieldSelection, function(i) { jQuery.fn[i] = this; });
    })();";
	$aInt->jquerycode = $jquerycode;
	$replies = array();
	$result = select_query("tbltickets", "userid,contactid,name,email,date,title,message,admin,attachment", array("id" => $id));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$contactid = $data['contactid'];
	$name = $data['name'];
	$email = $data['email'];
	$date = $data['date'];
	$title = $data['title'];
	$message = $data['message'];
	$admin = $data['admin'];
	$attachment = $data['attachment'];
	$friendlydate = (substr($date, 0, 10) == date("Y-m-d") ? "" : (substr($date, 0, 4) == date("Y") ? date("l jS F", strtotime($date)) : date("l jS F Y", strtotime($date))));
	$friendlytime = date("H:i", strtotime($date));
	$date = fromMySQLDate($date, true);
	$message = ticketMessageFormat($message);

	if ($userid) {
		$name = $aInt->outputClientLink(array($userid, $contactid));
	}

	$attachments = getTicketAttachmentsInfo($id, "", $attachment);
	$replies[] = array("id" => 0, "admin" => $admin, "userid" => $userid, "contactid" => $contactid, "clientname" => $name, "clientemail" => $email, "date" => $date, "friendlydate" => $friendlydate, "friendlytime" => $friendlytime, "message" => $message, "attachments" => $attachments, "numattachments" => count($attachments));
	$result = select_query("tblticketreplies", "", array("tid" => $id), "date", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$replyid = $data['id'];
		$userid = $data['userid'];
		$contactid = $data['contactid'];
		$name = $data['name'];
		$email = $data['email'];
		$date = $data['date'];
		$message = $data['message'];
		$attachment = $data['attachment'];
		$admin = $data['admin'];
		$rating = $data['rating'];
		$friendlydate = (substr($date, 0, 10) == date("Y-m-d") ? "" : (substr($date, 0, 4) == date("Y") ? date("l jS F", strtotime($date)) : date("l jS F Y", strtotime($date))));
		$friendlytime = date("H:i", strtotime($date));
		$date = fromMySQLDate($date, true);
		$message = ticketMessageFormat($message);

		if ($userid) {
			$name = $aInt->outputClientLink(array($userid, $contactid));
		}

		$attachments = getTicketAttachmentsInfo($id, $replyid, $attachment);
		$ratingstars = "";

		if ($admin && $rating) {
			$i = 8054;

			while ($i <= 5) {
				$ratingstars .= ($i <= $rating ? "<img src=\"../images/rating_pos.png\" align=\"absmiddle\">" : "<img src=\"../images/rating_neg.png\" align=\"absmiddle\">");
				++$i;
			}
		}

		$replies[] = array("id" => $replyid, "admin" => $admin, "userid" => $userid, "contactid" => $contactid, "clientname" => $name, "clientemail" => $email, "date" => $date, "friendlydate" => $friendlydate, "friendlytime" => $friendlytime, "message" => $message, "attachments" => $attachments, "numattachments" => count($attachments), "rating" => $ratingstars);
	}


	if ($CONFIG['SupportTicketOrder'] == "DESC") {
		krsort($replies);
	}

	$smartyvalues['replies'] = $replies;
	$smartyvalues['repliescount'] = count($replies);
	$smartyvalues['thumbnails'] = ($CONFIG['AttachmentThumbnails'] ? true : false);
	$splitticketdialog = $aInt->jqueryDialog("splitticket", $aInt->lang("support", "splitticketdialogtitle"), "<p>" . $aInt->lang("support", "splitticketdialoginfo") . "</p><table><tr><td align=\"right\" width=\"120\">" . $aInt->lang("support", "department") . (":</td><td><select id=\"splitdeptidx\">" . $departmentshtml . "</select></td></tr><tr><td align=\"right\">") . $aInt->lang("support", "splitticketdialognewticketname") . (":</td><td><input type=\"text\" id=\"splitsubjectx\" size=\"35\" value=\"" . $title . "\" /></td></tr><tr><td align=\"right\">") . $aInt->lang("support", "priority") . ":</td><td><select id=\"splitpriorityx\"><option value=\"High\"" . ($urgency == "High" ? " selected" : "") . ">High</option><option value=\"Medium\"" . ($urgency == "Medium" ? " selected" : "") . ">Medium</option><option value=\"Low\"" . ($urgency == "Low" ? " selected" : "") . ">Low</option></select></td></tr><tr><td align=\"right\">" . $aInt->lang("support", "splitticketdialognotifyclient") . ":</td><td><label><input type=\"checkbox\" id=\"splitnotifyclientx\" /> " . $aInt->lang("support", "splitticketdialognotifyclientinfo") . "</label></td></tr></table>", array($aInt->lang("global", "submit") => "$('#splitdeptid').val($('#splitdeptidx').val());$('#splitsubject').val($('#splitsubjectx').val());$('#splitpriority').val($('#splitpriorityx').val());$('#splitnotifyclient').val($('#splitnotifyclientx').attr('checked'));$('#ticketreplies').submit();", $aInt->lang("supportreq", "cancel") => ""), "", "400", "");
	$smartyvalues['splitticketdialog'] = $splitticketdialog;
}
else {
	if ($action == "open") {
		$result = select_query("tbladmins", "signature", array("id" => $_SESSION['adminid']));
		$data = mysql_fetch_array($result);
		$signature = $data['signature'];

		if ($errormessage != "") {
			infoBox($aInt->lang("global", "validationerror"), $errormessage);
			echo $infobox;
		}

		$jquerycode = "(function() {
    var fieldSelection = {
	    addToReply: function() {
		    var e = this.jquery ? this[0] : this;
		    var text = arguments[0] || '';
		    return (
			    ('selectionStart' in e && function() {
                    if (e.value==\"\\n\\n" . str_replace("\r\n", "\n", $signature) . "\") {
                        e.selectionStart=0;
                        e.selectionEnd=0;
                    }
                    e.value = e.value.substr(0, e.selectionStart) + text + e.value.substr(e.selectionEnd, e.value.length);
                    e.focus();
					return this;
				}) ||
				(document.selection && function() {
					e.focus();
					document.selection.createRange().text = text;
					return this;
				}) ||
				function() {
					e.value += text;
					return this;
				}
			)();
		}
	};
	jQuery.each(fieldSelection, function(i) { jQuery.fn[i] = this; });
    })();
$(\"#addfileupload\").click(function () {
    $(\"#fileuploads\").append(\"<input type=\\\"file\\\" name=\\\"attachments[]\\\" size=\\\"85\\\"><br />\");
    return false;
});
$(\"#clientsearchval\").keyup(function () {
	var ticketuseridsearchlength = $(\"#clientsearchval\").val().length;
	if (ticketuseridsearchlength>2) {
	$.post(\"search.php\", { ticketclientsearch: 1, value: $(\"#clientsearchval\").val() },
	    function(data){
            if (data) {
                $(\"#ticketclientsearchresults\").html(data);
                $(\"#ticketclientsearchresults\").slideDown(\"slow\");
                $(\"#clientsearchcancel\").fadeIn();
            }
        });
	}
});
$(\"#clientsearchcancel\").click(function () {
    $(\"#ticketclientsearchresults\").slideUp(\"slow\");
    $(\"#clientsearchcancel\").fadeOut();
});
$(\"#predefq\").keyup(function () {
    var intellisearchlength = $(\"#predefq\").val().length;
    if (intellisearchlength>2) {
    $.post(\"supporttickets.php\", { action: \"loadpredefinedreplies\", predefq: $(\"#predefq\").val() },
        function(data){
            $(\"#prerepliescontent\").html(data);
        });
    }
});
";
		$aInt->jquerycode = $jquerycode;
		$jscode = "function insertKBLink(url) {
    $(\"#replymessage\").addToReply(url);
}
function selectpredefcat(catid) {
    $.post(\"supporttickets.php\", { action: \"loadpredefinedreplies\", cat: catid },
    function(data){
        $(\"#prerepliescontent\").html(data);
    });
}
function loadpredef(catid) {
    $(\"#prerepliescontainer\").slideToggle();
    $(\"#prerepliescontent\").html('<img src=\\\"images/loading.gif\\\" align=\\\"top\\\" /> " . $aInt->lang("global", "loading") . "');
    $.post(\"supporttickets.php\", { action: \"loadpredefinedreplies\", cat: catid },
    function(data){
        $(\"#prerepliescontent\").html(data);
    });
}
function selectpredefreply(artid) {
    $.post(\"supporttickets.php\", { action: \"getpredefinedreply\", id: artid },
    function(data){
        $(\"#replymessage\").addToReply(data);
    });
    $(\"#prerepliescontainer\").slideToggle();
}
function searchselectclient(userid,name,email) {
    $(\"#clientsearchval\").val(\"\");
    $(\"#clientinput\").val(userid);
    $(\"#name\").val(name);
    $(\"#email\").val(email);
	$(\"#ticketclientsearchresults\").slideUp(\"slow\");
    $(\"#clientsearchcancel\").fadeOut();
    $.post(\"supporttickets.php\", { action: \"getcontacts\", userid: userid },
    function(data){
        if (data) {
            $(\"#contacthtml\").html(data);
            $(\"#contactrow\").show();
        } else {
            $(\"#contactrow\").hide();
        }
    });
}";
		$aInt->jscode = $jscode;

		if ($userid) {
			$result = select_query("tblclients", "id,firstname,lastname,companyname,email", array("id" => $userid));
			$data = mysql_fetch_array($result);
			$client = $data['id'];

			if ($client) {
				$name = $data['firstname'] . " " . $data['lastname'];

				if ($data['companyname']) {
					$name .= " (" . $data['companyname'] . ")";
				}

				$email = $data['email'];
			}
		}

		$contactsdata = "";

		if ($client) {
			$contactsdata = getTicketContacts($client);
		}

		echo "
<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?action=openticket\" enctype=\"multipart/form-data\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("emails", "to");
		echo "</td><td class=\"fieldarea\"><table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"500\"><input type=\"hidden\" name=\"client\" id=\"clientinput\" value=\"";
		echo $client;
		echo "\" /><input type=\"text\" name=\"name\" id=\"name\" size=\"40\" value=\"";
		echo $name;
		echo "\"></td><td>";
		echo $aInt->lang("clients", "search");
		echo ": <input type=\"text\" id=\"clientsearchval\" size=\"15\" /> <img src=\"images/icons/delete.png\" alt=\"Cancel\" class=\"absmiddle\" id=\"clientsearchcancel\" height=\"16\" width=\"16\"><br /><div id=\"ticketclientsearchresults\"></div></td></tr></table></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "email");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"email\" id=\"email\" size=\"50\" value=\"";
		echo $email;
		echo "\"> <label><input type=\"checkbox\" name=\"sendemail\" checked /> ";
		echo $aInt->lang("global", "sendemail");
		echo "</label></td></tr>
<tr id=\"contactrow\"";

		if (!$contactsdata) {
			echo " style=\"display:none;\"";
		}

		echo "><td class=\"fieldlabel\">";
		echo $aInt->lang("clientsummary", "contacts");
		echo "</td><td class=\"fieldarea\" id=\"contacthtml\">";
		echo $contactsdata;
		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "ccrecipients");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ccemail\" value=\"";
		echo $cc;
		echo "\" size=\"50\"> (";
		echo $aInt->lang("transactions", "commaseparated");
		echo ")</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "department");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"deptid\">";
		$result = select_query("tbladmins", "", array("id" => $_SESSION['adminid']));
		$data = mysql_fetch_array($result);
		$supportdepts = $data['supportdepts'];
		$supportdepts = explode(",", $supportdepts);
		$result = select_query("tblticketdepartments", "", "", "order", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$name = $data['name'];

			if (in_array($id, $supportdepts)) {
				echo "<option value=\"" . $id . "\"";

				if ($id == $department) {
					echo " selected";
				}

				echo ">" . $name . "</option>";
			}
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "subject");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"subject\" size=\"75\" value=\"";
		echo $subject;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "priority");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"priority\"><option>";
		echo $aInt->lang("status", "high");
		echo "<option selected>";
		echo $aInt->lang("status", "medium");
		echo "<option>";
		echo $aInt->lang("status", "low");
		echo "</select></td></tr>
</table>
<img src=\"images/spacer.gif\" height=\"8\" width=\"1\"><br>
<textarea name=\"message\" id=\"replymessage\" rows=20 style=\"width:100%\">";

		if ($message) {
			echo $message;
		}
		else {
			echo ((("\r\n") . "\r\n") . "\r\n") . $signature;
		}

		echo "</textarea><br>
<img src=\"images/spacer.gif\" height=\"8\" width=\"1\"><br>
<div id=\"insertlinks\" style=\"border:1px solid #DFDCCE;background-color:#F7F7F2;padding:5px;text-align:left;\">
<div align=\"center\"><a href=\"#\" onClick=\"window.open('supportticketskbarticle.php','kbartwnd','width=500,height=400,scrollbars=yes');return false\">";
		echo $aInt->lang("support", "insertkblink");
		echo "</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"#\" onclick=\"loadpredef('0');return false\">";
		echo $aInt->lang("support", "insertpredef");
		echo "</a></div>
</div>
<img src=\"images/spacer.gif\" height=\"8\" width=\"1\"><br>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("support", "attachments");
		echo "</td><td class=\"fieldarea\"><input type=\"file\" name=\"attachments[]\" size=\"85\"> <a href=\"#\" id=\"addfileupload\"><img src=\"images/icons/add.png\" align=\"absmiddle\" border=\"0\" /> ";
		echo $aInt->lang("support", "addmore");
		echo "</a><br /><div id=\"fileuploads\"></div></td></tr>
</table>
<div id=\"prerepliescontainer\" style=\"display:none;\">
    <img src=\"images/spacer.gif\" height=\"8\" width=\"1\" />
    <br />
    <div style=\"border:1px solid #DFDCCE;background-color:#F7F7F2;padding:5px;text-align:left;\">
        <div style=\"float:right;\">Search: <input type=\"text\" id=\"predefq\" size=\"25\" /></div>
        <div id=\"prerepliescontent\"></div>
    ";
		echo "</div>
</div>
<img src=\"images/spacer.gif\" height=\"8\" width=\"1\"><br>
<div align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("clientsummary", "openticket");
		echo "\" class=\"button\"></div>
</form>

";
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->templatevars = $smartyvalues;
$aInt->display();
?>