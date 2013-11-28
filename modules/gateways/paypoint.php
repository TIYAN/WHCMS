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

function paypoint_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "PayPoint.net (SecPay)" ), "merchantid" => array( "FriendlyName" => "Merchant ID", "Type" => "text", "Size" => "20" ), "remotepw" => array( "FriendlyName" => "Remote Password", "Type" => "text", "Size" => "30" ), "digestkey" => array( "FriendlyName" => "Digest Key", "Type" => "text", "Size" => "40" ), "testmode" => array( "FriendlyName" => "Test Mode", "Type" => "yesno" ) );
	return $configarray;
}


function paypoint_link($params) {
	$transid = $params["invoiceid"] . "-" . date( "Ymdhis" );
	
	$digest = md5( $transid . $params["amount"] . $params["remotepw"] );
	$code = "<form method=\"post\" action=\"https://www.secpay.com/java-bin/ValCard\">
<input type=\"hidden\" name=\"merchant\" value=\"" . $params["merchantid"] . "\" />
<input type=\"hidden\" name=\"trans_id\" value=\"" . $transid . "\" />
<input type=\"hidden\" name=\"amount\" value=\"" . $params["amount"] . "\" />
<input type=\"hidden\" name=\"currency\" value=\"" . $params["currency"] . "\" />
<input type=\"hidden\" name=\"repeat\" value=\"true\" />
<input type=\"hidden\" name=\"callback\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/paypoint.php\" />
<input type=\"hidden\" name=\"options\" value=\"cb_post=true,md_flds=trans_id:amount:callback\">
<input type=\"hidden\" name=\"digest\" value=\"" . $digest . "\" />";

	if ($params["testmode"]) {
		$code .= "<input type=\"hidden\" name=\"test_status\" value=\"true\">";
	}

	$code .= "<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>