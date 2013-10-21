<p class="heading2">{$LANG.kbsuggestions}</p>

<p>{$LANG.kbsuggestionsexplanation}</p>

<p>{foreach from=$kbarticles item=kbarticle}
<img src="images/article.gif" align="middle" alt="" /> <a href="knowledgebase.php?action=displayarticle&id={$kbarticle.id}" target="_blank">{$kbarticle.title}</a> - {$kbarticle.article}...<br>
{/foreach}</p>