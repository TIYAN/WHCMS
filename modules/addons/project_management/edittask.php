<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 * */

if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}


if (!project_management_checkperm( "Edit Tasks" )) {
	header( "Location: " . str_replace( "m=edittask", "m=view", $modulelink ) . "&projectid=" . $_REQUEST["projectid"] );
	exit();
}

require ROOTDIR . "/includes/gatewayfunctions.php";
require ROOTDIR . "/includes/ticketfunctions.php";
$projectid = $_REQUEST["projectid"];
$taskid = $_REQUEST["id"];
$modulelink .= "&projectid=" . (int)$projectid;

if ($a == "taskssave") {
	$task = $_REQUEST["task"];
	$taskid = $_REQUEST["taskid"];
	$taskadmin = $_REQUEST["taskadmin"];
	$taskduedate = toMySQLDate( $_REQUEST["taskduedate"] );
	$tasknotes = $_REQUEST["tasknotes"];
	update_query( "mod_projecttasks", array( "task" => $task, "adminid" => $taskadmin, "duedate" => $taskduedate, "notes" => $tasknotes ), array( "id" => $taskid ) );
	foreach ($_REQUEST["admin"] as $timerid => $adminval) {
		$starttime = $_REQUEST["start"][$timerid];
		$endtime = $_REQUEST["end"][$timerid];
		$starttime = toMySQLDate( $starttime );

		if (( $endtime && $endtime != "-" )) {
			$endtime = toMySQLDate( $endtime );
		}

		update_query( "mod_projecttimes", array( "adminid" => $adminval, "start" => strtotime( $starttime ), "end" => strtotime( $endtime ) ), array( "id" => $timerid ) );
	}

	project_management_log( $projectid, "Edited Task ID " . $taskid );
	header( "Location: " . str_replace( "m=edittask&projectid=" . $projectid, "m=view&projectid=" . $projectid, $modulelink ) );
	exit();
}


if ($projectid) {
	$result = select_query( "mod_project", "", array( "id" => $projectid ) );
	$data = mysql_fetch_array( $result );
	$projectid = $data["id"];

	if (!$projectid) {
		echo "<p><b>" . $vars["_lang"]["editedendtimefortimeid"] . "</b></p><p>Project ID Not Found</p>";
		return null;
	}

	$title = $data["title"];
	$attachments = $data["attachments"];
	$ticketids = $data["ticketids"];
	$notes = $data["notes"];
	$userid = $data["userid"];
	$adminid = $data["adminid"];
	$created = $data["created"];
	$duedate = $data["duedate"];
	$completed = $data["completed"];
	$projectstatus = $data["status"];
	$lastmodified = $data["lastmodified"];
	$daysleft = project_management_daysleft( $duedate, $vars );
	$attachments = explode( ",", $attachments );
	$ticketids = explode( ",", $ticketids );
	$created = fromMySQLDate( $created );
	$duedate = fromMySQLDate( $duedate );
	$lastmodified = fromMySQLDate( $lastmodified, true );
	$client = "";

	if (!$userid) {
		foreach ($ticketids as $i => $ticketnum) {

			if ($ticketnum) {
				$result = select_query( "tbltickets", "userid", array( "tid" => $ticketnum ) );
				$data = mysql_fetch_array( $result );
				$userid = $data["userid"];
				update_query( "mod_project", array( "userid" => $userid ), array( "id" => $projectid ) );
				continue;
			}
		}
	}


	if ($userid) {
		$result = select_query( "tblclients", "id,firstname,lastname,companyname", array( "id" => $userid ) );
		$data = mysql_fetch_array( $result );
		$clientname = $data[1] . " " . $data[2];

		if ($data[3]) {
			$clientname .= " (" . $data[3] . ")";
		}

		$client = "<a href=\"clientssummary.php?userid=" . $userid . "\">" . $clientname . "</a>";
	}

	$headtitle = $title;
}
else {
	header( "Location: " . str_replace( "m=edittask&projectid=" . $projectid, "", $modulelink ) );
	exit();
}

echo $headeroutput;
echo "<div id=\"title\" class=\"title\"><div class=\"displayval\">" . $headtitle . "</div><div class=\"editfield\"><input id=\"projecttitleeditfield\" type=\"text\" value=\"" . $headtitle . "\" /></div></div>
<div id\"daysleft\" class=\"daysleft\">" . $daysleft . "</div><br />

";
$taskid = $_REQUEST["id"];
$taskdata = mysql_fetch_assoc( select_query( "mod_projecttasks", "", array( "id" => $taskid ) ) );
$taskduedate = fromMySQLDate( $taskdata["duedate"] );
$taskadmindropdown = "<select name=\"taskadmin\" style=\"font-size:16px;\"><option value=\"0\">None</option>";
$result = select_query( "tbladmins", "id,firstname,lastname", "", "firstname` ASC,`lastname", "ASC" );

while ($data = mysql_fetch_array( $result )) {
	$aid = $data["id"];
	$adminfirstname = $data["firstname"];
	$adminlastname = $data["lastname"];
	$taskadmindropdown .= "<option value=\"" . $aid . "\"";

	if ($aid == $taskdata["adminid"]) {
		$taskadmindropdown .= " selected";
	}

	$taskadmindropdown .= ">" . $adminfirstname . " " . $adminlastname . "</option>";
}

$taskadmindropdown .= "</select>";
echo "<script type=\"text/javascript\" src=\"../modules/addons/project_management/js/jquery-ui-timepicker-addon.js\"></script>

<form method=\"post\" action=\"" . $modulelink . "&a=taskssave\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"taskid\" value=\"" . $taskid . "\" />

