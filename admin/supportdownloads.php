<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 **/

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Manage Downloads");
$aInt->title = $aInt->lang("support", "downloads");
$aInt->sidebar = "support";
$aInt->icon = "downloads";
$catid = (int)$catid;

if ($adddownload == "true") {
	check_token("WHMCS.admin.default");

	if ($filetype == "upload") {
		if (!isFileNameSafe($_FILES['uploadfile']['name'])) {
			$aInt->gracefulExit("Invalid upload filename.  Valid filenames contain only alpha-numeric, dot, hyphen and underscore characters.");
			exit();
		}

		move_uploaded_file($_FILES['uploadfile']['tmp_name'], $downloads_dir . $_FILES['uploadfile']['name']);
		$filename = $_FILES['uploadfile']['name'];
	}

	insert_query("tbldownloads", array("category" => $catid, "type" => $type, "title" => $title, "description" => $description, "location" => $filename, "clientsonly" => $clientsonly, "hidden" => $hidden, "productdownload" => $productdownload));
	logActivity("Added New Download - " . $title);
}

ob_start();

if (!is_writable($downloads_dir)) {
	infoBox($aInt->lang("support", "permissionswarn"), $aInt->lang("support", "permissionswarninfo"));
	echo $infobox;
	$error = "1";
}


