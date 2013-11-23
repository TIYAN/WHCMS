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

function nochex_activate() {
	defineGatewayField( "nochex", "text", "email", "", "NoChex Merchant ID", "50", "This is the email you have registered with NoChex" );
	defineGatewayField( "nochex", "yesno", "hide", "", "Hide Details", "0", "Tick to stop customer details being repeated on Nochex payment page" );
	defineGatewayField( "nochex", "yesno", "testmode", "", "Test Mode", "0", "Tick to enable test transaction mode" );
}


function nochex_link($params) {
	$code = "<form action=\"https://secure.nochex.com/\" method=\"post\">
<input type=hidden name=merchant_id value=\"" . $params["email"] . "\">
<input type=hidden name=amount value=\"" . $params["amount"] . "\">
<input type=hidden name=order_id value=\"" . $params["invoiceid"] . "\">
<input type=hidden name=description value=\"" . $params["description"] . "\">
<input type=hidden name=billing_fullname value=\"" . $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"] . "\">
<input type=hidden name=billing_address value=\"" . $params["clientdetails"]["address1"] . "
" . $params["clientdetails"]["address2"] . "
" . $params["clientdetails"]["city"] . "
" . $params["clientdetails"]["state"] . "
" . $params["clientdetails"]["country"] . "\">
<input type=hidden name=billing_postcode value=\"" . $params["clientdetails"]["postcode"] . "\">
<input type=hidden name=customer_phone_number value=\"" . $params["clientdetails"]["phonenumber"] . "\">
<input type=hidden name=email_address value=\"" . $params["clientdetails"]["email"] . "\">
<input type=hidden name=success_url value=\"" . $params["systemurl"] . "/viewinvoice.php?id=" . $params["invoiceid"] . "&paymentsuccess=true\">
<input type=hidden name=cancel_url value=\"" . $params["systemurl"] . "/viewinvoice.php?id=" . $params["invoiceid"] . "&paymentfailed=true\">
<input type=hidden name=decline_url value=\"" . $params["systemurl"] . "/viewinvoice.php?id=" . $params["invoiceid"] . "&paymentfailed=true\">
<input type=hidden name=responderurl value=\"" . $params["systemurl"] . "/modules/gateways/callback/nochex.php\">
<input type=hidden name=callback_url value=\"" . $params["systemurl"] . "/modules/gateways/callback/nochex.php\">
";

	if ($params["hide"]) {
		$code .= "<input type=hidden name=hide_billing_details value=\"true\">";
	}


	if ($params["testmode"]) {
		$code .= "<input type=hidden name=test_transaction value=\"100\">
<input type=hidden name=test_success_url value=\"" . $params["systemurl"] . "/viewinvoice.php?id=" . $params["invoiceid"] . "\">";
	}

	$code .= "
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["nochexname"] = "nochex";
$GATEWAYMODULE["nochexvisiblename"] = "NoChex";
$GATEWAYMODULE["nochextype"] = "Invoices";
?>