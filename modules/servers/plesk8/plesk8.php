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

function plesk8_ConfigOptions() {
	$configarray = array( "Client Template Name" => array( "Type" => "text", "Size" => "25" ), "Domain Template Name" => array( "Type" => "text", "Size" => "25" ), "IP Address" => array( "Type" => "text", "Size" => "20", "Description" => "Only required if instructed by WHMCS Support" ) );
	return $configarray;
}


function plesk8_ClientArea($params) {
	global $_LANG;

	if ($params['serverhostname']) {
		$domain = $params['serverhostname'];
	}
	else {
		$domain = $params['serverip'];
	}

	$code = "<form action=\"https://" . $domain . ":8443/login_up.php3\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"login_name\" value=\"" . $params['username'] . "\"><input type=\"hidden\" name=\"passwd\" value=\"" . $params['password'] . "\"><input type=\"submit\"  value=\"" . $_LANG['plesklogin'] . "\" class=\"button\"></form>";
	return $code;
}


function plesk8_AdminLink($params) {
	if ($params['serverhostname']) {
		$domain = $params['serverhostname'];
	}
	else {
		$domain = $params['serverip'];
	}

	$code = "<form action=\"https://" . $domain . ":8443/login_up.php3\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"login_name\" value=\"" . $params['serverusername'] . "\"><input type=\"hidden\" name=\"passwd\" value=\"" . $params['serverpassword'] . "\"><input type=\"submit\" value=\"Plesk\"></form>";
	return $code;
}


function plesk8_CreateAccount($params) {
	global $clientid;

	if ($params['clientsdetails']['country'] == "UK") {
		$params['clientsdetails']['country'] = "GB";
	}

	$packet = "
<client>
<add>
<gen_info>
";

	if ($params['clientsdetails']['companyname']) {
		$packet .= "<cname>" . $params['clientsdetails']['companyname'] . "</cname>";
	}

	$packet .= "<pname>" . $params['clientsdetails']['firstname'] . " " . $params['clientsdetails']['lastname'] . " " . $params['serviceid'] . "</pname>
<login>" . $params['username'] . "</login>
<passwd>" . $params['password'] . "</passwd>
<status>0</status>
<phone>" . $params['clientsdetails']['phonenumber'] . "</phone>
<fax/>
<email>" . $params['clientsdetails']['email'] . "</email>
<address>" . $params['clientsdetails']['address1'] . "</address>
<city>" . $params['clientsdetails']['city'] . "</city>
<state>" . $params['clientsdetails']['state'] . "</state>
<pcode>" . $params['clientsdetails']['postcode'] . "</pcode>
<country>" . $params['clientsdetails']['country'] . "</country>
</gen_info>
<template-name>" . $params['configoption1'] . "</template-name>
</add>
</client>
";
	$result = plesk8_connection( $params, $packet );

	if ($result['curlerror']) {
		return $result['curlerror'];
	}


	if ($result['PACKET'][SYSTEM]['ERRCODE']) {
		return $result['PACKET'][SYSTEM][ERRCODE] . " - " . $result['PACKET'][SYSTEM]['ERRTEXT'];
	}

	$clientid = $result['PACKET']['CLIENT']['ADD']['RESULT']['ID'];

	if (strlen( $clientid ) == 0) {
		return $result['PACKET']['CLIENT']['ADD']['RESULT']['ERRCODE'] . " - " . $result['PACKET']['CLIENT']['ADD']['RESULT']['ERRTEXT'];
	}


	if ($params['configoption3']) {
		$ipaddress = $params['configoption3'];
		$packet = "
<client>
<ippool_add_ip>
<client_id>" . $clientid . "</client_id>
<ip_address>" . $ipaddress . "</ip_address>
</ippool_add_ip>
</client>
";
		$result = plesk8_connection( $params, $packet );

		if ($result['curlerror']) {
			return $result['curlerror'];
		}
	}
	else {
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
		$result = plesk8_connection( $params, $packet );

		if ($result['curlerror']) {
			return $result['curlerror'];
		}


		if ($result['PACKET']['CLIENT']['IPPOOL_ADD_IP']['RESULT']['STATUS'] == "error") {
			return $result['PACKET']['CLIENT']['IPPOOL_ADD_IP']['RESULT']['ERRCODE'] . " - " . $result['PACKET']['CLIENT']['IPPOOL_ADD_IP']['RESULT']['ERRTEXT'];
		}

		$ipaddress = $result['PACKET']['CLIENT']['GET']['RESULT']['DATA']['IPPOOL']['IP-ADDRESS'];

		if (!$ipaddress) {
			$ipaddress = $result['PACKET']['CLIENT']['GET']['RESULT']['DATA']['IPPOOL']['IP_ADDRESS'];
		}
	}

	$packet = "
<domain>
	<add>
		<gen_setup>
			<name>" . $params['domain'] . "</name>
			<client_id>" . $clientid . "</client_id>
			<ip_address>" . $ipaddress . "</ip_address>
			<htype>vrt_hst</htype>
			<status>0</status>
		</gen_setup>
		<hosting>
			<vrt_hst>
				<ftp_login>" . $params['username'] . "</ftp_login>
				<ftp_password>" . $params['password'] . "</ftp_password>
				<ip_address>" . $ipaddress . "</ip_address>
			</vrt_hst>
		</hosting>
		<prefs>
            <www>true</www>
		</prefs>
		<user>
			<enabled>true</enabled>
			<password>" . $params['password'] . "</password>
		</user>
		<template-name>" . $params['configoption2'] . "</template-name>
	</add>
</domain>
";
	$result = plesk8_connection( $params, $packet );

	if ($result['curlerror']) {
		return $result['curlerror'];
	}


	if ($result['PACKET']['SYSTEM']['STATUS'] == "error") {
		return $result['PACKET']['SYSTEM']['ERRCODE'] . " - " . $result['PACKET']['SYSTEM']['ERRTEXT'];
	}


	if ($result['PACKET']['DOMAIN']['ADD']['RESULT']['STATUS'] == "error") {
		return $result['PACKET']['DOMAIN']['ADD']['RESULT']['ERRCODE'] . " - " . $result['PACKET']['DOMAIN']['ADD']['RESULT']['ERRTEXT'];
	}

	return "success";
}


