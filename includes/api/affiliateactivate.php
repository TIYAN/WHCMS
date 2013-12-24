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


if (!function_exists("getAdminName")) {
	require ROOTDIR . "/includes/adminfunctions.php";
}


if (!function_exists("affiliateActivate")) {
	require ROOTDIR . "/includes/affiliatefunctions.php";
}

$result = select_query("tblclients", "id", array("id" => $userid));
$data = mysql_fetch_array($result);
$userid = $data['id'];

if (!$userid) {
	$apiresults = array("result" => "error", "message" => "Client ID not found");
	return null;
}

affiliateActivate($userid);
$apiresults = array("result" => "success");
?>