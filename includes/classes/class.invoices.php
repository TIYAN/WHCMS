<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

class WHMCS_Invoices extends WHMCS_TableModel {
	public function _execute($criteria = null) {
		return $this->getInvoices($criteria);
	}

	public function getInvoices($criteria = array()) {
		global $aInt;
		global $currency;

		$query = " FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid";
		$filters = $this->buildCriteria($criteria);
		$query .= (count($filters) ? " WHERE " . implode(" AND ", $filters) : "");
		$result = full_query("SELECT COUNT(*)" . $query);
		$data = mysql_fetch_array($result);
		$this->getPageObj()->setNumResults($data[0]);
		$gateways = new WHMCS_Gateways();
		$orderby = $this->getPageObj()->getOrderBy();

		if ($orderby == "clientname") {
			$orderby = "firstname " . $this->getPageObj()->getSortDirection() . ",lastname " . $this->getPageObj()->getSortDirection() . ",companyname";
		}


		if ($orderby == "id") {
			$orderby = "tblinvoices.invoicenum " . $this->getPageObj()->getSortDirection() . ",tblinvoices.id";
		}

		$invoices = array();
		$query = "SELECT tblinvoices.*,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblclients.groupid,tblclients.currency" . $query . " ORDER BY " . $orderby . " " . $this->getPageObj()->getSortDirection() . " LIMIT " . $this->getQueryLimit();
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$invoicenum = $data['invoicenum'];
			$userid = $data['userid'];
			$date = $data['date'];
			$duedate = $data['duedate'];
			$subtotal = $data['subtotal'];
			$credit = $data['credit'];
			$total = $data['total'];
			$gateway = $data['paymentmethod'];
			$status = $data['status'];
			$firstname = $data['firstname'];
			$lastname = $data['lastname'];
			$companyname = $data['companyname'];
			$groupid = $data['groupid'];
			$currency = $data['currency'];
			$clientname = $aInt->outputClientLink($userid, $firstname, $lastname, $companyname, $groupid);
			$paymentmethod = $gateways->getDisplayName($gateway);
			$currency = getCurrency("", $currency);
			$totalformatted = formatCurrency($credit + $total);
			$statusformatted = $this->formatStatus($status);
			$date = fromMySQLDate($date);
			$duedate = fromMySQLDate($duedate);

			if (!$invoicenum) {
				$invoicenum = $id;
			}

			$invoices[] = array("id" => $id, "invoicenum" => $invoicenum, "userid" => $userid, "clientname" => $clientname, "date" => $date, "duedate" => $duedate, "subtotal" => $subtotal, "credit" => $credit, "total" => $total, "totalformatted" => $totalformatted, "gateway" => $gateway, "paymentmethod" => $paymentmethod, "status" => $status, "statusformatted" => $statusformatted);
		}

