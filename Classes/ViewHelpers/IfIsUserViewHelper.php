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
 * ViewHelper to write in header
 *
 * Example
 * <t3devapi:ifIsUser uid="8">
 * xxx
 * </t3devapi:ifIsUser>
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_IfIsUserViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
{

    /**
     * renders <f:then> child if the current logged in FE user is the passed id
     * otherwise renders <f:else> child.
     *
     * @param string $roles The usergroup list uid
     * @return string the rendered string
     * @api
     */
    public function render($uid)
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
        if ($GLOBALS['TSFE']->fe_user->user['uid'] == $arguments['uid']) {
            return true;
        } else {
            return false;
        }
    }


}

?>
