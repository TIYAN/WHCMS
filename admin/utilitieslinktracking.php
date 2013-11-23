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
$aInt = new WHMCS_Admin("Link Tracking");
$aInt->title = "Link Tracking";
$aInt->sidebar = "utilities";
$aInt->icon = "linktracking";
$aInt->helplink = "Link Tracking";

if ($action == "save") {
	check_token("WHMCS.admin.default");

	if ($id) {
		$table = "tbllinks";
		$array = array("name" => $name, "link" => html_entity_decode($url), "clicks" => $clicks, "conversions" => $conversions);
		$where = array("id" => $id);
		update_query($table, $array, $where);
	}
	else {
		$table = "tbllinks";
		$array = array("name" => $name, "link" => html_entity_decode($url), "clicks" => $clicks, "conversions" => $conversions);
		insert_query($table, $array);
	}

	header("Location: " . $_SERVER['PHP_SELF']);
	exit();
}


if ($sub == "delete") {
	check_token("WHMCS.admin.default");
	delete_query("tbllinks", array("id" => $id));
	header("Location: " . $_SERVER['PHP_SELF']);
	exit();
}

ob_start();

if (!$action) {
	$jscode = "function doDelete(id) {
	if (confirm(\"Are you sure you want to delete this link?\")) {
		window.location='" . $_SERVER['PHP_SELF'] . "?sub=delete&id='+id+'" . generate_token("link") . "';
	}
}";
	echo "
<p>The Link Tracking system allows you to track how people are arriving at your site (what links they are clicking on) and then how many conversions you get from people who have clicked on that link.</p>

<p><b>Options:</b> <a href=\"";
	echo $PHP_SELF;
	echo "?action=manage\">Add a New Link</a></p>

";
	$aInt->sortableTableInit("id", "ASC");
	$result = select_query("tbllinks", "COUNT(*)", "", $orderby, $order);
	$data = mysql_fetch_array($result);
	$numrows = $data[0];
	$result = select_query("tbllinks", "", "", $orderby, $order, $page * $limit . ("," . $limit));

	while ($data = mysql_fetch_array($result)) {
		$id = $data['id'];
		$name = $data['name'];
		$link = $data['link'];
		$clicks = $data['clicks'];
		$conversions = $data['conversions'];
		$displaylink = $link;

		if (40 < strlen($displaylink)) {
			$displaylink = substr($link, 0, 40) . "...";
		}

		$conversionrate = @round($conversions / $clicks * 100, 2);
		$tabledata[] = array($id, $name, "<a href=\"" . $link . "\" target=\"_blank\">" . $displaylink . "</a>", $clicks, $conversions, $conversionrate . "%", "<a href=\"" . $PHP_SELF . "?action=manage&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Edit\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Delete\"></a>");
	}

	echo $aInt->sortableTable(array(array("id", "ID"), array("name", "Name"), array("link", "Link"), array("clicks", "Clicks"), array("conversions", "Conversions"), array("conversionrate", "Conversion Rate"), "", ""), $tabledata);
}
else {
	if ($action == "manage") {
		if ($id) {
			$table = "tbllinks";
			$fields = "";
			$where = array("id" => $id);
			$result = select_query($table, $fields, $where);
			$data = mysql_fetch_array($result);
			$id = $data['id'];
			$name = $data['name'];
			$url = $data['link'];
			$clicks = $data['clicks'];
			$conversions = $data['conversions'];
			$actiontitle = "Edit Link";
		}
		else {
			$clicks = 0;
			$conversions = 0;
			$actiontitle = "Add Link";
		}

		echo "
<p>";
		echo "<s";
		echo "trong>";
		echo $actiontitle;
		echo "</strong></p>
<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?action=save";

		if ($id) {
			echo "&id=" . $id;
		}

		echo "\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">Name</td><td class=\"fieldarea\"><input type=\"text\" size=\"40\" name=\"name\" value=\"";
		echo $name;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Forward To</td><td class=\"fieldarea\"><input type=\"text\" name=\"url\" size=100 value=\"";
		echo $url;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Clicks</td><td class=\"fieldarea\"><input type=\"text\" name=\"clicks\" size=10 value=\"";
		echo $clicks;
		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">Conversions</td><td class=\"fieldarea\"><input type=\"text\" name=\"conversions\" size=10 value=\"";
		echo $conversions;
		echo "\"></td></tr>
";

		if ($id) {
			echo "<tr><td class=\"fieldlabel\">Link/URL</td><td class=\"fieldarea\"><input type=\"text\" name=\"linkurl\" size=100 value=\"";
			echo $CONFIG['SystemURL'];
			echo "/link.php?id=";
			echo $id;
			echo "\"></td></tr>";
		}

		echo "</table>
<p align=\"center\"><input type=\"submit\" value=\"Save Changes\" class=\"button\"></p>
</form>

";
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->display();
?>