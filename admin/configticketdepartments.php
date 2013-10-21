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
$aInt = new WHMCS_Admin("Configure Support Departments");
$aInt->title = $aInt->lang("supportticketdepts", "supportticketdeptstitle");
$aInt->sidebar = "config";
$aInt->icon = "logs";
$aInt->helplink = "Support Departments";

if ($sub == "add") {
	check_token("WHMCS.admin.default");

	if ($email == "") {
		infoBox($aInt->lang("global", "validationerror"), $aInt->lang("supportticketdepts", "emailreqdfordept"));
		$action = "add";
	}


	if ($name == "") {
		infoBox($aInt->lang("global", "validationerror"), $aInt->lang("supportticketdepts", "namereqdfordept"));
		$action = "add";
	}


	if (!$infobox) {
		$result = select_query("tblticketdepartments", "", "", "order", "DESC");
		$data = mysql_fetch_array($result);
		$order = $data['order'];
		++$order;
		$id = insert_query("tblticketdepartments", array("name" => $name, "description" => html_entity_decode($description), "email" => trim($email), "clientsonly" => $clientsonly, "piperepliesonly" => $piperepliesonly, "noautoresponder" => $noautoresponder, "hidden" => $hidden, "order" => $order, "host" => trim($host), "port" => trim($port), "login" => trim($login), "password" => encrypt(trim(html_entity_decode($password)))));
		$result = select_query("tbladmins", "id,supportdepts", array("disabled" => "0"));

		while ($data = mysql_fetch_array($result)) {
			$deptadminid = $data[0];
			$supportdepts = $data[1];
			$supportdepts = explode(",", $supportdepts);

			if (in_array($deptadminid, $admins)) {
				if (!in_array($id, $supportdepts)) {
					$supportdepts[] = $id;
				}
			}
			else {
				if (in_array($id, $supportdepts)) {
					$supportdepts = array_diff($supportdepts, array($id));
				}
			}

			update_query("tbladmins", array("supportdepts" => implode(",", $supportdepts)), array("id" => $deptadminid));
		}

		redir("createsuccess=1");
	}
}


if ($sub == "save") {
	check_token("WHMCS.admin.default");

	if ($email == "") {
		infoBox($aInt->lang("global", "validationerror"), $aInt->lang("supportticketdepts", "emailreqdfordept"));
		$action = "edit";
	}


	if ($name == "") {
		infoBox($aInt->lang("global", "validationerror"), $aInt->lang("supportticketdepts", "namereqdfordept"));
		$action = "edit";
	}

	$disabled[] = ($disabled ? 1 : 0);

	if (!$infobox) {
		update_query("tblticketdepartments", array("name" => $name, "description" => html_entity_decode($description), "email" => trim($email), "clientsonly" => $clientsonly, "piperepliesonly" => $piperepliesonly, "noautoresponder" => $noautoresponder, "hidden" => $hidden, "host" => trim($host), "port" => trim($port), "login" => trim($login), "password" => encrypt(trim(html_entity_decode($password)))), array("id" => $id));
		$result = select_query("tbladmins", "id,supportdepts", "");

		while ($data = mysql_fetch_array($result)) {
			$deptadminid = $data[0];
			$supportdepts = $data[1];
			$supportdepts = explode(",", $supportdepts);

			if (in_array($deptadminid, $admins)) {
				if (!in_array($id, $supportdepts)) {
					$supportdepts[] = $id;
				}
			}
			else {
				if (in_array($id, $supportdepts)) {
					$supportdepts = array_diff($supportdepts, array($id));
				}
			}

			update_query("tbladmins", array("supportdepts" => implode(",", $supportdepts), "disabled" => $disabled), array("id" => $deptadminid));
		}


		if ($customfieldname) {
			foreach ($customfieldname as $fid => $value) {
				update_query("tblcustomfields", array("fieldname" => $value, "fieldtype" => $customfieldtype[$fid], "description" => $customfielddesc[$fid], "fieldoptions" => $customfieldoptions[$fid], "regexpr" => html_entity_decode($customfieldregexpr[$fid]), "adminonly" => $customadminonly[$fid], "required" => $customrequired[$fid], "showorder" => $customshoworder[$fid], "sortorder" => $customsortorder[$fid]), array("id" => $fid));
			}
		}


		if ($addfieldname) {
			insert_query("tblcustomfields", array("type" => "support", "relid" => $id, "fieldname" => $addfieldname, "fieldtype" => $addfieldtype, "description" => $addcfdesc, "fieldoptions" => $addfieldoptions, "regexpr" => html_entity_decode($addregexpr), "adminonly" => $addadminonly, "required" => $addrequired, "showorder" => $addshoworder, "sortorder" => $addsortorder));
		}

		redir("savesuccess=1");
	}
}


