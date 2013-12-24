<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 * */

function ewayuk_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "eWay UK" ), "customerid" => array( "FriendlyName" => "Customer ID", "Type" => "text", "Size" => "20" ), "username" => array( "FriendlyName" => "Username", "Type" => "text", "Size" => "20" ), "testmode" => array( "FriendlyName" => "Test Mode", "Type" => "yesno", "Description" => "Tick this to enable test mode" ) );
	return $configarray;
}


function ewayuk_link($params) {
	$query = "";
	$gatewaytestmode = $params['testmode'];

	if ($gatewaytestmode == "on") {
		$query .= "CustomerID=87654321";
		$query .= "&UserName=TestAccount";
	}
	else {
		$query .= "CustomerID=" . $params['customerid'];
		$query .= "&UserName=" . $params['username'];
	}

	$query .= "&MerchantInvoice=" . $params['invoiceid'];
	$query .= "&MerchantReference=" . $params['invoiceid'];
	$query .= "&Amount=" . urlencode( $params['amount'] );
	$query .= "&Currency=" . $params['currency'];
	$query .= "&CustomerFirstName=" . $params['clientdetails']['firstname'];
	$query .= "&CustomerLastName=" . $params['clientdetails']['lastname'];
	$query .= "&CustomerAddress=" . $params['clientdetails']['address1'] . " " . $params['clientdetails']['address2'];
	$query .= "&CustomerCity=" . $params['clientdetails']['city'];
	$query .= "&CustomerState=" . $params['clientdetails']['state'];
	$query .= "&CustomerPostCode=" . $params['clientdetails']['postcode'];
	$query .= "&CustomerCountry=" . $params['clientdetails']['country'];
	$query .= "&CustomerEmail=" . $params['clientdetails']['email'];
	$query .= "&CustomerPhone=" . $params['clientdetails']['phonenumber'];
	$query .= "&CancelUrl=" . urlencode( $params['systemurl'] . "/viewinvoice.php?id=" . $params['invoiceid'] );
	$query .= "&ReturnUrl=" . urlencode( $params['systemurl'] . "/modules/gateways/callback/ewayuk.php" );
	$query = str_replace( " ", "%20", $query );
	$posturl = "https://payment.ewaygateway.com/Request/?" . $query;
	$response = curlCall( $posturl, "" );
	$responsemode = strtolower( ewayuk_fetch_data( $response, "<Result>", "</Result>" ) );

	if ($responsemode == "true") {
		$redirecturl = ewayuk_fetch_data( $response, "<Uri>", "</Uri>" );
		$code = "<input type=\"button\" value=\"" . $params['langpaynow'] . "\" onclick=\"window.location='" . $redirecturl . "'\" />
</form>";
		return $code;
	}

	logTransaction( "eWay UK", $response, "Error" );
	return "An Error Occurred. Please try again later or submit a ticket if the error persists.";
}


function ewayuk_fetch_data($string, $start_tag, $end_tag) {
	$position = stripos( $string, $start_tag );
	$str = substr( $string, $position );
	$str_second = substr( $str, strlen( $start_tag ) );
	$second_positon = stripos( $str_second, $end_tag );
	$str_third = substr( $str_second, 0, $second_positon );
	$ewayukhp_fetch_data = trim( $str_third );
	return $ewayukhp_fetch_data;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>