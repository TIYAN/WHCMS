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
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!function_exists("validateClientLogin")) {
	require ROOTDIR . "/includes/clientfunctions.php";
}

$_SESSION['adminid'] = "";

if (validateClientLogin($email, $password2)) {
	$apiresults = array("result" => "success", "userid" => $_SESSION['uid']);

	if ($_SESSION['cid']) {
		$apiresults['contactid'] = $_SESSION['cid'];
	}

	$apiresults['passwordhash'] = $_SESSION['upw'];
	return 1;
}

$apiresults = array("result" => "error", "message" => "Email or Password Invalid");
?>