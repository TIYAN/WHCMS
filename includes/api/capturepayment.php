<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("captureCCPayment")) {
	require ROOTDIR . "/includes/ccfunctions.php";
}


if (!function_exists("getClientsDetails")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}


if (!function_exists("processPaidInvoice")) {
	require ROOTDIR . "/includes/invoicefunctions.php";
}

$result = select_query("tblinvoices", "id", array("id" => $invoiceid, "status" => "Unpaid"));
$data = mysql_fetch_array($result);
$invoiceid = $data['id'];

if (!$invoiceid) {
	$apiresults = array("result" => "error", "message" => "Invoice Not Found or Not Unpaid");
	return 1;
}

$result = captureCCPayment($invoiceid, $cvv);

if ($result) {
	$apiresults = array("result" => "success");
	return 1;
}

$apiresults = array("result" => "error", "message" => "Payment Attempt Failed");
?>