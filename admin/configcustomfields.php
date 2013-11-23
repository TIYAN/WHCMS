<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Configure Custom Client Fields");
$aInt->title = $aInt->lang("customfields", "clienttitle");
$aInt->sidebar = "config";
$aInt->icon = "customfields";
$aInt->helplink = "Custom Fields";
$action = $whmcs->get_req_var("action");

if ($action == "save") {
	check_token("WHMCS.admin.default");

	if ($fieldname) {
		foreach ($fieldname as $fid => $value) {
			update_query("tblcustomfields", array("fieldname" => $value, "fieldtype" => $fieldtype[$fid], "description" => $description[$fid], "fieldoptions" => $fieldoptions[$fid], "regexpr" => html_entity_decode($regexpr[$fid]), "adminonly" => $adminonly[$fid], "required" => $required[$fid], "showorder" => $showorder[$fid], "showinvoice" => $showinvoice[$fid], "sortorder" => $sortorder[$fid]), array("id" => $fid));
		}
	}


	if ($addfieldname) {
		insert_query("tblcustomfields", array("type" => "client", "fieldname" => $addfieldname, "fieldtype" => $addfieldtype, "description" => $adddescription, "fieldoptions" => $addfieldoptions, "regexpr" => html_entity_decode($addregexpr), "adminonly" => $addadminonly, "required" => $addrequired, "showorder" => $addshoworder, "showinvoice" => $addshowinvoice, "sortorder" => $addsortorder));
	}

	redir("success=true");
}
else {
	if ($action == "delete") {
		check_token("WHMCS.admin.default");
		delete_query("tblcustomfields", array("id" => $id));
		delete_query("tblcustomfieldsvalues", array("fieldid" => $id));
		redir("deleted=true");
	}
}

$aInt->deleteJSConfirm("doDelete", "customfields", "delsure", $_SERVER['PHP_SELF'] . "?action=delete&id=");
ob_start();

if ($whmcs->get_req_var("success")) {
	infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("global", "changesuccessdesc"));
}

echo $infobox;
echo "
<p>";
echo $aInt->lang("customfields", "clientinfo");
echo "</p>
<form method=\"post\" action=\"";
echo $_SERVER['PHP_SELF'];
echo "?action=save\">
";
$result = select_query("tblcustomfields", "", array("type" => "client"), "sortorder` ASC,`id", "ASC");

while ($data = mysql_fetch_array($result)) {
	$fid = $data['id'];
	$fieldname = $data['fieldname'];
	$fieldtype = $data['fieldtype'];
	$description = $data['description'];
	$fieldoptions = $data['fieldoptions'];
	$regexpr = $data['regexpr'];
	$adminonly = $data['adminonly'];
	$required = $data['required'];
	$showorder = $data['showorder'];
	$showinvoice = $data['showinvoice'];
	$sortorder = $data['sortorder'];
	echo "<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=100 class=\"fieldlabel\">";
	echo $aInt->lang("customfields", "fieldname");
	echo "</td><td class=\"fieldarea\"><table width=\"98%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td><input type=\"text\" name=\"fieldname[";
	echo $fid;
	echo "]\" value=\"";
	echo $fieldname;
	echo "\" size=\"30\"></td><td align=\"right\">";
	echo $aInt->lang("customfields", "order");
	echo " <input type=\"text\" name=\"sortorder[";
	echo $fid;
	echo "]\" value=\"";
	echo $sortorder;
	echo "\" size=\"5\"></td></tr></table></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("customfields", "fieldtype");
	echo "</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"fieldtype[";
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
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"description[";
	echo $fid;
	echo "]\" value=\"";
	echo $description;
	echo "\" size=\"60\"> ";
	echo $aInt->lang("customfields", "descriptioninfo");
	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("customfields", "validation");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"regexpr[";
	echo $fid;
	echo "]\" value=\"";
	echo $regexpr;
	echo "\" size=\"60\"> ";
	echo $aInt->lang("customfields", "validationinfo");
	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("customfields", "selectoptions");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"fieldoptions[";
	echo $fid;
	echo "]\" value=\"";
	echo $fieldoptions;
	echo "\" size=\"60\"> ";
	echo $aInt->lang("customfields", "selectoptionsinfo");
	echo "</td></tr>
<tr><td class=\"fieldlabel\"></td><td class=\"fieldarea\"><table width=\"98%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td><input type=\"checkbox\" name=\"adminonly[";
	print $fid;
	echo "]\"";

	if ($adminonly == "on") {
		echo " checked";
	}

	echo "> ";
	echo $aInt->lang("customfields", "adminonly");
	echo " <input type=\"checkbox\" name=\"required[";
	echo $fid;
	echo "]\"";

	if ($required == "on") {
		echo " checked";
	}

	echo "> ";
	echo $aInt->lang("customfields", "requiredfield");
	echo " <input type=\"checkbox\" name=\"showorder[";
	echo $fid;
	echo "]\"";

	if ($showorder == "on") {
		echo " checked";
	}

	echo "> ";
	echo $aInt->lang("customfields", "orderform");
	echo " <input type=\"checkbox\" name=\"showinvoice[";
	echo $fid;
	echo "]\"";

	if ($showinvoice) {
		echo " checked";
	}

	echo "> ";
	echo $aInt->lang("customfields", "showinvoice");
	echo "</td><td align=\"right\"><a href=\"#\" onClick=\"doDelete('";
	echo $fid;
	echo "');return false\">";
	echo $aInt->lang("customfields", "deletefield");
	echo "</a></td></tr></table></td></tr>
</table>
<br>
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
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"adddescription\" size=\"60\"> ";
echo $aInt->lang("customfields", "descriptioninfo");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("customfields", "validation");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"addregexpr\" size=\"60\"> ";
echo $aInt->lang("customfields", "validationinfo");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("customfields", "selectoptions");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"addfieldoptions\" size=\"60\"> ";
echo $aInt->lang("customfields", "selectoptionsinfo");
echo "</td></tr>
<tr><td class=\"fieldlabel\"></td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"addadminonly\"> ";
echo $aInt->lang("customfields", "adminonly");
echo " <input type=\"checkbox\" name=\"addrequired\"> ";
echo $aInt->lang("customfields", "requiredfield");
echo " <input type=\"checkbox\" name=\"addshoworder\"> ";
echo $aInt->lang("customfields", "orderform");
echo " <input type=\"checkbox\" name=\"addshowinvoice\"> ";
echo $aInt->lang("customfields", "showinvoice");
echo "</td></tr>
</table>
<br>
<DIV ALIGN=\"center\"><INPUT TYPE=\"submit\" VALUE=\"";
echo $aInt->lang("global", "savechanges");
echo "\" class=\"button\"></DIV>
</form>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>