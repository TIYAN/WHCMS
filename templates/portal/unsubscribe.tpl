<br />

{if $successful}
<div class="successbox">
    {$LANG.unsubscribesuccess}
</div>
<br />
<p align="center">{$LANG.newsletterremoved}</p>
{/if}

{if $errormessage}
<div class="errorbox">
    {$LANG.erroroccured}
</div>
<br />
<p align="center">{$errormessage}</p>
{/if}

<p align="center">{$LANG.newsletterresubscribe|sprintf2:'<a href="clientarea.php?action=details">':'</a>'}</p>

<br /><br /><br /><br />