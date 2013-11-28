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

function initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav) {
	global $whmcs;
	global $CONFIG;
	global $_LANG;
	global $templates_compiledir;
	global $in_ssl;
	global $clientsdetails;
	global $smarty;
	global $smartyvalues;

	include_once ROOTDIR . "/includes/smarty/Smarty.class.php";
	$smarty = new Smarty();
	$smarty->caching = 0;
	$smarty->template_dir = ROOTDIR . "/templates/";
	$smarty->compile_dir = $templates_compiledir;
	$filename = $_SERVER['PHP_SELF'];
	$filename = substr($filename, strrpos($filename, "/"));
	$filename = str_replace("/", "", $filename);
	$filename = explode(".", $filename);
	$filename = $filename[0];
	$breadcrumb = array();
	$parts = explode(" > ", $breadcrumbnav);
	foreach ($parts as $part) {
		$parts2 = explode("\">", $part, 2);
		$link = str_replace("<a href=\"", "", $parts2[0]);
		$breadcrumb[] = array("link" => $link, "label" => strip_tags($parts2[1]));
	}

	$smarty->assign("template", $whmcs->get_sys_tpl_name());
	$smarty->assign("language", $whmcs->get_client_language());
	$smarty->assign("LANG", $_LANG);
	$smarty->assign("companyname", $CONFIG['CompanyName']);
	$smarty->assign("logo", $CONFIG['LogoURL']);
	$smarty->assign("charset", $CONFIG['Charset']);
	$smarty->assign("pagetitle", $pagetitle);
	$smarty->assign("pageicon", $pageicon);
	$smarty->assign("filename", $filename);
	$smarty->assign("breadcrumb", $breadcrumb);
	$smarty->assign("breadcrumbnav", $breadcrumbnav);
	$smarty->assign("todaysdate", date("l, jS F Y"));
	$smarty->assign("date_day", date("d"));
	$smarty->assign("date_month", date("m"));
	$smarty->assign("date_year", date("Y"));
	$smarty->assign("token", generate_token("plain"));

	if ($CONFIG['SystemSSLURL']) {
		$smarty->assign("systemsslurl", $CONFIG['SystemSSLURL'] . "/");
	}


	if ($in_ssl && $CONFIG['SystemSSLURL']) {
		$smarty->assign("systemurl", $CONFIG['SystemSSLURL'] . "/");
	}
	else {
		if ($CONFIG['SystemURL'] != "http://www.yourdomain.com/whmcs") {
			$smarty->assign("systemurl", $CONFIG['SystemURL'] . "/");
		}
	}


	if (isset($_SESSION['uid'])) {
		$smarty->assign("loggedin", true);

		if (!function_exists("getClientsDetails")) {
			require ROOTDIR . "/includes/clientfunctions.php";
		}

		$clientsdetails = getClientsDetails();
		$smarty->assign("clientsdetails", $clientsdetails);
		$smarty->assign("clientsstats", getClientsStats($_SESSION['uid']));

		if (isset($_SESSION['cid'])) {
			$result = select_query("tblcontacts", "id,firstname,lastname,email,permissions", array("id" => $_SESSION['cid'], "userid" => $_SESSION['uid']));
			$data = mysql_fetch_array($result);
			$loggedinuser = array("contactid" => $data['id'], "firstname" => $data['firstname'], "lastname" => $data['lastname'], "email" => $data['email']);
			$contactpermissions = explode(",", $data[4]);
		}
		else {
			$loggedinuser = array("userid" => $_SESSION['uid'], "firstname" => $clientsdetails['firstname'], "lastname" => $clientsdetails['lastname'], "email" => $clientsdetails['email']);
			$contactpermissions = array("profile", "contacts", "products", "manageproducts", "domains", "managedomains", "invoices", "tickets", "affiliates", "emails", "orders");
		}

		$smarty->assign("loggedinuser", $loggedinuser);
		$smarty->assign("contactpermissions", $contactpermissions);
	}


	if ($CONFIG['AllowLanguageChange'] == "on") {
		$smarty->assign("langchange", "true");
	}

	$setlanguage = "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'];
	$count = 0;
	foreach ($_GET as $k => $v) {
		$prefix = ($count == 0 ? "?" : "&");
		$setlanguage .= $prefix . htmlentities($k) . "=" . htmlentities($v);
		++$count;
	}

	$setlanguage .= "\" name=\"languagefrm\" id=\"languagefrm\"><strong>" . $_LANG['language'] . ":</strong> <select name=\"language\" onchange=\"languagefrm.submit()\">";
	foreach ($whmcs->getValidLanguages() as $lang) {
		$setlanguage .= "<option";

		if ($lang == $whmcs->get_client_language()) {
			$setlanguage .= " selected=\"selected\"";
		}

		$setlanguage .= ">" . ucfirst($lang) . "</option>";
	}

	$setlanguage .= "</select></form>";
	$smarty->assign("setlanguage", $setlanguage);
	$currenciesarray = array();
	$result = select_query("tblcurrencies", "id,code,`default`", "", "code", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$currenciesarray[] = array("id" => $data['id'], "code" => $data['code'], "default" => $data['default']);
	}


	if (count($currenciesarray) == 1) {
		$currenciesarray = "";
	}

	$smarty->assign("currencies", $currenciesarray);
	$smarty->assign("twitterusername", $whmcs->get_config("TwitterUsername"));
	$smarty->assign("condlinks", WHMCS_ClientArea::getconditionallinks());
	$smartyvalues = array();
}

