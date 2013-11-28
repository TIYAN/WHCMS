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

$whmcspath = "";
require dirname(__FILE__) . "/config.php";
require $whmcspath . "/init.php";
require ROOTDIR . "/includes/registrarfunctions.php";
$cron = WHMCS_Cron::init();
$cron->raiseLimits();
releaseSession();
$cronreport = "Domain Syncronisation Cron Report for " . date("d-m-Y H:i:s") . "<br />
<br />
";

if (!$CONFIG['DomainSyncEnabled']) {
	logActivity("Domain Sync Cron: Disabled. Run Aborted.");
	exit();
}

$registrarconfigops = array();
logActivity("Domain Sync Cron: Starting");
$transfersreport = "";
$result = select_query("tbldomains", "id,domain,registrar,registrationperiod,status,dnsmanagement,emailforwarding,idprotection", "registrar!='' AND status='Pending Transfer'", "id", "ASC");
$curlerrorregistrars = array();

while ($data = mysql_fetch_array($result)) {
	$domainid = $data['id'];
	$domain = $data['domain'];
	$registrar = $data['registrar'];
	$regperiod = $data['registrationperiod'];
	$status = $data['status'];
	$domainparts = explode(".", $domain, 2);
	$registrarconfigops[$registrar] = getRegistrarConfigOptions($registrar);
	$params = (is_array($registrarconfigops[$registrar]) ? $registrarconfigops[$registrar] :);
	$params['domainid'] = $domainid;
	$params['domain'] = $domain;
	$params['sld'] = $domainparts[0];
	$params['tld'] = $domainparts[1];
	$params['registrar'] = $registrar;
	$params['regperiod'] = $regperiod;
	$params['status'] = $status;
	$params['dnsmanagement'] = $data['dnsmanagement'];
	$params['emailforwarding'] = $data['emailforwarding'];
	$params['idprotection'] = $data['idprotection'];
	loadRegistrarModule($registrar);

	if (function_exists($registrar . "_TransferSync") && !in_array($registrar, $curlerrorregistrars)) {
		$transfersreport .= " - " . $domain . ": ";
		$updateqry = array();
		$response = call_user_func($registrar . "_TransferSync", $params);

		if (!$response['error']) {
			if ($response['active'] || $response['completed']) {
				$transfersreport .= "Transfer Completed";
				$updateqry['status'] = "Active";

				if ($response['expirydate']) {
					$updateqry['expirydate'] = $response['expirydate'];
					$transfersreport .= " - In Sync";
				}


				if ($CONFIG['DomainSyncNextDueDate'] && $response['expirydate']) {
					$newexpirydate = $response['expirydate'];

					if ($CONFIG['DomainSyncNextDueDateDays']) {
						$newexpirydate = explode("-", $newexpirydate);
						$newexpirydate = date("Y-m-d", mktime(0, 0, 0, $newexpirydate[1], $newexpirydate[2] - $CONFIG['DomainSyncNextDueDateDays'], $newexpirydate[0]));
					}

					$updateqry['nextduedate'] = $updateqry['nextinvoicedate'] = $newexpirydate;
				}

				sendMessage("Domain Transfer Completed", $domainid);
			}
			else {
				if ($response['failed']) {
					$transfersreport .= "Transfer Failed";
					$updateqry['status'] = "Cancelled";
					$failurereason = $response['reason'];

					if (!$failurereason) {
						$failurereason = $_LANG['domaintrffailreasonunavailable'];
					}

					sendMessage("Domain Transfer Failed", $domainid, array("domain_transfer_failure_reason" => $failurereason));
				}
				else {
					$transfersreport .= "Transfer Still In Progress";
				}
			}


			if (!$CONFIG['DomainSyncNotifyOnly'] && count($updateqry)) {
				update_query("tbldomains", $updateqry, array("id" => $domainid));
			}
		}
		else {
			if ($response['error'] && strtolower(substr($response['error'], 0, 4)) == "curl") {
				if (!in_array($registrar, $curlerrorregistrars)) {
					$curlerrorregistrars[] = $registrar;
				}

				$transfersreport .= "Error: " . $response['error'];
			}
			else {
				if ($response['error']) {
					$transfersreport .= "Error: " . $response['error'];
				}
			}
		}

		$transfersreport .= "<br />
";
	}
}


