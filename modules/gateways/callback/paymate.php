<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 **/

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
$GATEWAY = getGatewayVariables("paymateau");

if (!$GATEWAY['type']) {
	$GATEWAY = getGatewayVariables("paymatenz");
}


if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$invoiceid = checkCbInvoiceID($_POST['ref'], "Paymate");

if ($_POST['responseCode'] == "PA" && $invoiceid) {
	addInvoicePayment($invoiceid, $_POST['transactionID'], "", "", "paymate");
	logTransaction("Paymate", $_REQUEST, "Successful");
	redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
	return 1;
}

logTransaction("Paymate", $_REQUEST, "Error");
redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
?>