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

function resellerclubssl_ConfigOptions() {
	$pid = $_GET['id'];
	$customfieldid = get_query_val( "tblcustomfields", "id", "type='product' AND relid=" . (int)$pid . " AND fieldname LIKE 'Domain Name%'" );

	if (!$customfieldid) {
		insert_query( "tblcustomfields", array( "type" => "product", "relid" => $pid, "fieldname" => "Domain Name", "fieldtype" => "text", "description" => "Enter the domain name you want to protect", "required" => "on", "showorder" => "on", "showinvoice" => "on" ) );
	}

	$configarray = array( "Reseller ID" => array( "Type" => "text", "Size" => "20", "Description" => "Obtained from ResellerClub Settings > Personal Information > Primary Profile" ), "Password" => array( "Type" => "password", "Size" => "20", "Description" => "Your ResellerClub account password" ), "Certificate Type" => array( "Type" => "dropdown", "Options" => "SSL123,SGC SuperCert,Web Server,Wildcard" ), "Test Mode" => array( "Type" => "yesno" ) );
	return $configarray;
}


function resellerclubssl_CreateAccount($params) {
	require ROOTDIR . "/includes/countriescallingcodes.php";
	$result = select_query( "tblsslorders", "COUNT(*)", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );

	if ($data[0]) {
		return "An SSL Order already exists for this order";
	}

	$domainname = $params['domain'];

	if ($params['customfields']["Domain Name"]) {
		$domainname = $params['customfields']["Domain Name"];
	}

	updateService( array( "domain" => $domainname, "username" => "", "password" => "" ) );
	$certtype = ($params['configoptions']["Certificate Type"] ? $params['configoptions']["Certificate Type"] : $params['configoption3']);
	$certyears = 7;

	if ($params['configoptions']['Years']) {
		$certyears = $params['configoptions']['Years'];
	}
	else {
		$billingcycle = get_query_val( "tblhosting", "billingcycle", array( "id" => $params['serviceid'] ) );

		if ($billingcycle == "Biennially") {
			$certyears = 8;
		}
		else {
			if ($billingcycle == "Triennially") {
				$certyears = 9;
			}
		}
	}


	if ($certtype == "SSL123") {
		$apicerttype = "fssl";
	}
	else {
		if ($certtype == "SGC SuperCert") {
			$apicerttype = "sgc";
		}
		else {
			if ($certtype == "Web Server") {
				$apicerttype = "ssl";
			}
			else {
				if ($certtype == "Wildcard") {
					$apicerttype = "wild";
				}
			}
		}
	}

	$postfields = array();
	$postfields['auth-userid'] = $params['configoption1'];
	$postfields['auth-password'] = $params['configoption2'];
	$postfields['username'] = $params['clientsdetails']['email'];
	$result = resellerclubssl_SendCommand( "details", "customers", $postfields, $params, "GET" );
	unset( $postfields['username'] );

	if ($result['response']['status'] == "ERROR") {
		$postfields['lang-pref'] = resellerclubssl_Language( $params['clientsdetails']['language'] );
		$postfields['username'] = $params['clientsdetails']['email'];
		$postfields['passwd'] = resellerclubssl_genLBRandomPW();
		$postfields['name'] = $params['clientsdetails']['firstname'] . " " . $params['clientsdetails']['lastname'];
		$postfields['company'] = ($params['clientsdetails']['companyname'] ? $params['clientsdetails']['companyname'] : "N/A");
		$postfields['address-line-1'] = $params['clientsdetails']['address1'];
		$postfields['address-line-2'] = $params['clientsdetails']['address1'];
		$postfields['city'] = $params['clientsdetails']['city'];
		$postfields['state'] = $params['clientsdetails']['state'];
		$postfields['zipcode'] = $params['clientsdetails']['postcode'];
		$postfields['country'] = $params['clientsdetails']['country'];
		$postfields['phone'] = preg_replace( "/[^0-9]/", "", $params['clientsdetails']['phonenumber'] );
		$postfields['phone-cc'] = $countrycallingcodes[$params['clientsdetails']['country']];
		$result = resellerclubssl_SendCommand( "signup", "customers", $postfields, $params, "POST" );

		if ($result['response']['status'] == "ERROR") {
			return $result['response']['message'];
		}

		$customerid = $result['int'];
		unset( $postfields );
		$postfields = array();
		$postfields['auth-userid'] = $params['configoption1'];
		$postfields['auth-password'] = $params['configoption2'];
	}
	else {
		foreach ($result['hashtable']['entry'] as $entry => $value) {

			if ($value['string'][0] == "customerid") {
				$customerid = $value['string'][1];
				continue;
			}
		}
	}


	if (!$customerid) {
		return array( "error" => "Error obtaining customer id" );
	}

	$postfields['customer-id'] = $customerid;
	$postfields['domain-name'] = $domainname;
	$postfields['years'] = $certyears;
	$postfields['additional-licenses'] = 0;
	$postfields['cert-key'] = $apicerttype;
	$postfields['invoice-option'] = "NoInvoice";
	$result = resellerclubssl_SendCommand( "add", "digitalcertificate", $postfields, $params, "POST" );

	if ($result['response']['status'] == "ERROR") {
		return $result['response']['message'];
	}

	foreach ($result['hashtable']['entry'] as $entry => $value) {

		if ($value['string'][0] == "orderid") {
			$orderid = $value['string'][1];
			continue;
		}
	}

	$sslorderid = insert_query( "tblsslorders", array( "userid" => $params['clientsdetails']['userid'], "serviceid" => $params['serviceid'], "remoteid" => $orderid, "module" => "resellerclubssl", "certtype" => $certtype, "status" => "Awaiting Configuration" ) );

	if (!$orderid) {
		return "Unable to obtain Order-ID";
	}

	global $CONFIG;

	$sslconfigurationlink = $CONFIG['SystemURL'] . "/configuressl.php?cert=" . md5( $sslorderid );
	$sslconfigurationlink = "<a href=\"" . $sslconfigurationlink . "\">" . $sslconfigurationlink . "</a>";
	sendMessage( "SSL Certificate Configuration Required", $params['serviceid'], array( "ssl_configuration_link" => $sslconfigurationlink ) );
	return "success";
}


