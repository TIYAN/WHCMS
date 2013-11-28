<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

function quantumvault_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Quantum Vault" ), "loginid" => array( "FriendlyName" => "Login ID", "Type" => "text", "Size" => "20" ), "transkey" => array( "FriendlyName" => "Restrict Key", "Type" => "text", "Size" => "20", "Description" => "In the Processing Settings area of your QG Account" ), "apiusername" => array( "FriendlyName" => "API Username", "Type" => "text", "Size" => "20", "Description" => "Go to Processing Settings > Inline Frame API, set API Enabled = Y and generate Username & API Key" ), "apikey" => array( "FriendlyName" => "API Key", "Type" => "text", "Size" => "20" ), "vaultkey" => array( "FriendlyName" => "Vault Key", "Type" => "text", "Size" => "20", "Description" => "Set in Processing Tools > Secure Vault > Vault Config" ), "md5hash" => array( "FriendlyName" => "MD5 Hash", "Type" => "text", "Size" => "20", "Description" => "Also in the Processing Settings area of your Quantum Account" ), "testmode" => array( "FriendlyName" => "Test Module", "Type" => "yesno" ) );
	return $configarray;
}


function quantumvault_nolocalcc() {
}


function quantumvault_remoteinput($params) {
	$code = "<form method=\"post\" action=\"https://secure.quantumgateway.com/cgi/qgwdbe.php\">
<input type=\"hidden\" name=\"gwlogin\" value=\"" . $params["loginid"] . "\" />
<input type=\"hidden\" name=\"RestrictKey\" value=\"" . $params["transkey"] . "\" />
<input type=\"hidden\" name=\"amount\" value=\"" . $params["amount"] . "\" />
<input type=\"hidden\" name=\"ID\" value=\"" . $params["invoiceid"] . "\" />
<input type=\"hidden\" name=\"FNAME\" value=\"" . $params["clientdetails"]["firstname"] . "\" />
<input type=\"hidden\" name=\"LNAME\" value=\"" . $params["clientdetails"]["lastname"] . "\" />
<input type=\"hidden\" name=\"BADDR1\" value=\"" . $params["clientdetails"]["address1"] . "\" />
<input type=\"hidden\" name=\"BCITY\" value=\"" . $params["clientdetails"]["city"] . "\" />
<input type=\"hidden\" name=\"BSTATE\" value=\"" . $params["clientdetails"]["state"] . "\" />
<input type=\"hidden\" name=\"BZIP1\" value=\"" . $params["clientdetails"]["postcode"] . "\" />
<input type=\"hidden\" name=\"BCOUNTRY\" value=\"" . $params["clientdetails"]["country"] . "\" />
<input type=\"hidden\" name=\"PHONE\" value=\"" . $params["clientdetails"]["phonenumber"] . "\" />
<input type=\"hidden\" name=\"BCUST_EMAIL\" value=\"" . $params["clientdetails"]["email"] . "\" />
<input type=\"hidden\" name=\"AddToVault\" value=\"Y\" />
<input type=\"hidden\" name=\"cust_id\" value=\"" . $params["clientdetails"]["id"] . "\" />
<input type=\"hidden\" name=\"trans_method\" value=\"CC\" />
<input type=\"hidden\" name=\"ResponseMethod\" value=\"GET\" />
<input type=\"hidden\" name=\"post_return_url_approved\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/quantumvault.php\" />
<input type=\"hidden\" name=\"post_return_url_declined\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/quantumvault.php\" />
<noscript>
<input type=\"submit\" value=\"Click here to continue &raquo;\" />
</noscript>
</form>";
	return $code;
}


function quantumvault_remoteupdate($params) {
	if (!$params["gatewayid"]) {
		return "<p align=\"center\">You must pay your first invoice via credit card before you can update your stored card details here...</p>";
	}

	$quantum = quantumvault_getCode( $params["apiusername"], $params["apikey"], "650", "450", "0", "0", $params["gatewayid"], "CustomerEdit" );
	return $quantum["script"] . $quantum["iframe"];
}


function quantumvault_capture($params) {
	if (!$params["gatewayid"]) {
		return array( "status" => "failed", "rawdata" => "No Card Stored for this Client in Vault" );
	}

	$url = "https://secure.quantumgateway.com/cgi/xml_requester.php";
	$xml = "<QGWRequest>
<Authentication>
<GatewayLogin>" . $params["loginid"] . "</GatewayLogin>
<GatewayKey>" . $params["vaultkey"] . "</GatewayKey>
</Authentication>
<Request>
<RequestType>CreateTransaction</RequestType>
<TransactionType>CREDIT</TransactionType>
<ProcessType>SALES</ProcessType>
<CustomerID>" . $params["gatewayid"] . "</CustomerID>
<Memo>Invoice Number " . $params["invoiceid"] . "</Memo>
<Amount>" . $params["amount"] . "</Amount>
</Request>
</QGWRequest>";
	$data = curlCall( $url, "xml=" . $xml );
	$results = XMLtoArray( $data );

	if ($results["QGWREQUEST"]["RESULT"]["STATUS"] == "APPROVED") {
		return array( "status" => "success", "transid" => $results["QGWREQUEST"]["RESULT"]["TRANSACTIONID"], "rawdata" => $results["QGWREQUEST"]["RESULT"] );
	}

	return array( "status" => "error", "rawdata" => $data );
}


