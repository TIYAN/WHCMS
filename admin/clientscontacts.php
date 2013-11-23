<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Edit Clients Details");
$aInt->requiredFiles(array("clientfunctions"));
$aInt->inClientsProfile = true;
$aInt->valUserID($userid);

if ($action == "save") {
	checkPermission("Edit Clients Details");

	if ($subaccount) {
		$subaccount = "1";
		$result = select_query("tblclients", "COUNT(*)", array("email" => $email));
		$data = mysql_fetch_array($result);
		$result = select_query("tblcontacts", "COUNT(*)", array("email" => $email, "id" => array("sqltype" => "NEQ", "value" => $contactid)));
		$data2 = mysql_fetch_array($result);

		if ($data[0] + $data2[0]) {
			$querystring = "";
			foreach ($_REQUEST as $k => $v) {

				if (!is_array($v) && $k != "action") {
					$querystring .= "&" . $k . "=" . urlencode($v);
					continue;
				}
			}

			header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . $_LANG['ordererroruserexists'] . $querystring);
			exit();
		}
	}
	else {
		$subaccount = "0";
	}


	if ($domainemails) {
		$domainemails = 1;
	}


	if ($generalemails) {
		$generalemails = 1;
	}


	if ($invoiceemails) {
		$invoiceemails = 1;
	}


	if ($productemails) {
		$productemails = 1;
	}


	if ($supportemails) {
		$supportemails = 1;
	}


	if ($affiliateemails) {
		$affiliateemails = 1;
	}


	if ($contactid == "addnew") {
		if ($password && $password != $aInt->lang("fields", "password")) {
			$array['password'] = generateClientPW($password);
		}

		$contactid = addContact($userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password, $permissions, $generalemails, $productemails, $domainemails, $invoiceemails, $supportemails);
		logActivity("Added Contact - User ID: " . $userid . " - Contact ID: " . $contactid);
	}
	else {
		logActivity("Contact Modified - User ID: " . $userid . " - Contact ID: " . $contactid);
		$oldcontactdata = get_query_vals("tblcontacts", "", array("userid" => $_SESSION['uid'], "id" => $id));

		if ($permissions) {
			$permissions = implode(",", $permissions);
		}

		$table = "tblcontacts";
		$array = array("firstname" => $firstname, "lastname" => $lastname, "companyname" => $companyname, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phonenumber, "subaccount" => $subaccount, "permissions" => $permissions, "domainemails" => $domainemails, "generalemails" => $generalemails, "invoiceemails" => $invoiceemails, "productemails" => $productemails, "supportemails" => $supportemails, "affiliateemails" => $affiliateemails);

		if ($password && $password != $aInt->lang("fields", "entertochange")) {
			$array['password'] = generateClientPW($password);
		}

		$where = array("id" => $contactid);
		update_query($table, $array, $where);
		run_hook("ContactEdit", array_merge(array("userid" => $userid, "contactid" => $contactid, "olddata" => $oldcontactdata), $array));
	}

	header("Location: " . $_SERVER['PHP_SELF'] . ("?userid=" . $userid . "&contactid=" . $contactid));
	exit();
}


if ($action == "delete") {
	delete_query("tblcontacts", array("id" => $contactid));
	update_query("tblclients", array("billingcid" => ""), array("billingcid" => $contactid));
	run_hook("ContactDelete", array("userid" => $userid, "contactid" => $contactid));
	header("Location: " . $_SERVER['PHP_SELF'] . ("?userid=" . $userid));
	exit();
}

ob_start();

if ($error) {
	infoBox($aInt->lang("global", "validationerror"), $error);
	echo $infobox;
}

echo "
<form action=\"";
echo $_SERVER['PHP_SELF'];
echo "\" method=\"get\">
<input type=\"hidden\" name=\"userid\" value=\"";
echo $userid;
echo "\">
";
echo $aInt->lang("clientsummary", "contacts");
echo ": ";
echo "<s";
echo "elect name=\"contactid\" onChange=\"submit();\">
";
$result = select_query("tblcontacts", "", array("userid" => $userid), "firstname` ASC,`lastname", "ASC");

while ($data = mysql_fetch_array($result)) {
	$contactlistid = $data['id'];

	if (!$contactid) {
		$contactid = $contactlistid;
	}

	$contactlistfirstname = $data['firstname'];
	$contactlistlastname = $data['lastname'];
	$contactlistemail = $data['email'];
	echo "<option value=\"" . $contactlistid . "\"";

	if ($contactlistid == $contactid) {
		echo " selected";
	}

	echo ">" . $contactlistfirstname . " " . $contactlistlastname . " - " . $contactlistemail . "</option>";
}


