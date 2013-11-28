<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$auth = new WHMCS_Auth();

if ($auth->logout()) {
	redir("logout=1", "login.php");
}

redir("", "login.php");
?>