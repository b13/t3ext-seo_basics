<?php

namespace B13\SeoBasics\Tree;

/***************************************************************
*  Copyright notice
*  
*  (c) 2014 Benjamin Mack <benni@typo3.org>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/** 
 * @author	Benjamin Mack (benni@typo3.org)
 * @package	B13\SeoBasics
 * 
 * Note:
 * With TYPO3 CMS 6.1 (especially commit https://review.typo3.org/#/c/22632/)
 * there is a change that the t3lib_pageTree is used for BE uses
 * the solution for now is to extend the class and do an additional check
 * However, a generic tree function for Frontend purposes should be 
 * developed and either be included in here on the TYPO3 CMS core.
 */

class PageTreeView extends \TYPO3\CMS\Backend\Tree\View\PageTreeView {

	/**
	 * Fetches the data for the tree
	 *
	 * @param integer $uid item id for which to select subitems (parent id)
	 * @param integer $depth Max depth (recursivity limit)
	 * @param string $depthData HTML-code prefix for recursive calls.
	 * @param string $blankLineCode ? (internal)
	 * @param string $subCSSclass CSS class to use for <td> sub-elements
	 * @return integer The count of items on the level
	 * @todo Define visibility
	 */
	public function getTree($uid, $depth = 999, $depthData = '', $blankLineCode = '', $subCSSclass = '') {
		// Buffer for id hierarchy is reset:
		$this->buffer_idH = array();
		// Init vars
		$depth = (int)$depth;
		$HTML = '';
		$a = 0;
		$res = $this->getDataInit($uid, $subCSSclass);
		$c = $this->getDataCount($res);
		$crazyRecursionLimiter = 999;
		$idH = array();
		// Traverse the records:
		while ($crazyRecursionLimiter > 0 && ($row = $this->getDataNext($res, $subCSSclass))) {
			// webmount check removed by bmack

			$a++;
			$crazyRecursionLimiter--;
			$newID = $row['uid'];
			if ($newID == 0) {
				throw new \RuntimeException('Endless recursion detected: TYPO3 has detected an error in the database. Please fix it manually (e.g. using phpMyAdmin) and change the UID of ' . $this->table . ':0 to a new value.<br /><br />See <a href="http://forge.typo3.org/issues/16150" target="_blank">forge.typo3.org/issues/16150</a> to get more information about a possible cause.', 1294586383);
			}
			// Reserve space.
			$this->tree[] = array();
			end($this->tree);
			// Get the key for this space
			$treeKey = key($this->tree);
			$LN = $a == $c ? 'blank' : 'line';
			// If records should be accumulated, do so
			if ($this->setRecs) {
				$this->recs[$row['uid']] = $row;
			}
			// Accumulate the id of the element in the internal arrays
			$this->ids[] = ($idH[$row['uid']]['uid'] = $row['uid']);
			$this->ids_hierarchy[$depth][] = $row['uid'];
			$this->orig_ids_hierarchy[$depth][] = $row['_ORIG_uid'] ?: $row['uid'];

			// Make a recursive call to the next level
			$HTML_depthData = $depthData . IconUtility::getSpriteIcon('treeline-' . $LN);
			if ($depth > 1 && $this->expandNext($newID) && !$row['php_tree_stop']) {
				$nextCount = $this->getTree($newID, $depth - 1, $this->makeHTML ? $HTML_depthData : '', $blankLineCode . ',' . $LN, $row['_SUBCSSCLASS']);
				if (count($this->buffer_idH)) {
					$idH[$row['uid']]['subrow'] = $this->buffer_idH;
				}
				// Set "did expand" flag
				$exp = 1;
			} else {
				$nextCount = $this->getCount($newID);
				// Clear "did expand" flag
				$exp = 0;
			}
			// Set HTML-icons, if any:
			if ($this->makeHTML) {
				$HTML = $depthData . $this->PMicon($row, $a, $c, $nextCount, $exp);
				$HTML .= $this->wrapStop($this->getIcon($row), $row);
			}
			// Finally, add the row/HTML content to the ->tree array in the reserved key.
			$this->tree[$treeKey] = array(
				'row' => $row,
				'HTML' => $HTML,
				'HTML_depthData' => $this->makeHTML == 2 ? $HTML_depthData : '',
				'invertedDepth' => $depth,
				'blankLineCode' => $blankLineCode,
				'bank' => $this->bank
			);
		}
		$this->getDataFree($res);
		$this->buffer_idH = $idH;
		return $c;
	}


	/**
	 * Getting the tree data: next entry
	 *
	 * @param mixed $res Data handle
	 * @param string $subCSSclass CSS class for sub elements (workspace related)
	 * @return array item data array OR FALSE if end of elements.
	 * @access private
	 * @see getDataInit()
	 * @todo Define visibility
	 */
	public function getDataNext(&$res, $subCSSclass = '') {
		if (is_array($this->data)) {
			if ($res < 0) {
				$row = FALSE;
			} else {
				list(, $row) = each($this->dataLookup[$res][$this->subLevelID]);
			}
			return $row;
		} else {
			while ($row = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if (is_array($row)) {
					break;
				}
			}
			return $row;
		}
	}
}
