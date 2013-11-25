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
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View Products/Services");
$aInt->title = "Configurable Option Groups";
$aInt->sidebar = "config";
$aInt->icon = "configoptions";
$aInt->helplink = "Configurable Options";

if ($manageoptions) {
	$result = select_query("tblcurrencies", "", "", "code", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$curr_id = $data['id'];
		$curr_code = $data['code'];
		$currenciesarray[$curr_id] = $curr_code;
	}

	$totalcurrencies = count($currenciesarray) * 2;

	if ($save) {
		check_token("WHMCS.admin.default");
		checkPermission("Edit Products/Services");

		if (!$cid) {
			$cid = insert_query("tblproductconfigoptions", array("gid" => $gid, "optionname" => $configoptionname));
		}


		if ($optionname == "") {
			$optionname = array();
		}


		if ($addoptionname == "") {
			$addoptionname = array();
		}

		update_query("tblproductconfigoptions", array("optionname" => $configoptionname, "optiontype" => $configoptiontype, "qtyminimum" => $qtyminimum, "qtymaximum" => $qtymaximum), array("id" => $cid));
		foreach ($optionname as $key => $value) {
			update_query("tblproductconfigoptionssub", array("optionname" => $value, "sortorder" => $sortorder[$key], "hidden" => $hidden[$key]), array("id" => $key));
		}


		if ($price) {
			foreach ($price as $curr_id => $temp_values) {
				foreach ($temp_values as $optionid => $values) {
					update_query("tblpricing", array("msetupfee" => $values[1], "qsetupfee" => $values[2], "ssetupfee" => $values[3], "asetupfee" => $values[4], "bsetupfee" => $values[5], "tsetupfee" => $values[11], "monthly" => $values[6], "quarterly" => $values[7], "semiannually" => $values[8], "annually" => $values[9], "biennially" => $values[10], "triennially" => $values[12]), array("type" => "configoptions", "currency" => $curr_id, "relid" => $optionid));
				}
			}
		}


		if ($addoptionname) {
			insert_query("tblproductconfigoptionssub", array("configid" => $cid, "optionname" => $addoptionname, "sortorder" => $addsortorder, "hidden" => $addhidden));
		}

		header("Location: " . $_SERVER['PHP_SELF'] . ("?manageoptions=true&cid=" . $cid));
		exit();
	}


	if ($deleteconfigoption) {
		check_token("WHMCS.admin.default");
		checkPermission("Delete Products/Services");
		delete_query("tblproductconfigoptionssub", array("id" => $confid));
		header("Location: " . $_SERVER['PHP_SELF'] . ("?manageoptions=true&cid=" . $cid));
		exit();
	}

	$aInt->title = "Configurable Options";
	$result = select_query("tblproductconfigoptions", "", array("id" => $cid));
	$data = mysql_fetch_array($result);
	$cid = $data['id'];
	$optionname = $data['optionname'];
	$optiontype = $data['optiontype'];
	$qtyminimum = $data['qtyminimum'];
	$qtymaximum = $data['qtymaximum'];
	$order = $data['order'];
	ob_start();
	echo "
";
	echo "<s";
	echo "cript langauge=\"JavaScript\">
function deletegroupoption(id) {
	if (confirm(\"Are you sure you want to delete this product configuration option?\")) {
		window.location='";
	echo $PHP_SELF;
	echo "?manageoptions=true&cid=";
	echo $cid;
	echo "&deleteconfigoption=true&confid='+id+'";
	echo generate_token("link");
	echo "';
	}
}
function closewindow() {
	window.opener.document.managefrm.submit();
	window.close();
}
</script>

<form method=\"post\" action=\"";
	echo $_SERVER['PHP_SELF'];
	echo "?manageoptions=true&cid=";
	echo $cid;

	if ($gid) {
		echo "&gid=" . $gid;
	}

	echo "&save=true\">

<p>Option Name: <input type=\"text\" name=\"configoptionname\" size=\"50\" value=\"";
	echo $optionname;
	echo "\" /> Option Type: ";
	echo "<s";
	echo "elect name=\"configoptiontype\"><option value=\"1\"";

	if ($optiontype == "1") {
		echo " selected";
	}

	echo ">Dropdown</option><option value=\"2\"";

	if ($optiontype == "2") {
		echo " selected";
	}

	echo ">Radio</option><option value=\"3\"";

	if ($optiontype == "3") {
		echo " selected";
	}

	echo ">Yes/No</option><option value=\"4\"";

	if ($optiontype == "4") {
		echo " selected";
	}

	echo ">Quantity</option></select>";

	if ($optiontype == "4") {
		echo "<br>Minimum Quantity Required: <input type=\"text\" name=\"qtyminimum\" size=\"6\" value=\"" . $qtyminimum . "\" /> Maximum Allowed: <input type=\"text\" name=\"qtymaximum\" size=\"6\" value=\"" . $qtymaximum . "\" /> (Leave blank for no limit)";
	}

	echo "</p>

<table width=100% align=center cellpadding=2 cellspacing=1 bgcolor=#cccccc>
<tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold;\"><td>Options</td><td width=70> </td><td width=70> </td><td width=70>";
	echo $aInt->lang("billingcycles", "onetime");
	echo "/<br />";
	echo $aInt->lang("billingcycles", "monthly");
	echo "</td><td width=70>Quarterly</td><td width=70>Semi-Annual</td><td width=70>Annual</td><td width=70>Biennial</td><td width=70>Triennial</td><td width=50>Order</td><td width=30>Hide</td></tr>
";
	$x = 0;
	$query = "SELECT * FROM tblproductconfigoptionssub WHERE configid=" . (int)$cid . " ORDER BY sortorder ASC,id ASC";
	$result = full_query($query);

	while ($data = mysql_fetch_array($result)) {
		++$x;
		$optionid = $data['id'];
		$optionname = $data['optionname'];
		$sortorder = $data['sortorder'];
		$hidden = $data['hidden'];
		echo ("<tr bgcolor=\"#ffffff\" style=\"text-align:center;\"><td rowspan=\"" . $totalcurrencies . "\"><input type=\"text\" name=\"optionname[" . $optionid . "]") . "\" value=\"" . $optionname . "\" size=\"40\">";

		if (1 < $x) {
			echo "<br><a href=\"#\" onclick=\"deletegroupoption('" . $optionid . "');return false;\"><img src=\"images/icons/delete.png\" border=\"0\">";
		}

		echo "</td>";
		$firstcurrencydone = false;
		foreach ($currenciesarray as $curr_id => $curr_code) {
			$result2 = select_query("tblpricing", "", array("type" => "configoptions", "currency" => $curr_id, "relid" => $optionid));
			$data = mysql_fetch_array($result2);
			$pricing_id = $data['id'];

			if (!$pricing_id) {
				insert_query("tblpricing", array("type" => "configoptions", "currency" => $curr_id, "relid" => $optionid));
				$result2 = select_query("tblpricing", "", array("type" => "configoptions", "currency" => $curr_id, "relid" => $optionid));
				$data = mysql_fetch_array($result2);
			}

			$val[1] = $data['msetupfee'];
			$val[2] = $data['qsetupfee'];
			$val[3] = $data['ssetupfee'];
			$val[4] = $data['asetupfee'];
			$val[5] = $data['bsetupfee'];
			$val[11] = $data['tsetupfee'];
			$val[6] = $data['monthly'];
			$val[7] = $data['quarterly'];
			$val[8] = $data['semiannually'];
			$val[9] = $data['annually'];
			$val[10] = $data['biennially'];
			$val[12] = $data['triennially'];

			if ($firstcurrencydone) {
				echo "</tr><tr bgcolor=\"#ffffff\" style=\"text-align:center;\">";
			}

			echo (((((((((((((((((("<td rowspan=\"2\" bgcolor=\"#efefef\"><b>" . $curr_code . "</b></td><td>Setup</td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[1]\" size=\"10\" value=\"" . $val['1'] . "\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[2]\" size=\"10\" value=\"" . $val['2'] . "\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[3]\" size=\"10\" value=\"" . $val['3'] . "\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[4]\" size=\"10\" value=\"" . $val['4'] . "\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[5]\" size=\"10\" value=\"" . $val['5'] . "\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[11]\" size=\"10\" value=\"" . $val['11'] . "\"></td>";

			if (!$firstcurrencydone) {
				echo (("<td rowspan=\"" . $totalcurrencies . "\"><input type=\"text\" name=\"sortorder[" . $optionid . "]") . "\" value=\"" . $sortorder . "\" style=\"width:100%;\"></td><td rowspan=\"" . $totalcurrencies . "\"><input type=\"checkbox\" name=\"hidden[" . $optionid . "]") . "\" value=\"1\"";

				if ($hidden) {
					echo " checked";
				}

				echo " /></td>";
			}

			echo (((((((((((((((((("</tr><tr bgcolor=\"#ffffff\" style=\"text-align:center;\"><td>Pricing</td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[6]\" size=\"10\" value=\"" . $val['6'] . "\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[7]\" size=\"10\" value=\"" . $val['7'] . "\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[8]\" size=\"10\" value=\"" . $val['8'] . "\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[9]\" size=\"10\" value=\"" . $val['9'] . "\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[10]\" size=\"10\" value=\"" . $val['10'] . "\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "]") . "[") . $optionid . "]") . "[12]\" size=\"10\" value=\"" . $val['12'] . "\"></td>";
			$firstcurrencydone = true;
		}

		echo "</tr>";
	}


	if (($optiontype == "1" || $optiontype == "2") || $x == "0") {
		echo "<tr bgcolor=\"#efefef\"><td colspan=\"9\"><B>Add Option:</B> <input type=\"text\" name=\"addoptionname\" size=\"60\"></td><td><input type=\"text\" name=\"addsortorder\" value=\"0\" style=\"width:100%;\"></td><td><input type=\"checkbox\" name=\"addhidden\" value=\"1\" /></td></tr>
";
	}

	echo "</table>

<p align=\"center\"><input type=\"submit\" value=\"Save Changes\" class=\"button\" /> <input type=\"button\" value=\"Close Window\" onclick=\"closewindow();\" class=\"button\" /></p>

</form>

";
	$content = ob_get_contents();
	ob_end_clean();
	$aInt->content = $content;
	$aInt->displayPopUp();
	exit();
}


if ($action == "savegroup") {
	check_token("WHMCS.admin.default");
	checkPermission("Edit Products/Services");

	if ($id) {
		update_query("tblproductconfiggroups", array("name" => $name, "description" => $description), array("id" => $id));
		$response = "saved";
	}
	else {
		$id = insert_query("tblproductconfiggroups", array("name" => $name, "description" => $description));
		$response = "added";
	}

	delete_query("tblproductconfiglinks", array("gid" => $id));

	if ($productlinks) {
		foreach ($productlinks as $pid) {
			insert_query("tblproductconfiglinks", array("gid" => $id, "pid" => $pid));
		}
	}


	if ($order) {
		foreach ($order as $configid => $sortorder) {
			update_query("tblproductconfigoptions", array("order" => $sortorder, "hidden" => $hidden[$configid]), array("id" => $configid));
		}
	}

	header("Location: " . $_SERVER['PHP_SELF'] . ("?action=managegroup&id=" . $id));
	exit();
}


if ($action == "duplicate") {
	check_token("WHMCS.admin.default");
	checkPermission("Create New Products/Services");
	$result = select_query("tblproductconfiggroups", "", array("id" => $existinggroupid));
	$data = mysql_fetch_array($result);
	$addstr = "";
	foreach ($data as $key => $value) {

		if (is_numeric($key)) {
			if ($key == "0") {
				$value = "";
			}


			if ($key == "1") {
				$value = $newgroupname;
			}

			$addstr .= "'" . db_escape_string($value) . "',";
			continue;
		}
	}

	$addstr = substr($addstr, 0, 0 - 1);
	full_query("INSERT INTO tblproductconfiggroups VALUES (" . $addstr . ")");
	$newgroupid = mysql_insert_id();
	$result = select_query("tblproductconfigoptions", "", array("gid" => $existinggroupid));

	while ($data = mysql_fetch_array($result)) {
		$configid = $data['id'];
		$addstr = "";
		foreach ($data as $key => $value) {

			if (is_numeric($key)) {
				if ($key == "0") {
					$value = "";
				}


				if ($key == "1") {
					$value = $newgroupid;
				}

				$addstr .= "'" . db_escape_string($value) . "',";
				continue;
			}
		}

		$addstr = substr($addstr, 0, 0 - 1);
		full_query("INSERT INTO tblproductconfigoptions VALUES (" . $addstr . ")");
		$newconfigid = mysql_insert_id();
		$result2 = select_query("tblproductconfigoptionssub", "", array("configid" => $configid));

		while ($data = mysql_fetch_array($result2)) {
			$optionid = $data['id'];
			$addstr = "";
			foreach ($data as $key => $value) {

				if (is_numeric($key)) {
					if ($key == "0") {
						$value = "";
					}


					if ($key == "1") {
						$value = $newconfigid;
					}

					$addstr .= "'" . db_escape_string($value) . "',";
					continue;
				}
			}

			$addstr = substr($addstr, 0, 0 - 1);
			full_query("INSERT INTO tblproductconfigoptionssub VALUES (" . $addstr . ")");
			$newoptionid = mysql_insert_id();
			$result3 = select_query("tblpricing", "", array("type" => "configoptions", "relid" => $optionid));

			while ($data = mysql_fetch_array($result3)) {
				$addstr = "";
				foreach ($data as $key => $value) {

					if (is_numeric($key)) {
						if ($key == "0") {
							$value = "";
						}


						if ($key == "3") {
							$value = $newoptionid;
						}

						$addstr .= "'" . db_escape_string($value) . "',";
						continue;
					}
				}

				$addstr = substr($addstr, 0, 0 - 1);
				full_query("INSERT INTO tblpricing VALUES (" . $addstr . ")");
			}
		}
	}

	header("Location: " . $_SERVER['PHP_SELF'] . "?duplicated=true");
	exit();
}


if ($action == "deleteoption") {
	check_token("WHMCS.admin.default");
	checkPermission("Edit Products/Services");
	delete_query("tblproductconfigoptions", array("id" => $opid));
	delete_query("tblproductconfigoptionssub", array("configid" => $opid));
	delete_query("tblhostingconfigoptions", array("configid" => $opid));
	header("Location: " . $_SERVER['PHP_SELF'] . ("?action=managegroup&id=" . $id));
	exit();
}


if ($action == "deletegroup") {
	check_token("WHMCS.admin.default");
	checkPermission("Delete Products/Services");
	$result = select_query("tblproductconfigoptions", "", array("gid" => $id));

	while ($data = mysql_fetch_array($result)) {
		$opid = $data['id'];
		delete_query("tblproductconfigoptions", array("id" => $opid));
		delete_query("tblproductconfigoptionssub", array("configid" => $opid));
		delete_query("tblhostingconfigoptions", array("configid" => $opid));
	}

	delete_query("tblproductconfiggroups", array("id" => $id));
	delete_query("tblproductconfiglinks", array("gid" => $id));
	header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=true");
	exit();
}

ob_start();
$jscode = "function doDelete(id) {
if (confirm(\"Are you sure you want to delete this configurable option group?\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=deletegroup&id='+id+'" . generate_token("link") . "';
}}";

if ($action == "") {
	if ($deleted) {
		infoBox("Success", "The option group has been deleted successfully!");
	}


	if ($duplicated) {
		infoBox("Success", "The option group has been duplicated successfully!");
	}

	echo $infobox;
	echo "
<p>Configurable options allow you to offer addons and customisation options with your products. Options are assigned to groups and groups can then be applied to products.</p>

<p><b>Options:</b> <a href=\"";
	echo $_SERVER['PHP_SELF'];
	echo "?action=managegroup\">Create a New Group</a> | <a href=\"";
	echo $_SERVER['PHP_SELF'];
	echo "?action=duplicategroup\">Duplicate a Group</a></p>

";
	$aInt->sortableTableInit("nopagination");
	$result = select_query("tblproductconfiggroups", "", "", "name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$name = $data['name'];
		$description = $data['description'];
		$tabledata[] = array($name, $description, "<a href=\"" . $_SERVER['PHP_SELF'] . ("?action=managegroup&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Edit\"></a>"), "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Delete\"></a>");
	}

	echo $aInt->sortableTable(array("Group Name", "Description", "", ""), $tabledata);
}
else {
	if ($action == "managegroup") {
		if ($id) {
			$steptitle = "Manage Group";
			$result = select_query("tblproductconfiggroups", "", array("id" => $id));
			$data = mysql_fetch_array($result);
			$id = $data['id'];
			$name = $data['name'];
			$description = $data['description'];
			$productlinks = array();
			$result = select_query("tblproductconfiglinks", "", array("gid" => $id));

			while ($data = mysql_fetch_array($result)) {
				$productlinks[] = $data['pid'];
			}
		}
		else {
			checkPermission("Create New Products/Services");
			$steptitle = "Create a New Group";
			$id = "";
			$productlinks = array();
		}

		$jscode = "function manageconfigoptions(id) {
    window.open('" . $_SERVER['PHP_SELF'] . "?manageoptions=true&cid='+id,'configoptions','width=900,height=500,scrollbars=yes');
}
function addconfigoption() {
    window.open('" . $_SERVER['PHP_SELF'] . "?manageoptions=true&gid=" . $id . "','configoptions','width=800,height=500,scrollbars=yes');
}
function doDelete(id,opid) {
    if (confirm(\"Are you sure you want to delete this configurable option?\")) {
        window.location='" . $_SERVER['PHP_SELF'] . "?action=deleteoption&id='+id+'&opid='+opid+'" . generate_token("link") . "';
    }
}";
		echo "
<form method=\"post\" action=\"";
		echo $_SERVER['PHP_SELF'];
		echo "?action=savegroup&id=";
		echo $id;
		echo "\" name=\"managefrm\">

<p><b>";
		echo $steptitle;
		echo "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">Group Name</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" size=\"40\" value=\"";
		echo $name;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Description</td><td class=\"fieldarea\"><input type=\"text\" name=\"description\" size=\"80\" value=\"";
		echo $description;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Assigned Products</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"productlinks[]\" size=\"8\" style=\"width:90%\" multiple>
";
		$result = select_query("tblproducts", "tblproducts.id,tblproducts.name,tblproductgroups.name AS groupname", "", "groupname` ASC,`name", "ASC", "", "tblproductgroups ON tblproducts.gid=tblproductgroups.id");

		while ($data = mysql_fetch_array($result)) {
			$pid = $data['id'];
			$groupname = $data['groupname'];
			$name = $data['name'];
			echo "<option value=\"" . $pid . "\"";

			if (in_array($pid, $productlinks)) {
				echo " selected";
			}

			echo ">" . $groupname . " - " . $name . "</option>";
		}

		echo "</select></td></tr>
</table>

";

		if ($id) {
			echo "
<p><b>Configurable Options</b></p>

<p align=\"center\"><input type=\"button\" value=\"Add New Configurable Option\" class=\"button\" onclick=\"addconfigoption()\" /></p>

";
			$aInt->sortableTableInit("nopagination");
			$result = select_query("tblproductconfigoptions", "", array("gid" => $id), "order` ASC,`id", "ASC");

			while ($data = mysql_fetch_array($result)) {
				$configid = $data['id'];
				$optionname = $data['optionname'];
				$configorder = $data['order'];
				$hidden = $data['hidden'];

				if ($hidden) {
					$hidden = " checked";
				}

				$tabledata[] = array($optionname, ("<input type=\"text\" name=\"order[" . $configid . "]") . "\" size=\"10\" value=\"" . $configorder . "\" />", ("<input type=\"checkbox\" name=\"hidden[" . $configid . "]") . "\" value=\"1\"" . $hidden . " />", "<a href=\"#\" onClick=\"manageconfigoptions('" . $configid . "');return false\"><img src=\"images/edit.gif\" border=\"0\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "','" . $configid . "');return false\"><img src=\"images/delete.gif\" border=\"0\"></a>");
			}

			echo $aInt->sortableTable(array("Option", "Sort Order", "Hidden", "", ""), $tabledata);
		}

		echo "
<P ALIGN=\"center\"><input type=\"submit\" value=\"Save Changes\" class=\"button\" /> <input type=\"button\" value=\"Back to Groups List\" onClick=\"window.location='configproductoptions.php'\" class=\"button\" /></P>

</form>

";
	}
	else {
		if ($action == "duplicategroup") {
			checkPermission("Create New Products/Services");
			echo "
<p><b>Duplicate Group</b></p>

<form method=\"post\" action=\"";
			echo $PHP_SELF;
			echo "?action=duplicate\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=150 class=\"fieldlabel\">Existing Group</td><td class=\"fieldarea\">";
			echo "<s";
			echo "elect name=\"existinggroupid\">";
			$result = select_query("tblproductconfiggroups", "", "", "name", "ASC");

			while ($data = mysql_fetch_array($result)) {
				$id = $data['id'];
				$name = $data['name'];
				$description = $data['description'];

				if (50 < strlen($description)) {
					$description = substr($description, 0, 50) . "...";
				}

				echo "<option value=\"" . $id . "\">" . $name . " - " . $description . "</option>";
			}

			echo "</select></td></tr>
<tr><td class=\"fieldlabel\">New Group Name</td><td class=\"fieldarea\"><input type=\"text\" name=\"newgroupname\" size=\"50\"></td></tr>
</table>
<P ALIGN=\"center\"><input type=\"submit\" value=\"Continue >>\" class=\"button\"></P>
</form>

";
		}
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>