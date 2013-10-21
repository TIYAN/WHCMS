<p>{$LANG.flashtutorialsheadertext}</p>

<ul>
{foreach from=$tutorials item=tutorial}
    <li><a href="#" onclick="window.open('modules/tutorials/{$tutorial.filename}','','width=900,height=500');return false">{$tutorial.name}</li>
{/foreach}
</ul>