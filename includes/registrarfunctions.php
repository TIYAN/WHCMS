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
 **/

function checkDomain($domain) {
	global $domainparts;

	if (preg_match("/^[a-z0-9][a-z0-9\-]+[a-z0-9](\.[a-z]{2,4})+$/i", $domain)) {
		$domainparts = explode(".", $domain, 2);
		return true;
	}

	return false;
}

function getRegistrarsDropdownMenu($registrar, $name = "registrar") {
	global $aInt;

	$code = "<select name=\"" . $name . "\"><option value=\"\">" . $aInt->lang("global", "none") . "</option>";
	$result = select_query("tblregistrars", "DISTINCT registrar", "", "registrar", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$code .= "<option value=\"" . $data[0] . "\"";

		if ($registrar == $data[0]) {
			$code .= " selected";
		}

		$code .= ">" . ucfirst($data[0]) . "</option>";
	}

	$code .= "</select>";
	return $code;
}

function loadRegistrarModule($registrar) {
	if (!function_exists($registrar . "_getConfigArray")) {
		$registrar = get_query_val("tblregistrars", "registrar", array("registrar" => $registrar));

		if (!$registrar) {
			return false;
		}


		if (!isValidforPath($registrar)) {
			exit("Invalid Registrar Module Name");
		}


		if (!function_exists($registrar . "_getConfigArray")) {
			$regpath = ROOTDIR . "/modules/registrars/" . $registrar . "/" . $registrar . ".php";

			if (file_exists($regpath)) {
				require_once $regpath;
			}
		}
	}

	return true;
}

function RegBuildParams($params) {
	$reg = $params['registrar'];

	if (!$reg) {
		return false;
	}


	if (!loadRegistrarModule($reg)) {
		return false;
	}

	$regconfigs = getRegistrarConfigOptions($reg);
	$params = array_merge($params, $regconfigs);
	return $params;
}

function RegCallFunction($params, $func, $noarr = false) {
	$params = RegBuildParams($params);

	if (!$params) {
		return false;
	}

	$values = array();

	if (function_exists($params['registrar'] . "_" . $func)) {
		$values = call_user_func($params['registrar'] . "_" . $func, $params);
	}
	else {
		return array("na" => true);
	}


	if (!$noarr && !is_array($values)) {
		$values = array();
	}

	return $values;
}

function getRegistrarConfigOptions($registrar) {
	$configoptions = array();
	$result = select_query("tblregistrars", "", array("registrar" => $registrar));

	while ($data = @mysql_fetch_array($result)) {
		$setting = $data['setting'];
		$value = $data['value'];
		$configoptions[$setting] = decrypt($value);
	}

	return $configoptions;
}

function RegGetNameservers($params) {
	return RegCallFunction($params, "GetNameservers");
}

function RegSaveNameservers($params) {
	$i = 1;

	while ($i <= 5) {
		$params["ns" . $i] = trim($params["ns" . $i]);
		++$i;
	}

	$values = RegCallFunction($params, "SaveNameservers");

	if (!$values) {
		return false;
	}

	$userid = get_query_val("tbldomains", "userid", array("id" => $params['domainid']));

	if ($values['error']) {
		logActivity("Domain Registrar Command: Save Nameservers - Failed: " . $values['error'] . " - Domain ID: " . $params['domainid'], $userid);
	}
	else {
		logActivity("Domain Registrar Command: Save Nameservers - Successful", $userid);
	}

	return $values;
}

function RegGetRegistrarLock($params) {
	$values = RegCallFunction($params, "GetRegistrarLock", 1);

	if (is_array($values)) {
		return "";
	}

	return $values;
}

function RegSaveRegistrarLock($params) {
	$values = RegCallFunction($params, "SaveRegistrarLock");

	if (!$values) {
		return false;
	}

	$userid = get_query_val("tbldomains", "userid", array("id" => $params['domainid']));

	if ($values['error']) {
		logActivity("Domain Registrar Command: Toggle Registrar Lock - Failed: " . $values['error'] . " - Domain ID: " . $params['domainid'], $userid);
	}
	else {
		logActivity("Domain Registrar Command: Toggle Registrar Lock - Successful", $userid);
	}

	return $values;
}

function RegGetURLForwarding($params) {
	return RegCallFunction($params, "GetURLForwarding");
}

function RegSaveURLForwarding($params) {
	return RegCallFunction($params, "SaveURLForwarding");
}

