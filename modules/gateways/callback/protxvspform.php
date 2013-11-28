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
 **/

function base64EncodeX($plain) {
	$output = "";
	$output = base64_encode($plain);
	return $output;
}

function base64DecodeX($scrambled) {
	$scrambled = $output = "";
	base64_decode($scrambled);
	$output = str_replace(" ", "+", $scrambled);
	return $output;
}

function simpleXorX($InString, $Key) {
	$KeyList = array();
	$output = "";
	$i = 0;

	while ($i < strlen($Key)) {
		$KeyList[$i] = ord(substr($Key, $i, 1));
		++$i;
	}

	$i = 0;

	while ($i < strlen($InString)) {
		$output .= chr(ord(substr($InString, $i, 1)) ^ $KeyList[$i % strlen($Key)]);
		++$i;
	}

	return $output;
}

function getTokenX($thisString) {
	$Tokens = array("Status", "StatusDetail", "VendorTxCode", "VPSTxId", "TxAuthNo", "Amount", "AVSCV2", "AddressResult", "PostCodeResult", "CV2Result", "GiftAid", "3DSecureStatus", "CAVV");
	$output = array();
	$resultArray = array();
	$i = count($Tokens) - 1;

	while (0 <= $i) {
		$start = strpos($thisString, $Tokens[$i]);

		if ($start !== false) {
			$resultArray[$i]->start = $start;
			$resultArray[$i]->token = $Tokens[$i];
		}

		--$i;
	}

	sort($resultArray);
	$i = 0;

	while ($i < count($resultArray)) {
		$valueStart = $resultArray[$i]->start + strlen($resultArray[$i]->token) + 1;

		if ($i == count($resultArray) - 1) {
			$output[$resultArray[$i]->token] = substr($thisString, $valueStart);
		}
		else {
			$valueLength = $resultArray[$i + 1]->start - $resultArray[$i]->start - strlen($resultArray[$i]->token) - 2;
			$output[$resultArray[$i]->token] = substr($thisString, $valueStart, $valueLength);
		}

		++$i;
	}

	return $output;
}

require "../../../init.php";
$whmcs->load_function("invoice");
$whmcs->load_function("gateway");
$GATEWAY = getGatewayVariables("protxvspform");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$strEncryptionPassword = $GATEWAY['xorencryptionpw'];
$strCrypt = $_REQUEST['crypt'];
$strDecoded = simpleXorX(Base64DecodeX($strCrypt), $strEncryptionPassword);
$values = getTokenX($strDecoded);
$strStatus = $values['Status'];
$strStatusDetail = $values['StatusDetail'];
$strVendorTxCode = $values['VendorTxCode'];
$strVPSTxId = $values['VPSTxId'];
$strTxAuthNo = $values['TxAuthNo'];
$strAmount = $values['Amount'];
$strAVSCV2 = $values['AVSCV2'];
$strAddressResult = $values['AddressResult'];
$strPostCodeResult = $values['PostCodeResult'];
$strCV2Result = $values['CV2Result'];
$strGiftAid = $values['GiftAid'];
$str3DSecureStatus = $values['3DSecureStatus'];
$strCAVV = $values['CAVV'];
$invoiceid = substr($strVendorTxCode, 14);
$invoiceid = checkCbInvoiceID($invoiceid, "ProtX VSP Form");

if ($strStatus == "OK") {
	addInvoicePayment($invoiceid, $strVPSTxId, "", "", "protxvspform");
	logTransaction("ProtX VSP Form", $debugreport, "Successful");
	redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
	return 1;
}

logTransaction("ProtX VSP Form", $debugreport, "Error");
redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
?>