<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 * */

require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "chronopay" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}


if ($_POST["transaction_type"] == "onetime") {
	$transid = $_POST["transaction_id"];
	$invoiceid = $_POST["cs1"];
	$amount = $_POST["total"];
	$invoiceid = checkCbInvoiceID( $_POST["cs1"], "ChronoPay" );

	if ($invoiceid) {
		addInvoicePayment( $invoiceid, $transid, "", "", "chronopay" );
		logTransaction( "ChronoPay", $_REQUEST, "Successful" );
		header( "Location: " . $CONFIG["SystemURL"] . ( "/viewinvoice.php?id=" . $id ) );
		exit();
		return 1;
	}

	logTransaction( "ChronoPay", $_REQUEST, "Error" );
	header( "Location: " . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices" );
	exit();
	return 1;
}

logTransaction( "ChronoPay", $_REQUEST, "Invalid" );
header( "Location: " . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices" );
exit();
?>