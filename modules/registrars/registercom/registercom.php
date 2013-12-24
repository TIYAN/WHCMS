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

function registercom_getConfigArray() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Register.com" ), "applicationGuid" => array( "Type" => "text", "Size" => "20", "Description" => "This is the unique key assigned by RxPortalExpress" ), "TestMode" => array( "Type" => "yesno" ) );
	return $configarray;
}


function registercom_GetNameservers($params) {
	$tld = $params['tld'];
	$sld = $params['sld'];
	$domain = $sld . "." . $tld;
	$xml = "<serviceRequest>
<command>domainGet</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<page>1</page>
<domains>
<domainName>" . $domain . "</domainName>
</domains>
</request>
</serviceRequest>";
	$data = registercom_curlCall( $xml, $params );
	$data = $data['serviceResponse']['response']['domainGet']['domain']['nameServers']['nameServer'];
	$values['ns1'] = $data[0]['nsName']['value'];
	$values['ns2'] = $data[1]['nsName']['value'];

	if (empty( $values['ns1'] ) && empty( $values['ns2'] )) {
		$values['error'] = "Could not retrieve nameservers for the domain " . $domain;
	}

	return $values;
}


function registercom_SaveNameservers($params) {
	$tld = $params['tld'];
	$sld = $params['sld'];
	$domain = $sld . "." . $tld;
	$nameserver1 = $params['ns1'];
	$nameserver2 = $params['ns2'];
	$product_id = registercom_GetProductIdByDomain( $domain, $params );
	$xml_modify = "<serviceRequest>
<command>domainModify</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<productId>" . $product_id . "</productId>
<nameservers>
<nameserver>
<nsType>Primary</nsType>
<nsName>" . $nameserver1 . "</nsName>
</nameserver>
<nameserver>
<nsType>Secondary</nsType>
<nsName>" . $nameserver2 . "</nsName>
</nameserver>
</nameservers>
</request>
</serviceRequest>";
	registercom_curlCall( $xml, $params );
	$data = $params['tld'];
	$data = $data['serviceResponse']['productId']['value'];

	if ($product_id != $data) {
		$values['error'] = "The requested nameserver changes were NOT accepted by the registrar for the domain " . $domain;
	}

	return $values;
}


function registercom_GetRegistrarLock($params) {
	$tld = $params['tld'];
	$sld = $params['sld'];
	$domain = $sld . "." . $tld;
	$xml = "<serviceRequest>
<command>domainGet</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<page>1</page>
<domains>
<domainName>" . $domain . "</domainName>
</domains>
</request>
</serviceRequest>";
	$data = registercom_curlCall( $xml, $params );
	$lock = $data['serviceResponse']['response']['domainGet']['domain']['domainInfo']['registrarLock']['value'];

	if ($lock == "On") {
		$lockstatus = "locked";
	}
	else {
		$lockstatus = "unlocked";
	}

	return $lockstatus;
}


function registercom_SaveRegistrarLock($params) {
	$tld = $params['tld'];
	$sld = $params['sld'];
	$domain = $sld . "." . $tld;
	$product_id = registercom_GetProductIdByDomain( $domain, $params );

	if ($params['lockenabled'] == "locked") {
		$lockstatus = "True";
	}
	else {
		$lockstatus = "False";
	}

	$xml_lock = "<serviceRequest>
<command>domainLock</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<productId>" . $product_id . "</productId>
<registrarLock>" . $lockstatus . "</registrarLock>
</request>
</serviceRequest>";
	registercom_curlCall( $xml_lock, $params );
	$data = $params['tld'];
	$data = $data['serviceResponse']['status']['statusCode']['value'];

	if ($data != "1000") {
		$values['error'] = "Could not update Registrar Lock Status for the domain " . $domain;
	}

	return $values;
}


