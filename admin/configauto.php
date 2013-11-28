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
$aInt = new WHMCS_Admin("Configure Automation Settings");
$aInt->title = $aInt->lang("automation", "title");
$aInt->sidebar = "config";
$aInt->icon = "autosettings";
$aInt->helplink = "Automation Settings";

if ($sub == "save") {
	check_token("WHMCS.admin.default");
	update_query("tblconfiguration", array("value" => $autosuspend), array("setting" => "AutoSuspension"));
	update_query("tblconfiguration", array("value" => $days), array("setting" => "AutoSuspensionDays"));
	update_query("tblconfiguration", array("value" => $createinvoicedays), array("setting" => "CreateInvoiceDaysBefore"));
	update_query("tblconfiguration", array("value" => $createdomaininvoicedays), array("setting" => "CreateDomainInvoiceDaysBefore"));
	update_query("tblconfiguration", array("value" => $invoicesendreminder), array("setting" => "SendReminder"));
	update_query("tblconfiguration", array("value" => $invoicesendreminderdays), array("setting" => "SendInvoiceReminderDays"));
	update_query("tblconfiguration", array("value" => $updatestatusauto), array("setting" => "UpdateStatsAuto"));
	update_query("tblconfiguration", array("value" => $closeinactivetickets), array("setting" => "CloseInactiveTickets"));
	update_query("tblconfiguration", array("value" => $autotermination), array("setting" => "AutoTermination"));
	update_query("tblconfiguration", array("value" => $autoterminationdays), array("setting" => "AutoTerminationDays"));
	update_query("tblconfiguration", array("value" => $autounsuspend), array("setting" => "AutoUnsuspend"));
	update_query("tblconfiguration", array("value" => $addlatefeedays), array("setting" => "AddLateFeeDays"));
	update_query("tblconfiguration", array("value" => $invoicefirstoverduereminder), array("setting" => "SendFirstOverdueInvoiceReminder"));
	update_query("tblconfiguration", array("value" => $invoicesecondoverduereminder), array("setting" => "SendSecondOverdueInvoiceReminder"));
	update_query("tblconfiguration", array("value" => $invoicethirdoverduereminder), array("setting" => "SendThirdOverdueInvoiceReminder"));
	update_query("tblconfiguration", array("value" => $autocancellationrequests), array("setting" => "AutoCancellationRequests"));
	update_query("tblconfiguration", array("value" => $ccprocessdaysbefore), array("setting" => "CCProcessDaysBefore"));
	update_query("tblconfiguration", array("value" => $ccattemptonlyonce), array("setting" => "CCAttemptOnlyOnce"));
	update_query("tblconfiguration", array("value" => $ccretryeveryweekfor), array("setting" => "CCRetryEveryWeekFor"));
	update_query("tblconfiguration", array("value" => $ccdaysendexpirynotices), array("setting" => "CCDaySendExpiryNotices"));
	update_query("tblconfiguration", array("value" => $donotremovecconexpiry), array("setting" => "CCDoNotRemoveOnExpiry"));
	update_query("tblconfiguration", array("value" => $currencyautoupdateexchangerates), array("setting" => "CurrencyAutoUpdateExchangeRates"));
	update_query("tblconfiguration", array("value" => $currencyautoupdateproductprices), array("setting" => "CurrencyAutoUpdateProductPrices"));
	update_query("tblconfiguration", array("value" => $overagebillingmethod), array("setting" => "OverageBillingMethod"));
	update_query("tblconfiguration", array("value" => $invoicegenmonthly), array("setting" => "CreateInvoiceDaysBeforeMonthly"));
	update_query("tblconfiguration", array("value" => $invoicegenquarterly), array("setting" => "CreateInvoiceDaysBeforeQuarterly"));
	update_query("tblconfiguration", array("value" => $invoicegensemiannually), array("setting" => "CreateInvoiceDaysBeforeSemiAnnually"));
	update_query("tblconfiguration", array("value" => $invoicegenannually), array("setting" => "CreateInvoiceDaysBeforeAnnually"));
	update_query("tblconfiguration", array("value" => $invoicegenbiennially), array("setting" => "CreateInvoiceDaysBeforeBiennially"));
	update_query("tblconfiguration", array("value" => $invoicegentriennially), array("setting" => "CreateInvoiceDaysBeforeTriennially"));
	update_query("tblconfiguration", array("value" => $autoclientstatuschange), array("setting" => "AutoClientStatusChange"));
	$renewalstring = "";
	foreach ($renewals as $renewal) {
		$renewalstring .= "" . $renewal . ",";
	}

	update_query("tblconfiguration", array("value" => $renewalstring), array("setting" => "DomainRenewalNotices"));
	redir("success=true");
	exit();
}

