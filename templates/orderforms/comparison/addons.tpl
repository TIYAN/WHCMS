<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<script type="text/javascript" src="templates/orderforms/{$carttpl}/js/main.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/uistyle.css" />

<div id="order-comparison">

<h1>{$LANG.cartproductaddons}</h1>

<div class="cartcats">
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
{if $registerdomainenabled}{if $domain neq "register"}<a href="{$smarty.server.PHP_SELF}?a=add&domain=register">{/if}{$LANG.registerdomain}</a> | {/if}
{if $transferdomainenabled}{if $domain neq "transfer"}<a href="{$smarty.server.PHP_SELF}?a=add&domain=transfer">{/if}{$LANG.transferdomain}</a> | {/if}
<a href="{$smarty.server.PHP_SELF}?a=view">{$LANG.viewcart}</a>
</div>

<p>{$LANG.cartfollowingaddonsavailable}</p>

{foreach from=$addons key=num item=addon}
<div class="addoncontainer">
<form method="post" action="{$smarty.server.PHP_SELF}?a=add">
<input type="hidden" name="aid" value="{$addon.id}" />
<div class="addon">
<div class="title">{$addon.name}</div>
<div class="pricing">{if $addon.free}
{$LANG.orderfree}
{else}
{$addon.recurringamount} {$addon.billingcycle}
{if $addon.setupfee}<br /><span class="setup">{$addon.setupfee} {$LANG.ordersetupfee}</span>{/if}
{/if}</div>
<div class="clear"></div>
{$addon.description}
<div class="product">
<select name="productid">
{foreach from=$addon.productids item=product}
<option value="{$product.id}">{$product.product}{if $product.domain} - {$product.domain}{/if}</option>
{/foreach}
</select> <input type="submit" value="{$LANG.ordernowbutton}" class="cartbutton green" />
</div>
</div>
</form>
</div>
{if $num is not div by 2}
<div class="clear"></div>
{/if}
{/foreach}
<div class="clear"></div>

{if $noaddons}
<div class="errorbox">{$LANG.cartproductaddonsnone}</div>
{/if}

</div>