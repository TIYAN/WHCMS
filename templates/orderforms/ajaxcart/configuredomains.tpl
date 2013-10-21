<h2>{$LANG.cartdomainsconfig}</h2>

<p>{$LANG.cartdomainsconfiginfo}</p>

<div id="configdomainerror" class="errorbox hidden"></div>

<form id="domainconfigfrm" onsubmit="completedomainconfig();return false">

{foreach key=num item=domain from=$domains}
<p class="domainconfigtitle"><b>{$domain.domain}</b></p>
<table width="100%" cellspacing="0" cellpadding="0">
{if $domain.eppenabled}<tr class="{cycle values="rowcolor1,rowcolor2"}"><td class="fieldlabel">{$LANG.domaineppcode}:</td><td class="fieldarea"><input type="text" name="epp[{$num}]" size="20" value="{$domain.eppvalue}" /> {$LANG.domaineppcodedesc}</td></tr>{/if}
{if $domain.dnsmanagement}<label><input type="checkbox" onclick="domainconfigupdate();" name="dnsmanagement[{$num}]"{if $domain.dnsmanagementselected} checked{/if} /> {$LANG.domaindnsmanagement} ({$domain.dnsmanagementprice})</label><br />{/if}
{if $domain.emailforwarding}<label><input type="checkbox" onclick="domainconfigupdate();" name="emailforwarding[{$num}]"{if $domain.emailforwardingselected} checked{/if} /> {$LANG.domainemailforwarding} ({$domain.emailforwardingprice})</label><br />{/if}
{if $domain.idprotection}<label><input type="checkbox" onclick="domainconfigupdate();" name="idprotection[{$num}]"{if $domain.idprotectionselected} checked{/if} /> {$LANG.domainidprotection} ({$domain.idprotectionprice})</label><br />{/if}
</td></tr>
{foreach key=domainfieldname item=domainfield from=$domain.fields}
<tr class="{cycle values="rowcolor1,rowcolor2"}"><td class="fieldlabel">{$domainfieldname}:</td><td class="fieldarea">{$domainfield}</td></tr>
{/foreach}
</table>
{/foreach}

{if $atleastonenohosting}
<h2>{$LANG.domainnameservers}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td class="fieldlabel">{$LANG.cartnameserverchoice}:</td><td class="fieldarea"><label><input type="radio" name="customns" id="usedefaultns" onclick="showcustomns()" checked /> {$LANG.cartnameserverchoicedefault}</label><br /><label><input type="radio" name="customns" id="usecustomns" onclick="showcustomns()" /> {$LANG.cartnameserverchoicecustom}</label></td></tr>
<tr class="rowcolor2 hiddenns"><td class="fieldlabel">{$LANG.domainnameserver1}:</td><td class="fieldarea"><input type="text" name="domainns1" size="40" value="{$domainns1}" /></td></tr>
<tr class="rowcolor1 hiddenns"><td class="fieldlabel">{$LANG.domainnameserver2}:</td><td class="fieldarea"><input type="text" name="domainns2" size="40" value="{$domainns2}" /></td></tr>
<tr class="rowcolor2 hiddenns"><td class="fieldlabel">{$LANG.domainnameserver3}:</td><td class="fieldarea"><input type="text" name="domainns3" size="40" value="{$domainns3}" /></td></tr>
<tr class="rowcolor1 hiddenns"><td class="fieldlabel">{$LANG.domainnameserver4}:</td><td class="fieldarea"><input type="text" name="domainns4" size="40" value="{$domainns4}" /></td></tr>
<tr class="rowcolor2 hiddenns"><td class="fieldlabel">{$LANG.domainnameserver5}:</td><td class="fieldarea"><input type="text" name="domainns5" size="40" value="{$domainns5}" /></td></tr>
</table>
{/if}

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" /></p>

</form>

<div id="domainconfloading" class="loading"><img src="images/loading.gif" border="0" alt="{$LANG.loading}" /></div>