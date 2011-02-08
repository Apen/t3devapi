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
 * tx_t3devapi_miscellaneous
 * Class with a lot of functions :-)
 *
 * @author Yohann
 * @copyright Copyright (c) 2011
 */

class tx_t3devapi_miscellaneous
{
	// Parent object
	protected $cObj = null;

	/**
	 * Class constructor
	 */

	function __construct($pObj)
	{
		$this->cObj = $pObj->cObj;
	}

	/**
	 * This function return the array of template config
	 *
	 * @return
	 */

	function getTmplSetup()
	{
		return $GLOBALS['TSFE']->tmpl->setup['plugin.'];
	}

	/**
	 * This function return the array of TYPO3_CONF_VARS
	 *
	 * @return
	 */

	function getT3ConfVars()
	{
		return $GLOBALS['TYPO3_CONF_VARS'];
	}

	/**
	 * This function return the complete infos about the DB
	 *
	 * @return
	 */

	function getDbInfo()
	{
		$dbinfo = array();
		$dbinfo['TYPO3_db'] = TYPO3_db;
		$dbinfo['TYPO3_db_username'] = TYPO3_db_username;
		$dbinfo['TYPO3_db_password'] = TYPO3_db_password;
		$dbinfo['TYPO3_db_host'] = TYPO3_db_host;
		return $dbinfo;
	}

	/**
	 * This function return the complete url on the current pages with params
	 *
	 * @param array $additionalParamsArray
	 * @param integer $cache
	 * @param integer $altPageId
	 * @return
	 */

	function getURL($additionalParamsArray = array(), $cache = 0, $altPageId = 0)
	{
		$conf = array();
		$conf['useCacheHash'] = $cache;
		$conf['no_cache'] = 0;
		$conf['returnLast'] = 'url';
		$conf['parameter'] = $altPageId ? $altPageId : $GLOBALS['TSFE']->id;
		$conf['additionalParams'] = t3lib_div::implodeArrayForUrl('', $additionalParamsArray, '', 1);
		return $this->cObj->typolink('', $conf);
	}

	/**
	 * This function return the complete <a href="xx"> on the current pages with params
	 *
	 * @param array $additionalParamsArray
	 * @param integer $cache
	 * @param integer $altPageId
	 * @param mixed $label
	 * @return
	 */

	function getTypolink($additionalParamsArray = array(), $cache = 0, $altPageId = 0, $label)
	{
		$conf = array();
		$conf['useCacheHash'] = $cache;
		$conf['no_cache'] = 0;
		$conf['parameter'] = $altPageId ? $altPageId : $GLOBALS['TSFE']->id;
		$conf['additionalParams'] = t3lib_div::implodeArrayForUrl('', $additionalParamsArray, '', 1);
		return $this->cObj->typolink($label, $conf);
	}

	/**
	 * Change the page title if you corectly use the cHash
	 *
	 * @param mixed $new_title
	 */

	function changePageTitle($new_title)
	{
		// Caution : you do not must be in user_int because it doesn't work ;-)
		$GLOBALS['TSFE']->page['title'] = $new_title;
		// set pagetitle for indexed search to news title
		$GLOBALS['TSFE']->indexedDocTitle = $new_title;
	}

	/**
	 * Resize an image with image magick
	 * Prefer the cImage method to have access to all the parameters of cImage
	 *
	 * @param mixed $image
	 * @param mixed $title
	 * @param mixed $alt
	 * @param mixed $maxW
	 * @param mixed $maxH
	 * @param mixed $crop
	 * @return the image (HTML)
	 */

	function resizeImg($image, $title, $alt, $maxW, $maxH, $crop = false)
	{
		$img['file'] = $image;
		$lConf['file.']['maxH'] = $maxH;
		$lConf['file.']['maxW'] = $maxW;
		$lConf['altText'] = $alt;
		$lConf['titleText'] = $title;

		$lConf['emptyTitleHandling'] = 'removeAttr';
		// force crop
		if ($crop == true) {
			$lConf['file.']['height'] = $maxH . 'c';
			$lConf['file.']['width'] = $maxW . 'c';
		}

		return $this->cObj->cImage($img["file"], $lConf);
	}

