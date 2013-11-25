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
 * */

require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "nochex" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$debugreport = "";

if (!isset( $_POST )) {
	$_POST = &$HTTP_POST_VARS;
}

foreach ($_POST as $key => $value) {
	$values[] = $key . "=" . urlencode( $value );
	$debugreport .= ( "" . $key . " => " . $value . "
" );
}

$work_string = @implode( "&", $values );
$url = "https://www.nochex.com/nochex.dll/apc/apc";
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_POSTFIELDSIZE, 0 );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $work_string );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
$output = curl_exec( $ch );
curl_close( $ch );
$response = preg_replace( "'Content-type: text/plain'si", "", $output );

if ($response == "AUTHORISED") {
	addInvoicePayment( $_POST["order_id"], $_POST["transaction_id"], "", "", "nochex" );
	logTransaction( "NoChex", $debugreport, "Successful" );
	return 1;
}

logTransaction( "NoChex", $debugreport, "Invalid" );
?>