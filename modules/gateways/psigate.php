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
 * */

class PsiGatePayment {
	var $parser = null;
	var $xmlData = null;
	var $currentTag = null;
	var $myGatewayURL = null;
	var $myStoreID = null;
	var $myPassphrase = null;
	var $myPaymentType = null;
	var $myCardAction = null;
	var $mySubtotal = null;
	var $myTaxTotal1 = null;
	var $myTaxTotal2 = null;
	var $myTaxTotal3 = null;
	var $myTaxTotal4 = null;
	var $myTaxTotal5 = null;
	var $myShipTotal = null;
	var $myCardNumber = null;
	var $myCardExpMonth = null;
	var $myCardExpYear = null;
	var $myCardIDCode = null;
	var $myCardIDNumber = null;
	var $myTestResult = null;
	var $myOrderID = null;
	var $myUserID = null;
	var $myBname = null;
	var $myBcompany = null;
	var $myBaddress1 = null;
	var $myBaddress2 = null;
	var $myBcity = null;
	var $myBprovince = null;
	var $myBpostalcode = null;
	var $myBcountry = null;
	var $mySname = null;
	var $myScompany = null;
	var $mySaddress1 = null;
	var $mySaddress2 = null;
	var $myScity = null;
	var $mySprovince = null;
	var $mySpostalcode = null;
	var $myScountry = null;
	var $myPhone = null;
	var $myFax = null;
	var $myEmail = null;
	var $myComments = null;
	var $myCustomerIP = null;
	var $myResultTrxnTransTime = null;
	var $myResultTrxnOrderID = null;
	var $myResultTrxnApproved = null;
	var $myResultTrxnReturnCode = null;
	var $myResultTrxnErrMsg = null;
	var $myResultTrxnTaxTotal = null;
	var $myResultTrxnShipTotal = null;
	var $myResultTrxnSubTotal = null;
	var $myResultTrxnFullTotal = null;
	var $myResultTrxnPaymentType = null;
	var $myResultTrxnCardNumber = null;
	var $myResultTrxnCardExpMonth = null;
	var $myResultTrxnCardExpYear = null;
	var $myResultTrxnTransRefNumber = null;
	var $myResultTrxnCardIDResult = null;
	var $myResultTrxnAVSResult = null;
	var $myResultTrxnCardAuthNumber = null;
	var $myResultTrxnCardRefNumber = null;
	var $myResultTrxnCardType = null;
	var $myResultTrxnIPResult = null;
	var $myResultTrxnIPCountry = null;
	var $myResultTrxnIPRegion = null;
	var $myResultTrxnIPCity = null;
	var $myError = null;
	var $myErrorMessage = null;

	function ElementStart($parser, $tag, $attributes) {
		$this->currentTag = $tag;
	}


	function ElementEnd($parser, $tag) {
		$this->currentTag = "";
	}


	function charachterData($parser, $cdata) {
		$this->xmlData[$this->currentTag] = $cdata;
	}


	function setGatewayURL($GatewayURL) {
		$this->myGatewayURL = $GatewayURL;
	}


	function setStoreID($StoreID) {
		$this->myStoreID = $StoreID;
	}


	function setPassphrase($Passphrase) {
		$this->myPassphrase = $Passphrase;
	}


	function setPaymentType($PaymentType) {
		$this->myPaymentType = $PaymentType;
	}


	function setCardAction($CardAction) {
		$this->myCardAction = $CardAction;
	}


	function setSubtotal($Subtotal) {
		$this->mySubtotal = $Subtotal;
	}


	function setTaxTotal1($TaxTotal1) {
		$this->myTaxTotal1 = $TaxTotal1;
	}


	function setTaxTotal2($TaxTotal2) {
		$this->myTaxTotal2 = $TaxTotal2;
	}


	function setTaxTotal3($TaxTotal3) {
		$this->myTaxTotal3 = $TaxTotal3;
	}


	function setTaxTotal4($TaxTotal4) {
		$this->myTaxTotal4 = $TaxTotal4;
	}


	function setTaxTotal5($TaxTotal5) {
		$this->myTaxTotal5 = $TaxTotal5;
	}


