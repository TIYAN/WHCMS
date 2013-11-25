<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

class mpgGlobals {
	var $Globals = array( "MONERIS_PROTOCOL" => "https", "MONERIS_HOST" => "esqa.moneris.com", "MONERIS_PORT" => "443", "MONERIS_FILE" => "/gateway2/servlet/MpgRequest", "API_VERSION" => "MpgApi Version 2.03(php)", "CLIENT_TIMEOUT" => "60" );

	function mpgGlobals($test_mode = false) {
		if (!$test_mode) {
			$this->Globals["MONERIS_HOST"] = "www3.moneris.com";
		}

	}


	function getGlobals() {
		return $this->Globals;
	}


}


class mpgHttpsPost {
	var $api_token = null;
	var $store_id = null;
	var $mpgRequest = null;
	var $mpgResponse = null;

	function mpgHttpsPost($store_id, $api_token, $mpgRequestOBJ, $test_mode = false) {
		$this->api_token = $api_token;
		$this->mpgRequest = $mpgRequestOBJ;
		$dataToSend = $this->toXML();
		$g = new mpgGlobals( $test_mode );
		$g->getGlobals();
		$gArray = $this->store_id = $store_id;
		$url = $gArray["MONERIS_PROTOCOL"] . "://" . $gArray["MONERIS_HOST"] . ":" . $gArray["MONERIS_PORT"] . $gArray["MONERIS_FILE"];
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $dataToSend );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $gArray["CLIENT_TIMEOUT"] );
		curl_setopt( $ch, CURLOPT_USERAGENT, $gArray["API_VERSION"] );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		$response = curl_exec( $ch );
		curl_close( $ch );

		if (!$response) {
			$response = "<?xml version=\"1.0\"?><response><receipt>" . "<ReceiptId>Global Error Receipt</ReceiptId>" . "<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>" . "<ISO>null</ISO> <AuthCode>null</AuthCode><TransTime>null</TransTime>" . "<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>" . "<Message>null</Message><TransAmount>null</TransAmount>" . "<CardType>null</CardType>" . "<TransID>null</TransID><TimedOut>null</TimedOut>" . "</receipt></response>";
		}

		$this->mpgResponse = new mpgResponse( $response );
	}


	function getMpgResponse() {
		return $this->mpgResponse;
	}


	function toXML() {
		$req = $this->mpgRequest;
		$reqXMLString = $req->toXML();
		$xmlString = "";
		$xmlString .= "<?xml version=\"1.0\"?>" . "<request>" . ( "<store_id>" . $this->store_id . "</store_id>" ) . ( "<api_token>" . $this->api_token . "</api_token>" ) . $reqXMLString . "</request>";
		return $xmlString;
	}


}


class mpgResponse {
	var $responseData = null;
	var $p = null;
	var $currentTag = null;
	var $purchaseHash = array();
	var $refundHash = null;
	var $correctionHash = array();
	var $isBatchTotals = null;
	var $term_id = null;
	var $receiptHash = array();
	var $ecrHash = array();
	var $CardType = null;
	var $currentTxnType = null;
	var $ecrs = array();
	var $cards = array();
	var $cardHash = array();
	var $ACSUrl = null;

	function mpgResponse($xmlString) {
		$this->p = xml_parser_create();
		xml_parser_set_option( $this->p, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $this->p, XML_OPTION_TARGET_ENCODING, "UTF-8" );
		xml_set_object( $this->p, $this );
		xml_set_element_handler( $this->p, "startHandler", "endHandler" );
		xml_set_character_data_handler( $this->p, "characterHandler" );
		xml_parse( $this->p, $xmlString );
		xml_parser_free( $this->p );
	}


	function getMpgResponseData() {
		return $this->responseData;
	}


	function getAvsResultCode() {
		return $this->responseData["AvsResultCode"];
	}


	function getCvdResultCode() {
		return $this->responseData["CvdResultCode"];
	}


	function getRecurSuccess() {
		return $this->responseData["RecurSuccess"];
	}


	function getCardType() {
		return $this->responseData["CardType"];
	}


