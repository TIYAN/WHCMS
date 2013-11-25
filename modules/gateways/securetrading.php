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

function securetrading_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "SecureTrading" ), "username" => array( "FriendlyName" => "Username", "Type" => "text", "Size" => "20" ), "password" => array( "FriendlyName" => "Password", "Type" => "text", "Size" => "20" ), "siteref" => array( "FriendlyName" => "Site Reference", "Type" => "text", "Size" => "20" ) );
	return $configarray;
}


function securetrading_capture($params) {
	$gatewayusername = $params["username"];
	$gatewaypassword = $params["password"];
	$gatewaysiteref = $params["siteref"];
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<requestblock version=\"3.67\">
<alias>" . $gatewayusername . "</alias>
<request type=\"AUTH\">
<operation>
<sitereference>" . $gatewaysiteref . "</sitereference>
<accounttypedescription>ECOM</accounttypedescription>
</operation>
<merchant>
<orderreference>" . $params["invoiceid"] . "</orderreference>
</merchant>
<customer>
<delivery/>
<name>" . $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"] . "</name>
<email>" . $params["clientdetails"]["email"] . "</email>
<ip>" . $_SERVER["REMOTE_ADDR"] . "</ip>
</customer>
<billing>
<amount currencycode=\"" . $params["currency"] . "\">" . $params["amount"] * 100 . "</amount>
<premise>" . $params["clientdetails"]["address1"] . "</premise>
<street>" . $params["clientdetails"]["address2"] . "</street>
<town>" . $params["clientdetails"]["city"] . "</town>
<county>" . $params["clientdetails"]["state"] . "</county>
<country>" . $params["clientdetails"]["country"] . "</country>
<postcode>" . $params["clientdetails"]["postcode"] . "</postcode>
<email>" . $params["clientdetails"]["email"] . "</email>
<payment type=\"" . strtoupper( $params["cardtype"] ) . "\">
<expirydate>" . substr( $params["cardexp"], 0, 2 ) . "/20" . substr( $params["cardexp"], 2, 2 ) . "</expirydate>
<pan>" . $params["cardnum"] . "</pan>
<securitycode>" . $params["cccvv"] . "</securitycode>
</payment>
<name>
<middle> </middle>
<last>" . $params["clientdetails"]["lastname"] . "</last>
<first>" . $params["clientdetails"]["firstname"] . "</first>
</name>
</billing>
<settlement/>
</request>
</requestblock>";
	$authstr = "Basic " . base64_encode( $gatewayusername . ":" . $gatewaypassword );
	$headers = array( "HTTP/1.1", "Host: webservices.securetrading.net", "Accept: text/xml", "Authorization: " . $authstr, "User-Agent: WHMCS Gateway Module", "Content-type: text/xml;charset=\"utf-8\"", "Content-length: " . strlen( $xml ), "Connection: close" );
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, "https://webservices.securetrading.net:443/xml/" );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch, CURLOPT_USERPWD, "" . $gatewayusername . ":" . $gatewaypassword );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	$data = curl_exec( $ch );
	curl_close( $ch );
	$xmldata = XMLtoArray( $data );

	if ($xmldata["RESPONSEBLOCK"]["RESPONSE"]["ERROR"]["CODE"] == "0") {
		$results["transid"] = $xmldata["RESPONSEBLOCK"]["RESPONSE"]["TRANSACTIONREFERENCE"];
		return array( "status" => "success", "transid" => $results["transid"], "rawdata" => $data );
	}


	if ($xmldata["RESPONSEBLOCK"]["RESPONSE"]["ERROR"]["CODE"] == "99999") {
		$results["status"] = "error";
		return array( "status" => "error", "rawdata" => $data );
	}

	return array( "status" => "declined", "rawdata" => $data );
}


function securetrading_refund($params) {
	$gatewayusername = $params["username"];
	$gatewaypassword = $params["password"];
	$gatewaysiteref = $params["siteref"];
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><requestblock version=\"3.67\"><alias>" . $gatewayusername . "</alias><request type=\"REFUND\"> <merchant> <orderreference>" . $params["invoiceid"] . "</orderreference> </merchant> <operation> <sitereference>" . $gatewaysiteref . "</sitereference> <parenttransactionreference>" . $params["transid"] . "</parenttransactionreference> </operation> <billing> <amount currencycode=\"" . $params["currency"] . "\">" . $params["amount"] * 100 . "</amount> </billing> </request> </requestblock>";
	$authstr = "Basic " . base64_encode( $gatewayusername . ":" . $gatewaypassword );
	$headers = array( "HTTP/1.1", "Host: webservices.securetrading.net", "Accept: text/xml", "Authorization: " . $authstr, "User-Agent: WHMCS Gateway Module", "Content-type: text/xml;charset=\"utf-8\"", "Content-length: " . strlen( $xml ), "Connection: close" );
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, "https://webservices.securetrading.net:443/xml/" );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch, CURLOPT_USERPWD, "" . $gatewayusername . ":" . $gatewaypassword );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	$data = curl_exec( $ch );
	curl_close( $ch );
	$xmldata = XMLtoArray( $data );

	if ($xmldata["RESPONSEBLOCK"]["RESPONSE"]["ERROR"]["CODE"] == "0") {
		$results["transid"] = $xmldata["RESPONSEBLOCK"]["RESPONSE"]["TRANSACTIONREFERENCE"];
		return array( "status" => "success", "transid" => $results["transid"], "rawdata" => $data );
	}


	if ($xmldata["RESPONSEBLOCK"]["RESPONSE"]["ERROR"]["CODE"] == "99999") {
		$results["status"] = "error";
		return array( "status" => "error", "rawdata" => $data );
	}

	return array( "status" => "declined", "rawdata" => $data );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>