ob_start();

if ($success) {
	infoBox($aInt->lang("automation", "changesuccess"), $aInt->lang("automation", "changesuccessinfo"));
	echo $infobox;
}

echo "
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?sub=save\">
<p>";
echo $aInt->lang("automation", "croninfo");
echo "</p>

<div class=\"contentbox\">
";
echo $aInt->lang("automation", "cronphp");
echo ":<br><input type=\"text\" size=\"100\" value=\"php -q ";
$adminfolder = $whmcs->get_admin_folder_name();
echo ROOTDIR . "/" . $adminfolder;
echo "/cron.php\"><br><b>OR</b><br>
";
echo $aInt->lang("automation", "cronget");
echo ":<br><input type=\"text\" size=\"100\" value=\"GET ";
echo $CONFIG['SystemURL'];
echo "/";
echo $adminfolder;
echo "/cron.php\">
</div><br>

";
$result = select_query("tblconfiguration", "", "");

while ($data = mysql_fetch_array($result)) {
	$setting = $data['setting'];
	$value = $data['value'];
	$CONFIG["" . $setting] = "" . $value;
}

$jscode = "function showadvinvoice() {
    $(\"#advinvoicesettings\").slideToggle();
}";
echo "
<p><b>";
echo $aInt->lang("automation", "modulefunctions");
echo "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "autosuspend");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"autosuspend\"";

if ($CONFIG['AutoSuspension'] == "on") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "autosuspendinfo");
echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "suspenddays");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"days\" value=\"";
echo $CONFIG['AutoSuspensionDays'];
echo "\" size=3> ";
echo $aInt->lang("automation", "suspenddaysinfo");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "autounsuspend");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"autounsuspend\"";

if ($CONFIG['AutoUnsuspend'] == "on") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "autounsuspendinfo");
echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "autoterminate");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"autotermination\"";

if ($CONFIG['AutoTermination'] == "on") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "autoterminateinfo");
echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "terminatedays");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"autoterminationdays\" value=\"";
echo $CONFIG['AutoTerminationDays'];
echo "\" size=3> ";
echo $aInt->lang("automation", "terminatedaysinfo");
echo "</td></tr>
</table>
<p><b>";
echo $aInt->lang("automation", "billingsettings");
echo "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "invoicegen");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"createinvoicedays\" value=\"";
echo $CONFIG['CreateInvoiceDaysBefore'];
echo "\" size=3> ";
echo $aInt->lang("automation", "invoicegeninfo");
echo " (<a href=\"#\" onclick=\"showadvinvoice();return false\">";
echo $aInt->lang("automation", "advsettings");
echo "</a>)
<div id=\"advinvoicesettings\" align=\"center\" style=\"display:none;\">
<br />
<b>";
echo $aInt->lang("automation", "percycle");
echo "</b><br />
";
echo $aInt->lang("automation", "percycleinfo");
echo ":<br />
<table width=\"650\" cellspacing=\"1\" bgcolor=\"#cccccc\">
<tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold\"><td>";
echo $aInt->lang("billingcycles", "monthly");
echo "</td><td>";
echo $aInt->lang("billingcycles", "quarterly");
echo "</td><td>";
echo $aInt->lang("billingcycles", "semiannually");
echo "</td><td>";
echo $aInt->lang("billingcycles", "annually");
echo "</td><td>";
echo $aInt->lang("billingcycles", "biennially");
echo "</td><td>";
echo $aInt->lang("billingcycles", "triennially");
echo "</td></tr>
<tr bgcolor=\"#ffffff\"><td><input type=\"text\" name=\"invoicegenmonthly\" size=\"10\" value=\"";
echo $CONFIG['CreateInvoiceDaysBeforeMonthly'];
echo "\" /></td><td><input type=\"text\" name=\"invoicegenquarterly\" size=\"10\" value=\"";
echo $CONFIG['CreateInvoiceDaysBeforeQuarterly'];
echo "\" /></td><td><input type=\"text\" name=\"invoicegensemiannually\" size=\"10\" value=\"";
echo $CONFIG['CreateInvoiceDaysBeforeSemiAnnually'];
echo "\" /></td><td><input type=\"text\" name=\"invoicegenannually\" size=\"10\" value=\"";
echo $CONFIG['CreateInvoiceDaysBeforeAnnually'];
echo "\" /></td><td><input type=\"text\" name=\"invoicegenbiennially\" size=\"10\" value=\"";
echo $CONFIG['CreateInvoiceDaysBeforeBiennially'];
echo "\" /></td><td><input type=\"text\" name=\"invoicegentriennially\" size=\"10\" value=\"";
echo $CONFIG['CreateInvoiceDaysBeforeTriennially'];
echo "\" /></td></tr>
</table>
(";
echo $aInt->lang("automation", "blankcycledefault");
echo ")
<br /><br />
<b>";
echo $aInt->lang("automation", "domainsettings");
echo "</b><br />
";
echo $aInt->lang("automation", "domainsettingsinfo");
echo ":<br />
<input type=\"text\" name=\"createdomaininvoicedays\" value=\"";
echo $CONFIG['CreateDomainInvoiceDaysBefore'];
echo "\" size=\"3\"> (";
echo $aInt->lang("automation", "blankdefault");
echo ")<br /><br />
</div>
</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "reminderemails");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"invoicesendreminder\"";

