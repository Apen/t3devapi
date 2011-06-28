<?php

/* * *************************************************************
*
* Copyright notice
*
* (c) 20101 Yohann CERDAN <ycerdan@onext.fr>
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
 * tx_t3devapi_pibase
 * Class to extends pibase
 *
 * @author Yohann
 * @copyright Copyright (c) 2011
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');

class tx_t3devapi_pibase extends tslib_pibase
{
	public $profile;
	public $conf = null;
	public $template = null;
	public $misc = null;

	public function init() {
		// load ll and piVars
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// needed classes
		$this->template = new tx_t3devapi_templating(&$this);
		$this->misc = new tx_t3devapi_miscellaneous(&$this);
		$this->conf = tx_t3devapi_config::getArrayConfig();

		// Additionnal TS
		if (empty($this->conf['myTS']) === false) {
			$this->conf = $this->misc->loadTS($this->conf, $this->conf['myTS']);
		}

		// Debug
		$this->conf['debug'] = ($this->conf['debug'] == 1) ? true : false;

		// Profile
		$this->conf['profile'] = ($this->conf['profile'] == 1) ? true : false;

		// Path to the HTML template
		if (empty($this->conf['templateFile']) === false) {
			$this->addTemplate($this->conf['templateFile']);
		} else {
			$this->addTemplate('typo3conf/ext/' . $this->extKey . '/res/template.html');
		}

		// locallangs
		$this->conf['locallang'] = $this->misc->loadLL('typo3conf/ext/' . $this->extKey . '/' . dirname($this->scriptRelPath) . '/locallang.xml');
		$this->conf['markerslocallang'] = $this->misc->convertToMarkerArray($this->conf['locallang'], 'LLL:');
	}

	/*************************************** DB ***************************************/

	public function getRecord($from, $uid, $select = '*') {
		$query['SELECT'] = $select;
		$query['FROM'] = $from;
		$query['WHERE'] = 'uid=' . intval($uid);
		$res = tx_t3devapi_database::exec_SELECT_queryArray($query, $this->conf['debug']);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $row;
	}

	public function getEnableFields($table, $addWhere = '') {
		$content = '1=1';
		$content .= ' ' . $this->cObj->enableFields($table);
		$content .= (empty($this->conf['pidList']) === false) ? ' AND ' . $table . '.pid IN (' . $this->conf['pidList'] . ')' : '';
		$content .= (empty($addWhere) === false) ? ' ' . $addWhere : '';
		return $content;
	}

	public function getOrderBy($orderBy = '', $ascDesc = '') {
		$content = '';
		$content .= (empty($orderBy) === false) ? $orderBy : '';
		$content .= (empty($orderBy) === false) ? ' ' . $ascDesc : '';
		return $content;
	}

	public function getLimit($limit = '', $start = '') {
		$content = '';
		$content .= (empty($limit) === false) ? $limit : '';
		if (empty($start) === false) {
			if (empty($limit) === false) {
				$content = $start . ',' . $limit;
			} else {
				$content = $start . ',10000000000';
			}
		}
		return $content;
	}

	public function getListPageBrowser() {
		$this->conf['records']['pagebrowsernbrecords'] = $this->conf['pageBrowserNbRecords'];
		$this->conf['records']['nbpages'] = ceil(intval($this->conf['records']['nbrecordsall']) / intval($this->conf['pageBrowserNbRecords']));
		$this->conf['records']['offset'] = isset($this->piVars['page']) ? $this->piVars['page'] * $this->conf['pageBrowserNbRecords'] : 0;
		$this->conf['query']['LIMIT'] = $this->conf['records']['offset'] . ',' . $this->conf['pageBrowserNbRecords'];
		return tx_t3devapi_database::exec_SELECT_queryArray($this->conf['query'], $this->conf['debug']);
	}

	/*************************************** PROFILE ***************************************/

	public function profileStart() {
		$this->profile['parsetime'] = microtime(true);
		$this->profile['mem'] = tx_t3devapi_miscellaneous::getMemoryUsage();
	}

	public function profileStop() {
		$content = '';
		$this->profile['parsetime'] = (microtime(true) - $this->profile['parsetime']) . ' ms';
		$this->profile['mem'] = tx_t3devapi_miscellaneous::getMemoryUsage() - $this->profile['mem'] . ' ko (total:' . tx_t3devapi_miscellaneous::getMemoryUsage() . 'ko)';
		$content .= '<!-- ' . $this->prefixId . ' / ' . $this->profile['parsetime'] . ' / ' . $this->profile['mem'] . ' -->';
		return $content;
	}

	/*************************************** PAGEBROWSER (with tx_pagebrowse_pi1) ***************************************/

	function getHTMLPageBrowser($numberOfPages) {
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_pagebrowse_pi1.'];
		$conf += array(
			'pageParameterName' => $this->prefixId . '|page',
			'numberOfPages' => $numberOfPages,
		);
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->start(array(), '');
		return $cObj->cObjGetSingle('USER', $conf);
	}

	/*************************************** TEMPLATE ***************************************/

	public function addTemplate($path) {
		$this->template->initTemplate(trim($path), $this->conf['debug']);
	}

	/*************************************** CSS ***************************************/

	public function addCSS($path) {
		if ($this->cObj->getUserObjectType() == tslib_cObj::OBJECTTYPE_USER_INT) {
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey . 'css'] = '<link rel="stylesheet" type="text/css" href="' . trim($path) . '" media="all">';
		} else {
			$GLOBALS['TSFE']->pSetup['includeCSS.'][$this->extKey] = trim($path);
			$GLOBALS['TSFE']->pSetup['includeCSS.'][$this->extKey . '.'] = array('media' => 'screen');
		}
	}

	/*************************************** JS ***************************************/

	public function addJS($path, $includeInFooter = false) {
		if ($this->cObj->getUserObjectType() == tslib_cObj::OBJECTTYPE_USER_INT) {
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey . 'js'] = '<script src="' . trim($path) . '" type="text/javascript"></script>';
		} else {
			if ($includeInFooter === 1) {
				$includeJs = 'includeJSFooter.';
			} else {
				$includeJs = 'includeJS.';
			}
			$GLOBALS['TSFE']->pSetup[$includeJs] [] = trim($path);
		}
	}

}

// No XCLASS here

?>