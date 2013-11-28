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

if ($action == "add") {
	$reqperm = "Add Transaction";
}
else {
	if ($action == "edit") {
		$reqperm = "Edit Transaction";
	}
	else {
		$reqperm = "List Transactions";
	}
}

$aInt = new WHMCS_Admin($reqperm);
$aInt->inClientsProfile = true;
$aInt->requiredFiles(array("gatewayfunctions", "invoicefunctions"));
$aInt->valUserID($userid);

if ($sub == "add") {
	check_token("WHMCS.admin.default");

	if ($invoiceid) {
		$transuserid = get_query_val("tblinvoices", "userid", array("id" => $invoiceid));

		if (!$transuserid) {
			redir("error=invalidinvid");
		}
		else {
			if ($transuserid != $userid) {
				redir("error=wronguser");
			}
		}

		addInvoicePayment($invoiceid, $transid, $amountin, $fees, $paymentmethod, "", $date);
	}
	else {
		addTransaction($userid, 0, $description, $amountin, $fees, $amountout, $paymentmethod, $transid, $invoiceid, $date);
	}


	if ($addcredit) {
		if ($transid) {
			$description .= " (Trans ID: " . $transid . ")";
		}

		insert_query("tblcredit", array("clientid" => $userid, "date" => toMySQLDate($date), "description" => $description, "amount" => $amountin));
		$query = "UPDATE tblclients SET credit=credit+" . db_escape_string($amountin) . " WHERE id='" . db_escape_string($userid) . "'";
		full_query($query);
	}

	redir("userid=" . $userid);
	exit();
}


if ($sub == "save") {
	check_token("WHMCS.admin.default");
	update_query("tblaccounts", array("gateway" => $paymentmethod, "date" => toMySQLDate($date), "description" => $description, "amountin" => $amountin, "fees" => $fees, "amountout" => $amountout, "transid" => $transid, "invoiceid" => $invoiceid), array("id" => $id));
	logActivity("Modified Transaction (User ID: " . $userid . " - Transaction ID: " . $id . ")");
	redir("userid=" . $userid);
	exit();
}


if ($sub == "delete") {
	check_token("WHMCS.admin.default");
	checkPermission("Delete Transaction");
	delete_query("tblaccounts", array("id" => $ide));
	logActivity("Deleted Transaction (ID: " . $ide . " - User ID: " . $userid . ")");
	redir("userid=" . $userid);
	exit();
}

ob_start();

