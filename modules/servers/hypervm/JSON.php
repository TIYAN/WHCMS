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

class Services_JSON {


	/**
	 * constructs a new JSON instance
	 *
	 * @param int     $use object behavior flags; combine with boolean-OR
	 *
	 *                           possible values:
	 *                           - SERVICES_JSON_LOOSE_TYPE:  loose typing.
	 *                                   \"{...}\" syntax creates associative arrays
	 *                                   instead of objects in decode().
	 *                           - SERVICES_JSON_SUPPRESS_ERRORS:  error suppression.
	 *                                   Values which can"t be encoded (e.g. resources)
	 *                                   appear as NULL instead of throwing errors.
	 *                                   By default, a deeply-nested resource will
	 *                                   bubble up with an error, so all return values
	 *                                   from encode() should be checked with isError()
	 */
	function Services_JSON($use = 0) {
		$this->use = $use;
	}


	/**
	 * convert a string from one UTF-16 char to one UTF-8 char
	 *
	 * Normally should be handled by mb_convert_encoding, but
	 * provides a slower PHP-only method for installations
	 * that lack the multibye string extension.
	 *
	 * @param string  $utf16 UTF-16 character
	 * @return   string  UTF-8 character
	 * @access   private
	 */
	function utf162utf8($utf16) {
		if (function_exists( "mb_convert_encoding" )) {
			return mb_convert_encoding( $utf16, "UTF-8", "UTF-16" );
		}

		$bytes = ord( $utf16[0] ) << 8 | ord( $utf16[1] );
		switch (true) {
		case ( 127 & $bytes ) == $bytes: {
				return chr( 127 & $bytes );
			}

		case ( 2047 & $bytes ) == $bytes: {
				return chr( 192 | $bytes >> 6 & 31 ) . chr( 128 | $bytes & 63 );
			}

		case ( 65535 & $bytes ) == $bytes: {
				return chr( 224 | $bytes >> 12 & 15 ) . chr( 128 | $bytes >> 6 & 63 ) . chr( 128 | $bytes & 63 );
			}
		}

		return "";
	}


	/**
	 * convert a string from one UTF-8 char to one UTF-16 char
	 *
	 * Normally should be handled by mb_convert_encoding, but
	 * provides a slower PHP-only method for installations
	 * that lack the multibye string extension.
	 *
	 * @param string  $utf8 UTF-8 character
	 * @return   string  UTF-16 character
	 * @access   private
	 */
	function utf82utf16($utf8) {
		if (function_exists( "mb_convert_encoding" )) {
			return mb_convert_encoding( $utf8, "UTF-16", "UTF-8" );
		}

		switch (strlen( $utf8 )) {
		case 1: {
				$utf8;
			}
		}

		return ;
	}


	/**
	 * encodes an arbitrary variable into JSON format
	 *
	 * @param mixed   $var any number, boolean, string, array, or object to be encoded.
	 *                           see argument 1 to Services_JSON() above for array-parsing behavior.
	 *                           if var is a strng, note that encode() always expects it
	 *                           to be in ASCII or UTF-8 format!
	 *
	 * @return   mixed   JSON string representation of input var or an error if a problem occurs
	 * @access   public
	 */
	function encode($var) {
		switch (gettype( $var )) {
		case "boolean": {
				($var ? "true" : "false");
			}
		}

		return ;
	}


	/**
	 * array-walking function for use in generating JSON-formatted name-value pairs
	 *
	 * @param string  $name  name of key to use
	 * @param mixed   $value reference to an array element to be encoded
	 *
	 * @return   string  JSON-formatted name-value pair, like "\"name\":value"
	 * @access   private
	 */
	function name_value($name, $value) {
		$encoded_value = $this->encode( $value );

		if (Services_JSON::iserror( $encoded_value )) {
			return $encoded_value;
		}

		return $this->encode( strval( $name ) ) . ":" . $encoded_value;
	}


	/**
	 * reduce a string by removing leading and trailing comments and whitespace
	 *
	 * @param unknown $str string      string value to strip of comments and whitespace
	 *
	 * @return   string  string value stripped of comments and whitespace
	 * @access   private
	 */
	function reduce_string($str) {
		$str = preg_replace( array( "#^\s*//(.+)$#m", "#^\s*/\*(.+)\*/#Us", "#/\*(.+)\*/\s*$#Us" ), "", $str );
		return trim( $str );
	}


	/**
	 * decodes a JSON string into appropriate variable
	 *
	 * @param string  $str JSON-formatted string
	 *
	 * @return   mixed   number, boolean, string, array, or object
	 *                   corresponding to given JSON input string.
	 *                   See argument 1 to Services_JSON() above for object-output behavior.
	 *                   Note that decode() always returns strings
	 *                   in ASCII or UTF-8 format!
	 * @access   public
	 */
	function decode($str) {
		$str = $this->reduce_string( $str );
		switch (strtolower( $str )) {
		case "true": {
				true;
			}
		}

		return ;
	}


	/**
	 *
	 *
	 * @todo Ultimately, this should just call PEAR::isError()
	 */
	function isError($data, $code = null) {
		if (class_exists( "pear" )) {
			return PEAR::iserror( $data, $code );
		}


		if (( is_object( $data ) && ( get_class( $data ) == "services_json_error" || is_subclass_of( $data, "services_json_error" ) ) )) {
			return true;
		}

		return false;
	}


}


class Services_JSON_Error {
	function Services_JSON_Error($message = "unknown error", $code = null, $mode = null, $options = null, $userinfo = null) {
	}


}


define( "SERVICES_JSON_SLICE", 1 );
define( "SERVICES_JSON_IN_STR", 2 );
define( "SERVICES_JSON_IN_ARR", 3 );
define( "SERVICES_JSON_IN_OBJ", 4 );
define( "SERVICES_JSON_IN_CMT", 5 );
define( "SERVICES_JSON_LOOSE_TYPE", 16 );
define( "SERVICES_JSON_SUPPRESS_ERRORS", 32 );

if (class_exists( "PEAR_Error" )) {
	class Services_JSON_Error extends PEAR_Error {
		function Services_JSON_Error($message = "unknown error", $code = null, $mode = null, $options = null, $userinfo = null) {
			parent::pear_error( $message, $code, $mode, $options, $userinfo );
		}


	}


	return 1;
}

?>