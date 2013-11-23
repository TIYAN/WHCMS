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
$whmcs->load_function( "clientarea" );
$GATEWAY = getGatewayVariables( "worldpayfuturepay" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$invoiceid = mysql_real_escape_string( $_POST["cartId"] );
$futurepayid = mysql_real_escape_string( $_POST["futurePayId"] );
$transid = mysql_real_escape_string( $_POST["transId"] );
$invoiceid = checkCbInvoiceID( $invoiceid, "WorldPay FuturePay" );
initialiseClientArea( $_LANG["ordercheckout"], "", $_LANG["ordercheckout"] );
echo processSingleTemplate( "/templates/" . $whmcs->get_sys_tpl_name() . "/header.tpl", $smarty->_tpl_vars );
echo "<WPDISPLAY ITEM=\"banner\">";
$result = select_query( "tblinvoices", "", array( "id" => $invoiceid ) );
$data = mysql_fetch_array( $result );
$userid = $data["userid"];

if ($_POST["transStatus"] == "Y") {
	logTransaction( "WorldPay FuturePay", $_POST, "Successful" );
	update_query( "tblclients", array( "gatewayid" => $futurepayid ), array( "id" => $userid ) );
	addInvoicePayment( $invoiceid, $transid, "", "", "worldpayfuturepay" );
	echo "<p align=\"center\"><a href=\"" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true\">Click here to return to " . $CONFIG["CompanyName"] . "</a></p>";
}
else {
	logTransaction( "WorldPay FuturePay", $_POST, "Unsuccessful" );
	echo "<p align=\"center\"><a href=\"" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $invoiceid . "&paymentfailed=true\">Click here to return to " . $CONFIG["CompanyName"] . "</a></p>";
}

echo processSingleTemplate( "/templates/" . $whmcs->get_sys_tpl_name() . "/footer.tpl", $smarty->_tpl_vars );
?>