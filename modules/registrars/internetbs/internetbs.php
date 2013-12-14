<?php
/**
 * live api server url
 */
define ( 'API_SERVER_URL', 'https://api.internet.bs/' );
/**
 * api test server url, when $params['TestMode']='on' is used, then this url will be used
 */
define ( 'API_TESTSERVER_URL', 'https://testapi.internet.bs/' );

$internetbs_last_error = null;

function internetbs_getLastError() {
	global $internetbs_last_error;
	return $internetbs_last_error;
}

/**
 * runs an api command and returns parsed data
 *
 * @param string $commandUrl
 * @param array $postData
 * @param string $errorMessage if cannot connect to server
 * @return array
 */
function internetbs_runCommand($commandUrl, $postData) {
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $commandUrl );
	
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_USERAGENT, "Internet.bs WHMCS module V2.5.4");
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postData );
	
	$data = curl_exec ( $ch );
	
	global $internetbs_last_error;
	$internetbs_last_error = curl_error ( $ch );

	if(internetbs_isDebug()){
		internetbs_debugLog($commandUrl."\n================================\n".print_r($postData, true));
	}
	
	curl_close ( $ch );
	
	return (($data === false) ? false : internetbs_parseResult ( $data ));
}


function internetbs_debugLog($data)	{
	if (internetbs_isDebug())	file_put_contents("/tmp/whmcs.log",$data,FILE_APPEND);
}
    
function internetbs_isDebug()	{
	return (false);
}


function internetbs_getConnectionErrorMessage($message) {
	return 'Cannot conenct to server. [' . $message . ']';
}

function internetbs_getConfigArray() {
	$configarray = array (
			"Username"        => array("Type" => "text", "Size" => "50", "Description" => "Enter your Internet.bs ApiKey here" ), 
			"Password"        => array("Type" => "password", "Size" => "50", "Description" => "Enter your Internet.bs password here" ), 
			"TestMode"        => array("Type" => "yesno",'Description'=>"Check this checkbox if you want to connect to the test server" ),
			"HideWhoisData"   => array("Type" => "yesno",'Description'=>"Tick this box if you want to hide the information in the public whois for Admin/Billing/Technical contacts (.it)"),
			"SyncNextDueDate" => array("Type" => "yesno",'Description'=>"Tick this box if you want the expiry date sync script to update both expiry and next due dates (cron must be configured). If left unchecked it will only update the domain expiration date.")
	);
	return $configarray;
}

/**
 * parse result
 * format: array('name' => value)
 *
 * @param string $data
 * @return array
 */
function internetbs_parseResult($data) {
	
	
	if(internetbs_isDebug()){
		internetbs_debugLog($data);
	}
	
	$result = array ();
	$arr = explode ( "\n", $data );
	foreach ( $arr as $str ) {
		list ( $varName, $value ) = explode ( "=", $str, 2 );
		$varName = trim ( $varName );
		$value = trim ( $value );
		$result [$varName] = $value;
	}
	
	return $result;
}

/**
 * Expiration date sync
 * @param $parameters
 */
function internetbs_Sync($params){
    $username = $params ["Username"];
    $password = $params ["Password"];
    $testmode = $params ["TestMode"];
    $tld = $params ["tld"];
    $sld = $params ["sld"];
	
    $domainName = $sld . '.' . $tld;
    $apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
    $commandUrl = $apiServerUrl . 'Domain/Info';

    $data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName );

    $result = internetbs_runCommand ( $commandUrl, $data );
    $errorMessage = internetbs_getLastError ();
    $values=array();
    if ($result === false) {
        $values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
    } else if ($result ['status'] == 'FAILURE') {
        $values ["error"] = $result ['message'];
    } else {
        //success
        if($result["domainstatus"]=="EXPIRED"){
            $values["expired"]=true;

        } else if($result["domainstatus"]!='PENDING TRANSFER'){
            $values["active"]=true;
        }
        if(isset($result['expirationdate']) && $result['expirationdate']!='n/a'){
            $values["expirydate"]=str_replace("/","-",$result['expirationdate']);
        }
        return $values;

    }
}
/**
 * Expiration date sync
 * @param $parameters
 */
function internetbs_TransferSync($params){
    return internetbs_Sync($params);
}


/**
 * gets list of nameservers for a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_GetNameservers($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Info';
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName );
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	} else {
		// possible number of hosts exists
		$i = 0;
		while ( isset ( $result ['nameserver_' . $i] ) ) {
			$values ['ns' . ($i + 1)] = $result ['nameserver_' . $i];
			++ $i;
		}
	}
	
	return $values;
}

/**
 * attach nameserver to a domain by Domain/Update command
 *
 * @param array $params
 * @return array
 */
function internetbs_SaveNameservers($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];

	# code to save the nameservers
	$nslist = array ();
	for($i = 1; $i <= 5; $i ++) {
		if (isset ( $params ["ns$i"] )) {
			if (isset ( $params ['ns' . $i . '_ip'] ) && strlen ( $params ['ns' . $i . '_ip'] )) {
				$params ["ns$i"] .= ' ' . $params ['ns' . $i . '_ip'];
			}
			array_push ( $nslist, $params ["ns$i"] );
		}
	}
	
	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Update';
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName, 'ns_list' => trim ( implode ( ',', $nslist ), "," ) );
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	}
	
	return $values;
}

/**
 * gets registrar lock status of a domain
 *
 * @param array $params
 * @return string
 */
function internetbs_GetRegistrarLock($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	# code to get the lock status
	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/RegistrarLock/Status';
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName );
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'SUCCESS') {
		if ($result ['registrar_lock_status'] == 'LOCKED') {
			$lockstatus = "locked";
		} else {
			$lockstatus = "unlocked";
		}
	}
	
	return (strlen ( $lockstatus ) ? $lockstatus : $values);
}

