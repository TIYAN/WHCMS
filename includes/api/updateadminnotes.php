<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}

$apiresults = array("result" => "success");
update_query("tbladmins", array("notes" => $notes), array("id" => $_SESSION['adminid']));
?>