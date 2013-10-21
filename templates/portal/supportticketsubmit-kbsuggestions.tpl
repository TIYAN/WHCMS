<h2>{$LANG.kbsuggestions}</h2>
<p>{$LANG.kbsuggestionsexplanation}</p>
<p>{foreach from=$kbarticles item=kbarticle} <img src="images/article.gif" class="absmiddle" border="0" alt="" /> <a href="knowledgebase.php?action=displayarticle&id={$kbarticle.id}" target="_blank">{$kbarticle.title}</a> - {$kbarticle.article}...<br>
  {/foreach}</p>