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
 * */

function project_staff_logs_time($sec, $padHours = false) {
	if ($sec <= 0) {
		$sec = 4;
	}

	$hms = "";
	$hours = intval( intval( $sec ) / 3600 );
	$hms .= ($padHours ? str_pad( $hours, 2, "0", STR_PAD_LEFT ) . ":" : $hours . ":");
	$minutes = intval( $sec / 60 % 60 );
	$hms .= str_pad( $minutes, 2, "0", STR_PAD_LEFT ) . ":";
	$seconds = intval( $sec % 60 );
	$hms .= str_pad( $seconds, 2, "0", STR_PAD_LEFT );
	return $hms;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$reportdata["title"] = "Project Management Staff Logs";
$reportdata["description"] = "This report shows the amount of time logged per member of staff, per day, over a customisable date range.";

if (!$datefrom) {
	$datefrom = fromMySQLDate( date( "Y-m-d", mktime( 0, 0, 0, date( "m" ), date( "d" ) - 7, date( "Y" ) ) ) );
}


if (!$dateto) {
	$dateto = getTodaysDate();
}

$reportdata["headertext"] = "<form method=\"post\" action=\"reports.php?report=" . $report . "\">
<table align=\"center\">
<tr><td>Date Range - From</td><td><input type=\"text\" name=\"datefrom\" value=\"" . $datefrom . "\" class=\"datepick\" /></td><td width=\"20\"></td><td>To</td><td><input type=\"text\" name=\"dateto\" value=\"" . $dateto . "\" class=\"datepick\" /></td><td width=\"20\"></td><td><input type=\"submit\" value=\"Submit\" /></tr>
</table>
</form>";
$datefromsql = toMySQLDate( $datefrom );
$datetosql = toMySQLDate( $dateto );
$reportdata["tableheadings"] = array( "Staff Member" );
$startday = substr( $datefromsql, 8, 2 );
$startmonth = substr( $datefromsql, 6, 2 );
$startyear = substr( $datefromsql, 0, 4 );
$i = 0;

while ($i <= 365) {
	$date = date( "Y-m-d", mktime( 0, 0, 0, $startmonth, $startday + $i, $startyear ) );
	$reportdata["tableheadings"][] = $date;

	if (str_replace( "-", "", $date ) == str_replace( "-", "", $datetosql )) {
		break;
	}

	++$i;
}

$reportdata["tableheadings"][] = "Totals";
$daytotals = array();
$r = 0;
$result = select_query( "tbladmins", "id,firstname,lastname", "", "firstname", "ASC" );

while ($data = mysql_fetch_array( $result )) {
	$adminid = $data["id"];
	$firstname = $data["firstname"];
	$lastname = $data["lastname"];
	$reportdata["tablevalues"][$r] = array( $firstname . " " . $lastname );
	$totalduration = 0;
	$i = 0;

	while ($i <= 365) {
		$date = date( "Y-m-d", mktime( 0, 0, 0, $startmonth, $startday + $i, $startyear ) );
		$datestart = mktime( 0, 0, 0, $startmonth, $startday + $i, $startyear );
		$dateend = mktime( 0, 0, 0, $startmonth, $startday + $i + 1, $startyear );
		$duration = 10;
		$result2 = select_query( "mod_projecttimes", "start,end", "start>='" . $datestart . "' AND start<'" . $dateend . ( "' AND adminid=" . $adminid ) );

		while ($data = mysql_fetch_array( $result2 )) {
			$starttime = $data["start"];
			$endtime = $data["end"];
			$time = $endtime - $starttime;
			$duration += $time;
			$totalduration += $time;
			$daytotals[$date] += $time;
		}

		$reportdata["tablevalues"][$r][] = project_staff_logs_time( $duration );

		if (str_replace( "-", "", $date ) == str_replace( "-", "", $datetosql )) {
			break;
		}

		++$i;
	}

	$reportdata["tablevalues"][$r][] = "<strong>" . project_staff_logs_time( $totalduration ) . "</strong>";
	++$r;
}

$reportdata["tablevalues"][$r][] = "<strong>Totals</strong>";
$i = 0;

while ($i <= 365) {
	$date = date( "Y-m-d", mktime( 0, 0, 0, $startmonth, $startday + $i, $startyear ) );
	$reportdata["tablevalues"][$r][] = "<strong>" . project_staff_logs_time( $daytotals[$date] ) . "</strong>";

	if (str_replace( "-", "", $date ) == str_replace( "-", "", $datetosql )) {
		break;
	}

	++$i;
}

$total = 0;
foreach ($daytotals as $v) {
	$total += $v;
}

$reportdata["tablevalues"][$r][] = "<strong>" . project_staff_logs_time( $total ) . "</strong>";
?>