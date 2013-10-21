<script type="text/javascript" src="includes/jscript/jqueryui.js"></script>
<link rel="stylesheet" type="text/css" href="includes/jscript/css/ui.all.css" />

{literal}
<script>
	$(function() {
		$( "button, input:submit" ).button();
	});
</script>
{/literal}

<div class="homequicklinks">
    <div class="title">Quick Links</div>
    <div class="cols">
        <ul>
            <li><a href="cart.php"><img src="images/order.gif" border="0" hspace="5" align="absmiddle" alt="" />Place New Order</a></li>
            <li><a href="clientarea.php?action=products"><img src="images/products.gif" border="0" hspace="5" align="absmiddle" alt="" />My Services ({$clientsstats.productsnumactive})</a></li>
            <li><a href="clientarea.php?action=domains"><img src="images/domains.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.clientareanavdomains} ({$clientsstats.numactivedomains})</a></li>
            <li><a href="clientarea.php?action=quotes"><img src="images/pdf.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.quotestitle} ({$clientsstats.numquotes})</a></li>
        </ul>
    </div>
    <div class="cols">
        <ul>
            <li><a href="clientarea.php?action=invoices"><img src="images/invoices.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.invoices} ({$clientsstats.numdueinvoices})</a></li>
            <li><a href="supporttickets.php"><img src="images/supporttickets.gif" border="0" hspace="5" align="absmiddle" alt="" />My Tickets ({$clientsstats.numtickets})</a></li>
            <li><a href="affiliates.php"><img src="images/affiliates.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.affiliatestitle} ({$clientsstats.numaffiliatesignups})</a></li>
            <li><a href="clientarea.php?action=emails"><img src="images/emails.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.clientareaemails}</a></li>
        </ul>
    </div>
    (Number in brackets indicates number of active items)
</div>

<div class="homeyourdetails">
    <div class="title">Your Details</div>
    <div class="update"><a href="clientarea.php?action=details"><img src="images/details.gif" border="0" hspace="5" align="absmiddle" alt="" />{$LANG.clientareaupdateyourdetails}</a><br />Please ensure your details are<br />always kept up to date</div>
    <div class="details">
    <strong>{$clientsdetails.firstname} {$clientsdetails.lastname} {if $clientsdetails.companyname}({$clientsdetails.companyname}){/if}</strong><br />
    {$clientsdetails.address1}{if $clientsdetails.address2}, {$clientsdetails.address2}{/if}<br />
    {if $clientsdetails.city}{$clientsdetails.city}, {/if}{if $clientsdetails.state}{$clientsdetails.state}, {/if}{$clientsdetails.postcode}<br />
    {$clientsdetails.countryname}<br />
    <a href="mailto:{$clientsdetails.email}">{$clientsdetails.email}</a>
    </div>
</div>

<div class="clear"></div>

<div class="duebalance{if $clientsstats.numdueinvoices>0} overdue{/if}">Current Due Balance: {$clientsstats.dueinvoicesbalance}</div>
<div class="creditbalance{if $clientsstats.numdueinvoices>0} overdue{/if}">{$LANG.statscreditbalance}: {$clientsstats.creditbalance}</div>
<div class="balanceoptions">
{if $addfundsenabled}<button onclick="window.location='clientarea.php?action=addfunds'"><img src="images/affiliates.gif" border="0" align="absmiddle" alt="" /> {$LANG.addfunds}</button>{/if} &nbsp; {if $masspay && $clientsstats.numdueinvoices>0}<button onclick="window.location='clientarea.php?action=masspay&all=true'"><img src="images/invoices.gif" border="0" align="absmiddle" alt="" /> {$LANG.masspayall}</button>{/if}
</div>

<div class="quickjumpbar">
<div class="col1">Quick Jump</div>

<div class="cols"><form method="post" action="clientarea.php?action=products">My Services: <input type="text" name="q" value="{$LANG.searchenterdomain}" class="searchinput" onfocus="if(this.value=='{$LANG.searchenterdomain}')this.value=''" /> <input type="submit" value="{$LANG.go}" class="searchinput" /></form></div>

<div class="cols"><form method="post" action="clientarea.php?action=domains">My Domains: <input type="text" name="q" value="{$LANG.searchenterdomain}" class="searchinput" onfocus="if(this.value=='{$LANG.searchenterdomain}')this.value=''" /> <input type="submit" value="{$LANG.go}" class="searchinput" /></form></div>

<div class="clear"></div>

</div>

{foreach from=$addons_html item=addon_html}
<div style="margin:15px 0 15px 0;">{$addon_html}</div>
{/foreach}

{if in_array('tickets',$contactpermissions)}

<p class="heading2"><img src="images/supporttickets.gif" border="0" hspace="5" align="absmiddle" alt="" />{$clientsstats.numactivetickets} {$LANG.supportticketsopentickets} (<a href="submitticket.php">{$LANG.supportticketssubmitticket}</a>)</p>

