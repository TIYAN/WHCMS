<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("addClient")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}


if (!function_exists("updateInvoiceTotal")) {
	require ROOTDIR . "/includes/invoicefunctions.php";
}


if (!function_exists("saveQuote")) {
	require ROOTDIR . "/includes/quotefunctions.php";
}


if (!$subject) {
	$apiresults = array("result" => "error", "message" => "Subject is required");
	return null;
}

$stagearray = array("Draft", "Delivered", "On Hold", "Accepted", "Lost", "Dead");

if (!in_array($stage, $stagearray)) {
	$apiresults = array("result" => "error", "message" => "Invalid Stage");
	return null;
}


if (!$validuntil) {
	$apiresults = array("result" => "error", "message" => "Valid Until is required");
	return null;
}


if (!$datecreated) {
	$datecreated = date("Y-m-d");
}


if ($lineitems) {
	$lineitems = base64_decode($lineitems);
	$lineitemsarray = unserialize($lineitems);
}


if (!$userid) {
	$clienttype = "new";
}

$newquoteid = saveQuote("", $subject, $stage, $datecreated, $validuntil, $clienttype, $userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $currency, $lineitemsarray, $proposal, $customernotes, $adminnotes);
$apiresults = array("result" => "success", "quoteid" => $newquoteid);
?>