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
$aInt = new WHMCS_Admin("View Banned IPs");
$aInt->title = $aInt->lang("bans", "iptitle");
$aInt->sidebar = "config";
$aInt->icon = "configbans";
$aInt->helplink = "Security/Ban Control";

if ($whmcs->get_req_var("ip")) {
	check_token("WHMCS.admin.default");
	checkPermission("Add Banned IP");
	$expires = $year . $month . $day . $hour . $minutes . "00";
	insert_query("tblbannedips", array("ip" => $ip, "reason" => $reason, "expires" => $expires));
	header("Location: configbannedips.php?success=true");
	exit();
}


if ($whmcs->get_req_var("delete")) {
	check_token("WHMCS.admin.default");
	checkPermission("Unban Banned IP");
	delete_query("tblbannedips", array("id" => $id));
	header("Location: configbannedips.php?deleted=true");
	exit();
}

ob_start();

if ($whmcs->get_req_var("success")) {
	infoBox($aInt->lang("bans", "ipaddsuccess"), $aInt->lang("bans", "ipaddsuccessinfo"));
}


if ($whmcs->get_req_var("deleted")) {
	infoBox($aInt->lang("bans", "ipdelsuccess"), $aInt->lang("bans", "ipdelsuccessinfo"));
}

echo $infobox;
$aInt->deleteJSConfirm("doDelete", "bans", "ipdelsure", $_SERVER['PHP_SELF'] . "?delete=true&id=");
echo $aInt->Tabs(array($aInt->lang("global", "add"), $aInt->lang("global", "searchfilter")), true);
echo "
<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form method=\"post\" action=\"";
echo $PHP_SELF;
$new_ban_time = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 7, date("Y"));
echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "ipaddress");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ip\" size=\"20\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("bans", "banreason");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"reason\" size=\"90\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("bans", "banexpires");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"day\" size=\"1\" maxlength=\"2\" value=\"";
echo date("d", $new_ban_time);
echo "\">/<input type=\"text\" name=\"month\" size=\"1\" maxlength=\"2\" value=\"";
echo date("m", $new_ban_time);
echo "\">/<input type=\"text\" name=\"year\" size=\"3\" maxlength=\"4\" value=\"";
echo date("Y", $new_ban_time);
echo "\"> <input type=\"text\" name=\"hour\" size=\"1\" maxlength=\"2\" value=\"";
echo date("H", $new_ban_time);
echo "\">:<input type=\"text\" name=\"minutes\" size=\"1\" maxlength=\"2\" value=\"";
echo date("i", $new_ban_time);
echo "\"> (";
echo $aInt->lang("bans", "format");
echo ")</td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"10\" width=\"1\"><br>

<div align=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("bans", "addbannedip");
echo "\" name=\"postreply\" class=\"button\"></div>

</form>

  </div>
</div>
<div id=\"tab1box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "\">
Filter for ";
echo "<s";
echo "elect name=\"filterfor\"><option";

if ($filterfor == "IP Address") {
	echo " selected";
}

echo ">";
echo $aInt->lang("fields", "ipaddress");
echo "<option";

if ($filterfor == "Ban Reason") {
	echo " selected";
}

echo ">";
echo $aInt->lang("bans", "banreason");
echo "</select> matching <input type=\"text\" name=\"filtertext\" size=\"40\" value=\"";
echo $filtertext;
echo "\"> <input type=\"submit\" value=\"";
echo $aInt->lang("global", "search");
echo "\" name=\"postreply\" class=\"button\">
</form>

  </div>
</div>

<br>

";
$aInt->sortableTableInit("nopagination");
$where = array();

if ($filterfor = $whmcs->get_req_var("filterfor")) {
	$filtertext = $whmcs->get_req_var("filtertext");

	if ($filterfor == "IP Address") {
		$where = array("ip" => $filtertext);
	}
	else {
		$where = array("reason" => array("sqltype" => "LIKE", "value" => $filtertext));
	}
}

$result = select_query("tblbannedips", "", $where, "id", "DESC");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$ip = $data['ip'];
	$reason = $data['reason'];
	$expires = $data['expires'];
	$expires = fromMySQLDate($expires, "time");
	$tabledata[] = array("<a href=\"http://www.geoiptool.com/en/?IP=" . $ip . "\" target=\"_blank\">" . $ip . "</a>", $reason, $expires, "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
}

echo $aInt->sortableTable(array($aInt->lang("fields", "ipaddress"), $aInt->lang("bans", "banreason"), $aInt->lang("bans", "banexpires"), ""), $tabledata);
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>