function plesk8_TerminateAccount($params) {
	$packet = "<client>
<del>
<filter>
<login>" . $params['username'] . "</login>
</filter>
</del>
</client>";
	$result = plesk8_connection( $params, $packet );

	if ($result['curlerror']) {
		return $result['curlerror'];
	}


	if ($result['PACKET'][SYSTEM]['ERRCODE']) {
		return $result['PACKET'][SYSTEM][ERRCODE] . " - " . $result['PACKET'][SYSTEM]['ERRTEXT'];
	}


	if ($result['PACKET']['CLIENT']['DEL']['RESULT']['STATUS'] == "error") {
		return $result['PACKET']['CLIENT']['DEL']['RESULT']['ERRCODE'] . " - " . $result['PACKET']['CLIENT']['DEL']['RESULT']['ERRTEXT'];
	}

	return "success";
}


function plesk8_SuspendAccount($params) {
	$packet = "<client>
<set>
<filter>
<login>" . $params['username'] . "</login>
</filter>
<values>
<gen_info>
<status>16</status>
</gen_info>
</values>
</set>
</client>";
	$result = plesk8_connection( $params, $packet );

	if ($result['curlerror']) {
		return $result['curlerror'];
	}


	if ($result['PACKET'][SYSTEM]['ERRCODE']) {
		return $result['PACKET'][SYSTEM][ERRCODE] . " - " . $result['PACKET'][SYSTEM]['ERRTEXT'];
	}


	if ($result['PACKET']['CLIENT']['SET']['RESULT']['STATUS'] == "error") {
		return $result['PACKET']['CLIENT']['SET']['RESULT']['ERRCODE'] . " - " . $result['PACKET']['CLIENT']['SET']['RESULT']['ERRTEXT'];
	}

	return "success";
}


