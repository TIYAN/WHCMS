<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 * */

/**
 * Returns the TransIP -> WHMCS Contact type mapping.
 *
 * @author Alwin Garside <alwin@transip.nl>
 * @return array  Associative array that maps TransIP WhoisContact types to WHMCS contact types.
 */
function transip_getContactTypeMapping() {
	return array( Transip_WhoisContact::TYPE_REGISTRANT => "Registrant contact", Transip_WhoisContact::TYPE_ADMINISTRATIVE => "Administrative contact", Transip_WhoisContact::TYPE_TECHNICAL => "Technical contact" );
}


/**
 * Returns the TransIP -> WHMCS Contact field mapping.
 *
 * @author Alwin Garside <alwin@transip.nl>
 *
 * @return array  Associative array that maps TransIP WhoisContact fields to WHMCS fields.
 */
function transip_getContactFieldMapping() {
	return array( "firstName" => "First name", "middleName" => "Middle name", "lastName" => "Last name", "companyName" => "Company name", "companyKvk" => "Company KvK number", "companyType" => "Company type", "street" => "Street", "number" => "Number", "postalCode" => "Postal code", "city" => "City", "phoneNumber" => "Phone number", "faxNumber" => "Fax number", "email" => "Email address", "country" => "Country code" );
}


/**
 *
 *
 * @return array
 */
function transip_getConfigArray() {
	return array( "Endpoint" => array( "Type" => "dropdown", "Options" => "api.transip.nl,api.transip.be,api.transip.eu" ), "Login" => array( "Type" => "text", "Size" => "32", "Description" => "Your TransIP login name" ), "PrivateKey" => array( "Type" => "textarea", "Description" => "Private key (downloaded from your Controlpanel)" ), "ReadOnlyMode" => array( "Type" => "yesno", "Description" => "Don't allow any changes, use for testing" ) );
}


/**
 *
 *
 * @param array   $params
 */
function transip_initialize($params) {
	Transip_ApiSettings::$login = $params['Login'];
	Transip_ApiSettings::$privateKey = $params['PrivateKey'];
	Transip_ApiSettings::$mode = ($params['ReadOnlyMode'] ? "readonly" : "readwrite");
	Transip_ApiSettings::$endpoint = $params['Endpoint'];
}


/**
 *
 *
 * @author Johan Schuyt <johan@transip.nl>
 *
 * @param string  $address
 *
 * @return array
 */
function transip_splitAddress($address) {

	$address = trim($address);
	try
	{
		return transip_convertaddress($address);
	}
	catch (Exception $e)
	{
		if (preg_match("/[0-9]/Usi", $address, $matches))
		{
			if ($address == (string)intval($address))
			{
				return array("Postbus", trim($address));
			}
			$address = preg_replace("/([^0-9]*)([0-9]+)([^\\s]*)(.*)/si", '' . "\$1\$4 \$2\$3", $address);
			$address = trim($address);
			try
			{
				return transip_convertaddress($address);
			}
			catch (Exception $e)
			{
				return array("FakeAddress", "1");
        }
    }
    else
    {
        return array( $address, "1" );
    }
}
}


/**
 *
 *
 * @author Johan Schuyt <johan@transip.nl>
 *
 * @param string  $address
 *
 * @return array
 * @throws Exception
 */
function transip_convertAddress($address) {
	$maxLoop = 137;
	$address = preg_replace( "/(.*\s)([0-9]+)(\s*)-(\s*)([0-9]+)(.*)/si", "-", $address );
	$address = preg_replace( "/(.*\s)([0-9]+)\s([0-9]+)\s*hg(.*)/Usi", "-hg", $address );
	$address = preg_replace( "/(.*\s)([0-9]+)(\s*)(kamer|k)(\s*)([0-9]+)(.*)/si", "-k", $address );

	if (preg_match( "/^([^0-9\s]+)([0-9]+)$/", $address, $matches )) {
		return array( $matches[1], $matches[2] );
	}


	while (( !preg_match( "/(.*)[^0-9][0-9]+?[^\s]*$/Usi", $address, $matches ) && $maxLoop )) {
		$parts = explode( " ", $address );

		if (count( $parts ) < 3) {
			throw new Exception( "Could not convert address '" . $address . "'" );
		}

		$lastEl = array_pop( $parts );
		$parts .= count( $parts ) - 1;
		$address = implode( " ", $parts );
		--$maxLoop;
	}


	if (!preg_match( "/(.*)[^0-9][0-9]+?[^\s]*$/Usi", $address, $matches ) && $maxLoop == 0) {
		throw new Exception( "Hit limit of 10 rounds while trying to convert address line '" . $address . "'" );
	}

	$street = $matches[1];
	$number = trim( substr( $address, strlen( $street ) ) );
	return array( $street, $number );
}


/**
 *
 *
 * @param array   $data
 *
 * @return array
 */
