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
 * */

function getConfigArray() {
	$availablelanguages = array( "English", "Arabic", "Cantonese", "Croatian", "Czech", "Danish", "Dutch", "Estonian", "Finnish", "French", "German", "Greek", "Hebrew", "Hindi", "Hungarian", "Italian", "Japanese", "Korean", "Mandarin", "Norwegian", "Polish", "Portuguese", "Portugueseeu", "Romanian", "Russian", "Slovakian", "Spanish", "Swedish", "Thai", "Turkish", "Ukrainian", "Vietnamese" );
	$configarray = array( "Enable" => array( "Type" => "yesno", "Description" => "Tick to enable MaxMind Fraud Checking for Orders" ), "MaxMind License Key" => array( "Type" => "text", "Size" => "30", "Description" => "Not Registered? <a href=\"http://go.whmcs.com/78/maxmind\" target=\"_blank\">Click here to signup & get 1000 free checks + 20% off credits!</a>" ), "Reject Free Email Service" => array( "Type" => "yesno", "Description" => "Block orders from free email addresses such as Hotmail & Yahoo!" ), "Reject Country Mismatch" => array( "Type" => "yesno", "Description" => "Block orders where order address is different from IP Location" ), "Reject Anonymous Proxy" => array( "Type" => "yesno", "Description" => "Block orders where the user is ordering through a Proxy" ), "Reject High Risk Country" => array( "Type" => "yesno", "Description" => "Block orders from high risk countries" ), "MaxMind Fraud Risk Score" => array( "Type" => "text", "Size" => "2", "Description" => "Higher than this value and the order will be blocked" ), "Use New Risk Score" => array( "Type" => "yesno", "Description" => "Tick to use new riskScore which ranges from 0 to 100" ), "Perform Telephone Verification" => array( "Type" => "yesno", "Description" => "Tick this box to enable phone verification on orders" ), "Telephone Fraud Score" => array( "Type" => "text", "Size" => "2", "Description" => "Enter the Fraud Risk Score Above Which to Perform Phone Verification" ), "Force Phone Verify Products" => array( "Type" => "text", "Size" => "12", "Description" => "You can enter a comma separated list of product IDs here to always perform phone verification regardless of risk score" ), "Language" => array( "Type" => "dropdown", "Options" => implode( ",", $availablelanguages ), "Description" => "Set the language you want the call to be made in here" ) );
	return $configarray;
}


