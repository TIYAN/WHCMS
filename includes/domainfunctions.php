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

function getTLDList($type = "register") {
	global $currency;

	$currency_id = $currency['id'];
	$clientgroupid = (isset($_SESSION['uid']) ? get_query_val("tblclients", "groupid", array("id" => $_SESSION['uid'])) : "0");

	if (!$clientgroupid) {
		$clientgroupid = 0;
	}

	$checkfields = array("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "tsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially", "triennially");
	$extensions = array();
	$result = select_query("tbldomainpricing", "id,extension", "", "order", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$extension = $data['extension'];
		$wherequery = "";
		$pricinggroup = $clientgroupid;
		$data = get_query_vals("tblpricing", "", array("type" => "domainregister", "currency" => $currency_id, "relid" => $id, "tsetupfee" => $clientgroupid));

		if (!$data) {
			$pricinggroup = "0";
			$data = get_query_vals("tblpricing", "", array("type" => "domainregister", "currency" => $currency_id, "relid" => $id, "tsetupfee" => "0"));
		}

		$i = 0;

		if (is_array($data)) {
			foreach ($data as $k => $v) {

				if (is_integer($k) && 3 < $k) {
					if (0 < $v) {
						if ($checkfields[$i]) {
							$wherequery .= $checkfields[$i] . ">=0 OR ";
						}
					}

					++$i;
					continue;
				}
			}
		}


		if ($wherequery) {
			$result2 = select_query("tblpricing", "COUNT(*)", "type='domain" . $type . ("' AND currency='" . $currency_id . "' AND relid='" . $id . "' AND tsetupfee=" . $pricinggroup . " AND (") . substr($wherequery, 0, 0 - 4) . ")");
			$data = mysql_fetch_array($result2);

			if ($data[0]) {
				$extensions[] = $extension;
			}
		}
	}

	return $extensions;
}

function getTLDPriceList($tld, $display = "", $renewpricing = "", $userid = "") {
	global $currency;

	if ($renewpricing == "renew") {
		$renewpricing = true;
	}

	$currency_id = $currency['id'];
	$result = select_query("tbldomainpricing", "id", array("extension" => $tld));
	$data = mysql_fetch_array($result);
	$id = $data['id'];

	if (!$userid && isset($_SESSION['uid'])) {
		$userid = $_SESSION['uid'];
	}

	$clientgroupid = ($userid ? get_query_val("tblclients", "groupid", array("id" => $userid)) : "0");
	$checkfields = array("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially");

	if (!$renewpricing || $renewpricing === "transfer") {
		$data = get_query_vals("tblpricing", "", array("type" => "domainregister", "currency" => $currency_id, "relid" => $id, "tsetupfee" => $clientgroupid));

		if (!$data) {
			$data = get_query_vals("tblpricing", "", array("type" => "domainregister", "currency" => $currency_id, "relid" => $id, "tsetupfee" => "0"));
		}

		foreach ($checkfields as $k => $v) {
			$register[$k + 1] = $data[$v];
		}

		$data = get_query_vals("tblpricing", "", array("type" => "domaintransfer", "currency" => $currency_id, "relid" => $id, "tsetupfee" => $clientgroupid));

		if (!$data) {
			$data = get_query_vals("tblpricing", "", array("type" => "domaintransfer", "currency" => $currency_id, "relid" => $id, "tsetupfee" => "0"));
		}

		foreach ($checkfields as $k => $v) {
			$transfer[$k + 1] = $data[$v];
		}
	}


	if (!$renewpricing || $renewpricing !== "transfer") {
		$data = get_query_vals("tblpricing", "", array("type" => "domainrenew", "currency" => $currency_id, "relid" => $id, "tsetupfee" => $clientgroupid));

		if (!$data) {
			$data = get_query_vals("tblpricing", "", array("type" => "domainrenew", "currency" => $currency_id, "relid" => $id, "tsetupfee" => "0"));
		}

		foreach ($checkfields as $k => $v) {
			$renew[$k + 1] = $data[$v];
		}
	}

	$tldpricing = array();
	$years = 1;

	while ($years <= 10) {
		if ($renewpricing === "transfer") {
			if (0 < $register[$years] && 0 <= $transfer[$years]) {
				if ($display) {
					$transfer[$years] = formatCurrency($transfer[$years]);
				}

				$tldpricing[$years]['transfer'] = $transfer[$years];
			}
		}
		else {
			if ($renewpricing) {
				if (0 < $renew[$years]) {
					if ($display) {
						$renew[$years] = formatCurrency($renew[$years]);
					}

					$tldpricing[$years]['renew'] = $renew[$years];
				}
			}
			else {
				if (0 < $register[$years]) {
					if ($display) {
						$register[$years] = formatCurrency($register[$years]);
					}

					$tldpricing[$years]['register'] = $register[$years];

					if (0 <= $transfer[$years]) {
						if ($display) {
							$transfer[$years] = formatCurrency($transfer[$years]);
						}

						$tldpricing[$years]['transfer'] = $transfer[$years];
					}


					if (0 < $renew[$years]) {
						if ($display) {
							$renew[$years] = formatCurrency($renew[$years]);
						}

						$tldpricing[$years]['renew'] = $renew[$years];
					}
				}
			}
		}

		$years += 1;
	}

	return $tldpricing;
}

function cleanDomainInput($val) {
	global $CONFIG;

	$val = trim($val);

	if (!$CONFIG['AllowIDNDomains']) {
		$val = strtolower($val);
	}

	return $val;
}

function checkDomainisValid($sld, $tld) {
	global $CONFIG;

	if ($sld[0] == "-" || $sld[strlen($sld) - 1] == "-") {
		return 0;
	}

	$isidn = $isidntld = false;

	if ($CONFIG['AllowIDNDomains']) {
		if (!class_exists("idnhandler")) {
			require ROOTDIR . "/whoisfunctions.php";
		}

		$idnconv = new idnhandler();
		$idnconv->encode($sld);

		if ($idnconv->get_last_error() && $idnconv->get_last_error() != "The given string does not contain encodable chars") {
			return 0;
		}


		if ($idnconv->get_last_error() && $idnconv->get_last_error() == "The given string does not contain encodable chars") {
			$CONFIG['AllowIDNDomains'] = "";
		}
		else {
			$isidn = true;
		}
	}


	if ($isidn === FALSE) {
		if (preg_replace('/[^.%$^\'#~@&*(),_£?!+=:{}[]()|\/ \\ ]/', '', $sld)) {
			return 0;
		}


		if (!$CONFIG['AllowIDNDomains'] && preg_replace("/[^a-z0-9-.]/i", "", $sld . $tld) != $sld . $tld) {
			return 0;
		}


		if (preg_replace("/[^a-z0-9-.]/", "", $tld) != $tld) {
			return 0;
		}

		$validmask = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-";

		if (strspn($sld, $validmask) != strlen($sld)) {
			return 0;
		}
	}

	run_hook("DomainValidation", array("sld" => $sld, "tld" => $tld));

	if (($sld === false && $sld !== 0) || !$tld) {
		return 0;
	}

	$coretlds = array(".com", ".net", ".org", ".info", "biz", ".mobi", ".name", ".asia", ".tel", ".in", ".mn", ".bz", ".cc", ".tv", ".us", ".me", ".co.uk", ".me.uk", ".org.uk", ".net.uk", ".ch", ".li", ".de", ".jp");
	$DomainMinLengthRestrictions = $DomainMaxLengthRestrictions = array();
	require ROOTDIR . "/configuration.php";
	foreach ($coretlds as $ctld) {

		if (!array_key_exists($ctld, $DomainMinLengthRestrictions)) {
			$DomainMinLengthRestrictions[$ctld] = 3;
		}


		if (!array_key_exists($ctld, $DomainMaxLengthRestrictions)) {
			$DomainMaxLengthRestrictions[$ctld] = 63;
			continue;
		}
	}


	if (array_key_exists($tld, $DomainMinLengthRestrictions) && strlen($sld) < $DomainMinLengthRestrictions[$tld]) {
		return 0;
	}


	if (array_key_exists($tld, $DomainMaxLengthRestrictions) && $DomainMaxLengthRestrictions[$tld] < strlen($sld)) {
		return 0;
	}

	return 1;
}

function disableAutoRenew($domainid) {
	update_query("tbldomains", array("donotrenew" => "on"), array("id" => $domainid));
	$domainname = get_query_val("tbldomains", "domain", array("id" => $domainid));

	if ($_SESSION['adminid']) {
		logActivity("Admin Disabled Domain Auto Renew - Domain ID: " . $domainid . " - Domain: " . $domainname);
	}
	else {
		logActivity("Client Disabled Domain Auto Renew - Domain ID: " . $domainid . " - Domain: " . $domainname);
	}

	$result = select_query("tblinvoiceitems", "tblinvoiceitems.id,tblinvoiceitems.invoiceid", array("type" => "Domain", "relid" => $domainid, "status" => "Unpaid", "tblinvoices.userid" => $_SESSION['uid']), "", "", "", "tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid");

	while ($data = mysql_fetch_array($result)) {
		$itemid = $data['id'];
		$invoiceid = $data['invoiceid'];
		$result2 = select_query("tblinvoiceitems", "COUNT(*)", array("invoiceid" => $invoiceid));
		$data = mysql_fetch_array($result2);
		$itemcount = $data[0];
		$otheritemcount = 0;

		if (1 < $itemcount) {
			$otheritemcount = get_query_val("tblinvoiceitems", "COUNT(*)", "invoiceid=" . (int)$invoiceid . (" AND id!=" . $itemid . " AND type NOT IN ('PromoHosting','PromoDomain','GroupDiscount')"));
		}


		if ($itemcount == 1 || $otheritemcount == 0) {
			update_query("tblinvoices", array("status" => "Cancelled"), array("id" => $invoiceid));
			logActivity("Cancelled Previous Domain Renewal Invoice - Invoice ID: " . $invoiceid . " - Domain: " . $domainname);
			run_hook("InvoiceCancelled", array("invoiceid" => $invoiceid));
		}

		delete_query("tblinvoiceitems", array("id" => $itemid));
		updateInvoiceTotal($invoiceid);
		logActivity("Removed Previous Domain Renewal Line Item - Invoice ID: " . $invoiceid . " - Domain: " . $domainname);
	}

}

?>