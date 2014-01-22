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

function SumUpPackageUpgradeOrder($id, $newproductid, $newproductbillingcycle, $promocode, $paymentmethod = "", $checkout = "") {
	global $CONFIG;
	global $_LANG;
	global $currency;
	global $upgradeslist;
	global $orderamount;
	global $orderdescription;
	global $applytax;

	$_SESSION['upgradeids'] = "";
	$result = select_query("tblhosting", "tblproducts.name,tblproducts.id,tblhosting.nextduedate,tblhosting.billingcycle,tblhosting.amount,tblhosting.firstpaymentamount,tblhosting.domain", array("userid" => $_SESSION['uid'], "tblhosting.id" => $id), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");
	$data = mysql_fetch_array($result);
	$oldproductid = $data['id'];
	$oldproductname = $data['name'];
	$domain = $data['domain'];
	$nextduedate = $data['nextduedate'];
	$billingcycle = $data['billingcycle'];
	$oldamount = $data['amount'];

	if ($billingcycle == "One Time") {
		$oldamount = $data['firstpaymentamount'];
	}

	$newproductbillingcycleraw = $newproductbillingcycle;
	$newproductbillingcyclenice = ucfirst($newproductbillingcycle);

	if ($newproductbillingcyclenice == "Semiannually") {
		$newproductbillingcyclenice = "Semi-Annually";
	}

	$configoptionspricingarray = getCartConfigOptions($newproductid, "", $newproductbillingcyclenice, $id);

	if ($configoptionspricingarray) {
		foreach ($configoptionspricingarray as $configoptionkey => $configoptionvalues) {
			$configoptionsamount += $configoptionvalues['selectedrecurring'] . "<br>";
		}
	}

	$result = select_query("tblproducts", "id,name,tax,paytype", array("id" => $newproductid));
	$data = mysql_fetch_array($result);
	$newproductid = $data['id'];
	$newproductname = $data['name'];
	$applytax = $data['tax'];
	$paytype = $data['paytype'];

	if (!$newproductid) {
		exit("Invalid New Product ID");
	}


	if (!$newproductbillingcycle) {
		exit("Invalid New Billing Cycle");
	}

	$newproductbillingcycle = strtolower($newproductbillingcycle);
	$newproductbillingcycle = str_replace("-", "", $newproductbillingcycle);

	if ($newproductbillingcycle == "onetime") {
		$newproductbillingcycle = "monthly";
	}

	$result = select_query("tblpricing", $newproductbillingcycle, array("type" => "product", "currency" => $currency['id'], "relid" => $newproductid));
	$data = mysql_fetch_array($result);
	$newamount = $data[$newproductbillingcycle];

	if (($paytype == "onetime" || $paytype == "recurring") && $newamount < 0) {
		exit("Invalid New Billing Cycle");
	}

	$newamount += $configoptionsamount;
	$year = substr($nextduedate, 0, 4);
	$month = substr($nextduedate, 5, 2);
	$day = substr($nextduedate, 8, 2);
	$cyclemonths = getBillingCycleMonths($billingcycle);
	$prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $cyclemonths, $day, $year));
	$totaldays = round((strtotime($nextduedate) - strtotime($prevduedate)) / 86400);
	$cyclemonths = getBillingCycleMonths($newproductbillingcyclenice);
	$prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $cyclemonths, $day, $year));
	$newtotaldays = round((strtotime($nextduedate) - strtotime($prevduedate)) / 86400);

	if ($newproductbillingcyclenice == "Onetime") {
		$newtotaldays = $totaldays;
	}


	if ($billingcycle == "Free Account" || $billingcycle == "One Time") {
		$days = $newtotaldays = $totaldays = getBillingCycleDays($newproductbillingcyclenice);
		$totalmonths = getBillingCycleMonths($newproductbillingcyclenice);
		$nextduedate = date("Y-m-d", mktime(0, 0, 0, date("m") + $totalmonths, date("d"), date("Y")));
		$amountdue = format_as_currency($newamount - $oldamount);
		$difference = $newamount;
	}
	else {
		$todaysdate = date("Ymd");
		$nextduedatetime = strtotime($nextduedate);
		$todaysdate = strtotime($todaysdate);
		$days = round(($nextduedatetime - $todaysdate) / 86400);
		$daysnotused = $days / $totaldays;
		$refundamount = $oldamount * $daysnotused;
		$cyclemultiplier = $days / $newtotaldays;
		$amountdue = $newamount * $cyclemultiplier;
		$amountdue = $amountdue - $refundamount;

		if ($amountdue < 0 && !$CONFIG['CreditOnDowngrade']) {
			$amountdue = 0;
		}

		$amountdue = format_as_currency($amountdue);
		$difference = $newamount - $oldamount;
	}

	$discount = 0;
	$promoqualifies = true;

	if ($promocode) {
		$promodata = validateUpgradePromo($promocode);

		if (is_array($promodata)) {
			$appliesto = $promodata['appliesto'];
			$requires = $promodata['requires'];
			$cycles = $promodata['cycles'];
			$value = $promodata['value'];
			$type = $promodata['discounttype'];
			$promodesc = $promodata['desc'];

			if ($newproductbillingcycle == "free") {
				$billingcycle = "Free Account";
			}
			else {
				if ($newproductbillingcycle == "onetime") {
					$billingcycle = "One Time";
				}
				else {
					if ($newproductbillingcycle == "semiannually") {
						$billingcycle = "Semi-Annually";
					}
					else {
						$billingcycle = ucfirst($newproductbillingcycle);
					}
				}
			}


			if ((count($appliesto) && $appliesto[0]) && !in_array($newproductid, $appliesto)) {
				$promoqualifies = false;
			}


			if ((count($requires) && $requires[0]) && !in_array($oldproductid, $requires)) {
				$promoqualifies = false;
			}


			if ((count($cycles) && $cycles[0]) && !in_array($billingcycle, $cycles)) {
				$promoqualifies = false;
			}


			if ($promoqualifies && 0 < $amountdue) {
				if ($type == "Percentage") {
					$percent = $value / 100;
					$discount = $amountdue * $percent;
				}
				else {
					$discount = $value;

					if ($amountdue < $discount) {
						$discount = $amountdue;
					}
				}
			}
		}


		if ($discount == 0) {
			$promodata = get_query_vals("tblpromotions", "type,value", array("lifetimepromo" => 1, "recurring" => 1, "code" => $promocode));

			if (is_array($promodata)) {
				if ($promodata['type'] == "Percentage") {
					$percent = $promodata['value'] / 100;
					$discount = $amountdue * $percent;
				}
				else {
					$discount = $promodata['value'];

					if ($amountdue < $discount) {
						$discount = $amountdue;
					}
				}

				$promoqualifies = true;
			}
		}
	}

	$GLOBALS['subtotal'] = $amountdue;
	$GLOBALS['qualifies'] = $promoqualifies;
	$GLOBALS['discount'] = $discount;
	$upgradearray[] = array("oldproductid" => $oldproductid, "oldproductname" => $oldproductname, "newproductid" => $newproductid, "newproductname" => $newproductname, "daysuntilrenewal" => $days, "totaldays" => $totaldays, "newproductbillingcycle" => $newproductbillingcycleraw, "price" => formatCurrency($amountdue));

	if ($checkout) {
		$orderdescription = $_LANG['upgradedowngradepackage'] . (": " . $oldproductname . " => " . $newproductname . "<br>\r\n") . $_LANG['orderbillingcycle'] . ": " . $_LANG["orderpaymentterm" . str_replace(array("-", " "), "", strtolower($newproductbillingcycle))] . "<br>\r\n" . $_LANG['ordertotalduetoday'] . ": " . formatCurrency($amountdue);
		$amountwithdiscount = $amountdue - $discount;
		$upgradeid = insert_query("tblupgrades", array("type" => "package", "date" => "now()", "relid" => $id, "originalvalue" => $oldproductid, "newvalue" => "" . $newproductid . "," . $newproductbillingcycleraw, "amount" => $amountwithdiscount, "recurringchange" => $difference));
		$upgradeslist .= $upgradeid . ",";
		$_SESSION['upgradeids'][] = $upgradeid;

		if (0 < $amountdue) {
			if ($domain) {
				$domain = " - " . $domain;
			}

			insert_query("tblinvoiceitems", array("userid" => $_SESSION['uid'], "type" => "Upgrade", "relid" => $upgradeid, "description" => $_LANG['upgradedowngradepackage'] . ((": " . $oldproductname . $domain . "\r\n") . $oldproductname . " => " . $newproductname . " (") . getTodaysDate() . " - " . fromMySQLDate($nextduedate) . ")", "amount" => $amountdue, "taxed" => $applytax, "duedate" => "now()", "paymentmethod" => $paymentmethod));

			if (0 < $discount) {
				insert_query("tblinvoiceitems", array("userid" => $_SESSION['uid'], "description" => $_LANG['orderpromotioncode'] . ": " . $promocode . " - " . $promodesc, "amount" => $discount * (0 - 1), "taxed" => $applytax, "duedate" => "now()", "paymentmethod" => $paymentmethod));
			}

			$orderamount += $amountwithdiscount;
		}
		else {
			if ($CONFIG['CreditOnDowngrade']) {
				$creditamount = $amountdue * (0 - 1);
				insert_query("tblcredit", array("clientid" => $_SESSION['uid'], "date" => "now()", "description" => "Upgrade/Downgrade Credit", "amount" => $creditamount));
				update_query("tblclients", array("credit" => "+=" . $creditamount), array("id" => (int)$_SESSION['uid']));
			}

			update_query("tblupgrades", array("paid" => "Y"), array("id" => $upgradeid));
			doUpgrade($upgradeid);
		}
	}

	return $upgradearray;
}

