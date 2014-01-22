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

class Varilogix_Request {
	var $_api = null;
	var $_params = null;


	/**
	 * constructor
	 *
	 * @param string  $api
	 * @return Varilogix_Request
	 */
	function Varilogix_Request($api) {
		$this->_api = $api;
	}


	/**
	 * Set params to be sent with the request.
	 *
	 * @param mixed   $params either a string or an array
	 */
	function setParams($params) {
		if (is_array( $params )) {
			foreach ($params as $name => $value) {
				$this->_params[$name] = urlencode( $value );
			}

			return null;
		}

		$this->_params = $params;
	}


	/**
	 * Actually make the connection to our server and return
	 * the result
	 *
	 * @return mixed
	 */
	function execute() {
		$query_string = http_build_query( $this->_params );
		$link = curl_init();
		curl_setopt( $link, CURLOPT_URL, $this->_api );
		curl_setopt( $link, CURLOPT_VERBOSE, 0 );
		curl_setopt( $link, CURLOPT_POST, 1 );
		curl_setopt( $link, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $link, CURLOPT_POSTFIELDS, $query_string );
		curl_setopt( $link, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $link, CURLOPT_TIMEOUT, 360 );
		$res = curl_exec( $link );
		curl_close( $link );
		return $res;
	}


}


?>