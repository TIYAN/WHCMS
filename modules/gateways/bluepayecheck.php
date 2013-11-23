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
 * */

function bluepayecheck_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "BluePay Echeck" ), "bpaccountid" => array( "FriendlyName" => "Account ID", "Type" => "text", "Size" => "20" ), "bpuserid" => array( "FriendlyName" => "User ID", "Type" => "text", "Size" => "20" ), "bpsecretkey" => array( "FriendlyName" => "Secret Key", "Type" => "text", "Size" => "30" ), "testmode" => array( "FriendlyName" => "Test Module", "Type" => "yesno" ) );
	return $configarray;
}


function bluepayecheck_link($params) {
	$code = "<input type=\"button\" value=\"" . $params["langpaynow"] . "\" onClick=\"window.open('modules/gateways/bluepayecheck.php?invoiceid=" . $params["invoiceid"] . "','authnetecheck','width=600,height=500,toolbar=0,location=0,menubar=1,resizeable=0,status=1,scrollbars=1')\">";
	return $code;
}


function bluepayecheck_nolocalcc() {
}


function bluepayecheck_capture($params) {
	update_query( "tblclients", array( "cardtype" => "", "cardnum" => "", "expdate" => "", "issuenumber" => "", "startdate" => "" ), array( "id" => $params["clientdetails"]["userid"] ) );
	$url = "https://secure.bluepay.com/interfaces/bp20post";
	$postfields = array();
	$postfields["ACCOUNT_ID"] = $params["bpaccountid"];
	$postfields["USER_ID"] = $params["bpuserid"];
	$postfields["TRANS_TYPE"] = "SALE";
	$postfields["PAYMENT_TYPE"] = "ACH";
	$postfields["MODE"] = ($params["testmode"] ? "TEST" : "LIVE");
	$postfields["AMOUNT"] = $params["amount"];
	$postfields["INVOICE_ID"] = $params["invoiceid"];
	$postfields["NAME1"] = $params["clientdetails"]["firstname"];
	$postfields["NAME2"] = $params["clientdetails"]["lastname"];
	$postfields["COMPANY_NAME"] = $params["clientdetails"]["companyname"];
	$postfields["ADDR1"] = $params["clientdetails"]["address1"];
	$postfields["ADDR2"] = $params["clientdetails"]["address2"];
	$postfields["CITY"] = $params["clientdetails"]["city"];
	$postfields["STATE"] = $params["clientdetails"]["state"];
	$postfields["ZIP"] = $params["clientdetails"]["postcode"];
	$postfields["COUNTRY"] = $params["clientdetails"]["country"];
	$postfields["PHONE"] = $params["clientdetails"]["phonenumber"];
	$postfields["EMAIL"] = $params["clientdetails"]["email"];
	$postfields["MASTER_ID"] = $params["gatewayid"];
	$postfields["TAMPER_PROOF_SEAL"] = md5( $params["bpsecretkey"] . $params["bpaccountid"] . $postfields["TRANS_TYPE"] . $postfields["AMOUNT"] . $postfields["MASTER_ID"] . $postfields["NAME1"] . $postfields["PAYMENT_ACCOUNT"] );
	$data = curlCall( $url, $postfields );
	$result = explode( "&", $data );
	foreach ($result as $res) {
		$res = explode( "=", $res );
		$resultarray[$res[0]] = $res[1];
	}


	if ($resultarray["STATUS"] == "1") {
		return array( "status" => "success", "transid" => $resultarray["TRANS_ID"], "rawdata" => $resultarray );
	}

	return array( "status" => "error", "rawdata" => $resultarray );
}


