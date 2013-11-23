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
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View Email Message Log");
$aInt->title = $aInt->lang("system", "emailmessagelog");
$aInt->sidebar = "utilities";
$aInt->icon = "logs";
$aInt->sortableTableInit("date");
$result = select_query("tblemails,tblclients", "COUNT(tblemails.id)", "tblemails.userid=tblclients.id");
$data = mysql_fetch_array($result);
$numrows = $data[0];
$result = select_query("tblemails,tblclients", "tblemails.id,tblemails.date,tblemails.subject,tblemails.userid,tblclients.firstname,tblclients.lastname", "tblemails.userid=tblclients.id", "tblemails`.`id", "DESC", $page * $limit . ("," . $limit));

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$date = $data['date'];
	$subject = $data['subject'];
	$userid = $data['userid'];
	$firstname = $data['firstname'];
	$lastname = $data['lastname'];
	$tabledata[] = array(fromMySQLDate($date, "time"), "<a href=\"#\" onClick=\"window.open('clientsemails.php?&displaymessage=true&id=" . $id . "','','width=650,height=400,scrollbars=yes');return false\">" . $subject . "</a>", "<a href=\"clientssummary.php?userid=" . $userid . "\">" . $firstname . " " . $lastname . "</a>", "<a href=\"sendmessage.php?resend=true&emailid=" . $id . "\"><img src=\"images/icons/resendemail.png\" border=\"0\" alt=\"" . $aInt->lang("emails", "resendemail") . "\"></a>");
}

$content = $aInt->sortableTable(array($aInt->lang("fields", "date"), $aInt->lang("fields", "subject"), $aInt->lang("system", "recepient"), ""), $tabledata);
$aInt->content = $content;
$aInt->display();
?>