function SumUpConfigOptionsOrder($id, $configoptions, $promocode, $paymentmethod = "", $checkout = "") {
	global $CONFIG;
	global $_LANG;
	global $upgradeslist;
	global $orderamount;
	global $orderdescription;
	global $applytax;

	$_SESSION['upgradeids'] = array();
	$result = select_query("tblhosting", "packageid,domain,nextduedate,billingcycle", array("userid" => $_SESSION['uid'], "id" => $id));
	$data = mysql_fetch_array($result);
	$packageid = $data['packageid'];
	$domain = $data['domain'];
	$nextduedate = $data['nextduedate'];
	$billingcycle = $data['billingcycle'];
	$result = select_query("tblproducts", "name,tax", array("id" => $packageid));
	$data = mysql_fetch_array($result);
	$productname = $data['name'];
	$applytax = $data['tax'];

	if ($domain) {
		$productname .= " - " . $domain;
	}

	$year = substr($nextduedate, 0, 4);
	$month = substr($nextduedate, 5, 2);
	$day = substr($nextduedate, 8, 2);
	$cyclemonths = getBillingCycleMonths($billingcycle);
	$prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $cyclemonths, $day, $year));
	$totaldays = round((strtotime($nextduedate) - strtotime($prevduedate)) / 86400);
	$todaysdate = date("Ymd");
	$todaysdate = strtotime($todaysdate);
	$nextduedatetime = strtotime($nextduedate);
	$days = round(($nextduedatetime - $todaysdate) / 86400);

	if ($days < 0) {
		$days = $totaldays;
	}

	$percentage = $days / $totaldays;
	$discount = 0;
	$promoqualifies = true;

	if ($promocode) {
		$promodata = validateUpgradePromo($promocode);

		if (is_array($promodata)) {
			$appliesto = $promodata['appliesto'];
			$cycles = $promodata['cycles'];
			$promotype = $promodata['type'];
			$promovalue = $promodata['value'];
			$discounttype = $promodata['discounttype'];
			$upgradeconfigoptions = $promodata['configoptions'];
			$promodesc = $promodata['desc'];

			if ($promotype != "configoptions") {
				$promoqualifies = false;
			}


			if ((count($appliesto) && $appliesto[0]) && !in_array($packageid, $appliesto)) {
				$promoqualifies = false;
			}


			if ((count($cycles) && $cycles[0]) && !in_array($billingcycle, $cycles)) {
				$promoqualifies = false;
			}


			if ($discounttype == "Percentage") {
				$promovalue = $promovalue / 100;
			}
		}


		if ($promovalue == 0) {
			$promodata = get_query_vals("tblpromotions", "upgrades, upgradeconfig, type,value", array("lifetimepromo" => 1, "recurring" => 1, "code" => $promocode));

			if (is_array($promodata)) {
				if ($promodata['upgrades'] == 1) {
					$upgradeconfig = unserialize($promodata['upgradeconfig']);

					if ($upgradeconfig['type'] != "configoptions") {
						$promoqualifies = false;
					}

					$promovalue = $upgradeconfig['value'];
					$discounttype = $upgradeconfig['discounttype'];

					if ($discounttype == "Percentage") {
						$promovalue = $promovalue / 100;
					}

					$promoqualifies = true;
				}
				else {
					$promoqualifies = false;
				}
			}
		}
	}

	$configoptions = getCartConfigOptions($packageid, $configoptions, $billingcycle);
	$oldconfigoptions = getCartConfigOptions($packageid, "", $billingcycle, $id);
	$subtotal = 0;
	foreach ($configoptions as $key => $configoption) {
		$configid = $configoption['id'];
		$configname = $configoption['optionname'];
		$optiontype = $configoption['optiontype'];
		$new_selectedvalue = $configoption['selectedvalue'];
		$new_selectedqty = $configoption['selectedqty'];
		$new_selectedname = $configoption['selectedname'];
		$new_selectedsetup = $configoption['selectedsetup'];
		$new_selectedrecurring = $configoption['selectedrecurring'];
		$old_selectedvalue = $oldconfigoptions[$key]['selectedvalue'];
		$old_selectedqty = $oldconfigoptions[$key]['selectedqty'];
		$old_selectedname = $oldconfigoptions[$key]['selectedname'];
		$old_selectedsetup = $oldconfigoptions[$key]['selectedsetup'];
		$old_selectedrecurring = $oldconfigoptions[$key]['selectedrecurring'];

		if ((($optiontype == 1 || $optiontype == 2) && $new_selectedvalue != $old_selectedvalue) || (($optiontype == 3 || $optiontype == 4) && $new_selectedqty != $old_selectedqty)) {
			$difference = $new_selectedrecurring - $old_selectedrecurring;
			$amountdue = $difference * $percentage;
			$amountdue = format_as_currency($amountdue);

			if (!$CONFIG['CreditOnDowngrade'] && $amountdue < 0) {
				$amountdue = format_as_currency(0);
			}


			if ($optiontype == 1 || $optiontype == 2) {
				$db_orig_value = $old_selectedvalue;
				$db_new_value = $new_selectedvalue;
				$originalvalue = $old_selectedname;
				$newvalue = $new_selectedname;
			}
			else {
				if ($optiontype == 3) {
					$db_orig_value = $old_selectedqty;
					$db_new_value = $new_selectedqty;

					if ($old_selectedqty) {
						$originalvalue = $_LANG['yes'];
						$newvalue = $_LANG['no'];
					}
					else {
						$originalvalue = $_LANG['no'];
						$newvalue = $_LANG['yes'];
					}
				}
				else {
					if ($optiontype == 4) {
						$new_selectedqty = (int)$new_selectedqty;

						if ($new_selectedqty < 0) {
							$new_selectedqty = 0;
						}

						$db_orig_value = $old_selectedqty;
						$db_new_value = $new_selectedqty;
						$originalvalue = $old_selectedqty;
						$newvalue = $new_selectedqty . " x " . $configoption['options'][0]['nameonly'];
					}
				}
			}

			$subtotal += $amountdue;
			$itemdiscount = 0;

			if (($promoqualifies && 0 < $amountdue) && (!count($upgradeconfigoptions) || in_array($configid, $upgradeconfigoptions))) {
				$itemdiscount = ($discounttype == "Percentage" ? round($amountdue * $promovalue, 2) : ($amountdue < $promovalue ? $amountdue : $promovalue));
			}

			$discount += $itemdiscount;
			$upgradearray[] = array("configname" => $configname, "originalvalue" => $originalvalue, "newvalue" => $newvalue, "price" => formatCurrency($amountdue));

			if ($checkout) {
				if ($orderdescription) {
					$orderdescription .= "<br>\r\n<br>\r\n";
				}

				$orderdescription .= $_LANG['upgradedowngradeconfigoptions'] . (": " . $configname . " - " . $originalvalue . " => " . $newvalue . "<br>\r\nAmount Due: ") . formatCurrency($amountdue);
				$paid = "N";

				if ($amountdue <= 0) {
					$paid = "Y";
				}

				$amountwithdiscount = $amountdue - $itemdiscount;
				$upgradeid = insert_query("tblupgrades", array("type" => "configoptions", "date" => "now()", "relid" => $id, "originalvalue" => "" . $configid . "=>" . $db_orig_value, "newvalue" => $db_new_value, "amount" => $amountwithdiscount, "recurringchange" => $difference, "status" => "Pending", "paid" => $paid));
				$_SESSION['upgradeids'][] = $upgradeid;

				if (0 < $amountdue) {
					insert_query("tblinvoiceitems", array("userid" => $_SESSION['uid'], "type" => "Upgrade", "relid" => $upgradeid, "description" => $_LANG['upgradedowngradeconfigoptions'] . ((": " . $productname . "\r\n") . $configname . ": " . $originalvalue . " => " . $newvalue . " (") . getTodaysDate() . " - " . fromMySQLDate($nextduedate) . ")", "amount" => $amountdue, "taxed" => $applytax, "duedate" => "now()", "paymentmethod" => $paymentmethod));

					if (0 < $itemdiscount) {
						insert_query("tblinvoiceitems", array("userid" => $_SESSION['uid'], "description" => $_LANG['orderpromotioncode'] . ": " . $promocode . " - " . $promodesc, "amount" => $itemdiscount * (0 - 1), "taxed" => $applytax, "duedate" => "now()", "paymentmethod" => $paymentmethod));
					}

					$orderamount += $amountwithdiscount;
					continue;
				}


				if ($CONFIG['CreditOnDowngrade']) {
					$creditamount = $amountdue * (0 - 1);
					insert_query("tblcredit", array("clientid" => $_SESSION['uid'], "date" => "now()", "description" => "Upgrade/Downgrade Credit", "amount" => $creditamount));
					update_query("tblclients", array("credit" => "+=" . $creditamount), array("id" => (int)$_SESSION['uid']));
				}

				doUpgrade($upgradeid);
				continue;
			}

			continue;
		}
	}


	if (!count($upgradearray)) {
		redir("type=configoptions&id=" . (int)$id, "upgrade.php");
	}

	$GLOBALS['subtotal'] = $subtotal;
	$GLOBALS['qualifies'] = $promoqualifies;
	$GLOBALS['discount'] = $discount;
	return $upgradearray;
}

