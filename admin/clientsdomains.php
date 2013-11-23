<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View Clients Domains", false);
$aInt->requiredFiles(array("clientfunctions", "domainfunctions", "gatewayfunctions", "registrarfunctions"));
$aInt->inClientsProfile = true;

if (!$id && $domainid) {
	$id = $domainid;
}


if (!$userid && !$id) {
	$userid = get_query_val("tblclients", "id", "", "id", "ASC", "0,1");
}


if ($userid && !$id) {
	$aInt->valUserID($userid);

	if (!$userid) {
		$aInt->gracefulExit("Invalid User ID");
	}

	$id = get_query_val("tbldomains", "id", array("userid" => $userid), "domain", "ASC", "0,1");
}


if (!$id) {
	$aInt->gracefulExit($aInt->lang("domains", "nodomainsinfo") . " <a href=\"ordersadd.php?userid=" . $userid . "\">" . $aInt->lang("global", "clickhere") . "</a> " . $aInt->lang("orders", "toplacenew"));
}

$domains = new WHMCS_Domains();
$domain_data = $domains->getDomainsDatabyID($id);
$id = $did = $domainid = $domain_data['id'];
$userid = $domain_data['userid'];
$aInt->valUserID($userid);

if (!$id) {
	$aInt->gracefulExit("Domain ID Not Found");
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	checkPermission("Delete Clients Domains");
	run_hook("DomainDelete", array("userid" => $userid, "domainid" => $id));
	delete_query("tbldomains", array("id" => $id));
	logActivity("Deleted Domain - User ID: " . $userid . " - Domain ID: " . $id);
	header("Location: " . $_SERVER['PHP_SELF'] . ("?userid=" . $userid));
	exit();
}

include ROOTDIR . "/includes/additionaldomainfields.php";

if ($action == "savedomain" && $domain) {
	check_token("WHMCS.admin.default");
	checkPermission("Edit Clients Domains");
	$conf = "success";
	$currency = getCurrency($userid);
	$result = select_query("tblpricing", "msetupfee,qsetupfee,ssetupfee", array("type" => "domainaddons", "currency" => $currency['id'], "relid" => 0));
	$data = mysql_fetch_array($result);
	$domaindnsmanagementprice = $data['msetupfee'] * $regperiod;
	$domainemailforwardingprice = $data['qsetupfee'] * $regperiod;
	$domainidprotectionprice = $data['ssetupfee'] * $regperiod;
	$result = select_query("tbldomains", "dnsmanagement,emailforwarding,idprotection,donotrenew", array("id" => $id));
	$data = mysql_fetch_array($result);
	$olddnsmanagement = $data['dnsmanagement'];
	$oldemailforwarding = $data['emailforwarding'];
	$oldidprotection = $data['idprotection'];
	$olddonotrenew = $data['donotrenew'];

	if ($olddnsmanagement) {
		if (!$dnsmanagement) {
			$recurringamount -= $domaindnsmanagementprice;
			$conf = "removeddns";
		}
	}
	else {
		if ($dnsmanagement) {
			$recurringamount += $domaindnsmanagementprice;
			$conf = "addeddns";
		}
	}


	if ($oldemailforwarding) {
		if (!$emailforwarding) {
			$recurringamount -= $domainemailforwardingprice;
			$conf = "removedemailforward";
		}
	}
	else {
		if ($emailforwarding) {
			$recurringamount += $domainemailforwardingprice;
			$conf = "addedemailforward";
		}
	}


	if ($oldidprotection) {
		if (!$idprotection) {
			$recurringamount -= $domainidprotectionprice;
			$conf = "removedidprotect";
		}
	}
	else {
		if ($idprotection) {
			$recurringamount += $domainidprotectionprice;
			$conf = "addedidprotect";
		}
	}


	if ($autorecalc) {
		$domainparts = explode(".", $domain, 2);
		$temppricelist = getTLDPriceList("." . $domainparts[1], "", true, $userid);
		$recurringamount = $temppricelist[$regperiod]['renew'];

		if ($dnsmanagement) {
			$recurringamount += $domaindnsmanagementprice;
		}


		if ($emailforwarding) {
			$recurringamount += $domainemailforwardingprice;
		}


		if ($idprotection) {
			$recurringamount += $domainidprotectionprice;
		}


		if ($promoid) {
			$recurringamount -= recalcPromoAmount("D." . $domainparts[1], $userid, $id, $regperiod . "Years", $recurringamount, $promoid);
		}
	}


	if (!$olddonotrenew && $donotrenew) {
		disableAutoRenew($id);
	}

	$table = "tbldomains";
	$array = array("registrationdate" => toMySQLDate($regdate), "domain" => $domain, "firstpaymentamount" => $firstpaymentamount, "recurringamount" => $recurringamount, "paymentmethod" => $paymentmethod, "registrar" => $registrar, "registrationperiod" => $regperiod, "expirydate" => toMySQLDate($expirydate), "nextduedate" => toMySQLDate($nextduedate), "subscriptionid" => $subscriptionid, "promoid" => $promoid, "additionalnotes" => $additionalnotes, "status" => $status, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection, "donotrenew" => $donotrenew);

	if ($oldnextduedate != $nextduedate) {
		$array['nextinvoicedate'] = toMySQLDate($nextduedate);
	}

	$where = array("id" => $id);
	update_query($table, $array, $where);
	logActivity("Domain Modified - User ID: " . $userid . " - Domain ID: " . $id, $userid);

	if ($additionaldomainfield) {
		$domainparts = explode(".", $domain, 2);
		$tld = "." . $domainparts[1];
		$tempdomainfields = $additionaldomainfields[$tld];
		foreach ($tempdomainfields as $key => $value) {
			$key = $value['Name'];
			$value = $additionaldomainfield[$key];
			$table = "tbldomainsadditionalfields";
			$where = array("domainid" => $id, "name" => $key);
			$result = select_query($table, "COUNT(*)", $where);
			$data = mysql_fetch_array($result);

			if (!$data[0]) {
				insert_query($table, $where);
			}

			$array = array("value" => $value);
			update_query($table, $array, $where);
		}
	}

	loadRegistrarModule($registrar);

	if (function_exists($registrar . "_AdminDomainsTabFieldsSave")) {
		$domainparts = explode(".", $domain, 2);
		$params = array();
		$params['domainid'] = $id;
		$params['sld'] = $domainparts[0];
		$params['tld'] = $domainparts[1];
		$params['regperiod'] = $regperiod;
		$params['registrar'] = $registrar;
		$fieldsarray = call_user_func($registrar . "_AdminDomainsTabFieldsSave", $params);
	}

	$newlockstatus = ($lockstatus ? "locked" : "unlocked");
	run_hook("AdminClientDomainsTabFieldsSave", $_REQUEST);
	run_hook("DomainEdit", array("userid" => $userid, "domainid" => $id));
	$_SESSION['domainsavetemp'] = array("ns1" => $ns1, "ns2" => $ns2, "ns3" => $ns3, "ns4" => $ns4, "ns5" => $ns5, "oldns1" => $oldns1, "oldns2" => $oldns2, "oldns3" => $oldns3, "oldns4" => $oldns4, "oldns5" => $oldns5, "defaultns" => $defaultns, "newlockstatus" => $newlockstatus, "oldlockstatus" => $oldlockstatus);
	header("Location: clientsdomains.php?userid=" . $userid . "&id=" . $id . "&conf=" . $conf);
	exit();
}


