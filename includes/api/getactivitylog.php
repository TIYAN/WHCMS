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


if (!function_exists("getClientsDetails")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}


if (!$limitstart) {
	$limitstart = 0;
}


if (!$limitnum) {
	$limitnum = 25;
}

$result = select_query("tblactivitylog", "COUNT(id)", "");
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$apiresults = array("result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart);
$result = select_query("tblactivitylog", "id, date, description, user", "", "id", "DESC", "" . $limitstart . "," . $limitnum);
$apiresults['numreturned'] = mysql_num_rows($result);

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$date = $data['date'];
	$description = $data['description'];
	$user = $data['user'];
	$apiresults['activity']['entry'][] = array("id" => $id, "date" => $date, "description" => $description, "user" => $user);
}

$responsetype = "xml";
?>