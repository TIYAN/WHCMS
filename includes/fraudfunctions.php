<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 **/

function getFraudConfigOptions($fraud) {
	$configoptions = array();
	$result = select_query("tblfraud", "", array("fraud" => $fraud));

	while ($data = mysql_fetch_array($result)) {
		$setting = $data['setting'];
		$value = $data['value'];
		$configoptions[$setting] = $value;
	}

	return $configoptions;
}

function getActiveFraudModule() {
	global $CONFIG;

	$result = select_query("tblfraud", "fraud", array("setting" => "Enable", "value" => "on"));
	$data = mysql_fetch_array($result);
	$fraud = $data['fraud'];
	$orderid = $_SESSION['orderdetails']['OrderID'];

	if ($CONFIG['SkipFraudForExisting']) {
		$result = select_query("tblorders", "COUNT(*)", array("status" => "Active", "userid" => $_SESSION['uid']));
		$data = mysql_fetch_array($result);

		if ($data[0]) {
			$fraudmodule = "";
			logActivity("Order ID " . $orderid . " Skipped Fraud Check due to Already Active Orders");
		}
	}

	$hookresponses = run_hook("RunFraudCheck", array("orderid" => $orderid, "userid" => $_SESSION['uid']));
	foreach ($hookresponses as $hookresponse) {

		if ($hookresponse) {
			$fraud = "";
			logActivity("Order ID " . $orderid . " Skipped Fraud Check due to Custom Hook");
			continue;
		}
	}

	return $fraud;
}

function getFraudParams($fraudmodule, $userid = "", $ip = "") {
	global $remote_ip;

	if (!$userid) {
		$userid = $_SESSION['uid'];
	}

	include ROOTDIR . "/includes/countriescallingcodes.php";
	$params = getFraudConfigOptions($fraudmodule);
	$params['ip'] = ($ip ? $ip : $remote_ip);
	$params['forwardedip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
	$params['clientsdetails'] = getClientsDetails($userid);
	$countrycode = $params['clientsdetails']['country'];
	$params['clientsdetails']['countrycode'] = $countrycallingcodes[$countrycode];
	$phonenumber = preg_replace("/[^0-9]/", "", $params['clientsdetails']['phonenumber']);
	$params['clientsdetails']['phonenumber'] = $phonenumber;
	return $params;
}

function runFraudCheck($orderid, $fraudmodule, $userid = "", $ip = "") {
	if (!isValidforPath($fraudmodule)) {
		exit("Invalid Fraud Module Name");
	}


	if (!function_exists("doFraudCheck")) {
		include ROOTDIR . ("/modules/fraud/" . $fraudmodule . "/" . $fraudmodule . ".php");
	}

	$params = getFraudParams($fraudmodule, $userid, $ip);
	$results = doFraudCheck($params);
	$fraudoutput = "";

	if ($results) {
		foreach ($results as $key => $value) {

			if ((($key != "userinput" && $key != "title") && $key != "description") && $key != "error") {
				$fraudoutput .= ("" . $key . " => " . $value . "\r\n");
				continue;
			}
		}
	}

	update_query("tblorders", array("fraudmodule" => $fraudmodule, "fraudoutput" => $fraudoutput), array("id" => $orderid));
	$results['fraudoutput'] = $fraudoutput;
	return $results;
}

?>