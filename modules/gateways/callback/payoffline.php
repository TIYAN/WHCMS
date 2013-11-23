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
$GATEWAY = getGatewayVariables( "payoffline" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}


if (( !$_REQUEST["code"] && !$_REQUEST["callbackvars"] )) {
	header( "Status: 404 Not Found" );
	exit();
}
else {
	header( "Status: 200 OK" );
}

$amount = $_REQUEST["amt"];
$callbackvars2 = explode( "&amp;", $callbackvars );
foreach ($callbackvars2 as $value) {
	$values[] = explode( "=", $value );
}


if ($code == "5") {
	logTransaction( "Pay Offline", $orgipn, "Pending" );
	exit();
}


if ($transid) {
	checkCbTransID( $transid );
}


if ($code == "0") {
	$invoiceid = $values[0][1];

	if ($invoiceid) {
		checkCbInvoiceID( $invoiceid, "PayOffline" );
		addInvoicePayment( $invoiceid, $transid, $amount, "", "payoffline" );
		logTransaction( "Pay Offline", $_REQUEST, "Successful" );
	}
	else {
		$userid = $values[2][1];
		$userid = get_query_val( "tblclients", "id", array( "id" => $userid ) );

		if (!$userid) {
			logTransaction( "Pay Offline", $_REQUEST, "Invoice Not Found" );
		}
		else {
			insert_query( "tblcredit", array( "clientid" => $userid, "date" => "now()", "description" => "Pay Offline Transaction ID " . $transid, "amount" => $amount ) );
			update_query( "tblclients", array( "credit" => "+=" . $amount ), array( "id" => $userid ) );
			logTransaction( "Pay Offline", $_REQUEST, "Credit Added" );
		}
	}
}

exit();
?>