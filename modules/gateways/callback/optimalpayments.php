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

require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$whmcs->load_function( "client" );
$whmcs->load_function( "cc" );
$GATEWAY = getGatewayVariables( "optimalpayments" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

logTransaction( "Optimal Payments 3D Callback", $_REQUEST, "Received" );
$invoiceid = $_REQUEST["MD"];
$pares = $_REQUEST["PaRes"];
$params = getCCVariables( $invoiceid );
$xml = "<ccAuthenticateRequestV1
xmlns=\"http://www.optimalpayments.com/creditcard/xmlschema/v1\"
xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
xsi:schemaLocation=\"http://www.optimalpayments.com/creditcard/xmlschema/v1\">
<merchantAccount>
<accountNum>" . $GATEWAY["accountnumber"] . "</accountNum>
<storeID>" . $GATEWAY["merchantid"] . "</storeID>
<storePwd>" . $GATEWAY["merchantpw"] . "</storePwd>
</merchantAccount>
<confirmationNumber>" . $_SESSION["optimalpaymentsconfirmationnumber"] . "</confirmationNumber>
<paymentResponse>" . $pares . "</paymentResponse>
</ccAuthenticateRequestV1>";
$url = "https://webservices.optimalpayments.com/creditcardWS/CreditCardServlet/v1";

if ($params["testmode"]) {
	$url = "https://webservices.test.optimalpayments.com/creditcardWS/CreditCardServlet/v1";
}

$query_str = "txnMode=ccTDSAuthenticate&txnRequest=" . urlencode( $xml );
$data = curlCall( $url, $query_str );
$xmldata = XMLtoArray( $data );
$xmldata = $xmldata["CCTXNRESPONSEV1"];
$indicator = $xmldata["TDSAUTHENTICATERESPONSE"]["STATUS"];
$cavv = $xmldata["TDSAUTHENTICATERESPONSE"]["CAVV"];
$xid = $xmldata["TDSAUTHENTICATERESPONSE"]["XID"];
$eci = $xmldata["TDSAUTHENTICATERESPONSE"]["ECI"];
logTransaction( "Optimal Payments 3D Callback", $data, "Authenticate Response" );
$cardtype = optimalpayments_cardtype( $params["cardtype"] );
$xml = "<ccAuthRequestV1 xmlns=\"http://www.optimalpayments.com/creditcard/xmlschema/v1\"
xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
xsi:schemaLocation=\"http://www.optimalpayments.com/creditcard/xmlschema/v1\">
<merchantAccount>
<accountNum>" . $params["accountnumber"] . "</accountNum>
<storeID>" . $params["merchantid"] . "</storeID>
<storePwd>" . $params["merchantpw"] . "</storePwd>
</merchantAccount>
<merchantRefNum>" . $params["invoiceid"] . "</merchantRefNum>
<amount>" . $params["amount"] . "</amount>
<card>
<cardNum>" . $params["cardnum"] . "</cardNum>
<cardExpiry>
<month>" . substr( $params["cardexp"], 0, 2 ) . "</month>
<year>20" . substr( $params["cardexp"], 2, 2 ) . "</year>
</cardExpiry>
<cardType>" . $cardtype . "</cardType>
</card>
<authentication>
<indicator>" . $indicator . "</indicator>
<cavv>" . $cavv . "</cavv>
<xid>" . $xid . "</xid>
</authentication>
<billingDetails>
<cardPayMethod>WEB</cardPayMethod>
<firstName>" . $params["clientdetails"]["firstname"] . "</firstName>
<lastName>" . $params["clientdetails"]["lastname"] . "</lastName>
<street>" . $params["clientdetails"]["address1"] . "</street>
<city>" . $params["clientdetails"]["city"] . "</city>
<region>" . $params["clientdetails"]["state"] . "</region>
<country>" . $params["clientdetails"]["country"] . "</country>
<zip>" . $params["clientdetails"]["postcode"] . "</zip>
<phone>" . $params["clientdetails"]["phonenumber"] . "</phone>
<email>" . $params["clientdetails"]["email"] . "</email>
</billingDetails>
<recurringIndicator>R</recurringIndicator>
<customerIP>" . $remote_ip . "</customerIP>
<productType>M</productType>
</ccAuthRequestV1>";
$query_str = "txnMode=ccPurchase&txnRequest=" . urlencode( $xml );
logTransaction( "Optimal Payments 3D Callback", $query_str, "Payment Request" );
$data = curlCall( $url, $query_str );
$xmldata = XMLtoArray( $data );
$xmldata = $xmldata["CCTXNRESPONSEV1"];

if ($xmldata["CODE"] == "0") {
	addInvoicePayment( $invoiceid, $transid, "", "", "optimalpayments", "on" );
	logTransaction( "Optimal Payments 3D Callback", $data, "Approved" );
	sendMessage( "Credit Card Payment Confirmation", $invoiceid );
	$callbacksuccess = true;
}
else {
	logTransaction( "Optimal Payments 3D Callback", $data, "Declined" );
	sendMessage( "Credit Card Payment Failed", $invoiceid );
}

callback3DSecureRedirect( $invoiceid, $callbacksuccess );
?>