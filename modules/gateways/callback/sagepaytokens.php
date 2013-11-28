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
$GATEWAY = getGatewayVariables("sagepaytokens");

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

$response = sagepaytokens_call($url, $_POST);
$baseStatus = $response['Status'];
$invoiceid = $_REQUEST['invoiceid'];

if (!$invoiceid && isset($_SESSION['sagepaytokensinvoiceid'])) {
	$invoiceid = $_SESSION['sagepaytokensinvoiceid'];
}

$invoiceid = checkCbInvoiceID($invoiceid, "SagePay Tokens 3DAuth");
$callbacksuccess = false;
switch ($response['Status']) {
case "OK":
		checkCbTransID($response['VPSTxId']);
		addInvoicePayment($invoiceid, $response['VPSTxId'], "", "", "sagepaytokens", "on");
		logTransaction("SagePay Tokens 3DAuth", $response, "Successful");
		sendMessage("Credit Card Payment Confirmation", $invoiceid);
		$callbacksuccess = true;
		break;

case "NOTAUTHED":
		logTransaction("SagePay Tokens 3DAuth", $response, "Not Authed");
		sendMessage("Credit Card Payment Failed", $invoiceid);
		update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "issuenumber" => ""), array("id" => $userid));
		break;

case "REJECTED":
		logTransaction("SagePay Tokens 3DAuth", $response, "Rejected");
		sendMessage("Credit Card Payment Failed", $invoiceid);
		update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "issuenumber" => ""), array("id" => $userid));
		break;

case "FAIL":
		logTransaction("SagePay Tokens 3DAuth", $response, "Failed");
		sendMessage("Credit Card Payment Failed", $invoiceid);
		update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "issuenumber" => ""), array("id" => $userid));
		break;

default:
		logTransaction("SagePay Tokens 3DAuth", $response, "Error");
		sendMessage("Credit Card Payment Failed", $invoiceid);
		update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "issuenumber" => ""), array("id" => $userid));
		break;
}

callback3DSecureRedirect($invoiceid, $callbacksuccess);
?>