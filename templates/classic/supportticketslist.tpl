<p class="heading2">{$LANG.clientareanavsupporttickets}</p>

<p>{$LANG.supportticketssystemdescription}</p>

<table cellspacing="0" cellpadding="0" width="100%"><tr><td>{$LANG.supportticketsopentickets}: <strong>{$numopentickets}</strong></td><td align="right"><a href="submitticket.php">{$LANG.supportticketssubmitticket}</a></td></tr></table>

<form method="post" action="supporttickets.php">
<p align="center"><b>{$LANG.knowledgebasesearch}:</b> <input type="text" name="searchterm" size="25" value="{$searchterm}" /> <input type="submit" value="{$LANG.knowledgebasesearch}" class="button" /></p>
</form>

<table width="100%" cellspacing="0" cellpadding="0"><tr><td>{$numtickets} {$LANG.recordsfound},  {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</td><td align="right">{if $prevpage}<a href="supporttickets.php?page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="supporttickets.php?page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td></tr></table>

<br />

<table align="center" class="clientareatable" cellspacing="1">

<tr class="clientareatableheading"><td>{$LANG.supportticketsdate}</td><td>{$LANG.supportticketsdepartment}</td><td>{$LANG.supportticketssubject}</td><td>{$LANG.supportticketsstatus}</td><td>{$LANG.supportticketsticketurgency}</td></tr>

{foreach key=num item=ticket from=$tickets}

<tr class="clientareatableactive"><td>{$ticket.date}</td><td>{$ticket.department}</td><td><DIV ALIGN="left"><img src="images/article.gif" hspace="5" align="middle"><a href="viewticket.php?tid={$ticket.tid}&amp;c={$ticket.c}">{if $ticket.unread}<strong>{/if}{$ticket.subject}{if $ticket.unread}</strong>{/if}</a></DIV></td><td>{$ticket.status}</td><td width=80>{$ticket.urgency}</td></tr>

{foreachelse}

<tr class="clientareatableactive"><td colspan="5">{$LANG.norecordsfound}</td></tr>

{/foreach}

</table>

<br />

<table width="100%" cellspacing="0" cellpadding="0"><tr><td>{$LANG.show}: <a href="supporttickets.php?{if $searchterm}searchterm={$searchterm}&{/if}itemlimit=10">10</a> <a href="supporttickets.php?{if $searchterm}searchterm={$searchterm}&{/if}itemlimit=25">25</a> <a href="supporttickets.php?{if $searchterm}searchterm={$searchterm}&{/if}itemlimit=50">50</a> <a href="supporttickets.php?{if $searchterm}searchterm={$searchterm}&{/if}itemlimit=100">100</a> <a href="supporttickets.php?{if $searchterm}searchterm={$searchterm}&{/if}itemlimit=all">{$LANG.all}</a></td><td align="right">{if $prevpage}<a href="supporttickets.php?{if $searchterm}searchterm={$searchterm}&{/if}page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="supporttickets.php?{if $searchterm}searchterm={$searchterm}&{/if}page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</td></tr></table>