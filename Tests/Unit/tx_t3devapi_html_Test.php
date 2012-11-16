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


class Tx_T3devapi_Html_Test extends Tx_Phpunit_TestCase
{
	/**
	 * @var Tx_Phpunit_Framework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new Tx_Phpunit_Framework('t3devapi');
	}

	/**
	 * renderLabel
	 *
	 * @test
	 */
	public function renderLabel() {
		$htmlGenerated = tx_t3devapi_html::renderLabel('test', 'test');
		$html = '<label for="test">test</label>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderInput
	 *
	 * @test
	 */
	public function renderInput() {
		$htmlGenerated = tx_t3devapi_html::renderInput('text', 'test', 'test');
		$html = '<input type="text" name="test" id="test" value="test" />';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderText
	 *
	 * @test
	 */
	public function renderText() {
		$htmlGenerated = tx_t3devapi_html::renderText('test', 'test', array('class' => 'test'));
		$html = '<input type="text" name="test" id="test" value="test" class="test" />';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderHidden
	 *
	 * @test
	 */
	public function renderHidden() {
		$htmlGenerated = tx_t3devapi_html::renderHidden('test', 'test', array('class' => 'test'));
		$html = '<input type="hidden" name="test" id="test" value="test" class="test" />';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderButton
	 *
	 * @test
	 */
	public function renderButton() {
		$htmlGenerated = tx_t3devapi_html::renderButton('test', 'test', array('class' => 'test'));
		$html = '<input type="button" name="test" id="test" value="test" class="test" />';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderPassword
	 *
	 * @test
	 */
	public function renderPassword() {
		$htmlGenerated = tx_t3devapi_html::renderPassword('test', 'test', array('class' => 'test'));
		$html = '<input type="password" name="test" id="test" value="test" class="test" />';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderReset
	 *
	 * @test
	 */
	public function renderReset() {
		$htmlGenerated = tx_t3devapi_html::renderReset('test', 'test', array('class' => 'test'));
		$html = '<input type="reset" name="test" id="test" value="test" class="test" />';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderSubmit
	 *
	 * @test
	 */
	public function renderSubmit() {
		$htmlGenerated = tx_t3devapi_html::renderSubmit('test', 'test', array('class' => 'test'));
		$html = '<input type="submit" name="test" id="test" value="test" class="test" />';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderSubmit
	 *
	 * @test
	 */
	public function renderInputFile() {
		$htmlGenerated = tx_t3devapi_html::renderInputFile('test');
		$html = '<input type="file" name="test" id="test" />';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderTextArea
	 *
	 * @test
	 */
	public function renderTextArea() {
		$htmlGenerated = tx_t3devapi_html::renderTextArea('test', 'test de contenu', array('class' => 'test'));
		$html = '<textarea name="test" id="test" class="test">test de contenu</textarea>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderDiv
	 *
	 * @test
	 */
	public function renderDiv() {
		$htmlGenerated = tx_t3devapi_html::renderDiv('test', 'test');
		$html = '<div id="test">test</div>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderDiv
	 *
	 * @test
	 */
	public function renderScriptJs() {
		$htmlGenerated = tx_t3devapi_html::renderScriptJs('test');
		$html = '<script type="text/javascript">test</script>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderForm
	 *
	 * @test
	 */
	public function renderForm() {
		$htmlGenerated = tx_t3devapi_html::renderForm('test', 'test');
		$html = '<form name="test" method="post">test</form>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}


	/**
	 * renderCheckbox
	 *
	 * @test
	 */
	public function renderCheckbox() {
		$htmlGenerated = tx_t3devapi_html::renderCheckbox('test', 2, array(1, 2, 3), array('class' => 'test'));
		$html = '<input type="checkbox" value="2" name="test" id="test" checked="checked" class="test" />';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderRadio
	 *
	 * @test
	 */
	public function renderRadio() {
		$htmlGenerated = tx_t3devapi_html::renderRadio('test', 2, array(1, 2, 3), array('class' => 'test'));
		$html = '<input type="radio" value="2" name="test" id="test" checked="checked" class="test" />';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderSelectWithoutSelected
	 *
	 * @test
	 */
	public function renderSelectWithoutSelected() {
		$htmlGenerated = tx_t3devapi_html::renderSelect('test', array(1 => 'test 1', 2 => 'test 2', 3 => 'test 3'), NULL, array('class' => 'test'));
		$html = '<select name="test" id="test" class="test"><option value="1" selected="selected">test 1</option><option value="2">test 2</option><option value="3">test 3</option></select>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderSelectWithSelected
	 *
	 * @test
	 */
	public function renderSelectWithSelected() {
		$htmlGenerated = tx_t3devapi_html::renderSelect('test', array(1 => 'test 1', 2 => 'test 2', 3 => 'test 3'), 3, array('class' => 'test'));
		$html = '<select name="test" id="test" class="test"><option value="1">test 1</option><option value="2">test 2</option><option value="3" selected="selected">test 3</option></select>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderMultipleSelect
	 *
	 * @test
	 */
	public function renderMultipleSelect() {
		$htmlGenerated = tx_t3devapi_html::renderMultipleSelect('test', array(1 => 'test 1', 2 => 'test 2', 3 => 'test 3'), array(1, 3), array('class' => 'test'));
		$html = '<select multiple="multiple" name="test" id="test" class="test"><option value="1" selected="selected">test 1</option><option value="2">test 2</option><option value="3" selected="selected">test 3</option></select>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderMultipleSelectWithGroup
	 *
	 * @test
	 */
	public function renderMultipleSelectWithGroup() {
		$group1 = array(1 => 'test 1', 2 => 'test 2', 3 => 'test 3');
		$group2 = array(4 => 'test 4', 5 => 'test 5', 6 => 'test 6');
		$htmlGenerated = tx_t3devapi_html::renderMultipleSelectWithGroup('test', array('group1' => $group1, 'group2' => $group2), array(1, 3), array('class' => 'test'));
		$html = '<select multiple="multiple" name="test" id="test" class="test"><optgroup label="group1"><option value="1" selected="selected">test 1</option><option value="2">test 2</option><option value="3" selected="selected">test 3</option></optgroup><optgroup label="group2"><option value="4">test 4</option><option value="5">test 5</option><option value="6">test 6</option></optgroup></select>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderOptionTagSelected
	 *
	 * @test
	 */
	public function renderOptionTagSelected() {
		$htmlGenerated = tx_t3devapi_html::renderOptionTag('test', 'test', TRUE);
		$html = '<option value="test" selected="selected">test</option>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderOptionTagNotSelected
	 *
	 * @test
	 */
	public function renderOptionTagNotSelected() {
		$htmlGenerated = tx_t3devapi_html::renderOptionTag('test', 'test', FALSE);
		$html = '<option value="test">test</option>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * renderOptionGroup
	 *
	 * @test
	 */
	public function renderOptionGroup() {
		$htmlGenerated = tx_t3devapi_html::renderOptionGroup('test', 'test');
		$html = '<optgroup label="test">test</optgroup>';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	/**
	 * cleanId
	 *
	 * @test
	 */
	public function cleanId() {
		$htmlGenerated = tx_t3devapi_html::cleanId('tx_t3devapi_pi1[var]');
		$html = 'tx_t3devapi_pi1_var';
		$this->debug($htmlGenerated);
		$this->assertEquals(trim($htmlGenerated), $html);
	}

	public function debug($message) {
		echo '<em>' . htmlentities($message) . '</em>';
	}

}

?>