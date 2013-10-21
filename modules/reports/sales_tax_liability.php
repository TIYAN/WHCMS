<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

$reportdata["title"] = "Sales Tax Liability";
$reportdata["description"] = "This report shows sales tax liability for the selected period";

$reportdata["currencyselections"] = true;

$query = "select year(min(date)) as minimum, year(max(date)) as maximum from tblaccounts;";
$result = full_query($query);
$data = mysql_fetch_array($result);
$minyear = $data['minimum'];
$maxyear = $data['maximum'];

$reportdata["headertext"] = "<form method=\"post\" action=\"$PHP_SELF?report=$report&currencyid=$currencyid&calculate=true\"><center>Start Date: <input type=\"text\" name=\"startdate\" value=\"$startdate\" class=\"datepick\" /> &nbsp;&nbsp;&nbsp; End Date: <input type=\"text\" name=\"enddate\" value=\"$enddate\" class=\"datepick\" /> &nbsp;&nbsp;&nbsp; <input type=\"submit\" value=\"Generate Report\"></form>";

if ($calculate) {

	$query = "SELECT COUNT(*),SUM(total),SUM(tblinvoices.credit),SUM(tax),SUM(tax2) FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid WHERE datepaid>='".db_make_safe_human_date($startdate)."' AND datepaid<='".db_make_safe_human_date($enddate)." 23:59:59' AND tblinvoices.status='Paid' AND currency=".(int)$currencyid;
	$result = full_query($query);
	$data = mysql_fetch_array($result);
	$numinvoices = $data[0];
	$total = $data[1] + $data[2];
	$tax = $data[3];
    $tax2 = $data[4];

	if (!$total) $total="0.00";
	if (!$tax) $tax="0.00";
    if (!$tax2) $tax2="0.00";

	$reportdata["headertext"] .= "<br>$numinvoices Invoices Found<br><B>Total Invoiced:</B> ".formatCurrency($total)." &nbsp; <B>Tax Level 1 Liability:</B> ".formatCurrency($tax)." &nbsp; <B>Tax Level 2 Liability:</B> ".formatCurrency($tax2);
}

$reportdata["headertext"] .= "</center>";

$reportdata["tableheadings"] = array("Invoice ID","Client Name","Invoice Date","Date Paid","Subtotal","Tax","Credit","Total");

$query = "SELECT tblinvoices.*,tblclients.firstname,tblclients.lastname FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid WHERE datepaid>='".db_make_safe_human_date($startdate)."' AND datepaid<='".db_make_safe_human_date($enddate)." 23:59:59' AND tblinvoices.status='Paid' AND currency=".(int)$currencyid." ORDER BY date ASC";
$result = full_query($query);
while ($data = mysql_fetch_array($result)) {
	$id = $data["id"];
    $userid = $data["userid"];
	$client = $data["firstname"]." ".$data["lastname"];
	$date = fromMySQLDate($data["date"]);
	$datepaid = fromMySQLDate($data["datepaid"]);
    $currency = getCurrency($userid);
	$subtotal = $data["subtotal"];
	$credit = $data["credit"];
	$tax = $data["tax"]+$data["tax2"];
	$total = $data["total"] + $credit;
	$reportdata["tablevalues"][] = array("$id","$client","$date","$datepaid","$subtotal","$tax","$credit","$total");
}

$data["footertext"]="";

?>