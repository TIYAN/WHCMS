{if $bulkdomainsearchenabled}<p align="center"><a href="domainchecker.php">{$LANG.domainsimplesearch}</a> | <a href="domainchecker.php?search=bulkregister">{$LANG.domainbulksearch}</a> | <a href="domainchecker.php?search=bulktransfer">{$LANG.domainbulktransfersearch}</a></p>{/if}

{if $inccode}<div class="errorbox">{$LANG.captchaverifyincorrect}</div>{/if}

<p>{$LANG.domainbulktransferdescription}</p>

<form method="post" action="domainchecker.php?search=bulktransfer">

<div class="contentbox" align="center">

<p align="center"><textarea name="bulkdomains" cols="60" rows="8">{$bulkdomains}</textarea><br />

{if $capatacha}
{if $capatacha eq "recaptcha"}
<p>{$LANG.captchaverify}</p>
<div align="center">{$recapatchahtml}</div>
<br />
{else}
<img src="includes/verifyimage.php" align="middle" /> <input type="text" name="code" size="10" maxlength="5" /> &nbsp;&nbsp;&nbsp;
{/if}
{/if}

<input type="submit" value="{$LANG.domainlookupbutton}" class="button"></p>

</div>

</form>

<br />

{if $invalid}
	<p align="center" class="textred" style="font-size:18px;">{$LANG.ordererrordomaininvalid}</p>
{/if}

{if $availabilityresults}

<p><strong>{$LANG.morechoices}</strong></p>
<form method="post" action="{$systemsslurl}cart.php?a=add&domain=transfer">
<table class="clientareatable" cellspacing="1">
<tr class="clientareatableheading"><td width="20"></td><td>{$LANG.domainname}</td><td>{$LANG.domainstatus}</td><td>{$LANG.domainmoreinfo}</td></tr>
{foreach key=num item=result from=$availabilityresults}
<tr class="clientareatableactive"><td>{if $result.status eq "unavailable"}<input type="checkbox" name="domains[]" value="{$result.domain}" /><input type="hidden" name="domainsregperiod[{$result.domain}]" value="{$result.period}" />{else}X{/if}</td><td>{$result.domain}</td><td class="{if $result.status eq "unavailable"}textgreen{else}textred{/if}">{if $result.status eq "unavailable"}{$LANG.domainavailable}{else}{$LANG.domainunavailable}{/if}</td><td>{if $result.status eq "unavailable"}<select name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}{if $regoption.transfer}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.transfer}</option>{/if}{/foreach}</select>{/if}</td></tr>
{/foreach}
</table>
<p align="center"><input type="submit" value="{$LANG.ordernowbutton} >>" /></p>
</form>

{else}

<p align="center"><strong>{$LANG.domainspricing}</strong></p>

<table class="clientareatable" cellspacing="1">
<tr class="clientareatableheading"><td>{$LANG.domaintld}</td><td>{$LANG.domainminyears}</td><td>{$LANG.domainsregister}</td><td>{$LANG.domainstransfer}</td><td>{$LANG.domainsrenew}</td></tr>
{foreach key=num item=tldpricelist from=$tldpricelist}
<tr class="clientareatableactive"><td>{$tldpricelist.tld}</td><td>{$tldpricelist.period}</td><td>{if $tldpricelist.register}{$tldpricelist.register}{else}{$LANG.domainregnotavailable}{/if}</td><td>{if $tldpricelist.transfer}{$tldpricelist.transfer}{else}{$LANG.domainregnotavailable}{/if}</td><td>{if $tldpricelist.renew}{$tldpricelist.renew}{else}{$LANG.domainregnotavailable}{/if}</td></tr>
{/foreach}
</table>

{/if}