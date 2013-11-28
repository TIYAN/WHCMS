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

function buildCategoriesList($level, $parentlevel, $exclude = "") {
	global $catid;

	$result = select_query("tblticketpredefinedcats", "", array("parentid" => $level), "name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$parentid = $data['parentid'];
		$category = $data['name'];

		if ($id == $exclude) {
			continue;
		}

		echo "<option value=\"" . $id . "\"";

		if ($id == $catid) {
			echo " selected";
		}

		echo ">";
		$i = 1;

		while ($i <= $parentlevel) {
			echo "- ";
			++$i;
		}

		echo "" . $category . "</option>";
		buildCategoriesList($id, $parentlevel + 1);
	}

}

function deletePreDefCat($catid) {
	$result = select_query("tblticketpredefinedcats", "", array("parentid" => $catid));

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		delete_query("tblticketpredefinedreplies", array("catid" => $id));
		delete_query("tblticketpredefinedcats", array("id" => $id));
		deletePreDefCat($id);
	}

}

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Manage Predefined Replies");
$aInt->title = $aInt->lang("support", "predefreplies");
$aInt->sidebar = "support";
$aInt->icon = "ticketspredefined";

if ($addreply == "true") {
	check_token("WHMCS.admin.default");
	checkPermission("Create Predefined Replies");
	$lastid = insert_query("tblticketpredefinedreplies", array("catid" => $catid, "name" => $name));
	logActivity("Added New Predefined Reply - " . $title);
	redir("action=edit&id=" . $lastid);
	exit();
}


if ($sub == "save") {
	check_token("WHMCS.admin.default");
	checkPermission("Manage Predefined Replies");
	$table = "tblticketpredefinedreplies";
	$array = array("catid" => $catid, "name" => $name, "reply" => $reply);
	$where = array("id" => $id);
	update_query($table, $array, $where);
	logActivity("Modified Predefined Reply (ID: " . $id . ")");
	redir("catid=" . $catid . "&save=true");
	exit();
}


if ($sub == "savecat") {
	check_token("WHMCS.admin.default");
	checkPermission("Manage Predefined Replies");
	$table = "tblticketpredefinedcats";
	$array = array("parentid" => $parentid, "name" => $name);
	$where = array("id" => $id);
	update_query($table, $array, $where);
	logActivity("Modified Predefined Reply Category (ID: " . $id . ")");
	redir("catid=" . $parentid . "&savecat=true");
	exit();
}


if ($addcategory == "true") {
	check_token("WHMCS.admin.default");
	checkPermission("Create Predefined Replies");
	insert_query("tblticketpredefinedcats", array("parentid" => $catid, "name" => $catname));
	logActivity("Added New Predefined Reply Category - " . $catname);
	redir("catid=" . $catid . "&addedcat=true");
	exit();
}


if ($sub == "delete") {
	check_token("WHMCS.admin.default");
	checkPermission("Delete Predefined Replies");
	delete_query("tblticketpredefinedreplies", array("id" => $id));
	logActivity("Deleted Predefined Reply (ID: " . $id . ")");
	redir("catid=" . $catid . "&delete=true");
	exit();
}


if ($sub == "deletecategory") {
	check_token("WHMCS.admin.default");
	checkPermission("Delete Predefined Replies");
	delete_query("tblticketpredefinedreplies", array("catid" => $id));
	delete_query("tblticketpredefinedcats", array("id" => $id));
	deletePreDefCat($id);
	logActivity("Deleted Predefined Reply Category (ID: " . $id . ")");
	redir("catid=" . $catid . "&deletecat=true");
	exit();
}

ob_start();