function registercom_RegisterDomain($params) {
	require ROOTDIR . "/includes/countriescallingcodes.php";
	$tld = $params['tld'];
	$sld = $params['sld'];
	$domain = $sld . "." . $tld;
	$regperiod = $params['regperiod'];
	$nameserver1 = $params['ns1'];
	$nameserver2 = $params['ns2'];
	$RegistrantFirstName = $params['firstname'];
	$RegistrantLastName = $params['lastname'];
	$RegistrantAddress1 = $params['address1'];
	$RegistrantAddress2 = $params['address2'];
	$RegistrantCity = $params['city'];
	$RegistrantPostalCode = $params['postcode'];
	$RegistrantCountry = $params['country'];

	if ($RegistrantCountry == "US") {
		$RegistrantStateProvince = registercom_convert_us_state( $params['state'] );

		if ($RegistrantStateProvince == "false") {
			$RegistrantStateProvince = "";
		}
	}
	else {
		$RegistrantStateProvince = "";
	}

	$RegistrantEmailAddress = $params['email'];
	$RegistrantPhone = "+" . $countrycallingcodes[$params['country']] . "." . preg_replace( "/[^0-9]/", "", $params['phonenumber'] );
	$AdminPhone = "+" . $countrycallingcodes[$params['admincountry']] . "." . preg_replace( "/[^0-9]/", "", $params['adminphonenumber'] );
	$AdminFirstName = $params['adminfirstname'];
	$AdminLastName = $params['adminlastname'];
	$AdminAddress1 = $params['adminaddress1'];
	$AdminAddress2 = $params['adminaddress2'];
	$AdminCity = $params['admincity'];
	$AdminStateProvince = $params['adminstate'];
	$AdminPostalCode = $params['adminpostcode'];
	$AdminCountry = $params['admincountry'];

	if ($AdminCountry == "US") {
		$AdminStateProvince = registercom_convert_us_state( $params['adminstate'] );

		if ($AdminStateProvince == "false") {
			$AdminStateProvince = "";
		}
	}
	else {
		$AdminStateProvince = "";
	}

	$AdminEmailAddress = $params['adminemail'];
	$xml_adduser = "<serviceRequest>
<command>userAdd</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<userId>" . $RegistrantEmailAddress . "</userId>
<userAccountName>" . $RegistrantFirstName . " " . $RegistrantLastName . "</userAccountName>
<contacts>
<contact>
<firstName>" . $RegistrantFirstName . "</firstName>
<lastName>" . $RegistrantLastName . "</lastName>
<emailAddress>" . $RegistrantEmailAddress . "</emailAddress>
<telephoneNumber>" . $RegistrantPhone . "</telephoneNumber>
<addressLine1>" . $RegistrantAddress1 . "</addressLine1>
<addressLine2>" . $RegistrantAddress2 . "</addressLine2>
<city>" . $RegistrantCity . "</city>";

	if ($RegistrantCountry == "US") {
		$xml_adduser .= "<province>" . $params['state'] . "</province>";
	}
	else {
		$xml_adduser .= "<state>" . $RegistrantStateProvince . "</state>";
	}

	$xml_adduser .= "<postalCode>" . $RegistrantPostalCode . "</postalCode>
<countryCode>" . $RegistrantCountry . "</countryCode>
<contactType>Registration</contactType>
</contact>";

	if ($AdminEmailAddress != $RegistrantEmailAddress) {
		$xml_adduser .= "<contact>
<firstName>" . $AdminFirstName . "</firstName>
<lastName>" . $AdminLastName . "</lastName>
<emailAddress>" . $AdminEmailAddress . "</emailAddress>
<telephoneNumber>" . $AdminPhone . "</telephoneNumber>
<addressLine1>" . $AdminAddress1 . "</addressLine1>
<addressLine2>" . $AdminAddress2 . "</addressLine2>
<city>" . $AdminCity . "</city>";

		if ($AdminCountry == "US") {
			$xml_adduser .= "<province>" . $params['adminstate'] . "</province>";
		}
		else {
			$xml_adduser .= "<state>" . $AdminStateProvince . "</state>";
		}

		$xml_adduser .= "<postalCode>" . $AdminPostalCode . "</postalCode>
<countryCode>" . $AdminCountry . "</countryCode>
<contactType>Administration</contactType>
</contact>";
	}

	$xml_adduser .= "
</contacts>
</request>
</serviceRequest>";
	$data = registercom_curlCall( $xml_adduser, $params );

	if ($data['serviceResponse']['status']['statusCode']['value'] != "1000" && $data['serviceResponse']['status']['statusCode']['value'] != "1005") {
		$values['error'] = "Could not add or update user account " . $AdminFirstName . " " . $AdminLastName . " with Registrar for the domainAdd request of " . $domain;
		return $values;
	}

	$xml_adddomain = "<serviceRequest>
<command>domainAdd</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<userId>" . $AdminEmailAddress . "</userId>
<domainName>" . $domain . "</domainName>
<term>" . $regperiod . "</term>
<contacts>";
	$xml_adddomain .= "<contact>
<title>Mr.</title>
<firstName>" . $RegistrantFirstName . "</firstName>
<lastName>" . $RegistrantLastName . "</lastName>
<emailAddress>" . $RegistrantEmailAddress . "</emailAddress>
<telephoneNumber>" . $RegistrantPhone . "</telephoneNumber>
<addressLine1>" . $RegistrantAddress1 . "</addressLine1>
<addressLine2>" . $RegistrantAddress2 . "</addressLine2>
<city>" . $RegistrantCity . "</city>";

	if ($RegistrantCountry == "US") {
		$xml_adddomain .= "<province>" . $params['state'] . "</province>";
	}
	else {
		$xml_adddomain .= "<state>" . $RegistrantStateProvince . "</state>";
	}

	$xml_adddomain .= "<postalCode>" . $RegistrantPostalCode . "</postalCode>
<countryCode>" . $RegistrantCountry . "</countryCode>
<contactType>Registration</contactType>
</contact>";
	$xml_adddomain .= "<contact>
<title>Mr.</title>
<firstName>" . $AdminFirstName . "</firstName>
<lastName>" . $AdminLastName . "</lastName>
<emailAddress>" . $AdminEmailAddress . "</emailAddress>
<telephoneNumber>" . $AdminPhone . "</telephoneNumber>
<addressLine1>" . $AdminAddress1 . "</addressLine1>
<addressLine2>" . $AdminAddress2 . "</addressLine2>
<city>" . $AdminCity . "</city>";

	if ($AdminCountry == "US") {
		$xml_adddomain .= "<province>" . $params['adminstate'] . "</province>";
	}
	else {
		$xml_adddomain .= "<state>" . $AdminStateProvince . "</state>";
	}

	$xml_adddomain .= "<postalCode>" . $AdminPostalCode . "</postalCode>
<countryCode>" . $AdminCountry . "</countryCode>
<contactType>Administration</contactType>
</contact>";
	$xml_adddomain .= "</contacts>
</request>
</serviceRequest>";
	$data = registercom_curlCall( $xml_adddomain, $params );
	$domProductId = $data['serviceResponse']['response']['productId']['value'];
	$data = $data['serviceResponse']['status']['statusCode']['value'];

	if ($data != "1000") {
		$values['error'] = "Failed to register the domain " . $domain;
		return $values;
	}

	$domain_product_id = registercom_GetProductIdByDomain( $domain, $params );

	if ($domProductId != $domain_product_id) {
		$values['error'] = "Failed to register the domain " . $domain;
		return $values;
	}

}


