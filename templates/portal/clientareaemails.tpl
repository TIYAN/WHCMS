<h2>{$LANG.clientareaemails}</h2>
<p>{$LANG.clientareaemailsintrotext}</p>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <td>{$numproducts} {$LANG.recordsfound}, {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</td>
    <td align="right">{if $prevpage}<a href="clientarea.php?action=emails&amp;page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="clientarea.php?action=emails&amp;page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td>
  </tr>
</table>
<br />
<table width="100%" border="0" cellpadding="10" cellspacing="0" class="data">
  <tr>
    <th width="30%">{$LANG.clientareaemailsdate}</th>
    <th width="70%">{$LANG.clientareaemailssubject}</th>
  </tr>
  {foreach key=num item=email from=$emails}
  <tr>
    <td>{$email.date}</td>
    <td><a href="#" onclick="window.open('viewemail.php?id={$email.id}','','width=650,height=400,scrollbars=yes');return false">{$email.subject}</a></td>
  </tr>
  {foreachelse}
  <tr>
    <td colspan="2">{$LANG.norecordsfound}</td>
  </tr>
  {/foreach}
</table>
<br />
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr>
    <td>{$LANG.show}: <a href="clientarea.php?action=emails&itemlimit=10">10</a> <a href="clientarea.php?action=emails&itemlimit=25">25</a> <a href="clientarea.php?action=emails&itemlimit=50">50</a> <a href="clientarea.php?action=emails&itemlimit=100">100</a> <a href="clientarea.php?action=emails&itemlimit=all">{$LANG.all}</a></td>
    <td align="right">{if $prevpage}<a href="clientarea.php?action=emails&amp;page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="clientarea.php?action=emails&amp;page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td>
  </tr>
</table><br />