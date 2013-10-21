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
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Configure Servers");
$aInt->title = "Servers";
$aInt->sidebar = "config";
$aInt->icon = "servers";
$aInt->helplink = "Servers";

if ($action == "delete") {
	check_token("WHMCS.admin.default");
	$numaccounts = get_query_val("tblhosting", "COUNT(*)", array("server" => $id));

	if (0 < $numaccounts) {
		header("Location: " . $_SERVER['PHP_SELF'] . "?deleteerror=true");
		exit();
	}
	else {
		run_hook("ServerDelete", array("serverid" => $id));
		delete_query("tblservers", array("id" => $id));
		header("Location: " . $_SERVER['PHP_SELF'] . "?deletesuccess=true");
		exit();
	}
}


if ($action == "deletegroup") {
	check_token("WHMCS.admin.default");
	delete_query("tblservergroups", array("id" => $id));
	delete_query("tblservergroupsrel", array("serverid" => $id));
	header("Location: " . $_SERVER['PHP_SELF'] . "?deletegroupsuccess=true");
	exit();
}


if ($action == "save") {
	check_token("WHMCS.admin.default");

	if ($id) {
		$result = select_query("tblservers", "active,type", array("id" => $id));
		$data = mysql_fetch_array($result);

		if ($type == $data['type']) {
			$active = $data['active'];
		}
		else {
			$active = "";
		}

		update_query("tblservers", array("name" => $name, "type" => $type, "ipaddress" => trim($ipaddress), "assignedips" => trim($assignedips), "hostname" => trim($hostname), "monthlycost" => trim($monthlycost), "noc" => $noc, "statusaddress" => trim($statusaddress), "nameserver1" => trim($nameserver1), "nameserver1ip" => trim($nameserver1ip), "nameserver2" => trim($nameserver2), "nameserver2ip" => trim($nameserver2ip), "nameserver3" => trim($nameserver3), "nameserver3ip" => trim($nameserver3ip), "nameserver4" => trim($nameserver4), "nameserver4ip" => trim($nameserver4ip), "nameserver5" => trim($nameserver5), "nameserver5ip" => trim($nameserver5ip), "maxaccounts" => trim($maxaccounts), "username" => trim($username), "password" => encrypt(trim($password)), "accesshash" => trim($accesshash), "secure" => $secure, "disabled" => $disabled, "active" => $active), array("id" => $id));
		run_hook("ServerEdit", array("serverid" => $id));
		header("Location: " . $_SERVER['PHP_SELF'] . "?savesuccess=true");
	}
	else {
		$result = select_query("tblservers", "id", array("type" => $type, "active" => "1"));
		$data = mysql_fetch_array($result);
		$active = ($data['id'] ? "" : "1");
		$newid = insert_query("tblservers", array("name" => $name, "type" => $type, "ipaddress" => trim($ipaddress), "assignedips" => trim($assignedips), "hostname" => trim($hostname), "monthlycost" => trim($monthlycost), "noc" => $noc, "statusaddress" => trim($statusaddress), "nameserver1" => trim($nameserver1), "nameserver1ip" => trim($nameserver1ip), "nameserver2" => trim($nameserver2), "nameserver2ip" => trim($nameserver2ip), "nameserver3" => trim($nameserver3), "nameserver3ip" => trim($nameserver3ip), "nameserver4" => trim($nameserver4), "nameserver4ip" => trim($nameserver4ip), "nameserver5" => trim($nameserver5), "nameserver5ip" => trim($nameserver5ip), "maxaccounts" => trim($maxaccounts), "username" => trim($username), "password" => encrypt(trim($password)), "accesshash" => trim($accesshash), "secure" => $secure, "active" => $active, "disabled" => $disabled));
		run_hook("ServerAdd", array("serverid" => $newid));
		header("Location: " . $_SERVER['PHP_SELF'] . "?createsuccess=true");
	}

	exit();
}


if ($action == "savegroup") {
	check_token("WHMCS.admin.default");

	if ($id) {
		update_query("tblservergroups", array("name" => $name, "filltype" => $filltype), array("id" => $id));
		delete_query("tblservergroupsrel", array("groupid" => $id));
	}
	else {
		$id = insert_query("tblservergroups", array("name" => $name, "filltype" => $filltype));
	}


	if ($selectedservers) {
		foreach ($selectedservers as $serverid) {
			insert_query("tblservergroupsrel", array("groupid" => $id, "serverid" => $serverid));
		}
	}

	header("Location: " . $_SERVER['PHP_SELF']);
	exit();
}

ob_start();

