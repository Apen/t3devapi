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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * ViewHelper to use the array_push
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_Array_PushViewHelper extends TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('as', 'string', 'Template variable name to use', true, null);
        $this->registerArgument('array', 'array', 'array', true, null);
        $this->registerArgument('value', 'mixed', 'value', true, null);
    }

    /**
     * Render method
     *
     * @return array
     */
    public function render()
    {
        $name = $this->arguments['as'];
        $value = null;
        if (empty($this->arguments['array'])) {
            $this->arguments['array'] = array();
        }
        if (!empty($this->arguments['value'])) {
            array_push($this->arguments['array'], $this->arguments['value']);
            $value = $this->arguments['array'];
        }
        if ($name === null) {
            return $value;
        } else {
            if ($this->templateVariableContainer->exists($name)) {
                $this->templateVariableContainer->remove($name);
            }
            $this->templateVariableContainer->add($name, $value);
        }
        return null;
    }

}

?>