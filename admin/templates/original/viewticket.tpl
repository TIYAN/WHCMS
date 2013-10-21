{$infobox}

<div id="replyingadmin">
{if $replyingadmin}<div class="errorbox">{$replyingadmin.name} {$_ADMINLANG.support.viewedandstarted} @ {$replyingadmin.time}</div>{/if}
</div>

<h2 style="margin:0;">#{$tid} - {$subject} <select name="ticketstatus" id="ticketstatus" style="font-size:18px;">
{foreach from=$statuses item=statusitem}
<option{if $statusitem.title eq $status} selected{/if} style="color:{$statusitem.color}">{$statusitem.title}</option>
{/foreach}
</select></h2>

<p>{$_ADMINLANG.fields.client}: {if $userid}<a href="clientssummary.php?userid={$userid}"{if $clientgroupcolour} style="background-color:{$clientgroupcolour}"{/if} target="_blank">{$clientname}</a>{if $contactid} (<a href="clientscontacts.php?userid={$userid}&contactid={$contactid}"{if $clientgroupcolour} style="background-color:{$clientgroupcolour}"{/if} target="_blank">{$contactname}</a>){/if}{else}{$_ADMINLANG.support.notregclient}{/if} | {$_ADMINLANG.support.lastreply}: {$lastreply}</p>

{if $clientnotes}
<div style="clear:both;overflow:auto;margin:15px 0;padding:5px; max-height:150px;border:2px dashed #e0e0e0;border-right:0;background-color:#fff;-moz-border-radius: 6px;-webkit-border-radius: 6px;-o-border-radius: 6px;border-radius: 6px;">
{foreach from=$clientnotes item=note}
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
<div style="margin-bottom:15px;">{$addon_html}</div>
{/foreach}

<div id="tabs">
    <ul>
        <li id="tab0" class="tab"><a href="javascript:;">{$_ADMINLANG.support.addreply}</a></li>
        <li id="tab1" class="tab"><a href="javascript:;">{$_ADMINLANG.support.addnote}</a></li>
        <li id="tab2" class="tab"><a href="javascript:;">{$_ADMINLANG.setup.customfields}</a></li>
        <li id="tab3" class="tab" onclick="loadTab(3,'tickets',0)"><a href="javascript:;">{$_ADMINLANG.support.clienttickets}</a></li>
        <li id="tab4" class="tab" onclick="loadTab(4,'clientlog',0)"><a href="javascript:;">{$_ADMINLANG.support.clientlog}</a></li>
        <li id="tab5" class="tab"><a href="javascript:;">{$_ADMINLANG.fields.options}</a></li>
        <li id="tab6" class="tab" onclick="loadTab(6,'ticketlog',0)"><a href="javascript:;">{$_ADMINLANG.support.ticketlog}</a></li>
    </ul>
</div>

<div id="tab0box" class="tabbox">
    <div id="tab_content">

<form method="post" action="{$smarty.server.PHP_SELF}?action=viewticket&id={$ticketid}" enctype="multipart/form-data" name="replyfrm" id="replyfrm">

<textarea name="message" id="replymessage" rows="14" style="width:100%">


{$signature}</textarea>

<br /><img src="images/spacer.gif" height="8" width="1" /><br />