/**
 * enable/disable registrar lock for a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_SaveRegistrarLock($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	# code to save the registrar lock
	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	// if lockenabled is set, we need to run lock enable command
	if ($params ["lockenabled"] == "locked") {
		//$lockstatus="locked";
		$resourcePath = 'Domain/RegistrarLock/Enable';
	} else {
		//$lockstatus="unlocked";
		$resourcePath = 'Domain/RegistrarLock/Disable';
	}
	$commandUrl = $apiServerUrl . $resourcePath;
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName );
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	}
	
	return $values;
}

/**
 * gets email forwarding rules list of a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_GetEmailForwarding($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	# code to get email forwarding - the result should be an array of prefixes and forward to emails (max 10)
	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/EmailForward/List';
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName );
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	} else {
		$totalRules = $result ['total_rules'];
		for($i = 1; $i <= $totalRules; $i ++) {
			// prefix is the first part before @ at email addrss
			list ( $prefix, $domainName ) = explode ( '@', $result ['rule_' . $i . '_source'] );
			$values [$i] ["prefix"] = $prefix;
			$values [$i] ["forwardto"] = $result ['rule_' . $i . '_destination'];
		}
	}
	
	return $values;
}

/**
 * saves email forwarding rules of a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_SaveEmailForwarding($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	#code to save email forwarders
	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	
	$data = array ('apikey' => $username, 'password' => $password );
	
	$errorMessages = '';
	$rules = internetbs_GetEmailForwarding ( $params );
	if(is_array($rules)) {
    	foreach ( $rules as $rule ) {
    		$source = $rule ["prefix"] . "@" . $domainName;
    		$source = urlencode ( $source );
    		$cmdData = array ("source" => $source );
    		$cmdData = array_merge ( $cmdData, $data );
    		$cmd = $apiServerUrl . 'Domain/EmailForward/Remove';
    		$error = '';
    		internetbs_runCommand ( $cmd, $cmdData );
    	}
	}
	foreach ( $params ["prefix"] as $key => $value ) {
		$from = $params ["prefix"] [$key];
		$to = $params ["forwardto"] [$key];
		if (trim ( $to ) == '')
			continue;
		
		$data ['source'] = urlencode ( $from . '@' . $domainName );
		$data ['destination'] = urlencode ( $to );
		$commandUrl = $apiServerUrl . 'Domain/EmailForward/Add';
		// try to add rule
		$result = internetbs_runCommand ( $commandUrl, $data );
		$errorMessage = internetbs_getLastError ();
		if ($result === false) {
			$errorMessages .= internetbs_getConnectionErrorMessage ( $errorMessage );
		}
	}
	// error occurs
	if (strlen ( $errorMessages )) {
		$values ["error"] = $errorMessages;
	}
	
	return $values;

}

/**
 * gets DNS Record list of a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_GetDNS($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	# code here to get the current DNS settings - the result should be an array of hostname, record type, and address
	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/DnsRecord/List';
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName );
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else {
		if (is_array ( $result )) {
			$keys = array_keys ( $result );
			$temp = 0;
			foreach ( $keys as $key ) {
				if (strpos ( $key, 'records_' ) === 0) {
					$recNo = substr ( $key, 8 );
					$recNo = substr ( $recNo, 0, strpos ( $recNo, "_" ) );
					if ($recNo > $temp) {
						$temp = $recNo;
					}
				}
			}
		}
		
		$hostrecords = array ();
		$totalRecords = $temp + 1;
		for($i = 0; $i < $totalRecords; $i ++) {
			$recordType = '';
			if (isset ( $result ['records_' . $i . '_type'] )) {
				$recordType = trim ( $result ['records_' . $i . '_type'] );
			}
			if (! in_array ( strtolower ( $recordType ), array ("a", "mx", "cname", 'txt','aaaa','txt' ) ))
				continue;
			if (isset ( $result ['records_' . $i . '_name'] )) {
				$recordHostname = $result ['records_' . $i . '_name'];
				$recordHostname = strrev ( substr ( strrev ( $recordHostname ), mb_strlen ( $domainName, 'ASCII' ) + 1 ) );
			}
			if (isset ( $result ['records_' . $i . '_value'] )) {
				$recordAddress = $result ['records_' . $i . '_value'];
			}
			if (isset ( $result ['records_' . $i . '_name'] )) {
				$hostrecords [] = array ("hostname" => $recordHostname, "type" => $recordType, "address" => $recordAddress,'priority'=>$result ['records_' . $i . '_priority'] );
			}
        }
		
		$commandUrl = $apiServerUrl . "Domain/UrlForward/List";
		$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName );
		$result = internetbs_runCommand ( $commandUrl, $data );
		$errorMessage = internetbs_getLastError ();
		if ($result === false) {
			$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
		} else {
			$totalRecords = ( int ) $result ['total_rules'] + 1;
			for($i = 0; $i < $totalRecords; $i ++) {
				$recordType = '';
				if (isset ( $result ['rule_' . $i . '_isframed'] )) {
					$recordType = trim ( $result ['rule_' . $i . '_isframed'] ) == 'YES' ? "FRAME" : 'URL';
				}
				if (isset ( $result ['rule_' . $i . '_source'] )) {
					$recordHostname = $result ['rule_' . $i . '_source'];
					$recordHostname = strrev ( substr ( strrev ( $recordHostname ), mb_strlen ( $domainName, 'ASCII' ) + 1 ) );
				}
				if (isset ( $result ['rule_' . $i . '_destination'] )) {
					$recordAddress = $result ['rule_' . $i . '_destination'];
				}
				if (isset ( $result ['rule_' . $i . '_source'] )) {
					$hostrecords [] = array ("hostname" => $recordHostname, "type" => $recordType, "address" => $recordAddress);
				}
			}
		}
	}
	
	return (count ( $hostrecords ) ? $hostrecords : $values);

}

/**
 * saves dns records for a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_SaveDNS($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	
	$data = array ('apikey' => $username, 'password' => $password );
	
	$errorMessages = '';
	$recs = internetbs_GetDNS ( $params );
	if(is_array($recs)) {
    	foreach ( $recs as $r ) {
    		$source = $r ["hostname"] . ".$domainName";
    		$source = trim ( $source, ". " );
    		$type = $r ["type"];
    		$remParams = array ();
    		if ($type == "FRAME" || $type == "URL") {
    			$cmdPath = "Domain/UrlForward/Remove";
    			$remParams ["source"] = $source;
    		} else {
    			$cmdPath = "Domain/DnsRecord/Remove";
    			$remParams ["FullRecordName"] = $source;
    			$remParams ["type"] = $type;
    		}
    		$remParams = array_merge ( $remParams, $data );
    		$cmdPath = $apiServerUrl . $cmdPath;
    		internetbs_runCommand ( $cmdPath, $remParams );
    	}
	}
	# Loop through the submitted records
	foreach ( $params ["dnsrecords"] as $key => $values ) {
		$hostname = $values ["hostname"];
		$type = $values ["type"];
		$address = $values ["address"];
		if (trim ( $hostname ) === '' && trim ( $address ) == '')
			continue;
			
		# code to update the record
		if (($hostname != $domainName) && strpos ( $hostname, '.' . $domainName ) === false) {
			$hostname = $hostname . '.' . $domainName;
		}
		$cmdData = array ();
		if (! ($type == 'URL' || $type == 'FRAME')) {
			$cmdData ['fullrecordname'] = trim ( $hostname, ". " );
			$cmdData ['type'] = $type;
			$cmdData ['value'] = $address;
            $cmdData['priority']=intval($values["priority"]);
			$commandUrl = $apiServerUrl . 'Domain/DnsRecord/Add';
		} else {
			$cmdData ['source'] = trim ( $hostname, ". " );
			$cmdData ['isFramed'] = $type == 'FRAME' ? 'YES' : 'NO';
			$cmdData ['Destination'] = $address;
			$commandUrl = $apiServerUrl . 'Domain/UrlForward/Add';
		}
		$cmdData = array_merge ( $data, $cmdData );
		
		$result = internetbs_runCommand ( $commandUrl, $cmdData );
		$errorMessage = internetbs_getLastError ();
		if ($result === false) {
			$errorMessages .= internetbs_getConnectionErrorMessage ( $errorMessage );
		}
	}
	
	# If error, return the error message in the value below
	if (strlen ( $errorMessages )) {
		$values ["error"] = $errorMessages;
	}
	
	return $values;
}

/**
 * registers a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_RegisterDomain($params) {
	
	$params=internetbs_get_utf8_parameters($params);

	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$hideWhoisData = (isset($params ["HideWhoisData"]) && ('on' == strtolower($params ["HideWhoisData"])))?'YES':'NO';

	$tld = $params ["tld"];
	$sld = $params ["sld"];
	$regperiod = intval ( $params ["regperiod"] );

	# Registrant Details
	$RegistrantFirstName = $params ["firstname"];
	$RegistrantLastName = $params ["lastname"];
	$RegistrantCompany= trim($params["companyname"]);
	$RegistrantAddress1 = $params ["address1"];
	$RegistrantAddress2 = $params ["address2"];
	$RegistrantCity = $params ["city"];
	$RegistrantStateProvince = $params ["state"];
	$RegistrantPostalCode = $params ["postcode"];
	$RegistrantCountry = $params ["country"];
	$RegistrantEmailAddress = $params ["email"];
	$RegistrantPhone = internetbs_reformatPhone ( $params ["phonenumber"], $params ["country"] );
	# Admin Details
	$AdminFirstName = $params ["adminfirstname"];
	$AdminLastName = $params ["adminlastname"];
	$AdminCompany= trim($params["admincompanyname"]);
	$AdminAddress1 = $params ["adminaddress1"];
	$AdminAddress2 = $params ["adminaddress2"];
	$AdminCity = $params ["admincity"];
	$AdminStateProvince = $params ["adminstate"];
	$AdminPostalCode = $params ["adminpostcode"];
	$AdminCountry = $params ["admincountry"];
	$AdminEmailAddress = $params ["adminemail"];
	$AdminPhone = internetbs_reformatPhone ( $params ["adminphonenumber"], $params ["admincountry"] );
	# Put your code to register domain here

	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Create';
	
	$nslist = array ();
	for($i = 1; $i <= 5; $i ++) {
		if (isset ( $params ["ns$i"] )) {
			array_push ( $nslist, $params ["ns$i"] );
		}
	}
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName, 

	// registrant contact data
	'registrant_firstname' => $RegistrantFirstName, 'registrant_lastname' => $RegistrantLastName, 'registrant_street' => $RegistrantAddress1, 'registrant_street2' => $RegistrantAddress2, 'registrant_city' => $RegistrantCity, 'registrant_countrycode' => $RegistrantCountry, 'registrant_postalcode' => $RegistrantPostalCode, 'registrant_email' => $RegistrantEmailAddress, 'registrant_phonenumber' => $RegistrantPhone, 

	// technical contact data
	'technical_firstname' => $AdminFirstName, 'technical_lastname' => $AdminLastName, 'technical_street' => $AdminAddress1, 'technical_street2' => $AdminAddress2, 'technical_city' => $AdminCity, 'technical_countrycode' => $AdminCountry, 'technical_postalcode' => $AdminPostalCode, 'technical_email' => $AdminEmailAddress, 'technical_phonenumber' => $AdminPhone, 

	// admin contact data
	'admin_firstname' => $AdminFirstName, 'admin_lastname' => $AdminLastName, 'admin_street' => $AdminAddress1, 'admin_street2' => $AdminAddress2, 'admin_city' => $AdminCity, 'admin_countrycode' => $AdminCountry, 'admin_postalcode' => $AdminPostalCode, 'admin_email' => $AdminEmailAddress, 'admin_phonenumber' => $AdminPhone, 

	// billing contact data
	'billing_firstname' => $AdminFirstName, 'billing_lastname' => $AdminLastName, 'billing_street' => $AdminAddress1, 'billing_street2' => $AdminAddress2, 'billing_city' => $AdminCity, 'billing_countrycode' => $AdminCountry, 'billing_postalcode' => $AdminPostalCode, 'billing_email' => $AdminEmailAddress, 'billing_phonenumber' => $AdminPhone );
	
	if(!empty($RegistrantCompany)){
		$data["Registrant_Organization"] = $RegistrantCompany;
	}
	if(!empty($AdminCompany)){
		$data["technical_Organization"] = $AdminCompany;
		$data["admin_Organization"] = $AdminCompany;
		$data["billing_Organization"] = $AdminCompany;
	}
	// ns_list is optional
	if (count ( $nslist )) {
		$data ['ns_list'] = trim ( implode ( ',', $nslist ), "," );
	}
	if ($params ['idprotection']) {
		$data ["privateWhois"] = "FULL";
	}
	
	$extarr = explode ( '.', $tld );
	$ext = array_pop ( $extarr );
	
	if ($tld == 'eu' || $tld == 'be' || $ext == 'uk') {
		$data ['registrant_language'] = isset ( $params ['additionalfields'] ['Language'] ) ? $params ['additionalfields'] ['Language'] : 'en';
	}
	
	if($tld=='eu') {
	    
	    $europianLanguages = array("cs","da","de","el","en","es","et","fi","fr","hu","it","lt","lv","mt","nl","pl","pt","sk","sl","sv","ro","bg","ga");
	    if(!in_array($data ['registrant_language'],$europianLanguages)) {
	        $data ['registrant_language']='en';
	    }
	    
	    $europianCountries = array('AT', 'AX', 'BE', 'BG', 'CZ', 'CY', 'DE', 'DK', 'ES', 'EE', 'FI', 'FR', 'GR', 'GB', 'GF', 'GI', 'GP', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'MQ', 'NL', 'PL', 'PT', 'RE', 'RO', 'SE', 'SK', 'SI');
        if(!in_array($RegistrantCountry, $europianCountries)) {
            $RegistrantCountry = 'IT';
        }
        $data['registrant_countrycode'] = $RegistrantCountry;
	}
	
	if($tld=='be') {
	    
	    if(!in_array($data ['registrant_language'],array('en','fr','nl'))) {
	        $data ['registrant_language']='en';
	    }
	    
		// Same as for .EU
        if(!in_array($RegistrantCountry, array("AF","AX","AL","DZ","AS","AD","AO","AI","AQ","AG","AR","AM","AW","AU","AT","AZ","BS","BH","BD","BB","BY","BE","BZ","BJ","BM","BT","BO","BA","BW","BV","BR","IO","VG","BN","BG","BF","BI","KH","CM","CA","CV","KY","CF","TD","CL","CN","CX","CC","CO","KM","CG","CK","CR","HR","CU","CY","CZ","CD","DK","DJ","DM","DO","TL","EC","EG","SV","GQ","ER","EE","ET","FK","FO","FM","FJ","FI","FR","GF","PF","TF","GA","GM","GE","DE","GH","GI","GR","GL","GD","GP","GU","GT","GN","GW","GY","HT","HM","HN","HK","HU","IS","IN","ID","IR","IQ","IE","IM","IL","IT","CI","JM","JP","JO","KZ","KE","KI","KW","KG","LA","LV","LB","LS","LR","LY","LI","LT","LU","MO","MK","MG","MW","MY","MV","ML","MT","MH","MQ","MR","MU","YT","MX","MD","MC","MN","ME","MS","MA","MZ","MM","NA","NR","NP","NL","AN","NC","NZ","NI","NE","NG","NU","NF","KP","MP","NO","OM","PK","PW","PS","PA","PG","PY","PE","PH","PN","PL","PT","PR","QA","RE","RO","RU","RW","SH","KN","LC","PM","VC","WS","SM","ST","SA","SN","RS","SC","SL","SG","SK","SI","SB","SO","ZA","GS","KR","ES","LK","SD","SR","SJ","SZ","SE","CH","SY","TW","TJ","TZ","TH","TG","TK","TO","TT","TN","TR","TM","TC","TV","VI","UG","UA","AE","GB","US","UM","UY","UZ","VU","VA","VE","VN","WF","EH","YE","ZM","ZW"))) {
            $RegistrantCountry = 'IT';
        }
        $data['registrant_countrycode'] = $RegistrantCountry;
	}
	
	
	// ADDED FOR .DE //
	
	if($tld == 'de')
	{
		//mail("hardik.device@gmail.com", "Role", $params['additionalfields']['role']);
		//error_log($params['additionalfields']['role']);
		//file_put_contents("/tmp/errors.txt", $params['additionalfields']['role'],FILE_APPEND);
		if($params['additionalfields']['role']=="ORG")
		{
			$data['registrant_role'] = $params['additionalfields']['role'];
			$data['admin_role'] = "Person";
			$data['technical_role'] = "Role";
			$data['zone_role'] = "Role";
		}
		else
		{
			$data['registrant_role'] = $params['additionalfields']['role'];
			$data['admin_role'] = "Person";
			$data['technical_role'] = "Person";
			$data['zone_role'] = "Person";
		}
		if($params['additionalfields']['tosAgree']!='')
		{
		  $data['tosAgree']="YES";
		}
		else
		{
		   $data['tosAgree']="NO";
		}
		 $data['registrant_sip'] = @$params['additionalfields']['sip']; 
		
		 $data['clientip'] = internetbs_getClientIp();
		 if($params['additionalfields']['Restricted Publication']!='')
		 {
			$data['registrant_discloseName'] = "YES";
			$data['registrant_discloseContact'] = "YES";
			$data['registrant_discloseAddress'] = "YES";
		 }
		 else
		 {
			$data['registrant_discloseName'] = "NO";
			$data['registrant_discloseContact'] = "NO";
			$data['registrant_discloseAddress'] = "NO";
		 }
		 
		 $data['zone_firstname'] = $AdminFirstName;
		 $data['zone_lastname'] = $AdminLastName;
		 $data['zone_email'] = $AdminEmailAddress;
		 $data['zone_phonenumber'] = internetbs_reformatPhone($params["phonenumber"], $params["country"]);		 
		 $data['zone_postalcode'] = $AdminPostalCode;
		 $data['zone_city'] = $AdminCity;
		 $data['zone_street'] = $AdminAddress1;
		 $data['zone_countrycode'] = 'DE';
		 
		 $data['technical_fax'] = @$params['additionalfields']['fax'];
		 $data['zone_fax'] = @$params['additionalfields']['fax'];
	}
	// END OF .DE //
	
	// ADDED FOR .NL //
	
	if($tld == 'nl')
	{
		if($params['additionalfields']['nlTerm']!='')
		{
		  $data['registrant_nlTerm']="YES";
		}
		else
		{
		  $data['registrant_nlTerm']="NO";	
		}
		$data['registrant_clientip'] = internetbs_getClientIp();
		$data['registrant_nlLegalForm'] = $params['additionalfields']['nlLegalForm'];
		$data['technical_nlLegalForm'] = $params['additionalfields']['nlLegalForm'];
		$data['admin_nlLegalForm'] = $params['additionalfields']['nlLegalForm'];
		$data['billing_nlLegalForm'] = $params['additionalfields']['nlLegalForm'];
		$data['registrant_nlRegNumber'] = $params['additionalfields']['nlRegNumber'];
	}
	//END OF .NL //
	
	if($tld=='us')	{
		
		$data['registrant_usnexuscategory'] = $params['additionalfields']['Nexus Category'];
		
		$usDomainPurpose = trim($params['additionalfields']['Application Purpose']);
		
		if(strtolower($usDomainPurpose) == strtolower('Business use for profit'))	{
			$data['registrant_uspurpose'] = 'P1';
		} else if(strtolower($usDomainPurpose) == strtolower('Educational purposes'))	{
			$data['registrant_uspurpose'] = 'P4';
		} else if(strtolower($usDomainPurpose) == strtolower('Personal Use'))	{
			$data['registrant_uspurpose'] = 'P3';
		} else if(strtolower($usDomainPurpose) == strtolower('Government purposes'))	{
			$data['registrant_uspurpose'] = 'P5';
		} else {
			$data['registrant_uspurpose'] = 'P2';
		}
		
		$data['registrant_usnexuscategory'] = $params['additionalfields']['Nexus Category'];
		$data['registrant_usnexuscountry'] = $params['additionalfields']['Nexus Country'];
	}
	
	if ($ext == 'uk') {
		
		$legalType = $params ['additionalfields'] ['Legal Type'];
		$dotUKOrgType = "";
		switch ($legalType) {
			case "Individual" :
				$dotUKOrgType = "IND";
				break;
			case "UK Limited Company" :
				$dotUKOrgType = "LTD";
				break;
			case "UK Public Limited Company" :
				$dotUKOrgType = "PLC";
				break;
			case "UK Partnership" :
				$dotUKOrgType = "PTNR";
				break;
			case "UK Limited Liability Partnership" :
				$dotUKOrgType = "LLP";
				break;
			case "Sole Trader" :
				$dotUKOrgType = "STRA";
				break;
			case "UK Registered Charity" :
				$dotUKOrgType = "RCHAR";
				break;
			case "UK Entity (other)" :
				$dotUKOrgType = "OTHER";
				break;
			case "Foreign Organization" :
				$dotUKOrgType = "FCORP";
				break;
			case "Other foreign organizations" :
				$dotUKOrgType = "FOTHER";
				break;
		}
		
		if (in_array ( $dotUKOrgType, array ('LTD', 'PLC', 'LLP', 'IP', 'SCH', 'RCHAR' ) )) {
			$data ['registrant_dotUkOrgNo'] = $params ['additionalfields'] ['Company ID Number'];
			$data ['registrant_dotUKRegistrationNumber'] = $params ['additionalfields'] ['Company ID Number'];
		}
		
		// organization type
		$data ['registrant_dotUKOrgType'] = isset ( $params ['additionalfields'] ['Legal Type'] ) ? $dotUKOrgType : 'IND';
		if ($data ['registrant_dotUKOrgType'] == 'IND') {
			// hide data in private whois? (Y/N)
			$data ['registrant_dotUKOptOut'] = 'N';
		}
		
		$data ['registrant_dotUKLocality'] = $AdminCountry;
	}
	
	if ($tld == 'asia') {
	    
	    $asianCountries= array("AF","AQ","AM","AU","AZ","BH","BD","BT","BN","KH","CN","CX","CC","CK","CY","FJ","GE","HM","HK","IN","ID","IR","IQ","IL","JP","JO","KZ","KI","KP","KR","KW","KG","LA","LB","MO","MY","MV","MH","FM","MN","MM","NR","NP","NZ","NU","NF","OM","PK","PW","PS","PG","PH","QA","WS","SA","SG","SB","LK","SY","TW","TJ","TH","TL","TK","TO","TR","TM","TV","AE","UZ","VU","VN","YE");
	    if(!in_array($RegistrantCountry, $asianCountries)) {
	        $RegistrantCountry = 'BD';
	    }
	    $data['registrant_countrycode'] = $RegistrantCountry;
	    
		$data ['registrant_dotASIACedLocality'] = $RegistrantCountry;
		$data ['registrant_dotasiacedentity'] = $params ['additionalfields'] ['Legal Entity Type'];
		if ($data ['registrant_dotasiacedentity'] == 'other') {
			$data ['registrant_dotasiacedentityother'] = isset ( $params ['additionalfields'] ['Other legal entity type'] ) ? $params ['additionalfields'] ['Other legal entity type'] : 'otheridentity';
		}
		$data ['registrant_dotasiacedidform'] = $params ['additionalfields'] ['Identification Form'];
		if ($data ['registrant_dotasiacedidform'] != 'other') {
			$data ['registrant_dotASIACedIdNumber'] = $params ['additionalfields'] ['Identification Number'];
		}
		if ($data ['registrant_dotasiacedidform'] == 'other') {
			$data ['registrant_dotasiacedidformother'] = isset ( $params ['additionalfields'] ['Other identification form'] ) ? $params ['additionalfields'] ['Other identification form'] : 'otheridentity';
		}
	}
	
	if (in_array($ext, array('fr','re','pm','tf','wf','yt'))) {
	    
		$holderType = isset ( $params ['additionalfields'] ['Holder Type'] ) ? $params ['additionalfields'] ['Holder Type'] : 'individual';
		$data ['registrant_dotfrcontactentitytype'] = $holderType;
		$data ['admin_dotfrcontactentitytype'] = $holderType;
		
		switch ($holderType) {
			case 'individual' :
				$data ["registrant_dotfrcontactentitybirthdate"] = $params ['additionalfields'] ['Birth Date YYYY-MM-DD'];
				$data ['registrant_dotfrcontactentitybirthplacecountrycode'] = $params ['additionalfields'] ['Birth Country Code'];
				$data ['admin_dotfrcontactentitybirthdate'] = $params ['additionalfields'] ['Birth Date YYYY-MM-DD'];
				$data ['admin_dotfrcontactentitybirthplacecountrycode'] = $params ['additionalfields'] ['Birth Country Code'];
				if (strtolower ( $params ['additionalfields'] ['Birth Country Code'] ) == 'fr') {
					$data ['registrant_dotFRContactEntityBirthCity'] = $params ['additionalfields'] ['Birth City'];
					$data ['registrant_dotFRContactEntityBirthPlacePostalCode'] = $params ['additionalfields'] ['Birth Postal code'];
					$data ['admin_dotFRContactEntityBirthCity'] = $params ['additionalfields'] ['Birth City'];
					$data ['admin_dotFRContactEntityBirthPlacePostalCode'] = $params ['additionalfields'] ['Birth Postal code'];
				}
				$data ['registrant_dotFRContactEntityRestrictedPublication'] = isset ( $params ['additionalfields'] ['Restricted Publication'] ) ? 1 : 0;
				$data ['admin_dotFRContactEntityRestrictedPublication'] = isset ( $params ['additionalfields'] ['Restricted Publication'] ) ? 1 : 0;
				break;
			case 'company':
				$data ['registrant_dotFRContactEntitySiren'] = trim($params ['additionalfields'] ['Siren']);
				$data ['admin_dotFRContactEntitySiren'] = trim($params ['additionalfields'] ['Siren']);
				break;
			case 'trademark':
				$data ['registrant_dotFRContactEntityTradeMark'] = $params ['additionalfields'] ['Trade Mark'];
				$data ['admin_dotFRContactEntityTradeMark'] = $params ['additionalfields'] ['Trade Mark'];
				break;
			case 'association' :
				if (isset ( $params ['additionalfields'] ['Waldec'] )) {
					$data ['registrant_dotFRContactEntityWaldec'] = $params ['additionalfields'] ['Waldec'];
					$data ['admin_dotFRContactEntityWaldec'] = $params ['additionalfields'] ['Waldec'];
				} else {
					$data ['registrant_dotfrcontactentitydateofassociation'] = $params ['additionalfields'] ['Date of Association YYYY-MM-DD'];
					$data ['registrant_dotFRContactEntityDateOfPublication'] = $params ['additionalfields'] ['Date of Publication YYYY-MM-DD'];
					$data ['registrant_dotfrcontactentityannouceno'] = $params ['additionalfields'] ['Annouce No'];
					$data ['registrant_dotFRContactEntityPageNo'] = $params ['additionalfields'] ['Page No'];
					$data ['admin_dotfrcontactentitydateofassociation'] = $params ['additionalfields'] ['Date of Association YYYY-MM-DD'];
					$data ['admin_dotFRContactEntityDateOfPublication'] = $params ['additionalfields'] ['Date of Publication YYYY-MM-DD'];
					$data ['admin_dotfrcontactentityannouceno'] = $params ['additionalfields'] ['Annouce No'];
					$data ['admin_dotFRContactEntityPageNo'] = $params ['additionalfields'] ['Page No'];
				}
				
				break;
			case 'other' :
				$data ['registrant_dotFROtherContactEntity'] = $params ['additionalfields'] ['Other Legal Status'];
				$data ['admin_dotFROtherContactEntity'] = $params ['additionalfields'] ['Other Legal Status'];
				if (isset ( $params ['additionalfields'] ['Siren'] )) {
					$data ['registrant_dotFRContactEntitySiren'] = $params ['additionalfields'] ['Siren'];
					$data ['admin_dotFRContactEntitySiren'] = $params ['additionalfields'] ['Siren'];
				} else if (isset ( $params ['additionalfields'] ['Trade Mark'] )) {
					$data ['registrant_dotFRContactEntityTradeMark'] = $params ['additionalfields'] ['Trade Mark'];
					$data ['admin_dotFRContactEntityTradeMark'] = $params ['additionalfields'] ['Trade Mark'];
				}
				break;
		}
        $data ['registrant_dotFRContactEntitySiren'] = trim($params ['additionalfields'] ['Siren']);
        $data ['admin_dotFRContactEntitySiren'] = trim($params ['additionalfields'] ['Siren']);
        $data ['registrant_dotFRContactEntityVat'] = trim($params ['additionalfields'] ['VATNO']);
        $data ['admin_dotFRContactEntityVat'] = trim($params ['additionalfields'] ['VATNO']);
        $data ['registrant_dotFRContactEntityDuns'] = trim($params ['additionalfields'] ['DUNSNO']);
        $data ['admin_dotFRContactEntityDuns'] = trim($params ['additionalfields'] ['DUNSNO']);

		if ($holderType != 'individual') {
			$data ['registrant_dotFRContactEntityName'] = empty($RegistrantCompany)?$RegistrantFirstName . ' ' . $RegistrantLastName:$RegistrantCompany;
			$data ['admin_dotFRContactEntityName'] = empty($AdminCompany)?$AdminFirstName . ' ' . $AdminLastName:$AdminCompany;
		}
	
	}
	
	if($tld=='tel') {
		if(isset($params ['additionalfields']["telhostingaccount"])) { $TelHostingAccount = $params ['additionalfields']["telhostingaccount"];} else { $TelHostingAccount =  md5($RegistrantLastName.$RegistrantFirstName.time().rand(0,99999)); }
		if(isset($params ['additionalfields']["telhostingpassword"])) { $TelHostingPassword = $params ['additionalfields']["telhostingpassword"]; } else { $TelHostingPassword =  'passwd'.rand(0,99999); }

		$data['telHostingAccount'] = $TelHostingAccount;
		$data['telHostingPassword'] = $TelHostingPassword;
		if($params['additionalfields']['telhidewhoisdata']!='')
		{
			$data['telHideWhoisData']="YES";
		}
		else
		{
			$data['telHideWhoisData']="NO";
		}
	    //$data['telHostingAccount'] = md5($RegistrantLastName.$RegistrantFirstName.time().rand(0,99999));
	    //$data['telHostingPassword'] = 'passwd'.rand(0,99999);
	}
	
	if($tld=='it') {
	    $EUCountries = array('AT', 'BE', 'BG', 'CZ', 'CY', 'DE', 'DK', 'ES', 'EE', 'FI', 'FR', 'GR', 'GB', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SK', 'SI');
	    $EntityTypes = array('1. Italian and foreign natural persons'=>1,'2. Companies/one man companies'=>2,'3. Freelance workers/professionals'=>3,'4. non-profit organizations'=>4,'5. public organizations'=>5,'6. other subjects'=>6,'7. foreigners who match 2 - 6'=>7) ;
	    $legalEntityType = $params['additionalfields']['Legal Entity Type'];
	    $et = $EntityTypes[$legalEntityType];
	    $data['registrant_dotitentitytype']=$et;
	    
	    $isDotIdAdminAndRegistrantSame = (1==$et);
	    
	    if($et>=2 && $et<=6) {
	        $data['registrant_dotitnationality']='IT';
	        $data['registrant_countrycode']='IT';
	    } else if($et==7) {
	        if(!in_array($data['registrant_countrycode'],$EUCountries)) {
	            $data['registrant_countrycode']='FR';
	        }
	        $data['registrant_dotitnationality']=$data['registrant_countrycode'];
	    } else {
	        $nationality=internetbs_getCountryCodeByName($params['additionalfields']['Nationality']);
	        if(!in_array($nationality,$EUCountries) && !in_array($data['registrant_countrycode'],$EUCountries)) {
	            $nationality='IT';
	        }
	        $data['registrant_dotitnationality']=$nationality;
	    }
	    
	    if(strtoupper($data['registrant_countrycode'])=='IT') {
	    	// Extract province code from input value
	        $data['registrant_dotitprovince'] = internetbs_get2CharDotITProvinceCode($RegistrantStateProvince);
	    } else {
	        $data['registrant_dotitprovince'] = $RegistrantStateProvince;
	    }
            if(strtoupper($data['admin_countrycode'])=='IT') {
                $data['admin_dotitprovince'] = internetbs_get2CharDotITProvinceCode($AdminStateProvince);
            } else {
                $data['admin_dotitprovince'] = $AdminStateProvince;
            }
            
            $data['technical_dotitprovince']=$data['admin_dotitprovince'];
            
            $data['registrant_dotitregcode']=$params['additionalfields']['VATTAXPassportIDNumber'];
            $data['registrant_dotithidewhois']=$params['additionalfields']['Hide data in public WHOIS']=='on'?'YES':'NO';
            $data['admin_dotithidewhois']=$data['registrant_dotithidewhois'];

            // Hide or not data in public whois
            if(!$isDotIdAdminAndRegistrantSame)	{
                    $data['admin_dotithidewhois'] = $hideWhoisData;
            }
            $data['technical_dotithidewhois'] = $hideWhoisData;


            $data['registrant_clientip'] = internetbs_getClientIp();
                    $data['registrant_dotitterm1'] = 'yes';
            $data['registrant_dotitterm2'] = 'yes';
            $data['registrant_dotitterm3'] =  $params['additionalfields']['Hide data in public WHOIS']=='on'?'no':'yes';;
            $data['registrant_dotitterm4'] = 'yes';
	}
	
	// period is optional
	if (isset ( $params ["regperiod"] ) && $regperiod > 0) {
		$data ['period'] = $regperiod . "Y";
	}

	// create domain
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	}
	if ($result ['product_0_status'] == 'FAILURE') {
		if (isset ( $values ["error"] )) {
			$values ["error"] .= $result ['product_0_message'];
		} else {
			$values ["error"] = $result ['product_0_message'];
		}
	}
	if (($result ['status'] == 'FAILURE' || $result ['product_0_status'] == 'FAILURE') && (! isset ( $values ['error'] ) || empty ( $values ['error'] ))) {
		$values ['error'] = 'Error: cannot register domain';
	}
	
	return $values;
}

/**
 * @todo for uk, need to run change/tag/dotuk
 * initiates transfer for a domain
 *
 * @param unknown_type $params
 * @return unknown
 */