if ($action == "") {
	if ($addedcat) {
		infoBox($aInt->lang("global", "success"), $aInt->lang("support", "predefaddedcat"));
	}


	if ($save) {
		infoBox($aInt->lang("global", "success"), $aInt->lang("support", "predefsave"));
	}


	if ($savecat) {
		infoBox($aInt->lang("global", "success"), $aInt->lang("support", "predefsavecat"));
	}


	if ($delete) {
		infoBox($aInt->lang("global", "success"), $aInt->lang("support", "predefdelete"));
	}


	if ($deletecat) {
		infoBox($aInt->lang("global", "success"), $aInt->lang("support", "predefdeletecat"));
	}

	echo $infobox;

	if ($catid) {
		$catid = get_query_val("tblticketpredefinedcats", "id", array("id" => $catid));
	}

	$aInt->deleteJSConfirm("doDelete", "support", "predefdelsure", $_SERVER['PHP_SELF'] . "?catid=" . $catid . "&sub=delete&id=");
	$aInt->deleteJSConfirm("doDeleteCat", "support", "predefdelcatsure", $_SERVER['PHP_SELF'] . "?catid=" . $catid . "&sub=deletecategory&id=");
	echo $aInt->Tabs(array($aInt->lang("support", "addcategory"), $aInt->lang("support", "addpredef"), $aInt->lang("global", "searchfilter")), true);
	echo "
<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "?catid=";
	echo $catid;
	echo "&addcategory=true\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
	echo $aInt->lang("support", "catname");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"catname\" size=\"40\"></tr>
</table>
<img src=\"images/spacer.gif\" width=\"1\" height=\"10\"><br>
<div align=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("support", "addcategory");
	echo "\" class=\"button\"></div>
</form>

  </div>
</div>
<div id=\"tab1box\" class=\"tabbox\">
  <div id=\"tab_content\">

";

	if ($catid != "") {
		echo "<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?catid=";
		echo $catid;
		echo "&addreply=true\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("support", "articlename");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" size=\"60\"></td></tr>
</table>
<img src=\"images/spacer.gif\" width=\"1\" height=\"10\"><br>
<div align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("support", "addarticle");
		echo "\" class=\"button\"></div>
</form>
";
	}
	else {
		echo $aInt->lang("support", "pdnotoplevel");
	}

	echo "
  </div>
</div>
<div id=\"tab2box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form action=\"";
	echo $PHP_SELF;
	echo "\" method=\"post\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("support", "articlename");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"title\" size=\"40\" value=\"";
	echo $title;
	echo "\" /></td><td class=\"fieldlabel\">";
	echo $aInt->lang("mergefields", "message");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"message\" size=\"60\" value=\"";
	echo $message;
	echo "\" /></td></tr>
</table>
<input type=\"hidden\" name=\"search\" value=\"search\" />
<img src=\"images/spacer.gif\" height=\"10\" width=\"1\"><br>
<div align=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("global", "searchfilter");
	echo "\" class=\"button\"></div>
</form>

  </div>
</div>

";

	if ($catid == "") {
		$catid = "0";
	}


	if ($catid != "0") {
		$result = select_query("tblticketpredefinedcats", "", array("id" => $catid));
		$data = mysql_fetch_array($result);
		$catparentid = $data['parentid'];
		$catname = $data['name'];
		$catbreadcrumbnav = " > <a href=\"" . $PHP_SELF . "?catid=" . $catid . "\">" . $catname . "</a>";

		while ($catparentid != "0") {
			$result = select_query("tblticketpredefinedcats", "", array("id" => $catparentid));
			$data = mysql_fetch_array($result);
			$cattempid = $data['id'];
			$catparentid = $data['parentid'];
			$catname = $data['name'];
			$catbreadcrumbnav = " > <a href=\"" . $PHP_SELF . "?catid=" . $cattempid . "\">" . $catname . "</a>" . $catbreadcrumbnav;
		}

		$breadcrumbnav .= $catbreadcrumbnav;
		echo "<p>" . $aInt->lang("support", "youarehere") . (": <a href=\"" . $PHP_SELF . "\">") . $aInt->lang("support", "toplevel") . "</a> " . $breadcrumbnav . "</p>";
	}

	$result = select_query("tblticketpredefinedcats", "", array("parentid" => $catid), "name", "ASC");
	$numcats = mysql_num_rows($result);
	echo "
";

	if ($numcats != "0" && !$search) {
		echo "
<p><b>";
		echo $aInt->lang("support", "categories");
		echo "</b></p>

<table width=100%><tr>
";

		if ($catid == "") {
			$catid = "0";
		}

		$result = select_query("tblticketpredefinedcats", "", array("parentid" => $catid), "name", "ASC");
		$i = 0;

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$name = $data['name'];
			$result3 = select_query("tblticketpredefinedreplies", "id", array("catid" => $id));
			$numarticles = mysql_num_rows($result3);
			echo "<td width=33%><img src=\"../images/folder.gif\" align=\"absmiddle\"> <a href=\"" . $PHP_SELF . "?catid=" . $id . "\"><b>" . $name . "</b></a> (" . $numarticles . ") <a href=\"" . $PHP_SELF . "?action=editcat&id=" . $id . "\"><img src=\"images/edit.gif\" align=\"absmiddle\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . ("\" /></a> <a href=\"#\" onClick=\"doDeleteCat(" . $id . ");return false\"><img src=\"images/delete.gif\" align=\"absmiddle\" border=\"0\"alt=\"") . $aInt->lang("global", "delete") . ("\" /></a><br>" . $description . "</td>");
			++$i;

			if ($i % 3 == 0) {
				echo "</tr><tr><td><br></td></tr><tr>";
				$i = 0;
			}
		}

		echo "</tr></table>

