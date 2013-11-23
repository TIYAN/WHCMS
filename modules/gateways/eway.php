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

class EwayPayment {
	var $parser = null;
	var $xmlData = null;
	var $currentTag = null;
	var $myGatewayURL = null;
	var $myCustomerID = null;
	var $myTotalAmount = null;
	var $myCustomerFirstname = null;
	var $myCustomerLastname = null;
	var $myCustomerEmail = null;
	var $myCustomerAddress = null;
	var $myCustomerPostcode = null;
	var $myCustomerCountry = null;
	var $myCustomerInvoiceDescription = null;
	var $myCustomerInvoiceRef = null;
	var $myCardHoldersName = null;
	var $myCardNumber = null;
	var $myCardExpiryMonth = null;
	var $myCardExpiryYear = null;
	var $myCardCVN = null;
	var $myTrxnNumber = null;
	var $myOption1 = null;
	var $myOption2 = null;
	var $myOption3 = null;
	var $myResultTrxnStatus = null;
	var $myResultTrxnNumber = null;
	var $myResultTrxnOption1 = null;
	var $myResultTrxnOption2 = null;
	var $myResultTrxnOption3 = null;
	var $myResultTrxnReference = null;
	var $myResultTrxnError = null;
	var $myResultAuthCode = null;
	var $myResultReturnAmount = null;
	var $myError = null;
	var $myErrorMessage = null;

	function epXmlElementStart($parser, $tag, $attributes) {
		$this->currentTag = $tag;
	}


	function epXmlElementEnd($parser, $tag) {
		$this->currentTag = "";
	}


	function epXmlData($parser, $cdata) {
		$this->xmlData[$this->currentTag] = $cdata;
	}


	function setCustomerID($customerID) {
		$this->myCustomerID = $customerID;
	}


	function setTotalAmount($totalAmount) {
		$this->myTotalAmount = $totalAmount;
	}


	function setCustomerFirstname($customerFirstname) {
		$this->myCustomerFirstname = $customerFirstname;
	}


	function setCustomerLastname($customerLastname) {
		$this->myCustomerLastname = $customerLastname;
	}


	function setCustomerEmail($customerEmail) {
		$this->myCustomerEmail = $customerEmail;
	}


	function setCustomerAddress($customerAddress) {
		$this->myCustomerAddress = $customerAddress;
	}


	function setCustomerCountry($customerCountry) {
		$this->myCustomerCountry = $customerCountry;
	}


	function setCustomerPostcode($customerPostcode) {
		$this->myCustomerPostcode = $customerPostcode;
	}


	function setCustomerInvoiceDescription($customerInvoiceDescription) {
		$this->myCustomerInvoiceDescription = $customerInvoiceDescription;
	}


	function setCustomerInvoiceRef($customerInvoiceRef) {
		$this->myCustomerInvoiceRef = $customerInvoiceRef;
	}


	function setCardHoldersName($cardHoldersName) {
		$this->myCardHoldersName = $cardHoldersName;
	}


	function setCardNumber($cardNumber) {
		$this->myCardNumber = $cardNumber;
	}


	function setCardExpiryMonth($cardExpiryMonth) {
		$this->myCardExpiryMonth = $cardExpiryMonth;
	}


	function setCardExpiryYear($cardExpiryYear) {
		$this->myCardExpiryYear = $cardExpiryYear;
	}


	function setCardCVN($cardCVN) {
		$this->myCardCVN = $cardCVN;
	}


	function setTrxnNumber($trxnNumber) {
		$this->myTrxnNumber = $trxnNumber;
	}


	function setOption1($option1) {
		$this->myOption1 = $option1;
	}


	function setOption2($option2) {
		$this->myOption2 = $option2;
	}


	function setOption3($option3) {
		$this->myOption3 = $option3;
	}


	function getTrxnStatus() {
		return $this->myResultTrxnStatus;
	}


	function getTrxnNumber() {
		return $this->myResultTrxnNumber;
	}


	function getTrxnOption1() {
		return $this->myResultTrxnOption1;
	}


	function getTrxnOption2() {
		return $this->myResultTrxnOption2;
	}


	function getTrxnOption3() {
		return $this->myResultTrxnOption3;
	}


	function getTrxnReference() {
		return $this->myResultTrxnReference;
	}


	function getTrxnError() {
		return $this->myResultTrxnError;
	}


	function getAuthCode() {
		return $this->myResultAuthCode;
	}


	function getReturnAmount() {
		return $this->myResultReturnAmount;
	}


	function getError() {
		if ($this->myError != 0) {
			return $this->myError;
		}


		if ($this->getTrxnStatus() == "True") {
			return EWAY_TRANSACTION_OK;
		}


		if ($this->getTrxnStatus() == "False") {
			return EWAY_TRANSACTION_FAILED;
		}

		return EWAY_TRANSACTION_UNKNOWN;
	}


