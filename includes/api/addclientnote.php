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

$userid = get_query_val("tblclients", "id", array("id" => $userid));

if (!$userid) {
	$apiresults = array("result" => "error", "message" => "Client ID not found");
	return null;
}


if (!$notes) {
	$apiresults = array("result" => "error", "message" => "Notes can not be empty");
	return null;
}

$sticky = $sticky ? 1 : 0;
$noteid = insert_query("tblnotes", array("userid" => $userid, "adminid" => $_SESSION['adminid'], "created" => "now()", "modified" => "now()", "note" => nl2br($notes), "sticky" => $sticky));
$apiresults = array("result" => "success", "noteid" => $noteid);
?>