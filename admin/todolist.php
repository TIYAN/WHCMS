<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("To-Do List");
$aInt->title = $aInt->lang("todolist", "todolisttitle");
$aInt->sidebar = "utilities";
$aInt->icon = "todolist";

if ($action == "delete") {
	check_token("WHMCS.admin.default");
	delete_query("tbltodolist", array("id" => $id));
	redir();
	exit();
}


if ($action == "add") {
	check_token("WHMCS.admin.default");
	$table = "tbltodolist";
	$array = array("date" => toMySQLDate($date), "title" => $title, "description" => $description, "admin" => $admin, "status" => $status, "duedate" => toMySQLDate($duedate));
	insert_query($table, $array);
	redir();
	exit();
}


if ($action == "save") {
	check_token("WHMCS.admin.default");
	$table = "tbltodolist";
	$array = array("date" => toMySQLDate($date), "title" => $title, "description" => $description, "admin" => $admin, "status" => $status, "duedate" => toMySQLDate($duedate));
	$where = array("id" => $id);
	update_query($table, $array, $where);
	redir();
	exit();
}


if ($mass_assign) {
	check_token("WHMCS.admin.default");
	foreach ($selids as $id) {
		update_query("tbltodolist", array("admin" => $_SESSION['adminid']), array("id" => $id));
	}

	redir();
	exit();
}


if ($mass_inprogress) {
	check_token("WHMCS.admin.default");
	foreach ($selids as $id) {
		update_query("tbltodolist", array("status" => "In Progress"), array("id" => $id));
	}

	redir();
	exit();
}


if ($mass_completed) {
	check_token("WHMCS.admin.default");
	foreach ($selids as $id) {
		update_query("tbltodolist", array("status" => "Completed"), array("id" => $id));
	}

	redir();
	exit();
}


if ($mass_postponed) {
	check_token("WHMCS.admin.default");
	foreach ($selids as $id) {
		update_query("tbltodolist", array("status" => "Postponed"), array("id" => $id));
	}

	redir();
	exit();
}


if ($mass_delete) {
	check_token("WHMCS.admin.default");
	foreach ($selids as $id) {
		delete_query("tbltodolist", array("id" => $id));
	}

	redir();
	exit();
}

ob_start();

