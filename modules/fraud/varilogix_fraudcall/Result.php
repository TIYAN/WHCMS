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

class Varilogix_Call_Result {
	var $_api = "https://v3.varilogix.com/api/%s/pickup/";
	var $_apiName = null;
	var $_code = null;
	var $_message = null;
	var $_rawResponse = null;


	/**
	 * Constructor
	 *
	 * @return Varilogix_Call_Result
	 * @author Eric Coleman <eric@varilogix.com>
	 * */
	function Varilogix_Call_Result($apiName) {
		$this->_api = sprintf( $this->_api, $apiName );
		$this->_apiName = $apiName;
	}


	/**
	 * Fetch the result of the call
	 *
	 * @see getCode
	 * @see getMessage
	 * @param string  $call_id
	 * @return string either pass/fail/calling
	 */
	function fetch($call_id) {
		Varilogix_Request;
		$call = new ( $this->_api );
		$call->setParams( array( "call_id" => $call_id ) );
		$this->_rawResponse = $call->execute();
		$result = explode( ",", $this->_rawResponse );
		$this->_code = trim( $result[1] );
		$this->_message = trim( $result[2] );
		return trim( $result[0] );
	}


	/**
	 * Returns the error code
	 *
	 * @return int
	 * @author Eric Coleman <eric@varilogix.com>
	 * */
	function getCode() {
		return $this->_code;
	}


	/**
	 * Returns the error message
	 *
	 * @return string
	 * @author Eric Coleman <eric@varilogix.com>
	 * */
	function getMessage() {
		return $this->_message;
	}


	function getRawResponse() {
		return $this->_rawResponse;
	}


	/**
	 * WARNING: DO NOT USE THIS METHOD.  IT WILL MESS UP YOUR RESPONSES
	 *
	 * @private
	 * @hidden
	 */
	function isCompat() {
		$this->_api .= "?compat=true";
	}


}


?>