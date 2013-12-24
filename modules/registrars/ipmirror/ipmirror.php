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

function ipmirror_getConfigArray() {
	$configarray = array( "Username" => array( "Type" => "text", "Size" => "20", "Description" => "Enter your ccTLDBox username here" ), "Password" => array( "Type" => "password", "Size" => "20", "Description" => "Enter your ccTLDBox password here" ), "ccTLDBoxUrl" => array( "Type" => "text", "Size" => "35", "Description" => "URL to ccTLDBox Registrar API" ), "AllowRegContactChange" => array( "Type" => "yesno", "Description" => "Tick to allow a client to change the registrant contact of a domain" ), "AllowAdminContactChange" => array( "Type" => "yesno", "Description" => "Tick to allow a client to change the administrative contact of a domain" ), "AllowTechContactChange" => array( "Type" => "yesno", "Description" => "Tick to allow a client to change the technical contact of a domain" ), "AllowBillContactChange" => array( "Type" => "yesno", "Description" => "Tick to allow a client to change the billing contact of a domain" ), "DefaultRegContactId" => array( "Type" => "text", "Size" => "20", "Description" => "ID of default ccTLDBox registrant contact (optional)" ), "DefaultAdminContactId" => array( "Type" => "text", "Size" => "20", "Description" => "ID of default ccTLDBox administrative contact (optional)" ), "DefaultTechContactId" => array( "Type" => "text", "Size" => "20", "Description" => "ID of default ccTLDBox technical contact (optional)" ), "DefaultBillContactId" => array( "Type" => "text", "Size" => "20", "Description" => "ID of default ccTLDBox billing contact (optional)" ), "ActivateSync" => array( "Type" => "yesno", "Description" => "Tick to activate domain synchronization w/ ccTLDBox" ), "SyncNextDueDate" => array( "Type" => "yesno", "Description" => "Tick to update the next due date together w/ the expiry date" ), "SyncIDProtection" => array( "Type" => "yesno", "Description" => "Tick to update ID protection status of domain (safestWHOIS)" ), "ConnectionTimeout" => array( "Type" => "text", "Size" => "2", "Description" => "Connection to ccTLDBox timeout [seconds] (default=20)" ), "ExecutionTimeout" => array( "Type" => "text", "Size" => "2", "Description" => "Execution of ccTLDBox command timeout [seconds] (default=60)" ), "DebugMode" => array( "Type" => "yesno", "Description" => "Tick to output debug information (admins only). USE WITH CARE!" ) );
	return $configarray;
}


function ipmirror_GetNameservers($params) {
	return _ipmirror_GetNameservers( $params );
}


function ipmirror_SaveNameservers($params) {
	return _ipmirror_SaveNameservers( $params );
}


function ipmirror_GetRegistrarLock($params) {
	return _ipmirror_GetRegistrarLock( $params );
}


function ipmirror_SaveRegistrarLock($params) {
	return _ipmirror_SaveRegistrarLock( $params );
}


function ipmirror_GetEmailForwarding($params) {
	return _ipmirror_GetEmailForwarding( $params );
}


function ipmirror_SaveEmailForwarding($params) {
	return _ipmirror_SaveEmailForwarding( $params );
}


function ipmirror_GetDNS($params) {
	return _ipmirror_GetDNS( $params );
}


function ipmirror_SaveDNS($params) {
	return _ipmirror_SaveDNS( $params );
}


function ipmirror_RegisterDomain($params) {
	return _ipmirror_RegisterDomain( $params );
}


function ipmirror_TransferDomain($params) {
	return _ipmirror_TransferDomain( $params );
}


function ipmirror_RenewDomain($params) {
	return _ipmirror_RenewDomain( $params );
}


function ipmirror_GetContactDetails($params) {
	return _ipmirror_GetContactDetails( $params );
}


function ipmirror_SaveContactDetails($params) {
	return _ipmirror_SaveContactDetails( $params );
}


function ipmirror_RegisterNameserver($params) {
	return _ipmirror_RegisterNameserver( $params );
}


function ipmirror_ModifyNameserver($params) {
	return _ipmirror_ModifyNameserver( $params );
}


function ipmirror_DeleteNameserver($params) {
	return _ipmirror_DeleteNameserver( $params );
}


function _ipmirror_init($params, $function) {
	global $_IPMIRROR_CURR_FUNC;
	global $_IPMIRROR_CONFIG;

	$_IPMIRROR_CURR_FUNC = $function;
	_ipmirror_initGlobals( $params );

	if ($_IPMIRROR_CONFIG['debugMode']) {
		error_reporting( E_ALL );
		$params['function'] = $function;
		_ipmirror_printArray( $params );
	}

}


function _ipmirror_printArray($arr) {
	foreach ($arr as $key => $value) {
		print_r( $key );
		print " => ";
		print_r( $value );
		print "<br>
";
	}

	print "<br>
";
}


