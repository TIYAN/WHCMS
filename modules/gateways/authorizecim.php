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

function authorizecim_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Authorize.net CIM" ), "loginid" => array( "FriendlyName" => "Login ID", "Type" => "text", "Size" => "25" ), "transkey" => array( "FriendlyName" => "Transaction Key", "Type" => "text", "Size" => "25" ), "validationmode" => array( "FriendlyName" => "Validation Mode", "Type" => "dropdown", "Options" => "none,live" ), "testmode" => array( "FriendlyName" => "Test Mode", "Type" => "yesno" ) );
	return $configarray;
}


function authorizecim_capture($params) {
	if ($params['testmode']) {
		$url = "https://apitest.authorize.net/xml/v1/request.api";
	}
	else {
		$url = "https://api.authorize.net/xml/v1/request.api";
	}

	$gatewayids = explode( ",", $params['gatewayid'] );

	if (!$gatewayids[0]) {
		return array( "status" => "error", "rawdata" => "No Client Profile ID Found" );
	}


	if (!$gatewayids[1]) {
		return array( "status" => "error", "rawdata" => "No Client Payment Profile ID Found" );
	}

	$storednameaddresshash = $gatewayids[2];
	
	$nameaddresshash = md5( $params['clientdetails']['firstname'] . $params['clientdetails']['lastname'] . $params['clientdetails']['address1'] . $params['clientdetails']['city'] . $params['clientdetails']['state'] . $params['clientdetails']['postcode'] . $params['clientdetails']['country'] );

	if ($nameaddresshash != $storednameaddresshash) {
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<getCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">
<merchantAuthentication>
<name>" . $params['loginid'] . "</name>
<transactionKey>" . $params['transkey'] . "</transactionKey>
</merchantAuthentication>
<customerProfileId>" . $gatewayids[0] . "</customerProfileId>
<customerPaymentProfileId>" . $gatewayids[1] . "</customerPaymentProfileId>
</getCustomerPaymentProfileRequest>";
		$data = curlCall( $url, $xml, array( "HEADER" => array( "Content-Type: text/xml" ) ) );
		$xmldata = XMLtoArray( $data );
		$cardnum = $xmldata['GETCUSTOMERPAYMENTPROFILERESPONSE']['PAYMENTPROFILE']['PAYMENT']['CREDITCARD']['CARDNUMBER'];
		$expdate = $xmldata['GETCUSTOMERPAYMENTPROFILERESPONSE']['PAYMENTPROFILE']['PAYMENT']['CREDITCARD']['EXPIRATIONDATE'];
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<updateCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">
<merchantAuthentication>
<name>" . $params['loginid'] . "</name>
<transactionKey>" . $params['transkey'] . "</transactionKey>
</merchantAuthentication>
<customerProfileId>" . $gatewayids[0] . "</customerProfileId>
<paymentProfile>
<billTo>
<firstName><![CDATA[" . $params['clientdetails']['firstname'] . "]]></firstName>
<lastName><![CDATA[" . $params['clientdetails']['lastname'] . "]]></lastName>
<company><![CDATA[" . $params['clientdetails']['companyname'] . "]]></company>
<address><![CDATA[" . $params['clientdetails']['address1'] . "]]></address>
<city><![CDATA[" . $params['clientdetails']['city'] . "]]></city>
<state><![CDATA[" . $params['clientdetails']['state'] . "]]></state>
<zip><![CDATA[" . $params['clientdetails']['postcode'] . "]]></zip>
<country><![CDATA[" . $params['clientdetails']['country'] . "]]></country>
<phoneNumber>" . $params['clientdetails']['phonenumber'] . "</phoneNumber>
<faxNumber></faxNumber>
</billTo>
<payment>
<creditCard>
<cardNumber>" . $cardnum . "</cardNumber>
<expirationDate>" . $expdate . "</expirationDate>
</creditCard>
</payment>
<customerPaymentProfileId>" . $gatewayids[1] . "</customerPaymentProfileId>
</paymentProfile>
</updateCustomerPaymentProfileRequest>";
		$data = curlCall( $url, $xml, array( "HEADER" => array( "Content-Type: text/xml" ) ) );
		logTransaction( "Authorize.net CIM Remote Storage", $data, "Address Update" );
		$gatewayids[2] = $nameaddresshash;
		update_query( "tblclients", array( "gatewayid" => implode( ",", $gatewayids ) ), array( "id" => $params['clientdetails']['userid'] ) );
	}

	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">
<merchantAuthentication>
<name>" . $params['loginid'] . "</name>
<transactionKey>" . $params['transkey'] . "</transactionKey>
</merchantAuthentication>
<transaction>
<profileTransAuthCapture>
<amount>" . $params['amount'] . "</amount>
<customerProfileId>" . $gatewayids[0] . "</customerProfileId>
<customerPaymentProfileId>" . $gatewayids[1] . "</customerPaymentProfileId>
<order>
<invoiceNumber>" . $params['invoiceid'] . "</invoiceNumber>
</order>
<recurringBilling>false</recurringBilling>
";

	if ($params['cccvv']) {
		$xml .= "<cardCode>" . $params['cccvv'] . "</cardCode>
";
	}

	$xml .= "</profileTransAuthCapture>
</transaction>
<extraOptions><![CDATA[x_customer_ip=" . $remote_ip . "]]></extraOptions>
</createCustomerProfileTransactionRequest>";
	$data = curlCall( $url, $xml, array( "HEADER" => array( "Content-Type: text/xml" ) ) );
	$xmldata = XMLtoArray( $data );

	if ($xmldata['CREATECUSTOMERPROFILETRANSACTIONRESPONSE']['MESSAGES']['RESULTCODE'] == "Ok") {
		$transid = $xmldata['CREATECUSTOMERPROFILETRANSACTIONRESPONSE']['DIRECTRESPONSE'];
		$transid = explode( ",", $transid );
		$transid = $transid[6];
		return array( "status" => "success", "transid" => $transid, "rawdata" => $data );
	}

	return array( "status" => "error", "rawdata" => $data );
}


