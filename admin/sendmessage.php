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
$aInt = new WHMCS_Admin("Mass Mail", false);
$aInt->title = $aInt->lang("sendmessage", "sendmessagetitle");
$aInt->sidebar = "clients";
$aInt->icon = "massmail";
ob_start();
$massmailquery = $query = $safeStoredQuery = $queryMadeFromEmailType = $token = null;
$userInput_massmailquery = $whmcs->get_req_var("massmailquery");
$queryMgr = new WHMCS_Token_Query("Admin.Massmail");

if (!$queryMgr->isValidTokenFormat($userInput_massmailquery)) {
	$userInput_massmailquery = null;
}


if ($action == "preview") {
	check_token("WHMCS.admin.default");
	$email_preview = true;
	delete_query("tblemailtemplates", array("name" => "Mass Mail Template"));
	insert_query("tblemailtemplates", array("type" => $type, "name" => "Mass Mail Template", "subject" => html_entity_decode($subject), "message" => html_entity_decode($messagetxt), "fromname" => "", "fromemail" => "", "copyto" => ""));

	if ($massmail && $safeStoredQuery = $queryMgr->getQuery($queryMgr->getTokenValue())) {
		$massmailquery = $safeStoredQuery;
		$result = full_query($massmailquery);
		$totalemails = mysql_num_rows($result);
		$totalsteps = ceil($totalemails / $massmailamount);
		$esttotaltime = ($totalsteps - ($step + 1)) * $massmailinterval;
		$result = full_query($massmailquery . " LIMIT 0,1");

		while ($data = mysql_fetch_array($result)) {
			sendMessage("Mass Mail Template", $data['id'], "", true, $_SESSION['massmail']['attachments']);
		}
	}
	else {
		if ($multiple) {
			sendMessage("Mass Mail Template", $selectedclients[0], "", true, $_SESSION['massmail']['attachments']);
		}
		else {
			sendMessage("Mass Mail Template", $id, "", true, $_SESSION['massmail']['attachments']);
		}
	}

	exit();
}


