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


if (!function_exists("RegSaveNameservers")) {
	require ROOTDIR . "/includes/registrarfunctions.php";
}


if ($domainid) {
	$where = array("id" => $domainid);
}
else {
	$where = array("domain" => $domain);
}

$result = select_query("tbldomains", "id,domain,registrar,registrationperiod", $where);
$data = mysql_fetch_array($result);
$domainid = $data[0];

if (!$domainid) {
	$apiresults = array("result" => "error", "message" => "Domain ID Not Found");
	return false;
}


if (!($ns1 && $ns2)) {
	$apiresults = array("result" => "error", "message" => "ns1 and ns2 required");
	return false;
}

$domain = $data['domain'];
$registrar = $data['registrar'];
$regperiod = $data['registrationperiod'];
$domainparts = explode(".", $domain, 2);
$params = array();
$params['domainid'] = $domainid;
$params['sld'] = $domainparts[0];
$params['tld'] = $domainparts[1];
$params['regperiod'] = $regperiod;
$params['registrar'] = $registrar;
$params['ns1'] = $ns1;
$params['ns2'] = $ns2;
$params['ns3'] = $ns3;
$params['ns4'] = $ns4;
$params['ns5'] = $ns5;
$values = RegSaveNameservers($params);

if ($values['error']) {
	$apiresults = array("result" => "error", "message" => "Registrar Error Message", "error" => $values['error']);
	return false;
}


if (!$values) {
	$values = array();
}

$apiresults = array_merge(array("result" => "success"), $values);
?>