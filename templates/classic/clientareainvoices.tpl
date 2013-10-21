<p class="heading2">{$LANG.invoices}</p>

<table width="100%" cellspacing="0" cellpadding="0"><tr><td>{$numproducts} {$LANG.recordsfound},  {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</td><td align="right">{if $prevpage}<a href="clientarea.php?action=invoices&amp;page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="clientarea.php?action=invoices&amp;page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td></tr></table>

<br />

<table class="clientareatable" cellspacing="1">
<tr class="clientareatableheading">
<td>{$LANG.invoicestitle}</td><td>{$LANG.invoicesdatecreated}</td><td>{$LANG.invoicesdatedue}</td><td>{$LANG.invoicestotal}</td><td>{$LANG.invoicesstatus}</td><td></td></tr>
{foreach key=num item=invoice from=$invoices}
<tr class="clientareatableactive"><td><a href="viewinvoice.php?id={$invoice.id}" target="_blank">{$invoice.invoicenum}</a></td><td>{$invoice.datecreated}</td><td>{$invoice.datedue}</td><td>{$invoice.total}</td><td>{$invoice.status}</td><td><a href="viewinvoice.php?id={$invoice.id}" target="_blank">{$LANG.invoicesview}</a></td></tr>
{foreachelse}
<tr class="clientareatableactive"><td colspan=6>{$LANG.norecordsfound}</td></tr>
{/foreach}
</table>

<br />

<table width="100%" cellspacing="0" cellpadding="0"><tr><td>{$LANG.show}: <a href="clientarea.php?action=invoices&itemlimit=10">10</a> <a href="clientarea.php?action=invoices&itemlimit=25">25</a> <a href="clientarea.php?action=invoices&itemlimit=50">50</a> <a href="clientarea.php?action=invoices&itemlimit=100">100</a> <a href="clientarea.php?action=invoices&itemlimit=all">{$LANG.all}</a></td><td align="right">{if $prevpage}<a href="clientarea.php?action=invoices&amp;page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="clientarea.php?action=invoices&amp;page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td></tr></table>