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

function getCartConfigOptions($pid, $values, $cycle, $accountid = "", $orderform = "") {
	global $CONFIG;
	global $_LANG;
	global $currency;

	if ($cycle == "onetime") {
		$cycle = "monthly";
	}

	$configoptions = array();
	$cycle = strtolower(str_replace("-", "", $cycle));

	if ($cycle == "one time") {
		$cycle = "monthly";
	}

	$showhidden = (($_SESSION['adminid'] && defined("ADMINAREA")) ? true : false);

	if (!function_exists("getBillingCycleMonths")) {
		require ROOTDIR . "/includes/invoicefunctions.php";
	}

	$cyclemonths = getBillingCycleMonths($cycle);

	if ($accountid) {
		$values = array();
		$result = select_query("tblhostingconfigoptions", "", array("relid" => $accountid));

		while ($data = mysql_fetch_array($result)) {
			$configid = $data['configid'];
			$result2 = select_query("tblproductconfigoptions", "", array("id" => $configid));
			$data2 = mysql_fetch_array($result2);
			$optiontype = $data2['optiontype'];

			if ($optiontype == 3 || $optiontype == 4) {
				$configoptionvalue = $data['qty'];
			}
			else {
				$configoptionvalue = $data['optionid'];
			}

			$values[$configid] = $configoptionvalue;
		}
	}

	$where = array("pid" => $pid);

	if (!$showhidden) {
		$where['hidden'] = 0;
	}

	$result2 = select_query("tblproductconfigoptions", "", $where, "order` ASC,`id", "ASC", "", "tblproductconfiglinks ON tblproductconfiglinks.gid=tblproductconfigoptions.gid");

	while ($data2 = mysql_fetch_array($result2)) {
		$optionid = $data2['id'];
		$optionname = $data2['optionname'];
		$optiontype = $data2['optiontype'];
		$optionhidden = $data2['hidden'];
		$qtyminimum = $data2['qtyminimum'];
		$qtymaximum = $data2['qtymaximum'];

		if (strpos($optionname, "|")) {
			$optionname = explode("|", $optionname);
			$optionname = trim($optionname[1]);
		}

		$options = array();
		$selectedqty = 0;
		$selvalue = $values[$optionid];

		if ($optiontype == "3") {
			$result3 = select_query("tblproductconfigoptionssub", "", array("configid" => $optionid));
			$data3 = mysql_fetch_array($result3);
			$opid = $data3['id'];
			$ophidden = $data3['hidden'];
			$opname = $data3['optionname'];

			if (strpos($opname, "|")) {
				$opname = explode("|", $opname);
				$opname = trim($opname[1]);
			}

			$opnameonly = $opname;
			$result4 = select_query("tblpricing", "", array("type" => "configoptions", "currency" => $currency['id'], "relid" => $opid));
			$data = mysql_fetch_array($result4);
			$setup = $data[substr($cycle, 0, 1) . "setupfee"];
			$price = $fullprice = $data[$cycle];

			if ($orderform && $CONFIG['ProductMonthlyPricingBreakdown']) {
				$price = $price / $cyclemonths;
			}


			if (0 < $price) {
				$opname .= " " . formatCurrency($price);
			}

			$setupvalue = 0 < $setup ? " + " . formatCurrency($setup) . " " . $_LANG['ordersetupfee'] : "";
			$options[] = array("id" => $opid, "hidden" => $ophidden, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price);

			if (!$selvalue) {
				$selvalue = 0;
			}

			$selectedoption = $selsetup = $selrecurring = "";
			$selectedqty = $selvalue;
			$selvalue = $opid;
			$selname = $_LANG['no'];

			if ($selectedqty) {
				$selname = $_LANG['yes'];
				$selectedoption = $opname;
				$selsetup = $setup;
				$selrecurring = $fullprice;
			}
		}
		else {
			if ($optiontype == "4") {
				$result3 = select_query("tblproductconfigoptionssub", "", array("configid" => $optionid));
				$data3 = mysql_fetch_array($result3);
				$opid = $data3['id'];
				$ophidden = $data3['hidden'];
				$opname = $data3['optionname'];

				if (strpos($opname, "|")) {
					$opname = explode("|", $opname);
					$opname = trim($opname[1]);
				}

				$opnameonly = $opname;
				$result4 = select_query("tblpricing", "", array("type" => "configoptions", "currency" => $currency['id'], "relid" => $opid));
				$data = mysql_fetch_array($result4);
				$setup = $data[substr($cycle, 0, 1) . "setupfee"];
				$price = $fullprice = $data[$cycle];

				if ($orderform && $CONFIG['ProductMonthlyPricingBreakdown']) {
					$price = $price / $cyclemonths;
				}


				if (0 < $price) {
					$opname .= " " . formatCurrency($price);
				}

				$setupvalue = 0 < $setup ? " + " . formatCurrency($setup) . " " . $_LANG['ordersetupfee'] : "";
				$options[] = array("id" => $opid, "hidden" => $ophidden, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price);

				if (!is_numeric($selvalue) || $selvalue < 0) {
					$selvalue = $qtyminimum;
				}

				$selectedqty = $selvalue;
				$selvalue = $opid;
				$selname = $selectedqty;
				$selectedoption = $opname;
				$selsetup = $setup * $selectedqty;
				$selrecurring = $fullprice * $selectedqty;
			}
			else {
				$result3 = select_query("tblproductconfigoptionssub", "", array("configid" => $optionid), "sortorder` ASC,`id", "ASC");

				while ($data3 = mysql_fetch_array($result3)) {
					$opid = $data3['id'];
					$opname = $data3['optionname'];
					$ophidden = $data3['hidden'];

					if (strpos($opname, "|")) {
						$opname = explode("|", $opname);
						$opname = trim($opname[1]);
					}

					$opnameonly = $opname;
					$result4 = select_query("tblpricing", "", array("type" => "configoptions", "currency" => $currency['id'], "relid" => $opid));
					$data = mysql_fetch_array($result4);
					$setup = $data[substr($cycle, 0, 1) . "setupfee"];
					$price = $fullprice = $data[$cycle];

					if ($orderform && $CONFIG['ProductMonthlyPricingBreakdown']) {
						$price = $price / $cyclemonths;
					}


					if (0 < $price) {
						$opname .= " " . formatCurrency($price);
					}

					$setupvalue = 0 < $setup ? " + " . formatCurrency($setup) . " " . $_LANG['ordersetupfee'] : "";

					if ($showhidden || !$ophidden) {
						$options[] = array("id" => $opid, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price, "hidden" => $ophidden);
					}


					if ($opid == $selvalue || !$selvalue) {
						$selname = $opnameonly;
						$selectedoption = $opname;
						$selsetup = $setup;
						$selrecurring = $fullprice;
						$selvalue = $opid;
					}
				}
			}
		}

		$configoptions[] = array("id" => $optionid, "hidden" => $optionhidden, "optionname" => $optionname, "optiontype" => $optiontype, "selectedvalue" => $selvalue, "selectedqty" => $selectedqty, "selectedname" => $selname, "selectedoption" => $selectedoption, "selectedsetup" => $selsetup, "selectedrecurring" => $selrecurring, "qtyminimum" => $qtyminimum, "qtymaximum" => $qtymaximum, "options" => $options);
	}

	return $configoptions;
}

?>