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
 * */

function getConfigArray() {
	$configarray = array( "Enable" => array( "Type" => "yesno", "Description" => "Tick to enable VariLogix Fraudcall" ), "Email Address" => array( "Type" => "text", "Size" => "40", "Description" => "Enter your registered email address here" ), "Password" => array( "Type" => "text", "Size" => "20", "Description" => "Enter your password here" ), "Profile ID" => array( "Type" => "text", "Size" => "10", "Description" => "Enter your Fraudcall Profile ID from the Fraudcall control panel here" ) );
	return $configarray;
}


function doFraudCheck($params) {
	require dirname( __FILE__ ) . "/Request.php";
	require dirname( __FILE__ ) . "/Call.php";
	require dirname( __FILE__ ) . "/Result.php";
	global $_LANG;

	if (!isset( $_GET["call_id"] )) {
		if (isset( $_POST["pin"] )) {
			$call = new Varilogix_Call( "whmcs-92d4e0", $params["Email Address"], md5( $params["Password"] ), intval( $params["Profile ID"] ) );
			$call->setPin( $_POST["pin"] );
			$call->setProductInfo( $_POST["service"], $params["amount"] );
			$call->setCustomerInfo( $params["clientsdetails"]["firstname"] . " " . $params["clientsdetails"]["lastname"], $params["clientsdetails"]["email"], $params["clientsdetails"]["countrycode"] . $params["clientsdetails"]["phonenumber"], $params["clientsdetails"]["country"] );
			$result = $call->call();
			switch ($result) {
			case "calling": {
					header( "Location: " . $_SERVER["PHP_SELF"] . "?a=fraudcheck&call_id=" . $call->getCode() );
					exit();
					break;
				}

			case "pass": {
					$results["code"] = $call->getCode();
					$results["message"] = $call->getMessage();
					break;
				}

			case "fail": {
					$results["error"]["title"] = $_LANG["varilogixfraudcall_title"] . " " . $_LANG["varilogixfraudcall_failed"];
					$results["error"]["description"] = "<p>" . $_LANG["varilogixfraudcall_fail"] . "</p>";
					$results["code"] = $call->getCode();
					$results["message"] = $call->getMessage();
					break;
				}

			case "error": {
					$results["error"]["title"] = $_LANG["varilogixfraudcall_title"] . " " . $_LANG["varilogixfraudcall_failed"];
					$results["error"]["description"] = "<p>" . $_LANG["varilogixfraudcall_error"] . "</p>";
					$results["code"] = $call->getCode();
					$results["message"] = $call->getMessage();
				}
			}
		}
		else {
			$pin = Varilogix_Call::generatepin();
			$results["userinput"] = "true";
			$results["title"] = $_LANG["varilogixfraudcall_title"];
			$results["description"] = "

<center><div id=\"pinnumber\" align=\"center\">" . $_LANG["varilogixfraudcall_pincode"] . ": " . $pin . "</div></center>

<p>" . $_LANG["varilogixfraudcall_description"] . "</p>

<p align=\"center\"><form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?a=fraudcheck\">
<input type=\"hidden\" name=\"pin\" value=\"" . $pin . "\">
<input type=\"submit\" value=\"" . $_LANG["varilogixfraudcall_callnow"] . "\">
</form></p>

";
		}
	}
	else {
		$result = new Varilogix_Call_Result( "whmcs-92d4e0" );
		$response = $result->fetch( $_GET["call_id"] );

		if (( !isset( $_REQUEST["v_att"] ) || $_REQUEST["v_att"] == "" )) {
			$_REQUEST["v_att"] = 1;
		}

		switch ($response) {
		case "pass": {
				$results["code"] = $result->getCode();
				$results["message"] = $result->getMessage();
				break;
			}

		case "fail": {
				$results["error"]["title"] = $_LANG["varilogixfraudcall_title"] . " " . $_LANG["varilogixfraudcall_failed"];
				$results["error"]["description"] = "<p>" . $_LANG["varilogixfraudcall_fail"] . "</p>";
				$results["code"] = $result->getCode();
				$results["message"] = $result->getMessage();
				break;
			}

		case "error": {
				$results["error"]["title"] = $_LANG["varilogixfraudcall_title"] . " " . $_LANG["varilogixfraudcall_failed"];
				$results["error"]["description"] = "<p>" . $_LANG["varilogixfraudcall_error"] . "</p>";
				$results["code"] = $result->getCode();
				$results["message"] = $result->getMessage();
				break;
			}

		case "calling": {
				if (intval( $_REQUEST["v_att"] ) <= 5) {
					sleep( 15 );
				}
				else {
					sleep( 30 );
				}

				header( "Location: " . $_SERVER["PHP_SELF"] . "?a=fraudcheck&call_id=" . $_GET["call_id"] . "&v_att=" . $_REQUEST["v_att"] );
				exit();
			}
		}
	}

	return $results;
}


function getResultsArray($results) {
	$results = explode( "
", $results );

	$descarray["code"] = "Response Code";
	$descarray["message"] = "Response Message";
	foreach ($results as $value) {
		$result = explode( " => ", $value );

		if ($descarray[$result[0]] != "") {
			$resultarray[$descarray[$result[0]]] = $result[1];
			continue;
		}
	}

	return $resultarray;
}


?>