<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
<tr><td width="15%" class="fieldlabel">{$_ADMINLANG.support.postreply}</td><td class="fieldarea"><select name="postaction">
<option value="return">{$_ADMINLANG.support.setansweredreturn}
<option value="answered">{$_ADMINLANG.support.setansweredremain}
{foreach from=$statuses item=statusitem}
{if $statusitem.id > 4}<option value="setstatus{$statusitem.id}">{$_ADMINLANG.support.setto} {$statusitem.title} {$_ADMINLANG.support.andremain}</option>{/if}
{/foreach}
<option value="close">{$_ADMINLANG.support.closereturn}
<option value="note">{$_ADMINLANG.support.addprivatenote}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="#" onClick="window.open('supportticketskbarticle.php','kbartwnd','width=500,height=400,scrollbars=yes');return false">{$_ADMINLANG.support.insertkblink}</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="#" id="insertpredef">{$_ADMINLANG.support.insertpredef}</a>
</td></tr>
<tr><td class="fieldlabel">{$_ADMINLANG.support.attachments}</td><td class="fieldarea"><input type="file" name="attachments[]" size="85"> <a href="#" id="addfileupload"><img src="images/icons/add.png" align="absmiddle" border="0" /> {$_ADMINLANG.support.addmore}</a><br /><div id="fileuploads"></div></td></tr>
{if $userid}<tr><td class="fieldlabel">{$_ADMINLANG.support.addbilling}</td><td class="fieldarea"><input type="text" name="billingdescription" size="60" value="{$_ADMINLANG.support.toinvoicedes}" onfocus="if(this.value=='{$_ADMINLANG.support.toinvoicedes}')this.value=''" /> @ <input type="text" name="billingamount" size="10" value="{$_ADMINLANG.fields.amount}" /> <select name="billingaction">
<option value="3" /> {$_ADMINLANG.billableitems.invoiceimmediately}</option>
<option value="0" /> {$_ADMINLANG.billableitems.dontinvoicefornow}</option>
<option value="1" /> {$_ADMINLANG.billableitems.invoicenextcronrun}</option>
<option value="2" /> {$_ADMINLANG.billableitems.addnextinvoice}</option>
</select></td></tr>{/if}
</table>

<div id="prerepliescontainer" style="display:none;">
    <img src="images/spacer.gif" height="8" width="1" />
    <br />
    <div style="border:1px solid #DFDCCE;background-color:#F7F7F2;padding:5px;text-align:left;">
        <div style="float:right;">Search: <input type="text" id="predefq" size="25" /></div>
        <div id="prerepliescontent">{$predefinedreplies}</div>
    </div>
</div>

<img src="images/spacer.gif" height="8" width="1" />
<br />
<div align="center"><input type="submit" value="{$_ADMINLANG.support.addresponse}" name="postreply" class="button" id="postreplybutton" /></div>

</form>

    </div>
</div>
<div id="tab1box" class="tabbox">
    <div id="tab_content">

<form method="post" action="{$smarty.server.PHP_SELF}?action=viewticket&id={$ticketid}">
<input type="hidden" name="postaction" value="note" />

<textarea name="message" id="replymessage" rows="14" style="width:100%"></textarea>

<br />
<img src="images/spacer.gif" height="8" width="1" />
<br />

<div align="center"><input type="submit" value="{$_ADMINLANG.support.addnote}" class="button" name="postreply" /></div>

</form>

    </div>
</div>
<div id="tab2box" class="tabbox">
    <div id="tab_content">

<form method="post" action="{$smarty.server.PHP_SELF}?action=viewticket&id={$ticketid}&sub=savecustomfields">

{if !$numcustomfields}
<div align="center">{$_ADMINLANG.support.nocustomfields}</div>
{else}
<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
{foreach from=$customfields item=customfield}
<tr><td width="25%" class="fieldlabel">{$customfield.name}</td><td class="fieldarea">{$customfield.input}</td></tr>
{/foreach}
</table>
<img src="images/spacer.gif" height="10" width="1" /><br />
<div align="center"><input type="submit" value="{$_ADMINLANG.global.savechanges}" class="button"></div>
</form>
{/if}

    </div>
</div>
<div id="tab3box" class="tabbox">
    <div id="tab_content">

<img src="images/loading.gif" align="top" /> {$_ADMINLANG.global.loading}

    </div>
</div>
<div id="tab4box" class="tabbox">
    <div id="tab_content">

<img src="images/loading.gif" align="top" /> {$_ADMINLANG.global.loading}

    </div>
</div>
<div id="tab5box" class="tabbox">
    <div id="tab_content">

<form method="post" action="{$smarty.server.PHP_SELF}?action=viewticket&id={$ticketid}">

