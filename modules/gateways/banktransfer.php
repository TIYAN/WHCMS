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

function banktransfer_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Bank Transfer" ), "instructions" => array( "FriendlyName" => "Bank Transfer Instructions", "Type" => "textarea", "Rows" => "5", "Value" => "Bank Name:
Payee Name:
Sort Code:
Account Number:", "Description" => "The instructions you want displaying to customers who choose this payment method - the invoice number will be shown underneath the text entered above" ) );
	return $configarray;
}


function banktransfer_link($params) {
	global $_LANG;

	$code = "<p>" . nl2br( $params["instructions"] ) . "<br />" . $_LANG["invoicerefnum"] . ": " . $params["invoiceid"] . "</p>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>