if ($action == "") {
	if ($sub == "enable") {
		update_query("tblservers", array("disabled" => "0"), array("id" => $id));
		infoBox($aInt->lang("configservers", "enabled"), $aInt->lang("configservers", "enableddesc"));
	}


	if ($sub == "disable") {
		update_query("tblservers", array("disabled" => "1"), array("id" => $id));
		infoBox($aInt->lang("configservers", "disabled"), $aInt->lang("configservers", "disableddesc"));
	}


	if ($sub == "makedefault") {
		$result = select_query("tblservers", "", array("id" => $id));
		$data = mysql_fetch_array($result);
		$type = $data['type'];
		update_query("tblservers", array("active" => ""), array("type" => $type));
		update_query("tblservers", array("active" => "1"), array("id" => $id));
		infoBox($aInt->lang("configservers", "defaultchange"), $aInt->lang("configservers", "defaultchangedesc"));
	}


	if ($createsuccess) {
		infoBox($aInt->lang("configservers", "addedsucessful"), $aInt->lang("configservers", "addedsucessfuldesc"));
	}


	if ($deletesuccess) {
		infoBox($aInt->lang("configservers", "delsuccessful"), $aInt->lang("configservers", "delsuccessfuldesc"));
	}


	if ($deletegroupsuccess) {
		infoBox($aInt->lang("configservers", "groupdelsucessful"), $aInt->lang("configservers", "groupdelsucessfuldesc"));
	}


	if ($deleteerror) {
		infoBox($aInt->lang("configservers", "error"), $aInt->lang("configservers", "errordesc"));
	}


	if ($savesuccess) {
		infoBox($aInt->lang("configservers", "changesuccess"), $aInt->lang("configservers", "changesuccessdesc"));
	}

	echo $infobox;
	$jscode = "function doDelete(id) {
if (confirm(\"" . $aInt->lang("configservers", "delserverconfirm") . "\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=delete&id='+id+'" . generate_token("link") . "';
}}
function doDeleteGroup(id) {
if (confirm(\"" . $aInt->lang("configservers", "delgroupconfirm") . "\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=deletegroup&id='+id+'" . generate_token("link") . "';
}}";
	echo "
<p>";
	echo $aInt->lang("configservers", "pagedesc");
	echo "</p>

<p><B>";
	echo $aInt->lang("fields", "options");
	echo ":</B> <a href=\"";
	echo $PHP_SELF;
	echo "?action=manage\">";
	echo $aInt->lang("configservers", "addnewserver");
	echo "</a> | <a href=\"";
	echo $PHP_SELF;
	echo "?action=managegroup\">";
	echo $aInt->lang("configservers", "createnewgroup");
	echo "</a></p>

";
	$modulesarray = array();
	$dh = opendir(ROOTDIR . "/modules/servers/");

	while (false !== $file = readdir($dh)) {
		if (is_file(ROOTDIR . ("/modules/servers/" . $file . "/" . $file . ".php"))) {
			$modulesarray[] = $file;
		}
	}

	closedir($dh);
	$aInt->sortableTableInit("nopagination");
	$result3 = select_query("tblservers", "DISTINCT type", "", "type", "ASC");

	while ($data = mysql_fetch_array($result3)) {
		$servertype = $data['type'];
		$tabledata[] = array("dividingline", ucfirst($servertype));
		$disableddata = array();
		$result = select_query("tblservers", "", array("type" => $data['type']), "name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$name = $data['name'];
			$ipaddress = $data['ipaddress'];
			$hostname = $data['hostname'];
			$maxaccounts = $data['maxaccounts'];
			$username = $data['username'];
			$password = decrypt($data['password']);
			$accesshash = $data['accesshash'];
			$secure = $data['secure'];
			$active = $data['active'];
			$type = $data['type'];
			$disabled = $data['disabled'];
			$active = ($active ? "*" : "");
			$result2 = select_query("tblhosting", "COUNT(*)", "server='" . $id . "' AND (domainstatus='Active' OR domainstatus='Suspended')");
			$data = mysql_fetch_array($result2);
			$numaccounts = $data[0];
			$percentuse = @round($numaccounts / $maxaccounts * 100, 0);
			$params = array();
			$params['serverip'] = $ipaddress;
			$params['serverhostname'] = $hostname;
			$params['serverusername'] = $username;
			$params['serverpassword'] = $password;
			$params['serversecure'] = $secure;
			$params['serveraccesshash'] = $accesshash;

			if (in_array($type, $modulesarray)) {
				if (!isValidforPath($type)) {
					exit("Invalid Server Module Name");
				}

				require_once "../modules/servers/" . $type . "/" . $type . ".php";
				$adminlogincode = (function_exists($type . "_AdminLink") ? call_user_func($type . "_AdminLink", $params) : "-");
			}
			else {
				$adminlogincode = $aInt->lang("global", "modulefilemissing");
			}


			if ($disabled) {
				$disableddata[] = array("<i>" . $name . " (" . $aInt->lang("emailtpls", "disabled") . ")</i>", "<i>" . $ipaddress . "</i>", "<i>" . $numaccounts . "/" . $maxaccounts . "</i>", "<i>" . $percentuse . "%</i>", $adminlogincode, "<div align=\"center\"><a href=\"" . $PHP_SELF . "?sub=enable&id=" . $id . "\" title=\"" . $aInt->lang("configservers", "enableserver") . "\"><img src=\"images/icons/disabled.png\"></a></div>", "<a href=\"" . $PHP_SELF . "?action=manage&id=" . $id . "\" title=\"" . $aInt->lang("global", "edit") . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Edit\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\" title=\"" . $aInt->lang("global", "delete") . "\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
			}

			$tabledata[] = array("<a href=\"" . $PHP_SELF . "?sub=makedefault&id=" . $id . "\" title=\"" . $aInt->lang("configservers", "defaultsignups") . ("\">" . $name . "</a> " . $active), $ipaddress, "" . $numaccounts . "/" . $maxaccounts, "" . $percentuse . "%", $adminlogincode, "<div align=\"center\"><a href=\"" . $PHP_SELF . "?sub=disable&id=" . $id . "\" title=\"" . $aInt->lang("configservers", "disableserver") . "\"><img src=\"images/icons/tick.png\"></a></div>", "<a href=\"" . $PHP_SELF . "?action=manage&id=" . $id . "\" title=\"" . $aInt->lang("global", "edit") . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\" title=\"" . $aInt->lang("global", "delete") . "\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
		}

		foreach ($disableddata as $data) {
			$tabledata[] = $data;
		}
	}

	echo $aInt->sortableTable(array($aInt->lang("configservers", "servername"), $aInt->lang("fields", "ipaddress"), $aInt->lang("configservers", "activeaccounts"), $aInt->lang("configservers", "usage"), " ", $aInt->lang("fields", "status"), "", ""), $tabledata);
	echo "
<h2>";
	echo $aInt->lang("configservers", "groups");
	echo "</h2>

<p>";
	echo $aInt->lang("configservers", "groupsdesc");
	echo "</p>

";
	$tabledata = "";
	$result = select_query("tblservergroups", "", "", "name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$name = $data['name'];
		$filltype = $data['filltype'];

		if ($filltype == 1) {
			$filltype = $aInt->lang("configservers", "addleast");
		}
		else {
			if ($filltype == 2) {
				$filltype = $aInt->lang("configservers", "fillactive");
			}
		}

		$servers = "";
		$result2 = select_query("tblservergroupsrel", "tblservers.name", array("groupid" => $id), "name", "ASC", "", "tblservers ON tblservers.id=tblservergroupsrel.serverid");

		while ($data = mysql_fetch_array($result2)) {
			$servers .= $data['name'] . ", ";
		}

		$servers = substr($servers, 0, 0 - 2);
		$tabledata[] = array($name, $filltype, $servers, "<a href=\"" . $PHP_SELF . "?action=managegroup&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDeleteGroup('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
	}

	echo $aInt->sortableTable(array($aInt->lang("configservers", "groupname"), $aInt->lang("fields", "filltype"), $aInt->lang("setup", "servers"), "", ""), $tabledata);
}
else {
	if ($action == "manage") {
		if ($id) {
			$result = select_query("tblservers", "", array("id" => $id));
			$data = mysql_fetch_array($result);
			$id = $data['id'];
			$type = $data['type'];
			$name = $data['name'];
			$ipaddress = $data['ipaddress'];
			$assignedips = $data['assignedips'];
			$hostname = $data['hostname'];
			$monthlycost = $data['monthlycost'];
			$noc = $data['noc'];
			$statusaddress = $data['statusaddress'];
			$nameserver1 = $data['nameserver1'];
			$nameserver1ip = $data['nameserver1ip'];
			$nameserver2 = $data['nameserver2'];
			$nameserver2ip = $data['nameserver2ip'];
			$nameserver3 = $data['nameserver3'];
			$nameserver3ip = $data['nameserver3ip'];
			$nameserver4 = $data['nameserver4'];
			$nameserver4ip = $data['nameserver4ip'];
			$nameserver5 = $data['nameserver5'];
			$nameserver5ip = $data['nameserver5ip'];
			$maxaccounts = $data['maxaccounts'];
			$username = $data['username'];
			$password = decrypt($data['password']);
			$accesshash = $data['accesshash'];
			$secure = $data['secure'];
			$active = $data['active'];
			$disabled = $data['disabled'];
			$managetitle = $aInt->lang("configservers", "editserver");
		}
		else {
			$managetitle = $aInt->lang("configservers", "addserver");

			if (!$maxaccounts) {
				$maxaccounts = "200";
			}
		}

		echo "<h2>" . $managetitle . "</h2>";
		echo "
<form method=\"post\" action=\"";
		echo $_SERVER['PHP_SELF'];
		echo "?action=save";

		if ($id) {
			echo "&id=" . $id;
		}

		echo "\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"23%\" class=\"fieldlabel\">";
		echo $aInt->lang("fields", "name");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" size=\"30\" value=\"";
		echo $name;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "hostname");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"hostname\" size=\"40\" value=\"";
		echo $hostname;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "ipaddress");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ipaddress\" size=\"20\" value=\"";
		echo $ipaddress;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "assignedips");
		echo "<br />";
		echo $aInt->lang("configservers", "assignedipsdesc");
		echo "</td><td class=\"fieldarea\"><textarea name=\"assignedips\" cols=\"60\" rows=\"8\">";
		echo $assignedips;
		echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "monthlycost");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"monthlycost\" size=\"10\" value=\"";
		echo $monthlycost;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "datacenter");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"noc\" size=\"30\" value=\"";
		echo $noc;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "maxaccounts");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"maxaccounts\" size=\"6\" value=\"";
		echo $maxaccounts;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "statusaddress");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"statusaddress\" size=\"60\" value=\"";
		echo $statusaddress;
		echo "\"><br>";
		echo $aInt->lang("configservers", "statusaddressdesc");
		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("general", "enabledisable");
		echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"disabled\" value=\"1\"";

		if ($disabled) {
			echo "checked";
		}

		echo "> ";
		echo $aInt->lang("configservers", "disableserver");
		echo "</td></tr>
