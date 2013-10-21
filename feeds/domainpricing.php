<?php

require("../init.php");
require("../includes/domainfunctions.php");

/*
*** USAGE SAMPLES ***

<style type="text/css">
table.domainpricing {
    width: 600px;
    background-color: #ccc;
}
table.domainpricing th {
    padding: 3px;
    background-color: #efefef;
    font-weight: bold;
}
table.domainpricing td {
    padding: 3px;
    background-color: #fff;
    text-align: center;
}
</style>
<script language="javascript" src="feeds/domainpricing.php"></script>

*/

$currency = ($currency) ? getCurrency('',$currency) : getCurrency();

$code = '<table cellspacing="1" cellpadding="0" class="domainpricing"><tr><th>TLD</th><th>Min. Years</th><th>Register</th><th>Transfer</th><th>Renew</th></tr>';

$freeamt = formatCurrency(0);
$tldslist = getTLDList();
foreach ($tldslist AS $tld) {
    $tldpricing = getTLDPriceList($tld,true);
    $firstoption = current($tldpricing);
    $year = key($tldpricing);
    $transfer = ($firstoption["transfer"]==$freeamt) ? $_LANG['orderfree'] : $firstoption["transfer"];
    $code .= '<tr><td>'.$tld.'</td><td>'.$year.'</td><td>'.$firstoption["register"].'</td><td>'.$transfer.'</td><td>'.$firstoption["renew"].'</td></tr>';
}

$code .= '</table>';

echo "document.write('".$code."');";

?>