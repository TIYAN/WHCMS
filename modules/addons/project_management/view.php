<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.12
 * @ Author   : MTIMER
 * @ Release on : 2013-10-25
 * @ Website  : http://www.mtimer.cn
 *
 * */

function project_management_timesoutput($vars, $taskid) {
	$timesoutput = "<table width=\"95%\" bgcolor=\"#cccccc\" cellspacing=\"1\" align=\"center\" style=\"margin-top:5px;\"><tr class=\"taskholder" . $taskid . "\" bgcolor=\"#efefef\" style=\"text-align:center;font-weight:bold;\"><td align=\"center\">" . $vars["_lang"]["staff"] . "</td><td>" . $vars["_lang"]["starttime"] . "</td><td>" . $vars["_lang"]["stoptime"] . "</td><td>" . $vars["_lang"]["timespent"] . "</td><td width=\"25\"></td></tr>";
	$result2 = select_query( "mod_projecttimes", "*", array( "taskid" => $taskid ) );

	if ($timerdata = mysql_fetch_assoc( $result2 )) {
		$show_startresume = "false";
		$timerid = $timerdata["id"];
		$timeradmin = mysql_fetch_assoc( select_query( "tbladmins", "firstname,lastname", array( "id" => $timerdata["adminid"] ) ) );
		$timerstart = $timerdata["start"];
		$timerend = $timerdata["end"];
		$starttime = fromMySQLDate( date( "Y-m-d H:i:s", $timerstart ), 1 ) . ":" . date( "s", $timerstart );
		$endtimerlink = (( $timerdata["adminid"] == $_SESSION["adminid"] || project_management_check_masteradmin() ) ? "<a rel=\"" . $timerid . "\" id=\"ajaxendtimertaskid" . $taskid . "\" class=\"ajaxendtimer timerlink\">" . $vars["_lang"]["endtimer"] . "</a>" : $vars["_lang"]["inprogress"]);
		$deltimerlink = (( $timerdata["adminid"] == $_SESSION["adminid"] || project_management_check_masteradmin() ) ? "<a href=\"#\" onclick=\"deleteTimer('" . $timerid . "','" . $taskid . "');return false\"><img src=\"images/delete.gif\"></a>" : "");
		$endtime = ($timerend ? fromMySQLDate( date( "Y-m-d H:i:s", $timerend ), 1 ) . ":" . date( "s", $timerend ) : $endtimerlink);
		$totaltime = ($timerend ? project_management_sec2hms( $timerend - $timerstart ) : $vars["_lang"]["inprogress"]);
		$timesoutput .= "<tr bgcolor=\"#ffffff\" class=\"time taskholder" . $taskid . "\"><td>" . $timeradmin["firstname"] . " " . $timeradmin["lastname"] . "</td><td>" . $starttime . "</td><td id=\"ajaxendtimertaskholderid" . $timerid . "\">" . $endtime . "</td><td id=\"ajaxtimerstatusholderid" . $timerid . "\">" . $totaltime . "</td><td>" . $deltimerlink . "</td></tr>";

		if ($timerend) {
			$timecount += $timerend - $timerstart;
			$totaltimecount += $timerend - $timerstart;
			$show_startresume = "true";
			$invoicelinedesc .= " > " . $starttime . " - " . $endtime . " (" . $totaltime . " " . $vars["_lang"]["hours"] . ")
";
		}
	}


	if (!$timerid) {
		$timesoutput .= "<tr id=\"notasktimersexist" . $taskid . "\"><td colspan=\"6\" align=\"center\" bgcolor=\"#fff\">" . $vars["_lang"]["notimesrecorded"] . "</td></tr>";
	}

	$timesoutput .= "</table>";
	$GLOBALS["timerid"] = $timerid;
	$GLOBALS["timecount"] = $timecount;
	$GLOBALS["invoicelinedesc"] = $invoicelinedesc;
	return $timesoutput;
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

require ROOTDIR . "/includes/gatewayfunctions.php";
require ROOTDIR . "/includes/ticketfunctions.php";
$projectid = $_REQUEST["projectid"];
$modulelink .= "&projectid=" . (int)$projectid;

if ($a == "addticket") {
	if (project_management_checkperm( "Associate Tickets" )) {
		$ticketnum = $_REQUEST["ticketnum"];

		if (!trim( $ticketnum )) {
			exit( $vars["_lang"]["youmustenterticketnumber"] );
		}

		$data = get_query_vals( "tbltickets", "id,tid,date,title,status,lastreply", array( "tid" => $ticketnum ) );
		$ticketnum = $data["tid"];

		if (!$ticketnum) {
			exit( $vars["_lang"]["ticketnumberenterednotfound"] );
		}

		$ticketids = get_query_val( "mod_project", "ticketids", array( "id" => $projectid ) );
		$ticketids = explode( ",", $ticketids );

		if (in_array( $ticketnum, $ticketids )) {
			exit( $vars["_lang"]["ticketnumberalreadyassociated"] );
		}

		$ticketids[] = $ticketnum;
		update_query( "mod_project", array( "ticketids" => implode( ",", $ticketids ), "lastmodified" => "now()" ), array( "id" => $projectid ) );
		project_management_log( $projectid, $vars["_lang"]["addedticketassociation"] . $ticketnum );

		if ($_REQUEST["ajax"]) {
			echo $projectid;
			exit();
		}

		$ticketid = $data["id"];
		$ticketdate = $data["date"];
		$ticketnum = $data["tid"];
		$tickettitle = $data["title"];
		$ticketstatus = $data["status"];
		$ticketlastreply = $data["lastreply"];
		foreach ($ticketids as $i => $ajaxticketnum) {

			if ($ticketnum == $ajaxticketnum) {
				$ajaxticketid = $i;
				continue;
			}
		}

		echo "<tr id=\"ticketholder" . $i . "\"><td>" . fromMySQLDate( $ticketdate, true ) . "</td><td><a href=\"supporttickets.php?action=viewticket&id=" . $ticketid . "\" target=\"_blank\"><strong>#" . $ticketnum . " - " . $tickettitle . "</strong></a></td><td>" . getStatusColour( $ticketstatus ) . "</td><td>" . fromMySQLDate( $ticketlastreply, true ) . "</td><td>" . (project_management_checkperm( "Associate Tickets" ) ? "<a class=\"deleteticket\" id=\"deleteticket" . $i . "\"><img src=\"images/delete.gif\"></a>" : "") . "</td></tr>";
	}
	else {
		echo $vars["_lang"]["noticketassociatepermissions"];
	}

	exit();
}
else {
	if ($a == "addinvoice") {
		$invoicenum = $_REQUEST["invoicenum"];

		if (!trim( $invoicenum )) {
			exit( $vars["_lang"]["youmustenterinvoicenumber"] );
		}

		$data = get_query_vals( "tblinvoices", "id,date,datepaid,total,paymentmethod,status", array( "id" => $invoicenum ) );
		$invoicenum = $data["id"];

		if (!$invoicenum) {
			exit( $vars["_lang"]["invoicenumberenterednotfound"] );
		}

		$invoiceids = get_query_val( "mod_project", "invoiceids", array( "id" => $projectid ) );
		$invoiceids = explode( ",", $invoiceids );

		if (in_array( $invoicenum, $invoiceids )) {
			exit( $vars["_lang"]["invoicenumberalreadyassociated"] );
		}

		$invoiceids[] = $invoicenum;
		update_query( "mod_project", array( "invoiceids" => implode( ",", $invoiceids ), "lastmodified" => "now()" ), array( "id" => $projectid ) );
		project_management_log( $projectid, $vars["_lang"]["addedinvoiceassociation"] . $invoicenum );
		$invoiceid = $data["id"];
		$invoicedate = $data["date"];
		$invoicedatepaid = ($data["datepaid"] != "0000-00-00 00:00:00" ? fromMySQLDate( $data["datepaid"] ) : "-");
		$invoicetotal = $data["total"];
		$paymentmethod = get_query_val( "tblpaymentgateways", "value", array( "gateway" => $data["paymentmethod"], "setting" => "name" ) );
		$invoicestatus = $data["status"];
		echo "<tr id=\"invoiceholder" . $i . "\"><td><a href=\"invoices.php?action=edit&id=" . $invoiceid . "\" target=\"_blank\">" . $invoiceid . "</a></td><td>" . fromMySQLDate( $invoicedate ) . "</td><td>" . $invoicedatepaid . "</td><td>" . $invoicetotal . "</td><td>" . $paymentmethod . "</td><td>" . getInvoiceStatusColour( $invoicestatus ) . "</td></tr>";
		exit();
	}
	else {
if ($a == "addmsg") {
  if (project_management_checkperm("Post Messages")) {
    $message = ticketAutoHyperlinks(nl2br($_POST["msg"]));
    $projectsdir = $attachments_dir."projects/".$projectid."/";
    $projectsdir2 = $attachments_dir."projects/";
    $attachments = array();

    if ($_FILES["attachments"]["name"][0]) {
      if (!is_dir($projectsdir2)) {
        mkdir($projectsdir2);
      }

      if (!is_dir($projectsdir)) {
        mkdir($projectsdir);
      }

      foreach($_FILES["attachments"]["name"] as $num = >$filename) {
        $filename = trim($filename);
        $filename = preg_replace("/[^a-zA-Z0-9-_. ]/", "", $filename);
        mt_srand(time());
        $rand = mt_rand(100000, 999999);
        $newfilename = $rand."_".$filename;
        move_uploaded_file($_FILES["attachments"]["tmp_name"][$num], $projectsdir.$newfilename);
        $attachments[] = $newfilename;
      }
    }

    insert_query("mod_projectmessages", array("projectid" = >$projectid, "date" = >"now()", "message" = >$message, "attachments" = >implode(",", $attachments), "adminid" = >$_SESSION["adminid"]));
    project_management_log($projectid, $vars["_lang"]["newmsgposted"]);
    header("Location: ".$modulelink."&action=manage");
    exit();
  }
} else {
  if ($a == "updatestaffmsg") {
    $msgid = $_POST["msgid"];
    $msgtxt = html_entity_decode($_POST["msgtxt"]);
    update_query("mod_projectmessages", array("message" = >$msgtxt), array("id" = >$msgid));
    project_management_log($projectid, "Edited Staff Message");
    echo nl2br(ticketAutoHyperlinks($msgtxt));
    exit();
  } else {
    if ($a == "deletestaffmsg") {
      if (project_management_checkperm("Delete Messages")) {
        $attachments = explode(",", get_query_val("mod_projectmessages", "attachments", array("id" = >$_REQUEST["id"])));
        $projectsdir = $attachments_dir."projects/".$projectid."/";
        foreach($attachments as $i = >$attachment) {
          unlink($projectsdir.$attachments[$i]);
          project_management_log($projectid, $vars["_lang"]["deletedattachment"]." ".substr($attachments[$i], 7));
          unset($attachments[$i]);
        }

        delete_query("mod_projectmessages", array("id" = >$_REQUEST["id"]));
        project_management_log($projectid, "Deleted Staff Message");
        echo $_REQUEST["id"];
      } else {
        echo "0";
      }

      exit();
    } else {
      if ($a == "hookstarttimer") {
        $projectid = $_REQUEST["projectid"];
        $ticketnum = $_REQUEST["ticketnum"];
        $taskid = $_REQUEST["taskid"];
        $title = $_REQUEST["title"];

        if ((!$taskid && $title)) {
          $taskid = insert_query("mod_projecttasks", array("projectid" = >$projectid, "task" = >$title, "created" = >"now()"));
          project_management_log($projectid, $vars["_lang"]["addedtask"].$title);
        }

        $timerid = insert_query("mod_projecttimes", array("projectid" = >$projectid, "taskid" = >$taskid, "start" = >time(), "adminid" = >$_SESSION["adminid"]));
        project_management_log($projectid, $vars["_lang"]["startedtimerfortask"].get_query_val("mod_projecttasks", "task", array("id" = >$taskid)));

        if ($timerid) {
          $result = select_query("mod_projecttimes", "mod_projecttimes.id, mod_projecttimes.projectid, mod_project.title, mod_projecttimes.taskid, mod_projecttasks.task, mod_projecttimes.start", array("mod_projecttimes.adminid" = >$_SESSION["adminid"], "mod_projecttimes.end" = >"", "mod_project.ticketids" = >array("sqltype" = >"LIKE", "value" = >(int) $ticketnum)), "", "", "", "mod_projecttasks ON mod_projecttimes.taskid=mod_projecttasks.id INNER JOIN mod_project ON mod_projecttimes.projectid=mod_project.id");

          while ($data = mysql_fetch_array($result)) {
            echo "<div class=\"stoptimer".$data["id"]."\" style=\"padding-bottom:10px;\"><em>".$data["title"]." - Project ID ".$data["projectid"]."</em><br />&nbsp;&raquo; ".$data["task"]."<br />Started at ".fromMySQLDate(date("Y-m-d H:i:s", $data["start"]), 1).":".date("s", $data["start"])." - <a href=\"#\" onclick=\"projectendtimersubmit('".$data["projectid"]."','".$data["id"]."');return false\"><strong>Stop Timer</strong></a></div>";
          }
        } else {
          echo "0";
        }

        exit();
      } else {
        if ($a == "hookendtimer") {
          $timerid = $_POST["timerid"];
          $ticketnum = $_POST["ticketnum"];
          $taskid = get_query_val("mod_projecttimes", "taskid", array("id" = >$timerid, "adminid" = >$_SESSION["adminid"]));
          $projectid = get_query_val("mod_projecttimes", "projectid", array("id" = >$timerid, "adminid" = >$_SESSION["adminid"]));
          update_query("mod_projecttimes", array("end" = >time()), array("id" = >$timerid, "taskid" = >$taskid, "adminid" = >$_SESSION["adminid"]));
          project_management_log($projectid, $vars["_lang"]["stoppedtimerfortask"].get_query_val("mod_projecttasks", "task", array("id" = >$taskid)));

          if (!$taskid) {
            echo "0";
          } else {
            $result = select_query("mod_projecttimes", "mod_projecttimes.id, mod_projecttimes.projectid, mod_project.title, mod_projecttimes.taskid, mod_projecttasks.task, mod_projecttimes.start", array("mod_projecttimes.adminid" = >$_SESSION["adminid"], "mod_projecttimes.end" = >"", "mod_project.ticketids" = >array("sqltype" = >"LIKE", "value" = >(int) $ticketnum)), "", "", "", "mod_projecttasks ON mod_projecttimes.taskid=mod_projecttasks.id INNER JOIN mod_project ON mod_projecttimes.projectid=mod_project.id");

            while ($data = mysql_fetch_array($result)) {
              echo "<div class=\"stoptimer".$data["id"]."\" style=\"padding-bottom:10px;\"><em>".$data["title"]." - Project ID ".$data["projectid"]."</em><br />&nbsp;&raquo; ".$data["task"]."<br />Started at ".fromMySQLDate(date("Y-m-d H:i:s", $data["start"]), 1).":".date("s", $data["start"])." - <a href=\"#\" onclick=\"projectendtimersubmit('".$data["projectid"]."','".$data["id"]."');return false\"><strong>Stop Timer</strong></a></div>";
            }
          }

          exit();
        } else {
          if ($a == "starttimer") {
            $projectid = (int) $_REQUEST["projectid"];
            $taskid = (int) $_REQUEST["taskid"];
            $activetimers = select_query("mod_projecttimes", "id", array("end" = >"", "projectid" = >$projectid, "taskid" = >$taskid, "adminid" = >$_SESSION["adminid"]));

            if ($activetimersdata = mysql_fetch_assoc($activetimers)) {
              update_query("mod_projecttimes", array("end" = >time()), array("id" = >$activetimersdata["id"]));
            }

            $timerstart = time();

            if (($projectid && $taskid)) {
              $timerid = insert_query("mod_projecttimes", array("projectid" = >$projectid, "taskid" = >$taskid, "start" = >$timerstart, "adminid" = >$_SESSION["adminid"]));
              project_management_log($projectid, $vars["_lang"]["startedtimerfortask"].get_query_val("mod_projecttasks", "task", array("id" = >$taskid)));

              if ($timerid) {
                $timeradmin = get_query_val("tbladmins", "CONCAT(firstname,' ',lastname)", array("id" = >$_SESSION["adminid"]));
                $starttime = fromMySQLDate(date("Y-m-d H:i:s", $timerstart), 1).":".date("s", $timerstart);
                $endtimerlink = (($timerdata["adminid"] == $_SESSION["adminid"] || project_management_check_masteradmin()) ? "<a rel=\"".$timerid."\" id=\"ajaxendtimertaskid".$taskid."\" class=\"ajaxendtimer timerlink\">".$vars["_lang"]["endtimer"]."</a>": $vars["_lang"]["inprogress"]);
                $deltimerlink = (($timerdata["adminid"] == $_SESSION["adminid"] || project_management_check_masteradmin()) ? "<a onclick=\"deleteTimer('".$timerid."','".$taskid."')\" href=\"#\"><img src=\"images/delete.gif\"></a>": "");
                $endtime = ($timerend ? fromMySQLDate(date("Y-m-d H:i:s", $timerend), 1).":".date("s", $timerend) : $endtimerlink);
                $totaltime = ($timerend ? project_management_sec2hms($timerend - $timerstart) : "In Progress");
                echo "<tr bgcolor=\"#ffffff\" class=\"time taskholder".$taskid."\"><td>".$timeradmin."</td><td>".$starttime."</td><td id=\"ajaxendtimertaskholderid".$timerid."\">".$endtime."</td><td id=\"ajaxtimerstatusholderid".$timerid."\">".$totaltime."</td><td>".$deltimerlink."</td></tr>";
              }
            } else {
              echo $projectid." ".$taskid;
            }

            exit();
          } else {
            if ($a == "endtimer") {
              $timerid = $_REQUEST["timerid"];
              $projectid = $_REQUEST["projectid"];
              $taskid = $_REQUEST["taskid"];
              update_query("mod_projecttimes", array("end" = >time()), array("id" = >$timerid, "taskid" = >$taskid));
              logActivity(get_query_val("mod_projecttimes", "end-start", array("id" = >$timerid, "taskid" = >$taskid)));
              $duration = project_management_sec2hms(get_query_val("mod_projecttimes", "end-start", array("id" = >$timerid, "taskid" = >$taskid)));
              project_management_log($projectid, $vars["_lang"]["stoppedtimerfortask"].get_query_val("mod_projecttasks", "task", array("id" = >$taskid)));

              if ($_REQUEST["ajax"]) {
                echo json_encode(array("time" = >fromMySQLDate(date("Y-m-d H:i:s"), 1).":".date("s"), "duration" = >$duration));
              } else {
                header("Location: ".$modulelink."&action=manage&projectid=".$projectid);
              }

              exit();
            } else {
              if ($a == "deletetimer") {
                $timerid = $_REQUEST["id"];
                $taskid = $_REQUEST["taskid"];
                delete_query("mod_projecttimes", array("id" = >$timerid, "taskid" = >$taskid));
                project_management_log($projectid, $vars["_lang"]["deletedtimerfortask"].get_query_val("mod_projecttasks", "task", array("id" = >$taskid)));
                header("Location: ".$modulelink."&action=manage&projectid=".$projectid);
                exit();
              } else {
                if ($a == "addtask") {
                  $newtask = trim($_POST["newtask"]);
                  $maxorder = get_query_val("mod_projecttasks", "MAX(`order`)", array("projectid" = >$projectid));

                  if ($newtask) {
                    $taskid = insert_query("mod_projecttasks", array("projectid" = >$projectid, "task" = >$newtask, "created" = >"now()", "order" = >$maxorder + 1));
                    project_management_log($projectid, $vars["_lang"]["addedtask"].$newtask);
                  }

                  $taskedit = (project_management_checkperm("Edit Tasks") ? " <a href=\"".str_replace("&m=view", "&m=edittask", $modulelink)."&id=".$taskid."\"><img src=\"images/edit.gif\" align=\"absmiddle\" /></a>": "");
                  $taskdelete = (project_management_checkperm("Delete Tasks") ? " <a href=\"#\" onclick=\"deleteTask(".$taskid.");return false\"><img src=\"images/delete.gif\" align=\"absmiddle\" /></a>": "");
                  $timesoutput = project_management_timesoutput($vars, $taskid);
                  $notesoutput = "<div style=\"margin-top:5px;\"><table width=\"95%\" align=\"center\"><tr><td><textarea rows=\"3\" style=\"width:100%\" id=\"tasknotestxtarea".$taskid."\">".$tasknotes."</textarea></td><td width=\"120\" align=\"right\"><input type=\"button\" id=\"savetasknotestxtarea".$taskid."\" class=\"savetasknotestxtarea\" value=\"".$vars["_lang"]["savenotes"]."\" /></td></tr></table></div>";
                  $tasknotes = "<a class=\"tasknotestoggler\" id=\"tasknotestogglerclicker".$taskid."\"><img src=\"../modules/addons/project_management/images/". ($tasknotes ? "": "no")."notes.png\" align=\"absmiddle\" title=\"View/Edit Notes\" /></a>";
                  $tmptaskshtml = "";
                  $taskshtml = "<tr id=\"taskholder".$taskid."\">\"
    <td class=\"sortcol\"></td>
    <td>
	<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
		<tr><td align=\"left\"><input type=\"checkbox\" name=\"task[" . $taskid . "]\" id=\"tk" . $taskid . "\" value=\"1\"" . $taskcompleted . " onclick=\"updatetaskstatus('" . $taskid . "')\" /> " . $taskadmin . "<label for=\"tk" . $taskid . "\">" . $newtask . "</label> " . $taskduedate . " <span class=\"taskbox\">" . project_management_sec2hms( 0 ) . " Hrs</span> " . $tasknotes . " <div style=\"float:right;\"><a class=\"ajaxstarttimer tasktimerexpander\" id=\"ajaxstarttimer" . $taskid . "\"><img src=\"../modules/addons/project_management/images/starttimer.png\" align=\"absmiddle\" title=\"Start Timer\" /></a> <a id=\"tasktimertoggleclicker" . $taskid . "\" class=\"tasktimertoggle\"><img src=\"../modules/addons/project_management/images/notimes.png\" align=\"absmiddle\" title=\"View Times\" /></a> " . $taskedit . $taskdelete . "</div></td></tr>
		<tr style=\"display:none\" id=\"tasktimerexpandholder" . $taskid . "\"><td>" . $timesoutput . "</td></tr>
		<tr style=\"display:none\" id=\"tasknotesexpandholder" . $taskid . "\"><td>" . $notesoutput . "</td></tr>
	</table>
    </td>
</tr>";
echo $taskshtml;
exit();
} else {
  if ($a == "updatetask") {
    if ($_REQUEST["taskid"]) {
      update_query("mod_projecttasks", array("completed" = >($_REQUEST["status"] == "checked" ? "1": "0")), array("id" = >(int) $_REQUEST["taskid"]));
    }

    $taskstatusdata = project_management_tasksstatus($projectid, $vars);
    echo $taskstatusdata["html"];
    exit();
  } else {
    if ($a == "savetasksorder") {
      $torderarr = explode("&amp;", $_REQUEST["torderarr"]);
      $tonum = 0;
      foreach($torderarr as $v) {
        $v = explode("tasks[]=taskholder", $v);

        if ($v[1]) {
          update_query("mod_projecttasks", array("order" = >$tonum), array("id" = >$v[1])); ++$tonum;
          continue;
        }
      }

      exit();
    } else {
      if ($a == "savetasknotes") {
        if ($_REQUEST["taskid"]) {
          update_query("mod_projecttasks", array("notes" = >$_REQUEST["notes"]), array("id" = >(int) $_REQUEST["taskid"]));
          echo "1";
        } else {
          echo "0";
        }

        exit();
      } else {
        if ($a == "deletetask") {
          if (project_management_checkperm("Delete Tasks")) {
            $id = $_REQUEST["id"];
            delete_query("mod_projecttasks", array("projectid" = >$projectid, "id" = >$id));
            delete_query("mod_projecttimes", array("taskid" = >$id));
            project_management_log($projectid, $vars["_lang"]["deletedtask"]);
            echo $id;
            exit();
          }
        } else {
          if ($a == "deleteticket") {
            if (project_management_checkperm("Associate Tickets")) {
              $result = select_query("mod_project", "ticketids", array("id" = >$projectid));
              $data = mysql_fetch_array($result);
              $ticketids = explode(",", $data["ticketids"]);
              project_management_log($projectid, $vars["_lang"]["deletedticketrelationship"].$ticketids[$_REQUEST["id"]]);
              unset($ticketids[$_REQUEST["id"]]);
              update_query("mod_project", array("ticketids" = >implode(",", $ticketids), "lastmodified" = >"now()"), array("id" = >$projectid));
              echo $_REQUEST["id"];
              exit();
            }
          } else {
            if ($a == "projectsave") {
              $logmsg = "";
              $result = select_query("mod_project", "", array("id" = >$projectid));
              $data = mysql_fetch_array($result);
              $updateqry["userid"] = $_POST["userid"];
              $updateqry["title"] = $_POST["title"];
              $updateqry["adminid"] = $_POST["adminid"];
              $updateqry["created"] = toMySQLDate($_POST["created"]);
              $updateqry["duedate"] = toMySQLDate($_POST["duedate"]);
              $updateqry["lastmodified"] = "now()";

              if ($_POST["completed"]) {
                update_query("mod_projecttasks", array("completed" = >"1"), array("projectid" = >$projectid));
              }

              if (!$logmsg) {
                if (($updateqry["title"] && $updateqry["title"] != $data["title"])) {
                  $changes[] = $vars["_lang"]["titlechangedfrom"].$data["title"]." to ".$updateqry["title"];
                }

                if ((isset($updateqry["userid"]) && $updateqry["userid"] != $data["userid"])) {
                  $changes[] = $vars["_lang"]["assignedclientchangedfrom"].$data["userid"]." ".$vars["_lang"]["to"]." ".$updateqry["userid"];
                }

                if ($updateqry["adminid"] != $data["adminid"]) {
                  $changes[] = $vars["_lang"]["assignedadminchangedfrom"]. ($data["adminid"] ? getAdminName($data["adminid"]) : "Nobody")." ".$vars["_lang"]["to"]." ". ($updateqry["adminid"] ? getAdminName($updateqry["adminid"]) : "Nobody");
                }

                if (($_POST["created"] && $_POST["created"] != fromMySQLDate($data["created"]))) {
                  $changes[] = $vars["_lang"]["creationdatechangedfrom"].fromMySQLDate($data["created"])." to ".$_POST["created"];
                }

                if (($_POST["duedate"] && $_POST["duedate"] != fromMySQLDate($data["duedate"]))) {
                  $changes[] = $vars["_lang"]["duedatechangedfrom"].fromMySQLDate($data["duedate"])." to ".$_POST["duedate"];
                }

                if ($_POST["newticketid"]) {
                  $changes[] = $vars["_lang"]["addednewrelatedticket"].$_POST["newticketid"];
                }

                if (($updateqry["notes"] && $updateqry["notes"] != $data["notes"])) {
                  $changes[] = $vars["_lang"]["notesupdated"];
                }

                if (($updateqry["completed"] && $updateqry["completed"] != $data["completed"])) {
                  $changes[] = $vars["_lang"]["projectmarkedcompleted"];
                }

                $logmsg = $vars["_lang"]["updatedproject"].implode(", ", $changes);
              }

              if (count($changes)) {
                project_management_log($projectid, $logmsg);
              }

              update_query("mod_project", $updateqry, array("id" = >$projectid));
              echo project_management_daysleft($_POST["duedate"], $vars);
              exit();
            } else {
              if ($a == "statussave") {
                if (project_management_checkperm("Update Status")) {
                  $status = db_escape_string($_POST["status"]);
                  $statuses = explode(",", $vars["statusvalues"]);
                  $statusarray = array();
                  foreach($statuses as $tmpstatus) {
                    $tmpstatus = explode("|", $tmpstatus, 2);
                    $statusarray[] = $tmpstatus[0];
                  }

                  if (in_array($status, $statusarray)) {
                    $oldstatus = get_query_val("mod_project", "status", array("id" = >$projectid));
                    $updateqry = array("status" = >$status);

                    if (in_array($status, explode(",", $vars["completedstatuses"]))) {
                      $updateqry["completed"] = "1";
                    } else {
                      $updateqry["completed"] = "0";
                    }

                    update_query("mod_project", $updateqry, array("id" = >$projectid));
                    project_management_log($projectid, $vars["_lang"]["statuschangedfrom"].$oldstatus." ".$vars["_lang"]["to"]." ".$status);
                  }
                }

                exit();
              } else {
                if ($a == "addattachment") {
                  $projectsdir = $attachments_dir."projects/".$projectid."/";
                  $projectsdir2 = $attachments_dir."projects/";

                  if (!is_dir($projectsdir2)) {
                    mkdir($projectsdir2);
                  }

                  if (!is_dir($projectsdir)) {
                    mkdir($projectsdir);
                  }

                  $attachments = explode(",", get_query_val("mod_project", "attachments", array("id" = >$projectid)));

                  if (empty($attachments[0])) {
                    unset($attachments[0]);
                  }

                  if ($_FILES["attachments"]["name"][0]) {
                    foreach($_FILES["attachments"]["name"] as $num = >$filename) {
                      $filename = trim($filename);
                      $filename = preg_replace("/[^a-zA-Z0-9-_. ]/", "", $filename);
                      mt_srand(time());
                      $rand = mt_rand(100000, 999999);
                      $newfilename = $rand."_".$filename;
                      move_uploaded_file($_FILES["attachments"]["tmp_name"][$num], $projectsdir.$newfilename);
                      $attachments[] = $newfilename;
                      update_query("mod_project", array("attachments" = >implode(",", $attachments)), array("id" = >$projectid));
                      project_management_log($projectid, $vars["_lang"]["addedattachment"]." ".$filename);
                    }
                  }

                  header("Location: ".$modulelink);
                  exit();
                } else {
                  if ($a == "deleteattachment") {
                    if (project_management_check_masteradmin()) {
                      $attachments = explode(",", get_query_val("mod_project", "attachments", array("id" = >$projectid)));
                      $projectsdir = $attachments_dir."projects/".$projectid."/";
                      $i = $_REQUEST["i"];
                      unlink($projectsdir.$attachments[$i]);
                      project_management_log($projectid, $vars["_lang"]["deletedattachment"]." ".substr($attachments[$i], 7));
                      unset($attachments[$i]);
                      update_query("mod_project", array("attachments" = >implode(",", $attachments), "lastmodified" = >"now()"), array("id" = >$projectid));
                    }

                    header("Location: ".$modulelink."&action=manage&projectid=".$projectid);
                    exit();
                  } else {
                    if ($a == "addquickinvoice") {
                      $newinvoice = trim($_REQUEST["newinvoice"]);
                      $newinvoiceamt = trim($_REQUEST["newinvoiceamt"]);

                      if (($newinvoice && $newinvoiceamt)) {
                        $userid = get_query_val("mod_project", "userid", array("id" = >$projectid));
                        $gateway = (function_exists("getClientsPaymentMethod") ? getClientsPaymentMethod($userid) : "paypal");

                        if ($CONFIG["TaxEnabled"] == "on") {
                          $clientsdetails = getClientsDetails($userid);

                          if (!$clientsdetails["taxexempt"]) {
                            $state = $clientsdetails["state"];
                            $country = $clientsdetails["country"];
                            $taxdata = getTaxRate(1, $state, $country);
                            $taxdata2 = getTaxRate(2, $state, $country);
                            $taxrate = $taxdata["rate"];
                            $taxrate2 = $taxdata2["rate"];
                          }
                        }

                        $invoiceid = insert_query("tblinvoices", array("date" = >"now()", "duedate" = >"now()", "userid" = >$userid, "status" = >"Unpaid", "paymentmethod" = >$gateway, "taxrate" = >$taxrate, "taxrate2" = >$taxrate2));
                        insert_query("tblinvoiceitems", array("invoiceid" = >$invoiceid, "userid" = >$userid, "type" = >"Project", "relid" = >$projectid, "description" = >$newinvoice, "paymentmethod" = >$gateway, "amount" = >$newinvoiceamt, "taxed" = >"1"));
                        updateInvoiceTotal($invoiceid);
                        $invoiceids = get_query_val("mod_project", "invoiceids", array("id" = >$projectid));
                        $invoiceids = explode(",", $invoiceids);
                        $invoiceids[] = $invoiceid;
                        $invoiceids = implode(",", $invoiceids);
                        update_query("mod_project", array("invoiceids" = >$invoiceids), array("id" = >$projectid));
                        project_management_log($projectid, $vars["_lang"]["addedquickinvoice"]." ".$invoiceid, $userid);

                        if (1 < $CONFIG["InvoiceIncrement"]) {
                          $invoiceincrement = $CONFIG["InvoiceIncrement"] - 1;
                          $counter = 33;

                          while ($counter <= $invoiceincrement) {
                            $tempinvoiceid = insert_query("tblinvoices", array("date" = >"now()"));
                            delete_query("tblinvoices", array("id" = >$tempinvoiceid));
                            $counter += 33;
                          }
                        }

                        run_hook("InvoiceCreationAdminArea", array("invoiceid" = >$invoiceid));
                      }

                      header("Location: ".$modulelink."&action=manage&projectid=".$projectid);
                      exit();
                    } else {
                      if ($a == "gettimesheethead") {
                        echo "<link href=\"../includes/jscript/css/ui.all.css\" type=\"text/css /><script src=\"../includes/jscript/jquery.js\"></script><script src=\"../includes/jscript/jqueryui.js\"></script>";
                        exit();
                      } else {
                        if ($a == "gettimesheet") {
                          if (project_management_checkperm("Bill Tasks")) {
                            echo "<form method=\"post\" action=\"".$modulelink."&a=dynamicinvoicegenerate\">
<div class=\"box\">
<table width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\" class=\"tasks\" id=\"tasks\"><tr bgcolor=\"#efefef\">
	<th width=\"60%\">".$vars["_lang"]["description"]."</th><th width=\"10%\">".$vars["_lang"]["hours"]."</th><th width=\"14%\">".$vars["_lang"]["rate"]."</th><th width=\"15%\">".$vars["_lang"]["amount"]."</th><th width=\"20\"></th></tr>";
                            $dti = 0;
                            $tasksresult = select_query("mod_projecttasks", "id,task", array("projectid" = >$projectid, "billed" = >"0"));

                            if ($tasksdata = mysql_fetch_assoc($tasksresult)) {
                              $dynamictimes[$dti]["seconds"] = get_query_val("mod_projecttimes", "SUM(end-start)", array("taskid" = >$tasksdata["id"], "donotbill" = >0));
                              $dynamictimes[$dti]["description"] = $tasksdata["task"];
                              $dynamictimes[$dti]["rate"] = $vars["hourlyrate"];
                              $dynamictimes[$dti]["amount"] = $dynamictimes[$dti]["rate"] * ($dynamictimes[$dti]["seconds"] / 3600);

                              if (0 < $dynamictimes[$dti]["seconds"]) {
                                echo "<tr id=\"dynamictaskinvoiceitemholder".$dti."\">\"
			<td><input type=\"hidden\" name=\"taskid[" . $dti . "]\" value=\"" . $tasksdata["id"] . "\" /><input style=\"width:99%\" type=\"text\" name=\"description[" . $dti . "]\" value=\"" . $dynamictimes[$dti]["description"] . "\" /></td>
			<td><input type=\"hidden\" id=\"dynamicbillhours" . $dti . "\" name=\"hours[" . $dti . "]\" value=\"" . round( $dynamictimes[$dti]["seconds"] / 3600, 2 ) . "\" /><input type=\"text\" name=\"displayhours[" . $dti . "]\" class=\"dynamicbilldisplayhours\" id=\"dynamicbilldisplayhours" . $dti . "\" name=\"hours[" . $dti . "]\" value=\"" . project_management_sec2hms( $dynamictimes[$dti]["seconds"] ) . "\" /></td>
			<td><input type=\"text\" class=\"dynamicbillrate\" id=\"dynamicbillrate" . $dti . "\" name=\"rate[" . $dti . "]\" value=\"" . format_as_currency( $dynamictimes[$dti]["rate"] ) . "\" /></td>
			<td><input type=\"text\" id=\"dynamicbillamount" . $dti . "\" name=\"amount[" . $dti . "]\" value=\"" . format_as_currency( $dynamictimes[$dti]["amount"], 2 ) . "\" /></td>
			<td><a class=\"deldynamictaskinvoice\" id=\"deldynamictaskinvoice" . $dti . "\"><img src=\"images/delete.gif\"></a></td></tr>";
																									}

++$dti;
}

echo "</table></div><p align=\"center\"><input type=\"submit\" value=\"".$vars["_lang"]["generatenow"]."\" />&nbsp;<input type=\"submit\" onClick=\"form.action='".$modulelink."&a=dynamicinvoicegenerate&sendinvoicegenemail=true'\" value=\"".$vars["_lang"]["generatenowandemail"]."\" />&nbsp;<input type=\"button\" id=\"dynamictasksinvoicecancel\" value=\"".$vars["_lang"]["cancel"]."\" /></p>
		</form>";
}

exit();
} else {
  if ($a == "dynamicinvoicegenerate") {
    if (!project_management_checkperm("Bill Tasks")) {
      header("Location: ".$modulelink);
      exit();
    }

    $userid = get_query_val("mod_project", "userid", array("id" = >$projectid));
    $gateway = (function_exists("getClientsPaymentMethod") ? getClientsPaymentMethod($userid) : "paypal");

    if ($CONFIG["TaxEnabled"] == "on") {
      $clientsdetails = getClientsDetails($userid);

      if (!$clientsdetails["taxexempt"]) {
        $state = $clientsdetails["state"];
        $country = $clientsdetails["country"];
        $taxdata = getTaxRate(1, $state, $country);
        $taxdata2 = getTaxRate(2, $state, $country);
        $taxrate = $taxdata["rate"];
        $taxrate2 = $taxdata2["rate"];
      }
    }

    $invoiceid = insert_query("tblinvoices", array("date" = >"now()", "duedate" = >"now()", "userid" = >$userid, "status" = >"Unpaid", "paymentmethod" = >$gateway, "taxrate" = >$taxrate, "taxrate2" = >$taxrate2));
    foreach($_REQUEST["taskid"] as $taski = >$taskid) {
      update_query("mod_projecttasks", array("billed" = >1), array("id" = >$taskid));
    }

    foreach($_REQUEST["description"] as $desci = >$description) {

      if (((($description && $_REQUEST["displayhours"][$desci]) && $_REQUEST["rate"][$desci]) && $_REQUEST["amount"][$desci])) {
        $description. = " - ".$_REQUEST["displayhours"][$desci]." ".$vars["_lang"]["hours"];

        if ($_REQUEST["rate"][$desci] != $vars["hourlyrate"]) {
          $amount = $_REQUEST["hours"][$desci] * $_REQUEST["rate"][$desci];
        } else {
          $amount = $_REQUEST["amount"][$desci];
        }

        insert_query("tblinvoiceitems", array("invoiceid" = >$invoiceid, "userid" = >$userid, "type" = >"Project", "relid" = >$projectid, "description" = >$description, "paymentmethod" = >$gateway, "amount" = >round($amount, 2), "taxed" = >"1"));
        updateInvoiceTotal($invoiceid);

        if (1 < $CONFIG["InvoiceIncrement"]) {
          $invoiceincrement = $CONFIG["InvoiceIncrement"] - 1;
          $counter = 33;

          while ($counter <= $invoiceincrement) {
            $tempinvoiceid = insert_query("tblinvoices", array("date" = >"now()"));
            delete_query("tblinvoices", array("id" = >$tempinvoiceid));
            $counter += 33;
          }

          continue;
        }

        continue;
      }
    }

    $invoiceids = get_query_val("mod_project", "invoiceids", array("id" = >$projectid));
    $invoiceids = explode(",", $invoiceids);
    $invoiceids[] = $invoiceid;
    $invoiceids = implode(",", $invoiceids);
    update_query("mod_project", array("invoiceids" = >$invoiceids), array("id" = >$projectid));

    if (($invoiceid && $_REQUEST["sendinvoicegenemail"] == "true")) {
      sendMessage("Invoice Created", $invoiceid);
    }

    project_management_log($projectid, $vars["_lang"]["createdtimebasedinvoice"]." ".$invoiceid, $userid);
    run_hook("InvoiceCreationAdminArea", array("invoiceid" = >$invoiceid));
    header("Location: ".$modulelink);
    exit();
  } else {
    if ($a == "savetasklist") {
      $tasksarray = array();
      $result = select_query("mod_projecttasks", "", array("projectid" = >$_REQUEST["projectid"]), "order", "ASC");

      while ($data = mysql_fetch_array($result)) {
        $tasksarray[] = array("task" = >$data["task"], "notes" = >$data["notes"], "adminid" = >$data["adminid"], "duedate" = >$data["duedate"]);
      }

      insert_query("mod_projecttasktpls", array("name" = >$_REQUEST["taskname"], "tasks" = >serialize($tasksarray)));
    } else {
      if ($a == "loadtasklist") {
        $maxorder = get_query_val("mod_projecttasks", "MAX(`order`)", array("projectid" = >$_REQUEST["projectid"]));
        $result = select_query("mod_projecttasktpls", "tasks", array("id" = >$_REQUEST["tasktplid"]));
        $data = mysql_fetch_array($result);
        $tasks = unserialize($data["tasks"]);
        foreach($tasks as $task) {++$maxorder;
          insert_query("mod_projecttasks", array("projectid" = >$_REQUEST["projectid"], "task" = >$task["task"], "notes" = >$task["notes"], "adminid" = >$task["adminid"], "created" = >"now()", "order" = >$maxorder));
        }

        redir("module=project_management&m=view&projectid=".$_REQUEST["projectid"]);
      }
    }
  }
}
}
}
}
}
}
}
}
}
}
}
}
}
}
}
}
}
}
}
}
}
}
}


if ($projectid) {
	$result = select_query( "mod_project", "", array( "id" => $projectid ) );
	$data = mysql_fetch_array( $result );
	$projectid = $data["id"];

	if (!$projectid) {
		echo "<p><b>" . $vars["_lang"]["viewingproject"] . "</b></p><p>" . $vars["_lang"]["projectidnotfound"] . "</p>";
		return null;
	}

	$title = $data["title"];
	$attachments = $data["attachments"];
	$ticketids = $data["ticketids"];
	$notes = $data["notes"];
	$userid = $data["userid"];
	$adminid = $data["adminid"];
	$created = $data["created"];
	$duedate = $data["duedate"];
	$completed = $data["completed"];
	$projectstatus = $data["status"];
	$lastmodified = $data["lastmodified"];
	$show_project = (project_management_check_viewproject( $projectid ) ? true : false);

	if (!$show_project) {
		header( "Location: " . str_replace( "m=view", "m=overview", $modulelink ) );
		exit();
	}

	$daysleft = project_management_daysleft( $duedate, $vars );
	$attachments = explode( ",", $attachments );
	$ticketids = explode( ",", $ticketids );
	$created = fromMySQLDate( $created );
	$duedate = fromMySQLDate( $duedate );
	$lastmodified = fromMySQLDate( $lastmodified, true );
	$client = "";

	if (!$userid) {
		foreach ($ticketids as $i => $ticketnum) {

			if ($ticketnum) {
				$result = select_query( "tbltickets", "userid", array( "tid" => $ticketnum ) );
				$data = mysql_fetch_array( $result );
				$userid = $data["userid"];
				update_query( "mod_project", array( "userid" => $userid ), array( "id" => $projectid ) );
				continue;
			}
		}
	}


	if ($userid) {
		$result = select_query( "tblclients", "id,firstname,lastname,companyname", array( "id" => $userid ) );
		$data = mysql_fetch_array( $result );
		$clientname = $data[1] . " " . $data[2];

		if ($data[3]) {
			$clientname .= " (" . $data[3] . ")";
		}

		$client = "<a href=\"clientssummary.php?userid=" . $userid . "\">" . $clientname . "</a>";
	}

	$headtitle = $title;
}
else {
	$headtitle = $vars["_lang"]["newproject"];
	$daysleft = $client = "";
	$created = getTodaysDate();
	$duedate = getTodaysDate();
}

$admin = trim( get_query_val( "tbladmins", "CONCAT(firstname,' ',lastname)", array( "id" => $adminid ) ) );

if (!$admin) {
	$admin = $vars["_lang"]["none"];
}


if (!$client) {
	$client = $vars["_lang"]["none"];
}

$jquerycode .= "$(\"#addattachment\").click(function () {
    $(\"#attachments\").append(\"<input type=\\\"file\\\" name=\\\"attachments[]\\\" size=\\\"30\\\" /><br />\");
    return false;
});
$(\"#addmsgattachment\").click(function () {
    $(\"#msgattachments\").append(\"<input type=\\\"file\\\" name=\\\"attachments[]\\\" size=\\\"30\\\" /><br />\");
    return false;
});";

