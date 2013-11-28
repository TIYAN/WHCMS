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

function globalsignssl_ConfigOptions() {
	$result = select_query( "tblemailtemplates", "COUNT(*)", array( "name" => "SSL Certificate Configuration Required" ) );
	$data = mysql_fetch_array( $result );

	if (!$data[0]) {
		full_query( "INSERT INTO `tblemailtemplates` (`type` ,`name` ,`subject` ,`message` ,`fromname` ,`fromemail` ,`disabled` ,`custom` ,`language` ,`copyto` ,`plaintext` )VALUES ('product', 'SSL Certificate Configuration Required', 'SSL Certificate Configuration Required', '<p>Dear {$client_name},</p><p>Thank you for your order for an SSL Certificate. Before you can use your certificate, it requires configuration which can be done at the URL below.</p><p>{$ssl_configuration_link}</p><p>Instructions are provided throughout the process but if you experience any problems or have any questions, please open a ticket for assistance.</p><p>{$signature}</p>', '', '', '', '', '', '', '0')" );
	}

	$configarray = array( "Username" => array( "Type" => "text", "Size" => "25" ), "Password" => array( "Type" => "password", "Size" => "25", "Description" => "Do not have a GlobalSign SSL account? Visit www.globalsign.com/partners/ssl-resell/ to get an account" ), "SSL Certificate Type" => array( "Type" => "dropdown", "Options" => "AlphaSSL,DomainSSL,OrganizationSSL,ExtendedSSL" ), "Base Option" => array( "Type" => "dropdown", "Options" => "Standard SSL,Wildcard SSL" ), "Validity Period" => array( "Type" => "dropdown", "Options" => "1,2,3,4,5", "Description" => "Years" ), "Test Mode" => array( "Type" => "yesno" ), "" => array( "Type" => "na", "Description" => "Don't have a GlobalSign SSL account? Visit <a href=\"http://www.globalsign.com/partners/whmcs/\" target=\"_blank\">www.globalsign.com/partners/whmcs/</a> to signup free." ) );
	return $configarray;
}


function globalsignssl_CreateAccount($params) {
	$result = select_query( "tblsslorders", "COUNT(*)", array( "serviceid" => $params["serviceid"] ) );
	$data = mysql_fetch_array( $result );

	if ($data[0]) {
		return "An SSL Order already exists for this order";
	}

	updateService( array( "username" => "", "password" => "" ) );
	$sslorderid = insert_query( "tblsslorders", array( "userid" => $params["clientsdetails"]["userid"], "serviceid" => $params["serviceid"], "remoteid" => "", "module" => "globalsignssl", "certtype" => $params["configoption3"], "status" => "Awaiting Configuration" ) );
	global $CONFIG;

	$sslconfigurationlink = $CONFIG["SystemURL"] . "/configuressl.php?cert=" . md5( $sslorderid );
	$sslconfigurationlink = "<a href=\"" . $sslconfigurationlink . "\">" . $sslconfigurationlink . "</a>";
	sendMessage( "SSL Certificate Configuration Required", $params["serviceid"], array( "ssl_configuration_link" => $sslconfigurationlink ) );
	return "success";
}


function globalsignssl_AdminCustomButtonArray() {
	$buttonarray = array( "Cancel" => "cancel", "Resend Configuration Email" => "resend", "Resend Approver Email" => "resendapprover" );
	return $buttonarray;
}


function globalsignssl_cancel($params) {
	select_query( "tblsslorders", "COUNT(*)", array( "serviceid" => $params["serviceid"], "status" => "Awaiting Configuration" ) );
	$data = $result = mysql_fetch_array( $result );

	if (!$data[0]) {
		return "No Incomplete SSL Order exists for this order";
	}

	update_query( "tblsslorders", array( "status" => "Cancelled" ), array( "serviceid" => $params["serviceid"] ) );
	return "success";
}


