<?php
/**
 *
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 * */


class Security {


	/**
	 *  reads in a certificate file and creates a fingerprint
	 *  @param Filename of the certificate
	 *  @return fingerprint
	 */
	function createCertFingerprint($filename) {
		fopen( dirname( __FILE__ ) . '/security/' . $filename, 'r' );

		if (!$fp) {
			return false;
		}

		$cert = fread( $fp, 8192 );
		openssl_x509_read( $cert );

		if (!openssl_x509_export( $data, $data )) {
			return false;
		}

		$data = $data = $fp = $data = fclose( $fp );
		$data = base64_decode( $data );
		sha1( $data );
		$fingerprint = str_replace( '-----BEGIN CERTIFICATE-----', '', $data );
		strtoupper( $fingerprint );
		$fingerprint = str_replace( '-----END CERTIFICATE-----', '', $data );
		return $fingerprint;
	}


	/**
	 * function to sign a message
	 *
	 * @param filename of the private key
	 * @param message to sign
	 * @return signature
	 */
	function signMessage($priv_keyfile, $key_pass, $data) {
		$fp = $data = preg_replace( '/\s/', '', $data );
		fread( $fp, 8192 );
		$priv_key = fopen( dirname( __FILE__ ) . '/security/' . $priv_keyfile, 'r' );
		openssl_get_privatekey( $priv_key, $key_pass );
		$pkeyid = fclose( $fp );
		openssl_sign( $data, $signature, $pkeyid );
		openssl_free_key( $pkeyid );
		return $signature;
	}


	/**
	 * function to verify a message
	 *
	 * @param filename of the public key to decrypt the signature
	 * @param message to verify
	 * @param sent    signature
	 * @return signature
	 */
	function verifyMessage($certfile, $data, $signature) {
		$ok = 195;
		$fp = fopen( dirname( __FILE__ ) . '/security/' . $certfile, 'r' );

		if (!$fp) {
			return false;
		}

		$cert = fread( $fp, 8192 );
		fclose( $fp );
		$pubkeyid = openssl_get_publickey( $cert );
		$ok = openssl_verify( $data, $signature, $pubkeyid );
		openssl_free_key( $pubkeyid );
		return $ok;
	}


	/**
	 *
	 *
	 * @param fingerprint thatґs been sent
	 * @param the     configuration file loaded in as an array
	 * @return the filename of the certificate with this fingerprint
	 */
	function getCertificateName($fingerprint, $config) {
		$count = 185;
		$certFilename = $config['CERTIFICATE' . $count];

		while (isset( $certFilename )) {
			$buff = $this->createCertFingerprint( $certFilename );

			if ($fingerprint == $buff) {
				return $certFilename;
			}

			$count += 186;
			$certFilename = $config['CERTIFICATE' . $count];
		}

		return false;
	}


}


class IssuerBean {
	var $issuerID = '';
	var $issuerName = '';
	var $issuerList = '';


	/**
	 *
	 *
	 * @returns a readable representation of the IssuerBean
	 */
	function toString() {
		return 'IssuerBean: issuerID=' . $this->issuerID . ' issuerName=' . $this->issuerName . ' issuerList=' . $this->issuerList;
	}


	/**
	 *
	 *
	 * @return Returns the issuerID.
	 */
	function getIssuerID() {
		return $this->issuerID;
	}


	/**
	 *
	 *
	 * @param issuerID The issuerID to set.
	 */
	function setIssuerID($issuerID) {
		$this->issuerID = $issuerID;
	}


	/**
	 *
	 *
	 * @return Returns the issuerList. ("Short", "Long")
	 */
	function getIssuerList() {
		return $this->issuerList;
	}


	/**
	 *
	 *
	 * @param issuerList The issuerList to set.
	 */
	function setIssuerList($issuerList) {
		$this->issuerList = $issuerList;
	}


