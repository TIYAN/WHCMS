<?php
    /**
    * GoCardless WHMCS module
    *
    * @author WHMCSRC <info@whmcs.com>
    * @version 1.1.0
    */

    # load GoCardless library
    require_once ROOTDIR . '/modules/gateways/gocardless/GoCardless.php';

    define('GC_VERSION', '1.1.0');

    function po($val,$kill=true) {
        echo '<pre>'.print_r($val,true);$kill ? exit : null;
    }

    /**
    ** GoCardless configuration for WHMCS
    ** This method is used by WHMCS to establish the configuration information
    ** used within the admin interface. These params are then stored in `tblpaymentgateways`
    **/
    function gocardless_config() {

        global $CONFIG;
        if (!empty($CONFIG['SystemSSLURL']) && $CONFIG['SystemSSLURL'] != "") {
            $systemUrl = $CONFIG['SystemSSLURL'];
        } else {
            $systemUrl = $CONFIG['SystemURL'];
        }

        $aConfig = array(
            'FriendlyName' => array(
                'Type' => 'System',
                'Value' => 'GoCardless'
            ),
            'UsageNotes' => array(
                'Type' => 'System',
                'Value' => "Please make sure your credentials are correct before saving.)<br /><br /><i>You're running GoCardless for WHMCS v" . GC_VERSION . ". You must set your <strong>Webhook URI</strong> and <strong>Redirect URI</strong> within the 'Developer' tab on GoCardless to <strong>{$systemUrl}/modules/gateways/gocardless/callback.php</strong> and <strong>{$systemUrl}/modules/gateways/gocardless/redirect.php</strong> respectively in both the live and sandbox environments.<br /><br />A full guide to configuring WHMCS with GoCardless is available <a href='https://gocardless.com/partners/whmcs-detailed'>here</a>. For help, please email <a href='mailto:help@gocardless.com'>help@gocardless.com</a>."
            ),
            'merchant_id' => array(
                'FriendlyName' => 'Merchant ID',
                'Type' => 'text',
                'Size' => '15',
                'Description' => '<a href="http://gocardless.com/merchants/new" target="_blank">Sign up</a> for a GoCardless account then find your API keys in the Developer tab.'
            ),
            'app_id' => array(
                'FriendlyName' => 'App ID',
                'Type' => 'text',
                'Size' => '100'
            ),
            'app_secret' => array(
                'FriendlyName' => 'App Secret',
                'Type' => 'text',
                'Size' => '100'
            ),
            'access_token' => array(
                'FriendlyName' => 'Access Token',
                'Type' => 'text',
                'Size' => '100'
            ),
            'test_mode' => array(
                'FriendlyName' => 'Sandbox Mode',
                'Type' => 'yesno',
                'Description' => 'Tick to enable the GoCardless sandbox environment where real payments will not be taken. You will need to have set the specific sandbox keys below.'
            ),
            'dev_merchant_id' => array(
                'FriendlyName' => 'Sandbox Merchant ID',
                'Type' => 'text',
                'Size' => '15',
                'Description' => 'Use your GoCardless login details to access the <a href="http://sandbox.gocardless.com/" target="_blank">Sandbox</a> and then find your API keys in the Developer tab'
            ),
            'dev_app_id' => array(
                'FriendlyName' => 'Sandbox App ID',
                'Type' => 'text',
                'Size' => '100'
            ),
            'dev_app_secret' => array(
                'FriendlyName' => 'Sandbox App Secret',
                'Type' => 'text',
                'Size' => '100'
            ),
            'dev_access_token' => array(
                'FriendlyName' => 'Sandbox Access Token',
                'Type' => 'text',
                'Size' => '100'
            ),
            'instantpaid' => array(
                'FriendlyName' => 'Instant Activation',
                'Type' => 'yesno',
                'Description' => 'Tick to immediately mark invoices paid after payment is initiated (despite clearing not being confirmed for 3-5 working days). With this enabled, payments can still fail later on, and in this event the invoice will be marked as "unpaid".'
            ),
            'oneoffonly' => array(
                'FriendlyName' => 'One Off Only',
                'Type' => 'yesno',
                'Description' => 'Tick to only perform one off captures - no recurring pre-authorization agreements will be created.'
            )
        );

        return $aConfig;

    }

    /**
    * Checks whether test mode is enabled or disabled
    * and sets appropriate details against GoCardless object
    * @param array $params Array of parameters that contains gateway details
    */
    function gocardless_set_account_details($params=null) {

        # check if params have been supplied, if not attempt
        # to use global params
        if(is_null($params)) {
            unset($params);
            global $params;
        }
        global $CONFIG;

        # check we have been able to obtain the correct params
        if(!isset($params['app_id'])) {
            throw new Exception('Could not get GoCardless params');
        }

        if (!empty($CONFIG['SystemSSLURL']) && $CONFIG['SystemSSLURL'] != "") {
            $systemUrl = $CONFIG['SystemSSLURL'];
        } else {
            $systemUrl = $CONFIG['SystemURL'];
        }

        # check if we are running in Sandbox mode (test_mode)
        if($params['test_mode'] == 'on') {
            # Initialise SANDBOX Account Details
            GoCardless::$environment = 'sandbox';
            GoCardless::set_account_details(array(
                    'app_id'        => $params['dev_app_id'],
                    'app_secret'    => $params['dev_app_secret'],
                    'merchant_id'   => $params['dev_merchant_id'],
                    'access_token'  => $params['dev_access_token'],
                    'redirect_uri'  => $systemUrl.'/modules/gateways/gocardless/redirect.php',
                    'ua_tag'        => 'gocardless-whmcs/v' . GC_VERSION
                ));
        } else {
            # Initialise LIVE Account Details
			GoCardless::$environment = 'production';
            GoCardless::set_account_details(array(
                    'app_id'        => $params['app_id'],
                    'app_secret'    => $params['app_secret'],
                    'merchant_id'   => $params['merchant_id'],
                    'access_token'  => $params['access_token'],
                    'redirect_uri'  => $systemUrl.'/modules/gateways/gocardless/redirect.php',
                    'ua_tag'        => 'gocardless-whmcs/v' . GC_VERSION
                ));
        }
    }

    /**
    ** Builds the payment link for WHMCS users to be redirected to GoCardless
    **/
    function gocardless_link($params) {

        # get global config params
        global $CONFIG;

        # create GoCardless database if it hasn't already been created
        gocardless_createdb();

        # check the invoice, to see if it has a record with a valid resource ID. If it does, the invoice is pending payment.
        # we will return a message on the invoice to prevent duplicate payment attempts
        $aGC = mysql_fetch_assoc(select_query('mod_gocardless','id,payment_failed', array('invoiceid' => $params['invoiceid'], 'resource_id' => array('sqltype' => 'NEQ', 'value' => ''))));
        if ($aGC['id']) {
            if($aGC['payment_failed'] == 0) {
                # Pending Payment Found - Prevent Duplicate Payment with a Msg
                return '<strong>Your payment is currently pending and will be processed within 3-5 days.</strong>';
            } else {
                # display a message to the user suggesting that a payment against the invoice has failed
                return '<strong>One or more payment attempts have failed against this invoice. Please contact our support department.</strong>';
            }

        }

        # get relevant invoice data
        $aRecurrings = getRecurringBillingValues($params['invoiceid']);
        $recurringcycleunit = strtolower(substr($aRecurrings['recurringcycleunits'],0,-1));

        # check a number of conditions to see if it is possible to setup a preauth
        if(($params['oneoffonly'] == 'on') ||
            ($aRecurrings === false) ||
            ($aRecurrings['recurringamount'] <= 0)) {
            $noPreauth = true;
        } else {
            $noPreauth = false;
        }

        # set appropriate GoCardless API details
        gocardless_set_account_details($params);

        # set user array based on params parsed to $link
        $aUser = array(
            'first_name'        => $params['clientdetails']['firstname'],
            'last_name'         => $params['clientdetails']['lastname'],
            'email'             => $params['clientdetails']['email'],
            'billing_address1'  => $params['clientdetails']['address1'],
            'billing_address2'  => $params['clientdetails']['address2'],
            'billing_town'      => $params['clientdetails']['city'],
            'billing_county'    => $params['clientdetails']['state'],
            'billing_postcode'  => $params['clientdetails']['postcode'],
        );

        $invoice_item_query = select_query('tblinvoiceitems', 'relid', array('invoiceid' => $params['invoiceid'], 'type' => 'Hosting'));

        while ($invoice_item = mysql_fetch_assoc($invoice_item_query)) {
            $package_query = select_query('tblhosting', 'subscriptionid', array('id' => $invoice_item['relid']));
            $package = mysql_fetch_assoc($package_query);

            if (!empty($package['subscriptionid'])) {
                $preauthExists = true;
            }
        }

        if ($preauthExists) {
            # The customer already has a pre-auth, but it's yet to be charged so
            # let's not let them set up another...
            return (GoCardless::$environment == 'sandbox' ? '<strong style="color: #FF0000; font-size: 16px;">SANDBOX MODE</strong><br />' : null) . '<strong>Automatic payments via Direct Debit or another payment method are already configured for this invoice. You will receive an email once you have been billed.</strong>';
        }
        elseif ($noPreauth) {
            # if one of the $noPreauth conditions have been met, display a one time payment button
            # we are making a one off payment, display the appropriate code
            # Button title
            $title = 'Pay Now with GoCardless';

            # create GoCardless one off payment URL using the GoCardless library
            $url = GoCardless::new_bill_url(array(
                    'amount'  => $params['amount'],
                    'name'    => "Invoice #" . $params['invoiceid'],
                    'user'    => $aUser,
                    'state'   => $params['invoiceid'] . ':' . $params['amount']
                ));

            # return one time payment button code
              $sButton = (GoCardless::$environment == 'sandbox' ? '<strong style="color: #FF0000; font-size: 16px;">SANDBOX MODE</strong><br />' : null) . '<a href="'.$url.'" onclick="window.location=\''.$url.'\';" style="text-decoration: none"><input onclick="window.location=\''.$url.'\';" type="button" value="'.$title.'" /></a>';

        } else {
            # we are setting up a preauth (description friendly name), display the appropriate code

            # get the invoice from the database because we need the invoice creation date
            $aInvoice = mysql_fetch_assoc(select_query('tblinvoices','date',array('id' => $params['invoiceid'])));

            # GoCardless only supports months in the billing period so
            # if WHMCS is sending a year value we need to address this
            if($recurringcycleunit == 'year') {
                $recurringcycleunit = 'month';
                $aRecurrings['recurringcycleperiod'] = ($aRecurrings['recurringcycleperiod']*12);
            }

            $pre_auth_maximum = 5000; # Always create a £5000 pre-auth

            # Button title
            $title = 'Create Subscription with GoCardless';

            # create GoCardless preauth URL using the GoCardless library
            $url = GoCardless::new_pre_authorization_url(array(
                    'max_amount' => $pre_auth_maximum,
                    # set the setup fee as the first payment amount - recurring amount
                    'setup_fee' => ($aRecurrings['firstpaymentamount'] > $aRecurrings['recurringamount']) ? ($aRecurrings['firstpaymentamount']-$aRecurrings['recurringamount']) : 0,
                    'name' => "Direct Debit payments to " . $CONFIG['CompanyName'],
                    'interval_length' => $aRecurrings['recurringcycleperiod'],
                    # convert $aRecurrings['recurringcycleunits'] to valid value e.g. day,month,year
                    'interval_unit' => $recurringcycleunit,
                    # set the start date to the creation date of the invoice - 2 days
                    'start_at' => date_format(date_create($aInvoice['date'].' -2 days'),'Y-m-d\TH:i:sO'),
                    'user' => $aUser,
                    'state' => $params['invoiceid'] . ':' . $aRecurrings['recurringamount']
                ));

            # return the recurring preauth button code
            $sButton =  (GoCardless::$environment == 'sandbox' ? '<strong style="color: #FF0000; font-size: 16px;">SANDBOX MODE</strong><br />' : null) . 'When you get to GoCardless you will see an agreement for the <b>maximum possible amount</b> we\'ll ever need to charge you in a single invoice for this order, with a frequency of the shortest item\'s billing cycle. But rest assured we will never charge you more than the actual amount due.
            <br /><a onclick="window.location=\''.$url.'\';" href="'.$url.'" style="text-decoration: none"><input type="button" onclick="window.location=\''.$url.'\';" value="'.$title.'" /></a>';

        }

        # return the formatted button
        return $sButton;
    }

    /**
    ** WHMCS method to capture payments
    ** This method is triggered by WHMCS in an attempt to capture a PreAuth payment
    **
    ** @param array $params Array of paramaters parsed by WHMCS
    **/
    function gocardless_capture($params) {

        # create GoCardless DB if it hasn't already been created
        gocardless_createdb();

        # grab the gateway information from WHMCS
        $gateway = getGatewayVariables('gocardless');

        # Send the relevant API information to the GoCardless class for future processing
        gocardless_set_account_details($params);

        # check against the database if the bill relevant to this invoice has already been created
        $existing_payment_query = select_query('mod_gocardless', 'resource_id', array('invoiceid' => $params['invoiceid']));
        $existing_payment = mysql_fetch_assoc($existing_payment_query);

        # check if any rows have been returned or if the returned result is empty.
        # If no rows were returned, the bill has not already been made for this invoice
        # If a row was returned but the resource ID is empty, the bill has not been completed
        # we have already raised a bill with GoCardless (in theory)
        if (!mysql_num_rows($existing_payment_query) || empty($existing_payment['resource_id'])) {

            # query the database to get the relid of all invoice items
            $invoice_item_query = select_query('tblinvoiceitems', 'relid', array('invoiceid' => $params['invoiceid'], 'type' => 'Hosting'));

            # loop through each returned (each invoice item) and attempt to find a subscription ID
            while ($invoice_item = mysql_fetch_assoc($invoice_item_query)) {
                $package_query = select_query('tblhosting', 'subscriptionid', array('id' => $invoice_item['relid']));
                $package = mysql_fetch_assoc($package_query);

                # if we have found a subscriptionID, store it in $preauthid
                if (!empty($package['subscriptionid'])) {
                    $preauthid = $package['subscriptionid'];
                }
            }

            # now we are out of the loop, check if we have been able to get the PreAuth ID
            if (isset($preauthid)) {

                # we have found the PreAuth ID, so get it from GoCardless and process a new bill

                $pre_auth = GoCardless_PreAuthorization::find($preauthid);

                # check the preauth returned something
                if($pre_auth) {

                    # Create a bill with the $pre_auth object
                    try {
                        $bill = $pre_auth->create_bill(array(
                            'amount' => $params['amount'],
                            'name' => "Invoice #" . $params['invoiceid']
                        ));
                    } catch (Exception $e) {
                        # we failed to create a new bill, lets update mod_gocardless to alert the admin why payment hasnt been received,
                        # log this in the transaction log and exit out
                        update_query('mod_gocardless', array('payment_failed' => 1),array('invoiceid' => $params['invoiceid']));
                        logTransaction($params['paymentmethod'],"Failed to create GoCardless bill against pre-authorization " . $preauthid . " for invoice " . $params['invoiceid'] . ": " . print_r($e,true) . print_r($bill,true),'Failed');
                        return array('status' => 'error', 'rawdata' => $e);
                    }

                    # check that the bill has been created
                    if ($bill->id) {
                        # check if the bill already exists in the database, if it does we will just update the record
                        # if not, we will create a new record and record the transaction
                        if (!mysql_num_rows($existing_payment_query)) {
                            # Add the bill ID to the table and mark the transaction as pending

                            insert_query('mod_gocardless', array('invoiceid' => $params['invoiceid'], 'billcreated' => 1, 'resource_id' => $bill->id, 'preauth_id'  => $pre_auth->id));

                            if ($gateway['instantpaid'] == on)  {
                                # The Instant Activation option is on, so add to the Gateway Log and log a transaction on the invoice
                                addInvoicePayment($params['invoiceid'], $bill->id, $bill->amount, $bill->gocardless_fees, $gateway['paymentmethod']);
                                logTransaction($gateway['paymentmethod'], 'Bill of ' . $bill->amount . ' raised and logged for invoice ' . $params['invoiceid'] . ' with GoCardless ID ' . $bill->id, 'Successful');
                                return array('status' => 'success', 'rawdata' => print_r($bill, true));
                            } else {
                                # Instant Activation is off, so just add to the gateway log and wait before marking as paid until web hook arrives
                                logTransaction($gateway['paymentmethod'], 'Bill of ' . $bill->amount . ' raised for invoice ' . $params['invoiceid'] . ' with GoCardless ID ' . $bill->id, 'Successful');
                                return array('status' => 'pending', 'rawdata' => print_r($bill, true));
                            }


                        } else {
                            # update the table with the bill ID
                            update_query('mod_gocardless', array('billcreated' => 1, 'resource_id' => $bill->id), array('invoiceid' => $params['invoiceid']));
                        }

                    }
                } else {
                    # PreAuth could not be verified
                    logTransaction($gateway['paymentmethod'], 'The pre-authorization specified for invoice ' . $params['invoiceid'] . ' (' . $preauthid . ') does not seem to exist - something has gone wrong, or the customer needs to set up their Direct Debit again.', 'Incomplete');
                    return array('status' => 'error', 'rawdata' => array('message' => 'The pre-authorization ID was found for invoice ' . $params['invoiceid'] . ' but it could not be fetched.'));
                }


            } else {
                # we couldn't find the PreAuthID meaning at this point all we can do is give up!
                # the client will have to setup a new preauth to begin recurring payments again
                # or pay using an alternative method
                logTransaction($gateway['paymentmethod'], 'No pre-authorization found when trying to raise payment for invoice ' . $params['invoiceid'] . ' - something has gone wrong, or the customer needs to set up their Direct Debit again.', 'Incomplete');
                return array('status' => 'error', 'rawdata' => array('message' => 'No pre-authorisation ID found in WHMCS for invoice ' . $params['invoiceid']));
            }

        } else {
            # WHMCS is trying to collect the bill but one has already been created - this happens because the bill is not mark as 'paid'
            # until a web hook is received by default, so WHMCS thinks it still needs to collect.
            # logTransaction('GoCardless', 'Bill already created - awaiting update via web hook...' . "\nBill ID: " . $existing_payment['resource_id'], 'Pending');
            # return array('status' => 'Bill already created - awaiting update via web hook...', 'rawdata' =>
            #    array('message' => 'Bill already created - awaiting update via web hook...'));
            return array('status' => 'pending', 'rawdata' => array('message' => 'The bill has already been created for invoice ' . $params['invoiceid']));
        }

    }

    /**
    ** Supress credit card request on checkout
    **/
    function gocardless_nolocalcc() {}

    /**
    ** Create mod_gocardless table if it does not already exist
    **/
    function gocardless_createdb() {
        # check the table exists
        if(mysql_num_rows(full_query("SHOW TABLES LIKE 'mod_gocardless'"))) {
            # the table exists, check its at the latest version
            if(mysql_num_rows(full_query("SHOW FULL COLUMNS FROM `mod_gocardless` LIKE 'preauth_id'")) == 0) {
                # we are running the old version of the table
                $query = "ALTER TABLE `mod_gocardless`
                          ADD COLUMN `setup_id` varchar(16) default NULL,
                          ADD COLUMN `preauth_id` varchar(16) default NULL,
                          ADD COLUMN `payment_failed` varchar(16) NOT NULL default '0',
                          ADD CONSTRAINT UNIQUE KEY `invoiceid` (`invoiceid`),
                          ADD CONSTRAINT UNIQUE KEY `resource_id` (`resource_id`),
                          ADD CONSTRAINT UNIQUE KEY `setup_id` (`setup_id`)";

                full_query($query);
            }
        } else {
            # create the new table
            $query = "CREATE TABLE IF NOT EXISTS `mod_gocardless` (
            `id` int(11) NOT NULL auto_increment,
            `invoiceid` int(11) NOT NULL,
            `billcreated` int(11) default NULL,
            `resource_id` varchar(16) default NULL,
            `setup_id` varchar(16) default NULL,
            `preauth_id` varchar(16) default NULL,
            `payment_failed` tinyint(1) NOT NULL default '0',
            PRIMARY KEY  (`id`),
            UNIQUE KEY `invoiceid` (`invoiceid`),
            UNIQUE KEY `resource_id` (`resource_id`),
            UNIQUE KEY `setup_id` (`setup_id`))";

            full_query($query);
        }
    }

    /**
    ** Display payment status message to admin when the preauth
    ** has been setup but the payment is incomplete
    **/
    function gocardless_adminstatusmsg($vars) {

        if ($vars['status']=='Unpaid') {

            # get relevant invoice information from the database
            $d = select_query('mod_gocardless',"id,payment_failed",array('invoiceid' => $vars['invoiceid']));
            $aResult = mysql_fetch_assoc($d);

            # check we have been able to obtain the details
            if($aResult['id']) {
                if($aResult['payment_failed']) {
                    # if the payment failed flag is set, notify the admin of this problem
                    return array('type' => 'error', 'title' => 'GoCardless Payment Failed', 'msg' => 'One or more payments against this invoice have failed. By default, GoCardless will not attempt to make another payment.');
                } else {
                    # the record exists in the database, the invoice is unpaid and the payment hasnt failed
                    # this condition means that the payment must be pending!
                    return array('type' => 'info', 'title' => 'GoCardless Payment Pending', 'msg' => 'There is a pending payment already in processing for this invoice. Status will be automatically updated once confirmation is received back from GoCardless.' );
                }
            }
            unset($d,$aResult);
        }

    }
