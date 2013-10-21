<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-verticalsteps">

{include file="orderforms/verticalsteps/verticalsteps.tpl" step=1}

<div class="maincontent">

<div class="left">

<form method="get" action="{$smarty.server.PHP_SELF}">
<p>{$LANG.ordercategories}: <select name="gid" onchange="submit()">
{foreach key=num item=productgroup from=$productgroups}
<option value="{$productgroup.gid}">{$productgroup.name}</option>
{/foreach}
<option value="addons" selected="selected">{$LANG.cartproductaddons}</option>
{if $renewalsenabled}<option value="renewals">{$LANG.domainrenewals}</option>{/if}
{if $registerdomainenabled}<option value="domains">{$LANG.orderdomainregonly}</option>{/if}
</select></p>
</form>

</div>

<br /><br /><br />

{foreach from=$addons item=addon}
<div class="orderbox">
<form method="post" action="{$smarty.server.PHP_SELF}?a=add">
<input type="hidden" name="aid" value="{$addon.id}" />
<strong>{$addon.name}</strong><br />
{$addon.description}<br />
<div align="center" style="margin:5px;padding:2px;color:#cc0000;">
{if $addon.free}
{$LANG.orderfree}
{else}
{$addon.recurringamount} {$addon.billingcycle}{if $addon.setupfee} + {$addon.setupfee} {$LANG.ordersetupfee}{/if}
{/if}
</div>
<div align="center">
{$LANG.cartproductaddonschoosepackage}: <select name="productid">{foreach from=$addon.productids item=product}
<option value="{$product.id}">{$product.product}{if $product.domain} - {$product.domain}{/if}</option>
{/foreach}</select> <input type="submit" value="{$LANG.ordernowbutton} &raquo;" />
</div>
</form>
</div>
{/foreach}

{if $noaddons}
<div class="errorbox">{$LANG.cartproductaddonsnone}</div>
{/if}

</div>

</div>