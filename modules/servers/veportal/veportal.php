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

function veportal_ConfigOptions() {
	if (!mysql_num_rows( full_query( "SHOW TABLES LIKE 'mod_veportal'" ) )) {
		full_query( "CREATE TABLE `mod_veportal` (
`id` INT( 250 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`relid` TEXT NULL ,
`veid` TEXT NULL ,
`hostname` TEXT NULL ,
`ipad` TEXT NULL ,
`lastmod` TEXT NULL
) ENGINE = MYISAM " );
	}

	$configarray = array( "Package ID" => array( "Type" => "text", "Size" => "25" ), "UBC Set ID" => array( "Type" => "text", "Size" => "25" ), "Welcome Email" => array( "Type" => "yesno", "Description" => "Send vePortal Welcome eMail (Reccomended)" ) );
	return $configarray;
}


$get = function veportal_getAdminUsername($id) {;
	mysql_fetch_array( $get );
	$r = full_query( "SELECT * FROM tbladmins WHERE id = " . (int)$id );
	return $r['username'];
}


$get = function veportal_updatePackageNotes($id, $cmd, $newnotes) {;
	$r = mysql_fetch_array( $get );
	$previous = full_query( "SELECT * FROM tblhosting WHERE id = " . (int)$id );
	$date = date( "d/m/y @ H:i:s" );
	veportal_getAdminUsername( $_SESSION['adminid'] );
	$username = $r['notes'];
	$new = "" . $previous . ( "
-----------------------------------------------
Date: " . $date . " User: " . $username . "
-----------------------------------------------
Module Command: " . $cmd . "
" . $newnotes );

	if (!( full_query( "UPDATE tblhosting SET notes='" . db_escape_string( $new ) . "' WHERE id=" . (int)$id ))) {
		exit( mysql_error() );
		(bool)true;
	}

}


function veportal_getPackageFieldID($field, $pid) {
	$get = full_query( "SELECT * FROM tblcustomfields WHERE relid = '" . (int)$pid . "' AND fieldname = '" . db_escape_string( $field ) . "'" );
	$r = mysql_fetch_array( $get );
	return $r['id'];
}


function veportal_getPackageFields($params) {
	$fieldid['hostname'] = veportal_getPackageFieldID( "Hostname", $params['pid'] );
	$fieldid['veid'] = veportal_getPackageFieldID( "VEID", $params['pid'] );
	$fieldid['ipad'] = veportal_getPackageFieldID( "IP", $params['pid'] );
	return $fieldid;
}


function veportal_updateCustomData($params, $veid, $ip, $hostname) {
	$cfield = veportal_getPackageFields( $params );

	if (!( full_query( "UPDATE tblcustomfieldsvalues SET value='" . db_escape_string( $veid ) . "' WHERE fieldid='" . (int)$cfield['veid'] . "' AND relid = '" . (int)$params['serviceid'] . "'" ))) {
		exit( mysql_error() );
		(bool)true;
	}


	if (!( full_query( "UPDATE tblcustomfieldsvalues SET value='" . db_escape_string( $hostname ) . "' WHERE fieldid='" . (int)$cfield['hostname'] . "' AND relid = '" . (int)$params['serviceid'] . "'" ))) {
		exit( mysql_error() );
		(bool)true;
	}


	if (!( full_query( "UPDATE tblcustomfieldsvalues SET value='" . db_escape_string( $ip ) . "' WHERE fieldid='" . (int)$cfield['ipad'] . "' AND relid = '" . (int)$params['serviceid'] . "'" ))) {
		exit( mysql_error() );
		(bool)true;
	}

}


function veportal_updateVPSinfo($veid, $ip, $hostname, $serviceid, $params) {
	if (empty( $hostname )) {
		$hostname = $params['domain'];
	}


	if (!( full_query( "UPDATE tblhosting SET domain='" . db_escape_string( $hostname ) . "', dedicatedip='" . db_escape_string( "DO NOT EDIT THESE VALUES;veid=" . $veid . ";ip=" . $ip . ";hostname=" . $hostname ) . "' WHERE id='" . (int)$serviceid . "'" ))) {
		exit( mysql_error() );
		(bool)true;
	}

	veportal_updateCustomData( $params, $veid, $ip, $hostname );
}


function veportal_changeServiceStatus($id, $status) {
	if (!( full_query( "UPDATE tblhosting SET domainstatus='" . db_escape_string( $status ) . "' WHERE id=" . (int)$id ))) {
		exit( mysql_error() );
		(bool)true;
	}

}


function veportal_getUniqueCode($length) {
	$code = md5( uniqid( rand(), true ) );

	if ($length != "") {
		return substr( $code, 0, $length );
	}

	return $code;
}


function veportal_generateUsername($domain, $id) {
	$domain = str_replace( ".", "", $domain );
	$domain = str_replace( "-", "", $domain );
	$domain = str_replace( "_", "", $domain );
	$hash = veportal_getUniqueCode( "5" );
	$username = "" . $domain['0'] . "" . $domain['1'] . "" . $domain['2'] . "" . $domain['3'] . "" . $domain['4'] . ( "" . $hash );

	if (!( full_query( "UPDATE tblhosting SET username='" . db_escape_string( $username ) . "' WHERE id=" . (int)$id ))) {
		exit( mysql_error() );
		(bool)true;
	}

	return $username;
}


function veportal_getvePortalAccountInfo($serviceid) {
	$get = full_query( "SELECT * FROM mod_veportal WHERE relid = " . (int)$serviceid );
	$r = mysql_fetch_array( $get );
	$params['veid'] = $r['veid'];
	$params['hostname'] = $r['hostname'];
	$params['ipaddress'] = $r['ipad'];
	return $params;
}


function veportal_processAPI($api, $postfields, $params) {
	$api['user'] = $params['serverusername'];
	$api['key'] = $params['serverpassword'];
	$api['sslmode'] = $params['serversecure'];
	$api['host'] = $params['serverip'];
	$postfields['apikey'] = $api['key'];
	$postfields['apiuser'] = $api['user'];
	$postfields['apifunc'] = $api['function'];

	if ($api['sslmode'] != "on") {
		$url = "http://" . $api['host'] . ":2407/api.php";
	}
	else {
		$url = "https://" . $api['host'] . ":2408/api.php";
	}

	$query_string = http_build_query( $postfields );
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
	$data = curl_exec( $ch );
	curl_close( $ch );
	$data = explode( ";", $data );
	foreach ($data as $temp) {
		$temp = explode( "=", $temp );
		$results[$temp[0]] = $temp[1];
	}

	return $results;
}


function veportal_CreateAccount($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$serviceid = $params['serviceid'];
	$pid = $params['pid'];
	$domain = $params['domain'];
	$username = $params['username'];
	$password = $params['password'];
	$clientsdetails = $params['clientsdetails'];
	$customfields = $params['customfields'];
	$configoptions = $params['configoptions'];
	$api['function'] = "newacct";
	$post['package'] = $params['configoption1'];
	$post['ubcset'] = $params['configoption2'];
	$post['welcomeemail'] = $params['configoption13'];
	$post['ostemplate'] = $params['configoptions']["OS Template"];
	$post['email'] = $params['clientsdetails']['email'];
	$post['hostname'] = $params['customfields']['Hostname'];
	$post['server'] = "localhost";
	$post['ippool'] = "any";
	$post['password'] = $params['password'];
	$post['username'] = veportal_generateUsername( $post['hostname'], $serviceid );
	$apiResult = veportal_processAPI( $api, $post, $params );

	if ($apiResult['return'] == "error") {
		veportal_updatePackageNotes( $pid, "Create Account", "Failed Account Creation" );

		if ($apiResult['problem'] == "useridtaken") {
			$result = "Username Taken!";
		}
		else {
			if ($apiResult['problem'] == "wrongip") {
				$result = "Incorrect API IP";
			}
			else {
				if ($apiResult['problem'] == "wrongkey") {
					$result = "Incorrect API Key";
				}
				else {
					if ($apiResult['problem'] == "wrongrskey") {
						$result = "Incorrect API Key For Reseller";
					}
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
		$cfield = veportal_getPackageFields( $params );
		full_query( "DELETE FROM tblcustomfieldsvalues WHERE fieldid = '" . (int)$cfield['hostname'] . "' AND relid = '" . (int)$params['serviceid'] . "'" );
		full_query( "DELETE FROM tblcustomfieldsvalues WHERE fieldid = '" . (int)$cfield['veid'] . "' AND relid = '" . (int)$params['serviceid'] . "'" );
		full_query( "DELETE FROM tblcustomfieldsvalues WHERE fieldid = '" . (int)$cfield['ipad'] . "' AND relid = '" . (int)$params['serviceid'] . "'" );
		full_query( "INSERT INTO tblcustomfieldsvalues (fieldid, relid, value)
        VALUES ('" . (int)$cfield['veid'] . "', '" . (int)$params['serviceid'] . "', '--Not Populated--')" );
		full_query( "INSERT INTO tblcustomfieldsvalues (fieldid, relid, value)
        VALUES ('" . (int)$cfield['hostname'] . "', '" . (int)$params['serviceid'] . "', '--Not Populated--')" );
		full_query( "INSERT INTO tblcustomfieldsvalues (fieldid, relid, value)
        VALUES ('" . (int)$cfield['ipad'] . "', '" . (int)$params['serviceid'] . "', '--Not Populated--')" );
		veportal_updatePackageNotes( $serviceid, "Create Account", "Created VEID: " . $apiResult['veid'] . "" );
		veportal_changeServiceStatus( $serviceid, "Active" );
		veportal_updateVPSinfo( $apiResult['veid'], $apiResult['ipad'], $post['hostname'], $serviceid, $params );
		full_query( "INSERT INTO mod_veportal (relid, veid, hostname, ipad, lastmod)
        VALUES ('" . (int)$serviceid . "', '" . db_escape_string( $apiResult['veid'] ) . "', '" . db_escape_string( $apiResult['hostname'] ) . "', '" . db_escape_string( $apiResult['ipad'] ) . "', '" . time() . "')" );
	}

	return $result;
}


function veportal_TerminateAccount($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "destroyacct";
	$post['veid'] = $params['veid'];
	$apiResult = veportal_processAPI( $api, $post, $params );
	full_query( "DELETE FROM mod_veportal WHERE relid=" . (int)$params['serviceid'] );
	veportal_updatePackageNotes( $params['serviceid'], "Terminate Account", "Terminated Account" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_SuspendAccount($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "suspendacct";
	$post['veid'] = $params['veid'];
	$post['username'] = $params['username'];
	$apiResult = veportal_processAPI( $api, $post, $params );
	veportal_updatePackageNotes( $params['serviceid'], "Suspend Account", "Suspended Account" );
	veportal_changeServiceStatus( $params['serviceid'], "Suspended" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_UnsuspendAccount($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "unsuspendacct";
	$post['veid'] = $params['veid'];
	$post['username'] = $params['username'];
	$apiResult = veportal_processAPI( $api, $post, $params );
	veportal_updatePackageNotes( $params['serviceid'], "Unsuspend Account", "Unsuspended Account" );
	veportal_changeServiceStatus( $params['serviceid'], "Active" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_ChangePassword($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "changepass";
	$post['newpass'] = $params['password'];
	$post['username'] = $params['username'];
	$post['veid'] = $params['veid'];
	$apiResult = veportal_processAPI( $api, $post, $params );
	veportal_updatePackageNotes( $params['serviceid'], "Change Password", "Account password changed to " . $params['password'] . "" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_ChangePackage($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	veportal_updateVPSinfo( $params['veid'], $params['ipaddress'], $params['hostname'], $serviceid, $params );
	$api['function'] = "upgradevps";
	$post['veid'] = $params['veid'];
	$post['package'] = $params['configoption1'];
	$post['ubcset'] = $params['configoption2'];
	$apiResult = veportal_processAPI( $api, $post, $params );
	veportal_updatePackageNotes( $params['serviceid'], "Package Upgrade", "Account package upgraded" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_ClientArea($params) {
	global $_LANG;

	if ($params['username'] != "") {
		$code = "<a href=http://" . $params['serverip'] . ":2407/login.php?user=" . $params['username'] . "&pass=" . $params['password'] . ">" . $_LANG['veportallogin'] . "</a>";
	}
	else {
		$code = "<s>" . $_LANG['veportallogin'] . "</s>";
	}

	return $code;
}


function veportal_AdminLink($params) {
	$code = "<form action=http://" . $params['serverip'] . ":2407/login.php method=\"post\" target=\"_blank\">
<input type=\"hidden\" name=\"username\" value=\"" . $params['serverusername'] . "\" />
<input type=\"submit\" value=\"Login to vePortal\" />
</form>";
	return $code;
}


function veportal_LoginLink($params) {
	if ($params['username'] != "") {
		$code = "<a href=\"http://" . $params['serverip'] . ":2407/login.php?user=" . $params['username'] . "&pass=" . $params['password'] . "\"class=\"moduleloginlink\">Login to vePortal as <b>" . $params['username'] . "</b></a>";
	}
	else {
		$code = "<s>Login to vePortal</s>";
	}

	return $code;
}


function veportal_AdminCustomButtonArray() {
	$buttonarray = array( "Change Username" => "chusername", "Start VPS" => "startvps", "Stop VPS" => "stopvps", "Reboot VPS" => "rebootvps", "Backup VPS" => "backupvps", "Reload VPS OS" => "reloadvps", "Update Resource Usage" => "updateusage" );
	return $buttonarray;
}


function veportal_reloadvps($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "reloadvpsos";
	$post['veid'] = $params['veid'];
	$post['rootpass'] = $params['configoptions']["OS Template"];
	$post['ostemplate'] = $params['password'];
	$apiResult = veportal_processAPI( $api, $post, $params );
	veportal_updatePackageNotes( $params['serviceid'], "Reload VPS OS", "VPS OS Reloaded" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_backupvps($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "backupvps";
	$post['veid'] = $params['veid'];
	$apiResult = veportal_processAPI( $api, $post, $params );
	veportal_updatePackageNotes( $params['serviceid'], "Backup VPS", "VPS Backup Creation" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_startvps($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "commandvps";
	$post['veid'] = $params['veid'];
	$post['command'] = "start";
	$apiResult = veportal_processAPI( $api, $post, $params );
	veportal_updatePackageNotes( $params['serviceid'], "Start VPS", "VPS Started" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_stopvps($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "commandvps";
	$post['veid'] = $params['veid'];
	$post['command'] = "stop";
	$apiResult = veportal_processAPI( $api, $post, $params );
	veportal_updatePackageNotes( $params['serviceid'], "Stop VPS", "VPS Stopped" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_rebootvps($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "commandvps";
	$post['veid'] = $params['veid'];
	$post['command'] = "restart";
	$apiResult = veportal_processAPI( $api, $post, $params );
	veportal_updatePackageNotes( $params['serviceid'], "Reboot VPS", "VPS Restarted" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_chusername($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "chusername";
	$post['veid'] = $params['veid'];
	$post['username'] = $params['username'];
	$apiResult = veportal_processAPI( $api, $post, $params );
	veportal_updatePackageNotes( $params['serviceid'], "Change Username", "vePortal Username Changed To " . $post['username'] . "" );

	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


function veportal_updateusage($params) {
	$paramsb = veportal_getvePortalAccountInfo( $params['serviceid'] );
	$params = array_merge( $params, $paramsb );
	$api['function'] = "getvmusage";
	$post['veid'] = $params['veid'];
	$apiResult = veportal_processAPI( $api, $post, $params );
	$hdd = $apiResult['hdd'] * 1024;
	$hdd = number_format( $hdd, 0, ".", "" );
	$bw = $apiResult['bw'] * 1024;
	$bw = number_format( $bw, 0, ".", "" );
	$get = full_query( "SELECT * FROM tblhosting WHERE id = " . (int)$params['serviceid'] );
	$r = mysql_fetch_array( $get );
	$currentbw = $r['bwusage'];
	$currenthdd = $r['diskusage'];
	$hdd = $currenthdd + $hdd;
	$bw = $currentbw + $bw;

	if (!( full_query( "UPDATE tblhosting SET bwusage='" . $bw . "', diskusage='" . $hdd . "' WHERE id=" . (int)$params['serviceid'] ))) {
		exit( mysql_error() );
		(bool)true;
	}


	if ($apiResult['return'] == "error") {
		if ($apiResult['problem'] == "wrongip") {
			$result = "Incorrect API IP";
		}
		else {
			if ($apiResult['problem'] == "wrongkey") {
				$result = "Incorrect API Key";
			}
			else {
				if ($apiResult['problem'] == "nolicense") {
					$result = "vePortal Node Not Licensed. <b>Visit <a href='http://www.veportal.com/'>vePortal</a> To Purchase a License</b>";
				}
			}
		}
	}
	else {
		$successful = true;
		$result = "success";
	}

	return $result;
}


?>