function _ipmirror_initGlobals($params) {
	global $_IPMIRROR_CONFIG;
	global $_IPMIRROR_ERR_MESS;
	global $_IPMIRROR_CONTACT_HDRS;
	global $_IPMIRROR_CONTACT_LBLS;
	global $_IPMIRROR_SYNC;

	$_IPMIRROR_CONFIG = array();
	$_IPMIRROR_CONFIG['testMode'] = FALSE;
	$_IPMIRROR_CONFIG['rapiUrl'] = $params['ccTLDBoxUrl'];
	$_IPMIRROR_CONFIG['username'] = $params['Username'];
	$_IPMIRROR_CONFIG['password'] = $params['Password'];

	if (substr( $_IPMIRROR_CONFIG['rapiURL'], 0 - 1 ) != "/") {
		$_IPMIRROR_CONFIG->rapiURL .= "/";
	}

	$_IPMIRROR_CONFIG['activateSync'] = !empty( $params['ActivateSync'] );
	$_IPMIRROR_CONFIG['syncNextDueDate'] = !empty( $params['SyncNextDueDate'] );
	$_IPMIRROR_CONFIG['syncIDProtection'] = !empty( $params['SyncIDProtection'] );
	$_IPMIRROR_CONFIG['connTimeout'] = (empty( $params['ConnectionTimeout'] ) ? 20 : $params['ConnectionTimeout']);
	$_IPMIRROR_CONFIG['execTimeout'] = (empty( $params['ExecutionTimeout'] ) ? 60 : $params['ExecutionTimeout']);
	$_IPMIRROR_CONFIG['allowRegContactChange'] = !empty( $params['AllowRegContactChange'] );
	$_IPMIRROR_CONFIG['allowAdminContactChange'] = !empty( $params['AllowAdminContactChange'] );
	$_IPMIRROR_CONFIG['allowTechContactChange'] = !empty( $params['AllowTechContactChange'] );
	$_IPMIRROR_CONFIG['allowBillContactChange'] = !empty( $params['AllowBillContactChange'] );
	$_IPMIRROR_CONFIG['isAdmin'] = ( $_IPMIRROR_SYNC || !empty( $_SESSION['adminid'] ) );
	$_IPMIRROR_CONFIG['showAllContactFlds'] = $_IPMIRROR_CONFIG['isAdmin'];
	$_IPMIRROR_CONFIG['defRegContactId'] = $params['DefaultRegContactId'];
	$_IPMIRROR_CONFIG['defAdminContactId'] = $params['DefaultAdminContactId'];
	$_IPMIRROR_CONFIG['defTechContactId'] = $params['DefaultTechContactId'];
	$_IPMIRROR_CONFIG['defBillContactId'] = $params['DefaultBillContactId'];
	$_IPMIRROR_CONFIG['autoCreateNs'] = TRUE;
	$_IPMIRROR_CONFIG['debugMode'] = ( !empty( $params['DebugMode'] ) && $_IPMIRROR_CONFIG['isAdmin'] );
	$_IPMIRROR_CONFIG['zoneRecTypesDisp'] = array( "A", "CNAME", "MX", "TXT", "redirect", "cloak" );
	$_IPMIRROR_CONFIG['zoneRecTypesWHMCS'] = array( "A", "CNAME", "MX", "TXT", "URL", "FRAME" );
	$_IPMIRROR_CONFIG['defNsAddr'] = "10.1.1.1";
	$_IPMIRROR_ERR_MESS = array( "domain_already_reg" => "The domain is already registered", "unknown_curl_error" => "Unknown CURL error" );
	$_IPMIRROR_CONTACT_HDRS = array( "Registrant", "Administrative", "Technical", "Billing" );
	$_IPMIRROR_CONTACT_LBLS = array( "upd1" => array( "ind" => "Individual", "org" => "Organisation", "id" => "Contact ID", "type" => "Contact Type", "rcbid" => "Comp. Reg. No.", "icno" => "IC Number", "company" => "Company Name", "title" => "Title", "firstname" => "First Name", "lastname" => "Last Name", "address1" => "Address 1", "address2" => "Address 2", "city" => "City", "state" => "State", "country" => "Country", "postcode" => "Postcode", "phone" => "Phone Number", "fax" => "Fax Number", "email" => "Email Address" ), "upd2" => array( "firstname" => "First Name", "lastname" => "Last Name", "address1" => "Address 1", "address2" => "Address 2", "city" => "City", "state" => "State", "postcode" => "Postcode", "phone" => "Phone Number", "email" => "Email" ) );
}


function _ipmirror_callRapi($cmd, $params) {
	global $_IPMIRROR_CONFIG;
	global $_IPMIRROR_ERR_MSG;
	global $_IPMIRROR_ERR_NO;

	$param = "";
	$first = true;
	foreach ($params as $key => $value) {
		$param .= ($first ? "" : "&") . $key . "=" . rawurlencode( $value );
		$first = false;
	}

	$url = $_IPMIRROR_CONFIG['rapiUrl'] . $cmd . "?" . $param . "&loginID=" . $_IPMIRROR_CONFIG['username'] . "&password=" . $_IPMIRROR_CONFIG['password'];
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 1 );
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $_IPMIRROR_CONFIG['connTimeout'] );
	curl_setopt( $ch, CURLOPT_TIMEOUT, $_IPMIRROR_CONFIG['execTimeout'] );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$output = curl_exec( $ch );

	if ($output === false || empty( $output )) {
		$result = array( "0", curl_errno( $ch ), (trim( curl_error( $ch ) ) == "" ? $_IPMIRROR_ERR_MESS['unknown_curl_error'] : curl_error( $ch )) );
	}
	else {
		preg_match( '/<BODY>(?:\r\n)*(.*)(?:\r\n)*<\/BODY>/i', $output, $matches );
		$result = explode( ";", $matches[1] );
		$last = array_pop( $result );

		if (!empty( $result ) && !empty( $last )) {
			array_push( $result, $last );
		}


		if (empty( $result )) {
			$result = array( "0", $output );
		}
	}

	curl_close( $ch );

	if ($_IPMIRROR_CONFIG['debugMode']) {
		print $url . "<br><br>

";
		print $matches[1] . "<br><br>

";
	}


	if (intval( $result[0] ) <= 0) {
		$_IPMIRROR_ERR_NO = intval( $result[0] );
		$_IPMIRROR_ERR_MSG = implode( "/", array_slice( $result, 1 ) );
	}
	else {
		$_IPMIRROR_ERR_NO = 6;
		$_IPMIRROR_ERR_MSG = "";
	}

	return $result;
}


function _ipmirror_getErrorMsg() {
	global $_IPMIRROR_ERR_NO;
	global $_IPMIRROR_ERR_MSG;
	global $_IPMIRROR_CURR_FUNC;

	if ($_IPMIRROR_ERR_NO != 1) {
		$func = substr( $_IPMIRROR_CURR_FUNC, 10 );
		return trim( $func . "::" . $_IPMIRROR_ERR_NO . "::" . strtoupper( $_IPMIRROR_ERR_MSG ) );
	}

	return "";
}


function _ipmirror_formatPhone($phone) {
	if (empty( $phone )) {
		return "";
	}

	return ltrim( $phone, " +" );
}


