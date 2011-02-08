<?php

/* * *************************************************************
*
* Copyright notice
*
* (c) 2010 Yohann CERDAN <ycerdan@onext.fr>
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
 * tx_t3devapi_html
 * Class to create some html element with some php objects
 * Example :
 *
 * $my_image = new tx_t3devapi_html('img', array('src' => 'http://www.google.fr/intl/en_fr/images/logo.gif', 'border' => '0'));
 * $my_anchor = new tx_t3devapi_html('a', array('href' => 'http://google.fr', 'title' => 'Google'), array($my_image));
 * $my_div = new tx_t3devapi_html('div', '', array($my_anchor));
 * echo $my_div->output();
 *
 * @author Yohann
 * @copyright Copyright (c) 2011
 */

class tx_t3devapi_html
{
	/**
	 *
	 * @var tag type
	 * @access protected
	 */
	protected $_type;
	/**
	 *
	 * @var tag attributes
	 * @access protected
	 */
	protected $_attributes;
	/**
	 *
	 * @var tag text
	 * @access protected
	 */
	protected $_text = false;
	/**
	 *
	 * @var tag closers
	 * @access protected
	 */
	protected $_self_closers = array('input', 'img', 'hr', 'br', 'meta', 'link');

	/**
	 * This is the class constructor.
	 * It allows to set up the tag type, attributes, childs and text
	 *
	 * @param string $type Tag type
	 * @param array $attribute Tag type
	 * @param array $objects Tag type
	 * @param string $text Tag type
	 */

	public function __construct($type = '', $attribute = '', $objects = '', $text = '')
	{
		// Set the type
		$this->_type = strtolower($type);
		$this->_attributes = array();
		// Set attributes
		if (is_array($attribute))
			$this->setAttributes($attribute);
		// Inject HTML into parent
		if (is_array($objects))
			$this->inject($objects);
		// Set tag text
		if ($text)
			$this->setText($text);
	}

	/**
	 * Returns the value of an attribute
	 *
	 * @param string $attribute
	 */

	public function getAttributes($attribute)
	{
		return $this->_attributes[$attribute];
	}

	/**
	 * Set the value of an attribute
	 *
	 * @param array $attribute_arr
	 */

	public function setAttributes($attribute_arr)
	{
		$this->_attributes = array_merge($this->_attributes, $attribute_arr);
	}

	/**
	 * Set the text between opening and closing tag
	 * Tag must be text only
	 *
	 * @param string $attribute
	 * @param string $value
	 */

	public function setText($text)
	{
		if (is_string($text)) {
			$this->_text = $text;
		}
	}

	/**
	 * Remove an attribute
	 *
	 * @param string $attribute
	 */

	public function remove($attribute)
	{
		if (isset($this->_attributes[$attribute]))
			unset($this->_attributes[$attribute]);
	}

	/**
	 * Clear all attributes
	 */

	public function clear()
	{
		$this->_attributes = array();
	}

	/**
	 * Insert an array of child nodes into parent.
	 * Format code with an indent of 2 whitespace for childs nodes
	 *
	 * @param array $object_arr
	 */

	public function inject($object_arr)
	{
		foreach ($object_arr as $object) {
			if (get_class($object) == get_class($this)) {
				$this->_attributes['text'] .= $object->build();
			}
		}
	}

	/**
	 * Print the html
	 */

	public function output()
	{
		return $this->build();
	}

	/**
	 * Build the HTML node
	 */

	protected function build()
	{
		// start
		$build = '<' . $this->_type;
		// add attributes
		if (count($this->_attributes)) {
			foreach ($this->_attributes as $key => $value) {
				if ($key != 'text')
					$build .= ' ' . $key . '="' . $value . '"';
			}
		}
		// closing
		if (!in_array($this->_type, $this->_self_closers)) {
			// Parent node cannot have text
			if (is_string($this->_text)) {
				$build .= ">" . $this->_text . '</' . $this->_type . ">";
			} else {
				$build .= ">" . $this->_attributes['text'] . '</' . $this->_type . ">";
			}
		} else {
			$build .= " />";
		}
		// return it
		return $build;
	}

	public function renderSelect($name, $content = array(), $value = '', $attributes = array())
	{
		$my_options = array();
		$fill_selected = false;
		foreach ($content as $key => $entry) {
			$optionAttributes = array();
			$optionAttributes['value'] = $key;
			$select = '';
			// aucune valeur de selectionné indiqué
			if ($value == '' && ($fill_selected == false)) {
				// $select = 'selected="selected"';
				$optionAttributes['selected'] = 'selected';
				$fill_selected = true;
			}
			// une valeur est selectionné
			if (($value == $key) && ($fill_selected == false)) {
				// $select = 'selected="selected"';
				$optionAttributes['selected'] = 'selected';
				$fill_selected = true;
			}
			$my_options[] = new tx_t3devapi_html('option', $optionAttributes, '', $entry);
		}
		if (!isset($attributes['name'])) {
			$attributes['name'] = $name;
		}
		if (!isset($attributes['id'])) {
			$attributes['id'] = $name;
		}
		$my_select = new tx_t3devapi_html('select', $attributes, $my_options);
		return $my_select->output();
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class.tx_t3devapi_htmlelement.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class.tx_t3devapi_htmlelement.php']);
}

?>
