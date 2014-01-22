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

class monerisvault_mpgHttpsPost {
	var $environment = null;
	var $api_token = null;
	var $store_id = null;
	var $monerisvault_mpgRequest = null;
	var $monerisvault_mpgResponse = null;

	public function monerisvault_mpgHttpsPost($environment, $storeid, $apitoken, $monerisvault_mpgRequestOBJ) {
		$this->store_id = $storeid;
		$this->api_token = $apitoken;
		$this->monerisvault_mpgRequest = $monerisvault_mpgRequestOBJ;
		$dataToSend = $this->toXML();

		if ($environment == "live") {
			$globalarr = array("MONERIS_PROTOCOL" => "https", "MONERIS_HOST" => "www3.moneris.com", "MONERIS_PORT" => "443", "MONERIS_FILE" => "/gateway2/servlet/MpgRequest", "API_VERSION" => " CA PHP Api v.2.0.4 (Resolver)", "CLIENT_TIMEOUT" => "60");
		}
		else {
			$globalarr = array("MONERIS_PROTOCOL" => "https", "MONERIS_HOST" => "esqa.moneris.com", "MONERIS_PORT" => "443", "MONERIS_FILE" => "/gateway2/servlet/MpgRequest", "API_VERSION" => " CA PHP Api v.2.0.4 (Resolver)", "CLIENT_TIMEOUT" => "60");
		}

		$url = $globalarr['MONERIS_PROTOCOL'] . "://" . $globalarr['MONERIS_HOST'] . ":" . $globalarr['MONERIS_PORT'] . $globalarr['MONERIS_FILE'];
		logTransaction("Moneris Vault " . ucfirst($environment) . " Debug", $dataToSend, "Request to " . $url);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToSend);
		curl_setopt($ch, CURLOPT_TIMEOUT, $globalarr['CLIENT_TIMEOUT']);
		curl_setopt($ch, CURLOPT_USERAGENT, $globalarr['API_VERSION']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($ch);
		logTransaction("Moneris Vault " . ucfirst($environment) . " Debug", $response, "Response");
		curl_close($ch);

		if (!$response) {
			$response = "<?xml version=\"1.0\"?><response><receipt>" . "<ReceiptId>Global Error Receipt</ReceiptId>" . "<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>" . "<AuthCode>null</AuthCode><TransTime>null</TransTime>" . "<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>" . "<Message>Global Error Receipt</Message><TransAmount>null</TransAmount>" . "<CardType>null</CardType>" . "<TransID>null</TransID><TimedOut>null</TimedOut>" . "<CorporateCard>false</CorporateCard><MessageId>null</MessageId>" . "</receipt></response>";
		}

		$this->monerisvault_mpgResponse = new monerisvault_mpgResponse($response);
	}

	public function getmonerisvault_mpgResponse() {
		return $this->monerisvault_mpgResponse;
	}

	public function toXML() {
		$req = $this->monerisvault_mpgRequest;
		$reqXMLString = $req->toXML();
		$xmlString = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>" . "<request>" . ("<store_id>" . $this->store_id . "</store_id>") . ("<api_token>" . $this->api_token . "</api_token>") . $reqXMLString . "</request>";
		return $xmlString;
	}
}

class monerisvault_mpgResponse {
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
	var $resolveData = null;
	var $resolveDataHash = null;
	var $data_key = "";
	var $DataKeys = array();
	var $ACSUrl = null;

