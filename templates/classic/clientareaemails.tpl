<p class="heading2">{$LANG.clientareaemails}</p>

<p>{$LANG.clientareaemailsintrotext}</p>

<table width="100%" cellspacing="0" cellpadding="0"><tr><td>{$numproducts} {$LANG.recordsfound}, {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</td><td align="right">{if $prevpage}<a href="clientarea.php?action=emails&amp;page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="clientarea.php?action=emails&amp;page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td></tr></table>

<br />

<table class="clientareatable" cellspacing="1">
<tr class="clientareatableheading"><td width="30%">{$LANG.clientareaemailsdate}</td><td width="70%">{$LANG.clientareaemailssubject}</td></tr>
{foreach key=num item=email from=$emails}
<tr class="clientareatableactive"><td>{$email.date}</td><td><a href="#" onclick="window.open('viewemail.php?id={$email.id}','','width=650,height=400,scrollbars=yes');return false">{$email.subject}</a></td></tr>
{foreachelse}
<tr class="clientareatableactive"><td colspan="2">{$LANG.norecordsfound}</td></tr>
{/foreach}
</table>

<br />

<table width="100%" cellspacing="0" cellpadding="0"><tr><td>{$LANG.show}: <a href="clientarea.php?action=emails&itemlimit=10">10</a> <a href="clientarea.php?action=emails&itemlimit=25">25</a> <a href="clientarea.php?action=emails&itemlimit=50">50</a> <a href="clientarea.php?action=emails&itemlimit=100">100</a> <a href="clientarea.php?action=emails&itemlimit=all">{$LANG.all}</a></td><td align="right">{if $prevpage}<a href="clientarea.php?action=emails&amp;page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="clientarea.php?action=emails&amp;page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td></tr></table>