	function setShiptotal($Shiptotal) {
		$this->myShiptotal = $Shiptotal;
	}


	function setCardNumber($CardNumber) {
		$this->myCardNumber = $CardNumber;
	}


	function setCardExpMonth($CardExpMonth) {
		$this->myCardExpMonth = $CardExpMonth;
	}


	function setCardExpYear($CardExpYear) {
		$this->myCardExpYear = $CardExpYear;
	}


	function setCardIDCode($CardIDCode) {
		$this->myCardIDCode = $CardIDCode;
	}


	function setCardIDNumber($CardIDNumber) {
		$this->myCardIDNumber = $CardIDNumber;
	}


	function setTestResult($TestResult) {
		$this->myTestResult = $TestResult;
	}


	function setOrderID($OrderID) {
		$this->myOrderID = $OrderID;
	}


	function setUserID($UserID) {
		$this->myUserID = $UserID;
	}


	function setBname($Bname) {
		$this->myBname = $Bname;
	}


	function setBcompany($Bcompany) {
		$this->myBcompany = $Bcompany;
	}


	function setBaddress1($Baddress1) {
		$this->myBaddress1 = $Baddress1;
	}


	function setBaddress2($Baddress2) {
		$this->myBaddress2 = $Baddress2;
	}


	function setBcity($Bcity) {
		$this->myBcity = $Bcity;
	}


	function setBprovince($Bprovince) {
		$this->myBprovince = $Bprovince;
	}


	function setBpostalcode($Bpostalcode) {
		$this->myBpostalcode = $Bpostalcode;
	}


	function setBcountry($Bcountry) {
		$this->myBcountry = $Bcountry;
	}


	function setSname($Sname) {
		$this->mySname = $Sname;
	}


	function setScompany($Scompany) {
		$this->myScompany = $Scompany;
	}


	function setSaddress1($Saddress1) {
		$this->mySaddress1 = $Saddress1;
	}


	function setSaddress2($Saddress2) {
		$this->mySaddress2 = $Saddress2;
	}


	function setScity($Scity) {
		$this->myScity = $Scity;
	}


	function setSprovince($Sprovince) {
		$this->mySprovince = $Sprovince;
	}


	function setSpostalcode($Spostalcode) {
		$this->mySpostalcode = $Spostalcode;
	}


	function setScountry($Scountry) {
		$this->myScountry = $Scountry;
	}


	function setPhone($Phone) {
		$this->myPhone = $Phone;
	}


	function setFax($Fax) {
		$this->myFax = $Fax;
	}


	function setEmail($Email) {
		$this->myEmail = $Email;
	}


	function setComments($Comments) {
		$this->myComments = $Comments;
	}


	function setCustomerIP($CustomerIP) {
		$this->myCustomerIP = $CustomerIP;
	}


	function getTrxnTransTime() {
		return $this->myResultTrxnTransTime;
	}


	function getTrxnOrderID() {
		return $this->myResultTrxnOrderID;
	}


	function getTrxnApproved() {
		return $this->myResultTrxnApproved;
	}


	function getTrxnReturnCode() {
		return $this->myResultTrxnReturnCode;
	}


	function getTrxnErrMsg() {
		return $this->myResultTrxnErrMsg;
	}


	function getTrxnTaxTotal() {
		return $this->myResultTrxnTaxTotal;
	}


	function getTrxnShipTotal() {
		return $this->myResultTrxnShipTotal;
	}


	function getTrxnSubTotal() {
		return $this->myResultTrxnSubTotal;
	}


	function getTrxnFullTotal() {
		return $this->myResultTrxnFullTotal;
	}


	function getTrxnPaymentType() {
		return $this->myResultTrxnPaymentType;
	}


	function getTrxnCardNumber() {
		return $this->myResultTrxnCardNumber;
	}


	function getTrxnCardExpMonth() {
		return $this->myResultTrxnCardExpMonth;
	}


	function getTrxnCardExpYear() {
		return $this->myResultTrxnCardExpYear;
	}


	function getTrxnTransRefNumber() {
		return $this->myResultTrxnTransRefNumber;
	}


	function getTrxnCardIDResult() {
		return $this->myResultTrxnCardIDResult;
	}


