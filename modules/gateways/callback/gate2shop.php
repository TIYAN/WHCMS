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

require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "gate2shop" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$cId = $_REQUEST["customField1"];

if (isset( $_REQUEST["TransactionID"] )) {
	$trId = $_REQUEST["TransactionID"];
}


if (isset( $_REQUEST["ErrCode"] )) {
	$errCode = $_REQUEST["ErrCode"];
}


if (isset( $_REQUEST["ExErrCode"] )) {
	$exErrCode = $_REQUEST["ExErrCode"];
}


if (isset( $_REQUEST["Status"] )) {
	$status = $_REQUEST["Status"];
}


if (isset( $_REQUEST["responsechecksum"] )) {
	$responsechecksum = $_REQUEST["responsechecksum"];
}


if (isset( $_REQUEST["AuthCode"] )) {
	$authCode = $_REQUEST["AuthCode"];
}


if (isset( $_REQUEST["Token"] )) {
	$token = $_REQUEST["Token"];
}


if (isset( $_REQUEST["Reason"] )) {
	$reason = $_REQUEST["Reason"];
}


if (isset( $_REQUEST["ReasonCode"] )) {
	$ReasonCode = $_REQUEST["ReasonCode"];
}


if (isset( $_REQUEST["responsechecksum"] )) {
	$responseChecksum = $_REQUEST["responsechecksum"];
}


if (isset( $_REQUEST["totalAmount"] )) {
	$totalAmount = $_REQUEST["totalAmount"];
}


if (isset( $_REQUEST["ClientUniqueID"] )) {
	$custId = $_REQUEST["ClientUniqueID"];
}

$sCheckString = $GATEWAY["SecretKey"];
$sCheckString .= $trId;
$sCheckString .= $errCode;
$sCheckString .= $exErrCode;
$sCheckString .= $status;

$checksum = md5( $sCheckString );

if ($responseChecksum == $checksum) {
	if (( ( ( ( ( isset( $_REQUEST["ErrCode"] ) && $_REQUEST["ErrCode"] == 0 ) && isset( $_REQUEST["ExErrCode"] ) ) && $_REQUEST["ExErrCode"] == 0 ) && isset( $_REQUEST["Status"] ) ) && $_REQUEST["Status"] == "APPROVED" )) {
		$cId = checkCbInvoiceID( $cId, "gate2shop" );
		addInvoicePayment( $cId, $trId, "", "", "gate2shop" );
		logTransaction( "Gate2Shop", $_REQUEST, "Successful" );
	}
	else {
		logTransaction( "Gate2Shop", $_REQUEST, "Failed" );
	}

	header( "Location: ../../../viewinvoice.php?id=" . $cId );
	exit();
	return 1;
}

logTransaction( "Gate2Shop", $_REQUEST, "Checksum Error" );
exit( "Checksum Error. Please contact support" );
?>