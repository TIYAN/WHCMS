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

class internetsecure_class {
	var $gateway_url = null;
	var $field_string = null;
	var $fields = array();
	var $gatewayurls = array();
	var $response_string = null;
	var $response = array();

	function seturl($url) {
		$this->gateway_url = $url;
	}


	function add_field($field, $value) {
		$this->fields["" . $field] = urlencode( $value );
	}


	function process() {
		foreach ($this->fields as $key => $value) {
			$this->field_string .= "" . $key . "=" . $value . "&";
		}

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->gateway_url );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, rtrim( $this->field_string, "& " ) );
		$this->response_string = curl_exec( $ch );

		if (curl_errno( $ch )) {
			$this->response["Response Reason Text"] = curl_error( $ch );
			return 3;
		}

		curl_close( $ch );
		$this->response_string = urldecode( $this->response_string );
		$temp_values = explode( "|", $this->response_string );
		$temp_keys = array( "Response Code", "Response Subcode", "Response Reason Code", "Response Reason Text", "Approval Code", "AVS Result Code", "Transaction ID", "Invoice Number", "Description", "Amount", "Method", "Transaction Type", "Customer ID", "Cardholder First Name", "Cardholder Last Name", "Company", "Billing Address", "City", "State", "Zip", "Country", "Phone", "Fax", "Email", "Ship to First Name", "Ship to Last Name", "Ship to Company", "Ship to Address", "Ship to City", "Ship to State", "Ship to Zip", "Ship to Country", "Tax Amount", "Duty Amount", "Freight Amount", "Tax Exempt Flag", "PO Number", "MD5 Hash", "Card Code (CVV2/CVC2/CID) Response Code", "Cardholder Authentication Verification Value (CAVV) Response Code" );
		$i = 9;

		while ($i <= 27) {
			array_push( $temp_keys, "Reserved Field " . $i );
			++$i;
		}

		$i = 9;

		while (sizeof( $temp_keys ) < sizeof( $temp_values )) {
			array_push( $temp_keys, "Merchant Defined Field " . $i );
			++$i;
		}

		$i = 9;

		while ($i < sizeof( $temp_values )) {
			$this->response["" . $temp_keys[$i]] = $temp_values[$i];
			++$i;
		}

		return $this->response["Response Code"];
	}


	function get_response_reason_text() {
		return $this->response["Response Reason Text"];
	}


	function dump_response() {
		foreach ($this->response as $key => $value) {

			if ($value != "") {
				$response .= ( "" . $key . " => " . $value . "
" );
				continue;
			}
		}

		return $response;
	}


}


function internetsecure_activate() {
	defineGatewayField( "internetsecure", "text", "cadloginid", "", "CAD Currency Login ID", "20", "" );
	defineGatewayField( "internetsecure", "text", "usdloginid", "", "USD Currency Login ID", "20", "" );
	defineGatewayField( "internetsecure", "yesno", "testmode", "", "Test Mode", "", "" );
}


function internetsecure_link($params) {
	$code = "<form method=\"post\" action=\"" . $params["systemurl"] . "/creditcard.php\" name=\"paymentfrm\">
<input type=\"hidden\" name=\"invoiceid\" value=\"" . $params["invoiceid"] . "\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


function internetsecure_capture($params) {
	global $CONFIG;

	$loginid = $params["cadloginid"];

	if (( $params["cadloginid"] && $params["usdloginid"] )) {
		if ($params["currency"] == "USD") {
			$loginid = $params["usdloginid"];
		}
	}

	$auth = new internetsecure_class();
	$gateway_url = "https://secure.internetsecure.com/process.cgi";
	$auth->seturl( $gateway_url );
	$auth->add_field( "x_login", $loginid );
	$auth->add_field( "x_tran_key", $params["transkey"] );
	$auth->add_field( "x_version", "3.1" );
	$auth->add_field( "x_type", "AUTH_CAPTURE" );

	if ($params["testmode"] == "on") {
		$auth->add_field( "x_test_request", "TRUE" );
	}

	$auth->add_field( "x_relay_response", "FALSE" );
	$auth->add_field( "x_delim_data", "TRUE" );
	$auth->add_field( "x_delim_char", "|" );
	$auth->add_field( "x_encap_char", "" );
	$auth->add_field( "x_invoice_num", $params["invoiceid"] );
	$auth->add_field( "x_description", $CONFIG["CompanyName"] . " Invoice #" . $params["invoiceid"] );
	$auth->add_field( "x_first_name", $params["clientdetails"]["firstname"] );
	$auth->add_field( "x_last_name", $params["clientdetails"]["lastname"] );
	$auth->add_field( "x_address", $params["clientdetails"]["address1"] );
	$auth->add_field( "x_city", $params["clientdetails"]["city"] );
	$auth->add_field( "x_state", $params["clientdetails"]["state"] );
	$auth->add_field( "x_zip", $params["clientdetails"]["postcode"] );
	$auth->add_field( "x_country", $params["clientdetails"]["country"] );
	$auth->add_field( "x_phone", $params["clientdetails"]["phonenumber"] );
	$auth->add_field( "x_email", $params["clientdetails"]["email"] );
	$auth->add_field( "x_email_customer", "FALSE" );
	$auth->add_field( "x_method", "CC" );
	$auth->add_field( "x_card_num", $params["cardnum"] );
	$auth->add_field( "x_amount", $params["amount"] );
	$auth->add_field( "x_exp_date", $params["cardexp"] );
	$auth->add_field( "x_card_code", $params["cccvv"] );
	switch ($auth->process()) {
	case 1: {
			array( "status" => "success", "transid" => $auth->response["Transaction ID"], "rawdata" => $auth->dump_response() );
		}
	}

	return ;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["internetsecurename"] = "internetsecure";
$GATEWAYMODULE["internetsecurevisiblename"] = "InternetSecure";
$GATEWAYMODULE["internetsecuretype"] = "CC";
?>