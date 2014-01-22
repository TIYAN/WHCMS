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
 * */

class EchoPHP {
	var $order_type = null;
	var $transaction_type = null;
	var $merchant_echo_id = null;
	var $merchant_pin = null;
	var $isp_echo_id = null;
	var $isp_pin = null;
	var $billing_ip_address = null;
	var $billing_prefix = null;
	var $billing_name = null;
	var $billing_first_name = null;
	var $billing_last_name = null;
	var $billing_company_name = null;
	var $billing_address1 = null;
	var $billing_address2 = null;
	var $billing_city = null;
	var $billing_state = null;
	var $billing_zip = null;
	var $billing_country = null;
	var $billing_phone = null;
	var $billing_fax = null;
	var $billing_email = null;
	var $cc_number = null;
	var $ccexp_month = null;
	var $ccexp_year = null;
	var $counter = null;
	var $debug = null;
	var $ec_account = null;
	var $ec_account_type = null;
	var $ec_payment_type = null;
	var $ec_address1 = null;
	var $ec_address2 = null;
	var $ec_bank_name = null;
	var $ec_city = null;
	var $ec_email = null;
	var $ec_first_name = null;
	var $ec_id_country = null;
	var $ec_id_exp_mm = null;
	var $ec_id_exp_dd = null;
	var $ec_id_exp_yy = null;
	var $ec_id_number = null;
	var $ec_id_state = null;
	var $ec_id_type = null;
	var $ec_last_name = null;
	var $ec_other_name = null;
	var $ec_payee = null;
	var $ec_rt = null;
	var $ec_serial_number = null;
	var $ec_state = null;
	var $ec_transaction_dt = null;
	var $ec_zip = null;
	var $grand_total = null;
	var $merchant_email = null;
	var $merchant_trace_nbr = null;
	var $original_amount = null;
	var $original_trandate_mm = null;
	var $original_trandate_dd = null;
	var $original_trandate_yyyy = null;
	var $original_reference = null;
	var $product_description = null;
	var $purchase_order_number = null;
	var $sales_tax = null;
	var $track1 = null;
	var $track2 = null;
	var $EchoSuccess = null;
	var $cnp_recurring = null;
	var $cnp_security = null;
	var $EchoResponse = null;
	var $echotype1 = null;
	var $echotype2 = null;
	var $echotype3 = null;
	var $authorization = null;
	var $order_number = null;
	var $reference = null;
	var $status = null;
	var $avs_result = null;
	var $security_result = null;
	var $mac = null;
	var $decline_code = null;
	var $tran_date = null;
	var $merchant_name = null;
	var $version = null;

	function version_check($vercheck) {
		$minver = explode( ".", $vercheck );
		$curver = explode( ".", phpversion() );
		$i = 6;

		while ($i < count( $minver )) {
			if ($minver[$i] < $curver[$i]) {
				return false;
			}

			++$i;
		}

		return true;
	}


