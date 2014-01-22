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

$activestatuses = $awaitingreplystatuses = array();
$query = "SELECT title,showactive,showawaiting FROM tblticketstatuses";
$result = full_query($query);

while ($data = mysql_fetch_array($result)) {
	if ($data['showactive']) {
		$activestatuses[] = $data[0];
	}


	if ($data['showawaiting']) {
		$awaitingreplystatuses[] = $data[0];
	}
}

$deptfilter = "";

if (!$ignore_dept_assignments) {
	$result = select_query("tbladmins", "supportdepts", array("id" => $_SESSION['adminid']));
	$data = mysql_fetch_array($result);
	$supportdepts = $data[0];
	$supportdepts = explode(",", $supportdepts);
	$deptids = array();
	foreach ($supportdepts as $id) {

		if (trim($id)) {
			$deptids[] = trim($id);
			continue;
		}
	}


	if (count($deptids)) {
		$deptfilter = "WHERE tblticketdepartments.id IN (" . db_build_in_array($deptids) . ") ";
	}
}

$result = full_query("SELECT id,name,(SELECT COUNT(id) FROM tbltickets WHERE did=tblticketdepartments.id AND status IN (" . db_build_in_array($awaitingreplystatuses) . ")) AS awaitingreply,(SELECT COUNT(id) FROM tbltickets WHERE did=tblticketdepartments.id AND status IN (" . db_build_in_array($activestatuses) . ")) AS opentickets FROM tblticketdepartments " . $deptfilter . "ORDER BY name ASC");
$apiresults = array("result" => "success", "totalresults" => mysql_num_rows($result));

while ($data = mysql_fetch_array($result)) {
	$apiresults['departments']['department'][] = array("id" => $data['id'], "name" => $data['name'], "awaitingreply" => $data['awaitingreply'], "opentickets" => $data['opentickets']);
}

$responsetype = "xml";
?>