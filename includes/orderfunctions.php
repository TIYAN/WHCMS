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

function getOrderStatusColour($status) {
	$statuscolors = array("Active" => "779500", "Pending" => "CC0000", "Fraud" => "000000", "Cancelled" => "888");
	return "<span style=\"color:#" . $statuscolors[$status] . "\">" . $status . "</span>";
}

function getProductInfo($pid) {
	$result = select_query("tblproducts", "tblproducts.id,tblproducts.gid,tblproducts.type,tblproducts.name AS prodname,tblproductgroups.name AS groupname,tblproducts.description,tblproducts.freedomain,tblproducts.freedomainpaymentterms,tblproducts.freedomaintlds,tblproducts.stockcontrol,tblproducts.qty", array("tblproducts.id" => $pid), "", "", "", "tblproductgroups ON tblproductgroups.id=tblproducts.gid");
	$data = mysql_fetch_array($result);
	$productinfo = array();
	$productinfo['pid'] = $data['id'];
	$productinfo['gid'] = $data['gid'];
	$productinfo['type'] = $data['type'];
	$productinfo['groupname'] = $data['groupname'];
	$productinfo['name'] = $data['prodname'];
	$productinfo['description'] = nl2br($data['description']);
	$productinfo['freedomain'] = $data['freedomain'];
	$productinfo['freedomainpaymentterms'] = explode(",", $data['freedomainpaymentterms']);
	$productinfo['freedomaintlds'] = explode(",", $data['freedomaintlds']);
	$stockcontrol = $data['stockcontrol'];

	if ($stockcontrol) {
		$productinfo['qty'] = $data['qty'];
	}

	return $productinfo;
}

function getPricingInfo($pid, $inclconfigops = false, $upgrade = false) {
	global $CONFIG;
	global $_LANG;
	global $currency;

	$result = select_query("tblproducts", "", array("id" => $pid));
	$data = mysql_fetch_array($result);
	$paytype = $data['paytype'];
	$freedomain = $data['freedomain'];
	$freedomainpaymentterms = $data['freedomainpaymentterms'];
	$result = select_query("tblpricing", "", array("type" => "product", "currency" => $currency['id'], "relid" => $pid));
	$data = mysql_fetch_array($result);
	$msetupfee = $data['msetupfee'];
	$qsetupfee = $data['qsetupfee'];
	$ssetupfee = $data['ssetupfee'];
	$asetupfee = $data['asetupfee'];
	$bsetupfee = $data['bsetupfee'];
	$tsetupfee = $data['tsetupfee'];
	$monthly = $data['monthly'];
	$quarterly = $data['quarterly'];
	$semiannually = $data['semiannually'];
	$annually = $data['annually'];
	$biennially = $data['biennially'];
	$triennially = $data['triennially'];
	$freedomainpaymentterms = explode(",", $freedomainpaymentterms);
	$monthlypricingbreakdown = $CONFIG['ProductMonthlyPricingBreakdown'];
	$minprice = 0;
	$mincycle = "";
	$hasconfigoptions = false;

	if ($paytype == "free") {
		$pricing['type'] = $mincycle = "free";
	}
	else {
		if ($paytype == "onetime") {
			$configoptions = getCartConfigOptions($pid, array(), "onetime", "", true);

			if (count($configoptions)) {
				if ($inclconfigops) {
					foreach ($configoptions as $option) {
						$monthly += $option['selectedsetup'] + $option['selectedrecurring'];
					}
				}

				$hasconfigoptions = true;
			}

			$minprice = $monthly;
			$pricing['type'] = $mincycle = "onetime";
			$pricing['onetime'] = formatCurrency($monthly);

			if ($msetupfee != "0.00") {
				$pricing->onetime .= " + " . formatCurrency($msetupfee) . " " . $_LANG['ordersetupfee'];
			}


			if ((in_array("onetime", $freedomainpaymentterms) && $freedomain) && !$upgrade) {
				$pricing->onetime .= " (" . $_LANG['orderfreedomainonly'] . ")";
			}
		}
		else {
			if ($paytype == "recurring") {
				$pricing['type'] = "recurring";

				if (0 <= $monthly) {
					$configoptions = getCartConfigOptions($pid, array(), "monthly", "", true);

					if (count($configoptions)) {
						if ($inclconfigops) {
							foreach ($configoptions as $option) {
								$msetupfee += $option['selectedsetup'];
								$monthly += $option['selectedrecurring'];
							}
						}

						$hasconfigoptions = true;
					}


					if (!$mincycle) {
						$minprice = $monthly;
						$mincycle = "monthly";
					}


					if ($monthlypricingbreakdown) {
						$pricing['monthly'] = $_LANG['orderpaymentterm1month'] . " - " . formatCurrency($monthly);
					}
					else {
						$pricing['monthly'] = formatCurrency($monthly) . " " . $_LANG['orderpaymenttermmonthly'];
					}


					if ($msetupfee != "0.00") {
						$pricing->monthly .= " + " . formatCurrency($msetupfee) . " " . $_LANG['ordersetupfee'];
					}


					if ((in_array("monthly", $freedomainpaymentterms) && $freedomain) && !$upgrade) {
						$pricing->monthly .= " (" . $_LANG['orderfreedomainonly'] . ")";
					}
				}


				if (0 <= $quarterly) {
					$configoptions = getCartConfigOptions($pid, array(), "quarterly", "", true);

					if (count($configoptions)) {
						if ($inclconfigops) {
							foreach ($configoptions as $option) {
								$qsetupfee += $option['selectedsetup'];
								$quarterly += $option['selectedrecurring'];
							}
						}

						$hasconfigoptions = true;
					}


					if (!$mincycle) {
						$minprice = $quarterly;
						$mincycle = "quarterly";
					}


					if ($monthlypricingbreakdown) {
						$pricing['quarterly'] = $_LANG['orderpaymentterm3month'] . " - " . formatCurrency($quarterly / 3);
					}
					else {
						$pricing['quarterly'] = formatCurrency($quarterly) . " " . $_LANG['orderpaymenttermquarterly'];
					}


					if ($qsetupfee != "0.00") {
						$pricing->quarterly .= " + " . formatCurrency($qsetupfee) . " " . $_LANG['ordersetupfee'];
					}


					if ((in_array("quarterly", $freedomainpaymentterms) && $freedomain) && !$upgrade) {
						$pricing->quarterly .= " (" . $_LANG['orderfreedomainonly'] . ")";
					}
				}


				if (0 <= $semiannually) {
					$configoptions = getCartConfigOptions($pid, array(), "semiannually", "", true);

					if (count($configoptions)) {
						if ($inclconfigops) {
							foreach ($configoptions as $option) {
								$ssetupfee += $option['selectedsetup'];
								$semiannually += $option['selectedrecurring'];
							}
						}

						$hasconfigoptions = true;
					}


					if (!$mincycle) {
						$minprice = $semiannually;
						$mincycle = "semiannually";
					}


					if ($monthlypricingbreakdown) {
						$pricing['semiannually'] = $_LANG['orderpaymentterm6month'] . " - " . formatCurrency($semiannually / 6);
					}
					else {
						$pricing['semiannually'] = formatCurrency($semiannually) . " " . $_LANG['orderpaymenttermsemiannually'];
					}


					if ($ssetupfee != "0.00") {
						$pricing->semiannually .= " + " . formatCurrency($ssetupfee) . " " . $_LANG['ordersetupfee'];
					}


					if ((in_array("semiannually", $freedomainpaymentterms) && $freedomain) && !$upgrade) {
						$pricing->semiannually .= " (" . $_LANG['orderfreedomainonly'] . ")";
					}
				}


				if (0 <= $annually) {
					$configoptions = getCartConfigOptions($pid, array(), "annually", "", true);

					if (count($configoptions)) {
						if ($inclconfigops) {
							foreach ($configoptions as $option) {
								$asetupfee += $option['selectedsetup'];
								$annually += $option['selectedrecurring'];
							}
						}

						$hasconfigoptions = true;
					}


					if (!$mincycle) {
						$minprice = $annually;
						$mincycle = "annually";
					}


					if ($monthlypricingbreakdown) {
						$pricing['annually'] = $_LANG['orderpaymentterm12month'] . " - " . formatCurrency($annually / 12);
					}
					else {
						$pricing['annually'] = formatCurrency($annually) . " " . $_LANG['orderpaymenttermannually'];
					}


					if ($asetupfee != "0.00") {
						$pricing->annually .= " + " . formatCurrency($asetupfee) . " " . $_LANG['ordersetupfee'];
					}


					if ((in_array("annually", $freedomainpaymentterms) && $freedomain) && !$upgrade) {
						$pricing->annually .= " (" . $_LANG['orderfreedomainonly'] . ")";
					}
				}


				if (0 <= $biennially) {
					$configoptions = getCartConfigOptions($pid, array(), "biennially", "", true);

					if (count($configoptions)) {
						if ($inclconfigops) {
							foreach ($configoptions as $option) {
								$bsetupfee += $option['selectedsetup'];
								$biennially += $option['selectedrecurring'];
							}
						}

						$hasconfigoptions = true;
					}


					if (!$mincycle) {
						$minprice = $biennially;
						$mincycle = "biennially";
					}


					if ($monthlypricingbreakdown) {
						$pricing['biennially'] = $_LANG['orderpaymentterm24month'] . " - " . formatCurrency($biennially / 24);
					}
					else {
						$pricing['biennially'] = formatCurrency($biennially) . " " . $_LANG['orderpaymenttermbiennially'];
					}


					if ($bsetupfee != "0.00") {
						$pricing->biennially .= " + " . formatCurrency($bsetupfee) . " " . $_LANG['ordersetupfee'];
					}


					if ((in_array("biennially", $freedomainpaymentterms) && $freedomain) && !$upgrade) {
						$pricing->biennially .= " (" . $_LANG['orderfreedomainonly'] . ")";
					}
				}


				if (0 <= $triennially) {
					$configoptions = getCartConfigOptions($pid, array(), "triennially", "", true);

					if (count($configoptions)) {
						if ($inclconfigops) {
							foreach ($configoptions as $option) {
								$tsetupfee += $option['selectedsetup'];
								$triennially += $option['selectedrecurring'];
							}
						}

						$hasconfigoptions = true;
					}


					if (!$mincycle) {
						$minprice = $triennially;
						$mincycle = "triennially";
					}


					if ($monthlypricingbreakdown) {
						$pricing['triennially'] = $_LANG['orderpaymentterm36month'] . " - " . formatCurrency($triennially / 36);
					}
					else {
						$pricing['triennially'] = formatCurrency($triennially) . " " . $_LANG['orderpaymenttermtriennially'];
					}


					if ($tsetupfee != "0.00") {
						$pricing->triennially .= " + " . formatCurrency($tsetupfee) . " " . $_LANG['ordersetupfee'];
					}


					if ((in_array("triennially", $freedomainpaymentterms) && $freedomain) && !$upgrade) {
						$pricing->triennially .= " (" . $_LANG['orderfreedomainonly'] . ")";
					}
				}
			}
		}
	}

	$pricing['hasconfigoptions'] = $hasconfigoptions;

	if ($pricing['onetime']) {
		$pricing['cycles']['onetime'] = $pricing['onetime'];
	}


	if ($pricing['monthly']) {
		$pricing['cycles']['monthly'] = $pricing['monthly'];
	}


	if ($pricing['quarterly']) {
		$pricing['cycles']['quarterly'] = $pricing['quarterly'];
	}


	if ($pricing['semiannually']) {
		$pricing['cycles']['semiannually'] = $pricing['semiannually'];
	}


	if ($pricing['annually']) {
		$pricing['cycles']['annually'] = $pricing['annually'];
	}


	if ($pricing['biennially']) {
		$pricing['cycles']['biennially'] = $pricing['biennially'];
	}


	if ($pricing['triennially']) {
		$pricing['cycles']['triennially'] = $pricing['triennially'];
	}

	$pricing['rawpricing'] = array("msetupfee" => format_as_currency($msetupfee), "qsetupfee" => format_as_currency($qsetupfee), "ssetupfee" => format_as_currency($ssetupfee), "asetupfee" => format_as_currency($asetupfee), "bsetupfee" => format_as_currency($bsetupfee), "tsetupfee" => format_as_currency($tsetupfee), "monthly" => format_as_currency($monthly), "quarterly" => format_as_currency($quarterly), "semiannually" => format_as_currency($semiannually), "annually" => format_as_currency($annually), "biennially" => format_as_currency($biennially), "triennially" => format_as_currency($triennially));
	$pricing['minprice'] = array("price" => formatCurrency($minprice), "cycle" => $mincycle);
	return $pricing;
}

