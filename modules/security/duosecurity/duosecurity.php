<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

class WHMCS_DuoSecurity {
	function sign_vals($key, $vals, $prefix, $expire) {
		$exp = time() + $expire;
		$val = $vals . "|" . $exp;
		$b64 = base64_encode( $val );
		$cookie = $prefix . "|" . $b64;
		$sig = hash_hmac( "sha1", $cookie, $key );
		return $cookie . "|" . $sig;
	}


	function parse_vals($key, $val, $prefix) {
		$ts = time();
		list($u_prefix,$u_b64,$u_sig) = explode( "|", $val );
		$sig = hash_hmac( "sha1", $u_prefix . "|" . $u_b64, $key );

		if (hash_hmac( "sha1", $sig, $key ) != hash_hmac( "sha1", $u_sig, $key )) {
			return null;
		}


		if ($u_prefix != $prefix) {
			return null;
		}

		list($user, $ikey, $exp) = explode('|', base64_decode($u_b64));

		if (intval( $exp ) <= $ts) {
			return null;
		}

		return $user;
	}


	function signRequest($ikey, $skey, $akey, $username) {
		if (( !isset( $username ) || strlen( $username ) == 0 )) {
			return ERR_USER;
		}


		if (( !isset( $ikey ) || strlen( $ikey ) != IKEY_LEN )) {
			return ERR_IKEY;
		}


		if (( !isset( $skey ) || strlen( $skey ) != SKEY_LEN )) {
			return ERR_SKEY;
		}


		if (( !isset( $akey ) || strlen( $akey ) < AKEY_LEN )) {
			return ERR_AKEY;
		}

		$vals = $username . "|" . $ikey;
		$duo_sig = self::sign_vals( $skey, $vals, DUO_PREFIX, DUO_EXPIRE );
		$app_sig = self::sign_vals( $akey, $vals, APP_PREFIX, APP_EXPIRE );
		return $duo_sig . ":" . $app_sig;
	}


	function verifyResponse($ikey, $skey, $akey, $sig_response) {
		list($auth_sig, $app_sig) = explode(':', $sig_response);
		$auth_user = self::parse_vals( $skey, $auth_sig, AUTH_PREFIX );
		$app_user = self::parse_vals( $akey, $app_sig, APP_PREFIX );

		if ($auth_user != $app_user) {
			return null;
		}

		return $auth_user;
	}


}


function duosecurity_config() {
	global $licensing;

	$licensedata = $licensing->getKeyData( "configoptions" );
	$duouserlimit = (array_key_exists( "Duo Security", $licensedata ) ? $licensedata["Duo Security"] : 0);
	$usercount = get_query_val( "tblclients", "COUNT(id)", array( "authmodule" => "duosecurity" ) ) + get_query_val( "tbladmins", "COUNT(id)", array( "authmodule" => "duosecurity" ) );
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Duo Security" ), "Description" => array( "Type" => "System", "Value" => "Duo Security enables your users to secure their logins using their smartphones. Authentication options include push notifications, passcodes, text messages and/or phone calls.<br /><br />For more information about Duo Security, please <a href=\"http://go.whmcs.com/110/duo-security\" target=\"_blank\">click here</a>." . (0 < $duouserlimit ? "" : "<br /><br /><strong>Starts from just $3/per user/per month</strong>") ), "Licensed" => array( "Type" => "System", "Value" => (0 < $duouserlimit ? true : false) ), "SubscribeLink" => array( "Type" => "System", "Value" => "http://go.whmcs.com/110/duo-security" ), "UserLimit" => array( "Type" => "System", "Value" => $duouserlimit ), "User Limit" => array( "Type" => "Info", "Description" => $usercount . "/" . $duouserlimit . " - <a href=\"http://go.whmcs.com/122/buy-duo-security\" target=\"_blank\">Click here to buy more</a>" ) );
	return $configarray;
}