</table>
<p><b>";
		echo $aInt->lang("configservers", "nameservers");
		echo "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"23%\" class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "primarynameserver");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"nameserver1\" size=\"40\" value=\"";
		echo $nameserver1;
		echo "\"> ";
		echo $aInt->lang("fields", "ipaddress");
		echo ": <input type=\"text\" name=\"nameserver1ip\" size=\"25\" value=\"";
		echo $nameserver1ip;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "secondarynameserver");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"nameserver2\" size=\"40\" value=\"";
		echo $nameserver2;
		echo "\"> ";
		echo $aInt->lang("fields", "ipaddress");
		echo ": <input type=\"text\" name=\"nameserver2ip\" size=\"25\" value=\"";
		echo $nameserver2ip;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "thirdnameserver");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"nameserver3\" size=\"40\" value=\"";
		echo $nameserver3;
		echo "\"> ";
		echo $aInt->lang("fields", "ipaddress");
		echo ": <input type=\"text\" name=\"nameserver3ip\" size=\"25\" value=\"";
		echo $nameserver3ip;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "fourthnameserver");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"nameserver4\" size=\"40\" value=\"";
		echo $nameserver4;
		echo "\"> ";
		echo $aInt->lang("fields", "ipaddress");
		echo ": <input type=\"text\" name=\"nameserver4ip\" size=\"25\" value=\"";
		echo $nameserver4ip;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "fifthnameserver");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"nameserver5\" size=\"40\" value=\"";
		echo $nameserver5;
		echo "\"> ";
		echo $aInt->lang("fields", "ipaddress");
		echo ": <input type=\"text\" name=\"nameserver5ip\" size=\"25\" value=\"";
		echo $nameserver5ip;
		echo "\"></td></tr>
