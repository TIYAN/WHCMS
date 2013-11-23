<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Configure Banned Emails");
$aInt->title = $aInt->lang("bans", "emailtitle");
$aInt->sidebar = "config";
$aInt->icon = "configbans";
$aInt->helplink = "Security/Ban Control";

if ($email) {
	check_token("WHMCS.admin.default");
	insert_query("tblbannedemails", array("domain" => $email));
	header("Location: configbannedemails.php?success=true");
	exit();
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	delete_query("tblbannedemails", array("id" => $id));
	header("Location: configbannedemails.php?delete=true");
	exit();
}

ob_start();

if ($success) {
	infoBox($aInt->lang("bans", "emailaddsuccess"), $aInt->lang("bans", "emailaddsuccessinfo"));
}


if ($delete) {
	infoBox($aInt->lang("bans", "emaildelsuccess"), $aInt->lang("bans", "emaildelsuccessinfo"));
}

echo $infobox;
$jscode = "function doDelete(id) {
if (confirm(\"" . $aInt->lang("bans", "emaildelsure") . "\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=delete&id='+id+'" . generate_token("link") . "';
}}";
echo $aInt->Tabs(array($aInt->lang("global", "add")), true);
echo "
<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "email");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"email\" size=\"50\"> (";
echo $aInt->lang("bans", "onlydomain");
echo ")</td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"10\" width=\"1\"><br>
<div align=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("bans", "addbannedemail");
echo "\" class=\"button\"></div>

</form>

  </div>
</div>

<br>

";
$aInt->sortableTableInit("nopagination");
$result = select_query("tblbannedemails", "", "", "domain", "ASC");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$domain = $data['domain'];
	$count = $data['count'];
	$tabledata[] = array($domain, $count, "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
}

echo $aInt->sortableTable(array($aInt->lang("bans", "emaildomain"), $aInt->lang("bans", "usagecount"), ""), $tabledata);
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>