if (!$id) {
	$result = select_query("tbldomains", "id", array("userid" => $userid), "domain", "ASC", "0,1");
	$data = mysql_fetch_array($result);
	$id = $data['id'];
}

ob_start();
$did = $domain_data['id'];
$orderid = $domain_data['orderid'];
$ordertype = $domain_data['type'];
$domain = $domain_data['domain'];
$paymentmethod = $domain_data['paymentmethod'];
$firstpaymentamount = $domain_data['firstpaymentamount'];
$recurringamount = $domain_data['recurringamount'];
$registrar = $domain_data['registrar'];
$regtype = $domain_data['type'];
$expirydate = $domain_data['expirydate'];
$nextduedate = $domain_data['nextduedate'];
$subscriptionid = $domain_data['subscriptionid'];
$promoid = $domain_data['promoid'];
$registrationdate = $domain_data['registrationdate'];
$registrationperiod = $domain_data['registrationperiod'];
$domainstatus = $domain_data['status'];
$additionalnotes = $domain_data['additionalnotes'];
$dnsmanagement = $domain_data['dnsmanagement'];
$emailforwarding = $domain_data['emailforwarding'];
$idprotection = $domain_data['idprotection'];
$donotrenew = $domain_data['donotrenew'];

if (!$did) {
	$aInt->gracefulExit($aInt->lang("domains", "domainidnotfound"));
}

