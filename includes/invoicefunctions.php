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

function getInvoiceStatusColour($status, $clientarea = true) {
	if (!$clientarea) {
		global $aInt;

		if ($status == "Unpaid") {
			$status = "<span class=\"textred\">" . $aInt->lang("status", "unpaid") . "</span>";
		}
		else {
			if ($status == "Paid") {
				$status = "<span class=\"textgreen\">" . $aInt->lang("status", "paid") . "</span>";
			}
			else {
				if ($status == "Cancelled") {
					$status = "<span class=\"textgrey\">" . $aInt->lang("status", "cancelled") . "</span>";
				}
				else {
					if ($status == "Refunded") {
						$status = "<span class=\"textblack\">" . $aInt->lang("status", "refunded") . "</span>";
					}
					else {
						if ($status == "Collections") {
							$status = "<span class=\"textgold\">" . $aInt->lang("status", "collections") . "</span>";
						}
					}
				}
			}
		}
	}
	else {
		global $_LANG;

		if ($status == "Unpaid") {
			$status = "<span class=\"textred\">" . $_LANG['invoicesunpaid'] . "</span>";
		}
		else {
			if ($status == "Paid") {
				$status = "<span class=\"textgreen\">" . $_LANG['invoicespaid'] . "</span>";
			}
			else {
				if ($status == "Cancelled") {
					$status = "<span class=\"textgrey\">" . $_LANG['invoicescancelled'] . "</span>";
				}
				else {
					if ($status == "Refunded") {
						$status = "<span class=\"textblack\">" . $_LANG['invoicesrefunded'] . "</span>";
					}
					else {
						if ($status == "Collections") {
							$status = "<span class=\"textgold\">" . $_LANG['invoicescollections'] . "</span>";
						}
					}
				}
			}
		}
	}

	return $status;
}

function getInvoicePayUntilDate($nextduedate, $billingcycle, $fulldate = "") {
	$year = substr($nextduedate, 0, 4);
	$month = substr($nextduedate, 5, 2);
	$day = substr($nextduedate, 8, 2);
	$daysadjust = $months = 0;
	$months = (is_numeric($billingcycle) ? $billingcycle * 12 : getBillingCycleMonths($billingcycle));

	if (!$fulldate) {
		$daysadjust = 1;
	}

	$new_time = mktime(0, 0, 0, $month + $months, $day - $daysadjust, $year);
	$invoicepayuntildate = ($billingcycle != "One Time" ? date("Y-m-d", $new_time) : "");
	return $invoicepayuntildate;
}

function addTransaction($userid, $currencyid, $description, $amountin, $fees, $amountout, $gateway = "", $transid = "", $invoiceid = "", $date = "", $refundid = "", $rate = "") {
	$date = ($date ? toMySQLDate($date) . date(" H:i:s") : "now()");

	if ($userid) {
		$currency = getCurrency($userid);
		$currencyid = $currency['id'];
	}


	if (!$rate) {
		$result = select_query("tblcurrencies", "rate", array("id" => $currencyid));
		$data = mysql_fetch_array($result);
		$rate = $data['rate'];
	}


	if ($userid) {
		$currencyid = 0;
	}

	$array = array("userid" => $userid, "currency" => $currencyid, "gateway" => $gateway, "date" => $date, "description" => $description, "amountin" => $amountin, "fees" => $fees, "amountout" => $amountout, "rate" => $rate, "transid" => $transid, "invoiceid" => $invoiceid, "refundid" => $refundid);
	$saveid = insert_query("tblaccounts", $array);
	logActivity("Added Transaction - Transaction ID: " . $saveid);
	$array['id'] = $saveid;
	run_hook("AddTransaction", $array);
}

function updateInvoiceTotal($id) {
	global $CONFIG;

	$result = select_query("tblinvoiceitems", "", array("invoiceid" => $id));

	while ($data = mysql_fetch_array($result)) {
		if ($data['taxed'] == "1") {
			$taxsubtotal += $data['amount'];
		}

		$nontaxsubtotal += $data['amount'];
	}

	$subtotal = $total = $nontaxsubtotal + $taxsubtotal;
	$result = select_query("tblinvoices", "userid,credit,taxrate,taxrate2", array("id" => $id));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$credit = $data['credit'];
	$taxrate = $data['taxrate'];
	$taxrate2 = $data['taxrate2'];

	if (!function_exists("getClientsDetails")) {
		require_once dirname(__FILE__) . "/clientfunctions.php";
	}

	$clientsdetails = getClientsDetails($userid);
	$tax = $tax2 = 0;

	if ($CONFIG['TaxEnabled'] == "on" && !$clientsdetails['taxexempt']) {
		if ($taxrate != "0.00") {
			if ($CONFIG['TaxType'] == "Inclusive") {
				$taxrate = $taxrate / 100 + 1;
				$calc1 = $taxsubtotal / $taxrate;
				$tax = $taxsubtotal - $calc1;
			}
			else {
				$taxrate = $taxrate / 100;
				$tax = $taxsubtotal * $taxrate;
			}
		}


		if ($taxrate2 != "0.00") {
			if ($CONFIG['TaxL2Compound']) {
				$taxsubtotal += $tax;
			}


			if ($CONFIG['TaxType'] == "Inclusive") {
				$taxrate2 = $taxrate2 / 100 + 1;
				$calc1 = $taxsubtotal / $taxrate2;
				$tax2 = $taxsubtotal - $calc1;
			}
			else {
				$taxrate2 = $taxrate2 / 100;
				$tax2 = $taxsubtotal * $taxrate2;
			}
		}

		$tax = round($tax, 2);
		$tax2 = round($tax2, 2);
	}


	if ($CONFIG['TaxType'] == "Inclusive") {
		$subtotal = $subtotal - $tax - $tax2;
	}
	else {
		$total = $subtotal + $tax + $tax2;
	}


	if (0 < $credit) {
		if ($total < $credit) {
			$total = 0;
			$remainingcredit = $total - $credit;
		}
		else {
			$total -= $credit;
		}
	}

	$subtotal = format_as_currency($subtotal);
	$tax = format_as_currency($tax);
	$total = format_as_currency($total);
	update_query("tblinvoices", array("subtotal" => $subtotal, "tax" => $tax, "tax2" => $tax2, "total" => $total), array("id" => $id));
	run_hook("UpdateInvoiceTotal", array("invoiceid" => $id));
}

