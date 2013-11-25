<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

function project_management_recursive_rmdir($dir) {
	if (is_dir( $dir )) {
		$objects = scandir( $dir );
		foreach ($objects as $object) {

			if (( $object != "." && $object != ".." )) {
				if (filetype( $dir . "/" . $object ) == "dir") {
					project_management_recursive_rmdir( $dir . "/" . $object );
					continue;
				}

				unlink( $dir . "/" . $object );
				continue;
			}
		}

		reset( $objects );
		rmdir( $dir );
	}

}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$jscode .= "function doDelete(id) {
    if (confirm(\"" . $vars["_lang"]["confirmdeleteproject"] . "\")) {
        window.location='" . $modulelink . "&action=delete&projectid='+id;
    }
}
";

if ($action == "delete") {
	if (project_management_checkperm( "Delete Projects" )) {
		$projectdata = get_query_vals( "mod_project", "id,title,attachments", array( "id" => $_REQUEST["projectid"] ) );
		$attachments = explode( ",", $projectdata["attachments"] );
		$projectsdir = $attachments_dir . "projects/" . $projectdata["id"] . "/";
		project_management_recursive_rmdir( $projectsdir );
		delete_query( "mod_project", array( "id" => $projectdata["id"] ) );
		delete_query( "mod_projecttasks", array( "projectid" => $projectdata["id"] ) );
		delete_query( "mod_projecttimes", array( "projectid" => $projectdata["id"] ) );
		delete_query( "mod_projectmessages", array( "projectid" => $projectdata["id"] ) );
		delete_query( "mod_projectlog", array( "projectid" => $projectdata["id"] ) );
		project_management_log( $projectdata["projectid"], $vars["_lang"]["deletedproject"] . " - " . $projectdata["title"] );
	}

	header( "Location: " . $modulelink );
	exit();
}

$q = htmlspecialchars( $_REQUEST["q"] );
echo $headeroutput . "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"80%\" valign=\"top\">
";
$tabledata = "";
$aInt->sortableTableInit( "duedate", "ASC" );
$where = "completed=0";

