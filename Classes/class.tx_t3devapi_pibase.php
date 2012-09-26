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
 * tx_t3devapi_pibase
 * Class base of a plugin
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class tx_t3devapi_pibase
{

	/**
	 * @var tslib_cObj
	 */
	public $cObj = NULL;
	public $prefixId = '';
	public $scriptRelPath = '';
	public $extKey = '';
	public $pi_checkCHash = FALSE;
	public $piVars = array();
	public $profile;
	public $conf = NULL;
	public $template = NULL;
	/**
	 * @var $misc tx_t3devapi_miscellaneous
	 */
	public $misc = NULL;
	public $uploadPath = '';

	/**
	 * Constructor of the class
	 */
	public function __construct() {
		// Setting piVars:
		if ($this->prefixId) {
			$this->piVars = t3lib_div::_GPmerged($this->prefixId);
			// cHash mode check
			// IMPORTANT FOR CACHED PLUGINS (USER cObject): As soon as you generate cached plugin output which depends on
			// parameters (eg. seeing the details of a news item) you MUST check if a cHash value is set.
			// Background: The function call will check if a cHash parameter was sent with the URL because only if it was
			// the page may be cached. If no cHash was found the function will simply disable caching to avoid unpredictable
			// caching behaviour. In any case your plugin can generate the expected output and the only risk is that the content
			// may not be cached. A missing cHash value is considered a mistake in the URL resulting from either URL
			// manipulation, "realurl" "grayzones" etc. The problem is rare (more frequent with "realurl") but when it
			// occurs it is very puzzling!
			if ($this->pi_checkCHash && count($this->piVars)) {
				$GLOBALS['TSFE']->reqCHash();
			}
		}
	}

	/**
	 * Init the plugin
	 *
	 * @return void
	 */
	public function init() {
		// default piVars
		if (is_array($this->conf['_DEFAULT_PI_VARS.'])) {
			$this->piVars = t3lib_div::array_merge_recursive_overrule(
				$this->conf['_DEFAULT_PI_VARS.'], is_array($this->piVars) ? $this->piVars : array()
			);
		}

		// needed classes
		$this->template = new tx_t3devapi_templating($this);
		$this->misc     = new tx_t3devapi_miscellaneous($this);
		$this->conf     = tx_t3devapi_config::getArrayConfig();

		// Additionnal TS
		if (empty($this->conf['myTS']) === FALSE) {
			$this->conf = $this->misc->loadTS($this->conf, $this->conf['myTS']);
		}

		// Debug
		$this->conf['debug'] = ($this->conf['debug'] == 1) ? TRUE : FALSE;

		// Check conf profile
		$this->conf['profile'] = ($this->conf['profile'] == 1) ? TRUE : FALSE;
		$this->profileStart();

		// Path to the HTML template
		if (empty($this->conf['templateFile']) === FALSE) {
			$this->addTemplate($this->conf['templateFile']);
		} else {
			$this->addTemplate('typo3conf/ext/' . $this->extKey . '/res/template.html');
		}

		// locallangs array
		$this->conf['locallang']        = $this->misc->loadLL(
			'typo3conf/ext/' . $this->extKey . '/' . dirname($this->scriptRelPath) . '/locallang.xml'
		);
		$this->conf['markerslocallang'] = $this->misc->convertToMarkerArray($this->conf['locallang'], 'LLL:');

		// upload path
		$this->uploadPath = 'uploads/tx_' . $this->extKey . '/';
	}

	/**
	 * Default init of the plugin
	 * With the default flexform
	 *
	 * @return void
	 */
	public function defaultInit() {
		// Starting point
		$this->conf['pidList'] = $this->pi_getPidList($this->conf['pages'], '99');

		// List & single UID
		$this->conf['singleView'] = ($this->conf['singleView'] != '') ? $this->conf['singleView'] : $GLOBALS['TSFE']->id;
		$this->conf['listView']   = ($this->conf['listView'] != '') ? $this->conf['listView'] : $GLOBALS['TSFE']->id;

		// possibility to add where condition (AND xxx)
		$this->conf['addWhere'] = '';

		// Order by & ascDesc
		$this->conf['orderBy'] = ($this->conf['orderBy'] != '') ? $this->conf['orderBy'] : '';
		$this->conf['ascDesc'] = ($this->conf['ascDesc'] != '') ? $this->conf['ascDesc'] : '';

		// Limit & start
		$this->conf['limit'] = ($this->conf['limit'] != '') ? $this->conf['limit'] : '';
		$this->conf['start'] = ($this->conf['start'] != '') ? $this->conf['start'] : '';

		// Page browser
		$this->conf['pageBrowserNbRecords'] = ($this->conf['pageBrowserNbRecords'] != '') ?
			$this->conf['pageBrowserNbRecords'] : '';

		// Size of the list thumbs
		$thumbSize                 = explode('x', $this->conf['thumbSize']);
		$this->conf['thumbWidth']  = (isset($thumbSize[0]) && ($thumbSize[0] != '')) ? $thumbSize[0] : 150;
		$this->conf['thumbHeight'] = (isset($thumbSize[1]) && ($thumbSize[1] != '')) ? $thumbSize[1] : 150;

		// Size of the full thumbs
		$thumbSize                       = explode('x', $this->conf['thumbSizeSingle']);
		$this->conf['thumbSingleWidth']  = (isset($thumbSize[0]) && ($thumbSize[0] != '')) ? $thumbSize[0] : 500;
		$this->conf['thumbSingleHeight'] = (isset($thumbSize[1]) && ($thumbSize[1] != '')) ? $thumbSize[1] : 350;

		// CSS
		if (empty($this->conf['cssFile']) === FALSE) {
			$this->addCSS($this->conf['cssFile']);
		}

		// Filter the list view
		if ((isset($this->conf['listUID'])) && ($this->conf['listUID'] != '')) {
			$this->conf['addWhere'] = ' AND pages.uid IN (' . $this->conf['listUID'] . ')';
			$this->conf['orderBy']  = ' FIND_IN_SET(uid, \'' . $this->conf['listUID'] . '\')';
		}
	}

	/*************************************** DB ***************************************/

	/**
	 * Get a single record
	 *
	 * @param string $from
	 * @param int    $uid
	 * @param string $select
	 * @return array
	 */
	public function getRecord($from, $uid, $select = '*') {
		$query['SELECT'] = $select;
		$query['FROM']   = $from;
		$query['WHERE']  = 'uid=' . intval($uid);
		$res             = tx_t3devapi_database::exec_SELECT_queryArray($query, $this->conf['debug']);
		$row             = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $row;
	}

	/**
	 * Get records from a list of uids
	 *
	 * @param int    $from
	 * @param string $uids
	 * @param string $select
	 * @return array
	 */
	public function getRecords($from, $uids, $select = '*') {
		$query['SELECT'] = $select;
		$query['FROM']   = $from;
		$query['WHERE']  = 'uid IN (' . mysql_real_escape_string($uids) . ')';
		$rows            = tx_t3devapi_database::exec_SELECTgetRows($query, $this->conf['debug']);
		return $rows;
	}

	/**
	 * Returns a part of a WHERE clause which will filter out records with start/end times or hidden/fe_groups fields
	 * it also add a where condition if $this->conf['pidList'] is not empty
	 *
	 * @param string $table
	 * @param string $addWhere
	 * @return string
	 */
	public function getEnableFields($table, $addWhere = '') {
		$content = '1=1';
		$content .= ' ' . $this->cObj->enableFields($table);
		$content .= (empty($this->conf['pidList']) === FALSE) ?
			' AND ' . $table . '.pid IN (' . $this->conf['pidList'] . ')'
			: '';
		$content .= (empty($addWhere) === FALSE) ? ' ' . $addWhere : '';
		return $content;
	}

	/**
	 * Get the ORDER BY statement
	 *
	 * @param string $orderBy
	 * @param string $ascDesc
	 * @return string
	 */
	public function getOrderBy($orderBy = '', $ascDesc = '') {
		$content = '';
		$content .= (empty($orderBy) === FALSE) ? $orderBy : '';
		$content .= (empty($orderBy) === FALSE) ? ' ' . $ascDesc : '';
		return $content;
	}

	/**
	 * Get the LIMIT statement
	 *
	 * @param string $limit
	 * @param string $start
	 * @return string
	 */
	public function getLimit($limit = '', $start = '') {
		$content = '';
		$content .= (empty($limit) === FALSE) ? $limit : '';
		if (empty($start) === FALSE) {
			if (empty($limit) === FALSE) {
				$content = $start . ',' . $limit;
			} else {
				$content = $start . ',10000000000';
			}
		}
		return $content;
	}

	/**
	 * Get the current request with PageBrowser limit
	 *
	 * @return string SQL ressource
	 */
	public function getListPageBrowser() {
		$this->conf['records']['pagebrowsernbrecords'] = $this->conf['pageBrowserNbRecords'];
		$this->conf['records']['nbpages']              = ceil(
			intval($this->conf['records']['nbrecordsall']) / intval($this->conf['pageBrowserNbRecords'])
		);
		$this->conf['records']['offset']               = isset($this->piVars['page'])
			? $this->piVars['page'] * $this->conf['pageBrowserNbRecords'] : 0;
		$this->conf['query']['LIMIT']                  = $this->conf['records']['offset'] . ',' . $this->conf['pageBrowserNbRecords'];
		return tx_t3devapi_database::exec_SELECT_queryArray($this->conf['query'], $this->conf['debug']);
	}

	/**
	 * Start the profiling (parsetime and memory usage)
	 *
	 * @return void
	 */
	public function profileStart() {
		if ($this->conf['profile'] === TRUE) {
			$this->profile['parsetime'] = microtime(TRUE);
			$this->profile['mem']       = tx_t3devapi_miscellaneous::getMemoryUsage();
		}
	}

	/**
	 * End the profiling
	 *
	 * @return string
	 */
	public function profileStop() {
		$content = '';
		if ($this->conf['profile'] === TRUE) {
			$this->profile['parsetime'] = (microtime(TRUE) - $this->profile['parsetime']) . ' ms';
			$this->profile['mem']       = tx_t3devapi_miscellaneous::getMemoryUsage() - $this->profile['mem'] . ' ko';
			$this->profile['mem'] .= '(total:' . tx_t3devapi_miscellaneous::getMemoryUsage() . 'ko)';
			$content = $this->profile['parsetime'] . ' / ' . $this->profile['mem'];
		}
		return $content;
	}

	/*************************************** PAGEBROWSER (with tx_pagebrowse_pi1) ***************************************/

	/**
	 * Get the HTML code of the pahe browser
	 *
	 * @param int $numberOfPages
	 * @return string HTML code
	 */
	public function getHTMLPageBrowser($numberOfPages) {
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_pagebrowse_pi1.'];
		$conf += array(
			'pageParameterName' => $this->prefixId . '|page',
			'numberOfPages'     => $numberOfPages,
		);
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->start(array(), '');
		return $cObj->cObjGetSingle('USER', $conf);
	}

	/*************************************** TEMPLATE, CSS & JS ***************************************/

	/**
	 * Add a template file
	 *
	 * @param string $path
	 * @return void
	 */
	public function addTemplate($path) {
		$this->template->initTemplate(trim($path), $this->conf['debug']);
	}

	/**
	 * Add a CSS file
	 *
	 * @param string $path
	 * @param string $id
	 * @return void
	 */
	public function addCSS($path, $id = '') {
		if ($this->cObj->getUserObjectType() == tslib_cObj::OBJECTTYPE_USER_INT) {
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey . $id . 'css'] = '<link rel="stylesheet" type="text/css" href="' . trim(
				$path
			) . '" media="all">';
		} else {
			$GLOBALS['TSFE']->pSetup['includeCSS.'][$this->extKey . $id]       = trim($path);
			$GLOBALS['TSFE']->pSetup['includeCSS.'][$this->extKey . $id . '.'] = array('media' => 'screen');
		}
	}

	/**
	 * Add a JS file
	 *
	 * @param string  $path
	 * @param boolean $includeInFooter
	 * @param string  $id
	 * @return void
	 */
	public function addJS($path, $includeInFooter = FALSE, $id = '') {
		if ($this->cObj->getUserObjectType() == tslib_cObj::OBJECTTYPE_USER_INT) {
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey . $id . 'js'] = '<script src="' . trim(
				$path
			) . '" type="text/javascript"></script>';
		} else {
			if ($includeInFooter === 1) {
				$includeJs = 'includeJSFooter.';
			} else {
				$includeJs = 'includeJS.';
			}
			$GLOBALS['TSFE']->pSetup[$includeJs][] = trim($path);
		}
	}

	/*************************************** DISPLAY LIST ***************************************/

	/**
	 * Create a list view
	 * Exemple : $this->displayList('getAllItems', 'processItemList', 'listExtraGlobalMarker', 'ITEMS_LIST');
	 *
	 * @param string $getAllItems           function who return the SQL ressource
	 * @param string $processItemList       function who process each record
	 * @param string $listExtraGlobalMarker function who process the global array
	 * @param string $globalSubPart         global subpart
	 * @return string HTML code
	 */
	public function displayList($getAllItems, $processItemList, $listExtraGlobalMarker, $globalSubPart) {
		$res = $this->$getAllItems();

		if ($this->conf['records']['nbrecordsall'] == 0) {
			return $this->pi_getLL('noresults');
		}

		// process part

		$iItem            = 1;
		$markerArrayItems = array();

		while ($item = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$item['ii'] = $this->conf['records']['offset'] + $iItem;
			$item['i']  = $iItem++;
			if ($processItemList !== NULL) {
				$item = $this->$processItemList($item);
			}
			$markerArrayItems[] = array_merge($this->misc->convertToMarkerArray($item), $this->conf['markerslocallang']);
		}

		$this->conf['records']['recordsto']   = $this->conf['records']['offset'] + ($iItem - 1);
		$this->conf['records']['recordsfrom'] = $this->conf['records']['offset'] + 1;

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		// render part

		$markerArrayGlobal                      = array();
		$markerArrayGlobal['###PAGEBROWSER###'] = '';

		if (($this->conf['enablePageBrowser'] == 1) && ($this->conf['pageBrowserNbRecords'] > 0)) {
			$markerArrayGlobal['###PAGEBROWSER###'] = $this->getHTMLPageBrowser($this->conf['records']['nbpages']);
		}

		$markerArrayGlobal['###' . $globalSubPart . '_ITEM###'] = $this->template->renderAllTemplate(
			$markerArrayItems, '###' . $globalSubPart . '_ITEM###', $this->conf['debug']
		);

		if ($listExtraGlobalMarker !== NULL) {
			$markerArrayGlobal = array_merge(
				$markerArrayGlobal, $this->misc->convertToMarkerArray($this->$listExtraGlobalMarker()),
				$this->misc->convertToMarkerArray($this->conf['records']), $this->conf['markerslocallang']
			);
		} else {
			$markerArrayGlobal = array_merge(
				$markerArrayGlobal, $this->misc->convertToMarkerArray($this->conf['records']), $this->conf['markerslocallang']
			);
		}

		$content = $this->template->renderAllTemplate($markerArrayGlobal, '###' . $globalSubPart . '###', $this->conf['debug']);

		return $content;
	}

	/**
	 * Create a list view
	 * Same as displayList but with an array of records
	 * Exemple : $this->displayList('getAllItems', 'processItemList', 'listExtraGlobalMarker', 'ITEMS_LIST');
	 *
	 * @param string $getAllItems           function who return an array of records
	 * @param string $processItemList       function who process each record
	 * @param string $listExtraGlobalMarker function who process the global array
	 * @param string $globalSubPart         global subpart
	 * @return string HTML code
	 */
	public function displayListRows($getAllItems, $processItemList, $listExtraGlobalMarker, $globalSubPart) {
		if (is_array($getAllItems)) {
			$items                                 = $getAllItems;
			$this->conf['records']['nbrecordsall'] = count($items);
		} else {
			$items = $this->$getAllItems();
		}

		if ($this->conf['records']['nbrecordsall'] == 0) {
			return $this->pi_getLL('noresults');
		}

		// process part

		$iItem            = 1;
		$markerArrayItems = array();

		foreach ($items as $item) {
			$item['ii'] = $this->conf['records']['offset'] + $iItem;
			$item['i']  = $iItem++;
			if ($processItemList !== NULL) {
				$item = $this->$processItemList($item);
			}
			$markerArrayItems[] = array_merge($this->misc->convertToMarkerArray($item), $this->conf['markerslocallang']);
		}

		$this->conf['records']['recordsto']   = $this->conf['records']['offset'] + ($iItem - 1);
		$this->conf['records']['recordsfrom'] = $this->conf['records']['offset'] + 1;

		// render part

		$markerArrayGlobal                      = array();
		$markerArrayGlobal['###PAGEBROWSER###'] = '';

		if (($this->conf['enablePageBrowser'] == 1) && ($this->conf['pageBrowserNbRecords'] > 0)) {
			$markerArrayGlobal['###PAGEBROWSER###'] = $this->getHTMLPageBrowser($this->conf['records']['nbpages']);
		}

		$markerArrayGlobal['###' . $globalSubPart . '_ITEM###'] = $this->template->renderAllTemplate(
			$markerArrayItems, '###' . $globalSubPart . '_ITEM###', $this->conf['debug']
		);

		if ($listExtraGlobalMarker !== NULL) {
			$markerArrayGlobal = array_merge(
				$markerArrayGlobal, $this->misc->convertToMarkerArray($this->$listExtraGlobalMarker()),
				$this->misc->convertToMarkerArray($this->conf['records']), $this->conf['markerslocallang']
			);
		} else {
			$markerArrayGlobal = array_merge(
				$markerArrayGlobal, $this->misc->convertToMarkerArray($this->conf['records']), $this->conf['markerslocallang']
			);
		}

		$content = $this->template->renderAllTemplate($markerArrayGlobal, '###' . $globalSubPart . '###', $this->conf['debug']);

		return $content;
	}

	/*************************************** DISPLAY SINGLE ***************************************/

	/**
	 * Create a single view
	 *
	 * @param int    $uid                     uid of the record
	 * @param string $getItem                 function who return the SQL ressource
	 * @param string $processItem             function who process record
	 * @param string $singleExtraGlobalMarker function who process the global array
	 * @param string $globalSubPart           global subpart
	 * @return string HTML code
	 */
	public function displaySingle($uid, $getItem, $processItem, $singleExtraGlobalMarker, $globalSubPart) {
		$item = $this->$getItem($uid);
		if ($processItem !== NULL) {
			$item = $this->$processItem($item);
		}
		if ($singleExtraGlobalMarker === NULL) {
			$singleExtraGlobalMarker = array();
		}
		return $this->template->renderAllTemplate(
			array_merge(
				$this->misc->convertToMarkerArray($this->$singleExtraGlobalMarker()), $this->misc->convertToMarkerArray($item),
				$this->conf['markerslocallang']
			), '###' . $globalSubPart . '###', $this->conf['debug']
		);
	}

	/*************************************** FLEXFORMS ***************************************/

	/**
	 * Converts $this->cObj->data['pi_flexform'] from XML string to flexForm array.
	 *
	 * @param string $field     Field name to convert
	 * @return    void
	 */
	public function pi_initPIflexForm($field = 'pi_flexform') {
		// Converting flexform data into array:
		if (!is_array($this->cObj->data[$field]) && $this->cObj->data[$field]) {
			$this->cObj->data[$field] = t3lib_div::xml2array($this->cObj->data[$field]);
			if (!is_array($this->cObj->data[$field])) {
				$this->cObj->data[$field] = array();
			}
		}
	}

	/**
	 * Return value from somewhere inside a FlexForm structure
	 *
	 * @param    array      $T3FlexForm_array     FlexForm data
	 * @param    string     $fieldName            Field name to extract
	 * @param    string     $sheet                Sheet pointer, eg. "sDEF"
	 * @param    string     $lang                 Language pointer, eg. "lDEF"
	 * @param    string     $value                Value pointer, eg. "vDEF"
	 * @return    string        The content
	 */
	public function pi_getFFvalue($T3FlexForm_array, $fieldName, $sheet = 'sDEF', $lang = 'lDEF', $value = 'vDEF') {
		$sheetArray = is_array($T3FlexForm_array) ? $T3FlexForm_array['data'][$sheet][$lang] : '';
		if (is_array($sheetArray)) {
			return $this->pi_getFFvalueFromSheetArray($sheetArray, explode('/', $fieldName), $value);
		}
	}

	/**
	 * Returns part of $sheetArray pointed to by the keys in $fieldNameArray
	 *
	 * @param    array     $sheetArray      Multidimensiona array, typically FlexForm contents
	 * @param    array     $fieldNameArr    Array where each value points to a key in the FlexForms content
	 * @param    string    $value           Value for outermost key, typ. "vDEF" depending on language.
	 * @return    mixed        The value, typ. string.
	 */
	public function pi_getFFvalueFromSheetArray($sheetArray, $fieldNameArr, $value) {
		$tempArr = $sheetArray;
		foreach ($fieldNameArr as $k => $v) {
			if (t3lib_div::testInt($v)) {
				if (is_array($tempArr)) {
					$c = 0;
					foreach ($tempArr as $values) {
						if ($c == $v) {
							$tempArr = $values;
							break;
						}
						$c++;
					}
				}
			} else {
				$tempArr = $tempArr[$v];
			}
		}
		return $tempArr[$value];
	}

	/**
	 * Returns a commalist of page ids for a query (eg. 'WHERE pid IN (...)')
	 *
	 * @param    string         $pid_list  is a comma list of page ids (if empty current page is used)
	 * @param    integer        $recursive is an integer >=0 telling how deep to dig for pids under each entry in $pid_list
	 * @return    string        List of PID values (comma separated)
	 */
	public function pi_getPidList($pid_list, $recursive = 0) {
		if (!strcmp($pid_list, '')) {
			$pid_list = $GLOBALS['TSFE']->id;
		}

		$recursive = t3lib_div::intInRange($recursive, 0);

		$pid_list_arr = array_unique(t3lib_div::trimExplode(',', $pid_list, 1));
		$pid_list     = array();

		foreach ($pid_list_arr as $val) {
			$val = t3lib_div::intInRange($val, 0);
			if ($val) {
				$_list = $this->cObj->getTreeList(-1 * $val, $recursive);
				if ($_list) {
					$pid_list[] = $_list;
				}
			}
		}

		return implode(',', $pid_list);
	}

	/**
	 * Returns the localized label of the LOCAL_LANG key, $key
	 *
	 * @param    string  $key      The key from the LOCAL_LANG array for which to return the value.
	 * @return    string        The value from LOCAL_LANG.
	 */
	public function pi_getLL($key) {
		return $this->conf['locallang'][$key];
	}

	/**
	 * Wraps the input string in a <div> tag with the class attribute set to the prefixId.
	 *
	 * @param    string    $str    HTML content to wrap in the div-tags with the "main class" of the plugin
	 * @return    string           HTML content wrapped, ready to return to the parent object.
	 */
	public function pi_wrapInBaseClass($str) {
		$content = '<div class="' . str_replace('_', '-', $this->prefixId) . '">' . $str . '</div>';

		if ($this->conf['profile'] === TRUE) {
			$content .= '<!-- PROFILE ' . $this->prefixId . ': ' . $this->profileStop() . ' -->';
		}

		if ($this->conf['debug'] === TRUE) {
			tx_t3devapi_miscellaneous::debug($this->conf, 'Plugin configuration');
			if ($this->conf['profile'] === TRUE) {
				tx_t3devapi_miscellaneous::debug($this->profile, 'Profile');
			}
		}

		if (!$GLOBALS['TSFE']->config['config']['disablePrefixComment']) {
			$newContent = '<!-- BEGIN: plugin "' . $this->prefixId . '" -->';
			$newContent .= $content;
			$newContent .= '<!-- END: plugin "' . $this->prefixId . '" -->';
			return $newContent;
		}

		return $content;
	}

	/**
	 * Get a prefixed variable
	 *
	 * @param $value $value a variable
	 * @return string
	 */
	public function getPrefix($value) {
		return $this->prefixId . '[' . $value . ']';
	}

}

// No XCLASS here

?>