if ($sub == "delete") {
	check_token("WHMCS.admin.default");
	$result = select_query("tblticketdepartments", "", array("id" => $id));
	$data = mysql_fetch_array($result);
	$order = $data['order'];
	update_query("tblticketdepartments", array("order" => "-1"), array("`order`" => $order));
	delete_query("tblticketdepartments", array("id" => $id));
	$result = select_query("tblticketdepartments", "min(id) as id", array());
	$data = mysql_fetch_array($result);
	$newdeptid = $data['id'];
	update_query("tbltickets", array("did" => $newdeptid), array("did" => $id));
	delete_query("tblcustomfields", array("type" => "support", "relid" => $id));
	full_query("DELETE FROM tblcustomfieldsvalues WHERE fieldid NOT IN (SELECT id FROM tblcustomfields)");
	redir("delsuccess=1");
}


if ($sub == "deletecustomfield") {
	check_token("WHMCS.admin.default");
	delete_query("tblcustomfields", array("id" => $id));
	delete_query("tblcustomfieldsvalues", array("fieldid" => $id));
	redir("savesuccess=1");
}


if ($sub == "moveup") {
	$result = select_query("tblticketdepartments", "", array("`order`" => $order));
	$data = mysql_fetch_array($result);
	$premid = $data['id'];
	$order1 = $order - 1;
	update_query("tblticketdepartments", array("order" => $order), array("`order`" => $order1));
	update_query("tblticketdepartments", array("order" => $order1), array("id" => $premid));
	redir();
}


if ($sub == "movedown") {
	$result = select_query("tblticketdepartments", "", array("`order`" => $order));
	$data = mysql_fetch_array($result);
	$premid = $data['id'];
	$order1 = $order + 1;
	update_query("tblticketdepartments", array("order" => $order), array("`order`" => $order1));
	update_query("tblticketdepartments", array("order" => $order1), array("id" => $premid));
	redir();
}

ob_start();

if ($createsuccess) {
	infoBox($aInt->lang("supportticketdepts", "deptaddsuccess"), $aInt->lang("supportticketdepts", "deptaddsuccessdesc"));
}


if ($savesuccess) {
	infoBox($aInt->lang("supportticketdepts", "changessavesuccess"), $aInt->lang("supportticketdepts", "changessavesuccessdesc"));
}


if ($delsuccess) {
	infoBox($aInt->lang("global", "success"), "The selected support department was deleted successfully");
}

echo $infobox;