function registercom_TransferDomain($params) {
	require ROOTDIR . "/includes/countriescallingcodes.php";
	$tld = $params['tld'];
	$sld = $params['sld'];
	$domain = $sld . "." . $tld;
	$transfersecret = $params['transfersecret'];
	$nameserver1 = $params['ns1'];
	$nameserver2 = $params['ns2'];
	$RegistrantFirstName = $params['firstname'];
	$RegistrantLastName = $params['lastname'];
	$RegistrantAddress1 = $params['address1'];
	$RegistrantAddress2 = $params['address2'];
	$RegistrantCity = $params['city'];
	$RegistrantPostalCode = $params['postcode'];
	$RegistrantCountry = $params['country'];

	if ($RegistrantCountry == "US") {
		$RegistrantStateProvince = registercom_convert_us_state( $params['state'] );

		if ($RegistrantStateProvince == "false") {
			$RegistrantStateProvince = "";
		}
	}
	else {
		$RegistrantStateProvince = "";
	}

	$RegistrantEmailAddress = $params['email'];
	$RegistrantPhone = "+" . $countrycallingcodes[$params['country']] . "." . preg_replace( "/[^0-9]/", "", $params['phonenumber'] );
	$AdminPhone = "+" . $countrycallingcodes[$params['admincountry']] . "." . preg_replace( "/[^0-9]/", "", $params['adminphonenumber'] );
	$AdminFirstName = $params['adminfirstname'];
	$AdminLastName = $params['adminlastname'];
	$AdminAddress1 = $params['adminaddress1'];
	$AdminAddress2 = $params['adminaddress2'];
	$AdminCity = $params['admincity'];
	$AdminStateProvince = $params['adminstate'];
	$AdminPostalCode = $params['adminpostcode'];
	$AdminCountry = $params['admincountry'];

	if ($AdminCountry == "US") {
		$AdminStateProvince = registercom_convert_us_state( $params['adminstate'] );

		if ($AdminStateProvince == "false") {
			$AdminStateProvince = "";
		}
	}
	else {
		$AdminStateProvince = "";
	}

	$AdminEmailAddress = $params['adminemail'];
	$xml_adduser = "<serviceRequest>
<command>userAdd</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<userId>" . $RegistrantEmailAddress . "</userId>
<userAccountName>" . $RegistrantFirstName . " " . $RegistrantLastName . "</userAccountName>
<contacts>
<contact>
<firstName>" . $RegistrantFirstName . "</firstName>
<lastName>" . $RegistrantLastName . "</lastName>
<emailAddress>" . $RegistrantEmailAddress . "</emailAddress>
<telephoneNumber>" . $RegistrantPhone . "</telephoneNumber>
<addressLine1>" . $RegistrantAddress1 . "</addressLine1>
<addressLine2>" . $RegistrantAddress2 . "</addressLine2>
<city>" . $RegistrantCity . "</city>";

	if ($RegistrantCountry == "US") {
		$xml_adduser .= "<province>" . $params['state'] . "</province>";
	}
	else {
		$xml_adduser .= "<state>" . $RegistrantStateProvince . "</state>";
	}

	$xml_adduser .= "<postalCode>" . $RegistrantPostalCode . "</postalCode>
<countryCode>" . $RegistrantCountry . "</countryCode>
<contactType>Registration</contactType>
</contact>";

	if ($AdminEmailAddress != $RegistrantEmailAddress) {
		$xml_adduser .= "<contact>
<firstName>" . $AdminFirstName . "</firstName>
<lastName>" . $AdminLastName . "</lastName>
<emailAddress>" . $AdminEmailAddress . "</emailAddress>
<telephoneNumber>" . $AdminPhone . "</telephoneNumber>
<addressLine1>" . $AdminAddress1 . "</addressLine1>
<addressLine2>" . $AdminAddress2 . "</addressLine2>
<city>" . $AdminCity . "</city>";

		if ($AdminCountry == "US") {
			$xml_adduser .= "<province>" . $params['adminstate'] . "</province>";
		}
		else {
			$xml_adduser .= "<state>" . $AdminStateProvince . "</state>";
		}

		$xml_adduser .= "<postalCode>" . $AdminPostalCode . "</postalCode>
<countryCode>" . $AdminCountry . "</countryCode>
<contactType>Administration</contactType>
</contact>";
	}

	$xml_adduser .= "
</contacts>
</request>
</serviceRequest>";
	$data = registercom_curlCall( $xml_adduser, $params );

	if ($data['serviceResponse']['status']['statusCode']['value'] != "1000" && $data['serviceResponse']['status']['statusCode']['value'] != "1005") {
		$values['error'] = "Could not add or update user account " . $AdminFirstName . " " . $AdminLastName . " with Registrar for the domainTransferIn request of " . $domain;
		return $values;
	}

	$xml_adddomain = "<serviceRequest>
<command>domainTransferIn</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<userId>" . $AdminEmailAddress . "</userId>
<domainName>" . $domain . "</domainName>
<authCode>" . $transfersecret . "</authCode>
<contacts>";
	$xml_adddomain .= "<contact>
<title>Mr.</title>
<firstName>" . $RegistrantFirstName . "</firstName>
<lastName>" . $RegistrantLastName . "</lastName>
<emailAddress>" . $RegistrantEmailAddress . "</emailAddress>
<telephoneNumber>" . $RegistrantPhone . "</telephoneNumber>
<addressLine1>" . $RegistrantAddress1 . "</addressLine1>
<addressLine2>" . $RegistrantAddress2 . "</addressLine2>
<city>" . $RegistrantCity . "</city>";

	if ($RegistrantCountry == "US") {
		$xml_adddomain .= "<province>" . $params['state'] . "</province>";
	}
	else {
		$xml_adddomain .= "<state>" . $RegistrantStateProvince . "</state>";
	}

	$xml_adddomain .= "<postalCode>" . $RegistrantPostalCode . "</postalCode>
<countryCode>" . $RegistrantCountry . "</countryCode>
<contactType>Registration</contactType>
</contact>";
	$xml_adddomain .= "<contact>
<title>Mr.</title>
<firstName>" . $AdminFirstName . "</firstName>
<lastName>" . $AdminLastName . "</lastName>
<emailAddress>" . $AdminEmailAddress . "</emailAddress>
<telephoneNumber>" . $AdminPhone . "</telephoneNumber>
<addressLine1>" . $AdminAddress1 . "</addressLine1>
<addressLine2>" . $AdminAddress2 . "</addressLine2>
<city>" . $AdminCity . "</city>";

	if ($AdminCountry == "US") {
		$xml_adddomain .= "<province>" . $params['adminstate'] . "</province>";
	}
	else {
		$xml_adddomain .= "<state>" . $AdminStateProvince . "</state>";
	}

	$xml_adddomain .= "<postalCode>" . $AdminPostalCode . "</postalCode>
<countryCode>" . $AdminCountry . "</countryCode>
<contactType>Administration</contactType>
</contact>";
	$xml_adddomain .= "</contacts>
</request>
</serviceRequest>";
	$data = registercom_curlCall( $xml_adddomain, $params );
	print_r( $data );
	$domProductId = $data['serviceResponse']['response']['productId']['value'];
	$data = $data['serviceResponse']['status']['statusCode']['value'];

	if ($data != "1000") {
		$values['error'] = "Failed to transfer the domain " . $domain;
		return $values;
	}

	$domain_product_id = registercom_GetProductIdByDomain( $domain, $params );

	if ($domProductId != $domain_product_id) {
		$values['error'] = "Failed to transfer the domain " . $domain;
		return $values;
	}

}


