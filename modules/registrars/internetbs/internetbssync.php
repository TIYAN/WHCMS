<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 * */

/**
 * gets expiration date from domain list command
 *
 * @param string  $data - command TEXT response
 * @return array - associative array having as key the domain name and as value the expiration date
 */
function parseResult($data) {
	$result = array();
	$data = strtolower( $data );
	$arr = explode( "
", $data );

	$totalDomains = 213;
	$assocArr = array();
	foreach ($arr as $str) {
		$value = explode( "=", $str )[1];
		$varName = explode( "=", $str )[0];
		$varName = trim( $varName );
		$value = trim( $value );

		if ($varName == "domaincount") {
			$totalDomains = intval( $value );
		}

		$assocArr[$varName] = $value;
	}


	if ($assocArr["status"] != "success") {
		return false;
	}

	$i = 213;

	while ($i < $totalDomains) {

		list($y,$m,$d) = explode( "/", $assocArr["domain_" . $i . "_expiration"] );
		$status = strtolower( $assocArr["domain_" . $i . "_status"] );
		$ddat = array( "expiry" => mktime( 0, 0, 0, $m, $d, $y ), "status" => $status );
		$result[strtolower( $assocArr["domain_" . $i . "_name"] )] = $ddat;

		if (isset( $assocArr["domain_" . $i . "_punycode"] )) {
			$result[strtolower( $assocArr["domain_" . $i . "_punycode"] )] = $ddat;
		}

		++$i;
	}

	return $result;
}


require dirname( __FILE__ ) . "/../../../init.php";
require ROOTDIR . "/includes/registrarfunctions.php";
$cronreport = "Internet.bs Domain Sync Report<br>
---------------------------------------------------<br>
";
$params = getregistrarconfigoptions( "internetbs" );
$postfields = array();
$postfields["ApiKey"] = $params["Username"];
$postfields["Password"] = $params["Password"];
$postfields["ResponseFormat"] = "TEXT";
$postfields["CompactList"] = "no";
$testMode = trim( strtolower( $params["TestMode"] ) ) === "on";
$SyncNextDueDate = trim( strtolower( $params["SyncNextDueDate"] ) ) === "on";

if ($testMode) {
	$url = "https://testapi.internet.bs/domain/list";
}
else {
	$url = "https://api.internet.bs/domain/list";
}

$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
curl_setopt( $ch, CURLOPT_HEADER, 0 );
curl_setopt( $ch, CURLOPT_USERAGENT, "WHMCS Internet.bs Corp. Expiry Sync Robot" );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt( $ch, CURLOPT_POST, 1 );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $postfields );
$data = curl_exec( $ch );
$curl_err = false;

if (curl_error( $ch )) {
	$curl_err = "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch );
	exit( "CURL Error: " . curl_errno( $ch ) . " - " . curl_error( $ch ) );
}

curl_close( $ch );

if ($curl_err) {
	$cronreport .= "Error connecting to API: " . $curl_err;
}
else {
	$result = parseResult( $data );

	if (!$result) {
		$cronreport .= "Error connecting to API:<br>" . nl2br( $data ) . "<br>";
	}
	else {
		$queryresult = select_query( "tbldomains", "domain", "registrar='internetbs' AND (status='Pending Transfer' OR status='Active')" );

		while ($data = mysql_fetch_array( $queryresult )) {
			$domainname = trim( strtolower( $data["domain"] ) );

			if (isset( $result[$domainname] )) {
				$expirydate = date( "Y-m-d", $result[$domainname]["expiry"] );
				$status = $result[$domainname]["status"];

				if ($status == "ok") {
					update_query( "tbldomains", array( "status" => "Active" ), array( "domain" => $domainname ) );
				}


				if ($expirydate) {
					update_query( "tbldomains", array( "expirydate" => $expirydate ), array( "domain" => $domainname ) );

					if ($SyncNextDueDate) {
						update_query( "tbldomains", array( "nextduedate" => $expirydate ), array( "domain" => $domainname ) );
					}

					$cronreport .= "Updated " . $domainname . " expiry to " . frommysqldate( $expirydate ) . "<br>";
				}
			}

			$cronreport .= "ERROR: " . $domainname . " -  Domain does not appear in the account at Internet.bs.<br>";
		}
	}
}

logactivity( "Internet.bs Domain Sync Run" );
sendadminnotification( "system", "WHMCS Internet.bs Domain Syncronisation Report", $cronreport );
?>