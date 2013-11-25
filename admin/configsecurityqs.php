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
$aInt = new WHMCS_Admin("Configure Security Questions");
$aInt->title = $aInt->lang("setup", "securityqs");
$aInt->sidebar = "config";
$aInt->icon = "securityquestions";
$aInt->helplink = "Security Questions";

if ($action == "savequestion") {
	check_token("WHMCS.admin.default");

	if ($id) {
		update_query("tbladminsecurityquestions", array("question" => encrypt($addquestion)), array("id" => $id));
		header("Location: configsecurityqs.php?update=true");
	}
	else {
		insert_query("tbladminsecurityquestions", array("question" => encrypt($addquestion)));
		header("Location: configsecurityqs.php?added=true");
	}
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	$result = select_query("tblclients", "", array("securityqid" => $id));
	$numaccounts = mysql_num_rows($result);

	if (0 < $numaccounts) {
		header("Location: configsecurityqs.php?deleteerror=true");
		exit();
	}
	else {
		delete_query("tbladminsecurityquestions", array("id" => $id));
		header("Location: configsecurityqs.php?deletesuccess=true");
		exit();
	}
}

ob_start();

if ($deletesuccess) {
	infoBox($aInt->lang("securityquestionconfig", "delsuccess"), $aInt->lang("securityquestionconfig", "delsuccessinfo"));
}


if ($deleteerror) {
	infoBox($aInt->lang("securityquestionconfig", "error"), $aInt->lang("securityquestionconfig", "errorinfo"));
}


if ($added) {
	infoBox($aInt->lang("securityquestionconfig", "addsuccess"), $aInt->lang("securityquestionconfig", "changesuccessinfo"));
}


if ($update) {
	infoBox($aInt->lang("securityquestionconfig", "changesuccess"), $aInt->lang("securityquestionconfig", "changesuccessinfo"));
}

echo $infobox;
$jscode = "function doDelete(id) {
if (confirm(\"" . $aInt->lang("securityquestionconfig", "delsuresecurityquestion", 1) . "\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=delete&id='+id+'" . generate_token("link") . "';
}}";
echo "
<h2>";
echo $aInt->lang("securityquestionconfig", "questions");
echo "</h2>

";
$aInt->sortableTableInit("nopagination");
$result = select_query("tbladminsecurityquestions", "", "");

while ($data = mysql_fetch_assoc($result)) {
	$count = select_query("tblclients", "count(securityqid) as cnt", array("securityqid" => $data['id']));
	$count_data = mysql_fetch_assoc($count);
	$cnt = (is_null($count_data['cnt']) ? "0" : $count_data['cnt']);
	$tabledata[] = array(decrypt($data['question']), $cnt, "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $data['id'] . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $data['id'] . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
}

echo $aInt->sortableTable(array($aInt->lang("securityquestionconfig", "question"), $aInt->lang("securityquestionconfig", "uses"), "", ""), $tabledata);
echo "
<h2>";

if ($action == "edit") {
	$result = select_query("tbladminsecurityquestions", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	$question = decrypt($data['question']);
	echo $aInt->lang("securityquestionconfig", "edit");
}
else {
	echo $aInt->lang("securityquestionconfig", "add");
}

echo "</h2>

<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?action=savequestion&id=";
echo $id;
echo "\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "securityquestion");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"addquestion\" value=\"";
echo $question;
echo "\" size=\"100\" /></td></tr>
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