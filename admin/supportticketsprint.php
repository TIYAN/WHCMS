<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
require "../includes/customfieldfunctions.php";
$aInt = new WHMCS_Admin("List Support Tickets");
$aInt->requiredFiles(array("ticketfunctions"));
$result = select_query("tbltickets", "", array("id" => $id));
$data = mysql_fetch_array($result);
$id = $data['id'];
$tid = $data['tid'];
$deptid = $data['did'];
$pauserid = $data['userid'];
$name = $data['name'];
$email = $data['email'];
$date = $data['date'];
$title = $data['title'];
$message = $data['message'];
$tstatus = $data['status'];
$attachment = $data['attachment'];
$urgency = $data['urgency'];
$lastreply = $data['lastreply'];
$flag = $data['flag'];
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

$message = strip_tags($message);
$message = nl2br($message);
$message = ticketAutoHyperlinks($message);

if ($pauserid != "0000000000") {
	$result = select_query("tblclients", "", array("id" => $pauserid));
	$data = mysql_fetch_array($result);
	$firstname = $data['firstname'];
	$lastname = $data['lastname'];
	$clientinfo = "<a href=\"clientsprofile.php?userid=" . $puserid . "\">" . $firstname . " " . $lastname . "</a>";
}
else {
	$clientinfo = $aInt->lang("support", "notregclient");
}

$department = getDepartmentName($deptid);

if ($lastreply == "") {
	$lastreply = $date;
}

$date = fromMySQLDate($date, "time");
$lastreply = fromMySQLDate($lastreply, "time");
$outstatus = getStatusColour($tstatus);
ob_start();
echo "
<p><b>";
echo $title;
echo "</b></p>

<p><b><i>";
echo $aInt->lang("support", "ticketid");
echo ":</i></b> ";
echo $tid;
echo "<br>
<b><i>";
echo $aInt->lang("support", "department");
echo ":</i></b> ";
echo $department;
echo "<br>
<b><i>";
echo $aInt->lang("support", "createdate");
echo ":</i></b> ";
echo $date;
echo "<br>
<b><i>";
echo $aInt->lang("support", "lastreply");
echo ":</i></b> ";
echo $lastreply;
echo "<br>
<b><i>";
echo $aInt->lang("fields", "status");
echo ":</i></b> ";
echo $outstatus;
echo "<br>
<b><i>";
echo $aInt->lang("support", "priority");
echo ":</i></b> ";
echo $urgency;
echo "</p>
<hr size=1><p>
";
$customfields = getCustomFields("support", $deptid, $id, true);
foreach ($customfields as $customfield) {
	echo "<b><i>" . $customfield['name'] . ":</i></b> " . $customfield['value'] . "<br>";
}

echo "</p><hr size=1>

";

if ($pauserid != "0000000000") {
	$result2 = select_query("tblclients", "", array("id" => $pauserid));
	$data2 = mysql_fetch_array($result2);
	$firstname = $data2['firstname'];
	$lastname = $data2['lastname'];
	$clientinfo = "<b>" . $firstname . " " . $lastname . "</b>";
}
else {
	$clientinfo = "<b>" . $name . "</b> (" . $email . ")";
}

echo "" . $clientinfo . " @ " . $date . "<br><hr size=1><br>" . stripslashes($message) . "<hr size=1>";
$result = select_query("tblticketreplies", "", array("tid" => $id), "date", "ASC");

while ($data = mysql_fetch_array($result)) {
	$ids = $data['id'];
	$puserid = $data['userid'];
	$name = $data['name'];
	$email = $data['email'];
	$date = $data['date'];
	$date = fromMySQLDate($date, "time");
	$message = $data['message'];
	$attachment = $data['attachment'];
	$admin = $data['admin'];
	$message = strip_tags($message);
	$message = nl2br($message);
	$message = ticketAutoHyperlinks($message);

	if ($admin) {
		$clientinfo = "<b>" . $admin . "</b>";
	}
	else {
		if ($puserid != "0000000000") {
			$result2 = select_query("tblclients", "", array("id" => $pauserid));
			$data2 = mysql_fetch_array($result2);
			$firstname = $data2['firstname'];
			$lastname = $data2['lastname'];
			$clientinfo = "<B>" . $firstname . " " . $lastname . "</B>";
		}
		else {
			$clientinfo = "<B>" . $name . "</B><br><a href=\"mailto:" . $email . "\">" . $email . "</a>";
		}
	}

	echo "" . $clientinfo . " @ " . $date . "<br><hr size=1><br>" . $message . "<br><br><hr size=1>";
}

echo "<p align=center style=\"font-size:10px;\">" . $aInt->lang("support", "outputgenby") . " WHMCompleteSolution (www.whmcs.com)</p>";
ob_get_contents();
$content = $aInt->title = $aInt->lang("support", "printticketversion");
ob_end_clean();
$aInt->content = $content;
$aInt->displayPopUp();
?>