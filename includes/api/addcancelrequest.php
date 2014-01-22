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


if (!function_exists("updateInvoiceTotal")) {
	require ROOTDIR . "/includes/invoicefunctions.php";
}


if (!function_exists("createCancellationRequest")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}

$result = select_query("tblhosting", "id,userid", array("id" => $serviceid));
$data = mysql_fetch_array($result);
$serviceid = $data[0];
$userid = $data[1];

if (!$serviceid) {
	$apiresults = array("result" => "error", "message" => "Service ID Not Found");
	return false;
}

$validtypes = array("Immediate", "End of Billing Period");

if (!in_array($type, $validtypes)) {
	$type = "End of Billing Period";
}


if (!$reason) {
	$reason = "None Specified (API Submission)";
}

$result = createCancellationRequest($userid, $serviceid, $reason, $type);

if ($result == "success") {
	$apiresults = array("result" => "success", "serviceid" => $serviceid, "userid" => $userid);
	return 1;
}

$apiresults = array("result" => "error", "message" => $result, "serviceid" => $serviceid, "userid" => $userid);
?>