<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("SumUpPackageUpgradeOrder")) {
	require ROOTDIR . "/includes/upgradefunctions.php";
}


if (!function_exists("addTransaction")) {
	require ROOTDIR . "/includes/invoicefunctions.php";
}


if (!function_exists("getCartConfigOptions")) {
	require ROOTDIR . "/includes/configoptionsfunctions.php";
}

$result = select_query("tblhosting", "id,userid", array("id" => $serviceid));
$data = mysql_fetch_array($result);
$serviceid = $data['id'];
$clientid = $data['userid'];

if (!$serviceid) {
	$apiresults = array("result" => "error", "message" => "Service ID Not Found");
	return null;
}

$_SESSION['uid'] = $clientid;
global $currency;

$currency = getCurrency($clientid);
$checkout = ($calconly ? false : true);

if ($checkout) {
	$gatewaysarray = array();
	$result = select_query("tblpaymentgateways", "gateway", array("setting" => "name"));

	while ($data = mysql_fetch_array($result)) {
		$gatewaysarray[] = $data['gateway'];
	}


	if (!in_array($paymentmethod, $gatewaysarray)) {
		$apiresults = array("result" => "error", "message" => "Invalid Payment Method. Valid options include " . implode(",", $gatewaysarray));
		return null;
	}
}

$apiresults['result'] = "success";

if ($type == "product") {
	$upgrades = SumUpPackageUpgradeOrder($serviceid, $newproductid, $newproductbillingcycle, $promocode, $paymentmethod, $checkout);
	$apiresults = array_merge($apiresults, $upgrades[0]);
}
else {
	if ($type == "configoptions") {
		$subtotal = 0;
		$result = select_query("tblhosting", "packageid,billingcycle", array("id" => $serviceid));
		$data = mysql_fetch_array($result);
		$pid = $data[0];
		$billingcycle = $data[1];
		$configoption = getCartConfigOptions($pid, "", $billingcycle, $serviceid);
		$configoptions = $_REQUEST['configoptions'];

		if (!is_array($configoptions)) {
			$configoptions = array();
		}

		foreach ($configoption as $option) {
			$id = $option['id'];
			$optiontype = $option['optiontype'];
			$selectedvalue = $option['selectedvalue'];
			$selectedqty = $option['selectedqty'];

			if (!isset($configoptions[$id])) {
				if ($optiontype == "3" || $optiontype == "4") {
					$selectedvalue = $selectedqty;
				}

				$configoptions[$id] = $selectedvalue;
				continue;
			}
		}

		$upgrades = SumUpConfigOptionsOrder($serviceid, $configoptions, $promocode, $paymentmethod, $checkout);
		foreach ($upgrades as $i => $vals) {
			foreach ($vals as $k => $v) {
				$apiresults[$k . ($i + 1)] = $v;
			}
		}

		$subtotal = $GLOBALS['subtotal'];
		$discount = $GLOBALS['discount'];
		$apiresults['subtotal'] = formatCurrency($subtotal);
		$apiresults['discount'] = formatCurrency($discount);
		$apiresults['total'] = formatCurrency($subtotal - $discount);
	}
	else {
		$apiresults = array("result" => "error", "message" => "Invalid Upgrade Type");
		return null;
	}
}


if (!$checkout) {
	return null;
}

$upgradedata = createUpgradeOrder($serviceid, $ordernotes, $promocode, $paymentmethod);
$apiresults = array_merge($apiresults, $upgradedata);
?>