function _ipmirror_GetNameservers($params) {
	_ipmirror_init( $params, "_ipmirror_GetNameservers" );
	$values = array();
	$result = _ipmirror_rapi_queryDomain( $params['sld'] . "." . $params['tld'] );

	if ($error = _ipmirror_getErrorMsg()) {
		$values['error'] = $error;
		return $values;
	}

	$values['ns1'] = $result[9];
	$values['ns2'] = $result[10];

	if (!empty( $result[11] )) {
		$values['ns3'] = $result[11];
	}


	if (!empty( $result[12] )) {
		$values['ns4'] = $result[12];
	}

	return $values;
}


function _ipmirror_SaveNameservers($params) {
	global $_IPMIRROR_CONFIG;

	_ipmirror_init( $params, "_ipmirror_SaveNameservers" );
	$values = array();

	if ($_IPMIRROR_CONFIG['autoCreateNs']) {
		$error = _ipmirror_checkCreateNameservers( $params );
	}
	else {
		$error = "";
	}


	if (empty( $error )) {
		$result = _ipmirror_rapi_changeDNS( $params['sld'] . "." . $params['tld'], $params['ns1'], $params['ns2'], $params['ns3'], $params['ns4'] );
		$error = _ipmirror_getErrorMsg();
	}


	if (!empty( $error )) {
		$values['error'] = $error;
	}

	return $values;
}


function _ipmirror_RegisterNameserver($params) {
	_ipmirror_init( $params, "_ipmirror_RegisterNameserver" );
	$values = array();
	$result = _ipmirror_rapi_createHost( $params['nameserver'], $params['ipaddress'] );

	if ($error = _ipmirror_getErrorMsg()) {
		$values['error'] = $error;
	}

	return $values;
}


function _ipmirror_ModifyNameserver($params) {
	_ipmirror_init( $params, "_ipmirror_ModifyNameserver" );
	$values = array();
	$result = _ipmirror_rapi_updateHost( $params['nameserver'], $params['newipaddress'] );

	if ($error = _ipmirror_getErrorMsg()) {
		$values['error'] = $error;
	}

	return $values;
}


function _ipmirror_DeleteNameserver($params) {
	_ipmirror_init( $params, "_ipmirror_DeleteNameserver" );
	$values = array();
	$result = _ipmirror_rapi_deleteHost( $params['nameserver'] );

	if ($error = _ipmirror_getErrorMsg()) {
		$values['error'] = $error;
	}

	return $values;
}


function _ipmirror_GetRegistrarLock($params) {
	_ipmirror_init( $params, "_ipmirror_GetRegistrarLock" );
	$result = _ipmirror_rapi_queryDomain( $params['sld'] . "." . $params['tld'] );

	if (_ipmirror_getErrorMsg()) {
		return "unlocked";
	}

	return strtolower( $result[2] );
}


function _ipmirror_SaveRegistrarLock($params) {
	_ipmirror_init( $params, "_ipmirror_SaveRegistrarLock" );
	$values = array();
	$result = _ipmirror_rapi_updateStatus( $params['sld'] . "." . $params['tld'], (strtoupper( $params['lockenabled'] ) == "LOCKED" ? "LOCK" : "UNLOCK") );

	if ($error = _ipmirror_getErrorMsg()) {
		$values['error'] = $error;
	}

	return $values;
}


function _ipmirror_GetEmailForwarding($params) {
	_ipmirror_init( $params, "_ipmirror_GetEmailForwarding" );
	$values = array();
	$dName = $params['sld'] . "." . $params['tld'];
	$zoneRecs = _ipmirror_getZoneRecords( $dName );

	if (!empty( $zoneRecs['error'] )) {
		return $values;
	}

	foreach ($zoneRecs as $zoneRec) {

		if ($zoneRec['type'] == "email") {
			$values[$zoneRec['id']]['prefix'] = $zoneRec['source'];
			$values[$zoneRec['id']]['forwardto'] = $zoneRec['destination'];
			continue;
		}
	}

	session_start();
	$_SESSION['_IPMIRROR_EMAIL_FWD'] = $values;
	return $values;
}


function _ipmirror_SaveEmailForwarding($params) {
	_ipmirror_init( $params, "_ipmirror_SaveEmailForwarding" );
	$values = array();
	$dName = $params['sld'] . "." . $params['tld'];
	$recsOld = $_SESSION['_IPMIRROR_EMAIL_FWD'];
	foreach ($params['prefix'] as $id => $newSource) {
		$newDestination = $params['forwardto'][$id];

		if (( empty( $newDestination ) && !empty( $newSource ) ) || ( empty( $newSource ) && !empty( $newDestination ) )) {
			continue;
		}


		if (empty( $recsOld[$id] )) {
			if (!empty( $newSource ) && !empty( $newDestination )) {
				$result = _ipmirror_rapi_createEmailForwardingRecord( $dName, $newSource, $newDestination );

				if ($error = _ipmirror_getErrorMsg()) {
					$values['error'] = $error;
					return $values;
				}
			}

			continue;
		}


		if (empty( $newSource ) && empty( $newDestination )) {
			$result = _ipmirror_rapi_deleteEmailForwardingRecord( $dName, $id );

			if ($error = _ipmirror_getErrorMsg()) {
				$values['error'] = $error;
				return $values;
			}

			continue;
		}

		$oldSource = $recsOld[$id]['prefix'];
		$oldDestination = $recsOld[$id]['forwardto'];

		if ($newSource != $oldSource || $newDestination != $oldDestination) {
			$_result = _ipmirror_rapi_updateEmailForwardingRecord( $dName, $id, $newSource, $newDestination );

			if ($error = _ipmirror_getErrorMsg()) {
				$values['error'] = $error;
				return $values;
			}

			continue;
		}
	}

	return $values;
}