function createUpgradeOrder($id, $ordernotes, $promocode, $paymentmethod) {
	global $CONFIG;
	global $remote_ip;
	global $orderdescription;
	global $orderamount;

	if ($promocode && !$GLOBALS['qualifies']) {
		$promocode = "";
	}


	if ($promocode) {
		$result = select_query("tblpromotions", "upgradeconfig", array("code" => $promocode));
		$data = mysql_fetch_array($result);
		$upgradeconfig = $data['upgradeconfig'];
		$upgradeconfig = unserialize($upgradeconfig);
		$promo_type = $upgradeconfig['discounttype'];
		$promo_value = $upgradeconfig['value'];
		update_query("tblpromotions", array("uses" => "+1"), array("code" => $promocode));
	}

	$order_number = generateUniqueID();
	$orderid = insert_query("tblorders", array("ordernum" => $order_number, "userid" => $_SESSION['uid'], "date" => "now()", status => "Pending", "promocode" => $promocode, "promotype" => $promo_type, "promovalue" => $promo_value, "paymentmethod" => $paymentmethod, "ipaddress" => $remote_ip, "amount" => $orderamount, "notes" => $ordernotes));
	foreach ($_SESSION['upgradeids'] as $upgradeid) {
		update_query("tblupgrades", array("orderid" => $orderid), array("id" => $upgradeid));
	}

	sendMessage("Order Confirmation", $_SESSION['uid'], array("order_id" => $orderid, "order_number" => $order_number, "order_details" => $orderdescription));
	logActivity("Upgrade Order Placed - Order ID: " . $orderid);

	if (!function_exists("createInvoices")) {
		include ROOTDIR . "/includes/processinvoices.php";
	}

	$invoiceid = 0;
	$invoiceid = createInvoices($_SESSION['uid'], true);

	if ($invoiceid) {
		$result = select_query("tblinvoiceitems", "invoiceid", "type='Upgrade' AND relid IN (" . db_build_in_array(db_escape_numarray($_SESSION['upgradeids'])) . ")", "invoiceid", "DESC");
		$data = mysql_fetch_array($result);
		$invoiceid = $data['invoiceid'];
	}


	if ($CONFIG['OrderDaysGrace']) {
		$new_time = mktime(0, 0, 0, date("m"), date("d") + $CONFIG['OrderDaysGrace'], date("Y"));
		$duedate = date("Y-m-d", $new_time);
		update_query("tblinvoices", array("duedate" => $duedate), array("id" => $invoiceid));
	}


	if (!$CONFIG['NoInvoiceEmailOnOrder']) {
		sendMessage("Invoice Created", $invoiceid);
	}

	update_query("tblorders", array("invoiceid" => $invoiceid), array("id" => $orderid));
	$result = select_query("tblclients", "firstname, lastname, companyname, email, address1, address2, city, state, postcode, country, phonenumber, ip, host", array("id" => $_SESSION['uid']));
	$data = mysql_fetch_array($result);
	list($firstname,$lastname,$companyname,$email,$address1,$address2,$city,$state,$postcode,$country,$phonenumber,$ip,$host) = $data;
	$nicegatewayname = get_query_val("tblpaymentgateways", "value", array("gateway" => $paymentmethod, "setting" => "Name"));
	$ordertotal = get_query_val("tblinvoices", "total", array("id" => $invoiceid));
	$adminemailitems = "";

	if ($invoiceid) {
		$result = select_query("tblinvoiceitems", "description", "type='Upgrade' AND relid IN (" . db_build_in_array(db_escape_numarray($_SESSION['upgradeids'])) . ")", "invoiceid", "DESC");

		while ($invoicedata = mysql_fetch_assoc($result)) {
			$adminemailitems .= $invoicedata['description'] . "<br />";
		}
	}
	else {
		$adminemailitems .= "Upgrade/Downgrade";
	}

	sendAdminMessage("New Order Notification", array("order_id" => $orderid, "order_number" => $order_number, "order_date" => date("d/m/Y H:i:s"), "invoice_id" => $invoiceid, "order_payment_method" => $nicegatewayname, "order_total" => formatCurrency($ordertotal), "client_id" => $_SESSION['uid'], "client_first_name" => $firstname, "client_last_name" => $lastname, "client_email" => $email, "client_company_name" => $companyname, "client_address1" => $address1, "client_address2" => $address2, "client_city" => $city, "client_state" => $state, "client_postcode" => $postcode, "client_country" => $country, "client_phonenumber" => $phonenumber, "order_items" => $adminemailitems, "order_notes" => "", "client_ip" => $ip, "client_hostname" => $host), "account");
	return array("id" => $id, "orderid" => $orderid, "order_number" => $order_number, "invoiceid" => $invoiceid);
}