function registercom_RenewDomain($params) {
	$tld = $params['tld'];
	$sld = $params['sld'];
	$domain = $sld . "." . $tld;
	$regperiod = $params['regperiod'];
	$product_id = registercom_GetProductIdByDomain( $domain, $params );
	$xml_domainrenew = "<serviceRequest>
<command>domainRenew</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<productId>" . $product_id . "</productId>
<term>" . $regperiod . "</term>
</request>
</serviceRequest>";
	$data = registercom_curlCall( $xml_domainrenew, $params );
	$domProductId = $data['serviceResponse']['response']['productId']['value'];
	$data = $data['serviceResponse']['status']['statusCode']['value'];

	if ($data != "1000") {
		$values['error'] = "Failed to renew the domain " . $domain;
		return $values;
	}

	$domain_product_id = registercom_GetProductIdByDomain( $domain, $params );

	if ($domProductId != $domain_product_id) {
		$values['error'] = "Failed to renew the domain " . $domain;
		return $values;
	}

}


function registercom_GetContactDetails($params) {
	$tld = $params['tld'];
	$sld = $params['sld'];
	$domain = $sld . "." . $tld;
	$xml = "<serviceRequest>
<command>domainGet</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<page>1</page>
<domains>
<domainName>" . $domain . "</domainName>
</domains>
</request>
</serviceRequest>";
	$data = registercom_curlCall( $xml, $params );
	$data = $data['serviceResponse']['response']['domainGet']['domain']['contacts']['contact'];
	$values['Registrant']["First Name"] = $data[1]['firstName']['value'];
	$values['Registrant']["Last Name"] = $data[1]['lastName']['value'];
	$values['Admin']["First Name"] = $data[0]['firstName']['value'];
	$values['Admin']["Last Name"] = $data[0]['lastName']['value'];
	return $values;
}


