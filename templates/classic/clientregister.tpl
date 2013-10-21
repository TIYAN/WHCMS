{if $noregistration}
<br />
<div class="errorbox">{$LANG.registerdisablednotice}</div>
<br /><br /><br />
{else}
<script type="text/javascript" src="includes/jscript/statesdropdown.js"></script>
<script type="text/javascript" src="includes/jscript/pwstrength.js"></script>

<p>{$LANG.clientregisterheadertext}</p>

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}">
<input type="hidden" name="register" value="true" />

<table cellspacing="1" cellpadding="0" class="frame"><tr><td>
<table width="100%" cellpadding="2">
<tr><td width="150" class="fieldarea">{$LANG.clientareafirstname}</td><td><input type="text" name="firstname" size=30 value="{$clientfirstname}"></td></tr>
<tr><td class="fieldarea">{$LANG.clientarealastname}</td><td><input type="text" name="lastname" size=30 value="{$clientlastname}"></td></tr>
<tr><td class="fieldarea">{$LANG.clientareacompanyname}</td><td><input type="text" name="companyname" size=30 value="{$clientcompanyname}"></td></tr>
<tr><td class="fieldarea">{$LANG.clientareaemail}</td><td><input type="text" name="email" size=50 value="{$clientemail}"></td></tr>
<tr><td class="fieldarea">{$LANG.clientareaaddress1}</td><td><input type="text" name="address1" size=40 value="{$clientaddress1}"></td></tr>
<tr><td class="fieldarea">{$LANG.clientareaaddress2}</td><td><input type="text" name="address2" size=30 value="{$clientaddress2}"></td></tr>
<tr><td class="fieldarea">{$LANG.clientareacity}</td><td><input type="text" name="city" size=30 value="{$clientcity}"></td></tr>
<tr><td class="fieldarea">{$LANG.clientareastate}</td><td><input type="text" name="state" size=25 value="{$clientstate}"></td></tr>
<tr><td class="fieldarea">{$LANG.clientareapostcode}</td><td><input type="text" name="postcode" size=10 value="{$clientpostcode}"></td></tr>
<tr><td class="fieldarea">{$LANG.clientareacountry}</td><td>{$clientcountriesdropdown}</td></tr>
<tr><td class="fieldarea">{$LANG.clientareaphonenumber}</td><td><input type="text" name="phonenumber" size="20" value="{$clientphonenumber}"></td></tr>
</table>
</td></tr></table>

{if $customfields || $securityquestions || $currencies}
<br />
<table cellspacing="1" cellpadding="0" class="frame"><tr><td>
<table width="100%" cellpadding="2">
{if $securityquestions}
<tr><td class="fieldarea">{$LANG.clientareasecurityquestion}</td><td><select name="securityqid">
{foreach key=num item=question from=$securityquestions}
	<option value={$question.id}>{$question.question}</option>
{/foreach}
</select></td></tr>
<tr><td class="fieldarea">{$LANG.clientareasecurityanswer}</td><td><input type="password" name="securityqans" size="30"></td></tr>
{/if}
{foreach key=num item=customfield from=$customfields}
<tr><td class="fieldarea">{$customfield.name}</td><td>{$customfield.input} {$customfield.description}</td></tr>
{/foreach}
{if $currencies}
<tr><td width="150" class="fieldarea">{$LANG.choosecurrency}</td><td><select name="currency">
{foreach from=$currencies item=curr}
    <option value="{$curr.id}"{if !$smarty.post.currency && $curr.default || $smarty.post.currency eq $curr.id } selected{/if}>{$curr.code}</option>
{/foreach}
</select></td></tr>
{/if}
</table>
</td></tr></table>
{/if}

<p><strong>&nbsp;&raquo;&nbsp;{$LANG.orderlogininfo}</strong></p>
<p>{$LANG.orderlogininfopart1} {$companyname} {$LANG.orderlogininfopart2}</p>

<table cellspacing="1" cellpadding="0" class="frame"><tr><td>
<table width="100%" cellpadding="2">
<tr><td width="150" class="fieldarea">{$LANG.clientareapassword}</td><td width="175"><input type="password" name="password" id="newpw" size="25"></td><td><script language="javascript">showStrengthBar();</script></td></tr>
<tr><td class="fieldarea">{$LANG.clientareaconfirmpassword}</td><td><input type="password" name="password2" size="25"></td></tr>
</table>
</td></tr></table>

{if $capatacha}
<p><strong>&nbsp;&raquo;&nbsp;{$LANG.captchatitle}</strong></p>
<p>{$LANG.captchaverify}</p>
{if $capatacha eq "recaptcha"}
<div align="center">{$recapatchahtml}</div>
{else}
<p align="center"><img src="includes/verifyimage.php" align="middle" /> <input type="text" name="code" size="10" maxlength="5" /></p>
{/if}
{/if}

{if $accepttos}
<p><input type="checkbox" name="accepttos" id="accepttos"> <label for=accepttos>{$LANG.ordertosagreement} <a href="{$tosurl}" target="_blank">{$LANG.ordertos}</a></label>.<p>
{/if}

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" class="buttongo" /></p>

</form>
{/if}