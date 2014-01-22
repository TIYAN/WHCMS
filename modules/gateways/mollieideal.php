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

class iDEAL_Payment {
	private $partner_id = null;
	private $testmode = false;
	private $bank_id = null;
	private $amount = 0;
	private $description = null;
	private $return_url = null;
	private $report_url = null;
	private $bank_url = null;
	private $payment_url = null;
	private $transaction_id = null;
	private $paid_status = null;
	private $consumer_info = array();
	private $error_message = "";
	private $error_code = 0;
	private $api_host = "ssl://secure.mollie.nl";
	private $api_port = 443;

	function __construct($partner_id, $api_host = "ssl://secure.mollie.nl", $api_port = 443) {
		$this->partner_id = $partner_id;
		$this->api_host = $api_host;
		$this->api_port = $api_port;
	}


	function getBanks() {
		$query_variables = array( "a" => "banklist", "partner_id" => $this->partner_id );

		if ($this->testmode) {
			$query_variables['testmode'] = "true";
		}

		$banks_xml = $this->_sendRequest( $this->api_host, $this->api_port, "/xml/ideal/", http_build_query( $query_variables, "", "&" ) );

		if (empty( $banks_xml )) {
			return false;
		}

		$banks_object = $this->_XMLtoObject( $banks_xml );

		if (!$banks_object || $this->_XMlisError( $banks_object )) {
			return false;
		}

		$banks_array = array();
		foreach ($banks_object->bank as $bank) {
			$banks_array["" . $bank->bank_id] = "" . $bank->bank_name;
		}

		return $banks_array;
	}


	function createPayment($bank_id, $amount, $description, $return_url, $report_url) {
		if (( ( ( !$this->setBankId( $bank_id ) || !$this->setDescription( $description ) ) || !$this->setAmount( $amount ) ) || !$this->setReturnUrl( $return_url ) ) || !$this->setReportUrl( $report_url )) {
			$this->error_message = "De opgegeven betalings gegevens zijn onjuist of incompleet.";
			return false;
		}

		$query_variables = array( "a" => "fetch", "partnerid" => $this->getPartnerId(), "bank_id" => $this->getBankId(), "amount" => $this->getAmount(), "description" => $this->getDescription(), "reporturl" => $this->getReportURL(), "returnurl" => $this->getReturnURL() );
		$create_xml = $this->_sendRequest( $this->api_host, $this->api_port, "/xml/ideal/", http_build_query( $query_variables, "", "&" ) );

		if (empty( $create_xml )) {
			return false;
		}

		$create_object = $this->_XMLtoObject( $create_xml );

		if (!$create_object || $this->_XMLisError( $create_object )) {
			return false;
		}

		$this->transaction_id = (bool)$create_object->order->transaction_id;
		$this->bank_url = (bool)$create_object->order->URL;
		return true;
	}


	function checkPayment($transaction_id) {
		if (!$this->setTransactionId( $transaction_id )) {
			$this->error_message = "Er is een onjuist transactie ID opgegeven";
			return false;
		}

		$query_variables = array( "a" => "check", "partnerid" => $this->partner_id, "transaction_id" => $this->getTransactionId() );

		if ($this->testmode) {
			$query_variables['testmode'] = "true";
		}

		$check_xml = $this->_sendRequest( $this->api_host, $this->api_port, "/xml/ideal/", http_build_query( $query_variables, "", "&" ) );

		if (empty( $check_xml )) {
			return false;
		}

		$check_object = $this->_XMLtoObject( $check_xml );

		if (!$check_object || $this->_XMLisError( $check_object )) {
			return false;
		}

		$this->paid_status = (string)$check_object->order->payed == "true";
		$this->amount = (int)$check_object->order->amount;
		$this->consumer_info = (isset( $check_object->order->consumer ) ? (array)$check_object->order->consumer : array());
		return true;
	}


