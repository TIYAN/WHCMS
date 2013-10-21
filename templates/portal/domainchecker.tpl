{if $bulkdomainsearchenabled}
<p align="center"><strong>{$LANG.domainsimplesearch}</strong> | <a href="domainchecker.php?search=bulkregister">{$LANG.domainbulksearch}</a> | <a href="domainchecker.php?search=bulktransfer">{$LANG.domainbulktransfersearch}</a></p>
{/if}

{if !$loggedin && $currencies}
<form method="post" action="domainchecker.php">
  <p align="right">{$LANG.choosecurrency}:
    <select name="currency" onchange="submit()">
      {foreach from=$currencies item=curr}

      <option value="{$curr.id}"{if $curr.id eq $currency.id} selected{/if}>{$curr.code}</option>
      
{/foreach}
    </select>
    <input type="submit" value="{$LANG.go}" />
  </p>
</form>
{/if}
{if $inccode}
  <div class="errorbox">{$LANG.captchaverifyincorrect}</div>
{/if}
<p>{$LANG.domainintrotext}</p>
<form method="post" action="domainchecker.php">
  <div class="contentbox" align="center"> www.
    <input type="text" name="domain" value="{$domain}" size="40">
    <br />
    <br />
    <table border="0" align="center" cellpadding="10" cellspacing="0">
      <tr> {foreach key=num item=listtld from=$tldslist}
        <td align="left"><input type="checkbox" name="tlds[]" value="{$listtld}"{if in_array($listtld,$tlds)} checked{/if}>
          {$listtld}</td>
        {if $num % 5 == 0}</tr>
      <tr>{/if}{/foreach} </tr>
    </table>
    <br />
    {if $capatacha}
    {if $capatacha eq "recaptcha"}
    <p>{$LANG.captchaverify}</p>
    <div align="center">{$recapatchahtml}</div>
    <br />
    {else}
    <img src="includes/verifyimage.php" align="middle" /> <input type="text" name="code" size="10" maxlength="5" /> &nbsp;&nbsp;&nbsp;
    {/if}
    {/if}
    <input type="submit" id="Submit" value="{$LANG.domainlookupbutton}">
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
<h2>{$LANG.morechoices}</h2>
<form method="post" action="{$systemsslurl}cart.php?a=add&domain=register">
  <table width="100%" border="0" cellpadding="10" cellspacing="0" class="data">
    <tr>
      <th width="20"></td>
      <th>{$LANG.domainname}</th>
      <th>{$LANG.domainstatus}</th>
      <th>{$LANG.domainmoreinfo}</th>
    </tr>
    {foreach key=num item=result from=$availabilityresults}
    <tr>
      <td>{if $result.status eq "available"}
        <input type="checkbox" name="domains[]" value="{$result.domain}" {if $num eq "0" && $available}checked {/if}/>
        <input type="hidden" name="domainsregperiod[{$result.domain}]" value="{$result.period}" />
        {else}X{/if}</td>
      <td>{$result.domain}</td>
      <td class="{if $result.status eq "available"}textgreen{else}textred{/if}">{if $result.status eq "available"}{$LANG.domainavailable}{else}{$LANG.domainunavailable}{/if}</td>
      <td>{if $result.status eq "unavailable"}<a href="http://{$result.domain}" target="_blank">WWW</a> <a href="#" onclick="window.open('whois.php?domain={$result.domain}','whois','width=500,height=400,scrollbars=yes');return false">WHOIS</a>{else}
        <select name="domainsregperiod[{$result.domain}]">
          {foreach key=period item=regoption from=$result.regoptions}
          <option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>
          {/foreach}
        </select>
        {/if}</td>
    </tr>
    {/foreach}
  </table>
  <p align="center">
    <input type="submit" value="{$LANG.ordernowbutton} >>" />
  </p>
</form>
{/if}

{else}
<h2>{$LANG.domainspricing}</h2>
<table width="100%" border="0" cellpadding="10" cellspacing="0" class="data">
  <tr>
    <th>{$LANG.domaintld}</th>
    <th>{$LANG.domainminyears}</th>
    <th>{$LANG.domainsregister}</th>
    <th>{$LANG.domainstransfer}</th>
    <th>{$LANG.domainsrenew}</th>
  </tr>
  {foreach key=num item=tldpricelist from=$tldpricelist}
  <tr>
    <td>{$tldpricelist.tld}</td>
    <td>{$tldpricelist.period}</td>
    <td>{if $tldpricelist.register}{$tldpricelist.register}{else}{$LANG.domainregnotavailable}{/if}</td>
    <td>{if $tldpricelist.transfer}{$tldpricelist.transfer}{else}{$LANG.domainregnotavailable}{/if}</td>
    <td>{if $tldpricelist.renew}{$tldpricelist.renew}{else}{$LANG.domainregnotavailable}{/if}</td>
  </tr>
  {/foreach}
</table>
{/if}<br />