<?php

require("../init.php");

/*
*** USAGE SAMPLES ***

<script language="javascript" src="feeds/productpricing.php?pid=1&currencyid=1"></script>

<script language="javascript" src="feeds/productpricing.php?pid=5&currencyid=2"></script>

*/

    $result = select_query("tblproducts","",array("id"=>$pid));
	$data = mysql_fetch_array($result);
    $pid = $data['id'];
	$paytype = $data['paytype'];

    if (!$pid) widgetoutput('Product ID Not Found');

    if (!$currencyid) {
        $result = select_query("tblcurrencies","id","","id","ASC");
    	$data = mysql_fetch_array($result);
        $currencyid = $data['id'];
    }

    $currency = getCurrency('',$currencyid);

    $result = select_query("tblpricing","",array("type"=>"product","currency"=>$currencyid,"relid"=>$pid));
	$data = mysql_fetch_array($result);
	$msetupfee = $data['msetupfee'];
	$qsetupfee = $data['qsetupfee'];
	$ssetupfee = $data['ssetupfee'];
	$asetupfee = $data['asetupfee'];
	$bsetupfee = $data['bsetupfee'];
    $tsetupfee = $data['tsetupfee'];
	$monthly = $data['monthly'];
	$quarterly = $data['quarterly'];
	$semiannually = $data['semiannually'];
	$annually = $data['annually'];
	$biennially = $data['biennially'];
    $triennially = $data['triennially'];

    $systemurl = ($CONFIG['SystemSSLURL']) ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL'];

    $output = '<form method="post" action="'.$systemurl.'/cart.php?a=add&pid='.$pid.'">';

	if ($paytype=="free") {

		$output .= $_LANG['orderfree'];

	} elseif ($paytype=="onetime") {

        $output .= formatCurrency($monthly);
		if ($msetupfee!="0.00") $output .= " + ".formatCurrency($msetupfee)." ".$_LANG['ordersetupfee'];

	} elseif ($paytype=="recurring") {

        $output .= '<select name="billingcycle">';

        if ($triennially>=0) {
            $output .= '<option value="triennially">'.$_LANG['orderpaymentterm36month'].' - '.formatCurrency($triennially/36).'/mo';
			if($tsetupfee!="0.00") $output .= " + ".formatCurrency($tsetupfee)." ".$_LANG['ordersetupfee'];
            $output .= '</option>';
        }

        if ($biennially>=0) {
            $output .= '<option value="biennially">'.$_LANG['orderpaymentterm24month'].' - '.formatCurrency($biennially/24).'/mo';
			if($bsetupfee!="0.00") $output .= " + ".formatCurrency($bsetupfee)." ".$_LANG['ordersetupfee'];
            $output .= '</option>';
        }

        if ($annually>=0) {
            $output .= '<option value="annually">'.$_LANG['orderpaymentterm12month'].' - '.formatCurrency($annually/12).'/mo';
			if($asetupfee!="0.00") $output .= " + ".formatCurrency($asetupfee)." ".$_LANG['ordersetupfee'];
            $output .= '</option>';
        }

        if ($semiannually>=0) {
            $output .= '<option value="semiannually">'.$_LANG['orderpaymentterm6month'].' - '.formatCurrency($semiannually/6).'/mo';
			if($ssetupfee!="0.00") $output .= " + ".formatCurrency($ssetupfee)." ".$_LANG['ordersetupfee'];
            $output .= '</option>';
        }

        if ($quarterly>=0) {
            $output .= '<option value="quarterly">'.$_LANG['orderpaymentterm3month'].' - '.formatCurrency($quarterly/3).'/mo';
			if($qsetupfee!="0.00") $output .= " + ".formatCurrency($qsetupfee)." ".$_LANG['ordersetupfee'];
            $output .= '</option>';
        }

		if ($monthly>=0) {
            $output .= '<option value="monthly">'.$_LANG['orderpaymenttermmonthly'].' - '.formatCurrency($monthly).'/mo';
			if($msetupfee!="0.00") $output .= " + ".formatCurrency($msetupfee)." ".$_LANG['ordersetupfee'];
            $output .= '</option>';
		}

        $output .= '</select>';

    }

    $output .= ' <input type="submit" value="'.$_LANG['domainordernow'].'" /></form>';

    widgetoutput($output);

function widgetoutput($value) {
    echo "document.write('".addslashes($value)."');";
    exit;
}

?>