function internetbs_TransferDomain($params) {
	$params=internetbs_get_utf8_parameters($params);
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$hideWhoisData = (isset($params ["HideWhoisData"]) && ('on' == strtolower($params ["HideWhoisData"])))?'YES':'NO';
	
	$tld = $params ["tld"];
	$sld = $params ["sld"];

	$transfersecret = $params ["transfersecret"];

	# Registrant Details
	$RegistrantFirstName = $params ["firstname"];
	$RegistrantLastName = $params ["lastname"];
	$RegistrantCompany= trim($params["companyname"]);
	$RegistrantAddress1 = $params ["address1"];
	$RegistrantAddress2 = $params ["address2"];
	$RegistrantCity = $params ["city"];
	$RegistrantStateProvince = $params ["state"];
	$RegistrantPostalCode = $params ["postcode"];
	$RegistrantCountry = $params ["country"];
	$RegistrantEmailAddress = $params ["email"];
	$RegistrantPhone = internetbs_reformatPhone ( $params ["phonenumber"], $params ["country"] );
	# Admin Details
	$AdminFirstName = $params ["adminfirstname"];
	$AdminLastName = $params ["adminlastname"];
	$AdminAddress1 = $params ["adminaddress1"];
	$AdminAddress2 = $params ["adminaddress2"];
	$AdminCity = $params ["admincity"];
	$AdminCompany=$params["admincompanyname"];
	$AdminStateProvince = $params ["adminstate"];
	$AdminPostalCode = $params ["adminpostcode"];
	$AdminCountry = $params ["admincountry"];
	$AdminEmailAddress = $params ["adminemail"];
	$AdminPhone = internetbs_reformatPhone ( $params ["adminphonenumber"], $params ["admincountry"] );
	
	# code to transfer domain
	$domainName = $sld.'.'.$tld;

    $nslist = array ();
    for($i = 1; $i <= 5; $i ++) {
        if (isset($params["ns$i"])) {
            array_push($nslist, $params["ns$i"]);
        }
    }
	
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Transfer/Initiate';
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName, 'transferAuthInfo' => $transfersecret, 

	// registrant contact data
	'registrant_firstname' => $RegistrantFirstName, 'registrant_lastname' => $RegistrantLastName, 'registrant_street' => $RegistrantAddress1, 'registrant_street2' => $RegistrantAddress2, 'registrant_city' => $RegistrantCity, 'registrant_countrycode' => $RegistrantCountry, 'registrant_postalcode' => $RegistrantPostalCode, 'registrant_email' => $RegistrantEmailAddress, 'registrant_phonenumber' => $RegistrantPhone, 

	// technical contact data
	'technical_firstname' => $AdminFirstName, 'technical_lastname' => $AdminLastName, 'technical_street' => $AdminAddress1, 'technical_street2' => $AdminAddress2, 'technical_city' => $AdminCity, 'technical_countrycode' => $AdminCountry, 'technical_postalcode' => $AdminPostalCode, 'technical_email' => $AdminEmailAddress, 'technical_phonenumber' => $AdminPhone, 

	// admin contact data
	'admin_firstname' => $AdminFirstName, 'admin_lastname' => $AdminLastName, 'admin_street' => $AdminAddress1, 'admin_street2' => $AdminAddress2, 'admin_city' => $AdminCity, 'admin_countrycode' => $AdminCountry, 'admin_postalcode' => $AdminPostalCode, 'admin_email' => $AdminEmailAddress, 'admin_phonenumber' => $AdminPhone, 

	// billing contact data
	'billing_firstname' => $AdminFirstName, 'billing_lastname' => $AdminLastName, 'billing_street' => $AdminAddress1, 'billing_street2' => $AdminAddress2, 'billing_city' => $AdminCity, 'billing_countrycode' => $AdminCountry, 'billing_postalcode' => $AdminPostalCode, 'billing_email' => $AdminEmailAddress, 'billing_phonenumber' => $AdminPhone );
	
	if(!empty($RegistrantCompany)){
		$data["Registrant_Organization"] = $RegistrantCompany;
	}
	if(!empty($AdminCompany)){
		$data["technical_Organization"] = $AdminCompany;
		$data["admin_Organization"] = $AdminCompany;
		$data["billing_Organization"] = $AdminCompany;
	}
	// ns_list is optional
	if (count ( $nslist )) {
		$data ['ns_list'] = implode ( ',', $nslist );
	}
	
	if ($tld == 'eu' || $tld == 'be' || $tld == 'uk') {
		$data ['registrant_language'] = isset ( $params ['Language'] ) ? $params ['Language'] : 'en';
	}
	
	
	// ADDED FOR .DE //
	
	if($tld == 'de')
	{
		if($params['additionalfields']['role']=="ORG")
		{
			$data['registrant_role'] = $params['additionalfields']['role'];
			$data['admin_role'] = "Person";
			$data['technical_role'] = "Role";
			$data['zone_role'] = "Role";
		}
		else
		{
			$data['registrant_role'] = $params['additionalfields']['role'];
			$data['admin_role'] = "Person";
			$data['technical_role'] = "Person";
			$data['zone_role'] = "Person";
		}
		if($params['additionalfields']['tosAgree']!='')
		{
		  $data['tosAgree']="YES";
		}
		else
		{
		   $data['tosAgree']="NO";
		}
		 $data['registrant_sip'] = @$params['additionalfields']['sip']; 
		 $data['clientip'] = internetbs_getClientIp();
		 if($params['additionalfields']['Restricted Publication']!='')
		 {
			$data['registrant_discloseName'] = "YES";
			$data['registrant_discloseContact'] = "YES";
			$data['registrant_discloseAddress'] = "YES";
		 }
		 else
		 {
			$data['registrant_discloseName'] = "NO";
			$data['registrant_discloseContact'] = "NO";
			$data['registrant_discloseAddress'] = "NO";
		 }
		 $data['zone_firstname'] = $AdminFirstName;
		 $data['zone_lastname'] = $AdminLastName;
		 $data['zone_email'] = $AdminEmailAddress;
		 $data['zone_phonenumber'] = internetbs_reformatPhone($params["phonenumber"], $params["country"]);		 
		 $data['zone_postalcode'] = $AdminPostalCode;
		 $data['zone_city'] = $AdminCity;
		 $data['zone_street'] = $AdminAddress1;
		 $data['zone_countrycode'] = 'DE';
	}
	// END OF .DE //
	
	// ADDED FOR .NL //
	
	if($tld == 'nl')
	{
		if($params['additionalfields']['nlTerm']!='')
		{
		  $data['registrant_nlTerm']="YES";
		}
		else
		{
		  $data['registrant_nlTerm']="NO";	
		}
		$data['registrant_clientip'] = internetbs_getClientIp();
		$data['registrant_nlLegalForm'] = $params['additionalfields']['nlLegalForm'];
		$data['registrant_nlRegNumber'] = $params['additionalfields']['nlRegNumber'];
	}
	//END OF .NL //
	
	
	
	
	if($tld=='us')	{
		
		$data['registrant_usnexuscategory'] = $params['additionalfields']['Nexus Category'];
		
		$usDomainPurpose = trim($params['additionalfields']['Application Purpose']);
		
		if(strtolower($usDomainPurpose) == strtolower('Business use for profit'))	{
			$data['registrant_uspurpose'] = 'P1';
		} else if(strtolower($usDomainPurpose) == strtolower('Educational purposes'))	{
			$data['registrant_uspurpose'] = 'P4';
		} else if(strtolower($usDomainPurpose) == strtolower('Personal Use'))	{
			$data['registrant_uspurpose'] = 'P3';
		} else if(strtolower($usDomainPurpose) == strtolower('Government purposes'))	{
			$data['registrant_uspurpose'] = 'P5';
		} else {
			$data['registrant_uspurpose'] = 'P2';
		}
		
		$data['registrant_usnexuscategory'] = $params['additionalfields']['Nexus Category'];
		$data['registrant_usnexuscountry'] = $params['additionalfields']['Nexus Country'];
	}
	
	
	if ($tld == 'asia') {
		$data ['registrant_dotASIACedLocality'] = $AdminCountry;
		$data ['registrant_dotasiacedentity'] = $params['additionalfields']['Legal Entity Type'];
		if ($data ['registrant_dotasiacedentity'] == 'other') {
			$data ['registrant_dotasiacedentityother'] = isset ( $params['additionalfields'] ['Other legal entity type'] ) ? $params['additionalfields']['Other legal entity type'] : 'otheridentity';
		}
		$data ['registrant_dotasiacedidform'] = $params['additionalfields'] ['Identification Form'];
		if ($data ['registrant_dotasiacedidform'] != 'other') {
			$data ['registrant_dotASIACedIdNumber'] = $params['additionalfields'] ['Identification Number'];
		}
		if ($data ['registrant_dotasiacedidform'] == 'other') {
			$data ['registrant_dotasiacedidformother'] = isset ( $params['additionalfields'] ['Other identification form'] ) ? $params['additionalfields'] ['Other identification form'] : 'otheridentity';
		}
	}
	
	if ($tld == 'fr' || $tld == 're') {
		
		$holderType = isset ( $params ['additionalfields']['Holder Type'] ) ? $params['additionalfields']['Holder Type'] : 'individual';
		
		if($tld=='fr') {
    	    $holderType = isset ( $params ['additionalfields'] ['Holder Type'] ) ? $params ['additionalfields'] ['Holder Type'] : 'individual';
    		$data['admin_countrycode'] = 'FR';
		} else if ($tld=='re') {
		    $holderType = isset ( $params ['additionalfields'] ['Holder Type'] ) ? $params ['additionalfields'] ['Holder Type'] : 'other';
		    $data['registrant_countrycode'] = 'RE';
		    $frenchTerritoryCountries=array("GP", "MQ","GF","RE", "FR", "PF", "MQ", "YT", "NC", "PM", "WF", "MF", "BL", "TF");
		    if(!in_array($data['admin_countrycode'],$frenchTerritoryCountries)) {
		        $data['admin_countrycode']='RE';
		    }
		}
		
		$data ['registrant_dotfrcontactentitytype'] = $holderType;
		$data ['admin_dotfrcontactentitytype'] = $holderType;
		
		switch ($holderType) {
			case 'individual' :
				$data ["registrant_dotfrcontactentitybirthdate"] = $params ['additionalfields']['Birth Date YYYY-MM-DD'];
				$data ['registrant_dotfrcontactentitybirthplacecountrycode'] = $params ['additionalfields']['Birth Country Code'];
				$data ['admin_dotfrcontactentitybirthdate'] = $params ['additionalfields']['Birth Date YYYY-MM-DD'];
				$data ['admin_dotfrcontactentitybirthplacecountrycode'] = $params ['additionalfields']['Birth Country Code'];
				$data ['registrant_dotFRContactEntityBirthCity'] = $params ['additionalfields']['Birth City'];
				$data ['registrant_dotFRContactEntityBirthPlacePostalCode'] = $params ['additionalfields']['Birth Postal code'];
				$data ['admin_dotFRContactEntityBirthCity'] = $params ['additionalfields']['Birth City'];
				$data ['admin_dotFRContactEntityBirthPlacePostalCode'] = $params ['additionalfields']['Birth Postal code'];

				$data ['registrant_dotFRContactEntityRestrictedPublication'] = isset ( $params ['additionalfields']['Restricted Publication'] ) ? 1 : 0;
				$data ['admin_dotFRContactEntityRestrictedPublication'] = isset ( $params ['additionalfields']['Restricted Publication'] ) ? 1 : 0;
				break;
			case 'company' :
				$data ['registrant_dotFRContactEntitySiren'] = $params ['additionalfields']['Siren'];
				$data ['admin_dotFRContactEntitySiren'] = $params ['additionalfields']['Siren'];
				break;
			case 'trademark' :
				$data ['registrant_dotFRContactEntityTradeMark'] = $params ['additionalfields']['Trade Mark'];
				$data ['admin_dotFRContactEntityTradeMark'] = $params ['additionalfields']['Trade Mark'];
				break;
			case 'association' :
				if (isset ( $params ['Waldec'] )) {
					$data ['registrant_dotFRContactEntityWaldec'] = $params ['additionalfields']['Waldec'];
					$data ['admin_dotFRContactEntityWaldec'] = $params ['additionalfields']['Waldec'];
				} else {
					$data ['registrant_dotfrcontactentitydateofassociation'] = $params ['additionalfields']['Date of Association YYYY-MM-DD'];
					$data ['registrant_dotFRContactEntityDateOfPublication'] = $params ['additionalfields']['Date of Publication YYYY-MM-DD'];
					$data ['registrant_dotfrcontactentityannouceno'] = $params ['additionalfields']['Annouce No'];
					$data ['registrant_dotFRContactEntityPageNo'] = $params ['additionalfields']['Page No'];
					$data ['admin_dotfrcontactentitydateofassociation'] = $params ['additionalfields']['Date of Association YYYY-MM-DD'];
					$data ['admin_dotFRContactEntityDateOfPublication'] = $params ['additionalfields']['Date of Publication YYYY-MM-DD'];
					$data ['admin_dotfrcontactentityannouceno'] = $params ['additionalfields']['Annouce No'];
					$data ['admin_dotFRContactEntityPageNo'] = $params ['additionalfields']['Page No'];
				}
				
				break;
			case 'other' :
				$data ['registrant_dotFROtherContactEntity'] = $params ['additionalfields']['Other Legal Status'];
				$data ['admin_dotFROtherContactEntity'] = $params ['additionalfields']['Other Legal Status'];
				if (isset ( $params ['additionalfields']['Siren'] )) {
					$data ['registrant_dotFRContactEntitySiren'] = $params ['additionalfields']['Siren'];
					$data ['admin_dotFRContactEntitySiren'] = $params ['additionalfields']['Siren'];
				} else if (isset ( $params['additionalfields']['Trade Mark'] )) {
					$data ['registrant_dotFRContactEntityTradeMark'] = $params ['additionalfields']['Trade Mark'];
					$data ['admin_dotFRContactEntityTradeMark'] = $params ['additionalfields']['Trade Mark'];
				}
				break;
		}
        $data ['registrant_dotFRContactEntitySiren'] = trim($params ['additionalfields'] ['Siren']);
        $data ['admin_dotFRContactEntitySiren'] = trim($params ['additionalfields'] ['Siren']);
        $data ['registrant_dotFRContactEntityVat'] = trim($params ['additionalfields'] ['VATNO']);
        $data ['admin_dotFRContactEntityVat'] = trim($params ['additionalfields'] ['VATNO']);
        $data ['registrant_dotFRContactEntityDuns'] = trim($params ['additionalfields'] ['DUNSNO']);
        $data ['admin_dotFRContactEntityDuns'] = trim($params ['additionalfields'] ['DUNSNO']);

		if ($holderType != 'individual') {
			$data ['registrant_dotFRContactEntityName'] = empty($RegistrantCompany)?$RegistrantFirstName . ' ' . $RegistrantLastName:$RegistrantCompany;
			$data ['admin_dotFRContactEntityName'] = empty($AdminCompany)?$AdminFirstName . ' ' . $AdminLastName:$AdminCompany;
		}

	}

	// Same as for .IT
	if($tld=='tel') {
		if(isset($params ['additionalfields']["telhostingaccount"])) { $TelHostingAccount = $params ['additionalfields']["telhostingaccount"];} else { $TelHostingAccount =  md5($RegistrantLastName.$RegistrantFirstName.time().rand(0,99999)); }
		if(isset($params ['additionalfields']["telhostingpassword"])) { $TelHostingPassword = $params ['additionalfields']["telhostingpassword"]; } else { $TelHostingPassword =  'passwd'.rand(0,99999); }

		$data['telHostingAccount'] = $TelHostingAccount;
		$data['telHostingPassword'] = $TelHostingPassword;
		if($params['additionalfields']['telhidewhoisdata']!='')
		{
			$data['telHideWhoisData']="YES";
		}
		else
		{
			$data['telHideWhoisData']="NO";
		}
	    //$data['telHostingAccount'] = md5($RegistrantLastName.$RegistrantFirstName.time().rand(0,99999));
	    //$data['telHostingPassword'] = 'passwd'.rand(0,99999);
	}
	// ADDED FOR .DE/.NL //
	
	if($tld == 'de' || $tld == 'nl')
	{
		 $data['registrant_clientip'] = internetbs_getClientIp();	
	}
	
	if($tld=='it') {

	    $EUCountries = array('AT', 'BE', 'BG', 'CZ', 'CY', 'DE', 'DK', 'ES', 'EE', 'FI', 'FR', 'GR', 'GB', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SK', 'SI');
	    $EntityTypes = array('1. Italian and foreign natural persons'=>1,'2. Companies/one man companies'=>2,'3. Freelance workers/professionals'=>3,'4. non-profit organizations'=>4,'5. public organizations'=>5,'6. other subjects'=>6,'7. foreigners who match 2 - 6'=>7) ;
	    $legalEntityType = $params['additionalfields']['Legal Entity Type'];
	    $et = $EntityTypes[$legalEntityType];
	    $data['registrant_dotitentitytype']=$et;
        $isDotIdAdminAndRegistrantSame = (1==$et);
	    if($et>=2 && $et<=6) {
	        $data['registrant_dotitnationality']='IT';
	        $data['registrant_countrycode']='IT';
	    } else if($et==7) {
	        if(!in_array($data['registrant_countrycode'],$EUCountries)) {
	            $data['registrant_countrycode']='FR';
	        }
	        $data['registrant_dotitnationality']=$data['registrant_countrycode'];
	    } else {
	        $nationality=internetbs_getCountryCodeByName($params['additionalfields']['Nationality']);
	        if(!in_array($nationality,$EUCountries) && !in_array($data['registrant_countrycode'],$EUCountries)) {
	            $nationality='IT';
	        }
	        $data['registrant_dotitnationality']=$nationality;
	    }
	    
	    if(strtoupper($data['registrant_countrycode'])=='IT') {
	    	// Extract province code from input value
	        $data['registrant_dotitprovince'] = internetbs_get2CharDotITProvinceCode($RegistrantStateProvince);
	    } else {
	        $data['registrant_dotitprovince'] = $RegistrantStateProvince;
	    }
            if(strtoupper($data['admin_countrycode'])=='IT') {
                $data['admin_dotitprovince'] = internetbs_get2CharDotITProvinceCode($AdminStateProvince);
            } else {
                $data['admin_dotitprovince'] = $AdminStateProvince;
            }
            $data['technical_dotitprovince']=$data['admin_dotitprovince'];
            $data['registrant_dotitregcode']=$params['additionalfields']['VATTAXPassportIDNumber'];
            $data['registrant_dotithidewhois']=$params['additionalfields']['Hide data in public WHOIS']=='on'?'YES':'NO';
            $data['admin_dotithidewhois']=$data['registrant_dotithidewhois'];

            // Hide or not data in public whois
            if(!$isDotIdAdminAndRegistrantSame)	{
                    $data['admin_dotithidewhois'] = $hideWhoisData;
            }
            $data['technical_dotithidewhois'] = $hideWhoisData;


            $data['registrant_clientip'] = internetbs_getClientIp();
            $data['registrant_dotitterm1'] = 'yes';
            $data['registrant_dotitterm2'] = 'yes';
            $data['registrant_dotitterm3'] =  $params['additionalfields']['Hide data in public WHOIS']=='on'?'no':'yes';;
            $data['registrant_dotitterm4'] = 'yes';
	}
	if ($params ['idprotection']) {
		$data ["privateWhois"] = "FULL";
	}
	// create domain
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	}
	if ($result ['product_0_status'] == 'FAILURE') {
		if (isset ( $values ["error"] )) {
			$values ["error"] .= $result ['product_0_message'];
		} else {
			$values ["error"] = $result ['product_0_message'];
		}
	}
	if (($result ['status'] == 'FAILURE' || $result ['product_0_status'] == 'FAILURE') && (! isset ( $values ['error'] ) || empty ( $values ['error'] ))) {
		$values ['error'] = 'Error: cannot start transfer domain';
	}
	
	return $values;
}

