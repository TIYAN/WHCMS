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

function quantumgateway_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Quantum Gateway" ), "loginid" => array( "FriendlyName" => "Login ID", "Type" => "text", "Size" => "20" ), "transkey" => array( "FriendlyName" => "Restrict Key", "Type" => "text", "Size" => "20", "Description" => "In the Processing Settings area of your QG Account" ), "md5hash" => array( "FriendlyName" => "MD5 Hash", "Type" => "text", "Size" => "20", "Description" => "In the Processing Settings area of your QG Account" ), "maxmind" => array( "FriendlyName" => "MaxMind Fraud Control", "Type" => "yesno", "Description" => "Tick this box to use MaxMind Fraud Control" ) );
	return $configarray;
}


function quantumgateway_3dsecure($params) {
	if ($params["maxmind"]) {
		$params["maxmind"] = "1";
	}
	else {
		$params["maxmind"] = "2";
	}

	$code = "<form method=\"post\" action=\"https://secure.quantumgateway.com/cgi/qgwdbe.php\" name=\"paymentfrm\">
<input type=\"hidden\" name=\"gwlogin\" value=\"" . $params["loginid"] . "\">
<input type=\"hidden\" name=\"post_return_url_approved\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/quantumthreedsecure.php\">
<input type=\"hidden\" name=\"post_return_url_declined\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/quantumthreedsecure.php\">
<input type=\"hidden\" name=\"amount\" value=\"" . $params["amount"] . "\">
<input type=\"hidden\" name=\"ID\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"FNAME\" value=\"" . $params["clientdetails"]["firstname"] . "\">
<input type=\"hidden\" name=\"LNAME\" value=\"" . $params["clientdetails"]["lastname"] . "\">
<input type=\"hidden\" name=\"BADDR1\" value=\"" . $params["clientdetails"]["address1"] . "\">
<input type=\"hidden\" name=\"BCITY\" value=\"" . $params["clientdetails"]["city"] . "\">
<input type=\"hidden\" name=\"BSTATE\" value=\"" . $params["clientdetails"]["state"] . "\">
<input type=\"hidden\" name=\"BZIP1\" value=\"" . $params["clientdetails"]["postcode"] . "\">
<input type=\"hidden\" name=\"BCOUNTRY\" value=\"" . $params["clientdetails"]["country"] . "\">
<input type=\"hidden\" name=\"PHONE\" value=\"" . $params["clientdetails"]["phonenumber"] . "\">
<input type=\"hidden\" name=\"BCUST_EMAIL\" value=\"" . $params["clientdetails"]["email"] . "\">
<input type=\"hidden\" name=\"company_logo\" value=\"" . $params["companylogourl"] . "\">
<input type=\"hidden\" name=\"RestrictKey\" value=\"" . $params["transkey"] . "\">
<input type=\"hidden\" name=\"trans_method\" value=\"CC\">
<input type=\"hidden\" name=\"ccnum\" value=\"" . $params["cardnum"] . "\">
<input type=\"hidden\" name=\"ccmo\" value=\"" . substr( $params["cardexp"], 0, 2 ) . "\">
<input type=\"hidden\" name=\"ccyr\" value=\"" . substr( $params["cardexp"], 2, 2 ) . "\">
<input type=\"hidden\" name=\"MAXMIND\" value=\"" . $params["maxmind"] . "\">
";

	if ($params["cccvv"]) {
		$code .= "<input type=\"hidden\" name=\"CVV2\" value=\"" . $params["cccvv"] . "\">
<input type=\"hidden\" name=\"CVVtype\" value=\"1\">
";
	}
	else {
		$code .= "<input type=\"hidden\" name=\"CVVtype\" value=\"0\">
";
	}

	$code .= "<input type=\"hidden\" name=\"ResponseMethod\" value=\"GET\">
<noscript>
<div class=\"errorbox\"><b>JavaScript is currently disabled or is not supported by your browser.</b><br />Please click the continue button to proceed with the processing of your transaction.</div>
<p align=\"center\"><input type=\"submit\" value=\"Continue >>\" /></p>
</noscript>
</form>";
	return $code;
}


