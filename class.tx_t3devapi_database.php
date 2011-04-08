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
	 * Executes a select based on input query parts array
	 *
	 * @param	array		Query parts array
	 * @return	pointer		MySQL select result pointer / DBAL object
	 * @see exec_SELECTquery()
	 */

	function exec_SELECT_queryArray($queryParts, $debug)
	{
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);

		if (($GLOBALS['TYPO3_DB']->sql_error()) || ($debug === true)) {
			$debug = array();
			$debug['queryParts'] = $queryParts;
			$debug['sql'] = self::SELECT_queryArray($queryParts);
			$debug['error'] = $GLOBALS['TYPO3_DB']->sql_error();
			$debug['php'] = tx_t3devapi_miscellaneous::get_caller_method();
			t3lib_div::debug($debug, $GLOBALS['TYPO3_DB']->sql_error());
		}

		return $res;
	}

	/**
	 * Return a select based on input query parts array
	 *
	 * @param	array		Query parts array
	 * @return	pointer		MySQL select result pointer / DBAL object
	 * @see exec_SELECTquery()
	 */

	function SELECT_queryArray($queryParts)
	{
		return $GLOBALS['TYPO3_DB']->SELECTquery(
			$queryParts['SELECT'],
			$queryParts['FROM'],
			$queryParts['WHERE'],
			$queryParts['GROUPBY'],
			$queryParts['ORDERBY'],
			$queryParts['LIMIT']
		);
	}

	/**
	 * Get all the data according to the TCA (time,relation, etc...) from a sql ressource.
	 *
	 * Example :
	 * $result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
	 * $records = t3lib_div::makeInstance('tx_t3devapi_database');
	 * $rows = $records->getAllResults($result, $query['FROM']);
	 *
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
				$recordList [] = self::getResultRowTitles($row, $table);
			}
			$recordList [] = self::getResultRow($row, $table);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
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
	 *
	 * @param  $res
	 * @param  $table
	 * @return array
	 */

	function formatAllResults($res, $table, $title)
	{
		$content = '';

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="100">';
		$content .= $title;
		$content .= '</td></tr>';

		$first = 1;

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($first) {
				$first = 0;
				$headers = self::getResultRowTitles($row, $table);
				$content .= '<tr class="c-headLine">';
				foreach ($headers as $header) {
					$content .= '<td class="cell">' . $header . '</td>';
				}
				$content .= '</tr>';
			}
			$records = self::getResultRow($row, $table);
			$content .= '<tr class="db_list_normal">';
			foreach ($records as $record) {
				$content .= '<td class="cell">' . $record . '</td>';
			}
			$content .= '</tr>';
		}

		$content .= '</table>';

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $content;
	}

	/**
	 *
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
	 *
	 * @param  $row
	 * @param  $table
	 * @return array
	 */

	function getResultRow($row, $table)
	{
		$record = array();
		foreach ($row as $fieldName => $fieldValue) {
			if ((TYPO3_MODE == 'FE')) {
				$GLOBALS['TSFE']->includeTCA();
			}
			$record[$fieldName] = t3lib_BEfunc::getProcessedValueExtra($table, $fieldName, $fieldValue, 0, $row['uid']);
		}
		return $record;
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class.tx_t3devapi_database.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class.tx_t3devapi_database.php']);
}

?>