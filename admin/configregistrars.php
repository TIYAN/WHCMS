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
$aInt = new WHMCS_Admin("Configure Domain Registrars");
$aInt->title = $aInt->lang("domainregistrars", "title");
$aInt->sidebar = "config";
$aInt->icon = "domains";
$aInt->helplink = "Domain Registrars";
$aInt->requiredFiles(array("registrarfunctions", "modulefunctions"));

if ($action == "save") {
	check_token("WHMCS.admin.default");
	$module = $_GET['module'];
	unset($_POST['token']);
	unset($_POST['save']);

	if ($module) {
		delete_query("tblregistrars", array("registrar" => $module));
		foreach ($_POST as $key => $value) {
			insert_query("tblregistrars", array("registrar" => $module, "setting" => $key, "value" => encrypt(html_entity_decode(trim($value)))));
		}
	}

	header("Location: " . $_SERVER['PHP_SELF'] . ("?saved=true#" . $module));
	exit();
}


if ($action == "activate") {
	check_token("WHMCS.admin.default");
	$module = $_GET['module'];

	if ($module) {
		delete_query("tblregistrars", array("registrar" => $module));
		insert_query("tblregistrars", array("registrar" => $module, "setting" => "Username", "value" => ""));
	}

	header("Location: " . $_SERVER['PHP_SELF'] . ("?activated=true#" . $module));
	exit();
}


if ($action == "deactivate") {
	check_token("WHMCS.admin.default");
	$module = $_GET['module'];

	if ($module) {
		delete_query("tblregistrars", array("registrar" => $module));
	}

	header("Location: " . $_SERVER['PHP_SELF'] . "?deactivated=true");
	exit();
}

ob_start();

if ($saved) {
	infoBox($aInt->lang("domainregistrars", "changesuccess"), $aInt->lang("domainregistrars", "changesuccessinfo"));
}


if ($activated) {
	infoBox($aInt->lang("domainregistrars", "moduleactivated"), $aInt->lang("domainregistrars", "moduleactivatedinfo"), "success");
}


if ($deactivated) {
	infoBox($aInt->lang("domainregistrars", "moduledeactivated"), $aInt->lang("domainregistrars", "moduledeactivatedinfo"), "success");
}

echo $infobox;
$aInt->deleteJSConfirm("deactivateMod", "domainregistrars", "deactivatesure", $_SERVER['PHP_SELF'] . "?action=deactivate&module=");
$jscode .= "function showConfig(module) {
    $(\"#\"+module+\"config\").fadeToggle();
}
";
echo "<div class=\"tablebg\">
<table class=\"datatable\" width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"3\">
<tr><th width=\"140\"></th><th>" . $aInt->lang("addonmodules", "module") . "</th><th width=\"350\"></th></tr>";
$modulesarray = array();
$dh = opendir("../modules/registrars/");

while (false !== $file = readdir($dh)) {
	if (is_file("../modules/registrars/" . $file . "/" . $file . ".php")) {
		$modulesarray[] = $file;
	}
}

