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

require "../../../init.php";
$whmcs->load_function("invoice");
$whmcs->load_function("gateway");
$GATEWAY = getGatewayVariables("cashu");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$amount = $_REQUEST['amount'];
$currency = $_REQUEST['currency'];
$trn_id = $_REQUEST['trn_id'];
$session_id = (int)$_REQUEST['session_id'];
$verificationString = $_REQUEST['verificationString'];
$verstr = array(strtolower($GATEWAY['merchantid']), strtolower($trn_id), $GATEWAY['encryptionkeyword']);
$verstr = implode(":", $verstr);
$verstr = sha1($verstr);
$invoiceid = checkCbInvoiceID($session_id, "CashU");

if ($verstr == $verificationString) {
	addInvoicePayment($invoiceid, $trn_id, $amount, "0", "cashu");
	logTransaction("CashU", $debugdata, "Successful");
	redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
	return 1;
}

logTransaction("CashU", $_REQUEST, "Invalid Hash");
redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
?>