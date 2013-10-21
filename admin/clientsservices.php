<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View Clients Products/Services");
$aInt->requiredFiles(array("clientfunctions", "gatewayfunctions", "modulefunctions", "customfieldfunctions", "configoptionsfunctions", "invoicefunctions", "processinvoices"));
$aInt->inClientsProfile = true;
$id = (int)$whmcs->get_req_var("id");
$hostingid = (int)$whmcs->get_req_var("hostingid");
$userid = (int)$whmcs->get_req_var("userid");
$aid = $whmcs->get_req_var("aid");
$action = $whmcs->get_req_var("action");
$modop = $whmcs->get_req_var("modop");

if ($modop) {
	checkPermission("Perform Server Operations");
}


if (!$id && $hostingid) {
	$id = $hostingid;
}


if (!$userid && !$id) {
	$userid = get_query_val("tblclients", "id", "", "id", "ASC", "0,1");
}


if ($userid && !$id) {
	$aInt->valUserID($userid);

	if (!$userid) {
		$aInt->gracefulExit("Invalid User ID");
	}

	$id = get_query_val("tblhosting", "id", array("userid" => $userid), "domain", "ASC", "0,1");
}


if (!$id) {
	$aInt->gracefulExit($aInt->lang("services", "noproductsinfo") . " <a href=\"ordersadd.php?userid=" . $userid . "\">" . $aInt->lang("global", "clickhere") . "</a> " . $aInt->lang("orders", "toplacenew"));
}

