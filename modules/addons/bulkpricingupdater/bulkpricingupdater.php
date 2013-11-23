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
 * */

function bulkpricingupdater_config() {
	$configarray = array( "name" => "Bulk Pricing Updater", "description" => "This addon utility allows you to perform mass updates of product, addon & domain pricing accross existing clients", "version" => "2.0", "author" => "WHMCS", "language" => "english", "fields" => array() );
	return $configarray;
}


function bulkpricingupdater_output($vars) {
	$modulelink = $vars["modulelink"];
	$step = (isset( $_REQUEST["step"] ) ? $_REQUEST["step"] : "");

	if (!$step) {
		echo "
<p>By default, changing the pricing of products & services in the product configuration area will not affect existing clients. They remain at the prices they agreed to at the time of signing up. However, if you want to apply price increases to your existing clients too, then this addon utility allows you to do that.</p>
<p>(Use Ctrl+Click to select more than one criteria in any of the fields)</p>
";
		echo "
<form method=\"post\" action=\"";
		echo $modulelink;
		echo "&step=2\">

<p><b>Conditions</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">Product</td><td class=\"fieldarea\" colspan=\"3\">";
		echo "<s";
		echo "elect name=\"productids[]\" size=\"10\" multiple=\"true\" style=\"width:600px;\">";
		$result = select_query( "tblproducts", "tblproducts.id,tblproducts.gid,tblproducts.name,tblproductgroups.name AS groupname", "", "tblproductgroups`.`order` ASC,`tblproducts`.`order` ASC,`name", "ASC", "", "tblproductgroups ON tblproducts.gid=tblproductgroups.id" );

		while ($data = mysql_fetch_array( $result )) {
			$pid = $data["id"];
			$pname = $data["name"];
			$ptype = $data["groupname"];
			echo "<option value=\"" . $pid . "\">" . $ptype . " - " . $pname . "</option>";
		}

		$result = select_query( "tbladdons", "", "", "name", "ASC" );

		while ($data = mysql_fetch_array( $result )) {
			$id = $data["id"];
			$name = $data["name"];
			$description = $data["description"];
			echo "<option value=\"A" . $id . "\">Addon - " . $name . "</option>";
		}

		$result = select_query( "tbldomainpricing", "DISTINCT extension", "", "extension", "ASC" );

		while ($data = mysql_fetch_array( $result )) {
			$tld = $data["extension"];
			echo "<option value=\"D" . $tld . "\">Domain - " . $tld . "</option>";
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">Status</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"status[]\" size=\"5\" multiple=\"true\">
<option>Pending</option>
<option>Pending Transfer</option>
<option selected>Active</option>
<option selected>Suspended</option>
<option>Terminated</option>
<option>Cancelled</option>
<option>Expired</option>
<option>Fraud</option>
</select></td><td width=\"15%\" class=\"fieldlabel\">Billing Cycle</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"billingcycle[]\" size=\"8\" multiple=\"true\">
<option>Monthly</option>
<option>Quarterly</option>
<option>Semi-Annually</option>
<option>Annually</option>
<option>Biennially</option>
<option>Triennially</option>
";
		$domainyears = 0;

		while ($domainyears <= 10) {
			echo "<option value=\"" . $domainyears . "\">Domain: " . $domainyears . " Year</option>";
			++$domainyears;
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">Currency</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"currid\">";
		$result = select_query( "tblcurrencies", "id,code", "", "code", "ASC" );

		while ($data = mysql_fetch_array( $result )) {
			echo "<option value=\"" . $data["id"] . "\"";

			if ($data["id"] == $currency) {
				echo " selected";
			}

			echo ">" . $data["code"] . "</option>";
		}

		echo "</select></td><td class=\"fieldlabel\">Current Price</td><td class=\"fieldarea\"><input type=\"text\" name=\"currentprice\" size=\"10\" value=\"\" /> (Optional)</td></tr>
</table>

<p><b>Price</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">New Recurring Price</td><td class=\"fieldarea\"><input type=\"text\" name=\"newprice\" size=\"10\" value=\"0.00\" /></";
		echo "td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"Update Pricing\" class=\"button\" /></p>

</form>

";
		return null;
	}

	check_token();
	$productids = $_REQUEST["productids"];
	$status = $_REQUEST["status"];
	$billingcycle = $_REQUEST["billingcycle"];
	$currid = (int)$_REQUEST["currid"];
	$currentprice = $_REQUEST["currentprice"];
	$newprice = $_REQUEST["newprice"];
	$statusmatches = db_build_in_array( $status );
	$billingcyclematches = db_build_in_array( $billingcycle );

	if ($currentprice) {
		$currentprice = format_as_currency( $currentprice );
	}

	$newprice = format_as_currency( $newprice );
	echo "<p><b>Conditions</b></p><p>Statuses: " . $statusmatches . "<br />Billing Cycles: " . $billingcyclematches;

	if ($currentprice) {
		echo "<br />Current Price: " . $currentprice;
	}

	echo "</p><p><b>Pricing Update Results</b></p><ul>";
	$currentprice = db_escape_string( $currentprice );
	$newprice = db_escape_string( $newprice );
	foreach ($productids as $pid) {
		$prodfirstletter = substr( $pid, 0, 1 );
		$prodrest = (int)substr( $pid, 1 );
		echo "<li>";

		if ($prodfirstletter == "A") {
			$query = "UPDATE tblhostingaddons,tblhosting SET tblhostingaddons.recurring='" . $newprice . "' WHERE tblhostingaddons.addonid='" . $prodrest . "' AND tblhostingaddons.status IN (" . $statusmatches . ") AND tblhostingaddons.billingcycle IN (" . $billingcyclematches . ") AND tblhosting.id=tblhostingaddons.hostingid AND tblhosting.userid IN (SELECT id FROM tblclients WHERE currency='" . $currid . "')";

			if ($currentprice) {
				$query .= " AND tblhostingaddons.recurring='" . $currentprice . "'";
			}

			echo "Updated Addon ID " . $prodrest;
		}
		else {
			if ($prodfirstletter == "D") {
				$query = "UPDATE tbldomains SET recurringamount='" . $newprice . "' WHERE domain LIKE '%" . $prodrest . "' AND status IN (" . $statusmatches . ") AND registrationperiod IN (" . $billingcyclematches . ") AND userid IN (SELECT id FROM tblclients WHERE currency='" . $currid . "')";

				if ($currentprice) {
					$query .= " AND recurringamount='" . $currentprice . "'";
				}

				echo "Updated Domains with TLD " . $prodrest;
			}
			else {
				$pid = (int)$pid;
				$query = "UPDATE tblhosting SET amount='" . $newprice . "' WHERE packageid='" . $pid . "' AND domainstatus IN (" . $statusmatches . ") AND billingcycle IN (" . $billingcyclematches . ") AND userid IN (SELECT id FROM tblclients WHERE currency='" . $currid . "')";

				if ($currentprice) {
					$query .= " AND amount='" . $currentprice . "'";
				}

				echo "Updated Product ID " . $pid;
			}
		}

		$result = full_query( $query );
		$numaffected = mysql_affected_rows();
		echo " - " . $numaffected . " Affected";
		echo "</li>";
	}

}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>