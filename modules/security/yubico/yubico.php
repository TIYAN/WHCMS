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
 * */

class WHMCS_Yubikey {
	protected $_id = null;
	protected $_signatureKey = null;
	protected $_response = null;
	protected $_curlResult = null;
	protected $_curlError = null;
	protected $_timestampTolerance = null;
	protected $_curlTimeout = null;

	function __construct($id, $signatureKey = null) {
		if (( is_int( $id ) && 0 < $id )) {
			$this->_id = $id;
		}


		if (strlen( $signatureKey ) == 28) {
			$this->_signatureKey = base64_decode( $signatureKey );
		}

		$this->_timestampTolerance = 600;
		$this->_curlTimeout = 10;
	}


	function getTimestampTolerance() {
		return $this->_timestampTolerance;
	}


	function setTimestampTolerance($int) {
		if (( 0 < $int && $int < 86400 )) {
			$this->_timestampTolerance = $int;
			return true;
		}

		return false;
	}


	function getCurlTimeout() {
		return $this->_curlTimeout;
	}


	function setCurlTimeout($int) {
		if (( 0 < $int && $int < 600 )) {
			$this->_curlTimeout = $int;
			return true;
		}

		return false;
	}


	function getLastResponse() {
		return $this->_response;
	}


	function verify($otp) {
		unset($this->_response);
		unset($this->_curlResult);
		unset($this->_curlError);
		$otp = strtolower( $otp );

		if (!$this->_id) {
			$this->_response = "ID NOT SET";
			return false;
		}


		if (!$this->otpIsProperLength( $otp )) {
			$this->_response = "BAD OTP LENGTH";
			return false;
		}


		if (!$this->otpIsModhex( $otp )) {
			$this->_response = "OTP NOT MODHEX";
			return false;
		}

		$urlParams = "id=" . $this->_id . "&otp=" . $otp;
		$url = $this->createSignedRequest( $urlParams );

		if ($this->curlRequest( $url )) {
			$this->_response = "ERROR CONNECTING TO YUBICO - " . $this->_curlError;
			return false;
		}

		foreach ($this->_curlResult as $param) {

			if (substr( $param, 0, 2 ) == "h=") {
				$signature = substr( trim( $param ), 2 );
			}


			if (substr( $param, 0, 2 ) == "t=") {
				$timestamp = substr( trim( $param ), 2 );
			}


			if (substr( $param, 0, 7 ) == "status=") {
				$status = substr( trim( $param ), 7 );
				continue;
			}
		}

		$signedMessage = "status=" . $status . "&t=" . $timestamp;

		if (!$this->resultSignatureIsGood( $signedMessage, $signature )) {
			$this->_response = "BAD RESPONSE SIGNATURE";
			return false;
		}


		if (!$this->resultTimestampIsGood( $timestamp )) {
			$this->_response = "BAD TIMESTAMP";
			return false;
		}


		if ($status != "OK") {
			$this->_response = $status;
			return false;
		}

		$this->_response = "OK";
		return true;
	}


	function createSignedRequest($urlParams) {
		if ($this->_signatureKey) {
			$hash = urlencode( base64_encode( hash_hmac( "sha1", $urlParams, $this->_signatureKey, true ) ) );
			return "https://api.yubico.com/wsapi/verify?" . $urlParams . "&h=" . $hash;
		}

		return "https://api.yubico.com/wsapi/verify?" . $urlParams;
	}


	function curlRequest($url) {
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $this->_curlTimeout );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->_curlTimeout );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		$this->_curlResult = explode( "
", curl_exec( $ch ) );
		$this->_curlError = curl_error( $ch );
		$error = curl_errno( $ch );
		curl_close( $ch );
		return $error;
	}


	function otpIsProperLength($otp) {
		if (strlen( $otp ) == 44) {
			return true;
		}

		return false;
	}


	function otpIsModhex($otp) {
		$modhexChars = array( "c", "b", "d", "e", "f", "g", "h", "i", "j", "k", "l", "n", "r", "t", "u", "v" );
		foreach (str_split( $otp ) as $char) {

			if (!in_array( $char, $modhexChars )) {
				return false;
			}
		}

		return true;
	}


	function resultTimestampIsGood($timestamp) {
		$now = date( "U" );
		$timestampSeconds = date_format( date_create( substr( $timestamp, 0, 0 - 4 ) ), "U" );

		if (( !$timestamp || !$now )) {
			return false;
		}


		if (( $now < $timestampSeconds + $this->_timestampTolerance && $timestampSeconds - $this->_timestampTolerance < $now )) {
			return true;
		}

		return false;
	}


	function resultSignatureIsGood($signedMessage, $signature) {
		if (!$this->_signatureKey) {
			return true;
		}


		if (base64_encode( hash_hmac( "sha1", $signedMessage, $this->_signatureKey, true ) ) == $signature) {
			return true;
		}

		return false;
	}


}


