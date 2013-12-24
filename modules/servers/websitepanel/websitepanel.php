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

function websitepanel_ConfigOptions() {
	$configarray = array( "Package Name" => array( "Type" => "text", "Size" => "25" ), "Web Space Quota" => array( "Type" => "text", "Size" => "5", "Description" => "MB" ), "Bandwidth Limit" => array( "Type" => "text", "Size" => "5", "Description" => "MB" ), "PlanID" => array( "Type" => "text", "Size" => "3", "Description" => " *DNP Hosting Plan ID" ), "Parent SpaceId" => array( "Type" => "text", "Size" => "3", "Description" => "* SpaceID that all accounts are created under" ), "Enterprise Server Port" => array( "Type" => "text", "Size" => "5", "Description" => "* Required" ), "Different Potal URL" => array( "Type" => "yesno", "Description" => "Tick if portal address is different to server address" ), "Portal URL" => array( "Type" => "text", "Size" => "25", "Description" => "Portal URL, with http://, no trailing slash" ), "Send DNP Account Summary email" => array( "Type" => "yesno", "Description" => "Tick to send DNP Account Summary" ), "Send DNP Hosting Space Summary email" => array( "Type" => "yesno", "Description" => "Tick to send Hosting Space Summary" ), "Create Mail account" => array( "Type" => "yesno", "Description" => "Tick to create mail account" ), "Create FTP account" => array( "Type" => "yesno", "Description" => "Tick to create FTP account" ), "Temporary domain" => array( "Type" => "yesno", "Description" => "Tick to create a temp domain" ), "HTML email" => array( "Type" => "yesno", "Description" => "Tick enable HTML email from DNP" ), "Create Website" => array( "Type" => "yesno", "Description" => "Tick to create Website" ), "Count Bandwidth/Diskspace" => array( "Type" => "yesno", "Description" => "Tick to update diskpace/bandwidth in WHMCS" ) );
	return $configarray;
}


function websitepanel_CreateAccount($params) {
	$serverip = $params['serverip'];
	$serverusername = $params['serverusername'];
	$serverpassword = $params['serverpassword'];
	$secure = $params['serversecure'];
	$params['domain'];
	$packagetype = $params['type'];
	$username = $params['username'];
	$password = $params['password'];
	$accountid = $params['accountid'];
	$packageid = $params['packageid'];
	$clientsdetails = $params['clientsdetails'];
	$planId = $params['configoption4'];
	$parentPackageId = $params['configoption5'];
	$esport = $params['configoption6'];

	if (!class_exists( "SoapClient" )) {
		return "SOAP is missing. Please recompile PHP with the SOAP module included.";
	}


	if ($params['configoption11'] == "on") {
		$createMailAccount = true;
	}
	else {
		$createMailAccount = false;
	}


	if ($params['configoption9'] == "on") {
		$sendAccountLetter = true;
	}
	else {
		$sendAccountLetter = false;
	}


	if ($params['configoption10'] == "on") {
		$sendPackageLetter = true;
	}
	else {
		$sendPackageLetter = false;
	}


	if ($params['configoption13'] == "on") {
		$tempDomain = true;
	}
	else {
		$tempDomain = false;
	}


	if ($params['configoption12'] == "on") {
		$createFtpAccount = true;
	}
	else {
		$createFtpAccount = false;
	}


	if ($params['configoption14'] == "on") {
		$htmlMail = true;
	}
	else {
		$htmlMail = false;
	}


	if ($params['configoption15'] == "on") {
		$website = true;
	}
	else {
		$website = false;
	}


	if ($packagetype == "reselleraccount") {
		$roleid = 6;
	}
	else {
		$roleid = 7;
	}

	$param = array( "parentPackageId" => $parentPackageId, "username" => $username, "password" => $password, "roleId" => $roleid, "firstName" => $clientsdetails['firstname'], "lastName" => $clientsdetails['lastname'], "email" => $clientsdetails['email'], "htmlMail" => $htmlMail, "sendAccountLetter" => $sendAccountLetter, "createPackage" => true, "planId" => $planId, "sendPackageLetter" => $sendPackageLetter, "domainName" => $domain, "tempDomain" => $tempDomain, "createWebSite" => $website, "createFtpAccount" => $createFtpAccount, "ftpAccountName" => $username, "createMailAccount" => $createMailAccount );
	$result = $domain = websitepanel_call( $params, "CreateUserWizard", $param );
	return $result;
}


