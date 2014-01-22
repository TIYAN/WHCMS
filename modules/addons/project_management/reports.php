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
 * */

if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

echo $headeroutput;
echo "<h2>Reports</h2>";

if (!project_management_checkperm( "View Reports" )) {
	echo "<p>You do not have permission to view reports.</p>";
	return false;
}

$text_reports = array();
$dh = opendir( "../modules/reports/" );

while (false !== ($file = readdir( $dh ))) {
	if ( $file != "index.php" && is_file( "../modules/reports/" . $file ) ) {
		$file = str_replace( ".php", "", $file );

		if ( substr( $file, 0, 5 ) != "graph" && substr( $file, 0, 8 ) == "project_" ) {
			$nicename = str_replace( "_", " ", $file );
			$nicename = titleCase( $nicename );
			$text_reports[$file] = $nicename;
		}
	}
}

closedir( $dh );
asort( $text_reports );
echo "
<div class=\"reports\">
";
foreach ($text_reports as $k => $v) {
	echo "<a href=\"reports.php?report=" . $k . "\">" . $v . "</a>";
}

echo "</div>

<br />

<h2>Time Analysis by Staff Member</h2>

<p align=\"center\"><img src=\"reports.php?displaygraph=graph_project_tasks\"></p>
";
?>