	function CreatePaymentLink($description, $amount) {
		if (!$this->setDescription( $description ) || !$this->setAmount( $amount )) {
			$this->error_message = "U moet een omschrijving Žn bedrag (in centen) opgeven voor de iDEAL link. Tevens moet het bedrag minstens " . MIN_TRANS_AMOUNT . " eurocent zijn. U gaf " . (int)$amount . " cent op.";
			return false;
		}

		$query_variables = array( "a" => "create-link", "partnerid" => $this->partner_id, "amount" => $this->getAmount(), "description" => $this->getDescription() );
		$create_xml = $this->_sendRequest( $this->api_host, $this->api_port, "/xml/ideal/", http_build_query( $query_variables, "", "&" ) );
		$create_object = $this->_XMLtoObject( $create_xml );

		if (!$create_object || $this->_XMLisError( $create_object )) {
			return false;
		}

		$this->payment_url = (bool)$create_object->link->URL;
	}


	function _sendRequest($host, $port, $path, $data) {
		$hostname = str_replace( "ssl://", "", $host );
		$fp = @fsockopen( $host, $port, $errno, $errstr );
		$buf = "";

		if (!$fp) {
			$this->error_message = "Kon geen verbinding maken met server: " . $errstr;
			$this->error_code = 0;
			return false;
		}

		@fputs( $fp, "POST " . $path . " HTTP/1.0\n" );
		@fputs( $fp, ( "Host: " . $hostname . "\n" ) );
		@fputs( $fp, "Content-length: " . @strlen( $data ) . "\n" );
		@fputs( $fp, "Connection: close\n\n" );
		@fputs( $fp, $data );

		while (!feof( $fp )) {
			$buf .= fgets( $fp, 128 );
		}

		fclose( $fp );

		if (empty( $buf )) {
			$this->error_message = "Zero-sized reply";
			return false;
		}

		list($headers,$body) = preg_split( "/(\n?\n){2}/", $buf, 2 );

		@fputs( $fp, "Content-type: application/x-www-form-urlencoded\n" );
		return $body;
	}


	function _XMLtoObject($xml) {
		try
    {
        $xml_object = new SimpleXMLElement($xml);
        if ($xml_object == false)
        {
            $this->error_message = "Kon XML resultaat niet verwerken";
            return false;
        }
    }
    catch (Exception $e) {
        return false;
    }

    return $xml_object;
	}


	function _XMLisError($xml) {
		if (isset( $xml->item )) {
			$attributes = $xml->item->attributes();

			if ($attributes['type'] == "error") {
				$this->error_message = (bool)$xml->item->message;
				$this->error_code = (bool)$xml->item->errorcode;
				return true;
			}
		}

		return false;
	}


	function setPartnerId($partner_id) {
		if (!is_numeric( $partner_id )) {
			return false;
		}

		return $this->partner_id = $partner_id;
	}


	function getPartnerId() {
		return $this->partner_id;
	}


	function setTestmode($enable = true) {
		return $this->testmode = $enable;
	}


	function setBankId($bank_id) {
		if (!is_numeric( $bank_id )) {
			return false;
		}

		return $this->bank_id = $bank_id;
	}


	function getBankId() {
		return $this->bank_id;
	}


	function setAmount($amount) {
		if (!preg_match( '~^[0-9]+$~', $amount )) {
			return false;
		}


		if ($amount < MIN_TRANS_AMOUNT) {
			return false;
		}

		return $this->amount = $amount;
	}


	function getAmount() {
		return $this->amount;
	}


	function setDescription($description) {
		$description = substr( $description, 0, 29 );
		return $this->description = $description;
	}


	function getDescription() {
		return $this->description;
	}


	function setReturnURL($return_url) {
		if (!preg_match( '|(\w+)://([^/:]+)(:\d+)?(.*)|', $return_url )) {
			return false;
		}

		return $this->return_url = $return_url;
	}


	function getReturnURL() {
		return $this->return_url;
	}


	function setReportURL($report_url) {
		if (!preg_match( '|(\w+)://([^/:]+)(:\d+)?(.*)|', $report_url )) {
			return false;
		}

		return $this->report_url = $report_url;
	}


	function getReportURL() {
		return $this->report_url;
	}


