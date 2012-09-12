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
 * tx_t3devapi_fertehtmlarea
 * Class base to generate an rtehtmlarea
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */

require_once(t3lib_extMgm::extPath('rtehtmlarea') . 'pi2/class.tx_rtehtmlarea_pi2.php');

class tx_t3devapi_fertehtmlarea
{
	public $RTEObj;
	public $docLarge = 0;
	public $RTEcounter = 0;
	public $formName;
	public $additionalJS_initial = ''; // Initial JavaScript to be printed before the form
	public $additionalJS_pre = array(); // Additional JavaScript to be printed before the form
	public $additionalJS_post = array(); // Additional JavaScript to be printed after the form
	public $additionalJS_submit = array(); // Additional JavaScript to be executed on submit
	public $PA = array(
		'itemFormElName'  => '',
		'itemFormElValue' => '',
	);
	public $specConf = array(
		'rte_transform' => array(
			'parameters' => array('mode' => 'ts_css')
		)
	);
	public $thisConfig = array();
	public $RTEtypeVal = 'text';
	public $thePidValue;
	public $table;
	public $field;

	/**
	 * Constructor
	 * Init the RTE configurations of RTE.default.FE
	 */
	public function __construct() {
		$this->formName = 'tx_t3devapi_fertehtmlarea';
		$this->thePidValue = $GLOBALS['TSFE']->id;
		$pageTSConfig = $GLOBALS['TSFE']->getPagesTSconfig();
		$this->thisConfig = $pageTSConfig['RTE.']['default.']['FE.'];
	}

	/**
	 * Get an array with RTE markers
	 * Don't forget to add ###ADDITIONALJS_SUBMIT### to the onsubmit attribute of your form
	 *
	 * @return array
	 */
	public function drawRTE() {
		$markerArray = array();
		if (!$this->RTEObj) {
			/** @var $RTEObj tx_rtehtmlarea_pi2 */
			$this->RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');
		}
		if ($this->RTEObj->isAvailable()) {
			$RTEItem = $this->RTEObj->drawRTE($this, $this->table, $this->field, $row = array(), $this->PA, $this->specConf, $this->thisConfig, $this->RTEtypeVal, '', $this->thePidValue);
			$markerArray['###ADDITIONALJS_PRE###'] = $this->additionalJS_initial . '
		<script type="text/javascript">' . implode(chr(10), $this->additionalJS_pre) . '
		</script>';
			$markerArray['###ADDITIONALJS_POST###'] = '
		<script type="text/javascript">' . implode(chr(10), $this->additionalJS_post) . '
		</script>';
			$markerArray['###ADDITIONALJS_SUBMIT###'] = implode(';', $this->additionalJS_submit);
			$markerArray['###FORM_RTE_ENTRY###'] = $RTEItem;
		}
		return $markerArray;
	}

	/**
	 * Return the HTML code of the RTE content passed through the process
	 *
	 * @param string $val
	 * @param array  $dataArray
	 * @return string
	 */
	public function getRTEContent($val, $dataArray) {
		if (!$this->RTEObj) {
			/** @var $RTEObj tx_rtehtmlarea_pi2 */
			$this->RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');
		}
		if ($this->RTEObj->isAvailable()) {
			return $this->RTEObj->transformContent('db', $val, $this->table, $this->field, $dataArray, $this->specConf, $this->thisConfig, '', $this->thePidValue);
		}
	}

	/**
	 * Set the table
	 * @param string $table
	 */
	public function setTable($table) {
		$this->table = $table;
	}

	/**
	 * Get the table
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Set the field
	 * @param string $field
	 */
	public function setField($field) {
		$this->field = $field;
	}

	/**
	 * Get the field
	 * @return string
	 */
	public function getField() {
		return $this->field;
	}

	/**
	 * Set the PA
	 * @param array $PA
	 */
	public function setPA($PA) {
		$this->PA = $PA;
	}

	/**
	 * Get the PA
	 * @return array
	 */
	public function getPA() {
		return $this->PA;
	}

	/**
	 * Set the RTE counter
	 * @param int $RTEcounter
	 */
	public function setRTEcounter($RTEcounter) {
		$this->RTEcounter = $RTEcounter;
	}

	/**
	 * Get the RTE counter
	 * @return int
	 */
	public function getRTEcounter() {
		return $this->RTEcounter;
	}

	/**
	 * Get the form name
	 * @return string
	 */
	public function getFormName() {
		return $this->formName;
	}

	/**
	 * Set the form name
	 * @param string
	 */
	public function setFormName($formName) {
		$this->formName = $formName;
	}

}

?>