if (isset( $_GET["invoiceid"] )) {
	require "../../init.php";
	$whmcs->load_function( "gateway" );
	$whmcs->load_function( "invoice" );
	$GATEWAY = getGatewayVariables( "bluepayecheck" );

	if (!$GATEWAY["type"]) {
		exit( "Module Not Activated" );
	}

	$where = array( "id" => (int)$_GET["invoiceid"], "paymentmethod" => "bluepayecheck" );

	if (!isset( $_SESSION["adminid"] )) {
		$where["userid"] = $_SESSION["uid"];
	}

	$invoiceid = get_query_val( "tblinvoices", "id", $where );

	if (!$invoiceid) {
		exit( "Access Denied" );
	}

	echo "<html>
<head>
<title>Echeck Payment</title>
";
	echo "<s";
	echo "tyle>
body,td,input {
    font-family: Tahoma;
    font-size: 11px;
}
h1 {
    font-family: Tahoma;
    font-weight: normal;
    font-size: 18px;
    color: #000066;
}
</style>
</head>
<body>

<h1>Echeck Payment</h1>

";

	if ($submit) {
		$errormessage = "";

		if (!$bankacctname) {
			$errormessage .= "<li>You must enter your account name";
		}


		if (!$bankroutingcode) {
			$errormessage .= "<li>You must enter your banks routing code";
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
			$result = select_query( "tblinvoices", "tblclients.*,tblinvoices.id,tblinvoices.total", array( "tblinvoices.id" => $_GET["invoiceid"] ), "", "", "", "tblclients ON tblinvoices.userid=tblclients.id" );
			$data = mysql_fetch_array( $result );
			$invoiceid = $data["id"];
			$userid = $data["userid"];
			$firstname = $data["firstname"];
			$lastname = $data["lastname"];
			$address1 = $data["address1"];
			$city = $data["city"];
			$state = $data["state"];
			$postcode = $data["postcode"];
			$country = $data["country"];
			$phonenumber = $data["phonenumber"];
			$email = $data["email"];
			$result = select_query( "tblinvoices", "total", array( "id" => $invoiceid ) );
			$data = mysql_fetch_array( $result );
			$total = $data[0];
			$result = select_query( "tblaccounts", "SUM(amountin)-SUM(amountout)", array( "invoiceid" => $invoiceid ) );
			$data = mysql_fetch_array( $result );
			$amountpaid = $data[0];
			$balance = round( $total - $amountpaid, 2 );
			$balance = sprintf( "%01.2f", $balance );
			$params = getGatewayVariables( "bluepayecheck" );
			$url = "https://secure.bluepay.com/interfaces/bp20post";
			$postfields = array();
			$postfields["ACCOUNT_ID"] = $params["bpaccountid"];
			$postfields["USER_ID"] = $params["bpuserid"];
			$postfields["TRANS_TYPE"] = "SALE";
			$postfields["PAYMENT_TYPE"] = "ACH";
			$postfields["MODE"] = ($params["testmode"] ? "TEST" : "LIVE");
			$postfields["AMOUNT"] = $balance;
			$postfields["INVOICE_ID"] = $invoiceid;
			$postfields["NAME1"] = $firstname;
			$postfields["NAME2"] = $lastname;
			$postfields["COMPANY_NAME"] = $companyname;
			$postfields["ADDR1"] = $address1;
			$postfields["ADDR2"] = $address2;
			$postfields["CITY"] = $city;
			$postfields["STATE"] = $state;
			$postfields["ZIP"] = $postcode;
			$postfields["COUNTRY"] = $country;
			$postfields["PHONE"] = $phonenumber;
			$postfields["EMAIL"] = $email;
			$postfields["PAYMENT_ACCOUNT"] = strtoupper( substr( $bankaccttype, 0, 1 ) ) . ":" . $bankroutingcode . ":" . $bankacctnumber;
			$postfields["DOC_TYPE"] = "WEB";
			$postfields["TAMPER_PROOF_SEAL"] = md5( $params["bpsecretkey"] . $params["bpaccountid"] . $postfields["TRANS_TYPE"] . $postfields["AMOUNT"] . $postfields["MASTER_ID"] . $postfields["NAME1"] . $postfields["PAYMENT_ACCOUNT"] );
			$data = curlCall( $url, $postfields );
			$result = explode( "&", $data );
			foreach ($result as $res) {
				$res = explode( "=", $res );
				$resultarray[$res[0]] = $res[1];
			}


			if ($resultarray["STATUS"] == "1") {
				addInvoicePayment( $invoiceid, $resultarray["TRANS_ID"], "", "", "bluepayecheck" );
				update_query( "tblclients", array( "gatewayid" => $resultarray["TRANS_ID"] ), array( "id" => $userid ) );
				logTransaction( "BluePay Echeck", $resultarray, "Successful" );
				echo "<br /><h1 class=\"sucessfull\">Payment Successful</h1><p align=\"center\"><a href=\"#\" onclick=\"close_child_window();\">Click here to close the window</a></p>
<script language=\"javascript\">
function close_child_window(){
  window.opener.location.reload()
  window.close();
}
</script>";
			}
			else {
				$errormessage .= "<li>The echeck payment attempt was declined. Please check the supplied details";
				logTransaction( "BluePay Echeck", $resultarray, "Failed" );
			}
		}
	}


	if (( !$submit || $errormessage )) {
		echo "
<form method=\"post\" action=\"";
		echo $_SERVER["PHP_SELF"];
		echo "?invoiceid=";
		echo $_GET["invoiceid"];
		echo "\">
<input type=\"hidden\" name=\"submit\" value=\"true\" />

";

		if ($errormessage) {
			echo "<p style=\"color:#cc0000;\"><b>The following errors occurred:</b></p><ul>" . $errormessage . "</ul>";
		}

		echo "
<table>
<tr><td>Bank Account Name</td><td><input type=\"text\" name=\"bankacctname\" size=\"30\" value=\"";
		echo $bankacctname;
		echo "\" /></td></tr>
<tr><td>Bank Account Type</td><td><input type=\"radio\" name=\"bankaccttype\" value=\"checking\" checked /> Checking<br /><input type=\"radio\" name=\"bankaccttype\" value=\"savings\" /> Savings</td></tr>
<tr><td>Bank Routing Code</td><td><input type=\"text\" name=\"bankroutingcode\" size=\"20\" value=\"";
		echo $bankroutingcode;
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
</body>
</html>
";
}

?>