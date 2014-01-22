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

class WHMCS_Gateways 
{
	private static $gateways = null;
	private $displaynames = null;

	public function __construct() {
	}

	public function getDisplayNames() {
		$result = select_query("tblpaymentgateways", "gateway,value", array("setting" => "name"), "order", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$this->displaynames[$data['gateway']] = $data['value'];
		}

		return $this->displaynames;
	}

	public function getDisplayName($gateway) {
		if (!is_array($this->displaynames)) {
			$this->getDisplayNames();
		}

		return array_key_exists($gateway, $this->displaynames) ? $this->displaynames[$gateway] : $gateway;
	}

	public static function isNameValid($gateway) {
		if (!is_string($gateway) || empty($gateway)) {
			return false;
		}


		if (!ctype_alnum(str_replace(array("_", "-"), "", $gateway))) {
			return false;
		}

		return true;
	}

	public static function getActiveGateways() {

		if (is_array(self::$gateways)) {
			return self::$gateways;
		}

		self::$gateways = array();
		$result = select_query("tblpaymentgateways", "DISTINCT gateway", "");

		while ($data = mysql_fetch_array($result)) {
			$gateway = $data[0];

			if (WHMCS_Gateways::isnamevalid($gateway)) {
				self::$gateways[] = $gateway;
			}
		}

		return self::$gateways;
	}

	public static function makeSafeName($gateway) {
		$validgateways = WHMCS_Gateways::getactivegateways();
		return in_array($gateway, $validgateways) ? $gateway : "";
	}

	public function getAvailableGateways($invoiceid = "") {
		$validgateways = array();
		$result = full_query("SELECT DISTINCT gateway, (SELECT value FROM tblpaymentgateways g2 WHERE g1.gateway=g2.gateway AND setting='name' LIMIT 1) AS `name`, (SELECT `order` FROM tblpaymentgateways g2 WHERE g1.gateway=g2.gateway AND setting='name' LIMIT 1) AS `order` FROM `tblpaymentgateways` g1 WHERE setting='visible' AND value='on' ORDER BY `order` ASC");

		while ($data = mysql_fetch_array($result)) {
			$validgateways[$data[0]] = $data[1];
		}


		if ($invoiceid) {
			$disabledgateways = array();
			$result = select_query("tblinvoiceitems", "", array("type" => "Hosting", "invoiceid" => $invoiceid));

			while ($data = mysql_fetch_assoc($result)) {
				$relid = $data['relid'];

				if ($relid) {
					$result2 = full_query("SELECT pg.disabledgateways AS disabled FROM tblhosting h LEFT JOIN tblproducts p on h.packageid = p.id LEFT JOIN tblproductgroups pg on p.gid = pg.id where h.id = " . (int)$relid);
					$data2 = mysql_fetch_assoc($result2);
					$gateways = explode(",", $data2['disabled']);
					foreach ($gateways as $gateway) {

						if (array_key_exists($gateway, $validgateways)) {
							unset($validgateways[$gateway]);
							continue;
						}
					}
				}
			}
		}

		return $validgateways;
	}

	public function getCCDateMonths() {
		$months = array();
		$i = 1;

		while ($i <= 12) {
			$months[] = str_pad($i, 2, "0", STR_PAD_LEFT);
			++$i;
		}

		return $months;
	}

	public function getCCStartDateYears() {
		$startyears = array();
		$i = date("Y") - 12;

		while ($i <= date("Y")) {
			$startyears[] = $i;
			++$i;
		}

		return $startyears;
	}

	public function getCCExpiryDateYears() {
		$i = date("Y");
		$expiryyears = array();

		while ($i <= date("Y") + 12) {
			$expiryyears[] = $i;
			++$i;
		}

		return $expiryyears;
	}
}

?>