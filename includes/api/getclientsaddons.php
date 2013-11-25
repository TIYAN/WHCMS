<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}

global $currency;

$currency = getCurrency();
$where = array();

if ($serviceid) {
	if (is_numeric($serviceid)) {
		$where[] = "hostingid=" . (int)$serviceid;
	}
	else {
		$serviceids = explode(",", $serviceid);
		$where[] = "hostingid IN (" . implode("','", array_map("db_escape_string", $serviceids)) . ")";
	}
}


if ($clientid) {
	$result = select_query("tblhosting", "", array("userid" => $clientid));
	$hostingids = array();

	while ($data = mysql_fetch_array($result)) {
		$hostingids[] = $data['id'];
	}

	$where[] = "hostingid IN (" . implode("','", array_map("db_escape_string", $hostingids)) . ")";
}


if ($addonid) {
	$where[] = "addonid=" . (int)$addonid;
}

$result = select_query("tblhostingaddons", "", implode(" AND ", $where));
$apiresults = array("result" => "success", "serviceid" => $serviceid, "clientid" => $clientid, "totalresults" => mysql_num_rows($result));

while ($data = mysql_fetch_array($result)) {
	$aid = $data['id'];
	$addonarray = array("id" => $data['id'], "userid" => get_query_val("tblhosting", "userid", array("id" => $data['hostingid'])), "orderid" => $data['orderid'], "serviceid" => $data['hostingid'], "addonid" => $data['addonid'], "name" => $data['name'], "setupfee" => $data['setupfee'], "recurring" => $data['recurring'], "billingcycle" => $data['billingcycle'], "tax" => $data['tax'], "status" => $data['status'], "regdate" => $data['regdate'], "nextduedate" => $data['nextduedate'], "nextinvoicedate" => $data['nextinvoicedate'], "paymentmethod" => $data['paymentmethod'], "notes" => $data['notes']);
	$apiresults['addons']['addon'][] = $addonarray;
}

$responsetype = "xml";
?>