$expirydate = fromMySQLDate($expirydate);
$nextduedate = fromMySQLDate($nextduedate);
$regdate = fromMySQLDate($registrationdate);
echo $aInt->jqueryDialog("renew", $aInt->lang("domains", "renewdomain"), $aInt->lang("domains", "renewdomainq"), array($aInt->lang("global", "yes") => "window.location='" . $PHP_SELF . "?userid=" . $userid . "&id=" . $id . "&regaction=renew'", $aInt->lang("global", "no") => ""));
echo $aInt->jqueryDialog("getepp", $aInt->lang("domains", "requestepp"), $aInt->lang("domains", "requesteppq"), array($aInt->lang("global", "yes") => "window.location='" . $PHP_SELF . "?userid=" . $userid . "&id=" . $id . "&regaction=eppcode'", $aInt->lang("global", "no") => ""));
echo $aInt->jqueryDialog("reqdelete", $aInt->lang("domains", "requestdel"), $aInt->lang("domains", "requestdelq"), array($aInt->lang("global", "yes") => "window.location='" . $PHP_SELF . "?userid=" . $userid . "&id=" . $id . "&regaction=reqdelete'", $aInt->lang("global", "no") => ""));
echo $aInt->jqueryDialog("delete", $aInt->lang("domains", "delete"), $aInt->lang("domains", "deleteq"), array($aInt->lang("global", "yes") => "window.location='" . $PHP_SELF . "?userid=" . $userid . "&id=" . $id . "&action=delete" . generate_token("link") . "'", $aInt->lang("global", "no") => ""));
echo $aInt->jqueryDialog("reldomain", $aInt->lang("domains", "releasedomain"), $aInt->lang("domains", "releasedomainq") . "<br /><br />" . $aInt->lang("domains", "transfertag") . ": <input type=\"text\" id=\"transtag\" size=\"20\" />", array($aInt->lang("global", "submit") => "window.location='" . $PHP_SELF . "?userid=" . $userid . "&id=" . $id . "&regaction=release&transtag='+$(\"#transtag\").val();", $aInt->lang("global", "cancel") => ""));
echo $aInt->jqueryDialog("idprotectdomain", $aInt->lang("domains", "idprotection"), $aInt->lang("domains", "idprotectionq"), array($aInt->lang("global", "yes") => "window.location='" . $PHP_SELF . "?userid=" . $userid . "&id=" . $id . "&regaction=idtoggle'", $aInt->lang("global", "no") => ""));

if ($conf) {
	$ns1 = $_SESSION['domainsavetemp']['ns1'];
	$ns2 = $_SESSION['domainsavetemp']['ns2'];
	$ns3 = $_SESSION['domainsavetemp']['ns3'];
	$ns4 = $_SESSION['domainsavetemp']['ns4'];
	$ns5 = $_SESSION['domainsavetemp']['ns5'];
	$oldns1 = $_SESSION['domainsavetemp']['oldns1'];
	$oldns2 = $_SESSION['domainsavetemp']['oldns2'];
	$oldns3 = $_SESSION['domainsavetemp']['oldns3'];
	$oldns4 = $_SESSION['domainsavetemp']['oldns4'];
	$oldns5 = $_SESSION['domainsavetemp']['oldns5'];
	$defaultns = $_SESSION['domainsavetemp']['defaultns'];
	$newlockstatus = $_SESSION['domainsavetemp']['newlockstatus'];
	$oldlockstatus = $_SESSION['domainsavetemp']['oldlockstatus'];
	unset($_SESSION['domainsavetemp']);
}

releaseSession();

if ($conf == "success") {
	infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("global", "changesuccessdesc"));
}
else {
	if ($conf == "addeddns") {
		infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("domains", "dnsmanagementadded"));
	}
	else {
		if ($conf == "addedemailforward") {
			infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("domains", "emailforwardingadded"));
		}
		else {
			if ($conf == "addedidprotect") {
				infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("domains", "idprotectionadded"));
			}
			else {
				if ($conf == "removeddns") {
					infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("domains", "dnsmanagementremoved"));
				}
				else {
					if ($conf == "removedemailforward") {
						infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("domains", "emailforwardingremoved"));
					}
					else {
						if ($conf == "removedidprotect") {
							infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("domains", "idprotectionremoved"));
						}
					}
				}
			}
		}
	}
}

$domainregistraractions = ((checkPermission("Perform Registrar Operations", true) && $domains->getModule()) ? true : false);