<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
<tr><td width="15%" class="fieldlabel">{$_ADMINLANG.support.department}</td><td class="fieldarea"><select name="deptid">
{foreach from=$departments item=department}
<option value="{$department.id}"{if $department.id eq $deptid} selected{/if}>{$department.name}</option>
{/foreach}
</select></td><td width="15%" class="fieldlabel">{$_ADMINLANG.fields.clientid}</td><td class="fieldarea"><input type="text" name="userid" size="15" id="clientsearchval" value="{$userid}" /> <img src="images/icons/delete.png" alt="Cancel" class="absmiddle" id="clientsearchcancel" height="16" width="16"><div id="ticketclientsearchresults"></div></td></tr>
<tr><td class="fieldlabel">{$_ADMINLANG.fields.subject}</td><td class="fieldarea"><input type="text" name="subject" value="{$subject}" style="width:80%"></td><td class="fieldlabel">{$_ADMINLANG.support.flag}</td><td class="fieldarea"><select name="flagto">
<option value="0">{$_ADMINLANG.global.none}</option>
{foreach from=$staff item=staffmember}
<option value="{$staffmember.id}"{if $staffmember.id eq $flag} selected{/if}>{$staffmember.name}</option>
{/foreach}
</select></td></tr>
<tr><td class="fieldlabel">{$_ADMINLANG.fields.status}</td><td class="fieldarea"><select name="status">
{foreach from=$statuses item=statusitem}
<option{if $statusitem.title eq $status} selected{/if} style="color:{$statusitem.color}">{$statusitem.title}</option>
{/foreach}
</select></td><td class="fieldlabel">{$_ADMINLANG.support.priority}</td><td class="fieldarea"><select name="priority">
<option value="High"{if $priority eq "High"} selected{/if}>{$_ADMINLANG.status.high}</option>
<option value="Medium"{if $priority eq "Medium"} selected{/if}>{$_ADMINLANG.status.medium}</option>
<option value="Low"{if $priority eq "Low"} selected{/if}>{$_ADMINLANG.status.low}</option>
</select></td></tr>
<tr><td class="fieldlabel">{$_ADMINLANG.support.ccrecepients}</td><td class="fieldarea"><input type="text" name="cc" value="{$cc}" size="40"> ({$_ADMINLANG.transactions.commaseparated})</td><td class="fieldlabel">{$_ADMINLANG.support.mergeticket}</td><td class="fieldarea"><input type="text" name="mergetid" size="10"> ({$_ADMINLANG.support.notocombine})</td></tr>
</table>

<img src="images/spacer.gif" height="10" width="1"><br>
<div align="center"><input type="submit" value="{$_ADMINLANG.global.savechanges}" class="button"></div>
</form>

    </div>
</div>
<div id="tab6box" class="tabbox">
    <div id="tab_content">

<img src="images/loading.gif" align="top" /> {$_ADMINLANG.global.loading}

    </div>
</div>

<br />

{if $numnotes}
<h2>{$_ADMINLANG.support.privatestaffnote}</h2>
{foreach from=$notes item=note}
<div class="ticketstaffnotes">
<table class="ticketstaffnotestable">
<tr><td><strong>{$note.admin}</strong></td><td align="right"><strong>{$note.date}</strong></td><td width="16"><a href="#" onClick="doDeleteNote('{$note.id}');return false"><img src="images/delete.gif" alt="{$_ADMINLANG.support.deleteticketnote}" border="0" align="absmiddle"></a></td></tr>
</table>
{$note.message}
</div><br />
{/foreach}
{/if}

{if $relatedservices}
<div class="tablebg">
<table class="datatable" id="relatedservicestbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<tr><th>{$_ADMINLANG.fields.product}</th><th>{$_ADMINLANG.fields.amount}</th><th>{$_ADMINLANG.fields.billingcycle}</th><th>{$_ADMINLANG.fields.signupdate}</th><th>{$_ADMINLANG.fields.nextduedate}</th><th>{$_ADMINLANG.fields.status}</th></tr>
{foreach from=$relatedservices item=relatedservice}
<tr{if $relatedservice.selected} class="rowhighlight"{/if}><td>{$relatedservice.name}</td><td>{$relatedservice.amount}</td><td>{$relatedservice.billingcycle}</td><td>{$relatedservice.regdate}</td><td>{$relatedservice.nextduedate}</td><td>{$relatedservice.status}</td></tr>
{/foreach}
</table>
</div>
{if $relatedservicesexpand}<div id="relatedservicesexpand" style="padding:2px 15px;text-align:right;"><a href="#" onclick="expandRelServices();return false">{$_ADMINLANG.support.expand}</a></div>{/if}
{/if}

<br />
<form method="post" action="supporttickets.php" id="ticketreplies">
<input type="hidden" name="id" value="{$ticketid}" />
<input type="hidden" name="action" value="split" />
<table width="100%" cellpadding=5 cellspacing=1 bgcolor="#cccccc" align="center">
{foreach from=$replies item=reply}
<tr bgcolor="{cycle values="#F4F4F4,#F8F8F8"}"><td rowspan="2" width="200" valign="top">

