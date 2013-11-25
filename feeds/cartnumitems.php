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
require("../init.php");

/*
*** USAGE SAMPLES ***

<script language="javascript" src="feeds/cartnumitems.php"></script>

*/
$products = (isset($_SESSION["cart"]["products"]) && is_array($_SESSION["cart"]["products"])) ? $_SESSION["cart"]["products"] : array();
$addons = (isset($_SESSION["cart"]["addons"]) && is_array($_SESSION["cart"]["addons"])) ? $_SESSION["cart"]["addons"] : array();
$domains = (isset($_SESSION["cart"]["domains"]) && is_array($_SESSION["cart"]["domains"])) ? $_SESSION["cart"]["domains"] : array();
$renewals = (isset($_SESSION["cart"]["renewals"]) && is_array($_SESSION["cart"]["renewals"])) ? $_SESSION["cart"]["renewals"] : array();

$cartitems = count($products) + count($addons) + count($domains) + count($renewals);

$items = ($cartitems == 1) ? 'item' : 'items';

widgetoutput('You have <b>'.$cartitems.'</b> '.$items.' in your basket');

function widgetoutput($value) {
    echo "document.write('".addslashes($value)."');";
    exit;
}

?>