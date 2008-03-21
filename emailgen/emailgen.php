<?php
session_start();
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
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="800" height="250">
  <param name="movie" value="../email/EmailCutAndPasteWindow.swf" />
  <param name="quality" value="high" />
  <embed src="../email/EmailCutAndPasteWindow.swf" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="800" height="250"></embed>
</object>
<?php
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
$includeCampusEvents = $_GET['includeCampusEvents'];
$calendarReq = $_GET['calendar'];

//set base calendar
if($calendarReq=="Parkside") {
	$calendar[0]="http://www.google.com/calendar/feeds/p1jkqpkrqeltabq915v3nq7k9k@group.calendar.google.com/public/basic";
}
else if($calendarReq=="East") {
	$calendar[0]="http://www.google.com/calendar/feeds/vlrn9o4kqb9l0u5kg17kvqrrt4@group.calendar.google.com/public/basic";
}
else if($calendarReq=="North") {
	$calendar[0]="http://www.google.com/calendar/feeds/r2qnh7d3mttl3bbvbvbptm01us@group.calendar.google.com/public/basic";

}
else if($calendarReq=="South") {
	$calendar[0]="http://www.google.com/calendar/feeds/m9ijld6jui1n7348ln82fet7po@group.calendar.google.com/public/basic";

}
else if($calendarReq=="West") {
	$calendar[0]="http://www.google.com/calendar/feeds/6j4lo836fsfdebnlrp3tu071es@group.calendar.google.com/public/basic";
}
else{
	$calendar[0]="http://www.google.com/calendar/feeds/p1jkqpkrqeltabq915v3nq7k9k@group.calendar.google.com/public/basic";
}

//if include campus calendar, include it.
if($includeCampusEvents=="true") {
	$calendar[1]="http://www.google.com/calendar/feeds/usccalendar@gmail.com/public/basic";
}

//query data with begin,end
$beginTimeStr = $g->getBeginTimeStr(mktime(0,0,0,$beginMonth,$beginDay,$beginYear));
$endTimeStr = $g->getEndTimeStr(mktime(23,59,59, $endMonth, $endDay, $endYear));
$events = $g->getEventsListing($beginTimeStr, $endTimeStr, $calendar, $GLOBALS['cache_location'], 5000000);

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
	 $template = $template . "<p style=\"font-family: Arial, Helvetica, sans-serif;font-size: 18pt;font-weight: bold;color: #0033FF;\">" . date("l (n/d)", $event['beginTime']) . "</p>";
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