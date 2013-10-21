<div style="font-size:18px;">#{$clientsdetails.userid} - {$clientsdetails.firstname} {$clientsdetails.lastname}</div>
<img src="images/spacer.gif" width="1" height="6" /><br />

{if $notes}
<div id="clientsimportantnotes">
{foreach from=$notes item=note}
<div class="ticketstaffnotes">
    <table class="ticketstaffnotestable">
        <tr>
            <td>{$note.adminuser}</td>
            <td align="right">{$note.modified}</td>
        </tr>
    </table>
    <div>
        {$note.note}
        <div style="float:right;"><a href="clientsnotes.php?userid={$clientsdetails.userid}&action=edit&id={$note.id}"><img src="images/edit.gif" width="16" height="16" align="absmiddle" /></a></div>
    </div>
</div>
{/foreach}
</div>
{/if}

{foreach from=$addons_html item=addon_html}
<div style="margin-top:6px;margin-bottom:9px;">{$addon_html}</div>
{/foreach}

<table width="100%">
<tr><td width="34%" valign="top">

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.clientsummary.infoheading}</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
<tr><td width="110">{$_ADMINLANG.fields.firstname}</td><td>{$clientsdetails.firstname}</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.fields.lastname}</td><td>{$clientsdetails.lastname}</td></tr>
<tr><td>{$_ADMINLANG.fields.companyname}</td><td>{$clientsdetails.companyname}</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.fields.email}</td><td>{$clientsdetails.email}</td></tr>
<tr><td>{$_ADMINLANG.fields.address1}</td><td>{$clientsdetails.address1}</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.fields.address2}</td><td>{$clientsdetails.address2}</td></tr>
<tr><td>{$_ADMINLANG.fields.city}</td><td>{$clientsdetails.city}</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.fields.state}</td><td>{$clientsdetails.state}</td></tr>
<tr><td>{$_ADMINLANG.fields.postcode}</td><td>{$clientsdetails.postcode}</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.fields.country}</td><td>{$clientsdetails.country} - {$clientsdetails.countrylong}</td></tr>
<tr><td>{$_ADMINLANG.fields.phonenumber}</td><td>{$clientsdetails.phonenumber}</td></tr>
</table>
<ul>
<li><a href="clientssummary.php?userid={$clientsdetails.userid}&resetpw=true{$tokenvar}"><img src="images/icons/resetpw.png" border="0" align="absmiddle" /> {$_ADMINLANG.clients.resetsendpassword}</a>
<li><a href="#" onClick="openCCDetails();return false"><img src="images/icons/offlinecc.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.ccinfo}</a>
<li><a href="../dologin.php?username={$clientsdetails.email|urlencode}"><img src="images/icons/clientlogin.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.loginasclient}</a>
</ul>
</div>

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.clientsummary.contactsheading}</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
{foreach key=num from=$contacts item=contact}
<tr class="{cycle values=",altrow"}"><td align="center"><a href="clientscontacts.php?userid={$clientsdetails.userid}&contactid={$contact.id}">{$contact.firstname} {$contact.lastname}</a> - {$contact.email}</td></tr>
{foreachelse}
<tr><td align="center">{$_ADMINLANG.clientsummary.nocontacts}</td></tr>
{/foreach}
</table>
<ul>
<li><a href="clientscontacts.php?userid={$clientsdetails.userid}&contactid=addnew"><img src="images/icons/clientsadd.png" border="0" align="absmiddle" /> {$_ADMINLANG.clients.addcontact}</a>
</ul>
</div>

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.clientsummary.otherinfoheading}</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
<tr><td width="110">{$_ADMINLANG.fields.status}</td><td>{$clientsdetails.status}</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.fields.clientgroup}</td><td>{$clientgroup.name}</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.fields.signupdate}</td><td>{$signupdate}</td></tr>
<tr><td>{$_ADMINLANG.clientsummary.clientfor}</td><td>{$clientfor}</td></tr>
<tr class="altrow"><td width="110">{$_ADMINLANG.clientsummary.lastlogin}</td><td>{$lastlogin}</td></tr>
</table>
</div>

