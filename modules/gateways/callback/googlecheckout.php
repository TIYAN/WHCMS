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
 **/

class GoogleResponse {
    public $merchant_id = null;
    public $merchant_key = null;
    public $schema_url = null;
    public $log = null;
    public $response = null;
    public $root = "";
    public $data = array();
    public $xml_parser = null;

	/**
	 * @param string $id the merchant id
	 * @param string $key the merchant key
	 */
	public function GoogleResponse($id = null, $key = null) {
		$this->merchant_id = $id;
		$this->merchant_key = $key;
		$this->schema_url = "http://checkout.google.com/schema/2";
		$this->log = new GoogleLog("", "", L_OFF);
	}

	/**
	 * @param string $id the merchant id
	 * @param string $key the merchant key
	 */
	public function SetMerchantAuthentication($id, $key) {
		$this->merchant_id = $id;
		$this->merchant_key = $key;
	}

	public function SetLogFiles($errorLogFile, $messageLogFile, $logLevel = L_ERR_RQST) {
		$this->log = new GoogleLog($errorLogFile, $messageLogFile, $logLevel);
	}

	/**
	 * Verifies that the authentication sent by Google Checkout matches the
	 * merchant id and key
	 *
	 * @param string $headers the headers from the request
	 */
	public function HttpAuthentication($headers = null, $die = true) {
		if (!is_null($headers)) {
			$_SERVER = $headers;
		}


		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$compare_mer_id = $_SERVER['PHP_AUTH_USER'];
			$compare_mer_key = $_SERVER['PHP_AUTH_PW'];
		}
		else {
			if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
				list($compare_mer_id,$compare_mer_key) = explode(":", base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], strpos($_SERVER['HTTP_AUTHORIZATION'], " ") + 1)));
			}
			else {
				if (isset($_SERVER['Authorization'])) {
					list($compare_mer_id,$compare_mer_key) = explode(":", base64_decode(substr($_SERVER['Authorization'], strpos($_SERVER['Authorization'], " ") + 1)));
				}
				else {
					$this->SendFailAuthenticationStatus("Failed to Get Basic Authentication Headers", $die);
					return false;
				}
			}
		}


		if ($compare_mer_id != $this->merchant_id || $compare_mer_key != $this->merchant_key) {
			$this->SendFailAuthenticationStatus("Invalid Merchant Id/Key Pair", $die);
			return false;
		}

		return true;
	}

	public function SendOKStatus() {
		header("HTTP/1.0 200 OK");
	}

	/**
	 * Set the response header indicating an erroneous authentication from
	 * Google Checkout
	 *
	 * @param string $msg the message to log
	 */
	public function SendFailAuthenticationStatus($msg = "401 Unauthorized Access", $die = true) {
		$this->log->logError($msg);
		header("WWW-Authenticate: Basic realm=\"GoogleCheckout PHPSample Code\"");
		header("HTTP/1.0 401 Unauthorized");

		if ($die) {
			exit($msg);
			return null;
		}

		echo $msg;
	}

	/**
	 * Send an acknowledgement in response to Google Checkout"s request
	 * @param string $serial serial number of notification for acknowledgement
	 */
	public function SendAck($serial = null, $die = true) {
		$this->SendOKStatus();
		$acknowledgment = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . "<notification-acknowledgment xmlns=\"" . $this->schema_url . "\"";

		if (isset($serial)) {
			$acknowledgment .= " serial-number=\"" . $serial . "\"";
		}

		$acknowledgment .= " />";
		$this->log->LogResponse($acknowledgment);

		if ($die) {
			exit($acknowledgment);
			return null;
		}

		echo $acknowledgment;
	}
}

class GoogleLog {
    public $errorLogFile = null;
    public $messageLogFile = null;
    public $logLevel = "";