function outputClientArea($templatefile, $nowrapper = false) {
	global $whmcs;
	global $CONFIG;
	global $smarty;
	global $smartyvalues;
	global $orderform;
	global $usingsupportmodule;
	global $licensing;
	global $orderfrm;

	if (!$templatefile) {
		exit("Invalid Entity Requested");
	}


	if ($licensing->getBrandingRemoval()) {
		$copyrighttext = "";
	}
	else {
		$copyrighttext = "<p style=\"text-align:center;\">Powered by <a href=\"http://www.whmcs.com/\" target=\"_blank\">WHMCompleteSolution</a></p>";
	}


	if (isset($_SESSION['adminid'])) {
		$adminloginlink = "<div style=\"position:absolute;top:0px;right:0px;padding:5px;background-color:#000066;font-family:Tahoma;font-size:11px;color:#ffffff\" class=\"adminreturndiv\">Logged in as Administrator | <a href=\"" . $whmcs->get_admin_folder_name() . "/";

		if (isset($_SESSION['uid'])) {
			$adminloginlink .= "clientssummary.php?userid=" . $_SESSION['uid'] . "&return=1";
		}

		$adminloginlink .= "\" style=\"color:#6699ff\">Return to Admin Area</a></div>

";
	}
	else {
		$adminloginlink = "";
	}


	if (isset($GLOBALS['pagelimit'])) {
		$smartyvalues['itemlimit'] = $GLOBALS['pagelimit'];
	}


	if ($smartyvalues) {
		foreach ($smartyvalues as $key => $value) {
			$smarty->assign($key, $value);
		}
	}

	$hookvars = $smarty->_tpl_vars;
	unset($hookvars['LANG']);
	$hookres = run_hook("ClientAreaPage", $hookvars);
	foreach ($hookres as $arr) {
		foreach ($arr as $k => $v) {
			$hookvars[$k] = $v;
			$smarty->assign($k, $v);
		}
	}

	$hookres = run_hook("ClientAreaHeadOutput", $hookvars);
	$headoutput = "";
	foreach ($hookres as $data) {

		if ($data) {
			$headoutput .= $data . "\r\n";
			continue;
		}
	}

	$smarty->assign("headoutput", $headoutput);
	$hookres = run_hook("ClientAreaHeaderOutput", $hookvars);
	$headoutput = "";
	foreach ($hookres as $data) {

		if ($data) {
			$headoutput .= $data . "\r\n";
			continue;
		}
	}

	$smarty->assign("headeroutput", $headoutput);
	$hookres = run_hook("ClientAreaFooterOutput", $hookvars);
	$headoutput = "";
	foreach ($hookres as $data) {

		if ($data) {
			$headoutput .= $data . "\r\n";
			continue;
		}
	}

	$smarty->assign("footeroutput", $headoutput);

	if (!$nowrapper) {
		$header_file = $smarty->fetch($whmcs->get_sys_tpl_name() . "/header.tpl");
		$footer_file = $smarty->fetch($whmcs->get_sys_tpl_name() . "/footer.tpl");
	}


	if ($orderform) {
		$body_file = $smarty->fetch(ROOTDIR . "/templates/orderforms/" . $orderfrm->getTemplate() . "/" . $templatefile . ".tpl");
	}
	else {
		if ($usingsupportmodule) {
			$body_file = $smarty->fetch(ROOTDIR . "/templates/" . $CONFIG['SupportModule'] . "/" . $templatefile . ".tpl");
		}
		else {
			if (substr($templatefile, 0, 1) == "/") {
				$body_file = $smarty->fetch(ROOTDIR . $templatefile);
			}
			else {
				$body_file = $smarty->fetch(ROOTDIR . "/templates/" . $whmcs->get_sys_tpl_name() . "/" . $templatefile . ".tpl");
			}
		}
	}


	if ($nowrapper) {
		$template_output = $body_file;
	}
	else {
		$template_output = $header_file . $body_file . "

" . $copyrighttext . "

" . $adminloginlink . $footer_file;
	}


	if (!in_array($templatefile, array("3dsecure", "forwardpage", "viewinvoice"))) {
		$template_output = preg_replace("/(<form\W[^>]*\bmethod=('|\"|)POST('|\"|)\b[^>]*>)/i", "$1" . "\r\n" . generate_token(), $template_output);

	}

	echo $template_output;
}

