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

function paymentexpress_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Payment Express" ), "pxpayuserid" => array( "FriendlyName" => "User ID", "Type" => "text", "Size" => "20", "Description" => "Your account's user ID" ), "pxpaykey" => array( "FriendlyName" => "Post Password", "Type" => "text", "Size" => "70", "Description" => "Your account's 64 character key" ) );
	return $configarray;
}


function paymentexpress_link($params) {
	$url = "https://sec.paymentexpress.com/pxpay/pxaccess.aspx";
	$xml = "<GenerateRequest>
<PxPayUserId>" . $params["pxpayuserid"] . "</PxPayUserId>
<PxPayKey>" . $params["pxpaykey"] . "</PxPayKey>
<AmountInput>" . $params["amount"] . "</AmountInput>
<CurrencyInput>" . $params["currency"] . "</CurrencyInput>
<MerchantReference>" . $params["description"] . "</MerchantReference>
<EmailAddress>" . $params["clientdetails"]["email"] . "</EmailAddress>
<TxnData1>" . $params["invoiceid"] . "</TxnData1>
<TxnType>Purchase</TxnType>
<TxnId>" . substr( time() . $params["invoiceid"], 0, 16 ) . "</TxnId>
<BillingId></BillingId>
<EnableAddBillCard>0</EnableAddBillCard>
<UrlSuccess>" . $params["systemurl"] . "/modules/gateways/callback/paymentexpress.php</UrlSuccess>
<UrlFail>" . $params["systemurl"] . "/clientarea.php</UrlFail>
</GenerateRequest>";
	$data = curlCall( $url, $xml );
	$xmlresponse = XMLtoArray( $data );
	$uri = $xmlresponse["REQUEST"]["URI"];
	$code = "<form method=\"post\" action=\"" . $uri . "\"><input type=\"submit\" value=\"" . $params["langpaynow"] . "\"></form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>