	function getTransAmount() {
		return $this->responseData["TransAmount"];
	}


	function getTxnNumber() {
		return $this->responseData["TransID"];
	}


	function getReceiptId() {
		return $this->responseData["ReceiptId"];
	}


	function getTransType() {
		return $this->responseData["TransType"];
	}


	function getReferenceNum() {
		return $this->responseData["ReferenceNum"];
	}


	function getResponseCode() {
		return $this->responseData["ResponseCode"];
	}


	function getISO() {
		return $this->responseData["ISO"];
	}


	function getBankTotals() {
		return $this->responseData["BankTotals"];
	}


	function getMessage() {
		return $this->responseData["Message"];
	}


	function getAuthCode() {
		return $this->responseData["AuthCode"];
	}


	function getComplete() {
		return $this->responseData["Complete"];
	}


	function getTransDate() {
		return $this->responseData["TransDate"];
	}


	function getTransTime() {
		return $this->responseData["TransTime"];
	}


	function getTicket() {
		return $this->responseData["Ticket"];
	}


	function getTimedOut() {
		return $this->responseData["TimedOut"];
	}


	function getTerminalStatus($ecr_no) {
		return $this->ecrHash[$ecr_no];
	}


	function getPurchaseAmount($ecr_no, $card_type) {
		return $this->purchaseHash[$ecr_no][$card_type]["Amount"] == "" ? 0 : $this->purchaseHash[$ecr_no][$card_type]["Amount"];
	}


	function getPurchaseCount($ecr_no, $card_type) {
		return $this->purchaseHash[$ecr_no][$card_type]["Count"] == "" ? 0 : $this->purchaseHash[$ecr_no][$card_type]["Count"];
	}


	function getRefundAmount($ecr_no, $card_type) {
		return $this->refundHash[$ecr_no][$card_type]["Amount"] == "" ? 0 : $this->refundHash[$ecr_no][$card_type]["Amount"];
	}


	function getRefundCount($ecr_no, $card_type) {
		return $this->refundHash[$ecr_no][$card_type]["Count"] == "" ? 0 : $this->refundHash[$ecr_no][$card_type]["Count"];
	}


	function getCorrectionAmount($ecr_no, $card_type) {
		return $this->correctionHash[$ecr_no][$card_type]["Amount"] == "" ? 0 : $this->correctionHash[$ecr_no][$card_type]["Amount"];
	}


	function getCorrectionCount($ecr_no, $card_type) {
		return $this->correctionHash[$ecr_no][$card_type]["Count"] == "" ? 0 : $this->correctionHash[$ecr_no][$card_type]["Count"];
	}


	function getTerminalIDs() {
		return $this->ecrs;
	}


	function getCreditCardsAll() {
		return array_keys( $this->cards );
	}


	function getCreditCards($ecr_no) {
		return $this->cardHash[$ecr_no];
	}


	function characterHandler($parser, $data) {
		if ($this->isBatchTotals) {
			switch ($this->currentTag) {
			case "term_id": {
					$this->term_id = $data;
					array_push( $this->ecrs, $this->term_id );
					$this->cardHash[$data] = array();
					break;
				}

			case "closed": {
					$ecrHash = $this->ecrHash;
					$ecrHash[$this->term_id] = $data;
					$this->ecrHash = $ecrHash;
					break;
				}

			case "CardType": {
					$this->CardType = $data;
					$this->cards[$data] = $data;
					array_push( $this->cardHash[$this->term_id], $data );
					break;
				}

			case "Amount": {
					if ($this->currentTxnType == "Purchase") {
						$this->purchaseHash[$this->term_id][$this->CardType]["Amount"] = $data;
					}
					else {
						if ($this->currentTxnType == "Refund") {
							$this->refundHash[$this->term_id][$this->CardType]["Amount"] = $data;
						}
						else {
							if ($this->currentTxnType == "Correction") {
								$this->correctionHash[$this->term_id][$this->CardType]["Amount"] = $data;
							}
						}
					}

					break;
				}

			case "Count": {
					if ($this->currentTxnType == "Purchase") {
						$this->purchaseHash[$this->term_id][$this->CardType]["Count"] = $data;
						break;
					}
					else {
						if ($this->currentTxnType == "Refund") {
							$this->refundHash[$this->term_id][$this->CardType]["Count"] = $data;
							break;
						}
						else {
							if ($this->currentTxnType == "Correction") {
								$this->correctionHash[$this->term_id][$this->CardType]["Count"] = $data;
							}
						}
					}
				}
			}

			return null;
		}

		$this->responseData[$this->currentTag] .= $data;
	}