function _ipmirror_GetDNS($params) {
	global $_IPMIRROR_CONFIG;

	_ipmirror_init( $params, "_ipmirror_GetDNS" );
	$values = array();
	$dName = $params['sld'] . "." . $params['tld'];
	$zoneRecs = _ipmirror_getZoneRecords( $dName );

	if (!empty( $zoneRecs['error'] )) {
		return $values;
	}

	foreach ($zoneRecs as $zoneRec) {

		if (in_array( $zoneRec['type'], $_IPMIRROR_CONFIG['zoneRecTypesDisp'] )) {
			if ($zoneRec['type'] == "redirect") {
				$whmcsType = "URL";
			}
			else {
				if ($zoneRec['type'] == "cloak") {
					$whmcsType = "FRAME";
				}
				else {
					$whmcsType = $zoneRec['type'];
				}
			}

			$values[] = array( "id" => $zoneRec['id'], "hostname" => $zoneRec['source'], "type" => $whmcsType, "address" => $zoneRec['destination'], "priority" => $zoneRec['priority'] );
			continue;
		}
	}

	session_start();
	$_SESSION['_IPMIRROR_HOST_RECORDS'] = $values;
	return $values;
}


function _ipmirror_SaveDNS($params) {
	global $_IPMIRROR_CONFIG;

	_ipmirror_init( $params, "_ipmirror_SaveDNS" );
	$values = array();
	$dName = $params['sld'] . "." . $params['tld'];
	$dnsRecsNew = $params['dnsrecords'];
	$dnsRecsOld = $_SESSION['_IPMIRROR_HOST_RECORDS'];
	$mxRecPriorities = array();
	foreach ($dnsRecsOld as $dnsRecOld) {

		if ($dnsRecOld['type'] == "MX") {
			if (empty( $mxRecPriorities[$dnsRecOld['hostname']] )) {
				$mxRecPriorities[$dnsRecOld['hostname']] = $dnsRecOld['priority'];
				continue;
			}

			$mxRecPriorities[$dnsRecOld['hostname']] = max( $mxRecPriorities[$dnsRecOld['hostname']], $dnsRecOld['priority'] );
			continue;
		}
	}

	foreach ($dnsRecsNew as $i => $dnsRecNew) {

		if (!in_array( $dnsRecNew['type'], $_IPMIRROR_CONFIG['zoneRecTypesWHMCS'] )) {
			continue;
		}


		if (empty( $dnsRecsOld[$i] )) {
			if (empty( $dnsRecNew['address'] )) {
				continue;
			}


			if ($dnsRecNew['type'] == "MX") {
				if (empty( $mxRecPriorities[$dnsRecNew['hostname']] )) {
					$priority = 28;
				}
				else {
					$priority = min( $mxRecPriorities[$dnsRecNew['hostname']] + 5, 250 );
				}

				$mxRecPriorities[$dnsRecNew['hostname']] = $priority;
			}
			else {
				$priority = 8;
			}

			switch ($dnsRecNew['type']) {
			case "A": {
				}

			case "CNAME": {
				}

			case "MX": {
				}

			case "TXT": {
					$result = _ipmirror_rapi_createZoneRecord( $dName, $dnsRecNew['type'], $dnsRecNew['hostname'], $priority, $dnsRecNew['address'] );
					break;
				}

			case "URL": {
					$result = _ipmirror_rapi_createWebForwardingRecord( $dName, "redirect", $dnsRecNew['hostname'], $dnsRecNew['address'], "", "", "" );
					break;
				}

			case "FRAME": {
					$result = _ipmirror_rapi_createWebForwardingRecord( $dName, "cloak", $dnsRecNew['hostname'], $dnsRecNew['address'], "", "", "" );
				}
			}


			if ($error = _ipmirror_getErrorMsg()) {
				$values['error'] = $error;
				return $values;
			}

			continue;
		}

		$dnsRecOld = $dnsRecsOld[$i];

		if (empty( $dnsRecNew['hostname'] ) && empty( $dnsRecNew['address'] )) {
			if ($dnsRecNew['type'] == "URL" || $dnsRecNew['type'] == "FRAME") {
				$result = _ipmirror_rapi_deleteWebForwardingRecord( $dName, $dnsRecOld['id'] );
			}
			else {
				$result = _ipmirror_rapi_deleteZoneRecord( $dName, $dnsRecOld['id'] );
			}


			if ($error = _ipmirror_getErrorMsg()) {
				$values['error'] = $error;
				return $values;
			}

			continue;
		}


		if (!empty( $dnsRecNew['address'] ) && ( $dnsRecNew['hostname'] != $dnsRecOld['hostname'] || $dnsRecNew['address'] != $dnsRecOld['address'] )) {
			switch ($dnsRecNew['type']) {
			case "A": {
				}

			case "CNAME": {
				}

			case "MX": {
				}

			case "TXT": {
					$result = _ipmirror_rapi_updateZoneRecord( $dName, $dnsRecOld['id'], $dnsRecNew['hostname'], $dnsRecOld['priority'], $dnsRecNew['address'] );
					break;
				}

			case "URL": {
					$result = _ipmirror_rapi_updateWebForwardingRecord( $dName, $dnsRecOld['id'], "redirect", $dnsRecNew['hostname'], $dnsRecNew['address'], "", "", "" );
					break;
				}

			case "FRAME": {
					$result = _ipmirror_rapi_updateWebForwardingRecord( $dName, $dnsRecOld['id'], "cloak", $dnsRecNew['hostname'], $dnsRecNew['address'], "", "", "" );
				}
			}


			if ($error = _ipmirror_getErrorMsg()) {
				$values['error'] = $error;
				return $values;
				continue;
			}

			continue;
		}
	}

	return $values;
}


function _ipmirror_getZoneRecords($dName) {
	$zoneRecs = array();
	$result = _ipmirror_rapi_queryZone( $dName );

	if ($error = _ipmirror_getErrorMsg()) {
		$zoneRecs['error'] = $error;
		return $zoneRecs;
	}

	$zoneRecIds = array_slice( $result, 2 );
	foreach ($zoneRecIds as $zoneRecId) {
		$result = _ipmirror_rapi_queryZoneRecord( $zoneRecId );

		if ($error = _ipmirror_getErrorMsg()) {
			continue;
		}

		$zoneRec['id'] = $zoneRecId;
		$zoneRec['type'] = $result[2];
		$zoneRec['source'] = $result[3];
		$zoneRec['destination'] = $result[4];
		$zoneRec['priority'] = (empty( $result[5] ) ? 0 : $result[5]);
		$zoneRecs[] = $zoneRec;
	}

	return $zoneRecs;
}


