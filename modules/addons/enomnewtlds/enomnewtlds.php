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

function enomnewtlds_config() {
	global $enomnewtlds_CurrentVersion;

	$configarray = array( "name" => "eNom New TLDs", "description" => "Earn commissions offering New TLDs services to your customers.  This addon includes eNom's New TLDs Watchlist and order processing for New TLDs launch phases including: Sunrise, Landrush, and Pre-Registration. Learn more at http://www.enom.com/r/01.aspx", "version" => $enomnewtlds_CurrentVersion, "author" => "eNom", "language" => "english", "fields" => array() );
	return $configarray;
}


function enomnewtlds_activate($vars) {
	global $enomnewtlds_DefaultEnvironment;
	global $enomnewtlds_DBName;

	$LANG = $vars['_lang'];
	$sql = enomnewtlds_DB_GetCreateTable();
	$retval = full_query( $sql );

	if (!$retval) {
		return array( "status" => "error", "description" => $LANG['activate_failed1'] . $enomnewtlds_DBName . " : " . mysql_error() );
	}

	$companyname = "";
	$domain = "";
	$date = enomnewtlds_Helper_GetDateTime();
	$data = enomnewtlds_DB_GetDefaults();
	$domain = enomnewtlds_Helper_GetWatchlistUrl( $data['companyurl'] );
	insert_query( $enomnewtlds_DBName, array( "enabled" => "1", "configured" => "0", "environment" => $enomnewtlds_DefaultEnvironment, "companyname" => $data['companyname'], "companyurl" => $domain, "supportemail" => $data['supportemail'], "enableddate" => $date ) );
	enomnewtlds_DB_GetCreateHookTable();
	return array( "status" => "success", "description" => $LANG['activate_success1'] );
}


function enomnewtlds_deactivate($vars) {
	global $enomnewtlds_errormessage;
	global $enomnewtlds_DBName;
	global $enomnewtlds_CronDBName;

	$fields = array();
	$fields['statusid'] = "0";
	$LANG = $vars['_lang'];
	$data = enomnewtlds_DB_GetWatchlistSettingsLocal();
	$wlenabled = $data['enabled'];
	$wlconfigured = $data['configured'];
	$portalid = $data['portalid'];

	if ( ( $wlenabled && $wlconfigured ) || 0 < (int)$portalid ) {
		$success = enomnewtlds_API_UpdatePortalAccount( $vars, $portalid, $fields );

		if (!$success) {
		}
	}


	if (enomnewtlds_DB_TableExists()) {
		$sql = "DROP TABLE `" . $enomnewtlds_DBName . "`;";
		$retval = full_query( $sql );
	}
	else {
		$retval = 0;
	}


	if (!$retval) {
		return array( "status" => "error", "description" => $LANG['deactivate_failed2'] . ":  " . mysql_error() );
	}


	if (enomnewtlds_DB_HookTableExists()) {
		$sql = "DROP TABLE `" . $enomnewtlds_CronDBName . "`;";
		$retval = full_query( $sql );
	}

	return array( "status" => "success", "description" => $LANG['deactivate_success1'] );
}


function enomnewtlds_upgrade($vars) {
	$version = $vars['version'];
	global $enomnewtlds_CurrentVersion;

	if ($version < 1.10000000000000008881784) {
	}


	if ($version < 1.19999999999999995559108) {
	}

}


function enomnewtlds_clientarea($vars) {
	global $enomnewtlds_errormessage;
	global $enomnewtlds_mysalt;
	global $enomnewtlds_isbundled;

	$token = "";
	$modulelink = $vars['modulelink'];
	$version = $vars['version'];
	$LANG = $vars['_lang'];
	$pversion = ($enomnewtlds_isbundled ? "bundled" : "nonbundled") . " version " . $version;
	$userid = $_SESSION['uid'];

	if ($userid) {

		if (!( $query = full_query( "SELECT email FROM tblclients WHERE id=" . (int)$userid ))) {
			exit( "There was a problem with the SQL query: " . mysql_error() );
			(bool)true;
		}

		$data = mysql_fetch_array( $query );
		$email = $data[0];

		if (!$email) {
			enomnewtlds_AddError( $LANG['noemail'] );
		}
		else {
			if (!$enomnewtlds_mysalt) {
				enomnewtlds_AddError( $LANG['nosalt'] );
			}
			else {
				$code = hash( "sha512", $email . $enomnewtlds_mysalt );
				$password = substr( $code, 0, 15 );
			}
		}
	}
	else {
		enomnewtlds_AddError( $LANG['notloggedin'] );
	}

	$data = enomnewtlds_DB_GetWatchlistSettingsLocal();
	$wlenabled = $data['enabled'];
	$portalid = $data['portalid'];
	$environment = $data['environment'];

	if (enomnewtlds_Helper_IsNullOrEmptyString( $portalid )) {
		$portalid = "0";
	}

	$hasportalaccount = 0 < (int)$portalid;
	$success = true;

	if ( $wlenabled && !$hasportalaccount ) {
		enomnewtlds_AddError( $LANG['noportalacct'] );
		$success = false;
	}


	if ( $success && $portalid != "0" ) {
		$linkarray = array( "sitesource" => "whmcs", "embeded" => "1", "ruid" => $data['enomlogin'], "rpw" => $data['enompassword'], "pw" => $password, "portaluserid" => $userid, "email" => $email, "portalid" => $portalid );
		$success = enomnewtlds_API_GetPortalToken( $vars, $token, $linkarray );
	}
	else {
		if ($portalid == "0") {
			enomnewtlds_AddError( $LANG['noportalaccount'] );
		}
		else {
			if (!$success) {
			}
		}
	}

	$varsArray = array( "NEWTLDS_HASH" => $code, "WHMCS__EMAIL" => $email, "NEWTLDS_PASSWORD" => $password, "RESELLER_UID" => $data['enomlogin'], "RESELLER_PW" => $data['enompassword'], "NEWTLDS_ENABLED" => $wlenabled, "NEWTLDS_PORTALACCOUNT" => $hasportalaccount, "NEWTLDS_LINK" => $token, "WHMCS_CUSTOMERID" => $userid, "PORTAL_ID" => $portalid, "NEWTLDS_ERRORS" => $enomnewtlds_errormessage, "NEWTLDS_NOPORTALACCT" => $LANG['noportalaccount'], "NEWTLDS_NOTENABLED" => $LANG['headertext'], "NEWTLDS_NOTCONFIGURED" => $LANG['notconfigured'], "NEWTLDS_NOTLOGGEDIN" => $LANG['notloggedin'], "NEWTLDS_URLHOST" => enomnewtlds_Helper_GetWatchlistHost( $environment ), "NEWTLDS_PLUGINVERSION" => $pversion );
	return array( "pagetitle" => $LANG['pagetitle'], "breadcrumb" => array( $modulelink => $LANG['pagetitle'] ), "templatefile" => "enomnewtlds", "requirelogin" => true, "requiressl" => true, "vars" => $varsArray );
}


