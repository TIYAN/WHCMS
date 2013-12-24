<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 **/

function getConfigurationFileContentWithNewLicenseKey($key) {
	$attachments_dir = "";
	$downloads_dir = "";
	$customadminpath = "";
	$db_host = "";
	$db_username = "";
	$db_password = "";
	$db_name = "";
	$cc_encryption_hash = "";
	$templates_compiledir = "";
	$mysql_charset = "";
	$api_access_key = "";
	$autoauthkey = "";
	$display_errors = false;
	include ROOTDIR . "/configuration.php";
	sprintf("<?php%s" . "$license = '%s';%s" . "$db_host = '%s';%s" . "$db_username = '%s';%s" . "$db_password = '%s';%s" . "$db_name = '%s';%s" . "$cc_encryption_hash = '%s';%s" . "$templates_compiledir = '%s';%s", $newline, $key, $newline, $db_host, $newline, $db_username, $newline, $db_password, $newline, $db_name, $newline, $cc_encryption_hash, $newline, $templates_compiledir, $newline);
	$output = $newline = "\r\n";

	if ($mysql_charset) {
		$output .= sprintf("$mysql_charset = '%s';%s", $mysql_charset, $newline);
	}


	if ($attachments_dir) {
		$output .= sprintf("$attachments_dir = '%s';%s", $attachments_dir, $newline);
	}


	if ($downloads_dir) {
		$output .= sprintf("$downloads_dir = '%s';%s", $downloads_dir, $newline);
	}


	if ($customadminpath) {
		$output .= sprintf("$customadminpath = '%s';%s", $customadminpath, $newline);
	}


	if ($api_access_key) {
		$output .= sprintf("$api_access_key = '%s';%s", $api_access_key, $newline);
	}


	if ($autoauthkey) {
		$output .= sprintf("$autoauthkey = '%s';%s", $autoauthkey, $newline);
	}


	if ($display_errors) {
		$output .= sprintf("$display_errors = %s;%s", "true", $newline);
	}

	return $output;
}

define("ADMINAREA", true);
require "../init.php";

if (!($whmcs instanceof WHMCS_Init)) {
	exit("Failed to initialize application.");
}

$licenseerror = $whmcs->get_req_var("licenseerror");
$defaultLicenseErrorValue = "invalid";

if (is_string($licenseerror)) {
	$licenseerror = trim($licenseerror);
	$licenseerror = strtolower($licenseerror);
	$licenseerror = ($licenseerror && preg_match('/^[a-z]*$/', $licenseerror) === 1) ? $licenseerror : $defaultLicenseErrorValue;
}
else {
	$licenseerror = $defaultLicenseErrorValue;
}

$match = "";
$id = "";
$roleid = "";
$remote_ip = $whmcs->get_user_ip();
$performLicenseKeyUpdate = $whmcs->get_req_var("updatekey");

if ($performLicenseKeyUpdate === "true") {
	$result = select_query("tbladmins", "", array("username" => $username, "password" => md5($password), "disabled" => "0"));
	$data = mysql_fetch_array($result);
	$id = $data['id'];
	$roleid = $data['roleid'];
	$whitelisted = false;
	$whitelistedips = unserialize($CONFIG['WhitelistedIPs']);
	foreach ($whitelistedips as $whitelistedips) {

		if ($remote_ip == $whitelistedips['ip']) {
			$whitelisted = true;
			continue;
		}
	}


	if (!$id && !$whitelisted) {
		$expire_date = date("Y-m-d H:i:s", mktime(date("H"), date("i") + $CONFIG['InvalidLoginBanLength'], date("s"), date("m"), date("d"), date("Y")));

		if (!isset($CONFIG['LoginFailures'])) {
			insert_query("tblconfiguration", array("setting" => "LoginFailures", "value" => ""));
		}

		$loginfailures = unserialize($CONFIG['LoginFailures']);
		++$loginfailures[$remote_ip];

		if (3 <= $loginfailures[$remote_ip]) {
			unset($loginfailures[$remote_ip]);
			insert_query("tblbannedips", array("ip" => $remote_ip, "reason" => "3 Invalid Login Attempts", "expires" => $expire_date));
		}

		update_query("tblconfiguration", array("value" => serialize($loginfailures)), array("setting" => "LoginFailures"));
		$result = update_query("tbladmins", array("loginattempts" => "+1"), array("username" => $username));
		$result = select_query("tbladmins", "loginattempts", array("username" => $username));
		$data = mysql_fetch_array($result);
		$loginattempts = $data['loginattempts'];

		if (3 <= $loginattempts) {
			insert_query("tblbannedips", array("ip" => $remote_ip, "reason" => "3 Invalid Login Attempts", "expires" => $expire_date));
			update_query("tbladmins", array("loginattempts" => "0"), array("username" => $username));
		}

		sendAdminNotification("system", "WHMCS Admin Failed Login Attempt", "<p>A recent login attempt failed.  Details of the attempt are below.</p><p>Date/Time: " . date("d/m/Y H:i:s") . ("<br>Username: " . $username . "<br>IP Address: " . $remote_ip . "<br>Hostname: ") . gethostbyaddr($remote_ip) . "</p>");
		logActivity("Failed Admin Login Attempt - Username: " . $username);
	}


	if ($roleid) {
		$result = select_query("tbladminperms", "COUNT(*)", array("roleid" => $roleid, "permid" => "64"));
		$data = mysql_fetch_array($result);
		$match = $data[0];
	}

	$newlicensekey = trim($newlicensekey);
	$licenseKeyPattern = '/^[a-zA-Z0-9-]+$/';

	if (preg_match($licenseKeyPattern, $newlicensekey) !== 1) {
		exit("You did not enter a valid license key");
	}
	else {
		if (!$newlicensekey) {
			exit("You did not enter a new license key");
		}
		else {
			if (!$id) {
				exit("The admin username & password entered were incorrect");
			}
			else {
				if (!$match) {
					exit("You do not have permission to make this change");
				}
			}
		}
	}

	$newConfigurationContent = getConfigurationFileContentWithNewLicenseKey($newlicensekey);
	$fp = fopen("../configuration.php", "w");
	fwrite($fp, $newConfigurationContent);
	fclose($fp);
	update_query("tblconfiguration", array("value" => ""), array("setting" => "License"));
	redir("", "index.php");
}

