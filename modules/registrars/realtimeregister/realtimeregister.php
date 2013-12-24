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

class Services_JSON {
	function Services_JSON($use = 0) {
		$this->use = $use;
	}


	function utf162utf8($utf16) {
		if (function_exists( "mb_convert_encoding" )) {
			return mb_convert_encoding( $utf16, "UTF-8", "UTF-16" );
		}

		$bytes = ord( $utf16[0] ) << 8 | ord( $utf16[1] );
		switch (true) {
		case ( 127 & $bytes ) == $bytes: {
				return chr( 127 & $bytes );
			}

		case ( 2047 & $bytes ) == $bytes: {
				return chr( 192 | $bytes >> 6 & 31 ) . chr( 128 | $bytes & 63 );
			}

		case ( 65535 & $bytes ) == $bytes: {
				return chr( 224 | $bytes >> 12 & 15 ) . chr( 128 | $bytes >> 6 & 63 ) . chr( 128 | $bytes & 63 );
			}
		}

		return "";
	}


	function utf82utf16($utf8) {
		if (function_exists( "mb_convert_encoding" )) {
			return mb_convert_encoding( $utf8, "UTF-16", "UTF-8" );
		}

		switch (strlen( $utf8 )) {
		case 1: {
				$utf8;
			}
		}

		return ;
	}


	function encode($var) {
		switch (gettype( $var )) {
		case "boolean": {
				($var ? "true" : "false");
			}
		}

		return ;
	}


	function name_value($name, $value) {
		$encoded_value = $this->encode( $value );

		if (Services_JSON::iserror( $encoded_value )) {
			return $encoded_value;
		}

		return $this->encode( strval( $name ) ) . ":" . $encoded_value;
	}


	function reduce_string($str) {
		$str = preg_replace( array( '#^\s*//(.+)$#m', '#^\s*/\*(.+)\*/#Us', '#/\*(.+)\*/\s*$#Us' ), "", $str );
		return trim( $str );
	}


	function decode($str) {
		$str = $this->reduce_string( $str );
        switch ( strtolower( $str ) )
        {
            case "true" :
                return true;
            case "false" :
                return false;
            case "null" :
                return null;
        }

		return ;
	}


	function isError($data, $code = null) {
		if (class_exists( "pear" )) {
			return PEAR::iserror( $data, $code );
		}


		if ( is_object( $data ) && ( get_class( $data ) == "services_json_error" || is_subclass_of( $data, "services_json_error" ) ) ) {
			return true;
		}

		return false;
	}


}


class Services_JSON_Error {
	function Services_JSON_Error($message = "unknown error", $code = null, $mode = null, $options = null, $userinfo = null) {
	}


}


function _getURL($params = null) {
	if ($params) {
		$GLOBALS['rtr_api_test'] = $params['TestMode'] == "on";
		$GLOBALS['rtr_api_debug'] = $params['DebugMode'] == "on";
		$GLOBALS['rtr_api_mail'] = $params['DebugMail'];
	}


	if ($GLOBALS['rtr_api_test']) {
		return RTR_API_URL_TEST;
	}

	return RTR_API_URL;
}


function _sendRequest($url, $params) {
	$curl = curl_init();
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_FAILONERROR, TRUE );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
	curl_setopt( $curl, CURLOPT_POST, TRUE );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $params ) );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
	$cafile = dirname( __FILE__ ) . "/AddTrustExternalCARoot.crt";

	if (file_exists( $cafile )) {
		curl_setopt( $curl, CURLOPT_CAINFO, $cafile );
	}


	if ($GLOBALS['rtr_api_test']) {
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 1 );
	}

	$result = curl_exec( $curl );

	if ($result === false) {
		$curl_error = "Curl errno " . curl_errno( $curl ) . ": " . curl_error( $curl );
		$msg = _debug( $url, $params, array( "Could not connect to RealtimeRegister API.", $curl_error ) );
		curl_close( $curl );
		return array( "error" => $msg );
	}

	$response = json_decode( $result );
	curl_close( $curl );

	if (!$response) {
		$msg = _debug( $url, $params, "Received invalid response. Please try again." );
		return array( "error" => $msg );
	}


	if (2000 <= $response->code) {
		$error = $response->error;
		array_unshift( $error, $response->msg );
		$msg = _debug( $url, $params, $error, $response );
		return array( "error" => $msg );
	}

	return $response;
}