	function getTrxnAVSResult() {
		return $this->myResultTrxnAVSResult;
	}


	function getTrxnCardAuthNumber() {
		return $this->myResultTrxnCardAuthNumber;
	}


	function getTrxnCardRefNumber() {
		return $this->myResultTrxnCardRefNumber;
	}


	function getTrxnCardType() {
		return $this->myResultTrxnCardType;
	}


	function getTrxnIPResult() {
		return $this->myResultTrxnIPResult;
	}


	function getTrxnIPCountry() {
		return $this->myResultTrxnIPCountry;
	}


	function getTrxnIPRegion() {
		return $this->myResultTrxnIPRegion;
	}


	function getTrxnIPCity() {
		return $this->myResultTrxnIPCity;
	}


	function getError() {
		if ($this->myError != 0) {
			return $this->myError;
		}


		if ($this->getTrxnApproved() == "APPROVED") {
			return PSIGATE_TRANSACTION_OK;
		}


		if ($this->getTrxnApproved() == "DECLINED") {
			return PSIGATE_TRANSACTION_DECLINED;
		}

		return PSIGATE_TRANSACTION_ERROR;
	}


	function getErrorMessage() {
		if ($this->myError != 0) {
			return $this->myErrorMessage;
		}

		return $this->getTrxnError();
	}


	function PsiGatePayment() {
	}


	function doPayment() {
		$xmlRequest = "<Order>" . "<StoreID>" . htmlentities( $this->myStoreID ) . "</StoreID>" . "<Passphrase>" . htmlentities( $this->myPassphrase ) . "</Passphrase>" . "<Tax1>" . htmlentities( $this->myTaxTotal1 ) . "</Tax1>" . "<Tax2>" . htmlentities( $this->myTaxTotal2 ) . "</Tax2>" . "<Tax3>" . htmlentities( $this->myTaxTotal3 ) . "</Tax3>" . "<Tax4>" . htmlentities( $this->myTaxTotal4 ) . "</Tax4>" . "<Tax5>" . htmlentities( $this->myTaxTotal5 ) . "</Tax5>" . "<ShippingTotal>" . htmlentities( $this->myShippingtotal ) . "</ShippingTotal>" . "<Subtotal>" . htmlentities( $this->mySubtotal ) . "</Subtotal>" . "<PaymentType>" . htmlentities( $this->myPaymentType ) . "</PaymentType>" . "<CardAction>" . htmlentities( $this->myCardAction ) . "</CardAction>" . "<CardNumber>" . htmlentities( $this->myCardNumber ) . "</CardNumber>" . "<CardExpMonth>" . htmlentities( $this->myCardExpMonth ) . "</CardExpMonth>" . "<CardExpYear>" . htmlentities( $this->myCardExpYear ) . "</CardExpYear>" . "<CardIDCode>" . htmlentities( $this->myCardIDCode ) . "</CardIDCode>" . "<CardIDNumber>" . htmlentities( $this->myCardIDNumber ) . "</CardIDNumber>" . "<TestResult>" . htmlentities( $this->myTestResult ) . "</TestResult>" . "<OrderID>" . htmlentities( $this->myOrderID ) . "</OrderID>" . "<UserID>" . htmlentities( $this->myUserID ) . "</UserID>" . "<Bname>" . htmlentities( $this->myBname ) . "</Bname>" . "<Bcompany>" . htmlentities( $this->myBcompany ) . "</Bcompany>" . "<Baddress1>" . htmlentities( $this->myBaddress1 ) . "</Baddress1>" . "<Baddress2>" . htmlentities( $this->myBaddress2 ) . "</Baddress2>" . "<Bcity>" . htmlentities( $this->myBcity ) . "</Bcity>" . "<Bprovince>" . htmlentities( $this->myBprovince ) . "</Bprovince>" . "<Bpostalcode>" . htmlentities( $this->myBpostalcode ) . "</Bpostalcode>" . "<Bcountry>" . htmlentities( $this->myBcountry ) . "</Bcountry>" . "<Sname>" . htmlentities( $this->mySname ) . "</Sname>" . "<Scompany>" . htmlentities( $this->myScompany ) . "</Scompany>" . "<Saddress1>" . htmlentities( $this->mySaddress1 ) . "</Saddress1>" . "<Saddress2>" . htmlentities( $this->mySaddress2 ) . "</Saddress2>" . "<Scity>" . htmlentities( $this->myScity ) . "</Scity>" . "<Sprovince>" . htmlentities( $this->mySprovince ) . "</Sprovince>" . "<Spostalcode>" . htmlentities( $this->mySpostalcode ) . "</Spostalcode>" . "<Scountry>" . htmlentities( $this->myScountry ) . "</Scountry>" . "<Phone>" . htmlentities( $this->myPhone ) . "</Phone>" . "<Email>" . htmlentities( $this->myEmail ) . "</Email>" . "<Comments>" . htmlentities( $this->myComments ) . "</Comments>" . "<CustomerIP>" . htmlentities( $this->myCustomerIP ) . "</CustomerIP>" . "</Order>";
		$ch = curl_init( $this->myGatewayURL );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $xmlRequest );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		$xmlResponse = curl_exec( $ch );

