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

function enomssl_ConfigOptions() {
	$result = select_query( "tblemailtemplates", "COUNT(*)", array( "name" => "SSL Certificate Configuration Required" ) );
	$data = mysql_fetch_array( $result );

	if (!$data[0]) {
		full_query( "INSERT INTO `tblemailtemplates` (`type` ,`name` ,`subject` ,`message` ,`fromname` ,`fromemail` ,`disabled` ,`custom` ,`language` ,`copyto` ,`plaintext` )VALUES ('product', 'SSL Certificate Configuration Required', 'SSL Certificate Configuration Required', '<p>Dear {$client_name},</p><p>Thank you for your order for an SSL Certificate. Before you can use your certificate, it requires configuration which can be done at the URL below.</p><p>{$ssl_configuration_link}</p><p>Instructions are provided throughout the process but if you experience any problems or have any questions, please open a ticket for assistance.</p><p>{$signature}</p>', '', '', '', '', '', '', '0')" );
	}

	$result = select_query( "tblproducts", "configoption1,configoption2,configoption5", array( "servertype" => "enomssl", "configoption1" => array( "sqltype" => "NEQ", "value" => "" ) ) );
	$data = mysql_fetch_assoc( $result );
	$enomusername = $data['configoption1'];
	$enompassword = $data['configoption2'];
	$testmode = $data['configoption5'];

	if ($enomusername && $enompassword) {
		$postfields = array();
		$postfields['uid'] = $enomusername;
		$postfields['pw'] = $enompassword;
		$postfields['command'] = "GetCerts";
		$postfields['ResponseType'] = "XML";
		$result = enomssl_call( $postfields, $testmode );
		$certtypelist = "";
		foreach ($result['INTERFACE-RESPONSE']['GETCERTS']['CERTS'] as $cert => $details) {
			$certcode = $details['PRODCODE'];

			if ($certcode) {
				$certcode = str_replace( "-", " ", $certcode );
				$certcode = titleCase( $certcode );
				$certtypelist .= $certcode . ",";
				continue;
			}
		}

		$certtypelist = substr( $certtypelist, 0, 0 - 1 );

		if (!$certtypelist) {
			$certtypelist = "Certificate-Comodo-Essential,Certificate-Comodo-Instant,Certificate-Comodo-Premium-Wildcard,Certificate-Comodo-EV,Certificate-Comodo-EV-SGC,Certificate-GeoTrust-QuickSSL,Certificate-GeoTrust-QuickSSL-Premium,Certificate-GeoTrust-TrueBizID,Certificate-GeoTrust-TrueBizID-Wildcard,Certificate-GeoTrust-TrueBizID-EV,Certificate-RapidSSL-RapidSSL,Certificate-SBS-Instant,Certificate-SBS-Secure,Certificate-SBS-Secure-Plus,Certificate-SBS-EV,Certificate-SBS-SGC-EV,Certificate-VeriSign-Secure-Site,Certificate-VeriSign-Secure-Site-Pro,Certificate-VeriSign-Secure-Site-EV,Certificate-VeriSign-Secure-Site-Pro-EV";
		}
	}
	else {
		$certtypelist = "Please Enter your Username and Password";
	}

	$configarray = array( "Username" => array( "Type" => "text", "Size" => "25" ), "Password" => array( "Type" => "password", "Size" => "25" ), "Certificate Type" => array( "Type" => "dropdown", "Options" => $certtypelist ), "Years" => array( "Type" => "dropdown", "Options" => "1,2,3,4,5,6,7,8,9,10" ), "Test Mode" => array( "Type" => "yesno" ) );
	return $configarray;
}


