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

require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "payza" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}


if ($GATEWAY["type"] == "on") {
	$ipnv2handlerurl = "https://sandbox.payza.com/sandbox/ipn2.ashx";
}
else {
	$ipnv2handlerurl = "https://secure.payza.com/ipn2.ashx";
}

$token = "token=" . urlencode( $_POST["token"] );
$response = "";
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $ipnv2handlerurl );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $token );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_HEADER, false );
curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
$response = curl_exec( $ch );
curl_close( $ch );

if (0 < strlen( $response )) {
	if (urldecode( $response ) == "INVALID TOKEN") {
		logTransaction( "Payza", $_REQUEST, "Invalid Token" );
		exit();
		return 1;
	}

	$response = urldecode( $response );
	$aps = explode( "&", $response );
	foreach ($aps as $ap) {
		$ele = explode( "=", $ap );
		$info[$ele[0]] = $ele[1];
	}

	$result = select_query( "tblcurrencies", "", array( "code" => $info["ap_currency"] ) );
	$data = mysql_fetch_array( $result );
	$currencyid = $data["id"];

	if (!$currencyid) {
		logTransaction( "Payza", $response, "Unrecognised Currency" );
		exit();
	}


	if ($info["ap_status"] == "Success") {
		$_REQUEST = $info;
		$id = checkCbInvoiceID( $info["apc_1"], "Payza" );
		checkCbTransID( $info["ap_referencenumber"] );
		$amount = $info["ap_totalamount"];
		$fees = $info["ap_feeamount"];
		$result = select_query( "tblinvoices", "userid,total", array( "id" => $id ) );
		$data = mysql_fetch_array( $result );
		$userid = $data["userid"];
		$total = $data["total"];
		$currency = getCurrency( $userid );

		if ($currencyid != $currency["id"]) {
			$amount = convertCurrency( $amount, $currencyid, $currency["id"] );
			$fees = convertCurrency( $fees, $currencyid, $currency["id"] );

			if (( $total < $amount + 1 && $amount - 1 < $total )) {
				$amount = $total;
			}
		}


		if ($id != "") {
			addInvoicePayment( $info["apc_1"], $info["ap_referencenumber"], $amount, $fees, "Payza" );
			logTransaction( "Payza", $response, "Successful" );
			exit();
			return 1;
		}

		logTransaction( "Payza", $response, "Error" );
		exit();
		return 1;
	}

	logTransaction( "Payza", $response, "Unsuccessful" );
	exit();
	return 1;
}

logTransaction( "Payza", $response, "No response received from Payza" );
exit();
?>