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
 **/

class WHMCS_2FA 
{
	public $settings = array();
    public $clientmodules = array();
    public $adminmodules = array();
    public $adminmodule = "";
    public $adminsettings = array();
    public $admininfo = array();
    public $clientmodule = "";
    public $clientsettings = array();
    public $clientinfo = array();
    private $adminid = "";
    private $clientid = "";

	public function __construct() {
		$this->loadSettings();
	}

	public function loadSettings() {
		global $whmcs;

		$twofasettings = $whmcs->get_config("2fasettings");
		$this->settings = unserialize($twofasettings);

		if (!isset($this->settings['modules'])) {
			return false;
		}

		foreach ($this->settings['modules'] as $module => $data) {

			if ($data['clientenabled']) {
				$this->clientmodules[] = $module;
			}


			if ($data['adminenabled']) {
				$this->adminmodules[] = $module;
				continue;
			}
		}

		return true;
	}

	public function isForced() {
		if ($this->clientid) {
			return $this->isForcedClients();
		}


		if ($this->adminid) {
			return $this->isForcedAdmins();
		}

		return false;
	}

	public function isForcedClients() {
		return $this->settings['forceclient'];
	}

	public function isForcedAdmins() {
		return $this->settings['forceadmin'];
	}

	public function isActiveClients() {
		return count($this->clientmodules) ? true : false;
	}

	public function isActiveAdmins() {
		return count($this->adminmodules) ? true : false;
	}

	public function setClientID($id) {
		$this->clientid = $id;
		$this->adminid = "";
		return $this->loadClientSettings();
	}

	public function setAdminID($id) {
		$this->clientid = "";
		$this->adminid = $id;
		return $this->loadAdminSettings();
	}

	public function loadClientSettings() {
		$data = get_query_vals("tblclients", "id,firstname,lastname,email,authmodule,authdata", array("id" => $this->clientid, "status" => array("sqltype" => "NEQ", "value" => "Closed")));

		if (!$data['id']) {
			return false;
		}

		$this->clientmodule = $data['authmodule'];
		$this->clientsettings = unserialize($data['authdata']);

		if (!is_array($this->clientsettings)) {
			$this->clientsettings = array();
		}

		unset($data['authmodule']);
		unset($data['authdata']);
		$data['username'] = $data['email'];
		$this->clientinfo = $data;
		return true;
	}

	public function loadAdminSettings() {
		$data = get_query_vals("tbladmins", "id,username,firstname,lastname,email,authmodule,authdata", array("id" => $this->adminid, "disabled" => "0"));

		if (!$data['id']) {
			return false;
		}

		$this->adminmodule = $data['authmodule'];
		$this->adminsettings = unserialize($data['authdata']);

		if (!is_array($this->adminsettings)) {
			$this->adminsettings = array();
		}

		unset($data['authmodule']);
		unset($data['authdata']);
		$this->admininfo = $data;
		return true;
	}

	public function getAvailableModules() {
		if ($this->clientid) {
			return $this->getAvailableClientModules();
		}


		if ($this->adminid) {
			return $this->getAvailableAdminModules();
		}

		return false;
	}

	public function getAvailableClientModules() {
		return $this->clientmodules;
	}

	public function getAvailableAdminModules() {
		return $this->adminmodules;
	}

	public function isEnabled() {
		if ($this->clientid) {
			return $this->isEnabledClient();
		}


		if ($this->adminid) {
			return $this->isEnabledAdmin();
		}

		return false;
	}

	public function isEnabledClient() {
		return $this->clientmodule ? true : false;
	}

	public function isEnabledAdmin() {
		return $this->adminmodule ? true : false;
	}

	public function getModule() {
		if ($this->clientid) {
			return $this->clientmodule;
		}


		if ($this->adminid) {
			return $this->adminmodule;
		}

		return false;
	}

