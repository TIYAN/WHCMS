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

class WHMCSChart {
	var $chartcount = 0;

	public function WHMCSChart() {
	}

	public function drawChart($type, $data, $args = array(), $height = "300px", $width = "100%") {
		global $aInt;

		$datafunc = (!is_array($data) ? $data : "");

		if ($datafunc && !function_exists("json_encode")) {
			return "JSON appears to be missing from your PHP build and is required for graphs to function. Please recompile PHP with JSON included and then try again.";
		}


		if ($datafunc && isset($_POST['chartdata'])) {
			if ($_POST['chartdata'] == $datafunc) {
				if (function_exists("chartdata_" . $datafunc)) {
					$chartdata = call_user_func("chartdata_" . $datafunc);
					echo json_encode($chartdata);
					exit();
				}
				else {
					exit("Function Not Found");
				}
			}
		}


		if ($this->chartcount == 0) {
			$aInt->headOutput[] = "<script type=\"text/javascript\" src=\"https://www.google.com/jsapi\"></script>";
		}

		$this->chartcount++;
		$options = array();

		if (!isset($args['legendpos'])) {
			$args['legendpos'] = "top";
		}

		$options[] = "legend: {position: \"" . $args['legendpos'] . "\"}";

		if (isset($args['title'])) {
			$options[] = "title: '" . $args['title'] . "'";
		}


		if (isset($args['xlabel'])) {
			$options[] = "hAxis: {title: \"" . $args['xlabel'] . "\"}";
		}

		$vaxis = array();

		if (isset($args['ylabel'])) {
			$vaxis[] = "title: \"" . $args['ylabel'] . "\"";
		}


		if (isset($args['minyvalue'])) {
			$vaxis[] = "minValue: \"" . $args['minyvalue'] . "\"";
		}


		if (isset($args['maxyvalue'])) {
			$vaxis[] = "maxValue: \"" . $args['maxyvalue'] . "\"";
		}


		if (isset($args['gridlinescount'])) {
			$vaxis[] = "gridlines: {count:" . $args['gridlinescount'] . "}";
		}


		if (isset($args['minorgridlinescount'])) {
			$vaxis[] = "minorGridlines: {color:\"#efefef\",count:" . $args['minorgridlinescount'] . "}";
		}


		if (count($vaxis)) {
			$options[] = "vAxis: {" . implode(",", $vaxis) . "}";
		}


		if ($args['colors']) {
			$colors = $args['colors'];
			$colors = explode(",", $colors);
			foreach ($colors as $i => $color) {
				$colors[$i] = "\"" . $color . "\"";
			}

			$options[] = "colors: [" . implode(",", $colors) . "]";
		}


		if ($args['chartarea']) {
			$chartarea = explode(",", $args['chartarea']);
			$options[] = "chartArea: {left:" . $chartarea[0] . ",top:" . $chartarea[1] . ",width:\"" . $chartarea[2] . "\",height:\"" . $chartarea[3] . "\"}";
		}

		$output = "
  <script type=\"text/javascript\">
      google.load(\"visualization\", \"1\", {packages:[\"" . ($type == "Geo" ? "geochart" : "corechart") . "\"]});
      google.setOnLoadCallback(drawChart" . $this->chartcount . ");
      function drawChart" . $this->chartcount . "() {";

		if ($datafunc) {
			$output .= "
      var jsonData = $.ajax({
          url: \"" . $_SERVER['PHP_SELF'] . "\",
          type: \"POST\",
          data: \"chartdata=" . $datafunc . "\",
          dataType:\"json\",
          async: false
          }).responseText;
";
		}
		else {
			$output .= "
      var jsonData = '" . json_encode($data) . "';
";
		}

		$output .= "
        var data = new google.visualization.DataTable(jsonData);
        var options = { " . implode(",", $options) . " };
        var chart = new google.visualization." . $type . "Chart(document.getElementById(\"chartcont" . $this->chartcount . "\"));
        chart.draw(data,options);
      }
  </script>
  <div id=\"chartcont" . $this->chartcount . "\" style=\"width:" . $width . ";height:" . $height . ";\"><div style=\"padding-top:" . round($height / 2 - 10, 0) . "px;text-align:center;\"><img src=\"images/loading.gif\" /> Loading...</div></div>
";
		$aInt->chartFunctions[] = "drawChart" . $this->chartcount;
		return $output;
	}
}

?>