if ($action == "") {
	if ($sub == "save") {
		check_token("WHMCS.admin.default");
		update_query("tbldownloads", array("category" => $category, "type" => $type, "title" => $title, "description" => $description, "downloads" => $downloads, "location" => $location, "clientsonly" => $clientsonly, "hidden" => $hidden, "productdownload" => $productdownload), array("id" => $id));
		logActivity("Modified Download (ID: " . $id . ")");
	}


	if ($sub == "savecat") {
		check_token("WHMCS.admin.default");
		update_query("tbldownloadcats", array("name" => $name, "description" => $description, "hidden" => $hidden, "parentid" => $parentcategory), array("id" => $id));
		logActivity("Modified Download (ID: " . $id . ")");
	}


	if ($addcategory == "true") {
		check_token("WHMCS.admin.default");
		insert_query("tbldownloadcats", array("parentid" => $catid, "name" => $catname, "description" => $description, "hidden" => $hidden));
		logActivity("Added New Download Category - " . $catname);
	}


	if ($sub == "delete") {
		check_token("WHMCS.admin.default");
		$result = select_query("tbldownloads", "location", array("id" => $id));
		$data = mysql_fetch_array($result);
		$filename = $data['location'];

		if ((substr($filename, 0, 7) == "http://" || substr($filename, 0, 8) == "https://") || substr($filename, 0, 6) == "ftp://") {
		}
		else {
			deleteFile($downloads_dir, $filename);
		}

		delete_query("tbldownloads", array("id" => $id));
		logActivity("Deleted Download (ID: " . $id . ")");
	}


	if ($sub == "deletecategory") {
		check_token("WHMCS.admin.default");
		delete_query("tbldownloads", array("category" => $id));
		delete_query("tbldownloadcats", array("id" => $id));
		logActivity("Deleted Download Category (ID: " . $id . ")");
	}


	if (!$catid) {
		$catid = 0;
	}

	$breadcrumbnav = "";

	if ($catid != "0") {
		$result = select_query("tbldownloadcats", "", array("id" => $catid));
		$data = mysql_fetch_array($result);
		$catid = $data['id'];

		if (!$catid) {
			$aInt->gracefulExit("Category ID Not Found");
		}

		$catparentid = $data['parentid'];
		$catname = $data['name'];
		$catbreadcrumbnav = " > <a href=\"" . $PHP_SELF . "?catid=" . $catid . "\">" . $catname . "</a>";

		while ($catparentid != "0") {
			$result = select_query("tbldownloadcats", "", array("id" => $catparentid));
			$data = mysql_fetch_array($result);
			$cattempid = $data['id'];
			$catparentid = $data['parentid'];
			$catname = $data['name'];
			$catbreadcrumbnav = " > <a href=\"" . $PHP_SELF . "?catid=" . $cattempid . "\">" . $catname . "</a>" . $catbreadcrumbnav;
		}

		$breadcrumbnav .= $catbreadcrumbnav;
	}

	$aInt->deleteJSConfirm("doDelete", "support", "dldelsure", $_SERVER['PHP_SELF'] . "?catid=" . $catid . "&sub=delete&id=");
	$aInt->deleteJSConfirm("doDeleteCat", "support", "dlcatdelsure", $_SERVER['PHP_SELF'] . "?catid=" . $catid . "&sub=deletecategory&id=");
	echo $aInt->Tabs(array($aInt->lang("support", "addcategory"), $aInt->lang("support", "adddownload")), true);
	echo "
<div id=\"tab0box\" class=\"tabbox\">
  <div id=\"tab_content\">

<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "?catid=";
	echo $catid;
	echo "&addcategory=true\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
	echo $aInt->lang("support", "catname");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"catname\" size=\"40\"> <input type=\"checkbox\" name=\"hidden\"> ";
	echo $aInt->lang("support", "ticktohide");
	echo "</td></tr>
<tr><td class=\"fieldlabel\">";
	echo $aInt->lang("fields", "description");
	echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"description\" size=\"100\"></td></tr>
</table>
<img src=\"images/spacer.gif\" width=\"1\" height=\"10\"><br>
<div align=\"center\"><input type=\"submit\" value=\"";
	echo $aInt->lang("support", "addcategory");
	echo "\" class=\"button\"></div>
</form>

  </div>
</div>
<div id=\"tab1box\" class=\"tabbox\">
  <div id=\"tab_content\">

";

	if ($catid != "") {
		echo "<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?catid=";
		echo $catid;
		echo "&adddownload=true\" name=\"sample\" enctype=\"multipart/form-data\">
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("fields", "type");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"type\">
<option value=\"zip\">";
		echo $aInt->lang("support", "zipfile");
		echo "</option>
<option value=\"exe\">";
		echo $aInt->lang("support", "exefile");
		echo "</option>
<option value=\"pdf\">";
		echo $aInt->lang("support", "pdffile");
		echo "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "title");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"title\" size=50></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "description");
		echo "</td><td class=\"fieldarea\"><textarea name=\"description\" rows=3 style=\"width:100%\"></textarea></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "uploadfile");
		echo "</td><td class=\"fieldarea\"><label><input type=\"radio\" name=\"filetype\" value=\"manual\" checked> ";
		echo $aInt->lang("support", "manualftp");
		echo "</label><br />";
		echo $aInt->lang("support", "enterfilename");
		echo ": <input type=\"text\" name=\"filename\" size=\"50\"><br /><label><input type=\"radio\" name=\"filetype\" value=\"upload\"> ";
		echo $aInt->lang("support", "uploadfile");
		echo "</label><br />";
		echo $aInt->lang("support", "choosefile");
		echo ": <input type=\"file\" name=\"uploadfile\" style=\"width:80%\"><br>";
		echo "<font style=\"color:#cc0000\">" . $aInt->lang("support", "servermaxfile") . ": <strong>" . ini_get("upload_max_filesize") . "</strong> - " . $aInt->lang("support", "howtoincrease");
		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "clientsonly");
		echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"clientsonly\"> ";
		echo $aInt->lang("support", "clientsonlyinfo");
		echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("global", "hidden");
		echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"hidden\"> ";
		echo $aInt->lang("support", "hiddeninfo");
		echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "productdl");
		echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"productdownload\"> ";
		echo $aInt->lang("support", "productdlinfo");
		echo "</label></td></tr>
</table>
<img src=\"images/spacer.gif\" width=\"1\" height=\"10\"><br>
<div align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("support", "adddownload");
		echo "\" class=\"button\"> <input type=\"button\" value=\"";
		echo $aInt->lang("global", "cancelchanges");
		echo "\" onClick=\"window.location='";
		echo $PHP_SELF;
		echo "'\" class=\"button\"></div>
</form>
";
	}
	else {
		echo $aInt->lang("support", "notoplevel");
	}

	echo "
  </div>
</div>

";
	echo "<p>" . $aInt->lang("support", "youarehere") . (": <a href=\"" . $PHP_SELF . "\">") . $aInt->lang("support", "dlhome") . "</a> " . $breadcrumbnav . "</p>";
	$result = select_query("tbldownloadcats", "", array("parentid" => $catid), "name", "ASC");
	$numcats = mysql_num_rows($result);
	echo "
