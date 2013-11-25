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
require "includes/gatewayfunctions.php";
require "includes/quotefunctions.php";
require "includes/invoicefunctions.php";
require "includes/clientfunctions.php";
require "includes/countries.php";
$id = (int)$whmcs->get_req_var("id");

if (!isset($_SESSION['uid']) && !isset($_SESSION['adminid'])) {
	$pagetitle = $_LANG['clientareatitle'];
	$pageicon = "images/clientarea_big.gif";
	$pagetitle = $_LANG['quotestitle'];
	$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"clientarea.php\">" . $_LANG['clientareatitle'] . "</a> > <a href=\"clientarea.php?action=quotes\">" . $_LANG['quotes'] . "</a> > <a href=\"viewquote.php?id=" . $id . "\">" . $pagetitle . "</a>";
	initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);
	$goto = "viewquote";
	require "login.php";
	exit();
}


if (!class_exists("Smarty")) {
	require ROOTDIR . "/includes/smarty/Smarty.class.php";
}

$smarty = new Smarty();
$smarty->template_dir = "templates/" . $whmcs->get_sys_tpl_name() . "/";
$smarty->compile_dir = $templates_compiledir;
$smarty->assign("template", $whmcs->get_sys_tpl_name());
$smarty->assign("LANG", $_LANG);
$smarty->assign("logo", $CONFIG['LogoURL']);

if ($action == "accept") {
	if (!$agreetos && $CONFIG['EnableTOSAccept']) {
		$smarty->assign("agreetosrequired", true);
	}
	else {
		if (get_query_val("tblquotes", "", array("id" => $id, "userid" => $_SESSION['uid'], "stage" => array("sqltype" => "NEQ", "value" => "Draft"), "stage" => array("sqltype" => "NEQ", "value" => "Accepted")))) {
			update_query("tblquotes", array("stage" => "Accepted", "dateaccepted" => "now()"), array("id" => $id));
			logActivity("Quote Accepted - Quote ID: " . $id);
			$quote_data = get_query_vals("tblquotes", "*", array("id" => $id));

			if ($quote_data['userid']) {
				$clientsdetails = getClientsDetails($quote_data['userid'], "billing");
			}
			else {
				$clientsdetails = array();
				$clientsdetails['firstname'] = $data['firstname'];
				$clientsdetails['lastname'] = $data['lastname'];
				$clientsdetails['companyname'] = $data['companyname'];
				$clientsdetails['email'] = $data['email'];
				$clientsdetails['address1'] = $data['address1'];
				$clientsdetails['address2'] = $data['address2'];
				$clientsdetails['city'] = $data['city'];
				$clientsdetails['state'] = $data['state'];
				$clientsdetails['postcode'] = $data['postcode'];
				$clientsdetails['country'] = $data['country'];
				$clientsdetails['phonenumber'] = $data['phonenumber'];
			}

			sendMessage("Quote Accepted", $_SESSION['uid'], array("emailquote" => true, "quote_number" => $id, "quote_subject" => $quote_data['subject'], "quote_date_created" => $quote_data['datecreated'], "client_name" => $clientsdetails['firstname'] . " " . $clientsdetails['lastname'], "invoice_num" => ""));
			sendAdminMessage("Quote Accepted Notification", array("quote_number" => $id, "quote_subject" => $quote_data['subject'], "quote_date_created" => $quote_data['datecreated'], "client_id" => $vars['userid'], "clientname" => $clientsdetails['firstname'] . " " . $clientsdetails['lastname'], "client_email" => $clientsdetails['email'], "client_company_name" => $clientsdetails['companyname'], "client_address1" => $clientsdetails['address1'], "client_address2" => $clientsdetails['address2'], "client_city" => $clientsdetails['city'], "client_state" => $clientsdetails['state'], "client_postcode" => $clientsdetails['postcode'], "client_country" => $clientsdetails['country'], "client_phonenumber" => $clientsdetails['phonenumber'], "client_ip" => $clientsdetails['ip'], "client_hostname" => $clientsdetails['host']), "account");
			run_hook("acceptQuote", array("quoteid" => $id, "invoiceid" => $invoiceid));
		}
		else {
			$smarty->assign("error", "on");
			$template_output = $smarty->fetch("viewquote.tpl");
			echo $template_output;
			exit();
		}
	}
}


if (isset($_SESSION['adminid'])) {
	$result = select_query("tblquotes", "", array("id" => $id));
}
else {
	$result = select_query("tblquotes", "", array("id" => $id, "userid" => $_SESSION['uid'], "stage" => array("sqltype" => "NEQ", "value" => "Draft")));
}

$data = mysql_fetch_array($result);
$id = $data['id'];
$stage = $data['stage'];
$userid = $data['userid'];
$date = $data['datecreated'];
$validuntil = $data['validuntil'];
$subtotal = $data['subtotal'];
$total = $data['total'];
$status = $data['status'];
$proposal = $data['proposal'];
$notes = $data['customernotes'];
$currency = $data['currency'];

