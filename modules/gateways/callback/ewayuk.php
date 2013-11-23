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
 * */

require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "ewayuk" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$postfields = array();

if ($GATEWAY["testmode"]) {
	$postfields["CustomerID"] = "87654321";
	$postfields["UserName"] = "TestAccount";
}
else {
	$postfields["CustomerID"] = $GATEWAY["customerid"];
	$postfields["UserName"] = $GATEWAY["username"];
}

$postfields["AccessPaymentCode"] = $_REQUEST["AccessPaymentCode"];
$merchantposturl = "https://payment.ewaygateway.com/Result/?";
foreach ($postfields as $k => $v) {
	$merchantposturl .= "" . $k . "=" . urlencode( $v ) . "&";
}

$response = curlCall( $merchantposturl, "" );
$authecode = ewayuk_fetch_data( $response, "<authCode>", "</authCode>" );
$responsecode = ewayuk_fetch_data( $response, "<responsecode>", "</responsecode>" );
$returnamount = ewayuk_fetch_data( $response, "<returnamount>", "</returnamount>" );
$txn_id = ewayuk_fetch_data( $response, "<trxnnumber>", "</trxnnumber>" );
$trxnstatus = ewayuk_fetch_data( $response, "<trxnstatus>", "</trxnstatus>" );
$invoiceid = ewayuk_fetch_data( $response, "<MerchantInvoice>", "</MerchantInvoice>" );
$trxnresponsemessage = ewayuk_fetch_data( $response, "<trxnresponsemessage>", "</trxnresponsemessage>" );
$response = array( "response" => $response );

if ($trxnstatus == "true") {
	logTransaction( "eWay UK Hosted Payments", array_merge( $_REQUEST, $postfields, $response ), "Successful" );
	addInvoicePayment( $invoiceid, $txn_id, $returnamount, "", "ewayuk" );
	header( "Location: " . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true" );
	exit();
	return 1;
}

logTransaction( "eWay UK Hosted Payments", array_merge( $_REQUEST, $postfields, $response ), "Error" );
header( "Location: " . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $invoiceid . "&paymentfailed=true" );
exit();
?>