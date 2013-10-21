<?php
/**
 *
 *
 * @ WHMCS Decoded && Nulled By Mtimer
 *
 * @ Version  : 5.2.8
 * @ Author   : MTIMER
 * @ Release on : 2013-09-15
 * @ Website  : http://www.mtimer.cn
 *
 * */


function define_var($params) {
	$url_retorno = $params['url_retorno'];
	return $url_retorno;
}


require '../../../init.php';
$whmcs->load_function( 'gateway' );
$whmcs->load_function( 'invoice' );
$GATEWAY = getGatewayVariables( 'moipapi' );

if (!$GATEWAY['type']) {
	exit( 'Module Not Activated' );
}


if (!function_exists( 'log_var' )) {
	function log_var($var, $name = '', $to_file = false) {
		if ($to_file == true) {
			$txt = @fopen( 'debug.txt', 'a' );

			if ($txt) {
				fwrite( $txt, '-----------------------------------
' );
				fwrite( $txt, $name . '
' );
				fwrite( $txt, print_r( $var, true ) . '
' );
				fclose( $txt );
				return null;
			}
		}
		else {
			echo '<pre><b>' . $name . '</b><br>' . print_r( $var, true ) . '</pre>';
		}

	}


}

$gatewayname = 'Pagamento Direto Moip API 4.2 by Wellington cw2';
$id_transacao = $_POST['id_transacao'];
$transid = $_POST['cod_moip'];
$valor = $_POST['valor'];
$real = substr( $valor, 0, 0 - 2 );
$cent = substr( $valor, 0 - 2 );
$amount = $real . '.' . $cent;
$parcelas = $_POST['parcelas'];
$status_pagamento = $_POST['status_pagamento'];
$email_consumidor = $_POST['email_consumidor'];
$fee = $_POST['tipo_pagamento'];
$data_hora = date( 'd/m/Y H:i:s' );
$hora = date( 'H:i:s' );
log_var( $_POST, 'POST recebido: ', true );
$transacao = explode( ':', $_POST['id_transacao'] );
$transacao_novo = $transacao[1];
$tmp = explode( '-', $transacao_novo );
$invoiceid = $params['invoiceid'];
$faturaid = str_replace( ' ', '', $tmp[0] );
$varuser1 = explode( ' ', $transacao_novo );
$userid = $varuser1[3];
echo $userid;

if ($tipo_pagamento == 'BoletoBancario') {
	$tp_pagamento = 'Boleto Bancário';
	$vr_taxa = 1.38999999999999990230037;
	$percentual = 2.89999999999999991118216 / 100;
}
else {
	if ($tipo_pagamento == 'DebitoBancario') {
		$tp_pagamento = 'Débito Bancário';
		$vr_taxa = 0.390000000000000013322676;
		$percentual = 2.89999999999999991118216 / 100;
	}
	else {
		if ($tipo_pagamento == 'FinanciamentoBancario') {
			$tp_pagamento = 'Financiamento Bancário';
			$vr_taxa = 0.390000000000000013322676;
			$percentual = 2.89999999999999991118216 / 100;
		}
		else {
			if ($tipo_pagamento == 'CartaoDeCredito') {
				$tp_pagamento = 'CartÃ£o de Crédito';
				$vr_taxa = 0.390000000000000013322676;
				$percentual = 7.40000000000000035527137 / 100;
			}
			else {
				if ($tipo_pagamento == 'CartaoDeDebito') {
					$tp_pagamento = 'CartÃ£o de Débito';
					$vr_taxa = 0.390000000000000013322676;
					$percentual = 7.40000000000000035527137 / 100;
				}
				else {
					if ($tipo_pagamento == 'CarteiraMoIP') {
						$tp_pagamento = 'Carteira Moip';
						$vr_taxa = 0.390000000000000013322676;
						$percentual = 2.89999999999999991118216 / 100;
					}
					else {
						if ($tipo_pagamento == 'NaoDefinida') {
							$tp_pagamento = 'NÃ£o definida';
						}
						else {
							if ($tipo_pagamento == '') {
								$tp_pagamento = 'Indefinida';
							}
						}
					}
				}
			}
		}
	}
}

$valor = $amount;
$valor_final = $valor - $percentual * $valor;
$amount_out = $valor_final - $vr_taxa;
$variacao = $valor - $amount_out;

if ($status_pagamento == '1') {
	addinvoicepayment( $faturaid, '' . $transid . ' Pagamento autorizado ' . $tp_pagamento . ' ás ' . $hora . ' hs Fatura:', $amount, $variacao, '	moipapi' );
	logTransaction( $gatewayname, $_POST, 'Successful' );
	log_var( 'Status [' . $_POST['status_pagamento'] . ']
Transação "Autorizada", valor pago pelo cliente e identificado pelo MoIP. ', 'Retorno de dados MoIP, Fatura:' . $faturaid . '
Data: ' . $data_hora, true );
	echo 'Sucesso1';
	exit(  );
}
else {
	if ($status_pagamento == '2') {
		$msg = 'Pagamento Iniciado/abandonado: via ' . $tp_pagamento . ' ás ' . $hora . ' hs Fatura: ';
		addtransaction( $userid, $faturaid, $msg, '000', '0.00', '0000', 'moipapi', $transid, $faturaid );
		logTransaction( $gatewayname, $_POST, 'Successful' );
		echo 'Sucesso2';
		exit(  );
	}
	else {
		if ($status_pagamento == '3') {
			$transid2 = '2ªVia Boleto <a href=\'https://www.moip.com.br/Boleto.do?id=' . $transid . '\'';
			$transid = '</a> Trans ID ' . $transid . ' Boleto Impresso ás ' . $hora . ' (<a href=\'https://www.moip.com.br/Boleto.do?id=' . $transid . '\' title=\'2ª Via boleto\'>\'2ªVia boleto clique aqui\' </a>)';
			$msg = 'Boleto Impresso: via ' . $tp_pagamento . ' ás ' . $hora . ' hs Fatura:';
			addtransaction( $userid, $faturaid, $msg, '0.00', '0.00', '0.00', 'moipapi', $transid, $faturaid );
			logTransaction( $gatewayname, $_POST, 'Successful' );
			log_var( 'Status [' . $_POST['status_pagamento'] . ']
Transação "Iniciada", Boleto Impresso. ', 'Retorno de dados MoIP, Fatura: ' . $faturaid . '
Data: ' . $data_hora, true );
			echo 'Sucesso3';
			exit(  );
		}
		else {
			if ($status_pagamento == '4') {
				$amount = 'zero';
				$msg = 'Pagamento concluÃ­do: via ' . $tp_pagamento . ' ás ' . $hora . ' hs Fatura: ';
				addtransaction( $userid, $faturaid, $msg, '000', '0.00', '0000', 'moipapi', $transid, $faturaid );
				logTransaction( $gatewayname, $_POST, 'Successful' );
				log_var( 'Status [' . $_POST['status_pagamento'] . ']
Transação "Comcluida", valor repassado para sua conta MoIP. ', 'Retorno de dados MoIP, Fatura: ' . $faturaid . '
Data: ' . $data_hora, true );
				echo 'Sucesso4';
				exit(  );
			}
			else {
				if ($status_pagamento == '5') {
					$amount = 'zero';
					$msg = 'Pagamento CANCELADO: via ' . $tp_pagamento . ' ás ' . $hora . ' hs Fatura: ';
					addtransaction( $userid, $faturaid, $msg, '000', '0.00', '0000', 'moipapi', $transid, $faturaid );
					logTransaction( $gatewayname, $_POST, 'Successful' );
					log_var( 'Status [' . $_POST['status_pagamento'] . ']
Pagamento CANCELADO. ', 'Retorno de dados MoIP, Fatura: ' . $faturaid . '
Data: ' . $data_hora, true );
					echo 'Sucesso5';
					exit(  );
				}
				else {
					if ($status_pagamento == '6') {
						$amount = 'zero';
						$msg = 'Pagamento em análise: via ' . $tp_pagamento . ' em ' . $parcelas . ' parcelas ás ' . $hora . ' hs Fatura: ';
						addtransaction( $userid, $faturaid, $msg, '000', '0.00', '0000', 'moipapi', $transid, $faturaid );
						logTransaction( $gatewayname, $_POST, 'Successful' );
						log_var( 'Status [' . $_POST['status_pagamento'] . ']
Transação "Em análise", valor repassado para sua conta MoIP. ', 'Retorno de dados MoIP, Fatura: ' . $faturaid . '
Data: ' . $data_hora, true );
						echo 'Sucesso6';
						exit(  );
					}
					else {
						if ($status_pagamento == '7') {
							$amount = 'zero';
							$msg = 'Pagamento extornado: via ' . $tp_pagamento . ' em ' . $parcelas . ' parcelas ás ' . $hora . ' hs Fatura:';
							addtransaction( $userid, $faturaid, $msg, '000', '0.00', '0000', 'moipapi', $transid, $faturaid );
							logTransaction( $gatewayname, $_POST, 'Successful' );
							log_var( 'Status [' . $_POST['status_pagamento'] . ']
Pagamento Extornado. ', 'Retorno de dados MoIP, Fatura: ' . $faturaid . '
Data: ' . $data_hora, true );
							echo 'Sucesso7';
							exit(  );
						}
						else {
							$msg = 'Retorno desconhecido: via ' . $tp_pagamento . ' ás ' . $hora . ' hs Fatura:';
							$amount = 'zero';
							addtransaction( $userid, $faturaid, $msg, '00', '0.00', '0', 'moipapi', $transid, $faturaid );
							logTransaction( $gatewayname, $_POST, 'Successful' );
							log_var( 'Status [' . $_POST['status_pagamento'] . ']
Pagamento Iniciado. ', 'Retorno de dados MoIP, Fatura: ' . $faturaid . '
Data: ' . $data_hora, true );
							echo 'SucessoN';
							exit(  );
						}
					}
				}
			}
		}
	}
}

exit(  );
?>
