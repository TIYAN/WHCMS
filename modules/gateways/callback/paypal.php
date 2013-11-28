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

function paypal_email_trim($value) {
	$value = trim($value);
}

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
$GATEWAY = getGatewayVariables("paypal");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$postipn = "cmd=_notify-validate";
$orgipn = "";
foreach ($_POST as $key => $value) {
	$orgipn .= ("" . $key . " => " . $value . "\r\n");
	$postipn .= "&" . $key . "=" . urlencode(html_entity_decode($value));
}

$reply = curlCall("https://www.paypal.com/cgi-bin/webscr", $postipn);

if (!strcmp($reply, "VERIFIED")) {
}
else {
	if (!strcmp($reply, "INVALID")) {
		logTransaction("PayPal", $orgipn, "IPN Handshake Invalid");
		header("HTTP/1.0 406 Not Acceptable");
		exit();
	}
	else {
		logTransaction("PayPal", $orgipn . (("\r\n") . "\r\nIPN Handshake Response => " . $reply), "IPN Handshake Error");
		header("HTTP/1.0 406 Not Acceptable");
		exit();
	}
}

$paypalemail = $_POST['receiver_email'];
$payment_status = $_POST['payment_status'];
$subscr_id = $_POST['subscr_id'];
$txn_type = $_POST['txn_type'];
$txn_id = $_POST['txn_id'];
$mc_gross = $_POST['mc_gross'];
$mc_fee = $_POST['mc_fee'];
$idnumber = $_POST['custom'];
$paypalcurrency = $_REQUEST['mc_currency'];
$paypalemails = explode(",", strtolower($GATEWAY['email']));
array_walk($paypalemails, "paypal_email_trim");

if (!in_array(strtolower($paypalemail), $paypalemails)) {
	logTransaction("PayPal", $orgipn, "Invalid Receiver Email");
	exit();
}


if ($payment_status == "Pending") {
	logTransaction("PayPal", $orgipn, "Pending");
	exit();
}


if ($txn_id) {
	checkCbTransID($txn_id);
}


if (!is_numeric($idnumber)) {
	$idnumber = "";
}


if (($txn_type == "web_accept" && $_POST['invoice']) && $payment_status == "Completed") {
	update_query("tblaccounts", array("fees" => $mc_fee), array("transid" => $txn_id));
}

$result = select_query("tblcurrencies", "", array("code" => $paypalcurrency));
$data = mysql_fetch_array($result);
$paypalcurrencyid = $data['id'];
$currencyconvrate = $data['rate'];

if (!$paypalcurrencyid) {
	logTransaction("PayPal", $orgipn, "Unrecognised Currency");
	exit();
}

