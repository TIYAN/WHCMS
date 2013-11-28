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
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
$GATEWAY = getGatewayVariables("sagepayrepeats");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}


if ($protxsimmode) {
	$url = "https://test.sagepay.com/simulator/VSPDirectCallback.asp";
}
else {
	if ($GATEWAY['testmode']) {
		$url = "https://test.sagepay.com/gateway/service/direct3dcallback.vsp";
	}
	else {
		$url = "https://live.sagepay.com/gateway/service/direct3dcallback.vsp";
	}
}

$data = "PaRes=" . urlencode($_POST['PaRes']) . "&MD=" . $_POST['MD'];
$data = sagepayrepeats_formatData($_POST);
$response = sagepayrepeats_requestPost($url, $data);
$baseStatus = $response['Status'];
$transdump = "";
foreach ($response as $key => $value) {
	$transdump .= ("" . $key . " => " . $value . "\r\n");
}

$invoiceid = $_REQUEST['invoiceid'];

if (!$invoiceid && isset($_SESSION['sagepayrepeatsinvoiceid'])) {
	$invoiceid = $_SESSION['sagepayrepeatsinvoiceid'];
}

$invoiceid = checkCbInvoiceID($invoiceid, "SagePay Repeats 3DAuth");
$userid = get_query_val("tblinvoices", "userid", array("id" => $invoiceid));
$gatewayid = get_query_val("tblclients", "gatewayid", array("id" => $userid));
$callbacksuccess = false;
switch ($response['Status']) {
case "OK":
		checkCbTransID($response['VPSTxId']);
		addInvoicePayment($invoiceid, $response['VPSTxId'], "", "", "sagepayrepeats", "on");
		$gatewayid .= $response['VPSTxId'] . "," . $response['SecurityKey'] . "," . $response['TxAuthNo'];
		update_query("tblclients", array("gatewayid" => $gatewayid, "cardnum" => ""), array("id" => $userid));
		logTransaction("SagePay Repeats 3DAuth", $transdump, "Successful");
		sendMessage("Credit Card Payment Confirmation", $invoiceid);
		$callbacksuccess = true;
		break;

case "NOTAUTHED":
		logTransaction("SagePay Repeats 3DAuth", $transdump, "Not Authed");
		sendMessage("Credit Card Payment Failed", $invoiceid);
		update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "issuenumber" => ""), array("id" => $userid));
		break;

case "REJECTED":
		logTransaction("SagePay Repeats 3DAuth", $transdump, "Rejected");
		sendMessage("Credit Card Payment Failed", $invoiceid);
		update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "issuenumber" => ""), array("id" => $userid));
		break;

case "FAIL":
		logTransaction("SagePay Repeats 3DAuth", $transdump, "Failed");
		sendMessage("Credit Card Payment Failed", $invoiceid);
		update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "issuenumber" => ""), array("id" => $userid));
		break;

default:
		logTransaction("SagePay Repeats 3DAuth", $transdump, "Error");
		sendMessage("Credit Card Payment Failed", $invoiceid);
		update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "issuenumber" => ""), array("id" => $userid));
		break;
}

callback3DSecureRedirect($invoiceid, $callbacksuccess);
?>