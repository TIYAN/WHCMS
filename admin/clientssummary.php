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

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View Clients Summary", false);
$aInt->requiredFiles(array("clientfunctions", "processinvoices", "invoicefunctions", "gatewayfunctions", "affiliatefunctions", "modulefunctions"));
$aInt->inClientsProfile = true;
$aInt->valUserID($userid);

if ($return) {
	unset($_SESSION['uid']);
}


if ($action == "massaction") {
	check_token("WHMCS.admin.default");

	if ($inv) {
		checkPermission("Generate Due Invoices");
		$specificitems = array("products" => $selproducts, "addons" => $seladdons, "domains" => $seldomains);
		createInvoices($userid, "", "", $specificitems);
		infoBox($aInt->lang("invoices", "gencomplete"), $invoicecount . " Invoices Created");
	}


	if ($del) {
		if ($selproducts) {
			checkPermission("Delete Clients Products/Services");
			foreach ($selproducts as $pid) {
				delete_query("tblhosting", array("id" => $pid));
				delete_query("tblhostingaddons", array("hostingid" => $pid));
				logActivity("Deleted Product ID: " . $pid . " - User ID: " . $userid, $userid);
			}
		}


		if ($seladdons) {
			checkPermission("Delete Clients Products/Services");
			foreach ($seladdons as $aid) {
				delete_query("tblhostingaddons", array("id" => $aid));
				logActivity("Deleted Addon ID: " . $aid . " - User ID: " . $userid, $userid);
			}
		}


		if ($seldomains) {
			checkPermission("Delete Clients Domains");
			foreach ($seldomains as $did) {
				delete_query("tbldomains", array("id" => $did));
				logActivity("Deleted Domain ID: " . $did . " - User ID: " . $userid, $userid);
			}
		}

		infoBox($aInt->lang("global", "success"), $aInt->lang("clientsummary", "deletesuccess"));
	}


	if (((((($massupdate || $masscreate) || $masssuspend) || $massunsuspend) || $massterminate) || $masschangepackage) || $masschangepw) {
		if ($paymentmethod) {
			$paymentmethod = get_query_val("tblpaymentgateways", "gateway", array("gateway" => $paymentmethod));
		}


		if ($proratabill) {
			checkPermission("Edit Clients Products/Services");
			$targetnextduedate = toMySQLDate($nextduedate);
			foreach ($selproducts as $serviceid) {
				$data = get_query_vals("tblhosting", "packageid,domain,nextduedate,billingcycle,amount,paymentmethod", array("id" => $serviceid));
				$existingpid = $data['packageid'];
				$domain = $data['domain'];
				$existingnextduedate = $data['nextduedate'];
				$billingcycle = $data['billingcycle'];
				$price = $data['amount'];

				if (!$paymentmethod) {
					$paymentmethod = $data['paymentmethod'];
				}


				if ($recurringamount) {
					$price = $recurringamount;
				}

				$totaldays = getBillingCycleDays($billingcycle);
				$timediff = strtotime($targetnextduedate) - strtotime($existingnextduedate);
				$timediff = ceil($timediff / (60 * 60 * 24));
				$percent = $timediff / $totaldays;
				$amountdue = format_as_currency($price * $percent);
				$invdata = getInvoiceProductDetails($serviceid, $existingpid, "", "", $billingcycle, $domain);
				$description = $invdata['description'] . " (" . fromMySQLDate($existingnextduedate) . " - " . $nextduedate . ")";
				$tax = $invdata['tax'];
				insert_query("tblinvoiceitems", array("userid" => $userid, "type" => "ProrataProduct" . $targetnextduedate, "relid" => $serviceid, "description" => $description, "amount" => $amountdue, "taxed" => $tax, "duedate" => "now()", "paymentmethod" => $paymentmethod));
			}

			foreach ($seladdons as $aid) {
				$data = get_query_vals("tblhostingaddons", "hostingid,addonid,name,nextduedate,billingcycle,recurring,paymentmethod", array("id" => $aid));
				$serviceid = $data['hostingid'];
				$addonid = $data['addonid'];
				$name = $data['name'];
				$existingnextduedate = $data['nextduedate'];
				$billingcycle = $data['billingcycle'];
				$price = $data['recurring'];

				if (!$paymentmethod) {
					$paymentmethod = $data['paymentmethod'];
				}

				$domain = get_query_val("tblhosting", "domain", array("id" => $serviceid));

				if ($recurringamount) {
					$price = $recurringamount;
				}

				$totaldays = getBillingCycleDays($billingcycle);
				$timediff = strtotime($targetnextduedate) - strtotime($existingnextduedate);
				$timediff = ceil($timediff / (60 * 60 * 24));
				$percent = $timediff / $totaldays;
				$amountdue = format_as_currency($price * $percent);

				if ($domain) {
					$domain = "(" . $domain . ") ";
				}

				$description = $_LANG['orderaddon'] . " " . $domain . "- ";

				if ($name) {
					$description .= $name;
				}
				else {
					$description .= get_query_val("tbladdons", "name", array("id" => $addonid));
				}

				$description .= " (" . fromMySQLDate($existingnextduedate) . " - " . $nextduedate . ")";
				$tax = $invdata['tax'];
				insert_query("tblinvoiceitems", array("userid" => $userid, "type" => "ProrataProduct" . $targetnextduedate, "relid" => $aid, "description" => $description, "amount" => $amountdue, "taxed" => $tax, "duedate" => "now()", "paymentmethod" => $paymentmethod));
			}

			createInvoices($userid);
		}

		$updateqry = array();

		if ($firstpaymentamount) {
			$updateqry['firstpaymentamount'] = $firstpaymentamount;
		}


		if ($recurringamount) {
			$updateqry['amount'] = $recurringamount;
		}


		if ($nextduedate && !$proratabill) {
			$updateqry['nextduedate'] = $updateqry['nextinvoicedate'] = toMySQLDate($nextduedate);
		}


		if ($billingcycle) {
			$updateqry['billingcycle'] = $billingcycle;
		}


		if ($paymentmethod) {
			$updateqry['paymentmethod'] = $paymentmethod;
		}


		if ($status) {
			$updateqry['domainstatus'] = $status;
		}


		if ($overideautosuspend) {
			$updateqry['overideautosuspend'] = "on";
			$updateqry['overidesuspenduntil'] = toMySQLDate($overidesuspenduntil);
		}


		if ($selproducts && count($updateqry)) {
			checkPermission("Edit Clients Products/Services");
			foreach ($selproducts as $pid) {
				update_query("tblhosting", $updateqry, array("id" => $pid));
			}

			logActivity("Mass Updated Products IDs: " . implode(",", $selproducts) . (" - User ID: " . $userid), $userid);
		}

		unset($updateqry['amount']);
		unset($updateqry['domainstatus']);
		unset($updateqry['overideautosuspend']);
		unset($updateqry['overidesuspenduntil']);

		if ($status) {
			$updateqry['status'] = $status;
		}


		if ($seladdons) {
			unset($updateqry['firstpaymentamount']);

			if ($recurringamount) {
				$updateqry['recurring'] = $recurringamount;
			}


			if (count($updateqry)) {
				checkPermission("Edit Clients Products/Services");
				foreach ($seladdons as $aid) {
					update_query("tblhostingaddons", $updateqry, array("id" => $aid));
				}

				logActivity("Mass Updated Addons IDs: " . implode(",", $seladdons) . (" - User ID: " . $userid), $userid);
			}
		}


		if ($seldomains) {
			unset($updateqry['recurring']);
			unset($updateqry['billingcycle']);

			if ($firstpaymentamount) {
				$updateqry['firstpaymentamount'] = $firstpaymentamount;
			}


			if ($recurringamount) {
				$updateqry['recurringamount'] = $recurringamount;
			}


			if ($billingcycle == "Annually") {
				$updateqry['registrationperiod'] = "1";
			}


			if ($billingcycle == "Biennially") {
				$updateqry['registrationperiod'] = "2";
			}


			if ($billingcycle == "Triennially") {
				$updateqry['registrationperiod'] = "3";
			}


			if ($status == "Terminated") {
				$updateqry['status'] = "Expired";
			}


			if (count($updateqry)) {
				checkPermission("Edit Clients Domains");
				foreach ($seldomains as $did) {
					update_query("tbldomains", $updateqry, array("id" => $did));
				}

				logActivity("Mass Updated Domains IDs: " . implode(",", $seldomains) . (" - User ID: " . $userid), $userid);
			}
		}

		$moduleresults = array();

		if ($masscreate) {
			checkPermission("Perform Server Operations");
			foreach ($selproducts as $serviceid) {
				$modresult = ServerCreateAccount($serviceid);

				if ($modresult != "success") {
					$moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
					continue;
				}

				$moduleresults[] = "Service ID " . $serviceid . ": " . $aInt->lang("services", "createsuccess");
			}
		}


		if ($masssuspend) {
			checkPermission("Perform Server Operations");
			foreach ($selproducts as $serviceid) {
				$modresult = ServerSuspendAccount($serviceid);

				if ($modresult != "success") {
					$moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
					continue;
				}

				$moduleresults[] = "Service ID " . $serviceid . ": " . $aInt->lang("services", "suspendsuccess");
			}
		}


		if ($massunsuspend) {
			checkPermission("Perform Server Operations");
			foreach ($selproducts as $serviceid) {
				$modresult = ServerUnsuspendAccount($serviceid);

				if ($modresult != "success") {
					$moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
					continue;
				}

				$moduleresults[] = "Service ID " . $serviceid . ": " . $aInt->lang("services", "unsuspendsuccess");
			}
		}


		if ($massterminate) {
			checkPermission("Perform Server Operations");
			foreach ($selproducts as $serviceid) {
				$modresult = ServerTerminateAccount($serviceid);

				if ($modresult != "success") {
					$moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
					continue;
				}

				$moduleresults[] = "Service ID " . $serviceid . ": " . $aInt->lang("services", "terminatesuccess");
			}
		}


		if ($masschangepackage) {
			checkPermission("Perform Server Operations");
			foreach ($selproducts as $serviceid) {
				$modresult = ServerChangePackage($serviceid);

				if ($modresult != "success") {
					$moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
					continue;
				}

				$moduleresults[] = "Service ID " . $serviceid . ": " . $aInt->lang("services", "updownsuccess");
			}
		}


		if ($masschangepw) {
			checkPermission("Perform Server Operations");
			foreach ($selproducts as $serviceid) {
				$modresult = ServerChangePassword($serviceid);

				if ($modresult != "success") {
					$moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
					continue;
				}

				$moduleresults[] = "Service ID " . $serviceid . ": " . $aInt->lang("services", "pwchangesuccess");
			}
		}

		infoBox($aInt->lang("clientsummary", "massupdcomplete"), $aInt->lang("clientsummary", "modifysuccess") . "<br />" . implode("<br />", $moduleresults));
	}
}


