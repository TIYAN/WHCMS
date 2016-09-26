<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 20.1.1
 * @ Author   : TIYAN
 * @ Release on : 2016-12-24
 * @ Website  : https://hostinger-indonesia.blogspot.co.id/
 *
 **/

function eu_vat_config() {
	$configarray = array("name" => "EU VAT Addon", "version" => "2.0", "author" => "WHMCS", "language" => "english", "description" => "This addon allows you to configure a number of additional invoice/billing related options specific to EU invoicing & VAT requirements", "fields" => array());
	return $configarray;
}

function eu_vat_update_config_field($field) {
	global $modulevars;

	if (isset($modulevars[$field])) {
		update_query("tbladdonmodules", array("value" => $_POST[$field]), array("module" => "eu_vat", "setting" => $field));
		return null;
	}

	insert_query("tbladdonmodules", array("module" => "eu_vat", "setting" => $field, "value" => $_POST[$field]));
}

function eu_vat_output($vars) {
	global $CONFIG;
	global $aInt;

	$modulevars = array();
	$result = select_query("tbladdonmodules", "", array("module" => "eu_vat"));

	while ($data = mysql_fetch_array($result)) {
		$modulevars[$data['setting']] = $data['value'];
	}


	if ($_REQUEST['action'] == "save") {
		eu_vat_update_config_field("enablevalidation");
		eu_vat_update_config_field("vatcustomfield");
		eu_vat_update_config_field("homecountry");
		eu_vat_update_config_field("taxexempt");
		eu_vat_update_config_field("notaxexempthome");
		eu_vat_update_config_field("enablecustominvoicenum");
		eu_vat_update_config_field("custominvoicenumformat");
		eu_vat_update_config_field("custominvoicenumber");
		eu_vat_update_config_field("custominvoicenumautoreset");
		eu_vat_update_config_field("sequentialpaidautoreset");
		eu_vat_update_config_field("enableinvoicedatepayment");
		update_query("tblconfiguration", array("value" => $_POST['enblesequentialpaidinvoice']), array("setting" => "SequentialInvoiceNumbering"));
		update_query("tblconfiguration", array("value" => $_POST['sequentialpaidformat']), array("setting" => "SequentialInvoiceNumberFormat"));
		update_query("tblconfiguration", array("value" => $_POST['sequentialpaidnumber']), array("setting" => "SequentialInvoiceNumberValue"));
		redir("module=eu_vat");
	}

	$countries = array(ID ,AT, BE, BG, CY, CZ, DE, DK, EE, ES, FI, FR, GB, GR, HU, IE, IT, LT, LU, LV, MT, NL, PL, PT, RO, SE, SI, SK);

	if ($_REQUEST['action'] == "setupvat") {
		full_query("TRUNCATE tbltax");
		foreach ($countries as $country) {
			insert_query("tbltax", array("level" => "1", "name" => $_POST['vatlabel'], "state" => "", "country" => $country, "taxrate" => $_POST['vatrate']));
		}

		redir("module=eu_vat");
	}

	$LANG = $vars['_lang'];
	$customfields = array("Choose One...");
	$result = select_query("tblcustomfields", "", array("type" => "client"));

	while ($data = mysql_fetch_array($result)) {
		$customfields[] = $data['fieldname'];
	}


	if (!count($customfields)) {
		$customfields[] = "No Custom Fields Found";
	}

	echo "
<p>";
	echo $LANG['introtext'];
	echo "</p>

<form method=\"post\" action=\"";
	echo $vars['modulelink'];
	echo "\">
<input type=\"hidden\" name=\"action\" value=\"save\" />

<p><b>";
	echo $LANG['vatvalidationheading'];
	echo "</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"25%\" class=\"fieldlabel\">";
	echo $LANG['enable'];
	echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"enablevalidation\"";

	if ($modulevars['enablevalidation']) {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['validationdesc'];
	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $LANG['vatcustomfield'];
	echo "</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"vatcustomfield\">";
	foreach ($customfields as $v) {
		echo "<option";

		if ($v == $modulevars['vatcustomfield']) {
			echo " selected";
		}

		echo ">" . $v . "</option>";
	}

	echo "</select> ";
	echo $LANG['vatcustomfielddesc'];
	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $LANG['homecountry'];
	echo "</td><td class=\"fieldarea\">";
	echo "<s";
	echo "elect name=\"homecountry\">";
	foreach ($countries as $v) {
		echo "<option";

		if ($v == $modulevars['homecountry']) {
			echo " selected";
		}

		echo ">" . $v . "</option>";
	}

	echo "</select> ";
	echo $LANG['homecountrydesc'];
	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $LANG['taxexempt'];
	echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"taxexempt\"";

	if ($modulevars['taxexempt']) {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['taxexemptdesc'];
	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $LANG['homecountryexcl'];
	echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"notaxexempthome\"";

	if ($modulevars['notaxexempthome']) {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['homecountryexcldesc'];
	echo "</td></tr>
</table>

<p><b>";
	echo $LANG['custinvoiceformatheading'];
	echo "</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"25%\" class=\"fieldlabel\">";
	echo $LANG['enable'];
	echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"enablecustominvoicenum\"";

	if ($modulevars['enablecustominvoicenum']) {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['custinvoiceformatenabledesc'];
	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $LANG['custinvoiceformatnumbering'];
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"custominvoicenumformat\" size=\"30\" value=\"";
	echo $modulevars['custominvoicenumformat'];
	echo "\" /> ";
	echo $LANG['custinvoiceformatfields'];
	echo ": {YEAR} {MONTH} {DAY} {NUMBER}</td></tr>
<tr><td width=\"25%\" class=\"fieldlabel\">";
	echo $LANG['custinvoiceformatnextnumber'];
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"custominvoicenumber\" size=\"10\" value=\"";
	echo $modulevars['custominvoicenumber'];
	echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $LANG['custinvoiceformatautoreset'];
	echo "</td><td class=\"fieldarea\"><input type=\"radio\" name=\"custominvoicenumautoreset\" value=\"\"";

	if ($modulevars['custominvoicenumautoreset'] == "") {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['custinvoiceformatautoresetnever'];
	echo " <input type=\"radio\" name=\"custominvoicenumautoreset\" value=\"monthly\"";

	if ($modulevars['custominvoicenumautoreset'] == "monthly") {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['custinvoiceformatautoresetmonthly'];
	echo " <input type=\"radio\" name=\"custominvoicenumautoreset\" value=\"annually\"";

	if ($modulevars['custominvoicenumautoreset'] == "annually") {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['custinvoiceformatautoresetannually'];
	echo "</td></tr>
</table>

<p><b>";
	echo $LANG['seqpaidnumberheading'];
	echo "</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"25%\" class=\"fieldlabel\">";
	echo $LANG['enable'];
	echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"enblesequentialpaidinvoice\"";

	if ($CONFIG['SequentialInvoiceNumbering']) {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['seqpaidnumberenabledesc'];
	echo "</td></tr>
<tr><td width=\"25%\" class=\"fieldlabel\">";
	echo $LANG['seqpaidnumberformat'];
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"sequentialpaidformat\" size=\"30\" value=\"";
	echo $CONFIG['SequentialInvoiceNumberFormat'];
	echo "\" /> ";
	echo $LANG['custinvoiceformatfields'];
	echo ": {YEAR} {MONTH} {DAY} {NUMBER}</td></tr>
<tr><td width=\"25%\" class=\"fieldlabel\">";
	echo $LANG['seqpaidnumbernextnumber'];
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"sequentialpaidnumber\" size=\"10\" value=\"";
	echo $CONFIG['SequentialInvoiceNumberValue'];
	echo "\" /></td></tr>
<tr><td class=\"fieldlabel\">";
	echo $LANG['custinvoiceformatautoreset'];
	echo "</td><td class=\"fieldarea\"><input type=\"radio\" name=\"sequentialpaidautoreset\" value=\"\"";

	if ($modulevars['sequentialpaidautoreset'] == "") {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['custinvoiceformatautoresetnever'];
	echo " <input type=\"radio\" name=\"sequentialpaidautoreset\" value=\"monthly\"";

	if ($modulevars['sequentialpaidautoreset'] == "monthly") {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['custinvoiceformatautoresetmonthly'];
	echo " <input type=\"radio\" name=\"sequentialpaidautoreset\" value=\"annually\"";

	if ($modulevars['sequentialpaidautoreset'] == "annually") {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['custinvoiceformatautoresetannually'];
	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $LANG['seqpaidnumberinvoicedate'];
	echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"enableinvoicedatepayment\"";

	if ($modulevars['enableinvoicedatepayment']) {
		echo " checked";
	}

	echo " /> ";
	echo $LANG['seqpaidnumberinvoicedatedesc'];
	echo "</td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("global", "savechanges");
	echo "\" /></p>

</form>

<p><b>";
	echo $LANG['autovatrulessetupheading'];
	echo "</b></p>

<p>";
	echo $LANG['autovatrulessetupdesc'];
	echo "</p>

<form method=\"post\" action=\"";
	echo $vars['modulelink'];
	echo "\">
<input type=\"hidden\" name=\"action\" value=\"setupvat\" />
<p align=\"center\">";
	echo $LANG['vatlabel'];
	echo ": <input type=\"text\" name=\"vatlabel\" value=\"VAT\" size=\"10\" /> ";
	echo $LANG['vatrate'];
	echo ": <input type=\"text\" name=\"vatrate\" value=\"20\" size=\"3\" />% <input type=\"submit\" value=\"";
	echo $aInt->lang("global", "submit");
	echo "\" /></p>
</form>

<br /><br />

";
}

?>
