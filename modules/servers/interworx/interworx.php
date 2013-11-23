<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 * */

function interworx_ConfigOptions() {
	$configarray = array( "Package Name" => array( "Type" => "text", "Size" => "25" ), "Theme" => array( "Type" => "text", "Size" => "25" ), "Disk & BW Overselling" => array( "Type" => "yesno", "Description" => "If reseller, tick to allow" ) );
	return $configarray;
}


function interworx_ClientArea($params) {
	global $_LANG;

	$domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);

	if ($params["type"] == "reselleraccount") {
		$code = "<form action=\"https://" . $domain . ":2443/nodeworx/index.php?action=login\" method=\"post\" target=\"_blank\">
        <input type=\"hidden\" name=\"email\" value=\"" . $params["username"] . "\" />
		<input type=\"hidden\" name=\"password\" value=\"" . $params["password"] . "\" />
		<input type=\"submit\" value=\"" . $_LANG["nodeworxlogin"] . "\" class=\"button\" />
        </form>";
	}
	else {
		$code = "<form action=\"https://" . $domain . ":2443/siteworx/index.php?action=login\" method=\"post\" target=\"_blank\">
		<input type=\"hidden\" name=\"email\" value=\"" . $params["clientsdetails"]["email"] . "\" />
		<input type=\"hidden\" name=\"password\" value=\"" . $params["password"] . "\" />
        <input type=\"hidden\" name=\"domain\" value=\"" . $params["domain"] . "\" />
		<input type=\"submit\"  value=\"" . $_LANG["siteworxlogin"] . "\" class=\"button\" />
        </form>";
	}

	return $code;
}


function interworx_AdminLink($params) {
	$domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
	$code = "<form action=\"https://" . $domain . ":2443/nodeworx/\" method=\"post\" target=\"_blank\"><input type=\"submit\" value=\"InterWorx Panel\" /></form>";
	return $code;
}


$key = function interworx_CreateAccount($params) {;
	$api_controller = "/nodeworx/siteworx";

	if ($params["configoptions"]["Dedicated IP"]) {
		$action = "listDedicatedFreeIps";
		$client = new soapclient( "https://" . $params["serverip"] . ":2443/nodeworx/soap?wsdl" );
		$result = $client->route( $key, $api_controller, $action, $input );
		logModuleCall( "interworx", $action, $input, $result );

		if ($result["status"]) {
			return $result["status"] . " - " . $result["payload"];
		}
	}
	else {
		$action = "listFreeIps";
		$client = new soapclient( "https://" . $params["serverip"] . ":2443/nodeworx/soap?wsdl" );
		$result = $client->route( $key, $api_controller, $action, $input );
		logModuleCall( "interworx", $action, $input, $result );

		if ($result["status"]) {
			return $result["status"] . " - " . $result["payload"];
		}
	}

	$ipaddress = $result["payload"][0][0];

	if ($params["type"] == "reselleraccount") {
		$overselling = ($params["configoption3"] ? "1" : "0");
		$api_controller = "/nodeworx/reseller";
		$action = "add";
		$input = array( "nickname" => strtolower( $params["clientsdetails"]["firstname"] . $params["clientsdetails"]["lastname"] ), "email" => $params["clientsdetails"]["email"], "password" => $params["password"], "confirm_password" => $params["password"], "language" => "en-us", "theme" => $params["configoption2"], "billing_day" => "1", "status" => "active", "packagetemplate" => $params["configoption1"], "RSL_OPT_OVERSELL_STORAGE" => $overselling, "RSL_OPT_OVERSELL_BANDWIDTH" => $overselling, "ips" => $ipaddress, "database_servers" => "localhost" );
		update_query( "tblhosting", array( "username" => $params["clientsdetails"]["email"] ), array( "id" => $params["serviceid"] ) );
	}
	else {
		$action = "add";
		$input = array( "domainname" => $params["domain"], "ipaddress" => $ipaddress, "uniqname" => $params["username"], "nickname" => strtolower( $params["clientsdetails"]["firstname"] . $params["clientsdetails"]["lastname"] ), "email" => $params["clientsdetails"]["email"], "password" => $params["password"], "confirm_password" => $params["password"], "language" => "en-us", "theme" => $params["configoption2"], "packagetemplate" => $params["configoption1"] );
	}

	$client = new soapclient( "https://" . $params["serverip"] . ":2443/nodeworx/soap?wsdl" );
	$client->route( $key, $api_controller, $action, $input );
	$result = $params["serveraccesshash"];
	logModuleCall( "interworx", $action, $input, $result );

	if ($result["status"]) {
		return $result["status"] . " - " . $result["payload"];
	}

	return "success";
}


