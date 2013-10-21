<p>{$LANG.downloadsintrotext}</p>
<h2>{$LANG.downloadscategories}</h2>
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr> {foreach key=num item=dlcat from=$dlcats}
    <td width="33%" align="left" valign="top"><strong><img src="images/folder.gif" border="0" class="absmiddle" alt="folder" />&nbsp;<a href="{if $seofriendlyurls}downloads/{$dlcat.id}/{$dlcat.urlfriendlyname}{else}downloads.php?action=displaycat&amp;catid={$dlcat.id}{/if}">{$dlcat.name}</a></strong> ({$dlcat.numarticles})<br />
      {$dlcat.description} </td>
    {if $num is div by 3} </tr>
  <tr> {/if}
    {/foreach} </tr>
</table>
<br />
<table width="100%" border="0" cellpadding="0" cellspacing="10">
  <tr>
    <td width="50%" align="left" valign="top"><h3>{$LANG.downloadspopular}</h3>
      <table width="100%" border="0" cellpadding="10" cellspacing="0">
        {foreach key=num item=download from=$mostdownloads}
        <tr>
          <td><p>{$download.type} <a href="{$download.link}"><strong>{$download.title}</strong></a><br />
              {if $download.clientsonly}<em>Login Required</em><br />
              {/if}{$download.description}<br />
              <span style="color:#A8A8A8;font-size:11px;">{$LANG.downloadsfilesize}: {$download.filesize}</span><br />
              <br />
          </p></td>
        </tr>
        {/foreach}
      </table></td>
    <td width="50%" align="left" valign="top"><form method="post" action="downloads.php?action=search">
        <h3>{$LANG.knowledgebasesearch}</h3>
        <p align="center">
          <input type="text" name="search" size="25" /> 
          <input type="submit" value="{$LANG.knowledgebasesearch}" />
        </p>
      </form></td>
  </tr>
</table><br />