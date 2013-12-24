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

class WHMCS_Cron 
{
	private $incli = false;
    private $debugmode = false;
    private $lasttime = "";
    private $lastmemory = "";
    private $lastaction = "";
    private $log = array( );
    private $emaillog = array( );
    private $emailsublog = array( );
    private $args = array( );
    private $doonly = false;
    private $validactions = array( );
    private $starttime = "";

	public function __construct() {
	}

	public static function init() 
	{
		$obj = new WHMCS_Cron();
		$obj->incli = $obj->isRunningInCLI();
		$obj->validactions = $obj->getValidActions();
		$args = $obj->fetchArgs();

		if (in_array("debug", $args)) {
			$obj->setDebugMode(true);
		}
		else {
			$obj->setDebugMode(false);
		}

		$obj->determineRunMode();
		$obj->starttime = time();
		return $obj;
	}

	public function getValidActions() {
		$validactions = array("updaterates" => "Updating Currency Exchange Rates", "updatepricing" => "Updating Product Pricing for Current Exchange Rates", "invoices" => "Generating Invoices", "latefees" => "Applying Late Fees", "ccprocessing" => "Processing Credit Card Charges", "invoicereminders" => "Processing Invoice Reminder Notices", "domainrenewalnotices" => "Processing Domain Renewal Notices", "suspensions" => "Processing Overdue Suspensions", "terminations" => "Processing Overdue Terminations", "fixedtermterminations" => "Performing Automated Fixed Term Service Terminations", "cancelrequests" => "Processing Cancellation Requests", "closetickets" => "Auto Closing Inactive Tickets", "affcommissions" => "Processing Delayed Affiliate Commissions", "affreports" => "Sending Affiliate Reports", "emailmarketing" => "Processing Email Marketer Rules", "ccexpirynotices" => "Sending Credit Card Expiry Reminders", "usagestats" => "Updating Disk & Bandwidth Usage Stats", "overagesbilling" => "Processing Overage Billing Charges", "clientstatussync" => "Performing Client Status Sync", "backups" => "Database Backup");
		return $validactions;
	}

	public function isRunningInCLI() {
		return php_sapi_name() == "cli" && empty($_SERVER['REMOTE_ADDR']);
	}

	public function fetchArgs() {
		if ($this->incli) {
			$this->args = $_SERVER['argv'];
		}
		else {
			foreach ($this->validactions as $action => $name) {

				if (array_key_exists("skip_" . $action, $_REQUEST)) {
					$this->args[] = "skip_" . $action;
				}


				if (array_key_exists("do_" . $action, $_REQUEST)) {
					$this->args[] = "do_" . $action;
					continue;
				}
			}
		}

		return $this->args;
	}

	public function setDebugMode($state = false) {
		$this->debugmode = ($state ? true : false);

		if ($state) {
			error_reporting(E_ALL ^ E_NOTICE);
			return null;
		}

		error_reporting(0);
	}

	public function determineRunMode() {
		foreach ($this->args as $arg) {

			if (substr($arg, 0, 3) == "do_") {
				$this->doonly = true;
				return true;
			}
		}

		return false;
	}

	public function raiseLimits() {
		@ini_set("memory_limit", "512M");
		@ini_set("max_execution_time", 0);
		@set_time_limit(0);
	}

	public function isScheduled($action) {
		if (!array_key_exists($action, $this->validactions)) {
			return false;
		}

		$this->emailsublog = array();
		$this->lastaction = $action;

		if ($this->doonly) {
			if (in_array("do_" . $action, $this->args)) {
				$this->logAction();
				return true;
			}

			$this->logAction(false, true);
			return false;
		}


		if (in_array("skip_" . $action, $this->args)) {
			$this->logAction(false, true);
			return false;
		}

		$this->logAction();
		return true;
	}

	private function logAction($end = false, $skip = false) {
		$action = $this->validactions[$this->lastaction];
		$prefix = "Starting";

		if ($end) {
			$prefix = "Completed";
		}


		if ($skip) {
			$prefix = "Skipping";
		}

		$this->logActivity($prefix . " " . $action);
		return true;
	}

	public function logActivity($msg, $sub = false) {
		logActivity("Cron Job: " . $msg);

		if ($sub) {
			$msg = " - " . $msg;
		}

		$this->log($msg);
		return true;
	}

	public function logActivityDebug($msg) {
		$this->log($msg, 1);
		return true;
	}

	private function log($msg, $verbose = 0) {
		if ($this->debugmode) {
			microtime();
			$memory = $this->getMemUsage();
			$timediff = round($time - $this->lasttime, 2);
			$memdiff = $time = round($memory - $this->lastmemory, 2);
			$msg .= " (Time: " . $timediff . " Memory: " . $memory . ")";
			$this->lasttime = $time;
			$this->lastmemory = $memory;
		}


		if ($this->incli) {
			echo "" . $msg . "\r\n";
		}


		if (!$verbose) {
			$this->log[] = $msg;
		}

	}

	private function getMemUsage() {
		return round(memory_get_peak_usage() / (1024 * 1024), 2);
	}

	public function logmemusage($line) {
		$this->log("Memory Usage @ Line " . $line . ": " . $this->getMemUsage());
	}

	public function emailLog($msg) {
		$this->emaillog[] = $msg;

		if (count($this->emailsublog)) {
			foreach ($this->emailsublog as $entry) {
				$this->emaillog[] = " - " . $entry;
			}
		}

		$this->emaillog[] = "";
	}

	public function emailLogSub($msg) {
		$this->emailsublog[] = $msg;
		$this->logActivity($msg, true);
	}

	public function emailReport() {
		$cronreport = "Cron Job Report for " . date("l jS F Y @ H:i:s", $this->starttime) . "<br /><br />";
		foreach ($this->emaillog as $log) {
			$cronreport .= $log . "<br />";
		}

		echo $cronreport;
		sendAdminNotification("system", "WHMCS Cron Job Activity", $cronreport);
	}
}

?>