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

require "../../../init.php";
require "vpsnet.php";

if (!$_SESSION['uid']) {
	exit( "Access Denied" );
}

$result = select_query( "tblhosting", "count(*)", array( "id" => $serviceid, "userid" => $_SESSION['uid'] ) );
$data = mysql_fetch_array( $result );

if (!$data[0]) {
	exit( "Access Denied" );
}

$creds = vpsnet_GetCredentials();
$api = VPSNET::getinstance( $creds['username'], $creds['accesshash'] );
$result = select_query( "mod_vpsnet", "", array( "relid" => $serviceid ) );

while ($data = mysql_fetch_array( $result )) {
	${$data['setting']} = $data['value'];
}


if (!in_array( $period, array( "hourly", "daily", "weekly", "monthly" ) )) {
	$period = "hourly";
}

$postfields = new VirtualMachine();
$postfields->id = $netid;

if ($graph == "cpu") {
	$result = $postfields->showCPUGraph( $period );
}
else {
	$result = $postfields->showNetworkGraph( $period );
}

$output = $result['response_body'];
echo $output;
Exception {
	return "Caught exception: " . $e->getMessage();
	return 1;
}
?>