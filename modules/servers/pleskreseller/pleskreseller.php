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

function pleskreseller_ConfigOptions() {
	$configarray = array( "Domain Template Name" => array( "Type" => "text", "Size" => "25" ), "IP Address" => array( "Type" => "text", "Size" => "20" ), "Physical hosting management" => array( "Type" => "yesno", "Description" => "Webspace, bandwidth etc" ), "Manage FTP password" => array( "Type" => "yesno", "Description" => "Changing of FTP password" ), "Management of SSH access to server" => array( "Type" => "yesno", "Description" => "Full SSH access" ), "Management of chrooted SSH access to server" => array( "Type" => "yesno", "Description" => "Chrooted SSH access" ), "Hard disk quota assignment" => array( "Type" => "yesno", "Description" => "Hard disk quota" ), "Subdomains management" => array( "Type" => "yesno", "Description" => "Management of subdomains" ), "Domain aliases management" => array( "Type" => "yesno", "Description" => "Management of domain aliases" ), "Log rotation management" => array( "Type" => "yesno", "Description" => "Management of log rotation" ), "Anonymous FTP management" => array( "Type" => "yesno", "Description" => "Management of anonymous FTP" ), "Scheduler management" => array( "Type" => "yesno", "Description" => "Management of scheduled tasks" ), "DNS zone management" => array( "Type" => "yesno", "Description" => "Management of DNS records" ), "Java applications management" => array( "Type" => "yesno", "Description" => "Management of Tomcat apps" ), "Web statistics management" => array( "Type" => "yesno", "Description" => "Management of web statistics" ), "Mailing lists management" => array( "Type" => "yesno", "Description" => "Management of mailing lists" ), "Spam filter management" => array( "Type" => "yesno", "Description" => "Management of spam filter" ), "Antivirus management" => array( "Type" => "yesno", "Description" => "Management of anti virus" ), "Allow local backups" => array( "Type" => "yesno", "Description" => "Local backups" ), "Allow FTP backups" => array( "Type" => "yesno", "Description" => "FTP backups" ), "Ability to use Sitebuilder" => array( "Type" => "yesno", "Description" => "Access to Sitebuilder admin" ), "Home page management" => array( "Type" => "yesno", "Description" => "Management of Plesk home page" ), "Allow multiple sessions" => array( "Type" => "yesno", "Description" => "Multiple login sessions to Plesk" ) );
	return $configarray;
}


function pleskreseller_ClientArea($params) {
	global $_LANG;

	if ($params["serverhostname"]) {
		$domain = $params["serverhostname"];
	}
	else {
		$domain = $params["serverip"];
	}

	$port = ($params["serveraccesshash"] ? $params["serveraccesshash"] : "8443");
	$secure = ($params["serversecure"] ? "https" : "http");
	$code = "<form action=\"" . $secure . "://" . $domain . ":" . $port . "/login_up.php3\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"login_name\" value=\"" . $params["domain"] . "\"><input type=\"hidden\" name=\"passwd\" value=\"" . $params["password"] . "\"><input type=\"submit\" value=\"" . $_LANG["plesklogin"] . "\" class=\"button\"></form>";
	return $code;
}


