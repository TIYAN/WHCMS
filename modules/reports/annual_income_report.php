<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

$months = array('January','February','March','April','May','June','July','August','September','October','November','December');

if (!$year) $year = date("Y");

$reportdata["title"] = "Annual Income Report for ".$year;
$reportdata["description"] = "This report shows the income received broken down by month converted to the base currency using rates at the time of the transaction";

$currency = getCurrency(0,1);

$reportdata["tableheadings"] = array("Month","Amount In","Fees","Amount Out","Balance");

for ( $counter = 1; $counter <= 12; $counter += 1) {
	$month = $months[$counter-1];
	$counter = str_pad($counter, 2, "0", STR_PAD_LEFT);
    $data = get_query_vals("tblaccounts","SUM(amountin/rate),SUM(fees/rate),SUM(amountout/rate)","date LIKE '".(int)$year."-$counter-%'");
	$amountin = $data[0];
	$fees = $data[1];
	$amountout = $data[2];
	$monthlybalance = $amountin-$fees-$amountout;
	$overallbalance += $monthlybalance;
    $prevyearbal = get_query_val("tblaccounts","SUM((amountin-fees-amountout)/rate)","date LIKE '".(int)($year-1)."-$counter-%'");
    $prevyearbal = round($prevyearbal,2);
    $chartdata['rows'][] = array('c'=>array(array('v'=>$month),array('v'=>$prevyearbal,'f'=>formatCurrency($prevyearbal)),array('v'=>$monthlybalance,'f'=>formatCurrency($monthlybalance))));
	$amountin = formatCurrency($amountin);
	$fees = formatCurrency($fees);
	$amountout = formatCurrency($amountout);
	$monthlybalance = formatCurrency($monthlybalance);
	$reportdata["tablevalues"][] = array($month.' '.$year,$amountin,$fees,$amountout,$monthlybalance);
}

$overallbalance = formatCurrency($overallbalance);

$reportdata["footertext"] = '<p align="center"><b>Balance: '.$overallbalance.'</b></p>';

$reportdata["yearspagination"] = true;

$chartdata['cols'][] = array('label'=>'Days Range','type'=>'string');
$chartdata['cols'][] = array('label'=>$year-1,'type'=>'number');
$chartdata['cols'][] = array('label'=>$year,'type'=>'number');

$args = array();
$args['colors'] = '#F9D88C,#3070CF';
$args['chartarea'] = '80,20,90%,350';

$reportdata["headertext"] = $chart->drawChart('Column',$chartdata,$args,'400px');

?>