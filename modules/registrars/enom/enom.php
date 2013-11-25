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

class CEnomInterface {
	var $PostString = null;
	var $RawData = null;
	var $Values = null;

	function NewRequest() {
		$this->PostString = "";
		$this->RawData = "";
		$this->Values = "";
	}


	function AddError($error) {
		$this->Values["ErrCount"] = "1";
		$this->Values["Err1"] = $error;
	}


	function ParseResponse($buffer) {
		$Lines = explode( "
", $buffer );
		$NumLines = count( $Lines );
		$i = 7;

		while (!trim( $Lines[$i] )) {
			$i = $i + 1;
		}

		$StartLine = $i;
		$GotValues = 7;
		$i = $StartLine;

		while ($i < $NumLines) {
			if (substr( $Lines[$i], 1, 1 ) != ";") {
				$Result = explode( "=", $Lines[$i] );

				if (2 <= count( $Result )) {
					$name = trim( $Result[0] );
					$value = trim( $Result[1] );

					if ($name == "ApproverEmail") {
						$this->Values[$name][] = $value;
					}
					else {
						$this->Values[$name] = $value;
					}


					if ($name == "ErrCount") {
						$GotValues = 8;
					}
				}
			}

			++$i;
		}


		if ($GotValues == 0) {
			$this->AddError( "Could not connect to Server - Please try again later" );
		}

	}


	function AddParam($Name, $Value) {
		$this->PostString = $this->PostString . $Name . "=" . urlencode( $Value ) . "&";
	}


	function DoTransaction($params, $xml = false) {
		$Values = "";

		if ($params["TestMode"]) {
			$host = "resellertest.enom.com";
		}
		else {
			$host = "reseller.enom.com";
		}

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, "https://" . $host . "/interface.asp" );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->PostString );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
		$this->RawData = curl_exec( $ch );

		if (curl_error( $ch )) {
			$this->AddError( "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch ) );
		}

		curl_close( $ch );
		$action = explode( "command=", $this->PostString );
		$action = explode( "&", $action[1] );
		$action = $action[0];

		if (function_exists( "logModuleCall" )) {
			logModuleCall( "enom", $action, $this->PostString, $this->RawData, "", array( $params["Username"], $params["Password"] ) );
		}


		if ($xml) {
			return $this->RawData;
		}

		$this->ParseResponse( $this->RawData );
	}


}


function enom_getConfigArray() {
	$configarray = array( "Description" => array( "Type" => "System", "Value" => "Don't have an Enom Account yet? Get one here: <a href=\"http://go.whmcs.com/82/enom\" target=\"_blank\">www.whmcs.com/partners/enom</a>" ), "Username" => array( "Type" => "text", "Size" => "20", "Description" => "Enter your Enom Reseller Account Username here" ), "Password" => array( "Type" => "password", "Size" => "20", "Description" => "Enter your Enom Reseller Account Password here" ), "TestMode" => array( "Type" => "yesno" ), "DefaultNameservers" => array( "Type" => "yesno", "Description" => "Tick this box to use the default Enom nameservers for new domain registrations" ) );
	return $configarray;
}


function enom_GetNameservers($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "command", "getdns" );
	$Enom->DoTransaction( $params );
	$values = array();
	$i = 7;

	while ($i <= 12) {
		$values["ns" . $i] = $Enom->Values["DNS" . $i];
		++$i;
	}


	if ($Enom->Values["Err1"]) {
		$values["error"] = $Enom->Values["Err1"];
	}

	return $values;
}


function enom_SaveNameservers($params) {
	$Enom = new CEnomInterface();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "NS1", $params["ns1"] );
	$Enom->AddParam( "NS2", $params["ns2"] );
	$Enom->AddParam( "NS3", $params["ns3"] );
	$Enom->AddParam( "NS4", $params["ns4"] );
	$Enom->AddParam( "NS5", $params["ns5"] );
	$Enom->AddParam( "EndUserIP", $_SERVER["REMOTE_ADDR"] );
	$Enom->AddParam( "command", "modifyns" );
	$Enom->DoTransaction( $params );
	$values["error"] = $Enom->Values["Err1"];
	return $values;
}


function enom_GetRegistrarLock($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "command", "getreglock" );
	$Enom->DoTransaction( $params );

	if ($Enom->Values["ErrCount"] == "0") {
		$lock = $Enom->Values["RegLock"];

		if ($Enom->Values["IsLockable"] == "True") {
			if ($lock == "1") {
				$lockstatus = "locked";
			}
			else {
				$lockstatus = "unlocked";
			}
		}

		return $lockstatus;
	}

}


function enom_SaveRegistrarLock($params) {
	if ($params["lockenabled"] == "locked") {
		$lockstatus = "0";
	}
	else {
		$lockstatus = "1";
	}

	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "UnlockRegistrar", $lockstatus );
	$Enom->AddParam( "command", "setreglock" );
	$Enom->DoTransaction( $params );

	if ($Enom->Values["ErrCount"] != "0") {
		$values["error"] = $Enom->Values["Err1"];
	}

	return $values;
}


function enom_GetEmailForwarding($params) {
	$Enom = new CEnomInterface();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "command", "getforwarding" );
	$Enom->DoTransaction( $params );
	$counter = 7;

	while ($counter <= 100) {
		if ($Enom->Values["Username" . $counter]) {
			$values[$counter]["prefix"] = $Enom->Values["Username" . $counter];
			$values[$counter]["forwardto"] = $Enom->Values["ForwardTo" . $counter];
		}

		$counter += 7;
	}

	return $values;
}


function enom_SaveEmailForwarding($params) {
	$Enom = new CEnomInterface();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	foreach ($params["prefix"] as $key => $value) {
		$Enom->AddParam( "Address" . $key, $params["prefix"][$key] );
		$Enom->AddParam( "ForwardTo" . $key, $params["forwardto"][$key] );
	}

	$Enom->AddParam( "command", "forwarding" );
	$Enom->DoTransaction( $params );
	$values["error"] = $Enom->Values["Err1"];
	return $values;
}


