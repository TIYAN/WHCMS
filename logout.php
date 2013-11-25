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

define("CLIENTAREA", true);
require "init.php";

if (!isset($_SESSION['uid'])) {
	redir("", "index.php");
}

run_hook("ClientLogout", array("userid" => $_SESSION['uid']));
unset($_SESSION['uid']);
unset($_SESSION['cid']);
unset($_SESSION['upw']);
wDelCookie("User");
$pagetitle = $_LANG['logouttitle'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"clientarea.php\">" . $_LANG['clientareatitle'] . "</a> > <a href=\"logout.php\">" . $_LANG['logouttitle'] . "</a>";
$pageicon = "images/clientarea_big.gif";
$templatefile = "logout";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);
outputClientArea($templatefile);
?>