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

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View Email Templates");
$aInt->title = $aInt->lang("emailtpls", "title");
$aInt->sidebar = "config";
$aInt->icon = "massmail";
$aInt->helplink = "Email Templates";
$activelanguages = array();
$result = select_query("tblemailtemplates", "DISTINCT language", "", "type", "ASC");

while ($data = mysql_fetch_array($result)) {
	$activelanguage = $data['language'];

	if ($activelanguage) {
		$activelanguages[] = $activelanguage;
	}
}


if ($action == "new") {
	check_token("WHMCS.admin.default");
	checkPermission("Create/Edit Email Templates");
	$emailid = insert_query("tblemailtemplates", array("type" => $type, "name" => $name, "language" => "", "custom" => "1"));
	redir("action=edit&id=" . $emailid);
	exit();
}


if ($action == "delatt") {
	check_token("WHMCS.admin.default");
	checkPermission("Create/Edit Email Templates");
	$result = select_query("tblemailtemplates", "attachments", array("id" => $id));
	$data = mysql_fetch_array($result);
	$attachments = $data['attachments'];
	$attachments = explode(",", $attachments);
	$i = (int)$_GET['i'];
	$attachment = $attachments[$i];
	deleteFile($downloads_dir, $attachment);
	unset($attachments[$i]);
	update_query("tblemailtemplates", array("attachments" => implode(",", $attachments)), array("id" => $id));
	redir("action=edit&id=" . $id);
	exit();
}

ob_start();

