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

function googlecheckout_activate() {
	defineGatewayField( "googlecheckout", "text", "merchantid", "", "Merchant ID", "30", "" );
	defineGatewayField( "googlecheckout", "text", "merchantkey", "", "Merchant Key", "40", "" );
	defineGatewayField( "googlecheckout", "yesno", "sandbox", "", "Sandbox Mode", "", "" );
}


function googlecheckout_link($params) {
	if ($params['sandbox']) {
		$url = "https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/";
	}
	else {
		$url = "https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/";
	}

	$result = select_query( "tblinvoiceitems", "description", array( "invoiceid" => $params['invoiceid'] ) );

	while ($data = mysql_fetch_array( $result )) {
		$params->description .= " - " . $data[0];
	}

	$code = "<form action=\"" . $url . $params['merchantid'] . "\" id=\"BB_BuyButtonForm\" method=\"post\" name=\"BB_BuyButtonForm\">
    <input name=\"item_name_1\" type=\"hidden\" value=\"Invoice Payment\"/>
    <input name=\"item_description_1\" type=\"hidden\" value=\"" . $params['description'] . "\"/>
    <input name=\"item_quantity_1\" type=\"hidden\" value=\"1\"/>
    <input name=\"item_price_1\" type=\"hidden\" value=\"" . $params['amount'] . "\"/>
    <input name=\"item_currency_1\" type=\"hidden\" value=\"" . $params['currency'] . "\"/>
    <input name=\"item_merchant_id_1\" type=\"hidden\" value=\"" . $params['invoiceid'] . "\"/>
    <input type=\"hidden\" name=\"shopping-cart.items.item-1.digital-content.email-delivery\" value=\"true\" />
    <input name=\"continue_url\" type=\"hidden\" value=\"" . $params['systemurl'] . "/viewinvoice.php?id=" . $params['invoiceid'] . "\"/>
    <input name=\"_charset_\" type=\"hidden\" value=\"utf-8\"/>
    <input type=\"image\" name=\"Google Checkout\" alt=\"Fast checkout through Google\" src=\"https://checkout.google.com/buttons/checkout.gif?merchant_id=" . $params['merchantid'] . "&w=180&h=46&style=white&variant=text&loc=en_US\" height=\"46\" width=\"180\"/>
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE['googlecheckoutname'] = "googlecheckout";
$GATEWAYMODULE['googlecheckoutvisiblename'] = "Google Checkout";
$GATEWAYMODULE['googlecheckouttype'] = "Invoices";
$GATEWAYMODULE['googlecheckoutnotes'] = "In order to use Google Checkout in a live environment, you must have an SSL certificate. Inside your Google Checkout account you need to go to <i>Settings > Preferences > Order processing preferences</i> and select the option <i>Automatically authorize and charge the buyer's credit card.</i>  Also, in <i>Settings > Integration</i> you must enter the following callback url: " . $CONFIG['SystemSSLURL'] . "/modules/gateways/callback/googlecheckout.php";
?>