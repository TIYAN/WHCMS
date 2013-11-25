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
$aInt = new WHMCS_Admin("Add New Client", false);
$aInt->title = $aInt->lang("clients", "addnew");
$aInt->sidebar = "clients";
$aInt->icon = "clientsadd";
$aInt->requiredFiles(array("clientfunctions", "customfieldfunctions", "gatewayfunctions"));

if ($action == "add") {
	check_token("WHMCS.admin.default");
	$result = select_query("tblclients", "COUNT(*)", array("email" => $email));
	$data = mysql_fetch_array($result);

	if ($data[0]) {
		infoBox($aInt->lang("clients", "duplicateemail"), $aInt->lang("clients", "duplicateemailexp"), "error");
	}
	else {
		if (!trim($email) && !$cccheck) {
			infoBox($aInt->lang("global", "validationerror"), $aInt->lang("clients", "invalidemail"), "error");
		}
		else {
			if (!$cccheck && trim($email)) {
				$emaildomain = explode("@", $email, 2);
				$emaildomain = $emaildomain[1];

				if (!preg_match("/^([a-zA-Z0-9&'.])+([\.a-zA-Z0-9+_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $email)) {
					$errormessage .= "<li>" . $_LANG['clientareaerroremailinvalid'];
					infoBox($aInt->lang("global", "validationerror"), $aInt->lang("clients", "invalidemail"), "error");
				}
				else {
					$query = "subaccount=1 AND email='" . mysql_real_escape_string($email) . "'";
					$result = select_query("tblcontacts", "COUNT(*)", $query);
					$data = mysql_fetch_array($result);

					if ($data[0]) {
						infoBox($aInt->lang("clients", "duplicateemail"), $aInt->lang("clients", "duplicateemailexp"), "error");
					}
				}
			}


			if (!$infobox) {
				$_SESSION['currency'] = $currency;
				$userid = addClient($firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password, $securityqid, $securityqans, $sendemail, array("notes" => $notes, "status" => $status, "credit" => $credit, "taxexempt" => $taxexempt, "latefeeoveride" => $latefeeoveride, "overideduenotices" => $overideduenotices, "language" => $language, "billingcid" => $billingcid, "lastlogin" => "00000000000000", "groupid" => $groupid, "separateinvoices" => $separateinvoices, "disableautocc" => $disableautocc, "defaultgateway" => $paymentmethod));
				unset($_SESSION['uid']);
				unset($_SESSION['upw']);
				header("Location: clientssummary.php?userid=" . $userid);
				exit();
			}
		}
	}
}

releaseSession();
ob_start();
$questions = getSecurityQuestions("");
echo $infobox;
echo "
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?action=add\">

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
echo "\" tabindex=\"4\"></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "state");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"25\" name=\"state\" value=\"";
echo $state;
echo "\" tabindex=\"11\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "password");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"20\" name=\"password\" value=\"";
echo $password;
echo "\" tabindex=\"5\" /></td><td class=\"fieldlabel\">";
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
echo paymentMethodsSelection($aInt->lang("clients", "changedefault"), 20);
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
echo "elect name=\"billingcid\" tabindex=\"21\"><option value=\"\">";
echo $aInt->lang("global", "default");
echo "</option>";
$result = select_query("tblcontacts", "", array("userid" => $userid), "firstname` ASC,`lastname", "ASC");

while ($data = mysql_fetch_array($result)) {
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
echo "elect name=\"language\" tabindex=\"22\"><option value=\"\">";
echo $aInt->lang("global", "default");
echo "</option>";
foreach ($whmcs->getValidLanguages() as $lang) {
	echo "<option value=\"" . $lang . "\">" . ucfirst($lang) . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "separateinvoices");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"separateinvoices\"";

if ($separateinvoices == "on") {
	echo " checked";
}

echo " tabindex=\"18\">";
echo $aInt->lang("clients", "separateinvoicesdesc");
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "status");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"status\" tabindex=\"23\">
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

echo " tabindex=\"19\">";
echo $aInt->lang("clients", "disableccprocessingdesc");
echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("currencies", "currency");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"currency\" tabindex=\"24\">";
$result = select_query("tblcurrencies", "id,code,`default`", "", "code", "ASC");

while ($data = mysql_fetch_array($result)) {
	echo "<option value=\"" . $data['id'] . "\"";

	if (($currency && $data['id'] == $currency) || (!$currency && $data['default'])) {
		echo " selected";
	}

	echo ">" . $data['code'] . "</option>";
}

echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "creditbalance");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"10\" name=\"credit\" value=\"";
echo $credit;
echo "\" tabindex=\"25\"></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "clientgroup");
echo "</td><td class=\"fieldarea\">";
echo "<s";
echo "elect name=\"groupid\" tabindex=\"26\"><option value=\"0\">";
echo $aInt->lang("global", "none");
echo "</option>
";
$result = select_query("tblclientgroups", "", "", "groupname", "ASC");

while ($data = mysql_fetch_assoc($result)) {
	$group_id = $data['id'];
	$group_name = $data['groupname'];
	$group_colour = $data['groupcolour'];
	echo "<option style=\"background-color:" . $group_colour . "\" value=" . $group_id . "";

	if ($group_id == $groupid) {
		echo " selected";
	}

	echo ">" . $group_name . "</option>";
}

echo "</select></td></tr>
<tr>";
$taxindex = 27;
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

echo "<td class=\"fieldlabel\">";
echo $aInt->lang("fields", "adminnotes");
echo "</td><td class=\"fieldarea\" colspan=\"3\"><textarea name=\"notes\" rows=4 style=\"width:100%;\" tabindex=\"";
echo $taxindex++;
echo "\">";
echo $notes;
echo "</textarea></td></tr>
</table>

<br />
<label><input type=\"checkbox\" name=\"sendemail\" checked tabindex=\"";
echo $taxindex++;
echo "\" /> ";
echo $aInt->lang("clients", "newaccinfoemail");
echo "</label>
<br /><br />

<div align=\"center\"><input type=\"submit\" value=\"";
echo $aInt->lang("clients", "addclient");
echo "\" tabindex=\"";
echo $taxindex++;
echo "\" /></div>

</form>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>