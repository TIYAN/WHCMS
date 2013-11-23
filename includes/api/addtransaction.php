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


if (!function_exists("addTransaction")) {
	require ROOTDIR . "/includes/invoicefunctions.php";
}


if ($userid) {
	$result = select_query("tblclients", "id", array("id" => $userid));
	$data = mysql_fetch_array($result);

	if (!$data['id']) {
		$apiresults = array("result" => "error", "message" => "Client ID Not Found");
		return null;
	}
}


if ($invoiceid = (int)$_POST['invoiceid']) {
	$query = "SELECT * FROM tblinvoices WHERE id='" . $invoiceid . "'";
	$result = full_query($query);
	$data = mysql_fetch_array($result);

	if (!$data['id']) {
		$apiresults = array("result" => "error", "message" => "Invoice ID Not Found");
		return null;
	}
}


if (!$paymentmethod) {
	$apiresults = array("result" => "error", "message" => "Payment Method is required");
	return null;
}

addTransaction($userid, $currencyid, $description, $amountin, $fees, $amountout, $paymentmethod, $transid, $invoiceid, $date, "", $rate);

if ($userid && $credit) {
	if ($transid) {
		$description .= " (Trans ID: " . $transid . ")";
	}

	insert_query("tblcredit", array("clientid" => $userid, "date" => toMySQLDate($date), "description" => $description, "amount" => $amountin));
	$query = "UPDATE tblclients SET credit=credit+" . mysql_real_escape_string($amountin) . " WHERE id='" . mysql_real_escape_string($userid) . "'";
	full_query($query);
}

$apiresults = array("result" => "success");
?>