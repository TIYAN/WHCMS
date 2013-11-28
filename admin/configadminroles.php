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
$aInt = new WHMCS_Admin("Configure Admin Roles");
$aInt->title = $aInt->lang("setup", "adminroles");
$aInt->sidebar = "config";
$aInt->icon = "adminroles";
$aInt->helplink = "Administrator Roles";
$aInt->requiredFiles(array("reportfunctions"));
$chart = new WHMCSChart();

if ($action == "addrole") {
	check_token("WHMCS.admin.default");
	$adminrole = insert_query("tbladminroles", array("name" => $name));
	redir("action=edit&id=" . $adminrole);
	exit();
}


if ($action == "duplicaterole") {
	check_token("WHMCS.admin.default");
	$result = select_query("tbladminroles", "", array("id" => $existinggroup));
	$data = mysql_fetch_array($result);
	$widgets = $data['widgets'];
	$systememails = $data['systememails'];
	$accountemails = $data['accountemails'];
	$supportemails = $data['supportemails'];
	$roleid = insert_query("tbladminroles", array("name" => $newname, "widgets" => $widgets, "systememails" => $systememails, "accountemails" => $accountemails, "supportemails" => $supportemails));
	$result = select_query("tbladminperms", "", array("roleid" => $existinggroup));

	while ($data = mysql_fetch_array($result)) {
		insert_query("tbladminperms", array("roleid" => $roleid, "permid" => $data['permid']));
	}

	redir("action=edit&id=" . $roleid);
	exit();
}


if ($action == "save") {
	check_token("WHMCS.admin.default");
	update_query("tbladminroles", array("name" => $name, "widgets" => implode(",", $widget), "systememails" => $systememails, "accountemails" => $accountemails, "supportemails" => $supportemails), array("id" => $id));
	delete_query("tbladminperms", array("roleid" => $id));

	if ($adminperms) {
		foreach ($adminperms as $k => $v) {
			insert_query("tbladminperms", array("roleid" => $id, "permid" => $k));
		}
	}

	redir("saved=true");
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	$admincount = get_query_val("tbladmins", "COUNT(id)", array("roleid" => $id));

	if ($admincount) {
		redir();
	}

	delete_query("tbladminroles", array("id" => $id));
	delete_query("tbladminperms", array("roleid" => $id));
	redir("deleted=true");
}

ob_start();

if (!$action) {
	if ($saved) {
		infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("global", "changesuccessdesc"));
	}


	if ($deleted) {
		infoBox($aInt->lang("adminroles", "deletesuccess"), $aInt->lang("adminroles", "deletesuccessinfo"));
	}

	echo $infobox;
	$aInt->deleteJSConfirm("doDelete", "adminroles", "suredelete", $_SERVER['PHP_SELF'] . "?action=delete&id=");
	echo "
<p>";
	echo $aInt->lang("adminroles", "description");
	echo "</p>
<p><b>";
	echo $aInt->lang("adminroles", "options");
	echo ":</b> <a href=\"configadminroles.php?action=add\">";
	echo $aInt->lang("adminroles", "addnew");
	echo "</a> | <a href=\"configadminroles.php?action=duplicate\">";
	echo $aInt->lang("adminroles", "duplicate");
	echo "</a></p>

