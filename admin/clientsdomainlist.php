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
$aInt = new WHMCS_Admin("List Domains");
$aInt->title = $aInt->lang("services", "listdomains");
$aInt->sidebar = "clients";
$aInt->icon = "domains";
$aInt->requiredFiles(array("registrarfunctions"));
ob_start();
echo $aInt->Tabs(array($aInt->lang("global", "searchfilter")), true);
echo "

<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form action=\"";
echo $PHP_SELF;
echo "\" method=\"post\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "domain");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"domain\" size=\"35\" value=\"";
echo $domain;
echo "\"></td><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "status");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"status\">
<option value=\"\">";
echo $aInt->lang("global", "any");
echo "</option>
<option value=\"Pending\"";

if ($status == "Pending") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "pending");
echo "</option>
<option value=\"Pending Transfer\"";

if ($status == "Pending Transfer") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "pendingtransfer");
echo "</option>
<option value=\"Active\"";

if ($status == "Active") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "active");
echo "</option>
<option value=\"Expired\"";

if ($status == "Expired") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "expired");
echo "</option>
<option value=\"Cancelled\"";

if ($status == "Cancelled") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "cancelled");
echo "</option>
<option value=\"Fraud\"";

if ($status == "Fraud") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "fraud");
echo "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "registrar");
echo "</td><td class=\"fieldarea\">";
echo getRegistrarsDropdownMenu($registrar);
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "clientname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"clientname\" size=\"25\" value=\"";
echo $clientname;
echo "\"></td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"5\" width=\"1\"><br>
<DIV ALIGN=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("global", "search");
echo "\" class=\"button\"></DIV>

</form>

  </div>
</div>

<br>

";
$aInt->sortableTableInit("domain", "ASC");
$query = "FROM tbldomains INNER JOIN tblclients ON tblclients.id=tbldomains.userid WHERE tbldomains.id!='' ";

if ($clientname) {
	$query .= "AND concat(firstname,' ',lastname) LIKE '%" . db_escape_string($clientname) . "%' ";
}


if ($status) {
	$query .= "AND tbldomains.status='" . db_escape_string($status) . "' ";
}


if ($domain) {
	$query .= "AND tbldomains.domain LIKE '%" . db_escape_string($domain) . "%' ";
}


if ($registrar) {
	$query .= "AND tbldomains.registrar='" . db_escape_string($registrar) . "' ";
}


if ($id) {
	$query .= "AND tbldomains.id='" . db_escape_string($id) . "' ";
}


if ($subscriptionid) {
	$query .= "AND tbldomains.subscriptionid='" . db_escape_string($subscriptionid) . "' ";
}


if ($notes) {
	$query .= "AND tbldomains.additionalnotes LIKE '%" . db_escape_string($notes) . "%' ";
}

$result = full_query("SELECT COUNT(tbldomains.id) " . $query);
$data = mysql_fetch_array($result);
$numrows = $data[0];
$query .= "ORDER BY ";

if ($orderby == "clientname") {
	$query .= "firstname " . $order . ",lastname";
}
else {
	$query .= $orderby;
}

$query .= " " . $order;
$query = "SELECT tbldomains.*,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblclients.groupid,tblclients.currency " . $query . " LIMIT " . (int)$page * $limit . "," . (int)$limit;
$result = full_query($query);

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$userid = $data['userid'];
	$domain = $data['domain'];
	$amount = $data['recurringamount'];
	$registrar = $data['registrar'];
	$nextduedate = $data['nextduedate'];
	$expirydate = $data['expirydate'];
	$subscriptionid = $data['subscriptionid'];
	$registrationdate = $data['registrationdate'];
	$registrationperiod = $data['registrationperiod'];
	$status = $data['status'];
	$firstname = $data['firstname'];
	$lastname = $data['lastname'];
	$companyname = $data['companyname'];
	$groupid = $data['groupid'];
	$currency = $data['currency'];

	if (!$domain) {
		$domain = "(No Domain)";
	}

	$currency = getCurrency("", $currency);
	$amount = formatCurrency($amount);
	$registrationdate = fromMySQLDate($registrationdate);
	$nextduedate = fromMySQLDate($nextduedate);
	$expirydate = fromMySQLDate($expirydate);
	$registrationperiod .= (1 < $registrationperiod ? " " . $aInt->lang("domains", "years") : " " . $aInt->lang("domains", "year"));
	$tabledata[] = array("<input type=\"checkbox\" name=\"selectedclients[]\" value=\"" . $id . "\" class=\"checkall\" />", "<a href=\"clientsdomains.php?userid=" . $userid . "&domainid=" . $id . "\">" . $id . "</a>", "<a href=\"clientsdomains.php?userid=" . $userid . "&id=" . $id . "\">" . $domain . "</a> <a href=\"http://www." . $domain . "\" target=\"_blank\" style=\"color:#cc0000\"><small>www</small></a>" . " <span class=\"label " . strtolower($status) . "\">" . $status . "</span>", $aInt->outputClientLink($userid, $firstname, $lastname, $companyname, $groupid), $registrationperiod, ucfirst($registrar), $amount, $nextduedate, $expirydate);
}

$tableformurl = "sendmessage.php?type=domain&multiple=true";
$tableformbuttons = "<input type=\"submit\" value=\"" . $aInt->lang("global", "sendmessage") . "\" class=\"button\">";
echo $aInt->sortableTable(array("checkall", array("id", $aInt->lang("fields", "id")), array("domain", $aInt->lang("fields", "domain")), array("clientname", $aInt->lang("fields", "clientname")), array("registrationperiod", $aInt->lang("fields", "regperiod")), array("registrar", $aInt->lang("fields", "registrar")), array("recurringamount", $aInt->lang("fields", "price")), array("nextduedate", $aInt->lang("fields", "nextduedate")), array("expirydate", $aInt->lang("fields", "expirydate"))), $tabledata, $tableformurl, $tableformbuttons);
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->display();
?>