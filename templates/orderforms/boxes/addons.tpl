<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-boxes">

<div class="left">

<form method="post" action="{$smarty.server.PHP_SELF}">
<p>{$LANG.ordercategories}: <select name="gid" onchange="submit()">
{foreach key=num item=productgroup from=$productgroups}
<option value="{$productgroup.gid}">{$productgroup.name}</option>
{/foreach}
<option value="addons" selected>{$LANG.cartproductaddons}</option>
{if $renewalsenabled}<option value="renewals">{$LANG.domainrenewals}</option>{/if}
</select></p>
</form>

</div>
<div class="clear"></div>

<table width="90%" align="center" cellspacing="1" cellpadding="5">
<tr class="orderheadingrow"><td colspan=2></td></tr>
{foreach from=$addons item=addon}
<tr class="{cycle values="orderrow1,orderrow2"}"><td align="center">
<form method="post" action="{$smarty.server.PHP_SELF}?a=add">
<input type="hidden" name="aid" value="{$addon.id}" />
<strong>{$addon.name}</strong> - {$addon.description}<br />
<div align="center">
<div style="margin:5px;padding:2px;color:#cc0000;">
{if $addon.free}
{$LANG.orderfree}
{else}
{$addon.recurringamount} {$addon.billingcycle}
{if $addon.setupfee}+ {$addon.setupfee} {$LANG.ordersetupfee}<br />{/if}
{/if}
</div>
{$LANG.cartproductaddonschoosepackage}: <select name="productid">{foreach from=$addon.productids item=product}
<option value="{$product.id}">{$product.product}{if $product.domain} - {$product.domain}{/if}</option>
{/foreach}</select> <input type="submit" value="{$LANG.ordernowbutton} &raquo;" />
</div>
</form>
</td></tr>
{foreachelse}
<tr class="orderrow1"><td colspan="2" class="textcenter"><strong>{$LANG.cartproductaddonsnone}</strong></td></tr>
{/foreach}
<tr class="orderheadingrow"><td colspan=2></td></tr>
</table>

</div>