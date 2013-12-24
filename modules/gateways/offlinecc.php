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

function offlinecc_config() {
	$configarray = array( "FriendlyName" => array( "Type" => "System", "Value" => "Offline Credit Card" ), "RemoteStorage" => true );
	return $configarray;
}


function offlinecc_capture($params) {
	return false;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>