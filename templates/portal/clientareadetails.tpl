<script type="text/javascript" src="includes/jscript/statesdropdown.js"></script>

{include file="$template/clientareadetailslinks.tpl"}

<h2 class="heading2">{$LANG.clientareanavdetails}</h2>
{if $successful}
<div class="successbox">{$LANG.changessavedsuccessfully}</div>
<br />
{/if}
{if $errormessage}
<div class="errorbox">{$errormessage}</div>
<br />
{/if}
<form method="post" action="{$smarty.server.PHP_SELF}?action=details">
  <input type="hidden" name="save" value="true" />
  <table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td width="150" class="fieldarea">{$LANG.clientareafirstname}</td>
            <td>{if in_array('firstname',$uneditablefields)}{$clientfirstname}{else}<input type="text" name="firstname" value="{$clientfirstname}" size="25" />{/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientarealastname}</td>
            <td>{if in_array('lastname',$uneditablefields)}{$clientlastname}{else}<input type="text" name="lastname" value="{$clientlastname}" size="25" />{/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareacompanyname}</td>
            <td>{if in_array('companyname',$uneditablefields)}{$clientcompanyname}{else}<input type="text" name="companyname" value="{$clientcompanyname}" size="25" />{/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareaemail}</td>
            <td>{if in_array('email',$uneditablefields)}{$clientemail}{else}<input type="text" name="email" value="{$clientemail}" size="50" />{/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareaaddress1}</td>
            <td>{if in_array('address1',$uneditablefields)}{$clientaddress1}{else}<input type="text" name="address1" value="{$clientaddress1}" size="25" />{/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareaaddress2}</td>
            <td>{if in_array('address2',$uneditablefields)}{$clientaddress2}{else}<input type="text" name="address2" value="{$clientaddress2}" size="25" />{/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareacity}</td>
            <td>{if in_array('city',$uneditablefields)}{$clientcity}{else}<input type="text" name="city" value="{$clientcity}" size="25" />{/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareastate}</td>
            <td>{if in_array('state',$uneditablefields)}{$clientstate}{else}<input type="text" name="state" value="{$clientstate}" size="25" />{/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareapostcode}</td>
            <td>{if in_array('postcode',$uneditablefields)}{$clientpostcode}{else}<input type="text" name="postcode" value="{$clientpostcode}" size="25" />{/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareacountry}</td>
            <td>{if in_array('country',$uneditablefields)}{$clientcountry}{else}{$clientcountriesdropdown}{/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.clientareaphonenumber}</td>
            <td>{if in_array('phonenumber',$uneditablefields)}{$clientphonenumber}{else}<input type="text" name="phonenumber" value="{$clientphonenumber}" size="25" />{/if}</td>
          </tr>
      </table></td>
    </tr>
  </table>
  <br />
  <table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td width="150" class="fieldarea">{$LANG.paymentmethod}</td>
            <td><select name="paymentmethod">
                <option value="none">{$LANG.paymentmethoddefault}</option>
{foreach from=$paymentmethods item=method}
                <option value="{$method.sysname}"{if $method.sysname eq $defaultpaymentmethod} selected="selected"{/if}>{$method.name}</option>
{/foreach}
              </select></td>
          </tr>
          <tr>
            <td width="150" class="fieldarea">{$LANG.defaultbillingcontact}</td>
            <td><select name="billingcid">
                <option value="0">{$LANG.usedefaultcontact}</option>
{foreach key=num item=contact from=$contacts}
                <option value="{$contact.id}"{if $contact.id eq $billingcid} selected="selected"{/if}>{$contact.name}</option>
{/foreach}
              </select></td>
          </tr>
          {if $emailoptoutenabled}
          <tr>
            <td class="fieldarea">{$LANG.emailoptout}</td>
            <td><input type="checkbox" value="1" name="emailoptout" id="emailoptout" {if $emailoptout} checked{/if} /> {$LANG.emailoptoutdesc}</td>
          </tr>
          {/if}
      </table></td>
    </tr>
  </table>
  {if $customfields} <br />
  <table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          {foreach key=num item=customfield from=$customfields}
          <tr>
            <td width="150" class="fieldarea">{$customfield.name}</td>
            <td>{$customfield.input} {$customfield.required} {$customfield.description}</td>
          </tr>
          {/foreach}
      </table></td>
    </tr>
  </table>
  {/if}
  <p align="center">
    <input type="submit" value="{$LANG.clientareasavechanges}" class="button" />
    <input type="reset" value="{$LANG.clientareacancel}" class="button" />
  </p>
</form>