function quantumvault_refund($params) {
	if (!$params["gatewayid"]) {
		return array( "status" => "failed", "rawdata" => "No Card Stored for this Client in Vault" );
	}

	$url = "https://secure.quantumgateway.com/cgi/xml_requester.php";
	$xml = "<QGWRequest>
<Authentication>
<GatewayLogin>" . $params["loginid"] . "</GatewayLogin>
<GatewayKey>" . $params["transkey"] . "</GatewayKey>
</Authentication>
<Request>
<RequestType>ShowTransactionDetails</RequestType>
<TransactionID>" . $params["transid"] . "</TransactionID>
</Request>
</QGWRequest>";
	$data = curlCall( $url, "xml=" . $xml );
	$results = XMLtoArray( $data );
	$cclastfour = $results["QGWREQUEST"]["RESULT"]["CREDITCARDNUMBER"];
	$xml = "<QGWRequest>
<Authentication>
<GatewayLogin>" . $params["loginid"] . "</GatewayLogin>
<GatewayKey>" . $params["transkey"] . "</GatewayKey>
</Authentication>
<Request>
<RequestType>ProcessSingleTransaction</RequestType>
<ProcessType>RETURN</ProcessType>
<TransactionType>CREDIT</TransactionType>
<PaymentType>CC</PaymentType>
<CustomerID>" . $params["gatewayid"] . "</CustomerID>
<TransactionID>" . $params["transid"] . "</TransactionID>
<CreditCardNumber>" . $cclastfour . "</CreditCardNumber>
<Amount>" . $params["amount"] . "</Amount>
</Request>
</QGWRequest>";
	$data = curlCall( $url, "xml=" . $xml );
	$results = XMLtoArray( $data );

	if ($results["QGWREQUEST"]["RESULT"]["STATUS"] == "APPROVED") {
		return array( "status" => "success", "transid" => $results["QGWREQUEST"]["RESULT"]["TRANSACTIONID"], "rawdata" => $results["QGWREQUEST"]["RESULT"] );
	}

	return array( "status" => "error", "rawdata" => $data );
}


function _quantumvault_http_post($host, $path, $data, $port = 80) {
	$url = "https://secure.quantumgateway.com" . $path;
	$result = curlCall( $url, $data );
	
	$response = explode( "

", $result, 2 );
	$response[1] = $response[0];
	return $response;
}


function quantumvault_getCode($API_Username, $API_Key, $width, $height, $amount = "0", $id = "0", $custid = "0", $method = "0", $addtoVault = "N", $skipshipping = "N") {
	$thereturn = array();
	$random = rand( 1111111111, 9999999999 );
	$random = (int)$random;
	$response = _quantumvault_http_post( "secure.quantumgateway.com", "/cgi/ilf_authenticate.php", array( "API_Username" => $API_Username, "API_Key" => $API_Key, "randval" => $random, "lastip" => $_SERVER["REMOTE_ADDR"] ), 443 );

	if (is_array( $response )) {
		if ($response[1] != "error") {
			$extrapars = "";

			if ($method != "0") {
				$extrapars .= "&METHOD=" . $method;
			}


			if ($addtoVault != "N") {
				$extrapars .= "&AddToVault=" . $addtoVault;
			}


			if ($skipshipping != "N") {
				$extrapars .= "&skip_shipping_info=" . $skipshipping;
			}


			if ($custid != "0") {
				$extrapars .= "&CustomerID=" . urlencode( $custid );
			}


			if ($amount != "0") {
				$extrapars .= "&Amount=" . $amount . "&FNAME=Andres&LNAME=Roca";
			}


			if ($id != "0") {
				$extrapars .= "&ID=" . urlencode( $id );
			}

			$extrapars .= "&skip_shipping_info=Y&ilf_API_Style=2";
			$thereturn["iframe"] = "<iframe src=\"https://secure.quantumgateway.com/cgi/ilf.php?k=" . $response[1] . "&ip=" . $_SERVER["REMOTE_ADDR"] . $extrapars . "\" height=\"" . $height . "\" width=\"" . $width . "\" frameborder=\"0\"></iframe><br/>";
			$thereturn["script"] = "
<script type=\"text/javascript\">
function refreshSession(thek, theip) {
	var randomnumber=Math.random();
    jQuery.post(\"modules/gateways/quantumvault.php?cachebuster=\"+randomnumber, { ajax: \"1\", ip: theip, k: thek } );
}
setInterval(\"refreshSession('" . $response[1] . "','" . $_SERVER["REMOTE_ADDR"] . "')\",20000);
</script>
";
		}
	}

	return $thereturn;
}


function quantumvault_adminstatusmsg($vars) {
	$gatewayid = get_query_val( "tblclients", "gatewayid", array( "id" => $vars["userid"] ) );

	if ($gatewayid) {
		return array( "type" => "info", "title" => "Quantum Vault Profile", "msg" => "This customer has a Quantum Vault Profile storing their card details for automated recurring billing with ID " . $gatewayid );
	}

}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}


if (isset( $_POST["ajax"] )) {
	if ($_POST["ajax"] == "true") {
		$response = _quantumvault_http_post( "secure.quantumgateway.com", "/cgi/ilf_refresh.php", array( "ip" => $_POST["ip"], "k" => $_POST["k"] ), 443 );
	}
}

?>