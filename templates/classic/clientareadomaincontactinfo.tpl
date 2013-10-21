{literal}
<script language="javascript">
function usedefaultwhois(id) {
	jQuery("."+id.substr(0,id.length-1)+"customwhois").attr("disabled", true);
	jQuery("."+id.substr(0,id.length-1)+"defaultwhois").attr("disabled", false);
	jQuery('#'+id.substr(0,id.length-1)+'1').attr("checked", "checked");
}
function usecustomwhois(id) {
	jQuery("."+id.substr(0,id.length-1)+"customwhois").attr("disabled", false);
	jQuery("."+id.substr(0,id.length-1)+"defaultwhois").attr("disabled", true);
	jQuery('#'+id.substr(0,id.length-1)+'2').attr("checked", "checked");
}
</script>
{/literal}

<p class="heading2">{$LANG.domaincontactinfo}</p>

<p>{$LANG.domainname}: <strong>{$domain}</strong> {foreach key=contactdetail name=contacts item=values from=$contactdetails} {if !$smarty.foreach.contacts.first} - <a href="clientarea.php?action=domaincontacts#{$contactdetail}">{$LANG.jumpto} {$contactdetail}</a>{/if}{/foreach}
</p>

{if $successful}<div class="successbox">{$LANG.changessavedsuccessfully}</div><br />{/if}
{if $error}<div class="errorbox">{$error}</div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action=domaincontacts">

<input type="hidden" name="sub" value="save">
<input type="hidden" name="domainid" value="{$domainid}">

{foreach from=$contactdetails name=contactdetails key=contactdetail item=values}

<p><strong><a name="{$contactdetail}"></a>{$contactdetail}</strong>{if !$smarty.foreach.contactdetails.first} - <a href="clientarea.php?action=domaincontacts#">{$LANG.top}</a>{/if}</p>

<p><input type="radio" name="wc[{$contactdetail}]" id="{$contactdetail}1" value="contact" onclick="usedefaultwhois(id)"{if $defaultns} checked{/if} /> <label for="{$contactdetail}1">{$LANG.domaincontactusexisting}</label></p>
    <table id="{$contactdetail}defaultwhois">
      <tr>
        <td width="150" align="right">{$LANG.domaincontactchoose}</td>
        <td><select class="{$contactdetail}defaultwhois" name="sel[{$contactdetail}]" id="{$contactdetail}3" onclick="usedefaultwhois(id)">
            <option value="u{$clientsdetails.userid}">{$LANG.domaincontactprimary}</option>
            {foreach key=num item=contact from=$contacts}
            <option value="c{$contact.id}">{$contact.name}</option>
            {/foreach}
          </select></td>
      </tr>
  </table>
<p><input type="radio" name="wc[{$contactdetail}]" id="{$contactdetail}2" value="custom" onclick="usecustomwhois(id)"{if !$defaultns} checked{/if} /> <label for="{$contactdetail}2">{$LANG.domaincontactusecustom}</label></p>

<table width="100%">
{foreach key=name item=value from=$values}
<tr><td width="150" align="right">{$name}</td><td><input type="text" name="contactdetails[{$contactdetail}][{$name}]" value="{$value}" size="30" class="{$contactdetail}customwhois" /></td></tr>
{/foreach}
</table>

{/foreach}

<p align="center"><input type="submit" value="{$LANG.clientareasavechanges}" class="buttongo" /></p>

</form>

<form method="post" action="{$smarty.server.PHP_SELF}?action=domaindetails">
<input type="hidden" name="id" value="{$domainid}" />
<p align="center"><input type="submit" value="{$LANG.clientareabacklink}" class="button" /></p>
</form>