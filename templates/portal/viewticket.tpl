{if $error}
<p>{$LANG.supportticketinvalid}</p>
{else}

{literal}
<script language="javascript">
jQuery(document).ready(function(){
    jQuery("#addfileupload").click(function () {
        jQuery("#fileuploads").append("<input type=\"file\" name=\"attachments[]\" size=\"50\"><br />");
        return false;
    });
});
function rating_hover(id) {
  var selrating=id.split('_');
  for(var i=1; i<=5; i++){
    if(i<=selrating[1]) document.getElementById(selrating[0]+'_'+i).style.background="url(images/rating_pos.png)";
    if(i>selrating[1]) document.getElementById(selrating[0]+'_'+i).style.background="url(images/rating_neg.png)";
  }
}
function rating_leave(id){
  for(var i=1; i<=5; i++){
    document.getElementById(id+'_'+i).style.background="url(images/rating_neg.png)";
  }
}
function rating_select(id){
  {/literal}window.location='viewticket.php?tid={$tid}&c={$c}&rating='+id;{literal}
}
</script>
{/literal}

<h2>{$LANG.supportticketsviewticket} #{$tid}</h2>

{if $errormessage}
<div class="errorbox">{$errormessage}</div>
<br />
{/if}

<table width="100%" border="0" cellpadding="10" cellspacing="0" class="data">
        <tr>
          <th>{$LANG.supportticketsdepartment}</th>
          <th>{$LANG.supportticketsdate}</th>
          <th>{$LANG.supportticketssubject}</th>
          <th>{$LANG.supportticketsstatus}</th>
          <th>{$LANG.supportticketsticketurgency}</th>
        </tr>
        <tr>
          <td>{$department}</td>
          <td>{$date}</td>
          <td>{$subject}</td>
          <td>{$status}</td>
          <td>{$urgency}</td>
        </tr>
</table>
<br />

{if $customfields}
<table width="100%" cellspacing="0" cellpadding="0" class="frame">
  <tr>
    <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
        {foreach key=num item=customfield from=$customfields}
        <tr>
          <td width="150" class="fieldarea">{$customfield.name}:</td>
          <td>{$customfield.value}&nbsp;</td>
        </tr>
        {/foreach}
    </table></td>
  </tr>
</table>
<br />
{/if}

