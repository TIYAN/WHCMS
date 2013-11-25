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
$aInt = new WHMCS_Admin("Addon Modules", false);
$aInt->title = $aInt->lang("utilities", "addonmodules");
$aInt->sidebar = "addonmodules";
$aInt->icon = "addonmodules";
$action = $whmcs->get_req_var("action");

if ($action == "getcats") {
	$data = curlCall("http://www.whmcs.com/members/communityaddonsfeed.php", "action=getcats");
	echo $data;
	exit();
}


if ($action == "getaddons") {
	$activeaddonmodules = $CONFIG['ActiveAddonModules'];
	$data = array("active" => explode(",", $activeaddonmodules));

	if (is_dir(ROOTDIR . "/modules/addons/")) {
		$dh = opendir(ROOTDIR . "/modules/addons/");

		while (false !== $file = readdir($dh)) {
			$modfilename = ROOTDIR . ("/modules/addons/" . $file . "/" . $file . ".php");

			if (is_file($modfilename)) {
				$data['installed'][] = $file;
			}
		}
	}

	$data = curlCall("http://www.whmcs.com/members/communityaddonsfeed.php", "action=getaddons&catid=" . $catid . "&search=" . $search . "&modules=" . json_encode($data));
	echo $data;
	exit();
}

global $jquerycode;
global $jscode;

$jquerycode = $jscode = "";
ob_start();

