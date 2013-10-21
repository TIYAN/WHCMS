<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<script type="text/javascript" src="includes/jscript/statesdropdown.js"></script>

{literal}
<script language="javascript">
function removeItem(type,num) {
	var response = confirm("{/literal}{$LANG.cartremoveitemconfirm}{literal}");
	if (response) {
		window.location = 'cart.php?a=remove&r='+type+'&i='+num;
	}
}
function emptyCart(type,num) {
	var response = confirm("{/literal}{$LANG.cartemptyconfirm}{literal}");
	if (response) {
		window.location = 'cart.php?a=empty';
	}
}
function showloginform() {
    if (jQuery("#custtype").val()=="new") {
        jQuery("#custtype").val("existing");
        jQuery("#signupfrm").fadeToggle("slow",function(){
            jQuery("#loginfrm").fadeToggle();
        });
    } else {
        jQuery("#custtype").val("new");
        jQuery("#loginfrm").fadeToggle("slow",function(){
            jQuery("#signupfrm").fadeToggle();
        });
    }
}
function domaincontactchange() {
    if (jQuery("#domaincontact").val()=="addingnew") {
        jQuery("#domaincontactfields").slideDown();
    } else {
        jQuery("#domaincontactfields").slideUp();
    }
}
function showCCForm() {
    jQuery("#ccinputform").slideDown();
}
function hideCCForm() {
    jQuery("#ccinputform").slideUp();
}
function useExistingCC() {
    jQuery(".newccinfo").hide();
}
function enterNewCC() {
    jQuery(".newccinfo").show();
}
</script>
{/literal}

<div id="order-web20cart">

<h1>{if $checkout}{$LANG.ordercheckout}{else}{$LANG.ordersummary}{/if}</h1>

{if $errormessage}
<div class="errorbox">
    <p>{$LANG.clientareaerrors}</p>
    <ul>{$errormessage}</ul>
</div>
<br />
{elseif $promotioncode && $rawdiscount eq "0.00"}
<div class="errorbox">{$LANG.promoappliedbutnodiscount}</div>
<br />
{/if}

{if $bundlewarnings}
<div class="errorbox" style="display:block;">
<strong>{$LANG.bundlereqsnotmet}</strong><br />
{foreach from=$bundlewarnings item=warning}
{$warning}<br />
{/foreach}
</div>
{/if}

