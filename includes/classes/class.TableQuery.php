<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

class WHMCS_TableQuery {
	protected $recordOffset = 0;
	protected $recordLimit = 25;
	protected $data = array();

	public function getData() {
		return $this->data;
	}

	public function getOne() {
		return isset($this->data[0]) ? $this->data[0] : null;
	}

	public function setRecordLimit($limit) {
		$this->recordLimit = $limit;
		return $this;
	}

	public function getRecordLimit() {
		return $this->recordLimit;
	}

	public function getRecordOffset() {
		$page = $this->getPageObj()->getPage();
		$offset = ($page - 1) * $this->getRecordLimit();
		return $offset;
	}

	public function getQueryLimit() {
		return $this->getRecordOffset() . "," . $this->getRecordLimit();
	}

	public function setData($data = array()) {
		if (!is_array($data)) {
			throw new InvalidArgumentException("Dataset must be an array");
		}

		$this->data = $data;
		return $this;
	}
}

?>