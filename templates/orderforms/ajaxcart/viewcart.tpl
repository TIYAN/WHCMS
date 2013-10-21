<script type="text/javascript" src="includes/jscript/statesdropdown.js"></script>
<script type="text/javascript" src="includes/jscript/pwstrength.js"></script>

<div id="checkouterror" class="errorbox hidden"></div>

<form id="checkoutfrm" onsubmit="completeorder();return false">
<input type="hidden" name="submit" value="true" />

<h2>{$LANG.yourdetails}</h2>

<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td colspan="2"><label><input type="radio" name="custtype" value="new" id="custtypenew" onclick="showsignupfields()" {if $loggedin}disabled{else}checked{/if} /> {$LANG.cartnewcustomer}</label> <label><input type="radio" name="custtype" value="existing" id="custtypeexisting" {if $loggedin}checked{else}onclick="showloginfields()"{/if} /> {$LANG.cartexistingcustomer}</label></td></tr>
<tr class="rowcolor2 signupfields"><td class="fieldlabel">{$LANG.clientareafirstname}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.firstname}{else}<input type="text" name="firstname" style="width:40%;" value="{$clientsdetails.firstname}" />{/if}</td></tr>
<tr class="rowcolor1 signupfields"><td class="fieldlabel">{$LANG.clientarealastname}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.lastname}{else}<input type="text" name="lastname" style="width:40%;" value="{$clientsdetails.lastname}" />{/if}</td></tr>
<tr class="rowcolor2 signupfields"><td class="fieldlabel">{$LANG.clientareacompanyname}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.companyname}{else}<input type="text" name="companyname" style="width:40%;" value="{$clientsdetails.companyname}" />{/if}</td></tr>
<tr class="rowcolor1 signupfields"><td class="fieldlabel">{$LANG.clientareaemail}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.email}{else}<input type="text" name="email" style="width:60%;" value="{$clientsdetails.email}" />{/if}</td></tr>
<tr class="rowcolor2 signupfields"><td width="100" class="fieldarea">{$LANG.clientareaaddress1}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.address1}{else}<input type="text" name="address1" style="width:40%;" value="{$clientsdetails.address1}" />{/if}</td></tr>
<tr class="rowcolor1 signupfields"><td class="fieldlabel">{$LANG.clientareaaddress2}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.address2}{else}<input type="text" name="address2" style="width:40%;" value="{$clientsdetails.address2}" />{/if}</td></tr>
<tr class="rowcolor2 signupfields"><td class="fieldlabel">{$LANG.clientareacity}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.city}{else}<input type="text" name="city" style="width:40%;" value="{$clientsdetails.city}" />{/if}</td></tr>
<tr class="rowcolor1 signupfields"><td class="fieldlabel">{$LANG.clientareastate}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.state}{else}<input type="text" name="state" style="width:40%;" value="{$clientsdetails.state}" />{/if}</td></tr>
<tr class="rowcolor2 signupfields"><td class="fieldlabel">{$LANG.clientareapostcode}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.postcode}{else}<input type="text" name="postcode" style="width:40%;" value="{$clientsdetails.postcode}" />{/if}</td></tr>
<tr class="rowcolor1 signupfields"><td class="fieldlabel">{$LANG.clientareacountry}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.country}{else}{$clientcountrydropdown}{/if}</td></tr>
<tr class="rowcolor2 signupfields"><td class="fieldlabel">{$LANG.clientareaphonenumber}</td><td class="fieldarea">{if $loggedin}{$clientsdetails.phonenumber}{else}<input type="text" name="phonenumber" style="width:40%;" value="{$clientsdetails.phonenumber}" />{/if}</td></tr>
{if !$loggedin}
<tr class="rowcolor1 signupfields"><td class="fieldlabel">{$LANG.clientareapassword}</td><td class="fieldarea"><input type="password" name="password" id="newpw" size="20" value="{$password}" /></td></tr>
<tr class="rowcolor2 signupfields"><td class="fieldlabel">{$LANG.clientareaconfirmpassword}</td><td class="fieldarea"><input type="password" name="password2" size="20" value="{$password2}" /></td></tr>
{/if}
{if $customfields || $securityquestions}
{if $securityquestions && !$loggedin}
<tr class="rowcolor1 signupfields"><td class="fieldlabel">{$LANG.clientareasecurityquestion}</td><td class="fieldarea"><select name="securityqid">
{foreach key=num item=question from=$securityquestions}
	<option value={$question.id}>{$question.question}</option>
{/foreach}
</select></td></tr>
<tr class="rowcolor2 signupfields"><td class="fieldlabel">{$LANG.clientareasecurityanswer}</td><td class="fieldarea"><input type="password" name="securityqans" size="30"></td></tr>
{/if}
{foreach key=num item=customfield from=$customfields}
<tr class="{cycle values="rowcolor1,rowcolor2"} signupfields"><td class="fieldlabel">{$customfield.name}</td><td class="fieldarea">{$customfield.input} {$customfield.description}</td></tr>
{/foreach}
{/if}
<tr class="rowcolor2 loginfields"><td class="fieldlabel">{$LANG.clientareaemail}</td><td class="fieldarea"><input type="text" name="loginemail" style="width:60%;" /></td></tr>
<tr class="rowcolor1 loginfields"><td class="fieldlabel">{$LANG.clientareapassword}</td><td class="fieldarea"><input type="password" name="loginpw" size="20" /></td></tr>
</table>