";

	if ($numcats != "0") {
		echo "
<p><b>";
		echo $aInt->lang("support", "categories");
		echo "</b></p>

<table width=100%><tr>
";

		if ($catid == "") {
			$catid = "0";
		}

		$result = select_query("tbldownloadcats", "", array("parentid" => $catid), "name", "ASC");
		$i = 0;

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$name = $data['name'];
			$description = $data['description'];
			$hidden = $data['hidden'];
			$idnumbers = "";
			$idnumbers[] = $id;
			$result3 = select_query("tbldownloadcats", "id", array("parentid" => $id));

			while ($data3 = mysql_fetch_array($result3)) {
				$idnumbers[] = $data3['id'];
				$result4 = select_query("tbldownloadcats", "id", array("parentid" => $data3['id']));

				while ($data4 = mysql_fetch_array($result4)) {
					$idnumbers[] = $data4['id'];
					$result5 = select_query("tbldownloadcats", "id", array("parentid" => $data4['id']));

					while ($data5 = mysql_fetch_array($result5)) {
						$idnumbers[] = $data5['id'];
					}
				}
			}

			$queryreport = "";
			foreach ($idnumbers as $idnumber) {
				$queryreport .= " OR category='" . $idnumber . "'";
			}

			$queryreport = substr($queryreport, 4);
			$result2 = select_query("tbldownloads", "COUNT(*)", $queryreport);
			$data2 = mysql_fetch_array($result2);
			$numarticles = $data2[0];
			echo "<td width=33%><img src=\"../images/folder.gif\" align=\"absmiddle\"> <a href=\"" . $PHP_SELF . "?catid=" . $id . "\"><b>" . $name . "</b></a> (" . $numarticles . ") <a href=\"" . $PHP_SELF . "?action=editcat&id=" . $id . "\"><img src=\"images/edit.gif\" align=\"absmiddle\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . ("\" /></a> <a href=\"#\" onClick=\"doDeleteCat(" . $id . ")\"><img src=\"images/delete.gif\" align=\"absmiddle\" border=\"0\" alt=\"") . $aInt->lang("global", "delete") . "\" /></a>";

			if ($hidden == "on") {
				echo " <font color=#cccccc>(" . strtoupper($aInt->lang("fields", "hidden")) . ")</font>";
			}

			echo "<br>" . $description . "</td>";
			++$i;

			if ($i % 3 == 0) {
				echo "</tr><tr><td><br></td></tr><tr>";
				$i = 0;
			}
		}

		echo "</tr></table>

";
	}

	$result = select_query("tbldownloads", "", array("category" => $catid), "title", "ASC");
	$numarticles = mysql_num_rows($result);

	if ($numarticles != "0") {
		echo "
<p><b>";
		echo $aInt->lang("clientsummary", "filesheading");
		echo "</b></p>

<table width=100%><tr>
";
		$result = select_query("tbldownloads", "", array("category" => $catid), "title", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$category = $data['category'];
			$title = $data['title'];
			$description = strip_tags($data['description']);
			$downloads = $data['downloads'];
			$clientsonly = $data['clientsonly'];
			$hidden = $data['hidden'];
			$article = substr($article, 0, 150) . "...";
			echo "<p><img src=\"../images/article.gif\" align=\"absmiddle\"> <a href=\"" . $PHP_SELF . "?action=edit&id=" . $id . "\"><b>" . $title . "</b></a> <a href=\"#\" onClick=\"doDelete(" . $id . ")\"><img src=\"images/delete.gif\" align=\"absmiddle\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\" /></a></font>";

			if ($clientsonly == "on") {
				echo " <font color=#cccccc>(" . strtoupper($aInt->lang("support", "clientsonly")) . ")</font>";
			}


			if ($hidden == "on") {
				echo " <font color=#cccccc>(" . strtoupper($aInt->lang("fields", "hidden")) . ")</font>";
			}

			echo "<br>" . $description . "<br><font color=#cccccc>" . $aInt->lang("support", "downloads") . (": " . $downloads . "</font>");
		}

		echo "</tr></table>

";
	}
	else {
		echo "
<p><b>";
		echo $aInt->lang("support", "nodlfiles");
		echo "</b></p>

";
	}

	echo "
