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
$aInt = new WHMCS_Admin("Configure Two-Factor Authentication");
$aInt->title = $aInt->lang("twofa", "title");
$aInt->sidebar = "config";
$aInt->icon = "security";
$aInt->helplink = "Security Modules";
$aInt->requiredFiles(array("modulefunctions"));
$frm = new WHMCS_Form();

if ($frm->issubmitted()) {
	$whmcs->set_config("2fasettings", serialize(array("forceclient" => $whmcs->get_req_var("forceclient"), "forceadmin" => $whmcs->get_req_var("forceadmin"), "modules" => $whmcs->get_req_var("mod"))));
	redir("success=1");
}

ob_start();

if ($purchased) {
	$licensing->forceRemoteCheck();
	redir();
}

$twofasettings = $whmcs->get_config("2fasettings");
$twofasettings = unserialize($twofasettings);
echo $frm->form();
echo "<table width=\"100%\"><tr><td width=\"45%\" valign=\"top\">

<div style=\"padding:20px;background-color:#FAF5E4;-moz-border-radius: 10px;-webkit-border-radius: 10px;-o-border-radius: 10px;border-radius: 10px;\">";
echo "

<strong>What is Two-Factor Authentication?</strong><br /><br />

Two-factor authentication adds an additional layer of security by adding a second step to your login. It takes something you know (ie. your password) and adds a second factor, typically something you have (such as your phone.) Since both are required to log in, even if an attacker has your password they can't access your account.

<div style=\"margin:20px auto;padding:10px;width:370px;background-color:#fff;-moz-border-radius: 10px;-webkit-border-radius: 10px;-o-border-radius: 10px;border-radius: 10px;\"><img src=\"images/twofahow.png\" width=\"350\" height=\"233\" /></div>

<strong>Why do you need it?</strong><br /><br />

Passwords are increasingly easy to compromise. They can often be guessed or leaked, they usually don't change very often, and despite advice otherwise, many of us have favorite passwords that we use for more than one thing. So Two-factor authentication gives you additional security because your password alone no longer allows access to your account.<br /><br />

<strong>How it works?</strong><br /><br />

There are many different options available, and in WHMCS we support more than one so <i>you</i> have the choice.  But one of the most common and simplest to use is time based one-time passwords.  With these, in addition to your regular username & password, you also have to enter a 6 digit code that changes every 30 seconds.  Only your token device (typically a mobile smartphone) will know your secret key, and be able to generate valid one time passwords for your account.  And so your account is far safer.<br /><br />

<strong>Force Settings</strong><br /><br />

";
echo $frm->checkbox("forceclient", "Force Clients to enable Two Factor Authentication on Next Login", $twofasettings['forceclient']) . "<br />";
echo $frm->checkbox("forceadmin", "Force Administrator Users to enable Two Factor Authentication on Next Login", $twofasettings['forceadmin']) . "<br /><br />";
echo $frm->submit($aInt->lang("global", "savechanges"));
echo "</td><td width=\"55%\" valign=\"top\">";
$mod = new WHMCS_Module();
$moduleslist = $mod->getList("security");

if (!$moduleslist) {
	$aInt->gracefulExit("Security Module Folder Not Found. Please try reuploading all WHMCS related files.");
}

