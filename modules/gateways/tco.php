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

function tco_config() {
	global $CONFIG;

	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "2CheckOut" ), "UsageNotes" => array( "Type" => "System", "Value" => "For the automation to work, you need to enable INS Notifications inside your 2CheckOut account by going to Notifications > Global Settings > Global URL and entering the URL '" . $CONFIG["SystemURL"] . "/modules/gateways/callback/tco.php', enabling all notifications and clicking save" ), "vendornumber" => array( "FriendlyName" => "Vendor Account Number", "Type" => "text", "Size" => "10", "Description" => "Don't Yet Have an Account? Signup for Half Price using Promo Code \"WHMCS2CO24\" @ <a href=\"http://www.2checkout.com/va\" target=\"_blank\">www.2checkout.com/va</a>" ), "apiusername" => array( "FriendlyName" => "API Username", "Type" => "text", "Size" => "20", "Description" => "Setup in Account > User Management section of 2CheckOut's Panel" ), "apipassword" => array( "FriendlyName" => "API Password", "Type" => "text", "Size" => "20", "Description" => "" ), "secretword" => array( "FriendlyName" => "Secret Word", "Type" => "text", "Size" => "15", "Description" => "Used to validate callbacks, found in Account > Site Management of 2CheckOut's Panel (must leave blank for demo mode testing)" ), "disablerecur" => array( "FriendlyName" => "Disable Recurring", "Type" => "yesno", "Description" => "Tick to prevent offering the 2CheckOut Subscription option on invoices" ), "forcerecur" => array( "FriendlyName" => "Force Recurring", "Type" => "yesno", "Description" => "Tick to only show the recurring option on invoices when available" ), "purchaseroutine" => array( "FriendlyName" => "Purchase Routine", "Type" => "yesno", "Description" => "Tick to use 2CheckOut's New Cart Checkout Routine (Includes showing PayPal Option)" ), "skipfraudcheck" => array( "FriendlyName" => "Skip 2CO Fraud Check", "Type" => "yesno", "Description" => "Tick this box to mark invoices paid as soon as payments are made and not wait for 2CheckOut's Fraud Review Pass" ), "demomode" => array( "FriendlyName" => "Demo Mode", "Type" => "yesno" ) );
	return $configarray;
}


