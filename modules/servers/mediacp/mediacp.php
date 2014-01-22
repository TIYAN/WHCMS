<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 * */

function mcp_firstrun() {
	$checkServerID = select_query( "tblservers", "id", array( "type" => "castcontrol" ) );

	if (mysql_num_rows( $checkServerID ) == 0) {
		return true;
	}


	if ($serverData = mysql_fetch_assoc( $checkServerID )) {
		$selectCastcontrol = select_query( "tblhosting", "userid,password", array( "server" => (int)$serverData['id'] ), "id", "ASC" );

		if (mysql_num_rows( $selectCastcontrol ) == 0) {
			return true;
		}


		if ($hostingData = mysql_fetch_assoc( $selectCastcontrol )) {
			$checkUser = select_query( "whmcs_castcontrol", "", array( "customer_id" => (int)$hostingData['userid'] ) );

			if (mysql_num_rows( $checkUser ) == 0) {
				insert_query( "whmcs_castcontrol", array( "customer_id" => (int)$hostingData['userid'], "reference" => $hostingData['password'] ) );
			}
		}
	}

	return true;
}


function mcp_generatePassword($length = 8) {
	$password = "";
	$possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUV";
	$i = 5;

	while ($i < $length) {
		$char = substr( $possible, mt_rand( 0, strlen( $possible ) - 1 ), 1 );

		if (!strstr( $password, $char )) {
			$password .= $char;
			++$i;
			continue;
		}
	}

	return $password;
}


function mcp_checkTableCreation() {
	$result = full_query( "show tables like 'whmcs_castcontrol'" );

	if (mysql_num_rows( $result ) == 0) {
		$sql = "CREATE TABLE IF NOT EXISTS `whmcs_castcontrol` (" . "  `customer_id` int(11) NOT NULL," . "  `reference` varchar(50) NOT NULL," . "  PRIMARY KEY  (`customer_id`)" . ")";
		full_query( $sql );

		if ($error = mysql_error()) {
			echo $sql . "::" . mysql_error();
		}

		mcp_firstrun();
	}

}


function mcp_getClientPassword($customer_id, $update = false) {
	mcp_checkTableCreation();

	if (!is_numeric( $customer_id )) {
		return false;
	}

	$selectPassword = get_query_val( "whmcs_castcontrol", "reference", array( "customer_id" => (int)$customer_id ) );

	if (!$selectPassword) {
		$selectPassword = mcp_generatePassword();
		insert_query( "whmcs_castcontrol", array( "customer_id" => (int)$customer_id, "reference" => encrypt( $selectPassword ) ) );
	}
	else {
		$selectPassword = decrypt( $selectPassword );
	}


	if ($update) {
		update_query( "whmcs_castcontrol", array( "reference" => encrypt( $update ) ), array( "customer_id" => (int)$customer_id ) );
	}

	return $selectPassword;
}


