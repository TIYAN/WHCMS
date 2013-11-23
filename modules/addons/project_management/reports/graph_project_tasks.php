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

if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

require_once ROOTDIR . "/includes/adminfunctions.php";
$description = "This graph shows the number of tasks incomplete compared with total number of tasks for each project";
$datefrom = fromMySQLDate( date( "Y-m-d", mktime( 0, 0, 0, date( "m" ), date( "d" ) - 7, date( "Y" ) ) ) );
$dateto = fromMySQLDate( date( "Y-m-d", mktime( 0, 0, 0, date( "m" ), date( "d" ) + 1, date( "Y" ) ) ) );

if ($statsonly) {
	return false;
}

$chartdata = array();
$statuses = get_query_val( "tbladdonmodules", "value", array( "module" => "project_management", "setting" => "completedstatuses" ) );
$statuses = explode( ",", $statuses );
$result = select_query( "mod_project", "id,title", "status NOT IN ('" . implode( "','", array_map( "db_escape_string", $statuses ) ) . "')" );

while ($data = mysql_fetch_array( $result )) {
	$projectid = $data["id"];
	$title = $data["title"];
	$totaltasks = get_query_val( "mod_projecttasks", "COUNT(*)", array( "projectid" => $projectid ) );
	$completedtasks = get_query_val( "mod_projecttasks", "COUNT(*)", array( "projectid" => $projectid, "completed" => "1" ) );
	$chartdata[$title] = $completedtasks;
	$chartdata2[$title] = $totaltasks;
}

$graph = new WHMCSGraph( 1000, 400 );
$graph->setTitle( "Task Status per Project" );
$graph->setBarColor( "220,57,18", "51,102,204" );
$graph->addData( $chartdata, $chartdata2 );
$graph->setLegendTitle( "Completed Tasks", "Total Tasks" );
$graph->setDataValues( true );
$graph->setXValuesHorizontal( true );
$graph->setLegend( true );
?>