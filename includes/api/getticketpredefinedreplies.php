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

$result = select_query("tblticketpredefinedreplies", "COUNT(id)", $where);
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$apiresults = array("result" => "success", "totalresults" => $totalresults);
$result = select_query("tblticketpredefinedreplies", "name,reply", array("catid" => $catid), "name", "ASC");

while ($data = mysql_fetch_assoc($result)) {
	$apiresults['predefinedreplies']['predefinedreply'][] = array("name" => $data['name'], "reply" => $data['reply']);
}

$responsetype = "xml";
?>