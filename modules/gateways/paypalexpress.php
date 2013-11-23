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

function paypalexpress_config() {
	global $CONFIG;

	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "PayPal Express Checkout" ), "apiusername" => array( "FriendlyName" => "API Username", "Type" => "text", "Size" => "50", "Description" => "" ), "apipassword" => array( "FriendlyName" => "API Password", "Type" => "text", "Size" => "30" ), "apisignature" => array( "FriendlyName" => "API Signature", "Type" => "text", "Size" => "75" ), "sandbox" => array( "FriendlyName" => "Sandbox", "Type" => "yesno", "Description" => "Tick to enable test mode" ) );
	return $configarray;
}


function paypalexpress_link($params) {
	$paypalvars = getGatewayVariables( "paypal" );
	$params = array_merge( $params, $paypalvars );
	$params["returnurl"] = $params["systemurl"] . "/viewinvoice.php?id=" . $params["invoiceid"];
	return paypal_link( $params );
}


function paypalexpress_orderformoutput($params) {
	if ($_POST["paypalcheckout"]) {
		$postfields = array();
		$postfields["PAYMENTREQUEST_0_PAYMENTACTION"] = "Sale";
		$postfields["PAYMENTREQUEST_0_AMT"] = $params["amount"];
		$postfields["PAYMENTREQUEST_0_CURRENCYCODE"] = $params["currency"];
		$postfields["RETURNURL"] = $params["systemurl"] . "/modules/gateways/callback/paypalexpress.php";
		$postfields["CANCELURL"] = $params["systemurl"] . "/cart.php?a=view";
		$results = paypalexpress_api_call( $params, "SetExpressCheckout", $postfields );
		$ack = strtoupper( $results["ACK"] );

		if (( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" )) {
			logTransaction( "PayPal Express Token Gen", $results, "Successful" );
			$token = $results["TOKEN"];
			$_SESSION["paypalexpress"]["token"] = $token;
			$PAYPAL_URL = ($params["sandbox"] ? "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=" : "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=");
			header( "Location: " . $PAYPAL_URL . $token );
			exit();
		}
		else {
			logTransaction( "PayPal Express Token Gen", $results, "Error" );
			return "<p>PayPal Checkout Error. Please Contact Support.</p>";
		}
	}

	$code = "<form action=\"cart.php?a=view\" method=\"post\">
<input type=\"hidden\" name=\"paypalcheckout\" value=\"1\" />
<input type=\"image\" name=\"submit\" src=\"https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif\" border=\"0\" align=\"top\" alt=\"Check out with PayPal\" />
</form>";
	return $code;
}


function paypalexpress_orderformcheckout($params) {
	$orderid = get_query_val( "tblorders", "id", array( "invoiceid" => $params["invoiceid"] ) );
	update_query( "tblhosting", array( "paymentmethod" => "paypal" ), array( "orderid" => $orderid, "paymentmethod" => "paypalexpress" ) );
	update_query( "tblhostingaddons", array( "paymentmethod" => "paypal" ), array( "orderid" => $orderid, "paymentmethod" => "paypalexpress" ) );
	update_query( "tbldomains", array( "paymentmethod" => "paypal" ), array( "orderid" => $orderid, "paymentmethod" => "paypalexpress" ) );
	$finalPaymentAmount = $_SESSION["Payment_Amount"];
	$postfields = array();
	$postfields["TOKEN"] = $_SESSION["paypalexpress"]["token"];
	$postfields["PAYERID"] = $_SESSION["paypalexpress"]["payerid"];
	$postfields["PAYMENTREQUEST_0_PAYMENTACTION"] = "SALE";
	$postfields["PAYMENTREQUEST_0_AMT"] = $params["amount"];
	$postfields["PAYMENTREQUEST_0_CURRENCYCODE"] = $params["currency"];
	$postfields["IPADDRESS"] = $_SERVER["SERVER_NAME"];
	$results = paypalexpress_api_call( $params, "DoExpressCheckoutPayment", $postfields );
	$ack = strtoupper( $results["ACK"] );

	if (( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" )) {
		$transactionId = $results["PAYMENTINFO_0_TRANSACTIONID"];
		$transactionType = $results["PAYMENTINFO_0_TRANSACTIONTYPE"];
		$paymentType = $results["PAYMENTINFO_0_PAYMENTTYPE"];
		$orderTime = $results["PAYMENTINFO_0_ORDERTIME"];
		$amt = $results["PAYMENTINFO_0_AMT"];
		$currencyCode = $results["PAYMENTINFO_0_CURRENCYCODE"];
		$feeAmt = $results["PAYMENTINFO_0_FEEAMT"];
		$settleAmt = $results["PAYMENTINFO_0_SETTLEAMT"];
		$taxAmt = $results["PAYMENTINFO_0_TAXAMT"];
		$exchangeRate = $results["PAYMENTINFO_0_EXCHANGERATE"];
		$paymentStatus = $results["PAYMENTINFO_0_PAYMENTSTATUS"];

		if ($paymentStatus == "Completed") {
			return array( "status" => "success", "transid" => $transactionId, "fee" => $feeAmt, "rawdata" => $results );
		}


		if ($paymentStatus == "Pending") {
			return array( "status" => "payment pending", "rawdata" => $results );
		}

		return array( "status" => "invalid status", "rawdata" => $results );
	}

	return array( "status" => "error", "rawdata" => $results );
}


function paypalexpress_api_call($params, $methodName, $postfields) {
	$sBNCode = "WHMCS_ECWizard";
	$API_UserName = $params["apiusername"];
	$API_Password = $params["apipassword"];
	$API_Signature = $params["apisignature"];
	$API_Endpoint = ($params["sandbox"] ? "https://api-3t.sandbox.paypal.com/nvp" : "https://api-3t.paypal.com/nvp");
	$postfields["METHOD"] = $methodName;
	$postfields["VERSION"] = $version;
	$postfields["PWD"] = $API_Password;
	$postfields["USER"] = $API_UserName;
	$postfields["SIGNATURE"] = $API_Signature;
	$postfields["BUTTONSOURCE"] = $sBNCode;
	$nvpreq = "";
	foreach ($postfields as $k => $v) {
		$nvpreq .= "" . $k . "=" . urlencode( $v ) . "&";
	}

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $API_Endpoint );
	curl_setopt( $ch, CURLOPT_VERBOSE, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $nvpreq );
	curl_exec( $ch );
	$response = $version = "64";

	if (curl_errno( $ch )) {
	}

	curl_close( $ch );
	return paypalexpress_deformatNVP( $response );
}


function paypalexpress_deformatNVP($nvpstr) {
	$intial = 5;
	$nvpArray = array();

	while (strlen( $nvpstr )) {
		$keypos = strpos( $nvpstr, "=" );
		$valuepos = (strpos( $nvpstr, "&" ) ? strpos( $nvpstr, "&" ) : strlen( $nvpstr ));
		$keyval = substr( $nvpstr, $intial, $keypos );
		$valval = substr( $nvpstr, $keypos + 1, $valuepos - $keypos - 1 );
		$nvpArray[urldecode( $keyval )] = urldecode( $valval );
		$nvpstr = substr( $nvpstr, $valuepos + 1, strlen( $nvpstr ) );
	}

	return $nvpArray;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>