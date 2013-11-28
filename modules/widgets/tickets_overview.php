<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function widget_supporttickets_overview($vars) {
    global $_ADMINLANG;

    $title = "Support Tickets Overview";

    $activestatuses = $replystatuses = array();
    $result = select_query("tblticketstatuses","title,showactive,showawaiting","showactive=1");
    while ($data = mysql_fetch_array($result)) {
        if ($data['showactive']) $activestatuses[] = db_escape_string($data['title']);
        if ($data['showawaiting']) $replystatuses[] = db_escape_string($data['title']);
    }

    $ticketcount = 0;

    $chartdata = array();
    $query = "SELECT name,(SELECT COUNT(*) FROM tbltickets WHERE tbltickets.did=tblticketdepartments.id AND tbltickets.status IN (".db_build_in_array($replystatuses).")) FROM tblticketdepartments ORDER BY `order` ASC";
    $result = full_query($query);
    while ($data = mysql_fetch_array($result)) {
        $chartdata[] = "['".addslashes($data[0])."',".$data[1]."]";
        $ticketcount += $data[1];
    }
    $chartdata = implode(',',$chartdata);

    $chartdata2 = array();
    $query = "SELECT tblticketstatuses.title,(SELECT COUNT(*) FROM tbltickets WHERE tbltickets.status=tblticketstatuses.title) FROM tblticketstatuses WHERE showawaiting=1 ORDER BY sortorder ASC";
    $result = full_query($query);
    while ($data = mysql_fetch_array($result)) {
        $chartdata2[] = "['".$data[0]."',".$data[1]."]";
        $ticketcount += $data[1];
    }
    $chartdata2 = implode(',',$chartdata2);

    if (!$ticketcount) $content = '<br /><div align="center">There are <strong>0</strong> Tickets Currently Awaiting a Reply</div><br />';
    else $content = <<<EOF
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawTicketChart1);
      function drawTicketChart1() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Department');
        data.addColumn('number', 'Ticket Count');
        data.addRows([
          $chartdata
        ]);

        var options = {
          chartArea: {left:0,top:20,width:"100%",height:"160"},
          title: 'Awaiting Reply by Department'
        };

        var chart = new google.visualization.PieChart(document.getElementById('ticketsoverview1'));
        chart.draw(data, options);
      }
      google.setOnLoadCallback(drawTicketChart2);
      function drawTicketChart2() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Status');
        data.addColumn('number', 'Ticket Count');
        data.addRows([
          $chartdata2
        ]);

        var options = {
          chartArea: {left:0,top:20,width:"100%",height:"160"},
          title: 'Awaiting Reply by Status'
        };

        var chart = new google.visualization.PieChart(document.getElementById('ticketsoverview2'));
        chart.draw(data, options);
      }

    </script>

    <div id="ticketsoverview1" style="float:left;width: 50%; height: 200px;"></div>
    <div id="ticketsoverview2" style="float:right;width: 50%; height: 200px;"></div>

EOF;

    return array('title'=>$title,'content'=>$content);

}

add_hook("AdminHomeWidgets",1,"widget_supporttickets_overview");

?>