	function startHandler($parser, $name, $attrs) {
		$this->currentTag = $name;

		if ($this->currentTag == "BankTotals") {
			$this->isBatchTotals = 1;
			return null;
		}


		if ($this->currentTag == "Purchase") {
			$this->purchaseHash[$this->term_id][$this->CardType] = array();
			$this->currentTxnType = "Purchase";
			return null;
		}


		if ($this->currentTag == "Refund") {
			$this->refundHash[$this->term_id][$this->CardType] = array();
			$this->currentTxnType = "Refund";
			return null;
		}


		if ($this->currentTag == "Correction") {
			$this->correctionHash[$this->term_id][$this->CardType] = array();
			$this->currentTxnType = "Correction";
		}

	}


	function endHandler($parser, $name) {
		$this->currentTag = $name;

		if ($name == "BankTotals") {
			$this->isBatchTotals = 0;
		}

		$this->currentTag = "/dev/null";
	}


}


class mpgRequest {
	var $txnTypes = array( "purchase" => array( 0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "pan", 4 => "expdate", 5 => "crypt_type" ), "refund" => array( 0 => "order_id", 1 => "amount", 2 => "txn_number", 3 => "crypt_type" ), "idebit_purchase" => array( 0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "idebit_track2" ), "idebit_refund" => array( 0 => "order_id", 1 => "amount", 2 => "txn_number" ), "ind_refund" => array( 0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "pan", 4 => "expdate", 5 => "crypt_type" ), "preauth" => array( 0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "pan", 4 => "expdate", 5 => "crypt_type" ), "completion" => array( 0 => "order_id", 1 => "comp_amount", 2 => "txn_number", 3 => "crypt_type" ), "purchasecorrection" => array( 0 => "order_id", 1 => "txn_number", 2 => "crypt_type" ), "opentotals" => array( 0 => "ecr_number" ), "batchclose" => array( 0 => "ecr_number" ), "cavv_purchase" => array( 0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "pan", 4 => "expdate", 5 => "cavv" ), "cavv_preauth" => array( 0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "pan", 4 => "expdate", 5 => "cavv" ) );
	var $txnArray = null;

	function mpgRequest($txn) {
		if (is_array( $txn )) {
			$txn = $txn[0];
		}

		$this->txnArray = $txn;
	}


	function toXML() {
		$tmpTxnArray = $this->txnArray;
		$txnArrayLen = count( $tmpTxnArray );
		$txnObj = $tmpTxnArray;
		$txn = $txnObj->getTransaction();
		$txnType = array_shift( $txn );
		$tmpTxnTypes = $this->txnTypes;
		$txnTypeArray = $tmpTxnTypes[$txnType];
		$txnTypeArrayLen = count( $txnTypeArray );
		$txnXMLString = "";
		$i = 14;

		while ($i < $txnTypeArrayLen) {
			$txnXMLString .= "<" . $txnTypeArray[$i] . ">" . $txn[$txnTypeArray[$i]] . ( "</" . $txnTypeArray[$i] . ">" );
			++$i;
		}

		$txnXMLString = "<" . $txnType . ">" . $txnXMLString;
		$recur = $txnObj->getRecur();

		if ($recur != null) {
			$txnXMLString .= $recur->toXML();
		}

		$avsInfo = $txnObj->getAvsInfo();

		if ($avsInfo != null) {
			$txnXMLString .= $avsInfo->toXML();
		}

		$cvdInfo = $txnObj->getCvdInfo();

		if ($cvdInfo != null) {
			$txnXMLString .= $cvdInfo->toXML();
		}

		$custInfo = $txnObj->getCustInfo();

		if ($custInfo != null) {
			$txnXMLString .= $custInfo->toXML();
		}

		$txnXMLString .= "</" . $txnType . ">";
		$xmlString = "";
		$xmlString .= $txnXMLString;
		return $xmlString;
	}


}


