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

define("CLIENTAREA", true);
require "init.php";
require "includes/ticketfunctions.php";
require "includes/customfieldfunctions.php";
require "includes/clientfunctions.php";
$pagetitle = $_LANG['supportticketssubmitticket'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"clientarea.php\">" . $_LANG['clientareatitle'] . "</a> > <a href=\"supporttickets.php\">" . $_LANG['supportticketspagetitle'] . "</a> > <a href=\"submitticket.php\">" . $_LANG['supportticketssubmitticket'] . "</a>";
$pageicon = "images/submitticket_big.gif";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);
$action = $whmcs->get_req_var("action");
$step = $whmcs->get_req_var("step");
$name = $whmcs->get_req_var("name");
$email = $whmcs->get_req_var("email");
$urgency = $whmcs->get_req_var("urgency");
$subject = $whmcs->get_req_var("subject");
$message = $whmcs->get_req_var("message");
$customfield = $whmcs->get_req_var("customfield");

if ($action == "getkbarticles") {
	$kbarticles = getKBAutoSuggestions($text);

	if (count($kbarticles)) {
		$smarty->assign("kbarticles", $kbarticles);
		echo $smarty->fetch($whmcs->get_sys_tpl_name() . "/supportticketsubmit-kbsuggestions.tpl");
	}

	exit();
}

$captcha = clientAreaInitCaptcha();
$validate = new WHMCS_Validate();

if ($step == "3") {
	check_token();

	if (!isset($_SESSION['uid'])) {
		$validate->validate("required", "name", "supportticketserrornoname");

		if ($validate->validate("required", "email", "supportticketserrornoemail")) {
			$validate->validate("email", "email", "clientareaerroremailinvalid");
		}
	}

	$validate->validate("required", "subject", "supportticketserrornosubject");
	$validate->validate("required", "message", "supportticketserrornomessage");
	$validate->validate("fileuploads", "attachments", "supportticketsfilenotallowed");

	if ($captcha) {
		$validate->validate("captcha", "code", "captchaverifyincorrect");
	}

	$validate->validateCustomFields("support", $deptid);

	if ($validate->hasErrors()) {
		$step = "2";
	}
}

checkContactPermission("tickets");
$usingsupportmodule = false;

if ($CONFIG['SupportModule']) {
	if (!isset($_SESSION['uid'])) {
		$goto = "submitticket";
		include "login.php";
	}


	if (!isValidforPath($CONFIG['SupportModule'])) {
		exit("Invalid Support Module");
	}

	$supportmodulepath = "modules/support/" . $CONFIG['SupportModule'] . "/submitticket.php";

	if (file_exists($supportmodulepath)) {
		$usingsupportmodule = true;
		$templatefile = "";
		require $supportmodulepath;
		outputClientArea($templatefile);
		exit();
	}
}


