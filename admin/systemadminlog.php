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

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View Admin Log");
$aInt->title = $aInt->lang("system", "adminloginlog");
$aInt->sidebar = "utilities";
$aInt->icon = "logs";
$aInt->sortableTableInit("date");
$query = "DELETE FROM tbladminlog WHERE lastvisit='00000000000000'";
$result = full_query($query);
$date = date("Y-m-d H:i:s", mktime(date("H"), date("i") - 15, date("s"), date("m"), date("d"), date("Y")));
$query = "UPDATE tbladminlog SET logouttime=lastvisit WHERE lastvisit<'" . $date . "' and logouttime='00000000000000'";
$result = full_query($query);
$numrows = get_query_val("tbladminlog", "COUNT(*)", "");
$result = select_query("tbladminlog", "", "", "id", "DESC", $page * $limit . "," . $limit);

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$logintime = $data['logintime'];
	$lastvisit = $data['lastvisit'];
	$logouttime = $data['logouttime'];
	$admin_uname = $data['adminusername'];
	$ipaddress = $data['ipaddress'];
	$logintime = fromMySQLDate($logintime, true);
	$lastvisit = fromMySQLDate($lastvisit, true);

	if ($logouttime == "0000-00-00 00:00:00") {
		$logouttime = "-";
	}
	else {
		$logouttime = fromMySQLDate($logouttime, true);
	}

	$tabledata[] = array($logintime, $lastvisit, $logouttime, $admin_uname, "<a href=\"http://www.geoiptool.com/en/?IP=" . $ipaddress . "\" target=\"_blank\">" . $ipaddress . "</a>");
}

$content = $aInt->sortableTable(array($aInt->lang("system", "logintime"), $aInt->lang("system", "lastaccess"), $aInt->lang("system", "logouttime"), $aInt->lang("fields", "username"), $aInt->lang("fields", "ipaddress")), $tabledata);
$aInt->content = $content;
$aInt->display();
?>