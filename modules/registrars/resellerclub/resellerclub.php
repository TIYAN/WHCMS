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

function resellerclub_GetConfigArray() {
	$configarray = array( "Description" => array( "Type" => "System", "Value" => "Don't have a ResellerClub Account yet? Get one here: <a href=\"http://go.whmcs.com/86/resellerclub\" target=\"_blank\">www.whmcs.com/partners/resellerclub</a>" ), "ResellerID" => array( "Type" => "text", "Size" => "20", "Description" => "You can get this from the LogicBoxes Control Panel in Settings > Personal Information > Primary Profile" ), "APIKey" => array( "Type" => "password", "Size" => "20", "Description" => "Your API Key. You can get this from the LogicBoxes Control Panel in Settings -> API" ), "TestMode" => array( "Type" => "yesno" ) );
	return $configarray;
}


function resellerclub_GetNameservers($params) {
	if (!$params["ResellerID"]) {
		return array( "error" => "Missing Reseller ID. Please navigate to Setup > Domain Registrars to configure." );
	}


	if (!$params["APIKey"]) {
		return array( "error" => "Missing API Key. Please navigate to Setup > Domain Registrars to configure." );
	}

	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["options"] = "NsDetails";
	$result = resellerclub_SendCommand( "details", "domains", $postfields, $params, "GET" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	$x = 6;
	$x = 6;

	while ($x <= 5) {
		$values["ns" . $x] = $result["ns" . $x];
		++$x;
	}

	$values["error"] = $error;
	return $values;
}


function resellerclub_SaveNameservers($params) {
	if (!$params["ResellerID"]) {
		return array( "error" => "Missing Reseller ID. Please navigate to Setup > Domain Registrars to configure." );
	}


	if (!$params["APIKey"]) {
		return array( "error" => "Missing API Key. Please navigate to Setup > Domain Registrars to configure." );
	}

	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["options"] = "NsDetails";
	$nameserver1 = $params["ns1"];
	$nameserver2 = $params["ns2"];
	$nameserver3 = $params["ns3"];
	$nameserver4 = $params["ns4"];
	$nameserver5 = $params["ns5"];
	$nslist = "" . $nameserver1 . "&ns=" . $nameserver2;

	if ($nameserver3) {
		$nslist .= "&ns=" . $nameserver3;
	}


	if ($nameserver4) {
		$nslist .= "&ns=" . $nameserver4;
	}


	if ($nameserver5) {
		$nslist .= "&ns=" . $nameserver5;
	}

	$postfields["ns"] = "" . $nslist;
	$result = resellerclub_SendCommand( "modify-ns", "domains", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	return $values;
}


function resellerclub_GetRegistrarLock($params) {
	if (!$params["ResellerID"]) {
		return array( "error" => "Missing Reseller ID. Please navigate to Setup > Domain Registrars to configure." );
	}


	if (!$params["APIKey"]) {
		return array( "error" => "Missing API Key. Please navigate to Setup > Domain Registrars to configure." );
	}

	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$lockstatus = "unlocked";
	$result = resellerclub_SendCommand( "locks", "domains", $postfields, $params, "GET" );

	if ($result["transferlock"] == "1") {
		$lockstatus = "locked";
	}

	return $lockstatus;
}


function resellerclub_SaveRegistrarLock($params) {
	if (!$params["ResellerID"]) {
		return array( "error" => "Missing Reseller ID. Please navigate to Setup > Domain Registrars to configure." );
	}


	if (!$params["APIKey"]) {
		return array( "error" => "Missing API Key. Please navigate to Setup > Domain Registrars to configure." );
	}

	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;

	if ($params["lockenabled"] == "locked") {
		$result = resellerclub_SendCommand( "enable-theft-protection", "domains", $postfields, $params, "POST" );
	}
	else {
		$result = resellerclub_SendCommand( "disable-theft-protection", "domains", $postfields, $params, "POST" );
	}

	$values["error"] = $Enom->Values["Err1"];
	return $values;
}


function resellerclub_RegisterDomain($params) {
	if (!$params["ResellerID"]) {
		return array( "error" => "Missing Reseller ID. Please navigate to Setup > Domain Registrars to configure." );
	}


	if (!$params["APIKey"]) {
		return array( "error" => "Missing API Key. Please navigate to Setup > Domain Registrars to configure." );
	}

	global $CONFIG;

	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$postfields["username"] = resellerclub_getClientEmail( $params["userid"] );
	$result = resellerclub_SendCommand( "details", "customers", $postfields, $params, "GET" );
	unset( $postfields );

	if (strtoupper( $result["response"]["status"] ) == "ERROR") {
		if (!$result["response"]["message"]) {
			$result["response"]["message"] = $result["response"]["error"];
		}

		return array( "error" => $result["response"]["message"] );
	}


	if (strtoupper( $result["status"] ) == "ERROR") {
		$customerid = resellerclub_addCustomer( $params );
	}
	else {
		$customerid = $result["customerid"];
	}


	if (!$customerid) {
		return array( "error" => "Error obtaining customer id" );
	}


	if (is_array( $customerid )) {
		return $customerid;
	}

	$postfields["name"] = "" . $params["firstname"] . " " . $params["lastname"];
	$contacttype = resellerclub_ContactType( $params["tld"] );

	if ($contacttype == "CaContact") {
		$legal = strtolower( $params["additionalfields"]["Legal Type"] );

		if (( ( ( $legal != "canadian citizen" && $legal != "permanent resident of canada" ) && $legal != "aboriginal peoples" ) && $legal != "legal representative of a canadian citizen" )) {
			$canonindv = true;
		}
	}

	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["customer-id"] = "" . $customerid;
	$postfields["no-of-records"] = "10";
	$postfields["page-no"] = "1";
	$postfields["status"] = "Active";
	$postfields["email"] = $params["email"];
	$postfields["type"] = $contacttype;
	$result = resellerclub_SendCommand( "search", "contacts", $postfields, $params, "GET" );
	unset( $postfields );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}


	if (( ( ( ( strtoupper( $result["status"] ) == "ERROR" || $result["recsonpage"] == "0" ) || preg_match( "/es$/i", $params["tld"] ) ) || preg_match( "/ca$/i", $params["tld"] ) ) || preg_match( "/pro$/i", $params["tld"] ) )) {
		$contactid = resellerclub_addContact( $params, $customerid, $contacttype, $canonindv );
	}
	else {
		foreach ($result["result"] as $entry => $value) {
			$contactid = $value["contact.contactid"];

			if ($contactid) {
				break;
			}
		}
	}


	if (!$contactid) {
		return array( "error" => "Error obtaining contact id" );
	}


	if (( is_array( $contactid ) && $contactid["error"] )) {
		return $contactid;
	}


	if (is_array( $contactid )) {
		$additionalid = $contactid["additionalid"];
		$contactid = $contactid["contactid"];
	}

	$contactfields = resellerclub_ContactTLDSpecificFields( $params );

	if (( count( $contactfields ) && $contactfields["product-key"] )) {
		$postfields["auth-userid"] = $params["ResellerID"];
		$postfields["api-key"] = $params["APIKey"];
		$postfields["customer-id"] = $customerid;
		$postfields["contact-id"] = $contactid;
		$postfields = array_merge( $postfields, $contactfields );
		$result = resellerclub_SendCommand( "set-details", "contacts", $postfields, $params, "POST" );
	}

	unset( $postfields );

	if (preg_match( "/coop$/i", $params["tld"] )) {
		$sponsorid = resellerclub_addCOOPSponsor( $params );

		if (!$sponsorid) {
			return array( "error" => "Unable to add/obtain Sponsor ID" );
		}
	}

	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["customer-id"] = $customerid;
	$tld = $params["tld"];
	$sld = $params["sld"];
	$idnlang = explode( "|", $params["additionalfields"]["IDN Language"] );
	$idnlang = $idnlang[0];

	if (( $idnlang && $idnlang != "NOIDN" )) {
		$sld = $params["original"]["sld"];
		$postfields["attr-name1"] = "idnLanguageCode";
		$postfields["attr-value1"] = $idnlang;
	}

	$postfields["domain-name"] = "" . $sld . "." . $tld;
	$nameserver1 = $params["ns1"];
	$nameserver2 = $params["ns2"];
	$nameserver3 = $params["ns3"];
	$nameserver4 = $params["ns4"];
	$nameserver5 = $params["ns5"];
	$nslist = "" . $nameserver1 . "&ns=" . $nameserver2;

	if ($nameserver3) {
		$nslist .= "&ns=" . $nameserver3;
	}


	if ($nameserver4) {
		$nslist .= "&ns=" . $nameserver4;
	}


	if ($nameserver5) {
		$nslist .= "&ns=" . $nameserver5;
	}

	$postfields["ns"] = "" . $nslist;
	$postfields["years"] = $params["regperiod"];
	$postfields["reg-contact-id"] = $contactid;
	$postfields["admin-contact-id"] = ($additionalid ? $additionalid : $contactid);
	$postfields["tech-contact-id"] = ($additionalid ? $additionalid : $contactid);
	$postfields["billing-contact-id"] = ($additionalid ? $additionalid : $contactid);
	$postfields["invoice-option"] = "NoInvoice";
	$postfields["protect-privacy"] = ($params["idprotection"] ? "true" : "false");
	$postfields = array_merge( $postfields, resellerclub_DomainTLDSpecificFields( $params, $contactid ) );

	if (preg_match( "/au$/i", $params["tld"] )) {
		if (( is_numeric( $postfields["attr-value2"] ) && !resellerclub_validateABN( $postfields["attr-value2"] ) )) {
			return array( "error" => "Invalid ABN" );
		}
	}

	$result = resellerclub_SendCommand( "register", "domains", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}


	if ($result["actionstatus"] == "Failed") {
		return array( "error" => $result["actionstatusdesc"] );
	}


	if (( preg_match( "/xxx$/i", $params["tld"] ) && $params["additionalfields"]["Membership Token/ID"] )) {
		unset( $postfields );
		$postfields["auth-userid"] = $params["ResellerID"];
		$postfields["api-key"] = $params["APIKey"];
		$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
		$orderid = resellerclub_getOrderID( $postfields, $params );
		unset( $postfields );

		if (is_numeric( $orderid )) {
			$postfields["auth-userid"] = $params["ResellerID"];
			$postfields["api-key"] = $params["APIKey"];
			$postfields["order-id"] = $orderid;
			$postfields["association-id"] = $params["additionalfields"]["Membership Token/ID"];
			$result = resellerclub_SendCommand( "association-details", "domains/dotxxx", $postfields, $params, "POST" );
		}
	}

	$values = array( "success" => "success" );
	return $values;
}


function resellerclub_TransferDomain($params) {
	if (!$params["ResellerID"]) {
		return array( "error" => "Missing Reseller ID. Please navigate to Setup > Domain Registrars to configure." );
	}


	if (!$params["APIKey"]) {
		return array( "error" => "Missing API Key. Please navigate to Setup > Domain Registrars to configure." );
	}

	global $CONFIG;

	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$transfersecret = $params["transfersecret"];
	$postfields["username"] = resellerclub_getClientEmail( $params["userid"] );
	$result = resellerclub_SendCommand( "details", "customers", $postfields, $params, "GET" );
	unset( $postfields );

	if (strtoupper( $result["response"]["status"] ) == "ERROR") {
	}
	else {
		if (strtoupper( $result["status"] ) == "ERROR") {
			$customerid = resellerclub_addCustomer( $params );
		}
		else {
			$customerid = $result["customerid"];
		}
	}


	if (!$customerid) {
		return array( "error" => "Error obtaining customer id" );
	}


	if (is_array( $customerid )) {
		return $customerid;
	}

	$contacttype = resellerclub_ContactType( $params["tld"] );

	if ($contacttype == "CaContact") {
		$legal = strtolower( $params["additionalfields"]["Legal Type"] );

		if (( ( ( $legal != "canadian citizen" && $legal != "permanent resident of canada" ) && $legal != "aboriginal peoples" ) && $legal != "legal representative of a canadian citizen" )) {
			$canonindv = true;
		}
	}

	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["customer-id"] = "" . $customerid;
	$postfields["no-of-records"] = "10";
	$postfields["page-no"] = "1";
	$postfields["status"] = "Active";
	$postfields["email"] = $params["email"];
	$postfields["type"] = $contacttype;
	$result = resellerclub_SendCommand( "search", "contacts", $postfields, $params, "GET" );
	unset( $postfields );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}


	if (( ( ( ( strtoupper( $result["status"] ) == "ERROR" || $result["recsonpage"] == "0" ) || preg_match( "/es$/i", $params["tld"] ) ) || preg_match( "/ca$/i", $params["tld"] ) ) || preg_match( "/pro$/i", $params["tld"] ) )) {
		$contactid = resellerclub_addContact( $params, $customerid, $contacttype, $canonindv );
	}
	else {
		foreach ($result["result"] as $entry => $value) {
			$contactid = $value["contact.contactid"];

			if ($contactid) {
				break;
			}
		}
	}


	if (!$contactid) {
		return array( "error" => "Error obtaining contact id" );
	}


	if (( is_array( $contactid ) && $contactid["error"] )) {
		return $contactid;
	}


	if (is_array( $contactid )) {
		$additionalid = $contactid["additionalid"];
		$contactid = $contactid["contactid"];
	}

	$contactfields = resellerclub_ContactTLDSpecificFields( $params );

	if (count( $contactfields )) {
		$postfields["auth-userid"] = $params["ResellerID"];
		$postfields["api-key"] = $params["APIKey"];
		$postfields["customer-id"] = $customerid;
		$postfields["contact-id"] = $contactid;
		$postfields = array_merge( $postfields, $contactfields );
		$result = resellerclub_SendCommand( "set-details", "contacts", $postfields, $params, "POST" );
	}

	unset( $postfields );

	if (preg_match( "/coop$/i", $params["tld"] )) {
		$sponsorid = resellerclub_addCOOPSponsor( $params );

		if (!$sponsorid) {
			return array( "error" => "Unable to add/obtain Sponsor ID" );
		}
	}

	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["customer-id"] = $customerid;
	$tld = $params["tld"];
	$sld = $params["sld"];
	$idnlang = explode( "|", $params["additionalfields"]["IDN Language"] );
	$idnlang = $idnlang[0];

	if (( $idnlang && $idnlang != "NOIDN" )) {
		$sld = $params["original"]["sld"];
		$postfields["attr-name1"] = "idnLanguageCode";
		$postfields["attr-value1"] = $idnlang;
	}

	$postfields["domain-name"] = "" . $sld . "." . $tld;
	$postfields["years"] = $params["regperiod"];

	if ($transfersecret) {
		$postfields["auth-code"] = $transfersecret;
	}

	$postfields["reg-contact-id"] = $contactid;
	$postfields["admin-contact-id"] = ($additionalid ? $additionalid : $contactid);
	$postfields["tech-contact-id"] = ($additionalid ? $additionalid : $contactid);
	$postfields["billing-contact-id"] = ($additionalid ? $additionalid : $contactid);
	$postfields["invoice-option"] = "NoInvoice";
	$postfields["protect-privacy"] = ($params["idprotection"] ? "true" : "false");

	if (!preg_match( "/au$/i", $params["tld"] )) {
		$postfields = array_merge( $postfields, resellerclub_DomainTLDSpecificFields( $params, $contactid ) );
	}

	$result = resellerclub_SendCommand( "transfer", "domains", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}


	if ($result["actionstatus"] == "Failed") {
		return array( "error" => $result["actionstatusdesc"] );
	}

	$values = array( "success" => "success" );
	return $values;
}


