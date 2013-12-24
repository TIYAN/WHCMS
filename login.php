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
 **/

if (!defined("WHMCS")) {
	header("Location: clientarea.php");
	exit();
}

$_SESSION['loginurlredirect'] = html_entity_decode($_SERVER['REQUEST_URI']);

if (WHMCS_Session::get("2faverifyc")) {
	$templatefile = "logintwofa";

	if (WHMCS_Session::get("2fabackupcodenew")) {
		$smartyvalues['newbackupcode'] = true;
	}
	else {
		if ($whmcs->get_req_var("incorrect")) {
			$smartyvalues['incorrect'] = true;
		}
	}

	$twofa = new WHMCS_2FA();

	if ($twofa->setClientID(WHMCS_Session::get("2faclientid"))) {
		if (!$twofa->isActiveClients() || !$twofa->isEnabled()) {
			WHMCS_Session::destroy();
			redir();
		}


		if ($whmcs->get_req_var("backupcode")) {
			$smartyvalues['backupcode'] = true;
		}
		else {
			$challenge = $twofa->moduleCall("challenge");

			if ($challenge) {
				$smartyvalues['challenge'] = $challenge;
			}
			else {
				$smartyvalues['error'] = "Bad 2 Factor Auth Module. Please contact support.";
			}
		}
	}
	else {
		$smartyvalues['error'] = "An error occurred. Please try again.";
	}
}
else {
	$templatefile = "login";
	$smartyvalues['loginpage'] = true;
	$smartyvalues['formaction'] = "dologin.php";

	if ($whmcs->get_req_var("incorrect")) {
		$smartyvalues['incorrect'] = true;
	}
}

outputClientArea($templatefile);
exit();
?>