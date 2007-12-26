<?php

/*
 * The XMLWriter generates xml from event objects. The only non-utility function is getXML() which takes in an
 * associative array of event objects and outputs the XML representation of that events object array as a string. 
 */
class XMLGenerator {

	/*
	 * Utility buffer to track development of XML text throughout different function calls.
	 */
	var $buff;
	
	/*
	 * Constructor for XMLWriter. Simply initializes buffer as empty.
	 */
	function XMLWriter() {
		$this->buff = "";
	}
	
	/*
	 * Returns the XML representation of events array as a String.
	 *
	 * @param $events - The events array to generate the XML document of
	 * @return - The XML representation of events array.
	 */
	function getXML($events) {
		
		return
			"<?xml version=\"1.0\" encoding=\"utf-8\"?>
			<!-- Created with Liquid XML Studio 1.0.7.0 (http://www.liquid-technologies.com) -->
			<calendar xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">"
			. $this->genEventsXML($events) . "</calendar>";
	}
	
	/*
	 * Clears the buffer.
	 */
	function clearBuffer() {
		$this->buff="";
	}
		
	/*
	 * Utility function to help generate the xml for the events. Iterates through the events array
	 * and generates correct XML in necessary tree structure. Returns a String. 
	 *
	 * @param $events - the array of events objects 
	 * @return - The array of event objects to XML form (minus head)
	 */
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
	
	/*
	 * Utility function to generate year open tag.
	 *
	 * @param $event - an event object.
	 * @return - XML open tag tailored to event object.
	 */
	function genYearOpenXML($event) {
		return 
		  "<year>
    			<year_name>".date("Y", $event['beginTime'])."</year_name>
		  ";
	}

	/*
	 * Returns XML close tag for year.
	 */	
	function genYearCloseXML() {
		return "</year>";
	}
	
	/*
	 * Utility function to generate month open tag.
	 *
	 * @param $event - an event object.
	 * @return - XML open tag tailored to event object.
	 */
	function genMonthOpenXML($event) {
		return
			"<month>
      			<month_name>".date("m", $event['beginTime'])."</month_name>
			";
	}
	
	/*
	 * Utility function to generate close tag for month.
	 */
	function genMonthCloseXML() {
		return "</month>";
	}
	
	/*
	 * Utility function to generate day open tag.
	 *
	 * @param $event - an event object.
	 * @return - XML open tag tailored to event object.
	 */
	function genDayOpenXML($event) {
		return
			"<day>
				<day_name>".date("d", $event['beginTime'])."</day_name>
			";
	}
	
	/*
	 * Utility function to generate close tag for day.
	 */
	function genDayCloseXML() {
		return "</day>";
	}
	
	/*
	 * Utility function to generate the XML for a single event object.
	 *
	 * @param $event - the event object to create XML for
	 * @return - the xml representation of the event object.
	 */
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