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


if (!function_exists("getAdminName")) {
	require ROOTDIR . "/includes/adminfunctions.php";
}


if (!function_exists("AddNote")) {
	require ROOTDIR . "/includes/ticketfunctions.php";
}


if ($ticketnum) {
	$result = select_query("tbltickets", "id", array("tid" => $ticketnum));
}
else {
	$result = select_query("tbltickets", "id", array("id" => $ticketid));
}

$data = mysql_fetch_array($result);
$ticketid = $data['id'];

if (!$ticketid) {
	$apiresults = array("result" => "error", "message" => "Ticket ID not found");
	return null;
}

AddNote($ticketid, $message);
$apiresults = array("result" => "success");
?>