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


if (!function_exists("getTLDPriceList")) {
	require ROOTDIR . "/includes/domainfunctions.php";
}


if (!function_exists("recalcPromoAmount")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}


if ($domainid) {
	$where = array("id" => $domainid);
}
else {
	$where = array("domain" => $domain);
}

$result = select_query("tbldomains", "id", $where);
$data = mysql_fetch_array($result);
$domainid = $data['id'];

if (!$domainid) {
	$apiresults = array("result" => "error", "message" => "Domain ID Not Found");
	return null;
}


if ($type) {
	$updateqry['type'] = $type;
}


if ($regdate) {
	$updateqry['registrationdate'] = $regdate;
}


if ($domain) {
	$updateqry['domain'] = $domain;
}


if ($firstpaymentamount) {
	$updateqry['firstpaymentamount'] = $firstpaymentamount;
}


if ($recurringamount) {
	$updateqry['recurringamount'] = $recurringamount;
}


if ($registrar) {
	$updateqry['registrar'] = $registrar;
}


if ($regperiod) {
	$updateqry['registrationperiod'] = $regperiod;
}


if ($expirydate) {
	$updateqry['expirydate'] = $expirydate;
}


if ($nextduedate) {
	$updateqry['nextduedate'] = $nextduedate;
	$updateqry['nextinvoicedate'] = $nextduedate;
}


if ($paymentmethod) {
	$updateqry['paymentmethod'] = $paymentmethod;
}


if ($subscriptionid) {
	$updateqry['subscriptionid'] = $subscriptionid;
}


if ($status) {
	$updateqry['status'] = $status;
}


if ($notes) {
	$updateqry['additionalnotes'] = $notes;
}


if ($dnsmanagement) {
	$updateqry['dnsmanagement'] = $dnsmanagement;
}


if ($emailforwarding) {
	$updateqry['emailforwarding'] = $emailforwarding;
}


if ($idprotection) {
	$updateqry['idprotection'] = $idprotection;
}


if ($donotrenew) {
	$updateqry['donotrenew'] = $donotrenew;
}


if ($promoid) {
	$updateqry['promoid'] = $promoid;
}

update_query("tbldomains", $updateqry, array("id" => $domainid));

if ($autorecalc) {
	$updateqry = "";

	if ($domainid) {
		$where = array("id" => $domainid);
	}
	else {
		$where = array("domain" => $domain);
	}

	$result = select_query("tbldomains", "id,userid,domain,registrationperiod,dnsmanagement,emailforwarding,idprotection", $where);
	$data = mysql_fetch_assoc($result);
	$domainid = $data['id'];
	$domain = $data['domain'];
	$userid = $data['userid'];
	$regperiod = $data['registrationperiod'];
	$dnsmanagement = $data['dnsmanagement'];
	$emailforwarding = $data['emailforwarding'];
	$idprotection = $data['idprotection'];
	$domainparts = explode(".", $domain, 2);
	$temppricelist = getTLDPriceList("." . $domainparts[1], "", true, $userid);
	$recurringamount = $temppricelist[$regperiod]['renew'];
	$result = select_query("tblpricing", "msetupfee,qsetupfee,ssetupfee", array("type" => "domainaddons", "currency" => $currency['id'], "relid" => 0));
	$data = mysql_fetch_array($result);
	$domaindnsmanagementprice = $data['msetupfee'] * $regperiod;
	$domainemailforwardingprice = $data['qsetupfee'] * $regperiod;
	$domainidprotectionprice = $data['ssetupfee'] * $regperiod;

	if ($dnsmanagement) {
		$recurringamount += $domaindnsmanagementprice;
	}


	if ($emailforwarding) {
		$recurringamount += $domainemailforwardingprice;
	}


	if ($idprotection) {
		$recurringamount += $domainidprotectionprice;
	}


	if ($promoid) {
		$recurringamount -= recalcPromoAmount("D." . $domainparts[1], $userid, $domainid, $regperiod . "Years", $recurringamount, $promoid);
	}

	$updateqry['recurringamount'] = $recurringamount;
	update_query("tbldomains", $updateqry, array("id" => $domainid));
}

$apiresults = array("result" => "success", "domainid" => $domainid);

if ($updatens) {
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
}

?>