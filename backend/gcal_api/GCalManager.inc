<?php

class GCalManager {
	
	function GCalManager(){
	}

	function getEventsListing($startDate, $endDate, $calendarListing, $cache_location) {
		$oneEventExists = false;
		
		foreach($calendarListing as $calendar_xml_address) {
			$url = $calendar_xml_address . "?max-results=1000000&start-min=" . $startDate . "&start-max=" . $endDate;
			$feed = new SimplePie();
			$feed->feed_url($url);
			$feed->cache_location($cache_location);
			$feed->init();

			echo $url;

			if($feed->get_item_quantity()==0)
				continue;
			else
				$oneEventExists = true;
			
			foreach($feed->get_items() as $item) {
			
				//Extract id
				$id = $item->get_id();

				// Extract the 'where' information from the item summary, and ignore everything else
				$where = $item->get_description();
				$ignore_where=false;
				if (strpos($where,"Where: ")<1) {$ignore_where=true;}
					$where = substr($where,strpos($where,"Where: ")+6);
				$where = trim(substr($where,0,strpos($where,"&lt;")));
				if (substr($where,0,2)=="20" or $ignore_where) {$where = "";} // If there's no location, just ignore it

				// Extra event description
				$desc = $item->get_description();
				if(strpos($desc, "Event Description: ") < 1) {
					$desc = "";
				} else {
					$desc = substr($desc, strpos($desc, "Event Description: ")+18);
				}

				// Extract the link for Google Calendar
				$url=$item->get_links();

				//Extract when information
				$from = $item->get_description();

				if(strpos($from, 'Recurring Event') === false) { //Not a recurring event
					$from = trim(substr($from,strpos($from,"When: ")+6));
					$pos = strpos($from, 'to');
					$beginTimeStr = trim(substr($from,0,$pos));
					$beginTime = strtotime(trim(substr($from,0,$pos)));	 
					$strEndPos = strpos($from,'&');
					$toStr = substr($from,$pos+2, $strEndPos - $pos +2);
					if(strpos($toStr, ',') > -1) {
						$endTime = strtotime(substr($toStr, 0, strpos($toStr, '&')));
					} else {
						$lastSpace =  strlen($beginTimeStr) - strpos(strrev($beginTimeStr), ' ');
						$endTimeStr = substr($beginTimeStr, 0, $lastSpace) . $toStr;
						$endTimeStr = substr($endTimeStr, 0,  strpos($endTimeStr, '&'));
						$endTime = strtotime($endTimeStr);
					}
					$events[]=array('beginTime'=>$beginTime, 'endTime'=>$endTime, 'where'=>$where,'title'=>$item->get_title(), 'desc'=>			$desc,'url'=>$url[0], 'id'=>$id);
				}
				else { //Recurring  event
					$evtStartStr = trim(substr($from, strpos($from, "First start: ")+12));
					$evtStartStr = trim(substr($evtStartStr, 0, 23));
					$beginTime = strtotime($evtStartStr);

					$durationStr = trim(substr($from, strpos($from, "Duration: ")+9));
					$durationStr = trim(substr($durationStr,0, strpos($durationStr, ' ')));
					$durationInt = (int) $durationStr;

					$queryStart = strtotime($startDate);
					$queryEnd = strtotime($endDate);
					$counter = $queryStart;
					while($counter < $queryEnd) {
						if(date("D", $counter)== date("D", $beginTime)) {
							$beginTimeReal =  mktime(date("H", $beginTime), date("i", $beginTime), date("s", $beginTime), date("m", $counter), date("d", $counter), date("Y", $counter));
							$endTime = $beginTimeReal + $durationInt; 
							$events[]=array('beginTime'=>$beginTimeReal, 'endTime'=>$endTime, 'where'=>$where,'title'=>$item->get_title(), 'desc'=>$desc,'url'=>$url[0],'id'=>$id);						
						}
						$counter = $counter + 24*60*60;
					}				
				}			
			}
		}
		if($oneEventExists==false)
			return false;
		else {
			sort($events);
			return $events;
		}
	}

	function getCurrentTimeStamp() {
		return time();
	}
	
	function getNextWeekTimeStamp($currentTime) {
		return $currentTime + (7 * 24 * 60 * 60);
	}
	
