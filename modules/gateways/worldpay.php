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
 * */

function worldpay_config() {
	global $CONFIG;

	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "WorldPay" ), "installationid" => array( "FriendlyName" => "Installation ID", "Type" => "text", "Size" => "20", "Description" => "Enter your WorldPay Installation ID" ), "prpassword" => array( "FriendlyName" => "Payment Response Password", "Type" => "text", "Size" => "20", "Description" => "Enter your WorldPay Payment Response Password used in Callback Validations (Optional)" ), "testmode" => array( "FriendlyName" => "Test Mode", "Type" => "yesno" ) );
	return $configarray;
}


function worldpay_link($params) {
	$address = $params["clientdetails"]["address1"];

	if ($params["clientdetails"]["address2"]) {
		$address .= "
" . $params["clientdetails"]["address2"];
	}

	$address .= "
" . $params["clientdetails"]["city"];
	$address .= "
" . $params["clientdetails"]["state"];
	$code = "<form action=\"https://secure.worldpay.com/wcc/purchase\" method=\"post\">
<input type=\"hidden\" name=\"instId\" value=\"" . $params["installationid"] . "\">
<input type=\"hidden\" name=\"cartId\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"desc\" value=\"" . $params["description"] . "\">
<input type=\"hidden\" name=\"amount\" value=\"" . $params["amount"] . "\">
<input type=\"hidden\" name=\"currency\" value=\"" . $params["currency"] . "\">
<input type=\"hidden\" name=\"name\" value=\"" . $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"] . "\">
<input type=\"hidden\" name=\"email\" value=\"" . $params["clientdetails"]["email"] . "\">
<input type=\"hidden\" name=\"address\" value=\"" . $address . "\">
<input type=\"hidden\" name=\"postcode\" value=\"" . $params["clientdetails"]["postcode"] . "\">
<input type=\"hidden\" name=\"country\" value=\"" . $params["clientdetails"]["country"] . "\">
<input type=\"hidden\" name=\"tel\" value=\"" . $params["clientdetails"]["phonenumber"] . "\">";

	if ($params["testmode"] == "on") {
		$code .= "
<input type=\"hidden\" name=\"testMode\" value=\"100\">";
	}


	if ($params["authmode"] == "on") {
		$code .= "
<input type=\"hidden\" name=\"authMode\" value=\"E\">";
	}

	$code .= "
<INPUT TYPE=\"hidden\" NAME=\"MC_callback\" VALUE=\"" . $params["systemurl"] . "/modules/gateways/callback/worldpay.php\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>