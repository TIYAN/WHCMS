{php}
	$serviceid = $this->_tpl_vars['id'];
    $servicedata = get_query_vals("tblhosting","",array("id"=>$serviceid));
    $packagedata = get_query_vals("tblproducts","",array("id"=>$servicedata['packageid']));
    $serverdata = get_query_vals("tblservers","",array("id"=>$servicedata['server']));
	$params = array();
    
    $params['serviceid'] = $serviceid;
    $params['userid'] = $servicedata['userid'];
    $params['domain'] = $servicedata['domain'];
    $params['username'] = $servicedata['username'];
    $params['password'] = decrypt($servicedata['password']);
    $params['packageid'] = $servicedata['packageid'];
    $params['server'] = $servicedata['server'];
    $params['dedicatedip'] = $servicedata['dedicatedip'];
    $params['assignedips'] = $servicedata['assignedips'];
    $params['domainstatus'] = $servicedata['domainstatus'];
    
    $params['moduletype'] = $packagedata['moduletype'];
    $params['configoption1'] = $packagedata['configoption1'];
    $params['configoption2'] = $packagedata['configoption2'];
    $params['configoption3'] = $packagedata['configoption3'];
    $params['configoption4'] = $packagedata['configoption4'];
    $params['configoption5'] = $packagedata['configoption5'];
    
    $params['serverusername'] = $serverdata['username'];
    $params['serverpassword'] = decrypt($serverdata['password']);
    $params['serveraccesshash'] = $serverdata['accesshash'];
    
	$vpsinfo = '';
    $netid = get_query_val("mod_vpsnet","value",array("relid"=>$params["serviceid"],"setting"=>"netid"));
    
    if($netid){
    
    $vpsinfo .= '<style>
#vpsnetcont {
    margin: 10px;
    padding: 10px;
    background-color: #fff;
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
    -o-border-radius: 10px;
    border-radius: 10px;
}
#vpsnetcont table {
    width: 100%;
}
#vpsnetcont table tr th {
	padding: 4px;
    background-color: #1A4D80;
    color: #fff;
	font-weight: bold;
	text-align: center;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    -o-border-radius: 3px;
    border-radius: 3px;
}
#vpsnetcont table tr td {
    padding: 4px;
    border-bottom: 1px solid #efefef;
}
#vpsnetcont table tr td.fieldlabel {
    width: 175px;
    text-align: right;
    font-weight: bold;
    background-color: #efefef;
}
#vpsnetcont .tools {
    padding: 10px 0 0 15px;
}
</style>
';

    if ($_REQUEST['bwgraphs']) {

        $rtn = vpsnet_call($params,'network_graph',$netid,'GET','virtual_machines','period=hourly');
        $data = $rtn['response'];

        $datatable = array();
        $datatable[] = '["Time","Upload","Download"]';
        foreach ($data AS $d) $datatable[] = '["'.date("Y-m-d H:i",strtotime($d['created_at'])).'",'.round(($d['data_received']/(1024*1024)),2).','.round(($d['data_sent']/(1024*1024)),2).']';

        $vpsinfo .= '<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          '.implode(',',$datatable).'
        ]);

        var options = {
          title: "Network Usage - Hourly",
          hAxis: {title: "Time Period"},
          vAxis: {title: "Bandwidth (GB)"},
          legend: {position: "in"}
        };

        var chart = new google.visualization.AreaChart(document.getElementById("bwchart"));
        chart.draw(data, options);
      }
    </script>
    <div id="vpsnetcont">
    <div id="bwchart" style="width: 100%; height: 400px;"></div>
    </div>';

        echo $vpsinfo;

    }

    if ($_REQUEST['managebackups']) {

        $rtn = vpsnet_call($params,'backups',$netid,'GET');

        $vpsinfo .= '<div id="vpsnetcont">
The list below shows all the backups for your virtual machine, along with the last time each of these backups was run.<br /><br />
<table cellspacing="1">
<tr><th>Type</th><th>State</th><th>Date/Time</th><th>Size</th><th>Restore</th><th>Delete</th></tr>';
        foreach ($rtn['response'] AS $backup) {
            $lastupdated = $backup['updated_at'];
            $lastupdated = strtotime($lastupdated);
            $lastupdated = date("F dS, Y H:i",$lastupdated);
            $vpsinfo .= '
<tr><td>'.ucfirst($backup['backup_type']).'</td><td>'.(($backup['built'])?'Completed':'Pending').'</td><td>'.$lastupdated.'</td><td>'.(($backup['built'])?round(($backup['backup_size']/1024),1).' MB':'Not built yet').'</td><td><a href="clientarea.php?action=productdetails&modop=custom&a=restorebackup&id='.$params['serviceid'].'&bid='.$backup['id'].'" onclick="if (confirm(\'Are you sure you wish to restore this backup?\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/backup.png" align="absmiddle" /></a></td><td><a href="clientarea.php?action=productdetails&modop=custom&a=deletebackup&id='.$params['serviceid'].'&bid='.$backup['id'].'" onclick="if (confirm(\'Are you sure you wish to delete this backup?\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/deletebackup.png" align="absmiddle" /></a></td></tr>';
        }
        $vpsinfo .= '
</table>
<div class="tools">
<a href="clientarea.php?action=productdetails&modop=custom&a=snapshotbackup&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you want to create a new snapshot?\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/backup.png" align="absmiddle" /> Create a new Snapshot</a>&nbsp;&nbsp;
<a href="clientarea.php?action=productdetails&rsyncbackups=1&id='.$params['serviceid'].'"><img src="./modules/servers/vpsnet/img/restore.png" align="absmiddle" /> Rsync Backups</a>
</div>
</div>';

        echo $vpsinfo;

    }

    if ($_REQUEST['rsyncbackups']) {

        $rtn = vpsnet_call($params,'backups/rsync_backup',$netid,'GET');

        $data = $rtn['response'];

        $vpsinfo .= '<div id="vpsnetcont">
<table cellspacing="1">
<tr><td class="fieldlabel">Username</td><td>'.$data['username'].'</td></tr>
<tr><td class="fieldlabel">Password</td><td>'.$data['password'].'</td></tr>
<tr><td class="fieldlabel">Quota</td><td>'.$data['quota'].'</td></tr>
</table>
<div class="tools">
<a href="clientarea.php?action=productdetails&modop=custom&a=snapshotbackup&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you want to create a new snapshot?\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/backup.png" align="absmiddle" /> Create a new Snapshot</a>&nbsp;&nbsp;
<a href="clientarea.php?action=productdetails&rsyncbackups=1&id='.$params['serviceid'].'"><img src="./modules/servers/vpsnet/img/backup.png" align="absmiddle" /> Rsync Backups</a>
</div>
</div>';

		echo $vpsinfo;

    }

    $rtn = vpsnet_call($params,'',$netid,'GET');
    
    if (!$rtn['success']) return false;

    $data = $rtn['response']['virtual_machine'];

	$running = $data['running'];
	$pending = $data['power_action_pending'];
	$runningstatus = ($running) ? '<img src="./modules/servers/vpsnet/img/running.png" align="absmiddle" /> '.$_LANG['vpsnetrunning'] : '<img src="./modules/servers/vpsnet/img/notrunning.png" align="absmiddle" /> '.$_LANG['vpsnetnotrunning'];
	if ($pending) $runningstatus = '<img src="./modules/servers/vpsnet/img/notrunning.png" align="absmiddle" /> '.$_LANG['vpsnetpowercycling'];

	$bwused = $data['bandwidth_used'];
	$bwused = $bwused/1024/1024;
	$bwused = round($bwused,2)."MB";

	$cloudid = $data['cloud_id'];
	$templateid = $data['system_template_id'];

    $clouddata = vpsnet_call($params,'',$cloudid,'GET','clouds');
    foreach($clouddata['response']['system_templates'] as $templatearr){
    	$availtemplates[$templatearr['id']] = $templatearr['label'];
    }
    
    $templatelabel = $availtemplates[$templateid];
    $cloudname = $clouddata['response']['label'];

    $vpsinfo .= '<div id="vpsnetcont">
