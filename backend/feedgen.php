<?
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>

<style type="text/css">
<!--
.alertHd {border-bottom: 2px solid #FFCC00;
background-color: #CCCCCC;
text-align: center;
font-family: Verdana;
font-weight: bold;
font-size: 11px;
color: #404040;}

.alertBt {border-top: 2px solid #FFCC00;
background-color: #CCCCCC;
text-align: center;
font-family: Verdana;
font-weight: bold;
font-size: 11px;
color: #404040;}
.eventHeader {border-bottom: 2px solid #FFCC00;
border-top: 2px solid #FFCC00;
background-color: #cccccc;
text-align: center;
font-family: Verdana;
font-weight: bold;
font-size: 11px;
color: #FFFFFF;}
-->

</style>

</head>
<? /* ************************************************************** 
* htmlwrap() function - v1.6 
* Copyright (c) 2004-2005 Brian Huisman AKA GreyWyvern 
* 
* This program may be distributed under the terms of the GPL 
*   - http://www.gnu.org/licenses/gpl.txt 
* 
* 
* htmlwrap -- Safely wraps a string containing HTML formatted text (not 
* a full HTML document) to a specified width 
* 
* 
* Requirements 
*   htmlwrap() requires a version of PHP later than 4.1.0 on *nix or 
* 4.2.3 on win32. 
* 
* 
* Changelog 
* 1.6  - Fix for endless loop bug on certain special characters 
*         - Reported by Jamie Jones & Steve 
* 
* 1.5  - Tags no longer bulk converted to lowercase 
*         - Fixes a bug reported by Dave 
* 
* 1.4  - Made nobreak algorithm more robust 
*         - Fixes a bug reported by Jonathan Wage 
* 
* 1.3  - Added automatic UTF-8 encoding detection 
*      - Fixed case where HTML entities were not counted correctly 
*      - Some regexp speed tweaks 
* 
* 1.2  - Removed nl2br feature; script now *just* wraps HTML 
* 
* 1.1  - Now optionally works with UTF-8 multi-byte characters 
* 
* 
* Description 
* 
* string htmlwrap ( string str [, int width [, string break [, string nobreak]]]) 
* 
* htmlwrap() is a function which wraps HTML by breaking long words and 
* preventing them from damaging your layout.  This function will NOT 
* insert <br /> tags every "width" characters as in the PHP wordwrap() 
* function.  HTML wraps automatically, so this function only ensures 
* wrapping at "width" characters is possible.  Use in places where a 
* page will accept user input in order to create HTML output like in 
* forums or blog comments. 
* 
* htmlwrap() won't break text within HTML tags and also preserves any 
* existing HTML entities within the string, like &nbsp; and &lt;  It 
* will only count these entities as one character. 
* 
* The function also allows you to specify "protected" elements, where 
* line-breaks are not inserted.  This is useful for elements like <pre> 
* if you don't want the code to be damaged by insertion of newlines. 
* Add the names of the elements you wish to protect from line-breaks as 
* as a space separate list to the nobreak argument.  Only names of 
* valid HTML tags are accepted.  (eg. "code pre blockquote") 
* 
* htmlwrap() will *always* break long strings of characters at the 
* specified width.  In this way, the function behaves as if the 
* wordwrap() "cut" flag is always set.  However, the function will try 
* to find "safe" characters within strings it breaks, where inserting a 
* line-break would make more sense.  You may edit these characters by 
* adding or removing them from the $lbrks variable. 
* 
* htmlwrap() is safe to use on strings containing UTF-8 multi-byte 
* characters. 
* 
* See the inline comments and http://www.greywyvern.com/php.php 
* for more info 
******************************************************************** */ 

function htmlwrap($str, $width = 50, $break = "\n", $nobreak = "") { 

  // Split HTML content into an array delimited by < and > 
  // The flags save the delimeters and remove empty variables 
  $content = preg_split("/([<>])/", $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY); 

  // Transform protected element lists into arrays 
  $nobreak = explode(" ", strtolower($nobreak)); 

  // Variable setup 
  $intag = false; 
  $innbk = array(); 
  $drain = ""; 

  // List of characters it is "safe" to insert line-breaks at 
  // It is not necessary to add < and > as they are automatically implied 
  $lbrks = "/?!%)-}]\\\"':;&"; 

  // Is $str a UTF8 string? 
  $utf8 = (preg_match("/^([\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$/", $str)) ? true : false; 

  while (list(, $value) = each($content)) { 
    switch ($value) { 

      // If a < is encountered, set the "in-tag" flag 
      case "<": $intag = true; break; 

      // If a > is encountered, remove the flag 
      case ">": $intag = false; break; 

      default: 

        // If we are currently within a tag... 
        if ($intag) { 

          // Create a lowercase copy of this tag's contents 
          $lvalue = strtolower($value); 

          // If the first character is not a / then this is an opening tag 
          if ($lvalue{0} != "/") { 

            // Collect the tag name    
            preg_match("/^(\w*?)(\s|$)/", $lvalue, $t); 

            // If this is a protected element, activate the associated protection flag 
            if (in_array($t[1], $nobreak)) array_unshift($innbk, $t[1]); 

          // Otherwise this is a closing tag 
          } else { 

            // If this is a closing tag for a protected element, unset the flag 
            if (in_array(substr($lvalue, 1), $nobreak)) { 
              reset($innbk); 
              while (list($key, $tag) = each($innbk)) { 
                if (substr($lvalue, 1) == $tag) { 
                  unset($innbk[$key]); 
                  break; 
                } 
              } 
              $innbk = array_values($innbk); 
            } 
          } 

        // Else if we're outside any tags... 
        } else if ($value) { 

          // If unprotected... 
          if (!count($innbk)) { 

            // Use the ACK (006) ASCII symbol to replace all HTML entities temporarily 
            $value = str_replace("\x06", "", $value); 
            preg_match_all("/&([a-z\d]{2,7}|#\d{2,5});/i", $value, $ents); 
            $value = preg_replace("/&([a-z\d]{2,7}|#\d{2,5});/i", "\x06", $value); 

            // Enter the line-break loop 
            do { 
              $store = $value; 

              // Find the first stretch of characters over the $width limit 
              if (preg_match("/^(.*?\s)?(\S{".$width."})(?!(".preg_quote($break, "/")."|\s))(.*)$/s".(($utf8) ? "u" : ""), $value, $match)) { 

                if (strlen($match[2])) { 
                  // Determine the last "safe line-break" character within this match 
                  for ($x = 0, $ledge = 0; $x < strlen($lbrks); $x++) $ledge = max($ledge, strrpos($match[2], $lbrks{$x})); 
                  if (!$ledge) $ledge = strlen($match[2]) - 1; 

                  // Insert the modified string 
                  $value = $match[1].substr($match[2], 0, $ledge + 1).$break.substr($match[2], $ledge + 1).$match[4]; 
                } 
              } 

            // Loop while overlimit strings are still being found 
            } while ($store != $value); 

            // Put captured HTML entities back into the string 
            foreach ($ents[0] as $ent) $value = preg_replace("/\x06/", $ent, $value, 1); 
          } 
        } 
    } 

    // Send the modified segment down the drain 
    $drain .= $value; 
  } 

  // Return contents of the drain 
  return $drain; 
} 

?> 

<?
if(!ini_get('safe_mode'))
	putenv("TZ=America/Los_Angeles");
$calendar_xml_address[0] = "http://www.google.com/calendar/feeds/bdu50c5cjtbcmhecc7gobklo6k%40group.calendar.google.com/public/basic";	 
$cache_location="";
$dateformat="D j F, Y"; // Thursday, 10 March - see http://www.php.net/date for details	
$timeFormat = "g:ia";
$compareDateFormat = "D F Y";
$simplepie_location = "gcal_api/simplepie.inc";
$gcalmanager_location = "gcal_api/GCalManager.inc";
include ($simplepie_location);
include ($gcalmanager_location);

$g = new GCalManager();

$beginTimeStr = $g->getBeginTimeStr(mktime(1,1,1,8,26,2006));
$endTimeStr = $g->getEndTimeStr(mktime(1,1,1,8,29,2008));

echo "Begin: " . $beginTimeStr . "<BR>";
echo "End: " . $endTimeStr . "<BR>";

$events = $g->getEventsListing($beginTimeStr, $endTimeStr, $calendar_xml_address, $cache_location);

if($events===false) {
	echo "No events scheduled for this week.";
}
else {	
	echo "<table width=\"475\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";          
	$lastEventDate = "first_event_flag";
	foreach($events as $event) {
		  $currentEventDate = date($compareDateFormat, $event['beginTime']);
		  		  
		  if($lastEventDate!=$currentEventDate) {
			  echo "<tr>";
    	      echo "<td><div class=\"eventHeader\">"; 
			  echo date($dateformat, $event['beginTime']);
			  echo "</div></td></tr>";
		  }
          echo "<tr>";
          echo "<td bgcolor=\"#E5E5E5\">&nbsp;</td>";
          echo "</tr>";
          echo "<tr>";
          echo "<td bgcolor=\"#E5E5E5\" div align=\"left\">";
		  echo $event['title'] . "<br />";
		  echo date($timeFormat, $event['beginTime']) . " to " . date($timeFormat, $event['endTime']) . "<br \>"; 
		  echo $event['where'] . "<br \ >";
		  echo "<div align=\"justify\"><blockquote>" . htmlwrap($event['desc']) . "</blockquote></div>"; 
		  echo "</td>";
          echo "</tr>";
          echo"<tr>";
          echo"<td bgcolor=\"#E5E5E5\">&nbsp;</td>";
          echo "</tr>";
		  $lastEventDate = $currentEventDate;
	}
	echo "</table>";
}
?>
</td>
    </tr>
  </table>
</html>