if ($action == "uploadfile") {
	checkPermission("Manage Clients Files");
	$filename = $_FILES['uploadfile']['name'];

	if (!$title) {
		$title = $filename;
	}

	$filename = $origfilename = preg_replace("/[^a-zA-Z0-9-_. ]/", "", $filename);

	if (!$filename) {
		exit("Invalid File");
	}

	mt_srand(time());
	$rand = mt_rand(100000, 999999);
	$filename = "file" . $rand . "_" . $filename;
	run_hook("AdminClientFileUpload", array("userid" => $userid, "title" => $title, "filename" => $filename, "origfilename" => $origfilename, "adminonly" => $adminonly));
	move_uploaded_file($_FILES['uploadfile']['tmp_name'], $attachments_dir . $filename);
	insert_query("tblclientsfiles", array("userid" => $userid, "title" => $title, "filename" => $filename, "adminonly" => $adminonly, "dateadded" => "now()"));
	logActivity("Added Client File - Title: " . $title . " - User ID: " . $userid, $userid);
	header("Location: clientssummary.php?userid=" . $userid);
	exit();
}


if ($action == "deletefile") {
	checkPermission("Manage Clients Files");
	$result = select_query("tblclientsfiles", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	$title = $data['title'];
	$filename = $data['filename'];
	unlink($attachments_dir . $filename);
	delete_query("tblclientsfiles", array("id" => $id));
	logActivity("Deleted Client File - Title: " . $title . " - User ID: " . $userid, $userid);
	header("Location: clientssummary.php?userid=" . $userid);
	exit();
}


if ($action == "closeclient") {
	check_token("WHMCS.admin.default");
	checkPermission("Edit Clients Details");
	checkPermission("Edit Clients Products/Services");
	checkPermission("Edit Clients Domains");
	checkPermission("Manage Invoice");
	closeClient($userid);
	header("Location: " . $_SERVER['PHP_SELF'] . ("?userid=" . $userid));
	exit();
}


if ($action == "deleteclient") {
	check_token("WHMCS.admin.default");
	checkPermission("Delete Client");
	run_hook("ClientDelete", array("userid" => $userid));
	deleteClient($userid);
	header("Location: clients.php");
	exit();
}


if ($action == "savenotes") {
	check_token("WHMCS.admin.default");
	checkPermission("Edit Clients Details");
	update_query("tblclients", array("notes" => $adminnotes), array("id" => $userid));
	logActivity("Client Summary Notes Updated - User ID: " . $userid, $userid);
	header("Location: " . $_SERVER['PHP_SELF'] . ("?userid=" . $userid));
	exit();
}


if ($action == "addfunds") {
	check_token("WHMCS.admin.default");
	$addfundsamt = round($addfundsamt, 2);

	if (0 < $addfundsamt) {
		$invoiceid = createInvoices($userid);
		$paymentmethod = getClientsPaymentMethod($userid);
		insert_query("tblinvoiceitems", array("userid" => $userid, "type" => "AddFunds", "relid" => "", "description" => $_LANG['addfunds'], "amount" => $addfundsamt, "taxed" => "0", "duedate" => "now()", "paymentmethod" => $paymentmethod));
		$invoiceid = createInvoices($userid, "", true);
		header("Location: " . $PHP_SELF . "?userid=" . $userid . "&addfunds=true&invoiceid=" . $invoiceid);
	}
	else {
		header("Location: " . $PHP_SELF . "?userid=" . $userid);
	}

	exit();
}


if ($generateinvoices) {
	check_token("WHMCS.admin.default");
	checkPermission("Generate Due Invoices");
	$invoiceid = createInvoices($userid, $noemails);
	$_SESSION['adminclientgeninvoicescount'] = $invoicecount;
	header("Location: " . $PHP_SELF . "?userid=" . $userid . "&geninvoices=true");
	exit();
}


if ($activateaffiliate) {
	check_token("WHMCS.admin.default");
	affiliateActivate($userid);
	header("Location: " . $PHP_SELF . "?userid=" . $userid . "&affactivated=true");
	exit();
}


if ($resetpw) {
	check_token("WHMCS.admin.default");
	sendMessage("Automated Password Reset", $userid);
	header("Location: " . $PHP_SELF . "?userid=" . $userid . "&pwreset=true");
	exit();
}


if ($csajaxtoggle) {
	if (!checkPermission("Edit Clients Details", true)) {
		exit("Permission Denied");
	}


	if ($csajaxtoggle == "autocc") {
		$csajaxtoggle = "disableautocc";
	}
	else {
		if ($csajaxtoggle == "taxstatus") {
			$csajaxtoggle = "taxexempt";
		}
		else {
			if ($csajaxtoggle == "overduenotices") {
				$csajaxtoggle = "overideduenotices";
			}
			else {
				if ($csajaxtoggle == "latefees") {
					$csajaxtoggle = "latefeeoveride";
				}
				else {
					if ($csajaxtoggle == "splitinvoices") {
						$csajaxtoggle = "separateinvoices";
					}
					else {
						exit();
					}
				}
			}
		}
	}

	$csajaxtoggleval = get_query_val("tblclients", $csajaxtoggle, array("id" => $userid));

	if ($csajaxtoggleval == "on") {
		update_query("tblclients", array($csajaxtoggle => ""), array("id" => $userid));

		if ($csajaxtoggle == "taxexempt") {
			echo "<strong class=\"textred\">No</strong>";
		}
		else {
			echo "<strong class=\"textgreen\">Yes</strong>";
		}
	}
	else {
		update_query("tblclients", array($csajaxtoggle => "on"), array("id" => $userid));

		if ($csajaxtoggle == "taxexempt") {
			echo "<strong class=\"textgreen\">Yes</strong>";
		}
		else {
			echo "<strong class=\"textred\">No</strong>";
		}
	}

	exit();
}

releaseSession();
$clientsdetails = getClientsDetails($userid);
$currency = getCurrency($userid);
$jscode = "function openCCDetails() {
var winl = (screen.width - 340) / 2;
var wint = (screen.height - 575) / 2;
winprops = 'height=575,width=340,top='+wint+',left='+winl+',scrollbars=yes'
win = window.open('clientsccdetails.php?userid=" . $userid . "', 'ccdetails', winprops)
if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
}
function closeClient() {
if (confirm(\"" . $aInt->lang("clients", "closesure") . "\")) {
window.location='" . $PHP_SELF . "?userid=" . $userid . "&action=closeclient" . generate_token("link") . "';
}}
function deleteClient() {
if (confirm(\"" . $aInt->lang("clients", "deletesure") . "\")) {
window.location='" . $PHP_SELF . "?userid=" . $userid . "&action=deleteclient" . generate_token("link") . "';
}}
function deleteFile(id) {
if (confirm(\"" . $aInt->lang("clientsummary", "filedeletesure") . "\")) {
window.location='" . $PHP_SELF . "?userid=" . $userid . "&action=deletefile&id='+id;
}}";
$jquerycode = "$(\"#addfile\").click(function () {
    $(\"#addfileform\").slideToggle();
    return false;
});
$(\".csajaxtoggle\").click(function () {
	var csturl = \"clientssummary.php?userid=" . $userid . "&csajaxtoggle=\"+$(this).attr(\"id\");
	var cstelm = \"#\"+$(this).attr(\"id\");
	$.get(csturl, function(data){
		 $(cstelm).html(data);
	});
});
";
ob_start();

if ($geninvoices) {
	infoBox($aInt->lang("invoices", "gencomplete"), (int)$_SESSION['adminclientgeninvoicescount'] . " Invoices Created");
}


if ($addfunds) {
	infoBox($aInt->lang("clientsummary", "createaddfunds"), $aInt->lang("clientsummary", "createaddfundssuccess") . " - <a href=\"invoices.php?action=edit&id=" . (int)$invoiceid . "\">" . $aInt->lang("fields", "invoicenum") . $invoiceid . "</a>");
}


if ($pwreset) {
	infoBox($aInt->lang("clients", "resetsendpassword"), $aInt->lang("clients", "passwordsuccess"));
}


if ($affactivated) {
	infoBox($aInt->lang("clientsummary", "activateaffiliate"), $aInt->lang("clientsummary", "affiliateactivatesuccess"));
}

echo $infobox;
$clientstats = getClientsStats($userid);
$clientsdetails['status'] = $aInt->lang("status", strtolower($clientsdetails['status']));
$clientsdetails['autocc'] = ($clientsdetails['disableautocc'] ? $aInt->lang("global", "no") : $aInt->lang("global", "yes"));
$clientsdetails['taxstatus'] = ($clientsdetails['taxexempt'] ? $aInt->lang("global", "yes") : $aInt->lang("global", "no"));
$clientsdetails['overduenotices'] = ($clientsdetails['overideduenotices'] ? $aInt->lang("global", "no") : $aInt->lang("global", "yes"));
$clientsdetails['latefees'] = ($clientsdetails['latefeeoveride'] ? $aInt->lang("global", "no") : $aInt->lang("global", "yes"));
$clientsdetails['splitinvoices'] = ($clientsdetails['separateinvoices'] ? $aInt->lang("global", "yes") : $aInt->lang("global", "no"));
$templatevars['clientsdetails'] = $clientsdetails;
include "../includes/countries.php";
$templatevars['clientsdetails']['countrylong'] = $countries[$clientsdetails['country']];
$result = select_query("tblcontacts", "", array("userid" => $userid));
$contacts = array();

while ($data = mysql_fetch_array($result)) {
	$contacts[] = array("id" => $data['id'], "firstname" => $data['firstname'], "lastname" => $data['lastname'], "email" => $data['email']);
}

$templatevars['contacts'] = $contacts;
$result = select_query("tblclientgroups", "", array("id" => $clientsdetails['groupid']));
$data = mysql_fetch_array($result);
$groupname = $data['groupname'];
$groupcolour = $data['groupcolour'];

if (!$groupname) {
	$groupname = $aInt->lang("global", "none");
}

$templatevars['clientgroup'] = array("name" => $groupname, "colour" => $groupcolour);
$result = select_query("tblclients", "", array("id" => $userid));
$data = mysql_fetch_array($result);
$datecreated = $data['datecreated'];
$templatevars['signupdate'] = fromMySQLDate($datecreated);

if ($datecreated == "0000-00-00") {
	$clientfor = "Unknown";
}
else {
	$todaysdate = date("Ymd");
	$datecreated = strtotime($datecreated);
	$todaysdate = strtotime($todaysdate);
	$days = round(($datecreated - $todaysdate) / 86400);
	$clientfor = ceil($days / 30 * (0 - 1));

	if ($clientfor <= 0) {
		$clientfor = 0;
	}

	$clientfor .= " " . $aInt->lang("billableitems", "months");
}

$templatevars['clientfor'] = $clientfor;

if ($clientsdetails['lastlogin']) {
	$templatevars['lastlogin'] = $clientsdetails['lastlogin'];
}
else {
	$templatevars['lastlogin'] = $aInt->lang("global", "none");
}

$templatevars['stats'] = $clientstats;
$result = select_query("tblemails", "", array("userid" => $userid), "id", "DESC", "0,5");
$lastfivemail = array();

while ($data = mysql_fetch_array($result)) {
	$lastfivemail[] = array("id" => $data['id'], "date" => fromMySQLDate($data['date'], "time"), "subject" => ($data['subject'] ? $data['subject'] : "No Subject"));
}

$templatevars['lastfivemail'] = $lastfivemail;
$result = select_query("tblaffiliates", "", array("clientid" => $userid));
$data = mysql_fetch_array($result);
$affid = $data['id'];
$templatevars['affiliateid'] = $affid;

if ($affid) {
	$templatevars['afflink'] = "<a href=\"affiliates.php?action=edit&id=" . $affid . "\">" . $aInt->lang("clientsummary", "viewaffiliate") . "</a><br /><br />";
}
else {
	$templatevars['afflink'] = "<a href=\"clientssummary.php?userid=" . $userid . "&activateaffiliate=true\">" . $aInt->lang("clientsummary", "activateaffiliate") . "</a><br /><br />";
}

$templatevars['messages'] = "<select name=\"messagename\"><option value=\"newmessage\">" . $aInt->lang("global", "newmessage") . "</option>";
$query = "SELECT * FROM tblemailtemplates WHERE type='general' AND language='' AND name!='Password Reset Validation' ORDER BY name ASC";
$result = full_query($query);

while ($data = mysql_fetch_array($result)) {
	$messagename = $data['name'];
	$custom = $data['custom'];
	$templatevars->messages .= "<option value=\"" . $messagename . "\"";

	if ($custom == "1") {
		$templatevars->messages .= " style=\"background-color:#efefef\"";
	}

	$templatevars->messages .= ">" . $messagename . "</option>";
}

$templatevars->messages .= "</select>";
$recordsfound = "";
$productsummary = array();
$result = select_query("tblhosting", "tblhosting.*,tblproducts.name", array("userid" => $userid), "tblhosting`.`id", "DESC", "", "tblproducts ON tblproducts.id=tblhosting.packageid");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$regdate = $data['regdate'];
	$domain = $data['domain'];
	$dpackage = $data['name'];
	$dpaymentmethod = $data['paymentmethod'];
	$amount = formatCurrency($data['amount']);
	$billingcycle = $data['billingcycle'];
	$nextduedate = $data['nextduedate'];
	$status = $data['domainstatus'];
	$regdate = fromMySQLDate($regdate);
	$nextduedate = fromMySQLDate($nextduedate);

	if ($billingcycle == "One Time" || $billingcycle == "Free Account") {
		$nextduedate = "-";
		$amount = formatCurrency($data['firstpaymentamount']);
	}


	if ($domain == "") {
		$domain = "(" . $aInt->lang("addons", "nodomain") . ")";
	}

	$billingcycle = $aInt->lang("billingcycles", str_replace(array("-", "account", " "), "", strtolower($billingcycle)));
	$status = $aInt->lang("status", strtolower($status));
	$productsummary[] = array("id" => $id, "idshort" => ltrim($id, "0"), "regdate" => $regdate, "domain" => $domain, "dpackage" => $dpackage, "dpaymentmethod" => $dpaymentmethod, "amount" => $amount, "dbillingcycle" => $billingcycle, "nextduedate" => $nextduedate, "domainstatus" => $status);
}

