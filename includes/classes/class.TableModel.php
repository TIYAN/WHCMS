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

class WHMCS_TableModel extends WHMCS_TableQuery {
	protected $pageObj = null;
	protected $queryObj = null;

	public function __construct($obj = null) {
		global $whmcs;

		$this->pageObj = $obj;
		$numrecords = $whmcs->get_config("NumRecordstoDisplay");
		$this->setRecordLimit($numrecords);
		return $this;
	}

	public function _execute($implementationData = null) {
	}

	public function setPageObj($pageObj) {
		$this->pageObj = $pageObj;
	}

	public function getPageObj() {
		return $this->pageObj;
	}

	public function execute($implementationData = null) {
		$results = $this->_execute($implementationData);
		$this->getPageObj()->setData($results);
		return $this;
	}
}

?>