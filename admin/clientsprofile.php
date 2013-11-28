<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Edit Clients Details", false);
$aInt->requiredFiles(array("clientfunctions", "customfieldfunctions", "gatewayfunctions"));
$aInt->inClientsProfile = true;
$aInt->valUserID($userid);

if ($whmcs->get_req_var("save")) {
	check_token("WHMCS.admin.default");
	$email = trim($email);
	$password = trim($password);
	$result = select_query("tblclients", "COUNT(*)", "email='" . db_escape_string($email) . "' AND id!='" . db_escape_string($userid) . "'");
	$data = mysql_fetch_array($result);

	if ($data[0]) {
		redir("userid=" . $userid . "&emailexists=1");
		exit();
	}
	else {
		$errormessage = "";
		run_hook("ClientDetailsValidation", array());
		$_SESSION['profilevalidationerror'] = $errormessage;
		$oldclientsdetails = getClientsDetails($userid);
		$table = "tblclients";
		$array = array("firstname" => $firstname, "lastname" => $lastname, "companyname" => $companyname, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phonenumber, "currency" => $_POST['currency'], "notes" => $notes, "status" => $status, "taxexempt" => $taxexempt, "latefeeoveride" => $latefeeoveride, "overideduenotices" => $overideduenotices, "separateinvoices" => $separateinvoices, "disableautocc" => $disableautocc, "emailoptout" => $emailoptout, "overrideautoclose" => $overrideautoclose, "language" => $language, "billingcid" => $billingcid, "securityqid" => $securityqid, "securityqans" => encrypt($securityqans), "groupid" => $groupid);

		if (!$twofaenabled) {
			$array['authmodule'] = "";
			$array['authdata'] = "";
		}

		$where = array("id" => $userid);
		update_query($table, $array, $where);

		if ($password && $password != $aInt->lang("fields", "entertochange")) {
			if ($CONFIG['NOMD5']) {
				if ($password != decrypt($oldclientsdetails['password'])) {
					update_query("tblclients", array("password" => generateClientPW($password)), array("id" => $userid));
					run_hook("ClientChangePassword", array("userid" => $userid, "password" => $password));
				}
			}
			else {
				update_query("tblclients", array("password" => generateClientPW($password)), array("id" => $userid));
				run_hook("ClientChangePassword", array("userid" => $userid, "password" => $password));
			}
		}

		$customfields = getCustomFields("client", "", $userid, "on", "");
		foreach ($customfields as $k => $v) {
			$k = $v['id'];
			$customfieldsarray[$k] = $_POST['customfield'][$k];
		}

		$updatefieldsarray = array("firstname" => "First Name", "lastname" => "Last Name", "companyname" => "Company Name", "email" => "Email Address", "address1" => "Address 1", "address2" => "Address 2", "city" => "City", "state" => "State", "postcode" => "Postcode", "country" => "Country", "phonenumber" => "Phone Number", "billingcid" => "Billing Contact");
		$updatedtickboxarray = array("latefeeoveride" => "Late Fees Override", "overideduenotices" => "Overdue Notices", "taxexempt" => "Tax Exempt", "separateinvoices" => "Separate Invoices", "disableautocc" => "Disable CC Processing", "emailoptout" => "Marketing Emails Opt-out", "overrideautoclose" => "Auto Close");
		$changelist = array();
		foreach ($updatefieldsarray as $field => $displayname) {

			if ($array[$field] != $oldclientsdetails[$field]) {
				$changelist[] = "" . $displayname . ": '" . $oldclientsdetails[$field] . "' to '" . $array[$field] . "'";
				continue;
			}
		}

		foreach ($updatedtickboxarray as $field => $displayname) {
			$oldfield = ($oldclientsdetails[$field] ? "Enabled" : "Disabled");
			$newfield = ($array[$field] ? "Enabled" : "Disabled");

			if ($oldfield != $newfield) {
				$changelist[] = "" . $displayname . ": '" . $oldfield . "' to '" . $newfield . "'";
				continue;
			}
		}

		saveCustomFields($userid, $customfieldsarray);
		clientChangeDefaultGateway($userid, $paymentmethod);

		if (!count($changelist)) {
			$changelist[] = "No Changes";
		}

		logActivity("Client Profile Modified - " . implode(", ", $changelist) . (" - User ID: " . $userid), $userid);
		run_hook("AdminClientProfileTabFieldsSave", $_REQUEST);
		run_hook("ClientEdit", array_merge(array("userid" => $userid, "olddata" => $oldclientsdetails), $array));
		redir("userid=" . $userid . "&success=true");
		exit();
	}
}

