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
$aInt = new WHMCS_Admin("View Activity Log");
$aInt->title = $aInt->lang("system", "activitylog");
$aInt->sidebar = "utilities";
$aInt->icon = "logs";
ob_start();
echo $aInt->Tabs(array($aInt->lang("global", "searchfilter")), true);
echo "
<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form method=\"post\" action=\"systemactivitylog.php\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "date");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"date\" value=\"";
echo $date;
echo "\" class=\"datepick\"></td><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "username");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"username\"><option value=\"\">";
echo $aInt->lang("global", "any");
echo "</option>";
$query = "SELECT DISTINCT user FROM tblactivitylog ORDER BY user ASC";
$result = full_query($query);

while ($data = mysql_fetch_array($result)) {
	$user = $data['user'];
	echo "<option";

	if ($user == $username) {
		echo " selected";
	}

	echo ">" . $user . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "description");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"description\" value=\"";
echo $description;
echo "\" size=\"80\"></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "ipaddress");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ipaddress\" value=\"";
echo $ipaddress;
echo "\" size=\"20\"></td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"10\" width=\"1\"><br>
<div align=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("system", "filterlog");
echo "\" class=\"button\"></div>

</form>

  </div>
</div>

<br />

";
$result = select_query("tblactivitylog", "", "userid=0", "id", "DESC", $CONFIG['ActivityLimit'] . ",9999");

while ($data = mysql_fetch_array($result)) {
	delete_query("tblactivitylog", array("id" => $data['id']));
}

$aInt->sortableTableInit("date");
$where = "";

if ($date) {
	$where .= " AND date>'" . toMySQLDate($date) . "' AND date<='" . toMySQLDate($date) . "235959'";
}


if ($username) {
	$where .= " AND user='" . db_escape_string($username) . "'";
}


if ($description) {
	$where .= " AND description LIKE '%" . db_escape_string($description) . "%'";
}


if ($ipaddress) {
	$where .= " AND ipaddr='" . db_escape_string($ipaddress) . "'";
}


if ($where) {
	$where = substr($where, 5);
}

$result = select_query("tblactivitylog", "COUNT(*)", $where);
$data = mysql_fetch_array($result);
$numrows = $data[0];
$patterns = $replacements = array();
$patterns[] = "/User ID: (.*?) /";
$patterns[] = "/Service ID: (.*?) /";
$patterns[] = "/Domain ID: (.*?) /";
$patterns[] = "/Invoice ID: (.*?) /";
$patterns[] = "/Quote ID: (.*?) /";
$patterns[] = "/Order ID: (.*?) /";
$patterns[] = "/Transaction ID: (.*?) /";
$replacements[] = "<a href=\"clientssummary.php?userid=$1\">User ID: $1</a> ";
$replacements[] = "<a href=\"clientsservices.php?id=$1\">Service ID: $1</a> ";
$replacements[] = "<a href=\"clientsdomains.php?id=$1\">Domain ID: $1</a> ";
$replacements[] = "<a href=\"invoices.php?action=edit&id=$1\">Invoice ID: $1</a> ";
$replacements[] = "<a href=\"quotes.php?action=manage&id=$1\">Quote ID: $1</a> ";
$replacements[] = "<a href=\"orders.php?action=view&id=$1\">Order ID: $1</a> ";
$replacements[] = "<a href=\"transactions.php?action=edit&id=$1\">Transaction ID: $1</a> ";
$result = select_query("tblactivitylog", "", $where, "id", "DESC", $page * $limit . ("," . $limit));

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$description = $data['description'];
	$username = $data['user'];
	$date = $data['date'];
	$ipaddr = $data['ipaddr'];
	$description .= " ";
	$description = preg_replace($patterns, $replacements, $description);
	$tabledata[] = array(fromMySQLDate($date, "time"), "<div align=\"left\">" . $description . "</div>", $username, $ipaddr);
}

echo $aInt->sortableTable(array($aInt->lang("fields", "date"), $aInt->lang("fields", "description"), $aInt->lang("fields", "username"), $aInt->lang("fields", "ipaddress")), $tabledata);
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->display();
?>