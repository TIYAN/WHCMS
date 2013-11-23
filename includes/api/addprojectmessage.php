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


if (!function_exists("getClientsDetails")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}


if (!function_exists("saveCustomFields")) {
	require ROOTDIR . "/includes/customfieldfunctions.php";
}


if (!isset($_REQUEST['projectid'])) {
	$apiresults = array("result" => "error", "message" => "Project ID not Set");
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


if (!isset($_REQUEST['message'])) {
	$apiresults = array("result" => "error", "message" => "Message not Entered");
	return null;
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

$projectid = $_REQUEST['projectid'];
$adminid = $_REQUEST['adminid'];
$message = $_REQUEST['message'];
$date = "now()";
$apply = insert_query("mod_projectmessages", array("projectid" => $projectid, "adminid" => $adminid, "message" => $message, "date" => $date));
$apiresults = array("result" => "success", "message" => "Message has been added");
?>