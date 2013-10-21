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

function csv_clean($var) {
	$var = strip_tags($var);
	$var = str_replace(",", "", $var);
	return $var;
}

function csv_output($query) {
	global $fields;

	$result = full_query($query);

	while ($data = mysql_fetch_array($result)) {
		foreach ($fields as $field) {
			echo csv_clean($data[$field]) . ",";
		}

		echo "\r\n";
	}

}

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("CSV Downloads");
header("Pragma: public");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, private");
header("Cache-Control: private", false);
header("Content-Type: application/octet-stream");
header("Content-Transfer-Encoding: binary");
$report = $whmcs->get_req_var("report");
$type = $whmcs->get_req_var("type");
$print = $whmcs->get_req_var("print");
$currencyid = $whmcs->get_req_var("currencyid");
$month = $whmcs->get_req_var("month");
$year = $whmcs->get_req_var("year");

if ($report) {
	require "../includes/reportfunctions.php";
	$chart = new WHMCSChart();
	$currencies = array();
	$result = select_query("tblcurrencies", "", "", "code", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$code = $data['code'];
		$currencies[$id] = $code;

		if (!$currencyid && $data['default']) {
			$currencyid = $id;
		}


		if ($data['default']) {
			$defaultcurrencyid = $id;
		}
	}

	$currency = getCurrency("", $currencyid);
	$months = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	$month = (int)$month;
	$year = (int)$year;

	if (!$month) {
		$month = date("m");
	}


	if (!$year) {
		$year = date("Y");
	}

	$currentmonth = $months[(int)$month];
	$currentyear = $year;
	$month = str_pad($month, 2, "0", STR_PAD_LEFT);
	$gateways = new WHMCS_Gateways();
	$data = $reportdata = $chartsdata = $args = array();
	$report = preg_replace("/[^0-9a-z-_]/i", "", $report);
	$reportfile = "../modules/reports/" . $report . ".php";

	if (file_exists($reportfile)) {
		require $reportfile;
	}
	else {
		exit("Report File Not Found");
	}

	$rows = $trow = array();
	foreach ($reportdata['tableheadings'] as $heading) {
		$trow[] = $heading;
	}

	$rows[] = $trow;

	if ($reportdata['tablevalues']) {
		foreach ($reportdata['tablevalues'] as $values) {
			$trow = array();
			foreach ($values as $value) {

				if (substr($value, 0, 2) == "**") {
					$trow[] = csv_clean(substr($value, 2));
					continue;
				}

				$trow[] = csv_clean($value);
			}

			$rows[] = $trow;
		}
	}

	header("Content-disposition: attachment; filename=" . $report . "_export_" . date("Ymd") . ".csv");
	echo strip_tags($reportdata['title']) . "\r\n";
	foreach ($rows as $row) {
		echo implode(",", $row) . "\r\n";
	}

	return 1;
}


if ($type == "pdfbatch") {
	require ROOTDIR . "/includes/countries.php";
	require ROOTDIR . "/includes/clientfunctions.php";
	require ROOTDIR . "/includes/invoicefunctions.php";
	$whmcs->load_class("tcpdf");
	$result = select_query("tblpaymentgateways", "gateway,value", array("setting" => "name"), "order", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$gatewaysarray[$data['gateway']] = $data['value'];
	}

	$invoice = new WHMCS_Invoice();
	$invoice->pdfCreate($aInt->lang("reports", "pdfbatch") . " " . date("Y-m-d"));
	$orderby = "id";

	if ($sortorder == "Invoice Number") {
		$orderby = "invoicenum";
	}
	else {
		if ($sortorder == "Date Paid") {
			$orderby = "datepaid";
		}
		else {
			if ($sortorder == "Due Date") {
				$orderby = "duedate";
			}
			else {
				if ($sortorder == "Client ID") {
					$orderby = "userid";
				}
				else {
					if ($sortorder == "Client Name") {
						$orderby = "tblclients`.`firstname` ASC,`tblclients`.`lastname";
					}
				}
			}
		}
	}

	$clientWhere = ($userid ? "AND tblinvoices.userid=" . (int)$userid : "");

	if ($filterby == "Date Created") {
		$filterby = "date";
	}
	else {
		if ($filterby == "Due Date") {
			$filterby = "duedate";
		}
		else {
			$filterby = "datepaid";
			$dateto .= " 23:59:59";
		}
	}

	$batchpdfresult = select_query("tblinvoices", "tblinvoices.id", "tblinvoices." . $filterby . ">='" . toMySQLDate($datefrom) . ("' AND tblinvoices." . $filterby . "<='") . toMySQLDate($dateto) . "' AND tblinvoices.status IN ('" . implode("','", $statuses) . "') AND tblinvoices.paymentmethod IN ('" . implode("','", $paymentmethods) . "')" . $clientWhere, $orderby, "ASC", "", "tblclients ON tblclients.id=tblinvoices.userid");
	$numrows = mysql_num_rows($batchpdfresult);

	if (!$numrows) {
		redir("report=pdf_batch&noresults=1", "reports.php");
	}
	else {
		header("Content-Disposition: attachment; filename=\"" . $aInt->lang("reports", "pdfbatch") . " " . date("Y-m-d") . ".pdf\"");
	}


	while ($data = mysql_fetch_array($batchpdfresult)) {
		$invoice->pdfInvoicePage($data['id']);
	}

	$pdfdata = $invoice->pdfOutput();
	echo $pdfdata;
}

?>