{if $domainsinorder}
<h2>{$LANG.domainregistrantinfo}</h2>
<p>{$LANG.domainregistrantchoose}: <select name="contact" id="domaincontact" onchange="domaincontactchange()">
<option value="">{$LANG.usedefaultcontact}</option>
{foreach key=num item=domaincontact from=$domaincontacts}
<option value="{$domaincontact.id}">{$domaincontact.name}</option>
{/foreach}
<option value="addingnew">{$LANG.clientareanavaddcontact}...</option>
</select><br /></p>
<div id="domaincontactfields"{if $contact neq "addingnew"} class="hidden"{/if}>
<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td class="fieldlabel">{$LANG.clientareafirstname}</td><td class="fieldarea"><input type="text" name="domaincontactfirstname" style="width:40%;" value="{$domaincontact.firstname}" /></td></tr>
<tr class="rowcolor2"><td class="fieldlabel">{$LANG.clientarealastname}</td><td class="fieldarea"><input type="text" name="domaincontactlastname" style="width:40%;" value="{$domaincontact.lastname}" /></td></tr>
<tr class="rowcolor1"><td class="fieldlabel">{$LANG.clientareacompanyname}</td><td class="fieldarea"><input type="text" name="domaincontactcompanyname" style="width:40%;" value="{$domaincontact.companyname}" /></td></tr>
<tr class="rowcolor2"><td class="fieldlabel">{$LANG.clientareaemail}</td><td class="fieldarea"><input type="text" name="domaincontactemail" style="width:90%;" value="{$domaincontact.email}" /></td></tr>
<tr class="rowcolor1"><td class="fieldlabel">{$LANG.clientareaaddress1}</td><td class="fieldarea"><input type="text" name="domaincontactaddress1" style="width:40%;" value="{$domaincontact.address1}" /></td></tr>
<tr class="rowcolor2"><td class="fieldlabel">{$LANG.clientareaaddress2}</td><td class="fieldarea"><input type="text" name="domaincontactaddress2" style="width:40%;" value="{$domaincontact.address2}" /></td></tr>
<tr class="rowcolor1"><td class="fieldlabel">{$LANG.clientareacity}</td><td class="fieldarea"><input type="text" name="domaincontactcity" style="width:40%;" value="{$domaincontact.city}" /></td></tr>
<tr class="rowcolor2"><td class="fieldlabel">{$LANG.clientareastate}</td><td class="fieldarea"><input type="text" name="domaincontactstate" style="width:40%;" value="{$domaincontact.state}" /></td></tr>
<tr class="rowcolor1"><td class="fieldlabel">{$LANG.clientareapostcode}</td><td class="fieldarea"><input type="text" name="domaincontactpostcode" size="15" value="{$domaincontact.postcode}" /></td></tr>
<tr class="rowcolor2"><td class="fieldlabel">{$LANG.clientareacountry}</td><td class="fieldarea">{$domaincontactcountrydropdown}</td></tr>
<tr class="rowcolor1"><td class="fieldlabel">{$LANG.clientareaphonenumber}</td><td class="fieldarea"><input type="text" name="domaincontactphonenumber" size="20" value="{$domaincontact.phonenumber}" /></td></tr>
</table>
</div>
{/if}

