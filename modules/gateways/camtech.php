<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 * */

function camtech_activate() {
	defineGatewayField( "camtech", "text", "paymenturl", "", "Payment URL", "32", "" );
	defineGatewayField( "camtech", "text", "mid", "", "Merchant ID", "32", "" );
	defineGatewayField( "camtech", "text", "password", "", "Password", "32", "" );
}


function camtech_capture($params) {
	$invoiceid = $params['invoiceid'];
	$host = $params['paymenturl'];
	$timestamp = camtech_getGMTtimestamp();
	$vars = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . "<SecurePayMessage>" . "<MessageInfo>" . "<messageID>8af793f9af34bea0cf40f5fb5c630c</messageID>" . "<messageTimestamp>" . urlencode( $timestamp ) . "</messageTimestamp>" . "<timeoutValue>60</timeoutValue>" . "<apiVersion>xml-4.2</apiVersion>" . "</MessageInfo>" . "<MerchantInfo>" . "<merchantID>" . $params['mid'] . "</merchantID>" . "<password>" . $params['password'] . "</password>" . "</MerchantInfo>" . "<RequestType>Payment</RequestType>" . "<Payment>" . "<TxnList count=\"1\">" . "<Txn ID=\"1\">" . "<txnType>0</txnType>" . "<txnSource>0</txnSource>" . "<amount>" . $params['amount'] * 100 . "</amount>" . "<purchaseOrderNo>" . $invoiceid . "</purchaseOrderNo>" . "<CreditCardInfo>" . "<cardNumber>" . $params['cardnum'] . "</cardNumber>";

	if ($params['cccvv'] != "") {
		$vars .= "<cvv>" . $params['cccvv'] . "</cvv>";
	}

	$vars .= "<expiryDate>" . camtech_exp_month( $params['cardexp'] ) . "/" . camtech_exp_year( $params['cardexp'] ) . "</expiryDate>" . "</CreditCardInfo>" . "</Txn>" . "</TxnList>" . "</Payment>" . "</SecurePayMessage>";
	$response = camtech_openSocket( $host, $vars );
	$xmlres = array();
	$xmlres = camtech_makeXMLTree( $response );
	$transid = trim( $xmlres['SecurePayMessage'][Payment][TxnList][Txn]['txnID'] );
	$approved = trim( $xmlres['SecurePayMessage'][Payment][TxnList][Txn]['approved'] );
	$result = ($approved == "Yes" ? "success" : "");
	$responseCode = trim( $xmlres['SecurePayMessage'][Payment][TxnList][Txn]['responseCode'] );
	$desc = "responseCode = " . $responseCode . "
";
	$desc .= "transaction id = " . $transid . "
";

	if ($result == "success") {
		return array( "status" => "success", "transid" => $transid, "rawdata" => $desc );
	}

	return array( "status" => "declined", "rawdata" => $desc );
}


function camtech_link($params) {
	$code = "
  <form method=\"post\" action=\"" . $params['systemurl'] . "/creditcard.php\" name=\"paymentfrm\">
  <input type=\"hidden\" name=\"invoiceid\" value=\"" . $params['invoiceid'] . "\">
  <input type=\"submit\" value=\"" . $params['langpaynow'] . "\">
  </form>";
	return $code;
}


function camtech_getGMTtimeStamp() {
	$stamp = date( "YmdGis" ) . "000+1000";
	return $stamp;
}


function camtech_openSocket($host, $query) {
	$path = explode( "/", $host );
	$host = $path[0];
	unset( $path[0] );
	$path = "/" . implode( "/", $path );
	$post = "POST " . $path . " HTTP/1.1
";
	$post .= ( ( "Host: " . $host . "
" ) . "
" );
	$post .= "Content-type: application/x-www-form-urlencoded
";
	$post .= "Content-type: text/xml
";
	$post .= "Content-length: " . strlen( $query ) . "
";
	$post .= "Connection: close

" . $query;
	$h = fsockopen( "ssl://" . $host, 443, $errno, $errstr );

	if ($errstr) {
		print "" . $errstr . " (" . $errno . ")<br/>
";
	}

	fwrite( $h, $post );
	$headers = "";

	if ($str = trim( fgets( $h, 4096 ) )) {
		$headers .= ( "" . $str . "
" );
	}

	$headers2 = "";

	if ($str = trim( fgets( $h, 4096 ) )) {
		$headers2 .= ( "" . $str . "
" );
	}

	$body = "";

	while (!feof( $h )) {
		$body .= fgets( $h, 4096 );
	}

	fclose( $h );
	return $body;
}


function camtech_makeXMLTree($rawxml) {
	include_once ROOTDIR . "/includes/functions.php";
	$options = array( XML_OPTION_CASE_FOLDING => 0, XML_OPTION_SKIP_WHITE => 1 );
	return ParseXmlToArray( $rawxml, $options );
}


function camtech_exp_year($expiry) {
	return date( "y", mktime( 0, 0, 0, substr( $expiry, 0, 2 ), 1, substr( $expiry, 2 ) ) );
}


function camtech_exp_month($expiry) {
	return date( "m", mktime( 0, 0, 0, substr( $expiry, 0, 2 ), 1, substr( $expiry, 2 ) ) );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE['camtechname'] = "camtech";
$GATEWAYMODULE['camtechvisiblename'] = "Camtech";
$GATEWAYMODULE['camtechtype'] = "CC";
?>