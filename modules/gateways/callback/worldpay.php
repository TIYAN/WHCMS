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
$GATEWAY = getGatewayVariables( "worldpay" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}


if ($GATEWAY["prpassword"]) {
	if ($GATEWAY["prpassword"] != $_REQUEST["callbackPW"]) {
		logTransaction( $GATEWAY["name"], $_REQUEST, "Payment Response Password Mismatch" );
		exit();
	}
}

echo "<WPDISPLAY ITEM=\"banner\">";

if ($_POST["transStatus"] == "Y") {
	$id = checkCbInvoiceID( $_POST["cartId"], "WorldPay" );

	if ($id) {
		checkCbTransID( $_POST["transId"] );
		addInvoicePayment( $id, $_POST["transId"], "", "", "worldpay" );
		logTransaction( "WorldPay", $_POST, "Successful" );
		echo "<p align=\"center\"><a href=\"" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $id . "&paymentsuccess=true\">Click here to return to " . $CONFIG["CompanyName"] . "</a></p>";
		exit();
	}
	else {
		logTransaction( "WorldPay", $_POST, "Error" );
	}
}
else {
	logTransaction( "WorldPay", $_POST, "Unsuccessful" );
}

echo "<p align=\"center\"><a href=\"" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $id . "&paymentfailed=true\">Click here to return to " . $CONFIG["CompanyName"] . "</a></p>";
?>