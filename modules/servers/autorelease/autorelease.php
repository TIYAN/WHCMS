<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 * */

function autorelease_ConfigOptions() {
	$depts = array();
	$depts[] = "0|None";
	$result = select_query( "tblticketdepartments", "", "", "order", "ASC" );

	while ($data = mysql_fetch_array( $result )) {
		$id = $data['id'];
		$name = $data['name'];
		$depts[] = "" . $id . "|" . $name;
	}

	$configarray = array( "Create Action" => array( "Type" => "dropdown", "Options" => "None,Add To-Do List Item,Create Support Ticket" ), "Suspend Action" => array( "Type" => "dropdown", "Options" => "None,Add To-Do List Item,Create Support Ticket" ), "Unsuspend Action" => array( "Type" => "dropdown", "Options" => "None,Add To-Do List Item,Create Support Ticket" ), "Terminate Action" => array( "Type" => "dropdown", "Options" => "None,Add To-Do List Item,Create Support Ticket" ), "Renew Action" => array( "Type" => "dropdown", "Options" => "None,Add To-Do List Item,Create Support Ticket" ), "Support Dept ID" => array( "Type" => "dropdown", "Options" => implode( ",", $depts ) ) );
	return $configarray;
}


function autorelease_CreateAccount($params) {
	if ($params['configoption1'] == "Add To-Do List Item") {
		$todoarray['title'] = "Service Provisioned";
		$todoarray['description'] = "Service ID # " . $params['serviceid'] . " was just auto provisioned";
		$todoarray['status'] = "Pending";
		$todoarray['date'] = $todoarray['duedate'] = date( "Y-m-d" );
		insert_query( "tbltodolist", $todoarray );
	}
	else {
		if ($params['configoption1'] == "Create Support Ticket") {
			$postfields['action'] = "openticket";
			$postfields['clientid'] = $params['clientsdetails']['userid'];
			$postfields['deptid'] = ($params['configoption6'] ? $params['configoption6'] : "1");
			$postfields['subject'] = "Service Provisioned";
			$postfields['message'] = "Service ID # " . $params['serviceid'] . " was just auto provisioned";
			$postfields['priority'] = "Low";
			localAPI( $postfields['action'], $postfields, 1 );
		}
	}

	updateService( array( "username" => "", "password" => "" ) );
	return "success";
}


function autorelease_SuspendAccount($params) {
	if ($params['configoption2'] == "Add To-Do List Item") {
		$todoarray['title'] = "Service Suspension";
		$todoarray['description'] = "Service ID # " . $params['serviceid'] . " requires suspension";
		$todoarray['status'] = "Pending";
		$todoarray['date'] = $todoarray['duedate'] = date( "Y-m-d" );
		insert_query( "tbltodolist", $todoarray );
	}
	else {
		if ($params['configoption2'] == "Create Support Ticket") {
			$postfields['action'] = "openticket";
			$postfields['clientid'] = $params['clientsdetails']['userid'];
			$postfields['deptid'] = ($params['configoption6'] ? $params['configoption6'] : "1");
			$postfields['subject'] = "Service Suspension";
			$postfields['message'] = "Service ID # " . $params['serviceid'] . " requires suspension";
			$postfields['priority'] = "Low";
			localAPI( $postfields['action'], $postfields, 1 );
		}
	}

	return "success";
}


function autorelease_UnsuspendAccount($params) {
	if ($params['configoption3'] == "Add To-Do List Item") {
		$todoarray['title'] = "Service Reactivation";
		$todoarray['description'] = "Service ID # " . $params['serviceid'] . " requires unsuspending";
		$todoarray['status'] = "Pending";
		$todoarray['date'] = $todoarray['duedate'] = date( "Y-m-d" );
		insert_query( "tbltodolist", $todoarray );
	}
	else {
		if ($params['configoption3'] == "Create Support Ticket") {
			$postfields['action'] = "openticket";
			$postfields['clientid'] = $params['clientsdetails']['userid'];
			$postfields['deptid'] = ($params['configoption6'] ? $params['configoption6'] : "1");
			$postfields['subject'] = "Service Reactivation";
			$postfields['message'] = "Service ID # " . $params['serviceid'] . " requires unsuspending";
			$postfields['priority'] = "Low";
			localAPI( $postfields['action'], $postfields, 1 );
		}
	}

	return "success";
}


function autorelease_TerminateAccount($params) {
	if ($params['configoption4'] == "Add To-Do List Item") {
		$todoarray['title'] = "Service Termination";
		$todoarray['description'] = "Service ID # " . $params['serviceid'] . " requires termination";
		$todoarray['status'] = "Pending";
		$todoarray['date'] = $todoarray['duedate'] = date( "Y-m-d" );
		insert_query( "tbltodolist", $todoarray );
	}
	else {
		if ($params['configoption4'] == "Create Support Ticket") {
			$postfields['action'] = "openticket";
			$postfields['clientid'] = $params['clientsdetails']['userid'];
			$postfields['deptid'] = ($params['configoption6'] ? $params['configoption6'] : "1");
			$postfields['subject'] = "Service Termination";
			$postfields['message'] = "Service ID # " . $params['serviceid'] . " requires termination";
			$postfields['priority'] = "Low";
			localAPI( $postfields['action'], $postfields, 1 );
		}
	}

	return "success";
}


function autorelease_Renew($params) {
	if ($params['configoption5'] == "Add To-Do List Item") {
		$todoarray['title'] = "Service Renewal";
		$todoarray['description'] = "Service ID # " . $params['serviceid'] . " was just renewed";
		$todoarray['status'] = "Pending";
		$todoarray['date'] = $todoarray['duedate'] = date( "Y-m-d" );
		insert_query( "tbltodolist", $todoarray );
	}
	else {
		if ($params['configoption5'] == "Create Support Ticket") {
			$postfields['action'] = "openticket";
			$postfields['clientid'] = $params['clientsdetails']['userid'];
			$postfields['deptid'] = ($params['configoption6'] ? $params['configoption6'] : "1");
			$postfields['subject'] = "Service Renewal";
			$postfields['message'] = "Service ID # " . $params['serviceid'] . " was just renewed";
			$postfields['priority'] = "Low";
			localAPI( $postfields['action'], $postfields, 1 );
		}
	}

	return "success";
}


?>