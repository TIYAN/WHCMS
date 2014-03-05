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

define("WHMCS", true);
define("ROOTDIR", realpath(dirname(__FILE__) . "/../../"));
include_once ROOTDIR . "/includes/dbfunctions.php";
include_once ROOTDIR . "/includes/functions.php";

if (!defined("WHMCSDBCONNECT")) {
	if (defined("CLIENTAREA")) {
		include_once ROOTDIR . "/includes/clientareafunctions.php";
	}


	if (defined("ADMINAREA") || defined("MOBILEEDITION")) {
		include_once ROOTDIR . "/includes/adminfunctions.php";
	}
}

error_reporting(0);

class WHMCS_Init {
	private $input = array();
	private $last_input = null;
	private $config = array();
	private $clean_variables = array("int" => array(0 => "id", 1 => "userid", 2 => "kbcid", 3 => "invoiceid", 4 => "idkb", 5 => "currency"), "a-z" => array(0 => "systpl", 1 => "carttpl", 2 => "language"));
	public $db_variables = array(0 => "sld", 1 => "tld", 2 => "domains");
	private $license = "";
	private $db_host = "";
	private $db_username = "";
	private $db_password = "";
	private $db_name = "";
	private $db_sqlcharset = "";
	private $cc_hash = "";
	private $templates_compiledir = "";
	private $customadminpath = "";
	public $remote_ip = "";
	private $clientlang = "";
	private $protected_variables = array(0 => "whmcs", 1 => "smtp_debug", 2 => "attachments_dir", 3 => "downloads_dir", 4 => "customadminpath", 5 => "mysql_charset", 6 => "overidephptimelimit", 7 => "orderform", 8 => "smartyvalues", 9 => "usingsupportmodule", 10 => "copyrighttext", 11 => "adminorder", 12 => "revokelocallicense", 13 => "allow_idn_domains", 14 => "templatefile", 15 => "_LANG", 16 => "_ADMINLANG", 17 => "display_errors", 18 => "debug_output", 19 => "mysql_errors", 20 => "moduleparams", 21 => "errormessage");
	private $danger_vars = array(0 => "_GET", 1 => "_POST", 2 => "_REQUEST", 3 => "_SERVER", 4 => "_COOKIE", 5 => "_FILES", 6 => "_ENV", 7 => "GLOBALS");

	/**
	 * Dangerous vars to be blocked from input requests
	 *
	 * @var array
	 */
	public function __construct() {
	}