if ($domainregistraractions) {
	$domainparts = explode(".", $domain, 2);
	$params = array();
	$params['domainid'] = $id;
	$params['sld'] = $domainparts[0];
	$params['tld'] = $domainparts[1];
	$params['regperiod'] = $registrationperiod;
	$params['registrar'] = $registrar;
	$params['regtype'] = $regtype;
	$adminbuttonarray = "";
	loadRegistrarModule($registrar);

	if (function_exists($registrar . "_AdminCustomButtonArray")) {
		$adminbuttonarray = call_user_func($registrar . "_AdminCustomButtonArray", $params);
	}


	if ((((($oldns1 != $ns1 || $oldns2 != $ns2) || $oldns3 != $ns3) || $oldns4 != $ns4) || $oldns5 != $ns5) || $defaultns) {
		$nameservers = ($defaultns ? $domains->getDefaultNameservers() : array("ns1" => $ns1, "ns2" => $ns2, "ns3" => $ns3, "ns4" => $ns4, "ns5" => $ns5));
		$success = $domains->moduleCall("SaveNameservers", $nameservers);

		if (!$success) {
			infoBox($aInt->lang("domains", "nschangefail"), $domains->getLastError(), "error");
		}
		else {
			infoBox($aInt->lang("domains", "nschangesuccess"), $aInt->lang("domains", "nschangeinfo"), "success");
		}
	}


	if (!$oldlockstatus) {
		$oldlockstatus = $newlockstatus;
	}


	if ($newlockstatus != $oldlockstatus) {
		$params['lockenabled'] = $newlockstatus;
		$values = RegSaveRegistrarLock($params);

		if ($values['error']) {
			infoBox($aInt->lang("domains", "reglockfailed"), $values['error'], "error");
		}
		else {
			infoBox($aInt->lang("domains", "reglocksuccess"), $aInt->lang("domains", "reglockinfo"), "success");
		}
	}


	if ($regaction == "renew") {
		$values = RegRenewDomain($params);
		wSetCookie("DomRenewRes", $values);
		header("Location: clientsdomains.php?userid=" . $userid . "&id=" . $id . "&conf=renew");
		exit();
	}


	if ($regaction == "eppcode") {
		$values = RegGetEPPCode($params);

		if ($values['error']) {
			infoBox($aInt->lang("domains", "eppfailed"), $values['error'], "error");
		}
		else {
			if ($values['eppcode']) {
				infoBox($aInt->lang("domains", "epprequest"), $_LANG['domaingeteppcodeis'] . " " . $values['eppcode']);
			}
			else {
				infoBox($aInt->lang("domains", "epprequest"), $_LANG['domaingeteppcodeemailconfirmation'], "success");
			}
		}
	}


	if ($regaction == "reqdelete") {
		$values = RegRequestDelete($params);

		if ($values['error']) {
			infoBox($aInt->lang("domains", "deletefailed"), $values['error'], "error");
		}
		else {
			infoBox($aInt->lang("domains", "deletesuccess"), $aInt->lang("domains", "deleteinfo"), "success");
		}
	}


	if ($regaction == "release") {
		$params['transfertag'] = $transtag;
		$values = RegReleaseDomain($params);
		$successmessage = str_replace("%s", $transtag, $aInt->lang("domains", "releaseinfo"));

		if ($values['error']) {
			infoBox($aInt->lang("domains", "releasefailed"), $values['error'], "error");
		}
		else {
			infoBox($aInt->lang("domains", "releasesuccess"), $successmessage);
		}
	}


	if ($regaction == "custom") {
		$values = RegCustomFunction($params, $ac);

		if ($values['error']) {
			infoBox($aInt->lang("domains", "registrarerror"), $values['error'], "error");
		}
		else {
			if (!$values['message']) {
				$values['message'] = $aInt->lang("domains", "changesuccess");
			}

			infoBox($aInt->lang("domains", "changesuccess"), $values['message'], "success");
		}
	}


	if ($conf == "addedidprotect" || $conf == "removedidprotect") {
		$values = RegIDProtectToggle($params);

		if (is_array($values)) {
			if ($values['error']) {
				infoBox($aInt->lang("domains", "idprotectfailed"), $values['error'], "error");
			}
			else {
				infoBox($aInt->lang("domains", "idprotectsuccess"), $aInt->lang("domains", "idprotectinfo"), "success");
			}
		}
	}

	$success = $domains->moduleCall("GetNameservers");

	if ($success) {
		$nsvalues = $domains->getModuleReturn();
	}
	else {
		if (!$infobox) {
			infoBox($aInt->lang("domains", "registrarerror"), $domains->getLastError());
		}
	}


	if ($conf == "renew") {
		$values = wGetCookie("DomRenewRes", 1);

		if ($values['error']) {
			infoBox($aInt->lang("domains", "renewfailed"), $values['error'], "error");
		}
		else {
			$successmessage = str_replace("%s", $registrationperiod, $aInt->lang("domains", "renewinfo"));
			infoBox($aInt->lang("domains", "renewsuccess"), $successmessage, "success");
		}
	}

	$success = $domains->moduleCall("GetRegistrarLock");

	if ($success) {
		$lockstatus = $domains->getModuleReturn();
	}
}

$clientnotes = array();
$result = select_query("tblnotes", "tblnotes.*,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE tbladmins.id=tblnotes.adminid) AS adminuser", array("userid" => $userid, "sticky" => "1"), "modified", "DESC");

while ($data = mysql_fetch_assoc($result)) {
	$data['created'] = fromMySQLDate($data['created'], 1);
	$data['modified'] = fromMySQLDate($data['modified'], 1);
	$data['note'] = autoHyperLink(nl2br($data['note']));
	$clientnotes[] = $data;
}


