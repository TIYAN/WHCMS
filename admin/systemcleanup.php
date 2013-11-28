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
$aInt = new WHMCS_Admin("System Cleanup Operations");
$aInt->title = $aInt->lang("system", "cleanupoperations");
$aInt->sidebar = "utilities";
$aInt->icon = "cleanup";
ob_start();

if ($action == "pruneclientactivity" && $date) {
	check_token("WHMCS.admin.default");
	$sqldate = toMySQLDate($date);
	$query = "DELETE FROM tblactivitylog WHERE userid>0 AND date<'" . db_escape_string($sqldate) . "'";
	$result = full_query($query);
	logActivity("Cleanup Operation: Pruned Client Activity Logs from before " . $date);
	infoBox($aInt->lang("system", "cleanupsuccess"), $aInt->lang("system", "deleteactivityinfo") . (" " . $date . " (") . mysql_affected_rows() . ")");
}


if ($action == "deletemessages" && $date) {
	check_token("WHMCS.admin.default");
	$sqldate = toMySQLDate($date);
	$query = "DELETE FROM tblemails WHERE date<'" . db_escape_string($sqldate) . "'";
	$result = full_query($query);
	logActivity("Cleanup Operation: Pruned Messages Sent before " . $date);
	infoBox($aInt->lang("system", "cleanupsuccess"), $aInt->lang("system", "deletemessagesinfo") . (" " . $date . " (") . mysql_affected_rows() . ")");
}


if ($action == "cleargatewaylog") {
	check_token("WHMCS.admin.default");
	$query = "TRUNCATE tblgatewaylog";
	$result = full_query($query);
	infoBox($aInt->lang("system", "cleanupsuccess"), $aInt->lang("system", "deletegatewaylog"));
	logActivity("Cleanup Operation: Gateway Log Emptied");
}


if ($action == "clearmailimportlog") {
	check_token("WHMCS.admin.default");
	$query = "TRUNCATE tblticketmaillog";
	$result = full_query($query);
	infoBox($aInt->lang("system", "cleanupsuccess"), $aInt->lang("system", "deleteticketlog"));
	logActivity("Cleanup Operation: Ticket Mail Import Log Emptied");
}


if ($action == "clearwhoislog") {
	check_token("WHMCS.admin.default");
	$query = "TRUNCATE tblwhoislog";
	$result = full_query($query);
	infoBox($aInt->lang("system", "cleanupsuccess"), $aInt->lang("system", "deletewhoislog"));
	logActivity("Cleanup Operation: WHOIS Lookup Log Emptied");
}


if ($action == "emptytemplatecache") {
	check_token("WHMCS.admin.default");
	$dh = opendir($templates_compiledir);

	while (false !== $file = readdir($dh)) {
		deleteFile($templates_compiledir, $file);
	}

	closedir($dh);
	infoBox($aInt->lang("system", "cleanupsuccess"), $aInt->lang("system", "deletecacheinfo"));
	logActivity("Cleanup Operation: Template Cache Emptied");
}


if ($action == "deleteattachments" && $date) {
	check_token("WHMCS.admin.default");
	$sqldate = toMySQLDate($date);
	$result = select_query("tbltickets", "", "date<='" . db_escape_string($sqldate) . "' AND attachment!=''");

	while ($data = mysql_fetch_array($result)) {
		$attachment = $data['attachment'];
		$attachment = explode("|", $attachment);
		foreach ($attachment as $file) {
			deleteFile($attachments_dir, $file);
		}
	}

	$result = select_query("tblticketreplies", "", "date<='" . db_escape_string($sqldate) . "' AND attachment!=''");

	while ($data = mysql_fetch_array($result)) {
		$attachment = $data['attachment'];
		$attachment = explode("|", $attachment);
		foreach ($attachment as $file) {
			deleteFile($attachments_dir, $file);
		}
	}

	logActivity("Cleanup Operation: Pruned Attachments Uploaded before " . $date);
	infoBox($aInt->lang("system", "cleanupsuccess"), $aInt->lang("system", "deleteattachinfo") . (" " . $date));
}

$attachmentssize = $attachmentscount = 0;
$dh = opendir($attachments_dir);

