<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("AddReply")) {
	require ROOTDIR . "/includes/ticketfunctions.php";
}


if ($ticketnum) {
	$result = select_query("tbltickets", "", array("tid" => $ticketnum));
}
else {
	$result = select_query("tbltickets", "", array("id" => $ticketid));
}

$data = mysql_fetch_array($result);
$id = $data['id'];
$tid = $data['tid'];
$deptid = $data['did'];
$userid = $data['userid'];
$name = $data['name'];
$email = $data['email'];
$cc = $data['cc'];
$c = $data['c'];
$date = $data['date'];
$subject = $data['title'];
$message = $data['message'];
$status = $data['status'];
$priority = $data['urgency'];
$admin = $data['admin'];
$attachment = $data['attachment'];
$lastreply = $data['lastreply'];
$flag = $data['flag'];
$service = $data['service'];
$message = strip_tags($message);

if (!$id) {
	$apiresults = array("result" => "error", "message" => "Ticket ID Not Found");
	return null;
}


if ($userid) {
	$result2 = select_query("tblclients", "", array("id" => $userid));
	$data = mysql_fetch_array($result2);
	$name = $data['firstname'] . " " . $data['lastname'];

	if ($data['companyname']) {
		$name .= " (" . $data['companyname'] . ")";
	}

	$email = $data['email'];
}

$apiresults = array("result" => "success", "ticketid" => $id, "tid" => $tid, "c" => $c, "deptid" => $deptid, "deptname" => getDepartmentName($deptid), "userid" => $userid, "name" => $name, "email" => $email, "cc" => $cc, "date" => $date, "subject" => $subject, "status" => $status, "priority" => $priority, "admin" => $admin, "lastreply" => $lastreply, "flag" => $flag, "service" => $service);
$first_reply = array("userid" => $userid, "name" => $name, "email" => $email, "date" => $date, "message" => $message, "attachment" => $attachment, "admin" => $admin);
$sortorder = ($_REQUEST['repliessort'] ? $_REQUEST['repliessort'] : "ASC");

if ($sortorder == "ASC") {
	$apiresults['replies']['reply'][] = $first_reply;
}

$result = select_query("tblticketreplies", "", array("tid" => $id), "id", $sortorder);

while ($data = mysql_fetch_array($result)) {
	$userid = $data['userid'];
	$name = $data['name'];
	$email = $data['email'];
	$date = $data['date'];
	$message = $data['message'];
	$attachment = $data['attachment'];
	$admin = $data['admin'];
	$rating = $data['rating'];
	$message = strip_tags($message);

	if ($userid) {
		$result2 = select_query("tblclients", "", array("id" => $userid));
		$data = mysql_fetch_array($result2);
		$name = $data['firstname'] . " " . $data['lastname'];

		if ($data['companyname']) {
			$name .= " (" . $data['companyname'] . ")";
		}

		$email = $data['email'];
	}

	$apiresults['replies']['reply'][] = array("userid" => $userid, "name" => $name, "email" => $email, "date" => $date, "message" => $message, "attachment" => $attachment, "admin" => $admin, "rating" => $rating);
}


if ($sortorder != "ASC") {
	$apiresults['replies']['reply'][] = $first_reply;
}

$apiresults['notes'] = "";
$result = select_query("tblticketnotes", "", array("ticketid" => $id), "id", "ASC");

while ($data = mysql_fetch_array($result)) {
	$noteid = $data['id'];
	$admin = $data['admin'];
	$date = $data['date'];
	$message = $data['message'];
	$apiresults['notes']['note'][] = array("noteid" => $noteid, "date" => $date, "message" => $message, "admin" => $admin);
}

$responsetype = "xml";
?>