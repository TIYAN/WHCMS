<div class="totalduetoday">{$LANG.ordertotalduetoday}: {$producttotals.pricing.totaltoday}</div>
{if count($producttotals.pricing.recurring)}<div class="totalrecurring">{$LANG.ordertotalrecurring} {foreach from=$producttotals.pricing.recurring key=cycle item=amount}
    {$cycle}: {$amount}<br />
{/foreach}</div>{/if}