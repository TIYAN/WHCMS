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
$gatewaymodule = "amazonsimplepay";
$GATEWAY = getGatewayVariables( $gatewaymodule );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$status = $_POST["status"];
$invoiceid = $_POST["referenceId"];
$transid = $_POST["transactionId"];
$amount = number_format( substr( $_POST["transactionAmount"], strpos( $_POST["transactionAmount"], " " ) ), 2 );
$fee = "0.00";
$invoiceid = checkCbInvoiceID( $invoiceid, $GATEWAY["name"] );
checkCbTransID( $transid );
$parameters = $_POST;

if ($GATEWAY["testmode"]) {
	$url = "https://fps.sandbox.amazonaws.com";
}
else {
	$url = "https://fps.amazonaws.com";
}

$url .= "?Action=VerifySignature";
$url .= "&UrlEndPoint=" . $CONFIG["SystemURL"] . "/modules/gateways/callback/amazonsimplepay.php";
$url .= "&HttpParameters=" . rawurlencode( http_build_query( $parameters ) );
$url .= "&Version=2008-09-17";
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_FILETIME, false );
curl_setopt( $ch, CURLOPT_FRESH_CONNECT, true );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
curl_setopt( $ch, CURLOPT_MAXREDIRS, 0 );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$response = curl_exec( $ch );
curl_close( $ch );
$xmlobject = simplexml_load_string( trim( $response ) );
$results["status"] = (bool)$xmlobject->VerifySignatureResult->VerificationStatus;

if (( $status == "PS" && $results["status"] == "Success" )) {
	addInvoicePayment( $invoiceid, $transid, "", $fee, $gatewaymodule );
	logTransaction( $GATEWAY["name"], $_POST, "Successful" );
	return 1;
}

logTransaction( $GATEWAY["name"], $_POST, "Unsuccessful" );
?>