<script type="text/javascript" src="includes/jscript/pwstrength.js"></script>

{include file="$template/clientareadetailslinks.tpl"}

<h2>{$LANG.clientareanavchangepw}</h2>
{if $successful}
<div class="successbox">{$LANG.changessavedsuccessfully}</div>
<br />
{/if}
{if $errormessage}
<div class="errorbox">{$errormessage}</div>
<br />
{/if}
<form method="post" action="{$smarty.server.PHP_SELF}?action=changepw">
  <input type="hidden" name="submit" value="true" />
  <table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td width="150" class="fieldarea">{$LANG.existingpassword}</td>
            <td><input type="password" name="existingpw" size="25" /></td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td width="150" class="fieldarea">{$LANG.newpassword}</td>
            <td><input type="password" name="newpw" id="newpw" size="25" /></td>
            <td><script language="javascript">showStrengthBar();</script></td>
          </tr>
          <tr>
            <td width="150" class="fieldarea">{$LANG.confirmnewpassword}</td>
            <td><input type="password" name="confirmpw" size="25" /></td>
            <td>&nbsp;</td>
          </tr>
        </table></td>
    </tr>
  </table>
  <p align="center">
    <input type="submit" value="{$LANG.clientareasavechanges}" class="button" />
    <input type="reset" value="{$LANG.clientareacancel}" class="button" />
  </p>
</form>