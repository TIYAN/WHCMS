<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("List Services");

if ($listtype == "hostingaccount") {
	$pagetitle = $aInt->lang("services", "listhosting");
}
else {
	if ($listtype == "reselleraccount") {
		$pagetitle = $aInt->lang("services", "listreseller");
	}
	else {
		if ($listtype == "server") {
			$pagetitle = $aInt->lang("services", "listservers");
		}
		else {
			if ($listtype == "other") {
				$pagetitle = $aInt->lang("services", "listother");
			}
			else {
				$pagetitle = $aInt->lang("services", "title");
			}
		}
	}
}

$aInt->title = $pagetitle;
$aInt->sidebar = "clients";
$aInt->icon = "products";
$aInt->requiredFiles(array("clientfunctions", "customfieldfunctions", "gatewayfunctions"));
ob_start();

if (listtype) {
	$filter = "true";
}

$aInt->sortableTableInit("domain", "ASC");
$query = "FROM tblhosting INNER JOIN tblclients ON tblclients.id=tblhosting.userid INNER JOIN tblproducts ON tblhosting.packageid=tblproducts.id WHERE tblhosting.id!='' ";

if ($clientname) {
	$query .= "AND concat(firstname,' ',lastname) LIKE '%" . db_escape_string($clientname) . "%' ";
}


if ($listtype) {
	$query .= "AND tblproducts.type='" . db_escape_string($listtype) . "' ";
}


if ($package) {
	$query .= "AND tblproducts.id='" . db_escape_string($package) . "' ";
}


if ($billingcycle) {
	$query .= "AND tblhosting.billingcycle='" . db_escape_string($billingcycle) . "' ";
}


if ($server) {
	$query .= "AND tblhosting.server='" . db_escape_string($server) . "' ";
}


if ($paymentmethod) {
	$query .= "AND tblhosting.paymentmethod='" . db_escape_string($paymentmethod) . "' ";
}


if ($status) {
	$query .= "AND tblhosting.domainstatus='" . db_escape_string($status) . "' ";
}


if ($domain) {
	$query .= "AND tblhosting.domain LIKE '%" . db_escape_string($domain) . "%' ";
}


if ($username) {
	$query .= "AND tblhosting.username='" . db_escape_string($username) . "' ";
}


if ($dedicatedip) {
	$query .= "AND tblhosting.dedicatedip='" . db_escape_string($dedicatedip) . "' ";
}


if ($assignedips) {
	$query .= "AND tblhosting.assignedips LIKE '%" . db_escape_string($assignedips) . "%' ";
}


if ($packagesearch) {
	$query .= "AND tblproducts.name='" . db_escape_string($packagesearch) . "' ";
}


if ($id) {
	$query .= "AND tblhosting.id='" . db_escape_string($id) . "' ";
}


if ($subscriptionid) {
	$query .= "AND tblhosting.subscriptionid='" . db_escape_string($subscriptionid) . "' ";
}


if ($notes) {
	$query .= "AND tblhosting.notes LIKE '%" . db_escape_string($notes) . "%' ";
}


if ($customfieldvalue) {
	if ($customfield) {
		$query .= "AND tblhosting.id IN (SELECT relid FROM tblcustomfieldsvalues WHERE fieldid=" . (int)$customfield . " AND value LIKE '%" . db_escape_string($customfieldvalue) . "%') ";
	}
	else {
		$query .= "AND tblhosting.id IN (SELECT tblcustomfieldsvalues.relid FROM tblcustomfieldsvalues INNER JOIN tblcustomfields ON tblcustomfieldsvalues.fieldid=tblcustomfields.id WHERE tblcustomfields.type='product' AND tblcustomfieldsvalues.value LIKE '%" . db_escape_string($customfieldvalue) . "%') ";
	}
}

$result = full_query("SELECT COUNT(tblhosting.id) " . $query);
$data = mysql_fetch_array($result);
$numrows = $data[0];
echo $aInt->Tabs(array($aInt->lang("global", "searchfilter")), true);
echo "
<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form action=\"";
echo $PHP_SELF;
echo "\" method=\"post\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "producttype");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"listtype\">
<option value=\"\">";
echo $aInt->lang("global", "any");
echo "</option>
<option value=\"hostingaccount\"";

if ($listtype == "hostingaccount") {
	echo " selected";
}

echo ">";
echo $aInt->lang("orders", "sharedhosting");
echo "</option>
<option value=\"reselleraccount\"";

if ($listtype == "reselleraccount") {
	echo " selected";
}

echo ">";
echo $aInt->lang("orders", "resellerhosting");
echo "</option>
<option value=\"server\"";

if ($listtype == "server") {
	echo " selected";
}

echo ">";
echo $aInt->lang("orders", "server");
echo "</option>
<option value=\"other\"";

if ($listtype == "other") {
	echo " selected";
}

echo ">";
echo $aInt->lang("orders", "other");
echo "</option>
</select></td><td class=\"fieldlabel\" width=\"15%\">";
echo $aInt->lang("fields", "server");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"server\">
<option value=\"\">";
echo $aInt->lang("global", "any");
echo "</option>
";
$servers = $disabledservers = "";
$result2 = select_query("tblservers", "id,name,disabled", "", "name", "ASC");

while ($data = mysql_fetch_array($result2)) {
	$id = $data['id'];
	$servername = $data['name'];
	$serverdisabled = $data['disabled'];

	if ($serverdisabled) {
		$servername .= " (" . $aInt->lang("emailtpls", "disabled") . ")";
	}

	$servertemp = "<option value=\"" . $id . "\"";

	if ($server == $id) {
		$servertemp .= " selected";
	}

	$servertemp .= ">" . $servername . "</option>";

	if ($serverdisabled) {
		$disabledservers .= $servertemp;
	}

	$servers .= $servertemp;
}

