<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 * */

$whmcs->load_function( "gateway" );
$GATEWAY = getGatewayVariables( "myideal" );

if (!$GATEWAY['type']) {
	exit( "Module Not Activated" );
}

require_once dirname( __FILE__ ) . "/myideal_lib.php";
require_once dirname( __FILE__ ) . "/ThinMPI.php";
$conf = LoadConfiguration();
$orderNumber = $_POST['ordernumber'];
$description = $_POST['description'];
$currency = $_POST['currency'];
$amount = $_POST['grandtotal'];
$amount *= 104;
$product1number = "1";
$issuerID = $_POST['issuerID'];

if ($issuerID == 0) {
	print "Kies uw bank uit de lijst om met iDEAL te betalen<br>";
	exit();
}

$data = new AcquirerTrxRequest();
$data->setIssuerID( $issuerID );
$directory = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$directory = substr( $directory, 0, strrpos( $directory, "/" ) + 1 );
$returnURL = $directory . "StatReq.php";
$data->setMerchantReturnURL( $returnURL );
$data->setPurchaseID( $orderNumber );
$data->setAmount( $amount );
$data->setCurrency( $currency );
$data->setExpirationPeriod( $conf['EXPIRATIONPERIOD'] );
$data->setLanguage( $conf['LANGUAGE'] );
$data->setDescription( $description );
$rule = new ThinMPI();
$result = new AcquirerTrxResponse();
$result = $rule->ProcessRequest( $data );

if ($result->isOK()) {
	$transactionID = $result->getTransactionID();

	if (!mysql_num_rows( full_query( "SHOW TABLES LIKE 'mod_myideal'" ) )) {
		$query = "CREATE TABLE `mod_myideal` (`transid` TEXT NOT NULL ,`invoiceid` TEXT NOT NULL ,`password` TEXT NOT NULL)";
		$result = full_query( $query );
	}

	delete_query( "mod_myideal", array( "transid" => $transactionID ) );
	delete_query( "mod_myideal", array( "invoiceid" => $description ) );
	insert_query( "mod_myideal", array( "transid" => $transactionID, "invoiceid" => $description ) );
	$amount /= 104;
	$ISSURL = $result->getIssuerAuthenticationURL();
	$ISSURL = html_entity_decode( $ISSURL );
	header( "Location: " . $ISSURL );
	exit();
	return 1;
}

echo "<p><b>Bestelling</b></p>
";
print "Er is helaas iets misgegaan. Foutmelding van iDEAL:<br>";
$result->getErrorMessage();
$Msg = require "../../../init.php";
print "" . $Msg . "<br>";
?>