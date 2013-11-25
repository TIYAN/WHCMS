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

function doUnsubscribe($email, $key) {
	global $whmcs;
	global $_LANG;

	$whmcs->get_hash();

	if (!$email) {
		return $_LANG['pwresetemailrequired'];
	}

	$result = select_query("tblclients", "id,email,emailoptout", array("email" => $email));
	$data = mysql_fetch_array($result);
	$userid = $data['id'];
	$email = $data['email'];
	$emailoptout = $data['emailoptout'];
	$newkey = sha1($email . $userid . $cc_encryption_hash);

	if ($newkey == $key) {
		if (!$userid) {
			return $_LANG['unsubscribehashinvalid'];
		}


		if ($emailoptout == 1) {
			return $_LANG['alreadyunsubscribed'];
		}

		update_query("tblclients", array("emailoptout" => "1"), array("id" => $userid));
		sendMessage("Unsubscribe Confirmation", $userid);
		logActivity("Unsubscribed From Marketing Emails - User ID:" . $userid, $userid);
		return null;
	}

	return $_LANG['unsubscribehashinvalid'];
}

define("CLIENTAREA", true);
require "init.php";
$pagetitle = $_LANG['unsubscribe'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"clientarea.php\">" . $_LANG['clientareatitle'] . "</a> > <a href=\"unsubscribe.php\">" . $_LANG['unsubscribe'] . "</a>";
initialiseClientArea($pagetitle, "", $breadcrumbnav);
$email = $whmcs->get_req_var("email");
$key = $whmcs->get_req_var("key");

if ($email) {
	$errormessage = doUnsubscribe($email, $key);
	$smartyvalues['errormessage'] = $errormessage;

	if (!$errormessage) {
		$smartyvalues['successful'] = true;
	}

	$templatefile = "unsubscribe";
	outputClientArea($templatefile);
	return 1;
}

redir("index.php");
?>