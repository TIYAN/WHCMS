<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

function ippay_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "IP.Pay" ), "terminalid" => array( "FriendlyName" => "Terminal ID", "Type" => "text", "Size" => "25", "Description" => "Your Terminal ID assigned by IPpay" ), "testmode" => array( "FriendlyName" => "Test Mode", "Type" => "yesno" ) );
	return $configarray;
}


function ippay_capture($params) {
	global $remote_ip;

	$url = ($params["testmode"] ? "https://testgtwy.ippay.com/ippay" : "https://gtwy.ippay.com/ippay");
	$transid = $params["invoiceid"] . date( "YmdHis" );
	$transid = substr( $transid, 0, 18 );
	$transid = str_pad( $transid, 18, "0", STR_PAD_LEFT );
	$xmldata = "<JetPay>
    <TransactionType>SALE</TransactionType>
    <TerminalID>" . $params["terminalid"] . "</TerminalID>
    <TransactionID>" . $transid . "</TransactionID>
    <CardNum>" . $params["cardnum"] . "</CardNum>
    <CardExpMonth>" . substr( $params["cardexp"], 0, 2 ) . "</CardExpMonth>
    <CardExpYear>" . substr( $params["cardexp"], 2, 2 ) . "</CardExpYear>";

	if ($params["cccvv"]) {
		$xmldata .= "<CVV2>" . $params["cccvv"] . "</CVV2>";
	}


	if ($params["cardissuenum"]) {
		$xmldata .= "<Issue>" . $params["cardissuenum"] . "</Issue>";
	}


	if ($params["cardstart"]) {
		$xmldata .= "<CardStartMonth>" . substr( $params["cardstart"], 0, 2 ) . "</CardStartMonth>
<CardStartYear>" . substr( $params["cardstart"], 0, 2 ) . "</CardStartYear>";
	}

	$xmldata .= "<TotalAmount>" . $params["amount"] * 100 . "</TotalAmount>
    <CardName>" . $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"] . "</CardName>
    <BillingAddress>" . $params["clientdetails"]["address1"] . "</BillingAddress>
    <BillingCity>" . $params["clientdetails"]["city"] . "</BillingCity>
    <BillingStateProv>" . $params["clientdetails"]["state"] . "</BillingStateProv>
    <BillingPostalCode>" . $params["clientdetails"]["postcode"] . "</BillingPostalCode>
    <BillingPhone>" . $params["clientdetails"]["phonenumber"] . "</BillingPhone>
    <Email>" . $params["clientdetails"]["email"] . "</Email>
    <UserIPAddress>" . $remote_ip . "</UserIPAddress>
    <Origin>RECURRING</Origin>
    <UDField1>" . $params["invoiceid"] . "</UDField1>
    </JetPay>";
	$response = curlCall( $url, $xmldata );
	$response = XMLtoArray( $response );
	$response = $response["JETPAYRESPONSE"];

	if ($response["ACTIONCODE"] == "000") {
		return array( "status" => "success", "transid" => $response["TRANSACTIONID"], "rawdata" => $response );
	}

	return array( "status" => "declined", "rawdata" => $response );
}


function ippay_refund($params) {
	global $remote_ip;

	$url = ($params["testmode"] ? "https://testgtwy.ippay.com/ippay" : "https://gtwy.ippay.com/ippay");
	$transid = $params["invoiceid"] . date( "YmdHis" );
	$transid = substr( $transid, 0, 18 );
	$transid = str_pad( $transid, 18, "0", STR_PAD_LEFT );
	$xmldata = "<JetPay>
    <TransactionType>CREDIT</TransactionType>
    <TerminalID>" . $params["terminalid"] . "</TerminalID>
    <TransactionID>" . $transid . "</TransactionID>
    <CardNum>" . $params["cardnum"] . "</CardNum>
    <CardExpMonth>" . substr( $params["cardexp"], 0, 2 ) . "</CardExpMonth>
    <CardExpYear>" . substr( $params["cardexp"], 2, 2 ) . "</CardExpYear>
    <TotalAmount>" . $params["amount"] * 100 . "</TotalAmount>
    </JetPay>";
	$response = curlCall( $url, $xmldata );
	$response = XMLtoArray( $response );
	$response = $response["JETPAYRESPONSE"];

	if ($response["ACTIONCODE"] == "000") {
		return array( "status" => "success", "transid" => $response["TRANSACTIONID"], "rawdata" => $response );
	}

	return array( "status" => "error", "rawdata" => $response );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>