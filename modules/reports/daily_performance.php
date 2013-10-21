<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

$reportdata["title"] = "Daily Performance for ".$months[(int)$month]." ".$year;
$reportdata["description"] = "This report shows a daily activity summary for a given month.";

$reportdata["tableheadings"] = array("Date","Completed Orders","New Invoices","Paid Invoices","Opened Tickets","Ticket Replies","Cancellation Requests");

for ( $day = 1; $day <= 31; $day += 1) {

    $date = date("Y-m-d",mktime(0,0,0,$month,$day,$year));
    $daytext = date("l",mktime(0,0,0,$month,$day,$year));

	$query = "SELECT COUNT(*) FROM tblorders WHERE `date` LIKE '".db_make_safe_date($date)."%' AND status='Active'";
	$result = full_query($query);
	$data = mysql_fetch_array($result);
	$neworders = $data[0];

	$query = "SELECT COUNT(*) FROM tblinvoices WHERE `date`='".db_make_safe_date($date)."'";
	$result = full_query($query);
	$data = mysql_fetch_array($result);
	$newinvoices = $data[0];

	$query = "SELECT COUNT(*) FROM tblinvoices WHERE `datepaid` LIKE '".db_make_safe_date($date)."%'";
	$result = full_query($query);
	$data = mysql_fetch_array($result);
	$paidinvoices = $data[0];

	$query = "SELECT COUNT(*) FROM tbltickets WHERE `date` LIKE '".db_make_safe_date($date)."%'";
	$result = full_query($query);
	$data = mysql_fetch_array($result);
	$newtickets = $data[0];

	$query = "SELECT COUNT(*) FROM tblticketreplies WHERE `date` LIKE '".db_make_safe_date($date)."%' AND admin!=''";
	$result = full_query($query);
	$data = mysql_fetch_array($result);
	$ticketreplies = $data[0];

	$query = "SELECT COUNT(*) FROM tblcancelrequests WHERE `date` LIKE '".db_make_safe_date($date)."%'";
	$result = full_query($query);
	$data = mysql_fetch_array($result);
	$cancellations = $data[0];

	$reportdata["tablevalues"][] = array($daytext.' '.fromMySQLDate($date),$neworders,$newinvoices,$paidinvoices,$newtickets,$ticketreplies,$cancellations);

    $chartdata['rows'][] = array('c'=>array(array('v'=>fromMySQLDate($date)),array('v'=>(int)$neworders),array('v'=>(int)$newinvoices),array('v'=>(int)$paidinvoices),array('v'=>(int)$newtickets),array('v'=>(int)$ticketreplies),array('v'=>(int)$cancellations)));

}

$chartdata['cols'][] = array('label'=>'Day','type'=>'string');
$chartdata['cols'][] = array('label'=>'Completed Orders','type'=>'number');
$chartdata['cols'][] = array('label'=>'New Invoices','type'=>'number');
$chartdata['cols'][] = array('label'=>'Paid Invoices','type'=>'number');
$chartdata['cols'][] = array('label'=>'Opened Tickets','type'=>'number');
$chartdata['cols'][] = array('label'=>'Ticket Replies','type'=>'number');
$chartdata['cols'][] = array('label'=>'Cancellation Requests','type'=>'number');

$args = array();
$args['legendpos'] = 'right';

$reportdata["headertext"] = $chart->drawChart('Area',$chartdata,$args,'400px');

$reportdata["monthspagination"] = true;

?>