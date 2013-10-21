<h2>{$LANG.addfunds}</h2>
{if $addfundsdisabled}
<br />
<div class="errorbox">{$LANG.clientareaaddfundsdisabled}</div>
<br /><br /><br />
{else}
<p>{$LANG.addfundsdescription}</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="frame">
  <tr>
    <td><table width="100%" border="0" align="center" cellpadding="10" cellspacing="0">
        <tr>
          <td width="230" class="fieldarea"><strong>{$LANG.addfundsminimum}</strong></td>
          <td>{$currencysymbol}{$minimumamount} {$currency}</td>
        </tr>
        <tr>
          <td width="230" class="fieldarea"><strong>{$LANG.addfundsmaximum}</strong></td>
          <td>{$currencysymbol}{$maximumamount} {$currency}</td>
        </tr>
        <tr>
          <td width="230" class="fieldarea"><strong>{$LANG.addfundsmaximumbalance}</strong></td>
          <td>{$currencysymbol}{$maximumbalance} {$currency}</td>
        </tr>
      </table></td>
  </tr>
</table>
{if $notallowed}<br />
<div class="errorbox">{$LANG.clientareaaddfundsnotallowed}</div>
{/if}

{if $errormessage}<br />
<div class="errorbox">{$errormessage}</div>
{/if}
<form method="post" action="{$smarty.server.PHP_SELF}?action=addfunds">
  <p align="center"><strong>{$LANG.addfundsamount}:</strong>
    <input type="text" name="amount" size="10" value="{$amount}" />
    <br />
    <strong><br />
    {$LANG.orderpaymentmethod}:</strong>
    <select name="paymentmethod">
      
      {foreach key=num item=gateway from=$gateways}
      
      <option value="{$gateway.sysname}">{$gateway.name}</option>
      
      {/foreach}
    
    </select>
  </p>
  <p align="center">
    <input type="submit" value="{$LANG.addfunds}" />
  </p>
</form><br />
{/if}