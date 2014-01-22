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

class WHMCS_ListTable {
	private $pagination = true;
    private $columns = array();
    private $rows = array();
    private $output = array();
    private $showmassactionbtnstop = false;
    private $massactionurl = "";
    private $massactionbtns = "";
    private $sortableTableCount = 0;
    private $pageObj;

	public function __construct($obj) {
		$this->pageObj = $obj;
	}

	public function getPageObj() {
		return $this->pageObj;
	}

	public function setPagination($boolean) {
		$this->pagination = $boolean;
	}

	public function isPaginated() {
		return $this->pagination ? true : false;
	}

	public function setMassActionURL($url) {
		$this->massactionurl = $url;
		return true;
	}

	public function getMassActionURL() {
		$url = $this->massactionurl;

		if (!$url) {
			$url = $_SERVER['PHP_SELF'];
		}


		if (strpos($url, "?")) {
			$url .= "&";
		}
		else {
			$url .= "?";
		}

		$url .= "filter=1";
		return $url;
	}

	public function setMassActionBtns($btns) {
		$this->massactionbtns = $btns;
		return true;
	}

	public function getMassActionBtns() {
		return $this->massactionbtns;
	}

	public function setShowMassActionBtnsTop($boolean) {
		$this->showmassactionbtnstop = $boolean;
		return true;
	}

	public function getShowMassActionBtnsTop() {
		return $this->showmassactionbtnstop ? true : false;
	}

	public function setColumns($array) {
		if (!is_array($array)) {
			return false;
		}

		$this->columns = $array;
		$orderbyvals = array();
		foreach ($array as $vals) {

			if (is_array($vals) && $vals[0]) {
				$orderbyvals[] = $vals[0];
				continue;
			}
		}

		$this->getPageObj()->setValidOrderByValues($orderbyvals);
		return true;
	}

	public function getColumns() {
		return $this->columns;
	}

	public function addRow($array) {
		if (!is_array($array)) {
			return false;
		}

		$this->rows[] = $array;
		return true;
	}

	public function getRows() {
		return $this->rows;
	}

	public function outputTableHeader() {
		global $aInt;

		$page = $this->getPageObj()->getPage();
		$pages = $this->getPageObj()->getTotalPages();
		$numResults = $this->getPageObj()->getNumResults();
		$content = "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "?filter=1\">
<table width=\"100%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\"><tr>
<td width=\"50%\" align=\"left\">" . $numResults . " " . $aInt->lang("global", "recordsfound") . ", " . $aInt->lang("global", "page") . " " . $page . " " . $aInt->lang("global", "of") . " " . $pages . "</td>
<td width=\"50%\" align=\"right\">" . $aInt->lang("global", "jumppage") . ": <select name=\"page\" onchange=\"submit()\">";
		$newpage = 1;

		while ($newpage <= $pages) {
			$content .= "<option value=\"" . $newpage . "\"";

			if ($page == $newpage) {
				$content .= " selected";
			}

			$content .= ">" . $newpage . "</option>";
			++$newpage;
		}

		$content .= "</select> <input type=\"submit\" value=\"" . $aInt->lang("global", "go") . "\" class=\"btn-small\" /></td>
</tr></table>
</form>
";
		$this->addOutput($content);
	}

	public function outputTable() {
		global $aInt;

		$orderby = $this->getPageObj()->getOrderBy();
		$sortDirection = $this->getPageObj()->getSortDirection();
		$content = "";

		if ($this->getMassActionURL()) {
			$content .= "<form method=\"post\" action=\"" . $this->getMassActionURL() . "\">";
		}


		if ($this->getShowMassActionBtnsTop()) {
			$content .= "<div style=\"padding-bottom:2px;\">" . $aInt->lang("global", "withselected") . ": " . $this->getMassActionBtns() . "</div>";
		}

		$content .= "
<div class=\"tablebg\">
<table id=\"sortabletbl" . $this->sortableTableCount . "\" class=\"datatable\" width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"3\">
<tr>";
		$columns = $this->getColumns();
		foreach ($columns as $column) {

			if (is_array($column)) {
				$sortableheader = true;
				$columnid = $column[0];
				$columnname = $column[1];
				$width = (isset($column[2]) ? $column[2] : "");

				if (!$columnid) {
					$sortableheader = false;
				}
			}
			else {
				$sortableheader = false;
				$columnid = $width = "";
				$columnname = $column;
			}


			if (!$columnname) {
				$content .= "<th width=\"20\"></th>";
				continue;
			}


			if ($columnname == "checkall") {
				$aInt->internaljquerycode[] = "$(\"#checkall" . $this->sortableTableCount . "\").click(function () {
    $(\"#sortabletbl" . $this->sortableTableCount . " .checkall\").attr(\"checked\",this.checked);
});";
				$content .= "<th width=\"20\"><input type=\"checkbox\" id=\"checkall" . $this->sortableTableCount . "\"></th>";
				continue;
			}

			$width = ($width ? " width=\"" . $width . "\"" : "");
			$content .= "<th" . $width . ">";

			if ($sortableheader) {
				$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?orderby=" . $columnid . "\">";
			}

			$content .= $columnname;

			if ($sortableheader) {
				$content .= "</a>";

				if ($orderby == $columnid) {
					$content .= " <img src=\"images/" . strtolower($sortDirection) . ".gif\" class=\"absmiddle\" />";
				}
			}

			$content .= "</th>";
		}

		$content .= "</tr>
";
		$totalcols = count($columns);
		$rows = $this->getRows();

		if (count($rows)) {
			foreach ($rows as $vals) {

				if ($vals[0] == "dividingline") {
					$content .= "<tr><td colspan=\"" . $totalcols . "\" style=\"background-color:#efefef;\"><div align=\"left\"><b>" . $vals[1] . "</b></div></td></tr>";
					continue;
				}

				$content .= "<tr>";
				foreach ($vals as $val) {
					$content .= "<td>" . $val . "</td>";
				}

				$content .= "</tr>";
			}
		}
		else {
			$content .= "<tr><td colspan=\"" . $totalcols . "\">" . $aInt->lang("global", "norecordsfound") . "</td></tr>";
		}

		$content .= "</table>
</div>";

		if ($this->getMassActionBtns()) {
			$content .= "" . $aInt->lang("global", "withselected") . ": " . $this->getMassActionBtns() . "
</form>
";
		}

		$this->addOutput($content);
	}

	public function outputTablePagination() {
		global $aInt;

		$content = "<p align=\"center\">";
		$prevPage = $this->getPageObj()->getPrevPage();
		$nextPage = $this->getPageObj()->getNextPage();

		if ($prevPage) {
			$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=" . $prevPage . "&filter=1\">";
			$content .= $aInt->lang("global", "previouspage");
			$content .= "</a>";
		}
		else {
			$content .= $aInt->lang("global", "previouspage");
		}

		$content .= " &nbsp ";

		if ($nextPage) {
			$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=" . $nextPage . "&filter=1\">";
			$content .= $aInt->lang("global", "nextpage");
			$content .= "</a> &nbsp ";
		}
		else {
			$content .= $aInt->lang("global", "nextpage");
		}

		$content .= "</p>";
		$this->addOutput($content);
	}

	public function addOutput($content) {
		$this->output[] = $content;
	}

	public function output() {
		if ($this->isPaginated()) {
			$this->outputTableHeader();
		}

		$this->outputTable();

		if ($this->isPaginated()) {
			$this->outputTablePagination();
		}

		return implode("\r\n", $this->output);
	}
}

?>