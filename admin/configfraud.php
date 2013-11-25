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
$aInt = new WHMCS_Admin("Configure Fraud Protection");
$aInt->title = $aInt->lang("fraud", "title");
$aInt->sidebar = "config";
$aInt->icon = "configbans";
$aInt->helplink = "Fraud Protection";
$aInt->requiredFiles(array("fraudfunctions", "modulefunctions"));
ob_start();
$fraudmodules = array();
$dh = opendir(ROOTDIR . "/modules/fraud/");

while (false !== $file = readdir($dh)) {
	if (is_file(ROOTDIR . ("/modules/fraud/" . $file . "/" . $file . ".php"))) {
		$fraudmodules[] = $file;
	}
}

closedir($dh);

if ($fraud && in_array($fraud, $fraudmodules)) {
	if (!isValidforPath($fraud)) {
		exit("Invalid Fraud Module Name");
	}

	include "../modules/fraud/" . $fraud . "/" . $fraud . ".php";
	$configarray = getConfigArray();
	foreach ($configarray as $regconfoption => $values) {
		$result = select_query("tblfraud", "", array("fraud" => $fraud, "setting" => $regconfoption));
		$num_rows = mysql_num_rows($result);

		if ($num_rows == "0") {
			insert_query("tblfraud", array("fraud" => $fraud, "setting" => $regconfoption, "value" => $values['Value']));
			continue;
		}
	}


	if ($action == "save") {
		check_token("WHMCS.admin.default");
		foreach ($configarray as $regconfoption => $values) {
			$regconfoption2 = str_replace(" ", "_", $regconfoption);
			update_query("tblfraud", array("value" => trim($_POST[$regconfoption2])), array("fraud" => $fraud, "setting" => $regconfoption));
		}

		infoBox($aInt->lang("fraud", "changesuccess"), $aInt->lang("fraud", "changesuccessinfo"));
	}

	echo $infobox;
}
else {
	$fraud = "";
}

echo "<p>" . $aInt->lang("fraud", "info") . "</p>";
echo "<form method=\"post\" action=\"" . $PHP_SELF . "\"><p>" . $aInt->lang("fraud", "choose") . ": <select name=\"fraud\" onChange=\"submit();\">";
foreach ($fraudmodules as $file) {
	echo "<option value=\"" . $file . "\"";

	if ($fraud == $file) {
		echo " selected";
	}

	echo ">" . TitleCase(str_replace("_", " ", $file)) . "</option>";
}

echo "</select> <input type=\"submit\" value=\" " . $aInt->lang("global", "go") . " \" class=\"button\"></p></form>";

if ($fraud) {
	$configarray = getConfigArray();
	$configvalues = getFraudConfigOptions($fraud);
	echo "
<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "?action=save\">
<input type=\"hidden\" name=\"fraud\" value=\"";
	echo $fraud;
	echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
";
	foreach ($configarray as $regconfoption => $values) {

		if (!$values['FriendlyName']) {
			$values['FriendlyName'] = $regconfoption;
		}

		$values['Name'] = $regconfoption;
		$values['Value'] = $configvalues[$regconfoption];
		echo "<tr><td class=\"fieldlabel\">" . $values['FriendlyName'] . "</td><td class=\"fieldarea\">" . moduleConfigFieldOutput($values) . "</td></tr>";
	}

	echo "</table>

<p align=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("global", "savechanges");
	echo "\" class=\"button\" /></p>

</form>

";
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>