	function getErrorMessage() {
		if ($this->myError != 0) {
			return $this->myErrorMessage;
		}

		return $this->getTrxnError();
	}


	function EwayPayment($customerID = EWAY_DEFAULT_CUSTOMER_ID, $gatewayURL = EWAY_DEFAULT_GATEWAY_URL) {
		$this->myCustomerID = $customerID;
		$this->myGatewayURL = $gatewayURL;
	}


	function doPayment() {
		$xmlRequest = "<ewaygateway>" . "<ewayCustomerID>" . htmlentities( $this->myCustomerID ) . "</ewayCustomerID>" . "<ewayTotalAmount>" . htmlentities( $this->myTotalAmount ) . "</ewayTotalAmount>" . "<ewayCustomerFirstName><![CDATA[" . htmlentities( $this->myCustomerFirstname ) . "]]></ewayCustomerFirstName>" . "<ewayCustomerLastName><![CDATA[" . htmlentities( $this->myCustomerLastname ) . "]]></ewayCustomerLastName>" . "<ewayCustomerEmail>" . htmlentities( $this->myCustomerEmail ) . "</ewayCustomerEmail>" . "<ewayCustomerAddress><![CDATA[" . htmlentities( $this->myCustomerAddress ) . "]]></ewayCustomerAddress>" . "<ewayCustomerPostcode>" . htmlentities( $this->myCustomerPostcode ) . "</ewayCustomerPostcode>" . "<ewayCustomerInvoiceDescription>" . htmlentities( $this->myCustomerInvoiceDescription ) . "</ewayCustomerInvoiceDescription>" . "<ewayCustomerInvoiceRef>" . htmlentities( $this->myCustomerInvoiceRef ) . "</ewayCustomerInvoiceRef>" . "<ewayCardHoldersName><![CDATA[" . htmlentities( $this->myCardHoldersName ) . "]]></ewayCardHoldersName>" . "<ewayCardNumber>" . htmlentities( $this->myCardNumber ) . "</ewayCardNumber>" . "<ewayCardExpiryMonth>" . htmlentities( $this->myCardExpiryMonth ) . "</ewayCardExpiryMonth>" . "<ewayCardExpiryYear>" . htmlentities( $this->myCardExpiryYear ) . "</ewayCardExpiryYear>" . "<ewayCVN>" . htmlentities( $this->myCardCVN ) . "</ewayCVN>" . "<ewayTrxnNumber>" . htmlentities( $this->myTrxnNumber ) . "</ewayTrxnNumber>" . "<ewayCustomerIPAddress>" . $_SERVER["REMOTE_ADDR"] . "</ewayCustomerIPAddress>" . "<ewayCustomerBillingCountry>" . htmlentities( $this->myCustomerCountry ) . "</ewayCustomerBillingCountry>" . "<ewayOption1>" . htmlentities( $this->myOption1 ) . "</ewayOption1>" . "<ewayOption2>" . htmlentities( $this->myOption2 ) . "</ewayOption2>" . "<ewayOption3>" . htmlentities( $this->myOption3 ) . "</ewayOption3>" . "</ewaygateway>";
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->myGatewayURL );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $xmlRequest );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
		$xmlResponse = curl_exec( $ch );

		if (curl_errno( $ch ) == CURLE_OK) {
			$this->parser = xml_parser_create();
			xml_parser_set_option( $this->parser, XML_OPTION_CASE_FOLDING, FALSE );
			xml_set_object( $this->parser, $this );
			xml_set_element_handler( $this->parser, "epXmlElementStart", "epXmlElementEnd" );
			xml_set_character_data_handler( $this->parser, "epXmlData" );
			xml_parse( $this->parser, $xmlResponse, TRUE );

			if (xml_get_error_code( $this->parser ) == XML_ERROR_NONE) {
				$this->myResultTrxnStatus = $this->xmlData["ewayTrxnStatus"];
				$this->myResultTrxnNumber = $this->xmlData["ewayTrxnNumber"];
				$this->myResultTrxnOption1 = $this->xmlData["ewayTrxnOption1"];
				$this->myResultTrxnOption2 = $this->xmlData["ewayTrxnOption2"];
				$this->myResultTrxnOption3 = $this->xmlData["ewayTrxnOption3"];
				$this->myResultTrxnReference = $this->xmlData["ewayTrxnReference"];
				$this->myResultAuthCode = $this->xmlData["ewayAuthCode"];
				$this->myResultReturnAmount = $this->xmlData["ewayReturnAmount"];
				$this->myResultTrxnError = $this->xmlData["ewayTrxnError"];
				$this->myError = 0;
				$this->myErrorMessage = "";
			}
			else {
				$this->myError = xml_get_error_code( $this->parser ) + EWAY_XML_ERROR_OFFSET;
				$this->myErrorMessage = xml_error_string( $myError );
			}

			xml_parser_free( $this->parser );
		}
		else {
			$this->myError = curl_errno( $ch ) + EWAY_CURL_ERROR_OFFSET;
			$this->myErrorMessage = curl_error( $ch );
			$this->xmlData["CurlError"] = curl_errno( $ch ) . " - " . curl_error( $ch );
		}

		curl_close( $ch );
		return $this->getError();
	}


}


