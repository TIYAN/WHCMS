<?php

    /**
    * GoCardless WHMCS module redirect.php
    * This file confirms verifies a preauth and creates a bill underneath it
    * Either a one of payment (bill) or a pre authorisation can be handled by this file
    * @author WHMCS <info@whmcs.com>
    * @version 1.1.0
    */

    # load all required files
    $whmcsdir = dirname(__FILE__) . '/../../../';

    require_once $whmcsdir . 'dbconnect.php';
    require_once $whmcsdir . '/includes/functions.php';
    // Looking for WHMCS 5.2 compatability? Comment the above two lines with
    // "//" and then uncomment the line below:
    //
    // require_once $whmcsdir . 'init.php';

    require_once $whmcsdir . '/includes/gatewayfunctions.php';
    require_once $whmcsdir . '/includes/invoicefunctions.php';
    require_once $whmcsdir . '/modules/gateways/gocardless.php';

    # get gateway params
    $gateway = getGatewayVariables('gocardless');

    # sanity check to ensure module is active
    if (!$gateway['type']) die("Module Not Activated");

    # set relevant API information for GoCardless module
    gocardless_set_account_details($gateway);


    # if the resource ID and resouce type are set, confirm it using the GoCardless API
    if (isset($_GET['resource_id']) && isset($_GET['resource_type'])) {

        # if GoCardless fails to confirm the resource, an exception will be thrown
        # we will handle the exception gracefully
        try {
            $confirmed_resource = GoCardless::confirm_resource(array(
                    'resource_id'   => $_GET['resource_id'],
                    'resource_type' => $_GET['resource_type'],
                    'resource_uri'  => $_GET['resource_uri'],
                    'signature'     => $_GET['signature'],
                    'state'         => $_GET['state']
                ));
        } catch(Exception $e) {
            # failed to verify the resource with GoCardless. Log transaction and ouput error message to client
            logTransaction($gateway['paymentmethod'],'GoCardless Redirect Failed (Resource not verified) : ' .print_r($_GET,true) . 'Exception: ' . print_r($e,true),'Unsuccessful');
            header('HTTP/1.1 400 Bad Request');
            exit('Your request could not be completed');
        }

    } else {
        # failed to get resource ID and resource type, invalid request. Log transaction and ouput error message to client
        logTransaction($gateway['paymentmethod'],'GoCardless Redirect Failed (No data provided) : ' .print_r($_GET,true),'Unsuccessful');
        header('HTTP/1.1 400 Bad Request');
        exit('Your request could not be completed');
    }

    # split invoice data into invoiceID and invoiceAmount
    list($invoiceID) = explode(':', $_GET['state']);

    # check we have the invoiceID
    if($invoiceID) {
        # get the invoice amount and user ID by querying the invoice table
        $aResult = mysql_fetch_array(select_query('tblinvoices','userid,total',array('id' => $invoiceID)));
        $userID = $aResult['userid'];
        $invoiceAmount = $aResult['total'];

        # check this invoice exists (halt execution if it doesnt)
        checkCbInvoiceID($invoiceID, $gateway['paymentmethod']);

        # get user ID and gateway ID for use further down the script
        $gatewayID = mysql_result(select_query('tblclients', 'gatewayid', array('id' => $userID)),0,0);

        # if the user records gateway is blank, set it to gocardless
        if (empty($gatewayID)) {
            update_query('tblclients', array('gatewayid' => $gateway['paymentmethod']), array('id' => $userID));
        }

        # check if we are handling a preauth or a one time bill
        switch ($_GET['resource_type']) {

            case "pre_authorization":

            # get the confirmed resource (pre_auth) and created a referenced param $pre_auth
            $pre_auth = &$confirmed_resource;

            # check if we have a setup_fee
            $setup_id = false;
            $setup_amount = 0;
            if($pre_auth->setup_fee > 0) {
                # store the bill in $oSetupBill for later user
                $aoSetupBills = $pre_auth->bills();
                $oSetupBill = $aoSetupBills[0];
                $setup_id = $oSetupBill->id;
                $setup_amount = $oSetupBill->amount;
                unset($aoSetupBills);
            }

            # create a GoCardless bill and store it in $bill
            try {
                $amount_to_charge = $invoiceAmount - $setup_amount;
                $oBill = $pre_auth->create_bill(array(
                    'amount' => $amount_to_charge,
                    'name' => "Invoice #" . $invoiceID
                ));
            } catch (Exception $e) {
                # log that we havent been able to create the bill and exit out
                logTransaction($gateway['paymentmethod'],'Failed to create new bill: ' . print_r($e,true),'GoCardless Error');
                exit('Your request could not be completed');
            }

			try {
				# if we have been able to create the bill, the preauth ID being null suggests payment is pending
				# (this will display in the admin)
				if ($oBill->id) {
                    if($setup_id) {
                        # if we have a setup ID, we want to insert this into the query
                        if(!insert_query('mod_gocardless', array('invoiceid' => $invoiceID, 'billcreated' => 1, 'resource_id' => $oBill->id, 'setup_id' => $setup_id, 'preauth_id' => $pre_auth->id))) {
                            throw new Exception('Failed to record new mod_gocardless record for bill #'.$oBill->id);
                        }
                    } else {
                        # no setup ID bill
                        if(!insert_query('mod_gocardless', array('invoiceid' => $invoiceID, 'billcreated' => 1, 'resource_id' => $oBill->id, 'preauth_id' => $pre_auth->id))) {
                            throw new Exception('Failed to record new mod_gocardless record for bill #'.$oBill->id);
                        }
                    }
				} else {
					throw new Exception('Could not create GoCardless bill on Preauth #'.$pre_auth->id);
				}
			} catch (Exception $e) {
				logTransaction($gateway['paymentmethod'],$e->getMessage().'Exception Details: ' . print_r($e,true)."\r\nBill Details: " . print_r($oBill,true) . "\r\nInvoice #".$invoiceID);
				exit('Failed to record transaction, please contact support for more details.');
			}

            # query tblinvoiceitems to get the related service ID
            # update subscription ID with the resource ID on all hosting services corresponding with the invoice
            $d = select_query('tblinvoiceitems', 'relid', array('type' => 'Hosting', 'invoiceid' => $invoiceID));
            while ($res = mysql_fetch_assoc($d)) {
                update_query('tblhosting', array('subscriptionid' => $pre_auth->id), array('id' => $res['relid']));
            }

            # clean up
            unset($d,$res);
            break;

            case 'bill':
                # the response is a one time bill, we need to add the bill to the database
                $oBill = $confirmed_resource;
                insert_query('mod_gocardless', array('invoiceid' => $invoiceID, 'billcreated' => 1, 'resource_id' => $oBill->id));
                break;

            default:
                # we cannot handle anything other than a bill or preauths
                header('HTTP/1.1 400 Bad Request');
                exit('Your request could not be completed');
                break;
        }

        if($gateway['instantpaid'] == on) {
            # The "Instant Activation" option is enabled, so we need to mark now

            # convert currency where necessary (GoCardless only handles GBP)
            $aCurrency = getCurrency($res['userid']);
            if($gateway['convertto'] && ($aCurrency['id'] != $gateway['convertto'])) {
                # the users currency is not the same as the GoCardless currency, convert to the users currency
                $oBill->amount = convertCurrency($oBill->amount,$gateway['convertto'],$aCurrency['id']);
                $oBill->gocardless_fee = convertCurrency($oBill->gocardless_fee,$gateway['convertto'],$aCurrency['id']);

                # currency conversion on the setup fee bill
                if(isset($oSetupBill)) {
                    $oSetupBill->amount = convertCurrency($oBill->amount,$gateway['convertto'],$aCurrency['id']);
                    $oSetupBill->gocardless_fee = convertCurrency($oBill->gocardless_fee,$gateway['convertto'],$aCurrency['id']);

                }
            }

            # check if we are handling a preauth setup fee
            # if we are then we need to add it to the total bill
            if(isset($oSetupBill)) {
                addInvoicePayment($invoiceID, $oSetupBill->id, $oSetupBill->amount, $oSetupBill->gocardless_fees, $gateway['paymentmethod']);
                logTransaction($gateway['paymentmethod'], 'Setup fee of ' . $oSetupBill->amount . ' raised and logged for invoice ' . $invoiceID . ' with GoCardless ID ' . $oSetupBill->id, 'Successful');
            }

            # Log the payment for the amount of the main bill against the inovice
            addInvoicePayment($invoiceID, $oBill->id, $oBill->amount, $oBill->gocardless_fees, $gateway['paymentmethod']);
            logTransaction($gateway['paymentmethod'], 'Bill of ' . $oBill->amount . ' raised and logged for invoice ' . $invoiceID . ' with GoCardless ID ' . $oBill->id, 'Successful');
        } else {
            # Instant activation isn't enabled, so we will log in the Gateway Log but will not put anything on the invoice

            if(isset($oSetupBill)) {
                logTransaction($gateway['paymentmethod'], 'Setup fee bill ' . $oSetupBill->id . ' (' . $oSetupBill->amount . ') and bill ' . $oBill->id . ' (' . $oBill->amount . ') raised with GoCardless for invoice ' . $invoiceID . ', but not marked on invoice.', 'Pending');
            } else {
                logTransaction($gateway['paymentmethod'], 'Bill ' . $oBill->id . ' (' . $oBill->amount . ') raised with GoCardless for invoice ' . $invoiceID . ', but not marked on invoice.', 'Pending');
            }
        }

        # if we get to this point, we have verified everything we need to, redirect to invoice
        $systemURL = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
        header('HTTP/1.1 303 See Other');
        header("Location: {$systemURL}/viewinvoice.php?id={$invoiceID}");
        exit();

    } else {
        # we could not get an invoiceID so cannot process this further
        header('HTTP/1.1 400 Bad Request');
        exit('Your request could not be completed');
}