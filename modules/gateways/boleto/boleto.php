<?php

require("../../../init.php");
$whmcs->load_function('gateway');
$whmcs->load_function('client');

$GATEWAY = getGatewayVariables("boleto");
if (!$GATEWAY["type"]) die("Module Not Activated");

if ( !isset($_SESSION["uid"]) && !isset($_SESSION['adminid']) ) {
    header("Location: ../../../clientarea.php");
    exit;
}

$GATEWAY = array();
$gwresult = select_query("tblpaymentgateways","",array("gateway"=>"boleto"));
while ($data = mysql_fetch_array($gwresult)) {
	$gVgwsetting = $data["setting"];
	$gVgwvalue = $data["value"];
	$GATEWAY[$gVgwsetting] = $gVgwvalue;
}
if (!in_array($GATEWAY['banco'],array('banestes','bb','bradesco','cef','hsbc','itau','nossacaixa','real','unibanco'))) exit;

$result = select_query("tblinvoices","",array("id"=>(int)$invoiceid));
$data = mysql_fetch_array($result);
$id = $data["id"];
$userid = $data["userid"];
$date = $data["date"];
$duedate = $data["duedate"];
$subtotal = $data["subtotal"];
$credit = $data["credit"];
$tax = $data["tax"];
$taxrate = $data["taxrate"];
$total = $data["total"];

if ( $id && $userid && ( isset($_SESSION['adminid']) || $_SESSION["uid"]==$userid ) ) {} else {
	die("Invalid Access Attempt");
}

$clientsdetails = getClientsDetails($userid);

$year = substr($duedate,0,4);
$month = substr($duedate,5,2);
$day = substr($duedate,8,2);

$banco = $GATEWAY["banco"];
$banco = $whmcs->sanitize('a-z',$banco);

$dias_de_prazo_para_pagamento = 0; // No need for this, since the due date will be the same as the invoice's
$taxa_boleto = $GATEWAY["taxa"]; // FIELD NAME IN ADMIN: Taxa do boleto
$data_venc = date("d/m/Y",mktime(0,0,0,$month,$day,$year));  // It has to be the same as the invoice due date
$valor_cobrado = $total;
$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

$dadosboleto = array();
$dadosboleto["nosso_numero"] = $invoiceid;  // It's the variable nosso_numero for all banks
$dadosboleto["numero_documento"] = $invoiceid;
$dadosboleto["data_vencimento"] = $data_venc;
$dadosboleto["data_documento"] = date("d/m/Y");
$dadosboleto["data_processamento"] = date("d/m/Y");
$dadosboleto["valor_boleto"] = $valor_boleto;

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = $clientsdetails["firstname"]." ".$clientsdetails["lastname"]; 
$dadosboleto["endereco1"] = $clientsdetails["address1"];
$dadosboleto["endereco2"] = $clientsdetails["city"].", ".$clientsdetails["state"].", ".$clientsdetails["postcode"]; 

// INFORMACOES PARA O CLIENTE
// The information below needs to be configurable in the admin, it's some optional information for client's receipt (top portion of the boleto and intructions for cashier in the boleto itself.
$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Nonononono";   // FIELD DESCRIPTION: Linha 1 do Recibo do Sacado
$dadosboleto["demonstrativo2"] = "Mensalidade referente a nonon nonooon nononon";   // FIELD DESCRIPTION: Linha 2 do Recibo do Sacado
$dadosboleto["demonstrativo3"] = "BoletoPhp - http://www.boletophp.com.br";  // FIELD DESCRIPTION: Linha 3 do Recibo do Sacado
$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% ap�s o vencimento";  // FIELD DESCRIPTION: Linha 1 das Instru��es do Boleto
$dadosboleto["instrucoes2"] = "- Receber at� 10 dias ap�s o vencimento";  // FIELD DESCRIPTION: Linha 2 das Instru��es do Boleto
$dadosboleto["instrucoes3"] = "- Em caso de d�vidas entre em contato conosco: xxxx@xxxx.com.br";  // FIELD DESCRIPTION: Linha 3 das Instru��es do Boleto
$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto BoletoPhp - www.boletophp.com.br";  // FIELD DESCRIPTION: Linha 4 das Instru��es do Boleto

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "";
$dadosboleto["valor_unitario"] = "";
$dadosboleto["aceite"] = "N�O"; // FIELD NAME IN ADMIN: Aceite (SIM ou N�O)		
$dadosboleto["uso_banco"] = ""; 	
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "DM"; // FIELD NAME IN ADMIN: Esp�cie Doc
$dadosboleto["carteira"] = "SR";  // Each bank has its own. Needs to be configurable. FIELD NAME IN ADMIN: Carteira

// DADOS DA SUA CONTA
// This will be used for all banks
$dadosboleto["agencia"] = $GATEWAY["agencia"]; // FIELD NAME IN ADMIN: Ag�ncia (sem o d�gito)
$dadosboleto["conta"] = $GATEWAY["conta"]; 	// FIELD NAME IN ADMIN: N� da conta (sem o d�gito)
$dadosboleto["conta_cedente_dv"] = $GATEWAY["conta_cedente_dv"]; // FIELD NAME IN ADMIN: D�gito da conta 

