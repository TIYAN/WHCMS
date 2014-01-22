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


if (!function_exists("closeClient")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}

$result = select_query("tblclients", "id", array("id" => $clientid));
$data = mysql_fetch_array($result);

if (!$data['id']) {
	$apiresults = array("result" => "error", "message" => "Client ID Not Found");
	return 1;
}

closeClient($_REQUEST['clientid']);
$apiresults = array("result" => "success", "clientid" => $_REQUEST['clientid']);
?>