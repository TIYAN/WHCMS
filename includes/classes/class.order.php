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

class WHMCS_Order {
	private $orderid = "";
	private $data = array();

	public function __construct() {
	}

	public function setID($orderid) {
		$this->orderid = (int)$orderid;
		return $this->loadData();
	}

	public function loadData() {
		$result = select_query("tblorders", "tblorders.*,tblclients.firstname,tblclients.lastname,tblclients.email,tblclients.companyname,tblclients.address1,tblclients.address2,tblclients.city,tblclients.state,tblclients.postcode,tblclients.country,tblclients.groupid,(SELECT status FROM tblinvoices WHERE id=tblorders.invoiceid) AS invoicestatus", array("tblorders.id" => $this->orderid), "", "", "", "tblclients ON tblclients.id=tblorders.userid");
		$data = mysql_fetch_array($result);

		if (!$data['id']) {
			return false;
		}

		$this->data = $data;
		return true;
	}

	public function getData($var = "") {
		return array_key_exists($var, $this->data) ? $this->data[$var] : "";
	}

	public function createOrder($userid, $paymentmethod, $contactid = "") {
		global $whmcs;

		$order_number = generateUniqueID();
		$this->orderid = insert_query("tblorders", array("ordernum" => $order_number, "userid" => $userid, "contactid" => $contactid, "date" => "now()", "status" => "Pending", "paymentmethod" => $paymentmethod, "ipaddress" => $whmcs->get_user_ip()));
		logActivity("New Order Created - Order ID: " . $orderid . " - User ID: " . $userid);
		return $this->orderid;
	}

	public function updateOrder($data) {
	}
}

?>