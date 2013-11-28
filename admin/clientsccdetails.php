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
$aInt = new WHMCS_Admin("View Credit Card Details");
$aInt->title = $aInt->lang("clients", "ccdetails");
$aInt->requiredFiles(array("ccfunctions", "clientfunctions"));
ob_start();
$ccstoredisabled = $whmcs->get_config("CCNeverStore");

if ($ccstoredisabled) {
	echo "<p>" . $aInt->lang("clients", "ccstoredisabled") . "</p><p align=\"center\"><input type=\"button\" value=\"" . $aInt->lang("addons", "closewindow") . "\" class=\"button\" onclick=\"window.close()\" /></p>";
}
else {
	$validhash = "";

	if ($action == "clear") {
		check_token("WHMCS.admin.default");
		checkPermission("Update/Delete Stored Credit Card");
		updateCCDetails($userid, "", "", "", "", "", "", "", true);
	}
	else {
		if ($_POST['action'] == "save") {
			check_token("WHMCS.admin.default");
			checkPermission("Update/Delete Stored Credit Card");
			$errormessage = updateCCDetails($userid, $cctype, $ccnumber, $cardcvv, $ccexpirymonth . $ccexpiryyear, $ccstartmonth . $ccstartyear, $ccissuenum);

			if (!$errormessage) {
				$errormessage = "<B>" . $aInt->lang("global", "success") . "</B> - " . $aInt->lang("clients", "ccdetailschanged");
			}
		}
	}


	if ($fullcc) {
		check_token("WHMCS.admin.default");
		checkPermission("Decrypt Full Credit Card Number");
		$referrer = $_SERVER['HTTP_REFERER'];
		$pos = strpos($referrer, "?");

		if ($pos) {
			$referrer = substr($referrer, 0, $pos);
		}

		$adminfolder = $whmcs->get_admin_folder_name();

		if ($CONFIG['SystemURL'] . ("/" . $adminfolder . "/clientsccdetails.php") != $referrer && $CONFIG['SystemSSLURL'] . ("/" . $adminfolder . "/clientsccdetails.php") != $referrer) {
			echo "<p>" . $aInt->lang("global", "invalidaccessattempt") . "</p>";
			exit();
		}


		if ($cchash != $cc_encryption_hash) {
			$errormessage = "<B>" . $aInt->lang("global", "error") . "</B> - " . $aInt->lang("clients", "incorrecthash");
		}
		else {
			$validhash = "true";
			logActivity("Viewed Decrypted Credit Card Number for User ID " . $userid);
		}
	}


	if ($errormessage) {
		echo "<p align=\"center\" style=\"color:#cc0000;\">" . str_replace("<li>", " - ", $errormessage) . "</p>";
	}

	$data = getCCDetails($userid);
	$cardtype = $data['cardtype'];
	$cardnum = ($validhash ? $data['fullcardnum'] : $data['cardnum']);
	$cardexp = $data['expdate'];
	$cardissuenum = $data['issuenumber'];
	$cardstart = $data['startdate'];
	$gatewayid = $data['gatewayid'];
	echo "<table>
<tr><td colspan=\"2\"><b>";
	echo $aInt->lang("clients", "existingccdetails");
	echo "</b></td></tr>
<tr><td>";
	echo $aInt->lang("fields", "cardtype");
	echo ":</td><td>";
	echo $cardtype;
	echo "</td></tr>
<tr><td>";
	echo $aInt->lang("fields", "cardnum");
	echo ":</td><td>";
	echo $cardnum;

	if ($gatewayid) {
		echo " *";
	}

	echo "</td></tr>
<tr><td>";
	echo $aInt->lang("fields", "expdate");
	echo ":</td><td>";
	echo $cardexp;
	echo "</td></tr>
";

	if ($cardissuenum) {
		echo "<tr><td>";
		echo $aInt->lang("fields", "issueno");
		echo ":</td><td>";
		echo $cardissuenum;
		echo "</td></tr>";
	}


	if ($cardstart) {
		echo "<tr><td>";
		echo $aInt->lang("fields", "startdate");
		echo ":</td><td>";
		echo $cardstart;
		echo "</td></tr>";
	}

	echo "<tr><td colspan=\"2\"><br><b>";
	echo $aInt->lang("clients", "viewfullcardno");
	echo "</b></td></tr>
<tr><td colspan=\"2\">
";

	if ($data['fullcardnum']) {
		echo $aInt->lang("clients", "entercchash");
		echo "<br><br><div align=\"center\"><form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "\">";
		generate_token();
		echo "<input type=\"hidden\" name=\"userid\" value=\"";
		echo $userid;
		echo "\"><input type=\"hidden\" name=\"fullcc\" value=\"true\"><textarea name=\"cchash\" cols=\"40\" rows=\"3\"></textarea><br><input type=\"submit\" value=\"";
		echo $aInt->lang("global", "submit");
		echo "\" class=\"button\" /></div></form>
";
	}
	else {
		if ($gatewayid) {
			echo "<strong>" . $aInt->lang("fields", "gatewayid") . "</strong><br />\"" . $gatewayid . "\"<br /><br />" . $aInt->lang("clients", "ccstoredremotely");
		}
	}

	echo "</td></tr>
<tr><td colspan=\"2\"><br><b>";
	echo $aInt->lang("clients", "enternewcc");
	echo "</b></td></tr>
<tr><td><form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "\">
<input type=\"hidden\" name=\"action\" value=\"save\">
<input type=\"hidden\" name=\"userid\" value=\"";
	echo $userid;
	echo "\">
";
	generate_token();
	echo $aInt->lang("fields", "cardtype");
	echo ":</td><td>";
	echo "<s";
	echo "elect name=\"cctype\">
";
	$acceptedcctypes = $CONFIG['AcceptedCardTypes'];
	$acceptedcctypes = explode(",", $acceptedcctypes);
	foreach ($acceptedcctypes as $cctype) {
		echo "<option>" . $cctype . "</option>";
	}

	echo "</select></td></tr>
<tr><td nowrap>";
	echo $aInt->lang("fields", "cardnum");
	echo ":</td><td><input type=\"text\" name=\"ccnumber\" size=\"25\" autocomplete=\"off\"></td></tr>
<tr><td>";
	echo $aInt->lang("fields", "expdate");
	echo ":</td><td><input type=\"text\" name=\"ccexpirymonth\" size=\"2\" maxlength=\"2\">/<input type=\"text\" name=\"ccexpiryyear\" size=\"2\" maxlength=\"2\"> (";
	echo $aInt->lang("fields", "mmyy");
	echo ")</td></tr>
";

	if ($CONFIG['ShowCCIssueStart']) {
		echo "<tr><td>";
		echo $aInt->lang("fields", "issueno");
		echo ":</td><td><input type=\"text\" name=\"ccissuenum\" size=\"5\" maxlength=\"4\"></td></tr>
<tr><td>";
		echo $aInt->lang("fields", "startdate");
		echo ":</td><td><input type=\"text\" name=\"ccstartmonth\" size=\"2\" maxlength=\"2\">/<input type=\"text\" name=\"ccstartyear\" size=\"2\" maxlength=\"2\"> (";
		echo $aInt->lang("fields", "mmyy");
		echo ")</td></tr>
";
	}

	echo "<tr><td nowrap>";
	echo $aInt->lang("fields", "cardcvv");
	echo ":</td><td><input type=\"text\" name=\"cardcvv\" size=\"5\" autocomplete=\"off\"></td></tr>
</table>
";
	echo "<s";
	echo "cript language=\"JavaScript\">
function confirmClear() {
if (confirm(\"";
	echo $aInt->lang("clients", "ccdeletesure");
	echo "\")) {
window.location='";
	echo $PHP_SELF;
	echo "?userid=";
	echo $userid;
	echo "&action=clear";
	echo generate_token("link");
	echo "';
}}
</script>
<p align=center><input type=\"submit\" value=\"";
	echo $aInt->lang("global", "savechanges");
	echo "\" class=\"button\" /> <input type=\"button\" value=\"";
	echo $aInt->lang("addons", "closewindow");
	echo "\" class=\"button\" onclick=\"window.close()\" /><br /><input type=\"button\" value=\"";
	echo $aInt->lang("clients", "cleardetails");
	echo "\" class=\"button\" onClick=\"confirmClear();return false;\" style=\"color:#cc0000;\" /></p>
</form>

";
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->displayPopUp();
?>