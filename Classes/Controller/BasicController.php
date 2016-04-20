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
class Tx_T3devapi_Controller_BasicController extends TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

  /**
  * @var array
  */
  protected $piVars = array();

  /**
  * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
  */
  protected $typoScriptService;

  /**
  * @param \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService
  */
  public function injectTypoScriptService(\TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService)
  {
    $this->typoScriptService = $typoScriptService;
  }

  /**
  * Initialize view path if settings is passed (ts, flexform...)
  *
  * @param TYPO3\CMS\Fluid\View\TemplateView $view
  */
  protected function initializeView(TYPO3\CMS\Fluid\View\TemplateView $view) {
    if (!empty($this->settings['rootpath'])) {
      $view->setTemplateRootPath(PATH_site . trim($this->settings['rootpath']));
    }
  }

  protected function getPluginConfiguration($extensionName, $pluginName = null) {
    $setup =  $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
    $pluginConfiguration = array();
    if (TYPO3_MODE === 'FE') {
      $pluginKey = 'plugin.';
    } else {
      $pluginKey = 'module.';
    }
    if (is_array($setup[$pluginKey]['tx_' . strtolower($extensionName) . '.'])) {
      $pluginConfiguration = $this->typoScriptService->convertTypoScriptArrayToPlainArray($setup[$pluginKey]['tx_' . strtolower($extensionName) . '.']);
    }
    if ($pluginName !== null) {
      $pluginSignature = strtolower($extensionName . '_' . $pluginName);
      if (is_array($setup['plugin.']['tx_' . $pluginSignature . '.'])) {
        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
        $pluginConfiguration,
        $this->typoScriptService->convertTypoScriptArrayToPlainArray($setup[$pluginKey]['tx_' . $pluginSignature . '.'])
      );
    }
  }
  return $pluginConfiguration;
}

/**
* Initialiaze the settings from the flexform
*
* @param $repository string the repository name
* @return void
*/
protected function initializeSettings($repository) {
  $tsSettings = $this->getPluginConfiguration($this->extensionName);
  $originalSettings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

  if (!empty($tsSettings['settings'])) {
    foreach ($tsSettings['settings'] as $settingKey => $settingValue) {
      if (empty($originalSettings[$settingKey])) {
        $originalSettings[$settingKey] = $settingValue;
      }
    }
  }

  $this->settings = $originalSettings;
  $this->view->assign('settings', $this->settings);

  $orderDirection = \TYPO3\CMS\Extbase\Persistence\Generic\Query::ORDER_DESCENDING;
  $orderBy = 'title';

  if (!empty($this->settings['orderDirection'])) {
    switch ($this->settings['orderDirection']) {
      case 'desc':
      $orderDirection = \TYPO3\CMS\Extbase\Persistence\Generic\Query::ORDER_DESCENDING;
      break;
      case 'asc':
      $orderDirection = \TYPO3\CMS\Extbase\Persistence\Generic\Query::ORDER_ASCENDING;
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