/**
 * renews a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_RenewDomain($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	$regperiod = intval ( $params ["regperiod"] );
	
	# code to renew domain
	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Renew';
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName );
	
	// period is optional
	if (isset ( $params ["regperiod"] ) && $regperiod > 0) {
		$data ['period'] = $regperiod . "Y";
	}
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	}
	
	return $values;
}

/**
 * gets contact details for a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_GetContactDetails($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	# code to get WHOIS data
	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Info';
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName );
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	} else {
		# Data should be returned in an array as follows
		$values ["Registrant"] ["First Name"] = $result ['contacts_registrant_firstname'];
		$values ["Registrant"] ["Last Name"] = $result ['contacts_registrant_lastname'];
		$values ["Registrant"] ["Company"] = $result ['contacts_registrant_organization'];
		$values ["Registrant"] ["Email"] = $result ['contacts_registrant_email'];
		$values ["Registrant"] ["Phone Number"] = $result ['contacts_registrant_phonenumber'];
		$values ["Registrant"] ["Address1"] = $result ['contacts_registrant_street'];
		$values ["Registrant"] ["Address2"] = $result ['contacts_registrant_street1'];
		$values ["Registrant"] ["Postcode"] = $result ['contacts_registrant_postalcode'];
		$values ["Registrant"] ["City"] = $result ['contacts_registrant_city'];
		$values ["Registrant"] ["Country"] = $result ['contacts_registrant_country'];
		$values ["Registrant"] ["Country Code"] = $result ['contacts_registrant_countrycode'];
		
		$values ["Admin"] ["First Name"] = $result ['contacts_admin_firstname'];
		$values ["Admin"] ["Last Name"] = $result ['contacts_admin_lastname'];
		$values ["Admin"] ["Company"] = $result ['contacts_admin_organization'];
		$values ["Admin"] ["Email"] = $result ['contacts_admin_email'];
		$values ["Admin"] ["Phone Number"] = $result ['contacts_admin_phonenumber'];
		$values ["Admin"] ["Address1"] = $result ['contacts_admin_street'];
		$values ["Admin"] ["Address2"] = $result ['contacts_admin_street1'];
		$values ["Admin"] ["Postcode"] = $result ['contacts_admin_postalcode'];
		$values ["Admin"] ["City"] = $result ['contacts_admin_city'];
		$values ["Admin"] ["Country"] = $result ['contacts_admin_country'];
		$values ["Admin"] ["Country Code"] = $result ['contacts_admin_countrycode'];
		
		if(isset($result ['contacts_technical_email'])){
			$values ["Tech"] ["First Name"] = $result ['contacts_technical_firstname'];
			$values ["Tech"] ["Last Name"] = $result ['contacts_technical_lastname'];
			$values ["Tech"] ["Company"] = $result ['contacts_technical_organization'];
			$values ["Tech"] ["Email"] = $result ['contacts_technical_email'];
			$values ["Tech"] ["Phone Number"] = $result ['contacts_technical_phonenumber'];
			$values ["Tech"] ["Address1"] = $result ['contacts_technical_street'];
			$values ["Tech"] ["Address2"] = $result ['contacts_technical_street1'];
			$values ["Tech"] ["Postcode"] = $result ['contacts_technical_postalcode'];
			$values ["Tech"] ["City"] = $result ['contacts_technical_city'];
			$values ["Tech"] ["Country"] = $result ['contacts_technical_country'];
			$values ["Tech"] ["Country Code"] = $result ['contacts_technical_countrycode'];
		}
	}
	
	return $values;
}

/**
 * saves contact details
 *
 * @param array $params
 * @return array
 */