</table>
<p><b>";
		echo $aInt->lang("configservers", "serverdetails");
		echo "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"23%\" class=\"fieldlabel\">";
		echo $aInt->lang("fields", "type");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"type\">";
		$modulesarray = array();
		$dh = opendir("../modules/servers/");

		while (false !== $file = readdir($dh)) {
			if (is_file("../modules/servers/" . $file . "/" . $file . ".php")) {
				$modulesarray[] = $file;
			}
		}

		closedir($dh);
		sort($modulesarray);
		foreach ($modulesarray as $module) {
			echo "<option value=\"" . $module . "\"";

			if ($module == $type) {
				echo " selected";
			}

			echo ">" . ucfirst($module) . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "username");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"username\" size=\"25\" value=\"";
		echo $username;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "password");
		echo "</td><td class=\"fieldarea\"><input type=\"password\" name=\"password\" size=\"25\" value=\"";
		echo $password;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "accesshash");
		echo "<br>";
		echo $aInt->lang("configservers", "accesshashdesc");
		echo "</td><td class=\"fieldarea\"><textarea name=\"accesshash\" cols=\"60\" rows=\"8\">";
		echo $accesshash;
		echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("configservers", "secure");
		echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"secure\"";

		if ($secure) {
			echo " checked";
		}

		echo "> ";
		echo $aInt->lang("configservers", "usessl");
		echo "</td></tr>
