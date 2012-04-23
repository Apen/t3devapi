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
 * tx_t3devapi_export
 * Class to export datas
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class tx_t3devapi_export
{

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * tx_t3devapi_export::exportRecordsToXML()
	 * Example :
	 * $query['SELECT'] = 'uid,title,category';
	 * $query['FROM'] = 'tt_news';
	 * $query['WHERE'] = '';
	 *
	 * @param array $query
	 * @return string
	 */
	public function exportRecordsToXML($query) {
		$xmlObj = t3lib_div::makeInstance('t3lib_xml', 'typo3_export');
		$xmlObj->setRecFields($query['FROM'], $query['SELECT']);
		$xmlObj->renderHeader();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$query['SELECT'],
			$query['FROM'],
			$query['WHERE'],
			$query['GROUPBY'],
			$query['ORDERBY'],
			$query['LIMIT']
		);
		$xmlObj->renderRecords($query['FROM'], $res);
		$xmlObj->renderFooter();
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $xmlObj->getResult();
	}

}

tx_t3devapi_miscellaneous::XCLASS('ext/t3devapi/class.tx_t3devapi_export.php');

?>