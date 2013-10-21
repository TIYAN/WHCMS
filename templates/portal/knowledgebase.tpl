<p>{$LANG.knowledgebaseintrotext}</p>
<h2>{$LANG.knowledgebasesearch}</h2>
<form method="post" action="knowledgebase.php?action=search">
  <p align="center">
    <input type="text" name="search" size="40" /> <input type="submit" value="{$LANG.knowledgebasesearch}" />
  </p>
</form>
<h2>{$LANG.knowledgebasecategories}</h2>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr> {foreach key=num item=kbcat from=$kbcats}
    <td width="33%" align="left" valign="top"><img src="images/folder.gif" border="0" class="absmiddle" alt="Folder" /> <strong><a href="{if $seofriendlyurls}knowledgebase/{$kbcat.id}/{$kbcat.urlfriendlyname}{else}knowledgebase.php?action=displaycat&amp;catid={$kbcat.id}{/if}">{$kbcat.name}</a></strong> ({$kbcat.numarticles})<br />
      {$kbcat.description} </td>
    {if $num is div by 3} </tr>
  <tr> {/if}
    {/foreach} </tr>
</table>
<br />
<h2>{$LANG.knowledgebasepopular}</h2>
<table width="100%">
    {foreach key=num item=kbarticle from=$kbmostviews}
    <div class="kbarticle">
        <img src="images/article.gif" class="absmiddle" border="0" alt="Article" /> <strong><a href="{if $seofriendlyurls}knowledgebase/{$kbarticle.id}/{$kbarticle.urlfriendlytitle}.html{else}knowledgebase.php?action=displayarticle&amp;id={$kbarticle.id}{/if}">{$kbarticle.title}</a></strong><br />
        {$kbarticle.article|truncate:150:"..."}<br />
        <span class="kbviews">{$LANG.knowledgebaseviews}: {$kbarticle.views}</span>
    </div>
    {/foreach}
</table>
<br />