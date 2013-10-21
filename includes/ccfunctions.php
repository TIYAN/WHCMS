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
 **/

function updateCCDetails($userid, $cardtype, $cardnum, $cardcvv, $cardexp, $cardstart, $cardissue, $noremotestore = "", $fullclear = "") {
	global $CONFIG;
	global $_LANG;
	global $cc_encryption_hash;

	$gatewayid = get_query_val("tblclients", "gatewayid", array("id" => $userid));

	if ($fullclear) {
		update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "startdate" => "", "issuenumber" => "", "gatewayid" => ""), array("id" => $userid));
	}

	$cardnum = ccFormatNumbers($cardnum);
	$cardexp = ccFormatNumbers($cardexp);
	$cardstart = ccFormatNumbers($cardstart);
	$cardissue = ccFormatNumbers($cardissue);
	$cardexp = ccFormatDate($cardexp);
	$cardstart = ccFormatDate($cardstart);
	$cardcvv = ccFormatNumbers($cardcvv);

	if ($cardtype) {
		$errormessage = checkCreditCard($cardnum, $cardtype);

		if (!$cardexp || strlen($cardexp) != 4) {
			$errormessage .= "<li>" . $_LANG['creditcardenterexpirydate'];
		}
		else {
			if (substr($cardexp, 0, 2) < date("m") && "20" . substr($cardexp, 2) <= date("Y")) {
				$errormessage .= "<li>" . $_LANG['creditcardexpirydateinvalid'];
			}
		}
	}


	if ($errormessage) {
		return $errormessage;
	}


	if (!$userid) {
		return "";
	}


	if ($noremotestore) {
		return "";
	}


	if ($CONFIG['CCNeverStore']) {
		return "";
	}

	$remotestored = false;
	$result = select_query("tblpaymentgateways", "gateway,(SELECT id FROM tblinvoices WHERE paymentmethod=gateway AND userid='" . (int)$userid . "' ORDER BY id DESC LIMIT 0,1) AS invoiceid", "setting='type' AND (value='CC' OR value='OfflineCC')");

	while ($data = mysql_fetch_array($result)) {
		$gateway = $data['gateway'];
		$invoiceid = $data['invoiceid'];

		if ($invoiceid) {
			if (!isValidforPath($gateway)) {
				exit("Invalid Gateway Module Name");
			}

			require_once ROOTDIR . ("/modules/gateways/" . $gateway . ".php");

			if (function_exists($gateway . "_storeremote")) {
				$rparams = getCCVariables($invoiceid);
				$rparams['cardtype'] = $cardtype;
				$rparams['cardnum'] = $cardnum;
				$rparams['cardcvv'] = $cardcvv;
				$rparams['cardexp'] = $cardexp;
				$rparams['cardstart'] = $cardstart;
				$rparams['cardissuenum'] = $cardissue;
				$rparams['gatewayid'] = $gatewayid;
				$action = "create";

				if ($rparams['gatewayid']) {
					if ($rparams['cardnum']) {
						$action = "update";
					}
					else {
						$action = "delete";
					}
				}

				$rparams['action'] = $action;
				$captureresult = call_user_func($gateway . "_storeremote", $rparams);
				$result = select_query("tblpaymentgateways", "value", array("gateway" => $rparams['paymentmethod'], "setting" => "name"));
				$data = mysql_fetch_array($result);
				$gatewayname = $data['value'] . " Remote Storage";
				$debugdata = (is_array($captureresult['rawdata']) ? array_merge(array("UserID" => $rparams['clientdetails']['userid']), $captureresult['rawdata']) : "UserID => " . $rparams['clientdetails']['userid'] . "\r\n" . $captureresult['rawdata']);

				if ($captureresult['status'] == "success") {
					if (isset($captureresult['gatewayid'])) {
						update_query("tblclients", array("gatewayid" => $captureresult['gatewayid']), array("id" => $userid));
					}


					if ($action == "delete") {
						update_query("tblclients", array("cardtype" => "", "cardlastfour" => "", "cardnum" => "", "expdate" => "", "startdate" => "", "issuenumber" => "", "gatewayid" => ""), array("id" => $userid));
					}

					logTransaction($gatewayname, $debugdata, "Success");
				}
				else {
					logTransaction($gatewayname, $debugdata, ucfirst($captureresult['status']));
					return "<li>Remote Transaction Failure. Please Contact Support.";
				}

				$remotestored = true;
			}
		}
	}

	$cchash = md5($cc_encryption_hash . $userid);
	$cardlastfour = substr($cardnum, 0 - 4);

	if ($remotestored) {
		$cardnum = "";
	}

	update_query("tblclients", array("cardtype" => $cardtype, "cardlastfour" => $cardlastfour, "cardnum" => array("type" => "AES_ENCRYPT", "text" => $cardnum, "hashkey" => $cchash), "expdate" => array("type" => "AES_ENCRYPT", "text" => $cardexp, "hashkey" => $cchash), "startdate" => array("type" => "AES_ENCRYPT", "text" => $cardstart, "hashkey" => $cchash), "issuenumber" => array("type" => "AES_ENCRYPT", "text" => $cardissue, "hashkey" => $cchash)), array("id" => $userid));
	logActivity("Updated Stored Credit Card Details - User ID: " . $userid, $userid);
	run_hook("CCUpdate", array("userid" => $userid, "cardtype" => $cardtype, "cardnum" => $cardnum, "cardcvv" => $cardcvv, "expdate" => $cardexp, "cardstart" => $cardstart, "issuenumber" => $cardissue));
}

