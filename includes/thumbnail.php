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

require "../init.php";
error_reporting(0);

if (!function_exists("getimagesize")) {
	exit("You need to recompile with the GD library included in PHP for this feature to be able to function");
}

$filename = "";

if ($tid) {
	$data = get_query_vals("tbltickets", "userid,attachment", array("id" => $tid));
	$userid = $data[0];
	$attachments = $data[1];
	$attachments = explode("|", $attachments);
	$filename = $attachments_dir . $attachments[$i];
}


if ($rid) {
	$data = get_query_vals("tblticketreplies", "tid,attachment", array("id" => $rid));
	$ticketid = $data[0];
	$attachments = $data[1];
	$attachments = explode("|", $attachments);
	$filename = $attachments_dir . $attachments[$i];
	$userid = get_query_val("tbltickets", "userid", array("id" => $ticketid));
}


if ($_SESSION['uid'] != $userid && !$_SESSION['adminid']) {
	$filename = ROOTDIR . "/images/nothumbnail.gif";
}


if (!$filename) {
	$filename = ROOTDIR . "/images/nothumbnail.gif";
}

$size = getimagesize($filename);
switch ($size['mime']) {
case "image/jpeg": {
		$img = imagecreatefromjpeg($filename);
		break;
	}

case "image/gif": {
		$img = imagecreatefromgif($filename);
		break;
	}

case "image/png": {
		$img = imagecreatefrompng($filename);
		break;
	}

default: {
		$img = false;
		break;
	}
}

$thumbWidth = 200;
$thumbHeight = 125;

if (!$img) {
	$filename = ROOTDIR . "/images/nothumbnail.gif";
	$img = imagecreatefromgif($filename);
}

$width = imagesx($img);
$height = imagesy($img);
$new_width = $thumbWidth;
$new_height = floor($height * ($thumbWidth / $width));

if ($thumbHeight < $new_height) {
	$new_height = $thumbHeight;
	$new_width = floor($width * ($thumbHeight / $height));
}

$tmp_img = imagecreatetruecolor($new_width, $new_height);
imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Content-type: " . $size['mime']);
imagejpeg($tmp_img);
imagedestroy($tmp_img);
?>