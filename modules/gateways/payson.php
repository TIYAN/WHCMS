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

function payson_activate() {
	defineGatewayField( "payson", "text", "agentid", "", "Agent ID", "15", "" );
	defineGatewayField( "payson", "text", "email", "", "Seller Email", "50", "" );
	defineGatewayField( "payson", "text", "key", "", "Key", "20", "" );
	defineGatewayField( "payson", "yesno", "guaranteeoffered", "", "Offer Payson Guarantee", "", "" );
}


function payson_link($params) {
	$AgentID = $params["agentid"];
	$Key = $params["key"];
	$Description = $params["description"];
	$SellerEmail = $params["email"];
	$BuyerEmail = $params["clientdetails"]["email"];
	$BuyerFirstName = $params["clientdetails"]["firstname"];
	$BuyerLastName = $params["clientdetails"]["lastname"];
	$Cost = str_replace( ".", ",", $params["amount"] );
	$CurrencyCode = $params["currency"];
	$ExtraCost = "0";
	$OkUrl = $params["systemurl"] . "/modules/gateways/callback/payson.php";
	$CancelUrl = $params["returnurl"];
	$RefNr = $params["invoiceid"];
	$GuaranteeOffered = ($params["guaranteeoffered"] ? "2" : "1");
	$MD5string = $SellerEmail . ":" . $Cost . ":" . $ExtraCost . ":" . $OkUrl . ":" . $GuaranteeOffered . $Key;
	
	$MD5Hash = md5( $MD5string );
	$code = "
<form action=\"https://www.payson.se/merchant/default.aspx\" method=\"post\">
<input type=\"hidden\" name=\"BuyerEmail\" value=\"" . $BuyerEmail . "\">
<input type=\"hidden\" name=\"AgentID\" value=\"" . $AgentID . "\">
<input type=\"hidden\" name=\"Description\" value=\"" . $Description . "\">
<input type=\"hidden\" name=\"SellerEmail\" value=\"" . $SellerEmail . "\">
<input type=\"hidden\" name=\"BuyerFirstName\" value=\"" . $BuyerFirstName . "\">
<input type=\"hidden\" name=\"BuyerLastName\" value=\"" . $BuyerLastName . "\">
<input type=\"hidden\" name=\"Cost\" value=\"" . $Cost . "\">
<input type=\"hidden\" name=\"CurrencyCode\" value=\"" . $CurrencyCode . "\">
<input type=\"hidden\" name=\"ExtraCost\" value=\"" . $ExtraCost . "\">
<input type=\"hidden\" name=\"OkUrl\" value=\"" . $OkUrl . "\">
<input type=\"hidden\" name=\"CancelUrl\" value=\"" . $CancelUrl . "\">
<input type=\"hidden\" name=\"RefNr\" value=\"" . $RefNr . "\">
<input type=\"hidden\" name=\"MD5\" value=\"" . $MD5Hash . "\">
<input type=\"hidden\" name=\"GuaranteeOffered\" value=\"" . $GuaranteeOffered . "\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>
";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["paysonname"] = "payson";
$GATEWAYMODULE["paysonvisiblename"] = "Payson";
$GATEWAYMODULE["paysontype"] = "Invoices";
?>