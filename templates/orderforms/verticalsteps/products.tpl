<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-verticalsteps">

{include file="orderforms/verticalsteps/verticalsteps.tpl" step=1}

<div class="maincontent">

<div class="left">

<form method="get" action="{$smarty.server.PHP_SELF}">
<p>{$LANG.ordercategories}: <select name="gid" onchange="submit()">
{foreach key=num item=productgroup from=$productgroups}
<option value="{$productgroup.gid}"{if $gid eq $productgroup.gid} selected="selected"{/if}>{$productgroup.name}</option>
{/foreach}
{if $loggedin}
<option value="addons">{$LANG.cartproductaddons}</option>
{if $renewalsenabled}<option value="renewals">{$LANG.domainrenewals}</option>{/if}
{/if}
{if $registerdomainenabled}<option value="domains">{$LANG.orderdomainregonly}</option>{/if}
</select></p>
</form>

</div>
<div class="right">

{if !$loggedin && $currencies}
<form method="post" action="cart.php?gid={$gid}">
<p align="right">{$LANG.choosecurrency}: <select name="currency" onchange="submit()">{foreach from=$currencies item=curr}
<option value="{$curr.id}"{if $curr.id eq $currency.id} selected{/if}>{$curr.code}</option>
{/foreach}</select> <input type="submit" value="{$LANG.go}" /></p>
</form>
{/if}

</div>

<br /><br /><br />

{foreach key=num item=product from=$products}
<div class="orderbox">

<form method="post" action="{$smarty.server.PHP_SELF}?a=add&{if $product.bid}bid={$product.bid}{else}pid={$product.pid}{/if}">
<table width="100%"><tr><td width="75%">
<strong>{$product.name}</strong> {if $product.qty!=""}<em>({$product.qty} {$LANG.orderavailable})</em>{/if} - {$product.description}<br /><br />
{if $product.freedomain}<em>{$LANG.orderfreedomainregistration} {$LANG.orderfreedomaindescription}</em><br />{/if}
{if $product.bid}
<strong>{$LANG.bundledeal}</strong>{if $product.displayprice} {$product.displayprice}{/if}
{elseif $product.paytype eq "free"}
{$LANG.orderfree}<br />
<input type="hidden" name="billingcycle" value="free" />
{elseif $product.paytype eq "onetime"}
{if $product.pricing.hasconfigoptions}{$LANG.startingfrom} {/if}{$product.pricing.onetime} {$LANG.orderpaymenttermonetime}<br />
<input type="hidden" name="billingcycle" value="onetime" />
{elseif $product.paytype eq "recurring"}
<select name="billingcycle">
{if $product.pricing.monthly}<option value="monthly">{$product.pricing.monthly}</option>{/if}
{if $product.pricing.quarterly}<option value="quarterly">{$product.pricing.quarterly}</option>{/if}
{if $product.pricing.semiannually}<option value="semiannually">{$product.pricing.semiannually}</option>{/if}
{if $product.pricing.annually}<option value="annually">{$product.pricing.annually}</option>{/if}
{if $product.pricing.biennially}<option value="biennially">{$product.pricing.biennially}</option>{/if}
{if $product.pricing.triennially}<option value="triennially">{$product.pricing.triennially}</option>{/if}
</select>
{/if}
</td><td width="25%" class="textcenter">
<input type="submit" value="{$LANG.ordernowbutton}"{if $product.qty eq "0"} disabled{/if} />
</td></tr></table>
</form>

</div>
{/foreach}

<p><img align="left" src="images/padlock.gif" border="0" alt="Secure Transaction" style="padding-right: 10px;" /> {$LANG.ordersecure} (<strong>{$ipaddress}</strong>) {$LANG.ordersecure2}</p>

</div>

<div class="clear"></div>

</div>