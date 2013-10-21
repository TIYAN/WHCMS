<p>{$LANG.downloadsintrotext}</p>

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

<br />

<table width="100%" cellspacing="0" cellpadding="0"><tr><td width="50%" valign="top">

<p class="heading2">{$LANG.downloadspopular}</p>

<table width="100%">
{foreach key=num item=download from=$mostdownloads}
<tr><td>
<p>{$download.type} <a href="{$download.link}"><strong>{$download.title}</strong></a><br />{if $download.clientsonly}<i>Login Required</i><br />{/if}{$download.description}<br /><font style="color:#A8A8A8;font-size:10px;">{$LANG.downloadsfilesize}: {$download.filesize}</font><br /><br /></p>
</td></tr>
{/foreach}
</table>

</td><td width="50%">

<form method="post" action="downloads.php?action=search">
<p align="center" class="heading2">{$LANG.knowledgebasesearch}</p><p align="center"><input type="text" name="search" size="25" /><br /><br /><input type="submit" value="{$LANG.knowledgebasesearch}" class="button" /></p>
</form>

</td></tr></table>