function enomnewtlds_NEWTLDS_sidebar($vars) {
	$modulelink = $vars['modulelink'];
	$LANG = $vars['_lang'];
	$sidebar = "<span class=\"header\"><img src=\"images/icons/addonmodules.png\" class=\"absmiddle\" width=\"16\" height=\"16\" />" . $LANG['intro'] . "</span>
    <ul class=\"menu\">
        <li><a href=\"#\">" . $LANG['intro'] . "</a></li>
        <li><a href=\"#\">Version: " . $vars['version'] . "</a></li>
    </ul>";
	return $sidebar;
}


function enomnewtlds_DB_GetCreateTable() {
	global $enomnewtlds_DefaultEnvironment;
	global $enomnewtlds_DBName;

	$sql = "CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( $enomnewtlds_DBName ) . "` (
            `id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `enabled` INT( 1 ) NOT NULL DEFAULT '0' ,
            `configured` INT( 1 ) NOT NULL DEFAULT '0' ,
            `portalid` MEDIUMINT( 18 ) NOT NULL DEFAULT '0' ,
            `environment` INT( 1 ) NOT NULL DEFAULT '" . mysql_real_escape_string( $enomnewtlds_DefaultEnvironment ) . "' ,
            `enomlogin` VARCHAR( 272 ) NULL ,
            `enompassword` VARCHAR( 272 ) NULL ,
            `enableddate` VARCHAR( 272 ) NULL ,
            `configureddate` VARCHAR( 272 ) NULL,
            `supportemail` VARCHAR( 387 ) NULL ,
            `companyname` VARCHAR( 387 ) NULL ,
            `companyurl` VARCHAR( 387 ) NULL)
            ENGINE = MYISAM";
	return $sql;
}