{if !$loggedin && $currencies}
<form method="post" action="cart.php?a=view">
<div class="textright">{$LANG.choosecurrency}: <select name="currency" onchange="submit()">{foreach from=$currencies item=curr}
<option value="{$curr.id}"{if $curr.id eq $currency.id} selected{/if}>{$curr.code}</option>
{/foreach}</select> <input type="submit" value="{$LANG.go}" /></div>
</form>
{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?a=view">

<table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" class="data">
  <tr>
    <th width="55%">{$LANG.orderdesc}</th>
    <th width="45%">{$LANG.orderprice}</th>
  </tr>
  {foreach key=num item=product from=$products}
  <tr>
    <td><strong><em>{$product.productinfo.groupname}</em> - {$product.productinfo.name}</strong>{if $product.domain} ({$product.domain}){/if}<br />
      {if $product.configoptions}
      {foreach key=confnum item=configoption from=$product.configoptions}&nbsp;&raquo; {$configoption.name}: {if $configoption.type eq 1 || $configoption.type eq 2}{$configoption.option}{elseif $configoption.type eq 3}{if $configoption.qty}{$LANG.yes}{else}{$LANG.no}{/if}{elseif $configoption.type eq 4}{$configoption.qty} x {$configoption.option}{/if}<br />
      {/foreach}
      {/if}
      {if $product.allowqty}
{$LANG.cartqtyenterquantity} <input type="text" name="qty[{$num}]" size="3" value="{$product.qty}" /> <input type="submit" value="{$LANG.cartqtyupdate}" />
{/if}
    </td>
    <td class="textcenter"><strong>{$product.pricingtext}{if $product.proratadate}<br />
      ({$LANG.orderprorata} {$product.proratadate}){/if}</strong></td>
  </tr>
  {foreach key=addonnum item=addon from=$product.addons}
  <tr>
    <td><strong>{$LANG.orderaddon}</strong> - {$addon.name}</td>
    <td class="textcenter"><strong>{$addon.pricingtext}</strong></td>
  </tr>
  {/foreach}
  <tr class="config">
    <td><a href="{$smarty.server.PHP_SELF}?a=confproduct&i={$num}" class="textgreen">[{$LANG.carteditproductconfig}]</a> <a href="#" onclick="removeItem('p','{$num}');return false" class="textred">[{$LANG.cartremove}]</a></td>
    <td>&nbsp;</td>
  </tr>
  {/foreach}
  
  {foreach key=num item=addon from=$addons}
  <tr>
    <td><strong>{$addon.name}</strong><br />
      {$addon.productname}{if $addon.domainname} - {$addon.domainname}{/if} </td>
    <td class="textcenter"><strong>{$addon.pricingtext}</td>
  </tr>
  <tr class="config">
    <td><a href="#" onclick="removeItem('a','{$num}');return false" class="textred">[{$LANG.cartremove}]</a></td>
    <td>&nbsp;</td>
  </tr>
  {/foreach}
  
  {foreach key=num item=domain from=$domains}
  <tr>
    <td><strong>{if $domain.type eq "register"}{$LANG.orderdomainregistration}{else}{$LANG.orderdomaintransfer}{/if}</strong> - {$domain.domain} - {$domain.regperiod} {$LANG.orderyears}<br />
      {if $domain.dnsmanagement}&nbsp;&raquo; {$LANG.domaindnsmanagement}<br />
      {/if}
      {if $domain.emailforwarding}&nbsp;&raquo; {$LANG.domainemailforwarding}<br />
      {/if}
      {if $domain.idprotection}&nbsp;&raquo; {$LANG.domainidprotection}<br />
      {/if} </td>
    <td class="textcenter"><strong>{$domain.price}</strong></td>
  </tr>
  <tr class="config">
    <td><a href="{$smarty.server.PHP_SELF}?a=confdomains" class="textgreen">[{$LANG.cartconfigdomainextras}]</a> <a href="#" onclick="removeItem('d','{$num}');return false" class="textred">[{$LANG.cartremove}]</a></td>
    <td>&nbsp;</td>
  </tr>
  {/foreach}

  {foreach key=num item=domain from=$renewals}
  <tr>
    <td><strong>{$LANG.domainrenewal}</strong> - {$domain.domain} - {$domain.regperiod} {$LANG.orderyears}<br />
      {if $domain.dnsmanagement}&nbsp;&raquo; {$LANG.domaindnsmanagement}<br />
      {/if}
      {if $domain.emailforwarding}&nbsp;&raquo; {$LANG.domainemailforwarding}<br />
      {/if}
      {if $domain.idprotection}&nbsp;&raquo; {$LANG.domainidprotection}<br />
      {/if} </td>
    <td class="textcenter"><strong>{$domain.price}</strong></td>
  </tr>
  <tr class="config">
    <td><a href="#" onclick="removeItem('r','{$num}');return false" class="textred">[{$LANG.cartremove}]</a></td>
    <td>&nbsp;</td>
  </tr>
  {/foreach}
  
  {if $cartitems==0}
  <tr>
    <td colspan="2" class="textcenter"><br />
      {$LANG.cartempty} <br />
    <br /></td>
  </tr>
  {/if}
  <tr class="summary">
    <td align="right">{$LANG.ordersubtotal}: &nbsp;</td>
    <td class="textcenter">{$subtotal}</td>
  </tr>
  {if $promotioncode}
  <tr class="promo">
    <td align="right">{$promotiondescription}: &nbsp;</td>
    <td class="textcenter">{$discount}</td>
  </tr>
  {/if}
  {if $taxrate}
  <tr class="summary">
    <td align="right">{$taxname} @ {$taxrate}%: &nbsp;</td>
    <td class="textcenter">{$taxtotal}</td>
  </tr>
  {/if}
  {if $taxrate2}
  <tr class="summary">
    <td align="right">{$taxname2} @ {$taxrate2}%: &nbsp;</td>
    <td class="textcenter">{$taxtotal2}</td>
  </tr>
  {/if}
  <tr class="due">
    <td align="right">{$LANG.ordertotalduetoday}: &nbsp;</td>
    <td class="textcenter">{$total}</td>
  </tr>
  {if $totalrecurringmonthly || $totalrecurringquarterly || $totalrecurringsemiannually || $totalrecurringannually || $totalrecurringbiennially || $totalrecurringtriennially}
  <tr class="recurring">
    <td align="right">{$LANG.ordertotalrecurring}: &nbsp;</td>
    <td class="textcenter">{if $totalrecurringmonthly}{$totalrecurringmonthly} {$LANG.orderpaymenttermmonthly}<br />
      {/if}
      {if $totalrecurringquarterly}{$totalrecurringquarterly} {$LANG.orderpaymenttermquarterly}<br />
      {/if}
      {if $totalrecurringsemiannually}{$totalrecurringsemiannually} {$LANG.orderpaymenttermsemiannually}<br />
      {/if}
      {if $totalrecurringannually}{$totalrecurringannually} {$LANG.orderpaymenttermannually}<br />
      {/if}
      {if $totalrecurringbiennially}{$totalrecurringbiennially} {$LANG.orderpaymenttermbiennially}<br />
      {/if}
      {if $totalrecurringtriennially}{$totalrecurringtriennially} {$LANG.orderpaymenttermtriennially}<br />
      {/if}</td>
  </tr>
  {/if}
</table>

</form>

<br />

{if !$checkout}

<table class="noborders">
  <tr>
    <td width="55%" class="verttop textcenter"><form method="post" action="{$smarty.server.PHP_SELF}?a=view">
        <input type="hidden" name="validatepromo" value="true" />
        <strong>{$LANG.orderpromotioncode}</strong> {if $promotioncode}{$promotioncode} - {$promotiondescription}<br /><a href="{$smarty.server.PHP_SELF}?a=removepromo">{$LANG.orderdontusepromo}</a>{else}
        <input type="text" name="promocode" size="20" /> 
        <input type="submit" value="{$LANG.orderpromovalidatebutton}" />
        {/if}
      </form></td>
    <td width="45%" class="verttop textcenter"><input type="button" value="{$LANG.emptycart}" onclick="emptyCart();return false" />
      <input type="button" value="{$LANG.continueshopping}" onclick="window.location='cart.php'" />
    <input type="button" value="{$LANG.checkout}" onclick="window.location='cart.php?a=checkout'"{if $cartitems==0} disabled{/if} />

{foreach from=$gatewaysoutput item=gatewayoutput}
<div class="gateway"><strong>- {$LANG.or} -</strong><br /><br />{$gatewayoutput}</div>
{/foreach}
    </td>
  </tr>
</table>

{else}

<div class="center90">

<form method="post" action="{$smarty.server.PHP_SELF}?a=checkout">
<input type="hidden" name="submit" value="true" />

<h2>{$LANG.yourdetails}</h2>

<input type="hidden" name="custtype" id="custtype" value="{$custtype}" />

<div id="loginfrm"{if $custtype eq "existing" && !$loggedin}{else} style="display:none;"{/if}>

<p>{$LANG.newcustomersignup|sprintf2:'<a href="#" onclick="showloginform();return false;">':'</a>'}</a></p>

<div class="signupfieldsextra">

    <div class="clearfix">
	    <label for="loginemail">{$LANG.loginemail}</label>
		<div class="input">
            <input type="text" name="loginemail" id="loginemail" style="width:60%;" value="{$username}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="loginpw">{$LANG.loginpassword}</label>
		<div class="input">
            <input type="password" name="loginpw" id="loginpw" />
		</div>
	</div>

</div>

</div>

<div id="signupfrm"{if $custtype eq "existing" && !$loggedin} class="hidden"{/if}>

{if !$loggedin}<p><strong>{$LANG.alreadyregistered}</strong> <a href="{$smarty.server.PHP_SELF}?a=login" onclick="showloginform();return false;">{$LANG.clickheretologin}</a></p>{/if}

<div class="signupfields">

    <div class="clearfix">
	    <label for="firstname">{$LANG.clientareafirstname}</label>
		<div class="input">
            <input type="text" name="firstname" id="firstname" value="{$clientsdetails.firstname}"{if $loggedin} disabled="" class="disabled"{/if} />
		</div>
	</div>

    <div class="clearfix">
	    <label for="lastname">{$LANG.clientarealastname}</label>
		<div class="input">
		    <input type="text" name="lastname" id="lastname" value="{$clientsdetails.lastname}"{if $loggedin} disabled="" class="disabled"{/if} />
		</div>
	</div>

    <div class="clearfix">
	    <label for="companyname">{$LANG.clientareacompanyname}</label>
		<div class="input">
		    <input type="text" name="companyname" id="companyname" value="{$clientsdetails.companyname}"{if $loggedin} disabled="" class="disabled"{/if} />
		</div>
	</div>

    <div class="clearfix">
	    <label for="email">{$LANG.clientareaemail}</label>
		<div class="input">
		    <input type="text" name="email" id="email" value="{$clientsdetails.email}"{if $loggedin} disabled="" class="disabled"{/if} />
		</div>
	</div>
{if !$loggedin}
    <div class="clearfix">
	    <label for="password">{$LANG.clientareapassword}</label>
		<div class="input">
		    <input type="password" name="password" id="password" value="{$password}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="password2">{$LANG.clientareaconfirmpassword}</label>
		<div class="input">
		    <input type="password" name="password2" id="password2" value="{$password2}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="email"></label>
		<div class="input">
		    {include file="default/pwstrength.tpl"}
		</div>
	</div>
{/if}
</div>
<div class="signupfields">

    <div class="clearfix">
	    <label for="address1">{$LANG.clientareaaddress1}</label>
		<div class="input">
            <input type="text" name="address1" id="address1" value="{$clientsdetails.address1}"{if $loggedin} disabled="" class="disabled"{/if} />
		</div>
	</div>

    <div class="clearfix">
	    <label for="address2">{$LANG.clientareaaddress2}</label>
		<div class="input">
            <input type="text" name="address2" id="address2" value="{$clientsdetails.address2}"{if $loggedin} disabled="" class="disabled"{/if} />
		</div>
	</div>

    <div class="clearfix">
	    <label for="city">{$LANG.clientareacity}</label>
		<div class="input">
            <input type="text" name="city" id="city" value="{$clientsdetails.city}"{if $loggedin} disabled="" class="disabled"{/if} />
		</div>
	</div>

    <div class="clearfix">
	    <label for="state">{$LANG.clientareastate}</label>
		<div class="input">
            {if $loggedin}<input type="text" id="state" value="{$clientsdetails.state}" disabled="" class="disabled" />{else}<input type="text" name="state" id="state" value="{$clientsdetails.state}" />{/if}
		</div>
	</div>

    <div class="clearfix">
	    <label for="postcode">{$LANG.clientareapostcode}</label>
		<div class="input">
            <input type="text" name="postcode" id="postcode" value="{$clientsdetails.postcode}"{if $loggedin} disabled="" class="disabled"{/if} />
		</div>
	</div>

    <div class="clearfix">
	    <label for="country">{$LANG.clientareacountry}</label>
		<div class="input">
            {if $loggedin}<input type="text" id="country" value="{$clientsdetails.country}" disabled="" class="disabled" />{else}{$clientcountrydropdown|replace:'name="country"':'name="country" style="width:200px;"'}{/if}
		</div>
	</div>

    <div class="clearfix">
	    <label for="phonenumber">{$LANG.clientareaphonenumber}</label>
		<div class="input">
            <input type="text" name="phonenumber" id="phonenumber" value="{$clientsdetails.phonenumber}"{if $loggedin} disabled="" class="disabled"{/if} />
		</div>
	</div>

</div>
<div class="clear"></div>

{if $customfields || $securityquestions}
<div class="signupfieldsextra">
{if $securityquestions && !$loggedin}
    <div class="clearfix">
	    <label for="securityqid">{$LANG.clientareasecurityquestion}</label>
		<div class="input">
            <select name="securityqid" id="securityqid">
                {foreach key=num item=question from=$securityquestions}
        	    <option value={$question.id}>{$question.question}</option>
                {/foreach}
            </select>
		</div>
	</div>

    <div class="clearfix">
	    <label for="securityqans">{$LANG.clientareasecurityanswer}</label>
		<div class="input">
            <input type="text" name="securityqans" id="securityqans" />
		</div>
	</div>
{/if}
{foreach key=num item=customfield from=$customfields}
    <div class="clearfix">
	    <label for="customfield{$customfield.id}">{$customfield.name}</label>
		<div class="input">
            {$customfield.input} {$customfield.description}
		</div>
	</div>
{/foreach}
</div>
{/if}

{if $taxenabled && !$loggedin}
<p class="textcenter">{$LANG.carttaxupdateselections} <input type="submit" value="{$LANG.carttaxupdateselectionsupdate}" name="updateonly" /></p>
{/if}

</div>

{if $domainsinorder}
<h2>{$LANG.domainregistrantinfo}</h2>
<div class="cartbox">

{$LANG.domainregistrantchoose}: <select name="contact" id="domaincontact" onchange="domaincontactchange()">
    <option value="">{$LANG.usedefaultcontact}</option>
{foreach from=$domaincontacts item=domcontact}
    <option value="{$domcontact.id}"{if $contact==$domcontact.id} selected{/if}>{$domcontact.name}</option>
{/foreach}
    <option value="addingnew"{if $contact eq "addingnew"} selected{/if}>{$LANG.clientareanavaddcontact}...</option>
</select>

<div id="domaincontactfields"{if $contact neq "addingnew"} class="hidden"{/if}>
<br />
<div class="signupfields">

    <div class="clearfix">
	    <label for="firstname">{$LANG.clientareafirstname}</label>
		<div class="input">
            <input type="text" name="domaincontactfirstname" id="domaincontactfirstname" value="{$domaincontact.firstname}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="lastname">{$LANG.clientarealastname}</label>
		<div class="input">
		    <input type="text" name="domaincontactlastname" id="domaincontactlastname" value="{$domaincontact.lastname}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="companyname">{$LANG.clientareacompanyname}</label>
		<div class="input">
		    <input type="text" name="domaincontactcompanyname" id="domaincontactcompanyname" value="{$domaincontact.companyname}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="email">{$LANG.clientareaemail}</label>
		<div class="input">
		    <input type="text" name="domaincontactemail" id="domaincontactemail" value="{$domaincontact.email}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="phonenumber">{$LANG.clientareaphonenumber}</label>
		<div class="input">
            <input type="text" name="domaincontactphonenumber" id="domaincontactphonenumber" value="{$domaincontact.phonenumber}" />
		</div>
	</div>

</div>
<div class="signupfields">

    <div class="clearfix">
	    <label for="address1">{$LANG.clientareaaddress1}</label>
		<div class="input">
            <input type="text" name="domaincontactaddress1" id="domaincontactaddress1" value="{$domaincontact.address1}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="address2">{$LANG.clientareaaddress2}</label>
		<div class="input">
            <input type="text" name="domaincontactaddress2" id="domaincontactaddress2" value="{$domaincontact.address2}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="city">{$LANG.clientareacity}</label>
		<div class="input">
            <input type="text" name="domaincontactcity" id="domaincontactcity" value="{$domaincontact.city}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="state">{$LANG.clientareastate}</label>
		<div class="input">
            <input type="text" name="domaincontactstate" id="domaincontactstate" value="{$domaincontact.state}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="postcode">{$LANG.clientareapostcode}</label>
		<div class="input">
            <input type="text" name="domaincontactpostcode" id="domaincontactpostcode" value="{$domaincontact.postcode}" />
		</div>
	</div>

    <div class="clearfix">
	    <label for="country">{$LANG.clientareacountry}</label>
		<div class="input">
            {$domaincontactcountrydropdown|replace:'name="domaincontactcountry"':'name="domaincontactcountry" style="width:150px;"'|replace:'United States Outlying Islands':'US Outlying Islands'}
		</div>
	</div>

</div>
<div class="clear"></div>
</div>

  </div>
  {/if}

  <h2>{$LANG.orderpaymentmethod}</h2>
  <div class="cartbox">{foreach key=num item=gateway from=$gateways}
    <label><input type="radio" name="paymentmethod" value="{$gateway.sysname}" onclick="{if $gateway.type eq "CC"}showCCForm(){else}hideCCForm(){/if}"{if $selectedgateway eq $gateway.sysname} checked{/if} /> {$gateway.name}</label>
    {/foreach}</div>

<div id="ccinputform"{if $selectedgatewaytype neq "CC"} style="display:none;"{/if}>
<table>
        {if $clientsdetails.cclastfour}
        <tr>
          <td width="200" class="fieldarea"></td>
          <td><label><input type="radio" name="ccinfo" value="useexisting" id="useexisting" onclick="useExistingCC()"{if $clientsdetails.cclastfour} checked{else} disabled{/if} /> {$LANG.creditcarduseexisting}{if $clientsdetails.cclastfour} ({$clientsdetails.cclastfour}){/if}</label><br />
              <label><input type="radio" name="ccinfo" value="new" id="new" onclick="enterNewCC()"{if !$clientsdetails.cclastfour || $ccinfo eq "new"} checked{/if} /> {$LANG.creditcardenternewcard}</label>
          </td>
        </tr>
        {else}
        <input type="hidden" name="ccinfo" value="new" />
        {/if}
        <tr class="newccinfo"{if $clientsdetails.cclastfour && $ccinfo neq "new"} style="display:none;"{/if}>
          <td class="fieldarea">{$LANG.creditcardcardtype}</td>
          <td><select name="cctype">
            {foreach key=num item=cardtype from=$acceptedcctypes}
            <option{if $cctype eq $cardtype} selected{/if}>{$cardtype}</option>
            {/foreach}
          </select></td>
        </tr>
        <tr class="newccinfo"{if $clientsdetails.cclastfour && $ccinfo neq "new"} style="display:none;"{/if}>
          <td class="fieldarea">{$LANG.creditcardcardnumber}</td>
          <td><input type="text" name="ccnumber" size="30" value="{$ccnumber}" autocomplete="off" /></td>
        </tr>
        <tr class="newccinfo"{if $clientsdetails.cclastfour && $ccinfo neq "new"} style="display:none;"{/if}>
          <td class="fieldarea">{$LANG.creditcardcardexpires}</td>
          <td><select name="ccexpirymonth" id="ccexpirymonth" class="newccinfo">{foreach from=$months item=month}
<option{if $ccexpirymonth eq $month} selected{/if}>{$month}</option>
{/foreach}</select> / <select name="ccexpiryyear" class="newccinfo">
{foreach from=$expiryyears item=year}
<option{if $ccexpiryyear eq $year} selected{/if}>{$year}</option>
{/foreach}</select></td>
        </tr>
        {if $showccissuestart}
        <tr class="newccinfo"{if $clientsdetails.cclastfour && $ccinfo neq "new"} style="display:none;"{/if}>
          <td class="fieldarea">{$LANG.creditcardcardstart}</td>
          <td><select name="ccstartmonth" id="ccstartmonth" class="newccinfo">{foreach from=$months item=month}
<option{if $ccstartmonth eq $month} selected{/if}>{$month}</option>
{/foreach}</select> / <select name="ccstartyear" class="newccinfo">
{foreach from=$startyears item=year}
<option{if $ccstartyear eq $year} selected{/if}>{$year}</option>
{/foreach}</select></td>
        </tr>
        <tr class="newccinfo"{if $clientsdetails.cclastfour && $ccinfo neq "new"} style="display:none;"{/if}>
          <td class="fieldarea">{$LANG.creditcardcardissuenum}</td>
          <td><input type="text" name="ccissuenum" value="{$ccissuenum}" size="5" maxlength="3" /></td>
        </tr>
        {/if}
        <tr>
          <td width="200" class="fieldarea">{$LANG.creditcardcvvnumber}</td>
          <td><input type="text" name="cccvv" value="{$cccvv}" size="5" autocomplete="off" /> <a href="#" onclick="window.open('images/ccv.gif','','width=280,height=200,scrollbars=no,top=100,left=100');return false">{$LANG.creditcardcvvwhere}</a></td>
        </tr>
        {if $shownostore}
        <tr>
          <td class="fieldarea"></td>
          <td><label><input type="checkbox" name="nostore" /> {$LANG.creditcardnostore}</label></td>
        </tr>
        {/if}
      </table>
      <br />
</div>

  {if $shownotesfield}
  <h2>{$LANG.ordernotes}</h2>
  <p align="center">
    <textarea name="notes" rows="4" style="width:75%" onFocus="if(this.value=='{$LANG.ordernotesdescription}'){ldelim}this.value='';{rdelim}" onBlur="if (this.value==''){ldelim}this.value='{$LANG.ordernotesdescription}';{rdelim}">{$notes}</textarea>
  </p>
  {/if}
  
  {if $accepttos}
  <p align="center">
    <label><input type="checkbox" name="accepttos" id="accepttos" />
    {$LANG.ordertosagreement} <a href="{$tosurl}" target="_blank">{$LANG.ordertos}</a></label>
  <p> {/if}

<p align="center"><input type="submit" value="{$LANG.completeorder}"{if $cartitems==0} disabled{/if} onclick="this.value='{$LANG.pleasewait}'" /></p>

<br />

<p><img src="images/padlock.gif" border="0" class="imgfloat" alt="Secure Transaction" /> {$LANG.ordersecure} (<strong>{$ipaddress}</strong>) {$LANG.ordersecure2}</p>

</form>

</div>

{/if}

</div>