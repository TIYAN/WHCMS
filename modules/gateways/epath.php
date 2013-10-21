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

function epath_activate() {
	defineGatewayField( "epath", "text", "submiturl", "http://e-path.com.au/demo1/demo1/demo1.php", "Submit URL", "50", "Your unique secure e-Path payment page" );
	defineGatewayField( "epath", "text", "returl", "http://www.yourdomain.com/success.html", "Return URL", "50", "The URL you want users returning to once complete" );
}


function epath_link($params) {
	$invoiceid = $params["invoiceid"];
	$invoicetotal = $params["amount"];
	$query = "SELECT * FROM tblinvoiceitems WHERE invoiceid='" . (int)$invoiceid . "' AND type='Hosting'";
	$result = full_query( $query );
	$data = mysql_fetch_array( $result );
	$relid = $data["relid"];
	$query = "SELECT billingcycle FROM tblhosting WHERE id=" . (int)$relid;
	$result = full_query( $query );
	$data = mysql_fetch_array( $result );

	if ($data) {
		$billingcycle = $data["billingcycle"];
	}
	else {
		$billingcycle = "Only Only";
	}

	$code = "<form action=\"" . $params["submiturl"] . "\" method=\"post\" name=\"\">
<input type=\"hidden\" name=\"ord\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"des\" value=\"" . $params["description"] . "\">
<input type=\"hidden\" name=\"amt\" value=\"" . $params["amount"] . "\">
<input type=\"hidden\" name=\"frq\" value=\"" . $billingcycle . "\">
<input type=\"hidden\" name=\"ceml\" value=\"" . $params["clientdetails"]["email"] . "\">
<input type=\"hidden\" name=\"ret\" value=\"" . $params["returl"] . "\">
<input type=\"submit\" name=\"\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["epathname"] = "epath";
$GATEWAYMODULE["epathvisiblename"] = "e-Path";
$GATEWAYMODULE["epathtype"] = "Invoices";
?>