function mediacp_ConfigOptions() {
	$configarray = array( "Auth" => array( "Type" => "text", "Size" => "60", "Description" => "<br />You can find this in Cast-Control Administration -> API Key" ), "System Path" => array( "Type" => "text", "Size" => "60", "Description" => "<br />The Full URL to the MediaCP. Example: http://mydomain.com/cast/" ), "Service Type" => array( "Type" => "dropdown", "Options" => "shoutcast198,shoutcast2,icecast,windowsMediaServices,WowzaMedia,NoService", "Description" => "<br />Select the Media Service Type." ), "Wowza Media Type" => array( "Type" => "dropdown", "Options" => ",Live Streaming,Ondemand Streaming,Shoutcast Restream", "Description" => "<br />For Wowza Media Service Type ONLY." ), "Max Users" => array( "Type" => "text", "Size" => "5", "Description" => "Maximum Number of Listeners/Viewers for this service." ), "Max Bit Rate" => array( "Type" => "dropdown", "Options" => "24,32,40,48,56,64,80,96,112,128,160,192,224,256,320,400,480,560,640,720,800,920,1024,1280,1536,1792,99999", "Description" => "Maximum Bitrate Allowed by this service." ), "Bandwidth" => array( "Type" => "text", "Size" => "5", "Description" => "MB - Data Transfer allowed per month (0 for unlimited)" ), "Source" => array( "Type" => "dropdown", "Options" => ",sctransv1,sctransv2,ices04,ices20,streamTranscoderV3", "Description" => "Configure Source/AutoDJ Service.<br />Must be applied before creation or may break." ), "Quota" => array( "Type" => "text", "Size" => "6", "Description" => "MB - Maximum Media File Storage Space for this service." ), "SystemID" => array( "Type" => "text", "Size" => 5, "Description" => "Leave blank for default" ), "Port 80 Proxy" => array( "Type" => "dropdown", "Options" => "Disabled,Enabled", "Description" => "Uses additional system resources." ), "Messenger Controller" => array( "Type" => "dropdown", "Options" => "enabled", "Description" => "Messenger Controller is now always included with service." ), "Trial Service?" => array( "Type" => "dropdown", "Description" => "Automatically expire after trial period.", "Options" => "disabled,+1 days,+2 days,+3 days,+4 days,+5 days,+6 days,+7 days,+8 days,+9 days,+10 days,+11 days,+12 days,+13 days,+14 days,+15 days,+20 days,+25 days,+30 days" ), "Stream Auth" => array( "Type" => "dropdown", "Options" => "disabled,enabled", "Description" => "Currently only supports Shoutcast services." ), "Assign as Reseller" => array( "Type" => "text", "Size" => "2", "Description" => "Enter a Plan ID from MediaCP or leave blank to create normal account." ), "Create Media Service" => array( "Type" => "dropdown", "Description" => "Set to No for Reseller Accounts.", "Options" => "Yes,No" ), "Additional Custom Fields" => array( "Type" => "dropdown", "Options" => "disabled,enabled", "Description" => "<br />Automatically fill out custom fields. Windows Media Services ONLY.<br />Create Custom Fields in WHMCS Tab above: <br />Publish Point Name<br />Service Type<br />Public Hostname / IP and Port (Pull Only)<br />Broadcasting Username<br />Broadcasting Password" ) );
	return $configarray;
}


function mediacp_AdminLink($params) {
	return "";
}


function mediacp_LoginLink($params) {
	$api['system_path'] = $params['configoption1'];
	$code = "<a href=\"" . $params['configoption2'] . "/?page=login\">Login to Cast-Control</a>";
	return $code;
}