if ($action == "") {
	$jscode = "function doDelete(id) {
if (confirm(\"" . addslashes($aInt->lang("supportticketdepts", "delsuredept")) . "\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?sub=delete&id='+id+'" . generate_token("link") . "';
}}";
	echo "
<p>";
	echo $aInt->lang("supportticketdepts", "supportticketdeptsconfigheredesc");
	echo "</p>

<div class=\"contentbox\">
";
	echo $aInt->lang("supportticketdepts", "ticketimportusingef");
	echo ":<br><input type=\"text\" size=\"100\" value=\" | php -q ";
	$pos = strrpos($_SERVER['SCRIPT_FILENAME'], "/");
	$str = substr($_SERVER['SCRIPT_FILENAME'], 0, $pos);
	$pos = strrpos($str, "/");
	$str = substr($str, 0, $pos);
	echo $str;
	echo "/pipe/pipe.php\"><br><b>";
	echo $aInt->lang("global", "or");
	echo "</b><br>
";
	echo $aInt->lang("supportticketdepts", "ticketimportusingpop3imap");
	echo ":<br><input type=\"text\" size=\"100\" value=\"*/5 * * * * php -q ";
	echo $str;
	echo "/pipe/pop.php\">
</div>

<p>";
	echo "<s";
	echo "trong>";
	echo $aInt->lang("fields", "options");
	echo ":</strong> <a href=\"";
	echo $PHP_SELF;
	echo "?action=add\">";
	echo $aInt->lang("supportticketdepts", "addnewdept");
	echo "</a></p>

";
	$result = select_query("tblticketdepartments", "", "", "order", "DESC");
	$data = mysql_fetch_array($result);
	$lastorder = $data['order'];
	$aInt->sortableTableInit("nopagination");
	$result = select_query("tblticketdepartments", "", "", "order", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$name = $data['name'];
		$description = $data['description'];
		$email = $data['email'];
		$hidden = $data['hidden'];
		$order = $data['order'];

		if ($hidden == "on") {
			$hidden = $aInt->lang("global", "yes");
		}
		else {
			$hidden = $aInt->lang("global", "no");
		}


		if ($order != "1") {
			$moveup = "<a href=\"" . $PHP_SELF . "?sub=moveup&order=" . $order . "\"><img src=\"images/moveup.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("products", "navmoveup") . "\"></a>";
		}
		else {
			$moveup = "";
		}


		if ($order != $lastorder) {
			$movedown = "<a href=\"" . $PHP_SELF . "?sub=movedown&order=" . $order . "\"><img src=\"images/movedown.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("products", "navmovedown") . "\"></a>";
		}
		else {
			$movedown = "";
		}

		$tabledata[] = array($name, $description, $email, $hidden, $moveup, $movedown, "<a href=\"" . $PHP_SELF . "?action=edit&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
	}

	echo $aInt->sortableTable(array($aInt->lang("supportticketdepts", "deptname"), $aInt->lang("fields", "description"), $aInt->lang("supportticketdepts", "deptemail"), $aInt->lang("global", "hidden"), "", "", "", ""), $tabledata);
}
else {
	if ($action == "edit") {
		if (!$infobox) {
			$result = select_query("tblticketdepartments", "", array("id" => $id));
			$data = mysql_fetch_array($result);
			$name = $data['name'];
			$description = $data['description'];
			$email = $data['email'];
			$clientsonly = $data['clientsonly'];
			$piperepliesonly = $data['piperepliesonly'];
			$noautoresponder = $data['noautoresponder'];
			$hidden = $data['hidden'];
			$host = $data['host'];
			$port = $data['port'];
			$login = $data['login'];
			$password = decrypt($data['password']);
		}

		$jscode = "function doDelete(id) {
if (confirm(\"" . addslashes($aInt->lang("supportticketdepts", "delsurefielddata")) . "\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?sub=deletecustomfield&id='+id+'" . generate_token("link") . "';
}}";
		echo "
<h2>";
		echo $aInt->lang("supportticketdepts", "editdept");
		echo "</h2>

<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?sub=save\">
<input type=\"hidden\" name=\"id\" value=\"";
		echo $id;
		echo "\">

";
		echo $aInt->Tabs(array("Details", "Custom Fields"));
		echo "
<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
		echo $aInt->lang("supportticketdepts", "deptname");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" size=\"25\" value=\"";
		echo $name;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "description");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"description\" size=\"50\" value=\"";
		echo $description;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketdepts", "deptemail");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"email\" size=\"40\" value=\"";
		echo $email;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketdepts", "assignedadmins");
		echo "</td><td class=\"fieldarea\">
";
		$result = select_query("tbladmins", "", "", "username", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$supportdepts = $data['supportdepts'];
			$supportdepts = explode(",", $supportdepts);
			echo "<label><input type=\"checkbox\" name=\"admins[]\" value=\"" . $data['id'] . "\"";

			if (in_array($id, $supportdepts)) {
				echo " checked";
			}

			echo " /> ";

			if ($data['disabled'] == 1) {
				echo "<span class=\"disabledtext\">";
			}

			echo $data['username'] . " (" . trim($data['firstname'] . " " . $data['lastname']) . ")";

			if ($data['disabled'] == 1) {
				echo " - " . $aInt->lang("global", "disabled") . "</span>";
			}

			echo "</label><br />";
		}

		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketdepts", "clientsonly");
		echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"clientsonly\"";

		if ($clientsonly == "on") {
			echo " checked";
		}

		echo "> ";
		echo $aInt->lang("supportticketdepts", "clientsonlydesc");
		echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketdepts", "piperepliesonly");
		echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"piperepliesonly\"";

		if ($piperepliesonly == "on") {
			echo " checked";
		}

		echo "> ";
		echo $aInt->lang("supportticketdepts", "ticketsclientareaonlydesc");
		echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketdepts", "noautoresponder");
		echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"noautoresponder\"";

		if ($noautoresponder == "on") {
			echo " checked";
		}

		echo "> ";
		echo $aInt->lang("supportticketdepts", "noautoresponderdesc");
		echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("global", "hidden");
		echo "?</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"hidden\"";

		if ($hidden == "on") {
			echo " checked";
		}

		echo "> ";
		echo $aInt->lang("supportticketdepts", "hiddendesc");
		echo "</label></td></tr>
</table>
<p style=\"text-align:left;\"><b>";
		echo $aInt->lang("supportticketdepts", "pop3importconfigtitle");
		echo "</b> ";
		echo $aInt->lang("supportticketdepts", "pop3importconfigdesc");
		echo "</p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
		echo $aInt->lang("fields", "hostname");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"host\" size=\"40\" value=\"";
		echo $host;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketdepts", "pop3port");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"port\" size=\"10\" value=\"";
		echo $port;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketdepts", "pop3user");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"login\" size=\"40\" value=\"";
		echo $login;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("supportticketdepts", "pop3pass");
		echo "</td><td class=\"fieldarea\"><input type=\"password\" name=\"password\" size=\"20\" value=\"";
		echo $password;
		echo "\"></td></tr>
</table>

  </div>
</div>
<div id=\"tab1box\" class=\"tabbox\">
  <div id=\"tab_content\">

";
		$result = select_query("tblcustomfields", "", array("type" => "support", "relid" => $id), "id", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$fid = $data['id'];
			$fieldname = $data['fieldname'];
			$fieldtype = $data['fieldtype'];
			$description = $data['description'];
			$fieldoptions = $data['fieldoptions'];
			$regexpr = $data['regexpr'];
			$adminonly = $data['adminonly'];
			$required = $data['required'];
			$sortorder = $data['sortorder'];
			echo "<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=100 class=\"fieldlabel\">";
			echo $aInt->lang("customfields", "fieldname");
			echo "</td><td class=\"fieldarea\"><table width=\"98%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td><input type=\"text\" name=\"customfieldname[";
			echo $fid;
			echo "]\" value=\"";
			echo $fieldname;
			echo "\" size=\"30\"></td><td align=\"right\">";
			echo $aInt->lang("customfields", "order");
			echo "<input type=\"text\" name=\"customsortorder[";
			echo $fid;
			echo "]\" value=\"";
			echo $sortorder;
			echo "\" size=\"5\"></td></tr></table></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("customfields", "fieldtype");
			echo "</td><td class=\"fieldarea\">";
			echo "<s";
			echo "elect name=\"customfieldtype[";
			echo $fid;
			echo "]\">
<option value=\"text\"";

			if ($fieldtype == "text") {
				echo " selected";
			}

			echo ">";
			echo $aInt->lang("customfields", "typetextbox");
			echo "</option>
<option value=\"link\"";

			if ($fieldtype == "link") {
				echo " selected";
			}

			echo ">";
			echo $aInt->lang("customfields", "typelink");
			echo "</option>
<option value=\"password\"";

			if ($fieldtype == "password") {
				echo " selected";
			}

			echo ">";
			echo $aInt->lang("customfields", "typepassword");
			echo "</option>
<option value=\"dropdown\"";

			if ($fieldtype == "dropdown") {
				echo " selected";
			}

			echo ">";
			echo $aInt->lang("customfields", "typedropdown");
			echo "</option>
<option value=\"tickbox\"";

			if ($fieldtype == "tickbox") {
				echo " selected";
			}

			echo ">";
			echo $aInt->lang("customfields", "typetickbox");
			echo "</option>
<option value=\"textarea\"";

			if ($fieldtype == "textarea") {
				echo " selected";
			}

			echo ">";
			echo $aInt->lang("customfields", "typetextarea");
			echo "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("fields", "description");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"customfielddesc[";
			echo $fid;
			echo "]\" value=\"";
			echo $description;
			echo "\" size=\"60\"> ";
			echo $aInt->lang("customfields", "descriptioninfo");
			echo "</td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("customfields", "validation");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"customfieldregexpr[";
			echo $fid;
			echo "]\" value=\"";
			echo $regexpr;
			echo "\" size=\"60\"> ";
			echo $aInt->lang("customfields", "validationinfo");
			echo "</td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("customfields", "selectoptions");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"customfieldoptions[";
			echo $fid;
			echo "]\" value=\"";
			echo $fieldoptions;
			echo "\" size=\"60\"> ";
			echo $aInt->lang("customfields", "selectoptionsinfo");
			echo "</td></tr>
<tr><td class=\"fieldlabel\"></td><td class=\"fieldarea\"><table width=\"98%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td><label><input type=\"checkbox\" name=\"customadminonly[";
			echo $fid;
			echo "]\"";

			if ($adminonly == "on") {
				echo " checked";
			}

			echo "> ";
			echo $aInt->lang("customfields", "adminonly");
			echo "</label> <label><input type=\"checkbox\" name=\"customrequired[";
			echo $fid;
			echo "]\"";

			if ($required == "on") {
				echo " checked";
			}

			echo "> ";
			echo $aInt->lang("customfields", "requiredfield");
			echo "</label></td><td align=\"right\"><a href=\"#\" onClick=\"doDelete('";
			echo $fid;
			echo "');return false\">";
			echo $aInt->lang("customfields", "deletefield");
			echo "</a></td></tr></table></td></tr>
</table><br>
";
		}

		echo "<b>";
		echo $aInt->lang("customfields", "addfield");
		echo "</b><br><br>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=100 class=\"fieldlabel\">";
		echo $aInt->lang("customfields", "fieldname");
		echo "</td><td class=\"fieldarea\"><table width=\"98%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td><input type=\"text\" name=\"addfieldname\" size=\"30\"></td><td align=\"right\">";
		echo $aInt->lang("customfields", "order");
		echo " <input type=\"text\" name=\"addsortorder\" size=\"5\" value=\"0\"></td></tr></table></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("customfields", "fieldtype");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"addfieldtype\">
<option value=\"text\">";
		echo $aInt->lang("customfields", "typetextbox");
		echo "</option>
<option value=\"link\">";
		echo $aInt->lang("customfields", "typelink");
		echo "</option>
<option value=\"password\">";
		echo $aInt->lang("customfields", "typepassword");
		echo "</option>
<option value=\"dropdown\">";
		echo $aInt->lang("customfields", "typedropdown");
		echo "</option>
<option value=\"tickbox\">";
		echo $aInt->lang("customfields", "typetickbox");
		echo "</option>
<option value=\"textarea\">";
		echo $aInt->lang("customfields", "typetextarea");
		echo "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "description");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"addcfdesc\" size=\"60\"> ";
		echo $aInt->lang("customfields", "descriptioninfo");
		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("customfields", "validation");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"addregexpr\" size=\"60\"> ";
		echo $aInt->lang("customfields", "validationinfo");
		echo "</td></tr>
<tr><td class=\"fieldlabel\">Select Options</td><td class=\"fieldarea\"><input type=\"text\" name=\"addfieldoptions\" size=\"60\"> ";
		echo $aInt->lang("customfields", "selectoptionsinfo");
		echo "</td></tr>
<tr><td class=\"fieldlabel\"></td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"addadminonly\"> ";
		echo $aInt->lang("customfields", "adminonly");
		echo "</label> <label><input type=\"checkbox\" name=\"addrequired\"> ";
		echo $aInt->lang("customfields", "requiredfield");
		echo "</label></td></tr>
</table>

  </div>
</div>

<p align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("global", "savechanges");
		echo "\" class=\"button\"> <input type=\"button\" value=\"";
		echo $aInt->lang("global", "cancel");
		echo "\" onClick=\"window.location='";
		echo $PHP_SELF;
		echo "'\" class=\"button\"></p>

</form>

";
	}
}


if ($action == "add") {
	if ($port == "") {
		$port = "110";
	}

	echo "
<h2>";
	echo $aInt->lang("supportticketdepts", "addnewdept");
	echo "</h2>

<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "?sub=add\" autocomplete=\"off\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
	echo $aInt->lang("supportticketdepts", "deptname");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" size=\"25\" value=\"";
	echo $name;
	echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("fields", "description");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"description\" size=\"50\" value=\"";
	echo $description;
	echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("supportticketdepts", "deptemail");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"email\" size=\"40\" value=\"";
	echo $email;
	echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("supportticketdepts", "assignedadmins");
	echo "</td><td class=\"fieldarea\">
";
	$result = select_query("tbladmins", "", "", "username", "ASC");

	while ($data = mysql_fetch_array($result)) {
		echo "<label><input type=\"checkbox\" name=\"admins[]\" value=\"" . $data['id'] . "\"";
		echo " /> ";

		if ($data['disabled'] == 1) {
			echo "<span class=\"disabledtext\">";
		}

		echo $data['username'] . " (" . $data['firstname'] . " " . $data['lastname'] . ")";

		if ($data['disabled'] == 1) {
			echo " - " . $aInt->lang("global", "disabled") . "</span>";
		}

		echo "</label><br />";
	}

	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("supportticketdepts", "clientsonly");
	echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"clientsonly\"";

	if ($clientsonly == "on") {
		echo " checked";
	}

	echo "> ";
	echo $aInt->lang("supportticketdepts", "clientsonlydesc");
	echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("supportticketdepts", "piperepliesonly");
	echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"piperepliesonly\"";

	if ($piperepliesonly == "on") {
		echo " checked";
	}

	echo "> ";
	echo $aInt->lang("supportticketdepts", "ticketsclientareaonlydesc");
	echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("supportticketdepts", "noautoresponder");
	echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"noautoresponder\"";

	if ($noautoresponder == "on") {
		echo " checked";
	}

	echo "> ";
	echo $aInt->lang("supportticketdepts", "noautoresponderdesc");
	echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("global", "hidden");
	echo "?</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"hidden\"";

	if ($hidden == "on") {
		echo " checked";
	}

	echo "> ";
	echo $aInt->lang("supportticketdepts", "hiddendesc");
	echo "</label></td></tr>
</table>
<p><b>";
	echo $aInt->lang("supportticketdepts", "pop3importconfigtitle");
	echo "</b> ";
	echo $aInt->lang("supportticketdepts", "pop3importconfigdesc");
	echo "</p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
	echo $aInt->lang("fields", "hostname");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"host\" size=\"40\" value=\"";
	echo $host;
	echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("supportticketdepts", "pop3port");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"port\" size=\"10\" value=\"";
	echo $port;
	echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("supportticketdepts", "pop3user");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"login\" size=\"40\" value=\"";
	echo $login;
	echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("supportticketdepts", "pop3pass");
	echo "</td><td class=\"fieldarea\"><input type=\"password\" name=\"password\" size=\"20\" value=\"";
	echo $password;
	echo "\"></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("supportticketdepts", "addnewdept");
	echo "\" class=\"button\"> <input type=\"button\" value=\"";
	echo $aInt->lang("global", "cancel");
	echo "\" onClick=\"window.location='";
	echo $PHP_SELF;
	echo "'\" class=\"button\"></p>

</form>

";
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>