function duosecurity_activate($params) {
	global $licensing;

	$licensedata = $licensing->getKeyData( "configoptions" );
	$duouserlimit = (array_key_exists( "Duo Security", $licensedata ) ? $licensedata["Duo Security"] : 0);
	$usercount = get_query_val( "tblclients", "COUNT(id)", array( "authmodule" => "duosecurity" ) ) + get_query_val( "tbladmins", "COUNT(id)", array( "authmodule" => "duosecurity" ) );

	if ($duouserlimit == 0) {
		if (defined( "ADMINAREA" )) {
			return "<h2>DuoSecurity Activation Problem</h2><p>This WHMCS license has not had Duo Security Users purchased yet. To buy more, please navigate to Setup > Staff Management > Two-Factor Authentication.</p><br /><p align=\"center\"><input type=\"button\" value=\"Close Window\" onclick=\"dialogClose()\" /></p>";
		}

		return "<h2>DuoSecurity Activation Problem</h2><p>Error Code 101. Cannot continue. Please contact support.</p><br /><p align=\"center\"><input type=\"button\" value=\"Close Window\" onclick=\"dialogClose()\" /></p>";
	}


	if ($duouserlimit <= $usercount) {
		if (defined( "ADMINAREA" )) {
			return "<h2>DuoSecurity Activation Problem</h2><p>This WHMCS license has reached the allowed number of Duo Security users.</p><p>Please contact the system administrator.</p><br /><p align=\"center\"><input type=\"button\" value=\"Close Window\" onclick=\"dialogClose()\" /></p>";
		}

		return "<h2>DuoSecurity Activation Problem</h2><p>Error Code 102. Cannot continue. Please contact support.</p><br /><p align=\"center\"><input type=\"button\" value=\"Close Window\" onclick=\"dialogClose()\" /></p>";
	}

	return array( "completed" => true, "msg" => "You will be asked to configure your Duo Security Two-Factor Authentication the next time you login." );
}


function duosecurity_challenge($params) {
	global $whmcs;

	$appsecretkey = sha1( "Duo" . $whmcs->get_hash() );
	$adminid = $params["user_info"]["id"];
	$username = $params["user_info"]["username"];
	$email = $params["user_info"]["email"];
	$integrationkey = "DILXRHE92017KPRVVM4T";
	$secretkey = "lUQE5dQlJn69ime5PtWJ8f8A0oMjmVXZY6wA5tqT";
	$apihostname = "api-3ce575d8.duosecurity.com";
	$uid = $username . ":" . $email . ":" . $whmcs->get_license_key();
	$sig_request = WHMCS_DuoSecurity::signrequest( $integrationkey, $secretkey, $appsecretkey, $uid );

	if ($sig_request != null) {
		$output = "<script src=\"" . (defined( "ADMINAREA" ) ? "../" : "") . "modules/security/duosecurity/Duo-Web-v1.min.js\"></script>
<script>
  Duo.init({
    \"host\": \"" . $apihostname . "\",
    \"sig_request\": \"" . $sig_request . "\",
    \"post_action\": \"dologin.php\"
  });
</script>
<iframe id=\"duo_iframe\" width=\"100%\" height=\"500\" frameborder=\"0\"></iframe>";
	}
	else {
		$output = "There is an error with the DuoSecurity module configuration. Please try again.";
	}

	return $output;
}


function duosecurity_verify($params) {
	global $whmcs;

	$appsecretkey = sha1( "Duo" . $whmcs->get_hash() );
	$integrationkey = "DILXRHE92017KPRVVM4T";
	$secretkey = "lUQE5dQlJn69ime5PtWJ8f8A0oMjmVXZY6wA5tqT";
	$apihostname = "api-3ce575d8.duosecurity.com";

	if (WHMCS_DuoSecurity::verifyresponse( $integrationkey, $secretkey, $appsecretkey, $_POST["sig_response"] )) {
		return true;
	}

	return false;
}


?>