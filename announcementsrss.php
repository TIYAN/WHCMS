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
 **/

define("CLIENTAREA", true);
require "init.php";

if ($whmcs->get_req_var("lcinfo")) {
	echo "<textarea cols=100 rows=4>License Key: " . $whmcs->get_license_key() . "
System URL: " . $CONFIG['SystemURL'] . "
System SSL URL: " . $CONFIG['SystemSSLURL'] . "</textarea>";
	exit();
}

$language = ((isset($_REQUEST['language']) && in_array($_REQUEST['language'], $whmcs->getValidLanguages())) ? $_REQUEST['language'] : "");
header("Content-Type: application/rss+xml");
echo "<?xml version=\"1.0\" encoding=\"" . $CONFIG['Charset'] . "\"?><rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
<channel>
<atom:link href=\"" . $CONFIG['SystemURL'] . "/announcementsrss.php\" rel=\"self\" type=\"application/rss+xml\" />
<title><![CDATA[" . $CONFIG['CompanyName'] . "]]></title>
<description><![CDATA[" . $CONFIG['CompanyName'] . " " . $_LANG['announcementstitle'] . " " . $_LANG['rssfeed'] . "]]></description>
<link>" . $CONFIG['SystemURL'] . "/announcements.php</link>";
$result = select_query("tblannouncements", "*", array("published" => "on"), "date", "DESC");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$date = $data['date'];
	$title = $data['title'];
	$announcement = $data['announcement'];
	$result2 = select_query("tblannouncements", "", array("parentid" => $id, "language" => $language));
	$data = mysql_fetch_array($result2);

	if ($data['title']) {
		$title = $data['title'];
	}


	if ($data['announcement']) {
		$announcement = $data['announcement'];
	}

	$year = substr($date, 0, 4);
	$month = substr($date, 5, 2);
	$day = substr($date, 8, 2);
	$hours = substr($date, 11, 2);
	$minutes = substr($date, 14, 2);
	$seconds = substr($date, 17, 2);
	echo "
<item>
	<title><![CDATA[" . $title . "]]></title>
	<link>" . $CONFIG['SystemURL'] . "/announcements.php?id=" . $id . "</link>
    <guid>" . $CONFIG['SystemURL'] . "/announcements.php?id=" . $id . "</guid>
	<pubDate>" . date("r", mktime($hours, $minutes, $seconds, $month, $day, $year)) . "</pubDate>
	<description><![CDATA[" . $announcement . "]]></description>
</item>";
}

echo "
</channel>
</rss>";
?>