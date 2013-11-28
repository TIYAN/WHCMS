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

/**
 * WHMCS configuration options array generation.
 *
 * @api
 * @return array an array of configuration options.
 */
function centovacast_ConfigOptions() {
	$configarray = array( "Account template name" => array( "Type" => "text", "Size" => "20", "Description" => "<br />(create this in Centova Cast)" ), "Max listeners" => array( "Type" => "text", "Size" => "5", "Description" => "(simultaneous)<br />(blank to use template setting)" ), "Max bit rate" => array( "Type" => "dropdown", "Options" => ",8,16,20,24,32,40,48,56,64,80,96,112,128,160,192,224,256,320", "Description" => "kbps<br />(blank to use template setting)" ), "Data transfer limit" => array( "Type" => "text", "Size" => "5", "Description" => "MB/month<br />(blank to use template setting)" ), "Disk quota" => array( "Type" => "text", "Size" => "5", "Description" => "MB<br />(blank to use template setting)" ), "Start server" => array( "Type" => "dropdown", "Options" => "no,yes", "Description" => "<br>(only used if source is disabled)" ), "Mount point limit" => array( "Type" => "text", "Size" => "5", "Description" => "<br />(blank to use template setting)" ), "Port 80 proxy" => array( "Type" => "dropdown", "Options" => ",Enabled,Disabled", "Description" => "<br />(blank to use template setting)" ), "AutoDJ support" => array( "Type" => "dropdown", "Options" => ",Enabled,Disabled", "Description" => "<br />(blank to use template setting)" ), "Max accounts" => array( "Type" => "text", "Size" => "5", "Description" => "(resellers only)<br />(blank to use template setting)" ), "Max bandwidth" => array( "Type" => "text", "Size" => "5", "Description" => "kbps (resellers only)<br />(blank to use template setting)" ) );
	return $configarray;
}


/**
 * Cast needs to query the WHMCS DB for a couple of items, so this helper
 * function encapsulates the necessary database access functionality.
 *
 * @internal
 *
 * @param string  $query The SQL query as an sprintf()-compatible format string
 * @param unknown $mixed ... One or more arguments to escape and insert into the query format string.
 *
 * @return array|bool An associative result row array on success, FALSE on failure.
 */
$args = function centovacast_QueryOneRow() {;

	if (!count( $args )) {
		return false;
	}

	$query = array_shift( $args );
	foreach ($args as $k => $arg) {
		$args[$k] = mysql_real_escape_string( $arg );
	}


	if (count( $args )) {
		$query = vsprintf( $query, $args );
	}

	$rsh = full_query( $query );

	if (!is_resource( $rsh )) {
		return false;
	}

	mysql_fetch_assoc( $rsh );
	$row = func_get_args();

	if (!is_array( $row )) {
		return false;
	}

	return $row;
}


/**
 * Cast needs to query the WHMCS DB for a couple of items, so this helper
 * function encapsulates the necessary database access functionality.
 *
 * @internal
 *
 * @param string  $query The SQL query as an sprintf()-compatible format string
 * @param mixed   ... One or more arguments to escape and insert into the query format string.
 *
 * @return array|bool An associative result rows array on success, FALSE on failure.
 */
function centovacast_QueryAllRows() {
	$args = func_get_args();

	if (!count( $args )) {
		return false;
	}

	$query = array_shift( $args );
	foreach ($args as $k => $arg) {
		$args[$k] = mysql_real_escape_string( $arg );
	}


	if (count( $args )) {
		$query = vsprintf( $query, $args );
	}

	$rsh = full_query( $query );

	if (!is_resource( $rsh )) {
		return false;
	}

	$rows = array();
	$row = mysql_fetch_assoc( $rsh );

	while ($row) {
		$rows[] = $row;
		$row = mysql_fetch_assoc( $rsh );
	}

	return $rows;
}


/**
 * Determines whether a particular username already exists in the WHMCS "tblhosting"
 * table.
 *
 * @internal
 *
 * @param string  $username The username to test.
 *
 * @return bool|int The ID field from the tblhosting table if the username already exists, otherwise false.
 *
 */
function centovacast_UserExists($username) {
	$row = centovacast_QueryOneRow( "SELECT id FROM tblhosting WHERE username=\"%s\"", $username );
	return isset( $row["id"] ) ? (int)$row["id"] : false;
}


