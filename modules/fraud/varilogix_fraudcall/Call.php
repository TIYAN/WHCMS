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

class Varilogix_Call {
	var $_api = "https://v3.varilogix.com/api/%s/call/";
	var $_apiName = null;
	var $email = null;
	var $password = null;
	var $profile = null;
	var $service = null;
	var $domain = null;
	var $amount = null;
	var $name = null;
	var $telephone = null;
	var $country = null;
	var $pin = null;
	var $city = null;
	var $state = null;
	var $postalcode = null;
	var $ip = null;
	var $email_address = null;
	var $bin = null;
	var $test = "false";
	var $_code = null;
	var $_message = null;
	var $_rawResponse = null;


	/**
	 * Constructor
	 *
	 * @param string  $apiName  your api name
	 * @param string  $email    users email address
	 * @param string  $password md5 hash of the users password
	 * @param int     $profile  active profile to use
	 * @return  Varilogix_Call
	 * @author  Eric Coleman <eric@varilogix.com>
	 * */
	function Varilogix_Call($apiName, $email, $password, $profile) {
		if (strlen( $password ) != 32) {
			trigger_error( E_USER_ERROR, "password must be an MD5 hash" );
		}

		$this->_apiName = $apiName;
		$this->_api = sprintf( $this->_api, $apiName );
		$this->email = $email;
		$this->password = $password;
		$this->profile = $profile;
	}


	/**
	 * Set the product information that is being ordered.
	 *
	 * @param string  $service The service / item being sold
	 * @param float   $amount  The amount of the order
	 */
	function setProductInfo($service, $amount) {
		$this->service = $service;
		$this->amount = $amount;
	}


	/**
	 * Set domain information avavailble for this order.  This will
	 * allow us to automatically run whois information on each domain that
	 * is being ordered or used in your billing software.
	 *
	 * Multiple domain example
	 * <code>
	 * $domains = array("varilogix.com", "cnn.com", "google.com");
	 * $call->setDomainInfo($domains);
	 * </code>
	 *
	 * Single domain example
	 * <code>
	 * $call->setDomainInfo("varilogix.com");
	 * </code>
	 *
	 * @param string|array $domains single domain, or array of domains
	 */
	function setDomainInfo($domains) {
		if (is_array( $domains )) {
			$this->domain = implode( ",", $domains );
			return null;
		}

		$this->domain = $domains;
	}


	/**
	 * Set the customer information
	 *
	 * @param string  $name      Customer name
	 * @param string  $email     Customer email address
	 * @param string  $telephone Customer telephone number
	 * @param string  $country   ISO 3166-1 country code
	 */
	function setCustomerInfo($name, $email, $telephone, $country) {
		$this->name = $name;
		$this->email_address = $email;
		$this->telephone = $telephone;
		$this->country = $country;
	}


	/**
	 * Set the pin number to be used for this call.  The pin MUST be
	 * 4 digits
	 *
	 * @see   generatePin
	 * @param int     $pin
	 */
	function setPin($pin) {
		$this->pin = $pin;
	}


	/**
	 * Set AFIS information for this call
	 *
	 * @param string  $city
	 * @param string  $state
	 * @param mixed   $postal
	 * @param int     $bin
	 */
	function setAfisInformation($city, $state, $postal, $bin = null) {
		$this->city = $city;
		$this->state = $state;
		$this->postalcode = $postal;

		if (!is_null( $bin )) {
			$this->bin = $bin;
		}

	}


	function isTest($test) {
		if ($test) {
			$this->test = "true";
			return null;
		}

		$this->test = "false";
	}


	/**
	 * Generate a 4 digit call pin
	 *
	 * @see setPin
	 * @static
	 * @return int
	 * @author Eric Coleman <eric@varilogix.com>
	 * */
	function generatePin() {
		$pin = null;
		$i = 0;

		while ($i < 4) {
			$pin .= mt_rand( 1, 9 );
			++$i;
		}

		return (int)$pin;
	}


	/**
	 * Returns the result of the call request.  You can see the codes in
	 * /docs/ERROR_CODES.txt
	 *
	 * @see getCode
	 * @see getMessage
	 * @return bool
	 * @author Eric Coleman <eric@varilogix.com>
	 * */
	function call() {
		if (empty( $this->ip )) {
			$this->ip = $this->_getUserIp();
		}

		$call = new Varilogix_Request( $this->_api );
		$call->setParams( get_object_vars( $this ) );
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


	/**
	 * Gets a users ip.
	 *
	 * Credit: php.net (ip-to-country)
	 *
	 * @return string
	 * @author Eric Coleman <eric@varilogix.com>
	 * */
	function _getUserIp() {
		$ip = false;

		if (!empty( $_SERVER["HTTP_CLIENT_IP"] )) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}


		if (!empty( $_SERVER["HTTP_X_FORWARDED_FOR"] )) {
			$ips = explode( ", ", $_SERVER["HTTP_X_FORWARDED_FOR"] );

			if ($ip) {
				array_unshift( $ips, $ip );
				$ip = FALSE;
			}

			$i = 0;

			while ($i < count( $ips )) {
				if (!eregi( "^(10|172\.16|192\.168)\.", $ips[$i] )) {
					if (version_compare( phpversion(), "5.0.0", ">=" )) {
						if (ip2long( $ips[$i] ) != false) {
							$ip = $ips[$i];
							break;
						}
					}


					if (ip2long( $ips[$i] ) != 0 - 1) {
						$ip = $ips[$i];
						break;
					}
				}

				++$i;
			}
		}

		return $ip ? $ip : $_SERVER["REMOTE_ADDR"];
	}


	function getRawResponse() {
		return $this->_rawResponse;
	}


}


?>