function websitepanel_call($params, $func, $param, $retdata = "") {
	$wsdlfile = "esusers";

	if (( ( ( $func == "CreateUserWizard" || $func == "GetMyPackages" ) || $func == "UpdatePackageLiteral" ) || $func == "GetPackageBandwidth" ) || $func == "GetPackageDiskspace") {
		$wsdlfile = "espackages";
	}

	$http = ($params['serversecure'] ? "https" : "http");
	$serverip = $params['serverip'];
	$serverusername = $params['serverusername'];
	$serverpassword = $params['serverpassword'];
	$esport = $params['configoption6'];
	$soapaddress = $http . "://" . $serverip . ":" . $esport . "/" . $wsdlfile . ".asmx?WSDL";
	$client = new SoapClient( $soapaddress, array( "login" => $serverusername, "password" => $serverpassword ) );
	$result = (array)$client->$func( $param );
	Exception {
		logModuleCall( "websitepanel", $func, $param, $e->getMessage() );
		return "Caught exception: " . $e->getMessage();
		logModuleCall( "websitepanel", $func, $param, $result );

		if ($retdata) {
			return $result[$func . "Result"];
		}


		if (is_soap_fault( $result )) {
			return "SOAP Fault Code: " . $result->faultcode . " - Error: " . $result->faultstring;
		}

		$returnCode = $result[$func . "Result"];

		if (0 <= $returnCode) {
			return "success";
		}


		if ($returnCode == "-1100") {
			return "User account with the specified username already exists on the server";
		}


		if ($returnCode == "-700") {
			return "Specified mail domain already exists on the service";
		}


		if ($returnCode == "-701") {
			return "Mail resource is unavailable for the selected hosting space";
		}


		if ($returnCode == "-502") {
			return "Specified domain already exists";
		}


		if ($returnCode == "-301") {
			return "The hosting space could not be deleted because it has child spaces";
		}

		return "WebsitePanel API Error Code: " . $returnCode;
	}
}


function websitepanel_TerminateAccount($params) {
	$wspuserid = websitepanel_getuserid( $params );

	if (!$wspuserid) {
		return "Username '" . $params['username'] . "' not found in WebsitePanel";
	}

	$param = array( "userId" => $wspuserid );
	$result = websitepanel_call( $params, "DeleteUser", $param );
	return $result;
}


function websitepanel_SuspendAccount($params) {
	$wspuserid = websitepanel_getuserid( $params );

	if (!$wspuserid) {
		return "Username '" . $params['username'] . "' not found in WebsitePanel";
	}

	$param = array( "userId" => $wspuserid, "status" => "Suspended" );
	$result = websitepanel_call( $params, "ChangeUserStatus", $param );
	return $result;
}


function websitepanel_UnsuspendAccount($params) {
	$wspuserid = websitepanel_getuserid( $params );

	if (!$wspuserid) {
		return "Username '" . $params['username'] . "' not found in WebsitePanel";
	}

	$param = array( "userId" => $wspuserid, "status" => "Active" );
	$result = websitepanel_call( $params, "ChangeUserStatus", $param );
	return $result;
}


function websitepanel_ChangePassword($params) {
	$wspuserid = websitepanel_getuserid( $params );

	if (!$wspuserid) {
		return "Username '" . $params['username'] . "' not found in WebsitePanel";
	}

	$param = array( "userId" => $wspuserid, "password" => $params['password'] );
	$result = websitepanel_call( $params, "ChangeUserPassword", $param );
	return $result;
}


