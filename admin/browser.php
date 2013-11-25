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

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Browser");
$aInt->title = $aInt->lang("utilities", "browser");
$aInt->sidebar = "browser";
$aInt->icon = "browser";

if ($action == "delete") {
	delete_query("tblbrowserlinks", array("id" => $id));
	redir();
}


if ($action == "add") {
	insert_query("tblbrowserlinks", array("name" => $sitename, "url" => $siteurl));
	redir();
}

$url = "http://www.mtimer.cn/";
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
$jscode = "function doDelete(id) {
    if (confirm(\"" . $aInt->lang("browser", "deleteq") . "\")) {
        window.location='" . $_SERVER['PHP_SELF'] . "?action=delete&id='+id;
        return false;
    }
}
";
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->display();
?>