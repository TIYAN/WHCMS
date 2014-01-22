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
$aInt = new WHMCS_Admin("Edit Clients Domains");
$aInt->title = $aInt->lang("domains", "modifycontact");
$aInt->sidebar = "clients";
$aInt->icon = "clientsprofile";
$aInt->requiredFiles(array("clientfunctions", "registrarfunctions"));
ob_start();
$result = select_query("tbldomains", "", array("id" => $domainid));
$data = mysql_fetch_array($result);
$userid = $data['userid'];
$domain = $data['domain'];
$registrar = $data['registrar'];
$registrationperiod = $data['registrationperiod'];
$contactsarray = array();
$result = select_query("tblcontacts", "id,firstname,lastname", array("userid" => $userid, "address1" => array("sqltype" => "NEQ", "value" => "")), "firstname` ASC,`lastname", "ASC");

while ($data = mysql_fetch_assoc($result)) {
	$contactsarray[] = array("id" => $data['id'], "name" => $data['firstname'] . " " . $data['lastname']);
}

$domainparts = explode(".", $domain, 2);
$params['domainid'] = $domainid;
$params['sld'] = $domainparts[0];
$params['tld'] = $domainparts[1];
$params['regperiod'] = $registrationperiod;
$params['registrar'] = $registrar;

if ($action == "save") {
	check_token("WHMCS.admin.default");
	foreach ($wc as $wc_key => $wc_val) {

		if ($wc_val == "contact") {
			$selctype = $sel[$wc_key][0];
			$selcid = substr($sel[$wc_key], 1);

			if ($selctype == "c") {
				$tmpcontactdetails = get_query_vals("tblcontacts", "", array("userid" => $userid, "id" => $selcid));
			}
			else {
				if ($selctype == "u") {
					$tmpcontactdetails = get_query_vals("tblclients", "", array("id" => $selcid));
				}
			}

			require ROOTDIR . "/includes/countriescallingcodes.php";
			$phonenumber = $tmpcontactdetails['phonenumber'];
			$phonenumber = preg_replace("/[^0-9]/", "", $phonenumber);
			$countrycode = $tmpcontactdetails['country'];
			$countrycode = $countrycallingcodes[$countrycode];
			$tmpcontactdetails['phonenumber'] = "+" . $countrycode . "." . $phonenumber;
			$contactdetails[$wc_key]["First Name"] = $tmpcontactdetails['firstname'];
			$contactdetails[$wc_key]["Last Name"] = $tmpcontactdetails['lastname'];
			$contactdetails[$wc_key]["Full Name"] = $contactdetails[$wc_key]["Contact Name"] = $tmpcontactdetails['firstname'] . " " . $tmpcontactdetails['lastname'];
			$contactdetails[$wc_key]['Email'] = $contactdetails[$wc_key]["Email Address"] = $tmpcontactdetails['email'];
			$contactdetails[$wc_key]["Job Title"] = $wc_key;
			$contactdetails[$wc_key]["Company Name"] = $contactdetails[$wc_key]["Organisation Name"] = $tmpcontactdetails['companyname'];
			$contactdetails[$wc_key]['Address'] = $contactdetails[$wc_key]["Address 1"] = $contactdetails[$wc_key]['Street'] = $tmpcontactdetails['address1'];
			$contactdetails[$wc_key]["Address 2"] = $tmpcontactdetails['address2'];
			$contactdetails[$wc_key]['City'] = $tmpcontactdetails['city'];
			$contactdetails[$wc_key]['State'] = $contactdetails[$wc_key]['County'] = $contactdetails[$wc_key]['Region'] = $tmpcontactdetails['state'];
			$contactdetails[$wc_key]['Postcode'] = $contactdetails[$wc_key]["ZIP Code"] = $contactdetails[$wc_key]['ZIP'] = $tmpcontactdetails['postcode'];
			$contactdetails[$wc_key]['Country'] = $tmpcontactdetails['country'];
			$contactdetails[$wc_key]['Phone'] = $contactdetails[$wc_key]["Phone Number"] = $tmpcontactdetails['phonenumber'];
			continue;
		}
	}

	$params['contactdetails'] = $contactdetails;
	$values = RegSaveContactDetails($params);

	if ($values['error']) {
		infoBox($aInt->lang("domains", "registrarerror"), $values['error'], "error");
	}
}

$contactdetails = RegGetContactDetails($params);

