<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Configure Ticket Statuses");
$aInt->title = $aInt->lang("setup", "ticketstatuses");
$aInt->sidebar = "config";
$aInt->icon = "clients";
$aInt->helplink = "Support Ticket Statuses";

if ($action == "save") {
	check_token("WHMCS.admin.default");

	if ($id) {
		update_query("tblticketstatuses", array("title" => trim($title), "color" => $color, "sortorder" => $sortorder, "showactive" => $showactive, "showawaiting" => $showawaiting, "autoclose" => $autoclose), array("id" => $id));
		header("Location: configticketstatuses.php?update=true");
	}
	else {
		insert_query("tblticketstatuses", array("title" => trim($title), "color" => $color, "sortorder" => $sortorder, "showactive" => $showactive, "showawaiting" => $showawaiting, "autoclose" => $autoclose));
		header("Location: configticketstatuses.php?added=true");
	}

	exit();
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	$result = select_query("tblticketstatuses", "title", array("id" => $id));
	$data = mysql_fetch_assoc($result);
	$title = $data['title'];
	update_query("tbltickets", array("status" => "Closed"), array("status" => $title));
	delete_query("tblticketstatuses", array("id" => $id));
	header("Location: configticketstatuses.php?delete=true");
	exit();
}

ob_start();

if ($added) {
	infoBox($aInt->lang("ticketstatusconfig", "statusaddtitle"), $aInt->lang("ticketstatusconfig", "statusadddesc"));
}


if ($update) {
	infoBox($aInt->lang("ticketstatusconfig", "statusedittitle"), $aInt->lang("ticketstatusconfig", "statuseditdesc"));
}


if ($delete) {
	infoBox($aInt->lang("ticketstatusconfig", "statusdeltitle"), $aInt->lang("ticketstatusconfig", "statusdeldesc"));
}

echo $infobox;
$aInt->deleteJSConfirm("doDelete", "ticketstatusconfig", "delsureticketstatus", "?action=delete&id=");
echo "
<p>";
echo $aInt->lang("ticketstatusconfig", "pagedesc");
echo "</p>

<p>";
echo "<s";
echo "trong>";
echo $aInt->lang("fields", "options");
echo ":</strong> <a href=\"";
echo $PHP_SELF;
echo "\"><img src=\"images/icons/add.png\" align=\"top\" /> ";
echo $aInt->lang("global", "addnew");
echo "</a></p>

";
$aInt->sortableTableInit("nopagination");
$result = select_query("tblticketstatuses", "", "", "sortorder", "ASC");

while ($data = mysql_fetch_assoc($result)) {
	$statusid = $data['id'];
	$title = $data['title'];
	$color = $data['color'];
	$showactive = $data['showactive'];
	$showawaiting = $data['showawaiting'];
	$autoclose = $data['autoclose'];
	$sortorder = $data['sortorder'];
	$showactive = ($showactive ? "<img src=\"images/icons/tick.png\">" : "<img src=\"images/icons/disabled.png\">");
	$showawaiting = ($showawaiting ? "<img src=\"images/icons/tick.png\">" : "<img src=\"images/icons/disabled.png\">");
	$autoclose = ($autoclose ? "<img src=\"images/icons/tick.png\">" : "<img src=\"images/icons/disabled.png\">");

	if (4 < $statusid) {
		$delete = "<a href=\"#\" onClick=\"doDelete('" . $statusid . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>";
	}
	else {
		$delete = "";
	}

	$tabledata[] = array("<span style=\"font-weight:bold;color:" . $color . "\">" . $title . "</span>", $showactive, $showawaiting, $autoclose, $sortorder, "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $statusid . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", $delete);
}

echo $aInt->sortableTable(array($aInt->lang("fields", "title"), $aInt->lang("ticketstatusconfig", "includeinactivetickets"), $aInt->lang("ticketstatusconfig", "includeinawaitingreply"), $aInt->lang("ticketstatusconfig", "autoclose"), $aInt->lang("products", "sortorder"), "", ""), $tabledata);
echo "\r\n";
echo "<s";
echo "cript type=\"text/javascript\" src=\"../includes/jscript/jquery.miniColors.js\"></script>
<link rel=\"stylesheet\" type=\"text/css\" href=\"../includes/jscript/css/jquery.miniColors.css\" />
";
$jquerycode = "$(\".colorpicker\").miniColors();";
echo "
<h2>";

if ($action == "edit") {
	$result = select_query("tblticketstatuses", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	$title = $data['title'];
	$color = $data['color'];
	$sortorder = $data['sortorder'];
	$showactive = $data['showactive'];
	$showawaiting = $data['showawaiting'];
	$autoclose = $data['autoclose'];
	echo $aInt->lang("ticketstatusconfig", "edit");
}
else {
	$title = $showactive = $showawaiting = $autoclose = "";
	$color = "#000000";
	echo $aInt->lang("ticketstatusconfig", "add");
}

echo "</h2>

<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?action=save&id=";
echo $id;
echo "\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"25%\" class=\"fieldlabel\">";
echo $aInt->lang("clientsummary", "filetitle");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"title\" size=\"30\" value=\"";
echo $title;
echo "\"";

if ($id && $id <= 4) {
	echo " readonly=\"true\"";
}

echo " /></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("ticketstatusconfig", "statuscolor");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"color\" size=\"10\" value=\"";
echo $color;
echo "\" class=\"colorpicker\" /></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("ticketstatusconfig", "includeinactivetickets");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"showactive\" value=\"1\"";

if ($showactive) {
	echo " checked";
}

echo " /></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("ticketstatusconfig", "includeinawaitingreply");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"showawaiting\" value=\"1\"";

if ($showawaiting) {
	echo " checked";
}

echo " /></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("ticketstatusconfig", "autoclose");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"autoclose\" value=\"1\"";

if ($autoclose) {
	echo " checked";
}

echo " /></td></tr>
<tr><td width=\"25%\" class=\"fieldlabel\">";
echo $aInt->lang("products", "sortorder");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"sortorder\" size=\"10\" value=\"";
echo $sortorder;
echo "\" /></td></tr>
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