<div class="heading"><div class="text">{$LANG.ordersummary}</div><div id="cartloader"><img src="templates/orderforms/{$carttpl}/images/summaryloading.gif" border="0" /></div><div style="clear: both;"></div></div>

{if !$cartitems}

<br />

<div align="center">{$LANG.ordersummarybegin}</div>

<br /><br />

{else}

{if !$loggedin && $currencies}
<div class="currency">{$LANG.choosecurrency}: <select id="currency" onchange="currencychange()">{foreach from=$currencies item=curr}
<option value="{$curr.id}"{if $curr.id eq $currency.id} selected{/if}>{$curr.code}</option>
{/foreach}</select></div>
{/if}

<div class="overflow">

{foreach key=num item=product from=$products}

<div class="itemdesc">
<strong>{$product.productinfo.groupname} - {$product.productinfo.name}</strong><br />
{if $product.domain}{$product.domain}<br />{/if}
{if $product.configoptions}
{foreach key=confnum item=configoption from=$product.configoptions}- {$configoption.name}: {if $configoption.type eq 1 || $configoption.type eq 2}{$configoption.option}{elseif $configoption.type eq 3}{if $configoption.qty}{$LANG.yes}{else}{$LANG.no}{/if}{elseif $configoption.type eq 4}{$configoption.qty} x {$configoption.option}{/if}<br />{/foreach}
{/if}
</div>
<div class="itemmanage">
<a href="#" onclick="removeItem('p','{$num}');return false"><img src="templates/orderforms/{$carttpl}/images/delete.png" border="0" /></a>
</div>
<div class="itemprice">
{$product.pricingtext}{if $product.proratadate}<br />({$LANG.orderprorata} {$product.proratadate}){/if}
</div>
<div class="itemend"></div>

{foreach key=addonnum item=addon from=$product.addons}

<div class="itemdesc">
<strong>{$LANG.orderaddon}</strong><br />
{$addon.name}
</div>
<div class="itemmanage"></div>
<div class="itemprice">
{$addon.pricingtext}
</div>
<div class="itemend"></div>

{/foreach}

{/foreach}

{foreach key=num item=addon from=$addons}

<div class="itemdesc">
<strong>{$LANG.orderaddon}</strong><br />
<i>{$addon.productname}{if $addon.domainname} - {$addon.domainname}{/if}</i><br />
{$addon.name}
</div>
<div class="itemmanage">
<a href="#" onclick="removeItem('a','{$num}');return false"><img src="templates/orderforms/{$carttpl}/images/delete.png" border="0" /></a>
</div>
<div class="itemprice">
{$addon.pricingtext}
</div>
<div class="itemend"></div>

{/foreach}

{foreach key=num item=domain from=$domains}

<div class="itemdesc">
<strong>{if $domain.type eq "register"}{$LANG.orderdomainregistration}{else}{$LANG.orderdomaintransfer}{/if}</strong><br />
{$domain.domain}<br />
{$domain.regperiod} {$LANG.orderyears}<br />
{if $domain.dnsmanagement}&nbsp;+ {$LANG.domaindnsmanagement}<br />{/if}
{if $domain.emailforwarding}&nbsp;+ {$LANG.domainemailforwarding}<br />{/if}
{if $domain.idprotection}&nbsp;+ {$LANG.domainidprotection}<br />{/if}
</div>
<div class="itemmanage">
<a href="#" onclick="removeItem('d','{$num}');return false"><img src="templates/orderforms/{$carttpl}/images/delete.png" border="0" /></a>
</div>
<div class="itemprice">
{$domain.price}
</div>
<div class="itemend"></div>

{/foreach}

{foreach key=num item=domain from=$renewals}

<div class="itemdesc">
<strong>{$LANG.domainrenewal}</strong><br />
{$domain.domain}<br />
{$domain.regperiod} {$LANG.orderyears}<br />
{if $domain.dnsmanagement}&nbsp;+ {$LANG.domaindnsmanagement}<br />{/if}
{if $domain.emailforwarding}&nbsp;+ {$LANG.domainemailforwarding}<br />{/if}
{if $domain.idprotection}&nbsp;+ {$LANG.domainidprotection}<br />{/if}
</div>
<div class="itemmanage">
<a href="#" onclick="removeItem('r','{$num}');return false"><img src="templates/orderforms/{$carttpl}/images/delete.png" border="0" /></a>
</div>
<div class="itemprice">
{$domain.price}
</div>
<div class="itemend"></div>

{/foreach}

</div>

<div class="totalduelabel">
{$LANG.ordersubtotal}: <b>{$subtotal}</b><br />
{if $promotioncode}{$LANG.orderdiscount}: <b>{$discount}</b><br />{/if}
{if $taxrate}{$taxname} @ {$taxrate}%: <b>{$taxtotal}</b><br />{/if}
{if $taxrate2}{$taxname2} @ {$taxrate2}%: <b>{$taxtotal2}</b><br />{/if}
{$LANG.ordertotalduetoday}:
</div>
<div class="totaldue">{$total}</div>

<div class="subtotals" id="promocodeholder">
{if $promotype}
{$LANG.cartpromo}: {$promovalue}{if $promotype eq "Percentage"}%{elseif $promotype eq "Fixed Amount"}{/if} {$promorecurring}<br />
<a href="#" onclick="removepromo();return false">{$LANG.cartremovepromo}</a>
{else}
<input type="text" id="promocode" size="20" style="font-size:9px;" value="{$LANG.cartenterpromo}" onfocus="if(this.value=='{$LANG.cartenterpromo}')this.value=''" /> <input type="button" value="{$LANG.go}" style="font-size:9px;" onclick="applypromo()" />
{/if}
</div>

{if $totalrecurringmonthly || $totalrecurringquarterly || $totalrecurringsemiannually || $totalrecurringannually || $totalrecurringbiennially || $totalrecurringtriennially}
<div class="subtotals">
<i>{$LANG.cartrecurringcharges}:</i><br />
{if $totalrecurringmonthly}{$totalrecurringmonthly} {$LANG.orderpaymenttermmonthly}<br />{/if}
{if $totalrecurringquarterly}{$totalrecurringquarterly} {$LANG.orderpaymenttermquarterly}<br />{/if}
{if $totalrecurringsemiannually}{$totalrecurringsemiannually} {$LANG.orderpaymenttermsemiannually}<br />{/if}
{if $totalrecurringannually}{$totalrecurringannually} {$LANG.orderpaymenttermannually}<br />{/if}
{if $totalrecurringbiennially}{$totalrecurringbiennially} {$LANG.orderpaymenttermbiennially}<br />{/if}
{if $totalrecurringtriennially}{$totalrecurringtriennially} {$LANG.orderpaymenttermtriennially}<br />{/if}
</div>
{/if}

<div align="center"><input type="button" value="{$LANG.emptycart}" onclick="emptyCart()" /></div>
<br />

{/if}