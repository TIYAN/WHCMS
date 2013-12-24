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
$aInt = new WHMCS_Admin("My Account", false);
$aInt->title = $aInt->lang("global", "myaccount");
$aInt->sidebar = "config";
$aInt->icon = "home";
$aInt->requiredFiles(array("ticketfunctions"));
$action = $whmcs->get_req_var("action");
$errormessage = "";
$twofa = new WHMCS_2FA();
$twofa->setAdminID($_SESSION['adminid']);

if ($whmcs->get_req_var("2fasetup")) {
	if (!$twofa->isActiveAdmins()) {
		exit("Access denied");
	}

	ob_start();

	if ($twofa->isEnabled()) {
		echo "<div class=\"content\"><div style=\"padding:15px;\">";
		$disabled = $incorrect = false;

		if ($password = $whmcs->get_req_var("pwverify")) {
			$auth = new WHMCS_Auth();
			$auth->getInfobyID($_SESSION['adminid']);

			if ($auth->comparePassword($password)) {
				$twofa->disableUser();
				$disabled = true;
			}
			else {
				$incorrect = true;
			}
		}

		echo "<h2>" . $aInt->lang("twofa", "disable") . "</h2>";

		if (!$disabled) {
			echo "<p>" . $aInt->lang("twofa", "disableintro") . "</p>";

			if ($incorrect) {
				echo "<div class=\"errorbox\"><strong>Password Incorrect</strong><br />Please try again...</div>";
			}

			echo "<form onsubmit=\"dialogSubmit();return false\"><input type=\"hidden\" name=\"2fasetup\" value=\"1\" /><p align=\"center\">" . $aInt->lang("fields", "password") . ": <input type=\"password\" name=\"pwverify\" value=\"\" size=\"20\" /><p><p align=\"center\"><input type=\"button\" value=\"" . $aInt->lang("global", "disable") . "\" class=\"btn\" onclick=\"dialogSubmit()\" /></p></form>";
		}
		else {
			echo "<p>" . $aInt->lang("twofa", "disabledconfirmation") . "</p><p align=\"center\"><input type=\"button\" value=\"" . $aInt->lang("global", "close") . "\" onclick=\"window.location='myaccount.php'\" /></p>";
		}

		echo "<script type=\"text/javascript\">
$(\"#admindialogcont input:password:visible:first\").focus();
</script>
</div></div>";
	}
	else {
		$modules = $twofa->getAvailableModules();

		if (isset($module) && in_array($module, $modules)) {
			$output = $twofa->moduleCall("activate", $module);

			if (is_array($output) && isset($output['completed'])) {
				$msg = (isset($output['msg']) ? $output['msg'] : "");
				$settings = (isset($output['settings']) ? $output['settings'] : array());
				$backupcode = $twofa->activateUser($module, $settings);
				$output = "";

				if ($backupcode) {
					$output = "<div align=\"center\"><h2>" . $aInt->lang("twofa", "activationcomplete") . "</h2>";

					if ($msg) {
						$output .= "<div style=\"margin:20px;padding:10px;background-color:#f7f7f7;border:1px dashed #cccccc;text-align:center;\">" . $msg . "</div>";
					}

					$output .= "<h2>" . $aInt->lang("twofa", "backupcodeis") . ":</h2><div style=\"margin:20px auto;padding:10px;width:280px;background-color:#F2D4CE;border:1px dashed #AE432E;text-align:center;font-size:20px;\">" . $backupcode . "</div><p>" . $aInt->lang("twofa", "backupcodeexpl") . "</p>";
					$output .= "<p><input type=\"button\" value=\"" . $aInt->lang("global", "close") . "\" onclick=\"window.location='myaccount.php'\" /></p></div>";
				}
				else {
					$output = $aInt->lang("twofa", "activationerror");
				}
			}


			if (!$output) {
				echo "<div class=\"content\"><div style=\"padding:15px;\">";
				echo $aInt->lang("twofa", "generalerror");
				echo "</div></div>";
			}
			else {
				echo "<div class=\"content\"><div style=\"padding:15px;\">";
				echo $output;
				echo "</div></div>";
			}
		}
		else {
			echo "<div class=\"content\"><div style=\"padding:15px;\">";
			echo "<h2>" . $aInt->lang("twofa", "enable") . "</h2>";

			if ($twofa->isForced()) {
				echo "<div class=\"infobox\">" . $aInt->lang("twofa", "enforced") . "</div>";
			}

			echo "<p>" . $aInt->lang("twofa", "activateintro") . "</p>
<form><input type=\"hidden\" name=\"2fasetup\" value=\"1\" />";

			if (1 < count($modules)) {
				echo "<p>" . $aInt->lang("twofa", "choose") . "</p>";
				$mod = new WHMCS_Module("security");
				$first = true;
				foreach ($modules as $module) {
					$mod->load($module);
					$configarray = $mod->call("config");
					echo " &nbsp;&nbsp;&nbsp;&nbsp; <label><input type=\"radio\" name=\"module\" value=\"" . $module . "\"" . ($first ? " checked" : "") . " /> " . (isset($configarray['FriendlyName']['Value']) ? $configarray['FriendlyName']['Value'] : ucfirst($module)) . "</label><br />";
					$first = false;
				}
			}
			else {
				echo "<input type=\"hidden\" name=\"module\" value=\"" . $modules[0] . "\" />";
			}

			echo "<p align=\"center\"><br /><input type=\"button\" value=\"" . $aInt->lang("twofa", "getstarted") . " &raquo;\" onclick=\"dialogSubmit()\" class=\"btn btn-primary\" /></form>";
			echo "</div></div>";
		}
	}

	echo "<script type=\"text/javascript\">
$(\"#admindialogcont input:text:visible:first\").focus();
</script>";
	$content = ob_get_contents();
	ob_end_clean();
	echo $content;
	exit();
}


if ($action == "save") {
	check_token("WHMCS.admin.default");

	if ($password != $password2) {
		$errormessage = $aInt->lang("administrators", "pwmatcherror");
		$action = "edit";
	}
	else {
		update_query("tbladmins", array("firstname" => $firstname, "lastname" => $lastname, "email" => $email, "signature" => $signature, "notes" => $notes, "template" => $template, "language" => $language, "ticketnotifications" => implode(",", $ticketnotify)), array("id" => $_SESSION['adminid']));
		unset($_SESSION['adminlang']);
		logActivity("Administrator Account Modified (" . $firstname . " " . $lastname . ")");

		if ($password) {
			update_query("tbladmins", array("password" => md5(trim($password))), array("id" => $_SESSION['adminid']));
		}

		redir("success=true");
		exit();
	}
}

releaseSession();
$result = select_query("tbladmins", "tbladmins.*,tbladminroles.name", array("tbladmins.id" => $_SESSION['adminid']), "", "", "", "tbladminroles ON tbladminroles.id=tbladmins.roleid");
$data = mysql_fetch_array($result);

if (!$errormessage) {
	$firstname = $data['firstname'];
	$lastname = $data['lastname'];
	$email = $data['email'];
	$signature = $data['signature'];
	$notes = $data['notes'];
	$template = $data['template'];
	$language = $data['language'];
	$ticketnotifications = $data['ticketnotifications'];
	$ticketnotify = explode(",", $ticketnotifications);
}

$username = $data['username'];
$adminrole = $data['name'];
$language = $whmcs->validateLanguage($language, true);
ob_start();
$aInt->dialog("2fasetup");

if ($whmcs->get_req_var("success")) {
	infoBox($aInt->lang("administrators", "changesuccess"), $aInt->lang("administrators", "changesuccessinfo2"));
}


if ($errormessage) {
	infoBox($aInt->lang("global", "validationerror"), $errormessage);
}

echo $infobox;
echo "
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?action=save\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "username");
echo "</td><td class=\"fieldarea\"><b>";
echo $username;
echo "</b></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("administrators", "role");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "trong>";
echo $adminrole;
echo "</strong></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "firstname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"firstname\" size=\"30\" value=\"";
echo $firstname;
echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "lastname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"lastname\" size=\"30\" value=\"";
echo $lastname;
echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "email");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"email\" size=\"50\" value=\"";
echo $email;
echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("administrators", "ticketnotifications");
echo "</td><td class=\"fieldarea\">";
$nodepartments = true;
$supportdepts = getAdminDepartmentAssignments();
foreach ($supportdepts as $deptid) {
	$deptname = get_query_val("tblticketdepartments", "name", array("id" => $deptid));

	if ($deptname) {
		echo "<label><input type=\"checkbox\" name=\"ticketnotify[]\" value=\"" . $deptid . "\"" . (in_array($deptid, $ticketnotify) ? " checked" : "") . " /> " . $deptname . "</label><br />";
		$nodepartments = false;
		continue;
	}
}


