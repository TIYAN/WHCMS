<p>{$LANG.loginintrotext}</p>

<form action="{$systemsslurl}dologin.php" method="post" name="frmlogin">

{if $incorrect}<div class="errorbox">{$LANG.loginincorrect}</div><br />{/if}

<table align="center">
<tr><td align="right">{$LANG.loginemail}:</td><td><input type="text" name="username" size="40" value="{$username}" /></td></tr>
<tr><td align="right">{$LANG.loginpassword}:</td><td><input type="password" name="password" size="25" value="{$password}" /></td></tr>
</table>

<p align="center"><input type="submit" value="{$LANG.loginbutton}" class="button" /><br /><input type="checkbox" name="rememberme"{if $rememberme} checked="checked"{/if} /> {$LANG.loginrememberme}</p>

</form>

<p><strong>{$LANG.loginforgotten}</strong> <a href="pwreset.php">{$LANG.loginforgotteninstructions}</a></p>

<script language="javascript">
document.frmlogin.username.focus();
</script>