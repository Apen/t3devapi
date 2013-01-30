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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * ViewHelper to use strftime
 *
 * Example
 * <t3devapi:strftime format="%A %e %B %Y">{timestamp}</t3devapi:strftime>
 *
 * Renders the strftime
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_StrftimeViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper
{

	/**
	 * Render the supplied DateTime object as a formatted date using strftime.
	 *
	 * @param mixed  $date   either a DateTime object or a string (UNIX-Timestamp)
	 * @param string $format Format String which is taken to format the Date/Time
	 * @return string Formatted date
	 */
	public function render($date = NULL, $format = '%A, %d. %B %Y') {
		if ($date === NULL) {
			$date = $this->renderChildren();
			if ($date === NULL) {
				// current timestamp
				$date = time();
			}
		}

		$locales = setlocale(LC_ALL, '0');

		if ($date instanceof DateTime) {
			$content = strftime($format, $date->format('U'));
			if (stristr($locales, 'utf-8') !== FALSE) {
				return $content;
			}
			return utf8_encode($content);
		}

		$content = strftime($format, (int)$date);
		if (stristr($locales, 'utf-8') !== FALSE) {
			return $content;
		}
		return utf8_encode($content);
	}

}

?>