	public function monerisvault_mpgResponse($xmlString) {
		$this->p = xml_parser_create();
		xml_parser_set_option($this->p, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($this->p, XML_OPTION_TARGET_ENCODING, "UTF-8");
		xml_set_object($this->p, $this);
		xml_set_element_handler($this->p, "startHandler", "endHandler");
		xml_set_character_data_handler($this->p, "characterHandler");
		xml_parse($this->p, $xmlString);
		xml_parser_free($this->p);
	}

	public function getmonerisvault_mpgResponseData() {
		return $this->responseData;
	}

	public function getRecurSuccess() {
		return $this->responseData['RecurSuccess'];
	}

	public function getAvsResultCode() {
		return $this->responseData['AvsResultCode'];
	}

	public function getCvdResultCode() {
		return $this->responseData['CvdResultCode'];
	}

	public function getCardType() {
		return $this->responseData['CardType'];
	}

	public function getTransAmount() {
		return $this->responseData['TransAmount'];
	}

	public function getTxnNumber() {
		return $this->responseData['TransID'];
	}

	public function getReceiptId() {
		return $this->responseData['ReceiptId'];
	}

	public function getTransType() {
		return $this->responseData['TransType'];
	}

	public function getReferenceNum() {
		return $this->responseData['ReferenceNum'];
	}

	public function getResponseCode() {
		return $this->responseData['ResponseCode'];
	}

	public function getISO() {
		return $this->responseData['ISO'];
	}

	public function getBankTotals() {
		return $this->responseData['BankTotals'];
	}

	public function getMessage() {
		return $this->responseData['Message'];
	}

	public function getAuthCode() {
		return $this->responseData['AuthCode'];
	}

	public function getComplete() {
		return $this->responseData['Complete'];
	}

	public function getTransDate() {
		return $this->responseData['TransDate'];
	}

	public function getTransTime() {
		return $this->responseData['TransTime'];
	}

	public function getTicket() {
		return $this->responseData['Ticket'];
	}

	public function getTimedOut() {
		return $this->responseData['TimedOut'];
	}

	public function getCorporateCard() {
		return $this->responseData['CorporateCard'];
	}

	public function getRecurUpdateSuccess() {
		return $this->responseData['RecurUpdateSuccess'];
	}

	public function getNextRecurDate() {
		return $this->responseData['NextRecurDate'];
	}

	public function getRecurEndDate() {
		return $this->responseData['RecurEndDate'];
	}

	public function getDataKey() {
		return $this->responseData['DataKey'];
	}

	public function getResSuccess() {
		return $this->responseData['ResSuccess'];
	}

	public function getPaymentType() {
		return $this->responseData['PaymentType'];
	}

	public function getResolveData() {
		if ($this->responseData['ResolveData'] != "null") {
			return $this->resolveData;
		}

		return $this->responseData['ResolveData'];
	}

	public function setResolveData($data_key) {
		$this->resolveData = $this->resolveDataHash[$data_key];
	}

	public function getResolveDataHash() {
		return $this->resolveDataHash;
	}

	public function getDataKeys() {
		return $this->DataKeys;
	}

	public function getResDataDataKey() {
		return $this->resolveData['data_key'];
	}

	public function getResDataPaymentType() {
		return $this->resolveData['payment_type'];
	}

	public function getResDataCustId() {
		return $this->resolveData['cust_id'];
	}

	public function getResDataPhone() {
		return $this->resolveData['phone'];
	}

	public function getResDataEmail() {
		return $this->resolveData['email'];
	}

	public function getResDataNote() {
		return $this->resolveData['note'];
	}

	public function getResDataPan() {
		return $this->resolveData['pan'];
	}

	public function getResDataMaskedPan() {
		return $this->resolveData['masked_pan'];
	}

	public function getResDataExpDate() {
		return $this->resolveData['expdate'];
	}

	public function getResDataAvsStreetNumber() {
		return $this->resolveData['avs_street_number'];
	}

	public function getResDataAvsStreetName() {
		return $this->resolveData['avs_street_name'];
	}

	public function getResDataAvsZipcode() {
		return $this->resolveData['avs_zipcode'];
	}

	public function getResDataCryptType() {
		return $this->resolveData['crypt_type'];
	}

	public function getTerminalStatus($ecr_no) {
		return $this->ecrHash[$ecr_no];
	}

	public function getPurchaseAmount($ecr_no, $card_type) {
		return $this->purchaseHash[$ecr_no][$card_type]['Amount'] == "" ? 0 : $this->purchaseHash[$ecr_no][$card_type]['Amount'];
	}

	public function getPurchaseCount($ecr_no, $card_type) {
		return $this->purchaseHash[$ecr_no][$card_type]['Count'] == "" ? 0 : $this->purchaseHash[$ecr_no][$card_type]['Count'];
	}

	public function getRefundAmount($ecr_no, $card_type) {
		return $this->refundHash[$ecr_no][$card_type]['Amount'] == "" ? 0 : $this->refundHash[$ecr_no][$card_type]['Amount'];
	}

	public function getRefundCount($ecr_no, $card_type) {
		return $this->refundHash[$ecr_no][$card_type]['Count'] == "" ? 0 : $this->refundHash[$ecr_no][$card_type]['Count'];
	}

	public function getCorrectionAmount($ecr_no, $card_type) {
		return $this->correctionHash[$ecr_no][$card_type]['Amount'] == "" ? 0 : $this->correctionHash[$ecr_no][$card_type]['Amount'];
	}

	public function getCorrectionCount($ecr_no, $card_type) {
		return $this->correctionHash[$ecr_no][$card_type]['Count'] == "" ? 0 : $this->correctionHash[$ecr_no][$card_type]['Count'];
	}

	public function getTerminalIDs() {
		return $this->ecrs;
	}

	public function getCreditCardsAll() {
		return array_keys($this->cards);
	}

	public function getCreditCards($ecr) {
		return $this->cardHash[$ecr];
	}

	public function characterHandler($parser, $data) {
		if ($this->isBatchTotals) {
			switch ($this->currentTag) {
			case "term_id": {
					$this->term_id = $data;
					array_push($this->ecrs, $this->term_id);
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
					array_push($this->cardHash[$this->term_id], $data);
					break;
				}

			case "Amount": {
					if ($this->currentTxnType == "Purchase") {
						$this->purchaseHash[$this->term_id][$this->CardType]['Amount'] = $data;
					}
					else {
						if ($this->currentTxnType == "Refund") {
							$this->refundHash[$this->term_id][$this->CardType]['Amount'] = $data;
						}
						else {
							if ($this->currentTxnType == "Correction") {
								$this->correctionHash[$this->term_id][$this->CardType]['Amount'] = $data;
							}
						}
					}

					break;
				}

			case "Count": {
					if ($this->currentTxnType == "Purchase") {
						$this->purchaseHash[$this->term_id][$this->CardType]['Count'] = $data;
						break;
					}
					else {
						if ($this->currentTxnType == "Refund") {
							$this->refundHash[$this->term_id][$this->CardType]['Count'] = $data;
							break;
						}
						else {
							if ($this->currentTxnType == "Correction") {
								$this->correctionHash[$this->term_id][$this->CardType]['Count'] = $data;
							}
						}
					}
				}
			}

			return null;
		}


		if ($this->isResolveData && $this->currentTag != "ResolveData") {
			if ($this->currentTag == "data_key") {
				$this->data_key = $data;
				array_push($this->DataKeys, $this->data_key);
				$this->resolveData[$this->currentTag] .= $data;
				return null;
			}

			$this->currentTag;
			$this->resolveData[$this->currentTag] .= $data;
			return null;
		}

		$this->currentTag;
		$this->responseData[$this->currentTag] .= $data;
	}

	public function startHandler($parser, $name, $attrs) {
		$this->currentTag = $name;

		if ($this->currentTag == "ResolveData") {
			$this->isResolveData = 1;
		}
		else {
			if ($this->isResolveData) {
				$this->resolveData[$this->currentTag] = "";
			}
		}


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

	public function endHandler($parser, $name) {
		$this->currentTag = $name;

		if ($this->currentTag == "ResolveData") {
			$this->isResolveData = 0;

			if ($this->data_key != "") {
				$this->resolveDataHash[$this->data_key] = $this->resolveData;
				$this->resolveData = array();
			}
		}


		if ($name == "BankTotals") {
			$this->isBatchTotals = 0;
		}

		$this->currentTag = "/dev/null";
	}
}

class monerisvault_mpgRequest {
	var $txnTypes = array("purchase" => array(0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "pan", 4 => "expdate", 5 => "crypt_type"), "refund" => array(0 => "order_id", 1 => "amount", 2 => "txn_number", 3 => "crypt_type"), "idebit_purchase" => array(0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "idebit_track2"), "idebit_refund" => array(0 => "order_id", 1 => "amount", 2 => "txn_number"), "ind_refund" => array(0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "pan", 4 => "expdate", 5 => "crypt_type"), "preauth" => array(0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "pan", 4 => "expdate", 5 => "crypt_type"), "completion" => array(0 => "order_id", 1 => "comp_amount", 2 => "txn_number", 3 => "crypt_type"), "purchasecorrection" => array(0 => "order_id", 1 => "txn_number", 2 => "crypt_type"), "opentotals" => array(0 => "ecr_number"), "batchclose" => array(0 => "ecr_number"), "cavv_purchase" => array(0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "pan", 4 => "expdate", 5 => "cavv"), "cavv_preauth" => array(0 => "order_id", 1 => "cust_id", 2 => "amount", 3 => "pan", 4 => "expdate", 5 => "cavv"), "recur_update" => array(0 => "order_id", 1 => "cust_id", 2 => "pan", 3 => "expdate", 4 => "recur_amount", 5 => "add_num_recurs", 6 => "total_num_recurs", 7 => "hold", 8 => "terminate"), "res_add_cc" => array(0 => "cust_id", 1 => "phone", 2 => "email", 3 => "note", 4 => "pan", 5 => "expdate", 6 => "crypt_type"), "res_update_cc" => array(0 => "data_key", 1 => "cust_id", 2 => "phone", 3 => "email", 4 => "note", 5 => "pan", 6 => "expdate", 7 => "crypt_type"), "res_delete" => array(0 => "data_key"), "res_lookup_full" => array(0 => "data_key"), "res_lookup_masked" => array(0 => "data_key"), "res_get_expiring" => array(), "res_purchase_cc" => array(0 => "data_key", 1 => "order_id", 2 => "cust_id", 3 => "amount", 4 => "crypt_type"), "res_preauth_cc" => array(0 => "data_key", 1 => "order_id", 2 => "cust_id", 3 => "amount", 4 => "crypt_type"), "res_ind_refund_cc" => array(0 => "data_key", 1 => "order_id", 2 => "cust_id", 3 => "amount", 4 => "crypt_type"), "res_iscorporatecard" => array(0 => "data_key"));
	var $txnArray = null;

	public function monerisvault_mpgRequest($txn) {
		if (is_array($txn)) {
			$this->txnArray = $txn;
			return null;
		}

		$temp[0] = $txn;
		$this->txnArray = $temp;
	}

	public function getTransactionType() {
		$jtmp = $this->txnArray;
		$jtmp1 = $jtmp[0]->getTransaction();
		$jtmp2 = array_shift($jtmp1);
		return $jtmp2;
	}

	public function toXML() {
		$tmpTxnArray = $this->txnArray;
		$txnArrayLen = count($tmpTxnArray);
		$x = 0;

		while ($x < $txnArrayLen) {
			$txnObj = $tmpTxnArray[$x];
			$txn = $txnObj->getTransaction();
			$txnType = array_shift($txn);
			$tmpTxnTypes = $this->txnTypes;
			$txnTypeArray = $tmpTxnTypes[$txnType];
			$txnTypeArrayLen = count($txnTypeArray);
			$txnXMLString = "";
			$i = 0;

			while ($i < $txnTypeArrayLen) {
				if (array_key_exists($txnTypeArray[$i], $txn)) {
					$txnXMLString .= "<" . $txnTypeArray[$i] . ">" . $txn[$txnTypeArray[$i]] . ("</" . $txnTypeArray[$i] . ">");
				}

				++$i;
			}

			$txnXMLString = "<" . $txnType . ">" . $txnXMLString;
			$recur = $txnObj->getRecur();

			if ($recur != null) {
				$txnXMLString .= $recur->toXML();
			}

			$avs = $txnObj->getAvsInfo();

			if ($avs != null) {
				$txnXMLString .= $avs->toXML();
			}

			$cvd = $txnObj->getCvdInfo();

			if ($cvd != null) {
				$txnXMLString .= $cvd->toXML();
			}

			$custInfo = $txnObj->getCustInfo();

			if ($custInfo != null) {
				$txnXMLString .= $custInfo->toXML();
			}

			$txnXMLString .= "</" . $txnType . ">";
			$xmlString .= $txnXMLString;
			++$x;
		}

		return $xmlString;
	}
}

class monerisvault_mpgCustInfo {
	var $level3template = array("cust_info" => array(0 => "email", 1 => "instructions", "billing" => array(0 => "first_name", 1 => "last_name", 2 => "company_name", 3 => "address", 4 => "city", 5 => "province", 6 => "postal_code", 7 => "country", 8 => "phone_number", 9 => "fax", 10 => "tax1", 11 => "tax2", 12 => "tax3", 13 => "shipping_cost"), "shipping" => array(0 => "first_name", 1 => "last_name", 2 => "company_name", 3 => "address", 4 => "city", 5 => "province", 6 => "postal_code", 7 => "country", 8 => "phone_number", 9 => "fax", 10 => "tax1", 11 => "tax2", 12 => "tax3", 13 => "shipping_cost"), "item" => array(0 => "name", 1 => "quantity", 2 => "product_code", 3 => "extended_amount")));
	var $level3data = null;
	var $email = null;
	var $instructions = null;

	public function monerisvault_mpgCustInfo($custinfo = 0, $billing = 0, $shipping = 0, $items = 0) {
		if ($custinfo) {
			$this->setCustInfo($custinfo);
		}

	}

	public function setCustInfo($custinfo) {
		$this->level3data['cust_info'] = array($custinfo);
	}

	public function setEmail($email) {
		$this->email = $email;
		$this->setCustInfo(array("email" => $email, "instructions" => $this->instructions));
	}

	public function setInstructions($instructions) {
		$this->instructions = $instructions;
		$this->setCustinfo(array("email" => $this->email, "instructions" => $instructions));
	}

	public function setShipping($shipping) {
		$this->level3data['shipping'] = array($shipping);
	}

	public function setBilling($billing) {
		$this->level3data['billing'] = array($billing);
	}

	public function setItems($items) {
		if (!$this->level3data['item']) {
			$this->level3data['item'] = array($items);
			return null;
		}

		$index = count($this->level3data['item']);
		$this->level3data['item'][$index] = $items;
	}

	public function toXML() {
		$xmlString = $this->toXML_low($this->level3template, "cust_info");
		return $xmlString;
	}

	public function toXML_low($template, $txnType) {
		$x = 0;

		while ($x < count($this->level3data[$txnType])) {
			if (0 < $x) {
				$xmlString .= "</" . $txnType . "><" . $txnType . ">";
			}

			$keys = array_keys($template);
			$i = 0;

			while ($i < count($keys)) {
				$tag = $keys[$i];

				if (is_array($template[$keys[$i]])) {
					$data = $template[$tag];

					if (!count($this->level3data[$tag])) {
						continue;
					}

					$beginTag = "<" . $tag . ">";
					$endTag = "</" . $tag . ">";
					$xmlString .= $beginTag;
					$returnString = $this->toXML_low($data, $tag);
					$xmlString .= $returnString;
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

class monerisvault_mpgRecur {
	var $params = null;
	var $recurTemplate = array(0 => "recur_unit", 1 => "start_now", 2 => "start_date", 3 => "num_recurs", 4 => "period", 5 => "recur_amount");

	public function monerisvault_mpgRecur($params) {
		$this->params = $params;

		if (!$this->params['period']) {
			$this->params['period'] = 1;
		}

	}

	public function toXML() {
		$xmlString = "";
		foreach ($this->recurTemplate as $tag) {
			$xmlString .= "<" . $tag . ">" . $this->params[$tag] . ("</" . $tag . ">");
		}

		return "<recur>" . $xmlString . "</recur>";
	}
}

class monerisvault_mpgAvsInfo {
	var $params = null;
	var $avsTemplate = array(0 => "avs_street_number", 1 => "avs_street_name", 2 => "avs_zipcode");

	public function monerisvault_mpgAvsInfo($params) {
		$this->params = $params;
	}

	public function toXML() {
		$xmlString = "";
		foreach ($this->avsTemplate as $tag) {

			if (array_key_exists($tag, $this->params)) {
				$xmlString .= "<" . $tag . ">" . $this->params[$tag] . ("</" . $tag . ">");
				continue;
			}
		}

		return "<avs_info>" . $xmlString . "</avs_info>";
	}
}

class monerisvault_mpgCvdInfo {
	var $params = null;
	var $cvdTemplate = array(0 => "cvd_indicator", 1 => "cvd_value");

	public function monerisvault_mpgCvdInfo($params) {
		$this->params = $params;
	}

	public function toXML() {
		$xmlString = "";
		foreach ($this->cvdTemplate as $tag) {
			$xmlString .= "<" . $tag . ">" . $this->params[$tag] . ("</" . $tag . ">");
		}

		return "<cvd_info>" . $xmlString . "</cvd_info>";
	}
}

class monerisvault_mpgTransaction {
	var $txn = null;
	var $custInfo = "";
	var $recur = "";

	public function monerisvault_mpgTransaction($txn) {
		$this->txn = $txn;
	}

	public function getCustInfo() {
		return $this->custInfo;
	}

	public function setCustInfo($custInfo) {
		$this->custInfo = $custInfo;
		array_push($this->txn, $custInfo);
	}

	public function getRecur() {
		return $this->recur;
	}

	public function setRecur($recur) {
		$this->recur = $recur;
	}

	public function getTransaction() {
		return $this->txn;
	}

	public function getCvdInfo() {
		return $this->cvd;
	}

	public function setCvdInfo($cvd) {
		$this->cvd = $cvd;
	}

	public function getAvsInfo() {
		return $this->avs;
	}

	public function setAvsInfo($avs) {
		$this->avs = $avs;
	}
}

function monerisvault_config() {
	$configarray = array("FriendlyName" => array("Type" => "System", "Value" => "Moneris Vault"), "store_id" => array("FriendlyName" => "Store ID", "Type" => "text", "Size" => "20", "Description" => "The Store ID for your Moneris Account"), "api_token" => array("FriendlyName" => "API Token", "Type" => "text", "Size" => "20", "Description" => "The API Token for authenticating with the API"), "testmode" => array("FriendlyName" => "Test Mode", "Type" => "yesno"));
	return $configarray;
}

function monerisvault_storeremote($params) {
	if (!$params['action']) {
		return array("status" => "skipped", "rawdata" => "No Action Found. Skipped");
	}

	$store_id = $params['store_id'];
	$api_token = $params['api_token'];
	$environment = ($params['testmode'] ? "test" : "live");
	$data_key = $params['gatewayid'];
	$cust_id = $params['clientdetails']['userid'];
	$phone = $params['clientdetails']['phonenumber'];
	$email = $params['clientdetails']['email'];
	$note = "";
	$pan = $params['cardnum'];
	$expiry_date = substr($params['cardexp'], 2, 2) . substr($params['cardexp'], 0, 2);
	$crypt_type = "1";
	$avs_street_number = "";
	$avs_street_name = $params['clientdetails']['address1'];
	$avs_zipcode = $params['clientdetails']['postcode'];

	if ($params['action'] == "delete") {
		$type = "res_delete";
		$txnArray = array("type" => $type, "data_key" => $data_key);
		$mpgTxn = new monerisvault_mpgTransaction($txnArray);
		$monerisvault_mpgRequest = new monerisvault_mpgRequest($mpgTxn);
		$mpgHttpPost = new monerisvault_mpgHttpsPost($environment, $store_id, $api_token, $monerisvault_mpgRequest);
		$monerisvault_mpgResponse = $mpgHttpPost->getmonerisvault_mpgResponse();

		if ($monerisvault_mpgResponse->getResponseCode() != "null" && $monerisvault_mpgResponse->getResponseCode() < 50) {
			return array("status" => "success", "rawdata" => $monerisvault_mpgResponse->responseData);
		}

		return array("status" => "failed", "rawdata" => $monerisvault_mpgResponse->responseData);
	}


	if ($params['action'] == "update") {
		$type = "res_update_cc";
		$txnArray = array("type" => $type, "data_key" => $data_key, "cust_id" => $cust_id, "phone" => $phone, "email" => $email, "note" => $note, "pan" => $pan, "expdate" => $expiry_date, "crypt_type" => $crypt_type);
		$avsTemplate = array("avs_street_number" => $avs_street_number, "avs_street_name" => $avs_street_name, "avs_zipcode" => $avs_zipcode);
		$monerisvault_mpgAvsInfo = new monerisvault_mpgAvsInfo($avsTemplate);
		$mpgTxn = new monerisvault_mpgTransaction($txnArray);
		$mpgTxn->setAvsInfo($monerisvault_mpgAvsInfo);
		$monerisvault_mpgRequest = new monerisvault_mpgRequest($mpgTxn);
		$mpgHttpPost = new monerisvault_mpgHttpsPost($environment, $store_id, $api_token, $monerisvault_mpgRequest);
		$monerisvault_mpgResponse = $mpgHttpPost->getmonerisvault_mpgResponse();

		if ($monerisvault_mpgResponse->getResponseCode() != "null" && $monerisvault_mpgResponse->getResponseCode() < 50) {
			return array("status" => "success", "gatewayid" => $monerisvault_mpgResponse->getDataKey(), "rawdata" => $monerisvault_mpgResponse->responseData);
		}

		return array("status" => "failed", "rawdata" => $monerisvault_mpgResponse->responseData);
	}


	if ($params['action'] == "create") {
		$type = "res_add_cc";
		$txnArray = array("type" => $type, "cust_id" => $cust_id, "phone" => $phone, "email" => $email, "note" => $note, "pan" => $pan, "expdate" => $expiry_date, "crypt_type" => $crypt_type);
		$avsTemplate = array("avs_street_number" => $avs_street_number, "avs_street_name" => $avs_street_name, "avs_zipcode" => $avs_zipcode);
		$monerisvault_mpgAvsInfo = new monerisvault_mpgAvsInfo($avsTemplate);
		$mpgTxn = new monerisvault_mpgTransaction($txnArray);
		$mpgTxn->setAvsInfo($monerisvault_mpgAvsInfo);
		$monerisvault_mpgRequest = new monerisvault_mpgRequest($mpgTxn);
		$mpgHttpPost = new monerisvault_mpgHttpsPost($environment, $store_id, $api_token, $monerisvault_mpgRequest);
		$monerisvault_mpgResponse = $mpgHttpPost->getmonerisvault_mpgResponse();

		if ($monerisvault_mpgResponse->getResponseCode() != "null" && $monerisvault_mpgResponse->getResponseCode() < 50) {
			return array("status" => "success", "gatewayid" => $monerisvault_mpgResponse->getDataKey(), "rawdata" => $monerisvault_mpgResponse->responseData['Message']);
		}

		return array("status" => "failed", "rawdata" => $monerisvault_mpgResponse->responseData['Message']);
	}

}

function monerisvault_capture($params) {
	$environment = ($params['testmode'] ? "test" : "live");

	if (!$params['gatewayid'] && $params['cardnum']) {
		$storeremotedata = monerisvault_storeremote($params);

		if ($storeremotedata['status'] == "success") {
			$params['gatewayid'] = $storeremotedata['gatewayid'];

			if ($params['gatewayid']) {
				update_query("tblclients", array("gatewayid" => $params['gatewayid']), array("id" => $params['clientdetails']['userid']));
			}
			else {
				return array("status" => "failed", "rawdata" => "Error storing card with Moneris Vault. Received Empty Datakey. Capture Skipped. Error Detail: " . $storeremotedata['rawdata']);
			}
		}

		return array("status" => "failed", "rawdata" => "Error storing card with Moneris Vault. Capture Skipped. Error Detail: " . $storeremotedata['rawdata']);
	}


	if (!$params['gatewayid'] && !$params['cardnum']) {
		return array("status" => "skipped", "rawdata" => "No Card is stored with Moneris Vault. Capture Skipped. Try adding a card first");
	}

	$store_id = $params['store_id'];
	$api_token = $params['api_token'];
	$data_key = $params['gatewayid'];
	$orderid = "whmcs-inv" . $params['invoiceid'] . "-" . date("dmy-H:i:s");
	$amount = $params['amount'];
	$custid = $params['clientdetails']['userid'];
	$crypt_type = "1";
	$txnArray = array("type" => "res_purchase_cc", "data_key" => $data_key, "order_id" => $orderid, "cust_id" => $custid, "amount" => $amount, "crypt_type" => $crypt_type);
	$mpgTxn = new monerisvault_mpgTransaction($txnArray);
	$monerisvault_mpgRequest = new monerisvault_mpgRequest($mpgTxn);
	$mpgHttpPost = new monerisvault_mpgHttpsPost($environment, $store_id, $api_token, $monerisvault_mpgRequest);
	$monerisvault_mpgResponse = $mpgHttpPost->getmonerisvault_mpgResponse();

	if ($monerisvault_mpgResponse->getResponseCode() != "null" && $monerisvault_mpgResponse->getResponseCode() < 50) {
		return array("status" => "success", "transid" => $monerisvault_mpgResponse->getReferenceNum(), "rawdata" => $monerisvault_mpgResponse->responseData['Message']);
	}

	return array("status" => "declined", "rawdata" => $monerisvault_mpgResponse->responseData['Message']);
}

function monerisvault_refund($params) {
	$store_id = $params['store_id'];
	$api_token = $params['api_token'];
	$data_key = $params['gatewayid'];
	$orderid = "whmcs-ref" . $params['invoiceid'] . "-" . date("dmy-H:i:s");
	$amount = $params['amount'];
	$custid = $params['clientdetails']['userid'];
	$crypt_type = "1";
	$txnArray = array("type" => "res_ind_refund_cc", "data_key" => $data_key, "order_id" => $orderid, "cust_id" => $custid, "amount" => $amount, "crypt_type" => $crypt_type);
	$mpgTxn = new monerisvault_mpgTransaction($txnArray);
	$monerisvault_mpgRequest = new monerisvault_mpgRequest($mpgTxn);
	$environment = ($params['testmode'] ? "test" : "live");
	$mpgHttpPost = new monerisvault_mpgHttpsPost($environment, $store_id, $api_token, $monerisvault_mpgRequest);
	$monerisvault_mpgResponse = $mpgHttpPost->getmonerisvault_mpgResponse();

	if ($monerisvault_mpgResponse->getResponseCode() != "null" && $monerisvault_mpgResponse->getResponseCode() < 50) {
		return array("status" => "success", "transid" => $monerisvault_mpgResponse->getReferenceNum(), "rawdata" => $monerisvault_mpgResponse->responseData['Message']);
	}

	return array("status" => "declined", "rawdata" => serialize($monerisvault_mpgResponse->responseData['Message']));
}


if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}

?>