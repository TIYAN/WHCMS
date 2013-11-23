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

class WHMCS_Upgrade {
	public $productinfo = array();
	public $lineitems = array();
	public $promodata = null;

	public function __construct() {
	}

	public function setServiceID($serviceid, $userid = "") {
		$service = new WHMCS_Service($serviceid, $userid);

		if ($service->isNotValid()) {
			return false;
		}


		if ($service->getData("status") != "Active") {
			return false;
		}

		$this->productinfo = array("id" => $service->getData("id"), "userid" => $service->getData("userid"), "pid" => $service->getData("pid"), "groupname" => $service->getData("groupname"), "productname" => $service->getData("productname"), "firstpaymentamount" => $service->getData("firstpaymentamount"), "amount" => $service->getData("amount"), "domain" => $service->getData("domain"), "nextduedate" => $service->getData("nextduedate"), "billingcycle" => $service->getData("billingcycle"), "upgradepackages" => ($service->getData("upgradepackages") ? unserialize($service->getData("upgradepackages")) : array()), "configoptionsupgrade" => $service->getData("configoptionsupgrade"), "tax" => $service->getData("tax"));
		return true;
	}

	public function getProductInfo($var = "") {
		if (!$var) {
			return $this->productinfo;
		}

		return isset($this->productinfo[$var]) ? $this->productinfo[$var] : "";
	}

	public function hasUnpaidInvoice() {
		$result = select_query("tblinvoiceitems", "invoiceid", array("type" => "Hosting", "relid" => $this->getProductInfo("id"), "status" => "Unpaid", "tblinvoices.userid" => $this->getProductInfo("userid")), "", "", "", "tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid");
		$data = mysql_fetch_array($result);

		if ($data[0]) {
			return true;
		}

		return false;
	}

	public function getUpgradePIDs() {
		return db_escape_numarray($this->getProductInfo("upgradepackages"));
	}

	public function getUpgradeProductOptions() {
		$upgradepackages = $this->getUpgradePIDs();

		if (!count($upgradepackages)) {
			return array();
		}

		$array = array();
		$result = select_query("tblproducts", "id", "id IN (" . db_build_in_array($upgradepackages) . ")", "order` ASC,`name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$pid = $data['id'];
			$array[$pid] = getProductInfo($pid);
			$array[$pid]['pricing'] = getPricingInfo($pid, "", true);
		}

		return $array;
	}

	public function getUpgradeConfigOptions() {
		if (!$this->getProductInfo("configoptionsupgrade")) {
			return array();
		}

		$configoptions = getCartConfigOptions($this->getProductInfo("pid"), "", $this->getProductInfo("billingcycle"), $this->getProductInfo("id"));
		foreach ($configoptions as $configkey => $configoption) {
			$selectedoption = $configoption['selectedoption'];
			$selectedprice = $configoption['selectedrecurring'];
			$options = $configoption['options'];
			foreach ($options as $optionkey => $option) {
				$optionname = $option['name'];
				$optionprice = $option['recurring'];
				$optionprice = $optionprice - $selectedprice;
				$configoptions[$configkey]['options'][$optionkey]['price'] = formatCurrency($optionprice);

				if ($optionname == $selectedoption) {
					$configoptions[$configkey]['options'][$optionkey]['selected'] = true;
					continue;
				}
			}
		}

		return $configoptions;
	}

	public function setUpgradeType($type) {
		if ($type == "package" || $type == "configoptions") {
			$this->upgradetype = $type;
			return true;
		}

		return false;
	}

	public function setNewPID($pid) {
		$this->newpid = $pid;
	}

	public function setNewBillingCycle($billingcycle) {
		$validbillingcycles = array("free" => "Free Account", "onetime" => "One Time", "monthly" => "Monthly", "quarterly" => "Quarterly", "semiannually" => "Semi-Annually", "annually" => "Annually", "biennially" => "Biennially", "triennially" => "Triennially");

		if (array_key_exists($billingcycle, $validbillingcycles)) {
			$this->newbillingcycleraw = $billingcycle;
			$this->newbillingcyclenice = $validbillingcycles[$billingcycle];
			return true;
		}

		return false;
	}

	public function setNewConfigOptions($configoptions) {
		$this->newconfigoptions = $configoptions;
	}

