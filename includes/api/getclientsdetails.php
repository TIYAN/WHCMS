<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("getClientsDetails")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}

$where = array();

if ($clientid) {
	$where['id'] = $clientid;
}
else {
	if ($email) {
		$where['email'] = $email;
	}
}

$result = select_query("tblclients", "id", $where);
$data = mysql_fetch_array($result);
$clientid = $data['id'];

if (!$clientid) {
	$apiresults = array("result" => "error", "message" => "Client Not Found");
	return null;
}

$clientsdetails = getClientsDetails($clientid);
$currency_result = full_query("SELECT code FROM tblcurrencies WHERE id=" . (int)$clientsdetails['currency']);
$currency = mysql_fetch_assoc($currency_result);
$clientsdetails['currency_code'] = $currency['code'];

if ($responsetype == "xml") {
	$apiresults = array("result" => "success", "client" => $clientsdetails);
}
else {
	$apiresults = array_merge(array("result" => "success"), $clientsdetails);
}


if ($stats || $responsetype == "xml") {
	$apiresults = array("result" => "success", "client" => $clientsdetails, "stats" => getClientsStats($clientid));
}

?>