		return $invoices;
	}

	private function buildCriteria($criteria) {
		$filters = array();

		if ($criteria['clientid']) {
			$filters[] = "userid=" . (int)$criteria['clientid'];
		}


		if ($criteria['clientname']) {
			$filters[] = "concat(firstname,' ',lastname) LIKE '%" . db_escape_string($criteria['clientname']) . "%'";
		}


		if ($criteria['invoicenum']) {
			$filters[] = "(tblinvoices.id='" . db_escape_string($criteria['invoicenum']) . "' OR tblinvoices.invoicenum='" . db_escape_string($criteria['invoicenum']) . "')";
		}


		if ($criteria['lineitem']) {
			$filters[] = "tblinvoices.id IN (SELECT invoiceid FROM tblinvoiceitems WHERE description LIKE '%" . db_escape_string($criteria['lineitem']) . "%')";
		}


		if ($criteria['paymentmethod']) {
			$filters[] = "tblinvoices.paymentmethod='" . db_escape_string($criteria['paymentmethod']) . "'";
		}


		if ($criteria['invoicedate']) {
			$filters[] = "tblinvoices.date='" . toMySQLDate($criteria['invoicedate']) . "'";
		}


		if ($criteria['duedate']) {
			$filters[] = "tblinvoices.duedate='" . toMySQLDate($criteria['duedate']) . "'";
		}


		if ($criteria['datepaid']) {
			$filters[] = "tblinvoices.datepaid>='" . toMySQLDate($criteria['datepaid']) . "' AND tblinvoices.datepaid<='" . toMySQLDate($criteria['datepaid']) . "235959'";
		}


		if ($criteria['totalfrom']) {
			$filters[] = "tblinvoices.total>='" . db_escape_string($criteria['totalfrom']) . "'";
		}


		if ($criteria['totalto']) {
			$filters[] = "tblinvoices.total<='" . db_escape_string($criteria['totalto']) . "'";
		}


		if ($criteria['status']) {
			if ($criteria['status'] == "Overdue") {
				$filters[] = "tblinvoices.status='Unpaid' AND tblinvoices.duedate<'" . date("Ymd") . "'";
			}
			else {
				$filters[] = "tblinvoices.status='" . db_escape_string($criteria['status']) . "'";
			}
		}

		return $filters;
	}

	public function formatStatus($status) {
		if (defined("ADMINAREA")) {
			global $aInt;

			if ($status == "Unpaid") {
				$status = "<span class=\"textred\">" . $aInt->lang("status", "unpaid") . "</span>";
			}
			else {
				if ($status == "Paid") {
					$status = "<span class=\"textgreen\">" . $aInt->lang("status", "paid") . "</span>";
				}
				else {
					if ($status == "Cancelled") {
						$status = "<span class=\"textgrey\">" . $aInt->lang("status", "cancelled") . "</span>";
					}
					else {
						if ($status == "Refunded") {
							$status = "<span class=\"textblack\">" . $aInt->lang("status", "refunded") . "</span>";
						}
						else {
							if ($status == "Collections") {
								$status = "<span class=\"textgold\">" . $aInt->lang("status", "collections") . "</span>";
							}
							else {
								$status = "Unrecognised";
							}
						}
					}
				}
			}
		}
		else {
			global $_LANG;

			if ($status == "Unpaid") {
				$status = "<span class=\"textred\">" . $_LANG['invoicesunpaid'] . "</span>";
			}
			else {
				if ($status == "Paid") {
					$status = "<span class=\"textgreen\">" . $_LANG['invoicespaid'] . "</span>";
				}
				else {
					if ($status == "Cancelled") {
						$status = "<span class=\"textgrey\">" . $_LANG['invoicescancelled'] . "</span>";
					}
					else {
						if ($status == "Refunded") {
							$status = "<span class=\"textblack\">" . $_LANG['invoicesrefunded'] . "</span>";
						}
						else {
							if ($status == "Collections") {
								$status = "<span class=\"textgold\">" . $_LANG['invoicescollections'] . "</span>";
							}
							else {
								$status = "Unrecognised";
							}
						}
					}
				}
			}
		}

		return $status;
	}

	public function getInvoiceTotals() {
		global $currency;

		$invoicesummary = array();
		$result = full_query("SELECT currency,COUNT(tblinvoices.id),SUM(total) FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid WHERE tblinvoices.status='Paid' GROUP BY tblclients.currency");

		while ($data = mysql_fetch_array($result)) {
			$invoicesummary[$data[0]]['paid'] = $data[2];
		}

		$result = full_query("SELECT currency,COUNT(tblinvoices.id),SUM(total)-COALESCE(SUM((SELECT SUM(amountin) FROM tblaccounts WHERE tblaccounts.invoiceid=tblinvoices.id)),0) FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid WHERE tblinvoices.status='Unpaid' AND tblinvoices.duedate>='" . date("Ymd") . "' GROUP BY tblclients.currency");

		while ($data = mysql_fetch_array($result)) {
			$invoicesummary[$data[0]]['unpaid'] = $data[2];
		}

		$result = full_query("SELECT currency,COUNT(tblinvoices.id),SUM(total)-COALESCE(SUM((SELECT SUM(amountin) FROM tblaccounts WHERE tblaccounts.invoiceid=tblinvoices.id)),0) FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid WHERE tblinvoices.status='Unpaid' AND tblinvoices.duedate<'" . date("Ymd") . "' GROUP BY tblclients.currency");

		while ($data = mysql_fetch_array($result)) {
			$invoicesummary[$data[0]]['overdue'] = $data[2];
		}

		$totals = array();
		foreach ($invoicesummary as $currency => $vals) {
			$currency = getCurrency("", $currency);

			if (!isset($vals['paid'])) {
				$vals['paid'] = 0;
			}


			if (!isset($vals['unpaid'])) {
				$vals['unpaid'] = 0;
			}


			if (!isset($vals['overdue'])) {
				$vals['overdue'] = 0;
			}

			$paid = formatCurrency($vals['paid']);
			$unpaid = formatCurrency($vals['unpaid']);
			$overdue = formatCurrency($vals['overdue']);
			$totals[] = array("currencycode" => $currency['code'], "paid" => $paid, "unpaid" => $unpaid, "overdue" => $overdue);
		}

		return $totals;
	}
}

?>