releaseSession();
ob_start();

if ($whmcs->get_req_var("emailexists")) {
	infoBox($aInt->lang("clients", "duplicateemail"), $aInt->lang("clients", "duplicateemailexp"), "error");
}
else {
	if ($_SESSION['profilevalidationerror']) {
		infoBox($aInt->lang("global", "validationerror"), $_SESSION['profilevalidationerror'], "error");
		unset($_SESSION['profilevalidationerror']);
	}
	else {
		if ($whmcs->get_req_var("success")) {
			infoBox($aInt->lang("global", "changesuccess"), $aInt->lang("global", "changesuccessdesc"), "success");
		}
		else {
			if ($whmcs->get_req_var("resetpw")) {
				check_token("WHMCS.admin.default");
				sendMessage("Automated Password Reset", $userid);
				infoBox($aInt->lang("clients", "resetsendpassword"), $aInt->lang("clients", "passwordsuccess"), "success");
			}
		}
	}
}

echo $infobox;
$clientsdetails = getClientsDetails($userid);
$firstname = $clientsdetails['firstname'];
$lastname = $clientsdetails['lastname'];
$companyname = $clientsdetails['companyname'];
$email = $clientsdetails['email'];
$address1 = $clientsdetails['address1'];
$address2 = $clientsdetails['address2'];
$city = $clientsdetails['city'];
$state = $clientsdetails['state'];
$postcode = $clientsdetails['postcode'];
$country = $clientsdetails['country'];
$phonenumber = $clientsdetails['phonenumber'];
$currency = $clientsdetails['currency'];
$notes = $clientsdetails['notes'];
$status = $clientsdetails['status'];
$defaultgateway = $clientsdetails['defaultgateway'];
$taxexempt = $clientsdetails['taxexempt'];
$latefeeoveride = $clientsdetails['latefeeoveride'];
$overideduenotices = $clientsdetails['overideduenotices'];
$separateinvoices = $clientsdetails['separateinvoices'];
$disableautocc = $clientsdetails['disableautocc'];
$emailoptout = $clientsdetails['emailoptout'];
$overrideautoclose = $clientsdetails['overrideautoclose'];
$language = $clientsdetails['language'];
$billingcid = $clientsdetails['billingcid'];
$securityqid = $clientsdetails['securityqid'];
$securityqans = $clientsdetails['securityqans'];
$groupid = $clientsdetails['groupid'];
$twofaenabled = $clientsdetails['twofaenabled'];

if ($CONFIG['NOMD5']) {
	$password = decrypt($clientsdetails['password']);
}
else {
	$password = $aInt->lang("fields", "entertochange");
}

