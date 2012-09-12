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
 * tx_t3devapi_miscellaneous
 * Class with a lot of functions :-)
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class tx_t3devapi_miscellaneous
{
	// Parent object
	protected $pObj = NULL;

	/**
	 * Class constructor
	 *
	 * @param $pObj
	 */
	public function __construct(&$pObj) {
		$this->pObj = $pObj;
	}

	/**
	 * This print a debug
	 *
	 * @param string $var
	 * @param string $header
	 * @return void
	 */
	public static function debug($var = '', $header = '') {
		t3lib_div::debug($var, $header);
	}

	/**
	 * This function return the array of template config
	 *
	 * @return void
	 */
	public function getTmplSetup() {
		return $GLOBALS['TSFE']->tmpl->setup['plugin.'];
	}

	/**
	 * This function return the array of TYPO3_CONF_VARS
	 *
	 * @return array
	 */
	public function getT3ConfVars() {
		return $GLOBALS['TYPO3_CONF_VARS'];
	}

	/**
	 * This function return the complete infos about the DB
	 *
	 * @return array
	 */
	public function getDbInfo() {
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
	 * @param array   $additionalParamsArray
	 * @param integer $cache
	 * @param integer $altPageId
	 * @return string
	 */
	public function getURL($additionalParamsArray = array(), $cache = 0, $altPageId = 0) {
		$conf = array();
		$conf['useCacheHash'] = $cache;
		if ($this->pObj->cObj->getUserObjectType() == tslib_cObj::OBJECTTYPE_USER_INT) {
			$conf['useCacheHash'] = 0;
		}
		$conf['no_cache'] = 0;
		$conf['returnLast'] = 'url';
		$conf['parameter'] = $altPageId ? $altPageId : $GLOBALS['TSFE']->id;
		$conf['additionalParams'] = t3lib_div::implodeArrayForUrl('', $additionalParamsArray, '', 1);
		return $this->pObj->cObj->typolink('', $conf);
	}

	/**
	 * This function return the complete <a href="xx"> on the current pages with params
	 *
	 * @param array   $additionalParamsArray
	 * @param integer $cache
	 * @param integer $altPageId
	 * @param mixed   $label
	 * @return string
	 */
	public function getTypolink($additionalParamsArray = array(), $cache = 0, $altPageId = 0, $label) {
		$conf = array();
		$conf['useCacheHash'] = $cache;
		if ($this->pObj->cObj->getUserObjectType() == tslib_cObj::OBJECTTYPE_USER_INT) {
			$conf['useCacheHash'] = 0;
		}
		$conf['no_cache'] = 0;
		$conf['parameter'] = $altPageId ? $altPageId : $GLOBALS['TSFE']->id;
		$conf['additionalParams'] = t3lib_div::implodeArrayForUrl('', $additionalParamsArray, '', 1);
		return $this->pObj->cObj->typolink($label, $conf);
	}

	/**
	 * Change the page title if you corectly use the cHash
	 *
	 * @param string $newTitle
	 * @return void
	 */
	public function changePageTitle($newTitle) {
		// Caution : you do not must be in user_int because it doesn't work ;-)
		$GLOBALS['TSFE']->page['title'] = $newTitle;
		// set pagetitle for indexed search to news title
		$GLOBALS['TSFE']->indexedDocTitle = $newTitle;
	}

	/**
	 * Resize an image with image magick
	 * Prefer the cImage method to have access to all the parameters of cImage
	 *
	 * @param string  $image
	 * @param string  $title
	 * @param string  $alt
	 * @param string  $maxW
	 * @param string  $maxH
	 * @param boolean $crop
	 * @return string the image (HTML)
	 */
	public function resizeImg($image, $title, $alt, $maxW, $maxH, $crop = FALSE) {
		$img['file'] = $image;
		$lConf['file.']['maxH'] = $maxH;
		$lConf['file.']['maxW'] = $maxW;
		$lConf['altText'] = $alt;
		$lConf['titleText'] = $title;

		$lConf['emptyTitleHandling'] = 'removeAttr';
		// force crop
		if ($crop == TRUE) {
			$lConf['file.']['height'] = $maxH . 'c';
			$lConf['file.']['width'] = $maxW . 'c';
		}

		return $this->pObj->cObj->cImage($img['file'], $lConf);
	}

	/**
	 * Resize or crop an image with the cImage object
	 *
	 * @param string  $image
	 * @param string  $title
	 * @param string  $alt
	 * @param string  $width
	 * @param string  $height
	 * @param string  $londDesc
	 * @return string the image (HTML)
	 */
	public function cImage($image, $title, $alt, $width, $height, $londDesc = '') {
		$img['file'] = $image;
		$lConf['file.']['height'] = $height;
		$lConf['file.']['width'] = $width;
		$lConf['altText'] = $alt;
		$lConf['titleText'] = $title;
		if ($londDesc != '') {
			$lConf['longdescURL'] = $londDesc;
		}
		$lConf['emptyTitleHandling'] = 'removeAttr';
		return $this->pObj->cObj->cImage($img['file'], $lConf);
	}

	/**
	 * Exec the image magick binary
	 * Ex: $this->imageMagickExec($path.$filename,$path.$filenameList,"+profile '*' -geometry '100>x100>' -colorspace RGB -quality 70");
	 *
	 * @param string $input
	 * @param string $output
	 * @param string $params
	 * @return string
	 */
	public function imageMagickExec($input, $output, $params) {
		$cmd = t3lib_div::imageMagickCommand('convert', $params . ' ' . escapeshellarg($input) . ' ' . escapeshellarg($output));
		$ret = exec($cmd);
		t3lib_div::fixPermissions($output); // Change the permissions of the file
		return $ret;
	}

	/**
	 * This function format a RTE content
	 *
	 * @param string $value
	 * @return string formated content
	 */
	public function formatRTE($value) {
		return $this->pObj->cObj->parseFunc($value, array(), '< lib.parseFunc_RTE');
	}

	/**
	 * This function format a FILE link
	 *
	 * @param string $value
	 * @return string
	 */
	public function renderLinkType($value) {
		return $this->pObj->cObj->getTypoLink_URL($value);
	}

	/**
	 * This function return a mailto
	 *
	 * @param string $email
	 * @param array  $conf
	 * @return string
	 */
	public function getMailto($email, $conf = array()) {
		return $this->pObj->cObj->mailto_makelinks('mailto:' . $email, $conf);
	}

	/**
	 * This function return the base url
	 *
	 * @return string the base URL
	 */
	public function getBaseURL() {
		return $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'];
	}

	/**
	 * Set a variable in the register (accessible in the setup TS code)
	 *
	 * @param string $varname
	 * @param mixed  $varcontent
	 * @return void
	 */
	public function setRegister($varname, $varcontent) {
		$GLOBALS['TSFE']->register[$varname] = $varcontent;
	}

	/**
	 * Get a variable in the register (accessible in the setup TS code)
	 *
	 * @param mixed $varname
	 * @return mixed
	 */
	public function getRegister($varname) {
		return $GLOBALS['TSFE']->register[$varname];
	}

	/**
	 * Set a variable in typo3 fe_users session
	 *
	 * @param string $varname
	 * @param mixed  $varcontent
	 * @return void
	 */
	public function setSession($varname, $varcontent) {
		$GLOBALS['TSFE']->fe_user->setKey('ses', $varname, $varcontent);
		$GLOBALS['TSFE']->storeSessionData(); // validate the session
	}

	/**
	 * Get a variable in typo3 session (without params return all the session table)
	 *
	 * @param string $varname
	 * @return mixed
	 */
	public function getSession($varname = '') {
		if ($varname != '') {
			return $GLOBALS['TSFE']->fe_user->getKey('ses', $varname);
		} else {
			return $GLOBALS['TSFE']->fe_user->sesData;
		}
	}

	/**
	 * Get the tca of a table
	 *
	 * @param string $table
	 * @return array
	 */
	public static function getTableTCA($table) {
		global $TCA;
		$GLOBALS['TSFE']->includeTCA();
		t3lib_div::loadTCA($table);
		return $TCA[$table];
	}

	/**
	 * Get the rootline of the current page
	 *
	 * @return array
	 */
	public function getRootline() {
		$GLOBALS['TSFE']->getPageAndRootline();
		return $GLOBALS['TSFE']->rootLine;
	}

	/**
	 * This function return an array with ###value###
	 *
	 * @param array  $array
	 * @param string $marker_prefix
	 * @return array
	 */
	public function convertToMarkerArray($array, $marker_prefix = '') {
		$temp = array();
		foreach ($array as $key => $val) {
			$temp[self::convertToMarker($key, $marker_prefix)] = $val;
		}
		return $temp;
	}

	/**
	 * This function return a string with ###value###
	 *
	 * @param string  $value
	 * @param string  $marker_prefix
	 * @return string
	 */
	public function convertToMarker($value, $marker_prefix = '') {
		return '###' . strtoupper($marker_prefix . $value) . '###';
	}

	/**
	 * This function return the piVars Array with exlude value like var1,var2
	 *
	 * @param string  $exclude
	 * @param boolean $prefix
	 * @return array
	 */
	public function getPiVars($exclude = '', $prefix = FALSE) {
		$piVars = array();
		foreach ($this->pObj->piVars as $piVar => $piVarvalue) {
			if (!t3lib_div::inList($exclude, $piVar)) {
				if ($prefix === TRUE) {
					$piVars[$this->pObj->prefixId . '[' . $piVar . ']'] = $piVarvalue;
				} else {
					$piVars[$piVar] = $piVarvalue;
				}
			}
		}
		return $piVars;
	}

	/**
	 * This function get a tt_content records (for example a plugin)
	 *
	 * @param int $uid
	 * @return string HTML
	 */
	public function getGeneratedContent($uid) {
		$objContent = array(
			'tables' => 'tt_content',
			'source' => 'tt_content_' . $uid
		);
		return $this->pObj->cObj->RECORDS($objContent);
	}

	/**
	 * Load a TS string
	 *
	 * @param array  $conf
	 * @param string $content
	 * @return array
	 */
	public function loadTS($conf, $content) {
		require_once(PATH_t3lib . 'class.t3lib_tsparser.php');
		/** @var $tsparser t3lib_tsparser */
		$tsparser = t3lib_div::makeInstance('t3lib_tsparser');
		// Copy conf into existing setup
		$tsparser->setup = $conf;
		// Parse the new Typoscript
		$tsparser->parse($content);
		// Copy the resulting setup back into conf
		return $tsparser->setup;
	}

	/**
	 * Loads the TypoScript for the given extension prefix, e.g. tx_cspuppyfunctions_pi1, for use in a backend module.
	 *
	 * @param int    $pid
	 * @param string $extKey
	 * @return array
	 */
	public function loadTypoScriptForBEModule($pid, $extKey) {
		require_once(PATH_t3lib . 'class.t3lib_page.php');
		require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
		require_once(PATH_t3lib . 'class.t3lib_tsparser_ext.php');
		/** @var $sysPageObj t3lib_pageSelect */
		$sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sysPageObj->getRootLine($pid);
		/** @var $TSObj t3lib_tsparser_ext */
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
	 * @param string   $openFile
	 * @param boolean  $columnsOnly
	 * @param string   $delimiters
	 * @return array
	 */
	public function csv2array($openFile, $columnsOnly = FALSE, $delimiters = ";") {
		$handle = fopen($openFile, "r");
		$rows = 0;
		$columns = array();
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
	 * @param array    $buffer
	 * @param string   $file
	 * @param string   $delimiters
	 * @param boolean  $stringonly
	 * @return string
	 */
	public function array2csv($buffer, $file, $delimiters = ";", $stringonly = FALSE) {
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

		if ($stringonly == TRUE) {
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
	 * @param string  $text         String to truncate.
	 * @param integer $length       Length of returned string, including ellipsis.
	 * @param mixed   $ending       If string, will be used as Ending and appended to the trimmed string
	 * @param boolean $exact        If FALSE, $text will not be cut mid-word
	 * @param boolean $considerHtml If TRUE, HTML tags would be handled correctly
	 * @return string Trimmed string.
	 */
	public function truncate($text, $length = 100, $ending = '...', $exact = TRUE, $considerHtml = FALSE) {
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
						if ($pos !== FALSE) {
							array_splice($openTags, $pos, 1);
						}
					}
				}
				$truncate .= $tag[1];

				$contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
				if ($contentLength + $totalLength > $length) {
					$left = $length - $totalLength;
					$entitiesLength = 0;
					if (preg_match_all(
						'/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE
					)
					) {
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
	 *
	 * @return void
	 */
	public function debugQueryInit() {
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = TRUE;
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
	}

	/**
	 * Display the last query if you have activated the debugQueryInit()
	 *
	 * @return void
	 */
	public function debugQuery() {
		t3lib_div::debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery, 'SQL');
	}

	/**
	 * Replace the XCLASS statement at end of a class file
	 *
	 * @param string $file
	 * @return void
	 */
	public static function XCLASS($file) {
		global $TYPO3_CONF_VARS;
		if (defined(
			'TYPO3_MODE'
		) && isset($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS'][$file]) && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS'][$file]
		) {
			include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS'][$file]);
		}
	}

	/**
	 * Send a email using t3lib_htmlmail or the new swift mailer
	 * It depends on the TYPO3 version
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @param string $type
	 * @param string $fromEmail
	 * @param string $fromName
	 * @param string $charset
	 * @param array  $files
	 * @return mixed
	 */
	public function sendEmail($to, $subject, $message, $type = 'plain', $fromEmail = '', $fromName = '', $charset = 'utf-8', $files = array()) {
		$useSwiftMailer = t3lib_div::compat_version('4.5');
		if ($useSwiftMailer) {
			// new TYPO3 swiftmailer code
			/** @var $mail t3lib_mail_Message */
			$mail = t3lib_div::makeInstance('t3lib_mail_Message');
			$mail->setTo(array($to));
			$mail->setSubject($subject);
			$mail->setCharset($charset);
			$mail->setFrom(array($fromEmail => $fromName));
			$mail->setReplyTo(array($fromEmail => $fromName));

			// add Files
			if (!empty($files)) {
				foreach ($files as $file) {
					$mail->attach(Swift_Attachment::fromPath($file));
				}
			}

			// add Plain
			if ($type == 'plain') {
				$mail->addPart($message, 'text/plain');
			}

			// add HTML
			if ($type == 'html') {
				$mail->setBody($message, 'text/html');
			}

			// send
			$mail->send();
		} else {
			// send mail
			/** @var $mail t3lib_htmlmail */
			$mail = t3lib_div::makeInstance('t3lib_htmlmail');
			$mail->start();
			$mail->useBase64();
			$mail->charset = $charset;
			$mail->subject = $subject;

			// from
			$mail->from_email = $fromEmail;
			$mail->from_name = $fromName;

			// replyTo
			$mail->replyto_email = $fromEmail;
			$mail->replyto_name = $fromName;

			// recipients
			$mail->setRecipient($to);

			// add Plain
			if ($type == 'plain') {
				$mail->addPlain($message);
			}

			// add HTML
			if ($type == 'html') {
				$mail->theParts['html']['content'] = $message;
				$mail->theParts['html']['path'] = '';
				$mail->extractMediaLinks();
				$mail->extractHyperLinks();
				$mail->fetchHTMLMedia();
				$mail->substMediaNamesInHTML(0);
				$mail->substHREFsInHTML();
				$mail->setHtml($mail->encodeMsg($mail->theParts['html']['content']));
			}

			// add Files
			if (!empty($files)) {
				foreach ($files as $file) {
					$mail->addAttachment($file);
				}
			}

			// send
			$mail->setHeaders();
			$mail->setContent();

			return $mail->sendtheMail();
		}
	}

	/**
	 * Get the name of the caller function
	 *
	 * @param int $rank
	 * @return mixed
	 */
	public function get_caller_method($rank = 1) {
		$traces = debug_backtrace();
		if (isset($traces[$rank])) {
			return array(
				'file'     => $traces[$rank]['file'],
				'line'     => $traces[$rank]['line'],
				'function' => $traces[$rank]['function']
			);
		}
		return NULL;
	}

	/**
	 * Get the current memory usage
	 *
	 * @return void
	 */
	public function getMemoryUsage() {
		return (integer)((memory_get_usage() + 512) / 1024);
	}

	/**
	 * Load an xml locallang file
	 *
	 * @param string $LLFile
	 * @param string $langKey
	 * @return array
	 */
	public function loadLL($LLFile, $langKey = NULL) {
		$tsfeLoaded = isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE']);
		$langLoaded = isset($GLOBALS['LANG']) && is_object($GLOBALS['LANG']);

		// Lang detection
		if (is_NULL($langKey)) {
			if ($tsfeLoaded) {
				$langKey = $GLOBALS['TSFE']->lang;
			} elseif ($langLoaded) {
				$langKey = $GLOBALS['LANG']->lang;
			}
		}

		// Render Charset
		if ($langLoaded) {
			$renderCharset = $GLOBALS['LANG']->charSet;
		} elseif ($tsfeLoaded) {
			$renderCharset = $GLOBALS['TSFE']->renderCharset;
		}

		// Language list
		$LLArray = array();
		$langLoadList = array_unique(array('default', $langKey));

		// Loads locallang file
		if (@is_file($LLFile)) {
			$LOCAL_LANG = t3lib_div::readLLXMLfile($LLFile, end($langLoadList), $renderCharset);
		} else {
			$LOCAL_LANG = array();
		}

		// Process default langage and requested langage
		foreach ($langLoadList as $tLangKey) {
			if (isset($LOCAL_LANG[$tLangKey]) && is_array($LOCAL_LANG[$tLangKey])) {
				$LLArray[$tLangKey] = count($LLArray[$tLangKey]) ? array_merge($LLArray[$tLangKey], $LOCAL_LANG[$tLangKey])
					: $LOCAL_LANG[$tLangKey];
			}
		}

		// Merge arrays (requested langage(s) overrides default language)
		$finalRes = array();
		foreach ($langLoadList as $v) {
			if (isset($LLArray[$v])) {
				$finalRes = array_merge($finalRes, $LLArray[$v]);
				unset($LLArray[$v]);
			}
		}
		unset($LLArray);

		return $finalRes;
	}

	/**
	 * Determines the rootpage ID for a given page.
	 *
	 * @param    int  $pageId  A page ID somewhere in a tree.
	 * @return   int           The page's tree branch's root page ID
	 */
	public function getRootPageId($pageId) {
		$rootPageId = $pageId;
		$rootline = t3lib_BEfunc::BEgetRootLine($pageId);

		$rootline = array_reverse($rootline);
		foreach ($rootline as $page) {
			if ($page['is_siteroot']) {
				$rootPageId = $page['uid'];
			}
		}

		return $rootPageId;
	}

	/**
	 * Build the TSFE (for the BE for example)
	 *
	 * @param int $pid
	 * @return void
	 */
	public function buildTSFE($pid) {
		require_once(PATH_t3lib . 'class.t3lib_timetrack.php');
		require_once(PATH_t3lib . 'class.t3lib_tsparser_ext.php');
		require_once(PATH_t3lib . 'class.t3lib_page.php');
		require_once(PATH_t3lib . 'class.t3lib_stdgraphic.php');
		require_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_fe.php');
		require_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');
		require_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_gifbuilder.php');
		require_once(PATH_site . 'typo3/sysext/cms/tslib/class.tslib_content.php');
		require_once(PATH_t3lib . 'class.t3lib_div.php');

		$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');

		if (!is_object($GLOBALS['TT'])) {
			$GLOBALS['TT'] = new t3lib_timeTrack;
			$GLOBALS['TT']->start();
		}

		if (!is_object($GLOBALS['TSFE']) && $pid) {
			$GLOBALS['TSFE'] = new $temp_TSFEclassName($GLOBALS['TYPO3_CONF_VARS'], $pid, 0, 0, 0, 0, 0, 0);
			$GLOBALS['TSFE']->tmpl = t3lib_div::makeInstance('t3lib_tsparser_ext');
			$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
			$GLOBALS['TSFE']->tmpl->tt_track = 0; // Do not log time-performance information
			$GLOBALS['TSFE']->tmpl->init();
			$rootLine = $GLOBALS['TSFE']->sys_page->getRootLine($pid);
			$GLOBALS['TSFE']->tmpl->runThroughTemplates($rootLine, 0);
			$GLOBALS['TSFE']->tmpl->generateConfig();
			$GLOBALS['TSFE']->tmpl->loaded = 1;
			$GLOBALS['TSFE']->getConfigArray();
			$GLOBALS['TSFE']->linkVars = '' . $GLOBALS['TSFE']->config['config']['linkVars'];
			if ($GLOBALS['TSFE']->config['config']['simulateStaticDocuments_pEnc_onlyP']) {
				foreach (t3lib_div::trimExplode(
					         ',', $GLOBALS['TSFE']->config['config']['simulateStaticDocuments_pEnc_onlyP'], 1
				         ) as $temp_p) {
					$GLOBALS['TSFE']->pEncAllowedParamNames[$temp_p] = 1;
				}
			}
			$GLOBALS['TSFE']->newCObj();
		}
	}

	/**
	 * Returns given word as CamelCased.
	 *
	 * Converts a word like "send_email" to "SendEmail". It
	 * will remove non alphanumeric characters from the word, so
	 * "who's online" will be converted to "WhoSOnline"
	 *
	 * @param    string  $word  Word to convert to camel case
	 * @return   string         UpperCamelCasedWord
	 */
	public static function camelize($word) {
		return str_replace(' ', '', ucwords(preg_replace('![^A-Z^a-z^0-9]+!', ' ', $word)));
	}

	/**
	 * Returns a given CamelCasedString as an lowercase string with underscores.
	 * Example: Converts BlogExample to blog_example, and minimalValue to minimal_value
	 *
	 * @param    string   $string String to be converted to lowercase underscore
	 * @return   string            lowercase_and_underscored_string
	 */
	public static function camelCaseToLowerCaseUnderscored($string) {
		return strtolower(preg_replace('/(?<=\w)([A-Z])/', '_\\1', $string));
	}

	/**
	 * Returns a given string with underscores as UpperCamelCase.
	 * Example: Converts blog_example to BlogExample
	 *
	 * @param    string    $string  String to be converted to camel case
	 * @return   string             UpperCamelCasedWord
	 */
	public static function underscoredToUpperCamelCase($string) {
		return str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($string))));
	}

}

tx_t3devapi_miscellaneous::XCLASS('ext/t3devapi/class.tx_t3devapi_miscellaneous.php');

?>