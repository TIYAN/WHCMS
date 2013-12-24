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
 * */

function asiapay_activate() {
	defineGatewayField( "asiapay", "text", "merchantid", "", "Merchant ID", "20", "" );
	defineGatewayField( "asiapay", "yesno", "testmode", "", "Test Mode", "", "" );
}


function asiapay_capture($params) {
	global $CONFIG;

	if ($params['testmode']) {
		$posturl = "https://test.paydollar.com/b2cDemo/eng/dPayment/payComp.jsp";
	}
	else {
		$posturl = "https://www.paydollar.com/b2c2/eng/dPayment/payComp.jsp";
	}


	if ($params['cardtype'] == "Visa") {
		$cardtype = "VISA";
	}
	else {
		if ($params['cardtype'] == "MasterCard") {
			$cardtype = "Master";
		}
		else {
			if ($params['cardtype'] == "Diners Club") {
				$cardtype = "Diners";
			}
			else {
				if ($params['cardtype'] == "American Express") {
					$cardtype = "AMEX";
				}
				else {
					$cardtype = $params['cardtype'];
				}
			}
		}
	}

	$postfields = array();
	$postfields['merchantId'] = $params['merchantid'];
	$postfields['amount'] = $params['amount'];
	$postfields['orderRef'] = $params['invoiceid'];

	if ($params['currency'] == "HKD") {
		$poststr .= "currCode=344&";
	}
	else {
		if ($params['currency'] == "SGD") {
			$poststr .= "currCode=702&";
		}
		else {
			if ($params['currency'] == "CNY") {
				$poststr .= "currCode=156&";
			}
			else {
				if ($params['currency'] == "JPY") {
					$poststr .= "currCode=392&";
				}
				else {
					if ($params['currency'] == "TWD") {
						$poststr .= "currCode=901&";
					}
					else {
						if ($params['currency'] == "AUD") {
							$poststr .= "currCode=036&";
						}
						else {
							if ($params['currency'] == "EUR") {
								$poststr .= "currCode=978&";
							}
							else {
								if ($params['currency'] == "GBP") {
									$poststr .= "currCode=826&";
								}
								else {
									if ($params['currency'] == "CAD") {
										$poststr .= "currCode=124&";
									}
									else {
										if ($params['currency'] == "MOP") {
											$poststr .= "currCode=446&";
										}
										else {
											if ($params['currency'] == "PHP") {
												$poststr .= "currCode=608&";
											}
											else {
												if ($params['currency'] == "THB") {
													$poststr .= "currCode=764&";
												}
												else {
													if ($params['currency'] == "MYR") {
														$poststr .= "currCode=458&";
													}
													else {
														if ($params['currency'] == "IDR") {
															$poststr .= "currCode=360&";
														}
														else {
															if ($params['currency'] == "KRW") {
																$poststr .= "currCode=410&";
															}
															else {
																if ($params['currency'] == "SAR") {
																	$poststr .= "currCode=682&";
																}
																else {
																	if ($params['currency'] == "NZD") {
																		$poststr .= "currCode=554&";
																	}
																	else {
																		if ($params['currency'] == "AED") {
																			$poststr .= "currCode=784&";
																		}
																		else {
																			if ($params['currency'] == "BND") {
																				$poststr .= "currCode=096&";
																			}
																			else {
																				$poststr .= "currCode=840&";
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
			}
		}
	}

	$postfields['pMethod'] = $cardtype;
	$postfields['cardNo'] = $params['cardnum'];
	$postfields['securityCode'] = $params['cccvv'];
	$postfields['cardHolder'] = $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'];
	$postfields['epMonth'] = substr( $params['cardexp'], 0, 2 );
	$postfields['epYear'] = substr( $params['cardexp'], 2, 2 );
	$postfields['payType'] = "N";
	$postfields['successUrl'] = $params['systemurl'] . "/modules/gateways/callback/asiapay.php";
	$postfields['failUrl'] = $params['systemurl'] . "/modules/gateways/callback/asiapay.php";
	$postfields['errorUrl'] = $params['systemurl'] . "/modules/gateways/callback/asiapay.php";
	$postfields['lang'] = "E";
	$poststr = "";
	foreach ($postfields as $k => $v) {
		$poststr .= "" . $k . "=" . urlencode( $v ) . "&";
	}

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $posturl );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $poststr );
	$returneddata = curl_exec( $ch );

	if (curl_errno( $ch )) {
		$error = curl_error( $ch );
	}

	curl_close( $ch );
	$returneddata = explode( ",", $returneddata );
	foreach ($returneddata as $temphold) {
		$temphold = explode( "=", $temphold );

		if ($temphold[0]) {
			$data[$temphold[0]] = $temphold[1];
			continue;
		}
	}


	if ( !$data['errorMsg'] && !$data['Ref'] ) {
		return array( "status" => "success", "transid" => $transid, "rawdata" => $data );
	}

	return array( "status" => "declined", "rawdata" => $data );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE['asiapayname'] = "asiapay";
$GATEWAYMODULE['asiapayvisiblename'] = "AsiaPay";
$GATEWAYMODULE['asiapaytype'] = "CC";
?>