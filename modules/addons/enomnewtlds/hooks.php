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

function enomnewtlds_hook_cronjob() {
	global $enomnewtlds_hook_message;
	global $enomnewtlds_hook_BatchSize;
	global $enomnewtlds_hook_processed;
	global $enomnewtlds_DBName;
	global $enomnewtlds_hook_defaultgateway;

	$enomnewtlds_hook_message = "";
	enomnewtlds_hook_Helper_Log( "Starting New TLD Watchlist Cron Job" );
	enomnewtlds_hook_DB_GetCreateHookTable();
	enomnewtlds_hook_DB_GetBatchSize();
	$data = enomnewtlds_hook_DB_GetWatchlistSettingsLocal();
	$enomnewtlds_hook_defaultgateway = enomnewtlds_hook_DB_GetSystemDefaultGateway();
	$portalid = $data['portalid'];
	$enomuid = $data['enomlogin'];
	$enompw = $data['enompassword'];
	$enabled = $data['enabled'];
	$configured = $data['configured'];
	$companyname = $data['companyname'];
	$companyurl = $data['companyurl'];
	$supportemail = $data['supportemail'];
	$environment = enomnewtlds_hook_Helper_Getenvironment( $data['environment'] );
	$batches = 1;
	$enomnewtlds_hook_processed = 0;

	if ( !$enabled || !$configured ) {
		enomnewtlds_hook_Helper_Log( "Module is not configured." );
		return null;
	}

	enomnewtlds_hook_Helper_Log2( "******* Settings ******" );
	enomnewtlds_hook_Helper_Log2( "Enabled = " . $enabled );
	enomnewtlds_hook_Helper_Log2( "Configured = " . $configured );
	enomnewtlds_hook_Helper_Log2( "Portal ID = " . $portalid );
	enomnewtlds_hook_Helper_Log2( "Enom Login = " . $enomuid );
	enomnewtlds_hook_Helper_Log2( "Company Name = " . $companyname );
	enomnewtlds_hook_Helper_Log2( "Company URL = " . $companyurl );
	enomnewtlds_hook_Helper_Log2( "Batch Size = " . $enomnewtlds_hook_BatchSize );
	enomnewtlds_hook_Helper_Log2( "<br /><br />" );
	$fields = array();
	$fields['portalid'] = $portalid;
	$fields['uid'] = $enomuid;
	$fields['pw'] = $enompw;
	$fields['recordcount'] = $enomnewtlds_hook_BatchSize;
	enomnewtlds_hook_Helper_Log( "Calling eNom API To get the awarded domains" );
	$xmldata = enomnewtlds_hook_API_GetAwardedDomains( $fields );
	$success = $xmldata->ErrCount == 0;

	if ($success) {
		$returnedCount = $xmldata->Domains->DomainCount;
		$totalCount = $xmldata->Domains->TotalDomainCount;
		enomnewtlds_hook_Helper_Log( "Got " . $returnedCount . " domains returned, " . $totalCount . " total domains to process" );

		if ( 0 < (int)$returnedCount && 0 < (int)$totalCount ) {
			if ((int)$returnedCount < (int)$totalCount) {
				$batches = ceil( (int)$totalCount / (int)$returnedCount );
			}

			enomnewtlds_hook_Helper_Log2( "batches = " . $batches );
			enomnewtlds_hook_Helper_Log( "Batch size is " . $enomnewtlds_hook_BatchSize . " and with " . $totalCount . " domains returned, there are a total of " . $batches . " batches to process" );
			enomnewtlds_hook_ProcessDomains( $xmldata, $returnedCount, $totalCount, 1 );
			enomnewtlds_hook_DumpProcessedDomains();
			$i = 1;

			while ($i <= $batches) {
				enomnewtlds_hook_Helper_Log2( "Processing a batch - #" . $i . "/" . $batches );
				enomnewtlds_hook_ProcessBatch( $data, $returnedCount, $totalCount, $i + 1 );
				enomnewtlds_hook_DumpProcessedDomains();
				++$i;
			}
		}
	}
	else {
		enomnewtlds_hook_Helper_Log( "API ERRORS!" );
		$errcnt = $xmldata->ErrCount;
		$i = 1;

		while ($i <= $errcnt) {
			$err = $xmldata->errors=="Err" . $i;

			if ($i < $errcnt) {
				$result .= $err . "<br />";
			}

			enomnewtlds_hook_Helper_Log( "Error " . $i . " = " . $err );
			++$i;
		}


		if (!$result) {
			enomnewtlds_hook_Helper_Log( "UNKNOWN ERROR" );
		}
	}

	enomnewtlds_hook_DumpProcessedDomains();
	enomnewtlds_hook_Helper_Log( "Processed " . $enomnewtlds_hook_processed . " domains" );

	if (!enomnewtlds_hook_Helper_IsNullOrEmptyString( $supportemail )) {
		enomnewtlds_hook_Helper_SendEmail( $supportemail, "Watchlist CronJob Results final", $enomnewtlds_hook_message );
	}

}


