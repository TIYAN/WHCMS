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

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("getClientsDetails")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}


if (!function_exists("saveCustomFields")) {
	require ROOTDIR . "/includes/customfieldfunctions.php";
}


if (!isset($_REQUEST['projectid'])) {
	$apiresults = array("result" => "error", "message" => "Project ID Not SET");
	return null;
}


if (isset($_REQUEST['projectid'])) {
	$result = select_query("mod_project", "", array("id" => (int)$projectid));
	$data = mysql_fetch_assoc($result);
	$projectid = $data['id'];

	if (!$projectid) {
		$apiresults = array("result" => "error", "message" => "Project ID Not Found");
		return null;
	}
}


if (isset($_REQUEST['userid'])) {
	$result_userid = select_query("tblclients", "id", array("id" => $_REQUEST['userid']));
	$data_userid = mysql_fetch_array($result_userid);

	if (!$data_userid['id']) {
		$apiresults = array("result" => "error", "message" => "Client ID Not Found");
		return null;
	}
}


if (isset($_REQUEST['adminid'])) {
	$result_adminid = select_query("tbladmins", "id", array("id" => $_REQUEST['adminid']));
	$data_adminid = mysql_fetch_array($result_adminid);

	if (!$data_adminid['id']) {
		$apiresults = array("result" => "error", "message" => "Admin ID Not Found");
		return null;
	}
}


if (isset($_REQUEST['status'])) {
	$status_get = get_query_val("tbladdonmodules", "value", array("module" => "project_management", "setting" => "statusvalues"));
	$status_get = explode(",", $status_get);
	$status_main = (in_array($_REQUEST['status'], $status_get) ? $status_get : $status_get[0]);
}

$projectid = $_REQUEST['projectid'];
$title = (isset($_REQUEST['title']) ? trim($_REQUEST['title']) : "");
$adminid = $data_adminid['id'];
$userid = $data_user['id'];
$ticketids = $_REQUEST['ticketids'];
$invoiceids = $_REQUEST['invoiceids'];
$notes = $_REQUEST['notes'];
$status = $status_main;
$duedate = $_REQUEST['duedate'];
$completed = (isset($_REQUEST['completed']) ? 1 : 0);
$lastmodified = "now()";
$updateqry = array();

if ($projectid) {
	$updateqry['id'] = $projectid;
}


if ($title) {
	$updateqry['title'] = $title;
}


if ($adminid) {
	$updateqry['adminid'] = $adminid;
}


if ($userid) {
	$updateqry['userid'] = $userid;
}


if ($ticketids) {
	$updateqry['ticketids'] = $ticketids;
}


if ($invoiceid) {
	$updateqry['invoiceids'] = $invoiceids;
}


if ($notes) {
	$updateqry['notes'] = $notes;
}


if ($status) {
	$updateqry['status'] = $status;
}


if ($duedate) {
	$updateqry['duedate'] = $duedate;
}


if ($completed) {
	$updateqry['completed'] = $completed;
}

$updateqry['lastmodified'] = $lastmodified;
update_query("mod_project", $updateqry, array("id" => $projectid));
$apiresults = array("result" => "success", "message" => "Project Has Been Updated");
?>