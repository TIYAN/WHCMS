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
$GATEWAY = getGatewayVariables("chronopay");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}


if ($_POST['transaction_type'] == "onetime") {
	$transid = $_POST['transaction_id'];
	$invoiceid = $_POST['cs1'];
	$amount = $_POST['total'];
	$invoiceid = checkCbInvoiceID($_POST['cs1'], "ChronoPay");

	if ($invoiceid) {
		addInvoicePayment($invoiceid, $transid, "", "", "chronopay");
		logTransaction("ChronoPay", $_REQUEST, "Successful");
		redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
	}
	else {
		logTransaction("ChronoPay", $_REQUEST, "Error");
	}
}
else {
	logTransaction("ChronoPay", $_REQUEST, "Invalid");
}

redirSystemURL("action=invoices", "clientarea.php");
?>