function websitepanel_ChangePackage($params) {
	$wspuserid = websitepanel_getuserid( $params );

	if (!$wspuserid) {
		return "Username '" . $params['username'] . "' not found in WebsitePanel";
	}

	$param = array( "packageId" => websitepanel_getpackageid( $params, $wspuserid ), "statusId" => 1, "planId" => $params['configoption4'], "purchaseDate" => date( "c" ), "packageName" => $params['configoption1'], "packageComments" => "" );
	$result = websitepanel_call( $params, "UpdatePackageLiteral", $param );
	return $result;
}


function websitepanel_ClientArea($params) {
	global $_LANG;

	$username = $params['username'];
	$url = $params['configoption7'];
	$urladdress = $params['configoption8'];

	if ($url == "on") {
		$code = "<form method=\"post\" action=\"" . $urladdress . "/Default.aspx\" target=\"_blank\">
        <input type=\"hidden\" name=\"pid\" value=\"Login\" />
        <input type=\"hidden\" name=\"user\" value=\"" . $params['username'] . "\" />
        <input type=\"hidden\" name=\"password\" value=\"" . $params['password'] . "\" />
        <input type=\"submit\" value=\"" . $_LANG['websitepanellogin'] . "\" />
        </form>";
	}
	else {
		$http = ($params['serversecure'] ? "https" : "http");
		$domain = ($params['serverhostname'] ? $params['serverhostname'] : $params['serverip']);
		$code = "<form method=\"post\" action=\"" . $http . "://" . $domain . "/Default.aspx\" target=\"_blank\">
        <input type=\"hidden\" name=\"pid\" value=\"Login\" />
        <input type=\"hidden\" name=\"user\" value=\"" . $params['username'] . "\" />
        <input type=\"hidden\" name=\"password\" value=\"" . $params['password'] . "\" />
        <input type=\"submit\" value=\"" . $_LANG['websitepanellogin'] . "\" />
        </form>";
	}

	return $code;
}


function websitepanel_AdminLink($params) {
	$serverip = $params['serverip'];
	$serveridquery = full_query( "SELECT id FROM tblservers where ipaddress = '" . db_escape_string( $serverip ) . "'" );
	$serveridqueryresult = mysql_fetch_array( $serveridquery );
	$serverid = $serveridqueryresult['id'];
	$query = full_query( "SELECT configoption7,configoption8 FROM tblproducts WHERE id = (SELECT packageid FROM tblhosting where server = " . (int)$serverid . " limit 1) AND servertype = 'websitepanel'" );
	$queryresult = mysql_fetch_array( $query );
	$url = $queryresult['configoption7'];

	if ($url == "on") {
		$code = "<form method=\"post\" action=\"" . $queryresult['configoption8'] . "/Default.aspx\" target=\"_blank\">
        <input type=\"hidden\" name=\"pid\" value=\"Login\" />
        <input type=\"hidden\" name=\"user\" value=\"" . $params['serverusername'] . "\" />
        <input type=\"hidden\" name=\"password\" value=\"" . $params['serverpassword'] . "\" />
        <input type=\"submit\" value=\"Login to Control Panel\" />
        </form>";
	}
	else {
		$http = ($params['serversecure'] ? "https" : "http");
		$domain = ($params['serverhostname'] ? $params['serverhostname'] : $params['serverip']);
		$code = "<form method=\"post\" action=\"" . $http . "://" . $domain . "/Default.aspx\" target=\"_blank\">
        <input type=\"hidden\" name=\"pid\" value=\"Login\" />
        <input type=\"hidden\" name=\"user\" value=\"" . $params['serverusername'] . "\" />
        <input type=\"hidden\" name=\"password\" value=\"" . $params['serverpassword'] . "\" />
        <input type=\"submit\" value=\"Login to Control Panel\" />
        </form>";
	}

	return $code;
}


