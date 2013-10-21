<p>{$LANG.clientareaheader}</p>
{foreach from=$addons_html item=addon_html}
<div style="margin:15px 0 15px 0;">{$addon_html}</div>
{/foreach}
{if in_array('tickets',$contactpermissions)}
<h2><strong>{$clientsstats.numactivetickets}</strong> {$LANG.supportticketsopentickets}</h2>
<p><a href="submitticket.php">{$LANG.supportticketssubmitticket}</a></p>
<table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" class="data">
  <tr>
    <th>{$LANG.supportticketsdate}</th>
    <th>{$LANG.supportticketssubject}</th>
    <th>{$LANG.supportticketsstatus}</th>
    <th>{$LANG.supportticketsticketurgency}</th>
  </tr>
  {foreach key=num item=ticket from=$tickets}
  <tr>
    <td>{$ticket.date}</td>
    <td><div align="left"><img src="images/article.gif" hspace="5" align="middle" alt="" /><a href="viewticket.php?tid={$ticket.tid}&amp;c={$ticket.c}">{if $ticket.unread}<strong>{/if}#{$ticket.tid} - {$ticket.subject}{if $ticket.unread}</strong>{/if}</a></div></td>
    <td width="120">{$ticket.status}</td>
    <td width="80">{$ticket.urgency}</td>
  </tr>
  {foreachelse}
  <tr>
    <td colspan="4" align="center">{$LANG.norecordsfound}</td>
  </tr>
  {/foreach}
</table>
{/if}
{if in_array('invoices',$contactpermissions)}
<h2><strong>{$clientsstats.numdueinvoices}</strong> {$LANG.invoicesdue}</h2>
<form method="post" action="clientarea.php?action=masspay">
<table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" class="data">
  <tr>
    {if $masspay}<th width="15"></th>{/if}
    <th>{$LANG.invoicenumber}</th>
    <th>{$LANG.invoicesdatecreated}</th>
    <th>{$LANG.invoicesdatedue}</th>
    <th>{$LANG.invoicestotal}</th>
    <th>{$LANG.invoicesbalance}</th>
    <th>{$LANG.invoicesstatus}</th>
    <th>&nbsp;</th>
  </tr>
  {foreach key=num item=invoice from=$invoices}
  <tr>
    {if $masspay}<td><input type="checkbox" name="invoiceids[]" value="{$invoice.id}" /></td>{/if}
    <td><a href="viewinvoice.php?id={$invoice.id}" target="_blank">{$invoice.invoicenum}</a></td>
    <td>{$invoice.datecreated}</td>
    <td>{$invoice.datedue}</td>
    <td>{$invoice.total}</td>
    <td>{$invoice.balance}</td>
    <td>{$invoice.status}</td>
    <td><a href="viewinvoice.php?id={$invoice.id}" target="_blank">{$LANG.invoicesview}</a></td>
  </tr>
  {foreachelse}
  <tr>
    <td colspan="{if $masspay}8{else}7{/if}" align="center">{$LANG.norecordsfound}</td>
  </tr>
  {/foreach}
  {if $invoices}
  <tr style="font-weight:bold;">
    <td colspan="{if $masspay}4{else}3{/if}">{if $masspay}<input type="submit" value="{$LANG.masspayselected}" />{/if}</td>
    <td style="text-align:right;">{$LANG.invoicestotaldue}</td>
    <td>{$totalbalance}</td>
    <td colspan="2">{if $masspay}<a href="clientarea.php?action=masspay&all=true">{$LANG.masspayall}</a>{/if}</td>
  </tr>
  {/if}
</table>
</form>
{/if}
{if $files}
<h2>{$LANG.clientareafiles}</h2>
<table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" class="data">
  <tr>
    <th>{$LANG.clientareafilesdate}</th>
    <th>{$LANG.clientareafilesfilename}</th>
  </tr>
  {foreach key=num item=file from=$files}
  <tr>
    <td>{$file.date}</td>
    <td><img src="images/file.png" hspace="5" align="middle" alt="" /> <a href="dl.php?type=f&id={$file.id}"><strong>{$file.title}</strong></a></td>
  </tr>
  {/foreach}
</table>
{/if}