function enomnewtlds_hook_ProcessBatch($data, $returnedCount, $totalCount, $batch) {
	global $enomnewtlds_hook_message;
	global $enomnewtlds_hook_BatchSize;
	global $enomnewtlds_hook_processed;

	$portalid = $data['portalid'];
	$enomuid = $data['enomlogin'];
	$enompw = $data['enompassword'];
	$enabled = $data['enabled'];
	$configured = $data['configured'];
	$companyname = $data['companyname'];
	$companyurl = $data['companyurl'];
	$environment = enomnewtlds_hook_Helper_Getenvironment( $data['environment'] );
	$fields = array();
	$fields['portalid'] = $portalid;
	$fields['uid'] = $enomuid;
	$fields['pw'] = $enompw;
	$fields['recordcount'] = $enomnewtlds_hook_BatchSize;
	enomnewtlds_hook_Helper_Log( "Calling eNom API To get the awarded domains" );
	$xmldata = enomnewtlds_hook_API_GetAwardedDomains( $fields );
	enomnewtlds_hook_ProcessDomains( $xmldata, $returnedCount, $totalCount, $batch );
}


function enomnewtlds_hook_ProcessDomains($xmldata, $returnedCount, $totalCount, $batch) {
	global $enomnewtlds_hook_message;
	global $enomnewtlds_hook_BatchSize;
	global $enomnewtlds_hook_processed;

	$processed = 0;
	enomnewtlds_hook_Helper_Log( "Starting Batch " . $batch );
	foreach ($xmldata->Domains->Domain as $details) {
		++$processed;
		++$enomnewtlds_hook_processed;
		$domain = $details->DomainName;
		$email = $details->EmailAddress;
		$expdate = $details->ExpirationDate;
		$regdate = $details->RegisterDate;
		$domainnameid = $details->PortalDomainId;
		$userid = $details->ForeignLoginId;
		$regperiod = $details->RegistrationPeriod;
		$provisioned = $details->ResellerProvisioned;
		$dtregdate = new DateTime( $regdate );
		$dtexpdate = new DateTime( $expdate );
		$currency = getCurrency( $userid );
		$from = get_query_val( "tblcurrencies", "id", array( "code" => "USD" ) );
		$to = $currency['id'];

		if (!$from) {
			$from = "1";
		}

		$convert = $to != $from;
		$regprice = ($convert ? convertCurrency( $details->RegisterPrice, $from, $to ) : $details->RegisterPrice);
		$renewprice = ($convert ? convertCurrency( $details->RenewPrice, $from, $to ) : $details->RenewPrice);
		$gateway = enomnewtlds_hook_DB_GetUserDefaultGateway( $userid );
		$fields = array( "userid" => $userid, "type" => "Register", "registrationdate" => $dtregdate->format( "Y-m-d" ), "domain" => $domain, "firstpaymentamount" => $regprice, "recurringamount" => $renewprice, "registrar" => "enom", "registrationperiod" => $regperiod, "expirydate" => $dtexpdate->format( "Y-m-d" ), "nextduedate" => $dtexpdate->format( "Y-m-d" ), "nextinvoicedate" => $dtexpdate->format( "Y-m-d" ), "status" => "Active", "paymentmethod" => $gateway, "dnid" => $domainnameid, "provisioned" => $provisioned, "email" => $email );
		$result = enomnewtlds_hook_DB_InsertDomain( $fields );

		if ($result) {
			enomnewtlds_hook_AddDomainToUpdateList( $domainnameid );
			enomnewtlds_hook_DB_InsertIntoCronTable( $fields );
			continue;
		}
	}

	enomnewtlds_hook_Helper_Log( "Finished with Batch " . $batch . " - Processed " . $processed . " domains." );
}


