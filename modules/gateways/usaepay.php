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
 * */

function usaepay_activate() {
	defineGatewayField( "usaepay", "text", "key", "", "Key", "40", "" );
	defineGatewayField( "usaepay", "yesno", "testmode", "", "Test Mode", "", "" );
}


function usaepay_capture($params) {
	global $remote_ip;

	$url = "https://www.usaepay.com/gate";
	$postfields = array();
	$postfields["UMcommand"] = "cc:sale";
	$postfields["UMkey"] = $params["key"];
	$postfields["UMignoreDuplicate"] = "yes";
	$postfields["UMcard"] = $params["cardnum"];
	$postfields["UMexpir"] = $params["cardexp"];
	$postfields["UMamount"] = $params["amount"];
	$postfields["UMinvoice"] = $params["invoiceid"];
	$postfields["UMname"] = $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"];
	$postfields["UMstreet"] = $params["clientdetails"]["address1"];
	$postfields["UMzip"] = $params["clientdetails"]["postcode"];
	$postfields["UMcvv2"] = $params["cccvv"];
	$postfields["UMip"] = $remote_ip;
	$query_string = "";
	foreach ($postfields as $k => $v) {
		$query_string .= "" . $k . "=" . urlencode( $v ) . "&";
	}

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$result = curl_exec( $ch );

	if (curl_error( $ch )) {
		$result = "CURL Error: " . curl_error( $ch );
	}

	curl_close( $ch );
	$tmp = split( "
", $result );

	$result = $tmp[count( $tmp ) - 1];
	parse_str( $result, $tmp );

	if ($tmp["UMresult"] == "A") {
		return array( "status" => "success", "transid" => $tmp["UMrefNum"], "rawdata" => $tmp );
	}

	return array( "status" => "declined", "rawdata" => $tmp );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["usaepayname"] = "usaepay";
$GATEWAYMODULE["usaepayvisiblename"] = "USA ePay";
$GATEWAYMODULE["usaepaytype"] = "CC";
?>