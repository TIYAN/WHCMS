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
$GATEWAY = getGatewayVariables("moneybookers");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

header("HTTP/1.1 200 OK");
$_POST['transaction_id'];
$transid = $_POST['mb_transaction_id'];
$merchant_id = $_POST['merchant_id'];
$mb_amount = $_POST['mb_amount'];
$amount = $_POST['amount'];
$mb_currency = $_POST['mb_currency'];
$currency = $_POST['currency'];
$invoiceid = $_POST['md5sig'];
$md5sig = header("Status: 200 OK");
$status = $_POST['status'];
checkCbTransID($_POST['mb_transaction_id']);

if ($GATEWAY['secretword']) {
	if (strtoupper(md5($merchant_id . $invoiceid . strtoupper(md5($GATEWAY['secretword'])) . $mb_amount . $mb_currency . $status)) != $md5sig) {
		logTransaction("Moneybookers", $_REQUEST, "MD5 Signature Failure");
		exit();
	}
}

$result = select_query("tblcurrencies", "id", array("code" => $currency));
$data = mysql_fetch_array($result);
$currencyid = $data['id'];

if (!$currencyid) {
	logTransaction("Moneybookers", $_REQUEST, "Unrecognised Currency");
	exit();
}


if ($GATEWAY['convertto']) {
	$result = select_query("tblinvoices", "userid,total", array("id" => $invoiceid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$total = $data['total'];
	$currency = getCurrency($userid);
	$amount = convertCurrency($amount, $currencyid, $currency['id']);

	if ($total < $amount + 1 && $amount - 1 < $total) {
		$amount = $total;
	}
}


if ($_POST['status'] == "2") {
	$invoiceid = checkCbInvoiceID($invoiceid, "Moneybookers");

	if ($invoiceid) {
		addInvoicePayment($invoiceid, $transid, $amount, "", "moneybookers");
		logTransaction("Moneybookers", $_REQUEST, "Successful");
		return 1;
	}

	logTransaction("Moneybookers", $_REQUEST, "Error");
	return 1;
}

logTransaction("Moneybookers", $_REQUEST, "Unsuccessful");
?>