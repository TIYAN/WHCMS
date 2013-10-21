<p class="heading2">{$LANG.clientareacancelrequest}</p>

{if $invalid}

<p>{$LANG.clientareacancelinvalid}</p>

{else}

{if $requested}

<p>{$LANG.clientareacancelconfirmation}</p>

{else}

<p>{$LANG.clientareacancelproduct}: <strong>{$groupname} - {$productname}</strong>{if $domain} ({$domain}){/if}</p>

<br />

<form method="post" action="{$smarty.server.PHP_SELF}?action=cancel&amp;id={$id}">
<input type="hidden" name="sub" value="submit" />

<table width=80% align="center">
<tr><td>{if $error}<font style="color:#cc0000;font-weight:bold;">{/if}{$LANG.clientareacancelreason}:</td></tr>
<tr><td><textarea name="cancellationreason" rows="6" style="width:100%;"></textarea></td></tr>
<tr><td align="center">{$LANG.clientareacancellationtype}: <select name="type"><option value="Immediate">{$LANG.clientareacancellationimmediate}</option><option value="End of Billing Period">{$LANG.clientareacancellationendofbillingperiod}</option></select></td></tr>
</table>

<br />

{if $domainid}
<p align="center"><strong>{$LANG.cancelrequestdomain}</strong></p>
<p align="center">{$LANG.cancelrequestdomaindesc|sprintf2:$domainnextduedate:$domainprice:$domainregperiod}</p>
<p align="center"><input type="checkbox" name="canceldomain" id="canceldomain" /> <label for="canceldomain" class="textred">{$LANG.cancelrequestdomainconfirm}</label></p>
<br />
{/if}

<p align="center"><input type="submit" value="{$LANG.clientareacancelrequestbutton}" class="buttonwarn" /></p>

</form>

{/if}{/if}

<br /><br /><br />