$licensing->forceRemoteCheck();
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<title>WHMCS - License ";
echo TitleCase($licenseerror);
echo "</title>
";
echo "<s";
echo "tyle type=\"text/css\">
body {
    margin: 0;
    background-color: #F4F4F4;
    background-image: url('images/loginbg.gif');
    background-repeat: repeat-x;
}

body,td,th {
    font-family: Tahoma, Arial, Helvetica, sans-serif;
    font-size: 12px;
    color: #333;
}

a,a:visited {
    color: #000066;
    text-decoration: underline;
}

a:hover {
    text-decoration: none;
}

form {
    margin: 0;
";
echo "    padding: 0;
}

input,select {
    font-family: Tahoma, Arial, Helvetica, sans-serif;
    font-size: 16px;
}

.login_inputs {
    padding: 3px;
    border: 1px solid #ccc;
    font-size: 12px;
}

#logo {
    text-align: center;
    width: 420px;
    margin: 30px auto 10px auto;
    padding: 15px;
}

#login_container {
    color: #333;
    background-color: #fff;
    text-align: left;
    width:";
echo " 430px;
    padding: 10px;
    margin: 0 auto 10px auto;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    -o-border-radius: 10px;
    border-radius: 10px;
}

#login_container #login {
    text-align: left;
    margin: 0;
    padding: 20px 10px 20px 10px;
}

#login_container #login_msg {
    background-color: #FAF4B8;
    text-align: center;
    padding: 10px;
    margin: 0 0 1px ";
echo "0;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    -o-border-radius: 10px;
    border-radius: 10px;
}

#login_container #extra_info {
    background-color: #D3D3D3;
    text-align: left;
    padding: 10px;
    margin: 1px 0 0 0;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    -o-border-radius: 10px;
    border-radius: 10px;
}
</style>
</head>
<body>
    <div id=\"l";
echo "ogo\">
        <a href=\"login.php\"><img src=\"images/loginlogo.png\" alt=\"WHMCS\"
            border=\"0\" /></a>
    </div>
    <div id=\"login_container\">
        <div id=\"login_msg\">
            ";
echo "<s";
echo "pan style=\"font-size: 14px;\">";
echo "<s";
echo "trong>License ";
echo TitleCase($licenseerror);
echo "</strong>

        </div>
        <div id=\"login\">

";

