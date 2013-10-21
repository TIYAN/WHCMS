<p class="heading2">{$LANG.addfunds}</p>

{if $addfundsdisabled}

<br />
<div class="errorbox">{$LANG.clientareaaddfundsdisabled}</div>
<br /><br /><br />

{else}

<p>{$LANG.addfundsdescription}</p>

<table align="center" class="clientareatable" style="width:50%;" cellspacing="1">
<tr class="clientareatableactive"><td width=230><strong>{$LANG.addfundsminimum}</strong></td><td>{$currencysymbol}{$minimumamount} {$currency}</td></tr>
<tr class="clientareatableactive"><td><strong>{$LANG.addfundsmaximum}</strong></td><td>{$currencysymbol}{$maximumamount} {$currency}</td></tr>
<tr class="clientareatableactive"><td><strong>{$LANG.addfundsmaximumbalance}</strong></td><td>{$currencysymbol}{$maximumbalance} {$currency}</td></tr>
</table>

{if $notallowed}<br /><div class="errorbox">{$LANG.clientareaaddfundsnotallowed}</div>{/if}

{if $errormessage}<br /><div class="errorbox">{$errormessage}</div>{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action=addfunds">
<p align="center"><strong>{$LANG.addfundsamount}:</strong> <input type="text" name="amount" size="10" value="{$amount}" /><br />
<strong>{$LANG.orderpaymentmethod}:</strong> <select name="paymentmethod">{foreach key=num item=gateway from=$gateways}<option value="{$gateway.sysname}">{$gateway.name}</option>{/foreach}</select></p>
<p align="center"><input type="submit" value="{$LANG.addfunds}" class="buttongo" /></p>
</form>

{/if}