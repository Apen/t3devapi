<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Yohann CERDAN <cerdanyohann@yahoo.fr>
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
 * tx_t3devapi_tceforms
 * Class to create some tceforms in frontend
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class tx_t3devapi_tceforms {

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * List all the fields (string)
	 *
	 * @var string
	 */
	protected $fields;

	/**
	 * List all the fields (array)
	 *
	 * @var array
	 */
	protected $aFields;

	/**
	 * Current uid of the record (0=new)
	 *
	 * @var int
	 */
	protected $uid;

	/**
	 * TCA table of teh current table
	 *
	 * @var array
	 */
	protected $tca;

	/**
	 * Ref to the parent plugin
	 *
	 * @var $pObj tx_recordsmanagerfe_pi1
	 */
	protected $pObj;

	/**
	 * On submit form code
	 *
	 * @var string
	 */
	protected $onSubmit;

	/**
	 * Number of RTE on the page
	 *
	 * @var int
	 */
	protected $rteCounter;

	/**
	 * Name of the form
	 *
	 * @var string
	 */
	protected $formName;

	/**
	 * URL to submit
	 *
	 * @var string
	 */
	protected $actionUrl;

	/**
	 * List the default readonly fields
	 *
	 * @var string
	 */
	protected $readOnlyFields;

	/**
	 * The current record with all fields
	 * This is needed for the typoscript mapping
	 *
	 * @var array
	 */
	protected $currentRecord;

	/**
	 * Prefix use for URL parameter
	 *
	 * @var string
	 */
	protected $prefixId;

	/**
	 * Extension key
	 *
	 * @var string
	 */
	protected $extKey;

	/**
	 * Plugin parameters
	 *
	 * @var array
	 */
	protected $piVars;

	/**
	 * javascript code to validate fields
	 *
	 * @var string
	 */
	protected $addJs;

	/**
	 * Constructor
	 *
	 * @param tx_t3devapi_pibase $pObj
	 */
	public function __construct($pObj) {
		require_once(PATH_site . 'typo3conf/ext/t3devapi/Classes/class.tx_t3devapi_fertehtmlarea.php');
		$this->pObj = $pObj;
		$this->rteCounter = 0;
		$this->formName = 'txT3devapiTceforms';
		$this->actionUrl = '';
		$this->validation = new tx_t3devapi_validate();
		$this->readOnlyFields = 'uid,pid,deleted,cruser_id';
		$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
		$GLOBALS['LANG']->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
	}

	/**
	 * Generate the marker array to display the edit form
	 *
	 * @return array
	 */
	public function renderEditForm() {
		$content = '';
		$markerArray = array();
		$markerArray['###INFOS###'] = '';
		$markerArray['###ERRORS###'] = '';

		// init
		if ($this->uid == 0) {
			// insert mode
			$record = $this->getEmptyRecord($this->fields);
			// generate ts additionnal markers for display only
			$markerArray = array_merge($markerArray, tx_t3devapi_miscellaneous::convertToMarkerArray($this->checkMappingValues(array(), $this->pObj->conf['insertMappingMarkers'])));
		} else {
			// edit mode
			$whereCondition = '';
			if (!empty($this->pObj->conf['whereEdit'])) {
				$whereCondition = ' ' . str_replace(array_keys($this->pObj->feuserMarkers), array_values($this->pObj->feuserMarkers), $this->pObj->conf['whereEdit']);
			}
			$record = t3lib_BEfunc::getRecord($this->table, $this->uid, $this->fields, $whereCondition);
			$this->currentRecord = t3lib_BEfunc::getRecord($this->table, $this->uid, '*');
			if ($record === NULL) {
				return NULL;
			}
			// generate ts additionnal markers for display only
			$markerArray = array_merge($markerArray, tx_t3devapi_miscellaneous::convertToMarkerArray($this->checkMappingValues(array(), $this->pObj->conf['editMappingMarkers'])));
		}

		// if submit
		if (!empty($this->piVars['submit'])) {
			$datas = $this->getAllFormValues();
			// if no errors
			if (count($this->validation->getErrors()) === 0) {
				$datas = $this->formatAllFormValues($datas);
				if ($this->uid == 0) {
					$this->insertRecord($datas);
				} else {
					$this->updateRecord($datas);
					$record = t3lib_BEfunc::getRecord($this->table, $this->uid, $this->fields);
				}
			} else {
				// TODO: remettre les files
				// fill all the fields with the current piVars
				$record = $this->getPiVarsRecord($this->fields);
				$record = $this->formatAllFormValues($record);
			}
		}

		// group fields by fieldset (--div--)
		if (!empty($this->pObj->conf['enableFieldset'])) {
			// get first tca types
			$tcaTypesFirst = each($this->tca['types']);
			$allFieldsWithFieldset = preg_split('/,\s*(--div--\s*;.*?),/', $tcaTypesFirst['value']['showitem'], 0, PREG_SPLIT_DELIM_CAPTURE);
			$recordWithFieldset = array();
			// generate all fields with fieldset
			$div = 'LLL:EXT:lang/locallang_core.php:labels.generalTab';
			foreach ($allFieldsWithFieldset as $fieldsWithFieldset) {
				if (stristr($fieldsWithFieldset, '--div--')) {
					$divLabel = t3lib_div::trimExplode(';', $fieldsWithFieldset);
					$div = $divLabel[1];
				} else {
					$fields = t3lib_div::trimExplode(',', $fieldsWithFieldset);
					foreach ($fields as $field) {
						if (array_key_exists($field, $record)) {
							$field = preg_replace('/;.*/', '', $field);
							$recordWithFieldset[$div][] = $field;
						}
					}
				}
			}
			foreach ($recordWithFieldset as $fieldset => $fields) {
				$content .= '<fieldset><legend>' . $GLOBALS['LANG']->sL($fieldset) . '</legend>';
				foreach ($fields as $field) {
					$val = $this->formatSpecialValueBeforeDisplay($field, $record[$field]);
					$content .= $this->getFieldFromTca($field, $val);
					$markerArray['###' . strtoupper($field) . '_VAL###'] = $val;
				}
				$content .= '</fieldset>';
			}
		} else {
			// classic rendering - generate all fields
			foreach ($record as $field => $val) {
				$val = $this->formatSpecialValueBeforeDisplay($field, $val);
				$content .= $this->getFieldFromTca($field, $val);
				$markerArray['###' . strtoupper($field) . '_VAL###'] = $val;
			}
		}

		// submit button
		$submit = tx_t3devapi_html::renderSubmit($this->getPrefix('submit'), $this->getLabel('submit'));
		$content .= tx_t3devapi_html::renderDiv($this->extKey . '_' . $this->pObj->cObj->data['uid'] . '_submit', $submit);

		// form wrap
		$content = tx_t3devapi_html::renderForm($this->formName, $content, array('action' => $this->actionUrl, 'enctype' => 'multipart/form-data', 'onsubmit' => $this->formName . 'Submit();'));

		// on submit JS (required for rtehtmlarea)
		$content = tx_t3devapi_html::renderScriptJs('function ' . $this->formName . 'Submit() { ' . $this->onSubmit . '}') . $content;

		if (!empty($this->piVars['submit'])) {
			if (count($this->validation->getErrors()) > 0) {
				// display errors
				$errorsList = $this->validation->getErrors();
				$errorsContent = '';
				$errorsContent .= '<div class="alert alert-error">' . $this->getLabel('errors') . '<ul>';
				foreach ($errorsList as $error) {
					$errorsContent .= '<li>' . $error . '</li>';
				}
				$errorsContent .= '</ul></div>';
				$markerArray['###ERRORS###'] = $errorsContent;
			} else {
				// display infos
				$infosContent = '<div class="alert alert-success">' . $this->getLabel('updateok') . '</div>';
				$markerArray['###INFOS###'] = $infosContent;
			}
		}
		if (!empty($this->piVars['insertok'])) {
			$infosContent = '<div class="alert alert-success">' . $this->getLabel('updateok') . '</div>';
			$markerArray['###INFOS###'] = $infosContent;
		}

		// add js if needed
		$content .= $this->getAddjs();

		$markerArray['###FORM###'] = $content;

		return $markerArray;
	}

	/**
	 * Get all the field value (after checking them)
	 *
	 * @return array
	 */
	public function getAllFormValues() {
		$datas = array();
		foreach ($this->aFields as $field) {
			if (($this->uid > 0) && ($field == 'password') && empty($this->piVars[$field])) {

			} else {
				$datas = array_merge($datas, $this->checkFieldFromTca($field, $this->piVars[$field]));
			}
		}
		unset($datas['uid']);
		return $datas;
	}

	/**
	 * Insert a record in database
	 *
	 * @param $datas
	 * @return void
	 */
	public function insertRecord($datas) {
		$datas['pid'] = $this->pObj->conf['insertPID'];
		$datas = $this->checkMappingValues($datas, $this->pObj->conf['insertMapping']);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->table, $datas);
		$this->uid = $uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		$this->writeCustomLog('insert', $datas);
		// recheck to insert MM relation if needed
		$this->checkMappingValues($datas, $this->pObj->conf['insertMapping']);
		$this->getAllFormValues();
		$datas['uid'] = $this->uid;
		$this->hookForInsertRecord($datas);
		//t3lib_utility_Http::redirect($this->pObj->getEditUrl($uid) . '&' . $this->prefixId . '[insertok]=1');
		t3lib_utility_Http::redirect($this->pObj->getListUrl());
	}

	/**
	 * Update a record in database
	 *
	 * @param array $datas
	 * @return void
	 */
	public function updateRecord($datas) {
		$datas = $this->checkMappingValues($datas, $this->pObj->conf['editMapping']);
		if (isset($datas['password']) && empty($datas['password'])) {
			unset($datas['password']);
		}
		//t3lib_div::debug($GLOBALS['TYPO3_DB']->UPDATEquery($this->table, 'uid=' . $this->uid, $datas));
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table, 'uid=' . $this->uid, $datas);
		$this->writeCustomLog('edit', $datas);
		$this->hookForUpdateRecord($datas);
		if (!empty($this->pObj->conf['editRedirect'])) {
			t3lib_utility_Http::redirect($this->pObj->getListUrl());
		}
	}

	/**
	 * Hook method to do process after inserting record
	 *
	 * @param array $datas
	 */
	public function hookForInsertRecord($datas) {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['recordsmanagerfe']['hookForInsertRecord'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['recordsmanagerfe']['hookForInsertRecord'] as $classRef) {
				$procObj = & t3lib_div::getUserObj($classRef);
				$procObj->hookForInsertRecord($this, $datas);
			}
		}
	}

	/**
	 * Hook method to do process after updating record
	 *
	 * @param array $datas
	 */
	public function hookForUpdateRecord($datas) {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['recordsmanagerfe']['hookForUpdateRecord'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['recordsmanagerfe']['hookForUpdateRecord'] as $classRef) {
				$procObj = & t3lib_div::getUserObj($classRef);
				$procObj->hookForUpdateRecord($this, $datas);
			}
		}
	}

	/**
	 * Generate a field from the tca
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function getFieldFromTca($field, $val) {
		$content = '';

		$config = $this->getFieldConfig($field);
		$labelLl = $this->getFieldLabel($field);
		$label = $labelLl . $this->getExtraLabel($field);

		if (!empty($label)) {
			$content .= tx_t3devapi_html::renderLabel($this->getPrefix($field), $label);
		} else {
			$content .= tx_t3devapi_html::renderLabel($this->getPrefix($field), $field . ' : ');
		}

		//t3lib_div::debug(array($this->tca['columns'][$field], $config, $field, $val), $field);

		switch ($config['type']) {
			case 'input':
				$content .= $this->getFieldFromTcaInput($field, $val);
				break;
			case 'text':
				$content .= $this->getFieldFromTcaText($field, $val);
				break;
			case 'check':
				$content .= $this->getFieldFromTcaCheck($field, $val);
				break;
			case 'radio':
				$content .= $this->getFieldFromTcaRadio($field, $val);
				break;
			case 'select':
				$content .= $this->getFieldFromTcaSelect($field, $val);
				break;
			case 'group':
				$content .= $this->getFieldFromTcaGroup($field, $val);
				break;
			default:
				$content .= $this->getFieldFromTcaInput($field, $val);
				break;
		}

		$content = tx_t3devapi_html::renderDiv($this->extKey . '_' . $this->pObj->cObj->data['uid'] . '_' . $field, $content, array('class' => 'formItem'));

		return $content;
	}

	/**
	 * Get extra label to display an help on specific field
	 *
	 * @param string $field
	 * @return string
	 */
	public function getExtraLabel($field) {
		$config = $this->getFieldConfig($field);
		$evalList = explode(',', $config['eval']);
		$content = '';
		foreach ($evalList as $eval) {
			switch ($eval) {
				case 'required':
					$content .= ' <span class="required">*</span>';
					break;
				case 'date':
					$content .= ' (jj/mm/aaaa)';
					break;
				case 'datetime':
					$content .= ' (jj/mm/aaaa hh:mm)';
					break;
			}
		}
		return $content;
	}

	/**
	 * Generate a field from a tca type "input"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function getFieldFromTcaInput($field, $val) {
		$config = $this->getFieldConfig($field);

		$attributes = array();

		if (t3lib_div::inList($this->readOnlyFields, $field)) {
			$attributes['readonly'] = 'readonly';
		}

		if (t3lib_div::inList($config['eval'], 'required')) {
			$attributes['required'] = 'required';
		}

		if (t3lib_div::inList($config['eval'], 'password')) {
			// always display a blank password in edit mode
			if ($this->uid > 0) {
				$val = '';
				unset($attributes['required']);
			}
			return tx_t3devapi_html::renderPassword($this->getPrefix($field), $val, $attributes);
		}

		if (t3lib_div::inList($config['eval'], 'datetime')) {
			$id = tx_t3devapi_html::cleanId($this->getPrefix($field));
			$this->addJs .= "$('#" . $id . "').mobiscroll().datetime({lang:'" . $GLOBALS['TSFE']->lang . "',display:'bubble',mode:'clickpick'});\r\n";
		}

		if (t3lib_div::inList($config['eval'], 'date')) {
			$id = tx_t3devapi_html::cleanId($this->getPrefix($field));
			$this->addJs .= "$('#" . $id . "').mobiscroll().date({lang:'" . $GLOBALS['TSFE']->lang . "',display:'bubble',mode:'clickpick'});\r\n";
		}

		if (!empty($config['readOnly'])) {
			$attributes['readonly'] = 'readonly';
		}

		if (!empty($config)) {
			return tx_t3devapi_html::renderText($this->getPrefix($field), $val, $attributes);
		}

		$attributes['readonly'] = 'readonly';

		// default
		return tx_t3devapi_html::renderText($this->getPrefix($field), $val, $attributes);
	}

	/**
	 * Generate a field from a tca type "text"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function getFieldFromTcaText($field, $val) {
		$config = $this->getFieldConfig($field);

		if (!empty ($config['wizards']['RTE'])) {
			$this->rteCounter++;
			$rte = new tx_t3devapi_fertehtmlarea();
			$rte->setTable($this->table);
			$rte->setField($field);
			$rte->setPA(array('itemFormElName' => $this->getPrefix($field), 'itemFormElValue' => ($val)));
			$rte->setRTEcounter($this->rteCounter);
			$rte->setFormName($this->formName);
			$rte->setWidth('400px');
			$rte->setHeight('300px');
			$markerArray = $rte->drawRTE();
			unset($rte);
			$this->onSubmit .= $markerArray['###ADDITIONALJS_SUBMIT###'];
			return $markerArray['###ADDITIONALJS_PRE###'] . $markerArray['###FORM_RTE_ENTRY###'] . $markerArray['###ADDITIONALJS_POST###'];
		}

		$attributes = array();

		if (!empty($config['cols'])) {
			$attributes['cols'] = $config['cols'];
		}

		if (!empty($config['rows'])) {
			$attributes['rows'] = $config['rows'];
		}

		return tx_t3devapi_html::renderTextArea($this->getPrefix($field), $val, $attributes);
	}

	/**
	 * Generate a field from a tca type "check"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function getFieldFromTcaCheck($field, $val) {
		$config = $this->getFieldConfig($field);
		$attributes = array();
		$countCheckFields = count($config['items']);

		if ($countCheckFields > 1) {
			$content = '';
			$val = str_split(strrev(decbin($val)));

			foreach ($config['items'] as $itemKey => $item) {
				$contentCheck = '';
				if ($val[$itemKey] == 1) {
					$attributes['checked'] = 'checked';
				}
				$contentCheck .= tx_t3devapi_html::renderCheckbox($this->getPrefix($field) . '[' . $itemKey . ']', $itemKey, array(), $attributes);
				//$contentCheck .= tx_t3devapi_html::renderCheckbox($this->getPrefix($field) . '[' . $itemKey . ']', 1, array(), $attributes);
				$contentCheck .= tx_t3devapi_html::renderLabel($this->getPrefix($field) . '[' . $itemKey . ']', $GLOBALS['TSFE']->sL($item[0]));
				$content .= '<div id="' . $this->extKey . '_' . $this->pObj->cObj->data['uid'] . '_' . $field . '_check_' . $itemKey . '" class="">' . $contentCheck . '</div>';
				unset($attributes['checked']);
			}

			return $content;
		} else {
			return tx_t3devapi_html::renderCheckbox($this->getPrefix($field), 1, array($val));
		}
	}

	/**
	 * Generate a field from a tca type "radio"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function getFieldFromTcaRadio($field, $val) {
		$config = $this->getFieldConfig($field);
		$attributes = array();
		$content = '';

		if (!empty($config['items'])) {
			foreach ($config['items'] as $itemKey => $item) {
				$contentRadio = '';
				if ($val == $itemKey) {
					$attributes['checked'] = 'checked';
				}
				$id = $this->getPrefix($field) . '_' . $itemKey;
				$attributes['id'] = tx_t3devapi_html::cleanId($id);
				$contentRadio .= tx_t3devapi_html::renderRadio($this->getPrefix($field), $itemKey, array(), $attributes);
				$contentRadio .= tx_t3devapi_html::renderLabel($id, $GLOBALS['TSFE']->sL($item[0]));
				$content .= '<div id="' . $this->extKey . '_' . $this->pObj->cObj->data['uid'] . '_' . $field . '_radio_' . $itemKey . '" class="">' . $contentRadio . '</div>';
				unset($attributes['checked']);
			}
		}

		return $content;
	}

	/**
	 * Generate a field from a tca type "select"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function getFieldFromTcaSelect($field, $val) {
		$config = $this->getFieldConfig($field);
		$aVal = explode(',', $val);
		$optionList = array();
		$attributes = array();

		if (!empty($config['items'])) {
			foreach ($config['items'] as $item) {
				$optionList[$item[1]] = $GLOBALS['TSFE']->sL($item[0]);
			}
		}

		if (!empty($config['foreign_table'])) {
			if (!empty($config['MM'])) {
				if ($val > 0) {
					if ($config['maxitems'] > 1) {
						$aVal = array();
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $config['MM'], 'uid_local=' . $this->uid);
						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
							$aVal[] = $row['uid_foreign'];
						}
						$GLOBALS['TYPO3_DB']->sql_free_result($res);
					} else {
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $config['MM'], 'uid_local=' . $this->uid);
						$val = $res[0]['uid_foreign'];
					}
				}
			}
			$table = $config['foreign_table'];
			$where = strtolower(substr(trim($config['foreign_table_where']), 0, 3));
			$where = trim(($where == 'and' || $where == 'or ' || $where == 'gro' || $where == 'ord' || $where == 'lim') ? '1 ' . $config['foreign_table_where'] : $config['foreign_table_where']);
			$select = 'uid,' . $GLOBALS['TCA'][$table]['ctrl']['label'];
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$optionList[$row['uid']] = $row[$GLOBALS['TCA'][$table]['ctrl']['label']];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}

		/*if (!empty($config['readOnly'])) {
			$attributes['disabled'] = 'disabled';
		}*/

		if (!empty($config['size'])) {
			$attributes['size'] = $config['size'];
		}

		if ((count($aVal) > 1) || ($config['maxitems'] > 1)) {
			return tx_t3devapi_html::renderMultipleSelect($this->getPrefix($field) . '[]', $optionList, $aVal, $attributes);
		} else {
			return tx_t3devapi_html::renderSelect($this->getPrefix($field), $optionList, $val, $attributes);
		}

	}

	/**
	 * Generate a field from a tca type "group"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function getFieldFromTcaGroup($field, $val) {
		$config = $this->getFieldConfig($field);
		$attributes = array();
		$aVal = explode(',', $val);

		if ($config['internal_type'] == 'file') {
			$content = '';
			$contentList = '';
			$uploadFolder = $config['uploadfolder'];

			if (substr($uploadFolder, -1) != '/') {
				$uploadFolder = $uploadFolder . '/';
			}

			if (!empty($val)) {
				$contentList = '<ul>';

				foreach ($aVal as $keyFile => $file) {
					$currentFile = $uploadFolder . $file;

					$attributes['id'] = tx_t3devapi_html::cleanId($this->getPrefix($field . 'del') . '[]') . $keyFile;
					$contentCheck = tx_t3devapi_html::renderCheckbox($this->getPrefix($field . 'del') . '[]', $file, array(), $attributes);
					$contentCheck .= tx_t3devapi_html::renderLabel($attributes['id'], $this->getLabel('deletefile'));

					$contentList .= '<li>';

					if (getimagesize($currentFile) !== FALSE) {
						$contentList .= '<a class="img" href="' . $currentFile . '" target="_blank">' . $this->resizeImg($currentFile, $file, '', '40', '40c') . '</a>';
					}

					$contentList .= '<a class="path" href="' . $currentFile . '" target="_blank">' . $file . '</a>';
					$contentList .= $contentCheck . '</li>';

					$content .= tx_t3devapi_html::renderHidden($this->getPrefix($field) . '[]', $file, array('id' => ''));
				}
				$contentList .= '</ul>';
			}

			// $content .= $contentList . tx_t3devapi_html::renderInputFile($this->getPrefix($field));

			// prepare other input file
			$content .= $contentList;
			if (!empty($aVal[0])) {
				$nbToDisplay = $config['maxitems'] - count($aVal);
			} else {
				$nbToDisplay = $config['maxitems'];
			}
			for ($i = 1; $i <= $nbToDisplay; $i++) {
				$id = tx_t3devapi_html::cleanId($this->getPrefix($field) . '_' . $i);
				if ($i === 1) {
					$content .= tx_t3devapi_html::renderInputFile($this->getPrefix($field) . '[]', array('id' => $id));
				} else {
					$content .= tx_t3devapi_html::renderInputFile($this->getPrefix($field) . '[]', array('style' => 'display:none;padding-top:5px;', 'id' => $id));
				}
			}

			$content .= '<script type="text/javascript">';
			$content .= '$(document).ready(function(){';
			$content .= '$("#' . ($this->extKey . '_' . $this->pObj->cObj->data['uid'] . '_' . $field) . ' input[type=file]").on("change", function(){';
			$content .= 'if ($(this).next().is("input[type=file]")) {';
			$content .= 'if ($(this).val() != "") {';
			$content .= '$(this).next().show();';
			$content .= '} else {';
			$content .= '$(this).next().hide();';
			$content .= '}';
			$content .= '}';
			$content .= '});';
			$content .= '});';
			$content .= '</script>';

			return $content;
		}

		if ($config['internal_type'] == 'db') {
			if (!empty($config['MM'])) {
				return 'TODO: manage MM for internal_type=db (like in tt_news)';
			}

			$arrItems = array();
			$tableItems = array();
			$arrAllowed = t3lib_div::trimExplode(',', $config['allowed'], TRUE);

			if (count($arrAllowed) == 1) {
				$table = $arrAllowed[0];
				if ($GLOBALS['TCA'][$table]) {

					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, ' . $GLOBALS['TCA'][$table]['ctrl']['label'], $table, '1 ' . $this->pObj->cObj->enableFields($table));

					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						$tableItems[$row['uid']] = $row[$GLOBALS['TCA'][$table]['ctrl']['label']];
					}

					$GLOBALS['TYPO3_DB']->sql_free_result($res);
				}

				return tx_t3devapi_html::renderMultipleSelect($this->getPrefix($field) . '[]', $tableItems, $aVal, $attributes);
			} else {
				foreach ($arrAllowed as $table) {
					if ($GLOBALS['TCA'][$table]) {
						$tableItems = array();
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, ' . $GLOBALS['TCA'][$table]['ctrl']['label'], $table, '1 ' . $this->pObj->cObj->enableFields($table));

						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
							$tableItems[$table . '_' . $row['uid']] = $row[$GLOBALS['TCA'][$table]['ctrl']['label']];
						}

						$GLOBALS['TYPO3_DB']->sql_free_result($res);
						$arrItems[$table] = $tableItems;
					}
				}

				return tx_t3devapi_html::renderMultipleSelectWithGroup($this->getPrefix($field) . '[]', $arrItems, $aVal, $attributes);
			}
		}

		return $val;
	}

	/**
	 * Check and validate field of all the types
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function checkFieldFromTca($field, $val) {
		$datas = '';

		$config = $this->getFieldConfig($field);

		switch ($config['type']) {
			case 'input':
				$datas[$field] = $this->checkFieldFromTcaInput($field, $val);
				break;
			case 'text':
				$datas[$field] = $this->checkFieldFromTcaText($field, $val);
				break;
			case 'check':
				$datas[$field] = $this->checkFieldFromTcaCheck($field, $val);
				break;
			case 'radio':
				$datas[$field] = $this->checkFieldFromTcaRadio($field, $val);
				break;
			case 'select':
				$datas[$field] = $this->checkFieldFromTcaSelect($field, $val);
				break;
			case 'group':
				$datas[$field] = $this->checkFieldFromTcaGroup($field, $val);
				break;
			default:
				$datas[$field] = $this->checkFieldFromTcaInput($field, $val);
				break;
		}

		$this->checkFieldFromTcaEval($field, $datas[$field]);

		return $datas;
	}

	/**
	 * Validate a field with his eval field
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return void
	 */
	public function checkFieldFromTcaEval($field, $val) {
		$config = $this->getFieldConfig($field);
		$labelLl = $this->getFieldLabel($field);
		// don't check empty fields not required
		if (empty($val) && !t3lib_div::inList($config['eval'], 'required')) {
			return $val;
		}
		$evalList = explode(',', $config['eval']);
		foreach ($evalList as $eval) {
			switch ($eval) {
				case 'required':
					$this->validation->isRequired($val, sprintf($this->getLabel('errors.required'), $labelLl));
					if (($config['type'] == 'check') && ($val == 0)) {
						$this->validation->isRequired(NULL, sprintf($this->getLabel('errors.required'), $labelLl));
					}
					break;
				case 'uniqueInPid':
					$rows = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows($field, $this->table, 'deleted=0 AND ' . $field . "='" . $val . "'");
					if ($rows > 0) {
						$this->validation->setError(sprintf($this->getLabel('errors.unique'), $labelLl, $val));
					}
					break;
				case 'trim':
					$val = trim($val);
					break;
				case 'lower':
					$val = strtolower($val);
					break;
				case 'upper':
					$val = strtoupper($val);
					break;
				case 'nospace':
					$val = preg_replace('/\s+/', '', $val);
					break;
				case 'date':
					$this->validation->isDate($val, '%e/%m/%Y', sprintf($this->getLabel('errors.date'), $labelLl));
					break;
				case 'datetime':
					$this->validation->isDate($val, '%e/%m/%Y %H:%M', sprintf($this->getLabel('errors.datetime'), $labelLl));
					break;
				case 'email':
					$this->validation->isEmail($val, sprintf($this->getLabel('errors.email'), $labelLl));
					break;
			}
		}
	}

	/**
	 * Format all values depending of his declaration in the TCA (before saving)
	 *
	 * @param array $datas
	 * @return array
	 */
	public function formatAllFormValues($datas) {
		foreach ($this->aFields as $field) {
			if (!empty($datas[$field])) {
				$datas[$field] = $this->formatSpecialValueBeforeSave($field, $datas[$field]);
			}
		}
		return $datas;
	}

	/**
	 * Format a value depending of his declaratin in the TCA (before saving)
	 * For exmaple, it formalize valid date
	 *
	 * @param string $field
	 * @param string $val
	 * @return string
	 */
	public function formatSpecialValueBeforeSave($field, $val) {
		$config = $this->getFieldConfig($field);
		$evalList = explode(',', $config['eval']);
		foreach ($evalList as $eval) {
			switch ($eval) {
				case 'date':
					$valDate = strptime($val, '%e/%m/%Y');
					$val = mktime($valDate['tm_hour'], $valDate['tm_min'], $valDate['tm_sec'], $valDate['tm_mon'] + 1, $valDate['tm_mday'], $valDate['tm_year'] + 1900);
					break;
				case 'datetime':
					$valDate = strptime($val, '%e/%m/%Y %H:%M');
					$val = mktime($valDate['tm_hour'], $valDate['tm_min'], $valDate['tm_sec'], $valDate['tm_mon'] + 1, $valDate['tm_mday'], $valDate['tm_year'] + 1900);
					break;
			}
		}
		return $val;
	}

	/**
	 * Format a value depending of his declaratin in the TCA (before diplaying)
	 * For exmaple, it formalize valid date
	 *
	 * @param string $field
	 * @param string $val
	 * @return string
	 */
	public function formatSpecialValueBeforeDisplay($field, $val) {
		if (!empty($val)) {
			$config = $this->getFieldConfig($field);
			$evalList = explode(',', $config['eval']);
			foreach ($evalList as $eval) {
				switch ($eval) {
					case 'date':
						$val = strftime('%e/%m/%Y', $val);
						break;
					case 'datetime':
						$val = strftime('%e/%m/%Y %H:%M', $val);
						break;
				}
			}
		}
		return $val;
	}

	/**
	 * Check and validate field of type "input"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function checkFieldFromTcaInput($field, $val) {
		$config = $this->getFieldConfig($field);

		if (t3lib_div::inList($config['eval'], 'password')) {
			if (t3lib_extMgm::isLoaded('saltedpasswords') && $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel']) {
				$saltedpasswords = tx_saltedpasswords_div::returnExtConf();
				if ($saltedpasswords['enabled']) {
					$txSaltedpasswords = t3lib_div::makeInstance($saltedpasswords['saltedPWHashingMethod']);
					$val = $txSaltedpasswords->getHashedPassword($val);
				}
			}
		}

		return $val;
	}

	/**
	 * Check and validate field of type "text"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function checkFieldFromTcaText($field, $val) {
		$config = $this->getFieldConfig($field);
		if (!empty($config['wizards']['RTE'])) {
			// transform
			$rte = new tx_t3devapi_fertehtmlarea();
			$rte->setTable($this->table);
			$rte->setField($field);
			$rte->setPA(array('itemFormElName' => $this->getPrefix($field), 'itemFormElValue' => $val));
			$rte->setFormName($this->formName);
			return $rte->getRTEContent($val, $this->piVars);
		} else {
			return $val;
		}
	}

	/**
	 * Check and validate field of type "check"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function checkFieldFromTcaCheck($field, $val) {
		$config = $this->getFieldConfig($field);
		$countCheckFields = count($config['items']);
		if ($countCheckFields > 1) {
			$binString = '';
			for ($i = 0; $i < $countCheckFields; $i++) {
				if (in_array($i, $val)) {
					$binString .= '1';
				} else {
					$binString .= '0';
				}
			}
			return bindec(strrev($binString));
		} else {
			if (!empty($val)) {
				return 1;
			} else {
				return 0;
			}
		}
	}

	/**
	 * Check and validate field of type "radio"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function checkFieldFromTcaRadio($field, $val) {
		//$config = $this->getFieldConfig($field);
		return $val;
	}

	/**
	 * Check and validate field of type "select"
	 *
	 * @param string       $field
	 * @param string|array $val
	 * @return string
	 */
	public function checkFieldFromTcaSelect($field, $val) {
		$config = $this->getFieldConfig($field);
		if (is_array($val)) {
			if (!empty($config['MM'])) {
				foreach ($val as $valUid) {
					$this->checkFieldFromTcaSelectMm($field, $valUid);
				}
				// count number of element
				$GLOBALS['TYPO3_DB']->exec_DELETEquery($config['MM'], 'uid_local=' . $this->uid . ' AND uid_foreign NOT IN (' . implode(',', $val) . ')');
				$elementsAfter = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $config['MM'], 'uid_local=' . $this->uid);
				return count($elementsAfter);
			}
			return implode(',', $val);
		} else {
			if (!empty($config['MM'])) {
				$this->checkFieldFromTcaSelectMm($field, $val);
			}
			return $val;
		}
	}

	/**
	 * Check and validate field of type "select" with MM
	 *
	 * @param string $field
	 * @param array  $val
	 * @return void
	 */
	public function checkFieldFromTcaSelectMm($field, $val) {
		$config = $this->getFieldConfig($field);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $config['MM'], 'uid_local=' . $this->uid . ' AND uid_foreign=' . $val);
		if (count($res) > 0) {
			// already in DB, nothing
		} else {
			// insert
			$GLOBALS['TYPO3_DB']->exec_INSERTquery(
				$config['MM'],
				array('uid_local' => $this->uid, 'uid_foreign' => $val)
			);
		}
	}

	/**
	 * Check and validate field of type "group"
	 *
	 * @param string $field
	 * @param array  $val
	 * @return string
	 */
	public function checkFieldFromTcaGroup($field, $val) {
		$config = $this->getFieldConfig($field);

		if ($config['internal_type'] == 'file') {
			$existingFiles = array();

			// delete files
			if (!empty($val)) {
				foreach ($val as $file) {
					if ((!empty($this->piVars[$field . 'del'])) && (in_array($file, $this->piVars[$field . 'del']))) {
						@unlink(PATH_site . $config['uploadfolder'] . '/' . $file);
					} else {
						$existingFiles[] = $file;
					}
				}
			}

			$uploadedFilenames = array_filter($_FILES[$this->prefixId]['name'][$field]);
			$uploadedTmpFilenames = array_filter($_FILES[$this->prefixId]['tmp_name'][$field]);
			// upload new file
			if (!empty($uploadedFilenames)) {
				if (!empty($config['maxitems'])) {
					if (count($existingFiles) == $config['maxitems']) {
						$this->validation->setError(sprintf($this->getLabel('uploadlimit'), $config['maxitems']));
					}
				}

				foreach ($uploadedFilenames as $keyFilename => $filename) {
					$file = $uploadedTmpFilenames[$keyFilename];
					$fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
					$name = $fileFunc->getUniqueName($filename, PATH_site . $config['uploadfolder']);
					$fileName = substr($name, strlen(PATH_site . $config['uploadfolder']) + 1);
					$ext = pathinfo($fileName, PATHINFO_EXTENSION);
					if (!empty($config['allowed'])) {
						if ($this->validation->isInList($ext, $config['allowed'], sprintf($this->getLabel('errors.fileextension'), $this->getFieldLabel($field), $config['allowed'])
							) === TRUE
						) {
							t3lib_div::upload_copy_move($file, $name);
							$existingFiles[] = $fileName;
						}
					} else {
						t3lib_div::upload_copy_move($file, $name);
						$existingFiles[] = $fileName;
					}
				}
			}

			return implode(',', $existingFiles);
		}

		if (empty($val)) {
			return $val;
		}

		if ($config['internal_type'] == 'db') {
			return implode(',', $val);
		}

		return $val;
	}

	/**
	 * Check all the fields given by the typoscript of the flexform
	 * It can intialize different values
	 *
	 * @param array $datas
	 * @param array $tsArray
	 * @return array
	 */
	public function checkMappingValues($datas, $tsArray) {
		if (!empty($tsArray)) {
			foreach ($tsArray as $extraKey => $extraField) {
				$name = explode('.', $extraKey);
				$name = (string)$name[0];
				$config = $this->tca['columns'][$name]['config'];
				switch ($config['type']) {
					case 'select':
						// process default value for database relation (with MM or not)
						if (!empty($extraField[$name . '.']['value'])) {
							$datas[$name] = $this->checkFieldFromTcaSelect($name, explode(',', $extraField[$name . '.']['value']));
							break;
						}
					default:
						$lCobj = t3lib_div::makeInstance('tslib_cObj');
						$lCobj->start($this->currentRecord, $this->table);
						$datas[$name] = $lCobj->cObjGetSingle($extraField[$name], $extraField[$name . '.']);
						break;
				}
			}
		}
		return $datas;
	}

	/**
	 * Generate a record array with empty value (to initialize insert mode)
	 *
	 * @param string $fields
	 * @return array
	 */
	public function getEmptyRecord($fields) {
		$record = array();
		$fields = explode(',', $fields);
		foreach ($fields as $field) {
			$record[$field] = '';
			if (!empty($this->pObj->conf['insertMappingInit'][$field . '.'])) {
				$record[$field] = $this->pObj->cObj->cObjGetSingle($this->pObj->conf['insertMappingInit'][$field . '.'][$field], $this->pObj->conf['insertMappingInit'][$field . '.'][$field . '.']);
			}
		}
		return $record;
	}

	/**
	 * Generate a record array with piVars
	 *
	 * @param string $fields
	 * @return array
	 */
	public function getPiVarsRecord($fields) {
		$record = array();
		$fields = explode(',', $fields);
		foreach ($fields as $field) {
			$config = $this->getFieldConfig($field);
			$record[$field] = $this->piVars[$field];
			// process uploaded files that are not send in piVars
			if (($config['type'] == 'group') && ($config['internal_type'] == 'file')) {
				$record[$field] = $this->currentRecord[$field];
			}
		}
		return $record;
	}

	/**
	 * Read and affect tca parameters if they are defined in the conf
	 * ex: tca.fe_users.columns.name.config.eval = required
	 */
	public function addTsTca() {
		if (!empty($this->pObj->conf['tca.'][$this->table . '.'])) {
			$tsTca = tx_t3devapi_miscellaneous::stripDotInTsArray($this->pObj->conf['tca.'][$this->table . '.']);
			$this->tca = tx_t3devapi_miscellaneous::arrayMergeRecursiveReplace($this->tca, $tsTca);
		}
	}

	/**
	 * Set the table to process
	 *
	 * @param string $table
	 * @return void
	 */
	public function setTable($table) {
		$this->table = $table;
		$this->tca = tx_t3devapi_miscellaneous::getTableTCA($table);
		$this->addTsTca();
	}

	/**
	 * Return the table
	 *
	 * @return string $table
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Set the fields to process
	 *
	 * @param string $fields
	 * @return void
	 */
	public function setFields($fields) {
		$this->fields = $fields;
		$this->aFields = explode(',', $fields);
	}

	/**
	 * Set the uid of the current record
	 *
	 * @param int $uid
	 * @return void
	 */
	public function setUid($uid) {
		$this->uid = $uid;
	}

	/**
	 * Set the form name
	 *
	 * @param string $formName
	 * @return void
	 */
	public function setFormName($formName) {
		$this->formName = $formName;
	}

	/**
	 * Set the action url
	 *
	 * @param string $actionUrl
	 * @return void
	 */
	public function setActionUrl($actionUrl) {
		$this->actionUrl = $actionUrl;
	}

	/**
	 * Get the current record
	 *
	 * @return array
	 */
	public function getCurrentRecord() {
		return $this->currentRecord;
	}

	/**
	 * Return the config array of a field
	 *
	 * @param string $field
	 * @return array
	 */
	public function getFieldConfig($field) {
		return $this->tca['columns'][$field]['config'];
	}

	/**
	 * Return the label of a field
	 *
	 * @param string $field
	 * @return string
	 */
	public function getFieldLabel($field) {
		$labelLl = $GLOBALS['TSFE']->sL($this->tca['columns'][$field]['label']);
		if (!empty($this->pObj->conf['locallang'][$field])) {
			$labelLl = $this->pObj->conf['locallang'][$field];
		}
		return $labelLl;
	}

	/**
	 * Return the label for a key
	 *
	 * @param string $key
	 * @return string
	 */
	public function getLabel($key) {
		return $this->pObj->conf['locallang'][$key];
	}

	/**
	 * Get the plugin conf
	 *
	 * @return array
	 */
	public function getConf() {
		return $this->pObj->conf;
	}

	/**
	 * Set a prefixId
	 *
	 * @param string $prefixId
	 */
	public function setPrefixId($prefixId) {
		$this->prefixId = $prefixId;
		$this->piVars = t3lib_div::_GPmerged($this->prefixId);
	}

	/**
	 * Set the extension key
	 *
	 * @param string $extKey
	 */
	public function setExtKey($extKey) {
		$this->extKey = $extKey;
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

	/**
	 * Resize an image with image magick
	 * Prefer the cImage method to have access to all the parameters of cImage
	 *
	 * @param string  $image
	 * @param string  $title
	 * @param string  $alt
	 * @param string  $maxW
	 * @param string  $maxH
	 * @param boolean $crop
	 * @return string the image (HTML)
	 */
	public function resizeImg($image, $title, $alt, $maxW, $maxH, $crop = FALSE) {
		$img['file'] = $image;
		$lConf['file.']['maxH'] = $maxH;
		$lConf['file.']['maxW'] = $maxW;
		$lConf['altText'] = $alt;
		$lConf['titleText'] = $title;

		$lConf['emptyTitleHandling'] = 'removeAttr';
		// force crop
		if ($crop == TRUE) {
			$lConf['file.']['height'] = $maxH . 'c';
			$lConf['file.']['width'] = $maxW . 'c';
		}

		return $this->pObj->cObj->cImage($img['file'], $lConf);
	}

	/**
	 * get js for calendar
	 *
	 * @return string
	 */
	protected function getAddjs() {
		if (!empty($this->addJs)) {
			$content = '$(document).ready(function() {' . "\r\n" . $this->addJs . "\r\n" . '});';
			return tx_t3devapi_html::renderScriptJs($content);
		} else {
			return '';
		}
	}

	public function writeCustomLog($actionType, $datas) {
		$uidrecordlabel = $this->uid;
		if (!empty($datas[$this->tca['ctrl']['label']])) {
			$uidrecordlabel = $datas[$this->tca['ctrl']['label']];
		}
		if (!empty($this->currentRecord[$this->tca['ctrl']['label']])) {
			$uidrecordlabel = $this->currentRecord[$this->tca['ctrl']['label']];
		}
		$datas = array(
			'pid'             => 0,
			'deleted'         => 0,
			'tstamp'          => time(),
			'actiontype'      => $actionType,
			'actiontypelabel' => $this->getLabel($actionType),
			'tablename'       => $this->table,
			'tablenamelabel'  => $GLOBALS['LANG']->sL($this->tca['ctrl']['title']),
			'uidrecord'       => $this->uid,
			'uidrecordlabel'  => $uidrecordlabel,
			'datas'           => serialize($datas),
			'feuser'          => $GLOBALS['TSFE']->fe_user->user['uid'],
			'feuserlabel'     => $GLOBALS['TSFE']->fe_user->user[$GLOBALS['TCA']['fe_users']['ctrl']['label']],
			'pageuid'         => $GLOBALS['TSFE']->id,
			'pageuidlabel'    => $GLOBALS['TSFE']->page[$GLOBALS['TCA']['pages']['ctrl']['label']],
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_recordsmanagerfe_log', $datas);
	}

}

?>