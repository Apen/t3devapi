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
 * tx_t3devapi_validate
 * Class to validate some value
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class tx_t3devapi_validate
{
	/**
	 * @var array
	 */
	public $errors = array();

	/**
	 * Constructor of the class
	 */
	public function __construct() {
	}

	/**
	 * Set an error
	 *
	 * @param string $error
	 * @return void
	 */
	public function setError($error) {
		$this->errors[] = $error;
	}

	/**
	 * Get the errors
	 *
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Check if the value is required
	 *
	 * @param string $value
	 * @param string $error
	 * @return bool
	 */
	public function isRequired($value, $error) {
		if (!empty($value) || $value === 0 || $value === '0') {
			return TRUE;
		}
		$this->setError($error);
		return FALSE;
	}

	/**
	 * Check if the value is an IP
	 *
	 * @param string $value
	 * @param string $error
	 * @return bool
	 */
	public function isIP($value, $error) {
		if (!preg_match('/\b(([01]?\d?\d|2[0-4]\d|25[0-5])\.){3}([01]?\d?\d|2[0-4]\d|25[0-5])\b/', $value)) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the value is an email
	 *
	 * @param string $value
	 * @param string $error
	 * @return bool
	 */
	public function isEmail($value, $error) {
		if (!preg_match(
			'/^(?:(?#local-part)(?#quoted)"[^\"]*"|(?#non-quoted)[a-z0-9&+_-](?:\.?[a-z0-9&+_-]+)*)@(?:(?#domain)(?#domain-name)[a-z0-9](?:[a-z0-9-]*[a-z0-9])*(?:\.[a-z0-9](?:[a-z0-9-]*[a-z0-9])*)*|(?#ip)(\[)?(?:[01]?\d?\d|2[0-4]\d|25[0-5])(?:\.(?:[01]?\d?\d|2[0-4]\d|25[0-5])){3}(?(1)\]|))$/i',
			$value
		)
		) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the value is alphabetic
	 *
	 * @param string $value
	 * @param string $error
	 * @return bool
	 */
	public function isAlphabetic($value, $error) {
		$whiteSpace = '\s';
		$pattern    = '/[^a-zA-Z' . $whiteSpace . ']/u';
		if (preg_replace($pattern, '', (string)$value) !== $value) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the value is alphanumeric
	 *
	 * @param string $value
	 * @param string $error
	 * @return bool
	 */
	public function isAlphanumeric($value, $error) {
		$whiteSpace = '\s';
		$pattern    = '/[^a-zA-Z0-9' . $whiteSpace . ']/u';
		if (preg_replace($pattern, '', (string)$value) !== $value) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the value is an integer
	 *
	 * @param string $value
	 * @param string $error
	 * @return bool
	 */
	public function isInteger($value, $error) {
		$locale        = localeconv();
		$valueFiltered = str_replace($locale['thousands_sep'], '', $value);
		$valueFiltered = str_replace($locale['mon_thousands_sep'], '', $value);
		$valueFiltered = str_replace($locale['decimal_point'], '.', $valueFiltered);
		$valueFiltered = str_replace($locale['mon_decimal_point'], '.', $valueFiltered);
		if (strval(intval($valueFiltered)) != $valueFiltered) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the value is a float
	 *
	 * @param string $value
	 * @param string $error
	 * @return bool
	 */
	public function isFloat($value, $error) {
		$locale        = localeconv();
		$valueFiltered = str_replace($locale['thousands_sep'], '', $value);
		$valueFiltered = str_replace($locale['mon_thousands_sep'], '', $value);
		$valueFiltered = str_replace($locale['decimal_point'], '.', $valueFiltered);
		$valueFiltered = str_replace($locale['mon_decimal_point'], '.', $valueFiltered);
		if ($valueFiltered != strval(floatval($valueFiltered))) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the value is in the array
	 *
	 * @param string $value
	 * @param array  $array
	 * @param string $error
	 * @return bool
	 */
	public function isInArray($value, $array, $error) {
		if (!in_array($value, $array)) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the value is a date correctly formated
	 *
	 * @param string $value
	 * @param string $format (see http://fr.php.net/manual/fr/function.strftime.php)
	 * @param string $error
	 * @return bool
	 */
	public function isDate($value, $format, $error) {
		$parsedDate      = strptime($value, $format);
		$parsedDateYear  = $parsedDate['tm_year'] + 1900;
		$parsedDateMonth = $parsedDate['tm_mon'] + 1;
		$parsedDateDay   = $parsedDate['tm_mday'];
		if (!checkdate($parsedDateMonth, $parsedDateDay, $parsedDateYear)) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if teh value is equal
	 *
	 * @param string $value
	 * @param string $param
	 * @param string $error
	 * @return bool
	 */
	public function isEquals($value, $param, $error) {
		if ($value !== $param) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the value if less than $param
	 *
	 * @param int    $value
	 * @param int    $param
	 * @param string $error
	 * @return bool
	 */
	public function isLessthan($value, $param, $error) {
		if ($value >= $param || !is_numeric($value)) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the value if greater than $param
	 *
	 * @param int    $value
	 * @param int    $param
	 * @param string $error
	 * @return bool
	 */
	public function isGreaterthan($value, $param, $error) {
		if ($value <= $param || !is_numeric($value)) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the length of the value is min
	 *
	 * @param int    $value
	 * @param int    $param
	 * @param string $error
	 * @return bool
	 */
	public function isLengthMin($value, $param, $error) {
		$length = iconv_strlen($value);
		if ($length < $param) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the length of the value is max
	 *
	 * @param int    $value
	 * @param int    $param
	 * @param string $error
	 * @return bool
	 */
	public function isLengthMax($value, $param, $error) {
		$length = iconv_strlen($value);
		if ($length > $param) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if the value respect the regexp
	 *
	 * @param string $value
	 * @param string $param
	 * @param string $error
	 * @return bool
	 */
	public function isRegexp($value, $param, $error) {
		if (!preg_match($param, $value)) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Check if teh value is an URI
	 *
	 * @param string $value
	 * @param string $error
	 * @return bool
	 */

	public function isUri($value, $error) {
		if (!preg_match(
			'/^(?#Protocol)(?:(?:ht|f)tp(?:s?)\:\/\/|~\/|\/)?(?#Username:Password)(?:\w+:\w+@)?(?#Subdomains)(?:(?:[-\w]+\.)+(?#TopLevel Domains)(?:com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum|travel|[a-z]{2}))(?#Port)(?::[\d]{1,5})?(?#Directories)(?:(?:(?:\/(?:[-\w~!$+|.,=]|%[a-f\d]{2})+)+|\/)+|\?|#)?(?#Query)(?:(?:\?(?:[-\w~!$+|.,*:]|%[a-f\d{2}])+=?(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)(?:&(?:[-\w~!$+|.,*:]|%[a-f\d{2}])+=?(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)*)*(?#Anchor)(?:#(?:[-\w~!$+|.,*:=]|%[a-f\d]{2})*)?$/',
			$value
		)
		) {
			$this->setError($error);
			return FALSE;
		}
		return TRUE;
	}

}

tx_t3devapi_miscellaneous::XCLASS('ext/t3devapi/class.tx_t3devapi_validate.php');


?>