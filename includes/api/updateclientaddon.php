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

$result = select_query("tblhostingaddons", "id,addonid,hostingid,status", array("id" => $id));
$data = mysql_fetch_array($result);

if (!$data['id']) {
	$apiresults = array("result" => "error", "message" => "Addon ID Not Found");
	return null;
}

$serviceid = $data['hostingid'];
$currentstatus = $data['status'];
$userid = get_query_val("tblhosting", "userid", array("id" => $serviceid));
$updateqry = array();

if ($addonid) {
	$updateqry['addonid'] = $addonid;
}
else {
	$addonid = $data['addonid'];
}


if ($name) {
	$updateqry['name'] = $name;
}


if ($setupfee) {
	$updateqry['setupfee'] = $setupfee;
}


if ($recurring) {
	$updateqry['recurring'] = $recurring;
}


if ($billingcycle) {
	$updateqry['billingcycle'] = $billingcycle;
}


if ($nextduedate) {
	$updateqry['nextduedate'] = $nextduedate;
}


if ($nextinvoicedate) {
	$updateqry['nextinvoicedate'] = $nextinvoicedate;
}


if ($notes) {
	$updateqry['notes'] = $notes;
}


if ($status && $status != $currentstatus) {
	$updateqry['status'] = $status;
}


if (0 < count($updateqry)) {
	update_query("tblhostingaddons", $updateqry, array("id" => $id));
	logActivity("Modified Addon - Addon ID: " . $id . " - Service ID: " . $serviceid);

	if ($currentstatus != "Active" && $status == "Active") {
		run_hook("AddonActivated", array("id" => $id, "userid" => $userid, "serviceid" => $serviceid, "addonid" => $addonid));
	}
	else {
		if ($currentstatus != "Suspended" && $status == "Suspended") {
			run_hook("AddonSuspended", array("id" => $id, "userid" => $userid, "serviceid" => $serviceid, "addonid" => $addonid));
		}
		else {
			if ($currentstatus != "Terminated" && $status == "Terminated") {
				run_hook("AddonTerminated", array("id" => $id, "userid" => $userid, "serviceid" => $serviceid, "addonid" => $addonid));
			}
			else {
				if ($currentstatus != "Cancelled" && $status == "Cancelled") {
					run_hook("AddonCancelled", array("id" => $id, "userid" => $userid, "serviceid" => $serviceid, "addonid" => $addonid));
				}
				else {
					if ($currentstatus != "Fraud" && $status == "Fraud") {
						run_hook("AddonFraud", array("id" => $id, "userid" => $userid, "serviceid" => $serviceid, "addonid" => $addonid));
					}
					else {
						run_hook("AddonEdit", array("id" => $id, "userid" => $userid, "serviceid" => $serviceid, "addonid" => $addonid));
					}
				}
			}
		}
	}

	$apiresults = array("result" => "success", "id" => $id);
	return 1;
}

$apiresults = array("result" => "error", "id" => $id, "message" => "Nothing to Update");
?>