function internetbs_SaveContactDetails($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	# Data is returned as specified in the GetContactDetails() function
	$firstname = $params ["contactdetails"] ["Registrant"] ["First Name"];
	$lastname = $params ["contactdetails"] ["Registrant"] ["Last Name"];
	$company = $params ["contactdetails"] ["Registrant"] ["Company"];
	$email = $params ["contactdetails"] ["Registrant"] ["Email"];
	$phonenumber = internetbs_reformatPhone ( $params ["contactdetails"] ["Registrant"] ["Phone Number"], $params ["contactdetails"] ["Registrant"] ["Country Code"] );
	$address1 = $params ["contactdetails"] ["Registrant"] ["Address1"];
	if(!$address1){
			$address1 = $params ["contactdetails"] ["Registrant"] ["Address 1"];
	}
	$address2 = $params ["contactdetails"] ["Registrant"] ["Address2"];
	if(!$address2){
			$address2 = $params ["contactdetails"] ["Registrant"] ["Address 2"];
	}
	
	$postalcode = $params ["contactdetails"] ["Registrant"] ["Postcode"];
	$city = $params ["contactdetails"] ["Registrant"] ["City"];
	$country = $params ["contactdetails"] ["Registrant"] ["Country"];
	$countrycode = $params ["contactdetails"] ["Registrant"] ["Country Code"];
	if(!$countrycode){
		$countrycode=$country;
	}
	
	$adminfirstname = $params ["contactdetails"] ["Admin"] ["First Name"];
	$adminlastname = $params ["contactdetails"] ["Admin"] ["Last Name"];
	$adminCompany = $params ["contactdetails"] ["Admin"] ["Company"];
	$adminemail = $params ["contactdetails"] ["Admin"] ["Email"];
	$adminphonenumber = internetbs_reformatPhone ( $params ["contactdetails"] ["Admin"] ["Phone Number"], $params ["contactdetails"] ["Admin"] ["Country Code"] );
	$adminaddress1 = $params ["contactdetails"] ["Admin"] ["Address1"];
	if(!$adminaddress1){
			$adminaddress1 = $params ["contactdetails"] ["Admin"] ["Address 1"];
	}	
	$adminaddress2 = $params ["contactdetails"] ["Admin"] ["Address2"];
	if(!$adminaddress2){
			$adminaddress2 = $params ["contactdetails"] ["Admin"] ["Address 2"];
	}	

	$adminpostalcode = $params ["contactdetails"] ["Admin"] ["Postcode"];
	$admincity = $params ["contactdetails"] ["Admin"] ["City"];
	$admincountry = $params ["contactdetails"] ["Admin"] ["Country"];
	$admincountrycode = $params ["contactdetails"] ["Admin"] ["Country Code"];
	if(!$admincountrycode){
		$admincountrycode=$admincountry;
	}
	
	$techfirstname = $params ["contactdetails"] ["Tech"] ["First Name"];
	$techlastname = $params ["contactdetails"] ["Tech"] ["Last Name"];
	$techCompany = $params ["contactdetails"] ["Tech"] ["Company"];
	$techemail = $params ["contactdetails"] ["Tech"] ["Email"];
	$techphonenumber = internetbs_reformatPhone ( $params ["contactdetails"] ["Tech"] ["Phone Number"], $params ["contactdetails"] ["Tech"] ["Country Code"] );
	$techaddress1 = $params ["contactdetails"] ["Tech"] ["Address1"];
	if(!$techaddress1){
			$techaddress1 = $params ["contactdetails"] ["Tech"] ["Address 1"];
	}	
	$techaddress2 = $params ["contactdetails"] ["Tech"] ["Address2"];
	if(!$techaddress2){
			$techaddress2 = $params ["contactdetails"] ["Tech"] ["Address 2"];
	}	

	$techpostalcode = $params ["contactdetails"] ["Tech"] ["Postcode"];
	$techcity = $params ["contactdetails"] ["Tech"] ["City"];
	$techcountry = $params ["contactdetails"] ["Tech"] ["Country"];
	$techcountrycode = $params ["contactdetails"] ["Tech"] ["Country Code"];
	if(!$techcountrycode){
		$techcountrycode=$techcountry;
	}
	
	# Put your code to save new WHOIS data here
	

	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Update';
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName, 

	// registrant contact data
	'registrant_firstname' => $firstname, 'registrant_lastname' => $lastname, 'registrant_organization'=>$company, 'registrant_street' => $address1, 'registrant_street2' => $address2, 'registrant_city' => $city, 'registrant_countrycode' => $countrycode, 'registrant_postalcode' => $postalcode, 'registrant_email' => $email, 'registrant_phonenumber' => $phonenumber, 

	// technical contact data
	'technical_firstname' => $techfirstname, 'technical_lastname' => $techlastname, 'technical_organization'=>$techCompany, 'technical_street' => $techaddress1, 'technical_street2' => $techaddress2, 'technical_city' => $techcity, 'technical_countrycode' => $techcountrycode, 'technical_postalcode' => $techpostalcode, 'technical_email' => $techemail, 'technical_phonenumber' => $techphonenumber, 

	// admin contact data
	'admin_firstname' => $adminfirstname, 'admin_lastname' => $adminlastname, 'admin_organization'=>$adminCompany,'admin_street' => $adminaddress1, 'admin_street2' => $adminaddress2, 'admin_city' => $admincity, 'admin_countrycode' => $admincountrycode, 'admin_postalcode' => $adminpostalcode, 'admin_email' => $adminemail, 'admin_phonenumber' => $adminphonenumber, 

	// billing contact data
	'billing_firstname' => $adminfirstname, 'billing_lastname' => $adminlastname,'admin_organization'=>$adminCompany, 'billing_street' => $adminaddress1, 'billing_street2' => $adminaddress2, 'billing_city' => $admincity, 'billing_countrycode' => $admincountrycode, 'billing_postalcode' => $adminpostalcode, 'billing_email' => $adminemail, 'billing_phonenumber' => $adminphonenumber );
	
	$extarr = explode ( '.', $tld );
	$ext = array_pop ( $extarr );
	
	
	// Unset params which is not possible update for domain
    if('it' == $ext)	{
    	unset($data['registrant_countrycode']);
        unset($data['registrant_organization']);
        unset($data['registrant_countrycode']);
        unset($data['registrant_country']);
        unset($data['registrant_dotitentitytype']);
        unset($data['registrant_dotitnationality']);
        unset($data['registrant_dotitregcode']);
	} 
        
    if($ext == 'eu' || $ext == 'be')	{
		if(!strlen(trim($data['registrant_organization']))) {
			unset($data['registrant_firstname']);
			unset($data['registrant_lastname']);
		}
		unset($data['registrant_organization']);
	}

	if($ext == "co.uk" || $ext == "org.uk" || $ext == "me.uk" || $ext == 'uk') {
		unset($data['registrant_firstname']);
		unset($data['registrant_lastname']);
	}
	
	if($ext == "fr" || $ext == "re")	{
		unset($data['registrant_firstname']);
		unset($data['registrant_lastname']);
		unset($data['registrant_countrycode']);
		unset($data['registrant_countrycode']);
		
		if(!strlen(trim($data['admin_dotfrcontactentitysiren'])))	{
			unset($data['admin_dotfrcontactentitysiren']);	
		}
		
		if(trim(strtolower($data['admin_dotfrcontactentitytype'])) == 'individual')	{
			unset($data['admin_countrycode']);
		}
	}
	
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	}
	
	return $values;
}

