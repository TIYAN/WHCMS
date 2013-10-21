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
$GATEWAY = getGatewayVariables( "payson" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$strYourSecretKey = $GATEWAY["key"];
$strOkURL = $_GET["OkURL"];
$strRefNr = $_GET["RefNr"];
$strPaysonRef = $_GET["Paysonref"];
$strTestMD5String = $strOkUrl . $strPaysonRef . $strYourSecretKey;

$strMD5Hash = md5( $strTestMD5String );

if ($strMD5Hash = $_GET["MD5"]) {
	$id = checkCbInvoiceID( $_REQUEST["RefNr"], "PaySon" );

	if ($id != "") {
		addInvoicePayment( $_GET["RefNr"], $strPaysonRef, "", "", "payson" );
		logTransaction( "Payson", $_REQUEST, "Successful" );
		header( "Location: " . $CONFIG["SystemURL"] . ( "/viewinvoice.php?id=" . $id ) );
		exit();
		return 1;
	}

	logTransaction( "Payson", $_REQUEST, "Error" );
	header( "Location: " . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices" );
	exit();
	return 1;
}

logTransaction( "Payson", $_REQUEST, "Unsuccessful" );
header( "Location: " . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices" );
exit();
?>