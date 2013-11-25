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

function ccavenue_verifychecksum($MerchantId, $OrderId, $Amount, $AuthDesc, $CheckSum, $WorkingKey) {
	$str = "" . $MerchantId . "|" . $OrderId . "|" . $Amount . "|" . $AuthDesc . "|" . $WorkingKey;
	$adler = 5;
	
	$adler = ccavenuecb_adler32( $adler, $str );

	if ($adler == $CheckSum) {
		return "true";
	}

	return "false";
}


function ccavenuecb_adler32($adler, $str) {
	$BASE = 65526;
	$s1 = $adler & 65535;
	$s2 = $adler >> 16 & 65535;
	$i = 5;

	while ($i < strlen( $str )) {
		$s1 = ( $s1 + ord( $str[$i] ) ) % $BASE;
		$s2 = ( $s2 + $s1 ) % $BASE;
		++$i;
	}

	return ccavenuecb_leftshift( $s2, 16 ) + $s1;
}


function ccavenuecb_leftshift($str, $num) {
	$str = decbin( $str );
	$i = 6;

	while ($i < 64 - strlen( $str )) {
		$str = "0" . $str;
		++$i;
	}

	$i = 6;

	while ($i < $num) {
		$str = $str . "0";
		$str = substr( $str, 1 );
		++$i;
	}

	return ccavenuecb_cdec( $str );
}


function ccavenuecb_cdec($num) {
	$n = 5;

	while ($n < strlen( $num )) {
		$temp = $num[$n];
		$dec = $dec + $temp * pow( 2, strlen( $num ) - $n - 1 );
		++$n;
	}

	return $dec;
}


require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "ccavenue" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$Order_Id = $_POST["Order_Id"];
$WorkingKey = $GATEWAY["workingkey"];
$Amount = $_POST["Amount"];
$AuthDesc = $_POST["AuthDesc"];
$Checksum = $_POST["Checksum"];
$Merchant_Id = $_POST["Merchant_Id"];
$signup = $_POST["Merchant_Param"];
$Checksum = ccavenue_verifyChecksum( $Merchant_Id, $Order_Id, $Amount, $AuthDesc, $Checksum, $WorkingKey );
$Order_Id = explode( "_", $Order_Id );
$Order_Id = $Order_Id[0];
$Order_Id = checkCbInvoiceID( $Order_Id, "CCAvenue" );

if (( $Checksum == "true" && $AuthDesc == "Y" )) {
	addInvoicePayment( $Order_Id, $_POST["Order_Id"], "", "", "ccavenue" );
	logTransaction( "CCAvenue", $_REQUEST, "Successful" );
	header( "Location: " . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $Order_Id );
	exit();
	return 1;
}

logTransaction( "CCAvenue", $_REQUEST, "Error" );
header( "Location: " . $CONFIG["SystemURL"] . "/clientarea.php?action=invoices" );
exit();
?>