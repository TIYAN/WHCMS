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

function cartPreventDuplicateProduct($domain) {
	if (!$domain) {
		return true;
	}

	$domains = array();
	foreach ($_SESSION['cart']['products'] as $k => $values) {
		$domains[$k] = $values['domain'];
	}


	if (in_array($domain, $domains)) {
		$i = array_search($domain, $domains);
		unset($_SESSION['cart']['products'][$i]);
		$_SESSION['cart']['products'] = array_values($_SESSION['cart']['products']);
	}

}

function cartPreventDuplicateDomain($domain) {
	$domains = array();
	foreach ($_SESSION['cart']['domains'] as $k => $values) {
		$domains[$k] = $values['domain'];
	}


	if (in_array($domain, $domains)) {
		$i = array_search($domain, $domains);
		unset($_SESSION['cart']['domains'][$i]);
		$_SESSION['cart']['domains'] = array_values($_SESSION['cart']['domains']);
	}

}

function bundlesConvertBillingCycle($cycle) {
	return str_replace(array("-", " "), "", strtolower($cycle));
}

function bundlesStepCompleteRedirect($lastconfig) {
	$i = $lastconfig['i'];

	if ($lastconfig['type'] == "product" && !isset($_SESSION['cart']['products'][$i]['bnum'])) {
		return false;
	}


	if ($lastconfig['type'] == "domain" && !isset($_SESSION['cart']['domains'][$i]['bnum'])) {
		return false;
	}


	if (is_array($_SESSION['cart']['bundle'])) {
		$bnum = count($_SESSION['cart']['bundle']);
		--$bnum;
		$bundledata = $_SESSION['cart']['bundle'][$bnum];
		$bid = $bundledata['bid'];
		$step = $bundledata['step'];
		$complete = $bundledata['complete'];

		if (!$complete) {
			$data = get_query_vals("tblbundles", "", array("id" => $bid));
			$bid = $data['id'];
			$itemdata = $data['itemdata'];
			$itemdata = unserialize($itemdata);
			$step = $_SESSION['cart']['bundle'][$bnum]['step'] = $step + 1;
			$vals = $itemdata[$step];

			if (is_array($vals)) {
				if ($vals['type'] == "product") {
					$vals['bnum'] = $bnum;
					$vals['bitem'] = $step;
					$vals['billingcycle'] = bundlesConvertBillingCycle($vals['billingcycle']);
					$_SESSION['cart']['passedvariables'] = $vals;
					unset($_SESSION['cart']['lastconfigured']);
					redir("a=add&pid=" . $vals['pid']);
					return null;
				}


				if ($vals['type'] == "domain") {
					$vals['bnum'] = $bnum;
					$vals['bitem'] = $step;
					$_SESSION['cart']['passedvariables'] = $vals;
					unset($_SESSION['cart']['lastconfigured']);
					redir("a=add&domain=register");
					return null;
				}
			}
			else {
				$step = $_SESSION['cart']['bundle'][$bnum]['complete'] = 1;
			}
		}
	}

}

function bundlesValidateProductConfig($key, $billingcycle, $configoptions, $addons) {
	global $_LANG;

	$proddata = $_SESSION['cart']['products'][$key];

	if (!isset($proddata['bnum'])) {
		return false;
	}

	$bid = $_SESSION['cart']['bundle'][$proddata['bnum']]['bid'];

	if (!$bid) {
		return false;
	}

	$data = get_query_vals("tblbundles", "", array("id" => $bid));
	$itemdata = $data['itemdata'];
	$itemdata = unserialize($itemdata);
	$proditemdata = $itemdata[$proddata['bitem']];
	$errors = "";
	$productname = get_query_val("tblproducts", "name", array("id" => $proddata['pid']));

	if ($proditemdata['billingcycle'] && bundlesConvertBillingCycle($proditemdata['billingcycle']) != $billingcycle) {
		$errors .= "<li>" . sprintf($_LANG['bundlewarningproductcycle'], $proditemdata['billingcycle'], $productname);
	}

	foreach ($proditemdata['configoption'] as $cid => $opid) {

		if ($opid != $configoptions[$cid]) {
			$data = get_query_vals("tblproductconfigoptions", "optionname,optiontype,(SELECT optionname FROM tblproductconfigoptionssub WHERE id='" . (int)$opid . "') AS subopname", array("id" => $cid));

			if ($data['optiontype'] == 1 || $data['optiontype'] == 2) {
				$errors .= "<li>" . sprintf($_LANG['bundlewarningproductconfopreq'], $data['subopname'], $data['optionname']);
				continue;
			}


			if ($data['optiontype'] == 3) {
				if ($opid) {
					$errors .= "<li>" . sprintf($_LANG['bundlewarningproductconfopyesnoenable'], $data['optionname']);
					continue;
				}

				$errors .= "<li>" . sprintf($_LANG['bundlewarningproductconfopyesnodisable'], $data['optionname']);
				continue;
			}


			if ($data['optiontype'] == 4) {
				$errors .= "<li>" . sprintf($_LANG['bundlewarningproductconfopqtyreq'], $opid, $data['optionname']);
				continue;
			}

			continue;
		}
	}


	if ($proditemdata['addons']) {
		foreach ($proditemdata['addons'] as $addonid) {

			if (!in_array($addonid, $addons)) {
				$errors .= "<li>" . sprintf($_LANG['bundlewarningproductaddonreq'], get_query_val("tbladdons", "name", array("id" => $addonid)), $productname);
				continue;
			}
		}
	}

	return $errors;
}

