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

class WHMCS_Orders extends WHMCS_TableModel 
{
	private $orderid = 0;
    private $orderdata = null;
    private $statusoutputs = null;

	public function _execute($criteria = null) {
		return $this->getOrders($criteria);
	}

	public function getOrders($criteria = array()) {
		global $aInt;
		global $currency;

		$query = "FROM tblorders INNER JOIN tblclients ON tblclients.id=tblorders.userid";

		if ($criteria['paymentstatus']) {
			$query .= " INNER JOIN tblinvoices ON tblinvoices.id=tblorders.invoiceid";
		}

		$filters = $this->buildCriteria($criteria);

		if (count($filters)) {
			$query .= " WHERE " . implode(" AND ", $filters);
		}

		$result = full_query("SELECT COUNT(tblorders.id) " . $query);
		$data = mysql_fetch_array($result);
		$this->getPageObj()->setNumResults($data[0]);
		$query .= " ORDER BY tblorders." . $this->getPageObj()->getOrderBy() . " " . $this->getPageObj()->getSortDirection();
		$gateways = new WHMCS_Gateways();
		$invoices = new WHMCS_Invoices();
		$orders = array();
		$query = "SELECT tblorders.id,tblorders.ordernum,tblorders.userid,tblorders.date,tblorders.amount,tblorders.paymentmethod,tblorders.status,tblorders.invoiceid,tblorders.ipaddress,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblclients.groupid,tblclients.currency,(SELECT status FROM tblinvoices WHERE id=tblorders.invoiceid) AS invoicestatus " . $query . " LIMIT " . $this->getQueryLimit();
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$ordernum = $data['ordernum'];
			$userid = $data['userid'];
			$date = $data['date'];
			$amount = $data['amount'];
			$gateway = $data['paymentmethod'];
			$status = $data['status'];
			$invoiceid = $data['invoiceid'];
			$firstname = $data['firstname'];
			$lastname = $data['lastname'];
			$companyname = $data['companyname'];
			$groupid = $data['groupid'];
			$currency = $data['currency'];
			$ipaddress = $data['ipaddress'];
			$invoicestatus = $data['invoicestatus'];
			$date = fromMySQLDate($date, 1);
			$paymentmethod = $gateways->getDisplayName($gateway);
			$statusformatted = $this->formatStatus($status);

			if ($invoiceid == "0") {
				$paymentstatus = "<span class=\"textgreen\">" . $aInt->lang("orders", "noinvoicedue") . "</span>";
			}
			else {
				if (!$invoicestatus) {
					$paymentstatus = "<span class=\"textred\">Invoice Deleted</span>";
				}
				else {
					if ($invoicestatus == "Paid") {
						$paymentstatus = "<span class=\"textgreen\">" . $aInt->lang("status", "complete") . "</span>";
					}
					else {
						if ($invoicestatus == "Unpaid") {
							$paymentstatus = "<span class=\"textred\">" . $aInt->lang("status", "incomplete") . "</span>";
						}
						else {
							$paymentstatus = $invoices->formatStatus($invoicestatus);
						}
					}
				}
			}

			$currency = getCurrency("", $currency);
			$amount = formatCurrency($amount);
			$clientname = $aInt->outputClientLink($userid, $firstname, $lastname, $companyname, $groupid);
			$orders[] = array("id" => $id, "ordernum" => $ordernum, "date" => $date, "clientname" => $clientname, "gateway" => $gateway, "paymentmethod" => $paymentmethod, "amount" => $amount, "paymentstatus" => strip_tags($paymentstatus), "paymentstatusformatted" => $paymentstatus, "status" => $status, "statusformatted" => $statusformatted);
		}