";
	}
	else {
		if ($catid == "0" && !$search) {
			echo "<p><b>" . $aInt->lang("support", "nocatsfound") . "</b></p>";
		}
	}

	$where = "";

	if (!$search) {
		$where .= " AND catid='" . db_escape_string($catid) . "'";
	}


	if ($title) {
		$where .= " AND name LIKE '%" . db_escape_string($title) . "%'";
	}


	if ($message) {
		$where .= " AND reply LIKE '%" . db_escape_string($message) . "%'";
	}


	if ($where) {
		$where = substr($where, 5);
	}

	$result = select_query("tblticketpredefinedreplies", "", $where, "name", "ASC");
	$numarticles = mysql_num_rows($result);

	if ($search) {
		echo "<p>" . $aInt->lang("support", "youarehere") . (": <a href=\"" . $PHP_SELF . "\">") . $aInt->lang("support", "toplevel") . ("</a>  > <a href=\"" . $PHP_SELF . "\">") . $aInt->lang("global", "search") . "</a></p>";
	}


	if ($numarticles != "0") {
		echo "
<p><b>";
		echo $aInt->lang("support", "replies");
		echo "</b></p>

<table width=100%><tr>
";
		$result = select_query("tblticketpredefinedreplies", "", $where, "name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$name = $data['name'];
			$reply = strip_tags(stripslashes($data['reply']));
			$reply = substr($reply, 0, 150) . "...";
			echo "<p><img src=\"../images/article.gif\" align=\"absmiddle\"> <a href=\"" . $PHP_SELF . "?action=edit&id=" . $id . "\"><b>" . $name . "</b></a> <a href=\"#\" onClick=\"doDelete(" . $id . ");return false\"><img src=\"images/delete.gif\" align=\"absmiddle\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . ("\" /></a><br>" . $reply . "</p>");
		}

		echo "</tr></table>

";
	}
	else {
		if ($catid != "0" || $search) {
			echo "<p><b>" . $aInt->lang("support", "norepliesfound") . "</b></p>";
		}
	}

	echo "
";
}
else {
	if ($action == "edit") {
		$result = select_query("tblticketpredefinedreplies", "", array("id" => $id));
		$data = mysql_fetch_array($result);
		$catid = $data['catid'];
		$name = $data['name'];
		$reply = $data['reply'];
		echo "
<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?sub=save&id=";
		echo $id;
		echo "\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("support", "category");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"catid\">";
		buildCategoriesList(0, 0);
		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "replyname");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" value=\"";
		echo $name;
		echo "\" size=70></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("mergefields", "title");
		echo "</td><td class=\"fieldarea\">[NAME] - ";
		echo $aInt->lang("mergefields", "ticketname");
		echo "<br />[FIRSTNAME] - ";
		echo $aInt->lang("fields", "firstname");
		echo "<br />[EMAIL] - ";
		echo $aInt->lang("mergefields", "ticketemail");
		echo "</td></tr>
</table>
<br>
<textarea name=\"reply\" rows=18 style=\"width:100%\">";
		echo $reply;
		echo "</textarea>
<p align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("global", "savechanges");
		echo "\" class=\"button\"></p>
</form>

";
	}
	else {
		if ($action == "editcat") {
			$result = select_query("tblticketpredefinedcats", "", array("id" => $id));
			$data = mysql_fetch_array($result);
			$parentid = $catid = $data['parentid'];
			$name = stripslashes($data['name']);
			echo "
<form method=\"post\" action=\"";
			echo $PHP_SELF;
			echo "?catid=";
			echo $parentid;
			echo "&sub=savecat&id=";
			echo $id;
			echo "\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
			echo $aInt->lang("support", "parentcat");
			echo "</td><td class=\"fieldarea\">";
			echo "<s";
			echo "elect name=\"parentid\"><option value=\"\">";
			echo $aInt->lang("support", "toplevel");
			buildCategoriesList(0, 0, $id);
			echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("support", "catname");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" value=\"";
			echo $name;
			echo "\" size=40></td></tr>
</table>
<p align=\"center\"><input type=\"submit\" value=\"";
			echo $aInt->lang("global", "savechanges");
			echo "\" class=\"button\"></p>
</form>

";
		}
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>