	/**
	 * SetLogFiles
	 */
	public function GoogleLog($errorLogFile, $messageLogFile, $logLevel = L_ERR_RQST, $die = true) {
		$this->logLevel = $logLevel;

		if ($logLevel == L_OFF) {
			$this->logLevel = L_OFF;
		}
		else {
			if (!$this->errorLogFile = @fopen($errorLogFile, "a")) {
				header("HTTP/1.0 500 Internal Server Error");
				$log = "Cannot open " . $errorLogFile . " file.
" . "Logs are not writable, set them to 777";
				error_log($log, 0);

				if ($die) {
					exit($log);
				}
				else {
					echo $log;
					$this->logLevel = L_OFF;
				}
			}


			if (!$this->messageLogFile = @fopen($messageLogFile, "a")) {
				fclose($this->errorLogFile);
				header("HTTP/1.0 500 Internal Server Error");
				$log = "Cannot open " . $messageLogFile . " file.
" . "Logs are not writable, set them to 777";
				error_log($log, 0);

				if ($die) {
					exit($log);
				}
				else {
					echo $log;
					$this->logLevel = L_OFF;
				}
			}
		}

		$this->logLevel = $logLevel;
	}

	public function LogError($log) {
		if ($this->logLevel & L_ERR) {
			fwrite($this->errorLogFile, sprintf("
%s:- %s
", date("D M j G:i:s T Y"), $log));
			return true;
		}

		return false;
	}

	public function LogRequest($log) {
		if ($this->logLevel & L_RQST) {
			fwrite($this->messageLogFile, sprintf("
%s:- %s
", date("D M j G:i:s T Y"), $log));
			return true;
		}

		return false;
	}

	public function LogResponse($log) {
		if ($this->logLevel & L_RESP) {
			$this->LogRequest($log);
			return true;
		}

		return false;
	}
}

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
define("L_OFF", 0);
define("L_ERR", 1);
define("L_RQST", 2);
define("L_RESP", 4);
define("L_ERR_RQST", L_ERR | L_RQST);
define("L_ALL", L_ERR | L_RQST | L_RESP);
$GATEWAY = getGatewayVariables("googlecheckout");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$xml_response = (isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents("php://input"));
$Gresponse = new GoogleResponse($GATEWAY['merchantid'], $GATEWAY['merchantkey']);
$Gresponse->SetMerchantAuthentication($GATEWAY['merchantid'], $GATEWAY['merchantkey']);
$status = $Gresponse->HttpAuthentication();

if (!$status) {
	exit("authentication failed");
}

$raw_xml = $xml_response;
$Gresponse->SendAck(null, false);

if (get_magic_quotes_gpc()) {
	$xml_response = stripslashes($xml_response);
}

$xmldata = XMLtoArray($xml_response);

if (is_array($xmldata['CHARGE-AMOUNT-NOTIFICATION'])) {
	$ordernumber = $xmldata['CHARGE-AMOUNT-NOTIFICATION']['GOOGLE-ORDER-NUMBER'];
	$amount = $xmldata['CHARGE-AMOUNT-NOTIFICATION']['LATEST-CHARGE-AMOUNT'];
	$fee = $xmldata['CHARGE-AMOUNT-NOTIFICATION']['LATEST-CHARGE-FEE']['TOTAL'];
	$query = "SELECT data FROM tblgatewaylog WHERE gateway='Google Checkout' AND data LIKE '%new-order-notification%" . db_escape_string($ordernumber) . "%'";
	$result = full_query($query);
	$data = mysql_fetch_array($result);
	$gatewaylogdata = $data['data'];
	$orderxml = XMLtoArray($gatewaylogdata);
	$invoiceid = $orderxml['NEW-ORDER-NOTIFICATION']['SHOPPING-CART']['ITEMS']['ITEM']['MERCHANT-ITEM-ID'];
	$invoiceid = checkCbInvoiceID($invoiceid, "Google Checkout");
	checkCbTransID($ordernumber);

	if ($GATEWAY['convertto']) {
		$result = select_query("tblinvoices", "userid,total", array("id" => $invoiceid));
		$data = mysql_fetch_array($result);
		$userid = $data['userid'];
		$total = $data['total'];
		$currency = getCurrency($userid);
		$amount = convertCurrency($amount, $GATEWAY['convertto'], $currency['id']);
		$fee = convertCurrency($fee, $GATEWAY['convertto'], $currency['id']);

		if ($total < $amount + 1 && $amount - 1 < $total) {
			$amount = $total;
		}
	}

	addInvoicePayment($invoiceid, $ordernumber, $amount, $fee, "googlecheckout");
	logTransaction("Google Checkout", $xml_response, "Successful");
	return 1;
}

logTransaction("Google Checkout", $xml_response, "Status Update");
?>