if ($CONFIG['SendReminder'] == "on") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "reminderemailsinfo");
echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "unpaidreminder");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"invoicesendreminderdays\" value=\"";
echo $CONFIG['SendInvoiceReminderDays'];
echo "\" size=3> ";
echo $aInt->lang("automation", "unpaidreminderinfo");
echo " (";
echo $aInt->lang("automation", "todisable");
echo ")</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "firstreminder");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"invoicefirstoverduereminder\" value=\"";
echo $CONFIG['SendFirstOverdueInvoiceReminder'];
echo "\" size=3> ";
echo $aInt->lang("automation", "firstreminderinfo");
echo " (";
echo $aInt->lang("automation", "todisable");
echo ")</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "secondreminder");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"invoicesecondoverduereminder\" value=\"";
echo $CONFIG['SendSecondOverdueInvoiceReminder'];
echo "\" size=3> ";
echo $aInt->lang("automation", "secondreminderinfo");
echo " (";
echo $aInt->lang("automation", "todisable");
echo ")</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "thirdreminder");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"invoicethirdoverduereminder\" value=\"";
echo $CONFIG['SendThirdOverdueInvoiceReminder'];
echo "\" size=3> ";
echo $aInt->lang("automation", "thirdreminderinfo");
echo " (";
echo $aInt->lang("automation", "todisable");
echo ")</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "latefeedays");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"addlatefeedays\" value=\"";
echo $CONFIG['AddLateFeeDays'];
echo "\" size=5> ";
echo $aInt->lang("automation", "latefeedaysinfo");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "overages");
echo "</td><td class=\"fieldarea\"><label><input type=\"radio\" name=\"overagebillingmethod\" value=\"1\"";

if ($CONFIG['OverageBillingMethod'] == "1") {
	echo " checked";
}

echo "> ";
echo $aInt->lang("automation", "overageslastday");
echo "</label><br /><label><input type=\"radio\" name=\"overagebillingmethod\" value=\"2\"";

if ($CONFIG['OverageBillingMethod'] == "2") {
	echo " checked";
}

echo "> ";
echo $aInt->lang("automation", "overagesnextinvoice");
echo "</label></td></tr>
</table>
<p><b>";
echo $aInt->lang("automation", "ccsettings");
echo "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "ccdaysbeforedue");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ccprocessdaysbefore\" value=\"";
echo $CONFIG['CCProcessDaysBefore'];
echo "\" size=3> ";
echo $aInt->lang("automation", "ccdaysbeforedueinfo");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "cconlyonce");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"ccattemptonlyonce\"";