function transip_getContactsForRegisterAndTransfer($data) {
	$address = transip_splitAddress( $data['address1'] );
	$reg = new Transip_WhoisContact();
	$reg->type = "registrant";
	$reg->firstName = $data['firstname'];
	$reg->lastName = $data['lastname'];
	$reg->companyName = $data['companyname'];
	$reg->postalCode = $data['postcode'];
	$reg->city = $data['city'];
	$reg->street = $address[0];
	$reg->number = $address[1];
	$reg->country = $data['country'];
	$reg->phoneNumber = $data['phonenumber'];
	$reg->email = $data['email'];
	$adminAddress = transip_splitAddress( $data['adminaddress1'] );
	$admin = new Transip_WhoisContact();
	$admin->type = "administrative";
	$admin->firstName = $data['adminfirstname'];
	$admin->lastName = $data['adminlastname'];
	$admin->companyName = $data['admincompanyname'];
	$admin->postalCode = $data['adminpostcode'];
	$admin->city = $data['admincity'];
	$admin->street = $adminAddress[0];
	$admin->number = $adminAddress[1];
	$admin->country = $data['admincountry'];
	$admin->phoneNumber = $data['adminphonenumber'];
	$admin->email = $data['adminemail'];
	$techAddress = transip_splitAddress( $data['adminaddress1'] );
	$tech = new Transip_WhoisContact();
	$tech->type = "technical";
	$tech->firstName = $data['adminfirstname'];
	$tech->lastName = $data['adminlastname'];
	$tech->companyName = $data['admincompanyname'];
	$tech->postalCode = $data['adminpostcode'];
	$tech->city = $data['admincity'];
	$tech->street = $techAddress[0];
	$tech->number = $techAddress[1];
	$tech->country = $data['admincountry'];
	$tech->phoneNumber = $data['adminphonenumber'];
	$tech->email = $data['adminemail'];
	return array( $reg, $admin, $tech );
}


/**
 *
 *
 * @param Transip_WhoisContact[] $contacts
 *
 * @return array
 */
function transip_whoisContactsToContactDetails($contacts) {
	$contactTypeMapping = transip_getContactTypeMapping();
	$contactFieldMapping = transip_getContactFieldMapping();
	$contactDetails = array();
	foreach ($contacts as $whmcsType) {

		if (!isset( $contactTypeMapping[$contact->type] )) {
			continue;
		}

		$contactTypeMapping[$contact->type];

		if (isset( $contactDetails[$whmcsType] )) {
			continue;
		}

		$details = array();
		foreach ($contactFieldMapping as $transipField => $whmcsField) {
			$details[$whmcsField] = $contact->$transipField;
		}

		$contactDetails[$whmcsType] = $details;
	}

	return $contactDetails;
}


/**
 *
 *
 * @param array   $contactDetails
 *
 * @return array
 */