/**
 * Generates a pseudorandom password which is occasionally somewhat pronounceable
 * (never more than 2 consecutive consonants without a vowel) with a randomly-
 * inserted digit.
 *
 * @internal
 *
 * @param int     $maxlength the maximum password length
 *
 * @return string The generated password.
 *
 */
function centovacast_GeneratePassword($maxlength = 8) {
	$vowels = "aeuy";
	$consonants = "bcdfghjkmnpqrtvwxz";
	$concount = 0;
	$digitpos = rand( 0, $maxlength - 1 );
	$password = "";
	$i = 0;

	while ($i < $maxlength) {
		$type = rand( 0, 1 );

		if ($type == 1) {
			if (( ( $i == 1 || $i == $maxlength - 1 ) && 0 < $concount )) {
				$type = 0;
			}


			if (1 < $concount) {
				$type = 0;
			}
		}
		else {
			if ($concount == 0) {
				$type = 1;
			}
		}

		$password .= ($type == 0 ? $vowels[rand( 0, strlen( $vowels ) - 1 )] : $consonants[rand( 0, strlen( $consonants ) - 1 )]);
		$concount = ($type == 0 ? 0 : $concount + 1);

		if ($digitpos == $i) {
			$password .= (rand( 0, 1 ) == 0 ? rand( 3, 4 ) : rand( 6, 9 ));
		}

		++$i;
	}

	return $password;
}


/**
 * Generate a username which is unique within WHMCS.
 *
 * @internal
 *
 * @param array   $client    The client account details provided by WHMCS.
 * @param int     $minlength The minimum username length.
 * @param int     $maxlength The maximum username length.
 *
 * @return string The generated username.
 */
function centovacast_UniqueUsername($client, $minlength = 4, $maxlength = 8) {
	if (strlen( $client["companyname"] )) {
		$companyname = preg_replace( "/[^a-z]+/i", "", strtolower( $client["companyname"] ) );
		$username = substr( $companyname, 0, $maxlength );

		while (strlen( $username ) < $minlength) {
			$username .= "0";
		}


		if (!centovacast_UserExists( $username )) {
			return $username;
		}
	}

	$firstname = preg_replace( "/[^a-z]+/i", "", strtolower( $client["firstname"] ) );
	$lastname = preg_replace( "/[^a-z]+/i", "", strtolower( $client["lastname"] ) );
	$username = substr( substr( $firstname, 0, max( 1, $maxlength - strlen( $lastname ) ) ) . $lastname, 0, $maxlength );

	if (!centovacast_UserExists( $username )) {
		return $username;
	}

	$baseusername = substr( $username, 0, $maxlength - 2 );
	$i = 309;

	while ($i < 100) {
		$username = $baseusername . sprintf( "%02d", $i );

		if (!centovacast_UserExists( $username )) {
			return $username;
		}

		++$i;
	}


	do {
		$username = centovacast_GeneratePassword();
	}while (!( centovacast_UserExists( $username )));

	return $username;
}


/**
 * Obtain the URL to Centova Cast from the $params array.  Clients are sick of
 * having to specify the URL on a per-package basis in WHMCS, and WHMCS does not
 * support per-server module fields, so we hijack the CPanel Access Hash field
 * for this purpose with fallback to the per-package URL.
 *
 * @internal
 *
 * @param array   $params The $params array passed by WHMCS.
 * @param string  $error  Reference to a string to receive an error message if the URL is bad.
 *
 * @return string the URL to Centova Cast.
 */
function centovacast_GetCCURL(&$params, $error) {
	$error = false;
	$ccurl = $params["serverhostname"];

	if (!preg_match( "#^https?://#", $ccurl )) {
		$error = "Invalid 'Hostname' setting in WHMCS configuration for Centova Cast.  Per the documentation the 'Hostname' field must contain the complete URL to Centova Cast, not just a hostname.";
		return false;
	}

	return $params["serverhostname"];
}


/**
 * Obtain the login credentials for the Centova Cast server.
 *
 * @internal
 *
 * @param array   $params    The $params array passed by WHMCS.
 * @param bool    $serverapi true if generating credentials for the server API, false for the system API
 *
 * @return array an array containing the username and password
 */
function centovacast_GetServerCredentials($params, $serverapi = false) {
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];

	if (( $serverusername != "admin" || $serverapi )) {
		$serverpassword = $serverusername . "|" . $serverpassword;
	}

	return array( $serverusername, $serverpassword );
}