function authorizecim_storeremote($params) {
	$url = ($params['testmode'] ? "https://apitest.authorize.net/xml/v1/request.api" : "https://api.authorize.net/xml/v1/request.api");
	$gatewayids = explode( ",", $params['gatewayid'] );

	if ($params['action'] == "delete") {
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<deleteCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">
<merchantAuthentication>
<name>" . $params['loginid'] . "</name>
<transactionKey>" . $params['transkey'] . "</transactionKey>
</merchantAuthentication>
<customerProfileId>" . $gatewayids[0] . "</customerProfileId>
</deleteCustomerProfileRequest>";
		$data = curlCall( $url, $xml, array( "HEADER" => array( "Content-Type: text/xml" ) ) );
		$xmldata = XMLtoArray( $data );
		$debugdata = array( "Action" => "DeleteCard", "XMLData" => $data );
		return array( "status" => "success", "rawdata" => $debugdata );
	}


	if ($params['action'] == "update") {
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<updateCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">
<merchantAuthentication>
<name>" . $params['loginid'] . "</name>
<transactionKey>" . $params['transkey'] . "</transactionKey>
</merchantAuthentication>
<customerProfileId>" . $gatewayids[0] . "</customerProfileId>
<paymentProfile>
<billTo>
<firstName><![CDATA[" . $params['clientdetails']['firstname'] . "]]></firstName>
<lastName><![CDATA[" . $params['clientdetails']['lastname'] . "]]></lastName>
<company><![CDATA[" . $params['clientdetails']['companyname'] . "]]></company>
<address><![CDATA[" . $params['clientdetails']['address1'] . "]]></address>
<city><![CDATA[" . $params['clientdetails']['city'] . "]]></city>
<state><![CDATA[" . $params['clientdetails']['state'] . "]]></state>
<zip><![CDATA[" . $params['clientdetails']['postcode'] . "]]></zip>
<country><![CDATA[" . $params['clientdetails']['country'] . "]]></country>
<phoneNumber>" . $params['clientdetails']['phonenumber'] . "</phoneNumber>
<faxNumber></faxNumber>
</billTo>
<payment>
<creditCard>
<cardNumber>" . $params['cardnum'] . "</cardNumber>
<expirationDate>20" . substr( $params['cardexp'], 2, 2 ) . "-" . substr( $params['cardexp'], 0, 2 ) . "</expirationDate>
";

		if ($params['cccvv']) {
			$xml .= "<cardCode>" . $params['cccvv'] . "</cardCode>
";
		}

		$xml .= "</creditCard>
</payment>
<customerPaymentProfileId>" . $gatewayids[1] . "</customerPaymentProfileId>
</paymentProfile>
</updateCustomerPaymentProfileRequest>";
		$data = curlCall( $url, $xml, array( "HEADER" => array( "Content-Type: text/xml" ) ) );
		$xmldata = XMLtoArray( $data );
		$debugdata = array( "Action" => "UpdateCustomer", "XMLData" => $data );

		if ($xmldata['UPDATECUSTOMERPAYMENTPROFILERESPONSE']['MESSAGES']['RESULTCODE'] == "Ok") {
			
			$nameaddresshash = md5( $params['clientdetails']['firstname'] . $params['clientdetails']['lastname'] . $params['clientdetails']['address1'] . $params['clientdetails']['city'] . $params['clientdetails']['state'] . $params['clientdetails']['postcode'] . $params['clientdetails']['country'] );
			$gatewayid = $gatewayids[0] . "," . $gatewayids[1] . "," . $nameaddresshash;
			return array( "status" => "success", "gatewayid" => $gatewayid, "rawdata" => $debugdata );
		}


		if ($xmldata['UPDATECUSTOMERPAYMENTPROFILERESPONSE']['MESSAGES']['MESSAGE']['TEXT'] == "The record cannot be found.") {
			$params['gatewayid'] = "";
		}
		else {
			return array( "status" => "failed", "rawdata" => $debugdata );
		}
	}


	if ($params['action'] == "create") {
		$validationmode = ($params['validationmode'] == "none" ? "none" : "liveMode");
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<createCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">
<merchantAuthentication>
<name>" . $params['loginid'] . "</name>
<transactionKey>" . $params['transkey'] . "</transactionKey>
</merchantAuthentication>
<profile>
<merchantCustomerId>" . $params['clientdetails']['userid'] . rand( 100000, 999999 ) . "</merchantCustomerId>
<email>" . $params['clientdetails']['email'] . "</email>
<paymentProfiles>
<customerType>individual</customerType>
<billTo>
<firstName><![CDATA[" . $params['clientdetails']['firstname'] . "]]></firstName>
<lastName><![CDATA[" . $params['clientdetails']['lastname'] . "]]></lastName>
<company><![CDATA[" . $params['clientdetails']['companyname'] . "]]></company>
<address><![CDATA[" . $params['clientdetails']['address1'] . "]]></address>
<city><![CDATA[" . $params['clientdetails']['city'] . "]]></city>
<state><![CDATA[" . $params['clientdetails']['state'] . "]]></state>
<zip><![CDATA[" . $params['clientdetails']['postcode'] . "]]></zip>
<country><![CDATA[" . $params['clientdetails']['country'] . "]]></country>
<phoneNumber>" . $params['clientdetails']['phonenumber'] . "</phoneNumber>
<faxNumber></faxNumber>
</billTo>
<payment>
<creditCard>
<cardNumber>" . $params['cardnum'] . "</cardNumber>
<expirationDate>20" . substr( $params['cardexp'], 2, 2 ) . "-" . substr( $params['cardexp'], 0, 2 ) . "</expirationDate>
";

		if ($params['cccvv']) {
			$xml .= "<cardCode>" . $params['cccvv'] . "</cardCode>
";
		}

		$xml .= "</creditCard>
</payment>
</paymentProfiles>
</profile>
<validationMode>" . $validationmode . "</validationMode>
</createCustomerProfileRequest>";
		$data = curlCall( $url, $xml, array( "HEADER" => array( "Content-Type: text/xml" ) ) );
		$xmldata = XMLtoArray( $data );
		$debugdata = array( "Action" => "CreateCustomer", "XMLData" => $data );

		if ($xmldata['CREATECUSTOMERPROFILERESPONSE']['MESSAGES']['RESULTCODE'] == "Ok") {
			$customerprofileid = $xmldata['CREATECUSTOMERPROFILERESPONSE']['CUSTOMERPROFILEID'];
			$customerpaymentprofileid = $xmldata['CREATECUSTOMERPROFILERESPONSE']['CUSTOMERPAYMENTPROFILEIDLIST']['NUMERICSTRING'];
			
			$nameaddresshash = md5( $params['clientdetails']['firstname'] . $params['clientdetails']['lastname'] . $params['clientdetails']['address1'] . $params['clientdetails']['city'] . $params['clientdetails']['state'] . $params['clientdetails']['postcode'] . $params['clientdetails']['country'] );
			$gatewayid = $customerprofileid . "," . $customerpaymentprofileid . "," . $nameaddresshash;
			return array( "status" => "success", "gatewayid" => $gatewayid, "rawdata" => $debugdata );
		}


		if ($xmldata['CREATECUSTOMERPROFILERESPONSE']['MESSAGES']['MESSAGE']['CODE'] == "E00039") {
		}

		return array( "status" => "failed", "rawdata" => $debugdata );
	}

	return array( "status" => "skipped", "rawdata" => array( "Error" => "No Action Found" ) );
}


