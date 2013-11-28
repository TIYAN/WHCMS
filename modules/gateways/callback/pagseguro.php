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
 **/

function tep_not_null($value) {
	if (is_array($value)) {
		if (0 < sizeof($value)) {
			return true;
		}

		return false;
	}


	if (($value != "" && $value != "NULL") && 0 < strlen(trim($value))) {
		return true;
	}

	return false;
}

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
$GATEWAY = getGatewayVariables("pagseguro");

if (!$GATEWAY['type']) {
	exit("Module Not Activated");
}

$PagSeguro = "Comando=validar";
$PagSeguro .= "&Token=" . $GATEWAY['callbacktoken'];
foreach ($_POST as $k => $v) {
	$PagSeguro .= "&" . $k . "=" . urlencode(stripslashes($v));
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://pagseguro.uol.com.br/Security/NPI/Default.aspx");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $PagSeguro);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$resp = curl_exec($ch);

if (!tep_not_null($resp)) {
	curl_setopt($ch, CURLOPT_URL, "https://pagseguro.uol.com.br/Security/NPI/Default.aspx");
	$resp = curl_exec($ch);
}

curl_close($ch);

if (strcmp($resp, "VERIFICADO") == 0) {
	$VendedorEmail = addslashes($_POST['VendedorEmail']);
	$TransacaoID = addslashes($_POST['TransacaoID']);
	$Referencia = (int)$_POST['Referencia'];
	$StatusTransacao = addslashes($_POST['StatusTransacao']);
	$TipoPagamento = addslashes($_POST['TipoPagamento']);
	$CliNome = addslashes($_POST['CliNome']);
	$NumItens = addslashes($_POST['NumItens']);
	$ProdValor = number_format(str_replace(array(",", "."), ".", addslashes($_POST['ProdValor_1'])), 2, ".", "");
	$Taxa = 644;
	$invoiceid = checkCbInvoiceID($Referencia, "PagSeguro");
	switch ($TipoPagamento) {
	case "Boleto": {
		}

	case "Pagamento": {
		}

	case "Pagamento Online": {
			$Taxa = $ProdValor * 2.89999999999999991118216 / 100 + 0.40000000000000002220446;
			break;
		}

	case "Cartгo de Crйdito": {
			$Taxa = $ProdValor * 6.40000000000000035527137 / 100 + 0.40000000000000002220446;
		}
	}

	$result = select_query("tblinvoices", "userid,status", array("id" => $invoiceid));
	$payments = mysql_fetch_array($result);
	$userid = $payments['userid'];
	$status = $payments['status'];

	if ($GATEWAY['convertto']) {
		$currency = getCurrency($userid);
		$ProdValor = convertCurrency($ProdValor, $GATEWAY['convertto'], $currency['id']);
		$Taxa = convertCurrency($Taxa, $GATEWAY['convertto'], $currency['id']);
	}


	if ($GATEWAY['email'] != $VendedorEmail) {
		logTransaction("PagSeguro", $_REQUEST, "Invalid Vendor Email");
		return 1;
	}


	if ($StatusTransacao == "Aprovado") {
		if ($status == "Unpaid") {
			addInvoicePayment($invoiceid, $TransacaoID, $ProdValor, $Taxa, "pagseguro");
		}

		logTransaction("PagSeguro", $_REQUEST, "Incomplete");
		redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
		return 1;
	}


	if ($StatusTransacao == "Completo") {
		$result = select_query("tblinvoices", "status", array("id" => $invoiceid));
		$payments = mysql_fetch_array($result);
		$status = $payments['status'];

		if ($status == "Unpaid") {
			addInvoicePayment($invoiceid, $TransacaoID, $ProdValor, $Taxa, "pagseguro");
		}

		logTransaction("PagSeguro", $_REQUEST, "Completed");
		redirSystemURL("id=" . $invoiceid . "&paymentsuccess=true", "viewinvoice.php");
		return 1;
	}


	if ($StatusTransacao == "Cancelado") {
		logTransaction("PagSeguro", $_REQUEST, "Cancelled");
		redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
		return 1;
	}

	logTransaction("PagSeguro", $_REQUEST, "Processing");
	redirSystemURL("id=" . $invoiceid . "&paymentfailed=true", "viewinvoice.php");
	return 1;
}

logTransaction("PagSeguro", $_REQUEST, "Error");
redirSystemURL("action=invoices", "clientarea.php");
?>