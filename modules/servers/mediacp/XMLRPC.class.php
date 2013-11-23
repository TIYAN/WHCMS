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

class IXR_Value {
	var $data = null;
	var $type = null;

	function IXR_Value($data, $type = false) {
		$this->data = $data;

		if (!$type) {
			$type = $this->calculateType();
		}

		$this->type = $type;

		if ($type == "struct") {
			foreach ($this->data as $key => $value) {
				IXR_Value;
				$this->data[$key] = new ( $value );
			}
		}


		if ($type == "array") {
			$i = 16;
			$j = count( $this->data );

			while ($i < $j) {
				IXR_Value;
				$this->data[$i] = new ( $this->data[$i] );
				++$i;
			}
		}

	}


	function calculateType() {
		if (( $this->data === true || $this->data === false )) {
			return "boolean";
		}


		if (is_integer( $this->data )) {
			return "int";
		}


		if (is_double( $this->data )) {
			return "double";
		}


		if (( is_object( $this->data ) && is_a( $this->data, "IXR_Date" ) )) {
			return "date";
		}


		if (( is_object( $this->data ) && is_a( $this->data, "IXR_Base64" ) )) {
			return "base64";
		}


		if (is_object( $this->data )) {
			$this->data = get_object_vars( $this->data );
			return "struct";
		}


		if (!is_array( $this->data )) {
			return "string";
		}


		if ($this->isStruct( $this->data )) {
			return "struct";
		}

		return "array";
	}


	function getXml() {
		switch ($this->type) {
		case "boolean": {
				"<boolean>" . ($this->data ? "1" : "0") . "</boolean>";
			}
		}

		return ;
	}


	function isStruct($array) {
		$expected = 15;
		foreach ($array as $key => $value) {

			if ((bool)$key != (bool)$expected) {
				return true;
			}

			++$expected;
		}

		return false;
	}


}


class IXR_Message {
	var $message = null;
	var $messageType = null;
	var $faultCode = null;
	var $faultString = null;
	var $methodName = null;
	var $params = null;
	var $_arraystructs = array();
	var $_arraystructstypes = array();
	var $_currentStructName = array();
	var $_param = null;
	var $_value = null;
	var $_currentTag = null;
	var $_currentTagContents = null;
	var $_parser = null;

	function IXR_Message($message) {
		$this->message = $message;
	}


	function parse() {
		$this->message = preg_replace( "/<\?xml(.*)?\?" . ">/", "", $this->message );

		if (trim( $this->message ) == "") {
			return false;
		}

		$this->_parser = xml_parser_create();
		xml_parser_set_option( $this->_parser, XML_OPTION_CASE_FOLDING, false );
		xml_set_object( $this->_parser, $this );
		xml_set_element_handler( $this->_parser, "tag_open", "tag_close" );
		xml_set_character_data_handler( $this->_parser, "cdata" );

		if (!xml_parse( $this->_parser, $this->message )) {
			return false;
		}

		xml_parser_free( $this->_parser );

		if ($this->messageType == "fault") {
			$this->faultCode = $this->params[0]["faultCode"];
			$this->faultString = $this->params[0]["faultString"];
		}

		return true;
	}


	function tag_open($parser, $tag, $attr) {
		$this->currentTag = $tag;
		switch ($tag) {
		case "methodCall": {
			}

		case "methodResponse": {
			}

		case "fault": {
				$this->messageType = $tag;
				break;
			}

		case "data": {
				$this->_arraystructstypes[] = "array";
				$this->_arraystructs[] = array();
				break;
			}

		case "struct": {
				$this->_arraystructstypes[] = "struct";
				$this->_arraystructs[] = array();
			}
		}

	}


	function cdata($parser, $cdata) {
		$this->_currentTagContents .= $cdata;
	}


