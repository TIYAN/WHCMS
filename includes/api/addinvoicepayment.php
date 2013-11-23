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


if (!function_exists("addInvoicePayment")) {
	require ROOTDIR . "/includes/invoicefunctions.php";
}

$query = "SELECT * FROM tblinvoices WHERE id='" . (int)$_POST['invoiceid'] . "'";
$result = full_query($query);
$data = mysql_fetch_array($result);

if (!$data['id']) {
	$apiresults = array("result" => "error", "message" => "Invoice ID Not Found");
	return null;
}

$date = ($_POST['date'] ? fromMySQLDate($_POST['date']) : "");

if ($date2) {
	$date = fromMySQLDate($date2);
}

addInvoicePayment($_POST['invoiceid'], $_POST['transid'], $_POST['amount'], $_POST['fees'], $_POST['gateway'], $_POST['noemail'], $date);
$apiresults = array("result" => "success");
?>