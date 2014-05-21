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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * ViewHelper to meta tags
 *
 * Example
 * <t3devapi:titleTag>{object.title}</t3devapi:titleTag>
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_TitleTagViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface An instance of the Configuration Manager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}


	/**
	 * Override the title tag
	 *
	 * @param boolean $concat
	 * @return void
	 */
	public function render($concat = FALSE) {
		$content = $this->renderChildren();
		$contentObjectData = $this->configurationManager->getContentObject()->getUserObjectType();
		if (!empty($content) && ($this->configurationManager->getContentObject()->getUserObjectType() == tslib_cObj::OBJECTTYPE_USER)) {
			if ($concat === TRUE) {
				$GLOBALS['TSFE']->page['title'] .= $content;
				$GLOBALS['TSFE']->indexedDocTitle .= $content;
			} else {
				$GLOBALS['TSFE']->page['title'] = $content;
				$GLOBALS['TSFE']->indexedDocTitle = $content;
			}
		} else {
			if ($concat === TRUE) {
				$GLOBALS['TSFE']->content = preg_replace('@<title>(.*?)</title>@i', '<title>' . $content . ' $1</title>', $GLOBALS['TSFE']->content);
			} else {
				$GLOBALS['TSFE']->content = preg_replace('@<title>(.*?)</title>@i', '<title>' . $content . '</title>', $GLOBALS['TSFE']->content);
			}
		}
	}
}

?>