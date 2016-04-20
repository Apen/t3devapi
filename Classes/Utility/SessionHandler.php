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
 * Tx_T3devapi_Utility_SessionHandler
 * Class to work with session in BE/FE
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class  Tx_T3devapi_Utility_SessionHandler
{
    /**
     * Keeps TYPO3 mode.
     * Either 'FE' or 'BE'.
     *
     * @var string
     */
    protected $mode = null;

    /**
     * The User-Object with the session-methods.
     * Either $GLOBALS['BE_USER'] or $GLOBALS['TSFE']->fe_user.
     *
     * @var object
     */
    protected $sessionObject = null;

    /**
     * The key the data is stored in the session.
     *
     * @var string
     */
    protected $storageKey = 't3devapi';

    /**
     * Class constructor.
     *
     * @param string $mode
     */
    public function __construct($mode = null)
    {
        if ($mode) {
            $this->mode = $mode;
        }

        if ($this->mode === null || ($this->mode != "BE" && $this->mode != "FE")) {
            throw new \Exception("Typo3-Mode is not defined!", 1388660107);
        }
        $this->sessionObject = ($this->mode == "BE") ? $GLOBALS['BE_USER'] : $GLOBALS['TSFE']->fe_user;
    }

    /**
     * Setter for storageKey
     *
     * @return void
     */
    public function setStorageKey($storageKey)
    {
        $this->storageKey = $storageKey;
    }

    /**
     * Store value in session
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function store($key, $value)
    {
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        $sessionData[$key] = $value;
        $this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
    }

    /**
     * Delete value in session
     *
     * @param string $key
     * @return void
     */
    public function delete($key)
    {
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        unset($sessionData[$key]);
        $this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
    }

    /**
     * Read value from session
     *
     * @param string $key
     * @return mixed
     */
    public function get($key = null)
    {
        $sessionData = $this->sessionObject->getSessionData($this->storageKey);
        if ($key === null) {
            return $sessionData;
        } else {
            return isset($sessionData[$key]) ? $sessionData[$key] : null;
        }
    }
}