<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

function dottk_getConfigArray() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Dot.tk" ), "Email" => array( "Type" => "text", "Size" => "40", "Description" => "Enter your registered email address here" ), "Password" => array( "Type" => "password", "Size" => "20", "Description" => "Enter your password here" ), "TestPrefix" => array( "Type" => "text", "Size" => "20", "Description" => "Enter your test prefix here" ), "TestMode" => array( "Type" => "yesno" ) );
	return $configarray;
}


function dottk_GetNameservers($params) {
	$DOTTKAPI_user = $params["Email"];
	$DOTTKAPI_phrase = $params["Password"];
	$testmode = $params["TestMode"];

	if ($testmode) {
		$DOTTKAPI_testmode = 5;
		$DOTTKAPI_testprefix = $params["TestPrefix"];
	}

	$tld = $params["tld"];
	$sld = $params["sld"];
	$name = $sld . "." . $tld;

	if ($DOTTKAPI_testmode) {
		$domainname = $DOTTKAPI_testprefix . $name;
	}
	else {
		$domainname = $name;
	}

	$query = dottk_buildquery( array( "function" => "domain_status", "email" => $DOTTKAPI_user, "password" => $DOTTKAPI_phrase, "domainname" => $domainname ) );
	$response = dottk_put( $query, "POST", $params );
	$return["ns1"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["NAMESERVERS"]["HOSTNAME"];
	$return["ns2"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["NAMESERVERS1"]["HOSTNAME"];
	$return["ns3"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["NAMESERVERS2"]["HOSTNAME"];
	$return["ns4"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["NAMESERVERS3"]["HOSTNAME"];
	$return["ns5"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["NAMESERVERS4"]["HOSTNAME"];

	if ($response["curlerror"]) {
		return array( "error" => $response["curlerror"] );
	}


	if ($response["DOTTK"]["STATUS"] == "NOT OK") {
		return array( "error" => $response["DOTTK"]["REASON"] );
	}


	if ($response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["STATUS"] == "AVAILABLE") {
		return array( "error" => "Domain is not yet registered" );
	}

	return $return;
}


function dottk_SaveNameservers($params) {
	$DOTTKAPI_user = $params["Email"];
	$DOTTKAPI_phrase = $params["Password"];
	$testmode = $params["TestMode"];

	if ($testmode) {
		$DOTTKAPI_testmode = 5;
		$DOTTKAPI_testprefix = $params["TestPrefix"];
	}

	$tld = $params["tld"];
	$sld = $params["sld"];
	$name = $sld . "." . $tld;

	if ($DOTTKAPI_testmode) {
		$domainname = $DOTTKAPI_testprefix . $name;
	}
	else {
		$domainname = $name;
	}

	$qstring = array( "function" => "updatedns", "email" => $DOTTKAPI_user, "password" => $DOTTKAPI_phrase, "domainname" => $domainname );
	$nameserver1 = $params["ns1"];
	$nameserver2 = $params["ns2"];
	$nameserver3 = $params["ns3"];
	$nameserver4 = $params["ns4"];
	$nameserver5 = $params["ns5"];

	if ($nameserver1) {
		$qstring["nameserver1"] = $nameserver1;
	}


	if ($nameserver2) {
		$qstring["nameserver2"] = $nameserver2;
	}


	if ($nameserver3) {
		$qstring["nameserver3"] = $nameserver3;
	}


	if ($nameserver4) {
		$qstring["nameserver4"] = $nameserver4;
	}


	if ($nameserver5) {
		$qstring["nameserver5"] = $nameserver5;
	}

	$query = dottk_buildquery( $qstring );
	$query = preg_replace( "/\[(?:[0-9]|[1-9][0-9]+)\]=/", "=", $query );
	$query = preg_replace( "/%5B(?:[0-9]|[1-9][0-9]+)%5D=/", "=", $query );
	$response = dottk_put( $query, "POST", $params );

	if ($response["curlerror"]) {
		return array( "error" => $response["curlerror"] );
	}


	if ($response["DOTTK"]["STATUS"] == "NOT OK") {
		return array( "error" => $response["DOTTK"]["REASON"] );
	}

}


function dottk_RegisterDomain($params) {
	$DOTTKAPI_user = $params["Email"];
	$DOTTKAPI_phrase = $params["Password"];
	$testmode = $params["TestMode"];

	if ($testmode) {
		$DOTTKAPI_testmode = 5;
		$DOTTKAPI_testprefix = $params["TestPrefix"];
	}

	$tld = $params["tld"];
	$sld = $params["sld"];
	$name = $sld . "." . $tld;
	$regperiod = $params["regperiod"];
	$nameserver1 = $params["ns1"];
	$nameserver2 = $params["ns2"];
	$nameserver3 = $params["ns3"];
	$nameserver4 = $params["ns4"];
	$nameserver5 = $params["ns5"];

	if ($DOTTKAPI_testmode) {
		$domainname = $DOTTKAPI_testprefix . $name;
	}
	else {
		$domainname = $name;
	}

	$qstring = array( "function" => "register", "email" => $DOTTKAPI_user, "password" => $DOTTKAPI_phrase, "domainname" => $domainname, "lengthofregistration" => $regperiod );

	if ($nameserver1) {
		$qstring["nameserver1"] = $nameserver1;
	}


	if ($nameserver2) {
		$qstring["nameserver2"] = $nameserver2;
	}


	if ($nameserver3) {
		$qstring["nameserver3"] = $nameserver3;
	}


	if ($nameserver4) {
		$qstring["nameserver4"] = $nameserver4;
	}


	if ($nameserver5) {
		$qstring["nameserver5"] = $nameserver5;
	}

	$query = dottk_buildquery( $qstring );
	$query = preg_replace( "/\[(?:[0-9]|[1-9][0-9]+)\]=/", "=", $query );
	$query = preg_replace( "/%5B(?:[0-9]|[1-9][0-9]+)%5D=/", "=", $query );
	$response = dottk_put( $query, "POST", $params );

	if ($response["curlerror"]) {
		return array( "error" => $response["curlerror"] );
	}


	if ($response["DOTTK"]["STATUS"] == "NOT OK") {
		return array( "error" => $response["DOTTK"]["REASON"] );
	}


	if ($response["DOTTK"]["PARTNER_REGISTRATION"]["STATUS"] == "NOT AVAILABLE") {
		return array( "error" => "Domain is already registered" );
	}

	$RegistrantFirstName = $params["firstname"];
	$RegistrantLastName = $params["lastname"];
	$RegistrantAddress1 = $params["address1"];
	$RegistrantAddress2 = $params["address2"];
	$RegistrantCompany = $params["company"];
	$RegistrantCity = $params["city"];
	$RegistrantStateProvince = $params["state"];
	$RegistrantPostalCode = $params["postcode"];
	$RegistrantCountry = $params["country"];
	$RegistrantEmailAddress = $params["email"];
	$RegistrantPhone = $params["phonenumber"];

	if (!$RegistrantCompany) {
		$RegistrantCompany = "None";
	}

	$qstring = array( "function" => "updatewhois", "email" => $DOTTKAPI_user, "password" => $DOTTKAPI_phrase, "domainname" => $domainname );

	if ($RegistrantCompany != "") {
		$qstring["reg_company"] = $RegistrantCompany;
	}


	if ($RegistrantFirstName != "") {
		$qstring["reg_name"] = $RegistrantFirstName . " " . $RegistrantLastName;
	}


	if ($RegistrantAddress1 != "") {
		$qstring["reg_address"] = $RegistrantAddress1;
	}


	if ($RegistrantPostalCode != "") {
		$qstring["reg_zipcode"] = $RegistrantPostalCode;
	}


	if ($RegistrantCity != "") {
		$qstring["reg_city"] = $RegistrantCity;
	}


	if ($RegistrantCountry != "") {
		$qstring["reg_countrycode"] = $RegistrantCountry;
	}


	if ($RegistrantStateProvince != "") {
		$qstring["reg_statecode"] = $RegistrantStateProvince;
	}


	if ($RegistrantPhone != "") {
		$qstring["reg_phone_nr"] = $RegistrantPhone;
	}


	if ($RegistrantPhone != "") {
		$qstring["reg_fax_nr"] = $RegistrantPhone;
	}


	if ($RegistrantEmailAddress != "") {
		$qstring["reg_email"] = $RegistrantEmailAddress;
	}

	$query = dottk_buildquery( $qstring );
	$response = dottk_put( $query, "POST", $params );

	if ($response["curlerror"]) {
		return array( "error" => $response["curlerror"] );
	}


	if ($response["DOTTK"]["STATUS"] == "NOT OK") {
		return array( "error" => $response["DOTTK"]["REASON"] );
	}

	$values["error"] = $error;
	return $values;
}


function dottk_RenewDomain($params) {
	$DOTTKAPI_user = $params["Email"];
	$DOTTKAPI_phrase = $params["Password"];
	$testmode = $params["TestMode"];

	if ($testmode) {
		$DOTTKAPI_testmode = 5;
		$DOTTKAPI_testprefix = $params["TestPrefix"];
	}

	$tld = $params["tld"];
	$sld = $params["sld"];
	$name = $sld . "." . $tld;
	$regperiod = $params["regperiod"];

	if ($DOTTKAPI_testmode) {
		$domainname = $DOTTKAPI_testprefix . $name;
	}
	else {
		$domainname = $name;
	}

	$query = dottk_buildquery( array( "function" => "renew", "email" => $DOTTKAPI_user, "password" => $DOTTKAPI_phrase, "domainname" => $domainname, "lengthofregistration" => $regperiod ) );
	$response = dottk_put( $query, "POST", $params );

	if ($response["curlerror"]) {
		return array( "error" => $response["curlerror"] );
	}


	if ($response["DOTTK"]["STATUS"] == "NOT OK") {
		return array( "error" => $response["DOTTK"]["REASON"] );
	}

}


function dottk_GetContactDetails($params) {
	$DOTTKAPI_user = $params["Email"];
	$DOTTKAPI_phrase = $params["Password"];
	$testmode = $params["TestMode"];

	if ($testmode) {
		$DOTTKAPI_testmode = 5;
		$DOTTKAPI_testprefix = $params["TestPrefix"];
	}

	$tld = $params["tld"];
	$sld = $params["sld"];
	$name = $sld . "." . $tld;

	if ($DOTTKAPI_testmode) {
		$domainname = $DOTTKAPI_testprefix . $name;
	}
	else {
		$domainname = $name;
	}

	$query = dottk_buildquery( array( "function" => "domain_status", "email" => $DOTTKAPI_user, "password" => $DOTTKAPI_phrase, "domainname" => $domainname ) );
	$response = dottk_put( $query, "POST", $params );
	$values["Registrant"]["Name"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["WHOIS_INFO"]["REG_NAME"];
	$values["Registrant"]["Company"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["WHOIS_INFO"]["REG_COMPANY"];
	$values["Registrant"]["Address"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["WHOIS_INFO"]["REG_ADDRESS"];
	$values["Registrant"]["City"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["WHOIS_INFO"]["REG_CITY"];
	$values["Registrant"]["Country"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["WHOIS_INFO"]["REG_COUNTRYCODE"];
	$values["Registrant"]["Zip Code"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["WHOIS_INFO"]["REG_ZIPCODE"];
	$values["Registrant"]["Email"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["WHOIS_INFO"]["REG_EMAIL"];
	$values["Registrant"]["Telephone"] = $response["DOTTK"]["PARTNER_DOMAIN_STATUS"]["WHOIS_INFO"]["REG_PHONE_NR"];

	if ($response["curlerror"]) {
		return array( "error" => $response["curlerror"] );
	}


	if ($response["DOTTK"]["STATUS"] == "NOT OK") {
		return array( "error" => $response["DOTTK"]["REASON"] );
	}

	return $values;
}


function dottk_SaveContactDetails($params) {
	$custname = $params["contactdetails"]["Registrant"]["Name"];
	$company = $params["contactdetails"]["Registrant"]["Company"];
	$address = $params["contactdetails"]["Registrant"]["Address"];
	$city = $params["contactdetails"]["Registrant"]["City"];
	$country = $params["contactdetails"]["Registrant"]["Country"];
	$zipcode = $params["contactdetails"]["Registrant"]["Zip Code"];
	$email = $params["contactdetails"]["Registrant"]["Email"];
	$telephone = $params["contactdetails"]["Registrant"]["Telephone"];
	$DOTTKAPI_user = $params["Email"];
	$DOTTKAPI_phrase = $params["Password"];
	$testmode = $params["TestMode"];

	if ($testmode) {
		$DOTTKAPI_testmode = 5;
		$DOTTKAPI_testprefix = $params["TestPrefix"];
	}

	$tld = $params["tld"];
	$sld = $params["sld"];
	$name = $sld . "." . $tld;

	if ($DOTTKAPI_testmode) {
		$domainname = $DOTTKAPI_testprefix . $name;
	}
	else {
		$domainname = $name;
	}

	$qstring = array( "function" => "updatewhois", "email" => $DOTTKAPI_user, "password" => $DOTTKAPI_phrase, "domainname" => $domainname );

	if ($company != "") {
		$qstring["reg_company"] = $company;
	}


	if ($name != "") {
		$qstring["reg_name"] = $custname;
	}


	if ($address != "") {
		$qstring["reg_address"] = $address;
	}


	if ($zipcode != "") {
		$qstring["reg_zipcode"] = $zipcode;
	}


	if ($city != "") {
		$qstring["reg_city"] = $city;
	}


	if ($country != "") {
		$qstring["reg_countrycode"] = $country;
	}


	if ($state != "") {
		$qstring["reg_statecode"] = $state;
	}


	if ($phone != "") {
		$qstring["reg_phone_nr"] = $phone;
	}


	if ($fax != "") {
		$qstring["reg_fax_nr"] = $fax;
	}


	if ($email != "") {
		$qstring["reg_email"] = $email;
	}

	$query = dottk_buildquery( $qstring );
	$response = dottk_put( $query, "POST", $params );

	if ($response["curlerror"]) {
		return array( "error" => $response["curlerror"] );
	}


	if ($response["DOTTK"]["STATUS"] == "NOT OK") {
		return array( "error" => $response["DOTTK"]["REASON"] );
	}

}


function dottk_RegisterNameserver($params) {
	$DOTTKAPI_user = $params["Email"];
	$DOTTKAPI_phrase = $params["Password"];
	$testmode = $params["TestMode"];

	if ($testmode) {
		$DOTTKAPI_testmode = 5;
		$DOTTKAPI_testprefix = $params["TestPrefix"];
	}

	$nameserver = $params["nameserver"];
	$ipaddress = $params["ipaddress"];
	$query = dottk_buildquery( array( "function" => "host_registration", "email" => $DOTTKAPI_user, "password" => $DOTTKAPI_phrase, "hostname" => $nameserver, "ipaddress" => $ipaddress ) );
	$response = dottk_put( $query, "POST", $params );

	if ($response["curlerror"]) {
		return array( "error" => $response["curlerror"] );
	}


	if ($response["DOTTK"]["STATUS"] == "NOT OK") {
		return array( "error" => $response["DOTTK"]["REASON"] );
	}

	return "success";
}


function dottk_DeleteNameserver($params) {
	$DOTTKAPI_user = $params["Email"];
	$DOTTKAPI_phrase = $params["Password"];
	$testmode = $params["TestMode"];

	if ($testmode) {
		$DOTTKAPI_testmode = 5;
		$DOTTKAPI_testprefix = $params["TestPrefix"];
	}

	$nameserver = $params["nameserver"];
	$ipaddress = $params["ipaddress"];
	$query = dottk_buildquery( array( "function" => "host_removal", "email" => $DOTTKAPI_user, "password" => $DOTTKAPI_phrase, "hostname" => $nameserver ) );
	$response = dottk_put( $query, "POST", $params );

	if ($response["curlerror"]) {
		return array( "error" => $response["curlerror"] );
	}


	if ($response["DOTTK"]["STATUS"] == "NOT OK") {
		return array( "error" => $response["DOTTK"]["REASON"] );
	}

	return "success";
}


function dottk_put($xml, $callmethod, $params) {
	global $DOTTKAPI_user;
	global $DOTTKAPI_phrase;

	$xurl = "https://secure.dot.tk/partners/partnerapi.tk";
	$DOTTKAPI_xmlout = 4;
	$headers = array( "Accept: application/x-www-form-urlencoded", "Content-Type: application/x-www-form-urlencoded" );
	$session = curl_init();
	curl_setopt( $session, CURLOPT_URL, $xurl );
	curl_setopt( $session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
	curl_setopt( $session, CURLOPT_POSTFIELDS, $xml );
	curl_setopt( $session, CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $session, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $session, CURLOPT_SSL_VERIFYPEER, false );
	$response = curl_exec( $session );
	$action = explode( "&", $xml );
	$action = explode( "=", $action[0] );
	$action = end( $action );
	logModuleCall( "dottk", $action, $xml, $response, "", array( urlencode( $params["Password"] ), urlencode( $params["Email"] ) ) );

	if (curl_errno( $session )) {
		$response["curlerror"] = "CURL Error: " . curl_errno( $session ) . " - " . curl_error( $session );
		curl_close( $session );
		return $response;
	}

	curl_close( $session );

	if ($DOTTKAPI_xmlout == 0) {
		$xmlarray = XMLtoARRAY( $response );
		return $xmlarray;
	}

	return $response;
}


function dottk_buildquery($formdata) {
	$formdata["resellerid"] = "7615661";
	$query = "";
	foreach ($formdata as $k => $v) {

		if (substr( $k, 0, 10 ) == "nameserver") {
			$k = "nameserver";
		}

		$query .= "" . $k . "=" . urlencode( $v ) . "&";
	}

	return $query;
}


?>