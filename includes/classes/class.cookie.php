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
 **/

class WHMCS_Cookie {
	public function __construct() {
	}

	public static function get($name, $decodearray = false) {
		$val = (array_key_exists("WHMCS" . $name, $_COOKIE) ? $_COOKIE["WHMCS" . $name] : "");

		if ($decodearray) {
			$val = json_decode(base64_decode($val), true);
			$val = (is_array($val) ? htmlspecialchars_array($val) : array());
		}

		return $val;
	}

	public static function set($name, $value, $expires = 0, $secure = false) {
		if (is_array($value)) {
			$value = base64_encode(json_encode($value));
		}


		if (!is_numeric($expires)) {
			if (substr($expires, 0 - 1) == "m") {
				$expires = time() + substr($expires, 0, 0 - 1) * 30 * 24 * 60 * 60;
			}
			else {
				$expires = 0;
			}
		}

		return setcookie("WHMCS" . $name, $value, $expires, "/", null, $secure, true);
	}

	public static function delete($name) {
		unset($_COOKIE["WHMCS" . $name]);
		return self::set($name, null, 0 - 86400);
	}
}

?>