<?php
require_once dirname(__FILE__) . "/../../../dbconnect.php";
require_once dirname(__FILE__) . "/../../../includes/functions.php";
require_once dirname(__FILE__) . "/../../../includes/registrarfunctions.php";

require_once dirname(__FILE__) . "/namecheapapi.php";

$registrar = "namecheap";

$params = getregistrarconfigoptions($registrar);


$testmode = (bool)$params['TestMode'];
$username = $testmode ? $params['SandboxUsername'] : $params['Username'];
$password = $testmode ? $params['SandboxPassword'] : $params['Password'];
$sync_next_due_date = (bool)$params['SyncNextDueDate'];

$idna2 = new Net_IDNA2();


$report = "Namecheap Domain Sync Report\n"
        . "-----------------------------------------------------------------------------------------------------\n\n";

/**
 * Transfers
 */
$report .= "Processing transfers:\n";
$dbresult = select_query(
    "tbldomains",
    "id, LOWER(domain) AS domain",
    array('registrar' => $registrar, 'status' => "Pending Transfer")
);

if (!$dbresult || !mysql_num_rows($dbresult))
{
    $report .= "No domains with status 'Pending Transfer' found\n";
}
else
{
    $transfers = array();
    try {
        $request_params = array(
            'ListType' => "COMPLETED",
            'PageSize' => 100,
            'SortBy'   => "DOMAINNAME"
        );
        if (!empty($params['PromotionCode'])) {
            $request_params['PromotionCode'] = $params['PromotionCode'];
        }
        $api = new NamecheapRegistrarApi($username, $password, $testmode);
        $response = $api->request("namecheap.domains.transfer.getList", $request_params);
        $result = $api->parseResponse($response);
        $transfers = parseResult($result['TransferGetListResult']['Transfer']);
    } catch (Exception $e) {
        $report .= $e->getMessage() . "\n";
    }

    if (!$transfers)
    {
        $report .= "No registrar completed transfers found\n";
    }
    else
    {
        while (($row = mysql_fetch_assoc($dbresult)) !== false)
        {
            $row['domain'] = $idna2->encode($row['domain']);
            $report .= "Domain " . $row['domain'];
            if (isset($transfers[$row['domain']]) && "COMPLETED" == $transfers[$row['domain']]['Status'])
            {
                $t = $transfers[$row['domain']];

                $update = array(
                    'status' => "Active"
                );
                update_query(
                    "tbldomains",
                    $update,
                    array('id' => $row['id'])
                );
                $report .= " transfer status updated\n";
            }
            else
            {
                $report .= " skipped\n";
            }
        }
    }
}

/**
 * Active domains expiration and next due date dates synchronization
 */
if ($sync_next_due_date)
{
    $report .= "\nProcessing domains expirydate and nextduedate:\n";

    $dbresult = select_query(
        "tbldomains",
        "id, LOWER(domain) AS domain, expirydate",
        array('registrar' => $registrar,  'status' => "Active")
    );
    if (!$dbresult || !mysql_num_rows($dbresult))
    {
        $report .= "No domains with status 'Active' found\n";
    }
    else
    {
        $domains = array();
        try {
            $page = 1;
            $pageSize = 100;
            do
            {
                $request_params = array(
                    'ListType' => "ALL",
                    'Page'     => $page,
                    'PageSize' => $pageSize,
                    'SortBy'   => "NAME"
                );
                if (!empty($params['PromotionCode'])) {
                    $request_params['PromotionCode'] = $params['PromotionCode'];
                }
                $api = new NamecheapRegistrarApi($username, $password, $testmode);
                $response = $api->request("namecheap.domains.getList", $request_params);
                $result = $api->parseResponse($response);
                $domains += parseResult($result['DomainGetListResult']['Domain'], "Name");

                $totalItems = $result['Paging']['TotalItems'];
                $pageSize = $result['Paging']['PageSize'];
            } while (($pageSize * $page++) <= $totalItems);
        } catch (Exception $e) {
            $report .= $e->getMessage() . "\n";
        }
        
        
        if (!$domains)
        {
            $report .= "No registrar active domains found\n";
        }
        else
        {
            $count = 0;
            while (($row = mysql_fetch_assoc($dbresult)) !== false)
            {
                $row['domain'] = $idna2->encode($row['domain']);
                if ((isset($domains[$row['domain']]) && "false" == $domains[$row['domain']]['IsExpired']))
                {
                    
                    $expirydate = date("Y-m-d", strtotime($domains[$row['domain']]['Expires']));
                    if (!$expirydate)
                    {
                        $report .= "Getting expirydate for domain " . $row['domain'] . " failed\n";
                    }
                    else
                    {
                        if ($expirydate != $row['expirydate'])
                        {
                            $update = array(
                                'expirydate'  => $expirydate,
                                'nextduedate' => $expirydate
                            );
                            update_query(
                                "tbldomains",
                                $update,
                                array('id' => $row['id'])
                            );
                            $report .= "Domain " . $row['domain'] . " expirydate and nextduedate updated\n";
                        }
                    }
                    $count++;
                }
            }
            if (0 == $count)
            {
                $report .= "There were no domains to process\n";
            }
        }
    }
}
$report .= "\n-----------------------------------------------------------------------------------------------------\n"
    . "End of report\n";

logactivity("Namecheap Domain Sync Run");
sendadminnotification("system", "WHMCS Namecheap Domain Synchronization Report", nl2br($report));

function parseResult($transfers, $domainNameKey = "DomainName")
{
    $result = array();
    foreach ($transfers as $t)
    {
        $attr = $t['@attributes'];
        $result[strtolower($attr[$domainNameKey])] = $attr;
    }
    return $result;
}