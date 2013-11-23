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

function paypal_config() {
	global $CONFIG;

	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "PayPal" ), "UsageNotes" => array( "Type" => "System", "Value" => "You must enable IPN inside your PayPal account and set the URL to " . $CONFIG["SystemURL"] ), "email" => array( "FriendlyName" => "PayPal Email", "Type" => "text", "Size" => "40" ), "forceonetime" => array( "FriendlyName" => "Force One Time Payments", "Type" => "yesno", "Description" => "Never show the subscription payment button" ), "forcesubscriptions" => array( "FriendlyName" => "Force Subscriptions", "Type" => "yesno", "Description" => "Hide the one time payment button when a subscription can be created" ), "requireshipping" => array( "FriendlyName" => "Require Shipping Address", "Type" => "yesno", "Description" => "Tick this box to request a shipping address from a user on PayPal's site" ), "overrideaddress" => array( "FriendlyName" => "Client Address Matching", "Type" => "yesno", "Description" => "Tick this box to force using client profile information entered into WHMCS at PayPal" ), "apiusername" => array( "FriendlyName" => "API Username", "Type" => "text", "Size" => "40", "Description" => "API fields only required for refunds" ), "apipassword" => array( "FriendlyName" => "API Password", "Type" => "text", "Size" => "40" ), "apisignature" => array( "FriendlyName" => "API Signature", "Type" => "text", "Size" => "70" ) );
	return $configarray;
}


