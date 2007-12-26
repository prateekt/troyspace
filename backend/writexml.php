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
$beginDay = $_GET['beginDay'];
$beginMonth = $_GET['beginMonth'];
$beginYear = $_GET['beginYear'];
$endDay = $_GET['endDay'];
$endMonth = $_GET['endMonth'];
$endYear = $_GET['endYear'];
$sessionKey = $_GET['sessionKey'];

$calendar[0] = "http://www.google.com/calendar/feeds/usccalendar@gmail.com/public/basic";


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