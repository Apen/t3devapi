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


class Tx_T3devapi_Templating_Test extends Tx_Phpunit_TestCase
{
	/**
	 * @var Tx_Phpunit_Framework
	 */
	private $testingFramework;

	protected $template = NULL;

	public function setUp() {
		$this->testingFramework = new Tx_Phpunit_Framework('t3devapi');
		$this->conf = array();
		$this->conf['locallang']['unittest'] = 'Unit test';
		$this->template = new tx_t3devapi_templating($this);
		$this->template->initTemplate(PATH_site . 'typo3conf/ext/t3devapi/Tests/Resources/template.html');
	}

	/**
	 * initTemplate
	 *
	 * @test
	 */
	public function initTemplate() {
		$this->assertEquals($this->template->templateContent, $this->getContentTemplateFile('template.html'));
	}

	/**
	 * renderBasic
	 *
	 * @test
	 */
	public function renderBasic() {
		$markersArray = array();
		$markersArray[] = tx_t3devapi_miscellaneous::convertToMarkerArray(array('i' => 1, 'uid' => 1));
		$markersArray[] = tx_t3devapi_miscellaneous::convertToMarkerArray(array('i' => 2, 'uid' => 2));
		$htmlGenerated = $this->template->renderAllTemplate($markersArray, '###ITEMS_LIST_ITEM###');
		$markersArray = array();
		$markersArray = tx_t3devapi_miscellaneous::convertToMarkerArray(array('title' => test, 'items_list_item' => $htmlGenerated));
		$htmlGenerated = $this->template->renderAllTemplate($markersArray, '###ITEMS_LIST###');
		//t3lib_div::writeFile(PATH_site . 'typo3conf/ext/t3devapi/Tests/Resources/renderBasic.html', $htmlGenerated);
		$this->assertEquals($htmlGenerated, $this->getContentTemplateFile('renderBasic.html'));
	}

	/**
	 * renderLll
	 *
	 * @test
	 */
	public function renderLll() {
		$markersArray = array();
		$htmlGenerated = $this->template->renderAllTemplate($markersArray, '###ITEM_LLL###');
		$this->assertEquals($htmlGenerated, $this->getContentTemplateFile('renderLll.html'));
	}

	/**
	 * renderEmpty
	 *
	 * @test
	 */
	public function renderEmpty() {
		$markersArray = tx_t3devapi_miscellaneous::convertToMarkerArray(array('i' => 1, 'uid' => 1));
		$htmlGenerated = $this->template->renderAllTemplate($markersArray, '###ITEM_EMPTY###');
		$this->assertEquals($htmlGenerated, $this->getContentTemplateFile('renderEmpty.html'));
	}

	/**
	 * renderNotEmpty
	 *
	 * @test
	 */
	public function renderNotEmpty() {
		$markersArray = tx_t3devapi_miscellaneous::convertToMarkerArray(array('i' => 1, 'uid' => 1));
		$htmlGenerated = $this->template->renderAllTemplate($markersArray, '###ITEM_NOTEMPTY###');
		$this->assertEquals($htmlGenerated, $this->getContentTemplateFile('renderNotEmpty.html'));
	}

	/**
	 * renderCrop
	 *
	 * @test
	 */
	public function renderCrop() {
		$markersArray = tx_t3devapi_miscellaneous::convertToMarkerArray(array('text' => 'lorem ipsum lorem ipsum'));
		$htmlGenerated = $this->template->renderAllTemplate($markersArray, '###ITEM_CROP###');
		$this->assertEquals($htmlGenerated, $this->getContentTemplateFile('renderCrop.html'));
	}

	/**
	 * renderIf
	 *
	 * @test
	 */
	public function renderIf() {
		$markersArray = tx_t3devapi_miscellaneous::convertToMarkerArray(array('i' => 1, 'uid' => 1));
		$htmlGenerated = $this->template->renderAllTemplate($markersArray, '###ITEM_IF###');
		$this->assertEquals($htmlGenerated, $this->getContentTemplateFile('renderIf.html'));
	}

	/**
	 * renderTs
	 *
	 * @test
	 */
	public function renderTs() {
		$uidPage = 1;
		$this->assertFalse($this->checkPageExist($uidPage), 'Page with uid ' . $uidPage . ' should exist and be displayed');
		tx_t3devapi_miscellaneous::buildTSFE($uidPage);
		$markersArray = array();
		$htmlGenerated = $this->template->renderAllTemplate($markersArray, '###ITEM_TS###');
		$this->assertEquals($htmlGenerated, $this->getContentTemplateFile('renderTs.html'));
	}

	/**
	 * renderLink
	 *
	 * @test
	 */
	public function renderLink() {
		$uidPage = 1;
		$this->assertFalse($this->checkPageExist($uidPage), 'Page with uid ' . $uidPage . ' should exist and be displayed');
		tx_t3devapi_miscellaneous::buildTSFE($uidPage);
		$this->cObj = $GLOBALS['TSFE']->cObj;
		$this->setUp();
		$markersArray = array();
		$htmlGenerated = $this->template->renderAllTemplate($markersArray, '###ITEM_LINK###');
		$this->assertEquals($htmlGenerated, $this->getContentTemplateFile('renderLink.html'));
	}

	public function checkPageExist($uid) {
		$query = array();
		$query['SELECT'] = 'uid';
		$query['FROM'] = 'pages';
		$query['WHERE'] = 'uid=' . intval($uid) . ' AND deleted=0 AND hidden=0';
		$res = tx_t3devapi_database::exec_SELECT_queryArray($query);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return empty($row);
	}

	/**
	 * Get content of a template file
	 *
	 * @param string $file
	 * @return string
	 */
	protected function getContentTemplateFile($file) {
		return t3lib_div::getURL(PATH_site . 'typo3conf/ext/t3devapi/Tests/Resources/' . $file);
	}
}

?>