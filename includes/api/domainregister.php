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


if (!function_exists("RegRegisterDomain")) {
	require ROOTDIR . "/includes/registrarfunctions.php";
}


if ($domainid) {
	$result = select_query("tbldomains", "id", array("id" => $domainid));
}
else {
	$result = select_query("tbldomains", "id", array("domain" => $domain));
}

$data = mysql_fetch_array($result);
$domainid = $data[0];

if (!$domainid) {
	$apiresults = array("result" => "error", "message" => "Domain Not Found");
	return false;
}

$params = array("domainid" => $domainid);
$values = RegRegisterDomain($params);

if ($values['error']) {
	$apiresults = array("result" => "error", "message" => "Registrar Error Message", "error" => $values['error']);
	return false;
}

$apiresults = array_merge(array("result" => "success"), $values);
?>