$i = 0;
foreach ($moduleslist as $module) {
	$mod->load($module);
	$configarray = $mod->call("config");
	$moduleconfigdata = $twofasettings['modules'][$module];
	echo "<div style=\"width:90%;margin:" . ($i ? "10px" : "0") . " auto;padding:10px 20px;border:1px solid #ccc;background-color:#fff;-moz-border-radius: 10px;-webkit-border-radius: 10px;-o-border-radius: 10px;border-radius: 10px;\">";

	if ($moduleconfigdata['clientenabled'] || $moduleconfigdata['adminenabled']) {
		echo "<p style=\"float:right;\"><input type=\"button\" value=\"Deactivate\" class=\"btn-danger\" onclick=\"deactivate('" . $module . "')\" /></p>";
		$showstyle = "";
	}
	else {
		if (array_key_exists("Licensed", $configarray)) {
			if ($configarray['Licensed']['Value']) {
				echo "<p style=\"float:right;\"><input type=\"button\" value=\"Activate\" class=\"btn-success\" id=\"activatebtn" . $module . "\" onclick=\"activate('" . $module . "')\" /></p>";
			}
			else {
				echo "<p style=\"float:right;\"><input type=\"button\" value=\"Subscribe to Activate\" class=\"btn-inverse\" onclick=\"window.open('" . $configarray['SubscribeLink']['Value'] . "');dialogOpen();\" /></p>";
			}
		}
		else {
			echo "<p style=\"float:right;\"><input type=\"button\" value=\"Activate\" class=\"btn-success\" id=\"activatebtn" . $module . "\" onclick=\"activate('" . $module . "')\" /></p>";
		}

		$showstyle = "display:none;";
	}


	if (file_exists(ROOTDIR . "/modules/security/" . $module . "/logo.gif")) {
		echo "<img src=\"../modules/security/" . $module . "/logo.gif\" />";
	}
	else {
		if (file_exists(ROOTDIR . "/modules/security/" . $module . "/logo.jpg")) {
			echo "<img src=\"../modules/security/" . $module . "/logo.jpg\" />";
		}
		else {
			if (file_exists(ROOTDIR . "/modules/security/" . $module . "/logo.png")) {
				echo "<img src=\"../modules/security/" . $module . "/logo.png\" />";
			}
			else {
				echo "<h2>" . (isset($configarray['FriendlyName']['Value']) ? $configarray['FriendlyName']['Value'] : ucfirst($module)) . "</h2>";
			}
		}
	}


	if ($configarray['Description']['Value']) {
		echo "<p>" . $configarray['Description']['Value'] . "</p>";
	}

	echo "<div id=\"conf" . $module . "\" style=\"" . $showstyle . "\">";
	$tbl = new WHMCS_Table();
	$tbl->add("Enable for Clients", $frm->checkbox("mod[" . $module . "][clientenabled]", "Tick to Enable", $moduleconfigdata['clientenabled'], "1", "enable" . $module), 1);
	$tbl->add("Enable for Staff", $frm->checkbox("mod[" . $module . "][adminenabled]", "Tick to Enable", $moduleconfigdata['adminenabled'], "1", "enable" . $module), 1);
	foreach ($configarray as $key => $values) {

		if ($values['Type'] != "System") {
			if (!isset($values['FriendlyName'])) {
				$values['FriendlyName'] = $key;
			}

			$values['Name'] = "mod[" . $module . "][" . $key . "]";
			$values['Value'] = htmlspecialchars($moduleconfigdata[$key]);
			$tbl->add($values['FriendlyName'], moduleConfigFieldOutput($values), 1);
			continue;
		}
	}

	echo $tbl->output();
	echo "<p align=\"center\">" . $frm->submit($aInt->lang("global", "savechanges")) . "</p>";
	echo "</div>";
	echo "</div>";
	++$i;
}

echo "</td></tr></table>";
echo $frm->close();
$aInt->dialog("", "<div class=\"content\"><div style=\"padding:15px;\"><h2>Two-Factor Authentication Subscription</h2><br /><br /><div align=\"center\">You will now be redirected to purchase the selected<br />Two-Factor Authentcation solution in a new browser window.<br /><br />Once completed, please click on the button below to continue.<br /><br /><br /><form method=\"post\" action=\"configtwofa.php\"><input type=\"hidden\" name=\"purchased\" value=\"1\" /><input type=\"submit\" value=\"Continue &raquo;\" class=\"btn\" onclick=\"dialogClose()\" /></form></div></div></div>");
$content = ob_get_contents();
ob_end_clean();
$jscode = "
function activate(mod) {
    $(\"#activatebtn\"+mod).hide();
    $(\"#conf\"+mod).fadeIn();
}
function deactivate(mod) {
    $(\".enable\"+mod).attr(\"checked\",false);
    $(\"#conf\"+mod).fadeOut();
    $(\"#" . $frm->getname() . "\").submit();
}
";
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>