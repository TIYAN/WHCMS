<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<link rel="stylesheet" type="text/css" href="includes/jscript/css/ui.all.css" />

{literal}<script type="text/javascript">
jQuery(document).ready(function(){
    prodconfrecalcsummary();
});
</script>{/literal}

<form id="orderfrm" onsubmit="prodconfcomplete();return false;">

<input type="hidden" name="configure" value="true" />
<input type="hidden" name="i" value="{$i}" />

<div id="configproducterror" class="errorbox hidden">xxx</div>

{if $pricing.type eq "recurring"}
<h2>{$LANG.orderbillingcycle}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td>
<select name="billingcycle" onchange="prodconfrecalcsummary()">
{if $pricing.monthly}<option value="monthly"{if $billingcycle eq "monthly"} selected="selected"{/if}>{$pricing.monthly}</option>{/if}
{if $pricing.quarterly}<option value="quarterly"{if $billingcycle eq "quarterly"} selected="selected"{/if}>{$pricing.quarterly}</option>{/if}
{if $pricing.semiannually}<option value="semiannually"{if $billingcycle eq "semiannually"} selected="selected"{/if}>{$pricing.semiannually}</option>{/if}
{if $pricing.annually}<option value="annually"{if $billingcycle eq "annually"} selected="selected"{/if}>{$pricing.annually}</option>{/if}
{if $pricing.biennially}<option value="biennially"{if $billingcycle eq "biennially"} selected="selected"{/if}>{$pricing.biennially}</option>{/if}
{if $pricing.triennially}<option value="triennially"{if $billingcycle eq "triennially"} selected="selected"{/if}>{$pricing.triennially}</option>{/if}
</select>
</td></tr>
</table>
{/if}

{if $productinfo.type eq "server"}
<h2>{$LANG.cartconfigserver}</h2>
<div class="serverconfig">
<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td class="fieldlabel">{$LANG.serverhostname}:</td><td class="fieldarea"><input type="text" name="hostname" size="15" value="{$server.hostname}" /> eg. server1(.yourdomain.com)</td></tr>
<tr class="rowcolor2"><td class="fieldlabel">{$LANG.serverns1prefix}:</td><td class="fieldarea"><input type="text" name="ns1prefix" size="10" value="{$server.ns1prefix}" /> eg. ns1(.yourdomain.com)</td></tr>
<tr class="rowcolor1"><td class="fieldlabel">{$LANG.serverns2prefix}:</td><td class="fieldarea"><input type="text" name="ns2prefix" size="10" value="{$server.ns2prefix}" /> eg. ns2(.yourdomain.com)</td></tr>
<tr class="rowcolor2"><td class="fieldlabel">{$LANG.serverrootpw}:</td><td class="fieldarea"><input type="password" name="rootpw" size="20" value="{$server.rootpw}" /></td></tr>
</table>
</div>
{/if}

{if $configurableoptions}
<h2>{$LANG.orderconfigpackage}</h2>
<div class="configoptions">
<table width="100%" cellspacing="0" cellpadding="0">
{foreach from=$configurableoptions item=configoption}
<tr class="{cycle values="rowcolor1,rowcolor2"}"><td class="fieldlabel">{$configoption.optionname}:</td><td class="fieldarea">
{if $configoption.optiontype eq 1}
<select name="configoption[{$configoption.id}]" onchange="prodconfrecalcsummary()">
{foreach key=num2 item=options from=$configoption.options}
<option value="{$options.id}"{if $configoption.selectedvalue eq $options.id} selected="selected"{/if}>{$options.name}</option>
{/foreach}
</select>
{elseif $configoption.optiontype eq 2}
{foreach key=num2 item=options from=$configoption.options}
<label><input type="radio" name="configoption[{$configoption.id}]" value="{$options.id}"{if $configoption.selectedvalue eq $options.id} checked="checked"{/if} onclick="prodconfrecalcsummary()" /> {$options.name}</label><br />
{/foreach}
{elseif $configoption.optiontype eq 3}
<label><input type="checkbox" name="configoption[{$configoption.id}]" value="1"{if $configoption.selectedqty} checked{/if} onclick="prodconfrecalcsummary()" /> {$configoption.options.0.name}</label>
{elseif $configoption.optiontype eq 4}
{if $configoption.qtymaximum}
{literal}
	<script>
	jQuery(function() {
	    {/literal}
	    var configid = '{$configoption.id}';
	    var configmin = {$configoption.qtyminimum};
	    var configmax = {$configoption.qtymaximum};
	    var configval = {if $configoption.selectedqty}{$configoption.selectedqty}{else}{$configoption.qtyminimum}{/if};
        {literal}
		jQuery("#slider"+configid).slider({
            min: configmin,
            max: configmax,
            value: configval,
            range: "min",
            slide: function(event,ui) {
				jQuery("#confop"+configid).val(ui.value);
				jQuery("#confoplabel"+configid).html(ui.value);
                prodconfrecalcsummary();
			}
        });
	});
	</script>
{/literal}
<table width="90%"><tr><td width="30" id="confoplabel{$configoption.id}" class="configoplabel" style="border:0;">{if $configoption.selectedqty}{$configoption.selectedqty}{else}{$configoption.qtyminimum}{/if}</td><td style="border:0;"><div id="slider{$configoption.id}"></div></td></tr></table>
<input type="hidden" name="configoption[{$configoption.id}]" id="confop{$configoption.id}" value="{if $configoption.selectedqty}{$configoption.selectedqty}{else}{$configoption.qtyminimum}{/if}" />
{else}
<input type="text" name="configoption[{$configoption.id}]" value="{$configoption.selectedqty}" size="5" onkeyup="prodconfrecalcsummary()" /> x {$configoption.options.0.name}
{/if}
{/if}
</td></tr>
{/foreach}
</table>
</div>
{/if}

{if $addons}
<h2>{$LANG.cartaddons}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
{foreach from=$addons item=addon}
<tr class="{cycle values="rowcolor1,rowcolor2"}"><td width="25" align="center"><input type="checkbox" name="addons[{$addon.id}]" id="a{$addon.id}"{if $addon.status} checked{/if} onclick="prodconfrecalcsummary()" /></td><td><label for="a{$addon.id}"><strong>{$addon.name}</strong> - {$addon.description} ({$addon.pricing})</label></td></tr>
{/foreach}
</table>
{/if}

{if $customfields}
<h2>{$LANG.orderadditionalrequiredinfo}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
{foreach key=num item=customfield from=$customfields}
<tr class="{cycle values="rowcolor1,rowcolor2"}"><td class="fieldlabel">{$customfield.name}:</td><td class="fieldarea">{$customfield.input} {$customfield.description}</td></tr>
{/foreach}
</table>
{/if}

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" /></p>

</form>

<div id="prodconfloading" class="loading"><img src="images/loading.gif" border="0" alt="{$LANG.loading}" /></div>