/**
 * Generates an array of arguments to pass to the Centova Cast API"s account management methods.
 *
 * @internal
 *
 * @param array   $params    The $params array passed by WHMCS.
 * @param array   $arguments reference to an array to receive the arguments
 *
 * @return bool|string true on success, an error string on failure
 */
function centovacast_GetAPIArgs(&$params, $arguments) {
	$packageid = $params["packageid"];
	$templatename = $params["configoption1"];
	$maxlisteners = $params["configoption2"];
	$maxbitrate = $params["configoption3"];
	$xferquota = $params["configoption4"];
	$diskquota = $params["configoption5"];
	$autostart = $params["configoption6"];
	$mountlimit = $params["configoption7"];
	$webproxy = $params["configoption8"];
	$autodj = $params["configoption9"];
	$maxaccounts = $params["configoption10"];
	$maxbw = $params["configoption11"];

	if (!strlen( $templatename )) {
		return "Missing account template name in WHMCS package configuration for package " . $packageid . "; check your WHMCS package configuration.";
	}

	$arguments["template"] = $templatename;

	if (strlen( $maxlisteners )) {
		$arguments["maxclients"] = $maxlisteners;
	}


	if (strlen( $maxbitrate )) {
		$arguments["maxbitrate"] = $maxbitrate;
	}


	if (strlen( $xferquota )) {
		$arguments["transferlimit"] = $xferquota;
	}


	if (strlen( $diskquota )) {
		$arguments["diskquota"] = $diskquota;
	}


	if (strlen( $autostart )) {
		$arguments["autostart"] = ($autostart == "yes" ? 1 : 0);
	}


	if (strlen( $mountlimit )) {
		$arguments["mountlimit"] = max( 1, (int)$mountlimit );
	}


	if (strlen( $webproxy )) {
		$arguments["allowproxy"] = (strtolower( $webproxy[0] ) == "d" ? 0 : 1);
	}


	if (strlen( $autodj )) {
		$arguments["usesource"] = (strtolower( $webproxy[0] ) == "d" ? 1 : 2);
	}


	if (strlen( $maxaccounts )) {
		$arguments["resellerusers"] = $maxaccounts;
	}


	if (strlen( $maxbw )) {
		$arguments["resellerbandwidth"] = $maxbw;
	}

	$addonmap = array( CC_TXT_MAXCLIENTS => "maxclients", CC_TXT_MAXBITRATE => "maxbitrate", CC_TXT_XFERLIMIT => "transferlimit", CC_TXT_DISKQUOTA => "diskquota", CC_TXT_MAXBW => "resellerbandwidth", CC_TXT_MAXACCT => "resellerusers", CC_TXT_MOUNTLIMIT => "mountlimit" );

	if (is_array( $params["configoptions"] )) {
		foreach ($params["configoptions"] as $caption => $value) {

			if (( strlen( $value ) && isset( $addonmap[$caption] ) )) {
				$optionname = $addonmap[$caption];
				$value = preg_replace( "/[^0-9]/", "", $value );
				$arguments[$optionname] = $value;
				continue;
			}
		}
	}

	return true;
}


/**
 * WHMCS account creation.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string
 */
