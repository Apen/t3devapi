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
 * <t3devapi:ifHasRoles roles="1,2">
 * xxx
 * </t3devapi:ifHasRoles>
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_IfHasRolesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
{

    /**
     * renders <f:then> child if the current logged in FE user belongs to the specified roles (aka usergroup)
     * otherwise renders <f:else> child.
     *
     * @param string $roles The usergroup list uid
     * @return string the rendered string
     * @api
     */
    public function render($roles)
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
     * Determines whether the currently logged in FE user belongs to the specified usergroup
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
     * @return bool
     */
    static protected function evaluateCondition($arguments = null)
    {
        if (!isset($GLOBALS['TSFE']) || !$GLOBALS['TSFE']->loginUser) {
            return false;
        }
        $rolesArray = explode(',', $arguments['roles']);
        $find = false;
        foreach ($rolesArray as $fegroupsUid) {
            if (is_numeric($fegroupsUid)) {
                if (is_array($GLOBALS['TSFE']->fe_user->groupData['uid']) && in_array($fegroupsUid, $GLOBALS['TSFE']->fe_user->groupData['uid'])) {
                    $find = true;
                }
            }
        }
        return $find;
    }

}

?>
