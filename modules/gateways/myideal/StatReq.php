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
require_once dirname( __FILE__ ) . "/myideal_lib.php";
require_once dirname( __FILE__ ) . "/ThinMPI.php";
$GATEWAY = getGatewayVariables( "myideal" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$urltowhmcs = $CONFIG["SystemURL"] . "/";
$whmcslogo = $CONFIG["LogoURL"];
$data = new AcquirerStatusRequest();
$transID = $_GET["trxid"];
$transID = str_pad( $transID, 16, "0" );
$data->setTransactionID( $transID );
$rule = new ThinMPI();
$result = $rule->ProcessRequest( $data );

if (!$result->isOK()) {
	$error_message = $result->getErrorMessage();
}
else {
	if (!$result->isAuthenticated()) {
		$error_message = "Uw bestelling is helaas niet betaald, probeer het nog eens";
	}
	else {
		$transactionID = $result->getTransactionID();
		$invoiceid = get_query_val( "mod_myideal", "invoiceid", array( "transid" => $transactionID ) );
		$logdata = array( "TransactionID" => $transactionID, "InvoiceID" => $invoiceid );

		if (!$invoiceid) {
			logTransaction( "iDEAL", $logdata, "Invoice ID Not Found" );
		}

		logTransaction( "iDEAL", $logdata, "Successful" );
		addInvoicePayment( $invoiceid, $transactionID, "", "", "myideal" );
		header( "Location: " . $urltowhmcs . "viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true" );
		exit();
	}
}


if ($error_message) {
	echo "<html>
<head>
  <title> iDeal Payment Failed </title>
  <meta http-equiv=\"refresh\" content=\"10; url=";
	echo $urltowhmcs;
	echo "clientarea.php?action=invoices\">
</head>
<body bgcolor=\"#FFFFFF\" text=\"#000000\" link=\"#0000FF\" vlink=\"#800080\" alink=\"#FF0000\">

<center>

<img src=\"";
	echo $whmcslogo;
	echo "\"><br/><br/>

<p>De betaling is niet voldaan. U kunt het wellicht nogmaals proberen of een andere betaalwijze kiezen. <br />U wordt nu teruggestuurd naar het overzicht van uw facturen.<br />
<a href=\"";
	echo $urltowhmcs;
	echo "clientarea.php?action=invoices\">Klik hier om verder te gaan</a></p>

The payment was not made. Please try again or choose a different way to pay. <br />You will now be send back to the invoice overview.«<br/>
<a href=\"";
	echo $urltowhmcs;
	echo "clientarea.php?action=invoices\">Please click here to continue</a><br/><br/>

<p>";
	echo $error_message;
	echo "</p>

</center>

</body>
</html>
";
}

?>