if (!$id) {
	$smarty->assign("error", "on");
	$template_output = $smarty->fetch("viewquote.tpl");
	echo $template_output;
	exit();
}

$date = fromMySQLDate($date, 0, 1);
$validuntil = fromMySQLDate($validuntil, 0, 1);

if ($userid) {
	$clientsdetails = getClientsDetails($userid, "billing");
}
else {
	$clientsdetails = array();
	$clientsdetails['firstname'] = $data['firstname'];
	$clientsdetails['lastname'] = $data['lastname'];
	$clientsdetails['companyname'] = $data['companyname'];
	$clientsdetails['email'] = $data['email'];
	$clientsdetails['address1'] = $data['address1'];
	$clientsdetails['address2'] = $data['address2'];
	$clientsdetails['city'] = $data['city'];
	$clientsdetails['state'] = $data['state'];
	$clientsdetails['postcode'] = $data['postcode'];
	$clientsdetails['country'] = $data['country'];
	$clientsdetails['phonenumber'] = $data['phonenumber'];
}


if ($CONFIG['TaxEnabled']) {
	$tax = $data['tax1'];
	$tax2 = $data['tax2'];
	$taxrate = get_query_val("tbltax", "taxrate", array("level" => 1, "state" => $clientsdetails['state'], "country" => $clientsdetails['country']));

	if (!$taxrate) {
		$taxrate = get_query_val("tbltax", "taxrate", array("level" => 1, "state" => "", "country" => ""));
	}

	$taxrate2 = get_query_val("tbltax", "taxrate", array("level" => 2, "state" => $clientsdetails['state'], "country" => $clientsdetails['country']));

	if (!$taxrate2) {
		$taxrate2 = get_query_val("tbltax", "taxrate", array("level" => 2, "state" => "", "country" => ""));
	}
}
else {
	$taxrate = "0.00";
	$taxrate2 = "0.00";
}

$clientsdetails['country'] = $countries[$clientsdetails['country']];
$smarty->assign("clientsdetails", $clientsdetails);
$smarty->assign("companyname", $CONFIG['CompanyName']);
$smarty->assign("pagetitle", $_LANG['quotenumber'] . $id);
$smarty->assign("quoteid", $id);
$smarty->assign("quotenum", $id);
$smarty->assign("payto", nl2br($CONFIG['InvoicePayTo']));
$smarty->assign("datecreated", $date);
$smarty->assign("datedue", $duedate);
$smarty->assign("subtotal", number_format(round($subtotal, 2), 2));
$smarty->assign("discount", $discount) . "%";
$smarty->assign("tax", number_format(round($tax, 2), 2));
$smarty->assign("tax2", number_format(round($tax2, 2), 2));
$smarty->assign("total", number_format(round($total, 2), 2));

if ($taxrate != "0.00") {
	$taxname = getTaxRate(1, $clientsdetails['state'], $clientsdetails['country']);
	$smarty->assign("taxname", $taxname['name']);
	$smarty->assign("taxrate", $taxrate);
}


if ($taxrate2 != "0.00") {
	$taxname = getTaxRate(2, $clientsdetails['state'], $clientsdetails['country']);
	$smarty->assign("taxname2", $taxname['name']);
	$smarty->assign("taxrate2", $taxrate2);
}

$smarty->assign("stage", $stage);
$smarty->assign("validuntil", $validuntil);
$quoteitems = array();
$result = select_query("tblquoteitems", "quantity,description,unitprice,discount,taxable", array("quoteid" => $id), "id", "ASC");

while ($data = mysql_fetch_array($result)) {
	$qty = $data[0];
	$description = $data[1];
	$unitprice = $data[2];
	$discountpc = $discount = $data[3];
	$taxed = ($data[4] ? true : false);

	if (1 < $qty) {
		$description = $qty . " x " . $description . " @ " . $unitprice . $_LANG['invoiceqtyeach'];
		$amount = $qty * $unitprice;
	}
	else {
		$amount = $unitprice;
	}

	$discount = $amount * $discount / 100;

	if ($discount) {
		$amount -= $discount;
	}

	$quoteitems[] = array("description" => nl2br($description), "unitprice" => number_format(round($unitprice, 2), 2), "discount" => number_format(round($discount, 2), 2), "discountpc" => $discountpc, "amount" => number_format(round($amount, 2), 2), "taxed" => $taxed);
}

$smarty->assign("id", $id);
$smarty->assign("quoteitems", $quoteitems);
$smarty->assign("proposal", nl2br($proposal));
$smarty->assign("notes", nl2br($notes));
$smarty->assign("accepttos", $CONFIG['EnableTOSAccept']);
$smarty->assign("tosurl", $CONFIG['TermsOfService']);
$template_output = $smarty->fetch("viewquote.tpl");
echo $template_output;
?>