	/**
	 *
	 *
	 * @return Returns the issuerName.
	 */
	function getIssuerName() {
		return $this->issuerName;
	}


	/**
	 *
	 *
	 * @param issuerName The issuerName to set.
	 */
	function setIssuerName($issuerName) {
		$this->issuerName = $issuerName;
	}


}


class IdealRequest {
	var $merchantID = '';
	var $subID = '';
	var $authentication = '';


	/**
	 * clears all parameters
	 */
	function clear() {
		$this->merchantID = '';
		$this->subID = '';
		$this->authentication = '';
	}


	/**
	 *
	 *
	 * @returns a readable representation of the Class
	 */
	function toString() {
		return 'IdealRequest: merchantID = ' . $this->merchantID . ' subID = ' . $this->subID . ' authentication = ' . $this->authentication;
	}


	/**
	 * this method checks, whether all mandatory properties are set.
	 *
	 * @return true if all fields are valid, otherwise returns false
	 */
	function checkMandatory() {
		if (( ( 0 < strlen( $this->merchantID ) && 0 < strlen( $this->subID ) ) && 0 < strlen( $this->authentication ) )) {
			return true;
		}

		return false;
	}


	/**
	 *
	 *
	 * @return Returns the authentication.
	 */
	function getAuthentication() {
		return $this->authentication;
	}


	/**
	 *
	 *
	 * @param authentication The type of authentication to set.
	 * Currently only "RSA_SHA1" is implemented. (mandatory)
	 */
	function setAuthentication($authentication) {
		$this->authentication = trim( $authentication );
	}


	/**
	 *
	 *
	 * @return Returns the merchantID.
	 */
	function getMerchantID() {
		return $this->merchantID;
	}


	/**
	 *
	 *
	 * @param merchantID The merchantID to set. (mandatory)
	 */
	function setMerchantID($merchantID) {
		$this->merchantID = trim( $merchantID );
	}


	/**
	 *
	 *
	 * @return Returns the subID.
	 */
	function getSubID() {
		return $this->subID;
	}


	/**
	 *
	 *
	 * @param subID   The subID to set. (mandatory)
	 */
	function setSubID($subID) {
		$this->subID = trim( $subID );
	}


}


class IdealResponse {
	var $ok = ;
	var $errorMessage = '';
	var $errorCode = '';
	var $errorDetail = '';
	var $suggestedAction = '';
	var $suggestedExpirationPeriod = '';
	var $consumerMessage = '';


	/**
	 *
	 *
	 * @return If an error has ocurred during the previous Request, this method returns a detailed
	 * message about what went wrong. isOk() returnes false in that case.
	 */
	function getErrorMessage() {
		return $this->errorMessage;
	}


	/**
	 * sets the error string
	 *
	 * @param errorMessage The errorMessage to set.
	 */
	function setErrorMessage($errorMessage) {
		$this->errorMessage = $errorMessage;
	}


	function setErrorCode($errorCode) {
		$this->errorCode = $errorCode;
	}


	function getErrorCode() {
		return $this->errorCode;
	}


	function setErrorDetail($errorDetail) {
		$this->errorDetail = $errorDetail;
	}


	function getErrorDetail() {
		return $this->errorDetail;
	}


	function setSuggestedAction($suggestedAction) {
		$this->suggestedAction = $suggestedAction;
	}


	function getSuggestedAction() {
		return $this->suggestedAction;
	}


	function setSuggestedExpirationPeriod($suggestedExpirationPeriod) {
		$this->suggestedExpirationPeriod = $suggestedExpirationPeriod;
	}


	function getSuggestedExpirationPeriod() {
		return $this->suggestedExpirationPeriod;
	}


	function setConsumerMessage($consumerMessage) {
		$this->consumerMessage = $consumerMessage;
	}


	function getConsumerMessage() {
		return $this->consumerMessage;
	}