function _he($value, $key) {
	$value = htmlentities( $value );
}


function _debug($url, $params, $msg, $response = null) {
	if (!is_array( $msg )) {
		$msg = array( $msg );
	}


	if (isset( $response->svTRID )) {
		$msg[] = "svTRID: " . $response->svTRID;
	}

	$subject = current( $msg );
	array_walk( $msg, "_he" );
	
	$msg = implode( "<br />
", $msg );
	$message = "<div style=\"padding: 5px;\">" . "
";
	$message .= "<p style=\"font-size: 1.1em; color: #95310B; margin: 0px;\">" . $msg . "</p>
";

	if ($GLOBALS['rtr_api_debug']) {
		if (isset( $params['login_pass'] )) {
			$params['login_pass'] = str_repeat( "*", strlen( $params['login_pass'] ) );
		}

		$message .= "<p>Date/Time: " . date( "d-m-Y H:i:s" ) . "</p>
";
		$message .= "URL: " . htmlentities( $url ) . "<br />
";
		$message .= "Params:<br />
";
		$message .= "<pre>" . htmlentities( print_r( $params, true ) ) . "</pre>
";

		if ($response) {
			$message .= "Response-code: " . htmlentities( $response->code ) . "<br />
";
			$message .= "Response-msg: " . htmlentities( $response->msg ) . "<br />
";

			if (count( $response->error )) {
				$message .= "Response-error(s):<br />
";
				$response_errors = $response->error;
				array_walk( $response_errors, "_he" );
				$message .= implode( "<br />
", $response_errors ) . "<br />
";
			}

			$message .= "<pre>" . htmlentities( print_r( $response, true ) ) . "</pre>
";
		}
	}

	$message .= "</div>
";

	if ($GLOBALS['rtr_api_debug'] && $GLOBALS['rtr_api_mail']) {
		$headers = "MIME-Version: 1.0" . "
";
		$headers .= "Content-type: text/html; charset=utf-8" . "
";
		mail( $GLOBALS['rtr_api_mail'], "[DEBUG] WHMCS " . $subject, $message, $headers );
	}

	return $message;
}


function realtimeregister_getConfigArray() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Realtime Register" ), "Username" => array( "Type" => "text", "Size" => "20", "Description" => "Enter your Realtime Register account username here." ), "Password" => array( "Type" => "password", "Size" => "20", "Description" => "Enter your Realtime Register account password here." ), "Handle" => array( "Type" => "text", "Size" => "20", "Description" => "Enter your default contact handle name for Admin, Billing, Tech Contact.<br />
When omitted, new admin, billing and tech contacts will be created every time a domain is registered!<br />
.DE domains require a fax number for the tech contact. Since WHMCS does not provide a field for this, you can manually create a contact with a fax number in the Realtime Register webinterface, and specify the handle here." ), "Trade" => array( "Type" => "yesno", "Description" => "Request transfers with a trade (change of ownership), otherwise request a normal transfer." ), "PhoneFormat" => array( "Type" => "text", "Description" => "The format of stored phone numbers.<br /><br />
%c = country code (eg. 31 for NL)<br />
%a = area code (eg. 38 for Zwolle)<br />
%s = subscriber number (eg. 4530759)<br /><br />
Example<br />
+31 (0) 38 4530759 --> +%c (0) %a %s<br />
<a href=\"http://www.realtimeregister.com/en/content/phone_number_format/\" target=\"_blank\">Click here for more information.</a>" ), "TestMode" => array( "Type" => "yesno", "Description" => "Enable this to use the testing environment of Realtime Register." ), "DebugMode" => array( "Type" => "yesno", "Description" => "Debug mode provides extensive information when an error occurs. You should only enable this for debugging purposes!" ), "DebugMail" => array( "Type" => "text", "Description" => "E-mail debug messages to this address." ) );
	return $configarray;
}