$questions = getSecurityQuestions("");
echo "
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?save=true&userid=";
echo $userid;
echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "firstname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"30\" name=\"firstname\" value=\"";
echo $firstname;
echo "\" tabindex=\"1\"></td><td class=\"fieldlabel\" width=\"15%\">";
echo $aInt->lang("fields", "address1");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"30\" name=\"address1\" value=\"";
echo $address1;
echo "\" tabindex=\"8\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "lastname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"30\" name=\"lastname\" value=\"";
echo $lastname;
echo "\" tabindex=\"2\"></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "address2");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"30\" name=\"address2\" value=\"";
echo $address2;
echo "\" tabindex=\"9\"> <font color=#cccccc>";
echo "<s";
echo "mall>(";
echo $aInt->lang("global", "optional");
echo ")</small></font></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "companyname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"30\" name=\"companyname\" value=\"";
echo $companyname;
echo "\" tabindex=\"3\"> <font color=#cccccc>";
echo "<s";
echo "mall>(";
echo $aInt->lang("global", "optional");
echo ")</small></font></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "city");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"25\" name=\"city\" value=\"";
echo $city;
echo "\" tabindex=\"10\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "email");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"35\" name=\"email\" value=\"";
echo $email;
echo "\" tabindex=\"4\"> <a href=\"http://www.dnsstuff.com/tools/freemail/?domain=";
echo $email;
echo "\" target=\"_blank\" title=\"Check if Free Email Account\"><img src=\"images/info.gif\" border=\"0\" align=\"absmiddle\" /></a></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "state");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"25\" name=\"state\" value=\"";
echo $state;
echo "\" tabindex=\"11\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "password");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"20\" name=\"password\" value=\"";
echo $password;
echo "\" onfocus=\"if(this.value=='";
echo $aInt->lang("fields", "entertochange");
echo "')this.value=''\" tabindex=\"5\" /> <a href=\"clientsprofile.php?userid=";
echo $userid;
echo "&resetpw=true";
echo generate_token("link");
echo "\"><img src=\"images/icons/resetpw.png\" border=\"0\" align=\"absmiddle\" /> ";
echo $aInt->lang("clients", "resetsendpassword");
echo "</a></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "postcode");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"14\" name=\"postcode\" value=\"";
echo $postcode;
echo "\" tabindex=\"12\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "securityquestion");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"securityqid\" style=\"width:225px;\" tabindex=\"6\"><option value=\"\" selected>";
echo $aInt->lang("global", "none");
echo "</option>";
foreach ($questions as $quest => $ions) {
	echo "<option value=" . $ions['id'] . "";

	if ($ions['id'] == $securityqid) {
		echo " selected";
	}

	echo ">" . $ions['question'] . "</option>";
}

echo "</select></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "country");
echo "</td><td class=\"fieldarea\">";
include "../includes/countries.php";
echo getCountriesDropDown($country, "", 13);
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "securityanswer");
echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"securityqans\" size=\"40\" value=\"";
echo $securityqans;
echo "\" tabindex=\"7\"></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "phonenumber");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"20\" name=\"phonenumber\" value=\"";
echo $phonenumber;
echo "\" tabindex=\"14\"></td></tr>
<tr><td class=\"fieldlabel\"><br /></td><td class=\"fieldarea\"></td><td class=\"fieldlabel\"></td><td class=\"fieldarea\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "latefees");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"latefeeoveride\"";

if ($latefeeoveride == "on") {
	echo " checked";
}

echo " tabindex=\"15\"> ";
echo $aInt->lang("clients", "latefeesdesc");
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "paymentmethod");
echo "</td><td class=\"fieldarea\">";
$paymentmethod = $defaultgateway;
echo paymentMethodsSelection($aInt->lang("clients", "changedefault"), 21);
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "overduenotices");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"overideduenotices\"";

if ($overideduenotices == "on") {
	echo " checked";
}

echo " tabindex=\"16\"> ";
echo $aInt->lang("clients", "overduenoticesdesc");
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "billingcontact");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"billingcid\" tabindex=\"22\"><option value=\"0\">";
echo $aInt->lang("global", "default");
echo "</option>";
select_query("tblcontacts", "", array("userid" => $userid), "firstname` ASC,`lastname", "ASC");

while ($data = $result = mysql_fetch_array($result)) {
	echo "<option value=\"" . $data['id'] . "\"";

	if ($data['id'] == $billingcid) {
		echo " selected";
	}

	echo ">" . $data['firstname'] . " " . $data['lastname'] . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "taxexempt");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"taxexempt\"";

if ($taxexempt == "on") {
	echo " checked";
}

echo " tabindex=\"17\"> ";
echo $aInt->lang("clients", "taxexemptdesc");
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("global", "language");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"language\" tabindex=\"23\"><option value=\"\">";
echo $aInt->lang("global", "default");
echo "</option>";
foreach ($whmcs->getValidLanguages() as $lang) {
	echo "<option value=\"" . $lang . "\"";

	if ($language && $lang == $whmcs->validateLanguage($language)) {
		echo " selected=\"selected\"";
	}

	echo ">" . ucfirst($lang) . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "separateinvoices");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"separateinvoices\"";

if ($separateinvoices == "on") {
	echo " checked";
}