if ($action == "send") {
	check_token("WHMCS.admin.default");

	if (!$step) {
		if (!$message) {
			infoBox($aInt->lang("sendmessage", "validationerrortitle"), $aInt->lang("sendmessage", "validationerrormsg"));
		}


		if (!$subject) {
			infoBox($aInt->lang("sendmessage", "validationerrortitle"), $aInt->lang("sendmessage", "validationerrorsub"));
		}


		if (!$fromemail) {
			infoBox($aInt->lang("sendmessage", "validationerrortitle"), $aInt->lang("sendmessage", "validationerroremail"));
		}


		if (!$fromname) {
			infoBox($aInt->lang("sendmessage", "validationerrortitle"), $aInt->lang("sendmessage", "validationerrorname"));
		}
	}


	if ($infobox) {
		$showform = true;
	}
	else {
		$done = false;

		if ($type == "addon") {
			$type = "product";
		}


		if ($save == "on") {
			insert_query("tblemailtemplates", array("type" => $type, "name" => $savename, "subject" => html_entity_decode($subject), "message" => html_entity_decode($message), "fromname" => html_entity_decode($fromname), "fromemail" => $fromemail, "copyto" => $cc, "custom" => "1"));
			echo "<p>" . $aInt->lang("sendmessage", "msgsavedsuccess") . "</p>";
		}


		if (!$step) {
			delete_query("tblemailtemplates", array("name" => "Mass Mail Template"));
			insert_query("tblemailtemplates", array("type" => $type, "name" => "Mass Mail Template", "subject" => html_entity_decode($subject), "message" => html_entity_decode($message), "fromname" => html_entity_decode($fromname), "fromemail" => $fromemail, "copyto" => $cc));
			$_SESSION['massmail']['massmailamount'] = $massmailamount;
			$_SESSION['massmail']['massmailinterval'] = $massmailinterval;
			$_SESSION['massmail']['attachments'] = array();

			if (is_array($_FILES['attachments'])) {
				foreach ($_FILES['attachments']['name'] as $num => $displayname) {
					$filename = preg_replace("/[^a-zA-Z0-9-_. ]/", "", $displayname);

					if ($filename) {
						mt_srand(time());
						$rand = mt_rand(100000, 999999);
						$filename = "attach" . $rand . "_" . $filename;
						move_uploaded_file($_FILES['attachments']['tmp_name'][$num], $attachments_dir . $filename);
						$_SESSION['massmail']['attachments'][$attachments_dir . $filename] = $displayname;
						continue;
					}
				}
			}

			$step = 0;
		}


		if ($massmail && $safeStoredQuery = $queryMgr->getQuery($queryMgr->getTokenValue())) {
			$massmailquery = $safeStoredQuery;

			if ($emailoptout || WHMCS_Session::get("massmailemailoptout")) {
				WHMCS_Session::set("massmailemailoptout", true);
				$massmailquery .= " AND tblclients.emailoptout = '0'";
			}

			$sentids = $_SESSION['massmail']['sentids'];
			$massmailamount = (int)$_SESSION['massmail']['massmailamount'];
			$massmailinterval = (int)$_SESSION['massmail']['massmailinterval'];

			if (!$massmailamount) {
				$massmailamount = 25;
			}


			if (!$massmailinterval) {
				$massmailinterval = 30;
			}

			$result = full_query($massmailquery);
			$totalemails = mysql_num_rows($result);
			$totalsteps = ceil($totalemails / $massmailamount);
			$esttotaltime = ($totalsteps - ($step + 1)) * $massmailinterval;
			infoBox($aInt->lang("sendmessage", "massmailqueue"), $totalemails . $aInt->lang("sendmessage", "massmailspart1") . ($step + 1) . $aInt->lang("sendmessage", "massmailspart2") . $totalsteps . $aInt->lang("sendmessage", "massmailspart3") . $esttotaltime . $aInt->lang("sendmessage", "massmailspart4"));
			echo $infobox;
			$result = full_query($massmailquery . " LIMIT " . (int)$step * $massmailamount . "," . (int)$massmailamount);
			ob_start();

			while ($data = mysql_fetch_array($result)) {
				if ($sendforeach || (!$sendforeach && !in_array($data['userid'], $sentids))) {
					sendMessage("Mass Mail Template", $data['id'], "", true, $_SESSION['massmail']['attachments']);
					$sentids[] = $data['userid'];
				}

				echo "<li>" . $aInt->lang("sendmessage", "skippedduplicate") . $data['userid'] . "<br>";
			}

			$_SESSION['massmail']['sentids'] = $sentids;
			$content = ob_get_contents();
			ob_end_clean();
			echo "<ul>" . str_replace(array("<p>", "</p>"), array("<li>", "</li>"), $content) . "</ul>";
			$totalsent = $step * $massmailamount + $massmailamount;

			if ($totalemails <= $totalsent) {
				$done = true;
			}
			else {
				$massmaillink = "sendmessage.php?action=send&sendforeach=" . $sendforeach . "&massmail=1&step=" . ($step + 1) . generate_token("link");
				echo "<p><a href=\"" . $massmaillink . "\">" . $aInt->lang("sendmessage", "forcenextbatch") . ("</a></p><meta http-equiv=\"refresh\" content=\"30;url=" . $massmaillink . "\">");
			}
		}
		else {
			if ($multiple) {
				foreach ($selectedclients as $selectedclient) {
					$skipemail = false;

					if ($emailoptout) {
						if ($type == "general") {
							$skipemail = get_query_val("tblclients", "emailoptout", array("id" => $selectedclient));
						}
						else {
							if ($type == "product") {
								$skipemail = get_query_val("tblhosting", "emailoptout", array("tblhosting.id" => $selectedclient), "", "", "", "tblclients ON tblclients.id=tblhosting.userid");
							}
							else {
								if ($type == "domain") {
									$skipemail = get_query_val("tbldomains", "emailoptout", array("tbldomains.id" => $selectedclient), "", "", "", "tblclients ON tblclients.id=tbldomains.userid");
								}
								else {
									if ($type == "affiliate") {
										$skipemail = get_query_val("tblaffiliates", "emailoptout", array("tblaffiliates.id" => $selectedclient), "", "", "", "tblclients ON tblclients.id=tblaffiliates.clientid");
									}
								}
							}
						}
					}


					if ($skipemail) {
						echo "<p>Email Skipped for ID " . $selectedclient . " due to Marketing Email Opt-Out</p>";
					}
					else {
						sendMessage("Mass Mail Template", $selectedclient, "", true, $_SESSION['massmail']['attachments']);
					}

					$done = true;
				}
			}
			else {
				sendMessage("Mass Mail Template", $id, "", true, $_SESSION['massmail']['attachments']);
				$done = true;
			}
		}


		if ($done) {
			echo "<p><b>" . $aInt->lang("sendmessage", "sendingcompleted") . "</b></p>";
			delete_query("tblemailtemplates", array("name" => "Mass Mail Template"));
			foreach ($_SESSION['massmail']['attachments'] as $filename => $discard) {
				unlink($filename);
			}

			unset($_SESSION['massmail']);
		}
	}
}
else {
	$showform = true;
}