function ccFormatNumbers($val) {
	return preg_replace("/[^0-9]/", "", $val);
}

function ccFormatDate($date) {
	if (strlen($date) == 3) {
		$date = "0" . $date;
	}


	if (strlen($date) == 5) {
		$date = "0" . $date;
	}


	if (strlen($date) == 6) {
		$date = substr($date, 0, 2) . substr($date, 0 - 2);
	}

	return $date;
}

function getCCDetails($userid) {
	global $cc_encryption_hash;
	global $_LANG;

	$cchash = md5($cc_encryption_hash . $userid);
	$result = select_query("tblclients", "cardtype,cardlastfour,AES_DECRYPT(cardnum,'" . $cchash . "') as cardnum,AES_DECRYPT(expdate,'" . $cchash . "') as expdate,AES_DECRYPT(issuenumber,'" . $cchash . "') as issuenumber,AES_DECRYPT(startdate,'" . $cchash . "') as startdate,gatewayid", array("id" => $userid));
	$data = mysql_fetch_array($result);
	$carddata = array();
	$carddata['cardtype'] = $data['cardtype'];
	$carddata['cardlastfour'] = $data['cardlastfour'];
	$carddata['cardnum'] = ($data['cardlastfour'] ? "************" . $data['cardlastfour'] : $_LANG['nocarddetails']);
	$carddata['fullcardnum'] = $data['cardnum'];
	$carddata['expdate'] = ($data['expdate'] ? substr($data['expdate'], 0, 2) . "/" . substr($data['expdate'], 2, 2) : "");
	$carddata['startdate'] = ($data['startdate'] ? substr($data['startdate'], 0, 2) . "/" . substr($data['startdate'], 2, 2) : "");
	$carddata['issuenumber'] = $data['issuenumber'];
	$carddata['gatewayid'] = $data['gatewayid'];
	return $carddata;
}

