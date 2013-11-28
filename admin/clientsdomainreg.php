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
$aInt = new WHMCS_Admin("Perform Registrar Operations");
$aInt->title = $aInt->lang("domains", "regtransfer");
$aInt->sidebar = "clients";
$aInt->icon = "clientsprofile";
$aInt->requiredFiles(array("clientfunctions", "registrarfunctions"));

if ($action == "do") {
	check_token("WHMCS.admin.default");
}

ob_start();
$result = select_query("tbldomains", "", array("id" => $domainid));
$data = mysql_fetch_array($result);
$domainid = $data['id'];
$userid = $data['userid'];
$domain = $data['domain'];
$orderid = $data['orderid'];
$registrar = $data['registrar'];
$registrationperiod = $data['registrationperiod'];
$domainparts = explode(".", $domain, 2);
$params = array();
$params['domainid'] = $domainid;
$params['sld'] = $domainparts[0];
$params['tld'] = $domainparts[1];
$params['regperiod'] = $registrationperiod;
$params['registrar'] = $registrar;
$nsvals = array();

if (!$ns1 && !$ns2) {
	$result = select_query("tblhosting", "", array("domain" => $domain));
	$data = mysql_fetch_array($result);
	$server = $data['server'];

	if ($server) {
		$result = select_query("tblservers", "", array("id" => $server));
		$data = mysql_fetch_array($result);
		$i = 1;

		while ($i <= 5) {
			$nsvals[$i] = $data["nameserver" . $i];
			++$i;
		}

		$autonsdesc = "(" . $aInt->lang("domains", "autonsdesc1") . ")";
	}
	else {
		$i = 1;

		while ($i <= 5) {
			$nsvals[$i] = $CONFIG["DefaultNameserver" . $i];
			++$i;
		}

		$autonsdesc = "(" . $aInt->lang("domains", "autonsdesc2") . ")";
	}
}

$result = select_query("tblorders", "", array("id" => $orderid));
$data = mysql_fetch_array($result);
$nameservers = $data['nameservers'];

if ($nameservers && $nameservers != ",") {
	if (!$_POST) {
		$nameservers = explode(",", $nameservers);
		$i = 1;

		while ($i <= 5) {
			$nsvals[$i] = $nameservers[$i - 1];
			++$i;
		}

		$autonsdesc = "(" . $aInt->lang("domains", "autonsdesc3") . ")";
	}
}


if (!$transfersecret) {
	$transfersecret = $data['transfersecret'];
	$transfersecret = ($transfersecret ? unserialize($transfersecret) : array());
	$transfersecret = htmlspecialchars($transfersecret[$domain]);
}


if (is_array($_POST)) {
	$i = 1;

	while ($i <= 5) {
		if (isset($_POST["ns" . $i])) {
			$nsvals[$i] = $_POST["ns" . $i];
		}

		++$i;
	}
}

echo "
<form method=\"post\" action=\"";
echo $PHP_SELF;
echo "?domainid=";
echo $domainid;
echo "&action=do&ac=";
echo $ac;
echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "registrar");
echo "</td><td class=\"fieldarea\">";
echo ucfirst($registrar);
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("permissions", "action");
echo "</td><td class=\"fieldarea\">";

if ($ac == "") {
	echo $aInt->lang("domains", "actionreg");
}
else {
	echo $aInt->lang("domains", "transfer");
}

echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("fields", "domain");
echo "</td><td class=\"fieldarea\">";
echo $domain;
echo "</td></tr>
<tr><td class=\"fieldlabel\">";
echo $aInt->lang("domains", "regperiod");
echo "</td><td class=\"fieldarea\">";
echo $registrationperiod;
echo " ";
echo $aInt->lang("domains", "years");
echo "</td></tr>
";
$i = 1;

while ($i <= 5) {
	echo "<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("domains", "nameserver") . " " . $i;
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"ns";
	echo $i;
	echo "\" size=\"40\" value=\"";
	echo $nsvals[$i];
	echo "\" /> ";

	if ($i == 1) {
		echo $autonsdesc;
	}

	echo "</td></tr>";
	++$i;
}


if ($ac == "transfer") {
	echo "<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("domains", "eppcode");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"transfersecret\" size=\"20\" value=\"";
	echo $transfersecret;
	echo "\" /> (";
	echo $aInt->lang("domains", "ifreq");
	echo ")</td></tr>";
}

echo "<tr><td class=\"fieldlabel\">";
echo $aInt->lang("orders", "sendconfirmation");
echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"sendregisterconfirm\" checked /> ";
echo $aInt->lang("domains", "sendregisterconfirm");
echo "</td></tr>
</table>

";

if ($action == "do") {
	$i = 1;

	while ($i <= 5) {
		$params["ns" . $i] = $_POST["ns" . $i];
		++$i;
	}

	$params['transfersecret'] = $_POST['transfersecret'];

	if (!$ac) {
		$result = RegRegisterDomain($params);
	}
	else {
		$result = RegTransferDomain($params);
	}


	if ($result['error']) {
		infoBox($aInt->lang("global", "erroroccurred"), $result['error']);
		echo $infobox;
	}
	else {
		if (!$ac) {
			infoBox($aInt->lang("global", "success"), $aInt->lang("domains", "regsuccess"));
		}
		else {
			infoBox($aInt->lang("global", "success"), $aInt->lang("domains", "transuccess"));
		}

		echo "<br />" . $infobox;
		echo "
<p align=\"center\"><input type=\"button\" value=\"";
		echo $aInt->lang("global", "continue");
		echo " >>\" class=\"btn\" onClick=\"window.location='clientsdomains.php?userid=";
		echo $userid;
		echo "&domainid=";
		echo $domainid;
		echo "'\"></p>

";

		if ($sendregisterconfirm == "on") {
			if ($ac == "") {
				sendMessage("Domain Registration Confirmation", $domainid);
			}
			else {
				sendMessage("Domain Transfer Initiated", $domainid);
			}
		}

		$complete = "true";
	}
}


if ($complete != "true") {
	$replace = ($ac == "" ? $aInt->lang("domains", "actionreg") : $aInt->lang("domains", "transfer"));
	$question = str_replace("%s", $replace, $aInt->lang("domains", "actionquestion"));
	echo "
<p align=center>";
	echo $question;
	echo "</p>
<p align=center><input type=\"submit\" value=\" ";
	echo $aInt->lang("global", "yes");
	echo " \" class=\"btn btn-success\"> <input type=\"button\" value=\" ";
	echo $aInt->lang("global", "no");
	echo " \" class=\"btn\" onClick=\"window.location='clientsdomains.php?userid=";
	echo $userid;
	echo "&domainid=";
	echo $domainid;
	echo "'\">

";
}

echo "
</form>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>