/**
 * gets domain secret/ transfer auth info of a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_GetEPPCode($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	# code to request the EPP code - if the API returns it, pass back as below - otherwise return no value and it will assume code is emailed
	

	$domainName = $sld . '.' . $tld;
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Info';
	
	$data = array ('apikey' => $username, 'password' => $password, 'domain' => $domainName );
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	} else {
		$values ["eppcode"] = $result ['transferauthinfo'];
	}
	
	return $values;
}

/**
 * creates a host for a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_RegisterNameserver($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	$nameserver = $params ["nameserver"];
	$ipaddress = $params ["ipaddress"];
	
	# code to register the nameserver
	$domainName = $sld . '.' . $tld;
	
	if (($nameserver != $domainName) && strpos ( $nameserver, '.' . $domainName ) === false) {
		$nameserver = $nameserver . '.' . $domainName;
	}
	
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Host/Create';
	
	$data = array ('apikey' => $username, 'password' => $password, 'host' => $nameserver, 'ip_list' => $ipaddress );
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	}
	
	return $values;
}

/**
 * updates host of a domain
 *
 * @param array $params
 * @return array
 */
function internetbs_ModifyNameserver($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	$nameserver = $params ["nameserver"];
	$currentipaddress = $params ["currentipaddress"];
	$newipaddress = $params ["newipaddress"];
	
	# code to update the nameserver
	$domainName = $sld . '.' . $tld;
	
	if (($nameserver != $domainName) && strpos ( $nameserver, '.' . $domainName ) === false) {
		$nameserver = $nameserver . '.' . $domainName;
	}
	
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Host/Update';
	
	$data = array ('apikey' => $username, 'password' => $password, 'host' => $nameserver, 'ip_list' => $newipaddress );
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	}
	
	return $values;
}

/**
 * deletes a host
 *
 * @param array $params
 * @return array
 */
function internetbs_DeleteNameserver($params) {
	$username = $params ["Username"];
	$password = $params ["Password"];
	$testmode = $params ["TestMode"];
	$tld = $params ["tld"];
	$sld = $params ["sld"];
	
	$nameserver = $params ["nameserver"];
	
	# code to delete the nameserver
	$domainName = $sld . '.' . $tld;
	
	if (($nameserver != $domainName) && strpos ( $nameserver, '.' . $domainName ) === false) {
		$nameserver = $nameserver . '.' . $domainName;
	}
	
	$apiServerUrl = ($testmode == "on") ? API_TESTSERVER_URL : API_SERVER_URL;
	$commandUrl = $apiServerUrl . 'Domain/Host/Delete';
	
	$data = array ('apikey' => $username, 'password' => $password, 'host' => $nameserver );
	
	$result = internetbs_runCommand ( $commandUrl, $data );
	$errorMessage = internetbs_getLastError ();
	# If error, return the error message in the value below
	if ($result === false) {
		$values ["error"] = internetbs_getConnectionErrorMessage ( $errorMessage );
	} else if ($result ['status'] == 'FAILURE') {
		$values ["error"] = $result ['message'];
	}
	
	return $values;
}

