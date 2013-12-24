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
	exit("This file cannot be accessed directly");
}


if ($_POST['userid']) {
	$result = select_query("tblclients", "", array("id" => $_POST['userid']));
}
else {
	$result = select_query("tblclients", "", array("email" => $_POST['email']));
}

$data = mysql_fetch_array($result);

if ($data['id']) {
	$password = $data['password'];

	if ($CONFIG['NOMD5']) {
		$password = decrypt($password);
	}

	$apiresults = array("result" => "success", "password" => $password);
	return 1;
}

$apiresults = array("result" => "error", "message" => "Client ID Not Found");
?>