	/**
	 * Resize or crop an image with the cImage object
	 *
	 * @param mixed $image
	 * @param mixed $title
	 * @param mixed $alt
	 * @param mixed $width
	 * @param mixed $height
	 * @param string $londDesc
	 * @return the image (HTML)
	 */

	function cImage($image, $title, $alt, $width, $height, $londDesc = '')
	{
		$img['file'] = $image;
		$lConf['file.']['height'] = $height;
		$lConf['file.']['width'] = $width;
		$lConf['altText'] = $alt;
		$lConf['titleText'] = $title;
		if ($londDesc != '') {
			$lConf['longdescURL'] = $londDesc;
		}
		$lConf['emptyTitleHandling'] = 'removeAttr';
		return $this->cObj->cImage($img["file"], $lConf);
	}

	/**
	 * Exec the image magick binary
	 * Example : $this->imageMagickExec($path.$filename,$path.$filenameList,"+profile '*' -geometry '100>x100>' -colorspace RGB -quality 70");
	 *
	 * @param mixed $input
	 * @param mixed $output
	 * @param mixed $params
	 * @return
	 */

	function imageMagickExec($input, $output, $params)
	{
		$cmd = t3lib_div::imageMagickCommand('convert', $params . ' ' . escapeshellarg($input) . ' ' . escapeshellarg($output));
		$ret = exec($cmd);
		t3lib_div::fixPermissions($output); // Change the permissions of the file
		return $ret;
	}

	/**
	 * This function format a RTE content
	 *
	 * @param mixed $value
	 * @return the formated content
	 */

	function formatRTE($value)
	{
		return $this->cObj->parseFunc($value, array(), '< lib.parseFunc_RTE');
	}

	/**
	 * This function format a FILE link
	 *
	 * @param mixed $value
	 * @return
	 */

	function renderLinkType($value)
	{
		return $this->cObj->getTypoLink_URL($value);
	}

	/**
	 * This function return a mailto
	 *
	 * @param mixed $email
	 * @param array $conf
	 * @return
	 */

	function getMailto($email, $conf = array())
	{
		return $this->cObj->mailto_makelinks('mailto:' . $email, $conf);
	}

	/**
	 * This function return the base url
	 *
	 * @return the base URL
	 */

	function getBaseURL()
	{
		return $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'];
	}

	/**
	 * Set a variable in the register (accessible in the setup TS code)
	 *
	 * @param mixed $varname
	 * @param mixed $varcontent
	 */

	function setRegister($varname, $varcontent)
	{
		$GLOBALS['TSFE']->register[$varname] = $varcontent;
	}

	/**
	 * Get a variable in the register (accessible in the setup TS code)
	 *
	 * @param mixed $varname
	 * @param mixed $varcontent
	 * @return
	 */

	function getRegister($varname, $varcontent)
	{
		return $GLOBALS['TSFE']->register[$varname];
	}

	/**
	 * Set a variable in typo3 fe_users session
	 *
	 * @param mixed $varname
	 * @param mixed $varcontent
	 * @return
	 */

	function setSession($varname, $varcontent)
	{
		$GLOBALS['TSFE']->fe_user->setKey('ses', $varname, $varcontent);
		$GLOBALS['TSFE']->storeSessionData(); // validate the session
	}

	/**
	 * Get a variable in typo3 session (without params return all the session table)
	 *
	 * @param string $varname
	 * @return
	 */

	function getSession($varname = "")
	{
		if ($varname != "") {
			return $GLOBALS['TSFE']->fe_user->getKey('ses', $varname);
		} else {
			return $GLOBALS['TSFE']->fe_user->sesData;
		}
	}

	/**
	 * Get the tca of a table
	 *
	 * @param mixed $table
	 * @return
	 */

	function getTableTCA($table)
	{
		global $TCA;
		$GLOBALS['TSFE']->includeTCA();
		t3lib_div::loadTCA($table);
		return $TCA[$table];
	}

	/**
	 * Get the rootline of the current page
	 *
	 * @return
	 */

	function getRootline()
	{
		$GLOBALS['TSFE']->getPageAndRootline();
		return $GLOBALS['TSFE']->rootLine;
	}