	/**
	 *
	 *
	 * @return true, if the request was processed successfully, otherwise false. If
	 * false, additional information can be received calling getErrorMessage()
	 */
	function isOk() {
		return $this->ok;
	}


	/**
	 *
	 *
	 * @param ok      sets the OK flag
	 */
	function setOk($ok) {
		$this->ok = $ok;
	}


}


class DirectoryRequest extends IdealRequest {


	/**
	 * clears all parameters
	 */
	function clear() {
		IdealRequest::clear(  );
	}


	/**
	 * this method checks, whether all mandatory properties are set.
	 *
	 * @return true if all fields are valid, otherwise returns false
	 */
	function checkMandatory() {
		if (IdealRequest::checkmandatory(  )) {
			return true;
		}

		return false;
	}


	/**
	 *
	 *
	 * @returns a readable representation of the Class
	 */
	function toString() {
		return 'IdealRequest: merchantID = ' . $this->merchantID . ' subID = ' . $this->subID . ' authentication = ' . $this->authentication;
	}


	/**
	 *
	 *
	 * @return Returns the authentication.
	 */
	function getAuthentication() {
		return $this->authentication;
	}


	/**
	 *
	 *
	 * @param authentication The type of authentication to set.
	 * Currently only "RSA_SHA1" is implemented. (mandatory)
	 */
	function setAuthentication($authentication) {
		$this->authentication = trim( $authentication );
	}


	/**
	 *
	 *
	 * @return Returns the merchantID.
	 */
	function getMerchantID() {
		return $this->merchantID;
	}


	/**
	 *
	 *
	 * @param merchantID The merchantID to set. (mandatory)
	 */
	function setMerchantID($merchantID) {
		$this->merchantID = trim( $merchantID );
	}


	/**
	 *
	 *
	 * @return Returns the subID.
	 */
	function getSubID() {
		return $this->subID;
	}


	/**
	 *
	 *
	 * @param subID   The subID to set. (mandatory)
	 */
	function setSubID($subID) {
		$this->subID = trim( $subID );
	}


}


class DirectoryResponse extends IdealResponse {
	var $acquirerID = '';
	var $issuerList = array(  );


	/**
	 *
	 *
	 * @return Returns a list if IssuerBean objects.
	 * The List contains all Issuers that were send by the acquirer System during the Directory Request.
	 * The Issuers are stored as IssuerBean objects.
	 */
	function getIssuerList() {
		return $this->issuerList;
	}


	/**
	 *
	 *
	 * @return Returns the acquirerID from the answer XML message.
	 */
	function getAcquirerID() {
		return $this->acquirerID;
	}


	/**
	 *
	 *
	 * @param sets    the acquirerID
	 */
	function setAcqirerID($acquirerID) {
		$this->acquirerID = $acquirerID;
	}


	/**
	 * adds an Issuer to the IssuerList
	 */
	function addIssuer($bean) {
		if (is_a( $bean, 'IssuerBean' )) {
			array_push( $this->issuerList, $bean );
		}

	}


	/**
	 *
	 *
	 * @return If an error has ocurred during the previous Request, this method returns a detailed
	 * message about what went wrong. isOk() returnes false in that case.
	 */
	function getErrorMessage() {
		return $this->errorMessage;
	}


	/**
	 * sets the error string
	 *
	 * @param errorMessage The errorMessage to set.
	 */
	function setErrorMessage($errorMessage) {
		$this->errorMessage = $errorMessage;
	}


	function setErrorCode($errorCode) {
		$this->errorCode = $errorCode;
	}


	function getErrorCode() {
		return $this->errorCode;
	}


	function setErrorDetail($errorDetail) {
		$this->errorDetail = $errorDetail;
	}


	function getErrorDetail() {
		return $this->errorDetail;
	}


	function setSuggestedAction($suggestedAction) {
		$this->suggestedAction = $suggestedAction;
	}


	function getSuggestedAction() {
		return $this->suggestedAction;
	}


