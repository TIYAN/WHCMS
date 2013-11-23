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

function amazonsimplepay_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Amazon Simple Pay" ), "accesskey" => array( "FriendlyName" => "Access Key", "Type" => "text", "Size" => "40" ), "secretKey" => array( "FriendlyName" => "Secret Key", "Type" => "text", "Size" => "60" ), "testmode" => array( "FriendlyName" => "Sandbox", "Type" => "yesno", "Description" => "" ) );
	return $configarray;
}


function amazonsimplepay_link($params) {
	$gatewayaccesskey = $params["accesskey"];
	$gatewaysecretkey = $params["secretKey"];
	$gatewaytestmode = $params["testmode"];
	$invoiceid = $params["invoiceid"];
	$description = $params["description"];
	$amount = $params["amount"];
	$currency = $params["currency"];
	$firstname = $params["clientdetails"]["firstname"];
	$lastname = $params["clientdetails"]["lastname"];
	$email = $params["clientdetails"]["email"];
	$address1 = $params["clientdetails"]["address1"];
	$address2 = $params["clientdetails"]["address2"];
	$city = $params["clientdetails"]["city"];
	$state = $params["clientdetails"]["state"];
	$postcode = $params["clientdetails"]["postcode"];
	$country = $params["clientdetails"]["country"];
	$phone = $params["clientdetails"]["phone"];
	$companyname = $params["companyname"];
	$systemurl = $params["systemurl"];
	$currency = $params["currency"];
	$input["signatureVersion"] = "2";
	$input["signatureMethod"] = "HmacSHA256";
	$input["immediateReturn"] = "1";
	$input["collectShippingAddress"] = "0";
	$input["accessKey"] = $gatewayaccesskey;
	$input["referenceId"] = $invoiceid;
	$input["amount"] = $currency . " " . $amount;
	$input["variableMarketplaceFee"] = null;
	$input["isDonationWidget"] = "0";
	$input["fixedMarketplaceFee"] = null;
	$input["description"] = $description;
	$input["ipnUrl"] = $systemurl . "/modules/gateways/callback/amazonsimplepay.php";
	$input["returnUrl"] = $systemurl . "/viewinvoice.php?id=" . $invoiceid;
	$input["processImmediate"] = "1";
	$input["cobrandingStyle"] = "logo";
	$input["abandonUrl"] = $systemurl . "/viewinvoice.php?id=" . $invoiceid;
	uksort( $input, "strcmp" );

	if ($gatewaytestmode) {
		$action = "https://authorize.payments-sandbox.amazon.com/pba/paypipeline";
	}
	else {
		$action = "https://authorize.payments.amazon.com/pba/paypipeline";
	}

	$url_parsed = parse_url( $action );
	$url = "POST
";
	$url .= $url_parsed["host"] . "
";
	$url .= $url_parsed["path"] . "
";
	$querystring = array();
	foreach ($input as $key => $value) {
		$querystring[] = $key . "=" . str_replace( "%7E", "~", rawurlencode( $value ) );
	}

	$url .= implode( "&", $querystring );
	$input["signature"] = base64_encode( hash_hmac( "sha256", $url, $gatewaysecretkey, true ) );
	$code = "<form action=\"" . $action . "\" method=\"POST\">";
	foreach ($input as $key => $item) {
		$code .= "<input type=\"hidden\" name=\"" . $key . "\" value=\"" . $item . "\" >";
	}

	$code .= "
    <input type=\"image\" src=\"https://images-na.ssl-images-amazon.com/images/G/01/asp/beige_medium_paynow_withmsg_whitebg.gif\" border=\"0\" >
    </form>";
	return $code;
}


function amazonsimplepay_refund($params) {
	$gatewayaccesskey = $params["accesskey"];
	$gatewaysecretkey = $params["secretKey"];
	$gatewaytestmode = $params["testmode"];
	$transid = $params["transid"];
	$amount = $params["amount"];
	$currency = $params["currency"];
	$firstname = $params["clientdetails"]["firstname"];
	$lastname = $params["clientdetails"]["lastname"];
	$email = $params["clientdetails"]["email"];
	$address1 = $params["clientdetails"]["address1"];
	$address2 = $params["clientdetails"]["address2"];
	$city = $params["clientdetails"]["city"];
	$state = $params["clientdetails"]["state"];
	$postcode = $params["clientdetails"]["postcode"];
	$country = $params["clientdetails"]["country"];
	$phone = $params["clientdetails"]["phone"];
	$cardtype = $params["cardtype"];
	$cardnumber = $params["cardnum"];
	$cardexpiry = $params["cardexp"];
	$cardstart = $params["cardstart"];
	$cardissuenum = $params["cardissuenum"];
	$parameters = array( "Action" => "Refund", "AWSAccessKeyId" => $gatewayaccesskey, "SignatureVersion" => "2", "SignatureMethod" => "HmacSHA256", "Timestamp" => date( DATE_ATOM, time() ), "TransactionId" => $transid, "CallerReference" => "Refund-" . $transid, "Version" => "2008-09-17" );

	if ($gatewaytestmode) {
		$url = "https://fps.sandbox.amazonaws.com";
	}
	else {
		$url = "https://fps.amazonaws.com";
	}

	uksort( $parameters, "strcmp" );
	$url_parsed = parse_url( $url );

	if (empty( $url_parsed["path"] )) {
		$url_parsed["path"] = "/";
	}

	$signature = "POST
";
	$signature .= $url_parsed["host"] . "
";
	$signature .= $url_parsed["path"] . "
";
	$querystring = array();
	foreach ($parameters as $key => $value) {
		$querystring[] = $key . "=" . str_replace( "%7E", "~", rawurlencode( $value ) );
	}

	$signature .= implode( "&", $querystring );
	$parameters["Signature"] = base64_encode( hash_hmac( "sha256", $signature, $gatewaysecretkey, true ) );
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $parameters ) );
	$response = curl_exec( $ch );
	curl_close( $ch );
	$xmlobject = simplexml_load_string( trim( $response ) );
	$results["status"] = (bool)$xmlobject->RefundResult->TransactionStatus;
	$results["transid"] = (bool)$xmlobject->RefundResult->TransactionId;

	if (( $results["status"] == "Success" || $results["status"] == "Pending" )) {
		return array( "status" => "success", "transid" => $results["transid"], "rawdata" => $response );
	}


	if ($results["status"] == "Failure") {
		return array( "status" => "declined", "rawdata" => $response );
	}

	return array( "status" => "error", "rawdata" => $response );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>