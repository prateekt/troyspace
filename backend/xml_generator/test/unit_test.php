<?php

include_once "../XMLGenerator.php";

$w = new XMLGenerator();

$events[]=array('beginTime'=>2342342, 'endTime'=>2342342, 'where'=>"posse room",'title'=>"Ping Pong", 'desc'=>"Parkside Battle");						
$events[]=array('beginTime'=>234221342, 'endTime'=>233422, 'where'=>"gage room",'title'=>"rger e", 'desc'=>"Parkside Battle Part 2");

echo $w->getXML($events);

?>
