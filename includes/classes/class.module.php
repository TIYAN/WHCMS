<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

class WHMCS_Module {
	private $type = "";
	private $loadedmodule = "";

	const FUNCTIONDOESNTEXIST = "!Function not found in module!";

	public function __construct($type = "") {
		if ($type) {
			$this->setType($type);
		}

	}

	function settype($type) {
		global $whmcs;

		$type = $whmcs->sanitize("a-z", $type);
		$this->type = $type;
	}

	function gettype() {
		global $whmcs;

		$type = $whmcs->sanitize("a-z", $this->type);
		return $type;
	}

	private function setLoadedModule($module) {
		$this->loadedmodule = $module;
	}

	private function getLoadedModule() {
		return $this->loadedmodule;
	}

	public function getList($type = "") {
		if ($type) {
			$this->setType($type);
		}

		$modules = array();
		$dirpath = ROOTDIR . "/modules/" . $this->getType() . "/";

		if (!is_dir($dirpath)) {
			return false;
		}

		$dh = opendir($dirpath);

		while (false !== $module = readdir($dh)) {
			if (is_file($dirpath . ("/" . $module . "/" . $module . ".php"))) {
				$modules[] = $module;
			}
		}

		sort($modules);
		return $modules;
	}

	public function load($module) {
		global $whmcs;
		global $licensing;

		$module = $whmcs->sanitize("0-9a-z_-", $module);
		$modpath = ROOTDIR . "/modules/" . $this->getType() . ("/" . $module . "/" . $module . ".php");

		if (!file_exists($modpath)) {
			return false;
		}

		include_once $modpath;
		$this->setLoadedModule($module);
		return true;
	}

	public function call($name, $params = array()) {
		global $whmcs;
		global $licensing;

		if ($this->isExists($name)) {
			$response = call_user_func($this->getLoadedModule() . "_" . $name, $params);
			return $response;
		}

		return FUNCTIONDOESNTEXIST;
	}

	public function isExists($name) {
		return function_exists($this->getLoadedModule() . "_" . $name);
	}
}

?>