<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-boxes">

<p>{$LANG.cartdomainsconfigdesc}</p>

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?a=confdomains">
<input type="hidden" name="update" value="true" />

<table width="90%" align="center" cellspacing="1" cellpadding="5">

{foreach key=num item=domain from=$domains}

<tr><td colspan="2"><strong>{$domain.domain} - {$domain.regperiod} {$LANG.orderyears} {if $domain.hosting}<span style="color:#009900;">[{$LANG.cartdomainshashosting}]</span>{else}<a href="cart.php" style="color:#cc0000;">[{$LANG.cartdomainsnohosting}]</a><br />{/if}</strong></td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
{if $domain.configtoshow}
{if $domain.eppenabled}<tr class="orderrow1"><td class="leftcol">{$LANG.domaineppcode}</td><td><input type="text" name="epp[{$num}]" size="20" value="{$domain.eppvalue}" /> {$LANG.domaineppcodedesc}</td></tr>{/if}
{if $domain.dnsmanagement}<tr class="orderrow2"><td class="leftcol">{$LANG.domaindnsmanagement}</td><td><label><input type="checkbox" name="dnsmanagement[{$num}]"{if $domain.dnsmanagementselected} checked{/if} /> {$domain.dnsmanagementprice}</label></td></tr>{/if}
{if $domain.emailforwarding}<tr class="orderrow1"><td class="leftcol">{$LANG.domainemailforwarding}</td><td><label><input type="checkbox" name="emailforwarding[{$num}]"{if $domain.emailforwardingselected} checked{/if} /> {$domain.emailforwardingprice}</label></td></tr>{/if}
{if $domain.idprotection}<tr class="orderrow2"><td class="leftcol">{$LANG.domainidprotection}</td><td><label><input type="checkbox" name="idprotection[{$num}]"{if $domain.idprotectionselected} checked{/if} /> {$domain.idprotectionprice}</label></td></tr>{/if}
{foreach key=domainfieldname item=domainfield from=$domain.fields}
<tr class="orderrow1"><td class="leftcol">{$domainfieldname}:</td><td>{$domainfield}</td></tr>
{/foreach}
{/if}
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr><td height="10"></td></tr>

{/foreach}

</table>

{if $atleastonenohosting}
<table width="90%" align="center" cellspacing="1" cellpadding="5">
<tr><td colspan="2"><strong>{$LANG.domainnameservers}</strong><br />{$LANG.cartnameserversdesc}</td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr class="orderrow1"><td>{$LANG.domainnameserver1}:</td><td><input type="text" name="domainns1" size="40" value="{$domainns1}" /></td></tr>
<tr class="orderrow2"><td>{$LANG.domainnameserver2}:</td><td><input type="text" name="domainns2" size="40" value="{$domainns2}" /></td></tr>
<tr class="orderrow1"><td>{$LANG.domainnameserver3}:</td><td><input type="text" name="domainns3" size="40" value="{$domainns3}" /></td></tr>
<tr class="orderrow2"><td>{$LANG.domainnameserver4}:</td><td><input type="text" name="domainns4" size="40" value="{$domainns4}" /></td></tr>
<tr class="orderrow2"><td>{$LANG.domainnameserver5}:</td><td><input type="text" name="domainns5" size="40" value="{$domainns5}" /></td></tr>
<tr class="orderheadingrow"><td colspan="2"></td></tr>
<tr><td height="10"></td></tr>
</table>
{/if}

<p align="center"><input type="submit" value="{$LANG.updatecart}" /></p>

</form>

</div>