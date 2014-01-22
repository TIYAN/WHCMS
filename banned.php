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
 **/

define("CLIENTAREA", true);
require "init.php";
$pagetitle = $_LANG['bannedtitle'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"banned.php\">" . $_LANG['bannedtitle'] . "</a>";
$pageicon = "";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);
$remote_ip = $whmcs->get_user_ip();
$ip = explode(".", $remote_ip);
$ip = db_escape_numarray($ip);
$remote_ip1 = $ip[0] . "." . $ip[1] . "." . $ip[2] . ".*";
$remote_ip2 = $ip[0] . "." . $ip[1] . ".*.*";
$data = get_query_vals("tblbannedips", "", "ip='" . db_escape_string($remote_ip) . "' OR ip='" . db_escape_string($remote_ip1) . "' OR ip='" . db_escape_string($remote_ip2) . "'", "id", "DESC");
$id = $data['id'];
$reason = $data['reason'];
$expires = fromMySQLDate($data['expires'], true, true);

if (!$id) {
	redir("", "index.php");
}

$smartyvalues['ip'] = htmlspecialchars($remote_ip);
$smartyvalues['reason'] = $reason;
$smartyvalues['expires'] = $expires;
$templatefile = "banned";
outputClientArea($templatefile);
?>