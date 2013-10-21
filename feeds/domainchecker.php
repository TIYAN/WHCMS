<?php

require("../init.php");
require("../includes/domainfunctions.php");

/*
*** USAGE SAMPLE ***

<script language="javascript" src="feeds/domainchecker.php"></script>

*/

$systemurl = $CONFIG["SystemSSLURL"] ? $CONFIG["SystemSSLURL"] : $CONFIG["SystemURL"];

$currency = getCurrency();
$tlds = getTLDList();

$code = '<form action="'.$systemurl.'/domainchecker.php" method="post"><input type="hidden" name="direct" value="true">www. <input type="text" name="domain" size="30"> <select name="ext">';
foreach ($tlds AS $tld) {
    $code .= '<option>'.$tld.'</option>';
}
$code .= '</select> <input type="submit" value="Go"></form>';

echo "document.write('".$code."');";

?>