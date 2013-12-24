<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.14
 * @ Author   : MTIMER
 * @ Release on : 2013-11-28
 * @ Website  : http://www.mtimer.cn
 *
 **/

function calendar_core_calendar($vars) {
	$events = array();
	$result = select_query("tblcalendar", "", "start>=" . $vars['start'] . " AND end<=" . $vars['end']);

	while ($data = mysql_fetch_assoc($result)) {
		$events[] = array("id" => $data['id'], "title" => $data['title'], "start" => $data['start'], "end" => $data['end'], "allDay" => ($data['allday'] ? true : false), "editable" => true);
	}

	return $events;
}

function calendar_core_products($vars) {
	$events = array();
	$result = select_query("tblhosting", "tblhosting.id,tblhosting.domain,tblhosting.nextduedate,tblproducts.name", "domainstatus IN ('Active','Suspended') AND nextduedate BETWEEN '" . date("Y-m-d", $vars['start']) . "' AND '" . date("Y-m-d", $vars['end']) . "'", "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");

	while ($data = mysql_fetch_assoc($result)) {
		$events[] = array("id" => $data['id'], "title" => $data['name'] . ($data['domain'] ? " - " . $data['domain'] : ""), "start" => strtotime($data['nextduedate']) + 86400, "allDay" => true, "editable" => false, "url" => "clientshosting.php?id=" . $data['id']);
	}

	return $events;
}

function calendar_core_addons($vars) {
	$addons = array();
	$result = select_query("tbladdons", "id,name", "");

	while ($data = mysql_fetch_array($result)) {
		$addon_id = $data['id'];
		$addons[$addon_id] = $data['name'];
	}

	$events = array();
	$result = select_query("tblhostingaddons", "id,addonid,name,hostingid,nextduedate", "status IN ('Active','Suspended') AND nextduedate BETWEEN '" . date("Y-m-d", $vars['start']) . "' AND '" . date("Y-m-d", $vars['end']) . "'");

	while ($data = mysql_fetch_assoc($result)) {
		$name = (0 < strlen($data['name']) ? $data['name'] : $addons[$data['addonid']]);
		$events[] = array("id" => $data['id'], "title" => $name, "start" => strtotime($data['nextduedate']), "allDay" => true, "editable" => false, "url" => "clientsservices.php?id=" . $data['hostingid'] . "&aid=" . $data['id']);
	}

	return $events;
}

function calendar_core_domains($vars) {
	$events = array();
	$result = select_query("tbldomains", "", "status IN ('Active','Suspended') AND nextduedate BETWEEN '" . date("Y-m-d", $vars['start']) . "' AND '" . date("Y-m-d", $vars['end']) . "'");

	while ($data = mysql_fetch_assoc($result)) {
		$events[] = array("id" => $data['id'], "title" => "Domain Renewal - " . $data['domain'], "start" => strtotime($data['nextduedate']) + 86400, "allDay" => true, "editable" => false, "url" => "clientsdomains.php?id=" . $data['id']);
	}

	return $events;
}

function calendar_core_todoitems($vars) {
	$events = array();
	$result = select_query("tbltodolist", "", "duedate BETWEEN '" . date("Y-m-d", $vars['start']) . "' AND '" . date("Y-m-d", $vars['end']) . "'");

	while ($data = mysql_fetch_assoc($result)) {
		$events[] = array("id" => "td" . $data['id'], "title" => $data['title'], "start" => strtotime($data['duedate']), "allDay" => true, "editable" => true, "url" => "todolist.php?action=edit&id=" . $data['id']);
	}

	return $events;
}

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS_Admin("Calendar");
$aInt->title = $aInt->lang("utilities", "calendar");
$aInt->sidebar = "utilities";
$aInt->icon = "calendar";

if (($CONFIG['DateFormat'] == "DD/MM/YYYY" || $CONFIG['DateFormat'] == "DD.MM.YYYY") || $CONFIG['DateFormat'] == "DD-MM-YYYY") {
	$localdateformat = "dd/mm/yy";
}
else {
	if ($CONFIG['DateFormat'] == "MM/DD/YYYY") {
		$localdateformat = "mm/dd/yy";
	}
	else {
		if ($CONFIG['DateFormat'] == "YYYY/MM/DD" || $CONFIG['DateFormat'] == "YYYY-MM-DD") {
			$localdateformat = "yy/mm/dd";
		}
	}
}


