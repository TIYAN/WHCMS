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

class WHMCS_Nominet {
	protected $params = null;
	protected $socket = null;
	protected $response = "";
	protected $responsearray = "";
	protected $errmsg = "";
	protected $resultcode = 0;

	function __construct() {
	}


	function init($params) {
		$obj = new self();
		$obj->params = $params;
		return $obj;
	}


	function getLastError() {
		return $this->errmsg ? $this->errmsg : "An unknown error occurred";
	}


	function setError($errmsg) {
		$this->errmsg = $errmsg;
	}


	function getParam($key) {
		return isset( $this->params[$key] ) ? $this->params[$key] : "";
	}


	function getDomain() {
		return $this->getParam( "sld" ) . "." . $this->getParam( "tld" );
	}


	function connect() {
		if ($this->getParam( "TestMode" )) {
			$host = "testbed-epp.nominet.org.uk";
		}
		else {
			$host = "epp.nominet.org.uk";
		}

		$port = 705;
		$timeout = 15;
		$ssl = true;
		$target = sprintf( "%s://%s", ($ssl === true ? "ssl" : "tcp"), $host );

		if (!$this->socket = @fsockopen( $target, $port, $errno, $errstr, $timeout )) {
			$this->setError( "Connecting to " . $target . " on port " . $port . ". <p>The error message was '" . $errstr . "' (code " . $errno . ")" );
		}
		else {
			if (@feof( $this->socket )) {
				$this->setError( "Connection closed by remote server" );
			}
			else {
				$hdr = @fread( $this->socket, 4 );

				if (empty( $hdr ) && feof( $this->socket )) {
					$this->setError( "Connection closed by remote server" );
				}
				else {
					if (empty( $hdr )) {
						$this->setError( "Reading from server: " . $php_errormsg );
					}
					else {
						$unpacked = unpack( "N", $hdr );
						$length = $unpacked[1];

						if ($length < 5) {
							$this->setError( "Got a bad frame header length from server" );
						}
						else {
							$answer = fread( $this->socket, $length - 4 );
							$this->processResponse( $answer );
							$this->logCall( "connect", $target . ":" . $port );
							return true;
						}
					}
				}
			}
		}

		return false;
	}


	function processResponse($response) {
		$this->response = $response;
		$this->responsearray = XMLtoArray( $response );

		if (preg_match( '%<domain:ns>(.+)</domain:ns>%s', $response, $matches )) {
			$ns = trim( $matches[1] );
			$ns = preg_replace( '%</?domain:hostObj>%', ' ', $ns );
			
			$ns = preg_split( "/\s+|
/", $ns, NULL, PREG_SPLIT_NO_EMPTY );
			foreach ($ns as $k => $value) {
				$ns[$k] = chop( $value, "." );
			}


			if (0 < count( $ns )) {
				$this->responsearray['EPP']['RESPONSE']['RESDATA']["DOMAIN:INFDATA"]["DOMAIN:NS"]["DOMAIN:HOSTOBJ"] = $ns;
			}
		}

		return true;
	}


	function getResponse() {
		return $this->response;
	}


	function getResponseArray() {
		return $this->responsearray;
	}


	function getResultCode() {
		$response_code_pattern = "<result code=\"(\d+)\">";
		$matches = array();
		preg_match( $response_code_pattern, $this->response, $matches );
		$resultcode = (isset( $matches[1] ) ? (int)$matches[1] : 0);
		return $resultcode;
	}


	function isErrorCode() {
		$resultcode = $this->getResultCode();
		return $resultcode < 2000 ? false : true;
	}


	function getErrorDesc() {
		$results = $this->getResponseArray();
		$results = $results['EPP']['RESPONSE'];

		if (isset( $results['RESULT']['EXTVALUE']['REASON'] )) {
			return $results['RESULT']['EXTVALUE']['REASON'];
		}


		if (isset( $results['RESULT']['MSG'] )) {
			return $results['RESULT']['MSG'];
		}

	}


	function call($xml) {
		$command = XMLtoArray( $xml );
		$command = array_keys( $command['COMMAND'] );
		$command = $command[0];
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"urn:ietf:params:xml:ns:epp-1.0 epp-1.0.xsd\">" . $xml;
		fwrite( $this->socket, pack( "N", strlen( $xml ) + 4 ) . $xml );

		if (@feof( $this->socket )) {
			$this->setError( "Connection closed by remote server" );
		}
		else {
			$hdr = @fread( $this->socket, 4 );

			if (empty( $hdr ) && feof( $this->socket )) {
				$this->setError( "Connection closed by remote server" );
			}
			else {
				if (empty( $hdr )) {
					$this->setError( "Error: Reading from server: " . $php_errormsg );
				}
				else {
					$unpacked = unpack( "N", $hdr );
					$length = $unpacked[1];

					if ($length < 5) {
						$this->setError( "Got a bad frame header length from server" );
					}
					else {
						$answer = fread( $this->socket, $length - 4 );
						$this->processResponse( $answer );
						$this->logCall( $command, $xml );
						return true;
					}
				}
			}
		}

		return false;
	}


	function logCall($action, $request) {
		if (function_exists( "logModuleCall" )) {
			logModuleCall( "nominet", $action, $request, $this->getResponse(), $this->getResponseArray(), array( $this->getParam( "Username" ), $this->getParam( "Password" ) ) );
		}

		return true;
	}


	function login() {
		$xml = "  <command>
                <login>
                  <clID>" . $this->getParam( "Username" ) . "</clID>
                  <pw>" . $this->getParam( "Password" ) . "</pw>
                  <options>
                    <version>1.0</version>
                    <lang>en</lang>
                  </options>
                  <svcs>
		    <objURI>urn:ietf:params:xml:ns:domain-1.0</objURI>
		    <objURI>urn:ietf:params:xml:ns:contact-1.0</objURI>
		    <objURI>urn:ietf:params:xml:ns:host-1.0</objURI>
		    ";
		$xml .= "<svcExtension>
		      <extURI>http://www.nominet.org.uk/epp/xml/contact-nom-ext-1.0</extURI>
		      <extURI>http://www.nominet.org.uk/epp/xml/domain-nom-ext-1.0</extURI>
		      <extURI>http://www.nominet.org.uk/epp/xml/std-release-1.0</extURI>
		    </svcExtension>
                  </svcs>
                </login>
                <clTRID>ABC-12345</clTRID>
              </command>
            </epp>";
		$res = $this->call( $xml );

		if ($res) {
			if ($this->isErrorCode()) {
				$this->setError( "Login Failed. Please check details in Setup > Domain Registrars > Nominet" );
			}
			else {
				return true;
			}
		}

		return false;
	}


	function connectAndLogin() {
		if ($this->connect()) {
			if ($this->login()) {
				return true;
			}
		}

		return false;
	}


}


?>