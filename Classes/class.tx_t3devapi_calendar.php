<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Yohann CERDAN <cerdanyohann@yahoo.fr>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * tx_t3devapi_calendar
 * Class to generate a HTML calendar
 *
 * @author Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package TYPO3
 * @subpackage t3devapi
 */
class tx_t3devapi_calendar
{
	/**
	 * Constructor
	 */

	public function __construct() {
	}

	/**
	 * PHP Calendar (version 2.3), written by Keith Devens
	 * http://keithdevens.com/software/php_calendar
	 * see example at http://keithdevens.com/weblog
	 * License: http://keithdevens.com/software/license
	 *
	 * @param mixed $year
	 * @param mixed $month
	 * @param array $days
	 * @param integer $day_name_length
	 * @param mixed $month_href
	 * @param integer $first_day
	 * @param array $pn
	 * @return
	 */
	public function generateCalendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array()) {
		$first_of_month = gmmktime(0, 0, 0, $month, 1, $year);
		// remember that mktime will automatically correct if invalid dates are entered
		// for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
		// this provides a built in "rounding" feature to generate_calendar()
		$day_names = array(); //generate all the day names according to the current locale
		for ($n = 0, $t = (3 + $first_day) * 86400; $n < 7; $n++, $t += 86400) // January 4, 1970 was a Sunday
			$day_names[$n] = ucfirst(gmstrftime('%A', $t)); //%A means full textual day name
		list($month, $year, $month_name, $weekday) = explode(',', gmstrftime('%m,%Y,%B,%w', $first_of_month));
		$weekday = ($weekday + 7 - $first_day) % 7; //adjust for $first_day
		$title = htmlentities(ucfirst($month_name)) . '&nbsp;' . $year; //note that some locales don't capitalize month and day names
		// Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
		@list($p, $pl) = each($pn);
		@list($n, $nl) = each($pn); //previous and next links, if applicable
		if ($p)
			$p = '<span class="calendar-prev">' . ($pl ? '<a href="' . htmlspecialchars($pl) . '">' . $p . '</a>'
					: $p) . '</span>&nbsp;';
		if ($n)
			$n = '&nbsp;<span class="calendar-next">' . ($nl ? '<a href="' . htmlspecialchars($nl) . '">' . $n . '</a>'
					: $n) . '</span>';
		$calendar = '<table class="calendar">' . "\n" .
		            '<caption class="calendar-month">' . $p . ($month_href
				? '<a href="' . htmlspecialchars($month_href) . '">' . $title . '</a>' : $title) . $n .
		            "</caption>\n<tr>";

		if ($day_name_length) { // if the day names should be shown ($day_name_length > 0)
			// if day_name_length is >3, the full name of the day will be printed
			foreach ($day_names as $d)
				$calendar .= '<th abbr="' . htmlentities($d) . '">' . htmlentities($day_name_length < 4
						                                                                   ? substr($d, 0, $day_name_length)
						                                                                   : $d) . '</th>';
			$calendar .= "</tr>\n<tr>";
		}

		if ($weekday > 0) {
			for ($i = 1; $i <= $weekday; $i++) {
				$calendar .= '<td class="noDay">&nbsp;</td>';
			}
		}

		for ($day = 1, $days_in_month = gmdate('t', $first_of_month); $day <= $days_in_month; $day++, $weekday++) {
			if ($weekday == 7) {
				$weekday = 0; //start a new week
				$calendar .= "</tr>\n<tr>";
			}
			if (isset($days[$day]) and is_array($days[$day])) {
				@list($link, $classes, $content) = $days[$day];
				if (is_NULL($content))
					$content = $day;
				$calendar .= '<td' . ($classes ? ' class="' . htmlspecialchars($classes) . '">' : '>') .
				             ($link ? '<a href="' . htmlspecialchars($link) . '">' . $content . '</a>' : $content) . '</td>';
			} else
				$calendar .= "<td>$day</td>";
		}

		if ($weekday != 7) {
			for ($i = 1; $i <= (7 - $weekday); $i++) {
				$calendar .= '<td class="noDay">&nbsp;</td>';
			}
		}

		return $calendar . "</tr>\n</table>\n";
	}
}

tx_t3devapi_miscellaneous::XCLASS('ext/t3devapi/class.tx_t3devapi_calendar.php');

?>