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

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!$vars['clientenable']) {
	redir();
}

$tplvars = array();
$tplvars['_lang'] = $vars['_lang'];
$tplvars['features'] = $features = explode(",", $vars['clientfeatures']);
$a = $_GET['a'];

if (!$a) {
	$tplfile = "templates/clienthome";
	$result = select_query("mod_project", "COUNT(*)", array("userid" => $_SESSION['uid']));
	$data = mysql_fetch_array($result);
	$numitems = $data[0];
	list($orderby, $sort, $limit) = clientAreaTableInit("projects", "lastmodified", "DESC", $numitems);
	$projects = array();
	$result = select_query("mod_project", "", array("userid" => $_SESSION['uid']), $orderby, $sort, $limit);

	while ($data = mysql_fetch_array($result)) {
		$projects[] = array("id" => $data['id'], "title" => $data['title'], "adminid" => $data['adminid'], "adminname" => get_query_val("tbladmins", "CONCAT(firstname,' ',lastname)", array("id" => $data['adminid'])), "created" => fromMySQLDate($data['created'], 0, 1), "duedate" => fromMySQLDate($data['duedate'], 0, 1), "lastmodified" => fromMySQLDate($data['lastmodified'], 0, 1), "status" => $data['status']);
	}

	$tplvars['projects'] = $projects;
	$tplvars['orderby'] = $orderby;
	$tplvars['sort'] = strtolower($sort);
	$tplvars = array_merge($tplvars, clientAreaTablePageNav($numitems));
	return 1;
}