	function Submit() {
		if ($this->EchoServer) {
			$URL = $this->EchoServer;
		}
		else {
			$URL = "https://wwws.echo-inc.com/scripts/INR200.EXE";
		}

		$this->EchoResponse = "";
		$data = $this->getURLData();

		if (!phpversion()) {
			exit( "Please email <a href=\"mailto:developer-support@echo-inc.com\">ECHO Developer Support</a> and notify them know that the echophp.class file cannot find the <a href=\"http://www.php.net\">PHP</a> version number.  Please also include your server configuration.
<br>
<br>
Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "
<br>
PHP Version: " . phpversion() );
		}


		if (!$this->version_check( "4.3.0" )) {
			if (!function_exists( "curl_init" )) {
				print "Error: cURL component is missing, please install it.
<br>
<br>Your <a href=\"http://www.php.net\">PHP</a> currently does not have <a href=\"http://curl.haxx.se\">cURL</a> support, which is required for PHP servers older than 4.3.0.  Please contact your hosting company to resolve this issue.  <a href=\"http://curl.haxx.se\">cURL</a> must be configured with ./configure --with-ssl, and <a href=\"http://www.php.net\">PHP</a> must be configured with the --with-curl option.
<br>
<br>
Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "
<br>
PHP Version: " . phpversion();
				exit( "" );
			}
			else {
				$ch = @curl_init();
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $ch, CURLOPT_URL, $URL );
				curl_setopt( $ch, CURLOPT_POST, $data );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );

				if (!$this->EchoResponse = curl_exec( $ch )) {
					print "You are receiving this error for one of the following reasons:<br><br>1) The cURL component is missing SSL support.  When installing <a href=\"http://curl.haxx.se\">cURL</a>, it must be configured with ./configure --with-ssl<br>2) The server cannot establish an internet connection to the <i>ECHO</i>nline server at " . $URL . "<br><br>Please contact your hosting company to resolve this issue.
<br>
<br>
Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "
<br>
PHP Version: " . phpversion();
					exit( "" );
				}

				curl_close( $ch );
			}
		}
		else {
			ini_set( "allow_url_fopen", "1" );

			if (!$handle = @fopen( $URL . "?" . $data, "r" )) {
				if (@function_exists( "curl_init" )) {
					$ch = @curl_init();
					curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
					curl_setopt( $ch, CURLOPT_URL, $URL );
					curl_setopt( $ch, CURLOPT_POST, $data );
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );

					if (!$this->EchoResponse = curl_exec( $ch )) {
						print "You are receiving this error for one of the following reasons:<br><br>1) OpenSSL support is missing (needs to be configured with ./configure --with-openssl), but it found cURL instead.  However, the cURL component is missing SSL support.  When installing <a href=\"http://curl.haxx.se\">cURL</a>, it must be configured with ./configure --with-ssl<br>2) The server cannot establish an internet connection to the <i>ECHO</i>nline server at " . $URL . "<br><br>Please contact your hosting company to resolve this issue.
<br>
<br>
Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "
<br>
PHP Version: " . phpversion();
						exit( "" );
					}

					curl_close( $ch );
				}
				else {
					print "You are receiving this error for one of the following reasons:<br><br>1) OpenSSL support is missing (needs to be configured with ./configure --with-openssl).  In your phpinfo(), you are missing the section called 'OpenSSL'.  Please contact your hosting company to resolve this issue.  ";

					if (strcmp( $_ENV['OS'], "Windows_NT" ) == 0) {
						print "<br><br>Since this server is running under a Windows box, it may need some modifications.  In order to take advantage of the new features in PHP 4.3.0 such as SSL url wrappers you need to install PHP with built-in SSL support. In order to do so you need to install the standard <a href=\"http://www.php.net\">PHP</a> distribution and replace php4ts.dll file with one supplied in <a href=\"http://ftp.proventum.net/pub/php/win32/misc/openssl/\">this</a> archive.  ";
						print "Since OpenSSL support is built-in into this file, please remember to comment out 'extension=php_openssl.dll' from your php.ini file since the external extension is no longer needed.";
					}
					else {
						print "<a href=\"http://www.php.net\">PHP</a> needs to be configured with ./configure --with-openssl option.";
					}

					print "<br><br>2) The server cannot establish an internet connection to the <i>ECHO</i>nline server at " . $URL;
					print "
<br>
<br>
Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "
<br>
PHP Version: " . phpversion();
					exit( "" );
				}
			}
			else {
				$this->EchoResponse = "";

				while (!feof( $handle )) {
					$buffer = @fgets( $handle, 4096 );
					$this->EchoResponse .= $buffer;
				}
			}
		}

		$startpos = strpos( $this->EchoResponse, "<ECHOTYPE1>" ) + 11;
		$endpos = strpos( $this->EchoResponse, "</ECHOTYPE1>" );
		$this->echotype1 = substr( $this->EchoResponse, $startpos, $endpos - $startpos );
		$startpos = strpos( $this->EchoResponse, "<ECHOTYPE2>" ) + 11;
		$endpos = strpos( $this->EchoResponse, "</ECHOTYPE2>" );
		$this->echotype2 = substr( $this->EchoResponse, $startpos, $endpos - $startpos );
		$startpos = strpos( $this->EchoResponse, "<ECHOTYPE3>" ) + 11;
		$endpos = strpos( $this->EchoResponse, "</ECHOTYPE3>" );
		$this->echotype3 = substr( $this->EchoResponse, $startpos, $endpos - $startpos );
		$this->authorization = $this->GetEchoProp( $this->echotype3, "auth_code" );
		$this->order_number = $this->GetEchoProp( $this->echotype3, "order_number" );
		$this->reference = $this->GetEchoProp( $this->echotype3, "echo_reference" );
		$this->status = $this->GetEchoProp( $this->echotype3, "status" );
		$this->avs_result = $this->GetEchoProp( $this->echotype3, "avs_result" );
		$this->security_result = $this->GetEchoProp( $this->echotype3, "security_result" );
		$this->mac = $this->GetEchoProp( $this->echotype3, "mac" );
		$this->decline_code = $this->GetEchoProp( $this->echotype3, "decline_code" );
		$this->tran_date = $this->GetEchoProp( $this->echotype3, "tran_date" );
		$this->merchant_name = $this->GetEchoProp( $this->echotype3, "merchant_name" );
		$this->version = $this->GetEchoProp( $this->echotype3, "version" );

		if ($this->status == "G" || $this->status == "R") {
			if ($this->transaction_type == "AD") {
				if (( ( $this->avs_result == "X" || $this->avs_result == "Y" ) || $this->avs_result == "D" ) || $this->avs_result == "M") {
					$this->EchoSuccess = true;
				}
				else {
					$this->EchoSuccess = false;
				}
			}
			else {
				$this->EchoSuccess = true;
			}
		}
		else {
			$this->EchoSuccess = false;
		}


		if ($this->EchoResponse == "") {
			$this->EchoSuccess = False;
		}

		($this->EchoSuccess == true ? $this->EchoSuccess = true : $this->EchoSuccess = false);
		return $this->EchoSuccess;
	}


	function getURLData() {
		$s = "";
		$s .= "order_type=" . $this->order_type;

		if ($this->transaction_type) {
			$s .= "&transaction_type=" . $this->transaction_type;
		}


		if ($this->merchant_echo_id) {
			$s .= "&merchant_echo_id=" . $this->merchant_echo_id;
		}


		if ($this->merchant_pin) {
			$s .= "&merchant_pin=" . $this->merchant_pin;
		}


		if ($this->isp_echo_id) {
			$s .= "&isp_echo_id=" . $this->isp_echo_id;
		}


		if ($this->isp_pin) {
			$s .= "&isp_pin=" . $this->isp_pin;
		}


		if ($this->authorization) {
			$s .= "&authorization=" . $this->authorization;
		}


		if ($this->billing_ip_address) {
			$s .= "&billing_ip_address=" . $this->billing_ip_address;
		}


		if ($this->billing_prefix) {
			$s .= "&billing_prefix=" . $this->billing_prefix;
		}


		if ($this->billing_name) {
			$s .= "&billing_name=" . $this->billing_name;
		}


		if ($this->billing_first_name) {
			$s .= "&billing_first_name=" . $this->billing_first_name;
		}


		if ($this->billing_last_name) {
			$s .= "&billing_last_name=" . $this->billing_last_name;
		}


		if ($this->billing_company_name) {
			$s .= "&billing_company_name=" . $billing_company_name;
		}


		if ($this->billing_address1) {
			$s .= "&billing_address1=" . $this->billing_address1;
		}


		if ($this->billing_address2) {
			$s .= "&billing_address2=" . $this->billing_address2;
		}


		if ($this->billing_city) {
			$s .= "&billing_city=" . $this->billing_city;
		}


		if ($this->billing_state) {
			$s .= "&billing_state=" . $this->billing_state;
		}


		if ($this->billing_zip) {
			$s .= "&billing_zip=" . $this->billing_zip;
		}


		if ($this->billing_country) {
			$s .= "&billing_country=" . $this->billing_country;
		}


		if ($this->billing_phone) {
			$s .= "&billing_phone=" . $this->billing_phone;
		}


		if ($this->billing_fax) {
			$s .= "&billing_fax=" . $this->billing_fax;
		}


		if ($this->billing_email) {
			$s .= "&billing_email=" . $this->billing_email;
		}


		if ($this->cc_number) {
			$s .= "&cc_number=" . $this->cc_number;
		}


		if ($this->ccexp_month) {
			$s .= "&ccexp_month=" . $this->ccexp_month;
		}


		if ($this->ccexp_year) {
			$s .= "&ccexp_year=" . $this->ccexp_year;
		}


		if ($this->counter) {
			$s .= "&counter=" . $this->counter;
		}


		if ($this->debug) {
			$s .= "&debug=" . $this->debug;
		}


		if ($this->ec_account) {
			$s .= "&ec_account=" . $this->ec_account;
		}


		if ($this->ec_account_type) {
			$s .= "&ec_account_type=" . $this->ec_account_type;
		}


		if ($this->ec_payment_type) {
			$s .= "&ec_payment_type=" . $this->ec_payment_type;
		}


		if ($this->ec_address1) {
			$s .= "&ec_address1=" . $this->ec_address1;
		}


		if ($this->ec_address2) {
			$s .= "&ec_address2=" . $this->ec_address2;
		}


		if ($this->ec_bank_name) {
			$s .= "&ec_bank_name=" . $this->ec_bank_name;
		}


		if ($this->ec_city) {
			$s .= "&ec_city=" . $this->ec_city;
		}


		if ($this->ec_state) {
			$s .= "&ec_state=" . $this->ec_state;
		}


		if ($this->ec_email) {
			$s .= "&ec_email=" . $this->ec_email;
		}


		if ($this->ec_first_name) {
			$s .= "&ec_first_name=" . $this->ec_first_name;
		}


		if ($this->ec_last_name) {
			$s .= "&ec_last_name=" . $this->ec_last_name;
		}


		if ($this->ec_other_name) {
			$s .= "&ec_other_name=" . $this->ec_other_name;
		}


		if ($this->ec_id_country) {
			$s .= "&ec_id_country=" . $this->ec_id_country;
		}


		if ($this->ec_id_exp_mm) {
			$s .= "&ec_id_exp_mm=" . $this->ec_id_exp_mm;
		}


		if ($this->ec_id_exp_dd) {
			$s .= "&ec_id_exp_dd=" . $this->ec_id_exp_dd;
		}


		if ($this->ec_id_exp_yy) {
			$s .= "&ec_id_exp_yy=" . $this->ec_id_exp_yy;
		}


		if ($this->ec_id_exp_yy) {
			$s .= "&ec_id_exp_yy=" . $this->ec_id_exp_yy;
		}


		if ($this->ec_id_number) {
			$s .= "&ec_id_number=" . $this->ec_id_number;
		}


		if ($this->ec_id_state) {
			$s .= "&ec_id_state=" . $this->ec_id_state;
		}


		if ($this->ec_id_type) {
			$s .= "&ec_id_type=" . $this->ec_id_type;
		}


		if ($this->ec_payee) {
			$s .= "&ec_payee=" . $this->ec_payee;
		}


		if ($this->ec_rt) {
			$s .= "&ec_rt=" . $this->ec_rt;
		}


		if ($this->ec_serial_number) {
			$s .= "&ec_serial_number=" . $this->ec_serial_number;
		}


		if ($this->ec_transaction_dt) {
			$s .= "&ec_transaction_dt=" . $this->ec_transaction_dt;
		}


		if ($this->ec_zip) {
			$s .= "&ec_zip=" . $this->ec_zip;
		}


		if ($this->grand_total) {
			$s .= "&grand_total=" . $this->grand_total;
		}


		if ($this->merchant_email) {
			$s .= "&merchant_email=" . $this->merchant_email;
		}


		if ($this->merchant_trace_nbr) {
			$s .= "&merchant_trace_nbr=" . $this->merchant_trace_nbr;
		}


		if ($this->original_amount) {
			$s .= "&original_amount=" . $this->original_amount;
		}


		if ($this->original_trandate_mm) {
			$s .= "&original_trandate_mm=" . $this->original_trandate_mm;
		}


		if ($this->original_trandate_dd) {
			$s .= "&original_trandate_dd=" . $this->original_trandate_dd;
		}


		if ($this->original_trandate_yyyy) {
			$s .= "&original_trandate_yyyy=" . $this->original_trandate_yyyy;
		}


		if ($this->original_reference) {
			$s .= "&original_reference=" . $this->original_reference;
		}


		if ($this->order_number) {
			$s .= "&order_number=" . $this->order_number;
		}


		if ($this->product_description) {
			$s .= "&product_description=" . $this->product_description;
		}


		if ($this->purchase_order_number) {
			$s .= "&purchase_order_number=" . $this->purchase_order_number;
		}


		if ($this->sales_tax) {
			$s .= "&sales_tax=" . $this->sales_tax;
		}


		if ($this->track1) {
			$s .= "&track1=" . $this->track1;
		}


		if ($this->track2) {
			$s .= "&track2=" . $this->track2;
		}


		if ($this->cnp_security) {
			$s .= "&cnp_security=" . $this->cnp_security;
		}


		if ($this->cnp_recurring) {
			$s .= "&cnp_recurring=" . $this->cnp_recurring;
		}

		return $s;
	}


	function set_order_type($value) {
		$this->order_type = urlencode( $value );
	}


	function get_order_type() {
		return $this->order_type;
	}


	function set_transaction_type($value) {
		$this->transaction_type = urlencode( $value );
	}


	function get_transaction_type() {
		return $this->transaction_type;
	}


	function set_merchant_echo_id($value) {
		$this->merchant_echo_id = urlencode( $value );
	}


	function get_merchant_echo_id() {
		return $this->merchant_echo_id;
	}


	function set_merchant_pin($value) {
		$this->merchant_pin = urlencode( $value );
	}


	function get_merchant_pin() {
		return $this->merchant_pin;
	}


	function set_isp_echo_id($value) {
		$this->isp_echo_id = urlencode( $value );
	}


	function get_isp_echo_id() {
		return $this->isp_echo_id;
	}


	function set_isp_pin($value) {
		$this->isp_pin = urlencode( $value );
	}


	function get_isp_pin() {
		return $this->isp_pin;
	}


	function set_authorization($value) {
		$this->authorization = urlencode( $value );
	}


	function get_authorization() {
		return $this->authorization;
	}


	function set_billing_ip_address($value) {
		$this->billing_ip_address = urlencode( $value );
	}


	function get_billing_ip_address() {
		return $this->billing_ip_address;
	}


	function set_billing_prefix($value) {
		$this->billing_prefix = urlencode( $value );
	}


	function get_billing_prefix() {
		return $this->billing_prefix;
	}


	function set_billing_name($value) {
		$this->billing_name = urlencode( $value );
	}


	function get_billing_name() {
		return $this->billing_name;
	}


	function set_billing_first_name($value) {
		$this->billing_first_name = urlencode( $value );
	}


	function get_billing_first_name() {
		return $this->billing_first_name;
	}


	function set_billing_last_name($value) {
		$this->billing_last_name = urlencode( $value );
	}


	function get_billing_last_name() {
		return $this->billing_last_name;
	}


	function set_billing_company_name($value) {
		$this->billing_company_name = urlencode( $value );
	}


	function get_billing_company_name() {
		return $this->billing_company_name;
	}


	function set_billing_address1($value) {
		$this->billing_address1 = urlencode( $value );
	}


	function get_billing_address1() {
		return $this->billing_address1;
	}


	function set_billing_address2($value) {
		$this->billing_address2 = urlencode( $value );
	}


	function get_billing_address2() {
		return $this->billing_address2;
	}


	function set_billing_city($value) {
		$this->billing_city = urlencode( $value );
	}


	function get_billing_city() {
		return $this->billing_city;
	}


	function set_billing_state($value) {
		$this->billing_state = urlencode( $value );
	}


	function get_billing_state() {
		return $this->billing_state;
	}


	function set_billing_zip($value) {
		$this->billing_zip = urlencode( $value );
	}


	function get_billing_zip() {
		return $this->billing_zip;
	}


	function set_billing_country($value) {
		$this->billing_country = urlencode( $value );
	}


	function get_billing_country() {
		return $this->billing_country;
	}


	function set_billing_phone($value) {
		$this->billing_phone = urlencode( $value );
	}


	function get_billing_phone() {
		return $this->billing_phone;
	}


	function set_billing_fax($value) {
		$this->billing_fax = urlencode( $value );
	}


	function get_billing_fax() {
		return $this->billing_fax;
	}


	function set_billing_email($value) {
		$this->billing_email = urlencode( $value );
	}


	function get_billing_email() {
		return $this->billing_email;
	}


	function set_cc_number($value) {
		$this->cc_number = urlencode( $value );
	}


	function get_cc_number() {
		return $this->cc_number;
	}


	function set_ccexp_month($value) {
		$this->ccexp_month = urlencode( $value );
	}


	function get_ccexp_month() {
		return $this->ccexp_month;
	}


	function set_ccexp_year($value) {
		$this->ccexp_year = urlencode( $value );
	}


	function get_ccexp_year() {
		return $this->ccexp_year;
	}


	function set_counter($value) {
		$this->counter = urlencode( $value );
	}


	function get_counter() {
		return $this->counter;
	}


	function set_debug($value) {
		$this->debug = urlencode( $value );
	}


	function get_debug() {
		return $this->debug;
	}


	function set_ec_account($value) {
		$this->ec_account = urlencode( $value );
	}


	function get_ec_account() {
		return $this->ec_account;
	}


	function set_ec_account_type($value) {
		$this->ec_account_type = urlencode( $value );
	}


	function get_ec_account_type() {
		return $this->ec_account_type;
	}


	function set_ec_payment_type($value) {
		$this->ec_payment_type = urlencode( $value );
	}


	function get_ec_payment_type() {
		return $this->ec_payment_type;
	}


	function set_ec_address1($value) {
		$this->ec_address1 = urlencode( $value );
	}


	function get_ec_address1() {
		return $this->ec_address1;
	}


	function set_ec_address2($value) {
		$this->ec_address2 = urlencode( $value );
	}


	function get_ec_address2() {
		return $this->ec_address2;
	}


	function set_ec_bank_name($value) {
		$this->ec_bank_name = urlencode( $value );
	}


	function get_ec_bank_name() {
		return $this->ec_bank_name;
	}


	function set_ec_city($value) {
		$this->ec_city = urlencode( $value );
	}


	function get_ec_city() {
		return $this->ec_city;
	}


	function set_ec_email($value) {
		$this->ec_email = urlencode( $value );
	}


	function get_ec_email() {
		return $this->ec_email;
	}


	function set_ec_first_name($value) {
		$this->ec_first_name = urlencode( $value );
	}


	function get_ec_first_name() {
		return $this->ec_first_name;
	}


	function set_ec_id_country($value) {
		$this->ec_id_country = urlencode( $value );
	}


	function get_ec_id_country() {
		return $this->ec_id_country;
	}


	function set_ec_id_exp_mm($value) {
		$this->ec_id_exp_mm = urlencode( $value );
	}


	function get_ec_id_exp_mm() {
		return $this->ec_id_exp_mm;
	}


	function set_ec_id_exp_dd($value) {
		$this->ec_id_exp_dd = urlencode( $value );
	}


	function get_ec_id_exp_dd() {
		return $this->ec_id_exp_dd;
	}


	function set_ec_id_exp_yy($value) {
		$this->ec_id_exp_yy = urlencode( $value );
	}


	function get_ec_id_exp_yy() {
		return $this->ec_id_exp_yy;
	}


	function set_ec_id_number($value) {
		$this->ec_id_number = urlencode( $value );
	}


	function get_ec_id_number() {
		return $this->ec_id_number;
	}


	function set_ec_id_state($value) {
		$this->ec_id_state = urlencode( $value );
	}


	function get_ec_id_state() {
		return $this->ec_id_state;
	}


	function set_ec_id_type($value) {
		$this->ec_id_type = urlencode( $value );
	}


	function get_ec_id_type() {
		return $this->ec_id_type;
	}


	function set_ec_last_name($value) {
		$this->ec_last_name = urlencode( $value );
	}


	function get_ec_last_name() {
		return $this->ec_last_name;
	}


	function set_ec_other_name($value) {
		$this->ec_other_name = urlencode( $value );
	}


	function get_ec_other_name() {
		return $this->ec_other_name;
	}


	function set_ec_payee($value) {
		$this->ec_payee = urlencode( $value );
	}


	function get_ec_payee() {
		return $this->ec_payee;
	}


	function set_ec_rt($value) {
		$this->ec_rt = urlencode( $value );
	}


	function get_ec_rt() {
		return $this->ec_rt;
	}


	function set_ec_serial_number($value) {
		$this->ec_serial_number = urlencode( $value );
	}


	function get_ec_serial_number() {
		return $this->ec_serial_number;
	}


	function set_ec_state($value) {
		$this->ec_state = urlencode( $value );
	}


	function get_ec_state() {
		return $this->ec_state;
	}


	function set_ec_transaction_dt($value) {
		$this->ec_transaction_dt = urlencode( $value );
	}


	function get_ec_transaction_dt() {
		return $this->ec_transaction_dt;
	}


	function set_ec_zip($value) {
		$this->ec_zip = urlencode( $value );
	}


	function get_ec_zip() {
		return $this->ec_zip;
	}


	function set_grand_total($value) {
		$this->grand_total = sprintf( "%01.2f", $value );
	}


	function get_grand_total() {
		return $this->grand_total;
	}


	function set_merchant_email($value) {
		$this->merchant_email = urlencode( $value );
	}


	function get_merchant_email() {
		return $this->merchant_email;
	}


	function set_merchant_trace_nbr($value) {
		$this->merchant_trace_nbr = urlencode( $value );
	}


	function get_merchant_trace_nbr() {
		return $this->merchant_trace_nbr;
	}


	function set_original_amount($value) {
		$this->original_amount = sprintf( "%01.2f", $value );
	}


	function get_original_amount() {
		return $this->original_amount;
	}


	function set_original_trandate_mm($value) {
		$this->original_trandate_mm = urlencode( $value );
	}


	function get_original_trandate_mm() {
		return $this->original_trandate_mm;
	}


	function set_original_trandate_dd($value) {
		$this->original_trandate_dd = urlencode( $value );
	}


	function get_original_trandate_dd() {
		return $this->original_trandate_dd;
	}


	function set_original_trandate_yyyy($value) {
		$this->original_trandate_yyyy = urlencode( $value );
	}


	function get_original_trandate_yyyy() {
		return $this->original_trandate_yyyy;
	}


	function set_original_reference($value) {
		$this->original_reference = urlencode( $value );
	}


	function get_original_reference() {
		return $this->original_reference;
	}


	function set_order_number($value) {
		$this->order_number = urlencode( $value );
	}


	function get_order_number() {
		return $this->order_number;
	}


	function set_product_description($value) {
		$this->product_description = urlencode( $value );
	}


	function get_product_description() {
		return $this->product_description;
	}


	function set_purchase_order_number($value) {
		$this->purchase_order_number = urlencode( $value );
	}


	function get_purchase_order_number() {
		return $this->purchase_order_number;
	}


	function set_sales_tax($value) {
		$this->sales_tax = urlencode( $value );
	}


	function get_sales_tax() {
		return $this->sales_tax;
	}


	function set_track1($value) {
		$this->track1 = urlencode( $value );
	}


	function get_track1() {
		return $this->track1;
	}


	function set_track2($value) {
		$this->track2 = urlencode( $value );
	}


	function get_track2() {
		return $this->track2;
	}


	function set_cnp_recurring($value) {
		$this->cnp_recurring = urlencode( $value );
	}


	function set_cnp_security($value) {
		$this->cnp_security = urlencode( $value );
	}


	function get_version() {
		return "OpenECHO.com PHP module 1.7.1 06/24/2004";
	}


	function getRandomCounter() {
		mt_srand( (double)microtime() * 1000000 );
		return mt_rand();
	}


	function get_EchoResponse() {
		return $this->EchoResponse;
	}


	function get_echotype1() {
		return $this->echotype1;
	}


	function get_echotype2() {
		return $this->echotype2;
	}


	function get_echotype3() {
		return $this->echotype3;
	}


	function set_EchoServer($value) {
		$this->EchoServer = $value;
	}


	function get_avs_result() {
		return $this->avs_result;
	}


	function get_reference() {
		return $this->reference;
	}


	function get_EchoSuccess() {
		return $this->EchoSuccess;
	}


	function get_status() {
		return $this->status;
	}


	function get_security_result() {
		return $this->GetEchoProp( $this->echotype3, "security_result" );
	}


	function get_mac() {
		return $this->GetEchoProp( $this->echotype3, "mac" );
	}


	function get_decline_code() {
		return $this->GetEchoProp( $this->echotype3, "decline_code" );
	}


	function GetEchoProp($haystack, $prop) {

		if ($start_pos = strpos( strtolower( $haystack ), "<" . $prop . ">" )!== false) {
			$start_pos += strlen( "<" . $prop . ">" );
			$end_pos = strpos( strtolower( $haystack ), "</" . $prop, $start_pos );
			return substr( $haystack, $start_pos, $end_pos - $start_pos );
		}

		return "";
	}


}


