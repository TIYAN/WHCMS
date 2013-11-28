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

define("CLIENTAREA", true);
require "init.php";
require "includes/domainfunctions.php";
require "includes/whoisfunctions.php";
$capatacha = clientAreaInitCaptcha();
$pagetitle = $_LANG['domaintitle'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"domainchecker.php\">" . $_LANG['domaintitle'] . "</a>";
$templatefile = "domainchecker";
$pageicon = "images/domains_big.gif";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);
$search = $whmcs->get_req_var("search");
$domain = $whmcs->get_req_var("domain");
$bulkdomains = $whmcs->get_req_var("bulkdomains");
$tld = $whmcs->get_req_var("tld");
$tlds = $whmcs->get_req_var("tlds");
$ext = $whmcs->get_req_var("ext");
$direct = $whmcs->get_req_var("direct");
$sld = "";
$invalidtld = "";
$availabilityresults = array();
$search_tlds = array();
$tldslist = array();
$userid = (isset($_SESSION['uid']) ? $_SESSION['uid'] : "");
$currencyid = (isset($_SESSION['currency']) ? $_SESSION['currency'] : "");
$currency = getCurrency($userid, $currencyid);
$smartyvalues['currency'] = $currency;

if ($whmcs->get_config("BulkDomainSearchEnabled")) {
	$smartyvalues['bulkdomainsearchenabled'] = true;
}
else {
	$search = "";
}

$_SESSION['domaincheckerwhois'] = array();
$tldslist2 = getTLDList();
foreach ($tldslist2 as $k => $v) {
	$tldslist[$k + 1] = $v;
}


if (($search == "bulk" || $search == "bulkregister") || $search == "bulktransfer") {
	if ($search == "bulktransfer") {
		$templatefile = "bulkdomaintransfer";
		$getpricesfor = "transfer";
	}
	else {
		$templatefile = "bulkdomainchecker";
		$getpricesfor = "register";
	}

	$smartyvalues['bulk'] = true;
	$bulkdomains = strtolower($bulkdomains);
	$smartyvalues['bulkdomains'] = $bulkdomains;

	if ($bulkdomains) {
		check_token("domainchecker");
		$validate = new WHMCS_Validate();

		if ($capatacha) {
			$validate->validate("captcha", "code", "captchaverifyincorrect");
		}


		if ($validate->hasErrors()) {
			$smartyvalues['inccode'] = true;
			$bulkdomains = false;
		}


		if ($bulkdomains) {
			$bulkdomains = explode("
", $bulkdomains);

			$domaincount = 0;
			foreach ($bulkdomains as $domain) {
				$domainarray = explode(".", $domain, 2);
				$sld = $domainarray[0];
				$tld = "." . $domainarray[1];
				$sld = cleanDomainInput($sld);
				$tld = cleanDomainInput($tld);

				if ($domaincount < 20) {
					if (in_array($tld, $tldslist) && checkDomainisValid($sld, $tld)) {
						$_SESSION['domaincheckerwhois'][] = $sld . $tld;
						$result = lookupDomain($sld, $tld);

						if ($result['result'] != "error") {
							$tlddata = getTLDPriceList($tld, $getpricesfor);
							$availabilityresults[] = array("domain" => $sld . $tld, "status" => $result['result'], "regoptions" => $tlddata);
						}
					}
					else {
						$smartyvalues['invalid'] = true;
					}
				}

				++$domaincount;
			}
		}
	}
}
else {
	if (strpos($domain, ".")) {
		$domainparts = explode(".", $domain, 2);
		$sld = $domainparts[0];
		$dompart_tld = "." . $domainparts[1];
	}
	else {
		$sld = cleanDomainInput($domain);
		$dompart_tld = "";
	}


	if ($dompart_tld) {
		$search_tlds[] = cleanDomainInput($dompart_tld);
	}


	if ($tld) {
		$search_tlds[] = cleanDomainInput($tld);
	}


	if ($ext) {
		$search_tlds[] = cleanDomainInput($ext);
	}


	if (is_array($tlds)) {
		foreach ($tlds as $tld) {

			if (!in_array($tld, $search_tlds)) {
				$search_tlds[] = cleanDomainInput($tld);
				continue;
			}
		}
	}

	foreach ($search_tlds as $k => $temptld) {

		if (!in_array($temptld, $tldslist)) {
			$invalidtld = $temptld;
			unset($search_tlds[$k]);
			continue;
		}
	}

	$checkdomain = false;

	if ($sld && count($search_tlds)) {
		$checkdomain = true;
	}

	$validate = new WHMCS_Validate();

	if ($capatacha) {
		$validate->validate("captcha", "code", "captchaverifyincorrect");
	}


	if ((!$direct && $sld) && $validate->hasErrors()) {
		$smartyvalues['inccode'] = true;
		$checkdomain = false;
	}


	if ($whmcs->get_req_var("transfer")) {
		if ($domain != $_LANG['domaincheckerdomainexample']) {
			redir("a=add&domain=transfer&sld=" . $sld . "&tld=" . $search_tlds[0], "cart.php");
		}
		else {
			redir("a=add&domain=transfer", "cart.php");
		}
	}


	if ($whmcs->get_req_var("hosting")) {
		if ($domain != $_LANG['domaincheckerdomainexample']) {
			redir("sld=" . $sld . "&tld=" . $search_tlds[0], "cart.php");
		}
		else {
			redir("", "cart.php");
		}
	}

	$smartyvalues['domain'] = $domain;
	$smartyvalues['sld'] = $sld;
	$smartyvalues['ext'] = $smartyvalues['tld'] = (0 < count($search_tlds) ? $search_tlds[0] : "");
	$smartyvalues['tlds'] = $search_tlds;
	$smartyvalues['tldslist'] = $tldslist;
	$smartyvalues['invalidtld'] = $invalidtld;

	if ($checkdomain) {
		check_token("WHMCS.domainchecker");
		$smartyvalues['lookup'] = true;

		if (!checkDomainisValid($sld, $search_tlds[0])) {
			$smartyvalues['invalid'] = true;
		}
		else {
			$count = 0;

			if (count($search_tlds)) {
				foreach ($search_tlds as $tld) {
					$result = lookupDomain($sld, $tld);
					$_SESSION['domaincheckerwhois'][] = $sld . $tld;

					if (!$count) {
						if ($result['result'] == "available") {
							$smartyvalues['available'] = true;
						}
						else {
							if ($result['result'] == "error") {
								$smartyvalues['error'] = true;
							}
						}
					}

					$tlddata = getTLDPriceList($tld, true);
					$availabilityresults[] = array("domain" => $sld . $tld, "status" => $result['result'], "regoptions" => $tlddata, "errordetail" => $result['errordetail']);
					++$count;
				}
			}
		}
	}
}

$smartyvalues['availabilityresults'] = $availabilityresults;
$tldpricelist = array();

if ($tldslist) {
	foreach ($tldslist as $sel_tld) {
		$tldpricing = getTLDPriceList($sel_tld, true);
		$firstoption = current($tldpricing);
		$year = key($tldpricing);
		$tldpricelist[] = array("tld" => $sel_tld, "period" => $year, "register" => $firstoption['register'], "transfer" => $firstoption['transfer'], "renew" => $firstoption['renew']);
	}
}

$smartyvalues['tldpricelist'] = $tldpricelist;
$smartyvalues['capatacha'] = $capatacha;
$smartyvalues['recapatchahtml'] = clientAreaReCaptchaHTML();
outputClientArea($templatefile);
?>