function enom_GetDNS($params) {

	$Enom = new CEnomInterface();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "command", "gethosts" );
	$Enom->AddParam( "ResponseType", "XML" );
	$Enom->DoTransaction( $params, true );
	$xmldata = new SimpleXMLElement($Enom);
	$arraydata = XMLtoArray( $xmldata );
	foreach ($arraydata["INTERFACE-RESPONSE"] as $k => $values) {

		if (substr( $k, 0, 4 ) == "HOST") {
			$hostrecords[] = array( "hostname" => $values["NAME"], "type" => $values["TYPE"], "address" => $values["ADDRESS"], "priority" => $values["MXPREF"] );
			continue;
		}
	}

	return $hostrecords;
}


function enom_SaveDNS($params) {
	foreach ($params["dnsrecords"] as $key => $values) {

		if (( $values && $values["address"] )) {
			++$key;
			$newvalues["HostName" . $key] = $values["hostname"];
			$newvalues["RecordType" . $key] = $values["type"];
			$newvalues["Address" . $key] = $values["address"];

			if ($values["type"] == "MX") {
				$newvalues["MXPref" . $key] = $values["priority"];
				continue;
			}

			continue;
		}
	}

	$Enom = new CEnomInterface();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	foreach ($newvalues as $key => $value) {
		$Enom->AddParam( $key, $value );
	}

	$Enom->AddParam( "command", "sethosts" );
	$Enom->DoTransaction( $params );
	$values["error"] = $Enom->Values["Err1"];
	return $values;
}


