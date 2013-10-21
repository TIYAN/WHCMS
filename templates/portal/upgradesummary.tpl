<link rel="stylesheet" type="text/css" href="templates/orderforms/web20cart/style.css" />

<p>{$LANG.upgradesummary}</p>

<p>{$LANG.orderproduct}:<strong> {$groupname} - {$productname}</strong>{if $domain} ({$domain}){/if}</p>

{if $promoerror}
  <div class="errorbox">{$promoerror}</div>
{/if}

<table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" class="data">
  <tr>
    <th width="55%">{$LANG.orderdesc}</th>
    <th width="45%">{$LANG.orderprice}</th>
  </tr>

{foreach key=num item=upgrade from=$upgrades}
{if $type eq "package"}
<tr class="carttableproduct"><td><input type="hidden" name="pid" value="{$upgrade.newproductid}" /><input type="hidden" name="billingcycle" value="{$upgrade.newproductbillingcycle}" />{$upgrade.oldproductname} => {$upgrade.newproductname}</td><td align="center">{$currencysymbol}{$upgrade.price} {$currency}</td></tr>
{elseif $type eq "configoptions"}
<tr class="carttableproduct"><td>{$upgrade.configname}: {$upgrade.originalvalue} => {$upgrade.newvalue}</td><td align="center">{$currencysymbol}{$upgrade.price} {$currency}</td></tr>
{/if}
{/foreach}

<tr class="carttablesummary"><td align="right">{$LANG.ordersubtotal}: &nbsp;</td><td align="center">{$subtotal}</td></tr>
{if $promodesc}
<tr class="carttablesummary"><td align="right">{$promodesc}: &nbsp;</td><td align="center">{$discount}</td></tr>
{/if}
{if $taxrate}
<tr class="carttablesummary"><td align="right">{$taxname} @ {$taxrate}%: &nbsp;</td><td align="center">{$tax}</td></tr>
{/if}
{if $taxrate2}
<tr class="carttablesummary"><td align="right">{$taxname2} @ {$taxrate2}%: &nbsp;</td><td align="center">{$tax2}</td></tr>
{/if}
<tr class="carttabledue"><td align="right">{$LANG.ordertotalduetoday}: &nbsp;</td><td align="center">{$total}</td></tr>
</table>

{if $promorecurring}
  <div class="errorbox">{$LANG.recurringpromodesc|sprintf2:$promorecurring}</div>
{/if}

{if $type eq "package"}<p align="center">{$LANG.upgradeproductlogic} ({$upgrade.daysuntilrenewal} {$LANG.days})</p>{/if}

<form method="post" action="{$smarty.server.PHP_SELF}">
<input type="hidden" name="step" value="2">
<input type="hidden" name="type" value="{$type}">
<input type="hidden" name="id" value="{$id}">
{if $type eq "package"}
<input type="hidden" name="pid" value="{$upgrades.0.newproductid}" />
<input type="hidden" name="billingcycle" value="{$upgrades.0.newproductbillingcycle}" />
{/if}
{foreach from=$configoptions key=cid item=value}<input type="hidden" name="configoption[{$cid}]" value="{$value}" />{/foreach}
<p align="center"><strong>{$LANG.orderpromotioncode}:</strong> {if $promocode}
{$promocode} - {$promodesc} <input type="submit" name="removepromo" value="{$LANG.orderdontusepromo}" class="buttonwarn" />
{else}
<input type="text" name="promocode" size="20" /> <input type="submit" value="{$LANG.orderpromovalidatebutton}" class="button" />
{/if}</p>
</form>

<form method="post" action="{$smarty.server.PHP_SELF}">
<input type="hidden" name="step" value="3">
<input type="hidden" name="type" value="{$type}">
<input type="hidden" name="id" value="{$id}">
{if $type eq "package"}
<input type="hidden" name="pid" value="{$upgrades.0.newproductid}" />
<input type="hidden" name="billingcycle" value="{$upgrades.0.newproductbillingcycle}" />
{/if}
{foreach from=$configoptions key=cid item=value}<input type="hidden" name="configoption[{$cid}]" value="{$value}" />{/foreach}
{if $promocode}<input type="hidden" name="promocode" value="{$promocode}">{/if}

<p><strong>{$LANG.orderpaymentmethod}</strong></p>
<p>{foreach key=num item=gateway from=$gateways}<input type="radio" name="paymentmethod" value="{$gateway.sysname}" id="pgbtn{$num}"{if $selectedgateway eq $gateway.sysname} checked{/if}><label for="pgbtn{$num}">{$gateway.name}</label> {/foreach}</p>

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}"></p>

</form>