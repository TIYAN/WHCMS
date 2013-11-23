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


if (!$limitstart) {
	$limitstart = 0;
}


if (!$limitnum) {
	$limitnum = 25;
}

$where = array();

if ($status) {
	$where['status'] = $status;
}

$result = select_query("tbltodolist", "COUNT(id)", $where);
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$result = select_query("tbltodolist", "", $where, "duedate", "DESC", "" . $limitstart . "," . $limitnum);
$apiresults = array("result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => mysql_num_rows($result));

while ($data = mysql_fetch_assoc($result)) {
	$data['title'] = $data['title'];
	$data['description'] = strip_tags($data['description']);
	$apiresults['items']['item'][] = $data;
}

$responsetype = "xml";
?>