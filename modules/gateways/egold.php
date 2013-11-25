<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

function egold_activate() {
	defineGatewayField( "egold", "text", "accountid", "", "E-Gold ID", "20", "ID used to identify you to E-Gold" );
}


function egold_link($params) {
	if ($params["currency"] == "USD") {
		$currency = "1";
	}


	if ($params["currency"] == "CAD") {
		$currency = "2";
	}


	if ($params["currency"] == "GBP") {
		$currency = "44";
	}


	if ($params["currency"] == "AUD") {
		$currency = "61";
	}


	if ($params["currency"] == "JPY") {
		$currency = "81";
	}


	if ($params["currency"] == "EUR") {
		$currency = "85";
	}


	if ($params["currency"] == "LTL") {
		$currency = "97";
	}

	$strURL = "https://www.e-gold.com/sci_asp/payments.asp?";
	$strURL .= "PAYEE_ACCOUNT=" . $params["accountid"];
	$strURL .= "&PAYEE_NAME=" . $params["companyname"];
	$strURL .= "&PAYMENT_ID=" . $params["invoiceid"];
	$strURL .= "&PAYMENT_AMOUNT=" . $params["amount"];
	$strURL .= "&STATUS_URL=" . $params["systemurl"] . "/modules/gateways/callback/egold.php";
	$strURL .= "&PAYMENT_URL=" . $params["returnurl"];
	$strURL .= "&NOPAYMENT_URL=" . $params["returnurl"];
	$strURL .= "&STATUS_URL_METHOD=LINK";
	$strURL .= "&PAYMENT_URL_METHOD=LINK";
	$strURL .= "&NOPAYMENT_URL_METHOD=LINK";
	$strURL .= "&PAYMENT_UNITS=" . $currency;
	$strURL .= "&PAYMENT_METAL_ID=1";
	$strURL .= "&BAGGAGE_FIELDS=";
	$strURL .= "&SUGGESTED_MEMO=Invoice " . $params["invoiceid"];
	$code = "
<form action=\"" . $strURL . "\" method=\"post\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>
";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["egoldname"] = "egold";
$GATEWAYMODULE["egoldvisiblename"] = "E-Gold";
$GATEWAYMODULE["egoldtype"] = "Invoices";
?>