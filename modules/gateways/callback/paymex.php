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
$GATEWAY = getGatewayVariables( "paymex" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}


if ($_GET["xresp"] == "1") {
	$id = checkCbInvoiceID( $_GET["xinv"], "Paymex" );
	$result = select_query( "tblinvoices", "total", array( "id" => $_GET["xinv"] ) );
	$data = mysql_fetch_array( $result );
	$total = $data["total"];

	if ($id != "") {
		$fee = $total * 0.0294999999999999984456878 + 0.550000000000000044408921;
		$pos = strpos( $fee, "." );
		$pos = $pos + 3;
		$fee = substr( $fee, 0, $pos );
		addInvoicePayment( $_GET["xinv"], $_GET["xinv"], "", $fee, "tco" );
		logTransaction( "Paymex", $_REQUEST, "Successful" );
		header( "Location: " . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $_GET["xinv"] );
		exit();
		return 1;
	}

	logTransaction( "Paymex", $_REQUEST, "Error" );
	header( "Location: " . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices" );
	exit();
	return 1;
}

logTransaction( "Paymex", $_REQUEST, "Unsuccessful" );
header( "Location: " . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices" );
exit();
?>