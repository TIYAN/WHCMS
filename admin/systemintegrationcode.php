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
$aInt = new WHMCS_Admin("View Integration Code");
$aInt->title = $aInt->lang("system", "integrationcode");
$aInt->sidebar = "utilities";
$aInt->icon = "integrationcode";
$aInt->requiredFiles(array("domainfunctions"));
$currency = getCurrency();
$tlds = getTLDList();
$systemurl = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
ob_start();
echo "
<p>";
echo $aInt->lang("system", "integrationinfo");
echo "</p>

<p>";
echo $aInt->lang("system", "widgetsinfo");
echo " <a href=\"http://docs.whmcs.com/Widgets\" target=\"_blank\">http://docs.whmcs.com/Widgets</a></p>

<br />

<h2>";
echo $aInt->lang("system", "intclientlogin");
echo "</h2>
<p>";
echo $aInt->lang("system", "intclientlogininfo");
echo "</p>
<textarea rows=\"6\" style=\"width:100%;\"><form method=\"post\" action=\"";
echo $systemurl;
echo "/dologin.php\">
Email Address: <input type=\"text\" name=\"username\" size=\"50\" /><br />
Password: <input type=\"password\" name=\"password\" size=\"20\" /><br />
<input type=\"submit\" value=\"Login\" />
</form></textarea>
<br /><br />

<h2>";
echo $aInt->lang("system", "intdalookup");
echo "</h2>
<p>";
echo $aInt->lang("system", "intdalookupinfo");
echo "</p>
<textarea rows=\"10\" style=\"width:100%;\"><form action=\"";
echo $systemurl;
echo "/domainchecker.php\" method=\"post\">
<input type=\"hidden\" name=\"direct\" value=\"true\" />
Domain: <input type=\"text\" name=\"domain\" size=\"20\" /> ";
echo "<s";
echo "elect name=\"ext\">
";
foreach ($tlds as $tld) {
	echo "<option>" . $tld . "</option>
";
}

echo "</select>
<input type=\"submit\" value=\"Go\" />
</form>
</textarea>
<br /><br />

<h2>";
echo $aInt->lang("system", "intdo");
echo "</h2>
<p>";
echo $aInt->lang("system", "intdoinfo");
echo "</p>
<textarea rows=\"10\" style=\"width:100%;\"><form action=\"";
echo $systemurl;
echo "/cart.php?a=add&domain=register\" method=\"post\">
Domain: <input type=\"text\" name=\"sld\" size=\"20\" /> ";
echo "<s";
echo "elect name=\"tld\">
";
foreach ($tlds as $tld) {
	echo "<option>" . $tld . "</option>
";
}

echo "</select>
<input type=\"submit\" value=\"Go\" />
</form>
</textarea>
<br /><br />

<h2>";
echo $aInt->lang("system", "intuserreg");
echo "</h2>
<p>";
echo $aInt->lang("system", "intuserreginfo");
echo "</p>
<textarea rows=\"2\" style=\"width:100%;\"><a href=\"";
echo $systemurl;
echo "/register.php\">Click here to register with us</a></textarea>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>