function mediacp_CreateAccount($params) {
	global $debug;

	if (!empty( $params['domain'] ) && is_numeric( $params['domain'] )) {
		$api['args']['portbase'] = $params['domain'];
		$api['args']['unique_id'] = $params['domain'];
	}


	if (!strstr( $params['domain'], "terminated" ) && !empty( $params['domain'] )) {
		$return = "You have already created the account, Please Terminate the account first";
		return $return;
	}

	$api['path'] = $params['configoption2'];
	$api['rpc'] = "admin.user_create";
	$api['args']['auth'] = $params['configoption1'];
	$api['args']['username'] = trim( $params['clientsdetails']['email'] );
	$api['args']['hash'] = sha1( $api['args']['username'] . mcp_getClientPassword( $params['clientsdetails']['userid'] ) );
	$api['args']['user_email'] = trim( $params['clientsdetails']['email'] );

	if (is_numeric( $params['configoption15'] )) {
		$api['args']['reseller_plan'] = $params['configoption15'];
	}

	$return = mediacp_api( $api );

	if ($return['status'] != "success" && $return['error'] != "User account already exists") {
		return $return['error'];
	}

	$___url = parse_url( $params['configoption2'] );
	update_query( "tblhosting", array( "username" => $api['args']['username'], "password" => encrypt( mcp_getClientPassword( $params['clientsdetails']['userid'] ) ) ), array( "id" => (int)$params['serviceid'] ) );

	if ($params['configoption16'] == "No") {
		return "success";
	}


	if ($params['configoption4'] != "") {
		$api['args']['customfields']['servicetype'] = $params['configoption4'];
	}

	$result3 = select_query( "tblhostingconfigoptions", "", array( "relid" => (int)$params['serviceid'] ) );

	if ($data3 = mysql_fetch_array( $result3 )) {
		$optionid = $data3['optionid'];
		$configid = $data3['configid'];
		$result2 = select_query( "tblproductconfigoptions", "", array( "id" => (int)$configid ) );
		$data2 = mysql_fetch_array( $result2 );
		$optionname = $data2['optionname'];
		$result2 = select_query( "tblproductconfigoptionssub", "", array( "id" => (int)$optionid ) );
		$data2 = mysql_fetch_array( $result2 );
		$optionvalue = $data2['optionname'];
		$optionvalue = str_replace( "MB", "", $optionvalue );
		$optionvalue = str_replace( "KB", "", $optionvalue );
		$optionvalue = str_replace( "Kbps", "", $optionvalue );
		$optionvalue = trim( $optionvalue );

		if ($optionvalue == "Yes") {
			$optionvalue = "enabled";
		}
		else {
			if ($optionvalue == "No") {
				$optionvalue = "disabled";
			}
			else {
				if (strtolower( $optionvalue ) == "unlimited") {
					$optionvalue = "0";
				}
			}
		}


		if ($optionname == "Maximum Users") {
			$params['configoption5'] = $optionvalue;
		}


		if ($optionname == "Maximum Bit Rate" || $optionname == "Maximum Bitrate") {
			$params['configoption6'] = $optionvalue;
		}


		if ($optionname == "Bandwidth") {
			if (strstr( $optionvalue, "GB" )) {
				$optionvalue = str_replace( "GB", "", $optionvalue * 1024 );
			}


			if (strstr( $optionvalue, "TB" )) {
				$optionvalue = str_replace( "TB", "", $optionvalue * 1024 * 1024 );
			}

			$params['configoption7'] = $optionvalue;
		}


		if ($optionname == "Service Type") {
			if ($optionvalue == "Disabled" || $optionvalue == "") {
				$optionvalue == "";
			}


			if (strtolower( $optionvalue ) == "shoutcast") {
				$optionvalue = "shoutcast198";
			}


			if (strtolower( $optionvalue ) == "icecast") {
				$optionvalue = "icecast";
			}


			if (strstr( $optionvalue, "Wowza Media Server" )) {
				$optionvalue = "WowzaMedia";
			}


			if (strstr( $optionvalue, "Windows Media Service" )) {
				$optionvalue = "WowzaMedia";
			}


			if (strstr( $optionvalue, "CDS Service" )) {
				$optionvalue = "NoService";
			}

			$params['configoption5'] = $optionvalue;
		}


		if ($optionname == "Source") {
			if ($optionvalue == "Disabled" || $optionvalue == "") {
				$optionvalue == "";
			}

			$params['configoption8'] = $optionvalue;
		}


		if ($optionname == "Quota" || strstr( $optionname, "Disk Quota" )) {
			if (strstr( $optionvalue, "MB" )) {
				$optionvalue = str_replace( "MB", "", $optionvalue );
			}


			if (strstr( $optionvalue, "GB" )) {
				$optionvalue = str_replace( "GB", "", $optionvalue * 1024 );
			}


			if (strstr( $optionvalue, "TB" )) {
				$optionvalue = str_replace( "TB", "", $optionvalue * 1024 * 1024 );
			}

			$params['configoption9'] = $optionvalue;
		}


		if ($optionname == "Port 80 Proxy") {
			$params['configoption11'] = $optionvalue;
		}


		if ($optionname == "MSN Service Control" || $optionname == "Messenger Service Control") {
			$params['configoption12'] = $optionvalue;
		}


		if ($optionname == "Wowza Media Type" || $optionname == "Flash Media Type") {
			if (strpos( $optionvalue, "Shoutcast" ) !== FALSE) {
				$api['args']['customfields']['servicetype'] = "Shoutcast";
			}

			$api['args']['customfields']['servicetype'] = $optionvalue;
		}


		if ($optionname == "Source Reencode") {
			$api['args']['customfields']['ices_reencode'] = $optionvalue;
		}


		if ($optionname == "Permit Ondemand" || $optionname == "Ondemand Service") {
			$api['args']['customfields']['permit_ondemand'] = $optionvalue;
		}


		if ($optionname == "Ondemand Service") {
			$api['args']['customfields']['permit_ondemand'] = (strtolower( $optionvalue ) == "allowed" ? 1 : 0);
		}
	}

	$api['path'] = $params['configoption2'];
	$api['rpc'] = "admin.service_create";
	$api['args']['rpc_extra'] = 1;
	$api['args']['auth'] = $params['configoption1'];
	$api['args']['plan'] = false;
	$api['args']['userid'] = $return['id'];
	$api['args']['password'] = mcp_getClientPassword( $params['clientsdetails']['userid'] );
	$api['args']['adminpassword'] = mcp_getClientPassword( $params['clientsdetails']['userid'] );
	$api['args']['plugin'] = $params['configoption3'];
	$api['args']['maxuser'] = $params['configoption5'];
	$api['args']['bitrate'] = $params['configoption6'];
	$api['args']['bandwidth'] = $params['configoption7'];
	$api['args']['sourceplugin'] = $params['configoption8'];
	$api['args']['quota'] = $params['configoption9'];
	$api['args']['proxy'] = $params['configoption11'];
	$api['args']['messengercontrol'] = $params['configoption12'];
	$api['args']['streamauth'] = $params['configoption14'];
	$__PPN = "Publish Point Name";
	$__POP = "Service Type";
	$__SL = "Public Hostname / IP and Port (Pull Only)";
	$__SUN = "Broadcasting Username";
	$__SPW = "Broadcasting Password";
	$SCLocation = "Shoutcast Address";

	if (isset( $params['customfields'][$SCLocation] )) {
		$api['args']['customfields']['shoutcast_address'] = $params['customfields'][$SCLocation];
	}


	if (isset( $params['customfields'][$__PPN] ) && 4 < strlen( $params['customfields'][$__PPN] )) {
		$api['args']['customfields']['publish_name'] = $params['customfields'][$__PPN];
		$api['args']['unique_id'] = $params['customfields'][$__PPN];
	}


	if (isset( $params['customfields'][$__POP] ) && strtolower( $params['customfields'][$__POP] ) == "pull") {
		if (isset( $params['customfields'][$__SL] ) && ( substr( $params['customfields'][$__SL], 0, 6 ) == "mms://" || substr( $params['customfields'][$__SL], 0, 7 ) == "http://" )) {
			$api['args']['customfields']['sourcelocation'] = $params['customfields'][$__SL];
		}
	}


	if (isset( $params['customfields'][$__POP] ) && strtolower( $params['customfields'][$__POP] ) == "ondemand") {
		$api['args']['customfields']['sourcelocation'] = "Ondemand:";
		$api['args']['customfields']['permit_ondemand'] = 1;
	}


	if (!empty( $params['configoption13'] ) && $params['configoption13'] != "disabled") {
		$api['args']['expire'] = strtotime( $params['configoption13'] );
	}


	if (!empty( $params['configoption10'] )) {
		$api['args']['systemid'] = $params['configoption10'];
		$api['args']['system_id'] = $params['configoption10'];
	}

	$return = mediacp_api( $api );

	if ($return['status'] != "success") {
		return $return['error'];
	}


	if ($params['configoption17'] == "enabled") {
		$fieldid = (int)get_query_val( "tblcustomfields", "id", array( "fieldname" => $__PPN, "relid" => $params['packageid'] ) );
		$checkExistance = get_query_val( "tblcustomfieldsvalues", "fieldid", array( "fieldid" => $fieldid, "relid" => (int)$params['serviceid'] ) );

		if (!$checkExistance) {
			insert_query( "tblcustomfieldsvalues", array( "fieldid" => $fieldid, "relid" => (int)$params['serviceid'], "value" => $return['serverData']['publish_name'] ) );
		}
		else {
			update_query( "tblcustomfieldsvalues", array( "value" => $return['serverData']['publish_name'] ), array( "fieldid" => $fieldid, "relid" => (int)$params['serviceid'] ) );
		}

		$fieldid = (int)get_query_val( "tblcustomfields", "id", array( "fieldname" => $__SUN, "relid" => $params['packageid'] ) );
		$checkExistance = get_query_val( "tblcustomfieldsvalues", "fieldid", array( "fieldid" => $fieldid, "relid" => (int)$params['serviceid'] ) );

		if (!$checkExistance) {
			insert_query( "tblcustomfieldsvalues", array( "fieldid" => $fieldid, "relid" => (int)$params['serviceid'], "value" => $return['serverData']['windows_username'] ) );
		}
		else {
			update_query( "tblcustomfieldsvalues", array( "value" => $return['serverData']['windows_username'] ), array( "fieldid" => $fieldid, "relid" => (int)$params['serviceid'] ) );
		}

		$fieldid = (int)get_query_val( "tblcustomfields", "id", array( "fieldname" => $__SPW, "relid" => $params['packageid'] ) );
		$checkExistance = get_query_val( "tblcustomfieldsvalues", "fieldid", array( "fieldid" => $fieldid, "relid" => (int)$params['serviceid'] ) );

		if (!$checkExistance) {
			insert_query( "tblcustomfieldsvalues", array( "fieldid" => $fieldid, "relid" => (int)$params['serviceid'], "value" => $return['serverData']['password'] ) );
		}
		else {
			update_query( "tblcustomfieldsvalues", array( "value" => $return['serverData']['password'] ), array( "fieldid" => $fieldid, "relid" => (int)$params['serviceid'] ) );
		}
	}

	update_query( "tblhosting", array( "domain" => $___url['host'] . ":" . $return['portbase'] ), array( "id" => (int)$params['serviceid'] ) );
	return "success";
}