</td><td width="33%" valign="top">

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.clientsummary.billingheading}</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
<tr><td width="110">{$_ADMINLANG.status.paid}</td><td>{$stats.numpaidinvoices} ({$stats.paidinvoicesamount})</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.status.unpaid}/{$_ADMINLANG.status.due}</td><td>{$stats.numdueinvoices} ({$stats.dueinvoicesbalance})</td></tr>
<tr><td>{$_ADMINLANG.status.cancelled}</td><td>{$stats.numcancelledinvoices} ({$stats.cancelledinvoicesamount})</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.status.refunded}</td><td>{$stats.numrefundedinvoices} ({$stats.refundedinvoicesamount})</td></tr>
<tr><td>{$_ADMINLANG.status.collections}</td><td>{$stats.numcollectionsinvoices} ({$stats.collectionsinvoicesamount})</td></tr>
<tr class="altrow"><td><strong>{$_ADMINLANG.billing.income}</strong></td><td><strong>{$stats.income}</strong></td></tr>
<tr><td>{$_ADMINLANG.clients.creditbalance}</td><td>{$stats.creditbalance}</td></tr>
</table>
<ul>
<li><a href="invoices.php?action=createinvoice&userid={$clientsdetails.userid}"><img src="images/icons/invoicesedit.png" border="0" align="absmiddle" /> {$_ADMINLANG.invoices.create}</a>
<li><a href="#" onClick="showDialog('addfunds');return false"><img src="images/icons/addfunds.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.createaddfunds}</a>
<li><a href="#" onClick="showDialog('geninvoices');return false"><img src="images/icons/ticketspredefined.png" border="0" align="absmiddle" /> {$_ADMINLANG.invoices.geninvoices}</a>
<li><a href="clientsbillableitems.php?userid={$clientsdetails.userid}&action=manage"><img src="images/icons/billableitems.png" border="0" align="absmiddle" /> {$_ADMINLANG.billableitems.additem}</a>
<li><a href="#" onClick="window.open('clientscredits.php?userid={$clientsdetails.userid}','','width=750,height=350,scrollbars=yes');return false"><img src="images/icons/income.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.managecredits}</a>
<li><a href="quotes.php?action=manage&userid={$clientsdetails.userid}"><img src="images/icons/quotes.png" border="0" align="absmiddle" /> {$_ADMINLANG.quotes.createnew}</a>
</ul>
</div>

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.clientsummary.filesheading}</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
{foreach key=num from=$files item=file}
<tr class="{cycle values=",altrow"}"><td align="center"><a href="../dl.php?type=f&id={$file.id}"><img src="../images/file.png" align="absmiddle" vspace="1" border="0" /> {$file.title}</a> {if $file.adminonly}({$_ADMINLANG.clientsummary.fileadminonly}){/if} <img src="images/icons/delete.png" align="absmiddle" border="0" onClick="deleteFile('{$file.id}')" /></td></tr>
{foreachelse}
<tr><td align="center">{$_ADMINLANG.clientsummary.nofiles}</td></tr>
{/foreach}
</table>
<ul>
<li><a href="#" id="addfile"><img src="images/icons/add.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.fileadd}</a>
</ul>
<div id="addfileform" style="display:none;">
<img src="images/spacer.gif" width="1" height="4" /><br />
<form method="post" action="clientssummary.php?userid={$clientsdetails.userid}&action=uploadfile" enctype="multipart/form-data">
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
<tr><td width="40">{$_ADMINLANG.clientsummary.filetitle}</td><td class="fieldarea"><input type="text" name="title" style="width:90%" /></td></tr>
<tr><td>{$_ADMINLANG.clientsummary.filename}</td><td class="fieldarea"><input type="file" name="uploadfile" style="width:90%" /></td></tr>
<tr><td></td><td class="fieldarea"><input type="checkbox" name="adminonly" value="1" /> {$_ADMINLANG.clientsummary.fileadminonly} &nbsp;&nbsp;&nbsp;&nbsp; <input type="submit" value="{$_ADMINLANG.global.submit}" /></td></tr>
</table>
</form>
</div>
</div>

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.clientsummary.settingsheading}</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
<tr><td width="140">{$_ADMINLANG.clientsummary.settingtaxexempt}</td><td><span id="taxstatus"><strong class="{if $clientsdetails.taxstatus == "Yes"}textgreen{else}textred{/if}">{$clientsdetails.taxstatus}</strong></span> - <span id="taxstatus" class="csajaxtoggle" style="text-decoration:underline;cursor:pointer">{$_ADMINLANG.clientsummary.settingtoggle}</span></td></tr>
<tr class="altrow"><td>{$_ADMINLANG.clientsummary.settingautocc}</td><td><span id="autocc"><strong class="{if $clientsdetails.autocc == "Yes"}textgreen{else}textred{/if}">{$clientsdetails.autocc}</strong></span> - <span id="autocc" class="csajaxtoggle" style="text-decoration:underline;cursor:pointer">{$_ADMINLANG.clientsummary.settingtoggle}</span></td></tr>
<tr><td>{$_ADMINLANG.clientsummary.settingreminders}</td><td><span id="overduenotices"><strong class="{if $clientsdetails.overduenotices == "Yes"}textgreen{else}textred{/if}">{$clientsdetails.overduenotices}</strong></span> - <span id="overduenotices" class="csajaxtoggle" style="text-decoration:underline;cursor:pointer">{$_ADMINLANG.clientsummary.settingtoggle}</span></td></tr>
<tr class="altrow"><td>{$_ADMINLANG.clientsummary.settinglatefees}</td><td><span id="latefees"><strong class="{if $clientsdetails.latefees == "Yes"}textgreen{else}textred{/if}">{$clientsdetails.latefees}</strong></span> - <span id="latefees" class="csajaxtoggle" style="text-decoration:underline;cursor:pointer">{$_ADMINLANG.clientsummary.settingtoggle}</span></td></tr>
</table>
</div>

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.clientsummary.emailsheading}</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
{foreach key=num from=$lastfivemail item=email}
<tr class="{cycle values=",altrow"}"><td align="center">{$email.date} - <a href="#" onClick="window.open('clientsemails.php?&displaymessage=true&id={$email.id}','','width=650,height=400,scrollbars=yes');return false">{$email.subject}</a></td></tr>
{foreachelse}
<tr><td align="center">{$_ADMINLANG.clientsummary.noemails}</td></tr>
{/foreach}
</table>
</div>

