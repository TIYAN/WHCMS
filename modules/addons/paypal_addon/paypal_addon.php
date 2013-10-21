<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 * */

function paypal_addon_config() {
	$configarray = array( "name" => "PayPal Transaction Lookup", "version" => "2.0", "author" => "WHMCS", "description" => "This addon shows your PayPal account balance on the admin homepage & allows you to search PayPal Transactions without needing to login to PayPal", "fields" => array( "username" => array( "FriendlyName" => "API Username", "Type" => "text", "Size" => "30" ), "password" => array( "FriendlyName" => "API Password", "Type" => "password", "Size" => "30" ), "signature" => array( "FriendlyName" => "API Signature", "Type" => "password", "Size" => "50" ) ) );
	$baltitle = "Show Balance";
	$result = select_query( "tbladminroles", "", "", "name", "ASC" );

	while ($data = mysql_fetch_array( $result )) {
		$configarray["fields"]["showbalance" . $data["id"]] = array( "FriendlyName" => $baltitle, "Type" => "yesno", "Description" => "Display PayPal Balance on Homepage for <strong>" . $data["name"] . "</strong> users" );
		$baltitle = "";
	}

	return $configarray;
}


function paypal_addon_output($vars) {
	global $aInt;

	$modulelink = $vars["modulelink"];
	$url = "https://api-3t.paypal.com/nvp";
	$startdate = trim( $_REQUEST["startdate"] );
	$enddate = trim( $_REQUEST["enddate"] );
	$transid = trim( $_REQUEST["transid"] );
	$email = trim( $_REQUEST["email"] );
	$receiptid = trim( $_REQUEST["receiptid"] );
	$search = trim( $_REQUEST["search"] );

	if (!$startdate) {
		$startdate = fromMySQLDate( date( "Y-m-d", mktime( 0, 0, 0, date( "m" ) - 1, date( "d" ) + 1, date( "Y" ) ) ) );
	}


	if (!$enddate) {
		$enddate = fromMySQLDate( date( "Y-m-d", mktime( 0, 0, 0, date( "m" ), date( "d" ) + 1, date( "Y" ) ) ) );
	}

	echo "<form method=\"post\" action=\"" . $modulelink . "\">
<input type=\"hidden\" name=\"search\" value=\"true\" />
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">Transaction ID</td><td class=\"fieldarea\"><input type=\"text\" name=\"transid\" size=\"30\" value=\"" . $transid . "\" /></td></tr>
<tr><td width=\"20%\" class=\"fieldlabel\">Start Date</td><td class=\"fieldarea\"><input type=\"text\" name=\"startdate\" class=\"datepick\" size=\"30\" value=\"" . $startdate . "\" /></td></tr>
<tr><td width=\"20%\" class=\"fieldlabel\">End Date</td><td class=\"fieldarea\"><input type=\"text\" name=\"enddate\" class=\"datepick\" size=\"30\" value=\"" . $enddate . "\" /></td></tr>
<tr><td width=\"20%\" class=\"fieldlabel\">Email</td><td class=\"fieldarea\"><input type=\"text\" name=\"email\" size=\"30\" value=\"" . $email . "\" /></td></tr>
<tr><td width=\"20%\" class=\"fieldlabel\">Receipt ID</td><td class=\"fieldarea\"><input type=\"text\" name=\"receiptid\" size=\"30\" value=\"" . $receiptid . "\" /></td></tr>
</table>
<p align=\"center\"><input type=\"submit\" value=\"Search\" /></p>
</form>";

	if (!$search) {
		return false;
	}


	if ($transid) {
		$postfields = $resultsarray = array();
		$postfields["USER"] = $vars["username"];
		$postfields["PWD"] = $vars["password"];
		$postfields["SIGNATURE"] = $vars["signature"];
		$postfields["METHOD"] = "GetTransactionDetails";
		$postfields["TRANSACTIONID"] = $transid;
		$postfields["VERSION"] = "3.0";
		$result = curlCall( $url, $postfields );
		$resultsarray2 = explode( "&", $result );
		foreach ($resultsarray2 as $line) {
			$line = explode( "=", $line );
			$resultsarray[$line[0]] = urldecode( $line[1] );
		}

		$errormessage = $resultsarray["L_LONGMESSAGE0"];
		$payerstatus = $resultsarray["PAYERSTATUS"];
		$countrycode = $resultsarray["COUNTRYCODE"];
		$invoiceid = $resultsarray["INVNUM"];
		$timestamp = $resultsarray["TIMESTAMP"];
		$firstname = $resultsarray["FIRSTNAME"];
		$lastname = $resultsarray["LASTNAME"];
		$email = $resultsarray["EMAIL"];
		$transactionid = $resultsarray["TRANSACTIONID"];
		$transactiontype = $resultsarray["TRANSACTIONTYPE"];
		$paymenttype = $resultsarray["PAYMENTTYPE"];
		$ordertime = $resultsarray["ORDERTIME"];
		$amount = $resultsarray["AMT"];
		$fee = $resultsarray["FEEAMT"];
		$paymentstatus = $resultsarray["PAYMENTSTATUS"];
		$description = $resultsarray["L_NAME0"];
		$currencycode = $resultsarray["L_CURRENCYCODE0"];
		$exchrate = $resultsarray["EXCHANGERATE"];
		$settleamt = $resultsarray["SETTLEAMT"];

		if ($errormessage) {
			echo "<p><b>PayPal API Error Message</b></p><p>" . $errormessage . "</p>";
			return null;
		}

		echo "<p><b>PayPal Transaction Details</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">Transaction ID</td><td class=\"fieldarea\">" . $transactionid . "</td></tr>
<tr><td class=\"fieldlabel\">Date/Time</td><td class=\"fieldarea\">" . fromMySQLDate( $ordertime, true ) . "</td></tr>
<tr><td class=\"fieldlabel\">Transaction Type</td><td class=\"fieldarea\">" . $transactiontype . "</td></tr>
<tr><td class=\"fieldlabel\">Payment Type</td><td class=\"fieldarea\">" . $paymenttype . "</td></tr>
<tr><td class=\"fieldlabel\">Name</td><td class=\"fieldarea\">" . $firstname . " " . $lastname . "</td></tr>
<tr><td class=\"fieldlabel\">Email</td><td class=\"fieldarea\">" . $email . "</td></tr>
<tr><td class=\"fieldlabel\">Description</td><td class=\"fieldarea\">" . $description . "</td></tr>
<tr><td class=\"fieldlabel\">Amount</td><td class=\"fieldarea\">" . $amount . "</td></tr>
<tr><td class=\"fieldlabel\">PayPal Fee</td><td class=\"fieldarea\">" . $fee . "</td></tr>
<tr><td class=\"fieldlabel\">Currency</td><td class=\"fieldarea\">" . $currencycode . "</td></tr>";

		if ($exchrate) {
			echo "
<tr><td class=\"fieldlabel\">Exchange Rate</td><td class=\"fieldarea\">" . $exchrate . " (" . $settleamt . ")</td></tr>";
		}

		echo "
<tr><td class=\"fieldlabel\">Payer Status</td><td class=\"fieldarea\">" . ucfirst( $payerstatus ) . "</td></tr>
<tr><td class=\"fieldlabel\">PayPal Status</td><td class=\"fieldarea\">" . $paymentstatus . "</td></tr>
</table>";

		if (!$invoiceid) {
			$invoiceid = explode( "#", $description );
			$invoiceid = (int)$invoiceid[1];
		}

		$result = select_query( "tblinvoices", "tblinvoices.id,tblinvoices.status,tblinvoices.userid,tblclients.firstname,tblclients.lastname", array( "tblinvoices.id" => $invoiceid ), "", "", "", "tblclients ON tblclients.id=tblinvoices.userid" );
		$data = mysql_fetch_array( $result );
		$whmcs_invoiceid = $data["id"];
		$whmcs_status = $data["status"];
		$whmcs_userid = $data["userid"];
		$whmcs_firstname = $data["firstname"];
		$whmcs_lastname = $data["lastname"];

		if (!$whmcs_invoiceid) {
			$whmcs_status = "No Matching Invoice Found";
		}

		echo "<p><b>WHMCS Invoice Lookup</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">Invoice ID</td><td class=\"fieldarea\"><a href=\"invoices.php?action=edit&id=" . $whmcs_invoiceid . "\">" . $whmcs_invoiceid . "</a></td></tr>
<tr><td class=\"fieldlabel\">Invoice Status</td><td class=\"fieldarea\">" . $whmcs_status . "</td></tr>
<tr><td class=\"fieldlabel\">Client Name</td><td class=\"fieldarea\"><a href=\"clientssummary.php?userid=" . $whmcs_userid . "\">" . $whmcs_firstname . " " . $whmcs_lastname . "</a></td></tr>
</table>";
		$result = select_query( "tblaccounts", "", array( "transid" => $transactionid ) );
		$data = mysql_fetch_array( $result );
		$whmcstransid = $data["id"];
		$date = $data["date"];
		$invoiceid = $data["invoiceid"];
		$amountin = $data["amountin"];
		$fees = $data["fees"];
		$result = select_query( "tblinvoices", "", array( "id" => $invoiceid ) );
		$data = mysql_fetch_array( $result );
		$status = $data["status"];

		if ($invoiceid) {
			$date = fromMySQLDate( $date );
			$invoiceid = "<a href=\"invoices.php?action=edit&id=" . $invoiceid . "\">" . $invoiceid . "</a>";
		}
		else {
			$invoiceid = "No Matching Transaction Found";
		}

		echo "<p><b>WHMCS Transaction Lookup</b></p>

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"20%\" class=\"fieldlabel\">Date</td><td class=\"fieldarea\">" . $date . "</td></tr>
<tr><td class=\"fieldlabel\">Invoice ID</td><td class=\"fieldarea\">" . $invoiceid . "</td></tr>
<tr><td class=\"fieldlabel\">Amount</td><td class=\"fieldarea\">" . $amountin . "</td></tr>
<tr><td class=\"fieldlabel\">Invoice Status</td><td class=\"fieldarea\">" . $status . "</td></tr>
</table>";
		return null;
	}


	if ($startdate) {
		$startdate = date( "c", strtotime( toMySQLDate( $startdate ) ) ) . "<br>";
		$enddate = date( "c", strtotime( toMySQLDate( $enddate ) ) ) . "<br>";
		$postfields = $resultsarray = array();
		$postfields["USER"] = $vars["username"];
		$postfields["PWD"] = $vars["password"];
		$postfields["SIGNATURE"] = $vars["signature"];
		$postfields["METHOD"] = "TransactionSearch";

		if ($startdate) {
			$postfields["STARTDATE"] = $startdate;
		}


		if ($enddate) {
			$postfields["ENDDATE"] = $enddate;
		}


		if ($email) {
			$postfields["EMAIL"] = $email;
		}


		if ($receiptid) {
			$postfields["RECEIPTID"] = $receiptid;
		}

		$postfields["VERSION"] = "51.0";
		$result = curlCall( $url, $postfields );
		$resultsarray2 = explode( "&", $result );
		foreach ($resultsarray2 as $line) {
			$line = explode( "=", $line );
			$resultsarray[$line[0]] = urldecode( $line[1] );
		}


		if (( !empty( $resultsarray["L_ERRORCODE0"] ) && $resultsarray["L_ERRORCODE0"] != "11002" )) {
			echo "<p><b>PayPal API Error Message</b></p><p>" . $resultsarray["L_SEVERITYCODE0"] . " Code: " . $resultsarray["L_ERRORCODE0"] . " - " . $resultsarray["L_SHORTMESSAGE0"] . " - " . $resultsarray["L_LONGMESSAGE0"] . "</p>";
			return null;
		}


		if ($resultsarray["L_ERRORCODE0"] == "11002") {
			global $infobox;

			infoBox( "Search Results Truncated", "There were more than 100 matching transactions for the selected criteria. Please make your search parameters more specific to see all results" );
			echo $infobox;
		}

		$aInt->sortableTableInit( "nopagination" );
		$i = 0;

		while ($i < 100) {
			if (( $resultsarray["L_TYPE" . $i] == "Payment" && !empty( $resultsarray["L_EMAIL" . $i] ) )) {
				$data = get_query_vals( "tblaccounts", "tblclients.id AS userid, tblclients.firstname,tblclients.lastname,tblclients.companyname,tblaccounts.invoiceid,tblinvoices.total,tblinvoices.status", array( "transid" => $resultsarray["L_TRANSACTIONID" . $i] ), "", "", "", " tblclients ON tblclients.id = tblaccounts.userid INNER JOIN tblinvoices ON tblinvoices.id = tblaccounts.invoiceid" );
				$tabledata[] = $testarray = array( "clientname" => ($data["invoiceid"] ? ($data["companyname"] ? "<a href=\"clientssummary.php?userid=" . $data["userid"] . "\">" . $data["firstname"] . " " . $data["lastname"] . " (" . $data["companyname"] . ")</a>" : "<a href=\"clientssummary.php?userid=" . $data["userid"] . "\">" . $data["firstname"] . " " . $data["lastname"] . "</a>") : "Trans ID Not Found in WHMCS"), "transid" => "<a href=\"addonmodules.php?module=paypal_addon&search=1&transid=" . $resultsarray["L_TRANSACTIONID" . $i] . "\">" . $resultsarray["L_TRANSACTIONID" . $i] . "<a/>", "datetime" => fromMySQLDate( $resultsarray["L_TIMESTAMP" . $i], true ), "name" => $resultsarray["L_NAME" . $i], "email" => $resultsarray["L_EMAIL" . $i], "amt" => $resultsarray["L_NETAMT" . $i], "fee" => $resultsarray["L_FEEAMT" . $i], "curcode" => $resultsarray["L_CURRENCYCODE" . $i], "status" => $resultsarray["L_STATUS" . $i], "invoiceid" => ($data["invoiceid"] ? "<a href=\"invoices.php?action=edit&id=" . $data["invoiceid"] . "\">" . $data["invoiceid"] . "</a>" : "-"), "invoiceamt" => ($data["invoiceid"] ? $data["total"] : "-"), "invoicestatus" => ($data["invoiceid"] ? $data["status"] : "-") );
			}

			++$i;
		}

		echo $aInt->sortableTable( array( "Client Name", "Transaction ID", "Date/Time", " Payer Name", "Payer Email", "Amount", "Fee", "Currency Code", "Transaction Status", "Invoice ID", "Invoice Amount", "Invoice Status" ), $tabledata );
		return null;
	}

	global $infobox;

	infoBox( "Start Date Required", "You must enter a start and end date to search between" );
	echo $infobox;
}


?>