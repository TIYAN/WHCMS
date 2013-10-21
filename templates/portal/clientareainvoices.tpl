<h2 >{$LANG.invoices}</h2>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <td>{$numproducts} {$LANG.recordsfound},  {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</td>
    <td align="right">{if $prevpage}<a href="clientarea.php?action=invoices&amp;page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="clientarea.php?action=invoices&amp;page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td>
  </tr>
</table>
<br />
<table class="data" width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <th>{$LANG.invoicestitle}</th>
    <th>{$LANG.invoicesdatecreated}</th>
    <th>{$LANG.invoicesdatedue}</th>
    <th>{$LANG.invoicestotal}</th>
    <th>{$LANG.invoicesstatus}</th>
    <th>&nbsp;</th>
  </tr>
  {foreach key=num item=invoice from=$invoices}
  <tr>
    <td><a href="viewinvoice.php?id={$invoice.id}" target="_blank">{$invoice.invoicenum}</a></td>
    <td>{$invoice.datecreated}</td>
    <td>{$invoice.datedue}</td>
    <td>{$invoice.total}</td>
    <td>{$invoice.status}</td>
    <td><a href="viewinvoice.php?id={$invoice.id}" target="_blank">{$LANG.invoicesview}</a></td>
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
    <td>{$LANG.show}: <a href="clientarea.php?action=invoices&itemlimit=10">10</a> <a href="clientarea.php?action=invoices&itemlimit=25">25</a> <a href="clientarea.php?action=invoices&itemlimit=50">50</a> <a href="clientarea.php?action=invoices&itemlimit=100">100</a> <a href="clientarea.php?action=invoices&itemlimit=all">{$LANG.all}</a></td>
    <td align="right">{if $prevpage}<a href="clientarea.php?action=invoices&amp;page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="clientarea.php?action=invoices&amp;page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td>
  </tr>
</table><br />