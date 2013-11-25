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

define("CLIENTAREA", true);
require "init.php";
require "includes/whoisfunctions.php";
$domain = $whmcs->get_req_var("domain");

if ((!isset($_SESSION['domaincheckerwhois']) || !is_array($_SESSION['domaincheckerwhois'])) || !in_array($domain, $_SESSION['domaincheckerwhois'])) {
	exit("You must use the domain checker to get here");
}

include "includes/smarty/Smarty.class.php";
$smarty = new Smarty();
$smarty->template_dir = "templates/" . $whmcs->get_sys_tpl_name() . "/";
$smarty->compile_dir = $templates_compiledir;
$smarty->assign("template", $whmcs->get_sys_tpl_name());
$smarty->assign("LANG", $_LANG);
$smarty->assign("logo", $CONFIG['LogoURL']);
$smarty->assign("currency", $CONFIG['Currency']);
$smarty->assign("currencysymbol", $CONFIG['CurrencySymbol']);
$smarty->assign("companyname", $CONFIG['CompanyName']);
$smarty->assign("pagetitle", "WHOIS Results");
$domainparts = explode(".", $domain, 2);
$sld = $domainparts[0];
$tld = "." . $domainparts[1];
$result = lookupDomain($sld, $tld);
$smarty->assign("domain", $domain);
$smarty->assign("whois", $result['whois']);
$template_output = $smarty->fetch("whois.tpl");
echo $template_output;
?>