function resellerclubssl_TerminateAccount($params) {
	$sslexists = get_query_val( "tblsslorders", "COUNT(*)", array( "serviceid" => $params['serviceid'], "status" => "Awaiting Configuration" ) );

	if (!$sslexists) {
		return "SSL Either not Provisioned or Not Awaiting Configuration so unable to cancel";
	}

	update_query( "tblsslorders", array( "status" => "Cancelled" ), array( "serviceid" => $params['serviceid'] ) );
	$postfields = array();
	$postfields['auth-userid'] = $params['configoption1'];
	$postfields['auth-password'] = $params['configoption2'];
	$postfields['order-id'] = $params['remoteid'] = get_query_val( "tblsslorders", "remoteid", array( "serviceid" => $params['serviceid'] ) );
	resellerclubssl_SendCommand( "cancel", "digitalcertificate", $postfields, $params, "POST" );
	resellerclubssl_SendCommand( "delete", "digitalcertificate", $postfields, $params, "POST" );
	return "success";
}


function resellerclubssl_AdminCustomButtonArray() {
	$buttonarray = array( "Resend Configuration Email" => "resend", "Prepare for Reissue" => "Reissue", "Renew" => "Renew" );
	return $buttonarray;
}


function resellerclubssl_resend($params) {
	$id = get_query_val( "tblsslorders", "id", array( "serviceid" => $params['serviceid'] ) );

	if (!$id) {
		return "No SSL Order exists for this product";
	}

	global $CONFIG;

	$sslconfigurationlink = $CONFIG['SystemURL'] . "/configuressl.php?cert=" . md5( $id );
	$sslconfigurationlink = "<a href=\"" . $sslconfigurationlink . "\">" . $sslconfigurationlink . "</a>";
	sendMessage( "SSL Certificate Configuration Required", $params['serviceid'], array( "ssl_configuration_link" => $sslconfigurationlink ) );
	return "success";
}