	function getBeginTimeStr($timeStamp) {
		return date("Y-m-d", $timeStamp) . "T00:00:00";
	}
	
	function getEndTimeStr($timeStamp) {
		return date("Y-m-d", $timeStamp) . "T23:59:59";
	}
	
	function login($email, $password) {
		$url = "https://www.google.com/accounts/ClientLogin";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 4);
		curl_setopt($ch, CURLOPT_POSTFIELDS,"Email=" .$email. "&Passwd=" .$password. "&source=troyspace-client-1&service=cl");
		ob_start();
		curl_exec ($ch);
		curl_close ($ch);
		$result = ob_get_contents();
		ob_end_clean();
		if(!$result)
			return false;
		if(strpos($result, "Error=") === false) {
			$authIndex = strpos($result, "Auth=")+5;
			$authToken = trim(substr($result, $authIndex));
			return $authToken;
		}
		else {
			return false;
		}
	}

	function  createEvent($title, $content, $location, $beginTime, $endTime, $url, $authToken) {
		$title = htmlentities($title);
		$content = htmlentities($content);
		$location = htmlentities($location);
		$beginTime = htmlentities($beginTime);
		$endTime = htmlentities($endTime);	
		$xmlEntry = "<entry xmlns='http://www.w3.org/2005/Atom' xmlns:gd='http://schemas.google.com/g/2005'>
		<category scheme='http://schemas.google.com/g/2005#kind'
		term='http://schemas.google.com/g/2005#event'></category>
		<title type='text'>" . $title ."</title>
		<content type='text'>" . $content . "</content>
		<gd:transparency
		value='http://schemas.google.com/g/2005#event.opaque'>
		</gd:transparency>
		<gd:eventStatus
		value='http://schemas.google.com/g/2005#event.confirmed'>
		</gd:eventStatus>
		<gd:where valueString='" . $location . "'></gd:where>
		<gd:when startTime='". $beginTime . "'
		endTime='" . $endTime . "'></gd:when>
		</entry>";
		$header[] = "Content-type: application/atom+xml";
		$header[] = "Accept: text/xml";
		$header[] = "Content-length: ".strlen($xmlEntry);
		$header[] = "Authorization: GoogleLogin auth=" . $authToken;
		$header[] = "Cache-Control: no-cache";
		$header[] = "Connection: close \r\n";
		$header[] = $xmlEntry;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // allow redirects
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		ob_start();
		curl_exec ($ch);
		curl_close ($ch);
		$result = ob_get_contents();
		ob_end_clean();
		if(!$result)
			return false;
		else
			return $result;
	}

	function  getDeleteLink($url, $authToken) {
		$header[] = "Content-type: application/atom+xml";
		$header[] = "Accept: text/xml";
		$header[] = "Content-length: 0";
		$header[] = "Authorization: GoogleLogin auth=" . $authToken;
		$header[] = "Cache-Control: no-cache";
		$header[] = "Connection: close \r\n";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // allow redirects
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'GET');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		ob_start();
		curl_exec ($ch);
		curl_close ($ch);
		$result = ob_get_contents();
		if(!$result)
			return false;
		ob_end_clean();
		$tagToEnd = substr($result, strpos($result, "link rel='edit'")+15);
		$indexOfBeginning = strpos($tagToEnd, "href='")+6;
		$tagToEnd = substr($tagToEnd, $indexOfBeginning);
		$link = substr($tagToEnd, 0, strpos($tagToEnd, ">")-2);
		return $link;
	}
	
	function deleteEvent($deleteUrl, $authToken) {
		$header[] = "Content-type: application/atom+xml";
		$header[] = "Accept: text/xml";
		$header[] = "Content-length: 0";
		$header[] = "Authorization: GoogleLogin auth=" . $authToken;
		$header[] = "Cache-Control: no-cache";
		$header[] = "Connection: close \r\n";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$deleteUrl);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // allow redirects
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'DELETE');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		ob_start();
		curl_exec ($ch);
		curl_close ($ch);
		$result = ob_get_contents();
		ob_end_clean();
		if(!$result)
			return false;
		else
			return $result;
	}

}
?>