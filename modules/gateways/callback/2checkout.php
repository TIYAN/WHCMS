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
$GATEWAY = getGatewayVariables( "tco" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}


if ($GATEWAY["secretword"]) {
	$string_to_hash = $GATEWAY["secretword"] . $GATEWAY["vendornumber"] . $_REQUEST["x_trans_id"] . $_REQUEST["x_amount"];
	$check_key = strtoupper( md5( $string_to_hash ) );

	if ($check_key != $_REQUEST["x_MD5_Hash"]) {
		logTransaction( $GATEWAY["name"], $_REQUEST, "MD5 Hash Failure" );
		header( "Location: ../../../clientarea.php" );
		exit();
	}
}

echo "<html>
<head>
<title>" . $CONFIG["CompanyName"] . "</title>
</head>
<body>
<p>Payment Processing Completed. However it may take a while for 2CheckOut fraud verification to complete and the payment to be reflected on your account. Please wait while you are redirected back to the client area...</p>
";

if ($_POST["x_response_code"] == "1") {
	$id = checkCbInvoiceID( $_POST["x_invoice_num"], "2CheckOut" );

	if ($GATEWAY["skipfraudcheck"]) {
		echo "<meta http-equiv=\"refresh\" content=\"2;url=" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $id . "&paymentsuccess=true\">";
	}
	else {
		echo "<meta http-equiv=\"refresh\" content=\"2;url=" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $id . "&pendingreview=true\">";
	}
}
else {
	logTransaction( "2CheckOut", $_REQUEST, "Unsuccessful" );
	echo "<meta http-equiv=\"refresh\" content=\"2;url=" . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices\">";
}

echo "
</body>
</html>";
?>