function processSingleTemplate($templatepath, $templatevars) {
	global $CONFIG;
	global $smarty;
	global $smartyvalues;

	if ($smartyvalues) {
		foreach ($smartyvalues as $key => $value) {
			$smarty->assign($key, $value);
		}
	}

	foreach ($templatevars as $key => $value) {
		$smarty->assign($key, $value);
	}

	$templatecode = $smarty->fetch(ROOTDIR . $templatepath);
	return $templatecode;
}

function CALinkUpdateCC() {
	global $CONFIG;

	$result = select_query("tblpaymentgateways", "gateway", array("setting" => "type", "value" => "CC"));

	while ($data = mysql_fetch_array($result)) {
		$gateway = $data['gateway'];

		if (!isValidforPath($gateway)) {
			exit("Invalid Gateway Module Name");
		}


		if (file_exists(ROOTDIR . ("/modules/gateways/" . $gateway . ".php"))) {
			require_once ROOTDIR . ("/modules/gateways/" . $gateway . ".php");
		}


		if (function_exists($gateway . "_remoteupdate")) {
			$_SESSION['calinkupdatecc'] = 1;
			return true;
		}
	}


	if (!$CONFIG['CCNeverStore']) {
		$result = select_query("tblpaymentgateways", "COUNT(*)", "setting='type' AND (value='CC' OR value='OfflineCC')");
		$data = mysql_fetch_array($result);

		if ($data[0]) {
			$_SESSION['calinkupdatecc'] = 1;
			return true;
		}
	}

	$_SESSION['calinkupdatecc'] = 0;
	return false;
}

function CALinkUpdateSQ() {
	$get_sq_count = get_query_val("tbladminsecurityquestions", "COUNT(id)", "");

	if (0 < $get_sq_count) {
		$_SESSION['calinkupdatesq'] = 1;
		return true;
	}

	$_SESSION['calinkupdatesq'] = 0;
	return false;
}