if ($showform) {
	if (!$infobox) {
		unset($_SESSION['massmail']);
	}

	$todata = array();
	$query = "";

	if (!$type) {
		$type = "general";
	}

	$queryMadeFromEmailType = "";

	if ($type == "massmail") {
		$clientstatus = db_build_in_array($clientstatus);
		$clientgroup = db_build_in_array($clientgroup);
		$clientlanguage = db_build_in_array($clientlanguage, true);
		$productids = db_build_in_array($productids);
		$productstatus = db_build_in_array($productstatus);
		$server = db_build_in_array($server);
		$addonids = db_build_in_array($addonids);
		$addonstatus = db_build_in_array($addonstatus);
		$domainstatus = db_build_in_array($domainstatus);

		if ($emailtype == "General") {
			$type = "general";
			$query = "SELECT id,id AS userid,tblclients.firstname,tblclients.lastname,tblclients.email FROM tblclients WHERE id!=''";

			if ($clientstatus) {
				$query .= " AND tblclients.status IN (" . $clientstatus . ")";
			}


			if ($clientgroup) {
				$query .= " AND tblclients.groupid IN (" . $clientgroup . ")";
			}


			if ($clientlanguage) {
				$query .= " AND tblclients.language IN (" . $clientlanguage . ")";
			}


			if (is_array($customfield)) {
				foreach ($customfield as $k => $v) {

					if ($v) {
						if ($v == "cfon") {
							$v = "on";
						}


						if ($v == "cfoff") {
							$query .= " AND ((SELECT value FROM tblcustomfieldsvalues WHERE fieldid='" . db_escape_string($k) . "' AND relid=tblclients.id LIMIT 1)='' OR (SELECT value FROM tblcustomfieldsvalues WHERE fieldid='" . db_escape_string($k) . "' AND relid=tblclients.id LIMIT 1) IS NULL)";
							continue;
						}

						$query .= " AND (SELECT value FROM tblcustomfieldsvalues WHERE fieldid='" . db_escape_string($k) . "' AND relid=tblclients.id LIMIT 1)='" . db_escape_string($v) . "'";
						continue;
					}
				}
			}
		}
		else {
			if ($emailtype == "Product/Service") {
				$type = "product";
				$query = "SELECT tblhosting.id,tblhosting.userid,tblhosting.domain,tblclients.firstname,tblclients.lastname,tblclients.email FROM tblhosting INNER JOIN tblclients ON tblclients.id=tblhosting.userid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid WHERE tblhosting.id!=''";

				if ($productids) {
					$query .= " AND tblproducts.id IN (" . $productids . ")";
				}


				if ($productstatus) {
					$query .= " AND tblhosting.domainstatus IN (" . $productstatus . ")";
				}


				if ($server) {
					$query .= " AND tblhosting.server IN (" . $server . ")";
				}


				if ($clientstatus) {
					$query .= " AND tblclients.status IN (" . $clientstatus . ")";
				}


				if ($clientgroup) {
					$query .= " AND tblclients.groupid IN (" . $clientgroup . ")";
				}


				if ($clientlanguage) {
					$query .= " AND tblclients.language IN (" . $clientlanguage . ")";
				}


				if (is_array($customfield)) {
					foreach ($customfield as $k => $v) {

						if ($v) {
							$query .= " AND (SELECT value FROM tblcustomfieldsvalues WHERE fieldid='" . db_escape_string($k) . "' AND relid=tblclients.id LIMIT 1)='" . db_escape_string($v) . "'";
							continue;
						}
					}
				}
			}
			else {
				if ($emailtype == "Addon") {
					$type = "addon";
					$query = "SELECT tblhosting.id,tblhosting.userid,tblhosting.domain,tblclients.firstname,tblclients.lastname,tblclients.email FROM tblhosting INNER JOIN tblclients ON tblclients.id=tblhosting.userid INNER JOIN tblhostingaddons ON tblhostingaddons.hostingid = tblhosting.id WHERE tblhostingaddons.id!=''";

					if ($addonids) {
						$query .= " AND tblhostingaddons.addonid IN (" . $addonids . ")";
					}


					if ($addonstatus) {
						$query .= " AND tblhostingaddons.status IN (" . $addonstatus . ")";
					}


					if ($clientstatus) {
						$query .= " AND tblclients.status IN (" . $clientstatus . ")";
					}


					if ($clientgroup) {
						$query .= " AND tblclients.groupid IN (" . $clientgroup . ")";
					}


					if ($clientlanguage) {
						$query .= " AND tblclients.language IN (" . $clientlanguage . ")";
					}


					if (is_array($customfield)) {
						foreach ($customfield as $k => $v) {

							if ($v) {
								$query .= " AND (SELECT value FROM tblcustomfieldsvalues WHERE fieldid='" . db_escape_string($k) . "' AND relid=tblclients.id LIMIT 1)='" . db_escape_string($v) . "'";
								continue;
							}
						}
					}
				}
				else {
					if ($emailtype == "Domain") {
						$type = "domain";
						$query = "SELECT tbldomains.id,tbldomains.userid,tbldomains.domain,tblclients.firstname,tblclients.lastname,tblclients.email FROM tbldomains INNER JOIN tblclients ON tblclients.id=tbldomains.userid WHERE tbldomains.id!=''";

						if ($domainstatus) {
							$query .= " AND tbldomains.status IN (" . $domainstatus . ")";
						}


						if ($clientstatus) {
							$query .= " AND tblclients.status IN (" . $clientstatus . ")";
						}


						if ($clientgroup) {
							$query .= " AND tblclients.groupid IN (" . $clientgroup . ")";
						}


						if ($clientlanguage) {
							$query .= " AND tblclients.language IN (" . $clientlanguage . ")";
						}


						if (is_array($customfield)) {
							foreach ($customfield as $k => $v) {

								if ($v) {
									$query .= " AND (SELECT value FROM tblcustomfieldsvalues WHERE fieldid='" . db_escape_string($k) . "' AND relid=tblclients.id LIMIT 1)='" . db_escape_string($v) . "'";
									continue;
								}
							}
						}
					}
				}
			}
		}

		$queryMadeFromEmailType = $query;
	}


	if ($queryMadeFromEmailType || $userInput_massmailquery) {
		if ($queryMadeFromEmailType) {
			$massmailquery = $queryMadeFromEmailType;
		}
		else {
			if (!$queryMadeFromEmailType && $queryMgr->isValidTokenFormat($userInput_massmailquery)) {
				$massmailquery = $queryMgr->getQuery($userInput_massmailquery);
			}
			else {
				$massmailquery = "";
			}
		}

		$useridsdone = array();
		$result = full_query($massmailquery);

		while ($data = mysql_fetch_array($result)) {
			if ($sendforeach || (!$sendforeach && !in_array($data['userid'], $useridsdone))) {
				$temptodata = "" . $data['firstname'] . " " . $data['lastname'];

				if ($data['domain']) {
					$temptodata .= " - " . $data['domain'];
				}

				$temptodata .= " &lt;" . $data['email'] . "&gt;";
				$todata[] = $temptodata;
				$useridsdone[] = $data['userid'];
			}
		}
	}
	else {
		if ($multiple) {
			if ($type == "general") {
				foreach ($selectedclients as $id) {
					$result = select_query("tblclients", "", array("id" => $id));
					$data = mysql_fetch_array($result);
					$todata[] = "" . $data['firstname'] . " " . $data['lastname'] . " &lt;" . $data['email'] . "&gt;";
				}
			}
			else {
				if ($type == "product") {
					foreach ($selectedclients as $id) {
						$result = select_query("tblhosting", "tblclients.firstname,tblclients.lastname,tblclients.email,tblhosting.domain", array("tblhosting.id" => $id), "", "", "", "tblclients ON tblclients.id=tblhosting.userid");
						$data = mysql_fetch_array($result);
						$todata[] = "" . $data['firstname'] . " " . $data['lastname'] . " - " . $data['domain'] . " &lt;" . $data['email'] . "&gt;";
					}
				}
				else {
					if ($type == "domain") {
						foreach ($selectedclients as $id) {
							$result = select_query("tbldomains", "tblclients.firstname,tblclients.lastname,tblclients.email,tbldomains.domain", array("tbldomains.id" => $id), "", "", "", "tblclients ON tblclients.id=tbldomains.userid");
							$data = mysql_fetch_array($result);
							$todata[] = "" . $data['firstname'] . " " . $data['lastname'] . " - " . $data['domain'] . " &lt;" . $data['email'] . "&gt;";
						}
					}
					else {
						if ($type == "affiliate") {
							foreach ($selectedclients as $id) {
								$result = select_query("tblaffiliates", "tblclients.firstname,tblclients.lastname,tblclients.email", array("tblaffiliates.id" => $id), "", "", "", "tblclients ON tblclients.id=tblaffiliates.clientid");
								$data = mysql_fetch_array($result);
								$todata[] = "" . $data['firstname'] . " " . $data['lastname'] . " - " . $data['domain'] . " &lt;" . $data['email'] . "&gt;";
							}
						}
					}
				}
			}
		}
		else {
			if ($resend) {
				$result = select_query("tblemails", "", array("id" => $emailid));
				$data = mysql_fetch_array($result);
				$id = $data['userid'];
				$subject = $data['subject'];
				$message = $data['message'];
				$message = str_replace("<p><a href=\"" . $CONFIG['Domain'] . "\" target=\"_blank\"><img src=\"" . $CONFIG['LogoURL'] . "\" alt=\"" . $CONFIG['CompanyName'] . "\" border=\"0\"></a></p>", "", $message);
				$message = str_replace("<p><a href=\"" . $CONFIG['Domain'] . "\" target=\"_blank\"><img src=\"" . $CONFIG['LogoURL'] . "\" alt=\"" . $CONFIG['CompanyName'] . "\" border=\"0\" /></a></p>", "", $message);
				$message = str_replace(html_entity_decode($CONFIG['EmailGlobalHeader']), "", $message);
				$message = str_replace(html_entity_decode($CONFIG['EmailGlobalFooter']), "", $message);
				$styleend = strpos($message, "</style>") + 8;
				$message = trim(substr($message, $styleend));
				$type = "general";
			}


			if ($type == "general") {
				$result = select_query("tblclients", "", array("id" => $id));
				$data = mysql_fetch_array($result);

				if ($data['email']) {
					$todata[] = "" . $data['firstname'] . " " . $data['lastname'] . " &lt;" . $data['email'] . "&gt;";
				}
			}
			else {
				if ($type == "product") {
					$query = "SELECT tblclients.id,tblclients.firstname,tblclients.lastname,tblclients.email,tblhosting.domain FROM tblhosting INNER JOIN tblclients ON tblclients.id=tblhosting.userid WHERE tblhosting.id='" . mysql_real_escape_string($id) . "'";
					$result = full_query($query);
					$data = mysql_fetch_array($result);

					if ($data['email']) {
						$todata[] = "" . $data['firstname'] . " " . $data['lastname'] . " - " . $data['domain'] . " &lt;" . $data['email'] . "&gt;";
					}
				}
				else {
					if ($type == "domain") {
						$query = "SELECT tblclients.id,tblclients.firstname,tblclients.lastname,tblclients.email,tbldomains.domain FROM tbldomains INNER JOIN tblclients ON tblclients.id=tbldomains.userid WHERE tbldomains.id='" . mysql_real_escape_string($id) . "'";
						$result = full_query($query);
						$data = mysql_fetch_array($result);

						if ($data['email']) {
							$todata[] = "" . $data['firstname'] . " " . $data['lastname'] . " - " . $data['domain'] . " &lt;" . $data['email'] . "&gt;";
						}
					}
				}
			}
		}
	}


	if (!$todata) {
		infoBox($aInt->lang("sendmessage", "noreceiptients"), $aInt->lang("sendmessage", "noreceiptientsdesc"));
	}

	echo $infobox;

	if ($sub == "loadmessage") {
		$language = (((!$massmailquery && !$multiple) && (int)$data['id']) ? get_query_val("tblclients", "language", array("id" => $data['id'])) : "");
		$result = select_query("tblemailtemplates", "", array("name" => $messagename, "language" => $language));
		$data = mysql_fetch_array($result);

		if (!$data['id']) {
			$result = select_query("tblemailtemplates", "", array("name" => $messagename));
			$data = mysql_fetch_array($result);
		}

		$subject = $data['subject'];
		$message = $data['message'];
		$fromname = $data['fromname'];
		$fromemail = $data['fromemail'];
		$plaintext = $data['plaintext'];

		if ($plaintext) {
			$message = nl2br($message);
		}
	}

	echo "
<form method=\"post\" action=\"";
	echo $PHP_SELF;
	echo "\" name=\"frmmessage\"
    id=\"sendmsgfrm\" enctype=\"multipart/form-data\">
    <input type=\"hidden\" name=\"action\" value=\"send\" /> <input type=\"hidden\"
        name=\"type\" value=\"";
	echo $type;
	echo "\" />
";
	$token = $queryMgr->generateToken();
	$queryMgr->setQuery($token, "");
	$_SESSION['massmail']['sentids'] = array();
	WHMCS_Session::set("massmailemailoptout", false);

	if ($massmailquery) {
		if ($queryMgr->isValidTokenFormat($massmailquery)) {
			$queryToStore = $queryMgr->getQuery($massmailquery);
		}
		else {
			$queryToStore = $massmailquery;
		}

		$queryMgr->setQuery($token, $queryToStore);
		echo "<input type=\"hidden\" name=\"massmail\" value=\"true\" /><input type=\"hidden\" name=\"sendforeach\" value=\"" . $sendforeach . "\" />";
	}
	else {
		if ($multiple) {
			echo "<input type=\"hidden\" name=\"multiple\" value=\"true\" />";
			foreach ($selectedclients as $selectedclient) {
				echo "<input type=\"hidden\" name=\"selectedclients[]\" value=\"" . $selectedclient . "\" />";
			}
		}
		else {
			echo "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\" />";
		}
	}

	echo "
<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\"
        cellpadding=\"3\">
        <tr>
            <td width=\"140\" class=\"fieldlabel\">";
	echo $aInt->lang("emails", "from");
	echo "</td>
            <td class=\"fieldarea\"><input type=\"text\" name=\"fromname\" size=\"25\"
                value=\"";

	if (!$fromname) {
		echo $CONFIG['CompanyName'];
	}
	else {
		echo $fromname;
	}

	echo "\">
                <input type=\"text\" name=\"fromemail\" size=\"60\"
                value=\"";

	if (!$fromemail) {
		echo $CONFIG['Email'];
	}
	else {
		echo $fromemail;
	}

	echo "\"></td>
        </tr>
        <tr>
            <td class=\"fieldlabel\">";
	echo $aInt->lang("emails", "recipients");
	echo "</td>
            <td class=\"fieldarea\"><table cellspacing=\"0\" cellpadding=\"0\">
                    <tr>
                        <td>";
	echo "<select size=\"4\" style=\"width:450px;\">";
	foreach ($todata as $to) {
		echo "<option>" . $to . "</option>";
	}

	echo "</select>";
	echo "</td>
                        <td> &nbsp; ";
	echo $aInt->lang("sendmessage", "emailsentindividually1");
	echo "<br /> &nbsp; ";
	echo $aInt->lang("sendmessage", "emailsentindividually2");
	echo "</td>

                </table></td>
            </td>
        </tr>
        <tr>
            <td class=\"fieldlabel\">CC</td>
            <td class=\"fieldarea\"><input type=\"text\" name=\"cc\" size=\"80\" value=\"\"> ";
	echo $aInt->lang("sendmessage", "commaseparateemails");
	echo "</td>
        </tr>
        <tr>
            <td class=\"fieldlabel\">Subject</td>
            <td class=\"fieldarea\"><input type=\"text\" name=\"subject\" size=\"90\"
                value=\"";
	echo $subject;
	echo "\" id=\"subject\"></td>
        </tr>
    </table>

    <br>

    ";
	echo "<s";
	echo "cript langauge=\"javascript\">
frmmessage.subject.select();
</script>

    <textarea name=\"message\" id=\"email_msg1\" rows=\"25\" style=\"width: 100%\"
        class=\"tinymce\">";
	echo $message;
	echo "</textarea>

    <br />

    <table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\"
        cellpadding=\"3\">
        <tr>
            <td width=\"140\" class=\"fieldlabel\">";
	echo $aInt->lang("support", "attachments");
	echo "</td>
            <td class=\"fieldarea\"><div style=\"float: right;\">
                    <input type=\"button\"
                        value=\"";
	echo $aInt->lang("emailtpls", "rteditor");
	echo "\"
                        class=\"btn\" onclick=\"toggleEditor()\" />
                </div>
                <input type=\"file\" name=\"attachments[]\" style=\"width: 60%;\" /> <a
                href=\"#\" id=\"addfileupload\"><img src=\"images/icons/add.png\"
                    align=\"absmiddle\" border=\"0\" /> ";
	echo $aInt->lang("support", "addmore");
	echo "</a><br />
            <div id=\"fileuploads\"></div></td>
        </tr>
";

	if ($massmailquery || $multiple) {
		echo "<tr>
            <td class=\"fieldlabel\">";
		echo $aInt->lang("sendmessage", "marketingemail");
		echo "</td>
            <td class=\"fieldarea\"><label><input type=\"checkbox\" id=\"emailoptout\"
                    name=\"emailoptout\"> ";
		echo $aInt->lang("sendmessage", "dontsendemailunsubscribe");
		echo "</label></td>
        </tr>
";
	}


	if (checkPermission("Create/Edit Email Templates", true)) {
		echo "<tr>
            <td class=\"fieldlabel\">";
		echo $aInt->lang("sendmessage", "savemesasge");
		echo "</td>
            <td class=\"fieldarea\"><label><input type=\"checkbox\" name=\"save\"> ";
		echo $aInt->lang("sendmessage", "entersavename");
		echo ":</label>
                <input type=\"text\" name=\"savename\" size=\"30\"></td>
        </tr>";
	}


	if ($massmailquery) {
		echo "<tr>
            <td class=\"fieldlabel\">";
		echo $aInt->lang("sendmessage", "massmailsettings");
		echo "</td>
            <td class=\"fieldarea\">";
		echo $aInt->lang("sendmessage", "massmailsetting1");
		echo " <input
                type=\"text\" name=\"massmailamount\" size=\"5\" value=\"25\" /> ";
		echo $aInt->lang("sendmessage", "massmailsetting2");
		echo " <input
                type=\"text\" name=\"massmailinterval\" size=\"5\" value=\"30\" /> ";
		echo $aInt->lang("sendmessage", "massmailsetting3");
		echo "</td>
        </tr>";
	}

	echo "</table>

    <p align=\"center\">
        <input type=\"button\"
            value=\"";
	echo $aInt->lang("sendmessage", "preview");
	echo "\"
            onclick=\"previewMsg()\" class=\"btn\" /> <input type=\"submit\"
            value=\"";
	echo $aInt->lang("global", "sendmessage");
	echo " &raquo;\"
            class=\"btn-primary\" />
    </p>

</form>

";
	$aInt->richTextEditor();
	echo "<div id=\"emailoptoutinfo\">";
	infoBox($aInt->lang("sendmessage", "marketingemail"), $aInt->lang("sendmessage", "marketingemaildesc"));
	echo $infobox;
	echo "</div>";
	$i = 1;
	include "mergefields.php";
	echo "
<form method=\"post\" action=\"";
	echo $_SERVER['PHP_SELF'];
	echo "\">
    <input type=\"hidden\" name=\"sub\" value=\"loadmessage\"> <input
        type=\"hidden\" name=\"type\" value=\"";
	echo $type;
	echo "\">
";

	if ($massmailquery) {
		if ($queryMgr->isValidTokenFormat($massmailquery)) {
			$queryToStore = $queryMgr->getQuery($massmailquery);
		}
		else {
			$queryToStore = $massmailquery;
		}

		$token = $queryMgr->generateToken();
		$queryMgr->setQuery($token, $queryToStore);
		echo "<input type=\"hidden\" name=\"massmailquery\" value=\"" . $token . "\">";

		if ($sendforeach) {
			echo "<input type=\"hidden\" name=\"sendforeach\" value=\"" . $sendforeach . "\">";
		}
	}
	else {
		if ($multiple) {
			echo "<input type=\"hidden\" name=\"multiple\" value=\"true\">";
			foreach ($selectedclients as $selectedclient) {
				echo "<input type=\"hidden\" name=\"selectedclients[]\" value=\"" . $selectedclient . "\">";
			}
		}
		else {
			echo "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">";
		}
	}

	echo "<div class=\"contentbox\">
        <b>";
	echo $aInt->lang("sendmessage", "loadsavedmsg");
	echo ":</b> ";
	echo "<s";
	echo "elect
            name=\"messagename\"><option value=\"\">";
	echo $aInt->lang("sendmessage", "choose");
	echo "...";
	$query = "SELECT * FROM tblemailtemplates WHERE type='general' AND language='' ORDER BY custom,name ASC";
	$result = full_query($query);

	while ($data = mysql_fetch_array($result)) {
		$messid = $data['id'];
		$messagename = $data['name'];
		echo "<option style=\"background-color:#ffffff\">" . $messagename . "</option>";
	}


	if ($type != "general") {
		$result = select_query("tblemailtemplates", "", array("type" => $type, "language" => ""), "custom` ASC,`name", "ASC");

		while ($data = mysql_fetch_array($result)) {
			$messid = $data['id'];
			$messagename = $data['name'];
			echo "<option";

			if ($custom == "") {
				echo " style=\"background-color:#efefef\"";
			}

			echo ">" . $messagename . "</option>";
		}
	}

	echo "</select> <input type=\"submit\"
            value=\"";
	echo $aInt->lang("home", "load");
	echo "\">
    </div>
</form>

";
	echo $aInt->jqueryDialog("previewwnd", $aInt->lang("sendmessage", "preview"), "<div id=\"previewwndcontent\">" . $aInt->lang("global", "loading") . "</div>", array($aInt->lang("global", "ok") => ""), "450", "700", "");
	$jquerycode .= "$(\"#addfileupload\").click(function () {
    $(\"#fileuploads\").append(\"<input type=\\\"file\\\" name=\\\"attachments[]\\\" style=\\\"width:70%;\\\" /><br />\");
    return false;
});
$(\"#emailoptoutinfo\").hide();
$(\"#emailoptout\").click(function(){
    if (this.checked) {
        $(\"#emailoptoutinfo\").slideDown(\"slow\");
    } else {
        $(\"#emailoptoutinfo\").slideUp(\"slow\");
    }
});";
	$jscode = "function previewMsg() {
    if ($(\"#email_msg1\").tinymce().isHidden()) {
        alert(\"Cannot preview message while the rich-text editor is disabled - please re-enable and then try again\");
    } else {
        $(\"#previewwnd\").dialog(\"open\");
        jQuery.post(\"sendmessage.php\", $(\"#sendmsgfrm\").serialize()+\"&action=preview&messagetxt=\"+$(\"#email_msg1\").html(),
        function(data){
            if (data) {
                jQuery(\"#previewwndcontent\").html(data);}
            else {
                jQuery(\"#previewwndcontent\").html(\"Syntax Error - Please check your email message for invalid template syntax or missing closing tags\");
            }
        });
        return false;
    }
}";
}

$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();
?>