if ($action == "fetch") {
	echo "<p align=\"center\"><b>Add New Event</b></p><p>Title<br /><input type=\"text\" name=\"title\" style=\"width:80%;\" /></p>
<p>Description<br /><input type=\"text\" name=\"desc\" style=\"width:90%;\" /></p>
<table>
	<tr>
		<td width=\"160\">Start Date/Time<br /><input type=\"text\" name=\"start\" class=\"datepick\" id=\"start\" value=\"" . fromMySQLDate(substr($ymd, 0, 4) . "-" . substr($ymd, 4, 2) . "-" . substr($ymd, 6, 2)) . " 00:00:00" . "\" style=\"width:145px;\" /></td>
		<td width=\"160\">End Date/Time<br /><input type=\"text\" name=\"end\" class=\"datepick\" id=\"end\" value=\"" . fromMySQLDate(substr($ymd, 0, 4) . "-" . substr($ymd, 4, 2) . "-" . substr($ymd, 6, 2)) . " 23:59:59\" disabled style=\"width:145px;\" /></td>
	</tr>
</table>
<p><label><input type=\"checkbox\" name=\"allday\" id=\"allday\" value=\"1\" checked /> All Day</label></p>
<p><label>Recur Every <input type=\"text\" style=\"width:25px;\" name=\"recurevery\" /></label> <select name=\"recurtype\"><option value=\"days\">Days</option><option value=\"weeks\">Weeks</option><option value=\"months\">Months</option><option value=\"years\">Years</option></select> for <label><input type=\"text\" style=\"width:25px;\" name=\"recurtimes\" />  times*</label></p>
<p>*0 = Unlimited</label></p>
<p align=\"center\"><input type=\"submit\" value=\"Save\" /> <input type=\"button\" value=\"Cancel\" onclick=\"jQuery('#caledit').fadeOut()\" /></p>";
	exit();
}


if ($action == "refresh") {
	wSetCookie("CalendarDisplayTypes", $displaytypes, time() + 86400 * 365);
	redir();
}


if ($action == "save") {
	check_token("WHMCS.admin.default");
	$start = toMySQLDate($start);
	$start = strtotime($start, time());
	$end = toMySQLDate($end);
	$end = ((!$allday && $end) ? strtotime($end, time()) : "");

	if ($id) {
		update_query("tblcalendar", array("title" => $title, "desc" => $desc, "start" => $start, "end" => $end, "allday" => $allday), array("id" => $id));
	}
	else {
		$neweventid = insert_query("tblcalendar", array("title" => $title, "desc" => $desc, "start" => $start, "end" => $end, "allday" => $allday));

		if ($recurevery && $recurtype) {
			if ($recurtimes == 0) {
				$recurtimes = 99;
				$recurtype = "years";
			}

			$i = 1;

			while ($i <= $recurtimes - 1) {
				$nexttime = ($nexttime ? strtotime("+" . $recurevery . " " . $recurtype, $nexttime) : $start);
				$rstart = strtotime(date("Ymd", strtotime("+" . $recurevery . " " . $recurtype, $nexttime)) . $starttime);
				$rend = ($endtime ? strtotime(date("Ymd", strtotime("+" . $recurevery . " " . $recurtype, $nexttime)) . $endtime) : "");
				insert_query("tblcalendar", array("title" => $title, "desc" => $desc, "start" => $rstart, "end" => $rend, "allday" => $allday, "recurid" => $neweventid));
				update_query("tblcalendar", array("recurid" => $neweventid), array("id" => $neweventid));
				++$i;
			}
		}
	}

	redir();
}


if ($action == "update") {
	check_token("WHMCS.admin.default");

	if ($type == "move") {
		$start = get_query_val("tblcalendar", "start", array("id" => $id));
		$start = $start + $days * (24 * 60 * 60) + $minutes * 60;
		$allday = ($allday == "true" ? "1" : "0");
		update_query("tblcalendar", array("start" => $start, "allday" => $allday), array("id" => $id));
	}
	else {
		if ($type == "resize") {
			$data = get_query_vals("tblcalendar", "start,end", array("id" => $id));
			$start = $data['start'];
			$end = $data['end'];

			if (!$end) {
				$end = $start;
			}

			$end = $end + $days * (24 * 60 * 60) + $minutes * 60;
			update_query("tblcalendar", array("end" => $end), array("id" => $id));
		}
	}

	exit();
}


if ($action == "delete") {
	check_token("WHMCS.admin.default");
	delete_query("tblcalendar", array("id" => $id));
	exit();
}


