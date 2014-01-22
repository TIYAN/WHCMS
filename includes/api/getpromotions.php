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

$where = "";

if ($code) {
	$where['code'] = $code;
}

$result = select_query("tblpromotions", "", $where, "code", "ASC");
$apiresults = array("result" => "success", "totalresults" => mysql_num_rows($result));

while ($data = mysql_fetch_assoc($result)) {
	$apiresults['promotions']['promotion'][] = $data;
}

$responsetype = "xml";
?>