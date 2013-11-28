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

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Link Tracking");
$aInt->title = "Email Marketer";
$aInt->sidebar = "utilities";
$aInt->icon = "emailmarketer";
$aInt->helplink = "Email Marketer";

if ($action == "save") {
	check_token("WHMCS.admin.default");
	$settings = array("clientnumdays" => $clientnumdays, "clientsminactive" => $clientsminactive, "clientsmaxactive" => $clientsmaxactive, "clientemailtpl" => $clientemailtpl, "prodpids" => $prodpids, "prodstatus" => $prodstatus, "prodnumdays" => $prodnumdays, "prodfiltertype" => $prodfiltertype, "prodexcludepid" => $prodexcludepid, "prodexcludeaid" => $prodexcludeaid, "prodemailtpl" => $prodemailtpl);
	$settings = serialize($settings);

	if ($id) {
		update_query("tblemailmarketer", array("name" => $name, "type" => $type, "settings" => $settings, "disable" => $disable, "marketing" => $marketing), array("id" => $id));
	}
	else {
		insert_query("tblemailmarketer", array("name" => $name, "type" => $type, "settings" => $settings, "disable" => $disable, "marketing" => $marketing));
	}

	redir();
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	delete_query("tblemailmarketer", array("id" => $id));
	redir();
}

ob_start();

