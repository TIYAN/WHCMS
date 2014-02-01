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

function myideal_activate() {
	defineGatewayField( "myideal", "text", "merchantid", "00000000", "Merchant ID", "20", "Your Postbank/ING merchant ID" );
	defineGatewayField( "myideal", "text", "subid", "0", "SubID", "20", "Provided by Postbank / ING. Normally '0'" );
	defineGatewayField( "myideal", "text", "privatekey", "merchantprivatekey.pem", "Private Key", "20", "Normally merchantprivatekey.pem" );
	defineGatewayField( "myideal", "text", "privatekeypass", "yourpassword", "Private Key Pass", "20", "Provide your private key password" );
	defineGatewayField( "myideal", "text", "privatecert", "cert.cer", "Private Cert", "20", "Your private certificate (normally cert.cer) as uploaded to the Ideal Dashboard" );
	defineGatewayField( "myideal", "text", "certificate0", "ideal.cer", "Certificate0", "20", "Normally 'ideal.cer'" );
	defineGatewayField( "myideal", "text", "acquirertimeout", "10", "Acquirer Timeout", "20", "Default is '10'" );
	defineGatewayField( "myideal", "text", "expirationperiod", "PT10M", "Expiration Period", "20", "Default is 'PT10M'" );
	defineGatewayField( "myideal", "text", "language", "nl", "Language", "20", "Default is 'nl'" );
	defineGatewayField( "myideal", "text", "description", "", "Default Description", "20", "Your WHMCS transaction descriptions. Max. 32 chars." );
	defineGatewayField( "myideal", "text", "entrancecode", "0123456789", "Entrance Code", "20", "Entrance code is a random number. Max. 40 numbers." );
	defineGatewayField( "myideal", "text", "logfile", "thinmpi.log", "Logfile", "20", "Default is 'thinmpi.log'" );
	defineGatewayField( "myideal", "yesno", "testmode", "", "Test Mode", "", "Only check this when in Ideal Postbank / ING testing modus" );
}


function myideal_link($params) {
	require_once dirname( __FILE__ ) . "/myideal/myideal_lib.php";
	require_once dirname( __FILE__ ) . "/myideal/ThinMPI.php";
	$data = new DirectoryRequest();
	$rule = new ThinMPI();
	$result = $rule->ProcessRequest( $data );
	$gatewayusername = $params['username'];
	$gatewaytestmode = $params['testmode'];
	$invoiceid = $params['invoiceid'];
	$description = $params['description'];
	$amount = $params['amount'];
	$duedate = $params['duedate'];
	$firstname = $params['clientdetails']['firstname'];
	$lastname = $params['clientdetails']['lastname'];
	$email = $params['clientdetails']['email'];
	$address1 = $params['clientdetails']['address1'];
	$address2 = $params['clientdetails']['address2'];
	$city = $params['clientdetails']['city'];
	$state = $params['clientdetails']['state'];
	$postcode = $params['clientdetails']['postcode'];
	$country = $params['clientdetails']['country'];
	$phone = $params['clientdetails']['phone'];
	$companyname = $params['companyname'];
	$systemurl = $params['systemurl'];
	$currency = $params['currency'];

	if (!$result->isOK()) {
		$code = $result->getConsumerMessage();
		logTransaction( "My iDEAL", $result->getErrorDetail(), "Link Error" );
	}
	else {
		$issuerArray = $result->getIssuerList();

		if (count( $issuerArray ) == 0) {
			$code = "Lijst met banken niet beschikbaar, er is op dit moment geen betaling met iDEAL mogelijk.";
		}
		else {

			for ($i = 0;$i < count( $issuerArray );$i++) {
				if ($issuerArray[$i]->issuerList == "Short") {
					$issuerArrayShort[] = $issuerArray[$i];
				}
				else {
					$issuerArrayLong[] = $issuerArray[$i];
				}
			}

			$code = "<form action=\"modules/gateways/myideal/TransReq.php\" method=\"post\" name=\"OrderForm\">" . "<select name=\"issuerID\">";
			$code .= "<option value=\"0\">Kies uw bank...</option>";

			for ($i = 0;$i < count( $issuerArrayShort );$i++) {
				$code .= "<option value=\"" . $issuerArrayShort[$i]->issuerID . "\"> " . $issuerArrayShort[$i]->issuerName . " </option>";
			}


			if (0 < count( $issuerArrayLong )) {
				$code .= "<option value=\"0\">---Overige banken---</option>";
			}


			for ($i = 0;$i < count( $issuerArrayLong );$i++) {
				$code .= "<option value=\"" . $issuerArrayLong[$i]->issuerID . "\"> " . $issuerArrayLong[$i]->issuerName . " </option>";
			}

			$code .= "</select><br />" . "<input name=\"clicksubmit\" type=\"submit\" value=\"Betaal Nu\"><br />" . ( "<input name=\"grandtotal\" type=\"hidden\" value=\"" . $amount . "\">" ) . "<input name=\"ordernumber\" type=\"hidden\" value=\"" . substr( myideal_RandomString( $invoiceid ), 0, 15 ) . "\">" . ( "<input name=\"currency\" type=\"hidden\" value=\"" . $currency . "\">" ) . ( "<input name=\"description\" type=\"hidden\" value=\"" . $invoiceid . "\">" ) . "</form>";
		}
	}

	return $code;
}


function myideal_RandomString($num) {
	return date( "YmdHis" ) . $num;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE['myidealname'] = "myideal";
$GATEWAYMODULE['myidealvisiblename'] = "iDEAL";
$GATEWAYMODULE['myidealtype'] = "Invoices";
?>