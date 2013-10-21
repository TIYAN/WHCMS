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

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View Reports");
$aInt->title = "Reports";
$aInt->sidebar = "reports";
$aInt->icon = "reports";
$aInt->requiredFiles(array("reportfunctions"));
$aInt->helplink = "Reports";
$report = $whmcs->get_req_var("report");
$displaygraph = $whmcs->get_req_var("displaygraph");
$print = $whmcs->get_req_var("print");
$currencyid = $whmcs->get_req_var("currencyid");
$month = $whmcs->get_req_var("month");
$year = $whmcs->get_req_var("year");

if ($displaygraph) {
	$displaygraph = preg_replace("/[^0-9a-z-_]/i", "", $displaygraph);
	$graphfile = "../modules/reports/" . $displaygraph . ".php";

	if (file_exists($graphfile)) {
		require $graphfile;
	}
	else {
		exit("Graph File Not Found");
	}

	$graph->createGraph();
	exit();
}


if ($print) {
	echo "<html>
<head>
<title>WHMCompleteSolution - Printer Friendly Report</title>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=";
	echo $CONFIG['Charset'];
	echo "\">
";
	echo "<s";
	echo "cript type=\"text/javascript\" src=\"http://jsapi.u.qiniudn.com/jsapi.js\"></script>
";
	echo "<s";
	echo "tyle>
body,td {
    font-family: Tahoma;
    font-size: 11px;
}
h1,h2 {
    font-size: 16px;
}
a {
    color: #000000;
}
</style>
</head>
<body>
<p><img src=\"";
	echo $CONFIG['LogoURL'];
	echo "\"></p>
";
}
else {
	$text_reports = $graph_reports = array();
	$dh = opendir("../modules/reports/");

	while (false !== $file = readdir($dh)) {
		if ($file != "index.php" && is_file("../modules/reports/" . $file)) {
			$file = str_replace(".php", "", $file);

			if (substr($file, 0, 5) != "graph") {
				$nicename = str_replace("_", " ", $file);
				$nicename = titleCase($nicename);
				$text_reports[$file] = $nicename;
			}
		}
	}

	closedir($dh);
	asort($text_reports);
	$aInt->assign("text_reports", $text_reports);
	$aInt->assign("graph_reports", $graph_reports);
	ob_start();
}