if ($nodepartments) {
	echo $aInt->lang("administrators", "nosupportdeptsassigned");
}

echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("administrators", "supportsig");
echo "</td><td class=\"fieldarea\"><textarea name=\"signature\" cols=80 rows=4>";
echo $signature;
echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("global", "mynotes");
echo "</td><td class=\"fieldarea\"><textarea name=\"notes\" cols=80 rows=4>";
echo $notes;
echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "template");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"template\">";
$templates = array();
$dh = opendir("templates/");

while (false !== $folder = readdir($dh)) {
	if (is_file("templates/" . $folder . "/header.tpl")) {
		$templates[] = $folder;
	}
}

sort($templates);
foreach ($templates as $temp) {
	echo "<option value=\"" . $temp . "\"";

	if ($temp == $template) {
		echo " selected";
	}

	echo ">" . ucfirst($temp) . "</option>";
}

closedir($dh);
echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("global", "language");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"language\">";
foreach ($whmcs->getValidLanguages(true) as $lang) {
	echo "<option value=\"" . $lang . "\"";

	if ($lang == $language) {
		echo " selected=\"selected\"";
	}

	echo ">" . ucfirst($lang) . "</option>";
}

echo "</select></td></tr>
";

if ($twofa->isActiveAdmins()) {
	echo "<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("twofa", "title");
	echo "</td><td class=\"fieldarea\">";
	echo $twofa->isEnabled() ? "<input type=\"button\" value=\"" . $aInt->lang("twofa", "disableclickhere") . "\" onclick=\"dialogOpen()\" class=\"btn btn-danger\" />" : "<input type=\"button\" value=\"" . $aInt->lang("twofa", "enableclickhere") . "\" onclick=\"dialogOpen()\" class=\"btn btn-success\" />";
	echo "</td></td></tr>
";
}

echo "</table>

<p>";
echo $aInt->lang("administrators", "entertochange");
echo "</p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "password");
echo "</td><td class=\"fieldarea\"><input type=\"password\" name=\"password\" size=\"25\"></td></tr>
<tr><td class=\"fieldlabel\" >";
echo $aInt->lang("fields", "confpassword");
echo "</td><td class=\"fieldarea\"><input type=\"password\" name=\"password2\" size=\"25\"></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("global", "savechanges");
echo "\" class=\"button\"></p>

</form>

";

if ($whmcs->get_req_var("2faenforce")) {
	$aInt->jquerycode = "dialogOpen();";
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>