	/**
	 * Initialisation of class
	 *
	 * @return WHMCS_Init
	 */
	public function init() {
		spl_autoload_register(array($this, "load_class"));
		$_GET = $this->sanitize_input_vars($_GET);
		$_POST = $this->sanitize_input_vars($_POST);
		$_REQUEST = $this->sanitize_input_vars($_REQUEST);
		$_SERVER = $this->sanitize_input_vars($_SERVER);
		$_COOKIE = $this->sanitize_input_vars($_COOKIE);
		foreach ($this->danger_vars as $var) {

			if (isset($_REQUEST[$var]) || isset($_FILES[$var])) {
				exit("Unauthorized request");
				continue;
			}
		}

		$this->load_input();
		$this->clean_input();
		$this->register_globals();

		if (!$this->load_config_file()) {
			exit( "<div style=\"border: 1px dashed #cc0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#cc0000;\"><strong>Welcome to WHMCS 5.2.15 FULL DECODED && NULLED BY MTIMER!</strong><a></a><br>Before you can begin using WHMCS you need to perform the installation procedure. <a href=\"" . (file_exists( "install/install.php" ) ? "" : "../") . "install/install.php\" style=\"color:#000;\">Click here to begin ...</a><form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\" style=\"margin-top:10px;margin-bottom:5px;\"><input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\"><input type=\"hidden\" name=\"hosted_button_id\" value=\"N3T56B5LHAGBS\"><input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif\" border=\"0\" name=\"submit\" alt=\"Donate to get updates lifetime!\" style=\"margin-bottom:-5px;\"><p style=\"display:inline;margin-left:10px;\"> to get v5.2.16 & updates lifetime via email. Be fair and support this project. It doesn't cost much :) ~</p></form></div>" );
		}


		if (!$this->database_connect()) {
			exit("<div style=\"border: 1px dashed #cc0000;font-family:Tahoma;background-color:#FBEEEB;width:100%;padding:10px;color:#cc0000;\"><strong>Critical Error</strong><br>Could not connect to the database</div>");
		}

		$this->sanitize_db_vars();
		global $CONFIG;
		global $PHP_SELF;
		global $remote_ip;

		$PHP_SELF = $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
		$remote_ip = $this->remote_ip = $this->get_user_ip();
		$CONFIG = $this->load_config_vars();

		if ($this->enforce_ip_bans()) {
			redir("", $CONFIG['SystemURL'] . "/banned.php");
		}

		$instanceid = $this->getWHMCSInstanceID();

		if (!$instanceid) {
			$instanceid = $this->createWHMCSInstanceID();
		}

		$session = new WHMCS_Session();
		$session->create($instanceid);
		$token_manager = &getTokenManager($this);

		$token_manager->conditionallySetToken();

		if (isset($_SESSION['Language'])) {
			$this->set_client_language($_SESSION['Language'], 1);
		}


		if (isset($_REQUEST['systpl'])) {
			$_SESSION['Template'] = $_REQUEST['systpl'];
		}


		if (isset($_REQUEST['carttpl'])) {
			$_SESSION['OrderFormTemplate'] = $_REQUEST['carttpl'];
		}

		$this->validate_templates();
		$this->validate_admin_auth();
		$this->validate_client_auth();
		return $this;
	}

	public function load_class($name) {
		if (substr($name, 0, 6) == "WHMCS_") {
			$name = substr($name, 6);
		}

		$name = $this->sanitize("a-zA-Z0-9_", $name);
		$path = ROOTDIR . "/includes/classes/class." . strtolower($name) . ".php";
		$path2 = ROOTDIR . "/includes/classes/class." . $name . ".php";

		if (file_exists($path)) {
			include_once $path;
			return null;
		}


		if (file_exists($path2)) {
			include_once $path2;
		}

	}

	public function load_function($name) {
		$name = $this->sanitize("a-z", $name);

		if (file_exists($path = ROOTDIR . "/includes/" . $name . "functions.php")) {
			include_once $path;
			return null;
		}


		if (file_exists($path = ROOTDIR . "/includes/" . $name . ".php")) {
			include_once $path;
		}

	}

	public function sanitize_db_vars() {
		$needs_db_sanitization = $this->db_variables;
		$data = array_intersect_key($this->input, array_flip($needs_db_sanitization));

		if (count($data)) {
			$data = $this->db_sanitize($data);
			foreach ($data as $key => $value) {
				$this->input[$key] = $value;

				if (isset($_REQUEST[$key])) {
					$_REQUEST[$key] = $value;
				}

				global $$key;

				$$key = $value;
			}
		}

	}

	public function db_sanitize($data) {
		foreach ($data as $k => $v) {

			if (is_array($v)) {
				$data[$k] = $this->db_sanitize($v);
				continue;
			}

			$data[$k] = mysql_real_escape_string(substr($v, 0, 70));
		}

		return $data;
	}

	public function sanitize_input_vars($arr) {
		$cleandata = array();

		if (is_array($arr)) {
			if (isset($arr['sqltype'])) {
				continue;
			}

			foreach ($arr as $key => $val) {

				if (ctype_alnum(str_replace(array("_", "-", ".", " "), "", $key))) {
					if (is_array($val)) {
						$cleandata[$key] = $this->sanitize_input_vars($val);
						continue;
					}

					$val = str_replace(chr(0), "", $val);
					$cleandata[$key] = htmlspecialchars($val, ENT_QUOTES);

					if (@get_magic_quotes_gpc()) {
						$cleandata[$key] = stripslashes($cleandata[$key]);
						continue;
					}

					continue;
				}
			}
		}
		else {
			$arr = str_replace(chr(0), "", $arr);
			$cleandata = htmlspecialchars($arr, ENT_QUOTES);

			if (@get_magic_quotes_gpc()) {
				$cleandata = stripslashes($cleandata);
			}
		}

		return $cleandata;
	}

