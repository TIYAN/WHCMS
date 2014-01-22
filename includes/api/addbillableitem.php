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

$result = select_query("tblclients", "", array("id" => $clientid));
$data = mysql_fetch_array($result);
$clientid = $data['id'];

if (!$clientid) {
	$apiresults = array("result" => "error", "message" => "Client ID not Found");
	return null;
}


if (!$description) {
	$apiresults = array("result" => "error", "message" => "You must provide a description");
	return null;
}

$allowedtypes = array("noinvoice", "nextcron", "nextinvoice", "duedate", "recur");

if ($invoiceaction && !in_array($invoiceaction, $allowedtypes)) {
	$apiresults = array("result" => "error", "message" => "Invalid Invoice Action");
	return null;
}


if ($invoiceaction == "recur" && ((!$recur && !$recurcycle) || !$recurfor)) {
	$apiresults = array("result" => "error", "message" => "Recurring must have a unit, cycle and limit");
	return null;
}


if ($invoiceaction == "duedate" && !$duedate) {
	$apiresults = array("result" => "error", "message" => "Due date is required");
	return null;
}


if ($invoiceaction == "noinvoice") {
	$invoiceaction = "0";
}
else {
	if ($invoiceaction == "nextcron") {
		$invoiceaction = "1";

		if (!$duedate) {
			$duedate = date("Y-m-d");
		}
	}
	else {
		if ($invoiceaction == "nextinvoice") {
			$invoiceaction = "2";
		}
		else {
			if ($invoiceaction == "duedate") {
				$invoiceaction = "3";
			}
			else {
				if ($invoiceaction == "recur") {
					$invoiceaction = "4";
				}
			}
		}
	}
}

$id = insert_query("tblbillableitems", array("userid" => $clientid, "description" => $description, "hours" => $hours, "amount" => $amount, "recur" => $recur, "recurcycle" => $recurcycle, "recurfor" => $recurfor, "invoiceaction" => $invoiceaction, "duedate" => $duedate));
$apiresults = array("result" => "success", "billableid" => $id);
?>