if (!$contactid) {
	$contactid = "addnew";
}

echo "<option value=\"addnew\"";

if ($contactid == "addnew") {
	echo " selected";
}

echo ">";
echo $aInt->lang("global", "addnew");
echo "</option>
</select> <input type=\"submit\" value=\"";
echo $aInt->lang("global", "go");
echo "\">
</form>

<br>

";
$jscode = "function deleteContact() {
if (confirm(\"" . $aInt->lang("clients", "deletecontactconfirm") . "\")) {
window.location='" . $PHP_SELF . "?action=delete&userid=" . $userid . "&contactid=" . $contactid . "';
}}";

if ($resetpw) {
	sendMessage("Automated Password Reset", $userid, array("contactid" => $contactid));
	infoBox($aInt->lang("clients", "resetsendpassword"), $aInt->lang("clients", "passwordsuccess"));
	echo $infobox;
}


if ($contactid && $contactid != "addnew") {
	$result = select_query("tblcontacts", "", array("userid" => $userid, "id" => $contactid));
	$data = mysql_fetch_array($result);
	$contactid = $data['id'];
	$firstname = $data['firstname'];
	$lastname = $data['lastname'];
	$companyname = $data['companyname'];
	$email = $data['email'];
	$address1 = $data['address1'];
	$address2 = $data['address2'];
	$city = $data['city'];
	$state = $data['state'];
	$postcode = $data['postcode'];
	$country = $data['country'];
	$phonenumber = $data['phonenumber'];
	$subaccount = $data['subaccount'];
	$password = $data['password'];
	$permissions = explode(",", $data['permissions']);
	$generalemails = $data['generalemails'];
	$productemails = $data['productemails'];
	$domainemails = $data['domainemails'];
	$invoiceemails = $data['invoiceemails'];
	$supportemails = $data['supportemails'];
	$affiliateemails = $data['affiliateemails'];
	$password = ($CONFIG['NOMD5'] ? decrypt($data['password']) : $aInt->lang("fields", "entertochange"));
}


if (!is_array($permissions)) {
	$permissions = array();
}

echo "
<form method=\"post\" action=\"";
echo $_SERVER['PHP_SELF'];
echo "?action=save&userid=";
echo $userid;
echo "&contactid=";
echo $contactid;
echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "firstname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"30\" name=\"firstname\" tabindex=\"1\" value=\"";
echo $firstname;
echo "\"></td><td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "address");
echo " 1</td><td class=\"fieldarea\"><input type=\"text\" size=\"30\" name=\"address1\" tabindex=\"7\" value=\"";
echo $address1;
echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "lastname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"30\" name=\"lastname\" tabindex=\"2\" value=\"";
echo $lastname;
echo "\"></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "address");
echo " 2</td><td class=\"fieldarea\"><input type=\"text\" size=\"30\" name=\"address2\" tabindex=\"8\" value=\"";
echo $address2;
echo "\"> <font color=#cccccc>";
echo "<s";
echo "mall>(";
echo $aInt->lang("global", "optional");
echo ")</small></font></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "companyname");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"30\" name=\"companyname\" tabindex=\"3\" value=\"";
echo $companyname;
echo "\"> <font color=#cccccc>";
echo "<s";
echo "mall>(";
echo $aInt->lang("global", "optional");
echo ")</small></font></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "city");
echo "</td><td class=\"fieldarea\"><input type=\"text\" tabindex=\"9\" size=\"25\" name=\"city\" value=\"";
echo $city;
echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "email");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"35\" name=\"email\"  tabindex=\"4\" value=\"";
echo $email;
echo "\"> <a href=\"http://www.dnsstuff.com/tools/freemail.ch?domain=";
echo $email;
echo "\" target=\"_blank\" title=\"";
echo $aInt->lang("orders", "checkfreeemail");
echo "\"><img src=\"images/info.gif\" border=0 align=absmiddle></a></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "state");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"25\" name=\"state\" tabindex=\"10\" value=\"";
echo $state;
echo "\"></font></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("clients", "activatesubaccount");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" tabindex=\"5\" name=\"subaccount\" id=\"subaccount\"";

