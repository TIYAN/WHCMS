<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

$reportdata["title"] = "Clients by Country";
$reportdata["description"] = "This report shows the total number of active services per country, as well as total active unique clients per country in the table below.";

$reportdata["tableheadings"] = array("Country","Active Services","Active Clients");

require(ROOTDIR.'/includes/countries.php');

$clientstats = array();
$query = "SELECT country, COUNT(*) FROM tblclients WHERE status='Active' GROUP BY country ORDER BY country";
$result = full_query($query);
while ($data = mysql_fetch_array($result)) {
    $clientstats[$data[0]] = $data[1];
}

$query = "SELECT country, COUNT(*) FROM  tblhosting INNER JOIN tblclients ON tblclients.id=tblhosting.userid WHERE domainstatus='Active' GROUP BY country ORDER BY country";
$result = full_query($query);
while ($data = mysql_fetch_array($result)) {

    $countryname = $countries[$data[0]];
    if ($countryname) {

    $reportdata["tablevalues"][] = array($countryname,$data[1],$clientstats[$data[0]]);

    $chartdata['rows'][] = array('c'=>array(array('v'=>$data[0]),array('v'=>$data[1]),array('v'=>$clientstats[$data[0]])));

    unset($clientstats[$data[0]]);

    }

}

foreach ($clientstats AS $country=>$activeclient) {

    $countryname = $countries[$country];
    if ($countryname) {

    $reportdata["tablevalues"][] = array($countryname,'0',$activeclient);

    $chartdata['rows'][] = array('c'=>array(array('v'=>$country),array('v'=>0),array('v'=>$activeclient)));

    }

}

$chartdata['cols'][] = array('label'=>'Country','type'=>'string');
$chartdata['cols'][] = array('label'=>'Active Services','type'=>'number');

$args = array();
$args['legendpos'] = 'right';

$reportdata["headertext"] = $chart->drawChart('Geo',$chartdata,$args,'600px');

?>