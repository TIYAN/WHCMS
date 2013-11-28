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

if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

echo "\r\n<h2 id=\"mergefieldstoggle\">";
echo $aInt->lang( "mergefields", "title" );
echo "</h2>\r\n\r\n<div id=\"mergefields\" style=\"border:1px solid #8FBCE9;background:#ffffff;color:#000000;padding:5px;height:300px;overflow:auto;font-size:10px;z-index:10;\">\r\n\r\n<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"50%\" valign=\"top\">\r\n\r\n";
$customfields = run_hook( "EmailTplMergeFields", array( "type" => $type ) );

if (count( $customfields )) {
	echo "<b>Custom Defined Merge Fields</b><br /><table>";
	foreach ($customfields as $fields) {
		foreach ($fields as $k => $v) {
			echo "<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('".$k."');return false\">".$v."</a></td><td>{\$".$k."}</td></tr>";
		}
	}

	echo "</table><br />";
}


if ($type == "support") {
    echo "<b>";
    echo $aInt->lang( "mergefields", "support" );
    echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('ticket_id');return false\">";
    echo $aInt->lang( "fields", "id" );
    echo "</a></td><td>{\$ticket_id}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_department');return false\">";
    echo $aInt->lang( "support", "department" );
    echo "</a></td><td>{\$ticket_department}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_date_opened');return false\">";
    echo $aInt->lang( "mergefields", "dateopened" );
    echo "</a></td><td>{\$ticket_date_opened}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_subject');return false\">";
    echo $aInt->lang( "fields", "subject" );
    echo "</a></td><td>{\$ticket_subject}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_message');return false\">";
    echo $aInt->lang( "mergefields", "message" );
    echo "</a></td><td>{\$ticket_message}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_status');return false\">";
    echo $aInt->lang( "fields", "status" );
    echo "</a></td><td>{\$ticket_status}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_priority');return false\">";
    echo $aInt->lang( "support", "priority" );
    echo "</a></td><td>{\$ticket_priority}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_url');return false\">";
    echo $aInt->lang( "mergefields", "ticketurl" );
    echo "</a></td><td>{\$ticket_url}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_link');return false\">";
    echo $aInt->lang( "mergefields", "ticketlink" );
    echo "</a></td><td>{\$ticket_link}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_auto_close_time');return false\">";
    echo $aInt->lang( "mergefields", "autoclosetime" );
    echo "</a></td><td>{\$ticket_auto_close_time}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_kb_auto_suggestions');return false\">";
    echo $aInt->lang( "mergefields", "kbautosuggestions" );
    echo "</a></td><td>{\$ticket_kb_auto_suggestions}</td></tr>\r\n</table><br />\r\n";
}
else {
	if ($type == "affiliate") {
    echo "<b>";
    echo $aInt->lang( "mergefields", "affiliate" );
    echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('affiliate_total_visits');return false\">";
    echo $aInt->lang( "mergefields", "noreferrals" );
    echo "</a></td><td>{\$affiliate_total_visits}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('affiliate_balance');return false\">";
    echo $aInt->lang( "mergefields", "earnbalance" );
    echo "</a></td><td>{\$affiliate_balance}</td></tr>\r\n<tr><td nowrap><a href=\"#\" onclick=\"insertMergeField('affiliate_withdrawn');return false\">";
    echo $aInt->lang( "mergefields", "withdrawnamount" );
    echo "</a></td><td>{\$affiliate_withdrawn}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('affiliate_referrals_table');return false\">";
    echo $aInt->lang( "mergefields", "refdetails" );
    echo "</a></td><td>{\$affiliate_referrals_table}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('affiliate_referral_url');return false\">";
    echo $aInt->lang( "mergefields", "refurl" );
    echo "</a></td><td>{\$affiliate_referral_url}</td></tr>\r\n</table><br />\r\n";
	}
	else {
		if ($type == "addon") {
    echo "<b>";
    echo $aInt->lang( "mergefields", "addon" );
    echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('addon_reg_date');return false\">";
    echo $aInt->lang( "fields", "signupdate" );
    echo "</a></td><td>{\$addon_reg_date}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('addon_product');return false\">";
    echo $aInt->lang( "mergefields", "parentproduct" );
    echo "</a></td><td>{\$addon_product}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('addon_domain');return false\">";
    echo $aInt->lang( "mergefields", "parentdomain" );
    echo "</a></td><td>{\$addon_domain}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('addon_name');return false\">";
    echo $aInt->lang( "fields", "name" );
    echo "</a></td><td>{\$addon_name}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('addon_setup_fee');return false\">";
    echo $Var_2664->lang( "fields", "setupfee" );
    echo "</a></td><td>{\$addon_setup_fee}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('addon_recurring_amount');return false\">";
    echo $aInt->lang( "fields", "recurringamount" );
    echo "</a></td><td>{\$addon_recurring_amount}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('addon_billing_cycle');return false\">";
    echo $aInt->lang( "fields", "billingcycle" );
    echo "</a></td><td>{\$addon_billing_cycle}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('addon_payment_method');return false\">";
    echo $aInt->lang( "fields", "paymentmethod" );
    echo "</a></td><td>{\$addon_payment_method}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('addon_next_due_date');return false\">";
    echo $aInt->lang( "fields", "nextduedate" );
    echo "</a></td><td>{\$addon_next_due_date}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('addon_status');return false\">";
    echo $aInt->lang( "fields", "status" );
    echo "</a></td><td>{\$addon_status}</td></tr>\r\n</table><br />\r\n";
		}
		else {
			if ($type == "domain") {
				echo "<b>";
				echo $aInt->lang( "mergefields", "domain" );
				echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('addon_reg_date');return false\">";
				echo $aInt->lang( "fields", "orderid" );
				echo "</a></td><td>{\$domain_order_id}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_reg_date');return false\">";
				echo $aInt->lang( "fields", "signupdate" );
				echo "</a></td><td>{\$domain_reg_date}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_name');return false\">";
				echo $aInt->lang( "fields", "domain" );
				echo "</a></td><td>{\$domain_name}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_sld');return false\">";
				echo $aInt->lang( "mergefields", "sld" );
				echo "</a></td><td>{\$domain_sld}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_tld');return false\">";
				echo $aInt->lang( "mergefields", "tld" );
				echo "</a></td><td>{\$domain_tld}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_reg_period');return false\">";
				echo $aInt->lang( "fields", "regperiod" );
				echo "</a></td><td>{\$domain_reg_period}</td></tr>\r\n<tr><td nowrap><a href=\"#\" onclick=\"insertMergeField('domain_first_payment_amount');return false\">";
				echo $aInt->lang( "fields", "firstpaymentamount" );
				echo "</a></td><td>{\$domain_first_payment_amount}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_recurring_amount');return false\">";
				echo $aInt->lang( "fields", "recurringamount" );
				echo "</a></td><td>{\$domain_recurring_amount}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_next_due_date');return false\">";
				echo $aInt->lang( "fields", "nextduedate" );
				echo "</a></td><td>{\$domain_next_due_date}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_expiry_date');return false\">";
				echo $aInt->lang( "fields", "expirydate" );
				echo "</a></td><td>{\$domain_expiry_date}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_registrar');return false\">";
				echo $aInt->lang( "fields", "registrar" );
				echo "</a></td><td>{\$domain_registrar}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_days_until_expiry');return false\">";
				echo $aInt->lang( "mergefields", "daysexpiry" );
				echo "</a></td><td>{\$domain_days_until_expiry}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_days_until_nextdue');return false\">";
				echo $aInt->lang( "mergefields", "daysnextdue" );
				echo "</a></td><td>{\$domain_days_until_nextdue}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_status');return false\">";
				echo $aInt->lang( "fields", "status" );
				echo "</a></td><td>{\$domain_status}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_dns_management');return false\">";
				echo $aInt->lang( "domains", "dnsmanagement" );
				echo "</a></td><td>{\$domain_dns_management}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_email_forwarding');return false\">";
				echo $aInt->lang( "domains", "emailforwarding" );
				echo "</a></td><td>{\$domain_email_forwarding}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_id_protection');return false\">";
				echo $aInt->lang( "domains", "idprotection" );
				echo "</a></td><td>{\$domain_id_protection}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_do_not_renew');return false\">";
				echo $aInt->lang( "mergefields", "donotrenew" );
				echo "</a></td><td>{\$domain_do_not_renew}</td></tr>\r\n</table><br />\r\n";
			}
			else {
				if ($type == "invoice") {
					echo "<b>";
					echo $aInt->lang( "mergefields", "invoice" );
					echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('invoice_id');return false\">";
					echo $aInt->lang( "fields", "invoiceid" );
					echo "</a></td><td>{\$invoice_id}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_num');return false\">";
					echo $aInt->lang( "fields", "invoicenum" );
					echo "</a></td><td>{\$invoice_num}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_date_created');return false\">";
					echo $aInt->lang( "mergefields", "datecreated" );
					echo "</a></td><td>{\$invoice_date_created}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_date_due');return false\">";
					echo $aInt->lang( "fields", "duedate" );
					echo "</a></td><td>{\$invoice_date_due}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_date_paid');return false\">";
					echo $aInt->lang( "fields", "datepaid" );
					echo "</a></td><td>{\$invoice_date_paid}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_items');return false\">";
					echo $aInt->lang( "mergefields", "invoiceitems" );
					echo "</a></td><td>{\$invoice_items}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_html_contents');return false\">";
					echo $aInt->lang( "mergefields", "invoiceitemshtml" );
					echo "</a></td><td>{\$invoice_html_contents}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_subtotal');return false\">";
					echo $aInt->lang( "fields", "subtotal" );
					echo "</a></td><td>{\$invoice_subtotal}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_tax');return false\">";
					echo $aInt->lang( "fields", "tax" );
					echo "</a></td><td>{\$invoice_tax}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_tax_rate');return false\">";
					echo $aInt->lang( "fields", "taxrate" );
					echo "</a></td><td>{\$invoice_tax_rate}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_credit');return false\">";
					echo $aInt->lang( "fields", "credit" );
					echo "</a></td><td>{\$invoice_credit}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_total');return false\">";
					echo $aInt->lang( "fields", "total" );
					echo "</a></td><td>{\$invoice_total}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_amount_paid');return false\">";
					echo $aInt->lang( "mergefields", "amountpaid" );
					echo "</a></td><td>{\$invoice_amount_paid}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_balance');return false\">";
					echo $aInt->lang( "fields", "balance" );
					echo "</a></td><td>{\$invoice_balance}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_last_payment_amount');return false\">";
					echo $aInt->lang( "mergefields", "lastpaymentamount" );
					echo "</a></td><td>{\$invoice_last_payment_amount}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_last_payment_transid');return false\">";
					echo $aInt->lang( "mergefields", "lastpaymenttransid" );
					echo "</a></td><td>{\$invoice_last_payment_transid}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_payment_method');return false\">";
					echo $aInt->lang( "fields", "paymentmethod" );
					echo "</a></td><td>{\$invoice_payment_method}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_payment_link');return false\">";
					echo $aInt->lang( "mergefields", "paymentlink" );
					echo "</a></td><td>{\$invoice_payment_link}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_subscription_id');return false\">";
					echo $aInt->lang( "fields", "subscriptionid" );
					echo "</a></td><td>{\$invoice_subscription_id}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_status');return false\">";
					echo $aInt->lang( "fields", "status" );
					echo "</a></td><td>{\$invoice_status}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_link');return false\">";
					echo $aInt->lang( "mergefields", "invoicelink" );
					echo "</a></td><td>{\$invoice_link}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_previous_balance');return false\">";
					echo $aInt->lang( "mergefields", "prevbalance" );
					echo "</a></td><td>{\$invoice_previous_balance}</td></tr>\r\n<tr><td nowrap><a href=\"#\" onclick=\"insertMergeField('invoice_total_balance_due');return false\">";
					echo $aInt->lang( "mergefields", "invoicesbalance" );
					echo "</a></td><td>{\$invoice_total_balance_due}</td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_notes');return false\">";
					echo $aInt->lang( "fields", "notes" );
					echo "</a></td><td>{\$invoice_notes}</td></tr>\r\n</table><br />\r\n";
				}
				else {
					if ($type == "product") {
						echo "<b>";
						echo $aInt->lang( "mergefields", "product" );
						echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('service_order_id');return false\">";
						echo $aInt->lang( "fields", "orderid" );
						echo "</td><td>{\$service_order_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_id');return false\">";
						echo $aInt->lang( "fields", "id" );
						echo "</td><td>{\$service_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_reg_date');return false\">";
						echo $aInt->lang( "fields", "signupdate" );
						echo "</td><td>{\$service_reg_date}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_product_name');return false\">";
						echo $aInt->lang( "mergefields", "prodname" );
						echo "</td><td>{\$service_product_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_product_description');return false\">";
						echo $aInt->lang( "mergefields", "proddesc" );
						echo "</td><td>{\$service_product_description}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_domain');return false\">";
						echo $aInt->lang( "fields", "domain" );
						echo "</td><td>{\$service_domain}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_config_options');return false\">";
						echo $aInt->lang( "mergefields", "configoptions" );
						echo "</td><td>{\$service_config_options}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_server_name');return false\">";
						echo $aInt->lang( "mergefields", "servername" );
						echo "</td><td>{\$service_server_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_server_hostname');return false\">";
						echo $aInt->lang( "mergefields", "serverhostname" );
						echo "</td><td>{\$service_server_hostname}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_server_ip');return false\">";
						echo $aInt->lang( "mergefields", "serverip" );
						echo "</td><td>{\$service_server_ip}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_dedicated_ip');return false\">";
						echo $aInt->lang( "mergefields", "dedicatedip" );
						echo "</td><td>{\$service_dedicated_ip}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_assigned_ips');return false\">";
						echo $aInt->lang( "mergefields", "assignedips" );
						echo "</td><td>{\$service_assigned_ips}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_ns1');return false\">";
						echo $aInt->lang( "mergefields", "nameserver" );
						echo " 1</td><td>{\$service_ns1}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_ns2');return false\">";
						echo $aInt->lang( "mergefields", "nameserver" );
						echo " 2</td><td>{\$service_ns2}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_ns3');return false\">";
						echo $aInt->lang( "mergefields", "nameserver" );
						echo " 3</td><td>{\$service_ns3}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_ns4');return false\">";
						echo $aInt->lang( "mergefields", "nameserver" );
						echo " 4</td><td>{\$service_ns4}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_ns1_ip');return false\">";
						echo $aInt->lang( "mergefields", "nameserver" );
						echo " 1 ";
						echo $aInt->lang( "mergefields", "ip" );
						echo "</td><td>{\$service_ns1_ip}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_ns2_ip');return false\">";
						echo $aInt->lang( "mergefields", "nameserver" );
						echo " 2 ";
						echo $aInt->lang( "mergefields", "ip" );
						echo "</td><td>{\$service_ns2_ip}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_ns3_ip');return false\">";
						echo $aInt->lang( "mergefields", "nameserver" );
						echo " 3 ";
						echo $aInt->lang( "mergefields", "ip" );
						echo "</td><td>{\$service_ns3_ip}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_ns4_ip');return false\">";
						echo $aInt->lang( "mergefields", "nameserver" );
						echo " 4 ";
						echo $aInt->lang( "mergefields", "ip" );
						echo "</td><td>{\$service_ns4_ip}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_payment_method');return false\">";
						echo $aInt->lang( "fields", "paymentmethod" );
						echo "</td><td>{\$service_payment_method}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_first_payment_amount');return false\">";
						echo $aInt->lang( "fields", "firstpaymentamount" );
						echo "</td><td>{\$service_first_payment_amount}</a></td></tr>\r\n<tr><td nowrap><a href=\"#\" onclick=\"insertMergeField('service_recurring_amount');return false\">";
						echo $aInt->lang( "mergefields", "recurringpayment" );
						echo "</td><td>{\$service_recurring_amount}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_billing_cycle');return false\">";
						echo $aInt->lang( "fields", "billingcycle" );
						echo "</td><td>{\$service_billing_cycle}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_next_due_date');return false\">";
						echo $aInt->lang( "fields", "nextduedate" );
						echo "</td><td>{\$service_next_due_date}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_status');return false\">";
						echo $aInt->lang( "fields", "status" );
						echo "</td><td>{\$service_status}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_suspension_reason');return false\">";
						echo $aInt->lang( "mergefields", "suspreason" );
						echo "</td><td>{\$service_suspension_reason}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_cancellation_type');return false\">";
						echo $aInt->lang( "mergefields", "canceltype" );
						echo "</td><td>{\$service_cancellation_type}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_username');return false\">";
						echo $aInt->lang( "fields", "username" );
						echo "</td><td>{\$service_username}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_password');return false\">";
						echo $aInt->lang( "fields", "password" );
						echo "</td><td>{\$service_password}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_custom_fields');return false\">";
						echo $aInt->lang( "mergefields", "customfieldsarray" );
						echo "</td><td>{\$service_custom_fields.1}</a></td></tr>\r\n</table><br />\r\n";
					}
					else {
						if ($type == "admin") {
							if ($name == "New Order Notification") {
								echo "<b>";
								echo $aInt->lang( "mergefields", "order" );
								echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('client_id');return false\">";
								echo $aInt->lang( "fields", "orderid" );
								echo "</td><td>{\$order_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('order_number');return false\">";
								echo $aInt->lang( "fields", "ordernum" );
								echo "</td><td>{\$order_number}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('order_date');return false\">";
								echo $aInt->lang( "mergefields", "orderdate" );
								echo "</td><td>{\$order_date}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('order_items');return false\">";
								echo $aInt->lang( "orders", "items" );
								echo "</td><td>{\$order_items}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('order_total');return false\">";
								echo $aInt->lang( "mergefields", "duetoday" );
								echo "</td><td>{\$order_total}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('order_payment_method');return false\">";
								echo $aInt->lang( "fields", "paymentmethod" );
								echo "</td><td>{\$order_payment_method}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('invoice_id');return false\">";
								echo $aInt->lang( "fields", "invoiceid" );
								echo "</td><td>{\$invoice_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('order_notes');return false\">";
								echo $aInt->lang( "mergefields", "ordernotes" );
								echo "</td><td>{\$order_notes}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_ip');return false\">";
								echo $aInt->lang( "mergefields", "clientip" );
								echo "</td><td>{\$client_ip}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_hostname');return false\">";
								echo $aInt->lang( "mergefields", "clienthostname" );
								echo "</td><td>{\$client_hostname}</a></td></tr>\r\n</table><br />\r\n<b>";
								echo $aInt->lang( "mergefields", "client" );
								echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('client_id');return false\">";
								echo $aInt->lang( "fields", "id" );
								echo "</td><td>{\$client_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_first_name');return false\">";
								echo $aInt->lang( "fields", "firstname" );
								echo "</td><td>{\$client_first_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_last_name');return false\">";
								echo $aInt->lang( "fields", "lastname" );
								echo "</td><td>{\$client_last_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_company_name');return false\">";
								echo $aInt->lang( "fields", "companyname" );
								echo "</td><td>{\$client_company_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_email');return false\">";
								echo $aInt->lang( "fields", "email" );
								echo "</td><td>{\$client_email}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_address1');return false\">";
								echo $aInt->lang( "fields", "address1" );
								echo "</td><td>{\$client_address1}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_address2');return false\">";
								echo $aInt->lang( "fields", "address2" );
								echo "</td><td>{\$client_address2}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_city');return false\">";
								echo $aInt->lang( "fields", "city" );
								echo "</td><td>{\$client_city}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_state');return false\">";
								echo $aInt->lang( "fields", "state" );
								echo "</td><td>{\$client_state}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_postcode');return false\">";
								echo $aInt->lang( "fields", "postcode" );
								echo "</td><td>{\$client_postcode}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_country');return false\">";
								echo $aInt->lang( "fields", "country" );
								echo "</td><td>{\$client_country}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_phonenumber');return false\">";
								echo $aInt->lang( "fields", "phonenumber" );
								echo "</td><td>{\$client_phonenumber}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_customfields');return false\">";
								echo $aInt->lang( "customfields", "clienttitle" );
								echo "</td><td>{\$client_customfields}</a></td></tr>\r\n</table><br />\r\n";
							}
							else {
								if (( ( ( $name == "Automatic Setup Successful" || $name == "Automatic Setup Failed" ) || $name == "Service Unsuspension Failed" ) || $name == "Service Unsuspension Successful" )) {
									echo "<b>";
									echo $aInt->lang( "mergefields", "service" );
									echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('client_id');return false\">";
									echo $aInt->lang( "fields", "clientid" );
									echo "</td><td>{\$client_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_id');return false\">";
									echo $aInt->lang( "mergefields", "serviceid" );
									echo "</td><td>{\$service_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_product');return false\">";
									echo $aInt->lang( "fields", "product" );
									echo "</td><td>{\$service_product}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('service_domain');return false\">";
									echo $aInt->lang( "fields", "domain" );
									echo "</td><td>{\$service_domain}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('error_msg');return false\">";
									echo $aInt->lang( "mergefields", "errormsg" );
									echo "</td><td>{\$error_msg}</a></td></tr>\r\n</table><br />\r\n<b>";
									echo $aInt->lang( "mergefields", "domain" );
									echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('client_id');return false\">";
									echo $aInt->lang( "fields", "clientid" );
									echo "</td><td>{\$client_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_id');return false\">";
									echo $aInt->lang( "mergefields", "domainid" );
									echo "</td><td>{\$domain_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_type');return false\">";
									echo $aInt->lang( "domains", "regtype" );
									echo "</td><td>{\$domain_type}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('domain_name');return false\">";
									echo $aInt->lang( "mergefields", "domainname" );
									echo "</td><td>{\$domain_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('error_msg');return false\">";
									echo $aInt->lang( "mergefields", "errormsg" );
									echo "</td><td>{\$error_msg}</a></td></tr>\r\n</table><br />\r\n";
								}
								else {
									if (( $name == "Support Ticket Opened" || $name == "Support Ticket Response" )) {
										echo "<b>";
										echo $aInt->lang( "mergefields", "support" );
										echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('ticket_id');return false\">";
										echo $aInt->lang( "fields", "id" );
										echo "</td><td>{\$ticket_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_tid');return false\">";
										echo $aInt->lang( "support", "ticketid" );
										echo "</td><td>{\$ticket_tid}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_id');return false\">";
										echo $aInt->lang( "fields", "clientid" );
										echo "</td><td>{\$client_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_name');return false\">";
										echo $aInt->lang( "fields", "clientname" );
										echo "</td><td>{\$client_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_department');return false\">";
										echo $aInt->lang( "support", "department" );
										echo "</td><td>{\$ticket_department}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_subject');return false\">";
										echo $aInt->lang( "fields", "subject" );
										echo "</td><td>{\$ticket_subject}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_priority');return false\">";
										echo $aInt->lang( "support", "priority" );
										echo "</td><td>{\$ticket_priority}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('ticket_message');return false\">";
										echo $aInt->lang( "mergefields", "message" );
										echo "</td><td>{\$ticket_message}</a></td></tr>\r\n</table><br />\r\n";
									}
								}
							}
						}
					}
				}
			}
		}
	}
}


