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
 * ViewHelper to check if a variable is in a list
 *
 * Example
 * <t3devapi:inList list="{t3devapi:session(index:'agenda', identifier:'dates')}" item="{eventDate.filtre}">...</t3devapi:inList>
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_InListViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
{

    /**
     * Renders else-child or else-argument if variable $item is in $list
     *
     * @param string $list
     * @param string $item
     * @return string
     */
    public function render($list, $item)
    {
        $evaluation = static::evaluateCondition($this->arguments);

        if (false !== $evaluation) {
            return $this->renderThenChild();
        } else {
            return $this->renderElseChild();
        }
    }

    /**
     * This method decides if the condition is TRUE or FALSE. It can be overriden in extending viewhelpers to adjust functionality.
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
     * @return bool
     */
    static protected function evaluateCondition($arguments = null)
    {
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList($arguments['list'], $arguments['item']) === true) {
            return true;
        } else {
            return false;
        }
    }

}

?>