function addInvoicePayment($invoiceid, $transid, $amount, $fees, $gateway, $noemail = "", $date = "") {
	$result = select_query("tblinvoices", "userid,total,status", array("id" => $invoiceid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$total = $data['total'];
	$status = $data['status'];
	$result = select_query("tblaccounts", "SUM(amountin)-SUM(amountout)", array("invoiceid" => $invoiceid));
	$data = mysql_fetch_array($result);
	$amountpaid = $data[0];
	$balance = $total - $amountpaid;

	if (!$amount) {
		$amount = $balance;

		if ($amount <= 0) {
			return false;
		}
	}

	addTransaction($userid, 0, "Invoice Payment", $amount, $fees, 0, $gateway, $transid, $invoiceid, $date);
	$balance = format_as_currency($balance);
	$amount = format_as_currency($amount);
	$balance -= $amount;
	logActivity("Added Invoice Payment - Invoice ID: " . $invoiceid, $userid);
	run_hook("AddInvoicePayment", array("invoiceid" => $invoiceid));

	if ($balance <= 0 && $status == "Unpaid") {
		processPaidInvoice($invoiceid, $noemail, $date);
	}
	else {
		if (!$noemail) {
			sendMessage("Invoice Payment Confirmation", $invoiceid);
		}
	}


	if ($balance <= 0) {
		$result2 = select_query("tblcredit", "sum(amount)", array("relid" => $invoiceid));
		$data2 = mysql_fetch_array($result2);
		$amountcredited = $data2[0];
		$balance = $balance + $amountcredited;

		if ($balance < 0) {
			$balance = $balance * (0 - 1);
			insert_query("tblcredit", array("clientid" => $userid, "date" => "now()", "description" => "Invoice #" . $invoiceid . " Overpayment", "amount" => $balance, "relid" => $invoiceid));
			update_query("tblclients", array("credit" => "+=" . $balance), array("id" => $userid));
		}
	}

}

function refundInvoicePayment($transid, $amount, $sendtogateway, $addascredit = "", $sendemail = true, $refundtransid = "") {
	$result = select_query("tblaccounts", "", array("id" => $transid));
	$data = mysql_fetch_array($result);
	$transid = $data['id'];

	if (!$transid) {
		return "amounterror";
	}

	$userid = $data['userid'];
	$invoiceid = $data['invoiceid'];
	$gateway = $data['gateway'];
	$fullamount = $data['amountin'];
	$fees = $data['fees'];
	$gatewaytransid = $data['transid'];
	$rate = $data['rate'];
	$gateway = WHMCS_Gateways::makesafename($gateway);
	$result = select_query("tblaccounts", "SUM(amountout),SUM(fees)", array("refundid" => $transid));
	$data = mysql_fetch_array($result);
	$alreadyrefunded = $data[0];
	$alreadyrefundedfees = $data[1];
	$fullamount -= $alreadyrefunded;
	$fees -= $alreadyrefundedfees * (0 - 1);

	if ($fees <= 0) {
		$fees = 0;
	}

	$result = select_query("tblaccounts", "SUM(amountin),SUM(amountout)", array("invoiceid" => $invoiceid));
	$data = mysql_fetch_array($result);
	$invoicetotalpaid = $data[0];
	$invoicetotalrefunded = $data[1];

	if (!$amount) {
		$amount = $fullamount;
	}


	if (!$amount || $fullamount < $amount) {
		return "amounterror";
	}

	$amount = format_as_currency($amount);

	if ($addascredit) {
		addTransaction($userid, 0, "Refund of Transaction ID " . $gatewaytransid . " to Credit Balance", 0, $fees * (0 - 1), $amount, "", "", $invoiceid, "", $transid, $rate);
		addTransaction($userid, 0, "Credit from Refund of Invoice ID " . $invoiceid, $amount, $fees, 0, "", "", "", "", "", "");
		logActivity("Refunded Invoice Payment to Credit Balance - Invoice ID: " . $invoiceid, $userid);
		insert_query("tblcredit", array("clientid" => $userid, "date" => "now()", "description" => "Credit from Refund of Invoice ID " . $invoiceid, "amount" => $amount));
		update_query("tblclients", array("credit" => "+=" . $amount), array("id" => (int)$userid));

		if ($invoicetotalpaid - $invoicetotalrefunded - $amount <= 0) {
			update_query("tblinvoices", array("status" => "Refunded"), array("id" => $invoiceid));
			run_hook("InvoiceRefunded", array("invoiceid" => $invoiceid));
		}


		if ($sendemail) {
			sendMessage("Invoice Refund Confirmation", $invoiceid, array("invoice_refund_type" => "credit"));
		}

		return "creditsuccess";
	}

	$result = select_query("tblpaymentgateways", "value", array("gateway" => $gateway, "setting" => "convertto"));
	$data = mysql_fetch_array($result);
	$convertto = $data['value'];

	if ($convertto) {
		$result = select_query("tblclients", "currency", array("id" => $userid));
		$data = mysql_fetch_array($result);
		$fromcurrencyid = $data['currency'];
		$convertedamount = convertCurrency($amount, $fromcurrencyid, $convertto, $rate);
	}


	if ($gateway) {
		$params = getCCVariables($invoiceid);
	}


	if ($sendtogateway && function_exists($gateway . "_refund")) {
		$params['amount'] = ($convertedamount ? $convertedamount : $amount);
		$params['transid'] = $gatewaytransid;
		$params['paymentmethod'] = $gateway;
		$gatewayresult = call_user_func($gateway . "_refund", $params);
		$refundtransid = $gatewayresult['transid'];
		$rawdata = $gatewayresult['rawdata'];

		if (isset($gatewayresult['fees'])) {
			$fees = $gatewayresult['fees'];
		}

		$gatewayresult = $gatewayresult['status'];
		$result = select_query("tblpaymentgateways", "value", array("gateway" => $gateway, "setting" => "name"));
		$data = mysql_fetch_array($result);
		$gatewayname = $data['value'];
		logTransaction($gatewayname . " Refund", $rawdata, ucfirst($gatewayresult));
	}
	else {
		$gatewayresult = "manual";
		run_hook("ManualRefund", array("transid" => $transid, "amount" => $amount));
	}


	if ($gatewayresult == "success" || $gatewayresult == "manual") {
		addTransaction($userid, 0, "Refund of Transaction ID " . $gatewaytransid, 0, $fees * (0 - 1), $amount, $gateway, $refundtransid, $invoiceid, "", $transid, $rate);
		logActivity("Refunded Invoice Payment - Invoice ID: " . $invoiceid . " - Transaction ID: " . $transid, $userid);
		$result = select_query("tblinvoices", "total", array("id" => $invoiceid));
		$data = mysql_fetch_array($result);
		$invoicetotal = $data[0];

		if ($invoicetotalpaid - $invoicetotalrefunded - $amount <= 0) {
			update_query("tblinvoices", array("status" => "Refunded"), array("id" => $invoiceid));
			run_hook("InvoiceRefunded", array("invoiceid" => $invoiceid));
		}


		if ($sendemail) {
			sendMessage("Invoice Refund Confirmation", $invoiceid, array("invoice_refund_type" => "gateway"));
		}
	}

	return $gatewayresult;
}

function processPaidInvoice($invoiceid, $noemail = "", $date = "") {
	global $CONFIG;

	$result = select_query("tblinvoices", "invoicenum,userid,status", array("id" => $invoiceid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$invoicestatus = $data['status'];
	$invoicenum = $data['invoicenum'];

	if ($invoicestatus != "Unpaid") {
		return false;
	}

	$date = ($date ? toMySQLDate($date) . date(" H:i:s") : "now()");
	update_query("tblinvoices", array("status" => "Paid", "datepaid" => $date), array("id" => $invoiceid));
	logActivity("Invoice Marked Paid - Invoice ID: " . $invoiceid, $userid);

	if ($CONFIG['SequentialInvoiceNumbering'] && !$invoicenum) {
		$invoicenumber = $CONFIG['SequentialInvoiceNumberFormat'];
		$invnumval = get_query_val("tblconfiguration", "value", array("setting" => "SequentialInvoiceNumberValue"));
		update_query("tblconfiguration", array("value" => "+1"), array("setting" => "SequentialInvoiceNumberValue"));
		$invoicenumber = str_replace("{NUMBER}", $invnumval, $invoicenumber);
		update_query("tblinvoices", array("invoicenum" => $invoicenumber), array("id" => $invoiceid));
		++$CONFIG['SequentialInvoiceNumberValue'];
	}

	run_hook("InvoicePaidPreEmail", array("invoiceid" => $invoiceid));

	if (!$noemail) {
		sendMessage("Invoice Payment Confirmation", $invoiceid);
	}

	$result = select_query("tblinvoiceitems", "", "invoiceid='" . mysql_real_escape_string($invoiceid) . "' AND type!=''", "id", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$userid = $data['userid'];
		$type = $data['type'];
		$relid = $data['relid'];
		$amount = $data['amount'];

		if ($type == "Hosting") {
			makeHostingPayment($relid);
		}


		if (($type == "DomainRegister" || $type == "DomainTransfer") || $type == "Domain") {
			makeDomainPayment($relid, $type);
		}


		if ($type == "DomainAddonDNS") {
			$enabledcheck = get_query_val("tbldomains", "dnsmanagement", array("id" => $relid));

			if (!$enabledcheck) {
				$currency = getCurrency($userid);
				$dnscost = get_query_val("tblpricing", "msetupfee", array("type" => "domainaddons", "currency" => $currency['id'], "relid" => 0));
				update_query("tbldomains", array("dnsmanagement" => "on", "recurringamount" => "+=" . $dnscost), array("id" => $relid));
			}
		}


		if ($type == "DomainAddonEMF") {
			$enabledcheck = get_query_val("tbldomains", "emailforwarding", array("id" => $relid));

			if (!$enabledcheck) {
				$currency = getCurrency($userid);
				$emfcost = get_query_val("tblpricing", "qsetupfee", array("type" => "domainaddons", "currency" => $currency['id'], "relid" => 0));
				update_query("tbldomains", array("emailforwarding" => "on", "recurringamount" => "+=" . $emfcost), array("id" => $relid));
			}
		}


		if ($type == "DomainAddonIDP") {
			$enabledcheck = get_query_val("tbldomains", "idprotection", array("id" => $relid));

			if (!$enabledcheck) {
				$currency = getCurrency($userid);
				$idpcost = get_query_val("tblpricing", "ssetupfee", array("type" => "domainaddons", "currency" => $currency['id'], "relid" => 0));
				update_query("tbldomains", array("idprotection" => "on", "recurringamount" => "+=" . $idpcost), array("id" => $relid));
				$data = get_query_vals("tbldomains", "type,domain,registrar,registrationperiod", array("id" => $relid));
				$domainparts = explode(".", $data['domain'], 2);
				$params = array();
				$params['domainid'] = $relid;
				$params['sld'] = $domainparts[0];
				$params['tld'] = $domainparts[1];
				$params['regperiod'] = $data['registrationperiod'];
				$params['registrar'] = $data['registrar'];
				$params['regtype'] = $data['type'];

				if (!function_exists("RegIDProtectToggle")) {
					require ROOTDIR . "/includes/registrarfunctions.php";
				}

				$values = RegIDProtectToggle($params);

				if ($values['error']) {
					logActivity("ID Protection Enabling Failed - Error: " . $values['error'] . " - Domain ID: " . $relid, $userid);
				}

				logActivity("ID Protection Enabled Successfully - Domain ID: " . $relid, $userid);
			}
		}


		if ($type == "Addon") {
			makeAddonPayment($relid);
		}


		if ($type == "Upgrade") {
			if (!function_exists("processUpgradePayment")) {
				require dirname(__FILE__) . "/upgradefunctions.php";
			}

			processUpgradePayment($relid, "", "", "true");
		}


		if ($type == "AddFunds") {
			insert_query("tblcredit", array("clientid" => $userid, "date" => "now()", "description" => "Add Funds Invoice #" . $invoiceid, "amount" => $amount, "relid" => $invoiceid));
			update_query("tblclients", array("credit" => "+=" . $amount), array("id" => (int)$userid));
		}


		if ($type == "Invoice") {
			insert_query("tblcredit", array("clientid" => $userid, "date" => "now()", "description" => "Mass Invoice Payment Credit for Invoice #" . $relid, "amount" => $amount));
			update_query("tblclients", array("credit" => "+=" . $amount), array("id" => (int)$userid));
			applyCredit($relid, $userid, $amount);
		}


		if (substr($type, 0, 14) == "ProrataProduct") {
			$newduedate = substr($type, 14);
			update_query("tblhosting", array("nextduedate" => $newduedate, "nextinvoicedate" => $newduedate), array("id" => $relid));
		}


		if (substr($type, 0, 12) == "ProrataAddon") {
			$newduedate = substr($type, 12);
			update_query("tblhostingaddons", array("nextduedate" => $newduedate, "nextinvoicedate" => $newduedate), array("id" => $relid));
		}
	}

	run_hook("InvoicePaid", array("invoiceid" => $invoiceid));
}

function getTaxRate($level, $state, $country) {
	global $_LANG;

	$result = select_query("tbltax", "", array("level" => $level, "state" => $state, "country" => $country));
	$data = mysql_fetch_array($result);
	$taxname = $data['name'];
	$taxrate = $data['taxrate'];

	if (!$taxrate) {
		$result = select_query("tbltax", "", array("level" => $level, "state" => "", "country" => $country));
		$data = mysql_fetch_array($result);
		$taxname = $data['name'];
		$taxrate = $data['taxrate'];
	}


	if (!$taxrate) {
		$result = select_query("tbltax", "", array("level" => $level, "state" => "", "country" => ""));
		$data = mysql_fetch_array($result);
		$taxname = $data['name'];
		$taxrate = $data['taxrate'];
	}


	if (!$taxrate) {
		$taxrate = 0;
	}


	if (!$taxname) {
		$taxname = $_LANG['invoicestax'];
	}

	return array("name" => $taxname, "rate" => $taxrate);
}

function pdfInvoice($invoiceid) {
	global $whmcs;
	global $CONFIG;
	global $_LANG;
	global $currency;

	$invoice = new WHMCS_Invoice();
	$invoice->pdfCreate();
	$invoice->pdfInvoicePage($invoiceid);
	$pdfdata = $invoice->pdfOutput();
	return $pdfdata;
}

function makeHostingPayment($func_domainid) {
	global $CONFIG;
	global $disable_to_do_list_entries;

	$result = select_query("tblhosting", "", array("id" => $func_domainid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$billingcycle = $data['billingcycle'];
	$domain = $data['domain'];
	$packageid = $data['packageid'];
	$regdate = $data['regdate'];
	$nextduedate = $data['nextduedate'];
	$status = $data['domainstatus'];
	$server = $data['server'];
	$paymentmethod = $data['paymentmethod'];
	$suspendreason = $data['suspendreason'];
	$result = select_query("tblproducts", "", array("id" => $packageid));
	$data = mysql_fetch_array($result);
	$producttype = $data['type'];
	$productname = $data['name'];
	$module = $data['servertype'];
	$proratabilling = $data['proratabilling'];
	$proratadate = $data['proratadate'];
	$proratachargenextmonth = $data['proratachargenextmonth'];
	$autosetup = $data['autosetup'];

	if ($regdate == $nextduedate && $proratabilling) {
		$orderyear = substr($regdate, 0, 4);
		$ordermonth = substr($regdate, 5, 2);
		$orderday = substr($regdate, 8, 2);
		$proratavalues = getProrataValues($billingcycle, $product_onetime, $proratadate, $proratachargenextmonth, $orderday, $ordermonth, $orderyear, $userid);
		$nextduedate = $proratavalues['date'];
	}
	else {
		$nextduedate = getInvoicePayUntilDate($nextduedate, $billingcycle, true);
	}

	update_query("tblhosting", array("nextduedate" => $nextduedate, "nextinvoicedate" => $nextduedate), array("id" => $func_domainid));

	if (!function_exists("getModuleType")) {
		include dirname(__FILE__) . "/modulefunctions.php";
	}


	if (($status == "Pending" && $autosetup == "payment") && $module) {
		if (getNewClientAutoProvisionStatus($userid)) {
			logActivity("Running Module Create on Payment", $userid);
			$result = ServerCreateAccount($func_domainid);

			if ($result == "success") {
				sendMessage("defaultnewacc", $func_domainid);
				sendAdminMessage("Automatic Setup Successful", array("client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => ""), "account");
			}
			else {
				sendAdminMessage("Automatic Setup Failed", array("client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => $result), "account");
			}
		}
		else {
			logActivity("Module Create on Payment Suppressed for New Client", $userid);
		}
	}

	$suspenddate = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $CONFIG['AutoSuspensionDays'], date("Y")));

	if (((($status == "Suspended" && $CONFIG['AutoUnsuspend'] == "on") && $module) && !$suspendreason) && $suspenddate <= str_replace("-", "", $nextduedate)) {
		logActivity("Running Auto Unsuspend on Payment", $userid);
		$moduleresult = ServerUnsuspendAccount($func_domainid);

		if ($moduleresult == "success") {
			sendAdminMessage("Service Unsuspension Successful", array("client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => ""), "account");
		}
		else {
			sendAdminMessage("Service Unsuspension Failed", array("client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => $moduleresult), "account");

			if (!$disable_to_do_list_entries) {
				insert_query("tbltodolist", array("date" => "now()", "title" => "Manual Unsuspend Required", "description" => "The order placed for " . $domain . " has received its next payment and the automatic unsuspend has failed<br />Client ID: " . $userid . "<br>Product/Service: " . $productname . "<br>Domain: " . $domain, "admin" => "", "status" => "Pending", "duedate" => date("Y-m-d")));
			}
		}
	}


	if ($status != "Pending") {
		ServerRenew($func_domainid);
	}

	AffiliatePayment("", $func_domainid);
	$result = select_query("tblhostingaddons", "id,addonid", "hostingid=" . (int)$func_domainid . " AND addonid>0 AND billingcycle IN ('Free','Free Account') AND status='Pending'");

	while ($data = mysql_fetch_array($result)) {
		$aid = $data['id'];
		$addonid = $data['addonid'];
		$result = select_query("tbladdons", "autoactivate,welcomeemail", array("id" => $addonid));
		$data = mysql_fetch_array($result);
		$autoactivate = $data['autoactivate'];
		$welcomeemail = $data['welcomeemail'];

		if ($autoactivate) {
			update_query("tblhostingaddons", array("status" => "Active"), array("id" => $aid));

			if ($welcomeemail) {
				$result = select_query("tblemailtemplates", "name", array("id" => $welcomeemail));
				$data = mysql_fetch_array($result);
				$welcomeemailname = $data['name'];
				sendMessage($welcomeemailname, $func_domainid);
			}

			run_hook("AddonActivation", array("id" => $aid, "userid" => $userid, "serviceid" => $func_domainid, "addonid" => $addonid));
		}
	}

}

function makeDomainPayment($func_domainid, $type = "") {
	global $whmcs;

	$result = select_query("tbldomains", "", array("id" => $func_domainid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$orderid = $data['orderid'];
	$registrationperiod = $data['registrationperiod'];
	$registrationdate = $data['registrationdate'];
	$nextduedate = $data['nextduedate'];
	$recurringamount = $data['recurringamount'];
	$domain = $data['domain'];
	$paymentmethod = $data['paymentmethod'];
	$registrar = $data['registrar'];
	$status = $data['status'];
	$year = substr($nextduedate, 0, 4);
	$month = substr($nextduedate, 5, 2);
	$day = substr($nextduedate, 8, 2);
	$newnextduedate = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year + $registrationperiod));
	update_query("tbldomains", array("nextduedate" => $newnextduedate), array("id" => $func_domainid));
	$domaintype = substr($type, 6);
	$domainparts = explode(".", $domain, 2);
	$sld = $domainparts[0];
	$tld = $domainparts[1];
	$params = array();
	$params['domainid'] = $func_domainid;
	$params['sld'] = $sld;
	$params['tld'] = $tld;

	if (!function_exists("getRegistrarConfigOptions")) {
		require ROOTDIR . "/includes/registrarfunctions.php";
	}


	if ($domaintype == "Register" || $domaintype == "Transfer") {
		$result = select_query("tbldomainpricing", "autoreg", array("extension" => "." . $tld));
		$data = mysql_fetch_array($result);
		$autoreg = $data[0];

		if ($status == "Pending") {
			if (getNewClientAutoProvisionStatus($userid)) {
				if ($autoreg) {
					update_query("tbldomains", array("registrar" => $autoreg), array("id" => $func_domainid));
					$params['registrar'] = $autoreg;

					if ($domaintype == "Register") {
						logActivity("Running Automatic Domain Registration on Payment", $userid);
						$result = RegRegisterDomain($params);
						$emailmessage = "Domain Registration Confirmation";
					}
					else {
						if ($domaintype == "Transfer") {
							logActivity("Running Automatic Domain Transfer on Payment", $userid);
							$result = RegTransferDomain($params);
							$emailmessage = "Domain Transfer Initiated";
						}
					}

					$result = $result['error'];

					if ($result) {
						sendAdminMessage("Automatic Setup Failed", array("client_id" => $userid, "domain_id" => $func_domainid, "domain_type" => $domaintype, "domain_name" => $domain, "error_msg" => $result), "account");

						if ($whmcs->get_config("DomainToDoListEntries")) {
							if ($domaintype == "Register") {
								addToDoItem("Manual Domain Registration", "Client ID " . $userid . " has paid for the registration of domain " . $domain . " and the automated registration attempt has failed with the following error: " . $result);
								return null;
							}


							if ($domaintype == "Transfer") {
								addToDoItem("Manual Domain Transfer", "Client ID " . $userid . " has paid for the transfer of domain " . $domain . " and the automated transfer attempt has failed with the following error: " . $result);
								return null;
							}
						}
					}
					else {
						sendMessage($emailmessage, $func_domainid);
						sendAdminMessage("Automatic Setup Successful", array("client_id" => $userid, "domain_id" => $func_domainid, "domain_type" => $domaintype, "domain_name" => $domain, "error_msg" => ""), "account");
						return null;
					}
				}


				if ($whmcs->get_config("DomainToDoListEntries")) {
					if ($domaintype == "Register") {
						addToDoItem("Manual Domain Registration", "Client ID " . $userid . " has paid for the registration of domain " . $domain);
						return null;
					}


					if ($domaintype == "Transfer") {
						addToDoItem("Manual Domain Transfer", "Client ID " . $userid . " has paid for the transfer of domain " . $domain);
						return null;
					}
				}
			}
			else {
				logActivity("Automatic Domain Registration on Payment Suppressed for New Client", $userid);
				return null;
			}
		}


		if ($autoreg) {
			logActivity("Automatic Domain Registration Suppressed as Domain Is Already Active", $userid);
			return null;
		}
	}
	else {
		if (($status != "Pending" && $status != "Cancelled") && $status != "Fraud") {
			if ($whmcs->get_config("AutoRenewDomainsonPayment") && $registrar) {
				if (($whmcs->get_config("FreeDomainAutoRenewRequiresProduct") && $recurringamount <= 0) && !get_query_val("tblhosting", "COUNT(*)", array("userid" => $userid, "domain" => $domain, "domainstatus" => "Active"))) {
					logActivity("Surpressed Automatic Domain Renewal on Payment Due to Domain Being Free and having No Active Associated Product", $userid);
					sendAdminNotification("account", "Free Domain Renewal Manual Action Required", "The domain " . $domain . " (ID: " . $func_domainid . ") was just invoiced for renewal and automatically marked paid due to it being free, but because no active Product/Service matching the domain was found in order to qualify for the free domain offer, the renewal has not been automatically submitted to the registrar.  You must login to review & process this renewal manually should it be desired.");
					return null;
				}

				logActivity("Running Automatic Domain Renewal on Payment", $userid);
				$params['registrar'] = $registrar;
				$result = RegRenewDomain($params);
				$result = $result['error'];

				if ($result) {
					sendAdminMessage("Domain Renewal Failed", array("client_id" => $userid, "domain_id" => $func_domainid, "domain_name" => $domain, "error_msg" => $result), "account");

					if ($whmcs->get_config("DomainToDoListEntries")) {
						addToDoItem("Manual Domain Renewal", "Client ID " . $userid . " has paid for the renewal of domain " . $domain . " and the automated renewal attempt has failed with the following error: " . $result);
						return null;
					}
				}
				else {
					sendMessage("Domain Renewal Confirmation", $func_domainid);
					sendAdminMessage("Domain Renewal Successful", array("client_id" => $userid, "domain_id" => $func_domainid, "domain_name" => $domain, "error_msg" => ""), "account");
					return null;
				}
			}


			if ($whmcs->get_config("DomainToDoListEntries")) {
				addToDoItem("Manual Domain Renewal", "Client ID " . $userid . " has paid for the renewal of domain " . $domain);
			}
		}
	}

}

function makeAddonPayment($func_addonid) {
	global $CONFIG;

	$result = select_query("tblhostingaddons", "", array("id" => $func_addonid));
	$data = mysql_fetch_array($result);
	$id = $data['id'];
	$hostingid = $data['hostingid'];
	$addonid = $data['addonid'];
	$regdate = $data['regdate'];
	$name = $data['name'];
	$setupfee = $data['setupfee'];
	$recurring = $data['recurring'];
	$billingcycle = $data['billingcycle'];
	$free = $data['free'];
	$status = $data['status'];
	$nextduedate = $data['nextduedate'];
	$paymentmethod = $data['paymentmethod'];
	$amount = ($regdate == $nextduedate ? $setupfee + $recurring : $recurring);

	if ($gateway) {
		$paymentmethod = $gateway;
	}

	$result = select_query("tblhosting", "userid,domain", array("id" => $hostingid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$domain = $data['domain'];

	if (substr($regdate, 0, 8) == substr($nextduedate, 0, 8)) {
		$recurring = $setupfee;
	}

	$nextduedate = getInvoicePayUntilDate($nextduedate, $billingcycle, true);
	update_query("tblhostingaddons", array("nextduedate" => $nextduedate), array("id" => $func_addonid));

	if ($status == "Pending") {
		$result = select_query("tbladdons", "autoactivate,welcomeemail", array("id" => $addonid));
		$data = mysql_fetch_array($result);
		$autoactivate = $data['autoactivate'];
		$welcomeemail = $data['welcomeemail'];

		if ($autoactivate) {
			update_query("tblhostingaddons", array("status" => "Active"), array("id" => $func_addonid));

			if ($welcomeemail) {
				$result = select_query("tblemailtemplates", "name", array("id" => $welcomeemail));
				$data = mysql_fetch_array($result);
				$welcomeemailname = $data['name'];
				sendMessage($welcomeemailname, $hostingid);
			}

			run_hook("AddonActivation", array("id" => $func_addonid, "userid" => $userid, "serviceid" => $hostingid, "addonid" => $addonid));
		}
	}


	if ($status == "Suspended") {
		update_query("tblhostingaddons", array("status" => "Active"), array("id" => $func_addonid));

		if ($addonid) {
			$result2 = select_query("tbladdons", "suspendproduct", array("id" => $addonid));
			$data2 = mysql_fetch_array($result2);
			$suspendproduct = $data2[0];

			if ($suspendproduct) {
				$result2 = select_query("tblhosting", "servertype", array("tblhosting.id" => $hostingid), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");
				$data2 = mysql_fetch_array($result2);
				$module = $data2[0];
				logActivity("Unsuspending Parent Service for Addon Payment - Service ID: " . $hostingid, $userid);

				if (!function_exists("getModuleType")) {
					include dirname(__FILE__) . "/modulefunctions.php";
				}

				$serverresult = ServerUnsuspendAccount($hostingid);
			}
		}
	}

}

function getProrataValues($billingcycle, $amount, $proratadate, $proratachargenextmonth, $day, $month, $year, $userid) {
	global $CONFIG;

	if ($CONFIG['ProrataClientsAnniversaryDate']) {
		$result = select_query("tblclients", "datecreated", array("id" => $userid));
		$data = mysql_fetch_array($result);
		$clientregdate = $data[0];
		$clientregdate = explode("-", $clientregdate);
		$proratadate = $clientregdate[2];

		if ($proratadate <= 0) {
			$proratadate = date("d");
		}
	}

	$billingcycle = str_replace("-", "", strtolower($billingcycle));
	$proratamonths = getBillingCycleMonths($billingcycle);

	if ($billingcycle != "monthly") {
		$proratachargenextmonth = 0;
	}


	if ($billingcycle == "monthly") {
		if ($day < $proratadate) {
			$proratamonth = $month;
		}
		else {
			$proratamonth = $month + 1;
		}
	}
	else {
		$proratamonth = $month + $proratamonths;
	}

	$proratadateuntil = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate, $year));
	$proratainvoicedate = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate - 1, $year));
	$monthnumdays = array("31", "28", "31", "30", "31", "30", "31", "31", "30", "31", "30", "31");

	if (($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0) {
		$monthnumdays[1] = 29;
	}

	$totaldays = $extraamount = 0;

	if ($billingcycle == "monthly") {
		if ((($proratachargenextmonth < $proratadate && $day < $proratadate) && $proratachargenextmonth <= $day) || (($proratadate <= $proratachargenextmonth && $proratadate <= $day) && $proratachargenextmonth <= $day)) {
			++$proratamonth;
			$extraamount = $amount;
		}

		$totaldays += $monthnumdays[$month - 1];
		$days = ceil((strtotime($proratadateuntil) - strtotime("" . $year . "-" . $month . "-" . $day)) / (60 * 60 * 24));
		$proratadateuntil = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate, $year));
		$proratainvoicedate = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate - 1, $year));
	}
	else {
		$counter = $month;

		while ($counter <= $month + ($proratamonths - 1)) {
			$month2 = round($counter);

			if (12 < $month2) {
				$month2 = $month2 - 12;
			}


			if (12 < $month2) {
				$month2 = $month2 - 12;
			}


			if (12 < $month2) {
				$month2 = $month2 - 12;
			}

			$totaldays += $monthnumdays[$month2 - 1];
			++$counter;
		}

		$days = ceil((strtotime($proratadateuntil) - strtotime("" . $year . "-" . $month . "-" . $day)) / (60 * 60 * 24));
	}

	$prorataamount = round($amount * ($days / $totaldays), 2) + $extraamount;
	$days = ceil((strtotime($proratadateuntil) - strtotime("" . $year . "-" . $month . "-" . $day)) / (60 * 60 * 24));
	return array("amount" => $prorataamount, "date" => $proratadateuntil, "invoicedate" => $proratainvoicedate, "days" => $days);
}

function getNewClientAutoProvisionStatus($userid) {
	global $CONFIG;

	if ($CONFIG['AutoProvisionExistingOnly']) {
		$result = select_query("tblhosting", "COUNT(*)", array("userid" => $userid, "domainstatus" => "Active"));
		$data = mysql_fetch_array($result);
		$result = select_query("tbldomains", "COUNT(*)", array("userid" => $userid, "status" => "Active"));
		$data2 = mysql_fetch_array($result);

		if ($data[0] + $data2[0]) {
			return true;
		}

		return false;
	}

	return true;
}

function applyCredit($invoiceid, $userid, $amount, $noemail = "") {
	$amount = round($amount, 2);
	update_query("tblclients", array("credit" => "-=" . $amount), array("id" => (int)$userid));
	update_query("tblinvoices", array("credit" => "+=" . $amount), array("id" => (int)$invoiceid));
	insert_query("tblcredit", array("clientid" => $userid, "date" => "now()", "description" => "Credit Applied to Invoice #" . $invoiceid, "amount" => $amount * (0 - 1)));
	logActivity("Credit Applied - Amount: " . $amount . " - Invoice ID: " . $invoiceid, $userid);
	updateInvoiceTotal($invoiceid);
	$result = select_query("tblinvoices", "total", array("id" => $invoiceid));
	$data = mysql_fetch_array($result);
	$total = $data['total'];
	$result = select_query("tblaccounts", "SUM(amountin)-SUM(amountout)", array("invoiceid" => $invoiceid));
	$data = mysql_fetch_array($result);
	$amountpaid = $data[0];
	$balance = $total - $amountpaid;

	if ($balance <= 0) {
		processPaidInvoice($invoiceid, $noemail);
	}

}

function getBillingCycleDays($billingcycle) {
	$totaldays = 0;

	if ($billingcycle == "Monthly") {
		$totaldays = 30;
	}
	else {
		if ($billingcycle == "Quarterly") {
			$totaldays = 90;
		}
		else {
			if ($billingcycle == "Semi-Annually") {
				$totaldays = 180;
			}
			else {
				if ($billingcycle == "Annually") {
					$totaldays = 365;
				}
				else {
					if ($billingcycle == "Biennially") {
						$totaldays = 730;
					}
					else {
						if ($billingcycle == "Triennially") {
							$totaldays = 1095;
						}
					}
				}
			}
		}
	}

	return $totaldays;
}

function getBillingCycleMonths($billingcycle) {
	$months = 1;

	if ($billingcycle == "Quarterly" || $billingcycle == "quarterly") {
		$months = 3;
	}
	else {
		if ($billingcycle == "Semi-Annually" || $billingcycle == "semiannually") {
			$months = 6;
		}
		else {
			if ($billingcycle == "Annually" || $billingcycle == "annually") {
				$months = 12;
			}
			else {
				if ($billingcycle == "Biennially" || $billingcycle == "biennially") {
					$months = 24;
				}
				else {
					if ($billingcycle == "Triennially" || $billingcycle == "triennially") {
						$months = 36;
					}
				}
			}
		}
	}

	return $months;
}

?>