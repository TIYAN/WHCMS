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

function downloadLogin() {
	global $whmcs;
	global $CONFIG;
	global $_LANG;
	global $smarty;
	global $type;
	global $id;

	$pagetitle = $_LANG['downloadstitle'];
	$breadcrumbnav = "<a href=\"" . $CONFIG['SystemURL'] . "/index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"" . $CONFIG['SystemURL'] . "/downloads.php\">" . $_LANG['downloadstitle'] . "</a>";
	initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);
	$goto = "download";
	require "login.php";
}

define("CLIENTAREA", true);
require "init.php";
$type = $whmcs->get_req_var("type");
$viewpdf = $whmcs->get_req_var("viewpdf");
$i = (int)$whmcs->get_req_var("i");
$id = (int)$whmcs->get_req_var("id");
$folder_path = $file_name = $display_name = "";
$allowedtodownload = "";

if ($type == "i") {
	$result = select_query("tblinvoices", "", array("id" => $id));
	$data = mysql_fetch_array($result);

	if (!$data['id']) {
		exit("Invalid Access Attempt");
	}

	$invoiceid = $data['id'];
	$invoicenum = $data['invoicenum'];
	$userid = $data['userid'];

	if (!$invoiceid) {
		redir("", "clientarea.php");
	}


	if (!isset($_SESSION['adminid']) && $_SESSION['uid'] != $userid) {
		downloadLogin();
	}


	if (!$invoicenum) {
		$invoicenum = $invoiceid;
	}

	require "includes/invoicefunctions.php";
	$pdfdata = pdfInvoice($id);
	header("Pragma: public");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0, private");
	header("Cache-Control: private", false);
	header("Content-Type: application/pdf");
	header("Content-Disposition: " . ($viewpdf ? "inline" : "attachment") . "; filename=\"" . $_LANG['invoicefilename'] . $invoicenum . ".pdf\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: " . strlen($pdfdata));
	echo $pdfdata;
	exit();
	return 1;
}