<div class=\"edittask\">

<div class=\"heading\"><img src=\"images/icons/todolist.png\" /> " . $vars["_lang"]["editingtask"] . "</div>

<div><input type=\"text\" name=\"task\" value=\"" . $taskdata["task"] . "\" class=\"taskinput\" style=\"width: 100%;\" /></div>

<table width=\"100%\">
<tr><td width=\"100\">" . $vars["_lang"]["assignedto"] . "</td><td>" . $taskadmindropdown . "</td><td width=\"100\">" . $vars["_lang"]["duedate"] . "</td><td><input type=\"text\" class=\"datepick\" name=\"taskduedate\" value=\"" . $taskduedate . "\" style=\"font-size:16px;width:120px;\" /></td></tr>
<tr><td colspan=\"4\"><br />" . $vars["_lang"]["tasknotes"] . "</td></tr>
<tr><td colspan=\"4\"><textarea style=\"width:100%;height:150px;\" name=\"tasknotes\">" . $taskdata["notes"] . "</textarea></td></tr>
</table>

<br />

<div class=\"box\">
<table width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\" class=\"tasks\" id=\"tasks\">
<tr><th>" . $vars["_lang"]["admin"] . "</th><th>" . $vars["_lang"]["starttime"] . "</th><th>" . $vars["_lang"]["stoptime"] . "</th></tr>";

if (( ( $CONFIG["DateFormat"] == "DD/MM/YYYY" || $CONFIG["DateFormat"] == "DD.MM.YYYY" ) || $CONFIG["DateFormat"] == "DD-MM-YYYY" )) {
	$localdateformat = "dd/mm/yy";
}
else {
	if ($CONFIG["DateFormat"] == "MM/DD/YYYY") {
		$localdateformat = "mm/dd/yy";
	}
	else {
		if (( $CONFIG["DateFormat"] == "YYYY/MM/DD" || $CONFIG["DateFormat"] == "YYYY-MM-DD" )) {
			$localdateformat = "yy/mm/dd";
		}
	}
}

$timeid = "";
$time_result = select_query( "mod_projecttimes", "", array( "taskid" => $taskid ) );

if ($timedata = mysql_fetch_assoc( $time_result )) {
	$timeid = $timedata["id"];
	$timestart = $timedata["start"];
	$timeend = $timedata["end"];
	$ts["h"] = date( "H", $timedata["start"] );
	$ts["m"] = date( "i", $timedata["start"] );
	$ts["s"] = date( "s", $timedata["start"] );

	if ($timeend) {
		$te["h"] = date( "H", $timedata["end"] );
		$te["m"] = date( "i", $timedata["end"] );
		$te["s"] = date( "s", $timedata["end"] );
	}
	else {
		$te["h"] = date( "H" );
		$te["m"] = date( "i" );
		$te["s"] = date( "s" );
		$timeend = mktime();
	}

	$jquerycode .= "
$(\"#start" . $timeid . "\").datetimepicker({
	hour: " . $ts["h"] . ",
	minute: " . $ts["m"] . ",
	second: " . $ts["s"] . ",
	defaultDate: " . $timestart . ",
	showSecond:true,
	ampm:false,
	dateFormat: \"" . $localdateformat . "\",
	timeFormat: \"hh:mm:ss\",
});
$(\"#end" . $timeid . "\").datetimepicker({
	hour: " . $te["h"] . ",
	minute: " . $te["m"] . ",
	second: " . $te["s"] . ",
	defaultDate: " . $timeend . ",
	showSecond:true,
	ampm:false,
	dateFormat: \"" . $localdateformat . "\",
	timeFormat: \"hh:mm:ss\",
});";
	echo "<tr><td align=\"center\"><select name=\"admin[" . $timeid . "]\">";
	$result = select_query( "tbladmins", "id,firstname,lastname", "", "firstname` ASC,`lastname", "ASC" );

	while ($data = mysql_fetch_array( $result )) {
		$aid = $data["id"];
		$adminfirstname = $data["firstname"];
		$adminlastname = $data["lastname"];
		echo "<option value=\"" . $aid . "\"";

		if ($aid == $timedata["adminid"]) {
			echo " selected";
		}

		echo ">" . $adminfirstname . " " . $adminlastname . "</option>";
	}

	echo "</select></td><td align=\"center\"><input type=\"text\" id=\"start" . $timeid . "\" name=\"start[" . $timeid . "]\" value=\"" . fromMySQLDate( date( "Y-m-d H:i:s", $timedata["start"] ), 1 ) . ":" . date( "s", $timedata["start"] ) . "\" size=\"30\" /></td><td align=\"center\"><input type=\"text\" id=\"end" . $timeid . "\" name=\"end[" . $timeid . "]\" value=\"" . ($timedata["end"] ? fromMySQLDate( date( "Y-m-d H:i:s", $timedata["end"] ), 1 ) . ":" . date( "s", $timedata["end"] ) : "") . "\" size=\"30\" /></td></tr>";
}


if (!$timeid) {
	echo "<tr><td colspan=\"3\" align=\"center\">" . $vars["_lang"]["notimesrecorded"] . "</td></tr>";
}

echo "
</table>
</div>

<p align=\"center\"><input type=\"submit\" value=\"" . $vars["_lang"]["save"] . "\" /> <input type=\"reset\" value=\"" . $vars["_lang"]["cancel"] . "\" /></p>

</div>

<p><input type=\"button\" value=\"" . $vars["_lang"]["backtoproject"] . "\" onclick=\"window.location='" . $vars["modulelink"] . "&m=view&projectid=" . $projectid . "'\" />

";
?>