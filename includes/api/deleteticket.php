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

$result = select_query("tbltickets", "", array("id" => $ticketid));
$data = mysql_fetch_array($result);
$ticketid = $data['id'];

if (!$ticketid) {
	$apiresults = array("result" => "error", "message" => "Ticket ID not found");
	return null;
}


if (!function_exists("deleteTicket")) {
	require ROOTDIR . "/includes/ticketfunctions.php";
}

deleteTicket($ticketid);
$apiresults = array("result" => "success");
?>