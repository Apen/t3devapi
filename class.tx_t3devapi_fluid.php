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
 * tx_t3devapi_fluid
 * Class to use fluid in your plugin without extbase structuration
 *
 * @author Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package TYPO3
 * @subpackage t3devapi
 */
class tx_t3devapi_fluid
{
	protected $template = NULL;

	/**
	 * Constructor
	 *
	 * @param mixed $template
	 */
	public function __construct($template) {
		$this->template = $template;
	}

	/**
	 * Loads a template file and generate the content
	 *
	 * @param array $context datas to send
	 * @return string
	 */
	public function fluidView($context = array()) {
		$renderer = t3lib_div::makeInstance('Tx_Fluid_View_TemplateView');
		$controllerContext = t3lib_div::makeInstance('Tx_Extbase_MVC_Controller_ControllerContext');
		$controllerContext->setRequest(t3lib_div::makeInstance('Tx_Extbase_MVC_Web_Request'));
		$renderer->setControllerContext($controllerContext);
		$renderer->setPartialRootPath(t3lib_extMgm::extPath($this->extKey) . "res/partials/");
		$renderer->setTemplateRootPath(t3lib_extMgm::extPath($this->extKey) . "res/templates/");
		$renderer->setLayoutRootPath(t3lib_extMgm::extPath($this->extKey) . "res/layouts");
		$renderer->setTemplatePathAndFilename($this->template);

		foreach ($context as $key => $value) {
			$renderer->assign($key, $value);
		}

		return $renderer->render();
	}
}

tx_t3devapi_miscellaneous::XCLASS('ext/t3devapi/class.tx_t3devapi_fluid.php');

?>