function registercom_SaveContactDetails($params) {
	$tld = $params['tld'];
	$sld = $params['sld'];
	$domain = $sld . "." . $tld;
	$currentc_xml = "<serviceRequest>
<command>domainGet</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<page>1</page>
<domains>
<domainName>" . $domain . "</domainName>
</domains>
</request>
</serviceRequest>";
	$curcontacts = registercom_curlCall( $currentc_xml, $params );
	$product_id = $curcontacts['serviceResponse']['response']['domainGet']['domain']['domainInfo']['productId']['value'];
	$curcontacts = $curcontacts['serviceResponse']['response']['domainGet']['domain']['contacts']['contact'];
	$firstname = $params['contactdetails']['Registrant']["First Name"];
	$lastname = $params['contactdetails']['Registrant']["Last Name"];
	$adminfirstname = $params['contactdetails']['Admin']["First Name"];
	$adminlastname = $params['contactdetails']['Admin']["Last Name"];
	$xml_modify = "<serviceRequest>
<command>domainModify</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<productId>" . $product_id . "</productId>
<contacts>
<contact>
<title>Mr.</title>
<firstName>" . $adminfirstname . "</firstName>
<lastName>" . $adminlastname . "</lastName>
<emailAddress>" . $curcontacts[0]['emailAddress']['value'] . "</emailAddress>
<telephoneNumber>" . $curcontacts[0]['telephoneNumber']['value'] . "</telephoneNumber>
<addressLine1>" . $curcontacts[0]['addressLine1']['value'] . "</addressLine1>
<addressLine2>" . $curcontacts[0]['addressLine2']['value'] . "</addressLine2>
<city>" . $curcontacts[0]['city']['value'] . "</city>";

	if ($curcontacts[0]['countryCode']['value'] == "US") {
		$xml_modify .= "<province>" . $curcontacts[0]['province']['value'] . "</province>";
	}
	else {
		$xml_modify .= "<state>" . $curcontacts[0]['state']['value'] . "</state>";
	}

	$xml_modify .= "<postalCode>" . $curcontacts[0]['postalCode']['value'] . "</postalCode>
<countryCode>" . $curcontacts[0]['countryCode']['value'] . "</countryCode>
<contactType>Administration</contactType>
</contact>
<contact>
<title>Mr.</title>
<firstName>" . $firstname . "</firstName>
<lastName>" . $lastname . "</lastName>
<emailAddress>" . $curcontacts[1]['emailAddress']['value'] . "</emailAddress>
<telephoneNumber>" . $curcontacts[1]['telephoneNumber']['value'] . "</telephoneNumber>
<addressLine1>" . $curcontacts[1]['addressLine1']['value'] . "</addressLine1>
<addressLine2>" . $curcontacts[1]['addressLine2']['value'] . "</addressLine2>
<city>" . $curcontacts[1]['city']['value'] . "</city>";

	if ($curcontacts[1]['countryCode']['value'] == "US") {
		$xml_modify .= "<province>" . $curcontacts[1]['province']['value'] . "</province>";
	}
	else {
		$xml_modify .= "<state>" . $curcontacts[1]['state']['value'] . "</state>";
	}

	$xml_modify .= "<postalCode>" . $curcontacts[1]['postalCode']['value'] . "</postalCode>
<countryCode>" . $curcontacts[1]['countryCode']['value'] . "</countryCode>
<contactType>Registration</contactType>
</contact>
</contacts>
</request>
</serviceRequest>";
	registercom_curlCall( $xml_modify, $params );
	$data = $params['tld'];
	$data = $data['serviceResponse']['status']['statusCode']['value'];

	if ($data != "1000") {
		$values['error'] = "The requested domain contact changes were NOT accepted by the registrar for the domain " . $domain;
	}

	return $values;
}


