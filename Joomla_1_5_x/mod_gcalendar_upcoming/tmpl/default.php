<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Allon Moritz
 * @copyright 2007-2009 Allon Moritz
 * @version $Revision: 2.1.0 $
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!empty($error)){
	echo $error;
	return;
}

$SECSINDAY=86400;

$event_display=$params->get('output', '');

// Date format you want your details to appear
$dateformat=$params->get('dateFormat', '%d.%m.%Y');
$timeformat=$params->get('timeFormat', '%H:%M');
$calName = $params->get( 'name', NULL );

// Loop through the array, and display what we wanted.
for ($i = 0; $i < sizeof($gcalendar_data) && $i <$params->get( 'max', 5 ); $i++){
	$item = $gcalendar_data[$i];

	$feed = $item->get_feed();
	$tz = GCalendarUtil::getComponentParameter('timezone');
	if($tz == ''){
		$tz = $feed->get_timezone();
	}

	$itemID = GCalendarUtil::getItemId($feed->get('gcid'));
	if(!empty($itemID))$itemID = '&Itemid='.$itemID;

	// These are the dates we'll display
	$startDate = strftime($dateformat, $item->get_start_time());
	$startTime = strftime($timeformat, $item->get_start_time());
	$endDate = strftime($dateformat, $item->get_end_time());
	$endTime = strftime($timeformat, $item->get_end_time());

	$temp_event=$event_display;

	switch($item->get_day_type()){
		case $item->SINGLE_WHOLE_DAY:
			$temp_event=str_replace("{startdate}",$startDate,$temp_event);
			$temp_event=str_replace("{starttime}","",$temp_event);
			$temp_event=str_replace("{dateseparator}","",$temp_event);
			$temp_event=str_replace("{enddate}","",$temp_event);
			$temp_event=str_replace("{endtime}","",$temp_event);
			break;
		case $item->SINGLE_PART_DAY:
			$temp_event=str_replace("{startdate}",$startDate,$temp_event);
			$temp_event=str_replace("{starttime}",$startTime,$temp_event);
			$temp_event=str_replace("{dateseparator}","-",$temp_event);
			$temp_event=str_replace("{enddate}","",$temp_event);
			$temp_event=str_replace("{endtime}",$endTime,$temp_event);
			break;
		case $item->MULTIPLE_WHOLE_DAY:
			$endDate = strftime($dateformat, $item->get_end_time() - $SECSINDAY);
			$temp_event=str_replace("{startdate}",$startDate,$temp_event);
			$temp_event=str_replace("{starttime}","",$temp_event);
			$temp_event=str_replace("{dateseparator}","-",$temp_event);
			$temp_event=str_replace("{enddate}",$endDate,$temp_event);
			$temp_event=str_replace("{endtime}","",$temp_event);
			break;
		case $item->MULTIPLE_PART_DAY:
			$temp_event=str_replace("{startdate}",$startDate,$temp_event);
			$temp_event=str_replace("{starttime}",$startTime,$temp_event);
			$temp_event=str_replace("{dateseparator}","-",$temp_event);
			$temp_event=str_replace("{enddate}",$endDate,$temp_event);
			$temp_event=str_replace("{endtime}",$endTime,$temp_event);
			break;
	}

	//Make any URLs used in the description also clickable: thanks Adam
	$desc = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $item->get_description());

	$temp_event=str_replace("{title}",$item->get_title(),$temp_event);
	$temp_event=str_replace("{description}",$desc,$temp_event);
	$temp_event=str_replace("{where}",$item->get_location(),$temp_event);
	$temp_event=str_replace("{backlink}",JRoute::_('index.php?option=com_gcalendar&task=event&eventID='.$item->get_id().'&gcid='.$feed->get('gcid').$itemID),$temp_event);
	$temp_event=str_replace("{link}",$item->get_link().'&ctz='.$tz,$temp_event);
	$temp_event=str_replace("{maplink}","http://maps.google.com/?q=".urlencode($item->get_location()),$temp_event);
	// Accept and translate HTML
	$temp_event=str_replace("&lt;","<",$temp_event);
	$temp_event=str_replace("&gt;",">",$temp_event);
	$temp_event=str_replace("&quot;","\"",$temp_event);

	echo $temp_event;
}
?>