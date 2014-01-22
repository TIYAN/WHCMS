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

$where = array();

if ($clientid) {
	$where['userid'] = $clientid;
}


if ($invoiceid) {
	$where['invoiceid'] = $invoiceid;
}


if ($transid) {
	$where['transid'] = $transid;
}

$result = select_query("tblaccounts", "", $where);
$apiresults = array("result" => "success", "totalresults" => mysql_num_rows($result), "startnumber" => 0, "numreturned" => mysql_num_rows($result));

while ($data = mysql_fetch_assoc($result)) {
	$apiresults['transactions']['transaction'][] = $data;
}

$responsetype = "xml";
?>