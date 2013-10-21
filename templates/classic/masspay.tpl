<p class="heading2">{$LANG.masspaytitle}</p>

<p>{$LANG.masspaydescription}</p>

<form method="post" action="clientarea.php?action=masspay">
<input type="hidden" name="geninvoice" value="true" />

<table align="center" style="width:90%" class="clientareatable" cellspacing="1">
  <tr class="clientareatableheading">
    <td>{$LANG.invoicesdescription}</td>
    <td width="130">{$LANG.invoicesamount}</td>
  </tr>
  {foreach from=$invoiceitems key=invid item=invoiceitem}
  <tr class="clientareatablesuspended">
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
  <tr class="clientareatableheading">
    <td style="text-align:right;">{$LANG.invoicessubtotal}:</td>
    <td>{$subtotal}</td>
  </tr>
  {if $tax}<tr class="clientareatableheading">
    <td style="text-align:right;">{$LANG.invoicestax}:</td>
    <td>{$tax}</td>
  </tr>{/if}
  {if $tax2}<tr class="clientareatableheading">
    <td style="text-align:right;">{$LANG.invoicestax} 2:</td>
    <td>{$tax2}</td>
  </tr>{/if}
  {if $credit}<tr class="clientareatableheading">
    <td style="text-align:right;">{$LANG.invoicescredit}:</td>
    <td>{$credit}</td>
  </tr>{/if}
  {if $partialpayments}<tr class="clientareatableheading">
    <td style="text-align:right;">{$LANG.invoicespartialpayments}:</td>
    <td>{$partialpayments}</td>
  </tr>{/if}
  <tr class="clientareatableheading">
    <td style="text-align:right;">{$LANG.invoicestotaldue}:</td>
    <td>{$total}</td>
  </tr>
</table>

<h3>{$LANG.orderpaymentmethod}</h3>

<p align="center">{foreach key=num item=gateway from=$gateways}
    <input type="radio" name="paymentmethod" value="{$gateway.sysname}" id="pgbtn{$num}"{if $num eq 0} checked{/if} />
    <label for="pgbtn{$num}">{$gateway.name}</label>
    {/foreach}</p>

<p align="center"><input type="submit" value="{$LANG.masspaymakepayment}" class="buttongo" /></p>

</form>