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
 * */

function tcoconvertcurrency($amount, $currency, $invoiceid) {
	$result = select_query( "tblcurrencies", "id", array( "code" => $currency ) );
	$data = mysql_fetch_array( $result );
	$currencyid = $data["id"];

	if (!$currencyid) {
		logTransaction( $GATEWAY["name"], $_POST, "Unrecognised Currency" );
		exit();
	}

	$result = select_query( "tblinvoices", "userid,total", array( "id" => $invoiceid ) );
	$data = mysql_fetch_array( $result );
	$userid = $data["userid"];
	$total = $data["total"];
	$currency = getCurrency( $userid );

	if ($currencyid != $currency["id"]) {
		$amount = convertCurrency( $amount, $currencyid, $currency["id"] );

		if (( $total < $amount + 1 && $amount - 1 < $total )) {
			$amount = $total;
		}
	}

	return $amount;
}


require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$gatewaymodule = "tco";
$GATEWAY = getGatewayVariables( $gatewaymodule );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}


if ($GATEWAY["secretword"]) {
	$string_to_hash = $_REQUEST["sale_id"] . $GATEWAY["vendornumber"] . $_REQUEST["invoice_id"] . $GATEWAY["secretword"];
	$check_key = strtoupper( md5( $string_to_hash ) );

	if ($check_key != $_POST["md5_hash"]) {
		logTransaction( $GATEWAY["name"], $_POST, "MD5 Hash Failure" );
		exit();
	}
}

$message_type = $_POST["message_type"];
$serviceid = $_POST["vendor_order_id"];
$transid = $_POST["sale_id"];
$recurringtransid = $transid . "-" . $_POST["invoice_id"];
$amount = ($_POST["invoice_list_amount"] ? $_POST["invoice_list_amount"] : $_POST["item_list_amount_1"]);
$recurstatus = trim( $_POST["item_rec_status_1"] );
$invoiceid = ($_POST["item_id_1"] ? $_POST["item_id_1"] : $_POST["item_id_2"]);
$currency = $_POST["list_currency"];
$message_type = $_POST["message_type"];
$fee = $amount * 0.0550000000000000002775558;
$pos = strpos( $fee, "." );
$pos = $pos + 3;
$fee = substr( $fee, 0, $pos );
$fee = $fee + 0.45000000000000001110223;

if (( $message_type == "FRAUD_STATUS_CHANGED" && !$GATEWAY["skipfraudcheck"] )) {
	$fraud_status = $_POST["fraud_status"];

	if ($fraud_status == "pass") {
		if (( $recurstatus && $serviceid )) {
			$query = "SELECT tblinvoices.id FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid WHERE tblinvoiceitems.relid=" . (int)$serviceid . " AND tblinvoiceitems.type='Hosting' AND tblinvoices.status='Unpaid' ORDER BY tblinvoices.id ASC";
			$result = full_query( $query );
			$data = mysql_fetch_array( $result );
			$invoiceid = $data[0];
		}

		$invoiceid = checkCbInvoiceID( $invoiceid, $GATEWAY["name"] );
		logTransaction( $GATEWAY["name"], $_POST, "Fraud Status Pass" );
		checkCbTransID( $transid );
		$amount = tcoconvertcurrency( $amount, $currency, $invoiceid );
		addInvoicePayment( $invoiceid, $transid, $amount, $fee, $gatewaymodule );
		return 1;
	}

	logTransaction( $GATEWAY["name"], $_POST, "Fraud Status Fail" );
	return 1;
}