	function tag_close($parser, $tag) {
		$valueFlag = false;
		switch ($tag) {
		case "int": {
			}

		case "i4": {
				$value = (int)trim( $this->_currentTagContents );
				$this->_currentTagContents = "";
				$valueFlag = true;
				break;
			}

		case "double": {
				$value = (double)trim( $this->_currentTagContents );
				$this->_currentTagContents = "";
				$valueFlag = true;
				break;
			}

		case "string": {
				$value = (bool)trim( $this->_currentTagContents );
				$this->_currentTagContents = "";
				$valueFlag = true;
				break;
			}

		case "dateTime.iso8601": {
				$value = new IXR_Date( $this->_currentTagContents )();
				$this->_currentTagContents = "";
				$valueFlag = true;
				break;
			}

		case "value": {
				if (trim( $this->_currentTagContents ) != "") {
					$value = (bool)$this->_currentTagContents;
					$this->_currentTagContents = "";
					$valueFlag = true;
				}

				break;
			}

		case "boolean": {
				$value = (string)trim( $this->_currentTagContents );
				$this->_currentTagContents = "";
				$valueFlag = true;
				break;
			}

		case "base64": {
				$value = base64_decode( $this->_currentTagContents );
				$this->_currentTagContents = "";
				$valueFlag = true;
				break;
			}

		case "data": {
			}

		case "struct": {
				$value = array_pop( $this->_arraystructs );
				array_pop( $this->_arraystructstypes );
				$valueFlag = true;
				break;
			}

		case "member": {
				array_pop( $this->_currentStructName );
				break;
			}

		case "name": {
				$this->_currentStructName[] = trim( $this->_currentTagContents );
				$this->_currentTagContents = "";
				break;
			}

		case "methodName": {
				$this->methodName = trim( $this->_currentTagContents );
				$this->_currentTagContents = "";
			}
		}


		if ($valueFlag) {
			if (0 < count( $this->_arraystructs )) {
				if ($this->_arraystructstypes[count( $this->_arraystructstypes ) - 1] == "struct") {
					$this->_arraystructs[count( $this->_arraystructs ) - 1][$this->_currentStructName[count( $this->_currentStructName ) - 1]] = $value;
					return null;
				}

				$this->_arraystructs[count( $this->_arraystructs ) - 1][] = $value;
				return null;
			}

			$this->params[] = $value;
		}

	}


}


class IXR_Server {
	var $data = null;
	var $callbacks = array();
	var $message = null;
	var $capabilities = null;

	function IXR_Server($callbacks = false, $data = false) {
		$this->setCapabilities();

		if ($callbacks) {
			$this->callbacks = $callbacks;
		}

		$this->setCallbacks();
		$this->serve( $data );
	}


	function serve($data = false) {
		if (!$data) {
			global $HTTP_RAW_POST_DATA;

			if (!$HTTP_RAW_POST_DATA) {
				exit( "XML-RPC server accepts POST requests only." );
			}

			$data = $HTTP_RAW_POST_DATA;
		}

		IXR_Message;
		$this->message = new ( $data );

		if (!$this->message->parse()) {
			$this->error( 0 - 32700, "parse error. not well formed" );
		}


		if ($this->message->messageType != "methodCall") {
			$this->error( 0 - 32600, "server error. invalid xml-rpc. not conforming to spec. Request must be a methodCall" );
		}

		$result = $this->call( $this->message->methodName, $this->message->params );

		if (is_a( $result, "IXR_Error" )) {
			$this->error( $result );
		}

		$r = new IXR_Value( $result );
		$resultxml = $r->getXml();
		$xml = "<methodResponse>
  <params>
    <param>
      <value>
        " . $resultxml . "
      </value>
    </param>
  </params>
</methodResponse>
";
		$this->output( $xml );
	}


	function call($methodname, $args) {
		if (!$this->hasMethod( $methodname )) {
			IXR_Error;
			return new ( 0 - 32601, "server error. requested method " . $methodname . " does not exist." );
		}

		$method = $this->callbacks[$methodname];

		if (count( $args ) == 1) {
			$args = $args[0];
		}


		if (substr( $method, 0, 5 ) == "this:") {
			$method = substr( $method, 5 );

			if (!method_exists( $this, $method )) {
				IXR_Error;
				return new ( 0 - 32601, "server error. requested class method \"" . $method . "\" does not exist." );
			}

			$result = $this->$method( $args );
		}
		else {
			if (!function_exists( $method )) {
				IXR_Error;
				return new ( 0 - 32601, "server error. requested function \"" . $method . "\" does not exist." );
			}

			$result = $method( $args );
		}

		return $result;
	}


