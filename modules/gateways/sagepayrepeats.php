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

function sagepayrepeats_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "SagePay Repeat Payments" ), "vendorid" => array( "FriendlyName" => "Vendor ID", "Type" => "text", "Size" => "20" ), "testmode" => array( "FriendlyName" => "Test Mode", "Type" => "yesno" ) );
	return $configarray;
}


function sagepayrepeats_3dsecure($params) {
	$gatewayids = explode( ",", $params["gatewayid"] );

	if (( !$params["cardnum"] && count( $gatewayids ) == 4 )) {
		$results = sagepayrepeats_capture( $params );

		if ($results["status"] == "success") {
			addInvoicePayment( $params["invoiceid"], $results["transid"], "", "", "sagepayrepeats", "on" );
			logTransaction( "SagePay Repeats 3DAuth", $results["rawdata"], "Repeat Capture Success" );
			sendMessage( "Credit Card Payment Confirmation", $params["invoiceid"] );
			return "success";
		}

		logTransaction( "SagePay Repeats 3DAuth", $results["rawdata"], "Repeat Capture Failure" );
		return "declined";
	}

	global $protxsimmode;

	if ($protxsimmode) {
		$TargetURL = "https://test.sagepay.com/simulator/VSPDirectGateway.asp";
		$VerifyServer = false;
	}
	else {
		if ($params["testmode"]) {
			$TargetURL = "https://test.sagepay.com/gateway/service/vspdirect-register.vsp";
			$VerifyServer = false;
		}
		else {
			$TargetURL = "https://live.sagepay.com/gateway/service/vspdirect-register.vsp";
			$VerifyServer = true;
		}
	}

	$tempvendortxcode = date( "YmdHis" ) . $params["invoiceid"];
	$data["VPSProtocol"] = "2.23";
	$data["TxType"] = "PAYMENT";
	$data["Vendor"] = $params["vendorid"];
	$data["VendorTxCode"] = $tempvendortxcode;
	$data["Amount"] = $params["amount"];
	$data["Currency"] = $params["currency"];
	$data["Description"] = $params["companyname"] . " - Invoice #" . $params["invoiceid"];
	$cardtype = sagepayrepeats_getcardtype( $params["cardtype"] );
	$data["CardHolder"] = $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"];
	$data["CardType"] = $cardtype;
	$data["CardNumber"] = $params["cardnum"];
	$data["ExpiryDate"] = $params["cardexp"];
	$data["StartDate"] = $params["cardstart"];
	$data["IssueNumber"] = $params["cardissuenum"];
	$data["CV2"] = $params["cccvv"];
	$data["BillingSurname"] = $params["clientdetails"]["lastname"];
	$data["BillingFirstnames"] = $params["clientdetails"]["firstname"];
	$data["BillingAddress1"] = $params["clientdetails"]["address1"];
	$data["BillingAddress2"] = $params["clientdetails"]["address2"];
	$data["BillingCity"] = $params["clientdetails"]["city"];

	if ($params["clientdetails"]["country"] == "US") {
		$data["BillingState"] = $params["clientdetails"]["state"];
	}


	if ($params["clientdetails"]["country"] != "GB") {
		$params["clientdetails"]["postcode"] = "0000";
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
	$data = sagepayrepeats_formatData( $data );
	$response = sagepayrepeats_requestPost( $TargetURL, $data );
	$baseStatus = $response["Status"];
	$transdump = "";
	foreach ($response as $key => $value) {
		$transdump .= ( "" . $key . " => " . $value . "
" );
	}

	update_query( "tblclients", array( "gatewayid" => "" . $tempvendortxcode . ",", "cardnum" => "" ), array( "id" => $params["clientdetails"]["userid"] ) );
	switch ($baseStatus) {
	case "3DAUTH": {
			logTransaction( "SagePay Repeats 3DAuth", $transdump, "Ok" );
			$_SESSION["sagepayrepeatsinvoiceid"] = $params["invoiceid"];
			$code = "<form method=\"post\" action=\"" . $response["ACSURL"] . "\">
		<input type=\"hidden\" name=\"PaReq\" value=\"" . $response["PAReq"] . "\">
		<input type=\"hidden\" name=\"TermUrl\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/sagepayrepeats.php?invoiceid=" . $params["invoiceid"] . "\">
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


function sagepayrepeats_capture($params) {
	if ($protxsimmode) {
		$url = "https://test.sagepay.com/simulator/VSPDirectGateway.asp";
	}
	else {
		if ($params["testmode"]) {
			$url = "https://test.sagepay.com/gateway/service/repeat.vsp";
		}
		else {
			$url = "https://live.sagepay.com/gateway/service/repeat.vsp";
		}
	}

	$gatewayid = $params["gatewayid"];

	if (!$gatewayid) {
		return array( "status" => "No Repeat Details Stored", "rawdata" => "" );
	}

	$gatewayid = explode( ",", $gatewayid );

	if (count( $gatewayid ) != 4) {
		update_query( "tblclients", array( "gatewayid" => "", "cardnum" => "" ), array( "id" => $params["clientdetails"]["userid"] ) );
		return array( "status" => "Incomplete Remote Token", "rawdata" => implode( ",", $gatewayid ) );
	}

	$fields = array();
	$fields["VPSProtocol"] = "2.23";
	$fields["TxType"] = "REPEAT";
	$fields["Vendor"] = $params["vendorid"];
	$fields["VendorTxCode"] = date( "YmdHis" ) . $params["invoiceid"];
	$fields["Amount"] = $params["amount"];
	$fields["Currency"] = $params["currency"];
	$fields["Description"] = $params["companyname"] . " - Invoice #" . $params["invoiceid"];
	$fields["RelatedVPSTxId"] = $gatewayid[1];
	$fields["RelatedVendorTxCode"] = $gatewayid[0];
	$fields["RelatedSecurityKey"] = $gatewayid[2];
	$fields["RelatedTxAuthNo"] = $gatewayid[3];
	$poststring = sagepayrepeats_formatData( $fields );
	$output = sagepayrepeats_requestPost( $url, $poststring );

	if ($output["Status"] == "OK") {
		return array( "status" => "success", "transid" => $output["VPSTxId"], "rawdata" => $output );
	}

	return array( "status" => $output["Status"], "rawdata" => $output );
}


function sagepayrepeats_requestPost($url, $data) {
	set_time_limit( 60 );
	$output = array();
	$curlSession = curl_init();
	curl_setopt( $curlSession, CURLOPT_URL, $url );
	curl_setopt( $curlSession, CURLOPT_HEADER, 0 );
	curl_setopt( $curlSession, CURLOPT_POST, 1 );
	curl_setopt( $curlSession, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $curlSession, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curlSession, CURLOPT_TIMEOUT, 30 );
	curl_setopt( $curlSession, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt( $curlSession, CURLOPT_SSL_VERIFYHOST, 1 );
	$response = split( chr( 10 ), curl_exec( $curlSession ) );

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


function sagepayrepeats_formatData($data) {
	$output = "";
	foreach ($data as $key => $value) {
		$output .= "&" . $key . "=" . urlencode( $value );
	}

	$output = substr( $output, 1 );
	return $output;
}


function sagepayrepeats_getcardtype($cardtype) {
	if ($cardtype == "Visa") {
		$cardtype = "VISA";
	}
	else {
		if ($cardtype == "Visa Debit") {
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
																$cardtype = "DELTA";
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

?>