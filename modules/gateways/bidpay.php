<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 * */

function bidpay_activate() {
	defineGatewayField( "bidpay", "text", "sellertoken", "", "Seller Token", "30", "You can find this by logging into your BidPay account and clicking on Direct Payments" );
}


function bidpay_link($params) {
	$code = "
<form action=\"https://sandbox.bidpay.com/DirectPayment/Process.aspx\" method=\"post\">
<input type=\"hidden\" name=\"sellerToken\" value=\"" . $params['sellertoken'] . "\" />
<input type=\"hidden\" name=\"referenceNumber\" value=\"" . $params['invoiceid'] . "\" />
<input type=\"hidden\" name=\"item_0_ItemNumber\" value=\"" . $params['invoiceid'] . "\" />
<input type=\"hidden\" name=\"item_0_ItemDescription\" value=\"" . $params['description'] . "\" />
<input type=\"hidden\" name=\"item_0_Site\" value=\"" . $params['systemurl'] . "\" />
<input type=\"hidden\" name=\"item_0_ItemType\" value=\"WebsitePurchase\" />
<input type=\"hidden\" name=\"item_0_Amount\" value=\"" . $params['amount'] . "\" />
<input type=\"image\" src=\"http://www.bidpay.com/images/PaymentButton/88x31-PayNow.jpg\" alt=\"Pay Now\" />
</form>
";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE['bidpayname'] = "bidpay";
$GATEWAYMODULE['bidpayvisiblename'] = "BidPay";
$GATEWAYMODULE['bidpaytype'] = "Invoices";
?>