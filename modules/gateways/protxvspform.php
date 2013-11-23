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

function protxvspform_activate() {
	defineGatewayField( "protxvspform", "text", "vendorname", "", "Vendor Name", "25", "The VSPVendorName assigned to you by ProtX" );
	defineGatewayField( "protxvspform", "text", "xorencryptionpw", "", "Encryption Password", "25", "The XOR Encryption Password assigned to you by ProtX" );
	defineGatewayField( "protxvspform", "text", "vendoremail", "", "Vendor Email", "40", "The email address you want SagePay to send receipts to" );
	defineGatewayField( "protxvspform", "yesno", "testmode", "", "Test Mode", "", "" );
}


function protxvspform_link($params) {
	$strTransactionType = "PAYMENT";
	$strCustomerName = $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"];
	$strBillingAddress = $params["clientdetails"]["address1"];
	$strBillingPostCode = $params["clientdetails"]["postcode"];
	$strContactNumber = $params["clientdetails"]["phonenumber"];
	$strEncryptionPassword = $params["xorencryptionpw"];
	$strVendorTxCode = date( "YmdHis" ) . $params["invoiceid"];
	$strBasket = "1:" . $params["description"] . ":1:" . $params["amount"] . ":0:" . $params["amount"] . ":" . $params["amount"] . "";
	$strPost = "VendorTxCode=" . $strVendorTxCode;
	$strPost = $strPost . "&Amount=" . number_format( $params["amount"], 2 );
	$strPost = $strPost . "&Currency=" . $params["currency"];
	$strPost = $strPost . "&Description=" . $params["description"];
	$strPost = $strPost . "&SuccessURL=" . $params["systemurl"] . "/modules/gateways/callback/protxvspform.php?invoiceid=" . $params["invoiceid"];
	$strPost = $strPost . "&FailureURL=" . $params["systemurl"] . "/modules/gateways/callback/protxvspform.php?invoiceid=" . $params["invoiceid"];
	$strPost = $strPost . "&CustomerName=" . $strCustomerName;
	$strPost = $strPost . "&CustomerEMail=" . $strCustomerEMail;
	$strPost = $strPost . "&VendorEMail=" . $params["vendoremail"];
	$strPost = $strPost . "&BillingAddress=" . $strBillingAddress;
	$strPost = $strPost . "&BillingPostCode=" . $strBillingPostCode;
	$strPost = $strPost . "&DeliveryAddress=" . $strBillingAddress;
	$strPost = $strPost . "&DeliveryPostCode=" . $strBillingPostCode;
	$strPost = $strPost . "&ContactNumber=" . $strContactNumber;
	$strPost = $strPost . "&AllowGiftAid=0";

	if ($strTransactionType !== "AUTHENTICATE") {
		$strPost = $strPost . "&ApplyAVSCV2=0";
	}

	$strPost = $strPost . "&Apply3DSecure=0";
	$strCrypt = base64Encode( SimpleXor( $strPost, $strEncryptionPassword ) );
	$strPurchaseURL = "https://live.sagepay.com/gateway/service/vspform-register.vsp";

	if ($params["testmode"]) {
		$strPurchaseURL = "https://test.sagepay.com/gateway/service/vspform-register.vsp";
	}

	$code = "<form action=\"" . $strPurchaseURL . "\" method=\"post\">
<input type=\"hidden\" name=\"VPSProtocol\" value=\"2.22\">
<input type=\"hidden\" name=\"TxType\" value=\"" . $strTransactionType . "\">
<input type=\"hidden\" name=\"Vendor\" value=\"" . $params["vendorname"] . "\">
<input type=\"hidden\" name=\"Crypt\" value=\"" . $strCrypt . "\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


function base64Encode($plain) {
	$output = "";
	$output = base64_encode( $plain );
	return $output;
}


function base64Decode($scrambled) {
	$output = "";
	$scrambled = str_replace( " ", "+", $scrambled );
	$output = base64_decode( $scrambled );
	return $output;
}


function simpleXor($InString, $Key) {
	$KeyList = array();
	$output = "";
	$i = 6;

	while ($i < strlen( $Key )) {
		$KeyList[$i] = ord( substr( $Key, $i, 1 ) );
		++$i;
	}

	$i = 6;

	while ($i < strlen( $InString )) {
		$output .= chr( ord( substr( $InString, $i, 1 ) ) ^ $KeyList[$i % strlen( $Key )] );
		++$i;
	}

	return $output;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["protxvspformname"] = "protxvspform";
$GATEWAYMODULE["protxvspformvisiblename"] = "ProtX VSP Form";
$GATEWAYMODULE["protxvspformtype"] = "Invoices";
?>