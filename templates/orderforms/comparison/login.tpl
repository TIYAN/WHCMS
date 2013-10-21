<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<script type="text/javascript" src="templates/orderforms/{$carttpl}/js/main.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/uistyle.css" />

<div id="order-comparison">

{include file="orderforms/comparison/comparisonsteps.tpl" step=3}

<div class="cartcontainer">

<h2>{$LANG.cartexistingclientlogin|strtolower}</h2>

<p>{$LANG.cartexistingclientlogindesc}</p>

<form action="dologin.php" method="post">

{if $incorrect}<div class="errorbox">{$LANG.loginincorrect}</div><br />{/if}

<table align="center">
<tr><td align="right">{$LANG.loginemail}:</td><td><input type="text" name="username" size="40" value="{$username}" /></td></tr>
<tr><td align="right">{$LANG.loginpassword}:</td><td><input type="password" name="password" size="25" /></td></tr>
</table>
<p align="center"><input type="submit" value="{$LANG.loginbutton}" class="cartbutton" /></p>

</form>

<p><strong>{$LANG.loginforgotten}</strong> <a href="pwreset.php" target="_blank">{$LANG.loginforgotteninstructions}</a></p>

</div>

</form>

</div>