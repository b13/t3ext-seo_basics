<?php

namespace B13\SeoBasics\Tree;

/***************************************************************
 *  Copyright notice - MIT License (MIT)
 *
 *  (c) 2007-2014 Benni Mack <benni@typo3.org>
 *  All rights reserved
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 ***************************************************************/

use TYPO3\CMS\Backend\Utility\IconUtility;

/** 
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