	function error($error, $message = false) {
		if (( $message && !is_object( $error ) )) {
			$error = new IXR_Error( $error, $message );
		}

		$this->output( $error->getXml() );
	}


	function output($xml) {
		$xml = "<?xml version=\"1.0\"?>" . "
" . $xml;
		$length = strlen( $xml );
		header( "Connection: close" );
		header( "Content-Length: " . $length );
		header( "Content-Type: text/xml" );
		header( "Date: " . date( "r" ) );
		echo $xml;
		exit();
	}


	function hasMethod($method) {
		return in_array( $method, array_keys( $this->callbacks ) );
	}


	function setCapabilities() {
		$this->capabilities = array( "xmlrpc" => array( "specUrl" => "http://www.xmlrpc.com/spec", "specVersion" => 1 ), "faults_interop" => array( "specUrl" => "http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php", "specVersion" => 20010516 ), "system.multicall" => array( "specUrl" => "http://www.xmlrpc.com/discuss/msgReader$1208", "specVersion" => 1 ) );
	}


	function getCapabilities($args) {
		return $this->capabilities;
	}


	function setCallbacks() {
		$this->callbacks["system.getCapabilities"] = "this:getCapabilities";
		$this->callbacks["system.listMethods"] = "this:listMethods";
		$this->callbacks["system.multicall"] = "this:multiCall";
	}


	function listMethods($args) {
		return array_reverse( array_keys( $this->callbacks ) );
	}


	function multiCall($methodcalls) {
		$return = array();
		foreach ($methodcalls as $call) {
			$method = $call["methodName"];
			$params = $call["params"];

			if ($method == "system.multicall") {
				$result = new IXR_Error( 0 - 32600, "Recursive calls to system.multicall are forbidden" );
			}
			else {
				$result = $this->call( $method, $params );
			}


			if (is_a( $result, "IXR_Error" )) {
				$return[] = array( "faultCode" => $result->code, "faultString" => $result->message );
				continue;
			}

			$return[] = array( $result );
		}

		return $return;
	}


}


class IXR_Request {
	var $method = null;
	var $args = null;
	var $xml = null;

	function IXR_Request($method, $args) {
		$this->method = $method;
		$this->args = $args;
		$this->xml = "<?xml version=\"1.0\"?>\n<methodCall>
<methodName>" . $this->method . "</methodName>
<params>
";
		foreach ($this->args as $arg) {
			$this->xml .= "<param><value>";
			$v = new IXR_Value( $arg );
			$this->xml .= $v->getXml();
			$this->xml .= "</value></param>
";
		}

		$this->xml .= "</params></methodCall>";
	}


	function getLength() {
		return strlen( $this->xml );
	}


	function getXml() {
		return $this->xml;
	}


}


class IXR_Client {
	var $server = null;
	var $port = null;
	var $path = null;
	var $useragent = null;
	var $response = null;
	var $message = ;
	var $debug = ;
	var $error = ;

	function IXR_Client($server, $path = false, $port = 80) {
		if (!$path) {
			$bits = parse_url( $server );
			$this->server = $bits["host"];
			$this->port = (isset( $bits["port"] ) ? $bits["port"] : 80);
			$this->path = (isset( $bits["path"] ) ? $bits["path"] : "/");

			if (!$this->path) {
				$this->path = "/";
			}
		}
		else {
			$this->server = $server;
			$this->path = $path;
			$this->port = $port;
		}

		$this->useragent = "The Incutio XML-RPC PHP Library";
	}