function getCCVariables($invoiceid) {
	global $CONFIG;
	global $cc_encryption_hash;
	global $clientsdetails;

	if (!function_exists("paymentMethodsSelection")) {
		require_once dirname(__FILE__) . "/gatewayfunctions.php";
	}

	$result = select_query("tblinvoices", "userid,total,paymentmethod", array("id" => $invoiceid));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$total = $data['total'];
	$paymentmethod = $data['paymentmethod'];
	$result = select_query("tblaccounts", "SUM(amountin)-SUM(amountout)", array("invoiceid" => $invoiceid));
	$data = mysql_fetch_array($result);
	$amountpaid = $data[0];
	$balance = $total - $amountpaid;

	if ($balance < 0) {
		$balance = 0;
	}

	$cchash = md5($cc_encryption_hash . $userid);
	$result = select_query("tblclients", "cardtype,cardlastfour,AES_DECRYPT(cardnum,'" . $cchash . "') as cardnum,AES_DECRYPT(expdate,'" . $cchash . "') as expdate,AES_DECRYPT(issuenumber,'" . $cchash . "') as issuenumber,AES_DECRYPT(startdate,'" . $cchash . "') as startdate,gatewayid", array("id" => $userid));
	$data = mysql_fetch_array($result);
	$cardtype = $data['cardtype'];
	$cardnum = $data['cardnum'];
	$cardexp = $data['expdate'];
	$startdate = $data['startdate'];
	$issuenumber = $data['issuenumber'];
	$gatewayid = $data['gatewayid'];
	$result = select_query("tblclients", "bankname,banktype,AES_DECRYPT(bankcode,'" . $cchash . "') as bankcode,AES_DECRYPT(bankacct,'" . $cchash . "') as bankacct", array("id" => $userid));
	$data = mysql_fetch_array($result);
	$bankname = $data['bankname'];
	$banktype = $data['banktype'];
	$bankcode = $data['bankcode'];
	$bankacct = $data['bankacct'];
	$clientsdetails = getClientsDetails($userid, "billing");
	$params = getGatewayVariables($paymentmethod, $invoiceid, $balance);
	$params['cardtype'] = $cardtype;
	$params['cardnum'] = $cardnum;
	$params['cardexp'] = $cardexp;
	$params['cardstart'] = $startdate;
	$params['cardissuenum'] = $issuenumber;

	if ($banktype) {
		$params['bankname'] = $bankname;
		$params['banktype'] = $banktype;
		$params['bankcode'] = $bankcode;
		$params['bankacct'] = $bankacct;
	}

	$params['disableautocc'] = $clientsdetails['disableautocc'];
	$params['gatewayid'] = $gatewayid;
	return $params;
}

function captureCCPayment($invoiceid, $cccvv = "", $passedparams = false) {
	global $params;

	if (!$passedparams) {
		$params = getCCVariables($invoiceid);
	}


	if ($cccvv) {
		$params['cccvv'] = $cccvv;
	}


	if ($params['paymentmethod'] == "offlinecc") {
		return false;
	}


	if ((!$params['cardnum'] && !$params['gatewayid']) && !$params['cccvv']) {
		sendMessage("Credit Card Payment Due", $invoiceid);
	}
	else {
		$captureresult = call_user_func($params['paymentmethod'] . "_capture", $params);

		if (is_array($captureresult)) {
			$result = select_query("tblpaymentgateways", "value", array("gateway" => $params['paymentmethod'], "setting" => "name"));
			$data = mysql_fetch_array($result);
			$gatewayname = $data['value'];
			logTransaction($gatewayname, $captureresult['rawdata'], ucfirst($captureresult['status']));

			if ($captureresult['status'] == "success") {
				addInvoicePayment($params['invoiceid'], $captureresult['transid'], $params['originalamount'], $captureresult['fee'], $params['paymentmethod'], "on");
				sendMessage("Credit Card Payment Confirmation", $params['invoiceid']);
				return true;
			}

			sendMessage("Credit Card Payment Failed", $params['invoiceid']);
		}
		else {
			if ($captureresult == "success") {
				return true;
			}
		}
	}

	return false;
}