function enomnewtlds_hook_DumpProcessedDomains() {
	global $enomnewtlds_hook_DomainsToUpdate;

	if (0 < count( $enomnewtlds_hook_DomainsToUpdate )) {
		$domains = implode( ",", $enomnewtlds_hook_DomainsToUpdate );
		$fields = array();
		enomnewtlds_hook_Helper_Log( "Calling eNom API To Update the awarded domains statuses" );
		$xmldata = enomnewtlds_hook_API_SetAwardedDomains( $fields );
		$success = $xmldata->ErrCount == 0;

		if ($success) {
			enomnewtlds_hook_DB_UpdateProvisionedCronTable( $domains );
		}
	}

	$enomnewtlds_hook_DomainsToUpdate = array();
}


function enomnewtlds_hook_DB_GetSystemDefaultGateway() {
	$result = full_query( "SELECT g.gateway FROM `tblpaymentgateways` g inner join `tblpaymentgateways` gg on g.gateway=gg.gateway where gg.setting='visible' and gg.value='on' ORDER BY 'order' ASC LIMIT 0,1" );
	$data = mysql_fetch_array( $result );
	return $data[0];
}


function enomnewtlds_hook_DB_GetUserDefaultGateway($userid) {
	global $enomnewtlds_hook_defaultgateway;

	$result = select_query( "tblclients", "defaultgateway", array( "id" => $userid ) );
	$data = mysql_fetch_array( $result );

	if ($data[0]) {
		return $data[0];
	}

	return $enomnewtlds_hook_defaultgateway;
}


