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

function tep_not_null($value) {
	if (is_array( $value )) {
		if (0 < sizeof( $value )) {
			return true;
		}

		return false;
	}


	if (( ( $value != "" && $value != "NULL" ) && 0 < strlen( trim( $value ) ) )) {
		return true;
	}

	return false;
}


require "../../../init.php";
$whmcs->load_function( "gateway" );
$whmcs->load_function( "invoice" );
$GATEWAY = getGatewayVariables( "pagseguro" );

if (!$GATEWAY["type"]) {
	exit( "Module Not Activated" );
}

$debugreport = "";
$PagSeguro = "Comando=validar";
$PagSeguro .= "&Token=" . $GATEWAY["callbacktoken"];
foreach ($_POST as $k => $v) {
	$debugreport .= ( "" . $k . " => " . $v . "
" );
	$PagSeguro .= "&" . $k . "=" . urlencode( stripslashes( $v ) );
}

$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, "https://pagseguro.uol.com.br/Security/NPI/Default.aspx" );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $PagSeguro );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_HEADER, false );
curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
$resp = curl_exec( $ch );

if (!tep_not_null( $resp )) {
	curl_setopt( $ch, CURLOPT_URL, "https://pagseguro.uol.com.br/Security/NPI/Default.aspx" );
	$resp = curl_exec( $ch );
}

curl_close( $ch );

if (strcmp( $resp, "VERIFICADO" ) == 0) {
	$VendedorEmail = addslashes( $_POST["VendedorEmail"] );
	$TransacaoID = addslashes( $_POST["TransacaoID"] );
	$Referencia = (int)$_POST["Referencia"];
	$StatusTransacao = addslashes( $_POST["StatusTransacao"] );
	$TipoPagamento = addslashes( $_POST["TipoPagamento"] );
	$CliNome = addslashes( $_POST["CliNome"] );
	$NumItens = addslashes( $_POST["NumItens"] );
	$ProdValor = number_format( str_replace( array( ",", "." ), ".", addslashes( $_POST["ProdValor_1"] ) ), 2, ".", "" );
	$Taxa = 6;
	switch ($TipoPagamento) {
	case "Boleto": {
		}

	case "Pagamento": {
		}

	case "Pagamento Online": {
			$Taxa = $ProdValor * 2.89999999999999991118216 / 100 + 0.40000000000000002220446;
			break;
		}

	case "Cartão de Crédito": {
			$Taxa = $ProdValor * 6.40000000000000035527137 / 100 + 0.40000000000000002220446;
		}
	}

	$result = select_query( "tblinvoices", "userid,status", array( "id" => $Referencia ) );
	$payments = mysql_fetch_array( $result );
	$userid = $payments["userid"];
	$status = $payments["status"];

	if ($GATEWAY["convertto"]) {
		$currency = getCurrency( $userid );
		$ProdValor = convertCurrency( $ProdValor, $GATEWAY["convertto"], $currency["id"] );
		$Taxa = convertCurrency( $Taxa, $GATEWAY["convertto"], $currency["id"] );
	}


	if ($GATEWAY["email"] != $VendedorEmail) {
		logTransaction( "PagSeguro", $debugreport, "Fraudulent" );
		return 1;
	}


	if ($StatusTransacao == "Aprovado") {
		if ($status == "Unpaid") {
			addInvoicePayment( $Referencia, $TransacaoID, $ProdValor, $Taxa, "pagseguro" );
		}

		logTransaction( "PagSeguro", $debugreport, "Incomplete" );
		header( "Location: ../../../viewinvoice.php?id=" . $Referencia . "&paymentsuccess=true" );
		return 1;
	}


	if ($StatusTransacao == "Completo") {
		$result = select_query( "tblinvoices", "status", array( "id" => $Referencia ) );
		$payments = mysql_fetch_array( $result );
		$status = $payments["status"];

		if ($status == "Unpaid") {
			addInvoicePayment( $Referencia, $TransacaoID, $ProdValor, $Taxa, "pagseguro" );
		}

		logTransaction( "PagSeguro", $debugreport, "Completed" );
		header( "Location: ../../../viewinvoice.php?id=" . $Referencia . "&paymentsuccess=true" );
		return 1;
	}


	if ($StatusTransacao == "Cancelado") {
		logTransaction( "PagSeguro", $debugreport, "Cancelled" );
		header( "Location: ../../../viewinvoice.php?id=" . $Referencia . "&paymentfailed=true" );
		return 1;
	}

	logTransaction( "PagSeguro", $debugreport, "Processing" );
	header( "Location: ../../../viewinvoice.php?id=" . $Referencia . "&paymentfailed=true" );
	return 1;
}

logTransaction( "PagSeguro", $debugreport, "Error" );

if ($Referencia) {
	header( "Location: ../../../viewinvoice.php?id=" . $Referencia . "&paymentfailed=true" );
	return 1;
}

header( "Location: ../../../clientarea.php?action=invoices" );
?>