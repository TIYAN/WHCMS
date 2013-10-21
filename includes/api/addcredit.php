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

$result = select_query("tblclients", "id", array("id" => $clientid));
$data = mysql_fetch_array($result);

if (!$data['id']) {
	$apiresults = array("result" => "error", "message" => "Client ID Not Found");
	return 1;
}

insert_query("tblcredit", array("clientid" => $clientid, "date" => "now()", "description" => $description, "amount" => $amount));
full_query("UPDATE tblclients SET credit=credit+" . db_escape_string($amount) . " WHERE id='" . db_escape_string($clientid) . "'");
$currency = getCurrency($clientid);
logActivity("Added Credit - User ID: " . $clientid . " - Amount: " . formatCurrency($amount), $clientid);
$result = select_query("tblclients", "", array("id" => $clientid));
$data = mysql_fetch_array($result);
$creditbalance = $data['credit'];
$apiresults = array("result" => "success", "newbalance" => $creditbalance);
?>