if (count($clientnotes)) {
	echo "<div id=\"clientsimportantnotes\">";
	foreach ($clientnotes as $data) {
		echo "<div class=\"ticketstaffnotes\">
    <table class=\"ticketstaffnotestable\">
        <tr>
            <td>" . $data['adminuser'] . "</td>
            <td align=\"right\">" . $data['modified'] . "</td>
        </tr>
    </table>
    <div>
        " . $data['note'] . "
        <div style=\"float:right;\"><a href=\"clientsnotes.php?userid=" . $userid . "&action=edit&id=" . $data['id'] . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" align=\"absmiddle\" /></a></div>
    </div>
</div>";
	}

	echo "</div>";
}

echo "
<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td>
<form action=\"";
echo $PHP_SELF;
echo "\" method=\"get\">
<input type=\"hidden\" name=\"userid\" value=\"";
echo $userid;
echo "\">
";
echo $aInt->lang("clientsummary", "domains");
echo ": ";
echo "<s";
echo "elect name=\"id\" onChange=\"submit();\">
";
$result = select_query("tbldomains", "", array("userid" => $userid), "domain", "ASC");

while ($data = mysql_fetch_array($result)) {
	$domainlistid = $data['id'];
	$domainlistname = $data['domain'];
	$domainliststatus = $data['status'];
	echo "<option value=\"" . $domainlistid . "\"";

	if ($domainlistid == $id) {
		echo " selected";
	}


	if ($domainliststatus == "Pending") {
		echo " style=\"background-color:#ffffcc;\"";
	}
	else {
		if (($domainliststatus == "Expired" || $domainliststatus == "Cancelled") || $domainliststatus == "Fraud") {
			echo " style=\"background-color:#ff9999;\"";
		}
	}

	echo ">" . $domainlistname . "</option>";
}

echo "</select> <input type=\"submit\" value=\"";
echo $aInt->lang("global", "go");
echo "\" class=\"btn btn-success\" />
</form>
</td><td align=\"right\">
<input type=\"button\" onClick=\"window.open('clientsmove.php?type=domain&id=";
echo $id;
echo "','movewindow','width=500,height=200,top=100,left=100');return false\" value=\"";
echo $aInt->lang("services", "moveservice");
echo "\" class=\"btn\" /> &nbsp;&nbsp;&nbsp;
</td></tr></table>

";
echo $infobox ? $infobox : "<img src=\"images/spacer.gif\" height=\"10\" width=\"1\" /><br />";
echo "
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?action=savedomain&userid=";
echo $userid;
echo "&id=";
echo $id;
echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "ordernum");
echo "</td><td class=\"fieldarea\">";
echo $orderid;
echo " - <a href=\"orders.php?action=view&id=";
echo $orderid;
echo "\">";
echo $aInt->lang("orders", "vieworder");
echo "</a></td><td class=\"fieldlabel\">";
echo $aInt->lang("domains", "regperiod");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"regperiod\" size=4 value=\"";
echo $registrationperiod;
echo "\"> ";
echo $aInt->lang("domains", "years");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("orders", "ordertype");
echo "</td><td class=\"fieldarea\">";
echo $ordertype;
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "regdate");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"regdate\" value=\"";
echo $regdate;
echo "\" class=\"datepick\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "domain");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"domain\" size=\"30\" value=\"";
echo $domain;
echo "\"> <a href=\"http://www.";
echo $domain;
echo "\" target=\"_blank\" style=\"color:#cc0000\">www</a> <a href=\"whois.php?domain=";
echo $domain;
echo "\" target=\"_blank\">";
echo $aInt->lang("domains", "whois");
echo "</a></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "expirydate");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"expirydate\" value=\"";
echo $expirydate;
echo "\" class=\"datepick\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "registrar");
echo "</td><td class=\"fieldarea\">";
echo getRegistrarsDropdownMenu($registrar);
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "nextduedate");
echo "</td><td class=\"fieldarea\"><input type=\"hidden\" name=\"oldnextduedate\" value=\"";
echo $nextduedate;
echo "\"><input type=\"text\" name=\"nextduedate\" value=\"";
echo $nextduedate;
echo "\" class=\"datepick\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "firstpaymentamount");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"firstpaymentamount\" size=10 value=\"";
echo $firstpaymentamount;
echo "\"></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "paymentmethod");
echo "</td><td class=\"fieldarea\">";
echo paymentMethodsSelection();
echo " <a href=\"clientsinvoices.php?userid=";
echo $userid;
echo "&domainid=";
echo $id;
echo "\">";
echo $aInt->lang("invoices", "viewinvoices");
echo "</a></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "recurringamount");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"recurringamount\" size=10 value=\"";
echo $recurringamount;
echo "\"> <label><input type=\"checkbox\" name=\"autorecalc\" ";