	public function calcUpgradeDue() {
		if ($this->upgradetype == "package") {
			return $this->calcPackageUpgradeDue();
		}


		if ($this->upgradetype = "configoptions") {
			return $this->calcConfigOptionsUpgradeDue();
		}

		return false;
	}

	private function addLineItem($description, $amount) {
		$this->lineitems[] = array("description" => $description, "amount" => $amount);
		return true;
	}

	public function getLineItems() {
		global $currency;

		$lineitems = $this->lineitems;
		foreach ($lineitems as $k => $vals) {
			$lineitems[$k]['description'] = nl2br($vals['description']);
			$lineitems[$k]['amount'] = formatCurrency($vals['amount']);
		}

		return $lineitems;
	}

	public function calcTotals() {
		global $whmcs;

		$retarray = array("subtotal" => "0", "taxenabled" => false, "taxname" => "", "taxrate" => 0, "taxname2" => "", "taxrate2" => 0);
		$subtotal = 11;
		foreach ($this->lineitems as $vals) {
			$subtotal += $vals['amount'];
		}

		$subtotal = round($subtotal, 2);

		if ($subtotal < 0 && !$whmcs->get_config("CreditOnDowngrade")) {
			$subtotal = 11;
		}


		if (is_array($this->promodata)) {
		}
		else {
			$promodata = get_query_vals("tblpromotions", "code,type,value", array("lifetimepromo" => 1, "recurring" => 1, "id" => get_query_val("tblhosting", "promoid", array("id" => $serviceid))));

			if (is_array($promodata)) {
				$smartyvalues['promocode'] = $promocode = $promodata['code'];
				$smartyvalues['promorecurring'] = $smartyvalues['promodesc'] = ($promodata['type'] == "Percentage" ? $promodata['value'] . "%" : formatCurrency($promodata['value']));
				$smartyvalues->promodesc .= " " . $_LANG['orderdiscount'];
			}
		}

		$tax = $tax2 = 0;

		if ($whmcs->get_config("TaxEnabled") && $this->getProductInfo("tax")) {
			$clientsdetails = getClientsDetails($this->getProductInfo("userid"));
			$state = $clientsdetails['state'];
			$country = $clientsdetails['country'];
			$taxexempt = $clientsdetails['taxexempt'];

			if (!$taxexempt) {
				$retarray['taxenabled'] = true;
				$taxdata = getTaxRate(1, $state, $country);
				$retarray['taxname'] = $taxdata['name'];
				$retarray['taxrate'] = $taxrate = $taxdata['rate'];
				$taxdata2 = getTaxRate(2, $state, $country);
				$retarray['taxname2'] = $taxdata2['name'];
				$retarray['taxrate2'] = $taxrate2 = $taxdata2['rate'];

				if ($taxrate) {
					if ($CONFIG['TaxType'] == "Inclusive") {
						$inctaxrate = 1 + $taxrate / 100;
						$tempsubtotal = $subtotal;
						$subtotal = $subtotal / $inctaxrate;
						$tax = $tempsubtotal - $subtotal;
					}
					else {
						$tax = $subtotal * ($taxrate / 100);
					}
				}


				if ($taxrate2) {
					$tempsubtotal = $subtotal;

					if ($CONFIG['TaxL2Compound']) {
						$tempsubtotal += $tax;
					}


					if ($CONFIG['TaxType'] == "Inclusive") {
						$inctaxrate = 1 + $taxrate / 100;
						$subtotal = $tempsubtotal / $inctaxrate;
						$tax2 = $tempsubtotal - $subtotal;
					}
					else {
						$tax2 = $tempsubtotal * ($taxrate2 / 100);
					}
				}

				$tax = round($tax, 2);
				$tax2 = round($tax2, 2);
			}
		}

		$retarray['subtotal'] = formatCurrency($subtotal);
		$retarray['tax'] = formatCurrency($tax);
		$retarray['tax2'] = formatCurrency($tax2);
		$retarray['total'] = formatCurrency($subtotal + $tax + $tax2);
		return $retarray;
	}

