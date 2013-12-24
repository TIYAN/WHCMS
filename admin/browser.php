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
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Browser");
$aInt->title = $aInt->lang("utilities", "browser");
$aInt->sidebar = "browser";
$aInt->icon = "browser";

if ($action == "delete") {
	check_token("WHMCS.admin.default");
	delete_query("tblbrowserlinks", array("id" => $id));
	redir();
}


if ($action == "add") {
	check_token("WHMCS.admin.default");
	insert_query("tblbrowserlinks", array("name" => $sitename, "url" => $siteurl));
	redir();
}

$url = "http://www.whmcs.com/";
$link = $whmcs->get_req_var("link");
$result = select_query("tblbrowserlinks", "", "", "name", "ASC");

while ($data = mysql_fetch_array($result)) {
	$browserlinks[] = $data;

	if ($data['id'] == $link) {
		$url = $data['url'];
	}
}

$aInt->assign("browserlinks", $browserlinks);
$content = "<iframe width=\"100%\" height=\"580\" src=\"" . $url . "\" name=\"brwsrwnd\" style=\"min-width:1000px;\"></iframe>";
$aInt->deleteJSConfirm("doDelete", "browser", "deleteq", "?action=delete&id=");
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->display();
?>