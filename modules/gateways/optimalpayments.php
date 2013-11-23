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

function optimalpayments_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Optimal Payments" ), "accountnumber" => array( "FriendlyName" => "Account Number", "Type" => "text", "Size" => "20" ), "merchantid" => array( "FriendlyName" => "Merchant ID", "Type" => "text", "Size" => "20" ), "merchantpw" => array( "FriendlyName" => "Merchant Password", "Type" => "text", "Size" => "20" ), "testmode" => array( "FriendlyName" => "Test Mode", "Type" => "yesno" ) );
	return $configarray;
}


function optimalpayments_3dsecure($params) {
	$cardtype = optimalpayments_cardtype( $params["cardtype"] );
	$xml = "<ccEnrollmentLookupRequestV1
xmlns=\"http://www.optimalpayments.com/creditcard/xmlschema/v1\"
xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
xsi:schemaLocation=\"http://www.optimalpayments.com/creditcard/xmlschema/v1\">
<merchantAccount>
<accountNum>" . $params["accountnumber"] . "</accountNum>
<storeID>" . $params["merchantid"] . "</storeID>
<storePwd>" . $params["merchantpw"] . "</storePwd>
</merchantAccount>
<merchantRefNum>" . $params["invoiceid"] . "</merchantRefNum>
<amount>" . $params["amount"] . "</amount>
<card>
<cardNum>" . $params["cardnum"] . "</cardNum>
<cardExpiry>
<month>" . substr( $params["cardexp"], 0, 2 ) . "</month>
<year>20" . substr( $params["cardexp"], 2, 2 ) . "</year>
</cardExpiry>
<cardType>" . $cardtype . "</cardType>
</card>
</ccEnrollmentLookupRequestV1>";
	$url = "https://webservices.optimalpayments.com/creditcardWS/CreditCardServlet/v1";

	if ($params["testmode"]) {
		$url = "https://webservices.test.optimalpayments.com/creditcardWS/CreditCardServlet/v1";
	}

	$query_str = "txnMode=ccTDSLookup&txnRequest=" . urlencode( $xml );
	$data = curlCall( $url, $query_str );
	$xmldata = XMLtoArray( $data );
	$xmldata = $xmldata["CCTXNRESPONSEV1"];

	if ($xmldata["CODE"] == "0") {
		logTransaction( "Optimal Payments 3D Auth", $data, "Lookup Successful" );
		$_SESSION["optimalpaymentsconfirmationnumber"] = $xmldata["CONFIRMATIONNUMBER"];

		if ($xmldata["TDSRESPONSE"]["ENROLLMENTSTATUS"] == "Y") {
			$code = "<form method=\"post\" action=\"" . $xmldata["TDSRESPONSE"]["ACSURL"] . "\">
<input type=hidden name=\"PaReq\" value=\"" . $xmldata["TDSRESPONSE"]["PAYMENTREQUEST"] . "\">
<input type=hidden name=\"TermUrl\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/optimalpayments.php\">
<input type=hidden name=\"MD\" value=\"" . $params["invoiceid"] . "\">
<noscript>
<div class=\"errorbox\"><b>JavaScript is currently disabled or is not supported by your browser.</b><br />Please click the continue button to proceed with the processing of your transaction.</div>
<p align=\"center\"><input type=\"submit\" value=\"Continue >>\" /></p>
</noscript>
</form>";
			return $code;
		}

		$captureresult = optimalpayments_capture( $params );

		if ($captureresult["status"] == "success") {
			addInvoicePayment( $params["invoiceid"], $captureresult["transid"], "", "", "optimalpayments", "on" );
			sendMessage( "Credit Card Payment Confirmation", $invoiceid );
		}

		logTransaction( "Optimal Payments Non 3d Processed", $captureresult["rawdata"], ucfirst( $captureresult["status"] ) );
		return $captureresult["status"];
	}

	logTransaction( "Optimal Payments 3D Auth", $data, "Failed" );
}