function resellerclubssl_ClientArea($params) {
	global $_LANG;

	$data = get_query_vals( "tblsslorders", "", array( "serviceid" => $params['serviceid'] ) );
	$id = $data['id'];
	$orderid = $data['orderid'];
	$serviceid = $data['serviceid'];
	$remoteid = $data['remoteid'];
	$module = $data['module'];
	$certtype = $data['certtype'];
	$domain = $data['domain'];
	$provisiondate = $data['provisiondate'];
	$completiondate = $data['completiondate'];
	$expirydate = $data['expirydate'];
	$status = $data['status'];

	if ($id) {
		if (!$provisiondate) {
			$provisiondate = get_query_val( "tblhosting", "regdate", array( "id" => $params['serviceid'] ) );
		}

		$provisiondate = ($provisiondate == "0000-00-00" ? "-" : fromMySQLDate( $provisiondate ));

		if ($status == "Awaiting Configuration") {
			$status .= " - <a href=\"configuressl.php?cert=" . md5( $id ) . "\">" . $_LANG['sslconfigurenow'] . "</a>";
		}

		$output = "<div align=\"left\">
<table width=\"100%\">
<tr><td width=\"150\" class=\"fieldlabel\">" . $_LANG['sslprovisioningdate'] . ":</td><td>" . $provisiondate . "</td></tr>
<tr><td class=\"fieldlabel\">" . $_LANG['sslstatus'] . ":</td><td>" . $status . "</td></tr>
</table>
</div>";
		return $output;
	}

}


function resellerclubssl_AdminServicesTabFields($params) {
	$data = get_query_vals( "tblsslorders", "", array( "serviceid" => $params['serviceid'] ) );
	$id = $data['id'];
	$orderid = $data['orderid'];
	$serviceid = $data['serviceid'];
	$remoteid = $data['remoteid'];
	$module = $data['module'];
	$certtype = $data['certtype'];
	$domain = $data['domain'];
	$provisiondate = $data['provisiondate'];
	$completiondate = $data['completiondate'];
	$expirydate = $data['expirydate'];
	$status = $data['status'];

	if (!$id) {
		$remoteid = "-";
		$status = "Not Yet Provisioned";
	}

	$fieldsarray = array( "ResellerClub Order ID" => $remoteid, "SSL Configuration Status" => $status );
	return $fieldsarray;
}


function resellerclubssl_SSLStepOne($params) {
	if ($params['remoteid']) {
		$certdata = resellerclubssl_getCertDetails( $params );

		if (is_array( $certdata )) {
			if ($certdata['certificateEnrolled'] == "true") {
				update_query( "tblsslorders", array( "completiondate" => "now()", "status" => "Completed" ), array( "serviceid" => $params['serviceid'], "status" => array( "sqltype" => "NEQ", "value" => "Completed" ) ) );
				return null;
			}

			update_query( "tblsslorders", array( "completiondate" => "", "status" => "Awaiting Configuration" ), array( "serviceid" => $params['serviceid'], "status" => array( "sqltype" => "Completed" ) ) );
		}
	}

}


function resellerclubssl_SSLStepTwo($params) {
	$domain = strtolower( trim( $params['domain'] ) );

	if (substr( $domain, 0, 7 ) == "http://") {
		$domain = substr( $domain, 7 );
	}


	if (substr( $domain, 0, 4 ) == "www.") {
		$domain = substr( $domain, 4 );
	}

	$approveremails = array( "admin", "administrator", "hostmaster", "root", "postmaster" );
	foreach ($approveremails as $email) {
		$approveremailsarray[] = $email . "@" . $domain;
	}

	$values['approveremails'] = $approveremailsarray;
	return $values;
}