if ($_REQUEST["view"] == "tasks") {
	if ($_REQUEST["filter"] == "mine") {
		$where = "adminid=" . $_SESSION["adminid"];
	}
	else {
		if ($_REQUEST["filter"] == "mineincomplete") {
			$where = "completed=0  AND adminid=" . $_SESSION["adminid"];
		}
		else {
			if ($_REQUEST["filter"] == "incomplete") {
				$where = "completed=0";
			}
			else {
				if ($_REQUEST["filter"] == "week") {
					$where = "completed=0 AND duedate<='" . date( "Y-m-d", mktime( 0, 0, 0, date( "m" ), date( "d" ) + 7, date( "Y" ) ) ) . "'";
				}
				else {
					if ($_REQUEST["filter"] == "closed") {
						$where = "completed=1";
					}
					else {
						if (( $_REQUEST["filter"] == "project" && is_numeric( $_REQUEST["projectid"] ) )) {
							$where = "projectid='" . (int)$_REQUEST["projectid"] . "'";
						}
						else {
							$where = "";
						}
					}
				}
			}
		}
	}


	if (( project_management_checkperm( "View Only Assigned Projects" ) && !project_management_checkperm( "View All Projects" ) )) {
		if ($where) {
			$where .= " AND adminid=" . (int)$_SESSION["adminid"];
		}
		else {
			$where = "adminid=" . (int)$_SESSION["adminid"];
		}
	}

	$numrows = get_query_val( "mod_projecttasks", "COUNT(id)", $where );
	$orderby = (in_array( $orderby, array( "task", "created", "duedate" ) ) ? $orderby : "");

	if (!$orderby) {
		$order = "";
	}

	$result = select_query( "mod_projecttasks", "id,projectid,task,created,duedate,adminid,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE id=mod_projecttasks.adminid) AS adminuser", $where, $orderby, $order, $page * $limit . ( "," . $limit ) );

	while ($data = mysql_fetch_array( $result )) {
		extract( $data );
		$daysleft = ($duedate != "0000-00-00" ? project_management_daysleft( $duedate, $vars ) : "-");
		$created = fromMySQLDate( $created );
		$duedate = ($duedate != "0000-00-00" ? fromMySQLDate( $duedate ) : "-");
		$projectdata = get_query_vals( "mod_project", "", array( "id" => $projectid ) );
		$projectname = $projectdata["title"];
		$projectadminid = $projectdata["adminid"];
		$show_project = false;

		if (project_management_check_viewproject( $projectid )) {
			$show_project = true;
		}

		$projectname = ($show_project ? "<a href=\"" . str_replace( "m=overview", "m=view", $modulelink ) . "&projectid=" . $projectid . "\">" . $projectname . "</a>" : $projectname);

		if (!$adminuser) {
			$adminuser = "-";
		}

		$editprojecthtml = ($show_project ? "<a href=\"" . str_replace( "m=overview", "m=view", $modulelink ) . "&projectid=" . $projectid . "\"><img src=\"images/edit.gif\" border=\"0\" /></a>" : "");
		$deleteprojecthtml = (project_management_checkperm( "Delete Projects" ) ? "<a href=\"#\" onclick=\"doDelete('" . $projectid . "');return false\"><img src=\"images/delete.gif\" border=\"0\" /></a>" : "");
		$tabledata[] = array( "<div align=\"left\">" . $projectname . "</div>", "<div align=\"left\">" . $task . "</div>", $created, $duedate, $daysleft, $adminuser, $editprojecthtml, $deleteprojecthtml );
	}

	echo $aInt->sortableTable( array( array( "project", $vars["_lang"]["projectname"] ), array( "task", $vars["_lang"]["taskname"] ), array( "created", $vars["_lang"]["created"] ), array( "duedate", $vars["_lang"]["duedate"] ), array( "duedate", $vars["_lang"]["daysleft"] ), $vars["_lang"]["assignedto"], "", "" ), $tabledata );
}
else {
	if (is_numeric( $q )) {
		$where = "ticketids LIKE '%" . (int)$q . "%' OR title LIKE '%" . db_escape_string( $q ) . "%'";
	}
	else {
		if ($q) {
			$where = "title LIKE '%" . db_escape_string( $q ) . "%'";
		}
		else {
			if ($_REQUEST["view"] == "all") {
				$where = "";
			}
			else {
				if ($_REQUEST["view"] == "week") {
					$where = "completed=0 AND duedate<='" . date( "Y-m-d", mktime( 0, 0, 0, date( "m" ), date( "d" ) + 7, date( "Y" ) ) ) . "'";
				}
				else {
					if ($_REQUEST["view"] == "mineincomplete") {
						$where = "completed=0  AND adminid=" . $_SESSION["adminid"];
					}
					else {
						if ($_REQUEST["view"] == "mine") {
							$where = "adminid=" . $_SESSION["adminid"];
						}
						else {
							if (( $_REQUEST["view"] == "user" && !empty( $_REQUEST["userid"] ) )) {
								$where = "userid=" . (int)trim( $_REQUEST["userid"] );
							}
							else {
								if (( $_REQUEST["view"] == "ticket" && !empty( $_REQUEST["ticketid"] ) )) {
									$where = "ticketids LIKE '%" . get_query_val( "tbltickets", "tid", array( "id" => (int)trim( $_REQUEST["ticketid"] ) ) ) . "%'";
								}
								else {
									if ($_REQUEST["view"] == "closed") {
										$where = "completed=1";
									}
								}
							}
						}
					}
				}
			}
		}
	}


	if (( project_management_checkperm( "View Only Assigned Projects" ) && !project_management_checkperm( "View All Projects" ) )) {
		if ($where) {
			$where .= " AND adminid=" . (int)$_SESSION["adminid"];
		}
		else {
			$where = "adminid=" . (int)$_SESSION["adminid"];
		}
	}

	$numrows = get_query_val( "mod_project", "COUNT(id)", $where );
	$orderby = (in_array( $orderby, array( "title", "status", "created", "duedate", "lastmodified" ) ) ? $orderby : "");

	if (!$orderby) {
		$order = "";
	}

	$result = select_query( "mod_project", "id,title,status,created,duedate,adminid,lastmodified,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE id=mod_project.adminid) AS adminuser", $where, $orderby, $order, $page * $limit . ( "," . $limit ) );

	while ($data = mysql_fetch_array( $result )) {
		$projectid = $data["id"];
		$progressdata = project_management_tasksstatus( $projectid, $vars );
		$jquerycode .= "
$(function() {
	$( \"#progressbar" . $projectid . "\" ).progressbar({
		value: " . $progressdata["percent"] . "
	});
});";

		if (( ( ( $q || $_REQUEST["view"] == "ticket" ) || $_REQUEST["view"] == "user" ) && $numrows == 1 )) {
			header( "Location: " . $modulelink . "&m=view&projectid=" . $projectid );
			exit();
		}

		$title = $data["title"];
		$status = $data["status"];
		$adminid = $data["adminid"];
		$adminuser = $data["adminuser"];
		$created = $data["created"];
		$duedate = $data["duedate"];
		$lastmodified = $data["lastmodified"];
		$daysleft = project_management_daysleft( $duedate, $vars );
		$created = fromMySQLDate( $created );
		$duedate = fromMySQLDate( $duedate );
		$lastmodified = fromMySQLDate( $lastmodified, true );
		$show_project = false;

		if (project_management_check_viewproject( $projectid )) {
			$show_project = true;
		}

		$title = ($show_project ? "<a href=\"" . str_replace( "m=overview", "m=view", $modulelink ) . "&projectid=" . $projectid . "\">" . $title . "</a>" : $title);

		if (!$adminuser) {
			$adminuser = "-";
		}

		$editprojecthtml = ($show_project ? "<a href=\"" . str_replace( "m=overview", "m=view", $modulelink ) . "&projectid=" . $projectid . "\"><img src=\"images/edit.gif\" border=\"0\" /></a>" : "");
		$deleteprojecthtml = (project_management_checkperm( "Delete Projects" ) ? "<a href=\"#\" onclick=\"doDelete('" . $projectid . "');return false\"><img src=\"images/delete.gif\" border=\"0\" /></a>" : "");
		$tabledata[] = array( "<div align=\"left\">" . $title . "</div>", $adminuser, $status, $created, $duedate, "<div id=\"progressbar" . $projectid . "\"></div>", $daysleft, $lastmodified, $editprojecthtml, $deleteprojecthtml );
	}

	echo $aInt->sortableTable( array( array( "title", $vars["_lang"]["projectname"] ), $vars["_lang"]["assignedto"], array( "status", $vars["_lang"]["status"] ), array( "created", $vars["_lang"]["created"] ), array( "duedate", $vars["_lang"]["duedate"] ), array( "progress", $vars["_lang"]["projectprogress"] ), array( "duedate", $vars["_lang"]["daysleft"] ), array( "lastmodified", $vars["_lang"]["lastmodified"] ), "", "" ), $tabledata );
}

