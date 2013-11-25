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
$aInt = new WHMCS_Admin("Configure Currencies");
$aInt->title = $aInt->lang("currencies", "title");
$aInt->sidebar = "config";
$aInt->icon = "income";
$aInt->helplink = "Currencies";
$aInt->requiredFiles(array("currencyfunctions"));

if ($action == "add") {
	check_token("WHMCS.admin.default");
	insert_query("tblcurrencies", array("code" => $code, "prefix" => html_entity_decode($prefix), "suffix" => html_entity_decode($suffix), "format" => $format, "rate" => $rate));
	header("Location: configcurrencies.php");
	exit();
}


if ($action == "save") {
	check_token("WHMCS.admin.default");

	if ($id == 1) {
		$rate = 1;
	}

	update_query("tblcurrencies", array("code" => $code, "prefix" => html_entity_decode($prefix), "suffix" => html_entity_decode($suffix), "format" => $format, "rate" => $rate), array("id" => $id));

	if ($updatepricing) {
		currencyUpdatePricing($id);
	}

	header("Location: configcurrencies.php");
	exit();
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	$result = select_query("tblclients", "COUNT(*)", array("currency" => $id));
	$data = mysql_fetch_array($result);
	$inuse = $data[0];

	if (!$inuse) {
		delete_query("tblcurrencies", array("id" => $id));
		delete_query("tblpricing", array("currency" => $id));
	}

	header("Location: configcurrencies.php");
	exit();
}

ob_start();

if (!$action) {
	$jscode = "function doDelete(id) {
if (confirm(\"" . $aInt->lang("currencies", "delsure") . "\")) {
window.location='" . $_SERVER['PHP_SELF'] . "?action=delete&id='+id+'" . generate_token("link") . "';
}}";

	if ($updaterates) {
		$msg = currencyUpdateRates();
		infoBox($aInt->lang("currencies", "exchrateupdate"), $msg);
	}


	if ($updateprices) {
		currencyUpdatePricing();
		infoBox($aInt->lang("currencies", "updatepricing"), $aInt->lang("currencies", "updatepricinginfo"));
	}

	echo $infobox;
	echo "<p>" . $aInt->lang("currencies", "info") . "</p>";
	$aInt->sortableTableInit("nopagination");
	$totalcurrencies = 0;
	$result = select_query("tblcurrencies", "", "", "code", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$code = $data['code'];
		$prefix = $data['prefix'];
		$suffix = $data['suffix'];
		$format = $data['format'];
		$rate = $data['rate'];

		if ($format == 1) {
			$formatex = "1234.56";
		}
		else {
			if ($format == 2) {
				$formatex = "1,234.56";
			}
			else {
				if ($format == 3) {
					$formatex = "1.234,56";
				}
				else {
					if ($format == 4) {
						$formatex = "1,234";
					}
				}
			}
		}


		if ($id != 1) {
			$result2 = select_query("tblclients", "COUNT(*)", array("currency" => $id));
			$data = mysql_fetch_array($result2);
			$inuse = $data[0];
			$deletelink = "<a href=\"#\" onClick=\"";

			if ($inuse) {
				$deletelink .= "alert('" . addslashes($aInt->lang("currencies", "deleteinuse")) . "');return false";
			}
			else {
				$deletelink .= "doDelete('" . $id . "')";
			}

			$deletelink .= "\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>";
		}
		else {
			$deletelink = "";
		}

		$tabledata[] = array($code, $prefix, $suffix, $formatex, $rate, "<a href=\"" . $PHP_SELF . "?action=edit&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", $deletelink);
		++$totalcurrencies;
	}

	echo $aInt->sortableTable(array($aInt->lang("currencies", "code"), $aInt->lang("currencies", "prefix"), $aInt->lang("currencies", "suffix"), $aInt->lang("currencies", "format"), $aInt->lang("currencies", "baserate"), "", ""), $tabledata);
	echo "
<p align=\"center\"><input type=\"button\" value=\"";
	echo $aInt->lang("currencies", "updateexch");
	echo "\" class=\"button\" onclick=\"window.location='configcurrencies.php?updaterates=true'\" /> <input type=\"button\" value=\"";
	echo $aInt->lang("currencies", "updateprod");
	echo "\" class=\"button\" onclick=\"window.location='configcurrencies.php?updateprices=true'\" /></p>

<h2>";
	echo $aInt->lang("currencies", "addadditional");
	echo "</h2>

<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "\">
<input type=\"hidden\" name=\"action\" value=\"add\" />

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
	echo $aInt->lang("currencies", "code");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"code\" size=\"10\"> ";
	echo $aInt->lang("currencies", "codeinfo");
	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("currencies", "prefix");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"prefix\" size=\"10\"></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("currencies", "suffix");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"suffix\" size=\"10\"></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("currencies", "format");
	echo "</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"format\">
<option value=\"1\">1234.56</option>
<option value=\"2\">1,234.56</option>
<option value=\"3\">1.234,56</option>
<option value=\"4\">1,234</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("currencies", "baserate");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"rate\" size=\"10\"> ";
	echo $aInt->lang("currencies", "baserateinfo");
	echo "</td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("currencies", "add");
	echo "\" class=\"button\"></p>

</form>

";
}
else {
	if ($action == "edit") {
		$result = select_query("tblcurrencies", "", array("id" => $id));
		$data = mysql_fetch_array($result);
		$code = $data['code'];
		$prefix = $data['prefix'];
		$suffix = $data['suffix'];
		$format = $data['format'];
		$rate = $data['rate'];
		echo "
<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "\">
<input type=\"hidden\" name=\"action\" value=\"save\" />
<input type=\"hidden\" name=\"id\" value=\"";
		echo $id;
		echo "\" />

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("currencies", "code");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"code\" size=\"10\" value=\"";
		echo $code;
		echo "\"> ";
		echo $aInt->lang("currencies", "codeinfo");
		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("currencies", "prefix");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"prefix\" size=\"10\" value=\"";
		echo $prefix;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("currencies", "suffix");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"suffix\" size=\"10\" value=\"";
		echo $suffix;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("currencies", "format");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"format\">
<option value=\"1\"";

		if ($format == 1) {
			echo " selected";
		}

		echo ">1234.56</option>
<option value=\"2\"";

		if ($format == 2) {
			echo " selected";
		}

		echo ">1,234.56</option>
<option value=\"3\"";

		if ($format == 3) {
			echo " selected";
		}

		echo ">1.234,56</option>
<option value=\"4\"";

		if ($format == 4) {
			echo " selected";
		}

		echo ">1,234</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("currencies", "baserate");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"rate\" size=\"10\" value=\"";
		echo $rate;
		echo "\"";

		if ($id == 1) {
			echo " readonly=true disabled";
		}

		echo "> ";
		echo $aInt->lang("currencies", "baserateinfo");
		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("currencies", "updatepricing");
		echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"updatepricing\"> ";
		echo $aInt->lang("currencies", "recalcpricing");
		echo "</td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("global", "savechanges");
		echo "\" class=\"button\"></p>

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