function processUpgradePayment($upgradeid, $paidamount, $fees, $invoice = "", $gateway = "", $transid = "") {
	update_query("tblupgrades", array("paid" => "Y"), array("id" => $upgradeid));
	doUpgrade($upgradeid);
}

function doUpgrade($upgradeid) {
	$result = select_query("tblupgrades", "", array("id" => $upgradeid));
	$data = mysql_fetch_array($result);
	$orderid = $data['orderid'];
	$type = $data['type'];
	$relid = $data['relid'];
	$originalvalue = $data['originalvalue'];
	$newvalue = $data['newvalue'];
	$upgradeamount = $data['amount'];
	$recurringchange = $data['recurringchange'];
	$result = select_query("tblorders", "promocode", array("id" => $orderid));
	$data = mysql_fetch_array($result);
	$promocode = $data['promocode'];

	if ($type == "package") {
		$newvalue = explode(",", $newvalue);
		$newpackageid = $newvalue[0];
		$newbillingcycle = $newvalue[1];
		$changevalue = "amount";

		if ($newbillingcycle == "free") {
			$newbillingcycle = "Free Account";
		}
		else {
			if ($newbillingcycle == "onetime") {
				$newbillingcycle = "One Time";
				$changevalue = "firstpaymentamount";
				$recurringchange = $upgradeamount;
			}
			else {
				if ($newbillingcycle == "monthly") {
					$newbillingcycle = "Monthly";
				}
				else {
					if ($newbillingcycle == "quarterly") {
						$newbillingcycle = "Quarterly";
					}
					else {
						if ($newbillingcycle == "semiannually") {
							$newbillingcycle = "Semi-Annually";
						}
						else {
							if ($newbillingcycle == "annually") {
								$newbillingcycle = "Annually";
							}
							else {
								if ($newbillingcycle == "biennially") {
									$newbillingcycle = "Biennially";
								}
								else {
									if ($newbillingcycle == "triennially") {
										$newbillingcycle = "Triennially";
									}
								}
							}
						}
					}
				}
			}
		}

		$result = select_query("tblhosting", "billingcycle", array("id" => $relid));
		$data = mysql_fetch_array($result);
		$billingcycle = $data['billingcycle'];

		if ($billingcycle == "Free Account") {
			$newnextdue = getInvoicePayUntilDate(date("Y-m-d"), $newbillingcycle, true);
			update_query("tblhosting", array("nextduedate" => $newnextdue, "nextinvoicedate" => $newnextdue), array("id" => $relid));
		}


		if (!function_exists("migrateCustomFieldsBetweenProducts")) {
			require ROOTDIR . "/includes/customfieldfunctions.php";
		}

		migrateCustomFieldsBetweenProducts($relid, $newpackageid);
		update_query("tblhosting", array("packageid" => $newpackageid, "billingcycle" => $newbillingcycle, "" . $changevalue => "+=" . $recurringchange), array("id" => $relid));
		$result = full_query("SELECT tblinvoiceitems.id,tblinvoiceitems.invoiceid FROM tblinvoices INNER JOIN tblinvoiceitems ON tblinvoiceitems.invoiceid=tblinvoices.id INNER JOIN tblhosting ON tblhosting.id=tblinvoiceitems.relid WHERE tblinvoices.status='Unpaid' AND tblinvoiceitems.type='Hosting' AND tblhosting.id=" . (int)$relid . " ORDER BY tblinvoiceitems.duedate DESC");
		$data = mysql_fetch_array($result);
		$invitemid = $data['id'];
		$inviteminvoiceid = $data['invoiceid'];

		if ($invitemid) {
			update_query("tblinvoices", array("status" => "Cancelled"), array("id" => $inviteminvoiceid));
			update_query("tblinvoiceitems", array("duedate" => "0000-00-00"), array("id" => $invitemid));
			full_query("UPDATE tblhosting SET nextinvoicedate=nextduedate WHERE id=" . (int)$relid);
		}


		if (!function_exists("getCartConfigOptions")) {
			require ROOTDIR . "/includes/configoptionsfunctions.php";
		}

		$configoptions = getCartConfigOptions($newpackageid, "", $newbillingcycle);
		foreach ($configoptions as $configoption) {
			$result = select_query("tblhostingconfigoptions", "COUNT(*)", array("relid" => $relid, "configid" => $configoption['id']));
			$data = mysql_fetch_array($result);

			if (!$data[0]) {
				insert_query("tblhostingconfigoptions", array("relid" => $relid, "configid" => $configoption['id'], "optionid" => $configoption['selectedvalue']));
				continue;
			}
		}

		run_hook("AfterProductUpgrade", array("upgradeid" => $upgradeid));
	}
	else {
		if ($type == "configoptions") {
			$tempvalue = explode("=>", $originalvalue);
			$configid = $tempvalue[0];
			$result = select_query("tblproductconfigoptions", "", array("id" => $configid));
			$data = mysql_fetch_array($result);
			$optiontype = $data['optiontype'];
			$result = select_query("tblhostingconfigoptions", "COUNT(*)", array("relid" => $relid, "configid" => $configid));
			$data = mysql_fetch_array($result);

			if (!$data[0]) {
				insert_query("tblhostingconfigoptions", array("relid" => $relid, "configid" => $configid));
			}


			if ($optiontype == 1 || $optiontype == 2) {
				update_query("tblhostingconfigoptions", array("optionid" => $newvalue), array("relid" => $relid, "configid" => $configid));
			}
			else {
				if ($optiontype == 3 || $optiontype == 4) {
					update_query("tblhostingconfigoptions", array("qty" => $newvalue), array("relid" => $relid, "configid" => $configid));
				}
			}

			update_query("tblhosting", array("amount" => "+=" . $recurringchange), array("id" => $relid));
			run_hook("AfterConfigOptionsUpgrade", array("upgradeid" => $upgradeid));
		}
	}


	if ($promocode) {
		$result = select_query("tblpromotions", "id,type,recurring,value", array("code" => $promocode));
		$data = mysql_fetch_array($result);
		$promoid = $data[0];
		$promotype = $data[1];
		$promorecurring = $data[2];
		$promovalue = $data[3];

		if ($promorecurring) {
			$recurringamount = recalcRecurringProductPrice($relid);

			if ($promotype == "Percentage") {
				$discount = $recurringamount * ($promovalue / 100);
				$recurringamount = $recurringamount - $discount;
			}
			else {
				$recurringamount = ($recurringamount < $promovalue ? "0" : $recurringamount - $promovalue);
			}

			update_query("tblhosting", array("amount" => $recurringamount, "promoid" => $promoid), array("id" => $relid));
		}
		else {
			update_query("tblhosting", array("promoid" => "0"), array("id" => $relid));
		}
	}
	else {
		update_query("tblhosting", array("promoid" => "0"), array("id" => $relid));
	}


	if ($type == "package" || $type == "configoptions") {
		$data = get_query_vals("tblhosting", "userid,packageid", array("id" => $relid));
		$userid = $data['userid'];
		$pid = $data['packageid'];
		$result = select_query("tblproducts", "servertype,upgradeemail", array("id" => $pid));
		$data = mysql_fetch_array($result);
		$servertype = $data['servertype'];
		$upgradeemail = $data['upgradeemail'];

		if ($servertype) {
			if (!function_exists("getModuleType")) {
				require dirname(__FILE__) . "/modulefunctions.php";
			}

			$result = ServerChangePackage($relid);

			if ($result != "success") {
				logActivity("Automatic Product/Service Upgrade Failed - Service ID: " . $relid, $userid);
			}
			else {
				logActivity("Automatic Product/Service Upgrade Successful - Service ID: " . $relid, $userid);

				if ($upgradeemail) {
					$result = select_query("tblemailtemplates", "name", array("id" => $upgradeemail));
					$data = mysql_fetch_array($result);
					$emailtplname = $data[0];
					sendMessage($emailtplname, $relid);
				}
			}
		}
		else {
			insert_query("tbltodolist", array("date" => "now()", "title" => "Manual Upgrade Required", "description" => "Manual Upgrade Required for Service ID: " . $relid, "admin" => "", "status" => "Pending", "duedate" => date("Y-m-d")));
		}
	}

	update_query("tblupgrades", array("status" => "Completed"), array("id" => $upgradeid));
}

