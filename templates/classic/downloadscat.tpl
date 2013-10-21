<p>{$LANG.downloadsintrotext}</p>

{if $dlcats}

<p class="heading2">{$LANG.downloadscategories}</p>

<table width="100%">
<tr>
{foreach key=num item=dlcat from=$dlcats}
<td width="33%" valign="top"><strong><a href="{if $seofriendlyurls}downloads/{$dlcat.id}/{$dlcat.urlfriendlyname}{else}downloads.php?action=displaycat&amp;catid={$dlcat.id}{/if}"><img src="images/folder.gif" border="0" align="middle" alt="" />&nbsp;{$dlcat.name}</a></strong> ({$dlcat.numarticles})<br />
{$dlcat.description}
</td>
{if $num is div by 3}
</tr>
<tr>
{/if}
{/foreach}
</tr>
</table>

{/if}

{if $downloads}

<p class="heading2">{$LANG.downloadsfiles}</p>

<table width="100%">
{foreach key=num item=download from=$downloads}
<tr><td>
<p>{$download.type} <a href="{$download.link}"><strong>{$download.title}</strong></a><br />{if $download.clientsonly}<i>Login Required</i><br />{/if}{$download.description}<br /><font style="color:#A8A8A8;font-size:10px;">{$LANG.downloadsfilesize}: {$download.filesize}</font><br /><br /></p>
</td></tr>
{/foreach}
</table>

{else}

<p align="center"><strong>{$LANG.downloadsnone}</strong></p>

{/if}