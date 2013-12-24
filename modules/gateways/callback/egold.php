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
$GATEWAY = getGatewayVariables("egold");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$invoiceid = checkCbInvoiceID($invoiceid, "E-Gold");
checkCbTransID($_POST['PAYMENT_BATCH_NUM']);
addInvoicePayment($invoiceid, $_POST['PAYMENT_BATCH_NUM'], $_POST['PAYMENT_AMOUNT'], "", "egold");
logTransaction("E-Gold", $_REQUEST, "Successful");
header("HTTP/1.1 200 OK");
header("Status: 200 OK");
?>