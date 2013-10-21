<script type="text/javascript" src="includes/jscript/statesdropdown.js"></script>
<script type="text/javascript" src="includes/jscript/pwstrength.js"></script>
<script language="JavaScript" type="text/javascript">
{literal}
jQuery(document).ready(function(){
    jQuery("#subaccount").click(function () {
        if (jQuery("#subaccount:checked").val()!=null) {
            jQuery(".subaccountfields").show();
        } else {
            jQuery(".subaccountfields").hide();
        }
    });
});
{/literal}
</script>

{include file="$template/clientareadetailslinks.tpl"}

<p class="heading2">{$LANG.clientareanavaddcontact}</p>

{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action=addcontact">
<input type="hidden" name="submit" value="true" />

<table cellspacing="1" cellpadding="0" class="frame"><tr><td>
<table width="100%" cellpadding="2">
<tr><td width="150" class="fieldarea">{$LANG.clientareafirstname}</td><td><input type="text" name="firstname" value="{$contactfirstname}" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.clientarealastname}</td><td><input type="text" name="lastname" value="{$contactlastname}" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.clientareacompanyname}</td><td><input type="text" name="companyname" value="{$contactcompanyname}" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.clientareaemail}</td><td><input type="text" name="email" value="{$contactemail}" size="50" /></td></tr>
<tr><td class="fieldarea">{$LANG.clientareaaddress1}</td><td><input type="text" name="address1" value="{$contactaddress1}" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.clientareaaddress2}</td><td><input type="text" name="address2" value="{$contactaddress2}" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.clientareacity}</td><td><input type="text" name="city" value="{$contactcity}" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.clientareastate}</td><td><input type="text" name="state" value="{$contactstate}" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.clientareapostcode}</td><td><input type="text" name="postcode" value="{$contactpostcode}" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.clientareacountry}</td><td>{$countriesdropdown}</td></tr>
<tr><td class="fieldarea">{$LANG.clientareaphonenumber}</td><td><input type="text" name="phonenumber" value="{$contactphonenumber}" size="25" /></td></tr>
<tr><td class="fieldarea">{$LANG.subaccountactivate}</td><td><input type="checkbox" name="subaccount" id="subaccount"{if $subaccount} checked{/if} /> <label for="subaccount">{$LANG.subaccountactivatedesc}</label></td></tr>
<tr class="subaccountfields"{if !$subaccount} style="display:none;"{/if}><td class="fieldarea">{$LANG.clientareapassword}</td><td><table width="100%" cellspacing="0" cellpadding="0"><tr><td style="border:0;"><input type="password" name="password" id="newpw" size="25" /></td><td style="border:0;"><script language="JavaScript" type="text/javascript">showStrengthBar();</script></td></tr></table></td></tr>
<tr class="subaccountfields"{if !$subaccount} style="display:none;"{/if}><td class="fieldarea">{$LANG.clientareaconfirmpassword}</td><td><input type="password" name="password2" size="25" /></td></tr>
<tr class="subaccountfields"{if !$subaccount} style="display:none;"{/if}><td class="fieldarea">{$LANG.subaccountpermissions}</td><td>
<input type="checkbox" name="permissions[]" id="permprofile" value="profile"{if in_array('profile',$permissions)} checked{/if} /> <label for="permprofile">{$LANG.subaccountpermsprofile}</label><br />
<input type="checkbox" name="permissions[]" id="permcontacts" value="contacts"{if in_array('contacts',$permissions)} checked{/if} /> <label for="permcontacts">{$LANG.subaccountpermscontacts}</label><br />
<input type="checkbox" name="permissions[]" id="permproducts" value="products"{if in_array('products',$permissions)} checked{/if} /> <label for="permproducts">{$LANG.subaccountpermsproducts}</label><br />
<input type="checkbox" name="permissions[]" id="permmanageproducts" value="manageproducts"{if in_array('manageproducts',$permissions)} checked{/if} /> <label for="permmanageproducts">{$LANG.subaccountpermsmanageproducts}</label><br />
<input type="checkbox" name="permissions[]" id="permdomains" value="domains"{if in_array('domains',$permissions)} checked{/if} /> <label for="permdomains">{$LANG.subaccountpermsdomains}</label><br />
<input type="checkbox" name="permissions[]" id="permmanagedomains" value="managedomains"{if in_array('managedomains',$permissions)} checked{/if} /> <label for="permmanagedomains">{$LANG.subaccountpermsmanagedomains}</label><br />
<input type="checkbox" name="permissions[]" id="perminvoices" value="invoices"{if in_array('invoices',$permissions)} checked{/if} /> <label for="perminvoices">{$LANG.subaccountpermsinvoices}</label><br />
<input type="checkbox" name="permissions[]" id="permtickets" value="tickets"{if in_array('tickets',$permissions)} checked{/if} /> <label for="permtickets">{$LANG.subaccountpermstickets}</label><br />
<input type="checkbox" name="permissions[]" id="permaffiliates" value="affiliates"{if in_array('affiliates',$permissions)} checked{/if} /> <label for="permaffiliates">{$LANG.subaccountpermsaffiliates}</label><br />
<input type="checkbox" name="permissions[]" id="permemails" value="emails"{if in_array('emails',$permissions)} checked{/if} /> <label for="permemails">{$LANG.subaccountpermsemails}</label><br />
<input type="checkbox" name="permissions[]" id="permorders" value="orders"{if in_array('orders',$permissions)} checked{/if} /> <label for="permorders">{$LANG.subaccountpermsorders}</label>
</td></tr>
<tr><td class="fieldarea">{$LANG.clientareacontactsemails}</td><td>
<input type="checkbox" name="generalemails" id="generalemails" value="1"{if $generalemails} checked{/if} /> <label for="generalemails">{$LANG.clientareacontactsemailsgeneral}</label><br />
<input type="checkbox" name="productemails" id="productemails" value="1"{if $productemails} checked{/if} /> <label for="productemails">{$LANG.clientareacontactsemailsproduct}</label><br />
<input type="checkbox" name="domainemails" id="domainemails" value="1"{if $domainemails} checked{/if} /> <label for="domainemails">{$LANG.clientareacontactsemailsdomain}</label><br />
<input type="checkbox" name="invoiceemails" id="invoiceemails" value="1"{if $invoiceemails} checked{/if} /> <label for="invoiceemails">{$LANG.clientareacontactsemailsinvoice}</label><br />
<input type="checkbox" name="supportemails" id="supportemails" value="1"{if $supportemails} checked{/if} /> <label for="supportemails">{$LANG.clientareacontactsemailssupport}</label><br />
</td></tr>
</table>
</td></tr></table>

<p align="center"><input type="submit" value="{$LANG.clientareasavechanges}" class="buttongo" /> <input type="reset" value="{$LANG.clientareacancel}" class="button" /></p>

</form>