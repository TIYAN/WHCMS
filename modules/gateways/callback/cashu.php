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

require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "cashu" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$amount = $_REQUEST["amount"];
$currency = $_REQUEST["currency"];
$trn_id = $_REQUEST["trn_id"];
$session_id = $_REQUEST["session_id"];
$verificationString = $_REQUEST["verificationString"];
$verstr = array( strtolower( $GATEWAY["merchantid"] ), strtolower( $trn_id ), $GATEWAY["encryptionkeyword"] );
$verstr = implode( ":", $verstr );
$verstr = sha1( $verstr );

if ($verstr == $verificationString) {
	$invoiceid = checkCbInvoiceID( $session_id, "CashU" );
	addInvoicePayment( $invoiceid, $trn_id, $amount, "0", "cashu" );
	logTransaction( "CashU", $debugdata, "Successful" );
	header( "Location: " . $CONFIG["SystemURL"] . ( "/viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true" ) );
	exit();
	return 1;
}

logTransaction( "CashU", $_REQUEST, "Invalid Hash" );

if ($session_id) {
	header( "Location: " . $CONFIG["SystemURL"] . ( "/viewinvoice.php?id=" . $session_id . "&paymentfailed=true" ) );
}
else {
	header( "Location: " . $CONFIG["SystemURL"] . "/clientarea.php" );
}

exit();
?>