if ($autorecalcdefault) {
	echo " checked";
}

echo " /> ";
echo $aInt->lang("services", "autorecalc");
echo "</label></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "status");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"status\">
<option value=\"Pending\"";

if ($domainstatus == "Pending") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "pending");
echo "</option>
<option value=\"Pending Transfer\"";

if ($domainstatus == "Pending Transfer") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "pendingtransfer");
echo "</option>
<option value=\"Active\"";

if ($domainstatus == "Active") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "active");
echo "</option>
<option value=\"Expired\"";

if ($domainstatus == "Expired") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "expired");
echo "</option>
<option value=\"Cancelled\"";

if ($domainstatus == "Cancelled") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "cancelled");
echo "</option>
<option value=\"Fraud\"";

if ($domainstatus == "Fraud") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "fraud");
echo "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "promocode");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"promoid\" style=\"max-width:250px;\"><option value=\"0\">";
echo $aInt->lang("global", "none");
echo "</option>";
$currency = getCurrency($userid);
$result = select_query("tblpromotions", "", "", "code", "ASC");

while ($data = mysql_fetch_array($result)) {
	$promo_id = $data['id'];
	$promo_code = $data['code'];
	$promo_type = $data['type'];
	$promo_recurring = $data['recurring'];
	$promo_value = $data['value'];

	if ($promo_type == "Percentage") {
		$promo_value .= "%";
	}
	else {
		$promo_value = formatCurrency($promo_value);
	}


	if ($promo_type == "Free Setup") {
		$promo_value = $aInt->lang("promos", "freesetup");
	}

	$promo_recurring = ($promo_recurring ? $aInt->lang("status", "recurring") : $aInt->lang("status", "onetime"));

	if ($promo_type == "Price Override") {
		$promo_recurring = $aInt->lang("promos", "priceoverride");
	}


	if ($promo_type == "Free Setup") {
		$promo_recurring = "";
	}

	echo "<option value=\"" . $promo_id . "\"";

	if ($promo_id == $promoid) {
		echo " selected";
	}

	echo ">" . $promo_code . " - " . $promo_value . " " . $promo_recurring . "</option>";
}

echo "</select> (";
echo $aInt->lang("promotions", "noaffect");
echo ")</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "subscriptionid");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"25\" name=\"subscriptionid\" value=\"";
echo $subscriptionid;
echo "\"></td></tr>

";

