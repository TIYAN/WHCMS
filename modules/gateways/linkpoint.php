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
 * */

function linkpoint_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "LinkPoint" ), "storenumber" => array( "FriendlyName" => "Store Number", "Type" => "text", "Size" => "20" ), "keyfile" => array( "FriendlyName" => "Key File", "Type" => "text", "Size" => "50", "Description" => "Full path to file eg. /home/username/xxxxxx.pem" ), "testmode" => array( "FriendlyName" => "Test Mode", "Type" => "yesno" ) );
	return $configarray;
}


function linkpoint_capture($params) {
	include_once dirname( __FILE__ ) . "/../../includes/lphp.php";
	$mylphp = new lphp();

	if ($params['testmode'] == "on") {
		$myorder['host'] = "staging.linkpt.net";
		$myorder['result'] = "GOOD";
	}
	else {
		$myorder['host'] = "secure.linkpt.net";
		$myorder['result'] = "LIVE";
	}

	$myorder['port'] = "1129";
	$myorder['keyfile'] = $params['keyfile'];
	$myorder['configfile'] = $params['storenumber'];
	$myorder['ordertype'] = "SALE";
	$myorder['transactionorigin'] = "ECI";
	$myorder['terminaltype'] = "UNSPECIFIED";
	$myorder['chargetotal'] = $params['amount'];
	$address1Tokens = explode( " ", $params['clientdetails']['address1'] );

	if (is_numeric( $address1Tokens[0] )) {
		$myorder['addrnum'] = $address1Tokens[0];
	}

	$myorder['zip'] = $params['postcode'];
	$myorder['cardnumber'] = $params['cardnum'];
	$myorder['cardexpmonth'] = substr( $params['cardexp'], 0, 2 );
	$myorder['cardexpyear'] = substr( $params['cardexp'], 2, 2 );
	$myorder['cvmvalue'] = $params['cccvv'];

	if (0 < strlen( $myorder['cvmvalue'] )) {
		$myorder['cvmindicator'] = "provided";
	}

	$myorder['ip'] = $_SERVER['REMOTE_ADDR'];
	$myorder['name'] = $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'];
	$myorder['address1'] = $params['clientdetails']['address1'];
	$myorder['city'] = $params['clientdetails']['city'];
	$myorder['state'] = $params['clientdetails']['state'];
	$myorder['country'] = $params['clientdetails']['country'];
	$myorder['phone'] = $params['clientdetails']['phonenumber'];
	$myorder['fax'] = "";
	$myorder['zip'] = $params['clientdetails']['postcode'];
	$myorder['debugging'] = "false";
	$result = $mylphp->curl_process( $myorder );
	$desc = "Action => Capture
Client => " . $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'] . ( "
Result => " . $result . "
" ) . $mylphp->debugstr;
	foreach ($result as $errorkey => $errorvalue) {

		if ($errorkey != "cardnumber" && $errorkey != "cvmvalue") {
			$desc .= ( "" . $errorkey . " => " . $errorvalue . "
" );
			continue;
		}
	}


	if ($result['r_message'] === "APPROVED") {
		return array( "status" => "success", "transid" => $result['r_ordernum'], "rawdata" => $desc );
	}

	return array( "status" => "declined", "rawdata" => $desc );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>