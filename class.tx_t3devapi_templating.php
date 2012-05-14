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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * tx_t3devapi_templating
 * Class to use the TYPO3 templating system
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
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
	 * @param object $pObj
	 */
	public function __construct(&$pObj) {
		// Store parent object as a class variable
		$this->pObj = $pObj;
		$this->misc = new tx_t3devapi_miscellaneous($pObj);
		require_once(PATH_t3lib . 'class.t3lib_parsehtml.php');
	}

	/**
	 * Loads a template file
	 *
	 * @param string  $templateFile
	 * @param boolean $debug
	 * @return boolean
	 */
	public function initTemplate($templateFile, $debug = FALSE) {
		$templateAbsPath = t3lib_div::getFileAbsFileName(trim($templateFile));
		if ($templateAbsPath !== NULL) {
			$this->templateContent = t3lib_div::getURL($templateAbsPath);
			if ($debug === TRUE) {
				if ($this->templateContent === NULL) {
					tx_t3devapi_miscellaneous::debug('Check the path template or the rights', 'Error');
				}
				tx_t3devapi_miscellaneous::debug($this->templateContent, 'Content of ' . $templateFile);
			}
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Template rendering for subdatas and principal datas
	 *
	 * @param array   $templateMarkers
	 * @param string  $templateSection
	 * @param boolean $debug
	 * @return string HTML code
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

		if ($debug === TRUE) {
			tx_t3devapi_miscellaneous::debug($templateMarkers, 'Markers for ' . $templateSection);
		}

		$content = '';

		if (is_array($templateMarkers[0])) {
			foreach ($templateMarkers as $markers) {
				$content .= $this->renderAllTemplate($markers, $templateSection, $debug);
			}
		} else {
			$content = $this->renderSingle($templateMarkers, $templateSection);
		}

		return $this->cleanTemplate($content);
	}

	/**
	 * Render a single part with array and section
	 *
	 * @param array  $templateMarkers
	 * @param string $templateSection
	 * @return string
	 */
	public function renderSingle($templateMarkers, $templateSection) {
		$subParts = $this->getSubpart($this->templateContent, $templateSection);

		foreach ($templateMarkers as $subPart => $subContent) {
			if (preg_match_all('/(<!--).*?' . $subPart . '.*?(-->)/', $subParts, $matches) >= 2) {
				$subParts = $this->substituteSubpart($subParts, $subPart, $subContent);
			}
		}

		$content = $this->substituteMarkerArray($subParts, $templateMarkers);
		$content = $this->processHelpers($templateMarkers, $content);

		return $content;
	}

	/**
	 * Substitutes markers in a template. Usually, this is just a wrapper method
	 * around the t3lib_parsehtml::substituteMarkerArray method. However, this
	 * method is only available from TYPO3 4.2.
	 *
	 * @param  string $template The template
	 * @param  array  $marker   The markers that are to be replaced
	 * @return string           The template with replaced markers
	 */
	protected function substituteMarkerArray($template, $marker) {
		if (TYPO3_branch === '4.1' || TYPO3_branch === '4.0') {
			return str_replace(array_keys($marker), array_values($marker), $template);
		} else {
			return t3lib_parsehtml::substituteMarkerArray($template, $marker);
		}
	}


	/**
	 * Replaces a subpart in a template with content. This is just a wrapper method
	 * around the substituteSubpart method of the t3lib_parsehtml class.
	 *
	 * @param  string $template The tempalte
	 * @param  string $subpart  The subpart name
	 * @param  string $replace  The subpart content
	 * @return string           The template with replaced subpart.
	 */
	protected function substituteSubpart($template, $subpart, $replace) {
		return t3lib_parsehtml::substituteSubpart($template, $subpart, $replace);
	}


	/**
	 * Gets a subpart from a template. This is just a wrapper around the getSubpart
	 * method of the t3lib_parsehtml class.
	 *
	 * @param  string $template The template
	 * @param  string $subpart  The subpart name
	 * @return string           The subpart
	 */
	protected function getSubpart($template, $subpart) {
		return t3lib_parsehtml::getSubpart($template, $subpart);
	}

	/**
	 * Finds view helpers used in the current subpart being worked on.
	 *
	 * @param    string   $content A string that should be searched for view helpers.
	 * @return    array    A list of view helper names used in the template.
	 */
	protected function findViewHelpers($content) {
		preg_match_all('!###([\w]+):.*?\###!is', $content, $match);
		return array_unique($match[1]);
	}

	/**
	 * Processes helpers: finds and evaluates them in HTML code
	 *
	 * @param array  $templateMarkers
	 * @param string $content
	 * @return string
	 */
	protected function processHelpers($templateMarkers, $content) {
		$helpers = $this->findViewHelpers($content);
		foreach ($helpers as $helper) {
			$viewHelperArgumentLists = $this->getViewHelperArgumentLists($helper, $content, TRUE);
			switch ($helper) {
				case 'LLL':
					$content = $this->processHelpersLLL($templateMarkers, $content, $viewHelperArgumentLists);
					break;
				case 'EMPTY':
					$content = $this->processHelpersEmpty($templateMarkers, $content, $viewHelperArgumentLists);
					break;
				case 'NOTEMPTY':
					$content = $this->processHelpersNotEmpty($templateMarkers, $content, $viewHelperArgumentLists);
					break;
				case 'CROP':
					$content = $this->processHelpersCrop($templateMarkers, $content, $viewHelperArgumentLists);
					break;
				case 'IF':
					$content = $this->processHelpersIf($templateMarkers, $content, $viewHelperArgumentLists);
					break;
				case 'TS':
					$content = $this->processHelpersTs($templateMarkers, $content, $viewHelperArgumentLists);
					break;
				case 'LINK':
					$content = $this->processHelpersLink($templateMarkers, $content, $viewHelperArgumentLists);
					break;
				default:
					break;
			}
		}
		return $content;
	}

	/**
	 * Processes helpers LLL : finds and evaluates them in HTML code.
	 * Example : ###LLL:locallangvar###
	 *
	 * @param array  $templateMarkers
	 * @param string $content
	 * @param array  $viewHelperArgumentLists
	 * @return string
	 */
	protected function processHelpersLLL($templateMarkers, $content, $viewHelperArgumentLists) {
		foreach ($viewHelperArgumentLists as $viewHelperArgument) {
			if (empty($this->pObj->conf['locallang'][strtolower($viewHelperArgument)]) !== TRUE) {
				$value   = $this->pObj->conf['locallang'][strtolower($viewHelperArgument)];
				$content = t3lib_parsehtml::substituteMarker($content, '###LLL:' . $viewHelperArgument . '###', $value);
			}
		}
		return $content;
	}

	/**
	 * Processes helpers EMPTY : finds and evaluates them in HTML code.
	 * Example : ###EMPTY:VAR###
	 *
	 * @param array  $templateMarkers
	 * @param string $content
	 * @param array  $viewHelperArgumentLists
	 * @return string
	 */
	protected function processHelpersEmpty($templateMarkers, $content, $viewHelperArgumentLists) {
		foreach ($viewHelperArgumentLists as $viewHelperArgument) {
			if (empty($templateMarkers['###' . $viewHelperArgument . '###']) === TRUE) {
				$content = t3lib_parsehtml::substituteSubpart(
					$content,
					'###EMPTY:' . $viewHelperArgument . '###',
					t3lib_parsehtml::getSubpart($content, '###EMPTY:' . $viewHelperArgument . '###')
				);
			} else {
				$content = t3lib_parsehtml::substituteSubpart($content, '###EMPTY:' . $viewHelperArgument . '###', '');
			}
		}
		return $content;
	}

	/**
	 * Processes helpers NOTEMPTY : finds and evaluates them in HTML code.
	 * Example : ###NOTEMPTY:VAR###
	 *
	 * @param array  $templateMarkers
	 * @param string $content
	 * @param array  $viewHelperArgumentLists
	 * @return string
	 */
	protected function processHelpersNotEmpty($templateMarkers, $content, $viewHelperArgumentLists) {
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
	 * @param array  $templateMarkers
	 * @param string $content
	 * @param array  $viewHelperArgumentLists
	 * @return string
	 */
	protected function processHelpersCrop($templateMarkers, $content, $viewHelperArgumentLists) {
		foreach ($viewHelperArgumentLists as $viewHelperArgument) {
			$explodeViewHelperArguments = explode('|', $viewHelperArgument);
			$content                    = t3lib_parsehtml::substituteMarker(
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
	 *
	 * @param array  $templateMarkers
	 * @param string $content
	 * @param array  $viewHelperArgumentLists
	 * @return string
	 */
	protected function processHelpersIf($templateMarkers, $content, $viewHelperArgumentLists) {
		foreach ($viewHelperArgumentLists as $viewHelperArgument) {
			$explodeViewHelperArguments = explode('|', preg_replace('/\_(\w*)/', '###$1###', $viewHelperArgument));
			$comparand1                 = (strpos($explodeViewHelperArguments[0], '#') !== FALSE)
				? $templateMarkers[$explodeViewHelperArguments[0]]
				: $explodeViewHelperArguments[0];
			$comparand2                 = (strpos($explodeViewHelperArguments[2], '#') !== FALSE)
				? $templateMarkers[$explodeViewHelperArguments[2]]
				: $explodeViewHelperArguments[2];
			$operator                   = $explodeViewHelperArguments[1];
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
	 * Processes helpers TS : finds and evaluates them in HTML code.
	 * Example : ###TS:path.to.some.ts.property.or.content.object###
	 *
	 * @param array  $templateMarkers
	 * @param string $content
	 * @param array  $viewHelperArgumentLists
	 * @return string
	 */
	protected function processHelpersTs($templateMarkers, $content, $viewHelperArgumentLists) {
		foreach ($viewHelperArgumentLists as $viewHelperArgument) {
			$path         = $viewHelperArgument;
			$pathExploded = explode('.', trim($path));
			$depth        = count($pathExploded);
			$pathBranch   = $GLOBALS['TSFE']->tmpl->setup;
			$value        = '';
			for ($i = 0; $i < $depth; $i++) {
				if ($i < ($depth - 1)) {
					$pathBranch = $pathBranch[$pathExploded[$i] . '.'];
				} elseif (empty($pathExploded[$i])) {
					// path ends with a dot. We return the rest of the array
					$value = $pathBranch;
				} else {
					// path ends without a dot. We return the value.
					$value = $pathBranch[$pathExploded[$i]];
					if (isset($pathBranch[$pathExploded[$i] . '.'])) {
						// okay, seems to be a TS Content Element, let's run it
						$cObj  = t3lib_div::makeInstance('tslib_cObj');
						$value = $cObj->cObjGetSingle(
							$pathBranch[$pathExploded[$i]],
							$pathBranch[$pathExploded[$i] . '.']
						);
					}
				}
			}
			$content = t3lib_parsehtml::substituteMarker($content, '###TS:' . $viewHelperArgument . '###', $value);
		}
		return $content;
	}

	/**
	 * Processes helpers LINK : finds and evaluates them in HTML code.
	 * Example : ###LINK:Pid|AdditionalParameters|useCache###
	 * Example : ###LINK:3|param1=val1&param12=val2|1###
	 * If Pid is empty = current page
	 *
	 * @param array  $templateMarkers
	 * @param string $content
	 * @param array  $viewHelperArgumentLists
	 * @return string
	 */
	protected function processHelpersLink($templateMarkers, $content, $viewHelperArgumentLists) {
		foreach ($viewHelperArgumentLists as $viewHelperArgument) {
			$explodeViewHelperArguments = explode('|', $viewHelperArgument);
			$pid                        = $explodeViewHelperArguments[0] ? $explodeViewHelperArguments[0] : $GLOBALS['TSFE']->id;
			$additionalParameters       = $explodeViewHelperArguments[1] ?
				t3lib_div::explodeUrl2Array($explodeViewHelperArguments[1])
				: array();
			$useCache                   = $explodeViewHelperArguments[2] ? TRUE : FALSE;
			$value                      = $this->misc->getURL($additionalParameters, $useCache, $pid);
			$content                    = t3lib_parsehtml::substituteMarker(
				$content, '###LINK:' . $viewHelperArgument . '###', $value
			);
		}
		return $content;
	}

	/**
	 * Evaluates conditions.
	 *
	 * Supported operators are ==, !=, <, <=, >, >=, %
	 *
	 * @param string $comparand1     First comaprand
	 * @param string $comparand2     Second comaprand
	 * @param string $operator       Operator
	 * @return boolean    Boolean evaluation of the condition.
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
	 * @param    string   $helperMarker      marker name, can be lower case, doesn't need the ### delimiters
	 * @param    string   $subpart           subpart markup to search in
	 * @param    boolean  $removeDuplicates  Optionally determines whether duplicate view helpers are removed. Defaults to TRUE.
	 * @return    array    array of markers
	 */
	protected function getViewHelperArgumentLists($helperMarker, $subpart, $removeDuplicates = TRUE) {
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
	 * @param  string $content
	 * @return mixed
	 */
	protected function cleanTemplate($content) {
		return preg_replace('/^[\t\s\r]*\n+/m', '', $content);
	}

	/**
	 * Gets a list of Markers from the selected subpart.
	 *
	 * @param string $template
	 * @param string $markerPrefix
	 * @param bool   $capturePrefix
	 * @return array
	 */
	protected function getMarkersFromTemplate($template, $markerPrefix = '', $capturePrefix = TRUE) {
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

	/**
	 * Get all the subparts of a content
	 *
	 * @param string $template
	 * @return array
	 */
	protected function getTemplateSubpartTagList($template) {
		$tagList        = array();
		$orderedTagList = array();

		preg_match_all('/<!--.*?###(.*?)###.*?-->/', $template, $res, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);

		foreach ($res[0] as $k => $v) {
			$type = 'open';

			if (isset($tagList[$res[1][$k][0]])) {
				$type = 'close';
			}

			$tagList[$res[1][$k][0]][$type] = array(
				'matchedTag' => $v[0],
				'tagName'    => $res[1][$k][0],
				'offset'     => $v[1],
				'type'       => $type,
			);

			if ($type == 'close') {
				$orderedTagList[$res[1][$k][0]] = array($tagList[$res[1][$k][0]]['open'], $tagList[$res[1][$k][0]]['close']);
			}
		}

		unset($tagList);

		foreach ($orderedTagList as $tagKey => $tagValue) {
			$orderedTagList[$tagKey]['content'] = $this->getSubpart($template, '###' . $tagKey . '###');
			$orderedTagList[$tagKey]['markers'] = $this->getMarkersFromTemplate($orderedTagList[$tagKey]['content']);
		}

		return $orderedTagList;
	}

}

tx_t3devapi_miscellaneous::XCLASS('ext/t3devapi/class.tx_t3devapi_templating.php');

?>