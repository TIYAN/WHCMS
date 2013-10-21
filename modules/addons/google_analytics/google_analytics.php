<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.10
 * @ Author   : MTIMER
 * @ Release on : 2013-10-20
 * @ Website  : http://www.mtimer.cn
 *
 * */

function google_analytics_config() {
	$configarray = array( "name" => "Google Analytics", "description" => "This module provides a quick and easy way to integrate full Google Analytics tracking into your WHMCS installation", "version" => "1.0", "author" => "WHMCS", "fields" => array( "code" => array( "FriendlyName" => "Tracking Code", "Type" => "text", "Size" => "25", "Description" => "Format: UA-XXXXXXXX-X" ), "domain" => array( "FriendlyName" => "Tracking Domain", "Type" => "text", "Size" => "25", "Description" => "(Optional) Format: yourdomain.com" ) ) );
	return $configarray;
}


function google_analytics_output($vars) {
	echo "<br /><br />
<p align=\"center\"><input type=\"button\" value=\"Launch Google Analytics Website\" onclick=\"window.open('http://www.google.com.hk/analytics/','ganalytics');\" style=\"padding:20px 50px;font-size:20px;\" /></p>
<br /><br />
<p>Configuration of the Google Analytics Addon is done via <a href=\"configaddonmods.php\"><b>Setup > Addon Modules</b></a>. Please also ensure your active client area footer.tpl template file includes the {$footeroutput} template tag.</p>";
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

?>