function calcCartTotals($checkout = "", $ignorenoconfig = "") {
	global $CONFIG;
	global $_LANG;
	global $remote_ip;
	global $currency;
	global $promo_data;

	$cart_total = $cart_discount = $cart_tax = 0;
	run_hook("PreCalculateCartTotals", $_SESSION['cart']);

	if (!$ignorenoconfig) {
		if (array_key_exists("products", $_SESSION['cart'])) {
			foreach ($_SESSION['cart']['products'] as $key => $productdata) {

				if ($productdata['noconfig']) {
					unset($_SESSION['cart']['products'][$key]);
					continue;
				}
			}
		}

		$bundlewarnings = bundlesValidateCheckout();

		if (array_key_exists("products", $_SESSION['cart'])) {
			$_SESSION['cart']['products'] = array_values($_SESSION['cart']['products']);
		}
	}


	if ($checkout) {
		if (!$_SESSION['cart']) {
			return false;
		}

		run_hook("PreShoppingCartCheckout", $_SESSION['cart']);
		$order_number = generateUniqueID();
		$paymentmethod = $_SESSION['cart']['paymentmethod'];
		$availablegateways = getAvailableOrderPaymentGateways();

		if (!array_key_exists($paymentmethod, $availablegateways)) {
			foreach ($availablegateways as $k => $v) {
				$paymentmethod = $k;
				break;
			}
		}

		$userid = $_SESSION['uid'];
		$ordernotes = "";

		if ($_SESSION['cart']['notes'] && $_SESSION['cart']['notes'] != $_LANG['ordernotesdescription']) {
			$ordernotes = $_SESSION['cart']['notes'];
		}

		$cartitems = count($_SESSION['cart']['products']) + count($_SESSION['cart']['addons']) + count($_SESSION['cart']['domains']) + count($_SESSION['cart']['renewals']);

		if (!$cartitems) {
			return false;
		}

		$orderid = insert_query("tblorders", array("ordernum" => $order_number, "userid" => $userid, "contactid" => $_SESSION['cart']['contact'], "date" => "now()", "status" => "Pending", "paymentmethod" => $paymentmethod, "ipaddress" => $remote_ip, "notes" => $ordernotes));
		logActivity("New Order Placed - Order ID: " . $orderid . " - User ID: " . $userid);
		$domaineppcodes = array();
	}

	$promotioncode = (array_key_exists("promo", $_SESSION['cart']) ? $_SESSION['cart']['promo'] : "");

	if ($promotioncode) {
		$result = select_query("tblpromotions", "", array("code" => $promotioncode));
		$promo_data = mysql_fetch_array($result);
	}


	if (!isset($_SESSION['uid'])) {
		if (!$_SESSION['cart']['user']['country']) {
			$_SESSION['cart']['user']['country'] = $CONFIG['DefaultCountry'];
		}

		$state = $_SESSION['cart']['user']['state'];
		$country = $_SESSION['cart']['user']['country'];
	}
	else {
		$clientsdetails = getClientsDetails($_SESSION['uid']);
		$state = $clientsdetails['state'];
		$country = $clientsdetails['country'];
	}


	if ($CONFIG['TaxEnabled']) {
		$taxdata = getTaxRate(1, $state, $country);
		$taxname = $taxdata['name'];
		$taxrate = $taxdata['rate'];
		$rawtaxrate = $taxrate;
		$inctaxrate = $taxrate / 100 + 1;
		$taxrate /= 100;
		$taxdata = getTaxRate(2, $state, $country);
		$taxname2 = $taxdata['name'];
		$taxrate2 = $taxdata['rate'];
		$rawtaxrate2 = $taxrate2;
		$inctaxrate2 = $taxrate2 / 100 + 1;
		$taxrate2 /= 100;
	}


	if ($CONFIG['TaxInclusiveDeduct'] && ((!$taxrate && !$taxrate2) || $clientsdetails['taxexempt'])) {
		$result = select_query("tbltax", "", "");
		$data = mysql_fetch_array($result);
		$excltaxrate = 1 + $data['taxrate'] / 100;
	}
	else {
		$CONFIG['TaxInclusiveDeduct'] = 0;
	}

	$cartdata = $productsarray = $tempdomains = $orderproductids = $orderdomainids = $orderaddonids = $orderrenewalids = $freedomains = array();
	$recurring_cycles_total = array("monthly" => 0, "quarterly" => 0, "semiannually" => 0, "annually" => 0, "biennially" => 0, "triennially" => 0);

	if (array_key_exists("products", $_SESSION['cart']) && is_array($_SESSION['cart']['products'])) {
		foreach ($_SESSION['cart']['products'] as $key => $productdata) {
			$result = select_query("tblproducts", "tblproducts.id,tblproducts.gid,tblproductgroups.name AS groupname,tblproducts.name,tblproducts.paytype,tblproducts.allowqty,tblproducts.proratabilling,tblproducts.proratadate,tblproducts.proratachargenextmonth,tblproducts.tax,tblproducts.servertype,tblproducts.servergroup,tblproducts.stockcontrol,tblproducts.freedomain,tblproducts.freedomainpaymentterms,tblproducts.freedomaintlds", array("tblproducts.id" => $productdata['pid']), "", "", "", "tblproductgroups ON tblproductgroups.id=tblproducts.gid");
			$data = mysql_fetch_array($result);
			$pid = $data['id'];
			$gid = $data['gid'];
			$groupname = $data['groupname'];
			$productname = $data['name'];
			$paytype = $data['paytype'];
			$allowqty = $data['allowqty'];
			$proratabilling = $data['proratabilling'];
			$proratadate = $data['proratadate'];
			$proratachargenextmonth = $data['proratachargenextmonth'];
			$tax = $data['tax'];
			$servertype = $data['servertype'];
			$servergroup = $data['servergroup'];
			$stockcontrol = $data['stockcontrol'];
			$freedomain = $data['freedomain'];

			if ($freedomain) {
				$freedomainpaymentterms = $data['freedomainpaymentterms'];
				$freedomaintlds = $data['freedomaintlds'];
				$freedomainpaymentterms = explode(",", $freedomainpaymentterms);
				$freedomaintlds = explode(",", $freedomaintlds);
			}
			else {
				$freedomainpaymentterms = $freedomaintlds = array();
			}

			$productinfo = getProductInfo($pid);
			$productdata['productinfo'] = $productinfo;

			if (!function_exists("getCustomFields")) {
				require ROOTDIR . "/includes/customfieldfunctions.php";
			}

			$customfields = getCustomFields("product", $pid, "", true, "", $productdata['customfields']);
			$productdata['customfields'] = $customfields;
			$pricing = getPricingInfo($pid);
			$qty = $productdata['qty'];

			if (!$allowqty || !$qty) {
				$qty = 1;
			}

			$productdata['allowqty'] = $allowqty;
			$productdata['qty'] = $qty;

			if ($pricing['type'] == "recurring") {
				$billingcycle = strtolower($productdata['billingcycle']);

				if (!in_array($billingcycle, array("monthly", "quarterly", "semiannually", "annually", "biennially", "triennially"))) {
					$billingcycle = "";
				}


				if ($pricing['rawpricing'][$billingcycle] < 0) {
					$billingcycle = "";
				}


				if (!$billingcycle) {
					if (0 <= $pricing['rawpricing']['monthly']) {
						$billingcycle = "monthly";
					}
					else {
						if (0 <= $pricing['rawpricing']['quarterly']) {
							$billingcycle = "quarterly";
						}
						else {
							if (0 <= $pricing['rawpricing']['semiannually']) {
								$billingcycle = "semiannually";
							}
							else {
								if (0 <= $pricing['rawpricing']['annually']) {
									$billingcycle = "annually";
								}
								else {
									if (0 <= $pricing['rawpricing']['biennially']) {
										$billingcycle = "biennially";
									}
									else {
										if (0 <= $pricing['rawpricing']['triennially']) {
											$billingcycle = "triennially";
										}
									}
								}
							}
						}
					}
				}
			}
			else {
				if ($pricing['type'] == "onetime") {
					$billingcycle = "onetime";
				}
				else {
					$billingcycle = "free";
				}
			}

			$productdata['billingcycle'] = $billingcycle;

			if ($billingcycle == "free") {
				$product_setup = $product_onetime = $product_recurring = "0";
				$databasecycle = "Free Account";
			}
			else {
				if ($billingcycle == "onetime") {
					$product_setup = $pricing['rawpricing']['msetupfee'];
					$product_onetime = $pricing['rawpricing']['monthly'];
					$product_recurring = 0;
					$databasecycle = "One Time";
				}
				else {
					$product_setup = $pricing['rawpricing'][substr($billingcycle, 0, 1) . "setupfee"];
					$product_onetime = $product_recurring = $pricing['rawpricing'][$billingcycle];
					$databasecycle = ucfirst($billingcycle);

					if ($databasecycle == "Semiannually") {
						$databasecycle = "Semi-Annually";
					}
				}
			}

			$before_priceoverride_value = "";

			if ($bundleoverride = bundlesGetProductPriceOverride("product", $key)) {
				$before_priceoverride_value = $product_setup + $product_onetime;
				$product_setup = 0;
				$product_onetime = $product_recurring = $bundleoverride;
			}

			$hookret = run_hook("OrderProductPricingOverride", array("key" => $key, "pid" => $pid, "proddata" => $productdata));
			foreach ($hookret as $hookret2) {

				if (is_array($hookret2)) {
					if ($hookret2['setup']) {
						$product_setup = $hookret2['setup'];
					}


					if ($hookret2['recurring']) {
						$product_onetime = $product_recurring = $hookret2['recurring'];
						continue;
					}

					continue;
				}
			}

			$productdata['pricing']['baseprice'] = formatCurrency($product_onetime);
			$configurableoptions = array();
			$configurableoptions = getCartConfigOptions($pid, $productdata['configoptions'], $billingcycle);
			$configoptions = "";

			if ($configurableoptions) {
				foreach ($configurableoptions as $confkey => $value) {
					$configoptions[] = array("name" => $value['optionname'], "type" => $value['optiontype'], "option" => $value['selectedoption'], "optionname" => $value['selectedname'], "setup" => (0 < $value['selectedsetup'] ? formatCurrency($value['selectedsetup']) : ""), "recurring" => formatCurrency($value['selectedrecurring']), "qty" => $value['selectedqty']);
					$configoptionsdb[$value['id']] = array("value" => $value['selectedvalue'], "qty" => $value['selectedqty']);
					$product_setup += $value['selectedsetup'];
					$product_onetime += $value['selectedrecurring'];

					if (strlen($before_priceoverride_value)) {
						$before_priceoverride_value += $value['selectedrecurring'];
					}


					if ($billingcycle != "onetime") {
						$product_recurring += $value['selectedrecurring'];
						continue;
					}
				}
			}

			$productdata['configoptions'] = $configoptions;

			if (in_array($billingcycle, $freedomainpaymentterms)) {
				$domain = $productdata['domain'];
				$domainparts = explode(".", $domain, 2);
				$tld = "." . $domainparts[1];

				if (in_array($tld, $freedomaintlds)) {
					$freedomains[$domain] = $freedomain;
				}
			}


			if ($proratabilling) {
				$proratavalues = getProrataValues($billingcycle, $product_onetime, $proratadate, $proratachargenextmonth, date("d"), date("m"), date("Y"), $_SESSION['uid']);
				$product_onetime = $proratavalues['amount'];
				$productdata['proratadate'] = fromMySQLDate($proratavalues['date']);
			}


			if ($CONFIG['TaxInclusiveDeduct']) {
				$product_setup = format_as_currency($product_setup / $excltaxrate);
				$product_onetime = format_as_currency($product_onetime / $excltaxrate);
				$product_recurring = format_as_currency($product_recurring / $excltaxrate);
			}

			$product_total_today_db = $product_setup + $product_onetime;
			$product_recurring_db = $product_recurring;
			$productdata['pricing']['setup'] = $product_setup * $qty;
			$productdata['pricing']['recurring'][$billingcycle] = $product_recurring * $qty;
			$productdata['pricing']['totaltoday'] = $product_total_today_db * $qty;

			if ($product_onetime == 0 && $product_recurring == 0) {
				$pricing_text = $_LANG['orderfree'];
			}
			else {
				$pricing_text = "";

				if (strlen($before_priceoverride_value)) {
					$pricing_text .= "<strike>" . formatCurrency($before_priceoverride_value) . "</strike> ";
				}

				$pricing_text .= formatCurrency($product_onetime);

				if (0 < $product_setup) {
					$pricing_text .= " + " . formatCurrency($product_setup) . " " . $_LANG['ordersetupfee'];
				}


				if ($allowqty && 1 < $qty) {
					$pricing_text .= $_LANG['invoiceqtyeach'] . "<br />" . $_LANG['invoicestotal'] . ": " . formatCurrency($productdata['pricing']['totaltoday']);
				}
			}

			$productdata['pricingtext'] = $pricing_text;

			if ($promotioncode) {
				$onetimediscount = $recurringdiscount = $promoid = 0;

				if ($promocalc = CalcPromoDiscount($pid, $databasecycle, $product_total_today_db, $product_recurring_db, $product_setup)) {
					$onetimediscount = $promocalc['onetimediscount'];
					$recurringdiscount = $promocalc['recurringdiscount'];
					$product_total_today_db -= $onetimediscount;
					$product_recurring_db -= $recurringdiscount;
					$cart_discount += $onetimediscount * $qty;
					$promoid = $promo_data['id'];
				}
			}


			if (isset($productdata['priceoverride'])) {
				$product_total_today_db = $product_recurring_db = $product_onetime = $productdata['priceoverride'];
				$product_setup = 0;
			}

			$cart_total += $product_total_today_db * $qty;
			$product_total_qty_recurring = $product_recurring_db * $qty;

			if (($CONFIG['TaxEnabled'] && $tax) && !$clientsdetails['taxexempt']) {
				$cart_tax += $product_total_today_db * $qty;

				if ($CONFIG['TaxType'] == "Exclusive") {
					if ($CONFIG['TaxL2Compound']) {
						$product_total_qty_recurring += $product_total_qty_recurring * $taxrate;
						$product_total_qty_recurring += $product_total_qty_recurring * $taxrate2;
					}
					else {
						$product_total_qty_recurring += $product_total_qty_recurring * $taxrate + $product_total_qty_recurring * $taxrate2;
					}
				}
			}

			$recurring_cycles_total[$billingcycle] += $product_total_qty_recurring;
			$domain = $productdata['domain'];
			$serverhostname = $productdata['server']['hostname'];
			$serverns1prefix = $productdata['server']['ns1prefix'];
			$serverns2prefix = $productdata['server']['ns2prefix'];
			$serverrootpw = encrypt($productdata['server']['rootpw']);

			if ($serverns1prefix && $domain) {
				$serverns1prefix = $serverns1prefix . "." . $domain;
			}


			if ($serverns2prefix && $domain) {
				$serverns2prefix = $serverns2prefix . "." . $domain;
			}


			if ($serverhostname) {
				$domain = ($domain ? $serverhostname . "." . $domain : $serverhostname);
			}

			$productdata['domain'] = $domain;

			if ($checkout) {
				$multiqtyids = array();
				$qtycount = 1;

				while ($qtycount <= $qty) {
					$serverid = ($servertype ? getServerID($servertype, $servergroup) : "0");
					$hostingquerydates = ($databasecycle == "Free Account" ? "0000-00-00" : date("Y-m-d"));
					$serviceid = insert_query("tblhosting", array("userid" => $userid, "orderid" => $orderid, "packageid" => $pid, "server" => $serverid, "regdate" => "now()", "domain" => $domain, "paymentmethod" => $paymentmethod, "firstpaymentamount" => $product_total_today_db, "amount" => $product_recurring_db, "billingcycle" => $databasecycle, "nextduedate" => $hostingquerydates, "nextinvoicedate" => $hostingquerydates, "domainstatus" => "Pending", "ns1" => $serverns1prefix, "ns2" => $serverns2prefix, "password" => $serverrootpw, "promoid" => $promoid));
					$multiqtyids[$qtycount] = $serviceid;
					$orderproductids[] = $serviceid;

					if ($stockcontrol) {
						full_query("UPDATE tblproducts SET qty=qty-1 WHERE id='" . mysql_real_escape_string($pid) . "'");
					}


					if ($configoptionsdb) {
						foreach ($configoptionsdb as $key => $value) {
							insert_query("tblhostingconfigoptions", array("relid" => $serviceid, "configid" => $key, "optionid" => $value['value'], "qty" => $value['qty']));
						}
					}

					foreach ($productdata['customfields'] as $key => $value) {
						insert_query("tblcustomfieldsvalues", array("fieldid" => $value['id'], "relid" => $serviceid, "value" => $value['rawvalue']));
					}

					$productdetails = getInvoiceProductDetails($serviceid, $pid, date("Y-m-d"), $hostingquerydates, $databasecycle, $domain);
					$invoice_description = $productdetails['description'];
					$invoice_tax = $productdetails['tax'];

					if (!$_SESSION['cart']['geninvoicedisabled']) {
						$prodinvoicearray = array();
						$prodinvoicearray['userid'] = $userid;
						$prodinvoicearray['type'] = "Hosting";
						$prodinvoicearray['relid'] = $serviceid;
						$prodinvoicearray['taxed'] = $invoice_tax;
						$prodinvoicearray['duedate'] = $hostingquerydates;
						$prodinvoicearray['paymentmethod'] = $paymentmethod;

						if (0 < $product_setup) {
							$prodinvoicearray['description'] = $productname . " " . $_LANG['ordersetupfee'];
							$prodinvoicearray['amount'] = $product_setup;
							insert_query("tblinvoiceitems", $prodinvoicearray);
							$prodinvoicearray['type'] = "";
							$prodinvoicearray['relid'] = 0;
						}


						if (0 < $product_onetime) {
							$prodinvoicearray['description'] = $invoice_description;
							$prodinvoicearray['amount'] = $product_onetime;
							insert_query("tblinvoiceitems", $prodinvoicearray);
						}

						$promovals = getInvoiceProductPromo($product_total_today_db, $promoid, $userid, $serviceid, $product_setup + $product_onetime);

						if ($promovals['description']) {
							$prodinvoicearray['type'] = "PromoHosting";
							$prodinvoicearray['description'] = $promovals['description'];
							$prodinvoicearray['amount'] = $promovals['amount'];
							insert_query("tblinvoiceitems", $prodinvoicearray);
						}
					}

					$adminemailitems .= $_LANG['orderproduct'] . (": " . $groupname . " - " . $productname . "<br>
");

					if ($domain) {
						$adminemailitems .= $_LANG['orderdomain'] . (": " . $domain . "<br>
");
					}

					foreach ($configurableoptions as $confkey => $value) {
						$adminemailitems .= $value['optionname'] . ": " . $value['selectedname'] . "<br />
";
					}

					foreach ($customfields as $customfield) {

						if (!$customfield['adminonly']) {
							$adminemailitems .= "" . $customfield['name'] . ": " . $customfield['value'] . "<br />
";
							continue;
						}
					}

					$adminemailitems .= $_LANG['firstpaymentamount'] . ": " . formatCurrency($product_total_today_db) . "<br>
";

					if ($product_recurring_db) {
						$adminemailitems .= $_LANG['recurringamount'] . ": " . formatCurrency($product_recurring_db) . "<br>
";
					}

					$adminemailitems .= $_LANG['orderbillingcycle'] . ": " . $_LANG["orderpaymentterm" . str_replace(array("-", " "), "", strtolower($databasecycle))] . "<br>
";

					if ($allowqty && 1 < $qty) {
						$adminemailitems .= $_LANG['quantity'] . (": " . $qty . "<br>
") . $_LANG['invoicestotal'] . ": " . $productdata['pricing']['totaltoday'] . "<br>
";
					}

					$adminemailitems .= "<br>
";
					++$qtycount;
				}
			}

			$addonsarray = array();
			$addons = $productdata['addons'];

			if ($addons) {
				foreach ($addons as $addonid) {
					$result = select_query("tbladdons", "name,description,billingcycle,tax", array("id" => $addonid));
					$data = mysql_fetch_array($result);
					$addon_name = $data['name'];
					$addon_description = $data['description'];
					$addon_billingcycle = $data['billingcycle'];
					$addon_tax = $data['tax'];

					if (!$CONFIG['TaxEnabled']) {
						$addon_tax = "";
					}

					$result = select_query("tblpricing", "msetupfee,monthly", array("type" => "addon", "currency" => $currency['id'], "relid" => $addonid));
					$data = mysql_fetch_array($result);
					$addon_setupfee = $data['msetupfee'];
					$addon_recurring = $data['monthly'];
					$hookret = run_hook("OrderAddonPricingOverride", array("key" => $key, "pid" => $pid, "addonid" => $addonid, "proddata" => $productdata));
					foreach ($hookret as $hookret2) {

						if (is_array($hookret2)) {
							if ($hookret2['setup']) {
								$addon_setupfee = $hookret2['setup'];
							}


							if ($hookret2['recurring']) {
								$addon_recurring = $hookret2['recurring'];
								continue;
							}

							continue;
						}
					}

					$addon_total_today_db = $addon_setupfee + $addon_recurring;
					$addon_recurring_db = $addon_recurring;
					$addon_total_today = $addon_total_today_db * $qty;

					if ($CONFIG['TaxInclusiveDeduct']) {
						$addon_total_today_db = round($addon_total_today_db / $excltaxrate, 2);
						$addon_recurring_db = round($addon_recurring_db / $excltaxrate, 2);
					}


					if ($promotioncode) {
						$onetimediscount = $recurringdiscount = $promoid = 34;

						if ($promocalc = CalcPromoDiscount("A" . $addonid, $addon_billingcycle, $addon_total_today_db, $addon_recurring_db, $addon_setupfee)) {
							$onetimediscount = $promocalc['onetimediscount'];
							$recurringdiscount = $promocalc['recurringdiscount'];
							$addon_total_today_db -= $onetimediscount;
							$addon_recurring_db -= $recurringdiscount;
							$cart_discount += $onetimediscount * $qty;
						}
					}


					if ($checkout) {
						$qtycount = 1;

						while ($qtycount <= $qty) {
							$serviceid = $multiqtyids[$qtycount];
							$addonsetupfee = $addon_total_today_db - $addon_recurring_db;
							$aid = insert_query("tblhostingaddons", array("hostingid" => $serviceid, "addonid" => $addonid, "orderid" => $orderid, "regdate" => "now()", "name" => "", "setupfee" => $addonsetupfee, "recurring" => $addon_recurring_db, "billingcycle" => $addon_billingcycle, "status" => "Pending", "nextduedate" => "now()", "nextinvoicedate" => "now()", "paymentmethod" => $paymentmethod, "tax" => $addon_tax));
							$orderaddonids[] = $aid;
							$adminemailitems .= $_LANG['clientareaaddon'] . (": " . $addon_name . "<br>
") . $_LANG['ordersetupfee'] . ": " . formatCurrency($addonsetupfee) . "<br>
";

							if ($addon_recurring_db) {
								$adminemailitems .= $_LANG['recurringamount'] . ": " . formatCurrency($addon_recurring_db) . "<br>
";
							}

							$adminemailitems .= $_LANG['orderbillingcycle'] . ": " . $_LANG["orderpaymentterm" . str_replace(array("-", " "), "", strtolower($addon_billingcycle))] . "<br>
<br>
";
							++$qtycount;
						}
					}

					$addon_total_today_db *= $qty;
					$cart_total += $addon_total_today_db;
					$addon_recurring_db *= $qty;

					if ($addon_tax && !$clientsdetails['taxexempt']) {
						$cart_tax += $addon_total_today_db;

						if ($CONFIG['TaxType'] == "Exclusive") {
							if ($CONFIG['TaxL2Compound']) {
								$addon_recurring_db += $addon_recurring_db * $taxrate;
								$addon_recurring_db += $addon_recurring_db * $taxrate2;
							}
							else {
								$addon_recurring_db += $addon_recurring_db * $taxrate + $addon_recurring_db * $taxrate2;
							}
						}
					}

					$addon_billingcycle = str_replace(array("-", " "), "", strtolower($addon_billingcycle));
					$recurring_cycles_total[$addon_billingcycle] += $addon_recurring_db;

					if ($addon_setupfee == "0" && $addon_recurring == "0") {
						$pricing_text = $_LANG['orderfree'];
					}
					else {
						$pricing_text = formatCurrency($addon_recurring);

						if ($addon_setupfee != "0.00") {
							$pricing_text .= " + " . formatCurrency($addon_setupfee) . " " . $_LANG['ordersetupfee'];
						}


						if ($allowqty && 1 < $qty) {
							$pricing_text .= $_LANG['invoiceqtyeach'] . "<br />" . $_LANG['invoicestotal'] . ": " . formatCurrency($addon_total_today);
						}
					}

					$addonsarray[] = array("name" => $addon_name, "pricingtext" => $pricing_text, "setup" => formatCurrency($addon_setupfee), "recurring" => formatCurrency($addon_recurring), "totaltoday" => formatCurrency($addon_total_today));
					$productdata['pricing']['setup'] += $addon_setupfee * $qty;
					$productdata['pricing']['addons'] += $addon_recurring * $qty;
					$productdata['pricing']['recurring'][$addon_billingcycle] += $addon_recurring * $qty;
					$productdata['pricing']['totaltoday'] += $addon_total_today;
				}
			}

			$productdata['addons'] = $addonsarray;
			$totaltaxrates = 1;

			if (($CONFIG['TaxEnabled'] && $tax) && !$clientsdetails['taxexempt']) {
				$product_tax = $productdata['pricing']['totaltoday'];

				if ($CONFIG['TaxType'] == "Inclusive") {
					$totaltaxrates = 1 + ($taxrate + $taxrate2);
					$total_without_tax = $productdata['pricing']['totaltoday'] = $product_tax / $totaltaxrates;
					$total_tax_1 = $total_without_tax * $taxrate;
					$total_tax_2 = $total_without_tax * $taxrate2;
				}
				else {
					$total_tax_1 = $product_tax * $taxrate;

					if ($CONFIG['TaxL2Compound']) {
						$total_tax_2 = ($product_tax + $total_tax_1) * $taxrate2;
					}
					else {
						$total_tax_2 = $product_tax * $taxrate2;
					}
				}

				$total_tax_1 = round($total_tax_1, 2);
				$total_tax_2 = round($total_tax_2, 2);
				$productdata['pricing']['totaltoday'] += $total_tax_1 + $total_tax_2;

				if (0 < $total_tax_1) {
					$productdata['pricing']['tax1'] = formatCurrency($total_tax_1);
				}


				if (0 < $total_tax_2) {
					$productdata['pricing']['tax2'] = formatCurrency($total_tax_2);
				}
			}

			$productdata['pricing']['setup'] = formatCurrency($productdata['pricing']['setup']);
			foreach ($productdata['pricing']['recurring'] as $cycle => $recurring) {
				unset($productdata['pricing']['recurring'][$cycle]);

				if (0 < $recurring) {
					$recurringwithtax = $recurring;

					if ((($CONFIG['TaxEnabled'] && $tax) && !$clientsdetails['taxexempt']) && $CONFIG['TaxType'] == "Exclusive") {
						$rectax = $recurringwithtax * $taxrate;

						if ($CONFIG['TaxL2Compound']) {
							$rectax += ($recurringwithtax + $rectax) * $taxrate2;
						}
						else {
							$rectax += $recurringwithtax * $taxrate2;
						}

						$recurringwithtax += $rectax;
					}

					$productdata['pricing']['recurring'][$_LANG["orderpaymentterm" . $cycle]] = formatCurrency($recurringwithtax);
					$productdata['pricing']['recurringexcltax'][$_LANG["orderpaymentterm" . $cycle]] = formatCurrency($recurring / $totaltaxrates);
					continue;
				}
			}


			if (0 < $productdata['pricing']['addons']) {
				$productdata['pricing']['addons'] = formatCurrency($productdata['pricing']['addons']);
			}

			$productdata['pricing']['totaltoday'] = formatCurrency($productdata['pricing']['totaltoday']);
			$productsarray[$key] = $productdata;
		}
	}

	$cartdata['products'] = $productsarray;
	$addonsarray = array();

	if (array_key_exists("addons", $_SESSION['cart']) && is_array($_SESSION['cart']['addons'])) {
		foreach ($_SESSION['cart']['addons'] as $key => $addon) {
			$addonid = $addon['id'];
			$serviceid = $addon['productid'];
			$result = select_query("tbladdons", "name,description,billingcycle,tax", array("id" => $addonid));
			$data = mysql_fetch_array($result);
			$addon_name = $data['name'];
			$addon_description = $data['description'];
			$addon_billingcycle = $data['billingcycle'];
			$addon_tax = $data['tax'];

			if (!$CONFIG['TaxEnabled']) {
				$addon_tax = "";
			}

			$result = select_query("tblpricing", "msetupfee,monthly", array("type" => "addon", "currency" => $currency['id'], "relid" => $addonid));
			$data = mysql_fetch_array($result);
			$addon_setupfee = $data['msetupfee'];
			$addon_recurring = $data['monthly'];
			$hookret = run_hook("OrderAddonPricingOverride", array("key" => $key, "addonid" => $addonid, "serviceid" => $serviceid));
			foreach ($hookret as $hookret2) {

				if (strlen($hookret2)) {
					if ($hookret2['setup']) {
						$addon_setupfee = $hookret2['setup'];
					}


					if ($hookret2['recurring']) {
						$addon_recurring = $hookret2['recurring'];
						continue;
					}

					continue;
				}
			}

			$addon_total_today_db = $addon_setupfee + $addon_recurring;
			$addon_recurring_db = $addon_recurring;

			if ($CONFIG['TaxInclusiveDeduct']) {
				$addon_total_today_db = round($addon_total_today_db / $excltaxrate, 2);
				$addon_recurring_db = round($addon_recurring_db / $excltaxrate, 2);
			}


			if ($promotioncode) {
				$onetimediscount = $recurringdiscount = $promoid = 0;

				if ($promocalc = CalcPromoDiscount("A" . $addonid, $addon_billingcycle, $addon_total_today_db, $addon_recurring_db, $addon_setupfee)) {
					$onetimediscount = $promocalc['onetimediscount'];
					$recurringdiscount = $promocalc['recurringdiscount'];
					$addon_total_today_db -= $onetimediscount;
					$addon_recurring_db -= $recurringdiscount;
					$cart_discount += $onetimediscount;
				}
			}


			if ($checkout) {
				$addonsetupfee = $addon_total_today_db - $addon_recurring_db;
				$aid = insert_query("tblhostingaddons", array("hostingid" => $serviceid, "addonid" => $addonid, "orderid" => $orderid, "regdate" => "now()", "name" => "", "setupfee" => $addonsetupfee, "recurring" => $addon_recurring_db, "billingcycle" => $addon_billingcycle, "status" => "Pending", "nextduedate" => "now()", "nextinvoicedate" => "now()", "paymentmethod" => $paymentmethod, "tax" => $addon_tax));
				$orderaddonids[] = $aid;
				$adminemailitems .= $_LANG['clientareaaddon'] . (": " . $addon_name . "<br>
") . $_LANG['ordersetupfee'] . ": " . formatCurrency($addonsetupfee) . "<br>
";

				if ($addon_recurring_db) {
					$adminemailitems .= $_LANG['recurringamount'] . ": " . formatCurrency($addon_recurring_db) . "<br>
";
				}

				$adminemailitems .= $_LANG['orderbillingcycle'] . ": " . $_LANG["orderpaymentterm" . str_replace(array("-", " "), "", strtolower($addon_billingcycle))] . "<br>
<br>
";
			}

			$cart_total += $addon_total_today_db;

			if ($addon_tax && !$clientsdetails['taxexempt']) {
				$cart_tax += $addon_total_today_db;

				if ($CONFIG['TaxType'] == "Exclusive") {
					if ($CONFIG['TaxL2Compound']) {
						$addon_recurring_db += $addon_recurring_db * $taxrate;
						$addon_recurring_db += $addon_recurring_db * $taxrate2;
					}
					else {
						$addon_recurring_db = $addon_recurring_db + $addon_recurring_db * $taxrate + $addon_recurring_db * $taxrate2;
					}
				}
			}

			$addon_billingcycle = str_replace(array("-", " "), "", strtolower($addon_billingcycle));
			$recurring_cycles_total[$addon_billingcycle] += $addon_recurring_db;

			if ($addon_setupfee == "0" && $addon_recurring == "0") {
				$pricing_text = $_LANG['orderfree'];
			}
			else {
				$pricing_text = formatCurrency($addon_recurring);

				if ($addon_setupfee != "0.00") {
					$pricing_text .= " + " . formatCurrency($addon_setupfee) . " " . $_LANG['ordersetupfee'];
				}
			}

			$result = select_query("tblhosting", "tblproducts.name,tblhosting.domain", array("tblhosting.id" => $serviceid), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");
			$data = mysql_fetch_array($result);
			$productname = $data['name'];
			$domainname = $data['domain'];
			$addonsarray[] = array("name" => $addon_name, "productname" => $productname, "domainname" => $domainname, "pricingtext" => $pricing_text);
		}

		$cartdata['addons'] = $addonsarray;
	}

	include ROOTDIR . "/includes/additionaldomainfields.php";
	$totaldomainprice = 0;

	if (array_key_exists("domains", $_SESSION['cart']) && is_array($_SESSION['cart']['domains'])) {
		$result = select_query("tblpricing", "", array("type" => "domainaddons", "currency" => $currency['id'], "relid" => 0));
		$data = mysql_fetch_array($result);
		$domaindnsmanagementprice = $data['msetupfee'];
		$domainemailforwardingprice = $data['qsetupfee'];
		$domainidprotectionprice = $data['ssetupfee'];
		foreach ($_SESSION['cart']['domains'] as $key => $domain) {
			$domaintype = $domain['type'];
			$domainname = $domain['domain'];
			$regperiod = $domain['regperiod'];
			$domainparts = explode(".", $domainname, 2);
			$sld = $domainparts[0];
			$tld = $domainparts[1];
			$temppricelist = getTLDPriceList("." . $tld);

			if (!isset($temppricelist[$regperiod][$domaintype])) {
				$tldyears = array_keys($temppricelist);
				$regperiod = $tldyears[0];
			}


			if (!isset($temppricelist[$regperiod][$domaintype])) {
				exit("Invalid TLD/Registration Period Supplied for Domain Registration");
			}


			if (array_key_exists($domainname, $freedomains)) {
				$tldyears = array_keys($temppricelist);
				$regperiod = $tldyears[0];
				$domainprice = "0.00";
				$renewprice = ($freedomains[$domainname] == "once" ? $temppricelist[$regperiod]['renew'] : $renewprice = "0.00");
			}
			else {
				$domainprice = $temppricelist[$regperiod][$domaintype];
				$renewprice = $temppricelist[$regperiod]['renew'];
			}

			$before_priceoverride_value = "";

			if ($bundleoverride = bundlesGetProductPriceOverride("domain", $key)) {
				$before_priceoverride_value = $domainprice;
				$domainprice = $renewprice = $bundleoverride;
			}

			$hookret = run_hook("OrderDomainPricingOverride", array("type" => $domaintype, "domain" => $domainname, "regperiod" => $regperiod, "dnsmanagement" => $domain['dnsmanagement'], "emailforwarding" => $domain['emailforwarding'], "idprotection" => $domain['idprotection'], "eppcode" => html_entity_decode($domain['eppcode'])));
			foreach ($hookret as $hookret2) {

				if (strlen($hookret2)) {
					$before_priceoverride_value = $domainprice;
					$domainprice = $hookret2;
					continue;
				}
			}


			if ($domain['dnsmanagement']) {
				$dnsmanagement = true;
				$domainprice += $domaindnsmanagementprice * $regperiod;
				$renewprice += $domaindnsmanagementprice * $regperiod;

				if (strlen($before_priceoverride_value)) {
					$before_priceoverride_value += $domaindnsmanagementprice * $regperiod;
				}
			}
			else {
				$dnsmanagement = false;
			}


			if ($domain['emailforwarding']) {
				$emailforwarding = true;
				$domainprice += $domainemailforwardingprice * $regperiod;
				$renewprice += $domainemailforwardingprice * $regperiod;

				if (strlen($before_priceoverride_value)) {
					$before_priceoverride_value += $domainemailforwardingprice * $regperiod;
				}
			}
			else {
				$emailforwarding = false;
			}


			if ($domain['idprotection']) {
				$idprotection = true;
				$domainprice += $domainidprotectionprice * $regperiod;
				$renewprice += $domainidprotectionprice * $regperiod;

				if (strlen($before_priceoverride_value)) {
					$before_priceoverride_value += $domainidprotectionprice * $regperiod;
				}
			}
			else {
				$idprotection = false;
			}


			if ($CONFIG['TaxInclusiveDeduct']) {
				$domainprice = round($domainprice / $excltaxrate, 2);
				$renewprice = round($renewprice / $excltaxrate, 2);
			}

			$domain_price_db = $domainprice;
			$domain_renew_price_db = $renewprice;

			if ($promotioncode) {
				$onetimediscount = $recurringdiscount = $promoid = 0;

				if ($promocalc = CalcPromoDiscount("D." . $tld, $regperiod . "Years", $domain_price_db, $domain_renew_price_db)) {
					$onetimediscount = $promocalc['onetimediscount'];
					$recurringdiscount = $promocalc['recurringdiscount'];
					$domain_price_db -= $onetimediscount;
					$domain_renew_price_db -= $recurringdiscount;
					$cart_discount += $onetimediscount;
					$promoid = $promo_data['id'];
				}
			}


			if ($regperiod == "1") {
				$domain_billing_cycle = "annually";
			}
			else {
				if ($regperiod == "2") {
					$domain_billing_cycle = "biennially";
				}
				else {
					if ($regperiod == "3") {
						$domain_billing_cycle = "triennially";
					}
				}
			}

			$recurring_cycles_total[$domain_billing_cycle] += $domain_renew_price_db;

			if ((($CONFIG['TaxEnabled'] && $CONFIG['TaxDomains']) && $CONFIG['TaxType'] == "Exclusive") && !$clientsdetails['taxexempt']) {
				if ($CONFIG['TaxL2Compound']) {
					$recurring_cycles_total[$domain_billing_cycle] += $domain_renew_price_db * $taxrate + ($domain_renew_price_db + $domain_renew_price_db * $taxrate) * $taxrate2;
				}
				else {
					$recurring_cycles_total[$domain_billing_cycle] += $domain_renew_price_db * $taxrate + $domain_renew_price_db * $taxrate2;
				}
			}


			if ($checkout) {
				$donotrenew = ($CONFIG['DomainAutoRenewDefault'] ? "" : "on");
				$domainid = insert_query("tbldomains", array("userid" => $userid, "orderid" => $orderid, "type" => $domaintype, "registrationdate" => "now()", "domain" => $domainname, "firstpaymentamount" => $domain_price_db, "recurringamount" => $domain_renew_price_db, "registrationperiod" => $regperiod, "status" => "Pending", "paymentmethod" => $paymentmethod, "expirydate" => "00000000", "nextduedate" => "now()", "nextinvoicedate" => "now()", "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection, "donotrenew" => $donotrenew, "promoid" => $promoid));
				$orderdomainids[] = $domainid;
				$adminemailitems .= $_LANG['orderdomainregistration'] . ": " . ucfirst($domaintype) . "<br>
" . $_LANG['orderdomain'] . (": " . $domainname . "<br>
") . $_LANG['firstpaymentamount'] . ": " . formatCurrency($domain_price_db) . "<br>
" . $_LANG['recurringamount'] . ": " . formatCurrency($domain_renew_price_db) . "<br>
" . $_LANG['orderregperiod'] . (": " . $regperiod . " ") . $_LANG['orderyears'] . "<br>
";

				if ($dnsmanagement) {
					$adminemailitems .= " + " . $_LANG['domaindnsmanagement'] . "<br>
";
				}


				if ($emailforwarding) {
					$adminemailitems .= " + " . $_LANG['domainemailforwarding'] . "<br>
";
				}


				if ($idprotection) {
					$adminemailitems .= " + " . $_LANG['domainidprotection'] . "<br>
";
				}

				$adminemailitems .= "<br>
";

				if ($domaintype == "register") {
					unset($tempdomainfields);
					$tempdomainfields = $additionaldomainfields["." . $tld];

					if ($tempdomainfields) {
						foreach ($tempdomainfields as $fieldkey => $value) {
							$storedvalue = $domain['fields'][$fieldkey];
							insert_query("tbldomainsadditionalfields", array("domainid" => $domainid, "name" => $value['Name'], "value" => $storedvalue));
						}
					}
				}


				if ($domaintype == "transfer" && $domain['eppcode']) {
					$domaineppcodes[$domainname] = html_entity_decode($domain['eppcode']);
				}
			}

			$pricing_text = "";

			if (strlen($before_priceoverride_value)) {
				$pricing_text .= "<strike>" . formatCurrency($before_priceoverride_value) . "</strike> ";
			}

			$pricing_text .= formatCurrency($domainprice);
			$tempdomains[$key] = array("type" => $domaintype, "domain" => $domainname, "regperiod" => $regperiod, "price" => $pricing_text, "renewprice" => formatCurrency($renewprice), "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection, "eppvalue" => $domain['eppcode']);
			$totaldomainprice += $domain_price_db;
		}
	}

	$cartdata['domains'] = $tempdomains;
	$cart_total += $totaldomainprice;

	if ($CONFIG['TaxDomains']) {
		$cart_tax += $totaldomainprice;
	}

	$orderrenewals = "";

	if (array_key_exists("renewals", $_SESSION['cart']) && is_array($_SESSION['cart']['renewals'])) {
		$result = select_query("tblpricing", "", array("type" => "domainaddons", "currency" => $currency['id'], "relid" => 0));
		$data = mysql_fetch_array($result);
		$domaindnsmanagementprice = $data['msetupfee'];
		$domainemailforwardingprice = $data['qsetupfee'];
		$domainidprotectionprice = $data['ssetupfee'];
		foreach ($_SESSION['cart']['renewals'] as $domainid => $regperiod) {
			$result = select_query("tbldomains", "", array("id" => $domainid));
			$data = mysql_fetch_array($result);
			$domainname = $data['domain'];
			$expirydate = $data['expirydate'];

			if ($expirydate == "0000-00-00") {
				$expirydate = $data['nextduedate'];
			}

			$dnsmanagement = $data['dnsmanagement'];
			$emailforwarding = $data['emailforwarding'];
			$idprotection = $data['idprotection'];
			$domainparts = explode(".", $domainname, 2);
			$sld = $domainparts[0];
			$tld = "." . $domainparts[1];
			$temppricelist = getTLDPriceList($tld, "", true);

			if (!isset($temppricelist[$regperiod]['renew'])) {
				exit("Invalid TLD/Registration Period Supplied for Domain Renewal");
			}

			$renewprice = $temppricelist[$regperiod]['renew'];

			if ($dnsmanagement) {
				$renewprice += $domaindnsmanagementprice * $regperiod;
			}


			if ($emailforwarding) {
				$renewprice += $domainemailforwardingprice * $regperiod;
			}


			if ($idprotection) {
				$renewprice += $domainidprotectionprice * $regperiod;
			}


			if ($CONFIG['TaxInclusiveDeduct']) {
				$renewprice = round($renewprice / $excltaxrate, 2);
			}

			$domain_renew_price_db = $renewprice;

			if ($promotioncode) {
				$onetimediscount = $recurringdiscount = $promoid = 0;

				if ($promocalc = CalcPromoDiscount("D" . $tld, $regperiod . "Years", $domain_renew_price_db, $domain_renew_price_db)) {
					$onetimediscount = $promocalc['onetimediscount'];
					$domain_renew_price_db -= $onetimediscount;
					$cart_discount += $onetimediscount;
				}
			}

			$cart_total += $domain_renew_price_db;

			if ($CONFIG['TaxDomains']) {
				$cart_tax += $domain_renew_price_db;
			}


			if ($checkout) {
				$domain_renew_price_db = format_as_currency($domain_renew_price_db);
				$orderrenewalids[] = $domainid;
				$orderrenewals .= "" . $domainid . "=" . $regperiod . ",";
				$adminemailitems .= $_LANG['domainrenewal'] . (": " . $domainname . " - " . $regperiod . " ") . $_LANG['orderyears'] . "<br>
";
				$domaindesc = $_LANG['domainrenewal'] . (" - " . $domainname . " - " . $regperiod . " ") . $_LANG['orderyears'] . " (" . fromMySQLDate($expirydate) . " - " . fromMySQLDate(getInvoicePayUntilDate($expirydate, $regperiod)) . ")";

				if ($dnsmanagement) {
					$adminemailitems .= " + " . $_LANG['domaindnsmanagement'] . "<br>
";
					$domaindesc .= "
 + " . $_LANG['domaindnsmanagement'];
				}


				if ($emailforwarding) {
					$adminemailitems .= " + " . $_LANG['domainemailforwarding'] . "<br>
";
					$domaindesc .= "
 + " . $_LANG['domainemailforwarding'];
				}


				if ($idprotection) {
					$adminemailitems .= " + " . $_LANG['domainidprotection'] . "<br>
";
					$domaindesc .= "
 + " . $_LANG['domainidprotection'];
				}

				$adminemailitems .= "<br>
";
				$tax = ($CONFIG['TaxDomains'] ? "1" : "0");
				update_query("tbldomains", array("registrationperiod" => $regperiod, "recurringamount" => $domain_renew_price_db), array("id" => $domainid));
				insert_query("tblinvoiceitems", array("userid" => $userid, "type" => "Domain", "relid" => $domainid, "description" => $domaindesc, "amount" => $domain_renew_price_db, "taxed" => $tax, "duedate" => "now()", "paymentmethod" => $paymentmethod));
				$result = select_query("tblinvoiceitems", "tblinvoiceitems.id,tblinvoiceitems.invoiceid", array("type" => "Domain", "relid" => $domainid, "status" => "Unpaid", "tblinvoices.userid" => $_SESSION['uid']), "", "", "", "tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid");

				while ($data = mysql_fetch_array($result)) {
					$itemid = $data['id'];
					$invoiceid = $data['invoiceid'];
					$result2 = select_query("tblinvoiceitems", "COUNT(*)", array("invoiceid" => $invoiceid));
					$data = mysql_fetch_array($result2);
					$itemcount = $data[0];

					if ($itemcount == 1) {
						update_query("tblinvoices", array("status" => "Cancelled"), array("id" => $invoiceid));
						logActivity("Cancelled Previous Domain Renewal Invoice - Invoice ID: " . $invoiceid . " - Domain: " . $domainname);
					}

					delete_query("tblinvoiceitems", array("id" => $itemid));
					updateInvoiceTotal($invoiceid);
					logActivity("Removed Previous Domain Renewal Line Item - Invoice ID: " . $invoiceid . " - Domain: " . $domainname);
				}
			}

			$cartdata['renewals'][$domainid] = array("domain" => $domainname, "regperiod" => $regperiod, "price" => formatCurrency($renewprice), "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection);
		}
	}

	$cart_adjustments = 0;
	$adjustments = run_hook("CartTotalAdjustment", $_SESSION['cart']);
	foreach ($adjustments as $k => $adjvals) {

		if ($checkout) {
			insert_query("tblinvoiceitems", array("userid" => $userid, "type" => "", "relid" => "", "description" => $adjvals['description'], "amount" => $adjvals['amount'], "taxed" => $adjvals['taxed'], "duedate" => "now()", "paymentmethod" => $paymentmethod));
		}

		$adjustments[$k]['amount'] = formatCurrency($adjvals['amount']);
		$cart_adjustments += $adjvals['amount'];

		if ($adjvals['taxed']) {
			$cart_tax += $adjvals['amount'];
			continue;
		}
	}


	if ($CONFIG['TaxEnabled'] && !$clientsdetails['taxexempt']) {
		if ($CONFIG['TaxType'] == "Inclusive") {
			$totaltaxrates = 1 + ($taxrate + $taxrate2);
			$total_without_tax = $cart_tax / $totaltaxrates;
			$total_tax_1 = $total_without_tax * $taxrate;
			$total_tax_2 = $total_without_tax * $taxrate2;
		}
		else {
			$total_tax_1 = $cart_tax * $taxrate;

			if ($CONFIG['TaxL2Compound']) {
				$total_tax_2 = ($cart_tax + $total_tax_1) * $taxrate2;
			}
			else {
				$total_tax_2 = $cart_tax * $taxrate2;
			}
		}

		$total_tax_1 = round($total_tax_1, 2);
		$total_tax_2 = round($total_tax_2, 2);

		if ($CONFIG['TaxType'] == "Inclusive") {
			$cart_total -= $total_tax_1 + $total_tax_2;
		}
	}
	else {
		$total_tax_1 = $total_tax_2 = 0;
	}

	$cart_subtotal = $cart_total + $cart_discount;
	$cart_total += $total_tax_1 + $total_tax_2 + $cart_adjustments;
	$cart_subtotal = format_as_currency($cart_subtotal);
	$cart_discount = format_as_currency($cart_discount);
	$cart_adjustments = format_as_currency($cart_adjustments);
	$total_tax_1 = format_as_currency($total_tax_1);
	$total_tax_2 = format_as_currency($total_tax_2);
	$cart_total = format_as_currency($cart_total);

	if ($checkout) {
		$adminemailitems .= $_LANG['ordertotalduetoday'] . ": " . formatCurrency($cart_total);

		if ($promotioncode && $promo_data['promoapplied']) {
			update_query("tblpromotions", array("uses" => "+1"), array("code" => $promotioncode));
			$promo_recurring = ($promo_data['recurring'] ? "Recurring" : "One Time");
			update_query("tblorders", array("promocode" => $promo_data['code'], "promotype" => $promo_recurring . " " . $promo_data['type'], "promovalue" => $promo_data['value']), array("id" => $orderid));
		}


		if ($_SESSION['cart']['ns1'] && $_SESSION['cart']['ns1']) {
			$ordernameservers = $_SESSION['cart']['ns1'] . "," . $_SESSION['cart']['ns2'];

			if ($_SESSION['cart']['ns3']) {
				$ordernameservers .= "," . $_SESSION['cart']['ns3'];
			}


			if ($_SESSION['cart']['ns4']) {
				$ordernameservers .= "," . $_SESSION['cart']['ns4'];
			}


			if ($_SESSION['cart']['ns5']) {
				$ordernameservers .= "," . $_SESSION['cart']['ns5'];
			}
		}

		$domaineppcodes = (count($domaineppcodes) ? serialize($domaineppcodes) : "");
		$orderdata = array();

		if (is_array($_SESSION['cart']['bundle'])) {
			foreach ($_SESSION['cart']['bundle'] as $bvals) {
				$orderdata['bundleids'][] = $bvals['bid'];
			}
		}

		update_query("tblorders", array("amount" => $cart_total, "nameservers" => $ordernameservers, "transfersecret" => $domaineppcodes, "renewals" => substr($orderrenewals, 0, 0 - 1), "orderdata" => serialize($orderdata)), array("id" => $orderid));
		$invoiceid = 0;

		if (!$_SESSION['cart']['geninvoicedisabled']) {
			if (!$userid) {
				exit("An Error Occurred");
			}

			$invoiceid = createInvoices($userid, true, "", array("products" => $orderproductids, "addons" => $orderaddonids, "domains" => $orderdomainids));

			if ($CONFIG['OrderDaysGrace']) {
				$new_time = mktime(0, 0, 0, date("m"), date("d") + $CONFIG['OrderDaysGrace'], date("Y"));
				$duedate = date("Y-m-d", $new_time);
				update_query("tblinvoices", array("duedate" => $duedate), array("id" => $invoiceid));
			}


			if (!$CONFIG['NoInvoiceEmailOnOrder']) {
				sendMessage("Invoice Created", $invoiceid);
			}
		}


		if ($invoiceid) {
			update_query("tblorders", array("invoiceid" => $invoiceid), array("id" => $orderid));
			$result = select_query("tblinvoices", "status", array("id" => $invoiceid));
			$data = mysql_fetch_array($result);
			$status = $data['status'];

			if ($status == "Paid") {
				$invoiceid = "";
			}
		}


		if (!$_SESSION['adminid']) {
			if (isset($_COOKIE['WHMCSAffiliateID'])) {
				$result = select_query("tblaffiliates", "clientid", array("id" => (int)$_COOKIE['WHMCSAffiliateID']));
				$data = mysql_fetch_array($result);
				$clientid = $data['clientid'];

				if ($clientid && $_SESSION['uid'] != $clientid) {
					foreach ($orderproductids as $orderproductid) {
						insert_query("tblaffiliatesaccounts", array("affiliateid" => (int)$_COOKIE['WHMCSAffiliateID'], "relid" => $orderproductid));
					}
				}
			}


			if (isset($_COOKIE['WHMCSLinkID'])) {
				update_query("tbllinks", array("conversions" => "+1"), array("id" => $_COOKIE['WHMCSLinkID']));
			}
		}

		$result = select_query("tblclients", "firstname, lastname, companyname, email, address1, address2, city, state, postcode, country, phonenumber, ip, host", array("id" => $userid));
		$data = mysql_fetch_array($result);
		list($firstname,$lastname,$companyname,$email,$address1,$address2,$city,$state,$postcode,$country,$phonenumber,$ip,$host) = $data;
		$customfields = getCustomFields("client", "", $userid, "", true);
		$clientcustomfields = "";
		foreach ($customfields as $customfield) {
			$clientcustomfields .= "" . $customfield['name'] . ": " . $customfield['value'] . "<br />
";
		}

		$result = select_query("tblpaymentgateways", "value", array("gateway" => $paymentmethod, "setting" => "name"));
		$data = mysql_fetch_array($result);
		$nicegatewayname = $data['value'];
		sendAdminMessage("New Order Notification", array("order_id" => $orderid, "order_number" => $order_number, "order_date" => fromMySQLDate(date("Y-m-d H:i:s"), true), "invoice_id" => $invoiceid, "order_payment_method" => $nicegatewayname, "order_total" => formatCurrency($cart_total), "client_id" => $userid, "client_first_name" => $firstname, "client_last_name" => $lastname, "client_email" => $email, "client_company_name" => $companyname, "client_address1" => $address1, "client_address2" => $address2, "client_city" => $city, "client_state" => $state, "client_postcode" => $postcode, "client_country" => $country, "client_phonenumber" => $phonenumber, "client_customfields" => $clientcustomfields, "order_items" => $adminemailitems, "order_notes" => nl2br($ordernotes), "client_ip" => $ip, "client_hostname" => $host), "account");

		if (!$_SESSION['cart']['orderconfdisabled']) {
			sendMessage("Order Confirmation", $userid, array("order_id" => $orderid, "order_number" => $order_number, "order_details" => $adminemailitems));
		}

		$_SESSION['cart'] = array();
		$_SESSION['orderdetails'] = array("OrderID" => $orderid, "OrderNumber" => $order_number, "ServiceIDs" => $orderproductids, "DomainIDs" => $orderdomainids, "AddonIDs" => $orderaddonids, "RenewalIDs" => $orderrenewalids, "PaymentMethod" => $paymentmethod, "InvoiceID" => $invoiceid, "TotalDue" => $cart_total, "Products" => $orderproductids, "Domains" => $orderdomainids, "Addons" => $orderaddonids, "Renewals" => $orderrenewalids);
		run_hook("AfterShoppingCartCheckout", $_SESSION['orderdetails']);
	}

	$total_recurringmonthly = ($recurring_cycles_total['monthly'] <= 0 ? "" : formatCurrency($recurring_cycles_total['monthly']));
	$total_recurringquarterly = ($recurring_cycles_total['quarterly'] <= 0 ? "" : formatCurrency($recurring_cycles_total['quarterly']));
	$total_recurringsemiannually = ($recurring_cycles_total['semiannually'] <= 0 ? "" : formatCurrency($recurring_cycles_total['semiannually']));
	$total_recurringannually = ($recurring_cycles_total['annually'] <= 0 ? "" : formatCurrency($recurring_cycles_total['annually']));
	$total_recurringbiennially = ($recurring_cycles_total['biennially'] <= 0 ? "" : formatCurrency($recurring_cycles_total['biennially']));
	$total_recurringtriennially = ($recurring_cycles_total['triennially'] <= 0 ? "" : formatCurrency($recurring_cycles_total['triennially']));
	$cartdata['bundlewarnings'] = $bundlewarnings;
	$cartdata['rawdiscount'] = $cart_discount;
	$cartdata['subtotal'] = formatCurrency($cart_subtotal);
	$cartdata['discount'] = formatCurrency($cart_discount);
	$cartdata['promotype'] = $promo_data['type'];
	$cartdata['promovalue'] = (($promo_data['type'] == "Fixed Amount" || $promo_data['type'] == "Price Override") ? formatCurrency($promo_data['value']) : round($promo_data['value'], 2));
	$cartdata['promorecurring'] = ($promo_data['recurring'] ? $_LANG['recurring'] : $_LANG['orderpaymenttermonetime']);
	$cartdata['taxrate'] = $rawtaxrate;
	$cartdata['taxrate2'] = $rawtaxrate2;
	$cartdata['taxname'] = $taxname;
	$cartdata['taxname2'] = $taxname2;
	$cartdata['taxtotal'] = formatCurrency($total_tax_1);
	$cartdata['taxtotal2'] = formatCurrency($total_tax_2);
	$cartdata['adjustments'] = $adjustments;
	$cartdata['adjustmentstotal'] = formatCurrency($cart_adjustments);
	$cartdata['rawtotal'] = $cart_total;
	$cartdata['total'] = formatCurrency($cart_total);
	$cartdata['totalrecurringmonthly'] = $total_recurringmonthly;
	$cartdata['totalrecurringquarterly'] = $total_recurringquarterly;
	$cartdata['totalrecurringsemiannually'] = $total_recurringsemiannually;
	$cartdata['totalrecurringannually'] = $total_recurringannually;
	$cartdata['totalrecurringbiennially'] = $total_recurringbiennially;
	$cartdata['totalrecurringtriennially'] = $total_recurringtriennially;
	return $cartdata;
}

function SetPromoCode($promotioncode) {
	global $_LANG;

	$_SESSION['cart']['promo'] = "";
	$result = select_query("tblpromotions", "", array("code" => $promotioncode));
	$data = mysql_fetch_array($result);
	$id = $data['id'];
	$maxuses = $data['maxuses'];
	$uses = $data['uses'];
	$startdate = $data['startdate'];
	$expiredate = $data['expirationdate'];
	$newsignups = $data['newsignups'];
	$existingclient = $data['existingclient'];
	$onceperclient = $data['onceperclient'];

	if (!$id) {
		$promoerrormessage = $_LANG['ordercodenotfound'];
		return $promoerrormessage;
	}


	if ($startdate != "0000-00-00") {
		$startdate = str_replace("-", "", $startdate);

		if (date("Ymd") < $startdate) {
			$promoerrormessage = $_LANG['orderpromoprestart'];
			return $promoerrormessage;
		}
	}


	if ($expiredate != "0000-00-00") {
		$expiredate = str_replace("-", "", $expiredate);

		if ($expiredate < date("Ymd")) {
			$promoerrormessage = $_LANG['orderpromoexpired'];
			return $promoerrormessage;
		}
	}


	if (0 < $maxuses) {
		if ($maxuses <= $uses) {
			$promoerrormessage = $_LANG['orderpromomaxusesreached'];
			return $promoerrormessage;
		}
	}


	if ($newsignups && $_SESSION['uid']) {
		$result = select_query("tblorders", "COUNT(*)", array("userid" => $_SESSION['uid']));
		$data = mysql_fetch_array($result);
		$previousorders = $data[0];

		if (0 < $previousorders) {
			$promoerrormessage = $_LANG['promonewsignupsonly'];
			return $promoerrormessage;
		}
	}


	if ($existingclient) {
		if ($_SESSION['uid']) {
			$result = select_query("tblorders", "count(*)", array("status" => "Active", "userid" => $_SESSION['uid']));
			$orderCount = mysql_fetch_array($result);

			if ($orderCount[0] == 0) {
				$promoerrormessage = $_LANG['promoexistingclient'];
				return $promoerrormessage;
			}
		}
		else {
			$promoerrormessage = $_LANG['promoexistingclient'];
			return $promoerrormessage;
		}
	}


	if ($onceperclient) {
		if ($_SESSION['uid']) {
			$result = select_query("tblorders", "count(*)", "promocode='" . db_escape_string($promotioncode) . "' AND userid=" . (int)$_SESSION['uid'] . " AND status IN ('Pending','Active')");
			$orderCount = mysql_fetch_array($result);

			if (0 < $orderCount[0]) {
				$promoerrormessage = $_LANG['promoonceperclient'];
				return $promoerrormessage;
			}
		}
	}

	$_SESSION['cart']['promo'] = $promotioncode;
}

function CalcPromoDiscount($pid, $cycle, $fpamount, $recamount, $setupfee = 0) {
	global $promo_data;
	global $currency;

	$id = $promo_data['id'];
	$promotioncode = $promo_data['code'];

	if (!$id) {
		return false;
	}


	if ($_SESSION['adminid'] && !defined("CLIENTAREA")) {
	}
	else {
		$newsignups = $promo_data['newsignups'];

		if ($newsignups && $_SESSION['uid']) {
			$result = select_query("tblorders", "COUNT(*)", array("userid" => $_SESSION['uid']));
			$data = mysql_fetch_array($result);
			$previousorders = $data[0];

			if (2 <= $previousorders) {
				return false;
			}
		}

		$existingclient = $promo_data['existingclient'];
		$onceperclient = $promo_data['onceperclient'];

		if ($existingclient) {
			$result = select_query("tblorders", "count(*)", array("status" => "Active", "userid" => $_SESSION['uid']));
			$orderCount = mysql_fetch_array($result);

			if ($orderCount[0] < 1) {
				return false;
			}
		}


		if ($onceperclient) {
			$result = select_query("tblorders", "count(*)", "promocode='" . db_escape_string($promotioncode) . "' AND userid=" . (int)$_SESSION['uid'] . " AND status IN ('Pending','Active')");
			$orderCount = mysql_fetch_array($result);

			if (0 < $orderCount[0]) {
				return false;
			}
		}

		$applyonce = $promo_data['applyonce'];
		$promoapplied = $promo_data['promoapplied'];

		if ($applyonce && $promoapplied) {
			return false;
		}

		$appliesto = explode(",", $promo_data['appliesto']);

		if (!in_array($pid, $appliesto)) {
			return false;
		}

		$expiredate = $promo_data['expirationdate'];

		if ($expiredate != "0000-00-00") {
			$year = substr($expiredate, 0, 4);
			$month = substr($expiredate, 5, 2);
			$day = substr($expiredate, 8, 2);
			$validuntil = $year . $month . $day;
			$dayofmonth = date("d");
			$monthnum = date("m");
			$yearnum = date("Y");
			$todaysdate = $yearnum . $monthnum . $dayofmonth;

			if ($validuntil < $todaysdate) {
				return false;
			}
		}

		$cycles = $promo_data['cycles'];

		if ($cycles) {
			$cycles = explode(",", $cycles);

			if (!in_array($cycle, $cycles)) {
				return false;
			}
		}

		$maxuses = $promo_data['maxuses'];

		if ($maxuses) {
			$uses = $promo_data['uses'];

			if ($maxuses <= $uses) {
				return false;
			}
		}

		$requires = $promo_data['requires'];
		$requiresexisting = $promo_data['requiresexisting'];

		if ($requires) {
			$requires = explode(",", $requires);
			$hasrequired = false;

			if (is_array($_SESSION['cart']['products'])) {
				foreach ($_SESSION['cart']['products'] as $values) {

					if (in_array($values['pid'], $requires)) {
						$hasrequired = true;
					}


					if (is_array($values['addons'])) {
						foreach ($values['addons'] as $addonid) {

							if (in_array("A" . $addonid, $requires)) {
								$hasrequired = true;
								continue;
							}
						}

						continue;
					}
				}
			}


			if (is_array($_SESSION['cart']['addons'])) {
				foreach ($_SESSION['cart']['addons'] as $values) {

					if (in_array("A" . $values['id'], $requires)) {
						$hasrequired = true;
						continue;
					}
				}
			}


			if (is_array($_SESSION['cart']['domains'])) {
				foreach ($_SESSION['cart']['domains'] as $values) {
					$domainparts = explode(".", $values['domain'], 2);
					$tld = $domainparts[1];

					if (in_array("D." . $tld, $requires)) {
						$hasrequired = true;
						continue;
					}
				}
			}


			if (!$hasrequired && $requiresexisting) {
				$requiredproducts = $requiredaddons = array();
				$requireddomains = "";
				foreach ($requires as $v) {

					if (substr($v, 0, 1) == "A") {
						$requiredaddons[] = substr($v, 1);
						continue;
					}


					if (substr($v, 0, 1) == "D") {
						$requireddomains .= "domain LIKE '%" . substr($v, 1) . "' OR ";
						continue;
					}

					$requiredproducts[] = $v;
				}


				if (count($requiredproducts)) {
					$result = select_query("tblhosting", "COUNT(*)", "userid='" . (int)$_SESSION['uid'] . "' AND packageid IN (" . implode(",", $requiredproducts) . ") AND domainstatus='Active'");
					$data = mysql_fetch_array($result);

					if ($data[0]) {
						$hasrequired = true;
					}
				}


				if (count($requiredaddons)) {
					$result = select_query("tblhostingaddons", "COUNT(*)", "tblhosting.userid='" . (int)$_SESSION['uid'] . "' AND addonid IN (" . implode(",", $requiredaddons) . ") AND status='Active'", "", "", "", "tblhosting ON tblhosting.id=tblhostingaddons.hostingid");
					$data = mysql_fetch_array($result);

					if ($data[0]) {
						$hasrequired = true;
					}
				}


				if ($requireddomains) {
					$result = select_query("tbldomains", "COUNT(*)", "userid='" . (int)$_SESSION['uid'] . "' AND status='Active' AND (" . substr($requireddomains, 0, 0 - 4) . ")");
					$data = mysql_fetch_array($result);

					if ($data[0]) {
						$hasrequired = true;
					}
				}
			}


			if (!$hasrequired) {
				return false;
			}
		}
	}

	$type = $promo_data['type'];
	$value = $promo_data['value'];
	$onetimediscount = 0;

	if ($type == "Percentage") {
		$onetimediscount = $fpamount * ($value / 100);
	}
	else {
		if ($type == "Fixed Amount") {
			if ($currency['id'] != 1) {
				$promo_data['value'] = $value = convertCurrency($value, 1, $currency['id']);
			}


			if ($fpamount < $value) {
				$onetimediscount = $fpamount;
			}
			else {
				$onetimediscount = $value;
			}
		}
		else {
			if ($type == "Price Override") {
				if ($currency['id'] != 1) {
					$promo_data['value'] = convertCurrency($promo_data['value'], 1, $currency['id']);
				}


				if (!isset($promo_data['priceoverride'])) {
					$promo_data['priceoverride'] = $promo_data['value'];
				}

				$onetimediscount = $fpamount - $promo_data['priceoverride'];
			}
			else {
				if ($type == "Free Setup") {
					$onetimediscount = $setupfee;
					$promo_data['value'] += $setupfee;
				}
			}
		}
	}

	$recurringdiscount = 0;
	$recurring = $promo_data['recurring'];

	if ($recurring) {
		if ($type == "Percentage") {
			$recurringdiscount = $recamount * ($value / 100);
		}
		else {
			if ($type == "Fixed Amount") {
				if ($recamount < $value) {
					$recurringdiscount = $recamount;
				}
				else {
					$recurringdiscount = $value;
				}
			}
			else {
				if ($type == "Price Override") {
					$recurringdiscount = $recamount - $promo_data['priceoverride'];
				}
			}
		}
	}

	$onetimediscount = round($onetimediscount, 2);
	$recurringdiscount = round($recurringdiscount, 2);
	$promo_data['promoapplied'] = true;
	return array("onetimediscount" => $onetimediscount, "recurringdiscount" => $recurringdiscount);
}

function acceptOrder($orderid, $vars = array()) {
	if (!$orderid) {
		return false;
	}


	if (!is_array($vars)) {
		$vars = array();
	}

	$errors = array();
	run_hook("AcceptOrder", array("orderid" => $orderid));
	$result = select_query("tblhosting", "", array("orderid" => $orderid, "domainstatus" => "Pending"));

	while ($data = mysql_fetch_array($result)) {
		$productid = $data['id'];
		$updateqry = array();

		if ($vars['products'][$productid]['server']) {
			$updateqry['server'] = $vars['products'][$productid]['server'];
		}


		if ($vars['products'][$productid]['username']) {
			$updateqry['username'] = $vars['products'][$productid]['username'];
		}


		if ($vars['products'][$productid]['password']) {
			$updateqry['password'] = encrypt($vars['products'][$productid]['password']);
		}


		if ($vars['api']['serverid']) {
			$updateqry['server'] = $vars['api']['serverid'];
		}


		if ($vars['api']['username']) {
			$updateqry['username'] = $vars['api']['username'];
		}


		if ($vars['api']['password']) {
			$updateqry['password'] = $vars['api']['password'];
		}


		if (count($updateqry)) {
			update_query("tblhosting", $updateqry, array("id" => $productid));
		}

		$result2 = select_query("tblhosting", "tblproducts.servertype,tblproducts.autosetup", array("tblhosting.id" => $productid), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");
		$data = mysql_fetch_array($result2);
		$module = $data['servertype'];
		$autosetup = $data['autosetup'];
		$autosetup = ($autosetup ? true : false);
		$sendwelcome = ($autosetup ? true : false);

		if (count($vars)) {
			$autosetup = $vars['products'][$productid]['runcreate'];
			$sendwelcome = $vars['products'][$productid]['sendwelcome'];

			if (isset($vars['api']['autosetup'])) {
				$autosetup = $vars['api']['autosetup'];
			}


			if (isset($vars['api']['sendemail'])) {
				$sendwelcome = $vars['api']['sendemail'];
			}
		}


		if ($autosetup) {
			if ($module) {
				logActivity("Running Module Create on Accept Pending Order");

				if (!isValidforPath($module)) {
					exit("Invalid Server Module Name");
				}

				require_once ROOTDIR . ("/modules/servers/" . $module . "/" . $module . ".php");
				$moduleresult = ServerCreateAccount($productid);

				if ($moduleresult == "success") {
					if ($sendwelcome) {
						sendMessage("defaultnewacc", $productid);
					}
				}

				$errors[] = $moduleresult;
			}
		}

		update_query("tblhosting", array("domainstatus" => "Active"), array("id" => $productid));

		if ($sendwelcome) {
			sendMessage("defaultnewacc", $productid);
		}
	}

	$result = select_query("tblhostingaddons", "", array("orderid" => $orderid, "status" => "Pending"));

	while ($data = mysql_fetch_array($result)) {
		$aid = $data['id'];
		$hostingid = $data['hostingid'];
		$addonid = $data['addonid'];

		if ($addonid) {
			$result2 = select_query("tbladdons", "", array("id" => $addonid));
			$data = mysql_fetch_array($result2);
			$welcomeemail = $data['welcomeemail'];
			$sendwelcome = ($welcomeemail ? true : false);

			if (count($vars)) {
				$sendwelcome = $vars['addons'][$aid]['sendwelcome'];
			}


			if (isset($vars['api']['sendemail'])) {
				$sendwelcome = $vars['api']['sendemail'];
			}


			if ($welcomeemail && $sendwelcome) {
				$result3 = select_query("tblemailtemplates", "name", array("id" => $welcomeemail));
				$data = mysql_fetch_array($result3);
				$welcomeemailname = $data['name'];
				sendMessage($welcomeemailname, $hostingid);
			}


			if (!$userid) {
				$result3 = select_query("tblorders", "userid", array("id" => $orderid));
				$data = mysql_fetch_array($result3);
				$userid = $data['userid'];
			}

			run_hook("AddonActivation", array("id" => $aid, "userid" => $userid, "serviceid" => $hostingid, "addonid" => $addonid));
		}
	}

	update_query("tblhostingaddons", array("status" => "Active"), array("orderid" => $orderid, "status" => "Pending"));
	$result = select_query("tbldomains", "", array("orderid" => $orderid, "status" => "Pending"));

	while ($data = mysql_fetch_array($result)) {
		$domainid = $data['id'];
		$regtype = $data['type'];
		$domain = $data['domain'];
		$registrar = $data['registrar'];
		$emailmessage = ($regtype == "Transfer" ? "Domain Transfer Initiated" : "Domain Registration Confirmation");

		if ($vars['domains'][$domainid]['registrar']) {
			$registrar = $vars['domains'][$domainid]['registrar'];
		}


		if ($vars['api']['registrar']) {
			$registrar = $vars['api']['registrar'];
		}


		if ($registrar) {
			update_query("tbldomains", array("registrar" => $registrar), array("id" => $domainid));
		}


		if ($vars['domains'][$domainid]['sendregistrar']) {
			$sendregistrar = "on";
		}


		if ($vars['domains'][$domainid]['sendemail']) {
			$sendemail = "on";
		}


		if (isset($vars['api']['sendregistrar'])) {
			$sendregistrar = $vars['api']['sendregistrar'];
		}


		if (isset($vars['api']['sendemail'])) {
			$sendemail = $vars['api']['sendemail'];
		}


		if ($sendregistrar && $registrar) {
			$params = array();
			$params['domainid'] = $domainid;
			$moduleresult = ($regtype == "Transfer" ? RegTransferDomain($params) : RegRegisterDomain($params));

			if (!$moduleresult['error']) {
				if ($sendemail) {
					sendMessage($emailmessage, $domainid);
				}
			}

			$errors[] = $moduleresult['error'];
		}

		update_query("tbldomains", array("status" => "Active"), array("id" => $domainid, "status" => "Pending"));

		if ($sendemail) {
			sendMessage($emailmessage, $domainid);
		}
	}


	if (is_array($vars['renewals'])) {
		foreach ($vars['renewals'] as $domainid => $options) {

			if ($vars['renewals'][$domainid]['sendregistrar']) {
				$sendregistrar = "on";
			}


			if ($vars['renewals'][$domainid]['sendemail']) {
				$sendemail = "on";
			}


			if ($sendregistrar) {
				$params = array();
				$params['domainid'] = $domainid;
				$moduleresult = RegRenewDomain($params);

				if ($moduleresult['error']) {
					$errors[] = $moduleresult['error'];
					continue;
				}


				if ($sendemail) {
					sendMessage("Domain Renewal Confirmation", $domainid);
					continue;
				}

				continue;
			}


			if ($sendemail) {
				sendMessage("Domain Renewal Confirmation", $domainid);
				continue;
			}
		}
	}

	$result = select_query("tblorders", "userid,promovalue", array("id" => $orderid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$promovalue = $data['promovalue'];

	if (substr($promovalue, 0, 2) == "DR") {
		if ($vars['domains'][$domainid]['sendregistrar']) {
			$sendregistrar = "on";
		}


		if (isset($vars['api']['autosetup'])) {
			$sendregistrar = $vars['api']['autosetup'];
		}


		if ($sendregistrar) {
			$params = array();
			$params['domainid'] = $domainid;
			$moduleresult = RegRenewDomain($params);

			if ($moduleresult['error']) {
				$errors[] = $moduleresult['error'];
			}
			else {
				if ($sendemail) {
					sendMessage("Domain Renewal Confirmation", $domainid);
				}
			}
		}
		else {
			if ($sendemail) {
				sendMessage("Domain Renewal Confirmation", $domainid);
			}
		}
	}

	update_query("tblupgrades", array("status" => "Completed"), array("orderid" => $orderid));

	if (!count($errors)) {
		update_query("tblorders", array("status" => "Active"), array("id" => $orderid));
		logActivity("Order Accepted - Order ID: " . $orderid, $userid);
	}

	return $errors;
}

function changeOrderStatus($orderid, $status) {
	if (!$orderid) {
		return false;
	}

	$orderid = (int)$orderid;

	if ($status == "Cancelled") {
		run_hook("CancelOrder", array("orderid" => $orderid));
	}
	else {
		if ($status == "Fraud") {
			run_hook("FraudOrder", array("orderid" => $orderid));
		}
		else {
			if ($status == "Pending") {
				run_hook("PendingOrder", array("orderid" => $orderid));
			}
		}
	}

	update_query("tblorders", array("status" => $status), array("id" => $orderid));

	if ($status == "Cancelled" || $status == "Fraud") {
		$result = select_query("tblhosting", "tblhosting.id,tblhosting.domainstatus,tblproducts.servertype,tblhosting.packageid,tblproducts.stockcontrol,tblproducts.qty", array("orderid" => $orderid), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");

		while ($data = mysql_fetch_array($result)) {
			$productid = $data['id'];
			$prodstatus = $data['domainstatus'];
			$module = $data['servertype'];
			$packageid = $data['packageid'];
			$stockcontrol = $data['stockcontrol'];
			$qty = $data['qty'];

			if ($module && ($prodstatus == "Active" || $prodstatus == "Suspended")) {
				logActivity("Running Module Terminate on Order Cancel");

				if (!isValidforPath($module)) {
					exit("Invalid Server Module Name");
				}

				require_once ROOTDIR . ("/modules/servers/" . $module . "/" . $module . ".php");
				$moduleresult = ServerTerminateAccount($productid);

				if ($moduleresult == "success") {
					update_query("tblhosting", array("domainstatus" => $status), array("id" => $productid));

					if ($stockcontrol == "on") {
						update_query("tblproducts", array("qty" => "+1"), array("id" => $packageid));
					}
				}
			}

			update_query("tblhosting", array("domainstatus" => $status), array("id" => $productid));

			if ($stockcontrol == "on") {
				update_query("tblproducts", array("qty" => "+1"), array("id" => $packageid));
			}
		}
	}
	else {
		update_query("tblhosting", array("domainstatus" => $status), array("orderid" => $orderid));
	}

	update_query("tblhostingaddons", array("status" => $status), array("orderid" => $orderid));

	if ($status == "Pending") {
		$result = select_query("tbldomains", "id,type", array("orderid" => $orderid));

		while ($data = mysql_fetch_assoc($result)) {
			if ($data['type'] == "Transfer") {
				$status = "Pending Transfer";
			}
			else {
				$status = "Pending";
			}

			update_query("tbldomains", array("status" => $status), array("id" => $data['id']));
		}
	}
	else {
		update_query("tbldomains", array("status" => $status), array("orderid" => $orderid));
	}

	$result = select_query("tblorders", "userid,invoiceid", array("id" => $orderid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$invoiceid = $data['invoiceid'];

	if ($status == "Pending") {
		update_query("tblinvoices", array("status" => "Unpaid"), array("id" => $invoiceid, "status" => "Cancelled"));
	}
	else {
		update_query("tblinvoices", array("status" => "Cancelled"), array("id" => $invoiceid, "status" => "Unpaid"));
		run_hook("InvoiceCancelled", array("invoiceid" => $invoiceid));
	}

	logActivity("Order Status set to " . $status . " - Order ID: " . $orderid, $userid);
}

function cancelRefundOrder($orderid) {
	$orderid = (int)$orderid;
	$result = select_query("tblorders", "invoiceid", array("id" => $orderid));
	$data = mysql_fetch_array($result);
	$invoiceid = $data['invoiceid'];

	if ($invoiceid) {
		$result = select_query("tblinvoices", "status", array("id" => $invoiceid));
		$data = mysql_fetch_array($result);
		$invoicestatus = $data['status'];

		if ($invoicestatus == "Paid") {
			$result = select_query("tblaccounts", "id", array("invoiceid" => $invoiceid));
			$data = mysql_fetch_array($result);
			$transid = $data['id'];
			$gatewayresult = refundInvoicePayment($transid, "", true);

			if ($gatewayresult == "manual") {
				return "manual";
			}


			if ($gatewayresult != "success") {
				return "refundfailed";
			}
		}
		else {
			if ($invoicestatus == "Refunded") {
				return "alreadyrefunded";
			}

			return "notpaid";
		}
	}

	return "noinvoice";
}

function deleteOrder($orderid) {
	if (!$orderid) {
		return false;
	}

	$orderid = (int)$orderid;
	run_hook("DeleteOrder", array("orderid" => $orderid));
	$result = select_query("tblorders", "userid,invoiceid", array("id" => $orderid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$invoiceid = $data['invoiceid'];
	delete_query("tblhostingconfigoptions", "relid IN (SELECT id FROM tblhosting WHERE orderid=" . $orderid . ")");
	delete_query("tblaffiliatesaccounts", "relid IN (SELECT id FROM tblhosting WHERE orderid=" . $orderid . ")");
	delete_query("tblhosting", array("orderid" => $orderid));
	delete_query("tblhostingaddons", array("orderid" => $orderid));
	delete_query("tbldomains", array("orderid" => $orderid));
	delete_query("tblupgrades", array("orderid" => $orderid));
	delete_query("tblorders", array("id" => $orderid));
	delete_query("tblinvoices", array("id" => $invoiceid));
	delete_query("tblinvoiceitems", array("invoiceid" => $invoiceid));
	logActivity("Deleted Order - Order ID: " . $orderid, $userid);
}

function getAddons($pid, $addons) {
	global $currency;
	global $_LANG;

	if (!$addons) {
		$addons = array();
	}

	$addonsarray = array();
	$result = select_query("tbladdons", "", array("showorder" => "on"), "weight` ASC,`name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$addon_id = $data['id'];
		$addon_packages = $data['packages'];
		$addon_name = $data['name'];
		$addon_description = $data['description'];
		$addon_recurring = $data['recurring'];
		$addon_setupfee = $data['setupfee'];
		$addon_billingcycle = $data['billingcycle'];
		$addon_free = $data['free'];
		$result2 = select_query("tblpricing", "", array("type" => "addon", "currency" => $currency['id'], "relid" => $addon_id));
		$data = mysql_fetch_array($result2);
		$addon_setupfee = $data['msetupfee'];
		$addon_recurring = $data['monthly'];
		$addon_packages = explode(",", $addon_packages);

		if (in_array($pid, $addon_packages)) {
			$addon_status = (in_array($addon_id, $addons) ? true : false);
			$addon_checkbox = ("<input type=\"checkbox\" name=\"addons[" . $addon_id . "]") . "\" id=\"a" . $addon_id . "\"";

			if (in_array($addon_id, $addons)) {
				$addon_checkbox .= " checked";
			}

			$addon_checkbox .= " />";

			if ($addon_billingcycle == "Free") {
				$addon_pricingdetails = $_LANG['orderfree'];
			}
			else {
				$addon_pricingdetails = formatCurrency($addon_recurring) . " ";
				$addon_billingcycle = str_replace(array(" ", "-"), "", strtolower($addon_billingcycle));
				$addon_pricingdetails .= $_LANG["orderpaymentterm" . $addon_billingcycle];

				if (0 < $addon_setupfee) {
					$addon_pricingdetails .= " + " . formatCurrency($addon_setupfee) . " " . $_LANG['ordersetupfee'];
				}
			}

			$addonsarray[] = array("id" => $addon_id, "checkbox" => $addon_checkbox, "name" => $addon_name, "description" => $addon_description, "pricing" => $addon_pricingdetails, "status" => $addon_status);
		}
	}

	return $addonsarray;
}

function getAvailableOrderPaymentGateways() {
	$disabledgateways = "";

	if ($_SESSION['cart']['products']) {
		foreach ($_SESSION['cart']['products'] as $values) {
			$result = select_query("tblproducts", "gid", array("id" => $values['pid']));
			$data = mysql_fetch_array($result);
			$gid = $data['gid'];
			$result = select_query("tblproductgroups", "disabledgateways", array("id" => $gid));
			$data = mysql_fetch_array($result);
			$disabledgateways .= $data['disabledgateways'];
		}
	}

	$disabledgateways = explode(",", $disabledgateways);

	if (!function_exists("showPaymentGatewaysList")) {
		require ROOTDIR . "/includes/gatewayfunctions.php";
	}

	$gatewayslist = showPaymentGatewaysList($disabledgateways);
	foreach ($gatewayslist as $module => $vals) {

		if ($vals['type'] == "CC" || $vals['type'] == "OfflineCC") {
			if (!isValidforPath($module)) {
				exit("Invalid Gateway Module Name");
			}

			$gatewaypath = ROOTDIR . "/modules/gateways/" . $module . ".php";

			if (file_exists($gatewaypath)) {
				if ((!function_exists($module . "_config") && !function_exists($module . "_link")) && !function_exists($module . "_capture")) {
					require_once $gatewaypath;
				}
			}


			if (function_exists($module . "_nolocalcc")) {
				$gatewayslist[$module]['type'] = "Invoices";
				continue;
			}

			continue;
		}
	}

	return $gatewayslist;
}

?>