<?php

require("../init.php");

/*
*** USAGE SAMPLES ***

<script language="javascript" src="feeds/productsinfo.php?pid=1&get=name"></script>

<script language="javascript" src="feeds/productsinfo.php?pid=1&get=description"></script>

<script language="javascript" src="feeds/productsinfo.php?pid=1&get=price&billingcycle=monthly"></script>

<script language="javascript" src="feeds/productsinfo.php?pid=1&get=orderurl&carttpl=web20cart"></script>

*/

$result = select_query("tblproducts","",array("id"=>$pid));
$data = mysql_fetch_array($result);
$pid = $data['id'];
$name = $data['name'];
$description = $data['description'];

if (!$pid) widgetoutput('Product ID Not Found');

if ($get=="name") widgetoutput($name);

if ($get=="description") {
    $description = str_replace(array("\r","\n","\r\n"),"",nl2br($description));
    widgetoutput($description);
}

if ($get=="configoption") widgetoutput($data['configoption'].$configoptionnum);

if ($get=="orderurl") {
    $systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'].'/' : $CONFIG['SystemURL'].'/';
    if ($carttpl=="ajax") {
        widgetoutput($systemurl."order/?pid=$pid");
    } else {
        widgetoutput($systemurl."cart.php?a=add&pid=$pid&$carttpl=cart");
    }
}

if ($get=="price") {
    $currency = ($currency) ? getCurrency('',$currency) : getCurrency();
    $result = select_query("tblpricing","",array("type"=>"product","currency"=>$currency['id'],"relid"=>$pid));
    $data = mysql_fetch_array($result);
    $price = $data[$billingcycle];
    $price = formatCurrency($price);
    widgetoutput($price);
}

function widgetoutput($value) {
    echo "document.write('".addslashes($value)."');";
    exit;
}

?>