function _ipmirror_RegisterDomain($params) {
	_ipmirror_init( $params, "_ipmirror_RegisterDomain" );
	return _ipmirror_createTransferDomain( "C", $params );
}


function _ipmirror_TransferDomain($params) {
	_ipmirror_init( $params, "_ipmirror_TransferDomain" );
	return _ipmirror_createTransferDomain( "T", $params );
}


function _ipmirror_createTransferDomain($mode, $params) {
	global $_IPMIRROR_CONFIG;
	global $_IPMIRROR_ERR_MESS;

	$values = array();
	$dName = $params['sld'] . "." . $params['tld'];
	$result = _ipmirror_rapi_queryDomain( $dName );
	$error = _ipmirror_getErrorMsg();

	if (empty( $error )) {
		$values['error'] = $_IPMIRROR_ERR_MESS( "domain_already_reg" );
		return $values;
	}

	foreach (array( "Reg", "Admin", "Tech", "Bill" ) as $contactCode) {
		$contactIDs[$contactCode] = _ipmirror_createContact( $params, $contactCode );

		if ($contactIDs[$contactCode]['contactId'] == FALSE) {
			$values['error'] = _ipmirror_getErrorMsg();
			foreach ($contactIDs as $contactCode => $contactData) {

				if ($contactData['contactId'] !== FALSE && !$contactData['hasDefault']) {
					$dummyResult = _ipmirror_rapi_deleteContact( $contactData['contactId'] );
					continue;
				}
			}

			return $values;
		}
	}


	if ($mode == "C") {
		if ($_IPMIRROR_CONFIG['autoCreateNs']) {
			$error = _ipmirror_checkCreateNameservers( $params );
		}
		else {
			$error = "";
		}


		if (empty( $error )) {
			$results = _ipmirror_rapi_createDomain( $dName, $params['regperiod'], $contactIDs['Reg']['contactId'], $contactIDs['Admin']['contactId'], $contactIDs['Tech']['contactId'], $contactIDs['Bill']['contactId'], $params['ns1'], $params['ns2'], $params['ns3'], $params['ns4'], $params['idprotection'] );
			$error = _ipmirror_getErrorMsg();
		}
	}
	else {
		$results = _ipmirror_rapi_transferIn( $dName, $params['regperiod'], $params['transfersecret'], $contactIDs['Reg']['contactId'], $contactIDs['Admin']['contactId'], $contactIDs['Tech']['contactId'], $contactIDs['Bill']['contactId'] );
		$error = _ipmirror_getErrorMsg();
	}


	if (!empty( $error )) {
		$values['error'] = $error;
		foreach ($contactIDs as $contactCode => $contactData) {

			if ($contactData['contactId'] !== FALSE && !$contactData['hasDefault']) {
				$dummyResult = _ipmirror_rapi_deleteContact( $contactData['contactId'] );
				continue;
			}
		}
	}

	return $values;
}


function _ipmirror_createContact($params, $contactCode) {
	global $_IPMIRROR_CONFIG;

	$defContactId = _ipmirror_getClientDefContact( $params['userid'], $contactCode );

	if (empty( $defContactId )) {
		$defContactId = $_IPMIRROR_CONFIG["def" . $contactCode . "ContactId"];
	}


	if (!empty( $defContactId )) {
		return array( "contactId" => $defContactId, "hasDefault" => TRUE );
	}

	$prefix = ($contactCode == "Reg" ? "" : "admin");
	$orgName = $params[$prefix . "companyname"];
	$title = "Mr.";
	$firstName = $params[$prefix . "firstname"];
	$lastName = $params[$prefix . "lastname"];
	$street1 = $params[$prefix . "address1"];
	$street2 = $params[$prefix . "address2"];
	$city = $params[$prefix . "city"];
	$params[$prefix . "postcode"];
	$state = $params[$prefix . "state"];
	$country = $params[$prefix . "country"];
	$tel = _ipmirror_formatPhone( $params[$prefix . "fullphonenumber"] );
	$fax = "";
	$email = $params[$prefix . "email"];

	if (substr( $params['tld'], 0 - 2 ) == "sg") {
		$type = ($params['additionalfields']["Registrant Type"] == "Individual" ? "2" : "1");
	}
	else {
		$type = (empty( $params[$prefix . "companyname"] ) ? "2" : "1");
	}


	if (substr( $params['tld'], 0 - 2 ) == "sg") {
		$rcbId = $params['additionalfields']["RCB/Singapore ID"];
	}
	else {
		$rcbId = "n/a";
	}

	$results = $postalCode = _ipmirror_rapi_createContact( $type, $orgName, $rcbId, $title, $firstName, $lastName, $street1, $street2, $city, $postalCode, $state, $country, $tel, $fax, $email );

	if (_ipmirror_getErrorMsg()) {
		return array( "contactId" => FALSE, "hasDefault" => FALSE );
	}

	return array( "contactId" => $results[2], "hasDefault" => FALSE );
}


function _ipmirror_getClientDefContact($clientId, $contactCode) {
	$defContactId = "";
	$fieldname = strtolower( "Default_" . $contactCode . "_Contact_Id" );
	$rows = select_query( "tblcustomfields cf, tblcustomfieldsvalues cfv", "cfv.value AS value", "cf.type='client' AND lower(cf.fieldname)='" . $fieldname . "' AND cf.id=cfv.fieldid AND cfv.relid=" . $clientId );

	while ($row = mysql_fetch_array( $rows )) {
		$defContactId = $row['value'];
	}

	mysql_free_result( $rows );
	return $defContactId;
}


