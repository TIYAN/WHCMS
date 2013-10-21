<p>{$LANG.downloadsintrotext}</p>
{if $dlcats}
<h2>{$LANG.downloadscategories}</h2>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr> {foreach key=num item=dlcat from=$dlcats}
    <td width="33%" align="left" valign="top"><img src="images/folder.gif" border="0" class="absmiddle" alt="folder" />&nbsp;<strong><a href="{if $seofriendlyurls}downloads/{$dlcat.id}/{$dlcat.urlfriendlyname}{else}downloads.php?action=displaycat&amp;catid={$dlcat.id}{/if}">{$dlcat.name}</a></strong> ({$dlcat.numarticles})<br />
      {$dlcat.description} </td>
    {if $num is div by 3} </tr>
  <tr> {/if}
    {/foreach} </tr>
</table>
{/if}

{if $downloads}
<h2>{$LANG.downloadsfiles}</h2>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  {foreach key=num item=download from=$downloads}
  <tr>
    <td><p>{$download.type} <a href="{$download.link}" title="{$download.title}"><strong>{$download.title}</strong></a><br />
        {if $download.clientsonly}<em>Login Required</em><br />
        {/if}{$download.description}<br />
        <span style="color:#A8A8A8;font-size:11px;">{$LANG.downloadsfilesize}: {$download.filesize}</span><br />
        <br />
    </p></td>
  </tr>
  {/foreach}
</table>
{else}
<p align="center"><strong>{$LANG.downloadsnone}</strong></p>
{/if}<br />