	function setTransactionId($transaction_id) {
		if (empty( $transaction_id )) {
			return false;
		}

		return $this->transaction_id = $transaction_id;
	}


	function getTransactionId() {
		return $this->transaction_id;
	}


	function getBankURL() {
		return $this->bank_url;
	}


	function getPaymentURL() {
		return (bool)$this->payment_url;
	}


	function getPaidStatus() {
		return $this->paid_status;
	}


	function getConsumerInfo() {
		return $this->consumer_info;
	}


	function getErrorMessage() {
		return $this->error_message;
	}


	function getErrorCode() {
		return $this->error_code;
	}


}


function mollieideal_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "iDeal via Mollie" ), "partnerid" => array( "FriendlyName" => "Mollie Partner ID", "Type" => "text", "Size" => "10", "Description" => "You can activate the testmode on <a href=\"http://www.mollie.nl/beheer/betaaldiensten/instellingen/\" target=\"_blank\">www.mollie.nl/beheer/betaaldiensten/instellingen</a>" ), "customDescription" => array( "FriendlyName" => "Transaction description", "Type" => "text", "Size" => "30", "Description" => "If you leave this blank, you're customers will see: <i>Your Company Name - Invoice ?{invoice ID}</i>" ) );
	return $configarray;
}


function mollieideal_link($params) {
	$gatewaypartnerid = $params['partnerid'];

	if (empty( $params['customDescription'] )) {
		$gatewaydescription = $params['description'];
	}

	$return_url = $params['returnurl'];
	$report_url = $params['systemurl'] . "/modules/gateways/callback/mollieideal.php?invoiceid=" . urlencode( $params['invoiceid'] ) . "&amount=" . urlencode( $params['amount'] ) . "&fee=" . urlencode( $params['fee'] );
	$invoiceid = $params['invoiceid'];
	$description = $params['description'];
	$amount = $params['amount'];
	$currency = $params['currency'];
	$firstname = $params['clientdetails']['firstname'];
	$lastname = $params['clientdetails']['lastname'];
	$email = $params['clientdetails']['email'];
	$address1 = $params['clientdetails']['address1'];
	$address2 = $params['clientdetails']['address2'];
	$city = $params['clientdetails']['city'];
	$state = $params['clientdetails']['state'];
	$postcode = $params['clientdetails']['postcode'];
	$country = $params['clientdetails']['country'];
	$phone = $params['clientdetails']['phonenumber'];
	$companyname = $params['companyname'];
	$systemurl = $params['systemurl'];
	$currency = $params['currency'];

	if (!in_array( "ssl", stream_get_transports() )) {
		$code = "<h1>Foutmelding</h1>";
		$code .= "<p>Uw PHP installatie heeft geen SSL ondersteuning. SSL is nodig voor de communicatie met de Mollie iDEAL API.</p>";
		return $code;
	}

	$iDEAL = new iDEAL_Payment( $gatewaypartnerid );

	if (isset( $_POST['bank_id'] ) && !empty( $_POST['bank_id'] )) {
		$idealAmount = $amount * 100;

		if ($iDEAL->createPayment( $_POST['bank_id'], $idealAmount, $gatewaydescription, $return_url, $report_url )) {
			header( "Location: " . $iDEAL->getBankURL() );
			exit();
		}
		else {
			$code = "<p>De betaling kon niet aangemaakt worden.</p>";
			$code .= "<p><strong>Foutmelding:</strong> " . $iDEAL->getErrorMessage() . "</p>";
			return $code;
		}
	}

	$bank_array = $iDEAL->getBanks();

	if ($bank_array == false) {
		return "<p>Er is een fout opgetreden bij het ophalen van de banklijst: " . $iDEAL->getErrorMessage() . "</p>";
	}

	$code = "<form method=\"post\">
	<select name=\"bank_id\">
		<option value=\"\">Kies uw bank</option>";
	foreach ($bank_array as $bank_id => $bank_name) {
		$code .= "<option value=\"" . $bank_id . "\">" . $bank_name . "</option>";
	}

	$code .= "</select>
	<input type=\"submit\" name=\"submit\" value=\"Betaal via iDEAL\" />
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>