if ($type == "a") {
	$result = select_query("tbltickets", "userid,attachment", array("id" => $id));
	$data = mysql_fetch_array($result);
	$userid = $data['userid'];
	$attachments = $data['attachment'];
	$folder_path = $attachments_dir;
	$files = explode("|", $attachments);
	$file_name = $files[$i];
	$display_name = substr($file_name, 7);

	if ($userid && ($userid != $_SESSION['uid'] && !$_SESSION['adminid'])) {
		downloadLogin();
	}
}
else {
	if ($type == "ar") {
		$result = select_query("tblticketreplies", "userid,attachment", array("id" => $id));
		$data = mysql_fetch_array($result);
		$userid = $data['userid'];
		$attachments = $data['attachment'];
		$folder_path = $attachments_dir;
		$files = explode("|", $attachments);
		$file_name = $files[$i];
		$display_name = substr($file_name, 7);

		if ($userid && ($userid != $_SESSION['uid'] && !$_SESSION['adminid'])) {
			downloadLogin();
		}
	}
	else {
		if ($type == "d") {
			$result = select_query("tbldownloads", "", array("id" => $id));
			$data = mysql_fetch_array($result);
			$filename = $data['location'];
			$clientsonly = $data['clientsonly'];
			$productdownload = $data['productdownload'];

			if ($productdownload) {
				if (!$_SESSION['uid']) {
					downloadLogin();
				}

				$downloads = array();

				if ($serviceid) {
					$where = array("tblhosting.id" => $serviceid, "userid" => $_SESSION['uid'], "tblhosting.domainstatus" => "Active");
				}
				else {
					$where = array("userid" => $_SESSION['uid'], "tblhosting.domainstatus" => "Active");
				}

				$result = select_query("tblhosting", "DISTINCT tblproducts.id,tblproducts.downloads, tblproducts.servertype, tblproducts.configoption7", $where, "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");

				while ($data = mysql_fetch_array($result)) {
					$productdownloads = $data['downloads'];
					$productdownloads = unserialize($productdownloads);

					if (is_array($productdownloads)) {
						if (in_array($id, $productdownloads)) {
							if (($data['servertype'] == "licensing" && ($data['configoption7'] == "" || ($serviceid && $data['configoption7'] != ""))) || $data['servertype'] != "licensing") {
								$downloads = array_merge($downloads, $productdownloads);
							}

							echo $_LANG['dlinvalidlink'];
							exit();
						}
					}
				}


				if ($serviceid) {
					$where = array("tblhostingaddons.hostingid" => $serviceid, "tblhosting.userid" => $_SESSION['uid'], "tblhostingaddons.status" => "Active");
				}
				else {
					$where = array("tblhosting.userid" => $_SESSION['uid'], "tblhostingaddons.status" => "Active");
				}

				$result = select_query("tblhostingaddons", "DISTINCT tbladdons.id,tbladdons.downloads", $where, "", "", "", "tbladdons ON tbladdons.id=tblhostingaddons.addonid INNER JOIN tblhosting ON tblhosting.id=tblhostingaddons.hostingid");

				while ($data = mysql_fetch_array($result)) {
					$addondownloads = $data['downloads'];
					$addondownloads = explode(",", $addondownloads);
					$downloads = array_merge($downloads, $addondownloads);
				}


				if (in_array($id, $downloads)) {
					$allowedtodownload = true;
				}


				if (!$allowedtodownload) {
					$tplfile = ROOTDIR . "/templates/" . $whmcs->get_sys_tpl_name() . "/downloaddenied.tpl";

					if (file_exists($tplfile)) {
						$pagetitle = $_LANG['downloadstitle'];
						$breadcrumbnav = "<a href=\"" . $CONFIG['SystemURL'] . "/index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"" . $CONFIG['SystemURL'] . "/downloads.php\">" . $_LANG['downloadstitle'] . "</a>";
						initialiseClientArea($pagetitle, "", $breadcrumbnav);
						$result = select_query("tblproducts", "id,name,downloads", array("downloads" => array("sqltype" => "NEQ", "value" => "")));

						while ($data = mysql_fetch_array($result)) {
							$downloads = $data['downloads'];
							$downloads = unserialize($downloads);

							if (in_array($id, $downloads)) {
								$smartyvalues['pid'] = $data['id'];
								$smartyvalues['prodname'] = $data['name'];
								break;
							}
						}

						$result = select_query("tbladdons", "id,name,downloads", array("downloads" => array("sqltype" => "NEQ", "value" => "")));

						while ($data = mysql_fetch_array($result)) {
							$downloads = $data['downloads'];
							$downloads = explode(",", $downloads);

							if (in_array($id, $downloads)) {
								$smartyvalues['aid'] = $data['id'];
								$smartyvalues['addonname'] = $data['name'];
								break;
							}
						}

						outputClientArea("downloaddenied");
					}
					else {
						echo $_LANG['downloadpurchaserequired'];
					}

					exit();
				}

				$result = select_query("tblproducts", "tblproducts.configoption7", array("tblhosting.id" => $serviceid, "tblproducts.servertype" => "licensing"), "", "", "", "tblhosting ON tblhosting.packageid=tblproducts.id");
				$data = mysql_fetch_array($result);
				$supportpackage = $data['configoption7'];
				$addonid = explode("|", $supportpackage);
				$addonid = $addonid[0];

				if ($addonid) {
					$result = select_query("tbladdons", "name", array("id" => $addonid));
					$data = mysql_fetch_array($result);
					$addonname = $data['name'];
					$where = "tblhosting.userid='" . (int)$_SESSION['uid'] . "' AND tblhostingaddons.status='Active' AND (tblhostingaddons.name='" . mysql_real_escape_string($addonname) . "' OR tblhostingaddons.addonid='" . (int)$addonid . "')";

					if ($pid) {
						$where .= " AND tblhosting.id='" . (int)$pid . "'";
					}

					$result = select_query("tblhostingaddons", "COUNT(*)", $where, "", "", "", "tblhosting ON tblhosting.id=tblhostingaddons.hostingid");
					$data = mysql_fetch_array($result);
					$supportpackageactive = $data[0];

					if (!$supportpackageactive) {
						$formposturl = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
						echo "<div align=\"center\">
<br />
<b>Your Support & Updates period for this license has expired</b><br />
You will need to renew your support & updates before you can download the latest files<br />
<br />
<form action=\"" . $formposturl . "/cart.php?a=add\" method=\"post\">
<input type=\"hidden\" name=\"productid\" value=\"" . $serviceid . "\" />
<input type=\"hidden\" name=\"aid\" value=\"" . $addonid . "\" />
<input type=\"submit\" value=\"Click Here to Renew &raquo;\" />
</form>
</div>";
						exit();
					}
				}
			}


			if ($clientsonly && !$_SESSION['uid']) {
				downloadLogin();
			}

			update_query("tbldownloads", array("downloads" => "+1"), array("id" => $id));

			if ((substr($filename, 0, 7) == "http://" || substr($filename, 0, 8) == "https://") || substr($filename, 0, 6) == "ftp://") {
				header("Location: " . $filename);
				exit();
			}
			else {
				$folder_path = $downloads_dir;
				$file_name = $filename;
				$display_name = $filename;
			}
		}
		else {
			if ($type == "f") {
				$result = select_query("tblclientsfiles", "userid,filename,adminonly", array("id" => $id));
				$data = mysql_fetch_array($result);
				$userid = $data['userid'];
				$file_name = $data['filename'];
				$adminonly = $data['adminonly'];
				$folder_path = $attachments_dir;
				$display_name = substr($file_name, 11);

				if ($userid != $_SESSION['uid'] && !$_SESSION['adminid']) {
					downloadLogin();
				}


				if (!$_SESSION['adminid'] && $adminonly) {
					exit("Permission Denied");
				}
			}
			else {
				if ($type == "q") {
					if (!$_SESSION['uid'] && !$_SESSION['adminid']) {
						downloadLogin();
					}

					$result = select_query("tblquotes", "id,userid", array("id" => $id));
					$data = mysql_fetch_array($result);
					$id = $data['id'];
					$userid = $data['userid'];

					if ($userid != $_SESSION['uid'] && !$_SESSION['adminid']) {
						exit("Permission Denied");
					}

					require ROOTDIR . "/includes/clientfunctions.php";
					require ROOTDIR . "/includes/invoicefunctions.php";
					require ROOTDIR . "/includes/quotefunctions.php";
					$pdfdata = genQuotePDF($id);
					header("Pragma: public");
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
					header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0, private");
					header("Cache-Control: private", false);
					header("Content-Type: application/pdf");
					header("Content-Disposition: " . ($viewpdf ? "inline" : "attachment") . "; filename=\"" . $_LANG['quotefilename'] . $id . ".pdf\"");
					header("Content-Transfer-Encoding: binary");
					echo $pdfdata;
					exit();
				}
			}
		}
	}
}


if (!trim($folder_path) || !trim($file_name)) {
	redir("", "index.php");
}

$folder_path_real = realpath($folder_path);
$file_path = $folder_path . $file_name;
$file_path_real = realpath($file_path);

if ($file_path_real === false || strpos($file_path_real, $folder_path_real) !== 0) {
	exit("File not found. Please contact support.");
}

run_hook("FileDownload", array());
header("Pragma: public");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, private");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . $display_name . "\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($file_path_real));
readfile($file_path_real);
?>