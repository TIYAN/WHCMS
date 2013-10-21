<h2>{$LANG.domaingeteppcode}</h2>
<p>{$LANG.domaingeteppcodeexplanation}</p>
<p>{$LANG.domainname}: <strong>{$domain}</strong></p>
<div class="errorbox">{if $error}
  {$LANG.domaingeteppcodefailure} {$error}
  {else}
  {if $eppcode}
  {$LANG.domaingeteppcodeis} {$eppcode}
  {else}
  {$LANG.domaingeteppcodeemailconfirmation}
  {/if}
  {/if}</div>
<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
  <input type="hidden" name="id" value="{$domainid}" />
  <p align="center">
    <input type="submit" value="{$LANG.clientareabacklink}" />
  </p>
</form><br />