	function setSuggestedExpirationPeriod($suggestedExpirationPeriod) {
		$this->suggestedExpirationPeriod = $suggestedExpirationPeriod;
	}


	function getSuggestedExpirationPeriod() {
		return $this->suggestedExpirationPeriod;
	}


	function setConsumerMessage($consumerMessage) {
		$this->consumerMessage = $consumerMessage;
	}


	function getConsumerMessage() {
		return $this->consumerMessage;
	}


	/**
	 *
	 *
	 * @return true, if the request was processed successfully, otherwise false. If
	 * false, additional information can be received calling getErrorMessage()
	 */
	function isOk() {
		return $this->ok;
	}


	/**
	 *
	 *
	 * @param ok      sets the OK flag
	 */
	function setOk($ok) {
		$this->ok = $ok;
	}


}


class AcquirerStatusRequest extends IdealRequest {
	var $transactionID = '';


	/**
	 * rests all input data to empty strings
	 */
	function clear() {
		IdealRequest::clear(  );
		$this->transactionID = '';
	}


	/**
	 * this method checks, wheather all mandatory properties were set.
	 * If done so, true is returned, otherwise false.
	 *
	 * @return If done so, true is returned, otherwise false.
	 */
	function checkMandatory() {
		if (( IdealRequest::checkmandatory(  ) && 0 < strlen( $this->transactionID ) )) {
			return true;
		}

		return false;
	}


	/**
	 *
	 *
	 * @returns a readable representation of the Class
	 */
	function toString() {
		return IdealRequest::tostring(  ) . ' AcquirerStatusRequest: transactionID = ' . $this->transactionID;
	}


	/**
	 *
	 *
	 * @return Returns the transactionID.
	 */
	function getTransactionID() {
		return $this->transactionID;
	}


	/**
	 *
	 *
	 * @param transactionID The transactionID of the corresponding transaction. (mandatory)
	 */
	function setTransactionID($transactionID) {
		$this->transactionID = $transactionID;
	}


	/**
	 *
	 *
	 * @return Returns the authentication.
	 */
	function getAuthentication() {
		return $this->authentication;
	}


	/**
	 *
	 *
	 * @param authentication The type of authentication to set.
	 * Currently only "RSA_SHA1" is implemented. (mandatory)
	 */
	function setAuthentication($authentication) {
		$this->authentication = trim( $authentication );
	}


	/**
	 *
	 *
	 * @return Returns the merchantID.
	 */
	function getMerchantID() {
		return $this->merchantID;
	}


	/**
	 *
	 *
	 * @param merchantID The merchantID to set. (mandatory)
	 */
	function setMerchantID($merchantID) {
		$this->merchantID = trim( $merchantID );
	}


	/**
	 *
	 *
	 * @return Returns the subID.
	 */
	function getSubID() {
		return $this->subID;
	}


	/**
	 *
	 *
	 * @param subID   The subID to set. (mandatory)
	 */
	function setSubID($subID) {
		$this->subID = trim( $subID );
	}


}


class AcquirerStatusResponse extends IdealResponse {
	var $authenticated = ;
	var $consumerName = '';
	var $consumerAccountNumber = '';
	var $consumerCity = '';
	var $transactionID = '';
	var $status = '';


	/**
	 *
	 *
	 * @return Returns true, if the transaction was authenticated, otherwise false.
	 */
	function isAuthenticated() {
		return $this->authenticated;
	}


	/**
	 *
	 *
	 * @param authenticated The authenticated flag to be set.
	 */
	function setAuthenticated($authenticated) {
		$this->authenticated = $authenticated;
	}


	/**
	 *
	 *
	 * @return Returns the consumerAccountNumber.
	 */
	function getConsumerAccountNumber() {
		return $this->consumerAccountNumber;
	}


	/**
	 *
	 *
	 * @param consumerAccountNumber The consumerAccountNumber to set.
	 */
	function setConsumerAccountNumber($consumerAccountNumber) {
		$this->consumerAccountNumber = $consumerAccountNumber;
	}