function tco_link($params) {
	global $_LANG;

	$code = "";

	if (!$params["disablerecur"]) {
		$recurrings = getRecurringBillingValues( $params["invoiceid"] );

		if ($recurrings) {
			$code .= "<form action=\"" . $params["systemurl"] . "/modules/gateways/tco.php?recurring=1\" method=\"post\">
    <input type=\"hidden\" name=\"invoiceid\" value=\"" . $params["invoiceid"] . "\" />
    <input type=\"submit\" value=\"" . $_LANG["invoicesubscriptionpayment"] . "\" />
    </form>";
		}
	}


	if (( $params["forcerecur"] && $code )) {
		return $code;
	}

	global $CONFIG;

	$lang = $params["clientdetails"]["language"];

	if (!$lang) {
		$lang = $CONFIG["Language"];
	}


	if ($lang == "chinese") {
		$lang = "zh";
	}
	else {
		if ($lang == "danish") {
			$lang = "da";
		}
		else {
			if ($lang == "dutch") {
				$lang = "nl";
			}
			else {
				if ($lang == "french") {
					$lang = "fr";
				}
				else {
					if ($lang == "german") {
						$lang = "gr";
					}
					else {
						if ($lang == "greek") {
							$lang = "el";
						}
						else {
							if ($lang == "italian") {
								$lang = "it";
							}
							else {
								if ($lang == "japanese") {
									$lang = "jp";
								}
								else {
									if ($lang == "norwegian") {
										$lang = "no";
									}
									else {
										if ($lang == "portuguese") {
											$lang = "pt";
										}
										else {
											if ($lang == "slovenian") {
												$lang = "sl";
											}
											else {
												if ($lang == "spanish") {
													$lang = "es_la";
												}
												else {
													if ($lang == "swedish") {
														$lang = "sv";
													}
													else {
														if ($lang == "english") {
															$lang = "en";
														}
														else {
															$lang = "";
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


	if ($lang) {
		$lang = "<input type=\"hidden\" name=\"lang\" value=\"" . $lang . "\">";
	}


	if (!$params["purchaseroutine"]) {
		$purchaseroutine = "s";
	}

	$code .= "<form action=\"https://www.2checkout.com/checkout/" . $purchaseroutine . "purchase\" method=\"post\">
<input type=\"hidden\" name=\"x_login\" value=\"" . $params["vendornumber"] . "\">
<input type=\"hidden\" name=\"x_invoice_num\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"x_amount\" value=\"" . $params["amount"] . "\">
<input type=\"hidden\" name=\"tco_currency\" value=\"" . $params["currency"] . "\">
<input type=\"hidden\" name=\"c_name\" value=\"" . $params["description"] . "\">
<input type=\"hidden\" name=\"c_description\" value=\"" . $params["description"] . "\">
<input type=\"hidden\" name=\"c_price\" value=\"" . $params["amount"] . "\">
<input type=\"hidden\" name=\"c_tangible\" value=\"N\">
<input type=\"hidden\" name=\"x_First_Name\" value=\"" . $params["clientdetails"]["firstname"] . "\">
<input type=\"hidden\" name=\"x_Last_Name\" value=\"" . $params["clientdetails"]["lastname"] . "\">
<input type=\"hidden\" name=\"x_Email\" value=\"" . $params["clientdetails"]["email"] . "\">
<input type=\"hidden\" name=\"x_Address\" value=\"" . $params["clientdetails"]["address1"] . "\">
<input type=\"hidden\" name=\"x_City\" value=\"" . $params["clientdetails"]["city"] . "\">
<input type=\"hidden\" name=\"x_State\" value=\"" . $params["clientdetails"]["state"] . "\">
<input type=\"hidden\" name=\"x_Zip\" value=\"" . $params["clientdetails"]["postcode"] . "\">
<input type=\"hidden\" name=\"x_Country\" value=\"" . $params["clientdetails"]["country"] . "\">
<input type=\"hidden\" name=\"x_Phone\" value=\"" . $params["clientdetails"]["phonenumber"] . "\">
<input type=\"hidden\" name=\"fixed\" value=\"Y\">
<input type=\"hidden\" name=\"return_url\" value=\"" . $params["systemurl"] . "/cart.php\">
<input type=\"hidden\" name=\"return_url\" value=\"" . $params["systemurl"] . "/cart.php\">
" . $lang . "
<input type=\"hidden\" name=\"x_receipt_link_url\" value=\"" . $params["systemurl"] . "/modules/gateways/callback/2checkout.php\">";

	if ($params["demomode"] == "on") {
		$code .= "
<input type=\"hidden\" name=\"demo\" value=\"Y\">";
	}

	$code .= "<input type=\"submit\" value=\"" . $_LANG["invoiceoneoffpayment"] . "\" />
</form>";
	return $code;
}


function tco_refund($params) {
	$sale_id = $params["transid"];
	$invoice_id = "";

	if (strpos( $sale_id, "-" )) {
		$parts = explode( "-", $sale_id, 2 );
		$sale_id = $parts[0];
		$invoice_id = $parts[1];
	}

	$url = "https://www.2checkout.com/api/sales/refund_invoice";
	$post_variables = array( "sale_id" => $sale_id, "invoice_id" => $invoice_id, "amount" => $params["amount"], "currency" => "vendor", "category" => 5, "comment" => "Cancelled" );
	$query_string = http_build_query( $post_variables );
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_USERPWD, $params["apiusername"] . ":" . $params["apipassword"] );
	curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Accept: application/json" ) );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
	$results = array();

	if (!$response = curl_exec( $ch )) {
		$results["curl_error"] = curl_error( $ch );
		curl_close( $ch );
		return array( "status" => "error", "rawdata" => $results );
	}

	curl_close( $ch );

	if (!function_exists( "json_decode" )) {
		exit( "JSON Module Required in PHP Build for 2CheckOut Gateway" );
	}

	$response = json_decode( $response );

	if (( !count( $response->errors ) && $response->response_code == "OK" )) {
		$results["transid"] = $params["transid"];
		$results["message"] = $response->response_message;
		$results["status"] = "success";
		return array( "status" => "success", "transid" => $results["transid"], "rawdata" => $results );
	}

	$results["status"] = "error";
	$results["error_code"] = $response->errors[0]->code;
	$results["message"] = $response->errors[0]->message;
	return array( "status" => "error", "rawdata" => $results );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}


if (( isset( $_GET["recurring"] ) && $_GET["recurring"] == "1" )) {
	require "../../init.php";
	$whmcs->load_function( "gateway" );
	$whmcs->load_function( "invoice" );
	$whmcs->load_function( "client" );
	$GATEWAY = getGatewayVariables( "tco" );
	$invoiceid = $description = (int)$_POST["invoiceid"];
	$vendorid = $GATEWAY["vendornumber"];
	$apiusername = $GATEWAY["apiusername"];
	$apipassword = $GATEWAY["apipassword"];
	$demomode = $GATEWAY["demomode"];
	$recurrings = getRecurringBillingValues( $invoiceid );

	if (!$recurrings) {
		$url = "../../viewinvoice.php?id=" . $invoiceid;
		header( "Location:" . $url );
		exit();
	}

	$primaryserviceid = $recurrings["primaryserviceid"];
	$first_payment_amount = ($recurrings["firstpaymentamount"] ? $recurrings["firstpaymentamount"] : $recurrings["recurringamount"]);
	$recurring_amount = $recurrings["recurringamount"];

	if ($recurrings["recurringcycleunits"] == "Months") {
		$billing_cycle = $recurrings["recurringcycleperiod"] . " Month";
	}
	else {
		if ($recurrings["recurringcycleunits"] == "Years") {
			$billing_cycle = $recurrings["recurringcycleperiod"] . " Year";
		}
	}

	$billing_duration = "Forever";
	$startup_fee = $first_payment_amount - $recurring_amount;
	$url = "https://www.2checkout.com/api/products/create_product";
	$name = "Recurring Subscription for Invoice #" . $invoiceid;

	if ($demomode = "on") {
		$query_string = "name=" . $name . "&price=" . $recurring_amount . "&startup_fee=" . $startup_fee . "&demo=Y&recurring=1&recurrence=" . $billing_cycle . "&duration=" . $billing_duration . "&description=" . $description;
	}
	else {
		$query_string = "name=" . $name . "&price=" . $recurring_amount . "&startup_fee=" . $startup_fee . "&recurring=1&recurrence=" . $billing_cycle . "&duration=" . $billing_duration . "&description=" . $description;
	}

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_USERPWD, $apiusername . ":" . $apipassword );
	curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $query_string );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array( "Accept: application/json" ) );
	$response = curl_exec( $ch );
	curl_close( $ch );

	if (!function_exists( "json_decode" )) {
		exit( "JSON Module Required in PHP Build for 2CheckOut Gateway" );
	}

	$response = json_decode( $response, true );

	if (( !count( $response["errors"] ) && $response["response_code"] == "OK" )) {
		logTransaction( "2Checkout Recurring", print_r( $response, true ), "Ok" );
		$product_id = $response["product_id"];
		$assigned_product_id = $response["assigned_product_id"];
		$purchaseroutine = (!$GATEWAY["purchaseroutine"] ? "s" : "");
		$result = select_query( "tblinvoices", "userid", array( "id" => $invoiceid ) );
		$data = mysql_fetch_array( $result );
		$userid = $data[0];
		$clientsdetails = getClientsDetails( $userid );
		$currency = getCurrency( $userid );
		global $CONFIG;

		$lang = $clientsdetails["language"];

		if (!$lang) {
			$lang = $CONFIG["Language"];
		}

		$lang = strtolower( $lang );

		if ($lang == "chinese") {
			$lang = "zh";
		}
		else {
			if ($lang == "danish") {
				$lang = "da";
			}
			else {
				if ($lang == "dutch") {
					$lang = "nl";
				}
				else {
					if ($lang == "french") {
						$lang = "fr";
					}
					else {
						if ($lang == "german") {
							$lang = "gr";
						}
						else {
							if ($lang == "greek") {
								$lang = "el";
							}
							else {
								if ($lang == "italian") {
									$lang = "it";
								}
								else {
									if ($lang == "japanese") {
										$lang = "jp";
									}
									else {
										if ($lang == "norwegian") {
											$lang = "no";
										}
										else {
											if ($lang == "portuguese") {
												$lang = "pt";
											}
											else {
												if ($lang == "slovenian") {
													$lang = "sl";
												}
												else {
													if ($lang == "spanish") {
														$lang = "es_la";
													}
													else {
														if ($lang == "swedish") {
															$lang = "sv";
														}
														else {
															if ($lang == "english") {
																$lang = "en";
															}
															else {
																$lang = "";
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


		if ($lang) {
			$lang = "&lang=" . $lang;
		}


		if (( $clientsdetails["country"] != "US" || $clientsdetails["country"] != "CA" )) {
			$clientsdetails["state"] = "XX";
		}

		$url = "https://www.2checkout.com/checkout/" . $purchaseroutine . "purchase?sid=" . $vendorid . "&quantity=1&product_id=" . $assigned_product_id . "&tco_currency=" . $currency["code"] . "&merchant_order_id=" . $primaryserviceid . "&card_holder_name=" . $clientsdetails["firstname"] . " " . $clientsdetails["lastname"] . "&street_address=" . $clientsdetails["address1"] . "&city=" . $clientsdetails["city"] . "&state=" . $clientsdetails["state"] . "&zip=" . $clientsdetails["postcode"] . "&country=" . $clientsdetails["country"] . "&email=" . $clientsdetails["email"] . "&phone=" . $clientsdetails["phonenumber"] . $lang;
		header( "Location:" . $url );
		exit();
		return 1;
	}

	$apierror = "Errors => " . print_r( $response, true );
	logTransaction( "2Checkout Recurring", $apierror, "Error" );
	$url = "../../viewinvoice.php?id=" . $invoiceid . "&paymentfailed=true";
	header( "Location:" . $url );
	exit();
}

?>