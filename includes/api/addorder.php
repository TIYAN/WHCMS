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


if (!function_exists("addClient")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}


if (!function_exists("getCartConfigOptions")) {
	require ROOTDIR . "/includes/configoptionsfunctions.php";
}


if (!function_exists("checkDomainisValid")) {
	require ROOTDIR . "/includes/domainfunctions.php";
}


if (!function_exists("updateInvoiceTotal")) {
	require ROOTDIR . "/includes/invoicefunctions.php";
}


if (!function_exists("createInvoices")) {
	require ROOTDIR . "/includes/processinvoices.php";
}


if (!function_exists("calcCartTotals")) {
	require ROOTDIR . "/includes/orderfunctions.php";
}


if (!function_exists("ModuleBuildParams")) {
	require ROOTDIR . "/includes/modulefunctions.php";
}


if (!function_exists("cartPreventDuplicateProduct")) {
	require ROOTDIR . "/includes/cartfunctions.php";
}


if ($promocode && !$promooverride) {
	define("CLIENTAREA", true);
}

$result = select_query("tblclients", "id", array("id" => $_POST['clientid']));
$data = mysql_fetch_array($result);

if (!$data['id']) {
	$apiresults = array("result" => "error", "message" => "Client ID Not Found");
	return null;
}

$gatewaysarray = array();
$result = select_query("tblpaymentgateways", "gateway", array("setting" => "name"));

while ($data = mysql_fetch_array($result)) {
	$gatewaysarray[] = $data['gateway'];
}


if (!in_array($paymentmethod, $gatewaysarray)) {
	$apiresults = array("result" => "error", "message" => "Invalid Payment Method. Valid options include " . implode(",", $gatewaysarray));
	return null;
}


if ($clientip) {
	$remote_ip = $clientip;
}

$_SESSION['uid'] = $_POST['clientid'];
global $currency;

$currency = getCurrency($_POST['clientid']);
$_SESSION['cart'] = array();

if (is_array($pid)) {
	foreach ($pid as $i => $prodid) {

		if ($prodid) {
			$proddomain = $domain[$i];
			$prodbillingcycle = $billingcycle[$i];
			$configoptionsarray = array();
			$customfieldsarray = array();
			$domainfieldsarray = array();
			$addonsarray = array();

			if ($addons[$i]) {
				$addonsarray = explode(",", $addons[$i]);
			}


			if ($configoptions[$i]) {
				$configoptionsarray = unserialize(base64_decode($configoptions[$i]));
			}


			if ($customfields[$i]) {
				$customfieldsarray = unserialize(base64_decode($customfields[$i]));
			}

			$productarray = array("pid" => $prodid, "domain" => $proddomain, "billingcycle" => $prodbillingcycle, "server" => (((($hostname[$i] || $ns1prefix[$i]) || $ns2prefix[$i]) || $rootpw[$i]) ? array("hostname" => $hostname[$i], "ns1prefix" => $ns1prefix[$i], "ns2prefix" => $ns2prefix[$i], "rootpw" => $rootpw[$i]) : ""), "configoptions" => $configoptionsarray, "customfields" => $customfieldsarray, "addons" => $addonsarray);

			if (strlen($priceoverride[$i])) {
				$productarray['priceoverride'] = $priceoverride[$i];
			}

			$_SESSION['cart']['products'][] = $productarray;
			continue;
		}
	}
}
else {
	if ($pid) {
		$configoptionsarray = array();
		$customfieldsarray = array();
		$domainfieldsarray = array();
		$addonsarray = array();

		if ($addons) {
			$addonsarray = explode(",", $addons);
		}


		if ($configoptions) {
			$configoptions = base64_decode($configoptions);
			$configoptionsarray = unserialize($configoptions);
		}


		if ($customfields) {
			$customfields = base64_decode($customfields);
			$customfieldsarray = unserialize($customfields);
		}

		$productarray = array("pid" => $pid, "domain" => $domain, "billingcycle" => $billingcycle, "server" => (((($hostname || $ns1prefix) || $ns2prefix) || $rootpw) ? array("hostname" => $hostname, "ns1prefix" => $ns1prefix, "ns2prefix" => $ns2prefix, "rootpw" => $rootpw) : ""), "configoptions" => $configoptionsarray, "customfields" => $customfieldsarray, "addons" => $addonsarray);

		if (strlen($priceoverride)) {
			$productarray['priceoverride'] = $priceoverride;
		}

		$_SESSION['cart']['products'][] = $productarray;
	}
}


