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
 * */

function directdebit_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Direct Debit" ) );
	return $configarray;
}


function directdebit_link($params) {
	$code = "<form method=\"post\" action=\"modules/gateways/directdebit.php?invoiceid=" . $params["invoiceid"] . "\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\" />
</form>";
	return $code;
}


if (isset( $_GET["invoiceid"] )) {
	require "../../init.php";
	$whmcs->load_function( "gateway" );
	$whmcs->load_function( "invoice" );
	$GATEWAY = getGatewayVariables( "directdebit" );

	if (!$GATEWAY["type"]) {
		exit( "Module Not Activated" );
	}


	if ($_SESSION["adminid"]) {
		$result = select_query( "tblinvoices", "id,userid", array( "id" => (int)$invoiceid ) );
	}
	else {
		$result = select_query( "tblinvoices", "id,userid", array( "id" => (int)$invoiceid, "userid" => $_SESSION["uid"] ) );
	}

	$data = mysql_fetch_array( $result );
	$invoiceid = $data["id"];
	$userid = $data["userid"];

	if (!$invoiceid) {
		exit( "Access Denied" );
	}

	echo "<!DOCTYPE html>
<html lang=\"en\">
  <head>
    <meta http-equiv=\"content-type\" content=\"text/html; charset=";
	echo $CONFIG["Charset"];
	echo "\" />
    <title>Direct Debit Payment</title>
    <link href=\"../../templates/default/css/invoice.css\" rel=\"stylesheet\">
  </head>
<body>

<div class=\"wrapper\">

<p><img src=\"";
	echo $CONFIG["LogoURL"];
	echo "\" title=\"";
	echo $CONFIG["CompanyName"];
	echo "\" /></p>

<h1>Direct Debit Payment</h1>

";

	if ($submit) {
		$errormessage = "";

		if (!$bankname) {
			$errormessage .= "<li>You must enter your banks name";
		}


		if (!in_array( $bankaccttype, array( "Checking", "Savings" ) )) {
			$errormessage .= "<li>You must select your bank account type";
		}


		if (!$bankabacode) {
			$errormessage .= "<li>You must enter your banks ABA code";
		}


		if (!$bankacctnumber) {
			$errormessage .= "<li>You must enter your bank account number";
		}


		if (!$bankacctnumber2) {
			$errormessage .= "<li>You must confirm your bank account number";
		}


		if ($bankacctnumber != $bankacctnumber2) {
			$errormessage .= "<li>Your bank account number & confirmation don't match";
		}


		if (!$errormessage) {
			update_query( "tblclients", array( "bankname" => $bankname, "banktype" => $bankaccttype, "bankcode" => $bankabacode, "bankacct" => $bankacctnumber ), array( "id" => $userid ) );
			echo "<p align=\"center\">Thank you for submitting your details. We will attempt to process your payment using the supplied details within the next few days, and contact you in case of any problems.</p>
<p align=\"center\"><a href=\"#\" onclick=\"window.close()\">Click here to close the window</a></p>
";
		}
	}


	if (( !$submit || $errormessage )) {
		echo "
<p>Please submit your bank account details below to pay by Direct Debit.</p>

<form method=\"post\" action=\"";
		echo $_SERVER["PHP_SELF"];
		echo "?invoiceid=";
		echo (int)$_GET["invoiceid"];
		echo "\">
<input type=\"hidden\" name=\"submit\" value=\"true\" />

";

		if ($errormessage) {
			echo "<div class=\"creditbox\" style=\"text-align:left;\"><b>The following errors occurred:</b></p><ul>" . $errormessage . "</ul></div>";
		}

		echo "
<table>
<tr><td>Bank Name</td><td><input type=\"text\" name=\"bankname\" size=\"30\" value=\"";
		echo $bankname;
		echo "\" /></td></tr>
<tr><td>Bank Account Type</td><td><label><input type=\"radio\" name=\"bankaccttype\" value=\"Checking\"";

		if (( !$bankaccttype || $bankaccttype == "Checking" )) {
			echo " checked";
		}

		echo " /> Checking</label> <label><input type=\"radio\" name=\"bankaccttype\" value=\"Savings\"";

		if ($bankaccttype == "Savings") {
			echo " checked";
		}

		echo " /> Savings</label></td></tr>
<tr><td>Bank ABA Code</td><td><input type=\"text\" name=\"bankabacode\" size=\"20\" value=\"";
		echo $bankabacode;
		echo "\" /></td></tr>
<tr><td>Bank Account Number</td><td><input type=\"text\" name=\"bankacctnumber\" size=\"20\" value=\"";
		echo $bankacctnumber;
		echo "\" /></td></tr>
<tr><td>Confirm Account Number</td><td><input type=\"text\" name=\"bankacctnumber2\" size=\"20\" value=\"";
		echo $bankacctnumber2;
		echo "\" /></td></tr>
</table>

<p align=\"center\"><img src=\"https://www2.bankofamerica.com/creditcards/application/images/aba_routing.gif\" /></p>

<p align=\"center\"><input type=\"submit\" value=\"Submit\" /></p>

</form>

";
	}

	echo "
</div>

</body>
</html>
";
}

?>