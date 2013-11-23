<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!function_exists("emailtpl_template")) {
	function emailtpl_template($tpl_name, $tpl_source, $smarty_obj) {
		$tpl_source = $smarty_obj->get_template_vars($tpl_name);
		return empty($tpl_source) ? false : true;
	}

	function emailtpl_timestamp($tpl_name, $tpl_timestamp, $smarty_obj) {
		return true;
	}

	function emailtpl_secure($tpl_name, $smarty_obj) {
		return true;
	}

	function emailtpl_trusted($tpl_name, $smarty_obj) {
	}

	function sendMessage($func_messagename, $func_id, $extra = "", $displayresult = "", $attachments = "") {
		global $whmcs;
		global $CONFIG;
		global $_LANG;
		global $encryption_key;
		global $currency;
		global $downloads_dir;
		global $fromname;
		global $fromemail;
		global $whmcs;

		$sysurl = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
		$nosavemaillog = false;
		$email_merge_fields = array();

		if ($func_messagename == "defaultnewacc") {
			$result = select_query("tblproducts", "tblproducts.welcomeemail", array("tblhosting.id" => $func_id), "", "", "", "tblhosting ON tblhosting.packageid=tblproducts.id");
			$data = mysql_fetch_array($result);

			if (!$data['welcomeemail']) {
				return false;
			}

			$result = select_query("tblemailtemplates", "name", array("id" => $data['welcomeemail']));
			$data = mysql_fetch_array($result);
			$func_messagename = $data['name'];
		}


		if ($func_messagename == "Order Confirmation") {
			$userid = $func_id;
		}

		$result = select_query("tblemailtemplates", "", array("name" => $func_messagename, "language" => ""));
		$data = mysql_fetch_array($result);
		$emailtplid = $data['id'];
		$type = $data['type'];
		$subject = $data['subject'];
		$message = $data['message'];
		$tplattachments = $data['attachments'];
		$fromname = $data['fromname'];
		$fromemail = $data['fromemail'];
		$disabled = $data['disabled'];
		$copyto = $data['copyto'];
		$plaintext = $data['plaintext'];

		if (!$emailtplid) {
			logActivity("EMAILERROR: Email Template " . $func_messagename . " Not Found");
			return false;
		}


		if (!$func_id && $type != "support") {
			return false;
		}


		if ($disabled) {
			if ($displayresult) {
				echo "<p>The '" . $func_messagename . "' email template has been disabled (" . $subject . ")</p>";
			}

			return false;
		}


		if ($type == "invoice") {
			$invoice = new WHMCS_Invoice($func_id);
			$valid = $invoice->loadData();

			if (!$valid) {
				return false;
			}

			$data = $invoice->getOutput();
			$userid = $data['userid'];
			$invoicedescription = "";
			$invoiceitems = $invoice->getLineItems();
			foreach ($invoiceitems as $item) {
				$invoicedescription .= $item['description'] . " " . $item['amount'] . "<br>\r\n";
			}

			$invoicedescription .= "------------------------------------------------------<br>\r\n";
			$invoicedescription .= $_LANG['invoicessubtotal'] . ": " . $data['subtotal'] . "<br>\r\n";

			if (0 < $data['taxrate']) {
				$invoicedescription .= $data['taxrate'] . "% " . $data['taxname'] . ": " . $data['tax'] . "<br>\r\n";
			}


			if (0 < $data['taxrate2']) {
				$invoicedescription .= $data['taxrate2'] . "% " . $data['taxname2'] . ": " . $data['tax2'] . "<br>\r\n";
			}

			$invoicedescription .= $_LANG['invoicescredit'] . ": " . $data['credit'] . "<br>\r\n";
			$invoicedescription .= $_LANG['invoicestotal'] . ": " . $data['total'] . "";
			$paymentbutton = $invoice->getPaymentLink();
			$email_merge_fields['invoice_id'] = $data['invoiceid'];
			$email_merge_fields['invoice_num'] = $data['invoicenum'];
			$email_merge_fields['invoice_date_created'] = $data['date'];
			$email_merge_fields['invoice_date_due'] = $data['duedate'];
			$email_merge_fields['invoice_date_paid'] = $data['datepaid'];
			$email_merge_fields['invoice_items'] = $invoiceitems;
			$email_merge_fields['invoice_html_contents'] = $invoicedescription;
			$email_merge_fields['invoice_subtotal'] = $data['subtotal'];
			$email_merge_fields['invoice_credit'] = $data['credit'];
			$email_merge_fields['invoice_tax'] = $data['tax'];
			$email_merge_fields['invoice_tax_rate'] = $data['taxrate'] . "%";
			$email_merge_fields['invoice_tax2'] = $data['tax2'];
			$email_merge_fields['invoice_tax_rate2'] = $data['taxrate2'] . "%";
			$email_merge_fields['invoice_total'] = $data['total'];
			$email_merge_fields['invoice_amount_paid'] = $data['amountpaid'];
			$email_merge_fields['invoice_balance'] = $data['balance'];
			$email_merge_fields['invoice_status'] = $data['statuslocale'];
			$email_merge_fields['invoice_last_payment_amount'] = $data['lastpaymentamount'];
			$email_merge_fields['invoice_last_payment_transid'] = $data['lastpaymenttransid'];
			$email_merge_fields['invoice_payment_link'] = $paymentbutton;
			$email_merge_fields['invoice_payment_method'] = $data['paymentmethod'];
			$email_merge_fields['invoice_link'] = "<a href=\"" . $sysurl . "/viewinvoice.php?id=" . $data['id'] . "\">" . $sysurl . "/viewinvoice.php?id=" . $data['id'] . "</a>";
			$email_merge_fields['invoice_notes'] = $data['notes'];
			$email_merge_fields['invoice_subscription_id'] = $data['subscrid'];
			$email_merge_fields['invoice_previous_balance'] = $data['clientpreviousbalance'];
			$email_merge_fields['invoice_all_due_total'] = $data['clienttotaldue'];
			$email_merge_fields['invoice_total_balance_due'] = $data['clientbalancedue'];

			if ($CONFIG['EnablePDFInvoices']) {
				$invoice->pdfCreate();
				$invoice->pdfInvoicePage();
				$attachmentdata = $invoice->pdfOutput();
				$attachmentfilename = $_LANG['invoicefilename'] . $data['invoicenum'] . ".pdf";
			}
		}
		else {
			if ($type == "support") {
				if ($func_messagename == "Bounce Message") {
					$firstname = $extra[0];
					$email = $extra[1];
				}
				else {
					$result = select_query("tbltickets", "", array("id" => $func_id));
					$data = mysql_fetch_array($result);
					$id = $data['id'];
					$deptid = $data['did'];
					$tid = $data['tid'];
					$ticketcc = $data['cc'];
					$c = $data['c'];
					$userid = $data['userid'];
					$date = $data['date'];
					$title = $data['title'];
					$tmessage = $data['message'];
					$status = $data['status'];
					$urgency = $data['urgency'];
					$attachment = $data['attachment'];

					if ($userid) {
						getUsersLang($userid);
					}
					else {
						$whmcs->loadLanguage($_SESSION['Language']);
					}

					$urgency = $_LANG["supportticketsticketurgency" . strtolower($urgency)];

					if (function_exists("getStatusColour")) {
						$status = getStatusColour($status);
					}


					if ($userid == "0") {
						$firstname = $data['name'];
						$email = $data['email'];
					}

					$result = select_query("tblticketdepartments", "", array("id" => $deptid));
					$data = mysql_fetch_array($result);
					$fromname = $CONFIG['CompanyName'] . " " . $data['name'];
					$fromemail = $data['email'];
					$departmentname = $data['name'];
					$replyid = 0;

					if ($extra) {
						$result = select_query("tblticketreplies", "", array("id" => $extra));
						$data = mysql_fetch_array($result);
						$replyid = $data['id'];
						$tmessage = $data['message'];
						$attachment = $data['attachment'];
					}


					if ($attachment) {
						global $attachments_dir;

						$attachment = explode("|", $attachment);
						$attachments = array();
						foreach ($attachment as $file) {
							$attachments[$attachments_dir . $file] = substr($file, 7);
						}
					}

					$date = fromMySQLDate($date, 0, 1);

					if ($func_messagename != "Support Ticket Feedback Request") {
						$subject = "[Ticket ID: {$ticket_id}] {$ticket_subject}";
					}

					$tmessage = strip_tags($tmessage);

					if (!function_exists("getKBAutoSuggestions")) {
						require ROOTDIR . "/includes/ticketfunctions.php";
					}

					$kbarticles = getKBAutoSuggestions($tmessage);
					$kb_auto_suggestions = "";
					foreach ($kbarticles as $kbarticle) {
						$kb_auto_suggestions .= "<a href=\"" . $CONFIG['SystemURL'] . "/knowledgebase.php?action=displayarticle&id=" . $kbarticle['id'] . "\" target=\"_blank\">" . $kbarticle['title'] . "</a> - " . $kbarticle['article'] . "...<br />\r\n";
					}

					$tmessage = nl2br($tmessage);

					if (!function_exists("ticketAutoHyperlinks")) {
						require ROOTDIR . "/includes/ticketfunctions.php";
					}

					$tmessage = ticketAutoHyperlinks($tmessage);
					$email_merge_fields['ticket_id'] = $tid;
					$email_merge_fields['ticket_reply_id'] = $replyid;
					$email_merge_fields['ticket_department'] = $departmentname;
					$email_merge_fields['ticket_date_opened'] = $date;
					$email_merge_fields['ticket_subject'] = $title;
					$email_merge_fields['ticket_message'] = $tmessage;
					$email_merge_fields['ticket_status'] = $status;
					$email_merge_fields['ticket_priority'] = $urgency;
					$email_merge_fields['ticket_url'] = $sysurl . ("/viewticket.php?tid=" . $tid . "&c=" . $c);
					$email_merge_fields['ticket_link'] = "<a href=\"" . $sysurl . ("/viewticket.php?tid=" . $tid . "&c=" . $c . "\">") . $sysurl . ("/viewticket.php?tid=" . $tid . "&c=" . $c . "</a>");
					$email_merge_fields['ticket_auto_close_time'] = $CONFIG['CloseInactiveTickets'];
					$email_merge_fields['ticket_kb_auto_suggestions'] = $kb_auto_suggestions;

					if ($CONFIG['DisableSupportTicketReplyEmailsLogging'] && $func_messagename == "Support Ticket Reply") {
						$nosavemaillog = true;
					}
				}
			}
			else {
				if ($type == "domain") {
					$result = select_query("tbldomains", "", array("id" => $func_id));
					$data = mysql_fetch_array($result);
					$id = $data['id'];
					$userid = $data['userid'];
					$orderid = $data['orderid'];
					$registrationdate = $data['registrationdate'];
					$status = $data['status'];
					$domain = $data['domain'];
					$firstpaymentamount = $data['firstpaymentamount'];
					$recurringamount = $data['recurringamount'];
					$registrar = $data['registrar'];
					$registrationperiod = $data['registrationperiod'];
					$expirydate = $data['expirydate'];
					$nextduedate = $data['nextduedate'];
					$gateway = $data['paymentmethod'];
					$dnsmanagement = $data['dnsmanagement'];
					$emailforwarding = $data['emailforwarding'];
					$idprotection = $data['idprotection'];
					$donotrenew = $data['donotrenew'];
					getUsersLang($userid);
					$currency = getCurrency($userid);
					$status = $_LANG["clientarea" . strtolower(str_replace(" ", "", $status))];
					$expirydays_todaysdate = date("Ymd");
					$expirydays_todaysdate = strtotime($expirydays_todaysdate);
					$expirydays_expirydate = strtotime($expirydate);
					$expirydays = round(($expirydays_expirydate - $expirydays_todaysdate) / 86400);
					$expirydays_nextduedate = strtotime($nextduedate);
					$nextduedays = round(($expirydays_nextduedate - $expirydays_todaysdate) / 86400);

					if ($expirydays < 0) {
						$expirydays = $nextduedays;
					}


					if ($expirydays < 0) {
						$expirydays = 0;
						$nextduedays = 0;
					}

					$registrationdate = fromMySQLDate($registrationdate, 0, 1);
					$expirydate = fromMySQLDate($expirydate, 0, 1);
					$nextduedate = fromMySQLDate($nextduedate, 0, 1);
					$domainparts = explode(".", $domain, 2);
					$email_merge_fields['domain_id'] = $id;
					$email_merge_fields['domain_order_id'] = $orderid;
					$email_merge_fields['domain_reg_date'] = $registrationdate;
					$email_merge_fields['domain_status'] = $status;
					$email_merge_fields['domain_name'] = $domain;
					$email_merge_fields['domain_sld'] = $domainparts[0];
					$email_merge_fields['domain_tld'] = $domainparts[1];
					$email_merge_fields['domain_first_payment_amount'] = formatCurrency($firstpaymentamount);
					$email_merge_fields['domain_recurring_amount'] = formatCurrency($recurringamount);
					$email_merge_fields['domain_registrar'] = $registrar;
					$email_merge_fields['domain_reg_period'] = $registrationperiod . " " . $_LANG['orderyears'];
					$email_merge_fields['domain_expiry_date'] = $expirydate;
					$email_merge_fields['domain_next_due_date'] = $nextduedate;
					$email_merge_fields['domain_days_until_expiry'] = $expirydays;
					$email_merge_fields['domain_days_until_nextdue'] = $nextduedays;
					$email_merge_fields['domain_dns_management'] = ($dnsmanagement ? "1" : "0");
					$email_merge_fields['domain_email_forwarding'] = ($emailforwarding ? "1" : "0");
					$email_merge_fields['domain_id_protection'] = ($idprotection ? "1" : "0");
					$email_merge_fields['domain_do_not_renew'] = ($donotrenew ? "1" : "0");
				}
				else {
					if ($type == "product") {
						$gatewaysarray = array();
						$result = select_query("tblpaymentgateways", "gateway,value", array("setting" => "name"), "order", "ASC");

						while ($data = mysql_fetch_array($result)) {
							$gatewaysarray[$data['gateway']] = $data['value'];
						}

						$result = select_query("tblhosting", "tblhosting.*,tblproducts.name,tblproducts.description", array("tblhosting.id" => $func_id), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");
						$data = mysql_fetch_array($result);
						$id = $data['id'];
						$userid = $data['userid'];
						$orderid = $data['orderid'];
						$regdate = $data['regdate'];
						$nextduedate = $data['nextduedate'];
						$orderno = $data['orderno'];
						$domain = $data['domain'];
						$server = $data['server'];
						$package = $data['name'];
						$productdescription = $data['description'];
						$packageid = $data['packageid'];
						$upgrades = $data['upgrades'];
						$paymentmethod = $data['paymentmethod'];
						$paymentmethod = $gatewaysarray[$paymentmethod];

						if ($regdate == $nextduedate) {
							$amount = $data['firstpaymentamount'];
						}
						else {
							$amount = $data['amount'];
						}

						$firstpaymentamount = $data['firstpaymentamount'];
						$recurringamount = $data['amount'];
						$billingcycle = $data['billingcycle'];
						$domainstatus = $data['domainstatus'];
						$username = $data['username'];
						$password = decrypt($data['password']);
						$dedicatedip = $data['dedicatedip'];
						$assignedips = nl2br($data['assignedips']);
						$dedi_ns1 = $data['ns1'];
						$dedi_ns2 = $data['ns2'];
						$subscriptionid = $data['subscriptionid'];
						$suspendreason = $data['suspendreason'];
						$canceltype = get_query_val("tblcancelrequests", "type", array("relid" => $data['id']), "id", "DESC");
						$regdate = fromMySQLDate($regdate, 0, 1);

						if ($nextduedate != "-") {
							$nextduedate = fromMySQLDate($nextduedate, 0, 1);
						}

						getUsersLang($userid);
						$currency = getCurrency($userid);

						if ($domainstatus == "Suspended" && !$suspendreason) {
							$suspendreason = $_LANG['suspendreasonoverdue'];
						}

						$domainstatus = $_LANG["clientarea" . strtolower(str_replace(" ", "", $domainstatus))];
						$canceltype = $_LANG["clientareacancellation" . strtolower(str_replace(" ", "", $canceltype))];

						if ($server) {
							$result3 = select_query("tblservers", "", array("id" => $server));
							$data3 = mysql_fetch_array($result3);
							$servername = $data3['name'];
							$serverip = $data3['ipaddress'];
							$serverhostname = $data3['hostname'];
							$ns1 = $data3['nameserver1'];
							$ns1ip = $data3['nameserver1ip'];
							$ns2 = $data3['nameserver2'];
							$ns2ip = $data3['nameserver2ip'];
							$ns3 = $data3['nameserver3'];
							$ns3ip = $data3['nameserver3ip'];
							$ns4 = $data3['nameserver4'];
							$ns4ip = $data3['nameserver4ip'];
						}

						$billingcycleforconfigoptions = strtolower($billingcycle);
						$billingcycleforconfigoptions = preg_replace("/[^a-z]/i", "", $billingcycleforconfigoptions);
						$langbillingcycle = $billingcycleforconfigoptions;
						$billingcycleforconfigoptions = str_replace("lly", "l", $billingcycleforconfigoptions);

						if ($billingcycleforconfigoptions == "free account") {
							$billingcycleforconfigoptions = "monthly";
						}

						$configoptions = array();
						$configoptionshtml = "";
						$query4 = "SELECT tblproductconfigoptions.id, tblproductconfigoptions.optionname AS confoption, tblproductconfigoptions.optiontype AS conftype, tblproductconfigoptionssub.optionname, tblhostingconfigoptions.qty FROM tblhostingconfigoptions INNER JOIN tblproductconfigoptions ON tblproductconfigoptions.id = tblhostingconfigoptions.configid INNER JOIN tblproductconfigoptionssub ON tblproductconfigoptionssub.id = tblhostingconfigoptions.optionid INNER JOIN tblhosting ON tblhosting.id=tblhostingconfigoptions.relid INNER JOIN tblproductconfiglinks ON tblproductconfiglinks.gid=tblproductconfigoptions.gid WHERE tblhostingconfigoptions.relid='" . (int)$id . "' AND tblproductconfiglinks.pid=tblhosting.packageid ORDER BY tblproductconfigoptions.`order`,tblproductconfigoptions.id ASC";
						$result4 = full_query($query4);

						while ($data4 = mysql_fetch_array($result4)) {
							$confoption = $data4['confoption'];
							$conftype = $data4['conftype'];

							if (strpos($confoption, "|")) {
								$confoption = explode("|", $confoption);
								$confoption = trim($confoption[1]);
							}

							$optionname = $data4['optionname'];
							$optionqty = $data4['qty'];

							if (strpos($optionname, "|")) {
								$optionname = explode("|", $optionname);
								$optionname = trim($optionname[1]);
							}


							if ($conftype == 3) {
								if ($optionqty) {
									$optionname = $_LANG['yes'];
								}
								else {
									$optionname = $_LANG['no'];
								}
							}
							else {
								if ($conftype == 4) {
									$optionname = "" . $optionqty . " x " . $optionname;
								}
							}

							$configoptions[] = array("id" => $data4['id'], "option" => $confoption, "type" => $conftype, "value" => $optionname, "qty" => $optionqty, "setup" => $CONFIG['CurrencySymbol'] . $data4['setup'], "recurring" => $CONFIG['CurrencySymbol'] . $data4['recurring']);
							$configoptionshtml .= "" . $confoption . ": " . $optionname . " " . $CONFIG['CurrencySymbol'] . $data4['recurring'] . "<br>\r\n";
						}

						$email_merge_fields['service_order_id'] = $orderid;
						$email_merge_fields['service_id'] = $id;
						$email_merge_fields['service_reg_date'] = $regdate;
						$email_merge_fields['service_product_name'] = $package;
						$email_merge_fields['service_product_description'] = $productdescription;
						$email_merge_fields['service_config_options'] = $configoptions;
						$email_merge_fields['service_config_options_html'] = $configoptionshtml;
						$email_merge_fields['service_domain'] = $domain;
						$email_merge_fields['service_server_name'] = $servername;
						$email_merge_fields['service_server_hostname'] = $serverhostname;
						$email_merge_fields['service_server_ip'] = $serverip;
						$email_merge_fields['service_dedicated_ip'] = $dedicatedip;
						$email_merge_fields['service_assigned_ips'] = $assignedips;

						if ($dedi_ns1 != "") {
							$email_merge_fields['service_ns1'] = $dedi_ns1;
							$email_merge_fields['service_ns2'] = $dedi_ns2;
						}
						else {
							$email_merge_fields['service_ns1'] = $ns1;
							$email_merge_fields['service_ns2'] = $ns2;
							$email_merge_fields['service_ns3'] = $ns3;
							$email_merge_fields['service_ns4'] = $ns4;
						}

						$email_merge_fields['service_ns1_ip'] = $ns1ip;
						$email_merge_fields['service_ns2_ip'] = $ns2ip;
						$email_merge_fields['service_ns3_ip'] = $ns3ip;
						$email_merge_fields['service_ns4_ip'] = $ns4ip;
						$email_merge_fields['service_payment_method'] = $paymentmethod;
						$email_merge_fields['service_first_payment_amount'] = formatCurrency($firstpaymentamount);
						$email_merge_fields['service_recurring_amount'] = formatCurrency($recurringamount);
						$email_merge_fields['service_billing_cycle'] = $_LANG["orderpaymentterm" . $langbillingcycle];
						$email_merge_fields['service_next_due_date'] = $nextduedate;
						$email_merge_fields['service_status'] = $domainstatus;
						$email_merge_fields['service_username'] = $username;
						$email_merge_fields['service_password'] = $password;
						$email_merge_fields['service_subscription_id'] = $subscriptionid;
						$email_merge_fields['service_suspension_reason'] = $suspendreason;
						$email_merge_fields['service_cancellation_type'] = $canceltype;

						if (!function_exists("getCustomFields")) {
							require dirname(__FILE__) . "/customfieldfunctions.php";
						}

						$customfields = getCustomFields("product", $packageid, $func_id, true, "");
						$email_merge_fields['service_custom_fields'] = array();
						foreach ($customfields as $customfield) {
							$customfieldname = preg_replace("/[^0-9a-z]/", "", strtolower($customfield['name']));
							$email_merge_fields["service_custom_field_" . $customfieldname] = $customfield['value'];
							$email_merge_fields['service_custom_fields'][] = $customfield['value'];
						}
					}
					else {
						if ($type == "affiliate") {
							$result = select_query("tblaffiliates", "", array("id" => $func_id));
							$data = mysql_fetch_array($result);
							$id = $affiliateid = $data['id'];
							$userid = $data['clientid'];
							$visitors = $data['visitors'];
							$balance = $data['balance'];
							$withdrawn = $data['withdrawn'];
							$currency = getCurrency($userid);
							$balance = formatCurrency($balance);
							$withdrawn = formatCurrency($withdrawn);
							getUsersLang($userid);
							$referralstable .= "<table cellspacing=\"1\" bgcolor=\"#cccccc\" width=\"100%\"><tr bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold;\"><td>" . $_LANG['affiliatessignupdate'] . "</td><td>" . $_LANG['orderproduct'] . "</td><td>" . $_LANG['affiliatesamount'] . "</td><td>" . $_LANG['orderbillingcycle'] . "</td><td>" . $_LANG['affiliatescommission'] . "</td><td>" . $_LANG['affiliatesstatus'] . "</td></tr>";
							$service = "";
							$result = select_query("tblaffiliatesaccounts", "tblaffiliatesaccounts.*,tblproducts.name,tblhosting.userid,tblhosting.domainstatus,tblhosting.amount,tblhosting.firstpaymentamount,tblhosting.regdate,tblhosting.billingcycle", array("affiliateid" => $affiliateid), "regdate", "DESC", "", "tblhosting ON tblhosting.id=tblaffiliatesaccounts.relid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblclients ON tblclients.id=tblhosting.userid");

							while ($data = mysql_fetch_array($result)) {
								$affaccid = $data['id'];
								$lastpaid = $data['lastpaid'];
								$relid = $data['relid'];
								$ref_userid = $data['userid'];
								$amount = $data['amount'];
								$date = $data['regdate'];
								$service = $data['name'];
								$billingcycle = $data['billingcycle'];
								$status = $data['domainstatus'];

								if ($billingcycle == "One Time") {
									$amount = $data['firstpaymentamount'];
								}

								$commission = calculateAffiliateCommission($affiliateid, $relid);
								$currency = getCurrency($ref_userid);
								$amount = formatCurrency($amount);
								$commission = formatCurrency($commission);
								$date = fromMySQLDate($date, 0, 1);

								if ($status == "Active") {
									$status = $_LANG['clientareaactive'];
								}
								else {
									if ($status == "Pending") {
										$status = $_LANG['clientareapending'];
									}
									else {
										if ($status == "Suspended") {
											$status = $_LANG['clientareasuspended'];
										}
										else {
											if ($status == "Terminated") {
												$status = $_LANG['clientareaterminated'];
											}
											else {
												if ($status == "Cancelled") {
													$status = $_LANG['clientareacancelled'];
												}
												else {
													if ($status == "Fraud") {
														$status = $_LANG['clientareafraud'];
													}
												}
											}
										}
									}
								}

								$billingcycle = strtolower($billingcycle);
								$billingcycle = str_replace(" ", "", $billingcycle);
								$billingcycle = str_replace("-", "", $billingcycle);
								$billingcycle = $_LANG["orderpaymentterm" . $billingcycle];
								$referralstable .= "<tr bgcolor=\"#ffffff\" style=\"text-align:center;\"><td>" . $date . "</td><td>" . $service . "</td><td>" . $amount . "</td><td>" . $billingcycle . "</td><td>" . $commission . "</td><td>" . $status . "</td></tr>";
							}


							if (!$service) {
								$referralstable .= "<tr bgcolor=\"#ffffff\"><td colspan=\"6\" align=\"center\">" . $_LANG['affiliatesnosignups'] . "</td></tr>";
							}

							$referralstable .= "</table>";
							$email_merge_fields['affiliate_total_visits'] = $visitors;
							$email_merge_fields['affiliate_balance'] = $balance;
							$email_merge_fields['affiliate_withdrawn'] = $withdrawn;
							$email_merge_fields['affiliate_referrals_table'] = $referralstable;
							$email_merge_fields['affiliate_referral_url'] = $CONFIG['SystemURL'] . ("/aff.php?aff=" . $id);
						}
					}
				}
			}
		}

		$contactid = "";

		if ($type == "general") {
			$userid = $func_id;
		}


		if (in_array($func_messagename, array("Password Reset Validation", "Password Reset Confirmation", "Automated Password Reset")) && $extra['contactid']) {
			$contactid = $extra['contactid'];
		}


		if ($userid || $contactid) {
			if ($contactid) {
				$result2 = select_query("tblcontacts", "tblcontacts.*,(SELECT groupid FROM tblclients WHERE id=tblcontacts.userid) AS clgroupid,(SELECT groupname FROM tblclientgroups WHERE id=clgroupid) AS clgroupname,(SELECT language FROM tblclients WHERE id=tblcontacts.userid) AS language", array("id" => $contactid));
			}
			else {
				$result2 = select_query("tblclients", "tblclients.*,tblclients.groupid AS clgroupid,(SELECT groupname FROM tblclientgroups WHERE id=tblclients.groupid) AS clgroupname", array("id" => $userid));
			}

			$data2 = mysql_fetch_array($result2);

			if (!$firstname && !$email) {
				$firstname = $data2['firstname'];
				$email = $data2['email'];
			}

			$lastname = $data2['lastname'];
			$companyname = $data2['companyname'];
			$address1 = $data2['address1'];
			$address2 = $data2['address2'];
			$city = $data2['city'];
			$state = $data2['state'];
			$postcode = $data2['postcode'];
			$country = $data2['country'];
			$phonenumber = $data2['phonenumber'];
			$language = $data2['language'];
			$credit = $data2['credit'];
			$status = $data2['status'];
			$language = $data2['language'];
			$clgroupid = $data2['clgroupid'];
			$clgroupname = $data2['clgroupname'];
			$gatewayid = $data2['gatewayid'];
			$datecreated = fromMySQLDate($data2['datecreated'], 0, 1);
			$password = "**********";

			if ($CONFIG['NOMD5']) {
				$password = decrypt($data2['password']);
			}

			$cardtype = $data2['cardtype'];
			$cardnum = $data2['cardlastfour'];

			if (!function_exists("getCCDetails")) {
				require_once dirname(__FILE__) . "/ccfunctions.php";
			}

			$carddetails = getCCDetails($userid);
			$cardexp = $carddetails['expdate'];
			unset($carddetails);
			$currency = getCurrency($userid);
			$balance = get_query_val("tblinvoices", "SUM(total)-COALESCE((SELECT SUM(amountin-amountout) FROM tblaccounts WHERE tblaccounts.invoiceid=tblinvoices.id),0)", array("userid" => $userid, "status" => "Unpaid"));
			$email_merge_fields['client_due_invoices_balance'] = formatCurrency($balance);

			if ($func_messagename == "Automated Password Reset" && !$CONFIG['NOMD5']) {
				$length = 10;
				$seeds = "ABCDEFGHIJKLMNPQRSTUVYXYZ0123456789abcdefghijklmnopqrstuvwxyz";
				$seeds_count = strlen($seeds) - 1;
				$password = "";
				$i = 0;

				while ($i < $length) {
					$password .= $seeds[rand(0, $seeds_count)];
					++$i;
				}


				if (!function_exists("generateClientPW")) {
					require_once dirname(__FILE__) . "/clientfunctions.php";
				}

				$passwordhash = generateClientPW($password);

				if ($contactid) {
					update_query("tblcontacts", array("password" => $passwordhash), array("id" => $contactid));
				}
				else {
					update_query("tblclients", array("password" => $passwordhash), array("id" => $userid));
				}

				run_hook("ClientChangePassword", array("userid" => $userid, "password" => $password));
			}


			if ($extra['emailquote']) {
				$userid = $extra['client_id'];
				$firstname = $extra['client_first_name'];
				$lastname = $extra['client_last_name'];
				$companyname = $extra['client_company_name'];
				$email = $extra['client_email'];
				$address1 = $extra['client_address1'];
				$address2 = $extra['client_address2'];
				$city = $extra['client_city'];
				$state = $extra['client_state'];
				$postcode = $extra['client_postcode'];
				$country = $extra['client_country'];
				$phonenumber = $extra['client_phonenumber'];
				$language = $extra['client_language'];
				$attachmentfilename = $_LANG['quotefilename'] . $extra['quote_number'] . ".pdf";
				$attachmentdata = $extra['quoteattachmentdata'];
				$extra['quoteattachmentdata'] = "";
			}
		}


		if (!$email) {
			return false;
		}

		$fname = trim($firstname . " " . $lastname);

		if ($companyname) {
			$fname .= " (" . $companyname . ")";
		}

		$email_merge_fields['client_id'] = $userid;
		$email_merge_fields['client_name'] = $fname;
		$email_merge_fields['client_first_name'] = $firstname;
		$email_merge_fields['client_last_name'] = $lastname;
		$email_merge_fields['client_company_name'] = $companyname;
		$email_merge_fields['client_email'] = $email;
		$email_merge_fields['client_address1'] = $address1;
		$email_merge_fields['client_address2'] = $address2;
		$email_merge_fields['client_city'] = $city;
		$email_merge_fields['client_state'] = $state;
		$email_merge_fields['client_postcode'] = $postcode;
		$email_merge_fields['client_country'] = $country;
		$email_merge_fields['client_phonenumber'] = $phonenumber;
		$email_merge_fields['client_password'] = $password;
		$email_merge_fields['client_signup_date'] = $datecreated;
		$email_merge_fields['client_credit'] = formatCurrency($credit);
		$email_merge_fields['client_cc_type'] = $cardtype;
		$email_merge_fields['client_cc_number'] = $cardnum;
		$email_merge_fields['client_cc_expiry'] = $cardexp;
		$email_merge_fields['client_language'] = $language;
		$email_merge_fields['client_status'] = $status;
		$email_merge_fields['client_group_id'] = $clgroupid;
		$email_merge_fields['client_group_name'] = $clgroupname;
		$email_merge_fields['client_gateway_id'] = $gatewayid;
		$email_merge_fields['unsubscribe_url'] = $CONFIG['SystemURL'] . "/unsubscribe.php?email=" . $email . "&key=" . sha1($email . $userid . $cc_encryption_hash);

		if (!function_exists("getCustomFields")) {
			require dirname(__FILE__) . "/customfieldfunctions.php";
		}

		$customfields = getCustomFields("client", "", $userid, true, "");
		$email_merge_fields['client_custom_fields'] = array();
		foreach ($customfields as $customfield) {
			$customfieldname = preg_replace("/[^0-9a-z]/", "", strtolower($customfield['name']));
			$email_merge_fields["client_custom_field_" . $customfieldname] = $customfield['value'];
			$email_merge_fields['client_custom_fields'][] = $customfield['value'];
		}


		if (is_array($extra)) {
			foreach ($extra as $k => $v) {
				$email_merge_fields[$k] = $v;
			}
		}

		$email_merge_fields['company_name'] = $CONFIG['CompanyName'];
		$email_merge_fields['company_domain'] = $CONFIG['Domain'];
		$email_merge_fields['company_logo_url'] = $CONFIG['LogoURL'];
		$email_merge_fields['whmcs_url'] = $CONFIG['SystemURL'];
		$email_merge_fields['whmcs_link'] = "<a href=\"" . $CONFIG['SystemURL'] . "\">" . $CONFIG['SystemURL'] . "</a>";
		$email_merge_fields['signature'] = nl2br(html_entity_decode($CONFIG['Signature']));
		$email_merge_fields['date'] = date("l, jS F Y");
		$email_merge_fields['time'] = date("g:ia");
		$result = select_query("tblemailtemplates", "", array("name" => $func_messagename, "language" => $language));
		$data = mysql_fetch_array($result);

		if (substr($subject, 0, 10) != "[Ticket ID" && $data['subject']) {
			$subject = $data['subject'];
		}


		if ($data['message']) {
			$message = $data['message'];
		}

		$emailglobalheader = html_entity_decode($CONFIG['EmailGlobalHeader']);
		$emailglobalfooter = html_entity_decode($CONFIG['EmailGlobalFooter']);

		if ($emailglobalheader) {
			$message = $emailglobalheader . "\r\n" . $message;
		}


		if ($emailglobalfooter) {
			$message = $message . "\r\n" . $emailglobalfooter;
		}

		$hookresults = run_hook("EmailPreSend", array("messagename" => $func_messagename, "relid" => $func_id));
		foreach ($hookresults as $hookmergefields) {
			foreach ($hookmergefields as $k => $v) {

				if ($k == "abortsend" && $v == true) {
					return false;
				}

				$email_merge_fields[$k] = $v;
			}
		}


		if (!$fromname) {
			$fromname = $CONFIG['CompanyName'];
		}


		if (!$fromemail) {
			$fromemail = $CONFIG['Email'];
		}

		global $templates_compiledir;

		if (!class_exists("Smarty")) {
			require ROOTDIR . "/includes/smarty/Smarty.class.php";
		}

		$smarty = new Smarty();
		$smarty->caching = 0;
		$smarty->compile_dir = $templates_compiledir;
		$smarty->compile_id = md5($subject . $message);
		$smarty->register_resource("emailtpl", array("emailtpl_template", "emailtpl_timestamp", "emailtpl_secure", "emailtpl_trusted"));
		$smarty->assign("emailsubject", $subject);
		$smarty->assign("emailmessage", $message);
		foreach ($email_merge_fields as $mergefield => $mergevalue) {
			$smarty->assign($mergefield, $mergevalue);
		}

		$subject = $smarty->fetch("emailtpl:emailsubject");
		$message = $smarty->fetch("emailtpl:emailmessage");

		if (!trim($subject) && !trim($message)) {
			logActivity("EMAILERROR: Email Message Empty so Aborting Sending - Template Name " . $func_messagename . " ID " . $func_id);
			return false;
		}

		$whmcs->load_class("phpmailer");
		$mail = new PHPMailer(true);

		try{
		$mail->From = $fromemail;
		$mail->FromName = str_replace("&amp;", "&", $fromname);

		if ($CONFIG['MailType'] == "mail") {
			$mail->Mailer = "mail";
		}
		else {
			if ($CONFIG['MailType'] == "smtp") {
				$mail->IsSMTP();
				$mail->Host = $CONFIG['SMTPHost'];
				$mail->Port = $CONFIG['SMTPPort'];
				$mail->Hostname = $_SERVER['SERVER_NAME'];

				if ($CONFIG['SMTPSSL']) {
					$mail->SMTPSecure = $CONFIG['SMTPSSL'];
				}


				if ($CONFIG['SMTPUsername']) {
					$mail->SMTPAuth = true;
					$mail->Username = $CONFIG['SMTPUsername'];
					$mail->Password = decrypt($CONFIG['SMTPPassword']);
				}

				$mail->Sender = $mail->From;

				if ($fromemail != $CONFIG['SMTPUsername']) {
					$mail->AddReplyTo($fromemail, $fromname);
				}
			}
		}

		$mail->XMailer = $whmcs->get_config("CompanyName");
		$mail->CharSet = $CONFIG['Charset'];
		$mail->AddAddress(trim($email), $firstname . " " . $lastname);

		if ($CONFIG['BCCMessages']) {
			$bcc = $CONFIG['BCCMessages'] . ",";
			$bcc = explode(",", $bcc);
			foreach ($bcc as $value) {
				$ccaddress = trim($value);

				if ($ccaddress) {
					$mail->AddBCC($ccaddress);
					continue;
				}
			}
		}

		$additionalccs = "";

		if ($type == "support") {
			if ($ticketcc) {
				$ticketcc = explode(",", $ticketcc);
				foreach ($ticketcc as $ccaddress) {
					$ccaddress = trim($ccaddress);

					if ($ccaddress) {
						$mail->AddAddress($ccaddress);
						$additionalccs .= $ccaddress . ",";
						continue;
					}
				}
			}
		}
		else {
			$result = select_query("tblcontacts", "", array("userid" => $userid, $type . "emails" => "1"));

			while ($data = mysql_fetch_array($result)) {
				$ccaddress = trim($data['email']);
				$mail->AddAddress($ccaddress, $data['firstname'] . " " . $data['lastname']);
				$additionalccs .= $ccaddress . ",";
			}
		}


		if ($copyto) {
			$copytoarray = explode(",", $copyto);

			if ($CONFIG['MailType'] == "mail") {
				foreach ($copytoarray as $copytoemail) {
					$mail->AddBCC(trim($copytoemail));
				}
			}
			else {
				foreach ($copytoarray as $copytoemail) {
					$mail->AddCC(trim($copytoemail));
				}
			}
		}


		if ($additionalccs) {
			if ($copyto) {
				$copyto .= ",";
			}

			$copyto = substr($additionalccs, 0, 0 - 1);
		}

		$mail->Subject = $subject;

		if ($plaintext) {
			$message = str_replace("<br>", "", $message);
			$message = str_replace("<br />", "", $message);
			$message = strip_tags($message);
			$mail->Body = html_entity_decode($message);
			$message = nl2br($message);
		}
		else {
			$message_text = str_replace("<p>", "", $message);

			$message_text = str_replace("</p>", "\r\n\r\n", $message_text);
			$message_text = str_replace("<br>", "\r\n", $message_text);

			$message_text = str_replace("<br />", "\r\n", $message_text);

			$message_text = strip_tags($message_text);
			$cssdata = "";

			if ($CONFIG['EmailCSS']) {
				$cssdata = "<style>\r\n" . $CONFIG['EmailCSS'] . "\r\n</style>";
			}

			$message = $cssdata . "\r\n" . $message;
			$mail->Body = $message;
			$mail->AltBody = $message_text;
		}


		if ($tplattachments) {
			$tplattachments = explode(",", $tplattachments);
			foreach ($tplattachments as $attachment) {
				$filename = $downloads_dir . $attachment;
				$displayname = substr($attachment, 7);
				$mail->AddAttachment($filename, $displayname);
			}
		}


		if ($attachmentfilename) {
			if (is_array($attachmentfilename)) {
				$count = 0;
				foreach ($attachmentfilename as $filelist) {
					$mail->AddStringAttachment($attachmentdata[$count], $filelist);
					++$count;
				}
			}
			else {
				$mail->AddStringAttachment($attachmentdata, $attachmentfilename);
			}
		}


		if (is_array($attachments)) {
			foreach ($attachments as $filename => $displayname) {
				$mail->AddAttachment($filename, $displayname);
			}
		}

		global $smtp_debug;
		global $email_debug;
		global $email_preview;

		if ($smtp_debug) {
			$mail->SMTPDebug = true;
		}


		if ($email_debug) {
			echo "Email: {$email}<br>Subject: {$subject}<br>Message: {$message}<br>Attachment: {$attachmentfilename}<br><br>";
			return false;
		}


		if ($email_preview) {
			echo $message;
			return false;
		}

		$mail->Send();

		if ($displayresult) {
			echo "<p>Email Sent Successfully to <a href=\"clientssummary.php?userid=" . $userid . "\">" . $firstname . " " . $lastname . "</a></p>";
		}


		if ($userid && !$nosavemaillog) {
			insert_query("tblemails", array("userid" => $userid, "subject" => $subject, "message" => $message, "date" => "now()", "to" => $email, "cc" => $copyto, "bcc" => $CONFIG['BCCMessages']));
		}

		logActivity("Email Sent to " . $firstname . " " . $lastname . " (" . $subject . ")");
		$mail->ClearAddresses();

		} catch (phpmailerException $e) {
			logActivity("Email Sending Failed - " . $e->getMessage() . (" (User ID: " . $userid . " - Subject: " . $subject . ")"), "none");

			if ($displayresult) {
				echo "<p>Email Sending Failed - " . $e->errorMessage() . "</p>";
			}
		}

		catch (Exception $e) {
				logActivity("Email Sending Failed - " . $e->getMessage() . (" (User ID: " . $userid . " - Subject: " . $subject . ")"), "none");

				if ($displayresult) {
					echo "<p>Email Sending Failed - " . $e->getMessage() . "</p>";
				}

			return null;
		}
	}

	function sendAdminNotification($to = "system", $subject, $adminmessage, $deptid = "", $genericadminlink = true) {
		global $whmcs;
		global $CONFIG;
		global $smtp_debug;

		if (!$adminmessage) {
			return false;
		}


		if ($CONFIG['LogoURL']) {
			$message = "<p><a href=\"" . $CONFIG['Domain'] . "\" target=\"_blank\"><img src=\"" . $CONFIG['LogoURL'] . "\" alt=\"" . $CONFIG['CompanyName'] . "\" border=\"0\"></a></p>";
		}

		$adminurl = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
		$adminurl .= "/" . $whmcs->get_admin_folder_name() . "/";
		$message .= "<font style=\"font-family:Verdana;font-size:11px\"><p>" . $adminmessage . "</p>";

		if ($genericadminlink) {
			$message .= "<p><a href=\"" . $adminurl . "\">" . $adminurl . "</a></p>";
		}

		$whmcs->load_class("phpmailer");
		$mail = new PHPMailer();

		if ($deptid) {
			$result = select_query("tblticketdepartments", "name,email", array("id" => $deptid));
			$data = mysql_fetch_array($result);
			$mail->From = $data['email'];
			$mail->FromName = str_replace("&amp;", "&", $CONFIG['CompanyName'] . " " . $data['name']);
		}
		else {
			$mail->From = $CONFIG['SystemEmailsFromEmail'];
			$mail->FromName = str_replace("&amp;", "&", $CONFIG['SystemEmailsFromName']);
		}

		$mail->Subject = $subject;
		$mail->CharSet = $CONFIG['Charset'];

		if ($CONFIG['MailType'] == "mail") {
			$mail->Mailer = "mail";
		}
		else {
			if ($CONFIG['MailType'] == "smtp") {
				$mail->IsSMTP();
				$mail->Host = $CONFIG['SMTPHost'];
				$mail->Port = $CONFIG['SMTPPort'];
				$mail->Hostname = $_SERVER['SERVER_NAME'];

				if ($CONFIG['SMTPSSL']) {
					$mail->SMTPSecure = $CONFIG['SMTPSSL'];
				}


				if ($CONFIG['SMTPUsername']) {
					$mail->SMTPAuth = true;
					$mail->Username = $CONFIG['SMTPUsername'];
					$mail->Password = decrypt($CONFIG['SMTPPassword']);
				}

				$mail->Sender = $mail->From;
			}
		}

		$mail->XMailer = "WHMCS v" . $whmcs->get_config("Version");

		if ($smtp_debug) {
			$mail->SMTPDebug = true;
		}

		$message_text = str_replace("</p>", "\r\n\r\n", $message);

		$message_text = str_replace("<br>", "\r\n", $message_text);

		$message_text = str_replace("<br />", "\r\n", $message_text);

		$message_text = strip_tags($message_text);
		$mail->Body = $message;
		$mail->AltBody = $message_text;
		$emailcount = 0;
		$where = "tbladmins.disabled=0 AND tbladminroles." . db_escape_string($to) . "emails='1'";

		if ($deptid) {
			$where .= " AND tbladmins.ticketnotifications!=''";
		}

		$result = select_query("tbladmins", "firstname,lastname,email,ticketnotifications", $where, "", "", "", "tbladminroles ON tbladminroles.id=tbladmins.roleid");

		while ($data = mysql_fetch_array($result)) {
			if ($data['email']) {
				$adminsend = true;

				if ($deptid) {
					$ticketnotifications = $data['ticketnotifications'];
					$ticketnotifications = explode(",", $ticketnotifications);

					if (!in_array($deptid, $ticketnotifications)) {
						$adminsend = false;
					}
				}


				if ($adminsend) {
					$mail->AddAddress(trim($data['email']), $data['firstname'] . " " . $data['lastname']);
					++$emailcount;
				}
			}
		}


		if (!$emailcount) {
			return false;
		}


		if (!$mail->Send()) {
			logActivity("Admin Email Notification Sending Failed - " . $mail->ErrorInfo . (" (Subject: " . $subject . ")"), "none");
		}

		$mail->ClearAddresses();
	}

	function sendAdminMessage($name, $email_merge_fields = array(), $to = "system", $deptid = "", $adminid = "", $ticketnotify = "") {
		global $whmcs;
		global $CONFIG;
		global $templates_compiledir;
		global $smtp_debug;

		$result = select_query("tblemailtemplates", "", array("name" => $name, "language" => ""));
		$data = mysql_fetch_array($result);
		$type = $data['type'];
		$subject = $data['subject'];
		$message = $data['message'];
		$fromname = $data['fromname'];
		$fromemail = $data['fromemail'];
		$disabled = $data['disabled'];
		$copyto = $data['copyto'];
		$plaintext = $data['plaintext'];

		if ($disabled) {
			return false;
		}


		if (!$fromname) {
			$fromname = $CONFIG['SystemEmailsFromName'];
		}


		if (!$fromemail) {
			$fromemail = $CONFIG['SystemEmailsFromEmail'];
		}

		$email_merge_fields['company_name'] = $CONFIG['CompanyName'];
		$email_merge_fields['signature'] = nl2br($CONFIG['Signature']);
		$email_merge_fields['whmcs_url'] = $CONFIG['SystemURL'];
		$email_merge_fields['whmcs_link'] = "<a href=\"" . $CONFIG['SystemURL'] . "\">" . $CONFIG['SystemURL'] . "</a>";
		$adminurl = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
		$adminurl .= "/" . $whmcs->get_admin_folder_name() . "/";
		$email_merge_fields['whmcs_admin_url'] = $adminurl;
		$email_merge_fields['whmcs_admin_link'] = "<a href=\"" . $adminurl . "\">" . $adminurl . "</a>";
		include_once ROOTDIR . "/includes/smarty/Smarty.class.php";

		$smarty = new Smarty();
		$smarty->caching = 0;
		$smarty->compile_dir = $templates_compiledir;
		$smarty->compile_id = md5($subject . $message);
		$smarty->register_resource("emailtpl", array("emailtpl_template", "emailtpl_timestamp", "emailtpl_secure", "emailtpl_trusted"));
		$smarty->assign("emailsubject", $subject);
		$smarty->assign("emailmessage", $message);
		foreach ($email_merge_fields as $mergefield => $mergevalue) {
			$smarty->assign($mergefield, $mergevalue);
		}

		$subject = $smarty->fetch("emailtpl:emailsubject");
		$message = $smarty->fetch("emailtpl:emailmessage");
		$whmcs->load_class("phpmailer");
		$mail = new PHPMailer();

		if ($deptid) {
			$result = select_query("tblticketdepartments", "name,email", array("id" => $deptid));
			$data = mysql_fetch_array($result);
			$mail->From = $data['email'];
			$mail->FromName = str_replace("&amp;", "&", $CONFIG['CompanyName'] . " " . $data['name']);
		}
		else {
			$mail->From = $fromemail;
			$mail->FromName = str_replace("&amp;", "&", $fromname);
		}

		$mail->Subject = $subject;
		$mail->CharSet = $CONFIG['Charset'];

		if ($CONFIG['MailType'] == "mail") {
			$mail->Mailer = "mail";
		}
		else {
			if ($CONFIG['MailType'] == "smtp") {
				$mail->IsSMTP();
				$mail->Host = $CONFIG['SMTPHost'];
				$mail->Port = $CONFIG['SMTPPort'];
				$mail->Hostname = $_SERVER['SERVER_NAME'];

				if ($CONFIG['SMTPSSL']) {
					$mail->SMTPSecure = $CONFIG['SMTPSSL'];
				}


				if ($CONFIG['SMTPUsername']) {
					$mail->SMTPAuth = true;
					$mail->Username = $CONFIG['SMTPUsername'];
					$mail->Password = decrypt($CONFIG['SMTPPassword']);
				}

				$mail->Sender = $mail->From;
			}
		}


		if ($plaintext) {
			$message = str_replace("<br>", "", $message);
			$message = str_replace("<br />", "", $message);
			$message = strip_tags($message);
			$mail->Body = html_entity_decode($message);
		}
		else {

			$message_text = str_replace("</p>", "\r\n\r\n", $message);

			$message_text = str_replace("<br>", "\r\n", $message_text);

			$message_text = str_replace("<br />", "\r\n", $message_text);

			$message_text = strip_tags($message_text);

			if ($CONFIG['LogoURL']) {
				$message = "<p><a href=\"" . $CONFIG['Domain'] . "\" target=\"_blank\"><img src=\"" . $CONFIG['LogoURL'] . "\" alt=\"" . $CONFIG['CompanyName'] . "\" border=\"0\" /></a></p>" . "\r\n" . $message;
			}


			if ($CONFIG['EmailCSS']) {
				$message = "<style>\r\n" . $CONFIG['EmailCSS'] . ("\r\n</style>\r\n" . $message);
			}

			$mail->Body = $message;
			$mail->AltBody = $message_text;
		}


		if ($adminid) {
			$where = "tbladmins.disabled=0 AND tbladmins.id='" . (int)$adminid . "'";

			if ($type == "support") {
				$where .= " AND tbladminroles.supportemails='1'";
			}
		}
		else {
			$where = "tbladmins.disabled=0 AND tbladminroles." . db_escape_string($to) . "emails='1'";

			if ($deptid) {
				$where .= " AND tbladmins.ticketnotifications!=''";
			}
		}

		$emailcount = 0;
		$result = select_query("tbladmins", "firstname,lastname,email,supportdepts,ticketnotifications", $where, "", "", "", "tbladminroles ON tbladminroles.id=tbladmins.roleid");

		while ($data = mysql_fetch_array($result)) {
			if ($data['email']) {
				$adminsend = true;

				if ($ticketnotify) {
					$ticketnotifications = $data['ticketnotifications'];
					$ticketnotifications = explode(",", $ticketnotifications);

					if (!in_array($deptid, $ticketnotifications)) {
						$adminsend = false;
					}
				}
				else {
					if ($deptid) {
						$supportdepts = $data['supportdepts'];
						$supportdepts = explode(",", $supportdepts);

						if (!in_array($deptid, $supportdepts)) {
							$adminsend = false;
						}
					}
				}


				if ($adminsend) {
					$mail->AddAddress(trim($data['email']), $data['firstname'] . " " . $data['lastname']);
					++$emailcount;
				}
			}
		}


		if ($copyto) {
			$copytoarray = explode(",", $copyto);

			if ($CONFIG['MailType'] == "mail") {
				foreach ($copytoarray as $copytoemail) {
					$mail->AddBCC(trim($copytoemail));
				}
			}
			else {
				foreach ($copytoarray as $copytoemail) {
					$mail->AddCC(trim($copytoemail));
				}
			}
		}


		if (!$emailcount) {
			return false;
		}


		if ($smtp_debug) {
			$mail->SMTPDebug = true;
		}


		if (!$mail->Send()) {
			logActivity("Admin Email Notification Sending Failed - " . $mail->ErrorInfo . (" (Subject: " . $subject . ")"), "none");
		}

		$mail->ClearAddresses();
	}

	function toMySQLDate($date) {
		global $CONFIG;

		if (($CONFIG['DateFormat'] == "DD/MM/YYYY" || $CONFIG['DateFormat'] == "DD.MM.YYYY") || $CONFIG['DateFormat'] == "DD-MM-YYYY") {
			$day = substr($date, 0, 2);
			$month = substr($date, 3, 2);
			$year = substr($date, 6, 4);
			$hours = substr($date, 11, 2);
			$minutes = substr($date, 14, 2);
			$seconds = substr($date, 17, 2);
		}
		else {
			if ($CONFIG['DateFormat'] == "MM/DD/YYYY") {
				$day = substr($date, 3, 2);
				$month = substr($date, 0, 2);
				$year = substr($date, 6, 4);
				$hours = substr($date, 11, 2);
				$minutes = substr($date, 14, 2);
				$seconds = substr($date, 17, 2);
			}
			else {
				if ($CONFIG['DateFormat'] == "YYYY/MM/DD" || $CONFIG['DateFormat'] == "YYYY-MM-DD") {
					$day = substr($date, 8, 2);
					$month = substr($date, 5, 2);
					$year = substr($date, 0, 4);
					$hours = substr($date, 11, 2);
					$minutes = substr($date, 14, 2);
					$seconds = substr($date, 17, 2);
				}
			}
		}


		if ($hours && !$seconds) {
			$seconds = "00";
		}

		$date = $year . "-" . $month . "-" . $day;

		if ($hours) {
			$date .= " " . $hours . ":" . $minutes . ":" . $seconds;
		}

		return $date;
	}

	function fromMySQLDate($date, $time = "", $client = "", $zerodateval = "") {
		global $CONFIG;
		global $timeoffset;

		if (substr($date, 0, 10) == "0000-00-00" && $zerodateval) {
			return $zerodateval;
		}

		$year = substr($date, 0, 4);
		$month = substr($date, 5, 2);
		$day = substr($date, 8, 2);
		$hours = substr($date, 11, 2);
		$minutes = substr($date, 14, 2);
		$seconds = substr($date, 17, 2);

		if ($timeoffset) {
			$hours = $hours + $timeoffset;
			$new_time = mktime($hours, $minutes, $seconds, $month, $day, $year);
			$year = date("Y", $new_time);
			$month = date("m", $new_time);
			$day = date("d", $new_time);
			$hours = date("H", $new_time);
			$minutes = date("i", $new_time);
			$seconds = date("s", $new_time);
		}


		if ($client && $CONFIG['ClientDateFormat']) {
			if ($CONFIG['ClientDateFormat'] == "full") {
				$date = date("jS F Y", mktime(0, 0, 0, $month, $day, $year));
			}
			else {
				if ($CONFIG['ClientDateFormat'] == "shortmonth") {
					$date = date("jS M Y", mktime(0, 0, 0, $month, $day, $year));
				}
				else {
					if ($CONFIG['ClientDateFormat'] == "fullday") {
						$date = date("l, F jS, Y", mktime(0, 0, 0, $month, $day, $year));
					}
				}
			}


			if ($time) {
				$date .= " (" . $hours . ":" . $minutes . ")";
			}
		}
		else {
			$date = $CONFIG['DateFormat'];
			$date = str_replace("YYYY", $year, $date);
			$date = str_replace("MM", $month, $date);
			$date = str_replace("DD", $day, $date);

			if ($time) {
				$date .= " " . $hours . ":" . $minutes;
			}
		}

		return $date;
	}

	function MySQL2Timestamp($datetime) {
		$val = explode(" ", $datetime, 2);
		$date = explode("-", $val[0]);

		if ($val[1]) {
			$time = explode(":", $val[1]);
		}
		else {
			$time = "00:00:00";
		}

		return mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
	}

	function getTodaysDate($client = "") {
		return fromMySQLDate(date("Y-m-d"), 0, $client);
	}

	function xdecrypt($ckey, $string) {
		$string = base64_decode($string);
		$keys = array();
		$c_key = base64_encode(sha1(md5($ckey)));
		$c_key = substr($c_key, 0, round(ord($ckey[0]) / 5));
		$c2_key = base64_encode(md5(sha1($ckey)));
		$last = strlen($ckey) - 1;
		$c2_key = substr($c2_key, 1, round(ord($ckey[$last]) / 7));
		$c3_key = base64_encode(sha1(md5($c_key) . md5($c2_key)));
		$mid = round($last / 2);
		$c3_key = substr($c3_key, 1, round(ord($ckey[$mid]) / 9));
		$c_key = $c_key . $c2_key . $c3_key;
		$c_key = base64_encode($c_key);
		$i = 0;

		while ($i < strlen($c_key)) {
			$keys[] = $c_key[$i];
			++$i;
		}

		$i = 0;

		while ($i < strlen($string)) {
			$id = $i % count($keys);
			$ord = ord($string[$i]);
			$ord = $ord XOR ord ($keys[$id]);
			++$id;
			($ord = $ord AND ord ($keys[$id]));
			++$id;
			($ord = $ord OR ord ($keys[$id]));
			++$id;
      		$ord = $ord - ord ($keys[$id]);
			$string[$i] = chr($ord);
			++$i;
		}

		return base64_decode($string);
	}

	function AffiliatePayment($affaccid, $hostingid) {
		global $CONFIG;

		$payout = false;

		if ($affaccid) {
			$result = select_query("tblaffiliatesaccounts", "", array("id" => $affaccid));
		}
		else {
			$result = select_query("tblaffiliatesaccounts", "", array("relid" => $hostingid));
		}

		$data = mysql_fetch_array($result);
		$affaccid = $data['id'];
		$affid = $data['affiliateid'];
		$lastpaid = $data['lastpaid'];
		$relid = $data['relid'];
		$commission = calculateAffiliateCommission($affid, $relid, $lastpaid);
		$result = select_query("tblproducts", "tblproducts.affiliateonetime", array("tblhosting.id" => $relid), "", "", "", "tblhosting ON tblhosting.packageid=tblproducts.id");
		$data = mysql_fetch_array($result);
		$affiliateonetime = $data['affiliateonetime'];

		if ($affiliateonetime) {
			if ($lastpaid == "0000-00-00") {
				$payout = true;
			}
			else {
				$error = "This product is setup for a one time affiliate payment only and the commission has already been paid";
			}
		}
		else {
			$payout = true;
		}

		$result = select_query("tblaffiliates", "onetime", array("id" => $affid));
		$data = mysql_fetch_array($result);
		$onetime = $data['onetime'];

		if ($onetime && $lastpaid != "0000-00-00") {
			$payout = false;
			$error = "This affiliate is setup for a one time commission only on all products and that has already been paid";
		}


		if ($affaccid && $payout) {
			if ($CONFIG['AffiliatesDelayCommission']) {
				$clearingdate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG['AffiliatesDelayCommission'], date("Y")));
				insert_query("tblaffiliatespending", array("affaccid" => $affaccid, "amount" => $commission, "clearingdate" => $clearingdate));
			}
			else {
				$query = "UPDATE tblaffiliates SET balance=balance+" . db_escape_string($commission) . " WHERE id=" . (int)$affid;
				$result = full_query($query);
				insert_query("tblaffiliateshistory", array("affiliateid" => $affid, "date" => "now()", "affaccid" => $affaccid, "amount" => $commission));
			}

			update_query("tblaffiliatesaccounts", array("lastpaid" => "now()"), array("id" => $affaccid));
		}

		return $error;
	}

	function calculateAffiliateCommission($affid, $relid, $lastpaid = "") {
		global $CONFIG;
		static $AffCommAffiliatesData = array();

		$percentage = $fixedamount = "";
		$result = select_query("tblproducts", "tblproducts.affiliateonetime,tblproducts.affiliatepaytype,tblproducts.affiliatepayamount,tblhosting.amount,tblhosting.firstpaymentamount,tblhosting.billingcycle,tblhosting.userid,tblclients.currency", array("tblhosting.id" => $relid), "", "", "", "tblhosting ON tblhosting.packageid=tblproducts.id INNER JOIN tblclients ON tblclients.id=tblhosting.userid");
		$data = mysql_fetch_array($result);
		$userid = $data['userid'];
		$billingcycle = $data['billingcycle'];
		$affiliateonetime = $data['affiliateonetime'];
		$affiliatepaytype = $data['affiliatepaytype'];
		$affiliatepayamount = $data['affiliatepayamount'];
		$clientscurrency = $data['currency'];
		$amount = ((($lastpaid == "0000-00-00" || $billingcycle == "One Time") || $affiliateonetime) ? $data['firstpaymentamount'] : $data['amount']);

		if ($affiliatepaytype == "none") {
			return "0.00";
		}


		if ($affiliatepaytype) {
			if ($affiliatepaytype == "percentage") {
				$percentage = $affiliatepayamount;
			}
			else {
				$fixedamount = $affiliatepayamount;
			}
		}


		if (isset($AffCommAffiliatesData[$affid])) {
			$data = $AffCommAffiliatesData[$affid];
		}
		else {
			$result = select_query("tblaffiliates", "clientid,paytype,payamount,(SELECT currency FROM tblclients WHERE id=clientid) AS currency", array("id" => $affid));
			$data = mysql_fetch_array($result);
			$AffCommAffiliatesData[$affid] = $data;
		}

		$affuserid = $data['clientid'];
		$paytype = $data['paytype'];
		$payamount = $data['payamount'];
		$affcurrency = $data['currency'];

		if ($paytype) {
			$percentage = $fixedamount = "";

			if ($paytype == "percentage") {
				$percentage = $payamount;
			}
			else {
				$fixedamount = $payamount;
			}
		}


		if (!$fixedamount && !$percentage) {
			$percentage = $CONFIG['AffiliateEarningPercent'];
		}

		$commission = ($fixedamount ? convertCurrency($fixedamount, 1, $affcurrency) : convertCurrency($amount, $clientscurrency, $affcurrency) * ($percentage / 100));
		run_hook("CalcAffiliateCommission", array("affid" => $affid, "relid" => $relid, "amount" => $amount, "commission" => $commission));
		$commission = format_as_currency($commission);
		return $commission;
	}

	function logActivity($description, $userid = "") {
		global $remote_ip;
		static $username = null;

		if (!isset($username)) {
			if (isset($_SESSION['adminid'])) {
				$result = select_query("tbladmins", "username", array("id" => $_SESSION['adminid']));
				$data = mysql_fetch_array($result);
				$username = $data['username'];
			}
			else {
				if (isset($_SESSION['cid'])) {
					$username = "Sub-Account " . $_SESSION['cid'];
				}
				else {
					if (isset($_SESSION['uid'])) {
						$username = "Client";
					}
					else {
						$username = "System";
					}
				}
			}
		}


		if (!$userid && isset($_SESSION['uid'])) {
			$userid = $_SESSION['uid'];
		}

		insert_query("tblactivitylog", array("date" => "now()", "description" => $description, "user" => $username, "userid" => $userid, "ipaddr" => $remote_ip));
		run_hook("LogActivity", array("description" => $description, "user" => $username, "userid" => $userid, "ipaddress" => $remote_ip));
	}

	function addToDoItem($title, $description, $duedate = "", $status = "", $admin = "") {
		if (!$status) {
			$status = "Pending";
		}


		if (!$duedate) {
			$duedate = date("Y-m-d");
		}

		insert_query("tbltodolist", array("date" => "now()", "title" => $title, "description" => $description, "admin" => $admin, "status" => $status, "duedate" => $duedate));
	}

	function generateUniqueID($type = "") {
		$z = 0;

		if ($type == "") {
			$length = 10;
		}
		else {
			$length = 6;
		}


		while ($z <= 0) {
			$seedsfirst = "123456789";
			$seeds = "0123456789";
			$str = null;
			$seeds_count = strlen($seeds) - 1;
			$i = 0;

			while ($i < $length) {
				if ($i == 0) {
					$str .= $seedsfirst[rand(0, $seeds_count - 1)];
				}
				else {
					$str .= $seeds[rand(0, $seeds_count)];
				}

				++$i;
			}


			if ($type == "") {
				$result = select_query("tblorders", "id", array("ordernum" => $str));
				$data = mysql_fetch_array($result);
				$id = $data['id'];

				if ($id == "") {
					$z = 1;
					continue;
				}

				continue;
			}


			if ($type == "tickets") {
				$result = select_query("tbltickets", "id", array("tid" => $str));
				$data = mysql_fetch_array($result);
				$id = $data['id'];

				if ($id == "") {
					$z = 1;
					continue;
				}

				continue;
			}
		}

		return $str;
	}

	function foreignChrReplace($arr) {
		global $CONFIG;

		$cleandata = array();

		if (is_array($arr)) {
			foreach ($arr as $key => $val) {

				if (is_array($val)) {
					$cleandata[$key] = foreignChrReplace($val);
					continue;
				}


				if (function_exists("hook_transliterate")) {
					$cleandata[$key] = hook_transliterate($val);
					continue;
				}

				$cleandata[$key] = foreignChrReplace2($val);
			}
		}
		else {
			if (function_exists("hook_transliterate")) {
				$cleandata = hook_transliterate($arr);
			}
			else {
				foreignChrReplace2($arr);
				$cleandata = "";
			}
		}

		return $cleandata;
	}

	function foreignChrReplace2($string) {
		global $CONFIG;

		$accents = "/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig|tilde|ring|slash|zlig|elig|quest|caron);/";
		$string = htmlentities($string, ENT_NOQUOTES, $CONFIG['Charset']);
		$string = preg_replace($accents, "$1", $string);
		$string = html_entity_decode($string, ENT_NOQUOTES, $CONFIG['Charset']);

		if ((function_exists("mb_internal_encoding") && function_exists("mb_regex_encoding")) && function_exists("mb_ereg_replace")) {
			mb_internal_encoding("UTF-8");
			mb_regex_encoding("UTF-8");
			$changeKey = array("g" => "g", "ü" => "u", "s" => "s", "ö" => "o", "i" => "i", "ç" => "c", "G" => "G", "Ü" => "U", "S" => "S", "Ö" => "O", "I" => "I", "Ç" => "C");
			foreach ($changeKey as $i => $u) {
				$string = mb_ereg_replace($i, $u, $string);
			}
		}

		return $string;
	}

	function getModRewriteFriendlyString($title) {
		$title = foreignChrReplace($title);
		$title = str_replace("#", "sharp", $title);
		$title = str_replace("&quot;", "", $title);
		$title = str_replace("/", "or", $title);
		$title = str_replace("&amp;", "and", $title);
		$title = str_replace("&", "and", $title);
		$title = str_replace("+", "plus", $title);
		$title = str_replace("=", "equals", $title);
		$title = str_replace("@", "at", $title);
		$title = str_replace(" ", "-", $title);
		$title = preg_replace("/[^0-9a-zA-Z-]/i", "", $title);
		return $title;
	}

	function titleCase($title) {
		$smallwordsarray = array("of", "a", "the", "and", "an", "or", "nor", "but", "is", "if", "then", "else", "when", "at", "from", "by", "on", "off", "for", "in", "out", "over", "to", "into", "with");
		$words = explode(" ", $title);
		foreach ($words as $key => $word) {

			if ($key == 0 || !in_array($word, $smallwordsarray)) {
				$words[$key] = ucwords($word);
				continue;
			}
		}

		$newtitle = implode(" ", $words);
		return $newtitle;
	}

	function sanitize($str) {
		return $str;
	}

	/**
	 * Parse XML string to a PHP Array
	 *
	 * Expect list nodes of the same name to have their nodeName
	 * altered:
	 * <clients>
	 *   <client><name>foo</name></client>
	 *   <client><name>bar</name></client>
	 * <clients>
	 * becomes
	 * Array("clients => Array(
	 *          "client" => Array("name" => "foo"),
	 *          "client1" => Array("name" => "foo"),
	 *       )
	 *     )
	 * while text nodes of the same name will be become ordinal values
	 * <clients>
	 *   <client>foo</client>
	 *   <client>bar</client>
	 * </clients>
	 * becomes
	 * Array("clients" => Array(
	 *          "client" => Array(
	 *            0 => "foo",
	 *            1 => "bar",
	 *           )
	 *       )
	 *     )
	 *
	 * This is done for backwards compatibility for places that depend on the
	 * obscure behavior of node renaming as originally provided by XMLtoARRAY()
	 *
	 * @param string $rawxml Well-formed XML string to parse into a native PHP Array
	 * @return array
	 */
	function ParseXmlToArray($rawxml, $options = array()) {
		$xml_parser = xml_parser_create();
		$options = (is_array($options) ? $options : array());
		foreach ($options as $opt => $value) {
			xml_parser_set_option($xml_parser, $opt, $value);
		}

		xml_parse_into_struct($xml_parser, $rawxml, $vals, $index);
		xml_parser_free($xml_parser);
		$params = array();
		$level = array();
		$alreadyused = array();
		$x = 0;
		foreach ($vals as $xml_elem) {

			if ($xml_elem['type'] == "open") {
				if (in_array($xml_elem['tag'], $alreadyused)) {
					++$x;
					$xml_elem['tag'] = $xml_elem['tag'] . $x;
				}

				$level[$xml_elem['level']] = $xml_elem['tag'];
				$alreadyused[] = $xml_elem['tag'];
			}


			if ($xml_elem['type'] == "complete") {
				$tag_value = (isset($xml_elem['value']) ? $xml_elem['value'] : null);
				$data = array($xml_elem['tag'] => $tag_value);
				$do_levels = $xml_elem['level'] - 1;

				while (0 < $do_levels) {
					$data = array($level[$do_levels] => $data);
					--$do_levels;
				}

				$params = array_merge_recursive($params, $data);
				continue;
			}
		}

		return $params;
	}

	function XMLtoARRAY($rawxml) {
		return ParseXmlToArray($rawxml);
	}

	function format_as_currency($amount) {
		$amount += 9.99999999999999954748112e-7;
		$amount = round($amount, 2);
		$amount = sprintf("%01.2f", $amount);
		return $amount;
	}

	function encrypt($string) {
		global $cc_encryption_hash;

		$key = md5(md5($cc_encryption_hash)) . md5($cc_encryption_hash);
		$hash_key = _hash($key);
		$hash_length = strlen($hash_key);
		$iv = _generate_iv();
		$out = "";
		$c = 0;

		while ($c < $hash_length) {
			$out .= chr(ord($iv[$c]) ^ ord($hash_key[$c]));
			++$c;
		}

		$key = $iv;
		$c = 0;

		while ($c < strlen($string)) {
			if ($c != 0 && $c % $hash_length == 0) {
				$key = _hash($key . substr($string, $c - $hash_length, $hash_length));
			}

			$out .= chr(ord($key[$c % $hash_length]) ^ ord($string[$c]));
			++$c;
		}

		$out = base64_encode($out);
		return $out;
	}

	function decrypt($string) {
		global $cc_encryption_hash;

		$key = md5(md5($cc_encryption_hash)) . md5($cc_encryption_hash);
		$hash_key = _hash($key);
		$hash_length = strlen($hash_key);
		$string = base64_decode($string);
		$tmp_iv = substr($string, 0, $hash_length);
		$string = substr($string, $hash_length, strlen($string) - $hash_length);
		$iv = $out = "";
		$c = 0;

		while ($c < $hash_length) {
			$iv .= chr(ord($tmp_iv[$c]) ^ ord($hash_key[$c]));
			++$c;
		}

		$key = $iv;
		$c = 0;

		while ($c < strlen($string)) {
			if ($c != 0 && $c % $hash_length == 0) {
				$key = _hash($key . substr($out, $c - $hash_length, $hash_length));
			}

			$out .= chr(ord($key[$c % $hash_length]) ^ ord($string[$c]));
			++$c;
		}

		return $out;
	}

	function _hash($string) {
		if (function_exists("sha1")) {
			$hash = sha1($string);
		}
		else {
			$hash = md5($string);
		}

		$out = "";
		$c = 0;

		while ($c < strlen($hash)) {
			$out .= chr(hexdec($hash[$c] . $hash[$c + 1]));
			$c += 2;
		}

		return $out;
	}

	function _generate_iv() {
		global $cc_encryption_hash;

		srand((double)microtime() * 1000000);
		$iv = md5(strrev(substr($cc_encryption_hash, 13)) . substr($cc_encryption_hash, 0, 13));
		$iv .= rand(0, getrandmax());
		$iv .= serialize(array("key" => md5(md5($cc_encryption_hash)) . md5($cc_encryption_hash)));
		return _hash($iv);
	}

	function getUsersLang($userid) {
		global $whmcs;
		global $_LANG;
		static $DefaultLang = array();

		if (!$DefaultLang) {
			$DefaultLang = $_LANG;
		}

		$result = select_query("tblclients", "language", array("id" => $userid));
		$data = mysql_fetch_array($result);
		$language = $data['language'];

		if (!$language) {
			$_LANG = $DefaultLang;
			return null;
		}

		$whmcs->loadLanguage($language);
	}

	function getCurrency($userid = "", $cartcurrency = "") {
		static $usercurrencies = array();
		static $currenciesdata = array();

		if ($cartcurrency) {
			$currencyid = $cartcurrency;
		}


		if ($userid) {
			if (isset($usercurrencies[$userid])) {
				$currencyid = $usercurrencies[$userid];
			}
			else {
				$currencyid = $usercurrencies[$userid] = get_query_val("tblclients", "currency", array("id" => $userid));
			}
		}


		if (isset($currencyid)) {
			if (isset($currenciesdata[$currencyid])) {
				$data = $currenciesdata[$currencyid];
			}
			else {
				$currenciesdata[$currencyid] = $data = get_query_vals("tblcurrencies", "", array("id" => $currencyid));
			}
		}
		else {
			$data = get_query_vals("tblcurrencies", "", array("`default`" => "1"));
		}

		$currency_array = array("id" => $data['id'], "code" => $data['code'], "prefix" => $data['prefix'], "suffix" => $data['suffix'], "format" => $data['format'], "rate" => $data['rate']);
		return $currency_array;
	}

	function formatCurrency($amount) {
		global $currency;

		$amount += 9.99999999999999954748112e-7;
		$amount = round($amount, 2);

		if ($currency['format'] == 1) {
			$format_dm = "2";
			$format_dp = ".";
			$format_ts = "";
		}
		else {
			if ($currency['format'] == 2) {
				$format_dm = "2";
				$format_dp = ".";
				$format_ts = ",";
			}
			else {
				if ($currency['format'] == 3) {
					$format_dm = "2";
					$format_dp = ",";
					$format_ts = ".";
				}
				else {
					if ($currency['format'] == 4) {
						$format_dm = "0";
						$format_dp = "";
						$format_ts = ",";
					}
				}
			}
		}

		$amount = $currency['prefix'] . number_format($amount, $format_dm, $format_dp, $format_ts) . $currency['suffix'];
		return $amount;
	}

	function convertCurrency($amount, $from, $to, $base_currency_exchange_rate = "") {
		if (!$base_currency_exchange_rate) {
			$result = select_query("tblcurrencies", "rate", array("id" => $from));
			$data = mysql_fetch_array($result);
			$base_currency_exchange_rate = $data['rate'];
		}

		$result = select_query("tblcurrencies", "rate", array("id" => $to));
		$data = mysql_fetch_array($result);
		$convertto_currency_exchange_rate = $data['rate'];

		if (!$base_currency_exchange_rate) {
			$base_currency_exchange_rate = 1;
		}


		if (!$convertto_currency_exchange_rate) {
			$convertto_currency_exchange_rate = 1;
		}

		$convertto_amount = format_as_currency($amount / $base_currency_exchange_rate * $convertto_currency_exchange_rate);
		return $convertto_amount;
	}

	function getClientGroups() {
		$retarray = array();
		$result = select_query("tblclientgroups", "", "");

		while ($data = mysql_fetch_array($result)) {
			$retarray[$data['id']] = array("name" => $data['groupname'], "colour" => $data['groupcolour'], "discountpercent" => $data['discountpercent'], "susptermexempt" => $data['susptermexempt'], "separateinvoices" => $data['separateinvoices']);
		}

		return $retarray;
	}

	function curlCall($url, $postfields, $curlopts = array()) {
		global $debug_output;

		if (!array_key_exists("CURLOPT_TIMEOUT", $curlopts)) {
			$curlopts['CURLOPT_TIMEOUT'] = 20;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		if ($postfields) {
			$fieldstring = $postfields;

			if (is_array($fieldstring)) {
				$fieldstring = "";
				foreach ($postfields as $k => $v) {
					$fieldstring .= "" . $k . "=" . urlencode($v) . "&";
				}
			}

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldstring);
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $curlopts['CURLOPT_TIMEOUT']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		if (array_key_exists("HEADER", $curlopts)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $curlopts['HEADER']);
		}

		$retval = curl_exec($ch);

		if (curl_errno($ch)) {
			$retval = "CURL Error: " . curl_errno($ch) . " - " . curl_error($ch);
		}

		curl_close($ch);

		if ($debug_output) {
			echo (("<textarea rows=\"12\" cols=\"120\">URL: " . $url . "\r\n") . "\r\nData: " . $fieldstring . "\r\n") . "\r\nResponse: " . $retval . "</textarea><br>";
		}

		return $retval;
	}

	function get_token() {
		$token_manager = &getTokenManager();

		return $token_manager->getToken();
	}

	function set_token($token) {
		$token_manager = &getTokenManager();

		return $token_manager->setToken($token);
	}

	function conditionally_set_token() {
		$token_manager = &getTokenManager();

		return $token_manager->conditionallySetToken();
	}

	function generate_token($type = "form") {
		$token_manager = &getTokenManager();

		return $token_manager->generateToken($type);
	}

	function check_token($namespace = "WHMCS.default") {
		$token_manager = &getTokenManager();

		return $token_manager->checkToken($namespace);
	}

	function getTokenManager($instance = null) {
		global $whmcs;
		static $token_manager = null;

		if (!$token_manager) {
			include_once ROOTDIR . "/includes/classes/class.TokenManager.php";

			if (!$instance) {
				$instance = $whmcs;
			}

			$token_manager = WHMCS_TokenManager::init($instance);
		}

		return $token_manager;
	}

	function localAPI($cmd, $apivalues1, $adminuser = "") {
		global $whmcs;
		global $CONFIG;
		global $_LANG;
		global $currency;

		if (!$adminuser && !$_SESSION['adminid']) {
			return array("result" => "error", "message" => "Admin User var is required if no admin is logged in");
		}


		if (!is_array($apivalues1)) {
			$apivalues1 = array();
		}

		$startadminid = ($_SESSION['adminid'] ? $_SESSION['adminid'] : "");

		if ($adminuser) {
			if (is_numeric($adminuser)) {
				$where = array("id" => $adminuser);
			}
			else {
				$where = array("username" => $adminuser);
			}

			$result = select_query("tbladmins", "id", $where);
			$data = mysql_fetch_array($result);
			$adminid = $data['id'];

			if (!$adminid) {
				return array("result" => "error", "message" => "No matching admin user found");
			}

			$_SESSION['adminid'] = $adminid;
		}

		$_POSTbackup = $_POST;
		$_REQUESTbackup = $_REQUEST;
		$_POST = $_REQUEST = array();
		foreach ($apivalues1 as $k => $v) {
			$$k = $_REQUEST[$k] = $_POST[$k] = $v;
		}

		$whmcs->replace_input($apivalues1);
		$cmd = strtolower($cmd);

		if (!isValidforPath($cmd) || !file_exists(ROOTDIR . "/includes/api/" . $cmd . ".php")) {
			return array("result" => "error", "message" => "Invalid API Command");
		}

		require ROOTDIR . "/includes/api/" . $cmd . ".php";
		foreach ($apivalues1 as $k => $v) {
			unset($_REQUEST[$k]);
			unset($_POST[$k]);
		}

		$whmcs->reset_input();
		$_POST = $_POSTbackup;
		$_REQUEST = $_REQUESTbackup;

		if ($startadminid) {
			$_SESSION['adminid'] = $startadminid;
		}
		else {
			unset($_SESSION['adminid']);
		}

		return $apiresults;
	}

	function redir($vars = "", $file = "") {
		if (!$file) {
			$file = $_SERVER['PHP_SELF'];
		}

		$filenamePattern = "/^[a-zA-Z0-9\._\/\:\-]*$/";

		if (preg_match($filenamePattern, $file) !== 1) {
			exit(sprintf("Invalid filename for redirect: %s", $file));
		}


		if (is_string($vars) && strpos($vars, "=") !== false) {
			$urlEncodedNewline = urlencode("\r\n");

			$urlEncodeCarrageReturn = urlencode("\r\n");
			$newlinePattern = "/[\r\n\r\n]|(" . $urlEncodedNewline . ")|(" . $urlEncodedCarrageReturn . ")/i";
			$vars = sprintf("?%s", preg_replace($newlinePattern, "", trim($vars)));
		}
		else {
			if ($vars) {
				exit(sprintf("URL parameter variables must be in the form of an HTTP build query string"));
			}
		}

		header(sprintf("Location: %s%s", $file, $vars));
		exit();
	}

	function logModuleCall($module, $action, $request, $response, $arraydata = "", $replacevars = array()) {
		global $CONFIG;

		if (!$CONFIG['ModuleDebugMode']) {
			return false;
		}


		if (!$module) {
			return false;
		}


		if (!$action) {
			$action = "Unknown";
		}


		if (is_array($request)) {
			$request = print_r($request, true);
		}


		if (is_array($response)) {
			$response = print_r($response, true);
		}


		if (is_array($arraydata)) {
			$arraydata = print_r($arraydata, true);
		}

		foreach ($replacevars as $v) {
			$replacevar = "";
			$i = 0;

			while ($i < strlen($v)) {
				$replacevar .= "*";
				++$i;
			}

			$request = str_replace($v, $replacevar, $request);
			$response = str_replace($v, $replacevar, $response);
			$arraydata = str_replace($v, $replacevar, $arraydata);
		}

		insert_query("tblmodulelog", array("date" => "now()", "module" => strtolower($module), "action" => strtolower($action), "request" => $request, "response" => $response, "arrdata" => $arraydata));
	}

	function releaseSession() {
		session_write_close();
	}

	function wSetCookie($name, $value, $expires = 0, $secure = false) {
		return WHMCS_Cookie::set($name, $value, $expires, $secure);
	}

	function wGetCookie($name, $array = false) {
		return WHMCS_Cookie::get($name, $array);
	}

	function wDelCookie($name) {
		return WHMCS_Cookie::delete($name);
	}

	function updateService($fields, $serviceid = "") {
		if (!$serviceid && isset($GLOBALS['moduleparams'])) {
			$serviceid = $GLOBALS['moduleparams']['serviceid'];
		}


		if (!count($fields) || !$serviceid) {
			return false;
		}


		if (isset($fields['password']) && strlen($fields['password'])) {
			$fields['password'] = encrypt($fields['password']);
		}

		update_query("tblhosting", $fields, array("id" => $serviceid));
		return true;
	}

	function genRandomVal($len = 12) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVYWXYZ0123456789";
		$str = "";
		$seeds_count = strlen($chars) - 1;
		$i = 0;

		while ($i < $len) {
			$str .= $chars[rand(0, $seeds_count)];
			++$i;
		}

		return $str;
	}

	function autoHyperLink($message) {
		$message = preg_replace("/((http(s?):\/\/)|(www\.))([\w\.]+)([a-zA-Z0-9?&%#~.;:\/=+_-]+)/i", "<a href=\"http://\" target=\"_blank\"></a>", $message);
		return $message;
	}

	function isValidforPath($name) {
		if (!is_string($name) || empty($name)) {
			return false;
		}


		if (!ctype_alnum(str_replace(array("_", "-"), "", $name))) {
			return false;
		}

		return true;
	}
}

?>