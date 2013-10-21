<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 * */

function helm4_ConfigOptions() {
	$configarray = array( "Account Role ID" => array( "Type" => "text", "Size" => "5", "Description" => "" ), "Plan ID" => array( "Type" => "text", "Size" => "5", "Description" => "" ) );
	return $configarray;
}


function helm4_ClientArea($params) {
	global $_LANG;

	$code = "<form action=\"http://" . $params["serverip"] . ":8086/\" method=\"post\" target=\"_blank\"><input type=\"submit\" value=\"" . $_LANG["helmlogin"] . "\" class=\"button\"></form>";
	return $code;
}


function helm4_AdminLink($params) {
	$code = "<form action=\"http://" . $params["serverip"] . ":8086/\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"txtAccountName\" value=\"" . $params["serverusername"] . "\"><input type=\"hidden\" name=\"txtUserName\" value=\"" . $params["serverusername"] . "\"><input type=\"hidden\" name=\"txtPassword\" value=\"" . $params["serverpassword"] . "\"><input type=\"hidden\" name=\"btnLogin\" value=\"Login\"><input type=\"submit\" value=\"Helm\"></form>";
	return $code;
}


function helm4_CreateAccount($params) {
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "GetAccountByName", "UserAccountName" => $params["clientsdetails"]["email"], "IncludeChildren" => "false" );
	$result = helm4_connect( $params["serverip"], $fields );
	$helmaccountid = $result["RESULTS"]["RESULTDATA"]["RECORD"]["ACCOUNTID"];

	if ($helmaccountid) {
		$helmaccountusername = $result["RESULTS"]["RESULTDATA"]["RECORD"]["PRIMARYLOGINNAME"];
		$result = select_query( "tblhosting", "username,password", array( "username" => $helmaccountusername ) );
		$data = mysql_fetch_array( $result );
		updateService( array( "username" => $data["username"], "password" => $data["password"] ) );
	}


	if (!$helmaccountid) {
		$country = $params["clientsdetails"]["country"];

		if ($country == "UK") {
			$country = "GB";
		}

		$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "CreateAccount", "AccountRoleId" => $params["configoption1"], "NewAccountName" => $params["clientsdetails"]["email"], "CompanyName" => $params["clientsdetails"]["firstname"] . " " . $params["clientsdetails"]["lastname"], "AccountEmailAddress" => $params["clientsdetails"]["email"], "AdminLoginName" => $params["username"], "AdminLoginPassword" => $params["password"], "AdminEmailAddress" => $params["clientsdetails"]["email"], "FirstName" => $params["clientsdetails"]["firstname"], "LastName" => $params["clientsdetails"]["lastname"], "Address1" => $params["clientsdetails"]["address1"], "Address2" => $params["clientsdetails"]["address2"], "Address3" => "", "Town" => $params["clientsdetails"]["city"], "PostCode" => $params["clientsdetails"]["postcode"], "CountryCode" => $country, "CountyName" => $params["clientsdetails"]["county"] );
		$result = helm4_connect( $params["serverip"], $fields );
		$resultcode = $result["RESULTS"]["RESULTCODE"];
		$resultdescription = $result["RESULTS"]["RESULTDESCRIPTION"];
		$helmaccountid = $result["RESULTS"]["RESULTDATA"];

		if ($resultcode != "0") {
			return $resultdescription;
		}
	}

	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "AddPackage", "UserAccountID" => $helmaccountid, "PlanID" => $params["configoption2"], "PackageName" => $params["domain"], "Quantity" => "1" );
	$result = helm4_connect( $params["serverip"], $fields );
	$resultcode = $result["RESULTS"]["RESULTCODE"];
	$resultdescription = $result["RESULTS"]["RESULTDESCRIPTION"];

	if ($resultcode != "0") {
		return $resultdescription;
	}

	$helmpackageid = $result["RESULTS"]["RESULTDATA"]["RECORD"]["PACKAGEID"];
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "AddDomainToPackage", "UserAccountID" => $helmaccountid, "PackageID" => $helmpackageid, "DomainName" => $params["domain"], "IsPark" => "false" );
	$result = helm4_connect( $params["serverip"], $fields );
	$resultcode = $result["RESULTS"]["RESULTCODE"];
	$resultdescription = $result["RESULTS"]["RESULTDESCRIPTION"];

	if ($resultcode != "0") {
		return $resultdescription;
	}

	return "success";
}


function helm4_SuspendAccount($params) {
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "GetAccountByName", "UserAccountName" => $params["clientsdetails"]["email"], "IncludeChildren" => "false" );
	$result = helm4_connect( $params["serverip"], $fields );
	$helmaccountid = $result["RESULTS"]["RESULTDATA"]["RECORD"]["ACCOUNTID"];
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "SuspendAccount", "UserAccountId" => $helmaccountid, "IncludeChildren" => "false" );
	$result = helm4_connect( $params["serverip"], $fields );
	$resultcode = $result["RESULTS"]["RESULTCODE"];
	$resultdescription = $result["RESULTS"]["RESULTDESCRIPTION"];

	if ($resultcode != "0") {
		return $resultdescription;
	}

	return "success";
}


