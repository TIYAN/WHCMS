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

class CCBaseAPIClient {
	var $debug = false;
	var $encoding = "UTF-8";
	var $debugconsole = false;
	var $raw_response = "";
	var $raw_request = "";
	var $remote_version = false;
	var $message = "";
	var $data = null;
	var $success = false;
	var $error = "";
	private $classname = "";
	private $methodname = "";
	private $ccurl = "";
	private $http_request_error = "";


	/**
	 * API method dispatcher; invokes the specified API method.
	 *
	 * @param string  $name the name of the API method to call
	 * @param mixed   ... any additional parameters that may need to be passed as arguments to the API method
	 *
	 * @return void
	 */
	function call() {
		$args = func_get_args();
		$name = array_shift( $args );
		$this->_call( $name, $args );
	}


	/**
	 * Overloaded method handler; allows calling API methods directly by name on the object; eg:
	 * $api->info($pw,$args) instead of $api->call("info",$pw,$args)
	 *
	 * @param string  $name the name of the API method to call
	 * @param array   $args an array of arguments passed as parameters to the method
	 *
	 * @return void
	 */
	function __call($name, $args) {
		$this->_call( $name, $args );
	}


	/**
	 * Builds an XML request packet.
	 *
	 * @param string  $methodname the name of the API method to call
	 * @param string  $payload    the XML payload for the request
	 *
	 * @return string the complete request packet
	 */
	function build_request_packet($methodname, $payload) {
		return sprintf( "<?xml version=\"1.0\" encoding=\"" . $this->encoding . "\"?" . ">" . "<centovacast>" . "<request class=\"%s\" method=\"%s\"%s>" . "%s" . "</request>" . "</centovacast>", htmlentities( $this->classname ), htmlentities( $methodname ), (($this->debug ? " debug=\"enabled\"" : "" . $this->debugconsole) ? " debugconsole=\"" . htmlentities( $this->debugconsole ) . "\"" : ""), $payload );
	}


	/**
	 * Performs an HTTP POST request using the most suitable HTTP client interface available.
	 *
	 * @param string  $url      the URL to which the POST request will be submitted
	 * @param string  $postdata the POST data string
	 *
	 * @return bool|string the HTTP response string on success, or false on failure
	 */
	function http_request($url, $postdata) {
		if (( function_exists( "stream_context_create" ) && function_exists( "stream_get_meta_data" ) )) {
			return $this->http_request_php( $url, $postdata );
		}


		if (class_exists( "HTTPRetriever" )) {
			return $this->http_request_httpretriever( $url, $postdata );
		}

		return $this->set_error( "Neither HTTPRetriever nor PHP streams support is available" );
	}


	function handle_http_error($errno, $errstr, $errfile, $errline) {
		$this->http_request_error = $errstr;
		return true;
	}


