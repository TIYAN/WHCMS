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

if (!defined("WHMCS")) {
	exit("This file cannot be accessed directly");
}


if (!$limitstart) {
	$limitstart = 0;
}


if (!$limitnum) {
	$limitnum = 25;
}

$where = array();

if ($quoteid) {
	$where['id'] = $quoteid;
}


if ($userid) {
	$where['userid'] = $userid;
}


if ($subject) {
	$where['subject'] = $subject;
}


if ($stage) {
	$where['stage'] = $stage;
}


if ($datecreated) {
	$where['datecreated'] = $datecreated;
}


if ($lastmodified) {
	$where['lastmodified'] = $lastmodified;
}


if ($validuntil) {
	$where['validuntil'] = $validuntil;
}

$quotes = array();
$result = select_query("tblquotes", "", $where, "id", "DESC", (int)$limitstart . "," . (int)$limitnum);

while ($data = mysql_fetch_assoc($result)) {
	$result2 = select_query("tblquoteitems", "id,description,quantity,unitprice,discount,taxable", array("quoteid" => $data['id']));

	while ($itemdata = mysql_fetch_assoc($result2)) {
		$data['items']['item'][] = $itemdata;
	}

	$quotes[] = $data;
}

$apiresults = array("result" => "success", "totalresults" => count($notes), "quotes" => array("quote" => $quotes));
$responsetype = "xml";
?>