function bundlesValidateCheckout() {
	global $_LANG;

	if (!isset($_SESSION['cart']['bundle'])) {
		return "";
	}

	$bundlesess = $_SESSION['cart']['bundle'];
	foreach ($bundlesess as $k => $v) {
		unset($bundlesess[$k]['warnings']);
	}

	$bundledata = $warnings = array();
	foreach ($bundlesess as $bnum => $vals) {
		$bid = $vals['bid'];
		$data = get_query_vals("tblbundles", "", array("id" => $bid));
		$allowpromo = $data['allowpromo'];
		$itemdata = $data['itemdata'];
		$itemdata = unserialize($itemdata);
		$bundledata[$bid] = $itemdata;

		if ($_SESSION['cart']['promo'] && !$allowpromo) {
			$warnings[] = $_LANG['bundlewarningpromo'];
			$bundlesess[$bnum]['warnings'] = 1;
			continue;
		}
	}

	$numitemsperbundle = $productbundleddomains = $domainsincart = array();
	foreach ($_SESSION['cart']['domains'] as $k => $values) {
		$domainsincart[$values['domain']] = $k;
	}

	foreach ($_SESSION['cart']['products'] as $k => $v) {

		if (isset($v['bnum'])) {
			$bnum = $v['bnum'];
			$bitem = $v['bitem'];
			$pid = $v['pid'];
			$domain = $v['domain'];
			$billingcycle = $v['billingcycle'];
			$configoptions = $v['configoptions'];
			$addons = $v['addons'];
			$bid = $_SESSION['cart']['bundle'][$bnum]['bid'];
			$itemdata = $bundledata[$bid][$bitem];

			if ($itemdata['type'] != "product" || $pid != $itemdata['pid']) {
				unset($_SESSION['cart']['products'][$k]['bnum']);
				unset($_SESSION['cart']['products'][$k]['bitem']);
				continue;
			}

			++$numitemsperbundle[$bnum];
			$productname = get_query_val("tblproducts", "name", array("id" => $pid));

			if ($itemdata['billingcycle'] && bundlesConvertBillingCycle($itemdata['billingcycle']) != $billingcycle) {
				$warnings[] = sprintf($_LANG['bundlewarningproductcycle'], $itemdata['billingcycle'], $productname);
				$bundlesess[$bnum]['warnings'] = 1;
			}

			foreach ($itemdata['configoption'] as $cid => $opid) {

				if ($opid != $configoptions[$cid]) {
					$data = get_query_vals("tblproductconfigoptions", "optionname,optiontype,(SELECT optionname FROM tblproductconfigoptionssub WHERE id='" . (int)$opid . "') AS subopname", array("id" => $cid));

					if ($data['optiontype'] == 1 || $data['optiontype'] == 2) {
						$warnings[] = sprintf($_LANG['bundlewarningproductconfopreq'], $data['subopname'], $data['optionname']);
						$bundlesess[$bnum]['warnings'] = 1;
						continue;
					}


					if ($data['optiontype'] == 3) {
						if ($opid) {
							$warnings[] = sprintf($_LANG['bundlewarningproductconfopyesnoenable'], $data['optionname']);
						}
						else {
							$warnings[] = sprintf($_LANG['bundlewarningproductconfopyesnodisable'], $data['optionname']);
						}

						$bundlesess[$bnum]['warnings'] = 1;
						continue;
					}


					if ($data['optiontype'] == 4) {
						$warnings[] = sprintf($_LANG['bundlewarningproductconfopqtyreq'], $opid, $data['optionname']);
						$bundlesess[$bnum]['warnings'] = 1;
						continue;
					}

					continue;
				}
			}


			if ($itemdata['addons']) {
				foreach ($itemdata['addons'] as $addonid) {

					if (!in_array($addonid, $addons)) {
						$warnings[] = sprintf($_LANG['bundlewarningproductaddonreq'], get_query_val("tbladdons", "name", array("id" => $addonid)), $productname);
						$bundlesess[$bnum]['warnings'] = 1;
						continue;
					}
				}
			}


			if (array_key_exists($domain, $domainsincart)) {
				$domid = $domainsincart[$domain];
				$v = $_SESSION['cart']['domains'][$domid];
				$regperiod = $v['regperiod'];

				if (is_array($itemdata['tlds'])) {
					$domaintld = explode(".", $domain, 2);
					$domaintld = "." . $domaintld[1];

					if (!in_array($domaintld, $itemdata['tlds'])) {
						$warnings[] = sprintf($_LANG['bundlewarningdomaintld'], implode(",", $itemdata['tlds']), $domain);
						$bundlesess[$bnum]['warnings'] = 1;
					}
				}


				if ($itemdata['regperiod'] && $itemdata['regperiod'] != $regperiod) {
					$warnings[] = sprintf($_LANG['bundlewarningdomainregperiod'], $itemdata['regperiod'], $domain);
					$bundlesess[$bnum]['warnings'] = 1;
				}


				if (is_array($itemdata['domaddons'])) {
					foreach ($itemdata['domaddons'] as $domaddon) {

						if (!$v[$domaddon]) {
							$warnings[] = sprintf($_LANG['bundlewarningdomainaddon'], $_LANG["domain" . $domaddon], $domain);
							$bundlesess[$bnum]['warnings'] = 1;
							continue;
						}
					}
				}

				$productbundleddomains[$domain] = array($bnum, $bitem);
				continue;
			}


			if ((is_array($itemdata['tlds']) || $itemdata['regperiod']) || is_array($itemdata['domaddons'])) {
				$warnings[] = sprintf($_LANG['bundlewarningdomainreq'], $productname);
				$bundlesess[$bnum]['warnings'] = 1;
				continue;
			}

			continue;
		}
	}

	foreach ($_SESSION['cart']['domains'] as $k => $v) {

		if (isset($v['bnum'])) {
			$bnum = $v['bnum'];
			$bitem = $v['bitem'];
			$domain = $v['domain'];
			$regperiod = $v['regperiod'];
			$bid = $_SESSION['cart']['bundle'][$bnum]['bid'];
			$itemdata = $bundledata[$bid][$bitem];

			if ($itemdata['type'] != "domain") {
				unset($_SESSION['cart']['domains'][$k]['bnum']);
				unset($_SESSION['cart']['domains'][$k]['bitem']);
				continue;
			}

			++$numitemsperbundle[$bnum];

			if (is_array($itemdata['tlds'])) {
				$domaintld = explode(".", $domain, 2);
				$domaintld = "." . $domaintld[1];

				if (!in_array($domaintld, $itemdata['tlds'])) {
					$warnings[] = sprintf($_LANG['bundlewarningdomaintld'], implode(",", $itemdata['tlds']), $domain);
					$bundlesess[$bnum]['warnings'] = 1;
				}
			}


			if ($itemdata['regperiod'] && $itemdata['regperiod'] != $regperiod) {
				$warnings[] = sprintf($_LANG['bundlewarningdomainregperiod'], $itemdata['regperiod'], $domain);
				$bundlesess[$bnum]['warnings'] = 1;
			}


			if (is_array($itemdata['addons'])) {
				foreach ($itemdata['addons'] as $domaddon) {

					if (!$v[$domaddon]) {
						$warnings[] = sprintf($_LANG['bundlewarningdomainaddon'], $_LANG["domain" . $domaddon], $domain);
						$bundlesess[$bnum]['warnings'] = 1;
						continue;
					}
				}

				continue;
			}

			continue;
		}
	}

	foreach ($bundlesess as $bnum => $vals) {
		$bid = $vals['bid'];
		$bundletotalitems = count($bundledata[$bid]);

		if ($bundletotalitems != $numitemsperbundle[$bnum]) {
			unset($bundlesess[$bnum]);
			continue;
		}
	}

	$_SESSION['cart']['bundle'] = $bundlesess;
	$_SESSION['cart']['prodbundleddomains'] = $productbundleddomains;
	return $warnings;
}

