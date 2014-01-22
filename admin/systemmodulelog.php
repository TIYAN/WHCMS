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
$aInt = new WHMCS_Admin("View Module Debug Log");
$aInt->title = $aInt->lang("system", "moduledebuglog");
$aInt->sidebar = "utilities";
$aInt->icon = "logs";
$aInt->helplink = "Troubleshooting Module Problems";

if ($enable) {
	check_token("WHMCS.admin.default");

	if (isset($CONFIG['ModuleDebugMode'])) {
		update_query("tblconfiguration", array("value" => "on"), array("setting" => "ModuleDebugMode"));
	}
	else {
		insert_query("tblconfiguration", array("setting" => "ModuleDebugMode", "value" => "on"));
	}

	$CONFIG['ModuleDebugMode'] = "on";
}


if ($disable) {
	check_token("WHMCS.admin.default");
	update_query("tblconfiguration", array("value" => ""), array("setting" => "ModuleDebugMode"));
	$CONFIG['ModuleDebugMode'] = "";
}


if ($reset) {
	check_token("WHMCS.admin.default");
	delete_query("tblmodulelog", "id!=''");
	redir();
}


if (!$id) {
	$aInt->sortableTableInit("id");
	$numrows = get_query_val("tblmodulelog", "COUNT(*)", "", "id", "DESC");
	$result = select_query("tblmodulelog", "", "", "id", "DESC", $page * $limit . "," . $limit);

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$date = $data['date'];
		$module = $data['module'];
		$action = $data['action'];
		$request = $data['request'];
		$response = $data['response'];
		$arrdata = $data['arrdata'];

		if ($arrdata) {
			$response = $arrdata;
		}

		$date = fromMySQLDate($date, "time");
		$tabledata[] = array("<a href=\"?id=" . $id . "\">" . $date . "</a>", $module, $action, "<textarea rows=\"5\" style=\"width:100%;\">" . htmlentities($request) . "</textarea>", "<textarea rows=\"5\" style=\"width:100%;\">" . htmlentities($response) . "</textarea>");
	}

	$content = "<p>" . $aInt->lang("system", "moduledebuglogdesc") . "</p>
<form method=\"post\" action=\"\">
<p align=\"center\">";

	if ($CONFIG['ModuleDebugMode']) {
		$content .= "<input type=\"submit\" name=\"disable\" value=\"" . $aInt->lang("system", "disabledebuglogging") . "\" />";
	}
	else {
		$content .= "<input type=\"submit\" name=\"enable\" value=\"" . $aInt->lang("system", "enabledebuglogging") . "\" />";
	}

	$content .= " <input type=\"submit\" name=\"reset\" value=\"" . $aInt->lang("system", "resetdebuglogging") . "\" /></p>
</form>
" . $aInt->sortableTable(array(array("", $aInt->lang("fields", "date"), 120), array("", $aInt->lang("fields", "module"), 120), array("", $aInt->lang("fields", "action"), 150), $aInt->lang("fields", "request"), $aInt->lang("fields", "response")), $tabledata);
}
else {
	$result = select_query("tblmodulelog", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	$id = $data['id'];
	$date = $data['date'];
	$module = $data['module'];
	$action = $data['action'];
	$request = $data['request'];
	$response = $data['response'];
	$arrdata = $data['arrdata'];
	$date = fromMySQLDate($date, "time");
	$content = $aInt->lang("fields", "date") . ": " . $date . " - " . $aInt->lang("fields", "module") . ": " . $module . " - " . $aInt->lang("fields", "action") . ": " . $action . "<br /><br />
<b>" . $aInt->lang("fields", "request") . "</b><br />
<textarea rows=\"10\" style=\"width:100%;\">" . htmlentities($request) . "</textarea><br /><br />
<b>" . $aInt->lang("fields", "response") . "</b><br />
<textarea rows=\"20\" style=\"width:100%;\">" . htmlentities($response) . "</textarea><br /><br />";

	if ($arrdata) {
		$content .= "<b>" . $aInt->lang("fields", "interpretedresponse") . "</b><br />
<textarea rows=\"20\" style=\"width:100%;\">" . htmlentities($arrdata) . "</textarea><br /><br />";
	}

	$content .= "<a href=\"?\">&laquo; Back</a>";
}

$aInt->content = $content;
$aInt->display();
?>