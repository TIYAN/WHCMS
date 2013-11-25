<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 **/

require "../init.php";

if (!function_exists("imagecreatefrompng")) {
	exit("You need to recompile with the GD library included in PHP for this feature to be able to function");
}

$alphanum = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
$rand = substr(str_shuffle($alphanum), 0, 5);
$image = imagecreatefrompng("../images/verify.png");
$textColor = imagecolorallocate($image, 0, 0, 0);
imagestring($image, 5, 28, 4, $rand, $textColor);
$_SESSION['image_random_value'] = md5($rand);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>