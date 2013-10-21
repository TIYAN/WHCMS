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

class WHMCS_MobileListTable extends WHMCS_ListTable {
	private $tableheadoutput = "";
	private $sortableTableCount = 0;

	public function addTableHeadOutput($html) {
		$this->tableheadoutput .= $html . " ";
	}

	public function outputTableHeader() {
		global $aInt;

		$page = $this->getPageObj()->getPage();
		$pages = $this->getPageObj()->getTotalPages();
		$numResults = $this->getPageObj()->getNumResults();
		$content = "<div style=\"margin:5px 0 0 5px;float:left;\">" . $this->tableheadoutput . "</div><div style=\"margin:13px 0 0 5px;float:left;\">" . $numResults . " " . $aInt->lang("global", "recordsfound") . ", " . $aInt->lang("global", "page") . " " . $page . " " . $aInt->lang("global", "of") . " " . $pages . "</div>";
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
<table data-role=\"table\" id=\"table-column-toggle\" data-mode=\"columntoggle\" class=\"ui-responsive table-stroke ui-body-d table-stripe\">
  <thead>
    <tr>";
		$columns = $this->getColumns();
		foreach ($columns as $column) {

			if (is_array($column)) {
				$sortableheader = true;
				$columnid = $column[0];
				$columnname = $column[1];
				$width = isset($column[2]) ? $column[2] : "";
				$priority = isset($column[3]) ? $column[3] : "";

				if (!$columnid) {
					$sortableheader = false;
				}
			}
			else {
				$sortableheader = false;
				$columnid = $width = $priority = "";
				$columnname = $column;
			}


			if (!$columnname) {
				$content .= "<th width=\"20\"></th>";
				continue;
			}


			if ($columnname == "checkall") {
				$content .= "<th width=\"20\"><input type=\"checkbox\" id=\"checkall" . $this->sortableTableCount . "\"></th>";
				continue;
			}

			$width = $width ? " width=\"" . $width . "\"" : "";
			$priority = $priority ? " data-priority=\"" . $priority . "\"" : "";
			$content .= "<th" . $width . $priority . ">";

			if ($sortableheader) {
				$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?orderby=" . $columnid . "\" data-ajax=\"false\">";
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
  </thead>
  <tbody>
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

		$content .= "  </tbody>
</table>
";

		if ($this->getMassActionBtns()) {
			$content .= "<div data-role=\"controlgroup\" data-type=\"horizontal\">" . $this->getMassActionBtns() . "</div>
</form>
";
		}

		$this->addOutput($content);
	}

	public function outputTablePagination() {
		global $aInt;

		$prevPage = $this->getPageObj()->getPrevPage();
		$nextPage = $this->getPageObj()->getNextPage();
		$content = "<div class=\"tablepagenav\" data-role=\"controlgroup\" data-type=\"horizontal\">";

		if ($prevPage) {
			$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=" . $prevPage . "&filter=1\" data-role=\"button\" data-icon=\"arrow-l\" data-iconpos=\"left\" data-mini=\"true\">";
			$content .= "Prev Page";
			$content .= "</a>";
		}
		else {
			$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=" . $prevPage . "&filter=1\" data-role=\"button\" data-icon=\"arrow-l\" data-iconpos=\"left\" data-mini=\"true\" class=\"ui-disabled\">";
			$content .= "Prev Page";
			$content .= "</a>";
		}


		if ($nextPage) {
			$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=" . $nextPage . "&filter=1\" data-role=\"button\" data-icon=\"arrow-r\" data-iconpos=\"right\" data-mini=\"true\">";
			$content .= "Next Page";
			$content .= "</a>";
		}
		else {
			$content .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=" . $nextPage . "&filter=1\" data-role=\"button\" data-icon=\"arrow-r\" data-iconpos=\"right\" data-mini=\"true\" class=\"ui-disabled\">";
			$content .= "Next Page";
			$content .= "</a>";
		}

		$content .= "</div>";
		$this->addOutput($content);
	}
}

?>