if ($transfersreport) {
	$cronreport .= "Transfer Status Checks<br />
" . $transfersreport . "<br />
";
}

$cronreport .= "Active Domain Syncs<br />
";
$totalunsynced = get_query_val("tbldomains", "COUNT(id)", "registrar!='' AND status='Active' AND synced=0", "id", "ASC", "0,50");

if (!$totalunsynced) {
	update_query("tbldomains", array("synced" => "0"), "");
}

$result = select_query("tbldomains", "id,domain,expirydate,nextduedate,registrar,status", "registrar!='' AND status='Active' AND synced=0", "status` DESC, `id", "ASC", "0,50");

while ($data = mysql_fetch_array($result)) {
	$domainid = $data['id'];
	$domain = $data['domain'];
	$registrar = $data['registrar'];
	$expirydate = $data['expirydate'];
	$nextduedate = $data['nextduedate'];
	$status = $data['status'];
	$domainparts = explode(".", $domain, 2);
	$registrarconfigops[$registrar] = getRegistrarConfigOptions($registrar);
	$params = (is_array($registrarconfigops[$registrar]) ? $registrarconfigops[$registrar] :);
	$params['domainid'] = $domainid;
	$params['domain'] = $domain;
	$params['sld'] = $domainparts[0];
	$params['tld'] = $domainparts[1];
	$params['registrar'] = $registrar;
	$params['status'] = $status;
	loadRegistrarModule($registrar);
	$updateqry = array();
	$updateqry['synced'] = "1";
	$response = $synceditems = array();

	if (function_exists($registrar . "_Sync") && !in_array($registrar, $curlerrorregistrars)) {
		$response = call_user_func($registrar . "_Sync", $params);

		if (!$response['error']) {
			if ($response['active'] && $status != "Active") {
				$updateqry['status'] = "Active";
				$synceditems[] = "Status Changed to Active";
			}


			if ($response['expired'] && $status != "Expired") {
				$updateqry['status'] = "Expired";
				$synceditems[] = "Status Changed to Expired";
			}


			if ($response['expirydate'] && $expirydate != $response['expirydate']) {
				$updateqry['expirydate'] = $response['expirydate'];
				$synceditems[] = "Expiry Date updated to " . fromMySQLDate($response['expirydate']);
			}


			if ($CONFIG['DomainSyncNextDueDate'] && $response['expirydate']) {
				$newexpirydate = $response['expirydate'];

				if ($CONFIG['DomainSyncNextDueDateDays']) {
					$newexpirydate = explode("-", $newexpirydate);
					$newexpirydate = date("Y-m-d", mktime(0, 0, 0, $newexpirydate[1], $newexpirydate[2] - $CONFIG['DomainSyncNextDueDateDays'], $newexpirydate[0]));
				}


				if ($newexpirydate != $nextduedate) {
					$updateqry['nextduedate'] = $updateqry['nextinvoicedate'] = $newexpirydate;
					$synceditems[] = "Next Due Date updated to " . fromMySQLDate($newexpirydate);
				}
			}
		}
	}


	if ($CONFIG['DomainSyncNotifyOnly']) {
		$updateqry = array("synced" => "1");
	}

	update_query("tbldomains", $updateqry, array("id" => $domainid));
	$cronreport .= " - " . $domain . ": ";

	if (!count($response)) {
		$cronreport .= "Sync Not Supported by Registrar Module";
	}
	else {
		if ($response['error'] && strtolower(substr($response['error'], 0, 4)) == "curl") {
			if (!in_array($registrar, $curlerrorregistrars)) {
				$curlerrorregistrars[] = $registrar;
			}

			$cronreport .= "Error: " . $response['error'];
		}
		else {
			if ($response['error']) {
				$cronreport .= "Error: " . $response['error'];
			}
			else {
				if ((!function_exists($registrar . "_TransfersSync") && $status == "Pending Transfer") && $response['active']) {
					sendMessage("Domain Transfer Completed", $domainid);
				}

				$cronreport .= (count($synceditems) ? ($CONFIG['DomainSyncNotifyOnly'] ? "Out of Sync " : "Updated ") . implode(", ", $synceditems) : "In Sync");
			}
		}
	}

	$cronreport .= "<br />
";
}

echo $cronreport;
logActivity("Domain Sync Cron: Completed");
sendAdminNotification("system", "WHMCS Domain Syncronisation Cron Report", $cronreport);
?>