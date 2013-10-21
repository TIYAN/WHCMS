<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

$months = array('January','February','March','April','May','June','July','August','September','October','November','December');

if ($month=="") {
	$month=date("m");
	$year=date("Y");
}

$pmonth = str_pad($month, 2, "0", STR_PAD_LEFT);  

$reportdata["title"] = "Support Ticket Replies for ".$months[$month-1]." ".$year;
$reportdata["description"] = "This report shows a breakdown of support tickets dealt with per admin for a given month";

$reportdata["tableheadings"][] = "Admin";

for ( $day = 1; $day <= 31; $day += 1) {
	$reportdata["tableheadings"][] = $day;
}

$rowcount=0;

$reportvalues = array();

for ( $day = 1; $day <= 31; $day += 1) {
	
	$date = $year."-".$pmonth."-".str_pad($day,2,"0",STR_PAD_LEFT);
		
	$query = "SELECT `admin`,COUNT(tid) AS totalreplies,COUNT(DISTINCT tid) AS totaltickets FROM `tblticketreplies` WHERE date LIKE '".db_make_safe_date($date)."%' AND admin!='' GROUP BY `admin` ORDER BY `admin`";
	$result = full_query($query);
	while ($data = mysql_fetch_array($result)) {
		$adminname = $data[0];
		$totalreplies = $data[1];
		$totaltickets = $data[2];	
		$reportvalues[$adminname][$day] = array( "totalreplies" => $totalreplies, "totaltickets" => $totaltickets, );
	}
}

foreach ($reportvalues AS $adminname=>$values) {
	
	$reportdata["tablevalues"][$rowcount][] = "**$adminname";
	
	$rowcount++;
	$nextrow=$rowcount+1;
	
	$reportdata["tablevalues"][$rowcount][] = "Tickets";
	$reportdata["tablevalues"][$nextrow][] = "Replies";
	
	$i=1;
	
	foreach ($values AS $day=>$value) {
		
		while ($i < $day) {
			$reportdata["tablevalues"][$rowcount][] = "";
			$reportdata["tablevalues"][$nextrow][] = "";
			$i++;
		}
		
		if ($day==$i) {
			$reportdata["tablevalues"][$rowcount][] = $value["totaltickets"];
			$reportdata["tablevalues"][$nextrow][] = $value["totalreplies"];
		}
		$i++;

	}
	
	while ($day < 31) {
		$reportdata["tablevalues"][$rowcount][] = "";
		$reportdata["tablevalues"][$nextrow][] = "";
		$day++;
	}
	
	$rowcount=$nextrow+1;

}

$data["footertext"]="<table width=90% align=center><tr><td>";
if ($month=="1") {
	$data["footertext"].="<a href=\"$PHP_SELF?report=$report&month=12&year=".($year-1)."\"><< December ".($year-1)."</a>";
} else {
	$data["footertext"].="<a href=\"$PHP_SELF?report=$report&month=".($month-1)."&year=".$year."\"><< ".$months[($month-2)]." $year</a>";
}
$data["footertext"].="</td><td align=right>";
if ($month=="12") {
	$data["footertext"].="<a href=\"$PHP_SELF?report=$report&month=1&year=".($year+1)."\">January ".($year+1)." >></a>";
} else {
	$data["footertext"].="<a href=\"$PHP_SELF?report=$report&month=".($month+1)."&year=".$year."\">".$months[(($month+1)-1)]." $year >></a>";
}
$data["footertext"].="</td></tr></table>";

?>