if ($action == "") {
	if ($addlanguage) {
		check_token("WHMCS.admin.default");
		checkPermission("Manage Email Template Languages");
		$result = select_query("tblemailtemplates", "", array("language" => ""));

		while ($data = mysql_fetch_array($result)) {
			$type = $data['type'];
			$name = $data['name'];
			$subject = $data['subject'];
			$message = $data['message'];
			$fromname = $data['fromname'];
			$fromemail = $data['fromemail'];
			$disabled = $data['disabled'];
			$custom = $data['custom'];
			insert_query("tblemailtemplates", array("type" => $type, "name" => $name, "subject" => $subject, "message" => $message, "language" => $addlang));
		}

		$activelanguages = "";
		$activelanguages = array();
		$result = select_query("tblemailtemplates", "DISTINCT language", "", "type", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$activelanguage = $data['language'];

			if ($activelanguage) {
				$activelanguages[] = $activelanguage;
			}
		}

		redir();
	}


	if ($disablelanguage && $dislang) {
		check_token("WHMCS.admin.default");
		checkPermission("Manage Email Template Languages");
		delete_query("tblemailtemplates", array("language" => $dislang));
		$activelanguages = "";
		$activelanguages = array();
		$result = select_query("tblemailtemplates", "DISTINCT language", "", "type", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$activelanguage = $data['language'];

			if ($activelanguage) {
				$activelanguages[] = $activelanguage;
			}
		}

		redir();
	}


	if ($savemessage) {
		check_token("WHMCS.admin.default");
		checkPermission("Create/Edit Email Templates");

		if ($fromname == $CONFIG['CompanyName']) {
			$fromname = "";
		}


		if ($fromemail == $CONFIG['Email']) {
			$fromemail = "";
		}

		$result = select_query("tblemailtemplates", "attachments", array("id" => $id));
		$data = mysql_fetch_array($result);
		$attachments = $data['attachments'];
		$attachments = ($attachments ? explode(",", $attachments) : array());

		if ($_FILES['attachments']) {
			foreach ($_FILES['attachments']['name'] as $num => $filename) {

				if (empty($_FILES['attachments']['name']) || empty($_FILES['attachments']['name'][$num])) {
					continue;
				}


				if (!isFileNameSafe($_FILES['attachments']['name'][$num])) {
					$aInt->gracefulExit("Invalid upload filename.  Valid filenames contain only alpha-numeric, dot, hyphen and underscore characters.");
					exit();
				}

				$filename = trim($filename);

				if ($filename) {
					mt_srand(time());
					$rand = mt_rand(100000, 999999);
					$newfilename = $rand . "_" . $filename;
					move_uploaded_file($_FILES['attachments']['tmp_name'][$num], $downloads_dir . $newfilename);
					$attachments[] = $newfilename;
					continue;
				}
			}
		}

		update_query("tblemailtemplates", array("fromname" => $fromname, "fromemail" => $fromemail, "attachments" => implode(",", $attachments), "disabled" => $disabled, "copyto" => $copyto, "plaintext" => $plaintext), array("id" => $id));
		foreach ($subject as $key => $value) {
			update_query("tblemailtemplates", array("subject" => html_entity_decode($value, ENT_QUOTES), "message" => html_entity_decode($message[$key], ENT_QUOTES)), array("id" => $key));
		}


		if ($toggleeditor) {
			if ($editorstate) {
				redir("action=edit&id=" . $id);
			}
			else {
				redir("action=edit&id=" . $id . "&noeditor=1");
			}
		}

		redir("success=true");
	}


	if ($delete == "true") {
		check_token("WHMCS.admin.default");
		checkPermission("Delete Email Templates");
		delete_query("tblemailtemplates", array("id" => $id));
		redir("deleted=true");
	}


	if ($success) {
		infoBox($aInt->lang("emailtpls", "updatesuccess"), $aInt->lang("emailtpls", "updatesuccessinfo"));
	}
	else {
		if ($deleted) {
			infoBox($aInt->lang("emailtpls", "delsuccess"), $aInt->lang("emailtpls", "delsuccessinfo"));
		}
	}

	echo $infobox;
	$aInt->deleteJSConfirm("doDelete", "emailtpls", "delsure", "?delete=true&id=");
	echo "
<p>";
	echo $aInt->lang("emailtpls", "info");
	echo "</p>

";

	if (checkPermission("Create/Edit Email Templates", true)) {
		echo "<div class=\"contextbar\">
<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?action=new\">
<b>";
		echo $aInt->lang("emailtpls", "createnew");
		echo "</b> &nbsp;&nbsp;&nbsp; Type: ";
		echo "<s";
		echo "elect name=\"type\"><option value=\"general\">";
		echo $aInt->lang("emailtpls", "typegeneral");
		echo "</option><option value=\"product\">";
		echo $aInt->lang("emailtpls", "typeproduct");
		echo "</option><option value=\"domain\">";
		echo $aInt->lang("emailtpls", "typedomain");
		echo "</option><option value=\"invoice\">";
		echo $aInt->lang("emailtpls", "typeinvoice");
		echo "</option></select> &nbsp;&nbsp;&nbsp; ";
		echo $aInt->lang("emailtpls", "uniquename");
		echo ": <input type=\"text\" name=\"name\" size=\"30\"> &nbsp;&nbsp;&nbsp; <input type=\"submit\" value=\"";
		echo $aInt->lang("emailtpls", "create");
		echo "\" class=\"button\">
</form>
</div>
";
	}

	echo "
<div style=\"float:left;\">
";
	function outputEmailTpls($type) {
		global $aInt;

		$result2 = select_query("tblemailtemplates", "", array("type" => $type, "language" => ""), "name", "ASC");

		while ($data = mysql_fetch_array($result2)) {
			$id = $data['id'];
			$name = $data['name'];
			$disabled = $data['disabled'];
			$custom = $data['custom'];

			if ($disabled) {
				$csstype = "disabled";
				$disabled = " (" . $aInt->lang("emailtpls", "disabled") . ")";
			}
			else {
				if ($custom) {
					$csstype = "custom";
				}
				else {
					$csstype = "standard";
				}
			}

			echo "<div class=\"emailtpl" . $csstype . ("\"><a href=\"?action=edit&id=" . $id . "\"><img src=\"images/icons/massmail.png\" align=\"absmiddle\" border=\"0\" alt=\"") . $aInt->lang("global", "edit") . ("\" /> " . $name . "</a>" . $disabled . " ");

			if ($custom == "1") {
				echo "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" align=\"absmiddle\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\" /></a>";
			}

			echo "</div>";
		}

		echo "<div style=\"clear:left;\"></div>";
	}

	echo "<h2>" . ucfirst($aInt->lang("emailtpls", "typegeneral")) . " " . $aInt->lang("emailtpls", "messages") . "</h2>";
	outputEmailTpls("general");
	echo "<h2>" . ucfirst($aInt->lang("emailtpls", "typeproduct")) . " " . $aInt->lang("emailtpls", "messages") . "</h2>";
	outputEmailTpls("product");
	echo "<h2>" . ucfirst($aInt->lang("emailtpls", "typedomain")) . " " . $aInt->lang("emailtpls", "messages") . "</h2>";
	outputEmailTpls("domain");
	echo "<h2>" . ucfirst($aInt->lang("emailtpls", "typeinvoice")) . " " . $aInt->lang("emailtpls", "messages") . "</h2>";
	outputEmailTpls("invoice");
	echo "<h2>" . ucfirst($aInt->lang("emailtpls", "typesupport")) . " " . $aInt->lang("emailtpls", "messages") . "</h2>";
	outputEmailTpls("support");
	echo "<h2>" . ucfirst($aInt->lang("emailtpls", "typeadmin")) . " " . $aInt->lang("emailtpls", "messages") . "</h2>";
	outputEmailTpls("admin");
	$result = select_query("tblemailtemplates", "DISTINCT type", "type NOT IN ('general','product','domain','invoice','support','admin')", "type", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$type = $data['type'];
		echo "<h2>" . ucfirst($aInt->lang("emailtpls", "type" . $type)) . " " . $aInt->lang("emailtpls", "messages") . "</h2>";
		outputEmailTpls($type);
	}

	echo "</div>

<div style=\"clear:both;\"></div>

<br />

";

	if (checkPermission("Manage Email Template Languages", true)) {
		echo "<div class=\"contextbar\">
<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "\">
<div style=\"display:inline;padding-right:20px;\">
<b>";
		echo $aInt->lang("emailtpls", "activelang");
		echo ":</b> ";
		echo $aInt->lang("global", "default");
		foreach ($activelanguages as $language) {
			echo ", " . ucfirst($language);
		}

		echo "</div>
<div style=\"display:inline;padding-right:20px;\">
<b>";
		echo $aInt->lang("global", "add");
		echo ":</b> ";
		echo "<s";
		echo "elect name=\"addlang\">";
		$availlangs = $whmcs->getValidLanguages();
		foreach ($availlangs as $lang) {
			echo "<option value=\"" . $lang . "\">" . ucfirst($lang) . "</option>";
		}

		echo "</select> <input type=\"submit\" name=\"addlanguage\" value=\"";
		echo $aInt->lang("global", "submit");
		echo "\" class=\"button\" />
</div>
<div style=\"display:inline;\">
<b>";
		echo $aInt->lang("global", "disable");
		echo ":</b> ";
		echo "<s";
		echo "elect name=\"dislang\"><option value=\"xxx\">";
		echo $aInt->lang("emailtpls", "chooseone");
		echo "</option>
";
		foreach ($activelanguages as $lang) {
			echo "<option value=\"" . $lang . "\">" . ucfirst($lang) . "</option>";
		}

		echo "</select> <input type=\"submit\" name=\"disablelanguage\" value=\"";
		echo $aInt->lang("global", "submit");
		echo "\" class=\"button\" />
</div>
</form>
</div>
";
	}

	echo "
";
}
else {
	if ($action == "edit") {
		$result = select_query("tblemailtemplates", "", array("id" => $id));
		$data = mysql_fetch_array($result);
		$type = $data['type'];
		$name = $data['name'];
		$subject = $data['subject'];
		$message = $data['message'];
		$attachments = $data['attachments'];
		$fromname = $data['fromname'];
		$fromemail = $data['fromemail'];
		$disabled = $data['disabled'];
		$copyto = $data['copyto'];
		$plaintext = $data['plaintext'];

		if ($plaintextchange) {
			if ($plaintext) {
				$message = str_replace("\r\n\r\n", "</p><p>", $message);
				$message = str_replace("\r\n", "<br>", $message);

				update_query("tblemailtemplates", array("message" => $message, "plaintext" => ""), array("id" => $id));
				$plaintext = "";
			}
			else {
				$message = str_replace("<p>", "", $message);
				$message = str_replace("</p>", "\r\n\r\n", $message);
				$message = str_replace("<br>", "\r\n", $message);
				$message = str_replace("<br />", "\r\n", $message);

				$message = strip_tags($message);
				update_query("tblemailtemplates", array("message" => $message, "plaintext" => "1"), array("id" => $id));
				$plaintext = "1";
			}
		}

		$jquerycode = "$(\"#addfileupload\").click(function () {
    $(\"#fileuploads\").append(\"<input type=\\\"file\\\" name=\\\"attachments[]\\\" style=\\\"width:70%;\\\" /><br />\");
    return false;
});";
		echo "
<form method=\"post\" action=\"";
		echo $PHP_SELF;
		echo "?savemessage=true&id=";
		echo $id;
		echo "\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"editorstate\" value=\"";
		echo $noeditor;
		echo "\" />
<p><b>";
		echo $name;
		echo "</b></p>
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("emails", "from");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"fromname\" size=\"25\" value=\"";

		if ($fromname == "") {
			echo $CONFIG['CompanyName'];
		}
		else {
			echo $fromname;
		}

		echo "\"> <input type=\"text\" name=\"fromemail\" size=\"40\" value=\"";

		if ($fromemail == "") {
			echo $CONFIG['Email'];
		}
		else {
			echo $fromemail;
		}

		echo "\"></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("emailtpls", "copyto");
		echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"copyto\" size=\"50\" value=\"";
		echo $copyto;
		echo "\"> ";
		echo $aInt->lang("emailtpls", "commasep");
		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("support", "attachments");
		echo "</td><td class=\"fieldarea\">";

		if ($attachments) {
			$attachments = explode(",", $attachments);
			foreach ($attachments as $i => $attachment) {
				$filename = substr($attachment, 7);
				echo $i + 1 . (". " . $filename . " <a href=\"configemailtemplates.php?action=delatt&id=" . $id . "&i=" . $i) . generate_token("link") . "\"><img src=\"images/icons/delete.png\" border=\"0\" align=\"middle\" /> " . $aInt->lang("global", "delete") . "</a><br />";
			}
		}

		echo "<img src=\"images/spacer.gif\" width=\"1\" height=\"2\" /><br /><input type=\"file\" name=\"attachments[]\" style=\"width:70%;\" /> <a href=\"#\" id=\"addfileupload\"><img src=\"images/icons/add.png\" align=\"absmiddle\" border=\"0\" /> ";
		echo $aInt->lang("support", "addmore");
		echo "</a><br /><div id=\"fileuploads\"></div></td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("emailtpls", "plaintext");
		echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"plaintext\" value=\"1\"";

		if ($plaintext) {
			echo " checked";
		}

		echo " onClick=\"window.location='configemailtemplates.php?action=edit&id=";
		echo $id;
		echo "&plaintextchange=true'\"> ";
		echo $aInt->lang("emailtpls", "plaintextinfo");
		echo "</td></tr>
<tr><td class=\"fieldlabel\">";
		echo $aInt->lang("global", "disable");
		echo "</td><td class=\"fieldarea\"><input type=\"checkbox\" name=\"disabled\"";

		if ($disabled) {
			echo " checked";
		}

		echo "> ";
		echo $aInt->lang("emailtpls", "disableinfo");
		echo "</td></tr>
</table>
<br>
";
		$activelanguages = array();
		$result = select_query("tblemailtemplates", "DISTINCT language", "", "type", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$activelanguage = $data['language'];

			if ($activelanguage) {
				$activelanguages[] = $activelanguage;
			}
		}

		$result = select_query("tblemailtemplates", "", array("type" => $type, "name" => $name, "language" => ""));
		$data = mysql_fetch_array($result);
		$id = $data['id'];
		$default_subject = $data['subject'];
		$default_message = $data['message'];
		echo "<div style=\"float:right;\"><input type=\"submit\" name=\"toggleeditor\" value=\"" . $aInt->lang("emailtpls", "rteditor") . "\" class=\"btn\" /></div><b>" . $aInt->lang("emailtpls", "defaultversion") . "</b> - " . sprintf($aInt->lang("emailtpls", "defaultversionexp"), ucfirst($CONFIG['Language'])) . (("<br><br>Subject: <input type=\"text\" name=\"subject[" . $id . "]") . "\" size=80 value=\"" . $default_subject . "\"><br><br>");
		echo "<textarea name=\"message[";
		echo $id;
		echo "]\" id=\"email_msg1\" rows=\"25\" style=\"width:100%\" class=\"tinymce\">";
		echo $default_message;
		echo "</textarea><br>
";
		$i = 0;
		foreach ($activelanguages as $language) {
			$result = select_query("tblemailtemplates", "", array("type" => $type, "name" => $name, "language" => $language));
			$data = mysql_fetch_array($result);
			$id = $data['id'];
			$subject = $data['subject'];
			$message = $data['message'];

			if (!$id) {
				$subject = $default_subject;
				$message = $default_message;
				$id = insert_query("tblemailtemplates", array("type" => $type, "name" => $name, "language" => $language, "subject" => $subject, "message" => $message));
			}

			echo "<b>" . ucfirst($language) . " " . $aInt->lang("emailtpls", "version") . (("</b><br><br>Subject: <input type=\"text\" name=\"subject[" . $id . "]") . "\" size=80 value=\"" . $subject . "\"><br><br>");
			echo "<textarea name=\"message[";
			echo $id;
			echo "]\" id=\"email_msg";
			echo $i;
			echo "\" rows=\"25\" style=\"width:100%\" class=\"tinymce\">";
			echo $message;
			echo "</textarea><br>
";
			++$i;
		}

		echo "<p align=\"center\"><input type=\"submit\" value=\"";
		echo $aInt->lang("global", "savechanges");
		echo "\" class=\"btn btn-primary\" /></p>
</form>

";

		if (!$plaintext && !$noeditor) {
			$aInt->richTextEditor();
		}

		include "mergefields.php";
	}
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jscode = $jscode;
$aInt->jquerycode = $jquerycode;
$aInt->display();
?>