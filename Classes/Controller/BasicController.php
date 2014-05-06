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
 * Tx_T3devapi_Controller_BasicController
 * Class with classic functions for a controller
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_Controller_BasicController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var array
	 */
	protected $piVars = array();

	/**
	 * Initialize view path if settings is passed (ts, flexform...)
	 *
	 * @param Tx_Extbase_MVC_View_ViewInterface $view
	 */
	protected function initializeView(Tx_Extbase_MVC_View_ViewInterface $view) {
		if (!empty($this->settings['rootpath'])) {
			$view->setTemplateRootPath(PATH_site . trim($this->settings['rootpath']));
		}
	}

	/**
	 * Initialiaze the settings from the flexform
	 *
	 * @param $repository string the repository name
	 * @return void
	 */
	protected function initializeSettings($repository) {
		$orderDirection = Tx_Extbase_Persistence_Query::ORDER_DESCENDING;
		$orderBy = 'title';

		if (!empty($this->settings['orderDirection'])) {
			switch ($this->settings['orderDirection']) {
				case 'desc':
					$orderDirection = Tx_Extbase_Persistence_Query::ORDER_DESCENDING;
					break;
				case 'asc':
					$orderDirection = Tx_Extbase_Persistence_Query::ORDER_ASCENDING;
					break;
			}
		}

		if (!empty($this->settings['orderBy'])) {
			$orderBy = $this->settings['orderBy'];
			$this->$repository->setQueryOrderings(array($orderBy => $orderDirection));
		}

		if (!empty($this->settings['offset'])) {
			$this->$repository->setQueryOffset($this->settings['offset']);
		}

		if (!empty($this->settings['limit'])) {
			$this->$repository->setQueryLimit($this->settings['limit']);
		}

		if (!empty($this->settings['startingpoint'])) {
			$this->$repository->setStoragePage($this->settings['startingpoint']);
		}

		if (!empty($this->settings['listUID'])) {
			$this->$repository->setUidList($this->settings['listUID']);
		}

		$argKey = strtolower('tx_' . $this->request->getControllerExtensionKey() . '_' . $this->request->getPluginName());
		$piVars = $this->request->getArguments();
		$this->piVars = $this->request->getArguments();
		$pivarsparams = array();
		foreach ($piVars as $pivarkey => $pivarvalue) {
			$pivarsparams[$argKey][$pivarkey] = $pivarvalue;
		}
		$this->$repository->setPiVars($piVars);
		$this->view->assign('pivars', $piVars);
		$this->view->assign('pivarsparams', $pivarsparams);
		$this->view->assign('argkey', $argKey);
	}

}