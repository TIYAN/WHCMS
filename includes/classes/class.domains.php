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

class WHMCS_Domains {
	private $id = "";
	private $data = array();
	private $moduleresults = array();

	const ACTIVE_STATUS = "Active";

	public function __construct() {
	}

	public function getDomainsDatabyID($domainid) {
		$where = array("id" => $domainid);

		if (defined("CLIENTAREA")) {
			if (!isset($_SESSION['uid'])) {
				return false;
			}

			$where['userid'] = $_SESSION['uid'];
		}

		return $this->getDomainsData($where);
	}

	public function getDomainsData($where = "") {
		$result = select_query("tbldomains", "", $where);
		$data = mysql_fetch_array($result);

		if ($data['id']) {
			$this->id = $data['id'];
			$this->data = $data;
			return $data;
		}

		return false;
	}

	public function isActive() {
		if (is_array($this->data) && $this->data['status'] == self::ACTIVE_STATUS) {
			return true;
		}

		return false;
	}

	public function getData($var) {
		return isset($this->data[$var]) ? $this->data[$var] : "";
	}

	public function getModule() {
		global $whmcs;

		return $whmcs->sanitize("0-9a-z_-", $this->getData("registrar"));
	}

	public function hasFunction($function) {
		$mod = new WHMCS_Module("registrars");
		$module = $this->getModule();

		if (!$module) {
			$this->moduleresults = array("error" => "Domain not assigned to a registrar module");
			return false;
		}

		$loaded = $mod->load($module);

		if (!$loaded) {
			$this->moduleresults = array("error" => "Registrar module not found");
			return false;
		}

		return $mod->isExists($function);
	}

	public function moduleCall($function, $vars = "") {
		$mod = new WHMCS_Module("registrars");
		$module = $this->getModule();

		if (!$module) {
			$this->moduleresults = array("error" => "Domain not assigned to a registrar module");
			return false;
		}

		$loaded = $mod->load($module);

		if (!$loaded) {
			$this->moduleresults = array("error" => "Registrar module not found");
			return false;
		}

		$params = $this->buildParams($vars);
		$results = $mod->call($function, $params);

		if ($results === FUNCTIONDOESNTEXIST) {
			$this->moduleresults = array("error" => "Function not found");
			return false;
		}

		$this->moduleresults = $results;
		return ((is_array($results) && array_key_exists("error", $results)) && $results['error']) ? false : true;
	}

	private function buildParams($vars = "") {
		$params = array();
		$result = select_query("tblregistrars", "", array("registrar" => $this->getModule()));

		while ($data = mysql_fetch_array($result)) {
			$setting = $data['setting'];
			$value = $data['value'];
			$params[$setting] = decrypt($value);
		}

		$domainparts = explode(".", $this->getData("domain"), 2);
		$params['domainid'] = $this->getData("id");
		$params['sld'] = $domainparts[0];
		$params['tld'] = $domainparts[1];
		$params['regperiod'] = $this->getData("registrationperiod");
		$params['registrar'] = $this->getData("registrar");
		$params['regtype'] = $this->getData("type");

		if (is_array($vars)) {
			$params = array_merge($params, $vars);
		}

		return $params;
	}

	public function getModuleReturn($var = "") {
		if (!$var) {
			return $this->moduleresults;
		}

		return isset($this->moduleresults[$var]) ? $this->moduleresults[$var] : "";
	}

	public function getLastError() {
		return $this->getModuleReturn("error");
	}

	public function getDefaultNameservers() {
		global $whmcs;

		$vars = array();
		$serverid = get_query_val("tblhosting", "server", array("domain" => $this->getData("domain")));

		if ($serverid) {
			$result = select_query("tblservers", "nameserver1,nameserver2,nameserver3,nameserver4,nameserver5", array("id" => $serverid));
			$data = mysql_fetch_array($result);
			$i = 1;

			while ($i <= 5) {
				$vars["ns" . $i] = trim($data["nameserver" . $i]);
				++$i;
			}
		}
		else {
			$i = 1;

			while ($i <= 5) {
				$vars["ns" . $i] = trim($whmcs->get_config("DefaultNameserver" . $i));
				++$i;
			}
		}

		return $vars;
	}

	public function getSLD() {
		$domain = $this->getData("domain");
		$domainparts = explode(".", $this->getData("domain"), 2);
		return $domainparts[0];
	}

	public function getTLD() {
		$domain = $this->getData("domain");
		$domainparts = explode(".", $this->getData("domain"), 2);
		return $domainparts[1];
	}

	public function buildWHOISSaveArray($data) {
		$arr = array("First Name" => "firstname", "Last Name" => "lastname", "Full Name" => "fullname", "Contact Name" => "fullname", "Email" => "email", "Email Address" => "email", "Job Title" => "", "Company Name" => "companyname", "Organisation Name" => "companyname", "Address" => "address1", "Address 1" => "address1", "Street" => "address1", "Address 2" => "address2", "City" => "city", "State" => "state", "County" => "state", "Region" => "state", "Postcode" => "postcode", "ZIP Code" => "postcode", "ZIP" => "postcode", "Country" => "country", "Phone" => "phonenumber", "Phone Number" => "phonenumber");
		$data['fullname'] = $data['firstname'] . " " . $data['lastname'];
		$phonenumber = $data['phonenumber'];
		$phonenumber = preg_replace("/[^0-9]/", "", $phonenumber);
		$countrycode = $data['country'];
		$countrycallingcodes = array();
		require ROOTDIR . "/includes/countriescallingcodes.php";
		$countrycode = $countrycallingcodes[$countrycode];
		$data['phonenumber'] = "+" . $countrycode . "." . $phonenumber;
		$retarr = array();
		foreach ($arr as $k => $v) {
			$retarr[$k] = $data[$v];
		}

		return $retarr;
	}
}

?>