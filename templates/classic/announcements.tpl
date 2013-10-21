{if $twitterusername}
{literal}<script language="javascript">
jQuery(document).ready(function(){
  jQuery.post("announcements.php", { action: "twitterfeed", numtweets: 5 },
    function(data){
      jQuery("#announcementstwitterfeed").html(data);
    });
});
</script>{/literal}
<br />
<div id="announcementstwitter">
<div align="center"><a href="http://twitter.com/{$twitterusername}" target="_blank"><img src="images/twitterlogo.png" width="200" border="0" alt="{$LANG.twitterlatesttweets}" /></a></div>
<div id="announcementstwitterfeed">
<ul><li>{$LANG.loading}</li></ul>
</div>
</div>
{/if}

{foreach key=num item=announcement from=$announcements}
<p><a href="{if $seofriendlyurls}announcements/{$announcement.id}/{$announcement.urlfriendlytitle}.html{else}{$smarty.server.PHP_SELF}?id={$announcement.id}{/if}" class="heading2"><img src="images/article.gif" align="absmiddle" border="0" /> {$announcement.title}</a></p>
{$announcement.text|strip_tags|truncate:200:"..."}
{if strlen($announcement.text)>200}<div align="right"><a href="{if $seofriendlyurls}announcements/{$announcement.id}/{$announcement.urlfriendlytitle}.html{else}{$smarty.server.PHP_SELF}?id={$announcement.id}{/if}">{$LANG.more} &raquo;</a></div>{/if}
<p class="heading3">{$announcement.timestamp|date_format:"%A, %B %e, %Y"}</p>
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
<div class="fb-like" data-href="{$systemurl}{if $seofriendlyurls}announcements/{$announcement.id}/{$announcement.urlfriendlytitle}.html{else}announcements.php?id={$announcement.id}{/if}" data-send="true" data-width="450" data-show-faces="true" data-action="recommend"></div>
{/if}
<br /><br />
{foreachelse}
<p align="center"><strong>{$LANG.announcementsnone}</strong></p>
{/foreach}

<br />

{if $prevpage || $nextpage}

<div style="float: left; width: 100px;">
{if $prevpage}<a href="announcements.php?page={$prevpage}">{/if}&laquo; {$LANG.previouspage}{if $prevpage}</a>{/if}
</div>

<div style="float: right; width: 100px; text-align: right;">
{if $nextpage}<a href="announcements.php?page={$nextpage}">{/if}{$LANG.nextpage} &raquo;{if $nextpage}</a>{/if}
</div>

{/if}

<br />

<p align="center"><img src="images/rssfeed.gif" align="middle" alt="" /> <a href="announcementsrss.php">{$LANG.announcementsrss}</a></p>

<br />