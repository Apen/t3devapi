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
	public $templateContent = NULL;
	// Parent object
	public $pObj = NULL;
	public $misc = NULL;

	/**
	 * tx_t3devapi_templating::__construct()
	 *
	 * @param mixed $pObj
	 */

	public function __construct($pObj) {
		// Store parent object as a class variable
		$this->pObj = $pObj;
		$this->misc = new tx_t3devapi_miscellaneous(&$pObj);
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
			foreach ($templateMarkers as $markers) {
				$content .= $this->renderAllTemplate($markers, $templateSection, $debug);
			}
		} else { // Principal datas
			$content = $this->renderSingle($templateMarkers, $templateSection);
		}

		return $this->cleanTemplate($content);
	}

	/**
	 * Render a single part with array and section
	 *
	 * @param $templateMarkers
	 * @param $templateSection
	 * @return string
	 */

	public function renderSingle($templateMarkers, $templateSection) {
		$subParts = $this->pObj->cObj->getSubpart($this->templateContent, $templateSection);

		foreach ($templateMarkers as $subPart => $subContent) {
			if (preg_match_all('/(<!--).*?' . $subPart . '.*?(-->)/', $subParts, $matches) >= 2) { // subpart
				$subParts_temp = $this->pObj->cObj->getSubpart($subParts, $subPart);
				$subParts = $this->pObj->cObj->substituteSubpart($subParts, $subPart, $subContent);
			}
		}

		$content = $this->pObj->cObj->substituteMarkerArray($subParts, $templateMarkers);
		$content = $this->processHelpers($templateMarkers, $content);

		return $content;
	}

	/**
	 * Finds view helpers used in the current subpart being worked on.
	 *
	 * @param	string	A string that should be searched for view helpers.
	 * @return	array	A list of view helper names used in the template.
	 */

	public function findViewHelpers($content) {
		preg_match_all('!###([\w]+):.*?\###!is', $content, $match);
		return array_unique($match[1]);
	}

	/**
	 * Processes helpers: finds and evaluates them in HTML code.
	 *
	 * @param	string	HTML
	 */

	public function processHelpers($templateMarkers, $content) {
		$helpers = $this->findViewHelpers($content);

		foreach ($helpers as $helper) {
			$viewHelperArgumentLists = $this->getViewHelperArgumentLists($helper, $content, TRUE);
			//t3lib_div::debug($viewHelperArgumentLists, $helper);
			switch ($helper) {
				case 'NOTEMPTY':
					$content = $this->processHelpersNotEmpty($templateMarkers, $content, $viewHelperArgumentLists);
					break;
				case 'CROP':
					$content = $this->processHelpersCrop($templateMarkers, $content, $viewHelperArgumentLists);
					break;
				case 'IF':
					$content = $this->processHelpersIf($templateMarkers, $content, $viewHelperArgumentLists);
					break;
			}
		}

		return $content;
	}

	/**
	 * Processes helpers NOTEMPTY : finds and evaluates them in HTML code.
	 * Example : ###NOTEMPTY:VAR###
	 * @param	string	HTML
	 */

	public function processHelpersNotEmpty($templateMarkers, $content, $viewHelperArgumentLists) {
		foreach ($viewHelperArgumentLists as $viewHelperArgument) {
			if (empty($templateMarkers['###' . $viewHelperArgument . '###']) !== TRUE) {
				$content = t3lib_parsehtml::substituteSubpart(
					$content,
					'###NOTEMPTY:' . $viewHelperArgument . '###',
					t3lib_parsehtml::getSubpart($content, '###NOTEMPTY:' . $viewHelperArgument . '###')
				);
			} else {
				$content = t3lib_parsehtml::substituteSubpart($content, '###NOTEMPTY:' . $viewHelperArgument . '###', '');
			}
		}
		return $content;
	}

	/**
	 * Processes helpers CROP : finds and evaluates them in HTML code.
	 * Example : ###CROP:VAR|100|...###
	 *
	 * @param	string	HTML
	 */

	public function processHelpersCrop($templateMarkers, $content, $viewHelperArgumentLists) {
		foreach ($viewHelperArgumentLists as $viewHelperArgument) {
			$explodeViewHelperArguments = explode('|', $viewHelperArgument);
			$content = t3lib_parsehtml::substituteMarker(
				$content,
				'###CROP:' . $viewHelperArgument . '###',
				$this->misc->truncate(
					$templateMarkers['###' . $explodeViewHelperArguments[0] . '###'],
					(isset($explodeViewHelperArguments[1])) ? $explodeViewHelperArguments[1] : '100',
					(isset($explodeViewHelperArguments[2])) ? $explodeViewHelperArguments[2] : '...',
					FALSE,
					TRUE
				)
			);
		}
		return $content;
	}

	/**
	 * Processes helpers IF : finds and evaluates them in HTML code.
	 * Example : ###IF:_VAR|%|2### (underscore with a template var)
	 * Example : ###IF:10|%|2###
	 * @param	string	HTML
	 */

	public function processHelpersIf($templateMarkers, $content, $viewHelperArgumentLists) {
		foreach ($viewHelperArgumentLists as $viewHelperArgument) {
			$explodeViewHelperArguments = explode('|', preg_replace('/\_(\w*)/', '###$1###', $viewHelperArgument));
			$comparand1 = (strpos($explodeViewHelperArguments[0], '#') !== FALSE)
					? $templateMarkers[$explodeViewHelperArguments[0]]
					: $explodeViewHelperArguments[0];
			$comparand2 = (strpos($explodeViewHelperArguments[2], '#') !== FALSE)
					? $templateMarkers[$explodeViewHelperArguments[2]]
					: $explodeViewHelperArguments[2];
			$operator = $explodeViewHelperArguments[1];
			if ($this->evaluateCondition($comparand1, $comparand2, $operator)) {
				$content = t3lib_parsehtml::substituteSubpart(
					$content,
					'###IF:' . $viewHelperArgument . '###',
					t3lib_parsehtml::getSubpart($content, '###IF:' . $viewHelperArgument . '###')
				);
			} else {
				$content = t3lib_parsehtml::substituteSubpart($content, '###IF:' . $viewHelperArgument . '###', '');
			}
		}
		return $content;
	}

	/**
	 * Evaluates conditions.
	 *
	 * Supported operators are ==, !=, <, <=, >, >=, %
	 *
	 * @param	string	First comaprand
	 * @param	string	Second comaprand
	 * @param	string	Operator
	 * @return	boolean	Boolean evaluation of the condition.
	 */
	protected function evaluateCondition($comparand1, $comparand2, $operator) {
		$conditionResult = FALSE;

		switch ($operator) {
			case '==':
				$conditionResult = ($comparand1 == $comparand2);
				break;
			case '!=';
				$conditionResult = ($comparand1 != $comparand2);
				break;
			case '<';
				$conditionResult = ($comparand1 < $comparand2);
				break;
			case '<=';
				$conditionResult = ($comparand1 <= $comparand2);
				break;
			case '>';
				$conditionResult = ($comparand1 > $comparand2);
				break;
			case '>=';
				$conditionResult = ($comparand1 >= $comparand2);
				break;
			case '%';
				$conditionResult = ($comparand1 % $comparand2 == 0);
				break;
		}

		// explicit casting, just in case
		$conditionResult = (boolean)$conditionResult;

		return $conditionResult;
	}

	/**
	 * Gets a list of view helper marker arguments for a given view helper from
	 * the selected subpart.
	 *
	 * @param	string	marker name, can be lower case, doesn't need the ### delimiters
	 * @param	string	subpart markup to search in
	 * @param	boolean	Optionally determines whether duplicate view helpers are removed. Defaults to TRUE.
	 * @return	array	array of markers
	 */

	public function getViewHelperArgumentLists($helperMarker, $subpart, $removeDuplicates = TRUE) {
		// '!###' . $helperMarker . ':([A-Z0-9_-|.]*)\###!is'
		// '!###' . $helperMarker . ':(.*?)\###!is',
		// '!###' . $helperMarker . ':((.*?)+?(\###(.*?)\###(|.*?)?)?)?\###!is'
		// '!###' . $helperMarker . ':((?:###(?:.+?)###)(?:\|.+?)*|(?:.+?)+)###!is'
		preg_match_all(
			'/###' . $helperMarker . ':((?:###.+?###(?:\|.+?)*)|(?:.+?)?)###/si',
			$subpart,
			$match,
			PREG_PATTERN_ORDER
		);
		$markers = $match[1];

		if ($removeDuplicates) {
			$markers = array_unique($markers);
		}

		return $markers;
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

	/**
	 * Gets a list of Markers from the selected subpart.
	 *
	 * @param	string	marker name
	 * @return	array	array of markers
	 */

	public function getMarkersFromTemplate($template, $markerPrefix = '', $capturePrefix = TRUE) {
		$regex = '!###([A-Z0-9_-|:.]*)\###!is';

		if (!empty($markerPrefix)) {
			if ($capturePrefix) {
				$regex = '!###(' . strtoupper($markerPrefix) . '[A-Z0-9_-|:.]*)\###!is';
			} else {
				$regex = '!###' . strtoupper($markerPrefix) . '([A-Z0-9_-|:.]*)\###!is';
			}
		}

		preg_match_all($regex, $template, $match);
		$markers = array_unique($match[1]);

		return $markers;
	}

}

tx_t3devapi_miscellaneous::XCLASS('ext/t3devapi/class.tx_t3devapi_templating.php');

?>