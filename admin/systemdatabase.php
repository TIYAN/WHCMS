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

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Database Status");
$aInt->title = $aInt->lang("utilities", "dbstatus");
$aInt->sidebar = "utilities";
$aInt->icon = "dbbackups";
$aInt->requiredFiles(array("backupfunctions"));

if ($optimize) {
	$alltables = full_query("SHOW TABLES");

	while ($table = mysql_fetch_assoc($alltables)) {
		foreach ($table as $db => $tablename) {
			full_query("OPTIMIZE TABLE '" . $tablename . "'");
		}
	}

	infoBox($aInt->lang("system", "optcomplete"), $aInt->lang("system", "optcompleteinfo"));
}


if ($dlbackup) {
	$db_name = "";
	require ROOTDIR . "/configuration.php";
	set_time_limit(0);
	header("Content-type: application/octet-stream");
	header("Content-disposition: attachment; filename=" . $db_name . "_backup_" . date("Ymd_His") . ".zip");
	echo generateBackup();
}

ob_start();
echo $infobox;
echo "
<table width=760 align=center cellspacing=0 cellpadding=0><tr><td width=380 valign=top>

<table bgcolor=#cccccc cellspacing=1 width=370 align=center>
<tr style=\"text-align:center;font-weight:bold;background-color:#efefef\"><td>";
echo $aInt->lang("fields", "name");
echo "</td><td>";
echo $aInt->lang("fields", "rows");
echo "</td><td>";
echo $aInt->lang("fields", "size");
echo "</td></tr>
";
$query = "SHOW TABLE STATUS";
$result = full_query($query);
$i = 0;

while ($data = mysql_fetch_array($result)) {
	$name = $data['Name'];
	$rows = $data['Rows'];
	$datalen = $data['Data_length'];
	$indexlen = $data['Index_length'];
	$totalsize = $datalen + $indexlen;
	$totalrows += $rows;
	$size += $totalsize;
	$reportarray[] = array("name" => $name, "rows" => $rows, "size" => round($totalsize / 1024, 2));
	++$i;
}

foreach ($reportarray as $key => $value) {

	if ($key < $i / 2) {
		echo "<tr bgcolor=#ffffff style=\"text-align:center\"><td>" . $value['name'] . "</td><td>" . $value['rows'] . "</td><td>" . $value['size'] . " " . $aInt->lang("fields", "kb") . "</td></tr>";
		continue;
	}
}

echo "</table>

</td><td align=\"center\" width=370 valign=top>

<table bgcolor=#cccccc cellspacing=1 width=370>
<tr style=\"text-align:center;font-weight:bold;background-color:#efefef\"><td>";
echo $aInt->lang("fields", "name");
echo "</td><td>";
echo $aInt->lang("fields", "rows");
echo "</td><td>";
echo $aInt->lang("fields", "size");
echo "</td></tr>
";
foreach ($reportarray as $key => $value) {

	if ($i / 2 <= $key) {
		echo "<tr bgcolor=#ffffff style=\"text-align:center\"><td>" . $value['name'] . "</td><td>" . $value['rows'] . "</td><td>" . $value['size'] . " " . $aInt->lang("fields", "kb") . "</td></tr>";
		continue;
	}
}

echo "</table>

</td></tr></table>

<p align=center><b>";
echo $aInt->lang("system", "totaltables");
echo ":</b> ";
echo $i;
echo " - <b>";
echo $aInt->lang("system", "totalrows");
echo ":</b> ";
echo $totalrows;
echo " - <B>";
echo $aInt->lang("system", "totalsize");
echo ":</B> ";
echo round($size / 1024, 2);
echo " ";
echo $aInt->lang("fields", "kb");
echo "</p>

<p align=center><input type=\"button\" value=\"";
echo $aInt->lang("system", "opttables");
echo "\" class=\"button\" onClick=\"window.location='systemdatabase.php?optimize=true'\"> <input type=\"button\" value=\"";
echo $aInt->lang("system", "dldbbackup");
echo "\" class=\"button\" onClick=\"window.location='systemdatabase.php?dlbackup=true'\"></p>

</td></tr></table>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>