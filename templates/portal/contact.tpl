<p>{$LANG.contactheader}</p>
{if $sent}
<p><strong>{$LANG.contactsent}</strong></p>
{else}
<form method="post" action="contact.php?action=send">
  {if $errormessage}
  <div class="errorbox">{$errormessage}</div>
  <br />
  {/if}
  <table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td width="100" class="fieldarea">{$LANG.contactname}</td>
            <td><input type="text" name="name" size="30" value="{$name}" /></td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.contactemail}</td>
            <td><input type="text" name="email" size="50" value="{$email}" /></td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.contactsubject}</td>
            <td><input type="text" name="subject" size="60" value="{$subject}" /></td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.contactmessage}</td>
            <td><textarea name="message" rows="8" style="width:95%">{$message}</textarea></td>
          </tr>
      </table></td>
    </tr>
  </table>
  {if $capatacha}
  <h2>{$LANG.captchatitle}</h2>
  <p>{$LANG.captchaverify}</p>
  {if $capatacha eq "recaptcha"}
  <div align="center">{$recapatchahtml}</div>
  {else}
  <p align="center"><img src="includes/verifyimage.php" align="middle" /> <input type="text" name="code" size="10" maxlength="5" /></p>
  {/if}
  {/if}
  <p align="center">
    <input type="submit" value="{$LANG.contactsend}" />
  </p>
</form>
{/if}<br />