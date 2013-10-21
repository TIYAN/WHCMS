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

/**
 * WHMCS Plesk 10 Module
 *
 * @package     WHMCS
 * @subpackage  modules.servers.plesk10
 * @copyright   Copyright (c) WHMCS Limited 2005-2012
 * @license     http://www.whmcs.com/license/ WHMCS Eula
 * @link        http://www.whmcs.com/ WHMCS
 */
function plesk10_ConfigOptions() {
	$configarray = array( "Service Plan Name" => array( "Type" => "text", "Size" => "25" ), "Reseller Plan Name" => array( "Type" => "text", "Size" => "25" ) );
	return $configarray;
}


function plesk10_ClientArea($params) {
	global $_LANG;

	$domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
	$port = ($params["serveraccesshash"] ? $params["serveraccesshash"] : "8443");
	$secure = ($params["serversecure"] ? "https" : "http");
	$result = select_query( "tblhosting", "username,password", array( "server" => $params["serverid"], "userid" => $params["clientsdetails"]["userid"], "domainstatus" => "Active" ), "id", "ASC" );
	$data = mysql_fetch_array( $result );
	$code = "<form action=\"" . $secure . "://" . $domain . ":" . $port . "/login_up.php3\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"login_name\" value=\"" . $data["username"] . "\"><input type=\"hidden\" name=\"passwd\" value=\"" . decrypt( $data["password"] ) . "\"><input type=\"submit\" value=\"" . $_LANG["plesklogin"] . "\" class=\"button\"></form>";
	return $code;
}


function plesk10_AdminLink($params) {
	$domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
	$port = ($params["serveraccesshash"] ? $params["serveraccesshash"] : "8443");
	$secure = ($params["serversecure"] ? "https" : "http");
	$code = "<form action=\"" . $secure . "://" . $domain . ":" . $port . "/login_up.php3\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"login_name\" value=\"" . $params["serverusername"] . "\"><input type=\"hidden\" name=\"passwd\" value=\"" . $params["serverpassword"] . "\"><input type=\"submit\" value=\"Plesk\"></form>";
	return $code;
}


