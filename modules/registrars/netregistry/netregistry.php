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

function getSOAPClient($username, $password, $proxyHost, $proxyPort) {
	$location = "https://theconsole.netregistry.com.au/external/services/ResellerAPIService/";
	$WSDL = $location . "?wsdl";

	if (( ( ( isset( $proxyHost ) && isset( $proxyPort ) ) && $proxyHost != "" ) && $proxyPort != "" )) {
		$client = new SoapClient( $WSDL, array( "login" => $username, "password" => $password, "proxy_host" => $proxyHost, "proxy_port" => $proxyPort ) );
	}
	else {
		$client = new SoapClient( $WSDL, array( "login" => $username, "password" => $password ) );
	}

	$client->__setLocation( $location );
	return $client;
}


function hasErrors($result) {
	if (( !$result->return->success == "TRUE" || isset( $result->return->errors ) )) {
		return true;
	}

	return false;
}


function printErrors($result) {
	$errors = array();

	if (is_array( $result->return->errors )) {
		$errors = $result->return->errors;
	}
	else {
		$errors = array( $result->return->errors );
	}

	print "<div class='error'><p>";
	foreach ($errors as $error) {

		if (isset( $error->errorMsg )) {
			print "ERROR: " . $error->errorMsg . "<br>";
			continue;
		}

		print "ERROR CODE: " . $error->errorCode . "<br>";
	}

	print "</p></div>";
}


function getErrorString($result) {
	$errors = array();

	if (is_array( $result->return->errors )) {
		$errors = $result->return->errors;
	}
	else {
		$errors = array( $result->return->errors );
	}

	$errorString = "";
	foreach ($errors as $error) {
		$errorString .= "Error message:" . $error->errorMsg . "  Error code:" . $error->errorCode . "
";
	}

	return $errorString;
}


function getHashMap($arrayOfEntries) {
	$resultHashmap = array();

	if (isset( $arrayOfEntries )) {
		foreach ($arrayOfEntries as $entry) {
			$resultHashmap[$entry->key] = $entry->value;
		}
	}

	return $resultHashmap;
}


function getArrayOfEntries($hashMap) {
	$resultArrayOfEntries = array();
	foreach ($hashMap as $key => $value) {
		$object = new stdClass();
		$object->key = $key;
		$object->value = $value;
		array_push( $resultArrayOfEntries, $object );
	}

	return $resultArrayOfEntries;
}