		if (curl_errno( $ch ) == CURLE_OK) {
			$this->parser = xml_parser_create();
			xml_parser_set_option( $this->parser, XML_OPTION_CASE_FOLDING, FALSE );
			xml_set_object( $this->parser, $this );
			xml_set_element_handler( $this->parser, "ElementStart", "ElementEnd" );
			xml_set_character_data_handler( $this->parser, "charachterData" );
			xml_parse( $this->parser, $xmlResponse, TRUE );

			if (xml_get_error_code( $this->parser ) == XML_ERROR_NONE) {
				$this->myResultTrxnTransTime = $this->xmlData['TransTime'];
				$this->myResultTrxnOrderID = $this->xmlData['OrderID'];
				$this->myResultTrxnApproved = $this->xmlData['Approved'];
				$this->myResultTrxnReturnCode = $this->xmlData['ReturnCode'];
				$this->myResultTrxnErrMsg = $this->xmlData['ErrMsg'];
				$this->myResultTrxnTaxTotal = $this->xmlData['TaxTotal'];
				$this->myResultTrxnShipTotal = $this->xmlData['ShipTotal'];
				$this->myResultTrxnSubTotal = $this->xmlData['SubTotal'];
				$this->myResultTrxnFullTotal = $this->xmlData['FullTotal'];
				$this->myResultTrxnPaymentType = $this->xmlData['PaymentType'];
				$this->myResultTrxnCardNumber = $this->xmlData['CardNumber'];
				$this->myResultTrxnCardExpMonth = $this->xmlData['CardExpMonth'];
				$this->myResultTrxnCardExpYear = $this->xmlData['CardExpYear'];
				$this->myResultTrxnTransRefNumber = $this->xmlData['TransRefNumber'];
				$this->myResultTrxnCardIDResult = $this->xmlData['CardIDResult'];
				$this->myResultTrxnAVSResult = $this->xmlData['AVSResult'];
				$this->myResultTrxnCardAuthNumber = $this->xmlData['CardAuthNumber'];
				$this->myResultTrxnCardRefNumber = $this->xmlData['CardRefNumber'];
				$this->myResultTrxnCardType = $this->xmlData['CardType'];
				$this->myResultTrxnIPResult = $this->xmlData['IPResult'];
				$this->myResultTrxnIPCountry = $this->xmlData['IPCountry'];
				$this->myResultTrxnIPRegion = $this->xmlData['IPRegion'];
				$this->myResultTrxnIPCity = $this->xmlData['IPCity'];
				$this->myError = 0;
				$this->myErrorMessage = "";
			}
			else {
				$this->myError = xml_get_error_code( $this->parser ) + PSIGATE_XML_ERROR_OFFSET;
				$this->myErrorMessage = xml_error_string( $myError );
			}

			xml_parser_free( $this->parser );
		}
		else {
			$this->myError = curl_errno( $ch ) + PSIGATE_CURL_ERROR_OFFSET;
			$this->myErrorMessage = curl_error( $ch );
		}

		curl_close( $ch );
		return $this->getError();
	}


}


