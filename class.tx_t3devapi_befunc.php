<?php

/* * *************************************************************
*
* Copyright notice
*
* (c) 2011 Yohann CERDAN <ycerdan@onext.fr>
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
 * tx_t3devapi_befunc
 * Class with some be functions
 *
 * @author Yohann
 * @copyright Copyright (c) 2011
 */

class tx_t3devapi_befunc {
    /**
     * tx_t3devapi_befunc::__construct()
     */

    function __construct()
    {
    }

    function drawDBListTable($content)
    {
        return '<table cellspacing="1" cellpadding="2" border="0" class="typo3-dblist">' . $content . '</table>';
    }

    function drawDBListTitle($content, $colspan = 100)
    {
        return '<tr class="t3-row-header"><td colspan="' . $colspan . '">' . $content . '</td></tr>';
    }

    function drawDBListHeader($headers)
    {
        $content = '';
        $content .= '<tr class="c-headLine">';
        foreach ($headers as $header) {
            $content .= '<td class="cell">' . $header . '</td>';
        }
        $content .= '</tr>';
        return $content;
    }

    function drawDBListRows($rows)
    {
        $content = '';
        $content .= '<tr class="db_list_normal">';
        foreach ($rows as $row) {
            $content .= '<td class="cell">' . $row . '</td>';
        }
        $content .= '</tr>';
        return $content;
    }

    /**
     * tx_t3devapi_befunc::renderListNavigation()
     * Creates a typo3 backend pagebrowser for tables with many records
     *
     * Example (use limit in your SQL) :
     * $pointer = t3lib_div::_GP('pointer');
     * $limit = ($pointer !== null) ? $pointer . ',' . $nbElementsPerPage : '0,' . $nbElementsPerPage;
     * $current = ($pointer !== null) ? intval($pointer) : 0;
     * $pageBrowser = $this->renderListNavigation($nbTotalRecords, $this->nbElementsPerPage, $current, $nbElementsPerPage);
     */

    function renderListNavigation($totalItems, $iLimit, $firstElementNumber, $alwaysShow = false)
    {
        $totalPages = ceil($totalItems / $iLimit);

        $content = '';
        $returnContent = '';
        // Show page selector if not all records fit into one page
        if ($totalPages > 1 || $alwaysShow == true) {
            $first = $previous = $next = $last = $reload = '';
            $listURLOrig = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=' . t3lib_div::_GP('M');
            $listURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=' . t3lib_div::_GP('M');
            $listURL .= '&nbPerPage=' . $iLimit;
            $currentPage = floor(($firstElementNumber + 1) / $iLimit) + 1;
            // First
            if ($currentPage > 1) {
                $labelFirst = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:first');
                $first = '<a href="' . $listURL . '&pointer=0"><img width="16" height="16" title="' . $labelFirst . '" alt="' . $labelFirst . '" src="sysext/t3skin/icons/gfx/control_first.gif"></a>';
            } else {
                $first = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_first_disabled.gif">';
            }
            // Previous
            if (($currentPage - 1) > 0) {
                $labelPrevious = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:previous');
                $previous = '<a href="' . $listURL . '&pointer=' . (($currentPage - 2) * $iLimit) . '"><img width="16" height="16" title="' . $labelPrevious . '" alt="' . $labelPrevious . '" src="sysext/t3skin/icons/gfx/control_previous.gif"></a>';
            } else {
                $previous = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_previous_disabled.gif">';
            }
            // Next
            if (($currentPage + 1) <= $totalPages) {
                $labelNext = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:next');
                $next = '<a href="' . $listURL . '&pointer=' . (($currentPage) * $iLimit) . '"><img width="16" height="16" title="' . $labelNext . '" alt="' . $labelNext . '" src="sysext/t3skin/icons/gfx/control_next.gif"></a>';
            } else {
                $next = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_next_disabled.gif">';
            }
            // Last
            if ($currentPage != $totalPages) {
                $labelLast = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:last');
                $last = '<a href="' . $listURL . '&pointer=' . (($totalPages - 1) * $iLimit) . '"><img width="16" height="16" title="' . $labelLast . '" alt="' . $labelLast . '" src="sysext/t3skin/icons/gfx/control_last.gif"></a>';
            } else {
                $last = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_last_disabled.gif">';
            }

            $pageNumberInput = '<span>' . $currentPage . '</span>';
            $pageIndicator = '<span class="pageIndicator">'
             . sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:pageIndicator'), $pageNumberInput, $totalPages)
             . '</span>';

            if ($totalItems > ($firstElementNumber + $iLimit)) {
                $lastElementNumber = $firstElementNumber + $iLimit;
            } else {
                $lastElementNumber = $totalItems;
            }

            $rangeIndicator = '<span class="pageIndicator">'
             . sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:rangeIndicator'), $firstElementNumber + 1, $lastElementNumber)
             . '</span>';

            $reload = '<input type="text" name="nbPerPage" id="nbPerPage" size="5" value="' . $iLimit . '"/> / page '
             . '<a href="#"  onClick="jumpToUrl(\'' . $listURLOrig . '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value);">'
             . '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/refresh_n.gif"></a>';

            $content .= '<div id="typo3-dblist-pagination">'
             . $first . $previous
             . '<span class="bar">&nbsp;</span>'
             . $rangeIndicator . '<span class="bar">&nbsp;</span>'
             . $pageIndicator . '<span class="bar">&nbsp;</span>'
             . $next . $last . '<span class="bar">&nbsp;</span>'
             . $reload
             . '</div>';

            $returnContent = $content;
        } // end of if pages > 1
        return $returnContent;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class.tx_t3devapi_befunc.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3devapi/class.tx_t3devapi_befunc.php']);
}

?>