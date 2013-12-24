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
 **/

function project_management_config() {
	$configarray = array("name" => "Project Management", "version" => "1.1", "author" => "WHMCS", "language" => "english", "description" => "Track & manage projects, tasks & time with ease using the Official Project Management Addon for WHMCS.<br />Find out more & purchase @ <a href=\"http://go.whmcs.com/90/project-management\" target=\"_blank\">www.whmcs.com/addons/project-management</a>", "premium" => true, "fields" => array());

	if (!PMADDONLICENSE) {
		$configarray['fields']['license'] = array("FriendlyName" => "License Check Failed", "Type" => "", "Description" => "You need to purchase the project management addon from <a href=\"http://go.whmcs.com/90/project-management\" target=\"_blank\">www.whmcs.com/addons/project-management</a> before you can use this functionality. If you just purchased it recently, please <a href=\"configaddonmods.php?pmrefresh=1#project_management\">click here</a> to refresh this message");
	}

	$fieldname = "Master Admin Users";
	$result = select_query("tbladminroles", "", "", "name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$configarray['fields']["masteradmin" . $data['id']] = array("FriendlyName" => $fieldname, "Type" => "yesno", "Description" => "Allow Access to Settings for <strong>" . $data['name'] . "</strong> users");
		$fieldname = "";
	}

	return $configarray;
}

function project_management_activate() {
	$result = full_query($query);
	$result = full_query($query);
	$query = "CREATE TABLE IF NOT EXISTS `mod_projecttasks` (`id` int(10) NOT NULL AUTO_INCREMENT, `projectid` int(10) NOT NULL, `task` text NOT NULL, `notes` TEXT NOT NULL, `adminid` int(11) NOT NULL, `created` DATETIME NOT NULL, `duedate` date NOT NULL, `completed` int(1) NOT NULL, `billed` INT(1) NOT NULL, `order` INT(3) NOT NULL, PRIMARY KEY (`id`))";
	$result = full_query($query);
	$query = "CREATE TABLE IF NOT EXISTS `mod_projecttimes` (`id` int(10) NOT NULL AUTO_INCREMENT, `projectid` int(10) NOT NULL, `taskid` int(10) NOT NULL, `adminid` VARCHAR(255) NOT NULL, `start` VARCHAR(255) NOT NULL, `end` VARCHAR(255) NOT NULL, `donotbill` INT(1) NOT NULL, PRIMARY KEY (`id`))";
	$result = full_query($query);
	$query = "CREATE TABLE IF NOT EXISTS `mod_projecttasktpls` (`id` int(10) NOT NULL AUTO_INCREMENT, `name` text NOT NULL, `tasks` text NOT NULL, PRIMARY KEY (`id`))";
	full_query($query);
	$result = $query = "CREATE TABLE IF NOT EXISTS `mod_project` (`id` int(10) NOT NULL AUTO_INCREMENT,`userid` int(10) NOT NULL,`title` text NOT NULL,`attachments` text NOT NULL,`ticketids` text NOT NULL,`invoiceids` text NOT NULL,`notes` text NOT NULL,`adminid` int(10) NOT NULL,`status` VARCHAR(255) NOT NULL, `created` date NOT NULL,`duedate` date NOT NULL,`completed` int(1) NOT NULL,`lastmodified` datetime NOT NULL, PRIMARY KEY (`id`))";
	$query = "CREATE TABLE IF NOT EXISTS `mod_projectlog` (`id` INT(255) NOT NULL AUTO_INCREMENT PRIMARY KEY, `projectid` INT(11) NOT NULL, `date` DATETIME NOT NULL, `msg` VARCHAR(255) NOT NULL, `adminid` INT(11) NOT NULL)";
	full_query($query);
	$result = $query = "CREATE TABLE IF NOT EXISTS `mod_projectmessages` (`id` int(10) NOT NULL AUTO_INCREMENT, `projectid` int(10) NOT NULL, `date` datetime NOT NULL, `message` text NOT NULL, `attachments` text NOT NULL, `adminid` int(10) NOT NULL, PRIMARY KEY (`id`))";
	full_query("INSERT INTO `tbladdonmodules` (`module`, `setting`, `value`) VALUES('project_management', 'hourlyrate', '100.00')");
	full_query("INSERT INTO `tbladdonmodules` (`module`, `setting`, `value`) VALUES('project_management', 'statusvalues', 'Pending,In Progress,Awaiting,Abandoned,Completed')");
	full_query("INSERT INTO `tbladdonmodules` (`module`, `setting`, `value`) VALUES('project_management', 'completedstatuses', 'Abandoned,Completed')");
	full_query("INSERT INTO `tbladdonmodules` (`module`, `setting`, `value`) VALUES('project_management', 'perms', 'a:13:{i:0;a:3:{i:1;s:1:\"1\";i:2;s:1:\"1\";i:3;s:1:\"1\";}i:1;a:3:{i:1;s:1:\"1\";i:2;s:1:\"1\";i:3;s:1:\"1\";}i:2;a:2:{i:1;s:1:\"1\";i:2;s:1:\"1\";}i:3;a:2:{i:1;s:1:\"1\";i:2;s:1:\"1\";}i:4;a:2:{i:1;s:1:\"1\";i:2;s:1:\"1\";}i:5;a:2:{i:1;s:1:\"1\";i:2;s:1:\"1\";}i:6;a:1:{i:1;s:1:\"1\";}i:7;a:2:{i:1;s:1:\"1\";i:2;s:1:\"1\";}i:8;a:3:{i:1;s:1:\"1\";i:2;s:1:\"1\";i:3;s:1:\"1\";}i:9;a:3:{i:1;s:1:\"1\";i:2;s:1:\"1\";i:3;s:1:\"1\";}i:10;a:2:{i:1;s:1:\"1\";i:2;s:1:\"1\";}i:11;a:1:{i:1;s:1:\"1\";}i:12;a:1:{i:1;s:1:\"1\";}}')");
}

