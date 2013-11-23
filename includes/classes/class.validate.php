<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 **/

class WHMCS_Validate 
{
	protected $optional_fields = array();
	protected $validated = array();
	protected $errors = array();
	protected $errors_msgs = array();

	public function __construct() {
	}

	/**
	 * Specify optional fields to override required checks
	 *
	 * @param mixed $optionalfields Accepts either an array or comma separated list of optional fields
	 *
	 * @return WHMCS_Validate
	 **/
	public function setOptionalFields($optionalfields) {
		if (!is_array($optionalfields)) {
			$optionalfields = explode(",", $optionalfields);
		}

		$this->optional_fields = array_merge($this->optional_fields, $optionalfields);
		return $this;
	}

	/**
	 * Add a validation rule for a given field
	 *
	 * @param string $rule One of the defined validation rules
	 * @param string $field The field name to run the rule against
	 * @param string $error_lang_var The language var name to use for error on failure
	 * @param string $field2 The second field needed by some rules (or an array for certain rules)
	 *
	 * @return boolean True or false depending on pass or fail of rule
	 **/
	public function validate($rule, $field, $error_lang_var, $field2 = "") {
		if (in_array($field, $this->optional_fields)) {
			return false;
		}


		if ($this->runRule($rule, $field, $field2)) {
			$this->validated[] = $field;
			return true;
		}

		$this->errors[] = $field;
		$this->addError($error_lang_var);
		return false;
	}

	/**
	 * This function will load custom fields and perform validation rules as per custom field config
	 *
	 * @param string $type Type of custom field to validate
	 * @param int $relid Optional ID the type relates to - product ID or support department ID
	 * @param boolean $order Set true if in the order process to validate fields that only show on signup
	 *
	 * @return True
	 **/
	public function validateCustomFields($type, $relid = "", $order = false) {
		global $whmcs;

		$where = array("type" => $type, "adminonly" => "");

		if ($relid) {
			$where['relid'] = $relid;
		}


		if ($order) {
			$where['showorder'] = "on";
		}

		$result = select_query("tblcustomfields", "id,fieldname,fieldtype,fieldoptions,required,regexpr", $where, "sortorder` ASC,`id", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$fieldid = $data['id'];
			$fieldname = $data['fieldname'];
			$fieldtype = $data['fieldtype'];
			$fieldoptions = $data['fieldoptions'];
			$required = $data['required'];
			$regexpr = $data['regexpr'];

			if (strpos($fieldname, "|")) {
				$fieldname = explode("|", $fieldname);
				$fieldname = trim($fieldname[1]);
			}


			if ($fieldtype == "link") {
				$this->validate("url", "customfield[" . $fieldid . "]", $fieldname . " is an Invalid URL");
			}


			if ($fieldtype == "dropdown") {
				$this->validate("inarray", "customfield[" . $fieldid . "]", $fieldname . " Invalid Select Option", explode(",", $fieldoptions));
			}


			if ($fieldtype == "tickbox") {
				$this->validate("inarray", "customfield[" . $fieldid . "]", $fieldname . " Invalid Value", array("on", "1", ""));
			}


			if ($required) {
				$this->validate("required", "customfield[" . $fieldid . "]", $fieldname . " " . $whmcs->get_lang("clientareaerrorisrequired"));
			}


			if ($regexpr) {
				$this->validate("matchpattern", "customfield[" . $fieldid . "]", $fieldname . " " . $whmcs->get_lang("customfieldvalidationerror"), array($regexpr));
			}
		}

		return true;
	}

