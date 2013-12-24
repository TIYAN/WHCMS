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

define("CLIENTAREA", true);
require "init.php";
header("Content-Type: application/rss+xml; charset=" . $CONFIG['Charset']);
echo "<?xml version=\"1.0\" encoding=\"" . $CONFIG['Charset'] . "\"?>\n<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n<atom:link href=\"" . $CONFIG['SystemURL'] . "/networkissuesrss.php\" rel=\"self\" type=\"application/rss+xml\" />\n<title><![CDATA[" . $CONFIG['CompanyName'] . "]]></title>\n<description><![CDATA[" . $CONFIG['CompanyName'] . " " . $_LANG['networkissuestitle'] . " " . $_LANG['rssfeed'] . "]]></description>\n<link>" . $CONFIG['SystemURL'] . "/networkissues.php</link>";
$result = select_query("tblnetworkissues", "*", "status != 'Resolved'", "startdate", "DESC");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$date = $data['startdate'];
	$title = $data['title'];
	$description = $data['description'];
	$year = substr($date, 0, 4);
	$month = substr($date, 5, 2);
	$day = substr($date, 8, 2);
	$hours = substr($date, 11, 2);
	$minutes = substr($date, 14, 2);
	$seconds = substr($date, 17, 2);
	echo "
<item>
	<title>" . $title . "</title>
	<link>" . $CONFIG['SystemURL'] . "/networkissues.php?view=nid" . $id . "</link>
	<pubDate>" . date("r", mktime($hours, $minutes, $seconds, $month, $day, $year)) . "</pubDate>
	<description><![CDATA[" . $description . "]]></description>
</item>";
}

echo "
</channel>
</rss>";
?>