<div class="clientticketreplyheader">
  <table width="100%" border="0" cellpadding="10" cellspacing="0">
    <tr>
      <td>{$user|replace:"<br />
      ":" || "}</td>
      <td align="right">{$date}</td>
    </tr>
  </table>
</div>
<div class="clientticketreply">{$message}{if $attachments}<br />
  <br />
  <b>{$LANG.supportticketsticketattachments}</b><br />
  {foreach from=$attachments key=num item=attachment} <img src="images/article.gif" class="absmiddle" border="0" alt="{$attachment}" /> <a href="dl.php?type=a&id={$id}&i={$num}">{$attachment}</a><br />
  {/foreach}{/if}</div>
{foreach key=num item=reply from=$replies}
<div class="{if $reply.admin}admin{else}client{/if}ticketreplyheader">
  <table width="100%" border="0" cellpadding="10" cellspacing="0">
    <tr>
      <td>{$reply.user|replace:"<br />
      ":" || "}</td>
      <td align="right">{$reply.date}</td>
    </tr>
  </table>
</div>
<div class="{if $reply.admin}admin{else}client{/if}ticketreply">{$reply.message}{if $reply.attachments}<br />
  <br />
  <b>{$LANG.supportticketsticketattachments}</b><br />
  {foreach from=$reply.attachments key=num item=attachment} <img src="images/article.gif" class="absmiddle" border="0" alt="{$attachment}" /> <a href="dl.php?type=ar&id={$reply.id}&i={$num}">{$attachment}</a><br />
  {/foreach}{/if}
  {if $reply.admin && $ratingenabled}{if $reply.rating}
  <table align="right" cellspacing="0" cellpadding="0">
    <tr height="16">
      <td>{$LANG.ticketreatinggiven}&nbsp</td>
      <td width="16" background="images/rating_{if $reply.rating>=1}pos{else}neg{/if}.png"></td>
      <td width="16" background="images/rating_{if $reply.rating>=2}pos{else}neg{/if}.png"></td>
      <td width="16" background="images/rating_{if $reply.rating>=3}pos{else}neg{/if}.png"></td>
      <td width="16" background="images/rating_{if $reply.rating>=4}pos{else}neg{/if}.png"></td>
      <td width="16" background="images/rating_{if $reply.rating>=5}pos{else}neg{/if}.png"></td>
    </tr>
  </table>
  {else}
  <table align="right" cellspacing="0" cellpadding="0">
    <tr height="16" onmouseout="rating_leave('rate{$reply.id}')" style="cursor: pointer; cursor: hand;">
      <td>{$LANG.ticketratingquestion}&nbsp</td>
      <td onmouseover="rating_hover('rate{$reply.id}_1')" onclick="rating_select('rate{$reply.id}_1')"><b>{$LANG.ticketratingpoor}&nbsp;</td>
      <td width="16" id="rate{$reply.id}_1" onmouseover="rating_hover(this.id)" onclick="rating_select(this.id)" background="images/rating_neg.png"></td>
      <td width="16" id="rate{$reply.id}_2" onmouseover="rating_hover(this.id)" onclick="rating_select(this.id)" background="images/rating_neg.png"></td>
      <td width="16" id="rate{$reply.id}_3" onmouseover="rating_hover(this.id)" onclick="rating_select(this.id)" background="images/rating_neg.png"></td>
      <td width="16" id="rate{$reply.id}_4" onmouseover="rating_hover(this.id)" onclick="rating_select(this.id)" background="images/rating_neg.png"></td>
      <td width="16" id="rate{$reply.id}_5" onmouseover="rating_hover(this.id)" onclick="rating_select(this.id)" background="images/rating_neg.png"></td>
      <td onmouseover="rating_hover('rate{$reply.id}_5')" onclick="rating_select('rate{$reply.id}_5')"><b>&nbsp;{$LANG.ticketratingexcellent}</td>
    </tr>
  </table>
  {/if}<br />
  <br />
  <br />
  {/if}</div>
  <br />
{/foreach}

{if $showclosebutton}
<p align="center">
  <input type="button" value="{$LANG.supportticketsstatuscloseticket}" onclick="window.location='{$smarty.server.PHP_SELF}?tid={$tid}&amp;c={$c}&amp;closeticket=true'" />
</p>
{/if}
<h3>{$LANG.supportticketsreply}</h3>
<form method="post" action="{$smarty.server.PHP_SELF}?tid={$tid}&amp;c={$c}&amp;postreply=true" enctype="multipart/form-data">
  <table width="100%" cellspacing="0" cellpadding="0" class="frame">
    <tr>
      <td><table width="100%" border="0" cellpadding="10" cellspacing="0">
          <tr>
            <td width="120" class="fieldarea">{$LANG.supportticketsclientname}</td>
            <td>{if $loggedin}{$clientname}{else}
              <input type="text" name="replyname" size=30 value="{$replyname}" />
              {/if}</td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.supportticketsclientemail}</td>
            <td>{if $loggedin}{$email}{else}
              <input type="text" name="replyemail" size=50 value="{$replyemail}" />
              {/if}</td>
          </tr>
          <tr>
            <td colspan="2" class="fieldarea"><textarea name="replymessage" rows="12" cols="60" style="width:100%">{$replymessage}</textarea></td>
          </tr>
          <tr>
            <td class="fieldarea">{$LANG.supportticketsticketattachments}</td>
            <td><input type="file" name="attachments[]" size="50" />
              <a href="#" id="addfileupload"><img src="images/add.gif" class="absmiddle" alt="" border="0" /> {$LANG.addmore}</a><br />
              <div id="fileuploads"></div>
              ({$LANG.supportticketsallowedextensions}: {$allowedfiletypes})</td>
          </tr>
      </table></td>
    </tr>
  </table>
  <p align="center">
    <input type="submit" value="{$LANG.supportticketsticketsubmit}" class="button" />
  </p>
</form>
{/if}<br />