function paypal_link($params) {
	global $CONFIG;

	$invoiceid = $params["invoiceid"];
	$paypalemails = $params["email"];
	$paypalemails = explode( ",", $paypalemails );
	$paypalemail = trim( $paypalemails[0] );
	$recurrings = getRecurringBillingValues( $invoiceid );
	$primaryserviceid = $recurrings["primaryserviceid"];
	$firstpaymentamount = $recurrings["firstpaymentamount"];
	$firstcycleperiod = $recurrings["firstcycleperiod"];
	$firstcycleunits = strtoupper( substr( $recurrings["firstcycleunits"], 0, 1 ) );
	$recurringamount = $recurrings["recurringamount"];
	$recurringcycleperiod = $recurrings["recurringcycleperiod"];
	$recurringcycleunits = strtoupper( substr( $recurrings["recurringcycleunits"], 0, 1 ) );

	if (( $params["clientdetails"]["country"] == "US" || $params["clientdetails"]["country"] == "CA" )) {
		$phonenumber = preg_replace( "/[^0-9]/", "", $params["clientdetails"]["phonenumber"] );
		$phone1 = substr( $phonenumber, 0, 3 );
		$phone2 = substr( $phonenumber, 3, 3 );
		$phone3 = substr( $phonenumber, 6 );
	}
	else {
		$phone1 = $params["clientdetails"]["phonecc"];
		$phone2 = $params["clientdetails"]["phonenumber"];
	}

	$subnotpossible = false;

	if (!$recurrings) {
		$subnotpossible = true;
	}


	if ($recurrings["overdue"]) {
		$subnotpossible = true;
	}


	if ($params["forceonetime"]) {
		$subnotpossible = true;
	}


	if ($recurringamount <= 0) {
		$subnotpossible = true;
	}


	if (( 90 < $firstcycleperiod && $firstcycleunits == "D" )) {
		$subnotpossible = true;
	}


	if (( 24 < $firstcycleperiod && $firstcycleunits == "M" )) {
		$subnotpossible = true;
	}


	if (( 5 < $firstcycleperiod && $firstcycleunits == "Y" )) {
		$subnotpossible = true;
	}

	$code = "<table><tr>";

	if (!$subnotpossible) {
		$code .= "<td><form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" name=\"paymentfrm\">
<input type=\"hidden\" name=\"cmd\" value=\"_xclick-subscriptions\">
<input type=\"hidden\" name=\"business\" value=\"" . $paypalemail . "\">
<input type=\"hidden\" name=\"item_name\" value=\"" . $params["description"] . "\">
<input type=\"hidden\" name=\"no_shipping\" value=\"" . ($params["requireshipping"] ? "2" : "1") . "\">
<input type=\"hidden\" name=\"address_override\" value=\"" . ($params["overrideaddress"] ? "1" : "0") . "\">
<input type=\"hidden\" name=\"first_name\" value=\"" . $params["clientdetails"]["firstname"] . "\">
<input type=\"hidden\" name=\"last_name\" value=\"" . $params["clientdetails"]["lastname"] . "\">
<input type=\"hidden\" name=\"address1\" value=\"" . $params["clientdetails"]["address1"] . "\">
<input type=\"hidden\" name=\"city\" value=\"" . $params["clientdetails"]["city"] . "\">
<input type=\"hidden\" name=\"state\" value=\"" . $params["clientdetails"]["state"] . "\">
<input type=\"hidden\" name=\"zip\" value=\"" . $params["clientdetails"]["postcode"] . "\">
<input type=\"hidden\" name=\"country\" value=\"" . $params["clientdetails"]["country"] . "\">
<input type=\"hidden\" name=\"night_phone_a\" value=\"" . $phone1 . "\">
<input type=\"hidden\" name=\"night_phone_b\" value=\"" . $phone2 . "\">";

		if ($phone3) {
			$code .= "<input type=\"hidden\" name=\"night_phone_c\" value=\"" . $phone3 . "\">";
		}

		$code .= "<input type=\"hidden\" name=\"no_note\" value=\"1\">
<input type=\"hidden\" name=\"currency_code\" value=\"" . $params["currency"] . "\">
<input type=\"hidden\" name=\"bn\" value=\"WHMCS_ST\">";

		if ($firstpaymentamount) {
			$code .= "
<input type=\"hidden\" name=\"a1\" value=\"" . $firstpaymentamount . "\">
<input type=\"hidden\" name=\"p1\" value=\"" . $firstcycleperiod . "\">
<input type=\"hidden\" name=\"t1\" value=\"" . $firstcycleunits . "\">";
		}

		$code .= "
<input type=\"hidden\" name=\"a3\" value=\"" . $recurringamount . "\">
<input type=\"hidden\" name=\"p3\" value=\"" . $recurringcycleperiod . "\">
<input type=\"hidden\" name=\"t3\" value=\"" . $recurringcycleunits . "\">
<input type=\"hidden\" name=\"src\" value=\"1\">
<input type=\"hidden\" name=\"sra\" value=\"1\">
<input type=\"hidden\" name=\"charset\" value=\"" . $CONFIG["Charset"] . "\">
<input type=\"hidden\" name=\"custom\" value=\"" . $primaryserviceid . "\">
<input type=\"hidden\" name=\"return\" value=\"" . $params["returnurl"] . "&paymentsuccess=true\">
<input type=\"hidden\" name=\"cancel_return\" value=\"" . $params["returnurl"] . "&paymentfailed=true\">
<input type=\"hidden\" name=\"notify_url\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/paypal.php\">
<input type=\"hidden\" name=\"rm\" value=\"2\">";

		if (( !$firstpaymentamount && $params["modifysubscriptions"] )) {
			$code .= "
<input type=\"hidden\" name=\"modify\" value=\"1\">";
		}

		$code .= "
<input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/x-click-but20.gif\" border=\"0\" name=\"submit\" alt=\"Subscribe with PayPal for Automatic Payments\">
</form></td>";
	}


	if (( ( !$subnotpossible && $params["forcesubscriptions"] ) && !$params["forceonetime"] )) {
	}
	else {
		$code .= "<td><form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">
<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
<input type=\"hidden\" name=\"business\" value=\"" . $paypalemail . "\">";

		if ($params["style"]) {
			$code .= "<input type=\"hidden\" name=\"page_style\" value=\"" . $params["style"] . "\">";
		}

		$code .= "<input type=\"hidden\" name=\"item_name\" value=\"" . $params["description"] . "\">
<input type=\"hidden\" name=\"amount\" value=\"" . $params["amount"] . "\">
<input type=\"hidden\" name=\"tax\" value=\"0.00\">
<input type=\"hidden\" name=\"no_note\" value=\"1\">
<input type=\"hidden\" name=\"no_shipping\" value=\"" . ($params["requireshipping"] ? "2" : "1") . "\">
<input type=\"hidden\" name=\"address_override\" value=\"" . ($params["overrideaddress"] ? "1" : "0") . "\">
<input type=\"hidden\" name=\"first_name\" value=\"" . $params["clientdetails"]["firstname"] . "\">
<input type=\"hidden\" name=\"last_name\" value=\"" . $params["clientdetails"]["lastname"] . "\">
<input type=\"hidden\" name=\"address1\" value=\"" . $params["clientdetails"]["address1"] . "\">
<input type=\"hidden\" name=\"city\" value=\"" . $params["clientdetails"]["city"] . "\">
<input type=\"hidden\" name=\"state\" value=\"" . $params["clientdetails"]["state"] . "\">
<input type=\"hidden\" name=\"zip\" value=\"" . $params["clientdetails"]["postcode"] . "\">
<input type=\"hidden\" name=\"country\" value=\"" . $params["clientdetails"]["country"] . "\">
<input type=\"hidden\" name=\"night_phone_a\" value=\"" . $phone1 . "\">
<input type=\"hidden\" name=\"night_phone_b\" value=\"" . $phone2 . "\">";

		if ($phone3) {
			$code .= "<input type=\"hidden\" name=\"night_phone_c\" value=\"" . $phone3 . "\">";
		}

		$code .= "<input type=\"hidden\" name=\"charset\" value=\"" . $CONFIG["Charset"] . "\">
<input type=\"hidden\" name=\"currency_code\" value=\"" . $params["currency"] . "\">
<input type=\"hidden\" name=\"custom\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"return\" value=\"" . $params["returnurl"] . "&paymentsuccess=true\">
<input type=\"hidden\" name=\"cancel_return\" value=\"" . $params["returnurl"] . "&paymentfailed=true\">
<input type=\"hidden\" name=\"notify_url\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/paypal.php\">
<input type=\"hidden\" name=\"bn\" value=\"WHMCS_ST\">
<input type=\"hidden\" name=\"rm\" value=\"2\">
<input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/x-click-but03.gif\" border=\"0\" name=\"submit\" alt=\"Make a one time payment with PayPal\">
</form></td>";
	}

	$code .= "</tr></table>";
	return $code;
}