$key = function interworx_TerminateAccount($params) {;

	if ($params["type"] == "reselleraccount") {
		$resellers = interworx_GetResellers( $params );
		$email = $params["clientsdetails"]["email"];
		$resellerid = $resellers[$email];

		if (!$resellerid) {
			return "Reseller ID Not Found";
		}

		$api_controller = "/nodeworx/reseller";
		$action = "delete";
		$input = array( "reseller_id" => $resellerid );
	}
	else {
		$api_controller = "/nodeworx/siteworx";
		$action = "delete";
		$input = array( "domain" => $params["domain"], "confirm_action" => "1" );
	}

	$client = new soapclient( "https://" . $params["serverip"] . ":2443/nodeworx/soap?wsdl" );
	$client->route( $key, $api_controller, $action, $input );
	$result = $params["serveraccesshash"];
	logModuleCall( "interworx", $action, $input, $result );

	if ($result["status"]) {
		return $result["status"] . " - " . $result["payload"];
	}

	return "success";
}


function interworx_UsageUpdate($params) {
	$key = $params["serveraccesshash"];
	$api_controller = "/nodeworx/siteworx";
	$action = "listBandwidthAndStorage";
	$input = array();
	$client = new soapclient( "https://" . $params["serverip"] . ":2443/nodeworx/soap?wsdl" );
	$result = $client->route( $key, $api_controller, $action, $input );
	logModuleCall( "interworx", $action, $input, $result );
	$domainsdata = $result["payload"];
	foreach ($domainsdata as $data) {
		$domain = $data["domain"];
		$bandwidth_used = $data["bandwidth_used"];
		$bandwidth = $data["bandwidth"];
		$storage_used = $data["storage_used"];
		$storage = $data["storage"];
		update_query( "tblhosting", array( "diskusage" => $storage_used, "disklimit" => $storage, "bwusage" => $bandwidth_used, "bwlimit" => $bandwidth, "lastupdate" => "now()" ), array( "domain" => $domain, "server" => $params["serverid"] ) );
	}

}


$key = function interworx_SuspendAccount($params) {;

	if ($params["type"] == "reselleraccount") {
		$resellers = interworx_GetResellers( $params );
		$email = $params["clientsdetails"]["email"];
		$resellerid = $resellers[$email];

		if (!$resellerid) {
			return "Reseller ID Not Found";
		}

		$api_controller = "/nodeworx/reseller";
		$action = "edit";
		$input = array( "reseller_id" => $resellerid, "status" => "inactive" );
	}
	else {
		$api_controller = "/nodeworx/siteworx";
		$action = "edit";
		$input = array( "domain" => $params["domain"], "status" => "0" );
	}

	$client = new soapclient( "https://" . $params["serverip"] . ":2443/nodeworx/soap?wsdl" );
	$client->route( $key, $api_controller, $action, $input );
	$result = $params["serveraccesshash"];
	logModuleCall( "interworx", $action, $input, $result );

	if ($result["status"]) {
		return $result["status"] . " - " . $result["payload"];
	}

	return "success";
}


