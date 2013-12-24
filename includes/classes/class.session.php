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
 **/

class WHMCS_Session {
	private $last_session_data = array();

	public function __construct() {
	}

	private function getSessionName($instanceid) {
		$instanceid = "WHMCS" . $instanceid;
		return $instanceid;
	}

	public function create($instanceid) {
		session_name($this->getSessionName($instanceid));
		session_set_cookie_params(0, "/", null, false, true);

		if (session_start()) {
			return session_id();
		}

		return "";
	}

	public function get($key) {
		return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : "";
	}

	public static function set($key, $value) {
		$_SESSION[$key] = $value;
		return true;
	}

	public static function delete($key) {
		if (array_key_exists($key, $_SESSION)) {
			unset($_SESSION[$key]);
			return true;
		}

		return false;
	}

	public static function rotate() {
		return session_regenerate_id();
	}

	public static function destroy() {
		session_unset();
		session_destroy();
	}

	public static function nullify() {
		$this->last_session_data = $_SESSION;
		$_SESSION = array();
	}
}

?>