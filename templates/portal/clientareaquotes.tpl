<h2 >{$LANG.quotestitle}</h2>

{foreach from=$quotes key=num item=quote}

<div class="quotecontainer" onclick="window.location='dl.php?type=q&id={$quote.id}'">
    <div class="quoteid">
        #{$quote.id}
    </div>
    <div class="quoteleft">
        <div class="subject">{$quote.subject}</div>
        {$LANG.quotedatecreated}: {$quote.datecreated} &nbsp;&nbsp;&nbsp; {$LANG.quotevaliduntil}: {$quote.validuntil}
    </div>
    <div class="quoteright">
        <div class="stage">[ {$quote.stage} ]</div>
        <a href="viewquote.php?id={$quote.id}">{$LANG.quoteview}</a>&nbsp;&nbsp;&nbsp;<a href="dl.php?type=q&id={$quote.id}"><img src="images/pdf.png" align="absmiddle" border="0" /> {$LANG.quotedownload}</a>
    </div>
    <div class="clear"></div>
</div>

{foreachelse}

<br />
<p align="center">{$LANG.noquotes}</p>

{/foreach}

<br /><br />