	/**
	 * This function actually performs the requested validation rule
	 *
	 * @param string $rule The rule name to execute
	 * @param string $field The field name to run the rule against
	 * @param string $field2 The optional second field required by some rules (or an array for certain rules)
	 *
	 * @return boolean True or false depending upon the result of the rule
	 **/
	private function runRule($rule, $field, $field2) {
		global $whmcs;

		if (strpos($field, "[")) {
			$k1 = explode("[", $field);
			$k2 = explode("]", $k1[1]);
			$val = $whmcs->get_req_var($k1[0], $k2[0]);
		}
		else {
			$val = $whmcs->get_req_var($field);
		}


		if (!is_array($field2)) {
			$val2 = $whmcs->get_req_var($field2);
		}


		if (in_array($field, $this->optional_fields)) {
			return true;
		}

		switch ($rule) {
		case "required": {
				return !trim($val) ? false : true;
			}
		case "numeric": {
				return $this->is_numeric($val);;
			}
		case "match_value": {
            if (is_array($field2))
            {
                return ($field2[0] === $field2[1]) ? true:false;
            }
            return ($val === $val2) ? true:false;
			}
		case "matchpattern": {
				return preg_match($field2[0], $val);
			}
        case "email" :
            if (function_exists("filter_var"))
            {
                return filter_var($val, FILTER_VALIDATE_EMAIL);
            }
            return preg_match("/^([a-zA-Z0-9&'.])+([\\.a-zA-Z0-9+_-])*@([a-zA-Z0-9_-])+(\\.[a-zA-Z0-9_-]+)*\\.([a-zA-Z]{2,6})$/", $val);
        case "postcode" :
            return !preg_replace("/[a-zA-Z0-9 \\-]/", "", $val);
        case "phone" :
            return !preg_replace("/[0-9 .\\-()]/", "", $val);
        case "country" :
            if (preg_replace("/[A-Z]/", "", $val))
            {
                return false;
            }
            if (strlen($val) != 2)
            {
                return false;
            }
            return true;
        case "url" :
            return preg_match("|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i", $val);
        case "inarray" :
            return in_array($val, $field2);
        case "banneddomain" :
            if (!strpos($val, "@"))
            {
            	return false;
            }
            return get_query_val("tblbannedemails", "COUNT(id)", array("domain" => $val)) ? false : true;
        case "uniqueemail" :
            $where = array("email" => $val);
            if (is_array($field2) && 0 < $field2[0])
            {
                $where['id'] = array("sqltype" => "NEQ", "value" => $field2[0]);
            }
            if ($clientexists)
            {
                return false;
            }
            $where = array("subaccount" => "1", "email" => $val);
            if (is_array($field2) && 0 < $field2[1])
            {
                $where['id'] = array("sqltype" => "NEQ", "value" => $field2[1]);
            }
            if ($subaccexists)
            {
                return false;
            }
            return true;
        case "pwstrength" :
            $reqpwstrength = $whmcs->get_config('RequiredPWStrength');
            if (!$reqpwstrength)
            {
                return true;
            }
            $pwstrength = $this->calcPasswordStrength($val);
            if ($pwstrength <= $reqpwstrength)
            {
                return false;
            }
            return true;
		case "captcha": {
				return ($this->checkCaptchaInput($val) ? true : false);
			}
		case "uploaded": {
				return ($this->checkUploadExtensions($field) ? true : false);
			}
		}
		return true;
	}

	/**
	 * Checks the extensions of uploaded files against the allowed file types
	 *
	 * @param string $field The file upload field name to be checked
	 *
	 * @return boolean False if any file extension is not on the allow list
	 **/
	private function checkUploadExtensions($field) {
		global $whmcs;

		if ($_FILES[$field]['name'][0] == "") {
			return true;
		}

		$ext_array = $whmcs->get_config("TicketAllowedFileTypes");
		$ext_array = explode(",", trim($ext_array));

		if (!count($ext_array)) {
			return false;
		}

		foreach ($_FILES[$field]['name'] as $num => $filename) {
			$filename = trim($filename);

			if ($filename) {
				$filename = preg_replace("/[^a-zA-Z0-9-_. ]/", "", $filename);
				$parts = explode(".", $filename);
				$extension = "." . strtolower(end($parts));
				foreach ($ext_array as $value) {

					if (trim($value) == $extension) {
						return true;
					}
				}

				continue;
			}
		}

		return false;
	}