</td><td width="33%" valign="top">

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.services.title}</div>
<table class="clientssummarystats" cellspacing="0" cellpadding="2">
<tr><td width="140">{$_ADMINLANG.orders.sharedhosting}</td><td>{$stats.productsnumactivehosting} ({$stats.productsnumhosting} Total)</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.orders.resellerhosting}</td><td>{$stats.productsnumactivereseller} ({$stats.productsnumreseller} Total)</td></tr>
<tr><td>{$_ADMINLANG.orders.server}</td><td>{$stats.productsnumactiveservers} ({$stats.productsnumservers} Total)</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.orders.other}</td><td>{$stats.productsnumactiveother} ({$stats.productsnumother} Total)</td></tr>
<tr><td>{$_ADMINLANG.domains.title}</td><td>{$stats.numactivedomains} ({$stats.numdomains} Total)</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.stats.acceptedquotes}</td><td>{$stats.numacceptedquotes} ({$stats.numquotes} Total)</td></tr>
<tr><td>{$_ADMINLANG.support.supporttickets}</td><td>{$stats.numactivetickets} ({$stats.numtickets} Total)</td></tr>
<tr class="altrow"><td>{$_ADMINLANG.stats.affiliatesignups}</td><td>{$stats.numaffiliatesignups}</td></tr>
</table>
<ul>
<li><a href="orders.php?clientid={$clientsdetails.userid}"><img src="images/icons/orders.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.vieworders}</a>
<li><a href="ordersadd.php?userid={$clientsdetails.userid}"><img src="images/icons/ordersadd.png" border="0" align="absmiddle" /> {$_ADMINLANG.orders.addnew}</a>
</ul>
</div>

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.clientsummary.sendemailheading}</div>
<form action="clientsemails.php?userid={$clientsdetails.userid}&action=send&type=general" method="post">
<input type="hidden" name="id" value="{$clientsdetails.userid}">
<div align="center">{$messages} <input type="submit" value="{$_ADMINLANG.global.go}" class="button"></div>
</form>
</div>

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.clientsummary.actionsheading}</div>
<ul>
{foreach from=$customactionlinks item=customactionlink}
<li>{$customactionlink}</li>
{/foreach}
<li><a href="reports.php?report=client_statement&userid={$clientsdetails.userid}"><img src="images/icons/reports.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.accountstatement}</a>
<li><a href="supporttickets.php?action=open&userid={$clientsdetails.userid}"><img src="images/icons/ticketsopen.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.newticket}</a>
<li><a href="supporttickets.php?view=any&client={$clientsdetails.userid}"><img src="images/icons/ticketsother.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.viewtickets}</a>
<li><a href="{if $affiliateid}affiliates.php?action=edit&id={$affiliateid}{else}clientssummary.php?userid={$clientsdetails.userid}&activateaffiliate=true{$tokenvar}{/if}"><img src="images/icons/affiliates.png" border="0" align="absmiddle" /> {if $affiliateid}{$_ADMINLANG.clientsummary.viewaffiliate}{else}{$_ADMINLANG.clientsummary.activateaffiliate}{/if}</a>
<li><a href="#" onClick="window.open('clientsmerge.php?userid={$clientsdetails.userid}','movewindow','width=500,height=280,top=100,left=100,scrollbars=1');return false"><img src="images/icons/clients.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.mergeclients}</a>
<li><a href="#" onClick="closeClient();return false" style="color:#000000;"><img src="images/icons/delete.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.closeclient}</a>
<li><a href="#" onClick="deleteClient();return false" style="color:#CC0000;"><img src="images/icons/delete.png" border="0" align="absmiddle" /> {$_ADMINLANG.clientsummary.deleteclient}</a>
</ul>
</div>

