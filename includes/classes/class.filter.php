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

class WHMCS_Filter {
	private $name = "";
	private $data = array();
	private $allowedvars = array();

	public function __construct() {
		$filtername = $this->getFilename();
		$this->name = $filtername;
		$this->data = WHMCS_Cookie::get("FD", true);
	}

	private function getFilename() {
		$filename = $_SERVER['PHP_SELF'];
		$filename = substr($filename, strrpos($filename, "/"));
		$filename = str_replace(array("/", ".php"), "", $filename);
		return $filename;
	}

	public function isActive() {
		if (!array_key_exists($this->name, $this->data)) {
			return false;
		}

		foreach ($this->data[$this->name] as $v) {

			if ($v) {
				return true;
			}
		}

		return false;
	}

	public function setAllowedVars($allowedvars) {
		$this->allowedvars = $allowedvars;
		return true;
	}

	public function addAllowedVar($var) {
		$this->allowedvars[] = $var;
		return true;
	}

	public function getFromReq($var) {
		global $whmcs;

		return $whmcs->get_req_var($var);
	}

	public function getFromSession($var) {
		return isset($this->data[$this->name][$var]) ? $this->data[$this->name][$var] : "";
	}

	public function get($var) {
		$this->addAllowedVar($var);

		if ($this->getFromReq("filter")) {
			return $this->getFromSession($var);
		}

		return $this->getFromReq($var);
	}

	public function store() {
		if ($this->getFromReq("filter")) {
			return false;
		}

		$arr = array();
		foreach ($this->allowedvars as $op) {
			$arr[$op] = $this->getFromReq($op);
		}

		$this->data[$this->name] = $arr;
		WHMCS_Cookie::set("FD", $this->data);
		return true;
	}

	public function redir($vars = "") {
		if (is_array($this->data[$this->name])) {
			if ($vars) {
				$vars .= "&filter=1";
			}
			else {
				$vars = "filter=1";
			}
		}

		redir($vars);
	}
}

?>