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
$GATEWAY = getGatewayVariables("paymentexpress");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

logTransaction("Payment Express", $_REQUEST, "Received");
$url = "https://sec.paymentexpress.com/pxpay/pxaccess.aspx";
$xml = "<ProcessResponse>
<PxPayUserId>" . $GATEWAY['pxpayuserid'] . "</PxPayUserId>
<PxPayKey>" . $GATEWAY['pxpaykey'] . "</PxPayKey>
<Response>" . html_entity_decode($_REQUEST['result']) . "</Response>
</ProcessResponse>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
$outputXml = curl_exec($ch);
curl_close($ch);
$xmlresponse = XMLtoArray($outputXml);
$xmlresponse = $xmlresponse['RESPONSE'];
$success = $xmlresponse['SUCCESS'];
$invoiceid = (int)$xmlresponse['TXNDATA1'];
$transid = $xmlresponse['TXNID'];

if ($xmlresponse['SUCCESS'] == "1") {
	$result = select_query("tblinvoices", "id", array("id" => $invoiceid));
	$data = mysql_fetch_array($result);
	$id = $data['id'];

	if (!$id) {
		logTransaction("Payment Express", array_merge($_REQUEST, $xmlresponse), "Invoice ID Not Found");
		redirSystemURL("action=invoices", "clientarea.php");
	}

	$result = select_query("tblaccounts", "invoiceid", array("transid" => $transid));
	$data = mysql_fetch_array($result);
	$transinvoiceid = $data['invoiceid'];

	if ($transinvoiceid) {
		redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
	}

	addInvoicePayment($invoiceid, $transid, "", "", "paymentexpress");
	logTransaction("Payment Express", array_merge($_REQUEST, $xmlresponse), "Successful");
	redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
	return 1;
}

logTransaction("Payment Express", array_merge($_REQUEST, $xmlresponse), "Unsuccessful");

if ($invoiceid) {
	redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
	return 1;
}

redirSystemURL("action=invoices", "clientarea.php");
?>