	/**
	 *
	 *
	 * @return Returns the consumerCity.
	 */
	function getConsumerCity() {
		return $this->consumerCity;
	}


	/**
	 *
	 *
	 * @param consumerCity The consumerCity to set.
	 */
	function setConsumerCity($consumerCity) {
		$this->consumerCity = $consumerCity;
	}


	/**
	 *
	 *
	 * @return Returns the consumerName.
	 */
	function getConsumerName() {
		return $this->consumerName;
	}


	/**
	 *
	 *
	 * @param consumerName The consumerName to set.
	 */
	function setConsumerName($consumerName) {
		$this->consumerName = $consumerName;
	}


	/**
	 *
	 *
	 * @return Returns the transactionID.
	 */
	function getTransactionID() {
		return $this->transactionID;
	}


	/**
	 *
	 *
	 * @param transactionID The transactionID to set.
	 */
	function setTransactionID($transactionID) {
		$this->transactionID = $transactionID;
	}


	/**
	 *
	 *
	 * @return Returns the status.
	 */
	function getStatus() {
		return $this->status;
	}


	/**
	 *
	 *
	 * @param status  The status to set.
	 */
	function setStatus($status) {
		$this->status = $status;
	}


	/**
	 *
	 *
	 * @return If an error has ocurred during the previous Request, this method returns a detailed
	 * message about what went wrong. isOk() returnes false in that case.
	 */
	function getErrorMessage() {
		return $this->errorMessage;
	}


	/**
	 * sets the error string
	 *
	 * @param errorMessage The errorMessage to set.
	 */
	function setErrorMessage($errorMessage) {
		$this->errorMessage = $errorMessage;
	}


	function setErrorCode($errorCode) {
		$this->errorCode = $errorCode;
	}


	function getErrorCode() {
		return $this->errorCode;
	}


	function setErrorDetail($errorDetail) {
		$this->errorDetail = $errorDetail;
	}


	function getErrorDetail() {
		return $this->errorDetail;
	}


	function setSuggestedAction($suggestedAction) {
		$this->suggestedAction = $suggestedAction;
	}


	function getSuggestedAction() {
		return $this->suggestedAction;
	}


	function setSuggestedExpirationPeriod($suggestedExpirationPeriod) {
		$this->suggestedExpirationPeriod = $suggestedExpirationPeriod;
	}


	function getSuggestedExpirationPeriod() {
		return $this->suggestedExpirationPeriod;
	}


	function setConsumerMessage($consumerMessage) {
		$this->consumerMessage = $consumerMessage;
	}


	function getConsumerMessage() {
		return $this->consumerMessage;
	}


	/**
	 *
	 *
	 * @return true, if the request was processed successfully, otherwise false. If
	 * false, additional information can be received calling getErrorMessage()
	 */
	function isOk() {
		return $this->ok;
	}


	/**
	 *
	 *
	 * @param ok      sets the OK flag
	 */
	function setOk($ok) {
		$this->ok = $ok;
	}


}


class AcquirerTrxRequest extends IdealRequest {
	var $issuerID = '';
	var $merchantReturnURL = '';
	var $purchaseID = '';
	var $amount = '';
	var $currency = '';
	var $expirationPeriod = '';
	var $language = '';
	var $description = '';
	var $entranceCode = '';


	/**
	 *
	 *
	 * @return Returns the amount.
	 */
	function getAmount() {
		return $this->amount;
	}


	/**
	 *
	 *
	 * @param amount  The amount to set. (mandatory)
	 */
	function setAmount($amount) {
		$this->amount = $amount;
	}


	/**
	 *
	 *
	 * @return Returns the currency.
	 */
	function getCurrency() {
		return $this->currency;
	}


	/**
	 *
	 *
	 * @param currency The currency to set, e.g. "EUR". (mandatory)
	 */
	function setCurrency($currency) {
		$this->currency = $currency;
	}