if ($projectid) {
	$jquerycode .= "
$(\"#statuschange\").change(function () {
    $(\"#savesuccess\").fadeIn();
	$.post(\"" . $modulelink . "&a=statussave\", { status : $(\"#statuschange\").val() },
    function (data) {
        $(\"#savesuccess\").fadeOut(5000);
    });
});
$(\"#editprojectbtn\").click(function() {
	$(\".displayval\").fadeOut(\"fast\", function() {
        $(\".editfield\").fadeIn();
    });
	$(\"#editprojectform\").fadeIn();
	$(\"#editprojectbtn\").hide();
	$(\"#saveprojectbtn\").fadeIn();
	$(\"#cancelsaveprojectbtn\").fadeIn();
});
$(\"#cancelsaveprojectbtn\").click(function() {
	$(\"#saveprojectbtn\").hide();
	$(\"#cancelsaveprojectbtn\").hide();
	$(\"#editprojectbtn\").show();
    $(\".editfield\").fadeOut(\"fast\", function() {
        $(\".displayval\").fadeIn();
    });
});
$(\"#saveprojectbtn\").click(function() {
	$(\"#saveprojectbtn\").hide();
	$(\"#cancelsaveprojectbtn\").hide();
	$(\"#editprojectbtn\").show();

	$(\"#saveprocess\").fadeIn();

    $.post(\"" . $modulelink . "&a=projectsave\", { title : $(\"#title input\").val(), created : $(\"#created input\").val(), adminid: $(\"#adminid select\").val(), userid: $(\"#userid\").val(), duedate: $(\"#duedate input\").val() },
    function (data) {
        $(\"#title .displayval\").html($(\"#title input\").val());
        $(\"#created .displayval\").html($(\"#created input\").val());
        $(\"#adminid .displayval\").html($(\"#adminid select option:selected\").text());
        $(\"#client .displayval\").html($(\"#clientname\").val());
        $(\"#duedate .displayval\").html($(\"#duedate input\").val());
		$(\"#daysleft\").html(data);
        $(\".editfield\").fadeOut(\"fast\", function() {
        	$(\".displayval\").fadeIn();
        });
		$(\"#quickcreatebtn\").removeAttr(\"disabled\");
		$(\"#dynamictasksinvoicegen\").removeAttr(\"disabled\");
        $(\"#saveprocess\").hide();
        $(\"#savesuccess\").show();
        $(\"#savesuccess\").fadeOut(5000);
    });

});
$(\"#projecttitleeditfield\").bind(\"keypress\", function(e) {
	if((e.keyCode ? e.keyCode : e.which) == 13) {
		$(\"#saveprojectbtn\").hide();
		$(\"#cancelsaveprojectbtn\").hide();
		$(\"#editprojectbtn\").show();

		$(\"#saveprocess\").fadeIn();

		$.post(\"" . $modulelink . "&a=projectsave\", { title : $(\"#title input\").val(), created : $(\"#created input\").val(), adminid: $(\"#adminid select\").val(), userid: $(\"#userid\").val(), duedate: $(\"#duedate input\").val() },
		function (data) {
			$(\"#title .displayval\").html($(\"#title input\").val());
			$(\"#created .displayval\").html($(\"#created input\").val());
			$(\"#adminid .displayval\").html($(\"#adminid select option:selected\").text());
			$(\"#client .displayval\").html($(\"#clientname\").val());
			$(\"#duedate .displayval\").html($(\"#duedate input\").val());
			$(\"#daysleft\").html(data);
			$(\".editfield\").fadeOut(\"fast\", function() {
				$(\".displayval\").fadeIn();
			});
			$(\"#quickcreatebtn\").removeAttr(\"disabled\");
			$(\"#dynamictasksinvoicegen\").removeAttr(\"disabled\");
			$(\"#saveprocess\").hide();
			$(\"#savesuccess\").show();
			$(\"#savesuccess\").fadeOut(5000);
		});
	}
});
$(document).on(\"click\",\".ajaxstarttimer\",function(){
    var extraParams = {
        taskid: $(this).attr(\"id\").replace(\"ajaxstarttimer\", \"\"),
    };
	$.post(\"" . $modulelink . "&a=starttimer\", { taskid: extraParams.taskid },function(data){ajaxstarttimercallback(data, extraParams)});

	function ajaxstarttimercallback(data,extraParams) {
		$(\".taskholder\"+extraParams.taskid+\":last\").after(data);
		$(\"#notasktimersexist\"+extraParams.taskid).hide();
	};
});
$(document).on(\"click\",\".ajaxendtimer\", function(){
    var extraParams = {
        taskid: $(this).attr(\"id\").replace(\"ajaxendtimertaskid\", \"\"),
		timerid: $(this).attr(\"rel\"),
    };

	$.post(\"" . $modulelink . "&a=endtimer&ajax=1\", { taskid: extraParams.taskid, timerid: extraParams.timerid },function(data){ajaxendtimercallback(data, extraParams)});

	function ajaxendtimercallback(data,extraParams) {
		data = $.parseJSON(data);
		$(\"#ajaxendtimertaskholderid\"+extraParams.timerid).html(data.time);
		$(\"#ajaxtimerstatusholderid\"+extraParams.timerid).html(data.duration);
	};

});
$(document).on(\"keyup\",\".dynamicbilldisplayhours\", function(){
	hms = $(this).val().split(\":\");
	hours = Number(hms[0])+Number(hms[1]/60)+Number(hms[2]/3600);
	thisidattrval = $(this).attr(\"id\").replace(\"dynamicbilldisplayhours\",\"\");
	$(\"#dynamicbillhours\"+thisidattrval).val(hours);
	amount = $(\"#dynamicbillhours\"+thisidattrval).val()*$(\"#dynamicbillrate\"+thisidattrval).val();
	$(\"#dynamicbillamount\"+thisidattrval).val(amount.toFixed(2));

});
$(document).on(\"keyup\",\".dynamicbillrate\", function(){	$(\"#dynamicbillamount\"+$(this).attr(\"id\").replace(\"dynamicbillrate\",\"\")).val(parseFloat($(\"#dynamicbillhours\"+$(this).attr(\"id\").replace(\"dynamicbillrate\",\"\")).val() * $(this).attr(\"value\")).toFixed(2));
});
$(document).on(\"click\",\".deleteticket\", function(){
	if (confirm('Are you sure to delete this ticket?')) {
		$.post(\"" . $modulelink . "&a=deleteticket\", { id: $(this).attr(\"id\").replace(\"deleteticket\", \"\") },
		   function(data) {
			   if(data!=0){
			   		$(\"#ticketholder\"+data).hide();
			   } else {
				   alert(\"" . $vars["_lang"]["youmustbeanadmintodeleteticket"] . "\");
			   }
		   });
	}
});
$(document).on(\"click\",\"#dynamictasksinvoicegen\", function(){
	$(\"#dynamictasksinvoiceloading\").show();
	$(\"#dynamictasksinvoicegen\").attr(\"disabled\",\"true\");
	dynamictasksinvoicegencalled = true;
	$.get(\"" . $modulelink . "&a=gettimesheet\", function(data) {
	  $(\"#dynamictasksinvoiceholder\").append(data);
	  $(\"#dynamictasksinvoiceloading\").hide();
	  $(\"#dynamictasksinvoicegen\").slideUp();
	  $(\"#dynamictasksinvoiceholder\").slideDown();
	});
});
$(document).on(\"click\",\"#dynamictasksinvoicecancel\", function(){
	$(\"#dynamictasksinvoiceloading\").show();
	$(\"#dynamictasksinvoiceholder\").html(\"\");
	$(\"#dynamictasksinvoicegen\").removeAttr(\"disabled\");
	$.get(\"" . $modulelink . "&a=gettimesheethead\", function(data) {
	  $(\"#dynamictasksinvoiceholder\").html(data);
		$(\"#dynamictasksinvoiceloading\").hide();
		$(\"#dynamictasksinvoiceholder\").slideUp();
		$(\"#dynamictasksinvoicegen\").slideDown();
	});
});
$(document).on(\"click\",\".deldynamictaskinvoice\", function(){
	$(\"#dynamictaskinvoiceitemholder\"+$(this).attr(\"id\").replace(\"deldynamictaskinvoice\",\"\")).remove();
});
$(document).on(\"click\",\".deletestaffmsg\", function(){
	if (confirm('" . $vars["_lang"]["confirmdeletestaffmsg"] . "')) {
		$.post(\"" . $modulelink . "&a=deletestaffmsg\", { id: $(this).attr(\"id\").replace(\"deletestaffmsg\", \"\") },
		   function(data) {
			   if(data!=0){
		   			$(\"#msg\"+data).hide();
			   } else {
				   alert(\"" . $vars["_lang"]["youmustbeanadmintodeletemsg"] . "\");
			   }
		   });
	}
});
$(\"#tasks\").tableDnD({
	onDrop: function(table, row) {
		$.post(\"" . $modulelink . "\", { a: \"savetasksorder\", torderarr: $(\"#tasks\").tableDnDSerialize() });
	},
    dragHandle: \"sortcol\"
});
$(document).on(\"click\",\".tasktimertoggle\", function(){
	$(\"#tasktimerexpandholder\"+$(this).attr(\"id\").replace(\"tasktimertoggleclicker\",\"\")).fadeToggle(\"slow\");
});
$(document).on(\"click\",\".tasktimerexpander\", function(){
	$(\"#tasktimerexpandholder\"+$(this).attr(\"id\").replace(\"ajaxstarttimer\",\"\")).fadeIn(\"slow\");
});
$(document).on(\"click\",\".tasknotestoggler\", function(){
	$(\"#tasknotesexpandholder\"+$(this).attr(\"id\").replace(\"tasknotestogglerclicker\",\"\")).fadeToggle(\"slow\");
});
$(document).on(\"click\",\".savetasknotestxtarea\", function(){
	var thisid = $(this).attr(\"id\");
	$(\"#\"+thisid).val(\"" . $vars["_lang"]["saving"] . "\");
	$.post(\"" . $modulelink . "\", { a: \"savetasknotes\", taskid:$(this).attr(\"id\").replace(\"savetasknotestxtarea\",\"\"), notes: $(\"#tasknotestxtarea\"+$(this).attr(\"id\").replace(\"savetasknotestxtarea\",\"\")).val() },
	function(data){
		if(data == \"1\"){
			$(\"#\"+thisid).hide().val(\"" . $vars["_lang"]["savenotes"] . "\").fadeIn(\"slow\");
		} else {
			$(\"#\"+thisid).hide().val(\"" . $vars["_lang"]["savenotesfailed"] . "\").fadeIn(\"slow\");
		}
	});
});
$(\".editstaffmsg\").click(function() {
	var msgid = $(this).attr(\"id\").replace(\"editstaffmsg\",\"\");
	$(\"#msgholder\"+msgid).hide();
	$(\"#msgeditorholder\"+msgid).fadeIn(\"slow\");
});
$(\".msgeditorsavechanges\").click(function() {
	var msgid = $(this).attr(\"id\").replace(\"msgeditorsavechanges\",\"\");
	var msgtxt =  $(\"#msgeditor\"+msgid).val();
	$.post(\"" . $modulelink . "\", { a: \"updatestaffmsg\", msgid:msgid, msgtxt: msgtxt },
	function(data){
		$(\"#msgeditorholder\"+msgid).hide();
		$(\"#msgholder\"+msgid).html(data);
		$(\"#msgholder\"+msgid).fadeIn(\"slow\");
	});
});
$(\"#clientname\").keyup(function () {
	var ticketuseridsearchlength = $(\"#clientname\").val().length;
	if (ticketuseridsearchlength>2) {
	$.post(\"search.php\", { ticketclientsearch: 1, value: $(\"#clientname\").val() },
	    function(data){
            if (data) {
                $(\"#ticketclientsearchresults\").html(data);
                $(\"#ticketclientsearchresults\").slideDown(\"slow\");
                $(\"#clientsearchcancel\").fadeIn();
            }
        });
	}
});
$(\"#clientsearchcancel\").click(function () {
    $(\"#ticketclientsearchresults\").slideUp(\"slow\");
    $(\"#clientsearchcancel\").fadeOut();
});
";
}

$jscode .= "function loadTaskList() {
    $(\"#loadtasklist\").dialog(\"open\");
}
function saveTaskList() {
    $(\"#savetasklist\").dialog(\"open\");
}
function uploadAttachment() {
    $(\".attachmentupload\").fadeToggle();
}
function deleteTask(id) {
	if (confirm('" . $vars["_lang"]["confirmdeletetask"] . "')) {
		$.post(\"" . $modulelink . "&a=deletetask\", { id: id },
		   function(data) {
			   if(data!=0){
			   		$(\"#taskholder\"+data).hide();
					$(\".taskholder\"+data).hide();
			   } else {
				   alert(\"" . addslashes( $vars["_lang"]["youmustbeanadmintodeletetask"] ) . "\");
			   }
		   });
	}
}
function deleteAttachment(id) {
    if (confirm(\"" . $vars["_lang"]["confirmdeleteattachment"] . "\")) {
        window.location='" . $modulelink . "&a=deleteattachment&i='+id;
    }
}
function deleteTimer(id,taskid) {
    if (confirm(\"" . $vars["_lang"]["confirmdeletetimer"] . "\")) {
        window.location='" . $modulelink . "&a=deletetimer&projectid=" . $projectid . "&id='+id+'&taskid='+taskid;
    }
}
function addtask() {
    if ($(\"#newtask\").val()) {
        $(\"#tasksnone\").fadeOut();
        $(\"#taskloading\").fadeIn();
        $.post(\"" . $modulelink . "\", { a: \"addtask\", newtask: $(\"#newtask\").val() },
	    function(data){
            $(\"#taskloading\").fadeOut(\"fast\", function() {
                $(\"#tasks tr#taskloading\").before(data);
                $(\"#newtask\").val(\"\");
                $.post(\"" . $modulelink . "\", { a: \"updatetask\" },
            	    function(data){
                        $(\"#taskssummary\").html(data);
                    });
            });
        });
    }
}
function updatetaskstatus(taskid) {
    $.post(\"" . $modulelink . "\", { a: \"updatetask\", taskid: taskid, status: $(\"#tk\"+taskid).attr(\"checked\") },
	    function(data){
            $(\"#taskssummary\").html(data);
        });
}
function addticket() {
    $(\"#assocticketsloading\").show();
    $.post(\"" . $modulelink . "\", { a: \"addticket\", ticketnum: $(\"#newticketid\").val() },
	    function(data){
            if (data.substring(0,20)=='<tr id=\"ticketholder') {
                $(\"#assocticketsnone\").fadeOut();
                $(\"#assoctickets tr:last\").after(data);
                $(\"#newticketid\").val(\"\");
            } else alert(data);
            $(\"#assocticketsloading\").fadeOut();
        });
}
function addinvoice() {
    $(\"#associnvoicesloading\").show();
    $.post(\"" . $modulelink . "\", { a: \"addinvoice\", invoicenum: $(\"#newinvoiceid\").val() },
	    function(data){
            if (data.substring(0,21)=='<tr id=\"invoiceholder') {
                $(\"#associnvoicesnone\").fadeOut();
                $(\"#associnvoices tr:last\").after(data);
                $(\"#newinvoiceid\").val(\"\");
				$(\"#noassociatedinvoicesfound\").hide();
            } else alert(data);
            $(\"#associnvoicesloading\").fadeOut();
        });
}";
echo $headeroutput;

if (project_management_checkperm( "Edit Project Details" )) {
	echo "
<div class=\"editbtn\"><a id=\"editprojectbtn\">" . $vars["_lang"]["edit"] . "</a><a id=\"saveprojectbtn\" style=\"display:none\">" . $vars["_lang"]["save"] . "</a>&nbsp;<a id=\"cancelsaveprojectbtn\" style=\"display:none\">" . $vars["_lang"]["cancel"] . "</a></div>
<div id=\"saveprocess\" class=\"loading\"><img src=\"images/loading.gif\" /> " . $vars["_lang"]["saving"] . "</div>
<div id=\"savesuccess\" class=\"loading\">" . $vars["_lang"]["changessaved"] . "</div>";
}

echo "<script src=\"../includes/jscript/jqueryro.js\"></script>

<div id=\"title\" class=\"title\"><div class=\"displayval\">" . $headtitle . "</div><div class=\"editfield\"><input id=\"projecttitleeditfield\" type=\"text\" value=\"" . $headtitle . "\" /></div></div>
<div id=\"daysleft\" class=\"daysleft\">" . $daysleft . "</div><br />

<div class=\"infobar\">

<table width=\"100%\">
<tr>
<th>" . $vars["_lang"]["created"] . "</th>
<th>" . $vars["_lang"]["assignedto"] . "</th>
<th>" . $vars["_lang"]["associatedclient"] . "</th>
<th>" . $vars["_lang"]["duedate"] . "</th>
<th>" . $vars["_lang"]["totaltime"] . "</th>
<th style=\"border:0;\">" . $vars["_lang"]["status"] . "</th>
</tr>
<tr>
<td id=\"created\"><div class=\"displayval\">" . $created . "</div><div class=\"editfield\"><input type=\"text\" class=\"datepick\" value=\"" . $created . "\" /></div></td>
<td id=\"adminid\"><div class=\"displayval\">" . $admin . "</div><div class=\"editfield\"><select><option value=\"0\">" . $vars["_lang"]["none"] . "</option>";
$totalprojecttime = project_management_sec2hms( get_query_val( "mod_projecttimes", "SUM(end-start)", array( "projectid" => $projectid, "end" => array( "sqltype" => "NEQ", "value" => "" ) ) ) );
$result = select_query( "tbladmins", "id,firstname,lastname", array( "disabled" => "0" ), "firstname` ASC,`lastname", "ASC" );

while ($data = mysql_fetch_array( $result )) {
	$aid = $data["id"];
	$adminfirstname = $data["firstname"];
	$adminlastname = $data["lastname"];
	echo "<option value=\"" . $aid . "\"";

	if ($aid == $adminid) {
		echo " selected";
	}

	echo ">" . $adminfirstname . " " . $adminlastname . "</option>";
}

echo "</select></div></td>
<td id=\"client\"><div class=\"displayval\">" . $client . "</div><div class=\"editfield\"><input type=\"hidden\" id=\"userid\" value=\"" . $userid . "\" /><input type=\"text\" id=\"clientname\" value=\"" . $clientname . "\" onfocus=\"if(this.value=='" . addslashes( $clientname ) . "')this.value=''\" /> <img src=\"images/icons/delete.png\" alt=\"Cancel\" class=\"absmiddle\" id=\"clientsearchcancel\" height=\"16\" width=\"16\"><div id=\"ticketclientsearchresults\" style=\"z-index:2000;\"></div></div></td>
<td id=\"duedate\"><div class=\"displayval\">" . $duedate . "</div><div class=\"editfield\"><input type=\"text\" class=\"datepick\" value=\"" . $duedate . "\" /></div></td>
<td><div>" . $totalprojecttime . "</div></td>
<td style=\"border:0;\"><div>";

if (project_management_checkperm( "Update Status" )) {
	echo "<select name=\"status\" id=\"statuschange\">";
	$statuses = explode( ",", $vars["statusvalues"] );
	foreach ($statuses as $status) {
		$status = explode( "|", $status, 2 );

		if ($status[1]) {
			echo "<option style=\"background-color:" . $status[1] . "\" value=\"" . $status[0] . "\"";
		}
		else {
			echo "<option value=\"" . $status[0] . "\"";
		}


		if ($status[0] == $projectstatus) {
			echo " selected";
		}

		echo ">" . $status[0] . "</option>";
	}

	echo "</select>";
}
else {
	echo $projectstatus;
}

echo "</div></td>
</tr>
</table>
</div>

<table width=\"100%\" align=\"center\"><tr><td width=\"50%\" valign=\"top\">";
global $currency;

$currency = getCurrency( $userid );
$gateways = getGatewaysArray();
$taskshtml = "";
$taski = $totaltimecount = 0;
$result = select_query( "mod_projecttasks", "", array( "projectid" => $projectid ), "order", "ASC" );

while ($data = mysql_fetch_array( $result )) {
	$taskid = $data["id"];
	$task = $data["task"];
	$taskadminid = $data["adminid"];
	$taskduedate = $data["duedate"];
	$tasknotes = $data["notes"];
	$taskcompleted = $data["completed"];
	$taskadmin = ($taskadminid ? "<span class=\"taskbox\">" . getAdminName( $data["adminid"] ) . "</span> " : "");
	$taskduedate = ($taskduedate != "0000-00-00" ? " <span class=\"taskdue\">" . project_management_daysleft( $data["duedate"], $vars ) . " (" . fromMySQLDate( $data["duedate"] ) . ")</span>" : "");
	$taskcompleted = ($taskcompleted ? " checked=\"checked\"" : "");
	$taskedit = (project_management_checkperm( "Edit Tasks" ) ? " <a href=\"" . str_replace( "&m=view", "&m=edittask", $modulelink ) . "&id=" . $taskid . "\"><img src=\"images/edit.gif\" align=\"absmiddle\" title=\"Edit Task\" /></a>" : "");
	$taskdelete = (project_management_checkperm( "Delete Tasks" ) ? " <a href=\"#\" onclick=\"deleteTask(" . $taskid . ");return false\"><img src=\"images/delete.gif\" align=\"absmiddle\" /></a>" : "");
	$notesoutput = "<div align=\"center\" style=\"margin-top:5px;\"><table width=\"95%\" align=\"center\"><tr><td><textarea rows=\"3\" style=\"width:100%\" id=\"tasknotestxtarea" . $taskid . "\">" . $tasknotes . "</textarea></td><td width=\"120\" align=\"right\"><input type=\"button\" id=\"savetasknotestxtarea" . $taskid . "\" class=\"savetasknotestxtarea\" value=\"" . $vars["_lang"]["savenotes"] . "\" /></td></tr></table></div>";
	$tasknotes = "<a class=\"tasknotestoggler\" id=\"tasknotestogglerclicker" . $taskid . "\"><img src=\"../modules/addons/project_management/images/" . ($tasknotes ? "" : "no") . "notes.png\" align=\"absmiddle\" title=\"View/Edit Notes\" /></a>";
	++$taski;
	$invoicelinedesc = ( "" . $taski . ". " . $task . "
" );
	$timesoutput = project_management_timesoutput( $vars, $taskid );
	$timerid = $GLOBALS["timerid"];
	$timecount = $GLOBALS["timecount"];
	$invoicelinedesc = $GLOBALS["invoicelinedesc"];
	$csstimerdisplay = (!get_query_val( "mod_projecttimes", "id", array( "end" => "", "projectid" => $projectid, "taskid" => $taskid, "adminid" => $_SESSION["adminid"] ) ) ? "style=\"display:none\"" : "");
	$taskshtml .= "<tr id=\"taskholder" . $taskid . "\">
    <td class=\"sortcol\"></td>
    <td>
		<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
			<tr><td width=\"35%\" align=\"left\"><input type=\"checkbox\" name=\"task[" . $taskid . "]\" id=\"tk" . $taskid . "\" value=\"1\"" . $taskcompleted . " onclick=\"updatetaskstatus('" . $taskid . "')\" /> " . $taskadmin . "<label for=\"tk" . $taskid . "\">" . $task . "</label> " . $taskduedate . " <span class=\"taskbox\">" . project_management_sec2hms( $timecount ) . " Hrs</span> " . $tasknotes . " <div style=\"float:right;\"><a class=\"ajaxstarttimer tasktimerexpander\" id=\"ajaxstarttimer" . $taskid . "\"><img src=\"../modules/addons/project_management/images/starttimer.png\" align=\"absmiddle\" title=\"Start Timer\" /></a> <a id=\"tasktimertoggleclicker" . $taskid . "\" class=\"tasktimertoggle\"><img src=\"../modules/addons/project_management/images/" . ($timerid ? "" : "no") . "times.png\" align=\"absmiddle\" title=\"View Times\" /></a> " . $taskedit . $taskdelete . "</div></td></tr>
			<tr " . $csstimerdisplay . " id=\"tasktimerexpandholder" . $taskid . "\"><td>" . $timesoutput . "</td></tr>
			<tr style=\"display:none\" id=\"tasknotesexpandholder" . $taskid . "\"><td>" . $notesoutput . "</td></tr>
		</table>
	</td>
</tr>";

	if ($createinvoice) {
		$invoicelineamt = $timecount / 3600 * $vars["hourlyrate"];
		insert_query( "tblinvoiceitems", array( "invoiceid" => $invoiceid, "userid" => $userid, "type" => "Project", "relid" => $projectid, "description" => $invoicelinedesc, "amount" => $invoicelineamt, "taxed" => "1" ) );
	}
}


if (!$taski) {
	$taskshtml .= "<tr id=\"tasksnone\"><td class=\"fieldarea\" align=\"center\">" . $vars["_lang"]["notasks"] . "</td></tr>";
}

$totalhours = project_management_sec2hms( $totaltimecount );
$taskstatusdata = project_management_tasksstatus( $projectid, $vars );
echo "

<div class=\"heading\"><img src=\"images/icons/todolist.png\" /> " . $vars["_lang"]["projecttasks"] . " <span class=\"stat\" id=\"taskssummary\">" . $taskstatusdata["html"] . "</span></div>

<div class=\"box\">
<table width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\" class=\"tasks\" id=\"tasks\">
" . $taskshtml . "
<tr class=\"loading\" id=\"taskloading\"><td colspan=\"2\" align=\"center\"><img src=\"images/loading.gif\"> " . $vars["_lang"]["updating"] . "</td></tr>
</table>
</div>

<div align=\"right\" style=\"padding:3px 20px;\"><input type=\"button\" value=\"" . $vars["_lang"]["savetasklisttpl"] . "\" onclick=\"saveTaskList()\" /> <input type=\"button\" value=\"" . $vars["_lang"]["loadtasklisttpl"] . "\" onclick=\"loadTaskList()\" /></div>

";

if (project_management_checkperm( "Create Tasks" )) {
	echo "<form onsubmit=\"addtask();return false\">
<div class=\"addtask\"><b>" . $vars["_lang"]["newtask"] . "</b> <input type=\"text\" id=\"newtask\" style=\"width:65%;\" /> <input type=\"submit\" value=\"" . $vars["_lang"]["add"] . "\" /></div>
</form>";
}

echo "

<div class=\"heading\"><img src=\"images/icons/massmail.png\" /> " . $vars["_lang"]["associatedtickets"] . " ";

if (project_management_checkperm( "Associate Tickets" )) {
	echo "<span class=\"stat\">" . $vars["_lang"]["add"] . " " . $vars["_lang"]["ticketnumberhash"] . " <input type=\"text\" id=\"newticketid\" size=\"10\" /> <a href=\"#\" onclick=\"addticket();return false\">" . $vars["_lang"]["add"] . " &raquo;</a></span><span id=\"assocticketsloading\" class=\"loading\"><img src=\"images/loading.gif\" /> " . $vars["_lang"]["validating"] . "</span>";
}

echo "</div><div class=\"tablebg\">
<table class=\"datatable\" width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"3\" id=\"assoctickets\">
<tr><th>" . $vars["_lang"]["date"] . "</th><th class=\"fieldarea\">" . $vars["_lang"]["subject"] . "</th><th>" . $vars["_lang"]["status"] . "</th><th>" . $vars["_lang"]["lastupdated"] . "</th><th></th></tr>
";
$ticketinvoicelinks = array();
$ticketid = $sometickets = "";
foreach ($ticketids as $i => $ticketnum) {

	if ($ticketnum) {
		$result = select_query( "tbltickets", "id,tid,date,title,status,lastreply", array( "tid" => $ticketnum ) );
		$data = mysql_fetch_array( $result );
		$ticketid = $data["id"];

		if ($ticketid) {
			$ticketdate = $data["date"];
			$ticketnum = $data["tid"];
			$tickettitle = $data["title"];
			$ticketstatus = $data["status"];
			$ticketlastreply = $data["lastreply"];
			$ticketinvoicelinks[] = "description LIKE '%Ticket #" . $ticketnum . "%'";

			if ($ticketid) {
				echo "<tr id=\"ticketholder" . $i . "\"><td>" . fromMySQLDate( $ticketdate, true ) . "</td><td class=\"left\"><a href=\"supporttickets.php?action=viewticket&id=" . $ticketid . "\" target=\"_blank\"><strong>#" . $ticketnum . " - " . $tickettitle . "</strong></a></td><td>" . getStatusColour( $ticketstatus ) . "</td><td>" . fromMySQLDate( $ticketlastreply, true ) . "</td><td>" . (project_management_checkperm( "Associate Tickets" ) ? "<a class=\"deleteticket\" id=\"deleteticket" . $i . "\"><img src=\"images/delete.gif\"></a>" : "") . "</td></tr>";
			}

			$sometickets = true;
			continue;
		}

		continue;
	}
}


if (!$sometickets) {
	echo "<tr id=\"assocticketsnone\"><td colspan=\"5\" align=\"center\">" . $vars["_lang"]["noassociatedticketsfound"] . "</td></tr>";
}

echo "</table>
</div>

<br />

<div class=\"heading\"><img src=\"images/icons/invoices.png\" />  " . $vars["_lang"]["associatedinvoices"] . " ";
echo "<span class=\"stat\">" . $vars["_lang"]["add"] . " " . $vars["_lang"]["invoicenumberhash"] . " <input type=\"text\" id=\"newinvoiceid\" size=\"10\" /> <a href=\"#\" onclick=\"addinvoice();return false\">" . $vars["_lang"]["add"] . " &raquo;</a></span><span id=\"associnvoicesloading\" class=\"loading\"><img src=\"images/loading.gif\" /> " . $vars["_lang"]["validating"] . "</span>";
echo "</div><div class=\"tablebg\">
<table class=\"datatable\" width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"3\" id=\"associnvoices\">
<tr><th>" . $vars["_lang"]["invoicenumberhash"] . "</th><th>" . $vars["_lang"]["created"] . "</th><th>" . $vars["_lang"]["datepaid"] . "</th><th>" . $vars["_lang"]["total"] . "</th><th>" . $vars["_lang"]["paymentmethod"] . "</th><th>" . $vars["_lang"]["status"] . "</th></tr>
";
$invoiceids = get_query_val( "mod_project", "invoiceids", array( "id" => $projectid ) );
$invoicesoutputarr = explode( ",", $invoiceids );
foreach ($invoicesoutputarr as $invoiceid) {

	if ($invoiceid) {
		$data = get_query_vals( "tblinvoices", "id,date,datepaid,total,paymentmethod,status", array( "id" => $invoiceid ) );
		echo "<tr><td><a href=\"invoices.php?action=edit&id=" . $data["id"] . "\">" . $data["id"] . "</a></td><td>" . fromMySQLdate( $data["date"] ) . "</td><td>" . fromMySQLdate( $data["datepaid"] ) . "</td><td>" . formatCurrency( $data["total"] ) . "</td><td>" . $gateways[$data["paymentmethod"]] . "</td><td>" . getInvoiceStatusColour( $data["status"] ) . "</td></tr>";
		continue;
	}
}

$invoiceid = "";
$ticketinvoicesquery = (!empty( $ticketinvoicelinks ) ? "(" . implode( " OR ", $ticketinvoicelinks ) . ") OR " : "");
$result = select_query( "tblinvoices", "", "id IN (SELECT invoiceid FROM tblinvoiceitems WHERE description LIKE '%Project #" . $projectid . "%' OR " . $ticketinvoicesquery . " (type='Project' AND relid='" . $projectid . "'))", "id", "ASC" );

while ($data = mysql_fetch_array( $result )) {
	$invoiceid = $data["id"];

	if (!in_array( $invoiceid, $invoicesoutputarr )) {
		echo "<tr><td><a href=\"invoices.php?action=edit&id=" . $data["id"] . "\">" . $data["id"] . "</a></td><td>" . fromMySQLdate( $data["date"] ) . "</td><td>" . fromMySQLdate( $data["datepaid"] ) . "</td><td>" . formatCurrency( $data["total"] ) . "</td><td>" . $gateways[$data["paymentmethod"]] . "</td><td>" . getInvoiceStatusColour( $data["status"] ) . "</td></tr>";
	}
}


if (( !$invoiceid && !$invoicesoutputarr )) {
	echo "<tr id=\"noassociatedinvoicesfound\"><td colspan=\"6\" align=\"center\">" . $vars["_lang"]["noassociatedinvoicesfound"] . "</td></tr>";
}

echo "</table>
</div>";
echo "<form method=\"post\" action=\"" . $modulelink . "&a=addquickinvoice\"><p align=\"center\"><b>" . $vars["_lang"]["quickinvoice"] . "</b> <input type=\"text\" name=\"newinvoice\"";

if (!$userid) {
	echo " disabled value=\"" . $vars["_lang"]["associateclienttousefeature"] . "\"";
}

echo " style=\"width:50%;\" />&nbsp;@&nbsp;<input type=\"text\" name=\"newinvoiceamt\" size=\"10\" ";

if (!$userid) {
	echo " disabled ";
}

echo " /> <input type=\"submit\" id=\"quickcreatebtn\" value=\"" . $vars["_lang"]["create"] . "\" ";

if (!$userid) {
	echo " disabled ";
}

echo "/><br /><br />";

if (project_management_checkperm( "Bill Tasks" )) {
	echo "<input type=\"button\" id=\"dynamictasksinvoicegen\" value=\"" . $vars["_lang"]["billfortasktimeentries"] . "\" ";

	if (!$userid) {
		echo " disabled ";
	}

	echo "/>";
}

echo "</p></form><div id=\"dynamictasksinvoiceholder\"></div><div align=\"center\" class=\"loading\" id=\"dynamictasksinvoiceloading\"><img src=\"images/loading.gif\"> " . $vars["_lang"]["preparing"] . "</div>

</div>

";
echo "</td><td width=\"50%\" valign=\"top\">";
echo "<div class=\"messages\">

<div class=\"title\"><img src=\"images/icons/attachment.png\" /> " . $vars["_lang"]["attachments"] . " <span class=\"stat\"><a href=\"#\" onclick=\"uploadAttachment();return false\"><img src=\"images/icons/add.png\" align=\"absmiddle\" border=\"0\" /> " . $vars["_lang"]["upload"] . " </a></span></div>";
$attachment = "";
$attachmentslist = get_query_val( "mod_project", "attachments", array( "id" => $projectid ) );
$attachments = explode( ",", $attachmentslist );
echo "<div class=\"box\" id=\"attachmentsholderbox\">
    <div class=\"padding\">";
foreach ($attachments as $i => $attachment) {

	if ($attachment) {
		$attachment = substr( $attachment, 7 );
		echo "<img src=\"images/icons/ticketspredefined.png\" align=\"top\" /> <a href=\"../modules/addons/project_management/project_management.php?action=dl&projectid=" . $projectid . "&i=" . $i . "\">" . $attachment . "</a> " . (project_management_check_masteradmin() ? "<a href=\"#\" onclick=\"deleteAttachment('" . $i . "');return false\"><img src=\"images/delete.gif\" align=\"absmiddle\" border=\"0\" /></a>" : "") . " &nbsp;&nbsp;&nbsp; ";
		continue;
	}
}


if (!$attachment) {
	echo $vars["_lang"]["noattachments"];
}

echo "
    </div>
</div>
<div class=\"attachmentupload\" id=\"attachmentsholder\" style=\"display:none\">
<form method=\"post\" action=\"" . $modulelink . "&a=addattachment\" enctype=\"multipart/form-data\">
<input type=\"file\" name=\"attachments[]\" size=\"30\" /> <a href=\"#\" id=\"addattachment\"><img src=\"images/icons/add.png\" align=\"absmiddle\" border=\"0\" /> " . $vars["_lang"]["addanother"] . "</a> <input type=\"submit\" value=\"" . $vars["_lang"]["upload"] . "\" />
<div id=\"attachments\"></div>
</form>
</div>
<br />

<div class=\"title\"><img src=\"images/icons/tickets.png\" /> " . $vars["_lang"]["staffmessageboard"] . "</div>";
echo "<form method=\"post\" action=\"" . $modulelink . "&a=addmsg\" enctype=\"multipart/form-data\">
<div class=\"msgreply\">
<textarea name=\"msg\"></textarea><br />
<img src=\"images/icons/attachment.png\" /> <strong>" . $vars["_lang"]["attachments"] . ":</strong> <input type=\"file\" name=\"attachments[]\" size=\"30\" /> <a href=\"#\" id=\"addmsgattachment\"><img src=\"images/icons/add.png\" align=\"absmiddle\" border=\"0\" /> " . $vars["_lang"]["addanother"] . "</a> <input type=\"submit\" value=\"Post\"" . (!$projectid ? " disabled" : "") . " />
<div id=\"msgattachments\"></div>
</div>
</form>";
$msgid = "";
$result = select_query( "mod_projectmessages", "*,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE tbladmins.id=mod_projectmessages.adminid) AS adminuser", array( "projectid" => $projectid ), "date", "DESC" );
$i = 33;

while ($data = mysql_fetch_array( $result )) {
	$msgid = $data["id"];
	$date = $data["date"];
	$message = strip_tags( $data["message"] );
	$attachments = $data["attachments"];
	$adminuser = $data["adminuser"];
	$dates = explode( " ", $date );
	$dates2 = explode( "-", $dates[0] );
	$dates = $dates[1];
	$dates = explode( ":", $dates );
	$date = date( "jS F Y @ H:ia", mktime( $dates[0], $dates[1], $dates[2], $dates2[1], $dates2[2], $dates2[0] ) );
	$attachments = explode( ",", $attachments );
	$attachment = "";
	foreach ($attachments as $num => $attach) {

		if ($attach) {
			$attachment .= "<img src=\"../images/article.gif\" align=\"absmiddle\" /> <a href=\"../modules/addons/project_management/project_management.php?action=dl&projectid=" . $projectid . "&msg=" . $msgid . "&i=" . $num . "\">" . substr( $attach, 7 ) . "</a>";
			continue;
		}
	}


	if ($attachment) {
		$attachment = "<br /><br /><strong>" . $vars["_lang"]["attachments"] . "</strong><br />" . $attachment;
	}

	echo "<div class=\"msg" . $i . "\" id=\"msg" . $msgid . "\"><div class=\"date\">" . $vars["_lang"]["postedby"] . " <strong>" . $adminuser . "</strong> " . $vars["_lang"]["on"] . " " . $date . "</div><div class=\"msg\"><div class=\"msgholder\" id=\"msgholder" . $msgid . "\">" . nl2br( ticketAutoHyperlinks( $message ) ) . "</div>" . $attachment;
	echo "<div style=\"display:none\" class=\"msgeditorholder" . $i . "\" id=\"msgeditorholder" . $msgid . "\"><textarea class=\"msgeditor\" id=\"msgeditor" . $msgid . "\">" . $message . "</textarea><input type=\"button\" class=\"msgeditorsavechanges\" id=\"msgeditorsavechanges" . $msgid . "\" value=\"" . $vars["_lang"]["savechanges"] . "\" /></div>";
	echo "<div class=\"actions\" align=\"right\"><a class=\"editstaffmsg\" id=\"editstaffmsg" . $msgid . "\"><img src=\"images/edit.gif\"></a>";

	if (project_management_checkperm( "Delete Messages" )) {
		echo "&nbsp;<a class=\"deletestaffmsg\" id=\"deletestaffmsg" . $msgid . "\"><img src=\"images/delete.gif\"></a>";
	}

	echo "</div></div></div><div class=\"clear\"></div>";

	if ($i == 1) {
		$i = 34;
	}

	$i = 33;
}


if (!$msgid) {
	echo "<div class=\"msgnone\">" . $vars["_lang"]["nomessagespostedyet"] . "</div>";
}

echo "</div>

</td></tr></table>

<h2>" . $vars["_lang"]["activitylog"] . "</h2>

";
$aInt->sortableTableInit( "nopagination" );
$tabledata = "";
$result = select_query( "mod_projectlog", "mod_projectlog.*,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE tbladmins.id=mod_projectlog.adminid) AS admin", array( "projectid" => $projectid ), "id", "DESC", "0,15" );

while ($data = mysql_fetch_array( $result )) {
	$date = $data["date"];
	$msg = $data["msg"];
	$admin = $data["admin"];
	$date = fromMySQLDate( $date, true );
	$tabledata[] = array( $date, "<div align=\"left\">" . $msg . "</div>", $admin );
}

echo $aInt->sortableTable( array( $vars["_lang"]["date"], $vars["_lang"]["logentry"], $vars["_lang"]["adminuser"] ), $tabledata );
echo "
<div align=\"right\" style=\"padding:0 10px;\"><a href=\"addonmodules.php?module=project_management&m=activity&projectid=" . $projectid . "\">" . $vars["_lang"]["viewall"] . " &raquo;</a></div>

";
$loadtpllisthtml = "<form method=\"post\" action=\"" . $vars["modulelink"] . "&m=view&projectid=" . $projectid . "&a=loadtasklist\" id=\"loadtasklistfrm\"><div align=\"center\"><select name=\"tasktplid\" style=\"width:250px;\">";
$tplid = "";
$result = select_query( "mod_projecttasktpls", "", "", "name", "ASC" );

while ($data = mysql_fetch_array( $result )) {
	$tplid = $data["id"];
	$loadtpllisthtml .= "<option value=\"" . $tplid . "\">" . $data["name"] . "</option>";
}


if (!$tplid) {
	$loadtpllisthtml .= "<option value=\"\">" . $vars["_lang"]["tasklisttplsnone"] . "</option>";
}

$loadtpllisthtml .= "</select></div></form>";
$savetxt = $aInt->lang( "global", "save" );

if (!$savetxt) {
	$savetxt = "Save";
}

$oktxt = $aInt->lang( "global", "ok" );

if (!$oktxt) {
	$oktxt = "OK";
}

echo $aInt->jqueryDialog( "savetasklist", $vars["_lang"]["savetasklisttpl"], "<div align=\"center\">" . $vars["_lang"]["tasklisttplname"] . ": <input type=\"text\" name=\"taskname\" id=\"taskname\" style=\"width:200px;\" /></div>", array( $savetxt => "$(this).dialog('close');$.post('" . $vars["modulelink"] . "&m=view&projectid=" . $projectid . ( "', { a: 'savetasklist', taskname: $('#taskname').val() });" ), $aInt->lang( "global", "cancel" ) => "" ), "", "", "" );
echo $aInt->jqueryDialog( "loadtasklist", $vars["_lang"]["loadtasklisttpl"], $loadtpllisthtml, array( $oktxt => "$('#loadtasklistfrm').submit();", $aInt->lang( "global", "cancel" ) => "" ), "", "", "" );
?>