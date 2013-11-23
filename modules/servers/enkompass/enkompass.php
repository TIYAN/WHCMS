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

function enkompass_ConfigOptions() {
	$configarray = array( "Plan Name" => array( "Type" => "text", "Size" => "25", "Description" => "Plan Name from Enkompass" ), "Custom Settings" => array( "Type" => "yesno", "Description" => "Tick to ignore plan and use limits specified below" ), "Disk Quota" => array( "Type" => "text", "Size" => "10", "Description" => "MB (0=Unlimited)" ), "Bandwidth Limit" => array( "Type" => "text", "Size" => "10", "Description" => "MB (0=Unlimited)" ), "IP Address" => array( "Type" => "dropdown", "Options" => "Shared (Default),Dedicated,All IPs in Web Pool" ), "CGI Access" => array( "Type" => "yesno" ), "Max Addon Domains" => array( "Type" => "text", "Size" => "10", "Description" => "" ), "Max FTP Users" => array( "Type" => "text", "Size" => "10", "Description" => "" ), "Max Email Accounts" => array( "Type" => "text", "Size" => "10", "Description" => "" ), "Max Parked Domains" => array( "Type" => "text", "Size" => "10", "Description" => "" ), "Max Subdomains" => array( "Type" => "text", "Size" => "10", "Description" => "" ), "Max SQL Databases" => array( "Type" => "text", "Size" => "10", "Description" => "" ), "Max Mailing Lists" => array( "Type" => "text", "Size" => "10", "Description" => "" ), "Feature List" => array( "Type" => "text", "Size" => "15", "Description" => "" ), "Enkompass Theme" => array( "Type" => "text", "Size" => "15", "Description" => "" ), "Enkompass Skin" => array( "Type" => "text", "Size" => "15", "Description" => "" ), "Mail Server Hostname" => array( "Type" => "text", "Size" => "25" ), "MSSQL Server Hostname" => array( "Type" => "text", "Size" => "25" ), "MySQL Server Hostname" => array( "Type" => "text", "Size" => "25" ), "Reseller ACL List" => array( "Type" => "text", "Size" => "15" ), "Limit Reseller by Resources" => array( "Type" => "yesno", "Description" => "Uses Disk Quota And Bandwidth Limit Specified Above" ), "Limit Reseller by Accounts" => array( "Type" => "text", "Size" => "5", "Description" => "Enter Numeric Limit" ), "Allow Disk Space Overselling" => array( "Type" => "yesno" ), "Allow Bandwidth Overselling" => array( "Type" => "yesno" ) );
	return $configarray;
}


function enkompass_CreateAccount($params) {
	$dediip = false;

	if ($params["configoption5"] == "Dedicated") {
		$dediip = true;
	}


	if ($params["configoptions"]["DedicatedIP"]) {
		$dediip = true;
	}


	if ($params["configoption5"] == "All IPs in Web Pool") {
		$ip = "0.0.0.0";
	}
	else {
		$action = "listips";
		$postfields = array();
		$response = enkompass_req( $params, $action, $postfields );
		$primaryip = $ip = "";
		foreach ($response as $result) {
			$active = $result["ACTIVE"];
			$configured = $result["CONFIGURED"];
			$ip = $result["IP"];
			$used = $result["USED"];

			if (( ( $active && $configured ) && !$used )) {
				break;
				continue;
			}
		}


		if (!$ip) {
			if ($dediip) {
				return "No Available Dedicated IPs to Create Account With -OR- Using Old Enkompass Version where LISTIPS doesn't work and need to upgrade";
			}

			return "No Available IPs to Create Account With -OR- Using Old Enkompass Version where LISTIPS doesn't work and need to upgrade";
		}
	}


	if ($params["configoption3"] == "0") {
		$params["configoption3"] = "unlimited";
	}


	if ($params["configoption4"] == "0") {
		$params["configoption4"] = "unlimited";
	}

	$action = "createacct";
	$postfields = array();
	$postfields["contactemail"] = $params["clientsdetails"]["email"];
	$postfields["customip"] = $ip;
	$postfields["domain"] = $params["domain"];
	$postfields["username"] = $params["username"];
	$postfields["password"] = $params["password"];
	$postfields["plan"] = $params["configoption1"];

	if ($params["configoption2"]) {
		$postfields["quota"] = $params["configoption3"];
		$postfields["bwlimit"] = $params["configoption4"];
		$postfields["ip"] = ($params["configoption5"] ? "1" : "0");
		$postfields["cgi"] = ($params["configoption6"] ? "1" : "0");
		$postfields["maxaddon"] = $params["configoption7"];
		$postfields["maxftp"] = $params["configoption8"];
		$postfields["maxlst"] = $params["configoption13"];
		$postfields["maxpark"] = $params["configoption10"];
		$postfields["maxpop"] = $params["configoption9"];
		$postfields["maxsql"] = $params["configoption12"];
		$postfields["maxsub"] = $params["configoption11"];
		$postfields["featurelist"] = $params["configoption14"];
		$postfields["cptheme"] = $params["configoption15"];
		$postfields["cpmod"] = $params["configoption16"];
		$postfields["hasshell"] = ($params["configoption17"] ? "1" : "0");
	}


	if ($params["configoption17"]) {
		$postfields["server-mail"] = $params["configoption17"];
	}


	if ($params["configoption18"]) {
		$postfields["server-mssql"] = $params["configoption18"];
	}


	if ($params["configoption19"]) {
		$postfields["server-mysql"] = $params["configoption19"];
	}


	if ($params["type"] == "reselleraccount") {
		$postfields["reseller"] = "1";
	}

	$response = enkompass_req( $params, $action, $postfields, "0" );

	if ($response["STATUS"] != 1) {
		$error = $response["STATUSMSG"];

		if ($response["STATUS-DETAILS"]) {
			$error .= " - " . $response["STATUS-DETAILS"];
		}

		return $error;
	}


	if ($params["type"] == "reselleraccount") {
		$action = "setresellerlimits";
		$postfields = array();
		$postfields["user"] = $params["username"];

		if ($params["configoption21"]) {
			$postfields["enable_resource_limits"] = "1";
			$postfields["diskspace_limit"] = $params["configoption3"];
			$postfields["bandwidth_limit"] = $params["configoption4"];
		}
		else {
			$postfields["enable_resource_limits"] = "0";
		}


		if ($params["configoption22"]) {
			$postfields["enable_account_limit"] = "1";
			$postfields["account_limit"] = $params["configoption22"];
		}
		else {
			$postfields["enable_account_limit"] = "0";
		}

		$postfields["enable_overselling_diskspace"] = ($params["configoption23"] ? "1" : "0");
		$postfields["enable_overselling_bandwidth"] = ($params["configoption24"] ? "1" : "0");
		$response = enkompass_req( $params, $action, $postfields );

		if ($params["configoption20"]) {
			$action = "setacls";
			$postfields = array();
			$postfields["reseller"] = $params["username"];
			$postfields["acllist"] = $params["configoption20"];
			$response = enkompass_req( $params, $action, $postfields );
		}
	}

	return "success";
}


