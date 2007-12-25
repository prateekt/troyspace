<?php

class XMLWriter {

	var $buff;
	
	function XMLWriter() {
		$this->buff = "";
	}
	
	function getXML($events) {
		
		return
			"<?xml version=\"1.0\" encoding=\"utf-8\"?>
			<!-- Created with Liquid XML Studio 1.0.7.0 (http://www.liquid-technologies.com) -->
			<calendar xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">"
			. $this->genEventsXML($events) . "</calendar>";
	}
	
	function clearBuffer() {
		$this->buff="";
	}
		
	function genEventsXML($events) {
	
		//init counters
		$cMonth = "";
		$cYear = "";
		$cDay = "";
		
		foreach($events as $event) {
			
			//get current event time attributes
			$eventMonth = date("m", $event['beginTime']);
			$eventYear = date("Y", $event['beginTime']);
			$eventDay = date("d", $event['beginTime']);
						
			if($eventYear==$cYear && $eventMonth==$cMonth && $eventDay==$cDay) {
				$this->buff = $this->buff . $this->genEventXML($event);
			}
			else if($eventYear==$cYear && $eventMonth==$cMonth) {
				$this->buff = $this->buff . $this->genDayCloseXML();
				$this->buff = $this->buff . $this->genDayOpenXML($event);
				$this->buff = $this->buff . $this->genEventXML($event);
			}
			else if($eventYear==$cYear) {
				$this->buff = $this->buff . $this->genDayCloseXML();				
				$this->buff = $this->buff . $this->genMonthCloseXML();
				$this->buff = $this->buff . $this->genMonthOpenXML($event);
				$this->buff = $this->buff . $this->genDayOpenXML($event);
				$this->buff = $this->buff . $this->genEventXML($event);
			}
			else {
				if(!($cMonth == "" && $cYear == "" && $cDay == "")) { //if not first case
					$this->buff = $this->buff . $this->genDayCloseXML();				
					$this->buff = $this->buff . $this->genMonthCloseXML();
					$this->buff = $this->buff . $this->genYearCloseXML();
				}
				$this->buff = $this->buff . $this->genYearOpenXML($event);			
				$this->buff = $this->buff . $this->genMonthOpenXML($event);
				$this->buff = $this->buff . $this->genDayOpenXML($event);
				$this->buff = $this->buff . $this->genEventXML($event);
			}
			
			//update counters
			$cMonth = $eventMonth;
			$cYear = $eventYear;
			$cDay = $eventDay;

		}
		
		//add final close tag to day, month, year
		$this->buff = $this->buff . $this->genDayCloseXML();				
		$this->buff = $this->buff . $this->genMonthCloseXML();
		$this->buff = $this->buff . $this->genYearCloseXML();
		
		return $this->buff;								
	}
	
	function genYearOpenXML($event) {
		return 
		  "<year>
    			<year_name>".date("Y", $event['beginTime'])."</year_name>
		  ";
	}
	
	function genYearCloseXML() {
		return "</year>";
	}
	
	function genMonthOpenXML($event) {
		return
			"<month>
      			<month_name>".date("m", $event['beginTime'])."</month_name>
			";
	}
	
	function genMonthCloseXML() {
		return "</month>";
	}
	
	function genDayOpenXML($event) {
		return
			"<day>
				<day_name>".date("d", $event['beginTime'])."</day_name>
			";
	}
	
	function genDayCloseXML() {
		return "</day>";
	}
	
	function genEventXML($event) {
       return 
		"<event>
          <name>".$event['title']."</name>
          <start_time>
            <hour>".date("H", $event['beginTime'])."</hour>
            <minute>".date("i",$event['beginTime'])."</minute>
          </start_time>
          <end_time>
            <hour>".date("H", $event['endTime'])."</hour>
            <minute>".date("i", $event['endTime'])."</minute>
          </end_time>
          <location>".$event['where']."</location>
          <description>".$event['desc']."</description>
        </event>";
	}
}

?>