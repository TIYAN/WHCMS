<?php

require("../init.php");

/*
*** USAGE SAMPLES ***

<script language="javascript" src="feeds/domainprice.php?tld=.com&type=register&regperiod=1"></script>

<script language="javascript" src="feeds/domainprice.php?tld=.com&type=register&regperiod=1&format=1"></script>

*/

$result = select_query("tbldomainpricing","id",array("extension"=>$tld));
$data = mysql_fetch_array($result);
$did = $data['id'];

$currency = ($currency) ? getCurrency('',$currency) : getCurrency();

if (!in_array($type,array('register','transfer','renew'))) $type = 'register';

$result = select_query("tblpricing","msetupfee,qsetupfee,ssetupfee,asetupfee,bsetupfee,tsetupfee,monthly,quarterly,semiannually,annually,biennially,triennially",array("type"=>"domain".$type,"currency"=>$currency['id'],"relid"=>$did));
$data = mysql_fetch_array($result);

if ($regperiod<6) $regperiod = $regperiod-1;

$price = $data[$regperiod];

if ($format) $price = formatCurrency($price);

echo "document.write('".$price."');";

?>