function optimalpayments_capture($params) {
	global $remote_ip;

	$url = "https://webservices.optimalpayments.com/creditcardWS/CreditCardServlet/v1";

	if ($params["testmode"]) {
		$url = "https://webservices.test.optimalpayments.com/creditcardWS/CreditCardServlet/v1";
	}

	$cardtype = optimalpayments_cardtype( $params["cardtype"] );

	if ($params["country"] == "US") {
		$state = "<state>" . $params["clientdetails"]["state"] . "</state>";
	}
	else {
		$state = "<region>" . $params["clientdetails"]["state"] . "</region>";
	}

	$xml = "<ccAuthRequestV1 xmlns=\"http://www.optimalpayments.com/creditcard/xmlschema/v1\"
xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
xsi:schemaLocation=\"http://www.optimalpayments.com/creditcard/xmlschema/v1\">
<merchantAccount>
<accountNum>" . $params["accountnumber"] . "</accountNum>
<storeID>" . $params["merchantid"] . "</storeID>
<storePwd>" . $params["merchantpw"] . "</storePwd>
</merchantAccount>
<merchantRefNum>" . $params["invoiceid"] . "</merchantRefNum>
<amount>" . $params["amount"] . "</amount>
<card>
<cardNum>" . $params["cardnum"] . "</cardNum>
<cardExpiry>
<month>" . substr( $params["cardexp"], 0, 2 ) . "</month>
<year>20" . substr( $params["cardexp"], 2, 2 ) . "</year>
</cardExpiry>
<cardType>" . $cardtype . "</cardType>
";

	if ($params["cccvv"]) {
		$xml .= "<cvdIndicator>1</cvdIndicator>
<cvd>" . $params["cccvv"] . "</cvd>
";
	}
	else {
		$xml .= "<cvdIndicator>0</cvdIndicator>
";
	}

	$xml .= "</card>
<billingDetails>
<cardPayMethod>WEB</cardPayMethod>
<firstName>" . $params["clientdetails"]["firstname"] . "</firstName>
<lastName>" . $params["clientdetails"]["lastname"] . "</lastName>
<street>" . $params["clientdetails"]["address1"] . "</street>
<city>" . $params["clientdetails"]["city"] . "</city>
" . $state . "
<country>" . $params["clientdetails"]["country"] . "</country>
<zip>" . $params["clientdetails"]["postcode"] . "</zip>
<phone>" . $params["clientdetails"]["phonenumber"] . "</phone>
<email>" . $params["clientdetails"]["email"] . "</email>
</billingDetails>
<recurring>
<recurringIndicator>R</recurringIndicator>
</recurring>
<customerIP>" . $remote_ip . "</customerIP>
</ccAuthRequestV1>";
	$query_str = "txnMode=ccPurchase&txnRequest=" . urlencode( $xml );
	$data = curlCall( $url, $query_str );
	$xmldata = XMLtoArray( $data );
	$xmldata = $xmldata["CCTXNRESPONSEV1"];

	if ($xmldata["CODE"] == "0") {
		return array( "status" => "success", "transid" => $xmldata["txnNumber"], "rawdata" => $xmldata );
	}

	return array( "status" => "declined", "rawdata" => $xmldata );
}


function optimalpayments_cardtype($cardtype) {
	if ($cardtype == "Visa") {
		$cardtype = "VI";
	}
	else {
		if ($cardtype == "MasterCard") {
			$cardtype = "MC";
		}
		else {
			if ($cardtype == "American Express") {
				$cardtype = "AM";
			}
			else {
				if ($cardtype == "Diners Club") {
					$cardtype = "DC";
				}
				else {
					if ($cardtype == "Discover") {
						$cardtype = "DI";
					}
					else {
						if ($cardtype == "JCB") {
							$cardtype = "JC";
						}
						else {
							if ($cardtype == "Delta") {
								$cardtype = "VD";
							}
							else {
								if ($cardtype == "Solo") {
									$cardtype = "SO";
								}
								else {
									if ($cardtype == "Maestro") {
										$cardtype = "MD";
									}
									else {
										if ($cardtype == "Switch") {
											$cardtype = "SW";
										}
										else {
											if ($cardtype == "Electron") {
												$cardtype = "VE";
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