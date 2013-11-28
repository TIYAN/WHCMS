<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.13
 * @ Author   : MTIMER
 * @ Release on : 2013-11-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$licensing->forceRemoteCheck();
$aInt = new WHMCS_Admin("Configure General Settings");
$aInt->title = $aInt->lang("system", "checkforupdates");
$aInt->sidebar = "help";
$aInt->icon = "support";
ob_start();

if (!$licensing->getLatestVersion()) {
	infoBox($aInt->lang("system", "updatecheck"), $aInt->lang("system", "connectfailed"), "error");
	echo $infobox;
}
else {
	if ($CONFIG['Version'] != $licensing->getLatestVersion()) {
		infoBox($aInt->lang("system", "updatecheck"), $aInt->lang("system", "upgrade") . " <a href=\"https://www.whmcs.com/members/clientarea.php\" target=\"_blank\">" . $aInt->lang("system", "clickhere") . "</a>");
	}
	else {
		infoBox($aInt->lang("system", "updatecheck"), $aInt->lang("system", "runninglatestversion"), "success");
	}

	echo "<div class=\"versionnoticecont\">" . $infobox . "</div>";
	echo "
<br />

";
	echo "<s";
	echo "tyle>
.versioncont {
    margin:0 auto;
    padding:0 0 25px 0;
    width:480px;
}
.versionyour {
    float:left;
    margin:0;
    padding:10px 20px;
    width:200px;
    background-color:#535353;
    border-bottom:1px solid #fff;
    color: #fff;
    font-size:20px;
    text-align:right;
    -moz-border-radius: 10px 0 0 0;
    -webkit-border-radius: 10px 0 0 0;
    -o-border-ra";
	echo "dius: 10px 0 0 0;
    border-radius: 10px 0 0 0;
}
.versionyournum {
    float:left;
    margin:0;
    padding:5px 20px;
    width:200px;
    background-color:#666;
    color: #fff;
    font-family:Arial;
    font-size:70px;
    text-align:right;
    -moz-border-radius: 0 0 0 10px;
    -webkit-border-radius: 0 0 0 10px;
    -o-border-radius: 0 0 0 10px;
    border-radius: 0 0 0 10p";
	echo "x;
}
.versionlatest {
    float:left;
    margin:0;
    padding:10px 20px;
    width:200px;
    background-color:#035485;
    border-bottom:1px solid #fff;
    color: #fff;
    font-size:20px;
    text-align:left;
    -moz-border-radius: 0 10px 0 0;
    -webkit-border-radius: 0 10px 0 0;
    -o-border-radius: 0 10px 0 0;
    border-radius: 0 10px 0 0;
}
.versionlatestnum {
    fl";
	echo "oat:left;
    margin:0;
    padding:5px 20px;
    width:200px;
    background-color:#0467A2;
    color: #fff;
    font-family:Arial;
    font-size:70px;
    text-align:left;
    -moz-border-radius: 0 0 10px 0;
    -webkit-border-radius: 0 0 10px 0;
    -o-border-radius: 0 0 10px 0;
    border-radius: 0 0 10px 0;
}
.versionnoticecont {
    width:700px;
    margin:30px auto 10px;
}
";
	echo "
.newspost {
    margin:10px auto;
    padding:6px 15px;
    width:80%;
    background-color:#f8f8f8;
    border:1px solid #ccc;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    -o-border-radius: 10px;
    border-radius: 10px;
}
</style>

<div class=\"versioncont\">
<div class=\"versionyour\">";
	echo $aInt->lang("system", "yourversion");
	echo "</div>
<div class=\"versionlatest\">";
	echo $aInt->lang("system", "latestversion");
	echo "</div>
<div class=\"versionyournum\">";
	echo $whmcs->get_config("Version");
	echo "</div>
<div class=\"versionlatestnum\">";
	echo $licensing->getLatestVersion();
	echo "</div>
<div style=\"clear:both;\"></div>
</div>

";
}

$feed = curlCall("http://www.whmcs.com/feeds/news.php", "");
$feed = json_decode($feed, 1);
$count = 10;
foreach ($feed as $news) {
	echo "<div class=\"newspost\"><h2>" . ($news['link'] ? "<a href=\"" . $news['link'] . "\" target=\"_blank\">" : "") . $news['headline'] . ($news['link'] ? "</a>" : "") . "</h2>
<p>" . $news['text'] . "</p>
<p style=\"font-size:10px;\">" . date("l, F jS, Y", strtotime($news['date'])) . "</p>
</div>
";
	++$count;
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>