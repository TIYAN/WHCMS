<p>{$LANG.orderproduct}:<strong> {$groupname} - {$productname}</strong>{if $domain} ({$domain}){/if}</p>

{if $overdueinvoice}

<p>{$LANG.upgradeerroroverdueinvoice}</p>

<p align="center"><input type="button" value="{$LANG.clientareabacklink}" onclick="window.location='clientarea.php?action=productdetails&id={$id}'" class="button"></p>

{else}

{if $type eq "package"}

<p>{$LANG.upgradechoosepackage}</p>

<table width="90%" align="center" cellspacing="1" cellpadding="5">
<tr class="orderheadingrow"><td colspan=2></td></tr>
{foreach key=num item=upgradepackage from=$upgradepackages}
<tr class="{cycle values="orderrow1,orderrow2"}"><td align="center">
<form method="post" action="{$smarty.server.PHP_SELF}">
<input type="hidden" name="step" value="2">
<input type="hidden" name="type" value="{$type}">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="pid" value="{$upgradepackage.pid}">
<strong>{$upgradepackage.groupname} - {$upgradepackage.name}</strong><br />
{$upgradepackage.description}<br />
{if $upgradepackage.pricing.type eq "free"}
{$LANG.orderfree}<br />
<input type="hidden" name="billingcycle" value="free">
{elseif $upgradepackage.pricing.type eq "onetime"}
{$upgradepackage.pricing.onetime} {$LANG.orderpaymenttermonetime}
<input type="hidden" name="billingcycle" value="onetime">
{elseif $upgradepackage.pricing.type eq "recurring"}
<select name="billingcycle">
{if $upgradepackage.pricing.monthly}<option value="monthly">{$upgradepackage.pricing.monthly}</option>{/if}
{if $upgradepackage.pricing.quarterly}<option value="quarterly">{$upgradepackage.pricing.quarterly}</option>{/if}
{if $upgradepackage.pricing.semiannually}<option value="semiannually">{$upgradepackage.pricing.semiannually}</option>{/if}
{if $upgradepackage.pricing.annually}<option value="annually">{$upgradepackage.pricing.annually}</option>{/if}
{if $upgradepackage.pricing.biennially}<option value="biennially">{$upgradepackage.pricing.biennially}</option>{/if}
{if $upgradepackage.pricing.triennially}<option value="triennially">{$upgradepackage.pricing.triennially}</option>{/if}
</select>
{/if}<br />
<input type="submit" value="{$LANG.ordercontinuebutton}" />
</form>
</td></tr>
{/foreach}
<tr class="orderheadingrow"><td colspan=2></td></tr>
</table>

{elseif $type eq "configoptions"}

<p>{$LANG.upgradechooseconfigoptions}</p>

{if $errormessage}
  <div class="errorbox">{$errormessage}</div>
  <br />
{/if}

<form method="post" action="{$smarty.server.PHP_SELF}">
<input type="hidden" name="step" value="2">
<input type="hidden" name="type" value="{$type}">
<input type="hidden" name="id" value="{$id}">

<table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" class="data">
<tr><th></th><th>{$LANG.upgradecurrentconfig}</th><th></th><th>{$LANG.upgradenewconfig}</th></tr>
{foreach key=num item=configoption from=$configoptions}
<tr class="carttableproduct"><td><b>{$configoption.optionname}</b></td><td align="center">
{if $configoption.optiontype eq 1 || $configoption.optiontype eq 2}
{$configoption.selectedname}
{elseif $configoption.optiontype eq 3}
{if $configoption.selectedqty}{$LANG.yes}{else}{$LANG.no}{/if}
{elseif $configoption.optiontype eq 4}
{$configoption.selectedqty} x {$configoption.options.0.name}
{/if}
</td><td align="center">=></td><td align="center">
{if $configoption.optiontype eq 1 || $configoption.optiontype eq 2}
<select name="configoption[{$configoption.id}]">{foreach key=num item=option from=$configoption.options}
{if $option.selected}<option value="{$option.id}" selected>{$LANG.upgradenochange}</option>{else}<option value="{$option.id}">{$option.nameonly} {$currencysymbol}{$option.price} {$currency}{/if}</option>
{/foreach}</select>
{elseif $configoption.optiontype eq 3}
<input type="checkbox" name="configoption[{$configoption.id}]" value="1"{if $configoption.selectedqty} checked{/if}> {$configoption.options.0.name}
{elseif $configoption.optiontype eq 4}
<input type="text" name="configoption[{$configoption.id}]" value="{$configoption.selectedqty}" size="5"> x {$configoption.options.0.name}{/if}</td></tr>
{/foreach}
</table>

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}"></p>

</form>

{/if}

{/if}