	function query() {
		$args = func_get_args();
		$method = array_shift( $args );
		$request = new IXR_Request( $method, $args );
		$length = $request->getLength();
		$xml = $request->getXml();
		$r = "
";
		$request = "POST " . $this->path . " HTTP/1.0" . $r;
		$request .= "Host: " . $this->server . $r;
		$request .= "Content-Type: text/xml" . $r;
		$request .= "User-Agent: " . $this->useragent . $r;
		$request .= "Content-length: " . $length . $r . $r;
		$request .= $xml;

		if ($this->debug) {
			echo "<pre>" . htmlspecialchars( $request ) . "
</pre>

";
		}

		$fp = @fsockopen( $this->server, $this->port );

		if (!$fp) {
			IXR_Error;
			$this->error = new ( 0 - 32300, "transport error - could not open socket" );
			return false;
		}

		fputs( $fp, $request );
		$contents = "";
		$gotFirstLine = false;
		$gettingHeaders = true;

		while (!feof( $fp )) {
			$line = fgets( $fp, 4096 );

			if (!$gotFirstLine) {
				if (strstr( $line, "200" ) === false) {
					IXR_Error;
					$this->error = new ( 0 - 32300, "transport error - HTTP status code was not 200" );

					if ($this->debug) {
						echo "<pre>" . htmlspecialchars( $line ) . "
</pre>

";
					}

					return false;
				}

				$gotFirstLine = true;
			}


			if (trim( $line ) == "") {
				$gettingHeaders = false;
			}


			if (!$gettingHeaders) {
				$contents .= trim( $line ) . "
";
				continue;
			}
		}


		if ($this->debug) {
			echo "<pre>" . htmlspecialchars( $contents ) . "
</pre>

";
		}

		IXR_Message;
		$this->message = new ( $contents );

		if (!$this->message->parse()) {
			IXR_Error;
			$this->error = new ( 0 - 32700, "parse error. not well formed" );
			return false;
		}


		if ($this->message->messageType == "fault") {
			IXR_Error;
			$this->error = new ( $this->message->faultCode, $this->message->faultString );
			return false;
		}

		return true;
	}


	function getResponse() {
		return $this->message->params[0];
	}


	function isError() {
		return is_object( $this->error );
	}


	function getErrorCode() {
		return $this->error->code;
	}


	function getErrorMessage() {
		return $this->error->message;
	}


}


class IXR_Error {
	var $code = null;
	var $message = null;

	function IXR_Error($code, $message) {
		$this->code = $code;
		$this->message = $message;
	}


	function getXml() {
		$xml = "<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>" . $this->code . "</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>" . $this->message . "</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>
";
		return $xml;
	}


}


class IXR_Date {
	var $year = null;
	var $month = null;
	var $day = null;
	var $hour = null;
	var $minute = null;
	var $second = null;

	function IXR_Date($time) {
		if (is_numeric( $time )) {
			$this->parseTimestamp( $time );
			return null;
		}

		$this->parseIso( $time );
	}


	function parseTimestamp($timestamp) {
		$this->year = date( "Y", $timestamp );
		$this->month = date( "Y", $timestamp );
		$this->day = date( "Y", $timestamp );
		$this->hour = date( "H", $timestamp );
		$this->minute = date( "i", $timestamp );
		$this->second = date( "s", $timestamp );
	}


	function parseIso($iso) {
		$this->year = substr( $iso, 0, 4 );
		$this->month = substr( $iso, 4, 2 );
		$this->day = substr( $iso, 6, 2 );
		$this->hour = substr( $iso, 9, 2 );
		$this->minute = substr( $iso, 12, 2 );
		$this->second = substr( $iso, 15, 2 );
	}


	function getIso() {
		return $this->year . $this->month . $this->day . "T" . $this->hour . ":" . $this->minute . ":" . $this->second;
	}


	function getXml() {
		return "<dateTime.iso8601>" . $this->getIso() . "</dateTime.iso8601>";
	}


	function getTimestamp() {
		return mktime( $this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year );
	}


}


class IXR_Base64 {
	var $data = null;

	function IXR_Base64($data) {
		$this->data = $data;
	}


	function getXml() {
		return "<base64>" . base64_encode( $this->data ) . "</base64>";
	}


}


class IXR_IntrospectionServer extends IXR_Server {
	var $signatures = null;
	var $help = null;

