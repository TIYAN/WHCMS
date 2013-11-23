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

function libertyreserve_activate() {
	defineGatewayField( "libertyreserve", "text", "lr_acc", "", "Account Number", "20", "" );
	defineGatewayField( "libertyreserve", "text", "lr_store", "", "Store Name", "20", "" );
	defineGatewayField( "libertyreserve", "text", "lr_storekey", "", "Store Secret Key", "20", "" );
}


function libertyreserve_link($params) {
	global $CONFIG;

	$lr_acc = $params["lr_acc"];
	$lr_store = $params["lr_store"];
	$invoiceid = $params["invoiceid"];
	$description = $params["description"];
	$amount = $params["amount"];
	$duedate = $params["duedate"];
	$firstname = $params["clientdetails"]["firstname"];
	$lastname = $params["clientdetails"]["lastname"];
	$email = $params["clientdetails"]["email"];
	$address1 = $params["clientdetails"]["address1"];
	$address2 = $params["clientdetails"]["address2"];
	$city = $params["clientdetails"]["city"];
	$state = $params["clientdetails"]["state"];
	$postcode = $params["clientdetails"]["postcode"];
	$country = $params["clientdetails"]["country"];
	$phone = $params["clientdetails"]["phone"];
	$companyname = $params["companyname"];
	$systemurl = $params["systemurl"];
	$currency = $params["currency"];
	$lr_comments = "" . $description;
	$url = $CONFIG["SystemURL"] . "/modules/gateways/callback/libertyreserve.php";
	$code = "<form method=\"post\" action=\"https://sci.libertyreserve.com\" >
<input type=\"hidden\" name=\"lr_acc\" value=\"" . $lr_acc . "\" />
<input type=\"hidden\" name=\"lr_store\" value=\"" . $lr_store . "\" />
<input type=\"hidden\" name=\"lr_comments\" value=\"" . $lr_comments . "\" />
<input type=\"hidden\" name=\"lr_merchant_ref\" value=\"" . $invoiceid . "\" />
<input type=\"hidden\" name=\"invoiceid\" value=\"" . $invoiceid . "\" />
<input type=\"hidden\" name=\"id\" value=\"" . $invoiceid . "\" />
<input type=\"hidden\" name=\"lr_success_url\" value=\"" . $url . "\" />
<input type=\"hidden\" name=\"lr_fail_url\" value=\"" . $params["returnurl"] . "\" />
<input type=\"hidden\" name=\"lr_amnt\" value=\"" . $amount . "\" />
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\"><br />
<img src=\"https://www.libertyreserve.com/downloads/banners/accept3.gif\" border=\"0\" alt=\"We Accept Liberty Reserve\" />
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE = array( "libertyreservename" => "libertyreserve", "libertyreservevisiblename" => "Liberty Reserve", "libertyreservetype" => "Invoices" );
?>