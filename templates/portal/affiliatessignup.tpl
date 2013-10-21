{if $affiliatesystemenabled}
<p>{$LANG.affiliatesintrotext}</p>
<ul>
  <li>{$LANG.affiliatesbullet1} {$bonusdeposit}</li>
  <li>{$LANG.affiliatesearn} <strong>{$payoutpercentage}</strong> {$LANG.affiliatesbullet2}</li>
</ul>
<p>{$LANG.affiliatesfootertext}</p>
<br />
<form method="post" action="affiliates.php">
<input type="hidden" name="activate" value="true" />
<p align="center"><input type="submit" value="{$LANG.affiliatesactivate}" /></p>
</form>
{else}
<p>{$LANG.affiliatesdisabled}</p>
{/if}<br />