if ($action == "") {
	$aInt->deleteJSConfirm("doDelete", "transactions", "deletesure", "clientstransactions.php?userid=" . $userid . "&sub=delete&ide=");
	$currency = getCurrency($userid);

	if ($error == "invalidinvid") {
		infoBox("Check Invoice ID", "The Invoice ID you entered could not be found", "error");
	}
	else {
		if ($error == "wronguser") {
			infoBox("Check Invoice ID", "The Invoice ID you entered to assign this payment to belongs to a different client", "error");
		}
	}

	echo $infobox;
	$result = select_query("tblaccounts", "SUM(amountin),SUM(fees),SUM(amountout),SUM(amountin-fees-amountout)", array("userid" => $userid));
	$data = mysql_fetch_array($result);
	echo "
<table width=90% cellspacing=1 cellpadding=5 bgcolor=\"#CCCCCC\" align=\"center\"><tr bgcolor=\"#f4f4f4\" style=\"text-align:center\"><td><a href=\"";
	echo $PHP_SELF;
	echo "?userid=";
	echo $userid;
	echo "&action=add\">";
	echo $aInt->lang("transactions", "addnew");
	echo "</a></td><td>";
	echo $aInt->lang("transactions", "totalin");
	echo ": ";
	echo formatCurrency($data[0]);
	echo "</td><td>";
	echo $aInt->lang("transactions", "totalfees");
	echo ": ";
	echo formatCurrency($data[1]);
	echo "</td><td>";
	echo $aInt->lang("transactions", "totalout");
	echo ": ";
	echo formatCurrency($data[2]);
	echo "</td><td><B>";
	echo $aInt->lang("fields", "balance");
	echo ": ";
	echo formatCurrency($data[3]);
	echo "</B><br></td></tr></table>

<br>

";
	$aInt->sortableTableInit("date", "DESC");
	$result = select_query("tblaccounts", "COUNT(*)", array("userid" => $userid));
	$data = mysql_fetch_array($result);
	$numrows = $data[0];
	$result = select_query("tblaccounts", "", array("userid" => $userid), $orderby, $order, $page * $limit . ("," . $limit));

	while ($data = mysql_fetch_array($result)) {
		$ide = $data['id'];
		$date = $data['date'];
		$date = fromMySQLDate($date);
		$gateway = $data['gateway'];
		$description = $data['description'];
		$amountin = $data['amountin'];
		$fees = $data['fees'];
		$amountout = $data['amountout'];
		$transid = $data['transid'];
		$invoiceid = $data['invoiceid'];
		$totalin = $totalin + $amountin;
		$totalout = $totalout + $amountout;
		$totalfees = $totalfees + $fees;
		$amountin = formatCurrency($amountin);
		$fees = formatCurrency($fees);
		$amountout = formatCurrency($amountout);

		if ($invoiceid != "0") {
			$description .= " (<a href=\"invoices.php?action=edit&id=" . $invoiceid . "\">#" . $invoiceid . "</a>)";
		}


		if ($transid != "") {
			$description .= " - Trans ID: " . $transid;
		}

		$result2 = select_query("tblpaymentgateways", "", array("gateway" => $gateway, "setting" => "name"));
		$data = mysql_fetch_array($result2);
		$gateway = $data['value'];
		$tabledata[] = array($date, $gateway, $description, $amountin, $fees, $amountout, "<a href=\"" . $PHP_SELF . "?userid=" . $userid . "&action=edit&id=" . $ide . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Edit\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $ide . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Delete\"></a>");
	}

	echo $aInt->sortableTable(array(array("date", $aInt->lang("fields", "date")), array("gateway", $aInt->lang("fields", "paymentmethod")), array("description", $aInt->lang("fields", "description")), array("amountin", $aInt->lang("transactions", "amountin")), array("fees", $aInt->lang("transactions", "fees")), array("amountout", $aInt->lang("transactions", "amountout")), "", ""), $tabledata);
}
else {
	if ($action == "add") {
		$date2 = getTodaysDate();
		echo "
<p><b>";
		echo $aInt->lang("transactions", "addnew");
		echo "</b></p>

<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?userid=";
		echo $userid;
		echo "&sub=add\" name=\"calendarfrm\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("fields", "date");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"date\" value=\"";
		echo $date2;
		echo "\" class=\"datepick\"></td><td class=\"fieldlabel\" width=\"15%\">";
		echo $aInt->lang("transactions", "amountin");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"amountin\" size=10 value=\"0.00\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "description");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"description\" size=50></td><td class=\"fieldlabel\">";
		echo $aInt->lang("transactions", "fees");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"fees\" size=10 value=\"0.00\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "transid");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"transid\" size=30></td><td class=\"fieldlabel\">";
		echo $aInt->lang("transactions", "amountout");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"amountout\" size=10 value=\"0.00\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "invoiceid");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"invoiceid\" size=10></td><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "credit");
		echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"addcredit\"> ";
		echo $aInt->lang("invoices", "refundtypecredit");
		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "paymentmethod");
		echo "</td><td class=\"fieldarea\">";
		echo paymentMethodsSelection($aInt->lang("global", "none"));
		echo "</td><td class=\"fieldlabel\"></td><td class=\"fieldarea\"></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("transactions", "add");
		echo "\" class=\"button\"></p>

</form>

";
	}
	else {
		if ($action == "edit") {
			$result = select_query("tblaccounts", "", array("id" => $id));
			$data = mysql_fetch_array($result);
			$id = $data['id'];
			$date = $data['date'];
			$date = fromMySQLDate($date);
			$description = $data['description'];
			$amountin = $data['amountin'];
			$fees = $data['fees'];
			$amountout = $data['amountout'];
			$paymentmethod = $data['gateway'];
			$transid = $data['transid'];
			$invoiceid = $data['invoiceid'];
			echo "
<p><b>";
			echo $aInt->lang("transactions", "edit");
			echo "</b></p>

<form method=\"post\" action=\"";
			echo $PHP_SELF;
			echo "?userid=";
			echo $userid;
			echo "&sub=save&id=";
			echo $id;
			echo "\" name=\"calendarfrm\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
			echo $aInt->lang("fields", "date");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"12\" name=\"date\" value=\"";
			echo $date;
			echo "\" class=\"datepick\"></td><td width=\"15%\" class=\"fieldlabel\" width=110>";
			echo $aInt->lang("fields", "transid");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"transid\" size=20 value=\"";
			echo $transid;
			echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("fields", "paymentmethod");
			echo "</td><td class=\"fieldarea\">";
			echo paymentMethodsSelection($aInt->lang("global", "none"));
			echo "</td><td class=\"fieldlabel\">";
			echo $aInt->lang("transactions", "amountin");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"amountin\" size=10 value=\"";
			echo $amountin;
			echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("fields", "description");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"description\" size=50 value=\"";
			echo $description;
			echo "\"></td><td class=\"fieldlabel\">";
			echo $aInt->lang("transactions", "fees");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"fees\" size=10 value=\"";
			echo $fees;
			echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("fields", "invoiceid");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"invoiceid\" size=8 value=\"";
			echo $invoiceid;
			echo "\"></td><td class=\"fieldlabel\">";
			echo $aInt->lang("transactions", "amountout");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"amountout\" size=10 value=\"";
			echo $amountout;
			echo "\"></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
			echo $aInt->lang("global", "savechanges");
			echo "\" class=\"button\"></p>

</form>

";
		}
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>