<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 * */

function ccavenue_activate() {
	defineGatewayField( "ccavenue", "text", "merchantid", "", "Merchant ID", "20", "Enter your User ID for CCAvenue here" );
	defineGatewayField( "ccavenue", "text", "workingkey", "", "Working Key", "40", "Enter the Working Key here" );
	defineGatewayField( "ccavenue", "text", "infomsg", "", "Information Message", "125", "<br />An optional message to be displayed on the Invoice Payment client area screen informing of a manual review before the invoice is marked paid." );
}


function ccavenue_link($params) {
	$Merchant_Id = $params['merchantid'];
	$Amount = sprintf( "%.2f", $params['amount'] );
	$Order_Id = $params['invoiceid'] . "_" . date( "YmdHis" );
	$Redirect_Url = $params['systemurl'] . "/modules/gateways/callback/ccavenue.php";
	$WorkingKey = $params['workingkey'];
	$Checksum = ccavenue_getCheckSum( $Merchant_Id, $Amount, $Order_Id, $Redirect_Url, $WorkingKey );
	$strRet = "<form name=ccavenue method=\"post\" action=\"https://www.ccavenue.com/shopzone/cc_details.jsp\">";
	$strRet .= "<input type=hidden name=Merchant_Id value=\"" . $Merchant_Id . "\">";
	$strRet .= "<input type=hidden name=Amount value=\"" . $Amount . "\">";
	$strRet .= "<input type=hidden name=Order_Id value=\"" . $Order_Id . "\">";
	$strRet .= "<input type=hidden name=Redirect_Url value=\"" . $Redirect_Url . "\">";
	$strRet .= "<input type=hidden name=Checksum value=\"" . $Checksum . "\">";
	$strRet .= "<input type=\"hidden\" name=\"billing_cust_name\" value=\"" . $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'] . "\">";
	$strRet .= "<input type=\"hidden\" name=\"billing_cust_address\" value=\"" . $params['clientdetails']['address1'] . "\">";
	$strRet .= "<input type=\"hidden\" name=\"billing_cust_country\" value=\"" . $params['clientdetails']['country'] . "\">";
	$strRet .= "<input type=\"hidden\" name=\"billing_cust_tel\" value=\"" . $params['clientdetails']['phonenumber'] . "\">";
	$strRet .= "<input type=\"hidden\" name=\"billing_cust_email\" value=\"" . $params['clientdetails']['email'] . "\">";
	$strRet .= "<input type=\"hidden\" name=\"delivery_cust_name\" value=\"" . $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'] . "\">";
	$strRet .= "<input type=\"hidden\" name=\"delivery_cust_address\" value=\"" . $params['clientdetails']['address1'] . "\">";
	$strRet .= "<input type=\"hidden\" name=\"delivery_cust_tel\" value=\"" . $params['clientdetails']['phonenumber'] . "\">";
	$strRet .= "<input type=\"hidden\" name=\"delivery_cust_notes\" value=\"Invoice #" . $Order_Id . "\">";
	$strRet .= "<input type=\"submit\" value=\"" . $params['langpaynow'] . "\">";
	$strRet .= "</form>";
	$strRet .= "<br />" . $params['infomsg'];
	return $strRet;
}


function ccavenue_getchecksum($MerchantId, $Amount, $OrderId, $URL, $WorkingKey) {
	$str = "" . $MerchantId . "|" . $OrderId . "|" . $Amount . "|" . $URL . "|" . $WorkingKey;
	$adler = 1;
	
	$adler = ccavenue_adler32( $adler, $str );
	return $adler;
}


function ccavenue_adler32($adler, $str) {
	$BASE = 65521;
	$s1 = $adler & 0xffff;
	$s2 = ($adler >> 16) & 0xffff;
	$i = 0;

	while ($i < strlen( $str )) {
		$s1 = ( $s1 + ord( $str[$i] ) ) % $BASE;
		$s2 = ( $s2 + $s1 ) % $BASE;
		++$i;
	}

	return ccavenue_leftshift( $s2, 16 ) + $s1;
}


function ccavenue_leftshift($str, $num) {
	$str = decbin( $str );
	$i = 0;

	while ($i < (64 - strlen( $str ))) {
		$str = "0" . $str;
		++$i;
	}

	$i = 0;

	while ($i < $num) {
		$str = $str . "0";
		$str = substr( $str, 1 );
		++$i;
	}

	return ccavenue_cdec( $str );
}


function ccavenue_cdec($num) {
	$n = 0;

	while ($n < strlen( $num )) {
		$temp = $num[$n];
		$dec = $dec + $temp * pow( 2, strlen( $num ) - $n - 1 );
		++$n;
	}

	return $dec;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE['ccavenuename'] = "ccavenue";
$GATEWAYMODULE['ccavenuevisiblename'] = "CCAvenue";
$GATEWAYMODULE['ccavenuetype'] = "Invoices";
?>