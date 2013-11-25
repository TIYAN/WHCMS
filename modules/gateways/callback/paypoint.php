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

require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "paypoint" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$transid = $_REQUEST["trans_id"];
$valid = $_REQUEST["valid"];
$authcode = $_REQUEST["auth_code"];
$amount = $_REQUEST["amount"];
$code = $_REQUEST["code"];
$teststatus = $_REQUEST["test_status"];
$hash = $_REQUEST["hash"];
$expiry = $_REQUEST["expiry"];
$card_no = $_REQUEST["card_no"];
$customer = $_REQUEST["customer"];
$invoiceid = explode( "-", $transid );
$invoiceid = $invoiceid[0];
$invoiceid = checkCbInvoiceID( $invoiceid, "PayPoint" );

if ($GATEWAY["secretword"]) {
	$string_to_hash = "transid=" . $transid . "&amount=" . $amount . "&callback=" . $GATEWAY["systemurl"] . "/modules/gateways/callback/paypoint.php&" . $GATEWAY["digestkey"];
	
	$check_key = md5( $string_to_hash );

	if ($check_key != $hash) {
		logTransaction( "PayPoint", $_REQUEST, "MD5 Hash Failure" );
		exit();
	}
}


if (( $teststatus && !$GATEWAY["testmode"] )) {
	logTransaction( "PayPoint", $_REQUEST, "Invalid Test Mode" );
	exit();
}


if (( $code == "A" && $valid )) {
	addInvoicePayment( $invoiceid, $_REQUEST["x_trans_id"], $amount, $fee, "tco" );
	logTransaction( "PayPoint", $_REQUEST, "Successful" );
	echo "<meta http-equiv=\"refresh\" content=\"2;url=" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true\">";
	return 1;
}

logTransaction( "PayPoint", $_REQUEST, "Unsuccessful" );
echo "<meta http-equiv=\"refresh\" content=\"2;url=" . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices\">";
?>