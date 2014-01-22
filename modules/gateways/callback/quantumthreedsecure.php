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
$GATEWAY = getGatewayVariables("quantumgateway");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$invoiceid = $_REQUEST['ID'];
$transid = $_REQUEST['transID'];
$transresult = $_REQUEST['trans_result'];
$amount = $_REQUEST['amount'];
$md5_hash = $_REQUEST['md5_hash'];
checkCbTransID($transid);
$ourhash = md5($GATEWAY['md5hash'] . $GATEWAY['loginid'] . $transid . $amount);

if ($ourhash != $md5_hash) {
	logTransaction("Quantum Gateway", $_REQUEST, "MD5 Hash Failure");
	echo "Hash Failure. Please Contact Support.";
	exit();
}

$callbacksuccess = false;
$invoiceid = checkCbInvoiceID($invoiceid, "Quantum Gateway");

if ($GATEWAY['convertto']) {
	$result = select_query("tblinvoices", "userid,total", array("id" => $invoiceid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$total = $data['total'];
	$currency = getCurrency($userid);
	$amount = convertCurrency($amount, $GATEWAY['convertto'], $currency['id']);

	if ($total < $amount + 1 && $amount - 1 < $total) {
		$amount = $total;
	}
}


if ($transresult == "APPROVED") {
	addInvoicePayment($invoiceid, $transid, $amount, "", "quantumgateway", "on");
	logTransaction("Quantum Gateway", $_REQUEST, "Approved");
	sendMessage("Credit Card Payment Confirmation", $invoiceid);
	$callbacksuccess = true;
}
else {
	logTransaction("Quantum Gateway", $_REQUEST, "Declined");
	sendMessage("Credit Card Payment Failed", $invoiceid);
}

callback3DSecureRedirect($invoiceid, $callbacksuccess);
?>