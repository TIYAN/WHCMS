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

function payoffline_activate() {
	defineGatewayField( "payoffline", "text", "username", "", "Merchant Key", "20", "" );
	defineGatewayField( "payoffline", "text", "secretkey", "", "MD5 Secret Key", "40", "" );
	defineGatewayField( "payoffline", "text", "testuser", "", "Test Merchant Key", "20", "" );
	defineGatewayField( "payoffline", "text", "testkey", "", "Test MD5 Secret Key", "40", "" );
	defineGatewayField( "payoffline", "yesno", "testmode", "", "Test Mode", "", "" );
}


function payoffline_link($params) {
	$gatewaytestmode = $params["testmode"];

	if ($gatewaytestmode) {
		$code = "<form action=\"http://test.payoffline.com/TestTrans/iBasic.aspx\" method=\"post\">";
		$gatewayusername = $params["testuser"];
		$secretkey = $params["testkey"];
	}
	else {
		$code = "<form action=\"https://secure.payoffline.com/process/invoice.aspx\" method=\"post\">";
		$gatewayusername = $params["username"];
		$secretkey = $params["secretkey"];
	}

	$invoiceid = $params["invoiceid"];
	$description = $params["description"];
	$amount = $params["amount"];
	$duedate = $params["duedate"];
	$clientid = $params["clientdetails"]["userid"];
	$firstname = $params["clientdetails"]["firstname"];
	$lastname = $params["clientdetails"]["lastname"];
	$email = $params["clientdetails"]["email"];
	$address1 = $params["clientdetails"]["address1"];
	$address2 = $params["clientdetails"]["address2"];
	$city = $params["clientdetails"]["city"];
	$state = $params["clientdetails"]["state"];
	$postcode = $params["clientdetails"]["postcode"];
	$country = $params["clientdetails"]["country"];
	$phone = $params["clientdetails"]["phone"];
	$companyname = $params["companyname"];
	$systemurl = $params["systemurl"];
	$currency = $params["currency"];
	$callbackvars = "itm1=" . $invoiceid . "&amt1=" . $amount . "&client=" . $clientid;
	
	$md5sign = md5( $gatewayusername . $invoiceid . $amount . "30" . $params["systemurl"] . "/modules/gateways/callback/payoffline.php" . $params["returnurl"] . $params["returnurl"] . $callbackvars . $secretkey );
	$code .= "
    <input type=\"hidden\" name=\"mid\" value=\"" . $gatewayusername . "\">
    <input type=\"hidden\" name=\"oid\" value=\"" . $invoiceid . "\">
    <input type=\"hidden\" name=\"amt\" value=\"" . $amount . "\">
    <input type=\"hidden\" name=\"expdays\" value=\"30\">
    <input type=\"hidden\" name=\"callbackurl\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/payoffline.php\">
    <input type=\"hidden\" name=\"returl\" value=\"" . $params["returnurl"] . "\">
    <input type=\"hidden\" name=\"cancelurl\" value=\"" . $params["returnurl"] . "\">
    <input type=\"hidden\" name=\"callbackvars\" value=\"" . $callbackvars . "\">
    <input type=\"hidden\" name=\"sign\" value=\"" . $md5sign . "\">
    <input type=\"submit\" value=\"Submit\">
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["payofflinename"] = "payoffline";
$GATEWAYMODULE["payofflinevisiblename"] = "Pay Offline";
$GATEWAYMODULE["payofflinetype"] = "Invoices";
?>