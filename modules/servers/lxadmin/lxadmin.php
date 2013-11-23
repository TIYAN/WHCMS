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

class LxHelper {
	var $protocol = null;
	var $server = null;
	var $port = null;
	var $username = null;
	var $password = null;

	function LxHelper($server, $username, $password, $useSecure) {
		$this->protocol = ($useSecure ? "https" : "http");
		$this->server = $server;
		$this->port = ($useSecure ? 7777 : 7778);
		$this->username = $username;
		$this->password = $password;
	}


	function callLxApi($params) {
		$params = "login-class=client&login-name=" . $this->username . "&login-password=" . $this->password . "&output-type=json&" . $params;
		$ch = curl_init( $this->protocol . "://" . $this->server . ":" . $this->port . "/webcommand.php" );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
		$totalout = curl_exec( $ch );
		curl_close( $ch );
		$totalout = trim( $totalout );
		require_once dirname( __FILE__ ) . "/../hypervm/JSON.php";
		$json = new Services_JSON();
		$object = $json->decode( $totalout );

		if (!is_object( $object )) {
			print ( "Fatal Error. Got a non-object from the server: " . $totalout . "
" );
			return null;
		}

		return $object;
	}


	function callLxApi_getResourcePlans() {
		return $this->callLxApi( "action=simplelist&resource=resourceplan" );
	}


	function callLxApi_getDnsTemplates() {
		return $this->callLxApi( "action=simplelist&resource=dnstemplate" );
	}


	function callLxApi_getServers() {
		return $this->callLxApi( "action=simplelist&resource=pserver" );
	}


	function objectToCommaList($object, $addDefault = true) {
		foreach ($object as $value => ) {
			$options[] = $value;
		}


		if ($addDefault) {
			return "--- Please select ---," . implode( ",", $options );
		}

		return implode( ",", $options );
	}


	function getInternalResourceName($object, $name) {
		foreach ($object as $key => $value) {

			if ($value == $name) {
				return $key;
			}
		}

	}


}


function lxadmin_ConfigOptions() {
	global $packageconfigoption;

	$serverslist = array();
	$result = select_query( "tblservers", "id,name", array( "type" => "lxadmin" ) );

	while ($data = mysql_fetch_array( $result )) {
		$serverslist[] = $data[0] . "|" . $data[1];
	}


	if ($packageconfigoption[1] == "on") {
		$serverid = explode( "|", $packageconfigoption[8] );
		$serverid = $serverid[0];
		$result = select_query( "tblservers", "ipaddress, username, password, secure", array( "id" => (int)$serverid ) );

		if ($result) {
			$row = mysql_fetch_object( $result );

			if ($row) {
				$lxHelper = new LxHelper( $row->ipaddress, $row->username, decrypt( $row->password ), $row->secure );
				$json = $lxHelper->callLxApi_getResourcePlans();

				if ($json->return === "error") {
					print "ERROR: " . $json->message;
					return null;
				}

				$resourcePlans = $json->result;
				$json = $lxHelper->callLxApi_getDnsTemplates();

				if ($json->return === "error") {
					print "ERROR: " . $json->message;
					return null;
				}

				$dnsTemplates = $json->result;
				$json = $lxHelper->callLxApi_getServers();

				if ($json->return === "error") {
					print "ERROR: " . $json->message;
					return null;
				}

				$servers = $json->result;
			}
		}

		$configarray = array( "Get from server" => array( "Type" => "yesno", "Description" => "Get the available choices from the server" ), "Resource Plan" => array( "Type" => "dropdown", "Options" => LxHelper::objecttocommalist( $resourcePlans ) ), "DNS Template" => array( "Type" => "dropdown", "Options" => LxHelper::objecttocommalist( $dnsTemplates ) ), "Web Server" => array( "Type" => "dropdown", "Options" => LxHelper::objecttocommalist( $servers ) ), "Mail Server" => array( "Type" => "dropdown", "Options" => LxHelper::objecttocommalist( $servers ) ), "MySQL Server" => array( "Type" => "dropdown", "Options" => LxHelper::objecttocommalist( $servers ) ), "DNS Servers" => array( "Type" => "text", "Size" => "40", "Description" => "(comma&nbsp;separated)<br/>Available servers: " . LxHelper::objecttocommalist( $servers, false ) ), "Server to Load Choices From" => array( "Type" => "dropdown", "Options" => implode( ",", $serverslist ) ) );
	}
	else {
		$configarray = array( "Get from server" => array( "Type" => "yesno", "Description" => "Get the available choices from the server" ), "Resource Plan" => array( "Type" => "text", "Size" => "30", "Description" => "<br/>As specified in <strong>Client Home (admin)</strong> -&gt; <strong>Resource Plans</strong>" ), "DNS Template" => array( "Type" => "text", "Size" => "30", "Description" => "<br/>As specified in <strong>Client Home (admin)</strong> -&gt; <strong>DNS Templates</strong>" ), "Wev Server" => array( "Type" => "text", "Size" => "30", "Description" => "<br/>As specified in <strong>Client Home (admin)</strong> -&gt; <strong>Servers</strong>" ), "Mail Server" => array( "Type" => "text", "Size" => "30", "Description" => "<br/>As specified in <strong>Client Home (admin)</strong> -&gt; <strong>Servers</strong>" ), "MySQL Server" => array( "Type" => "text", "Size" => "30", "Description" => "<br/>As specified in <strong>Client Home (admin)</strong> -&gt; <strong>Servers</strong>" ), "DNS Servers" => array( "Type" => "text", "Size" => "40", "Description" => "<br/>As specified in <strong>Client Home (admin)</strong> -&gt; <strong>Servers</strong>. This is a comma separated list." ), "Server to Load Choices From" => array( "Type" => "dropdown", "Options" => implode( ",", $serverslist ) ) );
	}

	return $configarray;
}


function lxadmin_CreateAccount($params) {
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$secure = $params["serversecure"];
	$domain = $params["domain"];
	$username = $params["username"];
	$password = $params["password"];
	$clientsdetails = $params["clientsdetails"];
	$resourcePlan = $params["configoption2"];
	$dnsTemplate = $params["configoption3"];
	$webServer = $params["configoption4"];
	$mailServer = $params["configoption5"];
	$mysqlServer = $params["configoption6"];
	$dnsServers = $params["configoption7"];
	$lxHelper = new LxHelper( $serverip, $serverusername, $serverpassword, $secure );
	$json = $lxHelper->callLxApi_getResourcePlans();

	if ($json->return === "error") {
		return $json->message;
	}

	$resourcePlanInternal = LxHelper::getinternalresourcename( $json->result, $resourcePlan );
	$json = $lxHelper->callLxApi( "action=add" . "&class=client" . "&name=" . $username . "&v-password=" . $password . "&v-plan_name=" . $resourcePlanInternal . "&v-type=customer" . "&v-contactemail=" . $clientsdetails["email"] . "&v-send_welcome_f=off" . "&v-domain_name=" . $domain . "&v-dnstemplate_name=" . $dnsTemplate . "&v-websyncserver=" . $webServer . "&v-mmailsyncserver=" . $mailServer . "&v-mysqldbsyncserver=" . $mysqlServer . "&v-dnssyncserver_list=" . $dnsServers );

	if ($json->return === "error") {
		$result = $json->message;
	}
	else {
		$result = "success";
	}

	return $result;
}


function lxadmin_TerminateAccount($params) {
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$secure = $params["serversecure"];
	$username = $params["username"];
	$lxHelper = new LxHelper( $serverip, $serverusername, $serverpassword, $secure );
	$json = $lxHelper->callLxApi( "action=delete" . "&class=client" . "&name=" . $username );

	if ($json->return === "error") {
		$result = $json->message;
	}
	else {
		$result = "success";
	}

	return $result;
}


function lxadmin_SuspendAccount($params) {
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$secure = $params["serversecure"];
	$username = $params["username"];
	$lxHelper = new LxHelper( $serverip, $serverusername, $serverpassword, $secure );
	$json = $lxHelper->callLxApi( "action=update" . "&subaction=disable" . "&class=client" . "&name=" . $username );

	if ($json->return === "error") {
		$result = $json->message;
	}
	else {
		$result = "success";
	}

	return $result;
}


function lxadmin_UnsuspendAccount($params) {
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$secure = $params["serversecure"];
	$username = $params["username"];
	$lxHelper = new LxHelper( $serverip, $serverusername, $serverpassword, $secure );
	$json = $lxHelper->callLxApi( "action=update" . "&subaction=enable" . "&class=client" . "&name=" . $username );

	if ($json->return === "error") {
		$result = $json->message;
	}
	else {
		$result = "success";
	}

	return $result;
}


function lxadmin_ChangePassword($params) {
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$secure = $params["serversecure"];
	$username = $params["username"];
	$password = $params["password"];
	$lxHelper = new LxHelper( $serverip, $serverusername, $serverpassword, $secure );
	$json = $lxHelper->callLxApi( "action=update" . "&subaction=password" . "&class=client" . "&name=" . $username . "&v-password=" . $password );

	if ($json->return === "error") {
		$result = $json->message;
	}
	else {
		$result = "success";
	}

	return $result;
}


function lxadmin_ChangePackage($params) {
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$secure = $params["serversecure"];
	$username = $params["username"];
	$resourcePlan = $params["configoption2"];
	$lxHelper = new LxHelper( $serverip, $serverusername, $serverpassword, $secure );
	$json = $lxHelper->callLxApi_getResourcePlans();

	if ($json->return === "error") {
		return $json->message;
	}

	$resourcePlanInternal = LxHelper::getinternalresourcename( $json->result, $resourcePlan );
	$json = $lxHelper->callLxApi( "action=update" . "&subaction=change_plan" . "&class=client" . "&name=" . $username . "&v-resourceplan_name=" . $resourcePlanInternal );

	if ($json->return === "error") {
		$result = $json->message;
	}
	else {
		$result = "success";
	}

	return $result;
}


function lxadmin_LoginLink($params) {
	if ($params["serversecure"]) {
		$protocol = "https";
		$port = 7782;
	}
	else {
		$protocol = "http";
		$port = 7783;
	}

	$code = "<a href=\"" . $protocol . "://" . $params["serverip"] . ":" . $port . "/htmllib/phplib/?frm_clientname=" . $params["username"] . "&amp;frm_password=" . $params["password"] . "\" target=\"_blank\" class=\"moduleloginlink\">login to LxAdmin</a>";
	return $code;
}


?>