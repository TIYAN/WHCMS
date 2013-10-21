<p>{$LANG.knowledgebaseintrotext}</p>
<h2>{$LANG.knowledgebasesearch}</h2>
<form method="post" action="knowledgebase.php?action=search">
<input type="hidden" name="catid" value="{$catid}" />
  <p align="center">
    <input type="text" name="search" size="40" value="{$smarty.post.search}" /> <input type="submit" value="{$LANG.knowledgebasesearch}" />
  </p>
</form>
{if $kbcats}
<h2>{$LANG.knowledgebasecategories}</h2>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr> {foreach key=num item=kbcat from=$kbcats}
    <td width="33%" valign="top"><img src="images/folder.gif" border="0" class="absmiddle" alt="Folder" /> <strong><a href="{if $seofriendlyurls}knowledgebase/{$kbcat.id}/{$kbcat.urlfriendlyname}{else}knowledgebase.php?action=displaycat&amp;catid={$kbcat.id}{/if}">{$kbcat.name}</a></strong> ({$kbcat.numarticles})<br />
      {$kbcat.description} </td>
    {if $num is div by 3} </tr>
  <tr> {/if}
    {/foreach} </tr>
</table>
{/if}

<h2>{$LANG.knowledgebasearticles}</h2>
{if $kbarticles}
  {foreach key=num item=kbarticle from=$kbarticles}
  <div class="kbarticle"><img src="images/article.gif" class="absmiddle" border="0" alt="Article" /> <strong><a href="{if $seofriendlyurls}knowledgebase/{$kbarticle.id}/{$kbarticle.urlfriendlytitle}.html{else}knowledgebase.php?action=displayarticle&amp;id={$kbarticle.id}{/if}">{$kbarticle.title}</a></strong><br />
      {$kbarticle.article|truncate:100:"..."}<br />
      <span class="kbviews">{$LANG.knowledgebaseviews}: {$kbarticle.views}</span>
  </div>
  {/foreach}
{else}
<p align="center"><strong>{$LANG.knowledgebasenoarticles}</strong></p>
{/if}<br />