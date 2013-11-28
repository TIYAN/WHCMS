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

define("CLIENTAREA", true);
require "init.php";
$pagetitle = $_LANG['contacttitle'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"contact.php\">" . $_LANG['contacttitle'] . "</a>";
$templatefile = "contact";
$pageicon = "images/contact_big.gif";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);
$action = $whmcs->get_req_var("action");
$name = $whmcs->get_req_var("name");
$email = $whmcs->get_req_var("email");
$subject = $whmcs->get_req_var("subject");
$message = $whmcs->get_req_var("message");

if ($CONFIG['ContactFormDept']) {
	redir("step=2&deptid=" . (int)$CONFIG['ContactFormDept'], "submitticket.php");
}

$capatacha = clientAreaInitCaptcha();
$validate = new WHMCS_Validate();

if ($action == "send") {
	check_token();
	$validate->validate("required", "name", "contacterrorname");

	if ($validate->validate("required", "email", "clientareaerroremail")) {
		$validate->validate("email", "email", "clientareaerroremailinvalid");
	}

	$validate->validate("required", "subject", "contacterrorsubject");
	$validate->validate("required", "message", "contacterrormessage");
	$validate->validate("captcha", "code", "captchaverifyincorrect");

	if (!$validate->hasErrors()) {
		if ($CONFIG['LogoURL']) {
			$sendmessage = "<p><a href=\"" . $CONFIG['Domain'] . "\" target=\"_blank\"><img src=\"" . $CONFIG['LogoURL'] . "\" alt=\"" . $CONFIG['CompanyName'] . "\" border=\"0\"></a></p>";
		}

		$sendmessage .= "<font style=\"font-family:Verdana;font-size:11px\"><p>" . nl2br($message) . "</p>";
		$whmcs->load_class("phpmailer");
		$mail = new PHPMailer();
		$mail->From = $email;
		$mail->FromName = $name;
		$mail->Subject = "Contact Form: " . $subject;
		$mail->CharSet = $CONFIG['Charset'];

		if ($CONFIG['MailType'] == "mail") {
			$mail->Mailer = "mail";
		}
		else {
			if ($CONFIG['MailType'] == "smtp") {
				$mail->IsSMTP();
				$mail->Host = $CONFIG['SMTPHost'];
				$mail->Port = $CONFIG['SMTPPort'];
				$mail->Hostname = $_SERVER['SERVER_NAME'];

				if ($CONFIG['SMTPSSL']) {
					$mail->SMTPSecure = $CONFIG['SMTPSSL'];
				}


				if ($CONFIG['SMTPUsername']) {
					$mail->SMTPAuth = true;
					$mail->Username = $CONFIG['SMTPUsername'];
					$mail->Password = decrypt($CONFIG['SMTPPassword']);
				}

				$mail->Sender = $CONFIG['Email'];
				$mail->AddReplyTo($fromemail, $fromname);
			}
		}

		$message_text = str_replace("</p>", "\r\n\r\n", $sendmessage);
		$message_text = str_replace("<br>", "\r\n", $message_text);

		$message_text = str_replace("<br />", "\r\n", $message_text);

		$message_text = strip_tags($message_text);
		$mail->Body = $sendmessage;
		$mail->AltBody = $message_text;

		if (!$CONFIG['ContactFormTo']) {
			$contactformemail = $CONFIG['Email'];
		}
		else {
			$contactformemail = $CONFIG['ContactFormTo'];
		}

		$mail->AddAddress($contactformemail);

		if ($smtp_debug) {
			$mail->SMTPDebug = true;
		}

		$mail->Send();
		$mail->ClearAddresses();
		$sent = "true";
		$smarty->assign("sent", $sent);
	}
}

$smarty->assign("errormessage", $validate->getHTMLErrorOutput());
$smarty->assign("name", $name);
$smarty->assign("email", $email);
$smarty->assign("subject", $subject);
$smarty->assign("message", $message);
$smarty->assign("capatacha", $capatacha);
$smarty->assign("recapatchahtml", clientAreaReCaptchaHTML());
outputClientArea($templatefile);
?>