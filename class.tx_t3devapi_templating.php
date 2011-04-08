<?php

/* * *************************************************************
*
* Copyright notice
*
* (c) 2010 Yohann CERDAN <ycerdan@onext.fr>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
* ************************************************************* */

/**
 * tx_t3devapi_templating
 * Class to use the TYPO3 template system
 *
 * @author Yohann
 * @copyright Copyright (c) 2011
 */

class tx_t3devapi_templating
{
	// Template object for frontend functions
	protected $templateContent = null;
	// Parent object
	protected $pObj = null;

	/**
	 * tx_t3devapi_templating::__construct()
	 *
	 * @param mixed $pObj
	 */

	function __construct($pObj)
	{
		// Store parent object as a class variable
		$this->pObj = $pObj;
	}

	/**
	 * Loads a template file
	 *
	 * @param mixed $templateFile
	 * @param mixed $debug
	 * @return
	 */

	function initTemplate($templateFile, $debug = false)
	{
		$this->templateContent = $this->pObj->cObj->fileResource($templateFile);
		if ($debug === true) {
			if ($this->templateContent === null) {
				t3lib_div::debug('Check the path template or the rights', 'Error');
			}
			t3lib_div::debug($this->templateContent, 'Content of ' . $templateFile);
		}
		return true;
	}

	/**
	 * Template rendering for subdatas and principal datas
	 *
	 * @param mixed $templateMarkers
	 * @param mixed $templateSection
	 * @return
	 */

	function renderAllTemplate($templateMarkers, $templateSection, $debug = false)
	{
		// Check if the template is loaded
		if (!$this->templateContent) {
			return false;
		}
		// Check argument
		if (!is_array($templateMarkers)) {
			return false;
		}

		if ($debug == true) {
			t3lib_div::debug($templateMarkers, 'Markers for ' . $templateSection);
		}

		// Templating
		$content = '';

		if (is_array($templateMarkers[0])) { // Subdatas
			foreach ($templateMarkers as $key => $val) {
				$subParts = $this->pObj->cObj->getSubpart($this->templateContent, $templateSection);
				$content .= $this->pObj->cObj->substituteMarkerArray($subParts, $val);
			}

			return $content;
		} else { // Principal datas
			$subParts = $this->pObj->cObj->getSubpart($this->templateContent, $templateSection);

			foreach ($templateMarkers as $subPart => $subContent) {
				if (preg_match_all('/(<!--).*?' . $subPart . '.*?(-->)/', $subParts, $matches) >= 2) { // subpart
					$subParts_temp = $this->pObj->cObj->getSubpart($subParts, $subPart);
					$subParts = $this->pObj->cObj->substituteSubpart($subParts, $subPart, $subContent);
				}
			}

			$content = $this->pObj->cObj->substituteMarkerArray($subParts, $templateMarkers);

			return $content;
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class.tx_t3devapi_templating.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class.tx_t3devapi_templating.php']);
}

?>