function authorizecim_refund($params) {
	global $CONFIG;

	if ($params['testmode']) {
		$url = "https://apitest.authorize.net/xml/v1/request.api";
	}
	else {
		$url = "https://api.authorize.net/xml/v1/request.api";
	}

	$gatewayids = explode( ",", $params['gatewayid'] );
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">
<merchantAuthentication>
<name>" . $params['loginid'] . "</name>
<transactionKey>" . $params['transkey'] . "</transactionKey>
</merchantAuthentication>
<transaction>
<profileTransRefund>
<amount>" . $params['amount'] . "</amount>
<customerProfileId>" . $gatewayids[0] . "</customerProfileId>
<customerPaymentProfileId>" . $gatewayids[1] . "</customerPaymentProfileId>
<order>
<invoiceNumber>" . $params['invoiceid'] . "</invoiceNumber>
</order>
<transId>" . $params['transid'] . "</transId>
</profileTransRefund>
</transaction>
<extraOptions><![CDATA[x_customer_ip=" . $remote_ip . "]]></extraOptions>
</createCustomerProfileTransactionRequest>";
	$data = curlCall( $url, $xml, array( "HEADER" => array( "Content-Type: text/xml" ) ) );
	$xmldata = XMLtoArray( $data );

	if ($xmldata['CREATECUSTOMERPROFILETRANSACTIONRESPONSE']['MESSAGES']['RESULTCODE'] == "Ok") {
		$transid = $xmldata['CREATECUSTOMERPROFILETRANSACTIONRESPONSE']['DIRECTRESPONSE'];
		$transid = explode( ",", $transid );
		$transid = $transid[6];
		return array( "status" => "success", "transid" => $transid, "rawdata" => $data );
	}

	return array( "status" => "error", "rawdata" => $data );
}


function authorizecim_adminstatusmsg($vars) {
	$gatewayids = get_query_val( "tblclients", "gatewayid", array( "id" => $vars['userid'] ) );

	if ($gatewayids) {
		$gatewayids = explode( ",", $gatewayids );
		return array( "type" => "info", "title" => "Authorize.net CIM Profile", "msg" => "This customer has a remote Authorize.net CIM Profile storing their card details for automated recurring billing with ID " . $gatewayids[0] );
	}

}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>