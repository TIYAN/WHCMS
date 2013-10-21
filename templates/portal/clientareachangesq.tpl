{include file="$template/clientareadetailslinks.tpl"}

<h2>{$LANG.clientareanavsecurityquestions}</h2>

{if $successful}<div class="successbox">{$LANG.changessavedsuccessfully}</div><br />{/if}
{if $errormessage}<div class="errorbox">{$errormessage}</div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action=changesq">
<input type="hidden" name="submit" value="true" />
{if !$nocurrent}
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td width="250" class="fieldarea">{$currentquestion}</td>
            <td><input type="password" name="currentsecurityqans" size="25" /></td>
          </tr>
      </table></td>
    </tr>
  </table>
<br /><br />
{/if}

  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td width="250" class="fieldarea">{$LANG.clientareasecurityquestion}</td><td><select name="securityqid">
{foreach key=num item=question from=$securityquestions}  
	<option value={$question.id}>{$question.question}</option>
{/foreach}
</select></td></tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareasecurityanswer}</td><td><input type="password" name="securityqans" size="25" /></td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareasecurityconfanswer}</td><td><input type="password" name="securityqans2" size="25" /></td>
          </tr>
      </table></td>
    </tr>
  </table>

<p align="center"><input type="submit" value="{$LANG.clientareasavechanges}" class="button" /> <input type="reset" value="{$LANG.clientareacancel}" class="button" /></p>

</form>