function yubico_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Yubico" ), "Description" => array( "Type" => "System", "Value" => "Yubico is a hardware based solution which requires each of your users to use a YubiKey to authenticate and complete the login process.<br /><br />For more information about Yubico, please <a href=\"http://go.whmcs.com/118/yubico\" target=\"_blank\">click here</a>.<br /><br /><strong>Yubikeys start from just $25 each</strong>" ), "clientid" => array( "FriendlyName" => "Client ID", "Type" => "text", "Size" => "10", "Description" => "Setup Your YubiKey if you haven't already @ https://upgrade.yubico.com/getapikey/" ), "secretkey" => array( "FriendlyName" => "Secret Key", "Type" => "text", "Size" => "50", "Description" => "" ) );
	return $configarray;
}


function yubico_activate($params) {
	global $whmcs;

	$apiID = (int)$params["settings"]["clientid"];
	$signatureKey = $params["settings"]["secretkey"];
	$otp = (isset( $params["post_vars"]["yubicoprefix"] ) ? $params["post_vars"]["yubicoprefix"] : "");
	$invalid = false;

	if ($otp) {
		$otp = str_replace( "&quot;", "", $otp );
		$token = new WHMCS_Yubikey( $apiID, $signatureKey );
		$token->setCurlTimeout( 20 );
		$token->setTimestampTolerance( 500 );

		if ($token->verify( $otp )) {
			$otp = substr( $otp, 0, 12 );
			$output = array();
			$output["completed"] = true;
			$output["msg"] = "Yubico Key Detected & Saved Successfully!";
			$output["settings"] = array( "yubicoprefix" => sha1( $otp ) );
			return $output;
		}

		$invalid = true;
	}

	$output = "<h2>Yubico One-Time Password</h2>
<p>To associate your Yubico Key with your account, simply click into the text field below and press the button on your Yubico Key USB Device.  The system will then detect and validate your key upon submission to the next step.</p>
" . ($invalid ? "<div class=\"errorbox\"><strong>An Error Occurred. Please Try Again...</strong><br />The Yubico Key value entered could not be validated successfully with the Yubikey API</div>" : "") . "
<form onsubmit=\"dialogSubmit();return false\">
<input type=\"hidden\" name=\"2fasetup\" value=\"1\" />
<input type=\"hidden\" name=\"module\" value=\"yubico\" />
<table>
<tr><td width=\"100\">Yubico Key</td><td><input type=\"password\" name=\"yubicoprefix\" size=\"50\" id=\"yubicoprefix\" /></td></tr>
</table>
<script>
$(\"#yubicoprefix\").textext({
    plugins: 'prompt',
    prompt: 'Click Here & Activate Yubico Key'
})
</script>
<p align=\"center\"><input type=\"submit\" value=\"Activate &raquo;\" class=\"btn btn-primary large\" /></p>
</form>
";
	return $output;
}


function yubico_challenge($params) {
	$output = "<form method=\"post\" action=\"dologin.php\">
    <div align=\"center\">
        Yubico Key <input type=\"password\" name=\"otp\" size=\"20\" style=\"font-size:20px;\" /> <input type=\"submit\" value=\"Login &raquo;\" />
    </div>
</form>";
	return $output;
}


function yubico_verify($params) {
	$apiID = (int)$params["settings"]["clientid"];
	$signatureKey = $params["settings"]["secretkey"];
	$yubicoprefix = $params["user_settings"]["yubicoprefix"];
	$otp = $params["post_vars"]["otp"];
	$token = new WHMCS_Yubikey( $apiID, $signatureKey );
	$token->setCurlTimeout( 20 );
	$token->setTimestampTolerance( 500 );

	if ($token->verify( $otp )) {
		if (sha1( substr( $otp, 0, 12 ) ) == $yubicoprefix) {
			return true;
		}

		return false;
	}

	return false;
}


?>