$templatevars['productsummary'] = $productsummary;
$predefinedaddons = array();
$result = select_query("tbladdons", "", "");

while ($data = mysql_fetch_array($result)) {
	$addon_id = $data['id'];
	$addon_name = $data['name'];
	$predefinedaddons[$addon_id] = $addon_name;
}

$result = select_query("tblhostingaddons", "tblhostingaddons.*,tblhostingaddons.id AS aid,tblhostingaddons.name AS addonname,tblhosting.id AS hostingid,tblhosting.domain,tblproducts.name", array("tblhosting.userid" => $userid), "tblhosting`.`id", "DESC", "", "tblhosting ON tblhosting.id=tblhostingaddons.hostingid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid");
$addonsummary = array();

while ($data = mysql_fetch_array($result)) {
	$id = $data['aid'];
	$hostingid = $data['hostingid'];
	$addonid = $data['addonid'];
	$regdate = $data['regdate'];
	$domain = $data['domain'];
	$addonname = $data['addonname'];
	$dpackage = $data['name'];
	$dpaymentmethod = $data['paymentmethod'];
	$amount = formatCurrency($data['recurring']);
	$billingcycle = $data['billingcycle'];
	$nextduedate = $data['nextduedate'];

	if (!$addonname) {
		$addonname = $predefinedaddons[$addonid];
	}

	$regdate = fromMySQLDate($regdate);
	$nextduedate = fromMySQLDate($nextduedate);

	if ($dbillingcycle == "One Time" || $dbillingcycle == "Free Account") {
		$nextduedate = "-";
	}

	$status = $data['status'];

	if (!$domain) {
		$domain = "(" . $aInt->lang("addons", "nodomain") . ")";
	}

	$billingcycle = $aInt->lang("billingcycles", str_replace(array("-", "account", " "), "", strtolower($billingcycle)));
	$status = $aInt->lang("status", strtolower($status));
	$addonsummary[] = array("id" => $id, "idshort" => ltrim($id, "0"), "hostingid" => $hostingid, "serviceid" => $hostingid, "regdate" => $regdate, "domain" => $domain, "addonname" => $addonname, "dpackage" => $dpackage, "dpaymentmethod" => $dpaymentmethod, "amount" => $amount, "dbillingcycle" => $billingcycle, "nextduedate" => $nextduedate, "status" => $status);
}

