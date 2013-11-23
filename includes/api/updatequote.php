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


if (!function_exists("addTransaction")) {
	require ROOTDIR . "/includes/invoicefunctions.php";
}


if (!function_exists("saveQuote")) {
	require ROOTDIR . "/includes/quotefunctions.php";
}

$result = select_query("tblquotes", "", array("id" => $quoteid));
$data = mysql_fetch_array($result);
$quoteid = $data['id'];

if (!$quoteid) {
	$apiresults = array("result" => "error", "message" => "Quote ID Not Found");
	return null;
}

$stage = (is_null($stage) ? $data['stage'] : $stage);
$stagearray = array("Draft", "Delivered", "On Hold", "Accepted", "Lost", "Dead");

if ($stage && !in_array($stage, $stagearray)) {
	$apiresults = array("result" => "error", "message" => "Invalid Stage");
	return null;
}

$subject = (is_null($subject) ? $data['subject'] : $subject);
$validuntil = (is_null($validuntil) ? fromMySQLDate($data['validuntil']) : fromMySQLDate($validuntil));
$userid = (is_null($userid) ? $data['userid'] : $userid);

if (!$userid) {
	$clienttype = "new";
	$firstname = (is_null($firstname) ? $data['firstname'] : $firstname);
	$lastname = (is_null($lastname) ? $data['lastname'] : $lastname);
	$companyname = (is_null($companyname) ? $data['companyname'] : $companyname);
	$email = (is_null($email) ? $data['email'] : $email);
	$address1 = (is_null($address1) ? $data['address1'] : $address1);
	$address2 = (is_null($address2) ? $data['address2'] : $address2);
	$city = (is_null($city) ? $data['city'] : $city);
	$state = (is_null($state) ? $data['state'] : $state);
	$postcode = (is_null($postcode) ? $data['postcode'] : $postcode);
	$country = (is_null($country) ? $data['country'] : $country);
	$phonenumber = (is_null($phonenumber) ? $data['phonenumber'] : $phonenumber);
	$currency = (is_null($currency) ? $data['currency'] : $currency);
}

$proposal = (is_null($proposal) ? $data['proposal'] : $proposal);
$customernotes = (is_null($customernotes) ? $data['customernotes'] : $customernotes);
$adminnotes = (is_null($adminnotes) ? $data['adminnotes'] : $adminnotes);
$datecreated = fromMySQLDate($data['datecreated']);

if ($lineitems) {
	$lineitems = base64_decode($lineitems);
	$lineitemsarray = unserialize($lineitems);
}

saveQuote($quoteid, $subject, $stage, $datecreated, $validuntil, $clienttype, $userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $currency, $lineitemsarray, $proposal, $customernotes, $adminnotes);
$apiresults = array("result" => "success");
?>