<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-verticalsteps">

{include file="orderforms/verticalsteps/verticalsteps.tpl" step=2}

<div class="maincontent">

<h1>{$productinfo.groupname} - {$productinfo.name}</h1>

<p>{$LANG.cartproductdomaindesc}</p>

<form method="post" action="{$smarty.server.PHP_SELF}?a=add&pid={$pid}">
{foreach from=$passedvariables key=name item=value}
<input type="hidden" name="{$name}" value="{$value}" />
{/foreach}

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<div class="domainoptions">

{if $incartdomains}
<div><label><input type="radio" name="domainoption" value="incart" id="selincart" onclick="document.getElementById('register').style.display='none';document.getElementById('transfer').style.display='none';document.getElementById('owndomain').style.display='none';document.getElementById('subdomain').style.display='none';document.getElementById('incart').style.display=''" /> {$LANG.cartproductdomainuseincart}</label></div>
{/if}

{if $registerdomainenabled}
<div><label><input type="radio" name="domainoption" value="register" id="selregister" onclick="document.getElementById('register').style.display='';document.getElementById('transfer').style.display='none';document.getElementById('owndomain').style.display='none';document.getElementById('subdomain').style.display='none';document.getElementById('incart').style.display='none'" /> {$LANG.orderdomainoption1part1} {$companyname} {$LANG.orderdomainoption1part2}</label></div>
{/if}

{if $transferdomainenabled}
<div><label><input type="radio" name="domainoption" value="transfer" id="seltransfer" onclick="document.getElementById('register').style.display='none';document.getElementById('transfer').style.display='';document.getElementById('owndomain').style.display='none';document.getElementById('subdomain').style.display='none';document.getElementById('incart').style.display='none'" /> {$LANG.orderdomainoption3} {$companyname}</label></div>
{/if}

{if $owndomainenabled}
<div><label><input type="radio" name="domainoption" value="owndomain" id="selowndomain" onclick="document.getElementById('register').style.display='none';document.getElementById('transfer').style.display='none';document.getElementById('owndomain').style.display='';document.getElementById('subdomain').style.display='none';document.getElementById('incart').style.display='none'" /> {$LANG.orderdomainoption2}</label></div>
{/if}

{if $subdomains}
<div><label><input type="radio" name="domainoption" value="subdomain" id="selsubdomain" onclick="document.getElementById('register').style.display='none';document.getElementById('transfer').style.display='none';document.getElementById('owndomain').style.display='none';document.getElementById('subdomain').style.display='';document.getElementById('incart').style.display='none'" /> {$LANG.orderdomainoption4}</label></div>
{/if}

</div>

<div class="orderbox">

<div id="incart" align="center">{$LANG.cartproductdomainchoose}: <select name="incartdomain">
{foreach key=num item=incartdomain from=$incartdomains}
<option value="{$incartdomain}">{$incartdomain}</option>
{/foreach}
</select>
</div>

<div id="register" align="center">www. <input type="text" name="sld[0]" size="40" value="{$sld}" /> <select name="tld[0]">
{foreach key=num item=listtld from=$registertlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select>
</div>

<div id="transfer" align="center">www. <input type="text" name="sld[1]" size="40" value="{$sld}" /> <select name="tld[1]">
{foreach key=num item=listtld from=$transfertlds}
<option value="{$listtld}"{if $listtld eq $tld} selected="selected"{/if}>{$listtld}</option>
{/foreach}
</select>
</div>

<div id="owndomain" align="center">www. <input type="text" name="sld[2]" size="40" value="{$sld}" /> . <input type="text" name="tld[2]" size="7" value="{$tld}" /></div>

<div id="subdomain" align="center">http:// <input type="text" name="sld[3]" size="40" value="{$sld}" /> <select name="tld[3]">{foreach from=$subdomains key=subid item=subdomain}<option value="{$subid}">{$subdomain}</option>{/foreach}</select></div>

</div>

<script language="javascript" type="text/javascript">
document.getElementById('incart').style.display='none';
document.getElementById('register').style.display='none';
document.getElementById('transfer').style.display='none';
document.getElementById('owndomain').style.display='none';
document.getElementById('subdomain').style.display='none';
document.getElementById('sel{$domainoption}').checked='true';
document.getElementById('{$domainoption}').style.display='';
</script>

{if $availabilityresults}

<h2>{$LANG.choosedomains}</h2>

<table class="styled textcenter">
<tr><th>{$LANG.domainname}</th><th>{$LANG.domainstatus}</th><th>{$LANG.domainmoreinfo}</th></tr>
{foreach key=num item=result from=$availabilityresults}
<tr><td>{$result.domain}</td><td class="{if $result.status eq $searchvar}textgreen{else}textred{/if}">{if $result.status eq $searchvar}<input type="checkbox" name="domains[]" value="{$result.domain}"{if $num eq 0} checked{/if} /> {$LANG.domainavailable}{else}{$LANG.domainunavailable}{/if}</td><td>{if $result.regoptions}<select name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}{if $regoption.$domainoption}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.$domainoption}</option>{/if}{/foreach}</select>{/if}</td></tr>
{/foreach}
</table>

{/if}

{if $freedomaintlds}* <em>{$LANG.orderfreedomainregistration} {$LANG.orderfreedomainappliesto}: {$freedomaintlds}</em>{/if}

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" /></p>

</form>

</div>

</div>