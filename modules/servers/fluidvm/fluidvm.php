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

function fluidvm_ConfigOptions() {
	$configarray = array( "Type" => array( "Type" => "text", "Size" => "25", "Description" => "Either openvz/xen" ), "Plan Name" => array( "Type" => "text", "Size" => "25" ), "OS Template" => array( "Type" => "text", "Size" => "25" ), "Server" => array( "Type" => "text", "Size" => "25" ), "Number Of IPs" => array( "Type" => "dropdown", "Options" => "0,1,2,3,4,5,6,7,8" ), "Disable Welcome Email" => array( "Type" => "yesno", "Description" => "Prevent HyperVM Welcome Mail Sending" ) );
	return $configarray;
}


function fluidvm_ClientArea($params) {
	global $_LANG;

	$code .= "<form action=\"clientarea.php?action=productdetails\" method=\"post\" target=\"_blank\">
		<input type=\"hidden\" name=\"id\" value=\"" . $params["serviceid"] . "\" />
		<input type=\"hidden\" name=\"serveraction\" value=\"custom\" />
        <input type=\"hidden\" name=\"a\" value=\"restart\" />
		<input type=\"submit\" value=\"" . $_LANG["fluidvmrestart"] . "\" class=\"button\" />
		</form>";
	return $code;
}


function fluidvm_LoginLink($params) {
	if ($params["serversecure"]) {
		$protocol = "https";
		$port = "8087";
	}
	else {
		$protocol = "http";
		$port = "8086";
	}

	$domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
	$code = "<a href=\"" . $protocol . "://" . $domain . ":" . $port . "/check_login?var_username=" . urlencode( $params["username"] ) . "&var_password=" . urlencode( $params["password"] ) . "\" target=\"_blank\" class=\"moduleloginlink\">Login as Client</a>";
	return $code;
}


function fluidvm_AdminLink($params) {
	if ($params["serversecure"]) {
		$protocol = "https";
		$port = "8087";
	}
	else {
		$protocol = "http";
		$port = "8086";
	}

	$domain = ($params["serverhostname"] ? $params["serverhostname"] : $params["serverip"]);
	$code = "<form action=\"" . $protocol . "://" . $domain . ":" . $port . "/check_login\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"var_username\" value=\"" . $params["serverusername"] . "\"><input type=\"hidden\" name=\"var_password\" value=\"" . $params["serverpassword"] . "\"><input type=\"submit\" value=\"FluidVM Login\"></form>";
	return $code;
}


function fluidvm_CreateAccount($params) {
	if (isset( $params["customfields"]["Username"] )) {
		$params["username"] = $params["customfields"]["Username"];
	}

	$vhostname = "";

	if (isset( $params["customfields"]["Hostname"] )) {
		$vhostname = $params["customfields"]["Hostname"];

		if ($params["domain"]) {
			$vhostname .= "." . $params["domain"];
		}

		update_query( "tblhosting", array( "domain" => $vhostname ), array( "id" => $params["serviceid"] ) );
		$vhostname = "&v-hostname=" . $vhostname;
	}


	if (isset( $params["configoptions"]["Operating System"] )) {
		$params["configoption3"] = $params["configoptions"]["Operating System"];
	}


	if ($params["serveraccesshash"]) {
		$params["configoption4"] = $params["serveraccesshash"];
	}


	if (substr( $params["username"], 0 - 3 ) != ".vm") {
		$params->username .= ".vm";
		update_query( "tblhosting", array( "username" => $params["username"] ), array( "id" => (int)$params["serviceid"] ) );
	}

	$result = fluidvm_get_via_json( $params["serversecure"], $params["serverip"], $params["serverusername"], $params["serverpassword"], "8888", "action=simplelist&resource=resourceplan" );
	$list = $result->result;

	if ($list) {
		foreach ($list as $key => $value) {
			$plansarray[strtolower( $value )] = $key;
		}
	}


	if (!$params["configoption6"]) {
		$vhostname .= "&v-send_welcome_f=on";
	}

	$result = fluidvm_get_via_json( $params["serversecure"], $params["serverip"], $params["serverusername"], $params["serverpassword"], "8888", "action=add&class=vps&v-type=" . $params["configoption1"] . "&name=" . $params["username"] . "&v-num_ipaddress_f=" . $params["configoption5"] . "&v-contactemail=" . $params["clientsdetails"]["email"] . "&v-password=" . $params["password"] . "&v-ostemplate=" . $params["configoption3"] . "&v-syncserver=" . $params["configoption4"] . "&v-plan_name=" . $plansarray[strtolower( $params["configoption2"] )] . $vhostname );

	if (fluidvm_if_error( $result )) {
		return $result->message;
	}

	$result = fluidvm_get_via_json( $params["serversecure"], $params["serverip"], $params["serverusername"], $params["serverpassword"], "8888", "action=getproperty&class=vps&name=" . $params["username"] . "&v-coma_vmipaddress_a=" );
	$ipaddresses = $result->result->cadbahhgeh;
	update_query( "tblhosting", array( "dedicatedip" => $ipaddresses ), array( "id" => $params["serviceid"] ) );
	return "success";
}


