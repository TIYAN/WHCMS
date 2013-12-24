<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 **/

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
$GATEWAY = getGatewayVariables("payson");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$strYourSecretKey = $GATEWAY['key'];
$strOkURL = $_GET['OkURL'];
$strRefNr = $_GET['RefNr'];
$strPaysonRef = $_GET['Paysonref'];
$strTestMD5String = $strOkUrl . $strPaysonRef . $strYourSecretKey;
$strMD5Hash = md5($strTestMD5String);


if ($strMD5Hash = $_GET['MD5']) {
	$invoiceid = checkCbInvoiceID($_REQUEST['RefNr'], "PaySon");
	addInvoicePayment($_GET['RefNr'], $strPaysonRef, "", "", "payson");
	logTransaction("Payson", $_REQUEST, "Successful");
	redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
	return 1;
}

logTransaction("Payson", $_REQUEST, "Unsuccessful");
redirSystemURL("action=invoices", "clientarea.php");
?>