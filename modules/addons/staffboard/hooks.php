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
 * */

function widget_staffboard_overview($vars) {
	global $_ADMINLANG;

	$title = "Staff Noticeboard";
	$lastviews = get_query_val( "tbladdonmodules", "value", array( "module" => "staffboard", "setting" => "lastviewed" ) );

	if ($lastviews) {
		$lastviews = unserialize( $lastviews );
		$new = false;
	}
	else {
		$lastviews = array();
		$new = true;
	}

	$lastviewed = $lastviews[$_SESSION['adminid']];
	$lastviews[$_SESSION['adminid']] = time();

	if ($new) {
		insert_query( "tbladdonmodules", array( "module" => "staffboard", "setting" => "lastviewed", "value" => serialize( $lastviews ) ) );
	}
	else {
		update_query( "tbladdonmodules", array( "value" => serialize( $lastviews ) ), array( "module" => "staffboard", "setting" => "lastviewed" ) );
	}

	$numchanged = get_query_val( "mod_staffboard", "COUNT(id)", "date>='" . date( "Y-m-d H:i:s", $lastviewed ) . "'" );
	$content = "
<style>
.staffboardchanges {
    margin: 0 0 5px 0;
    padding: 8px 25px;
    font-size: 1.2em;
    text-align: center;
}
.staffboardnotices {
    max-height: 130px;
    overflow: auto;
    border-top: 1px solid #ccc;
    border-bottom: 1px solid #ccc;
}
.staffboardnotices div {
    padding: 5px 15px;
    border-bottom: 2px solid #fff;
}
.staffboardnotices div.pink {
    background-color: #F3CBF3;
}
.staffboardnotices div.yellow {
    background-color: #FFFFC1;
}
.staffboardnotices div.purple {
    background-color: #DCD7FE;
}
.staffboardnotices div.white {
    background-color: #FAFAFA;
}
.staffboardnotices div.pink {
    background-color: #F3CBF3;
}
.staffboardnotices div.blue {
    background-color: #A6E3FC;
}
.staffboardnotices div.green {
    background-color: #A5F88B;
}
</style>
<div class=\"staffboardchanges\">There are <strong>" . $numchanged . "</strong> New or Updated Staff Notices Since your Last Visit - <a href=\"addonmodules.php?module=staffboard\">Visit Noticeboard &raquo;</a></div><div class=\"staffboardnotices\">";
	$result = select_query( "mod_staffboard", "", "", "date", "DESC" );

	while ($data = mysql_fetch_array( $result )) {
		$content .= "<div class=\"" . $data['color'] . "\">" . fromMySQLDate( $data['date'], 1 ) . " - " . (100 < strlen( $data['note'] ) ? substr( $data['note'], 0, 100 ) . "..." : $data['note']) . "</div>";
	}

	$content .= "</div>";
	return array( "title" => $title, "content" => $content, "jquerycode" => $jquerycode );
}


if (!defined( "WHMCS" )) {
	exit( "This file cannot be accessed directly" );
}

add_hook( "AdminHomeWidgets", 1, "widget_staffboard_overview" );
?>