function resellerclubssl_SSLStepThree($params) {
	$values = array();
	require ROOTDIR . "/includes/countriescallingcodes.php";
	$postfields = array();
	$postfields['auth-userid'] = $params['configoption1'];
	$postfields['auth-password'] = $params['configoption2'];
	$postfields['order-id'] = $params['remoteid'];
	$certdata = resellerclubssl_getCertDetails( $params );

	if ($certdata['isenrolled'] == "false") {
		$postfields['attr-name1'] = "org_name";
		$postfields['attr-name2'] = "org_street1";
		$postfields['attr-name3'] = "org_city";
		$postfields['attr-name4'] = "org_state";
		$postfields['attr-name5'] = "org_postalcode";
		$postfields['attr-name6'] = "org_country";
		$postfields['attr-name7'] = "org_phone";
		$postfields['attr-name8'] = "org_fax";
		$postfields['attr-name9'] = "admin_firstname";
		$postfields['attr-name10'] = "admin_lastname";
		$postfields['attr-name11'] = "admin_jobtitle";
		$postfields['attr-name12'] = "admin_telephone";
		$postfields['attr-name13'] = "admin_email";
		$postfields['attr-name14'] = "tech_firstname";
		$postfields['attr-name15'] = "tech_lastname";
		$postfields['attr-name16'] = "tech_jobtitle";
		$postfields['attr-name17'] = "tech_telephone";
		$postfields['attr-name18'] = "tech_email";
		$postfields['attr-name19'] = "approveremail";
		$postfields['attr-name20'] = "software";
		$postfields['attr-name21'] = "csrString";
		$postfields['attr-value1'] = ($params['configdata']['company'] ? $params['configdata']['company'] : "N/A");
		$postfields['attr-value2'] = $params['configdata']['address1'];
		$postfields['attr-value3'] = $params['configdata']['city'];
		$postfields['attr-value4'] = $params['configdata']['state'];
		$postfields['attr-value5'] = $params['configdata']['postcode'];
		$postfields['attr-value6'] = $params['configdata']['country'];
		$postfields['attr-value7'] = $countrycallingcodes[$params['configdata']['country']] . preg_replace( "/[^0-9]/", "", $params['configdata']['phonenumber'] );
		$postfields['attr-value8'] = $countrycallingcodes[$params['configdata']['country']] . preg_replace( "/[^0-9]/", "", $params['configdata']['phonenumber'] );
		$postfields['attr-value9'] = $postfields['attr-value14'] = $params['configdata']['firstname'];
		$postfields['attr-value10'] = $postfields['attr-value15'] = $params['configdata']['lastname'];
		$postfields['attr-value11'] = "Administrator";
		$postfields['attr-value12'] = $countrycallingcodes[$params['configdata']['country']] . preg_replace( "/[^0-9]/", "", $params['configdata']['phonenumber'] );
		$postfields['attr-value13'] = $params['configdata']['email'];
		$postfields['attr-value16'] = "IT Admin";
		$postfields['attr-value17'] = $countrycallingcodes[$params['configdata']['country']] . preg_replace( "/[^0-9]/", "", $params['configdata']['phonenumber'] );
		$postfields['attr-value18'] = $params['configdata']['email'];
		$postfields['attr-value19'] = $params['approveremail'];
		$postfields['attr-value20'] = (( $params['servertype'] == "1013" || $params['servertype'] == "1014" ) ? "IIS" : "Other");
		$postfields['attr-value21'] = $params['csr'];
		$result = resellerclubssl_SendCommand( "enroll-for-thawtecertificate", "digitalcertificate", $postfields, $params, "POST" );
	}
	else {
		$postfields['csr-string'] = $params['csr'];
		$postfields['csr-software'] = (( $params['servertype'] == "1013" || $params['servertype'] == "1014" ) ? "IIS" : "Other");
		$postfields['approver-email'] = $params['approveremail'];
		$result = resellerclubssl_SendCommand( "reissue", "digitalcertificate", $postfields, $params, "POST" );
	}


	if ($result['response']['status'] == "ERROR") {
		return array( "error" => $result['response']['message'] );
	}


	if ($result['hashtable']['entry'][0]['string'][1] != "success") {
		return array( "error" => $result['hashtable']['entry'][1]['string'][1] );
	}

	return array( "provisioned" => true );
}