{if $reply.admin}

<strong>{$reply.admin}</strong><br />
{$_ADMINLANG.support.staff}<br />

{if $reply.rating}
<br />
{$_ADMINLANG.support.rating}: {$reply.rating}
<br />
{/if}

{else}

<strong>{$reply.clientname}</strong><br />

{if $reply.contactid}
{$_ADMINLANG.fields.contact}<br />
{elseif $reply.userid}
{$_ADMINLANG.fields.client}<br />
{else}
<a href="mailto:{$reply.clientemail}">{$reply.clientemail}</a>
<br />
<input type="button" value="{$_ADMINLANG.support.blocksender}" style="font-size:9px;" onclick="window.location='{$smarty.server.PHP_SELF}?action=viewticket&id={$ticketid}&blocksender=true'"><br>
{/if}

{/if}

<br />
<div class="editbtns{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}"><input type="button" value="{$_ADMINLANG.global.edit}" onclick="editTicket('{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}')" /></div><div class="editbtns{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}" style="display:none"><input type="button" value="{$_ADMINLANG.global.save}" onclick="editTicketSave('{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}')" /> <input type="button" value="{$_ADMINLANG.global.cancel}" onclick="editTicketCancel('{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}')" /></div>

</td><td bgcolor="#F4F4F4">

{if $reply.id}
<a href="#" onClick="doDeleteReply('{$reply.id}');return false">
{else}
<a href="#" onClick="doDeleteTicket();return false">
{/if}
<img src="images/icons/delete.png" alt="{$_ADMINLANG.support.deleteticket}" align="right" border="0" hspace="5"></a>

{if $reply.id}
<a href="#" onClick="quoteTicket('','{$reply.id}')">
{else}
<a href="#" onClick="quoteTicket('{$ticketid}','')">
{/if}
<img src="images/icons/quote.png" align="right" border="0"></a>
{if $reply.id }
<span style="float:right"><input type="checkbox" name="rids[]" value="{$reply.id}" />
{/if}
</span><span style="float:left">{$reply.date}</span>

</td></tr>
<tr><td bgcolor="#F4F4F4" id="content{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}">

<div>
<div class="message">
{$reply.message}
</div>
{if $reply.numattachments}
<p>
<b>{$_ADMINLANG.support.attachments}</b>
<br />
{foreach from=$reply.attachments key=num item=attachment}
{if $thumbnails}
<div class="ticketattachmentcontainer">
<a href="../{$attachment.dllink}"><img src="../includes/thumbnail.php?{if $reply.id}rid={$reply.id}{else}tid={$ticketid}{/if}&i={$num}" class="ticketattachmentthumb" /><br />
<img src="images/icons/attachment.png" align="absmiddle" /> {$attachment.filename}</a><br /><small><a href="{$attachment.deletelink}" onclick="return confirm('{$_ADMINLANG.support.delattachment|escape:'javascript'}')" style="color:#cc0000">{$_ADMINLANG.support.remove}</a></small>
</div>
{else}
<a href="../{$attachment.dllink}"><img src="images/icons/attachment.png" align="absmiddle" /> {$attachment.filename}</a> <small><a href="{$attachment.deletelink}" onclick="return confirm('{$_ADMINLANG.support.delattachment|escape:'javascript'}')" style="color:#cc0000">{$_ADMINLANG.support.remove}</a></small><br />
{/if}
{/foreach}
</p>
{/if}

</div>

</td></tr>
{/foreach}
</table>

<p align="center"><a href="supportticketsprint.php?id={$ticketid}" target="_blank">{$_ADMINLANG.support.viewprintable}</a>{if $repliescount>1}<span style="float:right;"><input type="button" value="{$_ADMINLANG.support.splitticketdialogbutton}" onclick="showDialog('splitticket')" /></span>{/if}</p>

{$splitticketdialog}
<input type="hidden" name="splitdeptid" id="splitdeptid" />
<input type="hidden" name="splitsubject" id="splitsubject" />
<input type="hidden" name="splitpriority" id="splitpriority" />
<input type="hidden" name="splitnotifyclient" id="splitnotifyclient" />
</form>