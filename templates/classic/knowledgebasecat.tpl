<p>{$LANG.knowledgebaseintrotext}</p>

{if $kbcats}

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

{/if}

{if $kbarticles}

<p class="heading2">{$LANG.knowledgebasearticles}</p>

{foreach key=num item=kbarticle from=$kbarticles}
<div class="kbarticle">
<img src="images/article.gif" align="middle" alt="" /> <strong><a href="{if $seofriendlyurls}knowledgebase/{$kbarticle.id}/{$kbarticle.urlfriendlytitle}.html{else}knowledgebase.php?action=displayarticle&amp;id={$kbarticle.id}{/if}">{$kbarticle.title}</a></strong><br />
{$kbarticle.article|truncate:100:"..."}<br />
<span class="kbviews">{$LANG.knowledgebaseviews}: {$kbarticle.views}</span>
</div>
{/foreach}

{else}

<p align="center"><strong>{$LANG.knowledgebasenoarticles}</strong></p>

{/if}