function mediacp_TerminateAccount($params) {
	global $debug;

	$PortBaseArray = explode( ":", $params['domain'] );
	$PortBase = $PortBaseArray['1'];
	$api['args']['auth'] = $params['configoption1'];
	$api['path'] = $params['configoption2'];
	$api['rpc'] = "admin.service_remove";
	$api['args']['unique_id'] = trim( $PortBase );
	$return = mediacp_api( $api );

	if ($return['status'] != "success" && $return['error'] != "Could not locate service") {
		return $return['error'];
	}

	delete_query( "tblcustomfieldsvalues", array( "relid" => (int)$params['serviceid'] ) );
	update_query( "tblhosting", array( "domain" => $params['domain'] . ":terminated" ), array( "id" => (int)$params['serviceid'] ) );
	return "success";
}


function mediacp_SuspendAccount($params) {
	global $debug;

	$PortBaseArray = explode( ":", $params['domain'] );
	$PortBase = $PortBaseArray['1'];
	$api['args']['auth'] = $params['configoption1'];
	$api['path'] = $params['configoption2'];
	$api['rpc'] = "admin.service_suspend";
	$api['args']['unique_id'] = trim( $PortBase );
	$api['args']['Reason'] = "Suspended indefinitely by billing system";
	$api['args']['Days'] = 9999999999999;
	$return = mediacp_api( $api );

	if ($return['status'] != "success") {
		return $return['error'];
	}

	return "success";
}


