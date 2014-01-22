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
	$tabledata[] = array(fromMySQLDate($date, true), "<a href=\"#\" onclick=\"$('#frmWhoisDomain').val('" . addslashes($domain) . ("');$('#frmWhois').submit();return false\">" . $domain . "</a>"), "<a href=\"http://www.geoiptool.com/en/?IP=" . $ip . "\" target=\"_blank\">" . $ip . "</a>");
}

$content = $aInt->sortableTable(array($aInt->lang("fields", "date"), $aInt->lang("fields", "domain"), $aInt->lang("fields", "ipaddress")), $tabledata);
$content .= "
<form method=\"post\" action=\"whois.php\" target=\"_blank\" id=\"frmWhois\">
<input type=\"hidden\" name=\"domain\" value=\"\" id=\"frmWhoisDomain\" />
</form>
";
$aInt->content = $content;
$aInt->display();
?>