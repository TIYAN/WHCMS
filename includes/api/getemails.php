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

$result = select_query("tblclients", "id", array("id" => $clientid));
$data = mysql_fetch_array($result);
$clientid = $data[0];

if (!$clientid) {
	$apiresults = array("status" => "error", "message" => "Client ID Not Found");
	return null;
}


if (!$limitstart) {
	$limitstart = 0;
}


if (!$limitnum) {
	$limitnum = 25;
}

$where = array();
$where['userid'] = $clientid;

if ($date) {
	$where['date'] = array("sqltype" => "LIKE", "value" => $date);
}


if ($subject) {
	$where['subject'] = array("sqltype" => "LIKE", "value" => $subject);
}

$result = select_query("tblemails", "COUNT(*)", $where);
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$result = select_query("tblemails", "", $where, "id", "DESC", "" . $limitstart . "," . $limitnum);
$apiresults = array("result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => mysql_num_rows($result));

while ($data = mysql_fetch_assoc($result)) {
	$apiresults['emails']['email'][] = $data;
}

$responsetype = "xml";
?>