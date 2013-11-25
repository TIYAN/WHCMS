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

function widget_paypal_addon($vars) {
	$title = "PayPal Overview";
	$params = array();
	$result = select_query( "tbladdonmodules", "setting,value", array( "module" => "paypal_addon" ) );

	while ($data = mysql_fetch_array( $result )) {
		$params[$data[0]] = $data[1];
	}

	$content = "";
	$adminroleid = get_query_val( "tbladmins", "roleid", array( "id" => $_SESSION["adminid"] ) );

	if ($params["showbalance" . $adminroleid]) {
		$url = "https://api-3t.paypal.com/nvp";
		$postfields = $resultsarray = array();
		$postfields["USER"] = $params["username"];
		$postfields["PWD"] = $params["password"];
		$postfields["SIGNATURE"] = $params["signature"];
		$postfields["METHOD"] = "GetBalance";
		$postfields["RETURNALLCURRENCIES"] = "1";
		$postfields["VERSION"] = "56.0";
		$result = curlCall( $url, $postfields );
		$resultsarray2 = explode( "&", $result );
		foreach ($resultsarray2 as $line) {
			$line = explode( "=", $line );
			$resultsarray[$line[0]] = urldecode( $line[1] );
		}

		$paypalbal = array();

		if (strtolower( $resultsarray["ACK"] ) != "success") {
			$paypalbal[] = "Error: " . $resultsarray["L_LONGMESSAGE0"];
		}
		else {
			$i = 0;

			while ($i <= 20) {
				if (isset( $resultsarray["L_AMT" . $i] )) {
					$paypalbal[] = number_format( $resultsarray["L_AMT" . $i], 2, ".", "," ) . " " . $resultsarray["L_CURRENCYCODE" . $i];
				}

				++$i;
			}
		}

		$content .= "<div style=\"margin:10px;padding:10px;background-color:#EFFAE4;text-align:center;font-size:16px;color:#000;\">PayPal Balance: <b>" . implode( " ~ ", $paypalbal ) . "</b></div>";
	}

	$content .= "<form method=\"post\" action=\"addonmodules.php?module=paypal_addon\">
<div align=\"center\" style=\"margin:10px;font-size:16px;\">Lookup PayPal Transaction ID: <input type=\"text\" name=\"transid\" size=\"30\" value=\"" . $_POST["transid"] . "\" style=\"font-size:16px;\" /> <input type=\"submit\" name=\"search\" value=\"Go\" /></div>
<div align=\"right\"><a href=\"addonmodules.php?module=paypal_addon\">Advanced Search &raquo;</a></div>
</form>";
	return array( "title" => $title, "content" => $content );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

add_hook( "AdminHomeWidgets", 1, "widget_paypal_addon" );
?>