switch ($txn_type) {
case "subscr_signup":
		logTransaction("PayPal", $orgipn, "Subscription Signup");
		exit();
		break;

case "subscr_cancel":
		update_query("tblhosting", array("subscriptionid" => ""), array("subscriptionid" => $subscr_id));
		logTransaction("PayPal", $orgipn, "Subscription Cancelled");
		exit();
		break;

case "subscr_payment":
		if ($payment_status != "Completed") {
			logTransaction("PayPal", $orgipn, "Incomplete");
			exit();
		}

		$query = "SELECT tblinvoices.id,tblinvoices.userid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid WHERE tblinvoiceitems.relid='" . (int)$idnumber . "' AND tblinvoiceitems.type='Hosting' AND tblinvoices.status='Unpaid' ORDER BY tblinvoices.id ASC";
		$result = full_query($query);
		$data = mysql_fetch_array($result);
		$invoiceid = $data['id'];
		$userid = $data['userid'];

		if ($invoiceid) {
			$orgipn .= ("Invoice Found from Product ID Match => " . $invoiceid . "\r\n");
		}
		else {
			$query = "SELECT tblinvoiceitems.invoiceid,tblinvoices.userid FROM tblhosting INNER JOIN tblinvoiceitems ON tblhosting.id=tblinvoiceitems.relid INNER JOIN tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid WHERE tblinvoices.status='Unpaid' AND tblhosting.subscriptionid='" . db_escape_string($subscr_id) . "' AND tblinvoiceitems.type='Hosting' ORDER BY tblinvoiceitems.invoiceid ASC";
			$result = full_query($query);
			$data = mysql_fetch_array($result);
			$invoiceid = $data['invoiceid'];
			$userid = $data['userid'];

			if ($invoiceid) {
				$orgipn .= ("Invoice Found from Subscription ID Match => " . $invoiceid . "\r\n");
			}
		}


		if (!$invoiceid) {
			$query = "SELECT tblinvoices.id,tblinvoices.userid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid WHERE tblinvoiceitems.relid='" . (int)$idnumber . "' AND tblinvoiceitems.type='Hosting' AND tblinvoices.status='Paid' ORDER BY tblinvoices.id DESC";
			$result = full_query($query);
			$data = mysql_fetch_array($result);
			$invoiceid = $data['id'];
			$userid = $data['userid'];

			if ($invoiceid) {
				$orgipn .= ("Paid Invoice Found from Product ID Match => " . $invoiceid . "\r\n");
			}
		}

		break;

case "web_accept":
		if ($payment_status != "Completed") {
			logTransaction("PayPal", $orgipn, "Incomplete");
			exit();
		}

		$result = select_query("tblinvoices", "", array("id" => $idnumber));
		$data = mysql_fetch_array($result);
		$invoiceid = $data['id'];
		$userid = $data['userid'];
}


if ($invoiceid) {
	logTransaction("PayPal", $orgipn, "Successful");
	$currency = getCurrency($userid);

	if ($paypalcurrencyid != $currency['id']) {
		$mc_gross = convertCurrency($mc_gross, $paypalcurrencyid, $currency['id']);
		$mc_fee = convertCurrency($mc_fee, $paypalcurrencyid, $currency['id']);
		$result = select_query("tblinvoices", "total", array("id" => $invoiceid));
		$data = mysql_fetch_array($result);
		$total = $data['total'];

		if ($total < $mc_gross + 1 && $mc_gross - 1 < $total) {
			$mc_gross = $total;
		}
	}

	addInvoicePayment($invoiceid, $txn_id, $mc_gross, $mc_fee, "paypal");
	$result = select_query("tblinvoiceitems", "", array("invoiceid" => $invoiceid, "type" => "Hosting"));
	$data = mysql_fetch_array($result);
	$relid = $data['relid'];
	update_query("tblhosting", array("subscriptionid" => $subscr_id), array("id" => $relid));
	exit();
}


if ($txn_type == "subscr_payment") {
	$result = select_query("tblhosting", "userid", array("subscriptionid" => $subscr_id));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];

	if ($userid) {
		$orgipn .= ("User ID Found from Subscription ID Match: User ID => " . $userid . "\r\n");
		insert_query("tblaccounts", array("userid" => $userid, "currency" => $paypalcurrencyid, "gateway" => "paypal", "date" => "now()", "description" => "PayPal Subscription Payment", "amountin" => $mc_gross, "fees" => $mc_fee, "rate" => $currencyconvrate, "transid" => $txn_id));
		insert_query("tblcredit", array("clientid" => $userid, "date" => "now()", "description" => "PayPal Subscription Transaction ID " . $txn_id, "amount" => $mc_gross));
		$query = "UPDATE tblclients SET credit=credit+" . db_escape_string($mc_gross) . " WHERE id=" . (int)$userid;
		$result = full_query($query);
		logTransaction("PayPal", $orgipn, "Credit Added");
		return 1;
	}

	logTransaction("PayPal", $orgipn, "Invoice Not Found");
	return 1;
}

logTransaction("PayPal", $orgipn, "Not Supported");
?>