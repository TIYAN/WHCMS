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
 **/

function staffboard_config() {
	$configarray = array("name" => "Staff Noticeboard", "version" => "1.1", "author" => "WHMCS", "description" => "Acts as a noticeboard within the WHMCS admin area providing a quick and easy way to communicate with all the staff via your WHMCS system");
	$fieldname = "Edit/Delete Permissions";
	$fielddesc = " (Select all you want to allow to edit and delete notes)";
	$result = select_query("tbladminroles", "", "", "id", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$configarray['fields']["masteradmin" . $data['id']] = array("FriendlyName" => $fieldname, "Type" => "yesno", "Description" => $data['name'] . $fielddesc);
		$fieldname = $fielddesc = "";
	}

	return $configarray;
}

function staffboard_activate() {
	full_query($query);
	$result = $query = "CREATE TABLE `mod_staffboard` (
        `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `note` TEXT NOT NULL,
        `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `color` VARCHAR(10) NOT NULL,
        `adminid` INT(10) NOT NULL,
        `x` INT(4) NOT NULL,
        `y` INT(4) NOT NULL,
        `z` INT(4) NOT NULL
       ) ; ";
}

function staffboard_deactivate() {
	full_query($query);
	$result = $query = "DROP TABLE `mod_staffboard`";
}

function staffboard_menubar($vars) {
	$modulelink = $vars['modulelink'];
	$links = array("" => "Notes", "refresh" => "Refresh");
	$tblinks = array("addoverlay" => "Add Note");
	echo "<style>
.lic_linksbar {
    padding:10px 25px 10px 25px;
    background-color:#666;
    font-weight:bold;
    font-size: 14px;
    color: #E3F0FD;
    margin: 0 0 15px 0;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    -o-border-radius: 5px;
    border-radius: 5px;
}
.lic_linksbar a {
    color: #fff;
    font-weight: normal;
}
.res_suboptions {
    background-color: #efefef;
    width: 250px;
    padding: 5px 10px 5px 10px;
    margin: 0 0 15px 15px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    -o-border-radius: 5px;
    border-radius: 5px;
}
</style>
<div class=\"lic_linksbar\">";
	$first = true;
	foreach ($links as $k => $v) {

		if (!$first) {
			echo " | ";
		}
		else {
			$first = false;
		}


		if ($_REQUEST['action'] != $k) {
			echo "<a href=\"" . $modulelink . "&action=" . $k . "\">";
		}

		echo $v;

		if ($_REQUEST['action'] != $k) {
			echo "</a>";
			continue;
		}
	}

	foreach ($tblinks as $k => $v) {

		if (!$first) {
			echo " | ";
		}
		else {
			$first = false;
		}


		if ($_REQUEST['action'] != $k) {
			echo "<a class=\"thickbox\" href=\"" . $modulelink . "&action=" . $k . "\">";
		}

		echo $v;

		if ($_REQUEST['action'] != $k) {
			echo "</a>";
			continue;
		}
	}

	echo "</div>";
}