function plesk8_UnsuspendAccount($params) {
	$packet = "<client>
<set>
<filter>
<login>" . $params['username'] . "</login>
</filter>
<values>
<gen_info>
<status>0</status>
</gen_info>
</values>
</set>
</client>";
	$result = plesk8_connection( $params, $packet );

	if ($result['curlerror']) {
		return $result['curlerror'];
	}


	if ($result['PACKET'][SYSTEM]['ERRCODE']) {
		return $result['PACKET'][SYSTEM][ERRCODE] . " - " . $result['PACKET'][SYSTEM]['ERRTEXT'];
	}


	if ($result['PACKET']['CLIENT']['SET']['RESULT']['STATUS'] == "error") {
		return $result['PACKET']['CLIENT']['SET']['RESULT']['ERRCODE'] . " - " . $result['PACKET']['CLIENT']['SET']['RESULT']['ERRTEXT'];
	}

	return "success";
}


function plesk8_ChangePassword($params) {
	$packet = "<client>
<set>
<filter>
<login>" . $params['username'] . "</login>
</filter>
<values>
<gen_info>
<passwd>" . $params['password'] . "</passwd>
</gen_info>
</values>
</set>
</client>";
	$result = plesk8_connection( $params, $packet );

	if ($result['curlerror']) {
		return $result['curlerror'];
	}


	if ($result['PACKET'][SYSTEM]['ERRCODE']) {
		return $result['PACKET'][SYSTEM][ERRCODE] . " - " . $result['PACKET'][SYSTEM]['ERRTEXT'];
	}


	if ($result['PACKET']['CLIENT']['SET']['RESULT']['STATUS'] == "error") {
		return $result['PACKET']['CLIENT']['SET']['RESULT']['ERRCODE'] . " - " . $result['PACKET']['CLIENT']['SET']['RESULT']['ERRTEXT'];
	}

	$packet = "<domain>
<set>
<filter>
<client_login>" . $params['username'] . "</client_login>
</filter>
<values>
<hosting>
<vrt_hst>
<ftp_password>" . $params['password'] . "</ftp_password>
</vrt_hst>
</hosting>
</values>
</set>
</domain>";
	$result = plesk8_connection( $params, $packet );

	if ($result['curlerror']) {
		return $result['curlerror'];
	}


	if ($result['PACKET'][SYSTEM]['ERRCODE']) {
		return $result['PACKET'][SYSTEM][ERRCODE] . " - " . $result['PACKET'][SYSTEM]['ERRTEXT'];
	}


	if ($result['PACKET']['DOMAIN']['SET']['RESULT']['STATUS'] == "error") {
		return $result['PACKET']['DOMAIN']['SET']['RESULT']['ERRCODE'] . " - " . $result['PACKET']['DOMAIN']['SET']['RESULT']['ERRTEXT'];
	}

	return "success";
}


function plesk8_connection($params, $packet) {
	global $clientid;
	global $plesk8packetversion;

	if (!$plesk8packetversion) {
		$plesk8packetversion = "1.4.1.0";
	}

	$url = "https://" . $params['serverip'] . ":8443/enterprise/control/agent.php";
	$headers = array( "HTTP_AUTH_LOGIN: " . $params['serverusername'], "HTTP_AUTH_PASSWD: " . $params['serverpassword'], "Content-Type: text/xml" );
	$packet = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><packet version=\"" . $plesk8packetversion . "\">" . $packet . "</packet>";
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $packet );
	$retval = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$result['curlerror'] = "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch );
	}
	else {
		$result = XMLtoARRAY( $retval );
	}

	curl_close( $ch );
	logModuleCall( "plesk8", $params['action'], $packet, $retval, $result );
	return $result;
}


?>