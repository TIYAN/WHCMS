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


if (!function_exists("saveCustomFields")) {
	require ROOTDIR . "/includes/customfieldfunctions.php";
}


if (!function_exists("openNewTicket")) {
	require ROOTDIR . "/includes/ticketfunctions.php";
}

$from = "";

if ($clientid) {
	$result = select_query("tblclients", "id", array("id" => $clientid));
	$data = mysql_fetch_array($result);

	if (!$data['id']) {
		$apiresults = array("result" => "error", "message" => "Client ID Not Found");
		return null;
	}


	if ($contactid) {
		$result = select_query("tblcontacts", "id", array("id" => $contactid, "userid" => $clientid));
		$data = mysql_fetch_array($result);

		if (!$data['id']) {
			$apiresults = array("result" => "error", "message" => "Contact ID Not Found");
			return null;
		}
	}
}
else {
	if (!$name || !$email) {
		$apiresults = array("result" => "error", "message" => "Name and email address are required if not a client");
		return null;
	}

	$from = array("name" => $name, "email" => $email);
}

$result = select_query("tblticketdepartments", "", array("id" => $deptid));
$data = mysql_fetch_array($result);
$deptid = $data['id'];

if (!$deptid) {
	$apiresults = array("result" => "error", "message" => "Department ID not found");
	return null;
}


if (!$subject) {
	$apiresults = array("result" => "error", "message" => "Subject is required");
	return null;
}


if (!$message) {
	$apiresults = array("result" => "error", "message" => "Message is required");
	return null;
}


if (!$priority || !in_array($priority, array("Low", "Medium", "High"))) {
	$priority = "Low";
}


if ($serviceid) {
	if (is_numeric($serviceid) || substr($serviceid, 0, 1) == "S") {
		$result = select_query("tblhosting", "id", array("id" => $serviceid, "userid" => $clientid));
		$data = mysql_fetch_array($result);

		if (!$data['id']) {
			$apiresults = array("result" => "error", "message" => "Service ID Not Found");
			return null;
		}

		$serviceid = "S" . $data['id'];
	}
	else {
		$serviceid = substr($serviceid, 1);
		$result = select_query("tbldomains", "id", array("id" => $serviceid, "userid" => $clientid));
		$data = mysql_fetch_array($result);

		if (!$data['id']) {
			$apiresults = array("result" => "error", "message" => "Service ID Not Found");
			return null;
		}

		$serviceid = "D" . $data['id'];
	}
}


if ($domainid) {
	$result = select_query("tbldomains", "id", array("id" => $domainid, "userid" => $clientid));
	$data = mysql_fetch_array($result);

	if (!$data['id']) {
		$apiresults = array("result" => "error", "message" => "Domain ID Not Found");
		return null;
	}

	$serviceid = "D" . $data['id'];
}

$ticketdata = openNewTicket($clientid, $contactid, $deptid, $subject, $message, $priority, "", $from, $serviceid, $cc, $noemail);

if ($customfields) {
	$customfields = base64_decode($customfields);
	$customfields = unserialize($customfields);
	saveCustomFields($ticketdata['ID'], $customfields);
}

$apiresults = array("result" => "success", "id" => $ticketdata['ID'], "tid" => $ticketdata['TID'], "c" => $ticketdata['C']);
?>