<?php
/*
*************************************************************************
*                                                                       *
* WHMCS - The Complete Client Management, Billing & Support Solution    *
* Copyright (c) WHMCS Ltd. All Rights Reserved,                         *
* Release Date: 24th November 2011                                      *
* Version 5.0                                                           *
*                                                                       *
*************************************************************************
*                                                                       *
* Email: info@whmcs.com                                                 *
* Website: htttp://www.whmcs.com                                        *
*                                                                       *
*************************************************************************

This file can be uploaded to each of your linux web servers in order to
display current load and uptime statistics for the server in the Server
Status page of the WHMCS Client Area and Admin Area Homepage

*/

error_reporting(0);

$action = (isset($_GET['action'])) ? $_GET['action'] : '';

if ($action=="phpinfo") {

    /*
    Uncoment the line below to allow users to view PHP Info for your
    server. This potentially allows access to information a malicious
    user could use to find weaknesses in your server.
    */
    #phpinfo();

} else {

	$load = file_get_contents("/proc/loadavg");
	$load = explode(' ',$load);
	$load = $load[0];
    if (!$load && function_exists('exec')) {
		$reguptime=trim(exec("uptime"));
		if ($reguptime) if (preg_match("/, *(\d) (users?), .*: (.*), (.*), (.*)/",$reguptime,$uptime)) $load = $uptime[3];
	}

	$uptime_text = file_get_contents("/proc/uptime");
	$uptime = substr($uptime_text,0,strpos($uptime_text," "));
	if (!$uptime && function_exists('shell_exec')) $uptime = shell_exec("cut -d. -f1 /proc/uptime");
	$days = floor($uptime/60/60/24);
	$hours = str_pad($uptime/60/60%24,2,"0",STR_PAD_LEFT);
	$mins = str_pad($uptime/60%60,2,"0",STR_PAD_LEFT);
	$secs = str_pad($uptime%60,2,"0",STR_PAD_LEFT);

	$phpver = phpversion();
	$mysqlver = (function_exists("mysql_get_client_info")) ? mysql_get_client_info() : '-';
	$zendver = (function_exists("zend_version")) ? zend_version() : '-';

	echo "<load>$load</load>\n";
	echo "<uptime>$days Days $hours:$mins:$secs</uptime>\n";
	echo "<phpver>$phpver</phpver>\n";
	echo "<mysqlver>$mysqlver</mysqlver>\n";
	echo "<zendver>$zendver</zendver>\n";

}

?>