{if $error}

<p>{$LANG.supportticketinvalid}</p>

{elseif $stillopen}

<br /><br />
<div class="errorbox">
    {$LANG.erroroccured}
</div>
<br />
<p align="center">{$LANG.feedbackclosed}</p>
<br /><br />

<p class="textcenter"><input type="button" value="Return to Client Area" onclick="window.location='clientarea.php'" class="btn" /></p>

<br /><br /><br /><br /><br />

{elseif $feedbackdone}

<br /><br />

<div class="successbox">
    {$LANG.feedbackprovided}
</div>
<br />
<p align="center">{$LANG.feedbackthankyou}</p>

<br /><br />

<p class="textcenter"><input type="button" value="{$LANG.returnclient}" onclick="window.location='clientarea.php'" class="btn" /></p>

<br /><br /><br /><br /><br />

{elseif $success}

<br /><br />

<div class="successbox">
    {$LANG.feedbackreceived}
</div>
<br />
<p align="center">{$LANG.feedbackthankyou}</p>

<br /><br />

<p class="textcenter"><input type="button" value="{$LANG.returnclient}" onclick="window.location='clientarea.php'" class="btn" /></p>

<br /><br /><br /><br /><br />

{else}

{if $errormessage}
<div class="errorbox">
    {$LANG.clientareaerrors}
</div>
<br />
<p align="center">{$errormessage}</p>
{/if}

<p>{$LANG.feedbackdesc}</p>

<div class="errorbox">[ <a href="viewticket.php?tid={$tid}&c={$c}">{$LANG.feedbackclickreview}</a> ]</div>

<br />

<table cellspacing="1" cellpadding="0" class="frame">
    <tr>
        <td><table width="100%" cellpadding="10">
          <tr>
            <td width="150" class="fieldarea">{$LANG.feedbackopenedat}:</td>
            <td><strong>{$opened}</strong></td>
          </tr>
          <tr>
            <td width="150" class="fieldarea">{$LANG.feedbacklastreplied}:</td>
            <td><strong>{$lastreply}</strong></td>
          </tr>
          <tr>
            <td width="150" class="fieldarea">{$LANG.feedbackstaffinvolved}:</td>
            <td><strong>{foreach from=$staffinvolved item=staff}{$staff}, {foreachelse}{$LANG.none}{/foreach}</strong></td>
          </tr>
          <tr>
            <td width="150" class="fieldarea">{$LANG.feedbacktotalduration}:</td>
            <td><strong>{$duration}</strong></td>
          </tr>
      </table></td>
    </tr>
</table>

<form method="post" action="{$smarty.server.PHP_SELF}?tid={$tid}&c={$c}&feedback=1">
<input type="hidden" name="validate" value="true" />

{foreach from=$staffinvolved key=staffid item=staff}

<div class="ticketfeedbackstaffcont">

    <p>{$LANG.feedbackpleaserate1} <strong>{$staff}</strong> {$LANG.feedbackhandled}:</p>

    <br />

    <div class="ticketfeedbackrating">
    <div class="rate" style="color:#efefef;">{$LANG.feedbackworst}</div>
    <div class="rate">1</div>
    <div class="rate">2</div>
    <div class="rate">3</div>
    <div class="rate">4</div>
    <div class="rate">5</div>
    <div class="rate">6</div>
    <div class="rate">7</div>
    <div class="rate">8</div>
    <div class="rate">9</div>
    <div class="rate">10</div>
    <div class="rate" style="color:#efefef;">{$LANG.feedbackbest}</div>
    </div>
    <div class="clear"></div>
    <div class="ticketfeedbackrating">
    <div class="rate">{$LANG.feedbackworst}</div>
    {foreach from=$ratings item=rating}
    <div class="rate"><input type="radio" name="rate[{$staffid}]" value="{$rating}"{if $rate.$staffid eq $rating} checked{/if} /></div>
    {/foreach}
    <div class="rate">{$LANG.feedbackbest}</div>
    </div>
    <div class="clear"></div>

    <br />

    <p>{$LANG.feedbackpleasecomment1} <strong>{$staff}</strong> {$LANG.feedbackhandled}.</p>

    <p class="textcenter"><textarea name="comments[{$staffid}]" rows="4" style="width:80%;">{$comments.$staffid}</textarea></p>

</div>

{/foreach}

<br />

<div class="ticketfeedbackstaffcont">
    <p>{$LANG.feedbackimprove}</p>
    <p><textarea name="comments[generic]" rows="4" style="width:80%;">{$comments.generic}</textarea></p>
</div>

<p class="textcenter"></p>

  <p align="center">
    <input type="submit" value="{$LANG.clientareasavechanges}" class="button" />
    <input type="reset" value="{$LANG.cancel}" class="button" />
  </p>

</form>

{literal}
<style>
.ticketfeedbackstaffcont {
    margin: 10px auto;
    padding: 15px;
    text-align: center;
    background-color: #efefef;
    -moz-border-radius: 6px;
    -webkit-border-radius: 6px;
    -o-border-radius: 6px;
    border-radius: 6px;
}
.ticketfeedbackrating {
    padding: 0;
}
.ticketfeedbackrating .rate {
    float: left;
    padding: 0 10px;
    min-width: 30px;
    text-align: center;
}

</style>
{/literal}

{/if}