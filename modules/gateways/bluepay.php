<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

class BluePayment {
	var $accountId = null;
	var $userId = null;
	var $tps = null;
	var $transType = null;
	var $payType = null;
	var $mode = null;
	var $masterId = null;
	var $secretKey = null;
	var $account = null;
	var $cvv2 = null;
	var $expire = null;
	var $ssn = null;
	var $birthdate = null;
	var $custId = null;
	var $custIdState = null;
	var $amount = null;
	var $name1 = null;
	var $name2 = null;
	var $addr1 = null;
	var $addr2 = null;
	var $city = null;
	var $state = null;
	var $zip = null;
	var $country = null;
	var $memo = null;
	var $orderId = null;
	var $invoiceId = null;
	var $tip = null;
	var $tax = null;
	var $doRebill = null;
	var $rebDate = null;
	var $rebExpr = null;
	var $rebCycles = null;
	var $rebAmount = null;
	var $doAutocap = null;
	var $avsAllowed = null;
	var $cvv2Allowed = null;
	var $response = null;
	var $transId = null;
	var $status = null;
	var $avsResp = null;
	var $cvv2Resp = null;
	var $authCode = null;
	var $message = null;
	var $rebid = null;

	function BluePayment($account = ACCOUNT_ID, $key = SECRET_KEY, $mode = MODE) {
		$this->accountId = $account;
		$this->secretKey = $key;
		$this->mode = $mode;
	}


	function sale($amount) {
		$this->transType = "SALE";
		$this->amount = BluePayment::formatamount( $amount );
	}


	function rebSale($transId, $amount = null) {
		$this->masterId = $transId;
		$this->sale( $amount );
	}


	function auth($amount) {
		$this->transType = "AUTH";
		$this->amount = BluePayment::formatamount( $amount );
	}


	function autocapAuth($amount, $avsAllow = null, $cvv2Allow = null) {
		$this->auth( $amount );
		$this->setAutocap();
		$this->addAvsProofing( $avsAllow );
		$this->addCvv2Proofing( $avsAllow );
	}


	function addLevel2Qual($orderId = null, $invoiceId = null, $tip = null, $tax = null) {
		$this->orderId = $orderId;
		$this->invoiceId = $invoiceId;
		$this->tip = $tip;
		$this->tax = $tax;
	}


	function refund($transId) {
		$this->transType = "REFUND";
		$this->masterId = $transId;
	}


	function capture($transId) {
		$this->transType = "CAPTURE";
		$this->masterId = $transId;
	}


	function rebCancel($transId) {
		$this->transType = "REBCANCEL";
		$this->masterId = $transId;
	}


	function rebAdd($amount, $date, $expr, $cycles) {
		$this->doRebill = "1";
		$this->rebAmount = BluePayment::formatamount( $amount );
		$this->rebDate = $date;
		$this->rebExpr = $expr;
		$this->rebCycles = $cycles;
	}


	function addAvsProofing($allow) {
		$this->avsAllowed = $allow;
	}


	function addCvv2Proofing($allow) {
		$this->cvv2Allowed = $allow;
	}


	function setAutocap() {
		$this->doAutocap = "1";
	}


	function setCustInfo($account, $cvv2, $expire, $name1, $name2, $addr1, $city, $state, $zip, $country, $addr2 = null, $memo = null) {
		$this->account = $account;
		$this->cvv2 = $cvv2;
		$this->expire = $expire;
		$this->name1 = $name1;
		$this->name2 = $name2;
		$this->addr1 = $addr1;
		$this->addr2 = $addr2;
		$this->city = $city;
		$this->state = $state;
		$this->zip = $zip;
		$this->country = $country;
		$this->memo = $memo;
	}


	function formatAmount($amount) {
		return sprintf( "%01.2f", (double)$amount );
	}


	function setOrderId($orderId) {
		$this->orderId = $orderId;
	}


	function calcTPS() {
		$hashstr = $this->secretKey . $this->accountId . $this->transType . $this->amount . $this->masterId . $this->name1 . $this->account;
		return md5( $hashstr );
	}


	function process() {
		$tps = $this->calcTPS();
		$fields = array( "ACCOUNT_ID" => $this->accountId, "USER_ID" => $this->userId, "TAMPER_PROOF_SEAL" => $tps, "TRANS_TYPE" => $this->transType, "PAYMENT_TYPE" => $this->payType, "MODE" => $this->mode, "MASTER_ID" => $this->masterId, "PAYMENT_ACCOUNT" => $this->account, "CARD_CVV2" => $this->cvv2, "CARD_EXPIRE" => $this->expire, "SSN" => $this->ssn, "BIRTHDATE" => $this->birthdate, "CUST_ID" => $this->custId, "CUST_ID_STATE" => $this->custIdState, "AMOUNT" => $this->amount, "NAME1" => $this->name1, "NAME2" => $this->name2, "ADDR1" => $this->addr1, "ADDR2" => $this->addr2, "CITY" => $this->city, "STATE" => $this->state, "ZIP" => $this->zip, "COUNTRY" => $this->country, "MEMO" => $this->memo, "ORDER_ID" => $this->orderId, "INVOICE_ID" => $this->invoiceId, "AMOUNT_TIP" => $this->tip, "AMOUNT_TAX" => $this->tax, "DO_REBILL" => $this->doRebill, "REB_FIRST_DATE" => $this->rebDate, "REB_EXPR" => $this->rebExpr, "REB_CYCLES" => $this->rebCycles, "REB_AMOUNT" => $this->rebAmount, "DO_AUTOCAP" => $this->doAutocap, "AVS_ALLOWED" => $this->avsAllowed, "CVV2_ALLOWED" => $this->cvv2Allowed );
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, POST_URL );
		curl_setopt( $ch, CURLOPT_USERAGENT, "BluepayPHP SDK/2.0" );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $fields ) );
		$this->response = curl_exec( $ch );
		curl_close( $ch );
		$this->parseResponse();
	}


	function parseResponse() {
		parse_str( $this->response, $array );
		$this->transId = $array["TRANS_ID"];
		$this->status = $array["STATUS"];
		$this->avsResp = $array["AVS"];
		$this->cvv2Resp = $array["CVV2"];
		$this->authCode = $array["AUTH_CODE"];
		$this->message = $array["MESSAGE"];
		$this->rebid = $array["REBID"];
	}


	function getResponse() {
		return $this->response;
	}


	function getTransId() {
		return $this->transId;
	}


	function getStatus() {
		return $this->status;
	}


	function getAvsResp() {
		return $this->avsResp;
	}


	function getCvv2Resp() {
		return $this->cvv2Resp;
	}


	function getAuthCode() {
		return $this->authCode;
	}


	function getMessage() {
		return $this->message;
	}


	function getRebid() {
		return $this->rebid;
	}


}


