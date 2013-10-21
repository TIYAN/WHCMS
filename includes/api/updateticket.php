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

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("closeTicket")) {
	require ROOTDIR . "/includes/ticketfunctions.php";
}

$result = select_query("tbltickets", "id", array("id" => $ticketid));
$data = mysql_fetch_array($result);

if (!$data['id']) {
	$apiresults = array("result" => "error", "message" => "Ticket ID Not Found");
	return null;
}

$updateqry = array();

if ($deptid) {
	$updateqry['did'] = $deptid;
}


if ($userid) {
	$updateqry['userid'] = $userid;
}


if ($name) {
	$updateqry['name'] = $name;
}


if ($email) {
	$updateqry['email'] = $email;
}


if ($cc) {
	$updateqry['cc'] = $cc;
}


if ($subject) {
	$updateqry['title'] = $subject;
}


if ($priority) {
	$updateqry['urgency'] = $priority;
}


if ($status && $status != "Closed") {
	$updateqry['status'] = $status;
}


if ($status == "Closed") {
	closeTicket($ticketid);
}


if ($flag) {
	$updateqry['flag'] = $flag;
}

update_query("tbltickets", $updateqry, array("id" => $ticketid));
$apiresults = array("result" => "success", "ticketid" => $ticketid);
?>