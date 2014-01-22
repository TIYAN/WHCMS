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


if (!function_exists("getClientsDetails")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}


if (!function_exists("saveCustomFields")) {
	require ROOTDIR . "/includes/customfieldfunctions.php";
}


if (!isset($_REQUEST['projectid'])) {
	$apiresults = array("result" => "error", "message" => "Project ID is Required");
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


if (!isset($_REQUEST['adminid'])) {
	$_REQUEST['adminid'] = $_SESSION['adminid'];
}


if (isset($_REQUEST['adminid'])) {
	$result_adminid = select_query("tbladmins", "id", array("id" => $_REQUEST['adminid']));
	$data_adminid = mysql_fetch_array($result_adminid);

	if (!$data_adminid['id']) {
		$apiresults = array("result" => "error", "message" => "Admin ID Not Found");
		return null;
	}
}


if (!isset($_REQUEST['taskid'])) {
	$apiresults = array("result" => "error", "message" => "Task ID Not Set");
	return null;
}


if (isset($_REQUEST['taskid'])) {
	$result_taskid = select_query("mod_projecttasks", "id", array("id" => $_REQUEST['taskid']));
	$data_taskid = mysql_fetch_array($result_taskid);

	if (!$data_taskid['id']) {
		$apiresults = array("result" => "error", "message" => "Task ID Not Found");
		return null;
	}
}

$projectid = $_REQUEST['projectid'];
$adminid = (int)$_REQUEST['adminid'];
$taskid = (int)$_REQUEST['taskid'];
$start_time = (isset($_REQUEST['start_time']) ? $_REQUEST['start_time'] : time());
$endtime = $_REQUEST['end_time'];
$apply = insert_query("mod_projecttimes", array("projectid" => $projectid, "adminid" => $adminid, "taskid" => $taskid, "start" => $start_time, "end" => $endtime));
$apiresults = array("result" => "success", "message" => "Start Timer Has Been Set");
?>