if ($CONFIG['CCAttemptOnlyOnce'] == "on") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "cconlyonceinfo");
echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "cceveryweek");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ccretryeveryweekfor\" value=\"";
echo $CONFIG['CCRetryEveryWeekFor'];
echo "\" size=3> ";
echo $aInt->lang("automation", "cceveryweekinfo");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "ccexpirynotices");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ccdaysendexpirynotices\" value=\"";
echo $CONFIG['CCDaySendExpiryNotices'];
echo "\" size=3> ";
echo $aInt->lang("automation", "ccexpirynoticesinfo");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "ccnoremove");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"donotremovecconexpiry\"";

if ($CONFIG['CCDoNotRemoveOnExpiry'] == "on") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "ccnoremoveinfo");
echo "</label></td></tr>
</table>
<p><b>";
echo $aInt->lang("automation", "currencysettings");
echo "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "exchangerates");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"currencyautoupdateexchangerates\"";

if ($CONFIG['CurrencyAutoUpdateExchangeRates'] == "on") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "exchangeratesinfo");
echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "productprices");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"currencyautoupdateproductprices\"";

if ($CONFIG['CurrencyAutoUpdateProductPrices'] == "on") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "productpricesinfo");
echo "</label></td></tr>
</table>
<p><b>";
echo $aInt->lang("automation", "domainsettings");
echo "</b></p> ";
$renewals = explode(",", $CONFIG['DomainRenewalNotices']);
echo "<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "firstrenewal");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"renewals[0]\" value=\"";
echo $renewals[0];
echo "\" size=3> ";
echo $aInt->lang("automation", "firstrenewalinfo");
echo " (";
echo $aInt->lang("automation", "todisable");
echo ")</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "secondrenewal");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"renewals[1]\" value=\"";
echo $renewals[1];
echo "\" size=3> ";
echo $aInt->lang("automation", "secondrenewalinfo");
echo " (";
echo $aInt->lang("automation", "todisable");
echo ")</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "thirdrenewal");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"renewals[2]\" value=\"";
echo $renewals[2];
echo "\" size=3> ";
echo $aInt->lang("automation", "thirdrenewalinfo");
echo " (";
echo $aInt->lang("automation", "todisable");
echo ")</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "fourthrenewal");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"renewals[3]\" value=\"";
echo $renewals[3];
echo "\" size=3> ";
echo $aInt->lang("automation", "fourthrenewalinfo");
echo " (";
echo $aInt->lang("automation", "todisable");
echo ")</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "fifthrenewal");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"renewals[4]\" value=\"";
echo $renewals[4];
echo "\" size=3> ";
echo $aInt->lang("automation", "fifthrenewalinfo");
echo " (";
echo $aInt->lang("automation", "todisable");
echo ")</td></tr>
</table>
<p><b>";
echo $aInt->lang("automation", "ticketsettings");
echo "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "inactivetickets");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"closeinactivetickets\" value=\"";
echo $CONFIG['CloseInactiveTickets'];
echo "\" size=3> ";
echo $aInt->lang("automation", "inactiveticketsinfo");
echo " (";
echo $aInt->lang("automation", "todisable");
echo ")</td></tr>
</table>
<p><b>";
echo $aInt->lang("automation", "misc");
echo "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "cancellation");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"autocancellationrequests\"";

if ($CONFIG['AutoCancellationRequests'] == "on") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "cancellationinfo");
echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "usage");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"updatestatusauto\"";

if ($CONFIG['UpdateStatsAuto'] == "on") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "usageinfo");
echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("automation", "autostatuschange");
echo "</td><td class=\"fieldarea\"><label><input type=\"radio\" name=\"autoclientstatuschange\" value=\"1\" ";

if ($CONFIG['AutoClientStatusChange'] == "1") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "disableautoinactiveinfo");
echo "</label> <br /><label><input type=\"radio\" name=\"autoclientstatuschange\" value=\"2\" ";

if ($CONFIG['AutoClientStatusChange'] == "2") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "defaultstatusautochange");
echo "</label> <br /><label><input type=\"radio\" name=\"autoclientstatuschange\" value=\"3\" ";

if ($CONFIG['AutoClientStatusChange'] == "3") {
	echo " CHECKED";
}

echo "> ";
echo $aInt->lang("automation", "setdaysforinactiveinfo");
echo "</label></td></tr>
</table>
<P ALIGN=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("global", "savechanges");
echo "\" class=\"button\"></P>
</form>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->display();
?>