if ($domainregistraractions) {
	if ($domains->hasFunction("GetNameservers")) {
		echo "<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("domains", "nameserver");
		echo " 1</td><td class=\"fieldarea\" colspan=\"3\"><input type=\"text\" name=\"ns1\" value=\"";
		echo $nsvalues['ns1'];
		echo "\" size=\"40\"><input type=\"hidden\" name=\"oldns1\" value=\"";
		echo $nsvalues['ns1'];
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("domains", "nameserver");
		echo " 2</td><td class=\"fieldarea\" colspan=\"3\"><input type=\"text\" name=\"ns2\" value=\"";
		echo $nsvalues['ns2'];
		echo "\" size=\"40\"><input type=\"hidden\" name=\"oldns2\" value=\"";
		echo $nsvalues['ns2'];
		echo "\" /> <input type=\"checkbox\" name=\"defaultns\" id=\"defaultns\" /> <label for=\"defaultns\">";
		echo $aInt->lang("domains", "resetdefaultns");
		echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("domains", "nameserver");
		echo " 3</td><td class=\"fieldarea\" colspan=\"3\"><input type=\"text\" name=\"ns3\" value=\"";
		echo $nsvalues['ns3'];
		echo "\" size=\"40\"><input type=\"hidden\" name=\"oldns3\" value=\"";
		echo $nsvalues['ns3'];
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("domains", "nameserver");
		echo " 4</td><td class=\"fieldarea\" colspan=\"3\"><input type=\"text\" name=\"ns4\" value=\"";
		echo $nsvalues['ns4'];
		echo "\" size=\"40\"><input type=\"hidden\" name=\"oldns4\" value=\"";
		echo $nsvalues['ns4'];
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("domains", "nameserver");
		echo " 5</td><td class=\"fieldarea\" colspan=\"3\"><input type=\"text\" name=\"ns5\" value=\"";
		echo $nsvalues['ns5'];
		echo "\" size=\"40\"><input type=\"hidden\" name=\"oldns5\" value=\"";
		echo $nsvalues['ns5'];
		echo "\" /></td></tr>
";
	}


	if ($lockstatus) {
		echo "<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("domains", "reglock");
		echo "</td><td class=\"fieldarea\" colspan=\"3\"><input type=\"checkbox\" name=\"lockstatus\"";

		if ($lockstatus == "locked") {
			echo " checked";
		}

		echo "> ";
		echo $aInt->lang("global", "ticktoenable");
		echo " <input type=\"hidden\" name=\"oldlockstatus\" value=\"";
		echo $lockstatus;
		echo "\"></td></tr>
";
	}

	echo "<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("domains", "registrarcommands");
	echo "</td><td colspan=\"3\">
";

	if ($domains->hasFunction("RegisterDomain")) {
		echo "<input type=\"button\" value=\"";
		echo $aInt->lang("domains", "actionreg");
		echo "\" class=\"button\" onClick=\"window.location='clientsdomainreg.php?domainid=";
		echo $id;
		echo "'\"> ";
	}


	if ($domains->hasFunction("TransferDomain")) {
		echo "<input type=\"button\" value=\"";
		echo $aInt->lang("domains", "transfer");
		echo "\" class=\"button\" onClick=\"window.location='clientsdomainreg.php?domainid=";
		echo $id;
		echo "&ac=transfer'\"> ";
	}


	if ($domains->hasFunction("RenewDomain")) {
		echo "<input type=\"button\" value=\"";
		echo $aInt->lang("domains", "renew");
		echo "\" class=\"button\" onClick=\"showDialog('renew')\"> ";
	}


	if ($domains->hasFunction("GetContactDetails")) {
		echo "<input type=\"button\" value=\"";
		echo $aInt->lang("domains", "modifydetails");
		echo "\" class=\"button\" onClick=\"window.location='clientsdomaincontacts.php?domainid=";
		echo $id;
		echo "'\"> ";
	}


	if ($domains->hasFunction("GetEPPCode")) {
		echo "<input type=\"button\" value=\"";
		echo $aInt->lang("domains", "getepp");
		echo "\" class=\"button\" onClick=\"showDialog('getepp')\"> ";
	}


	if ($domains->hasFunction("RequestDelete")) {
		echo "<input type=\"button\" value=\"";
		echo $aInt->lang("domains", "requestdelete");
		echo "\" class=\"button\" onClick=\"showDialog('reqdelete')\"> ";
	}


	if ($domains->hasFunction("ReleaseDomain")) {
		echo "<input type=\"button\" value=\"";
		echo $aInt->lang("domains", "releasedomain");
		echo "\" class=\"button\" onClick=\"showDialog('reldomain')\"> ";
	}


	if ($domains->moduleCall("AdminCustomButtonArray")) {
		$adminbuttonarray = $domains->getModuleReturn();
		foreach ($adminbuttonarray as $key => $value) {
			echo " <input type=\"button\" value=\"";
			echo $key;
			echo "\" class=\"button\" onClick=\"window.location='";
			echo $PHP_SELF;
			echo "?userid=";
			echo $userid;
			echo "&id=";
			echo $id;
			echo "&regaction=custom&ac=";
			echo $value;
			echo "'\">";
		}
	}

	echo "</td></tr>
";
}

echo "<tr><td class=\"fieldlabel\">";
echo $aInt->lang("domains", "managementtools");
echo "</td><td class=\"fieldarea\" colspan=\"3\"><input type=\"checkbox\" name=\"dnsmanagement\" id=\"dnsmanagement\"";

if ($dnsmanagement) {
	echo " checked";
}

echo "> <label for=\"dnsmanagement\">";
echo $aInt->lang("domains", "dnsmanagement");
echo "</label> <input type=\"checkbox\" name=\"emailforwarding\" id=\"emailforwarding\"";

if ($emailforwarding) {
	echo " checked";
}

echo "> <label for=\"emailforwarding\">";
echo $aInt->lang("domains", "emailforwarding");
echo "</label> <input type=\"checkbox\" name=\"idprotection\" id=\"idprotection\"";

if ($idprotection) {
	echo " checked";
}

echo "> <label for=\"idprotection\">";
echo $aInt->lang("domains", "idprotection");
echo "</label> <input type=\"checkbox\" name=\"donotrenew\" id=\"donotrenew\"";

if ($donotrenew) {
	echo " checked";
}

echo "> <label for=\"donotrenew\">";
echo $aInt->lang("domains", "donotrenew");
echo "</label></td></tr>
";

if (function_exists($registrar . "_AdminDomainsTabFields")) {
	$fieldsarray = call_user_func($registrar . "_AdminDomainsTabFields", $params);

	if (is_array($fieldsarray)) {
		foreach ($fieldsarray as $k => $v) {
			echo "<tr><td class=\"fieldlabel\">" . $k . "</td><td class=\"fieldarea\" colspan=\"3\">" . $v . "</td></tr>";
		}
	}
}