function staffboard_output($vars) {
	$modulelink = $vars['modulelink'];
	$action = $_REQUEST['action'];
	$adminroleid = get_query_val("tbladmins", "roleid", array("id" => $_SESSION['adminid']));

	if ($action == "updatenote") {
		$noteid = $_REQUEST['noteid'];

		if (get_query_val("mod_staffboard", "adminid", array("id" => $noteid)) || $vars["masteradmin" . $adminroleid]) {
			update_query("mod_staffboard", array("color" => $_REQUEST['color'], "note" => $_REQUEST['note'], "date" => "now()"), array("id" => $noteid));
			redir("module=staffboard");
		}
	}
	else {
		if ($action == "updatepos") {
			update_query("mod_staffboard", array("x" => (int)$_REQUEST['x'], "y" => (int)$_REQUEST['y'], "z" => (int)$_REQUEST['z']), array("id" => (int)$_REQUEST['id']));
			exit();
		}
		else {
			if ($action == "createnote") {
				if (!isset($_POST['note']) || !in_array($_POST['color'], array("yellow", "green", "blue", "white", "pink", "purple"))) {
					exit("Please go back and try again.");
				}

				$result = select_query("mod_staffboard", "z", "", "z", "DESC");
				$row = mysql_fetch_assoc($result);
				$lastz = $row['z'];
				insert_query("mod_staffboard", array("note" => $_POST['note'], "date" => "now()", "color" => $_POST['color'], "x" => 0, "y" => 0, "z" => $lastz + 1, "adminid" => $_SESSION['adminid']));
				redir("module=staffboard");
			}
			else {
				if ($action == "deletenote") {
					$noteid = $_REQUEST['noteid'];

					if (get_query_val("mod_staffboard", "adminid", array("id" => $noteid)) || $vars["masteradmin" . $adminroleid]) {
						delete_query("mod_staffboard", array("id" => $_REQUEST['noteid']));
					}

					redir("module=staffboard");
				}
				else {
					if ($action == "refresh") {
						redir("module=staffboard");
					}
				}
			}
		}
	}

	echo "<link href=\"../modules/addons/staffboard/css/jquery.staffboard.css\" rel=\"stylesheet\" type=\"text/css\" />";
	echo "<script type=\"text/javascript\" src=\"../modules/addons/staffboard/js/jquery.staffboard.js\"></script>";
	staffboard_menubar($vars);
	$notes = "";
	$notes_result = select_query("mod_staffboard");

	while ($row = mysql_fetch_assoc($notes_result)) {
		$result = select_query("tbladmins", "firstname,lastname", array("id" => $row['adminid']));
		$data = mysql_fetch_assoc($result);
		$editlink = (($row['adminid'] == $_SESSION['adminid'] || $vars["masteradmin" . $adminroleid]) ? " <a class=\"thickbox\" href=\"" . $modulelink . "&action=editnote&noteid=" . $row['id'] . "\">Edit</a>" : "");
		$editlink .= ($vars["masteradmin" . $adminroleid] ? " | <a onclick=\"return confirm('Are you sure you want to delete this note?');\" href=\"" . $modulelink . "&action=deletenote&noteid=" . $row['id'] . "\">Delete</a>" : "");

		if ($row['id'] < $row['z']) {
			$zaxis = $row['z'];
		}
		else {
			$zaxis = $row['id'];
		}

		$notes .= "
		<div id=\"note" . $row['id'] . "\" class=\"note " . $row['color'] . "\" style=\"left:" . $row['x'] . "px;top:" . $row['y'] . "px;z-index:" . $zaxis . "\"><div style=\"height:95%\">" . nl2br($row['note']) . "</div>
			<div class=\"author\">" . $data['firstname'] . " " . $data['lastname'] . " on " . fromMySQLDate($row['date'], 1) . "<br />" . $editlink . "</div>
			<span class=\"data\">" . $row['id'] . "</span>
		</div>";
	}

	echo "
	<div id=\"main\">
		" . $notes . "
	</div>
	";
}


if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if ($_REQUEST['action'] == "editnote") {
	$notedata = mysql_fetch_assoc(select_query("mod_staffboard", "", array("id" => $_REQUEST['noteid'])));
	$content = "
<div style=\"padding:20px 5px;\">

	<h3 class=\"Title\">Edit note</h3>

	<div id=\"noteData\">
		<form action=\"" . $modulelink . "&action=updatenote\" method=\"post\" class=\"note-form\">
		<input type=\"hidden\" name=\"noteid\" value=\"" . $_REQUEST['noteid'] . "\" />

		<label for=\"note\">Text of the note</label><textarea name=\"note\" id=\"note\" class=\"pr-body\" cols=\"150\" rows=\"50\">" . $notedata['note'] . "</textarea>

		 <label>Color</label> <select name=\"color\">
		 	<option ";
	$content .= ($notedata['color'] == "yellow" ? " selected " : "");
	$content .= "value=\"yellow\">Yellow</option>
			<option ";
	$content .= ($notedata['color'] == "blue" ? " selected " : "");
	$content .= "value=\"blue\">Blue</option>
			<option ";
	$content .= ($notedata['color'] == "green" ? " selected " : "");
	$content .= "value=\"green\">Green</option>
			<option ";
	$content .= ($notedata['color'] == "white" ? " selected " : "");
	$content .= "value=\"white\">White</option>
			<option ";
	$content .= ($notedata['color'] == "pink" ? " selected " : "");
	$content .= "value=\"pink\">Pink</option>
			<option ";
	$content .= ($notedata['color'] == "purple" ? " selected " : "");
	$content .= "value=\"purple\">Purple</option></select>

		<input type=\"submit\" name=\"submit\" value=\"Save Note\" />

		</form>
	</div>
</div>
		";
	echo $content;
	exit();
}


if ($_REQUEST['action'] == "addoverlay") {
	echo "
<div style=\"padding:20px 5px;\">

<h3 class=\"Title\">Add a new note</h3>

<div id=\"noteData\">
	<form action=\"" . $modulelink . "&action=createnote\" method=\"post\" class=\"note-form\">

	<label for=\"note\">Text of the note</label><textarea name=\"note\" id=\"note\" class=\"pr-body\" cols=\"150\" rows=\"50\"></textarea>

	<label>Color</label> <select name=\"color\"><option value=\"yellow\">Yellow</option><option value=\"blue\">Blue</option><option value=\"green\">Green</option><option value=\"white\">White</option><option value=\"pink\">Pink</option><option value=\"purple\">Purple</option></select>
	<input type=\"submit\" name=\"submit\" value=\"Add Note\" />

	</form>
</div>
</div>
	";
	exit();
}

?>