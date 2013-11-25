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
$aInt = new WHMCS_Admin("Manage Quotes");
$aInt->requiredFiles(array("clientfunctions", "invoicefunctions"));
$aInt->inClientsProfile = true;
$aInt->valUserID($userid);

if ($delete == "true") {
	checkPermission("Manage Quotes");
	delete_query("tblquotes", array("id" => $quoteid));
	logActivity("Deleted Quote (ID: " . $quoteid . " - User ID: " . $userid . ")");
	header("Location: " . $_SERVER['PHP_SELF'] . ("?userid=" . $userid));
	exit();
}

ob_start();
$jscode = "function doDelete(id) {
if (confirm(\"" . $aInt->lang("quotes", "deletesure") . "\")) {
window.location='" . $PHP_SELF . "?userid=" . $userid . "&delete=true&quoteid='+id;
}}";
echo "
<div align=center><input type=\"button\" value=\"";
echo $aInt->lang("quotes", "createnew");
echo "\" class=\"button\" onClick=\"window.location='quotes.php?action=manage&userid=";
echo $userid;
echo "'\"></div>

";
$currency = getCurrency($userid);
$aInt->sortableTableInit("id", "DESC");
$result = select_query("tblquotes", "COUNT(*)", array("userid" => $userid));
$data = mysql_fetch_array($result);
$numrows = $data[0];
$result = select_query("tblquotes", "", array("userid" => $userid), $orderby, $order, $page * $limit . ("," . $limit));

while ($data = mysql_fetch_assoc($result)) {
	$id = $data['id'];
	$subject = $data['subject'];
	$validuntil = $data['validuntil'];
	$datecreated = $data['datecreated'];
	$stage = $aInt->lang("status", str_replace(" ", "", strtolower($data['stage'])));
	$total = $data['total'];
	$validuntil = fromMySQLDate($validuntil);
	$datecreated = fromMySQLDate($datecreated);
	$total = formatCurrency($total);
	$tabledata[] = array("<a href=\"quotes.php?action=manage&id=" . $id . "\">" . $id . "</a>", $subject, $datecreated, $validuntil, $total, $stage, "<a href=\"quotes.php?action=manage&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>");
}

echo $aInt->sortableTable(array(array("id", $aInt->lang("quotes", "quotenum")), array("subject", $aInt->lang("quotes", "subject")), array("datecreated", $aInt->lang("quotes", "createdate")), array("validuntil", $aInt->lang("quotes", "validuntil")), array("total", $aInt->lang("fields", "total")), array("stage", $aInt->lang("quotes", "stage")), "", ""), $tabledata);
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>