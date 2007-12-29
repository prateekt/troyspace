<?php

include ("gcal_api/GCalManager.inc");
include_once "xml_generator/XMLGenerator.php";
include_once "file_writer/FileWriter.php";

//init environment
session_start();
if(!ini_get('safe_mode'))
	putenv($GLOBALS['locale']);
$g = new GCalManager();

//get parameters
/*$beginDay = $_GET['beginDay'];
$beginMonth = $_GET['beginMonth'];
$beginYear = $_GET['beginYear'];
$endDay = $_GET['endDay'];
$endMonth = $_GET['endMonth'];
$endYear = $_GET['endYear'];
$sessionKey = $_GET['sessionKey'];*/

//hard-coded parameters for this release
$beginDay = 1;
$beginMonth = 1;
$beginYear = 2007;
$endDay = 31;
$endMonth = 12;
$endYear = date("Y");
$sessionKey = "calendar_data";

$calendar[0]="http://www.google.com/calendar/feeds/usccalendar@gmail.com/public/basic";
$calendar[1]="http://www.google.com/calendar/feeds/r2qnh7d3mttl3bbvbvbptm01us@group.calendar.google.com/public/basic";
$calendar[2]="http://www.google.com/calendar/feeds/m9ijld6jui1n7348ln82fet7po@group.calendar.google.com/public/basic";
$calendar[3]="http://www.google.com/calendar/feeds/vlrn9o4kqb9l0u5kg17kvqrrt4@group.calendar.google.com/public/basic";
$calendar[4]="http://www.google.com/calendar/feeds/6j4lo836fsfdebnlrp3tu071es@group.calendar.google.com/public/basic";
$calendar[5]="http://www.google.com/calendar/feeds/p1jkqpkrqeltabq915v3nq7k9k@group.calendar.google.com/public/basic";

$beginTimeStr = $g->getBeginTimeStr(mktime(0,0,0,$beginMonth,$beginDay,$beginYear));
$endTimeStr = $g->getEndTimeStr(mktime(23,59,59, $endMonth, $endDay, $endYear));
$events = $g->getEventsListing($beginTimeStr, $endTimeStr, $calendar, $GLOBALS['cache_location']);

if($events===false) {
	echo "No events scheduled for this week.";
} else {
	$xmlWriter = new XMLGenerator();
	$fileWriter = new FileWriter();
	$fileWriter->writeFile("xml_storage/" . $sessionKey . ".xml", $xmlWriter->getXML($events));
}

?>