function helm4_UnsuspendAccount($params) {
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "GetAccountByName", "UserAccountName" => $params["clientsdetails"]["email"], "IncludeChildren" => "false" );
	$result = helm4_connect( $params["serverip"], $fields );
	$helmaccountid = $result["RESULTS"]["RESULTDATA"]["RECORD"]["ACCOUNTID"];
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "UnsuspendAccount", "UserAccountId" => $helmaccountid, "IncludeChildren" => "false" );
	$result = helm4_connect( $params["serverip"], $fields );
	$resultcode = $result["RESULTS"]["RESULTCODE"];
	$resultdescription = $result["RESULTS"]["RESULTDESCRIPTION"];

	if ($resultcode != "0") {
		return $resultdescription;
	}

	return "success";
}


function helm4_TerminateAccount($params) {
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "GetAccountByName", "UserAccountName" => $params["clientsdetails"]["email"], "IncludeChildren" => "false" );
	$result = helm4_connect( $params["serverip"], $fields );
	$helmaccountid = $result["RESULTS"]["RESULTDATA"]["RECORD"]["ACCOUNTID"];
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "GetPackages", "UserAccountId" => $helmaccountid );
	$result = helm4_connect( $params["serverip"], $fields );
	$resultcode = $result["RESULTS"]["RESULTCODE"];
	$resultdescription = $result["RESULTS"]["RESULTDESCRIPTION"];

	if ($resultcode != "0") {
		return $resultdescription;
	}

	$rawxml = $result["raw"];
	$output = explode( "<Record>", $rawxml );
	foreach ($output as $data) {
		$data = XMLtoARRAY( "<Record>" . $data );
		$data = $data["RECORD"];

		if ($data) {
			$helmpackagesarray[$data["NAME"]] = $data;
			continue;
		}
	}

	$helmpackageid = $helmpackagesarray[$params["domain"]]["PACKAGEID"];
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "GetDomainsByPackageID", "UserAccountId" => $helmaccountid, "PackageID" => $helmpackageid );
	$result = helm4_connect( $params["serverip"], $fields );
	$resultcode = $result["RESULTS"]["RESULTCODE"];
	$resultdescription = $result["RESULTS"]["RESULTDESCRIPTION"];

	if ($resultcode != "0") {
		return $resultdescription;
	}

	$rawxml = $result["raw"];
	$output = explode( "<Record>", $rawxml );
	foreach ($output as $data) {
		$data = XMLtoARRAY( "<Record>" . $data );
		$data = $data["RECORD"];

		if ($data) {
			$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "DeleteDomain", "DomainID" => $data["DOMAINID"] );
			$result = helm4_connect( $params["serverip"], $fields );
			continue;
		}
	}

	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "DeletePackage", "UserAccountId" => $helmaccountid, "PackageID" => $helmpackageid );
	$result = helm4_connect( $params["serverip"], $fields );
	$resultcode = $result["RESULTS"]["RESULTCODE"];
	$resultdescription = $result["RESULTS"]["RESULTDESCRIPTION"];

	if ($resultcode != "0") {
		return $resultdescription;
	}

	return "success";
}


function helm4_ChangePackage($params) {
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "GetAccountByName", "UserAccountName" => $params["clientsdetails"]["email"], "IncludeChildren" => "false" );
	$result = helm4_connect( $params["serverip"], $fields );
	$helmaccountid = $result["RESULTS"]["RESULTDATA"]["RECORD"]["ACCOUNTID"];
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "GetPackages", "UserAccountId" => $helmaccountid );
	$result = helm4_connect( $params["serverip"], $fields );
	$resultcode = $result["RESULTS"]["RESULTCODE"];
	$resultdescription = $result["RESULTS"]["RESULTDESCRIPTION"];

	if ($resultcode != "0") {
		return $resultdescription;
	}

	$rawxml = $result["raw"];
	$output = explode( "<Record>", $rawxml );
	foreach ($output as $data) {
		$data = XMLtoARRAY( "<Record>" . $data );
		$data = $data["RECORD"];

		if ($data) {
			$helmpackagesarray[$data["NAME"]] = $data;
			continue;
		}
	}

	$helmpackageid = $helmpackagesarray[$params["domain"]]["PACKAGEID"];
	$fields = array( "AccountName" => $params["serverusername"], "Username" => $params["serverusername"], "Password" => $params["serverpassword"], "action" => "UpgradePackage", "UserAccountId" => $helmaccountid, "PackageID" => $helmpackageid, "NewPlanID" => $params["configoption2"] );
	$result = helm4_connect( $params["serverip"], $fields );
	$resultcode = $result["RESULTS"]["RESULTCODE"];
	$resultdescription = $result["RESULTS"]["RESULTDESCRIPTION"];

	if ($resultcode != "0") {
		return $resultdescription;
	}

	return "success";
}


function helm4_connect($serverip, $fields) {
	$url = "http://" . $serverip . ":8086/ServiceAPI/HttpAPI.aspx";
	$query_string = http_build_query( $fields );
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
	$data = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$data = " Curl Error - " . curl_error( $ch ) . " (" . curl_errno( $ch ) . ")";
	}

	curl_close( $ch );
	$result = XMLtoARRAY( $data );
	logModuleCall( "helm4", $fields["action"], $fields, $data, $result, array( $fields["AccountName"], $fields["Username"], $fields["Password"] ) );
	$result["raw"] = $data;
	return $result;
}


?>