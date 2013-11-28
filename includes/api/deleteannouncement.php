<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}

$result = select_query("tblannouncements", "id", array("id" => $announcementid));
$data = mysql_fetch_array($result);

if (!$data['id']) {
	$apiresults = array("result" => "error", "message" => "Announcement ID Not Found");
	return false;
}

delete_query("tblannouncements", array("id" => $announcementid));
delete_query("tblannouncements", array("parentid" => $announcementid));
$apiresults = array("result" => "success", "announcementid" => $announcementid);
?>