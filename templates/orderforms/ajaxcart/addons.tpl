{include file='orderforms/ajaxcart/ajaxcartheader.tpl'}

<div id="addonscontainer">

<h2>{$LANG.cartchooseproduct}</h2>

<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td>
{foreach from=$productgroups item=group}<label><input type="radio" name="gid" value="{$group.gid}" onclick="window.location='cart.php?gid={$group.gid}'"{if $group.gid eq $gid} checked{/if} /> {$group.name}</label> {/foreach}
{if $loggedin}<label><input type="radio" name="gid" onclick="window.location='cart.php?gid=addons'" checked /> {$LANG.cartproductaddons}</label>
<label><input type="radio" name="gid" onclick="window.location='cart.php?gid=renewals'" /> {$LANG.domainrenewals}</label> {/if}
{if $registerdomainenabled}<label><input type="radio" name="gid" onclick="window.location='cart.php?a=add&domain=register'" /> {$LANG.registerdomain}</label> {/if}
{if $transferdomainenabled}<label><input type="radio" name="gid" onclick="window.location='cart.php?a=add&domain=transfer'" /> {$LANG.transferdomain}</label>{/if}
</td></tr>
</table>

<h2>{$LANG.cartproductaddons}</h2>

<p>{$LANG.cartfollowingaddonsavailable}</p>

{foreach from=$addons key=num item=addon}
<div class="addoncontainer">
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
<select id="addonpid{$num}">
{foreach from=$addon.productids item=product}
<option value="{$product.id}">{$product.product}{if $product.domain} - {$product.domain}{/if}</option>
{/foreach}
</select> <input type="submit" value="{$LANG.addtocart} &raquo;" onclick="addonaddtocart('{$addon.id}','{$num}')" />
</div>
</div>
</div>
{/foreach}
<div class="clear"></div>

{if $noaddons}
<div class="errorbox">{$LANG.cartproductaddonsnone}</div>
{else}
<div id="checkoutbtn"><input type="button" value="{$LANG.ajaxcartcheckout}" onclick="checkout()" /></div>
{/if}

</div>

<div id="signupcontainer"></div>

{include file='orderforms/ajaxcart/ajaxcartfooter.tpl'}