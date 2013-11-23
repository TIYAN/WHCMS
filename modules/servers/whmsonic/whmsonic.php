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

function whmsonic_ConfigOptions() {
	$configarray = array( "Client Type" => array( "Type" => "dropdown", "Options" => "External,internal", "Description" => "Notice: If Internal selected, please set the custom form field under the Custom Fields link, the client must enter cPanel username on the order page, that is also mean the client has already cPanel hosting account on the server. This option will not setup cPanel account! It will setup only radio under the provided cpanel user account." ), "Max Listeners Limit" => array( "Type" => "text", "Size" => "3" ), "Max BitRate Limit" => array( "Type" => "dropdown", "Options" => "64,128,24,32,48,96,192,384" ), "AutoDJ Feature" => array( "Type" => "yesno", "Description" => "If yes, the user will access to AutoDJ features in their cPanel WHMSonic." ), "Hosting Space" => array( "Type" => "text", "Size" => "25", "Description" => "Hosting space is required by external clients only if autodj option is enabled to upload music files. Please enter a limit, ex: 100 = 100MB. Enter only numbers in this field." ), "Bandwidth Limit" => array( "Type" => "text", "Size" => "25" ) );
	return $configarray;
}


function whmsonic_CreateAccount($params) {
	$ctype = $params["configoption1"];
	$listeners = $params["configoption2"];
	$radioip = $params["serverip"];
	$bitrate = $params["configoption3"];
	$autodj = $params["configoption4"];
	$hspace = $params["configoption5"];
	$bandwidth = $params["configoption6"];
	$serverp = $params["serverpassword"];
	$connection = $params["serverip"];
	$auth = "root:" . $serverp;
	$orderid = $params["serviceid"];

	if ($params["serversecure"] == "on") {
		$serverport = "2087";
		$ht = "https";
	}
	else {
		$serverport = "2086";
		$ht = "http";
	}

	$client_email = $params["clientsdetails"]["email"];
	$client_name = $params["clientsdetails"]["firstname"];

	if ($params["configoption1"] == "internal") {
		$radiousername = $params["customfields"]["cpanel username"];
	}
	else {
		$chars = "abcdefghijkmnpqrstuvwxyz0123456789";
		srand( (double)microtime() * 1000000 );
		$i = 6;
		$exu = "";

		while ($i <= 4) {
			$num = rand() % 33;
			$tmp = substr( $chars, $num, 1 );
			$exu = $exu . $tmp;
			++$i;
		}

		$radiousername = "sc_" . $exu;
	}

	$chars2 = "abcdefghijkmnopqrstuvwxyz023456789";
	srand( (double)microtime() * 1000000 );
	$i = 6;
	$pass = "";

	while ($i <= 7) {
		$num = rand() % 33;
		$tmp = substr( $chars2, $num, 1 );
		$pass = $pass . $tmp;
		++$i;
	}

	$query3 = "UPDATE tblhosting SET username='" . db_escape_string( $radiousername ) . "', password='" . db_escape_string( $pass ) . "' WHERE id=" . (int)$params["accountid"];
	$result3 = full_query( $query3 );
	$url = "" . $ht . "://" . $connection . ":" . $serverport . "/whmsonic/modules/api.php?";
	$data = "cmd=setup&ctype=" . $ctype . "&ip=" . $radioip . "&bitrate=" . $bitrate . "&autodj=" . $autodj . "&bw=" . $bandwidth . "&semail=" . $wemail . "&limit=" . $listeners . "&cemail=" . $client_email . "&cname=" . $client_name . "&rad_username=" . $radiousername . "&pass=" . $pass . "&hspace=" . $hspace;
	$ch = curl_init();
	curl_setopt( $ch, CURLAUTH_BASIC, CURLAUTH_ANY );
	curl_setopt( $ch, CURLOPT_USERPWD, $auth );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 59 );
	curl_setopt( $ch, CURLOPT_URL, $url );
	$retval = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$retval = "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch ) . " - Please check the radioIP in the package module configuration and check the root password in the servers settings of WHMCS.";
	}

	curl_close( $ch );

	if ($retval == "Complete") {
		$result = "success";
	}
	else {
		if (strpos( $retval, "Login Attempt Failed!" ) == true) {
			$result = "WHMSonic server(" . $radioip . ") WHM root login failed! The root password is incorrect. Please check your WHMSonic server settings in the WHMCS setup menu servers link.";
		}
		else {
			$result = "" . $retval;
		}
	}

	return $result;
}