if ($action == "") {
	$jscode = "function doDelete(id) {
if (confirm(\"" . addslashes($aInt->lang("todolist", "delsuretodoitem")) . "\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=delete&id='+id+'" . generate_token("link") . "';
}}";
	echo $aInt->Tabs(array($aInt->lang("global", "searchfilter"), $aInt->lang("todolist", "additem")), true);
	echo "
<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form method=\"post\" action=\"todolist.php\"><input type=\"hidden\" name=\"filter\" value=\"true\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">Date</td><td class=\"fieldarea\"><input type=\"text\" name=\"date\" value=\"";
	echo $date;
	echo "\" class=\"datepick\"></td><td width=\"15%\" class=\"fieldlabel\">";
	echo $aInt->lang("fields", "duedate");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"duedate\" value=\"";
	echo $duedate;
	echo "\" class=\"datepick\"></td></tr>
<tr><td class=\"fieldlabel\">Title</td><td class=\"fieldarea\"><input type=\"text\" name=\"title\" size=50 value=\"";
	echo $title;
	echo "\"></td><td class=\"fieldlabel\">";
	echo $aInt->lang("fields", "admin");
	echo "</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"admin\"><option value=\"\">Any";
	$result2 = select_query("tbladmins", "id,username", "", "username", "ASC");

	while ($data2 = mysql_fetch_array($result2)) {
		$admin_id = $data2['id'];
		$admin_username = $data2['username'];
		echo "<option value=\"" . $admin_id . "\"";

		if ($admin_id == $admin) {
			echo " selected";
		}

		echo ">" . $admin_username;
	}

	echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("fields", "description");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"description\" size=55 value=\"";
	echo $description;
	echo "\"></td><td class=\"fieldlabel\">";
	echo $aInt->lang("fields", "status");
	echo "</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"status\"><option";

	if ($status == "Incomplete") {
		echo " selected";
	}

	echo ">";
	echo $aInt->lang("todolist", "incomplete");
	echo "<option";

	if ($status == "New") {
		echo " selected";
	}

	echo ">New<option";

	if ($status == "Pending") {
		echo " selected";
	}

	echo ">Pending<option";

	if ($status == "In Progress") {
		echo " selected";
	}

	echo ">In Progress<option";

	if ($status == "Completed") {
		echo " selected";
	}

	echo ">Completed<option";

	if ($status == "Postponed") {
		echo " selected";
	}

	echo ">Postponed</select></td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"10\" width=\"1\"><br>
<div align=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("global", "searchfilter");
	echo "\" class=\"button\"></div>
</form>

  </div>
</div>
<div id=\"tab1box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "?action=add\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
	echo $aInt->lang("fields", "date");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"date\" value=\"";
	echo getTodaysDate();
	echo "\" class=\"datepick\"></td><td width=\"15%\" class=\"fieldlabel\">";
	echo $aInt->lang("fields", "duedate");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"duedate\" class=\"datepick\"></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("fields", "title");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"title\" size=50></td><td class=\"fieldlabel\">";
	echo $aInt->lang("fields", "admin");
	echo "</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"admin\"><option value=\"\">None";
	$result2 = select_query("tbladmins", "id,firstname,lastname", array("disabled" => "0"), "username", "ASC");

	while ($data2 = mysql_fetch_array($result2)) {
		$admin_id = $data2['id'];
		$admin_name = $data2['firstname'] . " " . $data2['lastname'];
		echo "<option value=\"" . $admin_id . "\">" . $admin_name . "</option>";
	}

	echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("fields", "description");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"description\" size=55></td><td class=\"fieldlabel\">";
	echo $aInt->lang("fields", "status");
	echo "</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"status\"><option>New<option>Pending<option>In Progress<option>Completed<option>Postponed</select></td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"10\" width=\"1\"><br>
<DIV ALIGN=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("todolist", "addtodoitem");
	echo "\" class=\"button\"></DIV>
</form>

  </div>
</div>

<br />

";
	$aInt->sortableTableInit("duedate", "ASC");
	unset($where);

	if ($status == "Incomplete" || $status == "") {
		$where['status'] = array("sqltype" => "NEQ", "value" => "Completed");
	}
	else {
		$where['status'] = $status;
	}


	if ($date) {
		$where['date'] = toMySQLDate($date);
	}


	if ($duedate) {
		$where['duedate'] = toMySQLDate($duedate);
	}


	if ($title) {
		$where['title'] = array("sqltype" => "LIKE", "value" => $title);
	}


	if ($description) {
		$where['description'] = array("sqltype" => "LIKE", "value" => $description);
	}


	if ($admin) {
		$where['admin'] = $admin;
	}

	$table = "tbltodolist";
	$result = select_query($table, "COUNT(*)", $where, $orderby, $order);
	$data = mysql_fetch_array($result);
	$numrows = $data[0];
	$AdminsArray = array();
	$result = select_query($table, "", $where, $orderby, $order, $page * $limit . ("," . $limit));

	while ($data = mysql_fetch_array($result)) {
		++$i;
		$id = $data['id'];
		$date = $data['date'];
		$title = $data['title'];
		$description = $data['description'];
		$adminid = $data['admin'];
		$status = $data['status'];
		$duedate = $data['duedate'];
		$date = fromMySQLDate($date);

		if ($duedate == "0000-00-00") {
			$duedate = "-";
		}
		else {
			$duedate = fromMySQLDate($duedate);
		}


		if (80 < strlen($description)) {
			$description = substr($description, 0, 80) . "...";
		}


		if ($adminid) {
			if (isset($AdminsArray[$adminid])) {
				$admin = $AdminsArray[$adminid];
			}
			else {
				$result2 = select_query("tbladmins", "firstname,lastname", array("id" => $adminid));
				$data = mysql_fetch_array($result2);
				$admin = $data['firstname'] . " " . $data['lastname'];
				$AdminsArray[$adminid] = $admin;
			}
		}
		else {
			$admin = "";
		}

		$tabledata[] = array("<input type=\"checkbox\" name=\"selids[]\" value=\"" . $id . "\" class=\"checkall\">", $date, $title, $description, $admin, $status, $duedate, "<a href=\"" . $PHP_SELF . "?action=edit&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
	}

	$tableformurl = $_SERVER['PHP_SELF'];
	$tableformbuttons = "<input type=\"submit\" value=\"Assign to Me\" class=\"button\" name=\"mass_assign\"> <input type=\"submit\" value=\"Set In Progress\" class=\"button\" name=\"mass_inprogress\"> <input type=\"submit\" value=\"Set as Completed\" class=\"button\" name=\"mass_completed\"> <input type=\"submit\" value=\"Set to Postponed\" class=\"button\" name=\"mass_postponed\"> <input type=\"submit\" value=\"Delete\" class=\"button\" name=\"mass_delete\">";
	echo $aInt->sortableTable(array("checkall", array("date", "Date"), array("title", "Title"), array("description", "Description"), array("admin", "Admin"), array("status", "Status"), array("duedate", "Due Date"), "", ""), $tabledata, $tableformurl, $tableformbuttons);
}
else {
	if ($action == "edit") {
		$table = "tbltodolist";
		$fields = "";
		$where = array("id" => $id);
		$result = select_query($table, $fields, $where);
		$data = mysql_fetch_array($result);
		$date = $data['date'];
		$title = $data['title'];
		$description = $data['description'];
		$admin = $data['admin'];
		$status = $data['status'];
		$duedate = $data['duedate'];
		$date = fromMySQLDate($date);
		$duedate = fromMySQLDate($duedate);
		echo "
<p><b>";
		echo $aInt->lang("todolist", "edittodoitem");
		echo "</b></p>

<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?action=save&id=";
		echo $id;
		echo "\" name=\"calendarfrm\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("fields", "date");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"date\" value=\"";
		echo $date;
		echo "\" class=\"datepick\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "title");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"title\" size=50 value=\"";
		echo $title;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "description");
		echo "</td><td class=\"fieldarea\"><textarea name=\"description\" cols=100 rows=8>";
		echo $description;
		echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "admin");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"admin\"><option value=\"\">None";
		$result2 = select_query("tbladmins", "id,firstname,lastname,disabled", "", "username", "ASC");

		while ($data2 = mysql_fetch_array($result2)) {
			$admin_id = $data2['id'];
			$admin_name = $data2['firstname'] . " " . $data2['lastname'];
			$admin_disabled = $data2['disabled'];
			echo "<option value=\"" . $admin_id . "\"";

			if ($admin_id == $admin) {
				echo " selected";
			}

			echo ">" . $admin_name . ($admin_disabled ? " (" . $aInt->lang("global", "disabled") . ")" : "") . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "duedate");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"duedate\" value=\"";
		echo $duedate;
		echo "\" class=\"datepick\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "status");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"status\"><option";

		if ($status == "Incomplete") {
			echo " selected";
		}

		echo ">Incomplete<option";

		if ($status == "New") {
			echo " selected";
		}

		echo ">New<option";

		if ($status == "Pending") {
			echo " selected";
		}

		echo ">Pending<option";

		if ($status == "In Progress") {
			echo " selected";
		}

		echo ">In Progress<option";

		if ($status == "Completed") {
			echo " selected";
		}

		echo ">Completed<option";

		if ($status == "Postponed") {
			echo " selected";
		}

		echo ">Postponed</select></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("global", "savechanges");
		echo "\" class=\"button\"></p>

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