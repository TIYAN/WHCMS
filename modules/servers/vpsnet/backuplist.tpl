<form action='clientarea.php?action=productdetails' method='POST'>
<input type='hidden' name='id' value='{$serviceid}' />
<input type='hidden' name='modop' value='custom' />
<input type='hidden' name='a' value='restorebackup' />
{foreach key=num item=option from=$options}
	<input type="radio" name="backupid" value="{$option.backupid}" />{$option.label}{if $option.abtype} - {$option.abtype}{/if} - {$option.lastupdated}<br />
{foreachelse}
	{$LANG.vpsnetnobackups}
{/foreach}
{if $option}<input type='submit' value='{$LANG.vpsrestorebackup}' class='button' /><br />
{$LANG.vpsrestorebackupwarning}{/if}
</form>