	public function moduleCall($function, $module = "") {
		$mod = new WHMCS_Module("security");
		$module = $module ? $module : $this->getModule();
		$loaded = $mod->load($module);

		if (!$loaded) {
			return false;
		}

		$params = $this->buildParams($module);
		$result = $mod->call($function, $params);
		return $result;
	}

	public function buildParams($module) {
		$params = array();
		$params['settings'] = $this->settings['modules'][$module];
		$params['user_info'] = $this->clientid ? $this->clientinfo : $this->admininfo;
		$params['user_settings'] = ($this->clientid ? $this->clientsettings : $this->adminsettings);
		$params['post_vars'] = $_POST;
		return $params;
	}

	public function activateUser($module, $settings = array()) {
		global $whmcs;

		if ($this->clientid) {
			$backupcode = sha1($whmcs->get_hash() . $this->adminid . time());
			$backupcode = substr($backupcode, 0, 16);
			$settings['backupcode'] = sha1($backupcode);
			update_query("tblclients", array("authmodule" => $module, "authdata" => serialize($settings)), array("id" => $this->clientid));
			$backupcodedisplay = substr($backupcode, 0, 4) . " " . substr($backupcode, 4, 4) . " " . substr($backupcode, 8, 4) . " " . substr($backupcode, 12, 4);
			return $backupcodedisplay;
		}


		if ($this->adminid) {
			$backupcode = sha1($whmcs->get_hash() . $this->adminid . time());
			$backupcode = substr($backupcode, 0, 16);
			$settings['backupcode'] = sha1($backupcode);
			update_query("tbladmins", array("authmodule" => $module, "authdata" => serialize($settings)), array("id" => $this->adminid));
			$backupcodedisplay = substr($backupcode, 0, 4) . " " . substr($backupcode, 4, 4) . " " . substr($backupcode, 8, 4) . " " . substr($backupcode, 12, 4);
			return $backupcodedisplay;
		}

		return false;
	}

	public function disableUser() {
		global $whmcs;

		if ($this->clientid) {
			update_query("tblclients", array("authmodule" => "", "authdata" => ""), array("id" => $this->clientid));
			return true;
		}


		if ($this->adminid) {
			update_query("tbladmins", array("authmodule" => "", "authdata" => ""), array("id" => $this->adminid));
			return true;
		}

		return false;
	}

	public function saveUserSettings($arr) {
		if (!is_array($arr)) {
			return false;
		}


		if ($this->clientid) {
			$this->clientsettings = array_merge($this->clientsettings, $arr);
			update_query("tblclients", array("authdata" => serialize($this->clientsettings)), array("id" => $this->clientid));
			return true;
		}


		if ($this->adminid) {
			$this->adminsettings = array_merge($this->adminsettings, $arr);
			update_query("tbladmins", array("authdata" => serialize($this->adminsettings)), array("id" => $this->adminid));
			return true;
		}

		return false;
	}

	public function getUserSetting($var) {
		if ($this->clientid) {
			return isset($this->clientsettings[$var]) ? $this->clientsettings[$var] : "";
		}


		if ($this->adminid) {
			return isset($this->adminsettings[$var]) ? $this->adminsettings[$var] : "";
		}

		return false;
	}

	public function verifyBackupCode($code) {
		$backupcode = $this->getUserSetting("backupcode");

		if (!$backupcode) {
			return false;
		}

		$code = preg_replace("/[^a-z0-9]/", "", strtolower($code));
		$code = sha1($code);
		return $backupcode == $code ? true : false;
	}

	public function generateNewBackupCode() {
		global $whmcs;

		$backupcode = sha1($whmcs->get_hash() . $uid . time());
		$backupcode = substr($backupcode, 0, 16);
		$uid = ($this->clientid ? $this->clientid : $this->adminid);
		$this->saveUserSettings(array("backupcode" => sha1($backupcode)));
		$backupcodedisplay = substr($backupcode, 0, 4) . " " . substr($backupcode, 4, 4) . " " . substr($backupcode, 8, 4) . " " . substr($backupcode, 12, 4);
		return $backupcodedisplay;
	}
}

?>