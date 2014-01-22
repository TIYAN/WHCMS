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


if (!$limitstart) {
	$limitstart = 0;
}


if (!$limitnum) {
	$limitnum = 25;
}

$search = mysql_real_escape_string($search);
$result = full_query("SELECT SQL_CALC_FOUND_ROWS id, firstname, lastname, companyname, email, groupid, datecreated, status FROM tblclients WHERE email LIKE '" . $search . "%' OR firstname LIKE '" . $search . "%' OR lastname LIKE '" . $search . "%' OR companyname LIKE '" . $search . "%' ORDER BY firstname, lastname, companyname LIMIT " . (int)$limitstart . ", " . (int)$limitnum);
$result_count = full_query("SELECT FOUND_ROWS()");
$data = mysql_fetch_array($result_count);
$totalresults = $data[0];
$apiresults = array("result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => mysql_num_rows($result));

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$firstname = $data['firstname'];
	$lastname = $data['lastname'];
	$companyname = $data['companyname'];
	$email = $data['email'];
	$groupid = $data['groupid'];
	$datecreated = $data['datecreated'];
	$status = $data['status'];
	$apiresults['clients']['client'][] = array("id" => $id, "firstname" => $firstname, "lastname" => $lastname, "companyname" => $companyname, "email" => $email, "datecreated" => $datecreated, "groupid" => $groupid, "status" => $status);
}

$responsetype = "xml";
?>