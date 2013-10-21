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
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View Clients Products/Services");

if ($userid && $hostingid) {
	redir("userid=" . $userid . "&id=" . $hostingid, "clientsservices.php");
}


if ($userid && $id) {
	redir("userid=" . $userid . "&id=" . $id, "clientsservices.php");
}


if ($id) {
	redir("id=" . $id, "clientsservices.php");
}


if ($userid) {
	redir("userid=" . $userid, "clientsservices.php");
}

redir("", "clientsservices.php");
?>