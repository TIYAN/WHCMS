<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-boxes">

<p>{$LANG.cartproductdesc}</p>

<form method="post" action="{$smarty.server.PHP_SELF}?a=confproduct&i={$i}">
<input type="hidden" name="configure" value="true">

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<table width="90%" align="center" cellspacing="1" cellpadding="5">

{if $productinfo}
<tr><td colspan="2"><strong>{$LANG.orderproduct}</strong></td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr class="orderrow1"><td class="leftcol">{$LANG.orderproduct}</td><td><strong>{$productinfo.groupname} - {$productinfo.name}</strong></td></tr>
<tr class="orderrow2"><td class="leftcol">{$LANG.orderdesc}</td><td>{$productinfo.description}</td></tr>
<tr class="orderrow1"><td class="leftcol">{$LANG.orderbillingcycle}</td><td><input type="hidden" name="previousbillingcycle" value="{$billingcycle}" />{if $pricing.type eq "free"}
<input type="hidden" name="billingcycle" value="free" />
{$LANG.orderfree}
{elseif $pricing.type eq "onetime"}
<input type="hidden" name="billingcycle" value="onetime" />
{$pricing.onetime} {$LANG.orderpaymenttermonetime}
{else}
<select name="billingcycle" onchange="submit()">
{if $pricing.monthly}<option value="monthly"{if $billingcycle eq "monthly"} selected="selected"{/if}>{$pricing.monthly}</option>{/if}
{if $pricing.quarterly}<option value="quarterly"{if $billingcycle eq "quarterly"} selected="selected"{/if}>{$pricing.quarterly}</option>{/if}
{if $pricing.semiannually}<option value="semiannually"{if $billingcycle eq "semiannually"} selected="selected"{/if}>{$pricing.semiannually}</option>{/if}
{if $pricing.annually}<option value="annually"{if $billingcycle eq "annually"} selected="selected"{/if}>{$pricing.annually}</option>{/if}
{if $pricing.biennially}<option value="biennially"{if $billingcycle eq "biennially"} selected="selected"{/if}>{$pricing.biennially}</option>{/if}
{if $pricing.triennially}<option value="triennially"{if $billingcycle eq "triennially"} selected="selected"{/if}>{$pricing.triennially}</option>{/if}
</select>
{/if}</td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr><td height="10"></td></tr>

{/if}

{if $productinfo.type eq "server"}
<tr><td colspan="2"><strong>{$LANG.cartconfigserver}</strong></td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr class="orderrow1"><td class="leftcol">{$LANG.serverhostname}</td><td><input type="text" name="hostname" size="15" value="{$server.hostname}" /> eg. server1(.yourdomain.com)</td></tr>
<tr class="orderrow2"><td class="leftcol">{$LANG.serverns1prefix}</td><td><input type="text" name="ns1prefix" size="10" value="{$server.ns1prefix}" /> eg. ns1(.yourdomain.com)</td></tr>
<tr class="orderrow1"><td class="leftcol">{$LANG.serverns2prefix}</td><td><input type="text" name="ns2prefix" size="10" value="{$server.ns2prefix}" /> eg. ns2(.yourdomain.com)</td></tr>
<tr class="orderrow2"><td class="leftcol">{$LANG.serverrootpw}</td><td><input type="password" name="rootpw" size="20" value="{$server.rootpw}" /></td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr><td height="10"></td></tr>
{/if}

{if $configurableoptions}
<tr><td colspan="2"><strong>{$LANG.orderconfigpackage}</strong></td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
{foreach key=num item=configoption from=$configurableoptions}
<tr class="orderrow{if $num % 2}2{else}1{/if}"><td class="leftcol">{$configoption.optionname}:</td><td>
{if $configoption.optiontype eq 1}
<select name="configoption[{$configoption.id}]">
{foreach key=num2 item=options from=$configoption.options}
<option value="{$options.id}"{if $configoption.selectedvalue eq $options.id} selected="selected"{/if}>{$options.name}</option>
{/foreach}
</select>
{elseif $configoption.optiontype eq 2}
{foreach key=num2 item=options from=$configoption.options}
<label><input type="radio" name="configoption[{$configoption.id}]" value="{$options.id}"{if $configoption.selectedvalue eq $options.id} checked="checked"{/if}> {$options.name}</label><br />
{/foreach}
{elseif $configoption.optiontype eq 3}
<label><input type="checkbox" name="configoption[{$configoption.id}]" value="1"{if $configoption.selectedqty} checked{/if}> {$configoption.options.0.name}</label>
{elseif $configoption.optiontype eq 4}
<input type="text" name="configoption[{$configoption.id}]" value="{$configoption.selectedqty}" size="5"> x {$configoption.options.0.name}
{/if}
</td></tr>
{/foreach}
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr><td height="10"></td></tr>
{/if}

{if $addons}
<tr><td colspan="2"><strong>{$LANG.cartaddons}</strong></td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
{foreach key=num item=addon from=$addons}
<tr class="orderrow{if $num % 2}2{else}1{/if}"><td class="leftcol">{$addon.checkbox}</td><td><label for="a{$addon.id}"><strong>{$addon.name}</strong> - {$addon.description} ({$addon.pricing})</label></td></tr>
{/foreach}
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr><td height="10"></td></tr>
{/if}

{if $customfields}
<tr><td colspan="2"><strong>{$LANG.orderadditionalrequiredinfo}</strong></td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
{foreach key=num item=customfield from=$customfields}
<tr class="orderrow{if $num % 2}2{else}1{/if}"><td class="leftcol">{$customfield.name}</td><td>{$customfield.input} {$customfield.description}</td></tr>
{/foreach}
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr><td height="10"></td></tr>
{/if}

{if $domainoption}
<tr><td colspan="2"><strong>{$LANG.cartproductdomain}</strong></td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
{if $domains}
<input type="hidden" name="domainoption" value="{$domainoption}" />
{foreach key=num item=domain from=$domains}
<input type="hidden" name="domains[]" value="{$domain.domain}" />
<input type="hidden" name="domainsregperiod[{$domain.domain}]" value="{$domain.regperiod}" />
<tr class="orderrow{if $num % 2}2{else}1{/if}"><td class="leftcol">{$LANG.orderdomain} {$num+1}</td><td>{$domain.domain}{if $domain.regperiod} ({$domain.regperiod} {$LANG.orderyears}){/if}</td></tr>
{/foreach}
{/if}
{if $additionaldomainfields}
{foreach key=domainfieldname item=domainfield from=$additionaldomainfields}
<tr class="orderrow{if $num % 2}2{else}1{/if}"><td class="leftcol">{$domainfieldname}</td><td>{$domainfield}</td></tr>
{/foreach}
{/if}
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr><td height="10"></td></tr>
{/if}

</table>

<p align="center">{if $firstconfig}<input type="submit" value="{$LANG.addtocart}" />{else}<input type="submit" value="{$LANG.updatecart}" />{/if}</p>

</form>

</div>