function enomnewtlds_hook_DB_GetCreateHookTable() {
	global $enomnewtlds_CronDBName;

	if (!enomnewtlds_hook_DB_TableExists()) {
		full_query( "CREATE TABLE IF NOT EXISTS `" . mysql_real_escape_string( $enomnewtlds_CronDBName ) . "` (
            `id` INT( 100 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `domainname` VARCHAR( 272 ) NOT NULL,
            `domainnameid` INT ( 10 ) NOT NULL,
            `emailaddress` VARCHAR( 272 ) NOT NULL,
            `expdate` VARCHAR( 272 ) NOT NULL,
            `regdate` VARCHAR( 272 ) NOT NULL,
            `userid` VARCHAR( 272 ) NOT NULL ,
            `regprice` VARCHAR( 272 ) NOT NULL ,
            `renewprice` VARCHAR( 272 ) NOT NULL,
            `regperiod` INT( 2 ) NOT NULL DEFAULT  '1' ,
            `provisioned` INT( 1 ) NOT NULL DEFAULT  '0',
            `provisiondate` VARCHAR( 272 ) NULL )
             ENGINE = MYISAM;" );
		full_query( "ALTER TABLE " . mysql_real_escape_string( $enomnewtlds_CronDBName ) . "
              ADD CONSTRAINT UniqueDomainName
                UNIQUE (domainname);" );
	}


	if (!mysql_num_rows( full_query( "select * from `tblconfiguration` where setting='enomnewtlds_cronbatchsize';" ) )) {
		full_query( "insert into `tblconfiguration` (Setting, value) VALUES('enomnewtlds_cronbatchsize', '50');" );
	}

}


function enomnewtlds_hook_DB_TableExists() {
	global $enomnewtlds_CronDBName;

	if (!mysql_num_rows( full_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( $enomnewtlds_CronDBName ) . "'" ) )) {
		return false;
	}

	return true;
}


function enomnewtlds_hook_DB_GetWatchlistSettingsLocal() {
	global $enomnewtlds_hook_DefaultEnvironment;
	global $enomnewtlds_DBName;

	$result = select_query( $enomnewtlds_DBName, "enabled,configured,portalid,environment,enomlogin,enompassword,companyname,companyurl", array() );
	$data = mysql_fetch_array( $result );

	if (enomnewtlds_hook_Helper_IsNullOrEmptyString( $data['portalid'] )) {
		$data['portalid'] = "0";
	}


	if (enomnewtlds_hook_Helper_IsNullOrEmptyString( $data['enompassword'] )) {
		$data['enompassword'] = "";
	}
	else {
		$data['enompassword'] = decrypt( $data['enompassword'] );
	}


	if (enomnewtlds_hook_Helper_IsNullOrEmptyString( $data['enomlogin'] )) {
		$data['enomlogin'] = "";
	}
	else {
		$data['enomlogin'] = decrypt( $data['enomlogin'] );
	}


	if (enomnewtlds_hook_Helper_IsNullOrEmptyString( $data['companyname'] )) {
		$data['companyname'] = "";
	}


	if (enomnewtlds_hook_Helper_IsNullOrEmptyString( $data['companyurl'] )) {
		$data['companyurl'] = "";
	}


	if (enomnewtlds_hook_Helper_IsNullOrEmptyString( $data['environment'] )) {
		$data['environment'] = $enomnewtlds_hook_DefaultEnvironment;
	}

	return $data;
}


function enomnewtlds_hook_DB_InsertDomain($domain) {
	$domainname = $domain['domain'];

	if ( is_array( $domain ) && !enomnewtlds_hook_Helper_IsNullOrEmptyString( $domainname ) ) {
		if (!enomnewtlds_hook_DB_DomainExists( $domainname )) {
			$values = array( "userid" => $domain['userid'], "type" => "Register", "registrationdate" => $domain['registrationdate'], "domain" => $domain['domain'], "firstpaymentamount" => $domain['firstpaymentamount'], "recurringamount" => $domain['recurringamount'], "registrar" => "enom", "registrationperiod" => $domain['registrationperiod'], "expirydate" => $domain['expirydate'], "nextduedate" => $domain['nextduedate'], "nextinvoicedate" => $domain['nextinvoicedate'], "status" => "Active", "paymentmethod" => $domain['paymentmethod'] );
			$result = insert_query( "tbldomains", $values );
		}
		else {
			enomnewtlds_hook_Helper_Log( "Domain name " . $domainname . " already exists in the table, cannot and should not re-insert!" );
			$result = true;
		}
	}
	else {
		enomnewtlds_hook_Helper_Log( "Domain name is null, empty, blank or domain Object is not an array!" );
		$result = false;
	}

	return $result;
}


function enomnewtlds_hook_DB_GetBatchSize() {
	global $enomnewtlds_hook_BatchSize;

	$result = select_query( "tblconfiguration", "value", array( "setting" => "enomnewtlds_cronbatchsize" ) );
	$data = mysql_fetch_array( $result );

	if (!$data) {
		$enomnewtlds_hook_BatchSize = "25";
	}
	else {
		$enomnewtlds_hook_BatchSize = $data[0];
	}

	enomnewtlds_hook_Helper_Log2( "Setting batch size = " . $enomnewtlds_hook_BatchSize );
	return $enomnewtlds_hook_BatchSize;
}


function enomnewtlds_hook_DB_InsertIntoCronTable($values) {
	global $enomnewtlds_CronDBName;

	$sql = "replace into " . mysql_real_escape_string( $enomnewtlds_CronDBName ) . "
                        (domainname, domainnameid, emailaddress, expdate, regdate, userid, regprice, renewprice, regperiod, provisioned)
                Values (
                '" . $values['domain'] . "',
                '" . $values['dnid'] . "',
                '" . $values['email'] . "',
                '" . $values['expirydate'] . "',
                '" . $values['registrationdate'] . "',
                '" . $values['userid'] . "',
                '" . $values['firstpaymentamount'] . "',
                '" . $values['recurringamount'] . "',
                '" . $values['registrationperiod'] . "',
                '" . $values['provisioned'] . "' )";
	$result = full_query( $sql );
	return $result;
}


function enomnewtlds_hook_DB_UpdateProvisionedCronTable($domains) {
	global $enomnewtlds_CronDBName;

	$result = "0";

	if ($domains != "") {
		$time = enomnewtlds_hook_Helper_GetDateTime();
		$domainnameids = $sql = "update " . mysql_real_escape_string( $enomnewtlds_CronDBName ) . " set provisioned='1', provisiondate='" . mysql_real_escape_string( $time ) . "' where domainnameid in (" . $domains . ");";
		$result = full_query( $sql );
	}

	return $result;
}


function enomnewtlds_hook_DB_DomainExists($domain) {
	if (!mysql_num_rows( full_query( "select domain from tbldomains where domain='" . mysql_real_escape_string( $domain ) . "'" ) )) {
		return false;
	}

	return true;
}


function enomnewtlds_hook_Helper_Log($String) {
	enomnewtlds_hook_AddMessage( $String );
	logActivity( $String );
}


function enomnewtlds_hook_Helper_Log2($String) {
	enomnewtlds_hook_AddMessage( $String );
}


function enomnewtlds_hook_Helper_GetDateTime() {
	$t = microtime( true );
	$micro = sprintf( "%06d", ( $t - floor( $t ) ) * 1000000 );
	$d = new DateTime( "Y-m-d H:i:s." . $micro, $t )();
	return $d->format( "Y-m-d H:i:s" );
}


function enomnewtlds_hook_Helper_IsNullOrEmptyString($str) {
	return ( !isset( $str ) || trim( $str ) === "" ) || strlen( $str ) == 0;
}


function enomnewtlds_hook_Helper_startsWith($haystack, $needle) {
	$length = strlen( $needle );
	return substr( $haystack, 0, $length ) === $needle;
}


function enomnewtlds_hook_Helper_endsWith($haystack, $needle) {
	$length = strlen( $needle );

	if ($length == 0) {
		return true;
	}

	return substr( $haystack, 0 - $length ) === $needle;
}


function enomnewtlds_hook_Helper_FormatAPICallForEmail($fields, $environment) {
	$url = "https://" . enomnewtlds_hook_Helper_GetAPIHost( $environment ) . "/interface.asp?";
	foreach ($fields as $x => $y) {
		$url .= $x . "=" . $y . "&";
	}

	return $url;
}


function enomnewtlds_hook_Helper_GetAPIHost($environment) {
	switch ($environment) {
		case "1":
			$url = "resellertest.enom.com";
			break;

		case "2":
			$url = "api.staging.local";
			break;

		case "3":
			$url = "api.build.local";
			break;

		case "4":
			$url = "reseller-sb.enom.com";
			break;

		default:
			$url = "reseller.enom.com";
			break;
	}

	return $url;
}


function enomnewtlds_hook_Helper_GetWatchlistHost($environment) {
	switch ($environment) {
		case "1":
			$url = "resellertest.tldportal.com";
			break;

		case "2":
			$url = "tldportal.staging.local";
			break;

		case "3":
			$url = "tldportal.build.local";
			break;

		case "4":
			$url = "preprod.tldportal.com";
			break;

		default:
			$url = "tldportal.com";
			break;
	}

	return $url;
}


function enomnewtlds_hook_Helper_Getenvironment($environment) {
	global $enomnewtlds_hook_DefaultEnvironment;

	if (enomnewtlds_hook_Helper_IsNullOrEmptyString( $environment )) {
		$data = enomnewtlds_hook_DB_GetWatchlistSettingsLocal();
		$environment = $data['environment'];
	}

	return $environment;
}


function enomnewtlds_hook_Helper_SendEmail($to, $subject, $message) {
	$headers .= "Content-type: text/html; charset=iso-8859-1" . "\n";
	$headers .= "X-Mailer: PHP/" . phpversion();
	$message = wordwrap( $message, 70 );
	$headers = "MIME-Version: 1.0" . "\n";
}


function enomnewtlds_hook_AddDomainToUpdateList($domain) {
	global $enomnewtlds_hook_DomainsToUpdate;

	$enomnewtlds_hook_DomainsToUpdate[] = $domain;
}


function enomnewtlds_hook_AddError($error) {
	global $enomnewtlds_hook_errormessage;

	if (enomnewtlds_hook_Helper_IsNullOrEmptyString( $enomnewtlds_hook_errormessage )) {
		$enomnewtlds_hook_errormessage = $error;
	}
	else {
		$enomnewtlds_hook_errormessage .= "<br />" . $error;
	}

	enomnewtlds_hook_Helper_Log( "ERROR!! - " . $error );
}


function enomnewtlds_hook_AddMessage($message) {
	global $enomnewtlds_hook_message;

	if (enomnewtlds_hook_Helper_IsNullOrEmptyString( $enomnewtlds_hook_message )) {
		$enomnewtlds_hook_message = $message;
		return null;
	}

	$enomnewtlds_hook_message .= "<br />" . $message;
}


function enomnewtlds_hook_API_GetAwardedDomains($fields) {
	global $enomnewtlds_hook_errormessage;

	$postfields = array();
	$postfields['command'] = "PORTAL_GETAWARDEDDOMAINS";

	if (is_array( $fields )) {
		foreach ($fields as $x => $y) {
			$postfields[$x] = $y;
		}
	}

	$xmldata = enomnewtlds_hook_API_CallEnom( $postfields );
	return $xmldata;
}


function enomnewtlds_hook_API_SetAwardedDomains($fields) {
	global $enomnewtlds_hook_errormessage;
	global $enomnewtlds_hook_DomainsToUpdate;

	$postfields = array();
	$postfields['domainlist'] = implode( ",", $enomnewtlds_hook_DomainsToUpdate );
	$postfields['command'] = "PORTAL_UPDATEAWARDEDDOMAINS";

	if (is_array( $fields )) {
		foreach ($fields as $x => $y) {
			$postfields[$x] = $y;
		}
	}

	$xmldata = enomnewtlds_hook_API_CallEnom( $postfields );
	return $xmldata;
}


function enomnewtlds_hook_API_CallEnom($postfields) {
	global $enomnewtlds_hook_errormessage;
	global $enomnewtlds_ModuleName;

	$data = array();
	$environment = enomnewtlds_hook_Helper_Getenvironment( $data['environment'] );
	$portalid = $data['portalid'];

	if (!in_array( "uid", $postfields )) {
		$enomuid = $data['enomlogin'];
		$postfields['uid'] = $enomuid;
	}


	if (!in_array( "pw", $postfields )) {
		$enompw = $data['enompassword'];
		$postfields['pw'] = $enompw;
	}


	if (!in_array( "portalid", $postfields )) {
		if ( !enomnewtlds_hook_Helper_IsNullOrEmptyString( $portalid ) && 0 < (int)$portalid ) {
			$postfields['portalid'] = $portalid;
		}
	}

	$postfields['ResponseType'] = "XML";
	$postfields['Source'] = "WHMCS";
	$postfields['sourceid'] = "37";
	$url = "https://" . enomnewtlds_hook_Helper_GetAPIHost( $environment ) . "/interface.asp";
	$data = curlCall( $url, $postfields );
	enomnewtlds_hook_Helper_FormatAPICallForEmail( $postfields, $environment );
	$call = enomnewtlds_hook_DB_GetWatchlistSettingsLocal();
	$apiData = $call . "<br /><br /><br />" . htmlentities( $data, ENT_COMPAT | ENT_HTML401, "UTF-8" );
	enomnewtlds_hook_Helper_Log2( "API DATA = " . $apiData );
	enomnewtlds_hook_Helper_Log( "API DATA = " . $call . "<br /><br /><br />" . $data );
	$xmldata = simplexml_load_string( $data );
	logModuleCall( $enomnewtlds_ModuleName, $postfields['command'], $postfields, $data, $xmldata );
	return $xmldata;
}


function enomnewtlds_hook_clientareavars() {
	return array( "enomnewtldsenabled" => true );
}


function enomnewtlds_hook_clientareahomeoutput() {
	return "<div style=\"background:#0064CD;background:-moz-linear-gradient(top, #0064CD 0%, #207ce5 100%);background:-webkit-linear-gradient(top, #0064CD 0%,#207ce5 100%);background:-ms-linear-gradient(top, #0064CD 0%,#207ce5 100%);background:linear-gradient(to bottom, #0064CD 0%,#207ce5 100%);border:1px solid #0972E2;color:#FFF;font-family:Trebuchet MS, Trebuchet MS, sans-serif;font-size:18px;text-align:center;margin:24px 0;padding:14px;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;-webkit-box-shadow:0 5px 5px -3px rgba(0,0,0,0.3);-moz-box-shadow:0 5px 5px -3px rgba(0,0,0,0.3);box-shadow:0 5px 5px -3px rgba(0,0,0,0.3);zoom:1;\">
  The next generation of domains is coming! Take advantage of New TLD opportunities. <a href=\"index.php?m=enomnewtlds\" style=\"color:#FFD20A\">Learn More &raquo;</a>
</div>";
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

$enomnewtlds_hook_message = "";
$enomnewtlds_hook_processed = 0;
$enomnewtlds_hook_defaultgateway = "";
$enomnewtlds_hook_BatchSize = "50";
$enomnewtlds_hook_DomainsToUpdate = array();
$enomnewtlds_hook_DefaultEnvironment = "0";
$enomnewtlds_ModuleName = "enomnewtlds";
$enomnewtlds_DBName = "mod_" . $enomnewtlds_ModuleName;
$enomnewtlds_CronDBName = $enomnewtlds_DBName . "_cron";
add_hook( "DailyCronJob", 1, "enomnewtlds_hook_cronjob" );
add_hook( "ClientAreaPage", 1, "enomnewtlds_hook_clientareavars" );
add_hook( "ClientAreaHomepage", 1, "enomnewtlds_hook_clientareahomeoutput" );
?>