<?php

/* * *************************************************************
*
* Copyright notice
*
* (c) 2011 Yohann CERDAN <ycerdan@onext.fr>
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
	protected $templateContent = NULL;
	// Parent object
	protected $pObj = NULL;

	/**
	 * tx_t3devapi_templating::__construct()
	 *
	 * @param mixed $pObj
	 */

	public function __construct($pObj) {
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

	public function initTemplate($templateFile, $debug = FALSE) {
		$this->templateContent = $this->pObj->cObj->fileResource($templateFile);
		if ($debug === TRUE) {
			if ($this->templateContent === NULL) {
				t3lib_div::debug('Check the path template or the rights', 'Error');
			}
			t3lib_div::debug($this->templateContent, 'Content of ' . $templateFile);
		}
		return TRUE;
	}

	/**
	 * Template rendering for subdatas and principal datas
	 *
	 * @param mixed $templateMarkers
	 * @param mixed $templateSection
	 * @return
	 */

	public function renderAllTemplate($templateMarkers, $templateSection, $debug = FALSE) {
		// Check if the template is loaded
		if (!$this->templateContent) {
			return FALSE;
		}
		// Check argument
		if (!is_array($templateMarkers)) {
			return FALSE;
		}

		if ($debug == TRUE) {
			tx_t3devapi_miscellaneous::debug($templateMarkers, 'Markers for ' . $templateSection);
		}

		// Templating
		$content = '';

		if (is_array($templateMarkers[0])) { // Subdatas
			foreach ($templateMarkers as $key => $val) {
				$subParts = $this->pObj->cObj->getSubpart($this->templateContent, $templateSection);
				$content .= $this->pObj->cObj->substituteMarkerArray($subParts, $val);
			}

			return $this->cleanTemplate($content);
		} else { // Principal datas
			$subParts = $this->pObj->cObj->getSubpart($this->templateContent, $templateSection);

			foreach ($templateMarkers as $subPart => $subContent) {
				if (preg_match_all('/(<!--).*?' . $subPart . '.*?(-->)/', $subParts, $matches) >= 2) { // subpart
					$subParts_temp = $this->pObj->cObj->getSubpart($subParts, $subPart);
					$subParts = $this->pObj->cObj->substituteSubpart($subParts, $subPart, $subContent);
				}
			}

			$content = $this->pObj->cObj->substituteMarkerArray($subParts, $templateMarkers);

			return $this->cleanTemplate($content);
		}
	}

	/**
	 * Clean a template string (remove blank lines...)
	 *
	 * @param  $content
	 * @return mixed
	 */

	public function cleanTemplate($content) {
		return preg_replace('/^[\t\s\r]*\n+/m', '', $content);
	}
}

tx_t3devapi_miscellaneous::XCLASS('ext/t3devapi/class.tx_t3devapi_templating.php');

?>