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
 * ViewHelper to use the array_diff
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_Array_DiffViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

    /**
     * Initialize arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments() {
        $this->registerArgument('as', 'string', 'Template variable name to use', TRUE, NULL);
        $this->registerArgument('array1', 'array', 'First array', TRUE, NULL);
        $this->registerArgument('array2', 'array', 'First array', TRUE, NULL);
    }

    /**
     * Render method
     *
     * @return array
     */
    public function render() {
        $name = $this->arguments['as'];
        $value = NULL;
        if (!empty($this->arguments['array1']) && !empty($this->arguments['array1'])) {
            $value = array_diff($this->arguments['array1'], $this->arguments['array2']);
        }
        if ($name === NULL) {
            return $value;
        } else {
            if ($this->templateVariableContainer->exists($name)) {
                $this->templateVariableContainer->remove($name);
            }
            $this->templateVariableContainer->add($name, $value);
        }
        return NULL;
    }

}

?>