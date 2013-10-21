<link rel="stylesheet" type="text/css" href="templates/orderforms/web20cart/style.css" />

<h2>{$LANG.masspaytitle}</h2>

<p>{$LANG.masspaydescription}</p>

<form method="post" action="clientarea.php?action=masspay">
<input type="hidden" name="geninvoice" value="true" />

<table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" class="data">
  <tr>
    <th>{$LANG.invoicesdescription}</th>
    <th width="130">{$LANG.invoicesamount}</th>
  </tr>
  {foreach from=$invoiceitems key=invid item=invoiceitem}
  <tr>
    <td colspan="2" style="text-align:left;">
      <strong>{$LANG.invoicenumber} {$invid}</strong>
      <input type="hidden" name="invoiceids[]" value="{$invid}" />
    </td>
  </tr>
  {foreach from=$invoiceitem item=item}
    <tr>
      <td>{$item.description}</td>
      <td>{$item.amount}</td>
    </tr>
  {/foreach}
  {foreachelse}
  <tr>
    <td colspan="6" align="center">{$LANG.norecordsfound}</td>
  </tr>
  {/foreach}
  <tr class="carttablesummary">
    <td style="text-align:right;">{$LANG.invoicessubtotal}:</td>
    <td>{$subtotal}</td>
  </tr>
  {if $tax}<tr class="carttablesummary">
    <td style="text-align:right;">{$LANG.invoicestax}:</td>
    <td>{$tax}</td>
  </tr>{/if}
  {if $tax2}<tr class="carttablesummary">
    <td style="text-align:right;">{$LANG.invoicestax} 2:</td>
    <td>{$tax2}</td>
  </tr>{/if}
  {if $credit}<tr class="carttablesummary">
    <td style="text-align:right;">{$LANG.invoicescredit}:</td>
    <td>{$credit}</td>
  </tr>{/if}
  {if $partialpayments}<tr class="carttablerecurring">
    <td style="text-align:right;">{$LANG.invoicespartialpayments}:</td>
    <td>{$partialpayments}</td>
  </tr>{/if}
  <tr class="carttabledue">
    <td style="text-align:right;">{$LANG.invoicestotaldue}:</td>
    <td>{$total}</td>
  </tr>
</table>

<h3>{$LANG.orderpaymentmethod}</h3>

<p align="center">{foreach key=num item=gateway from=$gateways}
    <input type="radio" name="paymentmethod" value="{$gateway.sysname}" id="pgbtn{$num}"{if $num eq 0} checked{/if} />
    <label for="pgbtn{$num}">{$gateway.name}</label>
    {/foreach}</p>

<p align="center"><input type="submit" value="{$LANG.masspaymakepayment}" /></p>

</form>