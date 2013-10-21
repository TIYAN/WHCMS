<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-boxes">

{if !$loggedin && $currencies}
<form method="post" action="cart.php?a=add&domain=register">
<p align="right">{$LANG.choosecurrency}: <select name="currency" onchange="submit()">{foreach from=$currencies item=curr}
<option value="{$curr.id}"{if $curr.id eq $currency.id} selected{/if}>{$curr.code}</option>
{/foreach}</select> <input type="submit" value="{$LANG.go}" /></p>
</form>
{/if}

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?a=add">

<table width="90%" align="center" cellspacing="1" cellpadding="5">
<tr class="orderheadingrow"><td colspan="2"></td></tr>

{if $registerdomainenabled}
<tr class="orderrow1"><td width="10"><input type="radio" name="domain" value="register"{if $domain eq "register"} checked{/if} /></td><td><label for="selregister">{$LANG.orderdomainoption1part1} {$companyname} {$LANG.orderdomainoption1part2}</label></td></tr>
{/if}

{if $transferdomainenabled}
<tr class="orderrow2"><td width="10"><input type="radio" name="domain" value="transfer"{if $domain eq "transfer"} checked{/if} /></td><td><label for="seltransfer">{$LANG.orderdomainoption3} {$companyname}</label></td></tr>
{/if}

<tr class="orderheadingrow"><td colspan="2"></td></tr>
</table>

<br />

<div class="textcenter">
www. <input type="text" name="sld" size="40" value="{$sld}" /> <select name="tld">
{foreach key=num item=listtld from=$tlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select>
</div>

<br />

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" /></p>

</form>

{if $availabilityresults}

<form method="post" action="{$smarty.server.PHP_SELF}?a=add&domain={$domain}">

<div class="center90">
<table class="styled">
<tr><th>{$LANG.domainname}</th><th>{$LANG.domainstatus}</th><th>{$LANG.domainmoreinfo}</th></tr>
{foreach key=num item=result from=$availabilityresults}
<tr class="clientareatableactive"><td>{$result.domain}</td><td class="{if $result.status eq $searchvar}textgreen{else}textred{/if}">{if $result.status eq $searchvar}<input type="checkbox" name="domains[]" value="{$result.domain}"{if $result.domain|in_array:$domains} checked{/if} /> {$LANG.domainavailable}{else}{$LANG.domainunavailable}{/if}</td><td>{if $result.regoptions}<select name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}{if $regoption.$domain}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.$domain}</option>{/if}{/foreach}</select>{/if}</td></tr>
{/foreach}
</table>
</div>

<p align="center"><input type="submit" value="{$LANG.addtocart}" /></p>

</form>

{/if}

<p align="right"><input type="button" value="{$LANG.viewcart}" onclick="window.location='cart.php?a=view'" /></p>

</div>