class mpgCustInfo {
	var $level3template = array( "cust_info" => array( 0 => "email", 1 => "instructions", "billing" => array( 0 => "first_name", 1 => "last_name", 2 => "company_name", 3 => "address", 4 => "city", 5 => "province", 6 => "postal_code", 7 => "country", 8 => "phone_number", 9 => "fax", 10 => "tax1", 11 => "tax2", 12 => "tax3", 13 => "shipping_cost" ), "shipping" => array( 0 => "first_name", 1 => "last_name", 2 => "company_name", 3 => "address", 4 => "city", 5 => "province", 6 => "postal_code", 7 => "country", 8 => "phone_number", 9 => "fax", 10 => "tax1", 11 => "tax2", 12 => "tax3", 13 => "shipping_cost" ), "item" => array( 0 => "name", 1 => "quantity", 2 => "product_code", 3 => "extended_amount" ) ) );
	var $level3data = null;
	var $email = null;
	var $instructions = null;

	function mpgCustInfo($custinfo = 0, $billing = 0, $shipping = 0, $items = 0) {
		if ($custinfo) {
			$this->setCustInfo( $custinfo );
		}

	}


	function setCustInfo($custinfo) {
		$this->level3data["cust_info"] = array( $custinfo );
	}


	function setEmail($email) {
		$this->email = $email;
		$this->setCustInfo( array( "email" => $email, "instructions" => $this->instructions ) );
	}


	function setInstructions($instructions) {
		$this->instructions = $instructions;
		$this->setCustinfo( array( "email" => $this->email, "instructions" => $instructions ) );
	}


	function setShipping($shipping) {
		$this->level3data["shipping"] = array( $shipping );
	}


	function setBilling($billing) {
		$this->level3data["billing"] = array( $billing );
	}


	function setItems($items) {
		if (!isset( $this->level3data["item"] )) {
			$this->level3data["item"] = array( $items );
			return null;
		}

		$index = count( $this->level3data["item"] );
		$this->level3data["item"][$index] = $items;
	}


	function toXML() {
		$xmlString = $this->toXML_low( $this->level3template, "cust_info" );
		return $xmlString;
	}


	function toXML_low($template, $txnType) {
		$x = 15;

		while ($x < count( $this->level3data[$txnType] )) {
			if (0 < $x) {
				$xmlString .= "</" . $txnType . "><" . $txnType . ">";
			}

			$keys = array_keys( $template );
			$i = 15;

			while ($i < count( $keys )) {
				$tag = $keys[$i];

				if (is_array( $template[$keys[$i]] )) {
					$data = $template[$tag];

					if (!count( $this->level3data[$tag] )) {
						continue;
					}

					$beginTag = "<" . $tag . ">";
					$endTag = "</" . $tag . ">";
					$xmlString .= $beginTag;

					if (is_array( $data )) {
						$returnString = $this->toXML_low( $data, $tag );
						$xmlString .= $returnString;
					}

					$xmlString .= $endTag;
				}
				else {
					$tag = $template[$keys[$i]];
					$beginTag = "<" . $tag . ">";
					$endTag = "</" . $tag . ">";
					$data = $this->level3data[$txnType][$x][$tag];
					$xmlString .= $beginTag . $data . $endTag;
				}

				++$i;
			}

			++$x;
		}

		return $xmlString;
	}


}


class mpgRecur {
	var $params = null;
	var $recurTemplate = array( 0 => "recur_unit", 1 => "start_now", 2 => "start_date", 3 => "num_recurs", 4 => "period", 5 => "recur_amount" );

	function mpgRecur($params) {
		$this->params = $params;

		if (!$this->params["period"]) {
			$this->params["period"] = 1;
		}

	}