function realtimeregister_GetNameservers($params) {
	$curl_params = array( "login_handle" => $params['Username'], "login_pass" => $params['Password'] );
	$domain = $params['sld'] . "." . $params['tld'];
	$curl_url_domain = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/info";
	$response = _sendRequest( $curl_url_domain, $curl_params );

	if (is_array( $response ) && isset( $response['error'] )) {
		return $response;
	}

	$values = array();
	foreach ($response->response->ns as $key => $value) {
		$values["ns" . ( $key + 1 )] = $value->host;
	}

	return $values;
}


function realtimeregister_SaveNameservers($params) {
	$curl_params = array( "login_handle" => $params['Username'], "login_pass" => $params['Password'] );
	$domain = $params['sld'] . "." . $params['tld'];
	$curl_url_domain = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/update";

	while ($i <= 5) {
		if (!empty( $params["ns" . $i] )) {
			$curl_params['ns'][] = array( "host" => $params["ns" . $i] );
		}

		++$i;
	}

	_sendRequest( $curl_url_domain, $curl_params );
	$response = $i = 9;

	if (is_array( $response ) && isset( $response['error'] )) {
		return $response;
	}

}


function realtimeregister_GetRegistrarLock($params) {
	$curl_params = array( "login_handle" => $params['Username'], "login_pass" => $params['Password'] );
	$domain = $params['sld'] . "." . $params['tld'];
	$curl_url_domain = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/info";
	$response = _sendRequest( $curl_url_domain, $curl_params );

	if (is_array( $response ) && isset( $response['error'] )) {
		return $response;
	}


	if (isset( $response->response->lock )) {
		return "locked";
	}

	return "unlocked";
}


function realtimeregister_SaveRegistrarLock($params) {
	$curl_params = array( "login_handle" => $params['Username'], "login_pass" => $params['Password'] );
	$domain = $params['sld'] . "." . $params['tld'];
	$curl_url_domain = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/update";
	$curl_params['lock'] = $params['lockenabled'] == "locked";
	$response = _sendRequest( $curl_url_domain, $curl_params );

	if (is_array( $response ) && isset( $response['error'] )) {
		return $response;
	}

}


