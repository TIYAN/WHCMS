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

function stormpay_activate() {
	global $GATEWAYMODULE;

	defineGatewayField( "stormpay", "text", "email", "", "StormPay Email", "50", "" );
	defineGatewayField( "stormpay", "yesno", "demomode", "", "Demo Mode", "", "" );
}


function stormpay_link($description, $firstpaymentamount, $recurringamount, $paymentterm, $ordernumber, $returnurl, $period = "1") {
	global $GATEWAY;

	if ($paymentterm == "Y") {
		$duration = 365 * $period;
	}
	else {
		if ($paymentterm == "Monthly") {
			$duration = "30";
		}


		if ($paymentterm == "Quarterly") {
			$duration = "90";
		}


		if ($paymentterm == "Semi-Annually") {
			$duration = "180";
		}


		if ($paymentterm == "Annually") {
			$duration = "365";
		}
	}


	if (( $recurringamount == "0.00" || $recurringamount == "" ) || $recurringamount == "0") {
		$code = "
<form method=\"post\" action=\"https://www.stormpay.com/stormpay/handle_gen.php\" name=\"paymentfrm\">
<input type=hidden name=payee_email value=\"" . $GATEWAY['email'] . "\">
<input type=hidden name=product_name value=\"" . $description . "\">
<input type=hidden name=unit_price value=\"" . $firstpaymentamount . "\">";

		if ($GATEWAY['demomode'] == "on") {
			$code .= "<input type=\"hidden\" name=\"test_mode\" value=\"1\">";
		}

		$code .= "<input type=\"submit\" value=\"" . $GATEWAY['LangPayNow'] . "\">
</form>
<SCRIPT LANGUAGE=\"JavaScript\">
window.document.paymentfrm.submit();
</SCRIPT>
";
	}
	else {
		$code = "
<form method=\"post\" action=\"https://www.stormpay.com/stormpay/handle_gen.php\" name=\"paymentfrm\">
<input type=\"hidden\" name=\"generic\" value=\"1\">
<input type=\"hidden\" name=\"payee_email\" value=\"" . $GATEWAY['email'] . "\">
<input type=\"hidden\" name=\"product_name\" value=\"" . $description . "\">
<input type=\"hidden\" name=\"subscription\" value=\"YES\">
<input type=\"hidden\" name=\"subscription\" value=\"" . $recurringamount . " ~ every " . $duration . " days ~ ~ ";

		if ($recurringamount < $firstpaymentamount) {
			$code .= "with " . ( $firstpaymentamount - $recurringamount ) . " setup fee";
		}

		$code .= " ~ ";

		if ($firstpaymentamount == "0") {
			$code .= "and " . $duration . " days trial period";
		}

		$code .= "\">
<input type=\"hidden\" name=\"require_IPN\" value=\"1\">
<input type=\"hidden\" name=\"notify_URL\" value=\"" . $GATEWAY['SystemURL'] . "/modules/gateways/callback/stormpay.php\">
<input type=\"hidden\" name=\"return_URL\" value=\"" . $GATEWAY['SystemURL'] . "/" . $returnurl . "\">
<input type=\"hidden\" name=\"cancel_URL\" value=\"" . $GATEWAY['SystemURL'] . "\">
<input type=\"hidden\" name=\"user1\" value=\"" . $ordernumber . "\">";

		if ($GATEWAY['demomode'] == "on") {
			$code .= "<input type=\"hidden\" name=\"test_mode\" value=\"1\">";
		}

		$code .= "<input type=\"submit\" value=\"" . $GATEWAY['LangPayNow'] . "\">
</form>
<SCRIPT LANGUAGE=\"JavaScript\">
window.document.paymentfrm.submit();
</SCRIPT>
";
	}

	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE['stormpayname'] = "stormpay";
$GATEWAYMODULE['stormpayvisiblename'] = "StormPay";
$GATEWAYMODULE['stormpaytype'] = "Subscriptions";
$GATEWAYMODULE['stormpaynotes'] = "You should enable IPN in your StormPay account and leave the post back url blank. Make Sure Receive IPN on the following events are ticked Subscriptions creation and Subscription payments received.  StormPay will not work with One Time Discounts or Setup Fees.";
?>