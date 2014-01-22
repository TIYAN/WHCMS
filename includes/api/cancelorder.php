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


if (!function_exists("getRegistrarConfigOptions")) {
	require ROOTDIR . "/includes/registrarfunctions.php";
}


if (!function_exists("ModuleBuildParams")) {
	require ROOTDIR . "/includes/modulefunctions.php";
}


if (!function_exists("changeOrderStatus")) {
	require ROOTDIR . "/includes/orderfunctions.php";
}

$result = select_query("tblorders", "", array("id" => $orderid, "status" => "Pending"));
$data = mysql_fetch_array($result);
$orderid = $data['id'];

if (!$orderid) {
	$apiresults = array("result" => "error", "message" => "Order ID not found or Status not Pending");
	return null;
}

changeOrderStatus($orderid, "Cancelled");
$apiresults = array("result" => "success");
?>