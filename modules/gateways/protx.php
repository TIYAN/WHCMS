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
 * */

function protx_activate() {
	defineGatewayField( "protx", "text", "vendorid", "", "Vendor ID", "20", "Main Account Vendor ID used for First Payment" );
	defineGatewayField( "protx", "text", "recurringvendorid", "", "Vendor ID", "20", "Vendor ID of Continuous Authority Merchant Account used for Recurring Payments" );
	defineGatewayField( "protx", "yesno", "testmode", "", "Test Mode", "", "" );
}


function protx_link($params) {
	$code = "<form method=\"post\" action=\"" . $params["systemurl"] . "/creditcard.php\" name=\"paymentfrm\">
<input type=\"hidden\" name=\"invoiceid\" value=\"" . $params["invoiceid"] . "\">
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


function protx_3dsecure($params) {
	global $protxsimmode;

	if ($protxsimmode) {
		$TargetURL = "https://test.sagepay.com/simulator/VSPDirectGateway.asp";
		$VerifyServer = false;
	}
	else {
		if ($params["testmode"] == "on") {
			$TargetURL = "https://test.sagepay.com/gateway/service/vspdirect-register.vsp";
			$VerifyServer = false;
		}
		else {
			$TargetURL = "https://live.sagepay.com/gateway/service/vspdirect-register.vsp";
			$VerifyServer = true;
		}
	}

	$data["VPSProtocol"] = "2.23";
	$data["TxType"] = "PAYMENT";
	$data["Vendor"] = $params["vendorid"];
	$data["VendorTxCode"] = date( "YmdHis" ) . $params["invoiceid"];
	$data["Amount"] = $params["amount"];
	$data["Currency"] = $params["currency"];
	$data["Description"] = $params["companyname"] . " - Invoice #" . $params["invoiceid"];
	$cardtype = protx_getcardtype( $params["cardtype"] );
	$data["CardHolder"] = $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"];
	$data["CardType"] = $cardtype;
	$data["CardNumber"] = $params["cardnum"];
	$data["ExpiryDate"] = $params["cardexp"];
	$data["StartDate"] = $params["cardstart"];
	$data["IssueNumber"] = $params["cardissuenum"];

	if (!$params["cccvv"]) {
		$params["cccvv"] = "000";
	}

	$data["CV2"] = $params["cccvv"];
	$data["BillingSurname"] = $params["clientdetails"]["lastname"];
	$data["BillingFirstnames"] = $params["clientdetails"]["firstname"];
	$data["BillingAddress1"] = $params["clientdetails"]["address1"];
	$data["BillingAddress2"] = $params["clientdetails"]["address2"];
	$data["BillingCity"] = $params["clientdetails"]["city"];

	if ($params["clientdetails"]["country"] == "US") {
		$data["BillingState"] = $params["clientdetails"]["state"];
	}

	$data["BillingPostCode"] = $params["clientdetails"]["postcode"];
	$data["BillingCountry"] = $params["clientdetails"]["country"];
	$data["BillingPhone"] = $params["clientdetails"]["phonenumber"];
	$data["DeliverySurname"] = $params["clientdetails"]["lastname"];
	$data["DeliveryFirstnames"] = $params["clientdetails"]["firstname"];
	$data["DeliveryAddress1"] = $params["clientdetails"]["address1"];
	$data["DeliveryAddress2"] = $params["clientdetails"]["address2"];
	$data["DeliveryCity"] = $params["clientdetails"]["city"];

	if ($params["clientdetails"]["country"] == "US") {
		$data["DeliveryState"] = $params["clientdetails"]["state"];
	}

	$data["DeliveryPostCode"] = $params["clientdetails"]["postcode"];
	$data["DeliveryCountry"] = $params["clientdetails"]["country"];
	$data["DeliveryPhone"] = $params["clientdetails"]["phonenumber"];
	$data["CustomerEMail"] = $params["clientdetails"]["email"];
	$data["ClientIPAddress"] = $_SERVER["REMOTE_ADDR"];
	$data = protx_formatData( $data );
	$response = protx_requestPost( $TargetURL, $data );
	$baseStatus = $response["Status"];
	$transdump = "";
	foreach ($response as $key => $value) {
		$transdump .= ( "" . $key . " => " . $value . "
" );
	}

	switch ($baseStatus) {
	case "3DAUTH": {
			logTransaction( "SagePay 3DAuth", $transdump, "3D Auth Required" );
			$_SESSION["protxinvoiceid"] = $params["invoiceid"];
			$code = "<form method=\"post\" action=\"" . $response["ACSURL"] . "\" name=\"paymentfrm\">
		<input type=\"hidden\" name=\"PaReq\" value=\"" . $response["PAReq"] . "\">
		<input type=\"hidden\" name=\"TermUrl\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/protxthreedsecure.php?invoiceid=" . $params["invoiceid"] . "\">
		<input type=\"hidden\" name=\"MD\" value=\"" . $response["MD"] . "\">
        <noscript>
        <div class=\"errorbox\"><b>JavaScript is currently disabled or is not supported by your browser.</b><br />Please click the continue button to proceed with the processing of your transaction.</div>
        <p align=\"center\"><input type=\"submit\" value=\"Continue >>\" /></p>
        </noscript>
		</form>";
			$code;
		}
	}

	return ;
}


function protx_capture($params) {
	global $protxsimmode;

	if ($protxsimmode) {
		$TargetURL = "https://test.sagepay.com/simulator/VSPDirectGateway.asp";
		$VerifyServer = false;
	}
	else {
		if ($params["testmode"] == "on") {
			$TargetURL = "https://test.sagepay.com/gateway/service/vspdirect-register.vsp";
			$VerifyServer = false;
		}
		else {
			$TargetURL = "https://live.sagepay.com/gateway/service/vspdirect-register.vsp";
			$VerifyServer = true;
		}
	}

	$data["VPSProtocol"] = "2.23";
	$data["TxType"] = "PAYMENT";
	$data["Vendor"] = $params["recurringvendorid"];
	$data["VendorTxCode"] = date( "YmdHis" ) . $params["invoiceid"];
	$data["Amount"] = $params["amount"];
	$data["Currency"] = $params["currency"];
	$data["Description"] = $params["companyname"] . " - Invoice #" . $params["invoiceid"];
	$cardtype = protx_getcardtype( $params["cardtype"] );
	$data["CardHolder"] = $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"];
	$data["CardType"] = $cardtype;
	$data["CardNumber"] = $params["cardnum"];
	$data["ExpiryDate"] = $params["cardexp"];
	$data["StartDate"] = $params["cardstart"];
	$data["IssueNumber"] = $params["cardissuenum"];

	if (!$params["cccvv"]) {
		$params["cccvv"] = "000";
	}

	$data["CV2"] = $params["cccvv"];
	$data["BillingSurname"] = $params["clientdetails"]["lastname"];
	$data["BillingFirstnames"] = $params["clientdetails"]["firstname"];
	$data["BillingAddress1"] = $params["clientdetails"]["address1"];
	$data["BillingAddress2"] = $params["clientdetails"]["address2"];
	$data["BillingCity"] = $params["clientdetails"]["city"];

	if ($params["clientdetails"]["country"] == "US") {
		$data["BillingState"] = $params["clientdetails"]["state"];
	}

	$data["BillingPostCode"] = $params["clientdetails"]["postcode"];
	$data["BillingCountry"] = $params["clientdetails"]["country"];
	$data["BillingPhone"] = $params["clientdetails"]["phonenumber"];
	$data["DeliverySurname"] = $params["clientdetails"]["lastname"];
	$data["DeliveryFirstnames"] = $params["clientdetails"]["firstname"];
	$data["DeliveryAddress1"] = $params["clientdetails"]["address1"];
	$data["DeliveryAddress2"] = $params["clientdetails"]["address2"];
	$data["DeliveryCity"] = $params["clientdetails"]["city"];

	if ($params["clientdetails"]["country"] == "US") {
		$data["DeliveryState"] = $params["clientdetails"]["state"];
	}

	$data["DeliveryPostCode"] = $params["clientdetails"]["postcode"];
	$data["DeliveryCountry"] = $params["clientdetails"]["country"];
	$data["DeliveryPhone"] = $params["clientdetails"]["phonenumber"];
	$data["CustomerEMail"] = $params["clientdetails"]["email"];
	$data["ClientIPAddress"] = $_SERVER["REMOTE_ADDR"];
	$data["ApplyAVSCV2"] = "2";
	$data["Apply3DSecure"] = "2";
	$data["AccountType"] = "C";

	if (( $params["cardtype"] == "Maestro" || $params["cardtype"] == "Solo" )) {
		$data["AccountType"] = "M";
	}


	if (( $params["cardtype"] == "American Express" || $params["cardtype"] == "Laser" )) {
		$data["AccountType"] = "E";
	}

	$data = protx_formatData( $data );
	$response = protx_requestPost( $TargetURL, $data );
	$baseStatus = $response["Status"];
	$transdump = "";
	foreach ($response as $key => $value) {
		$transdump .= ( "" . $key . " => " . $value . "
" );
	}

	switch ($baseStatus) {
	case "OK": {
			addInvoicePayment( $params["invoiceid"], $response["VPSTxId"], "", "", "protx", "on" );
			logTransaction( "SagePay", $transdump, "Successful" );
			sendMessage( "Credit Card Payment Confirmation", $params["invoiceid"] );
			$result = "success";
			$result;
		}
	}

	return ;
}


function protx_requestPost($url, $data) {
	set_time_limit( 60 );
	$output = array();
	$curlSession = curl_init();
	curl_setopt( $curlSession, CURLOPT_URL, $url );
	curl_setopt( $curlSession, CURLOPT_HEADER, 0 );
	curl_setopt( $curlSession, CURLOPT_POST, 1 );
	curl_setopt( $curlSession, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curlSession, CURLOPT_TIMEOUT, 60 );
	curl_setopt( $curlSession, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt( $curlSession, CURLOPT_SSL_VERIFYHOST, 1 );
	$response = explode( chr( 10 ), curl_exec( $curlSession ) );

	if (curl_error( $curlSession )) {
		$output["Status"] = "FAIL";
		$output["StatusDetail"] = curl_error( $curlSession );
	}

	curl_close( $curlSession );
	$i = 5;

	while ($i < count( $response )) {
		$splitAt = strpos( $response[$i], "=" );
		$output[trim( substr( $response[$i], 0, $splitAt ) )] = trim( substr( $response[$i], $splitAt + 1 ) );
		++$i;
	}

	return $output;
}


function protx_formatData($data) {
	$output = "";
	foreach ($data as $key => $value) {
		$output .= "&" . $key . "=" . urlencode( $value );
	}

	$output = substr( $output, 1 );
	return $output;
}


function protx_getcardtype($cardtype) {
	if ($cardtype == "Visa") {
		$cardtype = "VISA";
	}
	else {
		if ($cardtype == "MasterCard") {
			$cardtype = "MC";
		}
		else {
			if ($cardtype == "American Express") {
				$cardtype = "AMEX";
			}
			else {
				if ($cardtype == "Diners Club") {
					$cardtype = "DC";
				}
				else {
					if ($cardtype == "Discover") {
						$cardtype = "DC";
					}
					else {
						if ($cardtype == "EnRoute") {
							$cardtype = "VISA";
						}
						else {
							if ($cardtype == "JCB") {
								$cardtype = "JCB";
							}
							else {
								if ($cardtype == "Delta") {
									$cardtype = "DELTA";
								}
								else {
									if ($cardtype == "Solo") {
										$cardtype = "SOLO";
									}
									else {
										if ($cardtype == "Switch") {
											$cardtype = "SWITCH";
										}
										else {
											if ($cardtype == "Maestro") {
												$cardtype = "MAESTRO";
											}
											else {
												if ($cardtype == "Electron") {
													$cardtype = "UKE";
												}
												else {
													if ($cardtype == "Visa Electron") {
														$cardtype = "UKE";
													}
													else {
														if ($cardtype == "Visa Delta") {
															$cardtype = "VISA";
														}
														else {
															if ($cardtype == "Visa Debit") {
																$cardtype = "VISA";
															}
															else {
																if ($cardtype == "Laser") {
																	$cardtype = "LASER";
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	return $cardtype;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["protxname"] = "protx";
$GATEWAYMODULE["protxvisiblename"] = "SagePay";
$GATEWAYMODULE["protxtype"] = "CC";
?>