<h2>{$LANG.orderpaymentmethod}</h2>
<table width="100%" cellspacing="0" cellpadding="0">
<tr class="rowcolor1"><td>{foreach key=num item=gateway from=$gateways}<label><input type="radio" name="paymentmethod" value="{$gateway.sysname}" id="pgbtn{$num}" onclick="{if $gateway.type eq "CC"}showCCForm(){else}hideCCForm(){/if}"{if $selectedgateway eq $gateway.sysname} checked{/if} />{$gateway.name}</label> {/foreach}</td></tr>
</table>
<div id="ccinputform"{if $selectedgatewaytype neq "CC"} style="display:none;"{/if}>
<br />
<table width="100%" cellspacing="0" cellpadding="0">
{if $clientsdetails.cclastfour}<tr class="rowcolor2"><td colspan="2"><label><input type="radio" name="ccinfo" value="useexisting" id="useexisting" onclick="useExistingCC()"{if $clientsdetails.cclastfour} checked{else} disabled{/if} /> {$LANG.creditcarduseexisting}{if $clientsdetails.cclastfour} ({$clientsdetails.cclastfour}){/if}</label><br />
<label><input type="radio" name="ccinfo" value="new" id="new" onclick="enterNewCC()"{if !$clientsdetails.cclastfour || $ccinfo eq "new"} checked{/if} /> {$LANG.creditcardenternewcard}</label></td></tr>{else}<input type="hidden" name="ccinfo" value="new" />{/if}
<tr class="rowcolor2 newccinfo"{if $clientsdetails.cclastfour && $ccinfo neq "new"} style="display:none;"{/if}><td width="150" class="fieldarea">{$LANG.creditcardcardtype}</td><td><select name="cctype">
{foreach key=num item=cardtype from=$acceptedcctypes}
<option{if $cctype eq $cardtype} selected{/if}>{$cardtype}</option>
{/foreach}
</select></td></tr>
<tr class="rowcolor1 newccinfo"{if $clientsdetails.cclastfour && $ccinfo neq "new"} style="display:none;"{/if}><td class="fieldarea">{$LANG.creditcardcardnumber}</td><td><input type="text" name="ccnumber" size="30" value="{$ccnumber}" autocomplete="off" /></td></tr>
<tr class="rowcolor2 newccinfo"{if $clientsdetails.cclastfour && $ccinfo neq "new"} style="display:none;"{/if}><td class="fieldarea">{$LANG.creditcardcardexpires}</td><td><select name="ccexpirymonth" id="ccexpirymonth" class="newccinfo">{foreach from=$months item=month}
<option{if $ccexpirymonth eq $month} selected{/if}>{$month}</option>
{/foreach}</select> / <select name="ccexpiryyear" class="newccinfo">
{foreach from=$expiryyears item=year}
<option{if $ccexpiryyear eq $year} selected{/if}>{$year}</option>
{/foreach}</select></td></tr>
{if $showccissuestart}
<tr class="rowcolor1 newccinfo"{if $clientsdetails.cclastfour && $ccinfo neq "new"} style="display:none;"{/if}><td class="fieldarea">{$LANG.creditcardcardstart}</td><td><select name="ccstartmonth" id="ccstartmonth" class="newccinfo">{foreach from=$months item=month}
<option{if $ccstartmonth eq $month} selected{/if}>{$month}</option>
{/foreach}</select> / <select name="ccstartyear" class="newccinfo">
{foreach from=$startyears item=year}
<option{if $ccstartyear eq $year} selected{/if}>{$year}</option>
{/foreach}</select></td></tr>
<tr class="rowcolor2 newccinfo"{if $clientsdetails.cclastfour && $ccinfo neq "new"} style="display:none;"{/if}><td class="fieldarea">{$LANG.creditcardcardissuenum}</td><td><input type="text" name="ccissuenum" value="{$ccissuenum}" size="5" maxlength="3" /></td></tr>
{/if}
<tr class="rowcolor1"><td class="fieldarea">{$LANG.creditcardcvvnumber}</td><td><input type="text" name="cccvv" value="{$cccvv}" size="5" autocomplete="off" /> <a href="#" onclick="window.open('images/ccv.gif','','width=280,height=200,scrollbars=no,top=100,left=100');return false">{$LANG.creditcardcvvwhere}</a></td></tr>
{if $shownostore}<tr><td class="fieldarea">&nbsp;</td><td><label><input type="checkbox" name="nostore" /> {$LANG.creditcardnostore}</label></td></tr>{/if}
</table>
</div>

{if $shownotesfield}
<h2>{$LANG.ordernotes}</h2>
<p align="center"><textarea name="notes" rows="4" style="width:75%" onFocus="if(this.value=='{$LANG.ordernotesdescription}'){ldelim}this.value='';{rdelim}" onBlur="if (this.value==''){ldelim}this.value='{$LANG.ordernotesdescription}';{rdelim}">{$notes}</textarea></p>
{/if}

{if $accepttos}
<p align="center"><label><input type="checkbox" name="accepttos" id="accepttos" /> {$LANG.ordertosagreement} <a href="{$tosurl}" target="_blank">{$LANG.ordertos}</a></label><p>
{/if}

<p align="center"><input type="submit" value="{$LANG.completeorder}"{if $cartitems==0} disabled{/if} onclick="this.value='{$LANG.pleasewait}'" /></p>

</form>