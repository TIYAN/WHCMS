{if $errormessage}<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div><br />{/if}

<p><b>{$LANG.sslcertinfo}</b></p>

<table cellspacing="1" cellpadding="0" class="frame"><tr><td>
<table width="100%" cellpadding="2">
<tr><td width="120" class="fieldarea">{$LANG.sslorderdate}</td><td>{$date}</td></tr>
<tr><td class="fieldarea">{$LANG.sslcerttype}</td><td>{$certtype}</td></tr>
<tr><td class="fieldarea">{$LANG.sslstatus}</td><td>{$status}</td></tr>
{foreach from=$displaydata key=displaydataname item=displaydatavalue}
<tr><td class="fieldarea">{$displaydataname}</td><td>{$displaydatavalue}</td></tr>
{/foreach}
</table>
</td></tr></table>

<form name="submitticket" method="post" action="{$smarty.server.PHP_SELF}?cert={$cert}&step=3">

<p><b>{$LANG.sslcertapproveremail}</b></p>

<p>{$LANG.sslcertapproveremaildetails}</p>

<p>{foreach from=$approveremails item=approveremail key=num}
<input type="radio" name="approveremail" value="{$approveremail}"{if $num eq 0} checked{/if} /> {$approveremail}<br />
{/foreach}</p>

<p align="center"><input type="submit" value="{$LANG.ordercontinuebutton}" class="buttongo" /></p>

</form>