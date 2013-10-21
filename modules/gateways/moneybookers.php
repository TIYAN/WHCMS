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

function moneybookers_activate() {
	defineGatewayField( "moneybookers", "text", "merchantemail", "", "Merchant Email", "50", "The email address used to identify you to Skrill" );
	defineGatewayField( "moneybookers", "text", "secretword", "", "Secret Word", "20", "Must match what is set in the Merchant Tools section of your Skrill Account" );
}


function moneybookers_link($params) {
	global $CONFIG;

	$language = $CONFIG["Language"];

	if ($params["clientdetails"]["language"]) {
		$language = $params["clientdetails"]["language"];
	}

	$languagecode = "EN";

	if ($language == "German") {
		$languagecode = "DE";
	}


	if ($language == "Spanish") {
		$languagecode = "ES";
	}


	if ($language == "French") {
		$languagecode = "FR";
	}


	if ($language == "Turkish") {
		$languagecode = "TR";
	}


	if ($language == "Italian") {
		$languagecode = "IT";
	}

	$code = "<form action=\"https://www.moneybookers.com/app/payment.pl\">
<input type=\"hidden\" name=\"pay_to_email\" value=\"" . $params["merchantemail"] . "\">
<input type=\"hidden\" name=\"pay_from_email\" value=\"" . $params["clientdetails"]["email"] . "\">
<input type=\"hidden\" name=\"language\" value=\"" . $languagecode . "\">
<input type=\"hidden\" name=\"amount\" value=\"" . $params["amount"] . "\">
<input type=\"hidden\" name=\"currency\" value=\"" . $params["currency"] . "\">
<input type=\"hidden\" name=\"recipient_description\" value=\"" . $CONFIG["CompanyName"] . "\">
<input type=\"hidden\" name=\"detail1_description\" value=\"" . $params["description"] . "\">
<input type=\"hidden\" name=\"detail1_text\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"return_url\" value=\"" . $params["returnurl"] . "&paymentsuccess=true\">
<input type=\"hidden\" name=\"cancel_url\" value=\"" . $params["returnurl"] . "&paymentfailed=true\">
<input type=\"hidden\" name=\"status_url\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/moneybookers.php\">
<input type=\"hidden\" name=\"transaction_id\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"firstname\" value=\"" . $params["clientdetails"]["firstname"] . "\">
<input type=\"hidden\" name=\"lastname\" value=\"" . $params["clientdetails"]["lastname"] . "\">
<input type=\"hidden\" name=\"address\" value=\"" . $params["clientdetails"]["address1"] . "\">
<input type=\"hidden\" name=\"city\" value=\"" . $params["clientdetails"]["city"] . "\">
<input type=\"hidden\" name=\"state\" value=\"" . $params["clientdetails"]["state"] . "\">
<input type=\"hidden\" name=\"postal_code\" value=\"" . $params["clientdetails"]["postcode"] . "\">
<input type=\"hidden\" name=\"merchant_fields\" value=\"platform\">
<input type=\"hidden\" name=\"platform\" value=\"21477273\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["moneybookersname"] = "moneybookers";
$GATEWAYMODULE["moneybookersvisiblename"] = "Skrill";
$GATEWAYMODULE["moneybookerstype"] = "Invoices";
?>