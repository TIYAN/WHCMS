{if $success}

  <div class="successbox">
    {$LANG.pwresetvalidationsent}
  </div>

  <p>{$LANG.pwresetvalidationcheckemail}

{else}

<p>{$LANG.pwresetdesc}</p>

{if $errormessage}
<div class="errorbox">{$errormessage}</div>
{/if}

<form method="post" action="pwreset.php">
<input type="hidden" name="action" value="reset" />

  <p align="center">{$LANG.loginemail}:
    <input type="text" name="email" size="50" value="{$email}">
  </p>

  {if $securityquestion}
    <p>{$LANG.pwresetsecurityquestionrequired}</p>
    <p align="center"><strong>{$securityquestion}</strong></p>
    <p align="center">{$LANG.clientareasecurityanswer}:
      <input type="text" name="answer" size="30" value="{$answer}">
    </p>
  {/if}

  <p align="center">
    <input type="submit" value="{$LANG.pwresetsubmit}" class="buttongo" />
  </p>

</form>

{/if}