function mediacp_UnsuspendAccount($params) {
	global $debug;

	$PortBaseArray = explode( ":", $params['domain'] );
	$PortBase = $PortBaseArray['1'];
	$api['args']['auth'] = $params['configoption1'];
	$api['path'] = $params['configoption2'];
	$api['rpc'] = "admin.service_unsuspend";
	$api['args']['unique_id'] = trim( $PortBase );
	$api['start'] = true;
	$return = mediacp_api( $api );

	if ($return['status'] != "success") {
		return $return['error'];
	}

	return "success";
}


function mediacp_AdminCustomButtonArray() {
	$buttonarray = array( "Start" => "start", "Stop" => "stop", "Update Package" => "ChangePackage" );
	return $buttonarray;
}


function mediacp_start($params) {
	global $debug;

	$PortBaseArray = explode( ":", $params['domain'] );
	$PortBase = $PortBaseArray['1'];
	$api['args']['auth'] = $params['configoption1'];
	$api['path'] = $params['configoption2'];
	$api['rpc'] = "service.start";
	$api['args']['unique_id'] = trim( $PortBase );
	$return = mediacp_api( $api );

	if ($return['status'] != "success") {
		return $return['error'];
	}

}


function mediacp_stop($params) {
	global $debug;

	$PortBaseArray = explode( ":", $params['domain'] );
	$PortBase = $PortBaseArray['1'];
	$api['args']['auth'] = $params['configoption1'];
	$api['path'] = $params['configoption2'];
	$api['rpc'] = "service.stop";
	$api['args']['unique_id'] = trim( $PortBase );
	$return = mediacp_api( $api );

	if ($return['status'] != "success") {
		return $return['error'];
	}

}


