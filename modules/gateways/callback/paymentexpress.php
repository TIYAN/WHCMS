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
$GATEWAY = getGatewayVariables( "paymentexpress" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

logTransaction( "Payment Express", $_REQUEST, "Received" );
$url = "https://sec.paymentexpress.com/pxpay/pxaccess.aspx";
$xml = "<ProcessResponse>
<PxPayUserId>" . $GATEWAY["pxpayuserid"] . "</PxPayUserId>
<PxPayKey>" . $GATEWAY["pxpaykey"] . "</PxPayKey>
<Response>" . html_entity_decode( $_REQUEST["result"] ) . "</Response>
</ProcessResponse>";
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_POST, 1 );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
$outputXml = curl_exec( $ch );
curl_close( $ch );
$xmlresponse = XMLtoArray( $outputXml );
$xmlresponse = $xmlresponse["RESPONSE"];
$success = $xmlresponse["SUCCESS"];
$invoiceid = $xmlresponse["TXNDATA1"];
$transid = $xmlresponse["TXNID"];

if ($xmlresponse["SUCCESS"] == "1") {
	$result = select_query( "tblinvoices", "id", array( "id" => $invoiceid ) );
	$data = mysql_fetch_array( $result );
	$id = $data["id"];

	if (!$id) {
		logTransaction( "Payment Express", array_merge( $_REQUEST, $xmlresponse ), "Invoice ID Not Found" );
		header( "Location: ../../../clientarea.php" );
		exit();
	}

	$result = select_query( "tblaccounts", "invoiceid", array( "transid" => $transid ) );
	$data = mysql_fetch_array( $result );
	$transinvoiceid = $data["invoiceid"];

	if ($transinvoiceid) {
		header( "Location: ../../../viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true" );
		exit();
	}

	addInvoicePayment( $invoiceid, $transid, "", "", "paymentexpress" );
	logTransaction( "Payment Express", array_merge( $_REQUEST, $xmlresponse ), "Successful" );
	header( "Location: ../../../viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true" );
	exit();
	return 1;
}

logTransaction( "Payment Express", array_merge( $_REQUEST, $xmlresponse ), "Unsuccessful" );

if ($invoiceid) {
	header( "Location: ../../../viewinvoice.php?id=" . $invoiceid . "&paymentfailed=true" );
}
else {
	header( "Location: ../../../clientarea.php?action=invoices" );
}

exit();
?>