function quantumgateway_capture($params) {
	$url = "https://secure.quantumgateway.com/cgi/xml_requester.php";
	$fields = array();
	$fields["RequestType"] = "ProcessSingleTransaction";
	$fields["TransactionType"] = "CREDIT";
	$fields["ProcessType"] = ($params["cccvv"] ? "AUTH_CAPTURE" : "SALES");
	$fields["PaymentType"] = "CC";
	$fields["Amount"] = $params["amount"];
	$fields["CreditCardNumber"] = $params["cardnum"];
	$fields["ExpireMonth"] = substr( $params["cardexp"], 0, 2 );
	$fields["ExpireYear"] = "20" . substr( $params["cardexp"], 2, 2 );
	$fields["CVV2"] = $params["cccvv"];
	$fields["FirstName"] = $params["clientdetails"]["firstname"];
	$fields["LastName"] = $params["clientdetails"]["lastname"];
	$fields["Address"] = $params["clientdetails"]["address1"];
	$fields["City"] = $params["clientdetails"]["city"];
	$fields["State"] = $params["clientdetails"]["state"];
	$fields["ZipCode"] = $params["clientdetails"]["postcode"];
	$fields["Country"] = $params["clientdetails"]["country"];
	$fields["EmailAddress"] = $params["clientdetails"]["email"];
	$fields["PhoneNumber"] = $params["clientdetails"]["phonenumber"];
	$fields["InvoiceNumber"] = $params["invoiceid"];
	$xml = "<QGWRequest>
<Authentication>
<GatewayLogin>" . $params["loginid"] . "</GatewayLogin>
<GatewayKey>" . $params["transkey"] . "</GatewayKey>
</Authentication>
<Request>
";
	foreach ($fields as $k => $v) {
		$xml .= "<" . $k . ">" . $v . "</" . $k . ">
";
	}

	$xml .= "</Request>
</QGWRequest>";
	$data = curlCall( $url, "xml=" . $xml );
	$results = XMLtoArray( $data );

	if ($results["QGWREQUEST"]["RESULT"]["STATUS"] == "APPROVED") {
		return array( "status" => "success", "transid" => $results["QGWREQUEST"]["RESULT"]["TRANSACTIONID"], "rawdata" => $results["QGWREQUEST"]["RESULT"] );
	}

	return array( "status" => "error", "rawdata" => $data );
}


function quantumgateway_refund($params) {
	if (!$params["cardlastfour"]) {
		$url = "https://secure.quantumgateway.com/cgi/xml_requester.php";
		$prexml = "<QGWRequest>
<Authentication>
<GatewayLogin>" . $params["loginid"] . "</GatewayLogin>
<GatewayKey>" . $params["transkey"] . "</GatewayKey>
</Authentication>
<Request>
<RequestType>ShowTransactionDetails</RequestType>
<TransactionID>" . $params["transid"] . "</TransactionID>
</Request>
</QGWRequest>";
		$predata = curlCall( $url, "xml=" . $prexml );
		$preresults = XMLtoArray( $predata );

		if ($preresults["QGWREQUEST"]["RESULT"]["PAYMENTTYPE"] == "CC") {
			$params["cardlastfour"] = $preresults["QGWREQUEST"]["RESULT"]["CREDITCARDNUMBER"];
		}
		else {
			return array( "status" => "error", "rawdata" => "Original Payment not made by CC " . $predata );
		}
	}

	$url = "https://secure.quantumgateway.com/cgi/xml_requester.php";
	$xml = "<QGWRequest>
<Authentication>
<GatewayLogin>" . $params["loginid"] . "</GatewayLogin>
<GatewayKey>" . $params["transkey"] . "</GatewayKey>
</Authentication>
<Request>
<RequestType>ProcessSingleTransaction</RequestType>
<ProcessType>RETURN</ProcessType>
<PaymentType>CC</PaymentType>
<Amount>" . $params["amount"] . "</Amount>
<TransactionID>" . $params["transid"] . "</TransactionID>
<CreditCardNumber>" . $params["cardlastfour"] . "</CreditCardNumber>
</Request>
</QGWRequest>";
	$data = curlCall( $url, "xml=" . $xml );
	$results = XMLtoArray( $data );

	if ($results["QGWREQUEST"]["RESULT"]["STATUS"] == "APPROVED") {
		return array( "status" => "success", "transid" => $results["QGWREQUEST"]["RESULT"]["TRANSACTIONID"], "rawdata" => $results["QGWREQUEST"]["RESULT"] );
	}

	return array( "status" => "error", "rawdata" => $data );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>