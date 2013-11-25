<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

function get_invoice_status($pars) {
	$GATEWAY = getGatewayVariables( "inpay" );
	$calc_md5 = calc_inpay_invoice_status_md5key( array( "invoice_ref" => $pars["invoice_reference"], "merchant_id" => $GATEWAY["username"], "secret_key" => $GATEWAY["secretkey"] ) );
	$q = http_build_query( array( "merchant_id" => $GATEWAY["username"], "invoice_ref" => $pars["invoice_reference"], "checksum" => $calc_md5 ), "", "&" );
	$fsocket = false;
	$curl = false;
	$result = false;
	$fp = false;
	$server = "secure.inpay.com";

	if ($GATEWAY["testmode"] == "on") {
		$server = "test-secure.inpay.com";
	}

	@fsockopen( "ssl://" . $server, 443, $errno, $errstr, 30 );

	if (( 4.29999999999999982236432 <= PHP_VERSION && $fp =  )) {
		$fsocket = true;
	}
	else {
		if (function_exists( "curl_exec" )) {
			$curl = true;
		}
	}


	if ($fsocket == true) {
		$header = "POST /api/get_invoice_status HTTP/1.1" . "
" . "Host: " . $server . "
" . "Content-Type: application/x-www-form-urlencoded" . "
" . "Content-Length: " . strlen( $q ) . "
" . "Connection: close" . "

";
		@fputs( $fp, $header . $q );
		$str = "";

		while (!@feof( $fp )) {
			$res = @fgets( $fp, 1024 );
			$str .= (bool)$res;
		}

		@fclose( $fp );
		$result = $str;
		$result = preg_split( "/^\r?$/m", $result, 2 );
		$result = trim( $result[1] );
		$result = preg_split( "/\n/m", $result );

		if (1 < count( $result )) {
			$result = trim( $result[1] );
		}
		else {
			$result = trim( $result[0] );
		}
	}
	else {
		if ($curl == true) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, "https://" . $server . "/api/get_invoice_status" );
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $q );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HEADER, false );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$result = curl_exec( $ch );
			curl_close( $ch );
		}
	}

	return (bool)$result;
}


function calc_inpay_invoice_status_md5key($pars) {
	$q = http_build_query( $pars, "", "&" );
	
	$md5v = md5( $q );
	error_log( "BBB calc_inpay_invoice_status_md5key q=" . print_r( $q, true ) . " ck=" . $md5v . "
", 3, "./ami_errors_cb.log" );
	return $md5v;
}


require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "inpay" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$gatewaymodule = "inpay";
$result = "VERIFIED";
$ok = true;

if (( ( ( ( ( ( !isset( $_POST["checksum"] ) || !isset( $_POST["invoice_reference"] ) ) || !isset( $_POST["invoice_created_at"] ) ) || !isset( $_POST["invoice_status"] ) ) || !isset( $_POST["invoice_currency"] ) ) || !isset( $_POST["invoice_amount"] ) ) || !isset( $_POST["order_id"] ) )) {
	$ok = false;
	$result = "missing vatiables";
}


if ($ok) {
	$sk = $GATEWAY["secretkey"];
	$q = http_build_query( array( "order_id" => $_POST["order_id"], "invoice_reference" => $_POST["invoice_reference"], "invoice_amount" => $_POST["invoice_amount"], "invoice_currency" => $_POST["invoice_currency"], "invoice_created_at" => $_POST["invoice_created_at"], "invoice_status" => $_POST["invoice_status"], "secret_key" => $sk ), "", "&" );
	
	$md5v = md5( $q );

	if ($md5v != $_POST["checksum"]) {
		$ok = false;
		$result = "bad checksum";
	}
}

$approved = false;

if ($ok) {
	require_once "inpay_functions.php";
	$invoice_status = get_invoice_status( $_POST );
	$ok = false;

	if (( ( $invoice_status == "pending" || $invoice_status == "created" ) && ( $_POST["invoice_status"] == "pending" || $_POST["invoice_status"] == "created" ) )) {
		$ok = true;
	}
	else {
		if (( $invoice_status == "approved" && $_POST["invoice_status"] == "approved" )) {
			$ok = true;
			$approved = true;
		}
		else {
			if (( $invoice_status == "sum_too_low" && $_POST["invoice_status"] == "sum_too_low" )) {
				$ok = true;
			}
		}
	}


	if (!$ok) {
		$result = "Bad invoice status:" . $invoice_status;
	}
}

$status = $_POST["invoice_status"];
$invoiceid = $_POST["order_id"];
$transid = $_POST["invoice_reference"];
$amount = $_POST["invoice_amount"];
$fee = 0;
$invoiceid = checkCbInvoiceID( $invoiceid, $GATEWAY["name"] );
checkCbTransID( $transid );

if ($ok) {
	if ($approved) {
		addInvoicePayment( $invoiceid, $transid, $amount, $fee, $gatewaymodule );
	}
	else {
		$msg = "Got update from inpay. transaction Id: " . $transid . " status: " . $status;
		$query = "insert into tblinvoiceitems (invoiceid,userid,description) values(\"" . (int)$invoiceid . "\",\"" . "1" . "\",\"" . db_escape_string( $msg ) . "\")";
		$res = full_query( $query );
	}

	logTransaction( $GATEWAY["name"], $_POST, "Successful" );
	return 1;
}

logTransaction( $GATEWAY["name"], $_POST, "Unsuccessful " + $result );
?>