function eway_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "eWay" ), "customerid" => array( "FriendlyName" => "Customer ID", "Type" => "text", "Size" => "20" ), "refundpw" => array( "FriendlyName" => "Refund Password", "Type" => "text", "Size" => "30" ), "beagle" => array( "FriendlyName" => "Enable Beagle", "Type" => "yesno" ), "testmode" => array( "FriendlyName" => "Test Mode", "Type" => "yesno" ) );
	return $configarray;
}


function eway_capture($params) {
	if ($params["testmode"]) {
		$url = "https://www.eway.com.au/gateway/xmltest/testpage.asp";
	}
	else {
		if (( $params["beagle"] && $params["cccvv"] )) {
			$url = "https://www.eway.com.au/gateway_cvn/xmlbeagle.asp";
		}
		else {
			$url = "https://www.eway.com.au/gateway/xmlpayment.asp";
		}
	}

	$eway = new EwayPayment( $params["customerid"], $url );
	$eway->setCustomerFirstname( $params["clientdetails"]["firstname"] );
	$eway->setCustomerLastname( $params["clientdetails"]["lastname"] );
	$eway->setCustomerEmail( $params["clientdetails"]["email"] );
	$eway->setCustomerAddress( $params["clientdetails"]["address1"] . ", " . $params["clientdetails"]["city"] . ", " . $params["clientdetails"]["state"] );
	$eway->setCustomerPostcode( $params["clientdetails"]["postcode"] );
	$eway->setCustomerCountry( $params["clientdetails"]["country"] );
	$eway->setCustomerInvoiceDescription( $params["description"] );
	$eway->setCustomerInvoiceRef( $params["invoiceid"] );
	$eway->setCardHoldersName( $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"] );
	$eway->setCardNumber( $params["cardnum"] );
	$eway->setCardExpiryMonth( substr( $params["cardexp"], 0, 2 ) );
	$eway->setCardExpiryYear( substr( $params["cardexp"], 2, 2 ) );
	$eway->setCardCVN( $params["cccvv"] );
	$eway->setTrxnNumber( $params["invoiceid"] );
	$eway->setTotalAmount( round( $params["amount"] * 100, 2 ) );
	$desc = "Action => Capture
Client => " . $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"] . "
";
	$result = $eway->doPayment();
	foreach ($eway->xmlData as $key => $value) {
		$desc .= ( "" . $key . " => " . $value . "
" );
	}


	if ($result == EWAY_TRANSACTION_OK) {
		return array( "status" => "success", "transid" => $eway->getTrxnNumber(), "rawdata" => $desc );
	}

	return array( "status" => "declined", "rawdata" => $desc );
}


function eway_refund($params) {
	global $CONFIG;

	$url = "https://www.eway.com.au/gateway/xmlpaymentrefund.asp";
	$xml = "<ewaygateway>
<ewayCustomerID>" . $params["customerid"] . "</ewayCustomerID>
<ewayTotalAmount>" . $params["amount"] * 100 . "</ewayTotalAmount>
<ewayCardExpiryMonth>" . substr( $params["expdate"], 0, 2 ) . "</ewayCardExpiryMonth>
<ewayCardExpiryYear>" . substr( $params["expdate"], 2, 2 ) . "</ewayCardExpiryYear>
<ewayOriginalTrxnNumber>" . $params["transid"] . "</ewayOriginalTrxnNumber>
<ewayOption1></ewayOption1>
<ewayOption2></ewayOption2>
<ewayOption3></ewayOption3>
<ewayRefundPassword>" . $params["refundpw"] . "</ewayRefundPassword>
</ewaygateway>";
	$data = curlCall( $url, $xml );
	$results = XMLtoArray( $data );

	if ($results["EWAYRESPONSE"]["EWAYTRXNSTATUS"] == "True") {
		return array( "status" => "success", "transid" => $results["EWAYRESPONSE"]["EWAYTRXNREFERENCE"], "rawdata" => $results["EWAYRESPONSE"] );
	}

	return array( "status" => "error", "rawdata" => $results["EWAYRESPONSE"] );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

define( "EWAY_DEFAULT_GATEWAY_URL", "https://www.eway.com.au/gateway/xmlpayment.asp" );
define( "EWAY_DEFAULT_CUSTOMER_ID", "87654321" );
define( "EWAY_CURL_ERROR_OFFSET", 1000 );
define( "EWAY_XML_ERROR_OFFSET", 2000 );
define( "EWAY_TRANSACTION_OK", 0 );
define( "EWAY_TRANSACTION_FAILED", 1 );
define( "EWAY_TRANSACTION_UNKNOWN", 2 );
?>