<p>{$LANG.knowledgebaseintrotext}</p>

<p class="heading2">{$LANG.knowledgebasecategories}</p>

<table width="95%" align="center">
<tr>
{foreach key=num item=kbcat from=$kbcats}
<td width="33%" valign="top"><img src="images/folder.gif" border="0" align="middle" alt="" /> <strong><a href="{if $seofriendlyurls}knowledgebase/{$kbcat.id}/{$kbcat.urlfriendlyname}{else}knowledgebase.php?action=displaycat&amp;catid={$kbcat.id}{/if}">{$kbcat.name}</a></strong> ({$kbcat.numarticles})<br />
{$kbcat.description}
</td>
{if $num is div by 3}
</tr>
<tr>
{/if}
{/foreach}
</tr>
</table>

<br />

<table width="100%" cellspacing="0" cellpadding="0"><tr><td width="50%" valign="top">

<p class="heading2">{$LANG.knowledgebasepopular}</p>

{foreach key=num item=kbarticle from=$kbmostviews}
<div class="kbarticle">
<img src="images/article.gif" align="middle" alt="" /> <strong><a href="{if $seofriendlyurls}knowledgebase/{$kbarticle.id}/{$kbarticle.urlfriendlytitle}.html{else}knowledgebase.php?action=displayarticle&amp;id={$kbarticle.id}{/if}">{$kbarticle.title}</a></strong><br />
{$kbarticle.article|truncate:50:"..."}<br />
<span class="kbviews">{$LANG.knowledgebaseviews}: {$kbarticle.views}</span>
</div>
{/foreach}

</td><td width="50%">

<form method="post" action="knowledgebase.php?action=search">
<p align="center" class="heading2">{$LANG.knowledgebasesearch}</p><p align="center"><input type="text" name="search" size="25" /><br /><br /><input type="submit" value="{$LANG.knowledgebasesearch}" class="button" /></p>
</form>

</td></tr></table>