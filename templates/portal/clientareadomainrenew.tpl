<h2>{$LANG.domainrenew}</h2>
<p>{$LANG.domainrenewdesc}</p>
<p align="center"><strong>{$domain}</strong><br />
  {$LANG.domaincurrentrenewaldate}: {$nextduedate}</p>
<form method="post" action="{$smarty.server.PHP_SELF}?action=domainrenew">
  <input type="hidden" name="domainid" value="{$domainid}" />
  <input type="hidden" name="renew" value="true" />
  <p align="center">
    <select name="renewalperiod">
      
{foreach key=num from=$renewaloptions item=renewaloption}

      <option value="{$renewaloption.period}">{$renewaloption.period} {$LANG.orderyears} @ {$currencysymbol}{$renewaloption.price} {$currency}</option>
      
{/foreach}

    </select>
    <input type="submit" value="{$LANG.domainorderrenew}" class="button" />
  </p>
</form>
<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
  <input type="hidden" name="id" value="{$domainid}" />
  <p align="center">
    <input type="submit" value="{$LANG.clientareabacklink}" />
  </p>
</form><br />