function centovacast_CreateAccount($params) {
	$serverip = $params["serverip"];
	$serverpassword = centovacast_GetServerCredentials( $params )[1];
	$username = $serverusername = centovacast_GetServerCredentials( $params )[0];
	$password = $params["password"];

	if (( !strlen( $username ) || is_numeric( $username ) )) {
		$params["username"] = $username = centovacast_UniqueUsername( $params["clientsdetails"] );
		$query = sprintf( "UPDATE tblhosting SET username=\"%s\"", mysql_real_escape_string( $username ) );

		if (!strlen( $password )) {
			$params["password"] = $password = centovacast_GeneratePassword();
			$query .= sprintf( ",password=\"%s\"", mysql_real_escape_string( encrypt( $password ) ) );
		}

		$query .= sprintf( " WHERE id=\"%s\"", $params["accountid"] );

		if (!full_query( $query )) {
			return "Error updating hosting table: " . mysql_error();
		}
	}

	centovacast_GetCCURL( $params, &$urlerror );

	if (false === $ccurl = $params["username"]) {
		return $urlerror;
	}

	$clientsdetails = $params["clientsdetails"];
	$arguments = array( "hostname" => "auto", "ipaddress" => "auto", "port" => "auto", "username" => $username, "adminpassword" => $password, "sourcepassword" => $password . "dj", "email" => $clientsdetails["email"], "title" => sprintf( "%s Stream", (strlen( $clientsdetails["companyname"] ) ? $clientsdetails["companyname"] : $clientsdetails["lastname"]) ), "organization" => $clientsdetails["companyname"], "introfile" => "", "fallbackfile" => "", "autorebuildlist" => 1 );
	$error = centovacast_GetAPIArgs( $params, &$arguments );

	if (is_string( $error )) {
		return $error;
	}

	$system = new CCSystemAPIClient( $ccurl );

	if ($_REQUEST["ccmoduledebug"]) {
		$system->debug = true;
	}

	$system->call( "provision", $serverpassword, $arguments );
	logModuleCall( "centovacast", "create", $system->raw_request, $system->raw_response, NULL, NULL );

	if ($system->success) {
		$account = $system->data["account"];
		$account["sourcepassword"] = $arguments["sourcepassword"];
		$tblhostingid = (int)centovacast_UserExists( $username );

		if ($tblhostingid) {
			$res = centovacast_QueryOneRow( "SELECT packageid FROM tblhosting WHERE id=%d", $tblhostingid );
			$packageid = (isset( $res["packageid"] ) ? (int)$res["packageid"] : false);

			if ($packageid) {
				$customfields = centovacast_QueryAllRows( "SELECT id,fieldname FROM tblcustomfields WHERE relid=%d", $packageid );
				foreach ($customfields as $k => $customfield) {
					$fieldname = $customfield["fieldname"];
					$fieldid = (int)$customfield["id"];

					if (isset( $account[$fieldname] )) {
						$value = $account[$fieldname];
						$query = sprintf( "DELETE FROM tblcustomfieldsvalues WHERE fieldid=%d AND relid=%d", $fieldid, $tblhostingid );
						full_query( $query );
						$query = sprintf( "INSERT INTO tblcustomfieldsvalues (fieldid,relid,value) VALUES (%d,%d,\"%s\")", $fieldid, $tblhostingid, mysql_real_escape_string( $value ) );
						full_query( $query );
						continue;
					}
				}
			}
		}
	}

	return $system->success ? "success" : $system->error;
}


/**
 * WHMCS package change.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string
 */
function centovacast_ChangePackage($params) {
	$serverpassword = centovacast_GetServerCredentials( $params, true )[1];
	$serverusername = centovacast_GetServerCredentials( $params, true )[0];
	$username = $params["username"];
	$password = $params["password"];

	if (false === $ccurl = centovacast_GetCCURL( $params, &$urlerror )) {
		return $urlerror;
	}

	$server = new CCServerAPIClient( $ccurl );
	$arguments = array();
	$server->call( "getaccount", $username, $serverpassword, $arguments );

	if (!$server->success) {
		return $server->error;
	}


	if (( !is_array( $server->data ) || !count( $server->data ) )) {
		return "Error fetching account information from Centova Cast";
	}

	$account = $server->data["account"];

	if (( !is_array( $account ) || !isset( $account["username"] ) )) {
		return "Account does not exist in Centova Cast";
	}

	$error = centovacast_GetAPIArgs( $params, &$account );

	if (is_string( $error )) {
		return $error;
	}

	unset( $account["template"] );
	$server->call( "reconfigure", $username, $serverpassword, $account );
	logModuleCall( "centovacast", "changepackage", $server->raw_request, $server->raw_response, NULL, NULL );
	return $server->success ? "success" : $server->error;
}


/**
 * WHMCS account termination.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string
 */
function centovacast_TerminateAccount($params) {
	$serverpassword = centovacast_GetServerCredentials( $params )[1];
	$serverusername = centovacast_GetServerCredentials( $params )[0];
	$username = $params["username"];

	if (false === $ccurl = centovacast_GetCCURL( $params, &$urlerror )) {
		return $urlerror;
	}

	$system = new CCSystemAPIClient( $ccurl );
	$arguments = array( "username" => $username );
	$system->call( "terminate", $serverpassword, $arguments );
	logModuleCall( "centovacast", "terminate", $system->raw_request, $system->raw_response, NULL, NULL );
	return $system->success ? "success" : $system->error;
}


/**
 * WHMCS account suspension.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string
 */
function centovacast_SuspendAccount($params) {
	$serverpassword = centovacast_GetServerCredentials( $params )[1];
	$serverusername = centovacast_GetServerCredentials( $params )[0];
	$username = $params["username"];

	if (false === $ccurl = centovacast_GetCCURL( $params, &$urlerror )) {
		return $urlerror;
	}

	$system = new CCSystemAPIClient( $ccurl );
	$arguments = array( "username" => $username, "status" => "disabled" );
	$system->call( "setstatus", $serverpassword, $arguments );
	logModuleCall( "centovacast", "suspend", $system->raw_request, $system->raw_response, NULL, NULL );
	return $system->success ? "success" : $system->error;
}