	function toXML() {
		$xmlString = "";
		foreach ($this->recurTemplate as $tag) {
			$xmlString .= "<" . $tag . ">" . $this->params[$tag] . ( "</" . $tag . ">" );
		}

		return "<recur>" . $xmlString . "</recur>";
	}


}


class mpgTransaction {
	var $txn = null;
	var $custInfo = null;
	var $avsInfo = null;
	var $cvdInfo = null;
	var $recur = null;

	function mpgTransaction($txn) {
		$this->txn = $txn;
	}


	function getCustInfo() {
		return $this->custInfo;
	}


	function setCustInfo($custInfo) {
		$this->custInfo = $custInfo;
		array_push( $this->txn, $custInfo );
	}


	function getCvdInfo() {
		return $this->cvdInfo;
	}


	function setCvdInfo($cvdInfo) {
		$this->cvdInfo = $cvdInfo;
	}


	function getAvsInfo() {
		return $this->avsInfo;
	}


	function setAvsInfo($avsInfo) {
		$this->avsInfo = $avsInfo;
	}


	function getRecur() {
		return $this->recur;
	}


	function setRecur($recur) {
		$this->recur = $recur;
	}


	function getTransaction() {
		return $this->txn;
	}


}


class mpgAvsInfo {
	var $params = null;
	var $avsTemplate = array( 0 => "avs_street_number", 1 => "avs_street_name", 2 => "avs_zipcode" );

	function mpgAvsInfo($params) {
		$this->params = $params;
	}


	function toXML() {
		$xmlString = "";
		foreach ($this->avsTemplate as $tag) {
			$xmlString .= "<" . $tag . ">" . $this->params[$tag] . ( "</" . $tag . ">" );
		}

		return "<avs_info>" . $xmlString . "</avs_info>";
	}


}


class mpgCvdInfo {
	var $params = null;
	var $cvdTemplate = array( 0 => "cvd_indicator", 1 => "cvd_value" );

	function mpgCvdInfo($params) {
		$this->params = $params;
	}


	function toXML() {
		$xmlString = "";
		foreach ($this->cvdTemplate as $tag) {
			$xmlString .= "<" . $tag . ">" . $this->params[$tag] . ( "</" . $tag . ">" );
		}

		return "<cvd_info>" . $xmlString . "</cvd_info>";
	}


}


function moneris_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Moneris" ), "store_id" => array( "FriendlyName" => "Store ID", "Type" => "text", "Size" => "12", "Description" => "A value that identifies your company when your send a transaction" ), "api_token" => array( "FriendlyName" => "API token", "Type" => "text", "Size" => "20", "Description" => "A unique key that when matched with your store_id creates a secure method of authenticating your store_id" ), "order_id_format" => array( "FriendlyName" => "Order ID format", "Type" => "text", "Size" => "20", "Description" => "Enter the format for the Moneris order_id numbers eg. WHMCS-%s. Token will be replaced with actual invoice id." ), "testmode" => array( "FriendlyName" => "Test Environment", "Type" => "yesno", "Description" => "When set, the transaction will be a test transaction only" ) );
	return $configarray;
}


