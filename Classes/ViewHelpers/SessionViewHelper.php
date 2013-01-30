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
 * ViewHelper to get a session variable
 *
 * Example
 * <t3devapi:session index="{object.var}" identifier="{object.var}" />
 * {t3devapi:session(index:'odlannuaireCollab', identifier:'{collaborateur.uid}')}
 *
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_ViewHelpers_SessionViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper
{

	/**
	 * @param string $index
	 * @param string $identifier
	 * @return string
	 */
	public function render($index, $identifier) {
		if (!session_id()) {
			session_start();
		}
		return $_SESSION[$index][$identifier];
	}

}

?>