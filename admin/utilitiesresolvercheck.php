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

function assignedips_trim($value) {
	$value = trim($value);
}

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Domain Resolver Checker");
$aInt->title = $aInt->lang("utilitiesresolvercheck", "domainresolverchecktitle");
$aInt->sidebar = "utilities";
$aInt->icon = "domainresolver";
$aInt->helplink = "Domain Resolver Checker";
$aInt->requiredFiles(array("modulefunctions"));
ob_start();
echo "
<p>";
echo $aInt->lang("utilitiesresolvercheck", "pagedesc");
echo "</p>

";

if ($step == "") {
	echo "
<p align=\"center\">
<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "?step=2\">
";
	echo "<s";
	echo "elect name=\"server\" onChange=\"submit()\"><option value=\"\">Check All";
	$result = select_query("tblservers", "", array("disabled" => "0"), "name", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$serverid = $data['id'];
		$servername = $data['name'];
		$activeserver = $data['active'];
		$servermaxaccounts = $data['maxaccounts'];
		$query2 = "SELECT * FROM tblhosting WHERE server=" . (int)$serverid . " AND domainstatus!='Pending' AND domainstatus!='Terminated'";
		$result2 = full_query($query2);
		$servernumaccounts = mysql_num_rows($result2);
		echo "<option value=\"" . $serverid . "\"";

		if ($server == $serverid) {
			echo " selected";
		}

		echo ">" . $servername . " (" . $servernumaccounts . " Accounts)";
	}

	echo "</select>
<input type=\"submit\" value=\"";
	echo $aInt->lang("utilitiesresolvercheck", "runcheck");
	echo "\" class=\"button\">
</form>
</p>

";
}
else {
	if ($step == "2") {
		echo "
<form method=\"post\" action=\"sendmessage.php?type=product&multiple=true\" id=\"resolverfrm\">

<div class=\"tablebg\">
<table class=\"datatable\" width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"3\">
<tr><th width=\"20\"></th><th>";
		echo $aInt->lang("fields", "domain");
		echo "</th><th>";
		echo $aInt->lang("fields", "ipaddress");
		echo "</th><th>";
		echo $aInt->lang("utilitiesresolvercheck", "package");
		echo "</th><th>";
		echo $aInt->lang("fields", "status");
		echo "</th><th>";
		echo $aInt->lang("fields", "client");
		echo "</th></tr>
";

		if ($server) {
			$where = array("id" => $server);
		}

		$result = select_query("tblservers", "", $where, "name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$serverid = $data['id'];
			$servername = $data['name'];
			$serverip = $data['ipaddress'];
			$serverassignedips = $data['assignedips'];
			$serverusername = $data['username'];
			$serverpassword = $data['password'];
			$serverassignedips = explode("\n", $serverassignedips);

			array_walk($serverassignedips, "assignedips_trim");
			$serverassignedips[] = $serverip;
			echo "<tr><td colspan=\"6\" style=\"background-color:#efefef;font-weight:bold;\">" . $servername . " - " . $serverip . "</td></tr>";
			$serviceid = "";
			$result2 = select_query("tblhosting", "tblhosting.id AS serviceid,tblhosting.domain,tblhosting.domainstatus,tblhosting.userid,tblproducts.name,tblclients.firstname,tblclients.lastname,tblclients.companyname", "server='" . $serverid . "' AND domain!='' AND (domainstatus='Active' OR domainstatus='Suspended')", "domain", "ASC", "", "tblproducts ON tblhosting.packageid=tblproducts.id INNER JOIN tblclients ON tblhosting.userid=tblclients.id");

			while ($data = mysql_fetch_array($result2)) {
				$serviceid = $data['serviceid'];
				$domain = $data['domain'];
				$package = $data['name'];
				$status = $data['domainstatus'];
				$userid = $data['userid'];
				$firstname = $data['firstname'];
				$lastname = $data['lastname'];
				$companyname = $data['companyname'];
				$client = $firstname . " " . $lastname;

				if ($companyname) {
					$client .= " (" . $companyname . ")";
				}

				$ipaddress = gethostbyname($domain);
				$bgcolor = (!in_array($ipaddress, $serverassignedips) ? " style=\"background-color:#ffebeb\"" : "");
				echo "<tr style=\"text-align:center;\"><td" . $bgcolor . ("><input type=\"checkbox\" name=\"selectedclients[]\" value=\"" . $serviceid . "\"></td><td") . $bgcolor . ("><a href=\"clientshosting.php?userid=" . $userid . "&id=" . $serviceid . "\">" . $domain . "</a></td><td") . $bgcolor . (">" . $ipaddress . "</td><td") . $bgcolor . (">" . $package . "</td><td") . $bgcolor . (">" . $status . "</td><td") . $bgcolor . ("><a href=\"clientssummary.php?userid=" . $userid . "\">" . $client . "</a></td></tr>");
			}


			if (!$serviceid) {
				echo "<tr bgcolor=\"#ffffff\"><td colspan=\"6\" align=\"center\">" . $aInt->lang("global", "norecordsfound") . "</td></tr>";
			}
		}

		echo "</table>
</div>

<p>";
		echo $aInt->lang("global", "withselected");
		echo ": <input type=\"submit\" value=\"";
		echo $aInt->lang("global", "sendmessage");
		echo "\" class=\"button\" /> <input type=\"button\" value=\"";
		echo $aInt->lang("utilitiesresolvercheck", "terminateonserver");
		echo "\" onclick=\"showDialog('terminateaccts')\" class=\"button\" style=\"color:#cc0000;font-weight:bold;\" /></p>

</form>

<p>";
		echo $aInt->lang("utilitiesresolvercheck", "dediipwarning");
		echo "</p>

";
		echo $aInt->jqueryDialog("terminateaccts", addslashes($aInt->lang("utilitiesresolvercheck", "terminateonserver")), addslashes($aInt->lang("utilitiesresolvercheck", "delsureterminateonserver")), array("Yes" => "window.location='" . $PHP_SELF . "?step=terminate&'+$('#resolverfrm').serialize();", "No" => ""));
	}
	else {
		if ($step == "terminate") {
			echo "<h3>" . $aInt->lang("utilitiesresolvercheck", "terminatingaccts") . "</h3>
<ul>";
			foreach ($selectedclients as $serviceid) {
				$result = select_query("tblhosting", "tblhosting.id AS serviceid,tblhosting.domain,tblhosting.domainstatus,tblhosting.userid,tblproducts.name,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblproducts.servertype", array("tblhosting.id" => $serviceid), "", "", "", "tblproducts ON tblhosting.packageid=tblproducts.id INNER JOIN tblclients ON tblhosting.userid=tblclients.id");
				$data = mysql_fetch_array($result);
				$serviceid = $data['serviceid'];
				$domain = $data['domain'];
				$package = $data['name'];
				$status = $data['domainstatus'];
				$userid = $data['userid'];
				$firstname = $data['firstname'];
				$lastname = $data['lastname'];
				$companyname = $data['companyname'];
				$module = $data['servertype'];
				$client = $firstname . " " . $lastname;

				if ($companyname) {
					$client .= " (" . $companyname . ")";
				}


				if ($module) {
					if (!isValidforPath($module)) {
						exit("Invalid Server Module Name");
					}

					$modulepath = ROOTDIR . "/modules/servers/" . $module . "/" . $module . ".php";

					if (file_exists($modulepath)) {
						require_once $modulepath;
					}
				}

				$result = ServerTerminateAccount($serviceid);

				if ($result != "success") {
					$result = "Failed: " . $result;
				}
				else {
					$result = "Successful!";
				}

				echo "<li>" . $client . " - " . $package . " (" . $domain . ") - " . $result . "</li>";
			}

			echo "
</ul>
<p><b>" . $aInt->lang("utilitiesresolvercheck", "terminatingacctsdone") . "</b><br />" . $aInt->lang("utilitiesresolvercheck", "terminatingacctsdonedesc") . "</p>";
		}
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>