function internetbs_mapCountry($countryCode) {
	
	$mapc = array ('US' => 1, 'CA' => 1, 'AI' => 1, 'AG' => 1, 'BB' => 1, 'BS' => 1, 'VG' => 1, 'VI' => 1, 'KY' => 1, 'BM' => 1, 'GD' => 1, 'TC' => 1, 'MS' => 1, 'MP' => 1, 'GU' => 1, 'LC' => 1, 'DM' => 1, 'VC' => 1, 'PR' => 1, 'DO' => 1, 'TT' => 1, 'KN' => 1, 'JM' => 1, 'EG' => 20, 'MA' => 212, 'DZ' => 213, 'TN' => 216, 'LY' => 218, 'GM' => 220, 'SN' => 221, 'MR' => 222, 'ML' => 223, 'GN' => 224, 'CI' => 225, 'BF' => 226, 'NE' => 227, 'TG' => 228, 'BJ' => 229, 'MU' => 230, 'LR' => 231, 'SL' => 232, 'GH' => 233, 'NG' => 234, 'TD' => 235, 'CF' => 236, 'CM' => 237, 'CV' => 238, 'ST' => 239, 'GQ' => 240, 'GA' => 241, 'CG' => 242, 'CD' => 243, 'AO' => 244, 'GW' => 245, 'IO' => 246, 'AC' => 247, 'SC' => 248, 'SD' => 249, 'RW' => 250, 'ET' => 251, 'SO' => 252, 'DJ' => 253, 'KE' => 254, 'TZ' => 255, 'UG' => 256, 'BI' => 257, 'MZ' => 258, 'ZM' => 260, 'MG' => 261, 'RE' => 262, 'ZW' => 263, 'NA' => 264, 'MW' => 265, 'LS' => 266, 'BW' => 267, 'SZ' => 268, 'KM' => 269, 'YT' => 269, 'ZA' => 27, 'SH' => 290, 'ER' => 291, 'AW' => 297, 'FO' => 298, 'GL' => 299, 'GR' => 30, 'NL' => 31, 'BE' => 32, 'FR' => 33, 'ES' => 34, 'GI' => 350, 'PT' => 351, 'LU' => 352, 'IE' => 353, 'IS' => 354, 'AL' => 355, 'MT' => 356, 'CY' => 357, 'FI' => 358, 'BG' => 359, 'HU' => 36, 'LT' => 370, 'LV' => 371, 'EE' => 372, 'MD' => 373, 'AM' => 374, 'BY' => 375, 'AD' => 376, 'MC' => 377, 'SM' => 378, 'VA' => 379, 'UA' => 380, 'CS' => 381, 'YU' => 381, 'HR' => 385, 'SI' => 386, 'BA' => 387, 'EU' => 388, 'MK' => 389, 'IT' => 39, 'RO' => 40, 'CH' => 41, 'CZ' => 420, 'SK' => 421, 'LI' => 423, 'AT' => 43, 'GB' => 44, 'DK' => 45, 'SE' => 46, 'NO' => 47, 'PL' => 48, 'DE' => 49, 'FK' => 500, 'BZ' => 501, 'GT' => 502, 'SV' => 503, 'HN' => 504, 'NI' => 505, 'CR' => 506, 'PA' => 507, 'PM' => 508, 'HT' => 509, 'PE' => 51, 'MX' => 52, 'CU' => 53, 'AR' => 54, 'BR' => 55, 'CL' => 56, 'CO' => 57, 'VE' => 58, 'GP' => 590, 'BO' => 591, 'GY' => 592, 'EC' => 593, 'GF' => 594, 'PY' => 595, 'MQ' => 596, 'SR' => 597, 'UY' => 598, 'AN' => 599, 'MY' => 60, 'AU' => 61, 'CC' => 61, 'CX' => 61, 'ID' => 62, 'PH' => 63, 'NZ' => 64, 'SG' => 65, 'TH' => 66, 'TL' => 670, 'AQ' => 672, 'NF' => 672, 'BN' => 673, 'NR' => 674, 'PG' => 675, 'TO' => 676, 'SB' => 677, 'VU' => 678, 'FJ' => 679, 'PW' => 680, 'WF' => 681, 'CK' => 682, 'NU' => 683, 'AS' => 684, 'WS' => 685, 'KI' => 686, 'NC' => 687, 'TV' => 688, 'PF' => 689, 'TK' => 690, 'FM' => 691, 'MH' => 692, 'RU' => 7, 'KZ' => 7, 'XF' => 800, 'XC' => 808, 'JP' => 81, 'KR' => 82, 'VN' => 84, 'KP' => 850, 'HK' => 852, 'MO' => 853, 'KH' => 855, 'LA' => 856, 'CN' => 86, 'XS' => 870, 'XE' => 871, 'XP' => 872, 'XI' => 873, 'XW' => 874, 'XU' => 878, 'BD' => 880, 'XG' => 881, 'XN' => 882, 'TW' => 886, 'TR' => 90, 'IN' => 91, 'PK' => 92, 'AF' => 93, 'LK' => 94, 'MM' => 95, 'MV' => 960, 'LB' => 961, 'JO' => 962, 'SY' => 963, 'IQ' => 964, 'KW' => 965, 'SA' => 966, 'YE' => 967, 'OM' => 968, 'PS' => 970, 'AE' => 971, 'IL' => 972, 'BH' => 973, 'QA' => 974, 'BT' => 975, 'MN' => 976, 'NP' => 977, 'XR' => 979, 'IR' => 98, 'XT' => 991, 'TJ' => 992, 'TM' => 993, 'AZ' => 994, 'GE' => 995, 'KG' => 996, 'UZ' => 998 );
	
	if (isset ( $mapc [$countryCode] )) {
		return ($mapc [$countryCode]);
	} else {
		return (1);
	}
}
function internetbs_chekPhone($phoneNumber) {
	$phoneNumber = str_replace ( " ", "", $phoneNumber);
	$phoneNumber = str_replace ( "\t", "", $phoneNumber);

    return (bool) preg_match('/^\+[0-9]{1,4}\.[0-9 ]+$/is', $phoneNumber);
}
function internetbs_reformatPhone($phoneNumber, $countryCode) {
	$countryPhoneCode = internetbs_mapCountry ( $countryCode );
	$plus = 0;
	$country = "";
	
	$scontrol = trim ( $phoneNumber );
	$l = strlen ( $scontrol );
	if ($scontrol {0} == '+')
		$plus = true;
	$scontrol = preg_replace ( '#\D*#si', "", $scontrol );
	if ($plus)
		$scontrol = "+" . $scontrol;
	if (! $l) {
		return ("+$countryPhoneCode.1111111");
	}
	if (strncmp ( $scontrol, "00", 2 ) == 0) {
		$scontrol = "+" . substr ( $scontrol, 2 );
		if (strlen ( $scontrol ) == 1) {
			$scontrol = '1111111';
		}
	}
	$rphone = "+1.1111111";
	if ($scontrol {0} == '+') {
		for($i = 2; $i < strlen ( $scontrol ); $i ++) {
			$first = substr ( $scontrol, 1, $i - 1 );
			if ($first == $countryPhoneCode) {
				$scontrol = "+" . $first . "." . substr ( $scontrol, $i );
				return $scontrol;
			}
		}
		$scontrol = trim ( $scontrol, "+" );
		$rphone = "+" . $countryPhoneCode . "." . $scontrol;
	} else {
		$rphone = "+" . $countryPhoneCode . "." . $scontrol;
	}
	
	if (internetbs_chekPhone ( $rphone )) {
		return $rphone;
	}
	
	return "+1.1111111";
}


function internetbs_get_utf8_parameters($params) {
	$config = array();
	$result = full_query("SELECT setting, value FROM tblconfiguration;");
	while ( $row = mysql_fetch_array($result, MYSQL_ASSOC) ) {
		$config[strtolower($row['setting'])] = $row['value'];
	}
	if ( (strtolower($config["charset"]) != "utf-8") && (strtolower($config["charset"]) != "utf8") )
		return $params;

	$result = full_query("SELECT orderid FROM tbldomains WHERE id='".mysql_real_escape_string($params["domainid"])."' LIMIT 1;");
	if ( !($row = mysql_fetch_array($result, MYSQL_ASSOC)) )
		return $params;

	$result = full_query("SELECT userid,contactid FROM tblorders WHERE id='".mysql_real_escape_string($row['orderid'])."' LIMIT 1;");
	if ( !($row = mysql_fetch_array($result, MYSQL_ASSOC)) )
		return $params;

	if ( $row['contactid'] ) {
		$result = full_query("SELECT firstname, lastname, companyname, email, address1, address2, city, state, postcode, country, phonenumber FROM tblcontacts WHERE id='".mysql_real_escape_string($row['contactid'])."' LIMIT 1;");
		if ( !($row = mysql_fetch_array($result, MYSQL_ASSOC)) )
			return $params;
		foreach ( $row as $key => $value ) {
			$params[$key] = $value;
		}
	}
	elseif ( $row['userid'] ) {
		$result = full_query("SELECT firstname, lastname, companyname, email, address1, address2, city, state, postcode, country, phonenumber FROM tblclients WHERE id='".mysql_real_escape_string($row['userid'])."' LIMIT 1;");
		if ( !($row = mysql_fetch_array($result, MYSQL_ASSOC)) )
			return $params;
		foreach ( $row as $key => $value ) {
			$params[$key] = $value;
		}
	}

	$resultad = full_query("SELECT `name`,`value` FROM tbldomainsadditionalfields WHERE domainid='".mysql_real_escape_string($params["domainid"])."';");
	if(!isset($params['additionalfields'])){
		$params['additionalfields']=array();
	}
	while ( $row = mysql_fetch_array ( $resultad ) ) {
			$name=$row["name"];
			$value=$row["value"];
			$params['additionalfields'][$name]=$value;
	}
	
	if ( $config['registraradminuseclientdetails'] ) {
		$params['adminfirstname'] = $params['firstname'];
		$params['adminlastname'] = $params['lastname'];
		$params['admincompanyname'] = $params['companyname'];
		$params['adminemail'] = $params['email'];
		$params['adminaddress1'] = $params['address1'];
		$params['adminaddress2'] = $params['address2'];
		$params['admincity'] = $params['city'];
		$params['adminstate'] = $params['state'];
		$params['adminpostcode'] = $params['postcode'];
		$params['admincountry'] = $params['country'];
		$params['adminphonenumber'] = $params['phonenumber'];
	}
	else {
		$params['adminfirstname'] = $config['registraradminfirstname'];
		$params['adminlastname'] = $config['registraradminlastname'];
		$params['admincompanyname'] = $config['registraradmincompanyname'];
		$params['adminemail'] = $config['registraradminemailaddress'];
		$params['adminaddress1'] = $config['registraradminaddress1'];
		$params['adminaddress2'] = $config['registraradminaddress2'];
		$params['admincity'] = $config['registraradmincity'];
		$params['adminstate'] = $config['registraradminstateprovince'];
		$params['adminpostcode'] = $config['registraradminpostalcode'];
		$params['admincountry'] = $config['registraradmincountry'];
		$params['adminphonenumber'] = $config['registraradminphone'];
	}

	return $params;
}