if (!$module = $whmcs->get_req_var("module")) {
	$aInt->title = $aInt->lang("utilities", "addonsdirectory");
	echo "
<div id=\"searchaddons\"><form onsubmit=\"search();return false\"><input type=\"text\" id=\"searchterm\" /> <input type=\"submit\" value=\"Search\" /></form></div>
<div id=\"addonscats\"></div>
<div id=\"addonslist\">
<div class=\"loading\">";
	echo $aInt->lang("global", "loading");
	echo "<br /><img src=\"../images/loading.gif\" /></div>
</div>
<div style=\"clear:both;\"></div>

<p style=\"font-size:10px;\">* Please note that any addon modules listed above outside of the \"Official Addon's\" directory are third party modules that WHMCS is in no way affiliated with or endorsing by listing them in the addons directory. We are unable to provide support for, and cannot be held responsible for any pro";
	echo "blems resulting from the use of third party addons.</p>

";
	$jscode = "function loadcats() {
    $.post(\"addonmodules.php\", { action: \"getcats\" },
	    function(data){
		    $(\"#addonscats\").html(data);
		});
}
function loadaddons(id) {
    $(\".cat\").removeClass(\"addonsel\");
    $(\"#cat\"+id).addClass(\"addonsel\")
    $(\"#addonslist\").html('<div class=\"loading\">" . $aInt->lang("global", "loading", 1) . "<br /><img src=\"../images/loading.gif\" /></div>');
    $.post(\"addonmodules.php\", { action: \"getaddons\", catid: id },
	    function(data){
		    $(\"#addonslist\").html(data);
		});
}
function search() {
    $(\".cat\").removeClass(\"addonsel\");
    $(\"#cat\").addClass(\"addonsel\")
    $(\"#addonslist\").html('<div class=\"loading\">" . $aInt->lang("global", "loading", 1) . "<br /><img src=\"../images/loading.gif\" /></div>');
    $.post(\"addonmodules.php\", { action: \"getaddons\", search: $(\"#searchterm\").val() },
	    function(data){
		    $(\"#addonslist\").html(data);
		});
}";
	$jquerycode = "loadcats();loadaddons(\"\");";
}
else {
	$activeaddonmodules = $CONFIG['ActiveAddonModules'];
	$activeaddonmodules = explode(",", $activeaddonmodules);

	if (!in_array($module, $activeaddonmodules)) {
		$aInt->gracefulExit("Invalid Module Name. Please Try Again.");
	}

	$modulelink = "addonmodules.php?module=" . $module;
	$result = select_query("tbladdonmodules", "value", array("module" => $module, "setting" => "access"));
	$data = mysql_fetch_array($result);
	$allowedroles = explode(",", $data[0]);
	$result = select_query("tbladmins", "roleid", array("id" => $_SESSION['adminid']));
	$data = mysql_fetch_array($result);
	$adminroleid = $data[0];

	if (!isValidforPath($module)) {
		exit("Invalid Addon Module Name");
	}

	$modulepath = ROOTDIR . ("/modules/addons/" . $module . "/" . $module . ".php");

	if (file_exists($modulepath)) {
		require $modulepath;

		if (function_exists($module . "_config")) {
			$configarray = call_user_func($module . "_config");
			$aInt->title = $configarray['name'];

			if (in_array($adminroleid, $allowedroles)) {
				$modulevars = array("modulelink" => $modulelink);
				$result = select_query("tbladdonmodules", "", array("module" => $module));

				while ($data = mysql_fetch_array($result)) {
					$modulevars[$data['setting']] = $data['value'];
				}

				$_ADDONLANG = array();

				if (!isValidforPath($aInt->language)) {
					exit("Invalid Admin Language Name");
				}

				$addonlangfile = ROOTDIR . ("/modules/addons/" . $module . "/lang/") . $aInt->language . ".php";

				if (file_exists($addonlangfile)) {
					require $addonlangfile;
				}
				else {
					if ($configarray['language']) {
						if (!isValidforPath($configarray['language'])) {
							exit("Invalid Language Name from Addon Module Config");
						}

						$addonlangfile = ROOTDIR . ("/modules/addons/" . $module . "/lang/") . $configarray['language'] . ".php";

						if (file_exists($addonlangfile)) {
							require $addonlangfile;
						}
					}
				}


				if (count($_ADDONLANG)) {
					$modulevars['_lang'] = $_ADDONLANG;
				}


				if ($modulevars['version'] != $configarray['version']) {
					if (function_exists($module . "_upgrade")) {
						call_user_func($module . "_upgrade", $modulevars);
					}

					update_query("tbladdonmodules", array("value" => $configarray['version']), array("module" => $module, "setting" => "version"));
				}

				$sidebar = "";

				if (function_exists($module . "_sidebar")) {
					$sidebar = call_user_func($module . "_sidebar", $modulevars);
				}

				$aInt->assign("addon_module_sidebar", $sidebar);

				if (function_exists($module . "_output")) {
					call_user_func($module . "_output", $modulevars);
				}
				else {
					echo "<p>" . $aInt->lang("addonmodules", "nooutput") . "</p>";
				}
			}
			else {
				echo "<br /><br />
<p align=\"center\"><b>" . $aInt->lang("permissions", "accessdenied") . "</b></p>
<p align=\"center\">" . $aInt->lang("addonmodules", "noaccess") . "</p>
<p align=\"center\">" . $aInt->lang("addonmodules", "howtogrant") . "</p>";
			}
		}
		else {
			echo "<p>" . $aInt->lang("addonmodules", "error") . "</p>";
		}
	}
	else {
		$pagetitle = str_replace("_", " ", $module);
		$pagetitle = titleCase($pagetitle);
		echo "<h2>" . $pagetitle . "</h2>";

		if (in_array($adminroleid, $allowedroles)) {
			if (!isValidforPath($module)) {
				exit("Invalid Addon Module Name");
			}

			$modulepath = ROOTDIR . ("/modules/admin/" . $module . "/" . $module . ".php");

			if (file_exists($modulepath)) {
				require $modulepath;
			}
			else {
				echo "<p>" . $aInt->lang("addonmodules", "nooutput") . "</p>";
			}
		}
		else {
			echo "<br /><br />
<p align=\"center\"><b>" . $aInt->lang("permissions", "accessdenied") . "</b></p>
<p align=\"center\">" . $aInt->lang("addonmodules", "noaccess") . "</p>
<p align=\"center\">" . $aInt->lang("addonmodules", "howtogrant") . "</p>";
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