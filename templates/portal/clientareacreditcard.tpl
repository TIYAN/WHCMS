{include file="$template/clientareadetailslinks.tpl"}

<h2>{$LANG.clientareanavchangecc}</h2>

{if $remoteupdatecode}

  <div align="center">
    {$remoteupdatecode}
  </div>

{else}

{if $successful}<div class="successbox">{$LANG.changessavedsuccessfully}</div><br />{/if}
{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<form method="post" action="{$smarty.server.PHP_SELF}?action=creditcard">
<input type="hidden" name="submit" value="true" />

  <table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr><td width="150" class="fieldarea">{$LANG.creditcardcardtype}</td><td>{$cardtype}</td></tr>
          <tr><td class="fieldarea">{$LANG.creditcardcardnumber}</td><td>{$cardnum}{if $allowcustomerdelete && $cardtype} &nbsp;&nbsp;&nbsp; <input type="button" value="{$LANG.creditcarddelete}" class="button" onclick="window.location='clientarea.php?action=creditcard&delete=true'" />{/if}</td></tr>
          <tr><td class="fieldarea">{$LANG.creditcardcardexpires}</td><td>{$cardexp}</td></tr>
          {if $cardissuenum}<tr><td class="fieldarea">{$LANG.creditcardcardissuenum}</td><td>{$cardissuenum}</td></tr>{/if}
          {if $cardstart}<tr><td class="fieldarea">{$LANG.creditcardcardstart}</td><td>{$cardstart}</td></tr>{/if}
      </table></td>
    </tr>
  </table>

<h2>{$LANG.creditcardenternewcard}</h2>

<table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr><td width="150" class="fieldarea">{$LANG.creditcardcardtype}</td><td><select name="cctype">
{foreach key=num item=cardtype from=$acceptedcctypes}
<option{if $cardtype eq $cctype} selected{/if}>{$cardtype}</option>
{/foreach}
</select></td></tr>
          <tr><td class="fieldarea">{$LANG.creditcardcardnumber}</td><td><input type="text" name="ccnumber" size="25" autocomplete="off" value="{$ccnumber}" /></td></tr>
          <tr><td class="fieldarea">{$LANG.creditcardcardexpires}</td><td><select name="ccexpirymonth" id="ccexpirymonth">{foreach from=$months item=month}
<option{if $ccexpirymonth eq $month} selected{/if}>{$month}</option>
{/foreach}</select> / <select name="ccexpiryyear">
{foreach from=$expiryyears item=year}
<option{if $ccexpiryyear eq $year} selected{/if}>{$year}</option>
{/foreach}</select></td></tr>
          {if $showccissuestart}
          <tr><td class="fieldarea">{$LANG.creditcardcardstart}</td><td><select name="ccstartmonth" id="ccstartmonth">{foreach from=$months item=month}
<option{if $ccstartmonth eq $month} selected{/if}>{$month}</option>
{/foreach}</select> / <select name="ccstartyear">
{foreach from=$startyears item=year}
<option{if $ccstartyear eq $year} selected{/if}>{$year}</option>
{/foreach}</select></td></tr>
          <tr><td class="fieldarea">{$LANG.creditcardcardissuenum}</td><td><input type="text" name="ccissuenum" value="{$ccissuenum}" size="5" maxlength="3" /></td></tr>
          {/if}
          <tr><td class="fieldarea">{$LANG.creditcardcvvnumber}</td><td><input type="text" name="cardcvv" value="{$cardcvv}" size="5" maxlength="3" /></td></tr>
      </table></td>
    </tr>
  </table>

<p align="center"><input type="submit" value="{$LANG.clientareasavechanges}" class="button" /> <input type="reset" value="{$LANG.clientareacancel}" class="button" /></p>

</form>

{/if}