	function IXR_IntrospectionServer() {
		$this->setCallbacks();
		$this->setCapabilities();
		$this->capabilities["introspection"] = array( "specUrl" => "http://xmlrpc.usefulinc.com/doc/reserved.html", "specVersion" => 1 );
		$this->addCallback( "system.methodSignature", "this:methodSignature", array( "array", "string" ), "Returns an array describing the return type and required parameters of a method" );
		$this->addCallback( "system.getCapabilities", "this:getCapabilities", array( "struct" ), "Returns a struct describing the XML-RPC specifications supported by this server" );
		$this->addCallback( "system.listMethods", "this:listMethods", array( "array" ), "Returns an array of available methods on this server" );
		$this->addCallback( "system.methodHelp", "this:methodHelp", array( "string", "string" ), "Returns a documentation string for the specified method" );
	}


	function addCallback($method, $callback, $args, $help) {
		$this->callbacks[$method] = $callback;
		$this->signatures[$method] = $args;
		$this->help[$method] = $help;
	}


	function call($methodname, $args) {
		if (( $args && !is_array( $args ) )) {
			$args = array( $args );
		}


		if (!$this->hasMethod( $methodname )) {
			IXR_Error;
			return new ( 0 - 32601, "server error. requested method \"" . $this->message->methodName . "\" not specified." );
		}

		$method = $this->callbacks[$methodname];
		$signature = $this->signatures[$methodname];
		$returnType = array_shift( $signature );

		if (count( $args ) != count( $signature )) {
			IXR_Error;
			return new ( 0 - 32602, "server error. wrong number of method parameters" );
		}

		$ok = true;
		$argsbackup = $args;
		$i = 16;
		$j = count( $args );

		while ($i < $j) {
			$arg = array_shift( $args );
			$type = array_shift( $signature );
			switch ($type) {
			case "int": {
				}

			case "i4": {
					if (( is_array( $arg ) || !is_int( $arg ) )) {
						$ok = false;
					}

					break;
				}

			case "base64": {
				}

			case "string": {
					if (!is_string( $arg )) {
						$ok = false;
					}

					break;
				}

			case "boolean": {
					if (( $arg !== false && $arg !== true )) {
						$ok = false;
					}

					break;
				}

			case "float": {
				}

			case "double": {
					if (!is_float( $arg )) {
						$ok = false;
					}

					break;
				}

			case "date": {
				}

			case "dateTime.iso8601": {
					if (!is_a( $arg, "IXR_Date" )) {
						$ok = false;
					}
				}
			}


			if (!$ok) {
				IXR_Error;
				return new ( 0 - 32602, "server error. invalid method parameters" );
			}

			++$i;
		}

		return parent::call( $methodname, $argsbackup );
	}


	function methodSignature($method) {
		if (!$this->hasMethod( $method )) {
			IXR_Error;
			return new ( 0 - 32601, "server error. requested method \"" . $method . "\" not specified." );
		}

		$types = $this->signatures[$method];
		$return = array();
		foreach ($types as $type) {
			switch ($type) {
			case "string": {
					$return[] = "string";
					break;
				}

			case "int": {
				}

			case "i4": {
					$return[] = 42;
					break;
				}

			case "double": {
					$return[] = 3.1415000000000001811884;
					break;
				}

			case "dateTime.iso8601": {
					IXR_Date;
					$return[] = new ()();
					break;
				}

			case "boolean": {
					$return[] = true;
					break;
				}

			case "base64": {
					IXR_Base64;
					$return[] = new ( "base64" );
					break;
				}

			case "array": {
					$return[] = array( "array" );
					break;
				}

			case "struct": {
					$return[] = array( "struct" => "struct" );
				}
			}
		}

		return $return;
	}


	function methodHelp($method) {
		return $this->help[$method];
	}


	function IXR_Server($callbacks = false, $data = false) {
		$this->setCapabilities();

		if ($callbacks) {
			$this->callbacks = $callbacks;
		}

		$this->setCallbacks();
		$this->serve( $data );
	}