if (( $message_type == "ORDER_CREATED" && $GATEWAY["skipfraudcheck"] )) {
	if (( $recurstatus && $serviceid )) {
		$query = "SELECT tblinvoices.id FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid WHERE tblinvoiceitems.relid=" . (int)$serviceid . " AND tblinvoiceitems.type='Hosting' AND tblinvoices.status='Unpaid' ORDER BY tblinvoices.id ASC";
		$result = full_query( $query );
		$data = mysql_fetch_array( $result );
		$invoiceid = $data[0];
	}

	$invoiceid = checkCbInvoiceID( $invoiceid, $GATEWAY["name"] );
	logTransaction( $GATEWAY["name"], $_POST, "Payment Success" );
	checkCbTransID( $transid );
	$amount = tcoconvertcurrency( $amount, $currency, $invoiceid );
	addInvoicePayment( $invoiceid, $transid, $amount, $fee, $gatewaymodule );
	return 1;
}


if ($message_type == "RECURRING_INSTALLMENT_SUCCESS") {
	$query = "SELECT tblinvoices.id FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid WHERE tblinvoiceitems.relid=" . (int)$serviceid . " AND tblinvoiceitems.type='Hosting' AND tblinvoices.status='Unpaid' ORDER BY tblinvoices.id ASC";
	$result = full_query( $query );
	$data = mysql_fetch_array( $result );
	$invoiceid = $data[0];

	if (!$invoiceid) {
		$query = "SELECT tblinvoices.id,tblinvoices.userid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid WHERE tblinvoiceitems.relid=" . (int)$serviceid . " AND tblinvoiceitems.type='Hosting' AND tblinvoices.status='Paid' ORDER BY tblinvoices.id DESC";
		$result = full_query( $query );
		$data = mysql_fetch_array( $result );
		$invoiceid = $data[0];
		$userid = $data[1];
	}


	if (!$invoiceid) {
		$query = "SELECT tblinvoices.id,tblinvoices.userid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid INNER JOIN tblhosting ON tblhosting.id=tblinvoiceitems.relid WHERE tblhosting.subscriptionid='" . db_escape_string( $transid ) . "' AND tblinvoiceitems.type='Hosting' AND tblinvoices.status='Unpaid' ORDER BY tblinvoices.id ASC";
		$result = full_query( $query );
		$data = mysql_fetch_array( $result );
		$invoiceid = $data[0];
		$userid = $data[1];
	}


	if (!$invoiceid) {
		$query = "SELECT tblinvoices.id,tblinvoices.userid FROM tblinvoiceitems INNER JOIN tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid INNER JOIN tblhosting ON tblhosting.id=tblinvoiceitems.relid WHERE tblhosting.subscriptionid='" . db_escape_string( $transid ) . "' AND tblinvoiceitems.type='Hosting' AND tblinvoices.status='Paid' ORDER BY tblinvoices.id DESC";
		$result = full_query( $query );
		$data = mysql_fetch_array( $result );
		$invoiceid = $data[0];
		$userid = $data[1];
	}

	checkCbTransID( $recurringtransid );

	if (( !$invoiceid && !$serviceid )) {
		logTransaction( $GATEWAY["name"], array_merge( array( "InvoiceLookup" => "No Service ID Found in Callback" ), $_POST ), "Recurring Error" );
	}


	if (!$invoiceid) {
		logTransaction( $GATEWAY["name"], array_merge( array( "InvoiceLookup" => "No invoice match found for Service ID " . $serviceid . " or Subscription ID" ), $_POST ), "Recurring Error" );
	}

	logTransaction( $GATEWAY["name"], $_POST, "Recurring Success" );
	$amount = tcoconvertcurrency( $amount, $currency, $invoiceid );
	addInvoicePayment( $invoiceid, $recurringtransid, $amount, $fee, $gatewaymodule );

	if (( $serviceid && $transid )) {
		update_query( "tblhosting", array( "subscriptionid" => $transid ), array( "id" => $serviceid ) );
		return 1;
	}
}
else {
	if ($message_type == "RECURRING_INSTALLMENT_FAILED") {
		logTransaction( $GATEWAY["name"], $_POST, "Recurring Failed" );
		return 1;
	}

	logTransaction( $GATEWAY["name"], $_POST, "Notification Only" );
}

?>