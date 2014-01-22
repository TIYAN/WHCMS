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
$aInt = new WHMCS_Admin("View Activity Log");
$aInt->inClientsProfile = true;
$aInt->valUserID($userid);
ob_start();
echo "
<form method=\"post\" action=\"clientslog.php?userid=";
echo $userid;
echo "\">
<div style=\"width:100%;margin:0 auto;background-color:#f4f4f4;border:1px solid #ccc;text-align:center;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><table cellpadding=\"6\" cellspacing=\"0\" align=\"center\"><tr><td><b>";
echo $aInt->lang("global", "searchfilter");
echo "</b></td><td>";
echo $aInt->lang("fields", "date");
echo ": <input type=\"text\" name=\"date\" value=\"";
echo $date;
echo "\" class=\"datepick\"></td><td>";
echo $aInt->lang("fields", "description");
echo ": <input type=\"text\" name=\"description\" value=\"";
echo $description;
echo "\" size=\"30\"></td><td>";
echo $aInt->lang("fields", "username");
echo ": ";
echo "<s";
echo "elect name=\"username\"><option value=\"\">Any</option>";
$result = select_query("tblactivitylog", "DISTINCT user", "", "user", "ASC");

while ($data = mysql_fetch_array($result)) {
	$user = $data['user'];
	echo "<option";

	if ($user == $username) {
		echo " selected";
	}

	echo ">" . $user . "</option>";
}

echo "</select></td><td>";
echo $aInt->lang("fields", "ipaddress");
echo ": <input type=\"text\" name=\"ipaddress\" value=\"";
echo $ipaddress;
echo "\" size=\"20\"></td><td><input type=\"submit\" value=\"";
echo $aInt->lang("system", "filterlog");
echo "\" /></td></tr></table></div>
</form>

<br />

";
$aInt->sortableTableInit("date");
$where = "userid='" . (int)$userid . "' AND ";

if ($date) {
	$where .= "date>'" . toMySQLDate($date) . "' AND date<='" . toMySQLDate($date) . "235959' AND ";
}


if ($username) {
	$where .= "user='" . db_escape_string($username) . "' AND ";
}


if ($description) {
	$where .= "description LIKE '%" . db_escape_string($description) . "%' AND ";
}


if ($ipaddress) {
	$where .= " ipaddr='" . db_escape_string($ipaddress) . "' AND ";
}


if ($where) {
	$where = substr($where, 0, 0 - 5);
}

$result = select_query("tblactivitylog", "COUNT(*)", $where, "id", "DESC");
$data = mysql_fetch_array($result);
$numrows = $data[0];
$patterns[] = "/- User ID: (.*?) /";
$patterns[] = "/Service ID: (.*?) /";
$patterns[] = "/Domain ID: (.*?) /";
$patterns[] = "/Invoice ID: (.*?) /";
$patterns[] = "/Quote ID: (.*?) /";
$patterns[] = "/Order ID: (.*?) /";
$patterns[] = "/Transaction ID: (.*?) /";
$replacements[] = "";
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
	$description = whmcsHtmlspecialchars($description);
	$description = preg_replace($patterns, $replacements, $description);
	$tabledata[] = array(fromMySQLDate($date, "time"), "<div align=\"left\">" . $description . "</div>", $username, $ipaddr);
}

echo $aInt->sortableTable(array("Date", "Description", "User", "IP Address"), $tabledata);
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>