function clientAreaTableInit($name, $defaultorderby, $defaultsort, $numitems) {
	global $whmcs;

	$pagelimit = "";
	$itemlimit = $whmcs->get_req_var("itemlimit");
	$orderby = $whmcs->get_req_var("orderby");

	if ($itemlimit == "all") {
		$pagelimit = 99999999;
	}
	else {
		if (is_numeric($itemlimit)) {
			$pagelimit = $itemlimit;
		}
	}


	if ($pagelimit) {
		setcookie("pagelimit", $pagelimit, time() + 90 * 24 * 60 * 60);
	}


	if ((!$pagelimit && isset($_COOKIE['pagelimit'])) && is_numeric($_COOKIE['pagelimit'])) {
		$pagelimit = $_COOKIE['pagelimit'];
	}


	if (!$pagelimit) {
		$pagelimit = "10";
	}

	$GLOBALS['pagelimit'] = $pagelimit;
	$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : "1";

	if ($numitems < ($page - 1) * $pagelimit) {
		$page = 1;
	}

	$GLOBALS['page'] = $page;

	if (!isset($_SESSION["ca" . $name . "orderby"])) {
		$_SESSION["ca" . $name . "orderby"] = $defaultorderby;
	}


	if (!isset($_SESSION["ca" . $name . "sort"])) {
		$_SESSION["ca" . $name . "sort"] = $defaultsort;
	}


	if ($_SESSION["ca" . $name . "orderby"] == $orderby) {
		if ($_SESSION["ca" . $name . "sort"] == "ASC") {
			$_SESSION["ca" . $name . "sort"] = "DESC";
		}
		else {
			$_SESSION["ca" . $name . "sort"] = "ASC";
		}
	}


	if ($orderby) {
		$_SESSION["ca" . $name . "orderby"] = $_REQUEST['orderby'];
	}

	$orderby = preg_replace("/[^a-z0-9]/", "", $_SESSION["ca" . $name . "orderby"]);
	$sort = $_SESSION["ca" . $name . "sort"];

	if (!in_array($sort, array("ASC", "DESC"))) {
		$sort = "ASC";
	}

	$limit = ($page - 1) * $pagelimit . ("," . $pagelimit);
	return array($orderby, $sort, $limit);
}

function clientAreaTablePageNav($numitems) {
	$totalpages = ceil($numitems / (int)$GLOBALS['pagelimit']);
	$pagenumber = (int)$GLOBALS['page'];
	$prevpage = ($pagenumber != 1 ? $pagenumber - 1 : "");
	$nextpage = (($pagenumber != $totalpages && $numitems) ? $pagenumber + 1 : "");

	if (!$totalpages) {
		$totalpages = 1;
	}

	return array("numitems" => $numitems, "numproducts" => $numitems, "pagenumber" => $pagenumber, "totalpages" => $totalpages, "prevpage" => $prevpage, "nextpage" => $nextpage);
}

function clientAreaInitCaptcha() {
	global $whmcs;

	$capatacha = "";

	if ($whmcs->get_config("CaptchaSetting") == "on" || ($whmcs->get_config("CaptchaSetting") == "offloggedin" && !isset($_SESSION['uid']))) {
		if ($whmcs->get_config("CaptchaType") == "recaptcha") {
			require ROOTDIR . "/includes/recaptchalib.php";
			$capatacha = "recaptcha";
		}
		else {
			$capatacha = "default";
		}
	}

	$GLOBALS['capatacha'] = $capatacha;
	return $capatacha;
}

function clientAreaReCaptchaHTML() {
	global $CONFIG;

	if ($GLOBALS['capatacha'] != "recaptcha") {
		return "";
	}

	$publickey = $CONFIG['ReCAPTCHAPublicKey'];
	$recapatcha = recaptcha_get_html($publickey);
	return $recapatcha;
}

?>