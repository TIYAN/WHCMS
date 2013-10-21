<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<table width="100%"><tr><td width="160" valign="top">

{include file="orderforms/verticalsteps/verticalsteps.tpl" step=5}

</td><td valign="top">

<p><b>{$LANG.cartexistingclientlogin}</b></p>

{if $incorrect}<div class="errorbox">{$LANG.loginincorrect}</div><br />{/if}

<p>{$LANG.cartexistingclientlogindesc}</p>

<form action="dologin.php" method="post">

<table align="center">
<tr><td align="right">{$LANG.loginemail}:</td><td><input type="text" name="username" size="40" value="{$username}" /></td></tr>
<tr><td align="right">{$LANG.loginpassword}:</td><td><input type="password" name="password" size="25" /></td></tr>
</table>
<p align="center"><input type="submit" value="{$LANG.loginbutton}" /></p>

</form>

<p><strong>{$LANG.loginforgotten}</strong> <a href="pwreset.php" target="_blank">{$LANG.loginforgotteninstructions}</a></p>

</td></tr></table>