function registercom_xml2array($contents, $get_attributes = 1) {
	if (!$contents) {
		return array();
	}


	if (!function_exists( "xml_parser_create" )) {
		return array();
	}

	$parser = xml_parser_create();
	xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
	xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
	xml_parse_into_struct( $parser, $contents, $xml_values );
	xml_parser_free( $parser );

	if (!$xml_values) {
		return null;
	}

	$xml_array = array();
	$parents = array();
	$opened_tags = array();
	$arr = array();
	$current = &$xml_array;

	foreach ($xml_values as $data) {
		unset( $attributes );
		unset( $value );
		extract( $data );
		$result = "";

		if ($get_attributes) {
			$result = array();

			if (isset( $value )) {
				$result['value'] = $value;
			}


			if (isset( $attributes )) {
				foreach ($attributes as $attr => $val) {

					if ($get_attributes == 1) {
						$result['attr'][$attr] = $val;
						continue;
					}
				}
			}
		}
		else {
			if (isset( $value )) {
				$result = $value;
			}
		}


		if ($type == "open") {
			$parent[$level - 1] = &$current;

			if (!is_array( $current ) || !in_array( $tag, array_keys( $current ) )) {
				$current[$tag] = $result;
				$current = &$current[$tag];

				continue;
			}


			if (isset( $current[$tag][0] )) {
				array_push( $current[$tag], $result );
			}
			else {
				$current[$tag] = array( $current[$tag], $result );
			}

			$last = count( $current[$tag] ) - 1;
			$current = &$current[$tag][$last];

			continue;
		}


		if ($type == "complete") {
			if (!isset( $current[$tag] )) {
				$current[$tag] = $result;
				continue;
			}


			if (( is_array( $current[$tag] ) && $get_attributes == 0 ) || ( ( isset( $current[$tag][0] ) && is_array( $current[$tag][0] ) ) && $get_attributes == 1 )) {
				array_push( $current[$tag], $result );
				continue;
			}

			$current[$tag] = array( $current[$tag], $result );
			continue;
		}


		if ($type == "close") {
			$current = &$parent[$level - 1];

			continue;
		}
	}

	return $xml_array;
}