$result = select_query("tblhosting", "tblhosting.*,tblproducts.servertype,tblproducts.type", array("tblhosting.id" => $id), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");
$service_data = mysql_fetch_array($result);
$id = $service_data['id'];

if (!$id) {
	$aInt->gracefulExit("Service ID Not Found");
}

$userid = $service_data['userid'];
$aInt->valUserID($userid);
$producttype = $service_data['type'];
$module = $service_data['servertype'];
$orderid = $service_data['orderid'];
$packageid = $service_data['packageid'];
$server = $service_data['server'];
$regdate = $service_data['regdate'];
$domain = $service_data['domain'];
$paymentmethod = $service_data['paymentmethod'];
$firstpaymentamount = $service_data['firstpaymentamount'];
$amount = $service_data['amount'];
$billingcycle = $service_data['billingcycle'];
$nextduedate = $service_data['nextduedate'];
$domainstatus = $service_data['domainstatus'];
$username = $service_data['username'];
$password = decrypt($service_data['password']);
$notes = $service_data['notes'];
$subscriptionid = $service_data['subscriptionid'];
$promoid = $service_data['promoid'];
$suspendreason = $service_data['suspendreason'];
$overideautosuspend = $service_data['overideautosuspend'];
$ns1 = $service_data['ns1'];
$ns2 = $service_data['ns2'];
$dedicatedip = $service_data['dedicatedip'];
$assignedips = $service_data['assignedips'];
$diskusage = $service_data['diskusage'];
$disklimit = $service_data['disklimit'];
$bwusage = $service_data['bwusage'];
$bwlimit = $service_data['bwlimit'];
$lastupdate = $service_data['lastupdate'];
$overidesuspenduntil = $service_data['overidesuspenduntil'];
$frm = new WHMCS_Form();

if ($frm->issubmitted()) {
	checkPermission("Edit Clients Products/Services");
	$packageid = $whmcs->get_req_var("packageid");
	$oldserviceid = $whmcs->get_req_var("oldserviceid");
	$addonid = $whmcs->get_req_var("addonid");
	$name = $whmcs->get_req_var("name");
	$setupfee = $whmcs->get_req_var("setupfee");
	$recurring = $whmcs->get_req_var("recurring");
	$billingcycle = $whmcs->get_req_var("billingcycle");
	$status = $whmcs->get_req_var("status");
	$regdate = $whmcs->get_req_var("regdate");
	$oldnextduedate = $whmcs->get_req_var("oldnextduedate");
	$nextduedate = $whmcs->get_req_var("nextduedate");
	$paymentmethod = $whmcs->get_req_var("paymentmethod");
	$tax = $whmcs->get_req_var("tax");
	$promoid = $whmcs->get_req_var("promoid");
	$notes = $whmcs->get_req_var("notes");
	$configoption = $whmcs->get_req_var("configoption");

	if ($aid = $whmcs->get_req_var("aid")) {
		if ($billingcycle == "Free" || $billingcycle == "Free Account") {
			$setupfee = $recurring = 0;
			$nextduedate = fromMySQLDate("0000-00-00");
		}


		if (is_numeric($aid)) {
			$oldstatus = get_query_val("tblhostingaddons", "status", array("id" => $aid));
			$array = array("hostingid" => $id, "addonid" => $addonid, "name" => $name, "setupfee" => $setupfee, "recurring" => $recurring, "billingcycle" => $billingcycle, "status" => $status, "regdate" => toMySQLDate($regdate), "nextduedate" => toMySQLDate($nextduedate), "paymentmethod" => $paymentmethod, "tax" => $tax, "notes" => $notes);

			if ($oldnextduedate != $nextduedate) {
				$array['nextinvoicedate'] = toMySQLDate($nextduedate);
			}

			update_query("tblhostingaddons", $array, array("id" => $aid));

			if ($oldserviceid != $id) {
				logActivity("Transferred Addon from Service ID: " . $oldserviceid . " to Service ID: " . $id . " - Addon ID: " . $aid);
			}
			else {
				logActivity("Modified Addon - Addon ID: " . $aid . " - Service ID: " . $id);
			}


			if ($oldstatus != "Active" && $status == "Active") {
				run_hook("AddonActivated", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
			}
			else {
				if ($oldstatus != "Suspended" && $status == "Suspended") {
					run_hook("AddonSuspended", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
				}
				else {
					if ($oldstatus != "Terminated" && $status == "Terminated") {
						run_hook("AddonTerminated", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
					}
					else {
						if ($oldstatus != "Cancelled" && $status == "Cancelled") {
							run_hook("AddonCancelled", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
						}
						else {
							if ($oldstatus != "Fraud" && $status == "Fraud") {
								run_hook("AddonFraud", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
							}
							else {
								run_hook("AddonEdit", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
							}
						}
					}
				}
			}
		}
		else {
			checkPermission("Add New Order");
			$predefname = "";

			if ($addonid) {
				$result = select_query("tbladdons", "", array("id" => $addonid));
				$data = mysql_fetch_array($result);
				$addonid = $data['id'];
				$predefname = $data['name'];
				$tax = $data['tax'];

				if ($whmcs->get_req_var("defaultpricing")) {
					$billingcycle = $data['billingcycle'];
					$currency = getCurrency($userid);
					$result2 = select_query("tblpricing", "", array("type" => "addon", "currency" => $currency['id'], "relid" => $addonid));
					$data = mysql_fetch_array($result2);
					$setupfee = $data['msetupfee'];
					$recurring = $data['monthly'];
				}
			}

			$newaddonid = insert_query("tblhostingaddons", array("hostingid" => $id, "addonid" => $addonid, "name" => $name, "setupfee" => $setupfee, "recurring" => $recurring, "billingcycle" => $billingcycle, "status" => $status, "regdate" => toMySQLDate($regdate), "nextduedate" => toMySQLDate($nextduedate), "nextinvoicedate" => toMySQLDate($nextduedate), "paymentmethod" => $paymentmethod, "tax" => $tax, "notes" => $notes));
			logActivity("Added New Addon - " . $name . $predefname . " - Addon ID: " . $newaddonid . " - Service ID: " . $id);

			if ($geninvoice) {
				$invoiceid = createInvoices($userid, "", "", array("addons" => array($newaddonid)));
			}

			run_hook("AddonAdd", array("id" => $newaddonid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
		}

		redir("userid=" . $userid . "&id=" . $id . "&success=true");
	}


	if (!$whmcs->get_req_var("packageid") && !$whmcs->get_req_var("billingcycle")) {
		redir("userid=" . $userid . "&id=" . $id);
	}

	$currency = getCurrency($userid);
	run_hook("PreServiceEdit", array("serviceid" => $id));
	run_hook("PreAdminServiceEdit", array("serviceid" => $id));
	$configoptions = getCartConfigOptions($packageid, $configoption, $billingcycle);
	$configoptionsrecurring = 0;
	foreach ($configoptions as $configoption) {
		$configoptionsrecurring += $configoption['selectedrecurring'];
		$result = select_query("tblhostingconfigoptions", "COUNT(*)", array("relid" => $id, "configid" => $configoption['id']));
		$data = mysql_fetch_array($result);

		if (!$data[0]) {
			insert_query("tblhostingconfigoptions", array("relid" => $id, "configid" => $configoption['id']));
		}

		update_query("tblhostingconfigoptions", array("optionid" => $configoption['selectedvalue'], "qty" => $configoption['selectedqty']), array("relid" => $id, "configid" => $configoption['id']));
	}

	$newamount = ($autorecalcrecurringprice ? recalcRecurringProductPrice($id, $userid, $packageid, $billingcycle, $configoptionsrecurring, $promoid) : "-1");
	migrateCustomFieldsBetweenProducts($id, $packageid, true);
	$changelog = array();
	$logchangefields = array("regdate" => "Registration Date", "packageid" => "Product/Service", "server" => "Server", "domain" => "Domain", "dedicatedip" => "Dedicated IP", "paymentmethod" => "Payment Method", "firstpaymentamount" => "First Payment Amount", "amount" => "Recurring Amount", "billingcycle" => "Billing Cycle", "nextduedate" => "Next Due Date", "domainstatus" => "Status", "username" => "Username", "password" => "Password", "subscriptionid" => "Subscription ID");
	foreach ($logchangefields as $fieldname => $displayname) {
		$newval = $whmcs->get_req_var($fieldname);
		$oldval = $service_data[$fieldname];

		if ($fieldname == "regdate" || $fieldname == "nextduedate") {
			$newval = toMySQLDate($newval);
		}
		else {
			if ($fieldname == "password") {
				$oldval = decrypt($oldval);
			}
			else {
				if ($fieldname == "amount" && 0 <= $newamount) {
					$newval = $newamount;
				}
			}
		}


		if ($newval != $oldval) {
			$changelog[] = $displayname . " changed from " . $oldval . " to " . $newval;
			continue;
		}
	}

	$updatearr = array();
	$updatefields = array("server", "packageid", "domain", "paymentmethod", "firstpaymentamount", "amount", "billingcycle", "regdate", "nextduedate", "username", "password", "notes", "subscriptionid", "promoid", "overideautosuspend", "overidesuspenduntil", "ns1", "ns2", "domainstatus", "dedicatedip", "assignedips");
	foreach ($updatefields as $fieldname) {
		$newval = $whmcs->get_req_var($fieldname);

		if ($fieldname == "nextduedate" && $whmcs->get_req_var("billingcycle") == "Free Account") {
			$newval = "0000-00-00";
		}
		else {
			if (($fieldname == "regdate" || $fieldname == "nextduedate") || $fieldname == "overidesuspenduntil") {
				$newval = toMySQLDate($newval);
			}
			else {
				if ($fieldname == "password") {
					$newval = encrypt($newval);
				}
				else {
					if ($fieldname == "amount" && 0 <= $newamount) {
						$newval = $newamount;
					}
				}
			}
		}

		$updatearr[$fieldname] = $newval;
	}


	if (toMySQLDate($whmcs->get_req_var("oldnextduedate")) != $updatearr['nextduedate']) {
		$updatearr['nextinvoicedate'] = $updatearr['nextduedate'];
	}

	update_query("tblhosting", $updatearr, array("id" => $id));
	logActivity("Modified Product/Service - " . implode(", ", $changelog) . (" - User ID: " . $userid . " - Service ID: " . $id), $userid);
	$cancelid = get_query_val("tblcancelrequests", "id", array("relid" => $id, "type" => "End of Billing Period"), "id", "DESC");

	if ($autoterminateendcycle) {
		if ($cancelid) {
			update_query("tblcancelrequests", array("reason" => $autoterminatereason), array("id" => $cancelid));
		}
		else {
			createCancellationRequest($userid, $id, $autoterminatereason, "End of Billing Period");
		}
	}
	else {
		if ($cancelid) {
			delete_query("tblcancelrequests", array("id" => $cancelid));
			logActivity("Removed Automatic Cancellation for End of Current Cycle - Service ID: " . $id, $userid);
		}
	}

	$module = get_query_val("tblproducts", "servertype", array("id" => $packageid));

	if ($module) {
		if (!isValidforPath($module)) {
			exit("Invalid Server Module Name");
		}

		$modulepath = ROOTDIR . "/modules/servers/" . $module . "/" . $module . ".php";

		if (file_exists($modulepath)) {
			require_once $modulepath;
		}


		if (function_exists($module . "_AdminServicesTabFieldsSave")) {
			$params = ModuleBuildParams($id);
			$fieldsarray = call_user_func($params['moduletype'] . "_AdminServicesTabFieldsSave", $params);
		}
	}

	run_hook("AdminClientServicesTabFieldsSave", $_REQUEST);
	run_hook("AdminServiceEdit", array("userid" => $userid, "serviceid" => $id));
	run_hook("ServiceEdit", array("userid" => $userid, "serviceid" => $id));
	redir("userid=" . $userid . "&id=" . $id . "&success=true");
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	checkPermission("Delete Clients Products/Services");
	run_hook("ServiceDelete", array("userid" => $userid, "serviceid" => $id));
	delete_query("tblhosting", array("id" => $id));
	delete_query("tblhostingaddons", array("hostingid" => $id));
	delete_query("tblhostingconfigoptions", array("relid" => $id));
	full_query("DELETE FROM tblcustomfieldsvalues WHERE relid='" . db_escape_string($id) . "' AND fieldid IN (SELECT id FROM tblcustomfields WHERE type='product')");
	logActivity("Deleted Product/Service - User ID: " . $userid . " - Service ID: " . $id, $userid);
	redir("userid=" . $userid);
}


if ($action == "deladdon") {
	check_token("WHMCS.admin.default");
	checkPermission("Delete Clients Products/Services");
	run_hook("AddonDeleted", array("id" => $aid));
	delete_query("tblhostingaddons", array("id" => $aid));
	logActivity("Deleted Addon - User ID: " . $userid . " - Service ID: " . $id . " - Addon ID: " . $aid, $userid);
	redir("userid=" . $userid . "&id=" . $id);
}

ob_start();
$adminbuttonarray = "";

if ($module) {
	if (!isValidforPath($module)) {
		exit("Invalid Server Module Name");
	}

	$modulepath = ROOTDIR . "/modules/servers/" . $module . "/" . $module . ".php";

	if (file_exists($modulepath)) {
		require_once $modulepath;
	}


	if (function_exists($module . "_AdminCustomButtonArray")) {
		$adminbuttonarray = call_user_func($module . "_AdminCustomButtonArray");
	}
}


if ($modop == "create") {
	$result = ServerCreateAccount($id);
	wSetCookie("ModCmdResult", $result);
	redir("userid=" . $userid . "&id=" . $id . "&act=create&ajaxupdate=1");
}


if ($modop == "suspend") {
	$result = ServerSuspendAccount($id, $suspreason);
	wSetCookie("ModCmdResult", $result);

	if ($result == "success" && $suspemail == "true") {
		sendMessage("Service Suspension Notification", $id);
	}

	redir("userid=" . $userid . "&id=" . $id . "&act=suspend&ajaxupdate=1");
}


if ($modop == "unsuspend") {
	$result = ServerUnsuspendAccount($id);
	wSetCookie("ModCmdResult", $result);
	redir("userid=" . $userid . "&id=" . $id . "&act=unsuspend&ajaxupdate=1");
}


if ($modop == "terminate") {
	$result = ServerTerminateAccount($id);
	wSetCookie("ModCmdResult", $result);
	redir("userid=" . $userid . "&id=" . $id . "&act=terminate&ajaxupdate=1");
}


if ($modop == "changepackage") {
	$result = ServerChangePackage($id);
	wSetCookie("ModCmdResult", $result);
	redir("userid=" . $userid . "&id=" . $id . "&act=updown&ajaxupdate=1");
}


if ($modop == "changepw") {
	$result = ServerChangePassword($id);
	wSetCookie("ModCmdResult", $result);
	redir("userid=" . $userid . "&id=" . $id . "&act=pwchange&ajaxupdate=1");
}


if ($modop == "custom") {
	$result = ServerCustomFunction($id, $ac);

	if (substr($result, 0, 9) == "redirect|") {
		exit($result);
	}

	wSetCookie("ModCmdResult", $result);
	redir("userid=" . $userid . "&id=" . $id . "&act=custom&ajaxupdate=1");
}


if (in_array($whmcs->get_req_var("act"), array("create", "suspend", "unsuspend", "terminate", "updown", "pwchange", "custom"))) {

	if ($result = wGetCookie("ModCmdResult")) {
		if ($result != "success") {
			infoBox($aInt->lang("services", "moduleerror"), htmlspecialchars($result), "error");
		}
		else {
			infoBox($aInt->lang("services", "modulesuccess"), $aInt->lang("services", $act . "success"), "success");
		}
	}
}


if ($whmcs->get_req_var("success")) {
	infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("global", "changesuccessdesc"));
}

$regdate = fromMySQLDate($regdate);
$nextduedate = fromMySQLDate($nextduedate);
$overidesuspenduntil = fromMySQLDate($overidesuspenduntil);

if ($disklimit == "0") {
	$disklimit = $aInt->lang("global", "unlimited");
}


if ($bwlimit == "0") {
	$bwlimit = $aInt->lang("global", "unlimited");
}

$currency = getCurrency($userid);
$data = get_query_vals("tblcancelrequests", "id,type,reason", array("relid" => $id), "id", "DESC");
$cancelid = $data['id'];
$canceltype = $data['type'];
$autoterminatereason = $data['reason'];
$autoterminateendcycle = false;

if ($canceltype == "End of Billing Period") {
	$autoterminateendcycle = ($cancelid ? true : false);
}


if (!$server) {
	$server = get_query_val("tblservers", "id", array("type" => $module, "active" => "1"));

	if ($server) {
		update_query("tblhosting", array("server" => $server), array("id" => $id));
	}
}

$jscode = "function doDeleteAddon(id) {
if (confirm(\"" . $aInt->lang("addons", "areyousuredelete", 1) . "\")) {
window.location='" . $PHP_SELF . "?userid=" . $userid . "&id=" . $id . "&action=deladdon&aid='+id+'" . generate_token("link") . "';
}}
function runModuleCommand(cmd,custom) {
    $(\"#mod\"+cmd).dialog(\"close\");

    $(\"#modcmdbtns\").css(\"filter\",\"alpha(opacity=20)\");
    $(\"#modcmdbtns\").css(\"-moz-opacity\",\"0.2\");
    $(\"#modcmdbtns\").css(\"-khtml-opacity\",\"0.2\");
    $(\"#modcmdbtns\").css(\"opacity\",\"0.2\");
    var position = $(\"#modcmdbtns\").position();

    $(\"#modcmdworking\").css(\"position\",\"absolute\");
    $(\"#modcmdworking\").css(\"top\",position.top);
    $(\"#modcmdworking\").css(\"left\",position.left);
    $(\"#modcmdworking\").css(\"padding\",\"9px 50px 0\");
    $(\"#modcmdworking\").fadeIn();

    var reqstr = \"userid=" . $userid . "&id=" . $id . "&modop=\"+cmd;
    if (custom) reqstr += \"&ac=\"+custom;
    else if (cmd==\"suspend\") reqstr += \"&suspreason=\"+encodeURIComponent($(\"#suspreason\").val())+\"&suspemail=\"+$(\"#suspemail\").is(\":checked\");

    $.post(\"clientsservices.php\", reqstr,
    function(data){
        if (data.substr(0,9)==\"redirect|\") {
            window.location = data.substr(9);
        } else {
            $(\"#servicecontent\").html(data);
        }
    });

}
";
$aInt->jscode = $jscode;
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

echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td>";
$servicesarr = array();
$result = select_query("tblhosting", "tblhosting.id,tblhosting.domain,tblproducts.name,tblhosting.domainstatus", array("userid" => $userid), "domain", "ASC", "", "tblproducts ON tblhosting.packageid=tblproducts.id");

while ($data = mysql_fetch_array($result)) {
	$servicelist_id = $data['id'];
	$servicelist_product = $data['name'];
	$servicelist_domain = $data['domain'];
	$servicelist_status = $data['domainstatus'];

	if ($servicelist_domain) {
		$servicelist_product .= " - " . $servicelist_domain;
	}


	if ($servicelist_status == "Pending") {
		$color = "#ffffcc";
	}
	else {
		if ($servicelist_status == "Suspended") {
			$color = "#ccff99";
		}
		else {
			if (in_array($servicelist_status, array("Terminated", "Cancelled", "Fraud"))) {
				$color = "#ff9999";
			}
			else {
				$color = "#fff";
			}
		}
	}

	$servicesarr[$servicelist_id] = array($color, $servicelist_product);
}

$frmsub = new WHMCS_Form("frm2");
echo $frmsub->form("", "", "", "get", true);
echo $frmsub->hidden("userid", $userid);
echo "&nbsp;&nbsp;&nbsp; Products: " . $frmsub->dropdown("id", $servicesarr, $id, "submit()");
echo " " . $frmsub->submit($aInt->lang("global", "go"), "btn btn-success");
echo $frmsub->close();
echo "</td><td align=\"right\">

" . $frm->button($aInt->lang("services", "createupgorder"), "window.open('clientsupgrade.php?id=" . $id . "','','width=750,height=350,scrollbars=yes')") . " " . $frm->button($aInt->lang("services", "moveservice"), "window.open('clientsmove.php?type=hosting&id=" . $id . "','movewindow','width=500,height=300,top=100,left=100,scrollbars=yes')") . " &nbsp;&nbsp;&nbsp;

</td></tr></table>

<div id=\"modcmdresult\" style=\"display:none;\"></div>
";

if ($cancelid) {
	if (!$infobox) {
		infoBox($aInt->lang("services", "cancrequest"), $aInt->lang("services", "cancrequestinfo") . "<br />" . $_ADMINLANG['fields']['reason'] . ": " . $autoterminatereason);
	}
}

echo $infobox ? $infobox : "<img src=\"images/spacer.gif\" height=\"10\" width=\"1\" /><br />";

if ($lastupdate && $lastupdate != "0000-00-00 00:00:00") {
	echo "<div class=\"contentbox\">
<strong>" . $aInt->lang("services", "diskusage") . ":</strong> " . $diskusage . " " . $aInt->lang("fields", "mb") . ", <strong>" . $aInt->lang("services", "disklimit") . ":</strong> " . $disklimit . " " . $aInt->lang("fields", "mb") . ", ";

	if ($diskusage == $aInt->lang("global", "unlimited") || $disklimit == $aInt->lang("global", "unlimited")) {
	}
	else {
		echo "<strong>" . round($diskusage / $disklimit * 100, 0) . "% " . $aInt->lang("services", "used") . "</strong> :: ";
	}

	echo "<strong>" . $aInt->lang("services", "bwusage") . ":</strong> " . $bwusage . " " . $aInt->lang("fields", "mb") . ", <strong>" . $aInt->lang("services", "bwlimit") . ":</strong> " . $bwlimit . " " . $aInt->lang("fields", "mb") . ", ";

	if ($bwusage == $aInt->lang("global", "unlimited") || $bwlimit == $aInt->lang("global", "unlimited")) {
	}
	else {
		echo "<strong>" . round($bwusage / $bwlimit * 100, 0) . "% " . $aInt->lang("services", "used") . "</strong><br>";
	}

	echo "<small>(" . $aInt->lang("services", "lastupdated") . ": " . fromMySQLDate($lastupdate, "time") . ")</small>
</div>
<br />
";
}

echo $frm->form("?userid=" . $userid . "&id=" . $id . ($aid ? "&aid=" . $aid : ""));

if ($aid) {
	if ($aid == "add") {
		checkPermission("Add New Order");
		$managetitle = $aInt->lang("addons", "addnew");
		$setupfee = "0.00";
		$recurring = "0.00";
		$regdate = $nextduedate = getTodaysDate();
		$notes = "";
	}
	else {
		$managetitle = $aInt->lang("addons", "editaddon");
		$result = select_query("tblhostingaddons", "", array("id" => $aid));
		$data = mysql_fetch_array($result);
		$addonid = $data['addonid'];
		$customname = $data['name'];
		$recurring = $data['recurring'];
		$setupfee = $data['setupfee'];
		$billingcycle = $data['billingcycle'];
		$status = $data['status'];
		$regdate = $data['regdate'];
		$nextduedate = $data['nextduedate'];
		$paymentmethod = $data['paymentmethod'];
		$tax = $data['tax'];
		$notes = $data['notes'];
		$regdate = fromMySQLDate($regdate);
		$nextduedate = fromMySQLDate($nextduedate);
	}

	echo "<h2>" . $managetitle . "</h2>";
	$predefaddons = array();
	$result = select_query("tbladdons", "", "", "weight` ASC,`name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$preid = $data['id'];
		$name = $data['name'];
		$predefaddons[$preid] = $name;
	}

	$tbl = new WHMCS_Table();
	$tbl->add($aInt->lang("fields", "product"), $frm->hidden("oldserviceid", $id) . $frm->dropdown("id", $servicesarr, $id), 1);
	$tbl->add($aInt->lang("fields", "regdate"), $frm->date("regdate", $regdate));
	$tbl->add($aInt->lang("fields", "setupfee"), $frm->text("setupfee", $setupfee, "10"));
	$tbl->add($aInt->lang("addons", "predefinedaddon"), $frm->dropdown("addonid", $predefaddons, $addonid, "", "", true));
	$tbl->add($aInt->lang("global", "recurring"), $frm->text("recurring", $recurring, "10") . ($aid == "add" ? " " . $frm->checkbox("defaultpricing", $aInt->lang("addons", "usedefault"), true) : ""));
	$tbl->add($aInt->lang("addons", "customname"), $frm->text("name", $customname, "40"));
	$tbl->add($aInt->lang("fields", "billingcycle"), $aInt->cyclesDropDown($billingcycle, "", "Free"));
	$tbl->add($aInt->lang("fields", "status"), $aInt->productStatusDropDown($status));
	$tbl->add($aInt->lang("fields", "nextduedate"), $frm->date("nextduedate", $nextduedate));
	$tbl->add($aInt->lang("fields", "paymentmethod"), paymentMethodsSelection());
	$tbl->add($aInt->lang("addons", "taxaddon"), $frm->checkbox("tax", "", $tax));
	$tbl->add($aInt->lang("fields", "adminnotes"), $frm->textarea("notes", $notes, "4", "100%"), 1);
	echo $tbl->output();

	if ($aid == "add") {
		echo "<p align=\"center\"><input type=\"checkbox\" name=\"geninvoice\" id=\"geninvoice\" checked /> <label for=\"geninvoice\">" . $aInt->lang("addons", "geninvoice") . "</a></p>";
	}

	echo "<p align=\"center\">" . $frm->submit($aInt->lang("global", "savechanges"), "btn btn-primary") . " " . $frm->button($aInt->lang("global", "cancel"), "window.location='?userid=" . $userid . "&id=" . $id . "'") . "</p>";
}
else {
	$serversarr = $serversarr2 = array();
	$result = select_query("tblservers", "", array("type" => $module), "name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$serverid = $data['id'];
		$servername = $data['name'];
		$activeserver = $data['active'];
		$servermaxaccounts = $data['maxaccounts'];
		$disabled = $data['disabled'];

		if ($disabled) {
			$servername .= " (" . $aInt->lang("emailtpls", "disabled") . ")";
		}

		$result2 = select_query("tblhosting", "COUNT(*)", "server='" . $serverid . "' AND (domainstatus='Active' OR domainstatus='Suspended')");
		$data = mysql_fetch_array($result2);
		$servernumaccounts = $data[0];
		$label = $servername . " (" . $servernumaccounts . "/" . $servermaxaccounts . " " . $aInt->lang("fields", "accounts") . ")";

		if ($disabled) {
			$serversarr2[$serverid] = $label;
		}

		$serversarr[$serverid] = $label;
	}

	foreach ($serversarr2 as $k => $v) {
		$serversarr[$k] = $v;
	}

	$promoarr = array();
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

		$promoarr[$promo_id] = $promo_code . " - " . $promo_value . " " . $promo_recurring;
	}

	$tbl = new WHMCS_Table();
	$tbl->add($aInt->lang("fields", "ordernum"), $orderid . " - <a href=\"orders.php?action=view&id=" . $orderid . "\">" . $aInt->lang("orders", "vieworder") . "</a>");
	$tbl->add($aInt->lang("fields", "regdate"), $frm->date("regdate", $regdate));
	$tbl->add($aInt->lang("fields", "product"), $frm->hidden("oldpackageid", $packageid) . $frm->dropdown("packageid", $aInt->productDropDown($packageid), "", "submit()"));
	$tbl->add($aInt->lang("fields", "firstpaymentamount"), $frm->text("firstpaymentamount", $firstpaymentamount, "10"));
	$tbl->add($aInt->lang("fields", "server"), $frm->dropdown("server", $serversarr, $server, "submit()", "", true));
	$tbl->add($aInt->lang("fields", "recurringamount"), $frm->text("amount", $amount, "10") . " " . $frm->checkbox("autorecalcrecurringprice", $aInt->lang("services", "autorecalc"), ($autorecalcdefault ? true : false)));
	$tbl->add(($producttype == "server" ? $aInt->lang("fields", "hostname") : $aInt->lang("fields", "domain")), $frm->text("domain", $domain, "40") . " <a href=\"http://" . $domain . "\" target=\"_blank\" style=\"color:#cc0000\">www</a> <a href=\"whois.php?domain=" . $domain . "\" target=\"_blank\">" . $aInt->lang("domains", "whois") . "</a> <a href=\"http://www.intodns.com/" . $domain . "\" target=\"_blank\" style=\"color:#006633\">intoDNS</a>");
	$tbl->add($aInt->lang("fields", "nextduedate"), (in_array($billingcycle, array("One Time", "Free Account")) ? "N/A" : $frm->hidden("oldnextduedate", $nextduedate) . $frm->date("nextduedate", $nextduedate)));
	$tbl->add($aInt->lang("fields", "dedicatedip"), $frm->text("dedicatedip", $dedicatedip, "25"));
	$tbl->add($aInt->lang("fields", "billingcycle"), $aInt->cyclesDropDown($billingcycle));
	$tbl->add($aInt->lang("fields", "username"), $frm->text("username", $username, "20") . (function_exists($module . "_LoginLink") ? " " . ServerLoginLink($id) : ""));
	$tbl->add($aInt->lang("fields", "paymentmethod"), paymentMethodsSelection() . " <a href=\"clientsinvoices.php?userid=" . $userid . "&serviceid=" . $id . "\">" . $aInt->lang("invoices", "viewinvoices") . "</a>");
	$tbl->add($aInt->lang("fields", "password"), $frm->text("password", $password, "20"));
	$tbl->add($aInt->lang("fields", "promocode"), $frm->dropdown("promoid", $promoarr, $promoid, "", "", true) . " (" . $aInt->lang("services", "noaffect") . ")");
	$tbl->add($aInt->lang("fields", "status"), $aInt->productStatusDropDown($domainstatus, false, "domainstatus", "prodstatus") . ($domainstatus == "Suspended" ? " (" . $aInt->lang("services", "suspendreason") . ": " . (!$suspendreason ? $_LANG['suspendreasonoverdue'] : $suspendreason) . ")" : ""));
	$tbl->add($aInt->lang("fields", "subscriptionid"), $frm->text("subscriptionid", $subscriptionid, "25"));

	if ($producttype == "server") {
		$tbl->add($aInt->lang("fields", "assignedips"), $frm->textarea("assignedips", $assignedips, "4", "30"), 1);
		$tbl->add($aInt->lang("fields", "nameserver") . " 1", $frm->text("ns1", $ns1, "35"), 1);
		$tbl->add($aInt->lang("fields", "nameserver") . " 2", $frm->text("ns2", $ns2, "35"), 1);
	}

	$configoptions = array();
	$configoptions = getCartConfigOptions($packageid, "", $billingcycle, $id);

	if ($configoptions) {
		foreach ($configoptions as $configoption) {
			$optionid = $configoption['id'];
			$optionhidden = $configoption['hidden'];
			$optionname = ($optionhidden ? $configoption['optionname'] . " <i>(" . $aInt->lang("global", "hidden") . ")</i>" : $configoption['optionname']);
			$optiontype = $configoption['optiontype'];
			$selectedvalue = $configoption['selectedvalue'];
			$selectedqty = $configoption['selectedqty'];

			if ($optiontype == "1") {
				$inputcode = ("<select name=\"configoption[" . $optionid . "]") . "\">";
				foreach ($configoption['options'] as $option) {
					$inputcode .= ("<option value=\"" . $option['id'] . "\"");

					if ($option['hidden']) {
						$inputcode .= " style='color:#ccc;'";
					}


					if ($selectedvalue == $option['id']) {
						$inputcode .= " selected";
					}

					$inputcode .= ">" . $option['name'] . "</option>";
				}

				$inputcode .= "</select>";
			}
			else {
				if ($optiontype == "2") {
					$inputcode = "";
					foreach ($configoption['options'] as $option) {
						$inputcode .= (("<input type=\"radio\" name=\"configoption[" . $optionid . "]") . "\" value=\"" . $option['id'] . "\"");

						if ($selectedvalue == $option['id']) {
							$inputcode .= " checked";
						}


						if ($option['hidden']) {
							$inputcode .= "> <span style='color:#ccc;'>" . $option['name'] . "</span><br />";
							continue;
						}

						$inputcode .= "> " . $option['name'] . "<br />";
					}
				}
				else {
					if ($optiontype == "3") {
						$inputcode = ("<input type=\"checkbox\" name=\"configoption[" . $optionid . "]") . "\" value=\"1\"";

						if ($selectedqty) {
							$inputcode .= " checked";
						}

						$inputcode .= "> " . $configoption['options'][0]['name'];
					}
					else {
						if ($optiontype == "4") {
							$inputcode = ("<input type=\"text\" name=\"configoption[" . $optionid . "]") . "\" value=\"" . $selectedqty . "\" size=\"5\"> x " . $configoption['options'][0]['name'];
						}
					}
				}
			}

			$tbl->add($optionname, $inputcode, 1);
		}
	}


	if ($module) {
		$modulebtns = array();

		if (function_exists($module . "_CreateAccount")) {
			$modulebtns[] = $frm->button($aInt->lang("modulebuttons", "create"), "showDialog('modcreate')");
		}


		if (function_exists($module . "_SuspendAccount")) {
			$modulebtns[] = $frm->button($aInt->lang("modulebuttons", "suspend"), "showDialog('modsuspend')");
		}


		if (function_exists($module . "_UnsuspendAccount")) {
			$modulebtns[] = $frm->button($aInt->lang("modulebuttons", "unsuspend"), "showDialog('modunsuspend')");
		}


		if (function_exists($module . "_TerminateAccount")) {
			$modulebtns[] = $frm->button($aInt->lang("modulebuttons", "terminate"), "showDialog('modterminate')");
		}


		if (function_exists($module . "_ChangePackage")) {
			$modulebtns[] = $frm->button($aInt->lang("modulebuttons", "changepackage"), "showDialog('modchangepackage')");
		}


		if (function_exists($module . "_ChangePassword")) {
			$modulebtns[] = $frm->button($aInt->lang("modulebuttons", "changepassword"), "runModuleCommand('changepw')");
		}


		if ($adminbuttonarray) {
			foreach ($adminbuttonarray as $key => $value) {
				$modulebtns[] = $frm->button($key, "runModuleCommand('custom','" . $value . "')");
			}
		}

		$tbl->add($aInt->lang("services", "modulecommands"), "<div id=\"modcmdbtns\">" . implode(" ", $modulebtns) . "</div><div id=\"modcmdworking\" style=\"display:none;text-align:center;\"><img src=\"images/loader.gif\" /> &nbsp; Working...</div>", 1);

		if (function_exists($module . "_AdminServicesTabFields")) {
			$params = ModuleBuildParams($id);
			$fieldsarray = call_user_func($params['moduletype'] . "_AdminServicesTabFields", $params);

			if (is_array($fieldsarray)) {
				foreach ($fieldsarray as $k => $v) {
					$tbl->add($k, $v, 1);
				}
			}
		}
	}

	$hookret = run_hook("AdminClientServicesTabFields", array("id" => $id));
	foreach ($hookret as $hookdat) {
		foreach ($hookdat as $k => $v) {
			$tbl->add($k, $v, 1);
		}
	}

	$addonshtml = "";
	$aInt->sortableTableInit("nopagination");
	$service = new WHMCS_Service($id);
	$addons = $service->getAddons();
	foreach ($addons as $vals) {
		$tabledata[] = array($vals['regdate'], $vals['name'], $vals['pricing'], $vals['status'], $vals['nextduedate'], "<a href=\"" . $PHP_SELF . "?userid=" . $userid . "&id=" . $id . "&aid=" . $vals['id'] . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Edit\"></a>", "<a href=\"#\" onClick=\"doDeleteAddon('" . $vals['id'] . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Delete\"></a>");
	}

	$addonshtml = $aInt->sortableTable(array($aInt->lang("addons", "regdate"), $aInt->lang("addons", "name"), $aInt->lang("global", "pricing"), $aInt->lang("fields", "status"), $aInt->lang("fields", "nextduedate"), "", ""), $tabledata);
	$tbl->add($aInt->lang("addons", "title"), $addonshtml . "<div style=\"padding:5px 25px;\"><a href=\"clientsservices.php?userid=" . $userid . "&id=" . $id . "&aid=add\"><img src=\"images/icons/add.png\" border=\"0\" align=\"top\" /> Add New Addon</a></div>", 1);
	$customfields = getCustomFields("product", $packageid, $id, true);
	foreach ($customfields as $customfield) {
		$tbl->add($customfield['name'], $customfield['input'], 1);
	}

	$tbl->add($aInt->lang("services", "overrideautosusp"), $frm->checkbox("overideautosuspend", $aInt->lang("services", "nosuspenduntil"), $overideautosuspend) . " " . $frm->date("overidesuspenduntil", $overidesuspenduntil), 1);
	$tbl->add($aInt->lang("services", "endofcycle"), $frm->checkbox("autoterminateendcycle", $aInt->lang("services", "reason"), $autoterminateendcycle) . " " . $frm->text("autoterminatereason", $autoterminatereason, "60"), 1);
	$tbl->add($aInt->lang("fields", "adminnotes"), $frm->textarea("notes", $notes, "4", "100%"), 1);
	echo $tbl->output();
	echo "
<br />
<div align=\"center\">" . $frm->submit($aInt->lang("global", "savechanges"), "btn btn-primary") . " " . $frm->reset($aInt->lang("global", "cancelchanges")) . "<br />
<a href=\"#\" onclick=\"showDialog('delete');return false\" style=\"color:#cc0000\"><strong>" . $aInt->lang("global", "delete") . "</strong></a></div>";
}

echo $frm->close() . "

<br />

<div class=\"contentbox\">
<table align=\"center\"><tr><td>
<strong>" . $aInt->lang("global", "sendmessage") . "</strong>
</td><td>
";
$frmsub = new WHMCS_Form("frm3");
echo $frmsub->form("clientsemails.php?userid=" . $userid);
echo $frmsub->hidden("action", "send");
echo $frmsub->hidden("type", "product");
echo $frmsub->hidden("id", $id);
$emailarr = array();
$emailarr['newmessage'] = $aInt->lang("emails", "newmessage");
$result = select_query("tblemailtemplates", "", array("type" => "product", "language" => ""), "name", "ASC");

while ($data = mysql_fetch_array($result)) {
	$messagename = $data['name'];
	$custom = $data['custom'];
	$emailarr[$messagename] = ($custom ? array("#efefef", $messagename) : $messagename);
}

echo $frmsub->dropdown("messagename", $emailarr);
echo $frmsub->submit($aInt->lang("global", "sendmessage"));
echo $frmsub->close();
echo "</td><td>";
$frmsub = new WHMCS_Form("frm4");
echo $frmsub->form("clientsemails.php?userid=" . $userid);
echo $frmsub->hidden("action", "send");
echo $frmsub->hidden("type", "product");
echo $frmsub->hidden("id", $id);
echo $frmsub->hidden("messagename", "defaultnewacc");
echo $frmsub->submit($aInt->lang("emails", "senddefaultproductwelcome"));
echo $frmsub->close();
echo "</td></tr></table>
</div>";
$content = ob_get_contents();
ob_end_clean();

if ($whmcs->get_req_var("ajaxupdate")) {
	$content = preg_replace("/(<form\W[^>]*\bmethod=('|\"|)POST('|\"|)\b[^>]*>)/i", "$1" . "\r\n" . generate_token(), $content);

	echo $content;
	exit();
}
else {
	$content = "<div id=\"servicecontent\">" . $content . "</div>";
	$content .= $aInt->jqueryDialog("modcreate", $aInt->lang("services", "confirmcommand"), $aInt->lang("services", "createsure"), array($aInt->lang("global", "yes") => "runModuleCommand('create')", $aInt->lang("global", "no") => ""), "", "450");
	$content .= $aInt->jqueryDialog("modsuspend", $aInt->lang("services", "confirmcommand"), $aInt->lang("services", "suspendsure") . "<br /><div align=\"center\">" . $aInt->lang("services", "suspendreason") . ": <input type=\"text\" id=\"suspreason\" size=\"20\" /><br /><br /><input type=\"checkbox\" id=\"suspemail\" /> " . $aInt->lang("services", "suspendsendemail") . "</div>", array($aInt->lang("global", "yes") => "runModuleCommand('suspend')", $aInt->lang("global", "no") => ""), "", "450");
	$content .= $aInt->jqueryDialog("modunsuspend", $aInt->lang("services", "confirmcommand"), $aInt->lang("services", "unsuspendsure"), array($aInt->lang("global", "yes") => "runModuleCommand('unsuspend')", $aInt->lang("global", "no") => ""), "", "450");
	$content .= $aInt->jqueryDialog("modterminate", $aInt->lang("services", "confirmcommand"), $aInt->lang("services", "terminatesure"), array($aInt->lang("global", "yes") => "runModuleCommand('terminate')", $aInt->lang("global", "no") => ""), "", "450");
	$content .= $aInt->jqueryDialog("modchangepackage", $aInt->lang("services", "confirmcommand"), $aInt->lang("services", "chgpacksure"), array($aInt->lang("global", "yes") => "runModuleCommand('changepackage')", $aInt->lang("global", "no") => ""), "", "450");
	$content .= $aInt->jqueryDialog("delete", $aInt->lang("services", "deleteproduct"), $aInt->lang("services", "proddeletesure"), array($aInt->lang("global", "yes") => "window.location='" . $PHP_SELF . "?userid=" . $userid . "&id=" . $id . "&action=delete" . generate_token("link") . "'", $aInt->lang("global", "no") => ""), "180", "450");
}

$aInt->content = $content;
$aInt->display();
?>