function enomssl_CreateAccount($params) {
	$result = select_query( "tblsslorders", "COUNT(*)", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );

	if ($data[0]) {
		return "An SSL Order already exists for this order";
	}

	updateService( array( "username" => "", "password" => "" ) );
	$certtype = ($params['configoptions']["Certificate Type"] ? $params['configoptions']["Certificate Type"] : $params['configoption3']);
	$certyears = ($params['configoptions']['Years'] ? $params['configoptions']['Years'] : $params['configoption4']);
	$certtype = str_replace( " ", "-", strtolower( $certtype ) );
	$postfields = array();
	$postfields['uid'] = $params['configoption1'];
	$postfields['pw'] = $params['configoption2'];
	$postfields['ProductType'] = $certtype;
	$postfields['Quantity'] = $certyears;
	$postfields['ClearItems'] = "yes";
	$postfields['command'] = "AddToCart";
	$postfields['ResponseType'] = "XML";
	$result = enomssl_call( $postfields, $params['configoption5'] );

	if (!is_array( $result ) && substr( $result, 0, 4 ) == "CURL") {
		return $result;
	}

	$error = $result['INTERFACE-RESPONSE']['ERRORS']['ERR1'];

	if ($error) {
		return $error;
	}

	$postfields = array();
	$postfields['uid'] = $params['configoption1'];
	$postfields['pw'] = $params['configoption2'];
	$postfields['command'] = "InsertNewOrder";
	$postfields['ResponseType'] = "XML";
	$result = enomssl_call( $postfields, $params['configoption5'] );
	$error = $result['INTERFACE-RESPONSE']['ERRORS']['ERR1'];

	if ($error) {
		return $error;
	}

	$orderid = $result['INTERFACE-RESPONSE']['ORDERID'];
	$sslorderid = insert_query( "tblsslorders", array( "userid" => $params['clientsdetails']['userid'], "serviceid" => $params['serviceid'], "remoteid" => $orderid, "module" => "enomssl", "certtype" => $certtype, "status" => "Awaiting Configuration" ) );
	global $CONFIG;

	$sslconfigurationlink = $CONFIG['SystemURL'] . "/configuressl.php?cert=" . md5( $sslorderid );
	$sslconfigurationlink = "<a href=\"" . $sslconfigurationlink . "\">" . $sslconfigurationlink . "</a>";
	sendMessage( "SSL Certificate Configuration Required", $params['serviceid'], array( "ssl_configuration_link" => $sslconfigurationlink ) );
	return "success";
}


function enomssl_TerminateAccount($params) {
	$result = select_query( "tblsslorders", "COUNT(*)", array( "serviceid" => $params['serviceid'], "status" => "Awaiting Configuration" ) );
	$data = mysql_fetch_array( $result );

	if (!$data[0]) {
		return "SSL Either not Provisioned or Not Awaiting Configuration so unable to cancel";
	}

	update_query( "tblsslorders", array( "status" => "Cancelled" ), array( "serviceid" => $params['serviceid'] ) );
	return "success";
}


function enomssl_AdminCustomButtonArray() {
	$buttonarray = array( "Resend Configuration Email" => "resend" );
	return $buttonarray;
}


