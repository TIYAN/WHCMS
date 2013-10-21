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
{if $renewalsenabled}<option value="renewals">{$LANG.domainrenewals}</option>{/if}
{/if}
{if $registerdomainenabled}<option value="domains" selected="selected">{$LANG.orderdomainregonly}</option>{/if}
</select></p>
</form>

</div>
<div class="right">

{if !$loggedin && $currencies}
<form method="post" action="cart.php?a=add&domain=register">
<p align="right">{$LANG.choosecurrency}: <select name="currency" onchange="submit()">{foreach from=$currencies item=curr}
<option value="{$curr.id}"{if $curr.id eq $currency.id} selected{/if}>{$curr.code}</option>
{/foreach}</select> <input type="submit" value="{$LANG.go}" /></p>
</form>
{/if}

</div>

<br /><br /><br />

<form method="post" action="{$smarty.server.PHP_SELF}?a=add">

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div>{/if}

<div class="domainoptions">

<div><label><input type="radio" name="domain" value="register" id="selregister"{if $domain eq "register"} checked{/if} /> {$LANG.orderdomainoption1part1} {$companyname} {$LANG.orderdomainoption1part2}</label></div>

<div><label><input type="radio" name="domain" value="transfer" id="seltransfer"{if $domain eq "transfer"} checked{/if} /> {$LANG.orderdomainoption3} {$companyname}</label></div>

</div>

<div class="orderbox textcenter">

www. <input type="text" name="sld" size="40" value="{$sld}" /> <select name="tld">
{foreach key=num item=listtld from=$tlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select>

</div>

<p align="center"><input type="submit" value="{$LANG.checkavailability}" /></p>

</form>

{if $availabilityresults}

<h2>{$LANG.choosedomains}</h2>

<form method="post" action="{$smarty.server.PHP_SELF}?a=add&domain={$domain}">

<table class="styled textcenter">
<tr><th>{$LANG.domainname}</th><th>{$LANG.domainstatus}</th><th>{$LANG.domainmoreinfo}</th></tr>
{foreach key=num item=result from=$availabilityresults}
<tr><td>{$result.domain}</td><td class="{if $result.status eq $searchvar}textgreen{else}textred{/if}">{if $result.status eq $searchvar}<input type="checkbox" name="domains[]" value="{$result.domain}"{if $result.domain|in_array:$domains} checked{/if} /> {$LANG.domainavailable}{else}{$LANG.domainunavailable}{/if}</td><td>{if $result.regoptions}<select name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}{if $regoption.$domain}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.$domain}</option>{/if}{/foreach}</select>{/if}</td></tr>
{/foreach}
</table>

<p align="center"><input type="submit" value="{$LANG.addtocart}" /></p>

</form>

{/if}

</div>

</div>