	/**
	 *
	 *
	 * @return Returns the payment description.
	 */
	function getDescription() {
		return $this->description;
	}


	/**
	 *
	 *
	 * @param description The payment description to set. (optional)
	 */
	function setDescription($description) {
		if ($description != null) {
			$this->description = $description;
		}

	}


	/**
	 *
	 *
	 * @return Returns the entranceCode.
	 */
	function getEntranceCode() {
		return $this->entranceCode;
	}


	/**
	 *
	 *
	 * @param entranceCode The entranceCode to set. (mandatory)
	 */
	function setEntranceCode($entranceCode) {
		$this->entranceCode = $entranceCode;
	}


	/**
	 *
	 *
	 * @return Returns the expirationPeriod.
	 */
	function getExpirationPeriod() {
		return $this->expirationPeriod;
	}


	/**
	 *
	 *
	 * @param expirationPeriod The expirationPeriod to set. (mandatory)
	 */
	function setExpirationPeriod($expirationPeriod) {
		$this->expirationPeriod = $expirationPeriod;
	}


	/**
	 *
	 *
	 * @return Returns the issuerID.
	 */
	function getIssuerID() {
		return $this->issuerID;
	}


	/**
	 *
	 *
	 * @param issuerID The issuerID to set. (mandatory)
	 */
	function setIssuerID($issuerID) {
		$this->issuerID = $issuerID;
	}


	/**
	 *
	 *
	 * @return Returns the language.
	 */
	function getLanguage() {
		return $this->language;
	}


	/**
	 *
	 *
	 * @param language The language to set, e.g "nl". (mandatory)
	 */
	function setLanguage($language) {
		$this->language = $language;
	}


	/**
	 *
	 *
	 * @return Returns the merchantReturnURL.
	 */
	function getMerchantReturnURL() {
		return $this->merchantReturnURL;
	}


	/**
	 *
	 *
	 * @param merchantReturnURL The merchantReturnURL to set. (mandatory)
	 */
	function setMerchantReturnURL($merchantReturnURL) {
		$this->merchantReturnURL = $merchantReturnURL;
	}


	/**
	 *
	 *
	 * @return Returns the purchaseID.
	 */
	function getPurchaseID() {
		return $this->purchaseID;
	}


	/**
	 *
	 *
	 * @param purchaseID The purchaseID to set. (mandatory)
	 */
	function setPurchaseID($purchaseID) {
		$this->purchaseID = $purchaseID;
	}


	function clear() {
		IdealRequest::clear(  );
		$this->issuerID = '';
		$this->merchantReturnURL = '';
		$this->purchaseID = '';
		$this->amount = '';
		$this->currency = '';
		$this->expirationPeriod = '';
		$this->language = '';
		$this->description = '';
		$this->entranceCode = '';
	}


	/**
	 * this method checks, whether all mandatory properties were set.
	 * If done so, true is returned, otherwise false.
	 *
	 * @return If done so, true is returned, otherwise false.
	 */
	function checkMandatory() {
		if (( ( ( ( ( ( ( ( ( IdealRequest::checkmandatory(  ) == true && 0 < strlen( $this->issuerID ) ) && 0 < strlen( $this->merchantReturnURL ) ) && 0 < strlen( $this->purchaseID ) ) && 0 < strlen( $this->amount ) ) && 0 < strlen( $this->currency ) ) && 0 < strlen( $this->expirationPeriod ) ) && 0 < strlen( $this->language ) ) && 0 < strlen( $this->entranceCode ) ) && 0 < strlen( $this->description ) )) {
			return true;
		}

		return false;
	}


