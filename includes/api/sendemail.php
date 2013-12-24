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


if (!function_exists("pdfInvoice")) {
	require ROOTDIR . "/includes/invoicefunctions.php";
}


if ($_POST['custommessage']) {
	delete_query("tblemailtemplates", array("name" => "Mass Mail Template"));
	insert_query("tblemailtemplates", array("type" => $_POST['customtype'], "name" => "Mass Mail Template", "subject" => html_entity_decode($_POST['customsubject']), "message" => html_entity_decode($_POST['custommessage'])));
	$messagename = "Mass Mail Template";
}
else {
	$messagename = $_POST['messagename'];
}

$result = select_query("tblemailtemplates", "COUNT(*)", array("name" => $messagename));
$data = mysql_fetch_array($result);

if (!$data[0]) {
	$apiresults = array("result" => "error", "message" => "Email Template not found");
	return null;
}


if (isset($customvars)) {
	if (!is_array($customvars)) {
		$customvars = unserialize(base64_decode($customvars));
	}
}
else {
	$customvars = array();
}

sendMessage($messagename, $_POST['id'], $customvars);

if ($_POST['customtext']) {
	delete_query("tblemailtemplates", array("name" => "Mass Mail Template"));
}

$apiresults = array("result" => "success");
?>