function bluepay_activate() {
	defineGatewayField( "bluepay", "text", "accountid", "", "Account ID", "20", "" );
	defineGatewayField( "bluepay", "text", "secretkey", "", "Secret Key", "40", "" );
	defineGatewayField( "bluepay", "yesno", "testmode", "", "Demo Mode", "", "" );
}


function bluepay_capture($params) {
	if ($params["testmode"] == "on") {
		$gateway_testmode = "TEST";
	}
	else {
		$gateway_testmode = "LIVE";
	}

	define( "MODE", $gateway_testmode );
	define( "POST_URL", "https://secure.bluepay.com/interfaces/bp20post" );
	define( "ACCOUNT_ID", $params["accountid"] );
	define( "SECRET_KEY", $params["secretkey"] );
	define( "STATUS_DECLINE", "0" );
	define( "STATUS_APPROVED", "1" );
	define( "STATUS_ERROR", "2" );
	$bp = new BluePayment();
	$bp->sale( $params["amount"] );
	$bp->setCustInfo( $params["cardnum"], $params["cccvv"], $params["cardexp"], $params["clientdetails"]["firstname"], $params["clientdetails"]["lastname"], $params["clientdetails"]["address1"], $params["clientdetails"]["city"], $params["clientdetails"]["state"], $params["clientdetails"]["postcode"], $params["clientdetails"]["country"] );
	$bp->invoiceId = $params["invoiceid"];
	$bp->process();
	$desc = "Action => Capture
Client => " . $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"] . "
";
	$desc .= "TransId => " . $bp->getTransId() . "
" . "Status => " . $bp->getStatus() . "
" . "AVS Resp => " . $bp->getAvsResp() . "
" . "CVV2 Resp => " . $bp->getCvv2Resp() . "
" . "Auth Code => " . $bp->getAuthCode() . "
" . "Message => " . $bp->getMessage() . "
";
	switch ($bp->getStatus()) {
	case "1": {
			array( "status" => "success", "transid" => $bp->getTransId(), "rawdata" => $desc );
		}
	}

	return ;
}


function bluepay_refund($params) {
	if ($params["testmode"] == "on") {
		$gateway_testmode = "TEST";
	}
	else {
		$gateway_testmode = "LIVE";
	}

	define( "MODE", $gateway_testmode );
	define( "POST_URL", "https://secure.bluepay.com/interfaces/bp20post" );
	define( "ACCOUNT_ID", $params["accountid"] );
	define( "SECRET_KEY", $params["secretkey"] );
	define( "STATUS_DECLINE", "0" );
	define( "STATUS_APPROVED", "1" );
	define( "STATUS_ERROR", "2" );
	$bp = new BluePayment();
	$bp->refund( $params["transid"] );
	$bp->setCustInfo( $params["cardnum"], "", $params["cardexp"], $params["clientdetails"]["firstname"], $params["clientdetails"]["lastname"], $params["clientdetails"]["address1"], $params["clientdetails"]["city"], $params["clientdetails"]["state"], $params["clientdetails"]["postcode"], $params["clientdetails"]["country"] );
	$bp->process();
	$desc = "Action => Refund
Client => " . $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"] . "
";
	$desc .= "TransId => " . $bp->getTransId() . "
" . "Status => " . $bp->getStatus() . "
" . "AVS Resp => " . $bp->getAvsResp() . "
" . "CVV2 Resp => " . $bp->getCvv2Resp() . "
" . "Auth Code => " . $bp->getAuthCode() . "
" . "Message => " . $bp->getMessage() . "
";
	switch ($bp->getStatus()) {
	case "1": {
			array( "status" => "success", "transid" => $bp->getTransId(), "rawdata" => $desc );
		}
	}

	return ;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["bluepayname"] = "bluepay";
$GATEWAYMODULE["bluepayvisiblename"] = "BluePay";
$GATEWAYMODULE["bluepaytype"] = "CC";

if (!class_exists( "BluePayment" )) {
}


if (!function_exists( "toString" )) {
	function toString($string) {
		if (preg_match( "/ /", $string )) {
			$elements = explode( " ", $string );
			$string = "";
			$f = true;
			foreach ($elements as $elem) {

				if ($f) {
					$string .= $elem;
					$f = false;
					continue;
				}

				$string .= "+" . $elem;
			}
		}

		return $string;
	}


}


if (!function_exists( "http_build_query" )) {
	function http_build_query($data) {
		$keys = array_keys( $data );
		$string = "";
		$f = true;
		foreach ($keys as $key) {

			if ($f) {
				$string .= $key . "=" . toString( $data[$key] );
				$f = false;
				continue;
			}

			$string .= "&" . $key . "=" . toString( $data[$key] );
		}

		return $string;
	}


}

?>