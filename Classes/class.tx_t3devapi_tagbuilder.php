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
 * tx_t3devapi_tagbuilder
 * Class to create some tag element with some php objects
 * Inspirate by the a class in the Fluid project
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class tx_t3devapi_tagbuilder
{

	/**
	 * Name of the Tag to be rendered
	 *
	 * @var string
	 */
	protected $tagName = '';

	/**
	 * Content of the tag to be rendered
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Attributes of the tag to be rendered
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Specifies whether this tag needs a closing tag.
	 * E.g. <textarea> cant be self-closing even if its empty
	 *
	 * @var boolean
	 */
	protected $forceClosingTag = FALSE;

	/**
	 * Constructor
	 *
	 * @param string $tagName    name of the tag to be rendered
	 * @param string $tagContent content of the tag to be rendered
	 */
	public function __construct($tagName = '', $tagContent = '') {
		$this->setTagName($tagName);
		$this->setContent($tagContent);
	}

	/**
	 * Sets the tag name
	 *
	 * @param string $tagName name of the tag to be rendered
	 * @return void
	 */
	public function setTagName($tagName) {
		$this->tagName = $tagName;
	}

	/**
	 * Gets the tag name
	 *
	 * @return string tag name of the tag to be rendered
	 */
	public function getTagName() {
		return $this->tagName;
	}

	/**
	 * Sets the content of the tag
	 *
	 * @param string $tagContent content of the tag to be rendered
	 * @return void
	 */
	public function setContent($tagContent) {
		$this->content = $tagContent;
	}

	/**
	 * Gets the content of the tag
	 *
	 * @return string content of the tag to be rendered
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Returns TRUE if tag contains content, otherwise FALSE
	 *
	 * @return boolean TRUE if tag contains text, otherwise FALSE
	 */
	public function hasContent() {
		if ($this->content === NULL) {
			return FALSE;
		}
		return $this->content !== '';
	}

	/**
	 * Set this to TRUE to force a closing tag
	 * E.g. <textarea> cant be self-closing even if its empty
	 *
	 * @param boolean $forceClosingTag
	 * @return void
	 */
	public function forceClosingTag($forceClosingTag) {
		$this->forceClosingTag = $forceClosingTag;
	}

	/**
	 * Adds an attribute to the $attributes-collection
	 *
	 * @param string  $attributeName           name of the attribute to be added to the tag
	 * @param string  $attributeValue          attribute value
	 * @param boolean $escapeSpecialCharacters apply htmlspecialchars to attribute value
	 * @return void
	 */
	public function addAttribute($attributeName, $attributeValue, $escapeSpecialCharacters = TRUE) {
		if ($escapeSpecialCharacters) {
			$attributeValue = htmlspecialchars($attributeValue);
		}
		$this->attributes[$attributeName] = $attributeValue;
	}

	/**
	 * Adds attributes to the $attributes-collection
	 *
	 * @param array   $attributes              collection of attributes to add. key = attribute name, value = attribute value
	 * @param boolean $escapeSpecialCharacters apply htmlspecialchars to attribute values#
	 * @return void
	 */
	public function addAttributes(array $attributes, $escapeSpecialCharacters = TRUE) {
		foreach ($attributes as $attributeName => $attributeValue) {
			$this->addAttribute($attributeName, $attributeValue, $escapeSpecialCharacters);
		}
	}

	/**
	 * Removes an attribute from the $attributes-collection
	 *
	 * @param string $attributeName name of the attribute to be removed from the tag
	 * @return void
	 */
	public function removeAttribute($attributeName) {
		unset($this->attributes[$attributeName]);
	}

	/**
	 * Resets the TagBuilder by setting all members to their default value
	 *
	 * @return void
	 */
	public function reset() {
		$this->tagName         = '';
		$this->content         = '';
		$this->attributes      = array();
		$this->forceClosingTag = FALSE;
	}

	/**
	 * Renders and returns the tag
	 *
	 * @return string
	 */
	public function render() {
		if (empty($this->tagName)) {
			return '';
		}
		$output = '<' . $this->tagName;
		foreach ($this->attributes as $attributeName => $attributeValue) {
			$output .= ' ' . $attributeName . '="' . $attributeValue . '"';
		}
		if ($this->hasContent() || $this->forceClosingTag) {
			$output .= '>' . $this->content . '</' . $this->tagName . '>';
		} else {
			$output .= ' />';
		}
		return $output;
	}

}

?>