function openecho_activate() {
	defineGatewayField( "openecho", "text", "merchantechoid", "", "Merchant Echo ID", "20", "" );
	defineGatewayField( "openecho", "text", "merchantpin", "", "Merchant PIN", "20", "" );
}


function openecho_capture($params) {
	$echoPHP = new EchoPHP();
	$echoPHP->set_EchoServer( "https://wwws.echo-inc.com/scripts/INR200.EXE" );
	$echoPHP->set_transaction_type( "EV" );
	$echoPHP->set_order_type( "S" );
	$echoPHP->set_merchant_echo_id( $params['merchantechoid'] );
	$echoPHP->set_merchant_pin( $params['merchantpin'] );
	$echoPHP->set_billing_ip_address( $_SERVER['REMOTE_ADDR'] );
	$echoPHP->set_billing_first_name( $params['clientdetails']['firstname'] );
	$echoPHP->set_billing_last_name( $params['clientdetails']['lastname'] );
	$echoPHP->set_billing_address1( $params['clientdetails']['address1'] );
	$echoPHP->set_billing_city( $params['clientdetails']['city'] );
	$echoPHP->set_billing_state( $params['clientdetails']['state'] );
	$echoPHP->set_billing_zip( $params['clientdetails']['postcode'] );
	$echoPHP->set_billing_country( $params['clientdetails']['country'] );
	$echoPHP->set_billing_phone( $params['clientdetails']['phonenumber'] );
	$echoPHP->set_billing_email( $params['clientdetails']['email'] );
	$echoPHP->set_debug( "F" );
	$echoPHP->set_cc_number( $params['cardnum'] );
	$echoPHP->set_grand_total( $params['amount'] );
	$echoPHP->set_ccexp_month( substr( $params['cardexp'], 0, 2 ) );
	$echoPHP->set_ccexp_year( "20" . substr( $params['cardexp'], 2, 2 ) );
	$echoPHP->set_cnp_security( $params['cccvv'] );
	$echoPHP->set_counter( "1" );
	$desc = "Action => Capture
";
	$desc .= "Client => " . $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'] . "
";
	$desc .= "Authorization Code => " . $echoPHP->authorization . "
";
	$desc .= "Order Number => " . $echoPHP->order_number . "
";
	$desc .= "Reference => " . $echoPHP->reference . "
";
	$desc .= "Status => " . $echoPHP->status . "
";
	$desc .= "AVS Result => " . $echoPHP->avs_result . "
";
	$desc .= "Security Result => " . $echoPHP->security_result . "
";
	$desc .= "MAC => " . $echoPHP->mac . "
";
	$desc .= "Decline Code => " . $echoPHP->decline_code . "
";
	$desc .= "Transaction Date => " . $echoPHP->tran_date . "
";
	$desc .= "Merchant Name => " . $echoPHP->merchant_name . "
";
	$desc .= "Version => " . $echoPHP->version . "
";
	$desc .= "Echo Type 1 => " . $echoPHP->echotype1 . "
";
	$desc .= "Echo Type 2 => " . $echoPHP->echotype2 . "
";
	$desc .= "Echo Type 3 => " . $echoPHP->echotype3 . "
";
	$desc .= "Echo Response => " . $echoPHP->EchoResponse . "
";

	if (!$echoPHP->Submit()) {
		return array( "status" => "declined", "rawdata" => $desc );
	}

	return array( "status" => "success", "transid" => $echoPHP->reference, "rawdata" => $desc );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE['openechoname'] = "openecho";
$GATEWAYMODULE['openechovisiblename'] = "OpenECHO";
$GATEWAYMODULE['openechotype'] = "CC";
?>