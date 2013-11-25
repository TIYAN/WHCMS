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

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("View PHP Info");
$aInt->title = $aInt->lang("system", "phpinfo");
$aInt->sidebar = "utilities";
$aInt->icon = "phpinfo";
ob_start();
phpinfo();
$info = ob_get_contents();
ob_end_clean();
$info = preg_replace("%^.*<body>(.*)</body>.*$%ms", "$1", $info);
ob_start();
echo "<s";
echo "tyle type=\"text/css\">
.e {background-color: #EFF2F9; font-weight: bold; color: #000000;}
.v {background-color: #efefef; color: #000000;}
.vr {background-color: #efefef; text-align: right; color: #000000;}
hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
</style>
";
echo $info;
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>