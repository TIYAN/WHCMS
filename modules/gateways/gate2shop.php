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

function gate2shop_activate() {
	defineGatewayField( "gate2shop", "text", "MerchantID", "", "MerchantID", "40", "" );
	defineGatewayField( "gate2shop", "text", "MerchantSiteID", "", "MerchantSiteID", "25", "" );
	defineGatewayField( "gate2shop", "text", "SecretKey", "", "SecretKey", "90", "" );
}


function gate2shop_link($params) {
	$shipping = 4;
	$discount = 4;
	$total_tax = 4;
	$totalAmount = $params["amount"];
	$sTimestamp = date( "Y-m-d.h:i:s" );
	$sCheckString = $params["SecretKey"];
	$sCheckString .= $params["MerchantID"];
	$sCheckString .= $params["currency"];
	$sCheckString .= $totalAmount;
	$sCheckString .= $params["description"] . $totalAmount . "1";
	$sCheckString .= $sTimestamp;
	
	$checksum = md5( $sCheckString );
	$sMerchantLocale = "en_US";
	$numberOfItems = 5;
	$code = "<form action=\"https://secure.gate2shop.com/ppp/purchase.do\" method=\"post\">
<input type=\"hidden\" name=\"encoding\" value=\"utf-8\">
<input type=\"hidden\" name=\"customField1\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"merchant_id\" value=\"" . $params["MerchantID"] . "\">
<input type=\"hidden\" name=\"merchant_site_id\" value=\"" . $params["MerchantSiteID"] . "\">
<input type=\"hidden\" name=\"merchantLocale\" value=\"" . $sMerchantLocale . "\">
<input type=\"hidden\" name=\"first_name\" value=\"" . $params["clientdetails"]["firstname"] . "\">
<input type=\"hidden\" name=\"last_name\" value=\"" . $params["clientdetails"]["lastname"] . "\">
<input type=\"hidden\" name=\"email\" value=\"" . $params["clientdetails"]["email"] . "\">
<input type=\"hidden\" name=\"address1\" value=\"" . $params["clientdetails"]["address1"] . "\">
<input type=\"hidden\" name=\"address2\" value=\"" . $params["clientdetails"]["address2"] . "\">
<input type=\"hidden\" name=\"city\" value=\"" . $params["clientdetails"]["city"] . "\">
<input type=\"hidden\" name=\"country\" value=\"" . $params["clientdetails"]["country"] . "\">
<input type=\"hidden\" name=\"zip\" value=\"" . $params["clientdetails"]["postcode"] . "\">
<input type=\"hidden\" name=\"phone1\" value=\"" . $params["clientdetails"]["phonenumber"] . "\">
<input type=\"hidden\" name=\"version\" value=\"3.0.0\">
<input type=\"hidden\" name=\"currency\" value=\"" . $params["currency"] . "\">
<input type=\"hidden\" name=\"time_stamp\" value=\"" . $sTimestamp . "\">
<input type=\"hidden\" name=\"item_name_1\" value=\"" . $params["description"] . "\" />
<input type=\"hidden\" name=\"item_amount_1\" value=\"" . format_as_currency( $totalAmount - $shipping ) . "\" />
<input type=\"hidden\" name=\"item_quantity_1\" value=1 />
<input type=\"hidden\" name=\"numberofitems\" value=\"" . $numberOfItems . "\">
<input type=\"hidden\" name=\"discount\" value=\"" . $discount . "\">
<input type=\"hidden\" name=\"total_tax\" value=\"" . $total_tax . "\">
<input type=\"hidden\" name=\"total_amount\" value=\"" . $totalAmount . "\">
<input type=\"hidden\" name=\"checksum\" value=\"" . $checksum . "\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["gate2shopname"] = "gate2shop";
$GATEWAYMODULE["gate2shopvisiblename"] = "Gate2Shop";
$GATEWAYMODULE["gate2shoptype"] = "Invoices";
?>