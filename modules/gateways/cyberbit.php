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

function cyberbit_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "CyberBit" ), "merchantid" => array( "FriendlyName" => "Merchant ID", "Type" => "text", "Size" => "20" ), "transsecret" => array( "FriendlyName" => "Transaction Secret", "Type" => "text", "Size" => "30" ), "hashkey" => array( "FriendlyName" => "Hash Key", "Type" => "text", "Size" => "30" ), "testmode" => array( "FriendlyName" => "Test Mode", "Type" => "yesno" ) );
	return $configarray;
}


function cyberbit_link($params) {
	if ($params["testmode"]) {
		$url = "https://test.cyberbit.dk/spfv2/spfv2.php";
	}
	else {
		$url = "https://merch.pmtngin.com/start.php";
	}

	$params["amount"] *= 100;
	$params["invoiceid"] = time() . "-" . $params["invoiceid"];
	
	$hash = sha1( $params["merchantid"] . "1" . $params["invoiceid"] . "978" . $params["amount"] . "" . $params["hashkey"] );
	$code = "<form method=\"POST\" action=\"" . $url . "\">
<input type=\"hidden\" value=\"1\" name=\"transtype\">
<input type=\"hidden\" value=\"" . $params["transsecret"] . "\" name=\"secret\">
<input type=\"hidden\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/cyberbit.php\" name=\"accepturl\">
<input type=\"hidden\" value=\"" . $params["merchantid"] . "\" name=\"merchantid\">
<input type=\"hidden\" value=\"" . $params["invoiceid"] . "\" name=\"InternalorderId\">
<input type=\"hidden\" value=\"978\" name=\"currencycode\">
<input type=\"hidden\" value=\"" . $params["amount"] . "\" name=\"amountcleared\">
<input type=\"hidden\" value=\"" . $hash . "\" name=\"hash\">
<input type=\"hidden\" value=\"" . $params["clientdetails"]["email"] . "\" name=\"owneremail\">
<input type=\"hidden\" value=\"" . $params["clientdetails"]["address1"] . "\" name=\"owneraddress\">
<input type=\"hidden\" value=\"" . $params["clientdetails"]["address1"] . "\" name=\"owneraddressnumber\">
<input type=\"hidden\" value=\"" . $params["clientdetails"]["city"] . "\" name=\"ownercity\">
<input type=\"hidden\" value=\"" . $params["clientdetails"]["state"] . "\" name=\"ownerstate\">
<input type=\"hidden\" value=\"" . $params["clientdetails"]["country"] . "\" name=\"ownercountry\">
<input type=\"hidden\" value=\"" . $params["clientdetails"]["firstname"] . "\" name=\"ownerfirstname\">
<input type=\"hidden\" value=\"" . $params["clientdetails"]["lastname"] . "\" name=\"ownerlastname\">
<input type=\"hidden\" value=\"" . $params["clientdetails"]["postcode"] . "\" name=\"ownerzip\">
<input type=\"hidden\" value=\"" . $params["clientdetails"]["phonenumber"] . "\" name=\"ownerphone\">
<input type=\"hidden\" value='\"Item Number\";\"Item Description\";\"Amount\";\"Price\"' name=\"header\">
<input type=\"hidden\" value='\"1\";\"" . $params["description"] . "\";\"1\";\"" . $params["amount"] . "\"' name=\"orderline1\">
<input type=\"hidden\" value='\"Total\";\"" . $params["amount"] . "\"' name=\"total\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


function cyberbit_capture($params) {
	if ($params["testmode"]) {
		$url = "https://test.cyberbit.dk/author.php";
	}
	else {
		$url = "https://merch.pmtngin.com/author.php";
	}

	$postfields = array();
	$postfields["Version"] = "2";
	$postfields["Secret"] = $params["transsecret"];
	$postfields["MerchantId"] = $params["merchantid"];
	$postfields["RecurringRefId"] = $params["gatewayid"];
	$postfields["InternalOrderId"] = time() . "-" . $params["invoiceid"];
	$result = curlCall( $url, $postfields );
	$xmldata = XMLtoArray( $result );

	if ($xmldata["ECCPRO"]["STATUSCODE"] == "000") {
		return array( "status" => "success", "transid" => $xmldata["ECCPRO"]["RESPONSE"]["ORDERID"], "rawdata" => $xmldata["ECCPRO"]["RESPONSE"] );
	}

	return array( "status" => "declined", "rawdata" => $xmldata["ECCPRO"]["RESPONSE"] );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>