<div class="clientssummarybox">
<div class="title">{$_ADMINLANG.fields.adminnotes}</div>
<form method="post" action="{$smarty.server.PHP_SELF}?userid={$clientsdetails.userid}&action=savenotes">
<div align="center">
<textarea name="adminnotes" rows="6" style="width:90%;" />{$clientsdetails.notes}</textarea><br />
<input type="submit" value="{$_ADMINLANG.global.submit}" class="button" />
</div>
</form>
</div>

</td></tr>
<tr><td colspan="3">

<form method="post" action="{$smarty.server.PHP_SELF}?userid={$clientsdetails.userid}&action=massaction">

{literal}<script language="javascript">
$(document).ready(function(){
    $("#prodsall").click(function () {
        $(".checkprods").attr("checked",this.checked);
    });
    $("#addonsall").click(function () {
        $(".checkaddons").attr("checked",this.checked);
    });
    $("#domainsall").click(function () {
        $(".checkdomains").attr("checked",this.checked);
    });
});
</script>{/literal}

<table width="100%" class="form">
<tr><td colspan="2" class="fieldarea" style="text-align:center;"><strong>{$_ADMINLANG.services.title}</strong></td></tr>
<tr><td align="center">

<div class="tablebg">
<table class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
<tr><th width="20"><input type="checkbox" id="prodsall" /></th><th>{$_ADMINLANG.fields.id}</th><th>{$_ADMINLANG.fields.product}</th><th>{$_ADMINLANG.fields.amount}</th><th>{$_ADMINLANG.fields.billingcycle}</th><th>{$_ADMINLANG.fields.signupdate}</th><th>{$_ADMINLANG.fields.nextduedate}</th><th>{$_ADMINLANG.fields.status}</th><th width="20"></th></tr>
{foreach key=num from=$productsummary item=product}
<tr><td><input type="checkbox" name="selproducts[]" value="{$product.id}" class="checkprods" /></td><td><a href="clientsservices.php?userid={$clientsdetails.userid}&id={$product.id}">{$product.idshort}</a></td><td style="padding-left:5px;padding-right:5px">{$product.dpackage} - <a href="http://{$product.domain}" target="_blank">{$product.domain}</a></td><td>{$product.amount}</td><td>{$product.dbillingcycle}</td><td>{$product.regdate}</td><td>{$product.nextduedate}</td><td>{$product.domainstatus}</td><td><a href="clientsservices.php?userid={$clientsdetails.userid}&id={$product.id}"><img src="images/edit.gif" width="16" height="16" border="0" alt="Edit"></a></td></tr>
{foreachelse}
<tr><td colspan="9">{$_ADMINLANG.global.norecordsfound}</td></tr>
{/foreach}
</table>
</div>

</td></tr></table>

<img src="images/spacer.gif" width="1" height="4" /><br />

<table width="100%" class="form">
<tr><td colspan="2" class="fieldarea" style="text-align:center;"><strong>{$_ADMINLANG.addons.title}</strong></td></tr>
<tr><td align="center">

