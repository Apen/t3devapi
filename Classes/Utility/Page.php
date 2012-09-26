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
 * Tx_T3devapi_Utility_Page
 * Class with some page functions
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_Utility_Page
{
	/**
	 * Find all ids from given ids and level
	 *
	 * @param string  $pidList   comma seperated list of ids
	 * @param integer $recursive recursive levels
	 * @return string comma seperated list of ids
	 */
	public static function extendPidListByChildren($pidList = '', $recursive = 0) {
		if ($recursive <= 0) {
			return $pidList;
		}

		$cObj = t3lib_div::makeInstance('tslib_cObj');

		$recursive = Tx_T3devapi_Utility_Compatibility::forceIntegerInRange($recursive, 0);

		$pidList = array_unique(t3lib_div::trimExplode(',', $pidList, 1));

		$result = array();

		foreach ($pidList as $pid) {
			$pid = Tx_T3devapi_Utility_Compatibility::forceIntegerInRange($pid, 0);
			if ($pid) {
				$children = $cObj->getTreeList(-1 * $pid, $recursive);
				if ($children) {
					$result[] = $children;
				}
			}
		}

		return implode(',', $result);
	}
}

?>