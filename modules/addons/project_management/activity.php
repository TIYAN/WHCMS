<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

echo $headeroutput;

if (!project_management_checkperm( "View Recent Activity" )) {
	echo "<p>You do not have permission to view recent activity.</p>";
	return false;
}

$aInt->sortableTableInit( "duedate", "ASC" );
$tabledata = "";
$where = array();

if ($_REQUEST["projectid"]) {
	$where["projectid"] = (int)$_REQUEST["projectid"];
}

$result = select_query( "mod_projectlog", "COUNT(*)", $where );
$data = mysql_fetch_array( $result );
$numrows = $data[0];
$result = select_query( "mod_projectlog", "mod_projectlog.*,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE tbladmins.id=mod_projectlog.adminid) AS admin,(SELECT title FROM mod_project WHERE mod_project.id=mod_projectlog.projectid) AS projectname, (SELECT adminid FROM mod_project WHERE mod_project.id=mod_projectlog.projectid) as assignedadminid", $where, "id", "DESC", $page * $limit . "," . $limit );

while ($data = mysql_fetch_array( $result )) {
	$date = $data["date"];
	$projectid = $data["projectid"];
	$projectname = (project_management_check_viewproject( $projectid ) ? "<a href=\"" . $modulelink . "&m=view&projectid=" . $projectid . "\">" . $data["projectname"] . "</a>" : $data["projectname"]);
	$msg = $data["msg"];
	$admin = $data["admin"];
	$date = fromMySQLDate( $date, true );
	$tabledata[] = array( $date, $projectname, $msg, $admin );
}

echo $aInt->sortableTable( array( "Date", "Project", "Log Entry", "Admin User" ), $tabledata );
?>