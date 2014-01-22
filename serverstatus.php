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

function getPortStatus($params, &$smarty) {
	global $servers;

	$num = $params['num'];
	$res = @fsockopen($servers[$num]['ipaddress'], $params['port'], $errno, $errstr, 5);
	$status = "<img src=\"images/status" . ($res ? "ok" : "failed") . ".gif\" alt=\"" . $_LANG["serverstatus" . ($res ? "on" : "off") . "line"] . "\" width=\"16\" height=\"16\" />";
	return $status;
}

define("CLIENTAREA", true);
require "init.php";
$pagetitle = $_LANG['serverstatustitle'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"serverstatus.php\">" . $_LANG['serverstatustitle'] . "</a>";
$templatefile = "serverstatus";
$pageicon = "images/status_big.gif";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);

if ($CONFIG['NetworkIssuesRequireLogin'] && !isset($_SESSION['uid'])) {
	$goto = "serverstatus";
	require "login.php";
}

releaseSession();
$servers = array();
$result = select_query("tblservers", "", "disabled=0 AND statusaddress!=''", "name", "ASC");

while ($data = mysql_fetch_array($result)) {
	$name = $data['name'];
	$ipaddress = $data['ipaddress'];
	$statusaddress = $data['statusaddress'];

	if (substr($statusaddress, 0 - 1, 1) != "/") {
		$statusaddress .= "/";
	}


	if (substr($statusaddress, 0 - 9, 9) != "index.php") {
		$statusaddress .= "index.php";
	}

	$servers[] = array("name" => $name, "ipaddress" => $ipaddress, "statusaddr" => $statusaddress, "phpinfourl" => $statusaddress . "?action=phpinfo", "serverload" => $serverload, "uptime" => $uptime, "phpver" => $phpver, "mysqlver" => $mysqlver, "zendver" => $zendver);
}

$smarty->assign("servers", $servers);
$smarty->register_function("get_port_status", "getPortStatus");

if ($whmcs->get_req_var("getstats")) {
	$num = $whmcs->get_req_var("num");
	$statusaddress = $servers[$num]['statusaddr'];
	$filecontents = curlCall($statusaddress, "");
	preg_match('/\<load\>(.*?)\<\/load\>/', $filecontents, $serverload);
	preg_match('/\<uptime\>(.*?)\<\/uptime\>/', $filecontents, $uptime);
	preg_match('/\<phpver\>(.*?)\<\/phpver\>/', $filecontents, $phpver);
	preg_match('/\<mysqlver\>(.*?)\<\/mysqlver\>/', $filecontents, $mysqlver);
	preg_match('/\<zendver\>(.*?)\<\/zendver\>/', $filecontents, $zendver);
	$serverload = $serverload[1];
	$uptime = $uptime[1];
	$phpver = $phpver[1];
	$mysqlver = $mysqlver[1];
	$zendver = $zendver[1];

	if (!$serverload) {
		$serverload = $_LANG['serverstatusnotavailable'];
	}


	if (!$uptime) {
		$uptime = $_LANG['serverstatusnotavailable'];
	}

	echo json_encode(array("load" => $serverload, "uptime" => $uptime, "phpver" => $phpver, "mysqlver" => $mysqlver, "zendver" => $zendver));
	exit();
}


if ($whmcs->get_req_var("ping")) {
	$num = (int)$whmcs->get_req_var("num");
	$port = (int)$whmcs->get_req_var("port");

	if (is_array($servers[$num])) {
		$res = @fsockopen($servers[$num]['ipaddress'], $port, $errno, $errstr, 5);
		echo "<img src=\"images/status" . ($res ? "ok" : "failed") . ".gif\" alt=\"" . $_LANG["serverstatus" . ($res ? "on" : "off") . "line"] . "\" width=\"16\" height=\"16\" />";

		if ($res) {
			fclose($res);
		}
	}

	exit();
}

include "networkissues.php";
outputClientArea($templatefile);
?>