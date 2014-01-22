<?php

/* $Rev: 13 $ $Date: 2011-01-28 10:39:35 +0100 (Fri, 28 Jan 2011) $ */

define("RTR_API_URL", 		"https://httpapi.yoursrs.com/v1/");
define("RTR_API_URL_TEST",	"https://httpapi.realtimeregister-ote.com/v1/");

function _getURL($params = null) {
	if ($params) {
		/* Abuse this method to set global RTR_API_DEBUG & RTR_API_TEST vars */
		$GLOBALS['rtr_api_test']	= ($params['TestMode'] == "on");
		$GLOBALS['rtr_api_debug']	= ($params['DebugMode'] == "on");
		$GLOBALS['rtr_api_mail']	= $params['DebugMail'];
	}

	if ($GLOBALS['rtr_api_test']) {
		return RTR_API_URL_TEST;
	}

	return RTR_API_URL;
}

/* Function to send a request to the API, sets predefined options */
function _sendRequest($url, $params) {
	$curl = curl_init();
	
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_POST, TRUE);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

	$cafile = dirname(__FILE__) . "/AddTrustExternalCARoot.crt";
	if (file_exists($cafile)) {
		curl_setopt($curl, CURLOPT_CAINFO, $cafile);
	}
	
	if ($GLOBALS['rtr_api_test']) {
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
	}

	$result = curl_exec($curl);

	/* Could not connect to API, curl returned false */
	if ($result === false) {
		$curl_error = "Curl errno " . curl_errno($curl) . ": " . curl_error($curl);
		$msg = _debug($url, $params, array("Could not connect to RealtimeRegister API.", $curl_error));
		curl_close($curl);
		return array('error' => $msg);
	}
	
	/* Try to decode the response */
	$response = json_decode($result);
	
	curl_close($curl);

	/* Response could not be decoded */
	if (!$response) {
		$msg = _debug($url, $params, "Received invalid response. Please try again.");
		return array('error' => $msg);
	}

	/* An error occurred */
	if ($response->code >= 2000) {
		$error = $response->error;
		array_unshift($error, $response->msg);

		$msg = _debug($url, $params, $error, $response);
		return array('error' => $msg);
	}
	
	/* Uncomment next line to debug ALL requests (even the successful ones) */
//	_debug($url, $params, str_replace(_getURL(), "", $url), $response);

	return $response;
}

function _he(&$value, $key) {
	$value = htmlentities($value);
}

function _debug($url, $params, $msg, $response = null) {
	if (!is_array($msg)) {
		$msg = array($msg);
	}

	if (isset($response->svTRID)) {
		$msg[] = "svTRID: " . $response->svTRID;
	}

	$subject = current($msg);
	array_walk($msg, "_he");
	$msg = implode("<br />\n", $msg);
	
	$message  = '<div style="padding: 5px;">' . "\n";
	$message .= '<p style="font-size: 1.1em; color: #95310B; margin: 0px;">' . $msg . "</p>\n";
	
	if ($GLOBALS['rtr_api_debug']) {
		if (isset($params['login_pass'])) {
			$params['login_pass'] = str_repeat("*", strlen($params['login_pass']));
		}

		$message .= "<p>Date/Time: " . date("d-m-Y H:i:s") . "</p>\n";
		$message .= "URL: " . htmlentities($url) . "<br />\n";
		$message .= "Params:<br />\n";
		$message .= "<pre>" . htmlentities(print_r($params, true)) . "</pre>\n";
	
		if ($response) {
			$message .= "Response-code: " . htmlentities($response->code) . "<br />\n";
			$message .= "Response-msg: " . htmlentities($response->msg) . "<br />\n";
			if (count($response->error)) {
				$message .= "Response-error(s):<br />\n";
				$response_errors = $response->error;
				array_walk($response_errors, "_he");
				$message .= implode("<br />\n", $response_errors) . "<br />\n";
			}
			$message .= "<pre>" . htmlentities(print_r($response, true)) . "</pre>\n";
		}
	}

	$message .= "</div>\n";
	
	if ($GLOBALS['rtr_api_debug'] && $GLOBALS['rtr_api_mail']) {
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

		mail($GLOBALS['rtr_api_mail'], "[DEBUG] WHMCS " . $subject, $message, $headers );
	}

	return $message;
}

function realtimeregister_getConfigArray() {
	$configarray = array(
	 "Username"		=> array( "Type" => "text", "Size" => "20", "Description" => "Enter your Realtime Register account username here.", ),
	 "Password"		=> array( "Type" => "password", "Size" => "20", "Description" => "Enter your Realtime Register account password here.", ),
	 "Handle"		=> array( "Type" => "text", "Size" => "20", "Description" => "Enter your default contact handle name for Admin, Billing, Tech Contact.<br />\nWhen omitted, new admin, billing and tech contacts will be created every time a domain is registered!<br />\n.DE domains require a fax number for the tech contact. Since WHMCS does not provide a field for this, you can manually create a contact with a fax number in the Realtime Register webinterface, and specify the handle here.", ),
	 "Trade"		=> array( "Type" => "yesno", "Description" => "Request transfers with a trade (change of ownership), otherwise request a normal transfer.",),
	 "PhoneFormat"	=> array( "Type" => "text", "Description" => "The format of stored phone numbers.<br /><br />\n%c = country code (eg. 31 for NL)<br />\n%a = area code (eg. 38 for Zwolle)<br />\n%s = subscriber number (eg. 4530759)<br /><br />\nExample<br />\n+31 (0) 38 4530759 --> +%c (0) %a %s<br />\n<a href=\"http://www.realtimeregister.com/en/content/phone_number_format/\" target=\"_blank\">Click here for more information.</a>", ),
	 "TestMode"		=> array( "Type" => "yesno", "Description" => "Enable this to use the testing environment of Realtime Register.", ),
	 "DebugMode"	=> array( "Type" => "yesno", "Description" => "Debug mode provides extensive information when an error occurs. You should only enable this for debugging purposes!", ),
	 "DebugMail"	=> array( "Type" => "text", "Description" => "E-mail debug messages to this address.", ),
	);
	return $configarray;
}