function bundlesGetProductPriceOverride($type, $key) {
	global $currency;

	$proddata = $_SESSION['cart'][$type . "s"][$key];
	$prodbundleddomain = false;

	if (!isset($proddata['bnum']) && $type == "domain") {
		$domain = $proddata['domain'];

		if (is_array($_SESSION['cart']['prodbundleddomains'][$domain])) {
			$proddata['bnum'] = $_SESSION['cart']['prodbundleddomains'][$domain][0];
			$proddata['bitem'] = $_SESSION['cart']['prodbundleddomains'][$domain][1];
		}
	}


	if (!isset($proddata['bnum'])) {
		return false;
	}

	$bid = $_SESSION['cart']['bundle'][$proddata['bnum']]['bid'];

	if (!$bid) {
		return false;
	}

	$bundlewarnings = $_SESSION['cart']['bundle'][$proddata['bnum']]['warnings'];

	if ($bundlewarnings) {
		return false;
	}

	$data = get_query_vals("tblbundles", "", array("id" => $bid));
	$itemdata = $data['itemdata'];
	$itemdata = unserialize($itemdata);

	if ($type == "product" && $itemdata[$proddata['bitem']]['priceoverride']) {
		return convertCurrency($itemdata[$proddata['bitem']]['price'], 1, $currency['id']);
	}


	if ($type == "domain" && $itemdata[$proddata['bitem']]['dompriceoverride']) {
		return convertCurrency($itemdata[$proddata['bitem']]['domprice'], 1, $currency['id']);
	}

	return false;
}

?>