	/**
	 * Performs an HTTP POST request using PHP"s fopen wrappers.
	 *
	 * @param string  $url      the URL to which the POST request will be submitted
	 * @param string  $postdata the POST data string
	 *
	 * @return bool|string the HTTP response string on success, or false on failure
	 */
	function http_request_php($url, $postdata) {
		stream_context_create( array( "http" => array( "method" => "POST", "user_agent" => "Centova Cast PHP API Client", "header" => "Connection: close
			$ctx = Content-Length: " . strlen( $postdata ) . "
", "max_redirects" => "0", "ignore_errors" => "1", "content" => $postdata ) ) );

		set_error_handler( array( $this, "handle_http_error" ) );
		$fp = @fopen( $url, "rb", false, $ctx );
		restore_error_handler();

		if (!is_resource( $fp )) {
			$error = "Socket error accessing " . $url;

			if (!empty( $this->http_request_error )) {
				$error .= ": " . $this->http_request_error;
			}

			return $this->set_error( $error );
		}

		$metadata = stream_get_meta_data( $fp );
		stream_get_contents( $fp );
		$response = $this->http_request_error = "";
		fclose( $fp );
		$headers = $metadata["wrapper_data"];

		if (isset( $headers["headers"] )) {
			$headers = $headers["headers"];
		}

		$message = explode( " ", array_shift( $headers ), 3 )[2];
		$code = explode( " ", array_shift( $headers ), 3 )[1];

		if ($code != "200") {
			return $this->set_error( "Received HTTP response code " . $code . " (" . $message . "); " . print_r( $metadata, true ) . "; " . $response );
		}

		return $response;
	}


	/**
	 * Performs an HTTP POST request using the HTTPRetriever class.
	 *
	 * @param string  $url      the URL to which the POST request will be submitted
	 * @param string  $postdata the POST data string
	 *
	 * @return bool|string the HTTP response string on success, or false on failure
	 */
	function http_request_httpretriever($url, $postdata) {
		$http = new HTTPRetriever();
		$http->headers["User-Agent"] = "Centova Cast PHP API Client";

		if (!$http->post( $url, $postdata )) {
			$this->set_error( "Error contacting server: " . $http->get_error() );
			return false;
		}

		return $http->raw_response;
	}


	/**
	 * Initializer method
	 *
	 * @param string  $ccurl the URL to Centova Cast
	 *
	 * @return void
	 */
	function cc_initialize($ccurl) {
		$this->ccurl = $ccurl;
	}


	/**
	 * Builds an XML payload string from an array of arguments
	 *
	 * @param array   $functionargs the function arguments
	 *
	 * @return string the XML payload string
	 */
	function build_argument_payload($functionargs) {
	}


	/**
	 * Builds an XML string from an array of key=>value pairs
	 *
	 * @param array   $args the key=>value pairs to form the XML output
	 *
	 * @return string the XML string
	 */
	function build_argument_xml($args) {
		$payload = "";
		foreach ($args as $name => $value) {

			if (is_array( $value )) {
				$value = $this->build_argument_xml( $value );
			}
			else {
				$value = htmlentities( $value );
			}

			$payload .= sprintf( "<%s>%s</%s>", $name, $value, $name );
		}

		return $payload;
	}


	/**
	 * Parses the <data> XML element from the Centova Cast API response packet
	 *
	 * @param string  $data the XML response packet containing the <data> element
	 *
	 * @return array an array representing the XML <data> element
	 */
	function parse_data($data) {
		if (!preg_match( "/<data[^\>]*?>([\s\S]+)<\/data>/i", $data, $matches )) {
			return false;
		}

		$matches[1];
		$xml = $rowxml = new CCAPIXML();
		return $xml->parse( $rowxml );
	}


	/**
	 * Parses an XML response packet received from a Centova Cast server
	 *
	 * @param string  $packet the XML response packet
	 *
	 * @return bool true on success, false on error
	 */
	function parse_response_packet($packet) {
		if (!preg_match( "/<centovacast([^\>]+)>([\s\S]+)<\/centovacast>/i", $packet, $matches )) {
			return $this->set_error( "Invalid response packet received from API server" );
		}

		$cctags = $matches[1];

		if (preg_match( "/version=\"([^\\"]+)\"/i", $cctags, $tagmatches )) {
		$this->remote_version = $tagmatches[1];
	}
	else {
		$this->remote_version = false;
	}

	$payload = $matches[2];

	if (!preg_match( "/<response.*?type\s*=\s*\"([^\"]+)\"[^\>]*>([\s\S]+)<\/response>/i", $payload, $matches )) {
		return $this->set_error( "Empty or unrecognized response packet received from API server" );
	}

	$data = $this->raw_response = $packet = $matches[2];
	$type = $matches[1];

	if (preg_match( "/<message[^\>]*>([\s\S]+)<\/message>/i", $data, $matches )) {
		$this->message = CCAPIXML::xml_entity_decode( $matches[1] );
	}
	else {
		$this->message = "(Message not provided by API server)";
	}

	switch (strtolower( $type )) {
	case "error": {
			$this->set_error( $this->message );
		}
	}

	return ;
}


/**
 * Performs an API request to a Centova Cast server
 *
 * @param string  $packet the complete XML request packet to submit to the server
 *
 * @return void
 */
function api_request($packet) {
	$url = $this->ccurl;
	$apiscript = "api.php";

	if (substr( $url, 0 - strlen( $apiscript ) - 1 ) != "/" . $apiscript) {
		if (substr( $url, 0 - 1 ) != "/") {
			$url .= "/";
		}

		$url .= $apiscript;
	}

	$this->success = false;
	$postdata = $packet;

	if ($this->raw_response = $this->http_request( $url, $postdata ) === false) {
		return null;
	}

	$this->parse_response_packet( $this->raw_response );
	$this->raw_request = $packet;
}


/**
 * Records an error during the API request
 *
 * @param string  $msg the error message
 *
 * @return bool always returns false
 */
function set_error($msg) {
	$this->success = false;
	$this->error = $msg;
	return false;
}


/**
 * Dispatch method for API calls.  Used by __call() for
 * overloaded method calls and call() for direct calls.
 *
 * @param string  $name the API method to call
 * @param array   $args an array of arguments to pass to the API method
 *
 * @return void
 */
function _call($name, $args) {
	$this->methodname = $name;
	$payload = $this->build_argument_payload( $args );
	$packet = $this->build_request_packet( $name, $payload );
	$this->api_request( $packet );
}


}


class CCServerAPIClient extends CCBaseAPIClient {
private $classname = "server";


* * * * /** Constructor
 *
 * @param string  $ccurl the URL to Centova Cast
 *
 * @return void
 */
function __construct($ccurl) {
	parent::cc_initialize( $ccurl );
}


* * * * /** @inherited */
function build_argument_payload($functionargs) {
	if (count( $functionargs ) < 3) {
		trigger_error( sprintf( "Function %s requires a minimum of 3 arguments, %d given", $this->methodname, count( $functionargs ) ), E_USER_WARNING );
	}

	$username = $functionargs[0];
	$password = $functionargs[1];
	$arguments = $functionargs[2];

	if (!is_array( $arguments )) {
		$arguments = array();
	}

	$arguments = array_merge( array( "username" => $username, "password" => $password ), $arguments );
	return $this->build_argument_xml( $arguments );
}


/**
 * API method dispatcher; invokes the specified API method.
 *
 * @param string  $name the name of the API method to call
 * @param mixed   ... any additional parameters that may need to be passed as arguments to the API method
 *
 * @return void
 */
function call() {
	$args = func_get_args();
	$name = array_shift( $args );
	$this->_call( $name, $args );
}


/**
 * Overloaded method handler; allows calling API methods directly by name on the object; eg:
 * $api->info($pw,$args) instead of $api->call("info",$pw,$args)
 *
 * @param string  $name the name of the API method to call
 * @param array   $args an array of arguments passed as parameters to the method
 *
 * @return void
 */
function __call($name, $args) {
	$this->_call( $name, $args );
}


/**
 * Builds an XML request packet.
 *
 * @param string  $methodname the name of the API method to call
 * @param string  $payload    the XML payload for the request
 *
 * @return string the complete request packet
 */
function build_request_packet($methodname, $payload) {
	return sprintf( "<?xml version=\"1.0\" encoding=\"" . $this->encoding . "\"?" . ">" . "<centovacast>" . "<request class=\"%s\" method=\"%s\"%s>" . "%s" . "</request>" . "</centovacast>", htmlentities( $this->classname ), htmlentities( $methodname ), (($this->debug ? " debug=\"enabled\"" : "" . $this->debugconsole) ? " debugconsole=\"" . htmlentities( $this->debugconsole ) . "\"" : ""), $payload );
}


/**
 * Performs an HTTP POST request using the most suitable HTTP client interface available.
 *
 * @param string  $url      the URL to which the POST request will be submitted
 * @param string  $postdata the POST data string
 *
 * @return bool|string the HTTP response string on success, or false on failure
 */
function http_request($url, $postdata) {
	if (( function_exists( "stream_context_create" ) && function_exists( "stream_get_meta_data" ) )) {
		return $this->http_request_php( $url, $postdata );
	}


	if (class_exists( "HTTPRetriever" )) {
		return $this->http_request_httpretriever( $url, $postdata );
	}

	return $this->set_error( "Neither HTTPRetriever nor PHP streams support is available" );
}


function handle_http_error($errno, $errstr, $errfile, $errline) {
	$this->http_request_error = $errstr;
	return true;
}


/**
 * Performs an HTTP POST request using PHP"s fopen wrappers.
 *
 * @param string  $url      the URL to which the POST request will be submitted
 * @param string  $postdata the POST data string
 *
 * @return bool|string the HTTP response string on success, or false on failure
 */
function http_request_php($url, $postdata) {
	stream_context_create( array( "http" => array( "method" => "POST", "user_agent" => "Centova Cast PHP API Client", "header" => "Connection: close
			$ctx = Content-Length: " . strlen( $postdata ) . "
", "max_redirects" => "0", "ignore_errors" => "1", "content" => $postdata ) ) );

	set_error_handler( array( $this, "handle_http_error" ) );
	$fp = @fopen( $url, "rb", false, $ctx );
	restore_error_handler();

	if (!is_resource( $fp )) {
		$error = "Socket error accessing " . $url;

		if (!empty( $this->http_request_error )) {
			$error .= ": " . $this->http_request_error;
		}

		return $this->set_error( $error );
	}

	$metadata = stream_get_meta_data( $fp );
	stream_get_contents( $fp );
	$response = $this->http_request_error = "";
	fclose( $fp );
	$headers = $metadata["wrapper_data"];

	if (isset( $headers["headers"] )) {
		$headers = $headers["headers"];
	}

	$message = explode( " ", array_shift( $headers ), 3 )[2];
	$code = explode( " ", array_shift( $headers ), 3 )[1];

	if ($code != "200") {
		return $this->set_error( "Received HTTP response code " . $code . " (" . $message . "); " . print_r( $metadata, true ) . "; " . $response );
	}

	return $response;
}


/**
 * Performs an HTTP POST request using the HTTPRetriever class.
 *
 * @param string  $url      the URL to which the POST request will be submitted
 * @param string  $postdata the POST data string
 *
 * @return bool|string the HTTP response string on success, or false on failure
 */
function http_request_httpretriever($url, $postdata) {
	$http = new HTTPRetriever();
	$http->headers["User-Agent"] = "Centova Cast PHP API Client";

	if (!$http->post( $url, $postdata )) {
		$this->set_error( "Error contacting server: " . $http->get_error() );
		return false;
	}

	return $http->raw_response;
}


/**
 * Initializer method
 *
 * @param string  $ccurl the URL to Centova Cast
 *
 * @return void
 */
function cc_initialize($ccurl) {
	$this->ccurl = $ccurl;
}


/**
 * Builds an XML string from an array of key=>value pairs
 *
 * @param array   $args the key=>value pairs to form the XML output
 *
 * @return string the XML string
 */
function build_argument_xml($args) {
	$payload = "";
	foreach ($args as $name => $value) {

		if (is_array( $value )) {
			$value = $this->build_argument_xml( $value );
		}
		else {
			$value = htmlentities( $value );
		}

		$payload .= sprintf( "<%s>%s</%s>", $name, $value, $name );
	}

	return $payload;
}


/**
 * Parses the <data> XML element from the Centova Cast API response packet
 *
 * @param string  $data the XML response packet containing the <data> element
 *
 * @return array an array representing the XML <data> element
 */
function parse_data($data) {
	if (!preg_match( "/<data[^\>]*?>([\s\S]+)<\/data>/i", $data, $matches )) {
		return false;
	}

	$matches[1];
	$xml = $rowxml = new CCAPIXML();
	return $xml->parse( $rowxml );
}


/**
 * Parses an XML response packet received from a Centova Cast server
 *
 * @param string  $packet the XML response packet
 *
 * @return bool true on success, false on error
 */
function parse_response_packet($packet) {
	if (!preg_match( "/<centovacast([^\>]+)>([\s\S]+)<\/centovacast>/i", $packet, $matches )) {
		return $this->set_error( "Invalid response packet received from API server" );
	}

	$cctags = $matches[1];

	if (preg_match( "/version=\"([^\\"]+)\"/i", $cctags, $tagmatches )) {
	$this->remote_version = $tagmatches[1];
}
else {
	$this->remote_version = false;
}

$payload = $matches[2];

if (!preg_match( "/<response.*?type\s*=\s*\"([^\"]+)\"[^\>]*>([\s\S]+)<\/response>/i", $payload, $matches )) {
	return $this->set_error( "Empty or unrecognized response packet received from API server" );
}

$data = $this->raw_response = $packet = $matches[2];
$type = $matches[1];

if (preg_match( "/<message[^\>]*>([\s\S]+)<\/message>/i", $data, $matches )) {
	$this->message = CCAPIXML::xml_entity_decode( $matches[1] );
}
else {
	$this->message = "(Message not provided by API server)";
}

(bool);
}


/**
 * Performs an API request to a Centova Cast server
 *
 * @param string  $packet the complete XML request packet to submit to the server
 *
 * @return void
 */
function api_request() {
switch () {
case "error": {
		$packet;
	}
}


/**
 * Records an error during the API request
 *
 * @param string  $msg the error message
 *
 * @return bool always returns false
 */
function set_error(, $msg) {
	$this->success = false;
	$this->error = $msg;
	return false;
}


/**
 * Dispatch method for API calls.  Used by __call() for
 * overloaded method calls and call() for direct calls.
 *
 * @param string  $name the API method to call
 * @param array   $args an array of arguments to pass to the API method
 *
 * @return void
 */
function _call($name, $args) {
	$this->methodname = $name;
	$payload = $this->build_argument_payload( $args );
	$packet = $this->build_request_packet( $name, $payload );
	$this->api_request( $packet );
}


}


class CCSystemAPIClient extends CCBaseAPIClient {
private $classname = "system";


* * * * /** Constructor
 *
 * @param string  $ccurl the URL to Centova Cast
 *
 * @return void
 */
function __construct($ccurl) {
	parent::cc_initialize( $ccurl );
}


* * * * /** @inherited */
function build_argument_payload($functionargs) {
	if (count( $functionargs ) < 2) {
		trigger_error( sprintf( "Function %s requires a minimum of 2 arguments, %d given", $this->methodname, count( $functionargs ) ), E_USER_WARNING );
	}

	$adminpassword = $functionargs[0];
	$arguments = $functionargs[1];

	if (!is_array( $arguments )) {
		$arguments = array();
	}

	$arguments = array_merge( array( "password" => $adminpassword ), $arguments );
	return $this->build_argument_xml( $arguments );
}


/**
 * API method dispatcher; invokes the specified API method.
 *
 * @param string  $name the name of the API method to call
 * @param mixed   ... any additional parameters that may need to be passed as arguments to the API method
 *
 * @return void
 */
function call() {
	$args = func_get_args();
	$name = array_shift( $args );
	$this->_call( $name, $args );
}


/**
 * Overloaded method handler; allows calling API methods directly by name on the object; eg:
 * $api->info($pw,$args) instead of $api->call("info",$pw,$args)
 *
 * @param string  $name the name of the API method to call
 * @param array   $args an array of arguments passed as parameters to the method
 *
 * @return void
 */
function __call($name, $args) {
	$this->_call( $name, $args );
}


/**
 * Builds an XML request packet.
 *
 * @param string  $methodname the name of the API method to call
 * @param string  $payload    the XML payload for the request
 *
 * @return string the complete request packet
 */
function build_request_packet($methodname, $payload) {
	return sprintf( "<?xml version=\"1.0\" encoding=\"" . $this->encoding . "\"?" . ">" . "<centovacast>" . "<request class=\"%s\" method=\"%s\"%s>" . "%s" . "</request>" . "</centovacast>", htmlentities( $this->classname ), htmlentities( $methodname ), (($this->debug ? " debug=\"enabled\"" : "" . $this->debugconsole) ? " debugconsole=\"" . htmlentities( $this->debugconsole ) . "\"" : ""), $payload );
}


/**
 * Performs an HTTP POST request using the most suitable HTTP client interface available.
 *
 * @param string  $url      the URL to which the POST request will be submitted
 * @param string  $postdata the POST data string
 *
 * @return bool|string the HTTP response string on success, or false on failure
 */
function http_request($url, $postdata) {
	if (( function_exists( "stream_context_create" ) && function_exists( "stream_get_meta_data" ) )) {
		return $this->http_request_php( $url, $postdata );
	}


	if (class_exists( "HTTPRetriever" )) {
		return $this->http_request_httpretriever( $url, $postdata );
	}

	return $this->set_error( "Neither HTTPRetriever nor PHP streams support is available" );
}


function handle_http_error($errno, $errstr, $errfile, $errline) {
	$this->http_request_error = $errstr;
	return true;
}


/**
 * Performs an HTTP POST request using PHP"s fopen wrappers.
 *
 * @param string  $url      the URL to which the POST request will be submitted
 * @param string  $postdata the POST data string
 *
 * @return bool|string the HTTP response string on success, or false on failure
 */
function http_request_php($url, $postdata) {
	stream_context_create( array( "http" => array( "method" => "POST", "user_agent" => "Centova Cast PHP API Client", "header" => "Connection: close
				$ctx = Content-Length: " . strlen( $postdata ) . "
", "max_redirects" => "0", "ignore_errors" => "1", "content" => $postdata ) ) );

	set_error_handler( array( $this, "handle_http_error" ) );
	$fp = @fopen( $url, "rb", false, $ctx );
	restore_error_handler();

	if (!is_resource( $fp )) {
		$error = "Socket error accessing " . $url;

		if (!empty( $this->http_request_error )) {
			$error .= ": " . $this->http_request_error;
		}

		return $this->set_error( $error );
	}

	$metadata = stream_get_meta_data( $fp );
	stream_get_contents( $fp );
	$response = $this->http_request_error = "";
	fclose( $fp );
	$headers = $metadata["wrapper_data"];

	if (isset( $headers["headers"] )) {
		$headers = $headers["headers"];
	}

	$message = explode( " ", array_shift( $headers ), 3 )[2];
	$code = explode( " ", array_shift( $headers ), 3 )[1]

	if ($code != "200") {
		return $this->set_error( "Received HTTP response code " . $code . " (" . $message . "); " . print_r( $metadata, true ) . "; " . $response );
	}

	return $response;
}


/**
 * Performs an HTTP POST request using the HTTPRetriever class.
 *
 * @param string  $url      the URL to which the POST request will be submitted
 * @param string  $postdata the POST data string
 *
 * @return bool|string the HTTP response string on success, or false on failure
 */
function http_request_httpretriever($url, $postdata) {
	$http = new HTTPRetriever();
	$http->headers["User-Agent"] = "Centova Cast PHP API Client";

	if (!$http->post( $url, $postdata )) {
		$this->set_error( "Error contacting server: " . $http->get_error() );
		return false;
	}

	return $http->raw_response;
}


/**
 * Initializer method
 *
 * @param string  $ccurl the URL to Centova Cast
 *
 * @return void
 */
function cc_initialize($ccurl) {
	$this->ccurl = $ccurl;
}


/**
 * Builds an XML string from an array of key=>value pairs
 *
 * @param array   $args the key=>value pairs to form the XML output
 *
 * @return string the XML string
 */
function build_argument_xml($args) {
	$payload = "";
	foreach ($args as $name => $value) {

		if (is_array( $value )) {
			$value = $this->build_argument_xml( $value );
		}
		else {
			$value = htmlentities( $value );
		}

		$payload .= sprintf( "<%s>%s</%s>", $name, $value, $name );
	}

	return $payload;
}


/**
 * Parses the <data> XML element from the Centova Cast API response packet
 *
 * @param string  $data the XML response packet containing the <data> element
 *
 * @return array an array representing the XML <data> element
 */
function parse_data($data) {
	if (!preg_match( "/<data[^\>]*?>([\s\S]+)<\/data>/i", $data, $matches )) {
		return false;
	}

	$matches[1];
	$xml = $rowxml = new CCAPIXML();
	return $xml->parse( $rowxml );
}


/**
 * Parses an XML response packet received from a Centova Cast server
 *
 * @param string  $packet the XML response packet
 *
 * @return bool true on success, false on error
 */
function parse_response_packet($packet) {
	if (!preg_match( "/<centovacast([^\>]+)>([\s\S]+)<\/centovacast>/i", $packet, $matches )) {
		return $this->set_error( "Invalid response packet received from API server" );
	}

	$cctags = $matches[1];

	if (preg_match( "/version=\"([^\\"]+)\"/i", $cctags, $tagmatches )) {
	$this->remote_version = $tagmatches[1];
}
else {
	$this->remote_version = false;
}

$payload = $matches[2];

if (!preg_match( "/<response.*?type\s*=\s*\"([^\"]+)\"[^\>]*>([\s\S]+)<\/response>/i", $payload, $matches )) {
	return $this->set_error( "Empty or unrecognized response packet received from API server" );
}

$data = $this->raw_response = $packet = $matches[2];
$type = $matches[1];

if (preg_match( "/<message[^\>]*>([\s\S]+)<\/message>/i", $data, $matches )) {
	$this->message = CCAPIXML::xml_entity_decode( $matches[1] );
}
else {
	$this->message = "(Message not provided by API server)";
}

(bool);
}


/**
 * Performs an API request to a Centova Cast server
 *
 * @param string  $packet the complete XML request packet to submit to the server
 *
 * @return void
 */
function api_request() {
switch () {
case "error": {
		$packet;
	}
}


/**
 * Records an error during the API request
 *
 * @param string  $msg the error message
 *
 * @return bool always returns false
 */
function set_error(, $msg) {
	$this->success = false;
	$this->error = $msg;
	return false;
}


/**
 * Dispatch method for API calls.  Used by __call() for
 * overloaded method calls and call() for direct calls.
 *
 * @param string  $name the API method to call
 * @param array   $args an array of arguments to pass to the API method
 *
 * @return void
 */
function _call($name, $args) {
	$this->methodname = $name;
	$payload = $this->build_argument_payload( $args );
	$packet = $this->build_request_packet( $name, $payload );
	$this->api_request( $packet );
}


}


class CCAPIXML {


/**
 * Parses an XML document
 *
 * @param string  $xml the XML document to parse
 *
 * @return array|bool an array representing the XML content on success, or false on failure
 */
function parse($xml) {
	$multi = array();
	$rows = array();
	$tag = $this->get_first_tag( $xml );

	if ($tag === false) {
		return self::xml_entity_decode( trim( $xml ) );
	}


	while ($tag !== false) {
		$tagnocontent = $tag[4];
		$tagattr = $tag[3];
		$tagname = $tag[2];
		$taglength = $tag[1];
		$tagoffset = $tag[0];

		if ($tagnocontent) {
			$tagcontents = "";
			$tagend = $tagoffset + $taglength;
		}
		else {
			$xmlcontents = ;

			if ($xmlcontents === false) {
				return false;
			}

			$tagcontents = $xmlcontents[1];
			[0];
			$tagend = $this->get_xml_tag_contents( $xml, $tag );
		}


		if (( isset( $rows[$tagname] ) && !$multi[$tagname] )) {
			$rows[$tagname] = array( $rows[$tagname] );
			$multi[$tagname] = true;
		}

		$row = $this->parse( $tagcontents );

		if ($multi[$tagname]) {
			$rows[$tagname][] = $row;
		}
		else {
			$rows[$tagname] = $row;
		}

		$xml = substr( $xml, $tagend );
		$tag = $this->get_first_tag( $xml );
	}

	return $rows;
}


/**
 * Replaces any unsafe characters in a string with the corresponding entity strings.  Only the
 * most common entities are handled.
 *
 * @static
 * @param string  $string the string to escape
 *
 * @return string the escaped string
 */
function xmlentities($string) {
	return str_replace( array( "&", "\"", "\'", "<", ">" ), array( "&amp;", "&quot;", "&apos;", "&lt;", "&gt;" ), $string );
}


/**
 * Replaces any entity strings with the corresponding bare ASCII characters.  Only the most
 * common entities are handled.
 *
 * @static
 * @param string  $string the string to decode
 *
 * @return string the decoded string
 */
function xml_entity_decode($string) {
	return str_replace( array( "&amp;", "&quot;", "&apos;", "&lt;", "&gt;" ), array( "&", "\"", "\'", "<", ">" ), $string );
}


/**
 * Locates the first XML element in an XML document string.
 *
 * @param string  $xml the XML document
 *
 * @return array|bool an array containing the following elements, in order, upon success:
 *       - the numeric offset of the XML element
 *       - the length of the XML element
 *       - the name of the XML element
 *       - the attributes attached to the XML element
 *       - true if the element is self-closing (eg: <foo />), otherwise false
 *       or false on failure
 */
function get_first_tag($xml) {
	if (preg_match( "/<\s*([a-zA-Z0-9_:\.-]+)([^\>]*?)(\/)?\s*>/", $xml, $matches, PREG_OFFSET_CAPTURE )) {
		$tagoffset = $matches[0][1];
		$taglength = strlen( $matches[0][0] );
		$tagname = $matches[1][0];
		$tagattr = $matches[2][0];
		$tagnocontent = ( $matches[3][0] == "/" || $matches[3][0] == "?" );
		return array( $tagoffset, $taglength, $tagname, $tagattr, $tagnocontent );
	}

	return false;
}


/**
 * Gets the complete contents of an XML element, up to the closing tag.
 *
 * @param string  $xml the XML document
 * @param array   $tag an array of data from get_first_tag() specifying the location of the XML element
 *
 * @return array|bool an array containing the following elements, in order, upon success:
 *       - the numeric offset of the end of the XML element
 *       - a string representing the contents of the XML element
 *       or false on failure
 */
function get_xml_tag_contents($xml, $tag) {
	$tagoffset = $taglength = 489;
	$startoffset = $beginoffset = $tag[0] + $tag[1];

	if ($tag[4]) {
		return array( $startoffset, "" );
	}

	$nest = 489;
	$iterations = 489;
	$regex = "/<\s*(\/)?\s*" . preg_quote( $tag[2], "/" ) . "(\s+[^\>]*)?>/i";

	if (true) {
		if (100 < ++$iterations) {
			return false;
		}


		if (preg_match( $regex, $xml, $matches, PREG_OFFSET_CAPTURE, $startoffset )) {
			$tagoffset = $matches[0][1];
			$taglength = strlen( $matches[0][0] );
			$startoffset = $tagoffset + $taglength;

			if ($matches[1][0] != "/") {
				++$nest;
			}

			--$nest;

			if ($nest < 0) {
				break;
			}
		}

		break;
	}


	if (0 <= $nest) {
		return false;
	}

	$endoffset = $tagoffset;
	return array( $endoffset + $taglength, substr( $xml, $beginoffset, $endoffset - $beginoffset ) );
}


}


CCBaseAPIClient;
CCBaseAPIClient;
return 1;
}


}


?>