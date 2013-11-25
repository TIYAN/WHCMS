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

$where = array();

if ($type) {
	$where['type'] = $type;
}


if ($language) {
	$where['language'] = $language;
}

$result = select_query("tblemailtemplates", "", $where, "name", "ASC");
$apiresults = array("result" => "success", "totalresults" => mysql_num_rows($result));

while ($data = mysql_fetch_array($result)) {
	$apiresults['emailtemplates']['emailtemplate'][] = array("id" => $data['id'], "name" => $data['name'], "subject" => $data['subject'], "custom" => $data['custom']);
}

$responsetype = "xml";
?>