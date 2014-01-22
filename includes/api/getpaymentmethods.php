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


if (!function_exists("getGatewaysArray")) {
	require ROOTDIR . "/includes/gatewayfunctions.php";
}

$paymentmethods = getGatewaysArray();
$apiresults = array("result" => "success", "totalresults" => count($paymentmethods));
foreach ($paymentmethods as $module => $name) {
	$apiresults['paymentmethods']['paymentmethod'][] = array("module" => $module, "displayname" => $name);
}

$responsetype = "xml";
?>