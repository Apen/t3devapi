<?php

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * View Helper which creates a simple radio button (<input type="radio">).
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.radio name="myRadioButton" value="someValue" />
 * </code>
 * <output>
 * <input type="radio" name="myRadioButton" value="someValue" />
 * </output>
 *
 * <code title="Preselect">
 * <f:form.radio name="myRadioButton" value="someValue" checked="{object.value} == 5" />
 * </code>
 * <output>
 * <input type="radio" name="myRadioButton" value="someValue" checked="checked" />
 * (depending on $object)
 * </output>
 *
 * <code title="Bind to object property">
 * <f:form.radio property="newsletter" value="1" /> yes
 * <f:form.radio property="newsletter" value="0" /> no
 * </code>
 * <output>
 * <input type="radio" name="user[newsletter]" value="1" checked="checked" /> yes
 * <input type="radio" name="user[newsletter]" value="0" /> no
 * (depending on property "newsletter")
 * </output>
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class Tx_T3devapi_ViewHelpers_Form_RadioViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\RadioViewHelper
{

    /**
     * Renders the radio
     *
     * @param boolean $checked Specifies that the input element should be preselected
     *
     * @return string
     */
    public function render($checked = null)
    {
        $this->tag->addAttribute('type', 'radio');

        $nameAttribute = $this->getName();
        $valueAttribute = $this->getValue();

        // fix for bug with if ($checked === NULL && $this->isObjectAccessorMode()) {
        if ($checked === null && $this->isObjectAccessorMode() && $this->viewHelperVariableContainer->exists('Tx_Fluid_ViewHelpers_FormViewHelper', 'formObject')) {
            $this->addAdditionalIdentityPropertiesIfNeeded();
            $propertyValue = $this->getPropertyValue();

            // no type-safe comparisation by intention
            $checked = $propertyValue == $valueAttribute;
        }

        $this->registerFieldNameForFormTokenGeneration($nameAttribute);
        $this->tag->addAttribute('name', $nameAttribute);
        $this->tag->addAttribute('value', $valueAttribute);
        if ($checked) {
            $this->tag->addAttribute('checked', 'checked');
        }

        $this->setErrorClassAttribute();

        return $this->tag->render();
    }
}

?>