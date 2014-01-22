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

function ensimx_ConfigOptions() {
	$configarray = array( "Package Name" => array( "Type" => "text", "Size" => "25" ) );
	return $configarray;
}


function Get_Array_Keys_UL($array = array()) {
	$recursion = "Get_Array_Keys_UL";

	if (empty( $array )) {
		return "";
	}

	$out = "<b>" . "
";
	foreach ($array as $key => $elem) {
		$out .= htmlspecialchars( $elem ) . "<br>";
	}

	$out .= "<b>" . "
";
	return $out;
}


function ensimx_IsWarning($warning) {
	if (( $warning[0] == "bind: Disable service warning" && $warning[1] == " - (WARNING: 03ff000000000014): Please use the Bind DNS Manager to edit/delete the zone for this domain." ) && $warning[2] == "") {
		return "success";
	}

	return "fail";
}


function ensimx_CreateAccount($params) {
	$symlink = $params['serverpassword'];
	$domain = $params['domain'];
	$packagetype = $params['type'];
	$username = $params['username'];
	$password = $params['password'];
	$email = $params['clientsdetails']['email'];
	$configoption1 = $params['configoption1'];
	$string_exec = "/usr/bin/sudo /usr/local/bin/AddVirtDomain-" . $symlink . " -p " . $configoption1 . " -c siteinfo,domain=" . $domain . ",admin_user=" . $username . ",tpasswd=" . $password . ",email=" . $email . " 2>&1";
	exec( $string_exec, $output );

	if ($output[0] == "" || ensimx_IsWarning( $output ) == "success") {
		$result = "success";
	}
	else {
		$result = Get_Array_Keys_UL( $output );
	}

	return $result;
}


function ensimx_TerminateAccount($params) {
	$symlink = $params['serverpassword'];
	$domain = $params['domain'];
	$string_exec = "/usr/bin/sudo /usr/local/bin/DeleteVirtDomain-" . $symlink . " " . $domain . " 2>&1";
	exec( $string_exec, $output );

	if ($output[0] == "" || ensimx_IsWarning( $output ) == "success") {
		$result = "success";
	}
	else {
		$result = Get_Array_Keys_UL( $output );
	}

	return $result;
}


function ensimx_SuspendAccount($params) {
	$symlink = $params['serverpassword'];
	$domain = $params['domain'];
	$string_exec = "/usr/bin/sudo /usr/local/bin/DisableVirtDomain-" . $symlink . " " . $domain . " 2>&1";
	exec( $string_exec, $output );

	if ($output[0] == "" || ensimx_IsWarning( $output ) == "success") {
		$result = "success";
	}
	else {
		$result = Get_Array_Keys_UL( $output );
	}

	return $result;
}


function ensimx_UnsuspendAccount($params) {
	$symlink = $params['serverpassword'];
	$domain = $params['domain'];
	$string_exec = "/usr/bin/sudo /usr/local/bin/EnableVirtDomain-" . $symlink . " " . $domain . " 2>&1";
	exec( $string_exec, $output );

	if ($output[0] == "") {
		$result = "success";
	}
	else {
		$result = Get_Array_Keys_UL( $output );
	}

	return $result;
}


function ensimx_ChangePassword($params) {
	$symlink = $params['serverpassword'];
	$domain = $params['domain'];
	$password = $params['password'];
	$string_exec = "/usr/bin/sudo /usr/local/bin/ChangeDomainPasswd-" . $symlink . " " . $domain . " " . $password . " 2>&1";
	exec( $string_exec, $output );
	exec( $password, $output );

	if ($output[0] == "") {
		$result = "success";
	}
	else {
		$result = Get_Array_Keys_UL( $output );
	}

	return $result;
}


function ensimx_LoginLink($params) {
	$domain = $params['domain'];
	$username = $params['username'];
	$code = "<a href=\"https://" . $params['serverip'] . ":19638/siteadmin/\" target=\"_blank\" class=\"moduleloginlink\">login to control panel</a>";
	return $code;
}


?>