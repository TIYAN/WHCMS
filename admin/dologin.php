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
 **/

define("ADMINAREA", true);
require "../init.php";
session_regenerate_id();
$username = $whmcs->get_req_var("username");
$password = $whmcs->get_req_var("password");
$auth = new WHMCS_Auth();
$twofa = new WHMCS_2FA();

if ($twofa->isActiveAdmins() && isset($_SESSION['2faverify'])) {
	$twofa->setAdminID($_SESSION['2faadminid']);

	if (WHMCS_Session::get("2fabackupcodenew")) {
		WHMCS_Session::delete("2fabackupcodenew");
		WHMCS_Session::delete("2faverify");
		WHMCS_Session::delete("2faadminid");
		WHMCS_Session::delete("2farememberme");

		if (isset($_SESSION['admloginurlredirect'])) {
			header("Location: " . $_SESSION['admloginurlredirect']);
			unset($_SESSION['admloginurlredirect']);
		}
		else {
			redir("", "index.php");
		}

		exit();
	}


	if ($whmcs->get_req_var("backupcode")) {
		$success = $twofa->verifyBackupCode($whmcs->get_req_var("code"));
	}
	else {
		$success = $twofa->moduleCall("verify");
	}


	if ($success) {
		$adminfound = $auth->getInfobyID($_SESSION['2faadminid']);
		$auth->setSessionVars();
		$auth->processLogin();

		if ($_SESSION['2farememberme']) {
			$auth->setRememberMeCookie();
		}
		else {
			$auth->unsetRememberMeCookie();
		}


		if ($whmcs->get_req_var("backupcode")) {
			WHMCS_Session::set("2fabackupcodenew", true);
			redir("newbackupcode=1", "login.php");
		}

		WHMCS_Session::delete("2faverify");
		WHMCS_Session::delete("2faadminid");
		WHMCS_Session::delete("2farememberme");

		if (isset($_SESSION['admloginurlredirect'])) {
			header("Location: " . $_SESSION['admloginurlredirect']);
			unset($_SESSION['admloginurlredirect']);
		}
		else {
			redir("", "index.php");
		}

		exit();
	}

	redir(($whmcs->get_req_var("backupcode") ? "backupcode=1&" : "") . "incorrect=1", "login.php");
}


if (!trim($username) || !trim($password)) {
	redir("incorrect=1", "login.php");
}

$adminfound = $auth->getInfobyUsername($username);

if ($adminfound) {
	if ($auth->comparePassword($password)) {
		if ($whmcs->get_req_var("language")) {
			$_SESSION['adminlang'] = $whmcs->get_req_var("language");
		}


		if ($twofa->isActiveAdmins() && $auth->isTwoFactor()) {
			$_SESSION['2faverify'] = true;
			$_SESSION['2faadminid'] = $auth->getAdminID();
			$_SESSION['2farememberme'] = $whmcs->get_req_var("rememberme");
			redir("", "login.php");
		}

		$auth->setSessionVars();

		if ($whmcs->get_req_var("rememberme")) {
			$auth->setRememberMeCookie();
		}
		else {
			$auth->unsetRememberMeCookie();
		}

		$auth->processLogin();

		if (isset($_SESSION['admloginurlredirect'])) {
			header("Location: " . $_SESSION['admloginurlredirect']);
			unset($_SESSION['admloginurlredirect']);
		}
		else {
			redir("", "index.php");
		}

		exit();
	}
}

$auth->failedLogin();
redir("incorrect=1", "login.php");
?>