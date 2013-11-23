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
$aInt = new WHMCS_Admin("Configure Client Groups");
$aInt->title = $aInt->lang("clientgroups", "title");
$aInt->sidebar = "config";
$aInt->icon = "clients";
$aInt->helplink = "Client Groups";

if ($action == "savegroup") {
	check_token("WHMCS.admin.default");
	insert_query("tblclientgroups", array("groupname" => $groupname, "groupcolour" => $groupcolour, "discountpercent" => $discountpercent, "susptermexempt" => $susptermexempt, "separateinvoices" => $separateinvoices));
	header("Location: configclientgroups.php?added=true");
	exit();
}


if ($action == "updategroup") {
	check_token("WHMCS.admin.default");
	update_query("tblclientgroups", array("groupname" => $groupname, "groupcolour" => $groupcolour, "discountpercent" => $discountpercent, "susptermexempt" => $susptermexempt, "separateinvoices" => $separateinvoices), array("id" => $groupid));
	header("Location: configclientgroups.php?update=true");
	exit();
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	$result = select_query("tblclients", "", array("groupid" => $id));
	$numaccounts = mysql_num_rows($result);

	if (0 < $numaccounts) {
		header("Location: configclientgroups.php?deleteerror=true");
		exit();
	}
	else {
		delete_query("tblclientgroups", array("id" => $id));
		header("Location: configclientgroups.php?deletesuccess=true");
		exit();
	}
}


if ($action == "edit") {
	$result = select_query("tblclientgroups", "", array("id" => $id));
	$data = mysql_fetch_assoc($result);
	foreach ($data as $name => $value) {
		$$name = $value;
	}
}

ob_start();

if ($added) {
	infoBox($aInt->lang("clientgroups", "addsuccess"), $aInt->lang("clientgroups", "addsuccessinfo"));
}


if ($update) {
	infoBox($aInt->lang("clientgroups", "editsuccess"), $aInt->lang("clientgroups", "editsuccessinfo"));
}


if ($deletesuccess) {
	infoBox($aInt->lang("clientgroups", "delsuccess"), $aInt->lang("clientgroups", "delsuccessinfo"));
}


if ($deleteerror) {
	infoBox($aInt->lang("global", "erroroccurred"), $aInt->lang("clientgroups", "delerrorinfo"));
}

echo $infobox;
$jscode = "function doDelete(id) {
if (confirm(\"" . $aInt->lang("clientgroups", "delsure") . "\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=delete&id='+id+'" . generate_token("link") . "';
}}";
echo "
<p>";
echo $aInt->lang("clientgroups", "info");
echo "</p>

";
$aInt->sortableTableInit("nopagination");
$result = select_query("tblclientgroups", "", "");

while ($data = mysql_fetch_assoc($result)) {
	$suspterm = ($data['susptermexempt'] == "on" ? $aInt->lang("global", "yes") : $aInt->lang("global", "no"));
	$separateinv = ($data['separateinvoices'] == "on" ? $aInt->lang("global", "yes") : $aInt->lang("global", "no"));
	$groupcol = ($data['groupcolour'] ? "<div style=\"width:75px;background-color:" . $data['groupcolour'] . "\">" . $aInt->lang("clientgroups", "sample") . "</div>" : "");
	$tabledata[] = array($data['groupname'], $groupcol, $data['discountpercent'], $suspterm, $separateinv, "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $data['id'] . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $data['id'] . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
}

echo $aInt->sortableTable(array($aInt->lang("clientgroups", "groupname"), $aInt->lang("clientgroups", "groupcolour"), $aInt->lang("clientgroups", "perdiscount"), $aInt->lang("clientgroups", "susptermexempt"), $aInt->lang("clients", "separateinvoices"), "", ""), $tabledata);
$setaction = ($action == "edit" ? "updategroup" : "savegroup");
echo "
";
echo "<s";
echo "cript type=\"text/javascript\" src=\"../includes/jscript/jquery.miniColors.js\"></script>
<link rel=\"stylesheet\" type=\"text/css\" href=\"../includes/jscript/css/jquery.miniColors.css\" />
";
$jquerycode = "$(\".colorpicker\").miniColors();";
echo "
<h2>";

if ($action == "edit") {
	echo $aInt->lang("global", "edit");
}
else {
	echo $aInt->lang("global", "add");
}

echo " ";
echo $aInt->lang("clientgroups", "clientgroup");
echo "</h2>

<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?action=";
echo $setaction;
echo "\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"25%\" class=\"fieldlabel\">";
echo $aInt->lang("clientgroups", "groupname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"groupname\" size=\"40\" value=\"";
echo $groupname;
echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clientgroups", "groupcolour");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"groupcolour\" size=\"10\" value=\"";
echo $groupcolour;
echo "\" class=\"colorpicker\" /></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clientgroups", "grpdispercent");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"discountpercent\" size=\"10\" value=\"";
echo $discountpercent;
echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clientgroups", "exemptsusterm");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"susptermexempt\"";

if ($susptermexempt) {
	echo "checked";
}

echo " /></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "separateinvoicesdesc");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"separateinvoices\"";

if ($separateinvoices) {
	echo "checked";
}

echo " /></td></tr>
<input type=\"hidden\" name=\"groupid\" value=\"";
echo $id;
echo "\" />
</table>
<p align=center><input type=\"submit\" value=\"";
echo $aInt->lang("global", "savechanges");
echo "\" class=\"button\"></p>
</form>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>