if (!$action) {
	$aInt->deleteJSConfirm("doDelete", "global", "deleteconfirm", "?action=delete&id=");
	echo "
<p>The email marketer tool allows you to schedule automated emails to be sent out to your clients when certain events and/or criteria are met.</p>

<p><b>Options:</b> <a href=\"";
	echo $PHP_SELF;
	echo "?action=manage\">Create New</a></p>

";
	$aInt->sortableTableInit("name", "ASC");
	$result = select_query("tblemailmarketer", "COUNT(*)", "");
	$data = mysql_fetch_array($result);
	$numrows = $data[0];
	$result = select_query("tblemailmarketer", "", "", $orderby, $order, $page * $limit . ("," . $limit));

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$name = $data['name'];
		$type = $data['type'];
		$disable = $data['disable'];
		$marketing = $data['marketing'];
		$type = ($type == "client" ? "Client" : "Product/Service");
		$disable = ($disable ? "<img src=\"images/icons/disabled.png\">" : "<img src=\"images/icons/tick.png\">");
		$tabledata[] = array($id, $name, $type, "<div align=\"center\">" . $disable . "</a>", "<a href=\"" . $PHP_SELF . "?action=manage&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Edit\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Delete\"></a>");
	}

	echo $aInt->sortableTable(array("ID", "Name", "Type", "Active", "", ""), $tabledata);
}
else {
	if ($action == "manage") {
		if ($id) {
			$result = select_query("tblemailmarketer", "", array("id" => $id));
			$data = mysql_fetch_array($result);
			$id = $data['id'];
			$name = $data['name'];
			$type = $data['type'];
			$settings = $data['settings'];
			$marketing = $data['marketing'];
			$disable = $data['disable'];
			$settings = unserialize($settings);
			$clientnumdays = $settings['clientnumdays'];
			$clientsminactive = $settings['clientsminactive'];
			$clientsmaxactive = $settings['clientsmaxactive'];
			$clientemailtpl = $settings['clientemailtpl'];
			$prodpids = $settings['prodpids'];
			$prodstatus = $settings['prodstatus'];
			$prodnumdays = $settings['prodnumdays'];
			$prodfiltertype = $settings['prodfiltertype'];
			$prodexcludepid = $settings['prodexcludepid'];
			$prodexcludeaid = $settings['prodexcludeaid'];
			$prodemailtpl = $settings['prodemailtpl'];
		}
		else {
			$type = "client";
			$name = $disable = $clientnumdays = "";
		}

		$jscode = "
function showClientRel() {
    $(\"#productrel\").fadeOut(\"fast\",function(){
        $(\"#clientrel\").fadeIn(\"fast\");
    });
}
function showProductRel() {
    $(\"#clientrel\").fadeOut(\"fast\",function(){
        $(\"#productrel\").fadeIn(\"fast\");
    });
}";
		echo "
<p>";
		echo "<s";
		echo "trong>";
		echo $actiontitle;
		echo "</strong></p>

<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?action=save";

		if ($id) {
			echo "&id=" . $id;
		}

		echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"250\" class=\"fieldlabel\">Name</td><td class=\"fieldarea\"><input type=\"text\" size=\"40\" name=\"name\" value=\"";
		echo $name;
		echo "\" /> (Private Admin Use Only)</td></tr>
<tr><td class=\"fieldlabel\">Type</td><td class=\"fieldarea\"><input type=\"radio\" name=\"type\" value=\"client\" id=\"typeclient\" onclick=\"showClientRel()\"";

		if ($type == "client") {
			echo " checked";
		}

		echo " /> <label for=\"typeclient\">Client Related</label> <input type=\"radio\" name=\"type\" value=\"product\" id=\"typeproduct\" onclick=\"showProductRel()\"";

		if ($type == "product") {
			echo " checked";
		}

		echo " /> <label for=\"typeproduct\">Product/Service Related</label></td></tr>
<tr><td class=\"fieldlabel\">Marketing Email</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"marketing\" value=\"1\"";

		if ($marketing) {
			echo " checked";
		}

		echo " /> Don't send this email to clients who have opted out of marketing emails</td></tr>
<tr><td class=\"fieldlabel\">Disabled</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"disable\" value=\"1\"";

		if ($disable) {
			echo " checked";
		}

		echo " /> Tick this box to temporarily disable this marketing rule</td></tr>
</table>

<p><b>Criteria Options</b></p>

<p>You can specify your criteria below for when the email should be sent. The criteria available depends on the type of email selected above.</p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\" id=\"clientrel\"";

		if ($type != "client") {
			echo " style=\"display:none;\"";
		}

		echo ">
<tr><td width=\"250\" class=\"fieldlabel\">Days Since Registration</td><td class=\"fieldarea\"><input type=\"text\" name=\"clientnumdays\" size=\"10\" value=\"";
		echo $clientnumdays;
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">Minimum Number of Active Products/Services</td><td class=\"fieldarea\"><input type=\"text\" name=\"clientsminactive\" size=\"10\" value=\"";
		echo $clientsminactive;
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">Maximum Number of Active Products/Services</td><td class=\"fieldarea\"><input type=\"text\" name=\"clientsmaxactive\" size=\"10\" value=\"";
		echo $clientsmaxactive;
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">Email Template</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"clientemailtpl\">";
		$result = select_query("tblemailtemplates", "id,name", array("type" => "general", "language" => ""), "name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$mid = $data['id'];
			$name = $data['name'];
			echo "<option value=\"" . $mid . "\"";

			if ($mid == $clientemailtpl) {
				echo " selected";
			}

			echo ">" . $name . "</option>";
		}

		echo "</select></td></tr>
</table>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\" id=\"productrel\"";

		if ($type != "product") {
			echo " style=\"display:none;\"";
		}

		echo ">
<tr><td width=\"250\" class=\"fieldlabel\">Product/Service/Addon</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"prodpids[]\" size=\"6\" multiple=\"true\">
";
		$result2 = select_query("tblproducts", "tblproducts.id,tblproducts.gid,tblproducts.name,tblproductgroups.name AS groupname", "", "tblproductgroups`.`order` ASC,`tblproducts`.`order` ASC,`name", "ASC", "", "tblproductgroups ON tblproducts.gid=tblproductgroups.id");

		while ($data = mysql_fetch_array($result2)) {
			$pid = $data['id'];
			$pname = $data['name'];
			$ptype = $data['groupname'];
			echo "<option value=\"P" . $pid . "\"";

			if (in_array("P" . $pid, $prodpids)) {
				echo " selected";
			}

			echo ">" . $ptype . " - " . $pname . "</option>";
		}

		$result = select_query("tbladdons", "id,name", "", "name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$addon_id = $data['id'];
			$addon_name = $data['name'];
			$predefinedaddons[$addon_id] = $addon_name;
			echo "<option value=\"A" . $addon_id . "\"";

			if (in_array("A" . $addon_id, $prodpids)) {
				echo " selected";
			}

			echo ">Addon - " . $addon_name . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">Product/Service Status</td><td class=\"fieldarea\">";
		$statuses = array("Pending", "Active", "Suspended", "Terminated", "Cancelled", "Fraud");
		$code = "<select name=\"prodstatus[]\" size=\"6\" multiple=\"true\">";

		if ($anyop) {
			$code .= "<option value=\"\">" . $aInt->lang("global", "any") . "</option>";
		}

		foreach ($statuses as $stat) {
			$code .= "<option value=\"" . $stat . "\"";

			if (in_array($stat, $prodstatus)) {
				$code .= " selected";
			}

			$code .= ">" . $aInt->lang("status", strtolower($stat)) . "</option>";
		}

		$code .= "</select>";
		echo $code;
		echo "</td></tr>
<tr><td class=\"fieldlabel\">Number of Days</td><td class=\"fieldarea\"><input type=\"text\" name=\"prodnumdays\" size=\"10\" value=\"";
		echo $prodnumdays;
		echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">Days Filter Type</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"prodfiltertype\">
<option value=\"afterorder\"";

		if ($prodfiltertype == "afterorder") {
			echo " selected";
		}

		echo ">After Order Date</option>
<option value=\"beforedue\"";

		if ($prodfiltertype == "beforedue") {
			echo " selected";
		}

		echo ">Before Next Due Date</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">Does Not Have Product/Service</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"prodexcludepid[]\" size=\"6\" multiple=\"true\">
<option value=\"\">None</option>
";
		$result2 = select_query("tblproducts", "tblproducts.id,tblproducts.gid,tblproducts.name,tblproductgroups.name AS groupname", "", "tblproductgroups`.`order` ASC,`tblproducts`.`order` ASC,`name", "ASC", "", "tblproductgroups ON tblproducts.gid=tblproductgroups.id");

		while ($data = mysql_fetch_array($result2)) {
			$pid = $data['id'];
			$pname = $data['name'];
			$ptype = $data['groupname'];
			echo "<option value=\"" . $pid . "\"";

			if (in_array($pid, $prodexcludepid)) {
				echo " selected";
			}

			echo ">" . $ptype . " - " . $pname . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">Does Not Have Addon</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"prodexcludeaid[]\" size=\"6\" multiple=\"true\">
<option value=\"\">None</option>
";
		$result = select_query("tbladdons", "id,name", "", "name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$addon_id = $data['id'];
			$addon_name = $data['name'];
			$predefinedaddons[$addon_id] = $addon_name;
			echo "<option value=\"" . $addon_id . "\"";

			if (in_array($addon_id, $prodexcludeaid)) {
				echo " selected";
			}

			echo ">" . $addon_name . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">Email Template</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"prodemailtpl\">";
		$result = select_query("tblemailtemplates", "id,name", array("type" => "product", "language" => ""), "name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$mid = $data['id'];
			$name = $data['name'];
			echo "<option value=\"" . $mid . "\"";

			if ($mid == $prodemailtpl) {
				echo " selected";
			}

			echo ">" . $name . "</option>";
		}

		echo "</select></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"Save Changes\" class=\"button\"></p>

</form>

";
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->display();
?>