if ($action == "recurdelete") {
	check_token("WHMCS.admin.default");
	delete_query("tblcalendar", array("recurid" => $recurid));
	redir();
}

$caldisplaytypes = wGetCookie("CalendarDisplayTypes", 1);

if ($caldisplaytypes['events'] == "on") {
	add_hook("CalendarEvents", "-999", "calendar_core_calendar");
}


if ($caldisplaytypes['services'] == "on") {
	add_hook("CalendarEvents", "-998", "calendar_core_products");
}


if ($caldisplaytypes['addons'] == "on") {
	add_hook("CalendarEvents", "-997", "calendar_core_addons");
}


if ($caldisplaytypes['domains'] == "on") {
	add_hook("CalendarEvents", "-996", "calendar_core_domains");
}


if ($caldisplaytypes['todo'] == "on") {
	add_hook("CalendarEvents", "-995", "calendar_core_todoitems");
}

$calevents = array();
foreach ($hooks['CalendarEvents'] as $calfeed) {
	$calevents[] = $calfeed['hook_function'];
}


if ($_REQUEST['getcalfeed']) {
	$feed = $_REQUEST['feed'];
	$start = (int)$_REQUEST['start'];
	$end = (int)$_REQUEST['end'];

	if (in_array($feed, $calevents)) {
		$events = call_user_func($feed, array("start" => $start, "end" => $end));

		if (!is_array($events)) {
			$events = array();
		}

		echo json_encode($events);
	}

	exit();
}


if ($_REQUEST['editentry']) {
	$data = get_query_vals("tblcalendar", "", array("id" => $id));
	$starttime = date("Y-m-d", $data['start']);
	$endtime = ((!$data['allday'] && $data['end']) ? $data['end'] : "");
	$editcontent = array("defaultsdate" => date("Y, n, j", $data['start']), "defaultedate" => ($data['end'] ? date("Y, n, j", $data['end']) : date("Y, n, j", $data['start'])), "defaultsh" => date("H", $data['start']), "defaultsm" => date("i", $data['start']), "defaulteh" => date("H", $data['end']), "defaultem" => date("i", $data['end']), "html" => "<div align=\"center\"><b>Edit Event</b></div><input type=\"hidden\" name=\"id\" value=\"" . $data['id'] . "\" />
		<p>Title<br /><input type=\"text\" name=\"title\" style=\"width:80%;\" value=\"" . $data['title'] . "\" /></p>
		<p>Description<br /><input type=\"text\" name=\"desc\" style=\"width:90%;\" value=\"" . $data['desc'] . "\" /></p>
		<table>
			<tr>
				<td width=\"160\">Start Time<br /><input type=\"text\" name=\"start\" id=\"start\" value=\"" . fromMySQLDate(date("Y-m-d H:i", $data['start']), 1) . ":" . date("s", $data['start']) . "\" style=\"width:145px;\" /></td>
				<td width=\"160\">End Time<br /><input type=\"text\" name=\"end\" id=\"end\" value=\"" . ($endtime ? fromMySQLDate(date("Y-m-d H:i", $endtime), 1) . ":" . date("s", $endtime) : "") . "\" style=\"width:145px;\" /></td>
			</tr>
		</table>
		<p><label><input type=\"checkbox\" value=\"1\" name=\"allday\"" . ($data['allday'] ? " checked" : "") . " /> All Day</label>");

	if ($data['recurid']) {
		$editcontent->html .= "<label style=\"float:right;margin-right:9%;\"><a href=\"calendar.php?action=recurdelete&recurid=" . $data['recurid'] . "\">Delete Recurring Event</a></label>";
	}

	$editcontent->html .= "</p><div align=\"center\"><input type=\"submit\" value=\"Save\" /> <input type=\"button\" value=\"Delete\" onclick=\"deleteEntry('" . $data['id'] . "')\" /> <input type=\"button\" value=\"Cancel\" onclick=\"jQuery('#caledit').fadeOut()\" /></div>";
	echo json_encode($editcontent);
	exit();
}

