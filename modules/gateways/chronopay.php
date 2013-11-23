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

function chronopay_activate() {
	defineGatewayField( "chronopay", "text", "productid", "", "Product ID", "20", "The product ID of a generic product in your ChronoPay Account" );
}


function chronopay_link($params) {
	$code = "
<form action=\"https://secure.chronopay.com/index_shop.cgi\" method=\"post\">
<input type=\"hidden\" name=\"product_id\" value=\"" . $params["productid"] . "\">
<input type=\"hidden\" name=\"product_name\" value=\"" . $params["description"] . "\">
<input type=\"hidden\" name=\"product_price\" value=\"" . $params["amount"] . "\">
<input type=\"hidden\" name=\"product_price_currency\" value=\"" . $params["currency"] . "\">
<input type=\"hidden\" name=\"f_name\" value=\"" . $params["clientdetails"]["firstname"] . "\">
<input type=\"hidden\" name=\"s_name\" value=\"" . $params["clientdetails"]["lastname"] . "\">
<input type=\"hidden\" name=\"email\" value=\"" . $params["clientdetails"]["email"] . "\">
<input type=\"hidden\" name=\"street\" value=\"" . $params["clientdetails"]["address1"] . "\">
<input type=\"hidden\" name=\"city\" value=\"" . $params["clientdetails"]["city"] . "\">
<input type=\"hidden\" name=\"state\" value=\"" . $params["clientdetails"]["state"] . "\">
<input type=\"hidden\" name=\"zip\" value=\"" . $params["clientdetails"]["postcode"] . "\">
<input type=\"hidden\" name=\"country\" value=\"" . $params["clientdetails"]["country"] . "\">
<input type=\"hidden\" name=\"phone\" value=\"" . $params["clientdetails"]["phonenumber"] . "\">
<input type=\"hidden\" name=\"cs1\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"cb_url\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/chronopay.php\">
<input type=\"hidden\" name=\"cb_type\" value=\"P\">
<input type=\"hidden\" name=\"decline_url\" value=\"" . $params["returnurl"] . "\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>
";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["chronopayname"] = "chronopay";
$GATEWAYMODULE["chronopayvisiblename"] = "ChronoPay";
$GATEWAYMODULE["chronopaytype"] = "Invoices";
?>