if ($step == "") {
	$templatefile = "supportticketsubmit-stepone";
	$result = select_query("tblticketdepartments", "COUNT(*)", array("hidden" => ""));
	$data = mysql_fetch_array($result);
	$totaldepartments = $data[0];
	$where = "";
	$where['hidden'] = "";

	if (!$whmcs->get_config("ShowClientOnlyDepts") && !isset($_SESSION['uid'])) {
		$where['clientsonly'] = "";
	}

	$departments = array();
	$result = select_query("tblticketdepartments", "", $where, "order", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$dept_id = $data['id'];
		$dept_name = $data['name'];
		$dept_desc = $data['description'];
		$departments[] = array("id" => $dept_id, "name" => $dept_name, "description" => $dept_desc);
	}


	if (!$departments && $totaldepartments) {
		$goto = "submitticket";
		include "login.php";
	}

	$smarty->assign("departments", $departments);
}
else {
	if ($step == "2") {
		$templatefile = "supportticketsubmit-steptwo";
		$result = select_query("tblticketdepartments", "id,name,clientsonly", array("id" => $deptid));
		$data = mysql_fetch_array($result);
		$deptid = $data['id'];

		if (!$deptid) {
			redir();
		}

		$deptname = $data['name'];
		$clientsonly = $data['clientsonly'];

		if ($clientsonly && !$_SESSION['uid']) {
			$templatefile = "supportticketsubmit-stepone";
			$goto = "submitticket";
			include "login.php";
		}

		$smarty->assign("deptid", $deptid);
		$smarty->assign("department", $deptname);
		$where = "(hidden=''";

		if (!isset($_SESSION['uid'])) {
			$where .= " AND clientsonly=''";
		}

		$where .= ") OR id=" . (int)$deptid;
		$departments = array();
		$result = select_query("tblticketdepartments", "", $where, "order", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$dept_id = $data['id'];
			$dept_name = $data['name'];
			$dept_desc = $data['description'];
			$departments[] = array("id" => $dept_id, "name" => $dept_name, "description" => $dept_desc);
		}

		$smarty->assign("departments", $departments);

		if (isset($_SESSION['uid'])) {
			$clientsdetails = getClientsDetails($_SESSION['uid'], $_SESSION['cid']);
			$clientname = $clientsdetails['firstname'] . " " . $clientsdetails['lastname'];
			$email = $clientsdetails['email'];
			$smarty->assign("clientname", $clientname);
			$smarty->assign("email", $email);
			$relatedservices = array();
			$result = select_query("tblhosting", "tblhosting.id,tblhosting.domain,tblhosting.domainstatus,tblproducts.name", array("userid" => $_SESSION['uid']), "tblproducts`.`name` ASC,`tblhosting`.`domain", "ASC", "", "tblproducts ON tblproducts.id=tblhosting.packageid");

			while ($data = mysql_fetch_array($result)) {
				$productname = $data['name'];

				if ($data['domain']) {
					$productname .= " - " . $data['domain'];
				}

				$relatedservices[] = array("id" => "S" . $data['id'], "name" => $productname, "status" => $_LANG["clientarea" . strtolower($data['domainstatus'])]);
			}

			$result = select_query("tbldomains", "", array("userid" => $_SESSION['uid']), "domain", "ASC");

			while ($data = mysql_fetch_array($result)) {
				$relatedservices[] = array("id" => "D" . $data['id'], "name" => $_LANG['clientareahostingdomain'] . " - " . $data['domain'], "status" => $_LANG["clientarea" . strtolower(str_replace("-", "", $data['status']))]);
			}

			$smartyvalues['relatedservices'] = $relatedservices;
		}
		else {
			$smarty->assign("name", $name);
			$smarty->assign("email", $email);
		}

		$customfields = getCustomFields("support", $deptid, "", "", "", $customfield);
		$smarty->assign("customfields", $customfields);
		$smarty->assign("allowedfiletypes", $CONFIG['TicketAllowedFileTypes']);
		$smarty->assign("errormessage", $validate->getHTMLErrorOutput());
		$smarty->assign("urgency", $urgency);
		$smarty->assign("subject", $subject);
		$smarty->assign("message", $message);
		$smarty->assign("capatacha", $captcha);
		$smarty->assign("recapatchahtml", clientAreaReCaptchaHTML());

		if ($CONFIG['SupportTicketKBSuggestions']) {
			$smarty->assign("kbsuggestions", true);
		}
	}
	else {
		if ($step == "3") {
			$result = select_query("tblticketdepartments", "id,clientsonly", array("id" => $deptid));
			$data = mysql_fetch_array($result);
			$deptid = $data['id'];
			$check_clientsonly = $data['clientsonly'];

			if (!$deptid || ($check_clientsonly && !$_SESSION['uid'])) {
				exit();
			}

			$attachments = uploadTicketAttachments();
			$from['name'] = $name;
			$from['email'] = $email;
			$message .= ("\r\n") . "
----------------------------
IP Address: " . $remote_ip;
			$cc = "";

			if ($_SESSION['cid']) {
				$result = select_query("tblcontacts", "email", array("id" => $_SESSION['cid'], "userid" => $_SESSION['uid']));
				$data = mysql_fetch_array($result);
				$cc = $data['email'];
			}

			$ticketdetails = openNewTicket($_SESSION['uid'], $_SESSION['cid'], $deptid, $subject, $message, $urgency, $attachments, $from, $relatedservice, $cc);
			saveCustomFields($ticketdetails['ID'], $customfield);
			$_SESSION['tempticketdata'] = $ticketdetails;
			redir("step=4");
		}
		else {
			if ($step == "4") {
				$ticketdetails = $_SESSION['tempticketdata'];
				$templatefile = "supportticketsubmit-confirm";
				$smarty->assign("tid", $ticketdetails['TID']);
				$smarty->assign("c", $ticketdetails['C']);
				$smarty->assign("subject", $ticketdetails['Subject']);
			}
		}
	}
}

outputClientArea($templatefile);
?>