ob_start();
$calcolors = array();
$calcolors[] = array("bg" => "3366CC", "text" => "ffffff");
$calcolors[] = array("bg" => "FBE983", "text" => "000000");
$calcolors[] = array("bg" => "F83A22", "text" => "ffffff");
$calcolors[] = array("bg" => "B3DC6C", "text" => "000000");
$calcolors[] = array("bg" => "CAD5D5", "text" => "000000");
$calcolors[] = array("bg" => "F83A22", "text" => "ffffff");
$calcolors[] = array("bg" => "B3DC6C", "text" => "000000");
$calcolors[] = array("bg" => "cc0000", "text" => "ffffff");
echo "
<link rel='stylesheet' type='text/css' href='../includes/jscript/css/fullcalendar.css' />
<link rel='stylesheet' type='text/css' href='../includes/jscript/css/fullcalendar.print.css' media='print' />
<link rel=\"stylesheet\" type=\"text/css\" href=\"../includes/jscript/css/jquery-ui-timepicker-addon.css\" />
";
echo "<s";
echo "cript type='text/javascript' src='../includes/jscript/fullcalendar.min.js'></script>
";
echo "<s";
echo "cript type=\"text/javascript\" src=\"../includes/jscript/jquery-ui-timepicker-addon.js\"></script>
";
echo "<s";
echo "cript type='text/javascript'>
$(document).ready(function() {
var date = new Date();
var d = date.getDate();
var m = date.getMonth();
var y = date.getFullYear();

$('#calendar').fullCalendar({

    header: {
	    left: 'prev,next today',
		center: 'title',
		right: 'month,agendaWeek,agendaDay'
	},

    buttonText: {
        today: 'Today',
        month: 'Month',
        week: 'We";
echo "ek',
        day: 'Day',
    },

    timeFormat: 'H:mm',

    dayClick: function(date, allDay, jsEvent, view) {
		var dateclicked = $.fullCalendar.formatDate(date, 'yyyyMMdd');
        var xpos = jsEvent.pageX;
        if (xpos>($(window).width()-400)) xpos = xpos-350;
        $(\"#caledit\").css(\"top\",jsEvent.pageY);
        $(\"#caledit\").css(\"left\",xpos);
        $(\"#caledit\").load(\"ca";
echo "lendar.php?action=fetch&ymd=\"+dateclicked, function() {
			$('#allday').live('click', function() {
				if($('#allday').attr(\"checked\")){
					$('#end').attr(\"disabled\",true);
				} else {
					$('#end').attr(\"disabled\",false);
					$('#end').live('click', function() {
						$(this).datetimepicker({
							hour: 23,
							minute: 59,
							second: 59,
							defaultDate: date,
							";
echo "showSecond:true,
							ampm:false,
							dateFormat: \"";
echo $localdateformat;
echo "\",
							timeFormat: \"hh:mm:ss\",
							showOn: \"focus\"
						}).focus();
					});
				}
			});
			$('#start').live('click', function() {
				$(this).datetimepicker({
					hour: 00,
					minute: 00,
					second: 00,
					defaultDate: date,
					showSecond:true,
					ampm:false,
					dateFormat: \"";
echo $localdateformat;
echo "\",
					timeFormat: \"hh:mm:ss\",
					showOn: \"focus\"
				}).focus();
			});
		});
        $(\"#caledit\").fadeIn();

        //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
        //alert('Current view: ' + view.name);
        // change the day's background color just for fun
        //$(this).css('background-color', 'red');

    },
    eventClick: function(calEvent,";
echo " jsEvent, view) {

        var xpos = jsEvent.pageX;
        if (xpos>($(window).width()-400)) xpos = xpos-350;
        $(\"#caledit\").css(\"top\",jsEvent.pageY);
        $(\"#caledit\").css(\"left\",xpos);
        $(\"#caledit\").html('<img src=\"images/loading.gif\" /> ";
echo $aInt->lang("global", "loading", 1);
echo "');
        $.post(\"calendar.php\", { editentry: \"1\", id: calEvent.id }, function(data) {
			data = JSON.parse(data);
/*
			alert(data.defaultsh);
			alert(data.defaultsm);
			alert(data.defaulteh);
			alert(data.defaultem);
*/
            $(\"#caledit\").html(data.html);
			/* Disable End Field if All Days is selected
			if($('#allday').attr(\"checked\")){
				$('#end').attr(\"disabled\",";
echo "true);
			} else {
				$('#end').attr(\"disabled\",false);
			}
			*/
			$('#start').datetimepicker({
				hour: data.defaultsh,
				minute: data.defaultsm,
				defaultDate: new Date(data.defaultsdate),
				showSecond:true,
				ampm:false,
				dateFormat: \"";
echo $localdateformat;
echo "\",
				timeFormat: \"hh:mm:ss\",
			});
			$('#end').datetimepicker({
				hour: data.defaulteh,
				minute: data.defaultem,
				defaultDate: new Date(data.defaultedate),
				showSecond:true,
				ampm:false,
				dateFormat: \"";
echo $localdateformat;
echo "\",
				timeFormat: \"hh:mm:ss\",
			});
        });
        $(\"#caledit\").fadeIn();

        //alert('Event: ' + calEvent.id);
        //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
        //alert('View: ' + view.name);
        // change the border color just for fun
        //$(this).css('border-color', 'red');

    },
    eventDrop: function(calEvent,dayDelta,minuteD";
echo "elta,allDay,revertFunc) {

        $.post(\"calendar.php\", { action: \"update\", id: calEvent.id, type: \"move\", days: dayDelta, minutes: minuteDelta, allday: allDay, token: \"";
echo generate_token("plain");
echo "\" });

    },
    eventResize: function(calEvent,dayDelta,minuteDelta,revertFunc) {

        $.post(\"calendar.php\", { action: \"update\", id: calEvent.id, type: \"resize\", days: dayDelta, minutes: minuteDelta, token: \"";
echo generate_token("plain");
echo "\" });

    },
	eventSources: [
        ";
$i = 0;
foreach ($calevents as $calevent) {

	if (!isset($calcolors[$i])) {
		$i = 0;
	}

	echo "{ url: 'calendar.php?getcalfeed=1&feed=" . $calevent . "', color: '#" . $calcolors[$i]['bg'] . "', textColor: '#" . $calcolors[$i]['text'] . "' },";
	++$i;
}

echo "    ]

});

});

function deleteEntry(id) {
    jQuery(\"#calendar\").fullCalendar('removeEvents',id);
    $.post(\"calendar.php\", { action: \"delete\", id: id, token: \"";
echo generate_token("plain");
echo "\" });
    jQuery(\"#caledit\").fadeOut();
}

</script>
";
echo "<s";
echo "tyle type=\"text/css\">
#calendar {
	margin: 0 auto;
    width: 90%;
    max-width: 1200px;
}
#caledit {
    display:none;
    position:absolute;
    padding:8px;
    background-color:#f2f2f2;
    border:1px solid #ccc;
    width:350px;
    min-height:150px;
    z-index:100;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    -o-border-radius: 5px;
    border-radius: 5";
echo "px;
}
#caledit p {
    margin: 0 0 0 5px;
}
#calendarcontrols {
    float: right;
    margin: -45px 0 0 0;
    padding: 5px 15px;
    background-color: #F2F2F2;
    border: 1px dashed #CCC;
    font-size: 11px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    -o-border-radius: 5px;
    border-radius: 5px;
}
#calendarcontrols table td {
    font-size: 11px;
}
</st";
echo "yle>

<div id=\"calendarcontrols\"><form method=\"post\" name=\"refreshform\" action=\"calendar.php?action=refresh\"><table cellpadding=\"0\"><tr><td>";
echo "<s";
echo "trong>Show/Hide:</strong></td><td><input type=\"checkbox\" onclick=\"document.refreshform.submit()\" name=\"displaytypes[services]\" ";

if ($caldisplaytypes['services'] == "on") {
	echo "checked";
}

echo " /></td><td>Products/Services</td><td><input type=\"checkbox\" onclick=\"document.refreshform.submit()\" name=\"displaytypes[addons]\"  ";

if ($caldisplaytypes['addons'] == "on") {
	echo "checked";
}

echo " /></td><td>Addons</td><td><input type=\"checkbox\" onclick=\"document.refreshform.submit()\" name=\"displaytypes[domains]\"  ";

if ($caldisplaytypes['domains'] == "on") {
	echo "checked";
}

echo " /></td><td>Domains</td><td><input type=\"checkbox\" onclick=\"document.refreshform.submit()\" name=\"displaytypes[todo]\"  ";

if ($caldisplaytypes['todo'] == "on") {
	echo "checked";
}

echo " /></td><td>To-Do Items</td><td><input type=\"checkbox\" onclick=\"document.refreshform.submit()\" name=\"displaytypes[events]\"  ";

if ($caldisplaytypes['events'] == "on") {
	echo "checked";
}

echo " /></td><td>Events</td></tr></table></form></div>

<div id=\"calendar\"></div>

<form method=\"post\" action=\"calendar.php?action=save\">
<div id=\"caledit\"></div>
</form>

";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
?>