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
$GATEWAY = getGatewayVariables( "egold" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$debugreport = "";
foreach ($_POST as $k => $v) {
	$debugreport .= ( "" . $k . " => " . $v . "
" );
}

$result = select_query( "tblaccounts", "", array( "transid" => $_POST["PAYMENT_BATCH_NUM"] ) );
$num_rows = mysql_num_rows( $result );

if ($num_rows) {
	exit();
}

addInvoicePayment( $_POST["PAYMENT_ID"], $_POST["PAYMENT_BATCH_NUM"], $_POST["PAYMENT_AMOUNT"], "", "egold" );
logTransaction( "E-Gold", $debugreport, "Successful" );
header( "HTTP/1.1 200 OK" );
header( "Status: 200 OK" );
?>