/**
 * WHMCS account unsuspension.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string
 */
function centovacast_UnsuspendAccount($params) {
	$serverpassword = centovacast_GetServerCredentials( $params )[1];
	$serverusername = centovacast_GetServerCredentials( $params )[0];
	$username = $params["username"];

	if (false === $ccurl = centovacast_GetCCURL( $params, &$urlerror )) {
		return $urlerror;
	}

	$system = new CCSystemAPIClient( $ccurl );
	$arguments = array( "username" => $username, "status" => "enabled" );
	$system->call( "setstatus", $serverpassword, $arguments );
	logModuleCall( "centovacast", "unsuspend", $system->raw_request, $system->raw_response, NULL, NULL );
	return $system->success ? "success" : $system->error;
}


/**
 * WHMCS password change.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string
 */
function centovacast_ChangePassword($params) {
	$serverpassword = centovacast_GetServerCredentials( $params, true )[1];
	$serverusername = centovacast_GetServerCredentials( $params, true )[0];
	$username = $params["username"];
	$password = $params["password"];

	if (false === $ccurl = centovacast_GetCCURL( $params, &$urlerror )) {
		return $urlerror;
	}

	$server = new CCServerAPIClient( $ccurl );
	$arguments = array();
	$server->call( "getaccount", $username, $serverpassword, $arguments );

	if (!$server->success) {
		return $server->error;
	}


	if (( !is_array( $server->data ) || !count( $server->data ) )) {
		return "Error fetching account information from Centova Cast";
	}

	$account = $server->data["account"];

	if (( !is_array( $account ) || !isset( $account["username"] ) )) {
		return "Account does not exist in Centova Cast";
	}

	$account["adminpassword"] = $password;
	$server->call( "reconfigure", $username, $serverpassword, $account );
	logModuleCall( "centovacast", "changepassword", $server->raw_request, $server->raw_response, NULL, NULL );
	return $server->success ? "success" : $server->error;
}


/**
 * WHMCS administration area button array.
 *
 * @api
 *
 * @return array an array of buttons
 */
function centovacast_AdminCustomButtonArray() {
	return array( "Start Stream" => "StartStream", "Stop Stream" => "StopStream", "Restart Stream" => "RestartStream" );
}


/**
 * WHMCS client area HTML generation.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string the HTML for the client area
 */
function centovacast_ClientArea($params) {

	if (false === $ccurl = centovacast_GetCCURL( $params, &$urlerror )) {
		return $urlerror;
	}

	$username = $params["username"];
	$password = $params["password"];

	if (substr( $ccurl, 0 - 1 ) != "/") {
		$ccurl .= "/";
	}

	$loginurl = $ccurl . "login/index.php";
	$time = time();
	$authtoken = sha1( $username . $password . $time );
	$form = sprintf( "<form method=\"post\" action=\"%s\" target=\"_blank\">" . "<input type=\"hidden\" name=\"username\" value=\"%s\" />" . "<input type=\"hidden\" name=\"password\" value=\"%s\" />" . "<input type=\"submit\" name=\"login\" value=\"%s\" />" . "</form>", $loginurl, $username, $password, "Log in to Centova Cast" );
	$fn = dirname( __FILE__ ) . "/client_area.html";

	if (file_exists( $fn )) {
		$details = preg_replace( "/<!--[\s\S]*?-->/", "", str_replace( array( "[CCURL]", "[USERNAME]", "[TIME]", "[AUTH]" ), array( $ccurl, preg_replace( "/[^a-z0-9_]+/i", "", $username ), $time, $authtoken ), file_get_contents( $fn ) ) );
	}
	else {
		$details = "";
	}

	return $form . $details;
}


/**
 * WHMCS administration area HTML generation.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string the HTML for the administration area
 */
