<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-cart">

<h1>{$LANG.cartproductaddons}</h1>

<div class="cartbox" id="catlinks" align="center">
{foreach key=num item=productgroup from=$productgroups}
{if $gid eq $productgroup.gid}
{$productgroup.name} |
{else}
<a href="{$smarty.server.PHP_SELF}?gid={$productgroup.gid}">{$productgroup.name}</a> | 
{/if}
{/foreach}
{if $loggedin}
{$LANG.cartproductaddons} |
{if $renewalsenabled}<a href="{$smarty.server.PHP_SELF}?gid=renewals">{$LANG.domainrenewals}</a> | {/if}
{/if}
{if $registerdomainenabled}<a href="{$smarty.server.PHP_SELF}?a=add&domain=register">{$LANG.registerdomain}</a> | {/if}
{if $transferdomainenabled}<a href="{$smarty.server.PHP_SELF}?a=add&domain=transfer">{$LANG.transferdomain}</a> | {/if}
<a href="{$smarty.server.PHP_SELF}?a=view">{$LANG.viewcart}</a>
</div>

<br />

{foreach from=$addons item=addon}
<div class="cartbox" align="center">
<form method="post" action="{$smarty.server.PHP_SELF}?a=add">
<input type="hidden" name="aid" value="{$addon.id}" />
<strong>{$addon.name}</strong><br />
{$addon.description}<br />
<div class="cartpricing">
{if $addon.free}
{$LANG.orderfree}
{else}
{$addon.recurringamount} {$addon.billingcycle}
{if $addon.setupfee}<br />+ {$addon.setupfee} {$LANG.ordersetupfee}{/if}
{/if}
</div>
{$LANG.cartproductaddonschoosepackage}: <select name="productid">{foreach from=$addon.productids item=product}
<option value="{$product.id}">{$product.product}{if $product.domain} - {$product.domain}{/if}</option>
{/foreach}</select>
<br /><br />
<div align="center"><input type="submit" value="{$LANG.ordernowbutton}" /></div>
</form>
</div>
<br />
{/foreach}

{if $noaddons}
<div class="errorbox">{$LANG.cartproductaddonsnone}</div>
{/if}

<p align="right"><input type="button" value="{$LANG.viewcart}" onclick="window.location='cart.php?a=view'" /></p>

</div>