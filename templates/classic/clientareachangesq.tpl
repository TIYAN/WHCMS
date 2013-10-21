{include file="$template/clientareadetailslinks.tpl"}

<p class="heading2">{$LANG.clientareanavsecurityquestions}</p>

{if $successful}<div class="successbox">{$LANG.changessavedsuccessfully}</div><br />{/if}
{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action=changesq">
<input type="hidden" name="submit" value="true" />
{if !$nocurrent}
<table cellspacing="1" cellpadding="0" class="frame"><tr><td>
<table width="100%" cellpadding="2">
<tr><td width="250" class="fieldarea">{$currentquestion}</td><td><input type="password" name="currentsecurityqans" size="25" /></td></tr>
</table>
</td></tr></table>
<br /><br />
{/if}

<table cellspacing="1" cellpadding="0" class="frame"><tr><td>
<table width="100%" cellpadding="2">
<tr><td width="250" class="fieldarea">{$LANG.clientareasecurityquestion}</td><td><select name="securityqid">
{foreach key=num item=question from=$securityquestions}  
	<option value={$question.id}>{$question.question}</option>
{/foreach}
</select></td></tr>
<tr><td class="fieldarea">{$LANG.clientareasecurityanswer}</td><td><input type="password" name="securityqans" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.clientareasecurityconfanswer}</td><td><input type="password" name="securityqans2" size="25" /></td></tr>
</table>
</td></tr></table>

<p align="center"><input type="submit" value="{$LANG.clientareasavechanges}" class="buttongo" /> <input type="reset" value="{$LANG.clientareacancel}" class="button" /></p>

</form>