function enomnewtlds_DB_GetCreateHookTable() {
	global $enomnewtlds_CronDBName;

	if (!enomnewtlds_DB_HookTableExists()) {
		full_query( "CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( $enomnewtlds_CronDBName ) . "` (
            `id` INT( 100 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `domainname` VARCHAR( 272 ) NOT NULL,
            `domainnameid` INT ( 10 ) NOT NULL,
            `emailaddress` VARCHAR( 272 ) NOT NULL,
            `expdate` VARCHAR( 272 ) NOT NULL,
            `regdate` VARCHAR( 272 ) NOT NULL,
            `userid` VARCHAR( 272 ) NOT NULL ,
            `regprice` VARCHAR( 272 ) NOT NULL ,
            `renewprice` VARCHAR( 272 ) NOT NULL,
            `regperiod` INT( 2 ) NOT NULL DEFAULT  '1' ,
            `provisioned` INT( 1 ) NOT NULL DEFAULT  '0',
            `provisiondate` VARCHAR( 272 ) NULL )
             ENGINE = MYISAM;" );
		full_query( "ALTER TABLE " . mysql_real_escape_string( $enomnewtlds_CronDBName ) . "
              ADD CONSTRAINT UniqueDomainName
                UNIQUE (domainname);" );
	}


	if (!mysql_num_rows( full_query( "select * from `tblconfiguration` where setting='enomnewtlds_cronbatchsize';" ) )) {
		full_query( "insert into `tblconfiguration` (Setting, value) VALUES('enomnewtlds_cronbatchsize', '50');" );
	}

}


function enomnewtlds_DB_GetDefaults() {
	$data = array();
	$data['companyname'] = enomnewtlds_DB_GetDefaultcompanyname();
	$data['companyurl'] = enomnewtlds_DB_GetDefaultDomainName();
	$data['supportemail'] = enomnewtlds_DB_GetDefaultSupportEmail();
	return $data;
}


function enomnewtlds_DB_GetWatchlistSettingsLocal() {
	global $enomnewtlds_DefaultEnvironment;
	global $enomnewtlds_DBName;

	$result = select_query( $enomnewtlds_DBName, "enabled,configured,portalid,environment,enomlogin,enompassword,supportemail,companyname,companyurl", array() );
	$data = mysql_fetch_array( $result );

	if (enomnewtlds_Helper_IsNullOrEmptyString( $data['portalid'] )) {
		$data['portalid'] = "0";
	}


	if (enomnewtlds_Helper_IsNullOrEmptyString( $data['enompassword'] )) {
		$data['enompassword'] = "";
	}
	else {
		$data['enompassword'] = decrypt( $data['enompassword'] );
	}


	if (enomnewtlds_Helper_IsNullOrEmptyString( $data['enomlogin'] )) {
		$data['enomlogin'] = "";
	}
	else {
		$data['enomlogin'] = decrypt( $data['enomlogin'] );
	}


	if (enomnewtlds_Helper_IsNullOrEmptyString( $data['companyname'] )) {
		$data['companyname'] = "";
	}


	if (enomnewtlds_Helper_IsNullOrEmptyString( $data['companyurl'] )) {
		$data['companyurl'] = "";
	}


	if (enomnewtlds_Helper_IsNullOrEmptyString( $data['environment'] )) {
		$data['environment'] = $enomnewtlds_DefaultEnvironment;
	}


	if (enomnewtlds_Helper_IsNullOrEmptyString( $data['supportemail'] )) {
		$data['supportemail'] = "";
	}

	return $data;
}


function enomnewtlds_DB_GetWatchlistPortalExists() {
	if (!enomnewtlds_DB_TableExists()) {
		return false;
	}

	$data = enomnewtlds_DB_GetWatchlistSettingsLocal();

	if (!$data) {
		return false;
	}

	return $data['configured'] == 1;
}


function enomnewtlds_DB_TableExists() {
	if (!mysql_num_rows( full_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( $enomnewtlds_DBName ) . "'" ) )) {
		return false;
	}

	return true;
}


function enomnewtlds_DB_HookTableExists() {
	global $enomnewtlds_CronDBName;

	if (!mysql_num_rows( full_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( $enomnewtlds_CronDBName ) . "'" ) )) {
		return false;
	}

	return true;
}


function enomnewtlds_DB_GetWatchlistIsEnabled() {
	if (!enomnewtlds_DB_TableExists()) {
		return false;
	}

	$data = enomnewtlds_DB_GetWatchlistSettingsLocal();

	if (!$data) {
		return false;
	}

	return $data['enabled'] == 1;
}


function enomnewtlds_DB_UpdateDB($vars, $portalid = "0") {
	global $enomnewtlds_DBName;

	$LANG = $vars['_lang'];
	$companyname = $vars['companyname'];
	$companyurl = $vars['companyurl'];
	$supportemail = $vars['supportemail'];
	$datetime = enomnewtlds_Helper_GetDateTime();

	if (0 < (int)$portalid) {
		update_query( $enomnewtlds_DBName, array( "configured" => "1", "portalid" => $portalid, "configureddate" => $datetime, "companyname" => $companyname, "companyurl" => $companyurl, "supportemail" => $supportemail ), array( "id" => "1" ) );
		return 1;
	}

	update_query( $enomnewtlds_DBName, array( "configured" => "1", "configureddate" => $datetime, "companyname" => $companyname, "companyurl" => $companyurl, "supportemail" => $supportemail ), array( "id" => "1" ) );
	return 2;
}


function enomnewtlds_DB_BootstrapUidPw($enomuid, $enompw) {
	global $enomnewtlds_DBName;

	$datetime = enomnewtlds_Helper_GetDateTime();
	update_query( $enomnewtlds_DBName, array( "configureddate" => $datetime, "enomlogin" => $enomuid, "enompassword" => $enompw ), array( "id" => "1" ) );
}


function enomnewtlds_DB_GetDefaultcompanyname() {
	$result = select_query( "tblconfiguration", "value", array( "setting" => "CompanyName" ) );
	$data = mysql_fetch_array( $result );
	return $data[0];
}


function enomnewtlds_DB_GetDefaultDomainName() {
	$result = select_query( "tblconfiguration", "value", array( "setting" => "SystemURL" ) );
	$data = mysql_fetch_array( $result );
	return $data[0];
}


function enomnewtlds_DB_GetDefaultSupportEmail() {
	$result = select_query( "tblconfiguration", "value", array( "setting" => "Email" ) );
	$data = mysql_fetch_array( $result );
	return $data[0];
}


function enomnewtlds_Helper_GetDateTime() {
	$t = microtime( true );
	$micro = sprintf( "%06d", ( $t - floor( $t ) ) * 1000000 );
	$d = new DateTime( "Y-m-d H:i:s." . $micro, $t );
	return $d->format( "Y-m-d H:i:s" );
}


function enomnewtlds_Helper_IsNullOrEmptyString($str) {
	return ( !isset( $str ) || trim( $str ) === "" ) || strlen( $str ) == 0;
}


function enomnewtlds_Helper_FormatDomain($domainname) {
	$website = preg_replace( '/^(htt|ht|tt)p\:?\/\//i', '', $domainname );

	if (enomnewtlds_Helper_endsWith( $website, "/" )) {
		$length = strlen( $needle );
		$website = substr( $haystack, 0, (0 < $length ? $length - 1 : $length) );
	}

	return $website;
}


function enomnewtlds_Helper_startsWith($haystack, $needle) {
	$length = strlen( $needle );
	return substr( $haystack, 0, $length ) === $needle;
}


function enomnewtlds_Helper_endsWith($haystack, $needle) {
	$length = strlen( $needle );

	if ($length == 0) {
		return true;
	}

	return substr( $haystack, 0 - $length ) === $needle;
}


function enomnewtlds_AddError($error) {
	global $enomnewtlds_errormessage;

	if (enomnewtlds_Helper_IsNullOrEmptyString( $enomnewtlds_errormessage )) {
		$enomnewtlds_errormessage = $error;
		return null;
	}

	$enomnewtlds_errormessage .= "<br />" . $error;
}


function enomnewtlds_Helper_FormatAPICallForEmail($fields, $environment) {
	$url = "https://" . enomnewtlds_Helper_GetAPIHost( $environment ) . "/interface.asp?";
	foreach ($fields as $x => $y) {
		$url .= $x . "=" . $y . "&";
	}

	return $url;
}


function enomnewtlds_Helper_GetAPIHost($environment) {
	switch ($environment) {
	case "1": {
			$url = "resellertest.enom.com";
			break;
		}

	case "2": {
			$url = "api.staging.local";
			break;
		}

	case "3": {
			$url = "api.build.local";
			break;
		}

	case "4": {
			$url = "reseller-sb.enom.com";
			break;
		}

	default: {
			$url = "reseller.enom.com";
			break;
		}
	}

	return $url;
}


function enomnewtlds_Helper_GetDocumentationHost($environment) {
	switch ($environment) {
	case "1": {
			$url = "resellertest.enom.com";
			break;
		}

	case "2": {
			$url = "enom.staging.local";
			break;
		}

	case "3": {
			$url = "enom.build.local";
			break;
		}

	case "4": {
			$url = "enom5.enom.com";
			break;
		}

	default: {
			$url = "www.enom.com";
			break;
		}
	}

	return $url;
}


function enomnewtlds_Helper_GetWatchlistHost($environment) {
	switch ($environment) {
	case "1": {
			$url = "resellertest.tldportal.com";
			break;
		}

	case "2": {
			$url = "tldportal.staging.local";
			break;
		}

	case "3": {
			$url = "tldportal.build.local";
			break;
		}

	case "4": {
			$url = "preprod.tldportal.com";
			break;
		}

	default: {
			$url = "tldportal.com";
			break;
		}
	}

	return $url;
}


function enomnewtlds_Helper_Getenvironment($environment) {
	global $enomnewtlds_DefaultEnvironment;

	if (enomnewtlds_Helper_IsNullOrEmptyString( $environment )) {
		$data = enomnewtlds_DB_GetWatchlistSettingsLocal();
		$environment = $data['environment'];
	}

	return $environment;
}


function enomnewtlds_Helper_GetWatchlistUrl($domain = "") {
	global $enomnewtlds_ModuleName;

	if (enomnewtlds_Helper_IsNullOrEmptyString( $domain )) {
		$data = enomnewtlds_DB_GetDefaults();
		$domain = $data['companyurl'];
	}

	$domain .= (enomnewtlds_Helper_endsWith( $domain, "/" ) ? "index.php?m=" . $enomnewtlds_ModuleName : "/index.php?" . $enomnewtlds_ModuleName);
	return $domain;
}


function enomnewtlds_API_GetPortalToken(&$vars, $token, $fields) {
	$LANG = $vars['_lang'];
	$postfields = array();
	$postfields['command'] = "PORTAL_GETTOKEN";

	if (is_array( $fields )) {
		foreach ($fields as $x => $y) {
			$postfields[$x] = $y;
		}
	}

	$xmldata = enomnewtlds_API_CallEnom( $vars, $postfields );
	$success = $xmldata->ErrCount == 0;

	if ($success) {
		$result = "success";
		$token = $xmldata->token;
		return true;
	}

	$result = enomnewtlds_API_HandleErrors( $xmldata );

	if (!$result) {
		$result = $LANG['api_unknownerror'];
	}

	enomnewtlds_AddError( $result );
	return false;
}


function enomnewtlds_API_HandleErrors($xmldata) {
	$result = "";
	$errcnt = $xmldata->ErrCount;
	$i = 0;

	while ($i <= $errcnt) {
		$result = $xmldata->errors=="Err" . $i;

		if ( $i < $errcnt && 1 < $errcnt ) {
			$result .= "<br />";
		}

		++$i;
	}

	return $result;
}


function enomnewtlds_API_CreatePortalAccount(&$vars, $portalid, $fields) {
	$LANG = $vars['_lang'];
	$postfields = array();
	$postfields['command'] = "PORTAL_CREATEPORTAL";

	if (is_array( $fields )) {
		foreach ($fields as $x => $y) {
			$postfields[$x] = $y;
		}
	}

	enomnewtlds_API_CallEnom( $vars, $postfields );
	$success = $xmldata->ErrCount == 0;

	if ($success) {
		$result = "success";
		$portalid = $xmldata->portalid;

		if (!enomnewtlds_Helper_IsNullOrEmptyString( $portalid )) {
			return true;
		}

		$portalid = "0";
		return false;
	}

	$result = $xmldata = enomnewtlds_API_HandleErrors( $xmldata );

	if (!$result) {
		$result = $LANG['api_unknownerror'];
	}

	enomnewtlds_AddError( $result );
	$portalid = "0";
	return false;
}


function enomnewtlds_API_UpdatePortalAccount($vars, $portalid, $fields) {
	$LANG = $vars['_lang'];
	$postfields = array();
	$postfields['command'] = "PORTAL_UPDATEDETAILS";
	$postfields['PortalAccountID'] = $portalid;

	if (is_array( $fields )) {
		foreach ($fields as $x => $y) {
			$postfields[$x] = $y;
		}
	}

	$xmldata = enomnewtlds_API_CallEnom( $vars, $postfields );
	$success = $xmldata->ErrCount == 0;

	if ($success) {
		return true;
	}

	$result = enomnewtlds_API_HandleErrors( $xmldata );

	if (!$result) {
		$result = $LANG['api_unknownerror'];
	}

	enomnewtlds_AddError( $result );
	return false;
}


function enomnewtlds_API_GetPortalAccount(&$vars, $portalid) {
	$LANG = $vars['_lang'];
	$postfields = array();
	$postfields['command'] = "PORTAL_GETDETAILS";

	if (is_array( $fields )) {
		foreach ($fields as $x => $y) {
			$postfields[$x] = $y;
		}
	}

	enomnewtlds_API_CallEnom( $vars, $postfields );
	$success = $xmldata->ErrCount == 0;

	if ($success) {
		$result = "success";
		$portalid = $xmldata->tldportaldetails->portalid;

		if (enomnewtlds_Helper_IsNullOrEmptyString( $portalid )) {
			$portalid = "0";
		}

		return true;
	}

	$result = $xmldata = enomnewtlds_API_HandleErrors( $xmldata );

	if (!$result) {
		$result = $LANG['api_unknownerror'];
	}

	enomnewtlds_AddError( $result );
	$portalid = "0";
	return false;
}


function enomnewtlds_API_CallEnom($vars, $postfields) {
	global $enomnewtlds_ModuleName;
	global $enomnewtlds_CurrentVersion;

	$LANG = $vars['_lang'];
	$data = enomnewtlds_DB_GetWatchlistSettingsLocal();
	$environment = enomnewtlds_Helper_Getenvironment( $data['environment'] );
	$portalid = $data['portalid'];

	if (!in_array( "uid", $postfields )) {
		$enomuid = $data['enomlogin'];
		$postfields['uid'] = $enomuid;
	}


	if (!in_array( "pw", $postfields )) {
		$enompw = $data['enompassword'];
		$postfields['pw'] = $enompw;
	}


	if (!in_array( "portalid", $postfields )) {
		if ( !enomnewtlds_Helper_IsNullOrEmptyString( $portalid ) && 0 < (int)$portalid ) {
			$postfields['portalid'] = $portalid;
		}
	}

	$postfields['ResponseType'] = "XML";
	$postfields['Source'] = "WHMCS";
	$postfields['sourceid'] = "37";
	$postfields['bundled'] = ($enomnewtlds_isbundled ? 1 : 0);
	$postfields['pluginversion'] = $enomnewtlds_CurrentVersion;
	$url = "https://" . enomnewtlds_Helper_GetAPIHost( $environment ) . "/interface.asp";
	$data = curlCall( $url, $postfields );
	$xmldata = simplexml_load_string( $data );
	logModuleCall( $enomnewtlds_ModuleName, $postfields['command'], $postfields, $data, $xmldata );
	return $xmldata;
}


function enomnewtlds_output($vars) {
	global $enomnewtlds_errormessage;

	$enomnewtlds_errormessage = "";
	global $enomnewtlds_isbundled;
	global $enomnewtlds_CurrentVersion;

	$success_message = "";
	$modulelink = $vars['modulelink'];
	$LANG = $vars['_lang'];
	$data = enomnewtlds_DB_GetWatchlistSettingsLocal();
	$companyname = $data['companyname'];
	$companyurl = $data['companyurl'];
	$enomuid = $data['enomlogin'];
	$enompw = $data['enompassword'];
	$portalid = $data['portalid'];
	$supportemail = $data['supportemail'];
	$environment = enomnewtlds_Helper_Getenvironment( $data['environment'] );
	$configured = enomnewtlds_DB_GetWatchlistPortalExists();
	$form_iframe_tab = ($configured ? 2 : 1);
	$form_button_text = ($configured ? $LANG['form_update'] : $LANG['form_activate']);
	$form_terms_text = ($configured ? $LANG['form_terms2'] : $LANG['form_terms1']);
	$documentation_link = $LANG['documentation'];
	$url = enomnewtlds_Helper_GetDocumentationHost( $environment );

	if ($environment != "0") {
		$documentation_link = str_replace( "www.enom.com", $url, $documentation_link );
		$form_terms_text = str_replace( "www.enom.com", $url, $form_terms_text );
	}

	$create = false;
	$update = false;

	if ( ( enomnewtlds_Helper_IsNullOrEmptyString( $companyname ) || enomnewtlds_Helper_IsNullOrEmptyString( $companyurl ) ) || enomnewtlds_Helper_IsNullOrEmptyString( $supportemail ) ) {
		$data = enomnewtlds_DB_GetDefaults();

		if (enomnewtlds_Helper_IsNullOrEmptyString( $companyname )) {
			$companyname = $data['companyname'];
		}


		if (enomnewtlds_Helper_IsNullOrEmptyString( $companyurl )) {
			$companyurl = enomnewtlds_Helper_GetWatchlistUrl( $data['companyurl'] );
		}


		if (enomnewtlds_Helper_IsNullOrEmptyString( $supportemail )) {
			$supportemail = $data['supportemail'];
		}
	}


	if (isset( $_POST['enomuid'] )) {
		$enomuid = $_POST['enomuid'];
		$enompw = $_POST['enompw'];

		if ($enompw === "************") {
			$enompw = $data['enompassword'];
		}

		$companyname = $_POST['companyname'];
		$companyurl = $_POST['companyurl'];
		$supportemail = $_POST['supportemail'];
		$success = true;

		if (enomnewtlds_Helper_IsNullOrEmptyString( $enomuid )) {
			enomnewtlds_AddError( $LANG['enomuidrequired'] );
			$success = false;
		}


		if (enomnewtlds_Helper_IsNullOrEmptyString( $enompw )) {
			enomnewtlds_AddError( $LANG['enompwdrequired'] );
			$success = false;
		}


		if (enomnewtlds_Helper_IsNullOrEmptyString( $companyname )) {
			$companyname = enomnewtlds_DB_GetDefaultcompanyname();
		}


		if (enomnewtlds_Helper_IsNullOrEmptyString( $companyurl )) {
			$companyurl = enomnewtlds_Helper_GetWatchlistUrl();
		}


		if (enomnewtlds_Helper_IsNullOrEmptyString( $supportemail )) {
			$supportemail = enomnewtlds_DB_GetDefaultSupportEmail();
		}


		if ($success) {
			enomnewtlds_DB_BootstrapUidPw( encrypt( $enomuid ), encrypt( $enompw ) );
			$fields = array();
			$fields['companyurl'] = $companyurl;
			$fields['companyname'] = $companyname;
			$fields['supportemailaddress'] = $supportemail;
			$fields['portalType'] = "2";
			$fields['statusid'] = "1";

			if ( enomnewtlds_Helper_IsNullOrEmptyString( $portalid ) || (int)$portalid <= 0 ) {
				$nofields = array();
				$success = enomnewtlds_API_GetPortalAccount( $vars, &$portalid, $nofields );
			}


			if ($success) {
				if ( enomnewtlds_Helper_IsNullOrEmptyString( $portalid ) || (int)$portalid <= 0 ) {
					$create = true;
					$success = enomnewtlds_API_CreatePortalAccount( $vars, &$portalid, $fields );
				}
				else {
					$update = true;
					$success = enomnewtlds_API_UpdatePortalAccount( $vars, $portalid, $fields );
				}
			}
			else {
				enomnewtlds_AddError( $LANG['api_failedtoget'] );
				$success = false;
			}


			if ( $success && ( $update || $create ) ) {
				$mydata = array();
				$mydata['enomLogin'] = encrypt( $enomuid );
				$mydata['enomPassword'] = encrypt( $enompw );
				$mydata['companyname'] = $companyname;
				$mydata['companyurl'] = $companyurl;
				$mydata['supportemail'] = $supportemail;
				$result = enomnewtlds_DB_UpdateDB( $mydata, $portalid );

				if ($result == 1) {
					$success_message = $LANG['api_setupsuccess'];
				}
				else {
					$success_message = $LANG['api_setupsuccess2'];
				}
			}
			else {
				if ( $create || $update ) {
					enomnewtlds_AddError( ($create ? $LANG['api_failedtocreate'] : $LANG['api_failedtoupdate']) );
				}
			}
		}
	}

	$errormessage = $enomnewtlds_errormessage;
    echo "\n";
    echo "<s";
    echo "cript type=\"text/javascript\" language=\"JavaScript\">\n    $(\"#floatbar\").click(function (e) {\n        e.preventDefault();\n        $(this).find(\".popup\").fadeIn(\"slow\");\n    });\n\n    function InvalidValue(item) {\n        var control = document.getElementById(item);\n        if (control != null)\n        { control.style.backgroundColor = \"#FFE4E1\"; }\n    }\n\n    function RevertForm(item) {\n        var co";
    echo "ntrol = document.getElementById(item);\n        if (control != null)\n        { control.style.backgroundColor = \"\"; }\n    }\n    function ReturnFalse(msg) {\n        alert(msg);\n        return false;\n    }\n    function ValidateEmail(strValue) {\n        if (window.echeck(strValue)) {\n            var objRegExp = /(^[a-zA-Z0-9\\-_\\.]([a-zA-Z0-9\\-_\\.]*)@([a-z_\\.]*)([.][a-z]{3})$)|(^[a-z]([a-z_\\.]*)@([a-z_\\";
    echo ".]*)(\\.[a-z]{3})(\\.[a-z]{2})*$)/i;\n            return objRegExp.test(strValue);\n        }\n        return false;\n    }\n\n    function ValidateForm() {\n        var email = document.getElementById('supportemail');\n        var enomuid = document.getElementById('enomuid');\n        var enompw = document.getElementById('enompw');\n        var companyurl = document.getElementById('companyurl');\n        var ";
    echo "companyname = document.getElementById('companyname');\n        var msg = '';\n        \n         if (enomuid.value == \"\") {\n            InvalidValue('enomuid');\n            msg += \"eNom LoginID is required\\n\";\n        } else { RevertForm('enomuid'); }\n\n        if (enompw.value == \"\") {\n            InvalidValue('enompw');\n            msg += \"eNom Password is required\\n\";\n        } else { RevertForm('e";
    echo "nompw'); }\n\n       if (email.value == \"\") {\n            InvalidValue('supportemail');\n            msg += \"Support Email Address is required\\n\";\n        } else { RevertForm('supportemail'); }\n\n        if (companyname.value == \"\") {\n            InvalidValue('companyname');\n            msg += \"Company Name is required\\n\";\n        } else { RevertForm('companyname'); }\n\n        if (companyurl.value == ";
    echo "\"\") {\n            InvalidValue('companyurl');\n            msg += \"Company Url is required\\n\";\n        } else { RevertForm('companyurl'); }\n\n        if(msg != '')\n            return ReturnFalse(msg);\n         \n        return true;\n    }\n    \n    function ResetDefault()\n    {\n        var companyurl = document.getElementById('companyurl');\n        companyurl.value = '";
    echo enomnewtlds_helper_getwatchlisturl( );
    echo "';\n    }\n\n</script>\n\n";
    echo "<s";
    echo "tyle type=\"text/css\">\n\n\t.tld_wrp {margin-top:10px;font:16px/24px Arial, Verdana, Helvetica;padding:10px;color:#3C3C3C;background-color:#FFF;-webkit-border-radius:5px;border-radius:5px}\n\t.tld_wrp DIV,\n\t.tld_wrp SPAN,\n\t.tld_wrp A,\n\t.tld_wrp IMG,\n\t.tld_wrp STRONG,\n\t.tld_wrp FORM,\n\t.tld_wrp TABLE, \n\t.tld_wrp TR, \n\t.tld_wrp TH, \n\t.tld_wrp TD {font-family:inherit;font-size:inherit;line-height:inherit;ma";
    echo "rgin:0;padding:0;border:0;vertical-align:baseline;background-repeat:no-repeat;-webkit-appearance:none;-moz-appearance:none;appearance:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none}\n\t.tld_wrp STRONG {font-weight:bold}\n\t.tld_wrp A {text-decoration:none;cursor:pointer;color:#024DD6}\n\t.tld_wrp A:Hover {text-decoration:underline}\n\t.tld_wrp TABLE {border-collapse:collapse;border-spacing:0";
    echo "}\n\t.tld_wrp TH,\n\t.tld_wrp TD {font-weight:normal;vertical-align:top;text-align:left}\n\t.tld_wrp IMG {font-size:0;vertical-align:middle;max-width:100%;height:auto;-ms-interpolation-mode:bicubic}\n\t.tld_wrp INPUT[type=text], \n\t.tld_wrp INPUT[type=password] {-webkit-appearance:none;-moz-appearance:none;appearance:none;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;mar";
    echo "gin-bottom:3px;color:#000;display:inline;padding:0;font-weight:normal;vertical-align:baseline;font-family:\"Helvetica Neue\",Helvetica,Arial,sans-serif;font-size:13px;line-height:20px;height:20px;border-style:solid;border-color:#000 #CCC #CCC #000;-webkit-border-radius:2px;border-radius:2px;background-color:#FFF;background-size:100% 100%;margin-bottom:3px;border-width:1px;background-image:-webkit-gr";
    echo "adient(linear, left top, left bottom, from(#EEE), to(#FFF));background-image:-webkit-linear-gradient(#EEE 0%, #FFF 100%);background-image:-moz-linear-gradient(#EEE 0%, #FFF 100%);background-image:-ms-linear-gradient(#EEE 0%, #FFF 100%);background-image:-o-linear-gradient(#EEE 0%, #FFF 100%);background-image:linear-gradient(#EEE 0%, #FFF 100%)}\n\n\t.tld_wrp .sError1, \n\t.tld_wrp .sSuccess1 {text-align";
    echo ":left;padding:8px 10px 8px 42px;line-height:18px;font-size:14px;margin:1px 0 15px 0;position:relative;z-index:1;border:1px solid #000000;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px}\n\t.tld_wrp .sError1:Before, \n\t.tld_wrp .sSuccess1:Before {content:\"\";position:absolute;top:4px;left:10px;z-index:2;height:24px;width:24px;background:transparent url('../modules/addons/enomnewtlds/";
    echo "images/ico-info24x.png') no-repeat 0 0}\n\t.tld_wrp .sError1 {border-color:#CC9999;color:#C00;background:#FFEAEA}\n\t.tld_wrp .sSuccess1 {border-color:#A7B983;color:#333;background:#E8FF74}\n\t\t\t\t\n\t.tld_wrp .clearfix:after {content: \".\"; display: block; height: 0; clear: both; visibility: hidden;}\n\t.tld_wrp .clearfix {display: inline-block;}\n    \n</style>\n\n\n<form method=\"post\" action=\"";
    echo $modulelink;
    echo "\">\n\n\t<div class=\"tld_wrp\" style=\"padding:10px;width:852px;\">\n\n\t\t";

if (!enomnewtlds_Helper_IsNullOrEmptyString( $errormessage )) {
echo "			<div class=\"sError1\">
				";
echo "<s";
echo "trong>";
echo $errormessage;
echo "</strong>
			</div>
		";
}

echo "
		";

if (!enomnewtlds_Helper_IsNullOrEmptyString( $success_message )) {
echo "			<div class=\"sSuccess1\">
				";
echo "<s";
echo "trong>";
echo $success_message;
echo "</strong>
			</div>
		";
}

echo "
		<div class=\"clearfix\" style=\"display:block;clear:both;border:1px solid #CCC;width:850px;font-weight:bold;font-size:14px;background-color:#EEE\">

			<div style=\"float:left;width:500px;min-height:485px;background-color:#FFF;\">
				<div style=\"padding:20px\">

					<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
						<tr>
							<td width=\"50%\" style=\"font-size:14px\">
								";
echo "<s";
echo "trong>";
echo $LANG['form_enomloginid'];
echo "</strong> ";
echo "<s";
echo "pan style=\"color:red\">*</span>
							</td>
							<td width=\"50%\"style=\"text-align:right\">
								<a href=\"https://www.whmcs.com/members/freeenomaccount.php\" target=\"_blank\"\">";
echo $LANG['form_getenomaccount'];
echo "</a>
							</td>
						</tr>
						<tr>
							<td colspan=\"2\" width=\"100%\" style=\"font-size:14px;padding-bottom:10px\">
								<input type=\"text\" style=\"width:99%\" name=\"enomuid\" id=\"enomuid\" value=\"";
echo $enomuid;
echo "\" onfocus=\"RevertForm(this.id);\" />
							</td>
						</tr>
						<tr>
							<td colspan=\"2\" width=\"100%\" style=\"font-size:14px;padding-bottom:10px\">
								";
echo "<s";
echo "trong>";
echo $LANG['form_enompassword'];
echo "</strong> ";
echo "<s";
echo "pan style=\"color:red\">*</span><br />
								<input type=\"password\" style=\"width:99%\" name=\"enompw\" id=\"enompw\" value=\"";

if (!enomnewtlds_Helper_IsNullOrEmptyString( $enompw )) {
echo "************";
}

echo "\" onfocus=\"RevertForm(this.id);\" />
							</td>
						</tr>
						<tr>
							<td colspan=\"2\" width=\"100%\" style=\"font-size:14px;padding-bottom:10px\">
								";
echo "<s";
echo "trong>";
echo $LANG['form_companyname'];
echo "</strong> ";
echo "<s";
echo "pan style=\"color:red\">*</span><br />
								<input type=\"text\" name=\"companyname\" style=\"width:99%\" id=\"companyname\" value=\"";
echo $companyname;
echo "\" onfocus=\"RevertForm(this.id);\" />
							</td>
						</tr>
						<tr>
							<td colspan=\"2\" width=\"100%\" style=\"font-size:14px;padding-bottom:10px\">
								";
echo "<s";
echo "trong>";
echo $LANG['form_supportemail'];
echo "</strong> ";
echo "<s";
echo "pan style=\"color:red\">*</span><br />
								<input type=\"text\" name=\"supportemail\" style=\"width:99%\" id=\"supportemail\" value=\"";
echo $supportemail;
echo "\" onfocus=\"RevertForm(this.id);\" />
								<div style=\"margin-top:0;font-size:12px;line-height:16px;color:#666\">";
echo $LANG['form_support_email_desc'];
echo "</div>
							</td>
						</tr>
						<tr>
							<td colspan=\"2\" width=\"100%\" style=\"font-size:14px;border-bottom:dotted 1px #CCC;padding-bottom:10px\">
								";
echo "<s";
echo "trong>";
echo $LANG['form_companyurl'];
echo "</strong> ";
echo "<s";
echo "pan style=\"color:red\">*</span><br />
								<input type=\"text\" name=\"companyurl\" style=\"width:99%\" id=\"companyurl\" value=\"";
echo $companyurl;
echo "\" onfocus=\"RevertForm(this.id);\" />
								<div style=\"margin-top:0;font-size:12px;line-height:16px;color:#666;padding-bottom:10px\">
									";
echo $LANG['form_companyurl_text'];
echo " <a href=\"javascript:void(0)\" onclick=\"ResetDefault();\">";
echo $LANG['form_resetdefault'];
echo "</a>
								</div>
								<div>";
echo $form_terms_text;
echo "</div>
							</td>
						</tr>
						<tr>
							<td width=\"50%\" valign=\"bottom\" style=\"padding-top:15px\">
								<input type=\"submit\" value=\"";
echo $form_button_text;
echo " &raquo;\" style=\"cursor:pointer;border-style:outset;padding:7px;font-size:1.55em;*font-size:1.3em;font-family:Arial, Helvetica, sans-serif;font-weight:normal;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;border-width:1px\" onclick=\"return ValidateForm();\" />
							</td>
							<td width=\"50%\" valign=\"bottom\" style=\"text-align:right;padding-top:15px\">
								<img src=\"../modules/";
echo "addons/enomnewtlds/images/enom.gif\" border=\"0\" />
							</td>
						</tr>
						<!--<tr>
							<td colspan=\"2\" width=\"100%\"><p>";
echo $documentation_link;
echo "</p></td>
						</tr>-->
					</table>

				</div>
			</div>
			<div style=\"float:right;width:350px;min-height:485px\">
				<div style=\"padding:20px\">
					<div style=\"border:1px solid #CCC;background:#FFF;\">
						<iframe frameborder=\"0\" height=\"440px\" width=\"308px\" marginheight=\"0\" marginwidth=\"0\" scrolling=\"yes\" src=\"https://";
echo $url;
echo "/whmcs/tld-portal/addon-iframe.aspx?p=";
echo $form_iframe_tab;
echo "&version=";
echo $enomnewtlds_CurrentVersion;
echo "&bundled=";
echo $enomnewtlds_isbundled ? "1" : "0";
echo "\"></iframe>
					</div>
				</div>
			</div>

	</div>

</div>

</form>





";
}


if (!defined( "WHMCS" )) {
exit( "This file cannot be accessed directly" );
}

$enomnewtlds_CurrentVersion = "1.0";
$enomnewtlds_isbundled = false;
$enomnewtlds_errormessage = "";
$enomnewtlds_mysalt = "sAR2Th4Ste363tUkUw";
$enomnewtlds_DefaultEnvironment = "0";
$enomnewtlds_ModuleName = "enomnewtlds";
$enomnewtlds_DBName = "mod_" . $enomnewtlds_ModuleName;
$enomnewtlds_CronDBName = $enomnewtlds_DBName . "_cron";
?>