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
$aInt = new WHMCS_Admin("Manage Announcements");
$aInt->title = $aInt->lang("support", "announcements");
$aInt->sidebar = "support";
$aInt->icon = "announcements";

if ($sub == "delete") {
	check_token("WHMCS.admin.default");
	delete_query("tblannouncements", array("id" => $id));
	delete_query("tblannouncements", array("parentid" => $id));
	logActivity("Deleted Announcement (ID: " . $id . ")");
	redir();
}


if ($sub == "save") {
	check_token("WHMCS.admin.default");
	$date = toMySQLDate($date);

	if ($id) {
		update_query("tblannouncements", array("date" => $date, "title" => html_entity_decode($title), "announcement" => html_entity_decode($announcement), "published" => $published), array("id" => $id));
		logActivity("Modified Announcement (ID: " . $id . ")");
		run_hook("AnnouncementEdit", array("announcementid" => $id, "date" => $date, "title" => $title, "announcement" => $announcement, "published" => $published));
	}
	else {
		$id = insert_query("tblannouncements", array("date" => $date, "title" => html_entity_decode($title), "announcement" => html_entity_decode($announcement), "published" => $published));
		logActivity("Added New Announcement (" . $title . ")");
		run_hook("AnnouncementAdd", array("announcementid" => $id, "date" => $date, "title" => $title, "announcement" => $announcement, "published" => $published));
	}

	foreach ($multilang_title as $language => $title) {
		delete_query("tblannouncements", array("parentid" => $id, "language" => $language));

		if ($title) {
			insert_query("tblannouncements", array("parentid" => $id, "title" => html_entity_decode($title), "announcement" => html_entity_decode($multilang_announcement[$language]), "language" => $language));
			continue;
		}
	}


	if ($toggleeditor) {
		if ($editorstate) {
			redir("action=manage&id=" . $id);
		}
		else {
			redir("action=manage&id=" . $id . "&noeditor=1");
		}
	}

	redir("success=1");
}

ob_start();

if ($action == "") {
	$aInt->deleteJSConfirm("doDelete", "support", "announcesuredel", "?sub=delete&id=");

	if ($success) {
		infoBox($aInt->lang("global", "success"), $aInt->lang("global", "changesuccess"));
	}

	echo $infobox;
	echo "
<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "?action=manage\">
<p align=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("support", "announceadd");
	echo "\" class=\"button\" /></p>
</form>

";
	$query = "SELECT * FROM tblannouncements WHERE language='' ORDER BY date DESC";
	$result = full_query($query);
	$numrows = mysql_num_rows($result);
	$aInt->sortableTableInit("date", "DESC");
	$query .= " LIMIT " . (int)$page * $limit . "," . (int)$limit;
	$result = full_query($query);

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$date = $data['date'];
		$title = $data['title'];
		$published = $data['published'];
		$date = fromMySQLDate($date, true);

		if ($published == "on") {
			$published = "Yes";
		}
		else {
			$published = "No";
		}

		$tabledata[] = array($date, $title, $published, "<a href=\"" . $PHP_SELF . "?action=manage&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>");
	}

	echo $aInt->sortableTable(array($aInt->lang("fields", "date"), $aInt->lang("fields", "title"), $aInt->lang("support", "announcepublished"), "", ""), $tabledata, $tableformurl, $tableformbuttons);
}
else {
	if ($action == "manage") {
		$multilang_title = array();
		$multilang_announcement = array();

		if ($id) {
			$action = "Edit";
			$result = select_query("tblannouncements", "", array("id" => $id, "language" => ""));
			$data = mysql_fetch_array($result);
			$id = $data['id'];
			$date = $data['date'];
			$title = $data['title'];
			$announcement = $data['announcement'];
			$published = $data['published'];
			$date = fromMySQLDate($date, true);
			$result = select_query("tblannouncements", "", array("parentid" => $id));

			while ($data = mysql_fetch_array($result)) {
				$language = $data['language'];
				$multilang_title[$language] = $data['title'];
				$multilang_announcement[$language] = $data['announcement'];
			}
		}
		else {
			$action = "Add";
			$date = fromMySQLDate(date("Y-m-d H:i:s"), true);
		}

		$jscode = "function showtranslation(language) {
    $(\"#translation_\"+language).slideToggle();
}";
		echo "
<h2>";
		echo $action;
		echo " ";
		echo $aInt->lang("support", "announcement");
		echo "</h2>
<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?sub=save&id=";
		echo $id;
		echo "\">
<input type=\"hidden\" name=\"editorstate\" value=\"";
		echo $noeditor;
		echo "\" />
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("fields", "date");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"date\" value=\"";
		echo $date;
		echo "\" size=\"25\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "title");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"title\" value=\"";
		echo $title;
		echo "\" size=\"70\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "announcement");
		echo "</td><td class=\"fieldarea\"><textarea name=\"announcement\" rows=20 style=\"width:100%\" class=\"tinymce\">";
		echo $announcement;
		echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "announcepublished");
		echo "?</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"published\"";

		if ($published == "on") {
			echo " checked";
		}

		echo "></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" name=\"toggleeditor\" value=\"";
		echo $aInt->lang("emailtpls", "rteditor");
		echo "\" /> <input type=\"submit\" value=\"";
		echo $aInt->lang("global", "savechanges");
		echo "\" class=\"button\" ></p>

<h2>";
		echo $aInt->lang("support", "announcemultiling");
		echo "</h2>

";
		foreach (getValidLanguages() as $language) {

			if ($language != $CONFIG['Language']) {
				echo "<p><b><a href=\"#\" onClick=\"showtranslation('" . $language . "');return false;\">" . ucfirst($language) . "</a></b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\" id=\"translation_" . $language . "\"";

				if (!$multilang_title[$language]) {
					echo " style=\"display:none;\"";
				}

				echo ">
<tr><td width=\"15%\" class=\"fieldlabel\">" . $aInt->lang("fields", "title") . "</td><td class=\"fieldarea\"><input type=\"text\" name=\"multilang_title[" . $language . "]\" value=\"" . $multilang_title[$language] . "\" size=\"70\"></td></tr>
<tr><td class=\"fieldlabel\">" . $aInt->lang("support", "announcement") . "</td><td class=\"fieldarea\"><textarea name=\"multilang_announcement[" . $language . "]\" rows=20 style=\"width:100%\" class=\"tinymce\">" . $multilang_announcement[$language] . "</textarea></td></tr>
</table>";
				continue;
			}
		}

		echo "
<p align=\"center\"><input type=\"submit\" name=\"toggleeditor\" value=\"";
		echo $aInt->lang("emailtpls", "rteditor");
		echo "\" /> <input type=\"submit\" value=\"";
		echo $aInt->lang("global", "savechanges");
		echo "\" class=\"button\" /></p>

</form>

";

		if (!$noeditor) {
			$aInt->richTextEditor();
		}
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->jquerycode = $jquerycode;
$aInt->display();
?>