<table cellspacing="1">
<tr><td class="fieldlabel">Hostname</td><td>'.$data['hostname'].'</td><td class="fieldlabel">Domain Name</td><td>'.$data['domain_name'].'</td></tr>
<tr><td class="fieldlabel">Nodes</td><td>'.$data['slices_count'].'</td><td class="fieldlabel">Cloud</td><td>'.$cloudname.'</td></tr>
<tr><td class="fieldlabel">Initial Root Password</td><td>'.$data['password'].'</td><td class="fieldlabel">Backups Enabled</td><td>'.(($data['backups_enabled'])?'<img src="./modules/servers/vpsnet/img/tick.png" align="absmiddle" /> Yes':'<img src="./modules/servers/vpsnet/img/cross.png" align="absmiddle" /> No').'</td></tr>
<tr><td class="fieldlabel">Status</td><td>'.$runningstatus.'</td><td class="fieldlabel">IP Address</td><td>'.$data['primary_ip_address']['ip_address']['ip_address'].'</td></tr>
<tr><td class="fieldlabel">Monthly Bandwidth Used</td><td>'.$bwused.'</td><td class="fieldlabel">Deployed Storage</td><td>'.$data['deployed_disk_size'].'</td></tr>
<tr><td class="fieldlabel">Template</td><td>'.$templatelabel.'</td><td class="fieldlabel">Licenses</td><td>None</td></tr>
</table>
<div class="tools">';
if ($data['power_action_pending']) $vpsinfo .= '<img src="./modules/servers/vpsnet/img/running.png" align="absmiddle" /> This VPS is currently running a task. Power Management Options Not Available Until Complete.';
else {
if ($running) $vpsinfo .= '
<a href="clientarea.php?action=productdetails&modop=custom&a=shutdown&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you wish to shutdown this VPS?\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/shutdown.png" align="absmiddle" /> Shutdown</a>&nbsp;&nbsp;
<a href="clientarea.php?action=productdetails&modop=custom&a=poweroff&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you wish to force power off this VPS?\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/poweroff.png" align="absmiddle" /> Force Power Off</a>&nbsp;&nbsp;
<a href="clientarea.php?action=productdetails&modop=custom&a=reboot&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you wish to reboot this VPS?\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/reboot.png" align="absmiddle" /> Graceful Reboot</a>&nbsp;&nbsp;
<a href="clientarea.php?action=productdetails&modop=custom&a=recover&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you wish to reboot this VPS in recovery mode? Please note: in recovery mode the login is (root) and the password is (recovery).\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/recovery.png" align="absmiddle" /> Reboot in Recovery</a>&nbsp;&nbsp;
<a href="clientarea.php?action=productdetails&modop=custom&a=rebuild&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you want to rebuilt network for this VPS? Your virtual machine will be rebooted and the network interfaces configuration file on this virtual machine will be regenerated.\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/restart.png" align="absmiddle" /> Rebuild Network</a>
';
else $vpsinfo .= '
<a href="clientarea.php?action=productdetails&modop=custom&a=poweron&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you wish to start this VPS?\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/startup.png" align="absmiddle" /> Startup</a>&nbsp;&nbsp;
<a href="clientarea.php?action=productdetails&modop=custom&a=recover&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you wish to start this VPS in recovery mode? Please note: in recovery mode the login is (root) and the password is (recovery).\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/recovery.png" align="absmiddle" /> Startup in Recovery</a>&nbsp;&nbsp;
<a href="clientarea.php?action=productdetails&modop=custom&a=rebuild&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you want to rebuilt network for this VPS? Your virtual machine will be rebooted and the network interfaces configuration file on this virtual machine will be regenerated.\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/restart.png" align="absmiddle" /> Rebuild Network</a>
<a href="clientarea.php?action=productdetails&modop=custom&a=terminate&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you wish to delete this VPS? Please note: recovery is only possible for up to 12 hours after deletion, and only your last 3 deleted VPS\'s will be available for recovery.\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/delete.png" align="absmiddle" /> Delete VPS</a>&nbsp;&nbsp;
<a href="clientarea.php?action=productdetails&modop=custom&a=reinstall&id='.$params['serviceid'].'" onclick="if (confirm(\'Are you sure you want to re-install this VPS?\')) { return true; } return false;"><img src="./modules/servers/vpsnet/img/restart.png" align="absmiddle" /> Re-install VPS</a>
';
}
$vpsinfo .= '
</div>
</div>';

}
echo $vpsinfo;
{/php}