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
 * <code title="Example">
 * <t3devapi:rte name="myRteTextArea" rows="5" cols="40" value="This is shown inside the rte textarea" />
 * <t3devapi:rte property="content" rows="5" cols="40" /><br />
 * </code>
 *
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
require_once(TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rtehtmlarea') . 'pi2/class.tx_rtehtmlarea_pi2.php');

class Tx_T3devapi_ViewHelpers_RteViewHelper extends Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper
{

    /**
     * Return RTE
     *
     * @param    string  $name       Field name
     * @param    string  $namePrefix Name prefix (tx_ext_pi1[object])
     * @param    boolean $isLast     Is last flag (generate JavaScript only for the last RTE)
     * @param    string  $value      Any value
     * @param    string  $width      Width
     * @param    string  $height     height
     * @return   string    Generated RTE content
     */
    public function render($name, $namePrefix, $isLast = 1, $value = '', $width = '400px', $height = '300px')
    {
        require_once(TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rtehtmlarea') . 'pi2/class.tx_rtehtmlarea_pi2.php');
        require_once(PATH_site . 'typo3conf/ext/t3devapi/Classes/class.tx_t3devapi_fertehtmlarea.php');

        if ($this->viewHelperVariableContainer->exists('Tx_T3devapi_ViewHelpers_RteViewHelper', 'rte')) {
            $rteObject = $this->viewHelperVariableContainer->get('Tx_T3devapi_ViewHelpers_RteViewHelper', 'rte');
        } else {
            $rteObject = array();
            $rteObject['counter'] = 1;
            $rteObject['onsubmit'] = '';
        }

        $rte = new tx_t3devapi_fertehtmlarea();
        $rte->setField($name);
        $rte->setPA(array('itemFormElName' => $namePrefix . '[' . $name . ']', 'itemFormElValue' => $value));
        $this->registerFieldNameForFormTokenGeneration($namePrefix . '[' . $name . ']');
        $this->registerFieldNameForFormTokenGeneration($namePrefix . '[_TRANSFORM_' . $name . ']');
        $rte->setRTEcounter($rteObject['counter']);
        $rte->setWidth($width);
        $rte->setHeight($height);
        $markerArray = $rte->drawRTE();

        $rteObject['counter']++;
        $rteObject['onsubmit'] .= $markerArray['###ADDITIONALJS_SUBMIT###'];

        if ($isLast == 1) {
            $rteObject['onsubmit'] = '<script type="text/javascript">function rteMove(){' . $rteObject['onsubmit'] . '}</script>';
            //t3lib_div::debug($markerArray['###ADDITIONALJS_PRE###'] . $markerArray['###FORM_RTE_ENTRY###'] . $markerArray['###ADDITIONALJS_POST###'] . $rteObject['onsubmit'],'*');
            return $markerArray['###ADDITIONALJS_PRE###'] . $markerArray['###FORM_RTE_ENTRY###'] . $markerArray['###ADDITIONALJS_POST###'] . $rteObject['onsubmit'];
        } else {
            $this->viewHelperVariableContainer->addOrUpdate('Tx_T3devapi_ViewHelpers_RteViewHelper', 'rte', $rteObject);
            return $markerArray['###ADDITIONALJS_PRE###'] . $markerArray['###FORM_RTE_ENTRY###'] . $markerArray['###ADDITIONALJS_POST###'];
        }

    }

    /**
     * Initialize the arguments.
     */
    public function initializeArguments()
    {
    }
}

?>