function RegGetEmailForwarding($params) {
	return RegCallFunction($params, "GetEmailForwarding");
}

function RegSaveEmailForwarding($params) {
	return RegCallFunction($params, "SaveEmailForwarding");
}

function RegGetDNS($params) {
	return RegCallFunction($params, "GetDNS");
}

function RegSaveDNS($params) {
	return RegCallFunction($params, "SaveDNS");
}

function RegRenewDomain($params) {
	$domainid = $params['domainid'];
	$result = select_query("tbldomains", "", array("id" => $domainid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$domain = $data['domain'];
	$orderid = $data['orderid'];
	$registrar = $data['registrar'];
	$registrationperiod = $data['registrationperiod'];
	$dnsmanagement = ($data['dnsmanagement'] ? true : false);
	$emailforwarding = ($data['emailforwarding'] ? true : false);
	$idprotection = ($data['idprotection'] ? true : false);
	$domainparts = explode(".", $domain, 2);
	$params['registrar'] = $registrar;
	$params['sld'] = $domainparts[0];
	$params['tld'] = $domainparts[1];
	$params['regperiod'] = $registrationperiod;
	$params['dnsmanagement'] = $dnsmanagement;
	$params['emailforwarding'] = $emailforwarding;
	$params['idprotection'] = $idprotection;
	$values = RegCallFunction($params, "RenewDomain");

	if (!is_array($values)) {
		return false;
	}


	if ($values['na']) {
		return array("error" => "Registrar Function Not Supported");
	}


	if ($values['error']) {
		logActivity("Domain Renewal Failed - Domain ID: " . $domainid . " - Domain: " . $domain . " - Error: " . $values['error'], $userid);
		run_hook("AfterRegistrarRenewalFailed", array("params" => $params, "error" => $values['error']));
	}
	else {
		$result = select_query("tbldomains", "expirydate,registrationperiod", array("id" => $domainid));
		$data = mysql_fetch_array($result);
		$expirydate = $data['expirydate'];
		$registrationperiod = $data['registrationperiod'];
		$year = substr($expirydate, 0, 4);
		$month = substr($expirydate, 5, 2);
		$day = substr($expirydate, 8, 2);
		$year = $year + $registrationperiod;
		$expirydate = $year . "-" . $month . "-" . $day;
		update_query("tbldomains", array("expirydate" => $expirydate, "status" => "Active"), array("id" => $domainid));
		logActivity("Domain Renewed Successfully - Domain ID: " . $domainid . " - Domain: " . $domain, $userid);
		run_hook("AfterRegistrarRenewal", array("params" => $params));
	}

	return $values;
}

function RegRegisterDomain($paramvars) {
	global $CONFIG;

	$domainid = $paramvars['domainid'];
	$result = select_query("tbldomains", "", array("id" => $domainid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$domain = $data['domain'];
	$orderid = $data['orderid'];
	$registrar = $data['registrar'];
	$registrationperiod = $data['registrationperiod'];
	$dnsmanagement = ($data['dnsmanagement'] ? true : false);
	$emailforwarding = ($data['emailforwarding'] ? true : false);
	$idprotection = ($data['idprotection'] ? true : false);
	$domainparts = explode(".", $domain, 2);
	$result = select_query("tblorders", "contactid", array("id" => $orderid));
	$data = mysql_fetch_array($result);
	$contactid = $data['contactid'];

	if (!function_exists("getClientsDetails")) {
		require dirname(__FILE__) . "/clientfunctions.php";
	}

	$clientsdetails = getClientsDetails($userid, $contactid);
	$clientsdetails['fullstate'] = $clientsdetails['state'];
	$clientsdetails['state'] = convertStateToCode($clientsdetails['state'], $clientsdetails['country']);
	global $params;

	$params = array_merge($paramvars, $clientsdetails);
	$params['registrar'] = $registrar;
	$params['sld'] = $domainparts[0];
	$params['tld'] = $domainparts[1];
	$params['regperiod'] = $registrationperiod;
	$params['dnsmanagement'] = $dnsmanagement;
	$params['emailforwarding'] = $emailforwarding;
	$params['idprotection'] = $idprotection;

	if ($CONFIG['RegistrarAdminUseClientDetails'] == "on") {
		$params['adminfirstname'] = $clientsdetails['firstname'];
		$params['adminlastname'] = $clientsdetails['lastname'];
		$params['admincompanyname'] = $clientsdetails['companyname'];
		$params['adminemail'] = $clientsdetails['email'];
		$params['adminaddress1'] = $clientsdetails['address1'];
		$params['adminaddress2'] = $clientsdetails['address2'];
		$params['admincity'] = $clientsdetails['city'];
		$params['adminfullstate'] = $clientsdetails['fullstate'];
		$params['adminstate'] = $clientsdetails['state'];
		$params['adminpostcode'] = $clientsdetails['postcode'];
		$params['admincountry'] = $clientsdetails['country'];
		$params['adminphonenumber'] = $clientsdetails['phonenumber'];
	}
	else {
		$params['adminfirstname'] = $CONFIG['RegistrarAdminFirstName'];
		$params['adminlastname'] = $CONFIG['RegistrarAdminLastName'];
		$params['admincompanyname'] = $CONFIG['RegistrarAdminCompanyName'];
		$params['adminemail'] = $CONFIG['RegistrarAdminEmailAddress'];
		$params['adminaddress1'] = $CONFIG['RegistrarAdminAddress1'];
		$params['adminaddress2'] = $CONFIG['RegistrarAdminAddress2'];
		$params['admincity'] = $CONFIG['RegistrarAdminCity'];
		$params['adminfullstate'] = $CONFIG['RegistrarAdminStateProvince'];
		$params['adminstate'] = convertStateToCode($CONFIG['RegistrarAdminStateProvince'], $CONFIG['RegistrarAdminCountry']);
		$params['adminpostcode'] = $CONFIG['RegistrarAdminPostalCode'];
		$params['admincountry'] = $CONFIG['RegistrarAdminCountry'];
		$params['adminphonenumber'] = $CONFIG['RegistrarAdminPhone'];
	}

	require ROOTDIR . "/includes/countriescallingcodes.php";
	$phonenumber = $params['phonenumber'];
	$adminphonenumber = $params['adminphonenumber'];
	$phonenumber = preg_replace("/[^0-9]/", "", $phonenumber);
	$adminphonenumber = preg_replace("/[^0-9]/", "", $adminphonenumber);
	$countrycode = $params['country'];
	$admincountrycode = $params['admincountry'];
	$countrycode = $countrycallingcodes[$countrycode];
	$admincountrycode = $countrycallingcodes[$admincountrycode];
	$params['fullphonenumber'] = "+" . $countrycode . "." . $phonenumber;
	$params['adminfullphonenumber'] = "+" . $admincountrycode . "." . $adminphonenumber;

	if (!$params['ns1'] && !$params['ns2']) {
		$result = select_query("tblorders", "nameservers", array("id" => $orderid));
		$data = mysql_fetch_array($result);
		$nameservers = $data['nameservers'];
		$result = select_query("tblhosting", "server", array("domain" => $domain));
		$data = mysql_fetch_array($result);
		$server = $data['server'];

		if ($server) {
			$result = select_query("tblservers", "", array("id" => $server));
			$data = mysql_fetch_array($result);
			$i = 1;

			while ($i <= 5) {
				$params["ns" . $i] = trim($data["nameserver" . $i]);
				++$i;
			}
		}
		else {
			if ($nameservers && $nameservers != ",") {
				$nameservers = explode(",", $nameservers);
				$i = 1;

				while ($i <= 5) {
					$params["ns" . $i] = trim($nameservers[$i - 1]);
					++$i;
				}
			}
			else {
				$i = 1;

				while ($i <= 5) {
					$params["ns" . $i] = trim($CONFIG["DefaultNameserver" . $i]);
					++$i;
				}
			}
		}
	}
	else {
		$i = 1;

		while ($i <= 5) {
			$params["ns" . $i] = trim($params["ns" . $i]);
			++$i;
		}
	}

	$result = select_query("tbldomainsadditionalfields", "", array("domainid" => $domainid));

	while ($data = mysql_fetch_array($result)) {
		$field_name = $data['name'];
		$field_value = $data['value'];
		$params['additionalfields'][$field_name] = $field_value;
	}

	$originaldetails = $params;
	$params = foreignChrReplace($params);
	$params['original'] = $originaldetails;
	run_hook("PreDomainRegister", array("domain" => $domain));
	$values = RegCallFunction($params, "RegisterDomain");

	if (!is_array($values)) {
		return false;
	}


	if ($values['na']) {
		logActivity("Domain Registration Not Supported by Module - Domain ID: " . $domainid . " - Domain: " . $domain);
		return array("error" => "Registrar Function Not Supported");
	}


	if ($values['error']) {
		logActivity("Domain Registration Failed - Domain ID: " . $domainid . " - Domain: " . $domain . " - Error: " . $values['error'], $userid);
		run_hook("AfterRegistrarRegistrationFailed", array("params" => $params, "error" => $values['error']));
	}
	else {
		$expirydate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") + $registrationperiod));
		update_query("tbldomains", array("registrationdate" => date("Ymd"), "expirydate" => $expirydate, "status" => "Active"), array("id" => $domainid));
		logActivity("Domain Registered Successfully - Domain ID: " . $domainid . " - Domain: " . $domain, $userid);
		run_hook("AfterRegistrarRegistration", array("params" => $params));
	}

	return $values;
}

function RegTransferDomain($paramvars) {
	global $CONFIG;

	$domainid = $paramvars['domainid'];
	$passedepp = $paramvars['transfersecret'];
	$result = select_query("tbldomains", "", array("id" => $domainid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$domain = $data['domain'];
	$orderid = $data['orderid'];
	$registrar = $data['registrar'];
	$registrationperiod = $data['registrationperiod'];
	$dnsmanagement = ($data['dnsmanagement'] ? true : false);
	$emailforwarding = ($data['emailforwarding'] ? true : false);
	$idprotection = ($data['idprotection'] ? true : false);
	$domainparts = explode(".", $domain, 2);
	$result = select_query("tblorders", "contactid,nameservers,transfersecret", array("id" => $orderid));
	$data = mysql_fetch_array($result);
	$contactid = $data['contactid'];
	$nameservers = $data['nameservers'];
	$transfersecret = $data['transfersecret'];

	if (!function_exists("getClientsDetails")) {
		require dirname(__FILE__) . "/clientfunctions.php";
	}

	$clientsdetails = getClientsDetails($userid, $contactid);
	$clientsdetails['fullstate'] = $clientsdetails['state'];
	$clientsdetails['state'] = convertStateToCode($clientsdetails['state'], $clientsdetails['country']);
	global $params;

	$params = array_merge($paramvars, $clientsdetails);
	$params['registrar'] = $registrar;
	$params['sld'] = $domainparts[0];
	$params['tld'] = $domainparts[1];
	$params['regperiod'] = $registrationperiod;
	$params['dnsmanagement'] = $dnsmanagement;
	$params['emailforwarding'] = $emailforwarding;
	$params['idprotection'] = $idprotection;

	if ($CONFIG['RegistrarAdminUseClientDetails'] == "on") {
		$params['adminfirstname'] = $clientsdetails['firstname'];
		$params['adminlastname'] = $clientsdetails['lastname'];
		$params['admincompanyname'] = $clientsdetails['companyname'];
		$params['adminemail'] = $clientsdetails['email'];
		$params['adminaddress1'] = $clientsdetails['address1'];
		$params['adminaddress2'] = $clientsdetails['address2'];
		$params['admincity'] = $clientsdetails['city'];
		$params['adminfullstate'] = $clientsdetails['fullstate'];
		$params['adminstate'] = $clientsdetails['state'];
		$params['adminpostcode'] = $clientsdetails['postcode'];
		$params['admincountry'] = $clientsdetails['country'];
		$params['adminphonenumber'] = $clientsdetails['phonenumber'];
	}
	else {
		$params['adminfirstname'] = $CONFIG['RegistrarAdminFirstName'];
		$params['adminlastname'] = $CONFIG['RegistrarAdminLastName'];
		$params['admincompanyname'] = $CONFIG['RegistrarAdminCompanyName'];
		$params['adminemail'] = $CONFIG['RegistrarAdminEmailAddress'];
		$params['adminaddress1'] = $CONFIG['RegistrarAdminAddress1'];
		$params['adminaddress2'] = $CONFIG['RegistrarAdminAddress2'];
		$params['admincity'] = $CONFIG['RegistrarAdminCity'];
		$params['adminstate'] = $CONFIG['RegistrarAdminStateProvince'];
		$params['adminpostcode'] = $CONFIG['RegistrarAdminPostalCode'];
		$params['admincountry'] = $CONFIG['RegistrarAdminCountry'];
		$params['adminphonenumber'] = $CONFIG['RegistrarAdminPhone'];
	}

	require ROOTDIR . "/includes/countriescallingcodes.php";
	$phonenumber = $params['phonenumber'];
	$adminphonenumber = $params['adminphonenumber'];
	$phonenumber = preg_replace("/[^0-9]/", "", $phonenumber);
	$adminphonenumber = preg_replace("/[^0-9]/", "", $adminphonenumber);
	$countrycode = $params['country'];
	$admincountrycode = $params['admincountry'];
	$countrycode = $countrycallingcodes[$countrycode];
	$admincountrycode = $countrycallingcodes[$admincountrycode];
	$params['fullphonenumber'] = "+" . $countrycode . "." . $phonenumber;
	$params['adminfullphonenumber'] = "+" . $admincountrycode . "." . $adminphonenumber;

	if (!$params['ns1'] && !$params['ns2']) {
		$result = select_query("tblorders", "nameservers", array("id" => $orderid));
		$data = mysql_fetch_array($result);
		$nameservers = $data['nameservers'];
		$result = select_query("tblhosting", "server", array("domain" => $domain));
		$data = mysql_fetch_array($result);
		$server = $data['server'];

		if ($server) {
			$result = select_query("tblservers", "", array("id" => $server));
			$data = mysql_fetch_array($result);
			$i = 1;

			while ($i <= 5) {
				$params["ns" . $i] = trim($data["nameserver" . $i]);
				++$i;
			}
		}
		else {
			if ($nameservers && $nameservers != ",") {
				$nameservers = explode(",", $nameservers);
				$i = 1;

				while ($i <= 5) {
					$params["ns" . $i] = trim($nameservers[$i - 1]);
					++$i;
				}
			}
			else {
				$i = 1;

				while ($i <= 5) {
					$params["ns" . $i] = trim($CONFIG["DefaultNameserver" . $i]);
					++$i;
				}
			}
		}
	}
	else {
		$i = 1;

		while ($i <= 5) {
			$params["ns" . $i] = trim($params["ns" . $i]);
			++$i;
		}
	}

	$result = select_query("tbldomainsadditionalfields", "", array("domainid" => $domainid));

	while ($data = mysql_fetch_array($result)) {
		$field_name = $data['name'];
		$field_value = $data['value'];
		$params['additionalfields'][$field_name] = $field_value;
	}

	$originaldetails = $params;
	$params = foreignChrReplace($params);
	$params['original'] = $originaldetails;

	if (!$params['transfersecret']) {
		$transfersecret = ($transfersecret ? unserialize($transfersecret) : array());
		$params['transfersecret'] = $params['eppcode'] = $transfersecret[$domain];
	}
	else {
		$params['transfersecret'] = $params['eppcode'] = html_entity_decode($passedepp);
	}

	run_hook("PreDomainRegister", array("domain" => $domain));
	$values = RegCallFunction($params, "TransferDomain");

	if (!is_array($values)) {
		return false;
	}


	if ($values['na']) {
		logActivity("Domain Transfer Not Supported by Module - Domain ID: " . $domainid . " - Domain: " . $domain);
		return array("error" => "Registrar Function Not Supported");
	}


	if ($values['error']) {
		logActivity("Domain Transfer Failed - Domain ID: " . $domainid . " - Domain: " . $domain . " - Error: " . $values['error'], $userid);
		run_hook("AfterRegistrarTransferFailed", array("params" => $params, "error" => $values['error']));
	}
	else {
		update_query("tbldomains", array("status" => "Pending Transfer"), array("id" => $domainid));
		$array = array("date" => "now()", "title" => "Domain Pending Transfer", "description" => "Check the transfer status of the domain " . $params['sld'] . "." . $params['tld'] . "", "admin" => "", "status" => "In Progress", "duedate" => date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 5, date("Y"))));
		insert_query("tbltodolist", $array);
		logActivity("Domain Transfer Initiated Successfully - Domain ID: " . $domainid . " - Domain: " . $domain, $userid);
		run_hook("AfterRegistrarTransfer", array("params" => $params));
	}

	return $values;
}

function RegGetContactDetails($params) {
	return RegCallFunction($params, "GetContactDetails");
}

function RegSaveContactDetails($params) {
	$result = select_query("tbldomains", "id", array("domain" => $params['sld'] . "." . $params['tld']));
	$result = mysql_fetch_array($result);
	$domainid = $data['id'];
	$result = select_query("tbldomainsadditionalfields", "", array("domainid" => $domainid));

	while ($data = mysql_fetch_array($result)) {
		$field_name = $data['name'];
		$field_value = $data['value'];
		$params['additionalfields'][$field_name] = $field_value;
	}

	$originaldetails = $params;
	$params = foreignChrReplace($params);
	$params['original'] = $originaldetails;
	$values = RegCallFunction($params, "SaveContactDetails");

	if (!$values) {
		return false;
	}

	$result = select_query("tbldomains", "userid", array("id" => $params['domainid']));
	$data = mysql_fetch_array($result);
	$userid = $data[0];

	if ($values['error']) {
		logActivity("Domain Registrar Command: Update Contact Details - Failed: " . $values['error'] . " - Domain ID: " . $params['domainid'], $userid);
	}
	else {
		logActivity("Domain Registrar Command: Update Contact Details - Successful", $userid);
	}

	return $values;
}

function RegGetEPPCode($params) {
	$values = RegCallFunction($params, "GetEPPCode");

	if (!$values) {
		return false;
	}


	if ($values['eppcode']) {
		$values['eppcode'] = htmlentities($values['eppcode']);
	}

	return $values;
}

function RegRequestDelete($params) {
	$values = RegCallFunction($params, "RequestDelete");

	if (!$values) {
		return false;
	}


	if (!$values['error']) {
		update_query("tbldomains", array("status" => "Cancelled"), array("id" => $params['domainid']));
	}

	return $values;
}

function RegReleaseDomain($params) {
	return RegCallFunction($params, "ReleaseDomain");
}

function RegRegisterNameserver($params) {
	return RegCallFunction($params, "RegisterNameserver");
}

function RegModifyNameserver($params) {
	return RegCallFunction($params, "ModifyNameserver");
}

function RegDeleteNameserver($params) {
	return RegCallFunction($params, "DeleteNameserver");
}

function RegIDProtectToggle($params) {
	$domainid = $params['domainid'];
	$result = select_query("tbldomains", "idprotection", array("id" => $domainid));
	$data = mysql_fetch_assoc($result);
	$idprotection = ($data['idprotection'] ? true : false);
	$params['protectenable'] = $idprotection;
	return RegCallFunction($params, "IDProtectToggle");
}

function RegClientAreaOutput($params) {
	$domainid = $params['domainid'];
	$result = select_query("tbldomains", "idprotection", array("id" => $domainid));
	$data = mysql_fetch_assoc($result);
	$idprotection = ($data['idprotection'] ? true : false);
	$params['protectenable'] = $idprotection;
	$values = "";
	return RegCallFunction($params, "ClientArea");
}

function RegGetDefaultNameservers($params, $domain) {
	global $CONFIG;

	$serverid = get_query_val("tblhosting", "server", array("domain" => $domain));

	if ($serverid) {
		$result = select_query("tblservers", "", array("id" => $serverid));
		$data = mysql_fetch_array($result);
		$i = 1;

		while ($i <= 5) {
			$params["ns" . $i] = trim($data["nameserver" . $i]);
			++$i;
		}
	}
	else {
		$i = 1;

		while ($i <= 5) {
			$params["ns" . $i] = trim($CONFIG["DefaultNameserver" . $i]);
			++$i;
		}
	}

	return $params;
}

function RegCustomFunction($params, $func_name) {
	return RegCallFunction($params, $func_name);
}

function RebuildRegistrarModuleHookCache() {
	global $CONFIG;

	$dh = $hooksarray = array();
	readdir($dh);

	while (false !== $module = opendir(ROOTDIR . "/modules/registrars/")) {
		if (is_file(ROOTDIR . ("/modules/registrars/" . $module . "/hooks.php")) && get_query_val("tblregistrars", "COUNT(*)", array("registrar" => $module))) {
			$hooksarray[] = $module;
		}
	}

	closedir($dh);

	if (isset($CONFIG['RegistrarModuleHooks'])) {
		update_query("tblconfiguration", array("value" => implode(",", $hooksarray)), array("setting" => "RegistrarModuleHooks"));
		return null;
	}

	insert_query("tblconfiguration", array("setting" => "RegistrarModuleHooks", "value" => implode(",", $hooksarray)));
}

?>