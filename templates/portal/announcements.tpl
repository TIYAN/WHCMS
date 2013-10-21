{foreach key=num item=announcement from=$announcements}
<h2><a href="{if $seofriendlyurls}announcements/{$announcement.id}/{$announcement.urlfriendlytitle}.html{else}{$smarty.server.PHP_SELF}?id={$announcement.id}{/if}">{$announcement.title}</a></h2>
<p class="small"><strong>{$announcement.timestamp|date_format:"%A, %B %e, %Y"}</strong></p>
{$announcement.text|strip_tags|truncate:200:"..."}
{if strlen($announcement.text)>200}<p><a href="{if $seofriendlyurls}announcements/{$announcement.id}/{$announcement.urlfriendlytitle}.html{else}{$smarty.server.PHP_SELF}?id={$announcement.id}{/if}">{$LANG.more} &raquo;</a></p>{/if}
{if $facebookrecommend}
{literal}
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) {return;}
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
{/literal}
<div class="fb-recommend" data-href="{$systemurl}{if $seofriendlyurls}announcements/{$announcement.id}/{$announcement.urlfriendlytitle}.html{else}announcements.php?id={$announcement.id}{/if}" data-send="true" data-width="450" data-show-faces="true" data-action="recommend"></div>
{/if}
<hr />
{foreachelse}
<h2>{$LANG.announcementsnone}</h2>
{/foreach}

{if $prevpage || $nextpage}

<div style="float: left; width: 100px;">
{if $prevpage}<a href="announcements.php?page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if}
</div>

<div style="float: right; width: 100px; text-align: right;">
{if $nextpage}<a href="announcements.php?page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}
</div>

{/if}

<br />

<p align="center"><img src="images/rssfeed.gif" class="absmiddle" alt="" border="0" /> <a href="announcementsrss.php">{$LANG.announcementsrss}</a></p>