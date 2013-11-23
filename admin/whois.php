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
$aInt = new WHMCS_Admin("WHOIS Lookups");
$aInt->title = $aInt->lang("whois", "title");
$aInt->sidebar = "utilities";
$aInt->icon = "domains";
$aInt->requiredFiles(array("domainfunctions", "whoisfunctions"));
ob_start();
$whoisservers = file_get_contents("../includes/whoisservers.php");
$whoisservers = explode("\n", $whoisservers);

foreach ($whoisservers as $value) {
	$value = explode("|", $value);
	$mtlds[] = trim(strip_tags($value[0]));
}


if ($domain) {
	$domain = strtolower($domain);
	$domainbits = explode(".", $domain, 2);
	$sld = $domainbits[0];
	$tld = "." . $domainbits[1];
}


if ($action == "checkavailability") {
	$result = lookupDomain($sld, $tld);
	echo $result['result'];
	exit();
}

echo "
<form method=\"post\" action=\"";
echo $_SERVER['PHP_SELF'];
echo "\">
<p align=\"center\" style=\"font-size:18px;\">www. <input type=\"text\" name=\"domain\" value=\"";
echo $domain;
echo "\" size=\"40\" style=\"font-size:18px;\" /> <input type=\"submit\" value=\"Lookup Domain\" class=\"btn\" /></p>
</form>

";

if ($sld) {
	$checkdomain = $sld . $tld;

	if (!in_array($tld, $mtlds)) {
		echo "<p align=\"center\" style=\"font-size:18px;color:#cc0000;\">" . sprintf($aInt->lang("whois", "invalidtld"), $tld) . "</p>";
	}
	else {
		$result = lookupDomain($sld, $tld);

		if ($result['result'] == "available") {
			echo "<p align=\"center\" style=\"font-size:18px;color:#669900;\">" . sprintf($aInt->lang("whois", "available"), $checkdomain) . "</p>";
		}
		else {
			if ($result['result'] == "error") {
				echo "<p align=\"center\" style=\"font-size:18px;color:#cc0000;\">" . $aInt->lang("whois", "error") . ("</p><p align=\"center\">" . $result['errordetail'] . "</p>");
			}
			else {
				echo "<p align=\"center\" style=\"font-size:18px;color:#cc0000;\">" . sprintf($aInt->lang("whois", "unavailable"), $checkdomain) . "</p>";
				echo "<p><strong>" . $aInt->lang("whois", "whois") . ("</strong></p><p>" . $result['whois'] . "</p>");
			}
		}
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>