function realtimeregister_GetNameservers($params) {
	$curl_params    = array('login_handle' => $params['Username'], 'login_pass' => $params['Password']);
	$domain         = $params['sld'] . "." . $params['tld'];

	$curl_url_domain = _getUrl($params) . "domains/" . urlencode($domain) . "/info";

	$response = _sendRequest($curl_url_domain, $curl_params);

	if (is_array($response) && isset($response['error'])) {
		return $response;
	}
	
	$values = array();

	foreach ($response->response->ns as $key => $value) {
		$values['ns'.($key+1)] = $value->host;
	}

	return $values;
}

function realtimeregister_SaveNameservers($params) {
	$curl_params    = array('login_handle' => $params['Username'], 'login_pass' => $params['Password']);
	$domain         = $params['sld'] . "." . $params['tld'];

	$curl_url_domain = _getUrl($params) . "domains/" . urlencode($domain) . "/update";
	
	for ($i = 1; $i <= 4; $i++) {
		if (!empty($params['ns'.$i])) {
			$curl_params['ns'][] = array("host" => $params['ns'.$i]);
		}
	};

	$response = _sendRequest($curl_url_domain, $curl_params);

	if (is_array($response) && isset($response['error'])) {
		return $response;
	}
}

function realtimeregister_GetRegistrarLock($params) {
	$curl_params    = array('login_handle' => $params['Username'], 'login_pass' => $params['Password']);
	$domain         = $params['sld'] . "." . $params['tld'];

	$curl_url_domain = _getUrl($params) . "domains/" . urlencode($domain) . "/info";

	$response = _sendRequest($curl_url_domain, $curl_params);

	if (is_array($response) && isset($response['error'])) {
		return $response;
	}

	if (isset($response->response->lock)) {
		return "locked";
	} else {
		return "unlocked";
	}
}

function realtimeregister_SaveRegistrarLock($params) {
	$curl_params    = array('login_handle' => $params['Username'], 'login_pass' => $params['Password']);
	$domain         = $params['sld'] . "." . $params['tld'];

	$curl_url_domain = _getUrl($params) . "domains/" . urlencode($domain) . "/update";

	$curl_params['lock'] = ($params['lockenabled'] == "locked");

	$response = _sendRequest($curl_url_domain, $curl_params);

	if (is_array($response) && isset($response['error'])) {
		return $response;
	}
}

function template_GetEmailForwarding($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	# Put your code to get email forwarding here - the result should be an array of prefixes and forward to emails (max 10)
	foreach ($result AS $value) {
		$values[$counter]["prefix"] = $value["prefix"];
		$values[$counter]["forwardto"] = $value["forwardto"];
	}
	return $values;
}

function template_SaveEmailForwarding($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	foreach ($params["prefix"] AS $key=>$value) {
		$forwardarray[$key]["prefix"] =  $params["prefix"][$key];
		$forwardarray[$key]["forwardto"] =  $params["forwardto"][$key];
	}
	# Put your code to save email forwarders here
}