if (( $type != "support" && $type != "admin" )) {
	echo "<b>";
	echo $aInt->lang( "mergefields", "client" );
	echo "</b><br />\r\n<table>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('client_id');return false\">";
	echo $aInt->lang( "fields", "id" );
	echo "</td><td>{\$client_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_name');return false\">";
	echo $aInt->lang( "fields", "clientname" );
	echo "</td><td>{\$client_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_first_name');return false\">";
	echo $aInt->lang( "fields", "firstname" );
	echo "</td><td>{\$client_first_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_last_name');return false\">";
	echo $aInt->lang( "fields", "lastname" );
	echo "</td><td>{\$client_last_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_company_name');return false\">";
	echo $aInt->lang( "fields", "companyname" );
	echo "</td><td>{\$client_company_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_email');return false\">";
	echo $aInt->lang( "fields", "email" );
	echo "</td><td>{\$client_email}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_address1');return false\">";
	echo $aInt->lang( "fields", "address1" );
	echo "</td><td>{\$client_address1}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_address2');return false\">";
	echo $aInt->lang( "fields", "address2" );
	echo "</td><td>{\$client_address2}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_city');return false\">";
	echo $aInt->lang( "fields", "city" );
	echo "</td><td>{\$client_city}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_state');return false\">";
	echo $aInt->lang( "fields", "state" );
	echo "</td><td>{\$client_state}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_postcode');return false\">";
	echo $aInt->lang( "fields", "postcode" );
	echo "</td><td>{\$client_postcode}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_country');return false\">";
	echo $aInt->lang( "fields", "country" );
	echo "</td><td>{\$client_country}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_phonenumber');return false\">";
	echo $aInt->lang( "fields", "phonenumber" );
	echo "</td><td>{\$client_phonenumber}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_password');return false\">";
	echo $aInt->lang( "fields", "password" );
	echo "</td><td>{\$client_password}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_signup_date');return false\">";
	echo $aInt->lang( "fields", "signupdate" );
	echo " </td><td>{\$client_signup_date}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_credit');return false\">";
	echo $aInt->lang( "clients", "creditbalance" );
	echo "</td><td>{\$client_credit}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_cc_type');return false\">";
	echo $aInt->lang( "fields", "cardtype" );
	echo "</td><td>{\$client_cc_type}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_cc_number');return false\">";
	echo $aInt->lang( "fields", "cardlast4" );
	echo "</td><td>{\$client_cc_number}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_cc_expiry');return false\">";
	echo $aInt->lang( "fields", "expdate" );
	echo "</td><td>{\$client_cc_expiry}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_gateway_id');return false\">";
	echo $aInt->lang( "fields", "gatewayid" );
	echo "</td><td>{\$client_gateway_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_group_id');return false\">";
	echo $aInt->lang( "mergefields", "clientgroupid" );
	echo "</td><td>{\$client_group_id}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_group_name');return false\">";
	echo $aInt->lang( "mergefields", "clientgroupname" );
	echo " </td><td>{\$client_group_name}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_due_invoices_balance');return false\">";
	echo $aInt->lang( "mergefields", "invoicesbalance" );
	echo " </td><td>{\$client_due_invoices_balance}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_custom_fields');return false\">";
	echo $aInt->lang( "mergefields", "customfieldsarray" );
	echo "</td><td>{\$client_custom_fields.1}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('client_status');return false\">";
	echo $aInt->lang( "fields", "status" );
	echo "</td><td>{\$client_status}</a></td></tr>\r\n</table><br />\r\n";
}