function whmsonic_TerminateAccount($params) {
	$connection = $params["serverip"];
	$rad_username = $params["username"];
	$serverp = $params["serverpassword"];
	$auth = "root:" . $serverp;

	if ($params["serversecure"] == "on") {
		$serverport = "2087";
		$ht = "https";
	}
	else {
		$serverport = "2086";
		$ht = "http";
	}

	$url = "" . $ht . "://" . $connection . ":" . $serverport . "/whmsonic/modules/api.php?";
	$data = "cmd=terminate&rad_username=" . $rad_username;
	$ch = curl_init();
	curl_setopt( $ch, CURLAUTH_BASIC, CURLAUTH_ANY );
	curl_setopt( $ch, CURLOPT_USERPWD, $auth );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 59 );
	curl_setopt( $ch, CURLOPT_URL, $url );
	$retval = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$retval = "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch );
	}

	curl_close( $ch );

	if ($retval == "Complete") {
		$result = "success";
	}
	else {
		$result = "<br>" . $retval;
	}

	return $result;
}


function whmsonic_SuspendAccount($params) {
	$connection = $params["serverip"];
	$rad_username = $params["username"];
	$serverp = $params["serverpassword"];
	$auth = "root:" . $serverp;

	if ($params["serversecure"] == "on") {
		$serverport = "2087";
		$ht = "https";
	}
	else {
		$serverport = "2086";
		$ht = "http";
	}

	$url = "" . $ht . "://" . $connection . ":" . $serverport . "/whmsonic/modules/api.php?";
	$data = "cmd=suspend&rad_username=" . $rad_username;
	$ch = curl_init();
	curl_setopt( $ch, CURLAUTH_BASIC, CURLAUTH_ANY );
	curl_setopt( $ch, CURLOPT_USERPWD, $auth );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 59 );
	curl_setopt( $ch, CURLOPT_URL, $url );
	$retval = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$retval = "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch );
	}

	curl_close( $ch );

	if ($retval == "Complete") {
		$result = "success";
	}
	else {
		$result = "<br>" . $retval;
	}

	return $result;
}


function whmsonic_UnsuspendAccount($params) {
	$connection = $params["serverip"];
	$rad_username = $params["username"];
	$serverp = $params["serverpassword"];
	$auth = "root:" . $serverp;

	if ($params["serversecure"] == "on") {
		$serverport = "2087";
		$ht = "https";
	}
	else {
		$serverport = "2086";
		$ht = "http";
	}

	$url = "" . $ht . "://" . $connection . ":" . $serverport . "/whmsonic/modules/api.php?";
	$data = "cmd=unsuspend&rad_username=" . $rad_username;
	$ch = curl_init();
	curl_setopt( $ch, CURLAUTH_BASIC, CURLAUTH_ANY );
	curl_setopt( $ch, CURLOPT_USERPWD, $auth );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 59 );
	curl_setopt( $ch, CURLOPT_URL, $url );
	$retval = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$retval = "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch );
	}

	curl_close( $ch );

	if ($retval == "Complete") {
		$result = "success";
	}
	else {
		$result = "<br>" . $retval;
	}

	return $result;
}


function whmsonic_ClientArea($params) {
	global $_LANG;

	$connection = $params["serverip"];
	$code = "<form action=http://" . $connection . "/cpanel/ method=post target=_blank><input type=hidden name=ip value=" . $licenseip . "><input type=submit value=\"" . $_LANG["whmsoniclogin"] . "\"></form>";
	return $code;
}


function whmsonic_AdminLink($params) {
	$connection = $params["serverip"];

	if ($params["serversecure"] == "on") {
		$serverport = "2087";
		$ht = "https";
	}
	else {
		$serverport = "2086";
		$ht = "http";
	}

	$code = "<form action=\"" . $ht . "://" . $connection . ":" . $serverport . "/whmsonic/main.php\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"username\" value=\"" . $params["serverusername"] . "\"><input type=\"hidden\" name=\"password\" value=\"" . $params["serverpassword"] . "\"><input type=\"submit\" value=\"WHMSonic Login\"></form>";
	return $code;
}


?>