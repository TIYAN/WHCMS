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

class eprocessing_class {
	var $field_string = null;
	var $fields = array();
	var $gatewayurls = array();
	var $response_string = null;
	var $response = array();

	function add_field($field, $value) {
		$this->fields["" . $field] = urlencode( $value );
	}


	function process() {
		$gateway_url = "https://www.eProcessingNetwork.Com/cgi-bin/an/order.pl";
		foreach ($this->fields as $key => $value) {
			$this->field_string .= "" . $key . "=" . $value . "&";
		}

		$ch = curl_init( $gateway_url );
		curl_setopt( $ch, CURLOPT_URL, $gateway_url );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_NOPROGRESS, 1 );
		curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 120 );
		curl_setopt( $ch, CURLOPT_USERAGENT, $agent );
		curl_setopt( $ch, CURLOPT_REFERER, $ref );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, rtrim( $this->field_string, "& " ) );
		$this->response_string = curl_exec( $ch );

		if (curl_errno( $ch )) {
			$this->response["Response Reason Text"] = curl_error( $ch );
			return 3;
		}

		curl_close( $ch );
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


	function dump_fields() {
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


function eprocessingnetwork_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "eProcessingNetwork" ), "loginid" => array( "FriendlyName" => "Login ID", "Type" => "text", "Size" => "20", "Description" => "Enter your account id used to identify you as a merchant" ), "transkey" => array( "FriendlyName" => "Transaction Key", "Type" => "text", "Size" => "20", "Description" => "This can be found in the Processing Controls section of the eProcessingNetwork Merchant Support Center" ) );
	return $configarray;
}


function eprocessingnetwork_capture($params) {
	$auth = new eprocessing_class();
	$auth->add_field( "x_login", $params['loginid'] );
	$auth->add_field( "x_tran_key", $params['transkey'] );
	$auth->add_field( "x_version", "3.1" );
	$auth->add_field( "x_type", "AUTH_CAPTURE" );
	$auth->add_field( "x_relay_response", "FALSE" );
	$auth->add_field( "x_delim_data", "TRUE" );
	$auth->add_field( "x_delim_char", "|" );
	$auth->add_field( "x_encap_char", "" );
	$auth->add_field( "x_description", "Invoice #" . $params['invoiceid'] );
	$auth->add_field( "x_first_name", $params['clientdetails']['firstname'] );
	$auth->add_field( "x_last_name", $params['clientdetails']['lastname'] );
	$auth->add_field( "x_address", $params['clientdetails']['address1'] );
	$auth->add_field( "x_city", $params['clientdetails']['city'] );
	$auth->add_field( "x_state", $params['clientdetails']['state'] );
	$auth->add_field( "x_zip", $params['clientdetails']['postcode'] );
	$auth->add_field( "x_country", $params['clientdetails']['country'] );
	$auth->add_field( "x_phone", $params['clientdetails']['phone'] );
	$auth->add_field( "x_method", "CC" );
	$auth->add_field( "x_card_num", $params['cardnum'] );
	$auth->add_field( "x_amount", $params['amount'] );
	$auth->add_field( "x_exp_date", $params['cardexp'] );
	$auth->add_field( "x_card_code", $params['cccvv'] );
	$desc = "Action => Auth_Capture
Client => " . $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'] . "
";
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

?>