function registercom_convert_us_state($name, $to = "abbrev") {
	$states = array( array( "name" => "Alabama", "abbrev" => "AL" ), array( "name" => "Alaska", "abbrev" => "AK" ), array( "name" => "Arizona", "abbrev" => "AZ" ), array( "name" => "Arkansas", "abbrev" => "AR" ), array( "name" => "California", "abbrev" => "CA" ), array( "name" => "Colorado", "abbrev" => "CO" ), array( "name" => "Connecticut", "abbrev" => "CT" ), array( "name" => "Delaware", "abbrev" => "DE" ), array( "name" => "Florida", "abbrev" => "FL" ), array( "name" => "Georgia", "abbrev" => "GA" ), array( "name" => "Hawaii", "abbrev" => "HI" ), array( "name" => "Idaho", "abbrev" => "ID" ), array( "name" => "Illinois", "abbrev" => "IL" ), array( "name" => "Indiana", "abbrev" => "IN" ), array( "name" => "Iowa", "abbrev" => "IA" ), array( "name" => "Kansas", "abbrev" => "KS" ), array( "name" => "Kentucky", "abbrev" => "KY" ), array( "name" => "Louisiana", "abbrev" => "LA" ), array( "name" => "Maine", "abbrev" => "ME" ), array( "name" => "Maryland", "abbrev" => "MD" ), array( "name" => "Massachusetts", "abbrev" => "MA" ), array( "name" => "Michigan", "abbrev" => "MI" ), array( "name" => "Minnesota", "abbrev" => "MN" ), array( "name" => "Mississippi", "abbrev" => "MS" ), array( "name" => "Missouri", "abbrev" => "MO" ), array( "name" => "Montana", "abbrev" => "MT" ), array( "name" => "Nebraska", "abbrev" => "NE" ), array( "name" => "Nevada", "abbrev" => "NV" ), array( "name" => "New Hampshire", "abbrev" => "NH" ), array( "name" => "New Jersey", "abbrev" => "NJ" ), array( "name" => "New Mexico", "abbrev" => "NM" ), array( "name" => "New York", "abbrev" => "NY" ), array( "name" => "North Carolina", "abbrev" => "NC" ), array( "name" => "North Dakota", "abbrev" => "ND" ), array( "name" => "Ohio", "abbrev" => "OH" ), array( "name" => "Oklahoma", "abbrev" => "OK" ), array( "name" => "Oregon", "abbrev" => "OR" ), array( "name" => "Pennsylvania", "abbrev" => "PA" ), array( "name" => "Rhode Island", "abbrev" => "RI" ), array( "name" => "South Carolina", "abbrev" => "SC" ), array( "name" => "South Dakota", "abbrev" => "SD" ), array( "name" => "Tennessee", "abbrev" => "TN" ), array( "name" => "Texas", "abbrev" => "TX" ), array( "name" => "Utah", "abbrev" => "UT" ), array( "name" => "Vermont", "abbrev" => "VT" ), array( "name" => "Virginia", "abbrev" => "VA" ), array( "name" => "Washington", "abbrev" => "WA" ), array( "name" => "West Virginia", "abbrev" => "WV" ), array( "name" => "Wisconsin", "abbrev" => "WI" ), array( "name" => "Wyoming", "abbrev" => "WY" ) );
	$return = false;
	foreach ($states as $state) {

		if ($to == "name") {
			if (strtolower( $state['abbrev'] ) == strtolower( $name )) {
				$return = $state['name'];
				break;
			}

			continue;
		}


		if ($to == "abbrev") {
			if (strtolower( $state['name'] ) == strtolower( $name )) {
				$return = strtoupper( $state['abbrev'] );
				break;
			}

			continue;
		}
	}

	return $return;
}


function registercom_curlCall($xml, $params) {
	if ($params['TestMode']) {
		$url = "https://staging-services.rxportalexpress.com/V1.0/";
	}
	else {
		$url = "https://services.rxportalexpress.com/V1.0/";
	}

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $xml );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	$xml_data = curl_exec( $ch );
	curl_close( $ch );
	$tempxml = XMLtoArray( $xml );
	$command = $tempxml['SERVICEREQUEST']['COMMAND'];
	logModuleCall( "registercom", $command, $xml, $xml_data );
	return registercom_xml2array( $xml_data );
}


function registercom_GetProductIdByDomain($domain, $params) {
	$xml = "<serviceRequest>
<command>domainGet</command>
<client>
<applicationGuid>" . $params['applicationGuid'] . "</applicationGuid>
<clientRef>" . md5( date( "YmdHis" ) ) . "</clientRef>
</client>
<request>
<page>1</page>
<domains>
<domainName>" . $domain . "</domainName>
</domains>
</request>
</serviceRequest>";
	$data = registercom_curlCall( $xml, $params );
	return $data['serviceResponse']['response']['domainGet']['domain']['domainInfo']['productId']['value'];
}


?>