function transip_contactDetailsToWhoisContacts($contactDetails) {
	$contactTypeMapping = transip_getContactTypeMapping();
	$contactFieldMapping = transip_getContactFieldMapping();
	$contacts = array();
	foreach ($contactTypeMapping as $transipType => $whmcsType) {

		if (isset( $contactDetails[$whmcsType] )) {
			$contact = new Transip_WhoisContact();
			$contact->type = $transipType;
			foreach ($contactFieldMapping as $transipField => $whmcsField) {

				if (isset( $contactDetails[$whmcsType][$whmcsField] )) {
					$contact->$transipField = $contactDetails[$whmcsType][$whmcsField];
					continue;
				}
			}

			$contacts[] = $contact;
			continue;
		}
	}

	return $contacts;
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_GetNameservers($params) {
	transip_initialize( $params );
	$domain = null;
	$domainName = $params['sld'] . "." . $params['tld'];
	$domain = Transip_DomainService::getinfo( $domainName );
	$nameservers = array();
	foreach ($domain->nameservers as $index => $nameserver) {
		$nameservers["ns" . ( $index + 1 )] = $nameserver->hostname;
	}

	logModuleCall( "transip", "GetNameservers", $domainName, $domain, null );
	return $nameservers;
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_SaveNameservers($params) {
	transip_initialize( $params );
	$domainName = $params['sld'] . "." . $params['tld'];
	$nameservers = array();
	foreach ($params as $key => $value) {

		if (preg_match( "/^ns([0-9]+)$/", $key, $matches ) && !empty( $value )) {
			$nameservers[$matches[1] - 1] = new Transip_Nameserver( $value );
			continue;
		}
	}

	Transip_DomainService::setnameservers( $domainName, $nameservers );
	logModuleCall( "transip", "SaveNameservers", array( $domainName, $nameservers ), null );
	return array();
}


/**
 *
 *
 * @param array   $params
 *
 * @return array|string
 */
function transip_GetRegistrarLock($params) {
	transip_initialize( $params );
	$domainName = $params['sld'] . "." . $params['tld'];
	$isLocked = Transip_DomainService::getislocked( $domainName );
	$result = ($isLocked ? "locked" : "unlocked");
	logModuleCall( "transip", "GetRegistrarLock", $domainName, $isLocked, $result );
	return $result;
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_SaveRegistrarLock($params) {
	transip_initialize( $params );
	$domainName = $params['sld'] . "." . $params['tld'];

	if ($params['lockenabled'] == "locked") {
		logModuleCall( "transip", "SaveRegistrarLock", array( "setLock()", $domainName ) );
		Transip_DomainService::setlock( $domainName );
	}
	else {
		logModuleCall( "transip", "SaveRegistrarLock", array( "unsetLock()", $domainName ) );
		Transip_DomainService::unsetlock( $domainName );
	}

	return array();
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_GetDNS($params) {
	transip_initialize( $params );
	$domainName = $params['sld'] . "." . $params['tld'];
	$domain = Transip_DomainService::getinfo( $domainName );
	$records = array();
	foreach ($domain->dnsEntries as $dnsEntry) {
		$records[] = array( "hostname" => $dnsEntry->name, "type" => $dnsEntry->type, "address" => $dnsEntry->content );
	}

	logModuleCall( "transip", "GetDNS", $domainName, (array)$domain, $records );
	return $records;
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_SaveDNS($params) {
	transip_initialize( $params );
	$domainName = $params['sld'] . "." . $params['tld'];
	$dnsEntries = array();
	foreach ($params['dnsrecords'] as $dnsRecord) {

		if (empty( $dnsRecord['hostname'] ) || empty( $dnsRecord['address'] )) {
			continue;
		}

		$dnsEntries[] = new Transip_DnsEntry( $dnsRecord['hostname'], 86400, $dnsRecord['type'], $dnsRecord['address'] );
	}

	logModuleCall( "transip", "SaveDNS", array( $domainName, $dnsEntries ) );
	Transip_DomainService::setdnsentries( $domainName, $dnsEntries );
	return array();
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_RegisterDomain($params) {
	transip_initialize( $params );
	$domain = new Transip_Domain( $params['sld'] . "." . $params['tld'] );
	$domain->nameservers = array();
	foreach ($params as $key => $value) {

		if (preg_match( "/^ns([0-9]+)$/", $key, $matches ) && !empty( $value )) {
			$domain->nameservers[$matches[1] - 1] = new Transip_Nameserver( $value );
			continue;
		}
	}

	$domain->contacts = transip_getContactsForRegisterAndTransfer( $params );
	logModuleCall( "transip", "RegisterDomain", $domain, null );
	Transip_DomainService::register( $domain );
	return array();
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_TransferDomain($params) {
	transip_initialize( $params );
	$domain = new Transip_Domain( $params['sld'] . "." . $params['tld'] );
	$domain->nameservers = array();
	foreach ($params as $key => $value) {

		if (preg_match( "/^ns([0-9]+)$/", $key, $matches ) && !empty( $value )) {
			$domain->nameservers[$matches[1] - 1] = new Transip_Nameserver( $value );
			continue;
		}
	}

	$domain->contacts = transip_getContactsForRegisterAndTransfer( $params );
	$authCode = $params['transfersecret'];
	logModuleCall( "transip", "TransferDomain", array( $domain, $authCode ) );

	if (preg_match( "/\.(\w+)$/", $domain->name, $matches )) {
		switch ($matches[1]) {
		case "be": {
			}

		case "eu": {
			}

		case "nl": {
				Transip_DomainService::transferwithoutownerchange( $domain, $authCode );
				break;
			}

		default: {
				Transip_DomainService::transferwithownerchange( $domain, $authCode );
			}
		}
	}

	return array();
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_RenewDomain($params) {
	return array();
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_GetContactDetails($params) {
	transip_initialize( $params );
	$domainName = $params['sld'] . "." . $params['tld'];
	$domain = Transip_DomainService::getinfo( $domainName );
	$result = transip_whoisContactsToContactDetails( $domain->contacts );
	logModuleCall( "transip", "GetContactDetails", $domainName, (array)$domain, $result );
	return $result;
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_SaveContactDetails($params) {
	transip_initialize( $params );
	$domainName = $params['sld'] . "." . $params['tld'];
	$contacts = transip_contactDetailsToWhoisContacts( $params['contactdetails'] );
	logModuleCall( "transip", "SaveContactDetails", array( $domainName, $contacts ) );
	Transip_DomainService::setcontacts( $domainName, $contacts );
	return array();
}


/**
 *
 *
 * @param array   $params
 *
 * @return array
 */
function transip_GetEPPCode($params) {
	transip_initialize( $params );
	$domainName = $params['sld'] . "." . $params['tld'];
	$authCode = array( "eppcode" => Transip_DomainService::getauthcode( $domainName ) );
	logModuleCall( "transip", "GetEPPCode", $domainName, $authCode );
	return $authCode;
}


require_once "Transip/DomainService.php";
?>