<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-boxes">

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
</select></p>
</form>

</div>
<div class="right">

{if !$loggedin && $currencies}
<form method="post" action="cart.php?gid={$smarty.get.gid}">
<p align="right">{$LANG.choosecurrency}: <select name="currency" onchange="submit()">{foreach from=$currencies item=curr}
<option value="{$curr.id}"{if $curr.id eq $currency.id} selected{/if}>{$curr.code}</option>
{/foreach}</select> <input type="submit" value="{$LANG.go}" /></p>
</form>
{/if}

</div>
<div class="clear"></div>

<form method="post" action="{$smarty.server.PHP_SELF}?a=add">

<table width="90%" align="center" cellspacing="1" cellpadding="5">
<tr class="orderheadingrow"><td colspan="2"></td></tr>
{foreach key=num item=product from=$products}
<tr class="{cycle values="orderrow1,orderrow2"}"><td width="10"><input type="radio" name="pid" value="{if $product.bid}b{$product.bid}{else}{$product.pid}{/if}"{if $product.qty eq "0"} disabled{/if} /></td><td><strong>{$product.name}</strong> {if $product.qty!=""}<em>({$product.qty} {$LANG.orderavailable})</em>{/if}{if $product.description} - {$product.description}{/if}</td></tr>
{/foreach}
<tr class="orderheadingrow"><td colspan="2"></td></tr>
</table>

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" /></p>

</form>

{if $registerdomainenabled}<p align="center"><a href="{$smarty.server.PHP_SELF}?a=add&domain=register">{$LANG.orderdomainregonly}</a></p>{/if}

<p><img align="left" src="images/padlock.gif" border="0" alt="Secure Transaction" style="padding-right: 10px;" /> {$LANG.ordersecure} (<strong>{$ipaddress}</strong>) {$LANG.ordersecure2}</p>

{php}
if (isset($_SESSION["cart"])) {
    unset($_SESSION["cart"]);
}
{/php}

</div>