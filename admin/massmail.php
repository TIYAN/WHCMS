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
$aInt = new WHMCS_Admin("Mass Mail");
$aInt->title = $aInt->lang("permissions", "21");
$aInt->sidebar = "clients";
$aInt->icon = "massmail";
$aInt->helplink = "Mass Mail";
$aInt->requiredFiles(array("customfieldfunctions"));
$clientgroups = getClientGroups();
$jscode = "function showMailOptions(type) {
    $(\"#product_criteria\").slideUp();
    $(\"#addon_criteria\").slideUp();
    $(\"#domain_criteria\").slideUp();
    $(\"#client_criteria\").slideDown();
    if (type) $(\"#\"+type+\"_criteria\").slideDown();
}";
ob_start();
echo "
<p>";
echo $aInt->lang("massmail", "pagedesc");
echo "</p>

<form method=\"post\" action=\"sendmessage.php?type=massmail\">

<h2>";
echo $aInt->lang("massmail", "messagetype");
echo "</h2>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
echo $aInt->lang("massmail", "emailtype");
echo "</td><td class=\"fieldarea\">
<input type=\"radio\" name=\"emailtype\" value=\"General\" id=\"typegen\" onclick=\"showMailOptions('')\" /> <label for=\"typegen\">";
echo $aInt->lang("emailtpls", "typegeneral");
echo "</label> &nbsp; <input type=\"radio\" name=\"emailtype\" value=\"Product/Service\" id=\"typeprod\" onclick=\"showMailOptions('product')\" /> <label for=\"typeprod\">";
echo $aInt->lang("fields", "product");
echo "</label> &nbsp; <input type=\"radio\" name=\"emailtype\" value=\"Addon\" id=\"typeaddon\" onclick=\"showMailOptions('addon')\" /> <label for=\"typeaddon\">";
echo $aInt->lang("fields", "addon");
echo "</label> &nbsp; <input type=\"radio\" name=\"emailtype\" value=\"Domain\" id=\"typedom\" onclick=\"showMailOptions('domain')\" /> <label for=\"typedom\">";
echo $aInt->lang("fields", "domain");
echo "</label>
</tr></tr>
</table>

<div id=\"client_criteria\" style=\"display:none;\">

<br />

<h2>";
echo $aInt->lang("massmail", "clientcriteria");
echo "</h2>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "clientgroup");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"clientgroup[]\" size=\"4\" multiple=\"true\">";
foreach ($clientgroups as $groupid => $data) {
	echo "<option value=\"" . $groupid . "\">" . $data['name'] . "</option>";
}

echo "</select></td></tr>
";
$customfields = getCustomFields("client", "", "", true);
foreach ($customfields as $customfield) {
	echo "<tr><td class=\"fieldlabel\">" . $customfield['name'] . "</td><td class=\"fieldarea\">";

	if ($customfield['type'] == "tickbox") {
		echo "<input type=\"radio\" name=\"customfield[" . $customfield['id'] . "]\" value=\"\" checked /> No Filter <input type=\"radio\" name=\"customfield[" . $customfield['id'] . "]\" value=\"cfon\" /> Checked Only <input type=\"radio\" name=\"customfield[" . $customfield['id'] . "]\" value=\"cfoff\" /> Unchecked Only";
	}
	else {
		echo str_replace("\"><option value=\"", "\"><option value=\"\">" . $aInt->lang("global", "any") . "</option><option value=\"", $customfield['input']);
	}

	echo "</td></tr>";
}

echo "<tr><td class=\"fieldlabel\">";
echo $aInt->lang("global", "language");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"clientlanguage[]\" size=\"4\" multiple=\"true\"><option value=\"\" selected>";
echo $aInt->lang("global", "default");
echo "</option>";
$result = select_query("tblclients", "DISTINCT language", "", "language", "ASC");

while ($data = mysql_fetch_array($result)) {
	$language = $displanguage = $data['language'];

	if (trim($language)) {
		echo "<option value=\"" . $language . "\" selected>" . ucfirst($displanguage) . "</option>";
	}
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("massmail", "clientstatus");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"clientstatus[]\" size=\"3\" multiple=\"true\"><option value=\"Active\" selected>";
echo $aInt->lang("status", "active");
echo "</option><option value=\"Inactive\" selected>";
echo $aInt->lang("status", "inactive");
echo "</option><option value=\"Closed\" selected>";
echo $aInt->lang("status", "closed");
echo "</option></select></td></tr>
</table>

</div>
<div id=\"product_criteria\" style=\"display:none;\">

<br />

<h2>";
echo $aInt->lang("massmail", "productservicecriteria");
echo "</h2>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "product");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"productids[]\" size=\"10\" multiple=\"true\">";
$result = select_query("tblproducts", "tblproducts.id,tblproducts.name,tblproductgroups.name AS groupname", "", "tblproductgroups`.`order` ASC,`tblproducts`.`order` ASC,`tblproducts`.`name", "ASC", "", "tblproductgroups ON tblproducts.gid=tblproductgroups.id");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$name = $data['name'];
	$groupname = $data['groupname'];
	echo "<option value=\"" . $id . "\">" . $groupname . " - " . $name . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("massmail", "productservicestatus");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"productstatus[]\" size=\"5\" multiple=\"true\">
<option value=\"Pending\">";
echo $aInt->lang("status", "pending");
echo "</option>
<option value=\"Active\">";
echo $aInt->lang("status", "active");
echo "</option>
<option value=\"Suspended\">";
echo $aInt->lang("status", "suspended");
echo "</option>
<option value=\"Terminated\">";
echo $aInt->lang("status", "terminated");
echo "</option>
<option value=\"Cancelled\">";
echo $aInt->lang("status", "cancelled");
echo "</option>
<option value=\"Fraud\">";
echo $aInt->lang("status", "fraud");
echo "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("massmail", "assignedserver");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"server[]\" size=\"5\" multiple=\"true\">";
$result = select_query("tblservers", "", "", "name", "ASC");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$name = $data['name'];
	echo "<option value=\"" . $id . "\">" . $name . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("massmail", "sendforeachdomain");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"sendforeach\">";
echo $aInt->lang("massmail", "tickboxsendeverymatchingdomain");
echo "</td></tr>
</table>

</div>
<div id=\"addon_criteria\" style=\"display:none;\">

<br />

<h2>";
echo $aInt->lang("massmail", "addoncriteria");
echo "</h2>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "addon");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"addonids[]\" size=\"10\" multiple=\"true\">";
$result = select_query("tbladdons", "id,name", "", "name", "ASC");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$addonname = $data['name'];
	echo "<option value=\"" . $id . "\">" . $addonname . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("massmail", "addonstatus");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"addonstatus[]\" size=\"5\" multiple=\"true\">
<option value=\"Pending\">";
echo $aInt->lang("status", "pending");
echo "</option>
<option value=\"Active\">";
echo $aInt->lang("status", "active");
echo "</option>
<option value=\"Suspended\">";
echo $aInt->lang("status", "suspended");
echo "</option>
<option value=\"Terminated\">";
echo $aInt->lang("status", "terminated");
echo "</option>
<option value=\"Cancelled\">";
echo $aInt->lang("status", "cancelled");
echo "</option>
<option value=\"Fraud\">";
echo $aInt->lang("status", "fraud");
echo "</option>
</select></td></tr>
</table>

</div>
<div id=\"domain_criteria\" style=\"display:none;\">

<br />

<h2>";
echo $aInt->lang("massmail", "domaincriteria");
echo "</h2>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
echo $aInt->lang("massmail", "domainstatus");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"domainstatus[]\" size=\"5\" multiple=\"true\">
<option value=\"Pending\">";
echo $aInt->lang("status", "pending");
echo "</option>
<option value=\"Pending Transfer\">";
echo $aInt->lang("status", "pendingtransfer");
echo "</option>
<option value=\"Active\">";
echo $aInt->lang("status", "active");
echo "</option>
<option value=\"Expired\">";
echo $aInt->lang("status", "expired");
echo "</option>
<option value=\"Cancelled\">";
echo $aInt->lang("status", "cancelled");
echo "</option>
<option value=\"Fraud\">";
echo $aInt->lang("status", "fraud");
echo "</option>
</select></td></tr>
</table>

</div>

<p align=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("massmail", "composemsg");
echo "\" class=\"button\"></p>

</form>

<p>";
echo $aInt->lang("massmail", "footnote");
echo "</p>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->display();
?>