function globalsignssl_resend($params) {
	$result = select_query( "tblsslorders", "id", array( "serviceid" => $params["serviceid"] ) );
	$data = mysql_fetch_array( $result );
	$id = $data["id"];

	if (!$id) {
		return "No SSL Order exists for this product";
	}

	global $CONFIG;

	$sslconfigurationlink = $CONFIG["SystemURL"] . "/configuressl.php?cert=" . md5( $id );
	$sslconfigurationlink = "<a href=\"" . $sslconfigurationlink . "\">" . $sslconfigurationlink . "</a>";
	sendMessage( "SSL Certificate Configuration Required", $params["serviceid"], array( "ssl_configuration_link" => $sslconfigurationlink ) );
	return "success";
}


function globalsignssl_resendapprover($params) {
	$result = select_query( "tblsslorders", "remoteid", array( "serviceid" => $params["serviceid"] ) );
	$data = mysql_fetch_array( $result );
	$remoteid = $data["remoteid"];

	if (!$remoteid) {
		return "No SSL Order exists for this product";
	}

	$user = $params["configoption1"];
	$pass = $params["configoption2"];
	$prodcode = $params["configoption3"];
	$baseoption = $params["configoption4"];
	$validityperiod = $params["configoption5"];
	$testmode = $params["configoption6"];

	if ($testmode) {
		$wsdlorderurl = "http://testsystem.globalsign.com/wsdls/gasorder.wsdl";
		$wsdlqueryurl = "http://testsystem.globalsign.com/wsdls/gasquery.wsdl";
	}
	else {
		$wsdlorderurl = "https://system.globalsign.com/wsdls/gasorder.wsdl";
		$wsdlqueryurl = "https://system.globalsign.com/wsdls/gasquery.wsdl";
	}

	$request = array();
	$request["Request"]["OrderRequestHeader"]["AuthToken"]["UserName"] = $user;
	$request["Request"]["OrderRequestHeader"]["AuthToken"]["Password"] = $pass;
	$request["Request"]["OrderID"] = $remoteid;
	$request["Request"]["ResendEmailType"] = "APPROVEREMAIL";
	$client = new SoapClient( $wsdlorderurl );
	$result = $client->ResendEmail( $request );
	logModuleCall( "globalsignssl", "resendapprover", $request, (array)$result, "", array( $user, $pass ) );
	$errorcode = $result->Response->OrderResponseHeader->SuccessCode;

	if (0 <= $errorcode) {
		return "success";
	}

	return "Error Code: " . $result->Response->OrderResponseHeader->Errors->Error->ErrorCode . " - " . $result->Response->OrderResponseHeader->Errors->Error->ErrorMessage;
}


function globalsignssl_ClientArea($params) {
	global $_LANG;

	$result = select_query( "tblsslorders", "", array( "serviceid" => $params["serviceid"] ) );
	$data = mysql_fetch_array( $result );
	$id = $data["id"];
	$orderid = $data["orderid"];
	$serviceid = $data["serviceid"];
	$remoteid = $data["remoteid"];
	$module = $data["module"];
	$certtype = $data["certtype"];
	$domain = $data["domain"];
	$provisiondate = $data["provisiondate"];
	$completiondate = $data["completiondate"];
	$status = $data["status"];

	if ($id) {
		$provisiondate = ($provisiondate == "0000-00-00" ? "-" : fromMySQLDate( $provisiondate ));
		$status .= " - <a href=\"configuressl.php?cert=" . md5( $id ) . "\">" . $_LANG["sslconfigurenow"] . "</a>";
		$output = "<div align=\"left\">
<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" class=\"frame\"><tr><td>
<table width=\"100%\" border=\"0\" cellpadding=\"10\" cellspacing=\"2\">
<tr><td width=\"150\" class=\"fieldarea\">" . $_LANG["sslprovisioningdate"] . ":</td><td>" . $provisiondate . "</td></tr>
<tr><td class=\"fieldarea\">" . $_LANG["sslstatus"] . ":</td><td>" . $status . "</td></tr>
</table>
</td></tr></table>
</div>";
		return $output;
	}

}


