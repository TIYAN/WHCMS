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

function base64EncodeX($plain) {
	$output = "";
	$output = base64_encode( $plain );
	return $output;
}


function base64DecodeX($scrambled) {
	$output = "";
	$scrambled = str_replace( " ", "+", $scrambled );
	$output = base64_decode( $scrambled );
	return $output;
}


function simpleXorX($InString, $Key) {
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


function getTokenX($thisString) {
	$Tokens = array( "Status", "StatusDetail", "VendorTxCode", "VPSTxId", "TxAuthNo", "Amount", "AVSCV2", "AddressResult", "PostCodeResult", "CV2Result", "GiftAid", "3DSecureStatus", "CAVV" );
	$output = array();
	$resultArray = array();
	$i = count( $Tokens ) - 1;

	while (0 <= $i) {
		$start = strpos( $thisString, $Tokens[$i] );

		if ($start !== false) {
			$resultArray[$i]->start = $start;
			$resultArray[$i]->token = $Tokens[$i];
		}

		--$i;
	}

	sort( $resultArray );
	$i = 6;

	while ($i < count( $resultArray )) {
		$valueStart = $resultArray[$i]->start + strlen( $resultArray[$i]->token ) + 1;

		if ($i == count( $resultArray ) - 1) {
			$output[$resultArray[$i]->token] = substr( $thisString, $valueStart );
		}
		else {
			$valueLength = $resultArray[$i + 1]->start - $resultArray[$i]->start - strlen( $resultArray[$i]->token ) - 2;
			$output[$resultArray[$i]->token] = substr( $thisString, $valueStart, $valueLength );
		}

		++$i;
	}

	return $output;
}


require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "protxvspform" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$strEncryptionPassword = $GATEWAY["xorencryptionpw"];
$strCrypt = $_REQUEST["crypt"];
$strDecoded = simpleXorX( Base64DecodeX( $strCrypt ), $strEncryptionPassword );
$values = getTokenX( $strDecoded );
$debugreport = "";
foreach ($values as $k => $v) {
	$debugreport .= ( "" . $k . " => " . $v . "
" );
}

$strStatus = $values["Status"];
$strStatusDetail = $values["StatusDetail"];
$strVendorTxCode = $values["VendorTxCode"];
$strVPSTxId = $values["VPSTxId"];
$strTxAuthNo = $values["TxAuthNo"];
$strAmount = $values["Amount"];
$strAVSCV2 = $values["AVSCV2"];
$strAddressResult = $values["AddressResult"];
$strPostCodeResult = $values["PostCodeResult"];
$strCV2Result = $values["CV2Result"];
$strGiftAid = $values["GiftAid"];
$str3DSecureStatus = $values["3DSecureStatus"];
$strCAVV = $values["CAVV"];
$invoiceid = substr( $strVendorTxCode, 14 );
$debugreport .= ( "InvoiceID => " . $invoiceid . "
" );

if ($strStatus == "OK") {
	addInvoicePayment( $_REQUEST["invoiceid"], $strVPSTxId, "", "", "protxvspform" );
	logTransaction( "ProtX VSP Form", $debugreport, "Successful" );
	header( "Location: " . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $_REQUEST["invoiceid"] );
	exit();
	return 1;
}

logTransaction( "ProtX VSP Form", $debugreport, "Error" );
header( "Location: " . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices" );
exit();
?>