echo "<b>";
echo $aInt->lang( "mergefields", "other" );
echo "</b><br />\r\n<table>\r\n\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('company_name');return false\">";
echo $aInt->lang( "fields", "companyname" );
echo "</td><td>{\$company_name}</a></td></tr>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('company_domain');return false\">";
echo $aInt->lang( "fields", "domain" );
echo "</td><td>{\$company_domain}</a></td></tr>\r\n<tr><td width=\"150\"><a href=\"#\" onclick=\"insertMergeField('company_logo_url');return false\">";
echo $aInt->lang( "general", "logourl" );
echo "</td><td>{\$company_logo_url}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('whmcs_url');return false\">";
echo $aInt->lang( "mergefields", "whmcsurl" );
echo "</td><td>{\$whmcs_url}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('whmcs_link');return false\">";
echo $aInt->lang( "mergefields", "whmcslink" );
echo "</td><td>{\$whmcs_link}</a></td></tr>\r\n";

if ($type == "admin") {
	echo "<tr><td><a href=\"#\" onclick=\"insertMergeField('whmcs_admin_url');return false\">";
	echo $aInt->lang( "mergefields", "whmcsadminurl" );
	echo "</td><td>{\$whmcs_admin_url}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('whmcs_admin_link');return false\">";
	echo $aInt->lang( "mergefields", "whmcsadminlink" );
	echo "</td><td>{\$whmcs_admin_link}</a></td></tr>\r\n";
}