function plesk10_CreateAccount($params) {
	if ($params["type"] == "reselleraccount") {
		$packet = "<reseller>
<add>
<gen-info>";

		if ($params["clientsdetails"]["companyname"]) {
			$packet .= "<cname>" . $params["clientsdetails"]["companyname"] . "</cname>";
		}

		$packet .= "<pname>" . $params["clientsdetails"]["firstname"] . " " . $params["clientsdetails"]["lastname"] . "</pname>
<login>" . $params["username"] . "</login>
<passwd>" . $params["password"] . "</passwd>
<status>0</status>
<phone>" . $params["clientsdetails"]["phonenumber"] . "</phone>
<fax/>
<email>" . $params["clientsdetails"]["email"] . "</email>
<address>" . $params["clientsdetails"]["address1"] . "</address>
<city>" . $params["clientsdetails"]["city"] . "</city>
<state>" . $params["clientsdetails"]["state"] . "</state>
<pcode>" . $params["clientsdetails"]["postcode"] . "</pcode>
<country>" . $params["clientsdetails"]["country"] . "</country>
</gen-info>
<plan-name>" . $params["configoption2"] . "</plan-name>
</add>
</reseller>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["RESELLER"]["ADD"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["RESELLER"]["ADD"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["RESELLER"]["ADD"]["RESULT"]["ERRTEXT"];
		}

		return "success";
	}

	$sqlresult = select_query( "tblhosting", "username", array( "userid" => $params["clientsdetails"]["userid"] ) );

	while ($data = mysql_fetch_array( $sqlresult )) {
		$username = $data[0];
		$packet = "<customer>
<get>
<filter>
<login>" . $username . "</login>
</filter>
<dataset>
<gen_info/>
</dataset>
</get>
</customer>";
		$result = plesk10_connection( $params, $packet );
		$clientid = $result["PACKET"]["CUSTOMER"]["GET"]["RESULT"]["ID"];

		if ($clientid) {
			break;
		}
	}


	if (!$clientid) {
		$packet = "<customer>
<add>
<gen_info>";

		if ($params["clientsdetails"]["companyname"]) {
			$packet .= "<cname>" . $params["clientsdetails"]["companyname"] . "</cname>";
		}

		$packet .= "<pname>" . $params["clientsdetails"]["firstname"] . " " . $params["clientsdetails"]["lastname"] . "</pname>
<login>" . $params["username"] . "</login>
<passwd>" . $params["password"] . "</passwd>
<status>0</status>
<phone>" . $params["clientsdetails"]["phonenumber"] . "</phone>
<fax/>
<email>" . $params["clientsdetails"]["email"] . "</email>
<address>" . $params["clientsdetails"]["address1"] . "</address>
<city>" . $params["clientsdetails"]["city"] . "</city>
<state>" . $params["clientsdetails"]["state"] . "</state>
<pcode>" . $params["clientsdetails"]["postcode"] . "</pcode>
<country>" . $params["clientsdetails"]["country"] . "</country>";

		if ($resellerid) {
			$packet .= "<owner-id>" . $resellerid . "</owner-id>";
		}

		$packet .= "</gen_info>
</add>
</customer>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["CUSTOMER"]["ADD"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["CUSTOMER"]["ADD"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["CUSTOMER"]["ADD"]["RESULT"]["ERRTEXT"];
		}

		$clientid = $result["PACKET"]["CUSTOMER"]["ADD"]["RESULT"]["ID"];
	}

	$packet = "<ip><get/></ip>";
	$result = plesk10_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}

	$ipaddress = "";
	foreach ($result["PACKET"]["IP"]["GET"]["RESULT"]["ADDRESSES"] as $ipdata) {

		if ($ipdata["TYPE"] == "shared") {
			$ipaddress = $ipdata["IP_ADDRESS"];
			break;
		}
	}


	if (!$ipaddress) {
		$ipaddress = $params["serverip"];
	}

	$packet = "<webspace>
<add>
<gen_setup>
<name>" . $params["domain"] . "</name>
<owner-id>" . $clientid . "</owner-id>
<ip_address>" . $ipaddress . "</ip_address>
<htype>vrt_hst</htype>
<status>0</status>
</gen_setup>
<hosting>
<vrt_hst>
<property>
<name>ftp_login</name>
<value>" . $params["username"] . "</value>
</property>
<property>
<name>ftp_password</name>
<value>" . $params["password"] . "</value>
</property>
<ip_address>" . $ipaddress . "</ip_address>
</vrt_hst>
</hosting>
<prefs>
<www>true</www>
</prefs>
<plan-name>" . $params["configoption1"] . "</plan-name>
</add>
</webspace>";
	$result = plesk10_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
		return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
	}


	if ($result["PACKET"]["WEBSPACE"]["ADD"]["RESULT"]["STATUS"] != "ok") {
		return "Error Code: " . $result["PACKET"]["WEBSPACE"]["ADD"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["WEBSPACE"]["ADD"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function plesk10_SuspendAccount($params) {
	$suspendstatus = 20;

	if (( $params["serverusername"] != "root" && $params["serverusername"] != "admin" )) {
		$suspendstatus = 36;
	}


	if ($params["type"] == "reselleraccount") {
		$packet = "<reseller>
<set>
<filter>
<login>" . $params["username"] . "</login>
</filter>
<values>
<gen-info>
<status>" . $suspendstatus . "</status>
</gen-info>
</values>
</set>
</reseller>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["RESELLER"]["SET"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["RESELLER"]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["RESELLER"]["SET"]["RESULT"]["ERRTEXT"];
		}
	}
	else {
		$packet = "<webspace>
<set>
<filter>
<name>" . $params["domain"] . "</name>
</filter>
<values>
<gen_setup>
<status>" . $suspendstatus . "</status>
</gen_setup>
</values>
</set>
</webspace>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["WEBSPACE"]["SET"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["WEBSPACE"]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["WEBSPACE"]["SET"]["RESULT"]["ERRTEXT"];
		}
	}

	return "success";
}


function plesk10_UnsuspendAccount($params) {
	if ($params["type"] == "reselleraccount") {
		$packet = "<reseller>
<set>
<filter>
<login>" . $params["username"] . "</login>
</filter>
<values>
<gen-info>
<status>0</status>
</gen-info>
</values>
</set>
</reseller>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["RESELLER"]["SET"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["RESELLER"]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["RESELLER"]["SET"]["RESULT"]["ERRTEXT"];
		}
	}
	else {
		$packet = "<webspace>
<set>
<filter>
<name>" . $params["domain"] . "</name>
</filter>
<values>
<gen_setup>
<status>0</status>
</gen_setup>
</values>
</set>
</webspace>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["WEBSPACE"]["SET"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["WEBSPACE"]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["WEBSPACE"]["SET"]["RESULT"]["ERRTEXT"];
		}
	}

	return "success";
}


function plesk10_TerminateAccount($params) {
	if ($params["type"] == "reselleraccount") {
		$packet = "<reseller>
<del>
<filter>
<login>" . $params["username"] . "</login>
</filter>
</del>
</reseller>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["RESELLER"]["DEL"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["RESELLER"]["DEL"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["RESELLER"]["DEL"]["RESULT"]["ERRTEXT"];
		}
	}
	else {
		$packet = "<webspace>
<del>
<filter>
<name>" . $params["domain"] . "</name>
</filter>
</del>
</webspace>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["WEBSPACE"]["DEL"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["WEBSPACE"]["DEL"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["WEBSPACE"]["DEL"]["RESULT"]["ERRTEXT"];
		}
	}

	return "success";
}


function plesk10_ChangePassword($params) {
	if ($params["type"] == "reselleraccount") {
		$packet = "<reseller>
<set>
<filter>
<login>" . $params["username"] . "</login>
</filter>
<values>
<gen-info>
<passwd>" . $params["password"] . "</passwd>
</gen-info>
</values>
</set>
</reseller>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["RESELLER"]["SET"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["RESELLER"]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["RESELLER"]["SET"]["RESULT"]["ERRTEXT"];
		}
	}
	else {
		$packet = "<customer>
<set>
<filter>
<login>" . $params["username"] . "</login>
</filter>
<values>
<gen_info>
<passwd>" . $params["password"] . "</passwd>
</gen_info>
</values>
</set>
</customer>";
		$result = plesk10_connection( $params, $packet );
		$packet = "<webspace>
<get>
<filter>
<name>" . $params["domain"] . "</name>
</filter>
<dataset>
<hosting/>
</dataset>
</get>
</webspace>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["WEBSPACE"]["GET"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["WEBSPACE"]["GET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["WEBSPACE"]["GET"]["RESULT"]["ERRTEXT"];
		}

		$ipaddress = $result["PACKET"]["WEBSPACE"]["GET"]["RESULT"]["DATA"]["HOSTING"]["VRT_HST"]["IP_ADDRESS"];
		$packet = "<webspace>
<set>
<filter>
<name>" . $params["domain"] . "</name>
</filter>
<values>
<hosting>
<vrt_hst>
<property>
<name>ftp_password</name>
<value>" . $params["password"] . "</value>
</property>
<ip_address>" . $ipaddress . "</ip_address>
</vrt_hst>
</hosting>
</values>
</set>
</webspace>";
		$result = plesk10_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
			return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
		}


		if ($result["PACKET"]["WEBSPACE"]["SET"]["RESULT"]["STATUS"] != "ok") {
			return "Error Code: " . $result["PACKET"]["WEBSPACE"]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["WEBSPACE"]["SET"]["RESULT"]["ERRTEXT"];
		}
	}

	return "success";
}


function plesk10_ChangePackage($params) {
	$packet = "<service-plan>
<get>
<filter>
<name>" . $params["configoption1"] . "</name>
</filter>
</get>
</service-plan>";
	$result = plesk10_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
		return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
	}


	if ($result["PACKET"]["SERVICE-PLAN"]["GET"]["RESULT"]["STATUS"] != "ok") {
		return "Error Code: " . $result["PACKET"]["SERVICE-PLAN"]["GET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["SERVICE-PLAN"]["GET"]["RESULT"]["ERRTEXT"];
	}

	$guid = $result["PACKET"]["SERVICE-PLAN"]["GET"]["RESULT"]["GUID"];
	$packet = "<webspace>
<switch-subscription>
<filter>
<name>" . $params["domain"] . "</name>
</filter>
<plan-guid>" . $guid . "</plan-guid>
</switch-subscription>
</webspace>";
	$result = plesk10_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result["PACKET"]["SYSTEM"]["ERRCODE"]) {
		return "Error Code: " . $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
	}


	if ($result["PACKET"]["WEBSPACE"]["SWITCH-SUBSCRIPTION"]["RESULT"]["STATUS"] != "ok") {
		return "Error Code: " . $result["PACKET"]["WEBSPACE"]["SWITCH-SUBSCRIPTION"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["WEBSPACE"]["SWITCH-SUBSCRIPTION"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function plesk10_connection($params, $packet) {
	global $plesk10packetversion;

	if (!$plesk10packetversion) {
		$plesk10packetversion = "1.6.3.0";
	}

	$secure = ($params["serversecure"] ? "https" : "http");
	$hostname = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
	$port = ($params["serveraccesshash"] ? $params["serveraccesshash"] : "8443");
	$url = "" . $secure . "://" . $hostname . ":" . $port . "/enterprise/control/agent.php";
	$headers = array( "HTTP_AUTH_LOGIN: " . $params["serverusername"], "HTTP_AUTH_PASSWD: " . $params["serverpassword"], "Content-Type: text/xml" );
	$packet = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><packet version=\"" . $plesk10packetversion . "\">" . $packet . "</packet>";
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 300 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $packet );
	$retval = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$result["curlerror"] = "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch );
	}
	else {
		$result = XMLtoARRAY( $retval );
	}

	curl_close( $ch );
	logModuleCall( "plesk10", $params["action"], $packet, $retval, $result );
	return $result;
}


?>