$templatevars['addonsummary'] = $addonsummary;
$domainsummary = array();
$result = select_query("tbldomains", "", array("userid" => $userid), "id", "DESC");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$domain = $data['domain'];
	$registrar = ucfirst($data['registrar']);
	$registrationdate = $data['registrationdate'];
	$nextduedate = $data['nextduedate'];
	$expirydate = $data['expirydate'];
	$status = $data['status'];
	$registrationdate = fromMySQLDate($registrationdate);
	$nextduedate = fromMySQLDate($nextduedate);
	$expirydate = fromMySQLDate($expirydate);
	$status = $aInt->lang("status", strtolower(str_replace(" ", "", $status)));
	$domainsummary[] = array("id" => $id, "idshort" => ltrim($id, "0"), "domain" => $domain, "registrar" => $registrar, "registrationdate" => $registrationdate, "nextduedate" => $nextduedate, "expirydate" => $expirydate, "status" => $status);
}

$templatevars['domainsummary'] = $domainsummary;
$where['validuntil'] = array("sqltype" => ">", "value" => date("Ymd"));
$where['userid'] = $userid;
$result = select_query("tblquotes", "", $where);
$quotes = array();

while ($data = mysql_fetch_assoc($result)) {
	$id = $data['id'];
	$subject = $data['subject'];
	$datecreated = $data['datecreated'];
	$validuntil = $data['validuntil'];
	$stage = $data['stage'];
	$total = formatCurrency($data['total']);
	$datecreated = fromMySQLDate($datecreated);
	$validuntil = fromMySQLDate($validuntil);
	$quotes[] = array("id" => $id, "idshort" => ltrim($id, "0"), "datecreated" => $datecreated, "subject" => $subject, "stage" => $stage, "total" => $total, "validuntil" => $validuntil);
}

