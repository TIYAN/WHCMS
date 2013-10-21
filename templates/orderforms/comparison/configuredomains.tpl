<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<script type="text/javascript" src="templates/orderforms/{$carttpl}/js/main.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/uistyle.css" />

<div id="order-comparison">

{include file="orderforms/comparison/comparisonsteps.tpl" step=2}

<div class="cartcontainer">

{$LANG.cartdomainsconfiginfo}<br /><br />

{if $errormessage}<div class="errorbox">{$LANG.clientareaerrors}<ul>{$errormessage}</ul></div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?a=confdomains">
<input type="hidden" name="update" value="true" />

{foreach key=num item=domain from=$domains}
<h2>{$domain.domain}</h2>
<div class="domainconfig">
<table width="100%" cellspacing="0" cellpadding="0" class="configtable">
<tr><td class="fieldlabel">Hosting:</td><td class="fieldarea">{if $domain.hosting}<span style="color:#009900;">[{$LANG.cartdomainshashosting}]</span>{else}<a href="cart.php" style="color:#cc0000;">[{$LANG.cartdomainsnohosting}]</a><br />{/if}</td></tr>
<tr><td class="fieldlabel">{$LANG.orderregperiod}:</td><td class="fieldarea">{$domain.regperiod} {$LANG.orderyears}</td></tr>
{if $domain.eppenabled}<tr><td class="fieldlabel">{$LANG.domaineppcode}:</td><td class="fieldarea"><input type="text" name="epp[{$num}]" size="20" value="{$domain.eppvalue}" /> {$LANG.domaineppcodedesc}</td></tr>{/if}
{if $domain.dnsmanagement || $domain.emailforwarding || $domain.idprotection}<tr><td class="fieldlabel">{$LANG.cartaddons}:</td><td class="fieldarea">
{if $domain.dnsmanagement}<label><input type="checkbox" name="dnsmanagement[{$num}]"{if $domain.dnsmanagementselected} checked{/if} /> {$LANG.domaindnsmanagement} ({$domain.dnsmanagementprice})</label><br />{/if}
{if $domain.emailforwarding}<label><input type="checkbox" name="emailforwarding[{$num}]"{if $domain.emailforwardingselected} checked{/if} /> {$LANG.domainemailforwarding} ({$domain.emailforwardingprice})</label><br />{/if}
{if $domain.idprotection}<label><input type="checkbox" name="idprotection[{$num}]"{if $domain.idprotectionselected} checked{/if} /> {$LANG.domainidprotection} ({$domain.idprotectionprice})</label><br />{/if}
</td></tr>{/if}
{foreach key=domainfieldname item=domainfield from=$domain.fields}
<tr><td class="fieldlabel">{$domainfieldname}:</td><td class="fieldarea">{$domainfield}</td></tr>
{/foreach}
</table>
</div>
{/foreach}

{if $atleastonenohosting}
<h2>{$LANG.domainnameservers|strtolower}</h2>
<div class="nameservers">
<table width="100%" cellspacing="0" cellpadding="0" class="configtable">
<tr><td class="fieldlabel">{$LANG.cartnameserverchoice}:</td><td class="fieldarea"><label><input type="radio" name="customns" id="usedefaultns" checked /> {$LANG.cartnameserverchoicedefault}</label><br /><label><input type="radio" name="customns" id="usecustomns" /> {$LANG.cartnameserverchoicecustom}</label></td></tr>
<tr><td class="fieldlabel">{$LANG.domainnameserver1}:</td><td class="fieldarea"><input type="text" name="domainns1" size="40" value="{$domainns1}" /></td></tr>
<tr><td class="fieldlabel">{$LANG.domainnameserver2}:</td><td class="fieldarea"><input type="text" name="domainns2" size="40" value="{$domainns2}" /></td></tr>
<tr><td class="fieldlabel">{$LANG.domainnameserver3}:</td><td class="fieldarea"><input type="text" name="domainns3" size="40" value="{$domainns3}" /></td></tr>
<tr><td class="fieldlabel">{$LANG.domainnameserver4}:</td><td class="fieldarea"><input type="text" name="domainns4" size="40" value="{$domainns4}" /></td></tr>
<tr><td class="fieldlabel">{$LANG.domainnameserver5}:</td><td class="fieldarea"><input type="text" name="domainns5" size="40" value="{$domainns5}" /></td></tr>
</table>
</div>
{/if}

<p align="center"><input type="submit" value="{$LANG.updatecart}" class="cartbutton green" /></p>

</div>

</form>

</div>