";
	$aInt->sortableTableInit("nopagination");
	$result = select_query("tbladminroles", "", "", "name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$deletejs = (3 < $data['id'] ? "doDelete('" . $data['id'] . "')" : "alert('" . $aInt->lang("adminroles", "nodeldefault", 1) . "')");
		$assigned = array();
		$result2 = select_query("tbladmins", "id,username,disabled", array("roleid" => $data['id']), "username", "ASC");

		while ($data2 = mysql_fetch_array($result2)) {
			$assigned[] = "<a href=\"configadmins.php?action=manage&id=" . $data2['id'] . "\"" . ($data2['disabled'] ? " style=\"color:#ccc;\"" : "") . ">" . $data2['username'] . "</a>";
		}


		if (count($assigned)) {
			$deletejs = "alert('" . $aInt->lang("adminroles", "nodelinuse", 1) . "')";
		}
		else {
			$assigned[] = $aInt->lang("global", "none");
		}

		$tabledata[] = array($data['name'], implode(", ", $assigned), "<a href=\"" . $PHP_SELF . "?action=edit&id=" . $data['id'] . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"" . $deletejs . "\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
	}

	echo $aInt->sortableTable(array($aInt->lang("fields", "groupname"), $aInt->lang("supportticketdepts", "assignedadmins"), "", ""), $tabledata);
}
else {
	if ($action == "add") {
		echo "
<p>";
		echo "<s";
		echo "trong>";
		echo $aInt->lang("adminroles", "addnew");
		echo "</strong></p>
<form method=\"post\" action=\"";
		echo $_SERVER['PHP_SELF'];
		echo "?action=addrole\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("fields", "name");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" size=\"40\" value=\"";
		echo $name;
		echo "\"></td></tr>
</table>
<p align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("global", "continue");
		echo " >>\" class=\"button\" /></p>
</form>

";
	}
	else {
		if ($action == "duplicate") {
			echo "
<p>";
			echo "<s";
			echo "trong>";
			echo $aInt->lang("adminroles", "duplicate");
			echo "</strong></p>
<form method=\"post\" action=\"";
			echo $_SERVER['PHP_SELF'];
			echo "?action=duplicaterole\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
			echo $aInt->lang("adminroles", "existinggroupname");
			echo "</td><td class=\"fieldarea\">";
			echo "<s";
			echo "elect name=\"existinggroup\">";
			$result = select_query("tbladminroles", "", "", "name", "ASC");

			while ($data = mysql_fetch_array($result)) {
				echo "<option value=\"" . $data['id'] . "\">" . $data['name'] . "</otpion>";
			}

			echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("adminroles", "newgroupname");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"newname\" size=\"40\" value=\"";
			echo $name;
			echo "\"></td></tr>
</table>
<p align=\"center\"><input type=\"submit\" value=\"";
			echo $aInt->lang("global", "continue");
			echo " >>\" class=\"button\" /></p>
</form>

";
		}
		else {
			if ($action == "edit") {
				$result = select_query("tbladminroles", "", array("id" => $id));
				$data = mysql_fetch_array($result);
				$name = $data['name'];
				$widgets = $data['widgets'];
				$systememails = $data['systememails'];
				$accountemails = $data['accountemails'];
				$supportemails = $data['supportemails'];
				$widgets = explode(",", $widgets);
				$adminpermsarray = getAdminPermsArray();
				$totalpermissions = count($adminpermsarray);
				$totalpermissionspercolumn = round($totalpermissions / 3);
				echo "<s";
				echo "cript type=\"text/javascript\">
function zCheckAll(oForm) {
	var oElems = oForm.elements;
	for (var i=0;oElems.length>i;i++) {
  		if (oElems[i].type == \"checkbox\")
			oElems[i].checked = true;
	}
}
function zUncheckAll(oForm) {
	var oElems = oForm.elements;
	for (var i=0;oElems.length>i;i++) {
  		if (oElems[i].type == \"checkbox\")
			oElems[i].checked = false;
	}
}
</script>
<form met";
				echo "hod=\"post\" action=\"";
				echo $_SERVER['PHP_SELF'];
				echo "?action=save&id=";
				echo $id;
				echo "\" name=\"frmperms\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
				echo $aInt->lang("fields", "name");
				echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" size=\"40\" value=\"";
				echo $name;
				echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
				echo $aInt->lang("adminroles", "permissions");
				echo "</td><td class=\"fieldarea\">";
				echo "<table width=\"100%\"><tr><td valign=\"top\" width=\"34%\">";
				$rowcount = 0;
				$colcount = 0;
				foreach ($adminpermsarray as $k => $v) {
					echo ("<input type=\"checkbox\" name=\"adminperms[" . $k . "]") . "\" id=\"adminperms" . $k . "\"";
					$result = select_query("tbladminperms", "COUNT(*)", array("roleid" => $id, "permid" => $k));
					$data = mysql_fetch_array($result);

					if ($data[0]) {
						echo " checked";
					}

					echo "> <label for=\"adminperms" . $k . "\">" . $aInt->lang("permissions", $k) . "</label><br>";
					++$rowcount;

					if ($rowcount == $totalpermissionspercolumn) {
						if ($colcount < 2) {
							echo "</td><td valign=\"top\" width=\"33%\">";
						}

						$rowcount = 0;
						++$colcount;
						continue;
					}
				}

				echo "</td></tr></table>";
				echo "<div align=\"right\"><a href=\"#\" onClick=\"zCheckAll(frmperms);return false\">";
				echo $aInt->lang("adminroles", "checkall");
				echo "</a> | <a href=\"#\" onClick=\"zUncheckAll(frmperms);return false\">";
				echo $aInt->lang("adminroles", "uncheckall");
				echo "</a></div></td></tr>
<tr><td class=\"fieldlabel\">";
				echo $aInt->lang("adminroles", "widgets");
				echo "</td><td class=\"fieldarea\">

<table width=\"100%\"><tr><td width=\"33%\" valign=\"top\">
";
				$hooksdir = ROOTDIR . "/modules/widgets/";

				if (is_dir($hooksdir)) {
					$dh = opendir($hooksdir);

					while (false !== $hookfile = readdir($dh)) {
						if (is_file($hooksdir . $hookfile) && $hookfile != "index.php") {
							$extension = explode(".", $hookfile);
							$extension = end($extension);

							if ($extension == "php") {
								include $hooksdir . $hookfile;
							}
						}
					}
				}

				closedir($dh);
				function load_admin_home_widgets() {
					global $aInt;
					global $hooks;

					$hook_name = "AdminHomeWidgets";
					$args = array("adminid" => $_SESSION['adminid'], "loading" => "<img src=\"images/loading.gif\" align=\"absmiddle\" /> " . $aInt->lang("global", "loading"));

					if (!array_key_exists($hook_name, $hooks)) {
						return array();
					}

					reset($hooks[$hook_name]);
					$results = array();

					while (list($key,$hook) = each($hooks[$hook_name])) {
						$widgetname = substr($hook['hook_function'], 7);

						if (function_exists($hook['hook_function'])) {
							$res = call_user_func($hook['hook_function'], $args);

							if ($res) {
								$results[$widgetname] = $res['title'];
							}
						}
					}

					return $results;
				}

				$listwidgets = load_admin_home_widgets();
				asort($listwidgets);
				$totalportlets = ceil(count($listwidgets) / 3);
				$i = 0;
				foreach ($listwidgets as $k => $v) {
					echo "<input type=\"checkbox\" name=\"widget[]\" value=\"" . $k . "\" id=\"widget" . $k . "\"";

					if (in_array($k, $widgets)) {
						echo " checked";
					}

					echo " /> <label for=\"widget" . $k . "\">" . $v . "</label><br />";

					if ($totalportlets <= $i) {
						echo "</td><td width=\"33%\" valign=\"top\">";
						$i = 0;
						continue;
					}

					++$i;
				}

				echo "</td></tr></table>

</td></tr>
<tr><td class=\"fieldlabel\">";
				echo $aInt->lang("adminroles", "emailmessages");
				echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"systememails\" value=\"1\"";

				if ($systememails) {
					echo " checked";
				}

				echo "> ";
				echo $aInt->lang("adminroles", "systememails");
				echo "<br /><input type=\"checkbox\" name=\"accountemails\" value=\"1\"";

				if ($accountemails) {
					echo " checked";
				}

				echo "> ";
				echo $aInt->lang("adminroles", "accountemails");
				echo "<br /><input type=\"checkbox\" name=\"supportemails\" value=\"1\"";

				if ($supportemails) {
					echo " checked";
				}

				echo "> ";
				echo $aInt->lang("adminroles", "supportemails");
				echo "</td></tr>
</table>
<p align=\"center\"><input type=\"submit\" value=\"";
				echo $aInt->lang("global", "savechanges");
				echo "\" class=\"button\" /></p>
</form>
";
			}
		}
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->display();
?>