	/**
	 * The two functions below are used solely as a temporary workaround for local API compatability with $whmcs->get_req_var()
	 */
	public function replace_input($array) {
		$this->last_input = $this->input;
		$this->input = $array;
		return true;
	}

	public function reset_input() {
		if (is_array($this->last_input)) {
			$this->input = $this->last_input;
			return true;
		}

		return false;
	}

	public function get_req_var($k, $k2 = "") {
		if ($k2) {
			return isset($this->input[$k][$k2]) ? $this->input[$k][$k2] : "";
		}

		return isset($this->input[$k]) ? $this->input[$k] : "";
	}

	public function get_req_var_if($e, $key, $fallbackarray) {
		if ($e) {
			$var = $this->get_req_var($key);
		}
		else {
			$var = (array_key_exists($key, $fallbackarray) ? $fallbackarray[$key] : "");
		}

		return $var;
	}

	private function load_input() {
		foreach ($_COOKIE as $k => $v) {
			unset($_REQUEST[$k]);
		}

		foreach ($_REQUEST as $k => $v) {
			$this->input[$k] = $v;
		}

	}

	private function clean_input() {
		foreach ($this->clean_variables as $type => $vars) {
			foreach ($vars as $var) {

				if (isset($this->input[$var])) {
					$this->input[$var] = $this->sanitize($type, $this->input[$var]);
					continue;
				}
			}
		}

		foreach ($this->protected_variables as $var) {

			if (isset($this->input[$var])) {
				unset($this->input[$var]);
			}

			global $$var;

			$$var = "";
		}

	}

	public function sanitize($type, $var) {
		if ($type == "int") {
			$var = (int)$var;
		}
		else {
			if ($type == "a-z") {
				$var = preg_replace("/[^0-9a-z-]/i", "", $var);
			}
			else {
				$var = preg_replace("/[^" . $type . "]/i", "", $var);
			}
		}

		return $var;
	}

	private function register_globals() {
		foreach ($this->input as $k => $v) {

			if (!in_array($k, $this->danger_vars)) {
				global $$k;

				$$k = $v;
				continue;
			}
		}

	}

	private function load_config_file() {
		global $license;
		global $cc_encryption_hash;
		global $templates_compiledir;
		global $attachments_dir;
		global $downloads_dir;
		global $customadminpath;
		global $disable_iconv;
		global $api_access_key;
		global $disable_admin_ticket_page_counts;
		global $disable_clients_list_services_summary;
		global $disable_auto_ticket_refresh;
		global $pleskpacketversion;
		global $smtp_debug;

		$license = $db_host = $db_name = $db_username = $db_password = $mysql_charset = $display_errors = $templates_compiledir = $attachments_dir = $downloads_dir = $customadminpath = $disable_iconv = $overidephptimelimit = $api_access_key = $disable_admin_ticket_page_counts = $disable_clients_list_services_summary = $disable_auto_ticket_refresh = $pleskpacketversion = $smtp_debug = "";

		if (file_exists(ROOTDIR . "/configuration.php")) {
			ob_start();
			require ROOTDIR . "/configuration.php";
			ob_end_clean();
		}
		else {
			return false;
		}


		if (!$db_name || !$license) {
			return false;
		}

		$this->license = $license;
		$this->db_host = $db_host;
		$this->db_username = $db_username;
		$this->db_password = $db_password;
		$this->db_name = $db_name;
		$this->cc_hash = $cc_encryption_hash;

		if ($mysql_charset) {
			$this->db_sqlcharset = $mysql_charset;
		}


		if ($display_errors) {
			$this->display_errors();
		}


		if (!$templates_compiledir || $templates_compiledir == "templates_c/") {
			$templates_compiledir = ROOTDIR . "/templates_c/";
		}


		if (!$attachments_dir) {
			$attachments_dir = ROOTDIR . "/attachments/";
		}


		if (!$downloads_dir) {
			$downloads_dir = ROOTDIR . "/downloads/";
		}


		if (!$customadminpath) {
			$customadminpath = "admin";
		}


		if (!$overidephptimelimit) {
			$overidephptimelimit = 300;
		}

		@set_time_limit($overidephptimelimit);
		$this->templates_compiledir = $templates_compiledir;
		$this->customadminpath = $customadminpath;
		return true;
	}