function project_management_deactivate() {
	$query = "DROP TABLE `mod_project`";
	$result = full_query($query);
	$query = "DROP TABLE `mod_projectmessages`";
	$result = full_query($query);
	$query = "DROP TABLE `mod_projecttasks`";
	$result = full_query($query);
	$query = "DROP TABLE `mod_projecttimes`";
	$result = full_query($query);
	$query = "DROP TABLE `mod_projecttasktpls`";
	$result = full_query($query);
	$query = "DROP TABLE `mod_projectlog`";
	$result = full_query($query);
}

function project_management_upgrade() {
	if ($version < 1.10000000000000008881784) {
		$result = full_query("ALTER TABLE `mod_project`  ADD `invoiceids` TEXT NOT NULL AFTER `ticketids`");
		$result = full_query("ALTER TABLE `mod_projecttasks`  ADD `duedate` DATE NOT NULL AFTER `created`");
		$result = full_query("ALTER TABLE `mod_projecttasks`  ADD `notes` TEXT NOT NULL AFTER `task`");
		$result = full_query("ALTER TABLE `mod_projecttasks`  ADD `adminid` INT(11) NOT NULL AFTER `notes`");
		$result = full_query("ALTER TABLE `mod_projecttasks`  ADD `billed` INT(1) NOT NULL AFTER `completed`");
		$result = full_query("ALTER TABLE `mod_projecttasks`  ADD `order` INT(3) NOT NULL AFTER `billed`");
		$result = full_query("ALTER TABLE `mod_projecttimes`  ADD `donotbill` INT(1) NOT NULL");
		$query = "CREATE TABLE IF NOT EXISTS `mod_projecttasktpls` (`id` int(10) NOT NULL AUTO_INCREMENT, `name` text NOT NULL, `tasks` text NOT NULL, PRIMARY KEY (`id`))";
		$result = full_query($query);
	}

}

