<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 * */

function virtualmin_ConfigOptions() {
	$configarray = array( "Template Name" => array( "Type" => "text", "Size" => "30" ), "Plan Name" => array( "Type" => "text", "Size" => "30" ), "Dedicated IP" => array( "Type" => "yesno", "Description" => "Tick to auto assign next available dedicated IP" ) );
	return $configarray;
}


function virtualmin_ClientArea($params) {
	global $_LANG;

	$http = ($params["serversecure"] ? "https" : "http");
	$domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
	$code = "<form action=\"" . $http . "://" . $domain . "/session_login.cgi\" method=\"post\" target=\"_blank\">
<input type=\"hidden\" name=\"user\" value=\"" . $params["username"] . "\" />
<input type=\"hidden\" name=\"pass\" value=\"" . $params["password"] . "\" />
<input type=\"hidden\" name=\"notestingcookie\" value=\"1\" />
<input type=\"submit\" value=\"" . $_LANG["virtualminlogin"] . "\" class=\"button\" />
</form>";
	return $code;
}


function virtualmin_AdminLink($params) {
	$http = ($params["serversecure"] ? "https" : "http");
	$domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
	$code = "<form action=\"" . $http . "://" . $domain . "/session_login.cgi\" method=\"post\" target=\"_blank\">
<input type=\"hidden\" name=\"user\" value=\"" . $params["serverusername"] . "\" />
<input type=\"hidden\" name=\"pass\" value=\"" . $params["serverpassword"] . "\" />
<input type=\"hidden\" name=\"notestingcookie\" value=\"1\" />
<input type=\"submit\" value=\"Login to Control Panel\" class=\"button\" />
</form>";
	return $code;
}


function virtualmin_CreateAccount($params) {
	if ($params["type"] == "reselleraccount") {
		if (!$params["username"]) {
			$username = preg_replace( "/[^a-z0-9]/", "", strtolower( $params["clientsdetails"]["firstname"] . $params["clientsdetails"]["lastname"] . $params["serviceid"] ) );
			update_query( "tblhosting", array( "username" => $username ), array( "id" => $params["serviceid"] ) );
			$params["username"] = $username;
		}

		$postfields = array();
		$postfields["program"] = "create-reseller";
		$postfields["name"] = $params["username"];
		$postfields["pass"] = $params["password"];
		$postfields["email"] = $params["clientsdetails"]["email"];

		if ($params["configoption2"]) {
			$postfields["plan"] = $params["configoption2"];
		}

		$result = virtualmin_req( $params, $postfields );
	}
	else {
		$postfields = array();
		$postfields["program"] = "create-domain";
		$postfields["domain"] = $params["domain"];
		$postfields["user"] = $params["username"];
		$postfields["pass"] = $params["password"];
		$postfields["email"] = $params["clientsdetails"]["email"];

		if ($params["configoption1"]) {
			$postfields["template"] = $params["configoption1"];
		}


		if ($params["configoption2"]) {
			$postfields["plan"] = $params["configoption2"];
		}


		if ($params["configoption3"]) {
			$postfields["allocate-ip"] = "";
		}

		$postfields["features-from-plan"] = "";
		$result = virtualmin_req( $params, $postfields );
	}

	return $result;
}


function virtualmin_SuspendAccount($params) {
	if ($params["type"] == "reselleraccount") {
		$postfields = array();
		$postfields["program"] = "modify-reseller";
		$postfields["name"] = $params["username"];
		$postfields["pass"] = md5( rand( 10000, 99999999 ) . $params["domain"] );
		$postfields["lock"] = "1";
	}
	else {
		$postfields = array();
		$postfields["program"] = "disable-domain";
		$postfields["domain"] = $params["domain"];
	}

	$result = virtualmin_req( $params, $postfields );
	return $result;
}


