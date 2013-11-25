<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("ServerSuspendAccount")) {
	require ROOTDIR . "/includes/modulefunctions.php";
}

$result = select_query("tblhosting", "packageid", array("id" => $_POST['accountid']));
$data = mysql_fetch_array($result);
$packageid = $data['packageid'];
$result = ServerSuspendAccount($_POST['accountid'], $_POST['suspendreason']);

if ($result == "success") {
	$apiresults = array("result" => "success");
	return 1;
}

$apiresults = array("result" => "error", "message" => $result);
?>