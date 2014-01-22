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

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("getClientsDetails")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}


if (!function_exists("updateInvoiceTotal")) {
	require ROOTDIR . "/includes/invoicefunctions.php";
}

$result = select_query("tblclients", "id", array("id" => $_POST['userid']));
$data = mysql_fetch_array($result);

if (!$data['id']) {
	$apiresults = array("result" => "error", "message" => "Client ID Not Found");
	return null;
}

$taxrate = $_POST['taxrate'];
$taxrate2 = $_POST['taxrate2'];

if (($CONFIG['TaxEnabled'] == "on" && !$taxrate) && !$taxrate2) {
	$clientsdetails = getClientsDetails($_POST['userid']);

	if (!$clientsdetails['taxexempt']) {
		$state = $clientsdetails['state'];
		$country = $clientsdetails['country'];
		$taxdata = getTaxRate(1, $state, $country);
		$taxdata2 = getTaxRate(2, $state, $country);
		$taxrate = $taxdata['rate'];
		$taxrate2 = $taxdata2['rate'];
	}
}

$invoiceid = insert_query("tblinvoices", array("date" => $_POST['date'], "duedate" => $_POST['duedate'], "userid" => $_POST['userid'], "status" => "Unpaid", "taxrate" => $taxrate, "taxrate2" => $taxrate2, "paymentmethod" => $_POST['paymentmethod'], "notes" => $_POST['notes']));
foreach ($_POST as $k => $v) {

	if (substr($k, 0, 10) == "itemamount") {
		$counter = substr($k, 10);
		$description = $_POST["itemdescription" . $counter];
		$amount = $_POST["itemamount" . $counter];
		$taxed = $_POST["itemtaxed" . $counter];

		if ($description) {
			insert_query("tblinvoiceitems", array("invoiceid" => $invoiceid, "userid" => $userid, "description" => $description, "amount" => $amount, "taxed" => $taxed));
			continue;
		}

		continue;
	}
}

updateInvoiceTotal($invoiceid);

if ($_POST['sendinvoice']) {
	sendMessage("Invoice Created", $invoiceid);
}


if ($autoapplycredit) {
	$result = select_query("tblclients", "credit", array("id" => $userid));
	$data = mysql_fetch_array($result);
	$credit = $data['credit'];
	$result = select_query("tblinvoices", "total", array("id" => $invoiceid));
	$data = mysql_fetch_array($result);
	$total = $data['total'];

	if (0 < $credit) {
		$doprocesspaid = "";

		if ($total <= $credit) {
			$creditleft = $credit - $total;
			$credit = $total;
			$doprocesspaid = true;
		}
		else {
			$creditleft = 0;
		}

		logActivity("Credit Automatically Applied at Invoice Creation - Invoice ID: " . $invoiceid . " - Amount: " . $credit, $userid);
		update_query("tblclients", array("credit" => $creditleft), array("id" => $userid));
		update_query("tblinvoices", array("credit" => $credit), array("id" => $invoiceid));
		updateInvoiceTotal($invoiceid);

		if ($doprocesspaid) {
			processPaidInvoice($invoiceid);
		}
	}
}

$apiresults = array("result" => "success", "invoiceid" => $invoiceid);
?>