$hookret = run_hook("AdminClientDomainsTabFields", array("id" => $id));
foreach ($hookret as $hookdat) {
	foreach ($hookdat as $k => $v) {
		echo "<td class=\"fieldlabel\">" . $k . "</td><td class=\"fieldarea\" colspan=\"3\">" . $v . "</td></tr>";
	}
}

$domainparts = explode(".", $domain, 2);
$tld = "." . $domainparts[1];
$tempdomainfields = $additionaldomainfields[$tld];

if ($tempdomainfields) {
	$result = select_query("tbldomainsadditionalfields", "", array("domainid" => $id));

	while ($data = mysql_fetch_array($result)) {
		$field_name = $data['name'];
		$field_value = $data['value'];
		$values[$field_name] = $field_value;
	}

	foreach ($tempdomainfields as $key => $value) {
		$fieldname = $keyname = $value['Name'];

		if ($value['DisplayName']) {
			$fieldname = $value['DisplayName'];
		}

		$langvar = $value['LangVar'];

		if ($_LANG[$langvar]) {
			$fieldname = $_LANG[$langvar];
		}


		if ($value['Type'] == "text") {
			$input = "<input type=\"text\" size=\"" . $value['Size'] . (("\" name=\"additionaldomainfield[" . $keyname . "]") . "\" value=\"") . $values[$keyname] . "\">";
		}
		else {
			if ($value['Type'] == "dropdown") {
				$input = ("<select name=\"additionaldomainfield[" . $keyname . "]") . "\">";
				$fieldoptions = explode(",", $value['Options']);
				foreach ($fieldoptions as $optionvalue) {
					$opkey = $opvalue = $optionvalue;

					if (strpos($opkey, "|")) {
						$opkey = explode("|", $opkey, 2);
						$opvalue = trim($opkey[1]);
						$opkey = trim($opkey[0]);
					}

					$input .= ("<option value=\"" . $opkey . "\"");

					if ($values[$keyname] == $opkey) {
						$input .= " selected";
					}

					$input .= ">" . $opvalue . "</option>";
				}

				$input .= "</select>";
			}
			else {
				if ($value['Type'] == "tickbox") {
					$input = (("<input type=\"checkbox\" name=\"additionaldomainfield[" . $keyname . "]") . "\"");

					if ($values[$keyname] == "on") {
						$input .= " checked";
					}

					$input .= ">";
				}
				else {
					if ($value['Type'] == "radio") {
						$fieldoptions = explode(",", $value['Options']);
						$input = "";
						foreach ($fieldoptions as $optionvalue) {
							$input .= (("<input type=\"radio\" name=\"additionaldomainfield[" . $keyname . "]") . "\" value=\"" . $optionvalue . "\"");

							if ($values[$keyname] == $optionvalue) {
								$input .= " checked";
							}

							$input .= "> " . $optionvalue . "<br>";
						}
					}
				}
			}
		}

		echo "<tr><td class=\"fieldlabel\">" . $fieldname . "</td><td class=\"fieldarea\" colspan=\"3\">" . $input . "</td></tr>";
	}
}

echo "<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "adminnotes");
echo "</td><td class=\"fieldarea\" colspan=\"3\"><textarea name=\"additionalnotes\" rows=4 style=\"width:100%;\">";
echo $additionalnotes;
echo "</textarea></td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"10\" width=\"1\" /><br />
<div align=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("global", "savechanges");
echo "\" class=\"btn btn-primary\" /> <input type=\"reset\" value=\"";
echo $aInt->lang("global", "cancelchanges");
echo "\" class=\"btn\" /><br /><a href=\"#\" onClick=\"showDialog('delete');return false\" style=\"color:#cc0000\">";
echo "<s";
echo "trong>";
echo $aInt->lang("global", "delete");
echo "</strong></a></div>
</form>

<br>

<form action=\"clientsemails.php?userid=";
echo $userid;
echo "\" method=\"post\">
<input type=\"hidden\" name=\"action\" value=\"send\">
<input type=\"hidden\" name=\"type\" value=\"domain\">
<input type=\"hidden\" name=\"id\" value=\"";
echo $id;
echo "\">
<div class=\"contentbox\">";
echo "<B>" . $aInt->lang("global", "sendmessage") . "</B> <select name=\"messagename\"><option value=\"newmessage\">" . $aInt->lang("emails", "newmessage") . "</option>";
$result = select_query("tblemailtemplates", "", array("type" => "domain", "language" => ""), "name", "ASC");

while ($data = mysql_fetch_array($result)) {
	$messagename = $data['name'];
	$custom = $data['custom'];
	echo "<option value=\"" . $messagename . "\"";

	if ($custom == "1") {
		echo " style=\"background-color:#efefef\"";
	}

	echo ">" . $messagename . "</option>";
}

echo "</select> <input type=\"submit\" value=\"" . $aInt->lang("global", "sendmessage") . "\">";
echo "</div>
</form>
";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>