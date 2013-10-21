<p>{$LANG.loginintrotext}</p>
<form action="{$systemsslurl}dologin.php" method="post" name="frmlogin" id="frmlogin">
  {if $incorrect}
  <div class="errorbox">{$LANG.loginincorrect}</div>
  <br />
  {/if}
  <table style="margin: 0 auto;" cellpadding="0" cellspacing="0" border="0" align="center" class="frame">
    <tr>
      <td><table border="0" align="center" cellpadding="10" cellspacing="0">
          <tr>
            <td width="150" align="right" class="fieldarea">{$LANG.loginemail}:</td>
            <td><input type="text" name="username" size="40" value="{$username}" /></td>
          </tr>
          <tr>
            <td width="150" align="right" class="fieldarea">{$LANG.loginpassword}:</td>
            <td><input type="password" name="password" size="25" value="{$password}" /></td>
          </tr>
          <tr>
            <td width="150" align="right" class="fieldarea"><input type="checkbox" name="rememberme"{if $rememberme} checked="checked"{/if} /></td>
            <td>{$LANG.loginrememberme}</td>
          </tr>
          <tr>
            <td width="150" align="right" class="fieldarea">&nbsp;</td>
            <td><input type="submit" value="{$LANG.loginbutton}" /></td>
          </tr>
        </table></td>
    </tr>
  </table><br />
</form>
<p align="center"><strong>{$LANG.loginforgotten}</strong> <a href="pwreset.php">{$LANG.loginforgotteninstructions}</a></p>
<script type="text/javascript">
document.frmlogin.username.focus();
</script>
<br />