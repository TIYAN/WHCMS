<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("calcCartTotals")) {
	require ROOTDIR . "/includes/orderfunctions.php";
}


if (!function_exists("checkDetailsareValid")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}


if (!function_exists("saveCustomFields")) {
	require ROOTDIR . "/includes/customfieldfunctions.php";
}


if ($clientip) {
	$remote_ip = $clientip;
}

$errormessage = checkDetailsareValid();

if ($errormessage && !$skipvalidation) {
	$errormessage = explode("<li>", $errormessage);
	$error = $errormessage[1];
	$apiresults = array("result" => "error", "message" => $error);
	return 1;
}

$_SESSION['currency'] = $currency;
$sendemail = ($noemail ? false : true);
$langatstart = $_SESSION['Language'];

if ($language) {
	$_SESSION['Language'] = $language;
}

addClient($firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password2, $securityqid, $securityqans, $sendemail);

if ($_POST['cctype']) {
	if (!function_exists("updateCCDetails")) {
		require ROOTDIR . "/includes/ccfunctions.php";
	}

	updateCCDetails($_SESSION['uid'], $_POST['cctype'], $_POST['cardnum'], $_POST['expdate'], $_POST['startdate'], $_POST['issuenumber']);
}

$updateqry = array();

if ($groupid) {
	$updateqry['groupid'] = $groupid;
}


if ($notes) {
	$updateqry['notes'] = $notes;
}


if (count($updateqry)) {
	update_query("tblclients", $updateqry, array("id" => $_SESSION['uid']));
}


if ($customfields) {
	$customfields = base64_decode($customfields);
	$customfields = unserialize($customfields);
	saveCustomFields($_SESSION['uid'], $customfields);
}

$apiresults = array("result" => "success", "clientid" => $_SESSION['uid']);
$_SESSION['Language'] = $langatstart;
?>