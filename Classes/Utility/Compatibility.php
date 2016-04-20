<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Yohann CERDAN <cerdanyohann@yahoo.fr>
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
 * Tx_T3devapi_Utility_Compatibility
 * Class with some typo3 compatibility functions
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_Utility_Compatibility
{

    /**
     * Forces the integer $theInt into the boundaries of $min and $max. If the $theInt is 'FALSE' then the $zeroValue is applied.
     *
     * @param integer $theInt    Input value
     * @param integer $min       Lower limit
     * @param integer $max       Higher limit
     * @param integer $zeroValue Default value if input is FALSE.
     * @return integer The input value forced into the boundaries of $min and $max
     */
    public static function forceIntegerInRange($theInt, $min, $max = 2000000000, $zeroValue = 0)
    {
        return \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($theInt, $min, $max, $zeroValue);
    }

    /**
     * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
     *
     * @param $versionNumber string Version number on format x.x.x
     * @return integer Integer version of version number (where each part can count to 999)
     */
    public static function convertVersionNumberToInteger($versionNumber)
    {
        return \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($versionNumber);
    }

    /**
     * Returns true if the current TYPO3_version is greater than $versionNumber
     *
     * @param string $versionNumber
     * @return boolean
     */
    public static function isGreaterThan($versionNumber)
    {
        return (self::convertVersionNumberToInteger(TYPO3_version) > self::convertVersionNumberToInteger($versionNumber));
    }

    /**
     * Returns true if the current TYPO3_version is greater than $versionNumber
     *
     * @param string $versionNumber
     * @return boolean
     */
    public static function isGreaterThanOrEqual($versionNumber)
    {
        return (self::convertVersionNumberToInteger(TYPO3_version) >= self::convertVersionNumberToInteger($versionNumber));
    }

    /**
     * Returns true if the current TYPO3_version is smaller than $versionNumber
     *
     * @param string $versionNumber
     * @return boolean
     */
    public static function isLessThan($versionNumber)
    {
        return (self::convertVersionNumberToInteger(TYPO3_version) < self::convertVersionNumberToInteger($versionNumber));
    }

    /**
     * Returns true if the current TYPO3_version is smaller than $versionNumber
     *
     * @param string $versionNumber
     * @return boolean
     */
    public static function isLessThanOrEqual($versionNumber)
    {
        return (self::convertVersionNumberToInteger(TYPO3_version) <= self::convertVersionNumberToInteger($versionNumber));
    }

}

?>