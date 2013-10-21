<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-verticalsteps">

{include file="orderforms/verticalsteps/verticalsteps.tpl" step=3}

<div class="maincontent">

<h1>{$LANG.cartdomainsconfig}</h1>

<p>{$LANG.cartdomainsconfigdesc}</p>

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?a=confdomains">
<input type="hidden" name="update" value="true" />

{foreach key=num item=domain from=$domains}
<p><strong>{$domain.domain}</strong> - {$domain.regperiod} {$LANG.orderyears} {if $domain.hosting}<span style="color:#009900;">[{$LANG.cartdomainshashosting}]</span>{else}<a href="cart.php" style="color:#cc0000;">[{$LANG.cartdomainsnohosting}]</a><br />{/if}</p>
{if $domain.configtoshow}
<div class="orderbox">
<b class="orderboxrtop"><b class="r1"></b><b class="r2"></b><b class="r3"></b><b class="r4"></b></b>
<div class="orderboxpadding">
{if $domain.eppenabled}{$LANG.domaineppcode} <input type="text" name="epp[{$num}]" size="20" value="{$domain.eppvalue}" /> {$LANG.domaineppcodedesc}<br />{/if}
{if $domain.dnsmanagement}<label><input type="checkbox" name="dnsmanagement[{$num}]"{if $domain.dnsmanagementselected} checked{/if} /> {$LANG.domaindnsmanagement} ({$domain.dnsmanagementprice})</label><br />{/if}
{if $domain.emailforwarding}<label><input type="checkbox" name="emailforwarding[{$num}]"{if $domain.emailforwardingselected} checked{/if} /> {$LANG.domainemailforwarding} ({$domain.emailforwardingprice})</label><br />{/if}
{if $domain.idprotection}<label><input type="checkbox" name="idprotection[{$num}]"{if $domain.idprotectionselected} checked{/if} /> {$LANG.domainidprotection} ({$domain.idprotectionprice})</label><br />{/if}
{foreach key=domainfieldname item=domainfield from=$domain.fields}
{$domainfieldname}: {$domainfield}<br />
{/foreach}
</div>
<b class="orderboxrbottom"><b class="r4"></b><b class="r3"></b><b class="r2"></b><b class="r1"></b></b>
</div>
{/if}
{/foreach}

{if $atleastonenohosting}
<h2>{$LANG.domainnameservers}</h2>
<p>{$LANG.cartnameserversdesc}</p>
<div class="orderbox">
<table class="noborders">
<tr><td>{$LANG.domainnameserver1}:</td><td><input type="text" name="domainns1" size="40" value="{$domainns1}" /></td></tr>
<tr><td>{$LANG.domainnameserver2}:</td><td><input type="text" name="domainns2" size="40" value="{$domainns2}" /></td></tr>
<tr><td>{$LANG.domainnameserver3}:</td><td><input type="text" name="domainns3" size="40" value="{$domainns3}" /></td></tr>
<tr><td>{$LANG.domainnameserver4}:</td><td><input type="text" name="domainns4" size="40" value="{$domainns4}" /></td></tr>
<tr><td>{$LANG.domainnameserver5}:</td><td><input type="text" name="domainns5" size="40" value="{$domainns5}" /></td></tr>
</table>
</div>
{/if}

<p align="center"><input type="submit" value="{$LANG.updatecart}" /></p>

</form>

</div>

</div>