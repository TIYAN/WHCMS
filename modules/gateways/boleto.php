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

function boleto_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Boleto" ), "banco" => array( "FriendlyName" => "Banco", "Type" => "dropdown", "Options" => "banestes,bb,bradesco,cef,hsbc,itau,nossacaixa,real,unibanco" ), "taxa" => array( "FriendlyName" => "Taxa", "Type" => "text", "Size" => "10" ), "agencia" => array( "FriendlyName" => "Agencia", "Type" => "text", "Size" => "20" ), "conta" => array( "FriendlyName" => "Conta", "Type" => "text", "Size" => "20" ), "conta_cedente" => array( "FriendlyName" => "Conta Cedente", "Type" => "text", "Size" => "20", "Description" => "ContaCedente do Cliente, sem digito (Somente Números)" ), "conta_cedente_dv" => array( "FriendlyName" => "Conta Cedente DV", "Type" => "text", "Size" => "20", "Description" => "Digito da ContaCedente do Cliente" ), "convenio" => array( "FriendlyName" => "Convenio", "Type" => "text", "Size" => "20" ), "contrato" => array( "FriendlyName" => "Contrato", "Type" => "text", "Size" => "20" ) );
	return $configarray;
}


function boleto_link($params) {
	$code = "<input type=\"button\" value=\"" . $params["langpaynow"] . "\" onClick=\"window.location='" . $params["systemurl"] . "/modules/gateways/boleto/boleto.php?invoiceid=" . $params["invoiceid"] . "'\" />";
	return $code;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>