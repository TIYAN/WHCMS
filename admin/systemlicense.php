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
$aInt = new WHMCS_Admin("Main Homepage");
$aInt->title = $aInt->lang("license", "title");
$aInt->sidebar = "help";
$aInt->icon = "support";
ob_start();
echo "
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
echo $aInt->lang("license", "regto");
echo "</td><td class=\"fieldarea\">";
echo $licensing->getKeyData("registeredname");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("license", "key");
echo "</td><td class=\"fieldarea\">";
echo $whmcs->get_license_key();
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("license", "type");
echo "</td><td class=\"fieldarea\">";
echo $licensing->getKeyData("productname");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("license", "validdomain");
echo "</td><td class=\"fieldarea\">";
echo $licensing->getKeyData("validdomains");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("license", "validip");
echo "</td><td class=\"fieldarea\">";
echo $licensing->getKeyData("validips");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("license", "validdir");
echo "</td><td class=\"fieldarea\">";
echo $licensing->getKeyData("validdirs");
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("license", "brandingremoval");
echo "</td><td class=\"fieldarea\">";
echo $licensing->getBrandingRemoval() ? "Yes" : "No";
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("license", "addons");
echo "</td><td class=\"fieldarea\">";
echo count($licensing->getActiveAddons()) ? implode("<br />", $licensing->getActiveAddons()) : "None";
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("license", "created");
echo "</td><td class=\"fieldarea\">";
echo date("l, jS F Y", strtotime($licensing->getKeyData("regdate")));
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("license", "expires");
echo "</td><td class=\"fieldarea\">";
echo $licensing->getExpiryDate(true);
echo "</td></tr>
</table>

<p>";
echo $aInt->lang("license", "reissue1");
echo " <a href=\"http://www.mtimer.cn/\">http://www.mtimer.cn/release/25-whmcs-full-decoded-nulled-by-mtimer.html</a> ";
echo $aInt->lang("license", "reissue2");
echo "</p>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>