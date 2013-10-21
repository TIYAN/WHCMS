{if $bulkdomainsearchenabled}<p align="center"><a href="domainchecker.php">{$LANG.domainsimplesearch}</a> | <a href="domainchecker.php?search=bulkregister">{$LANG.domainbulksearch}</a> | <a href="domainchecker.php?search=bulktransfer">{$LANG.domainbulktransfersearch}</a></p>{/if}

{if !$loggedin && $currencies}
<form method="post" action="domainchecker.php">
<p align="right">{$LANG.choosecurrency}: <select name="currency" onchange="submit()">{foreach from=$currencies item=curr}
<option value="{$curr.id}"{if $curr.id eq $currency.id} selected{/if}>{$curr.code}</option>
{/foreach}</select> <input type="submit" value="{$LANG.go}" /></p>
</form>
{/if}

{if $inccode}<div class="errorbox">{$LANG.captchaverifyincorrect}</div>{/if}

<p>{$LANG.domainintrotext}</p>

<form method="post" action="domainchecker.php">

<div class="contentbox" align="center">

www. <input type="text" name="domain" value="{$domain}" size="40"><br />
<table align="center"><tr>
{foreach key=num item=listtld from=$tldslist}<td align="left"><input type="checkbox" name="tlds[]" value="{$listtld}"{if in_array($listtld,$tlds)} checked{/if}>{$listtld}</td>{if $num % 5 == 0}</tr><tr>{/if}{/foreach}
</tr></table>

{if $capatacha}
{if $capatacha eq "recaptcha"}
<p>{$LANG.captchaverify}</p>
<div align="center">{$recapatchahtml}</div>
<br />
{else}
<img src="includes/verifyimage.php" align="middle" /> <input type="text" name="code" size="10" maxlength="5" /> &nbsp;&nbsp;&nbsp;
{/if}
{/if}

<input type="submit" value="{$LANG.domainlookupbutton}" class="button" />

</div>

</form>

<br />

{if $lookup}

{if $available}
	<p align="center" class="textgreen" style="font-size:18px;">{$LANG.domainavailable1} <strong>{$domain}{$ext}</strong> {$LANG.domainavailable2}</p>
{elseif $invalid}
	<p align="center" class="textred" style="font-size:18px;">{$LANG.ordererrordomaininvalid}</p>
{elseif $error}
	<p align="center" class="textred" style="font-size:18px;">{$LANG.domainerror}</p>
{else}
	<p align="center" class="textred" style="font-size:18px;">{$LANG.domainunavailable1} <strong>{$domain}{$ext}</strong> {$LANG.domainunavailable2}</p>
{/if}

{if !$invalid}

<p><strong>{$LANG.morechoices}</strong></p>
<form method="post" action="{$systemsslurl}cart.php?a=add&domain=register">
<table class="clientareatable" cellspacing="1">
<tr class="clientareatableheading"><td width="20"></td><td>{$LANG.domainname}</td><td>{$LANG.domainstatus}</td><td>{$LANG.domainmoreinfo}</td></tr>
{foreach key=num item=result from=$availabilityresults}
<tr class="clientareatableactive"><td>{if $result.status eq "available"}<input type="checkbox" name="domains[]" value="{$result.domain}" {if $num eq "0" && $available}checked {/if}/><input type="hidden" name="domainsregperiod[{$result.domain}]" value="{$result.period}" />{else}X{/if}</td><td>{$result.domain}</td><td class="{if $result.status eq "available"}textgreen{else}textred{/if}">{if $result.status eq "available"}{$LANG.domainavailable}{else}{$LANG.domainunavailable}{/if}</td><td>{if $result.status eq "unavailable"}<a href="http://{$result.domain}" target="_blank">WWW</a> <a href="#" onclick="window.open('whois.php?domain={$result.domain}','whois','width=500,height=400,scrollbars=yes');return false">WHOIS</a>{else}<select name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>{/foreach}</select>{/if}</td></tr>
{/foreach}
</table>
<p align="center"><input type="submit" value="{$LANG.ordernowbutton} >>" class="buttongo" /></p>
</form>

{/if}

{else}

<p align="center"><strong>{$LANG.domainspricing}</strong></p>

<table class="clientareatable" cellspacing="1">
<tr class="clientareatableheading"><td>{$LANG.domaintld}</td><td>{$LANG.domainminyears}</td><td>{$LANG.domainsregister}</td><td>{$LANG.domainstransfer}</td><td>{$LANG.domainsrenew}</td></tr>
{foreach key=num item=tldpricelist from=$tldpricelist}
<tr class="clientareatableactive"><td>{$tldpricelist.tld}</td><td>{$tldpricelist.period}</td><td>{if $tldpricelist.register}{$tldpricelist.register}{else}{$LANG.domainregnotavailable}{/if}</td><td>{if $tldpricelist.transfer}{$tldpricelist.transfer}{else}{$LANG.domainregnotavailable}{/if}</td><td>{if $tldpricelist.renew}{$tldpricelist.renew}{else}{$LANG.domainregnotavailable}{/if}</td></tr>
{/foreach}
</table>

{/if}