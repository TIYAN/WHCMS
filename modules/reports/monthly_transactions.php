<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

$reportdata["title"] = "Monthly Transactions Report for ".$months[(int)$month]." ".$year;
$reportdata["description"] = "This report provides a summary of daily payments activity for a given month. The Amount Out figure includes both expenditure transactions and refunds.";

$reportdata["currencyselections"] = true;

$reportdata["tableheadings"] = array("Date","Amount In","Fees","Amount Out","Balance");

for ( $counter = 1; $counter <= 31; $counter += 1) {
	$counter = str_pad($counter, 2, "0", STR_PAD_LEFT);  
	$query = "SELECT SUM(amountin),SUM(fees),SUM(amountout) FROM tblaccounts INNER JOIN tblclients ON tblclients.id=tblaccounts.userid WHERE date LIKE '".db_make_safe_date("$year-$month-$counter")."%' AND tblclients.currency=".(int)$currencyid;
	$result = full_query($query);
	$data = mysql_fetch_array($result);
	$amountin = $data[0];
	$fees = $data[1];
	$amountout = $data[2];
    $query = "SELECT SUM(amountin),SUM(fees),SUM(amountout) FROM tblaccounts WHERE date LIKE '".db_make_safe_date("$year-$month-$counter")."%' AND userid='0' AND currency=".(int)$currencyid;
	$result = full_query($query);
	$data = mysql_fetch_array($result);
	$amountin += $data[0];
	$fees += $data[1];
	$amountout += $data[2];
	$dailybalance = $amountin-$fees-$amountout;
	$overallbalance += $dailybalance;
    $chartdata['rows'][] = array('c'=>array(array('v'=>$counter),array('v'=>$amountin,'f'=>formatCurrency($amountin)),array('v'=>$fees,'f'=>formatCurrency($fees)),array('v'=>$amountout,'f'=>formatCurrency($amountout))));
	$amountin = formatCurrency($amountin);
	$fees = formatCurrency($fees);
	$amountout = formatCurrency($amountout);
	$dailybalance = formatCurrency($dailybalance);
	$reportdata["tablevalues"][] = array(fromMySQLDate("$year-$month-$counter"),$amountin,$fees,$amountout,$dailybalance);
}

$overallbalance = formatCurrency($overallbalance);

$reportdata["footertext"] = "<p align=\"center\"><strong>Balance: ".$overallbalance."</strong></p>";

$reportdata["monthspagination"] = true;

$chartdata['cols'][] = array('label'=>'Days Range','type'=>'string');
$chartdata['cols'][] = array('label'=>'Amount In','type'=>'number');
$chartdata['cols'][] = array('label'=>'Fees','type'=>'number');
$chartdata['cols'][] = array('label'=>'Amount Out','type'=>'number');

$args['colors'] = '#80D044,#F9D88C,#CC0000';

$reportdata["headertext"] = $chart->drawChart('Area',$chartdata,$args,'450px');

?>