function websitepanel_LoginLink($params) {
	$pid = $params['pid'];
	$username = $params['username'];
	$url = $params['configoption7'];
	$urladdress = $params['configoption8'];

	if ($url == "on") {
		$code = "<a href=\"" . $urladdress . "/Default.aspx?pid=Login&user=" . $params['username'] . "&password=" . $params['password'] . "\" target=\"_blank\" class=\"moduleloginlink\">login to control panel</a>";
	}
	else {
		$http = ($params['serversecure'] ? "https" : "http");
		$domain = ($params['serverhostname'] ? $params['serverhostname'] : $params['serverip']);
		$code = "<a href=\"" . $http . "://" . $domain . "/Default.aspx?pid=Login&user=" . $params['username'] . "&password=" . $params['password'] . "\" target=\"_blank\" class=\"moduleloginlink\">login to control panel</a>";
	}

	return $code;
}


function websitepanel_getuserid($params) {
	websitepanel_call( $params, "GetUserByUsername", $param, true );
	$result = $param = array( "username" => $params['username'] );
	return $result->UserId;
}


function websitepanel_getpackageid($params, $user) {
	$param = array( "userId" => $user );
	$result = websitepanel_call( $params, "GetMyPackages", $param, true );
	return $result->PackageInfo->PackageId;
}


function websitepanel_UsageUpdate($params) {
	$serverid = $params['serverid'];
	$serverip = $params['serverip'];
	$serverusername = $params['serverusername'];
	$serverpassword = $params['serverpassword'];
	$query = full_query( "SELECT username,packageid,regdate FROM tblhosting WHERE server=" . (int)$serverid . " AND domainstatus IN ('Active','Suspended')" );

	while ($row = mysql_fetch_array( $query )) {
		$username = $row['username'];
		$whmcspackageID = $row['packageid'];
		$packagequery = full_query( "SELECT configoption2,configoption3,configoption6,configoption16 FROM tblproducts where id = " . (int)$whmcspackageID );
		$packagequeryresult = mysql_fetch_array( $packagequery );

		if ($packagequeryresult['configoption16'] == "on") {
			$esport = $packagequeryresult['configoption6'];
			$dslimit = $packagequeryresult['configoption2'];
			$bwlimit = $packagequeryresult['configoption3'];
			$params['configoption6'] = $esport;
			$params['username'] = $username;
			$userID = websitepanel_getuserid( $params );
			$packageID = websitepanel_getpackageid( $params, $userID );
			$startDate = websitepanel_calculateDate( $row['regdate'] );
			$bandwidth = websitepanel_getBandwidth( $params, $packageID, $startDate );
			$diskspace = websitepanel_getDiskspace( $params, $packageID );
			update_query( "tblhosting", array( "diskusage" => $diskspace, "disklimit" => $dslimit, "bwusage" => $bandwidth, "bwlimit" => $bwlimit, "lastupdate" => "now()" ), array( "server" => $params['serverid'], "username" => $username ) );
		}

		Exception {
		}
	}

}


function websitepanel_getBandwidth($params, $packageID, $startDate) {
	$param = array( "packageId" => $packageID, "startDate" => $startDate, "endDate" => date( "Y-m-d", time() ) );
	$result = websitepanel_call( $params, "GetPackageBandwidth", $param, true );
	$xml = simplexml_load_string( $result->any );
	$total = 5;
	foreach ($xml->NewDataSet->Table as $Table) {
		$total = $total + $Table->MegaBytesTotal;
	}

	return $total;
}


function websitepanel_getDiskspace($params, $packageID) {
	$result = websitepanel_call( $params, "GetPackageDiskspace", $param, true );
	simplexml_load_string( $result->any );
	$xml = $param = array( "packageId" => $packageID );
	$total = 5;
	foreach ($xml->NewDataSet->Table as $Table) {
		$total = $total + $Table->Diskspace;
	}

	return $total;
}


function websitepanel_calculateDate($date) {
	$dateexplode = explode( "-", $date );
	$currentyear = date( "Y" );
	$currentmonth = date( "m" );
	$newdate = $currentyear . "-" . $currentmonth . "-" . $dateexplode[2];
	$dateDiff = time() - strtotime( "+1 hour", strtotime( $newdate ) );
	$fullDays = floor( $dateDiff / ( 60 * 60 * 24 ) );

	if ($fullDays < 0) {
		return date( "Y-m-d", strtotime( "-1 month", strtotime( $newdate ) ) );
	}

	return $newdate;
}


?>