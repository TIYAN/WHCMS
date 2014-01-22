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

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!$name) {
	$apiresults = array("result" => "error", "message" => "You must supply a name for the product");
	return false;
}


if (!$type) {
	$type = "other";
}


if ($stockcontrol || $qty) {
	$stockcontrol = "on";
}
else {
	$stockcontrol = "";
}


if (!$paytype) {
	$paytype = "free";
}


if ($hidden) {
	$hidden = "on";
}


if ($showdomainoptions) {
	$showdomainoptions = "on";
}

$tax = ($tax ? "1" : "0");
$pid = insert_query("tblproducts", array("type" => $type, "gid" => $gid, "name" => $name, "description" => $description, "hidden" => $hidden, "showdomainoptions" => $showdomainoptions, "welcomeemail" => $welcomeemail, "stockcontrol" => $stockcontrol, "qty" => $qty, "proratabilling" => $proratabilling, "proratadate" => $proratadate, "proratachargenextmonth" => $proratachargenextmonth, "paytype" => $paytype, "subdomain" => $subdomain, "autosetup" => $autosetup, "servertype" => $module, "servergroup" => $servergroupid, "configoption1" => $configoption1, "configoption2" => $configoption2, "configoption3" => $configoption3, "configoption4" => $configoption4, "configoption5" => $configoption5, "configoption6" => $configoption6, "tax" => $tax, "order" => $order));
foreach ($pricing as $currency => $values) {
	insert_query("tblpricing", array("type" => "product", "currency" => $currency, "relid" => $pid, "msetupfee" => $values['msetupfee'], "qsetupfee" => $values['qsetupfee'], "ssetupfee" => $values['ssetupfee'], "asetupfee" => $values['asetupfee'], "bsetupfee" => $values['bsetupfee'], "tsetupfee" => $values['tsetupfee'], "monthly" => $values['monthly'], "quarterly" => $values['quarterly'], "semiannually" => $values['semiannually'], "annually" => $values['annually'], "biennially" => $values['biennially'], "triennially" => $values['triennially']));
}

$apiresults = array("result" => "success", "pid" => $pid);
?>