function fluidvm_TerminateAccount($params) {
	$result = fluidvm_get_via_json( $params["serversecure"], $params["serverip"], $params["serverusername"], $params["serverpassword"], "8888", "class=vps&name=" . $params["username"] . "&action=delete" );

	if (fluidvm_if_error( $result )) {
		return $result->message;
	}

	return "success";
}


function fluidvm_SuspendAccount($params) {
	$result = fluidvm_get_via_json( $params["serversecure"], $params["serverip"], $params["serverusername"], $params["serverpassword"], "8888", "class=vps&name=" . $params["username"] . "&action=update&subaction=disable" );

	if (fluidvm_if_error( $result )) {
		return $result->message;
	}

	return "success";
}


function fluidvm_UnsuspendAccount($params) {
	$result = fluidvm_get_via_json( $params["serversecure"], $params["serverip"], $params["serverusername"], $params["serverpassword"], "8888", "class=vps&name=" . $params["username"] . "&action=update&subaction=enable" );

	if (fluidvm_if_error( $result )) {
		return $result->message;
	}

	return "success";
}


function fluidvm_ChangePackage($params) {
	$list = $result = fluidvm_get_via_json( $params["serversecure"], $params["serverip"], $params["serverusername"], $params["serverpassword"], "8888", "action=simplelist&resource=resourceplan" );

	if ($list) {
		foreach ($list as $key => $value) {
			$plansarray[strtolower( $value )] = $key;
		}
	}

	fluidvm_get_via_json( $params["serversecure"], $params["serverip"], $params["serverusername"], $params["serverpassword"], "8888", "class=vps&name=" . $params["username"] . "&action=update&subaction=change_plan&v-newresourceplan=" . $plansarray[strtolower( $params["configoption2"] )] . "" );
	$result = $result->result;

	if (fluidvm_if_error( $result )) {
		return $result->message;
	}

	return "success";
}


function fluidvm_AdminCustomButtonArray() {
	$buttonarray = array( "Restart" => "restart" );
	return $buttonarray;
}


function fluidvm_ClientAreaCustomButtonArray() {
	$buttonarray = array( "Restart" => "restart" );
	return $buttonarray;
}


function fluidvm_restart($params) {
	$result = fluidvm_get_via_json( $params["serversecure"], $params["serverip"], $params["serverusername"], $params["serverpassword"], "8888", "class=vps&name=" . $params["username"] . "&action=update&subaction=reboot" );

	if (fluidvm_if_error( $result )) {
		return $result->message;
	}

	return "success";
}


function fluidvm_get_via_json($protocol, $server, $serverusername, $serverpassword, $port, $param) {
	if ($protocol) {
		$protocol = "https";
		$port = "8087";
	}
	else {
		$protocol = "http";
		$port = "8086";
	}

	$param = "login-class=client&login-name=" . $serverusername . "&login-password=" . $serverpassword . "&output-type=json&" . $param;
	$url = "" . $protocol . "://" . $server . ":" . $port . "/webcommand.php";
	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $param );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
	$totalout = curl_exec( $ch );
	$totalout = trim( $totalout );

	if (curl_errno( $ch )) {
		$totalout = "Curl Error: " . curl_errno( $ch ) . " - " . curl_error( $ch );
	}

	require_once dirname( __FILE__ ) . "/JSON.php";
	$json = new Services_JSON();
	$object = $json->decode( $totalout );
	logModuleCall( "fluidvm", "", $url . "?" . $param, $totalout, "", array( $serverusername, $serverpassword ) );
	return $object;
}


function fluidvm_if_error($json) {
	return $json->return === "error";
}


?>