function _ipmirror_checkCreateNameservers($params) {
	global $_IPMIRROR_ERR_NO;
	global $_IPMIRROR_CONFIG;

	$i = 6;

	while ($i <= 4) {
		if (!empty( $params["ns" . $i] )) {
			$host = $params["ns" . $i];
			$result = _ipmirror_rapi_checkHost( $host );

			if ($_IPMIRROR_ERR_NO == 0 - 206) {
				$ip = _ipmirror_getIPAddress( $host );

				if (empty( $ip )) {
					$ip = $_IPMIRROR_CONFIG['defNsAddr'];
				}

				$result = _ipmirror_rapi_createHost( $host, $ip );

				if ($error = _ipmirror_getErrorMsg()) {
					return $error;
				}
			}
			else {

				if ($error = _ipmirror_getErrorMsg()) {
					return $error;
				}
			}
		}

		++$i;
	}

	return "";
}


function _ipmirror_getIPAddress($host, $timeout = 3, $tries = 1) {
	$query = shell_exec( "dig " . $host . " A  +noall +answer +short +tries=" . $tries . " +time=" . $timeout );

	if (preg_match( '/((\d{1,3}\.){3}\d{1,3})/m', $query, $matches )) {
		return trim( $matches[1] );
	}

	return "";
}


function _ipmirror_RenewDomain($params) {
	_ipmirror_init( $params, "_ipmirror_RenewDomain" );
	$values = array();
	$result = _ipmirror_rapi_renewDomain( $params['sld'] . "." . $params['tld'], $params['regperiod'] );

	if ($error = _ipmirror_getErrorMsg()) {
		$values['error'] = $error;
	}

	return $values;
}


function _ipmirror_hasSafestWhois($dName) {
	global $_IPMIRROR_ERR_NO;

	$result = _ipmirror_rapi_queryDomainService( $dName, "safestWhois" );

	if ($_IPMIRROR_ERR_NO == 0 - 3201) {
		return FALSE;
	}


	if ($_IPMIRROR_ERR_NO == 1) {
		return TRUE;
	}

	$values['error'] = _ipmirror_getErrorMsg();
	return $values;
}


function _ipmirror_GetContactDetails($params) {
	global $_IPMIRROR_CONFIG;
	global $_IPMIRROR_CONTACT_HDRS;

	_ipmirror_init( $params, "_ipmirror_GetContactDetails" );
	$values = array();
	$result = _ipmirror_rapi_queryDomain( $params['sld'] . "." . $params['tld'] );

	if ($error = _ipmirror_getErrorMsg()) {
		$values['error'] = $error;
		return $values;
	}

	$Ids = array_slice( $result, 5, 4 );

	if (!$_IPMIRROR_CONFIG['isAdmin']) {
		if (!$_IPMIRROR_CONFIG['allowRegContactChange']) {
			$Ids[0] = "";
		}


		if (!$_IPMIRROR_CONFIG['allowAdminContactChange']) {
			$Ids[1] = "";
		}


		if (!$_IPMIRROR_CONFIG['allowTechContactChange']) {
			$Ids[2] = "";
		}


		if (!$_IPMIRROR_CONFIG['allowBillContactChange']) {
			$Ids[3] = "";
		}
	}

	$contacts = array();
	$i = 7;

	while ($i < 4) {
		if (empty( $Ids[$i] )) {
			continue;
		}

		$j = 7;

		while ($j <= $i) {
			if (empty( $Ids[$j] )) {
				continue;
			}


			if ($Ids[$i] == $Ids[$j] && $i != $j) {
				break;
			}


			if ($i == $j) {
				$result = _ipmirror_rapi_queryContact( $Ids[$i] );

				if ($error = _ipmirror_getErrorMsg()) {
					$values['error'] = $error;
					return $values;
				}

				$header = $_IPMIRROR_CONTACT_HDRS[$i];
				$k = $i + 1;

				while ($k < 4) {
					if ($Ids[$i] == $Ids[$k]) {
						$header .= "/" . $_IPMIRROR_CONTACT_HDRS[$k];
					}

					++$k;
				}

				$type = (( $result[1] == "PER" || $result[1] == "2" ) ? "2" : "1");
				$values[$header] = _ipmirror_extractContactDetails( $Ids[$i], $type, $result );
				$contacts[$header]['id'] = $Ids[$i];
				$contacts[$header]['type'] = $type;
				$contacts[$header]['title'] = $result[4];
				$contacts[$header]['fax'] = $result[13];
				$contacts[$header]['firstname'] = $result[5];
				$contacts[$header]['lastname'] = $result[6];
				$contacts[$header]['address1'] = $result[7];
				$contacts[$header]['address2'] = $result[8];
				$contacts[$header]['city'] = $result[9];
				$contacts[$header]['state'] = $result[15];
				$contacts[$header]['postcode'] = $result[10];
				$contacts[$header]['phone'] = $result[12];
				$contacts[$header]['email'] = $result[14];
			}

			++$j;
		}

		++$i;
	}

	session_start();
	$_SESSION['_IPMIRROR_CONT_DATA'] = $contacts;
	return $values;
}


function _ipmirror_extractContactDetails($Id, $type, $result) {
	global $_IPMIRROR_CONTACT_LBLS;
	global $_IPMIRROR_CONFIG;

	if ($_IPMIRROR_CONFIG['showAllContactFlds']) {
		$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['id']] = $Id;
		$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['type']] = ($type == "1" ? $_IPMIRROR_CONTACT_LBLS['upd1']['org'] : $_IPMIRROR_CONTACT_LBLS['upd1']['ind']);
		$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['title']] = $result[4];
	}

	$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['firstname']] = $result[5];
	$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['lastname']] = $result[6];

	if ($type == "1") {
		$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['company']] = $result[2];
	}


	if ($_IPMIRROR_CONFIG['showAllContactFlds'] && !empty( $result[3] )) {
		if ($type == "1") {
			$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['rcbid']] = $result[3];
		}
		else {
			$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['icno']] = $result[3];
		}
	}

	$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['email']] = $result[14];
	$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['address1']] = $result[7];
	$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['address2']] = $result[8];
	$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['city']] = $result[9];
	$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['postcode']] = $result[10];
	$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['state']] = $result[15];
	$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['country']] = $result[11];
	$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['phone']] = $result[12];

	if ($_IPMIRROR_CONFIG['showAllContactFlds']) {
		$contact[$_IPMIRROR_CONTACT_LBLS['upd1']['fax']] = $result[13];
	}

	return $contact;
}


