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


if (!function_exists("getRegistrarConfigOptions")) {
	require ROOTDIR . "/includes/registrarfunctions.php";
}


if (!function_exists("ModuleBuildParams")) {
	require ROOTDIR . "/includes/modulefunctions.php";
}


if (!function_exists("changeOrderStatus")) {
	require ROOTDIR . "/includes/orderfunctions.php";
}

$result = select_query("tblorders", "", array("id" => $orderid));
$data = mysql_fetch_array($result);
$orderid = $data['id'];

if (!$orderid) {
	$apiresults = array("result" => "error", "message" => "Order ID Not Found");
	return null;
}

changeOrderStatus($orderid, "Fraud");
$apiresults = array("result" => "success");
?>