function ccProcessing() {
	global $whmcs;
	global $cron;
	global $CONFIG;

	$chargedates = array();
	$chargedates[] = "tblinvoices.duedate='" . date("Ymd", mktime(0, 0, 0, date("m"), date("d") + $CONFIG['CCProcessDaysBefore'], date("Y"))) . "'";

	if (!$CONFIG['CCAttemptOnlyOnce']) {
		$i = 1;

		while ($i <= $CONFIG['CCRetryEveryWeekFor']) {
			$chargedates[] = "tblinvoices.duedate='" . date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $i * 7 + $CONFIG['CCProcessDaysBefore'], date("Y"))) . "'";
			++$i;
		}
	}

	$qrygateways = array();
	$query = "SELECT gateway FROM tblpaymentgateways WHERE setting='type' AND value='CC'";
	$result = full_query($query);

	while ($data = mysql_fetch_array($result)) {
		$qrygateways[] = "tblinvoices.paymentmethod='" . db_escape_string($data['gateway']) . "'";
	}


	if (count($qrygateways)) {
		$z = $y = 0;
		$query = "SELECT tblinvoices.* FROM tblinvoices INNER JOIN tblclients ON tblclients.id=tblinvoices.userid WHERE (tblinvoices.status='Unpaid') AND (" . implode(" OR ", $qrygateways) . ") AND tblclients.disableautocc='' AND (" . implode(" OR ", $chargedates) . ")";
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			if (is_object($cron)) {
				$cron->logActivityDebug("Processing Capture for Invoice #" . $data['id']);
			}
			else {
				logActivity("Processing Capture for Invoice #" . $data['id']);
			}


			if (captureCCPayment($data['id'])) {
				++$z;

				if (is_object($cron)) {
					$cron->logActivityDebug("Capture Successful");
				}

				logActivity("Capture Successful");
			}

			++$y;

			if (is_object($cron)) {
				$cron->logActivityDebug("Capture Failed");
			}

			logActivity("Capture Failed");
		}


		if (is_object($cron)) {
			$cron->logActivity("Credit Card Payments Processed (" . $z . " Captured, " . $y . " Failed)", true);
			$cron->emailLog($z . " Credit Card Payments Processed (" . $y . " Failed)");
		}
		else {
			logActivity("Credit Card Payments Processed (" . $z . " Captured, " . $y . " Failed)");
		}

		return "" . $z . " Captured, " . $y . " Failed";
	}

	return false;
}

function checkCreditCard($cardnumber, $cardname) {
	global $_LANG;

	$cards = array(array("name" => "Visa", "length" => "13,16", "prefixes" => "4", "checkdigit" => true), array("name" => "MasterCard", "length" => "16", "prefixes" => "51,52,53,54,55", "checkdigit" => true), array("name" => "Diners Club", "length" => "14", "prefixes" => "300,301,302,303,304,305,36,38", "checkdigit" => true), array("name" => "Carte Blanche", "length" => "14", "prefixes" => "300,301,302,303,304,305,36,38", "checkdigit" => true), array("name" => "American Express", "length" => "15", "prefixes" => "34,37", "checkdigit" => true), array("name" => "Discover", "length" => "16", "prefixes" => "6011", "checkdigit" => true), array("name" => "JCB", "length" => "15,16", "prefixes" => "3,1800,2131", "checkdigit" => true), array("name" => "Discover", "length" => "16", "prefixes" => "6011", "checkdigit" => true), array("name" => "Enroute", "length" => "15", "prefixes" => "2014,2149", "checkdigit" => true));
	$ccErrorNo = 0;
	$cardType = 0 - 1;
	$i = 0;

	while ($i < sizeof($cards)) {
		if (strtolower($cardname) == strtolower($cards[$i]['name'])) {
			$cardType = $i;
			break;
		}

		++$i;
	}


	if (strlen($cardnumber) == 0) {
		return "<li>" . $_LANG['creditcardenternumber'];
	}


	if ($cards[$cardType]) {
		$cardNo = $cardnumber;

		if ($cards[$cardType]['checkdigit']) {
			$checksum = 0;
			$mychar = "";
			$j = 1;
			$i = strlen($cardNo) - 1;

			while (0 <= $i) {
				$calc = $cardNo[$i] * $j;

				if (9 < $calc) {
					$checksum = $checksum + 1;
					$calc = $calc - 10;
				}

				$checksum = $checksum + $calc;

				if ($j == 1) {
					$j = 2;
				}
				else {
					$j = 1;
				}

				--$i;
			}


			if ($checksum % 10 != 0) {
				return "<li>" . $_LANG['creditcardnumberinvalid'];
			}
		}

		$prefixes = explode(",", $cards[$cardType]['prefixes']);
		$PrefixValid = false;
		foreach ($prefixes as $prefix) {

			if (substr($cardNo, 0, strlen($prefix)) == $prefix) {
				$PrefixValid = true;
				break;
			}
		}


		if (!$PrefixValid) {
			return "<li>" . $_LANG['creditcardnumberinvalid'];
		}

		$LengthValid = false;
		$lengths = explode(",", $cards[$cardType]['length']);
		foreach ($lengths as $length) {

			if (strlen($cardNo) == $length) {
				$LengthValid = true;
				break;
			}
		}


		if (!$LengthValid) {
			return "<li>" . $_LANG['creditcardnumberinvalid'];
		}
	}

}

?>