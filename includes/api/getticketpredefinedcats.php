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

$result = select_query("tblticketpredefinedcats", "COUNT(id)");
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$apiresults = array("result" => "success", "totalresults" => $totalresults);
$result = full_query("SELECT c.*, COUNT(r.id) AS replycount FROM tblticketpredefinedcats c LEFT JOIN tblticketpredefinedreplies r ON c.id=r.catid GROUP BY c.id ORDER BY c.name ASC");

while ($data = mysql_fetch_assoc($result)) {
	$apiresults['predefinedreplies']['predefinedreply'][] = array("id" => $data['id'], "parentid" => $data['parentid'], "name" => $data['name'], "replycount" => $data['replycount']);
	$apiresults['categories']['category'][] = $data;
}

$responsetype = "xml";
?>