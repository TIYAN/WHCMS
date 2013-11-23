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
$aInt = new WHMCS_Admin("Configure Support Departments");
$aInt->title = $aInt->lang("supportticketescalations", "supportticketescalationstitle");
$aInt->sidebar = "config";
$aInt->icon = "todolist";
$aInt->helplink = "Support Ticket Escalations";

if ($action == "save") {
	check_token("WHMCS.admin.default");

	if (is_array($departments)) {
		$departments = implode(",", $departments);
	}


	if (is_array($statuses)) {
		$statuses = implode(",", $statuses);
	}


	if (is_array($priorities)) {
		$priorities = implode(",", $priorities);
	}


	if (is_array($notify)) {
		$notify = implode(",", $notify);
	}


	if ($id) {
		update_query("tblticketescalations", array("name" => $name, "departments" => $departments, "statuses" => $statuses, "priorities" => $priorities, "timeelapsed" => $timeelapsed, "newdepartment" => $newdepartment, "newstatus" => $newstatus, "newpriority" => $newpriority, "flagto" => $flagto, "notify" => $notify, "addreply" => $addreply), array("id" => $id));
		header("Location: " . $_SERVER['PHP_SELF'] . "?saved=true");
	}
	else {
		insert_query("tblticketescalations", array("name" => $name, "departments" => $departments, "statuses" => $statuses, "priorities" => $priorities, "timeelapsed" => $timeelapsed, "newdepartment" => $newdepartment, "newstatus" => $newstatus, "newpriority" => $newpriority, "flagto" => $flagto, "notify" => $notify, "addreply" => $addreply));
		header("Location: " . $_SERVER['PHP_SELF'] . "?added=true");
	}

	exit();
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	delete_query("tblticketescalations", array("id" => $id));
	header("Location: configticketescalations.php?deleted=true");
	exit();
}

ob_start();