echo $servers . $disabledservers;
echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "product");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"package\">";
echo $aInt->productDropDown($package, 0, true);
echo "</select></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "paymentmethod");
echo "</td><td class=\"fieldarea\">";
echo paymentMethodsSelection($aInt->lang("global", "any"));
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "billingcycle");
echo "</td><td class=\"fieldarea\">";
echo $aInt->cyclesDropDown($billingcycle, true);
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "status");
echo "</td><td class=\"fieldarea\">";
echo $aInt->productStatusDropDown($status, true);
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "domain");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"domain\" size=\"35\" value=\"";
echo $domain;
echo "\"></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "customfield");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"customfield\" style=\"width:200px;\"><option value=\"\">";
echo $aInt->lang("global", "any");
echo "</option>";
$result2 = select_query("tblcustomfields", "tblcustomfields.id,tblcustomfields.fieldname,tblproducts.name", array("tblcustomfields.type" => "product"), "", "", "", "tblproducts ON tblproducts.id=tblcustomfields.relid");

while ($data = mysql_fetch_array($result2)) {
	$fieldid = $data['id'];
	$fieldname = $data['fieldname'];
	$fieldprodname = $data['name'];
	echo "<option value=\"" . $fieldid . "\"";

	if ($customfield == "" . $fieldid) {
		echo " selected";
	}

	echo "> " . $fieldprodname . " - " . $fieldname . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "clientname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"clientname\" size=\"25\" value=\"";
echo $clientname;
echo "\"></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "customfieldvalue");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"customfieldvalue\" size=\"30\" value=\"";
echo $customfieldvalue;
echo "\"></td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"5\" width=\"1\"><br>
<DIV ALIGN=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("global", "search");
echo "\" class=\"button\"></DIV>

</form>

</div>
</div>

<br />

";
$query .= "ORDER BY ";

if ($orderby == "product") {
	$query .= "tblproducts.name";
}
else {
	if ($orderby == "clientname") {
		$query .= "tblclients.firstname " . $order . ",tblclients.lastname";
	}
	else {
		$query .= $orderby;
	}
}

$query .= " " . $order;
$query = "SELECT tblhosting.*,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblclients.groupid,tblclients.currency,tblproducts.name,tblproducts.type " . $query . " LIMIT " . (int)$page * $limit . "," . (int)$limit;
$result = full_query($query);

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$userid = $data['userid'];
	$regdate = $data['regdate'];
	$orderno = $data['orderno'];
	$domain = $data['domain'];
	$dtype = $data['type'];
	$dpackage = $data['name'];
	$dpaymentmethod = $data['paymentmethod'];
	$firstpaymentamount = $data['firstpaymentamount'];
	$amount = $data['amount'];
	$billingcycle = $data['billingcycle'];
	$nextduedate = $data['nextduedate'];
	$status = $data['domainstatus'];
	$notes = $data['notes'];
	$firstname = $data['firstname'];
	$lastname = $data['lastname'];
	$companyname = $data['companyname'];
	$groupid = $data['groupid'];
	$currency = $data['currency'];

	if (!$domain) {
		$domain = "(" . $aInt->lang("addons", "nodomain") . ")";
	}

	$linkvalue = ($dtype == "other" ? "" : " <a href=\"http://" . $domain . "\" target=\"_blank\" style=\"color:#cc0000\"><small>www</small></a>");

	if ($billingcycle == "One Time" || $billingcycle == "Free Account") {
		$nextduedate = "0000-00-00";
		$amount = $firstpaymentamount;
	}

	$currency = getCurrency("", $currency);
	$amount = formatCurrency($amount);
	$regdate = fromMySQLDate($regdate);
	$nextduedate = ($nextduedate == "0000-00-00" ? "-" : fromMySQLDate($nextduedate));
	$billingcycle = $aInt->lang("billingcycles", str_replace(array("-", "account", " "), "", strtolower($billingcycle)));
	$tabledata[] = array("<input type=\"checkbox\" name=\"selectedclients[]\" value=\"" . $id . "\" class=\"checkall\" />", "<a href=\"clientshosting.php?userid=" . $userid . "&id=" . $id . "\">" . $id . "</a>", $dpackage . " <span class=\"label " . strtolower($status) . "\">" . $status . "</span>", "<a href=\"clientshosting.php?userid=" . $userid . "&id=" . $id . "\">" . $domain . "</a>" . $linkvalue, $aInt->outputClientLink($userid, $firstname, $lastname, $companyname, $groupid), $amount, $billingcycle, $nextduedate);
}

$tableformurl = "sendmessage.php?type=product&multiple=true";
$tableformbuttons = "<input type=\"submit\" value=\"" . $aInt->lang("global", "sendmessage") . "\" class=\"button\">";
echo $aInt->sortableTable(array("checkall", array("id", $aInt->lang("fields", "id")), array("product", $aInt->lang("fields", "product")), array("domain", $aInt->lang("fields", "domain")), array("clientname", $aInt->lang("fields", "clientname")), array("amount", $aInt->lang("fields", "price")), array("billingcycle", $aInt->lang("fields", "billingcycle")), array("nextduedate", $aInt->lang("fields", "nextduedate"))), $tabledata, $tableformurl, $tableformbuttons);
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->display();
?>