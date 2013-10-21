<script>
function toggleCheckboxes() {ldelim}
    jQuery(".domids").attr('checked',!jQuery(".domids:first").is(':checked'));
{rdelim}
</script>

<h2>{$LANG.clientareanavdomains}</h2>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <td>{$numproducts} {$LANG.recordsfound},  {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</td>
    <td align="right"><form method="post" action="clientarea.php?action=domains"><input type="text" name="q" value="{if $q}{$q}{else}{$LANG.searchenterdomain}{/if}" class="searchinput" onfocus="if(this.value=='{$LANG.searchenterdomain}')this.value=''" /> <input type="submit" value="{$LANG.searchfilter}" class="searchinput" /></form></td>
  </tr>
</table>
<br />

<form method="post" action="clientarea.php?action=bulkdomain">

<table class="data" width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <th width="20"><input type="checkbox" onclick="toggleCheckboxes()" /></th>
    <th><a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&orderby=regdate">{$LANG.clientareahostingregdate}</a>{if $orderby eq "regdate"} <img src="images/{$sort}.gif" alt="" border="0" />{/if}</th>
    <th><a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&orderby=domain">{$LANG.clientareahostingdomain}</a>{if $orderby eq "domain"} <img src="images/{$sort}.gif" alt="" border="0" />{/if}</th>
    <th><a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&orderby=nextduedate">{$LANG.clientareahostingnextduedate}</a>{if $orderby eq "nextduedate"} <img src="images/{$sort}.gif" alt="" border="0" />{/if}</th>
    <th><a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&orderby=price">{$LANG.orderprice}</a>{if $orderby eq "price"} <img src="images/{$sort}.gif" alt="" border="0" />{/if}</th>
    <th width="20">&nbsp;</th>
  </tr>
  {foreach key=num item=domain from=$domains}
  <tr class="clientareatable{$domain.rawstatus}">
    <td><input type="checkbox" name="domids[]" class="domids" value="{$domain.id}" /></td>
    <td>{$domain.registrationdate}</td>
    <td><a href="http://{$domain.domain}" target="_blank">{$domain.domain}</a></td>
    <td>{$domain.nextduedate}</td>
    <td>{$domain.amount}</td>
    <td><a href="{$smarty.server.PHP_SELF}?action=domaindetails&id={$domain.id}'"><img src="images/viewdetails.gif" border="0" /></a></td>
  </tr>
  {foreachelse}
  <tr>
    <td colspan="6">{$LANG.norecordsfound}</td>
  </tr>
  {/foreach}
</table>

<p><select name="update" onchange="submit()">
<option>{$LANG.domainbulkmanagement}</option>
<option>------------------------------</option>
<option value="nameservers">{$LANG.domainmanagens}</option>
<option value="autorenew">{$LANG.domainautorenewstatus}</option>
<option value="reglock">{$LANG.domainreglockstatus}</option>
<option value="contactinfo">{$LANG.domaincontactinfoedit}</option>
<option value="renew">{$LANG.domainmassrenew}</option>
</select> <input type="submit" value="{$LANG.go}" /></p>

</form>

<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <td>{$LANG.show}: <a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&itemlimit=10">10</a> <a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&itemlimit=25">25</a> <a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&itemlimit=50">50</a> <a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&itemlimit=100">100</a> <a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&itemlimit=all">{$LANG.all}</a></td>
    <td align="right">{if $prevpage}<a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&amp;page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&amp;page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td>
  </tr>
</table>
<br />
<table border="0" align="center" cellpadding="10" cellspacing="0">
  <tr>
    <td width="10" align="right" class="clientareatableactive">&nbsp;</td>
    <td>{$LANG.clientareaactive}</td>
    <td width="10" align="right" class="clientareatablepending">&nbsp;</td>
    <td>{$LANG.clientareapending}</td>
    <td width="10" align="right" class="clientareatablependingtransfer">&nbsp;</td>
    <td>{$LANG.clientareapendingtransfer}</td>
    <td width="10" align="right" class="clientareatableterminated">&nbsp;</td>
    <td>{$LANG.clientareaexpired}/{$LANG.clientareacancelled}</td>
    <td width="10" align="right" class="clientareatablefraud">&nbsp;</td>
    <td>{$LANG.clientareafraud}</td>
  </tr>
</table><br />