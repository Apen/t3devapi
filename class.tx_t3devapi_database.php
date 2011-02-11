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
 * tx_t3devapi_export
 * Class to generate some generic records array
 *
 * @author Yohann
 * @copyright Copyright (c) 2011
 */

class tx_t3devapi_database
{

	/**
	 * tx_t3devapi_database::__construct()
	 */

	function __construct()
	{
	}

	/**
	 * Get all the data according to the TCA (time,relation, etc...) from a sql ressource.
	 *
	 * Example :
	 * $result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
	 * $records = t3lib_div::makeInstance('tx_t3devapi_database');
	 * $rows = $records->getAllResults($result, $query['FROM']);
	 * @param  $res
	 * @param  $table
	 * @return array
	 */

	function getAllResults($res, $table)
	{
		$first = 1;
		$recordList = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($first) {
				$first = 0;
				$recordList [] = $this->getResultRowTitles($row, $table);
			}
			$recordList [] = $this->getResultRow($row, $table);
		}
		return $recordList;
	}

	/**
	 * Get all the data according to the TCA (time,relation, etc...) from a sql ressource.
	 * With the List view style
	 *
	 * Example :
	 * $result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
	 * $records = t3lib_div::makeInstance('tx_t3devapi_database');
	 * $rows = $records->getAllResults($result, $query['FROM']);
	 * @param  $res
	 * @param  $table
	 * @return array
	 */

	function formatAllResults($res, $table, $title)
	{
		$content = '';

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="10">';
		$content .= $title;
		$content .= '</td></tr>';

		$first = 1;

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($first) {
				$first = 0;
				$headers = $this->getResultRowTitles($row, $table);
				$content .= '<tr class="c-headLine">';
				foreach ($headers as $header) {
					$content .= '<td class="cell">' . $header . '</td>';
				}
				$content .= '</tr>';
			}
			$records = $this->getResultRow($row, $table);
			$content .= '<tr class="db_list_normal">';
			foreach ($records as $record) {
				$content .= '<td class="cell">' . $record . '</td>';
			}
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;
	}

	/**
	 * @param  $row
	 * @param  $table
	 * @return array
	 */

	function getResultRowTitles($row, $table)
	{
		global $TCA;
		$tableHeader = array();
		$conf = $TCA[$table];
		foreach ($row as $fieldName => $fieldValue) {
			$title = $GLOBALS['LANG']->sL($conf['columns'][$fieldName]['label'] ? $conf['columns'][$fieldName]['label'] : $fieldName, 1);
			$tableHeader[$fieldName] = $title;
		}
		return $tableHeader;
	}

	/**
	 * @param  $row
	 * @param  $table
	 * @return array
	 */

	function getResultRow($row, $table)
	{
		$record = array();
		foreach ($row as $fieldName => $fieldValue) {
			$record[$fieldName] = $this->getProcessedValueExtra($table, $fieldName, $fieldValue, null, ',');
		}
		return $record;
	}

	/**
	 * @param  $table
	 * @param  $fN
	 * @param  $fV
	 * @param  $conf
	 * @param  $splitString
	 * @return string
	 */

	function getProcessedValueExtra($table, $fN, $fV, $conf, $splitString)
	{
		global $TCA;
		// Analysing the fields in the table.
		if (is_array($TCA[$table])) {
			t3lib_div::loadTCA($table);
			$fC = $TCA[$table]['columns'][$fN];
			$fields = $fC['config'];
			$fields['exclude'] = $fC['exclude'];
			if (is_array($fC) && $fC['label']) {
				$fields['label'] = preg_replace('/:$/', '', trim($GLOBALS['LANG']->sL($fC['label'])));

				switch ($fields['type']) {
					case 'input':
						if (preg_match('/int|year/i', $fields['eval'])) {
							$fields['type'] = 'number';
						} elseif (preg_match('/time/i', $fields['eval'])) {
							$fields['type'] = 'time';
						} elseif (preg_match('/date/i', $fields['eval'])) {
							$fields['type'] = 'date';
						} else {
							$fields['type'] = 'text';
						}
						break;
					case 'check':
						if (!$fields['items']) {
							$fields['type'] = 'boolean';
						} else {
							$fields['type'] = 'binary';
						}
						break;
					case 'radio':
						$fields['type'] = 'multiple';
						break;
					case 'select':
						$fields['type'] = 'multiple';
						if ($fields['foreign_table']) {
							$fields['type'] = 'relation';
						}
						if ($fields['special']) {
							$fields['type'] = 'text';
						}
						break;
					case 'group':
						$fields['type'] = 'files';
						if ($fields['internal_type'] == 'db') {
							$fields['type'] = 'relation';
						}
						break;
					case 'user':
					case 'flex':
					case 'passthrough':
					case 'none':
					case 'text':
					default:
						$fields['type'] = 'text';
						break;
				}
			} else {
				$fields['label'] = '[FIELD: ' . $fN . ']';
				switch ($fN) {
					case 'pid':
						$fields['type'] = 'relation';
						$fields['allowed'] = 'pages';
						break;
					case 'cruser_id':
						$fields['type'] = 'relation';
						$fields['allowed'] = 'be_users';
						break;
					case 'tstamp':
					case 'crdate':
						$fields['type'] = 'time';
						break;
					default:
						$fields['type'] = 'number';
						break;
				}
			}
		}

		switch ($fields['type']) {
			case 'date':
				if ($fV != -1) {
					$out = strftime('%e-%m-%Y', $fV);
				}
				break;
			case 'time':
				if ($fV != -1) {
					if ($splitString == '<br />') {
						$out = strftime('%H:%M' . $splitString . '%e-%m-%Y', $fV);
					} else {
						$out = strftime('%H:%M %e/%m/%Y', $fV);
					}
				}
				break;
			case 'multiple':
			case 'binary':
			case 'relation':
				$fullsearch = t3lib_div::makeInstance('t3lib_fullsearch');
				$out = $fullsearch->makeValueList($fN, $fV, $fields, $table, $splitString);
				break;
			case 'boolean':
				$out = $fV ? 'True' : 'False';
				break;
			case 'files':
			default:
				$out = htmlspecialchars($fV);
				break;
		}
		return $out;
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class..tx_t3devapi_database.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class..tx_t3devapi_database.php']);
}

?>