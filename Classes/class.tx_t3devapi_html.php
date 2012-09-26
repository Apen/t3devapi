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
 * tx_t3devapi_html
 * Class to create some html element
 * based on tx_t3devapi_tagbuilder
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class tx_t3devapi_html
{

	/**
	 * Render a label
	 *
	 * @param string $for
	 * @param string $content
	 * @return string
	 */
	public static function renderLabel($for, $content) {
		$tag = new tx_t3devapi_tagbuilder();
		$tag->setTagName('label');
		$tag->addAttribute('for', self::cleanId($for));
		$tag->setContent($content);
		return $tag->render();
	}

	/**
	 * Render a generic input (used by all the input type)
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $value
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderInput($type, $name, $value = '', $attributes = array()) {
		$tag = new tx_t3devapi_tagbuilder();
		$tag->setTagName('input');
		$tag->addAttribute('type', $type);
		if (!isset($attributes['name'])) {
			$tag->addAttribute('name', $name);
		}
		if (!isset($attributes['id'])) {
			$tag->addAttribute('id', self::cleanId($name));
		}
		$tag->addAttribute('value', $value);
		$tag->addAttributes($attributes);
		return $tag->render();
	}

	/**
	 * Render a input type text
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderText($name, $value = '', $attributes = array()) {
		return self::renderInput('text', $name, $value, $attributes);
	}

	/**
	 * Render a input type hidden
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderHidden($name, $value = '', $attributes = array()) {
		return self::renderInput('hidden', $name, $value, $attributes);
	}

	/**
	 * Render a input type button
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderButton($name, $value = '', $attributes = array()) {
		return self::renderInput('button', $name, $value, $attributes);
	}

	/**
	 * Render a input type password
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderPassword($name, $value = '', $attributes = array()) {
		return self::renderInput('password', $name, $value, $attributes);
	}

	/**
	 * Render a input type reset
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderReset($name, $value = '', $attributes = array()) {
		return self::renderInput('reset', $name, $value, $attributes);
	}

	/**
	 * Render a input type submit
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderSubmit($name, $value = '', $attributes = array()) {
		return self::renderInput('submit', $name, $value, $attributes);
	}

	/**
	 * Render a input type file
	 *
	 * @param string $name
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderInputFile($name, $attributes = array()) {
		$tag = new tx_t3devapi_tagbuilder();
		$tag->setTagName('input');
		$tag->addAttribute('type', 'file');
		if (!isset($attributes['name'])) {
			$tag->addAttribute('name', $name);
		}
		if (!isset($attributes['id'])) {
			$tag->addAttribute('id', self::cleanId($name));
		}
		$tag->addAttributes($attributes);
		return $tag->render();
	}

	/**
	 * Render a textarea
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderTextArea($name, $value = '', $attributes = array()) {
		$tag = new tx_t3devapi_tagbuilder();
		$tag->setTagName('textarea');
		$tag->forceClosingTag(TRUE);
		if (!isset($attributes['name'])) {
			$tag->addAttribute('name', $name);
		}
		if (!isset($attributes['id'])) {
			$tag->addAttribute('id', self::cleanId($name));
		}
		$tag->setContent($value);
		$tag->addAttributes($attributes);
		return $tag->render();
	}

	/**
	 * Render a checbox
	 *
	 * @param string $name
	 * @param string $content
	 * @param array  $arrayOfValues
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderCheckbox($name, $content, $arrayOfValues = array(), $attributes = array()) {
		$tag = new tx_t3devapi_tagbuilder();
		$tag->setTagName('input');
		$tag->addAttribute('type', 'checkbox');
		$tag->addAttribute('value', $content);
		if (!isset($attributes['name'])) {
			$tag->addAttribute('name', $name);
		}
		if (!isset($attributes['id'])) {
			$tag->addAttribute('id', self::cleanId($name));
		}
		if (is_array($arrayOfValues)) {
			if (in_array($content, $arrayOfValues)) {
				$tag->addAttribute('checked', 'checked');
			}
		}
		$tag->addAttributes($attributes);
		return $tag->render();
	}

	/**
	 * Render a radio button
	 *
	 * @param string $name
	 * @param string $content
	 * @param array  $arrayOfValues
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderRadio($name, $content, $arrayOfValues = array(), $attributes = array()) {
		$tag = new tx_t3devapi_tagbuilder();
		$tag->setTagName('input');
		$tag->addAttribute('type', 'radio');
		$tag->addAttribute('value', $content);
		if (!isset($attributes['name'])) {
			$tag->addAttribute('name', $name);
		}
		if (!isset($attributes['id'])) {
			$tag->addAttribute('id', self::cleanId($name));
		}
		if (in_array($content, $arrayOfValues)) {
			$tag->addAttribute('checked', 'checked');
		}
		$tag->addAttributes($attributes);
		return $tag->render();
	}

	/**
	 * Render a select
	 *
	 * @param string $name
	 * @param array  $content
	 * @param string $value
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderSelect($name, $content = array(), $value = '', $attributes = array()) {
		$tag = new tx_t3devapi_tagbuilder();
		$tag->forceClosingTag(TRUE);
		$tag->setTagName('select');
		if (!isset($attributes['name'])) {
			$tag->addAttribute('name', $name);
		}
		if (!isset($attributes['id'])) {
			$tag->addAttribute('id', self::cleanId($name));
		}
		$options = '';
		if (array_key_exists($value, $content) === FALSE) {
			$keys = array_keys($content);
			$value = $keys[0];
		}
		foreach ($content as $key => $entry) {
			if ($value == $key) {
				$options .= self::renderOptionTag($key, $entry, TRUE);
			} else {
				$options .= self::renderOptionTag($key, $entry, FALSE);
			}

		}
		$tag->setContent($options);
		$tag->addAttributes($attributes);
		return $tag->render();
	}

	/**
	 * Render a multiple select
	 *
	 * @param string $name
	 * @param array  $content
	 * @param array  $arrayOfValues
	 * @param array  $attributes
	 * @return string
	 */
	public static function renderMultipleSelect($name, $content = array(), $arrayOfValues = array(), $attributes = array()) {
		$tag = new tx_t3devapi_tagbuilder();
		$tag->forceClosingTag(TRUE);
		$tag->setTagName('select');
		$tag->addAttribute('multiple', 'multiple');
		if (!isset($attributes['name'])) {
			$tag->addAttribute('name', $name);
		}
		if (!isset($attributes['id'])) {
			$tag->addAttribute('id', self::cleanId($name));
		}
		$options = '';
		foreach ($content as $key => $entry) {
			if (is_array($arrayOfValues)) {
				if (in_array($key, $arrayOfValues)) {
					$options .= self::renderOptionTag($key, $entry, TRUE);
				} else {
					$options .= self::renderOptionTag($key, $entry, FALSE);
				}
			} else {
				$options .= self::renderOptionTag($key, $entry, FALSE);
			}
		}
		$tag->setContent($options);
		$tag->addAttributes($attributes);
		return $tag->render();
	}

	/**
	 * Render one option tag
	 *
	 * @param string  $value      value attribute of the option tag (will be escaped)
	 * @param string  $label      content of the option tag (will be escaped)
	 * @param boolean $isSelected specifies wheter or not to add selected attribute
	 * @return string the rendered option tag
	 */
	public static function renderOptionTag($value, $label, $isSelected) {
		$output = '<option value="' . htmlspecialchars($value) . '"';
		if ($isSelected) {
			$output .= ' selected="selected"';
		}
		$output .= '>' . htmlspecialchars($label) . '</option>';

		return $output;
	}

	/**
	 * Render an option group
	 *
	 * @param $label
	 * @param $value
	 * @return string
	 */
	public static function renderOptionGroup($label, $value) {
		$output = '<optgroup label="' . htmlspecialchars($label) . '">';
		$output .= $value;
		$output .= '</optgroup>';
		return $output;
	}

	/**
	 * Clean an ID string (ex: without [])
	 *
	 * @param string $text
	 * @return mixed
	 */
	public static function cleanId($text) {
		$text = preg_replace('/\[\]/', '', $text);
		$text = preg_replace('/\]/', '', $text);
		$text = preg_replace('/\[/', '_', $text);
		return $text;
	}

}

tx_t3devapi_miscellaneous::XCLASS('ext/t3devapi/class.tx_t3devapi_html.php');

?>