echo "<tr><td><a href=\"#\" onclick=\"insertMergeField('unsubscribe_url');return false\">";
echo $aInt->lang( "mergefields", "unsubscribeurl" );
echo "</td><td>{\$unsubscribe_url}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('signature');return false\">";
echo $aInt->lang( "mergefields", "signature" );
echo "</td><td>{\$signature}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('date');return false\">";
echo $aInt->lang( "mergefields", "date" );
echo "</td><td>{\$date}</a></td></tr>\r\n<tr><td><a href=\"#\" onclick=\"insertMergeField('time');return false\">";
echo $aInt->lang( "mergefields", "time" );
echo "</td><td>{\$time}</a></td></tr>\r\n</table><br />\r\n\r\n</td><td width=\"50%\" valign=\"top\">\r\n\r\n<b>";
echo $aInt->lang( "mergefields", "condisplay" );
echo "</b><br />\r\n";
echo $aInt->lang( "mergefields", "condisplay1" );
echo ":<br /><br />\r\n{if $ticket_department eq \"";
echo $aInt->lang( "supportreq", "sales" );
echo "\"}<br />\r\n";
echo $aInt->lang( "mergefields", "condisplay2" );
echo "<br />\r\n{else}<br />\r\n";
echo $aInt->lang( "mergefields", "condisplay3" );
echo "<br />\r\n{/if}<br /><br />\r\n\r\n<b>";
echo $aInt->lang( "mergefields", "looping" );
echo "</b><br />\r\n";
echo $aInt->lang( "mergefields", "looping1" );
echo ":<br /><br />\r\n{foreach from=\$array_data item=data}<br />\r\n{\$data.option}: {\$data.value}<br />\r\n{/foreach}\r\n\r\n</td></tr></table>\r\n\r\n</div>\r\n\r\n<br />";
?>