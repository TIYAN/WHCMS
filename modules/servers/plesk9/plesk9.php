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

function plesk9_ConfigOptions() {
	$configarray = array( "Client Template Name" => array( "Type" => "text", "Size" => "25" ), "Domain Template Name" => array( "Type" => "text", "Size" => "25" ), "Reseller Template Name" => array( "Type" => "text", "Size" => "25" ) );
	return $configarray;
}


function plesk9_ClientArea($params) {
	global $_LANG;

	$domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
	$port = ($params["serveraccesshash"] ? $params["serveraccesshash"] : "8443");
	$secure = ($params["serversecure"] ? "https" : "http");
	$code = "<form action=\"" . $secure . "://" . $domain . ":" . $port . "/login_up.php3\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"login_name\" value=\"" . $params["username"] . "\"><input type=\"hidden\" name=\"passwd\" value=\"" . $params["password"] . "\"><input type=\"submit\" value=\"" . $_LANG["plesklogin"] . "\" class=\"button\"></form>";
	return $code;
}


function plesk9_AdminLink($params) {
	if ($params["serverhostname"]) {
		$domain = $params["serverhostname"];
	}
	else {
		$domain = $params["serverip"];
	}

	$port = ($params["serveraccesshash"] ? $params["serveraccesshash"] : "8443");
	$secure = ($params["serversecure"] ? "https" : "http");
	$code = "<form action=\"" . $secure . "://" . $domain . ":" . $port . "/login_up.php3\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"login_name\" value=\"" . $params["serverusername"] . "\"><input type=\"hidden\" name=\"passwd\" value=\"" . $params["serverpassword"] . "\"><input type=\"submit\" value=\"Plesk\"></form>";
	return $code;
}


