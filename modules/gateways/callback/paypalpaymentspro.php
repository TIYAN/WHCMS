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

require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$whmcs->load_function( "client" );
$whmcs->load_function( "cc" );
$GATEWAY = $params = getGatewayVariables( "paypalpaymentspro" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$callbacksuccess = false;
$pares = $_REQUEST["PaRes"];
$invoiceid = $_REQUEST["MD"];

if (( ( strcasecmp( "", $pares ) != 0 && $pares != null ) && isset( $_SESSION["Centinel_TransactionId"] ) )) {
	if ($params["sandbox"]) {
		$mapurl = "https://centineltest.cardinalcommerce.com/maps/txns.asp";
	}
	else {
		$mapurl = "https://paypal.cardinalcommerce.com/maps/txns.asp";
	}

	$currency = "";

	if ($params["currency"] == "USD") {
		$currency = "840";
	}


	if ($params["currency"] == "GBP") {
		$currency = "826";
	}


	if ($params["currency"] == "EUR") {
		$currency = "978";
	}


	if ($params["currency"] == "CAD") {
		$currency = "124";
	}

	$postfields = array();
	$postfields["MsgType"] = "cmpi_authenticate";
	$postfields["Version"] = "1.7";
	$postfields["ProcessorId"] = $params["processorid"];
	$postfields["MerchantId"] = $params["merchantid"];
	$postfields["TransactionPwd"] = $params["transpw"];
	$postfields["TransactionType"] = "C";
	$postfields["PAResPayload"] = $pares;
	$postfields["OrderId"] = $_SESSION["Centinel_OrderId"];
	$postfields["TransactionId"] = $_SESSION["Centinel_TransactionId"];
	$queryString = "<CardinalMPI>
";
	foreach ($postfields as $name => $value) {
		$queryString .= "<" . $name . ">" . $value . "</" . $name . ">
";
	}

	$queryString .= "</CardinalMPI>";
	$data = "cmpi_msg=" . urlencode( $queryString );
	$response = curlCall( $mapurl, $data );
	$xmlarray = XMLtoArray( $response );
	$xmlarray = $xmlarray["CARDINALMPI"];
	$errorno = $xmlarray["ERRORNO"];
	$paresstatus = $xmlarray["PARESSTATUS"];
	$sigverification = $xmlarray["SIGNATUREVERIFICATION"];
	$cavv = $xmlarray["CAVV"];
	$eciflag = $xmlarray["ECIFLAG"];
	$xid = $xmlarray["XID"];

	if (( ( ( strcasecmp( "0", $errorno ) == 0 || strcasecmp( "1140", $errorno ) == 0 ) && strcasecmp( "Y", $sigverification ) == 0 ) && ( strcasecmp( "Y", $paresstatus ) == 0 || strcasecmp( "A", $paresstatus ) == 0 ) )) {
		logTransaction( "PayPal Pro 3D Secure Callback", $_REQUEST, "Auth Passed" );
		$auth = array( "paresstatus" => $paresstatus, "cavv" => $cavv, "eciflag" => $eciflag, "xid" => $xid );
		$params = getCCVariables( $invoiceid );

		if (isset( $_SESSION["Centinel_Details"] )) {
			$params["cardtype"] = $_SESSION["Centinel_Details"]["cardtype"];
			$params["cardnum"] = $_SESSION["Centinel_Details"]["cardnum"];
			$params["cardexp"] = $_SESSION["Centinel_Details"]["cardexp"];
			$params["cccvv"] = $_SESSION["Centinel_Details"]["cccvv"];
			$params["cardstart"] = $_SESSION["Centinel_Details"]["cardstart"];
			$params["cardissuenum"] = $_SESSION["Centinel_Details"]["cardissuenum"];
			unset( $_SESSION["Centinel_Details"] );
		}

		$result = paypalpaymentspro_capture( $params, $auth );

		if ($result["status"] == "success") {
			logTransaction( "PayPal Pro 3D Capture", $result["rawdata"], "Successful" );
			addInvoicePayment( $invoiceid, $result["transid"], "", "", "paypalpaymentspro", "on" );
			sendMessage( "Credit Card Payment Confirmation", $invoiceid );
			$callbacksuccess = true;
		}
		else {
			logTransaction( "PayPal Pro 3D Capture", $result["rawdata"], "Failed" );
		}
	}
	else {
		if (strcasecmp( "N", $paresstatus ) == 0) {
			logTransaction( "PayPal Pro 3D Secure Callback", $_REQUEST, "Auth Failed" );
		}
		else {
			logTransaction( "PayPal Pro 3D Secure Callback", $_REQUEST, "Unexpected Status, Capture Anyway" );
			$auth = array( "paresstatus" => $paresstatus, "cavv" => $cavv, "eciflag" => $eciflag, "xid" => $xid );
			$params = getCCVariables( $invoiceid );

			if (isset( $_SESSION["Centinel_Details"] )) {
				$params["cardtype"] = $_SESSION["Centinel_Details"]["cardtype"];
				$params["cardnum"] = $_SESSION["Centinel_Details"]["cardnum"];
				$params["cardexp"] = $_SESSION["Centinel_Details"]["cardexp"];
				$params["cccvv"] = $_SESSION["Centinel_Details"]["cccvv"];
				$params["cardstart"] = $_SESSION["Centinel_Details"]["cardstart"];
				$params["cardissuenum"] = $_SESSION["Centinel_Details"]["cardissuenum"];
				unset( $_SESSION["Centinel_Details"] );
			}

			$result = paypalpaymentspro_capture( $params, $auth );

			if ($result["status"] == "success") {
				logTransaction( "PayPal Pro 3D Capture", $result["rawdata"], "Successful" );
				addInvoicePayment( $invoiceid, $result["transid"], "", "", "paypalpaymentspro", "on" );
				sendMessage( "Credit Card Payment Confirmation", $invoiceid );
				$callbacksuccess = true;
			}
			else {
				logTransaction( "PayPal Pro 3D Capture", $result["rawdata"], "Failed" );
			}
		}
	}
}
else {
	logTransaction( "PayPal Pro 3D Secure Callback", $_REQUEST, "Error" );
}


if (!$callbacksuccess) {
	sendMessage( "Credit Card Payment Failed", $invoiceid );
}

callback3DSecureRedirect( $invoiceid, $callbacksuccess );
?>