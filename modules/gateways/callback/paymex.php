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
$GATEWAY = getGatewayVariables("paymex");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$invoiceid = checkCbInvoiceID($_GET['xinv'], "Paymex");

if ($_GET['xresp'] == "1") {
	$result = select_query("tblinvoices", "total", array("id" => $invoiceid));
	$data = mysql_fetch_array($result);
	$total = $data['total'];
	$fee = $total * 0.0294999999999999984456878 + 0.550000000000000044408921;
	$pos = strpos($fee, ".");
	$pos = $pos + 3;
	$fee = substr($fee, 0, $pos);
	addInvoicePayment($invoiceid, $invoiceid, "", $fee, "paymex");
	logTransaction("Paymex", $_REQUEST, "Successful");
	redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
	return 1;
}

logTransaction("Paymex", $_REQUEST, "Unsuccessful");
redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
?>