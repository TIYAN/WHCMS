<html>
<head>
<title>{$companyname} - {* This code should be uncommented for EU companies using the sequential invoice numbering so that when unpaid it is shown as a proforma invoice {if $status eq "Paid"}*}{$LANG.invoicenumber}{*{else}{$LANG.proformainvoicenumber}{/if}*}{$invoicenum}</title>
<link rel="stylesheet" type="text/css" href="templates/{$template}/invoicestyle.css">
</head>
<body bgcolor="#efefef">

{if $error}
<p style="color:#cc0000;">{$LANG.invoiceserror}</p>
{else}

<table id="wrapper" cellspacing="1" cellpadding="10" bgcolor="#cccccc" align="center"><tr><td bgcolor="#ffffff">

<table width="100%"><tr><td width="50%">

{if $logo}<p><img src="{$logo}"></p>{else}<h1>{$companyname}</h1>{/if}

</td><td width="50%" align="center">

{if $status eq "Unpaid"}
<font class="unpaid">{$LANG.invoicesunpaid}</font><br />
{if $allowchangegateway}
<form method="post" action="{$smarty.server.PHP_SELF}?id={$invoiceid}">{$gatewaydropdown}</form>
{else}
{$paymentmethod}<br />
{/if}
{$paymentbutton}
{elseif $status eq "Paid"}
<font class="paid">{$LANG.invoicespaid}</font><br />
{$paymentmethod}<br />
({$datepaid})
{elseif $status eq "Refunded"}
<font class="refunded">{$LANG.invoicesrefunded}</font>
{elseif $status eq "Cancelled"}
<font class="cancelled">{$LANG.invoicescancelled}</font>
{elseif $status eq "Collections"}
<font class="collections">{$LANG.invoicescollections}</font>
{/if}

</td></tr></table>

{if $smarty.get.paymentsuccess}
<p align="center" class="paid">{$LANG.invoicepaymentsuccessconfirmation}</p>
{elseif $smarty.get.pendingreview}
<p align="center" class="paid">{$LANG.invoicepaymentpendingreview}</p>
{elseif $smarty.get.paymentfailed}
<p align="center" class="unpaid">{$LANG.invoicepaymentfailedconfirmation}</p>
{elseif $offlinepaid}
<p align="center" class="refunded">{$LANG.invoiceofflinepaid}</p>
{else}
<br />
{/if}

{if $manualapplycredit}
<div class="creditbox">{$LANG.invoiceaddcreditdesc1} {$totalcredit}. {$LANG.invoiceaddcreditdesc2}<br />
<form method="post" action="{$smarty.server.PHP_SELF}?id={$invoiceid}"><input type="hidden" name="applycredit" value="true" />
{$LANG.invoiceaddcreditamount}: <input type="text" name="creditamount" size="10" value="{$creditamount}" /> <input type="submit" value="{$LANG.invoiceaddcreditapply}" />
</form></div>
<br />
{/if}

<table width="100%" id="invoicetoptables" cellspacing="0"><tr><td width="50%" id="invoicecontent" style="border:1px solid #cccccc">

<table width="100%" height="120" cellspacing="0" cellpadding="10" id="invoicetoptables"><tr><td id="invoicecontent" valign="top" style="border:1px solid #cccccc">

<strong>{$LANG.invoicesinvoicedto}</strong><br />
{if $clientsdetails.companyname}{$clientsdetails.companyname}<br />{/if}
{$clientsdetails.firstname} {$clientsdetails.lastname}<br />
{$clientsdetails.address1}, {$clientsdetails.address2}<br />
{$clientsdetails.city}, {$clientsdetails.state}, {$clientsdetails.postcode}<br />
{$clientsdetails.country}
{if $customfields}
<br /><br />
{foreach from=$customfields item=customfield}
{$customfield.fieldname}: {$customfield.value}<br />
{/foreach}
{/if}

</td></tr></table>

</td><td width="50%" id="invoicecontent" style="border:1px solid #cccccc;border-left:0px;">

<table width="100%" height="120" cellspacing="0" cellpadding="10" id="invoicetoptables"><tr><td id="invoicecontent" valign="top" style="border:1px solid #cccccc">

<strong>{$LANG.invoicespayto}</strong><br />
{$payto}

</td></tr></table>

</td></tr></table>

<p><strong>{* This code should be uncommented for EU companies using the sequential invoice numbering so that when unpaid it is shown as a proforma invoice {if $status eq "Paid"}*}{$LANG.invoicenumber}{*{else}{$LANG.proformainvoicenumber}{/if}*}{$invoicenum}</strong><br />
{$LANG.invoicesdatecreated}: {$datecreated}<br />
{$LANG.invoicesdatedue}: {$datedue}</p>