function resellerclub_RenewDomain($params) {
	if (!$params["ResellerID"]) {
		return array( "error" => "Missing Reseller ID. Please navigate to Setup > Domain Registrars to configure." );
	}


	if (!$params["APIKey"]) {
		return array( "error" => "Missing API Key. Please navigate to Setup > Domain Registrars to configure." );
	}

	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["options"] = "OrderDetails";
	$result = resellerclub_SendCommand( "details", "domains", $postfields, $params, "GET" );
	$expiry = $result["endtime"];

	if (!$expiry) {
		return array( "error" => "Unable to obtain Domain Expiry Date from Registrar" );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$regperiod = $params["regperiod"];
	$postfields["order-id"] = $orderid;
	$postfields["years"] = $regperiod;
	$postfields["exp-date"] = $expiry;
	$postfields["invoice-option"] = "NoInvoice";
	$result = resellerclub_SendCommand( "renew", "domains", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}


	if ($result["error"]) {
		return array( "error" => "Renewal order placed. " . substr( $result["error"], 0, 0 - 1 ) . " if / when sufficient funds are available in the reseller account." );
	}


	if ($result["actionstatus"] == "Failed") {
		return array( "error" => $result["actionstatusdesc"] );
	}

	$values = array( "success" => "success" );
	return $values;
}


function resellerclub_GetContactDetails($params) {
	if (!$params["ResellerID"]) {
		return array( "error" => "Missing Reseller ID. Please navigate to Setup > Domain Registrars to configure." );
	}


	if (!$params["APIKey"]) {
		return array( "error" => "Missing API Key. Please navigate to Setup > Domain Registrars to configure." );
	}

	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["options"] = "ContactIds";
	$result = resellerclub_SendCommand( "details", "domains", $postfields, $params, "GET" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	$contactid = $result["registrantcontactid"];

	if (!$contactid) {
		return array( "error" => "Error obtaining contact id" );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["contact-id"] = $contactid;
	$result = resellerclub_SendCommand( "details", "contacts", $postfields, $params, "GET" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	$values["Registrant"] = array( "Full Name" => $result["name"], "Email" => $result["emailaddr"], "Company Name" => $result["company"], "Address 1" => $result["address1"], "Address 2" => $result["address2"], "Address 3" => $result["address3"], "City" => $result["city"], "State" => $result["state"], "Postcode" => $result["zip"], "Country" => $result["country"], "Phone Number" => "+" . $result["telnocc"] . "." . $result["telno"] );
	return $values;
}


function resellerclub_SaveContactDetails($params) {
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["options"] = "ContactIds";
	$result = resellerclub_SendCommand( "details", "domains", $postfields, $params, "GET" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	$contactid = $result["registrantcontactid"];

	if (!$contactid) {
		return array( "error" => "Error obtaining contact id" );
	}

	$phonenumber = $params["contactdetails"]["Registrant"]["Phone Number"];
	$phonenumber = preg_replace( "/[^0-9.]/", "", $phonenumber );
	$phonenumberparts = explode( ".", $phonenumber, 2 );

	if (count( $phonenumberparts ) == 2) {
		$phonecc = $phonenumberparts[0];
		$phonenumber = $phonenumberparts[1];
	}
	else {
		$phonecc = substr( $phonenumber, 0, 2 );
		$phonenumber = substr( $phonenumber, 2 );
	}

	unset( $postfields );

	if (!$params["contactdetails"]["Registrant"]["Company Name"]) {
		$params["contactdetails"]["Registrant"]["Company Name"] = "N/A";
	}

	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["contact-id"] = $contactid;
	$postfields["name"] = $params["contactdetails"]["Registrant"]["Full Name"];
	$postfields["company"] = $params["contactdetails"]["Registrant"]["Company Name"];
	$postfields["email"] = $params["contactdetails"]["Registrant"]["Email"];
	$postfields["address-line-1"] = $params["contactdetails"]["Registrant"]["Address 1"];
	$postfields["address-line-2"] = $params["contactdetails"]["Registrant"]["Address 2"];
	$postfields["address-line-3"] = $params["contactdetails"]["Registrant"]["Address 3"];
	$postfields["city"] = $params["contactdetails"]["Registrant"]["City"];
	$postfields["state"] = $params["contactdetails"]["Registrant"]["State"];
	$postfields["zipcode"] = $params["contactdetails"]["Registrant"]["Postcode"];
	$postfields["country"] = $params["contactdetails"]["Registrant"]["Country"];
	$postfields["phone-cc"] = $phonecc;
	$postfields["phone"] = $phonenumber;
	$result = resellerclub_SendCommand( "modify", "contacts", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

}


function resellerclub_GetEPPCode($params) {
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["options"] = "OrderDetails";
	$result = resellerclub_SendCommand( "details", "domains", $postfields, $params, "GET" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	$values["eppcode"] = $result["domsecret"];
	return $values;
}


function resellerclub_RegisterNameserver($params) {
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["cns"] = $params["nameserver"];
	$postfields["ip"] = $params["ipaddress"];
	$result = resellerclub_SendCommand( "add-cns", "domains", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	return array();
}


function resellerclub_ModifyNameserver($params) {
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["cns"] = $params["nameserver"];
	$postfields["old-ip"] = $params["currentipaddress"];
	$postfields["new-ip"] = $params["newipaddress"];
	$result = resellerclub_SendCommand( "modify-cns-ip", "domains", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	return array();
}


function resellerclub_DeleteNameserver($params) {
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["cns"] = $params["nameserver"];
	$postfields["ip"] = gethostbyname( $params["nameserver"] . "." . $postfields["domain-name"] );
	$result = resellerclub_SendCommand( "delete-cns-ip", "domains", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	return array();
}


function resellerclub_RequestDelete($params) {
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$result = $sld = resellerclub_SendCommand( "delete", "domains", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}


	if ($result["actionstatus"] == "Failed") {
		return array( "error" => $result["actionstatusdesc"] );
	}

	return $values;
}


function resellerclub_GetDNS($params) {
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$result = resellerclub_SendCommand( "activate", "dns", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$pagenumber = 7;
	$postfields["no-of-records"] = "50";
	$postfields["page-no"] = $pagenumber;
	$typelist = array( "A", "MX", "CNAME", "TXT", "AAAA" );
	$hostrecords = array();
	foreach ($typelist as $type) {
		$postfields["type"] = $type;
		$result = resellerclub_SendCommand( "search-records", "dns/manage", $postfields, $params, "GET" );

		if (strtoupper( $result["status"] ) == "ERROR") {
			if (!$result["message"]) {
				$result["message"] = $result["error"];
			}

			return array( "error" => $result["message"] );
		}

		foreach ($result as $entry => $value) {
			$host = "";
			$address = "";
			$recid = "";
			$recid = $entry;
			$host = $value["host"];
			$address = $value["value"];

			if ($type == "MX") {
				$priority = $value1["priority"];
			}


			if (( $host && $address )) {
				$hostrecords[] = array( "hostname" => htmlentities( $host ), "type" => "" . $type, "address" => htmlentities( $address ), "priority" => "" . $priority, "recid" => $recid );
				continue;
			}
		}
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$postfields["order-id"] = $orderid;
	$result = resellerclub_SendCommand( "details", "domainforward", $postfields, $params, "GET" );

	if (( !$result["status"] && $result["forward"] )) {
		$host = "";
		$address = "";
		$recid = "";
		$hostrecords[] = array( "hostname" => "@", "type" => "URL", "address" => htmlentities( $result["forward"] ) );
	}

	return $hostrecords;
}


function resellerclub_SaveDNS($params) {
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$hostrecords = resellerclub_GetDNS( $params );
	$newrecords = $params["dnsrecords"];
	foreach ($newrecords as $num => $newvalues) {
		$oldvalues = $hostrecords[$num];
		$oldhostname = $oldvalues["hostname"];
		$oldtype = $oldvalues["type"];
		$oldaddress = $oldvalues["address"];
		$oldpriority = $oldvalues["priority"];
		$newhostname = $newvalues["hostname"];
		$newtype = $newvalues["type"];
		$newaddress = $newvalues["address"];
		$newpriority = $newvalues["priority"];

		if ($newpriority == "N/A") {
			$newpriority = "";
		}


		if (( !$newhostname || !$newaddress )) {
			if (( $oldhostname && $oldaddress )) {
				if (( $oldtype != "URL" && $oldtype != "FRAME" )) {
					$postfields["host"] = $oldhostname;
					$postfields["value"] = $oldaddress;
					$result = resellerclub_SendCommand( "delete-record", "dns/manage", $postfields, $params, "POST" );
					continue;
				}

				$orderid = resellerclub_getOrderID( $postfields, $params );
				$postfields["order-id"] = $orderid;
				$postfields["url-masking"] = "false";
				$postfields["sub-domain-forwarding"] = "false";
				$postfields["path-forwarding"] = "false";
				$postfields["forward-to"] = "";
				$result = resellerclub_SendCommand( "manage", "domainforward", $postfields, $params, "POST" );
				continue;
			}

			continue;
		}


		if (( ( ( $oldhostname != $newhostname || $oldtype != $newtype ) || $oldaddress != $newaddress ) || ( $type == "MX" && $oldpriority != $newpriority ) )) {
			$postfields["host"] = $newhostname;
			$ltype = strtolower( $newtype );

			if ($ltype == "a") {
				$ltype = "ipv4";
			}


			if ($ltype == "aaaa") {
				$ltype = "ipv6";
			}


			if ($ltype == "mx") {
				$postfields["priority"] = $newpriority;
			}


			if (( $ltype == "url" || $ltype == "frame" )) {
				$orderid = resellerclub_getOrderID( $postfields, $params );
				$postfields["order-id"] = $orderid;
				$result = resellerclub_SendCommand( "activate", "domainforward", $postfields, $params, "POST" );
				$postfields["url-masking"] = "true";
				$postfields["sub-domain-forwarding"] = "true";
				$postfields["path-forwarding"] = "true";
				$postfields["forward-to"] = html_entity_decode( $newaddress );
				$result = resellerclub_SendCommand( "manage", "domainforward", $postfields, $params, "POST" );
			}
			else {
				if (in_array( $ltype, array( "ipv4", "ipv6", "cname", "mx", "ns", "txt", "srv", "soa" ) )) {
					if (( !$oldhostname && !$oldaddress )) {
						$postfields["value"] = $newaddress;
						$result = resellerclub_SendCommand( "add-" . $ltype . "-record", "dns/manage", $postfields, $params, "POST" );
					}
					else {
						$postfields["current-value"] = html_entity_decode( $oldaddress );
						$postfields["new-value"] = html_entity_decode( $newaddress );
						$result = resellerclub_SendCommand( "update-" . $ltype . "-record", "dns/manage", $postfields, $params, "POST" );
					}
				}
			}

			$error = false;

			if (( $result["status"] == "Failed" || $result["status"] == "ERROR" )) {
				if (!$result["msg"]) {
					$result["msg"] == $result["message"];
				}

				$errormsgs[] = $newtype . "|" . $newhostname . "|" . $newaddress . " - " . $result["msg"];
				continue;
			}

			continue;
		}
	}


	if (count( $errormsgs )) {
		return array( "error" => implode( "<br />", $errormsgs ) );
	}

	return array();
}


function resellerclub_GetEmailForwarding($params) {
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$result = resellerclub_SendCommand( "is-ownership-verified", "mail/domain", $postfields, $params, "GET" );

	if ($result["response"]["isOwnershipVerified"] != "true") {
		unset( $postfields );
		$postfields["auth-userid"] = $params["ResellerID"];
		$postfields["api-key"] = $params["APIKey"];
		$postfields["domain-name"] = $sld . "." . $tld;
		$postfields["value"] = "@";
		$postfields["type"] = "MX";
		$postfields["host"] = "mx1.mailhostbox.com";
		$postfields["priority"] = "100";
		$result = resellerclub_SendCommand( "add-mx-record", "dns/manage", $postfields, $params, "POST" );
		$postfields["host"] = "mx2.mailhostbox.com";
		$result = resellerclub_SendCommand( "add-mx-record", "dns/manage", $postfields, $params, "POST" );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$result = resellerclub_SendCommand( "activate", "mail", $postfields, $params, "POST" );
	$postfields["account-types"] = "forward_only";
	$result = resellerclub_SendCommand( "search", "mail/users", $postfields, $params, "GET" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	foreach ($result["response"]["users"] as $entry => $value) {
		$email = explode( "@", $value["emailAddress"] );
		$values[$entry]["prefix"] = $email[0];
		$values[$entry]["forwardto"] = $value["adminForwards"];
	}

	return $values;
}


function resellerclub_SaveEmailForwarding($params) {
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$result = resellerclub_SendCommand( "activate", "mail", $postfields, $params, "POST" );
	$postfields["account-types"] = "forward_only";
	foreach ($params["prefix"] as $key => $value) {
		$email = $params["prefix"][$key] . "@" . $params["sld"] . "." . $params["tld"];
		$postfields["email"] = $email;
		$forwardto = $params["forwardto"][$key];
		$result = resellerclub_SendCommand( "search", "mail/users", $postfields, $params, "GET" );

		if (strtoupper( $result["status"] ) == "ERROR") {
			if (!$result["message"]) {
				$result["message"] = $result["error"];
			}

			return array( "error" => $result["message"] );
		}


		if ($result["response"]["message"] == "No Records found") {
			unset( $postfields );
			$postfields["auth-userid"] = $params["ResellerID"];
			$postfields["api-key"] = $params["APIKey"];
			$postfields["order-id"] = $orderid;
			$postfields["email"] = $email;
			$postfields["forwards"] = $forwardto;
			$result2 = resellerclub_SendCommand( "add-forward-only-account", "mail/user", $postfields, $params, "POST" );
			continue;
		}

		foreach ($result["response"]["users"] as $entry => $values) {
			unset( $postfields );
			$postfields["auth-userid"] = $params["ResellerID"];
			$postfields["api-key"] = $params["APIKey"];
			$postfields["order-id"] = $orderid;
			$postfields["email"] = $email;

			if (!$forwardto) {
				$postfields["forwards"] = $values["adminForwards"];
				$result2 = resellerclub_SendCommand( "delete", "mail/user", $postfields, $params, "POST" );
			}
			else {
				$existingforwards = explode( ",", $values["adminForwards"] );
				$addforwards = explode( ",", $forwardto );
				$forwards = $removeforwards = "";
				foreach ($addforwards as $key => $value) {

					if (!in_array( $value, $existingforwards )) {
						$forwards = "" . $value . ",";
						continue;
					}
				}


				if ($forwards) {
					$forwards = substr( $forwards, 0, 0 - 1 );
					$postfields["forwards"] = $forwards;
					$result2 = resellerclub_SendCommand( "add-admin-forwards", "mail/user", $postfields, $params, "POST" );
				}

				foreach ($existingforwards as $key => $value) {

					if (!in_array( $value, $addforwards )) {
						$removeforwards = "" . $value . ",";
						continue;
					}
				}


				if ($removeforwards) {
					$postfields["forwards"] = $removeforwards;
					$result2 = resellerclub_SendCommand( "delete-admin-forwards", "mail/user", $postfields, $params, "POST" );
				}
			}


			if (strtoupper( $result2["status"] ) == "ERROR") {
				if (!$result2["message"]) {
					$result2["message"] = $result2["error"];
				}

				return array( "error" => $result2["message"] );
			}
		}
	}

}


function resellerclub_ReleaseDomain($params) {
	$transfertag = $params["transfertag"];
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["new-tag"] = $transfertag;
	$result = resellerclub_SendCommand( "release", "domains/uk", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	update_query( "tbldomains", array( "status" => "Cancelled" ), array( "id" => $params["domainid"] ) );
}


function resellerclub_IDProtectToggle($params) {
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;

	if ($params["protectenable"]) {
		$postfields["protect-privacy"] = "true";
		$idprotect = "on";
	}
	else {
		$postfields["protect-privacy"] = "false";
		$idprotect = "";
	}

	$postfields["reason"] = "Customer Request";
	$result = resellerclub_SendCommand( "modify-privacy-protection", "domains", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	update_query( "tbldomains", array( "idprotection" => $idprotect ), array( "id" => $params["domainid"] ) );
}


function resellerclub_AdminCustomButtonArray() {
	$buttonarray = array();
	$params = get_query_vals( "tbldomains", "", array( "id" => $_REQUEST["id"] ) );

	if (( $params["type"] == "Transfer" && $params["status"] == "Pending Transfer" )) {
		$buttonarray["Resend Transfer Approval Email"] = "resendtransferapproval";
		$buttonarray["Cancel Domain Transfer"] = "canceldomaintransfer";
	}

	return $buttonarray;
}


function resellerclub_resendtransferapproval($params) {
	$postfields = $values = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$result = resellerclub_SendCommand( "resend-rfa", "domains", $postfields, $params, "POST" );

	if ($result["status"] == "true") {
		$values["message"] = "Successfully resent the transfer approval email";
	}
	else {
		$values["error"] = $result["message"];
	}

	return $values;
}


function resellerclub_canceldomaintransfer() {
	$postfields = $values = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$result = resellerclub_SendCommand( "cancel-transfer", "domains", $postfields, $params, "POST" );

	if ($result["status"] == "Success") {
		$values["message"] = "Successfully cancelled the domain transfer";
	}
	else {
		$values["error"] = $result["message"];
	}

	return $values;
}


function resellerclub_SendCommand($command, $type, $postfields, $params, $method, $nodecode = false) {
	if ($params["TestMode"]) {
		$url = "https://test.httpapi.com/api/" . $type . "/" . $command . ".json";
	}
	else {
		$url = "https://httpapi.com/api/" . $type . "/" . $command . ".json";
	}

	$ch = curl_init();

	if ($method == "GET") {
		$url .= "?";
		foreach ($postfields as $field => $data) {
			$url .= "" . $field . "=" . rawurlencode( $data ) . "&";
		}

		$url = substr( $url, 0, 0 - 1 );
		$postfields["url"] = $url;
	}
	else {
		$postfield = "";
		foreach ($postfields as $field => $data) {

			if ($field != "ns") {
				$data = rawurlencode( $data );
			}


			if (( ( preg_match( "/es$/i", $params["tld"] ) && !$data ) && $field == "attr-value2" )) {
				$data = 6;
			}


			if (( ( preg_match( "/es$/i", $params["tld"] ) && !$data ) && $field == "attr-value3" )) {
				$data = rawurlencode( $params["additionalfields"]["ID Form Number"] );
			}

			$postfield .= "" . $field . "=" . $data . "&";
		}

		$postfield = substr( $postfield, 0, 0 - 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $postfield );
		$postfields["posteddata"] = $postfield;
	}

	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$data = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$ip = resellerclub_GetIP();
		$ip2 = (isset( $_SERVER["SERVER_ADDR"] ) ? $_SERVER["SERVER_ADDR"] : $_SERVER["LOCAL_ADDR"]);
		$result["response"]["status"] = "ERROR";
		$result["response"]["message"] = "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch ) . ( " (IP: " . $ip . " & " . $ip2 . ")" );
	}
	else {
		if (( $nodecode && is_numeric( $data ) )) {
			$result = $data;
		}
		else {
			$result = json_decode( $data, true );
		}
	}

	curl_close( $ch );
	logModuleCall( "logicboxes", "" . $type . "/" . $command, $postfields, $data, $result, array( $params["ResellerID"], $params["APIKey"] ) );
	return $result;
}


function resellerclub_getOrderID($postfields, $params) {
	$domain = $postfields["domain-name"];

	if (isset( $GLOBALS["logicboxesorderids"][$domain] )) {
		$result = $GLOBALS["logicboxesorderids"][$domain];
	}
	else {
		$result = resellerclub_SendCommand( "orderid", "domains", $postfields, $params, "GET", true );
		$GLOBALS["logicboxesorderids"][$domain] = $result;
	}


	if (is_array( $result )) {
		if (strtoupper( $result["response"]["status"] ) == "ERROR") {
			return $result["response"]["message"];
		}


		if (strtoupper( $result["status"] ) == "ERROR") {
			return $result["message"];
		}
	}

	$orderid = $result;

	if (( !$orderid || is_array( $orderid ) )) {
		return "Unable to obtain Order-ID";
	}

	return $orderid;
}


function resellerclub_genLBRandomPW() {
	$letters = "ABCDEFGHIJKLMNPQRSTUVYXYZabcdefghijklmnopqrstuvwxyz";
	$numbers = "0123456789";
	$letterscount = strlen( $letters ) - 1;
	$numberscount = strlen( $numbers ) - 1;
	$password = "";
	$i = 5;

	while ($i < 5) {
		$password .= $letters[rand( 0, $letterscount )] . $numbers[rand( 0, $numberscount )];
		++$i;
	}

	return $password;
}


function resellerclub_xml2array($contents, $get_attributes = 1, $priority = "tag") {
	$parser = xml_parser_create( "" );
	xml_parser_set_option( $parser, XML_OPTION_TARGET_ENCODING, "UTF-8" );
	xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
	xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
	xml_parse_into_struct( $parser, trim( $contents ), $xml_values );
	xml_parser_free( $parser );

	if (!$xml_values) {
		return null;
	}

	$xml_array = array();
	$parents = array();
	$opened_tags = array();
	$arr = array();
	$current = &$xml_array;

	$repeated_tag_index = array();
	foreach ($xml_values as $data) {
		unset( $attributes );
		unset( $value );
		extract( $data );
		$result = array();
		$attributes_data = array();

		if (isset( $value )) {
			if ($priority == "tag") {
				$result = $value;
			}
			else {
				$result["value"] = $value;
			}
		}


		if (( isset( $attributes ) && $get_attributes )) {
			foreach ($attributes as $attr => $val) {

				if ($priority == "tag") {
					$attributes_data[$attr] = $val;
					continue;
				}

				$result["attr"][$attr] = $val;
			}
		}


		if ($type == "open") {
			$parent[$level - 1] = &$current;

			if (( !is_array( $current ) || !in_array( $tag, array_keys( $current ) ) )) {
				$current[$tag] = $result;

				if ($attributes_data) {
					$current[$tag . "_attr"] = $attributes_data;
				}

				$repeated_tag_index[$tag . "_" . $level] = 1;
				$current = &$current[$tag];

				continue;
			}


			if (isset( $current[$tag][0] )) {
				$current[$tag][$repeated_tag_index[$tag . "_" . $level]] = $result;
				++$repeated_tag_index[$tag . "_" . $level];
			}
			else {
				$current[$tag] = array( $current[$tag], $result );
				$repeated_tag_index[$tag . "_" . $level] = 2;

				if (isset( $current[$tag . "_attr"] )) {
					$current[$tag]["0_attr"] = $current[$tag . "_attr"];
					unset( $current[$tag . "_attr"] );
				}
			}

			$last_item_index = $repeated_tag_index[$tag . "_" . $level] - 1;
			$current = &$current[$tag][$last_item_index];

			continue;
		}


		if ($type == "complete") {
			if (!isset( $current[$tag] )) {
				$current[$tag] = $result;
				$repeated_tag_index[$tag . "_" . $level] = 1;

				if (( $priority == "tag" && $attributes_data )) {
					$current[$tag . "_attr"] = $attributes_data;
					continue;
				}

				continue;
			}


			if (( isset( $current[$tag][0] ) && is_array( $current[$tag] ) )) {
				$current[$tag][$repeated_tag_index[$tag . "_" . $level]] = $result;

				if (( ( $priority == "tag" && $get_attributes ) && $attributes_data )) {
					$current[$tag][$repeated_tag_index[$tag . "_" . $level] . "_attr"] = $attributes_data;
				}

				++$repeated_tag_index[$tag . "_" . $level];
				continue;
			}

			$current[$tag] = array( $current[$tag], $result );
			$repeated_tag_index[$tag . "_" . $level] = 1;

			if (( $priority == "tag" && $get_attributes )) {
				if (isset( $current[$tag . "_attr"] )) {
					$current[$tag]["0_attr"] = $current[$tag . "_attr"];
					unset( $current[$tag . "_attr"] );
				}


				if ($attributes_data) {
					$current[$tag][$repeated_tag_index[$tag . "_" . $level] . "_attr"] = $attributes_data;
				}
			}

			++$repeated_tag_index[$tag . "_" . $level];
			continue;
		}


		if ($type == "close") {
			$current = &$parent[$level - 1];

			continue;
		}
	}

	return $xml_array;
}


function resellerclub_ContactTLDSpecificFields($params) {
	$postfields = array();

	if (preg_match( "/us$/i", $params["tld"] )) {
		$purpose = $params["additionalfields"]["Application Purpose"];
		$category = $params["additionalfields"]["Nexus Category"];

		if ($purpose == "Business use for profit") {
			$purpose = "P1";
		}
		else {
			if (( ( ( $purpose == "Non-profit business" || $purpose == "Club" ) || $purpose == "Association" ) || $purpose == "Religious Organization" )) {
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

		$postfields["attr-name1"] = "purpose";
		$postfields["attr-value1"] = "" . $purpose;
		$postfields["attr-name2"] = "category";
		$postfields["attr-value2"] = "" . $category;
		$postfields["product-key"] = "domus";
	}
	else {
		if (preg_match( "/uk$/i", $params["tld"] )) {
			if ($params["additionalfields"]["Registrant Name"]) {
				$postfields["name"] = $params["additionalfields"]["Registrant Name"];
			}
		}
		else {
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

				$postfields["attr-name1"] = "CPR";
				$postfields["attr-value1"] = "" . $legaltype;
				$postfields["attr-name2"] = "AgreementVersion";
				$postfields["attr-value2"] = "2.0";
				$postfields["attr-name3"] = "AgreementValue";
				$postfields["attr-value3"] = "y";
				$postfields["product-key"] = "dotca";
			}
			else {
				if (preg_match( "/es$/i", $params["tld"] )) {
					$ltypearray = explode( "|", $params["additionalfields"]["Legal Entity Type"] );
					$legaltype = $ltypearray[0];

					if (!$legaltype) {
						$legaltype = "1";
					}


					if ($legaltype == "1") {
						$postfields["company"] = "N/A";
					}

					$params["additionalfields"]["ID Form Type"] = explode( "|", $params["additionalfields"]["ID Form Type"] );
					$idtype = $params["additionalfields"]["ID Form Type"][0];
					$idnumber = $params["additionalfields"]["ID Form Number"];
					$postfields["attr-name1"] = "es_form_juridica";
					$postfields["attr-value1"] = "" . $legaltype;
					$postfields["attr-name2"] = "es_tipo_identificacion";
					$postfields["attr-value2"] = "" . $idtype;
					$postfields["attr-name3"] = "es_identificacion";
					$postfields["attr-value3"] = "" . $idnumber;
					$postfields["product-key"] = "dotes";
				}
				else {
					if (preg_match( "/asia$/i", $params["tld"] )) {
						$postfields["attr-name1"] = "locality";
						$postfields["attr-value1"] = $params["country"];
						$postfields["attr-name2"] = "legalentitytype";
						$postfields["attr-value2"] = $params["additionalfields"]["Legal Type"];
						$postfields["attr-name3"] = "identform";
						$postfields["attr-value3"] = $params["additionalfields"]["Identity Form"];
						$postfields["attr-name4"] = "identnumber";
						$postfields["attr-value4"] = $params["additionalfields"]["Identity Number"];
						$postfields["product-key"] = "dotasia";
					}
					else {
						if (preg_match( "/ru$/i", $params["tld"] )) {
							$postfields["attr-name1"] = "contact-type";
							$postfields["attr-value1"] = $params["additionalfields"]["Contact Type"];

							if ($postfields["attr-value1"] == "ORG") {
								$postfields["attr-name3"] = "org-r";
								$postfields["attr-value3"] = $params["companyname"];
								$postfields["attr-name6"] = "kpp";
								$postfields["attr-value6"] = $params["additionalfields"]["Tax Payer Number"];
								$postfields["attr-name7"] = "code";
								$postfields["attr-value7"] = $params["additionalfields"]["Taxpayer Identification Number"];
							}
							else {
								$postfields["attr-name2"] = "birth-date";
								$postfields["attr-value2"] = $params["additionalfields"]["Birth Date"];
								$postfields["attr-name4"] = "person-r";
								$postfields["attr-value4"] = "" . $params["firstname"] . " " . $params["lastname"];
								$postfields["attr-name8"] = "passport";
								$postfields["attr-value8"] = $params["additionalfields"]["Passport Information"];
							}

							$postfields["attr-name5"] = "address-r";
							$postfields["attr-value5"] = "" . $params["address1"] . " " . $params["city"] . " " . $params["fullstate"] . " " . $params["country"] . " " . $params["postcode"];
						}
						else {
							if (preg_match( "/pro$/i", $params["tld"] )) {
								$postfields["attr-name1"] = "profession";
								$postfields["attr-value1"] = $params["additionalfields"]["Profession"];
								$postfields["product-key"] = "dotpro";
							}
							else {
								if (preg_match( "/nl$/i", $params["tld"] )) {
									$postfields["attr-name1"] = "legalForm";
									$postfields["attr-value1"] = ($params["companyname"] ? "ANDERS" : "PERSOON");
									$postfields["product-key"] = "dotnl";
								}
							}
						}
					}
				}
			}
		}
	}

	return $postfields;
}


function resellerclub_DomainTLDSpecificFields($params, $contactid) {
	$postfields = array();

	if (( preg_match( "/es$/i", $params["tld"] ) || preg_match( "/de$/i", $params["tld"] ) )) {
		$postfields["protect-privacy"] = "0";
	}


	if (preg_match( "/asia$/i", $params["tld"] )) {
		$postfields["attr-name1"] = "cedcontactid";
		$postfields["attr-value1"] = $contactid;
	}


	if (( ( ( preg_match( "/uk$/i", $params["tld"] ) || preg_match( "/eu$/i", $params["tld"] ) ) || preg_match( "/nz$/i", $params["tld"] ) ) || preg_match( "/ru$/i", $params["tld"] ) )) {
		$postfields["admin-contact-id"] = "-1";
		$postfields["tech-contact-id"] = "-1";
		$postfields["billing-contact-id"] = "-1";
	}


	if (preg_match( "/ca$/i", $params["tld"] )) {
		$postfields["billing-contact-id"] = "-1";
	}


	if (preg_match( "/au$/i", $params["tld"] )) {
		$postfields["attr-name1"] = "id-type";
		$postfields["attr-name2"] = "id";
		$postfields["attr-name3"] = "policyReason";
		$postfields["attr-name4"] = "isAUWarranty";
		$postfields["attr-value4"] = "true";
		$postfields["attr-value5"] = "";
		$postfields["attr-value6"] = "";
		$postfields["attr-value7"] = "";
		$val4 = $params["additionalfields"]["Eligibility ID"];
		switch ($params["additionalfields"]["Eligibility ID Type"]) {
		case "Australian Company Number (ACN)": {
				$val5 = "ACN";
				break;
			}

		case "ACT Business Number": {
				$val5 = "ACT BN";
				break;
			}

		case "NSW Business Number": {
				$val5 = "NSW BN";
				break;
			}

		case "NT Business Number": {
				$val5 = "NT BN";
				break;
			}

		case "QLD Business Number": {
				$val5 = "QLD BN";
				break;
			}

		case "SA Business Number": {
				$val5 = "SA BN";
				break;
			}

		case "TAS Business Number": {
				$val5 = "TAS BN";
				break;
			}

		case "VIC Business Number": {
				$val5 = "VIC BN";
				break;
			}

		case "WA Business Number": {
				$val5 = "WA BN";
				break;
			}

		case "Trademark (TM)": {
				$val5 = "TM";
				break;
			}

		case "Australian Business Number (ABN)": {
				$val5 = "ABN";
				break;
			}

		case "Australian Registered Body Number (ARBN)": {
				$val5 = "ARBN";
				break;
			}

		case "Other - Used to record an Incorporated Association number": {
				$val5 = "Other";
				break;
			}

		default: {
				$val5 = "ABN";
			}
		}


		if ($params["additionalfields"]["Eligibility Reason"] == "Domain name is an Exact Match Abbreviation or Acronym of your Entity or Trading Name.") {
			$postfields["attr-value3"] = "1";
		}
		else {
			$postfields["attr-value3"] = "2";
			$postfields["attr-value4"] = "true";
		}

		$postfields["attr-value1"] = $val5;
		$postfields["attr-value2"] = $val4;

		if (( $val5 == "TM" || $val5 == "Other" )) {
			$postfields["attr-name5"] = "eligibilityType";
			$postfields["attr-value5"] = ($val5 == "Other" ? "Other" : "Trademark Owner");
		}

		$regnamevals = array( "VIC BN", "NSW BN", "SA BN", "NT BN", "WA BN", "TAS BN", "ACT BN", "QLD BN", "TM", "Other" );

		if ($val5 == "TM") {
			$postfields["attr-name6"] = "eligibilityName";
			$postfields["attr-value6"] = $params["additionalfields"]["Eligibility Name"];
		}


		if (in_array( $val5, $regnamevals )) {
			$postfields["attr-name7"] = "registrantName";
			$postfields["attr-value7"] = $params["additionalfields"]["Registrant Name"];
		}
	}

	return $postfields;
}


function resellerclub_Sync($params) {
	$domainid = $params["domainid"];
	$domainname = $params["domain"];
	$status = $params["status"];
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["domain-name"] = $domainname;
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["options"] = "All";
	$result = resellerclub_SendCommand( "details", "domains", $postfields, $params, "GET" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	$expirytime = $currentstatus = "";
	$expirytime = $result["endtime"];
	$currentstatus = $result["currentstatus"];

	if ($expirytime) {
		$returndata = array();

		if ($currentstatus == "Active") {
			$returndata["active"] = true;
		}
		else {
			if ($currentstatus == "Expired") {
				$returndata["expired"] = true;
			}
		}

		$returndata["expirydate"] = date( "Y-m-d", $expirytime );
		return $returndata;
	}

	return array( "error" => "No expiry date returned" );
}


function resellerclub_TransferSync($params) {
	$domainid = $params["domainid"];
	$domainname = $params["domain"];
	$status = $params["status"];
	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["domain-name"] = $domainname;
	$orderid = resellerclub_getOrderID( $postfields, $params );

	if (!is_numeric( $orderid )) {
		return array( "error" => $orderid );
	}

	unset( $postfields );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["order-id"] = $orderid;
	$postfields["options"] = "All";
	$result = resellerclub_SendCommand( "details", "domains", $postfields, $params, "GET" );

	if ($result["status"] == "ERROR") {
		return array( "error" => $result["message"] );
	}

	$expirytime = $currentstatus = "";
	$expirytime = $result["endtime"];
	$currentstatus = $result["currentstatus"];

	if ($expirytime) {
		$returndata = array();

		if ($currentstatus == "Active") {
			$returndata["active"] = true;
		}
		else {
			if ($currentstatus == "Expired") {
				$returndata["expired"] = true;
			}
		}

		$returndata["expirydate"] = date( "Y-m-d", $expirytime );
		return $returndata;
	}

	return array( "error" => "No expiry date returned" );
}


function resellerclub_DomainSync($registrar) {
	$lcregistrar = strtolower( $registrar );
	$cronreport = "" . $registrar . " Domain Sync Report<br>
---------------------------------------------------<br>
";
	$params = getRegistrarConfigOptions( $lcregistrar );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$queryresult = select_query( "tbldomains", "id,domain,status", "registrar='" . $lcregistrar . "' AND (status='Pending Transfer' OR status='Active')" );

	while ($data = mysql_fetch_array( $queryresult )) {
		$domainid = $data["id"];
		$domainname = $data["domain"];
		$status = $data["status"];
		$postfields["domain-name"] = $domainname;
		$orderid = resellerclub_getOrderID( $postfields, $params );

		if (!is_numeric( $orderid )) {
			$cronreport .= "Error for " . $domainname . ": " . $orderid . "<br>
";
		}

		unset( $postfields );
		$postfields["auth-userid"] = $params["ResellerID"];
		$postfields["api-key"] = $params["APIKey"];
		$postfields["order-id"] = $orderid;
		$postfields["options"] = "All";
		$result = resellerclub_SendCommand( "details", "domains", $postfields, $params, "GET" );

		if ($result["status"] == "ERROR") {
			$cronreport .= "Error for " . $domainname . ": " . $result["message"] . "<br>
";
		}

		$expirytime = $currentstatus = "";
		$expirytime = $result["endtime"];
		$currentstatus = $result["currentstatus"];

		if ($expirytime) {
			$updateqry = array();

			if ($currentstatus == "Active") {
				$updateqry["status"] = "Active";
			}

			$expirydate = date( "Y-m-d", $expirytime );
			$updateqry["expirydate"] = $expirydate;

			if (count( $updateqry )) {
				update_query( "tbldomains", $updateqry, array( "id" => $domainid ) );
			}


			if (( $status == "Pending Transfer" && $currentstatus == "Active" )) {
				sendMessage( "Domain Transfer Completed", $domainid );
				$cronreport .= "Processed Domain Transfer Completion of " . $domainname . " - Updated expiry to " . fromMySQLDate( $expirydate ) . "<br>
";
			}

			$cronreport .= "Updated " . $domainname . " expiry to " . fromMySQLDate( $expirydate ) . "<br>
";
		}

		$cronreport .= "Error for " . $domainname . ": No expiry date returned<br>
";
	}

	echo $cronreport;
	logActivity( "" . $registrar . " Domain Sync Run" );
	sendAdminNotification( "system", "WHMCS " . $registrar . " Domain Syncronisation Report", $cronreport );
}


function resellerclub_GetIP() {
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, "http://www.whmcs.com/getip/" );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$contents = curl_exec( $ch );
	curl_close( $ch );
	return $contents;
}


function resellerclub_Language($language) {
	$language = strtolower( $language );
	$allowedlanguages = array( "ar", "by", "br", "bg", "ch", "nl", "en", "fi", "fr", "de", "ID", "it", "ja", "me", "pt", "ru", "sk", "sl", "e1", "es", "tr" );
	switch ($language) {
	case "arabic": {
			$language = "ar";
			break;
		}

	case "bulgarian": {
			$language = "bg";
			break;
		}

	case "chinese": {
			$language = "zh";
			break;
		}

	case "dutch": {
			$language = "nl";
			break;
		}

	case "finnish": {
			$language = "fi";
			break;
		}

	case "german": {
			$language = "de";
			break;
		}

	case "italian": {
			$language = "it";
			break;
		}

	case "japanese": {
			$language = "ja";
			break;
		}

	case "portuguese-br": {
			$language = "br";
			break;
		}

	case "portuguese-pt": {
			$language = "pt";
			break;
		}

	case "russian": {
			$language = "ru";
			break;
		}

	case "spanish": {
			$language = "es";
			break;
		}

	case "turkish": {
			$language = "tr";
			break;
		}

	case "english" :
        default :
            $language = "en";
	}

	if (!in_array( $language, $allowedlanguages )) {
			$language = "en";
	}

	if (strlen( $language ) == 2) {
		return $language;
	}

	return "en";
}


function resellerclub_ClientAreaCustomButtonArray($params) {
	global $_LANG;

	$buttonarray = array();

	if ($_SESSION["uid"]) {
		$xxxcount = get_query_val( "tbldomains", "count(*)", array( "userid" => $_SESSION["uid"], "status" => "Active", "domain" => array( "sqltype" => "LIKE", "value" => ".xxx" ) ) );

		if ($xxxcount) {
			$buttonarray[$_LANG["xxxmembershipidupdate"]] = "UpdateXXX";
		}
	}

	return $buttonarray;
}


function resellerclub_UpdateXXX($params) {
	if (!preg_match( "/xxx$/i", $params["tld"] )) {
		return array( "error" => "Incorrect TLD" );
	}


	if ($_POST["membershipid"]) {
		if (preg_match( "/xxx$/i", $params["tld"] )) {
			unset( $postfields );
			$postfields["auth-userid"] = $params["ResellerID"];
			$postfields["api-key"] = $params["APIKey"];
			$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
			$orderid = resellerclub_getOrderID( $postfields, $params );
			unset( $postfields );

			if (is_numeric( $orderid )) {
				$postfields["auth-userid"] = $params["ResellerID"];
				$postfields["api-key"] = $params["APIKey"];
				$postfields["order-id"] = $orderid;
				$postfields["association-id"] = $_POST["membershipid"];
				$result = resellerclub_SendCommand( "association-details", "domains/dotxxx", $postfields, $params, "POST" );

				if (strtoupper( $result["status"] ) == "ERROR") {
					$error = $result["message"];
				}


				if ($result["actionstatus"] == "Failed") {
					$error = true;
				}


				if (( $error && $result["actionstatusdesc"] )) {
					$error = $result["actionstatusdesc"];
				}

				foreach ($result["hashtable"]["entry"] as $id => $values) {

					if (( $values["string"][0] == "actionstatus" && $values["string"][1] == "Failed" )) {
						$error = true;
					}


					if (( $values["string"][0] == "actionstatusdesc" && $error == true )) {
						$error = $values["string"][1];
						continue;
					}
				}


				if (!$error) {
					$memberid = update_query( "tbldomainsadditionalfields", array( "value" => $_POST["membershipid"] ), array( "name" => "Membership Token/ID", "domainid" => $params["domainid"] ) );
					$success = true;
				}
			}
			else {
				$error = $orderid;
			}
		}
	}

	$postfields = array();
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$postfields["domain-name"] = $params["sld"] . "." . $params["tld"];
	$orderid = resellerclub_getOrderID( $postfields, $params );
	$memberid = get_query_val( "tbldomainsadditionalfields", "value", array( "name" => "Membership Token/ID", "domainid" => $params["domainid"] ) );
	$retarray = array( "templatefile" => "updatexxx", "vars" => array( "order-id" => $orderid, "domain" => $postfields["domain-name"], "membershipid" => $memberid ) );

	if ($error) {
		$retarray["vars"]["error"] = $error;
	}


	if ($success) {
		$retarray["vars"]["success"] = $success;
	}

	return $retarray;
}


function resellerclub_validateABN($abn) {
	$abnarray = str_split( $abn );
	$weights = array( 10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19 );
	$abnarray -= 5;
	foreach ($abnarray as $id => $num) {
		$abnarray[$id] = $abnarray[$id] * $weights[$id];
		$abnsum += $abnarray[$id];
	}


	if ($abnsum % 89) {
		return false;
	}

	return true;
}


function resellerclub_ContactType($tld) {
	if (preg_match( "/uk$/i", $tld )) {
		$contacttype = "UkContact";
	}
	else {
		if (preg_match( "/eu$/i", $tld )) {
			$contacttype = "EuContact";
		}
		else {
			if (preg_match( "/cn$/i", $tld )) {
				$contacttype = "CnContact";
			}
			else {
				if (preg_match( "/co$/i", $tld )) {
					$contacttype = "CoContact";
				}
				else {
					if (preg_match( "/ca$/i", $tld )) {
						$contacttype = "CaContact";
					}
					else {
						if (preg_match( "/es$/i", $tld )) {
							$contacttype = "EsContact";
						}
						else {
							if (preg_match( "/de$/i", $tld )) {
								$contacttype = "DeContact";
							}
							else {
								if (preg_match( "/ru$/i", $tld )) {
									$contacttype = "RuContact";
								}
								else {
									if (preg_match( "/nl$/i", $tld )) {
										$contacttype = "NlContact";
									}
									else {
										$contacttype = "Contact";
									}
								}
							}
						}
					}
				}
			}
		}
	}

	return $contacttype;
}


function resellerclub_addCustomer($params) {
	global $CONFIG;

	require ROOTDIR . "/includes/countriescallingcodes.php";

	if (!function_exists( "getClientsDetails" )) {
		require ROOTDIR . "/includes/clientfunctions.php";
	}

	$clientdetails = foreignChrReplace( getClientsDetails( $params["userid"] ) );
	$language = ($clientdetails["language"] ? $clientdetails["language"] : $CONFIG["Language"]);
	$language = resellerclub_Language( $language );
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["username"] = $clientdetails["email"];
	$postfields["passwd"] = resellerclub_genLBRandomPW();
	$postfields["name"] = $clientdetails["firstname"] . " " . $clientdetails["lastname"];
	$companyname = $clientdetails["companyname"];

	if (!$companyname) {
		$companyname = "N/A";
	}

	$postfields["company"] = $companyname;
	$postfields["address-line-1"] = substr( $clientdetails["address1"], 0, 64 );

	if (64 < $clientdetails["address1"]) {
		$postfields["address-line-2"] = substr( $clientdetails["address1"] . ", " . $clientdetails["address2"], 64, 128 );
	}
	else {
		$postfields["address-line-2"] = substr( $clientdetails["address2"], 0, 64 );
	}

	$postfields["city"] = $clientdetails["city"];

	if ($params["country"] != "US") {
		$postfields["state"] = $clientdetails["state"];
	}
	else {
		$postfields["state"] = convertStateToCode( $clientdetails["state"], $clientdetails["country"] );
	}

	$postfields["zipcode"] = $clientdetails["postcode"];
	$postfields["country"] = $clientdetails["country"];
	$phonenumber = $clientdetails["phonenumber"];
	$phonenumber = preg_replace( "/[^0-9]/", "", $phonenumber );
	$countrycode = $clientdetails["country"];
	$countrycode = $countrycallingcodes[$countrycode];
	$postfields["phone-cc"] = $countrycode;
	$postfields["phone"] = $phonenumber;
	$postfields["lang-pref"] = "" . $language;
	$result = resellerclub_SendCommand( "signup", "customers", $postfields, $params, "POST" );
	unset( $postfields );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	$customerid = $result;
	return $customerid;
}


function resellerclub_addContact($params, $customerid, $contacttype, $canonindv = false) {
	require ROOTDIR . "/includes/countriescallingcodes.php";
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["customer-id"] = $customerid;
	$postfields["email"] = $params["email"];

	if ($canonindv) {
		$postfields["name"] = $params["companyname"];
		$postfields["company"] = "N/A";
	}
	else {
		$postfields["name"] = $params["firstname"] . " " . $params["lastname"];
		$companyname = $params["companyname"];

		if (!$companyname) {
			$companyname = "N/A";
		}

		$postfields["company"] = $companyname;
	}

	$postfields["address-line-1"] = substr( $params["address1"], 0, 64 );

	if (64 < $params["address1"]) {
		$postfields["address-line-2"] = substr( $params["address1"] . ", " . $params["address2"], 64, 128 );
	}
	else {
		$postfields["address-line-2"] = substr( $params["address2"], 0, 64 );
	}

	$postfields["city"] = $params["city"];

	if ($params["country"] != "US") {
		$postfields["state"] = $params["fullstate"];
	}
	else {
		$postfields["state"] = $params["state"];
	}

	$postfields["zipcode"] = $params["postcode"];
	$postfields["country"] = $params["country"];
	$phonenumber = $params["phonenumber"];
	$phonenumber = preg_replace( "/[^0-9]/", "", $phonenumber );
	$countrycode = $params["country"];
	$countrycode = $countrycallingcodes[$countrycode];
	$postfields["phone-cc"] = $countrycode;
	$postfields["phone"] = $phonenumber;
	$postfields["type"] = $contacttype;
	$postfields = array_merge( $postfields, resellerclub_ContactTLDSpecificFields( $params ) );
	$result = resellerclub_SendCommand( "add", "contacts", $postfields, $params, "POST" );

	if (strtoupper( $result["status"] ) == "ERROR") {
		if (!$result["message"]) {
			$result["message"] = $result["error"];
		}

		return array( "error" => $result["message"] );
	}

	$contactid = $result;

	if (preg_match( "/es$/i", $params["tld"] )) {
		$postfields["company"] = "N/A";
		$params["additionalfields"]["Contact ID Form Type"] = explode( "|", $params["additionalfields"]["Contact ID Form Type"] );
		$idtype = $params["additionalfields"]["Contact ID Form Type"][0];
		$idnumber = $params["additionalfields"]["Contact ID Form Number"];
		$postfields["attr-name1"] = "es_form_juridica";
		$postfields["attr-value1"] = "1";
		$postfields["attr-name2"] = "es_tipo_identificacion";
		$postfields["attr-value2"] = "" . $idtype;
		$postfields["attr-name3"] = "es_identificacion";
		$postfields["attr-value3"] = "" . $idnumber;
		$postfields["product-key"] = "dotes";
		$result = resellerclub_SendCommand( "add", "contacts", $postfields, $params, "POST" );

		if (strtoupper( $result["status"] ) == "ERROR") {
			if (!$result["message"]) {
				$result["message"] = $result["error"];
			}

			return array( "error" => $result["message"] );
		}

		$additionalid = $result;
	}


	if (preg_match( "/ca$/i", $params["tld"] )) {
		$postfields["name"] = $params["firstname"] . " " . $params["lastname"];
		$companyname = $params["companyname"];

		if (!$companyname) {
			$companyname = "N/A";
		}

		$postfields["company"] = $companyname;
		$postfields["attr-name1"] = "CPR";
		$postfields["attr-value1"] = "CCT";
		$result = resellerclub_SendCommand( "add", "contacts", $postfields, $params, "POST" );

		if (strtoupper( $result["status"] ) == "ERROR") {
			if (!$result["message"]) {
				$result["message"] = $result["error"];
			}

			return array( "error" => $result["message"] );
		}

		$additionalid = $result;
	}

	unset( $postfields );
	return array( "contactid" => $contactid, "additionalid" => $additionalid );
}


function resellerclub_getClientEmail($userid) {
	return get_query_val( "tblclients", "email", array( "id" => $userid ) );
}


function resellerclub_addCOOPSponsor($params) {
	$postfields["auth-userid"] = $params["ResellerID"];
	$postfields["api-key"] = $params["APIKey"];
	$postfields["customer-id"] = $customerid;
	$postfields["name"] = $params["additionalfields"]["Contact Name"];
	$postfields["email"] = $params["additionalfields"]["Contact Email"];
	$postfields["company"] = $params["additionalfields"]["Contact Company"];
	$postfields["address-line-1"] = $params["additionalfields"]["Address 1"];

	if ($params["additionalfields"]["Address 2"]) {
		$postfields["address-line-2"] = $params["additionalfields"]["Address 2"];
	}

	$postfields["city"] = $params["additionalfields"]["City"];
	$postfields["state"] = $params["additionalfields"]["State"];
	$postfields["zipcode"] = $params["additionalfields"]["ZIP Code"];
	$postfields["country"] = $params["additionalfields"]["Country"];
	$postfields["phone-cc"] = $params["additionalfields"]["Phone CC"];
	$postfields["phone"] = $params["additionalfields"]["Phone"];
	$result = resellerclub_SendCommand( "add-sponsor", "contacts/coop", $postfields, $params, "POST" );
	return $result;
}


?>