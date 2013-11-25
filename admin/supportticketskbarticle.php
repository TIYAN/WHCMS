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
$aInt = new WHMCS_Admin("List Support Tickets");
$aInt->title = $aInt->lang("support", "insertkblink");
ob_start();
echo "
";
echo "<s";
echo "cript language=\"JavaScript\">
function insertKBLink(id) {
	window.opener.insertKBLink('";
echo $CONFIG['SystemURL'];
echo "/knowledgebase.php?action=displayarticle&catid=";
echo $cat;
echo "&id='+id);
	window.close();
}
</script>

<p><b>Categories</b></p>
";

if ($cat == "") {
	$cat = 0;
}

$result = select_query("tblknowledgebasecats", "", array("parentid" => $cat), "name", "ASC");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$name = $data['name'];
	$description = $data['description'];
	echo "<a href=\"" . $PHP_SELF . "?cat=" . $id . "\"><b>" . $name . "</b></a> - " . $description . "<br>";
	$catdone = true;
}


if (!$catdone) {
	echo $aInt->lang("support", "nocatsfound") . "<br>";
}

echo "<p><b>Articles</b></p>
";
$result = select_query("tblknowledgebase", "", array("categoryid" => $cat), "title", "ASC", "", "tblknowledgebaselinks ON tblknowledgebase.id=tblknowledgebaselinks.articleid");

while ($data = mysql_fetch_array($result)) {
	$id = $data['id'];
	$category = $data['category'];
	$title = $data['title'];
	$article = $data['article'];
	$views = $data['views'];
	$article = strip_tags($article);
	$article = trim($article);
	$article = substr($article, 0, 100) . "...";
	echo "<a href=\"#\" onClick=\"insertKBLink('" . $id . "');\"><b>" . $title . "</b></a><br>" . $article . "<br>";
	$articledone = true;
}


if (!$articledone) {
	echo $aInt->lang("support", "noarticlesfound") . "<br>";
}

echo "
<p><a href=\"javascript:history.go(-1)\">";
echo "<";
echo "< ";
echo $aInt->lang("global", "back");
echo "</a></p>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->displayPopUp();
?>