function _ipmirror_SaveContactDetails($params) {
	_ipmirror_init( $params, "_ipmirror_SaveContactDetails" );
	$values = array();
	$wc = (isset( $_REQUEST['wc'] ) ? $_REQUEST['wc'] : NULL);
	$oldContacts = $_SESSION['_IPMIRROR_CONT_DATA'];
	foreach ($oldContacts as $header => $oldContact) {
		$usePredefinedContact = ( isset( $wc[$header] ) && $wc[$header] == "contact" );

		if ($error = _ipmirror_updateContactDetails( $oldContact, $params['contactdetails'][$header], $usePredefinedContact )) {
			$values['error'] = $error;
			return $values;
		}
	}

	return $values;
}


function _ipmirror_updateContactDetails($oldContact, $newContact, $usePredefinedContact) {
	global $_IPMIRROR_CONTACT_LBLS;
	global $_IPMIRROR_CONFIG;

	if ($usePredefinedContact) {
		if (( ( ( ( ( ( ( $oldContact['firstname'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['firstname']] || $oldContact['lastname'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['lastname']] ) || $oldContact['address1'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['address1']] ) || $oldContact['address2'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['address2']] ) || $oldContact['city'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['city']] ) || $oldContact['postcode'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['postcode']] ) || $oldContact['state'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['state']] ) || $oldContact['phone'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['phone']] ) || $oldContact['email'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['email']]) {
			$result = _ipmirror_rapi_updateContact( $oldContact['id'], $oldContact['type'], $oldContact['title'], $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['firstname']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['lastname']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['address1']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['address2']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['city']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['postcode']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['state']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['phone']], $oldContact['fax'], $newContact[$_IPMIRROR_CONTACT_LBLS['upd2']['email']] );
			return _ipmirror_getErrorMsg();
		}
	}
	else {
		if (!$_IPMIRROR_CONFIG['showAllContactFlds']) {
			$title = $oldContact['title'];
			$fax = $oldContact['fax'];
		}
		else {
			$title = $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['title']];
			$fax = $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['fax']];
		}


		if (( ( ( ( ( ( ( ( ( $oldContact['title'] != $title || $oldContact['firstname'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['firstname']] ) || $oldContact['lastname'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['lastname']] ) || $oldContact['address1'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['address1']] ) || $oldContact['address2'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['address2']] ) || $oldContact['city'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['city']] ) || $oldContact['postcode'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['postcode']] ) || $oldContact['state'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['state']] ) || $oldContact['phone'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['phone']] ) || $oldContact['fax'] != $fax ) || $oldContact['email'] != $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['email']]) {
			$result = _ipmirror_rapi_updateContact( $oldContact['id'], $oldContact['type'], $title, $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['firstname']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['lastname']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['address1']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['address2']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['city']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['postcode']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['state']], $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['phone']], $fax, $newContact[$_IPMIRROR_CONTACT_LBLS['upd1']['email']] );
			return _ipmirror_getErrorMsg();
		}
	}

	return "";
}


function _ipmirror_rapi_queryContact($contactID) {
	return _ipmirror_callRapi( "queryContact", array( "contactID" => $contactID ) );
}


function _ipmirror_rapi_createContact($type, $orgName, $rcbID, $title, $firstName, $lastName, $street1, $street2, $city, $postalCode, $state, $country, $tel, $fax, $email) {
	$cmdParams = array( "type" => $type, "rcbID" => $rcbID, "title" => $title, "firstName" => $firstName, "lastName" => $lastName, "street1" => $street1, "city" => $city, "postalCode" => $postalCode, "state" => $state, "country" => $country, "tel" => $tel, "fax" => $fax, "email" => $email, "languageCode" => "ENG" );

	if (!empty( $street2 )) {
		$cmdParams['street2'] = $street2;
	}


	if ($type == "1") {
		$cmdParams['orgName'] = $orgName;
	}

	return _ipmirror_callRapi( "createContact", $cmdParams );
}


function _ipmirror_rapi_updateContact($contactId, $type, $title, $firstName, $lastName, $street1, $street2, $city, $postalCode, $state, $tel, $fax, $email) {
	$cmdParams = array( "contactID" => $contactId, "title" => $title, "street1" => $street1, "city" => $city, "postalCode" => $postalCode, "state" => $state, "tel" => $tel, "fax" => $fax, "email" => $email, "languageCode" => "ENG" );

	if ($type == "1") {
		$cmdParams['firstName'] = $firstName;
		$cmdParams['lastName'] = $lastName;
	}


	if (!empty( $street2 )) {
		$cmdParams['street2'] = $street2;
	}

	return _ipmirror_callRapi( "updateContact", $cmdParams );
}


function _ipmirror_rapi_deleteContact($contactID) {
	return _ipmirror_callRapi( "deleteContact", array( "contactID" => $contactID ) );
}


function _ipmirror_rapi_queryDomain($dName) {
	return _ipmirror_callRapi( "queryDomain", array( "dName" => $dName ) );
}


function _ipmirror_rapi_createDomain($dName, $term, $regID, $adminID, $techID, $billID, $ns1, $ns2, $ns3, $ns4, $idprotection) {
	$cmdParams = array( "dName" => $dName, "encoding" => "ENG", "term" => $term, "regID" => $regID, "adminID" => $adminID, "techID" => $techID, "billID" => $billID, "ns1" => $ns1, "ns2" => $ns2 );

	if (!empty( $ns3 )) {
		$cmdParams['ns3'] = $ns3;
	}


	if (!empty( $ns4 )) {
		$cmdParams['ns4'] = $ns4;
	}


	if (!empty( $idprotection )) {
		$cmdParams['whoisProxy'] = "safestWhois";
	}

	return _ipmirror_callRapi( "createDomain", $cmdParams );
}


