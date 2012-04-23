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
 * tx_t3devapi_config
 * Class to get all the configuration in a plugin (typoscript, flexform, piVars, extConf)
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class tx_t3devapi_config
{
	// Parent object
	protected $conf = NULL;
	protected $cObj = NULL;

	/**
	 * Constructor
	 *
	 * @param mixed $pObj
	 */
	public function __construct($pObj) {
		$this->cObj = $pObj->cObj;
		$this->conf = $pObj->conf;
	}

	/**
	 * This function get all configurations from ts, flexform, getpost...
	 *
	 * @param boolean $debug
	 * @return array
	 */
	public function getArrayConfig($debug = FALSE) {
		// TYPOSCRIPT = template with plugin.tx_xxxx_pi1.xxxx = xxxx
		$arrayConfig = $this->conf;

		// Init and get the flexform data of the plugin
		$this->pi_initPIflexForm();
		$flexConfig = array();
		$piFlexForm = array();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		if (isset($piFlexForm['data'])) {
			foreach ($piFlexForm['data'] as $sheet => $data) {
				foreach ($data as $lang => $value) {
					foreach ($value as $key => $val) {
						$flexConfig[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
					}
				}
			}
		}

		// test contentId to know if this content is concerned by piVars
		($arrayConfig['contentId'] == $this->cObj->data['uid']) ? $arrayConfig['piVars'] = 1 : $arrayConfig['piVars'] = 0;
		// add "ext_conf_template.txt"
		if ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]) {
			$extConf     = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
			$arrayConfig = array_merge($arrayConfig, $extConf);
		}

		// add the pi config "plugin.tx_xxxx_pi1 = xxxx" which is not imported in the $conf
		if (is_array($GLOBALS['TSFE']->tmpl->setup['plugin.'][$this->prefixId . '.'])) {
			$arrayConfig = array_merge($arrayConfig, $GLOBALS['TSFE']->tmpl->setup['plugin.'][$this->prefixId . '.']);
		}

		// add $piVars
		$arrayConfig = array_merge($arrayConfig, $this->piVars);

		// merge TYPOSCRIPT with FLEXFORM
		$arrayConfig = array_merge($flexConfig, $arrayConfig);

		if ($debug == TRUE) {
			tx_t3devapi_miscellaneous::debug($arrayConfig);
		}

		return $arrayConfig;
	}
}

tx_t3devapi_miscellaneous::XCLASS('ext/t3devapi/class.tx_t3devapi_config.php');

?>