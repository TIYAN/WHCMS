<p class="heading2">{$LANG.domainregisterns}</p>

<p>{$LANG.domainregisternsexplanation}</p>

<p>{$LANG.domainname}: <strong>{$domain}</strong></p>

{if $result}<div class="errorbox">{$result}</div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action=domainregisterns">
<input type="hidden" name="sub" value="register">
<input type="hidden" name="domainid" value="{$domainid}">
<div class="contentbox">
<p><strong>{$LANG.domainregisternsreg}</strong><br />
<table>
<tr><td align="right">{$LANG.domainregisternsns}</td><td align="left"><input type="text" name="ns" size="10" /> . {$domain}</td></tr>
<tr><td align="right">{$LANG.domainregisternsip}</td><td align="left"><input type="text" name="ipaddress" size="20" /></td></tr>
</table>
<input type="submit" value="{$LANG.clientareasavechanges}" class="buttongo" /></p>
</div>
</form>

<br />

<form method="post" action="{$smarty.server.PHP_SELF}?action=domainregisterns">
<input type="hidden" name="sub" value="modify">
<input type="hidden" name="domainid" value="{$domainid}">
<div class="contentbox">
<p><strong>{$LANG.domainregisternsmod}</strong><br />
<table>
<tr><td align="right">{$LANG.domainregisternsns}</td><td align="left"><input type="text" name="ns" size="10" /> . {$domain}</td></tr>
<tr><td align="right">{$LANG.domainregisternscurrentip}</td><td align="left"><input type="text" name="currentipaddress" size="20" /></td></tr>
<tr><td align="right">{$LANG.domainregisternsnewip}</td><td align="left"><input type="text" name="newipaddress" size="20" /></td></tr>
</table>
<input type="submit" value="{$LANG.clientareasavechanges}" class="buttongo" /></p>
</div>
</form>

<br />

<form method="post" action="{$smarty.server.PHP_SELF}?action=domainregisterns">
<input type="hidden" name="sub" value="delete">
<input type="hidden" name="domainid" value="{$domainid}">
<div class="contentbox">
<p><strong>{$LANG.domainregisternsdel}</strong><br />
<table>
<tr><td align="right">{$LANG.domainregisternsns}</td><td align="left"><input type="text" name="ns" size="10" /> . {$domain}</td></tr>
</table>
<input type="submit" value="{$LANG.clientareasavechanges}" class="buttongo" /></p>
</div>
</form>

<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
<input type="hidden" name="id" value="{$domainid}" />
<p align="center"><input type="submit" value="{$LANG.clientareabacklink}" class="button" /></p>
</form>