<table cellspacing="0" id="invoiceitemstable" align="center"><tr><td id="invoiceitemsheading" align="center" width="70%" style="border:1px solid #cccccc;border-bottom:0px;"><strong>{$LANG.invoicesdescription}</strong></td><td id="invoiceitemsheading" align="center" width="30%" style="border:1px solid #cccccc;border-left:0px;border-bottom:0px;"><strong>{$LANG.invoicesamount}</strong></td></tr>
{foreach key=num item=invoiceitem from=$invoiceitems}
<tr bgcolor=#ffffff><td id="invoiceitemsrow" style="border:1px solid #cccccc;border-bottom:0px;">{$invoiceitem.description}{if $invoiceitem.taxed eq "true"} *{/if}</td><td align="center" id="invoiceitemsrow" style="border:1px solid #cccccc;border-bottom:0px;border-left:0px;">{$invoiceitem.amount}</td></tr>
{/foreach}
<tr><td id="invoiceitemsheading" style="border:1px solid #cccccc;border-bottom:0px;"><div align="right">{$LANG.invoicessubtotal}:&nbsp;</div></td><td id="invoiceitemsheading" align="center" style="border:1px solid #cccccc;border-bottom:0px;border-left:0px;"><strong>{$subtotal}</strong></td></tr>
{if $taxrate}<tr><td id="invoiceitemsheading" style="border:1px solid #cccccc;border-bottom:0px;"><div align="right">{$taxrate}% {$taxname}:&nbsp;</div></td><td id="invoiceitemsheading" align="center" style="border:1px solid #cccccc;border-bottom:0px;border-left:0px;"><strong>{$tax}</strong></td></tr>{/if}
{if $taxrate2}<tr><td id="invoiceitemsheading" style="border:1px solid #cccccc;border-bottom:0px;"><div align="right">{$taxrate2}% {$taxname2}:&nbsp;</div></td><td id="invoiceitemsheading" align="center" style="border:1px solid #cccccc;border-bottom:0px;border-left:0px;"><strong>{$tax2}</strong></td></tr>{/if}
<tr><td id="invoiceitemsheading" style="border:1px solid #cccccc;border-bottom:0px;"><div align="right">{$LANG.invoicescredit}:&nbsp;</div></td><td id="invoiceitemsheading" align="center" style="border:1px solid #cccccc;border-bottom:0px;border-left:0px;"><strong>{$credit}</strong></td></tr>
<tr><td id="invoiceitemsheading" style="border:1px solid #cccccc;"><div align="right">{$LANG.invoicestotal}:&nbsp;</div></td><td id="invoiceitemsheading" align="center" style="border:1px solid #cccccc;border-left:0px;"><strong>{$total}</strong></td></tr>
</table>

{if $taxrate}<p>* {$LANG.invoicestaxindicator}</p>{/if}

<p><strong>{$LANG.invoicestransactions}</strong></p>

<table cellspacing="0" id="invoiceitemstable" align="center"><tr><td id="invoiceitemsheading" align="center" width="30%" style="border:1px solid #cccccc"><strong>{$LANG.invoicestransdate}</strong></td><td id="invoiceitemsheading" align="center" width="25%" style="border:1px solid #cccccc;border-left:0px;"><strong>{$LANG.invoicestransgateway}</strong></td><td id="invoiceitemsheading" align="center" width="25%" style="border:1px solid #cccccc;border-left:0px;"><strong>{$LANG.invoicestransid}</strong></td><td id="invoiceitemsheading" align="center" width="20%" style="border:1px solid #cccccc;border-left:0px;"><strong>{$LANG.invoicestransamount}</strong></td></tr>
{foreach key=num item=transaction from=$transactions}
<tr bgcolor=#ffffff><td id="invoiceitemsrow" align="center" style="border:1px solid #cccccc;border-top:0px;">{$transaction.date}</td><td align="center" id="invoiceitemsrow" style="border:1px solid #cccccc;border-left:0px;border-top:0px;">{$transaction.gateway}</td><td align="center" id="invoiceitemsrow" style="border:1px solid #cccccc;border-left:0px;border-top:0px;">{$transaction.transid}</td><td align="center" id="invoiceitemsrow" style="border:1px solid #cccccc;border-left:0px;border-top:0px;">{$transaction.amount}</td></tr>
{foreachelse}
<tr bgcolor=#ffffff><td id="invoiceitemsrow" colspan=4 align="center" style="border:1px solid #cccccc;border-top:0px;">{$LANG.invoicestransnonefound}</td></tr>
{/foreach}
<tr><td id="invoiceitemsheading" width="30%" style="border:1px solid #cccccc;border-top:0px;" colspan=3><DIV ALIGN="right"><strong>{$LANG.invoicesbalance}:&nbsp;</strong></DIV></td><td id="invoiceitemsheading" align="center" width="20%" style="border:1px solid #cccccc;border-left:0px;border-top:0px;"><strong>{$balance}</strong></td></tr>
</table>

{if $notes}
<p>{$LANG.invoicesnotes}: {$notes}</p>
{/if}

<br /><br /><br /><br /><br />

</td></tr></table>

{/if}

<p align="center"><a href="clientarea.php">{$LANG.invoicesbacktoclientarea}</a> | <a href="dl.php?type=i&amp;id={$invoiceid}">{$LANG.invoicesdownload}</a> | <a href="javascript:window.close()">{$LANG.closewindow}</a></p>

</body>
</html>