function paypal_refund($params) {
	if ($params["sandbox"]) {
		$url = "https://api-3t.sandbox.paypal.com/nvp";
	}
	else {
		$url = "https://api-3t.paypal.com/nvp";
	}

	$postfields = array();
	$postfields["VERSION"] = "3.0";
	$postfields["METHOD"] = "RefundTransaction";
	$postfields["BUTTONSOURCE"] = "WHMCS_WPP_DP";
	$postfields["USER"] = $params["apiusername"];
	$postfields["PWD"] = $params["apipassword"];
	$postfields["SIGNATURE"] = $params["apisignature"];
	$postfields["TRANSACTIONID"] = $params["transid"];
	$postfields["REFUNDTYPE"] = "Partial";
	$postfields["AMT"] = $params["amount"];
	$postfields["CURRENCYCODE"] = $params["currency"];
	$result = curlCall( $url, $postfields );
	$resultsarray2 = explode( "&", $result );
	foreach ($resultsarray2 as $line) {
		$line = explode( "=", $line );
		$resultsarray[$line[0]] = urldecode( $line[1] );
	}


	if (strtoupper( $resultsarray["ACK"] ) == "SUCCESS") {
		return array( "status" => "success", "rawdata" => $resultsarray, "transid" => $resultsarray["REFUNDTRANSACTIONID"], "fees" => $resultsarray["FEEREFUNDAMT"] );
	}

	return array( "status" => "error", "rawdata" => $resultsarray );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>