<div class="networkissuessummary">
<a href="{$smarty.server.PHP_SELF}?view=open" class="networkissuesopen">{$opencount} {$LANG.networkissuesstatusopen}</a>
<a href="{$smarty.server.PHP_SELF}?view=scheduled" class="networkissuesscheduled">{$scheduledcount} {$LANG.networkissuesstatusscheduled}</a>
<a href="{$smarty.server.PHP_SELF}?view=resolved" class="networkissuesclosed">{$resolvedcount} {$LANG.networkissuesstatusresolved}</a>
</div>

{foreach from=$issues item=issue}

{if $issue.clientaffected}<div class="networkissueaffected">{/if}

<p><span class="heading2"><img src="images/article.gif" align="absmiddle" border="0" /> {$issue.title} ({$issue.status})</span><br /><img src="images/spacer.gif" align="absmiddle" height="1" width="18" border="0" /> <span class="heading3">{$LANG.networkissuesaffecting} {$issue.type} - {if $issue.type eq $LANG.networkissuestypeserver}{$issue.server}{else}{$issue.affecting}{/if} | {$LANG.networkissuespriority} - {$issue.priority}</span></p>

<div class="networkissuedescription">
{$issue.description}
<b>{$LANG.networkissuesdate}</b> - {$issue.startdate}{if $issue.enddate} - {$issue.enddate}{/if}<br />
<b>{$LANG.networkissueslastupdated}</b> - {$issue.lastupdate}
</div>

{if $issue.clientaffected}</div>{/if}

{foreachelse}

<p align="center"><b>{$LANG.networkissuesnonefound}</b></p>

{/foreach}

<br />

<p align="center">{if $prevpage}<a href="networkissues.php?{if $view}view={$view}&{/if}page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if} &nbsp; {if $nextpage}<a href="networkissues.php?{if $view}view={$view}&{/if}page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}</p>

{if $loggedin}<p>{$LANG.networkissuesaffectingyourservers}</p>{/if}
<br />
<p align="center"><img src="images/rssfeed.gif" class="middle" alt="" border="0" /> <a href="networkissuesrss.php">{$LANG.announcementsrss}</a></p>
<br />