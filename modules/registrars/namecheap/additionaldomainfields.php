<?php

$_LANG['idnCode'] = 'IDN Code Country';
$_LANG['idnCodeDescription'] = 'Code of Internationalized Domain Name';

include_once dirname(__file__)."/namecheapapi.php";


$showIdnCodeSelection = false;

if (!empty($_POST['domain'])) {
    list($sld, $tld) = explode(".", $_POST['domain'], 2);
    $oIDNA = new NamecheapRegistrarIDNA($sld, $tld);
    $showIdnCodeSelection = $oIDNA->sldWasEncoded();
}
else if (isset($_SESSION['cart']['domains']) && sizeof($_SESSION['cart']['domains']))
{
    foreach($_SESSION['cart']['domains'] as $cartDomain) {
        list($sld, $tld) = explode(".", $cartDomain['domain'], 2);
        $oIDNA = new NamecheapRegistrarIDNA($sld, $tld);
        if ($oIDNA->sldWasEncoded()) {
            $showIdnCodeSelection = true;
            break;
        }
    }
}




if ($showIdnCodeSelection) {
    
    $idnCodesOptions = implode(",", array_keys($oIDNA->getCodeOptions()));
    
    foreach($oIDNA->getTldList() as $tld) {
        foreach($additionaldomainfields[".".$tld] as $additionalField) {
            if ($additionalField['Name'] == 'idnCode')
                continue 2;
        }
        $additionaldomainfields[".".$tld][] = array("Name" => "idnCode", "LangVar" => 'idnCode', "Type" => "dropdown", "Options" => $idnCodesOptions, 'Description' => $_LANG['idnCodeDescription']);
    }
}