function virtualmin_UnsuspendAccount($params) {
	if ($params["type"] == "reselleraccount") {
		$postfields = array();
		$postfields["program"] = "modify-reseller";
		$postfields["name"] = $params["username"];
		$postfields["pass"] = $params["password"];
		$postfields["lock"] = "0";
	}
	else {
		$postfields = array();
		$postfields["program"] = "enable-domain";
		$postfields["domain"] = $params["domain"];
	}

	$result = virtualmin_req( $params, $postfields );
	return $result;
}


function virtualmin_TerminateAccount($params) {
	if ($params["type"] == "reselleraccount") {
		$postfields = array();
		$postfields["program"] = "delete-reseller";
		$postfields["name"] = $params["username"];
	}
	else {
		$postfields = array();
		$postfields["program"] = "delete-domain";
		$postfields["domain"] = $params["domain"];
	}

	$result = virtualmin_req( $params, $postfields );
	return $result;
}


function virtualmin_ChangePassword($params) {
	$postfields = array();
	$postfields["program"] = "modify-domain";
	$postfields["domain"] = $params["domain"];
	$postfields["pass"] = $params["password"];
	$result = virtualmin_req( $params, $postfields );
	return $result;
}


function virtualmin_ChangePackage($params) {
	$postfields = array();
	$postfields["program"] = "modify-domain";
	$postfields["domain"] = $params["domain"];
	$postfields["plan-features"] = "";

	if ($params["configoption1"]) {
		$postfields["template"] = $params["configoption1"];
	}


	if ($params["configoption2"]) {
		$postfields["apply-plan"] = $params["configoption2"];
	}

	$result = virtualmin_req( $params, $postfields );
	return $result;
}


function virtualmin_UsageUpdate($params) {
	$postfields = array();
	$postfields["program"] = "list-domains";
	$postfields["multiline"] = "";
	$result = virtualmin_req( $params, $postfields, true );
	$dataarray = explode( "
", $result );

	$arraydata = array();
	foreach ($dataarray as $line) {

		if (substr( $line, 0, 4 ) == "    ") {
			$line = trim( $line );
			$line = explode( ":", $line, 2 );
			$arraydata[trim( $line[0] )] = trim( $line[1] );
			continue;
		}

		$domainsarray[$domain] = $arraydata;
		$domain = trim( $line );
		$arraydata = array();
	}

	foreach ($domainsarray as $domain => $values) {
		$diskusage = $values["Server byte quota used"] / 1048576;
		$disklimit = $values["Server block quota"] / 1024;
		$bwlimit = $values["Bandwidth byte limit"] / 1048576;
		$bwused = $values["Bandwidth byte usage"] / 1048576;

		if ($domain) {
			update_query( "tblhosting", array( "diskusage" => $diskusage, "disklimit" => $disklimit, "bwusage" => $bwused, "bwlimit" => $bwlimit, "lastupdate" => "now()" ), array( "domain" => $domain, "server" => $params["serverid"] ) );
			continue;
		}
	}

}


function virtualmin_req($params, $postfields, $rawdata = false) {
	$http = ($params["serversecure"] ? "https" : "http");
	$domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
	$url = "" . $http . "://" . $domain . "/virtual-server/remote.cgi?" . $fieldstring;
	$fieldstring = "";
	foreach ($postfields as $k => $v) {
		$fieldstring .= "" . $k . "=" . urlencode( $v ) . "&";
	}

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $fieldstring );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_USERPWD, $params["serverusername"] . ":" . $params["serverpassword"] );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
	$data = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$data = "Curl Error: " . curl_errno( $ch ) . " - " . curl_error( $ch );
	}

	curl_close( $ch );
	logModuleCall( "virtualmin", $postfields["program"], $postfields, $data );

	if (strpos( $data, "Unauthorized" ) == true) {
		return "Server Login Invalid";
	}


	if ($rawdata) {
		return $data;
	}

	$exitstatuspos = strpos( $data, "Exit status:" );
	$exitstatus = trim( substr( $data, $exitstatuspos + 12 ) );

	if ($exitstatus == "0") {
		$result = "success";
	}
	else {
		$dataarray = explode( "
", $data );

		$result = $dataarray[0];
	}

	return $result;
}


?>