	/**
	 * This function return an array with ###value###
	 *
	 * @param mixed $array
	 * @param string $marker_prefix
	 * @return
	 */

	function convertToMarkerArray($array, $marker_prefix = "")
	{
		$temp = array();
		foreach ($array as $key => $val) {
			$temp["###" . strtoupper($marker_prefix . $key) . "###"] = $val;
		}
		return $temp;
	}

	/**
	 * This function return a string with ###value###
	 *
	 * @param mixed $value
	 * @param string $marker_prefix
	 * @return
	 */

	function convertToMarker($value, $marker_prefix = '')
	{
		return '###' . strtoupper($marker_prefix . $value) . '###';
	}

	/**
	 * This function return the piVars Array with exlude value like var1,var2
	 *
	 * @param mixed $exclude
	 * @return
	 */

	function getPiVars($exclude)
	{
		foreach ($this->pObj->piVars as $piVar => $piVarvalue) {
			if (t3lib_div::inList($exclude, $piVar)) {
				unset($this->pObj->piVars[$piVar]);
			}
		}
		return $this->pObj->piVars;
	}

	/**
	 * This function get a tt_content records (for example a plugin)
	 *
	 * @param mixed $uid
	 * @return HTML
	 */

	function getGeneratedContent($uid)
	{
		$objContent = array('tables' => 'tt_content', 'source' => 'tt_content_' . $uid);
		return $this->cObj->RECORDS($objContent);
	}

	/**
	 * Loads the TypoScript for the given extension prefix, e.g. tx_cspuppyfunctions_pi1, for use in a backend module.
	 *
	 * @param string $extKey
	 * @return array
	 */

	function loadTypoScriptForBEModule($pid, $extKey)
	{
		require_once(PATH_t3lib . 'class.t3lib_page.php');
		require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
		require_once(PATH_t3lib . 'class.t3lib_tsparser_ext.php');
		$sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sysPageObj->getRootLine($pid);
		$TSObj = t3lib_div::makeInstance('t3lib_tsparser_ext');
		$TSObj->tt_track = 0;
		$TSObj->init();
		$TSObj->runThroughTemplates($rootLine);
		$TSObj->generateConfig();
		return $TSObj->setup['plugin.'][$extKey . '.'];
	}

	/**
	 * This function return an array of a csv file
	 *
	 * @param mixed $openFile
	 * @param mixed $columnsOnly
	 * @param string $delimiters
	 * @return
	 */

	function csv2array($openFile, $columnsOnly = false, $delimiters = ";")
	{
		$handle = fopen($openFile, "r");
		$rows = 0;
		while (!feof($handle)) {
			$columns[] = explode($delimiters, fgets($handle, 4096));
			if ($rows++ == 0 && $columnsOnly) {
				break;
			}
		}
		fclose($handle);
		return $columns;
	}

	/**
	 * This function return csv string of an array
	 *
	 * @param mixed $buffer
	 * @param mixed $file
	 * @param string $delimiters
	 * @param mixed $stringonly
	 * @return
	 */

	function array2csv($buffer, $file, $delimiters = ";", $stringonly = false)
	{
		$csv = "";
		$i = 0;
		foreach ($buffer as $val) {
			foreach ($val as $key => $value) {
				$csv .= utf8_encode($value) . $delimiters;
			}
			$i++;
			if (count($buffer) > $i) {
				$csv .= "\r\n";
			}
		}

		if ($stringonly == true) {
			return $csv;
		}

		$fp = fopen($file, 'w+');
		fwrite($fp, $csv);
		fclose($fp);
	}

	/**
	 * Truncates text.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with the ending if the text is longer than length.
	 *
	 * @param string $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param mixed $ending If string, will be used as Ending and appended to the trimmed string. Can also be an associative array that can contain the last three params of this method.
	 * @param boolean $exact If false, $text will not be cut mid-word
	 * @param boolean $considerHtml If true, HTML tags would be handled correctly
	 * @return string Trimmed string.
	 */

