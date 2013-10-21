{literal}
<script>
function getStats(num) {
    jQuery.post('serverstatus.php', 'getstats=1&num='+num, function(data) {
        jQuery("#load"+num).html(data.load);
        jQuery("#uptime"+num).html(data.uptime);
    },'json');
}
function checkPort(num,port) {
    jQuery.post('serverstatus.php', 'ping=1&num='+num+'&port='+port, function(data) {
        jQuery("#port"+port+"_"+num).html(data);
    });
}
</script>
{/literal}

<p>{$LANG.serverstatusheadingtext}</p>

<table class="clientareatable" align="center" cellspacing="1">
    <tr class="clientareatableheading">
        <td>{$LANG.servername}</td>
        <td>HTTP</td>
        <td>FTP</td>
        <td>POP3</td>
        <td>{$LANG.serverstatusphpinfo}</td>
        <td>{$LANG.serverstatusserverload}</td>
        <td>{$LANG.serverstatusuptime}</td>
    </tr>
{foreach key=num item=server from=$servers}
    <tr class="clientareatableactive">
        <td>{$server.name}</td>
        <td id="port80_{$num}"><img src="images/loadingsml.gif" alt="{$LANG.loading}" /></td>
        <td id="port21_{$num}"><img src="images/loadingsml.gif" alt="{$LANG.loading}" /></td>
        <td id="port110_{$num}"><img src="images/loadingsml.gif" alt="{$LANG.loading}" /></td>
        <td><a href="{$server.phpinfourl}" target="_blank">{$LANG.serverstatusphpinfo}</a></td>
        <td id="load{$num}"><img src="images/loadingsml.gif" alt="{$LANG.loading}" /></td>
        <td id="uptime{$num}"><img src="images/loadingsml.gif" alt="{$LANG.loading}" /><script> checkPort({$num},80); checkPort({$num},21); checkPort({$num},110); getStats({$num}); </script></td>
    </tr>
{foreachelse}
    <tr class="clientareatableactive">
        <td colspan="7">{$LANG.serverstatusnoservers}</td>
    </tr>
{/foreach}
</table>