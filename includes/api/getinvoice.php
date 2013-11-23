<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}

$result = select_query("tblinvoices", "", array("id" => $invoiceid));
$data = mysql_fetch_array($result);
$invoiceid = $data['id'];

if (!$invoiceid) {
	$apiresults = array("status" => "error", "message" => "Invoice ID Not Found");
	return null;
}

$userid = $data['userid'];
$invoicenum = $data['invoicenum'];
$date = $data['date'];
$duedate = $data['duedate'];
$datepaid = $data['datepaid'];
$subtotal = $data['subtotal'];
$credit = $data['credit'];
$tax = $data['tax'];
$tax2 = $data['tax2'];
$total = $data['total'];
$taxrate = $data['taxrate'];
$taxrate2 = $data['taxrate2'];
$status = $data['status'];
$paymentmethod = $data['paymentmethod'];
$notes = $data['notes'];
$result = select_query("tblaccounts", "SUM(amountin)-SUM(amountout)", array("invoiceid" => $invoiceid));
$data = mysql_fetch_array($result);
$amountpaid = $data[0];
$balance = $total - $amountpaid;
$balance = format_as_currency($balance);
$gatewaytype = get_query_val("tblpaymentgateways", "value", array("gateway" => $paymentmethod, "setting" => "type"));
$ccgateway = (($gatewaytype == "CC" || $gatewaytype == "OfflineCC") ? true : false);
$apiresults = array("result" => "success", "invoiceid" => $invoiceid, "invoicenum" => $invoicenum, "userid" => $userid, "date" => $date, "duedate" => $duedate, "datepaid" => $datepaid, "subtotal" => $subtotal, "credit" => $credit, "tax" => $tax, "tax2" => $tax2, "total" => $total, "balance" => $balance, "taxrate" => $taxrate, "taxrate2" => $taxrate2, "status" => $status, "paymentmethod" => $paymentmethod, "notes" => $notes, "ccgateway" => $ccgateway);
$result = select_query("tblinvoiceitems", "", array("invoiceid" => $invoiceid));

while ($data = mysql_fetch_array($result)) {
	$apiresults['items']['item'][] = array("id" => $data['id'], "type" => $data['type'], "relid" => $data['relid'], "description" => $data['description'], "amount" => $data['amount'], "taxed" => $data['taxed']);
}

$apiresults['transactions'] = "";
$result = select_query("tblaccounts", "", array("invoiceid" => $invoiceid));

while ($data = mysql_fetch_assoc($result)) {
	$apiresults['transactions']['transaction'][] = $data;
}

$responsetype = "xml";
?>