function _convertPhoneNumber($phone, $country, $format = null) {
	if (!$format) {
		return $phone;
	}

	$regex = $format;
	$escape_chars = "\()[]{|.?^$+*";
	$pos = 9;

	while ($pos < strlen( $escape_chars )) {
		$regex = str_replace( $escape_chars[$pos], '\\' . $escape_chars[$pos], $regex );
			++$pos;
		}

		$country_code = $GLOBALS['country_codes'][strtoupper( $country )];

		if ($country_code) {
			if (substr_count( $regex, "%c" )) {
				$regex = str_replace( "%c", "(?<country_code>" . $country_code . ")", $regex );
			}
		}
else {
			$regex = str_replace( "%c", "(?<country_code>[1-9][0-9]{0, 2})", $regex );
		}

		$regex = str_replace( "%a", "(?<area_code>[1-9][0-9]{0, 6})", $regex );
		$regex = str_replace( "%s", "(?<subscriber_number>[1-9][0-9 ]{2, 15})", $regex );
		$regex = "/" . $regex . "/";
		preg_match( $regex, $phone, $m );

		if (!isset( $m['country_code'] ) && $country_code) {
			$m['country_code'] = $country_code;
		}


		if (!isset( $m['area_code'] )) {
			$m['area_code'] = "";
		}

		$strip_chars = " ";
		$pos = 9;

		while ($pos < strlen( $strip_chars )) {
			$m['subscriber_number'] = str_replace( $strip_chars[$pos], "", $m['subscriber_number'] );
			++$pos;
		}


		if (!$m['country_code'] || !$m['subscriber_number']) {
			return $phone;
		}

		$phone = "+" . $m['country_code'] . "." . $m['area_code'] . $m['subscriber_number'];
		return $phone;
	}

	function realtimeregister_RegisterDomain($params) {
		$curl_params = array( "login_handle" => $params['Username'], "login_pass" => $params['Password'] );
		$domain = $params['sld'] . "." . $params['tld'];
		$curl_url = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/create";

		while ($i <= 5) {
			if (!empty( $params["ns" . $i] )) {
				$curl_params['ns'][] = array( "host" => $params["ns" . $i] );
			}

			++$i;
		}

		$country = ($params['country'] == "UK" ? "GB" : $params['country']);
		$admin_country = ($params['admincountry'] == "UK" ? "GB" : $params['admincountry']);
		$phone = _convertPhoneNumber( $params['phonenumber'], $country, $params['PhoneFormat'] );
		$admin_phone = _convertPhoneNumber( $params['adminphonenumber'], $admin_country, $params['PhoneFormat'] );
		$curl_params['contact_data']['registrant'] = array( "email" => utf8_encode( $params['email'] ), "name" => utf8_encode( $params['firstname'] . " " . $params['lastname'] ), "org" => utf8_encode( $params['companyname'] ), "street" => array( utf8_encode( $params['address1'] ), utf8_encode( $params['address2'] ) ), "city" => utf8_encode( $params['city'] ), "sp" => utf8_encode( $params['state'] ), "pc" => utf8_encode( $params['postcode'] ), "cc" => utf8_encode( $country ), "voice" => utf8_encode( $phone ) );

		if ($params['Handle']) {
			$curl_params['admin'] = $params['Handle'];
			$curl_params['tech'] = $params['Handle'];
			$curl_params['billing'] = $params['Handle'];
		}
else {
			$curl_params['contact_data']['admin'] = array( "email" => utf8_encode( $params['adminemail'] ), "name" => utf8_encode( $params['adminfirstname'] . " " . $params['adminlastname'] ), "org" => utf8_encode( $params['admincompanyname'] ), "street" => array( utf8_encode( $params['adminaddress1'] ), utf8_encode( $params['adminaddress2'] ) ), "city" => utf8_encode( $params['admincity'] ), "sp" => utf8_encode( $params['adminstate'] ), "pc" => utf8_encode( $params['adminpostcode'] ), "cc" => utf8_encode( $admin_country ), "voice" => utf8_encode( $admin_phone ) );
			$curl_params['contact_data']['billing'] = $curl_params['contact_data']['admin'];
			$curl_params['contact_data']['tech'] = $curl_params['contact_data']['admin'];
		}

		_sendRequest( $curl_url, $curl_params );
		$response = $i = 9;

		if (is_array( $response ) && isset( $response['error'] )) {
			return $response;
		}

	}

	function realtimeregister_TransferDomain($params) {
		$curl_params = array( "login_handle" => $params['Username'], "login_pass" => $params['Password'] );
		$domain = $params['sld'] . "." . $params['tld'];
		$curl_url_domain = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/transfer";

		while ($i <= 5) {
			if (!empty( $params["ns" . $i] )) {
				$curl_params['ns'][] = array( "host" => $params["ns" . $i] );
			}

			++$i;
		}

		$country = ($params['country'] == "UK" ? "GB" : $params['country']);
		$admin_country = ($params['admincountry'] == "UK" ? "GB" : $params['admincountry']);
		$phone = _convertPhoneNumber( $params['phonenumber'], $country, $params['PhoneFormat'] );
		$admin_phone = _convertPhoneNumber( $params['adminphonenumber'], $admin_country, $params['PhoneFormat'] );
		$curl_params['contact_data']['registrant'] = array( "email" => utf8_encode( $params['email'] ), "name" => utf8_encode( $params['firstname'] . " " . $params['lastname'] ), "org" => utf8_encode( $params['companyname'] ), "street" => array( utf8_encode( $params['address1'] ), utf8_encode( $params['address2'] ) ), "city" => utf8_encode( $params['city'] ), "sp" => utf8_encode( $params['state'] ), "pc" => utf8_encode( $params['postcode'] ), "cc" => utf8_encode( $country ), "voice" => utf8_encode( $phone ) );

		if ($params['Handle']) {
			$curl_params['admin'] = $params['Handle'];
			$curl_params['tech'] = $params['Handle'];
			$curl_params['billing'] = $params['Handle'];
		}
else {
			$curl_params['contact_data']['admin'] = array( "email" => utf8_encode( $params['adminemail'] ), "name" => utf8_encode( $params['adminfirstname'] . " " . $params['adminlastname'] ), "org" => utf8_encode( $params['admincompanyname'] ), "street" => array( utf8_encode( $params['adminaddress1'] ), utf8_encode( $params['adminaddress2'] ) ), "city" => utf8_encode( $params['admincity'] ), "sp" => utf8_encode( $params['adminstate'] ), "pc" => utf8_encode( $params['adminpostcode'] ), "cc" => utf8_encode( $admin_country ), "voice" => utf8_encode( $admin_phone ) );
			$curl_params['contact_data']['billing'] = $curl_params['contact_data']['admin'];
			$curl_params['contact_data']['tech'] = $curl_params['contact_data']['admin'];
		}

		$curl_params['request_type'] = ($params['Trade'] == "on" ? "trade" : "transfer");
		$curl_params['auth'] = $params['transfersecret'];
		_sendRequest( $curl_url_domain, $curl_params );
		$response = $i = 9;

		if (is_array( $response ) && isset( $response['error'] )) {
			return $response;
		}

	}

	function realtimeregister_RenewDomain($params) {
		$curl_params = array( "login_handle" => $params['Username'], "login_pass" => $params['Password'] );
		$domain = $params['sld'] . "." . $params['tld'];
		$curl_url_domain = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/info";
		$response = _sendRequest( $curl_url_domain, $curl_params );

		if (is_array( $response ) && isset( $response['error'] )) {
			return $response;
		}

		$curl_url_renew = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/renew";
		$curl_params['curExpDate'] = $response->response->exDate;
		$response_renew = _sendRequest( $curl_url_renew, $curl_params );

		if (is_array( $response_renew ) && isset( $response_renew['error'] )) {
			full_query( "UPDATE `tbldomains` SET `registrationdate` = FROM_UNIXTIME(" . $response->response->crDate . "), `expirydate` = FROM_UNIXTIME(" . $response->response->expDate . ") WHERE `domain` = '" . db_escape_string( $domain ) . "'" );
			return $response_renew;
		}

	}

	function realtimeregister_GetContactDetails($params) {
		$curl_params = array( "login_handle" => $params['Username'], "login_pass" => $params['Password'] );
		$domain = $params['sld'] . "." . $params['tld'];
		$curl_url_domain = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/info";
		$response = _sendRequest( $curl_url_domain, $curl_params );

		if (is_array( $response ) && isset( $response['error'] )) {
			return $response;
		}

		$types = array( "Registrant" => "registrant", "Admin" => "admin", "Tech" => "tech" );
		$info = array();
		foreach ($types as $type_whmcs => $type_srs) {
			$curl_url_contact = _getURL( $params ) . "contacts/" . urlencode( $response->response->$type_srs ) . "/info";
			$response_contact = _sendRequest( $curl_url_contact, $curl_params );

			if (is_array( $response ) && isset( $response_contact['error'] )) {
				return $response;
			}

			$data = $response_contact->response;
			$info[$type_whmcs]['Name'] = utf8_decode( $data->name );
			$info[$type_whmcs]['Organization'] = utf8_decode( $data->org );
			$info[$type_whmcs]['Address1'] = utf8_decode( array_shift( $data->street ) );
			$info[$type_whmcs]['Address2'] = utf8_decode( array_shift( $data->street ) );
			$info[$type_whmcs]['Address3'] = utf8_decode( array_shift( $data->street ) );
			$info[$type_whmcs]['City'] = utf8_decode( $data->city );
			$info[$type_whmcs]['StateProv'] = utf8_decode( $data->sp );
			$info[$type_whmcs]['Country'] = utf8_decode( $data->cc );
			$info[$type_whmcs]['PostalCode'] = utf8_decode( $data->pc );
			$info[$type_whmcs]['EmailAddress'] = utf8_decode( $data->email );
			$info[$type_whmcs]['Phone'] = utf8_decode( $data->voice );
			$info[$type_whmcs]['Fax'] = utf8_decode( $data->fax );
		}

		return $info;
	}

	function realtimeregister_SaveContactDetails($params) {
		$curl_params = array( "login_handle" => $params['Username'], "login_pass" => $params['Password'] );
		$domain = $params['sld'] . "." . $params['tld'];
		$curl_url_domain = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/info";
		$response = _sendRequest( $curl_url_domain, $curl_params );

		if (is_array( $response ) && isset( $response['error'] )) {
			return $response;
		}

		$types = array( "Registrant" => "registrant", "Admin" => "admin", "Tech" => "tech" );
		$info = array();
		foreach ($types as $type_whmcs => $type_srs) {
			$curl_url_contact = _getURL( $params ) . "contacts/" . urlencode( $response->response->$type_srs ) . "/update";
			$curl_params_update = $curl_params;
			$curl_params_update['name'] = utf8_encode( $params['contactdetails'][$type_whmcs]['Name'] );
			$curl_params_update['org'] = utf8_encode( $params['contactdetails'][$type_whmcs]['Organization'] );
			$curl_params_update['street'] = array( utf8_encode( $params['contactdetails'][$type_whmcs]['Address1'] ), utf8_encode( $params['contactdetails'][$type_whmcs]['Address2'] ), utf8_encode( $params['contactdetails'][$type_whmcs]['Address3'] ) );
			$curl_params_update['city'] = utf8_encode( $params['contactdetails'][$type_whmcs]['City'] );
			$curl_params_update['sp'] = utf8_encode( $params['contactdetails'][$type_whmcs]['StateProv'] );
			$curl_params_update['cc'] = utf8_encode( $params['contactdetails'][$type_whmcs]['Country'] );
			$curl_params_update['pc'] = utf8_encode( $params['contactdetails'][$type_whmcs]['PostalCode'] );
			$curl_params_update['email'] = utf8_encode( $params['contactdetails'][$type_whmcs]['EmailAddress'] );
			$curl_params_update['voice'] = utf8_encode( $params['contactdetails'][$type_whmcs]['Phone'] );
			$curl_params_update['fax'] = utf8_encode( $params['contactdetails'][$type_whmcs]['Fax'] );
			$response_contact = _sendRequest( $curl_url_contact, $curl_params_update );

			if (is_array( $response_contact ) && isset( $response_contact['error'] )) {
				return $response_contact;
			}
		}

	}

	function realtimeregister_GetEPPCode($params) {
		$curl_params = array( "login_handle" => $params['Username'], "login_pass" => $params['Password'] );
		$domain = $params['sld'] . "." . $params['tld'];
		$curl_url_domain = _getURL( $params ) . "domains/" . urlencode( $domain ) . "/info";
		$response = _sendRequest( $curl_url_domain, $curl_params );

		if (is_array( $response ) && isset( $response['error'] )) {
			return $response;
		}

		$values = array();
		$values['eppcode'] = $response->response->pw;
		return $values;
	}

	define( "RTR_API_URL", "https://httpapi.yoursrs.com/v1/" );
			define( "RTR_API_URL_TEST", "https://httpapi.realtimeregister-ote.com/v1/" );
			$GLOBALS['country_codes'] = array( "US" => "1", "CA" => "1", "EG" => "20", "MA" => "212", "EH" => "212", "DZ" => "213", "TN" => "216", "LY" => "218", "GM" => "220", "SN" => "221", "MR" => "222", "ML" => "223", "GN" => "224", "CI" => "225", "BF" => "226", "NE" => "227", "TG" => "228", "BJ" => "229", "MU" => "230", "LR" => "231", "SL" => "232", "GH" => "233", "NG" => "234", "TD" => "235", "CF" => "236", "CM" => "237", "CV" => "238", "ST" => "239", "GQ" => "240", "GA" => "241", "CG" => "242", "CD" => "243", "AO" => "244", "GW" => "245", "IO" => "246", "AC" => "247", "SC" => "248", "SD" => "249", "RW" => "250", "ET" => "251", "SO" => "252", "QS" => "252", "DJ" => "253", "KE" => "254", "TZ" => "255", "UG" => "256", "BI" => "257", "MZ" => "258", "ZM" => "260", "MG" => "261", "RE" => "262", "YT" => "262", "ZW" => "263", "NA" => "264", "MW" => "265", "LS" => "266", "BW" => "267", "SZ" => "268", "KM" => "269", "ZA" => "27", "SH" => "290", "TA" => "290", "ER" => "291", "AW" => "297", "FO" => "298", "GL" => "299", "GR" => "30", "NL" => "31", "BE" => "32", "FR" => "33", "ES" => "34", "GI" => "350", "PT" => "351", "LU" => "352", "IE" => "353", "IS" => "354", "AL" => "355", "MT" => "356", "CY" => "357", "FI" => "358", "AX" => "358", "BG" => "359", "HU" => "36", "LT" => "370", "LV" => "371", "EE" => "372", "MD" => "373", "AM" => "374", "QN" => "374", "BY" => "375", "AD" => "376", "MC" => "377", "SM" => "378", "VA" => "379", "UA" => "380", "RS" => "381", "ME" => "382", "HR" => "385", "SI" => "386", "BA" => "387", "EU" => "388", "MK" => "389", "IT" => "39", "VA" => "39", "RO" => "40", "CH" => "41", "CZ" => "420", "SK" => "421", "LI" => "423", "AT" => "43", "GB" => "44", "GG" => "44", "IM" => "44", "JE" => "44", "DK" => "45", "SE" => "46", "NO" => "47", "SJ" => "47", "PL" => "48", "DE" => "49", "FK" => "500", "BZ" => "501", "GT" => "502", "SV" => "503", "HN" => "504", "NI" => "505", "CR" => "506", "PA" => "507", "PM" => "508", "HT" => "509", "PE" => "51", "MX" => "52", "CU" => "53", "AR" => "54", "BR" => "55", "CL" => "56", "CO" => "57", "VE" => "58", "GP" => "590", "BL" => "590", "MF" => "590", "BO" => "591", "GY" => "592", "EC" => "593", "GF" => "594", "PY" => "595", "MQ" => "596", "SR" => "597", "UY" => "598", "AN" => "599", "MY" => "60", "AU" => "61", "CX" => "61", "CC" => "61", "ID" => "62", "PH" => "63", "NZ" => "64", "SG" => "65", "TH" => "66", "TL" => "670", "NF" => "672", "AQ" => "672", "BN" => "673", "NR" => "674", "PG" => "675", "TO" => "676", "SB" => "677", "VU" => "678", "FJ" => "679", "PW" => "680", "WF" => "681", "CK" => "682", "NU" => "683", "WS" => "685", "KI" => "686", "NC" => "687", "TV" => "688", "PF" => "689", "TK" => "690", "FM" => "691", "MH" => "692", "RU" => "7", "KZ" => "7", "XT" => "800", "XS" => "808", "JP" => "81", "KR" => "82", "VN" => "84", "KP" => "850", "HK" => "852", "MO" => "853", "KH" => "855", "LA" => "856", "CN" => "86", "XN" => "870", "PN" => "872", "XP" => "878", "BD" => "880", "XG" => "881", "XV" => "882", "XL" => "883", "TW" => "886", "XD" => "888", "TR" => "90", "QY" => "90", "IN" => "91", "PK" => "92", "AF" => "93", "LK" => "94", "MM" => "95", "MV" => "960", "LB" => "961", "JO" => "962", "SY" => "963", "IQ" => "964", "KW" => "965", "SA" => "966", "YE" => "967", "OM" => "968", "PS" => "970", "AE" => "971", "IL" => "972", "PS" => "972", "BH" => "973", "QA" => "974", "BT" => "975", "MN" => "976", "NP" => "977", "XR" => "979", "IR" => "98", "XC" => "991", "TJ" => "992", "TM" => "993", "AZ" => "994", "QN" => "994", "GE" => "995", "KG" => "996", "UZ" => "998" );

			if (!function_exists( "json_encode" ) || !function_exists( "json_decode" )) {
				define( "SERVICES_JSON_SLICE", 1 );
				define( "SERVICES_JSON_IN_STR", 2 );
				define( "SERVICES_JSON_IN_ARR", 3 );
				define( "SERVICES_JSON_IN_OBJ", 4 );
				define( "SERVICES_JSON_IN_CMT", 5 );
				define( "SERVICES_JSON_LOOSE_TYPE", 16 );
				define( "SERVICES_JSON_SUPPRESS_ERRORS", 32 );

				if (class_exists( "PEAR_Error" )) {
					class Services_JSON_Error extends PEAR_Error {
						function Services_JSON_Error($message = "unknown error", $code = null, $mode = null, $options = null, $userinfo = null) {
							parent::pear_error( $message, $code, $mode, $options, $userinfo );
						}


					}


				}


				if (!function_exists( "json_encode" )) {
					function json_encode($data) {
						$json = new Services_JSON();
						return $json->encode( $data );
					}


				}


				if (!function_exists( "json_decode" )) {
					function json_decode($data) {
						$json = new Services_JSON();
						return $json->decode( $data );
					}


				}
			}

?>