// DADOS PERSONALIZADOS - Personalized fields for each bank
// BANCO DO BRASIL - boleto_bb.php
$dadosboleto["convenio"] = $GATEWAY["convenio"];  // FIELD NAME IN ADMIN: N� do Conv�nio (6, 7 ou 8 d�gitos)
$dadosboleto["contrato"] = $GATEWAY["contrato"]; // FIELD NAME IN ADMIN: N� do seu contrato
$dadosboleto["variacao_carteira"] = "-019";  // FIELD NAME IN ADMIN: Varia��o da Carteira com tra�o (opcional)
// TIPO DO BOLETO
$dadosboleto["formatacao_convenio"] = "7"; // // FIELD NAME IN ADMIN: Formata��o do Conv�nio (8 p/Conv�nio c/8 d�gitos, 7 p/Conv�nio c/7 d�gitos, 6 p/Conv�nio c/6 d�gitos)
$dadosboleto["formatacao_nosso_numero"] = "2"; // FIELD NAME IN ADMIN: Formata��o do Nosso N�mero (Apenas p/Conv�nio c/6 d�gitos: informe 1 para Nosso N�mero de at� 5 d�gitos ou 2 para Nosso N�mero de at� 17 d�gitos)

// DADOS PERSONALIZADOS - BANESTES - boleto_banestes.php 
$dadosboleto["tipo_cobranca"] = "2";  // FIELD NAME IN ADMIN: Tipo de cobran�a (2- Sem registro; 3- Caucionada; 4,5,6 e 7- Com registro)

// DADOS PERSONALIZADOS - BRADESCO - boleto_bradesco.php
$dadosboleto["agencia_dv"] = "0"; // FIELD NAME IN ADMIN: D�gito da Ag�ncia
$dadosboleto["conta_cedente"] = "0403005"; // Same as $dadosboleto["conta"] = $GATEWAY["conta"]
$dadosboleto["conta_dv"] = "2"; 	// Same as  $dadosboleto["conta_cedente_dv"] = $GATEWAY["conta_cedente_dv"];

// DADOS PERSONALIZADOS - CEF - boleto_cef.php
$dadosboleto["conta_cedente"] = "87000000414"; // Same as $dadosboleto["conta"] = $GATEWAY["conta"]
$dadosboleto["conta_cedente_dv"] = "3"; // Same as $dadosboleto["conta_dv"]
$dadosboleto["inicio_nosso_numero"] = $invoiceid;  // It's not the invoice ID. It's the variable $dadosboleto["inicio_nosso_numero"] = "80"; in boleto_cef.php AND this is for CEF only - FIELD NAME IN ADMIN: In�cio do Nosso N�mero (CEF somente) Carteira CR: 80, 81 ou 82  - Carteira SR: 90

// DADOS PERSONALIZADOS - HSBC - boleto_hsbc.php
$dadosboleto["codigo_cedente"] = "1122334"; // FIELD NAME IN ADMIN: C�digo do Cedente (Somente 7 digitos)

// DADOS PERSONALIZADOS - NOSSA CAIXA - boleto_nossacaixa.php 
$dadosboleto["conta_cedente"] = "001131";  // Same as $dadosboleto["conta"] = $GATEWAY["conta"] 
$dadosboleto["conta_cedente_dv"] = "1"; // Same as $dadosboleto["conta_dv"]
$dadosboleto["modalidade_conta"] = "04";  // FIELD NAME IN ADMIN: Modalidade da conta

// DADOS PERSONALIZADOS - SANTANDER BANESPA - boleto_santander_banespa.php
$dadosboleto["codigo_cliente"] = "0707077"; // FIELD NAME IN ADMIN: C�digo do Cedente
$dadosboleto["ponto_venda"] = "1333"; // FIELD NAME IN ADMIN: Ponto de Venda = Ag�ncia
$dadosboleto["carteira_descricao"] = "COBRAN�A SIMPLES - CSR";  // FIELD NAME IN ADMIN: Descri��o da Carteira

// DADOS PERSONALIZADOS - UNIBANCO - boleto_unibanco.php
$dadosboleto["codigo_cliente"] = "2031671"; // FIELD NAME IN ADMIN: C�digo do Cedente

// SEUS DADOS
$dadosboleto["identificacao"] = "BoletoPhp - C�digo Aberto de Sistema de Boletos"; // This could be the same variable for the admin company's name. It's the page title for the generated boleto
$dadosboleto["cpf_cnpj"] = "";  // No need for this
$dadosboleto["endereco"] = "Rua Central, 123"; // No need for this
$dadosboleto["cidade_uf"] = "Curitiba - PR"; // No need for this
$dadosboleto["cedente"] = "Alcantara & Schmidt Ltda.";  // FIELD NAME IN ADMIN: Cedente (it needs to be configured in the admin)

require("boleto_$banco.php");

?>