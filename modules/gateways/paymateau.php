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

function paymateau_activate() {
	defineGatewayField( "paymateau", "text", "mid", "", "Member ID", "20", "" );
}


function paymateau_link($params) {
	$code = "<form action=\"https://www.paymate.com/PayMate/ExpressPayment\" method=\"post\">
<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
<input type=\"hidden\" name=\"mid\" value=\"" . $params["mid"] . "\">
<input type=\"hidden\" name=\"amt\" value=\"" . $params["amount"] . "\">
<input type=\"hidden\" name=\"amt_editable\" value=\"N\">
<input type=\"hidden\" name=\"currency\" value=\"" . $params["currency"] . "\">
<input type=\"hidden\" name=\"ref\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"return\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/paymate.php\">
<input type=\"hidden\" name=\"back\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/paymate.php\">
<input type=\"hidden\" name=\"notify\" value=\"place holder for notify url\">
<input type=\"hidden\" name=\"popup\" value=\"false\">
<input type=\"hidden\" name=\"pmt_sender_email\" value=\"" . $params["clientdetails"]["email"] . "\">
<input type=\"hidden\" name=\"pmt_contact_firstname\" value=\"" . $params["clientdetails"]["firstname"] . "\">
<input type=\"hidden\" name=\"pmt_contact_surname\" value=\"" . $params["clientdetails"]["lastname"] . "\">
<input type=\"hidden\" name=\"pmt_contact_phone\" value=\"" . $params["clientdetails"]["phonenumber"] . "\">
<input type=\"hidden\" name=\"pmt_country\" value=\"" . $params["clientdetails"]["country"] . "\">
<input type=\"hidden\" name=\"regindi_sub\" value=\"" . $params["clientdetails"]["city"] . "\">
<input type=\"hidden\" name=\"regindi_address1\" value=\"" . $params["clientdetails"]["address1"] . "\">
<input type=\"hidden\" name=\"regindi_address2\" value=\"" . $params["clientdetails"]["address2"] . "\">
<input type=\"hidden\" name=\"regindi_state\" value=\"" . $params["clientdetails"]["state"] . "\">
<input type=\"hidden\" name=\"regindi_pcode\" value=\"" . $params["clientdetails"]["postcode"] . "\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["paymateauname"] = "paymateau";
$GATEWAYMODULE["paymateauvisiblename"] = "Paymate";
$GATEWAYMODULE["paymateautype"] = "Invoices";
?>