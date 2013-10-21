<h2>{$LANG.clientareaproducts}</h2>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <td>{$numproducts} {$LANG.recordsfound},  {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</td>
    <td align="right"><form method="post" action="clientarea.php?action=products"><input type="text" name="q" value="{if $q}{$q}{else}{$LANG.searchenterdomain}{/if}" class="searchinput" onfocus="if(this.value=='{$LANG.searchenterdomain}')this.value=''" /> <input type="submit" value="{$LANG.searchfilter}" class="searchinput" /></form></td>
  </tr>
</table>
<br />
<table class="data" width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <th><a href="clientarea.php?action=products{if $q}&q={$q}{/if}&orderby=product">{$LANG.orderproduct}</a>{if $orderby eq "product"} <img src="images/{$sort}.gif" alt="" border="0" />{/if}</th>
    <th><a href="clientarea.php?action=products{if $q}&q={$q}{/if}&orderby=price">{$LANG.orderprice}</a>{if $orderby eq "price"} <img src="images/{$sort}.gif" alt="" border="0" />{/if}</th>
    <th><a href="clientarea.php?action=products{if $q}&q={$q}{/if}&orderby=billingcycle">{$LANG.orderbillingcycle}</a>{if $orderby eq "billingcycle"} <img src="images/{$sort}.gif" alt="" border="0" />{/if}</th>
    <th><a href="clientarea.php?action=products{if $q}&q={$q}{/if}&orderby=nextduedate">{$LANG.clientareahostingnextduedate}</a>{if $orderby eq "nextduedate"} <img src="images/{$sort}.gif" alt="" border="0" />{/if}</th>
    <th width="20">&nbsp;</th>
  </tr>
  {foreach key=num item=service from=$services}
  <tr class="clientareatable{$service.class}">
    <td>{$service.group} - {$service.product}{if $service.domain}<br />
    <a href="http://{$service.domain}" target="_blank">{$service.domain}</a>{/if}</td>
    <td>{$service.amount}</td>
    <td>{$service.billingcycle}</td>
    <td>{$service.nextduedate}</td>
    <td><form method="post" action="{$smarty.server.PHP_SELF}?action=productdetails">
        <input type="hidden" name="id" value="{$service.id}" />
        <input type="image" src="images/viewdetails.gif" alt="{$LANG.clientareaviewdetails}" />
    </form></td>
  </tr>
  {foreachelse}
  <tr>
    <td colspan="6">{$LANG.norecordsfound}</td>
  </tr>
  {/foreach}
</table>
<br />
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <td>{$LANG.show}: <a href="clientarea.php?action=products{if $q}&q={$q}{/if}&itemlimit=10">10</a> <a href="clientarea.php?action=products{if $q}&q={$q}{/if}&itemlimit=25">25</a> <a href="clientarea.php?action=products{if $q}&q={$q}{/if}&itemlimit=50{if $q}&q={$q}{/if}">50</a> <a href="clientarea.php?action=products{if $q}&q={$q}{/if}&itemlimit=100">100</a> <a href="clientarea.php?action=products{if $q}&q={$q}{/if}&itemlimit=all">{$LANG.all}</a></td>
    <td align="right">{if $prevpage}<a href="clientarea.php?action=products{if $q}&q={$q}{/if}&amp;page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="clientarea.php?action=products{if $q}&q={$q}{/if}&amp;page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td>
  </tr>
</table>
<br />
<table border="0" align="center" cellpadding="10" cellspacing="0">
  <tr>
    <td width="10" align="right" class="clientareatableactive">&nbsp;</td>
    <td>{$LANG.clientareaactive}</td>
    <td width="10" align="right" class="clientareatablepending">&nbsp;</td>
    <td>{$LANG.clientareapending}</td>
    <td width="10" align="right" class="clientareatablesuspended">&nbsp;</td>
    <td>{$LANG.clientareasuspended}</td>
    <td width="10" align="right" class="clientareatableterminated">&nbsp;</td>
    <td>{$LANG.clientareaterminated}</td>
  </tr>
</table><br />