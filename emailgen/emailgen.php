<?php
session_start();

include ("../backend/gcal_api/GCalManager.inc");
include_once "../backend/constants.php";

//init environment
if(!ini_get('safe_mode'))
	putenv($GLOBALS['locale']);
$g = new GCalManager();

//start-end parameters
$beginDay = date("d");
$beginMonth = date("m");
$beginYear = date("Y");
$next  = mktime(0, 0, 0, date("m")  , date("d")+7, date("Y"));
$endDay = date("d", $next);
$endMonth = date("m", $next);
$endYear = date("Y", $next);
$sessionKey = "calendar_data";

$calendar[0]="http://www.google.com/calendar/feeds/p1jkqpkrqeltabq915v3nq7k9k@group.calendar.google.com/public/basic";
$calendar[1]="http://www.google.com/calendar/feeds/usccalendar@gmail.com/public/basic";
$beginTimeStr = $g->getBeginTimeStr(mktime(0,0,0,$beginMonth,$beginDay,$beginYear));
$endTimeStr = $g->getEndTimeStr(mktime(23,59,59, $endMonth, $endDay, $endYear));
$events = $g->getEventsListing($beginTimeStr, $endTimeStr, $calendar, $GLOBALS['cache_location'], 5000000);

echo "<BR>" . $beginDay . " " . $beginMonth . " " .$beginYear;

if($events===false) {
	echo "No events scheduled for this time period.";
} else {
	
	$dayCompare = "counter_init";
	foreach($events as $event) {
		if($dayCompare!=date("d", $event['beginTime'])) {
			echo genEventCode($event, true);
		}
		else {
			echo genEventCode($event,false);
		}
		$dayCompare = date("d", $event['beginTime']);
	}
}

?>

<?php
//functions
function genEventCode($event, $includeDate) {
	$template = 
	"<body>";
	if($includeDate) {
	 $template = $template . "<p style=\"font-family: Arial, Helvetica, sans-serif;font-size: 18pt;font-weight: bold;color: #0033FF;\">" . date("l (m/d)", $event['beginTime']) . "</p>";
	}
	$template = $template . 
	 "<ul>
	   <li><span style=\"font-family: Arial, Helvetica, sans-serif;font-size: 18pt;font-weight: bold;color: #FF0000;\"><strong>"  . $event['title'] . ":</strong></span><strong><span class=\"style2\"> <span style=\"font-family: Arial, Helvetica, sans-serif;font-size: 10pt;color: #009900;\">". $event['where'] ."&nbsp;&nbsp;</span></span><span style=\"font-family: Arial, Helvetica, sans-serif;font-size: 10pt;font-weight: bold;color: #6633FF;\">" . date("g:i a", $event['beginTime']). "</span><span class=\"style2\"><br>
		  <em style=\"font-family: Arial, Helvetica, sans-serif;font-size: 10pt;font-weight: bold;color: #666666;\">" . $event['desc']."</em></span></strong></li>
	 </ul>
	</body>";	
	return $template;
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.Date {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 18pt;
	font-weight: bold;
	color: #0033FF;
}
.name {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 18pt;
	font-weight: bold;
	color: #FF0000;
}
.time {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-weight: bold;
	color: #6633FF;
}
.location {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	color: #009900;
}
.description {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
	font-weight: bold;
	color: #666666;
}
-->
</style>
</head>
