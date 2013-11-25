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

define("CLIENTAREA", true);
require "init.php";

if (isset($_SESSION['uid'])) {
	require "includes/smarty/Smarty.class.php";
	$smarty = new Smarty();
	$smarty->template_dir = "templates/" . $whmcs->get_sys_tpl_name() . "/";
	$smarty->compile_dir = $templates_compiledir;
	$smarty->assign("template", $whmcs->get_sys_tpl_name());
	$smarty->assign("LANG", $_LANG);
	$smarty->assign("logo", $CONFIG['LogoURL']);
	$smarty->assign("companyname", $CONFIG['CompanyName']);
	$id = $whmcs->get_req_var("id");
	$result = select_query("tblemails", "", array("id" => $id, "userid" => $_SESSION['uid']));
	$data = mysql_fetch_array($result);
	$date = $data['date'];
	$subject = $data['subject'];
	$message = $data['message'];
	$date = fromMySQLDate($date, "time");
	$smarty->assign("date", $date);
	$smarty->assign("subject", $subject);
	$smarty->assign("message", $message);
	$template_output = $smarty->fetch("viewemail.tpl");
	echo $template_output;
	return 1;
}

redir("", "index.php");
?>