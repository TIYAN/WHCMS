<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-web20cart">

<h1>{$LANG.cartproductaddons}</h1>

<div class="cartmenu" align="center"> {foreach key=num item=productgroup from=$productgroups}
  {if $gid eq $productgroup.gid}
  {$productgroup.name} | 
  {else} <a href="{$smarty.server.PHP_SELF}?gid={$productgroup.gid}">{$productgroup.name}</a> | 
  {/if}
  {/foreach}
  {if $loggedin}
  <strong>{$LANG.cartproductaddons} </strong>|
  {if $renewalsenabled}<a href="{$smarty.server.PHP_SELF}?gid=renewals">{$LANG.domainrenewals}</a> | {/if}
  {/if}
  {if $registerdomainenabled}<a href="{$smarty.server.PHP_SELF}?a=add&domain=register">{$LANG.registerdomain}</a> |{/if}
  {if $transferdomainenabled}<a href="{$smarty.server.PHP_SELF}?a=add&domain=transfer">{$LANG.transferdomain}</a> |{/if} <a href="{$smarty.server.PHP_SELF}?a=view">{$LANG.viewcart}</a> </div>

{foreach from=$addons item=addon}
<div class="cartbox" align="center">
    <form method="post" action="{$smarty.server.PHP_SELF}?a=add">
        <table class="noborders">
            <tr>
                <td width="40%">
        <input type="hidden" name="aid" value="{$addon.id}" />
        <strong>{$addon.name}</strong><br />
        {$addon.description}<br />
        {$LANG.cartchooseproduct}: <select name="productid">{foreach from=$addon.productids item=product}
        <option value="{$product.id}">{$product.product}{if $product.domain} - {$product.domain}{/if}</option>
        {/foreach}</select></td>
                <td width="40%" class="pricing textcenter"> {if $addon.free}
        {$LANG.orderfree}
        {else}
        {$addon.recurringamount} {$addon.billingcycle}
        {if $addon.setupfee}<br />+ {$addon.setupfee} {$LANG.ordersetupfee}{/if}
        {/if}</td>
                <td width="20%" class="textcenter"><input type="submit" value="{$LANG.ordernowbutton}" /></td>
            </tr>
        </table>
    </form>
</div>
{/foreach}

{if $noaddons}
<div class="errorbox textcenter">{$LANG.cartproductaddonsnone}</div>
<br />
{/if}

<p align="center"><input type="button" value="{$LANG.viewcart}" onclick="window.location='cart.php?a=view'" /></p>

</div>