	/**
	 * Validates captcha field input
	 *
	 * @param string $val The captcha code input
	 *
	 * @return boolean False if the captcha check fails verification
	 **/
	private function checkCaptchaInput($val) {
		global $whmcs;

		$captchatype = $whmcs->get_config("CaptchaType");

		if ($captchatype == "recaptcha") {
			if (!function_exists("recaptcha_check_answer")) {
				require ROOTDIR . "/includes/recaptchalib.php";
			}

			$resp = recaptcha_check_answer($whmcs->get_config("ReCAPTCHAPrivateKey"), $whmcs->get_user_ip(), $whmcs->get_req_var("recaptcha_challenge_field"), $whmcs->get_req_var("recaptcha_response_field"));

			if (!is_object($resp)) {
				return false;
			}


			if (!$resp->is_valid) {
				return false;
			}
		}
		else {
			if ($_SESSION['image_random_value'] != md5(strtoupper($val))) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Calculates password strength
	 *
	 * @param string $val The user input password
	 *
	 * @return int Password strength
	 **/
	private function calcPasswordStrength($pw) {
		$pwstrength = 0;
		$pwlength = $calcpwlength = strlen($pw);

		if (5 < $pwlength) {
			$calcpwlength = 5;
		}

		$numnumeric = preg_replace("/[^0-9]/", "", $pw);
		$numeric = strlen($numnumeric);

		if (3 < $numeric) {
			$numeric = 3;
		}

		$symbols = preg_replace("/[^A-Za-z0-9]/", "", $pw);
		$numsymbols = $pwlength - strlen($symbols);

		if ($numsymbols < 0) {
			$numsymbols = 0;
		}


		if (3 < $numsymbols) {
			$numsymbols = 3;
		}

		$numupper = preg_replace("/[A-Z]/", "", $pw);
		$upper = $pwlength - strlen($numupper);

		if ($upper < 0) {
			$upper = 0;
		}


		if (3 < $upper) {
			$upper = 3;
		}

		$pwstrength = $calcpwlength * 10 - 20 + $numeric * 10 + $numsymbols * 15 + $upper * 10;
		return $pwstrength;
	}

	/**
	 * Adds an error to the error messages array
	 *
	 * @param mixed $var Either a client area language file string, or an admin area language file array reference
	 *
	 * @return boolean True
	 **/
	public function addError($var) {
		global $_LANG;
		global $aInt;

		if (defined("ADMINAREA")) {
			$this->errors_msgs[] = $aInt->lang($var[0], $var[1]);
		}
		else {
			$this->errors_msgs[] = (array_key_exists($var, $_LANG) ? $_LANG[$var] : $var);
		}

		return true;
	}

	/**
	 * Adds an array of errors to the error messages array
	 *
	 * @param array $errors An array of error messages
	 *
	 * @return boolean True
	 **/
	public function addErrors($errors) {
		foreach ($errors as $v) {
			$this->addError($v);
		}

		return true;
	}

	public function validated($field) {
		if ($field) {
			return in_array($field, $this->validated);
		}

		return $this->validated;
	}

	public function error($field) {
		if ($field) {
			return in_array($field, $this->errors);
		}

		return $this->errors;
	}

	/**
	 * Returns an array of error messages currently in memory
	 *
	 * @return array Error Messages
	 **/
	public function getErrors() {
		return $this->errors_msgs;
	}

	/**
	 * Returns the number of error messages currently in memory
	 *
	 * @return int Number of Errors
	 **/
	public function hasErrors() {
		return count($this->getErrors());
	}

	/**
	 * Returns an HTML formatted list of error messages currently in memory
	 *
	 * @return string HTML Formatted Error Message Output
	 **/
	public function getHTMLErrorOutput() {
		$code = "";
		foreach ($this->getErrors() as $msg) {
			$code .= "<li>" . $msg . "</li>";
		}

		return $code;
	}
}

?>