<div class="tablebg">
<table class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
<tr><th width="20"><input type="checkbox" id="addonsall" /></th><th>ID</th><th>{$_ADMINLANG.addons.name}</th><th>{$_ADMINLANG.fields.amount}</th><th>{$_ADMINLANG.fields.billingcycle}</th><th>{$_ADMINLANG.fields.signupdate}</th><th>{$_ADMINLANG.fields.nextduedate}</th><th>{$_ADMINLANG.fields.status}</th><th width="20"></th></tr>
{foreach key=num from=$addonsummary item=addon}
<tr><td><input type="checkbox" name="seladdons[]" value="{$addon.id}" class="checkaddons" /></td><td><a href="clientsservices.php?userid={$clientsdetails.userid}&id={$addon.serviceid}&aid={$addon.id}">{$addon.idshort}</a></td><td style="padding-left:5px;padding-right:5px">{$addon.addonname}<br>{$addon.dpackage} - <a href="http://{$addon.domain}" target="_blank">{$addon.domain}</a></td><td>{$addon.amount}</td><td>{$addon.dbillingcycle}</td><td>{$addon.regdate}</td><td>{$addon.nextduedate}</td><td>{$addon.status}</td><td><a href="clientsservices.php?userid={$clientsdetails.userid}&id={$addon.serviceid}&aid={$addon.id}"><img src="images/edit.gif" width="16" height="16" border="0" alt="Edit"></a></td></tr>
{foreachelse}
<tr><td colspan="9">{$_ADMINLANG.global.norecordsfound}</td></tr>
{/foreach}
</table>
</div>

</td></tr></table>

<img src="images/spacer.gif" width="1" height="4" /><br />

<table width="100%" class="form">
<tr><td colspan="2" class="fieldarea" style="text-align:center;"><strong>{$_ADMINLANG.domains.title}</strong></td></tr>
<tr><td align="center">

<div class="tablebg">
<table class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
<tr><th width="20"><input type="checkbox" id="domainsall" /></th><th>{$_ADMINLANG.fields.id}</th><th>{$_ADMINLANG.fields.domain}</th><th>{$_ADMINLANG.fields.registrar}</th><th>{$_ADMINLANG.fields.regdate}</th><th>{$_ADMINLANG.fields.nextduedate}</th><th>{$_ADMINLANG.fields.expirydate}</th><th>{$_ADMINLANG.fields.status}</th><th width="20"></th></tr>
{foreach key=num from=$domainsummary item=domain}
<tr><td><input type="checkbox" name="seldomains[]" value="{$domain.id}" class="checkdomains" /></td><td><a href="clientsdomains.php?userid={$clientsdetails.userid}&domainid={$domain.id}">{$domain.idshort}</a></td><td style="padding-left:5px;padding-right:5px"><a href="http://{$domain.domain}" target="_blank">{$domain.domain}</a></td><td>{$domain.registrar}</td><td>{$domain.registrationdate}</td><td>{$domain.nextduedate}</td><td>{$domain.expirydate}</td><td>{$domain.status}</td><td><a href="clientsdomains.php?userid={$clientsdetails.userid}&domainid={$domain.id}"><img src="images/edit.gif" width="16" height="16" border="0" alt="Edit"></a></td></tr>
{foreachelse}
<tr><td colspan="9">{$_ADMINLANG.global.norecordsfound}</td></tr>
{/foreach}
</table>
</div>

</td></tr></table>

<img src="images/spacer.gif" width="1" height="4" /><br />

<table width="100%" class="form">
<tr><td colspan="2" class="fieldarea" style="text-align:center;"><strong>{$_ADMINLANG.clientsummary.currentquotes}</strong></td></tr>
<tr><td align="center">

<div class="tablebg">
<table class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
<tr><th>{$_ADMINLANG.fields.id}</th><th>{$_ADMINLANG.fields.subject}</th><th>{$_ADMINLANG.fields.date}</th><th>{$_ADMINLANG.fields.total}</th><th>{$_ADMINLANG.fields.validuntil}</th><th>{$_ADMINLANG.fields.status}</th><th width="20"></th></tr>
{foreach key=num from=$quotes item=quote}
<tr><td>{$quote.id}</td><td style="padding-left:5px;padding-right:5px">{$quote.subject}</td><td>{$quote.datecreated}</td><td>{$quote.total}</td><td>{$quote.validuntil}</td><td>{$quote.stage}</td><td><a href="quotes.php?action=manage&id={$quote.id}"><img src="images/edit.gif" width="16" height="16" border="0" alt="Edit"></a></td></tr>
{foreachelse}
<tr><td colspan="7">{$_ADMINLANG.global.norecordsfound}</td></tr>
{/foreach}
</table>
</div>

</td></tr></table>