<table align="center" style="width:90%" class="clientareatable" cellspacing="1">
<tr class="clientareatableheading"><td>{$LANG.supportticketsdate}</td><td>{$LANG.supportticketssubject}</td><td>{$LANG.supportticketsstatus}</td><td>{$LANG.supportticketsticketurgency}</td></tr>
{foreach key=num item=ticket from=$tickets}
<tr><td>{$ticket.date}</td><td><div align="left"><img src="images/article.gif" hspace="5" align="middle" alt="" /><a href="viewticket.php?tid={$ticket.tid}&amp;c={$ticket.c}">{if $ticket.unread}<strong>{/if}#{$ticket.tid} - {$ticket.subject}{if $ticket.unread}</strong>{/if}</a></div></td><td width="120">{$ticket.status}</td><td width="80">{$ticket.urgency}</td></tr>
{foreachelse}
<tr class="clientareatableactive"><td colspan="4">{$LANG.norecordsfound}</td></tr>
{/foreach}
</table>

{/if}

{if in_array('invoices',$contactpermissions)}

<p class="heading2"><img src="images/invoices.gif" border="0" hspace="5" align="absmiddle" alt="" />{$clientsstats.numdueinvoices} {$LANG.invoicesdue}</p>

<form method="post" action="clientarea.php?action=masspay">

<table align="center" style="width:90%" class="clientareatable" cellspacing="1">
<tr class="clientareatableheading">{if $masspay}<td width="15"></td>{/if}<td>{$LANG.invoicenumber}</td><td>{$LANG.invoicesdatecreated}</td><td>{$LANG.invoicesdatedue}</td><td>{$LANG.invoicestotal}</td><td>{$LANG.invoicesbalance}</td><td>{$LANG.invoicesstatus}</td><td></td></tr>
{foreach key=num item=invoice from=$invoices}
<tr>{if $masspay}<td><input type="checkbox" name="invoiceids[]" value="{$invoice.id}" /></td>{/if}<td><a href="viewinvoice.php?id={$invoice.id}" target="_blank">{$invoice.invoicenum}</a></td><td>{$invoice.datecreated}</td><td>{$invoice.datedue}</td><td>{$invoice.total}</td><td>{$invoice.balance}</td><td>{$invoice.status}</td><td><a href="viewinvoice.php?id={$invoice.id}" target="_blank">{$LANG.invoicesview}</a></td></tr>
{foreachelse}
<tr class="clientareatableactive"><td colspan="{if $masspay}8{else}7{/if}">{$LANG.norecordsfound}</td></tr>
{/foreach}
{if $invoices}<tr class="clientareatableheading"><td colspan="{if $masspay}4{else}3{/if}">{if $masspay}<input type="submit" value="{$LANG.masspayselected}" class="buttongo" />{/if}</td><td>{$LANG.invoicestotaldue}</td><td>{$totalbalance}</td><td colspan="2">{if $masspay}<a href="clientarea.php?action=masspay&all=true">{$LANG.masspayall}</a>{/if}</td></tr>{/if}
</table>

</form>

{/if}

{if $files}

<p class="heading2"><img src="images/file.png" border="0" hspace="5" align="absmiddle" alt="" /> {$LANG.clientareafiles}</p>

<table align="center" style="width:90%" class="clientareatable" cellspacing="1">
<tr class="clientareatableheading"><td>{$LANG.clientareafilesdate}</td><td>{$LANG.clientareafilesfilename}</td></tr>
{foreach key=num item=file from=$files}
<tr class="clientareatableactive"><td>{$file.date}</td><td align="left"><img src="images/file.png" hspace="5" align="middle" alt="" /> <a href="dl.php?type=f&id={$file.id}"><strong>{$file.title}</strong></a></td></tr>
{/foreach}
</table>

{/if}

{if $twitterusername}
<p class="heading2"><img src="images/twittericon.png" border="0" hspace="5" align="absmiddle" alt="" /> {$LANG.twitterlatesttweets}</p>
<div id="twitterfeed">
  <p><img src="images/loading.gif"></p>
</div>
{literal}<script language="javascript">
jQuery(document).ready(function(){
  jQuery.post("announcements.php", { action: "twitterfeed", numtweets: 3, usessl: 1 },
    function(data){
      jQuery("#twitterfeed").html(data);
    });
});
</script>{/literal}
{elseif $announcements}
<p class="heading2">{$LANG.latestannouncements}</p>
{foreach from=$announcements item=announcement}
<p>{$announcement.date} - <a href="{if $seofriendlyurls}announcements/{$announcement.id}/{$announcement.urlfriendlytitle}.html{else}announcements.php?id={$announcement.id}{/if}"><b>{$announcement.title}</b></a><br />{$announcement.text|strip_tags|truncate:100:"..."}</p>
{/foreach}
{/if}