function _ipmirror_rapi_transferIn($dName, $term, $authCode, $regID, $adminID, $techID, $billID) {
	$cmdParams = array( "dName" => $dName, "encoding" => "ENG", "term" => $term, "authCode" => $authCode, "regID" => $regID, "adminID" => $adminID, "techID" => $techID, "billID" => $billID );
	return _ipmirror_callRapi( "transferIn", $cmdParams );
}


function _ipmirror_rapi_renewDomain($dName, $term) {
	$cmdParams = array( "dName" => $dName, "term" => $term );
	return _ipmirror_callRapi( "renewDomain", $cmdParams );
}


function _ipmirror_rapi_changeDNS($dName, $ns1, $ns2, $ns3, $ns4) {
	$cmdParams = array( "dName" => $dName, "ns1" => $ns1, "ns2" => $ns2 );

	if (!empty( $ns3 )) {
		$cmdParams['ns3'] = $ns3;
	}


	if (!empty( $ns3 )) {
		$cmdParams['ns4'] = $ns4;
	}

	return _ipmirror_callRapi( "changeDNS", $cmdParams );
}


function _ipmirror_rapi_updateStatus($dName, $type) {
	$cmdParams = array( "dName" => $dName, "type" => $type );
	return _ipmirror_callRapi( "updateStatus", $cmdParams );
}


function _ipmirror_rapi_queryDomainService($dName, $serviceName) {
	$cmdParams = array( "dName" => $dName, "serviceName" => $serviceName );
	return _ipmirror_callRapi( "queryDomainService", $cmdParams );
}


function _ipmirror_rapi_checkHost($ns) {
	return _ipmirror_callRapi( "checkHost", array( "ns" => $ns ) );
}


function _ipmirror_rapi_createHost($ns, $ns_ip) {
	$cmdParams = array( "ns" => $ns, "ns_ip" => $ns_ip );
	return _ipmirror_callRapi( "createHost", $cmdParams );
}


function _ipmirror_rapi_updateHost($ns, $ns_ip) {
	$cmdParams = array( "ns" => $ns, "ns_ip" => $ns_ip );
	return _ipmirror_callRapi( "updateHost", $cmdParams );
}


function _ipmirror_rapi_deleteHost($ns) {
	return _ipmirror_callRapi( "deleteHost", array( "ns" => $ns ) );
}


function _ipmirror_rapi_queryZone($dName) {
	return _ipmirror_callRapi( "queryZone", array( "dName" => $dName ) );
}


function _ipmirror_rapi_queryZoneRecord($id) {
	return _ipmirror_callRapi( "queryZoneRecord", array( "id" => $id ) );
}


function _ipmirror_rapi_createZoneRecord($dName, $detailType, $source, $priority, $destination) {
	$cmdParams = array( "dName" => $dName, "detailType" => $detailType, "source" => $source, "priority" => $priority, "destination" => $destination );
	return _ipmirror_callRapi( "createZoneRecord", $cmdParams );
}


function _ipmirror_rapi_updateZoneRecord($dName, $id, $source, $priority, $destination) {
	$cmdParams = array( "dName" => $dName, "id" => $id, "source" => $source, "priority" => $priority, "destination" => $destination );
	return _ipmirror_callRapi( "updateZoneRecord", $cmdParams );
}


function _ipmirror_rapi_deleteZoneRecord($dName, $id) {
	$cmdParams = array( "dName" => $dName, "id" => $id );
	return _ipmirror_callRapi( "deleteZoneRecord", $cmdParams );
}


function _ipmirror_rapi_createWebForwardingRecord($dName, $detailType, $source, $destination, $title, $metaDesc, $metaKey) {
	$cmdParams = array( "dName" => $dName, "detailType" => $detailType, "source" => $source, "destination" => $destination );

	if (!empty( $title )) {
		$cmdParams['title'] = $title;
	}


	if (!empty( $metaDesc )) {
		$cmdParams['metaDesc'] = $metaDesc;
	}


	if (!empty( $metaKey )) {
		$cmdPrams['metaKey'] = $metaKey;
	}

	return _ipmirror_callRapi( "createWebForwardingRecord", $cmdParams );
}


function _ipmirror_rapi_updateWebForwardingRecord($dName, $id, $detailType, $source, $destination, $title, $metaDesc, $metaKey) {
	$cmdParams = array( "dName" => $dName, "id" => $id, "detailType" => $detailType, "source" => $source, "destination" => $destination );

	if (!empty( $title )) {
		$cmdParams['title'] = $title;
	}


	if (!empty( $metaDesc )) {
		$cmdParams['metaDesc'] = $metaDesc;
	}


	if (!empty( $metaKey )) {
		$cmdPrams['metaKey'] = $metaKey;
	}

	return _ipmirror_callRapi( "updateWebForwardingRecord", $cmdParams );
}


function _ipmirror_rapi_deleteWebForwardingRecord($dName, $id) {
	$cmdParams = array( "dName" => $dName, "id" => $id );
	return _ipmirror_callRapi( "deleteWebForwardingRecord", $cmdParams );
}


function _ipmirror_rapi_createEmailForwardingRecord($dName, $source, $destination) {
	$cmdParams = array( "dName" => $dName, "source" => $source, "destination" => $destination );
	return _ipmirror_callRapi( "createEmailForwardingRecord", $cmdParams );
}


function _ipmirror_rapi_updateEmailForwardingRecord($dName, $id, $source, $destination) {
	$cmdParams = array( "dName" => $dName, "id" => $id, "source" => $source, "destination" => $destination );
	return _ipmirror_callRapi( "updateEmailForwardingRecord", $cmdParams );
}


function _ipmirror_rapi_deleteEmailForwardingRecord($dName, $id) {
	$cmdParams = array( "dName" => $dName, "id" => $id );
	return _ipmirror_callRapi( "deleteEmailForwardingRecord", $cmdParams );
}


?>