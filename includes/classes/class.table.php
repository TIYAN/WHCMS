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

class WHMCS_Table {
	private $fields = array();
	private $labelwidth = "20";

	/**
	 * Constructor of class
	 *
	 * @param int $width The width to apply to the field label columns (defaults to 20%)
	 *
	 * @return WHMCS_Table
	 **/
	public function __construct($width = "20") {
		$this->labelwidth = $width;
		return $this;
	}

	/**
	 * Adds a field to the table
	 *
	 * @param string $name Field label/name
	 * @param string $field Table cell content
	 * @param boolean $fullwidth Set true for full width field (ie. single column)
	 *
	 * @return string Valid HTML Form Element
	 **/
	public function add($name, $field, $fullwidth = false) {
		if ($fullwidth) {
			$fullwidth = true;
		}

		$this->fields[] = array("name" => $name, "field" => $field, "fullwidth" => $fullwidth);
		return $this;
	}

	/**
	 * Builds and returns table output
	 *
	 * @return string Valid HTML Table Element
	 **/
	public function output() {
		$code = "<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\"><tr>";
		$i = 0;
		foreach ($this->fields as $k => $v) {
			$colspan = "";

			if ($v['fullwidth']) {
				$colspan = "3";

				if ($colspan && $i != 0) {
					$code .= "</tr><tr>";
					$i = 0;
				}

				++$i;
			}

			$code .= "<td class=\"fieldlabel\" width=\"" . $this->labelwidth . "%\">" . $v['name'] . "</td><td class=\"fieldarea\"" . ($colspan ? " colspan=\"" . $colspan . "\"" : "") . ">" . $v['field'] . "</td>";
			++$i;

			if ($i == 2) {
				$code .= "</tr><tr>";
				$i = 0;
				continue;
			}
		}

		$code .= "</tr></table>";
		return $code;
	}
}

?>