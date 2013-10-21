<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Configure Database Backups");
$aInt->title = $aInt->lang("backups", "title");
$aInt->sidebar = "config";
$aInt->icon = "dbbackups";
$aInt->helplink = "Backups";

if ($sub == "save") {
	check_token("WHMCS.admin.default");
	$save_arr = array("DailyEmailBackup" => $dailyemailbackup, "FTPBackupHostname" => $ftpbackuphostname, "FTPBackupPort" => (int)$ftpbackupport, "FTPBackupUsername" => $ftpbackupusername, "FTPBackupPassword" => encrypt($ftpbackuppassword), "FTPBackupDestination" => $ftpbackupdestination, "FTPPassiveMode" => $ftppassivemode);
	foreach ($save_arr as $k => $v) {

		if (!isset($CONFIG[$k])) {
			insert_query("tblconfiguration", array("setting" => $k, "value" => trim($v)));
			continue;
		}

		update_query("tblconfiguration", array("value" => trim($v)), array("setting" => $k));
	}

	header("Location: " . $_SERVER['PHP_SELF'] . "?success=true");
	exit();
}


if (!isset($CONFIG['FTPBackupPort'])) {
	insert_query("tblconfiguration", array("setting" => "FTPBackupPort", "value" => "21"));
	$CONFIG['FTPBackupPort'] = "21";
}

ob_start();

if ($success) {
	infoBox($aInt->lang("backups", "changesuccess"), $aInt->lang("backups", "changesuccessinfo"));
	echo $infobox;
}

echo "<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?sub=save\">

<p>";
echo $aInt->lang("backups", "description");
echo "</p>

<p><b>";
echo $aInt->lang("backups", "dailyemail");
echo "</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "email");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"dailyemailbackup\" value=\"";
echo $CONFIG['DailyEmailBackup'];
echo "\" size=\"40\"> ";
echo $aInt->lang("backups", "emailinfo");
echo " (";
echo $aInt->lang("backups", "blanktodisable");
echo ")</td></tr>
</table>

<p><b>";
echo $aInt->lang("backups", "dailyftp");
echo "</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\"  class=\"fieldlabel\">";
echo $aInt->lang("backups", "ftphost");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ftpbackuphostname\" value=\"";
echo $CONFIG['FTPBackupHostname'];
echo "\" size=\"30\"> ";
echo $aInt->lang("backups", "hostnameinfo");
echo " (";
echo $aInt->lang("backups", "blanktodisable");
echo ")</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("backups", "ftpport");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ftpbackupport\" value=\"";
echo $CONFIG['FTPBackupPort'];
echo "\" size=\"6\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("backups", "ftpuser");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ftpbackupusername\" value=\"";
echo $CONFIG['FTPBackupUsername'];
echo "\" size=\"30\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("backups", "ftppass");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ftpbackuppassword\" value=\"";
echo decrypt($CONFIG['FTPBackupPassword']);
echo "\" size=\"30\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("backups", "ftppath");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ftpbackupdestination\" value=\"";
echo $CONFIG['FTPBackupDestination'];
echo "\" size=\"30\"> ";
echo $aInt->lang("backups", "relativepath");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("backups", "ftppassivemode");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"ftppassivemode\"";

if ($CONFIG['FTPPassiveMode']) {
	echo " checked";
}

echo " /> ";
echo $aInt->lang("global", "ticktoenable");
echo "</label></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("global", "savechanges");
echo "\" class=\"button\"></p>

</form>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>