function internetbs_getCountryCodeByName($countryName) {
    $country = array("AFGHANISTAN"=>"AF","ALAND ISLANDS"=>"AX","ALBANIA"=>"AL","ALGERIA"=>"DZ","AMERICAN SAMOA"=>"AS","ANDORRA"=>"AD","ANGOLA"=>"AO","ANGUILLA"=>"AI","ANTARCTICA"=>"AQ","ANTIGUA AND BARBUDA"=>"AG","ARGENTINA"=>"AR","ARMENIA"=>"AM","ARUBA"=>"AW","AUSTRALIA"=>"AU","AUSTRIA"=>"AT","AZERBAIJAN"=>"AZ","BAHAMAS"=>"BS","BAHRAIN"=>"BH","BANGLADESH"=>"BD","BARBADOS"=>"BB","BELARUS"=>"BY","BELGIUM"=>"BE","BELIZE"=>"BZ","BENIN"=>"BJ","BERMUDA"=>"BM","BHUTAN"=>"BT","BOLIVIA"=>"BO","BOSNIA AND HERZEGOVINA"=>"BA","BOTSWANA"=>"BW","BOUVET ISLAND"=>"BV","BRAZIL"=>"BR","BRITISH INDIAN OCEAN TERRITORY"=>"IO","BRITISH VIRGIN ISLANDS"=>"VG","BRUNEI"=>"BN","BULGARIA"=>"BG","BURKINA FASO"=>"BF","BURUNDI"=>"BI","CAMBODIA"=>"KH","CAMEROON"=>"CM","CANADA"=>"CA","CAPE VERDE"=>"CV","CAYMAN ISLANDS"=>"KY","CENTRAL AFRICAN REPUBLIC"=>"CF","CHAD"=>"TD","CHILE"=>"CL","CHINA"=>"CN","CHRISTMAS ISLAND"=>"CX","COCOS (KEELING) ISLANDS"=>"CC","COLOMBIA"=>"CO","COMOROS"=>"KM","CONGO"=>"CG","COOK ISLANDS"=>"CK","COSTA RICA"=>"CR","CROATIA"=>"HR","CUBA"=>"CU","CYPRUS"=>"CY","CZECH REPUBLIC"=>"CZ","DEMOCRATIC REPUBLIC OF CONGO"=>"CD","DENMARK"=>"DK","DISPUTED TERRITORY"=>"XX","DJIBOUTI"=>"DJ","DOMINICA"=>"DM","DOMINICAN REPUBLIC"=>"DO","EAST TIMOR"=>"TL","ECUADOR"=>"EC","EGYPT"=>"EG","EL SALVADOR"=>"SV","EQUATORIAL GUINEA"=>"GQ","ERITREA"=>"ER","ESTONIA"=>"EE","ETHIOPIA"=>"ET","FALKLAND ISLANDS"=>"FK","FAROE ISLANDS"=>"FO","FEDERATED STATES OF MICRONESIA"=>"FM","FIJI"=>"FJ","FINLAND"=>"FI","FRANCE"=>"FR","FRENCH GUYANA"=>"GF","FRENCH POLYNESIA"=>"PF","FRENCH SOUTHERN TERRITORIES"=>"TF","GABON"=>"GA","GAMBIA"=>"GM","GEORGIA"=>"GE","GERMANY"=>"DE","GHANA"=>"GH","GIBRALTAR"=>"GI","GREECE"=>"GR","GREENLAND"=>"GL","GRENADA"=>"GD","GUADELOUPE"=>"GP","GUAM"=>"GU","GUATEMALA"=>"GT","GUERNSEY"=>"GG","GUINEA"=>"GN","GUINEA-BISSAU"=>"GW","GUYANA"=>"GY","HAITI"=>"HT","HEARD ISLAND AND MCDONALD ISLANDS"=>"HM","HONDURAS"=>"HN","HONG KONG"=>"HK","HUNGARY"=>"HU","ICELAND"=>"IS","INDIA"=>"IN","INDONESIA"=>"ID","IRAN"=>"IR","IRAQ"=>"IQ","IRAQ-SAUDI ARABIA NEUTRAL ZONE"=>"XE","IRELAND"=>"IE","ISRAEL"=>"IL","ISLE OF MAN"=>"IM","ITALY"=>"IT","IVORY COAST"=>"CI","JAMAICA"=>"JM","JAPAN"=>"JP","JERSEY"=>"JE","JORDAN"=>"JO","KAZAKHSTAN"=>"KZ","KENYA"=>"KE","KIRIBATI"=>"KI","KUWAIT"=>"KW","KYRGYZSTAN"=>"KG","LAOS"=>"LA","LATVIA"=>"LV","LEBANON"=>"LB","LESOTHO"=>"LS","LIBERIA"=>"LR","LIBYA"=>"LY","LIECHTENSTEIN"=>"LI","LITHUANIA"=>"LT","LUXEMBOURG"=>"LU","MACAU"=>"MO","MACEDONIA"=>"MK","MADAGASCAR"=>"MG","MALAWI"=>"MW","MALAYSIA"=>"MY","MALDIVES"=>"MV","MALI"=>"ML","MALTA"=>"MT","MARSHALL ISLANDS"=>"MH","MARTINIQUE"=>"MQ","MAURITANIA"=>"MR","MAURITIUS"=>"MU","MAYOTTE"=>"YT","MEXICO"=>"MX","MOLDOVA"=>"MD","MONACO"=>"MC","MONGOLIA"=>"MN","MONTSERRAT"=>"MS","MOROCCO"=>"MA","MOZAMBIQUE"=>"MZ","MYANMAR"=>"MM","NAMIBIA"=>"NA","NAURU"=>"NR","NEPAL"=>"NP","NETHERLANDS"=>"NL","NETHERLANDS ANTILLES"=>"AN","NEW CALEDONIA"=>"NC","NEW ZEALAND"=>"NZ","NICARAGUA"=>"NI","NIGER"=>"NE","NIGERIA"=>"NG","NIUE"=>"NU","NORFOLK ISLAND"=>"NF","NORTH KOREA"=>"KP","NORTHERN MARIANA ISLANDS"=>"MP","NORWAY"=>"NO","OMAN"=>"OM","PAKISTAN"=>"PK","PALAU"=>"PW","PALESTINIAN OCCUPIED TERRITORIES"=>"PS","PANAMA"=>"PA","PAPUA NEW GUINEA"=>"PG","PARAGUAY"=>"PY","PERU"=>"PE","PHILIPPINES"=>"PH","PITCAIRN ISLANDS"=>"PN","POLAND"=>"PL","PORTUGAL"=>"PT","PUERTO RICO"=>"PR","QATAR"=>"QA","REUNION"=>"RE","ROMANIA"=>"RO","RUSSIA"=>"RU","RWANDA"=>"RW","SAINT HELENA AND DEPENDENCIES"=>"SH","SAINT KITTS AND NEVIS"=>"KN","SAINT LUCIA"=>"LC","SAINT PIERRE AND MIQUELON"=>"PM","SAINT VINCENT AND THE GRENADINES"=>"VC","SAMOA"=>"WS","SAN MARINO"=>"SM","SAO TOME AND PRINCIPE"=>"ST","SAUDI ARABIA"=>"SA","SENEGAL"=>"SN","SEYCHELLES"=>"SC","SIERRA LEONE"=>"SL","SINGAPORE"=>"SG","SLOVAKIA"=>"SK","SLOVENIA"=>"SI","SOLOMON ISLANDS"=>"SB","SOMALIA"=>"SO","SOUTH AFRICA"=>"ZA","SOUTH GEORGIA AND SOUTH SANDWICH ISLANDS"=>"GS","SOUTH KOREA"=>"KR","SPAIN"=>"ES","SPRATLY ISLANDS"=>"PI","SRI LANKA"=>"LK","SUDAN"=>"SD","SURINAME"=>"SR","SVALBARD AND JAN MAYEN"=>"SJ","SWAZILAND"=>"SZ","SWEDEN"=>"SE","SWITZERLAND"=>"CH","SYRIA"=>"SY","TAIWAN"=>"TW","TAJIKISTAN"=>"TJ","TANZANIA"=>"TZ","THAILAND"=>"TH","TOGO"=>"TG","TOKELAU"=>"TK","TONGA"=>"TO","TRINIDAD AND TOBAGO"=>"TT","TUNISIA"=>"TN","TURKEY"=>"TR","TURKMENISTAN"=>"TM","TURKS AND CAICOS ISLANDS"=>"TC","TUVALU"=>"TV","UGANDA"=>"UG","UKRAINE"=>"UA","UNITED ARAB EMIRATES"=>"AE","UNITED KINGDOM"=>"GB","UNITED NATIONS NEUTRAL ZONE"=>"XD","UNITED STATES"=>"US","UNITED STATES MINOR OUTLYING ISLANDS"=>"UM","URUGUAY"=>"UY","US VIRGIN ISLANDS"=>"VI","UZBEKISTAN"=>"UZ","VANUATU"=>"VU","VATICAN CITY"=>"VA","VENEZUELA"=>"VE","VIETNAM"=>"VN","WALLIS AND FUTUNA"=>"WF","WESTERN SAHARA"=>"EH","YEMEN"=>"YE","ZAMBIA"=>"ZM","ZIMBABWE"=>"ZW","SERBIA"=>"RS","MONTENEGRO"=>"ME","SAINT MARTIN"=>"MF","SAINT BARTHELEMY"=>"BL");
    return $country[$countryName]; 
}

function internetbs_getClientIp() {
    return (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null));
}

function internetbs_get2CharDotITProvinceCode($province) {
	
	$provinceFiltered = trim($province);
	
	$provinceNamesInPossibleVariants = array(
	'Agrigento'=>'AG',
	'Alessandria'=>'AL',
	'Ancona'=>'AN',
	'Aosta, Aoste (fr)'=>'AO',
	'Aosta, Aoste'=>'AO',
	'Aosta'=>'AO',
	'Aoste'=>'AO',
	'Arezzo'=>'AR',
	'Ascoli Piceno'=>'AP',
	'Ascoli-Piceno'=>'AP',
	'Asti'=>'AT',
	'Avellino'=>'AV',
	'Bari'=>'BA',
	'Barletta-Andria-Trani'=>'BT',
	'Barletta Andria Trani'=>'BT',
	'Belluno'=>'BL',
	'Benevento'=>'BN',
	'Bergamo'=>'BG',
	'Biella'=>'BI',
	'Bologna'=>'BO',
	'Bolzano, Bozen (de)'=>'BZ',
	'Bolzano, Bozen'=>'BZ',
	'Bolzano'=>'BZ',
	'Bozen'=>'BZ',
	'Brescia'=>'BS',
	'Brindisi'=>'BR',
	'Cagliari'=>'CA',
	'Caltanissetta'=>'CL',
	'Campobasso'=>'CB',
	'Carbonia-Iglesias'=>'CI',
	'Carbonia Iglesias'=>'CI',
	'Carbonia'=>'CI',
	'Caserta'=>'CE',
	'Catania'=>'CT',
	'Catanzaro'=>'CZ',
	'Chieti'=>'CH',
	'Como'=>'CO',
	'Cosenza'=>'CS',
	'Cremona'=>'CR',
	'Crotone'=>'KR',
	'Cuneo'=>'CN',
	'Enna'=>'EN',
	'Fermo'=>'FM',
	'Ferrara'=>'FE',
	'Firenze'=>'FI',
	'Foggia'=>'FG',
	'Forli-Cesena'=>'FC',
	'Forli Cesena'=>'FC',
	'Forli'=>'FC',
	'Frosinone'=>'FR',
	'Genova'=>'GE',
	'Gorizia'=>'GO',
	'Grosseto'=>'GR',
	'Imperia'=>'IM',
	'Isernia'=>'IS',
	'La Spezia'=>'SP',
	'L\'Aquila'=>'AQ',
	'LAquila'=>'AQ',
	'L-Aquila'=>'AQ',
	'L Aquila'=>'AQ',
	'Latina'=>'LT',
	'Lecce'=>'LE',
	'Lecco'=>'LC',
	'Livorno'=>'LI',
	'Lodi'=>'LO',
	'Lucca'=>'LU',
	'Macerata'=>'MC',
	'Mantova'=>'MN',
	'Massa-Carrara'=>'MS',
	'Massa Carrara'=>'MS',
	'Massa'=>'MS',
	'Matera'=>'MT',
	'Medio Campidano'=>'VS',
	'Medio-Campidano'=>'VS',
	'Medio'=>'VS',
	'Messina'=>'ME',
	'Milano'=>'MI',
	'Modena'=>'MO',
	'Monza e Brianza'=>'MB',
	'Monza-e-Brianza'=>'MB',
	'Monza-Brianza'=>'MB',
	'Monza Brianza'=>'MB',
	'Monza'=>'MB',
	'Napoli'=>'NA',
	'Novara'=>'NO',
	'Nuoro'=>'NU',
	'Ogliastra'=>'OG',
	'Olbia-Tempio'=>'OT',
	'Olbia Tempio'=>'OT',
	'Olbia'=>'OT',
	'Oristano'=>'OR',
	'Padova'=>'PD',
	'Palermo'=>'PA',
	'Parma'=>'PR',
	'Pavia'=>'PV',
	'Perugia'=>'PG',
	'Pesaro e Urbino'=>'PU',
	'Pesaro-e-Urbino'=>'PU',
	'Pesaro-Urbino'=>'PU',
	'Pesaro Urbino'=>'PU',
	'Pesaro'=>'PU',
	'Pescara'=>'PE',
	'Piacenza'=>'PC',
	'Pisa'=>'PI',
	'Pistoia'=>'PT',
	'Pordenone'=>'PN',
	'Potenza'=>'PZ',
	'Prato'=>'PO',
	'Ragusa'=>'RG',
	'Ravenna'=>'RA',
	'Reggio Calabria'=>'RC',
	'Reggio-Calabria'=>'RC',
	'Reggio'=>'RC',
	'Reggio Emilia'=>'RE',
	'Reggio-Emilia'=>'RE',
	'Reggio'=>'RE',
	'Rieti'=>'RI',
	'Rimini'=>'RN',
	'Roma'=>'RM',
	'Rovigo'=>'RO',
	'Salerno'=>'SA',
	'Sassari'=>'SS',
	'Savona'=>'SV',
	'Siena'=>'SI',
	'Siracusa'=>'SR',
	'Sondrio'=>'SO',
	'Taranto'=>'TA',
	'Teramo'=>'TE',
	'Terni'=>'TR',
	'Torino'=>'TO',
	'Trapani'=>'TP',
	'Trento'=>'TN',
	'Treviso'=>'TV',
	'Trieste'=>'TS',
	'Udine'=>'UD',
	'Varese'=>'VA',
	'Venezia'=>'VE',
	'Verbano-Cusio-Ossola'=>'VB',
	'Verbano Cusio Ossola'=>'VB',
	'Verbano'=>'VB',
	'Verbano-Cusio'=>'VB',
	'Verbano-Ossola'=>'VB',
	'Vercelli'=>'VC',
	'Verona'=>'VR',
	'Vibo Valentia'=>'VV',
	'Vibo-Valentia'=>'VV',
	'Vibo'=>'VV',
	'Vicenza'=>'VI',
	'Viterbo'=>'VT',
	);
	
	
	// Check if we need to search province code
	if(strlen($provinceFiltered) == 2)	{
		// Looks we already have 2 char province code
		return strtoupper($provinceFiltered);
	} else {
		$provinceFiltered = strtolower(preg_replace('/[^a-z]/i','',$provinceFiltered));
		
		foreach($provinceNamesInPossibleVariants as $name => $code)	{
			if(strtolower(preg_replace('/[^a-z]/i','',$name)) == $provinceFiltered)	{
				return $code;
			}
		}
		
		return $province;
	}
	
}

function internetbs_getItProvinceCode($inputElementValue) {
	
	$code = 'RM';
	
	preg_match('/\[\s*([a-z]{2})\s*\]$/i', $inputElementValue, $matches);

	if(isset($matches[1]))	{
		$code = $matches[1];	
	}
	
	return $code; 
}
?>