	function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false)
	{
		if (is_array($ending)) {
			extract($ending);
		}
		if ($considerHtml) {
			if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			$totalLength = mb_strlen($ending);
			$openTags = array();
			$truncate = '';
			preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
			foreach ($tags as $tag) {
				if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
					if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
						array_unshift($openTags, $tag[2]);
					} else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
						$pos = array_search($closeTag[1], $openTags);
						if ($pos !== false) {
							array_splice($openTags, $pos, 1);
						}
					}
				}
				$truncate .= $tag[1];

				$contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
				if ($contentLength + $totalLength > $length) {
					$left = $length - $totalLength;
					$entitiesLength = 0;
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
						foreach ($entities[0] as $entity) {
							if ($entity[1] + 1 - $entitiesLength <= $left) {
								$left--;
								$entitiesLength += mb_strlen($entity[0]);
							} else {
								break;
							}
						}
					}

					$truncate .= mb_substr($tag[3], 0, $left + $entitiesLength);
					break;
				} else {
					$truncate .= $tag[3];
					$totalLength += $contentLength;
				}
				if ($totalLength >= $length) {
					break;
				}
			}
		} else {
			if (mb_strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = mb_substr($text, 0, $length - strlen($ending));
			}
		}
		if (!$exact) {
			$spacepos = mb_strrpos($truncate, ' ');
			if (isset($spacepos)) {
				if ($considerHtml) {
					$bits = mb_substr($truncate, $spacepos);
					preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
					if (!empty($droppedTags)) {
						foreach ($droppedTags as $closingTag) {
							if (!in_array($closingTag[1], $openTags)) {
								array_unshift($openTags, $closingTag[1]);
							}
						}
					}
				}
				$truncate = mb_substr($truncate, 0, $spacepos);
			}
		}

		$truncate .= $ending;

		if ($considerHtml) {
			foreach ($openTags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}

	/**
	 * Enable the SQL debug
	 */

	function debugQueryInit()
	{
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
		$GLOBALS['TYPO3_DB']->debugOutput = true;
	}

	/**
	 * Display the last query if you have activated the debugQueryInit()
	 */

	function debugQuery()
	{
		debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, 'SQL');
	}

	/**
	 * Send a HTML mail using t3lib_htmlmail
	 *
	 * @param mixed $content
	 * @param mixed $title
	 * @param mixed $recipient
	 * @param mixed $fromEmail
	 * @param mixed $fromName
	 * @param string $replyTo
	 * @return
	 */

	function sendHTMLMail($content, $title, $recipient, $fromEmail, $fromName, $replyTo = '')
	{
		if (trim($recipient) && trim($content)) {
			$subject = $title;

			$Typo3_htmlmail = t3lib_div::makeInstance('t3lib_htmlmail');
			$Typo3_htmlmail->start();
			$Typo3_htmlmail->useBase64();

			$Typo3_htmlmail->subject = $subject;
			$Typo3_htmlmail->from_email = $fromEmail;
			$Typo3_htmlmail->from_name = $fromName;
			$Typo3_htmlmail->replyto_email = $replyTo ? $replyTo : $fromEmail;
			$Typo3_htmlmail->replyto_name = $replyTo ? '' : $fromName;
			$Typo3_htmlmail->organisation = '';
			$Typo3_htmlmail->priority = 3;
			// HTML
			$Typo3_htmlmail->theParts['html']['content'] = $content; // Fetches the content of the page
			$Typo3_htmlmail->theParts['html']['path'] = '';
			$Typo3_htmlmail->extractMediaLinks();
			$Typo3_htmlmail->extractHyperLinks();
			$Typo3_htmlmail->fetchHTMLMedia();
			$Typo3_htmlmail->substMediaNamesInHTML(0); // 0 = relative
			$Typo3_htmlmail->substHREFsInHTML();
			$Typo3_htmlmail->setHTML($Typo3_htmlmail->encodeMsg($Typo3_htmlmail->theParts['html']['content']));
			// PLAIN
			$Typo3_htmlmail->addPlain('');
			// SET Headers and Content
			$Typo3_htmlmail->setHeaders();
			$Typo3_htmlmail->setContent();
			$Typo3_htmlmail->setRecipient($recipient);

			return $Typo3_htmlmail->sendtheMail();
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class.tx_t3devapi_miscellaneous.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class.tx_t3devapi_miscellaneous.php']);
}

?>