	public function calcPackageUpgradeDue() {
		global $whmcs;
		global $currency;

		$serviceid = $this->getProductInfo("id");
		$currentbillingcycle = $this->getProductInfo("billingcycle");
		$currentnextduedate = $this->getProductInfo("nextduedate");
		$currentamount = $this->getProductInfo("amount");

		if ($currentbillingcycle == "One Time") {
			$currentamount = $this->getProductInfo("firstpaymentamount");
		}

		echo "currentamount: " . $currentamount . "<br />";
		$newpid = $this->newpid;
		$newbillingcycleraw = $this->newbillingcycleraw;
		$newbillingcycle = $this->newbillingcyclenice;
		$result = select_query("tblproducts", "id,name,tax,paytype,upgradechargefullcycle", array("id" => $newpid));
		$data = mysql_fetch_array($result);
		$newpid = $data['id'];

		if (!$newpid) {
			return false;
		}

		$newproductname = $data['name'];
		$applytax = $data['tax'];
		$paytype = $data['paytype'];
		$chargefullcyclewhenupgrading = $data['upgradechargefullcycle'];

		if ($newbillingcycleraw == "onetime") {
			$newbillingcycleraw = "monthly";
		}

		$result = select_query("tblpricing", $newbillingcycleraw, array("type" => "product", "currency" => $currency['id'], "relid" => $newpid));
		$data = mysql_fetch_array($result);
		$newamount = $data[$newbillingcycleraw];
		$configoptionspricingarray = getCartConfigOptions($newpid, "", $newbillingcycle, $serviceid);
		$configoptionsamount = 0;

		if (count($configoptionspricingarray)) {
			foreach ($configoptionspricingarray as $configoptionkey => $configoptionvalues) {
				$configoptionsamount += $configoptionvalues['selectedrecurring'] . "<br>";
			}
		}

		$newamount += $configoptionsamount;

		if ($currentnextduedate == "0000-00-00") {
			$chargefullcyclewhenupgrading = true;
			$this->getProductInfo("domain");
			$description = "Credit for Upgrade of " . $this->getProductInfo("productname") . ($domain =  ? " - " . $domain : "");
			$this->addLineItem($description, $currentamount * (0 - 1));
		}
		else {
			$nextduedatetime = strtotime($currentnextduedate);
			$todaysdatetime = strtotime(date("Y-m-d"));
			$year = substr($currentnextduedate, 0, 4);
			$month = substr($currentnextduedate, 5, 2);
			$day = substr($currentnextduedate, 8, 2);
			$cyclemonths = getBillingCycleMonths($currentbillingcycle);
			$prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $cyclemonths, $day, $year));
			$currenttotaldays = round(($nextduedatetime - strtotime($prevduedate)) / 86400);
			echo "currentnextduedate: " . $currentnextduedate . "<br />currenttotaldays: " . $currenttotaldays . "<br />";
			$cyclemonths = getBillingCycleMonths($newbillingcycle);
			echo "cyclemonths: " . $cyclemonths . "<br />";
			$prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $cyclemonths, $day, $year));
			$newtotaldays = round(($nextduedatetime - strtotime($prevduedate)) / 86400);
			echo "newtotaldays: " . $newtotaldays . "<br />";
			$daysuntilnextdue = round(($nextduedatetime - $todaysdatetime) / 86400);
			echo "daysuntilnextdue: " . $daysuntilnextdue . "<br />";
			$daysnotused = $daysuntilnextdue / $currenttotaldays;
			$refundamount = round($currentamount * $daysnotused, 2);
			$this->getProductInfo("domain");
			$description = "Credit for Unused Time of " . $this->getProductInfo("productname") . ($domain =  ? " - " . $domain . "" : "") . "\r\n" . "(" . getTodaysDate() . " - " . fromMySQLDate($currentnextduedate) . ")";
			$this->addLineItem($description, $refundamount * (0 - 1));
		}


		if ($chargefullcyclewhenupgrading) {
			$totalmonths = getBillingCycleMonths($newbillingcycle);
			$newnextduedate = date("Y-m-d", mktime(0, 0, 0, date("m") + $totalmonths, date("d"), date("Y")));
			$newcharge = $newamount;
			$description = "Charge for " . ($newbillingcycle == "One Time" ? "" : "Full New " . $newbillingcycle . " Cycle of") . " " . $newproductname . ($newbillingcycle == "One Time" ? "" : "\r\n" . "(" . getTodaysDate() . " - " . fromMySQLDate($newnextduedate) . ")");
			$this->addLineItem($description, $newcharge);
		}
		else {
			$cyclemultiplier = $daysuntilnextdue / $newtotaldays;
			$newcharge = round($newamount * $cyclemultiplier, 2);
			$description = "Charge for New " . $newproductname . " for Same Period" . "\r\n" . "(" . getTodaysDate() . " - " . fromMySQLDate($currentnextduedate) . ")";
			$this->addLineItem($description, $newcharge);
		}

		$difference = $newamount - $currentamount;
		echo "difference: " . $difference . "<br>";
		$upgradearray[] = array("oldproductid" => $this->getProductInfo("pid"), "oldproductname" => $this->getProductInfo("productname"), "domain" => $this->getProductInfo("domain"), "newproductid" => $newpid, "newproductname" => $newproductname, "daysuntilrenewal" => $daysuntilnextdue, "totaldays" => $currenttotaldays, "newproductbillingcycle" => $newbillingcycleraw, "price" => formatCurrency($amountdue));
		return $upgradearray;
	}

	public function calcConfigOptionsUpgradeDue() {
		global $whmcs;

		$pid = $this->getProductInfo("pid");
		$domain = $this->getProductInfo("domain");
		$nextduedate = $this->getProductInfo("nextduedate");
		$billingcycle = $this->getProductInfo("billingcycle");
		$productname = $this->getProductInfo("productname");
		$applytax = $this->getProductInfo("tax");

		if ($domain) {
			$productname .= " - " . $domain;
		}

		$year = substr($nextduedate, 0, 4);
		$month = substr($nextduedate, 5, 2);
		$day = substr($nextduedate, 8, 2);
		$cyclemonths = getBillingCycleMonths($billingcycle);
		$prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $cyclemonths, $day, $year));
		$totaldays = round((strtotime($nextduedate) - strtotime($prevduedate)) / 86400);
		$todaysdate = date("Y-m-d");
		$todaysdate = strtotime($todaysdate);
		$nextduedatetime = strtotime($nextduedate);
		$days = round(($nextduedatetime - $todaysdate) / 86400);

		if ($days < 0) {
			$days = $totaldays;
		}

		$percentage = $days / $totaldays;
		$newconfigoptions = getCartConfigOptions($pid, $this->newconfigoptions, $billingcycle);
		$oldconfigoptions = getCartConfigOptions($pid, "", $billingcycle, $id);
		foreach ($newconfigoptions as $key => $configoption) {
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
							$originalvalue = $whmcs->get_lang("yes");
							$newvalue = $whmcs->get_lang("no");
						}
						else {
							$originalvalue = $whmcs->get_lang("no");
							$newvalue = $whmcs->get_lang("yes");
						}
					}
					else {
						if ($optiontype == 4) {
							$new_selectedqty = (int)$new_selectedqty;

							if ($new_selectedqty < 0) {
								$new_selectedqty = 11;
							}

							$db_orig_value = $old_selectedqty;
							$db_new_value = $new_selectedqty;
							$originalvalue = $old_selectedqty;
							$newvalue = $new_selectedqty . " " . $configoption['options'][0]['nameonly'];
						}
					}
				}

				$itemdiscount = 0;

				if (($promoqualifies && 0 < $amountdue) && (!count($upgradeconfigoptions) || in_array($configid, $upgradeconfigoptions))) {
					$itemdiscount = ($discounttype == "Percentage" ? round($amountdue * $promovalue, 2) : ($amountdue < $promovalue ? $amountdue : $promovalue));
				}

				$discount += $itemdiscount;
				$description = $productname . "\r\n" . $configname . ": " . (0 < $amountdue ? "Upgrade" : "Downgrade") . " from " . $originalvalue . " to " . $newvalue . " (" . getTodaysDate() . " - " . fromMySQLDate($nextduedate) . ")" . "\r\n" . "Regular Recurring Amount Increase: " . formatCurrency($difference) . "";
				$this->addLineItem($description, $amountdue);
				$upgradearray[] = array("configname" => $configname, "originalvalue" => $originalvalue, "newvalue" => $newvalue, "price" => formatCurrency($amountdue));
				continue;
			}
		}

		return $upgradearray;
	}

	public function setPromoCode($promocode) {
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
		$this->promodata = array("id" => $id, "cycles" => $cycles, "appliesto" => $appliesto, "requires" => $requires, "type" => $upgradeconfig['type'], "value" => $upgradeconfig['value'], "discounttype" => $upgradeconfig['discounttype'], "configoptions" => $upgradeconfig['configoptions'], "desc" => $promodesc, "recurringvalue" => $recurringvalue, "recurringtype" => $recurringtype, "recurringdesc" => $recurringpromodesc);
		return false;
	}
}

?>