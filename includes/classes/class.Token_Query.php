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
 **/

class WHMCS_Token_Query 
{
	private $tokenName = "";
	const DEFAULT_TOKEN_NAME = "TokenQuery";

	/**
	 * Constructor
	 *
	 * @param string $name Token name for object instance
	 *
	 * @return WHMCS_Token_Query
	 */
	public function __construct($name = "") {
		if (empty($name)) {
			$name = self::DEFAULT_TOKEN_NAME;
		}

		$this->setTokenName($name);
		return $this;
	}

	/**
	 * generate a new token value
	 *
	 * @return string Random alaphanumeric string 16 char long
	 */
	public function generateToken() {
		return $this->generateRandomAlphanumeric(16);
	}

	/**
	 * Determine if string would be a valid token
	 *
	 * @param string $token
	 *
	 * @return boolean
	 */
	public function isValidTokenFormat($token) {
		$isValid = false;

		if (is_string($token)) {
			$isValid = (preg_match("/[a-zA-Z0-9]{16}/", $token) ? true : false);
		}

		return $isValid;
	}

	/**
	 * Store a token for later retrieval
	 *
	 * Only supports retrieval within the same login session
	 *
	 * @param string $token token to store
	 *
	 * @return string Proxy name that must be used to store a correlative query
	 */
	public function setTokenValue($token) {
		$tokenName = $this->getTokenName();
		$this->setSessionValue($tokenName, $token);
		return $this->getQueryName($token);
	}

	/**
	 * Retrieve the last stored token value
	 *
	 * @return string A token value
	 */
	public function getTokenValue() {
		$token = "";
		$tokenName = $this->getTokenName();
		$token = $this->getSessionValue($tokenName);
		return $token;
	}

	/**
	 * Retrieve the query associated with a token value
	 *
	 * Note: if the token cannot be referenced, or the token value passed
	 * is empty, the an empty string is returned
	 *
	 * @param string $token
	 *
	 * @return string the stored query or a blank string
	 */
	public function getQuery($token = "") {
		$query = "";

		if ($token) {
			$tokenQueryName = $this->getQueryName($token);
			$query = $this->getSessionValue($tokenQueryName);
		}

		return $query;
	}

	/**
	 * Store a query for later retrieval
	 *
	 * @param string $token The associated token that can be used to retrieve query later
	 * @param string $query The query to store
	 *
	 * @return void
	 */
	public function setQuery($token = "", $query = "") {
		if ($token) {
			$tokenQueryName = $this->setTokenValue($token);
			$this->setSessionValue($tokenQueryName, $query);
		}

	}

	/**
	 * Get a random alphanumeric string
	 *
	 * @param integer $length desired character length of string
	 *
	 * @return string
	 */
	public function generateRandomAlphanumeric($length = 16) {
		mt_srand();
		$values = array_merge(range(65, 90), range(97, 122), range(48, 57));
		$max = count($values) - 1;
		$str = chr(mt_rand(97, 122));
		$i = 0;

		while ($i < $length) {
			$str .= chr($values[mt_rand(0, $max)]);
			++$i;
		}

		return $str;
	}

	/**
	 * Set token name associated with the object"s implementation
	 *
	 * @param unknown $name
	 *
	 * @return WHMCS_Token_Query
	 */
	public function setTokenName($name) {
		if (!is_string($name)) {
			throw new InvalidArgumentException(sprintf("Name must be a string", ""));
		}

		$this->tokenName = $name;
		return $this;
	}

	/**
	 * Get token name associated with the object"s implementation
	 *
	 * @return string
	 */
	public function getTokenName() {
		return $this->tokenName;
	}

	/**
	 * The internal reference point for the query correlative to a
	 * given token value
	 *
	 * @param string $token Token to find a query for
	 *
	 * @return string
	 */
	private function getQueryName($token) {
		return $this->getTokenName() . "_" . $token;
	}

	/**
	 * retrieve from the backend storage based on key
	 *
	 * Note: if key cannot be referenced, an empty string is returned
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	private function getSessionValue($key) {
		$value = "";

		if (class_exists("WHMCS_Session")) {
			$value = WHMCS_Session::get($key);
		}
		else {
			if (!empty($_SESSION[$key])) {
				$value = $_SESSION[$key];
			}
		}

		return $value;
	}

	/**
	 * store a key/value pair in the backend storage
	 *
	 * @param unknown $key
	 * @param unknown $value
	 *
	 * @return void
	 */
	private function setSessionValue($key, $value) {
		if (class_exists("WHMCS_Session")) {
			WHMCS_Session::set($key, $value);
		}
		else {
			$_SESSION[$key] = $value;
		}

		return $this;
	}
}

?>