<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function widget_activity_log($vars) {
    global $_ADMINLANG;

    $title = $_ADMINLANG['utilities']['activitylog'];

    $content = '';

    $patterns = $replacements = array();
    $patterns[] = '/User ID: (.*?) /';
    $patterns[] = '/Service ID: (.*?) /';
    $patterns[] = '/Domain ID: (.*?) /';
    $patterns[] = '/Invoice ID: (.*?) /';
    $patterns[] = '/Order ID: (.*?) /';
    $patterns[] = '/Transaction ID: (.*?) /';
    $replacements[] = '<a href="clientssummary.php?userid=$1">User ID: $1</a> ';
    $replacements[] = '<a href="clientshosting.php?id=$1">Service ID: $1</a> ';
    $replacements[] = '<a href="clientsdomains.php?id=$1">Domain ID: $1</a> ';
    $replacements[] = '<a href="invoices.php?action=edit&id=$1">Invoice ID: $1</a> ';
    $replacements[] = '<a href="orders.php?action=view&id=$1">Order ID: $1</a> ';
    $replacements[] = '<a href="transactions.php?action=edit&id=$1">Transaction ID: $1</a> ';

    $result = select_query("tblactivitylog","","","id","DESC","0,10");
    while ($data = mysql_fetch_array($result)) {
        $description = $data["description"].' ';
        $description = htmlentities($description, ENT_QUOTES, "UTF-8");
        $description = preg_replace($patterns, $replacements, $description);
        $content .= $description.'<br /><span style="font-size:11px;">&nbsp; - '.fromMySQLDate($data["date"],true).' - '.$data['user'].' - '.$data['ipaddr'].'</span><br />';
    }

    if (!$content) $content = '<div align="center">No Activity Recorded Yet</div>';
    else $content .= '<div align="right"><a href="systemactivitylog.php">'.$_ADMINLANG['home']['viewall'].' &raquo;</a></div>';

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_activity_log");

?>