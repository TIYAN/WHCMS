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

$result = select_query("tblclients", "id", array("id" => $clientid));
$data = mysql_fetch_array($result);
$clientid = $data['id'];

if (!$clientid) {
	$apiresults = array("result" => "error", "message" => "Client ID Not Found");
	return null;
}

$credits = array();
$result = select_query("tblcredit", "id,date,description,amount,relid", array("clientid" => $clientid), "date", "ASC");

while ($data = mysql_fetch_assoc($result)) {
	$credits[] = $data;
}

$apiresults = array("result" => "success", "totalresults" => count($credits), "clientid" => $clientid, "credits" => array("credit" => $credits));
$responsetype = "xml";
?>