	function serve($data = false) {
		if (!$data) {
			global $HTTP_RAW_POST_DATA;

			if (!$HTTP_RAW_POST_DATA) {
				exit( "XML-RPC server accepts POST requests only." );
			}

			$data = $HTTP_RAW_POST_DATA;
		}

		IXR_Message;
		$this->message = new ( $data );

		if (!$this->message->parse()) {
			$this->error( 0 - 32700, "parse error. not well formed" );
		}


		if ($this->message->messageType != "methodCall") {
			$this->error( 0 - 32600, "server error. invalid xml-rpc. not conforming to spec. Request must be a methodCall" );
		}

		$result = $this->call( $this->message->methodName, $this->message->params );

		if (is_a( $result, "IXR_Error" )) {
			$this->error( $result );
		}

		$r = new IXR_Value( $result );
		$resultxml = $r->getXml();
		$xml = "<methodResponse>
  <params>
    <param>
      <value>
        " . $resultxml . "
      </value>
    </param>
  </params>
</methodResponse>
";
		$this->output( $xml );
	}


	function error($error, $message = false) {
		if (( $message && !is_object( $error ) )) {
			$error = new IXR_Error( $error, $message );
		}

		$this->output( $error->getXml() );
	}


	function output($xml) {
		$xml = "<?xml version=\"1.0\"?>" . "
" . $xml;
		$length = strlen( $xml );
		header( "Connection: close" );
		header( "Content-Length: " . $length );
		header( "Content-Type: text/xml" );
		header( "Date: " . date( "r" ) );
		echo $xml;
		exit();
	}


	function hasMethod($method) {
		return in_array( $method, array_keys( $this->callbacks ) );
	}


	function setCapabilities() {
		$this->capabilities = array( "xmlrpc" => array( "specUrl" => "http://www.xmlrpc.com/spec", "specVersion" => 1 ), "faults_interop" => array( "specUrl" => "http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php", "specVersion" => 20010516 ), "system.multicall" => array( "specUrl" => "http://www.xmlrpc.com/discuss/msgReader$1208", "specVersion" => 1 ) );
	}


	function getCapabilities($args) {
		return $this->capabilities;
	}


	function setCallbacks() {
		$this->callbacks["system.getCapabilities"] = "this:getCapabilities";
		$this->callbacks["system.listMethods"] = "this:listMethods";
		$this->callbacks["system.multicall"] = "this:multiCall";
	}


	function listMethods($args) {
		return array_reverse( array_keys( $this->callbacks ) );
	}


	function multiCall($methodcalls) {
		$return = array();
		foreach ($methodcalls as $call) {
			$method = $call["methodName"];
			$params = $call["params"];

			if ($method == "system.multicall") {
				$result = new IXR_Error( 0 - 32600, "Recursive calls to system.multicall are forbidden" );
			}
			else {
				$result = $this->call( $method, $params );
			}


			if (is_a( $result, "IXR_Error" )) {
				$return[] = array( "faultCode" => $result->code, "faultString" => $result->message );
				continue;
			}

			$return[] = array( $result );
		}

		return $return;
	}


}


class IXR_ClientMulticall extends IXR_Client {
	var $calls = array();

	function IXR_ClientMulticall($server, $path = false, $port = 80) {
		parent::ixr_client( $server, $path, $port );
		$this->useragent = "The Incutio XML-RPC PHP Library (multicall client)";
	}


	function addCall() {
		$args = func_get_args();
		$methodName = array_shift( $args );
		$struct = array( "methodName" => $methodName, "params" => $args );
		$this->calls[] = $struct;
	}


	function query() {
		return parent::query( "system.multicall", $this->calls );
	}


	function IXR_Client($server, $path = false, $port = 80) {
		if (!$path) {
			$bits = parse_url( $server );
			$this->server = $bits["host"];
			$this->port = (isset( $bits["port"] ) ? $bits["port"] : 80);
			$this->path = (isset( $bits["path"] ) ? $bits["path"] : "/");

			if (!$this->path) {
				$this->path = "/";
			}
		}
		else {
			$this->server = $server;
			$this->path = $path;
			$this->port = $port;
		}

		$this->useragent = "The Incutio XML-RPC PHP Library";
	}


	function getResponse() {
		return $this->message->params[0];
	}


	function isError() {
		return is_object( $this->error );
	}


	function getErrorCode() {
		return $this->error->code;
	}


	function getErrorMessage() {
		return $this->error->message;
	}


}


IXR_Server;
IXR_Client;
?>