function centovacast_AdminLink($params) {
	$query = "SELECT hostname FROM tblservers WHERE tblservers.ipaddress=\"%s\" AND tblservers.username=\"%s\" AND tblservers.type=\"centovacast\" LIMIT 1";
	$res = centovacast_QueryOneRow( $query, $params["serverip"], $params["serverusername"] );

	if (!$res["hostname"]) {
		return "";
	}

	$params["serverhostname"] = $res["hostname"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];

	if (false === $ccurl = centovacast_GetCCURL( $params, &$urlerror )) {
		return $urlerror;
	}


	if (substr( $ccurl, 0 - 1 ) != "/") {
		$ccurl .= "/";
	}

	$ccurl .= "login/index.php";
	return sprintf( "<form method=\"post\" action=\"%s\" target=\"_blank\">" . "<input type=\"hidden\" name=\"username\" value=\"%s\" />" . "<input type=\"hidden\" name=\"password\" value=\"%s\" />" . "<input type=\"submit\" name=\"login\" value=\"%s\" />" . "</form>", $ccurl, $serverusername, $serverpassword, "Log in to Centova Cast" );
}


/**
 * Changes the state of a Cast streaming server account.
 *
 * @internal
 *
 * @param array   $params   The $params array passed by WHMCS
 * @param string  $newstate One of:
 *       "start" - start the stream
 *       "stop" - stop the stream
 *       "restart" - restart the stream
 *
 * @return string The literal string "success" on success, or an error message on failure.
 */
function centovacast_SetState($params, $newstate) {
	if (!in_array( $newstate, array( "start", "stop", "restart" ) )) {
		return "Invalid state";
	}

	$serverpassword = centovacast_GetServerCredentials( $params, true )[1];
	$serverusername = centovacast_GetServerCredentials( $params, true )[0];
	$username = $params["username"];

	if (false === $ccurl = centovacast_GetCCURL( $params, &$urlerror )) {
		return $urlerror;
	}

	$server = new CCServerAPIClient( $ccurl );
	$arguments = array();
	$server->call( $newstate, $username, $serverpassword, $arguments );
	logModuleCall( "centovacast", "setstate", $server->raw_request, $server->raw_response, NULL, NULL );
	return $server->success ? "success" : $server->error;
}


/**
 * Start the stream.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string
 */
function centovacast_StartStream($params) {
	return centovacast_SetState( $params, "start" );
}


/**
 * Stop the stream.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string
 */
function centovacast_StopStream($params) {
	return centovacast_SetState( $params, "stop" );
}


/**
 * Restart the stream.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string
 */
function centovacast_RestartStream($params) {
	return centovacast_SetState( $params, "restart" );
}


/**
 * WHMCS account usage update.
 *
 * @api
 *
 * @param array   $params The $params array passed by WHMCS.
 *
 * @return string
 */
function centovacast_UsageUpdate($params) {
	$serverpassword = centovacast_GetServerCredentials( $params )[1];
	$serverusername = centovacast_GetServerCredentials( $params )[0];

	if (false === $ccurl = centovacast_GetCCURL( $params, &$urlerror )) {
		return $urlerror;
	}

	$system = new CCSystemAPIClient( $ccurl );

	if ($_REQUEST["ccmoduledebug"]) {
		$system->debug = true;
	}

	$arguments = array();
	$system->call( "usage", $serverpassword, $arguments );
	logModuleCall( "centovacast", "usageupdate", $system->raw_request, $system->raw_response, NULL, NULL );

	if (!$system->success) {
		return $system->error;
	}


	if (( !is_array( $system->data ) || !count( $system->data ) )) {
		return "Error fetching account information from Centova Cast";
	}

	$accounts = $system->data["row"];

	if (( !is_array( $accounts ) || !count( $accounts ) )) {
		return "No accounts in Centova Cast";
	}

	$serverid = $params["serverid"];
	foreach ($accounts as $k => $values) {
		update_query( "tblhosting", array( "diskused" => $values["diskusage"], "disklimit" => max( 0, $values["diskquota"] ), "bwused" => $values["transferusage"], "bwlimit" => max( 0, $values["transferlimit"] ), "lastupdate" => "now()" ), array( "server" => $serverid, "username" => $values["username"] ) );
	}

	return "success";
}


require_once dirname( __FILE__ ) . "/class_APIClient.php";
define( "CC_TXT_MAXCLIENTS", "Max listeners" );
define( "CC_TXT_MAXBITRATE", "Max bit rate" );
define( "CC_TXT_XFERLIMIT", "Data transfer limit" );
define( "CC_TXT_DISKQUOTA", "Disk quota" );
define( "CC_TXT_MAXBW", "Max bandwidth" );
define( "CC_TXT_MAXACCT", "Max accounts" );
define( "CC_TXT_MOUNTLIMIT", "Mount point limit" );
?>