	/**
	 *
	 *
	 * @returns a readable representation of the Class
	 */
	function toString() {
		return IdealRequest::tostring(  ) . ' AcquirerTrxRequest: issuerID = ' . $this->issuerID . ' merchantReturnURL = ' . $this->merchantReturnURL . ' purchaseID = ' . $this->purchaseID . ' amount = ' . $this->amount . ' currency = ' . $this->currency . ' expirationPeriod = ' . $this->expirationPeriod . ' language = ' . $this->language . ' entranceCode = ' . $this->entranceCode . ' description = ' . $this->description;
	}


	/**
	 *
	 *
	 * @return Returns the authentication.
	 */
	function getAuthentication() {
		return $this->authentication;
	}


	/**
	 *
	 *
	 * @param authentication The type of authentication to set.
	 * Currently only "RSA_SHA1" is implemented. (mandatory)
	 */
	function setAuthentication($authentication) {
		$this->authentication = trim( $authentication );
	}


	/**
	 *
	 *
	 * @return Returns the merchantID.
	 */
	function getMerchantID() {
		return $this->merchantID;
	}


	/**
	 *
	 *
	 * @param merchantID The merchantID to set. (mandatory)
	 */
	function setMerchantID($merchantID) {
		$this->merchantID = trim( $merchantID );
	}


	/**
	 *
	 *
	 * @return Returns the subID.
	 */
	function getSubID() {
		return $this->subID;
	}


	/**
	 *
	 *
	 * @param subID   The subID to set. (mandatory)
	 */
	function setSubID($subID) {
		$this->subID = trim( $subID );
	}


}


class AcquirerTrxResponse extends IdealResponse {
	var $acquirerID = null;
	var $issuerAuthenticationURL = null;
	var $transactionID = null;


	/**
	 *
	 *
	 * @return Returns the acquirerID.
	 */
	function getAcquirerID() {
		return $this->acquirerID;
	}


	/**
	 *
	 *
	 * @param acquirerID The acquirerID to set. (mandatory)
	 */
	function setAcquirerID($acquirerID) {
		$this->acquirerID = $acquirerID;
	}


	/**
	 *
	 *
	 * @return Returns the issuerAuthenticationURL.
	 */
	function getIssuerAuthenticationURL() {
		return $this->issuerAuthenticationURL;
	}


	/**
	 *
	 *
	 * @param issuerAuthenticationURL The issuerAuthenticationURL to set.
	 */
	function setIssuerAuthenticationURL($issuerAuthenticationURL) {
		$this->issuerAuthenticationURL = $issuerAuthenticationURL;
	}


	/**
	 *
	 *
	 * @return Returns the transactionID.
	 */
	function getTransactionID() {
		return $this->transactionID;
	}


	/**
	 *
	 *
	 * @param transactionID The transactionID to set.
	 */
	function setTransactionID($transactionID) {
		$this->transactionID = $transactionID;
	}


	/**
	 *
	 *
	 * @return If an error has ocurred during the previous Request, this method returns a detailed
	 * message about what went wrong. isOk() returnes false in that case.
	 */
	function getErrorMessage() {
		return $this->errorMessage;
	}


	/**
	 * sets the error string
	 *
	 * @param errorMessage The errorMessage to set.
	 */
	function setErrorMessage($errorMessage) {
		$this->errorMessage = $errorMessage;
	}


	function setErrorCode($errorCode) {
		$this->errorCode = $errorCode;
	}


	function getErrorCode() {
		return $this->errorCode;
	}


	function setErrorDetail($errorDetail) {
		$this->errorDetail = $errorDetail;
	}


	function getErrorDetail() {
		return $this->errorDetail;
	}


	function setSuggestedAction($suggestedAction) {
		$this->suggestedAction = $suggestedAction;
	}


	function getSuggestedAction() {
		return $this->suggestedAction;
	}


	function setSuggestedExpirationPeriod($suggestedExpirationPeriod) {
		$this->suggestedExpirationPeriod = $suggestedExpirationPeriod;
	}


	function getSuggestedExpirationPeriod() {
		return $this->suggestedExpirationPeriod;
	}