function enom_RegisterDomain($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "numyears", $params["regperiod"] );
	$Enom->AddParam( "NS1", $params["ns1"] );
	$Enom->AddParam( "NS2", $params["ns2"] );
	$Enom->AddParam( "NS3", $params["ns3"] );
	$Enom->AddParam( "NS4", $params["ns4"] );
	$Enom->AddParam( "NS5", $params["ns5"] );
	$Enom->AddParam( "IgnoreNSFail", "Yes" );
	$Enom->AddParam( "EmailNotify", "1" );

	if ($params["DefaultNameservers"]) {
		$Enom->AddParam( "UseDNS", "default" );
	}


	if ($params["companyname"]) {
		$jobtitle = "Director";
	}

	$Enom->AddParam( "RegistrantFirstName", $params["firstname"] );
	$Enom->AddParam( "RegistrantLastName", $params["lastname"] );
	$Enom->AddParam( "RegistrantOrganizationName", $params["companyname"] );
	$Enom->AddParam( "RegistrantJobTitle", $jobtitle );
	$Enom->AddParam( "RegistrantAddress1", $params["address1"] );
	$Enom->AddParam( "RegistrantAddress2", $params["address2"] );
	$Enom->AddParam( "RegistrantCity", $params["city"] );
	$Enom->AddParam( "RegistrantStateProvince", $params["state"] );
	$Enom->AddParam( "RegistrantPostalCode", $params["postcode"] );
	$Enom->AddParam( "RegistrantCountry", $params["country"] );
	$Enom->AddParam( "RegistrantEmailAddress", $params["email"] );
	$Enom->AddParam( "RegistrantPhone", $params["fullphonenumber"] );
	$Enom->AddParam( "RegistrantStateProvinceChoice", "P" );

	if (preg_match( "/ca$/i", $params["tld"] )) {
		$contacttypes = array( "Admin", "Tech", "AuxBilling" );
		foreach ($contacttypes as $contacttype) {
			$Enom->AddParam( $contacttype . "FirstName", $params["firstname"] );
			$Enom->AddParam( $contacttype . "LastName", $params["lastname"] );
			$Enom->AddParam( $contacttype . "OrganizationName", $params["companyname"] );
			$Enom->AddParam( $contacttype . "JobTitle", $jobtitle );
			$Enom->AddParam( $contacttype . "Address1", $params["address1"] );
			$Enom->AddParam( $contacttype . "Address2", $params["address2"] );
			$Enom->AddParam( $contacttype . "City", $params["city"] );
			$Enom->AddParam( $contacttype . "StateProvince", $params["state"] );
			$Enom->AddParam( $contacttype . "PostalCode", $params["postcode"] );
			$Enom->AddParam( $contacttype . "Country", $params["country"] );
			$Enom->AddParam( $contacttype . "EmailAddress", $params["email"] );
			$Enom->AddParam( $contacttype . "Phone", $params["fullphonenumber"] );
		}
	}
	else {
		if (!preg_match( "/uk$/i", $params["tld"] )) {
			$contacttypes = array( "Admin", "Tech", "AuxBilling" );
			foreach ($contacttypes as $contacttype) {
				$Enom->AddParam( $contacttype . "FirstName", $params["adminfirstname"] );
				$Enom->AddParam( $contacttype . "LastName", $params["adminlastname"] );
				$Enom->AddParam( $contacttype . "OrganizationName", $params["admincompanyname"] );
				$Enom->AddParam( $contacttype . "JobTitle", $jobtitle );
				$Enom->AddParam( $contacttype . "Address1", $params["adminaddress1"] );
				$Enom->AddParam( $contacttype . "Address2", $params["adminaddress2"] );
				$Enom->AddParam( $contacttype . "City", $params["admincity"] );
				$Enom->AddParam( $contacttype . "StateProvince", $params["adminstate"] );
				$Enom->AddParam( $contacttype . "PostalCode", $params["adminpostcode"] );
				$Enom->AddParam( $contacttype . "Country", $params["admincountry"] );
				$Enom->AddParam( $contacttype . "EmailAddress", $params["adminemail"] );
				$Enom->AddParam( $contacttype . "Phone", $params["adminfullphonenumber"] );
			}
		}
	}


	if (preg_match( "/us$/i", $params["tld"] )) {
		$nexus = $params["additionalfields"]["Nexus Category"];
		$countrycode = $params["additionalfields"]["Nexus Country"];
		$purpose = $params["additionalfields"]["Application Purpose"];

		if ($purpose == "Business use for profit") {
			$purpose = "P1";
		}
		else {
			if ($purpose == "Non-profit business") {
				$purpose = "P2";
			}
			else {
				if ($purpose == "Club") {
					$purpose = "P2";
				}
				else {
					if ($purpose == "Association") {
						$purpose = "P2";
					}
					else {
						if ($purpose == "Religious Organization") {
							$purpose = "P2";
						}
						else {
							if ($purpose == "Personal Use") {
								$purpose = "P3";
							}
							else {
								if ($purpose == "Educational purposes") {
									$purpose = "P4";
								}
								else {
									if ($purpose == "Government purposes") {
										$purpose = "P5";
									}
								}
							}
						}
					}
				}
			}
		}

		switch ($nexus) {
		case "C11": {
			}

		case "C12": {
			}

		case "C21": {
				$Enom->AddParam( "us_nexus", $nexus );
				break;
			}

		case "C31": {
			}

		case "C32": {
				$Enom->AddParam( "us_nexus", $nexus );
				$Enom->AddParam( "global_cc_us", $countrycode );
			}
		}

		$Enom->AddParam( "us_purpose", $purpose );
	}


	if (preg_match( "/uk$/i", $params["tld"] )) {
		if ($params["additionalfields"]["Legal Type"] == "UK Limited Company") {
			$uklegaltype = "LTD";
		}
		else {
			if ($params["additionalfields"]["Legal Type"] == "UK Public Limited Company") {
				$uklegaltype = "PLC";
			}
			else {
				if ($params["additionalfields"]["Legal Type"] == "UK Partnership") {
					$uklegaltype = "PTNR";
				}
				else {
					if ($params["additionalfields"]["Legal Type"] == "UK Limited Liability Partnership") {
						$uklegaltype = "LLP";
					}
					else {
						if ($params["additionalfields"]["Legal Type"] == "Sole Trader") {
							$uklegaltype = "STRA";
						}
						else {
							if ($params["additionalfields"]["Legal Type"] == "UK Registered Charity") {
								$uklegaltype = "RCHAR";
							}
							else {
								$uklegaltype = "IND";
							}
						}
					}
				}
			}
		}

		$ukregoptout = "n";

		if (( $params["additionalfields"]["WHOIS Opt-out"] && $uklegaltype == "IND" )) {
			$ukregoptout = "y";
		}

		$Enom->AddParam( "uk_legal_type", $uklegaltype );
		$Enom->AddParam( "uk_reg_co_no", strtoupper( $params["additionalfields"]["Company ID Number"] ) );
		$Enom->AddParam( "registered_for", $params["additionalfields"]["Registrant Name"] );
		$Enom->AddParam( "uk_reg_opt_out", $ukregoptout );
	}


	if (preg_match( "/ca$/i", $params["tld"] )) {
		if ($params["additionalfields"]["Legal Type"] == "Corporation") {
			$legaltype = "CCO";
		}
		else {
			if ($params["additionalfields"]["Legal Type"] == "Canadian Citizen") {
				$legaltype = "CCT";
			}
			else {
				if ($params["additionalfields"]["Legal Type"] == "Permanent Resident of Canada") {
					$legaltype = "RES";
				}
				else {
					if ($params["additionalfields"]["Legal Type"] == "Government") {
						$legaltype = "GOV";
					}
					else {
						if ($params["additionalfields"]["Legal Type"] == "Canadian Educational Institution") {
							$legaltype = "EDU";
						}
						else {
							if ($params["additionalfields"]["Legal Type"] == "Canadian Unincorporated Association") {
								$legaltype = "ASS";
							}
							else {
								if ($params["additionalfields"]["Legal Type"] == "Canadian Hospital") {
									$legaltype = "HOP";
								}
								else {
									if ($params["additionalfields"]["Legal Type"] == "Partnership Registered in Canada") {
										$legaltype = "PRT";
									}
									else {
										if ($params["additionalfields"]["Legal Type"] == "Trade-mark registered in Canada") {
											$legaltype = "TDM";
										}
										else {
											$legaltype = "CCO";
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$whoisoptout = "FULL";

		if (( $params["additionalfields"]["WHOIS Opt-out"] && ( $legaltype == "CCT" || $legaltype == "RES" ) )) {
			$whoisoptout = "PRIVATE";
		}

		$ciraagreement = "N";

		if ($params["additionalfields"]["CIRA Agreement"]) {
			$ciraagreement = "Y";
		}

		$Enom->AddParam( "cira_legal_type", $legaltype );
		$Enom->AddParam( "cira_whois_display", $whoisoptout );
		$Enom->AddParam( "cira_language", "en" );
		$Enom->AddParam( "cira_agreement_version", "2.0" );
		$Enom->AddParam( "cira_agreement_value", $ciraagreement );

		if ($ciraagreement == "N") {
			return array( "error" => "The CIRA Agreement must be agreed to by the customer before the domain can be registered" );
		}
	}


	if (preg_match( "/eu$/i", $params["tld"] )) {
		$Enom->AddParam( "eu_whoispolicy", "I AGREE" );
		$Enom->AddParam( "eu_agreedelete", "YES" );
		$Enom->AddParam( "eu_adr_lang", "EN" );
	}


	if (preg_match( "/it$/i", $params["tld"] )) {
		$Enom->AddParam( "it_consentforpublishing", ($params["additionalfields"]["Publish Personal Data"] ? "1" : "0") );
		$Enom->AddParam( "it_personal_data_for_reg", ($params["additionalfields"]["Consent for Processing of Information"] ? "1" : "0") );
		$Enom->AddParam( "it_datafordiffusion", ($params["additionalfields"]["Consent for Dissemination and Accessibility via the Internet"] ? "1" : "0") );
		$Enom->AddParam( "it_agreedelete", "YES" );
		$Enom->AddParam( "it_sect3_liability", ($params["additionalfields"]["Accept Section 3 of .IT registrar contract"] ? "1" : "0") );
		$Enom->AddParam( "it_explicit_acceptance", ($params["additionalfields"]["Explicit Acceptance of Registry Terms"] ? "1" : "0") );
		$Enom->AddParam( "it_pin", $params["additionalfields"]["Tax ID"] );
		$Enom->AddParam( "it_entity_type", substr( $params["additionalfields"]["Type of Registrant Entity"], 0, 1 ) );
	}


	if (preg_match( "/de$/i", $params["tld"] )) {
		$Enom->AddParam( "confirmaddress", "DE" );
		$Enom->AddParam( "de_agreedelete", "YES" );
	}


	if (preg_match( "/nl$/i", $params["tld"] )) {
		$Enom->AddParam( "nl_agreedelete", "YES" );
	}


	if (preg_match( "/fm$/i", $params["tld"] )) {
		$Enom->AddParam( "fm_agreedelete", "YES" );
	}


	if (preg_match( "/be$/i", $params["tld"] )) {
		$Enom->AddParam( "be_agreedelete", "YES" );
	}


	if (preg_match( "/nz$/i", $params["tld"] )) {
		$Enom->AddParam( "co.nz_agreedelete", "YES" );
	}


	if (preg_match( "/tel$/i", $params["tld"] )) {
		$telregoptout = "NO";

		if ($params["additionalfields"]["Registrant Type"] == "Legal Person") {
			$regtype = "legal_person";
		}
		else {
			$regtype = "natural_person";

			if ($params["additionalfields"]["WHOIS Opt-out"]) {
				$telregoptout = "YES";
			}
		}

		$telpw = "";
		$length = 19;
		$seeds = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVYWXYZ";
		$seeds_count = strlen( $seeds ) - 1;
		$i = 9;

		while ($i < $length) {
			$telpw .= $seeds[rand( 0, $seeds_count )];
			++$i;
		}


		if (is_numeric( substr( $telpw, 0, 1 ) )) {
			$telpw = "a" . $telpw;
		}

		$Enom->AddParam( "tel_whoistype", $regtype );
		$Enom->AddParam( "tel_publishwhois", $telregoptout );
		$Enom->AddParam( "tel_username", strtolower( $params["firstname"] . $params["lastname"] . $params["domainid"] ) );
		$Enom->AddParam( "tel_password", $telpw );
		$Enom->AddParam( "tel_emailaddress", $params["email"] );
	}


	if (preg_match( "/pro$/i", $params["tld"] )) {
		$Enom->AddParam( "pro_profession", $params["additionalfields"]["Profession"] );
	}


	if (preg_match( "/es$/i", $params["tld"] )) {
		$params["additionalfields"]["ID Form Type"] = explode( "|", $params["additionalfields"]["ID Form Type"] );
		$idtype = $params["additionalfields"]["ID Form Type"][0];
		$Enom->AddParam( "es_registrantidtype", $idtype );
		$Enom->AddParam( "es_registrantid", $params["additionalfields"]["ID Form Number"] );
	}


	if (preg_match( "/au$/i", $params["tld"] )) {
		$idtype = $params["additionalfields"]["Registrant ID Type"];

		if ($idtype == "Business Registration Number") {
			$idtype = "RBN";
		}

		$idnumber = ($params["additionalfields"]["Eligibility ID"] ? $params["additionalfields"]["Eligibility ID"] : $params["additionalfields"]["Registrant ID"]);
		$Enom->AddParam( "au_registrantidtype", $idtype );
		$Enom->AddParam( "au_registrantid", $params["additionalfields"]["ID Form Number"] );
	}


	if (preg_match( "/sg$/i", $params["tld"] )) {
		$idnumber = $params["additionalfields"]["RCB Singapore ID"];
		$Enom->AddParam( "sg_rcbid", $idnumber );
	}

	$Enom->AddParam( "command", "purchase" );
	$Enom->DoTransaction( $params );
	$values["error"] = $Enom->Values["Err1"];

	if (( !$values["error"] && $Enom->Values["RRPCode"] != "200" )) {
		$values["error"] = $Enom->Values["RRPText"];
	}


	if (( $params["idprotection"] && !$values["error"] )) {
		$Enom->NewRequest();
		$Enom->AddParam( "uid", $params["Username"] );
		$Enom->AddParam( "pw", $params["Password"] );
		$Enom->AddParam( "ProductType", "IDProtect" );
		$Enom->AddParam( "TLD", $params["tld"] );
		$Enom->AddParam( "SLD", $params["sld"] );
		$Enom->AddParam( "Quantity", $params["regperiod"] );
		$Enom->AddParam( "ClearItems", "yes" );
		$Enom->AddParam( "command", "AddToCart" );
		$Enom->DoTransaction( $params );
		$Enom->NewRequest();
		$Enom->AddParam( "uid", $params["Username"] );
		$Enom->AddParam( "pw", $params["Password"] );
		$Enom->AddParam( "command", "InsertNewOrder" );
		$Enom->DoTransaction( $params );
	}

	return $values;
}


function enom_TransferDomain($params) {
	if ($params["companyname"]) {
		$jobtitle = "Director";
	}

	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "DomainCount", "1" );
	$Enom->AddParam( "OrderType", "Autoverification" );
	$Enom->AddParam( "TLD1", $params["tld"] );
	$Enom->AddParam( "SLD1", $params["sld"] );
	$Enom->AddParam( "AuthInfo1", $params["transfersecret"] );
	$Enom->AddParam( "UseContacts", "1" );
	$Enom->AddParam( "Lock", "1" );
	$Enom->AddParam( "EmailNotify", "1" );

	if (( preg_match( "/eu$/i", $params["tld"] ) || preg_match( "/ca$/i", $params["tld"] ) )) {
		$Enom->AddParam( "RegistrantFirstName", $params["firstname"] );
		$Enom->AddParam( "RegistrantLastName", $params["lastname"] );
		$Enom->AddParam( "RegistrantOrganizationName", $params["companyname"] );
		$Enom->AddParam( "RegistrantJobTitle", $jobtitle );
		$Enom->AddParam( "RegistrantAddress1", $params["address1"] );
		$Enom->AddParam( "RegistrantAddress2", $params["address2"] );
		$Enom->AddParam( "RegistrantCity", $params["city"] );
		$Enom->AddParam( "RegistrantStateProvince", $params["state"] );
		$Enom->AddParam( "RegistrantPostalCode", $params["postcode"] );
		$Enom->AddParam( "RegistrantCountry", $params["country"] );
		$Enom->AddParam( "RegistrantEmailAddress", $params["email"] );
		$Enom->AddParam( "RegistrantPhone", $params["fullphonenumber"] );
		$Enom->AddParam( "eu_whoispolicy", "I AGREE" );
		$Enom->AddParam( "eu_agreedelete", "YES" );
		$Enom->AddParam( "eu_adr_lang", "EN" );
	}


	if (preg_match( "/it$/i", $params["tld"] )) {
		$Enom->AddParam( "it_agreedelete", "YES" );
	}


	if (preg_match( "/de$/i", $params["tld"] )) {
		$Enom->AddParam( "confirmaddress", "DE" );
		$Enom->AddParam( "de_agreedelete", "YES" );
	}


	if (preg_match( "/nl$/i", $params["tld"] )) {
		$Enom->AddParam( "nl_agreedelete", "YES" );
	}


	if (preg_match( "/fm$/i", $params["tld"] )) {
		$Enom->AddParam( "fm_agreedelete", "YES" );
	}

	$Enom->AddParam( "command", "TP_CreateOrder" );
	$Enom->DoTransaction( $params );
	$values["error"] = $Enom->Values["Err1"];
	return $values;
}


function enom_RenewDomain($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "command", "getcontacts" );
	$Enom->DoTransaction( $params );
	$nNumErrors = $Enom->Values["ErrCount"];

	if (0 < $nNumErrors) {
		$errormessage = "An Error Occurred";
	}
	else {
		$RegistrantOrganizationName = $Enom->Values["RegistrantOrganizationName"];
		$RegistrantFirstName = $Enom->Values["RegistrantFirstName"];
		$RegistrantLastName = $Enom->Values["RegistrantLastName"];
		$RegistrantAddress1 = $Enom->Values["RegistrantAddress1"];
		$RegistrantCity = $Enom->Values["RegistrantCity"];
		$RegistrantEmailAddress = $Enom->Values["RegistrantEmailAddress"];
		$RegistrantPostalCode = $Enom->Values["RegistrantPostalCode"];
	}

	$result = select_query( "tbldomains", "expirydate", array( "id" => $params["domainid"] ) );
	$data = mysql_fetch_array( $result );
	$expirydate = $data["expirydate"];
	$expirydate = str_replace( "-", "", $expirydate );

	if (( $expirydate == "00000000" || date( "Ymd" ) <= $expirydate )) {
		$Enom->NewRequest();
		$Enom->AddParam( "uid", $params["Username"] );
		$Enom->AddParam( "pw", $params["Password"] );
		$Enom->AddParam( "tld", $params["tld"] );
		$Enom->AddParam( "sld", $params["sld"] );
		$Enom->AddParam( "NumYears", $params["regperiod"] );
		$Enom->AddParam( "OverrideOrder", 0 );
		$Enom->AddParam( "RegistrantEmailAddress", $RegistrantEmailAddress );
		$Enom->AddParam( "RegistrantCity", $RegistrantCity );
		$Enom->AddParam( "RegistrantAddress1", $RegistrantAddress1 );
		$Enom->AddParam( "RegistrantLastName", $RegistrantLastName );
		$Enom->AddParam( "RegistrantFirstName", $RegistrantFirstName );
		$Enom->AddParam( "RegistrantOrganizationName", $RegistrantOrganizationName );
		$Enom->AddParam( "RegistrantPostalCode", $RegistrantPostalCode );
		$Enom->AddParam( "EndUserIP", $_SERVER["REMOTE_ADDR"] );
		$Enom->AddParam( "command", "extend" );
		$Enom->DoTransaction( $params );
	}
	else {
		$Enom->NewRequest();
		$Enom->AddParam( "uid", $params["Username"] );
		$Enom->AddParam( "pw", $params["Password"] );
		$Enom->AddParam( "DomainName", $params["sld"] . "." . $params["tld"] );
		$Enom->AddParam( "NumYears", $params["regperiod"] );
		$Enom->AddParam( "command", "UpdateExpiredDomains" );
		$Enom->DoTransaction( $params );
	}

	$values["error"] = $Enom->Values["Err1"];

	if (( $params["idprotection"] && !$values["error"] )) {
		$Enom->NewRequest();
		$Enom->AddParam( "uid", $params["Username"] );
		$Enom->AddParam( "pw", $params["Password"] );
		$Enom->AddParam( "ProductType", "IDProtectRenewal" );
		$Enom->AddParam( "TLD", $params["tld"] );
		$Enom->AddParam( "SLD", $params["sld"] );
		$Enom->AddParam( "Quantity", $params["regperiod"] );
		$Enom->AddParam( "ClearItems", "yes" );
		$Enom->AddParam( "command", "AddToCart" );
		$Enom->DoTransaction( $params );
		$Enom->NewRequest();
		$Enom->AddParam( "uid", $params["Username"] );
		$Enom->AddParam( "pw", $params["Password"] );
		$Enom->AddParam( "command", "InsertNewOrder" );
		$Enom->DoTransaction( $params );
	}

	return $values;
}


function enom_GetContactDetails($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "command", "getcontacts" );
	$Enom->DoTransaction( $params );
	$contacttypes = array( "Registrant", "Admin", "Tech" );
	$i = 6;

	while ($i <= 2) {
		if (( ( $Enom->Values["RegistrantUpdatable"] == "False" || $Enom->Values["RegistrantUpdatable"] === False ) && $contacttypes[$i] == "Registrant" )) {
			continue;
		}

		$values[$contacttypes[$i]]["First Name"] = $Enom->Values[$contacttypes[$i] . "FirstName"];
		$values[$contacttypes[$i]]["Last Name"] = $Enom->Values[$contacttypes[$i] . "LastName"];
		$values[$contacttypes[$i]]["Organisation Name"] = $Enom->Values[$contacttypes[$i] . "OrganizationName"];
		$values[$contacttypes[$i]]["Job Title"] = $Enom->Values[$contacttypes[$i] . "JobTitle"];
		$values[$contacttypes[$i]]["Email"] = $Enom->Values[$contacttypes[$i] . "EmailAddress"];
		$values[$contacttypes[$i]]["Address 1"] = $Enom->Values[$contacttypes[$i] . "Address1"];
		$values[$contacttypes[$i]]["Address 2"] = $Enom->Values[$contacttypes[$i] . "Address2"];
		$values[$contacttypes[$i]]["City"] = $Enom->Values[$contacttypes[$i] . "City"];
		$values[$contacttypes[$i]]["State"] = $Enom->Values[$contacttypes[$i] . "StateProvince"];
		$values[$contacttypes[$i]]["Postcode"] = $Enom->Values[$contacttypes[$i] . "PostalCode"];
		$values[$contacttypes[$i]]["Country"] = $Enom->Values[$contacttypes[$i] . "Country"];
		$values[$contacttypes[$i]]["Phone"] = $Enom->Values[$contacttypes[$i] . "Phone"];
		$values[$contacttypes[$i]]["Fax"] = $Enom->Values[$contacttypes[$i] . "Fax"];
		++$i;
	}

	return $values;
}


function enom_SaveContactDetails($params) {
	require ROOTDIR . "/includes/countriescallingcodes.php";
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$contacttypes = array( "Registrant", "Admin", "Tech" );
	$i = 8;

	while ($i <= 2) {
		$phonenumber = $params["contactdetails"][$contacttypes[$i]]["Phone"];
		$country = $params["contactdetails"][$contacttypes[$i]]["Country"];
		$phoneprefix = $countrycallingcodes[$country];

		if (( substr( $phonenumber, 0, 1 ) != "+" && $phoneprefix )) {
			$params["contactdetails"][$contacttypes[$i]]["Phone"] = "+" . $phoneprefix . "." . $phonenumber;
		}

		$Enom->AddParam( $contacttypes[$i] . "Fax", $params["contactdetails"][$contacttypes[$i]]["Fax"] );
		$Enom->AddParam( $contacttypes[$i] . "Phone", $params["contactdetails"][$contacttypes[$i]]["Phone"] );
		$Enom->AddParam( $contacttypes[$i] . "Country", $params["contactdetails"][$contacttypes[$i]]["Country"] );
		$Enom->AddParam( $contacttypes[$i] . "PostalCode", $params["contactdetails"][$contacttypes[$i]]["Postcode"] );
		$Enom->AddParam( $contacttypes[$i] . "StateProvince", $params["contactdetails"][$contacttypes[$i]]["State"] );
		$Enom->AddParam( $contacttypes[$i] . "City", $params["contactdetails"][$contacttypes[$i]]["City"] );
		$Enom->AddParam( $contacttypes[$i] . "EmailAddress", $params["contactdetails"][$contacttypes[$i]]["Email"] );
		$Enom->AddParam( $contacttypes[$i] . "Address2", $params["contactdetails"][$contacttypes[$i]]["Address 2"] );
		$Enom->AddParam( $contacttypes[$i] . "Address1", $params["contactdetails"][$contacttypes[$i]]["Address 1"] );
		$Enom->AddParam( $contacttypes[$i] . "JobTitle", $params["contactdetails"][$contacttypes[$i]]["Job Title"] );
		$Enom->AddParam( $contacttypes[$i] . "LastName", $params["contactdetails"][$contacttypes[$i]]["Last Name"] );
		$Enom->AddParam( $contacttypes[$i] . "FirstName", $params["contactdetails"][$contacttypes[$i]]["First Name"] );
		$Enom->AddParam( $contacttypes[$i] . "OrganizationName", $params["contactdetails"][$contacttypes[$i]]["Organisation Name"] );
		++$i;
	}


	if (preg_match( "/us$/i", $params["tld"] )) {
		$nexus = $params["additionalfields"]["Nexus Category"];
		$countrycode = $params["additionalfields"]["Nexus Country"];
		$purpose = $params["additionalfields"]["Application Purpose"];

		if ($purpose == "Business use for profit") {
			$purpose = "P1";
		}
		else {
			if ($purpose == "Non-profit business") {
				$purpose = "P2";
			}
			else {
				if ($purpose == "Club") {
					$purpose = "P2";
				}
				else {
					if ($purpose == "Association") {
						$purpose = "P2";
					}
					else {
						if ($purpose == "Religious Organization") {
							$purpose = "P2";
						}
						else {
							if ($purpose == "Personal Use") {
								$purpose = "P3";
							}
							else {
								if ($purpose == "Educational purposes") {
									$purpose = "P4";
								}
								else {
									if ($purpose == "Government purposes") {
										$purpose = "P5";
									}
								}
							}
						}
					}
				}
			}
		}

		switch ($nexus) {
		case "C11": {
			}

		case "C12": {
			}

		case "C21": {
				$Enom->AddParam( "us_nexus", $nexus );
				break;
			}

		case "C31": {
			}

		case "C32": {
				$Enom->AddParam( "us_nexus", $nexus );
				$Enom->AddParam( "global_cc_us", $countrycode );
			}
		}

		$Enom->AddParam( "us_purpose", $purpose );
	}


	if (preg_match( "/uk$/i", $params["tld"] )) {
		if ($params["additionalfields"]["Legal Type"] == "UK Limited Company") {
			$uklegaltype = "LTD";
		}
		else {
			if ($params["additionalfields"]["Legal Type"] == "UK Public Limited Company") {
				$uklegaltype = "PLC";
			}
			else {
				if ($params["additionalfields"]["Legal Type"] == "UK Partnership") {
					$uklegaltype = "PTNR";
				}
				else {
					if ($params["additionalfields"]["Legal Type"] == "UK Limited Liability Partnership") {
						$uklegaltype = "LLP";
					}
					else {
						if ($params["additionalfields"]["Legal Type"] == "Sole Trader") {
							$uklegaltype = "STRA";
						}
						else {
							if ($params["additionalfields"]["Legal Type"] == "UK Registered Charity") {
								$uklegaltype = "RCHAR";
							}
							else {
								$uklegaltype = "IND";
							}
						}
					}
				}
			}
		}

		$Enom->AddParam( "uk_legal_type", $uklegaltype );
		$Enom->AddParam( "uk_reg_co_no", $params["additionalfields"]["Company ID Number"] );
		$Enom->AddParam( "registered_for", $params["additionalfields"]["Registrant Name"] );
	}


	if (preg_match( "/ca$/i", $params["tld"] )) {
		if ($params["additionalfields"]["Legal Type"] == "Corporation") {
			$legaltype = "CCO";
		}
		else {
			if ($params["additionalfields"]["Legal Type"] == "Canadian Citizen") {
				$legaltype = "CCT";
			}
			else {
				if ($params["additionalfields"]["Legal Type"] == "Permanent Resident of Canada") {
					$legaltype = "RES";
				}
				else {
					if ($params["additionalfields"]["Legal Type"] == "Government") {
						$legaltype = "GOV";
					}
					else {
						if ($params["additionalfields"]["Legal Type"] == "Canadian Educational Institution") {
							$legaltype = "EDU";
						}
						else {
							if ($params["additionalfields"]["Legal Type"] == "Canadian Unincorporated Association") {
								$legaltype = "ASS";
							}
							else {
								if ($params["additionalfields"]["Legal Type"] == "Canadian Hospital") {
									$legaltype = "HOP";
								}
								else {
									if ($params["additionalfields"]["Legal Type"] == "Partnership Registered in Canada") {
										$legaltype = "PRT";
									}
									else {
										if ($params["additionalfields"]["Legal Type"] == "Trade-mark registered in Canada") {
											$legaltype = "TDM";
										}
										else {
											$legaltype = "CCO";
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$whoisoptout = "FULL";

		if (( $params["additionalfields"]["WHOIS Opt-out"] && ( $legaltype == "CCT" || $legaltype == "RES" ) )) {
			$whoisoptout = "PRIVATE";
		}

		$Enom->AddParam( "cira_legal_type", $legaltype );
		$Enom->AddParam( "cira_whois_display", $whoisoptout );
		$Enom->AddParam( "cira_language", "en" );
		$Enom->AddParam( "cira_agreement_version", "2.0" );
		$Enom->AddParam( "cira_agreement_value", "Y" );
	}


	if (preg_match( "/eu$/i", $params["tld"] )) {
		$Enom->AddParam( "eu_whoispolicy", "I AGREE" );
		$Enom->AddParam( "eu_agreedelete", "YES" );
	}


	if (preg_match( "/it$/i", $params["tld"] )) {
		$Enom->AddParam( "it_agreedelete", "YES" );
	}


	if (preg_match( "/de$/i", $params["tld"] )) {
		$Enom->AddParam( "confirmaddress", "DE" );
		$Enom->AddParam( "de_agreedelete", "YES" );
	}


	if (preg_match( "/tel$/i", $params["tld"] )) {
		$telregoptout = "NO";

		if ($params["additionalfields"]["Registrant Type"] == "Legal Person") {
			$regtype = "legal_person";
		}
		else {
			$regtype = "natural_person";

			if ($params["additionalfields"]["WHOIS Opt-out"]) {
				$telregoptout = "YES";
			}
		}

		$telpw = "";
		$length = 18;
		$seeds = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVYWXYZ";
		$seeds_count = strlen( $seeds ) - 1;
		$i = 8;

		while ($i < $length) {
			$telpw .= $seeds[rand( 0, $seeds_count )];
			++$i;
		}

		$Enom->AddParam( "tel_whoistype", $regtype );
		$Enom->AddParam( "tel_publishwhois", $telregoptout );
		$Enom->AddParam( "tel_username", strtolower( $params["contactdetails"]["Registrant"]["First Name"] . $params["contactdetails"]["Registrant"]["Last Name"] . $params["domainid"] ) );
		$Enom->AddParam( "tel_password", $telpw );
		$Enom->AddParam( "tel_emailaddress", $params["contactdetails"]["Registrant"]["Email"] );
	}


	if (preg_match( "/pro$/i", $params["tld"] )) {
		$Enom->AddParam( "pro_profession", $params["additionalfields"]["Profession"] );
	}


	if (preg_match( "/es$/i", $params["tld"] )) {
		$params["additionalfields"]["ID Form Type"] = explode( "|", $params["additionalfields"]["ID Form Type"] );
		$idtype = $params["additionalfields"]["ID Form Type"][0];
		$Enom->AddParam( "es_registrantidtype", $idtype );
		$Enom->AddParam( "es_registrantid", $params["additionalfields"]["ID Form Number"] );
	}


	if (preg_match( "/sg$/i", $params["tld"] )) {
		$idnumber = $params["additionalfields"]["RCB Singapore ID"];
		$Enom->AddParam( "sg_rcbid", $idnumber );
	}

	$Enom->AddParam( "EndUserIP", $enduserip );
	$Enom->AddParam( "command", "contacts" );
	$Enom->DoTransaction( $params );
	$values["error"] = $Enom->Values["Err1"];
	return $values;
}


function enom_GetEPPCode($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "command", "SynchAuthInfo" );
	$Enom->AddParam( "EmailEPP", "True" );
	$Enom->AddParam( "RunSynchAutoInfo", "True" );
	$Enom->DoTransaction( $params );
	$values["error"] = $Enom->Values["Err1"];
	return $values;
}


function enom_RegisterNameserver($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "command", "RegisterNameServer" );
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "Add", "true" );
	$Enom->AddParam( "NSName", $params["nameserver"] );
	$Enom->AddParam( "IP", $params["ipaddress"] );
	$Enom->DoTransaction( $params );

	if ($Enom->Values["Err1"]) {
		$error = $Enom->Values["Err1"];
	}


	if ($Enom->Values["ResponseString1"]) {
		$error = $Enom->Values["ResponseString1"];
	}

	$values["error"] = $error;
	return $values;
}


function enom_ModifyNameserver($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "command", "UpdateNameServer" );
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "Add", "true" );
	$Enom->AddParam( "NS", $params["nameserver"] );
	$Enom->AddParam( "OldIP", $params["currentipaddress"] );
	$Enom->AddParam( "NewIP", $params["newipaddress"] );
	$Enom->DoTransaction( $params );

	if ($Enom->Values["Err1"]) {
		$error = $Enom->Values["Err1"];
	}


	if ($Enom->Values["ResponseString1"]) {
		$error = $Enom->Values["ResponseString1"];
	}

	$values["error"] = $error;
	return $values;
}


function enom_DeleteNameserver($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "command", "DeleteNameServer" );
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "Add", "true" );
	$Enom->AddParam( "NS", $params["nameserver"] );
	$Enom->DoTransaction( $params );

	if ($Enom->Values["Err1"]) {
		$error = $Enom->Values["Err1"];
	}


	if ($Enom->Values["ResponseString1"]) {
		$error = $Enom->Values["ResponseString1"];
	}

	$values["error"] = $error;
	return $values;
}


function enom_AdminCustomButtonArray($params) {
	$buttonarray = array();

	if ($params["regtype"] == "Transfer") {
		$buttonarray["Resend Transfer Approval Email"] = "resendtransferapproval";
		$buttonarray["Cancel Domain Transfer"] = "canceldomaintransfer";
	}

	return $buttonarray;
}


function enom_resendtransferapproval($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "command", "TP_CancelOrder" );
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->DoTransaction( $params );

	if (( $Enom->Values["Err1"] || $Enom->Values["ResponseString1"] )) {
		$values["error"] = $Enom->Values["Err1"];
	}
	else {
		$values["message"] = "Successfully resent the transfer approval email";
	}

	return $values;
}


function enom_getorderid($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "command", "StatusDomain" );
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->AddParam( "OrderType", "Transfer" );
	$Enom->DoTransaction( $params, true );

	if (( $Enom->Values["Err1"] || !$Enom->Values["OrderID"] )) {
		$errmsg = "Unable to Find Domain Order";

		if ($Enom->Values["Err1"]) {
			$errmsg .= " - " . $Enom->Values["Err1"];
		}

		return $errmsg;
	}

	return $Enom->Values["OrderID"];
}


function enom_canceldomaintransfer($params) {
	$orderid = enom_getorderid( $params );

	if (!is_numeric( $orderid )) {
		$values["error"] = $orderid;
		return $values;
	}

	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "command", "TP_CancelOrder" );
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "TransferOrderID", $orderid );
	$Enom->DoTransaction( $params );

	if (( $Enom->Values["Err1"] || $Enom->Values["ResponseString1"] )) {
		$values["error"] = $Enom->Values["Err1"];
	}
	else {
		$values["message"] = "Successfully cancelled the domain transfer";
	}

	return $values;
}


function enom_Sync($params) {
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "command", "GetDomainExp" );
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->DoTransaction( $params );
	$values = array();

	if (( $Enom->Values["Err1"] || $Enom->Values["ResponseString1"] )) {
		$values["error"] = $Enom->Values["Err1"];
	}
	else {
		$expirydate = $Enom->Values["ExpirationDate"];

		if ($expirydate) {
			$expirydate = explode( " ", $expirydate );
			$expirydate = explode( "/", $expirydate[0] );
			$day = $expirydate[1];
			$month = $expirydate[0];
			$year = $expirydate[2];
			$expirydate = $year . "-" . str_pad( $month, 2, "0", STR_PAD_LEFT ) . "-" . str_pad( $day, 2, "0", STR_PAD_LEFT );

			if (trim( $year )) {
				$values["status"] = "Active";
			}

			$values["expirydate"] = $expirydate;
		}
	}

	return $values;
}


function enom_TransferSync($params) {
	$cancelledstatusids = array( "2", "4", "6", "7", "8", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "30", "31", "32", "33", "34", "36", "37", "45" );
	$pendingstatusids = array( "0", "1", "3", "9", "10", "11", "12", "13", "14", "28", "29", "35" );
	$values = array();
	$Enom = new CEnomInterface();
	$Enom->NewRequest();
	$Enom->AddParam( "command", "TP_GetDetailsByDomain" );
	$Enom->AddParam( "uid", $params["Username"] );
	$Enom->AddParam( "pw", $params["Password"] );
	$Enom->AddParam( "sld", $params["sld"] );
	$Enom->AddParam( "tld", $params["tld"] );
	$Enom->DoTransaction( $params );

	if (( $Enom->Values["Err1"] || $Enom->Values["ResponseString1"] )) {
		$values["error"] = $Enom->Values["Err1"];
	}
	else {
		$count = $Enom->Values["ordercount"];
		$statusid = $Enom->Values["statusid" . $count];
		$statusdesc = $Enom->Values["statusdesc" . $count];

		if ($statusid == "5") {
			if ($params["idprotection"]) {
				$Enom->NewRequest();
				$Enom->AddParam( "uid", $params["Username"] );
				$Enom->AddParam( "pw", $params["Password"] );
				$Enom->AddParam( "ProductType", "IDProtect" );
				$Enom->AddParam( "TLD", $params["tld"] );
				$Enom->AddParam( "SLD", $params["sld"] );
				$Enom->AddParam( "Quantity", $params["regperiod"] );
				$Enom->AddParam( "ClearItems", "yes" );
				$Enom->AddParam( "command", "AddToCart" );
				$Enom->DoTransaction( $params );
				$Enom->NewRequest();
				$Enom->AddParam( "uid", $params["Username"] );
				$Enom->AddParam( "pw", $params["Password"] );
				$Enom->AddParam( "command", "InsertNewOrder" );
				$Enom->DoTransaction( $params );
			}

			$values["completed"] = true;
		}
		else {
			if (in_array( $statusid, $cancelledstatusids )) {
				$values["failed"] = true;
				$values["reason"] = $statusdesc;
			}
			else {
				if (in_array( $statusid, $pendingstatusids )) {
					$values["pendingtransfer"] = true;
					$values["reason"] = $statusdesc;
				}
			}
		}
	}

	return $values;
}


?>