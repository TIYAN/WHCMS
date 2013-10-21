<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-cart">

<h1>{if $domain eq "register"}{$LANG.registerdomainname}{else}{$LANG.transferdomainname}{/if}</h1>

{if !$loggedin && $currencies}
<form method="post" action="cart.php">
<p align="right">{$LANG.choosecurrency}: <select name="currency" onchange="submit()">{foreach from=$currencies item=curr}
<option value="{$curr.id}"{if $curr.id eq $currency.id} selected{/if}>{$curr.code}</option>
{/foreach}</select> <input type="submit" value="{$LANG.go}" /></p>
</form>
{/if}

<div class="cartbox" align="center"><strong>
{foreach key=num item=productgroup from=$productgroups}
{if $gid eq $productgroup.gid}
{$productgroup.name} | 
{else}
<a href="{$smarty.server.PHP_SELF}?gid={$productgroup.gid}">{$productgroup.name}</a> | 
{/if}
{/foreach}
{if $loggedin}
<a href="{$smarty.server.PHP_SELF}?gid=addons">{$LANG.cartproductaddons}</a> |
{if $renewalsenabled}<a href="{$smarty.server.PHP_SELF}?gid=renewals">{$LANG.domainrenewals}</a> | {/if}
{/if}
{if $registerdomainenabled}{if $domain neq "register"}<a href="{$smarty.server.PHP_SELF}?a=add&domain=register">{/if}{$LANG.registerdomain}</a> | {/if}
{if $transferdomainenabled}{if $domain neq "transfer"}<a href="{$smarty.server.PHP_SELF}?a=add&domain=transfer">{/if}{$LANG.transferdomain}</a> | {/if}
<a href="{$smarty.server.PHP_SELF}?a=view">{$LANG.viewcart}</a>
</strong></div>

<p>{if $domain eq "register"}{$LANG.registerdomaindesc}{else}{$LANG.transferdomaindesc}{/if}</p>

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?a=add&domain={$domain}">

<div class="cartbox" align="center">www. <input type="text" name="sld" size="40" value="{$sld}" /> <select name="tld">
{foreach key=num item=listtld from=$tlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select> <input type="submit" value="{$LANG.checkavailability}" /></div>

<p align="center"></p>

</form>

{if $availabilityresults}

<h1>{$LANG.choosedomains}</h1>

<form method="post" action="{$smarty.server.PHP_SELF}?a=add&domain={$domain}">

<table class="domains">
<tr class="carttableheading"><td>{$LANG.domainname}</td><td>{$LANG.domainstatus}</td><td>{$LANG.domainmoreinfo}</td></tr>
{foreach key=num item=result from=$availabilityresults}
<tr class="carttablerow"><td>{$result.domain}</td><td class="{if $result.status eq $searchvar}textgreen{else}textred{/if}">{if $result.status eq $searchvar}<input type="checkbox" name="domains[]" value="{$result.domain}"{if $result.domain|in_array:$domains} checked{/if} /> {$LANG.domainavailable}{else}{$LANG.domainunavailable}{/if}</td><td>{if $result.regoptions}<select name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}{if $regoption.$domain}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.$domain}</option>{/if}{/foreach}</select>{/if}</td></tr>
{/foreach}
</table>

<p align="center"><input type="submit" value="{$LANG.addtocart}" /></p>

</form>

{/if}

<p align="right"><input type="button" value="{$LANG.viewcart}" onclick="window.location='cart.php?a=view'" /></p>

</div>