function resellerclubssl_Reissue($params) {
	$id = get_query_val( "tblsslorders", "id", array( "serviceid" => $params['serviceid'] ) );

	if (!$id) {
		return "No SSL Order exists for this product";
	}

	update_query( "tblsslorders", array( "status" => "Awaiting Configuration" ), array( "serviceid" => $params['serviceid'] ) );
	global $CONFIG;

	$sslconfigurationlink = $CONFIG['SystemURL'] . "/configuressl.php?cert=" . md5( $id );
	$sslconfigurationlink = "<a href=\"" . $sslconfigurationlink . "\">" . $sslconfigurationlink . "</a>";
	sendMessage( "SSL Certificate Configuration Required", $params['serviceid'], array( "ssl_configuration_link" => $sslconfigurationlink ) );
	return "success";
}


function resellerclubssl_Renew($params) {
	$params['configdata'] = unserialize( get_query_val( "tblsslorders", "configdata", array( "serviceid" => $params['serviceid'] ) ) );
	$postfields = array();
	$postfields['auth-userid'] = $params['configoption1'];
	$postfields['auth-password'] = $params['configoption2'];
	$postfields['order-id'] = $params['remoteid'] = get_query_val( "tblsslorders", "remoteid", array( "serviceid" => $params['serviceid'] ) );
	$certdata = resellerclubssl_getCertDetails( $params, "ExecutionInfoParams" );
	$postfields['years'] = ($params['configoptions']['Years'] ? $params['configoptions']['Years'] : 1);
	$postfields['additional-licenses'] = 0;
	$postfields['invoice-option'] = "NoInvoice";
	$postfields['exp-date'] = $certdata['ExpirationTimeStamp'];
	$postfields['attr-name1'] = "org_name";
	$postfields['attr-name2'] = "org_street1";
	$postfields['attr-name3'] = "org_city";
	$postfields['attr-name4'] = "org_state";
	$postfields['attr-name5'] = "org_postalcode";
	$postfields['attr-name6'] = "org_country";
	$postfields['attr-name7'] = "org_phone";
	$postfields['attr-name8'] = "org_fax";
	$postfields['attr-name9'] = "admin_firstname";
	$postfields['attr-name10'] = "admin_lastname";
	$postfields['attr-name11'] = "admin_jobtitle";
	$postfields['attr-name12'] = "admin_telephone";
	$postfields['attr-name13'] = "admin_email";
	$postfields['attr-name14'] = "tech_firstname";
	$postfields['attr-name15'] = "tech_lastname";
	$postfields['attr-name16'] = "tech_jobtitle";
	$postfields['attr-name17'] = "tech_telephone";
	$postfields['attr-name18'] = "tech_email";
	$postfields['attr-name19'] = "approveremail";
	$postfields['attr-name20'] = "software";
	$postfields['attr-name21'] = "csrString";
	$postfields['attr-value1'] = ($params['configdata']['company'] ? $params['configdata']['company'] : "N/A");
	$postfields['attr-value2'] = $params['configdata']['address1'];
	$postfields['attr-value3'] = $params['configdata']['city'];
	$postfields['attr-value4'] = $params['configdata']['state'];
	$postfields['attr-value5'] = $params['configdata']['postcode'];
	$postfields['attr-value6'] = $params['configdata']['country'];
	$postfields['attr-value7'] = $countrycallingcodes[$params['configdata']['country']] . preg_replace( "/[^0-9]/", "", $params['configdata']['phonenumber'] );
	$postfields['attr-value8'] = $countrycallingcodes[$params['configdata']['country']] . preg_replace( "/[^0-9]/", "", $params['configdata']['phonenumber'] );
	$postfields['attr-value9'] = $postfields['attr-value14'] = $params['configdata']['firstname'];
	$postfields['attr-value10'] = $postfields['attr-value15'] = $params['configdata']['lastname'];
	$postfields['attr-value11'] = "Administrator";
	$postfields['attr-value12'] = $countrycallingcodes[$params['configdata']['country']] . preg_replace( "/[^0-9]/", "", $params['configdata']['phonenumber'] );
	$postfields['attr-value13'] = $params['configdata']['email'];
	$postfields['attr-value16'] = "IT Admin";
	$postfields['attr-value17'] = $countrycallingcodes[$params['configdata']['country']] . preg_replace( "/[^0-9]/", "", $params['configdata']['phonenumber'] );
	$postfields['attr-value18'] = $params['configdata']['email'];
	$postfields['attr-value19'] = $params['configdata']['approveremail'];
	$postfields['attr-value20'] = (( $params['configdata']['servertype'] == "1013" || $params['configdata']['servertype'] == "1014" ) ? "IIS" : "Other");
	$postfields['attr-value21'] = $params['configdata']['csr'];
	$error = "";
	$result = resellerclubssl_SendCommand( "renew", "digitalcertificate", $postfields, $params, "POST" );

	if ($result['response']['status'] == "ERROR") {
		$error .= $result['response']['message'];
	}


	if ($result['hashtable']['entry'][0]['string'][1] != "success") {
		$error .= $result['hashtable']['entry'][1]['string'][1];
	}


	if (!$error) {
		$error = "success";
	}

	return $error;
}