	function setConsumerMessage($consumerMessage) {
		$this->consumerMessage = $consumerMessage;
	}


	function getConsumerMessage() {
		return $this->consumerMessage;
	}


	/**
	 *
	 *
	 * @return true, if the request was processed successfully, otherwise false. If
	 * false, additional information can be received calling getErrorMessage()
	 */
	function isOk() {
		return $this->ok;
	}


	/**
	 *
	 *
	 * @param ok      sets the OK flag
	 */
	function setOk($ok) {
		$this->ok = $ok;
	}


}


function LoadConfiguration() {
	$myideal_conf = array(  );
	require dirname( __FILE__ ) . '/../../../configuration.php';
	$whmcsmysql = @mysql_connect( $db_host, $db_username, $db_password );

	if (!( @mysql_select_db( $db_name ))) {
		exit( 'Could not connect to the database' );
		(bool)true;
	}

	$testmode = false;
	$acquirerurl = 'ssl://ideal.secure-ing.com:443/ideal/iDeal';
	$acquirertesturl = 'ssl://idealtest.secure-ing.com:443/ideal/iDeal';
	$authenticationtype = 'SHA1_RSA';
	$res = full_query( 'SELECT setting, value FROM tblpaymentgateways WHERE gateway=\'myideal\'' );

	while ($row = mysql_fetch_array( $res )) {
		$setting = $row['setting'];
		switch ($setting) {
		case 'merchantid': {
				$myideal_conf['MERCHANTID'] = $row['value'];
				break;
			}

		case 'subid': {
				$myideal_conf['SUBID'] = $row['value'];
				break;
			}

		case 'privatekey': {
				$myideal_conf['PRIVATEKEY'] = $row['value'];
				break;
			}

		case 'privatekeypass': {
				$myideal_conf['PRIVATEKEYPASS'] = $row['value'];
				break;
			}

		case 'privatecert': {
				$myideal_conf['PRIVATECERT'] = $row['value'];
				break;
			}

		case 'certificate0': {
				$myideal_conf['CERTIFICATE0'] = $row['value'];
				break;
			}

		case 'acquirertimeout': {
				$myideal_conf['ACQUIRERTIMEOUT'] = $row['value'];
				break;
			}

		case 'currency': {
				$myideal_conf['CURRENCY'] = $row['value'];
				break;
			}

		case 'expirationperiod': {
				$myideal_conf['EXPIRATIONPERIOD'] = $row['value'];
				break;
			}

		case 'language': {
				$myideal_conf['LANGUAGE'] = $row['value'];
				break;
			}

		case 'description': {
				$myideal_conf['DESCRIPTION'] = $row['value'];
				break;
			}

		case 'entrancecode': {
				$myideal_conf['ENTRANCECODE'] = $row['value'];
				break;
			}

		case 'logfile': {
				$myideal_conf['LOGFILE'] = $row['value'];
				break;
			}

		case 'testmode': {
				if ($row['value'] == 'on') {
					$testmode = true;
				}
			}
		}
	}


	if ($testmode == true) {
		$myideal_conf['ACQUIRERURL'] = $acquirertesturl;
	}
	else {
		$myideal_conf['ACQUIRERURL'] = $acquirerurl;
	}

	$myideal_conf['AUTHENTICATIONTYPE'] = $authenticationtype;
	$res = full_query( 'SELECT value FROM tblconfiguration WHERE setting=\'SystemURL\'' );
	$row = mysql_fetch_array( $res );
	$systemurl = $row[0];
	$myideal_conf['MERCHANTRETURNURL'] = $systemurl . '/modules/gateways/myideal/StatReq.php';
	return $myideal_conf;
}


if (!defined( 'WHMCS' )) {
	exit( 'This file cannot be accessed directly' );
}

IdealRequest;
IdealResponse;
IdealRequest;
IdealResponse;
IdealRequest;
IdealResponse;
?>
