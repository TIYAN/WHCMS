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
require "../protx.php";
$GATEWAY = $params = getGatewayVariables( "protx" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}


if ($protxsimmode) {
	$url = "https://test.sagepay.com/simulator/VSPDirectCallback.asp";
}
else {
	if ($params["testmode"] == "on") {
		$url = "https://test.sagepay.com/gateway/service/direct3dcallback.vsp";
	}
	else {
		$url = "https://live.sagepay.com/gateway/service/direct3dcallback.vsp";
	}
}

$data = "PaRes=" . urlencode( $_POST["PaRes"] ) . "&MD=" . $_POST["MD"];
$data = protx_formatData( $_POST );
$response = protx_requestPost( $url, $data );
$baseStatus = $response["Status"];
$transdump = "";
foreach ($response as $key => $value) {
	$transdump .= ( "" . $key . " => " . $value . "
" );
}

$invoiceid = $_REQUEST["invoiceid"];

if (( !$invoiceid && isset( $_SESSION["protxinvoiceid"] ) )) {
	$invoiceid = $_SESSION["protxinvoiceid"];
}

$transdump .= "Invoice ID => " . $invoiceid;

if ($params["cardtype"] == "Maestro") {
	$result = select_query( "tblinvoices", "userid", array( "id" => $invoiceid ) );
	$data = mysql_fetch_array( $result );
	update_query( "tblclients", array( "cardtype" => "", "cardnum" => "", "expdate" => "", "issuenumber" => "", "startdate" => "" ), array( "id" => $data["userid"] ) );
}

$callbacksuccess = false;
switch ($response["Status"]) {
case "OK": {
		addInvoicePayment( $invoiceid, $response["VPSTxId"], "", "", "protx", "on" );
		logTransaction( "ProtX", $transdump, "Successful" );
		sendMessage( "Credit Card Payment Confirmation", $invoiceid );
		$callbacksuccess = true;
		break;
	}

case "NOTAUTHED": {
		logTransaction( "ProtX", $transdump, "Not Authed" );
		sendMessage( "Credit Card Payment Failed", $invoiceid );
		break;
	}

case "REJECTED": {
		logTransaction( "ProtX", $transdump, "Rejected" );
		sendMessage( "Credit Card Payment Failed", $invoiceid );
		break;
	}

case "FAIL": {
		logTransaction( "ProtX", $transdump, "Failed" );
		sendMessage( "Credit Card Payment Failed", $invoiceid );
		break;
	}

default: {
		logTransaction( "ProtX", $transdump, "Error" );
		sendMessage( "Credit Card Payment Failed", $invoiceid );
		break;
	}
}

callback3DSecureRedirect( $invoiceid, $callbacksuccess );
?>