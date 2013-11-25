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
$aInt = new WHMCS_Admin("View WHOIS Lookup Log");
$aInt->title = $aInt->lang("system", "whois");
$aInt->sidebar = "utilities";
$aInt->icon = "logs";
$aInt->sortableTableInit("date");
$numrows = get_query_val("tblwhoislog", "COUNT(*)", "");
$result = select_query("tblwhoislog", "", "", "id", "DESC", $page * $limit . "," . $limit);

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$date = $data['date'];
	$domain = $data['domain'];
	$ip = $data['ip'];
	$tabledata[] = array(fromMySQLDate($date, true), "<a href=\"whois.php?domain=" . $domain . "\">" . $domain . "</a>", "<a href=\"http://www.geoiptool.com/en/?IP=" . $ip . "\" target=\"_blank\">" . $ip . "</a>");
}

$content = $aInt->sortableTable(array($aInt->lang("fields", "date"), $aInt->lang("fields", "domain"), $aInt->lang("fields", "ipaddress")), $tabledata);
$aInt->content = $content;
$aInt->display();
?>