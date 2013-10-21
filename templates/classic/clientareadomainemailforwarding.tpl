<p class="heading2">{$LANG.domainemailforwarding}</p>

<p>{$LANG.domainemailforwardingdesc}</p>

<p>{$LANG.domainname}: <strong>{$domain}</strong></p>

{if $error}<div class="errorbox">{$error}</div><br />{/if}

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

<table align="center">
<tr><td align="center"><strong>{$LANG.domainemailforwardingprefix}</strong></td><td></td><td align="center"><strong>{$LANG.domainemailforwardingforwardto}</strong></td></tr>
{foreach key=num item=emailforwarder from=$emailforwarders}
<tr><td><input type="text" name="emailforwarderprefix[{$num}]" value="{$emailforwarder.prefix}" size="15" /></td><td>@{$domain} => </td><td><input type="text" name="emailforwarderforwardto[{$num}]" value="{$emailforwarder.forwardto}" size="35" /></td></tr>
{/foreach}
<tr><td><input type="text" name="emailforwarderprefixnew" size="15" /></td><td>@{$domain} => </td><td><input type="text" name="emailforwarderforwardtonew" size="35" /></td></tr>
</table>
<p align="center"><input type="submit" value="{$LANG.clientareasavechanges}" class="buttongo" /></p>
</form>

{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
<input type="hidden" name="id" value="{$domainid}" />
<p align="center"><input type="submit" value="{$LANG.clientareabacklink}" class="button" /></p>
</form>