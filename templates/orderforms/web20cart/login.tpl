<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />

<div id="order-web20cart">

<h1>{$LANG.cartexistingclientlogin}</h1>

<p>{$LANG.cartexistingclientlogindesc}</p>

{if $incorrect}
<div class="errorbox textcenter">{$LANG.loginincorrect}</div>
{/if}

<br />

<form action="dologin.php" method="post">

<div class="signupfieldsextra">

    <div class="clearfix">
	    <label for="username">{$LANG.loginemail}</label>
		<div class="input">
            <input type="text" name="username" id="username" style="width:40%;" value="{$username}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="password">{$LANG.loginpassword}</label>
		<div class="input">
            <input type="password" name="password" id="password" />
		</div>
	</div>

</div>

<p align="center"><input type="submit" value="{$LANG.loginbutton}" /></p>

</form>

<p><strong>{$LANG.loginforgotten}</strong> <a href="pwreset.php" target="_blank">{$LANG.loginforgotteninstructions}</a></p>

</div>