		return $orders;
	}

	private function buildCriteria($criteria) {
		$filters = array();

		if ($criteria['status']) {
			if (($criteria['status'] == "Pending" || $criteria['status'] == "Active") || $criteria['status'] == "Cancelled") {
				$statusfilter = "";
				$where = array("show" . strtolower($criteria['status']) => "1");
				$result = select_query("tblorderstatuses", "title", $where);

				while ($data = mysql_fetch_array($result)) {
					$statusfilter .= "'" . $data[0] . "',";
				}

				$statusfilter = substr($statusfilter, 0, 0 - 1);
				$filters[] = "tblorders.status IN (" . $statusfilter . ")";
			}
			else {
				$filters[] = "tblorders.status='" . db_escape_string($criteria['status']) . "'";
			}
		}


		if ($criteria['clientid']) {
			$filters[] = "tblorders.userid='" . db_escape_string($criteria['clientid']) . "'";
		}


		if ($criteria['amount']) {
			$filters[] = "tblorders.amount='" . db_escape_string($criteria['amount']) . "'";
		}


		if ($criteria['orderid']) {
			$filters[] = "tblorders.id='" . db_escape_string($criteria['orderid']) . "'";
		}


		if ($criteria['ordernum']) {
			$filters[] = "tblorders.ordernum='" . db_escape_string($criteria['ordernum']) . "'";
		}


		if ($criteria['orderip']) {
			$filters[] = "tblorders.ipaddress='" . db_escape_string($criteria['orderip']) . "'";
		}


		if ($criteria['orderdate']) {
			$tempdate = toMySQLDate(urldecode($criteria['orderdate']));
			$filters[] = "tblorders.date LIKE '" . db_escape_string($tempdate) . "%'";
		}


		if ($criteria['clientname']) {
			$filters[] = "concat(firstname,' ',lastname) LIKE '%" . db_escape_string($criteria['clientname']) . "%'";
		}


		if ($criteria['paymentstatus']) {
			$filters[] = "tblinvoices.status='" . db_escape_string($criteria['paymentstatus']) . "'";
		}

		return $filters;
	}

	public function getStatuses() {
		$statuses = array();
		$result = select_query("tblorderstatuses", "title,color", "", "sortorder", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$statuses[$data['title']] = "<span style=\"color:" . $data['color'] . "\">" . $data['title'] . "</span>";
		}

		$this->statusoutputs = $statuses;
		return $statuses;
	}

	public function formatStatus($status) {
		if (!$this->statusoutputs) {
			$this->getStatuses();
		}

		return array_key_exists($status, $this->statusoutputs) ? $this->statusoutputs[$status] : $status;
	}

	public function setID($orderid) {
		$this->orderid = (int)$orderid;
		$data = $this->loadData();
		return is_array($data) ? true : false;
	}

	public function loadData() {
		$result = select_query("tblorders", "", array("id" => $this->orderid));
		$this->orderdata = mysql_fetch_assoc($result);
		return $this->orderdata;
	}

	public function getData($var = "") {
		if (is_array($this->orderdata) && $var) {
			return isset($this->orderdata[$var]) ? $this->orderdata[$var] : "";
		}

	}

	public function getFraudResults() {
		global $whmcs;

		$fraudmodule = $this->getData("fraudmodule");

		if ($fraudmodule) {
			if (!isValidforPath($fraudmodule)) {
				exit("Invalid Fraud Module Name");
			}

			include ROOTDIR . ("/modules/fraud/" . $fraudmodule . "/" . $fraudmodule . ".php");
			$fraudoutput = $this->getData("fraudoutput");
			$fraudresults = getResultsArray($fraudoutput);
			return $fraudresults;
		}

		return false;
	}

	public function delete($orderid = "") {
		if (!$orderid) {
			$orderid = $this->orderid;
		}

		$orderid = (int)$orderid;
		run_hook("DeleteOrder", array("orderid" => $orderid));
		$result = select_query("tblorders", "userid,invoiceid", array("id" => $orderid));
		$data = mysql_fetch_array($result);
		$userid = $data['userid'];
		$invoiceid = $data['invoiceid'];
		delete_query("tblhostingconfigoptions", "relid IN (SELECT id FROM tblhosting WHERE orderid=" . $orderid . ")");
		delete_query("tblaffiliatesaccounts", "relid IN (SELECT id FROM tblhosting WHERE orderid=" . $orderid . ")");
		delete_query("tblhosting", array("orderid" => $orderid));
		delete_query("tblhostingaddons", array("orderid" => $orderid));
		delete_query("tbldomains", array("orderid" => $orderid));
		delete_query("tblupgrades", array("orderid" => $orderid));
		delete_query("tblorders", array("id" => $orderid));
		delete_query("tblinvoices", array("id" => $invoiceid));
		delete_query("tblinvoiceitems", array("invoiceid" => $invoiceid));
		logActivity("Deleted Order - Order ID: " . $orderid, $userid);
		return true;
	}

	public function setCancelled($orderid = "") {
		if (!$orderid) {
			$orderid = $this->orderid;
		}

		return $this->changeStatus($orderid, "Cancelled");
	}

	public function setFraud($orderid = "") {
		if (!$orderid) {
			$orderid = $this->orderid;
		}

		return $this->changeStatus($orderid, "Fraud");
	}

	public function setPending($orderid = "") {
		if (!$orderid) {
			$orderid = $this->orderid;
		}

		return $this->changeStatus($orderid, "Pending");
	}

	private function changeStatus($orderid, $status) {
		if (!$orderid) {
			return false;
		}

		$orderid = (int)$orderid;

		if ($status == "Cancelled") {
			run_hook("CancelOrder", array("orderid" => $orderid));
		}
		else {
			if ($status == "Fraud") {
				run_hook("FraudOrder", array("orderid" => $orderid));
			}
			else {
				if ($status == "Pending") {
					run_hook("PendingOrder", array("orderid" => $orderid));
				}
			}
		}

		update_query("tblorders", array("status" => $status), array("id" => $orderid));

		if ($status == "Cancelled" || $status == "Fraud") {
			$result = select_query("tblhosting", "tblhosting.id,tblhosting.domainstatus,tblproducts.servertype,tblhosting.packageid,tblproducts.stockcontrol,tblproducts.qty", array("orderid" => $orderid), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");

			while ($data = mysql_fetch_array($result)) {
				$productid = $data['id'];
				$prodstatus = $data['domainstatus'];
				$module = $data['servertype'];
				$packageid = $data['packageid'];
				$stockcontrol = $data['stockcontrol'];
				$qty = $data['qty'];

				if ($module && ($prodstatus == "Active" || $prodstatus == "Suspended")) {
					logActivity("Running Module Terminate on Order Cancel");

					if (!isValidforPath($module)) {
						exit("Invalid Server Module Name");
					}

					require_once ROOTDIR . ("/modules/servers/" . $module . "/" . $module . ".php");
					$moduleresult = ServerTerminateAccount($productid);

					if ($moduleresult == "success") {
						update_query("tblhosting", array("domainstatus" => $status), array("id" => $productid));

						if ($stockcontrol == "on") {
							update_query("tblproducts", array("qty" => "+1"), array("id" => $packageid));
						}
					}
				}

				update_query("tblhosting", array("domainstatus" => $status), array("id" => $productid));

				if ($stockcontrol == "on") {
					update_query("tblproducts", array("qty" => "+1"), array("id" => $packageid));
				}
			}
		}
		else {
			update_query("tblhosting", array("domainstatus" => $status), array("orderid" => $orderid));
		}

		update_query("tblhostingaddons", array("status" => $status), array("orderid" => $orderid));

		if ($status == "Pending") {
			$result = select_query("tbldomains", "id,type", array("orderid" => $orderid));

			while ($data = mysql_fetch_assoc($result)) {
				if ($data['type'] == "Transfer") {
					$status = "Pending Transfer";
				}
				else {
					$status = "Pending";
				}

				update_query("tbldomains", array("status" => $status), array("id" => $data['id']));
			}
		}
		else {
			update_query("tbldomains", array("status" => $status), array("orderid" => $orderid));
		}

		$result = select_query("tblorders", "userid,invoiceid", array("id" => $orderid));
		$data = mysql_fetch_array($result);
		$userid = $data['userid'];
		$invoiceid = $data['invoiceid'];

		if ($status == "Pending") {
			update_query("tblinvoices", array("status" => "Unpaid"), array("id" => $invoiceid, "status" => "Cancelled"));
		}
		else {
			update_query("tblinvoices", array("status" => "Cancelled"), array("id" => $invoiceid, "status" => "Unpaid"));
			run_hook("InvoiceCancelled", array("invoiceid" => $invoiceid));
		}

		logActivity("Order Status set to " . $status . " - Order ID: " . $orderid, $userid);
	}

	public function getItems() {
		global $aInt;

		$orderid = $this->orderid;
		$items = array();
		$result = select_query("tblhosting", "", array("orderid" => $orderid));

		while ($data = mysql_fetch_array($result)) {
			$hostingid = $data['id'];
			$domain = $data['domain'];
			$billingcycle = $data['billingcycle'];
			$hostingstatus = $data['domainstatus'];
			$firstpaymentamount = formatCurrency($data['firstpaymentamount']);
			$recurringamount = $data['amount'];
			$packageid = $data['packageid'];
			$server = $data['server'];
			$regdate = $data['regdate'];
			$nextduedate = $data['nextduedate'];
			$serverusername = $data['username'];
			$serverpassword = decrypt($data['password']);
			$result2 = select_query("tblproducts", "tblproducts.name,tblproducts.type,tblproducts.welcomeemail,tblproducts.autosetup,tblproducts.servertype,tblproductgroups.name AS groupname", array("tblproducts.id" => $packageid), "", "", "", "tblproductgroups ON tblproducts.gid=tblproductgroups.id");
			$data = mysql_fetch_array($result2);
			$groupname = $data['groupname'];
			$productname = $data['name'];
			$producttype = $data['type'];
			$welcomeemail = $data['welcomeemail'];
			$autosetup = $data['autosetup'];
			$servertype = $data['servertype'];

			if ($producttype == "hostingaccount") {
				$type = $aInt->lang("orders", "sharedhosting");
			}
			else {
				if ($producttype == "reselleraccount") {
					$type = $aInt->lang("orders", "resellerhosting");
				}
				else {
					if ($producttype == "server") {
						$type = $aInt->lang("orders", "server");
					}
					else {
						if ($producttype == "other") {
							$type = $aInt->lang("orders", "other");
						}
					}
				}
			}

			$items[] = array("type" => "product", "producttype" => $type, "description" => $groupname . " - " . $productname, "domain" => $domain, "billingcycle" => $aInt->lang("billingcycles", str_replace(array("-", "account", " "), "", strtolower($billingcycle))), "amount" => $firstpaymentamount, "paymentstatus" => $paymentstatus, "status" => $aInt->lang("status", strtolower($hostingstatus)));
		}

		$predefinedaddons = array();
		$result = select_query("tbladdons", "", "");

		while ($data = mysql_fetch_array($result)) {
			$addon_id = $data['id'];
			$addon_name = $data['name'];
			$addon_welcomeemail = $data['welcomeemail'];
			$predefinedaddons[$addon_id] = array("name" => $addon_name, "welcomeemail" => $addon_welcomeemail);
		}

		$result = select_query("tblhostingaddons", "", array("orderid" => $orderid));

		while ($data = mysql_fetch_array($result)) {
			$aid = $data['id'];
			$hostingid = $data['hostingid'];
			$addonid = $data['addonid'];
			$name = $data['name'];
			$billingcycle2 = $data['billingcycle'];
			$addonamount = $data['recurring'] + $data['setupfee'];
			$addonstatus = $data['status'];
			$regdate = $data['regdate'];
			$nextduedate = $data['nextduedate'];
			$addonamount = formatCurrency($addonamount);

			if (!$name) {
				$name = $predefinedaddons[$addonid]['name'];
			}

			$items[] = array("type" => "addon", "producttype" => $aInt->lang("orders", "addon"), "description" => $name, "domain" => "", "billingcycle" => $aInt->lang("billingcycles", str_replace(array("-", "account", " "), "", strtolower($billingcycle2))), "amount" => $addonamount, "paymentstatus" => $paymentstatus, "status" => $aInt->lang("status", strtolower($addonstatus)));
		}

		$result = select_query("tbldomains", "", array("orderid" => $orderid));

		while ($data = mysql_fetch_array($result)) {
			$domainid = $data['id'];
			$type = $data['type'];
			$domain = $data['domain'];
			$registrationperiod = $data['registrationperiod'];
			$status = $data['status'];
			$regdate = $data['registrationdate'];
			$nextduedate = $data['nextduedate'];
			$domainamount = formatCurrency($data['firstpaymentamount']);
			$domainregistrar = $data['registrar'];
			$dnsmanagement = $data['dnsmanagement'];
			$emailforwarding = $data['emailforwarding'];
			$idprotection = $data['idprotection'];
			$type = $aInt->lang("domains", strtolower($type));

			if ($dnsmanagement) {
				$type .= " + " . $aInt->lang("domains", "dnsmanagement");
			}


			if ($emailforwarding) {
				$type .= " + " . $aInt->lang("domains", "emailforwarding");
			}


			if ($idprotection) {
				$type .= " + " . $aInt->lang("domains", "idprotection");
			}

			$items[] = array("type" => "domain", "producttype" => $aInt->lang("fields", "domain"), "description" => $type, "domain" => $domain, "billingcycle" => $registrationperiod . " " . $aInt->lang("domains", "year" . $regperiods), "amount" => $domainamount, "paymentstatus" => $paymentstatus, "status" => $aInt->lang("status", strtolower(str_replace(" ", "", $status))));
		}

		$result = select_query("tblupgrades", "", array("orderid" => $orderid));

		while ($data = mysql_fetch_array($result)) {
			$upgradeid = $data['id'];
			$type = $data['type'];
			$relid = $data['relid'];
			$originalvalue = $data['originalvalue'];
			$newvalue = $data['newvalue'];
			$upgradeamount = formatCurrency($data['amount']);
			$newrecurringamount = $data['newrecurringamount'];
			$status = $data['status'];
			$paid = $data['paid'];
			$result2 = select_query("tblhosting", "tblproducts.name AS productname,domain", array("tblhosting.id" => $relid), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");
			$data = mysql_fetch_array($result2);
			$productname = $data['productname'];
			$domain = $data['domain'];

			if ($type == "package") {
				$result2 = select_query("tblproducts", "name", array("id" => $originalvalue));
				$data = mysql_fetch_array($result2);
				$oldpackagename = $data['name'];
				$newvalue = explode(",", $newvalue);
				$newpackageid = $newvalue[0];
				$result2 = select_query("tblproducts", "name", array("id" => $newpackageid));
				$data = mysql_fetch_array($result2);
				$newpackagename = $data['name'];
				$newbillingcycle = $newvalue[1];
				$details = "<a href=\"clientshosting.php?userid=" . $userid . "&id=" . $relid . "\">" . $oldpackagename . " => " . $newpackagename . "</a><br />";

				if ($domain) {
					$details .= $domain;
				}

				$items[] = array("type" => "upgrade", "producttype" => "Product Upgrade", "description" => $details, "domain" => "", "billingcycle" => $aInt->lang("billingcycles", $newbillingcycle), "amount" => $upgradeamount, "paymentstatus" => $paymentstatus, "status" => $aInt->lang("status", strtolower($status)));
			}


			if ($type == "configoptions") {
				$tempvalue = explode("=>", $originalvalue);
				$configid = $tempvalue[0];
				$oldoptionid = $tempvalue[1];
				$result2 = select_query("tblproductconfigoptions", "", array("id" => $configid));
				$data = mysql_fetch_array($result2);
				$configname = $data['optionname'];
				$optiontype = $data['optiontype'];

				if ($optiontype == 1 || $optiontype == 2) {
					$result2 = select_query("tblproductconfigoptionssub", "", array("id" => $oldoptionid));
					$data = mysql_fetch_array($result2);
					$oldoptionname = $data['optionname'];
					$result2 = select_query("tblproductconfigoptionssub", "", array("id" => $newvalue));
					$data = mysql_fetch_array($result2);
					$newoptionname = $data['optionname'];
				}
				else {
					if ($optiontype == 3) {
						if ($oldoptionid) {
							$oldoptionname = "Yes";
							$newoptionname = "No";
						}
						else {
							$oldoptionname = "No";
							$newoptionname = "Yes";
						}
					}
					else {
						if ($optiontype == 4) {
							$result2 = select_query("tblproductconfigoptionssub", "", array("configid" => $configid));
							$data = mysql_fetch_array($result2);
							$optionname = $data['optionname'];
							$oldoptionname = $oldoptionid;
							$newoptionname = $newvalue . " x " . $optionname;
						}
					}
				}

				$details = "<a href=\"clientshosting.php?userid=" . $userid . "&id=" . $relid . "\">" . $productname;
				$details .= " - " . $domain;
				$details .= "</a><br />" . $configname . ": " . $oldoptionname . " => " . $newoptionname . "<br>";
				$items[] = array("type" => "upgrade", "producttype" => "Options Upgrade", "description" => $details, "domain" => "", "billingcycle" => "", "amount" => $upgradeamount, "paymentstatus" => $paymentstatus, "status" => $aInt->lang("status", strtolower($status)));
			}
		}

		return $items;
	}
}

?>