{include file='orderforms/ajaxcart/ajaxcartheader.tpl'}

<div id="renewalscontainer">

<h2>{$LANG.cartchooseproduct}</h2>

<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td>
{foreach from=$productgroups item=group}<label><input type="radio" name="gid" value="{$group.gid}" onclick="window.location='cart.php?gid={$group.gid}'"{if $group.gid eq $gid} checked{/if} /> {$group.name}</label> {/foreach}
{if $loggedin}<label><input type="radio" name="gid" onclick="window.location='cart.php?gid=addons'" /> {$LANG.cartproductaddons}</label>
<label><input type="radio" name="gid" onclick="window.location='cart.php?gid=renewals'" checked /> {$LANG.domainrenewals}</label> {/if}
{if $registerdomainenabled}<label><input type="radio" name="gid" onclick="window.location='cart.php?a=add&domain=register'" /> {$LANG.registerdomain}</label> {/if}
{if $transferdomainenabled}<label><input type="radio" name="gid" onclick="window.location='cart.php?a=add&domain=transfer'" /> {$LANG.transferdomain}</label>{/if}
</td></tr>
</table>

<h2>{$LANG.domainrenewals}</h2>

<p>{$LANG.domainrenewdesc}</p>

{if $renewals}
<div id="checkoutbtn"><input type="button" value="{$LANG.ajaxcartcheckout}" onclick="checkout()" /></div>
{/if}

{foreach from=$renewals key=num item=renewal}
<div class="addoncontainer">
<div class="addon">
<div class="title">{$renewal.domain}</div>
<div class="pricing">
    {if $renewal.daysuntilexpiry > 30}
    <span class="textgreen">{$renewal.daysuntilexpiry} {$LANG.domainrenewalsdays}</span>
    {elseif $renewal.daysuntilexpiry > 0}
    <span class="textred">{$renewal.daysuntilexpiry} {$LANG.domainrenewalsdays}</span>
    {else}
    <span class="textblack">{$renewal.daysuntilexpiry*-1} {$LANG.domainrenewalsdaysago}</span>
    {/if}
    <br />
    <span style="font-size:11px;color: #000;">{$LANG.domaindaysuntilexpiry}</span>
</div>
<div class="clear"></div>
{$addon.description}
<div class="product">
<select id="renewalperiod{$num}">
    {foreach from=$renewal.renewaloptions item=renewaloption}
    <option value="{$renewaloption.period}">{$renewaloption.period} {$LANG.orderyears} @ {$renewaloption.price}</option>
    {/foreach}
</select> <input type="submit" value="{$LANG.addtocart} &raquo;" onclick="renewaladdtocart('{$renewal.id}','{$num}')" />
</div>
</div>
</div>
{foreachelse}
<div class="errorbox">{$LANG.domainrenewalsnoneavailable}</div>
{/foreach}
<div class="clear"></div>

{if $renewals}
<div id="checkoutbtn"><input type="button" value="{$LANG.ajaxcartcheckout}" onclick="checkout()" /></div>
{/if}

</div>

<div id="signupcontainer"></div>

{include file='orderforms/ajaxcart/ajaxcartfooter.tpl'}