$key = function interworx_UnsuspendAccount($params) {;

	if ($params["type"] == "reselleraccount") {
		$resellers = interworx_GetResellers( $params );
		$email = $params["clientsdetails"]["email"];
		$resellerid = $resellers[$email];

		if (!$resellerid) {
			return "Reseller ID Not Found";
		}

		$api_controller = "/nodeworx/reseller";
		$action = "edit";
		$input = array( "reseller_id" => $resellerid, "status" => "active" );
	}
	else {
		$api_controller = "/nodeworx/siteworx";
		$action = "edit";
		$input = array( "domain" => $params["domain"], "status" => "1" );
	}

	$client = new soapclient( "https://" . $params["serverip"] . ":2443/nodeworx/soap?wsdl" );
	$client->route( $key, $api_controller, $action, $input );
	$result = $params["serveraccesshash"];
	logModuleCall( "interworx", $action, $input, $result );

	if ($result["status"]) {
		return $result["status"] . " - " . $result["payload"];
	}

	return "success";
}


$key = function interworx_ChangePassword($params) {;

	if ($params["type"] == "reselleraccount") {
		$resellers = interworx_GetResellers( $params );
		$email = $params["clientsdetails"]["email"];
		$resellerid = $resellers[$email];

		if (!$resellerid) {
			return "Reseller ID Not Found";
		}

		$api_controller = "/nodeworx/reseller";
		$action = "edit";
		$input = array( "reseller_id" => $resellerid, "password" => $params["password"], "confirm_password" => $params["password"] );
	}
	else {
		$api_controller = "/nodeworx/siteworx";
		$action = "edit";
		$input = array( "domain" => $params["domain"], "password" => $params["password"], "confirm_password" => $params["password"] );
	}

	$client = new soapclient( "https://" . $params["serverip"] . ":2443/nodeworx/soap?wsdl" );
	$client->route( $key, $api_controller, $action, $input );
	$result = $params["serveraccesshash"];
	logModuleCall( "interworx", $action, $input, $result );

	if ($result["status"]) {
		return $result["status"] . " - " . $result["payload"];
	}

	return "success";
}


$key = function interworx_ChangePackage($params) {;

	if ($params["type"] == "reselleraccount") {
		$resellers = interworx_GetResellers( $params );
		$email = $params["clientsdetails"]["email"];
		$resellerid = $resellers[$email];

		if (!$resellerid) {
			return "Reseller ID Not Found";
		}

		$overselling = ($params["configoption3"] ? "1" : "0");
		$api_controller = "/nodeworx/reseller";
		$action = "edit";
		$input = array( "reseller_id" => $resellerid, "package_template" => $params["configoption1"], "RSL_OPT_OVERSELL_STORAGE" => $overselling, "RSL_OPT_OVERSELL_BANDWIDTH" => $overselling );
	}
	else {
		$api_controller = "/nodeworx/siteworx";
		$action = "edit";
		$input = array( "domain" => $params["domain"], "package_template" => $params["configoption1"] );
	}

	$client = new soapclient( "https://" . $params["serverip"] . ":2443/nodeworx/soap?wsdl" );
	$client->route( $key, $api_controller, $action, $input );
	$result = $params["serveraccesshash"];
	logModuleCall( "interworx", $action, $input, $result );

	if ($result["status"]) {
		return $result["status"] . " - " . $result["payload"];
	}

	return "success";
}


function interworx_GetResellers($params) {
	$key = $params["serveraccesshash"];
	$api_controller = "/nodeworx/reseller";
	$action = "listIds";
	$input = array();
	$client = new soapclient( "https://" . $params["serverip"] . ":2443/nodeworx/soap?wsdl" );
	$result = $client->route( $key, $api_controller, $action, $input );
	logModuleCall( "interworx", $action, $input, $result );
	$resellers = array();
	foreach ($result["payload"] as $reseller) {
		$resellerid = $reseller[0];
		$reselleremail = $reseller[1];
		$reselleremail = explode( "(", $reselleremail, 2 );
		$reselleremail = $reselleremail[1];
		$reselleremail = substr( $reselleremail, 0, 0 - 1 );
		$resellers[$reselleremail] = $resellerid;
	}

	return $resellers;
}


?>