closedir($dh);
sort($modulesarray);
foreach ($modulesarray as $module) {

	if (!isValidforPath($module)) {
		exit("Invalid Registrar Module Name");
	}


	if (file_exists("../modules/registrars/" . $module . "/logo.gif")) {
		$registrarlogourl = "../modules/registrars/" . $module . "/logo.gif";
	}
	else {
		if (file_exists("../modules/registrars/" . $module . "/logo.jpg")) {
			$registrarlogourl = "../modules/registrars/" . $module . "/logo.jpg";
		}
		else {
			if (file_exists("../modules/registrars/" . $module . "/logo.png")) {
				$registrarlogourl = "../modules/registrars/" . $module . "/logo.png";
			}
			else {
				$registrarlogourl = "./images/spacer.gif";
			}
		}
	}

	$moduleactive = false;
	$moduleconfigdata = getRegistrarConfigOptions($module);

	if (is_array($moduleconfigdata) && !empty($moduleconfigdata)) {
		$moduleactive = true;
		$moduleaction = "<input type=\"button\" value=\"" . $aInt->lang("addonmodules", "activate") . "\" disabled=\"disabled\" class=\"btn disabled\" /> <input type=\"button\" value=\"" . $aInt->lang("addonmodules", "deactivate") . "\" onclick=\"deactivateMod('" . $module . "');return false\" class=\"btn-danger\" />  <input type=\"button\" value=\"" . $aInt->lang("addonmodules", "config") . "\" class=\"btn\" onclick=\"showConfig('" . $module . "')\" />";
	}
	else {
		$moduleaction = "<input type=\"button\" value=\"" . $aInt->lang("addonmodules", "activate") . "\" onclick=\"window.location='" . $_SERVER['PHP_SELF'] . "?action=activate&module=" . $module . generate_token("link") . "'\" class=\"btn-success\" /> <input type=\"button\" value=\"" . $aInt->lang("addonmodules", "deactivate") . "\" disabled=\"disabled\" class=\"btn disabled\" /> <input type=\"button\" value=\"" . $aInt->lang("addonmodules", "config") . "\" disabled=\"disabled\" class=\"btn disabled\" />";
	}

	$regpath = ROOTDIR . ("/modules/registrars/" . $module . "/" . $module . ".php");

	if (file_exists($regpath)) {
		require_once $regpath;
	}

	$configarray = call_user_func($module . "_getConfigArray", $params);
	echo "	<tr id=\"formholder_";
	echo $module;
	echo "\" ";

	if ($moduleactive) {
		echo "class=\"active\" style=\"background-color:#EBFEE2;\"";
	}

	echo ">
		<td align=\"center\" ";

	if ($moduleactive) {
		echo "style=\"background-color:#EBFEE2;\"";
	}

	echo "><a name=\"";
	echo $module;
	echo "\"></a><img src=\"";
	echo $registrarlogourl;
	echo "\" width=\"125\" height=\"40\" style=\"border:1px solid #ccc;\" /></td>
		<td class=\"title\" ";

	if ($moduleactive) {
		echo "style=\"background-color:#EBFEE2;\"";
	}

	echo ">";
	echo "<s";
	echo "trong>&nbsp;&raquo; ";
	echo $configarray['FriendlyName']['Value'] ? $configarray['FriendlyName']['Value'] : ucfirst($module);
	echo "</strong>";

	if ($configarray['Description']['Value']) {
		echo "<br />" . $configarray['Description']['Value'];
	}

	echo "</td>
		<td width=\"200\" align=\"center\" ";

	if ($moduleactive) {
		echo "style=\"background-color:#EBFEE2;\"";
	}

	echo ">";
	echo $moduleaction;
	echo "</td>
	</tr>
	<tr><td id=\"";
	echo $module;
	echo "config\" class=\"config\" style=\"display:none;padding:15px;\" colspan=\"3\"><form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "?action=save&module=";
	echo $module . generate_token("link");
	echo "\">
		<table class=\"form\" width=\"100%\">
        ";
	foreach ($configarray as $key => $values) {

		if ($values['Type'] != "System") {
			if (!$values['FriendlyName']) {
				$values['FriendlyName'] = $key;
			}

			$values['Name'] = $key;
			$values['Value'] = htmlspecialchars($moduleconfigdata[$key]);
			echo "<tr><td class=\"fieldlabel\">" . $values['FriendlyName'] . "</td><td class=\"fieldarea\">" . moduleConfigFieldOutput($values) . "</td></tr>";
			continue;
		}
	}

	echo "		</table><br /><div align=\"center\"><input type=\"submit\" name=\"save\" value=\"";
	echo $aInt->lang("global", "savechanges");
	echo "\" class=\"btn primary\" /></form></div><br />
	</td></tr>
";
}

echo "</table>
</div>

";
echo "<s";
echo "cript language=\"javascript\">
$(document).ready(function(){
    var modpass = window.location.hash;
    if (modpass) $(modpass+\"config\").show();
});
</script>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>