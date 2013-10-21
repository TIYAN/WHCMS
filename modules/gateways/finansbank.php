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

function finansbank_activate() {
	defineGatewayField( "finansbank", "text", "merchantid", "", "Merchant ID", "20", "" );
	defineGatewayField( "finansbank", "text", "merchantpw", "", "Merchant Password", "20", "" );
	defineGatewayField( "finansbank", "text", "merchantnumber", "", "Merchant Number", "20", "" );
	defineGatewayField( "finansbank", "text", "currency", "", "Currency", "10", "" );
	defineGatewayField( "finansbank", "yesno", "testmode", "", "Test Mode", "", "" );
}


function finansbank_capture($params) {
	if ($params["testmode"] == "on") {
		$gateway_url = "https://testserver.fbwebpos.com/servlet/cc5ApiServer";
	}
	else {
		$gateway_url = "https://www.fbwebpos.com/servlet/cc5ApiServer";
	}

	$name = $params["merchantid"];
	$password = $params["merchantpw"];
	$clientid = $params["merchantnumber"];
	$lip = gethostbyname( $REMOTE_ADDR );
	$email = $params["clientdetails"]["email"];
	$oid = $params["invoiceid"];
	$type = "Auth";
	$ccno = $params["cardnum"];
	$ccay = substr( $params["cardexp"], 0, 2 );
	$ccyil = substr( $params["cardexp"], 2, 2 );
	$tutar = $params["amount"];
	$cv2 = $params["cccvv"];
	$taksit = "";
	$request = "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<CC5Request>
<Name>{NAME}</Name>
<Password>{PASSWORD}</Password>
<ClientId>{CLIENTID}</ClientId>
<IPAddress>{IP}</IPAddress>
<Email>{EMAIL}</Email>
<Mode>P</Mode>
<OrderId>{OID}</OrderId>
<GroupId></GroupId>
<TransId></TransId>
<UserId></UserId>
<Type>{TYPE}</Type>
<Number>{CCNO}</Number>
<Expires>{CCTAR}</Expires>
<Cvv2Val>{CV2}</Cvv2Val>
<Total>{TUTAR}</Total>
<Currency>840</Currency>
<Taksit>{TAKSIT}</Taksit>
<BillTo>
<Name></Name>
<Street1></Street1>
<Street2></Street2>
<Street3></Street3>
<City></City>
<StateProv></StateProv>
<PostalCode></PostalCode>
<Country></Country>
<Company></Company>
<TelVoice></TelVoice>
</BillTo>
<ShipTo>
<Name></Name>
<Street1></Street1>
<Street2></Street2>
<Street3></Street3>
<City></City>
<StateProv></StateProv>
<PostalCode></PostalCode>
<Country></Country>
</ShipTo>
<Extra></Extra>
</CC5Request>
";
	$request = str_replace( "{NAME}", $name, $request );
	$request = str_replace( "{PASSWORD}", $password, $request );
	$request = str_replace( "{CLIENTID}", $clientid, $request );
	$request = str_replace( "{IP}", $lip, $request );
	$request = str_replace( "{OID}", $oid, $request );
	$request = str_replace( "{TYPE}", $type, $request );
	$request = str_replace( "{CCNO}", $ccno, $request );
	$request = str_replace( "{CCTAR}", $ccexpirymonth . "/" . $ccexpiryyear, $request );
	$request = str_replace( "{CV2}", $cccvv, $request );
	$request = str_replace( "" . $total, $tutar, $request );
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $gateway_url );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 90 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $request );
	$result = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$error = curl_error( $ch );
		logTransaction( "Turkey National Banks", "Error => " . $error, "Error" );
		sendMessage( "Credit Card Payment Failed", $params["invoiceid"] );
		$result = "error";
		return $result;
	}

	curl_close( $ch );
	$Response = "";
	$OrderId = "";
	$AuthCode = "";
	$ProcReturnCode = "";
	$ErrMsg = "";
	$HOSTMSG = "";
	$response_tag = "Response";
	$posf = strpos( $result, "<" . $response_tag . ">" );
	$posl = strpos( $result, "</" . $response_tag . ">" );
	$posf = $posf + strlen( $response_tag ) + 2;
	$Response = substr( $result, $posf, $posl - $posf );
	$response_tag = "OrderId";
	$posf = strpos( $result, "<" . $response_tag . ">" );
	$posl = strpos( $result, "</" . $response_tag . ">" );
	$posf = $posf + strlen( $response_tag ) + 2;
	$OrderId = substr( $result, $posf, $posl - $posf );
	$response_tag = "AuthCode";
	$posf = strpos( $result, "<" . $response_tag . ">" );
	$posl = strpos( $result, "</" . $response_tag . ">" );
	$posf = $posf + strlen( $response_tag ) + 2;
	$AuthCode = substr( $result, $posf, $posl - $posf );
	$response_tag = "ProcReturnCode";
	$posf = strpos( $result, "<" . $response_tag . ">" );
	$posl = strpos( $result, "</" . $response_tag . ">" );
	$posf = $posf + strlen( $response_tag ) + 2;
	$ProcReturnCode = substr( $result, $posf, $posl - $posf );
	$response_tag = "ErrMsg";
	$posf = strpos( $result, "<" . $response_tag . ">" );
	$posl = strpos( $result, "</" . $response_tag . ">" );
	$posf = $posf + strlen( $response_tag ) + 2;
	$ErrMsg = substr( $result, $posf, $posl - $posf );
	$debugdata = "Action => Auth
Client => " . $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"] . ( "
Response => " . $Response . "
OrderId => " . $OrderId . "
AuthCode => " . $AuthCode . "
ProcReturnCode => " . $ProcReturnCode . "
ErrMsg => " . $ErrMsg );

	if ($Response === "Approved") {
		return array( "status" => "success", "transid" => $transid, "rawdata" => $debugdata );
	}

	return array( "status" => "declined", "rawdata" => $debugdata );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["finansbankname"] = "finansbank";
$GATEWAYMODULE["finansbankvisiblename"] = "Turkish Finansbank";
$GATEWAYMODULE["finansbanktype"] = "CC";
?>