function template_GetDNS($params) {
    $username = $params["Username"];
	$password = $params["Password"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
    # Put your code here to get the current DNS settings - the result should be an array of hostname, record type, and address
    $hostrecords = array();
    $hostrecords[] = array( "hostname" => "ns1", "type" => "A", "address" => "192.168.0.1", );
    $hostrecords[] = array( "hostname" => "ns2", "type" => "A", "address" => "192.168.0.2", );
	return $hostrecords;

}

function template_SaveDNS($params) {
    $username = $params["Username"];
	$password = $params["Password"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
    # Loop through the submitted records
	foreach ($params["dnsrecords"] AS $key=>$values) {
		$hostname = $values["hostname"];
		$type = $values["type"];
		$address = $values["address"];
		# Add your code to update the record here
	}
    # If error, return the error message in the value below
	$values["error"] = $Enom->Values["Err1"];
	return $values;
}

function _convertPhoneNumber($phone, $country, $format = null) {
	if (!$format) {
		return $phone;
	}

	$regex			= $format;
	$escape_chars	= '\()[]{|.?^$+*';

	for ($pos = 0; $pos < strlen($escape_chars); $pos++) {
		$regex = str_replace($escape_chars[$pos], "\\" . $escape_chars[$pos], $regex);
	}

	$country_code = $GLOBALS['country_codes'][strtoupper($country)];
	
	if ($country_code) {
		if (substr_count($regex, "%c")) {
			$regex = str_replace("%c", "(?P<country_code>" . $country_code . ")", $regex);
		}
	} else {
		$regex = str_replace("%c", "(?P<country_code>[1-9][0-9]{0,2})", $regex);
	}

	$regex = str_replace("%a", "(?P<area_code>[1-9][0-9]{0,6})", $regex);
	$regex = str_replace("%s", "(?P<subscriber_number>[1-9][0-9 ]{2,15})", $regex);
	$regex = "/" . $regex . "/";

	preg_match($regex, $phone, $m);

	if (!isset($m['country_code']) && $country_code) {
		$m['country_code'] = $country_code;
	}

	if (!isset($m['area_code'])) {
		$m['area_code'] = "";
	}

	$strip_chars = " ";

	for ($pos = 0; $pos < strlen($strip_chars); $pos++) {
		$m['subscriber_number'] = str_replace($strip_chars[$pos], "", $m['subscriber_number']);
	}
	
	if (!$m['country_code'] || !$m['subscriber_number']) {
		return $phone;
	}

	$phone = "+" . $m['country_code'] . "." . $m['area_code'] . $m['subscriber_number'];

	return $phone;
}

function realtimeregister_RegisterDomain($params) {
	$curl_params    = array('login_handle' => $params['Username'], 'login_pass' => $params['Password']);
	$domain         = $params['sld'] . "." . $params['tld'];
	$curl_url		= _getUrl($params) . "domains/" . urlencode($domain) . "/create";

	for ($i = 1; $i <= 4; $i++) {
		if (!empty($params['ns'.$i])) {
			$curl_params['ns'][] = array("host" => $params['ns'.$i]);
		}
	};
	
	$country		= ($params['country'] == "UK" ? "GB" : $params['country']);
	$admin_country	= ($params['admincountry'] == "UK" ? "GB" : $params['admincountry']);

	$phone			= _convertPhoneNumber($params['phonenumber'], $country, $params['PhoneFormat']);
	$admin_phone	= _convertPhoneNumber($params['adminphonenumber'], $admin_country, $params['PhoneFormat']);

	$curl_params['contact_data']['registrant']	= array('email'		=> utf8_encode($params['email']),
														'name'		=> utf8_encode($params['firstname'] . " " . $params['lastname']),
														'org'		=> utf8_encode($params['companyname']),
														'street'	=> array(	utf8_encode($params['address1']),
																				utf8_encode($params['address2'])),
														'city'		=> utf8_encode($params['city']),
														'sp'		=> utf8_encode($params['state']),
														'pc'		=> utf8_encode($params['postcode']),
														'cc'		=> utf8_encode($country),
														'voice'		=> utf8_encode($phone));
	if ($params['Handle']) {
		$curl_params['admin']	= $params['Handle'];
		$curl_params['tech']	= $params['Handle'];
		$curl_params['billing']	= $params['Handle'];
	} else {
		$curl_params['contact_data']['admin']	= array('email'		=> utf8_encode($params['adminemail']),
														'name'		=> utf8_encode($params['adminfirstname'] . " " . $params['adminlastname']),
														'org'		=> utf8_encode($params['admincompanyname']),
														'street'	=> array(	utf8_encode($params['adminaddress1']),
																				utf8_encode($params['adminaddress2'])),
														'city'		=> utf8_encode($params['admincity']),
														'sp'		=> utf8_encode($params['adminstate']),
														'pc'		=> utf8_encode($params['adminpostcode']),
														'cc'		=> utf8_encode($admin_country),
														'voice'		=> utf8_encode($admin_phone));
		$curl_params['contact_data']['billing']	= $curl_params['contact_data']['admin'];
		$curl_params['contact_data']['tech']	= $curl_params['contact_data']['admin'];
	}

	$response = _sendRequest($curl_url, $curl_params);

	if (is_array($response) && isset($response['error'])) {
		return $response;
	}

	/* RTR API does not support registration period */
//	$regperiod = $params["regperiod"];
}

function realtimeregister_TransferDomain($params) {
	$curl_params    = array('login_handle' => $params['Username'], 'login_pass' => $params['Password']);
	$domain         = $params['sld'] . "." . $params['tld'];

	$curl_url_domain = _getUrl($params) . "domains/" . urlencode($domain) . "/transfer";
	
	for ($i = 1; $i <= 4; $i++) {
		if (!empty($params['ns'.$i])) {
			$curl_params['ns'][] = array("host" => $params['ns'.$i]);
		}
	};

	$country        = ($params['country'] == "UK" ? "GB" : $params['country']);
	$admin_country  = ($params['admincountry'] == "UK" ? "GB" : $params['admincountry']);

	$phone          = _convertPhoneNumber($params['phonenumber'], $country, $params['PhoneFormat']);
	$admin_phone    = _convertPhoneNumber($params['adminphonenumber'], $admin_country, $params['PhoneFormat']);

	$curl_params['contact_data']['registrant']	= array('email'		=> utf8_encode($params['email']),
														'name'		=> utf8_encode($params['firstname'] . " " . $params['lastname']),
														'org'		=> utf8_encode($params['companyname']),
														'street'	=> array(	utf8_encode($params['address1']),
																				utf8_encode($params['address2'])),
														'city'		=> utf8_encode($params['city']),
														'sp'		=> utf8_encode($params['state']),
														'pc'		=> utf8_encode($params['postcode']),
														'cc'		=> utf8_encode($country),
														'voice'		=> utf8_encode($phone));
	if ($params['Handle']) {
		$curl_params['admin']   = $params['Handle'];
		$curl_params['tech']    = $params['Handle'];
		$curl_params['billing'] = $params['Handle'];
	} else {
		$curl_params['contact_data']['admin']	= array('email'		=> utf8_encode($params['adminemail']),
														'name'		=> utf8_encode($params['adminfirstname'] . " " . $params['adminlastname']),
														'org'		=> utf8_encode($params['admincompanyname']),
														'street'	=> array(	utf8_encode($params['adminaddress1']),
																				utf8_encode($params['adminaddress2'])),
														'city'		=> utf8_encode($params['admincity']),
														'sp'		=> utf8_encode($params['adminstate']),
														'pc'		=> utf8_encode($params['adminpostcode']),
														'cc'		=> utf8_encode($admin_country),
														'voice'		=> utf8_encode($admin_phone));
		$curl_params['contact_data']['billing']	= $curl_params['contact_data']['admin'];
		$curl_params['contact_data']['tech']	= $curl_params['contact_data']['admin'];
	}

	$curl_params['request_type'] = ($params['Trade'] == "on" ? "trade" : "transfer");
	$curl_params['auth'] = $params['transfersecret'];

	$response = _sendRequest($curl_url_domain, $curl_params);

	if (is_array($response) && isset($response['error'])) {
		return $response;
	}

	/* RTR API does not support registration period */
//	$regperiod = $params["regperiod"];
}

function realtimeregister_RenewDomain($params) {
	$curl_params    = array('login_handle' => $params['Username'], 'login_pass' => $params['Password']);
	$domain         = $params['sld'] . "." . $params['tld'];

	$curl_url_domain = _getUrl($params) . "domains/" . urlencode($domain) . "/info";

	$response = _sendRequest($curl_url_domain, $curl_params);

	if (is_array($response) && isset($response['error'])) {
		return $response;
	}

	$curl_url_renew = _getUrl($params) . "domains/" . urlencode($domain) . "/renew";
	
	$curl_params['curExpDate'] = $response->response->exDate;

	$response_renew = _sendRequest($curl_url_renew, $curl_params);

	if (is_array($response_renew) && isset($response_renew['error'])) {
		mysql_query("UPDATE `tbldomains` SET `registrationdate` = FROM_UNIXTIME(" . $response->response->crDate . "), `expirydate` = FROM_UNIXTIME(" . $response->response->expDate . ") WHERE `domain` = '" . $domain . "'");
		return $response_renew;
	}

	/* RTR API does not support registration period */
//	$regperiod = $params["regperiod"];
}

function realtimeregister_GetContactDetails($params) {
	$curl_params    = array('login_handle' => $params['Username'], 'login_pass' => $params['Password']);
	$domain         = $params['sld'] . "." . $params['tld'];

	$curl_url_domain = _getUrl($params) . "domains/" . urlencode($domain) . "/info";

	$response = _sendRequest($curl_url_domain, $curl_params);

	if (is_array($response) && isset($response['error'])) {
		return $response;
	}

	$types = array( 'Registrant'    => 'registrant',
					'Admin'         => 'admin',
					'Tech'          => 'tech');

	$info = array();

	foreach ($types as $type_whmcs => $type_srs) {
		$curl_url_contact = _getUrl($params) . "contacts/" . urlencode($response->response->$type_srs) . "/info";
		$response_contact = _sendRequest($curl_url_contact, $curl_params);

		if (is_array($response) && isset($response_contact['error'])) {
			return $response;
		}

		$data = $response_contact->response;

		$info[$type_whmcs]['Name']             = utf8_decode($data->name);
		$info[$type_whmcs]['Organization']     = utf8_decode($data->org);
		$info[$type_whmcs]['Address1']         = utf8_decode(array_shift($data->street));
		$info[$type_whmcs]['Address2']         = utf8_decode(array_shift($data->street));
		$info[$type_whmcs]['Address3']         = utf8_decode(array_shift($data->street));
		$info[$type_whmcs]['City']             = utf8_decode($data->city);
		$info[$type_whmcs]['StateProv']        = utf8_decode($data->sp);
		$info[$type_whmcs]['Country']          = utf8_decode($data->cc);
		$info[$type_whmcs]['PostalCode']       = utf8_decode($data->pc);
		$info[$type_whmcs]['EmailAddress']     = utf8_decode($data->email);
		$info[$type_whmcs]['Phone']            = utf8_decode($data->voice);
		$info[$type_whmcs]['Fax']              = utf8_decode($data->fax);
	}

	return $info;
}

function realtimeregister_SaveContactDetails($params) {
	$curl_params    = array('login_handle' => $params['Username'], 'login_pass' => $params['Password']);
	$domain         = $params['sld'] . "." . $params['tld'];

	$curl_url_domain = _getUrl($params) . "domains/" . urlencode($domain) . "/info";

	$response = _sendRequest($curl_url_domain, $curl_params);

	if (is_array($response) && isset($response['error'])) {
		return $response;
	}

	$types = array( 'Registrant'    => 'registrant',
					'Admin'         => 'admin',
					'Tech'          => 'tech');

	$info = array();

	foreach ($types as $type_whmcs => $type_srs) {
		$curl_url_contact = _getUrl($params) . "contacts/" . urlencode($response->response->$type_srs) . "/update";

		$curl_params_update = $curl_params;

		$curl_params_update['name']     = utf8_encode($params['contactdetails'][$type_whmcs]['Name']);
		$curl_params_update['org']      = utf8_encode($params['contactdetails'][$type_whmcs]['Organization']);
		$curl_params_update['street']	= array(	utf8_encode($params['contactdetails'][$type_whmcs]['Address1']),
										    		utf8_encode($params['contactdetails'][$type_whmcs]['Address2']),
										    		utf8_encode($params['contactdetails'][$type_whmcs]['Address3']));
		$curl_params_update['city']     = utf8_encode($params['contactdetails'][$type_whmcs]['City']);
		$curl_params_update['sp']       = utf8_encode($params['contactdetails'][$type_whmcs]['StateProv']);
		$curl_params_update['cc']       = utf8_encode($params['contactdetails'][$type_whmcs]['Country']);
		$curl_params_update['pc']       = utf8_encode($params['contactdetails'][$type_whmcs]['PostalCode']);
		$curl_params_update['email']    = utf8_encode($params['contactdetails'][$type_whmcs]['EmailAddress']);
		$curl_params_update['voice']    = utf8_encode($params['contactdetails'][$type_whmcs]['Phone']);
		$curl_params_update['fax']      = utf8_encode($params['contactdetails'][$type_whmcs]['Fax']);

		$response_contact = _sendRequest($curl_url_contact, $curl_params_update);

		if (is_array($response_contact) && isset($response_contact['error'])) {
			return $response_contact;
		}
	}
}

function realtimeregister_GetEPPCode($params) {
   	$curl_params    = array('login_handle' => $params['Username'], 'login_pass' => $params['Password']);
	$domain         = $params['sld'] . "." . $params['tld'];

	$curl_url_domain = _getUrl($params) . "domains/" . urlencode($domain) . "/info";

	$response = _sendRequest($curl_url_domain, $curl_params);

	if (is_array($response) && isset($response['error'])) {
		return $response;
	}

	$values = array();

    $values["eppcode"] = $response->response->pw;

    return $values;
}

function template_RegisterNameserver($params) {
    $username = $params["Username"];
	$password = $params["Password"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
    $nameserver = $params["nameserver"];
    $ipaddress = $params["ipaddress"];
    # Put your code to register the nameserver here
    # If error, return the error message in the value below
    $values["error"] = $error;
    return $values;
}

function template_ModifyNameserver($params) {
    $username = $params["Username"];
	$password = $params["Password"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
    $nameserver = $params["nameserver"];
    $currentipaddress = $params["currentipaddress"];
    $newipaddress = $params["newipaddress"];
    # Put your code to update the nameserver here
    # If error, return the error message in the value below
    $values["error"] = $error;
    return $values;
}

function template_DeleteNameserver($params) {
    $username = $params["Username"];
	$password = $params["Password"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
    $nameserver = $params["nameserver"];
    # Put your code to delete the nameserver here
    # If error, return the error message in the value below
    $values["error"] = $error;
    return $values;
}

$GLOBALS['country_codes'] = array( 'US' => '1', 'CA' => '1', 'EG' => '20', 'MA' => '212', 'EH' => '212', 'DZ' => '213', 'TN' => '216', 'LY' => '218', 'GM' => '220', 'SN' => '221', 'MR' => '222', 'ML' => '223', 'GN' => '224', 'CI' => '225', 'BF' => '226', 'NE' => '227', 'TG' => '228', 'BJ' => '229', 'MU' => '230', 'LR' => '231', 'SL' => '232', 'GH' => '233', 'NG' => '234', 'TD' => '235', 'CF' => '236', 'CM' => '237', 'CV' => '238', 'ST' => '239', 'GQ' => '240', 'GA' => '241', 'CG' => '242', 'CD' => '243', 'AO' => '244', 'GW' => '245', 'IO' => '246', 'AC' => '247', 'SC' => '248', 'SD' => '249', 'RW' => '250', 'ET' => '251', 'SO' => '252', 'QS' => '252', 'DJ' => '253', 'KE' => '254', 'TZ' => '255', 'UG' => '256', 'BI' => '257', 'MZ' => '258', 'ZM' => '260', 'MG' => '261', 'RE' => '262', 'YT' => '262', 'ZW' => '263', 'NA' => '264', 'MW' => '265', 'LS' => '266', 'BW' => '267', 'SZ' => '268', 'KM' => '269', 'ZA' => '27', 'SH' => '290', 'TA' => '290', 'ER' => '291', 'AW' => '297', 'FO' => '298', 'GL' => '299', 'GR' => '30', 'NL' => '31', 'BE' => '32', 'FR' => '33', 'ES' => '34', 'GI' => '350', 'PT' => '351', 'LU' => '352', 'IE' => '353', 'IS' => '354', 'AL' => '355', 'MT' => '356', 'CY' => '357', 'FI' => '358', 'AX' => '358', 'BG' => '359', 'HU' => '36', 'LT' => '370', 'LV' => '371', 'EE' => '372', 'MD' => '373', 'AM' => '374', 'QN' => '374', 'BY' => '375', 'AD' => '376', 'MC' => '377', 'SM' => '378', 'VA' => '379', 'UA' => '380', 'RS' => '381', 'ME' => '382', 'HR' => '385', 'SI' => '386', 'BA' => '387', 'EU' => '388', 'MK' => '389', 'IT' => '39', 'VA' => '39', 'RO' => '40', 'CH' => '41', 'CZ' => '420', 'SK' => '421', 'LI' => '423', 'AT' => '43', 'GB' => '44', 'GG' => '44', 'IM' => '44', 'JE' => '44', 'DK' => '45', 'SE' => '46', 'NO' => '47', 'SJ' => '47', 'PL' => '48', 'DE' => '49', 'FK' => '500', 'BZ' => '501', 'GT' => '502', 'SV' => '503', 'HN' => '504', 'NI' => '505', 'CR' => '506', 'PA' => '507', 'PM' => '508', 'HT' => '509', 'PE' => '51', 'MX' => '52', 'CU' => '53', 'AR' => '54', 'BR' => '55', 'CL' => '56', 'CO' => '57', 'VE' => '58', 'GP' => '590', 'BL' => '590', 'MF' => '590', 'BO' => '591', 'GY' => '592', 'EC' => '593', 'GF' => '594', 'PY' => '595', 'MQ' => '596', 'SR' => '597', 'UY' => '598', 'AN' => '599', 'MY' => '60', 'AU' => '61', 'CX' => '61', 'CC' => '61', 'ID' => '62', 'PH' => '63', 'NZ' => '64', 'SG' => '65', 'TH' => '66', 'TL' => '670', 'NF' => '672', 'AQ' => '672', 'BN' => '673', 'NR' => '674', 'PG' => '675', 'TO' => '676', 'SB' => '677', 'VU' => '678', 'FJ' => '679', 'PW' => '680', 'WF' => '681', 'CK' => '682', 'NU' => '683', 'WS' => '685', 'KI' => '686', 'NC' => '687', 'TV' => '688', 'PF' => '689', 'TK' => '690', 'FM' => '691', 'MH' => '692', 'RU' => '7', 'KZ' => '7', 'XT' => '800', 'XS' => '808', 'JP' => '81', 'KR' => '82', 'VN' => '84', 'KP' => '850', 'HK' => '852', 'MO' => '853', 'KH' => '855', 'LA' => '856', 'CN' => '86', 'XN' => '870', 'PN' => '872', 'XP' => '878', 'BD' => '880', 'XG' => '881', 'XV' => '882', 'XL' => '883', 'TW' => '886', 'XD' => '888', 'TR' => '90', 'QY' => '90', 'IN' => '91', 'PK' => '92', 'AF' => '93', 'LK' => '94', 'MM' => '95', 'MV' => '960', 'LB' => '961', 'JO' => '962', 'SY' => '963', 'IQ' => '964', 'KW' => '965', 'SA' => '966', 'YE' => '967', 'OM' => '968', 'PS' => '970', 'AE' => '971', 'IL' => '972', 'PS' => '972', 'BH' => '973', 'QA' => '974', 'BT' => '975', 'MN' => '976', 'NP' => '977', 'XR' => '979', 'IR' => '98', 'XC' => '991', 'TJ' => '992', 'TM' => '993', 'AZ' => '994', 'QN' => '994', 'GE' => '995', 'KG' => '996', 'UZ' => '998' );

if (!function_exists("json_encode") || !function_exists("json_decode")) {
	/**
	 * LICENSE: Redistribution and use in source and binary forms, with or
	 * without modification, are permitted provided that the following
	 * conditions are met: Redistributions of source code must retain the
	 * above copyright notice, this list of conditions and the following
	 * disclaimer. Redistributions in binary form must reproduce the above
	 * copyright notice, this list of conditions and the following disclaimer
	 * in the documentation and/or other materials provided with the
	 * distribution.
	 *
	 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED
	 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
	 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN
	 * NO EVENT SHALL CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
	 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
	 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
	 * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
	 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
	 * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
	 * DAMAGE.
	 */
	define('SERVICES_JSON_SLICE', 1); define('SERVICES_JSON_IN_STR', 2); define('SERVICES_JSON_IN_ARR', 3); define('SERVICES_JSON_IN_OBJ', 4); define('SERVICES_JSON_IN_CMT', 5); define('SERVICES_JSON_LOOSE_TYPE', 16); define('SERVICES_JSON_SUPPRESS_ERRORS', 32); class Services_JSON { function Services_JSON($use = 0) { $this->use = $use; } function utf162utf8($utf16) { if(function_exists('mb_convert_encoding')) { return mb_convert_encoding($utf16, 'UTF-8', 'UTF-16'); } $bytes = (ord($utf16{0}) << 8) | ord($utf16{1}); switch(true) { case ((0x7F & $bytes) == $bytes): return chr(0x7F & $bytes); case (0x07FF & $bytes) == $bytes: return chr(0xC0 | (($bytes >> 6) & 0x1F)) . chr(0x80 | ($bytes & 0x3F)); case (0xFFFF & $bytes) == $bytes: return chr(0xE0 | (($bytes >> 12) & 0x0F)) . chr(0x80 | (($bytes >> 6) & 0x3F)) . chr(0x80 | ($bytes & 0x3F)); } return ''; } function utf82utf16($utf8) { if(function_exists('mb_convert_encoding')) { return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8'); } switch(strlen($utf8)) { case 1: return $utf8; case 2: return chr(0x07 & (ord($utf8{0}) >> 2)) . chr((0xC0 & (ord($utf8{0}) << 6)) | (0x3F & ord($utf8{1}))); case 3: return chr((0xF0 & (ord($utf8{0}) << 4)) | (0x0F & (ord($utf8{1}) >> 2))) . chr((0xC0 & (ord($utf8{1}) << 6)) | (0x7F & ord($utf8{2}))); } return ''; } function encode($var) { switch (gettype($var)) { case 'boolean': return $var ? 'true' : 'false'; case 'NULL': return 'null'; case 'integer': return (int) $var; case 'double': case 'float': return (float) $var; case 'string': $ascii = ''; $strlen_var = strlen($var); for ($c = 0; $c < $strlen_var; ++$c) { $ord_var_c = ord($var{$c}); switch (true) { case $ord_var_c == 0x08: $ascii .= '\b'; break; case $ord_var_c == 0x09: $ascii .= '\t'; break; case $ord_var_c == 0x0A: $ascii .= '\n'; break; case $ord_var_c == 0x0C: $ascii .= '\f'; break; case $ord_var_c == 0x0D: $ascii .= '\r'; break; case $ord_var_c == 0x22: case $ord_var_c == 0x2F: case $ord_var_c == 0x5C: $ascii .= '\\'.$var{$c}; break; case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)): $ascii .= $var{$c}; break; case (($ord_var_c & 0xE0) == 0xC0): $char = pack('C*', $ord_var_c, ord($var{$c + 1})); $c += 1; $utf16 = $this->utf82utf16($char); $ascii .= sprintf('\u%04s', bin2hex($utf16)); break; case (($ord_var_c & 0xF0) == 0xE0): $char = pack('C*', $ord_var_c, ord($var{$c + 1}), ord($var{$c + 2})); $c += 2; $utf16 = $this->utf82utf16($char); $ascii .= sprintf('\u%04s', bin2hex($utf16)); break; case (($ord_var_c & 0xF8) == 0xF0): $char = pack('C*', $ord_var_c, ord($var{$c + 1}), ord($var{$c + 2}), ord($var{$c + 3})); $c += 3; $utf16 = $this->utf82utf16($char); $ascii .= sprintf('\u%04s', bin2hex($utf16)); break; case (($ord_var_c & 0xFC) == 0xF8): $char = pack('C*', $ord_var_c, ord($var{$c + 1}), ord($var{$c + 2}), ord($var{$c + 3}), ord($var{$c + 4})); $c += 4; $utf16 = $this->utf82utf16($char); $ascii .= sprintf('\u%04s', bin2hex($utf16)); break; case (($ord_var_c & 0xFE) == 0xFC): $char = pack('C*', $ord_var_c, ord($var{$c + 1}), ord($var{$c + 2}), ord($var{$c + 3}), ord($var{$c + 4}), ord($var{$c + 5})); $c += 5; $utf16 = $this->utf82utf16($char); $ascii .= sprintf('\u%04s', bin2hex($utf16)); break; } } return '"'.$ascii.'"'; case 'array': if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) { $properties = array_map(array($this, 'name_value'), array_keys($var), array_values($var)); foreach($properties as $property) { if(Services_JSON::isError($property)) { return $property; } } return '{' . join(',', $properties) . '}'; } $elements = array_map(array($this, 'encode'), $var); foreach($elements as $element) { if(Services_JSON::isError($element)) { return $element; } } return '[' . join(',', $elements) . ']'; case 'object': $vars = get_object_vars($var); $properties = array_map(array($this, 'name_value'), array_keys($vars), array_values($vars)); foreach($properties as $property) { if(Services_JSON::isError($property)) { return $property; } } return '{' . join(',', $properties) . '}'; default: return ($this->use & SERVICES_JSON_SUPPRESS_ERRORS) ? 'null' : new Services_JSON_Error(gettype($var)." can not be encoded as JSON string"); } } function name_value($name, $value) { $encoded_value = $this->encode($value); if(Services_JSON::isError($encoded_value)) { return $encoded_value; } return $this->encode(strval($name)) . ':' . $encoded_value; } function reduce_string($str) { $str = preg_replace(array( '#^\s*//(.+)$#m', '#^\s*/\*(.+)\*/#Us', '#/\*(.+)\*/\s*$#Us' ), '', $str); return trim($str); } function decode($str) { $str = $this->reduce_string($str); switch (strtolower($str)) { case 'true': return true; case 'false': return false; case 'null': return null; default: $m = array(); if (is_numeric($str)) { return ((float)$str == (integer)$str) ? (integer)$str : (float)$str; } elseif (preg_match('/^("|\').*(\1)$/s', $str, $m) && $m[1] == $m[2]) { $delim = substr($str, 0, 1); $chrs = substr($str, 1, -1); $utf8 = ''; $strlen_chrs = strlen($chrs); for ($c = 0; $c < $strlen_chrs; ++$c) { $substr_chrs_c_2 = substr($chrs, $c, 2); $ord_chrs_c = ord($chrs{$c}); switch (true) { case $substr_chrs_c_2 == '\b': $utf8 .= chr(0x08); ++$c; break; case $substr_chrs_c_2 == '\t': $utf8 .= chr(0x09); ++$c; break; case $substr_chrs_c_2 == '\n': $utf8 .= chr(0x0A); ++$c; break; case $substr_chrs_c_2 == '\f': $utf8 .= chr(0x0C); ++$c; break; case $substr_chrs_c_2 == '\r': $utf8 .= chr(0x0D); ++$c; break; case $substr_chrs_c_2 == '\\"': case $substr_chrs_c_2 == '\\\'': case $substr_chrs_c_2 == '\\\\': case $substr_chrs_c_2 == '\\/': if (($delim == '"' && $substr_chrs_c_2 != '\\\'') || ($delim == "'" && $substr_chrs_c_2 != '\\"')) { $utf8 .= $chrs{++$c}; } break; case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6)): $utf16 = chr(hexdec(substr($chrs, ($c + 2), 2))) . chr(hexdec(substr($chrs, ($c + 4), 2))); $utf8 .= $this->utf162utf8($utf16); $c += 5; break; case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F): $utf8 .= $chrs{$c}; break; case ($ord_chrs_c & 0xE0) == 0xC0: $utf8 .= substr($chrs, $c, 2); ++$c; break; case ($ord_chrs_c & 0xF0) == 0xE0: $utf8 .= substr($chrs, $c, 3); $c += 2; break; case ($ord_chrs_c & 0xF8) == 0xF0: $utf8 .= substr($chrs, $c, 4); $c += 3; break; case ($ord_chrs_c & 0xFC) == 0xF8: $utf8 .= substr($chrs, $c, 5); $c += 4; break; case ($ord_chrs_c & 0xFE) == 0xFC: $utf8 .= substr($chrs, $c, 6); $c += 5; break; } } return $utf8; } elseif (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str)) { if ($str{0} == '[') { $stk = array(SERVICES_JSON_IN_ARR); $arr = array(); } else { if ($this->use & SERVICES_JSON_LOOSE_TYPE) { $stk = array(SERVICES_JSON_IN_OBJ); $obj = array(); } else { $stk = array(SERVICES_JSON_IN_OBJ); $obj = new stdClass(); } } array_push($stk, array('what' => SERVICES_JSON_SLICE, 'where' => 0, 'delim' => false)); $chrs = substr($str, 1, -1); $chrs = $this->reduce_string($chrs); if ($chrs == '') { if (reset($stk) == SERVICES_JSON_IN_ARR) { return $arr; } else { return $obj; } } $strlen_chrs = strlen($chrs); for ($c = 0; $c <= $strlen_chrs; ++$c) { $top = end($stk); $substr_chrs_c_2 = substr($chrs, $c, 2); if (($c == $strlen_chrs) || (($chrs{$c} == ',') && ($top['what'] == SERVICES_JSON_SLICE))) { $slice = substr($chrs, $top['where'], ($c - $top['where'])); array_push($stk, array('what' => SERVICES_JSON_SLICE, 'where' => ($c + 1), 'delim' => false)); if (reset($stk) == SERVICES_JSON_IN_ARR) { array_push($arr, $this->decode($slice)); } elseif (reset($stk) == SERVICES_JSON_IN_OBJ) { $parts = array(); if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) { $key = $this->decode($parts[1]); $val = $this->decode($parts[2]); if ($this->use & SERVICES_JSON_LOOSE_TYPE) { $obj[$key] = $val; } else { $obj->$key = $val; } } elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) { $key = $parts[1]; $val = $this->decode($parts[2]); if ($this->use & SERVICES_JSON_LOOSE_TYPE) { $obj[$key] = $val; } else { $obj->$key = $val; } } } } elseif ((($chrs{$c} == '"') || ($chrs{$c} == "'")) && ($top['what'] != SERVICES_JSON_IN_STR)) { array_push($stk, array('what' => SERVICES_JSON_IN_STR, 'where' => $c, 'delim' => $chrs{$c})); } elseif (($chrs{$c} == $top['delim']) && ($top['what'] == SERVICES_JSON_IN_STR) && ((strlen(substr($chrs, 0, $c)) - strlen(rtrim(substr($chrs, 0, $c), '\\'))) % 2 != 1)) { array_pop($stk); } elseif (($chrs{$c} == '[') && in_array($top['what'], array(SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ))) { array_push($stk, array('what' => SERVICES_JSON_IN_ARR, 'where' => $c, 'delim' => false)); } elseif (($chrs{$c} == ']') && ($top['what'] == SERVICES_JSON_IN_ARR)) { array_pop($stk); } elseif (($chrs{$c} == '{') && in_array($top['what'], array(SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ))) { array_push($stk, array('what' => SERVICES_JSON_IN_OBJ, 'where' => $c, 'delim' => false)); } elseif (($chrs{$c} == '}') && ($top['what'] == SERVICES_JSON_IN_OBJ)) { array_pop($stk); } elseif (($substr_chrs_c_2 == '/*') && in_array($top['what'], array(SERVICES_JSON_SLICE, SERVICES_JSON_IN_ARR, SERVICES_JSON_IN_OBJ))) { array_push($stk, array('what' => SERVICES_JSON_IN_CMT, 'where' => $c, 'delim' => false)); $c++; } elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == SERVICES_JSON_IN_CMT)) { array_pop($stk); $c++; for ($i = $top['where']; $i <= $c; ++$i) $chrs = substr_replace($chrs, ' ', $i, 1); } } if (reset($stk) == SERVICES_JSON_IN_ARR) { return $arr; } elseif (reset($stk) == SERVICES_JSON_IN_OBJ) { return $obj; } } } } function isError($data, $code = null) { if (class_exists('pear')) { return PEAR::isError($data, $code); } elseif (is_object($data) && (get_class($data) == 'services_json_error' || is_subclass_of($data, 'services_json_error'))) { return true; } return false; } } if (class_exists('PEAR_Error')) { class Services_JSON_Error extends PEAR_Error { function Services_JSON_Error($message = 'unknown error', $code = null, $mode = null, $options = null, $userinfo = null) { parent::PEAR_Error($message, $code, $mode, $options, $userinfo); } } } else { class Services_JSON_Error { function Services_JSON_Error($message = 'unknown error', $code = null, $mode = null, $options = null, $userinfo = null) { } } }
	
	if (!function_exists("json_encode")) {
		function json_encode($data) {
			$json = new Services_JSON();
			return $json->encode($data);
		}
	}

	if (!function_exists("json_decode")) {
		function json_decode($data) {
			$json = new Services_JSON();
			return $json->decode($data);
		}
	}
}

?>