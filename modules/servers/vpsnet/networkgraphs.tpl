<p class="heading2">{$LANG.vpsnetnetworkgraphs}</p>

<p><b>{$LANG.vpsnethourly}</b></p>

<p align="center"><img src="modules/servers/vpsnet/showgraph.php?serviceid={$serviceid}&graph=network&period=hourly"></p>

<p><b>{$LANG.vpsnetdaily}</b></p>

<p align="center"><img src="modules/servers/vpsnet/showgraph.php?serviceid={$serviceid}&graph=network&period=daily"></p>

<p><b>{$LANG.vpsnetweekly}</b></p>

<p align="center"><img src="modules/servers/vpsnet/showgraph.php?serviceid={$serviceid}&graph=network&period=weekly"></p>

<p><b>{$LANG.vpsnetmonthly}</b></p>

<p align="center"><img src="modules/servers/vpsnet/showgraph.php?serviceid={$serviceid}&graph=network&period=monthly"></p>

<br /><br />

<form method="post" action="clientarea.php?action=productdetails">
<input type="hidden" name="id" value="{$serviceid}" />
<p align="center"><input type="submit" value="{$LANG.clientareabacklink}" class="button" /></p>
</form>