function resellerclubssl_SendCommand($command, $type, $postfields, $params, $method) {
	if ($params['configoption4']) {
		$url = "https://test.httpapi.com/api/" . $type . "/" . $command . ".xml";
	}
	else {
		$url = "https://httpapi.com/api/" . $type . "/" . $command . ".xml";
	}

	$ch = curl_init();

	if ($method == "GET") {
		$url .= "?";
		foreach ($postfields as $field => $data) {
			$url .= "" . $field . "=" . rawurlencode( $data ) . "&";
		}

		$url = substr( $url, 0, 0 - 1 );
	}
	else {
		$query_string = "";
		foreach ($postfields as $field => $data) {

			if ($field != "ns") {
				$data = rawurlencode( $data );
			}

			$query_string .= "" . $field . "=" . $data . "&";
		}

		$postfield = substr( $postfield, 0, 0 - 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
	}

	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$data = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$ip = resellerclubssl_GetIP();
		$ip2 = (isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR']);
		$result['response']['status'] = "ERROR";
		$result['response']['message'] = "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch ) . ( " (IP: " . $ip . " & " . $ip2 . ")" );
	}
	else {
		$result = resellerclubssl_xml2array( $data );
	}

	curl_close( $ch );
	logModuleCall( "logicboxes", $command, $postfields, $data, $result, array( $params['configoption1'], $params['configoption2'] ) );

	if ($result['response']['message'] == "An unexpected error has occurred") {
		$result['response']['message'] = "Login Failure or Unexpected Error";
	}

	return $result;
}


function resellerclubssl_getCertDetails($params, $option = "All") {
	$postfields = array();
	$postfields['auth-userid'] = $params['configoption1'];
	$postfields['auth-password'] = $params['configoption2'];
	$postfields['order-id'] = $params['remoteid'];
	$postfields['option'] = $option;
	$result = resellerclubssl_SendCommand( "details", "digitalcertificate", $postfields, $params, "GET" );

	if ($result['response']['status'] == "ERROR") {
		return $result['response']['message'];
	}


	if ($option != "All") {
		$result = $result['hashtable']['entry'][0];
	}

	foreach ($result['hashtable']['entry'] as $entry => $value) {
		$certdata[$value['string'][0]] = $value['string'][1];
	}

	return $certdata;
}


function resellerclubssl_getOrderID($postfields, $params) {
	$domain = $postfields['domain-name'];

	if (isset( $GLOBALS['logicboxesorderids'][$domain] )) {
		$result = $GLOBALS['logicboxesorderids'][$domain];
	}
	else {
		$result = resellerclubssl_SendCommand( "orderid", "digitalcertificate", $postfields, $params, "GET" );
		$GLOBALS['logicboxesorderids'][$domain] = $result;
	}


	if ($result['response']['status'] == "ERROR") {
		return $result['response']['message'];
	}

	$orderid = $result['int'];

	if (!$orderid) {
		return "Unable to obtain Order-ID";
	}

	return $orderid;
}


function resellerclubssl_genLBRandomPW() {
	$letters = "ABCDEFGHIJKLMNPQRSTUVYXYZabcdefghijklmnopqrstuvwxyz";
	$numbers = "0123456789";
	$letterscount = strlen( $letters ) - 1;
	$numberscount = strlen( $numbers ) - 1;
	$password = "";
	$i = 0;

	while ($i < 5) {
		$password .= $letters[rand( 0, $letterscount )] . $numbers[rand( 0, $numberscount )];
		++$i;
	}

	return $password;
}


function resellerclubssl_xml2array($contents, $get_attributes = 1, $priority = "tag") {
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
				$result['value'] = $value;
			}
		}


		if (isset( $attributes ) && $get_attributes) {
			foreach ($attributes as $attr => $val) {

				if ($priority == "tag") {
					$attributes_data[$attr] = $val;
					continue;
				}

				$result['attr'][$attr] = $val;
			}
		}


		if ($type == "open") {
			$parent[$level - 1] = &$current;

			if (!is_array( $current ) || !in_array( $tag, array_keys( $current ) )) {
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
					$current[$tag]['0_attr'] = $current[$tag . "_attr"];
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

				if ($priority == "tag" && $attributes_data) {
					$current[$tag . "_attr"] = $attributes_data;
					continue;
				}

				continue;
			}


			if (isset( $current[$tag][0] ) && is_array( $current[$tag] )) {
				$current[$tag][$repeated_tag_index[$tag . "_" . $level]] = $result;

				if (( $priority == "tag" && $get_attributes ) && $attributes_data) {
					$current[$tag][$repeated_tag_index[$tag . "_" . $level] . "_attr"] = $attributes_data;
				}

				++$repeated_tag_index[$tag . "_" . $level];
				continue;
			}

			$current[$tag] = array( $current[$tag], $result );
			$repeated_tag_index[$tag . "_" . $level] = 1;

			if ($priority == "tag" && $get_attributes) {
				if (isset( $current[$tag . "_attr"] )) {
					$current[$tag]['0_attr'] = $current[$tag . "_attr"];
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


function resellerclubssl_GetIP() {
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, "http://automation.whatismyip.com/n09230945.asp" );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$contents = curl_exec( $ch );
	curl_close( $ch );
	return $contents;
}


function resellerclubssl_Language($language) {
	$language = strtolower( $language );
	switch ($language) {
	case "dutch":
			$language = "nl";
			break;

	case "german":
			$language = "de";
			break;

	case "italian": {
			$language = "it";
			break;
		}

	case "portuguese-br":
			$language = "pt";
			break;

	case "portuguese-pt":
			$language = "pt";
			break;

	case "spanish":
			$language = "es";
			break;

	case "turkish":
			$language = "tr";
			break;

	case "english":

	default:
		$language = "en";
	}

	
	if (strlen( $language ) == 2) {
		return $language;
	}

	return "en";
}


?>