echo "
</td><td width=\"1%\"></td><td width=\"19%\" valign=\"top\">";

if (project_management_checkperm( "View Recent Activity" )) {
	echo "<div align=\"center\"><b>" . $vars["_lang"]["recentactivity"] . "</b></div>";
	$result = select_query( "mod_projectlog", "mod_projectlog.*,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE tbladmins.id=mod_projectlog.adminid) AS admin,(SELECT title FROM mod_project WHERE mod_project.id=mod_projectlog.projectid) AS projectname, (SELECT adminid FROM mod_project WHERE mod_project.id=mod_projectlog.projectid) as assignedadminid", "", "id", "DESC", "0,10" );
	$projectid = "";

	while ($data = mysql_fetch_array( $result )) {
		$date = $data["date"];
		$projectid = $data["projectid"];
		$projectname = (project_management_check_viewproject( $projectid ) ? "<a href=\"" . $modulelink . "&m=view&projectid=" . $projectid . "\">" . $data["projectname"] . "</a>" : $data["projectname"]);
		$msg = $data["msg"];
		$admin = $data["admin"];
		$date = fromMySQLDate( $date, true );

		if (project_management_checkperm( "View Projects" )) {
			echo "<div class=\"recentactivity\" onclick=\"window.location='" . $modulelink . "&m=view&projectid=" . $projectid . "'\"><div class=\"title\"><a href=\"" . $modulelink . "&m=view&projectid=" . $projectid . "\">" . $projectname . "</a></div><div class=\"desc\">" . $msg . "</div><div style=\"float:left;\" class=\"small\">" . $admin . "</div><div style=\"float:right;\" class=\"small\">" . $date . "</div><div style=\"clear:both;\"></div></div>";
		}

		echo "<div class=\"recentactivity\"><div class=\"title\">" . $projectname . "</div><div class=\"desc\">" . $msg . "</div><div style=\"float:left;\" class=\"small\">" . $admin . "</div><div style=\"float:right;\" class=\"small\">" . $date . "</div><div style=\"clear:both;\"></div></div>";
	}

	echo "<div align=\"right\"><a href=\"" . $modulelink . "&m=activity\">View More &raquo;</a> &nbsp;&nbsp;&nbsp;</div>";
}
else {
	if (!project_management_checkperm( "View Recent Activity" )) {
		echo "<div class=\"recentactivity\"><div class=\"desc\" align=\"center\"><br />Welcome to the<br /><strong>Project Management Addon</strong> for WHMCS!<br /><br />Please click on <strong>Create New Project</strong> on the menu bar above to begin creating your ";
	}


	if (!$projectid) {
		echo "first ";
	}

	echo "project...<br /><br /><br /></div></div>";
}

echo "</td></tr></table>";
?>