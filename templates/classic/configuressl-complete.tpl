{if $errormessage}

<div class="errorbox">{$errormessage|replace:'<li>':' &nbsp;#&nbsp; '} &nbsp;#&nbsp; </div>

{else}

<p><b>{$LANG.sslconfigcomplete}</b></p>

<p>{$LANG.sslconfigcompletedetails}</p>

<form method="post" action="clientarea.php?action=productdetails">
<input type="hidden" name="id" value="{$serviceid}" />
<p align="center"><input type="submit" value="{$LANG.invoicesbacktoclientarea}" /></p>
 </form>

{/if}