";
}
else {
	if ($action == "edit") {
		$result = select_query("tbldownloads", "", array("id" => $id));
		$data = mysql_fetch_array($result);
		$category = $data['category'];
		$type = $data['type'];
		$title = $data['title'];
		$description = $data['description'];
		$downloads = $data['downloads'];
		$location = $data['location'];
		$clientsonly = $data['clientsonly'];
		$hidden = $data['hidden'];
		$productdownload = $data['productdownload'];
		echo "
<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?catid=";
		echo $category;
		echo "&sub=save&id=";
		echo $id;
		echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
		echo $aInt->lang("support", "category");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"category\">";
		$result = select_query("tbldownloadcats", "", "", "parentid` ASC,`name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$catid = $data['id'];
			$category2 = $data['name'];
			echo "<option value=\"" . $catid . "\"";

			if ($catid == $category) {
				echo " selected";
			}

			echo ">" . $category2;
		}

		echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "type");
		echo "</td><td class=\"fieldarea\">";
		echo "<s";
		echo "elect name=\"type\">
<option value=\"zip\" ";

		if ($type == "zip") {
			echo "SELECTED";
		}

		echo ">";
		echo $aInt->lang("support", "zipfile");
		echo "</option>
<option value=\"exe\" ";

		if ($type == "exe") {
			echo "SELECTED";
		}

		echo ">";
		echo $aInt->lang("support", "exefile");
		echo "</option>
<option value=\"pdf\" ";

		if ($type == "pdf") {
			echo "SELECTED";
		}

		echo ">";
		echo $aInt->lang("support", "pdffile");
		echo "</option>
</select></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "title");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"title\" value=\"";
		echo $title;
		echo "\" size=50></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("fields", "description");
		echo "</td><td class=\"fieldarea\"><textarea name=\"description\" rows=3 style=\"width:100%\">";
		echo $description;
		echo "</textarea></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "filename");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"location\" value=\"";
		echo $location;
		echo "\" size=60></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "downloads");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"downloads\" value=\"";
		echo $downloads;
		echo "\" size=6></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "clientsonly");
		echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"clientsonly\"";

		if ($clientsonly == "on") {
			echo "checked";
		}

		echo "> ";
		echo $aInt->lang("support", "clientsonlyinfo");
		echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("global", "hidden");
		echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"hidden\"";

		if ($hidden == "on") {
			echo "checked";
		}

		echo " /> ";
		echo $aInt->lang("support", "hiddeninfo");
		echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "productdl");
		echo "</td><td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"productdownload\"";

		if ($productdownload == "on") {
			echo "checked";
		}

		echo "> ";
		echo $aInt->lang("support", "productdlinfo");
		echo "</label></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "downloadlink");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" size=\"100\" value=\"";
		echo $CONFIG['SystemURL'];
		echo "/dl.php?type=d&id=";
		echo $id;
		echo "\" readonly></td></tr>
</table>

<p align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("global", "savechanges");
		echo "\" class=\"button\"></p>

</form>

";
	}
	else {
		if ($action == "editcat") {
			$result = select_query("tbldownloadcats", "", array("id" => $id));
			$data = mysql_fetch_array($result);
			$parentid = $data['parentid'];
			$name = $data['name'];
			$description = $data['description'];
			$hidden = $data['hidden'];
			echo "
<form method=\"post\" action=\"";
			echo $PHP_SELF;
			echo "?catid=";
			echo $parentid;
			echo "&sub=savecat&id=";
			echo $id;
			echo "\">

<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td width=\"15%\" class=\"fieldlabel\">";
			echo $aInt->lang("support", "parentcat");
			echo "</td><td class=\"fieldarea\">";
			echo "<s";
			echo "elect name=\"parentcategory\"><option value=\"\">";
			echo $aInt->lang("support", "toplevel");
			$result = select_query("tbldownloadcats", "", "", "parentid` ASC,`name", "ASC");

			while ($data = mysql_fetch_array($result)) {
				$id = $data['id'];
				$category2 = $data['name'];
				echo "<option value=\"" . $id . "\"";

				if ($id == $parentid) {
					echo " selected";
				}

				echo ">" . $category2;
			}

			echo "</select></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("support", "catname");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"name\" value=\"";
			echo $name;
			echo "\" size=40></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("fields", "description");
			echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"description\" value=\"";
			echo $description;
			echo "\" size=100></td></tr>
<tr><td class=\"fieldlabel\">";
			echo $aInt->lang("fields", "hidden");
			echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"hidden\"";

			if ($hidden == "on") {
				echo " checked";
			}

			echo "> ";
			echo $aInt->lang("support", "hiddeninfo");
			echo "</td></tr>
</table>

<P ALIGN=\"center\"><input type=\"submit\" value=\"";
			echo $aInt->lang("global", "savechanges");
			echo "\" class=\"button\"></P>
</form>

";
		}
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>