while (false !== $file = readdir($dh)) {
	$fullpath = $attachments_dir . $file;

	if (is_file($fullpath) && $file != "index.php") {
		$attachmentssize += filesize($fullpath);
		++$attachmentscount;
	}
}

closedir($dh);
$attachmentssize /= 1024 * 1024;
$attachmentssize = round($attachmentssize, 2);
echo $infobox;
echo "
<p>";
echo $aInt->lang("system", "cleanupdescription");
echo "</p>

<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"49%\">

<div class=\"contentbox\">
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "\"><input type=\"hidden\" name=\"action\" value=\"cleargatewaylog\" />
<b>";
echo $aInt->lang("system", "emptygwlog");
echo "</b> <input type=\"submit\" value=\" ";
echo $aInt->lang("global", "go");
echo " &raquo; \" class=\"button\" />
</form>
</div>

<br>

<div class=\"contentbox\">
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "\"><input type=\"hidden\" name=\"action\" value=\"clearmailimportlog\" />
<b>";
echo $aInt->lang("system", "emptytmlog");
echo "</b> <input type=\"submit\" value=\" ";
echo $aInt->lang("global", "go");
echo " &raquo; \" class=\"button\" />
</form>
</div>

</td><td width=\"2%\"></td><td width=\"49%\">

<div class=\"contentbox\">
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "\"><input type=\"hidden\" name=\"action\" value=\"clearwhoislog\" />
<b>";
echo $aInt->lang("system", "emptywllog");
echo "</b> <input type=\"submit\" value=\" ";
echo $aInt->lang("global", "go");
echo " &raquo; \" class=\"button\" />
</form>
</div>

<br>

<div class=\"contentbox\">
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "\"><input type=\"hidden\" name=\"action\" value=\"emptytemplatecache\" />
<b>";
echo $aInt->lang("system", "emptytc");
echo "</b> <input type=\"submit\" value=\" ";
echo $aInt->lang("global", "go");
echo " &raquo; \" class=\"button\" />
</form>
</div>

</td></tr></table>

<br>

<div class=\"contentbox\">
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?action=pruneclientactivity\">
<b>";
echo $aInt->lang("system", "prunecal");
echo "</b><br>
";
$result = select_query("tblactivitylog", "COUNT(*)", "userid>0");
$data = mysql_fetch_array($result);
$num_rows = $data[0];
echo $aInt->lang("system", "totallogentries") . ": <b>" . $num_rows . "</b>";
echo "<br>
";
echo $aInt->lang("system", "deleteentriesbefore");
echo ": <input type=\"text\" name=\"date\" class=\"datepick\"> <input type=\"submit\" value=\"";
echo $aInt->lang("global", "delete");
echo "\" class=\"button\"></form>
</div>

<br>

<div class=\"contentbox\">
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?action=deletemessages\">
<b>";
echo $aInt->lang("system", "prunese");
echo "</b><br>
";
$result = select_query("tblemails", "COUNT(*)", "");
$data = mysql_fetch_array($result);
$num_rows = $data[0];
echo $aInt->lang("system", "totalsavedemails") . ": <b>" . $num_rows . "</b>";
echo "<br>
";
echo $aInt->lang("system", "deletemailsbefore");
echo ": <input type=\"text\" name=\"date\" class=\"datepick\"> <input type=\"submit\" value=\"";
echo $aInt->lang("global", "delete");
echo "\" class=\"button\"></form>
</div>

<br>

<div class=\"contentbox\">
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?action=deleteattachments\">
<b>";
echo $aInt->lang("system", "pruneoa");
echo "</b><br>
";
echo $aInt->lang("system", "nosavedattachments") . ": <b>" . $attachmentscount . "</b><br>" . $aInt->lang("system", "filesizesavedatt") . ": <b>" . $attachmentssize . " " . $aInt->lang("fields", "mb") . "</b>";
echo "<br>
";
echo $aInt->lang("system", "deleteattachbefore");
echo ": <input type=\"text\" name=\"date\" class=\"datepick\"> <input type=\"submit\" value=\"";
echo $aInt->lang("global", "delete");
echo "\" class=\"button\"></form>
</div>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>