function doFraudCheck($params, $checkonly = false) {
	global $_LANG;
	global $cc_encryption_hash;

	$availablelanguages = array( "English", "Arabic", "Cantonese", "Croatian", "Czech", "Danish", "Dutch", "Estonian", "Finnish", "French", "German", "Greek", "Hebrew", "Hindi", "Hungarian", "Italian", "Japanese", "Korean", "Mandarin", "Norwegian", "Polish", "Portuguese", "Portugueseeu", "Romanian", "Russian", "Slovakian", "Spanish", "Swedish", "Thai", "Turkish", "Ukrainian", "Vietnamese" );

	if (in_array( $_SESSION["Language"], $availablelanguages )) {
		$params["Language"] = $_SESSION["Language"];
	}


	if ($params["Language"] == "Portuguese-br") {
		$params["Language"] = "PT_BR";
	}


	if ($params["Language"] == "Portuguese-pt") {
		$params["Language"] = "PT_PT";
	}

	$phonecc = $params["clientsdetails"]["countrycode"];
	$phonenumber = $params["clientsdetails"]["phonenumber"];

	if (( $phonecc == "44" && substr( $phonenumber, 0, 1 ) == "0" )) {
		$phonenumber = substr( $phonenumber, 1 );
	}

	$phonecclen = strlen( $phonecc );

	if (substr( $phonenumber, 0, $phonecclen ) == $phonecc) {
		$phonenumber = "+" . $phonenumber;
	}
	else {
		$phonenumber = "+" . $phonecc . $phonenumber;
	}

	$emaildomain = explode( "@", $params["clientsdetails"]["email"], 2 );
	$emaildomain = $emaildomain[1];
	$cchash = md5( $cc_encryption_hash . $params["clientsdetails"]["userid"] );
	$cardnum = get_query_val( "tblclients", "AES_DECRYPT(cardnum,'" . $cchash . "') as cardnum", array( "id" => $params["clientsdetails"]["userid"] ) );
	$url = "http://minfraud3.maxmind.com/app/ccv2r";
	$postfields = array();
	$postfields["license_key"] = $params["MaxMind License Key"];
	$postfields["requested_type"] = "standard";
	$postfields["i"] = $params["ip"];
	$postfields["EmailMD5"] = md5( $params["clientsdetails"]["email"] );
	$postfields["PasswordMD5"] = md5( $params["clientsdetails"]["password"] );
	$postfields["city"] = $params["clientsdetails"]["city"];
	$postfields["region"] = $params["clientsdetails"]["state"];
	$postfields["postal"] = $params["clientsdetails"]["postcode"];
	$postfields["country"] = $params["clientsdetails"]["country"];
	$postfields["domain"] = $emaildomain;
	$postfields["custPhone"] = $phonenumber;

	if ($cardnum) {
		$postfields["bin"] = substr( $cardnum, 0, 6 );
	}

	$postfields["shipAddr"] = $params["clientsdetails"]["address1"];
	$postfields["shipCity"] = $params["clientsdetails"]["city"];
	$postfields["shipRegion"] = $params["clientsdetails"]["state"];
	$postfields["shipPostal"] = $params["clientsdetails"]["postcode"];
	$postfields["shipCountry"] = $params["clientsdetails"]["country"];
	$postfields["txnID"] = $_SESSION["orderdetails"]["OrderID"];
	$postfields["sessionID"] = session_id();
	$postfields["user_agent"] = $_SERVER["HTTP_USER_AGENT"];
	$postfields["accept_language"] = $_SERVER["HTTP_ACCEPT_LANGUAGE"];

	if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
		$postfields["forwardedIP"] = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}

	$content = curlCall( $url, $postfields );

	if (substr( $content, 0, 10 ) == "CURL Error") {
		$results["err"] = $content;
	}
	else {
		if (!$content) {
			$results["err"] = "No Response Received";
		}
		else {
			$results = array();
			$keyvaluepairs = explode( ";", $content );
			foreach ($keyvaluepairs as $v) {
				$v = explode( "=", $v );
				$results[$v[0]] = $v[1];
			}
		}
	}


	if ($checkonly) {
		return $results;
	}


	if (( $params["Reject Free Email Service"] == "on" && $results["freeMail"] == "Yes" )) {
		$results["error"]["title"] = $_LANG["maxmind_title"] . " " . $_LANG["maxmind_error"];
		$results["error"]["description"] = $_LANG["maxmind_rejectemail"];
	}


	if (( $params["Reject Country Mismatch"] == "on" && $results["countryMatch"] == "No" )) {
		$results["error"]["title"] = $_LANG["maxmind_title"] . " " . $_LANG["maxmind_error"];
		$results["error"]["description"] = $_LANG["maxmind_countrymismatch"];
	}


	if (( $params["Reject Anonymous Proxy"] == "on" && $results["anonymousProxy"] == "Yes" )) {
		$results["error"]["title"] = $_LANG["maxmind_title"] . " " . $_LANG["maxmind_error"];
		$results["error"]["description"] = $_LANG["maxmind_anonproxy"];
	}


	if (( $params["Reject High Risk Country"] == "on" && $results["highRiskCountry"] == "Yes" )) {
		$results["error"]["title"] = $_LANG["maxmind_title"] . " " . $_LANG["maxmind_error"];
		$results["error"]["description"] = $_LANG["maxmind_highriskcountry"];
	}

	$score = ($params["Use New Risk Score"] ? $results["riskScore"] : $results["score"]);

	if (( $params["MaxMind Fraud Risk Score"] != "" && $params["MaxMind Fraud Risk Score"] < $score )) {
		$results["error"]["title"] = $_LANG["maxmind_title"] . " " . $_LANG["maxmind_error"];
		$results["error"]["description"] = $_LANG["maxmind_highfraudriskscore"];
	}

	$forcephoneverify = false;
	$forcepids = $params["Force Phone Verify Products"];

	if ($forcepids) {
		$forcepids = explode( ",", $forcepids );
		foreach ($forcepids as $k => $v) {
			$forcepids[$k] = trim( $v );
		}

		$result = select_query( "tblhosting", "COUNT(id)", "orderid=" . (int)$_SESSION["orderdetails"]["OrderID"] . " AND packageid IN (" . implode( ",", $forcepids ) . ")" );
		$data = mysql_fetch_array( $result );

		if ($data[0]) {
			$forcephoneverify = true;
		}
	}


	if (( ( !$params["error"]["title"] && $params["Perform Telephone Verification"] ) && ( $params["Telephone Fraud Score"] <= $score || $forcephoneverify ) )) {
		if ($_POST["pin"]) {
			if ($_POST["pin"] != $_SESSION["maxmindpin"]) {
				$results["error"]["title"] = $_LANG["maxmind_title"] . " " . $_LANG["maxmind_incorrectcode"];
				$results["error"]["description"] = "<p>" . $_LANG["maxmind_faileddescription"] . "</p>";
				$results["code"] = $_SESSION["maxmindpin"];
				$results["message"] = "Pin Code Verification Failed";
			}
		}
		else {
			$pin = "";
			$i = 0;

			while ($i < 4) {
				$pin .= mt_rand( 1, 9 );
				++$i;
			}

			$_SESSION["maxmindpin"] = $pin;
			$url = "https://www.maxmind.com/app/telephone_http";
			$postfields = array();
			$postfields["l"] = $params["MaxMind License Key"];
			$postfields["phone"] = $phonenumber;
			$postfields["verify_code"] = $pin;

			if ($params["Language"] != "English") {
				$postfields["language"] = $params["Language"];
			}

			$content = curlCall( $url, $postfields );

			if (substr( $content, 0, 10 ) == "CURL Error") {
				$results["err"] = $content;
			}
			else {
				if (!$content) {
					$results["err"] = "No Response Received";
				}
				else {
					$keyvaluepairs = explode( ";", $content );
					foreach ($keyvaluepairs as $v) {
						$v = explode( "=", $v );
						$results[$v[0]] = $v[1];
					}
				}
			}

			$results["userinput"] = "true";
			$results["title"] = $_LANG["maxmind_title"];
			$results["description"] = "<p>" . $_LANG["maxmind_callingnow"] . "</p>
<form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?step=fraudcheck\">
<center><div id=\"pinnumber\" align=\"center\">" . $_LANG["maxmind_pincode"] . ": <input type=\"text\" name=\"pin\" size=\"10\"></div></center>
<p align=\"center\"><input type=\"submit\" value=\"" . $_LANG["ordercontinuebutton"] . "\"></p>
</form>";
		}
	}

	return $results;
}