function enomssl_resend($params) {
	$result = select_query( "tblsslorders", "id", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	$id = $data['id'];

	if (!$id) {
		return "No SSL Order exists for this product";
	}

	global $CONFIG;

	$sslconfigurationlink = $CONFIG['SystemURL'] . "/configuressl.php?cert=" . md5( $id );
	$sslconfigurationlink = "<a href=\"" . $sslconfigurationlink . "\">" . $sslconfigurationlink . "</a>";
	sendMessage( "SSL Certificate Configuration Required", $params['serviceid'], array( "ssl_configuration_link" => $sslconfigurationlink ) );
	return "success";
}


function enomssl_ClientArea($params) {
	global $_LANG;

	$result = select_query( "tblsslorders", "", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	$id = $data['id'];
	$orderid = $data['orderid'];
	$serviceid = $data['serviceid'];
	$remoteid = $data['remoteid'];
	$module = $data['module'];
	$certtype = $data['certtype'];
	$domain = $data['domain'];
	$provisiondate = $data['provisiondate'];
	$completiondate = $data['completiondate'];
	$status = $data['status'];

	if ($id) {
		if (!$provisiondate) {
			$result = select_query( "tblhosting", "regdate", array( "id" => $params['serviceid'] ) );
			$data = mysql_fetch_array( $result );
			$provisiondate = $data['regdate'];
		}

		$provisiondate = fromMySQLDate( $provisiondate );
		$status .= " - <a href=\"configuressl.php?cert=" . md5( $id ) . "\">" . $_LANG['sslconfigurenow'] . "</a>";
		$output = "<div align=\"left\">
<table width=\"100%\">
<tr><td width=\"150\" class=\"fieldlabel\">" . $_LANG['sslprovisioningdate'] . ":</td><td>" . $provisiondate . "</td></tr>
<tr><td class=\"fieldlabel\">" . $_LANG['sslstatus'] . ":</td><td>" . $status . "</td></tr>
</table>
</div>";
		return $output;
	}

}


function enomssl_AdminServicesTabFields($params) {
	$result = select_query( "tblsslorders", "", array( "serviceid" => $params['serviceid'] ) );
	$data = mysql_fetch_array( $result );
	$id = $data['id'];
	$orderid = $data['orderid'];
	$serviceid = $data['serviceid'];
	$remoteid = $data['remoteid'];
	$module = $data['module'];
	$certtype = $data['certtype'];
	$domain = $data['domain'];
	$provisiondate = $data['provisiondate'];
	$completiondate = $data['completiondate'];
	$status = $data['status'];

	if (!$id) {
		$remoteid = "-";
		$status = "Not Yet Provisioned";
	}

	$fieldsarray = array( "Enom Order ID" => $remoteid, "SSL Configuration Status" => $status );
	return $fieldsarray;
}


function enomssl_SSLStepOne($params) {
	$orderid = $params['remoteid'];
	$values = array();

	if (!$_SESSION['enomsslcert'][$orderid]['id']) {
		$postfields = array();
		$postfields['uid'] = $params['configoption1'];
		$postfields['pw'] = $params['configoption2'];
		$postfields['command'] = "CertGetCerts";
		$postfields['ResponseType'] = "XML";
		$result = enomssl_call( $postfields, $params['configoption5'] );
		$values['error'] = $result['INTERFACE-RESPONSE']['ERRORS']['ERR1'];

		if ($values['error']) {
			return $values;
		}

		$cert_allowconfig = false;
		foreach ($result['INTERFACE-RESPONSE']['CERTGETCERTS']['CERTS'] as $certificate) {
			$temp_cert_id = $certificate['CERTID'];
			$temp_cert_name = $certificate['PRODDESC'];
			$temp_cert_status = $certificate['CERTSTATUS'];
			$temp_cert_orderid = $certificate['ORDERID'];
			$temp_cert_orderdate = $certificate['ORDERDATE'];
			$temp_cert_validityperiod = $certificate['VALIDITYPERIOD'];

			if ($temp_cert_orderid == $orderid) {
				$cert_id = $temp_cert_id;
				$cert_name = $temp_cert_name;
				$cert_orderid = $temp_cert_orderid;
				$cert_orderdate = $temp_cert_orderdate;
				$cert_validityperiod = $temp_cert_validityperiod;

				if ($temp_cert_status == "Awaiting Configuration" || $temp_cert_status == "Rejected by Customer") {
					$cert_allowconfig = true;
					continue;
				}

				continue;
			}
		}


		if (!$cert_allowconfig) {
			update_query( "tblsslorders", array( "completiondate" => "now()", "status" => "Completed" ), array( "serviceid" => $params['serviceid'], "status" => array( "sqltype" => "NEQ", "value" => "Completed" ) ) );
		}
		else {
			update_query( "tblsslorders", array( "completiondate" => "", "status" => "Awaiting Configuration" ), array( "serviceid" => $params['serviceid'] ) );
		}

		$_SESSION['enomsslcert'][$orderid]['id'] = $cert_id;
	}
	else {
		$cert_id = $_SESSION['enomsslcert'][$orderid]['id'];
	}

	$postfields = array();
	$postfields['uid'] = $params['configoption1'];
	$postfields['pw'] = $params['configoption2'];
	$postfields['CertID'] = $cert_id;
	$postfields['command'] = "CertGetCertDetail";
	$postfields['ResponseType'] = "XML";
	$result = enomssl_call( $postfields, $params['configoption5'] );
	$values['error'] = $result['INTERFACE-RESPONSE']['ERRORS']['ERR1'];

	if ($values['error']) {
		return $values;
	}

	$values['displaydata']['Domain'] = $result['INTERFACE-RESPONSE']['CERTGETCERTDETAIL']['DOMAINNAME'];
	$values['displaydata']["Validity Period"] = $result['INTERFACE-RESPONSE']['CERTGETCERTDETAIL']['VALIDITYPERIOD'] . " Months";
	$values['displaydata']["Expiration Date"] = $result['INTERFACE-RESPONSE']['CERTGETCERTDETAIL']['EXPIRATIONDATE'];
	return $values;
}


function enomssl_SSLStepTwo($params) {
	$orderid = $params['remoteid'];
	$cert_id = $_SESSION['enomsslcert'][$orderid]['id'];
	$webservertype = $params['servertype'];
	$csr = $params['csr'];
	$firstname = $params['firstname'];
	$lastname = $params['lastname'];
	$organisationname = $params['orgname'];
	$jobtitle = $params['jobtitle'];
	$emailaddress = $params['email'];
	$address1 = $params['address1'];
	$address2 = $params['address2'];
	$city = $params['city'];
	$state = $params['state'];
	$postcode = $params['postcode'];
	$country = $params['country'];
	$phonenumber = $params['phonenumber'];
	$faxnumber = $params['faxnumber'];
	$values = array();
	$postfields = array();
	$postfields['uid'] = $params['configoption1'];
	$postfields['pw'] = $params['configoption2'];
	$postfields['CertID'] = $cert_id;
	$postfields['WebServerType'] = $webservertype;
	$postfields['CSR'] = $csr;
	$contacttypes = array( "Admin", "Tech", "Billing" );
	foreach ($contacttypes as $contacttype) {
		$postfields[$contacttype . "FName"] = $firstname;
		$postfields[$contacttype . "LName"] = $lastname;
		$postfields[$contacttype . "OrgName"] = $organisationname;
		$postfields[$contacttype . "JobTitle"] = $jobtitle;
		$postfields[$contacttype . "Address1"] = $address1;
		$postfields[$contacttype . "Address2"] = $address2;
		$postfields[$contacttype . "City"] = $city;
		$postfields[$contacttype . "State"] = $state;
		$postfields[$contacttype . "PostalCode"] = $postcode;
		$postfields[$contacttype . "Country"] = $country;
		$postfields[$contacttype . "Phone"] = $phonenumber;
		$postfields[$contacttype . "Fax"] = $faxnumber;
		$postfields[$contacttype . "EmailAddress"] = $emailaddress;
	}

	$postfields['command'] = "CertConfigureCert";
	$postfields['ResponseType'] = "XML";
	$result = enomssl_call( $postfields, $params['configoption5'] );
	$values['error'] = $result['INTERFACE-RESPONSE']['ERRORS']['ERR1'];

	if ($values['error']) {
		return $values;
	}

	$approveremailsarray = array();
	foreach ($result['INTERFACE-RESPONSE']['CERTCONFIGURECERT'] as $k => $v) {

		if (substr( $k, 0, 8 ) == "APPROVER") {
			$approver = trim( $v['APPROVEREMAIL'] );

			if ($approver) {
				$approveremailsarray[] = $approver;
				continue;
			}

			continue;
		}
	}

	$values['approveremails'] = $approveremailsarray;
	$postfields = array();
	$postfields['uid'] = $params['configoption1'];
	$postfields['pw'] = $params['configoption2'];
	$postfields['CertID'] = $cert_id;
	$postfields['command'] = "CertGetCertDetail";
	$postfields['ResponseType'] = "XML";
	$result = enomssl_call( $postfields, $params['configoption5'] );
	$values['error'] = $result['INTERFACE-RESPONSE']['ERRORS']['ERR1'];

	if ($values['error']) {
		return $values;
	}

	$values['displaydata']['Domain'] = $result['INTERFACE-RESPONSE']['CERTGETCERTDETAIL']['DOMAINNAME'];
	$values['displaydata']["Validity Period"] = $result['INTERFACE-RESPONSE']['CERTGETCERTDETAIL']['VALIDITYPERIOD'] . " Months";
	$values['displaydata']["Expiration Date"] = $result['INTERFACE-RESPONSE']['CERTGETCERTDETAIL']['EXPIRATIONDATE'];
	update_query( "tblhosting", array( "domain" => $values['displaydata']['Domain'] ), array( "id" => $params['serviceid'] ) );
	$postfields = array();
	$postfields['uid'] = $params['configoption1'];
	$postfields['pw'] = $params['configoption2'];
	$postfields['CertID'] = $cert_id;
	$postfields['CSR'] = $csr;
	$postfields['command'] = "CertParseCSR";
	$postfields['ResponseType'] = "XML";
	$result = enomssl_call( $postfields, $params['configoption5'] );
	$values['error'] = $result['INTERFACE-RESPONSE']['ERRORS']['ERR1'];

	if ($values['error']) {
		return $values;
	}

	$values['displaydata']['Organization'] = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['ORGANIZATION'];
	$values['displaydata']["Organization Unit"] = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['ORGANIZATIONUNIT'];
	$values['displaydata']['Email'] = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['EMAIL'];
	$values['displaydata']['Locality'] = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['LOCALITY'];
	$values['displaydata']['State'] = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['STATE'];
	$values['displaydata']['Country'] = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['COUNTRY'];
	return $values;
}


function enomssl_SSLStepThree($params) {
	$params['remoteid'];
	$cert_id = $_SESSION['enomsslcert'][$orderid]['id'];
	$webservertype = $params['servertype'];
	$csr = $params['csr'];
	$firstname = $params['firstname'];
	$lastname = $params['lastname'];
	$organisationname = $params['organisationname'];
	$jobtitle = $params['jobtitle'];
	$emailaddress = $params['email'];
	$address1 = $params['address1'];
	$address2 = $params['address2'];
	$city = $params['city'];
	$state = $params['state'];
	$postcode = $params['postcode'];
	$country = $params['country'];
	$phonenumber = $params['phonenumber'];
	$faxnumber = $params['faxnumber'];
	$approveremail = $params['approveremail'];
	$cert_id = $_SESSION['enomsslcert'][$orderid]['id'];
	unset( $_SESSION['enomsslcert'] );
	$postfields = array();
	$postfields['uid'] = $params['configoption1'];
	$postfields['pw'] = $params['configoption2'];
	$postfields['CertID'] = $cert_id;
	$postfields['CSR'] = $csr;
	$postfields['command'] = "CertParseCSR";
	$postfields['ResponseType'] = "XML";
	$result = enomssl_call( $postfields, $params['configoption5'] );
	$csr_organization = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['ORGANIZATION'];
	$csr_organizationunit = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['ORGANIZATIONUNIT'];
	$csr_email = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['EMAIL'];
	$csr_locality = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['LOCALITY'];
	$csr_state = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['STATE'];
	$csr_country = $result['INTERFACE-RESPONSE']['CERTPARSECSR']['COUNTRY'];
	$postfields = array();
	$postfields['uid'] = $params['configoption1'];
	$postfields['pw'] = $params['configoption2'];
	$postfields['ApproverEmail'] = $params['approveremail'];
	$postfields['CertID'] = $cert_id;
	$postfields['CSRAddress1'] = $address1;
	$postfields['CSRPostalCode'] = $postcode;

	if ($csr_organization) {
		$postfields['CSROrganization'] = $csr_organization;
	}


	if ($csr_organizationunit) {
		$postfields['CSROrganizationUnit'] = $csr_organizationunit;
	}


	if ($csr_locality) {
		$postfields['CSRLocality'] = $csr_locality;
	}


	if ($csr_state) {
		$postfields['CSRStateProvince'] = $csr_state;
	}


	if ($csr_country) {
		$postfields['CSRCountry'] = $csr_country;
	}

	$postfields['command'] = "CertPurchaseCert";
	$postfields['ResponseType'] = "XML";
	$result = $orderid = enomssl_call( $postfields, $params['configoption5'] );
	$values['error'] = $result['INTERFACE-RESPONSE']['ERRORS']['ERR1'];

	if ($values['error']) {
		return $values;
	}

	return $values;
}


function enomssl_call($fields, $testmode = "") {
	$query_string = "";
	foreach ($fields as $k => $v) {
		$query_string .= "" . $k . "=" . urlencode( $v ) . "&";
	}

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, "https://" . $url . "/interface.asp" );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
	$data = curl_exec( $ch );

	if (curl_error( $ch )) {
		return "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch );
	}

	curl_close( $ch );
	XMLtoARRAY( $data );
	$result = $url = ($testmode ? "resellertest.enom.com" : "reseller.enom.com");
	logModuleCall( "enomssl", $fields['command'], $fields, $result, "", array( $fields['uid'], $fields['pw'] ) );
	return $result;
}


?>