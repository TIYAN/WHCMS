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

function getValidLanguages($admin = "") {
	global $whmcs;

	$langs = $whmcs->getValidLanguages($admin);
	return $langs;
}

function htmlspecialchars_array($arr) {
	global $whmcs;

	return $whmcs->sanitize_input_vars($arr);
}

error_reporting(0);
include dirname(__FILE__) . "/includes/classes/class.init.php";

if (!class_exists("WHMCS_Init")) {
	echo "<div style=\"border: 1px dashed #cc0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#cc0000;\"><strong>维护中 (Err 1)</strong><br>网站升级中... 请稍后访问...</div>";
	exit();
}

$whmcs = new WHMCS_Init();
$whmcs = $whmcs->init();

if ($CONFIG['Version'] == "5.2.14") {
	update_query("tblconfiguration", array("value" => "5.2.15"), array("setting" => "Version"));
	$CONFIG['Version'] = "5.2.15";
}


if ($CONFIG['Version'] != "5.2.15") {
	if (file_exists("../install/install.php")) {
		header("Location: ../install/install.php");
		exit();
	}

	echo "<div style=\"border: 1px dashed #cc0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#cc0000;\"><strong>维护中 (Err 2)</strong><br>网站升级中... 请稍后访问...</div>";
	exit();
}


if (file_exists(ROOTDIR . "/install/install.php")) {
	echo "<div style=\"border: 1px dashed #cc0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#cc0000;\"><strong>安全提醒</strong><br>你必须删除 install 目录才能继续使用 WHMCS</div>";
	exit();
}


if (!$whmcs->check_template_cache_writeable()) {
	exit("<div style=\"border: 1px dashed #cc0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#cc0000;\"><strong>权限错误</strong><br>The 模版缓存目录 '" . $whmcs->get_template_compiledir_name() . "' 必须设置为可写 (CHMOD 777) 才能继续。<br>如果此路径错误, 请在 configuration.php file 中修正。</div>");
}


if ((defined("CLIENTAREA") && $CONFIG['MaintenanceMode']) && !$_SESSION['adminid']) {
	if ($CONFIG['MaintenanceModeURL']) {
		header("Location: " . $CONFIG['MaintenanceModeURL']);
		exit();
	}

	echo "<div style=\"border: 1px dashed #cc0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#cc0000;\"><strong>维护中 (Err 3)</strong><br>" . $CONFIG['MaintenanceModeMessage'] . "</div>";
	exit();
}

$licensing = WHMCS_License::init();

if ($licensing->getVersionHash() != "9eb7da5f081b3fc7ae1e460afdcb89ea8239eca1") {
	exit("License Checking Error");
}


if ((defined("CLIENTAREA") && isset($_SESSION['uid'])) && !isset($_SESSION['adminid'])) {
	$twofa = new WHMCS_2FA();
	$twofa->setClientID($_SESSION['uid']);

	if (($twofa->isForced() && !$twofa->isEnabled()) && $twofa->isActiveClients()) {
		if ($whmcs->get_filename() == "clientarea" && ($whmcs->get_req_var("action") == "security" || $whmcs->get_req_var("2fasetup"))) {
		}
		else {
			redir("action=security&2fasetup=1&enforce=1", "clientarea.php");
		}
	}
}


if (isset($_SESSION['currency']) && is_array($_SESSION['currency'])) {
	$_SESSION['currency'] = $_SESSION['currency']['id'];
}


if (!isset($_SESSION['uid']) && isset($_REQUEST['currency'])) {
	$result = select_query("tblcurrencies", "id", array("id" => (int)$_REQUEST['currency']));
	$data = mysql_fetch_array($result);

	if ($data['id']) {
		$_SESSION['currency'] = $data['id'];
	}
}


if (defined("CLIENTAREA") && isset($_REQUEST['language'])) {
	$whmcs->set_client_language($_REQUEST['language']);
}

$whmcs->loadLanguage();

if (defined("CLIENTAREA") && $CONFIG['SystemSSLURL']) {
	$files = array("aff.php", "clientarea.php", "supporttickets.php", "contact.php", "passwordreminder.php", "login.php", "logout.php", "affiliates.php", "submitticket.php", "viewemail.php", "viewinvoice.php", "viewticket.php", "creditcard.php", "register.php", "upgrade.php", "cart.php", "configuressl.php", "domainchecker.php", "networkissues.php", "serverstatus.php", "pwreset.php");
	$nonsslfiles = array("announcements.php", "banned.php", "contact.php", "downloads.php", "index.php", "tutorials.php", "whois.php", "knowledgebase.php");

	if (!defined("WHMCSWWW1")) {
		$nonsslfiles[] = "dl.php";
	}

	$filename = $_SERVER['PHP_SELF'];
	$filename = substr($filename, strrpos($filename, "/"));
	$filename = str_replace("/", "", $filename);
	$ssldomain = $CONFIG['SystemSSLURL'];
	$nonssldomain = $CONFIG['SystemURL'];

	if ($_SESSION['FORCESSL'] && $filename == "index.php") {
		define("FORCESSL", true);
	}


	if (in_array($filename, $files) || defined("FORCESSL")) {
		if (!$_SERVER['HTTPS'] || $_SERVER['HTTPS'] == "off") {
			redir($_REQUEST, $ssldomain . "/" . $filename);
		}

		$in_ssl = true;
	}
	else {
		if (in_array($filename, $nonsslfiles)) {
			if ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") {
				redir($_REQUEST, $nonssldomain . "/" . $filename);
			}
		}
	}
}

ob_start();
require ROOTDIR . "/includes/hookfunctions.php";
ob_end_clean();
?>