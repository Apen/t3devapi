<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Yohann CERDAN <cerdanyohann@yahoo.fr>
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
 * ViewHelper to write in header
 *
 * Example
 * <code title="Example">
 * <f:form.textarea name="content" value="{pivars.content}" class="tinymcerte"/>
 * <t3devapi:tinyMce/>
 * </code>
 *
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_TinyMceViewHelper extends TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Return RTE
     *
     * @param    string  $name       Field name
     * @param    string  $namePrefix Name prefix (tx_ext_pi1[object])
     * @param    boolean $isLast     Is last flag (generate JavaScript only for the last RTE)
     * @param    string  $value      Any value
     * @param    string  $width      Width
     * @param    string  $height     height
     * @return   string    Generated RTE content
     */
    public function render($width = '400', $height = '300')
    {
        $headerkey = 't3devapi-tinymce-js';
        if (empty($GLOBALS['TSFE']->additionalHeaderData[$headerkey])) {
            $content = '<script type="text/javascript" src="//cdn.tinymce.com/4/tinymce.min.js"></script>';
            $content .= "
			<script type='text/javascript'>
			tinymce.init({
				selector: '.tinymcerte',
				width: " . $width . ",
				height:  " . $height . ",
				menubar: false,
				//content_css: 'css/content.css',
				language_url : '/typo3conf/ext/recordsmanagerfe/res/fr_FR.js',
				plugins: [
					'advlist autolink link image lists charmap print preview hr anchor pagebreak',
					'wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
					'save table directionality emoticons template paste textcolor'
				],
				toolbar: 'bold italic bullist outdent indent | link image'
			});
			</script>
			";
            $GLOBALS['TSFE']->additionalHeaderData[$headerkey] = $content;
        }
        return null;
    }


}

?>