function enkompass_SuspendAccount($params) {
	$action = "suspendacct";
	$postfields = array();
	$postfields["user"] = $params["username"];
	$postfields["reason"] = $params["suspendreason"];
	$postfields["suspend-sub-accounts"] = "1";
	$response = enkompass_req( $params, $action, $postfields );

	if ($response["STATUS"] != 1) {
		$error = $response["STATUSMSG"];

		if ($response["STATUS-DETAILS"]) {
			$error .= " - " . $response["STATUS-DETAILS"];
		}

		return $error;
	}

	return "success";
}


function enkompass_UnsuspendAccount($params) {
	if ($params["type"] == "reselleraccount") {
		$action = "unsuspendreseller";
		$postfields = array();
		$postfields["user"] = $params["username"];
		$postfields["all"] = "1";
		$response = enkompass_req( $params, $action, $postfields );
	}
	else {
		$action = "unsuspendacct";
		$postfields = array();
		$postfields["user"] = $params["username"];
		$postfields["unsuspend-sub-accounts"] = "1";
		$response = enkompass_req( $params, $action, $postfields );
	}


	if ($response["STATUS"] != 1) {
		$error = $response["STATUSMSG"];

		if ($response["STATUS-DETAILS"]) {
			$error .= " - " . $response["STATUS-DETAILS"];
		}

		return $error;
	}

	return "success";
}


function enkompass_ChangePackage($params) {
	if ($params["type"] == "reselleraccount") {
		$action = "setresellerlimits";
		$postfields = array();
		$postfields["user"] = $params["username"];

		if ($params["configoption21"]) {
			$postfields["enable_resource_limits"] = "1";
			$postfields["diskspace_limit"] = $params["configoption3"];
			$postfields["bandwidth_limit"] = $params["configoption4"];
		}
		else {
			$postfields["enable_resource_limits"] = "0";
		}


		if ($params["configoption22"]) {
			$postfields["enable_account_limit"] = "1";
			$postfields["account_limit"] = $params["configoption22"];
		}
		else {
			$postfields["enable_account_limit"] = "0";
		}

		$postfields["enable_overselling_diskspace"] = ($params["configoption23"] ? "1" : "0");
		$postfields["enable_overselling_bandwidth"] = ($params["configoption24"] ? "1" : "0");
		$response = enkompass_req( $params, $action, $postfields );
	}
	else {
		$action = "changepackage";
		$postfields = array();
		$postfields["user"] = $params["username"];
		$postfields["pkg"] = $params["configoption1"];
		$response = enkompass_req( $params, $action, $postfields );
	}


	if ($response["STATUS"] != 1) {
		$error = $response["STATUSMSG"];

		if ($response["STATUS-DETAILS"]) {
			$error .= " - " . $response["STATUS-DETAILS"];
		}

		return $error;
	}

	return "success";
}


