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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Paginate controller to create the pagination.
 * Extended version from fluid core
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_Widget_Controller_PaginateController extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController
{

    /**
     * @var array
     */
    protected $configuration = array(
        'itemsPerPage'           => 10,
        'insertAbove'            => false,
        'insertBelow'            => true,
        'pagesAfter'             => 3,
        'pagesBefore'            => 3,
        'lessPages'              => true,
        'forcedNumberOfLinks'    => 5,
        'forceFirstPrevNextlast' => false,
        'showFirstLast'          => true
    );

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected $objects;

    /**
     * @var integer
     */
    protected $currentPage = 1;

    /**
     * @var integer
     */
    protected $pagesBefore = 1;

    /**
     * @var integer
     */
    protected $pagesAfter = 1;

    /**
     * @var boolean
     */
    protected $lessPages = false;

    /**
     * @var integer
     */
    protected $forcedNumberOfLinks = 10;

    /**
     * @var integer
     */
    protected $numberOfPages = 1;

    /**
     * Initialize the action and get correct configuration
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->objects = $this->widgetConfiguration['objects'];
        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
            $this->configuration,
            (array)$this->widgetConfiguration['configuration'],
            true
        );
        $this->numberOfPages = ceil(count($this->objects) / (integer)$this->configuration['itemsPerPage']);
        $this->pagesBefore = (integer)$this->configuration['pagesBefore'];
        $this->pagesAfter = (integer)$this->configuration['pagesAfter'];
        $this->lessPages = (boolean)$this->configuration['lessPages'];
        $this->forcedNumberOfLinks = (integer)$this->configuration['forcedNumberOfLinks'];
    }

    /**
     * If a certain number of links should be displayed, adjust before and after
     * amounts accordingly.
     *
     * @return void
     */
    protected function adjustForForcedNumberOfLinks()
    {
        $forcedNumberOfLinks = $this->forcedNumberOfLinks;
        if ($forcedNumberOfLinks > $this->numberOfPages) {
            $forcedNumberOfLinks = $this->numberOfPages;
        }
        $totalNumberOfLinks = min($this->currentPage, $this->pagesBefore) +
            min($this->pagesAfter, $this->numberOfPages - $this->currentPage) + 1;
        if ($totalNumberOfLinks <= $forcedNumberOfLinks) {
            $delta = intval(ceil(($forcedNumberOfLinks - $totalNumberOfLinks) / 2));
            $incr = ($forcedNumberOfLinks & 1) == 0 ? 1 : 0;
            if ($this->currentPage - ($this->pagesBefore + $delta) < 1) {
                // Too little from the right to adjust
                $this->pagesAfter = $forcedNumberOfLinks - $this->currentPage - 1;
                $this->pagesBefore = $forcedNumberOfLinks - $this->pagesAfter - 1;
            } elseif ($this->currentPage + ($this->pagesAfter + $delta) >= $this->numberOfPages) {
                $this->pagesBefore = $forcedNumberOfLinks - ($this->numberOfPages - $this->currentPage);
                $this->pagesAfter = $forcedNumberOfLinks - $this->pagesBefore - 1;
            } else {
                $this->pagesBefore += $delta;
                $this->pagesAfter += $delta - $incr;
            }
        }

    }

    /**
     * Main action which does all the fun
     *
     * @param integer $currentPage
     * @return void
     */
    public function indexAction($currentPage = 1)
    {
        // set current page
        $this->currentPage = (integer)$currentPage;
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        } elseif ($this->currentPage > $this->numberOfPages) {
            $this->currentPage = $this->numberOfPages;
        }

        // modify query
        $itemsPerPage = (integer)$this->configuration['itemsPerPage'];

        if (is_a($this->objects, '\TYPO3\CMS\Extbase\Persistence\QueryResultInterface') || is_a($this->objects, 'TYPO3\\CMS\\Extbase\\Persistence\\QueryResultInterface')) {
            $query = $this->objects->getQuery();

            // limit should only be used if needed and pagination only if results > itemsPerPage
            if ($itemsPerPage < $this->objects->count()) {
                $query->setLimit($itemsPerPage);
            }

            if ($this->currentPage > 1) {
                $query->setOffset((integer)($itemsPerPage * ($this->currentPage - 1)));
            }
            $modifiedObjects = $query->execute();
        } else {
            if (empty($this->objects)) {
                return null;
            }

            $offset = 0;
            if ($this->currentPage > 1) {
                $offset = ((integer)($itemsPerPage * ($this->currentPage - 1)));
            }
            $modifiedObjects = array_slice($this->objects, $offset, (integer)$itemsPerPage);
        }

        $this->view->assign('contentArguments', array($this->widgetConfiguration['as'] => $modifiedObjects));
        $this->view->assign('configuration', $this->configuration);
        $this->view->assign('pagination', $this->buildPagination());
    }

    /**
     * Returns an array with the keys
     * "pages", "current", "numberOfPages", "nextPage" & "previousPage"
     *
     * @return array
     */
    public function buildPagination()
    {
        $this->adjustForForcedNumberOfLinks();

        $pages = array();
        $start = max($this->currentPage - $this->pagesBefore, 0);
        $end = min($this->numberOfPages, $this->currentPage + $this->pagesAfter + 1);
        for ($i = $start; $i < $end; $i++) {
            $j = $i + 1;
            $pages[] = array('number' => $j, 'isCurrent' => (intval($j) === intval($this->currentPage)));
        }

        $pagination = array(
            'pages'         => $pages,
            'current'       => $this->currentPage,
            'numberOfPages' => $this->numberOfPages,
            'numberOfItems' => count($this->objects),
            'pagesBefore'   => $this->pagesBefore,
            'pagesAfter'    => $this->pagesAfter,
            'firstPageItem' => ($this->currentPage - 1) * (int)$this->configuration['itemsPerPage'] + 1
        );
        if ($this->currentPage < $this->numberOfPages) {
            $pagination['nextPage'] = $this->currentPage + 1;
            $pagination['lastPageItem'] = $this->currentPage * (integer)$this->configuration['itemsPerPage'];
        } else {
            $pagination['lastPageItem'] = $pagination['numberOfItems'];
        }

        // previous pages
        if ($this->currentPage > 1) {
            $pagination['previousPage'] = $this->currentPage - 1;
        }

        // less pages (before current)
        if ($start > 0 && $this->lessPages) {
            $pagination['lessPages'] = true;
        }

        // next pages (after current)
        if ($end != $this->numberOfPages && $this->lessPages) {
            $pagination['morePages'] = true;
        }

        return $pagination;
    }

    /**
     * Fix for setViewConfiguration to override the path
     * plugin.tx_xxx.view.widget.Tx_T3devapi_ViewHelpers_Widget_PaginateViewHelper.templateRootPath = EXT:xxx/Resources/Private/Templates/
     *
     * @param TYPO3\CMS\Fluid\View\TemplateView $view
     * @return void
     */
    protected function setViewConfiguration(TYPO3\CMS\Fluid\View\TemplateView $view)
    {
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $widgetViewHelperClassName = $this->request->getWidgetContext()->getWidgetViewHelperClassName();
        if (isset($extbaseFrameworkConfiguration['view']['widget'][$widgetViewHelperClassName]['templateRootPath'])
            && strlen($extbaseFrameworkConfiguration['view']['widget'][$widgetViewHelperClassName]['templateRootPath']) > 0
            && method_exists($view, 'setTemplateRootPath')
        ) {
            $view->setTemplateRootPath(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['widget'][$widgetViewHelperClassName]['templateRootPath']));
        }
    }

}

?>