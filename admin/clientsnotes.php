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

if ($action == "edit") {
	$reqperm = "Add/Edit Client Notes";
}
else {
	$reqperm = "View Clients Notes";
}

$aInt = new WHMCS_Admin($reqperm);
$aInt->inClientsProfile = true;
$aInt->valUserID($userid);
$id = (int)$id;

if ($sub == "add") {
	check_token("WHMCS.admin.default");
	checkPermission("Add/Edit Client Notes");
	insert_query("tblnotes", array("userid" => $userid, "adminid" => $_SESSION['adminid'], "created" => "now()", "modified" => "now()", "note" => $note, "sticky" => $sticky));
	logActivity("Added Note - User ID: " . $userid);
	header("Location: " . $_SERVER['PHP_SELF'] . ("?userid=" . $userid));
	exit();
}
else {
	if ($sub == "save") {
		check_token("WHMCS.admin.default");
		checkPermission("Add/Edit Client Notes");
		update_query("tblnotes", array("note" => $note, "sticky" => $sticky, "modified" => "now()"), array("id" => $id));
		logActivity("Updated Note - User ID: " . $userid . " - ID: " . $id);
		header("Location: " . $_SERVER['PHP_SELF'] . ("?userid=" . $userid));
		exit();
	}
	else {
		if ($sub == "delete") {
			check_token("WHMCS.admin.default");
			checkPermission("Delete Client Notes");
			delete_query("tblnotes", array("id" => $id));
			logActivity("Deleted Note - User ID: " . $userid . " - ID: " . $id);
			header("Location: " . $_SERVER['PHP_SELF'] . ("?userid=" . $userid));
			exit();
		}
	}
}

$aInt->deleteJSConfirm("doDelete", "clients", "deletenote", "clientsnotes.php?userid=" . $userid . "&sub=delete&id=");
ob_start();
$aInt->sortableTableInit("created", "ASC");
$result = select_query("tblnotes", "COUNT(*)", array("userid" => $userid), "created", "ASC", "", "tbladmins ON tbladmins.id=tblnotes.adminid");
$data = mysql_fetch_array($result);
$numrows = $data[0];
$result = select_query("tblnotes", "tblnotes.*,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE tbladmins.id=tblnotes.adminid) AS adminuser", array("userid" => $userid), "modified", "DESC");

while ($data = mysql_fetch_array($result)) {
	$noteid = $data['id'];
	$created = $data['created'];
	$modified = $data['modified'];
	$note = $data['note'];
	$admin = $data['adminuser'];

	if (!$admin) {
		$admin = "Admin Deleted";
	}

	$note = nl2br($note);
	$note = autoHyperLink($note);
	$created = fromMySQLDate($created, "time");
	$modified = fromMySQLDate($modified, "time");
	$importantnote = ($data['sticky'] ? "high" : "low");
	$tabledata[] = array($created, $note, $admin, $modified, "<img src=\"images/" . $importantnote . "priority.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("clientsummary", "importantnote") . "\">", "<a href=\"" . $PHP_SELF . "?userid=" . $userid . "&action=edit&id=" . $noteid . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $noteid . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
}

echo $aInt->sortableTable(array($aInt->lang("fields", "created"), $aInt->lang("fields", "note"), $aInt->lang("fields", "admin"), $aInt->lang("fields", "lastmodified"), "", "", ""), $tabledata);
echo "
<br>

";

if ($action == "edit") {
	$notesdata = get_query_vals("tblnotes", "note, sticky", array("userid" => $userid, "id" => $id));
	$note = $notesdata['note'];
	$importantnote = ($notesdata['sticky'] ? " checked" : "");
	echo "<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "?userid=";
	echo $userid;
	echo "&sub=save&id=";
	echo $id;
	echo "\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldarea\"><textarea name=\"note\" rows=\"6\" style=\"width:99%;\">";
	echo $note;
	echo "</textarea></td><td align=\"center\" width=\"60\"><input type=\"submit\" value=\"";
	echo $aInt->lang("global", "savechanges");
	echo "\" class=\"button\"><br /><label><input type=\"checkbox\" class=\"checkbox\" name=\"sticky\" value=\"1\"";
	echo $importantnote;
	echo " /> ";
	echo $aInt->lang("clientsummary", "stickynotescheck");
	echo "</label></td></tr>
</table>
</form>
";
}
else {
	echo "<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "?userid=";
	echo $userid;
	echo "&sub=add\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldarea\"><textarea name=\"note\" rows=\"6\" style=\"width:99%;\"></textarea></td><td align=\"center\" width=\"60\"><input type=\"submit\" value=\"";
	echo $aInt->lang("global", "addnew");
	echo "\" class=\"button\" /><br /><label><input type=\"checkbox\" class=\"checkbox\" name=\"sticky\" value=\"1\" /> ";
	echo $aInt->lang("clientsummary", "stickynotescheck");
	echo "</label></td></tr>
</table>
</form>
";
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>