<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-verticalsteps">

{include file="orderforms/verticalsteps/verticalsteps.tpl" step=2}

<div class="maincontent">

<div class="left">

<form method="get" action="{$smarty.server.PHP_SELF}">
<p>{$LANG.ordercategories}: <select name="gid" onchange="submit()">
{foreach key=num item=productgroup from=$productgroups}
<option value="{$productgroup.gid}">{$productgroup.name}</option>
{/foreach}
{if $loggedin}
<option value="addons">{$LANG.cartproductaddons}</option>
<option value="renewals" selected="selected">{$LANG.domainrenewals}</option>
{/if}
{if $registerdomainenabled}<option value="domains">{$LANG.orderdomainregonly}</option>{/if}
</select></p>
</form>

</div>

<br /><br /><br />

<p>{$LANG.domainrenewdesc}</p>

<form method="post" action="cart.php?a=add&renewals=true">

<table class="styled textcenter">
<tr><th width="20"></th><th>{$LANG.orderdomain}</th><th>{$LANG.domainstatus}</th><th>{$LANG.domaindaysuntilexpiry}</th><th></th></tr>
{foreach from=$renewals item=renewal}
<tr><td>{if !$renewal.pastgraceperiod}<input type="checkbox" name="renewalids[]" value="{$renewal.id}" />{/if}</td><td>{$renewal.domain}</td><td>{$renewal.status}</td><td>
      {if $renewal.daysuntilexpiry > 30}
        <span class="textgreen">{$renewal.daysuntilexpiry} {$LANG.domainrenewalsdays}</span>
      {elseif $renewal.daysuntilexpiry > 0}
        <span class="textred">{$renewal.daysuntilexpiry} {$LANG.domainrenewalsdays}</span>
      {else}
        <span class="textblack">{$renewal.daysuntilexpiry*-1} {$LANG.domainrenewalsdaysago}</span>
      {/if}
      {if $renewal.ingraceperiod}
        <br />
        <span class="textred">{$LANG.domainrenewalsingraceperiod}<span>
      {/if}
</td><td>
      {if $renewal.beforerenewlimit}
        <span class="textred">{$LANG.domainrenewalsbeforerenewlimit|sprintf2:$renewal.beforerenewlimitdays}<span>
      {elseif $renewal.pastgraceperiod}
        <span class="textred">{$LANG.domainrenewalspastgraceperiod}<span>
      {else}
        <select name="renewalperiod[{$renewal.id}]">
        {foreach from=$renewal.renewaloptions item=renewaloption}
          <option value="{$renewaloption.period}">{$renewaloption.period} {$LANG.orderyears} @ {$renewaloption.price}</option>
        {/foreach}
        </select>
      {/if}
</td></tr>
{foreachelse}
<tr><td colspan="5">{$LANG.domainrenewalsnoneavailable}</td></tr>
{/foreach}
</table>

<p align="center"><input type="submit" value="{$LANG.ordernowbutton}" /></p>

</form>

</div>

</div>