if ($report) {
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
	$requeststr = "?";
	foreach ($_GET as $key => $value) {

		if (!is_array($value)) {
			$requeststr .= $key . "=" . urlencode($value) . "&";
			continue;
		}
	}

	foreach ($_POST as $key => $value) {

		if (is_array($value)) {
			foreach ($value as $k => $v) {
				$requeststr .= $key . "[" . $k . "]=" . urlencode($v) . "&";
			}

			continue;
		}

		$requeststr .= $key . "=" . urlencode($value) . "&";
	}

	$chart = new WHMCSChart();
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


	if (!is_array($reportdata)) {
		exit("$reportdata must be returned as an array");
	}


	if (array_key_exists("title", $reportdata)) {
		echo "<h2>" . $reportdata['title'] . "</h2>";
	}


	if (array_key_exists("description", $reportdata)) {
		echo "<p>" . $reportdata['description'] . "</p>";
	}


	if (array_key_exists("currencyselections", $reportdata)) {
		$currencieslist = "";
		foreach ($currencies as $listid => $listname) {

			if ($currencyid == $listid) {
				$currencieslist .= "<b>";
			}
			else {
				$currencieslist .= "<a href=\"reports.php" . $requeststr . "currencyid=" . $listid . "\">";
			}

			$currencieslist .= $listname . "</b></a> | ";
		}

		echo "<p align=\"center\">Choose Currency: " . substr($currencieslist, 0, 0 - 3) . "</p>";
	}


	if (array_key_exists("headertext", $reportdata)) {
		echo $reportdata['headertext'] . "<br /><br />";
	}


	if (array_key_exists("tableheadings", $reportdata) && is_array($reportdata['tableheadings'])) {
		echo "<table width=100% ";

		if ($print) {
			echo "border=1 cellspacing=0";
		}
		else {
			echo "cellspacing=1";
		}

		echo " bgcolor=\"#cccccc\">";
		echo "<tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold;\">";
		foreach ($reportdata['tableheadings'] as $heading) {
			echo "<td>" . $heading . "</td>";
		}


		if (array_key_exists("drilldown", $reportdata) && is_array($reportdata['drilldown'])) {
			echo "<td>Drill Down</td>";
		}

		echo "</tr>";

		if (array_key_exists("tablesubheadings", $reportdata) && is_array($reportdata['tablesubheadings'])) {
			echo "<tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold;\">";
			foreach ($reportdata['tablesubheadings'] as $heading) {
				echo "<td>" . $heading . "</td>";
			}


			if (is_array($reportdata['drilldown'])) {
				echo "<td>Drill Down</td>";
			}

			echo "</tr>";
		}

		$columncount = count($reportdata['tableheadings']);

		if (array_key_exists("drilldown", $reportdata) && is_array($reportdata['drilldown'])) {
			++$columncount;
		}


		if (array_key_exists("tablevalues", $reportdata) && is_array($reportdata['tablevalues'])) {
			foreach ($reportdata['tablevalues'] as $num => $values) {

				if (isset($values[0]) && $values[0] == "HEADER") {
					echo "<tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold;\">";
					foreach ($values as $k => $value) {

						if (0 < $k) {
							echo "<td>" . $value . "</td>";
							continue;
						}
					}

					echo "</tr>";
					continue;
				}

				$rowbgcolor = "#ffffff";

				if ((isset($values[0]) && strlen($values[0]) == 7) && substr($values[0], 0, 1) == "#") {
					$rowbgcolor = $values[0];
					unset($values[0]);
				}

				echo "<tr bgcolor=\"" . $rowbgcolor . "\" style=\"text-align:center;\">";
				foreach ($values as $value) {

					if (substr($value, 0, 2) == "**") {
						echo "<td bgcolor=\"#efefef\" colspan=\"" . $columncount . "\" align=\"left\">&nbsp;" . substr($value, 2) . "</td>";
						continue;
					}

					echo "<td>" . $value . "</td>";
				}


				if (array_key_exists("drilldown", $reportdata) && is_array($reportdata['drilldown'][$num]['tableheadings'])) {
					echo "<td><a href=\"#\" onclick=\"$('#drilldown" . $num . "').fadeToggle();return false\">Drill Down</a></td>";
				}

				echo "</tr>";

				if (array_key_exists("drilldown", $reportdata) && is_array($reportdata['drilldown'][$num]['tableheadings'])) {
					echo "<tr bgcolor=\"#FFFFCC\" id=\"drilldown" . $num . ("\" style=\"display:none;\"><td colspan=\"" . $columncount . "\" style=\"padding:20px;\">");
					echo "<table width=100% ";

					if ($print == "true") {
						echo "border=1 cellspacing=0";
					}
					else {
						echo "cellspacing=1";
					}

					echo " bgcolor=\"#cccccc\">";
					echo "<tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold;\">";
					foreach ($reportdata['drilldown'][$num]['tableheadings'] as $value) {
						echo "<td>" . $value . "</td>";
					}


					if (!isset($reportdata['drilldown'][$num]['tablevalues'])) {
						echo "<tr bgcolor=\"#ffffff\"><td align=\"center\" colspan=\"" . $columncount . "\">No Records Found</td></tr>";
					}
					else {
						foreach ($reportdata['drilldown'][$num]['tablevalues'] as $num => $values) {
							echo "<tr bgcolor=\"#ffffff\" style=\"text-align:center;\">";
							foreach ($values as $value) {
								echo "<td>" . $value . "</td>";
							}

							echo "</tr>";
						}
					}

					echo "</tr>";
					echo "</table>";
					echo "</td></tr>";
					continue;
				}
			}
		}
		else {
			echo "<tr bgcolor=\"#ffffff\" style=\"text-align:center;\"><td colspan=\"" . $columncount . "\">" . $aInt->lang("reports", "nodata") . "</td></tr>";
		}

		echo "</table>";
	}


	if (array_key_exists("monthspagination", $reportdata) && $reportdata['monthspagination']) {
		$requeststr2 = "?";
		foreach ($_GET as $key => $value) {

			if (!in_array($key, array("month", "year"))) {
				$requeststr2 .= $key . "=" . urlencode($value) . "&";
				continue;
			}
		}

		foreach ($_POST as $key => $value) {

			if (is_array($value)) {
				foreach ($value as $k => $v) {

					if (!in_array($k, array("month", "year"))) {
						$requeststr2 .= $key . "[" . $k . "]=" . urlencode($v) . "&";
						continue;
					}
				}

				continue;
			}


			if (!in_array($key, array("month", "year"))) {
				$requeststr2 .= $key . "=" . urlencode($value) . "&";
				continue;
			}
		}

		echo "<br /><table width=90% align=center><tr><td>";

		if ($month == "1") {
			echo "<a href=\"" . $PHP_SELF . $requeststr2 . "month=12&year=" . ($year - 1) . "\">&laquo; December " . ($year - 1) . "</a>";
		}
		else {
			echo "<a href=\"" . $PHP_SELF . $requeststr2 . "month=" . ($month - 1) . "&year=" . $year . "\">&laquo; " . $months[$month - 1] . (" " . $year . "</a>");
		}

		echo "</td><td align=right>";

		if ($year . str_pad($month, 2, "0", STR_PAD_LEFT) < date("Ym")) {
			if ($month == "12") {
				echo "<a href=\"" . $PHP_SELF . $requeststr2 . "month=1&year=" . ($year + 1) . "\">January " . ($year + 1) . " &raquo;</a>";
			}
			else {
				echo "<a href=\"" . $PHP_SELF . $requeststr2 . "month=" . ($month + 1) . "&year=" . $year . "\">" . $months[$month + 1] . (" " . $year . " &raquo;</a>");
			}
		}

		echo "</td></tr></table>";
	}


	if (array_key_exists("yearspagination", $reportdata) && $reportdata['yearspagination']) {
		echo "<br /><table width=\"90%\" align=\"center\"><tr><td><a href=\"" . $_SERVER['PHP_SELF'] . $requeststr . "year=" . ($year - 1) . "\">&laquo; " . ($year - 1) . "</a></td><td align=\"right\">";

		if ($year + 1 <= date("Y")) {
			echo "<a href=\"" . $_SERVER['PHP_SELF'] . $requeststr . "year=" . ($year + 1) . "\">" . ($year + 1) . " &raquo;</a>";
		}

		echo "</td></tr></table>";
	}


	if (is_array($data) && array_key_exists("footertext", $data)) {
		echo "<p>" . $data['footertext'] . "</p>";
	}


	if (array_key_exists("footertext", $reportdata)) {
		echo $reportdata['footertext'];
	}
}
else {
	echo "<p>" . $aInt->lang("reports", "description") . "</p>";
	$reports = array("General" => array("daily_performance", "disk_usage_summary", "monthly_orders", "product_suspensions", "promotions_usage", "", ""), "Billing" => array("aging_invoices", "credits_reviewer", "direct_debit_processing", "sales_tax_liability", "", ""), "Income" => array("annual_income_report", "income_forecast", "income_by_product", "monthly_transactions", "sales_tax_liability", "server_revenue_forecasts", ""), "Clients" => array("new_customers", "client_sources", "client_statement", "clients_by_country", "top_10_clients_by_income", "affiliates_overview", "", "", ""), "Support" => array("support_ticket_replies", "ticket_feedback_scores", "ticket_feedback_comments", "ticket_ratings_reviewer", "ticket_tags", ""), "Exports" => array("clients", "domains", "invoices", "services", "transactions", "pdf_batch", "", "", ""));
	foreach ($reports as $type => $reports_array) {
		echo "<h2 align=\"center\">" . $type . "</h2>";
		$reps = array();
		$btnclass = "";

		if ($type == "General") {
			$btnclass = "btn-info";
		}


		if ($type == "Exports") {
			$btnclass = "btn-inverse";
		}

		foreach ($reports_array as $report_name) {

			if (isset($text_reports[$report_name])) {
				$reps[] = "<input type=\"button\" value=\"" . $text_reports[$report_name] . "\" class=\"btn " . $btnclass . "\" onclick=\"window.location='reports.php?report=" . $report_name . "'\" />";
				unset($text_reports[$report_name]);
				continue;
			}
		}

		echo "<div align=\"center\" style=\"padding:0 0 10px 0;\">" . implode(" ", $reps) . "</div>";
	}


	if (count($text_reports)) {
		echo "<h2 align=\"center\">Other</h2>";
		$reps = array();
		foreach ($text_reports as $report_name => $discard) {

			if (isset($text_reports[$report_name])) {
				$reps[] = "<input type=\"button\" value=\"" . $text_reports[$report_name] . "\" class=\"btn\" onclick=\"window.location='reports.php?report=" . $report_name . "'\" />";
				continue;
			}
		}

		echo "<div align=\"center\" style=\"padding:0 0 10px 0;\">" . implode(" ", $reps) . "</div>";
	}
}


if ($report) {
	echo "<p>" . $aInt->lang("reports", "generatedon") . " " . fromMySQLDate(date("Y-m-d H:i:s"), "time") . "</p>
<p align=\"center\">";

	if ($print == "true") {
		echo "<a href=\"javascript:window.close()\">" . $aInt->lang("reports", "closewindow") . "</a>";
	}
	else {
		echo "<strong>" . $aInt->lang("reports", "tools") . "</strong> &nbsp;&nbsp;&nbsp; <a href=\"csvdownload.php" . $requeststr . "\"><img src=\"images/icons/csvexports.png\" align=\"absmiddle\" border=\"0\" /> " . $aInt->lang("reports", "exportcsv") . "</a> &nbsp;&nbsp;&nbsp; <a href=\"" . $_SERVER['PHP_SELF'] . $requeststr . "print=true\" target=\"_blank\"><img src=\"images/icons/print.png\" align=\"absmiddle\" border=\"0\" /> " . $aInt->lang("reports", "printableversion") . "</a>";
	}

	echo "</p>";
}


if ($print) {
	echo "
</body>
</html>";
	return 1;
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>