if ($licenseerror == "suspended") {
	echo "<p>Your license key ";
	echo $license;
	echo " has been suspended.  Possible reasons for this include:</p>
            <ul>
                <li>Your license is overdue on payment</li>
                <li>Your license has been suspended for being used on a banned
                    domain</li>
                <li>Your license was found to be being used against the End User
                    License Agreement</li>
            </ul>
            <p>
   ";
	echo "             Got a new license key? <a
                    href=\"licenseerror.php?licenseerror=change\">Click here to enter it</a>
            </p>
";
}
else {
	if ($licenseerror == "pending") {
		echo "<p>The WHMCS License Key ";
		echo $license;
		echo " you just tried to access is still pending. This error occurs when we have not yet received the payment for your license.</p>
            <p>
                Got a new license key? <a
                    href=\"licenseerror.php?licenseerror=change\">Click here to enter it</a>
            </p>
";
	}
	else {
		if ($licenseerror == "invalid") {
			echo "<p>Your license key ";
			echo $license;
			echo " is invalid. Possible reasons for this include:</p>
            <ul>
                <li>The license key has been entered incorrectly</li>
                <li>The domain being used to access your install has changed</li>
                <li>The IP address your install is located on has changed</li>
                <li>The directory you are using has changed</li>
            </ul>
            <p>
             ";
			echo "   If required, you can reissue your license on-demand from our client
                area @ <a href=\"https://www.whmcs.com/members/clientarea.php\"
                    target=\"_blank\">www.whmcs.com/members/clientarea.php</a> which will
                update the allowed location details.
            </p>
            <p>
                Got a new license key? <a
                    href=\"licenseerror.p";
			echo "hp?licenseerror=change\">Click here to enter it</a>
            </p>
";
		}
		else {
			if ($licenseerror == "expired") {
				echo "<p>Your license key ";
				echo $license;
				echo " has expired!  To resolve this you can:</p>
            <ul>
                <li>Check your email for a copy of the invoice or payment reminders</li>
                <li>Order a new license from <a href=\"http://www.whmcs.com/order/\"
                    target=\"_blank\">www.whmcs.com/order</a></li>
            </ul>
            <p>
                If you feel this message to be an error, please contact us @ <a";
				echo "
                    href=\"http://www.whmcs.com/get-support\" target=\"_blank\">www.whmcs.com/get-support</a>
            </p>
            <p>
                Got a new license key? <a
                    href=\"licenseerror.php?licenseerror=change\">Click here to enter it</a>
            </p>
";
			}
			else {
				if ($licenseerror == "version") {
					echo "<p>
                You are using an Owned License for which the support & updates
                validity period expired before this release. Therefore in order to
                use this version of WHMCS, you first need to renew your support &
                updates access. You can do this from our client area @ <a
                    href=\"https://www.whmcs.com/members/clientarea.php\" target=\"";
					echo "_blank\">www.whmcs.com/members/clientarea.php</a>
            </p>
            <p>
                If you feel this message to be an error, please contact us @ <a
                    href=\"http://www.whmcs.com/get-support\" target=\"_blank\">www.whmcs.com/get-support</a>
            </p>
            <p>
                Got a new license key? <a
                    href=\"licenseerror.php?licenseerror=change\">C";
					echo "lick here to enter it</a>
            </p>
";
				}
				else {
					if ($licenseerror == "noconnection") {
						echo "<p>WHMCS has not been able to verify your license for the last few days.</p>
            <p>Before you can access your WHMCS Admin Area again, the license
                needs to be validated successfully. Please check & ensure that you
                don't have a firewall or DNS rule blocking outgoing connections to
                our website.</p>
            <p>
                For further assista";
						echo "nce, please visit <a
                    href=\"http://docs.whmcs.com/Licensing#Common_Errors\"
                    target=\"_blank\">http://docs.whmcs.com/Licensing</a>
            </p>
";
					}
					else {
						if ($licenseerror == "change") {
							echo "<p>You can change your license key by entering your admin login details
                and new key below. Requires full admin access permissions.</p>
";

							if (is_writable("../configuration.php")) {
							}
							else {
								echo "<p
                align=center style=\"color: #cc0000\">
                <b>You must set the permissions for the configuration.php file to
                    777 so it can be written to before you can change your license key</b>
            </p>";
							}


							if ($loginincorrect) {
								echo "<p align=center>
                <b>Login Details Incorrect</b>
            </p>";
							}


							if ($keyblank) {
								echo "<p align=center>
                <b>You must enter a new license key to change your key</b>
            </p>";
							}

							echo "<form method=\"post\"
                action=\"";
							echo $PHP_SELF;
							echo "?licenseerror=change&updatekey=true\">
                <table align=center>
                    <tr>
                        <td align=\"right\">Username:</td>
                        <td><input type=\"text\" name=\"username\"></td>
                    </tr>
                    <tr>
                        <td align=\"right\">Password:</td>
                        <td><input type=\"password\" name=\"password\"></td>
       ";
							echo "             </tr>
                    <tr>
                        <td align=\"right\">New License Key:</td>
                        <td><input type=\"text\" name=\"newlicensekey\"></td>
                    </tr>
                </table>
                <p align=\"center\">
                    <input type=\"submit\" value=\"Change License Key\">
                </p>
            </form>
";
						}
					}
				}
			}
		}
	}
}

echo "
  </div>

</body>
</html>";
?>