<p align="center"><input type="button" value="{$_ADMINLANG.clientsummary.massupdateitems}" class="button" onclick="$('#massupdatebox').slideToggle()" /> <input type="submit" name="inv" value="{$_ADMINLANG.clientsummary.invoiceselected}" class="button" /> <input type="submit" name="del" value="{$_ADMINLANG.clientsummary.deleteselected}" class="button" /></p>

<div id="massupdatebox" style="width:75%;background-color:#f7f7f7;border:1px dashed #cccccc;padding:10px;margin-left:auto;margin-right:auto;display:none;">
<h2 style="text-align:center;margin:0 0 10px 0">{$_ADMINLANG.clientsummary.massupdateitems}</h2>
<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
<tr><td width="15%" class="fieldlabel" nowrap>{$_ADMINLANG.fields.firstpaymentamount}</td><td class="fieldarea"><input type="text" size="20" name="firstpaymentamount" /></td><td width="15%" class="fieldlabel" nowrap>{$_ADMINLANG.fields.recurringamount}</td><td class="fieldarea"><input type="text" size="20" name="recurringamount" /></td></tr>
<tr><td class="fieldlabel" width="15%">{$_ADMINLANG.fields.nextduedate}</td><td class="fieldarea"><input type="text" size="20" name="nextduedate" class="datepick" /> <input type="checkbox" name="proratabill" id="proratabill" /> <label for="proratabill">{$_ADMINLANG.clientsummary.createproratainvoice}</label></td><td width="15%" class="fieldlabel">{$_ADMINLANG.fields.billingcycle}</td><td class="fieldarea"><select name="billingcycle"><option value="">- {$_ADMINLANG.global.nochange} -</option><option value="Free Account">{$_ADMINLANG.billingcycles.free}</option><option value="One Time">{$_ADMINLANG.billingcycles.onetime}</option><option value="Monthly">{$_ADMINLANG.billingcycles.monthly}</option><option value="Quarterly">{$_ADMINLANG.billingcycles.quarterly}</option><option value="Semi-Annually">{$_ADMINLANG.billingcycles.semiannually}</option><option value="Annually">{$_ADMINLANG.billingcycles.annually}</option><option value="Biennially">{$_ADMINLANG.billingcycles.biennially}</option><option value="Triennially">{$_ADMINLANG.billingcycles.triennially}</option></select></td></tr>
<tr><td class="fieldlabel" width="15%">{$_ADMINLANG.fields.paymentmethod}</td><td class="fieldarea">{$paymentmethoddropdown}</td><td class="fieldlabel" width="15%">{$_ADMINLANG.fields.status}</td><td class="fieldarea"><select name="status"><option value="">- {$_ADMINLANG.global.nochange} -</option><option value="Pending">{$_ADMINLANG.status.pending}</option><option value="Active">{$_ADMINLANG.status.active}</option><option value="Suspended">{$_ADMINLANG.status.suspended}</option><option value="Terminated">{$_ADMINLANG.status.terminated}</option><option value="Cancelled">{$_ADMINLANG.status.cancelled}</option><option value="Fraud">{$_ADMINLANG.status.fraud}</option></select></td></tr>
<tr><td class="fieldlabel" width="15%">{$_ADMINLANG.services.modulecommands}</td><td class="fieldarea" colspan="3"><input type="submit" name="masscreate" value="{$_ADMINLANG.modulebuttons.create}" class="button" /> <input type="submit" name="masssuspend" value="{$_ADMINLANG.modulebuttons.suspend}" class="button" /> <input type="submit" name="massunsuspend" value="{$_ADMINLANG.modulebuttons.unsuspend}" class="button" /> <input type="submit" name="massterminate" value="{$_ADMINLANG.modulebuttons.terminate}" class="button" /> <input type="submit" name="masschangepackage" value="{$_ADMINLANG.modulebuttons.changepackage}" class="button" /> <input type="submit" name="masschangepw" value="{$_ADMINLANG.modulebuttons.changepassword}" class="button" /></td></tr>
<tr><td class="fieldlabel" width="15%">{$_ADMINLANG.services.overrideautosusp}</td><td class="fieldarea" colspan="3"><input type="checkbox" name="overideautosuspend" id="overridesuspend" /> <label for="overridesuspend">{$_ADMINLANG.services.nosuspenduntil}</label> <input type="text" name="overidesuspenduntil" class="datepick" /></td></tr>
</table>
<br />
<div align="center"><input type="submit" name="massupdate" value="{$_ADMINLANG.global.submit}" /></div>
</div>

</form>

</td></tr></table>