function psigate_activate() {
	defineGatewayField( "psigate", "text", "storeid", "", "Store ID", "20", "" );
	defineGatewayField( "psigate", "text", "passphrase", "", "Pass Phrase", "20", "" );
	defineGatewayField( "psigate", "yesno", "testmode", "", "Test Mode", "", "" );
}


function psigate_capture($params) {
	global $remote_ip;

	$psi = new PsiGatePayment();

	if ($params['testmode'] == "on") {
		$psi->setGatewayURL( "https://dev.psigate.com:7989/Messenger/XMLMessenger" );
	}
	else {
		$psi->setGatewayURL( "https://secure.psigate.com:7934/Messenger/XMLMessenger" );
	}

	$psi->setStoreID( $params['storeid'] );
	$psi->setPassPhrase( $params['passphrase'] );
	$psi->setOrderID( $params['invoiceid'] );
	$psi->setPaymentType( "CC" );
	$psi->setCardAction( "0" );
	$psi->setSubTotal( $params['amount'] );
	$psi->setCardNumber( $params['cardnum'] );
	$psi->setCardExpMonth( substr( $params['cardexp'], 0, 2 ) );
	$psi->setCardExpYear( substr( $params['cardexp'], 2, 2 ) );
	$psi->setUserID( $params[] );
	$psi->setBname( $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'] );
	$psi->setBcompany( $params['clientdetails']['companyname'] );
	$psi->setBaddress1( $params['clientdetails']['address1'] );
	$psi->setBaddress2( $params['clientdetails']['address2'] );
	$psi->setBcity( $params['clientdetails']['city'] );
	$psi->setBprovince( $params['clientdetails']['state'] );
	$psi->setBpostalCode( $params['clientdetails']['postcode'] );
	$psi->setBcountry( $params['clientdetails']['country'] );
	$psi->setSname( $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'] );
	$psi->setScompany( $params['clientdetails']['companyname'] );
	$psi->setSaddress1( $params['clientdetails']['address1'] );
	$psi->setSaddress2( $params['clientdetails']['address2'] );
	$psi->setScity( $params['clientdetails']['city'] );
	$psi->setSprovince( $params['clientdetails']['state'] );
	$psi->setSpostalCode( $params['clientdetails']['postcode'] );
	$psi->setScountry( $params['clientdetails']['country'] );
	$psi->setPhone( $params['clientdetails']['phonenumber'] );
	$psi->setEmail( $params['clientdetails']['email'] );
	$psi->setComments( "" );
	$psi->setCustomerIP( $remote_ip );

	if ($params['cccvv']) {
		$psi->setCardIDCode( "1" );
		$psi->setCardIDNumber( $params['cccvv'] );
	}

	$psi_xml_error = !( $psi->doPayment() == PSIGATE_TRANSACTION_OK );
	$desc = "Action => Capture
Client => " . $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'] . "
";
	$desc .= "Transaction Time => " . $psi->myResultTrxnTransTime . "
";
	$desc .= "Order ID => " . $psi->myResultTrxnOrderID . "
";
	$desc .= "Approved => " . $psi->myResultTrxnApproved . "
";
	$desc .= "Return Code => " . $psi->myResultTrxnReturnCode . "
";
	$desc .= "Error Message => " . $psi->myResultTrxnErrMsg . "
";
	$desc .= "Total => " . $psi->myResultTrxnFullTotal . "
";
	$desc .= "Payment Type => " . $psi->myResultTrxnPaymentType . "
";
	$desc .= "Card Number => " . $psi->myResultTrxnCardNumber . "
";
	$desc .= "Expiry Month => " . $psi->myResultTrxnCardExpMonth . "
";
	$desc .= "Expiry Year => " . $psi->myResultTrxnCardExpYear . "
";
	$desc .= "Reference Number => " . $psi->myResultTrxnTransRefNumber . "
";
	$desc .= "Card ID Result => " . $psi->myResultTrxnCardIDResult . "
";
	$desc .= "AVS Result => " . $psi->myResultTrxnAVSResult . "
";
	$desc .= "Card Auth Number => " . $psi->myResultTrxnCardAuthNumber . "
";
	$desc .= "Card Ref Number => " . $psi->myResultTrxnCardRefNumber . "
";
	$desc .= "Card Type => " . $psi->myResultTrxnCardType . "
";
	$desc .= "IP Result => " . $psi->myResultTrxnIPResult . "
";
	$desc .= "IP Country => " . $psi->myResultTrxnIPCountry . "
";
	$desc .= "IP Region => " . $psi->myResultTrxnIPRegion . "
";
	$desc .= "IP City => " . $psi->myResultTrxnIPCity . "
";
	$desc .= "Error => " . $psi->myError . "
";
	$desc .= "Error Message => " . $psi->myErrorMessage . "
";

	if ($psi->myResultTrxnApproved == "APPROVED") {
		return array( "status" => "success", "transid" => $psi->myResultTrxnTransRefNumber, "rawdata" => $desc );
	}


	if ($psi->myResultTrxnApproved == "DECLINED") {
		return array( "status" => "declined", "rawdata" => $desc );
	}

	return array( "status" => "error", "rawdata" => $desc );
}


function psigate_refund($params) {
	global $remote_ip;

	$psi = new PsiGatePayment();

	if ($params['testmode'] == "on") {
		$psi->setGatewayURL( "https://dev.psigate.com:7989/Messenger/XMLMessenger" );
	}
	else {
		$psi->setGatewayURL( "https://secure.psigate.com:7934/Messenger/XMLMessenger" );
	}

	$psi->setStoreID( $params['storeid'] );
	$psi->setPassPhrase( $params['passphrase'] );
	$psi->setOrderID( $params['invoiceid'] );
	$psi->setPaymentType( "CC" );
	$psi->setCardAction( "3" );
	$psi->setSubTotal( $params['amount'] );
	$psi_xml_error = !( $psi->doPayment() == PSIGATE_TRANSACTION_OK );
	$desc = "Action => Refund
Client => " . $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'] . "
";
	$desc .= "Transaction Time => " . $psi->myResultTrxnTransTime . "
";
	$desc .= "Order ID => " . $psi->myResultTrxnOrderID . "
";
	$desc .= "Approved => " . $psi->myResultTrxnApproved . "
";
	$desc .= "Return Code => " . $psi->myResultTrxnReturnCode . "
";
	$desc .= "Error Message => " . $psi->myResultTrxnErrMsg . "
";
	$desc .= "Total => " . $psi->myResultTrxnFullTotal . "
";
	$desc .= "Payment Type => " . $psi->myResultTrxnPaymentType . "
";
	$desc .= "Card Number => " . $psi->myResultTrxnCardNumber . "
";
	$desc .= "Expiry Month => " . $psi->myResultTrxnCardExpMonth . "
";
	$desc .= "Expiry Year => " . $psi->myResultTrxnCardExpYear . "
";
	$desc .= "Reference Number => " . $psi->myResultTrxnTransRefNumber . "
";
	$desc .= "IP Result => " . $psi->myResultTrxnIPResult . "
";
	$desc .= "IP Country => " . $psi->myResultTrxnIPCountry . "
";
	$desc .= "IP Region => " . $psi->myResultTrxnIPRegion . "
";
	$desc .= "IP City => " . $psi->myResultTrxnIPCity . "
";
	$desc .= "Error => " . $psi->myError . "
";
	$desc .= "Error Message => " . $psi->myErrorMessage . "
";

	if ($psi->myResultTrxnApproved == "APPROVED") {
		return array( "status" => "success", "transid" => $psi->myResultTrxnTransRefNumber, "rawdata" => $desc );
	}


	if ($psi->myResultTrxnApproved == "DECLINED") {
		return array( "status" => "declined", "rawdata" => $desc );
	}

	return array( "status" => "error", "rawdata" => $desc );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE['psigatename'] = "psigate";
$GATEWAYMODULE['psigatevisiblename'] = "PSIGate";
$GATEWAYMODULE['psigatetype'] = "CC";
define( "PSIGATE_CURL_ERROR_OFFSET", 1000 );
define( "PSIGATE_XML_ERROR_OFFSET", 2000 );
define( "PSIGATE_TRANSACTION_OK", APPROVED );
define( "PSIGATE_TRANSACTION_DECLINED", DECLINED );
define( "PSIGATE_TRANSACTION_ERROR", ERROR );
?>