$templatevars['quotes'] = $quotes;
$result = select_query("tblclientsfiles", "", array("userid" => $userid), "title", "ASC");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$title = $data['title'];
	$adminonly = $data['adminonly'];
	$dateadded = $data['dateadded'];
	$dateadded = fromMySQLDate($dateadded);
	$files[] = array("id" => $id, "title" => $title, "adminonly" => $adminonly, "date" => $dateadded);
}

$templatevars['files'] = $files;
$paymentmethoddropdown = paymentMethodsSelection("- " . $aInt->lang("global", "nochange") . " -");
$templatevars['paymentmethoddropdown'] = $paymentmethoddropdown;
$templatevars['notes'] = array();
$result = select_query("tblnotes", "tblnotes.*,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE tbladmins.id=tblnotes.adminid) AS adminuser", array("userid" => $userid, "sticky" => "1"), "modified", "DESC");

while ($data = mysql_fetch_assoc($result)) {
	$data['created'] = fromMySQLDate($data['created'], 1);
	$data['modified'] = fromMySQLDate($data['modified'], 1);
	$data['note'] = autoHyperLink(nl2br($data['note']));
	$templatevars['notes'][] = $data;
}

$addons_html = run_hook("AdminAreaClientSummaryPage", array("userid" => $userid));
$templatevars['addons_html'] = $addons_html;
$tmplinks = run_hook("AdminAreaClientSummaryActionLinks", array("userid" => $userid));
$actionlinks = array();
foreach ($tmplinks as $tmplinks2) {
	foreach ($tmplinks2 as $tmplinks3) {
		$actionlinks[] = $tmplinks3;
	}
}

