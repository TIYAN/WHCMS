<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/style.css" />
<link rel="stylesheet" type="text/css" href="templates/orderforms/{$carttpl}/uistyle.css" />

<div id="order-comparison">

{include file="orderforms/comparison/comparisonsteps.tpl" step=3}

<div class="cartcontainer">

<h2>{$LANG.orderconfirmation|strtolower}</h2>

<br />

<p>{$LANG.orderreceived}</p>

<div class="cartbox">
<p align="center"><strong>{$LANG.ordernumberis} {$ordernumber}</strong></p>
</div>

<p>{$LANG.orderfinalinstructions}</p>

{if $invoiceid && !$ispaid}
<div class="errorbox">{$LANG.ordercompletebutnotpaid}</div>
<p align="center"><a href="viewinvoice.php?id={$invoiceid}" target="_blank">{$LANG.invoicenumber}{$invoiceid}</a></p>
{/if}

{foreach from=$addons_html item=addon_html}
<div style="margin:15px 0 15px 0;">{$addon_html}</div>
{/foreach}

{if $ispaid}
<!-- Enter any HTML code which needs to be displayed once a user has completed the checkout of their order here - for example conversion tracking and affiliate tracking scripts -->
{/if}

<br /><br />

<p align="center"><a href="clientarea.php">{$LANG.ordergotoclientarea}</a></p>

<br />

</div>

</div>