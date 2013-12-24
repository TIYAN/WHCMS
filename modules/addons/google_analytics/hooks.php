<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 * */

function google_analytics_hook_checkout_tracker($vars) {
	global $CONFIG;

	$modulevars = array();
	$result = select_query( "tbladdonmodules", "", array( "module" => "google_analytics" ) );

	while ($data = mysql_fetch_array( $result )) {
		$value = $data['value'];
		$value = explode( "|", $value );
		$value = trim( $value[0] );
		$modulevars[$data['setting']] = $value;
	}


	if (!$modulevars['code']) {
		return false;
	}

	$orderid = $vars['orderid'];
	$ordernumber = $vars['ordernumber'];
	$invoiceid = $vars['invoiceid'];
	$ispaid = $vars['ispaid'];
	$amount = $subtotal = $vars['amount'];
	$paymentmethod = $vars['paymentmethod'];
	$clientdetails = $vars['clientdetails'];
	$result = select_query( "tblorders", "renewals", array( "id" => $orderid ) );
	$data = mysql_fetch_array( $result );
	$renewals = $data['renewals'];

	if ($invoiceid) {
		$result = select_query( "tblinvoices", "subtotal,tax,tax2,total", array( "id" => $invoiceid ) );
		$data = mysql_fetch_array( $result );
		$subtotal = $data['subtotal'];
		$tax = $data['tax'] + $data['tax2'];
		$total = $data['total'];
	}


	if (isset( $_SESSION['gatracking'][$orderid] )) {
		return false;
	}

	$_SESSION['gatracking'][$orderid] = 1;
	$code = "<script type=\"text/javascript\">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '" . $modulevars['code'] . "']);";

	if ($modulevars['domain']) {
		$code .= "
  _gaq.push(['_setDomainName', '." . $modulevars['domain'] . "']);";
	}

	$code .= "
  _gaq.push(['_trackPageview']);
  _gaq.push(['_addTrans',
    '" . $orderid . "',
    'WHMCS Cart',
    '" . $subtotal . "',
    '" . $tax . "',
    '0',
    '" . $clientdetails['city'] . "',
    '" . $clientdetails['state'] . "',
    '" . $clientdetails['country'] . "'
  ]);
";
	$result = select_query( "tblhosting", "tblhosting.id,tblproducts.id AS pid,tblproducts.name,tblproductgroups.name AS groupname,tblhosting.firstpaymentamount", array( "orderid" => $orderid ), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblproductgroups ON tblproductgroups.id=tblproducts.gid" );

	while ($data = mysql_fetch_array( $result )) {
		$serviceid = $data['id'];
		$itempid = $data['pid'];
		$name = $data['name'];
		$groupname = $data['groupname'];
		$itemamount = $data['firstpaymentamount'];
		$code .= "
  _gaq.push(['_addItem',
    '" . $orderid . "',
    'PID" . $itempid . "',
    '" . $name . "',
    '" . $groupname . "',
    '" . $itemamount . "',
    '1'
  ]);
   ";
	}

	$result = select_query( "tblhostingaddons", "tblhostingaddons.id,tblhostingaddons.addonid,tbladdons.name,tblhostingaddons.setupfee,tblhostingaddons.recurring", array( "orderid" => $orderid ), "", "", "", "tbladdons ON tbladdons.id=tblhostingaddons.addonid" );

	while ($data = mysql_fetch_array( $result )) {
		$aid = $data['id'];
		$addonid = $data['addonid'];
		$name = $data['name'];
		$groupname = $data['groupname'];
		$itemamount = $data['setupfee'] + $data['recurring'];
		$code .= "
  _gaq.push(['_addItem',
    '" . $orderid . "',
    'AID" . $addonid . "',
    '" . $name . "',
    'Addons',
    '" . $itemamount . "',
    '1'
  ]);
   ";
	}

	$result = select_query( "tbldomains", "tbldomains.id,tbldomains.type,tbldomains.domain,tbldomains.firstpaymentamount", array( "orderid" => $orderid ) );

	while ($data = mysql_fetch_array( $result )) {
		$did = $data['id'];
		$regtype = $data['type'];
		$domain = $data['domain'];
		$itemamount = $data['firstpaymentamount'];
		$domainparts = explode( ".", $domain, 2 );
		$code .= "
  _gaq.push(['_addItem',
    '" . $orderid . "',
    'TLD" . strtoupper( $domainparts[1] ) . "',
    '" . $regtype . "',
    'Domain',
    '" . $itemamount . "',
    '1'
  ]);
   ";
	}


	if ($renewals) {
		$renewals = explode( ",", $renewals );
		foreach ($renewals as $renewal) {
			$renewal = explode( "=", $renewal );
			$domainid = $renewal[0];
			$registrationperiod = $renewal[1];
			$result = select_query( "tbldomains", "id,domain,recurringamount", array( "id" => $domainid ) );
			$data = mysql_fetch_array( $result );
			$did = $data['id'];
			$domain = $data['domain'];
			$itemamount = $data['recurringamount'];
			$domainparts = explode( ".", $domain, 2 );
			$code .= "
  _gaq.push(['_addItem',
    '" . $orderid . "',
    'TLD" . strtoupper( $domainparts[1] ) . "',
    'Renewal',
    'Domain',
    '" . $itemamount . "',
    '1'
  ]);
   ";
		}
	}

	$code .= "
  _gaq.push(['_trackTrans']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>";
	return $code;
}


function google_analytics_hook_page_tracking($vars) {
	global $smarty;

	$modulevars = array();
	$result = select_query( "tbladdonmodules", "", array( "module" => "google_analytics" ) );

	while ($data = mysql_fetch_array( $result )) {
		$value = $data['value'];
		$value = explode( "|", $value );
		$value = trim( $value[0] );
		$modulevars[$data['setting']] = $value;
	}


	if (!$modulevars['code']) {
		return false;
	}

	$jscode = "<script type=\"text/javascript\">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '" . $modulevars['code'] . "']);";

	if ($modulevars['domain']) {
		$jscode .= "
  _gaq.push(['_setDomainName', '." . $modulevars['domain'] . "']);";
	}

	$jscode .= "
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>";
	return $jscode;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

add_hook( "ShoppingCartCheckoutCompletePage", 1, "google_analytics_hook_checkout_tracker" );
add_hook( "ClientAreaFooterOutput", 1, "google_analytics_hook_page_tracking" );
?>