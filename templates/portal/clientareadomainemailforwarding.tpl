<h2>{$LANG.domainemailforwarding}</h2>
<p>{$LANG.domainemailforwardingdesc}</p>
<p>{$LANG.domainname}: <strong>{$domain}</strong></p>
{if $error}
<div class="errorbox">{$error}</div>
<br />
{/if}
{if $external}
<br /><br />
<div align="center">
{$code}
</div>
<br /><br /><br /><br />
{else}
<form method="post" action="{$smarty.server.PHP_SELF}?action=domainemailforwarding">
  <input type="hidden" name="sub" value="save" />
  <input type="hidden" name="domainid" value="{$domainid}" />
  <table class="data" width="100%" border="0" align="center" cellpadding="10" cellspacing="0">
    <tr>
      <th align="center"><strong>{$LANG.domainemailforwardingprefix}</strong></th>
      <th></th>
      <th align="center"><strong>{$LANG.domainemailforwardingforwardto}</strong></th>
    </tr>
    {foreach key=num item=emailforwarder from=$emailforwarders}
    <tr>
      <td><input type="text" name="emailforwarderprefix[{$num}]" value="{$emailforwarder.prefix}" size="15" /></td>
      <td>@{$domain} => </td>
      <td><input type="text" name="emailforwarderforwardto[{$num}]" value="{$emailforwarder.forwardto}" size="35" /></td>
    </tr>
    {/foreach}
    <tr>
      <td><input type="text" name="emailforwarderprefixnew" size="15" /></td>
      <td>@{$domain} => </td>
      <td><input type="text" name="emailforwarderforwardtonew" size="35" /></td>
    </tr>
  </table>
  <p align="center">
    <input type="submit" value="{$LANG.clientareasavechanges}" class="button" />
  </p>
</form>
{/if}
<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
  <input type="hidden" name="id" value="{$domainid}" />
  <p align="center">
    <input type="submit" value="{$LANG.clientareabacklink}" />
  </p>
</form><br />