</table>
<p align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("global", "savechanges");
		echo "\" class=\"button\"></p>
</form>

";
	}
	else {
		if ($action == "managegroup") {
			if ($id) {
				$managetitle = $aInt->lang("configservers", "editgroup");
				$result = select_query("tblservergroups", "", array("id" => $id));
				$data = mysql_fetch_array($result);
				$id = $data['id'];
				$name = $data['name'];
				$filltype = $data['filltype'];
			}
			else {
				$managetitle = $aInt->lang("configservers", "newgroup");
				$filltype = "1";
			}

			echo "<h2>" . $managetitle . "</h2>";
			$jquerycode = "$(\"#serveradd\").click(function () {
  $(\"#serverslist option:selected\").appendTo(\"#selectedservers\");
  return false;
});
$(\"#serverrem\").click(function () {
  $(\"#selectedservers option:selected\").appendTo(\"#serverslist\");
  return false;
});";
			echo "
<form method=\"post\" action=\"";
			echo $_SERVER['PHP_SELF'];
			echo "?action=savegroup&id=";
			echo $id;
			echo "\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
			echo $aInt->lang("fields", "name");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" size=\"40\" value=\"";
			echo $name;
			echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("fields", "filltype");
			echo "</td><td class=\"fieldarea\"><input type=\"radio\" name=\"filltype\" value=\"1\"";

			if ($filltype == 1) {
				echo " checked";
			}

			echo "> ";
			echo $aInt->lang("configservers", "addleast");
			echo "<br /><input type=\"radio\" name=\"filltype\" value=\"2\"";

			if ($filltype == 2) {
				echo " checked";
			}

			echo "> ";
			echo $aInt->lang("configservers", "fillactive");
			echo "</td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("fields", "selectedservers");
			echo "</td><td class=\"fieldarea\"><table><td><td>";
			echo "<s";
			echo "elect size=\"10\" multiple=\"multiple\" id=\"serverslist\" style=\"width:200px;\">";
			$selectedservers = array();
			$result = select_query("tblservergroupsrel", "tblservers.id,tblservers.name,tblservers.disabled", array("groupid" => $id), "name", "ASC", "", "tblservers ON tblservers.id=tblservergroupsrel.serverid");

			while ($data = mysql_fetch_array($result)) {
				$id = $data['id'];
				$name = $data['name'];
				$disabled = $data['disabled'];

				if ($disabled) {
					$name .= " (" . $aInt->lang("emailtpls", "disabled") . ")";
				}

				$selectedservers[$id] = $name;
			}

			$result = select_query("tblservers", "", "", "name", "ASC");

			while ($data = mysql_fetch_array($result)) {
				$id = $data['id'];
				$name = $data['name'];
				$disabled = $data['disabled'];

				if ($disabled) {
					$name .= " (Disabled)";
				}


				if (!array_key_exists($id, $selectedservers)) {
					echo "<option value=\"" . $id . "\">" . $name . "</option>";
				}
			}

			echo "</select></td><td align=\"center\"><input type=\"button\" id=\"serveradd\" value=\"";
			echo $aInt->lang("global", "add");
			echo " &raquo;\"><br /><br /><input type=\"button\" id=\"serverrem\" value=\"&laquo; ";
			echo $aInt->lang("global", "remove");
			echo "\"></td><td>";
			echo "<s";
			echo "elect size=\"10\" multiple=\"multiple\" id=\"selectedservers\" name=\"selectedservers[]\" style=\"width:200px;\">";
			foreach ($selectedservers as $id => $name) {
				echo "<option value=\"" . $id . "\">" . $name . "</option>";
			}

			echo "</select></td></td></table></td></tr>
</table>
<p align=\"center\"><input type=\"submit\" value=\"";
			echo $aInt->lang("global", "savechanges");
			echo "\" onclick=\"$('#selectedservers *').attr('selected','selected')\" class=\"button\"></p>
</form>

";
		}
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->jquerycode = $jquerycode;
$aInt->display();
?>