	public function get_license_key() {
		return $this->license;
	}

	private function database_connect() {
		global $whmcsmysql;

		$whmcsmysql = @mysql_connect($this->db_host, $this->db_username, $this->db_password);
		$selected_db = @mysql_select_db($this->db_name);

		if (!$selected_db) {
			return false;
		}

		full_query("SET SESSION wait_timeout=600");

		if ($this->db_sqlcharset) {
			full_query("SET NAMES '" . db_escape_string($this->db_sqlcharset) . "'");
		}

		return true;
	}

	private function check_ip($ip) {
		if ((!empty($ip) && ip2long($ip) != 0 - 1) && ip2long($ip) != false) {
			$private_ips = array(array("0.0.0.0", "2.255.255.255"), array("10.0.0.0", "10.255.255.255"), array("127.0.0.0", "127.255.255.255"), array("169.254.0.0", "169.254.255.255"), array("172.16.0.0", "172.31.255.255"), array("192.0.2.0", "192.0.2.255"), array("192.168.0.0", "192.168.255.255"), array("255.255.255.0", "255.255.255.255"));
			foreach ($private_ips as $r) {
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);

				if ($min <= ip2long($ip) && ip2long($ip) <= $max) {
					return false;
					continue;
				}
			}

			return true;
		}

		return false;
	}

	public function get_user_ip() {
		$ip_result = "";

		if (function_exists("apache_request_headers")) {
			$headers = apache_request_headers();

			if (array_key_exists("X-Forwarded-For", $headers)) {
				$userip = explode(",", $headers['X-Forwarded-For']);
				$ip = trim($userip[0]);

				if ($this->check_ip($ip)) {
					$ip_result = $ip;
				}
			}
		}


		if (!$ip_result) {
			if (isset($_SERVER['HTTP_CLIENT_IP']) && $this->check_ip($_SERVER['HTTP_CLIENT_IP'])) {
				$ip_result = $_SERVER['HTTP_CLIENT_IP'];
			}
			else {
				$ip_array = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']) : array());

				if (count($ip_array)) {
					$ip = trim($ip_array[count($ip_array) - 1]);

					if ($this->check_ip($ip)) {
						$ip_result = $ip;
					}
				}
			}
		}


		if (!$ip_result) {
			if (isset($_SERVER['HTTP_X_FORWARDED']) && $this->check_ip($_SERVER['HTTP_X_FORWARDED'])) {
				$ip_result = $_SERVER['HTTP_X_FORWARDED'];
			}
			else {
				if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->check_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
					$ip_result = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
				}
				else {
					if (isset($_SERVER['HTTP_FORWARDED_FOR']) && $this->check_ip($_SERVER['HTTP_FORWARDED_FOR'])) {
						$ip_result = $_SERVER['HTTP_FORWARDED_FOR'];
					}
					else {
						if (isset($_SERVER['HTTP_FORWARDED']) && $this->check_ip($_SERVER['HTTP_FORWARDED'])) {
							$ip_result = $_SERVER['HTTP_FORWARDED'];
						}
						else {
							if (isset($_SERVER['REMOTE_ADDR'])) {
								$ip = $_SERVER['REMOTE_ADDR'];

								if (ip2long($ip) != false && ip2long($ip) != 0 - 1) {
									$ip_result = $ip;
								}
								else {
									if ($this->isIPv6($ip)) {
										$ip_result = $ip;
									}
								}
							}
						}
					}
				}
			}
		}

		return $ip_result;
	}

	public function isIPv6($ip) {
		$ip = trim($ip);

		if (0 <= version_compare(phpversion(), "5.2.0")) {
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
				return true;
			}
		}
		else {
			$hexadecPattern = "[0-9a-fA-F]{1,4}";
			$dotDecAddressPattern = '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';

			if (strpos($ip, "::") !== false) {
				$regex = '%^::(' . $hexadecPattern . ':)?' . $dotDecAddressPattern . '/\d+$%';

				if (preg_match($regex, $ip)) {
					return true;
				}

				$dbColonCount = 0;
				$regex = '/^' . $hexadecPattern . '$/';
				$ipArr = explode(":", $ip);
				foreach ($ipArr as $value) {
					$value = trim($value);

					if (strlen($value) < 1) {
						++$dbColonCount;
						continue;
					}


					if (!preg_match($regex, $value)) {
						return false;
					}
				}


				if ($dbColonCount < 2) {
					return true;
				}
			}
			else {
				$regex = '%^(' . $hexadecPattern . ':){6}((' . $hexadecPattern . ':' . $hexadecPattern . ')|(' . $dotDecAddressPattern . '))$%';

				if (preg_match($regex, $ip)) {
					return true;
				}
			}
		}

		return false;
	}

	private function load_config_vars() {
		$CONFIG = array();
		$result = select_query("tblconfiguration", "", "");

		while ($data = @mysql_fetch_array($result)) {
			$setting = $data['setting'];
			$value = $data['value'];
			$CONFIG[$setting] = $value;
		}


		if (isset($CONFIG['DisplayErrors']) && $CONFIG['DisplayErrors']) {
			$this->display_errors();
		}

		header("Content-Type: text/html; charset=" . $CONFIG['Charset']);
		foreach (array("SystemURL", "SystemSSLURL", "Domain") as $v) {
			$CONFIG[$v] = (substr($CONFIG[$v], 0 - 1, 1) == "/" ? substr($CONFIG[$v], 0, 0 - 1) : $CONFIG[$v]);
		}


		if ($CONFIG['SystemURL'] == $CONFIG['SystemSSLURL'] || substr($CONFIG['SystemSSLURL'], 0, 5) != "https") {
			$CONFIG['SystemSSLURL'] = "";
		}

		$this->config = $CONFIG;
		$this->clientlang = $this->validateLanguage($CONFIG['Language']);
		return $CONFIG;
	}

	public function set_config($k, $v) {
		global $CONFIG;

		if (!isset($this->config[$k])) {
			insert_query("tblconfiguration", array("setting" => $k, "value" => trim($v)));
		}
		else {
			update_query("tblconfiguration", array("value" => trim($v)), array("setting" => $k));
		}

		$CONFIG[$k] = $this->config[$k] = $v;
	}

	public function get_config($k) {
		return isset($this->config[$k]) ? $this->config[$k] : "";
	}

	public function get_template_compiledir_name() {
		return $this->templates_compiledir;
	}

	public function check_template_cache_writeable() {
		$dir = $this->get_template_compiledir_name();

		if (!is_writeable($dir)) {
			return false;
		}

		return true;
	}

	public function get_admin_folder_name() {
		if (isValidforPath($this->customadminpath)) {
			return $this->customadminpath;
		}

		return "admin";
	}

	private function enforce_ip_bans() {
		if (substr($_SERVER['PHP_SELF'], 0 - 10, 10) != "banned.php") {
			$result = full_query("DELETE FROM tblbannedips WHERE expires<now()");
			$bannedipcheck = explode(".", $this->remote_ip);
			$remote_ip1 = $bannedipcheck[0] . "." . $bannedipcheck[1] . "." . $bannedipcheck[2] . ".*";
			$remote_ip2 = $bannedipcheck[0] . "." . $bannedipcheck[1] . ".*.*";
			$result = full_query("SELECT * FROM tblbannedips WHERE ip='" . db_escape_string($this->remote_ip) . "' OR ip='" . db_escape_string($remote_ip1) . "' OR ip='" . db_escape_string($remote_ip2) . "' ORDER BY id DESC");
			$data = @mysql_fetch_array($result);

			if ($data['id']) {
				return true;
			}
		}

		return false;
	}

	public function get_filename() {
		$filename = $_SERVER['PHP_SELF'];
		$filename = substr($filename, strrpos($filename, "/"));
		$filename = str_replace(array("/", ".php"), "", $filename);
		return $filename;
	}

	private function validate_client_auth() {
		$haship = ($this->get_config("DisableSessionIPCheck") ? "" : $this->get_user_ip());

		if ((defined("CLIENTAREA") && !isset($_SESSION['uid'])) && isset($_COOKIE['WHMCSUser'])) {
			$cookiedata = explode(":", $_COOKIE['WHMCSUser']);

			if (is_numeric($cookiedata[0])) {
				$data = get_query_vals("tblclients", "id,password", array("id" => (int)$cookiedata[0]));
				$loginhash = sha1($data['id'] . $data['password'] . $haship . substr(sha1($this->get_hash()), 0, 20));
				$cookiehashcompare = sha1($loginhash . $this->get_hash());

				if ($cookiedata[1] == $cookiehashcompare) {
					$_SESSION['uid'] = $data['id'];
					$_SESSION['upw'] = $loginhash;
					$_SESSION['tkval'] = substr(sha1(rand(1000, 9999) . time()), 0, 12);
				}
			}
		}


		if (isset($_SESSION['uid'])) {
			if (!is_numeric($_SESSION['uid'])) {
				session_unset();
				session_destroy();
			}
			else {
				if (!isset($_SESSION['adminid'])) {
					$result = select_query("tblclients", "password", array("id" => $_SESSION['uid']));
					$data = mysql_fetch_array($result);
					$cid = "";

					if (isset($_SESSION['cid']) && is_numeric($_SESSION['cid'])) {
						$cid = $_SESSION['cid'];
						$result = select_query("tblcontacts", "password", array("id" => $_SESSION['cid']));
						$data = mysql_fetch_array($result);
					}


					if ($_SESSION['upw'] != sha1($_SESSION['uid'] . $cid . $data['password'] . $haship . substr(sha1($this->get_hash()), 0, 20))) {
						session_unset();
						session_destroy();
					}
				}
			}


			if (isset($_SESSION['currency'])) {
				unset($_SESSION['currency']);
			}
		}

	}

	private function validate_admin_auth() {
		$auth = new WHMCS_Auth();

		if ($auth->isLoggedIn()) {
			$auth->getInfobyID($_SESSION['adminid']);

			if ($auth->isSessionPWHashValid($this)) {
				return null;
			}

			$auth->destroySession();
			return null;
		}


		if ($auth->isValidRememberMeCookie($this)) {
			$auth->setSessionVars($this);
		}

	}

	public function get_hash() {
		return $this->cc_hash;
	}

	private function display_errors() {
		@ini_set("display_errors", "on");

		if (defined("DEVMODE")) {
			@error_reporting(E_ALL);
			return null;
		}

		@error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
	}

	private function validate_templates() {
		global $CONFIG;

		$systpl = $this->get_config("Template");

		if (isset($_SESSION['Template'])) {
			$systpl = $_SESSION['Template'];
		}

		$systpl = $this->sanitize("a-z", $systpl);

		if ($systpl == "" || !is_dir(ROOTDIR . "/templates/" . $systpl . "/")) {
			$systpl = "default";
		}

		$CONFIG['Template'] = $this->config['Template'] = $systpl;
		$carttpl = $this->get_config("OrderFormTemplate");

		if (isset($_SESSION['OrderFormTemplate'])) {
			$carttpl = $_SESSION['OrderFormTemplate'];
		}

		$carttpl = $this->sanitize("a-z", $carttpl);

		if ($carttpl == "" || !is_dir(ROOTDIR . "/templates/orderforms/" . $carttpl . "/")) {
			$carttpl = "modern";
		}

		$CONFIG['OrderFormTemplate'] = $this->config['OrderFormTemplate'] = $carttpl;
	}

	public function getValidLanguages($admin = "") {
		static $ClientLanguages = array();
		static $AdminLanguages = array();

		$langs = array();

		if ($admin) {
			if (count($AdminLanguages)) {
				return $AdminLanguages;
			}

			$admin = "/" . $this->get_admin_folder_name();
		}
		else {
			if (count($ClientLanguages)) {
				return $ClientLanguages;
			}
		}

		$dirpath = ROOTDIR . $admin . "/lang/";

		if (!is_dir($dirpath)) {
			exit("Language Folder Not Found");
		}

		$dh = opendir($dirpath);

		while (false !== $file = readdir($dh)) {
			if (!is_dir(ROOTDIR . ("/lang/" . $file))) {
				$pieces = explode(".", $file);

				if ($pieces[1] == "php") {
					$langs[] = $pieces[0];
				}
			}
		}

		closedir($dh);
		sort($langs);

		if ($admin) {
			$AdminLanguages = $langs;
		}
		else {
			$ClientLanguages = $langs;
		}

		return $langs;
	}

	public function validateLanguage($lang, $admin = "") {
		$lang = strtolower($lang);
		$lang = $this->sanitize("a-z", $lang);
		$validlangs = $this->getValidLanguages($admin);

		if (!in_array($lang, $validlangs)) {
			if (in_array("english", $validlangs)) {
				$lang = "english";
			}
			else {
				$lang = $validlangs[0];
			}
		}


		if (!$lang) {
			exit("No Valid Language File Found");
		}

		return $lang;
	}

	public function loadLanguage($lang = "", $admin = "") {
		global $_LANG;
		global $_ADMINLANG;

		if (!$lang) {
			$lang = $this->clientlang;
		}

		$lang = $this->validateLanguage($lang, $admin);
		ob_start();

		if ($admin) {
			$admin = "/" . $this->get_admin_folder_name();
		}

		$langfilepath = ROOTDIR . $admin . "/lang/" . $lang . ".php";
		$langfileoverridespath = ROOTDIR . $admin . "/lang/overrides/" . $lang . ".php";

		if ($admin) {
			$_ADMINLANG = array();
		}
		else {
			$_LANG = array();
		}


		if (file_exists($langfilepath)) {
			include $langfilepath;
		}
		else {
			exit("Language File '" . $lang . "' Missing");
		}


		if (file_exists($langfileoverridespath)) {
			include $langfileoverridespath;
		}

		ob_end_clean();
	}

	public function set_client_language($lang, $skip = "") {
		$lang = $this->clientlang = $this->validateLanguage($lang);

		if ($skip) {
			return false;
		}


		if (isset($_SESSION['uid']) && !isset($_SESSION['adminid'])) {
			update_query("tblclients", array("language" => $lang), array("id" => $_SESSION['uid']));
		}

		$_SESSION['Language'] = $lang;
	}

	public function get_client_language() {
		return $this->clientlang;
	}

	public function get_lang($var) {
		global $_LANG;

		return isset($_LANG[$var]) ? $_LANG[$var] : "Missing Language Var " . $var;
	}

	public function in_ssl() {
		return ((array_key_exists("HTTPS", $_SERVER) && $_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ? true : false;
	}

	public function getWHMCSInstanceID() {
		return $this->get_config("InstanceID");
	}

	private function createWHMCSInstanceID() {
		$instanceid = genRandomVal(12);
		$this->set_config("InstanceID", $instanceid);
		return $instanceid;
	}

	public function get_sys_tpl_name() {
		$tpl = $this->get_config("Template");

		if (isValidforPath($tpl)) {
			return $tpl;
		}

		return "default";
	}

	/**
	 * Get current filename
	 *
	 * @return string The filename without extension
	 */
	public function getCurrentFilename() {
		$filename = $_SERVER['PHP_SELF'];
		$filename = substr($filename, strrpos($filename, "/"));
		$filename = str_replace("/", "", $filename);
		$filename = substr($filename, 0, strrpos($filename, "."));
		return $filename;
	}
}

?>