<?php

    /**
    * GoCardless WHMCS module
    *
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

    # get gateway params using WHMCS getGatewayVariables method
    $gateway = getGatewayVariables('gocardless');

    # sanity check to ensure module is active
    if (!$gateway['type']) die("Module Not Activated");

    # set relevant API information for GoCardless module
    gocardless_set_account_details($gateway);

    # get the raw contents of the callback and decode JSON
    $webhook = file_get_contents('php://input');
    $webhook_array = json_decode($webhook, true);

    # validate the webhook by verifying the integrity of the payload with GoCardless
    if(GoCardless::validate_webhook($webhook_array['payload']) !== true) {
        # we could not validate the web hook
        header('HTTP/1.1 400 Bad Request');
        exit(__LINE__.': Payload could not be verified');
    }

    # store various elements of the webhook array into params
    $val = $webhook_array['payload'];

    # base what we are doing depending on the resource type
    switch($val['resource_type']) {

        case 'pre_authorization':

        # handle preauths (possible actions - cancelled, expired)
        switch($val['action']) {

            # handle cancelled or expired preauths
            case 'cancelled':
            case 'expired':
                # delete related preauths
                foreach ($val['pre_authorizations'] as $aPreauth) {
                    # find preauth in tblhosting and empty out the subscriptionid field
                    update_query('tblhosting',array('subscriptionid' => ''),array('subscriptionid'    => $aPreauth['id']));
                    # log each preauth that has been cancelled
                    logTransaction($gateway['paymentmethod'],'GoCardless Preauthorisation Cancelled ('.$aPreauth['id'].')','Cancelled');
                }
                break;
            default:
                # we cannot handle this request
                header('HTTP/1.1 400 Bad Request');
                exit(__LINE__.': Unknown pre-authorisation action');
                break;
        }

        break;
        case 'bill':

        # handle bills (possible actions - created, failed, paid, withdrawn, refunded)
        switch($val['action']) {

            case 'paid':
                # loop through batch of bills and process appropriately, adding the
                # bill amount to the invoice once complete
                foreach($val['bills'] as $aBill) {

                    # get the associated invoiceID based on the bill ID
                    $invoiceID = mysql_result(full_query("SELECT `invoiceid` FROM `mod_gocardless` WHERE `resource_id` = '".mysql_real_escape_string($aBill['id'])."' OR `setup_id` = '".mysql_real_escape_string($aBill['id'])."'"),0,0);

                    # verify we have been able to get the invoice ID
                    if($invoiceID) {

                        # get the userID to process the currency
                        $userID = mysql_result(select_query('tblinvoices','userid',array('id' => $invoiceID)),0,0);

                        # verify the invoice ID (to ensure it exists) and transaction ID to ensure it is unique
                        checkCBInvoiceID($invoiceID, $gateway['paymentmethod']);
                        checkCBTransID($aBill['id']);

                        # calculate GoCardless fees
                        $aBill['fees'] = ($aBill['amount'] - $aBill['amount_minus_fees']);

                        # convert the currency where necessary
                        $aCurrency = getCurrency($userID);
                        if($gateway['convertto'] && ($aCurrency['id'] != $gateway['convertto'])) {
                            # the users currency is not the same as the GoCardless currency, convert to the users currency
                            $aBill['amount'] = convertCurrency($aBill['amount'],$gateway['convertto'],$aCurrency['id']);
                            $aBill['fees']   = convertCurrency($aBill['fees'],$gateway['convertto'],$aCurrency['id']);
                        }


                        # if we get to this point, we have verified the callback and performed sanity checks
                        # add a payment to the invoice and create a transaction log

                        if ($gateway['instantpaid'] == 'off') {
                            # No invoice payment will have been recorded yet, so now we'll record it
                            addInvoicePayment($invoiceID, $aBill['id'], $aBill['amount'], $aBill['fees'], $gateway['paymentmethod']);
                        }
                        logTransaction($gateway['paymentmethod'], 'The bill '.$aBill['id'].' for invoice #'.$invoiceID . 'has been successfully marked as paid.', 'Successful');

                        # clean up for next loop
                        unset($invoiceID,$userID);

                    } else {
                        header('HTTP/1.1 400 Bad Request');
                        logTransaction($gateway['paymentmethod'],'Could not find invoice with ID. callback.php ' . __LINE__ . $invoiceID,'Failed');
                        exit(__LINE__.': Could not get invoice ID for ' . htmlentities($aBill['id']));
                    }

                }
                break;

            case 'failed':
            case 'refunded':
                # loop through each bill that has failed or been refunded
                foreach($val['bills'] as $aBill) {

                    # attempt to obtain the mod_gocardless record
                    $invoiceID = mysql_result(full_query("SELECT `invoiceid` FROM `mod_gocardless` WHERE `resource_id` = '".mysql_real_escape_string($aBill['id'])."' OR `setup_id` = '".mysql_real_escape_string($aBill['id'])."'"),0,0);

                    # verify we have been able to get the invoice ID
                    if($invoiceID) {

                        # load the corresponding invoice in $aInvoice array
                        $aInvoice = mysql_fetch_assoc(select_query('tblinvoices','status',array('id' => $invoiceID)));

                        # mark the GoCardless record as failed (this will be displayed on the admin invoice page)
                        full_query("UPDATE `mod_gocardless` SET `payment_failed` = 1 WHERE `resource_id` = '".mysql_real_escape_string($aBill['id'])."' OR `setup_id` = '".mysql_real_escape_string($aBill['id'])."' AND `payment_failed` = '0'");

                        # check if the invoice is marked as paid already
                        if($aInvoice['status'] == 'Paid') {
                            # the invoice is marked as paid already (mark as paid instantly)
                            update_query('tblinvoices', array('status' => 'Unpaid'), array('id' => $invoiceID));
                        }

                        # update the corresponding transaction to mark as FAIL and mark the invoice as unpaid
                        update_query('tblaccounts', array('amountin' => "0", 'fees' => "0", 'transid' => (($val['action'] == 'failed') ? 'FAIL_' : 'REFUND_') . $aBill['id']),array('invoiceid' => $invoiceID, 'transid' => $aBill['id']));

                        # log the failed/refunded transaction in the gateway log as status 'Payment Failed/Refunded'
                        logTransaction($gateway['paymentmethod'],"GoCardless Payment {$val['action']}.\r\nPreauth ID: {$aBill['source_id']}\nBill ID: {$aBill['id']}: " . print_r($aBill,true),'Bill ' . ucfirst($val['action']));

                        # clean up for next loop
                        unset($invoiceID,$userID);

                    } else {
                        header('HTTP/1.1 400 Bad Request');
                        logTransaction($gateway['paymentmethod'],'Could not find invoice with ID. callback.php ' . __LINE__ . $invoiceID,'Failed');
                        exit;
                    }
                }
                break;

            case 'created':
                # we dont want to handle created bills
                foreach($val['bills'] as $aBill) {
                    logTransaction($gateway['paymentmethod'],'GoCardless Bill Created ('.$aBill['id'].')','Bill Created');
                }
                break;
        }
        break;
        default:
            header('HTTP/1.1 400 Bad Request');
            logTransaction($gateway['paymentmethod'],'Could not determine given resource type. callback.php ' . __LINE__ . $invoiceID,'Failed');
            exit(__LINE__.': Could not determine given resource type');
            break;
    }

    # if we get to this point we are done
    header('HTTP/1.1 200 OK');