if ($subaccount) {
	echo "checked";
}

echo "> <label for=\"subaccount\">";
echo $aInt->lang("global", "ticktoenable");
echo "</label></td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "postcode");
echo "</td><td class=\"fieldarea\"><input type=\"text\" tabindex=\"11\" size=\"14\" name=\"postcode\" value=\"";
echo $postcode;
echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "password");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"20\" name=\"password\" tabindex=\"6\" value=\"";
echo $password;
echo "\" onfocus=\"if(this.value==";
echo $aInt->lang("fields", "entertochange");
echo ")this.value=''\" />";

if ($contactid != "addnew") {
	echo " <a href=\"clientscontacts.php?userid=";
	echo $userid;
	echo "&contactid=";
	echo $contactid;
	echo "&resetpw=true\"><img src=\"images/icons/resetpw.png\" border=\"0\" align=\"absmiddle\" /> ";
	echo $aInt->lang("clients", "resetsendpassword");
	echo "</a>";
}

echo "</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "country");
echo "</td><td class=\"fieldarea\">";
include "../includes/countries.php";
echo getCountriesDropDown($country, "", "12");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "emailnotifications");
echo "</td><td class=\"fieldarea\">
<label><input type=\"checkbox\" name=\"generalemails\" tabindex=\"14\"";

if ($generalemails) {
	echo "checked";
}

echo "> General</label>
<label><input type=\"checkbox\" name=\"invoiceemails\" tabindex=\"15\"";

if ($invoiceemails) {
	echo "checked";
}

echo "> Invoice</label>
<label><input type=\"checkbox\" name=\"supportemails\" tabindex=\"16\"";

if ($supportemails) {
	echo "checked";
}

echo "> Support</label><br />
<label><input type=\"checkbox\" name=\"productemails\" tabindex=\"17\"";

if ($productemails) {
	echo "checked";
}

echo "> Product</label>
<label><input type=\"checkbox\" name=\"domainemails\" tabindex=\"18\"";

if ($domainemails) {
	echo "checked";
}

echo "> Domain</label>
<label><input type=\"checkbox\" name=\"affiliateemails\" tabindex=\"19\"";

if ($affiliateemails) {
	echo "checked";
}

echo "> Affiliate</label>
</td><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "phonenumber");
echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"20\" name=\"phonenumber\" tabindex=\"13\" value=\"";
echo $phonenumber;
echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "permissions");
echo "</td><td class=\"fieldarea\" colspan=\"3\">
<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"50%\" valign=\"top\">
";
$taxindex = 20;
$perms = array("profile", "contacts", "products", "manageproducts", "domains", "managedomains", "invoices", "tickets", "affiliates", "emails", "orders");
foreach ($perms as $perm) {
	++$taxindex;
	echo "<label><input type=\"checkbox\" name=\"permissions[]\" tabindex=\"" . $taxindex . "\" value=\"" . $perm . "\"";

	if (in_array($perm, $permissions)) {
		echo " checked";
	}

	echo " /> " . $aInt->lang("contactpermissions", "perm" . $perm) . "</label><br />";

	if ($perm == "managedomains") {
		echo "</td><td width=\"50%\" valign=\"top\">";
		continue;
	}
}

echo "</td></tr></table>
</td></tr>
</table>

<p align=\"center\">";

if ($contactid != "addnew") {
	echo "<input type=\"submit\" value=\"";
	echo $aInt->lang("global", "savechanges");
	echo "\" class=\"btn btn-primary\" tabindex=\"";
	echo $taxindex++;
	echo "\" /> <input type=\"reset\" value=\"";
	echo $aInt->lang("global", "cancelchanges");
	echo "\" class=\"button\" tabindex=\"";
	echo $taxindex++;
	echo "\" /><br />
<a href=\"#\" onClick=\"deleteContact();return false\" style=\"color:#cc0000\"><b>";
	echo $aInt->lang("global", "delete");
	echo "</b></a>";
}
else {
	echo "<input type=\"submit\" value=\"";
	echo $aInt->lang("clients", "addcontact");
	echo "\" class=\"btn btn-primary\" tabindex=\"";
	echo $taxindex++;
	echo "\" /> <input type=\"reset\" value=\"";
	echo $aInt->lang("global", "cancelchanges");
	echo "\" class=\"button\" tabindex=\"";
	echo $taxindex++;
	echo "\" />";
}

echo "</p>

</form>

  </div>
</div>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>