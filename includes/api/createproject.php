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

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (isset($_REQUEST['userid'])) {
	$result_userid = select_query("tblclients", "id", array("id" => $_REQUEST['userid']));
	$data_userid = mysql_fetch_array($result_userid);

	if (!$data_userid['id']) {
		$apiresults = array("result" => "error", "message" => "Client ID Not Found");
		return null;
	}
}


if (!isset($_REQUEST['adminid'])) {
	$apiresults = array("result" => "error", "message" => "Admin ID not Set");
	return null;
}


if (isset($_REQUEST['adminid'])) {
	$result_adminid = select_query("tbladmins", "id", array("id" => $_REQUEST['adminid']));
	$data_adminid = mysql_fetch_array($result_adminid);

	if (!$data_adminid['id']) {
		$apiresults = array("result" => "error", "message" => "Admin ID Not Found");
		return null;
	}
}


if (!trim($_REQUEST['title'])) {
	$apiresults = array("result" => "error", "message" => "Project Title is Required.");
	return null;
}


if (isset($_REQUEST['status'])) {
	$status = get_query_val("tbladdonmodules", "value", array("module" => "project_management", "setting" => "statusvalues"));
	$status_get = explode(",", $status);
	$status_main = (in_array($_REQUEST['status'], $status_get) ? $status_get : $status_get[0]);
}

$created = (!isset($_REQUEST['created']) ? date("Y-m-d") : $_REQUEST['created']);
$duedate = (!isset($_REQUEST['duedate']) ? date("Y-m-d") : $_REQUEST['duedate']);
$completed = (isset($_REQUEST['completed']) ? 1 : 0);
$projectid = insert_query("mod_project", array("userid" => $_REQUEST['userid'], "title" => $_REQUEST['title'], "ticketids" => $_REQUEST['ticketids'], "invoiceids" => $_REQUEST['invoiceids'], "notes" => $_REQUEST['notes'], "adminid" => $_REQUEST['adminid'], "status" => $status_main, "created" => $created, "duedate" => $duedate, "completed" => $completed, "lastmodified" => "now()"));
$apiresults = array("result" => "success", "message" => "Project has been created");
?>