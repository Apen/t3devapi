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
 * Tx_T3devapi_Domain_Repository_BasicRepository
 * Class with classic functions for a repository
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage t3devapi
 */
class Tx_T3devapi_Domain_Repository_BasicRepository extends Tx_Extbase_Persistence_Repository
{
	protected $queryOffset = NULL;
	protected $queryLimit = NULL;
	protected $queryOrderings = array();
	protected $storagePage = NULL;
	protected $uidList = NULL;
	protected $piVars = array();

	/**
	 * Returns a query for objects of this repository
	 *
	 * @return Tx_Extbase_Persistence_QueryInterface
	 */
	public function createQuery() {
		$query = parent::createQuery();
		$constraints = array();

		if (!empty($this->queryOffset)) {
			$query->setOffset((int)$this->queryOffset);
		}

		if (!empty($this->queryLimit)) {
			$query->setLimit((int)$this->queryLimit);
		}

		if (!empty($this->queryOrderings)) {
			$query->setOrderings($this->queryOrderings);
		}

		if (!empty($this->storagePage)) {
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$pidList = t3lib_div::intExplode(',', Tx_T3devapi_Utility_Page::extendPidListByChildren($this->storagePage, 9999), TRUE);
			$constraints[] = $query->in('pid', $pidList);
		}

		if (!empty($this->uidList)) {
			$uidList = t3lib_div::intExplode(',', $this->uidList, TRUE);
			$constraints[] = $query->in('uid', $uidList);
		}

		if (!empty($this->piVars)) {
			$constraints = $this->setSearchConstraints($query, $constraints);
		}

		if (!empty($constraints)) {
			$query->matching(
				$query->logicalAnd($constraints)
			);
		}

		return $query;
	}

	/**
	 * Add constraints to the query
	 * Overload this function to add your process
	 *
	 * @param $query
	 * @param $constraints
	 * @return array
	 */
	public function setSearchConstraints($query, $constraints) {
		return $constraints;
	}

	public function setQueryLimit($queryLimit) {
		$this->queryLimit = $queryLimit;
	}

	public function getQueryLimit() {
		return $this->queryLimit;
	}

	public function setQueryOffset($queryOffset) {
		$this->queryOffset = $queryOffset;
	}

	public function getQueryOffset() {
		return $this->queryOffset;
	}

	public function setQueryOrderings($queryOrderings) {
		$this->queryOrderings = $queryOrderings;
	}

	public function getQueryOrderings() {
		return $this->queryOrderings;
	}

	public function setStoragePage($storagePage) {
		$this->storagePage = $storagePage;
	}

	public function getStoragePage() {
		return $this->storagePage;
	}

	public function setUidList($uidList) {
		$this->uidList = $uidList;
	}

	public function getUidList() {
		return $this->uidList;
	}

	public function setPiVars($piVars) {
		$this->piVars = $piVars;
	}

	public function getPiVars() {
		return $this->piVars;
	}
}