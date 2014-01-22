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


if (!function_exists("addContact")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}

$result = select_query("tblclients", "id", array("id" => $clientid));
$data = mysql_fetch_array($result);

if (!$data[0]) {
	$apiresults = array("result" => "error", "message" => "Client ID Not Found");
	return null;
}

$permissions = $permissions ? explode(",", $permissions) : array();

if (count($permissions)) {
	$result = select_query("tblclients", "id", array("email" => $email));
	$data = mysql_fetch_array($result);
	$result = select_query("tblcontacts", "id", array("email" => $email, "subaccount" => "1"));
	$data2 = mysql_fetch_array($result);

	if ($data['id'] || $data2['id']) {
		$apiresults = array("result" => "error", "message" => "Duplicate Email Address");
		return null;
	}
}


if ($generalemails) {
	$generalemails = "1";
}


if ($productemails) {
	$productemails = "1";
}


if ($domainemails) {
	$domainemails = "1";
}


if ($invoiceemails) {
	$invoiceemails = "1";
}


if ($supportemails) {
	$supportemails = "1";
}

$contactid = addContact($clientid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password2, $permissions, $generalemails, $productemails, $domainemails, $invoiceemails, $supportemails);
$apiresults = array("result" => "success", "contactid" => $contactid);
?>