function plesk9_CreateAccount($params) {
	global $clientid;

	if ($params["type"] == "reselleraccount") {
		$packet = "
<reseller>
<add>
<gen-info>
<cname>" . $params["clientsdetails"]["companyname"] . "</cname>
<pname>" . $params["clientsdetails"]["firstname"] . " " . $params["clientsdetails"]["lastname"] . " " . $params["serviceid"] . "</pname>
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
<template-name>" . $params["configoption3"] . "</template-name>
</add>
</reseller>
";
		$result = plesk9_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["RESELLER"]["ADD"]["RESULT"]["STATUS"] != "ok") {
			return $result["PACKET"]["RESELLER"]["ADD"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["RESELLER"]["ADD"]["RESULT"]["ERRTEXT"];
		}

		$resellerid = $clientid = $result["PACKET"]["RESELLER"]["ADD"]["RESULT"]["ID"];
		$packet = "
<reseller>
	<get>
		<filter>
			<id>" . $resellerid . "</id>
		</filter>
		<dataset>
			<ippool/>
		</dataset>
	</get>
</reseller>
";
		$result = plesk9_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["RESELLER"]["IPPOOL_ADD_IP"]["RESULT"]["STATUS"] == "error") {
			return $result["PACKET"]["RESELLER"]["IPPOOL_ADD_IP"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["RESELLER"]["IPPOOL_ADD_IP"]["RESULT"]["ERRTEXT"];
		}

		$ipaddress = $result["PACKET"]["RESELLER"]["GET"]["RESULT"]["DATA"]["IPPOOL"]["IP-ADDRESS"];

		if (!$ipaddress) {
			$ipaddress = $result["PACKET"]["RESELLER"]["GET"]["RESULT"]["DATA"]["IPPOOL"]["IP"]["IP-ADDRESS"];
		}
	}
	else {
		$packet = "<client>
<add>
<gen_info>";

		if ($params["clientsdetails"]["companyname"]) {
			$packet .= "<cname>" . $params["clientsdetails"]["companyname"] . "</cname>";
		}

		$packet .= "<pname>" . $params["clientsdetails"]["firstname"] . " " . $params["clientsdetails"]["lastname"] . " " . $params["serviceid"] . "</pname>
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
<template-name>" . $params["configoption1"] . "</template-name>
</add>
</client>";
		$result = plesk9_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result[PACKET][SYSTEM][ERRCODE]) {
			return $result[PACKET][SYSTEM][ERRCODE] . " - " . $result[PACKET][SYSTEM][ERRTEXT];
		}

		$clientid = $result["PACKET"]["CLIENT"]["ADD"]["RESULT"]["ID"];

		if (strlen( $clientid ) == 0) {
			return $result["PACKET"]["CLIENT"]["ADD"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["CLIENT"]["ADD"]["RESULT"]["ERRTEXT"];
		}

		$packet = "
<client>
	<get>
		<filter>
			<id>" . $clientid . "</id>
		</filter>
		<dataset>
			<ippool/>
		</dataset>
	</get>
</client>
";
		$result = plesk9_connection( $params, $packet );

		if ($result["curlerror"]) {
			return $result["curlerror"];
		}


		if ($result["PACKET"]["CLIENT"]["IPPOOL_ADD_IP"]["RESULT"]["STATUS"] == "error") {
			return $result["PACKET"]["CLIENT"]["IPPOOL_ADD_IP"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["CLIENT"]["IPPOOL_ADD_IP"]["RESULT"]["ERRTEXT"];
		}

		$ipaddress = $result["PACKET"]["CLIENT"]["GET"]["RESULT"]["DATA"]["IPPOOL"]["IP-ADDRESS"];

		if (!$ipaddress) {
			$ipaddress = $result["PACKET"]["CLIENT"]["GET"]["RESULT"]["DATA"]["IPPOOL"]["IP"]["IP-ADDRESS"];
		}
	}

	$packet = "
<domain>
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
		<user>
			<enabled>true</enabled>
			<password>" . $params["password"] . "</password>
		</user>
		<template-name>" . $params["configoption2"] . "</template-name>
	</add>
</domain>
";
	$result = plesk9_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result["PACKET"]["SYSTEM"]["STATUS"] == "error") {
		return $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
	}


	if ($result["PACKET"]["DOMAIN"]["ADD"]["RESULT"]["STATUS"] == "error") {
		return $result["PACKET"]["DOMAIN"]["ADD"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["DOMAIN"]["ADD"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function plesk9_TerminateAccount($params) {
	if ($params["type"] == "reselleraccount") {
		$packet = "<reseller>
<del>
<filter>
<login>" . $params["username"] . "</login>
</filter>
</del>
</reseller>";
		$type = "RESELLER";
	}
	else {
		$packet = "<client>
<del>
<filter>
<login>" . $params["username"] . "</login>
</filter>
</del>
</client>";
		$type = "CLIENT";
	}

	$result = plesk9_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result[PACKET][SYSTEM][ERRCODE]) {
		return $result[PACKET][SYSTEM][ERRCODE] . " - " . $result[PACKET][SYSTEM][ERRTEXT];
	}


	if ($result["PACKET"][$type]["DEL"]["RESULT"]["STATUS"] == "error") {
		return $result["PACKET"][$type]["DEL"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"][$type]["DEL"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function plesk9_SuspendAccount($params) {
	if ($params["type"] == "reselleraccount") {
		$packet = "<reseller>
<set>
<filter>
<login>" . $params["username"] . "</login>
</filter>
<values>
<gen-info>
<status>16</status>
</gen-info>
</values>
</set>
</reseller>";
		$type = "RESELLER";
	}
	else {
		$packet = "<client>
<set>
<filter>
<login>" . $params["username"] . "</login>
</filter>
<values>
<gen_info>
<status>16</status>
</gen_info>
</values>
</set>
</client>";
		$type = "CLIENT";
	}

	$result = plesk9_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result[PACKET][SYSTEM][ERRCODE]) {
		return $result[PACKET][SYSTEM][ERRCODE] . " - " . $result[PACKET][SYSTEM][ERRTEXT];
	}


	if ($result["PACKET"][$type]["SET"]["RESULT"]["STATUS"] != "ok") {
		return $result["PACKET"][$type]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"][$type]["SET"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function plesk9_UnsuspendAccount($params) {
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
		$type = "RESELLER";
	}
	else {
		$packet = "<client>
<set>
<filter>
<login>" . $params["username"] . "</login>
</filter>
<values>
<gen_info>
<status>0</status>
</gen_info>
</values>
</set>
</client>";
		$type = "CLIENT";
	}

	$result = plesk9_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result[PACKET][SYSTEM][ERRCODE]) {
		return $result[PACKET][SYSTEM][ERRCODE] . " - " . $result[PACKET][SYSTEM][ERRTEXT];
	}


	if ($result["PACKET"][$type]["SET"]["RESULT"]["STATUS"] == "error") {
		return $result["PACKET"][$type]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"][$type]["SET"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function plesk9_ChangePassword($params) {
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
		$type = "RESELLER";
	}
	else {
		$packet = "<domain>
<set>
<filter>
<domain-name>" . $params["domain"] . "</domain-name>
</filter>
<values>
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
</vrt_hst>
</hosting>
</values>
</set>
</domain>";
		$result = plesk9_connection( $params, $packet );
		$packet = "<client>
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
</client>";
		$type = "CLIENT";
	}

	$result = plesk9_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result[PACKET][SYSTEM][ERRCODE]) {
		return $result[PACKET][SYSTEM][ERRCODE] . " - " . $result[PACKET][SYSTEM][ERRTEXT];
	}


	if ($result["PACKET"][$type]["SET"]["RESULT"]["STATUS"] == "error") {
		return $result["PACKET"][$type]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"][$type]["SET"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function plesk9_UsageUpdate($params) {
	$packet = "<client>
<get>
<filter/>
<dataset>
<gen_info/>
<stat/>
<limits/>
</dataset>
</get>
</client>";
	$result = plesk9_connection( $params, $packet );
	foreach ($result["PACKET"]["CLIENT"]["GET"] as $client) {
		foreach ($client as $k => $v) {

			if (substr( $k, 0, 4 ) == "DATA") {
				foreach ($v as $k => $v) {

					if (substr( $k, 0, 4 ) == "GEN_") {
						$username = $v["LOGIN"];
						continue;
					}


					if (substr( $k, 0, 4 ) == "STAT") {
						$diskused = $v["DISK_SPACE"];
						$bwused = $v["TRAFFIC"];
						continue;
					}


					if (substr( $k, 0, 4 ) == "LIMI") {
						foreach ($v as $k1 => $v1) {

							if ($v1["NAME"] == "disk_space") {
								$disklimit = $v1["VALUE"];
							}


							if ($v1["NAME"] == "max_traffic") {
								$bwlimit = $v1["VALUE"];
								continue;
							}
						}

						continue;
					}
				}

				$diskused = (is_null( $diskused ) ? 0 : $diskused / 1024 / 1024);
				$bwused = (is_null( $bwused ) ? 0 : $bwused / 1024 / 1024);
				$disklimit = (is_null( $disklimit ) ? 0 : $disklimit / 1024 / 1024);
				$bwlimit = (is_null( $diskused ) ? 0 : $bwlimit / 1024 / 1024);
				update_query( "tblhosting", array( "diskusage" => $diskused, "disklimit" => $disklimit, "bwusage" => $bwused, "bwlimit" => $bwlimit, "lastupdate" => "now()" ), array( "username" => $username, "server" => $params["serverid"] ) );
				continue;
			}
		}
	}

}


function plesk9_connection($params, $packet) {
	global $clientid;
	global $pleskpacketversion;

	if (!$pleskpacketversion) {
		$pleskpacketversion = "1.5.2.1";
	}

	$secure = ($params["serversecure"] ? "https" : "http");
	$port = ($params["serveraccesshash"] ? $params["serveraccesshash"] : "8443");
	$url = "" . $secure . "://" . $params["serverip"] . ( ":" . $port . "/enterprise/control/agent.php" );
	$headers = array( "HTTP_AUTH_LOGIN: " . $params["serverusername"], "HTTP_AUTH_PASSWD: " . $params["serverpassword"], "Content-Type: text/xml" );
	$packet = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><packet version=\"" . $pleskpacketversion . "\">" . $packet . "</packet>";
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
	logModuleCall( "plesk9", $params["action"], $packet, $retval, $result );
	return $result;
}


?>