if ($contactdetails['error'] != "") {
	infoBox($aInt->lang("domains", "registrarerror"), $contactdetails['error']);
	$error = "1";
}

echo "<s";
echo "cript language=\"javascript\">
function usedefaultwhois(id) {
	jQuery(\".\"+id.substr(0,id.length-1)+\"customwhois\").attr(\"disabled\", true);
	jQuery(\".\"+id.substr(0,id.length-1)+\"defaultwhois\").attr(\"disabled\", false);
	jQuery('#'+id.substr(0,id.length-1)+'1').attr(\"checked\", \"checked\");
}
function usecustomwhois(id) {
	jQuery(\".\"+id.substr(0,id.length-1)+\"customwhois\").attr(\"disabled\", false);
";
echo "
	jQuery(\".\"+id.substr(0,id.length-1)+\"defaultwhois\").attr(\"disabled\", true);
	jQuery('#'+id.substr(0,id.length-1)+'2').attr(\"checked\", \"checked\");
}
</script>
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?domainid=";
echo $domainid;
echo "&action=save\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "registrar");
echo "</td><td class=\"fieldarea\">";
echo ucfirst($registrar);
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "domain");
echo "</td><td class=\"fieldarea\">";
echo $domain;
echo "</td></tr>
</table>

";
echo $infobox;

if ($error != "1") {
	$i = 0;
	foreach ($contactdetails as $contactdetail => $values) {
		echo "
<p><b>";
		echo $contactdetail;
		echo "</b>";

		if ($i != 0) {
			echo " - <a href=\"clientsdomaincontacts.php?domainid=";
			echo $domainid;
			echo "#\">";
			echo $aInt->lang("global", "top");
			echo "</a>";
		}

		++$i;
		echo "</p>

<p><input type=\"radio\" name=\"wc[";
		echo $contactdetail;
		echo "]\" id=\"";
		echo $contactdetail;
		echo "1\" value=\"contact\" onclick=\"usedefaultwhois(id)\" /> <label for=\"";
		echo $contactdetail;
		echo "1\">";
		echo $aInt->lang("domains", "domaincontactusexisting");
		echo "</label></p>
    <table id=\"";
		echo $contactdetail;
		echo "defaultwhois\">
      <tr>
        <td width=\"150\" align=\"right\">";
		echo $aInt->lang("domains", "domaincontactchoose");
		echo "</td>
        <td>";
		echo "<s";
		echo "elect name=\"sel[";
		echo $contactdetail;
		echo "]\" id=\"";
		echo $contactdetail;
		echo "3\" class=\"";
		echo $contactdetail;
		echo "defaultwhois\" onclick=\"usedefaultwhois(id)\">
            <option value=\"u";
		echo $userid;
		echo "\">";
		echo $aInt->lang("domains", "domaincontactprimary");
		echo "</option>
            ";
		foreach ($contactsarray as $subcontactsarray) {
			echo "            <option value=\"c";
			echo $subcontactsarray['id'];
			echo "\">";
			echo $subcontactsarray['name'];
			echo "</option>
            ";
		}

		echo "          </select></td>
      </tr>
  </table>
<p><input type=\"radio\" name=\"wc[";
		echo $contactdetail;
		echo "]\" id=\"";
		echo $contactdetail;
		echo "2\" value=\"custom\" onclick=\"usecustomwhois(id)\" checked /> <label for=\"";
		echo $contactdetail;
		echo "2\">";
		echo $aInt->lang("domains", "domaincontactusecustom");
		echo "</label></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\" id=\"";
		echo $contactdetail;
		echo "customwhois\">
";
		foreach ($values as $name => $value) {
			echo "<tr><td width=\"20%\" class=\"fieldlabel\">";
			echo $name;
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"contactdetails[";
			echo $contactdetail;
			echo "][";
			echo $name;
			echo "]\" value=\"";
			echo $value;
			echo "\" size=\"30\" class=\"";
			echo $contactdetail;
			echo "customwhois\"></td></tr>
";
		}

		echo "</table>

";
	}
}

echo "
<p align=center><input type=\"submit\" value=\"";
echo $aInt->lang("global", "savechanges");
echo "\" class=\"button\"> <input type=\"button\" value=\"";
echo $aInt->lang("global", "goback");
echo "\" class=\"button\" onClick=\"window.location='clientsdomains.php?userid=";
echo $userid;
echo "&domainid=";
echo $domainid;
echo "'\"></p>

</form>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>