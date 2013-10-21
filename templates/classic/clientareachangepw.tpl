<script type="text/javascript" src="includes/jscript/pwstrength.js"></script>

{include file="$template/clientareadetailslinks.tpl"}

<p class="heading2">{$LANG.clientareanavchangepw}</p>

{if $successful}<div class="successbox">{$LANG.changessavedsuccessfully}</div><br />{/if}
{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action=changepw">
<input type="hidden" name="submit" value="true" />

<table cellspacing="1" cellpadding="0" class="frame"><tr><td>
<table width="100%" cellpadding="2">
<tr><td width="150" class="fieldarea">{$LANG.existingpassword}</td><td width="175"><input type="password" name="existingpw" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.newpassword}</td><td><input type="password" name="newpw" id="newpw" size="25" /></td><td><script language="javascript">showStrengthBar();</script></td></tr>
<tr><td class="fieldarea">{$LANG.confirmnewpassword}</td><td><input type="password" name="confirmpw" size="25" /></td></tr>
</table>
</td></tr></table>

<p align="center"><input type="submit" value="{$LANG.clientareasavechanges}" class="buttongo" /> <input type="reset" value="{$LANG.clientareacancel}" class="button" /></p>

</form>