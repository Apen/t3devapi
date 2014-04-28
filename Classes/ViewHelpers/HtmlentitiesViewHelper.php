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
 * ViewHelper to call htmlentities
 *
 * Example
 * <t3devapi:htmlentities>...</t3devapi:htmlentities>
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_HtmlentitiesViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Disable the escaping interceptor because otherwise the child nodes would be escaped before this view helper
	 * can decode the text's entities.
	 *
	 * @var boolean
	 */
	protected $escapingInterceptorEnabled = FALSE;

	/**
	 * Escapes special characters with their escaped counterparts as needed using PHPs htmlentities() function.
	 *
	 * @param string  $value        string to format
	 * @param boolean $keepQuotes   if TRUE, single and double quotes won&#39;t be replaced (sets ENT_NOQUOTES flag)
	 * @param string  $encoding
	 * @param boolean $doubleEncode If FALSE existing html entities won&#39;t be encoded, the default is to convert everything.
	 * @return string the altered string
	 * @see http://www.php.net/manual/function.htmlentities.php
	 * @api
	 */
	public function render($value = NULL, $keepQuotes = FALSE, $encoding = 'UTF-8', $doubleEncode = TRUE) {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}
		if (!is_string($value)) {
			return $value;
		}
		$flags = $keepQuotes ? ENT_NOQUOTES : ENT_COMPAT;
		return htmlentities($value, $flags, $encoding, $doubleEncode);
	}

}

?>