if ($action == "") {
	if ($added) {
		infoBox($aInt->lang("supportticketescalations", "ruleaddsuccess"), $aInt->lang("supportticketescalations", "ruleaddsuccessdesc"));
	}


	if ($saved) {
		infoBox($aInt->lang("supportticketescalations", "ruleeditsuccess"), $aInt->lang("supportticketescalations", "ruleeditsuccessdesc"));
	}


	if ($deleted) {
		infoBox($aInt->lang("supportticketescalations", "ruledelsuccess"), $aInt->lang("supportticketescalations", "ruledelsuccessdesc"));
	}

	echo $infobox;
	$jscode = "function doDelete(id) {
if (confirm(\"" . addslashes($aInt->lang("taxconfig", "delsureescalationrule")) . "\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=delete&id='+id+'" . generate_token("link") . "';
}}";
	echo "
<p>";
	echo $aInt->lang("supportticketescalations", "escalationrulesinfo");
	echo "</p>

<div class=\"contentbox\">
";
	echo $aInt->lang("supportticketescalations", "croncommandreq");
	echo "<br /><input type=\"text\" size=\"100\" value=\"php -q ";
	echo ROOTDIR . "/" . $whmcs->get_admin_folder_name();
	echo "/cron.php escalations\" />
</div>

<p><B>";
	echo $aInt->lang("fields", "options");
	echo ":</B> <a href=\"";
	echo $_SERVER['PHP_SELF'];
	echo "?action=manage\">";
	echo $aInt->lang("supportticketescalations", "addnewrule");
	echo "</a></p>

";
	$aInt->sortableTableInit("nopagination");
	$result = select_query("tblticketescalations", "", "", "name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$name = $data['name'];
		$tabledata[] = array($name, "<a href=\"" . $_SERVER['PHP_SELF'] . ("?action=manage&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"") . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
	}

	echo $aInt->sortableTable(array($aInt->lang("addons", "name"), "", ""), $tabledata);
}
else {
	if ($action == "manage") {
		if ($id) {
			$edittitle = "Edit Rule";
			$result = select_query("tblticketescalations", "", array("id" => $id));
			$data = mysql_fetch_array($result);
			$id = $data['id'];
			$name = $data['name'];
			$departments = $data['departments'];
			$statuses = $data['statuses'];
			$priorities = $data['priorities'];
			$timeelapsed = $data['timeelapsed'];
			$newdepartment = $data['newdepartment'];
			$newstatus = $data['newstatus'];
			$newpriority = $data['newpriority'];
			$flagto = $data['flagto'];
			$notify = $data['notify'];
			$addreply = $data['addreply'];
			$departments = explode(",", $departments);
			$statuses = explode(",", $statuses);
			$priorities = explode(",", $priorities);
			$notify = explode(",", $notify);
		}
		else {
			$edittitle = "Add New Rule";
			$departments = $statuses = $priorities = $notify = array();
		}

		echo "<h2>" . $edittitle . "</h2>";
		echo "
<form method=\"post\" action=\"";
		echo $_SERVER['PHP_SELF'];
		echo "?action=save\">
<input type=\"hidden\" name=\"id\" value=\"";
		echo $id;
		echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
		echo $aInt->lang("addons", "name");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" size=\"50\" value=\"";
		echo $name;
		echo "\"></td></tr>
</table>

<p><b>";
		echo $aInt->lang("supportticketescalations", "conditions");
		echo "</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
		echo $aInt->lang("supportticketescalations", "departments");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"departments[]\" size=\"4\" multiple=\"true\">";
		$result = select_query("tblticketdepartments", "", "", "name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$departmentid = $data['id'];
			$departmentname = $data['name'];
			echo "<option value=\"" . $departmentid . "\"";

			if (in_array($departmentid, $departments)) {
				echo " selected";
			}

			echo ">" . $departmentname . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketescalations", "statuses");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"statuses[]\" size=\"4\" multiple=\"true\">
";
		$result = select_query("tblticketstatuses", "", "", "sortorder", "ASC");

		while ($data = mysql_fetch_assoc($result)) {
			$title = $data['title'];
			echo "<option";

			if (in_array($title, $statuses)) {
				echo " selected";
			}

			echo ">" . $title . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">Priorities</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"priorities[]\" size=\"3\" multiple=\"true\">
<option";

		if (in_array("Low", $priorities)) {
			echo " selected";
		}

		echo ">";
		echo $aInt->lang("supportticketescalations", "prioritylow");
		echo "</option>
<option";

		if (in_array("Medium", $priorities)) {
			echo " selected";
		}

		echo ">";
		echo $aInt->lang("supportticketescalations", "prioritymedium");
		echo "</option>
<option";

		if (in_array("High", $priorities)) {
			echo " selected";
		}

		echo ">";
		echo $aInt->lang("supportticketescalations", "priorityhigh");
		echo "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketescalations", "timeelapsed");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"timeelapsed\" size=\"10\" value=\"";
		echo $timeelapsed;
		echo "\"> ";
		echo $aInt->lang("supportticketescalations", "minsincelastreply");
		echo "</td></tr>
</table>

<p><b>";
		echo $aInt->lang("supportticketescalations", "actions");
		echo "</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
		echo $aInt->lang("supportticketescalations", "department");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"newdepartment\"><option value=\"\">- ";
		echo $aInt->lang("supportticketescalations", "nochange");
		echo " -</option>";
		$result = select_query("tblticketdepartments", "", "", "name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$departmentid = $data['id'];
			$departmentname = $data['name'];
			echo "<option value=\"" . $departmentid . "\"";

			if ($newdepartment == $departmentid) {
				echo " selected";
			}

			echo ">" . $departmentname . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "status");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"newstatus\"><option value=\"\">- ";
		echo $aInt->lang("supportticketescalations", "nochange");
		echo " -</option>
";
		$result = select_query("tblticketstatuses", "", "", "sortorder", "ASC");

		while ($data = mysql_fetch_assoc($result)) {
			$title = $data['title'];
			echo "<option";

			if ($title == $newstatus) {
				echo " selected";
			}

			echo ">" . $title . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketescalations", "priority");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"newpriority\"><option value=\"\">- ";
		echo $aInt->lang("supportticketescalations", "nochange");
		echo " -</option>
<option";

		if ($newpriority == "Low") {
			echo " selected";
		}

		echo ">";
		echo $aInt->lang("supportticketescalations", "prioritylow");
		echo "</option>
<option";

		if ($newpriority == "Medium") {
			echo " selected";
		}

		echo ">";
		echo $aInt->lang("supportticketescalations", "prioritymedium");
		echo "</option>
<option";

		if ($newpriority == "High") {
			echo " selected";
		}

		echo ">";
		echo $aInt->lang("supportticketescalations", "priorityhigh");
		echo "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketescalations", "flagto");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"flagto\"><option value=\"\">- ";
		echo $aInt->lang("supportticketescalations", "nochange");
		echo " -</option>";
		$result = select_query("tbladmins", "", "", "username", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$flag_adminid = $data['id'];
			$flag_adminusername = $data['username'];
			echo "<option value=\"" . $flag_adminid . "\"";

			if ($flag_adminid == $flagto) {
				echo " selected";
			}

			echo ">" . $flag_adminusername . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketescalations", "notifyadmins");
		echo "</td><td class=\"fieldarea\">
<label><input type=\"checkbox\" name=\"notify[]\" value=\"all\"";

		if (in_array("all", $notify)) {
			echo " checked";
		}

		echo " /> ";
		echo $aInt->lang("supportticketescalations", "notifyadminsdesc");
		echo "</label>
<div style=\"padding:5px;\">";
		echo $aInt->lang("supportticketescalations", "alsonotify");
		echo ":</div>
";
		$result = select_query("tbladmins", "", "", "username", "ASC");

		while ($data = mysql_fetch_array($result)) {
			echo "<label><input type=\"checkbox\" name=\"notify[]\" value=\"" . $data['id'] . "\"";

			if (in_array($data['id'], $notify)) {
				echo " checked";
			}

			echo " /> ";

			if ($data['disabled'] == 1) {
				echo "<span class=\"disabledtext\">";
			}

			echo $data['username'] . " (" . $data['firstname'] . " " . $data['lastname'] . ")";

			if ($data['disabled'] == 1) {
				echo " - " . $aInt->lang("global", "disabled") . "</span> ";
			}

			echo "</label>";
		}

		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "addreply");
		echo "</td><td class=\"fieldarea\"><textarea name=\"addreply\" rows=\"15\" style=\"width:90%;\">";
		echo $addreply;
		echo "</textarea></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("global", "savechanges");
		echo "\" class=\"button\" /></p>

</form>

";
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>