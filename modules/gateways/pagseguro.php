<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

function pagseguro_activate() {
	defineGatewayField( "pagseguro", "text", "email", "", "Email Address", "50", "" );
	defineGatewayField( "pagseguro", "text", "callbacktoken", "", "Callback Token", "30", "" );
}


function pagseguro_link($params) {
	$number = preg_replace( "/[^0-9]/", "", $params["clientdetails"]["phonenumber"] );

	if (12 < strlen( $number )) {
		$number = substr( $number, strlen( $number ) - 12, strlen( $number ) );
	}

	$formatednumber = substr_replace( "000000000000", $number, strlen( $mask ) - strlen( $number ) );
	$cliente_tel = substr( $formatednumber, 4, 8 );
	$cliente_ddd = substr( $formatednumber, 2, 2 );
	$code = "<form target=\"pagseguro\" action=\"https://pagseguro.uol.com.br/security/webpagamentos/webpagto.aspx\" method=\"post\">
<input type=\"hidden\" name=\"email_cobranca\" value=\"" . $params["email"] . "\">
<input type=\"hidden\" name=\"tipo\" value=\"CP\">
<input type=\"hidden\" name=\"moeda\" value=\"BRL\">
<input type=\"hidden\" name=\"item_id_1\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"item_descr_1\" value=\"" . $params["description"] . "\">
<input type=\"hidden\" name=\"item_quant_1\" value=\"1\">
<input type=\"hidden\" name=\"item_valor_1\" value=\"" . $params["amount"] * 100 . "\">
<input type=\"hidden\" name=\"ref_transacao\" value=\"" . $params["invoiceid"] . "\">
<input type=\"hidden\" name=\"cliente_nome\" value=\"" . $params["clientdetails"]["firstname"] . " " . $params["clientdetails"]["lastname"] . "\" />
<input type=\"hidden\" name=\"cliente_cep\" value=\"" . $params["clientdetails"]["postcode"] . "\" />
<input type=\"hidden\" name=\"cliente_end\" value=\"" . $params["clientdetails"]["address1"] . "\" />
<input type=\"hidden\" name=\"cliente_bairro\" value=\"" . $params["clientdetails"]["address2"] . "\" />
<input type=\"hidden\" name=\"cliente_cidade\" value=\"" . $params["clientdetails"]["city"] . "\" />
<input type=\"hidden\" name=\"cliente_uf\" value=\"" . $params["clientdetails"]["state"] . "\" />
<input type=\"hidden\" name=\"cliente_pais\" value=\"BRA\" />
<input type=\"hidden\" name=\"cliente_ddd\" value=\"" . $cliente_ddd . "\" />
<input type=\"hidden\" name=\"cliente_tel\" value=\"" . $cliente_tel . "\" />
<input type=\"hidden\" name=\"cliente_email\" value=\"" . $params["clientdetails"]["email"] . "\" />
<input type=\"hidden\" name=\"cliente_num\" value=\"s\n\" />
<input type=\"submit\" value=\"" . $params["langpaynow"] . "\">
</form>";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$GATEWAYMODULE["pagseguroname"] = "pagseguro";
$GATEWAYMODULE["pagsegurovisiblename"] = "PagSeguro";
$GATEWAYMODULE["pagsegurotype"] = "Invoices";
?>