function logError($msg) {
	$fd = fopen( "netreglog.log", "a" );

	if (is_string( $msg )) {
		$str = "[" . date( "Y/m/d h:i:s", mktime() ) . "] " . $msg;
		fwrite( $fd, $str . "
" );
	}

	fclose( $fd );
}


function addIfNotEmpty($array, $value) {
	if (( isset( $value ) && trim( $value ) != "" )) {
		array_push( $array, trim( $value ) );
	}

}


function getStateCodeFromState($state) {
	if (( isset( $state ) && is_string( $state ) )) {
		if (strcasecmp( trim( $state ), "Queensland" ) == 0) {
			return "QLD";
		}


		if (strcasecmp( trim( $state ), "New South Wales" ) == 0) {
			return "NSW";
		}


		if (strcasecmp( trim( $state ), "Victoria" ) == 0) {
			return "VIC";
		}


		if (strcasecmp( trim( $state ), "South Australia" ) == 0) {
			return "SA";
		}


		if (strcasecmp( trim( $state ), "Australian Capital Territory" ) == 0) {
			return "ACT";
		}


		if (strcasecmp( trim( $state ), "Northern Territory" ) == 0) {
			return "NT";
		}


		if (strcasecmp( trim( $state ), "Tasmania" ) == 0) {
			return "TAS";
		}


		if (strcasecmp( trim( $state ), "Western Australia" ) == 0) {
			return "WA";
		}
	}

	return $state;
}


function netregistry_getConfigArray() {
	$configarray = array( "Username" => array( "Type" => "text", "Size" => "20", "Description" => "Enter your username here" ), "Password" => array( "Type" => "password", "Size" => "20", "Description" => "Enter your password here" ), "ProxyHost" => array( "Type" => "text", "Size" => "60", "Description" => "Enter your ProxyHost here (Usually only for shared hosting environment. Not required)" ), "ProxyPort" => array( "Type" => "text", "Size" => "60", "Description" => "Enter your ProxyPort here (Usually only for shared hosting environment. Not required)" ) );
	return $configarray;
}


function netregistry_GetNameservers($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$proxyHost = $params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$values = array();
	try{
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $client->domainInfo( array( "domain" => "" . $sld . "." . $tld ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values["error"] = getErrorString( $result );
	}
	else {
		if (isset( $result->return->fields->entries )) {
			$returnedFields = getHashMap( $result->return->fields->entries );
			$values["ns1"] = $returnedFields["ns.name.0"];
			$values["ns2"] = $returnedFields["ns.name.1"];
			$values["ns3"] = $returnedFields["ns.name.2"];
			$values["ns4"] = $returnedFields["ns.name.3"];
			$values["ns5"] = $returnedFields["ns.name.4"];
		}
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function netregistry_SaveNameservers($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$nameServers = array();
	try{
	addIfNotEmpty( &$nameServers, $params["ns1"] );
	addIfNotEmpty( &$nameServers, $params["ns2"] );
	addIfNotEmpty( &$nameServers, $params["ns3"] );
	addIfNotEmpty( &$nameServers, $params["ns4"] );
	addIfNotEmpty( &$nameServers, $params["ns5"] );
	$values = array();
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $proxyHost = $client->updateDomainNS( array( "domain" => "" . $sld . "." . $tld, "nameServers" => $nameServers ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values["error"] = getErrorString( $result );
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function netregistry_RegisterDomain($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$regperiod = $params["regperiod"];
	$additionalFields = $params["additionalfields"];
	$nameServers = array();
	try{
	addIfNotEmpty( &$nameServers, $params["ns1"] );
	addIfNotEmpty( &$nameServers, $params["ns2"] );
	addIfNotEmpty( &$nameServers, $params["ns3"] );
	addIfNotEmpty( &$nameServers, $params["ns4"] );
	addIfNotEmpty( &$nameServers, $params["ns5"] );

	if (( ( isset( $params["companyname"] ) && $params["companyname"] != null ) || strcmp( trim( $params["companyname"] ), "" ) != 0 )) {
		$organisation = $params["companyname"];
	}
	else {
		$organisation = $params["firstname"] . " " . $params["lastname"];
	}

	$contactDetails = array( "firstName" => $params["firstname"], "lastName" => $params["lastname"], "address1" => $params["address1"], "address2" => $params["address2"], "suburb" => $params["city"], "state" => getStateCodeFromState( $params["state"] ), "postcode" => $params["postcode"], "country" => $params["country"], "organisation" => $organisation, "phone" => $params["phonenumber"], "email" => $params["email"] );
	$strLen = strlen( ".au" );
	$endStr = substr( $tld, strlen( $tld ) - $strLen );

	if (strcasecmp( ".au", $endStr ) == 0) {
		$eligibility = getArrayOfEntries( array( "au.registrant.name" => $additionalFields["Eligibility Name"], "au.registrantid.type" => getAUEligabilityType( $additionalFields["Eligibility ID Type"] ), "au.registrant.number" => $additionalFields["Eligibility ID"], "au.org.type" => $additionalFields["Eligibility Type"] ) );
	}


	if (strcasecmp( "asia", $tld ) == 0) {
		$identNumber = "";

		if (strcmp( trim( $additionalFields["Identification Number (ABN, ACN, Passport number etc.)"] ), "" ) == 0) {
			$identNumber = $additionalFields["Other Identification Form (only needed if you chose 'Other' as the Identification Form)"];
		}
		else {
			$identNumber = $additionalFields["Identification Number (ABN, ACN, Passport number etc.)"];
		}

		$eligibility = getArrayOfEntries( array( "asia.country" => $additionalFields["Country"], "asia.legal.entity.type" => $additionalFields["Legal Entity Type"], "asia.id.form" => $additionalFields["Identification Form"], "asia.id.number" => $identNumber ) );
	}

	$values = array();
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$domainDetails = array( "domain" => "" . $sld . "." . $tld, "period" => $regperiod, "nameServers" => $nameServers, "contactDetails" => $contactDetails, "eligibility" => $eligibility );
	$result = $proxyHost = $client->registerDomain( $domainDetails );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values["error"] = getErrorString( $result );
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function getAUEligabilityType($whmcsType) {
	$eligibilityTypes = array( "Australian Company Number (ACN)" => "ACN", "Australian Business Number (ABN)" => "ABN", "VIC Business Number" => "VIC BN", "NSW Business Number" => "NSW BN", "SA Business Number" => "SA BN", "NT Business Number" => "NT BN", "WA Business Number" => "WA BN", "TAS Business Number" => "TAS BN", "ACT Business Number" => "ACT BN", "QLD Business Number" => "QLD BN", "Trademark (TM)" => "TM", "Other - Used to record an Incorporated Association number" => "OTHER" );
	$result = $eligibilityTypes[$whmcsType];

	if ($result == null) {
		$result = "OTHER";
	}

	return $result;
}


function netregistry_TransferDomain($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$regperiod = $params["regperiod"];
	$transfersecret = $params["transfersecret"];
	$nameserver1 = $params["ns1"];
	$nameserver2 = $params["ns2"];
	$nameserver3 = $params["ns3"];
	$nameserver4 = $params["ns4"];
	$nameserver5 = $params["ns5"];
	$organisation = "";

	if (isset( $params["companyname"] )) {
		$organisation = $params["companyname"];
	}

	$contactDetails = array( "firstName" => $params["firstname"], "lastName" => $params["lastname"], "address1" => $params["address1"], "address2" => $params["address2"], "suburb" => $params["city"], "state" => getStateCodeFromState( $params["state"] ), "postcode" => $params["postcode"], "country" => $params["country"], "organisation" => $organisation, "phone" => $params["phonenumber"], "email" => $params["email"] );
	$values = array();
	try{
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $proxyHost = $client->transferDomain( array( "domain" => "" . $sld . "." . $tld, "contactDetails" => $contactDetails, "authcode" => $transfersecret, "period" => $regperiod ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values["error"] = getErrorString( $result );
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function netregistry_RenewDomain($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$regperiod = $params["regperiod"];
	$values = array();
	try{
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $proxyHost = $client->renewDomain( array( "domain" => "" . $sld . "." . $tld, "period" => $regperiod ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values["error"] = getErrorString( $result );
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function netregistry_GetContactDetails($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$proxyHost = $params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$values = array();
	try{
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $client->domainInfo( array( "domain" => "" . $sld . "." . $tld ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values->error .= getErrorString( $result );
		return $values;
	}


	if (isset( $result->return->fields->entries )) {
		$returnedFields = getHashMap( $result->return->fields->entries );

		if (( 5 <= strlen( $tld ) && strcmp( substr( $tld, 0 - 5 ), "co.uk" ) == 0 )) {
			print "<div id='infobox'>
                        <strong>Registrar Warning</strong>
                        <br/>
                        Saving the Registrant contact details for the .co.uk tld will have no effect. You must contact Nominet directly to update the registrant contact. You can still update the Tech and admin contacts.
                      </div>";
		}
		else {
			if (strcmp( substr( $tld, 0 - 2 ), "eu" ) == 0) {
				print "<div id='infobox'>
                        <strong>Registrar Warning</strong>
                        <br/>
                        Saving the Registrant contact details for the .eu tld will have no effect. You must contact Eurid directly to update the registrant contact. You can still update the Tech and admin contacts.
                      </div>";
			}
		}


		if (( isset( $returnedFields["domain.ownerid"] ) && strcmp( trim( $returnedFields["domain.ownerid"] ), "" ) != 0 )) {
			netregistry_getContactsDetails( "" . $sld . "." . $tld, $returnedFields["domain.ownerid"], "Registrant", $client, $values );
		}


		if (( isset( $returnedFields["domain.adminid"] ) && strcmp( trim( $returnedFields["domain.adminid"] ), "" ) != 0 )) {
			netregistry_getContactsDetails( "" . $sld . "." . $tld, $returnedFields["domain.adminid"], "Admin", $client, $values );
		}


		if (( isset( $returnedFields["domain.techid"] ) && strcmp( trim( $returnedFields["domain.techid"] ), "" ) != 0 )) {
			netregistry_getContactsDetails( "" . $sld . "." . $tld, $returnedFields["domain.techid"], "Tech", $client, $values );
		}
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function netregistry_getContactsDetails($domain, $nicHandle, $contactType, &$client, $returnValues) {
	$contactResult = $client->contactInfo( array( "domain" => $domain, "nicHandle" => $nicHandle ) );

	if (hasErrors( $contactResult )) {
		logError( getErrorString( $contactResult ) );
		$returnValues->error .= getErrorString( $contactResult ) . "<br>";
		return null;
	}


	if ($contactResult->return->fields->entries) {
		$domainContact = getHashMap( $contactResult->return->fields->entries );

		if (( isset( $domainContact["user.firstname"] ) || isset( $domainContact["user.organisation"] ) )) {
			$strLen = strlen( ".nz" );
			$endStr = substr( $domain, strlen( $domain ) - $strLen );

			if (strcasecmp( ".nz", $endStr ) == 0) {
				$returnValues[$contactType]["Contact Name"] = $domainContact["user.organisation"];
				$returnValues[$contactType]["Address 1"] = $domainContact["user.address1"];
				$returnValues[$contactType]["Address 2"] = $domainContact["user.address2"];
				$returnValues[$contactType]["Suburb"] = $domainContact["user.suburb"];
				$returnValues[$contactType]["Country"] = $domainContact["user.country"];
				$returnValues[$contactType]["Phone"] = $domainContact["user.phone"];
				$returnValues[$contactType]["Email"] = $domainContact["user.email"];
				return null;
			}

			$returnValues[$contactType]["First Name"] = $domainContact["user.firstname"];
			$returnValues[$contactType]["Last Name"] = $domainContact["user.lastname"];
			$returnValues[$contactType]["Address 1"] = $domainContact["user.address1"];
			$returnValues[$contactType]["Address 2"] = $domainContact["user.address2"];
			$returnValues[$contactType]["Address 3"] = $domainContact["user.address3"];
			$returnValues[$contactType]["Address 4"] = $domainContact["user.address4"];
			$returnValues[$contactType]["Suburb"] = $domainContact["user.suburb"];
			$returnValues[$contactType]["State"] = getStateCodeFromState( $domainContact["user.state"] );
			$returnValues[$contactType]["Postcode"] = $domainContact["user.postcode"];
			$returnValues[$contactType]["Country"] = $domainContact["user.country"];
			$returnValues[$contactType]["Phone"] = $domainContact["user.phone"];
			$returnValues[$contactType]["Organisation"] = $domainContact["user.organisation"];
			$returnValues[$contactType]["Fax"] = $domainContact["user.fax"];
			$returnValues[$contactType]["Mobile"] = $domainContact["user.mobile"];
			$returnValues[$contactType]["Email"] = $domainContact["user.email"];
		}
	}

}


function netregistry_SaveContactDetails($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$proxyHost = $params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$values = array();
	try{
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $client->domainInfo( array( "domain" => "" . $sld . "." . $tld ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values->error .= getErrorString( $result );
		return $values;
	}


	if (isset( $result->return->fields->entries )) {
		$returnedFields = getHashMap( $result->return->fields->entries );

		if (!( ( 5 <= strlen( $tld ) && strcmp( substr( $tld, 0 - 5 ), "co.uk" ) == 0 ) || strcmp( substr( $tld, 0 - 2 ), "eu" ) == 0 )) {
			if (( isset( $returnedFields["domain.ownerid"] ) && strcmp( trim( $returnedFields["domain.ownerid"] ), "" ) != 0 )) {
				netregistry_updateContact( "" . $sld . "." . $tld, $returnedFields["domain.ownerid"], $params, "Registrant", $client, $values );
			}
		}


		if (( isset( $returnedFields["domain.adminid"] ) && strcmp( trim( $returnedFields["domain.adminid"] ), "" ) != 0 )) {
			netregistry_updateContact( "" . $sld . "." . $tld, $returnedFields["domain.adminid"], $params, "Admin", $client, $values );
		}


		if (( isset( $returnedFields["domain.techid"] ) && strcmp( trim( $returnedFields["domain.techid"] ), "" ) != 0 )) {
			netregistry_updateContact( "" . $sld . "." . $tld, $returnedFields["domain.techid"], $params, "Tech", $client, $values );
		}
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function netregistry_updateContact($domain, $nicHandle, $params, $contactType, &$client, $returnValues) {
	$contactDetails = array();
	$strLen = strlen( ".nz" );
	$endStr = substr( $domain, strlen( $domain ) - $strLen );

	if (strcasecmp( ".nz", $endStr ) == 0) {
		$contactDetails = array( "firstName" => "", "lastName" => "", "address1" => $params["contactdetails"][$contactType]["Address 1"], "address2" => $params["contactdetails"][$contactType]["Address 2"], "address3" => "", "address4" => "", "suburb" => $params["contactdetails"][$contactType]["Suburb"], "state" => "", "postcode" => "", "country" => $params["contactdetails"][$contactType]["Country"], "phone" => $params["contactdetails"][$contactType]["Phone"], "organisation" => $params["contactdetails"][$contactType]["Contact Name"], "fax" => "", "mobile" => "", "email" => $params["contactdetails"][$contactType]["Email"] );
	}
	else {
		$contactDetails = array( "firstName" => $params["contactdetails"][$contactType]["First Name"], "lastName" => $params["contactdetails"][$contactType]["Last Name"], "address1" => $params["contactdetails"][$contactType]["Address 1"], "address2" => $params["contactdetails"][$contactType]["Address 2"], "address3" => $params["contactdetails"][$contactType]["Address 3"], "address4" => $params["contactdetails"][$contactType]["Address 4"], "suburb" => $params["contactdetails"][$contactType]["Suburb"], "state" => getStateCodeFromState( $params["contactdetails"][$contactType]["State"] ), "postcode" => $params["contactdetails"][$contactType]["Postcode"], "country" => $params["contactdetails"][$contactType]["Country"], "phone" => $params["contactdetails"][$contactType]["Phone"], "organisation" => $params["contactdetails"][$contactType]["Organisation"], "fax" => $params["contactdetails"][$contactType]["Fax"], "mobile" => $params["contactdetails"][$contactType]["Mobile"], "email" => $params["contactdetails"][$contactType]["Email"] );
	}

	$updateResult = $client->contactUpdate( array( "domain" => $domain, "nicHandle" => $nicHandle, "contactDetails" => $contactDetails ) );

	if (hasErrors( $updateResult )) {
		logError( $updateResult );
		$returnValues->error .= $contactType . " save: " . getErrorString( $updateResult ) . "<br>";
	}

}


function netregistry_GetEPPCode($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$proxyHost = $params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$values = array();
	try{
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $client->domainAuthcode( array( "domain" => "" . $sld . "." . $tld ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values["error"] = getErrorString( $result );
	}
	else {
		if ($result->return->fields->entries) {
			$domainAuthcode = getHashMap( $result->return->fields->entries );
			$values["eppcode"] = $domainAuthcode["domain.authcode"];
		}
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function netregistry_GetRegistrarLock($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$proxyHost = $params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$lockstatus = "unlocked";

	if (( ( strcmp( substr( $tld, 0 - 2 ), "au" ) == 0 || strcmp( substr( $tld, 0 - 2 ), "uk" ) == 0 ) || strcmp( substr( $tld, 0 - 2 ), "nz" ) == 0 )) {
		return $lockstatus;
	}
	try{
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $client->domainInfo( array( "domain" => "" . $sld . "." . $tld ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
	}
	else {
		if (isset( $result->return->fields->entries )) {
			$returnedFields = getHashMap( $result->return->fields->entries );

			if (( strpos( $returnedFields["domain.status"], "clientTransferProhibited" ) !== false || strpos( $returnedFields["domain.status"], "REGISTRAR-LOCK" ) !== false )) {
				$lockstatus = "locked";
			}
		}
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
	}
	return $lockstatus;
}


function netregistry_SaveRegistrarLock($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$proxyHost = $params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$values = array();
	try{
	if (strcmp( substr( $tld, 0 - 2 ), "au" ) == 0) {
		$values->error .= "ERROR: .au domains do not implement lock functionality";
		return $values;
	}


	if (strcmp( substr( $tld, 0 - 2 ), "nz" ) == 0) {
		$values->error .= "ERROR: .nz domains do not implement lock functionality";
		return $values;
	}


	if (strcmp( substr( $tld, 0 - 2 ), "uk" ) == 0) {
		$values->error .= "ERROR: .uk domains do not implement lock functionality";
		return $values;
	}

	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );

	if (( isset( $params["lockenabled"] ) && strcmp( $params["lockenabled"], "locked" ) == 0 )) {
		$result = $client->lockDomain( array( "domain" => "" . $sld . "." . $tld ) );
	}
	else {
		$result = $client->unlockDomain( array( "domain" => "" . $sld . "." . $tld ) );
	}


	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values["error"] = getErrorString( $result );
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function netregistry_RegisterNameserver($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$nameserver = $params["nameserver"];
	$ipaddress = $params["ipaddress"];
	$values = array();
	try{
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $proxyHost = $client->createHost( array( "domain" => "" . $sld . "." . $tld, "hostName" => $nameserver, "ip" => $ipaddress ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values["error"] = getErrorString( $result );
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function netregistry_ModifyNameserver($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$nameserver = $params["nameserver"];
	$currentipaddress = $params["currentipaddress"];
	$newipaddress = $params["newipaddress"];
	$values = array();
	try{
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $proxyHost = $client->updateHost( array( "domain" => "" . $sld . "." . $tld, "hostName" => $nameserver, "ip" => $newipaddress ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values["error"] = getErrorString( $result );
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


function netregistry_DeleteNameserver($params) {
	$username = $params["Username"];
	$password = $params["Password"];
	$params["ProxyHost"];
	$proxyPort = $params["ProxyPort"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$nameserver = $params["nameserver"];
	$values = array();
	try{
	$client = getSOAPClient( $username, $password, $proxyHost, $proxyPort );
	$result = $proxyHost = $client->deleteHost( array( "domain" => "" . $sld . "." . $tld, "hostName" => $nameserver ) );

	if (hasErrors( $result )) {
		logError( getErrorString( $result ) );
		$values["error"] = getErrorString( $result );
	}
	}
	catch ( SoapFault $fault ) {
		logError( $fault );
		$values->error .= $fault;
	}
	return $values;
}


?>