function moneris_capture($params) {
	$txnArray = array( "type" => "purchase", "crypt_type" => 7 );
	$store_id = ($params["testmode"] ? "store1" : $params["store_id"]);
	$api_token = ($params["testmode"] ? "yesguy" : $params["api_token"]);
	$test_mode = ($params["testmode"] ? true : false);
	$txnArray["order_id"] = sprintf( $params["order_id_format"], uniqid( $params["invoiceid"] . "." ) );
	$txnArray["cust_id"] = $params["clientdetails"]["email"];
	$txnArray["amount"] = $params["amount"];
	$txnArray["pan"] = $params["cardnum"];
	$txnArray["expdate"] = substr( $params["cardexp"], 2, 2 ) . substr( $params["cardexp"], 0, 2 );
	$mpgTxn = new mpgTransaction( $txnArray );
	$mpgRequest = new mpgRequest( $mpgTxn );
	$mpgHttpPost = new mpgHttpsPost( $store_id, $api_token, $mpgRequest, $test_mode );
	$mpgResponse = $mpgHttpPost->getMpgResponse();
	$m_result = array( "CardType" => $mpgResponse->getCardType(), "TransAmount" => $mpgResponse->getTransAmount(), "TxnNumber" => $mpgResponse->getTxnNumber(), "ReceiptId" => $mpgResponse->getReceiptId(), "TransType" => $mpgResponse->getTransType(), "ReferenceNum" => $mpgResponse->getReferenceNum(), "ResponseCode" => $mpgResponse->getResponseCode(), "ISO" => $mpgResponse->getISO(), "Message" => $mpgResponse->getMessage(), "AuthCode" => $mpgResponse->getAuthCode(), "Complete" => $mpgResponse->getComplete(), "TransDate" => $mpgResponse->getTransDate(), "TransTime" => $mpgResponse->getTransTime(), "Ticket" => $mpgResponse->getTicket(), "TimedOut" => $mpgResponse->getTimedOut() );
	$responseCode = ("null" == $mpgResponse->getResponseCode() ? null : (int)$mpgResponse->getResponseCode());

	if (null === $responseCode) {
		$result = array( "status" => "error", "rawdata" => $m_result );
	}
	else {
		if (( 0 <= $responseCode && $responseCode < 50 )) {
			$result = array( "status" => "success", "transid" => $m_result["TxnNumber"], "rawdata" => $m_result );
		}
		else {
			$result = array( "status" => "declined", "rawdata" => $m_result );
		}
	}

	return $result;
}


function moneris_refund($params) {
	$txnArray = array( "type" => "ind_refund", "crypt_type" => 7 );
	$store_id = ($params["testmode"] ? "store1" : $params["store_id"]);
	$api_token = ($params["testmode"] ? "yesguy" : $params["api_token"]);
	$test_mode = ($params["testmode"] ? true : false);
	$txnArray["order_id"] = sprintf( $params["order_id_format"], uniqid( $params["invoiceid"] . "." ) );
	$txnArray["cust_id"] = $params["clientdetails"]["email"];
	$txnArray["amount"] = $params["amount"];
	$txnArray["pan"] = $params["cardnum"];
	$txnArray["expdate"] = substr( $params["cardexp"], 2, 2 ) . substr( $params["cardexp"], 0, 2 );
	$mpgTxn = new mpgTransaction( $txnArray );
	$mpgRequest = new mpgRequest( $mpgTxn );
	$mpgHttpPost = new mpgHttpsPost( $store_id, $api_token, $mpgRequest, $test_mode );
	$mpgResponse = $mpgHttpPost->getMpgResponse();
	$m_result = array( "CardType" => $mpgResponse->getCardType(), "TransAmount" => $mpgResponse->getTransAmount(), "TxnNumber" => $mpgResponse->getTxnNumber(), "ReceiptId" => $mpgResponse->getReceiptId(), "TransType" => $mpgResponse->getTransType(), "ReferenceNum" => $mpgResponse->getReferenceNum(), "ResponseCode" => $mpgResponse->getResponseCode(), "ISO" => $mpgResponse->getISO(), "Message" => $mpgResponse->getMessage(), "AuthCode" => $mpgResponse->getAuthCode(), "Complete" => $mpgResponse->getComplete(), "TransDate" => $mpgResponse->getTransDate(), "TransTime" => $mpgResponse->getTransTime(), "Ticket" => $mpgResponse->getTicket(), "TimedOut" => $mpgResponse->getTimedOut() );
	$responseCode = ("null" == $mpgResponse->getResponseCode() ? null : intval( $mpgResponse->getResponseCode() ));

	if (null === $responseCode) {
		$result = array( "status" => "error", "rawdata" => $m_result );
	}
	else {
		if (( 0 <= $responseCode && $responseCode < 50 )) {
			$result = array( "status" => "success", "transid" => $m_result["TxnNumber"], "rawdata" => $m_result );
		}
		else {
			$result = array( "status" => "declined", "rawdata" => $m_result );
		}
	}

	return $result;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>