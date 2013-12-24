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

function imsp_config() {
	$configarray = array("FriendlyName" => array("Type" => "System", "Value" => "IMSP"), "merchantid" => array("FriendlyName" => "Merchant ID", "Type" => "text", "Size" => "25"), "terminalid" => array("FriendlyName" => "Terminal ID", "Type" => "text", "Size" => "25"), "passcode" => array("FriendlyName" => "Passcode", "Type" => "text", "Size" => "25"), "testmode" => array("FriendlyName" => "Test Mode", "Type" => "yesno"));
	return $configarray;
}

function imsp_3dsecure($params) {
	global $remote_ip;

	$currency = "978";
	$amount = str_pad($params['amount'] * 100, 12, "0", STR_PAD_LEFT);
	$signature = $params['passcode'] . $params['merchantid'] . $params['terminalid'] . $params['invoiceid'] . $params['passcode'] . $amount;
	$signature = sha1($signature);
	$postfields = array();
	$postfields['merchantid'] = $params['merchantid'];
	$postfields['terminalid'] = $params['terminalid'];
	$postfields['trxntype'] = "Sale";
	$postfields['cardnumber'] = $params['cardnum'];
	$postfields['expirydate'] = $params['cardexp'];

	if ($params['cccvv']) {
		$postfields['cardvervalue'] = $params['cccvv'];
	}

	$postfields['amount'] = $amount;
	$postfields['currency'] = $currency;
	$postfields['batchnumber'] = $params['invoiceid'];
	$postfields['invoicenumber'] = $params['invoiceid'];
	$postfields['ipaddress'] = $remote_ip;
	$postfields['signature'] = $signature;
	$postfields['responseurl'] = $params['systemurl'] . "/modules/gateways/callback/imsp.php";
	$data = curlCall($url, $postfields);
	$resultstemp = explode(";", $data);
	$results = array();
	foreach ($resultstemp as $v) {
		$v = explode("|", $v);

		if ($v[0]) {
			$results[$v[0]] = $v[1];
			continue;
		}
	}

	print_r($results);
	$responsecode = $results['responsecode'];
	$responsereasoncode = $results['responsereasoncode'];
	$trxnid = $results['trxnid'];
	$url = "https://test.imsp.com/staging/Request3DS.aspx";
	$acsurl = "";
	$pareq = "";
	$termurl = "";
	$Md = "";

	if ($responsecode == "5" && $responsereasoncode == "18") {
		logTransaction("IMSP 3D Secure", $results, "3D Auth Forward");
		$code = "<form method=\"POST\" action=\"" . $acsurl . "\">
                <input type=hidden name=\"PaReq\" value=\"" . $pareq . "\">
                <input type=hidden name=\"TermUrl\" value=\"" . $termurl . "\">
                <input type=hidden name=\"MD\" value=\"" . $Md . "\">
                <noscript>
                <center>
                    <font color=\"red\">
                        <h2>Processing your Payer Authentication Transaction</h2>
                        <h3>JavaScript is currently disabled or is not supported by your browser.<br></h3>
                        <h4>Please click Submit to continue the processing of your transaction.</h4>
                    </font>
                <input type=\"submit\" value=\"Continue\">
                </center>
                </noscript>
            </form>";
		return $code;
	}


	if ($responsecode == "1") {
		logTransaction("IMSP 3D Secure", $results, "Successful");
		addInvoicePayment($params['invoiceid'], $trxnid, "", "", "imsp", "on");
		sendMessage("Credit Card Payment Confirmation", $params['invoiceid']);
		redirSystemURL("id=" . $params['invoiceid'] . "&paymentsuccess=true", "viewinvoice.php");
	}
	else {
		if ($responsecode == "2") {
			logTransaction("IMSP 3D Secure", $results, "Declined");
		}
		else {
			if ($responsecode == "3") {
				logTransaction("IMSP 3D Secure", $results, "Parse Error");
			}
			else {
				logTransaction("IMSP 3D Secure", $results, "System Error");
			}
		}
	}

	return "declined";
}

function imsp_capture($params) {
	return array("status" => "error", "rawdata" => "Not Supported");
}


if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}

?>