if (is_array($domaintype)) {
	foreach ($domaintype as $i => $type) {

		if ($type) {
			if ($domainfields[$i]) {
				$domainfields[$i] = base64_decode($domainfields[$i]);
				$domainfieldsarray[$i] = unserialize($domainfields[$i]);
			}

			$_SESSION['cart']['domains'][] = array("type" => $type, "domain" => $domain[$i], "regperiod" => $regperiod[$i], "dnsmanagement" => $dnsmanagement[$i], "emailforwarding" => $emailforwarding[$i], "idprotection" => $idprotection[$i], "eppcode" => $eppcode[$i], "fields" => $domainfieldsarray[$i]);
			continue;
		}
	}
}
else {
	if ($domaintype) {
		if ($domainfields) {
			$domainfields = base64_decode($domainfields);
			$domainfieldsarray = unserialize($domainfields);
		}

		$_SESSION['cart']['domains'][] = array("type" => $domaintype, "domain" => $domain, "regperiod" => $regperiod, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection, "eppcode" => $eppcode, "fields" => $domainfieldsarray);
	}
}


if ($addonid) {
	$_SESSION['cart']['addons'][] = array("id" => $addonid, "productid" => $serviceid);
}


if ($addonids) {
	foreach ($addonids as $i => $addonid) {
		$_SESSION['cart']['addons'][] = array("id" => $addonid, "productid" => $serviceids[$i]);
	}
}


if ($domainrenewals) {
	foreach ($domainrenewals as $domain => $regperiod) {
		$result = select_query("tbldomains", "id", array("userid" => $_SESSION['uid'], "domain" => $domain));
		$data = mysql_fetch_array($result);
		$domainid = $data[0];

		if ($domainid) {
			$_SESSION['cart']['renewals'][$domainid] = $regperiod;
			continue;
		}
	}
}

$cartitems = count($_SESSION['cart']['products']) + count($_SESSION['cart']['addons']) + count($_SESSION['cart']['domains']) + count($_SESSION['cart']['renewals']);

if (!$cartitems) {
	$apiresults = array("result" => "error", "message" => "No items added to cart so order cannot proceed");
	return null;
}

$_SESSION['cart']['ns1'] = $nameserver1;
$_SESSION['cart']['ns2'] = $nameserver2;
$_SESSION['cart']['ns3'] = $nameserver3;
$_SESSION['cart']['ns4'] = $nameserver4;
$_SESSION['cart']['paymentmethod'] = $paymentmethod;
$_SESSION['cart']['promo'] = $promocode;
$_SESSION['cart']['notes'] = $notes;

if ($contactid) {
	$_SESSION['cart']['contact'] = $contactid;
}


if ($noinvoice) {
	$_SESSION['cart']['geninvoicedisabled'] = true;
}


if ($noinvoiceemail) {
	$CONFIG['NoInvoiceEmailOnOrder'] = true;
}


if ($noemail) {
	$_SESSION['cart']['orderconfdisabled'] = true;
}

$cartdata = calcCartTotals(true);

if (($affid && is_array($_SESSION['orderdetails']['Products'])) && $_SESSION['uid'] != $affid) {
	foreach ($_SESSION['orderdetails']['Products'] as $productid) {
		insert_query("tblaffiliatesaccounts", array("affiliateid" => $affid, "relid" => $productid));
	}
}

$productids = $addonids = $domainids = "";

if (is_array($_SESSION['orderdetails']['Products'])) {
	$productids = implode(",", $_SESSION['orderdetails']['Products']);
}


if (is_array($_SESSION['orderdetails']['Addons'])) {
	$addonids = implode(",", $_SESSION['orderdetails']['Addons']);
}


if (is_array($_SESSION['orderdetails']['Domains'])) {
	$domainids = implode(",", $_SESSION['orderdetails']['Domains']);
}

$apiresults = array("result" => "success", "orderid" => $_SESSION['orderdetails']['OrderID'], "productids" => $productids, "addonids" => $addonids, "domainids" => $domainids);

if (!$noinvoice) {
	$apiresults['invoiceid'] = ($_SESSION['orderdetails']['InvoiceID'] ? $_SESSION['orderdetails']['InvoiceID'] : get_query_val("tblorders", "invoiceid", array("id" => $_SESSION['orderdetails']['OrderID'])));
}

?>