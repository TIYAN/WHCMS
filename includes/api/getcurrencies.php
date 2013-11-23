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

$result = select_query("tblcurrencies", "", "", "id", "ASC");
$apiresults = array("result" => "success", "totalresults" => mysql_num_rows($result));

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$code = $data['code'];
	$prefix = $data['prefix'];
	$suffix = $data['suffix'];
	$format = $data['format'];
	$rate = $data['rate'];
	$apiresults['currencies']['currency'][] = array("id" => $id, "code" => $code, "prefix" => $prefix, "suffix" => $suffix, "format" => $format, "rate" => $rate);
}

$responsetype = "xml";
?>