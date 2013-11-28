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

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
$GATEWAY = getGatewayVariables("worldpay");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}


if ($GATEWAY['prpassword']) {
	if ($GATEWAY['prpassword'] != $_REQUEST['callbackPW']) {
		logTransaction($GATEWAY['name'], $_REQUEST, "Payment Response Password Mismatch");
		exit();
	}
}

$invoiceid = checkCbInvoiceID($_POST['cartId'], "WorldPay");
echo "<WPDISPLAY ITEM=\"banner\">";

if ($_POST['transStatus'] == "Y") {
	if ($invoiceid) {
		checkCbTransID($_POST['transId']);
		addInvoicePayment($invoiceid, $_POST['transId'], "", "", "worldpay");
		logTransaction("WorldPay", $_POST, "Successful");
		echo "<p align=\"center\"><a href=\"" . $CONFIG['SystemURL'] . "/viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true\">Click here to return to " . $CONFIG['CompanyName'] . "</a></p>";
		exit();
	}
	else {
		logTransaction("WorldPay", $_POST, "Error");
	}
}
else {
	logTransaction("WorldPay", $_POST, "Unsuccessful");
}

echo "<p align=\"center\"><a href=\"" . $CONFIG['SystemURL'] . "/viewinvoice.php?id=" . $invoiceid . "&paymentfailed=true\">Click here to return to " . $CONFIG['CompanyName'] . "</a></p>";
?>