function validateUpgradePromo($promocode) {
	global $_LANG;

	$result = select_query("tblpromotions", "", array("code" => $promocode));
	$data = mysql_fetch_array($result);
	$id = $data['id'];
	$recurringtype = $data['type'];
	$recurringvalue = $data['value'];
	$recurring = $data['recurring'];
	$cycles = $data['cycles'];
	$appliesto = $data['appliesto'];
	$requires = $data['requires'];
	$maxuses = $data['maxuses'];
	$uses = $data['uses'];
	$startdate = $data['startdate'];
	$expiredate = $data['expirationdate'];
	$existingclient = $data['existingclient'];
	$onceperclient = $data['onceperclient'];
	$upgrades = $data['upgrades'];
	$upgradeconfig = $data['upgradeconfig'];
	$upgradeconfig = unserialize($upgradeconfig);
	$type = $upgradeconfig['discounttype'];
	$value = $upgradeconfig['value'];
	$configoptions = $upgradeconfig['configoptions'];

	if (!$id) {
		return $_LANG['ordercodenotfound'];
	}


	if (!$upgrades) {
		return $_LANG['promoappliedbutnodiscount'];
	}


	if ($startdate != "0000-00-00") {
		$startdate = str_replace("-", "", $startdate);

		if (date("Ymd") < $startdate) {
			return $_LANG['orderpromoprestart'];
		}
	}


	if ($expiredate != "0000-00-00") {
		$expiredate = str_replace("-", "", $expiredate);

		if ($expiredate < date("Ymd")) {
			return $_LANG['orderpromoexpired'];
		}
	}


	if (0 < $maxuses && $maxuses <= $uses) {
		return $_LANG['orderpromomaxusesreached'];
	}


	if ($onceperclient) {
		$result = select_query("tblorders", "count(*)", array("status" => "Active", "userid" => $_SESSION['uid'], "promocode" => $promocode));
		$orderCount = mysql_fetch_array($result);

		if (0 < $orderCount[0]) {
			return $_LANG['promoonceperclient'];
		}
	}

	$promodesc = ($type == "Percentage" ? $value . "%" : formatCurrency($value));
	$promodesc .= " " . $_LANG['orderdiscount'];

	if (!$recurring) {
		$recurringvalue = 0;
		$recurringtype = "";
	}

	$recurringpromodesc = (($recurring && 0 < $recurringvalue) ? $recurringpromodesc = ($recurringtype == "Percentage" ? $recurringvalue . "%" : formatCurrency($recurringvalue)) : "");
	$cycles = explode(",", $cycles);
	$appliesto = explode(",", $appliesto);
	$requires = explode(",", $requires);
	return array("id" => $id, "cycles" => $cycles, "appliesto" => $appliesto, "requires" => $requires, "type" => $upgradeconfig['type'], "value" => $upgradeconfig['value'], "discounttype" => $upgradeconfig['discounttype'], "configoptions" => $upgradeconfig['configoptions'], "desc" => $promodesc, "recurringvalue" => $recurringvalue, "recurringtype" => $recurringtype, "recurringdesc" => $recurringpromodesc);
}

?>