function pleskreseller_AdminLink($params) {
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


function pleskreseller_CreateAccount($params) {
	global $clientid;

	$ipaddress = $params["configoption2"];
	$params["configoption3"] = ($params["configoption3"] ? "true" : "false");
	$params["configoption4"] = ($params["configoption4"] ? "true" : "false");
	$params["configoption5"] = ($params["configoption5"] ? "true" : "false");
	$params["configoption6"] = ($params["configoption6"] ? "true" : "false");
	$params["configoption7"] = ($params["configoption7"] ? "true" : "false");
	$params["configoption8"] = ($params["configoption8"] ? "true" : "false");
	$params["configoption9"] = ($params["configoption9"] ? "true" : "false");
	$params["configoption10"] = ($params["configoption10"] ? "true" : "false");
	$params["configoption11"] = ($params["configoption11"] ? "true" : "false");
	$params["configoption12"] = ($params["configoption12"] ? "true" : "false");
	$params["configoption13"] = ($params["configoption13"] ? "true" : "false");
	$params["configoption14"] = ($params["configoption14"] ? "true" : "false");
	$params["configoption15"] = ($params["configoption15"] ? "true" : "false");
	$params["configoption16"] = ($params["configoption16"] ? "true" : "false");
	$params["configoption17"] = ($params["configoption17"] ? "true" : "false");
	$params["configoption18"] = ($params["configoption18"] ? "true" : "false");
	$params["configoption19"] = ($params["configoption19"] ? "true" : "false");
	$params["configoption20"] = ($params["configoption20"] ? "true" : "false");
	$params["configoption21"] = ($params["configoption21"] ? "true" : "false");
	$params["configoption22"] = ($params["configoption22"] ? "true" : "false");
	$params["configoption23"] = ($params["configoption23"] ? "true" : "false");
	$clientsdetails = $params["clientsdetails"];
	$packet = "<domain>
	<add>
		<gen_setup>
			<name>" . $params["domain"] . "</name>
			<ip_address>" . $ipaddress . "</ip_address>
			<htype>vrt_hst</htype>
			<status>0</status>
		</gen_setup>
		<hosting>
			<vrt_hst>
				<ftp_login>" . $params["username"] . "</ftp_login>
				<ftp_password>" . $params["password"] . "</ftp_password>
				<ip_address>" . $ipaddress . "</ip_address>
			</vrt_hst>
		</hosting>
		<user>
			<enabled>true</enabled>
			<password>" . $params["password"] . "</password>
			<cname>" . $clientsdetails["companyname"] . "</cname>
			<pname>" . $clientsdetails["firstname"] . " " . $clientsdetails["lastname"] . "</pname>
			<phone>" . $clientsdetails["phonenumber"] . "</phone>
			<email>" . $clientsdetails["email"] . "</email>
			<address>" . $clientsdetails["address1"] . "</address>
			<city>" . $clientsdetails["city"] . "</city>
			<state>" . $clientsdetails["state"] . "</state>
			<pcode>" . $clientsdetails["postcode"] . "</pcode>
			<country>" . $clientsdetails["country"] . "</country>
			<multiply_login>" . $params["configoption23"] . "</multiply_login>
			<perms>
				<manage_phosting>" . $params["configoption3"] . "</manage_phosting>
				<manage_ftp_password>" . $params["configoption4"] . "</manage_ftp_password>
				<manage_not_chroot_shell>" . $params["configoption5"] . "</manage_not_chroot_shell>
				<manage_sh_access>" . $params["configoption6"] . "</manage_sh_access>
				<manage_quota>" . $params["configoption7"] . "</manage_quota>
				<manage_subdomains>" . $params["configoption8"] . "</manage_subdomains>
				<manage_domain_aliases>" . $params["configoption9"] . "</manage_domain_aliases>
				<manage_log>" . $params["configoption10"] . "</manage_log>
				<manage_anonftp>" . $params["configoption11"] . "</manage_anonftp>
				<manage_crontab>" . $params["configoption12"] . "</manage_crontab>
				<manage_dns>" . $params["configoption13"] . "</manage_dns>
				<manage_webapps>" . $params["configoption14"] . "</manage_webapps>
				<manage_maillists>" . $params["configoption16"] . "</manage_maillists>
				<manage_spamfilter>" . $params["configoption17"] . "</manage_spamfilter>
				<manage_drweb>" . $params["configoption18"] . "</manage_drweb>
				<allow_local_backups>" . $params["configoption19"] . "</allow_local_backups>
				<allow_ftp_backups>" . $params["configoption20"] . "</allow_ftp_backups>
				<site_builder>" . $params["configoption21"] . "</site_builder>
				<manage_dashboard>" . $params["configoption22"] . "</manage_dashboard>
			</perms>
		</user>
		<template-name>" . $params["configoption1"] . "</template-name>
	</add>
</domain>";
	$result = pleskreseller_connection( $params, $packet );

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


function pleskreseller_TerminateAccount($params) {
	$packet = "<domain>
	<del>
		<filter>
			<domain_name>" . $params["domain"] . "</domain_name>
		</filter>
	</del>
</domain>";
	$result = pleskreseller_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result["PACKET"]["SYSTEM"]["STATUS"] == "error") {
		return $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
	}


	if ($result["PACKET"]["DOMAIN"]["DEL"]["RESULT"]["STATUS"] == "error") {
		return $result["PACKET"]["DOMAIN"]["DEL"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["DOMAIN"]["DEL"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function pleskreseller_SuspendAccount($params) {
	$packet = "<domain>
<set>
	<filter>
		<domain_name>" . $params["domain"] . "</domain_name>
	</filter>
	<values>
		<gen_setup>
			<status>64</status>
		</gen_setup>
	</values>
</set>
</domain>";
	$result = pleskreseller_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result["PACKET"]["SYSTEM"]["STATUS"] == "error") {
		return $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
	}


	if ($result["PACKET"]["DOMAIN"]["SET"]["RESULT"]["STATUS"] == "error") {
		return $result["PACKET"]["DOMAIN"]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["DOMAIN"]["SET"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function pleskreseller_UnsuspendAccount($params) {
	$packet = "<domain>
<set>
	<filter>
		<domain_name>" . $params["domain"] . "</domain_name>
	</filter>
	<values>
		<gen_setup>
			<status>0</status>
		</gen_setup>
	</values>
</set>
</domain>";
	$result = pleskreseller_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result["PACKET"]["SYSTEM"]["STATUS"] == "error") {
		return $result["PACKET"]["SYSTEM"]["ERRCODE"] . " - " . $result["PACKET"]["SYSTEM"]["ERRTEXT"];
	}


	if ($result["PACKET"]["DOMAIN"]["SET"]["RESULT"]["STATUS"] == "error") {
		return $result["PACKET"]["DOMAIN"]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["DOMAIN"]["SET"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function pleskreseller_ChangePassword($params) {
	$packet = "<domain>
<set>
<filter>
<domain_name>" . $params["domain"] . "</domain_name>
</filter>
<values>
<hosting>
<vrt_hst>
<ftp_login>" . $params["username"] . "</ftp_login>
<ftp_password>" . $params["password"] . "</ftp_password>
<ip_address>" . $params["configoption2"] . "</ip_address>
</vrt_hst>
</hosting>
<user>
<enabled>true</enabled>
<password>" . $params["password"] . "</password>
</user>
</values>
</set>
</domain>";
	$result = pleskreseller_connection( $params, $packet );

	if ($result["curlerror"]) {
		return $result["curlerror"];
	}


	if ($result[PACKET][SYSTEM][ERRCODE]) {
		return $result[PACKET][SYSTEM][ERRCODE] . " - " . $result[PACKET][SYSTEM][ERRTEXT];
	}


	if ($result["PACKET"]["DOMAIN"]["SET"]["RESULT"]["STATUS"] == "error") {
		return $result["PACKET"]["DOMAIN"]["SET"]["RESULT"]["ERRCODE"] . " - " . $result["PACKET"]["DOMAIN"]["SET"]["RESULT"]["ERRTEXT"];
	}

	return "success";
}


function pleskreseller_connection($params, $packet) {
	global $clientid;
	global $pleskpacketversion;

	if (!$pleskpacketversion) {
		$pleskpacketversion = "1.4.1.0";
	}

	$url = "https://" . $params["serverip"] . ":8443/enterprise/control/agent.php";
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
	logModuleCall( "pleskreseller", "", $packet, $retval, $result );
	return $result;
}


?>