echo " tabindex=\"18\"> ";
echo $aInt->lang("clients", "separateinvoicesdesc");
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "status");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"status\" tabindex=\"24\">
<option value=\"Active\"";

if ($status == "Active") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "active");
echo "</option>
<option value=\"Inactive\"";

if ($status == "Inactive") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "inactive");
echo "</option>
<option value=\"Closed\"";

if ($status == "Closed") {
	echo " selected";
}

echo ">";
echo $aInt->lang("status", "closed");
echo "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "disableccprocessing");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"disableautocc\"";

if ($disableautocc == "on") {
	echo " checked";
}

echo " tabindex=\"19\"> ";
echo $aInt->lang("clients", "disableccprocessingdesc");
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("currencies", "currency");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"currency\" tabindex=\"25\">";
select_query("tblcurrencies", "id,code", "", "code", "ASC");

while ($data = $result = mysql_fetch_array($result)) {
	echo "<option value=\"" . $data['id'] . "\"";

	if ($data['id'] == $currency) {
		echo " selected";
	}

	echo ">" . $data['code'] . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "marketingemailsoptout");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"emailoptout\"";

if ($emailoptout == "1") {
	echo " checked";
}

echo " value=\"1\" tabindex=\"20\"> ";
echo $aInt->lang("clients", "disablemarketingemails");
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "clientgroup");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"groupid\" tabindex=\"27\"><option value=\"0\">";
echo $aInt->lang("global", "none");
echo "</option>
";
select_query("tblclientgroups", "", "", "groupname", "ASC");

while ($data = $result = mysql_fetch_assoc($result)) {
	$data['id'];
	$data['groupname'];
	$group_colour = $group_name = $group_id = $data['groupcolour'];
	echo "<option style=\"background-color:" . $group_colour . "\" value=" . $group_id . "";

	if ($group_id == $groupid) {
		echo " selected";
	}

	echo ">" . $group_name . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "overrideautoclose");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"overrideautoclose\"";

if ($overrideautoclose == "1") {
	echo " checked";
}

echo " value=\"1\" tabindex=\"20\"> ";
echo $aInt->lang("clients", "overrideautocloseinfo");
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("twofa", "title");
echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"twofaenabled\"";

if ($twofaenabled) {
	echo " checked";
}

echo " value=\"1\" tabindex=\"27\"> ";
echo $aInt->lang("clients", "2faenabled");
echo "</label></td></tr>
<tr>";
$taxindex = 0;
$customfields = getCustomFields("client", "", $userid, "on", "");
$x = 0;
foreach ($customfields as $customfield) {
	++$x;
	echo "<td class=\"fieldlabel\">" . $customfield['name'] . "</td><td class=\"fieldarea\">" . str_replace(array("<input", "<select", "<textarea"), array("<input tabindex=\"" . $taxindex . "\"", "<select tabindex=\"" . $taxindex . "\"", "<textarea tabindex=\"" . $taxindex . "\""), $customfield['input']) . "</td>";

	if ($x % 2 == 0 || $x == count($customfields)) {
		echo "</tr><tr>";
	}

	++$taxindex;
}

$hookret = run_hook("AdminClientProfileTabFields", $clientsdetails);
foreach ($hookret as $hookdat) {
	foreach ($hookdat as $k => $v) {
		echo "<td class=\"fieldlabel\">" . $k . "</td><td class=\"fieldarea\" colspan=\"3\">" . $v . "</td></tr>";
	}
}

echo "<td class=\"fieldlabel\">";
echo $aInt->lang("fields", "adminnotes");
echo "</td><td class=\"fieldarea\" colspan=\"3\"><textarea name=\"notes\" rows=4 style=\"width:100%;\" tabindex=\"";
echo $taxindex++;
echo "\">";
echo $notes;
echo "</textarea></td></tr>
</table>

<img src=\"images/spacer.gif\" height=\"10\" width=\"1\"><br>
<div align=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("global", "savechanges");
echo "\" class=\"btn btn-primary\" tabindex=\"";
echo $taxindex++;
echo "\"> <input type=\"reset\" value=\"";
echo $aInt->lang("global", "cancelchanges");
echo "\" class=\"button\" tabindex=\"";
echo $taxindex++;
echo "\"></div>
</form>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>