function getResultsArray($results) {
	$results = explode( "
", $results );

	$descarray = array();
	$descarray["distance"] = "Distance from IP address to Address";
	$descarray["countryMatch"] = "If Country of IP address matches Address";
	$descarray["countryCode"] = "Country Code of the IP address";
	$descarray["freeMail"] = "Whether e-mail is from free e-mail provider";
	$descarray["anonymousProxy"] = "Whether IP address is Anonymous Proxy";
	$descarray["score"] = "Old Fraud Risk Score";
	$descarray["proxyScore"] = "Likelihood of IP Address being an Open Proxy";
	$descarray["riskScore"] = "New Risk Score Rating";
	$descarray["ip_city"] = "Estimated City of the IP address";
	$descarray["ip_region"] = "Estimated State/Region of the IP address";
	$descarray["ip_latitude"] = "Estimated Latitude of the IP address";
	$descarray["ip_longitude"] = "Estimated Longitude of the IP address";
	$descarray["ip_isp"] = "ISP of the IP address";
	$descarray["ip_org"] = "Organization of the IP address";
	$descarray["custPhoneInBillingLoc"] = "Customer Phone in Billing Location";
	$descarray["highRiskCountry"] = "IP address or billing address in high risk country";
	$descarray["cityPostalMatch"] = "Whether billing city and state match zipcode";
	$descarray["carderEmail"] = "Whether e-mail is in database of high risk e-mails";
	$descarray["maxmindID"] = "MaxMind ID";
	$descarray["err"] = "MaxMind Error";
	$descarray["explanation"] = "Explanation";
	$values = array();
	foreach ($results as $value) {
		$result = explode( " => ", $value );
		$result[1] = str_replace( "http://www.maxmind.com/app/ccv2r_signup", "http://www.maxmind.com/app/ccfd_promo?promo=WHMCS4562", $result[1] );
		$values[$result[0]] = $result[1];
	}

	$resultarray = array();
	foreach ($descarray as $k => $v) {

		if ($k == "riskScore") {
			$k;
			$values-> .= "%";
		}

		$resultarray[$v] = $values[$k];
	}


	if ($values["curl_error"]) {
		$resultsarray = array( "Connection Error" => $values["curl_error"] );
	}

	return $resultarray;
}


?>