function enkompass_ChangePassword($params) {
	$action = "passwd";
	$postfields = array();
	$postfields["user"] = $params["username"];
	$postfields["pass"] = $params["password"];
	$response = enkompass_req( $params, $action, $postfields, "0" );

	if ($response["STATUS"] != 1) {
		$error = $response["STATUSMSG"];

		if ($response["STATUS-DETAILS"]) {
			$error .= " - " . $response["STATUS-DETAILS"];
		}

		return $error;
	}

	return "success";
}


function enkompass_TerminateAccount($params) {
	if ($params["type"] == "reselleraccount") {
		$action = "terminatereseller";
		$postfields = array();
		$postfields["reseller"] = $params["username"];
		$response = enkompass_req( $params, $action, $postfields );
	}
	else {
		$action = "removeacct";
		$postfields = array();
		$postfields["user"] = $params["username"];
		$response = enkompass_req( $params, $action, $postfields );
	}


	if ($response["STATUS"] != 1) {
		$error = $response["STATUSMSG"];

		if ($response["STATUS-DETAILS"]) {
			$error .= " - " . $response["STATUS-DETAILS"];
		}

		return $error;
	}

	return "success";
}


function enkompass_UsageUpdate($params) {
	$action = "listaccts";
	$postfields = array();
	$postfields["showusage"] = "1";
	$response = enkompass_req( $params, $action, $postfields );
	foreach ($response as $acct) {
		$domain = $acct["DOMAIN"];
		$disklimit = $acct["DISKLIMIT"];
		$diskused = $acct["DISKUSED"];
		$bwlimit = $acct["BANDWIDTHLIMIT"];
		$bwused = $acct["BANDWIDTHUSED"];
		update_query( "tblhosting", array( "diskusage" => $diskused, "disklimit" => $disklimit, "bwusage" => $bwused, "bwlimit" => $bwlimit, "lastupdate" => "now()" ), array( "domain" => $domain, "server" => $params["serverid"] ) );
	}

}


function enkompass_ClientArea($params) {
	global $_LANG;

	if ($params["serversecure"]) {
		$http = "https";
		$cpanelport = "2083";
		$whmport = "2087";
		$mailport = "2096";
	}
	else {
		$http = "http";
		$cpanelport = "2082";
		$whmport = "2086";
		$mailport = "2095";
	}


	if ($params["serverhostname"]) {
		$domain = $params["serverhostname"];
	}
	else {
		$domain = $params["serverip"];
	}

	$code = "<form action=\"" . $http . "://" . $domain . ":" . $cpanelport . "/frontend/login/login.aspx\" method=\"post\" target=\"_blank\">
		<input type=\"hidden\" name=\"Login1$UserName\" value=\"" . $params["username"] . "\" />
		<input type=\"hidden\" name=\"Login1$Password\" value=\"" . $params["password"] . "\" />
		<input type=\"submit\" value=\"" . $_LANG["enkompasslogin"] . "\" class=\"button\" />
		</form>";
	return $code;
}


function enkompass_AdminLink($params) {
	$url = ($params["serversecure"] ? "https" : "http");
	$url .= "://" . $params["serverip"] . ":";
	$url .= ($params["serversecure"] ? "2087" : "2086");
	return "<form method=\"post\" action=\"" . $url . "/\" target=\"_blank\"><input type=\"submit\" value=\"Go To Enkompass Panel\" /></form>";
}


function enkompass_req($params, $action, $postfields, $version = "1") {
	$url = ($params["serversecure"] ? "https" : "http");
	$url .= "://" . $params["serverip"] . ":";
	$url .= ($params["serversecure"] ? "2087" : "2086");
	$url .= "/api/xml-api/xml-api.asmx/" . $action . "?";
	$postfields["api.version"] = $version;
	$postfields["request.user"] = $params["serverusername"];
	$postfields["request.hash"] = $params["serveraccesshash"];
	foreach ($postfields as $k => $v) {
		$url .= $k . "=" . urlencode( $v ) . "&";
	}

	$data = curlCall( $url, "" );
	$xml = XMLtoArray( $data );
	logModuleCall( "enkompass", $action, $postfields, $data, $xml );

	if (is_array( $xml["LISTIPS"] )) {
		return $xml["LISTIPS"];
	}


	if (is_array( $xml[strtoupper( $action )]["RESULT"] )) {
		return $xml[strtoupper( $action )]["RESULT"];
	}


	if (is_array( $xml[strtoupper( $action )][strtoupper( $action ) . "1"] )) {
		return $xml[strtoupper( $action )][strtoupper( $action ) . "1"];
	}


	if (is_array( $xml["LISTACCTS"] )) {
		return $xml["LISTACCTS"];
	}

	return array( "STATUS" => "0", "STATUSMSG" => "Error: " . htmlentities( $data ) );
}


?>