function mediacp_ChangePassword($params) {
	$api['args']['auth'] = $params['configoption1'];
	$api['path'] = $params['configoption2'];
	$api['rpc'] = "admin.user_update";
	$api['args']['username'] = trim( $params['clientsdetails']['email'] );
	$api['args']['hash'] = sha1( $api['args']['username'] . $params['password'] );
	$return = mediacp_api( $api );

	if ($return['status'] != "success") {
		return $return['error'];
	}

	return "success";
}


function mediacp_ClientArea($params) {
	$code = "<form action=\"" . $params['configoption2'] . "/?page=login\" method=\"post\" target=\"_blank\">
	<input type=\"hidden\" name=\"username\" value=\"" . $params['username'] . "\" />
	<input type=\"hidden\" name=\"user_password\" value=\"" . $params['password'] . "\" />
	<input type=\"submit\" name=\"login_submit\" value=\"Login to Control Panel\" />
	</form>";
	return $code;
}


function mediacp_ChangePackage($params) {
	$PortBaseArray = explode( ":", $params['domain'] );
	$PortBase = $PortBaseArray['1'];
	$api['args']['auth'] = $params['configoption1'];
	$api['path'] = $params['configoption2'];
	$api['rpc'] = "admin.service_update";
	$api['args']['unique_id'] = trim( $PortBase );
	$api['args']['maxuser'] = $params['configoption4'];
	$api['args']['bitrate'] = $params['configoption5'];
	$api['args']['bandwidth'] = $params['configoption6'];
	$api['args']['sourceplugin'] = $params['configoption7'];
	$api['args']['quota'] = $params['configoption8'];
	$api['args']['proxy'] = $params['configoption10'];
	$api['args']['messengercontrol'] = $params['configoption11'];
	$api['args']['system_id'] = $params['configoption9'];
	$return = mediacp_api( $api );

	if ($return['status'] != "success") {
		return $return['error'];
	}

	return "success";
}


function mediacp_ClientAreaCustomButtonArray() {
	$servercustombuttons = array( "stop" => "stop", "start" => "start" );
	return $servercustombuttons;
}


function mediacp_api($api) {
	$client = new IXR_Client( $api['path'] . "/system/rpc.php" );

	if (isset( $_GET['debug'] )) {
		$client->debug = true;
		echo "Integration Version: " . date( "d-m-Y.", filemtime( __FILE__ ) ) . "<br />
";
		print_r( $api );
		echo "<br />
";
	}


	if (!$client->query( $api['rpc'], $api['args'] )) {
		exit( "An error occurred - " . $client->getErrorCode() . ":" . $client->getErrorMessage() );
	}

	return $client->getResponse();
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}


if (!method_exists( "IXR_Value" )) {
	include_once "XMLRPC.class.php";
}

?>