function project_management_output($vars) {
	global $whmcs;
	global $licensing;
	global $CONFIG;
	global $aInt;
	global $numrows;
	global $page;
	global $limit;
	global $order;
	global $orderby;
	global $jquerycode;
	global $jscode;
	global $attachments_dir;

	require ROOTDIR . "/includes/clientfunctions.php";
	require ROOTDIR . "/includes/invoicefunctions.php";
	$modulelink = $vars['modulelink'];
	$perms = unserialize($vars['perms']);
	$m = $_REQUEST['m'];
	$a = $_REQUEST['a'];
	$action = $_REQUEST['action'];

	if (!PMADDONLICENSE) {
		if ($whmcs->get_req_var("refresh")) {
			$licensing->forceRemoteCheck();
			redir("module=project_management");
		}

		echo "<div class=\"gracefulexit\">
Your WHMCS license key is not enabled to use the Project Management Addon yet.<br /><br />
You can find out more about it and purchase @ <a href=\"http://go.whmcs.com/90/project-management\" target=\"_blank\">www.whmcs.com/addons/project-management</a><br /><br />
If you have only recently purchased the addon, please <a href=\"addonmodules.php?module=project_management&refresh=1\">click here</a> to perform a license refresh.
</div>";
		return false;
	}


	if ($_REQUEST['createproj']) {
		$statuses = explode(",", $vars['statusvalues']);

		if ($_REQUEST['ajax']) {
			if (project_management_checkperm("Create New Projects")) {
				$dates = array();
				foreach ($_REQUEST['input'] as $key => $value) {

					if ($value['name'] == "ticketnum") {
						$value['name'] = "ticketids";
					}


					if ($value['name'] == "created" || $value['name'] == "duedate") {
						$dates[$value['name']] = $value['value'];
						$value['value'] = toMySQLDate($value['value']);
					}

					$insertarr[$value['name']] = $value['value'];
				}

				$insertarr['status'] = $statuses[0];
				$insertarr['lastmodified'] = "now()";
				$projectid = insert_query("mod_project", $insertarr);
				echo "<tr><td><a href=\"addonmodules.php?module=project_management&m=view&projectid=" . $projectid . "\">" . $projectid . "</a></td><td><a href=\"addonmodules.php?module=project_management&m=view&projectid=" . $projectid . "\">" . $insertarr['title'] . "</a> <span id=\"projecttimercontrol" . $projectid . "\" class=\"tickettimer\"><a href=\"#\" onclick=\"projectstarttimer('" . $projectid . "');return false\"><img src=\"../modules/addons/project_management/images/starttimer.png\" align=\"absmiddle\" border=\"0\" /> Start Tracking Time</a></td><td>" . get_query_val("tbladmins", "CONCAT(firstname,' ',lastname)", array("id" => $insertarr['adminid'])) . "</td><td>" . $dates['created'] . "</td><td>" . $dates['duedate'] . "</td><td>" . getTodaysDate() . "</td><td>" . $statuses[0] . "</td></tr>";
				exit();
			}
			else {
				echo "0";
				exit();
			}
		}


		if (project_management_checkperm("Create New Projects") && trim($_REQUEST['title'])) {
			$projectid = insert_query("mod_project", array("title" => $_REQUEST['title'], "userid" => $_REQUEST['userid'], "created" => toMySQLDate($_REQUEST['created']), "duedate" => toMySQLDate($_REQUEST['duedate']), "adminid" => $_REQUEST['adminid'], "ticketids" => $_REQUEST['ticketnum'], "status" => $statuses[0]));
			project_management_log($projectid, $vars['_lang']['createdproject']);
			redir("module=project_management&m=view&projectid=" . (int)$projectid);
		}
	}

	$jscode = "function createnewproject() {
    $(\"#createnewcont\").slideDown();
}
function cancelnewproject() {
    $(\"#createnewcont\").slideUp();
}
function searchselectclient(userid,name,email) {
    $(\"#clientname\").val(name);
    $(\"#userid\").val(userid);
    $(\"#cpclientname\").val(name);
    $(\"#cpuserid\").val(userid);
    $(\"#cpclientsearchcancel\").fadeOut();
	$(\"#cpticketclientsearchresults\").slideUp(\"slow\");
}
";
	$jquerycode = "$(\"#cpclientname\").keyup(function () {
	var ticketuseridsearchlength = $(\"#cpclientname\").val().length;
	if (ticketuseridsearchlength>2) {
	$.post(\"search.php\", { ticketclientsearch: 1, value: $(\"#cpclientname\").val() },
	    function(data){
            if (data) {
                $(\"#cpticketclientsearchresults\").html(data);
                $(\"#cpticketclientsearchresults\").slideDown(\"slow\");
                $(\"#cpclientsearchcancel\").fadeIn();
            }
        });
	}
});
$(\"#cpclientsearchcancel\").click(function () {
    $(\"#cpticketclientsearchresults\").slideUp(\"slow\");
    $(\"#cpclientsearchcancel\").fadeOut();
});";
	$headeroutput = "
<link href=\"../modules/addons/project_management/css/style.css\" rel=\"stylesheet\" type=\"text/css\" />

<div class=\"projectmanagement\">";

	if (project_management_checkperm("Create New Projects")) {
		$headeroutput .= "
<div id=\"createnewcont\" style=\"display:none;\">
<div class=\"createnewcont2\">
<div class=\"createnewproject\">
<div class=\"title\">" . $vars['_lang']['createnewproject'] . "</div>
<form method=\"post\" action=\"" . $modulelink . "&createproj=1\">
<div class=\"label\">" . $vars['_lang']['title'] . "</div>
<input type=\"text\" name=\"title\" class=\"title\" />
<div class=\"float\">
<div class=\"label\">" . $vars['_lang']['created'] . "</div>
<input type=\"text\" name=\"created\" class=\"datepick\" value=\"" . getTodaysDate() . "\" />
</div>
<div class=\"float\">
<div class=\"label\">" . $vars['_lang']['duedate'] . "</div>
<input type=\"text\" name=\"duedate\" class=\"datepick\" value=\"" . getTodaysDate() . "\" />
</div>
<div class=\"float\">
<div class=\"label\">" . $vars['_lang']['assignedto'] . "</div>
<select class=\"title\" name=\"adminid\">";
		$headeroutput .= "<option value=\"0\">" . $vars['_lang']['none'] . "</option>";
		$result = select_query("tbladmins", "id,firstname,lastname", array("disabled" => "0"), "firstname` ASC,`lastname", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$aid = $data['id'];
			$adminfirstname = $data['firstname'];
			$adminlastname = $data['lastname'];
			$headeroutput .= "<option value=\"" . $aid . "\"";

			if ($aid == $adminid) {
				echo " selected";
			}

			$headeroutput .= ">" . $adminfirstname . " " . $adminlastname . "</option>";
		}

		$headeroutput .= "</select>
</div>
<div class=\"float\">
<div class=\"label\">" . $vars['_lang']['ticketnumberhash'] . "</div>
<input type=\"text\" name=\"ticketnum\" class=\"ticketnum\" />
</div>
<div class=\"clear\"></div>
<div class=\"float\">
<div class=\"label\">" . $vars['_lang']['associatedclient'] . "</div>
<input type=\"hidden\" name=\"userid\" id=\"cpuserid\" /><input type=\"text\" id=\"cpclientname\" value=\"" . $clientname . "\" class=\"title\" onfocus=\"if(this.value=='" . addslashes($clientname) . "')this.value=''\" /> <img src=\"images/icons/delete.png\" alt=\"" . $vars['_lang']['cancel'] . "\" align=\"right\" id=\"clientsearchcancel\" height=\"16\" width=\"16\"><div id=\"cpticketclientsearchresults\" style=\"z-index:2000;\"></div>
</div>
<br /><br />
<div align=\"center\"><input type=\"submit\" value=\"" . $vars['_lang']['create'] . "\" class=\"create\" />&nbsp;<input type=\"button\" value=\"" . $vars['_lang']['cancel'] . "\" class=\"create\" onclick=\"cancelnewproject();return false\" /></div>
</form>
</div>
</div>
</div>";
	}

	$headeroutput .= "<div class=\"adminbar\"><a href=\"" . $modulelink . "\"><img src=\"images/icons/system.png\" /> " . $vars['_lang']['home'] . "</a> <a href=\"" . $modulelink . "&m=reports\"><img src=\"images/icons/reports.png\" /> " . $vars['_lang']['viewreports'] . "</a> <a href=\"reports.php?report=project_staff_logs\"><img src=\"images/icons/billableitems.png\" /> " . $vars['_lang']['viewstafflogs'] . "</a> <a href=\"" . $modulelink . "&m=activity\"><img src=\"images/icons/logs.png\" /> " . $vars['_lang']['viewactivitylogs'] . "</a> ";

	if (project_management_check_masteradmin()) {
		$headeroutput .= "<a href=\"" . $modulelink . "&m=settings\"><img src=\"images/icons/config.png\" /> " . $vars['_lang']['settings'] . "</a> ";
	}

	$headeroutput .= "<a href=\"http://docs.whmcs.com/Project_Management\"><img src=\"images/icons/support.png\" /> " . $vars['_lang']['help'] . "</a></div>

<div class=\"mainbar\">
<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td>";

	if (project_management_checkperm("Create New Projects")) {
		$headeroutput .= "<a href=\"#\" onclick=\"createnewproject();return false\" class=\"create\"><img src=\"images/icons/add.png\" align=\"top\" border=\"0\" /> <b>" . $vars['_lang']['createnewproject'] . "</b></a>
</td><td align=\"center\">";
	}

	$headeroutput .= "<span class=\"browsehover\">" . $vars['_lang']['browseprojects'] . "</span>:&nbsp;<a href=\"" . $modulelink . "\">" . $vars['_lang']['incomplete'] . "</a> | <a href=\"" . $modulelink . "&view=mineincomplete\">" . $vars['_lang']['myincomplete'] . "</a> | <a href=\"" . $modulelink . "&view=all\">" . $vars['_lang']['viewall'] . "</a> | <a href=\"" . $modulelink . "&view=mine\">" . $vars['_lang']['assignedtome'] . "</a> | <a href=\"" . $modulelink . "&view=week\">" . $vars['_lang']['duein7days'] . "</a> | <a href=\"" . $modulelink . "&view=closed\">" . $vars['_lang']['closed'] . "</a><br />
<strong>" . $vars['_lang']['browsetasks'] . "</strong>&nbsp;:&nbsp;<a href=\"" . $modulelink . "&view=tasks&filter=incomplete\">" . $vars['_lang']['incomplete'] . "</a> | <a href=\"" . $modulelink . "&view=tasks&filter=mineincomplete\">" . $vars['_lang']['myincomplete'] . "</a> | <a href=\"" . $modulelink . "&view=tasks\">" . $vars['_lang']['viewall'] . "</a> | <a href=\"" . $modulelink . "&view=tasks&filter=mine\">" . $vars['_lang']['assignedtome'] . "</a> | <a href=\"" . $modulelink . "&view=tasks&filter=week\">" . $vars['_lang']['duein7days'] . "</a> | <a href=\"" . $modulelink . "&view=tasks&filter=closed\">" . $vars['_lang']['closed'] . "</a></span>
</td><td>
<form method=\"post\" action=\"" . $modulelink . "\">
<div class=\"search\"><input type=\"text\" name=\"q\" value=\"" . (isset($_REQUEST['q']) ? $_REQUEST['q'] : $vars['_lang']['search']) . "\" onfocus=\"this.value=(this.value=='" . $vars['_lang']['search'] . "') ? '' : this.value;\" onblur=\"this.value=(this.value=='') ? '" . $vars['_lang']['search'] . "' : this.value;\" class=\"search\" /></div>
</form>
</td></tr></table>
</div>
";

	if (!in_array($m, array("view", "edittask", "activity", "reports", "settings"))) {
		$m = "overview";
	}

	$modulelink .= "&m=" . $m;
	require ROOTDIR . "/modules/addons/project_management/" . $m . ".php";
	echo "</div>";
}

function project_management_daysleft($duedate, $vars) {
	if ($duedate == "0000-00-00") {
		return "<span style=\"color:#73BC10\">" . $vars['_lang']['noduedate'] . "</span>";
	}

	$datetime = strtotime("now");
	$date2 = strtotime($duedate);
	$days = ceil(($date2 - $datetime) / 86400);

	if ($days == "-0") {
		$days = 0;
	}

	$dueincolor = ($days < 2 ? "cc0000" : "73BC10");

	if (0 <= $days) {
		return "<span style=\"color:#" . $dueincolor . "\">" . $vars['_lang']['duein'] . " " . $days . " " . $vars['_lang']['days'] . "</span>";
	}

	return "<span style=\"color:#" . $dueincolor . "\">" . $vars['_lang']['due'] . " " . $days * (0 - 1) . " " . $vars['_lang']['daysago'] . "</span>";
}

function project_management_tasksstatus($projectid, $vars) {
	$totaltasks = get_query_val("mod_projecttasks", "COUNT(id)", array("projectid" => $projectid));
	$completed = get_query_val("mod_projecttasks", "COUNT(id)", array("projectid" => $projectid, "completed" => "1"));
	$html = "<span class=\"" . ($totaltasks == $completed ? "green" : "red") . "\">" . $totaltasks . " " . $vars['_lang']['tasks'] . "</span> / " . $completed . " " . $vars['_lang']['completed'];
	$percent = round($completed / $totaltasks * 100);
	return array("completed" => $completed, "total" => $totaltasks, "percent" => $percent, "html" => $html);
}

function project_management_log($projectid, $msg) {
	insert_query("mod_projectlog", array("projectid" => $projectid, "date" => "now()", "msg" => $msg, "adminid" => $_SESSION['adminid']));
	update_query("mod_project", array("lastmodified" => "now()"), array("id" => $projectid));
}

function project_management_sec2hms($sec, $padHours = false) {
	if ($sec <= 0) {
		$sec = 0;
	}

	$hms = "";
	$hours = intval(intval($sec) / 3600);
	$hms .= ($padHours ? str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" : $hours . ":");
	$minutes = intval($sec / 60 % 60);
	$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";
	$seconds = intval($sec % 60);
	$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
	return $hms;
}

function project_management_checkperm($perm) {
	if (project_management_check_masteradmin()) {
		return true;
	}

	static $PMRoleID = 0;
	static $PMPerms = "";

	if (!$PMPerms) {
		$perms = get_query_val("tbladdonmodules", "value", array("module" => "project_management", "setting" => "perms"));
		$PMPerms = unserialize($perms);
	}


	if (!$PMRoleID) {
		$PMRoleID = get_query_val("tbladmins", "roleid", array("id" => $_SESSION['adminid']));
	}

	$revperms = array();
	$permissions = project_management_permslist();
	foreach ($permissions as $k => $v) {
		$revperms[$v] = $k;
	}


	if ($PMPerms[$revperms[$perm]][$PMRoleID]) {
		return true;
	}

	return false;
}

function project_management_permslist() {
	$permissions = array("0" => "Create New Projects", "1" => "View All Projects", "13" => "View Only Assigned Projects", "2" => "Edit Project Details", "3" => "Update Status", "4" => "Create Tasks", "5" => "Edit Tasks", "6" => "Delete Tasks", "7" => "Bill Tasks", "8" => "Associate Tickets", "9" => "Post Messages", "10" => "View Reports", "11" => "Delete Messages", "12" => "Delete Projects", "14" => "View Recent Activity");
	return $permissions;
}

function project_management_check_viewproject($projectid, $adminid = "") {
	if (!$adminid) {
		$adminid = $_SESSION['adminid'];
	}


	if (project_management_checkperm("View All Projects")) {
		return true;
	}

	$projectid = get_query_val("mod_project", "id", array("id" => $projectid));

	if (!$projectid) {
		return false;
	}


	if (project_management_checkperm("View Only Assigned Projects")) {
		$projectadminid = get_query_val("mod_project", "adminid", array("id" => $projectid));

		if ($adminid == $projectadminid) {
			return true;
		}

		$tasksresult = select_query("mod_projecttasks", "adminid", array("projectid" => $projectid));

		while ($tasksdata = mysql_fetch_assoc($tasksresult)) {
			if ($adminid == $tasksdata['adminid']) {
				return true;
			}
		}
	}

	return false;
}

function project_management_check_masteradmin($PMRoleID = "", $adminid = "") {
	if (!$PMRoleID) {
		$PMRoleID = get_query_val("tbladmins", "roleid", array("id" => ($adminid ? $adminid : $_SESSION['adminid'])));
	}


	if (get_query_val("tbladdonmodules", "value", array("module" => "project_management", "setting" => "masteradmin" . $PMRoleID)) == "on") {
		return true;
	}

	return false;
}

function project_management_clientarea($vars) {
	require ROOTDIR . "/modules/addons/project_management/clientarea.php";
	return array("pagetitle" => $vars['_lang']['projectsoverview'], "templatefile" => $tplfile, "vars" => $tplvars, "forcessl" => true, "requirelogin" => true);
}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "dl") {
	require "../../../init.php";
	$projectid = (isset($_REQUEST['projectid']) ? (int)$_REQUEST['projectid'] : 0);
	$msg = (isset($_REQUEST['msg']) ? (int)$_REQUEST['msg'] : 0);
	$i = (isset($_REQUEST['i']) ? (int)$_REQUEST['i'] : 0);
	$adminid = (isset($_SESSION['adminid']) ? (int)$_SESSION['adminid'] : 0);
	$userid = (isset($_SESSION['uid']) ? (int)$_SESSION['uid'] : 0);

	if ($adminid) {
		$result = select_query("tbladdonmodules", "value", array("module" => "project_management", "setting" => "access"));
		$data = mysql_fetch_array($result);
		$allowedroles = explode(",", $data[0]);
		$result = select_query("tbladmins", "roleid", array("id" => $adminid));
		$data = mysql_fetch_array($result);
		$adminroleid = $data[0];

		if (!in_array($adminroleid, $allowedroles)) {
			exit("Access Denied");
		}


		if (!project_management_check_viewproject($projectid)) {
			exit("Access Denied");
		}
	}
	else {
		if ($userid) {
			$accessallowed = get_query_val("mod_project", "id", array("id" => $projectid, "userid" => $userid));

			if (!$accessallowed) {
				exit("Access Denied");
			}
		}
		else {
			exit("Access Denied");
		}
	}


	if ($msg) {
		if (!$adminid) {
			exit("Access Denied");
		}

		$result = select_query("mod_projectmessages", "attachments", array("id" => $msg, "projectid" => $projectid));
		$data = mysql_fetch_array($result);
		$attachments = $data['attachments'];
		$attachments = explode(",", $attachments);
		$filename = $attachments[$i];
	}
	else {
		$result = select_query("mod_project", "attachments", array("id" => $projectid));
		$data = mysql_fetch_array($result);
		$attachments = $data['attachments'];
		$attachments = explode(",", $attachments);
		$filename = $attachments[$i];
	}

	$projectsdir = $attachments_dir . "projects/" . $projectid . "/";
	$filepath = $projectsdir . $attachments[$i];
	$folder_path_real = realpath($projectsdir);
	$file_path_real = realpath($filepath);

	if ($file_path_real === false || strpos($file_path_real, $folder_path_real) !== 0) {
		exit("File not found. Please contact support.");
	}

	header("Pragma: public");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0, private");
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"" . substr($filename, 7) . "\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: " . filesize($file_path_real) . "");
	readfile($file_path_real);
	exit();
}


if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (defined("PMADDONLICENSE")) {
	exit("License Hacking Attempt Detected");
}

global $whmcs;
global $licensing;

if ($whmcs->get_req_var("pmrefresh")) {
	$licensing->forceRemoteCheck();
}

define("PMADDONLICENSE", $licensing->isActiveAddon("Project Management Addon"));
?>