if ($a == "view") {
	$tplfile = "templates/clientview";
	$result = select_query("mod_project", "", array("userid" => $_SESSION['uid'], "id" => $_REQUEST['id']));
	$data = mysql_fetch_array($result);
	$projectid = $data['id'];

	if (!$projectid) {
		exit("Access Denied");
	}


	if (in_array("addtasks", $features) && trim($_POST['newtask'])) {
		insert_query("mod_projecttasks", array("projectid" => $projectid, "task" => trim($_POST['newtask']), "created" => "now()", "order" => get_query_val("mod_projecttasks", "`order`", array("projectid" => $projectid), "order", "DESC") + 1));
		redir("m=project_management&a=view&id=" . $projectid);
	}


	if (in_array("files", $features) && $_POST['upload']) {
		global $attachments_dir;

		$projectsdir2 = $attachments_dir . "projects/";
		$projectsdir = $attachments_dir . "projects/" . $projectid . "/";

		if (!is_dir($projectsdir2)) {
			mkdir($projectsdir2);
		}


		if (!is_dir($projectsdir)) {
			mkdir($projectsdir);
		}

		$attachments = explode(",", $data['attachments']);

		if (empty($attachments[0])) {
			unset($attachments[0]);
		}


		if ($_FILES['attachments']['name'][0]) {
			foreach ($_FILES['attachments']['name'] as $num => $filename) {

				if (empty($_FILES['attachments']['name']) || empty($_FILES['attachments']['name'][$num])) {
					continue;
				}


				if (!isFileNameSafe($_FILES['attachments']['name'][$num])) {
					exit("Invalid upload filename.  Valid filenames contain only alpha-numeric, dot, hyphen and underscore characters.");
				}

				$filename = trim($filename);
				$filename = preg_replace("/[^a-zA-Z0-9-_. ]/", "", $filename);
				mt_srand(time());
				$rand = mt_rand(100000, 999999);
				$newfilename = $rand . "_" . $filename;
				move_uploaded_file($_FILES['attachments']['tmp_name'][$num], $projectsdir . $newfilename);
				$attachments[] = $newfilename;
				update_query("mod_project", array("attachments" => implode(",", $attachments)), array("id" => $projectid));
				project_management_log($projectid, $vars['_lang']['clientaddedattachment'] . " " . $filename);
			}
		}

		redir("m=project_management&a=view&id=" . $projectid);
	}

	global $currency;

	$currency = getCurrency($_SESSION['uid']);
	$tplvars['project'] = array("id" => $data['id'], "title" => $data['title'], "adminid" => $data['adminid'], "adminname" => get_query_val("tbladmins", "CONCAT(firstname,' ',lastname)", array("id" => $data['adminid'])), "created" => fromMySQLDate($data['created'], 0, 1), "duedate" => fromMySQLDate($data['duedate'], 0, 1), "duein" => project_management_daysleft($data['duedate']), "lastmodified" => fromMySQLDate($data['lastmodified'], 0, 1), "totaltime" => $totaltime, "status" => $data['status']);

	if (!$tplvars['project']['adminname']) {
		$tplvars['project']['adminname'] = "None";
	}

	$ticketids = $data['ticketids'];
	$invoiceids = $data['invoiceids'];
	$attachments = $data['attachments'];
	$tickets = $invoices = $attachmentsarray = array();
	$ticketids = explode(",", $ticketids);
	foreach ($ticketids as $ticketnum) {

		if ($ticketnum) {
			$result = select_query("tbltickets", "id,tid,c,title,status,lastreply", array("tid" => $ticketnum));
			$data = mysql_fetch_array($result);
			$ticketid = $data['id'];

			if ($ticketid) {
				$tickets[] = array("tid" => $data['tid'], "c" => $data['c'], "title" => $data['title'], "status" => $data['status'], "lastreply" => $data['lastreply']);
				$ticketinvoicelinks[] = "description LIKE '%Ticket #" . $data['tid'] . "%'";
				continue;
			}

			continue;
		}
	}

	$tplvars['tickets'] = $tickets;
	$invoiceids = explode(",", $invoiceids);
	foreach ($invoiceids as $k => $invoiceid) {

		if (!$invoiceid) {
			unset($invoiceids[$k]);
			continue;
		}
	}


	if (!function_exists("getGatewaysArray")) {
		require ROOTDIR . "/includes/gatewayfunctions.php";
	}

	$gateways = getGatewaysArray();
	$ticketinvoicesquery = (!empty($ticketinvoicelinks) ? "(" . implode(" OR ", $ticketinvoicelinks) . ") OR " : "");
	$result = $ticketinvoicelinks = select_query("tblinvoices", "", "id IN (SELECT invoiceid FROM tblinvoiceitems WHERE description LIKE '%Project #" . $projectid . "%' OR " . $ticketinvoicesquery . " (type='Project' AND relid='" . $projectid . "')) OR id IN (" . db_build_in_array(db_escape_numarray($invoiceids)) . ")", "id", "ASC");

	while ($data = mysql_fetch_array($result)) {
		$invoices[] = array("id" => $data['id'], "date" => fromMySQLDate($data['date'], 0, 1), "duedate" => fromMySQLDate($data['duedate'], 0, 1), "datepaid" => fromMySQLDate($data['datepaid'], 0, 1), "total" => formatCurrency($data['total']), "paymentmethod" => $gateways[$data['paymentmethod']], "status" => $data['status'], "rawstatus" => strtolower($data['status']));
	}

	$tplvars['invoices'] = $invoices;
	$attachments = explode(",", $attachments);
	foreach ($attachments as $i => $attachment) {
		$attachment = substr($attachment, 7);

		if ($attachment) {
			$attachmentsarray[$i] = array("filename" => $attachment);
			continue;
		}
	}

	$tplvars['attachments'] = $attachmentsarray;
	$totaltimecount = 0;
	$i = 1;
	$tasks = array();
	$result = select_query("mod_projecttasks", "id,task,notes,adminid,created,duedate,completed", array("projectid" => $projectid), "order", "ASC");

	while ($data = mysql_fetch_assoc($result)) {
		$tasks[$i] = $data;
		$tasks[$i]['adminname'] = ($data['adminid'] ? get_query_val("tbladmins", "CONCAT(firstname,' ',lastname)", array("id" => $data['adminid'])) : "0");
		$tasks[$i]['duein'] = ($data['duedate'] != "0000-00-00" ? project_management_daysleft($data['duedate'], $vars) : "0");
		$tasks[$i]['duedate'] = ($data['duedate'] != "0000-00-00" ? fromMySQLDate($data['duedate'], 0, 1) : "0");
		$totaltasktime = 0;
		$result2 = select_query("mod_projecttimes", "", array("projectid" => $projectid, "taskid" => $data['id']));

		while ($data = mysql_fetch_array($result2)) {
			$timerid = $data['id'];
			$timerstart = $data['start'];
			$timerend = $data['end'];
			$starttime = fromMySQLDate(date("Y-m-d H:i:s", $timerstart), 1, 1) . ":" . date("s", $timerstart);
			$endtime = ($timerend ? fromMySQLDate(date("Y-m-d H:i:s", $timerend), 1, 1) . ":" . date("s", $timerend) : 0);
			$totaltime = ($timerend ? project_management_sec2hms($timerend - $timerstart) : 0);
			$tasks[$i]['times'][] = array("id" => $data['id'], "adminid" => $data['adminid'], "adminname" => get_query_val("tbladmins", "CONCAT(firstname,' ',lastname)", array("id" => $data['adminid'])), "start" => $starttime, "end" => $endtime, "duration" => $totaltime);

			if ($timerend) {
				$totaltasktime += $timerend - $timerstart;
			}
		}

		$totaltimecount += $totaltasktime;
		$tasks[$i]['totaltime'] = project_management_sec2hms($totaltasktime);
		++$i;
	}

	$tplvars['tasks'] = $tasks;
	$totaltime = project_management_sec2hms($totaltimecount);
	$tplvars['project']['totaltime'] = $totaltime;
	return 1;
}

redir("m=project_management");
?>