$templatevars['customactionlinks'] = $actionlinks;
$templatevars['tokenvar'] = generate_token("link");
$aInt->templatevars = $templatevars;
echo $aInt->getTemplate("clientssummary");
echo $aInt->jqueryDialog("geninvoices", $aInt->lang("invoices", "geninvoices"), $aInt->lang("invoices", "geninvoicessendemails"), array($aInt->lang("global", "yes") => "window.location='" . $PHP_SELF . "?userid=" . $userid . "&generateinvoices=true" . generate_token("link") . "'", $aInt->lang("global", "no") => "window.location='" . $PHP_SELF . "?userid=" . $userid . "&generateinvoices=true&noemails=true" . generate_token("link") . "'"));
echo $aInt->jqueryDialog("addfunds", $aInt->lang("clientsummary", "createaddfunds"), $aInt->lang("clientsummary", "createaddfundsdesc") . "<br /><div align=\"center\">" . $aInt->lang("fields", "amount") . ": <input type=\"text\" id=\"addfundsamt\" value=\"" . $CONFIG['AddFundsMinimum'] . "\" size=\"10\" /></div>", array($aInt->lang("global", "submit") => "window.location='" . $PHP_SELF . "?userid=" . $userid . "&action=addfunds" . generate_token("link") . ("&addfundsamt='+$('#addfundsamt').val()"), $aInt->lang("global", "cancel") => ""));
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>