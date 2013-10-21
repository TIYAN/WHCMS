<div class="halfwidthcontainer">

{include file="$template/pageheader.tpl" title=$LANG.twofactorauth}

{if $newbackupcode}
<div class="successbox">
    {$LANG.twofabackupcodereset}
</div>
{elseif $incorrect}
<div class="errorbox">
    {$LANG.twofa2ndfactorincorrect}
</div>
{elseif $error}
<div class="errorbox">
    {$error}
</div>
{else}
<div class="successbox">
    {$LANG.twofa2ndfactorreq}
</div>
{/if}

<form method="post" action="{$systemsslurl}dologin.php" class="form-stacked" name="frmlogin">

<br />

{$challenge}

<br />

<div class="alert alert-block alert-info textcenter">
{$LANG.twofacantaccess2ndfactor} <a href="login.php?backupcode=1">{$LANG.twofaloginusingbackupcode}</a></p>
</div>

</form>

<br /><br /><br /><br />

</div>