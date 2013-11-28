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

function cashu_activate() {
	defineGatewayField( "cashu", "text", "merchantid", "", "Merchant ID", "20", "" );
	defineGatewayField( "cashu", "text", "encryptionkeyword", "", "Encryption Keyword", "20", "" );
	defineGatewayField( "cashu", "yesno", "demomode", "", "Demo Mode", "", "" );
}


function cashu_link($params) {
	if ($params["cconvert"] == "on") {
		$params["amount"] = number_format( $params["amount"] / $params["ccrate"], 2, ".", "" );
		$params["currency"] = $params["cccurrency"];
	}

	$token = md5( $params["merchantid"] . ":" . $params["amount"] . ":" . strtolower( $params["currency"] ) . ":" . $params["encryptionkeyword"] );
	$code = "<form action=\"https://www.cashu.com/cgi-bin/pcashu.cgi\" method=\"post\">
<input type=\"hidden\" name=\"merchant_id\" value=\"" . $params["merchantid"] . "\">
<input type=\"hidden\" name=\"token\" value=\"" . $token . "\">
<input type=\"hidden\" name=\"display_text\" value=\"" . $params["description"] . "\">
<input type=\"hidden\" name=\"currency\" value=\"" . $params["currency"] . "\">
<input type=\"hidden\" name=\"amount\" value=\"" . $params["amount"] . "\">
<input type=\"hidden\" name=\"language\" value=\"en\">
<input type=\"hidden\" name=\"email\" value=\"" . $params["clientdetails"]["email"] . "\">
<input type=\"hidden\" name=\"session_id\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"txt1\" value=\"" . $params["description"] . "\">";

	if ($params["demomode"] == "on") {
		$code .= "<input type=\"hidden\" name=\"test_mode\" value=\"1\">";
	}

	$code .= "
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["cashuname"] = "cashu";
$GATEWAYMODULE["cashuvisiblename"] = "CashU";
$GATEWAYMODULE["cashutype"] = "Invoices";
$GATEWAYMODULE["cashunotes"] = "You must set the 'thanx_url' in your CashU Control Panel to: " . $CONFIG["SystemURL"] . "/modules/gateways/callback/cashu.php";
?>