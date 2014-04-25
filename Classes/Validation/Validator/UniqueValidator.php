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
 * Tx_T3devapi_Validation_Validator_UniqueValidator
 * Class to validate a unique record in model
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_Validation_Validator_UniqueValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * Inject the objectManager
	 *
	 * @param Tx_Extbase_Object_ObjectManager objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	public function isValid($value) {
		if ($this->options['repository'] && $this->options['property']) {
			$repository = $this->objectManager->get($this->options['repository']);
			$methodName = 'countBy' . ucfirst($this->options['property']);
			$count = $repository->$methodName($value);
			if ($count == 0) {
				return TRUE;
			} else {
				$this->addError($this->options['property'] . ' is not unique', 1398325655);
				return FALSE;
			}
		}
	}

}