function globalsignssl_SSLStepOne($params) {
	$user = $params["configoption1"];
	$pass = $params["configoption2"];
	$prodcode = $params["configoption3"];
	$baseoption = $params["configoption4"];
	$validityperiod = $params["configoption5"];
	$testmode = $params["configoption6"];
	$values = array();

	if ($prodcode == "OrganizationSSL") {
		$values["additionalfields"]["Organization Information"] = array( "orgname" => array( "FriendlyName" => "Organization Name", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgaddress" => array( "FriendlyName" => "Address 1", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgcity" => array( "FriendlyName" => "City", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgstate" => array( "FriendlyName" => "State", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgpostcode" => array( "FriendlyName" => "Postcode", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgcountry" => array( "FriendlyName" => "Country", "Type" => "country", "Required" => true ), "orgphone" => array( "FriendlyName" => "Phone Number", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ) );
	}
	else {
		if ($prodcode == "ExtendedSSL") {
			$values["additionalfields"]["Organization Information"] = array( "bizcatcode" => array( "FriendlyName" => "Business Category Code", "Type" => "dropdown", "Options" => "Private Organization,Government Entity,Business Entity" ), "bizname" => array( "FriendlyName" => "Business Name", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgaddress" => array( "FriendlyName" => "Address 1", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgcity" => array( "FriendlyName" => "City", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgstate" => array( "FriendlyName" => "State", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgpostcode" => array( "FriendlyName" => "Postcode", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgcountry" => array( "FriendlyName" => "Country", "Type" => "country", "Required" => true ), "orgphone" => array( "FriendlyName" => "Phone Number", "Type" => "text", "Size" => "30", "Description" => "", "Required" => true ), "orgregnum" => array( "FriendlyName" => "Incorporating Agency Reg Number", "Type" => "text", "Size" => "20", "Description" => "As supplied to you by Companies House, Secretary of State, etc...", "Required" => true ) );
		}
	}

	return $values;
}


function globalsignssl_SSLStepTwo($params) {
	$user = $params["configoption1"];
	$pass = $params["configoption2"];
	$prodcode = $params["configoption3"];
	$baseoption = $params["configoption4"];
	$validityperiod = $params["configoption5"];
	$testmode = $params["configoption6"];
	$webservertype = $params["servertype"];
	$csr = $params["csr"];
	$firstname = $params["firstname"];
	$lastname = $params["lastname"];
	$orgname = $params["orgname"];
	$jobtitle = $params["jobtitle"];
	$emailaddress = $params["email"];
	$address1 = $params["address1"];
	$address2 = $params["address2"];
	$city = $params["city"];
	$state = $params["state"];
	$postcode = $params["postcode"];
	$country = $params["country"];
	$phonenumber = $params["phonenumber"];

	if ($prodcode == "AlphaSSL") {
		$prodcode = "DV_LOW";
	}
	else {
		if ($prodcode == "DomainSSL") {
			$prodcode = "DV";
		}
		else {
			if ($prodcode == "OrganizationSSL") {
				$prodcode = "OV";
			}
			else {
				if ($prodcode == "ExtendedSSL") {
					$prodcode = "EV";
				}
			}
		}
	}


	if ($baseoption == "Wildcard SSL") {
		$baseoption = "wildcard";
	}
	else {
		$baseoption = "";
	}

	$orderkind = "new";

	if ($params["customfields"]["OrderKind"] == "transfer") {
		$orderkind = "transfer";
	}


	if ($params["configoptions"]["ValidityPeriod"]) {
		$validityperiod = $params["configoptions"]["ValidityPeriod"];
	}


	if ($params["configoptions"]["Years"]) {
		$validityperiod = $params["configoptions"]["Years"];
	}

	$validityperiod = $validityperiod * 12;

	if ($testmode) {
		$wsdlorderurl = "http://testsystem.globalsign.com/wsdls/gasorder.wsdl";
		$wsdlqueryurl = "http://testsystem.globalsign.com/wsdls/gasquery.wsdl";
	}
	else {
		$wsdlorderurl = "https://system.globalsign.com/wsdls/gasorder.wsdl";
		$wsdlqueryurl = "https://system.globalsign.com/wsdls/gasquery.wsdl";
	}

	$request = array();
	$request["Request"]["OrderRequestHeader"]["AuthToken"]["UserName"] = $user;
	$request["Request"]["OrderRequestHeader"]["AuthToken"]["Password"] = $pass;
	$request["Request"]["OrderRequestParameter"]["ProductCode"] = $prodcode;
	$request["Request"]["OrderRequestParameter"]["BaseOption"] = $baseoption;
	$request["Request"]["OrderRequestParameter"]["OrderKind"] = $orderkind;
	$request["Request"]["OrderRequestParameter"]["ValidityPeriod"]["Months"] = $validityperiod;
	$request["Request"]["OrderRequestParameter"]["Licenses"] = "1";
	$request["Request"]["OrderRequestParameter"]["CSR"] = $csr;
	$client = new SoapClient( $wsdlorderurl );
	$result = $client->GSValidateOrderParameters( $request );
	logModuleCall( "globalsignssl", "validateorder", $request, (array)$result, "", array( $user, $pass ) );
	$errorcode = $result->Response->OrderResponseHeader->SuccessCode;

	if (0 <= $errorcode) {
		$csrdata = $result->Response->ParsedCSR;
	}
	else {
		$values["error"] = "Error Code: " . $result->Response->OrderResponseHeader->Errors->Error->ErrorCode . " - " . $result->Response->OrderResponseHeader->Errors->Error->ErrorMessage;
		return $values;
	}

	$request = array();
	$request["Request"]["QueryRequestHeader"]["AuthToken"]["UserName"] = $user;
	$request["Request"]["QueryRequestHeader"]["AuthToken"]["Password"] = $pass;
	$request["Request"]["FQDN"] = $csrdata->DomainName;
	$client2 = new SoapClient( $wsdlqueryurl );
	$result = $client2->GetDVApproverList( $request );
	logModuleCall( "globalsignssl", "getapprovers", $request, (array)$result, "", array( $user, $pass ) );
	$errorcode = $result->Response->QueryResponseHeader->SuccessCode;

	if (0 <= $errorcode) {
		$tempapproveremails = $result->Response->Approvers->Approver;
		$approveremails = array();
		foreach ($tempapproveremails as $tempapproveremail) {
			$approveremails[] = $tempapproveremail->ApproverEmail;
		}

		$orderid = $result->Response->OrderID;
	}
	else {
		$values["error"] = $result->Response->QueryResponseHeader->Errors->Error->ErrorCode . " - " . $result->Response->QueryResponseHeader->Errors->Error->ErrorMessage;
		return $values;
	}

	$_SESSION["globalsignsslcert"]["orderid"] = $orderid;
	update_query( "tblsslorders", array( "remoteid" => $orderid ), array( "serviceid" => $params["serviceid"] ) );
	$values["approveremails"] = $approveremails;
	$values["displaydata"]["Domain"] = $csrdata->DomainName;
	$values["displaydata"]["Validity Period"] = $validityperiod . " Months";
	$values["displaydata"]["Organization"] = $csrdata->Organization;
	$values["displaydata"]["Organization Unit"] = $csrdata->OrganizationUnit;
	$values["displaydata"]["Email"] = $csrdata->Email;
	$values["displaydata"]["Locality"] = $csrdata->Locality;
	$values["displaydata"]["State"] = $csrdata->State;
	$values["displaydata"]["Country"] = $csrdata->Country;
	update_query( "tblhosting", array( "domain" => $values["displaydata"]["Domain"] ), array( "id" => $params["serviceid"] ) );
	return $values;
}


function globalsignssl_SSLStepThree($params) {
	$user = $params["configoption1"];
	$pass = $params["configoption2"];
	$prodcode = $params["configoption3"];
	$baseoption = $params["configoption4"];
	$validityperiod = $params["configoption5"];
	$testmode = $params["configoption6"];
	$webservertype = $params["servertype"];
	$csr = $params["csr"];
	$firstname = $params["firstname"];
	$lastname = $params["lastname"];
	$orgname = $params["orgname"];
	$jobtitle = $params["jobtitle"];
	$emailaddress = $params["email"];
	$address1 = $params["address1"];
	$address2 = $params["address2"];
	$city = $params["city"];
	$state = $params["state"];
	$postcode = $params["postcode"];
	$country = $params["country"];
	$phonenumber = $params["phonenumber"];
	$orderid = $params["remoteid"];
	$approveremail = $params["approveremail"];

	if ($prodcode == "AlphaSSL") {
		$prodcode = "DV_LOW";
	}
	else {
		if ($prodcode == "DomainSSL") {
			$prodcode = "DV";
		}
		else {
			if ($prodcode == "OrganizationSSL") {
				$prodcode = "OV";
			}
			else {
				if ($prodcode == "ExtendedSSL") {
					$prodcode = "EV";
				}
			}
		}
	}


	if ($baseoption == "Wildcard SSL") {
		$baseoption = "wildcard";
	}
	else {
		$baseoption = "";
	}

	$orderkind = "new";

	if ($params["customfields"]["OrderKind"] == "transfer") {
		$orderkind = "transfer";
	}


	if ($params["configoptions"]["ValidityPeriod"]) {
		$validityperiod = $params["configoptions"]["ValidityPeriod"];
	}


	if ($params["configoptions"]["Years"]) {
		$validityperiod = $params["configoptions"]["Years"];
	}

	$validityperiod = $validityperiod * 12;

	if ($testmode) {
		$wsdlorderurl = "http://testsystem.globalsign.com/wsdls/gasorder.wsdl";
		$wsdlqueryurl = "http://testsystem.globalsign.com/wsdls/gasquery.wsdl";
	}
	else {
		$wsdlorderurl = "https://system.globalsign.com/wsdls/gasorder.wsdl";
		$wsdlqueryurl = "https://system.globalsign.com/wsdls/gasquery.wsdl";
	}

	$request = array();
	$request["Request"]["OrderRequestHeader"]["AuthToken"]["UserName"] = $user;
	$request["Request"]["OrderRequestHeader"]["AuthToken"]["Password"] = $pass;
	$request["Request"]["OrderRequestParameter"]["ProductCode"] = $prodcode;
	$request["Request"]["OrderRequestParameter"]["BaseOption"] = $baseoption;
	$request["Request"]["OrderRequestParameter"]["OrderKind"] = $orderkind;
	$request["Request"]["OrderRequestParameter"]["ValidityPeriod"]["Months"] = $validityperiod;
	$request["Request"]["OrderRequestParameter"]["Licenses"] = "1";
	$request["Request"]["OrderRequestParameter"]["CSR"] = $csr;
	$request["Request"]["ApproverEmail"] = $approveremail;
	$request["Request"]["ContactInfo"]["FirstName"] = $firstname;
	$request["Request"]["ContactInfo"]["LastName"] = $lastname;
	$request["Request"]["ContactInfo"]["Phone"] = $phonenumber;
	$request["Request"]["ContactInfo"]["Email"] = $emailaddress;

	if ($prodcode == "OV") {
		$request["Request"]["OrganizationInfo"]["OrganizationName"] = $params["fields"]["orgname"];
		$request["Request"]["OrganizationInfo"]["OrganizationAddress"]["AddressLine1"] = $params["fields"]["orgaddress"];
		$request["Request"]["OrganizationInfo"]["OrganizationAddress"]["City"] = $params["fields"]["orgcity"];
		$request["Request"]["OrganizationInfo"]["OrganizationAddress"]["Region"] = $params["fields"]["orgstate"];
		$request["Request"]["OrganizationInfo"]["OrganizationAddress"]["PostalCode"] = $params["fields"]["orgpostcode"];
		$request["Request"]["OrganizationInfo"]["OrganizationAddress"]["Country"] = $params["fields"]["orgcountry"];
		$request["Request"]["OrganizationInfo"]["OrganizationAddress"]["Phone"] = $params["fields"]["orgphone"];
	}
	else {
		if ($prodcode == "EV") {
			$bizcatcode = $params["fields"]["bizcatcode"];

			if ($bizcatcode == "Business Entity") {
				$bizcatcode = "BE";
			}
			else {
				if ($bizcatcode == "Government Entity") {
					$bizcatcode = "GE";
				}
				else {
					$bizcatcode = "PO";
				}
			}

			$request["Request"]["OrganizationInfoEV"]["BusinessAssumedName"] = $params["fields"]["bizname"];
			$request["Request"]["OrganizationInfoEV"]["BusinessCategoryCode"] = $bizcatcode;
			$request["Request"]["OrganizationInfoEV"]["OrganizationAddress"]["AddressLine1"] = $params["fields"]["orgaddress"];
			$request["Request"]["OrganizationInfoEV"]["OrganizationAddress"]["City"] = $params["fields"]["orgcity"];
			$request["Request"]["OrganizationInfoEV"]["OrganizationAddress"]["Region"] = $params["fields"]["orgstate"];
			$request["Request"]["OrganizationInfoEV"]["OrganizationAddress"]["PostalCode"] = $params["fields"]["orgpostcode"];
			$request["Request"]["OrganizationInfoEV"]["OrganizationAddress"]["Country"] = $params["fields"]["orgcountry"];
			$request["Request"]["OrganizationInfoEV"]["OrganizationAddress"]["Phone"] = $params["fields"]["orgphone"];
			$request["Request"]["RequestorInfo"]["FirstName"] = $firstname;
			$request["Request"]["RequestorInfo"]["LastName"] = $lastname;
			$request["Request"]["RequestorInfo"]["OrganizationName"] = $orgname;
			$request["Request"]["RequestorInfo"]["Email"] = $emailaddress;
			$request["Request"]["RequestorInfo"]["Phone"] = $phonenumber;
			$request["Request"]["ApproverInfo"]["FirstName"] = $firstname;
			$request["Request"]["ApproverInfo"]["LastName"] = $lastname;
			$request["Request"]["ApproverInfo"]["OrganizationName"] = $orgname;
			$request["Request"]["ApproverInfo"]["Email"] = $emailaddress;
			$request["Request"]["ApproverInfo"]["Phone"] = $phonenumber;
			$request["Request"]["AuthorizedSignerInfo"]["FirstName"] = $firstname;
			$request["Request"]["AuthorizedSignerInfo"]["LastName"] = $lastname;
			$request["Request"]["AuthorizedSignerInfo"]["Email"] = $emailaddress;
			$request["Request"]["AuthorizedSignerInfo"]["Phone"] = $phonenumber;
			$request["Request"]["JurisdictionInfo"]["Country"] = $country;
			$request["Request"]["JurisdictionInfo"]["StateOrProvince"] = $state;
			$request["Request"]["JurisdictionInfo"]["Locality"] = $city;
			$request["Request"]["JurisdictionInfo"]["IncorporatingAgencyRegistrationNumber"] = $params["fields"]["orgregnum"];
		}
		else {
			$request["Request"]["OrderID"] = $orderid;
		}
	}

	$client = new SoapClient( $wsdlorderurl );

	if ($prodcode == "DV_LOW") {
		$result = $client->GSDVOrder( $request );
	}
	else {
		if ($prodcode == "DV") {
			$result = $client->GSDVOrder( $request );
		}
		else {
			if ($prodcode == "OV") {
				$result = $client->GSOVOrder( $request );
			}
			else {
				if ($prodcode == "EV") {
					$result = $client->GSEVOrder( $request );
				}
			}
		}
	}

	logModuleCall( "globalsignssl", "order", $request, (array)$result, "", array( $user, $pass ) );
	$errorcode = $result->Response->OrderResponseHeader->SuccessCode;

	if (0 <= $errorcode) {
		$orderid = $result->Response->OrderID;
		update_query( "tblsslorders", array( "provisiondate" => "now()" ), array( "serviceid" => $params["serviceid"] ) );
	}
	else {
		if ($result->Response->OrderResponseHeader->Errors->Error->ErrorCode) {
			$values["error"] = $result->Response->OrderResponseHeader->Errors->Error->ErrorCode . " - " . $result->Response->OrderResponseHeader->Errors->Error->ErrorMessage;
		}
		else {
			if ($result->Response->OrderResponseHeader->Errors->Error[0]->ErrorCode) {
				$values["error"] = $result->Response->OrderResponseHeader->Errors->Error[0]->ErrorCode . " - " . $result->Response->OrderResponseHeader->Errors->Error[0]->ErrorMessage;
			}
			else {
				$values["error"] = "An Unknown Error Occurred. Please contact support.";
			}
		}
	}

	return $values;
}


?>